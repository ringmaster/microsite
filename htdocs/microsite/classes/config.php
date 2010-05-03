<?php

class Config
{
	static $data = false;

	function load($filename = null) {
		if(empty($filename)) {
			$filename = MICROSITE_CONFIG;
		}
		
		self::$data = parse_ini_file($filename);
		
		$constants = array(
			'#{MICROSITE_PATH}#',
			'#{MICROSITE_CONFIG}#',
			'#{MICROSITE_CONFIG_PATH}#',
		);
		$replacements = array(
			MICROSITE_PATH,
			MICROSITE_CONFIG,
			dirname(MICROSITE_CONFIG),
		);
		
		foreach(self::$data as $k => $v) {
			self::$data[$k] = preg_replace($constants, $replacements, $v);
		}
	}
	
	function get($value, $default = null) {
		if(!is_array(self::$data)) {
			self::load();
		}
		if(!isset(self::$data[$value])) {
			return $default;
		}
		return self::$data[$value];
	}

}

?>