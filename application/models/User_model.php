<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * User Model
 * Handles user authentication and user-related operations
 */
class User_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get user by email
     */
    public function get_by_email($email) {
        return $this->db->where('email', $email)
                        ->where('is_active', 1)
                        ->where('deleted_at IS NULL')
                        ->get('users')
                        ->row();
    }

    /**
     * Get user by ID
     */
    public function get_by_id($user_id) {
        return $this->db->where('user_id', $user_id)
                        ->where('is_active', 1)
                        ->where('deleted_at IS NULL')
                        ->get('users')
                        ->row();
    }

    /**
     * Validate user login
     */
    public function validate_login($email, $password) {
        $user = $this->get_by_email($email);
        
        if ($user && password_verify($password, $user->password_hash)) {
            // Update last login
            $this->update_last_login($user->user_id);
            return $user;
        }
        
        return FALSE;
    }

    /**
     * Update last login timestamp
     */
    public function update_last_login($user_id) {
        $this->db->where('user_id', $user_id)
                 ->update('users', ['last_login' => date('Y-m-d H:i:s')]);
    }

    /**
     * Create new user
     */
    public function create($data) {
        $user_data = [
            'user_id' => $this->generate_uuid(),
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'is_active' => isset($data['is_active']) ? $data['is_active'] : 1,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('users', $user_data) ? $user_data['user_id'] : FALSE;
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

    /**
     * Update user password
     */
    public function update_password($user_id, $password_hash) {
        $this->db->where('user_id', $user_id);
        return $this->db->update('users', [
            'password_hash' => $password_hash,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
