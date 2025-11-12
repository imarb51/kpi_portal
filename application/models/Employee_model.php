<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Employee Model
 * Handles employee-related database operations
 */
class Employee_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get employee by user ID
     */
    public function get_by_user_id($user_id) {
        $this->db->select('u.user_id as employee_id, u.user_id, u.employee_code, u.first_name, u.last_name, 
                          u.middle_name, u.email, u.phone_number, u.profile_picture_url,
                          u.is_active, u.created_at, u.updated_at,
                          ewi.designation, ewi.reporting_manager_id, ewi.hr_spokesperson_id, 
                          ewi.department_id, d.department_name')
                 ->from('users u')
                 ->join('employee_work_info ewi', 'u.user_id = ewi.employee_id', 'left')
                 ->join('departments d', 'ewi.department_id = d.department_id', 'left')
                 ->where('u.user_id', $user_id)
                 ->where('u.deleted_at IS NULL');
        
        return $this->db->get()->row();
    }

    /**
     * Get employee by employee ID
     */
    public function get_by_id($employee_id) {
        $this->db->select('u.user_id as employee_id, u.user_id, u.employee_code, u.first_name, u.last_name, 
                          u.middle_name, u.email, u.phone_number, u.profile_picture_url,
                          u.is_active, u.created_at, u.updated_at,
                          ewi.designation, ewi.reporting_manager_id, ewi.hr_spokesperson_id, 
                          ewi.department_id, d.department_name, ewi.employee_status')
                 ->from('users u')
                 ->join('employee_work_info ewi', 'u.user_id = ewi.employee_id', 'left')
                 ->join('departments d', 'ewi.department_id = d.department_id', 'left')
                 ->where('u.user_id', $employee_id)
                 ->where('u.deleted_at IS NULL');
        
        return $this->db->get()->row();
    }

    /**
     * Get employee roles
     */
    public function get_roles($employee_id) {
        $this->db->select('r.role_id, r.role_name, er.is_primary')
                 ->from('employee_roles er')
                 ->join('roles r', 'er.role_id = r.role_id')
                 ->where('er.employee_id', $employee_id);
        
        return $this->db->get()->result();
    }

    /**
     * Check if employee has role
     */
    public function has_role($employee_id, $role_name) {
        $this->db->select('er.id')
                 ->from('employee_roles er')
                 ->join('roles r', 'er.role_id = r.role_id')
                 ->where('er.employee_id', $employee_id)
                 ->where('r.role_name', $role_name);
        
        return $this->db->get()->num_rows() > 0;
    }

    /**
     * Get primary role for employee
     */
    public function get_primary_role($employee_id) {
        $this->db->select('r.role_name')
                 ->from('employee_roles er')
                 ->join('roles r', 'er.role_id = r.role_id')
                 ->where('er.employee_id', $employee_id)
                 ->where('er.is_primary', 1);
        
        $result = $this->db->get()->row();
        return $result ? $result->role_name : NULL;
    }

    /**
     * Get direct reports (team members) for a manager
     */
    public function get_direct_reports($manager_id) {
        // Get employees where manager_id appears in comma-separated reporting_manager_id
        $this->db->select('u.user_id as employee_id, u.employee_code, u.first_name, u.last_name, 
                          ewi.designation, d.department_name, ewi.employee_status')
                 ->from('users u')
                 ->join('employee_work_info ewi', 'u.user_id = ewi.employee_id', 'left')
                 ->join('departments d', 'ewi.department_id = d.department_id', 'left')
                 ->where("FIND_IN_SET('{$manager_id}', ewi.reporting_manager_id) > 0", NULL, FALSE)
                 ->where('u.deleted_at IS NULL')
                 ->where('(ewi.employee_status IS NULL OR ewi.employee_status = "ACTIVE")', NULL, FALSE)
                 ->order_by('u.first_name', 'ASC');
        
        return $this->db->get()->result();
    }

    /**
     * Get manager details for an employee
     */
    public function get_manager($employee_id) {
        $this->db->select('ewi.reporting_manager_id')
                 ->from('employee_work_info ewi')
                 ->where('ewi.employee_id', $employee_id);
        
        $result = $this->db->get()->row();
        
        if ($result && $result->reporting_manager_id) {
            return $this->get_by_id($result->reporting_manager_id);
        }
        
        return NULL;
    }

    /**
     * Get all managers for an employee (multiple managers support)
     */
    public function get_employee_managers($employee_id) {
        // Get the employee's work info with comma-separated manager IDs
        $work_info = $this->db->select('reporting_manager_id')
                              ->from('employee_work_info')
                              ->where('employee_id', $employee_id)
                              ->get()
                              ->row();
        
        if (!$work_info || empty($work_info->reporting_manager_id)) {
            return [];
        }
        
        // Split comma-separated manager IDs
        $manager_ids = explode(',', $work_info->reporting_manager_id);
        $manager_ids = array_map('trim', $manager_ids);
        
        // Get manager details
        $this->db->select('u.user_id, u.employee_code, u.first_name, u.last_name, u.email')
                 ->from('users u')
                 ->where_in('u.user_id', $manager_ids)
                 ->where('u.deleted_at IS NULL');
        
        return $this->db->get()->result();
    }

    /**
     * Get full name of employee
     */
    public function get_full_name($employee_id) {
        $employee = $this->get_by_id($employee_id);
        
        if ($employee) {
            return trim($employee->first_name . ' ' . $employee->last_name);
        }
        
        return '';
    }

    /**
     * Search employees by name or code
     */
    public function search($search_term) {
        $this->db->select('u.user_id as employee_id, u.employee_code, u.first_name, u.last_name, 
                          ewi.designation, d.department_name')
                 ->from('users u')
                 ->join('employee_work_info ewi', 'u.user_id = ewi.employee_id', 'left')
                 ->join('departments d', 'ewi.department_id = d.department_id', 'left')
                 ->where('u.deleted_at IS NULL')
                 ->group_start()
                     ->like('u.first_name', $search_term)
                     ->or_like('u.last_name', $search_term)
                     ->or_like('u.employee_code', $search_term)
                 ->group_end()
                 ->limit(10);
        
        return $this->db->get()->result();
    }

    /**
     * Get all employees (for admin)
     */
    public function get_all($limit = NULL, $offset = 0) {
        $this->db->select('u.user_id as employee_id, u.employee_code, u.first_name, u.last_name, 
                          ewi.designation, d.department_name, ewi.employee_status')
                 ->from('users u')
                 ->join('employee_work_info ewi', 'u.user_id = ewi.employee_id', 'left')
                 ->join('departments d', 'ewi.department_id = d.department_id', 'left')
                 ->where('u.deleted_at IS NULL')
                 ->order_by('u.first_name', 'ASC');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }

    /**
     * Generate UUID
     */
    public function generate_uuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Get all managers for an employee
     */
    public function get_all_managers($employee_id) {
        $this->db->select('u.user_id as employee_id, u.first_name, u.last_name, u.email, erm.is_primary')
                 ->from('employee_reporting_managers erm')
                 ->join('users u', 'erm.manager_id = u.user_id')
                 ->where('erm.employee_id', $employee_id)
                 ->where('u.deleted_at IS NULL')
                 ->order_by('erm.is_primary', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Get all HR spokespersons for an employee
     */
    public function get_all_hr_spokespersons($employee_id) {
        $this->db->select('u.user_id as employee_id, u.first_name, u.last_name, u.email, ehr.is_primary')
                 ->from('employee_hr_spokespersons ehr')
                 ->join('users u', 'ehr.hr_id = u.user_id')
                 ->where('ehr.employee_id', $employee_id)
                 ->where('u.deleted_at IS NULL')
                 ->order_by('ehr.is_primary', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Notify managers and HR when employee agrees to KPI assignments
     */
    public function notify_kpi_agreement($employee_id, $assignment_id) {
        $this->load->library('email');
        $this->load->config('email');
        
        $email_config = $this->config->item('email');
        $this->email->initialize($email_config);
        
        // Get employee details
        $employee = $this->get_by_id($employee_id);
        
        // Get assignment details
        $assignment = $this->db->where('assignment_id', $assignment_id)->get('employee_kpi_assignments')->row();
        
        // Get period details
        $period = $this->db->where('period_id', $assignment->review_period_id)->get('review_periods')->row();
        
        // Get all managers and HR
        $managers = $this->get_all_managers($employee_id);
        $hr_persons = $this->get_all_hr_spokespersons($employee_id);
        
        $recipients = array_merge($managers, $hr_persons);
        
        if (empty($recipients)) {
            return false;
        }
        
        $subject = "KPI Assignment Agreed - {$employee->first_name} {$employee->last_name}";
        
        $message = "<h2>KPI Assignment Agreement</h2>";
        $message .= "<p><strong>{$employee->first_name} {$employee->last_name}</strong> ({$employee->employee_code}) has agreed to their KPI assignments for:</p>";
        $message .= "<p><strong>Period:</strong> {$period->period_name}</p>";
        $message .= "<p><strong>Agreement Time:</strong> " . date('F d, Y h:i A', strtotime($assignment->employee_agreement_at)) . "</p>";
        $message .= "<p>You can now proceed with the review process.</p>";
        $message .= "<hr>";
        $message .= "<p><small>This is an automated notification from the KPI Portal.</small></p>";
        
        $email_sent = true;
        foreach ($recipients as $recipient) {
            $this->email->clear();
            $this->email->from($email_config['smtp_from'], $email_config['smtp_from_name']);
            $this->email->to($recipient->email);
            $this->email->subject($subject);
            $this->email->message($message);
            
            if (!$this->email->send()) {
                log_message('error', "Failed to send KPI agreement notification to {$recipient->email}");
                $email_sent = false;
            }
        }
        
        return $email_sent;
    }

    /**
     * Notify managers and HR when employee submits a query about KPI assignments
     */
    public function notify_kpi_query($employee_id, $assignment_id, $query_text) {
        $this->load->library('email');
        $this->load->config('email');
        
        $email_config = $this->config->item('email');
        $this->email->initialize($email_config);
        
        // Get employee details
        $employee = $this->get_by_id($employee_id);
        
        // Get assignment details
        $assignment = $this->db->where('assignment_id', $assignment_id)->get('employee_kpi_assignments')->row();
        
        // Get period details
        $period = $this->db->where('period_id', $assignment->review_period_id)->get('review_periods')->row();
        
        // Get all managers and HR
        $managers = $this->get_all_managers($employee_id);
        $hr_persons = $this->get_all_hr_spokespersons($employee_id);
        
        $recipients = array_merge($managers, $hr_persons);
        
        if (empty($recipients)) {
            return false;
        }
        
        $subject = "KPI Assignment Query - {$employee->first_name} {$employee->last_name}";
        
        $message = "<h2>KPI Assignment Query</h2>";
        $message .= "<p><strong>{$employee->first_name} {$employee->last_name}</strong> ({$employee->employee_code}) has submitted a query about their KPI assignments for:</p>";
        $message .= "<p><strong>Period:</strong> {$period->period_name}</p>";
        $message .= "<p><strong>Query Time:</strong> " . date('F d, Y h:i A', strtotime($assignment->employee_query_at)) . "</p>";
        $message .= "<div style='background: #f8f9fa; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;'>";
        $message .= "<h3 style='margin-top: 0;'>Employee Query:</h3>";
        $message .= "<p>" . nl2br(htmlspecialchars($query_text)) . "</p>";
        $message .= "</div>";
        $message .= "<p>Please review the query and respond to the employee accordingly.</p>";
        $message .= "<hr>";
        $message .= "<p><small>This is an automated notification from the KPI Portal.</small></p>";
        
        $email_sent = true;
        foreach ($recipients as $recipient) {
            $this->email->clear();
            $this->email->from($email_config['smtp_from'], $email_config['smtp_from_name']);
            $this->email->to($recipient->email);
            $this->email->subject($subject);
            $this->email->message($message);
            
            if (!$this->email->send()) {
                log_message('error', "Failed to send KPI query notification to {$recipient->email}");
                $email_sent = false;
            }
        }
        
        return $email_sent;
    }
}
