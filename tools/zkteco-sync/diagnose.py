"""Diagnose punch time mismatches between ZKTeco and XHRM."""
import sqlite3

conn = sqlite3.connect('attendance_backup.db')

print("=" * 60)
print("  ZKTeco Attendance Diagnostic — 2026-02-19")
print("=" * 60)

# All punches for ID 5 today
print("\n--- ZKTeco User ID 5 punches on 2026-02-19 ---")
rows = conn.execute(
    "SELECT timestamp, punch_type FROM raw_punches WHERE zk_user_id = 5 AND timestamp LIKE '2026-02-19%' ORDER BY timestamp"
).fetchall()
for r in rows:
    print(f"  {r[0]}  Type: {r[1]}")
print(f"  Total: {len(rows)}")

# Who punched around 10:13?
print("\n--- Who punched around 10:13 on 2026-02-19? ---")
rows = conn.execute(
    "SELECT zk_user_id, user_name, timestamp FROM raw_punches WHERE timestamp BETWEEN '2026-02-19 10:12:00' AND '2026-02-19 10:14:00' ORDER BY timestamp"
).fetchall()
for r in rows:
    print(f"  ZK_ID: {r[0]:>4}  Name: {str(r[1] or 'N/A'):<20}  Time: {r[2]}")

# Who punched around 15:23?
print("\n--- Who punched around 15:23 on 2026-02-19? ---")
rows = conn.execute(
    "SELECT zk_user_id, user_name, timestamp FROM raw_punches WHERE timestamp BETWEEN '2026-02-19 15:22:00' AND '2026-02-19 15:24:00' ORDER BY timestamp"
).fetchall()
for r in rows:
    print(f"  ZK_ID: {r[0]:>4}  Name: {str(r[1] or 'N/A'):<20}  Time: {r[2]}")

# All employees with their ZKTeco IDs
print("\n--- All ZKTeco Users ---")
emps = conn.execute("SELECT zk_user_id, user_name FROM employees ORDER BY zk_user_id").fetchall()
for e in emps:
    print(f"  ZK_ID: {e[0]:>4}  Name: {e[1]}")

# How many punches each user has today
print("\n--- Punch count per user on 2026-02-19 ---")
rows = conn.execute("""
    SELECT zk_user_id, COUNT(*) as cnt, MIN(timestamp) as first, MAX(timestamp) as last
    FROM raw_punches
    WHERE timestamp LIKE '2026-02-19%'
    GROUP BY zk_user_id
    ORDER BY zk_user_id
""").fetchall()
for r in rows:
    print(f"  ZK_ID: {r[0]:>4}  Punches: {r[1]}  First: {r[2]}  Last: {r[3]}")

conn.close()
