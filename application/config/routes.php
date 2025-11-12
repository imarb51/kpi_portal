<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
*/

$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/*
| -------------------------------------------------------------------------
| Authentication Routes
| -------------------------------------------------------------------------
*/
$route['login'] = 'auth/login';
$route['logout'] = 'auth/logout';
$route['authenticate'] = 'auth/authenticate';

/*
| -------------------------------------------------------------------------
| Dashboard Routes
| -------------------------------------------------------------------------
*/
$route['dashboard'] = 'dashboard/index';

/*
| -------------------------------------------------------------------------
| Manager Routes
| -------------------------------------------------------------------------
*/
$route['manager'] = 'manager/dashboard';
$route['manager/dashboard'] = 'manager/dashboard';
$route['manager/team'] = 'manager/team';
$route['manager/team/view/(:any)'] = 'manager/view_employee/$1';
$route['manager/kpi/assign/(:any)'] = 'manager/assign_kpi/$1';
$route['manager/kpi/edit-score/(:any)'] = 'manager/edit_kpi_score/$1';
$route['manager/edit-requests'] = 'manager/edit_requests';
$route['manager/edit-request/review/(:any)'] = 'manager/review_edit_request/$1';

/*
| -------------------------------------------------------------------------
| Employee Routes
| -------------------------------------------------------------------------
*/
$route['employee'] = 'employee/dashboard';
$route['employee/dashboard'] = 'employee/dashboard';
$route['employee/kpis'] = 'employee/my_kpis';
$route['employee/kpi/view/(:any)'] = 'employee/view_kpi/$1';
$route['employee/kpi/request-edit/(:any)'] = 'employee/request_edit/$1';
$route['employee/edit-requests'] = 'employee/my_edit_requests';
$route['employee/reviews'] = 'employee/my_reviews';

/*
| -------------------------------------------------------------------------
| Admin Routes
| -------------------------------------------------------------------------
*/
$route['admin'] = 'admin/dashboard';
$route['admin/dashboard'] = 'admin/dashboard';
$route['admin/employees'] = 'admin/employees';
$route['admin/create_employee'] = 'admin/create_employee';
$route['admin/edit_employee'] = 'admin/employees';
$route['admin/edit_employee/(:any)'] = 'admin/edit_employee/$1';
$route['admin/view_employee/(:any)'] = 'admin/view_employee/$1';
$route['admin/deactivate_employee/(:any)'] = 'admin/deactivate_employee/$1';
$route['admin/activate_employee/(:any)'] = 'admin/activate_employee/$1';
$route['admin/manage_roles/(:any)'] = 'admin/manage_roles/$1';
$route['admin/manage_team/(:any)'] = 'admin/manage_team/$1';
$route['admin/departments'] = 'admin/departments';
$route['admin/create_department'] = 'admin/create_department';
$route['admin/edit_department/(:any)'] = 'admin/edit_department/$1';
$route['admin/delete_department/(:any)'] = 'admin/delete_department/$1';
$route['admin/kpi_categories'] = 'admin/kpi_categories';
$route['admin/create_category'] = 'admin/create_category';
$route['admin/edit_category/(:any)'] = 'admin/edit_category/$1';
$route['admin/kpi_templates'] = 'admin/kpi_templates';
$route['admin/create_template'] = 'admin/create_template';
$route['admin/edit_template/(:any)'] = 'admin/edit_template/$1';
$route['admin/delete_template/(:any)'] = 'admin/delete_template/$1';
$route['admin/review_periods'] = 'admin/review_periods';
$route['admin/create_period'] = 'admin/create_period';
$route['admin/edit_period/(:any)'] = 'admin/edit_period/$1';
$route['admin/activate_period/(:any)'] = 'admin/activate_period/$1';
$route['admin/close_period/(:any)'] = 'admin/close_period/$1';
$route['admin/reports'] = 'admin/reports';
$route['admin/audit_logs'] = 'admin/audit_logs';
