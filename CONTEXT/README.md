# XHRM Project Context

## What is XHRM?
XHRM is a whitelabeled version of OrangeHRM OS 5.8, customized for **Mimar Construction** (by Xsofty).  
**Live URL:** https://mimar.xsofty.com  
**Repository:** https://github.com/ihamzarizvi/XHRM  

---

## Version 1.0 — Current Release (Feb 2026)

### Features Completed

#### 1. Whitelabeling (OrangeHRM → XHRM)
- Replaced "OrangeHRM OS 5.8" with "XHRM OS" throughout the app
- Replaced "OrangeHRM, Inc." with "Xsofty" and updated links to xsofty.com
- Custom branding in login page, footer, and about sections
- Files changed: various config files, templates, Vue components

#### 2. Password Manager Plugin (`XHRMPasswordManagerPlugin`)
- Full vault system for storing encrypted passwords
- Encrypted with per-user keys
- Share vault items between employees
- Plugin location: `src/plugins/XHRMPasswordManagerPlugin/`
- Frontend: `src/client/src/XHRMPasswordManagerPlugin/`
- Menu: appears in sidebar as "Password Manager"

#### 3. ZKTeco Attendance Sync
- **Purpose:** Syncs attendance records from ZKTeco K50 biometric device to XHRM
- **Device:** IP `192.168.11.201`, Port `4371`, Password `1234`
- **Location:** `tools/zkteco-sync/`
- **Key files:**
  - `sync.py` — Main sync script (Python)
  - `run_sync.bat` — Interactive menu-driven batch script
  - `config.ini` — Configuration (device IP, XHRM URL, API key)
  - `attendance.db` — Local SQLite database of punch records

##### Sync Architecture:
```
ZKTeco K50 → sync.py → Local SQLite DB → XHRM API → ohrm_attendance_record table
```

##### How It Works:
1. `sync.py` pulls punch records from ZKTeco device via ZK protocol
2. Stores them in local `attendance.db` (SQLite)
3. Groups punches by employee+date (earliest=punch-in, latest=punch-out)
4. Pushes to XHRM via `web/api/attendance_import.php`
5. PHP API looks up employee by `employee_id` field (NOT internal `emp_number`)
6. Creates attendance records in `ohrm_attendance_record` table

##### Employee ID Matching:
- ZKTeco stores users by numeric ID (e.g., 5)
- XHRM has two IDs: internal `emp_number` (auto) and `employee_id` (PIM form field)
- The sync matches ZKTeco user ID → XHRM `employee_id` field
- **IMPORTANT:** Each employee's "Employee Id" in PIM must match their ZKTeco user ID

##### Sync Safety Rules:
- **Only unsynced records are pushed** (records marked `synced_to_xhrm=0`)
- **Only found employees get marked synced** — if an employee doesn't exist in XHRM yet, their records stay unsynced for later
- **Server never overwrites existing records** — if attendance already exists for employee+date, it's skipped (protects manual edits)
- **Reset option (menu 9)** resets all sync flags, but PHP API still won't overwrite existing records

##### run_sync.bat Menu:
```
1. Full Sync (pull + backup + push)
2. Pull from ZKTeco only
3. Backup database only
4. Push to XHRM only
5. Show stats
6. Show last 20 records
7. Check XHRM connection
8. Run scheduled sync
9. Reset sync status
10. Push by date range
11. Run diagnostics
12. Exit
```

##### API Endpoint:
- URL: `web/api/attendance_import.php`
- Method: POST (JSON)
- Auth: API key in `config.ini` (`xhrm-zkteco-sync-2024-secret-key`)
- Also has debug mode: `GET ?debug=1`

#### 4. Attendance Date Range Filter
- Both "Employee Attendance Records" and "My Attendance" pages now have **From** and **To** date fields
- Replaced the single "Date" field
- "View" button re-queries the API in-place (no page navigation)
- Backend API already supported `fromDate`/`toDate` parameters
- Files changed:
  - `src/client/src/XHRMAttendancePlugin/pages/ViewEmployeeAttendanceDetailed.vue`
  - `src/client/src/XHRMAttendancePlugin/pages/ViewMyAttendance.vue`

### Server Setup

- **Hosting:** Hostinger (shared hosting)
- **Domain:** mimar.xsofty.com
- **PHP Version:** 8.1+
- **Database:** MySQL
- **Web Server:** Apache with `.htaccess`
- **Document Root:** Points to `web/` directory
- **Git Deploy:** Via Hostinger's Git integration (auto-pull on push to main)
- **Frontend Build:** Built locally (`npm run build` in `src/client/`), compiled files committed to `web/dist/`

### Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.1, Symfony, Doctrine ORM |
| Frontend | Vue.js 3, Oxd UI Library |
| Database | MySQL 8 |
| Build | npm, webpack (vue-cli) |
| Device Sync | Python 3, pyzk library |
| Server | Apache, Hostinger shared hosting |

### Database Tables (Key Custom Ones)

| Table | Purpose |
|-------|---------|
| `ohrm_attendance_record` | Attendance punch in/out records |
| `hs_hr_employee` | Employee master data |
| `hs_hr_emp_basicsalary` | Employee salary info |
| `ohrm_work_shift` | Work shift definitions |
| `ohrm_user_role` | User roles (Admin, ESS) |
| `ohrm_module` | Module enable/disable toggles |

### User Roles
- **Admin** (id=1) — Full access, system configuration
- **ESS** (id=2) — Employee Self Service, limited access

### Known Working State
- Login: ✅ Working
- Dashboard: ✅ Working  
- PIM: ✅ Working
- Attendance: ✅ Working (with date range filter)
- Leave: ✅ Working
- Time: ✅ Working
- Password Manager: ✅ Working
- ZKTeco Sync: ✅ Working
- Work Shifts: ✅ Working (at /admin/workShift)

---

## Version 2.0 — Planned (Payroll Module)

### Overview
Full payroll module integrating with Attendance, Leave, Work Shifts, and PIM.

### Key Features Planned
1. **Multi-currency support** (PKR default)
2. **Flexible pay periods** — Monthly, Bi-weekly, Weekly, Contract, Hourly
3. **Configurable salary components** — Earnings (allowances) and Deductions
4. **Attendance rules** — Grace period, late threshold, half-day, lates-to-absent conversion
5. **Overtime rules** — Configurable rates for weekday/weekend/holiday OT
6. **Tax slabs** — Finance Manager manages per financial year (Pakistan FBR style)
7. **Financial year management**
8. **Holiday calendar** — HR-managed public holidays
9. **Payroll generation** — Auto-calculate from attendance + leave + salary data
10. **Approval workflow** — Draft → Pending Approval → Approved/Rejected → Paid
11. **Finance Manager role** — New user role with payroll approval permissions
12. **PDF payslips** — With company logo from Corporate Branding
13. **Email distribution** — Send payslips on approval
14. **Employee portal** — "My Payslips" self-service page
15. **Module toggle** — Enable/disable from Admin → Configuration
16. **Loans & Advances** — With monthly deduction tracking

### New Plugin
`src/plugins/XHRMPayrollPlugin/` — Following the same architecture as existing plugins

### New Database Tables (10)
1. `xhrm_salary_component` — Configurable earnings/deductions
2. `xhrm_attendance_rule` — Late/absent/half-day rules
3. `xhrm_overtime_rule` — OT rate configuration
4. `xhrm_financial_year` — Financial year periods
5. `xhrm_tax_slab` — Tax brackets per financial year
6. `xhrm_holiday` — Public holiday calendar
7. `xhrm_payroll_run` — Payroll batch processing records
8. `xhrm_payslip` — Individual employee payslips
9. `xhrm_payslip_item` — Payslip line items (earnings/deductions breakdown)
10. `xhrm_employee_loan` — Employee loans and advances

### New User Role
- **Finance Manager** — Can approve payroll, manage tax slabs, configure financial year

### Build Phases
- **Phase 1:** Plugin skeleton, DB migration, salary components, attendance/OT rules, holiday calendar (~1 week)
- **Phase 2:** Financial year, tax slabs, tax engine (~3-4 days)
- **Phase 3:** Payroll calculation engine, payroll run generation (~1-2 weeks)
- **Phase 4:** Approval workflow, payslips, PDF, email, employee portal (~1 week)

### Detailed Plan
See: `CONTEXT/payroll_module_plan.md`

### Screen Mockups
See: `CONTEXT/mockups/` folder

---

## File Structure Reference

```
XHRM/
├── src/
│   ├── client/                    # Vue.js frontend
│   │   └── src/
│   │       ├── XHRMAttendancePlugin/   # Attendance pages (modified)
│   │       ├── XHRMPasswordManagerPlugin/ # Password Manager UI
│   │       └── core/              # Shared components & services
│   ├── plugins/                   # PHP backend plugins
│   │   ├── XHRMAttendancePlugin/  # Attendance backend
│   │   ├── XHRMPasswordManagerPlugin/ # Password Manager backend
│   │   ├── XHRMPimPlugin/         # PIM (has EmployeeSalary, PayPeriod entities)
│   │   ├── XHRMAdminPlugin/       # Admin (has WorkShift entity)
│   │   ├── XHRMCorePlugin/        # Core (has UserRole, Module entities)
│   │   └── ... (20+ other plugins)
│   └── lib/                       # Framework libraries
├── web/
│   ├── api/
│   │   └── attendance_import.php  # ZKTeco sync API endpoint
│   ├── dist/                      # Compiled Vue.js frontend (committed)
│   └── index.php                  # Application entry point
├── tools/
│   └── zkteco-sync/
│       ├── sync.py                # Main sync script
│       ├── run_sync.bat           # Interactive menu
│       ├── config.ini             # ZKTeco + XHRM config
│       └── attendance.db          # Local SQLite database
└── CONTEXT/                       # This folder — project documentation
```

---

## Quick Reference

### Build Frontend Locally
```bash
cd src/client
npm install
npm run build
# Compiled files go to web/dist/ — commit and push
```

### Deploy to Server
```bash
git add -A
git commit -m "description"
git push
# Hostinger auto-pulls, or manually git pull on server
```

### Run ZKTeco Sync
```bash
cd tools/zkteco-sync
run_sync.bat
# Choose option 1 for full sync
```

### Important Config Files
- `src/config/config.yaml` — Main app config
- `tools/zkteco-sync/config.ini` — Sync tool config
- `web/.htaccess` — Apache routing rules
