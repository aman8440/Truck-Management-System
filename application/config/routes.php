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
$route['default_controller'] = 'welcome';

$route['api/trucks']['GET'] = 'truckController/index';
$route['api/trucks/all']['GET'] = 'truckController/get_data';
$route['api/trucks/(:num)']['GET'] = 'truckController/view/$1';
$route['api/trucks/create']['POST'] = 'truckController/create';
$route['api/trucks/update/(:num)']['PUT'] = 'truckController/update/$1';
$route['api/trucks/delete/(:num)']['DELETE'] = 'truckController/delete/$1';
$route['api/trucks/checkTruck']['POST'] = 'truckController/checkUniqueTruck';

$route['api/trailer']['GET'] = 'trailerController/index';
$route['api/trailer/all']['GET'] = 'trailerController/get_data';
$route['api/trailer/(:num)']['GET'] = 'trailerController/view/$1';
$route['api/trailer/create']['POST'] = 'trailerController/create';
$route['api/trailer/update/(:num)']['PUT'] = 'trailerController/update/$1';
$route['api/trailer/delete/(:num)']['DELETE'] = 'trailerController/delete/$1';
$route['api/trailer/checkTrailer']['POST'] = 'trailerController/checkUniqueTrailer';

$route['api/driver']['GET'] = 'driverController/index';
$route['api/driver/all']['GET'] = 'driverController/get_data';
$route['api/driver/(:num)']['GET'] = 'driverController/view/$1';
$route['api/driver/create']['POST'] = 'driverController/create';
$route['api/driver/update/(:num)']['PUT'] = 'driverController/update/$1';
$route['api/driver/delete/(:num)']['DELETE'] = 'driverController/delete/$1';
$route['api/driver/checkEmail']['POST'] = 'driverController/checkUniqueEmail';
$route['api/driver/checkName']['POST'] = 'driverController/checkUniqueName';
$route['api/driver/checkPhone']['POST'] = 'driverController/checkUniquePhone';
$route['api/driver/checkLicense']['POST'] = 'driverController/checkUniqueLicense';

$route['api/dispatcher']['GET'] = 'dispatcherController/index';
$route['api/dispatcher/all']['GET'] = 'dispatcherController/get_data';
$route['api/dispatcher/(:num)']['GET'] = 'dispatcherController/view/$1';
$route['api/dispatcher/create']['POST'] = 'dispatcherController/create';
$route['api/dispatcher/update/(:num)']['PUT'] = 'dispatcherController/update/$1';
$route['api/dispatcher/delete/(:num)']['DELETE'] = 'dispatcherController/delete/$1';
$route['api/dispatcher/checkEmail']['POST'] = 'dispatcherController/checkUniqueEmail';
$route['api/dispatcher/checkName']['POST'] = 'dispatcherController/checkUniqueName';
$route['api/dispatcher/checkPhone']['POST'] = 'dispatcherController/checkUniquePhone';

$route['api/load'] = 'loadController/index';
$route['api/load/(:num)'] = 'loadController/view/$1';
$route['api/load/create'] = 'loadController/create';
$route['api/load/update/(:num)'] = 'loadController/update/$1';
$route['api/load/delete/(:num)'] = 'loadController/delete/$1';

$route['api/login/(:num)'] = 'adminController/view/$1';
$route['api/login'] = 'adminController/create';
$route['api/logout'] = 'adminController/logout';

$route['forgot_password'] = 'adminController/forgot_password';
$route['reset_password'] = 'adminController/reset_password';

$route['api/load-docs'] = 'load_DocController/index';
$route['api/load-docs/create'] = 'load_DocController/create';
$route['api/load-docs/view/(:num)'] = 'load_DocController/view/$1';
$route['api/load-docs/update/(:num)'] = 'load_DocController/update/$1';
$route['api/load-docs/delete/(:num)'] = 'load_DocController/delete/$1';

$route['404_override'] = '';
$route['translate_uri_dashes'] = TRUE;
