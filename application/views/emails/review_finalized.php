<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPI Review Finalized</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border: 1px solid #dee2e6;
            border-radius: 0 0 5px 5px;
        }
        .score-box {
            background-color: white;
            border: 2px solid #007bff;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .score-box h2 {
            color: #007bff;
            font-size: 48px;
            margin: 10px 0;
        }
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: white;
        }
        .kpi-table th {
            background-color: #007bff;
            color: white;
            padding: 12px;
            text-align: left;
        }
        .kpi-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #dee2e6;
        }
        .kpi-table tr:last-child td {
            border-bottom: none;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        .badge-primary {
            background-color: #007bff;
            color: white;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        .badge-info {
            background-color: #17a2b8;
            color: white;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            color: #6c757d;
            font-size: 12px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 KPI Review Finalized</h1>
    </div>
    
    <div class="content">
        <p>Dear <strong><?php echo $employee->manager_first_name . ' ' . $employee->manager_last_name; ?></strong>,</p>
        
        <p>
            <strong><?php echo $employee->first_name . ' ' . $employee->last_name; ?></strong> 
            has confirmed and finalized their KPI review for the 
            <strong><?php echo $period->period_name; ?></strong> period.
        </p>
        
        <div class="score-box">
            <h4 style="margin: 0; color: #6c757d;">Overall Performance Score</h4>
            <h2><?php echo number_format($total_score, 2); ?></h2>
            <p style="margin: 5px 0; color: #6c757d;">out of 100</p>
        </div>
        
        <h3>📋 Review Summary</h3>
        <table style="width: 100%; background-color: white; margin: 15px 0;">
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;"><strong>Employee:</strong></td>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;"><?php echo $employee->first_name . ' ' . $employee->last_name; ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;"><strong>Review Period:</strong></td>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;"><?php echo $period->period_name; ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;"><strong>Period Dates:</strong></td>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                    <?php echo date('M d, Y', strtotime($period->start_date)); ?> - 
                    <?php echo date('M d, Y', strtotime($period->end_date)); ?>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;"><strong>Total KPIs:</strong></td>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;"><?php echo count($kpis); ?></td>
            </tr>
            <tr>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;"><strong>Total Weightage:</strong></td>
                <td style="padding: 10px; border-bottom: 1px solid #dee2e6;">
                    <span class="badge <?php echo $total_weightage == 100 ? 'badge-success' : 'badge-danger'; ?>">
                        <?php echo number_format($total_weightage, 1); ?>%
                    </span>
                </td>
            </tr>
            <tr>
                <td style="padding: 10px;"><strong>Finalized On:</strong></td>
                <td style="padding: 10px;"><?php echo date('M d, Y h:i A'); ?></td>
            </tr>
        </table>
        
        <h3>🎯 KPI Breakdown</h3>
        <table class="kpi-table">
            <thead>
                <tr>
                    <th>KPI Name</th>
                    <th>Category</th>
                    <th>Weightage</th>
                    <th>Score</th>
                    <th>Weighted</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($kpis as $kpi): ?>
                <tr>
                    <td><strong><?php echo $kpi->kpi_name; ?></strong></td>
                    <td><span class="badge badge-info"><?php echo $kpi->category_name; ?></span></td>
                    <td><?php echo number_format($kpi->weightage, 1); ?>%</td>
                    <td>
                        <span class="badge <?php 
                            if ($kpi->score >= 4) echo 'badge-success';
                            elseif ($kpi->score >= 3) echo 'badge-primary';
                            elseif ($kpi->score >= 2) echo 'badge-warning';
                            else echo 'badge-danger';
                        ?>"><?php echo number_format($kpi->score, 1); ?> / 5</span>
                    </td>
                    <td><strong><?php echo number_format($kpi->weighted_score, 2); ?></strong></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div style="background-color: #e7f3ff; border-left: 4px solid #007bff; padding: 15px; margin: 20px 0;">
            <p style="margin: 0;">
                <strong>ℹ️ What's Next?</strong><br>
                The employee has acknowledged the review scores. You can now:
            </p>
            <ul style="margin: 10px 0;">
                <li>Add final comments or feedback</li>
                <li>Export the performance report</li>
                <li>Schedule a one-on-one meeting to discuss results</li>
                <li>Set goals for the next review period</li>
            </ul>
        </div>
        
        <div style="text-align: center;">
            <a href="<?php echo base_url('manager/dashboard'); ?>" class="button">
                View Full Report in Portal
            </a>
        </div>
        
        <p style="margin-top: 30px; color: #6c757d; font-size: 14px;">
            <strong>Note:</strong> This email was generated automatically by the KPI Portal system. 
            The employee has confirmed acceptance of the performance scores for this review period.
        </p>
    </div>
    
    <div class="footer">
        <p>
            <strong>KPI Performance Management Portal</strong><br>
            © <?php echo date('Y'); ?> Your Organization. All rights reserved.<br>
            This is an automated notification. Please do not reply to this email.
        </p>
    </div>
</body>
</html>
