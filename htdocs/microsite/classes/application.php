<?php

class Application 
{
	static $path;
	static $fullpath;
	
	public static function Start()
	{
		self::set_path();

		$glob = glob(MICROSITE_PATH . '/microsite/controllers/*.php');
		$controllers = array();
		foreach($glob as $controller) {
			$controllers[basename($controller, '.php')] = $controller;
		}

		if(self::$path[0] == '') {
			$controller = 'index';
		}
		else {
			$controller = self::$path[0];
		}

		// Am I logged in?
		if(Config::get('Auth/required', false) && !in_array(self::$fullpath, Config::get('Auth/skip_controller', array()))) {
			Auth::required();
		}

		if(!isset($controllers[$controller])) {
			$controller = Config::get('Controllers/controller_default', 'index');
		}
		if(isset($controllers[$controller])) {
			include $controllers[$controller];
			$class = $controller . 'Controller';
			$obj = new $class(self::$path);
			$method = self::$path[1];
			if(isset(self::$path[1])) {
				if(self::$path[1][0] != '_' && method_exists($obj, $method)) {
					$args = array_slice(self::$path, 2);
					$obj->$method($args);
				}
				else {
					if(method_exists($obj, 'index')) {
						$args = array_slice(self::$path, 1);
						$obj->index($args);
					}
				}
			}
			else{
				if(method_exists($obj, 'index')) {
					$obj->index(self::$path);
				}
			}
		}
		else {
			print_r(self::$path);
		}
	}

	public function set_path()
	{
		if(isset($_GET['p'])) {
			$path = $_GET['p'];
		}
		else {
			$base_url = rtrim(dirname(self::script_name()));

			$start_url = (isset($_SERVER['REQUEST_URI'])
				? $_SERVER['REQUEST_URI']
				: $_SERVER['SCRIPT_NAME'] .
					( isset($_SERVER['PATH_INFO'])
					? $_SERVER['PATH_INFO']
					: '') );
			
			if(strpos($start_url, '?')) {
				list($start_url) = explode('?', $start_url, 2);
			}

			if('/' != $base_url) {
				$start_url = str_replace($base_url, '', $start_url);
			}

			$path = trim($start_url, '/');
		}
	
		self::$fullpath = $path;	
		self::$path = explode('/', $path);
	}
	
	public static function script_name()
	{
		switch (true) {
			case isset($scriptname):
				break;
			case isset($_SERVER['SCRIPT_NAME']):
				$scriptname = $_SERVER['SCRIPT_NAME'];
				break;
			case isset($_SERVER['PHP_SELF']):
				$scriptname = $_SERVER['PHP_SELF'];
				break;
			default:
				throw new exception('Could not determine script name.');
				die();
		}
		return $scriptname;
	}
	
}

?>