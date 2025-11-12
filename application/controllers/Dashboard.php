<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Dashboard Controller
 * Main dashboard that redirects based on role
 */
class Dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->check_login();
    }

    /**
     * Main dashboard index
     */
    public function index() {
        $role = $this->session->userdata('primary_role');
        
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
                redirect('login');
                break;
        }
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
