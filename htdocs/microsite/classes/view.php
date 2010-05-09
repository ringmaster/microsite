<?php

class View
{
	private $vars = array();
	
	public function __construct($vars, $template = null)
	{
		$this->vars = $vars;
		if(isset($template)) {
			$this->render($emplate);
		}
	}
	
	public function __get($name)
	{
		return $this->vars[$name];
	}
	
	public function __set($name, $value)
	{
		$this->vars[$name] = $value;
	}
	
	public static function fragment($fragment)
	{
		$view = new Dom($fragment);
		return $view;
	}
	
	public function render($viewname = '')
	{
		$glob = glob(MICROSITE_PATH . '/microsite/views/*.php');
		$views = array();
		foreach($glob as $view) {
			$views[basename($view, '.php')] = $view;
		}
		$views = Plugin::call('viewlist', $views);

		if($viewname == '') {
			if($this->vars['_view'] != '') {
				$viewname = $this->vars['_view'];
			}
			else {
				$viewname = '404';
			}
		}
		if(isset($views[$viewname])) {
			foreach($this->vars as $k => $v) {
				if($k[0] != '_') $$k = $v;
			}
			$view = $this;
			ob_start();
			include $views[$viewname];
			$out = ob_get_clean();
			$out = new Dom($out);
			return $out;
		}
		else {
			throw(new Exception('View does not exist: ' . $viewname));
		}
	}
}


?>