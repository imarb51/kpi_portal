<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin Controller
 * Handles Super Admin operations - full system management
 */
class Admin extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_login();
        $this->load->model('employee_model');
        $this->load->model('user_model');
        $this->load->model('kpi_model');
        $this->load->model('admin_model');
        $this->check_admin_role();
    }

    /**
     * Admin Dashboard
     */
    public function dashboard() {
        $data['total_employees'] = $this->admin_model->count_employees();
        $data['total_active_kpis'] = $this->admin_model->count_active_kpis();
        $data['total_departments'] = $this->admin_model->count_departments();
        $data['pending_requests'] = $this->admin_model->count_pending_requests();
        
        $data['active_period'] = $this->kpi_model->get_active_period();
       
        
        $data['page_title'] = 'Admin Dashboard';
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/dashboard', $data);
        $this->load->view('common/footer');
    }

    // ============================================
    // EMPLOYEE MANAGEMENT
    // ============================================

    /**
     * List all employees
     */
    public function employees() {
        $search = $this->input->get('search');
        $status = $this->input->get('status');
        
        if ($search) {
            $data['employees'] = $this->admin_model->search_employees($search, $status);
        } else {
            $data['employees'] = $this->admin_model->get_all_employees($status);
        }
        
        $data['page_title'] = 'Manage Employees';
        $data['search'] = $search;
        $data['status'] = $status;
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/employees/list', $data);
        $this->load->view('common/footer');
    }

    /**
     * Create new employee
     */
    public function create_employee() {
        if ($this->input->method() === 'post') {
            $this->load->library('form_validation');
            
            // Debug: Log what was posted BEFORE validation
            log_message('debug', '=== CREATE EMPLOYEE POST DATA ===');
            log_message('debug', 'POST data: ' . print_r($this->input->post(), true));
            log_message('debug', 'Reporting Manager IDs raw: ' . print_r($this->input->post('reporting_manager_ids'), true));
            log_message('debug', 'HR Spokesperson IDs raw: ' . print_r($this->input->post('hr_spokesperson_ids'), true));
            
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]');
            $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
            $this->form_validation->set_rules('first_name', 'First Name', 'required');
            $this->form_validation->set_rules('last_name', 'Last Name', 'required');
            $this->form_validation->set_rules('employee_code', 'Employee Code', 'required|is_unique[users.employee_code]');
            $this->form_validation->set_rules('designation', 'Designation', 'required');
            $this->form_validation->set_rules('department_id', 'Department', 'required');
            $this->form_validation->set_rules('reporting_manager_ids[]', 'Reporting Managers', 'required');
            $this->form_validation->set_rules('hr_spokesperson_ids[]', 'HR Spokespersons', 'required');
            $this->form_validation->set_rules('date_of_joining', 'Date of Joining', 'required');
            
            if ($this->form_validation->run() === TRUE) {
                log_message('debug', '✅ Validation PASSED');
                // Store password before hashing (for welcome email)
                $plain_password = $this->input->post('password');
                $send_welcome_email = $this->input->post('send_welcome_email');
                
                // Debug: Log the POST data
                $post_data = $this->input->post();
                log_message('debug', 'POST data received: ' . print_r($post_data, true));
                log_message('debug', 'Reporting Manager IDs: ' . print_r($this->input->post('reporting_manager_ids'), true));
                log_message('debug', 'HR Spokesperson IDs: ' . print_r($this->input->post('hr_spokesperson_ids'), true));
                
                $result = $this->admin_model->create_employee($post_data);
                
                if ($result) {
                    // Send welcome email if checkbox is checked
                    if ($send_welcome_email) {
                        $employee_data = $this->input->post();
                        $email_sent = $this->admin_model->send_welcome_email($employee_data, $plain_password);
                        
                        if ($email_sent) {
                            $this->session->set_flashdata('success', 'Employee created successfully and welcome email sent!');
                        } else {
                            $this->session->set_flashdata('success', 'Employee created successfully, but welcome email could not be sent. Please send credentials manually.');
                        }
                    } else {
                        $this->session->set_flashdata('success', 'Employee created successfully');
                    }
                    
                    redirect('admin/employees');
                } else {
                    log_message('error', '❌ Failed to create employee - model returned FALSE');
                    $this->session->set_flashdata('error', 'Failed to create employee');
                }
            } else {
                // Validation failed
                log_message('error', '❌ Validation FAILED');
                log_message('error', 'Validation errors: ' . validation_errors());
                $this->session->set_flashdata('error', 'Please fix the validation errors: ' . validation_errors());
            }
        }
        
        $data['departments'] = $this->admin_model->get_active_departments();
        $data['employees'] = $this->admin_model->get_all_employees('ACTIVE');
        $data['roles'] = $this->admin_model->get_all_roles();
        $data['page_title'] = 'Create Employee';
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/employees/create', $data);
        $this->load->view('common/footer');
    }

    /**
     * Edit employee
     */
    public function edit_employee($employee_id = null) {
        if (!$employee_id) {
            $this->session->set_flashdata('error', 'Employee ID is required');
            redirect('admin/employees');
            return;
        }
        
        $data['employee'] = $this->admin_model->get_employee_full_details($employee_id);
        
        if (!$data['employee']) {
            $this->session->set_flashdata('error', 'Employee not found');
            redirect('admin/employees');
        }
        
        if ($this->input->method() === 'post') {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('first_name', 'First Name', 'required');
            $this->form_validation->set_rules('last_name', 'Last Name', 'required');
            $this->form_validation->set_rules('designation', 'Designation', 'required');
            $this->form_validation->set_rules('department_id', 'Department', 'required');
            $this->form_validation->set_rules('reporting_manager_ids[]', 'Reporting Managers', 'required');
            $this->form_validation->set_rules('hr_spokesperson_ids[]', 'HR Spokespersons', 'required');
            $this->form_validation->set_rules('employee_status', 'Status', 'required');
            
            if ($this->form_validation->run() === TRUE) {
                // Debug: Log the POST data
                $post_data = $this->input->post();
                log_message('debug', 'Update Employee POST Data: ' . print_r($post_data, TRUE));
                log_message('debug', 'Reporting Manager IDs: ' . print_r($this->input->post('reporting_manager_ids'), TRUE));
                log_message('debug', 'HR Spokesperson IDs: ' . print_r($this->input->post('hr_spokesperson_ids'), TRUE));
                
                $result = $this->admin_model->update_employee($employee_id, $post_data);
                
                // Debug: Log the result
                log_message('debug', 'Update Employee Result: ' . ($result ? 'TRUE' : 'FALSE'));
                
                if ($result) {
                    $this->session->set_flashdata('success', 'Employee updated successfully');
                    redirect('admin/employees');
                    return; // Stop execution after redirect
                } else {
                    // Get database error details
                    $db_error = $this->db->error();
                    $error_msg = 'Failed to update employee. ';
                    
                    if (!empty($db_error['message'])) {
                        $error_msg .= 'Database Error: ' . $db_error['message'];
                        log_message('error', 'DB Error on employee update: ' . print_r($db_error, TRUE));
                    } else {
                        $error_msg .= 'Please check application logs for details.';
                    }
                    
                    $this->session->set_flashdata('error', $error_msg);
                    $this->session->set_flashdata('debug_info', json_encode([
                        'post_data' => $this->input->post(),
                        'db_error' => $db_error,
                        'employee_id' => $employee_id
                    ]));
                    // Don't redirect here - continue to show the form with error
                }
            } else {
                // Validation failed - show what fields failed
                $error_msg = 'Please fix the following errors:<br>';
                $error_msg .= validation_errors();
                $this->session->set_flashdata('error', $error_msg);
            }
        }
        
        $data['departments'] = $this->admin_model->get_active_departments();
        $data['roles'] = $this->admin_model->get_all_roles();
        $data['employee_roles'] = $this->admin_model->get_employee_roles($employee_id);
        $data['page_title'] = 'Edit Employee';
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/employees/edit', $data);
        $this->load->view('common/footer');
    }

    /**
     * View employee details
     */
    public function view_employee($employee_id) {
        $data['employee'] = $this->admin_model->get_employee_full_details($employee_id);
        
        if (!$data['employee']) {
            $this->session->set_flashdata('error', 'Employee not found');
            redirect('admin/employees');
        }
        
        $data['employee_roles'] = $this->admin_model->get_employee_roles($employee_id);
        $data['active_period'] = $this->kpi_model->get_active_period();
        
        if ($data['active_period']) {
            $data['kpis'] = $this->kpi_model->get_employee_kpis($employee_id, $data['active_period']->period_id);
            $data['total_weightage'] = $this->kpi_model->get_total_weightage($employee_id, $data['active_period']->period_id);
            $data['total_score'] = $this->kpi_model->calculate_total_score($employee_id, $data['active_period']->period_id);
            
            // Check for edit requests and chat messages
            $data['has_edit_request'] = false;
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
                        
                        $data['has_edit_request'] = true;
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
                        
                        $data['has_edit_request'] = true;
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
                            $data['has_edit_request'] = true;
                            $data['edit_request_employee_kpi_id'] = $kpi->employee_kpi_id;
                            $data['chat_messages'] = $chat_messages;
                            $data['show_both_chats'] = false;
                            $data['chat_is_locked'] = false;
                            break;
                        }
                    }
                }
            }
        }
        
        $data['page_title'] = 'Employee Details';
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/employees/view', $data);
        $this->load->view('common/footer');
    }

    /**
     * Deactivate employee
     */
    public function deactivate_employee($employee_id) {
        $result = $this->admin_model->update_employee_status($employee_id, 'INACTIVE');
        
        if ($result) {
            $this->session->set_flashdata('success', 'Employee deactivated successfully');
        } else {
            $this->session->set_flashdata('error', 'Failed to deactivate employee');
        }
        
        redirect('admin/employees');
    }

    /**
     * Delete employee permanently
     */
    public function delete_employee($employee_id) {
        if (!$employee_id) {
            $this->session->set_flashdata('error', 'Employee ID is required');
            redirect('admin/employees');
            return;
        }

        // Get employee details before deletion
        $employee = $this->admin_model->get_employee_full_details($employee_id);
        
        if (!$employee) {
            $this->session->set_flashdata('error', 'Employee not found');
            redirect('admin/employees');
            return;
        }

        // Delete employee (cascading will handle related records)
        $result = $this->admin_model->delete_employee($employee_id);
        
        if ($result) {
            $this->session->set_flashdata('success', 'Employee "' . $employee->first_name . ' ' . $employee->last_name . '" deleted successfully');
            log_message('info', "Employee deleted: {$employee_id} by admin: " . $this->session->userdata('employee_id'));
        } else {
            $this->session->set_flashdata('error', 'Failed to delete employee. Please try again.');
        }
        
        redirect('admin/employees');
    }

    /**
     * AJAX: Get managers by department
     */
    public function get_managers_by_department() {
        header('Content-Type: application/json');
        
        $department_id = $this->input->post('department_id');
        
        log_message('debug', 'get_managers_by_department called with department_id: ' . $department_id);
        
        if (!$department_id) {
            echo json_encode([]);
            return;
        }
        
        $managers = $this->admin_model->get_managers_by_department($department_id);
        
        log_message('debug', 'Found ' . count($managers) . ' managers for department ' . $department_id);
        
        echo json_encode($managers);
    }

    /**
     * AJAX: Get HR spokespersons
     */
    public function get_hr_spokespersons() {
        header('Content-Type: application/json');
        
        $hr_persons = $this->admin_model->get_hr_spokespersons();
        
        log_message('debug', 'Found ' . count($hr_persons) . ' HR spokespersons');
        
        echo json_encode($hr_persons);
    }

    /**
     * Activate employee
     */
    public function activate_employee($employee_id) {
        $result = $this->admin_model->update_employee_status($employee_id, 'ACTIVE');
        
        if ($result) {
            $this->session->set_flashdata('success', 'Employee activated successfully');
        } else {
            $this->session->set_flashdata('error', 'Failed to activate employee');
        }
        
        redirect('admin/employees');
    }

    // ============================================
    // ROLE MANAGEMENT
    // ============================================

    /**
     * Manage employee roles
     */
    public function manage_roles($employee_id = null) {
        if (!$employee_id) {
            $this->session->set_flashdata('error', 'Employee ID is required');
            redirect('admin/employees');
            return;
        }
        
        $data['employee'] = $this->employee_model->get_by_id($employee_id);
        
        if (!$data['employee']) {
            $this->session->set_flashdata('error', 'Employee not found');
            redirect('admin/employees');
        }
        
        if ($this->input->method() === 'post') {
            $roles = $this->input->post('roles');
            $primary_role = $this->input->post('primary_role');
            
            $result = $this->admin_model->update_employee_roles($employee_id, $roles, $primary_role);
            
            if ($result) {
                $this->session->set_flashdata('success', 'Roles updated successfully');
                redirect('admin/employees');
            } else {
                $this->session->set_flashdata('error', 'Failed to update roles');
            }
        }
        
        $data['all_roles'] = $this->admin_model->get_all_roles();
        $data['employee_roles'] = $this->admin_model->get_employee_roles($employee_id);
        $data['page_title'] = 'Manage Employee Roles';
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/employees/manage_roles', $data);
        $this->load->view('common/footer');
    }

    /**
     * Manage employee's team (assign team members to a manager)
     */
    public function manage_team($manager_id = null) {
        if (!$manager_id) {
            $this->session->set_flashdata('error', 'Manager ID is required');
            redirect('admin/employees');
            return;
        }
        
        $data['manager'] = $this->employee_model->get_by_id($manager_id);
        
        if (!$data['manager']) {
            $this->session->set_flashdata('error', 'Employee not found');
            redirect('admin/employees');
        }
        
        if ($this->input->method() === 'post') {
            $team_members = $this->input->post('team_members');
            
            $result = $this->admin_model->assign_team_members($manager_id, $team_members);
            
            if ($result) {
                $this->session->set_flashdata('success', 'Team members assigned successfully');
                redirect('admin/employees');
            } else {
                $this->session->set_flashdata('error', 'Failed to assign team members');
            }
        }
        
        // Get current team members
        $data['current_team'] = $this->admin_model->get_team_members($manager_id);
        
        // Get all employees except the manager (potential team members)
        $data['all_employees'] = $this->admin_model->get_all_employees('ACTIVE');
        
        $data['page_title'] = 'Manage Team';
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/employees/manage_team', $data);
        $this->load->view('common/footer');
    }

    // ============================================
    // DEPARTMENT MANAGEMENT
    // ============================================

    /**
     * List all departments
     */
    public function departments() {
        $data['departments'] = $this->admin_model->get_all_departments();
        $data['page_title'] = 'Manage Departments';
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/departments/list', $data);
        $this->load->view('common/footer');
    }

    /**
     * Create department
     */
    public function create_department() {
        if ($this->input->method() === 'post') {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('department_name', 'Department Name', 'required|is_unique[departments.department_name]');
            $this->form_validation->set_rules('department_code', 'Department Code', 'required|is_unique[departments.department_code]');
            
            if ($this->form_validation->run() === TRUE) {
                $result = $this->admin_model->create_department($this->input->post());
                
                if ($result) {
                    $this->session->set_flashdata('success', 'Department created successfully');
                    redirect('admin/departments');
                } else {
                    $this->session->set_flashdata('error', 'Failed to create department');
                }
            }
        }
        
        $data['departments'] = $this->admin_model->get_all_departments();
        $data['employees'] = $this->admin_model->get_all_employees('ACTIVE');
        $data['page_title'] = 'Create Department';
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/departments/create', $data);
        $this->load->view('common/footer');
    }

    /**
     * Edit department
     */
    public function edit_department($department_id) {
        $data['department'] = $this->admin_model->get_department($department_id);
        
        if (!$data['department']) {
            $this->session->set_flashdata('error', 'Department not found');
            redirect('admin/departments');
        }
        
        if ($this->input->method() === 'post') {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('department_name', 'Department Name', 'required');
            
            if ($this->form_validation->run() === TRUE) {
                $result = $this->admin_model->update_department($department_id, $this->input->post());
                
                if ($result) {
                    $this->session->set_flashdata('success', 'Department updated successfully');
                    redirect('admin/departments');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update department');
                }
            }
        }
        
        $data['departments'] = $this->admin_model->get_all_departments();
        $data['employees'] = $this->admin_model->get_all_employees('ACTIVE');
        $data['page_title'] = 'Edit Department';
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/departments/edit', $data);
        $this->load->view('common/footer');
    }

    /**
     * Delete department
     */
    public function delete_department($department_id) {
        $result = $this->admin_model->delete_department($department_id);
        
        if ($result) {
            $this->session->set_flashdata('success', 'Department deleted successfully');
        } else {
            $this->session->set_flashdata('error', 'Cannot delete department. It may have associated employees.');
        }
        
        redirect('admin/departments');
    }

    // ============================================
    // KPI CATEGORY MANAGEMENT
    // ============================================

    /**
     * List KPI categories
     */
    public function kpi_categories() {
        $data['categories'] = $this->admin_model->get_all_categories();
        $data['page_title'] = 'Manage KPI Categories';
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/kpi_categories/list', $data);
        $this->load->view('common/footer');
    }

    /**
     * Create KPI category
     */
    public function create_category() {
        if ($this->input->method() === 'post') {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('category_name', 'Category Name', 'required|is_unique[kpi_categories.category_name]');
            
            if ($this->form_validation->run() === TRUE) {
                $result = $this->admin_model->create_category($this->input->post());
                
                if ($result) {
                    $this->session->set_flashdata('success', 'Category created successfully');
                    redirect('admin/kpi_categories');
                } else {
                    $this->session->set_flashdata('error', 'Failed to create category');
                }
            }
        }
        
        $data['page_title'] = 'Create KPI Category';
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/kpi_categories/create', $data);
        $this->load->view('common/footer');
    }

    /**
     * Edit KPI category
     */
    public function edit_category($category_id) {
        $data['category'] = $this->admin_model->get_category($category_id);
        
        if (!$data['category']) {
            $this->session->set_flashdata('error', 'Category not found');
            redirect('admin/kpi_categories');
        }
        
        if ($this->input->method() === 'post') {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('category_name', 'Category Name', 'required');
            
            if ($this->form_validation->run() === TRUE) {
                $result = $this->admin_model->update_category($category_id, $this->input->post());
                
                if ($result) {
                    $this->session->set_flashdata('success', 'Category updated successfully');
                    redirect('admin/kpi_categories');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update category');
                }
            }
        }
        
        $data['page_title'] = 'Edit KPI Category';
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/kpi_categories/edit', $data);
        $this->load->view('common/footer');
    }

    // ============================================
    // KPI TEMPLATE MANAGEMENT
    // ============================================

    /**
     * List KPI templates
     */
    public function kpi_templates() {
        $category = $this->input->get('category');
        
        // Get grouped templates (by template_name)
        if ($category) {
            $data['grouped_templates'] = $this->admin_model->get_templates_grouped($category);
        } else {
            $data['grouped_templates'] = $this->admin_model->get_templates_grouped();
        }
        
        $data['categories'] = $this->admin_model->get_all_categories();
        $data['selected_category'] = $category;
        $data['page_title'] = 'Manage KPI Templates';
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/kpi_templates/list_new', $data);
        $this->load->view('common/footer');
    }

    /**
     * AJAX endpoint to get KPIs by template name
     */
    public function get_template_kpis() {
        $template_name = $this->input->post('template_name');
        $kpis = $this->admin_model->get_kpis_by_template_name($template_name);
        
        echo json_encode(['kpis' => $kpis]);
    }

    /**
     * Edit template group (all KPIs with same template_name)
     */
    public function edit_template_group($template_name = '') {
        // Check if template_name is passed via POST (from the list page form or edit form submission)
        if (empty($template_name) && $this->input->post('template_name_param')) {
            // From list page
            $template_name = $this->input->post('template_name_param');
        } elseif (empty($template_name) && $this->input->post('original_template_name')) {
            // From edit form submission
            $template_name = $this->input->post('original_template_name');
        } else {
            // Decode URL-encoded template name (fallback)
            $template_name = urldecode($template_name);
        }
        
        if (empty($template_name)) {
            $this->session->set_flashdata('error', 'Invalid template name');
            redirect('admin/kpi_templates');
            return;
        }

        // Handle POST submission for editing (check if kpis array exists)
        if ($this->input->method() === 'post' && $this->input->post('kpis')) {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('template_name', 'Template Name', 'required');
            
            if ($this->form_validation->run() === TRUE) {
                $new_template_name = $this->input->post('template_name');
                $department_id = $this->input->post('department_id') ?: NULL; // Get department, NULL if not set
                $kpis = $this->input->post('kpis'); // Array of KPIs
                
                // Validate that each KPI has a category
                $all_have_category = true;
                foreach ($kpis as $kpi) {
                    if (empty($kpi['category_id'])) {
                        $all_have_category = false;
                        break;
                    }
                }
                
                if (!$all_have_category) {
                    $this->session->set_flashdata('error', 'Each KPI must have a category selected');
                    redirect('admin/edit_template_group');
                    return;
                }
                
                // Update template without weightage validation (Manager will assign weightage later)
                $result = $this->admin_model->update_template_group($template_name, $new_template_name, $department_id, $kpis);
                
                if ($result) {
                    $this->session->set_flashdata('success', 'Template updated successfully');
                    redirect('admin/kpi_templates');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update template');
                    redirect('admin/edit_template_group');
                }
            } else {
                $this->session->set_flashdata('error', validation_errors());
                redirect('admin/edit_template_group');
            }
            return;
        }

        // GET request - load template data
        $data['kpis'] = $this->admin_model->get_kpis_by_template_name($template_name);
        
        if (empty($data['kpis'])) {
            $this->session->set_flashdata('error', 'Template not found');
            redirect('admin/kpi_templates');
            return;
        }
        
        $data['template_name'] = $template_name;
        $data['category_id'] = $data['kpis'][0]->category_id;
        $data['current_department_id'] = $data['kpis'][0]->department_id ?? NULL; // Get current department
        $data['categories'] = $this->admin_model->get_all_categories();
        $data['departments'] = $this->admin_model->get_all_departments();
        $data['page_title'] = 'Edit Template: ' . $template_name;
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/kpi_templates/edit_group', $data);
        $this->load->view('common/footer');
    }

    /**
     * Create KPI template
     */
    public function create_template() {
        if ($this->input->method() === 'post') {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('template_name', 'Template Name', 'required');
            
            if ($this->form_validation->run() === TRUE) {
                $employee_id = $this->session->userdata('employee_id');
                
                // Get template data
                $template_data = [
                    'template_name' => $this->input->post('template_name'),
                    'department_id' => $this->input->post('department_id') ?: NULL, // NULL if not set
                    'kpis' => $this->input->post('kpis') // Array of KPIs with category_id per KPI
                ];
                
                // Validate that each KPI has a category
                $all_have_category = true;
                foreach ($template_data['kpis'] as $kpi) {
                    if (empty($kpi['category_id'])) {
                        $all_have_category = false;
                        break;
                    }
                }
                
                if (!$all_have_category) {
                    $this->session->set_flashdata('error', 'Each KPI must have a category selected');
                    redirect('admin/create_template_form');
                    return;
                }
                
                // Create template without weightage validation (Manager will assign weightage later)
                $result = $this->admin_model->create_template_batch($template_data, $employee_id);
                
                if ($result) {
                    $this->session->set_flashdata('success', 'KPI template created successfully with ' . count($template_data['kpis']) . ' KPIs');
                    redirect('admin/kpi_templates');
                } else {
                    $this->session->set_flashdata('error', 'Failed to create template');
                }
            }
        }
        
        $data['categories'] = $this->admin_model->get_all_categories();
        $data['departments'] = $this->admin_model->get_all_departments();
        $data['page_title'] = 'Create KPI Template';
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/kpi_templates/create_new', $data);
        $this->load->view('common/footer');
    }

    /**
     * Import KPI template from CSV
     */
    public function import_template() {
        if ($this->input->method() === 'post') {
            // Configure upload
            $config['upload_path'] = './uploads/templates/';
            $config['allowed_types'] = 'csv';
            $config['max_size'] = 5120; // 5MB
            $config['file_name'] = 'kpi_template_' . time();
            
            // Create upload directory if it doesn't exist
            if (!is_dir($config['upload_path'])) {
                mkdir($config['upload_path'], 0755, true);
            }
            
            $this->load->library('upload', $config);
            
            if (!$this->upload->do_upload('excel_file')) {
                $error = $this->upload->display_errors('', '');
                $this->session->set_flashdata('error', 'Upload failed: ' . $error);
                redirect('admin/create_template');
                return;
            }
            
            $upload_data = $this->upload->data();
            $file_path = $upload_data['full_path'];
            
            try {
                // Parse the CSV data using native PHP
                $template_data = $this->parse_kpi_csv($file_path);
                
                if ($template_data && count($template_data['kpis']) > 0) {
                    $employee_id = $this->session->userdata('employee_id');
                    $result = $this->admin_model->create_template_from_import($template_data, $employee_id);
                    
                    if ($result) {
                        $this->session->set_flashdata('success', 'KPI template imported successfully! Created ' . count($template_data['kpis']) . ' KPIs.');
                        // Delete uploaded file after processing
                        @unlink($file_path);
                        redirect('admin/kpi_templates');
                    } else {
                        $this->session->set_flashdata('error', 'Failed to save imported template');
                    }
                } else {
                    $this->session->set_flashdata('error', 'Invalid CSV format or no KPIs found. Please check the file structure.');
                }
                
                // Delete uploaded file
                @unlink($file_path);
                
            } catch (Exception $e) {
                @unlink($file_path);
                log_message('error', 'CSV Import Error: ' . $e->getMessage());
                $this->session->set_flashdata('error', 'Error processing CSV file: ' . $e->getMessage());
            }
            
            redirect('admin/create_template');
        }
        
        redirect('admin/create_template');
    }

    /**
     * Parse CSV file and extract KPI template data
     * Uses native PHP functions - no external libraries needed
     */
    private function parse_kpi_csv($file_path) {
        $template_data = [
            'template_name' => 'Imported Template ' . date('Y-m-d H:i:s'),
            'current_category' => 'Work KPIs',
            'kpis' => []
        ];
        
        // Open CSV file
        if (($handle = fopen($file_path, 'r')) === FALSE) {
            return false;
        }
        
        $row_number = 0;
        $header_found = false;
        
        // Read CSV line by line
        while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $row_number++;
            
            // Skip completely empty rows
            if (empty(array_filter($row))) {
                continue;
            }
            
            // Get first two columns (Weightage and Description)
            $weightage = isset($row[0]) ? trim($row[0]) : '';
            $description = isset($row[1]) ? trim($row[1]) : '';
            
            // Skip if description is empty
            if (empty($description)) {
                continue;
            }
            
            // Check for header row
            if (!$header_found && (stripos($weightage, 'weightage') !== false || stripos($description, 'kpi') !== false || stripos($description, 'description') !== false)) {
                $header_found = true;
                continue;
            }
            
            // Skip "Total" rows
            if (stripos($description, 'total') !== false) {
                continue;
            }
            
            // Clean weightage value
            $weightage_clean = str_replace(['%', ' '], '', $weightage);
            
            // Check if this is a category header (no weightage or "100%")
            if (empty($weightage_clean) || $weightage_clean === '100') {
                // This is a section header - update current category
                if (strlen($description) < 100 && !is_numeric($description)) {
                    $template_data['current_category'] = $description;
                }
                continue;
            }
            
            // Parse weightage as number
            if (is_numeric($weightage_clean)) {
                $weightage_value = floatval($weightage_clean);
                
                // Only add KPIs with non-zero weightage
                if ($weightage_value > 0 && $weightage_value <= 100) {
                    $template_data['kpis'][] = [
                        'kpi_name' => $description,
                        'weightage' => $weightage_value,
                        'category' => $template_data['current_category']
                    ];
                }
            }
        }
        
        fclose($handle);
        
        log_message('debug', 'CSV Import: Found ' . count($template_data['kpis']) . ' KPIs');
        
        return count($template_data['kpis']) > 0 ? $template_data : false;
    }

    /**
     * Edit KPI template
     */
    public function edit_template($template_id) {
        $data['template'] = $this->admin_model->get_template($template_id);
        
        if (!$data['template']) {
            $this->session->set_flashdata('error', 'Template not found');
            redirect('admin/kpi_templates');
        }
        
        if ($this->input->method() === 'post') {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('kpi_name', 'KPI Name', 'required');
            $this->form_validation->set_rules('category_id', 'Category', 'required');
            
            if ($this->form_validation->run() === TRUE) {
                $result = $this->admin_model->update_template($template_id, $this->input->post());
                
                if ($result) {
                    $this->session->set_flashdata('success', 'Template updated successfully');
                    redirect('admin/kpi_templates');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update template');
                }
            }
        }
        
        $data['categories'] = $this->admin_model->get_all_categories();
        $data['page_title'] = 'Edit KPI Template';
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/kpi_templates/edit', $data);
        $this->load->view('common/footer');
    }

    /**
     * Delete KPI template
     */
    public function delete_template($template_id) {
        $result = $this->admin_model->delete_template($template_id);
        
        if ($result) {
            $this->session->set_flashdata('success', 'Template deleted successfully');
        } else {
            $this->session->set_flashdata('error', 'Cannot delete template. It may be assigned to employees.');
        }
        
        redirect('admin/kpi_templates');
    }

    /**
     * Delete all templates in a template group by template_name
     */
    public function delete_template_group($template_name) {
        $template_name = urldecode($template_name);
        $result = $this->admin_model->delete_template_group($template_name);
        
        if ($result) {
            $this->session->set_flashdata('success', 'Template group "' . $template_name . '" deleted successfully');
        } else {
            $this->session->set_flashdata('error', 'Cannot delete template group. Some templates may be assigned to employees.');
        }
        
        redirect('admin/kpi_templates');
    }

    // ============================================
    // REVIEW PERIOD MANAGEMENT
    // ============================================

    /**
     * List review periods
     */
    public function review_periods() {
        $data['periods'] = $this->admin_model->get_all_periods();
        $data['page_title'] = 'Manage Review Periods';
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/review_periods/list', $data);
        $this->load->view('common/footer');
    }

    /**
     * Create review period
     */
    public function create_period() {
        if ($this->input->method() === 'post') {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('period_name', 'Period Name', 'required');
            $this->form_validation->set_rules('period_type', 'Period Type', 'required');
            $this->form_validation->set_rules('cycle_year', 'Cycle Year', 'required');
            $this->form_validation->set_rules('start_date', 'Start Date', 'required');
            $this->form_validation->set_rules('end_date', 'End Date', 'required');
            
            if ($this->form_validation->run() === TRUE) {
                $result = $this->admin_model->create_period($this->input->post());
                
                if ($result) {
                    $this->session->set_flashdata('success', 'Review period created successfully');
                    redirect('admin/review_periods');
                } else {
                    $this->session->set_flashdata('error', 'Failed to create review period');
                }
            }
        }
        
        $data['page_title'] = 'Create Review Period';
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/review_periods/create', $data);
        $this->load->view('common/footer');
    }

    /**
     * Edit review period
     */
    public function edit_period($period_id) {
        $data['period'] = $this->admin_model->get_period($period_id);
        
        if (!$data['period']) {
            $this->session->set_flashdata('error', 'Period not found');
            redirect('admin/review_periods');
        }
        
        if ($this->input->method() === 'post') {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('period_name', 'Period Name', 'required');
            $this->form_validation->set_rules('start_date', 'Start Date', 'required');
            $this->form_validation->set_rules('end_date', 'End Date', 'required');
            
            if ($this->form_validation->run() === TRUE) {
                $result = $this->admin_model->update_period($period_id, $this->input->post());
                
                if ($result) {
                    $this->session->set_flashdata('success', 'Review period updated successfully');
                    redirect('admin/review_periods');
                } else {
                    $this->session->set_flashdata('error', 'Failed to update review period');
                }
            }
        }
        
        $data['page_title'] = 'Edit Review Period';
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/review_periods/edit', $data);
        $this->load->view('common/footer');
    }

    /**
     * Activate review period (make it active)
     */
    public function activate_period($period_id) {
        $result = $this->admin_model->activate_period($period_id);
        
        if ($result) {
            $this->session->set_flashdata('success', 'Review period activated successfully');
        } else {
            $this->session->set_flashdata('error', 'Failed to activate review period');
        }
        
        redirect('admin/review_periods');
    }

    /**
     * Close review period
     */
    public function close_period($period_id) {
        $result = $this->admin_model->close_period($period_id);
        
        if ($result) {
            $this->session->set_flashdata('success', 'Review period closed successfully');
        } else {
            $this->session->set_flashdata('error', 'Failed to close review period');
        }
        
        redirect('admin/review_periods');
    }

    // ============================================
    // REPORTS & ANALYTICS
    // ============================================

    /**
     * Organization-wide reports
     */
    public function reports() {
        $period_id = $this->input->get('period_id');
        
        if (!$period_id) {
            $active_period = $this->kpi_model->get_active_period();
            $period_id = $active_period ? $active_period->period_id : NULL;
        }
        
        $data['selected_period_id'] = $period_id;
        $data['all_periods'] = $this->kpi_model->get_all_periods();
        
        if ($period_id) {
            $data['department_performance'] = $this->admin_model->get_department_performance($period_id);
            $data['top_performers'] = $this->admin_model->get_top_performers($period_id, 10);
            $data['low_performers'] = $this->admin_model->get_low_performers($period_id, 10);
        }
        
        $data['page_title'] = 'Reports & Analytics';
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/reports/dashboard', $data);
        $this->load->view('common/footer');
    }

    // ============================================
    // AUDIT LOGS
    // ============================================

    /**
     * View audit logs
     */
    public function audit_logs() {
        $page = $this->input->get('page') ? $this->input->get('page') : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        $data['logs'] = $this->admin_model->get_audit_logs($limit, $offset);
        $data['total_logs'] = $this->admin_model->count_audit_logs();
        $data['current_page'] = $page;
        $data['total_pages'] = ceil($data['total_logs'] / $limit);
        
        $data['page_title'] = 'Audit Logs';
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/audit_logs', $data);
        $this->load->view('common/footer');
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Check if user is logged in
     */
    private function check_login() {
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }
    }

    /**
     * Check if user has admin role
     */
    private function check_admin_role() {
        $employee_id = $this->session->userdata('employee_id');
        
        if (!$this->employee_model->has_role($employee_id, 'SUPER_ADMIN')) {
            $this->session->set_flashdata('error', 'Access denied. Admin role required.');
            redirect('dashboard');
        }
    }

    // ============================================
    // KPI FALLBACK MANAGEMENT
    // ============================================

    /**
     * KPI Fallback Management Page
     */
    public function kpi_fallback() {
        $this->load->model('kpi_fallback_model');
        
        // Get all employees for dropdown
        $data['employees'] = $this->admin_model->get_all_employees('ACTIVE');
        
        // Get recent fallback logs (will be empty until table is created)
        $data['fallback_logs'] = [];
        
        $data['page_title'] = 'KPI Assignment Fallback System';
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/kpi_fallback', $data);
        $this->load->view('common/footer');
    }

    /**
     * Check who would assign KPIs for an employee (with fallback)
     */
    public function check_kpi_fallback() {
        $this->load->model('kpi_fallback_model');
        
        $employee_id = $this->input->post('employee_id');
        
        if (!$employee_id) {
            $this->session->set_flashdata('error', 'Please select an employee');
            redirect('admin/kpi_fallback');
            return;
        }
        
        // Get all employees for dropdown
        $data['employees'] = $this->admin_model->get_all_employees('ACTIVE');
        $data['selected_employee'] = $employee_id;
        
        // Get fallback assignor
        $data['fallback_result'] = $this->kpi_fallback_model->get_kpi_assignor($employee_id);
        
        // Get recent fallback logs
        $data['fallback_logs'] = [];
        
        $data['page_title'] = 'KPI Assignment Fallback System';
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/kpi_fallback', $data);
        $this->load->view('common/footer');
    }

    /**
     * Change Password
     */
    public function change_password() {
        if ($this->input->method() === 'post') {
            $this->load->library('form_validation');
            
            $this->form_validation->set_rules('current_password', 'Current Password', 'required');
            $this->form_validation->set_rules('new_password', 'New Password', 'required|min_length[6]');
            $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[new_password]');
            
            if ($this->form_validation->run() === TRUE) {
                $user_id = $this->session->userdata('user_id');
                $current_password = $this->input->post('current_password');
                $new_password = $this->input->post('new_password');
                
                // Verify current password
                $user = $this->user_model->get_by_id($user_id);
                if (!$user || !password_verify($current_password, $user->password_hash)) {
                    $this->session->set_flashdata('error', 'Current password is incorrect');
                    redirect('admin/change_password');
                    return;
                }
                
                // Update password
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                if ($this->user_model->update_password($user_id, $new_hash)) {
                    $this->session->set_flashdata('success', 'Password changed successfully');
                    redirect('admin/dashboard');
                } else {
                    $this->session->set_flashdata('error', 'Failed to change password');
                    redirect('admin/change_password');
                }
            }
        }
        
        $data['page_title'] = 'Change Password';
        
        $this->load->view('common/header', $data);
        $this->load->view('common/change_password', $data);
        $this->load->view('common/footer');
    }

    // ==================== KPI EDIT CHAT SYSTEM ====================
    
    /**
     * Send a message in KPI edit chat (Admin responds to employee)
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
        $admin_id = $this->session->userdata('employee_id');
        
        // Determine the phase based on employee agreement status
        $kpi = $this->kpi_model->get_kpi_by_id($employee_kpi_id);
        $phase = 'SETUP'; // Default to setup
        
        if ($kpi && $kpi->employee_agreement_status === 'AGREED') {
            // If employee has agreed, we're in scoring phase
            $phase = 'SCORING';
        }
        
        // Add the admin's message with appropriate phase
        $result = $this->kpi_model->add_chat_message(
            $employee_kpi_id,
            $employee_id,
            $admin_id,
            'ADMIN',
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
     * Admin marks KPIs as agreed (employee clicked "I Agree")
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
    
    /**
     * Edit KPI for an employee (Admin has full access like Manager)
     */
    public function edit_employee_kpi($employee_id) {
        // Load the same edit functionality as manager
        // This gives admin full KPI editing capability
        
        $data['employee'] = $this->employee_model->get_by_id($employee_id);
        if (!$data['employee']) {
            $this->session->set_flashdata('error', 'Employee not found');
            redirect('admin/employees');
        }
        
        $data['active_period'] = $this->kpi_model->get_active_period();
        if (!$data['active_period']) {
            $this->session->set_flashdata('error', 'No active review period');
            redirect('admin/view_employee/' . $employee_id);
        }
        
        if ($this->input->method() === 'post') {
            // Handle KPI updates
            $kpi_ids = $this->input->post('kpi_id');
            $weightages = $this->input->post('weightage');
            $delete_kpis = $this->input->post('delete_kpi');
            
            // Update existing KPIs
            if ($kpi_ids) {
                foreach ($kpi_ids as $index => $kpi_id) {
                    if (!isset($delete_kpis[$index])) {
                        $this->kpi_model->update_kpi_weightage($kpi_id, $weightages[$index]);
                    } else {
                        $this->kpi_model->delete_employee_kpi($kpi_id);
                    }
                }
            }
            
            // Add new KPIs if requested
            $new_kpi_templates = $this->input->post('new_kpi_template');
            $new_kpi_weightages = $this->input->post('new_kpi_weightage');
            
            if ($new_kpi_templates) {
                foreach ($new_kpi_templates as $index => $template_id) {
                    if (!empty($template_id) && !empty($new_kpi_weightages[$index])) {
                        $this->kpi_model->assign_kpi(
                            $employee_id,
                            $template_id,
                            $data['active_period']->period_id,
                            $this->session->userdata('employee_id'),
                            $new_kpi_weightages[$index]
                        );
                    }
                }
            }
            
            $this->session->set_flashdata('success', 'KPIs updated successfully');
            redirect('admin/view_employee/' . $employee_id);
        }
        
        // Load KPIs and templates for editing
        $data['kpis'] = $this->kpi_model->get_employee_kpis($employee_id, $data['active_period']->period_id);
        $data['available_templates'] = $this->kpi_model->get_templates_by_category();
        $data['total_weightage'] = $this->kpi_model->get_total_weightage($employee_id, $data['active_period']->period_id);
        $data['page_title'] = 'Edit KPIs - ' . $data['employee']->first_name . ' ' . $data['employee']->last_name;
        
        $this->load->view('common/header', $data);
        $this->load->view('admin/employees/edit_kpis', $data);
        $this->load->view('common/footer');
    }

    /**
     * Assign KPI template to employee (Admin can do this for any employee)
     */
    public function assign_kpi($employee_id) {
        $data['employee'] = $this->admin_model->get_employee_full_details($employee_id);
        
        if (!$data['employee']) {
            $this->session->set_flashdata('error', 'Employee not found');
            redirect('admin/employees');
        }
        
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
            $admin_id = $this->session->userdata('employee_id');
            
            // Validate total weightage
            $total_weightage = 0;
            foreach ($kpis as $kpi) {
                $total_weightage += floatval($kpi['weightage']);
            }
            
            if (abs($total_weightage - 100) > 0.01) {
                $this->session->set_flashdata('error', 'Total weightage must equal 100%. Current: ' . $total_weightage . '%');
                redirect('admin/assign_kpi/' . $employee_id);
                return;
            }
            
            // Assign all KPIs from template (admin acts as assigner)
            $result = $this->kpi_model->assign_template_kpis($employee_id, $template_name, $period_id, $admin_id, $kpis);
            
            if ($result) {
                $this->session->set_flashdata('success', count($kpis) . ' KPIs assigned successfully from template: ' . $template_name);
            } else {
                $this->session->set_flashdata('error', 'Failed to assign KPIs');
            }
            
            redirect('admin/view_employee/' . $employee_id);
            return;
        }
        
        // GET request - show form
        $data['active_period'] = $this->kpi_model->get_active_period();
        
        // Get employee's department to auto-fetch relevant templates
        $department_id = $data['employee']->department_id ?? NULL;
        
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
     * Edit employee KPIs (Admin has access to all employees)
     */
    public function edit_employee_kpis($employee_id) {
        $period_id = $this->input->get('period_id');
        
        // Get employee data (needed for both GET and POST)
        $employee = $this->employee_model->get_by_id($employee_id);
        
        if (!$employee) {
            $this->session->set_flashdata('error', 'Employee not found');
            redirect('admin/employees');
        }
        
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
            $admin_id = $this->session->userdata('employee_id');
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
                            'assigned_by' => $admin_id,
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
                            'last_edited_by' => $admin_id,
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
                redirect('admin/edit_employee_kpis/' . $employee_id . '?period_id=' . $period_id);
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
                        redirect('admin/edit_employee_kpis/' . $employee_id . '?period_id=' . $period_id);
                        return;
                    }
                    
                    $update_data = [
                        'weightage' => floatval($kpi_data['weightage']),
                        'last_edited_by' => $admin_id,
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
            if (!$employee_has_agreed) {
                $this->db->where('employee_id', $employee_id);
                $this->db->where('review_period_id', $period_id);
                $this->db->update('employee_kpis', [
                    'employee_agreement_status' => 'PENDING'
                ]);
            }
            
            // Send notification message if notes provided
            if (!empty($manager_notes)) {
                $any_employee_kpi_id = !empty($kpis) ? array_key_first($kpis) : NULL;
                if ($any_employee_kpi_id) {
                    $this->kpi_model->add_kpi_chat_message($any_employee_kpi_id, $admin_id, 'ADMIN', $manager_notes);
                }
            }
            
            $period = $this->db->get_where('review_periods', ['period_id' => $period_id])->row();
            $period_name = $period ? $period->period_name : 'current period';
            
            log_message('info', 'Admin ' . $admin_id . ' updated KPIs for employee ' . $employee_id . ' in period ' . $period_id);
            
            if (!$employee_has_agreed) {
                $this->session->set_flashdata('success', 'KPIs updated successfully. Employee has been notified to review changes.');
            } else {
                $this->session->set_flashdata('success', 'Scores updated successfully.');
            }
            redirect('admin/view_employee/' . $employee_id);
            return;
        }
        
        // GET request - show form
        $data['employee'] = $employee;
        $data['period_id'] = $period_id;
        
        // Get KPIs for this employee and period
        $data['kpis'] = $this->kpi_model->get_employee_kpis($employee_id, $period_id);
        
        // Check if employee has agreed (for conditional display)
        $data['employee_has_agreed'] = false;
        if (!empty($data['kpis'])) {
            $first_kpi = $data['kpis'][0];
            $data['employee_has_agreed'] = ($first_kpi->employee_agreement_status === 'AGREED');
        }
        
        $data['page_title'] = 'Edit KPIs - ' . $employee->first_name . ' ' . $employee->last_name;
        
        $this->load->view('common/header', $data);
        $this->load->view('manager/edit_employee_kpis', $data);
        $this->load->view('common/footer');
    }
}
