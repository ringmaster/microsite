<?php

class Controller 
{
	public static function Startup($db) 
	{
		return new Controller($db);
	}
	
	public function __construct($db)
	{
		$this->db = $db;
		$this->set_path();

		$page = $db->row('SELECT * FROM pages p WHERE p.url = :url', array('url' => $this->path));
		
		$view = null;

		switch($_SERVER['REQUEST_METHOD']) {
			case 'POST':
				if(!$page) {
					$db->query('INSERT INTO pages (url) VALUES (:url)', array('url' => $this->path));
					$page = $db->row('SELECT * FROM pages p WHERE p.id = :page_id', array('page_id' => $db->lastInsertId()));
				}

				$db->query('DELETE FROM components WHERE page_id = :page_id', array('page_id' => $page->id));
				
				foreach($_POST['vars'] as $k => $v) {
					if(in_array($k, array('path'))) {
						continue;
					}
					if($v != '') {
						$db->query("INSERT INTO components (page_id, name, type, value) VALUES (:page_id, :name, '', :value);", array('page_id' => $page->id, 'name' => $k, 'value' => $v));
					}
				}
				if($_POST['newfield'] != '' && $_POST['newvalue'] != '') {
					$db->query("INSERT INTO components (page_id, name, type, value) VALUES (:page_id, :name, '', :value);", array('page_id' => $page->id, 'name' => $_POST['newfield'], 'value' => $_POST['newvalue']));
				}
				if($_POST['vars']['path'] != $page->url) {
					$db->query('UPDATE pages SET url = :url WHERE id = :page_id', array('url' => $_POST['path'], $page->id));
				}

				header('location: ' . $_SERVER['REQUEST_URI']);
				
				break;
			case 'GET':
				$components = $db->assoc('SELECT c.name, c.value FROM components c WHERE c.page_id = :page_id', array('page_id' => $page->id));
				$v = new View($components);
				$v->path = $this->path;
		
				if($_SERVER['QUERY_STRING'] != '') {
					switch($_SERVER['QUERY_STRING']) {
						case 'edit':
							$view = 'edit';
							break;
					}
				}

				$v->render($view);
				break;
		}
		
	}
	
	public function set_path()
	{
		if(isset($_GET['p'])) {
			$this->path = $_GET['p'];
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

			$this->path = trim($start_url, '/');
		}
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
		$glob = glob(dirname(__FILE__) . '/views/*.php');
		$views = array();
		foreach($glob as $view) {
			$views[basename($view, '.php')] = $view;
		}

		if($viewname == '') {
			if($this->vars['_view'] != '') {
				$viewname = $this->vars['_view'];
			}
			else {
				$viewname = 'table';
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
}

class DB extends PDO
{
	public function __construct($connect_string)
	{
		list($type, $file)= explode(':', $connect_string, 2);
		if($file == basename($file)) {
			$connect_string = implode(':', array($type, MICROSITE_PATH . '/' . $file));
		}

		parent::__construct($connect_string, '', '');
		$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		$this->exec( 'PRAGMA synchronous = OFF');
	}

	public function query( $query, $args = array() )
	{
		if($this->pdo_statement != NULL) {
			$this->pdo_statement->closeCursor();
		}

		if($this->pdo_statement = $this->prepare($query, array(PDO::ATTR_EMULATE_PREPARES => true))) {
			$this->pdo_statement->setFetchMode( PDO::FETCH_CLASS, $this->fetch_class );

			if(!$this->pdo_statement->execute($args)) {
				throw new exception($this->pdo_statement->errorInfo());
			}
			return true;
		}
		else {
			throw new exception($this->errorInfo());
		}
	}
	
	public function results($query, $args = array(), $class_name = 'StdClass')
	{
		$this->fetch_class = $class_name;
		if ( $this->query( $query, $args ) ) {
			return $this->pdo_statement->fetchAll();
		}
		else {
			return false;
		}
	}
	
	public function row($query, $args = array(), $class_name = 'StdClass')
	{
		$this->fetch_class = $class_name;

		if ( $this->query( $query, $args ) ) {
			return $this->pdo_statement->fetch();
		}
		else {
			return false;
		}
	}
	
	public function col($query, $args)
	{
		if ( $this->query( $query, $args ) ) {
			return $this->pdo_statement->fetchAll(PDO::FETCH_COLUMN);
		}
		else {
			return false;
		}
	}
	
	public function val($query, $args)
	{
		if ( $this->query( $query, $args ) ) {
			$result = $this->pdo_statement->fetch(PDO::FETCH_NUM);
			return $result[0];
		}
		else {
			return false;
		}
	}
	
	public function assoc($query, $args = array(), $keyfield = 0, $valuefield = 1)
	{
		if ( $this->query( $query, $args ) ) {
			$result = $this->pdo_statement->fetchAll(PDO::FETCH_BOTH);
			$output = array();
			foreach($result as $item) {
				$output[$item[$keyfield]] = $item[$valuefield];
			}
			return $output;
		}
		else {
			return false;
		}
	}
}

$db = false;
if(file_exists(MICROSITE_CONFIG)) {
	include MICROSITE_CONFIG;
	$db = new DB($config['connect_string']);
	/*
	$db->exec('CREATE TABLE pages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, url VARCHAR(255) NOT NULL);');
	$db->exec('
CREATE TABLE components (
  id       integer PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE,
  page_id  integer NOT NULL,
  name     varchar(50) NOT NULL,
  type     varchar(50) NOT NULL,
  value    text
);');
	$db->exec('
CREATE INDEX components_page_id
  ON components
  (page_id);');
	$db->exec("INSERT INTO pages (url) VALUES ('');");
	$page_id = $db->lastInsertID();
	$db->query("INSERT INTO components (page_id, name, type, value) VALUES (?,?,?,?);", array($page_id, 'title', '', 'Hello'));
	$db->query("INSERT INTO components (page_id, name, type, value) VALUES (?,?,?,?);", array($page_id, '_view', '', 'template.php'));
	*/
	
}

Controller::startup($db);


?>