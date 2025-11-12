<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * KPI Assignment Fallback Helper
 * Handles fallback logic when reporting manager is unavailable
 */
class Kpi_fallback_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get the appropriate person to assign KPIs when manager is unavailable
     * 
     * Fallback hierarchy:
     * 1. Reporting Manager (if active)
     * 2. HR Spokesperson (if active)
     * 3. Department Head (if exists and active)
     * 4. Any Super Admin (as last resort)
     * 
     * @param string $employee_id
     * @return object|null Assignor details with reason
     */
    public function get_kpi_assignor($employee_id) {
        // Get employee work info
        $work_info = $this->db->select('ewi.*, u.first_name, u.last_name, u.employee_code')
                              ->from('employee_work_info ewi')
                              ->join('users u', 'ewi.employee_id = u.user_id')
                              ->where('ewi.employee_id', $employee_id)
                              ->where('u.deleted_at IS NULL')
                              ->get()
                              ->row();
        
        if (!$work_info) {
            return null;
        }

        // Priority 1: Check Reporting Manager
        $manager = $this->check_employee_availability($work_info->reporting_manager_id);
        if ($manager) {
            return (object)[
                'assignor_id' => $manager->employee_id,
                'assignor_name' => $manager->first_name . ' ' . $manager->last_name,
                'assignor_email' => $manager->email,
                'assignor_role' => 'Reporting Manager',
                'is_fallback' => false,
                'fallback_reason' => null
            ];
        }

        // Priority 2: Check HR Spokesperson
        $hr = $this->check_employee_availability($work_info->hr_spokesperson_id);
        if ($hr) {
            return (object)[
                'assignor_id' => $hr->employee_id,
                'assignor_name' => $hr->first_name . ' ' . $hr->last_name,
                'assignor_email' => $hr->email,
                'assignor_role' => 'HR Spokesperson',
                'is_fallback' => true,
                'fallback_reason' => 'Reporting manager unavailable'
            ];
        }

        // Priority 3: Check Department Head
        if (!empty($work_info->department_id)) {
            $dept_head = $this->get_department_head($work_info->department_id);
            if ($dept_head) {
                return (object)[
                    'assignor_id' => $dept_head->employee_id,
                    'assignor_name' => $dept_head->first_name . ' ' . $dept_head->last_name,
                    'assignor_email' => $dept_head->email,
                    'assignor_role' => 'Department Head',
                    'is_fallback' => true,
                    'fallback_reason' => 'Reporting manager and HR spokesperson unavailable'
                ];
            }
        }

        // Priority 4: Get any Super Admin
        $super_admin = $this->get_active_super_admin();
        if ($super_admin) {
            return (object)[
                'assignor_id' => $super_admin->employee_id,
                'assignor_name' => $super_admin->first_name . ' ' . $super_admin->last_name,
                'assignor_email' => $super_admin->email,
                'assignor_role' => 'Super Admin',
                'is_fallback' => true,
                'fallback_reason' => 'All designated managers unavailable'
            ];
        }

        // No one available
        return null;
    }

    /**
     * Check if employee exists and is active
     */
    private function check_employee_availability($employee_id) {
        if (empty($employee_id)) {
            return null;
        }

        $employee = $this->db->select('u.user_id as employee_id, u.employee_code, u.first_name, u.last_name, u.email, u.phone_number, u.is_active as user_active, ewi.employee_status')
                             ->from('users u')
                             ->join('employee_work_info ewi', 'u.user_id = ewi.employee_id')
                             ->where('u.user_id', $employee_id)
                             ->where('u.is_active', 1)
                             ->where('u.deleted_at IS NULL')
                             ->where('ewi.employee_status', 'ACTIVE')
                             ->get()
                             ->row();

        return $employee;
    }

    /**
     * Get active department head
     */
    private function get_department_head($department_id) {
        // Find employee with MANAGER role in this department
        $dept_head = $this->db->select('u.user_id as employee_id, u.employee_code, u.first_name, u.last_name, u.email, u.phone_number')
                              ->from('users u')
                              ->join('employee_work_info ewi', 'u.user_id = ewi.employee_id')
                              ->join('employee_roles er', 'u.user_id = er.employee_id')
                              ->join('roles r', 'er.role_id = r.role_id')
                              ->where('ewi.department_id', $department_id)
                              ->where('r.role_name', 'MANAGER')
                              ->where('u.is_active', 1)
                              ->where('u.deleted_at IS NULL')
                              ->where('ewi.employee_status', 'ACTIVE')
                              ->order_by('er.is_primary', 'DESC')
                              ->limit(1)
                              ->get()
                              ->row();

        return $dept_head;
    }

    /**
     * Get any active super admin
     */
    private function get_active_super_admin() {
        $super_admin = $this->db->select('u.user_id as employee_id, u.employee_code, u.first_name, u.last_name, u.email, u.phone_number')
                                ->from('users u')
                                ->join('employee_work_info ewi', 'u.user_id = ewi.employee_id')
                                ->join('employee_roles er', 'u.user_id = er.employee_id')
                                ->join('roles r', 'er.role_id = r.role_id')
                                ->where('r.role_name', 'SUPER_ADMIN')
                                ->where('u.is_active', 1)
                                ->where('u.deleted_at IS NULL')
                                ->where('ewi.employee_status', 'ACTIVE')
                                ->limit(1)
                                ->get()
                                ->row();

        return $super_admin;
    }

    /**
     * Log fallback assignment for audit trail
     */
    public function log_fallback_assignment($employee_id, $assignor_info, $period_id) {
        $log_data = [
            'id' => $this->generate_uuid(),
            'employee_id' => $employee_id,
            'assignor_id' => $assignor_info->assignor_id,
            'assignor_role' => $assignor_info->assignor_role,
            'is_fallback' => $assignor_info->is_fallback ? 1 : 0,
            'fallback_reason' => $assignor_info->fallback_reason,
            'period_id' => $period_id,
            'assigned_at' => date('Y-m-d H:i:s')
        ];

        // Try to insert into database, fallback to log file if table doesn't exist
        try {
            $this->db->insert('kpi_assignment_logs', $log_data);
            return true;
        } catch (Exception $e) {
            // Table doesn't exist yet, log to file
            log_message('info', 'KPI Assignment Fallback: ' . json_encode($log_data));
            return false;
        }
    }

    /**
     * Send notification about fallback assignment
     */
    public function send_fallback_notification($employee, $assignor_info, $original_manager_name) {
        $this->load->library('email');
        $this->load->config('email');
        
        $email_config = $this->config->item('email');
        $this->email->initialize($email_config);

        // Email to employee
        $subject = "KPI Assignment - Action Required";
        $message = "
            <h3>KPI Assignment Notification</h3>
            <p>Dear {$employee->first_name},</p>
            
            <p>Your KPIs for the current period will be assigned by:</p>
            <p><strong>{$assignor_info->assignor_name}</strong> ({$assignor_info->assignor_role})</p>
            
            <p><strong>Reason:</strong> {$assignor_info->fallback_reason}</p>
            
            <p>Your designated reporting manager ({$original_manager_name}) is currently unavailable.</p>
            
            <p>Please contact {$assignor_info->assignor_name} for any questions about your KPI assignments.</p>
            
            <p>Best regards,<br>KPI Portal Team</p>
        ";

        $this->email->from($email_config['smtp_from'], $email_config['smtp_from_name']);
        $this->email->to($employee->email);
        $this->email->subject($subject);
        $this->email->message($message);
        
        $result = $this->email->send();
        
        if (!$result) {
            log_message('error', 'Failed to send fallback notification: ' . $this->email->print_debugger());
        }
        
        return $result;
    }

    /**
     * Get fallback history for an employee
     */
    public function get_fallback_history($employee_id = null) {
        $this->db->select('kal.*, 
                          u.first_name || " " || u.last_name as employee_name,
                          a.first_name || " " || a.last_name as assignor_name,
                          rp.period_name')
                 ->from('kpi_assignment_logs kal')
                 ->join('users u', 'kal.employee_id = u.user_id')
                 ->join('users a', 'kal.assignor_id = a.user_id')
                 ->join('review_periods rp', 'kal.period_id = rp.period_id')
                 ->where('kal.is_fallback', 1);
        
        if ($employee_id) {
            $this->db->where('kal.employee_id', $employee_id);
        }
        
        $this->db->order_by('kal.assigned_at', 'DESC')
                 ->limit(50);
        
        try {
            return $this->db->get()->result();
        } catch (Exception $e) {
            // Table doesn't exist yet
            return [];
        }
    }

    /**
     * Generate UUID
     */
    private function generate_uuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
