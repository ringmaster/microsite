<?php

class Plugin
{
	static protected $plugins = array();
	
	public static function extends_plugin( $class )
	{
		$parents = class_parents( $class, false );
		return in_array( 'Plugin', $parents );
	}
	
	public static function get_plugin_classes()
	{
		$classes = get_declared_classes();
		return array_filter( $classes, array( 'Plugin', 'extends_plugin' ) );
	}
	
	public static function call_commands($cmds)
	{
		foreach(self::$plugins as $plugin) {
			if(method_exists($plugin, 'commands')) {
				$cmds = $plugin->commands($cmds);
			}
		}
		return $cmds;
	}
	
	public static function register_pseudo_plugin($obj)
	{
		self::$plugins[get_class($obj)] = $obj;
	}
	
	public static function call()
	{
		$args = func_get_args();
		$cmd = array_shift($args);
		$result = reset($args);
		foreach(self::$plugins as $plugin) {
			if(method_exists($plugin, $cmd)) {
				$result = call_user_func_array(array($plugin, $cmd), $args);
				$args[0] = $result;
			}
		}
		return $result;
	}
	
	public static function __static()
	{
		$pluginfiles = glob(MICROSITE_PATH . '/plugins/*/*.php');
		foreach($pluginfiles as $file) {
			include $file;
		}
		
		$classes = self::get_plugin_classes();
		foreach($classes as $class) {
			self::$plugins[$class] = new $class();
		}
	}
}


?>