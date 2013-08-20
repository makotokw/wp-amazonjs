<?php
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-Type: text/javascript; charset=UTF-8');
ob_start();

require_once dirname(__FILE__) . '/../../../wp-config.php';
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
$buffer = ob_get_contents();
ob_end_clean();
if (!empty($buffer)) {
	if (is_array($result)) {
		$result['ob'] = $buffer;
	}
}
ob_start();
$jsonResponse = json_encode($result);
ob_end_clean();
echo $jsonResponse;
?>