<?php
if (!function_exists('json_encode')) {
	require_once dirname(__FILE__).'/Services_JSON/JSON.php';
	function json_encode($content, $assoc=false)
	{
		$json = new Amazonjs_JSON(($assoc) ? AMAZONJS_JSON_LOOSE_TYPE : 0);
		return $json->encode($content);
	}
}