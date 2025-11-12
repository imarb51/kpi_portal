<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Email Configuration
|--------------------------------------------------------------------------
| Configure email settings for KPI Portal notifications
| Uses CodeIgniter's Email library with Gmail SMTP
*/

// Create email configuration array
$config['email'] = array(
    'protocol'  => 'smtp',
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_user' => 'imran.shaikh@gozoop.com',
    'smtp_pass' => 'sspq rexr xvjy ckfn',
    'smtp_crypto' => 'tls',
    'smtp_from' => 'imran.shaikh@gozoop.com',
    'smtp_from_name' => 'KPI Portal',
    'mailtype'  => 'html',
    'charset'   => 'utf-8',
    'wordwrap'  => TRUE,
    'newline'   => "\r\n",
    'crlf'      => "\r\n",
    'validate'  => TRUE
);

// Also set individual config items for backward compatibility
$config['protocol'] = 'smtp';
$config['smtp_host'] = 'smtp.gmail.com';
$config['smtp_port'] = 587;
$config['smtp_user'] = 'imran.shaikh@gozoop.com';
$config['smtp_pass'] = 'sspq rexr xvjy ckfn';
$config['smtp_crypto'] = 'tls';
$config['smtp_from'] = 'imran.shaikh@gozoop.com';
$config['smtp_from_name'] = 'KPI Portal';
$config['charset'] = 'utf-8';
$config['newline'] = "\r\n";
$config['crlf'] = "\r\n";
$config['wordwrap'] = TRUE;
$config['mailtype'] = 'html';
$config['validate'] = TRUE;

/*
|--------------------------------------------------------------------------
| Gmail Configuration Instructions
|--------------------------------------------------------------------------
| If using Gmail:
| 1. Enable 2-Step Verification on your Google Account
| 2. Generate an "App Password" at https://myaccount.google.com/apppasswords
| 3. Use the generated 16-character password as smtp_pass
|
| For Office 365:
| - smtp_host = 'smtp.office365.com'
| - smtp_port = 587
| - smtp_crypto = 'tls'
|
| For Local Development (MailHog):
| - smtp_host = 'localhost'
| - smtp_port = 1025
| - smtp_crypto = '' (empty)
| - smtp_user = '' (empty)
| - smtp_pass = '' (empty)
*/
