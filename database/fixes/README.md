# KPI Portal Database Fixes

## Overview
This directory contains SQL scripts to fix identified database issues in the KPI Portal system.

## ⚠️ IMPORTANT - BACKUP FIRST!
Before running ANY of these scripts, **BACKUP YOUR DATABASE**:

```bash
# Full backup
mysqldump -u root kpi_portal > backup_kpi_portal_$(date +%Y%m%d_%H%M%S).sql

# Or via phpMyAdmin: Export > SQL format
```

## Quick Start

### Option 1: Run All Fixes (Recommended)
```bash
cd c:\xampp\htdocs\KPI-portal\database\fixes
C:\xampp\mysql\bin\mysql.exe -u root kpi_portal < MASTER_FIX_ALL.sql
```

### Option 2: Run Individual Fixes
Execute only the fixes you need in this order:

```bash
# Critical fixes first
mysql -u root kpi_portal < fix_01_remove_duplicate_imran.sql
mysql -u root kpi_portal < fix_02_correct_weighted_score_formula.sql

# Then other fixes
mysql -u root kpi_portal < fix_03_sync_manager_systems.sql
mysql -u root kpi_portal < fix_04_remove_unused_tables.sql
mysql -u root kpi_portal < fix_05_add_performance_indexes.sql
mysql -u root kpi_portal < fix_06_document_score_scale.sql
mysql -u root kpi_portal < fix_07_add_soft_delete.sql
```

## Fix Details

### 🔥 CRITICAL (Fix Immediately)

#### Fix #1: Remove Duplicate Imran Shaikh
**File:** `fix_01_remove_duplicate_imran.sql`

**Problem:** Imran Shaikh exists twice in database
- `emp-imran-001` (EMP999) - older record
- `00c28abb-b68e-414a-ad59-c063ff5f2787` (1440) - newer record

**Solution:** Merges records, keeps older one, deletes duplicate

**Impact:** Prevents data confusion, fixes reporting structure

---

#### Fix #2: Weighted Score Formula Verification
**File:** `fix_02_correct_weighted_score_formula.sql`

**Problem:** Confusion about whether formula `weightage × score / 5` is correct

**Solution:** Adds documentation and verification. Formula is CORRECT if:
- Score scale: 0-5
- Weightage: 0-100%
- Final score: 0-100

**Impact:** Clarifies scoring system, adds comments

---

### ⚠️ HIGH PRIORITY

#### Fix #3: Sync Manager Systems
**File:** `fix_03_sync_manager_systems.sql`

**Problem:** Two manager tracking systems exist:
- OLD: `employee_work_info.reporting_manager_id`
- NEW: `employee_reporting_managers` table

**Solution:** Syncs both systems, optionally removes redundant column

**Impact:** Single source of truth for manager relationships

---

#### Fix #5: Add Performance Indexes
**File:** `fix_05_add_performance_indexes.sql`

**Problem:** Missing composite indexes causing slow queries

**Solution:** Adds optimized indexes:
- `idx_employee_period` on `(employee_id, review_period_id)`
- `idx_status_period` on `(status, review_period_id)`
- `idx_manager_employees` on `(manager_id, is_primary)`
- Full-text search indexes

**Impact:** 2-10x faster query performance

---

#### Fix #7: Add Soft Delete
**File:** `fix_07_add_soft_delete.sql`

**Problem:** Hard deletes cause permanent data loss

**Solution:** 
- Adds `deleted_at` and `deleted_by` columns
- Creates soft delete procedures
- Creates views filtering deleted records
- Adds audit triggers

**Impact:** Recoverable deletions, audit trail, compliance

---

### 📋 MEDIUM PRIORITY

#### Fix #4: Remove Unused Tables
**File:** `fix_04_remove_unused_tables.sql`

**Problem:** Empty tables taking up space:
- `employee_kpi_assignments` (0 rows)
- `kpi_assignment_logs` (0 rows)

**Solution:** Backs up structures, removes unused tables

**Impact:** Cleaner schema, less confusion

---

#### Fix #6: Document Score Scale
**File:** `fix_06_document_score_scale.sql`

**Problem:** No documentation of 0-5 score scale

**Solution:**
- Creates `kpi_score_scale` reference table
- Creates `performance_grade_scale` table (A+, A, B, C, D, F)
- Creates helper views and functions
- Adds validation triggers

**Impact:** Clear scoring guidelines, better UX

---

## Verification After Applying Fixes

### 1. Check for Duplicates
```sql
SELECT first_name, last_name, COUNT(*) 
FROM employees 
GROUP BY first_name, last_name 
HAVING COUNT(*) > 1;
```
Expected: 0 rows

### 2. Check Weightage Totals
```sql
SELECT 
    ek.employee_id,
    SUM(ks.weightage) as total
FROM employee_kpis ek
JOIN kpi_scores ks ON ek.employee_kpi_id = ks.employee_kpi_id
GROUP BY ek.employee_id, ek.review_period_id
HAVING ABS(total - 100) > 0.1;
```
Expected: 0 rows (all should equal 100%)

### 3. Check Indexes
```sql
SHOW INDEX FROM employee_kpis;
```
Expected: Should see new composite indexes

### 4. Check Soft Delete Columns
```sql
SHOW COLUMNS FROM employee_kpis LIKE 'deleted%';
```
Expected: deleted_at and deleted_by columns

### 5. Test Performance View
```sql
SELECT * FROM v_employee_performance_summary;
```
Expected: Shows employee scores with grades

---

## Application Code Changes Required

After applying database fixes, update these files:

### 1. Employee_model.php
```php
// Update get_manager() to use employee_reporting_managers
public function get_manager($employee_id) {
    $this->db->select('e.*')
             ->from('employee_reporting_managers erm')
             ->join('employees e', 'erm.manager_id = e.employee_id')
             ->where('erm.employee_id', $employee_id)
             ->where('erm.is_primary', 1);
    return $this->db->get()->row();
}
```

### 2. Add Soft Delete Methods
```php
// In all models that handle deletions
public function soft_delete($id, $deleted_by) {
    return $this->db->where('id', $id)
                    ->update($this->table, [
                        'deleted_at' => date('Y-m-d H:i:s'),
                        'deleted_by' => $deleted_by
                    ]);
}

public function restore($id) {
    return $this->db->where('id', $id)
                    ->update($this->table, [
                        'deleted_at' => NULL,
                        'deleted_by' => NULL
                    ]);
}
```

### 3. Filter Deleted Records in Queries
```php
// Add to all SELECT queries
$this->db->where('deleted_at IS NULL');
```

---

## Rollback Instructions

Each fix script includes rollback commands at the bottom. To rollback a specific fix:

```sql
-- Example: Rollback soft delete
ALTER TABLE employee_kpis DROP COLUMN deleted_at, DROP COLUMN deleted_by;
DROP PROCEDURE IF EXISTS sp_soft_delete_employee_kpi;
-- ... (see individual fix file for complete rollback)
```

---

## Support

If you encounter issues:

1. Check the error message carefully
2. Verify your backup is complete
3. Try running fixes individually instead of master script
4. Check foreign key constraints
5. Verify data integrity before and after

---

## Maintenance Schedule

After applying fixes, set up regular maintenance:

```sql
-- Weekly: Optimize tables
OPTIMIZE TABLE employee_kpis, kpi_scores;

-- Monthly: Analyze for query optimizer
ANALYZE TABLE employee_kpis, kpi_scores, employee_reporting_managers;

-- Quarterly: Purge old deleted records
CALL sp_purge_old_deleted_records(90); -- Delete soft-deleted records older than 90 days
```

---

## Version History

- **v1.0** (2025-11-06): Initial fix scripts created
  - Fix #1: Remove duplicate Imran Shaikh
  - Fix #2: Verify weighted score formula
  - Fix #3: Sync manager systems
  - Fix #4: Remove unused tables
  - Fix #5: Add performance indexes
  - Fix #6: Document score scale
  - Fix #7: Add soft delete capability
