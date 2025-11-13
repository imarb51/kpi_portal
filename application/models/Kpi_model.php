<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * KPI Model
 * Handles KPI assignments, scoring, and reviews
 */
class Kpi_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get active review period (H1 or H2)
     */
    public function get_active_period() {
        return $this->db->where('status', 'ACTIVE')
                        ->where('is_active', 1)
                        ->get('review_periods')
                        ->row();
    }

    /**
     * Get all review periods
     */
    public function get_all_periods() {
        return $this->db->order_by('start_date', 'DESC')
                        ->get('review_periods')
                        ->result();
    }

    /**
     * Get KPI templates by category
     */
    public function get_templates_by_category($category_name = NULL) {
        $this->db->select('kt.*, kc.category_name')
                 ->from('kpi_templates kt')
                 ->join('kpi_categories kc', 'kt.category_id = kc.category_id')
                 ->where('kt.is_active', 1);
        
        if ($category_name) {
            $this->db->where('kc.category_name', $category_name);
        }
        
        return $this->db->order_by('kc.category_name, kt.kpi_name')
                        ->get()
                        ->result();
    }

    /**
     * Get employee KPIs for a specific period
     */
    public function get_employee_kpis($employee_id, $period_id = NULL) {
        if (!$period_id) {
            $active_period = $this->get_active_period();
            $period_id = $active_period ? $active_period->period_id : NULL;
        }
        
        if (!$period_id) {
            return [];
        }
        
        $this->db->select('ekpi.*, kt.kpi_name, kt.description, kt.category_id, kc.category_name, 
                          ks.weightage, ks.score, ks.weighted_score, ks.score_id, ks.remark,
                          rp.period_name, rp.period_type,
                          ekpi.employee_agreement_status, ekpi.employee_agreement_date, 
                          ekpi.employee_agreement_notes, ekpi.manager_response_notes,
                          ekpi.manager_responded_at, ekpi.manager_responded_by')
                 ->from('employee_kpis ekpi')
                 ->join('kpi_templates kt', 'ekpi.template_id = kt.template_id')
                 ->join('kpi_categories kc', 'kt.category_id = kc.category_id')
                 ->join('kpi_scores ks', 'ekpi.employee_kpi_id = ks.employee_kpi_id', 'left')
                 ->join('review_periods rp', 'ekpi.review_period_id = rp.period_id')
                 ->where('ekpi.employee_id', $employee_id)
                 ->where('ekpi.review_period_id', $period_id)
                 ->order_by('kc.category_name, kt.kpi_name');
        
        return $this->db->get()->result();
    }

    /**
     * Get KPI details by ID
     */
    public function get_kpi_by_id($employee_kpi_id) {
        $this->db->select('ekpi.*, kt.kpi_name, kt.description, kc.category_name, 
                          ks.weightage, ks.score, ks.weighted_score, ks.score_id, ks.remark,
                          rp.period_name, rp.period_type, 
                          u.first_name, u.last_name, u.employee_code')
                 ->from('employee_kpis ekpi')
                 ->join('kpi_templates kt', 'ekpi.template_id = kt.template_id')
                 ->join('kpi_categories kc', 'kt.category_id = kc.category_id')
                 ->join('kpi_scores ks', 'ekpi.employee_kpi_id = ks.employee_kpi_id', 'left')
                 ->join('review_periods rp', 'ekpi.review_period_id = rp.period_id')
                 ->join('users u', 'ekpi.employee_id = u.user_id')
                 ->where('ekpi.employee_kpi_id', $employee_kpi_id)
                 ->where('u.deleted_at IS NULL');
        
        return $this->db->get()->row();
    }

    /**
     * Assign KPI to employee
     */
    public function assign_kpi($employee_id, $template_id, $period_id, $assigned_by, $weightage = 0) {
        $employee_kpi_id = $this->generate_uuid();
        
        // Insert employee KPI
        $kpi_data = [
            'employee_kpi_id' => $employee_kpi_id,
            'employee_id' => $employee_id,
            'template_id' => $template_id,
            'review_period_id' => $period_id,
            'assigned_by' => $assigned_by,
            'assigned_date' => date('Y-m-d'),
            'status' => 'ASSIGNED'
        ];
        
        $this->db->insert('employee_kpis', $kpi_data);
        
        // Insert initial score with weightage
        $score_data = [
            'score_id' => $this->generate_uuid(),
            'employee_kpi_id' => $employee_kpi_id,
            'weightage' => $weightage,
            'score' => NULL,
            'last_edited_by' => $assigned_by,
            'last_edited_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('kpi_scores', $score_data);
        
        return $employee_kpi_id;
    }

    /**
     * Assign multiple KPIs from a template to employee
     * Manager can customize weightages for each KPI
     */
    public function assign_template_kpis($employee_id, $template_name, $period_id, $assigned_by, $kpis) {
        $this->db->trans_start();
        
        try {
            foreach ($kpis as $kpi) {
                $employee_kpi_id = $this->generate_uuid();
                
                // Handle template_id
                $template_id = NULL;
                
                if (!empty($kpi['template_id'])) {
                    // KPI from existing template
                    $template_id = $kpi['template_id'];
                } else {
                    // Manually added KPI - create a new template entry for it
                    $template_id = $this->generate_uuid();
                    $template_data = [
                        'template_id' => $template_id,
                        'template_name' => $template_name,
                        'kpi_name' => $kpi['kpi_name'],
                        'category_id' => $kpi['category_id'],
                        'description' => isset($kpi['description']) ? $kpi['description'] : NULL,
                        'weightage' => 0, // Default weightage in template
                        'default_score' => 0,
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    $this->db->insert('kpi_templates', $template_data);
                }
                
                // Insert employee KPI
                $kpi_data = [
                    'employee_kpi_id' => $employee_kpi_id,
                    'employee_id' => $employee_id,
                    'template_id' => $template_id,
                    'review_period_id' => $period_id,
                    'assigned_by' => $assigned_by,
                    'assigned_date' => date('Y-m-d'),
                    'status' => 'ASSIGNED'
                ];
                
                $this->db->insert('employee_kpis', $kpi_data);
                
                // Insert initial score with customized weightage
                $score_data = [
                    'score_id' => $this->generate_uuid(),
                    'employee_kpi_id' => $employee_kpi_id,
                    'weightage' => floatval($kpi['weightage']),
                    'score' => !empty($kpi['target_score']) ? floatval($kpi['target_score']) : NULL,
                    'last_edited_by' => $assigned_by,
                    'last_edited_at' => date('Y-m-d H:i:s')
                ];
                
                $this->db->insert('kpi_scores', $score_data);
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                log_message('error', 'Failed to assign template KPIs: ' . $this->db->error()['message']);
                return FALSE;
            }
            
            log_message('info', 'Template "' . $template_name . '" assigned to employee ' . $employee_id . ' with ' . count($kpis) . ' KPIs');
            return TRUE;
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Error assigning template KPIs: ' . $e->getMessage());
            return FALSE;
        }
    }

    /**
     * Update KPI score
     */
    public function update_score($score_id, $data, $edited_by) {
        // Get old values for history
        $old_score = $this->db->where('score_id', $score_id)->get('kpi_scores')->row();
        
        // Update score
        $update_data = array_merge($data, [
            'last_edited_by' => $edited_by,
            'last_edited_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->db->where('score_id', $score_id)->update('kpi_scores', $update_data);
        
        // Log history
        foreach ($data as $field => $new_value) {
            if (isset($old_score->$field) && $old_score->$field != $new_value) {
                $this->log_score_history(
                    $score_id,
                    $old_score->employee_kpi_id,
                    $field,
                    $old_score->$field,
                    $new_value,
                    $edited_by
                );
            }
        }
        
        return TRUE;
    }

    /**
     * Update KPI weightage
     */
    public function update_weightage($score_id, $weightage, $edited_by) {
        return $this->update_score($score_id, ['weightage' => $weightage], $edited_by);
    }

    /**
     * Update KPI score value
     */
    public function update_score_value($score_id, $score, $edited_by) {
        return $this->update_score($score_id, ['score' => $score], $edited_by);
    }

    /**
     * Calculate total score for employee in a period
     */
    public function calculate_total_score($employee_id, $period_id) {
        $this->db->select_sum('ks.weighted_score', 'total_score')
                 ->from('employee_kpis ekpi')
                 ->join('kpi_scores ks', 'ekpi.employee_kpi_id = ks.employee_kpi_id')
                 ->where('ekpi.employee_id', $employee_id)
                 ->where('ekpi.review_period_id', $period_id);
        
        $result = $this->db->get()->row();
        return $result ? $result->total_score : 0;
    }

    /**
     * Get total weightage for employee KPIs
     */
    public function get_total_weightage($employee_id, $period_id) {
        $this->db->select_sum('ks.weightage', 'total_weightage')
                 ->from('employee_kpis ekpi')
                 ->join('kpi_scores ks', 'ekpi.employee_kpi_id = ks.employee_kpi_id')
                 ->where('ekpi.employee_id', $employee_id)
                 ->where('ekpi.review_period_id', $period_id);
        
        $result = $this->db->get()->row();
        return $result ? $result->total_weightage : 0;
    }

    /**
     * Submit edit request
     */
    public function submit_edit_request($employee_kpi_id, $requested_by, $data) {
        // DEPRECATED: Now using employee_agreement_status in employee_kpis table
        // This method kept for backwards compatibility but does nothing
        log_message('info', 'submit_edit_request called but deprecated - use request_kpi_edit instead');
        return FALSE;
    }

    /**
     * Get edit requests for employee
     * DEPRECATED: Now using employee_agreement_status in employee_kpis table
     */
    public function get_edit_requests($employee_id = NULL, $status = NULL) {
        return [];
    }

    /**
     * Get pending edit requests for manager's team
     * DEPRECATED: Now using employee_agreement_status in employee_kpis table
     */
    public function get_pending_requests_for_manager($manager_id) {
        return [];
    }

    /**
     * Get pending edit requests for a specific employee
     */
    public function get_employee_pending_requests($employee_id) {
        // DEPRECATED: Now using employee_agreement_status in employee_kpis table
        // This method kept for backwards compatibility but returns empty array
        return [];
    }

    /**
     * Review edit request (approve/reject)
     * DEPRECATED: Now using employee_agreement_status in employee_kpis table
     */
    public function review_edit_request($request_id, $status, $reviewed_by, $comments = NULL) {
        return FALSE;
    }

    // ==================== KPI EDIT CHAT SYSTEM ====================
    
    /**
     * Get all chat messages for a specific employee_kpi_id
     */
    public function get_kpi_chat_messages($employee_kpi_id, $phase = NULL) {
        $this->db->select('kem.*, u.first_name, u.last_name, u.employee_code')
                 ->from('kpi_edit_messages kem')
                 ->join('users u', 'kem.sender_id = u.user_id')
                 ->where('kem.employee_kpi_id', $employee_kpi_id);
        
        // Filter by phase if specified
        if ($phase !== NULL) {
            $this->db->where('kem.phase', $phase);
        }
        
        return $this->db->order_by('kem.created_at', 'ASC')
                        ->get()
                        ->result();
    }
    
    /**
     * Add a new message to the chat
     * $phase: 'SETUP' for setup mode conversations, 'SCORING' for scoring mode conversations
     */
    public function add_chat_message($employee_kpi_id, $employee_id, $sender_id, $sender_type, $message, $phase = 'SETUP') {
        $data = [
            'message_id' => $this->generate_uuid(),
            'employee_kpi_id' => $employee_kpi_id,
            'employee_id' => $employee_id,
            'sender_id' => $sender_id,
            'sender_type' => $sender_type,
            'message' => $message,
            'phase' => $phase
        ];
        
        return $this->db->insert('kpi_edit_messages', $data);
    }
    
    /**
     * Set employee agreement status and optionally lock KPIs
     */
    public function set_employee_agreement($employee_kpi_id, $status, $lock = false, $notes = null) {
        $data = [
            'employee_agreement_status' => $status,
            'employee_agreement_date' => date('Y-m-d H:i:s')
        ];
        
        if ($lock) {
            $data['is_locked'] = 1;
        }
        
        if ($notes !== null) {
            $data['employee_agreement_notes'] = $notes;
        }
        
        return $this->db->where('employee_kpi_id', $employee_kpi_id)
                        ->update('employee_kpis', $data);
    }
    
    /**
     * Update KPI weightage
     */
    public function update_kpi_weightage($employee_kpi_id, $weightage) {
        return $this->db->where('employee_kpi_id', $employee_kpi_id)
                        ->update('employee_kpis', ['weightage' => $weightage]);
    }
    
    /**
     * Delete employee KPI (alias for delete_kpi)
     */
    public function delete_employee_kpi($employee_kpi_id) {
        return $this->delete_kpi($employee_kpi_id);
    }

    /**
     * Log score history
     */
    private function log_score_history($score_id, $employee_kpi_id, $field, $old_value, $new_value, $changed_by, $reason = NULL) {
        $history_data = [
            'history_id' => $this->generate_uuid(),
            'score_id' => $score_id,
            'employee_kpi_id' => $employee_kpi_id,
            'field_changed' => $field,
            'old_value' => $old_value,
            'new_value' => $new_value,
            'changed_by' => $changed_by,
            'change_reason' => $reason
        ];
        
        $this->db->insert('kpi_score_history', $history_data);
    }

    /**
     * Get score history
     */
    public function get_score_history($score_id) {
        $this->db->select('ksh.*, u.first_name, u.last_name')
                 ->from('kpi_score_history ksh')
                 ->join('users u', 'ksh.changed_by = u.user_id', 'left')
                 ->where('ksh.score_id', $score_id)
                 ->where('u.deleted_at IS NULL')
                 ->order_by('ksh.changed_at', 'DESC');
        
        return $this->db->get()->result();
    }

    /**
     * Delete employee KPI
     */
    public function delete_kpi($employee_kpi_id) {
        return $this->db->where('employee_kpi_id', $employee_kpi_id)
                        ->delete('employee_kpis');
    }

    /**
     * Finalize employee review for a period
     * Marks all KPIs as finalized and sends email notification to manager
     */
    public function finalize_employee_review($employee_id, $period_id) {
        $this->db->trans_start();
        
        // Get employee details with manager's email from users table
        $employee = $this->db->select('u.*, 
                                       ewi.reporting_manager_id,
                                       m.first_name as manager_first_name, 
                                       m.last_name as manager_last_name, 
                                       m.email as manager_email')
                             ->from('users u')
                             ->join('employee_work_info ewi', 'u.user_id = ewi.employee_id', 'left')
                             ->join('users m', 'ewi.reporting_manager_id = m.user_id', 'left')
                             ->where('u.user_id', $employee_id)
                             ->where('u.deleted_at IS NULL')
                             ->get()
                             ->row();
        
        if (!$employee) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Employee not found'];
        }
        
        // Get period details
        $period = $this->db->where('period_id', $period_id)->get('review_periods')->row();
        
        if (!$period) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Review period not found'];
        }
        
        // Get all KPIs and calculate totals
        $kpis = $this->get_employee_kpis($employee_id, $period_id);
        $total_score = $this->calculate_total_score($employee_id, $period_id);
        $total_weightage = $this->get_total_weightage($employee_id, $period_id);
        
        // Check if weightage is 100%
        if ($total_weightage != 100) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => 'Cannot finalize: Total weightage must be exactly 100%'];
        }
        
        // Check if all KPIs are scored
        $unscored = 0;
        foreach ($kpis as $kpi) {
            if ($kpi->score === NULL) {
                $unscored++;
            }
        }
        
        if ($unscored > 0) {
            $this->db->trans_rollback();
            return ['success' => false, 'message' => "Cannot finalize: {$unscored} KPI(s) not yet scored by manager"];
        }
        
        // Update all employee_kpis to mark as finalized
        $this->db->where('employee_id', $employee_id)
                 ->where('review_period_id', $period_id)
                 ->update('employee_kpis', [
                     'is_finalized' => 1,
                     'finalized_at' => date('Y-m-d H:i:s')
                 ]);
        
        $this->db->trans_complete();
        
        if ($this->db->trans_status() === FALSE) {
            return ['success' => false, 'message' => 'Database error during finalization'];
        }
        
        // Send email notification to manager
        if ($employee->reporting_manager_id) {
            $email_sent = $this->send_finalization_email($employee, $period, $kpis, $total_score, $total_weightage);
        } else {
            $email_sent = false;
        }
        
        return [
            'success' => true,
            'message' => 'Performance review finalized successfully',
            'email_sent' => $email_sent,
            'total_score' => $total_score,
            'kpi_count' => count($kpis)
        ];
    }

    /**
     * Check if employee review is finalized for a period
     */
    public function is_review_finalized($employee_id, $period_id) {
        $result = $this->db->select('COUNT(*) as total, SUM(CASE WHEN is_finalized = 1 THEN 1 ELSE 0 END) as finalized')
                           ->from('employee_kpis')
                           ->where('employee_id', $employee_id)
                           ->where('review_period_id', $period_id)
                           ->get()
                           ->row();
        
        return $result && $result->total > 0 && $result->finalized == $result->total;
    }

    /**
     * Send finalization email to reporting manager
     */
    private function send_finalization_email($employee, $period, $kpis, $total_score, $total_weightage) {
        // Load email library
        $this->load->library('email');
        
        // Configure email for Gmail
        $config = array(
            'protocol'  => 'smtp',
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_user' => $this->config->item('smtp_user', 'email'),
            'smtp_pass' => $this->config->item('smtp_pass', 'email'),
            'smtp_crypto' => 'tls',
            'mailtype'  => 'html',
            'charset'   => 'utf-8',
            'wordwrap'  => TRUE,
            'newline'   => "\r\n"
        );
        
        $this->email->initialize($config);
        
        // Determine manager email
        $manager_email = !empty($employee->manager_email) ? $employee->manager_email : $employee->manager_employee_email;
        
        if (empty($manager_email)) {
            log_message('error', 'Cannot send finalization email: Manager email not found for employee ' . $employee->employee_id);
            return false;
        }
        
        // Email subject
        $subject = "KPI Review Finalized - {$employee->first_name} {$employee->last_name} ({$period->period_name})";
        
        // Email body
        $message = $this->load->view('emails/review_finalized', [
            'employee' => $employee,
            'period' => $period,
            'kpis' => $kpis,
            'total_score' => $total_score,
            'total_weightage' => $total_weightage
        ], TRUE);
        
        // Set email parameters
        $this->email->from($this->config->item('smtp_from', 'email'), $this->config->item('smtp_from_name', 'email'));
        $this->email->to($manager_email);
        $this->email->subject($subject);
        $this->email->message($message);
        
        // Send email
        if ($this->email->send()) {
            log_message('info', "Finalization email sent to {$manager_email} for employee {$employee->employee_id}");
            return true;
        } else {
            log_message('error', 'Email sending failed: ' . $this->email->print_debugger());
            return false;
        }
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
}
