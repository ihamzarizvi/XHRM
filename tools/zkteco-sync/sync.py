"""
ZKTeco K50 → XHRM Attendance Sync Tool
=======================================
Pulls attendance logs from ZKTeco biometric device, stores local backup,
and pushes to XHRM server via API or CSV export.

Usage:
    python sync.py                  # Run sync once (pull + backup + push)
    python sync.py --schedule       # Run on configured schedule
    python sync.py --pull-only      # Pull from device, backup only (no push)
    python sync.py --export-csv     # Export unsynced records to CSV
    python sync.py --push           # Push unsynced records to XHRM API
    python sync.py --status         # Show sync status
    python sync.py --test-device    # Test ZKTeco connection
    python sync.py --test-server    # Test XHRM server connection
"""

import os
import sys
import csv
import json
import time
import sqlite3
import argparse
import configparser
import logging
from datetime import datetime, timedelta, timezone
from pathlib import Path

try:
    from zk import ZK
except ImportError:
    print("ERROR: pyzk not installed. Run: pip install pyzk")
    sys.exit(1)

try:
    import requests
except ImportError:
    print("ERROR: requests not installed. Run: pip install requests")
    sys.exit(1)

try:
    import schedule
except ImportError:
    schedule = None

try:
    from colorama import init, Fore, Style
    init()
except ImportError:
    # Fallback if colorama not installed
    class Fore:
        GREEN = RED = YELLOW = CYAN = MAGENTA = WHITE = RESET = ""
    class Style:
        BRIGHT = RESET_ALL = ""

# ─── Configuration ─────────────────────────────────────────────────────────────

SCRIPT_DIR = Path(__file__).parent
CONFIG_FILE = SCRIPT_DIR / "config.ini"
MAPPING_FILE = SCRIPT_DIR / "employee_mapping.ini"

def load_config():
    """Load configuration from config.ini"""
    if not CONFIG_FILE.exists():
        print(f"{Fore.RED}ERROR: config.ini not found at {CONFIG_FILE}{Style.RESET_ALL}")
        print("Copy config.ini.example to config.ini and edit it with your settings.")
        sys.exit(1)

    config = configparser.ConfigParser()
    config.read(CONFIG_FILE)
    return config


def load_employee_mapping():
    """Load ZKTeco → XHRM employee ID mapping from employee_mapping.ini.
    Returns a dict: {zk_user_id: xhrm_emp_number}
    """
    mapping = {}
    if not MAPPING_FILE.exists():
        logger.warning(f"{Fore.YELLOW}⚠ No employee_mapping.ini found — using ZKTeco IDs as XHRM employee numbers{Style.RESET_ALL}")
        return mapping

    config = configparser.ConfigParser()
    config.read(MAPPING_FILE)

    if config.has_section("mapping"):
        for zk_id_str, xhrm_id_str in config.items("mapping"):
            try:
                zk_id = int(zk_id_str.strip())
                xhrm_id = int(xhrm_id_str.strip())
                mapping[zk_id] = xhrm_id
            except ValueError:
                continue

    if mapping:
        logger.info(f"  Loaded {len(mapping)} employee mappings from employee_mapping.ini")
    else:
        logger.warning(f"{Fore.YELLOW}⚠ employee_mapping.ini has no mappings — using ZKTeco IDs as XHRM employee numbers{Style.RESET_ALL}")

    return mapping


# ─── Logging ───────────────────────────────────────────────────────────────────

LOG_FILE = SCRIPT_DIR / "sync.log"

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s [%(levelname)s] %(message)s",
    handlers=[
        logging.FileHandler(LOG_FILE, encoding="utf-8"),
        logging.StreamHandler()
    ]
)
logger = logging.getLogger("zkteco-sync")


# ─── Database ──────────────────────────────────────────────────────────────────

def init_db(db_path):
    """Initialize SQLite database for local backup."""
    conn = sqlite3.connect(db_path)
    conn.execute("PRAGMA journal_mode=WAL")
    conn.execute("""
        CREATE TABLE IF NOT EXISTS raw_punches (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            zk_user_id INTEGER NOT NULL,
            user_name TEXT,
            timestamp TEXT NOT NULL,
            punch_type INTEGER DEFAULT 0,
            device_ip TEXT,
            pulled_at TEXT NOT NULL,
            synced_to_xhrm INTEGER DEFAULT 0,
            synced_at TEXT,
            sync_method TEXT,
            UNIQUE(zk_user_id, timestamp)
        )
    """)
    conn.execute("""
        CREATE TABLE IF NOT EXISTS sync_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            sync_time TEXT NOT NULL,
            records_pulled INTEGER DEFAULT 0,
            records_new INTEGER DEFAULT 0,
            records_pushed INTEGER DEFAULT 0,
            push_method TEXT,
            status TEXT,
            error_message TEXT
        )
    """)
    conn.execute("""
        CREATE TABLE IF NOT EXISTS employees (
            zk_user_id INTEGER PRIMARY KEY,
            user_name TEXT,
            xhrm_emp_number INTEGER,
            last_updated TEXT
        )
    """)
    conn.execute("""
        CREATE INDEX IF NOT EXISTS idx_punches_synced
        ON raw_punches(synced_to_xhrm)
    """)
    conn.execute("""
        CREATE INDEX IF NOT EXISTS idx_punches_timestamp
        ON raw_punches(timestamp)
    """)
    conn.commit()
    return conn


# ─── ZKTeco Device ─────────────────────────────────────────────────────────────

def connect_device(config):
    """Connect to ZKTeco device."""
    ip = config.get("zkteco", "ip")
    port = config.getint("zkteco", "port", fallback=4370)
    password = config.getint("zkteco", "password", fallback=0)

    logger.info(f"Connecting to ZKTeco at {ip}:{port}...")
    zk = ZK(ip, port=port, timeout=10, password=password)

    try:
        conn = zk.connect()
        logger.info(f"{Fore.GREEN}✓ Connected to ZKTeco device{Style.RESET_ALL}")
        return conn
    except Exception as e:
        logger.error(f"{Fore.RED}✗ Failed to connect: {e}{Style.RESET_ALL}")
        raise


def pull_attendance(zk_conn):
    """Pull all attendance records from ZKTeco device."""
    logger.info("Pulling attendance records from device...")
    try:
        attendance = zk_conn.get_attendance()
        if attendance is None:
            attendance = []
        logger.info(f"  Pulled {len(attendance)} total records from device")
        return attendance
    except Exception as e:
        logger.error(f"Error pulling attendance: {e}")
        raise


def pull_users(zk_conn):
    """Pull user list from ZKTeco device."""
    logger.info("Pulling user list from device...")
    try:
        users = zk_conn.get_users()
        if users is None:
            users = []
        logger.info(f"  Found {len(users)} users on device")
        return users
    except Exception as e:
        logger.error(f"Error pulling users: {e}")
        raise


# ─── Local Backup ──────────────────────────────────────────────────────────────

def save_to_db(conn, attendance_records, users, device_ip):
    """Save attendance records to local SQLite database."""
    now = datetime.now().isoformat()
    new_count = 0
    duplicate_count = 0

    # Update employee cache
    for user in users:
        conn.execute("""
            INSERT OR REPLACE INTO employees (zk_user_id, user_name, last_updated)
            VALUES (?, ?, ?)
        """, (user.user_id, user.name, now))

    # Insert punches (skip duplicates)
    for record in attendance_records:
        try:
            timestamp_str = record.timestamp.strftime("%Y-%m-%d %H:%M:%S")
            conn.execute("""
                INSERT OR IGNORE INTO raw_punches
                (zk_user_id, user_name, timestamp, punch_type, device_ip, pulled_at)
                VALUES (?, ?, ?, ?, ?, ?)
            """, (
                record.user_id,
                getattr(record, 'user_name', None),
                timestamp_str,
                getattr(record, 'punch', 0),
                device_ip,
                now
            ))
            if conn.total_changes:
                new_count += 1
            else:
                duplicate_count += 1
        except sqlite3.IntegrityError:
            duplicate_count += 1

    conn.commit()
    logger.info(f"  {Fore.GREEN}New: {new_count}{Style.RESET_ALL}, "
                f"Duplicates skipped: {duplicate_count}")
    return new_count


def get_unsynced_records(conn, date_from=None, date_to=None):
    """Get records not yet pushed to XHRM, optionally filtered by date range."""
    query = "SELECT id, zk_user_id, user_name, timestamp, punch_type FROM raw_punches WHERE synced_to_xhrm = 0"
    params = []

    if date_from:
        query += " AND DATE(timestamp) >= ?"
        params.append(date_from)
    if date_to:
        query += " AND DATE(timestamp) <= ?"
        params.append(date_to)

    query += " ORDER BY timestamp ASC"
    cursor = conn.execute(query, params)
    return cursor.fetchall()


def mark_as_synced(conn, record_ids, method="api"):
    """Mark records as synced to XHRM."""
    now = datetime.now().isoformat()
    for rid in record_ids:
        conn.execute("""
            UPDATE raw_punches
            SET synced_to_xhrm = 1, synced_at = ?, sync_method = ?
            WHERE id = ?
        """, (now, method, rid))
    conn.commit()


# ─── CSV Export ────────────────────────────────────────────────────────────────

def export_csv(conn, config, mark_synced=False):
    """Export unsynced records as CSV file in ZKTeco format for XHRM import."""
    records = get_unsynced_records(conn)

    if not records:
        logger.info(f"{Fore.YELLOW}No unsynced records to export.{Style.RESET_ALL}")
        return None

    export_dir = Path(config.get("backup", "csv_export_path", fallback="./exports"))
    export_dir.mkdir(parents=True, exist_ok=True)

    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    csv_file = export_dir / f"attendance_{timestamp}.csv"

    # Write in ZKTeco raw format that XHRM's ImportAttendanceController expects
    with open(csv_file, "w", newline="", encoding="utf-8") as f:
        writer = csv.writer(f)
        writer.writerow(["Name", "No.", "Date/Time", "Location ID", "ID Number"])

        record_ids = []
        for record in records:
            rid, zk_user_id, user_name, timestamp_str, punch_type = record
            # Convert timestamp format from YYYY-MM-DD HH:MM:SS to DD-MM-YYYY HH:MM
            dt = datetime.strptime(timestamp_str, "%Y-%m-%d %H:%M:%S")
            formatted_dt = dt.strftime("%d-%m-%Y %H:%M")

            writer.writerow([
                user_name or f"Employee {zk_user_id}",
                zk_user_id,
                formatted_dt,
                1,  # Location ID
                zk_user_id
            ])
            record_ids.append(rid)

    logger.info(f"{Fore.GREEN}✓ Exported {len(records)} records to: {csv_file}{Style.RESET_ALL}")

    if mark_synced:
        mark_as_synced(conn, record_ids, method="csv")
        logger.info(f"  Marked {len(record_ids)} records as synced (csv)")

    return csv_file


# ─── API Push ──────────────────────────────────────────────────────────────────

def push_to_xhrm_api(conn, config, date_from=None, date_to=None):
    """Push unsynced records to XHRM via the attendance import API."""
    if date_from or date_to:
        logger.info(f"  Date filter: {date_from or 'any'} → {date_to or 'any'}")
    records = get_unsynced_records(conn, date_from=date_from, date_to=date_to)

    if not records:
        logger.info(f"{Fore.YELLOW}No unsynced records to push.{Style.RESET_ALL}")
        return 0

    url = config.get("xhrm", "url").rstrip("/")
    api_key = config.get("xhrm", "api_key")
    tz_name = config.get("sync", "timezone", fallback="Asia/Karachi")
    tz_offset = config.getfloat("sync", "timezone_offset", fallback=5.0)

    endpoint = f"{url}/web/api/attendance_import.php"

    # Group records by employee and date for punch-in/out pairing
    grouped = {}
    record_id_map = {}
    for record in records:
        rid, zk_user_id, user_name, timestamp_str, punch_type = record
        dt = datetime.strptime(timestamp_str, "%Y-%m-%d %H:%M:%S")
        date_key = dt.strftime("%Y-%m-%d")
        key = (zk_user_id, date_key)
        if key not in grouped:
            grouped[key] = []
            record_id_map[key] = []
        grouped[key].append(dt)
        record_id_map[key].append(rid)

    # Build attendance entries (earliest = in, latest = out)
    entries = []
    all_record_ids = []
    for (zk_id, date_key), punches in grouped.items():
        punches.sort()
        entry = {
            "empNumber": int(zk_id),  # Sent as ZKTeco ID — PHP looks up by Employee Id field
            "date": date_key,
            "punchIn": punches[0].strftime("%Y-%m-%d %H:%M:%S"),
            "timezoneName": tz_name,
            "timezoneOffset": tz_offset,
        }
        if len(punches) > 1:
            entry["punchOut"] = punches[-1].strftime("%Y-%m-%d %H:%M:%S")
        entries.append(entry)
        all_record_ids.extend(record_id_map[(zk_id, date_key)])

    if not entries:
        logger.info(f"{Fore.YELLOW}No records to push.{Style.RESET_ALL}")
        return 0

    payload = {
        "api_key": api_key,
        "records": entries
    }

    logger.info(f"Pushing {len(entries)} attendance entries to XHRM API...")
    try:
        response = requests.post(
            endpoint,
            json=payload,
            headers={"Content-Type": "application/json"},
            timeout=120,  # Increased timeout for large batches
            verify=True
        )

        if response.status_code == 200:
            result = response.json()
            if result.get("success"):
                imported = result.get("imported", 0)
                skipped = result.get("skipped", 0)
                missing = result.get("missingEmployees", [])
                errors = result.get("errors", [])

                # Only mark records as synced if their employee was found on the server
                # Records for missing employees stay unsynced so they auto-sync
                # once the Employee Id is set in XHRM
                missing_set = set(int(m) for m in missing) if missing else set()
                synced_ids = []
                for (zk_id, date_key), rids in record_id_map.items():
                    if int(zk_id) not in missing_set:
                        synced_ids.extend(rids)

                if synced_ids:
                    mark_as_synced(conn, synced_ids, method="api")

                logger.info(f"{Fore.GREEN}✓ API Import Summary:{Style.RESET_ALL}")
                logger.info(f"  {Fore.GREEN}Imported:  {imported} records{Style.RESET_ALL}")
                if skipped:
                    logger.info(f"  {Fore.YELLOW}Skipped:   {skipped} (already existed on server){Style.RESET_ALL}")
                if missing:
                    unsynced_count = len(all_record_ids) - len(synced_ids)
                    logger.warning(f"  {Fore.YELLOW}⚠ {len(missing)} ZKTeco user IDs not found in XHRM:{Style.RESET_ALL}")
                    logger.warning(f"    IDs: {', '.join(str(m) for m in missing)}")
                    logger.warning(f"    → {unsynced_count} records kept as UNSYNCED (will auto-sync later)")
                    logger.warning(f"    → Set matching Employee Ids in XHRM PIM, then re-run sync.")
                if errors:
                    for err in errors:
                        if "not found" not in err.lower():
                            logger.warning(f"  {Fore.YELLOW}⚠ {err}{Style.RESET_ALL}")
                return imported
            else:
                logger.error(f"{Fore.RED}✗ API error: {result.get('message', 'Unknown error')}{Style.RESET_ALL}")
                return 0
        else:
            logger.error(f"{Fore.RED}✗ HTTP {response.status_code}: {response.text[:200]}{Style.RESET_ALL}")
            return 0

    except requests.exceptions.ConnectionError:
        logger.error(f"{Fore.RED}✗ Cannot connect to XHRM server at {url}{Style.RESET_ALL}")
        return 0
    except Exception as e:
        logger.error(f"{Fore.RED}✗ Push error: {e}{Style.RESET_ALL}")
        return 0


# ─── Upload CSV to XHRM ───────────────────────────────────────────────────────

def upload_csv_to_xhrm(csv_file, config):
    """Upload a CSV file to XHRM's attendance import endpoint."""
    url = config.get("xhrm", "url").rstrip("/")
    api_key = config.get("xhrm", "api_key")
    endpoint = f"{url}/web/api/attendance_import.php"

    logger.info(f"Uploading CSV to XHRM: {csv_file}...")
    try:
        with open(csv_file, "rb") as f:
            response = requests.post(
                endpoint,
                files={"attendance_file": (csv_file.name, f, "text/csv")},
                data={"api_key": api_key, "mode": "csv"},
                timeout=60,
                verify=True
            )

        if response.status_code == 200:
            result = response.json()
            if result.get("success"):
                logger.info(f"{Fore.GREEN}✓ CSV uploaded: {result.get('imported', '?')} records imported{Style.RESET_ALL}")
                return True
            else:
                logger.error(f"{Fore.RED}✗ CSV error: {result.get('message', 'Unknown')}{Style.RESET_ALL}")
        else:
            logger.error(f"{Fore.RED}✗ HTTP {response.status_code}{Style.RESET_ALL}")
        return False

    except Exception as e:
        logger.error(f"{Fore.RED}✗ Upload error: {e}{Style.RESET_ALL}")
        return False


# ─── Sync Logic ────────────────────────────────────────────────────────────────

def log_sync(conn, pulled, new, pushed, method, status, error=None):
    """Log sync operation to database."""
    conn.execute("""
        INSERT INTO sync_log (sync_time, records_pulled, records_new, records_pushed,
                             push_method, status, error_message)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    """, (datetime.now().isoformat(), pulled, new, pushed, method, status, error))
    conn.commit()


def run_sync(config, push_method="api"):
    """Run a full sync cycle: pull → backup → push."""
    db_path = Path(config.get("backup", "db_path", fallback="./attendance_backup.db"))
    if not db_path.is_absolute():
        db_path = SCRIPT_DIR / db_path

    db_conn = init_db(str(db_path))
    zk_conn = None
    pulled_count = 0
    new_count = 0
    pushed_count = 0

    print(f"\n{Fore.CYAN}{'='*60}")
    print(f"  ZKTeco → XHRM Attendance Sync")
    print(f"  {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"{'='*60}{Style.RESET_ALL}\n")

    try:
        # Step 1: Connect to ZKTeco
        zk_conn = connect_device(config)

        # Step 2: Pull users
        users = pull_users(zk_conn)

        # Step 3: Pull attendance
        attendance = pull_attendance(zk_conn)
        pulled_count = len(attendance)

        # Step 4: Save to local SQLite backup
        device_ip = config.get("zkteco", "ip")
        new_count = save_to_db(db_conn, attendance, users, device_ip)

        # Step 5: Push to XHRM (if not pull-only)
        if push_method == "api":
            pushed_count = push_to_xhrm_api(db_conn, config)
        elif push_method == "csv":
            csv_file = export_csv(db_conn, config, mark_synced=True)
            if csv_file:
                upload_csv_to_xhrm(csv_file, config)
                pushed_count = new_count
        elif push_method == "none":
            logger.info("Pull-only mode — skipping push to XHRM")

        log_sync(db_conn, pulled_count, new_count, pushed_count, push_method, "success")

        print(f"\n{Fore.GREEN}✓ Sync complete!{Style.RESET_ALL}")
        print(f"  Total from device: {pulled_count}")
        print(f"  New records saved: {new_count}")
        print(f"  Pushed to XHRM:   {pushed_count}\n")

    except Exception as e:
        logger.error(f"Sync failed: {e}")
        log_sync(db_conn, pulled_count, new_count, pushed_count, push_method, "error", str(e))
    finally:
        if zk_conn:
            try:
                zk_conn.disconnect()
                logger.info("Disconnected from ZKTeco device")
            except:
                pass
        db_conn.close()


# ─── Status Display ────────────────────────────────────────────────────────────

def show_status(config):
    """Display current sync status and statistics."""
    db_path = Path(config.get("backup", "db_path", fallback="./attendance_backup.db"))
    if not db_path.is_absolute():
        db_path = SCRIPT_DIR / db_path

    if not db_path.exists():
        print(f"{Fore.YELLOW}No sync database found. Run a sync first.{Style.RESET_ALL}")
        return

    conn = sqlite3.connect(str(db_path))

    # Total records
    total = conn.execute("SELECT COUNT(*) FROM raw_punches").fetchone()[0]
    synced = conn.execute("SELECT COUNT(*) FROM raw_punches WHERE synced_to_xhrm = 1").fetchone()[0]
    unsynced = total - synced
    employees = conn.execute("SELECT COUNT(*) FROM employees").fetchone()[0]

    # Last sync
    last_sync = conn.execute(
        "SELECT sync_time, records_pulled, records_new, status FROM sync_log ORDER BY id DESC LIMIT 1"
    ).fetchone()

    # Today's records
    today = datetime.now().strftime("%Y-%m-%d")
    today_count = conn.execute(
        "SELECT COUNT(*) FROM raw_punches WHERE timestamp LIKE ?", (f"{today}%",)
    ).fetchone()[0]

    print(f"\n{Fore.CYAN}{'='*50}")
    print(f"  ZKTeco Sync Status")
    print(f"{'='*50}{Style.RESET_ALL}")
    print(f"  Device:          {config.get('zkteco', 'ip')}:{config.get('zkteco', 'port')}")
    print(f"  XHRM Server:     {config.get('xhrm', 'url')}")
    print(f"  Sync Interval:   {config.get('sync', 'interval')} min")
    print(f"{'─'*50}")
    print(f"  Total Records:   {total}")
    print(f"  Synced to XHRM:  {Fore.GREEN}{synced}{Style.RESET_ALL}")
    print(f"  Pending Sync:    {Fore.YELLOW}{unsynced}{Style.RESET_ALL}")
    print(f"  Today's Punches: {today_count}")
    print(f"  Employees:       {employees}")
    print(f"{'─'*50}")

    if last_sync:
        status_color = Fore.GREEN if last_sync[3] == "success" else Fore.RED
        print(f"  Last Sync:       {last_sync[0]}")
        print(f"  Last Status:     {status_color}{last_sync[3]}{Style.RESET_ALL}")
        print(f"  Last Pulled:     {last_sync[1]} (new: {last_sync[2]})")
    else:
        print(f"  Last Sync:       {Fore.YELLOW}Never{Style.RESET_ALL}")

    print(f"{'='*50}\n")
    conn.close()


# ─── Test Functions ────────────────────────────────────────────────────────────

def test_device(config):
    """Test ZKTeco device connectivity."""
    print(f"\n{Fore.CYAN}Testing ZKTeco device...{Style.RESET_ALL}")
    try:
        zk_conn = connect_device(config)
        users = pull_users(zk_conn)
        attendance = pull_attendance(zk_conn)

        print(f"\n  {Fore.GREEN}✓ Device Info:{Style.RESET_ALL}")
        print(f"    Users on device:      {len(users)}")
        print(f"    Attendance records:   {len(attendance)}")

        if users:
            print(f"\n  {Fore.CYAN}Users:{Style.RESET_ALL}")
            for u in users:
                print(f"    ID: {u.user_id:>4}  Name: {u.name}")

        if attendance:
            print(f"\n  {Fore.CYAN}Last 5 punches:{Style.RESET_ALL}")
            sorted_att = sorted(attendance, key=lambda x: x.timestamp, reverse=True)[:5]
            for a in sorted_att:
                print(f"    User {a.user_id:>4}  {a.timestamp}  Punch: {getattr(a, 'punch', '?')}")

        zk_conn.disconnect()
        print(f"\n  {Fore.GREEN}✓ Device connection OK!{Style.RESET_ALL}\n")
    except Exception as e:
        print(f"\n  {Fore.RED}✗ Device test failed: {e}{Style.RESET_ALL}\n")


def test_server(config):
    """Test XHRM server connectivity."""
    print(f"\n{Fore.CYAN}Testing XHRM server...{Style.RESET_ALL}")
    url = config.get("xhrm", "url").rstrip("/")
    api_key = config.get("xhrm", "api_key")

    try:
        # Test basic connectivity
        response = requests.get(f"{url}/web/index.php/auth/login", timeout=10, verify=True)
        print(f"  Server reachable: {Fore.GREEN}✓{Style.RESET_ALL} (HTTP {response.status_code})")

        # Test API endpoint
        endpoint = f"{url}/web/api/attendance_import.php"
        response = requests.post(
            endpoint,
            json={"api_key": api_key, "action": "test"},
            timeout=10,
            verify=True
        )
        if response.status_code == 200:
            result = response.json()
            if result.get("success"):
                print(f"  API endpoint:     {Fore.GREEN}✓{Style.RESET_ALL} ({result.get('message', 'OK')})")
            else:
                print(f"  API endpoint:     {Fore.RED}✗ {result.get('message', 'Error')}{Style.RESET_ALL}")
        else:
            print(f"  API endpoint:     {Fore.YELLOW}⚠ HTTP {response.status_code}{Style.RESET_ALL}")

        print(f"\n  {Fore.GREEN}✓ Server connection OK!{Style.RESET_ALL}\n")
    except requests.exceptions.ConnectionError:
        print(f"\n  {Fore.RED}✗ Cannot reach server at {url}{Style.RESET_ALL}\n")
    except Exception as e:
        print(f"\n  {Fore.RED}✗ Server test failed: {e}{Style.RESET_ALL}\n")


# ─── Scheduler ─────────────────────────────────────────────────────────────────

def run_scheduled(config):
    """Run sync on a configurable schedule."""
    if schedule is None:
        print(f"{Fore.RED}ERROR: 'schedule' package not installed. Run: pip install schedule{Style.RESET_ALL}")
        return

    interval = config.getint("sync", "interval", fallback=5)

    if interval <= 0:
        print(f"{Fore.YELLOW}Sync interval is 0 — manual mode only.{Style.RESET_ALL}")
        return

    print(f"\n{Fore.CYAN}{'='*60}")
    print(f"  ZKTeco Scheduled Sync — Every {interval} minute(s)")
    print(f"  Press Ctrl+C to stop")
    print(f"{'='*60}{Style.RESET_ALL}\n")

    # Run once immediately
    run_sync(config, push_method="api")

    # Schedule recurring sync
    schedule.every(interval).minutes.do(run_sync, config=config, push_method="api")

    try:
        while True:
            schedule.run_pending()
            time.sleep(1)
    except KeyboardInterrupt:
        print(f"\n{Fore.YELLOW}Scheduler stopped by user.{Style.RESET_ALL}")


# ─── Reset Sync ────────────────────────────────────────────────────────────────

def reset_sync_status(config):
    """Reset all records to unsynced so they can be re-pushed."""
    db_path = Path(config.get("backup", "db_path", fallback="./attendance_backup.db"))
    if not db_path.is_absolute():
        db_path = SCRIPT_DIR / db_path

    if not db_path.exists():
        print(f"{Fore.YELLOW}No sync database found. Run a sync first.{Style.RESET_ALL}")
        return

    conn = sqlite3.connect(str(db_path))
    synced_count = conn.execute("SELECT COUNT(*) FROM raw_punches WHERE synced_to_xhrm = 1").fetchone()[0]

    if synced_count == 0:
        print(f"{Fore.YELLOW}No synced records to reset.{Style.RESET_ALL}")
        conn.close()
        return

    conn.execute("UPDATE raw_punches SET synced_to_xhrm = 0, synced_at = NULL, sync_method = NULL")
    conn.commit()
    conn.close()

    print(f"{Fore.GREEN}✓ Reset {synced_count} records to unsynced status.{Style.RESET_ALL}")
    print(f"  Next sync will re-push all records to XHRM.")


# ─── Main ──────────────────────────────────────────────────────────────────────

def main():
    parser = argparse.ArgumentParser(
        description="ZKTeco K50 → XHRM Attendance Sync Tool",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Examples:
  python sync.py                    Sync once (pull + backup + push via API)
  python sync.py --schedule         Run on configured schedule
  python sync.py --pull-only        Pull from device, backup only
  python sync.py --export-csv       Export unsynced records to CSV
  python sync.py --push api         Push unsynced records via API
  python sync.py --push csv         Push unsynced records via CSV upload
  python sync.py --status           Show sync status
  python sync.py --test-device      Test ZKTeco device connection
  python sync.py --test-server      Test XHRM server connection
  python sync.py --reset-sync       Reset sync status (re-push all records)
  python sync.py --push-date-range  Push records for a specific date range (interactive)
  python sync.py --date-from 2026-01-01 --date-to 2026-01-31  Push date range
        """
    )

    parser.add_argument("--schedule", action="store_true", help="Run on configured schedule")
    parser.add_argument("--pull-only", action="store_true", help="Pull & backup only, no push")
    parser.add_argument("--export-csv", action="store_true", help="Export unsynced records to CSV")
    parser.add_argument("--push", choices=["api", "csv"], help="Push method (api or csv)")
    parser.add_argument("--status", action="store_true", help="Show sync status")
    parser.add_argument("--test-device", action="store_true", help="Test ZKTeco connection")
    parser.add_argument("--test-server", action="store_true", help="Test XHRM server connection")
    parser.add_argument("--reset-sync", action="store_true", help="Reset sync status to re-push all records")
    parser.add_argument("--push-date-range", action="store_true", help="Push records for a specific date range (interactive)")
    parser.add_argument("--date-from", type=str, help="Start date (YYYY-MM-DD) for push filter")
    parser.add_argument("--date-to", type=str, help="End date (YYYY-MM-DD) for push filter")

    args = parser.parse_args()
    config = load_config()

    if args.status:
        show_status(config)
    elif args.test_device:
        test_device(config)
    elif args.test_server:
        test_server(config)
    elif args.reset_sync:
        reset_sync_status(config)
    elif args.export_csv:
        db_path = Path(config.get("backup", "db_path", fallback="./attendance_backup.db"))
        if not db_path.is_absolute():
            db_path = SCRIPT_DIR / db_path
        conn = init_db(str(db_path))
        export_csv(conn, config, mark_synced=False)
        conn.close()
    elif args.schedule:
        run_scheduled(config)
    elif args.pull_only:
        run_sync(config, push_method="none")
    elif args.push:
        if args.push == "csv":
            # Export CSV and upload
            db_path = Path(config.get("backup", "db_path", fallback="./attendance_backup.db"))
            if not db_path.is_absolute():
                db_path = SCRIPT_DIR / db_path
            conn = init_db(str(db_path))
            csv_file = export_csv(conn, config, mark_synced=False)
            if csv_file:
                success = upload_csv_to_xhrm(csv_file, config)
                if success:
                    # Mark records as synced after successful upload
                    unsynced = get_unsynced_records(conn)
                    mark_as_synced(conn, [r[0] for r in unsynced], method="csv")
            conn.close()
        else:
            # Push via API
            db_path = Path(config.get("backup", "db_path", fallback="./attendance_backup.db"))
            if not db_path.is_absolute():
                db_path = SCRIPT_DIR / db_path
            conn = init_db(str(db_path))
            push_to_xhrm_api(conn, config)
            conn.close()
    elif args.push_date_range:
        # Interactive date range push
        print(f"\n{Fore.CYAN}Push Attendance by Date Range{Style.RESET_ALL}")
        print(f"  Enter dates in YYYY-MM-DD format, or press Enter to skip.\n")
        date_from = input("  From date (e.g. 2026-01-01): ").strip() or None
        date_to = input("  To date   (e.g. 2026-02-19): ").strip() or None
        if date_from:
            try:
                datetime.strptime(date_from, "%Y-%m-%d")
            except ValueError:
                print(f"{Fore.RED}Invalid from-date format. Use YYYY-MM-DD{Style.RESET_ALL}")
                return
        if date_to:
            try:
                datetime.strptime(date_to, "%Y-%m-%d")
            except ValueError:
                print(f"{Fore.RED}Invalid to-date format. Use YYYY-MM-DD{Style.RESET_ALL}")
                return
        db_path = Path(config.get("backup", "db_path", fallback="./attendance_backup.db"))
        if not db_path.is_absolute():
            db_path = SCRIPT_DIR / db_path
        conn = init_db(str(db_path))
        push_to_xhrm_api(conn, config, date_from=date_from, date_to=date_to)
        conn.close()
    elif args.date_from or args.date_to:
        # Date range from CLI args
        db_path = Path(config.get("backup", "db_path", fallback="./attendance_backup.db"))
        if not db_path.is_absolute():
            db_path = SCRIPT_DIR / db_path
        conn = init_db(str(db_path))
        push_to_xhrm_api(conn, config, date_from=args.date_from, date_to=args.date_to)
        conn.close()
    else:
        # Default: full sync with API push
        run_sync(config, push_method="api")


if __name__ == "__main__":
    main()
