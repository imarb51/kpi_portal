# Quick Reference: Users & Employees Merge

## What Changed?

**OLD:** Two separate tables (`users` + `employees`)  
**NEW:** One merged table (`users`)

---

## Quick SQL Query Migration

### Before (OLD):
```sql
SELECT e.*, u.email
FROM employees e
LEFT JOIN users u ON e.user_id = u.user_id
WHERE e.employee_code = '1440';
```

### After (NEW):
```sql
SELECT u.*
FROM users u
WHERE u.employee_code = '1440'
AND u.deleted_at IS NULL;
```

---

## Quick Code Migration

### Getting Employee Data

**Before:**
```php
// Had to join two tables
$this->db->select('e.*, u.email')
         ->from('employees e')
         ->join('users u', 'e.user_id = u.user_id')
         ->where('e.employee_id', $id)
         ->get()->row();
```

**After:**
```php
// Single table!
$this->db->select('u.*')
         ->from('users u')
         ->where('u.user_id', $id)
         ->where('u.deleted_at IS NULL')
         ->get()->row();
```

### Getting User Email

**Before:**
```php
// Had to join to get email
$this->db->select('e.first_name, e.last_name, u.email')
         ->from('employees e')
         ->join('users u', 'e.user_id = u.user_id')
```

**After:**
```php
// Email is in users table!
$this->db->select('u.first_name, u.last_name, u.email')
         ->from('users u')
         ->where('u.deleted_at IS NULL')
```

---

## Important Filters

### ALWAYS Add Soft Delete Filter:

```php
->where('deleted_at IS NULL')
```

### For Backward Compatibility:

```php
// Add alias so old code works
->select('u.user_id as employee_id, u.*')
```

---

## Model Method Quick Reference

### User_model (NEW Methods)

```php
// Get user with work info
$this->User_model->get_with_details($user_id);

// Get by employee code
$this->User_model->get_by_employee_code('1440');

// Get full name
$this->User_model->get_full_name($user_id);

// Update user
$this->User_model->update($user_id, $data);

// Soft delete
$this->User_model->soft_delete($user_id, $deleted_by);

// Restore
$this->User_model->restore($user_id);

// Search users
$this->User_model->search('John');

// Get all active
$this->User_model->get_all_active(20, 0);
```

### Employee_model (UPDATED Methods)

```php
// All these now use 'users' table internally:
$this->Employee_model->get_by_id($employee_id);
$this->Employee_model->get_by_user_id($user_id);
$this->Employee_model->get_by_employee_code('1440');
$this->Employee_model->get_direct_reports($manager_id);
$this->Employee_model->search('John');
$this->Employee_model->get_all(20, 0);
```

---

## Field Name Changes

| Old Field | New Field | Notes |
|-----------|-----------|-------|
| `e.employee_id` | `u.user_id` | Same value, different column |
| `e.employee_code` | `u.employee_code` | Now in users table |
| `e.first_name` | `u.first_name` | Now in users table |
| `e.last_name` | `u.last_name` | Now in users table |
| `e.employee_email` | `u.email` | Merged into email field |
| N/A | `u.deleted_at` | NEW: Soft delete timestamp |
| N/A | `u.deleted_by` | NEW: Who deleted the user |

---

## Common Patterns

### Pattern 1: Get Employee with Manager

**Before:**
```php
$this->db->select('e.*, u.email, m.first_name as manager_name')
         ->from('employees e')
         ->join('users u', 'e.user_id = u.user_id')
         ->join('employee_work_info ewi', 'e.employee_id = ewi.employee_id')
         ->join('employees m', 'ewi.reporting_manager_id = m.employee_id')
```

**After:**
```php
$this->db->select('u.*, m.first_name as manager_name')
         ->from('users u')
         ->join('employee_work_info ewi', 'u.user_id = ewi.employee_id')
         ->join('users m', 'ewi.reporting_manager_id = m.user_id')
         ->where('u.deleted_at IS NULL')
         ->where('m.deleted_at IS NULL')
```

### Pattern 2: Search Employees

**Before:**
```php
$this->db->like('e.first_name', $term)
         ->or_like('e.last_name', $term)
         ->from('employees e')
```

**After:**
```php
$this->db->like('u.first_name', $term)
         ->or_like('u.last_name', $term)
         ->from('users u')
         ->where('u.deleted_at IS NULL')
```

### Pattern 3: Create New Employee

**Before:**
```php
// Step 1: Create user
$user_id = $this->User_model->create([
    'email' => $email,
    'password' => $password
]);

// Step 2: Create employee
$this->db->insert('employees', [
    'user_id' => $user_id,
    'employee_code' => '1440',
    'first_name' => 'John',
    'last_name' => 'Doe'
]);
```

**After:**
```php
// One step!
$user_id = $this->User_model->create([
    'email' => $email,
    'password' => $password,
    'employee_code' => '1440',
    'first_name' => 'John',
    'last_name' => 'Doe'
]);
```

---

## Migration Steps (Quick)

1. **Backup:**
   ```bash
   mysqldump -u root kpi_portal > backup.sql
   ```

2. **Run Migration:**
   ```bash
   mysql -u root kpi_portal < database/fixes/fix_08_merge_users_employees.sql
   ```

3. **Deploy Code:**
   - Copy updated `User_model.php`
   - Copy updated `Employee_model.php`

4. **Test:**
   - Login
   - View employees
   - Search
   - Create KPIs

---

## Troubleshooting

### Error: "Unknown column 'e.employee_id'"
**Fix:** Change `e.employee_id` to `u.user_id`

### Error: "Table 'employees' doesn't exist"
**Fix:** Change `FROM employees e` to `FROM users u`

### Login not working
**Fix:** Add `->where('deleted_at IS NULL')` to auth query

### Employee not showing in list
**Fix:** Check if soft-deleted: `SELECT * FROM users WHERE deleted_at IS NOT NULL`

---

## Testing Checklist

Quick tests after migration:

```bash
# Test 1: Login
✓ Can login with email/password

# Test 2: View employees
✓ Employee list shows all active employees

# Test 3: Search
✓ Search by name works
✓ Search by code works

# Test 4: Manager view
✓ Manager sees their team

# Test 5: KPI operations
✓ Can assign KPIs
✓ Can edit KPIs
✓ Can view KPIs
```

---

## Help

**Full Documentation:**
- `database/fixes/MIGRATION_GUIDE_USERS_EMPLOYEES.md`
- `database/fixes/CODE_CHANGES_SUMMARY.md`

**Migration Script:**
- `database/fixes/fix_08_merge_users_employees.sql`

**Updated Models:**
- `application/models/User_model.php`
- `application/models/Employee_model.php`

---

*Quick Reference v1.0 - November 7, 2025*
