<?php

class View
{
	private $vars = array();
	
	public function __construct($vars)
	{
		$this->vars = $vars;
	}
	
	public function __get($name)
	{
		return $this->vars[$name];
	}
	
	public function __set($name, $value)
	{
		$this->vars[$name] = $value;
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
			include $views[$viewname];
		}
		else {
			echo 'View does not exist: ' . $viewname;
		}
	}
	
	public function capture($viewname = '')
	{
		ob_start();
		$this->render($viewname);
		return ob_get_clean();
	}
}


?>