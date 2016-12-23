<?php

// Error Reporting
error_reporting(E_ALL);

// Check PHP Version
if (version_compare(phpversion(), '5.3.0', '<') == true) {
  exit('PHP 5.3+ Required');
}

// Set Local Time Zone
date_default_timezone_set('America/Los_Angeles');

if (!isset($_SERVER['HTTP_HOST'])) {
	$_SERVER['HTTP_HOST'] = getenv('HTTP_HOST');
}

// Check if SSL
if (isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) {
	$_SERVER['HTTPS'] = true;
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
	$_SERVER['HTTPS'] = true;
} else {
	$_SERVER['HTTPS'] = false;
}

// Modification Override
function modification($filename) {
	if (!defined('DIR_CATALOG')) {
		$file = DIR_MODIFICATION . 'app/' . substr($filename, strlen(DIR_APPLICATION));
	} else {
		$file = DIR_MODIFICATION . 'admin/' .  substr($filename, strlen(DIR_APPLICATION));
	}

	if (substr($filename, 0, strlen(DIR_SYSTEM)) == DIR_SYSTEM) {
		$file = DIR_MODIFICATION . 'system/' . substr($filename, strlen(DIR_SYSTEM));
	}
	
	if (file_exists($file)) {
		return $file;
	} else {
		return $filename;
	}
}

// Autoloader
function autoload($class) {
	$file = DIR_SYSTEM . 'library/' . str_replace('\\', '/', strtolower($class)) . '.php';

	if (file_exists($file)) {
		include(modification($file));

		return true;
	} else {
		return false;
	}
}

spl_autoload_register('autoload');
spl_autoload_extensions('.php');

// Engine
require_once modification(DIR_SYSTEM . 'engine/action.php');
require_once modification(DIR_SYSTEM . 'engine/controller.php');
require_once modification(DIR_SYSTEM . 'engine/event.php');
require_once modification(DIR_SYSTEM . 'engine/front.php');
require_once modification(DIR_SYSTEM . 'engine/loader.php');
require_once modification(DIR_SYSTEM . 'engine/model.php');
require_once modification(DIR_SYSTEM . 'engine/registry.php');

// Helper
require_once DIR_SYSTEM . 'helper/json.php';
require_once DIR_SYSTEM . 'helper/utf8.php';
