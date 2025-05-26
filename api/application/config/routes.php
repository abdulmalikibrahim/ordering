<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'API';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
$route['login'] = 'API/login';
$route['checkLogin'] = 'API/checkLogin';
$route['logout'] = 'API/logout';
$route['get_master'] = 'API/get_master';
$route['upload_master'] = 'API/upload_master';
$route['get_account'] = 'API/get_account';
$route['get_dept'] = 'API/get_dept';
$route['save_account'] = 'API/save_account';
$route['delete_account'] = 'API/delete_account';
$route['reset_password'] = 'API/reset_password';
$route['save_dept'] = 'API/save_dept';
$route['delete_departement'] = 'API/delete_departement';
$route['get_data_so'] = 'API/get_data_so';
$route['upload_parts'] = 'API/upload_parts';
$route['get_shop_code'] = 'API/get_shop_code';
$route['get_pic_shop'] = 'API/get_pic_shop';
$route['get_detail_part_so'] = 'API/get_detail_part_so';
$route['delete_so_number'] = 'API/delete_so_number';
$route['get_data_so_management'] = 'API/get_data_so_management';
$route['approve_so'] = 'API/approve_so';
$route['release_so'] = 'API/release_so';
$route['count_remain_approve'] = 'API/count_remain_approve';
$route['get_data_graph'] = 'API/get_data_graph';
$route['delete_part_so'] = 'API/delete_part_so';
$route['data_part_master'] = 'API/data_part_master';
$route['count_remain_release'] = 'API/count_remain_release';
$route['cancel_approve_so'] = 'API/cancel_approve_so';
$route['send_email'] = 'API/send_email';
$route['export_master'] = 'API/export_master';
$route['export_detail_part'] = 'API/export_detail_part';
$route['get_detail_so'] = 'API/get_detail_so';
$route['print_so'] = 'API/print_so';