<?php
/**
 * PHPMailer Helper for KPI Portal
 * Download PHPMailer from: https://github.com/PHPMailer/PHPMailer
 * Place PHPMailer files in application/libraries/PHPMailer/
 */

if (!function_exists('send_kpi_email')) {
    /**
     * Send email using PHPMailer with Gmail SMTP
     * 
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $body HTML email body
     * @param array $config Email configuration (from email.php)
     * @return array Result array with 'success' and 'message'
     */
    function send_kpi_email($to, $subject, $body, $config = null) {
        $CI =& get_instance();
        
        // Load email config if not provided
        if ($config === null) {
            $CI->config->load('email');
            $config = $CI->config->item('email');
        }
        
        // Check if PHPMailer is available
        $phpmailer_path = APPPATH . 'libraries/PHPMailer/src/PHPMailer.php';
        
        if (!file_exists($phpmailer_path)) {
            log_message('error', 'PHPMailer not found at: ' . $phpmailer_path);
            return [
                'success' => false,
                'message' => 'PHPMailer library not installed'
            ];
        }
        
        try {
            require_once APPPATH . 'libraries/PHPMailer/src/Exception.php';
            require_once APPPATH . 'libraries/PHPMailer/src/PHPMailer.php';
            require_once APPPATH . 'libraries/PHPMailer/src/SMTP.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $config['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $config['smtp_user'];
            $mail->Password   = $config['smtp_pass'];
            $mail->SMTPSecure = $config['smtp_crypto']; // 'tls' or 'ssl'
            $mail->Port       = $config['smtp_port'];
            
            // Recipients
            $mail->setFrom($config['smtp_from'], $config['smtp_from_name']);
            $mail->addAddress($to);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);
            
            // Send email
            $mail->send();
            
            log_message('info', 'Email sent successfully to: ' . $to);
            
            return [
                'success' => true,
                'message' => 'Email sent successfully'
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Email sending failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Email could not be sent. Error: ' . $e->getMessage()
            ];
        }
    }
}

if (!function_exists('send_kpi_email_fallback')) {
    /**
     * Fallback email function using PHP's mail() if PHPMailer is not available
     * Note: This won't work with Gmail SMTP authentication
     * 
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $body HTML email body
     * @param array $config Email configuration
     * @return array Result array
     */
    function send_kpi_email_fallback($to, $subject, $body, $config) {
        $headers = array(
            'From: ' . $config['smtp_from'],
            'Reply-To: ' . $config['smtp_from'],
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8'
        );
        
        $success = mail($to, $subject, $body, implode("\r\n", $headers));
        
        if ($success) {
            log_message('info', 'Email sent via fallback to: ' . $to);
            return ['success' => true, 'message' => 'Email sent'];
        } else {
            log_message('error', 'Fallback email failed to: ' . $to);
            return ['success' => false, 'message' => 'Email failed'];
        }
    }
}

if (!function_exists('notify_kpi_agreement')) {
    /**
     * Notify manager(s) when employee agrees to KPIs
     * 
     * @param string $manager_email Manager's email address
     * @param object $employee Employee object with details
     * @param string $period_id Review period ID
     * @return array Result array
     */
    function notify_kpi_agreement($manager_email, $employee, $period_id) {
        $CI =& get_instance();
        
        $subject = 'Employee KPI Agreement - ' . $employee->first_name . ' ' . $employee->last_name;
        
        $body = '
        <html>
        <body style="font-family: Arial, sans-serif;">
            <h2 style="color: #28a745;">Employee Agreed to KPIs</h2>
            <p>Hello,</p>
            <p><strong>' . htmlspecialchars($employee->first_name . ' ' . $employee->last_name) . '</strong> 
            (Employee Code: ' . htmlspecialchars($employee->employee_code) . ') has agreed to the assigned KPIs.</p>
            
            <p><strong>Period:</strong> ' . htmlspecialchars($period_id) . '</p>
            
            <p>You can now proceed to assign scores during the review period.</p>
            
            <p>
                <a href="' . base_url('manager/view_employee/' . $employee->employee_id) . '" 
                   style="background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                    View Employee KPIs
                </a>
            </p>
            
            <hr>
            <p style="color: #666; font-size: 12px;">
                This is an automated notification from the KPI Portal System.<br>
                Please do not reply to this email.
            </p>
        </body>
        </html>
        ';
        
        return send_kpi_email($manager_email, $subject, $body);
    }
}

if (!function_exists('notify_kpi_query')) {
    /**
     * Notify manager(s) when employee requests KPI edit
     * 
     * @param string $manager_email Manager's email address
     * @param object $employee Employee object with details
     * @param string $period_id Review period ID
     * @param string $notes Employee's edit request notes
     * @return array Result array
     */
    function notify_kpi_query($manager_email, $employee, $period_id, $notes) {
        $CI =& get_instance();
        
        $subject = 'KPI Edit Request - ' . $employee->first_name . ' ' . $employee->last_name;
        
        $body = '
        <html>
        <body style="font-family: Arial, sans-serif;">
            <h2 style="color: #ffc107;">KPI Edit Request</h2>
            <p>Hello,</p>
            <p><strong>' . htmlspecialchars($employee->first_name . ' ' . $employee->last_name) . '</strong> 
            (Employee Code: ' . htmlspecialchars($employee->employee_code) . ') has requested changes to their assigned KPIs.</p>
            
            <p><strong>Period:</strong> ' . htmlspecialchars($period_id) . '</p>
            
            <div style="background-color: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;">
                <h4 style="margin-top: 0;">Employee\'s Request:</h4>
                <p style="white-space: pre-wrap;">' . nl2br(htmlspecialchars($notes)) . '</p>
            </div>
            
            <p>Please review the request and take appropriate action.</p>
            
            <p>
                <a href="' . base_url('manager/kpi_edit_requests') . '" 
                   style="background-color: #ffc107; color: #000; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                    View Edit Requests
                </a>
            </p>
            
            <hr>
            <p style="color: #666; font-size: 12px;">
                This is an automated notification from the KPI Portal System.<br>
                Please do not reply to this email.
            </p>
        </body>
        </html>
        ';
        
        return send_kpi_email($manager_email, $subject, $body);
    }
}

if (!function_exists('notify_kpi_updated')) {
    /**
     * Notify employee when manager updates their KPIs
     * 
     * @param string $employee_email Employee's email address
     * @param object $employee Employee object with details
     * @param object $manager Manager object with details
     * @param object $period Review period object
     * @param string $manager_notes Manager's explanation of changes
     * @return array Result array
     */
    function notify_kpi_updated($employee_email, $employee, $manager, $period, $manager_notes) {
        $CI =& get_instance();
        
        $subject = 'Your KPIs Have Been Updated - ' . $period->period_name;
        
        $body = '
        <html>
        <body style="font-family: Arial, sans-serif;">
            <h2 style="color: #17a2b8;">Your KPIs Have Been Updated</h2>
            <p>Hello <strong>' . htmlspecialchars($employee->first_name . ' ' . $employee->last_name) . '</strong>,</p>
            
            <p>Your manager, <strong>' . htmlspecialchars($manager->first_name . ' ' . $manager->last_name) . '</strong>, 
            has reviewed and updated your KPIs for <strong>' . htmlspecialchars($period->period_name) . '</strong>.</p>
            
            ' . (!empty($manager_notes) ? '
            <div style="background-color: #d1ecf1; padding: 15px; border-left: 4px solid #17a2b8; margin: 20px 0;">
                <h4 style="margin-top: 0; color: #0c5460;">Manager\'s Message:</h4>
                <p style="white-space: pre-wrap;">' . nl2br(htmlspecialchars($manager_notes)) . '</p>
            </div>
            ' : '') . '
            
            <p><strong>What You Need to Do:</strong></p>
            <ul>
                <li>Review the updated KPI weightages</li>
                <li>If you agree with the changes, click "I Agree" to proceed</li>
                <li>If you need further changes, you can request another edit</li>
            </ul>
            
            <p>
                <a href="' . base_url('employee/my_kpis') . '" 
                   style="background-color: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                    Review Updated KPIs
                </a>
            </p>
            
            <hr>
            <p style="color: #666; font-size: 12px;">
                This is an automated notification from the KPI Portal System.<br>
                Please do not reply to this email.
            </p>
        </body>
        </html>
        ';
        
        return send_kpi_email($employee_email, $subject, $body);
    }
}
