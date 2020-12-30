<?php
defined('BASEPATH') or exit('No direct script access allowed');

$route['default_controller'] = 'Welcome/index';
$route['404_override'] = '';
$route['translate_uri_dashes'] = false;

$route['store']['post'] = 'FileController/store';
