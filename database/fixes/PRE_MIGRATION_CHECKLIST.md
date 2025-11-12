# Pre-Migration Checklist: Users & Employees Table Merge

## ⚠️ CRITICAL: Read This First

**This migration will:**
- ✅ Merge `users` and `employees` tables into single `users` table
- ✅ Update all foreign key references
- ✅ Create backward compatibility view
- ✅ Preserve all existing data
- ⚠️ Rename old tables to `users_old` and `employees_old`
- ⚠️ Require application code deployment

**Estimated Downtime:** 5-10 minutes (depending on data size)

---

## Pre-Migration Checklist

### 1. Environment Verification

- [ ] **Running on TEST/STAGING server first?** (DO NOT run on production first!)
- [ ] **Database backup completed?**
- [ ] **Application maintenance mode enabled?**
- [ ] **All users logged out?**
- [ ] **No active KPI review sessions?**

### 2. Database Backup

```powershell
# PowerShell command
cd c:\xampp\mysql\bin
$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
.\mysqldump.exe -u root --single-transaction --routines --triggers kpi_portal > "c:\xampp\htdocs\KPI-portal\database\backup_before_merge_$timestamp.sql"
```

- [ ] **Backup file created successfully?**
- [ ] **Backup file size looks correct?** (should be several MB)
- [ ] **Can open backup file and see SQL content?**

### 3. Data Verification (BEFORE Migration)

Run these queries and **record the results**:

```sql
-- Count users
SELECT 'users' as table_name, COUNT(*) as count FROM users;

-- Count employees
SELECT 'employees' as table_name, COUNT(*) as count FROM employees;

-- Check for orphaned records
SELECT 'Users without employees' as issue, COUNT(*) as count
FROM users u
LEFT JOIN employees e ON u.user_id = e.user_id
WHERE e.employee_id IS NULL;

SELECT 'Employees without users' as issue, COUNT(*) as count
FROM employees e
LEFT JOIN users u ON e.user_id = u.user_id
WHERE u.user_id IS NULL;

-- Sample data check
SELECT u.user_id, u.email, e.employee_code, e.first_name, e.last_name
FROM users u
JOIN employees e ON u.user_id = e.user_id
LIMIT 5;
```

**Record Results:**
- Users count: ___________
- Employees count: ___________
- Users without employees: ___________ (should be 0)
- Employees without users: ___________ (should be 0)
- Sample data looks correct: ✓ / ✗

### 4. Dependency Check

- [ ] **All KPI assignments saved?**
- [ ] **No pending KPI agreements?**
- [ ] **No active email sending processes?**
- [ ] **No scheduled tasks running?**

```sql
-- Check for pending operations
SELECT 'Pending KPI agreements' as check_item, COUNT(*) as count
FROM employee_kpis
WHERE agreement_status = 'PENDING';

SELECT 'Active KPI periods' as check_item, COUNT(*) as count
FROM review_periods
WHERE status = 'ACTIVE';
```

### 5. Code Readiness

- [ ] **Updated User_model.php ready?**
- [ ] **Updated Employee_model.php ready?**
- [ ] **All code changes reviewed?**
- [ ] **Deployment plan prepared?**

### 6. Migration Script Review

Open `database/fixes/fix_08_merge_users_employees.sql` and verify:

- [ ] **Script syntax looks correct?**
- [ ] **Backup steps included?**
- [ ] **Foreign key updates included?**
- [ ] **Verification queries included?**
- [ ] **Rollback procedure documented?**

### 7. Testing Environment

- [ ] **Test server available?**
- [ ] **Test server has same MySQL version as production?**
- [ ] **Test data matches production structure?**
- [ ] **Test server accessible?**

### 8. Communication

- [ ] **Users notified of maintenance window?**
- [ ] **Stakeholders informed?**
- [ ] **Support team on standby?**
- [ ] **Rollback plan communicated?**

---

## Migration Execution Checklist

### Phase 1: Preparation (15 minutes before)

- [ ] **Enable maintenance mode**
  ```php
  // In config.php or create maintenance.html
  ```
- [ ] **Stop all cron jobs**
- [ ] **Verify no active sessions**
  ```sql
  SELECT COUNT(*) FROM sessions WHERE last_activity > UNIX_TIMESTAMP() - 300;
  ```
- [ ] **One final backup**

### Phase 2: Migration (10 minutes)

- [ ] **Connect to MySQL**
  ```bash
  mysql -u root kpi_portal
  ```
- [ ] **Run migration script**
  ```sql
  source c:/xampp/htdocs/KPI-portal/database/fixes/fix_08_merge_users_employees.sql
  ```
- [ ] **Watch for errors** (should show "MIGRATION COMPLETED SUCCESSFULLY!")
- [ ] **Review verification output**

### Phase 3: Verification (5 minutes)

- [ ] **Check data counts match**
  ```sql
  SELECT COUNT(*) FROM users;  -- Should match old users count
  SELECT COUNT(*) FROM employees;  -- Should work (it's a view)
  ```
- [ ] **Verify foreign keys**
  ```sql
  SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE
  WHERE TABLE_SCHEMA = 'kpi_portal'
  AND REFERENCED_TABLE_NAME = 'users';
  -- Should show ~10 foreign keys
  ```
- [ ] **Test view works**
  ```sql
  SELECT * FROM employees LIMIT 5;  -- Should return data
  ```
- [ ] **Check for orphaned records**
  ```sql
  -- Should return 0 rows
  SELECT COUNT(*) FROM employee_kpis ek
  LEFT JOIN users u ON ek.employee_id = u.user_id
  WHERE u.user_id IS NULL;
  ```

### Phase 4: Code Deployment (5 minutes)

- [ ] **Deploy updated User_model.php**
- [ ] **Deploy updated Employee_model.php**
- [ ] **Clear application cache** (if any)
- [ ] **Verify file permissions**

### Phase 5: Smoke Testing (10 minutes)

- [ ] **Test login**
  - Try to login with existing user
  - Verify redirect to dashboard
- [ ] **Test employee list**
  - Admin views all employees
  - Manager views team members
- [ ] **Test employee search**
  - Search by name
  - Search by code
- [ ] **Test KPI operations**
  - View employee KPIs
  - Assign new KPI
  - Edit KPI weightage
- [ ] **Test email** (if possible)
  - Send test notification

### Phase 6: Go Live (2 minutes)

- [ ] **Disable maintenance mode**
- [ ] **Restart cron jobs**
- [ ] **Monitor error logs**
  ```powershell
  Get-Content "c:\xampp\htdocs\KPI-portal\application\logs\log-$(Get-Date -Format 'yyyy-MM-dd').php" -Wait -Tail 50
  ```
- [ ] **Notify users system is back online**

---

## Post-Migration Monitoring (24-48 hours)

### Immediate (First Hour)

- [ ] **Watch error logs continuously**
- [ ] **Monitor user logins**
- [ ] **Check for any user complaints**
- [ ] **Verify KPI operations working**

### First Day

- [ ] **Check error logs every 2 hours**
- [ ] **Verify all features working**
- [ ] **Monitor database performance**
  ```sql
  SHOW PROCESSLIST;
  ```
- [ ] **Check slow query log**

### First Week

- [ ] **Daily log review**
- [ ] **Collect user feedback**
- [ ] **Monitor system performance**
- [ ] **Verify data integrity**
  ```sql
  -- Run weekly integrity check
  SELECT 'Active users' as metric, COUNT(*) as count 
  FROM users WHERE deleted_at IS NULL;
  
  SELECT 'Users with KPIs' as metric, COUNT(DISTINCT employee_id) as count
  FROM employee_kpis WHERE deleted_at IS NULL;
  ```

---

## Rollback Checklist (If Needed)

### If Rollback Needed BEFORE Table Rename:

- [ ] **Stop migration immediately**
- [ ] **Run rollback commands**
  ```sql
  DROP TABLE IF EXISTS users_new;
  DROP TABLE IF EXISTS user_employee_mapping;
  ```
- [ ] **Verify old tables intact**
- [ ] **Resume normal operations**

### If Rollback Needed AFTER Table Rename:

- [ ] **Enable maintenance mode**
- [ ] **Run rollback commands**
  ```sql
  RENAME TABLE users TO users_new;
  RENAME TABLE users_old TO users;
  RENAME TABLE employees_old TO employees;
  ```
- [ ] **Restore foreign keys** (see migration script for FK definitions)
- [ ] **Restore original application code**
- [ ] **Test login and basic operations**
- [ ] **Disable maintenance mode**
- [ ] **Notify stakeholders**

---

## Success Criteria

Migration is considered successful if:

- ✅ All data migrated (counts match)
- ✅ No orphaned records
- ✅ All foreign keys working
- ✅ Login works correctly
- ✅ Employee list displays
- ✅ Search functionality works
- ✅ KPI operations functional
- ✅ No errors in logs
- ✅ No user complaints

---

## Emergency Contacts

**Technical Team:**
- Database Admin: __________________
- Application Developer: __________________
- System Administrator: __________________

**Escalation:**
- Technical Lead: __________________
- Project Manager: __________________

---

## Notes & Observations

Use this space to record any issues, observations, or deviations from the plan:

```
Date/Time: _______________
Issue: ___________________
Resolution: ______________
___________________________
___________________________
___________________________
```

---

## Sign-Off

### Pre-Migration Review

- [ ] All checklist items completed
- [ ] Backup verified
- [ ] Test environment successful
- [ ] Stakeholders notified

**Reviewed by:** ___________________  
**Date:** ___________________  
**Signature:** ___________________

### Post-Migration Approval

- [ ] Migration successful
- [ ] All tests passed
- [ ] No critical errors
- [ ] Production stable

**Approved by:** ___________________  
**Date:** ___________________  
**Signature:** ___________________

---

*Checklist Version 1.0 - November 7, 2025*
*KPI Portal - Users & Employees Table Merge*
