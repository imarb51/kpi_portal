# Users & Employees Merge - Code Changes Summary

## Overview

This document summarizes all code changes made to support the merged `users` and `employees` tables.

---

## Database Changes

### New Table Structure: `users`

**File:** `database/fixes/fix_08_merge_users_employees.sql`

- Merged `users` + `employees` tables into single `users` table
- Added employee fields: `employee_code`, `first_name`, `last_name`, `middle_name`, `phone_number`, `profile_picture_url`
- Added soft delete: `deleted_at`, `deleted_by`
- Created backward compatibility view: `employees` (points to `users` table)
- Updated all foreign keys to reference `users` table
- Created stored procedure: `sp_soft_delete_user()`

---

## Application Code Changes

### 1. User_model.php

**File:** `application/models/User_model.php`

#### Modified Methods:

**get_by_email()**
```php
// ADDED: Soft delete filter
->where('deleted_at IS NULL')
```

**get_by_id()**
```php
// ADDED: Soft delete filter
->where('deleted_at IS NULL')
```

**create()**
```php
// ADDED: Employee fields
'employee_code' => $data['employee_code'],
'first_name' => $data['first_name'],
'last_name' => $data['last_name'],
'middle_name' => isset($data['middle_name']) ? $data['middle_name'] : NULL,
'phone_number' => isset($data['phone_number']) ? $data['phone_number'] : NULL,
'profile_picture_url' => isset($data['profile_picture_url']) ? $data['profile_picture_url'] : NULL,
```

#### New Methods Added:

1. **get_with_details($user_id)**
   - Replaces old get_employee_by_user_id()
   - Returns user with work info and department in single query
   - Includes soft delete filter

2. **get_by_employee_code($employee_code)**
   - Find user by employee code
   - Includes soft delete filter

3. **get_full_name($user_id)**
   - Returns formatted full name (first + middle + last)

4. **update($user_id, $data)**
   - Update user information (name, email, phone, etc.)
   - Only updates allowed fields for security

5. **soft_delete($user_id, $deleted_by)**
   - Soft delete user (sets deleted_at, deleted_by)
   - Sets is_active = 0

6. **restore($user_id)**
   - Restore soft-deleted user
   - Clears deleted_at, deleted_by
   - Sets is_active = 1

7. **get_all_active($limit, $offset)**
   - Get all active users with pagination
   - Excludes soft-deleted users

8. **search($search_term, $limit)**
   - Search users by name, email, or employee code
   - Excludes soft-deleted users

---

### 2. Employee_model.php

**File:** `application/models/Employee_model.php`

#### Modified Methods:

**get_by_user_id($user_id)**
```php
// CHANGED: FROM 'employees e' TO 'users u'
->from('users u')
// ADDED: employee_id alias for backward compatibility
->select('u.user_id as employee_id, u.user_id, u.employee_code, ...')
// ADDED: Soft delete filter
->where('u.deleted_at IS NULL')
```

**get_by_id($employee_id)**
```php
// CHANGED: FROM 'employees e' TO 'users u'
->from('users u')
// ADDED: employee_id alias
->select('u.user_id as employee_id, u.user_id, ...')
// ADDED: Soft delete filter
->where('u.deleted_at IS NULL')
```

**get_by_employee_code($employee_code)** ← NEW METHOD
```php
// NEW: Get employee by employee code
->from('users u')
->where('u.employee_code', $employee_code)
->where('u.deleted_at IS NULL')
```

**get_direct_reports($manager_id)**
```php
// CHANGED: FROM 'employees e' TO 'users u'
->join('users u', 'erm.employee_id = u.user_id')
// ADDED: Soft delete filter
->where('u.deleted_at IS NULL')
// CHANGED: GROUP BY from 'e.employee_id' to 'u.user_id'
->group_by('u.user_id')
```

**get_employee_managers($employee_id)**
```php
// CHANGED: FROM 'employees e' TO 'users u'
->from('employee_reporting_managers erm')
->join('users u', 'erm.manager_id = u.user_id')
// REMOVED: JOIN to users table (no longer needed)
// CHANGED: SELECT uses u.email directly (not separate join)
->select('u.user_id as employee_id, u.employee_code, u.first_name, u.last_name, u.email, ...')
// ADDED: Soft delete filter
->where('u.deleted_at IS NULL')
```

**search($search_term)**
```php
// CHANGED: FROM 'employees e' TO 'users u'
->from('users u')
->join('employee_work_info ewi', 'u.user_id = ewi.employee_id', 'left')
// CHANGED: All LIKE clauses use 'u.' prefix
->like('u.first_name', $search_term)
->or_like('u.last_name', $search_term)
->or_like('u.employee_code', $search_term)
// ADDED: Soft delete filter
->where('u.deleted_at IS NULL')
```

**get_all($limit, $offset)**
```php
// CHANGED: FROM 'employees e' TO 'users u'
->from('users u')
->join('employee_work_info ewi', 'u.user_id = ewi.employee_id', 'left')
// CHANGED: All SELECT fields use 'u.' prefix
->select('u.user_id as employee_id, u.employee_code, ...')
// ADDED: Soft delete filter
->where('u.deleted_at IS NULL')
```

**get_all_managers($employee_id)**
```php
// CHANGED: FROM 'employees e' TO 'users u'
->join('users u', 'erm.manager_id = u.user_id')
// REMOVED: JOIN to users for email (no longer needed)
// CHANGED: SELECT uses u.email directly
->select('u.user_id as employee_id, u.first_name, u.last_name, u.email, ...')
// ADDED: Soft delete filter
->where('u.deleted_at IS NULL')
```

**get_all_hr_spokespersons($employee_id)**
```php
// CHANGED: FROM 'employees e' TO 'users u'
->join('users u', 'ehr.hr_id = u.user_id')
// REMOVED: JOIN to users for email
// CHANGED: SELECT uses u.email directly
->select('u.user_id as employee_id, u.first_name, u.last_name, u.email, ...')
// ADDED: Soft delete filter
->where('u.deleted_at IS NULL')
```

---

## Pattern Changes Summary

### 1. Table References

**Before:**
```php
->from('employees e')
->join('users u', 'e.user_id = u.user_id', 'left')
```

**After:**
```php
->from('users u')
// No need to join users table anymore!
```

### 2. Email Access

**Before:**
```php
->from('employees e')
->join('users u', 'e.user_id = u.user_id', 'left')
->select('e.*, u.email')
```

**After:**
```php
->from('users u')
->select('u.*, u.email')  // Email is directly in users table
```

### 3. Soft Delete Filter

**Added to ALL queries:**
```php
->where('u.deleted_at IS NULL')
```

### 4. Backward Compatibility Alias

**Added to maintain compatibility:**
```php
->select('u.user_id as employee_id, u.user_id, ...')
```
This allows old code expecting `employee_id` to continue working.

---

## Testing Checklist

After deployment, test these features:

### Authentication & User Management
- [ ] Login with email/password
- [ ] User profile display
- [ ] User creation
- [ ] User update
- [ ] Password reset
- [ ] Last login timestamp updates

### Employee Operations
- [ ] View employee list
- [ ] Search employees by name
- [ ] Search employees by code
- [ ] View employee details
- [ ] Get employee full name

### Manager Operations
- [ ] View team members (direct reports)
- [ ] Get employee managers
- [ ] Multiple managers support
- [ ] Primary manager identification

### KPI Operations
- [ ] View employee KPIs
- [ ] Assign KPIs to employee
- [ ] Edit KPI weightage
- [ ] Save KPI scores
- [ ] KPI agreement workflow

### Email Notifications
- [ ] KPI agreement notifications
- [ ] KPI query notifications
- [ ] Manager emails correct
- [ ] HR spokesperson emails correct

### Soft Delete
- [ ] Soft delete user
- [ ] Deleted user not in lists
- [ ] Deleted user can't login
- [ ] Restore deleted user
- [ ] Restored user can login

---

## Breaking Changes

### NONE! 

This migration is **fully backward compatible** thanks to:

1. **employees VIEW** - Old queries selecting from `employees` table continue to work
2. **employee_id alias** - Queries expecting `employee_id` field get it aliased from `user_id`
3. **All foreign keys updated** - References to `employees` table updated to `users` table

### Deprecated (but still working):

- Selecting from `employees` table directly (use `users` instead)
- Using `employee_id` as separate field (it's now an alias for `user_id`)

---

## Performance Improvements

### Before Migration
```php
// 2 table JOIN every time
$this->db->select('e.*, u.email')
         ->from('employees e')
         ->join('users u', 'e.user_id = u.user_id', 'left')
         ->where('e.employee_id', $id);
```

### After Migration
```php
// Single table query!
$this->db->select('u.*')
         ->from('users u')
         ->where('u.user_id', $id)
         ->where('u.deleted_at IS NULL');
```

**Result:** 
- ⚡ Faster queries (no JOIN overhead)
- ⚡ Better index utilization
- ⚡ Reduced storage (no duplicate data)

---

## File Summary

### Files Modified:
1. ✅ `application/models/User_model.php` - Enhanced with 8 new methods
2. ✅ `application/models/Employee_model.php` - Updated 10 methods, added 1 new method

### Files Created:
1. ✅ `database/fixes/fix_08_merge_users_employees.sql` - Migration script
2. ✅ `database/fixes/MIGRATION_GUIDE_USERS_EMPLOYEES.md` - Complete migration guide
3. ✅ `database/fixes/CODE_CHANGES_SUMMARY.md` - This file

### Total Lines Changed:
- User_model.php: ~120 lines added/modified
- Employee_model.php: ~80 lines modified

---

## Rollback Information

If needed, rollback is possible:

1. **Before Step 7 (table rename):** Simple DROP tables
2. **After Step 7:** Rename tables back + restore foreign keys

See `MIGRATION_GUIDE_USERS_EMPLOYEES.md` for detailed rollback procedures.

---

## Next Steps

1. ✅ Backup database
2. ✅ Review migration script
3. ⏳ Test on staging server
4. ⏳ Execute migration on production
5. ⏳ Deploy updated code
6. ⏳ Monitor for 24-48 hours
7. ⏳ Clean up old tables after 1-2 weeks

---

*Migration prepared: November 7, 2025*
*CodeIgniter 3.x + MySQL/MariaDB 10.4.32*
