<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Welcome Controller
 * Landing page for the KPI Portal
 */
class Welcome extends CI_Controller {

    /**
     * Index Page for this controller.
     */
    public function index() {
        $data['page_title'] = 'Welcome';
        $this->load->view('common/header', $data);
        $this->load->view('welcome');
        $this->load->view('common/footer');
    }
}
