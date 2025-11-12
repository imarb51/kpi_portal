<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Employee Controller
 * Handles employee operations - view KPIs, submit edit requests
 */
class Employee extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_login();
        $this->load->model('employee_model');
        $this->load->model('kpi_model');
    }

    /**
     * Employee Dashboard
     */
    public function dashboard() {
        $employee_id = $this->session->userdata('employee_id');
        
        // Get active period
        $data['active_period'] = $this->kpi_model->get_active_period();
        
        // Check if active period exists
        if ($data['active_period']) {
            // Get employee KPIs
            $data['kpis'] = $this->kpi_model->get_employee_kpis($employee_id, $data['active_period']->period_id);
            
            // Calculate totals
            $data['total_weightage'] = $this->kpi_model->get_total_weightage($employee_id, $data['active_period']->period_id);
            $data['total_score'] = $this->kpi_model->calculate_total_score($employee_id, $data['active_period']->period_id);
            
            // Get pending edit requests count
            $data['pending_requests_count'] = count($this->kpi_model->get_edit_requests($employee_id, 'PENDING'));
            
            // Check if review is finalized
            $data['is_finalized'] = $this->kpi_model->is_review_finalized($employee_id, $data['active_period']->period_id);
        } else {
            // No active period - set defaults
            $data['kpis'] = [];
            $data['total_weightage'] = 0;
            $data['total_score'] = 0;
            $data['pending_requests_count'] = 0;
            $data['is_finalized'] = false;
        }
        
        // Get manager info
        $data['manager'] = $this->employee_model->get_manager($employee_id);
        
        $data['page_title'] = 'My Dashboard';
        
        $this->load->view('common/header', $data);
        $this->load->view('employee/dashboard', $data);
        $this->load->view('common/footer');
    }

    /**
     * View my KPIs (read-only)
     */
    public function my_kpis() {
        $employee_id = $this->session->userdata('employee_id');
        
        // Get active period
        $data['active_period'] = $this->kpi_model->get_active_period();
        
        // Get all periods
        $data['all_periods'] = $this->kpi_model->get_all_periods();
        
        // Get selected period
        $selected_period_id = $this->input->get('period_id') ?: $data['active_period']->period_id;
        
        // Get employee KPIs
        $data['kpis'] = $this->kpi_model->get_employee_kpis($employee_id, $selected_period_id);
        
        // Calculate totals
        $data['total_weightage'] = $this->kpi_model->get_total_weightage($employee_id, $selected_period_id);
        $data['total_score'] = $this->kpi_model->calculate_total_score($employee_id, $selected_period_id);
        
        // Check for chat messages and agreement status
        $data['has_chat_conversation'] = false;
        $data['chat_employee_kpi_id'] = null;
        $data['chat_messages'] = [];
        $data['chat_is_locked'] = false;
        $data['has_agreed'] = false;
        
        if (!empty($data['kpis'])) {
            foreach ($data['kpis'] as $kpi) {
                // Check if employee has agreed to KPIs
                if ($kpi->is_locked || $kpi->employee_agreement_status === 'AGREED') {
                    $data['has_agreed'] = true;
                }
                
                // Show chat if there are any messages (regardless of locked/agreed status)
                $chat_messages = $this->kpi_model->get_kpi_chat_messages($kpi->employee_kpi_id);
                
                if (!empty($chat_messages)) {
                    $data['has_chat_conversation'] = true;
                    $data['chat_employee_kpi_id'] = $kpi->employee_kpi_id;
                    $data['chat_messages'] = $chat_messages;
                    $data['chat_is_locked'] = $kpi->is_locked || $kpi->employee_agreement_status === 'AGREED';
                    break;
                }
            }
        }
        
        // Get selected period details
        $data['selected_period'] = NULL;
        foreach ($data['all_periods'] as $period) {
            if ($period->period_id === $selected_period_id) {
                $data['selected_period'] = $period;
                break;
            }
        }
        
        $data['page_title'] = 'My KPIs';
        
        $this->load->view('common/header', $data);
        $this->load->view('employee/my_kpis', $data);
        $this->load->view('common/footer');
    }

    /**
     * Employee agrees to all assigned KPIs for a period
     */
    public function agree_all_kpis() {
        $employee_id = $this->session->userdata('employee_id');
        $period_id = $this->input->post('period_id');
        
        if (!$period_id) {
            $this->session->set_flashdata('error', 'Invalid period');
            redirect('employee/my_kpis');
            return;
        }
        
        // Update all KPIs for this employee and period to AGREED and LOCK them
        $this->db->where('employee_id', $employee_id);
        $this->db->where('review_period_id', $period_id);
        $this->db->update('employee_kpis', [
            'employee_agreement_status' => 'AGREED',
            'employee_agreement_date' => date('Y-m-d H:i:s'),
            'is_locked' => 1  // Lock KPIs when employee agrees
        ]);
        
        if ($this->db->affected_rows() > 0) {
            // Send notification to all managers
            $this->load->model('employee_model');
            $managers = $this->employee_model->get_employee_managers($employee_id);
            
            if (!empty($managers)) {
                $this->load->helper('email');
                $employee = $this->employee_model->get_by_id($employee_id);
                
                foreach ($managers as $manager) {
                    notify_kpi_agreement($manager->email, $employee, $period_id);
                }
            }
            
            $this->session->set_flashdata('success', 'Thank you! You have successfully agreed to all assigned KPIs.');
        } else {
            $this->session->set_flashdata('error', 'No KPIs found to agree');
        }
        
        redirect('employee/my_kpis?period_id=' . $period_id);
    }

    /**
     * Request edit for KPIs
     */
    public function request_kpi_edit() {
        $employee_id = $this->session->userdata('employee_id');
        $period_id = $this->input->get('period_id') ?: $this->input->post('period_id');
        
        if ($this->input->method() === 'post') {
            $notes = $this->input->post('edit_notes');
            
            if (empty($notes)) {
                $this->session->set_flashdata('error', 'Please provide details about the changes you would like');
                redirect('employee/request_kpi_edit?period_id=' . $period_id);
                return;
            }
            
            // Update all KPIs for this employee and period to REQUESTED_EDIT
            $this->db->where('employee_id', $employee_id);
            $this->db->where('review_period_id', $period_id);
            $this->db->update('employee_kpis', [
                'employee_agreement_status' => 'REQUESTED_EDIT',
                'employee_agreement_date' => date('Y-m-d H:i:s'),
                'employee_agreement_notes' => $notes
            ]);
            
            if ($this->db->affected_rows() > 0) {
                // Add chat message only once (for the first KPI in the period)
                $kpis = $this->kpi_model->get_employee_kpis($employee_id, $period_id);
                if (!empty($kpis)) {
                    // Only add message to the first KPI to avoid duplicates
                    $this->kpi_model->add_chat_message(
                        $kpis[0]->employee_kpi_id,
                        $employee_id,
                        $employee_id,
                        'EMPLOYEE',
                        $notes
                    );
                }
                // Send notification to all managers
                $this->load->model('employee_model');
                $managers = $this->employee_model->get_employee_managers($employee_id);
                
                if (!empty($managers)) {
                    $this->load->helper('email');
                    $employee = $this->employee_model->get_by_id($employee_id);
                    
                    foreach ($managers as $manager) {
                        notify_kpi_query($manager->email, $employee, $period_id, $notes);
                    }
                }
                
                $this->session->set_flashdata('success', 'Your edit request has been sent to your manager(s).');
            } else {
                $this->session->set_flashdata('error', 'Failed to submit edit request');
            }
            
            redirect('employee/my_kpis?period_id=' . $period_id);
            return;
        }
        
        // GET request - show form
        $data['period_id'] = $period_id;
        $data['kpis'] = $this->kpi_model->get_employee_kpis($employee_id, $period_id);
        $data['page_title'] = 'Request KPI Edit';
        
        $this->load->view('common/header', $data);
        $this->load->view('employee/request_kpi_edit', $data);
        $this->load->view('common/footer');
    }

    /**
     * View assigned KPIs with agreement/query options
     */
    public function assigned_kpis() {
        $employee_id = $this->session->userdata('employee_id');
        
        // Get active period
        $data['active_period'] = $this->kpi_model->get_active_period();
        
        // Get all periods
        $data['all_periods'] = $this->kpi_model->get_all_periods();
        
        // Get selected period
        $selected_period_id = $this->input->get('period_id') ?: ($data['active_period'] ? $data['active_period']->period_id : null);
        
        if ($selected_period_id) {
            // Get employee KPIs with category info
            $this->db->select('ek.*, kt.kpi_name, kt.description, kc.category_name')
                     ->from('employee_kpis ek')
                     ->join('kpi_templates kt', 'ek.template_id = kt.template_id')
                     ->join('kpi_categories kc', 'kt.category_id = kc.category_id')
                     ->where('ek.employee_id', $employee_id)
                     ->where('ek.review_period_id', $selected_period_id)
                     ->order_by('kc.category_name, kt.kpi_name');
            
            $data['kpis'] = $this->db->get()->result();
            
            // Calculate totals
            $total_weightage = 0;
            $total_weighted_score = 0;
            
            foreach ($data['kpis'] as $kpi) {
                $total_weightage += $kpi->weightage;
                if ($kpi->weighted_score !== NULL) {
                    $total_weighted_score += $kpi->weighted_score;
                }
            }
            
            $data['total_weightage'] = $total_weightage;
            $data['total_weighted_score'] = $total_weighted_score;
            
            // Get assignment record
            $this->db->select('*')
                     ->from('employee_kpi_assignments')
                     ->where('employee_id', $employee_id)
                     ->where('review_period_id', $selected_period_id);
            
            $data['assignment'] = $this->db->get()->row();
        } else {
            $data['kpis'] = [];
            $data['total_weightage'] = 0;
            $data['total_weighted_score'] = 0;
            $data['assignment'] = null;
        }
        
        // Get selected period details
        $data['selected_period'] = NULL;
        if ($selected_period_id) {
            foreach ($data['all_periods'] as $period) {
                if ($period->period_id === $selected_period_id) {
                    $data['selected_period'] = $period;
                    break;
                }
            }
        }
        
        $data['page_title'] = 'My Assigned KPIs';
        
        $this->load->view('common/header', $data);
        $this->load->view('employee/assigned_kpis', $data);
        $this->load->view('common/footer');
    }

    /**
     * Agree to KPI assignments
     */
    public function agree_to_kpis() {
        if ($this->input->method() !== 'post') {
            redirect('employee/assigned_kpis');
            return;
        }
        
        $employee_id = $this->session->userdata('employee_id');
        $assignment_id = $this->input->post('assignment_id');
        
        if (empty($assignment_id)) {
            $this->session->set_flashdata('error', 'Invalid assignment');
            redirect('employee/assigned_kpis');
            return;
        }
        
        // Update assignment status
        $this->db->where('assignment_id', $assignment_id)
                 ->where('employee_id', $employee_id)
                 ->update('employee_kpi_assignments', [
                     'employee_agreement_status' => 'AGREED',
                     'employee_agreement_at' => date('Y-m-d H:i:s')
                 ]);
        
        if ($this->db->affected_rows() > 0) {
            // Send notification email to managers and HR
            $this->load->model('employee_model');
            $result = $this->employee_model->notify_kpi_agreement($employee_id, $assignment_id);
            
            if ($result) {
                $this->session->set_flashdata('success', 'You have agreed to the KPI assignments. Your manager and HR have been notified.');
            } else {
                $this->session->set_flashdata('success', 'You have agreed to the KPI assignments, but email notification failed.');
            }
        } else {
            $this->session->set_flashdata('error', 'Failed to update agreement status');
        }
        
        redirect('employee/assigned_kpis');
    }

    /**
     * Submit query about KPI assignments
     */
    public function submit_kpi_query() {
        if ($this->input->method() !== 'post') {
            redirect('employee/assigned_kpis');
            return;
        }
        
        $employee_id = $this->session->userdata('employee_id');
        $assignment_id = $this->input->post('assignment_id');
        $query_text = $this->input->post('query_text');
        
        if (empty($assignment_id) || empty($query_text)) {
            $this->session->set_flashdata('error', 'Please provide your query');
            redirect('employee/assigned_kpis');
            return;
        }
        
        // Update assignment with query
        $this->db->where('assignment_id', $assignment_id)
                 ->where('employee_id', $employee_id)
                 ->update('employee_kpi_assignments', [
                     'employee_agreement_status' => 'QUERIED',
                     'employee_query' => $query_text,
                     'employee_query_at' => date('Y-m-d H:i:s')
                 ]);
        
        if ($this->db->affected_rows() > 0) {
            // Send notification email to managers and HR
            $this->load->model('employee_model');
            $result = $this->employee_model->notify_kpi_query($employee_id, $assignment_id, $query_text);
            
            if ($result) {
                $this->session->set_flashdata('success', 'Your query has been submitted. Your manager and HR will be notified.');
            } else {
                $this->session->set_flashdata('success', 'Your query has been submitted, but email notification failed.');
            }
        } else {
            $this->session->set_flashdata('error', 'Failed to submit query');
        }
        
        redirect('employee/assigned_kpis');
    }

    /**
     * View single KPI details
     */
    public function view_kpi($employee_kpi_id) {
        $kpi = $this->kpi_model->get_kpi_by_id($employee_kpi_id);
        
        // Verify this KPI belongs to logged-in employee
        if (!$kpi || $kpi->employee_id !== $this->session->userdata('employee_id')) {
            $this->session->set_flashdata('error', 'Access denied');
            redirect('employee/my_kpis');
        }
        
        $data['kpi'] = $kpi;
        $data['history'] = $this->kpi_model->get_score_history($kpi->score_id);
        $data['page_title'] = 'KPI Details';
        
        $this->load->view('common/header', $data);
        $this->load->view('employee/view_kpi', $data);
        $this->load->view('common/footer');
    }

    /**
     * Request edit for KPI
     */
    public function request_edit($employee_kpi_id) {
        log_message('debug', '=== REQUEST_EDIT START ===');
        log_message('debug', 'Employee KPI ID: ' . $employee_kpi_id);
        log_message('debug', 'Method: ' . $this->input->method());
        
        $kpi = $this->kpi_model->get_kpi_by_id($employee_kpi_id);
        
        // Verify this KPI belongs to logged-in employee
        if (!$kpi || $kpi->employee_id !== $this->session->userdata('employee_id')) {
            log_message('error', 'Access denied for employee_kpi_id: ' . $employee_kpi_id);
            $this->session->set_flashdata('error', 'Access denied');
            redirect('employee/my_kpis');
        }
        
        if ($this->input->method() === 'post') {
            log_message('debug', 'POST request detected');
            log_message('debug', 'POST data: ' . json_encode($this->input->post()));
            
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('field_to_change', 'Field to Change', 'required');
            $this->form_validation->set_rules('requested_value', 'Requested Value', 'required');
            $this->form_validation->set_rules('remark', 'Remark', 'required|min_length[10]');
            
            if ($this->form_validation->run() === TRUE) {
                log_message('debug', 'Form validation passed');
                
                $user_id = $this->session->userdata('user_id'); // Use user_id instead of employee_id
                log_message('debug', 'User ID: ' . $user_id);
                
                $request_data = [
                    'field_to_change' => $this->input->post('field_to_change'),
                    'current_value' => $this->input->post('current_value'),
                    'requested_value' => $this->input->post('requested_value'),
                    'remark' => $this->input->post('remark')
                ];
                
                log_message('debug', 'Request data: ' . json_encode($request_data));
                
                $result = $this->kpi_model->submit_edit_request($employee_kpi_id, $user_id, $request_data);
                
                log_message('debug', 'Submit result: ' . ($result ? 'SUCCESS' : 'FAILED'));
                
                if ($result) {
                    $this->session->set_flashdata('success', 'Edit request submitted successfully. Your manager will review it.');
                    redirect('employee/my_edit_requests');
                } else {
                    log_message('error', 'Database insert failed');
                    log_message('error', 'DB Error: ' . $this->db->error()['message']);
                    $this->session->set_flashdata('error', 'Failed to submit edit request. Please try again.');
                }
            } else {
                log_message('error', 'Form validation failed');
                log_message('error', 'Validation errors: ' . validation_errors());
                $this->session->set_flashdata('error', 'Please correct the errors: ' . validation_errors());
            }
        }
        
        $data['kpi'] = $kpi;
        $data['page_title'] = 'Request KPI Edit';
        
        $this->load->view('common/header', $data);
        $this->load->view('employee/request_edit', $data);
        $this->load->view('common/footer');
    }

    /**
     * View my edit requests
     */
    public function my_edit_requests() {
        $employee_id = $this->session->userdata('employee_id');
        
        $data['requests'] = $this->kpi_model->get_edit_requests($employee_id);
        $data['page_title'] = 'My Edit Requests';
        
        $this->load->view('common/header', $data);
        $this->load->view('employee/my_edit_requests', $data);
        $this->load->view('common/footer');
    }

    /**
     * Accept Final Report - Finalize KPI review and send notification to manager
     */
    public function accept_final_report() {
        $employee_id = $this->session->userdata('employee_id');
        
        // Get active period
        $active_period = $this->kpi_model->get_active_period();
        
        if (!$active_period) {
            $this->session->set_flashdata('error', 'No active review period found');
            redirect('employee/dashboard');
            return;
        }
        
        // Check if already finalized
        if ($this->kpi_model->is_review_finalized($employee_id, $active_period->period_id)) {
            $this->session->set_flashdata('warning', 'Your review has already been finalized for this period');
            redirect('employee/dashboard');
            return;
        }
        
        // Finalize the review
        $result = $this->kpi_model->finalize_employee_review($employee_id, $active_period->period_id);
        
        if ($result['success']) {
            $message = $result['message'];
            if ($result['email_sent']) {
                $message .= ' An email notification has been sent to your reporting manager.';
            } else {
                $message .= ' However, email notification could not be sent. Please inform your manager manually.';
            }
            $this->session->set_flashdata('success', $message);
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }
        
        redirect('employee/dashboard');
    }

    /**
     * View my performance reviews
     */
    public function my_reviews() {
        $employee_id = $this->session->userdata('employee_id');
        
        // Get all review periods
        $this->db->select('pr.*, rp.period_name, rp.period_type, rp.cycle_year,
                          u.first_name as reviewer_first_name, u.last_name as reviewer_last_name')
                 ->from('performance_reviews pr')
                 ->join('review_periods rp', 'pr.review_period_id = rp.period_id')
                 ->join('users u', 'pr.reviewer_id = u.user_id', 'left')
                 ->where('pr.employee_id', $employee_id)
                 ->where('u.deleted_at IS NULL')
                 ->order_by('rp.start_date', 'DESC');
        
        $data['reviews'] = $this->db->get()->result();
        $data['page_title'] = 'My Performance Reviews';
        
        $this->load->view('common/header', $data);
        $this->load->view('employee/my_reviews', $data);
        $this->load->view('common/footer');
    }

    /**
     * Change Password
     */
    public function change_password() {
        $user_id = $this->session->userdata('user_id');
        
        if ($this->input->method() === 'post') {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('current_password', 'Current Password', 'required');
            $this->form_validation->set_rules('new_password', 'New Password', 'required|min_length[6]');
            $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[new_password]');
            
            if ($this->form_validation->run() === TRUE) {
                // Get current password hash from database
                $user = $this->db->where('user_id', $user_id)->get('users')->row();
                
                if (!$user) {
                    $this->session->set_flashdata('error', 'User not found');
                    redirect('employee/change_password');
                    return;
                }
                
                // Verify current password
                if (!password_verify($this->input->post('current_password'), $user->password_hash)) {
                    $this->session->set_flashdata('error', 'Current password is incorrect');
                    redirect('employee/change_password');
                    return;
                }
                
                // Check if new password is different from current
                if ($this->input->post('current_password') === $this->input->post('new_password')) {
                    $this->session->set_flashdata('error', 'New password must be different from current password');
                    redirect('employee/change_password');
                    return;
                }
                
                // Update password
                $new_password_hash = password_hash($this->input->post('new_password'), PASSWORD_BCRYPT);
                $updated = $this->db->where('user_id', $user_id)
                                   ->update('users', ['password_hash' => $new_password_hash]);
                
                if ($updated) {
                    log_message('info', "Password changed successfully for user_id: {$user_id}");
                    $this->session->set_flashdata('success', 'Password changed successfully!');
                    redirect('employee/dashboard');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update password. Please try again.');
                }
            }
        }
        
        $data['page_title'] = 'Change Password';
        $data['user_role'] = 'employee';
        
        $this->load->view('common/header', $data);
        $this->load->view('common/change_password', $data);
        $this->load->view('common/footer');
    }

    /**
     * Send reply in KPI chat conversation
     */
    public function send_kpi_reply() {
        $employee_id = $this->session->userdata('employee_id');
        $employee_kpi_id = $this->input->post('employee_kpi_id');
        $period_id = $this->input->post('period_id');
        $message = $this->input->post('message');
        
        if (empty($message)) {
            $this->session->set_flashdata('error', 'Please enter a message');
            redirect('employee/my_kpis?period_id=' . $period_id);
            return;
        }
        
        // Add the employee's reply message
        $result = $this->kpi_model->add_chat_message(
            $employee_kpi_id,
            $employee_id,
            $employee_id,
            'EMPLOYEE',
            $message
        );
        
        if ($result) {
            $this->session->set_flashdata('success', 'Reply sent to manager');
        } else {
            $this->session->set_flashdata('error', 'Failed to send reply');
        }
        
        redirect('employee/my_kpis?period_id=' . $period_id);
    }

    /**
     * Check if user is logged in
     */
    private function check_login() {
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
    }
}
