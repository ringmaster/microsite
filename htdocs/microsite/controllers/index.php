<?php

class IndexController
{

	function __construct($path)
	{
		$this->db = new DB(Config::get('connect_string'));
		// Auth::required();
	}

	function index($path)
	{
		$path = implode('/', $path);

		$page = $this->db->row('SELECT * FROM pages p WHERE p.url = :url', array('url' => $path));
		
		$view = null;

		switch($_SERVER['REQUEST_METHOD']) {
			case 'POST':
				if(!$page) {
					$this->db->query('INSERT INTO pages (url) VALUES (:url)', array('url' => $path));
					$page = $this->db->row('SELECT * FROM pages p WHERE p.id = :page_id', array('page_id' => $this->db->lastInsertId()));
				}

				$this->db->query('DELETE FROM components WHERE page_id = :page_id', array('page_id' => $page->id));
				
				foreach($_POST['vars'] as $k => $v) {
					if(in_array($k, array('path'))) {
						continue;
					}
					if($v != '') {
						if(get_magic_quotes_gpc()) {
							$v = stripslashes($v);
						}
						$this->db->query("INSERT INTO components (page_id, name, type, value) VALUES (:page_id, :name, '', :value);", array('page_id' => $page->id, 'name' => $k, 'value' => $v));
					}
				}
				if($_POST['newfield'] != '' && $_POST['newvalue'] != '') {
					$this->db->query("INSERT INTO components (page_id, name, type, value) VALUES (:page_id, :name, '', :value);", array('page_id' => $page->id, 'name' => $_POST['newfield'], 'value' => $_POST['newvalue']));
				}
				if($_POST['vars']['path'] != $page->url) {
					$this->db->query('UPDATE pages SET url = :url WHERE id = :page_id', array('url' => $_POST['path'], $page->id));
				}

				header('location: ' . $_SERVER['REQUEST_URI']);
				
				break;
			case 'GET':
				$components = $this->db->assoc('SELECT c.name, c.value FROM components c WHERE c.page_id = :page_id ORDER BY c.name ASC', array('page_id' => $page->id));
				$v = new View($components);
				$v->path = $path;
		
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

}
?>