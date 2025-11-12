<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to KPI Portal</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px;
        }
        .credentials-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .credentials-box .label {
            font-weight: bold;
            color: #667eea;
            display: inline-block;
            width: 120px;
        }
        .credentials-box .value {
            font-family: 'Courier New', monospace;
            background: white;
            padding: 8px 12px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 5px;
        }
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #667eea;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }
        .button:hover {
            background: #5568d3;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .divider {
            border-top: 2px solid #eee;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Welcome to KPI Portal!</h1>
        </div>
        
        <div class="content">
            <p>Dear <strong><?= $employee['first_name'] ?> <?= $employee['last_name'] ?></strong>,</p>
            
            <p>Welcome aboard! Your account has been created in the KPI Portal system. Below are your login credentials:</p>
            
            <div class="credentials-box">
                <div style="margin-bottom: 15px;">
                    <div class="label">Email:</div>
                    <div class="value"><?= $employee['email'] ?></div>
                </div>
                <div>
                    <div class="label">Password:</div>
                    <div class="value"><?= $employee['password'] ?></div>
                </div>
            </div>
            
            <div class="warning-box">
                <strong>⚠️ Important Security Notice:</strong><br>
                For security reasons, please change your password immediately after your first login.
            </div>
            
            <div style="text-align: center;">
                <a href="<?= base_url('auth/login') ?>" class="button">
                    🔐 Login to KPI Portal
                </a>
            </div>
            
            <div class="divider"></div>
            
            <div class="info-box">
                <strong>📋 Your Employee Details:</strong><br>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li><strong>Employee Code:</strong> <?= $employee['employee_code'] ?></li>
                    <li><strong>Designation:</strong> <?= $employee['designation'] ?></li>
                    <li><strong>Department:</strong> <?= $employee['department_name'] ?? 'N/A' ?></li>
                    <li><strong>Date of Joining:</strong> <?= date('d M Y', strtotime($employee['date_of_joining'])) ?></li>
                </ul>
            </div>
            
            <h3>What's Next?</h3>
            <ol>
                <li><strong>Login</strong> using the credentials provided above</li>
                <li><strong>Change your password</strong> from your profile settings</li>
                <li><strong>Complete your profile</strong> with any additional information</li>
                <li><strong>Explore the KPI Portal</strong> features and dashboard</li>
            </ol>
            
            <p>If you have any questions or need assistance, please contact your HR Spokesperson or IT Support.</p>
            
            <p>Best regards,<br>
            <strong>KPI Portal Team</strong></p>
        </div>
        
        <div class="footer">
            <p>This is an automated email from KPI Portal. Please do not reply to this email.</p>
            <p>© <?= date('Y') ?> KPI Portal. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
