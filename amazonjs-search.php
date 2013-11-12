<?php

function amazonjs_shutdown() {
	if (defined('AMAZONJS_CALLED_JSON')) {
		return;
	}
	$error = error_get_last();
	if ($error['type'] === E_ERROR || $error['type'] ===  E_COMPILE_ERROR) {
		echo $error['message'];
	}
}

function json($result) {
	while (ob_get_level() > 0) {
		ob_end_clean();
	}
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-Type: text/javascript; charset=UTF-8');
	$jsonResponse = json_encode($result);
	echo $jsonResponse;
	define('AMAZONJS_CALLED_JSON', 1);
	exit;
}

register_shutdown_function('amazonjs_shutdown');
ob_start();

$absPath = realpath(dirname(__FILE__) . '/../../../');
if (substr($absPath, -1) != '/') {
	$absPath .= '/';
}

define('ABSPATH', $absPath);

if (file_exists(ABSPATH . 'wp-config.php')) {
	require_once(ABSPATH . 'wp-config.php');
} elseif (file_exists(dirname(ABSPATH) . '/wp-config.php') && !file_exists(dirname(ABSPATH) . '/wp-settings.php')) {
	require_once(dirname(ABSPATH) . '/wp-config.php');
} else {
	json(array('message' => "Can't find wp-config.php"));
}

require_once dirname(__FILE__) . '/amazonjs.php';

// from http get
$itemPage = @$_GET['ItemPage'];
$id = @$_GET['ID'];
$keywords = @$_GET['Keywords'];
$searchIndex = @$_GET['SearchIndex'];
$countryCode = @$_GET['CountryCode'];

if (!empty($id)) {
	if (preg_match('/^http?:\/\//', $id)) {
		// parse ItemId from URL
		if (preg_match('/^http?:\/\/.+\.amazon\.([^\/]+).+(\/dp\/|\/gp\/product\/)([^\/]+)/', $id, $matches)) {
			$domain = $matches[1];
			$itemId = $matches[3];
		}
		if (!isset($itemId)) {
			$keywords = $id;
		}
	} else {
		$itemId = $id;
	}
}
$amazonjs = new Amazonjs();
$amazonjs->init();
$result = array();
if (isset($itemId)) {
	$result = $amazonjs->itemlookup($countryCode, $itemId);
} else {
	$result = $amazonjs->itemsearch($countryCode, $searchIndex, $keywords, $itemPage);
}
json($result);


