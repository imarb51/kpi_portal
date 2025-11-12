# Users & Employees Table Merge - Migration Guide

## Overview

This migration consolidates the `users` and `employees` tables into a single `users` table to eliminate redundancy and improve data integrity.

## Current Problem

- **Duplicate Data**: User authentication data in `users` table, employee info in `employees` table
- **Synchronization Issues**: Two tables must be kept in sync via `user_id` foreign key
- **Complexity**: Every query needs JOIN between users and employees
- **Data Integrity Risk**: Possibility of orphaned records or mismatched data

## Solution

Merge both tables into a single `users` table containing:
- Authentication fields (email, password_hash)
- Employee information (employee_code, first_name, last_name, etc.)
- Status and timestamps
- Soft delete support

## Migration Steps

### Step 1: Backup Current Database

```powershell
# PowerShell command to backup database
cd c:\xampp\mysql\bin
.\mysqldump.exe -u root kpi_portal > "c:\xampp\htdocs\KPI-portal\database\backup_before_merge_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql"
```

### Step 2: Review Migration Script

Open and review: `database/fixes/fix_08_merge_users_employees.sql`

**CRITICAL CHECKS:**
1. Verify data counts match (users count = employees count)
2. Check for users without employees
3. Check for employees without users
4. Review foreign key dependencies

### Step 3: Execute Migration (TEST ENVIRONMENT FIRST!)

```sql
-- Connect to MySQL
mysql -u root kpi_portal

-- Source the migration file
source c:/xampp/htdocs/KPI-portal/database/fixes/fix_08_merge_users_employees.sql

-- Watch for any errors
-- Verify all verification queries pass
```

### Step 4: Verify Migration Success

```sql
-- Check new users table
SELECT COUNT(*) FROM users;
SELECT * FROM users LIMIT 5;

-- Check employees view (backward compatibility)
SELECT COUNT(*) FROM employees;
SELECT * FROM employees LIMIT 5;

-- Verify no orphaned records
SELECT 
    'employee_kpis' as table_name,
    COUNT(*) as orphaned_count
FROM employee_kpis ek
LEFT JOIN users u ON ek.employee_id = u.user_id
WHERE u.user_id IS NULL;

-- Check foreign keys
SELECT 
    TABLE_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'kpi_portal'
AND REFERENCED_TABLE_NAME = 'users'
ORDER BY TABLE_NAME;
```

### Step 5: Deploy Updated Application Code

The following files have been updated to work with the merged table:

**Models Updated:**
- ✅ `application/models/User_model.php` - Enhanced with employee fields
- ✅ `application/models/Employee_model.php` - Updated all queries to use `users` table

**Key Changes:**
1. All `employees e` references changed to `users u`
2. Added `deleted_at IS NULL` filters for soft deletes
3. Added `u.user_id as employee_id` aliases for backward compatibility
4. Updated foreign key references in JOINs

### Step 6: Test Application

**Test Checklist:**
- [ ] Login works correctly
- [ ] User profile displays all information
- [ ] Manager can view team members
- [ ] Employee can view their KPIs
- [ ] Admin can view all employees
- [ ] Employee search works
- [ ] KPI assignment works
- [ ] Email notifications work
- [ ] Soft delete works correctly

### Step 7: Monitor for Issues

Watch the error logs:
```powershell
# PowerShell command to monitor logs
Get-Content "c:\xampp\htdocs\KPI-portal\application\logs\log-$(Get-Date -Format 'yyyy-MM-dd').php" -Wait -Tail 50
```

## New Table Structure

### users (merged table)

```sql
CREATE TABLE users (
    -- Authentication
    user_id VARCHAR(36) NOT NULL PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    
    -- Employee Information
    employee_code VARCHAR(50) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    middle_name VARCHAR(100) DEFAULT NULL,
    phone_number VARCHAR(20) DEFAULT NULL,
    profile_picture_url VARCHAR(500) DEFAULT NULL,
    
    -- Status
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL,
    
    -- Soft Delete
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    deleted_by VARCHAR(36) DEFAULT NULL
);
```

## Backward Compatibility

### employees VIEW

A view is created for backward compatibility:

```sql
CREATE OR REPLACE VIEW employees AS
SELECT 
    user_id as employee_id,
    user_id,
    employee_code,
    first_name,
    last_name,
    middle_name,
    phone_number,
    email as employee_email,
    profile_picture_url,
    created_at,
    updated_at
FROM users
WHERE deleted_at IS NULL;
```

**Note:** This view allows old code to continue working temporarily, but you should update all code to use the `users` table directly.

## Code Changes Required

### Example: Old vs New

**OLD CODE (Before Migration):**
```php
// User_model.php
public function get_by_email($email) {
    return $this->db->where('email', $email)
                    ->where('is_active', 1)
                    ->get('users')
                    ->row();
}

// Employee_model.php
public function get_by_user_id($user_id) {
    $this->db->select('e.*, ewi.designation')
             ->from('employees e')
             ->join('employee_work_info ewi', 'e.employee_id = ewi.employee_id', 'left')
             ->where('e.user_id', $user_id);
    return $this->db->get()->row();
}
```

**NEW CODE (After Migration):**
```php
// User_model.php
public function get_by_email($email) {
    return $this->db->where('email', $email)
                    ->where('is_active', 1)
                    ->where('deleted_at IS NULL')  // ← ADDED
                    ->get('users')
                    ->row();
}

// Employee_model.php
public function get_by_user_id($user_id) {
    $this->db->select('u.user_id as employee_id, u.*, ewi.designation')
             ->from('users u')  // ← CHANGED
             ->join('employee_work_info ewi', 'u.user_id = ewi.employee_id', 'left')
             ->where('u.user_id', $user_id)
             ->where('u.deleted_at IS NULL');  // ← ADDED
    return $this->db->get()->row();
}
```

## New Features Added

### 1. Soft Delete Support

```php
// Soft delete a user and all their data
public function soft_delete($user_id, $deleted_by) {
    $this->db->where('user_id', $user_id);
    return $this->db->update('users', [
        'deleted_at' => date('Y-m-d H:i:s'),
        'deleted_by' => $deleted_by,
        'is_active' => 0
    ]);
}

// Restore soft-deleted user
public function restore($user_id) {
    $this->db->where('user_id', $user_id);
    return $this->db->update('users', [
        'deleted_at' => NULL,
        'deleted_by' => NULL,
        'is_active' => 1
    ]);
}
```

### 2. Enhanced User Model

New methods in `User_model.php`:
- `get_with_details()` - Get user with work info and department
- `get_by_employee_code()` - Find user by employee code
- `get_full_name()` - Get formatted full name
- `update()` - Update user information
- `soft_delete()` - Soft delete user
- `restore()` - Restore deleted user
- `get_all_active()` - Get all active users
- `search()` - Search users by name/email/code

## Rollback Procedure

If you need to rollback BEFORE renaming tables (Step 7 in migration):

```sql
DROP TABLE IF EXISTS users_new;
DROP TABLE IF EXISTS user_employee_mapping;
-- Old tables are still intact
```

If you need to rollback AFTER renaming tables:

```sql
-- Rename back
RENAME TABLE users TO users_new;
RENAME TABLE users_old TO users;
RENAME TABLE employees_old TO employees;

-- You'll need to restore foreign keys manually
-- See the original schema for FK definitions
```

## Cleanup (After 1-2 Weeks of Testing)

Once you've verified everything works correctly:

```sql
-- Drop old backup tables
DROP TABLE IF EXISTS employees_old;
DROP TABLE IF EXISTS users_old;
DROP TABLE IF EXISTS backup_users;
DROP TABLE IF EXISTS backup_employees;
DROP TABLE IF EXISTS user_employee_mapping;
```

## Benefits

### Before Migration
- ✗ Two tables to maintain (users + employees)
- ✗ Complex JOINs in every query
- ✗ Risk of data synchronization issues
- ✗ Duplicate information storage
- ✗ No soft delete support

### After Migration
- ✅ Single source of truth for user/employee data
- ✅ Simpler queries (no JOIN needed for basic info)
- ✅ Better data integrity
- ✅ Soft delete support built-in
- ✅ Easier to maintain and extend
- ✅ Enhanced User_model with more methods

## Performance Impact

**Expected Improvements:**
- 🚀 Faster queries (no JOIN for basic user info)
- 🚀 Reduced storage (eliminated duplicate data)
- 🚀 Better index utilization

**Monitoring:**
```sql
-- Check table sizes before/after
SELECT 
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.TABLES
WHERE table_schema = 'kpi_portal'
AND table_name IN ('users', 'users_old', 'employees_old')
ORDER BY (data_length + index_length) DESC;
```

## Support & Troubleshooting

### Common Issues

**Issue: "Table 'employees' doesn't exist" error**
- **Solution:** The view should handle this. Verify view exists: `SHOW CREATE VIEW employees;`

**Issue: Foreign key constraint fails**
- **Solution:** Check if referenced user exists and is not soft-deleted

**Issue: Login not working after migration**
- **Solution:** Verify `deleted_at IS NULL` filter is applied in authentication queries

**Issue: User data not showing**
- **Solution:** Check if user is soft-deleted: `SELECT * FROM users WHERE user_id = '...' AND deleted_at IS NOT NULL`

### Debug Queries

```sql
-- Check active users
SELECT COUNT(*) as active_users FROM users WHERE deleted_at IS NULL;

-- Check deleted users
SELECT COUNT(*) as deleted_users FROM users WHERE deleted_at IS NOT NULL;

-- Find users with missing work_info
SELECT u.user_id, u.employee_code, u.first_name, u.last_name
FROM users u
LEFT JOIN employee_work_info ewi ON u.user_id = ewi.employee_id
WHERE ewi.employee_id IS NULL
AND u.deleted_at IS NULL;
```

## Migration Checklist

- [ ] Backup database
- [ ] Review migration script
- [ ] Test migration on staging/development server first
- [ ] Execute migration on production
- [ ] Verify data integrity
- [ ] Deploy updated application code
- [ ] Test all critical features
- [ ] Monitor error logs for 24-48 hours
- [ ] Clean up old tables after 1-2 weeks

## Contact

For issues or questions during migration, check:
- Application logs: `application/logs/`
- MySQL error log: `c:\xampp\mysql\data\mysql_error.log`
- Create detailed bug reports with:
  - Error message
  - Steps to reproduce
  - Query that failed
  - Expected vs actual result
