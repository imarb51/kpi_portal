<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Manager Controller
 * Handles manager operations - team management, KPI assignment, scoring
 */
class Manager extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_login();
        // Load models FIRST before using them
        $this->load->model('employee_model');
        $this->load->model('kpi_model');
        // Then check manager role (which uses employee_model)
        $this->check_manager_role();
    }

    /**
     * Manager Dashboard
     */
    public function dashboard() {
        $employee_id = $this->session->userdata('employee_id');
        
        // Get direct reports
        $data['team_members'] = $this->employee_model->get_direct_reports($employee_id);
        
        // Get pending edit requests
        $data['pending_requests'] = $this->kpi_model->get_pending_requests_for_manager($employee_id);
        
        // Get active period
        $data['active_period'] = $this->kpi_model->get_active_period();
        
        $data['page_title'] = 'Manager Dashboard';
        
        $this->load->view('common/header', $data);
        $this->load->view('manager/dashboard', $data);
        $this->load->view('common/footer');
    }

    /**
     * View team members
     */
    public function team() {
        $employee_id = $this->session->userdata('employee_id');
        
        $data['team_members'] = $this->employee_model->get_direct_reports($employee_id);
        
        // Count pending edit requests
        $this->db->select('ek.employee_id, ek.review_period_id')
                 ->from('employee_kpis ek')
                 ->join('employee_reporting_managers erm', 'ek.employee_id = erm.employee_id')
                 ->where('erm.manager_id', $employee_id)
                 ->where('ek.employee_agreement_status', 'REQUESTED_EDIT')
                 ->group_by('ek.employee_id, ek.review_period_id');
        
        $data['pending_requests_count'] = $this->db->count_all_results();
        
        $data['page_title'] = 'My Team';
        
        $this->load->view('common/header', $data);
        $this->load->view('manager/team', $data);
        $this->load->view('common/footer');
    }

    /**
     * View employee details and KPIs
     */
    public function view_employee($employee_id) {
        // Verify this employee reports to current manager
        if (!$this->verify_direct_report($employee_id)) {
            $this->session->set_flashdata('error', 'Access denied');
            redirect('manager/team');
        }
        
        $data['employee'] = $this->employee_model->get_by_id($employee_id);
        $data['active_period'] = $this->kpi_model->get_active_period();
        $data['kpis'] = $this->kpi_model->get_employee_kpis($employee_id, $data['active_period']->period_id);
        
        // Get pending edit requests for this specific employee
        $data['pending_edit_requests'] = $this->kpi_model->get_employee_pending_requests($employee_id);
        
        // Check if employee has requested edits or if there's an ongoing conversation
        $data['has_bulk_edit_request'] = false;
        $data['bulk_edit_notes'] = '';
        $data['bulk_edit_date'] = '';
        $data['edit_request_status'] = '';
        $data['edit_request_employee_kpi_id'] = null;
        $data['chat_messages'] = [];
        $data['chat_is_locked'] = false;
        
        if (!empty($data['kpis'])) {
            foreach ($data['kpis'] as $kpi) {
                // Determine which phase messages to show
                if (isset($kpi->is_finalized) && $kpi->is_finalized == 1) {
                    // Finalized: Show both setup and scoring messages (read-only)
                    $setup_messages = $this->kpi_model->get_kpi_chat_messages($kpi->employee_kpi_id, 'SETUP');
                    $scoring_messages = $this->kpi_model->get_kpi_chat_messages($kpi->employee_kpi_id, 'SCORING');
                    
                    $data['has_bulk_edit_request'] = true;
                    $data['bulk_edit_notes'] = $kpi->employee_agreement_notes;
                    $data['bulk_edit_date'] = $kpi->employee_agreement_date;
                    $data['edit_request_status'] = $kpi->employee_agreement_status;
                    $data['edit_request_employee_kpi_id'] = $kpi->employee_kpi_id;
                    $data['setup_chat_messages'] = $setup_messages;
                    $data['scoring_chat_messages'] = $scoring_messages;
                    $data['show_both_chats'] = true;
                    $data['chat_is_locked'] = true;
                    break;
                    
                } elseif ($kpi->employee_agreement_status === 'AGREED') {
                    // Scoring Mode: Show setup (read-only) and scoring (active)
                    $setup_messages = $this->kpi_model->get_kpi_chat_messages($kpi->employee_kpi_id, 'SETUP');
                    $scoring_messages = $this->kpi_model->get_kpi_chat_messages($kpi->employee_kpi_id, 'SCORING');
                    
                    $data['has_bulk_edit_request'] = true;
                    $data['bulk_edit_notes'] = $kpi->employee_agreement_notes;
                    $data['bulk_edit_date'] = $kpi->employee_agreement_date;
                    $data['edit_request_status'] = $kpi->employee_agreement_status;
                    $data['edit_request_employee_kpi_id'] = $kpi->employee_kpi_id;
                    $data['setup_chat_messages'] = $setup_messages;
                    $data['scoring_chat_messages'] = $scoring_messages;
                    $data['show_both_chats'] = true;
                    $data['chat_is_locked'] = false; // Active chat in scoring
                    break;
                    
                } else {
                    // Setup Mode: Show only setup phase messages
                    $chat_messages = $this->kpi_model->get_kpi_chat_messages($kpi->employee_kpi_id, 'SETUP');
                    
                    // Show chat if there are any messages or edit requested
                    if (!empty($chat_messages) || $kpi->employee_agreement_status === 'REQUESTED_EDIT') {
                        $data['has_bulk_edit_request'] = true;
                        $data['bulk_edit_notes'] = $kpi->employee_agreement_notes;
                        $data['bulk_edit_date'] = $kpi->employee_agreement_date;
                        $data['edit_request_status'] = $kpi->employee_agreement_status;
                        $data['edit_request_employee_kpi_id'] = $kpi->employee_kpi_id;
                        $data['chat_messages'] = $chat_messages;
                        $data['show_both_chats'] = false;
                        $data['chat_is_locked'] = false;
                        break;
                    }
                }
            }
        }
        
        // Calculate totals
        $data['total_weightage'] = $this->kpi_model->get_total_weightage($employee_id, $data['active_period']->period_id);
        $data['total_score'] = $this->kpi_model->calculate_total_score($employee_id, $data['active_period']->period_id);
        
        $data['page_title'] = 'Employee KPIs - ' . $data['employee']->first_name . ' ' . $data['employee']->last_name;
        
        $this->load->view('common/header', $data);
        $this->load->view('manager/view_employee', $data);
        $this->load->view('common/footer');
    }

    /**
     * Assign KPI template to employee
     */
    public function assign_kpi($employee_id) {
        // Verify this employee reports to current manager
        if (!$this->verify_direct_report($employee_id)) {
            $this->session->set_flashdata('error', 'Access denied');
            redirect('manager/team');
        }
        
        // Load admin model for template access
        $this->load->model('admin_model');
        
        if ($this->input->method() === 'post') {
            // Check if template selection or KPI assignment
            if ($this->input->post('action') == 'load_template') {
                // AJAX request to load template KPIs
                $template_name = $this->input->post('template_name');
                $kpis = $this->admin_model->get_kpis_by_template_name($template_name);
                echo json_encode(['kpis' => $kpis]);
                return;
            }
            
            // Regular form submission - assign all KPIs
            $template_name = $this->input->post('template_name');
            $period_id = $this->input->post('period_id');
            $kpis = $this->input->post('kpis'); // Array of KPIs with weightage and scores
            $manager_id = $this->session->userdata('employee_id');
            
            // Validate total weightage
            $total_weightage = 0;
            foreach ($kpis as $kpi) {
                $total_weightage += floatval($kpi['weightage']);
            }
            
            if (abs($total_weightage - 100) > 0.01) {
                $this->session->set_flashdata('error', 'Total weightage must equal 100%. Current: ' . $total_weightage . '%');
                redirect('manager/assign_kpi/' . $employee_id);
                return;
            }
            
            // Assign all KPIs from template
            $result = $this->kpi_model->assign_template_kpis($employee_id, $template_name, $period_id, $manager_id, $kpis);
            
            if ($result) {
                $this->session->set_flashdata('success', count($kpis) . ' KPIs assigned successfully from template: ' . $template_name);
            } else {
                $this->session->set_flashdata('error', 'Failed to assign KPIs');
            }
            
            redirect('manager/view_employee/' . $employee_id);
            return;
        }
        
        // GET request - show form
        $data['employee'] = $this->employee_model->get_by_id($employee_id);
        $data['active_period'] = $this->kpi_model->get_active_period();
        
        // Get employee's department to auto-fetch relevant templates
        $employee_details = $this->admin_model->get_employee_full_details($employee_id);
        $department_id = $employee_details->department_id ?? NULL;
        
        // Get templates filtered by employee's department
        $data['templates'] = $this->admin_model->get_templates_grouped(NULL, $department_id);
        
        // Get currently assigned KPIs
        if ($data['active_period']) {
            $data['assigned_kpis'] = $this->kpi_model->get_employee_kpis($employee_id, $data['active_period']->period_id);
        } else {
            $data['assigned_kpis'] = [];
        }
        
        $data['page_title'] = 'Assign KPI - ' . $data['employee']->first_name . ' ' . $data['employee']->last_name;
        
        $this->load->view('common/header', $data);
        $this->load->view('manager/assign_kpi_template', $data);
        $this->load->view('common/footer');
    }

    /**
     * Edit KPI score (weightage and score)
     */
    public function edit_kpi_score($employee_kpi_id) {
        $kpi = $this->kpi_model->get_kpi_by_id($employee_kpi_id);
        
        if (!$kpi) {
            $this->session->set_flashdata('error', 'KPI not found');
            redirect('manager/dashboard');
        }
        
        // Verify this employee reports to current manager
        if (!$this->verify_direct_report($kpi->employee_id)) {
            $this->session->set_flashdata('error', 'Access denied');
            redirect('manager/dashboard');
        }
        
        if ($this->input->method() === 'post') {
            $weightage = $this->input->post('weightage');
            $score = $this->input->post('score');
            $manager_id = $this->session->userdata('employee_id');
            
            $update_data = [];
            if ($weightage !== NULL && $weightage !== '') {
                $update_data['weightage'] = $weightage;
            }
            if ($score !== NULL && $score !== '') {
                $update_data['score'] = $score;
            }
            
            if (!empty($update_data)) {
                $result = $this->kpi_model->update_score($kpi->score_id, $update_data, $manager_id);
                
                if ($result) {
                    $this->session->set_flashdata('success', 'KPI score updated successfully');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update KPI score');
                }
            }
            
            redirect('manager/view_employee/' . $kpi->employee_id);
        }
        
        $data['kpi'] = $kpi;
        $data['history'] = $this->kpi_model->get_score_history($kpi->score_id);
        $data['page_title'] = 'Edit KPI Score';
        
        $this->load->view('common/header', $data);
        $this->load->view('manager/edit_kpi_score', $data);
        $this->load->view('common/footer');
    }

    /**
     * View all edit requests
     */
    /**
     * Edit Requests - DEPRECATED (Now shown on individual employee's view_employee page)
     * Keeping for backward compatibility, redirects to team page
     */
    public function edit_requests() {
        $this->session->set_flashdata('info', 'Edit requests are now shown on each employee\'s KPI page. Click "View KPI" for any team member.');
        redirect('manager/team');
    }

    /**
     * KPI Edit Requests - DEPRECATED (Now shown on individual employee's view_employee page)
     * Keeping for backward compatibility, redirects to team page
     */
    public function kpi_edit_requests() {
        $this->session->set_flashdata('info', 'Edit requests are now shown on each employee\'s KPI page. Click "View KPI" for any team member.');
        redirect('manager/team');
    }

    /**
     * Review edit request (approve/reject)
     */
    public function review_edit_request($request_id) {
        if ($this->input->method() === 'post') {
            $action = $this->input->post('action'); // 'approve' or 'reject'
            $status = ($action === 'approve') ? 'APPROVED' : 'REJECTED';
            $comments = $this->input->post('review_comments'); // Get review comments from form
            $manager_id = $this->session->userdata('employee_id');
            
            // Get the employee_id before processing to redirect back
            $this->db->select('ekpi.employee_id')
                     ->from('kpi_edit_requests ker')
                     ->join('employee_kpis ekpi', 'ker.employee_kpi_id = ekpi.employee_kpi_id')
                     ->where('ker.request_id', $request_id);
            $request_info = $this->db->get()->row();
            
            $result = $this->kpi_model->review_edit_request($request_id, $status, $manager_id, $comments);
            
            if ($result) {
                $message = $status === 'APPROVED' ? 'Edit request approved successfully' : 'Edit request rejected';
                if ($comments) {
                    $message .= ' with your feedback';
                }
                $this->session->set_flashdata('success', $message);
            } else {
                $this->session->set_flashdata('error', 'Failed to process request');
            }
            
            // Redirect back to the employee's KPI page
            if ($request_info && $request_info->employee_id) {
                redirect('manager/view_employee/' . $request_info->employee_id);
            } else {
                redirect('manager/team');
            }
            return;
        }
        
        // If GET request, redirect to team page (no separate review page needed)
        $this->session->set_flashdata('error', 'Invalid request');
        redirect('manager/team');
    }

    /**
     * Edit individual KPI (weightage and score)
     */
    public function edit_kpi($employee_kpi_id) {
        $kpi = $this->kpi_model->get_kpi_by_id($employee_kpi_id);
        
        if (!$kpi || !$this->verify_direct_report($kpi->employee_id)) {
            $this->session->set_flashdata('error', 'Access denied');
            redirect('manager/dashboard');
        }
        
        if ($this->input->method() === 'post') {
            $weightage = $this->input->post('weightage');
            $score = $this->input->post('score');
            $manager_id = $this->session->userdata('employee_id');
            
            $update_data = [
                'weightage' => floatval($weightage),
                'last_edited_by' => $manager_id,
                'last_edited_at' => date('Y-m-d H:i:s')
            ];
            
            // Update score if provided
            if ($score !== '' && $score !== NULL) {
                $update_data['score'] = floatval($score);
            }
            
            $result = $this->kpi_model->update_score($kpi->score_id, $update_data);
            
            if ($result) {
                $this->session->set_flashdata('success', 'KPI updated successfully');
            } else {
                $this->session->set_flashdata('error', 'Failed to update KPI');
            }
            
            redirect('manager/view_employee/' . $kpi->employee_id);
            return;
        }
        
        $data['kpi'] = $kpi;
        $data['employee'] = $this->employee_model->get_by_id($kpi->employee_id);
        $data['page_title'] = 'Edit KPI';
        
        $this->load->view('common/header', $data);
        $this->load->view('manager/edit_kpi', $data);
        $this->load->view('common/footer');
    }

    /**
     * Delete KPI assignment
     */
    public function delete_kpi($employee_kpi_id) {
        $kpi = $this->kpi_model->get_kpi_by_id($employee_kpi_id);
        
        if (!$kpi || !$this->verify_direct_report($kpi->employee_id)) {
            $this->session->set_flashdata('error', 'Access denied');
            redirect('manager/dashboard');
        }
        
        $result = $this->kpi_model->delete_kpi($employee_kpi_id);
        
        if ($result) {
            $this->session->set_flashdata('success', 'KPI deleted successfully');
        } else {
            $this->session->set_flashdata('error', 'Failed to delete KPI');
        }
        
        redirect('manager/view_employee/' . $kpi->employee_id);
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
                    redirect('manager/change_password');
                    return;
                }
                
                // Verify current password
                if (!password_verify($this->input->post('current_password'), $user->password_hash)) {
                    $this->session->set_flashdata('error', 'Current password is incorrect');
                    redirect('manager/change_password');
                    return;
                }
                
                // Check if new password is different from current
                if ($this->input->post('current_password') === $this->input->post('new_password')) {
                    $this->session->set_flashdata('error', 'New password must be different from current password');
                    redirect('manager/change_password');
                    return;
                }
                
                // Update password
                $new_password_hash = password_hash($this->input->post('new_password'), PASSWORD_BCRYPT);
                $updated = $this->db->where('user_id', $user_id)
                                   ->update('users', ['password_hash' => $new_password_hash]);
                
                if ($updated) {
                    log_message('info', "Password changed successfully for user_id: {$user_id}");
                    $this->session->set_flashdata('success', 'Password changed successfully!');
                    redirect('manager/dashboard');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update password. Please try again.');
                }
            }
        }
        
        $data['page_title'] = 'Change Password';
        $data['user_role'] = 'manager';
        
        $this->load->view('common/header', $data);
        $this->load->view('common/change_password', $data);
        $this->load->view('common/footer');
    }

    /**
     * View KPI edit requests from employees
     */
    // public function kpi_edit_requests() {
    //     $manager_id = $this->session->userdata('employee_id');
        
    //     // Get all employees who report to this manager and have pending edit requests
    //     $this->db->select('u.user_id as employee_id, u.employee_code, u.first_name, u.last_name,
    //                       ek.review_period_id, ek.employee_agreement_notes, ek.employee_agreement_date,
    //                       rp.period_name')
    //              ->from('employee_kpis ek')
    //              ->join('users u', 'ek.employee_id = u.user_id')
    //              ->join('review_periods rp', 'ek.review_period_id = rp.period_id')
    //              ->join('employee_reporting_managers erm', 'u.user_id = erm.employee_id')
    //              ->where('erm.manager_id', $manager_id)
    //              ->where('ek.employee_agreement_status', 'REQUESTED_EDIT')
    //              ->where('u.deleted_at IS NULL')
    //              ->group_by('u.user_id, ek.review_period_id')
    //              ->order_by('ek.employee_agreement_date', 'DESC');
        
    //     $data['requests'] = $this->db->get()->result();
    //     $data['page_title'] = 'KPI Edit Requests';
        
    //     $this->load->view('common/header', $data);
    //     $this->load->view('manager/kpi_edit_requests', $data);
    //     $this->load->view('common/footer');
    // }

    /**
     * Edit employee KPIs (after edit request or direct edit)
     */
    public function edit_employee_kpis($employee_id) {
        if (!$this->verify_direct_report($employee_id)) {
            $this->session->set_flashdata('error', 'Access denied');
            redirect('manager/team');
        }
        
        $period_id = $this->input->get('period_id');
        
        // Get employee data (needed for both GET and POST)
        $employee = $this->employee_model->get_by_id($employee_id);
        
        if ($this->input->method() === 'post') {
            // Check if employee has already agreed BEFORE any processing
            // This determines if we're in "Setup Mode" or "Scoring Mode"
            $current_kpis = $this->kpi_model->get_employee_kpis($employee_id, $period_id);
            $employee_has_agreed = false;
            if (!empty($current_kpis)) {
                $employee_has_agreed = ($current_kpis[0]->employee_agreement_status === 'AGREED');
            }
            
            // Process KPI updates
            $kpis = $this->input->post('kpis');
            $new_kpis = $this->input->post('new_kpis');
            $delete_kpis = $this->input->post('delete_kpis');
            $manager_notes = $this->input->post('manager_notes');
            $manager_id = $this->session->userdata('employee_id');
            $user_id = $this->session->userdata('user_id');
            
            // Handle deletions first
            if (!empty($delete_kpis)) {
                foreach ($delete_kpis as $kpi_id) {
                    if (!empty($kpi_id)) {
                        // Delete from kpi_scores first (foreign key constraint)
                        $this->db->where('employee_kpi_id', $kpi_id);
                        $this->db->delete('kpi_scores');
                        
                        // Then delete from employee_kpis
                        $this->db->where('employee_kpi_id', $kpi_id);
                        $this->db->delete('employee_kpis');
                    }
                }
            }
            
            // Handle new KPIs
            if (!empty($new_kpis)) {
                foreach ($new_kpis as $new_kpi) {
                    if (!empty($new_kpi['kpi_name'])) {
                        $employee_kpi_id = $this->kpi_model->generate_uuid();
                        
                        // Create a custom template for this KPI
                        $template_id = $this->kpi_model->generate_uuid();
                        $template_data = [
                            'template_id' => $template_id,
                            'template_name' => 'Custom - ' . $employee->first_name . ' ' . $employee->last_name,
                            'kpi_name' => $new_kpi['kpi_name'],
                            'category_id' => $new_kpi['category_id'],
                            'description' => !empty($new_kpi['description']) ? $new_kpi['description'] : NULL,
                            'weightage' => 0,
                            'default_score' => 0,
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                        $this->db->insert('kpi_templates', $template_data);
                        
                        // Insert employee KPI
                        $kpi_data = [
                            'employee_kpi_id' => $employee_kpi_id,
                            'employee_id' => $employee_id,
                            'template_id' => $template_id,
                            'review_period_id' => $period_id,
                            'assigned_by' => $manager_id,
                            'assigned_date' => date('Y-m-d'),
                            'status' => 'ASSIGNED'
                        ];
                        $this->db->insert('employee_kpis', $kpi_data);
                        
                        // Insert score
                        $score_data = [
                            'score_id' => $this->kpi_model->generate_uuid(),
                            'employee_kpi_id' => $employee_kpi_id,
                            'weightage' => floatval($new_kpi['weightage']),
                            'score' => !empty($new_kpi['score']) ? floatval($new_kpi['score']) : NULL,
                            'remark' => !empty($new_kpi['remark']) ? $new_kpi['remark'] : NULL,
                            'last_edited_by' => $manager_id,
                            'last_edited_at' => date('Y-m-d H:i:s')
                        ];
                        $this->db->insert('kpi_scores', $score_data);
                        
                        // Add to kpis array for validation
                        if (!is_array($kpis)) {
                            $kpis = [];
                        }
                        $kpis[$employee_kpi_id] = [
                            'weightage' => $new_kpi['weightage'],
                            'score' => $new_kpi['score']
                        ];
                    }
                }
            }
            
            // Validate total weightage (only count non-deleted KPIs)
            $total_weightage = 0;
            if (!empty($kpis)) {
                foreach ($kpis as $kpi_id => $kpi) {
                    // Skip if this KPI is marked for deletion
                    if (!empty($delete_kpis) && in_array($kpi_id, $delete_kpis)) {
                        continue;
                    }
                    $total_weightage += floatval($kpi['weightage']);
                }
            }
            
            if (abs($total_weightage - 100) > 0.01) {
                $this->session->set_flashdata('error', 'Total weightage must equal 100%. Current: ' . $total_weightage . '%');
                redirect('manager/edit_employee_kpis/' . $employee_id . '?period_id=' . $period_id);
                return;
            }
            
            // Update existing KPIs
            if (!empty($kpis)) {
                foreach ($kpis as $kpi_id => $kpi_data) {
                    // Skip if this KPI is marked for deletion
                    if (!empty($delete_kpis) && in_array($kpi_id, $delete_kpis)) {
                        continue;
                    }
                    
                    // Skip new KPIs (they were already inserted)
                    if (strpos($kpi_id, 'new_') === 0) {
                        continue;
                    }
                    
                    // Validate score (max 5)
                    if (isset($kpi_data['score']) && $kpi_data['score'] !== '' && floatval($kpi_data['score']) > 5) {
                        $this->session->set_flashdata('error', 'Score cannot be more than 5!');
                        redirect('manager/edit_employee_kpis/' . $employee_id . '?period_id=' . $period_id);
                        return;
                    }
                    
                    $update_data = [
                        'weightage' => floatval($kpi_data['weightage']),
                        'last_edited_by' => $manager_id,
                        'last_edited_at' => date('Y-m-d H:i:s')
                    ];
                    
                    // Update score if provided
                    if (isset($kpi_data['score']) && $kpi_data['score'] !== '') {
                        $update_data['score'] = floatval($kpi_data['score']);
                    }
                    
                    // Update remark if provided (per-employee remark for scoring)
                    if (isset($kpi_data['remark'])) {
                        $update_data['remark'] = $kpi_data['remark'];
                    }
                    
                    $this->db->where('employee_kpi_id', $kpi_id);
                    $this->db->update('kpi_scores', $update_data);
                }
            }
            
            // Only reset agreement status if employee has not agreed yet
            // If already agreed, we're just updating scores, so keep the agreement
            if (!$employee_has_agreed) {
                $this->db->where('employee_id', $employee_id);
                $this->db->where('review_period_id', $period_id);
                $this->db->update('employee_kpis', [
                    'employee_agreement_status' => 'PENDING',
                    'employee_agreement_date' => NULL,
                    'employee_agreement_notes' => NULL,
                    'manager_response_notes' => $manager_notes,
                    'manager_responded_at' => date('Y-m-d H:i:s'),
                    'manager_responded_by' => $user_id
                ]);
            } else {
                // Just update manager notes without resetting agreement
                $this->db->where('employee_id', $employee_id);
                $this->db->where('review_period_id', $period_id);
                $this->db->update('employee_kpis', [
                    'manager_response_notes' => $manager_notes,
                    'manager_responded_at' => date('Y-m-d H:i:s'),
                    'manager_responded_by' => $user_id
                ]);
            }
            
            // Send notification to employee only if agreement was reset
            if (!$employee_has_agreed) {
                $this->load->helper('email');
                $manager = $this->employee_model->get_by_id($manager_id);
                
                // Get period details
                $period = $this->db->get_where('review_periods', ['period_id' => $period_id])->row();
                
                // Send email notification
                if (!empty($employee->email)) {
                    notify_kpi_updated($employee->email, $employee, $manager, $period, $manager_notes);
                }
                
                $this->session->set_flashdata('success', 'KPIs updated successfully. Employee has been notified to review changes.');
            } else {
                $this->session->set_flashdata('success', 'Scores updated successfully.');
            }
            redirect('manager/view_employee/' . $employee_id);
            return;
        }
        
        // GET request - show edit form
        $data['employee'] = $employee;
        $data['kpis'] = $this->kpi_model->get_employee_kpis($employee_id, $period_id);
        $data['period_id'] = $period_id;
        $data['page_title'] = 'Edit Employee KPIs';
        
        // Check if employee has agreed (for conditional display)
        $data['employee_has_agreed'] = false;
        if (!empty($data['kpis'])) {
            $first_kpi = $data['kpis'][0];
            $data['employee_has_agreed'] = ($first_kpi->employee_agreement_status === 'AGREED');
        }
        
        $this->load->view('common/header', $data);
        $this->load->view('manager/edit_employee_kpis', $data);
        $this->load->view('common/footer');
    }

    /**
     * Reject KPI edit request
     */
    public function reject_kpi_edit_request() {
        $employee_id = $this->input->post('employee_id');
        $period_id = $this->input->post('period_id');
        $rejection_reason = $this->input->post('rejection_reason');
        
        if (!$this->verify_direct_report($employee_id)) {
            $this->session->set_flashdata('error', 'Access denied');
            redirect('manager/kpi_edit_requests');
            return;
        }
        
        // Update status back to PENDING
        $this->db->where('employee_id', $employee_id);
        $this->db->where('review_period_id', $period_id);
        $this->db->update('employee_kpis', [
            'employee_agreement_status' => 'PENDING',
            'employee_agreement_notes' => 'REJECTED: ' . $rejection_reason,
            'employee_agreement_date' => date('Y-m-d H:i:s')
        ]);
        
        // Send notification to employee
        $this->load->helper('email');
        $employee = $this->employee_model->get_by_id($employee_id);
        // TODO: Send email notification with rejection reason
        
        $this->session->set_flashdata('success', 'Edit request rejected. Employee has been notified.');
        redirect('manager/kpi_edit_requests');
    }

    /**
     * Check if user is logged in
     */
    private function check_login() {
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
    }

    /**
     * Check if user has manager role
     */
    private function check_manager_role() {
        $employee_id = $this->session->userdata('employee_id');
        
        if (!$this->employee_model->has_role($employee_id, 'MANAGER') && 
            !$this->employee_model->has_role($employee_id, 'SUPER_ADMIN')) {
            $this->session->set_flashdata('error', 'Access denied. Manager role required.');
            redirect('employee/dashboard');
        }
    }

    /**
     * Verify employee is a direct report
     */
    private function verify_direct_report($employee_id) {
        $manager_id = $this->session->userdata('employee_id');
        $direct_reports = $this->employee_model->get_direct_reports($manager_id);
        
        foreach ($direct_reports as $report) {
            if ($report->employee_id === $employee_id) {
                return TRUE;
            }
        }
        
        return FALSE;
    }
    
    // ==================== KPI EDIT CHAT SYSTEM ====================
    
    /**
     * Send a message in KPI edit chat (Manager responds to employee)
     */
    public function send_kpi_chat_message() {
        $this->load->library('form_validation');
        
        $this->form_validation->set_rules('employee_kpi_id', 'KPI ID', 'required');
        $this->form_validation->set_rules('employee_id', 'Employee ID', 'required');
        $this->form_validation->set_rules('message', 'Message', 'required');
        
        if ($this->form_validation->run() === FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
            return;
        }
        
        $employee_kpi_id = $this->input->post('employee_kpi_id');
        $employee_id = $this->input->post('employee_id');
        $message = $this->input->post('message');
        $manager_id = $this->session->userdata('employee_id');
        
        // Verify this employee reports to current manager
        if (!$this->verify_direct_report($employee_id)) {
            $this->session->set_flashdata('error', 'Access denied');
            redirect('manager/team');
            return;
        }
        
        // Determine the phase based on employee agreement status
        $kpi = $this->kpi_model->get_kpi_by_id($employee_kpi_id);
        $phase = 'SETUP'; // Default to setup
        
        if ($kpi && $kpi->employee_agreement_status === 'AGREED') {
            // If employee has agreed, we're in scoring phase
            $phase = 'SCORING';
        }
        
        // Add the manager's message with appropriate phase
        $result = $this->kpi_model->add_chat_message(
            $employee_kpi_id,
            $employee_id,
            $manager_id,
            'MANAGER',
            $message,
            $phase
        );
        
        if ($result) {
            // Don't change status - keep the conversation active
            $this->session->set_flashdata('success', 'Message sent to employee');
        } else {
            $this->session->set_flashdata('error', 'Failed to send message');
        }
        
        redirect($_SERVER['HTTP_REFERER']);
    }
    
    /**
     * Manager marks KPIs as agreed (employee clicked "I Agree")
     */
    public function mark_kpi_agreed($employee_kpi_id) {
        // Lock the KPIs when agreed
        $result = $this->kpi_model->set_employee_agreement($employee_kpi_id, 'AGREED', true);
        
        if ($result) {
            $this->session->set_flashdata('success', 'KPIs locked and marked as agreed');
        } else {
            $this->session->set_flashdata('error', 'Failed to update agreement status');
        }
        
        redirect($_SERVER['HTTP_REFERER']);
    }
}
