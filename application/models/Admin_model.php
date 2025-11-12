<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin Model
 * Handles admin-specific database operations
 */
class Admin_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // ============================================
    // DASHBOARD STATISTICS
    // ============================================

    public function count_employees() {
        return $this->db->where('employee_status', 'ACTIVE')
                        ->from('employee_work_info')
                        ->count_all_results();
    }

    public function count_active_kpis() {
        $active_period = $this->db->where('status', 'ACTIVE')->get('review_periods')->row();
        if (!$active_period) return 0;
        
        return $this->db->where('review_period_id', $active_period->period_id)
                        ->from('employee_kpis')
                        ->count_all_results();
    }

    public function count_departments() {
        return $this->db->where('is_active', 1)
                        ->from('departments')
                        ->count_all_results();
    }

    public function count_pending_requests() {
        // DEPRECATED: Now using employee_agreement_status in employee_kpis table
        // Count employees with REQUESTED_EDIT status instead
        return $this->db->select('COUNT(DISTINCT employee_id) as count')
                        ->from('employee_kpis')
                        ->where('employee_agreement_status', 'REQUESTED_EDIT')
                        ->get()
                        ->row()
                        ->count;
    }

    

    // ============================================
    // EMPLOYEE MANAGEMENT
    // ============================================

    public function get_all_employees($status = NULL) {
        $this->db->select('u.user_id as employee_id, u.*, ewi.designation, ewi.employee_status, d.department_name,
                          u.is_active as user_active,
                          manager.first_name as manager_first_name, 
                          manager.last_name as manager_last_name')
                 ->from('users u')
                 ->join('employee_work_info ewi', 'u.user_id = ewi.employee_id', 'left')
                 ->join('departments d', 'ewi.department_id = d.department_id', 'left')
                 ->join('users manager', 'ewi.reporting_manager_id = manager.user_id', 'left')
                 ->where('u.deleted_at IS NULL');
        
        if ($status) {
            $this->db->where('ewi.employee_status', $status);
        }
        
        $employees = $this->db->order_by('u.first_name', 'ASC')->get()->result();
        
        // Add all managers and HR for each employee
        foreach ($employees as $emp) {
            // Get all reporting managers
            $this->db->select('u.user_id as employee_id, u.employee_code, u.first_name, u.last_name, erm.is_primary')
                     ->from('employee_reporting_managers erm')
                     ->join('users u', 'erm.manager_id = u.user_id')
                     ->where('erm.employee_id', $emp->employee_id)
                     ->where('u.deleted_at IS NULL')
                     ->order_by('erm.is_primary', 'DESC');
            $emp->reporting_managers = $this->db->get()->result();
            
            // Get all HR spokespersons
            $this->db->select('u.user_id as employee_id, u.employee_code, u.first_name, u.last_name, ehr.is_primary')
                     ->from('employee_hr_spokespersons ehr')
                     ->join('users u', 'ehr.hr_id = u.user_id')
                     ->where('ehr.employee_id', $emp->employee_id)
                     ->where('u.deleted_at IS NULL')
                     ->order_by('ehr.is_primary', 'DESC');
            $emp->hr_spokespersons = $this->db->get()->result();
        }
        
        return $employees;
    }

    /**
     * Get managers by department - only employees with MANAGER role from specific department
     */
    public function get_managers_by_department($department_id = null) {
        $this->db->select('u.user_id as employee_id, u.employee_code, u.first_name, u.last_name, 
                          ewi.designation, d.department_name, d.department_id')
                 ->from('users u')
                 ->join('employee_work_info ewi', 'u.user_id = ewi.employee_id', 'left')
                 ->join('departments d', 'ewi.department_id = d.department_id', 'left')
                 ->join('employee_roles er', 'u.user_id = er.employee_id')
                 ->join('roles r', 'er.role_id = r.role_id')
                 ->where('u.deleted_at IS NULL')
                 ->where('ewi.employee_status', 'ACTIVE')
                 ->where('r.role_name', 'MANAGER');
        
        if ($department_id) {
            $this->db->where('ewi.department_id', $department_id);
        }
        
        return $this->db->order_by('u.first_name', 'ASC')->get()->result();
    }

    /**
     * Get HR spokespersons - employees from HR department
     */
    public function get_hr_spokespersons() {
        // Get all employees with HR role (regardless of department)
        $this->db->select('u.user_id as employee_id, u.employee_code, u.first_name, u.last_name, 
                          ewi.designation, d.department_name')
                 ->from('users u')
                 ->join('employee_work_info ewi', 'u.user_id = ewi.employee_id', 'left')
                 ->join('departments d', 'ewi.department_id = d.department_id', 'left')
                 ->join('employee_roles er', 'u.user_id = er.employee_id', 'inner')
                 ->join('roles r', 'er.role_id = r.role_id', 'inner')
                 ->where('u.deleted_at IS NULL')
                 ->where('ewi.employee_status', 'ACTIVE')
                 ->where('r.role_name', 'HR')
                 ->group_by('u.user_id');
        
        return $this->db->order_by('u.first_name', 'ASC')->get()->result();
    }

    public function search_employees($search_term, $status = NULL) {
        $this->db->select('u.user_id as employee_id, u.*, ewi.designation, ewi.employee_status, d.department_name,
                          u.is_active as user_active')
                 ->from('users u')
                 ->join('employee_work_info ewi', 'u.user_id = ewi.employee_id', 'left')
                 ->join('departments d', 'ewi.department_id = d.department_id', 'left')
                 ->where('u.deleted_at IS NULL')
                 ->group_start()
                     ->like('u.first_name', $search_term)
                     ->or_like('u.last_name', $search_term)
                     ->or_like('u.employee_code', $search_term)
                     ->or_like('u.email', $search_term)
                 ->group_end();
        
        if ($status) {
            $this->db->where('ewi.employee_status', $status);
        }
        
        return $this->db->order_by('u.first_name', 'ASC')->get()->result();
    }

    public function get_employee_full_details($employee_id) {
        $this->db->select('u.user_id as employee_id, u.*, ewi.*, d.department_name, u.is_active as user_active,
                          manager.first_name as manager_first_name, 
                          manager.last_name as manager_last_name,
                          hr.first_name as hr_first_name, 
                          hr.last_name as hr_last_name')
                 ->from('users u')
                 ->join('employee_work_info ewi', 'u.user_id = ewi.employee_id', 'left')
                 ->join('departments d', 'ewi.department_id = d.department_id', 'left')
                 ->join('users manager', 'ewi.reporting_manager_id = manager.user_id', 'left')
                 ->join('users hr', 'ewi.hr_spokesperson_id = hr.user_id', 'left')
                 ->where('u.user_id', $employee_id)
                 ->where('u.deleted_at IS NULL');
        
        $employee = $this->db->get()->row();
        
        if ($employee) {
            // Get all reporting managers
            $this->db->select('erm.*, u.user_id as employee_id, u.employee_code, u.first_name, u.last_name, erm.is_primary')
                     ->from('employee_reporting_managers erm')
                     ->join('users u', 'erm.manager_id = u.user_id')
                     ->where('erm.employee_id', $employee_id)
                     ->where('u.deleted_at IS NULL')
                     ->order_by('erm.is_primary', 'DESC');
            $employee->reporting_managers = $this->db->get()->result();
            
            // Get all HR spokespersons
            $this->db->select('ehr.*, u.user_id as employee_id, u.employee_code, u.first_name, u.last_name, ehr.is_primary')
                     ->from('employee_hr_spokespersons ehr')
                     ->join('users u', 'ehr.hr_id = u.user_id')
                     ->where('ehr.employee_id', $employee_id)
                     ->where('u.deleted_at IS NULL')
                     ->order_by('ehr.is_primary', 'DESC');
            $employee->hr_spokespersons = $this->db->get()->result();
        }
        
        return $employee;
    }

    public function create_employee($data) {
        $this->db->trans_start();
        
        // Debug: Log incoming data
        log_message('debug', 'create_employee called with data: ' . print_r($data, true));
        
        // Create user record with employee info - use numerical ID
        $user_id = $this->generate_user_id();
        log_message('debug', 'Generated user_id: ' . $user_id);
        
        $user_data = [
            'user_id' => $user_id,
            'employee_code' => $data['employee_code'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'] ?? NULL,
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'is_active' => 1
        ];
        
        log_message('debug', 'Inserting user data: ' . print_r($user_data, true));
        $this->db->insert('users', $user_data);
        
        if ($this->db->error()['code'] != 0) {
            log_message('error', 'User insert error: ' . print_r($this->db->error(), true));
        }
        
        // Get the first reporting manager and HR spokesperson for backward compatibility
        $reporting_manager_ids = isset($data['reporting_manager_ids']) ? $data['reporting_manager_ids'] : (isset($data['reporting_manager_id']) ? [$data['reporting_manager_id']] : []);
        $hr_spokesperson_ids = isset($data['hr_spokesperson_ids']) ? $data['hr_spokesperson_ids'] : (isset($data['hr_spokesperson_id']) ? [$data['hr_spokesperson_id']] : []);
        
        log_message('debug', 'Reporting manager IDs after processing: ' . print_r($reporting_manager_ids, true));
        log_message('debug', 'HR spokesperson IDs after processing: ' . print_r($hr_spokesperson_ids, true));
        
        // Store comma-separated manager IDs for employee_work_info
        $manager_ids_csv = !empty($reporting_manager_ids) ? implode(',', $reporting_manager_ids) : null;
        $hr_ids_csv = !empty($hr_spokesperson_ids) ? implode(',', $hr_spokesperson_ids) : null;
        
        log_message('debug', 'Manager IDs CSV: ' . $manager_ids_csv);
        log_message('debug', 'HR IDs CSV: ' . $hr_ids_csv);
        
        // Create work info
        $work_info_data = [
            'id' => $this->generate_uuid(),
            'employee_id' => $user_id,
            'department_id' => $data['department_id'],
            'designation' => $data['designation'],
            'reporting_manager_id' => $manager_ids_csv,
            'hr_spokesperson_id' => $hr_ids_csv,
            'date_of_joining' => $data['date_of_joining'],
            'employment_type' => $data['employment_type'] ?? 'Full-time',
            'employee_status' => 'ACTIVE'
        ];
        $this->db->insert('employee_work_info', $work_info_data);
        
        // Insert multiple reporting managers
        if (!empty($reporting_manager_ids)) {
            foreach ($reporting_manager_ids as $index => $manager_id) {
                if (!empty($manager_id)) {
                    $manager_data = [
                        'id' => $this->generate_uuid(),
                        'employee_id' => $user_id,
                        'manager_id' => $manager_id,
                        'is_primary' => ($index === 0) ? 1 : 0
                    ];
                    $this->db->insert('employee_reporting_managers', $manager_data);
                    log_message('debug', "Inserted manager {$manager_id} for employee {$user_id}, is_primary=" . ($index === 0 ? '1' : '0'));
                }
            }
        }
        
        // Insert multiple HR spokespersons
        if (!empty($hr_spokesperson_ids)) {
            foreach ($hr_spokesperson_ids as $index => $hr_id) {
                if (!empty($hr_id)) {
                    $hr_data = [
                        'id' => $this->generate_uuid(),
                        'employee_id' => $user_id,
                        'hr_id' => $hr_id,
                        'is_primary' => ($index === 0) ? 1 : 0
                    ];
                    $this->db->insert('employee_hr_spokespersons', $hr_data);
                    log_message('debug', "Inserted HR {$hr_id} for employee {$user_id}, is_primary=" . ($index === 0 ? '1' : '0'));
                }
            }
        }
        
        // Assign default employee role
        if (isset($data['roles']) && is_array($data['roles'])) {
            foreach ($data['roles'] as $role_id) {
                $role_data = [
                    'id' => $this->generate_uuid(),
                    'employee_id' => $user_id,
                    'role_id' => $role_id,
                    'is_primary' => ($role_id === $data['primary_role']) ? 1 : 0
                ];
                $this->db->insert('employee_roles', $role_data);
            }
        } else {
            // Default employee role
            $employee_role = $this->db->where('role_name', 'EMPLOYEE')->get('roles')->row();
            if ($employee_role) {
                $role_data = [
                    'id' => $this->generate_uuid(),
                    'employee_id' => $user_id,
                    'role_id' => $employee_role->role_id,
                    'is_primary' => 1
                ];
                $this->db->insert('employee_roles', $role_data);
            }
        }
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            log_message('error', 'Transaction failed in create_employee. DB Error: ' . print_r($this->db->error(), true));
            return FALSE;
        }
        
        log_message('debug', 'Employee created successfully with user_id: ' . $user_id);
        return $user_id;
    }

    /**
     * Send welcome email to new employee with login credentials
     */
    public function send_welcome_email($employee_data, $password) {
        $this->load->library('email');
        $this->load->config('email');
        
        // Get email configuration
        $email_config = $this->config->item('email');
        $this->email->initialize($email_config);
        
        // Get department name if available
        if (!empty($employee_data['department_id'])) {
            $dept = $this->db->where('department_id', $employee_data['department_id'])
                             ->get('departments')
                             ->row();
            $employee_data['department_name'] = $dept ? $dept->department_name : 'N/A';
        } else {
            $employee_data['department_name'] = 'N/A';
        }
        
        // Add password to employee data for email template
        $employee_data['password'] = $password;
        
        // Email subject
        $subject = "Welcome to KPI Portal - Your Account Details";
        
        // Email body from template
        $message = $this->load->view('emails/welcome_employee', ['employee' => $employee_data], TRUE);
        
        // Set email parameters
        $this->email->from($email_config['smtp_from'], $email_config['smtp_from_name']);
        $this->email->to($employee_data['email']);
        $this->email->subject($subject);
        $this->email->message($message);
        
        // Send email
        if ($this->email->send()) {
            log_message('info', "Welcome email sent to {$employee_data['email']}");
            return true;
        } else {
            log_message('error', 'Welcome email sending failed: ' . $this->email->print_debugger());
            return false;
        }
    }

    public function update_employee($employee_id, $data) {
        $this->db->trans_start();
        
        try {
            // Update user record - merged users/employees table
            $employee_data = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone_number' => $data['phone_number'] ?? NULL
            ];
            
            // Update email only if provided and different
            if (isset($data['email']) && !empty($data['email'])) {
                $employee_data['email'] = $data['email'];
            }
            
            log_message('debug', 'Updating employee ' . $employee_id . ' with data: ' . print_r($employee_data, TRUE));
            $this->db->where('user_id', $employee_id)->update('users', $employee_data);
            
            if ($this->db->affected_rows() === 0) {
                log_message('error', 'Employee update: No rows affected for employee_id ' . $employee_id);
            }
            
            // Get reporting managers and HR spokespersons
            $reporting_manager_ids = isset($data['reporting_manager_ids']) ? $data['reporting_manager_ids'] : (isset($data['reporting_manager_id']) ? [$data['reporting_manager_id']] : []);
            $hr_spokesperson_ids = isset($data['hr_spokesperson_ids']) ? $data['hr_spokesperson_ids'] : (isset($data['hr_spokesperson_id']) ? [$data['hr_spokesperson_id']] : []);
            
            log_message('debug', 'Reporting manager IDs for update: ' . print_r($reporting_manager_ids, TRUE));
            log_message('debug', 'HR spokesperson IDs for update: ' . print_r($hr_spokesperson_ids, TRUE));
            
            // Store comma-separated manager IDs for employee_work_info
            $manager_ids_csv = !empty($reporting_manager_ids) ? implode(',', $reporting_manager_ids) : null;
            $hr_ids_csv = !empty($hr_spokesperson_ids) ? implode(',', $hr_spokesperson_ids) : null;
            
            log_message('debug', 'Manager IDs CSV for update: ' . $manager_ids_csv);
            log_message('debug', 'HR IDs CSV for update: ' . $hr_ids_csv);
            
            // Check if work info exists
            $work_info_exists = $this->db->where('employee_id', $employee_id)
                                         ->get('employee_work_info')
                                         ->num_rows() > 0;
            
            log_message('debug', 'Work info exists for employee ' . $employee_id . ': ' . ($work_info_exists ? 'YES' : 'NO'));
            
            // Prepare work info data - only include fields that are present and valid
            $work_info_data = [];
            
            if (isset($data['department_id'])) {
                $work_info_data['department_id'] = $data['department_id'];
            }
            if (isset($data['designation']) && !empty($data['designation'])) {
                $work_info_data['designation'] = $data['designation'];
            }
            if ($manager_ids_csv) {
                $work_info_data['reporting_manager_id'] = $manager_ids_csv;
            }
            if ($hr_ids_csv) {
                $work_info_data['hr_spokesperson_id'] = $hr_ids_csv;
            }
            if (isset($data['employment_type'])) {
                $work_info_data['employment_type'] = $data['employment_type'];
            } else {
                $work_info_data['employment_type'] = 'Full-time';
            }
            if (isset($data['employee_status'])) {
                $work_info_data['employee_status'] = $data['employee_status'];
            } else {
                $work_info_data['employee_status'] = 'ACTIVE';
            }
            
            log_message('debug', 'Work info data: ' . print_r($work_info_data, TRUE));
            
            if ($work_info_exists) {
                // Update existing work info - only update if we have data
                if (!empty($work_info_data)) {
                    $this->db->where('employee_id', $employee_id)->update('employee_work_info', $work_info_data);
                    log_message('debug', 'Updated work info. Affected rows: ' . $this->db->affected_rows());
                }
            } else {
                // Insert new work info record only if all required fields are present
                $missing_fields = [];
                if (empty($work_info_data['designation'])) $missing_fields[] = 'designation';
                if (empty($work_info_data['reporting_manager_id'])) $missing_fields[] = 'reporting_manager_id';
                if (empty($work_info_data['hr_spokesperson_id'])) $missing_fields[] = 'hr_spokesperson_id';
                
                if (!empty($missing_fields)) {
                    $error_msg = 'Cannot create work info: missing required fields (' . implode(', ', $missing_fields) . ')';
                    log_message('error', $error_msg);
                    log_message('error', 'Attempted data: ' . print_r($work_info_data, TRUE));
                    $this->db->trans_rollback();
                    return FALSE;
                }
                
                $work_info_data['id'] = $this->generate_uuid();
                $work_info_data['employee_id'] = $employee_id;
                $this->db->insert('employee_work_info', $work_info_data);
                log_message('debug', 'Inserted work info. Affected rows: ' . $this->db->affected_rows());
            }
            
            // Delete existing managers and HR spokespersons
            $this->db->where('employee_id', $employee_id)->delete('employee_reporting_managers');
            $this->db->where('employee_id', $employee_id)->delete('employee_hr_spokespersons');
            
            // Insert updated reporting managers
            if (!empty($reporting_manager_ids)) {
                foreach ($reporting_manager_ids as $index => $manager_id) {
                    if (!empty($manager_id)) {
                        $manager_data = [
                            'id' => $this->generate_uuid(),
                            'employee_id' => $employee_id,
                            'manager_id' => $manager_id,
                            'is_primary' => ($index === 0) ? 1 : 0
                        ];
                        $this->db->insert('employee_reporting_managers', $manager_data);
                        log_message('debug', "Updated manager {$manager_id} for employee {$employee_id}, is_primary=" . ($index === 0 ? '1' : '0'));
                    }
                }
            }
            
            // Insert updated HR spokespersons
            if (!empty($hr_spokesperson_ids)) {
                foreach ($hr_spokesperson_ids as $index => $hr_id) {
                    if (!empty($hr_id)) {
                        $hr_data = [
                            'id' => $this->generate_uuid(),
                            'employee_id' => $employee_id,
                            'hr_id' => $hr_id,
                            'is_primary' => ($index === 0) ? 1 : 0
                        ];
                        $this->db->insert('employee_hr_spokespersons', $hr_data);
                        log_message('debug', "Updated HR {$hr_id} for employee {$employee_id}, is_primary=" . ($index === 0 ? '1' : '0'));
                    }
                }
            }
            
            // Check for any database errors
            $error = $this->db->error();
            if ($error['code'] !== 0) {
                log_message('error', '❌ Database error code ' . $error['code'] . ': ' . $error['message']);
                log_message('error', 'Full error details: ' . print_r($error, TRUE));
                $this->db->trans_rollback();
                return FALSE;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Exception in update_employee: ' . $e->getMessage());
            $this->db->trans_rollback();
            return FALSE;
        }
        
        $this->db->trans_complete();
        
        $status = $this->db->trans_status();
        log_message('debug', 'Transaction status: ' . ($status ? 'SUCCESS' : 'FAILED'));
        
        return $status;
    }

    public function update_employee_status($employee_id, $status) {
        return $this->db->where('employee_id', $employee_id)
                        ->update('employee_work_info', ['employee_status' => $status]);
    }

    /**
     * Delete employee (soft delete)
     */
    public function delete_employee($employee_id) {
        $this->db->trans_start();
        
        // Hard delete - remove from all related tables first
        log_message('debug', 'Hard deleting employee: ' . $employee_id);
        
        // Delete from employee_roles
        $this->db->where('employee_id', $employee_id)->delete('employee_roles');
        log_message('debug', 'Deleted from employee_roles');
        
        // Delete from employee_reporting_managers (as employee)
        $this->db->where('employee_id', $employee_id)->delete('employee_reporting_managers');
        log_message('debug', 'Deleted from employee_reporting_managers (as employee)');
        
        // Delete from employee_reporting_managers (as manager)
        $this->db->where('manager_id', $employee_id)->delete('employee_reporting_managers');
        log_message('debug', 'Deleted from employee_reporting_managers (as manager)');
        
        // Delete from employee_hr_spokespersons (as employee)
        $this->db->where('employee_id', $employee_id)->delete('employee_hr_spokespersons');
        log_message('debug', 'Deleted from employee_hr_spokespersons (as employee)');
        
        // Delete from employee_hr_spokespersons (as HR)
        $this->db->where('hr_id', $employee_id)->delete('employee_hr_spokespersons');
        log_message('debug', 'Deleted from employee_hr_spokespersons (as HR)');
        
        // Delete from employee_work_info
        $this->db->where('employee_id', $employee_id)->delete('employee_work_info');
        log_message('debug', 'Deleted from employee_work_info');
        
        // Finally, delete from users table
        $this->db->where('user_id', $employee_id)->delete('users');
        log_message('debug', 'Deleted from users table');
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status()) {
            log_message('debug', '✅ Employee hard deleted successfully');
            return TRUE;
        } else {
            log_message('error', '❌ Failed to hard delete employee');
            return FALSE;
        }
    }

    // ============================================
    // ROLE MANAGEMENT
    // ============================================

    public function get_all_roles() {
        return $this->db->order_by('role_name', 'ASC')->get('roles')->result();
    }

    public function get_employee_roles($employee_id) {
        $this->db->select('er.*, r.role_name')
                 ->from('employee_roles er')
                 ->join('roles r', 'er.role_id = r.role_id')
                 ->where('er.employee_id', $employee_id);
        
        return $this->db->get()->result();
    }

    public function update_employee_roles($employee_id, $roles, $primary_role) {
        $this->db->trans_start();
        
        // Delete existing roles
        $this->db->where('employee_id', $employee_id)->delete('employee_roles');
        
        // Insert new roles
        if (is_array($roles)) {
            foreach ($roles as $role_id) {
                $role_data = [
                    'id' => $this->generate_uuid(),
                    'employee_id' => $employee_id,
                    'role_id' => $role_id,
                    'is_primary' => ($role_id === $primary_role) ? 1 : 0
                ];
                $this->db->insert('employee_roles', $role_data);
            }
        }
        
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }

    // ============================================
    // DEPARTMENT MANAGEMENT
    // ============================================

    public function get_all_departments() {
        $this->db->select('d.*,
                          (SELECT COUNT(*) FROM employee_work_info WHERE department_id = d.department_id) as employee_count')
                 ->from('departments d')
                 ->order_by('d.department_name', 'ASC');
        
        return $this->db->get()->result();
    }

    public function get_active_departments() {
        return $this->db->where('is_active', 1)
                        ->order_by('department_name', 'ASC')
                        ->get('departments')
                        ->result();
    }

    public function get_department($department_id) {
        return $this->db->where('department_id', $department_id)->get('departments')->row();
    }

    public function create_department($data) {
        $department_data = [
            'department_id' => $this->generate_uuid(),
            'department_name' => $data['department_name'],
            'department_code' => $data['department_code'],
            'description' => $data['description'] ?? NULL,
            'is_active' => 1
        ];
        
        return $this->db->insert('departments', $department_data);
    }

    public function update_department($department_id, $data) {
        $department_data = [
            'department_name' => $data['department_name'],
            'description' => $data['description'] ?? NULL,
            'is_active' => isset($data['is_active']) ? $data['is_active'] : 1
        ];
        
        return $this->db->where('department_id', $department_id)
                        ->update('departments', $department_data);
    }

    public function delete_department($department_id) {
        // Check if department has employees
        $count = $this->db->where('department_id', $department_id)
                          ->from('employee_work_info')
                          ->count_all_results();
        
        if ($count > 0) {
            return FALSE;
        }
        
        return $this->db->where('department_id', $department_id)->delete('departments');
    }

    // ============================================
    // KPI CATEGORY MANAGEMENT
    // ============================================

    public function get_all_categories() {
        $this->db->select('kc.*,
                          (SELECT COUNT(*) FROM kpi_templates WHERE category_id = kc.category_id) as template_count')
                 ->from('kpi_categories kc')
                 ->order_by('kc.category_name', 'ASC');
        
        return $this->db->get()->result();
    }

    public function get_category($category_id) {
        return $this->db->where('category_id', $category_id)->get('kpi_categories')->row();
    }

    public function create_category($data) {
        $category_data = [
            'category_id' => $this->generate_uuid(),
            'category_name' => $data['category_name'],
            'description' => $data['description'] ?? NULL,
            'is_active' => 1
        ];
        
        return $this->db->insert('kpi_categories', $category_data);
    }

    public function update_category($category_id, $data) {
        $category_data = [
            'category_name' => $data['category_name'],
            'description' => $data['description'] ?? NULL,
            'is_active' => isset($data['is_active']) ? $data['is_active'] : 1
        ];
        
        return $this->db->where('category_id', $category_id)
                        ->update('kpi_categories', $category_data);
    }

    // ============================================
    // KPI TEMPLATE MANAGEMENT
    // ============================================

    public function get_all_templates() {
        $this->db->select('kt.*, kc.category_name, e.first_name as creator_first_name, e.last_name as creator_last_name')
                 ->from('kpi_templates kt')
                 ->join('kpi_categories kc', 'kt.category_id = kc.category_id')
                 ->join('employees e', 'kt.created_by = e.employee_id', 'left')
                 ->order_by('kc.category_name, kt.kpi_name', 'ASC');
        
        return $this->db->get()->result();
    }

    public function get_templates_by_category($category_id) {
        $this->db->select('kt.*, kc.category_name, u.first_name as creator_first_name, u.last_name as creator_last_name')
                 ->from('kpi_templates kt')
                 ->join('kpi_categories kc', 'kt.category_id = kc.category_id')
                 ->join('users u', 'kt.created_by = u.user_id', 'left')
                 ->where('kt.category_id', $category_id)
                 ->order_by('kt.kpi_name', 'ASC');
        
        return $this->db->get()->result();
    }

    public function get_template($template_id) {
        $this->db->select('kt.*, kc.category_name')
                 ->from('kpi_templates kt')
                 ->join('kpi_categories kc', 'kt.category_id = kc.category_id')
                 ->where('kt.template_id', $template_id);
        
        return $this->db->get()->row();
    }

    /**
     * Get templates grouped by template_name
     * Returns array with template info and KPI count
     */
    /**
     * Get templates grouped by template_name
     * Returns array with template info and KPI count
     * One template can have KPIs from multiple categories
     */
    public function get_templates_grouped($category_id = NULL, $department_id = NULL) {
        $this->db->select('kt.template_name, 
                          COUNT(*) as kpi_count, 
                          GROUP_CONCAT(DISTINCT kc.category_name SEPARATOR ", ") as categories,
                          MAX(kt.created_at) as created_at,
                          MAX(kt.updated_at) as updated_at,
                          MAX(kt.is_active) as is_active,
                          MAX(kt.department_id) as department_id,
                          d.department_name,
                          u.first_name as creator_first_name, 
                          u.last_name as creator_last_name')
                 ->from('kpi_templates kt')
                 ->join('kpi_categories kc', 'kt.category_id = kc.category_id')
                 ->join('users u', 'kt.created_by = u.user_id', 'left')
                 ->join('departments d', 'kt.department_id = d.department_id', 'left')
                 ->where('kt.is_active', 1);
        
        if ($category_id) {
            $this->db->having('FIND_IN_SET("' . $category_id . '", GROUP_CONCAT(DISTINCT kt.category_id)) > 0');
        }
        
        // Filter by department if provided
        // NULL department_id means template is available for all departments
        if ($department_id) {
            $this->db->group_start();
            $this->db->where('kt.department_id', $department_id);
            $this->db->or_where('kt.department_id IS NULL', NULL, FALSE);
            $this->db->group_end();
        }
        
        $this->db->group_by('kt.template_name, u.first_name, u.last_name, d.department_name')
                 ->order_by('kt.template_name', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get all KPIs in a template by template_name
     * Includes category information for each KPI
     */
    public function get_kpis_by_template_name($template_name) {
        return $this->db->select('kt.*, kc.category_name')
                        ->from('kpi_templates kt')
                        ->join('kpi_categories kc', 'kt.category_id = kc.category_id', 'left')
                        ->where('kt.template_name', $template_name)
                        ->where('kt.is_active', 1)
                        ->order_by('kt.template_id', 'ASC')
                        ->get()
                        ->result();
    }

    /**
     * Update template group (all KPIs with same template_name)
     * Deletes old KPIs and inserts new ones in a transaction
     * Each KPI can have its own category now
     */
    public function update_template_group($old_template_name, $new_template_name, $department_id, $kpis) {
        $this->db->trans_start();
        
        // Get the creator from existing template
        $existing = $this->db->select('created_by')
                            ->where('template_name', $old_template_name)
                            ->where('is_active', 1)
                            ->limit(1)
                            ->get('kpi_templates')
                            ->row();
        
        if (!$existing) {
            $this->db->trans_rollback();
            return false;
        }
        
        $created_by = $existing->created_by;
        
        // Soft delete old KPIs (set is_active = 0)
        $this->db->where('template_name', $old_template_name)
                 ->update('kpi_templates', ['is_active' => 0]);
        
        // Insert new KPIs (each with its own category)
        foreach ($kpis as $kpi) {
            $kpi_data = [
                'template_id' => $this->generate_uuid(),
                'category_id' => $kpi['category_id'], // Per-KPI category
                'department_id' => $department_id, // Department for the template
                'template_name' => $new_template_name,
                'kpi_name' => trim($kpi['kpi_name']),
                'weightage' => isset($kpi['weightage']) ? floatval($kpi['weightage']) : 0,
                'default_score' => isset($kpi['default_score']) ? floatval($kpi['default_score']) : 5,
                'description' => isset($kpi['description']) ? trim($kpi['description']) : NULL,
                'is_active' => 1,
                'created_by' => $created_by,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->db->insert('kpi_templates', $kpi_data);
        }
        
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }

    public function create_template($data, $created_by) {
        $template_data = [
            'template_id' => $this->generate_uuid(),
            'category_id' => $data['category_id'],
            'kpi_name' => $data['kpi_name'],
            'description' => $data['description'] ?? NULL,
            'measurement_unit' => $data['measurement_unit'] ?? NULL,
            'calculation_method' => $data['calculation_method'] ?? NULL,
            'target_type' => $data['target_type'] ?? NULL,
            'weightage' => $data['weightage'] ?? NULL,
            'is_active' => 1,
            'created_by' => $created_by
        ];
        
        return $this->db->insert('kpi_templates', $template_data);
    }

    public function update_template($template_id, $data) {
        $template_data = [
            'category_id' => $data['category_id'],
            'kpi_name' => $data['kpi_name'],
            'description' => $data['description'] ?? NULL,
            'measurement_unit' => $data['measurement_unit'] ?? NULL,
            'calculation_method' => $data['calculation_method'] ?? NULL,
            'target_type' => $data['target_type'] ?? NULL,
            'weightage' => $data['weightage'] ?? NULL,
            'is_active' => isset($data['is_active']) ? $data['is_active'] : 1
        ];
        
        return $this->db->where('template_id', $template_id)
                        ->update('kpi_templates', $template_data);
    }

    public function delete_template($template_id) {
        // Check if template is assigned to employees
        $count = $this->db->where('template_id', $template_id)
                          ->from('employee_kpis')
                          ->count_all_results();
        
        if ($count > 0) {
            return FALSE;
        }
        
        return $this->db->where('template_id', $template_id)->delete('kpi_templates');
    }

    /**
     * Delete all templates in a template group (by template name)
     */
    public function delete_template_group($template_name) {
        // Get all template IDs for this template name
        $template_ids = $this->db->select('template_id')
                                  ->where('template_name', $template_name)
                                  ->get('kpi_templates')
                                  ->result_array();
        
        if (empty($template_ids)) {
            return FALSE;
        }
        
        $template_id_list = array_column($template_ids, 'template_id');
        
        // Check if any template in the group is assigned to employees
        $count = $this->db->where_in('template_id', $template_id_list)
                          ->from('employee_kpis')
                          ->count_all_results();
        
        if ($count > 0) {
            return FALSE;
        }
        
        // Delete all templates with this template name
        return $this->db->where('template_name', $template_name)->delete('kpi_templates');
    }

    /**
     * Create multiple KPIs under one template name (batch insert)
     */
    public function create_template_batch($template_data, $created_by) {
        $this->db->trans_start();
        
        try {
            $template_name = $template_data['template_name'];
            $department_id = $template_data['department_id'] ?? NULL; // Department for the template
            $kpis = $template_data['kpis'];
            
            foreach ($kpis as $kpi) {
                $insert_data = [
                    'template_id' => $this->generate_uuid(),
                    'template_name' => $template_name,
                    'department_id' => $department_id, // Add department_id to each KPI
                    'category_id' => $kpi['category_id'], // Per-KPI category
                    'kpi_name' => $kpi['kpi_name'],
                    'weightage' => isset($kpi['weightage']) ? floatval($kpi['weightage']) : 0,
                    'default_score' => !empty($kpi['default_score']) ? $kpi['default_score'] : NULL,
                    'is_active' => 1,
                    'created_by' => $created_by,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->insert('kpi_templates', $insert_data);
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                log_message('error', 'Failed to create template batch: ' . $this->db->error()['message']);
                return FALSE;
            }
            
            log_message('info', 'Template created: ' . $template_name . ' with ' . count($kpis) . ' KPIs');
            return TRUE;
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Error creating template batch: ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Create multiple KPI templates from imported Excel data
     */
    public function create_template_from_import($template_data, $created_by) {
        $this->db->trans_start();
        
        try {
            // Get or create categories for the KPIs
            $category_map = [];
            
            foreach ($template_data['kpis'] as $kpi) {
                $category_name = $kpi['category'];
                
                // Check if category exists in map
                if (!isset($category_map[$category_name])) {
                    // Try to find existing category
                    $existing = $this->db->where('category_name', $category_name)
                                        ->get('kpi_categories')
                                        ->row();
                    
                    if ($existing) {
                        $category_map[$category_name] = $existing->category_id;
                    } else {
                        // Create new category
                        $new_category_id = $this->generate_uuid();
                        $this->db->insert('kpi_categories', [
                            'category_id' => $new_category_id,
                            'category_name' => $category_name,
                            'description' => 'Auto-created from import',
                            'is_active' => 1
                        ]);
                        $category_map[$category_name] = $new_category_id;
                    }
                }
                
                // Insert KPI template
                // Note: Weightage is stored in description since kpi_templates doesn't have weightage column
                // Weightage is meant to be set per employee in kpi_scores table when KPI is assigned
                $template_insert = [
                    'template_id' => $this->generate_uuid(),
                    'category_id' => $category_map[$category_name],
                    'kpi_name' => $kpi['kpi_name'],
                    'description' => 'Suggested weightage: ' . $kpi['weightage'] . '%',
                    'is_active' => 1,
                    'created_by' => $created_by
                ];
                
                $this->db->insert('kpi_templates', $template_insert);
            }
            
            $this->db->trans_complete();
            return $this->db->trans_status();
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Error importing template: ' . $e->getMessage());
            return FALSE;
        }
    }

    // ============================================
    // REVIEW PERIOD MANAGEMENT
    // ============================================

    public function get_all_periods() {
        return $this->db->order_by('start_date', 'DESC')->get('review_periods')->result();
    }

    public function get_period($period_id) {
        return $this->db->where('period_id', $period_id)->get('review_periods')->row();
    }

    public function create_period($data) {
        $period_data = [
            'period_id' => $this->generate_uuid(),
            'period_name' => $data['period_name'],
            'period_type' => $data['period_type'],
            'cycle_year' => $data['cycle_year'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'fiscal_year' => $data['fiscal_year'] ?? $data['cycle_year'],
            'is_active' => isset($data['is_active']) ? $data['is_active'] : 1,
            'status' => $data['status'] ?? 'UPCOMING'
        ];
        
        return $this->db->insert('review_periods', $period_data);
    }

    public function update_period($period_id, $data) {
        $period_data = [
            'period_name' => $data['period_name'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'is_active' => isset($data['is_active']) ? $data['is_active'] : 1,
            'status' => $data['status'] ?? 'UPCOMING'
        ];
        
        return $this->db->where('period_id', $period_id)
                        ->update('review_periods', $period_data);
    }

    public function activate_period($period_id) {
        $this->db->trans_start();
        
        // Deactivate all other periods
        $this->db->update('review_periods', ['status' => 'CLOSED']);
        
        // Activate selected period
        $this->db->where('period_id', $period_id)
                 ->update('review_periods', ['status' => 'ACTIVE', 'is_active' => 1]);
        
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }

    public function close_period($period_id) {
        return $this->db->where('period_id', $period_id)
                        ->update('review_periods', ['status' => 'CLOSED']);
    }

    // ============================================
    // REPORTS & ANALYTICS
    // ============================================

    public function get_department_performance($period_id) {
        $this->db->select('d.department_name, 
                          COUNT(DISTINCT u.user_id) as employee_count,
                          AVG(ks.weighted_score) as avg_score')
                 ->from('departments d')
                 ->join('employee_work_info ewi', 'd.department_id = ewi.department_id')
                 ->join('users u', 'ewi.employee_id = u.user_id')
                 ->join('employee_kpis ekpi', 'u.user_id = ekpi.employee_id')
                 ->join('kpi_scores ks', 'ekpi.employee_kpi_id = ks.employee_kpi_id')
                 ->where('ekpi.review_period_id', $period_id)
                 ->where('ewi.employee_status', 'ACTIVE')
                 ->where('u.deleted_at IS NULL')
                 ->group_by('d.department_id')
                 ->order_by('avg_score', 'DESC');
        
        return $this->db->get()->result();
    }

    public function get_top_performers($period_id, $limit = 10) {
        $this->db->select('u.user_id as employee_id, u.first_name, u.last_name, u.employee_code,
                          ewi.designation, d.department_name,
                          SUM(ks.weighted_score) as total_score')
                 ->from('users u')
                 ->join('employee_work_info ewi', 'u.user_id = ewi.employee_id')
                 ->join('departments d', 'ewi.department_id = d.department_id', 'left')
                 ->join('employee_kpis ekpi', 'u.user_id = ekpi.employee_id')
                 ->join('kpi_scores ks', 'ekpi.employee_kpi_id = ks.employee_kpi_id')
                 ->where('ekpi.review_period_id', $period_id)
                 ->where('ks.score IS NOT NULL')
                 ->where('u.deleted_at IS NULL')
                 ->group_by('u.user_id')
                 ->order_by('total_score', 'DESC')
                 ->limit($limit);
        
        return $this->db->get()->result();
    }

    public function get_low_performers($period_id, $limit = 10) {
        $this->db->select('u.user_id as employee_id, u.first_name, u.last_name, u.employee_code,
                          ewi.designation, d.department_name,
                          SUM(ks.weighted_score) as total_score')
                 ->from('users u')
                 ->join('employee_work_info ewi', 'u.user_id = ewi.employee_id')
                 ->join('departments d', 'ewi.department_id = d.department_id', 'left')
                 ->join('employee_kpis ekpi', 'u.user_id = ekpi.employee_id')
                 ->join('kpi_scores ks', 'ekpi.employee_kpi_id = ks.employee_kpi_id')
                 ->where('ekpi.review_period_id', $period_id)
                 ->where('ks.score IS NOT NULL')
                 ->where('u.deleted_at IS NULL')
                 ->group_by('u.user_id')
                 ->order_by('total_score', 'ASC')
                 ->limit($limit);
        
        return $this->db->get()->result();
    }

    // ============================================
    // AUDIT LOGS
    // ============================================

    public function get_audit_logs($limit = 50, $offset = 0) {
        $this->db->select('al.*, u.email, u.first_name, u.last_name')
                 ->from('audit_logs al')
                 ->join('users u', 'al.user_id = u.user_id', 'left')
                 ->order_by('al.timestamp', 'DESC')
                 ->limit($limit, $offset);
        
        return $this->db->get()->result();
    }

    public function count_audit_logs() {
        return $this->db->count_all('audit_logs');
    }

    // ============================================
    // TEAM MANAGEMENT
    // ============================================

    /**
     * Get team members reporting to a manager
     */
    public function get_team_members($manager_id) {
        $this->db->select('u.user_id as employee_id, u.employee_code, u.first_name, u.last_name, u.email, 
                          ewi.designation, d.department_name')
                 ->from('users u')
                 ->join('employee_work_info ewi', 'u.user_id = ewi.employee_id')
                 ->join('departments d', 'ewi.department_id = d.department_id', 'left')
                 ->where('ewi.reporting_manager_id', $manager_id)
                 ->where('ewi.employee_status', 'ACTIVE')
                 ->where('u.deleted_at IS NULL');
        
        return $this->db->get()->result();
    }

    /**
     * Assign team members to a manager
     */
    public function assign_team_members($manager_id, $team_member_ids) {
        $this->db->trans_start();
        
        // First, remove this manager from all employees (reset)
        $this->db->where('reporting_manager_id', $manager_id)
                 ->set('reporting_manager_id', NULL)
                 ->update('employee_work_info');
        
        // Now assign selected team members to this manager
        if (!empty($team_member_ids)) {
            foreach ($team_member_ids as $employee_id) {
                $this->db->where('employee_id', $employee_id)
                         ->set('reporting_manager_id', $manager_id)
                         ->update('employee_work_info');
            }
        }
        
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    private function generate_uuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Generate sequential numerical user ID
     */
    private function generate_user_id() {
        // Get the last user ID with numeric format using raw SQL to avoid double DESC issue
        $sql = "SELECT user_id FROM users 
                WHERE user_id REGEXP '^user-[0-9]+$' 
                ORDER BY CAST(SUBSTRING(user_id, 6) AS UNSIGNED) DESC 
                LIMIT 1";
        
        $result = $this->db->query($sql)->row();
        
        if ($result) {
            // Extract the number part and increment
            $last_number = (int)str_replace('user-', '', $result->user_id);
            $next_number = $last_number + 1;
        } else {
            // Start from 1000 if no numeric IDs exist yet
            $next_number = 1000;
        }
        
        return 'user-' . $next_number;
    }
}
