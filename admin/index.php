<?php

// Load Configuration File
if (is_file('config.php')) {
	require_once 'config.php';
}

// Load Bootstrap File
require_once DIR_SYSTEM . 'bootstrap.php';

// Load Version File
if (is_file(DIR_SYSTEM . 'version.php')) {
	require_once DIR_SYSTEM . 'version.php';
}

// Registry
$registry = new Registry();

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Config
$config = new Config();
$registry->set('config', $config);

// Database(MySQL)
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$registry->set('db', $db);

// Settings
$query = $db->query("SELECT * FROM `" . DB_PREFIX . "setting` WHERE 1 ORDER BY setting_id ASC");
foreach ($query->rows as $result) {
	if (!$result['serialized']) {
		$config->set($result['key'], $result['value']);
	} else {
		$config->set($result['key'], unserialize($result['value']));
	}
}

// Url
$url = new Url(HTTP_SERVER, $config->get('config_secure') ? HTTPS_SERVER : HTTP_SERVER);
$registry->set('url', $url);

// Log
$log = new Log($config->get('config_error_filename'));
$registry->set('log', $log);

function error_handler($errno, $errstr, $errfile, $errline) {
	global $log, $config;

	// error suppressed with @
	if (error_reporting() === 0) {
		return false;
	}

	switch ($errno) {
		case E_NOTICE:
		case E_USER_NOTICE:
			$error = 'Notice';
			break;
		case E_WARNING:
		case E_USER_WARNING:
			$error = 'Warning';
			break;
		case E_ERROR:
		case E_USER_ERROR:
			$error = 'Fatal Error';
			break;
		default:
			$error = 'Unknown';
			break;
	}

	if ($config->get('config_error_display')) {
		echo '<b>' . $error . '</b>: ' . $errstr . ' in <b>' . $errfile . '</b> on line <b>' . $errline . '</b>';
	}

	if ($config->get('config_error_log')) {
		$log->write('PHP ' . $error . ':  ' . $errstr . ' in ' . $errfile . ' on line ' . $errline);
	}

	return true;
}

// Error Handler
set_error_handler('error_handler');

// Request
$request = new Request();
$registry->set('request', $request);

// Tool
$tool = new Tool($registry);
$registry->set('tool', $tool);

// Response
$response = new Response();
$response->addHeader('Content_Type: text/html; charset=utf-8');
$registry->set('response', $response);

// Cache
$cache = new Cache('file');
$registry->set('cache', $cache);

// Session
$session = new Session();
$registry->set('session', $session);

// Language Detection
$languages = array();

$query = $db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE status = '1'");

foreach ($query->rows as $result) {
	$languages[$result['code']] = $result;
}

$detect = '';

if (isset($request->server['HTTP_ACCEPT_LANGUAGE']) && $request->server['HTTP_ACCEPT_LANGUAGE']) {
	$browser_languages = explode(',', $request->server['HTTP_ACCEPT_LANGUAGE']);

	foreach ($browser_languages as $browser_language) {
		foreach ($languages as $key => $value) {
			if ($value['status']) {
				$locale = explode(',', $value['locale']);

				if (in_array($browser_language, $locale)) {
					$detect = $key;

					break 2;
				}
			}
		}
	}
}

if (isset($session->data['admin_language']) && array_key_exists($session->data['admin_language'], $languages) && $languages[$session->data['admin_language']]['status']) {
	$code = $session->data['admin_language'];
} elseif (isset($request->cookie['admin_language']) && array_key_exists($request->cookie['admin_language'], $languages) && $languages[$request->cookie['admin_language']]['status']) {
	$code = $request->cookie['admin_language'];
} elseif ($detect) {
	$code = $detect;
} else {
	$code = $config->get('config_admin_language');
}

if (!isset($session->data['admin_language']) || $session->data['admin_language'] != $code) {
	$session->data['admin_language'] = $code;
}

if (!isset($request->cookie['admin_language']) || $request->cookie['admin_language'] != $code) {
	setcookie('admin_language', $code, time() + 60 * 60 * 24 * 30, '/', $request->server['HTTP_HOST']);
}

$config->set('config_admin_language_id', $languages[$code]['language_id']);
$config->set('config_admin_language', $languages[$code]['code']);

// Language
$language = new Language($languages[$code]['directory']);
$language->load('default');
$registry->set('language', $language);

// Document
$document = new Document();
$registry->set('document', $document);

// User
$registry->set('user', new User($registry));

// Event
$event = new Event($registry);
$registry->set('event', $event);
$query = $db->query("SELECT * FROM `" . DB_PREFIX . "event`");
foreach ($query->rows as $result) {
	$event->register($result['trigger'], $result['action']);
}

// Front Controller
$controller = new Front($registry);

// Login
$controller->addPreAction(new Action('common/login/check'));

// Permission
//$controller->addPreAction(new Action('error/permission/check'));

// Router
if (isset($request->get['route'])) {
	$action = new Action($request->get['route']);
} else {
	$action = new Action('common/dashboard');
}

// Dispatch
$controller->dispatch($action, new Action('error/not_found'));

// Output
$response->output();
