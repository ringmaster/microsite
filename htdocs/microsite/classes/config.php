<?php

class Config
{
	static $data = false;

	function load($filename = null) {
		if(empty($filename)) {
			$filename = MICROSITE_CONFIG;
		}
		
		self::$data = parse_ini_file($filename, true);
		
		$replacements = array(
			'#{MICROSITE_PATH}#' => MICROSITE_PATH,
			'#{MICROSITE_CONFIG}#' => MICROSITE_CONFIG,
			'#{MICROSITE_CONFIG_PATH}#' => dirname(MICROSITE_CONFIG),
		);
		
		self::$data = self::replace(self::$data, $replacements);
	}
	
	function replace($array, $replacements) {
		$out = array();
		foreach($array as $key => $value) {
			if(is_array($value)) {
				$out[$key] = self::replace($value, $replacements);
			}
			else {
				$out[$key] = preg_replace(array_keys($replacements), array_values($replacements), $value);
			}
		}
		return $out;
	}
	
	function get($value, $default = null) {
		if(!is_array(self::$data)) {
			self::load();
		}
		if(is_scalar($value)) {
			$value = explode('/', $value);
		}
		$data = & self::$data;
		foreach($value as $keys) {
			if(isset($data[$keys])) {
				$data = & $data[$keys];
			}
			else {
				return $default;
			}
		}
		return $data;
	}

}

?>