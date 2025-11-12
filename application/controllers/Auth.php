<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Auth Controller
 * Handles user authentication (login/logout)
 */
class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('user_model');
        $this->load->model('employee_model');
        $this->load->library('session');
    }

    /**
     * Login page
     */
    public function login() {
        // If already logged in, redirect to dashboard
        if ($this->session->userdata('logged_in')) {
            redirect('dashboard');
        }
        
        $data['page_title'] = 'Login';
        $this->load->view('common/header', $data);
        $this->load->view('auth/login');
        $this->load->view('common/footer');
    }

    /**
     * Process login
     */
    public function authenticate() {
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        
        // Validate inputs
        if (empty($email) || empty($password)) {
            $this->session->set_flashdata('error', 'Email and password are required');
            redirect('login');
        }
        
        // Validate user credentials
        $user = $this->user_model->validate_login($email, $password);
        
        if ($user) {
            // Get employee details
            $employee = $this->employee_model->get_by_user_id($user->user_id);
            
            if ($employee) {
                // Get employee roles
                $roles = $this->employee_model->get_roles($employee->employee_id);
                $primary_role = $this->employee_model->get_primary_role($employee->employee_id);
                
                // Set session data
                $session_data = [
                    'logged_in' => TRUE,
                    'user_id' => $user->user_id,
                    'employee_id' => $employee->employee_id,
                    'employee_code' => $employee->employee_code,
                    'full_name' => trim($employee->first_name . ' ' . $employee->last_name),
                    'email' => $user->email,
                    'designation' => $employee->designation,
                    'primary_role' => $primary_role,
                    'roles' => $roles
                ];
                
                $this->session->set_userdata($session_data);
                
                // Redirect based on role
                $this->redirect_by_role($primary_role);
            } else {
                $this->session->set_flashdata('error', 'Employee record not found');
                redirect('login');
            }
        } else {
            $this->session->set_flashdata('error', 'Invalid email or password');
            redirect('login');
        }
    }

    /**
     * Logout
     */
    public function logout() {
        $this->session->unset_userdata('logged_in');
        $this->session->unset_userdata('user_id');
        $this->session->unset_userdata('employee_id');
        $this->session->unset_userdata('employee_code');
        $this->session->unset_userdata('full_name');
        $this->session->unset_userdata('email');
        $this->session->unset_userdata('designation');
        $this->session->unset_userdata('primary_role');
        $this->session->unset_userdata('roles');
        
        $this->session->set_flashdata('success', 'You have been logged out successfully');
        redirect('login');
    }

    /**
     * Redirect user based on their primary role
     */
    private function redirect_by_role($role) {
        switch ($role) {
            case 'SUPER_ADMIN':
                redirect('admin/dashboard');
                break;
            case 'MANAGER':
                redirect('manager/dashboard');
                break;
            case 'EMPLOYEE':
                redirect('employee/dashboard');
                break;
            default:
                redirect('dashboard');
                break;
        }
    }
}
