<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="KPI Portal - Key Performance Indicator Management System">
    <meta name="author" content="KPI Portal">
    <meta name="keywords" content="KPI, Performance, Management, Dashboard, Review">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo base_url('assets/img/kpi.png'); ?>">
    <link rel="shortcut icon" type="image/png" href="<?php echo base_url('assets/img/kpi.png'); ?>">
    <link rel="apple-touch-icon" href="<?php echo base_url('assets/img/kpi.png'); ?>">
    
    <!-- Open Graph / Social Media Meta Tags -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>KPI Portal">
    <meta property="og:description" content="Key Performance Indicator Management System">
    <meta property="og:image" content="<?php echo base_url('assets/img/kpi.png'); ?>">
    <meta property="og:url" content="<?php echo current_url(); ?>">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>KPI Portal">
    <meta name="twitter:description" content="Key Performance Indicator Management System">
    <meta name="twitter:image" content="<?php echo base_url('assets/img/kpi.png'); ?>">
    
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>KPI Portal</title>
    
    <!-- Bootstrap 4 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
        :root {
            --primary-red: #dc3545;
            --dark-red: #c82333;
            --light-red: #e85563;
            --sidebar-width: 250px;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-red) 0%, var(--dark-red) 100%);
            color: white;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .sidebar-header {
            padding: 20px;
            background: rgba(0,0,0,0.2);
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h3 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .sidebar-header .logo-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .user-info {
            padding: 15px 20px;
            background: rgba(0,0,0,0.15);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .user-info .user-name {
            font-weight: bold;
            font-size: 1rem;
        }
        
        .user-info .user-role {
            font-size: 0.85rem;
            opacity: 0.9;
        }
        
        .sidebar-menu {
            padding: 10px 0;
        }
        
        .sidebar-menu .menu-item {
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            display: block;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu .menu-item:hover {
            background: rgba(255,255,255,0.1);
            border-left-color: white;
            text-decoration: none;
            color: white;
        }
        
        .sidebar-menu .menu-item.active {
            background: rgba(255,255,255,0.15);
            border-left-color: white;
            font-weight: bold;
        }
        
        .sidebar-menu .menu-item i {
            width: 25px;
            margin-right: 10px;
        }
        
        .sidebar-menu .menu-section {
            padding: 15px 20px 5px;
            font-size: 0.75rem;
            text-transform: uppercase;
            opacity: 0.7;
            letter-spacing: 1px;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s ease;
        }
        
        .top-navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px 30px;
            margin-bottom: 0;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .top-navbar .breadcrumb {
            background: none;
            padding: 0;
            margin: 0;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--dark-red) 100%);
            color: white;
            padding: 30px;
            margin-bottom: 30px;
            border-radius: 0;
        }
        
        .page-header h2 {
            margin: 0 0 5px 0;
            font-weight: 600;
        }
        
        .content-wrapper {
            padding: 30px;
        }
        
        .card {
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
            border: none;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .card-header {
            background: white;
            border-bottom: 2px solid var(--primary-red);
            font-weight: 600;
            color: var(--primary-red);
        }
        
        .btn-primary {
            background-color: var(--primary-red);
            border-color: var(--primary-red);
        }
        
        .btn-primary:hover {
            background-color: var(--dark-red);
            border-color: var(--dark-red);
        }
        
        .badge-primary {
            background-color: var(--primary-red);
        }
        
        .text-primary {
            color: var(--primary-red) !important;
        }
        
        .bg-primary {
            background-color: var(--primary-red) !important;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            
            .sidebar.active {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .menu-toggle {
                display: block !important;
            }
        }
        
        .menu-toggle {
            display: none;
            cursor: pointer;
            font-size: 1.5rem;
            color: var(--primary-red);
        }
        
        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: 8px;
            right: 15px;
            background: white;
            color: var(--primary-red);
            border-radius: 10px;
            padding: 2px 6px;
            font-size: 0.7rem;
            font-weight: bold;
        }
        
        .sidebar-menu .menu-item {
            position: relative;
        }
    </style>
</head>
<body>
    <?php if ($this->session->userdata('logged_in')): ?>
    <?php 
    $primary_role = strtolower($this->session->userdata('primary_role'));
    $user_roles = $this->session->userdata('roles');
    if (!is_array($user_roles)) {
        $user_roles = [];
    }
    $current_url = uri_string();
    ?>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <h3>KPI Portal</h3>
        </div>
        
        <div class="user-info">
            <div class="user-name">
                <i class="fas fa-user-circle"></i>
                <?php echo $this->session->userdata('user_name'); ?>
            </div>
            <div class="user-role">
                <i class="fas fa-id-badge"></i>
                <?php echo ucfirst($primary_role); ?>
            </div>
        </div>
        
        <div class="sidebar-menu">
            <!-- Debug: Remove after testing -->
            <!-- Role: <?php echo $primary_role; ?> -->
            
            <?php if ($primary_role === 'super_admin'): ?>
                <!-- Super Admin Menu -->
                <div class="menu-section">Dashboard</div>
                <a href="<?php echo base_url('admin/dashboard'); ?>" 
                   class="menu-item <?php echo (strpos($current_url, 'admin/dashboard') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                
                <div class="menu-section">KPI Management</div>
                <a href="<?php echo base_url('admin/kpi_templates'); ?>" 
                   class="menu-item <?php echo (strpos($current_url, 'admin/kpi_templates') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-tasks"></i> KPI Templates
                </a>
                <a href="<?php echo base_url('admin/kpi_categories'); ?>" 
                   class="menu-item <?php echo (strpos($current_url, 'admin/categories') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-folder"></i> Categories
                </a>
                
                <div class="menu-section">User Management</div>
                <!-- <a href="<?php echo base_url('admin/users'); ?>" 
                   class="menu-item <?php echo (strpos($current_url, 'admin/users') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Users
                </a> -->
                <a href="<?php echo base_url('admin/employees'); ?>" 
                   class="menu-item <?php echo (strpos($current_url, 'admin/employees') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-user-tie"></i> Employees
                </a>
                <a href="<?php echo base_url('admin/departments'); ?>" 
                   class="menu-item <?php echo (strpos($current_url, 'admin/departments') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-building"></i> Departments
                </a>
                
                <div class="menu-section">Quick Actions</div>
                <a href="<?php echo base_url('admin/kpi_templates/create'); ?>" 
                   class="menu-item <?php echo (strpos($current_url, 'admin/kpi_templates/create') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-plus-circle"></i> Create KPI Template
                </a>
                
                <a href="<?php echo base_url('admin/create_employee'); ?>" 
                   class="menu-item <?php echo (strpos($current_url, 'admin/employees/create') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-id-card"></i> Add Employee
                </a>
                <a href="<?php echo base_url('admin/review_periods/create'); ?>" 
                   class="menu-item <?php echo (strpos($current_url, 'admin/review_periods/create') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-plus"></i> Create Review Period
                </a>
                
                <div class="menu-section">Settings</div>
                <a href="<?php echo base_url('admin/review_periods'); ?>" 
                   class="menu-item <?php echo (strpos($current_url, 'admin/review_periods') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt"></i> Review Periods
                </a>
                <a href="<?php echo base_url('admin/settings'); ?>" 
                   class="menu-item <?php echo (strpos($current_url, 'admin/settings') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i> System Settings
                </a>
                
            <?php elseif ($primary_role === 'manager'): ?>
                <!-- Manager Menu -->
                <div class="menu-section">Dashboard</div>
                <a href="<?php echo base_url('manager/dashboard'); ?>" 
                   class="menu-item <?php echo (strpos($current_url, 'manager/dashboard') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                
                <div class="menu-section">Team Management</div>
                <a href="<?php echo base_url('manager/team'); ?>" 
                   class="menu-item <?php echo (strpos($current_url, 'manager/team') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> My Team
                </a>
                
                <div class="menu-section">My KPIs</div>
                <a href="<?php echo base_url('employee/dashboard'); ?>" 
                   class="menu-item <?php echo (strpos($current_url, 'employee/dashboard') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i> My Dashboard
                </a>
                <a href="<?php echo base_url('employee/my_kpis'); ?>" 
                   class="menu-item <?php echo (strpos($current_url, 'employee/my_kpis') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-list-alt"></i> My KPIs
                </a>
                
            <?php else: ?>
                <!-- Employee Menu -->
                <div class="menu-section">Dashboard</div>
                <a href="<?php echo base_url('employee/dashboard'); ?>" 
                   class="menu-item <?php echo (strpos($current_url, 'employee/dashboard') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                
                <div class="menu-section">My Performance</div>
                <a href="<?php echo base_url('employee/my_kpis'); ?>" 
                   class="menu-item <?php echo (strpos($current_url, 'employee/my_kpis') !== false) ? 'active' : ''; ?>">
                    <i class="fas fa-list-alt"></i> My KPIs
                </a>
                
            <?php endif; ?>
            
            <div class="menu-section">Account</div>
            <a href="<?php echo base_url(($primary_role === 'super_admin' ? 'admin' : $primary_role) . '/change_password'); ?>" 
               class="menu-item <?php echo (strpos($current_url, 'change_password') !== false) ? 'active' : ''; ?>">
                <i class="fas fa-key"></i> Change Password
            </a>
            <a href="<?php echo base_url('auth/logout'); ?>" class="menu-item">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="top-navbar">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="menu-toggle" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </span>
                    <nav aria-label="breadcrumb" class="d-inline-block ml-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo base_url(($primary_role === 'super_admin' ? 'admin' : $primary_role)); ?>">Home</a></li>
                            <?php if (isset($page_title)): ?>
                                <li class="breadcrumb-item active"><?php echo $page_title; ?></li>
                            <?php endif; ?>
                        </ol>
                    </nav>
                </div>
                <div class="text-muted">
                    <i class="fas fa-clock"></i> <?php echo date('l, F j, Y'); ?>
                </div>
            </div>
        </div>
        
        <!-- Flash Messages -->
        <?php if ($this->session->flashdata('success')): ?>
        <div class="content-wrapper">
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-check-circle"></i> <?php echo $this->session->flashdata('success'); ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($this->session->flashdata('error')): ?>
        <div class="content-wrapper">
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-exclamation-circle"></i> <?php echo $this->session->flashdata('error'); ?>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($this->session->flashdata('warning')): ?>
        <div class="content-wrapper">
            <div class="alert alert-warning alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-exclamation-triangle"></i> <?php echo $this->session->flashdata('warning'); ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?>