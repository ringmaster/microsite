<?php

class DB extends PDO
{
	static $db = array();
	
	public static function get($index = 'root')
	{
		if(empty(self::$db[$index])) {
			self::$db[$index] = new DB(Config::get('connect_string'));
		}
		return self::$db[$index];
	}
	
	public static function disconnect($index = 'root')
	{
		self::$db[$index] = null;
	}
	
	public static function set($instance, $index = 0)
	{
		if($index === 0) {
			$index = count(self::$db);
		}
		self::$db[$index] = $instance;
	}
	
	public function __construct($connect_string, $username = '', $password = '', $index = 'root')
	{
		try {
			parent::__construct($connect_string, $username, $password);
			$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		}
		catch (PDOException $e) {
			echo 'Connection failed: ' . $e->getMessage();
		}

		DB::set($this, $index);
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
	
	public function results($query, $args = array(), $class_name = 'Model')
	{
		$this->fetch_class = $class_name;
		if ( $this->query( $query, $args ) ) {
			return $this->pdo_statement->fetchAll();
		}
		else {
			return false;
		}
	}
	
	public function row($query, $args = array(), $class_name = 'Model')
	{
		$this->fetch_class = $class_name;

		if ( $this->query( $query, $args ) ) {
			return $this->pdo_statement->fetch();
		}
		else {
			return false;
		}
	}
	
	public function col($query, $args = array())
	{
		if ( $this->query( $query, $args ) ) {
			return $this->pdo_statement->fetchAll(PDO::FETCH_COLUMN);
		}
		else {
			return false;
		}
	}
	
	public function val($query, $args = array())
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
			if(is_string($valuefield)) {
				$this->fetch_class = $valuefield;
				$result = $this->pdo_statement->fetchAll();
			}
			else {
				$result = $this->pdo_statement->fetchAll(PDO::FETCH_NUM);
			}
			$output = array();
			foreach($result as $item) {
				if(is_object($item)) {
					$output[$item->$keyfield] = $item;
				}
				elseif(is_array($item)) {
					$output[$item[$keyfield]] = $item[$valuefield];
				}
			}
			return $output;
		}
		else {
			return false;
		}
	}
	
	public static function inclause($values, $prefix) 
	{
		$out = array();
		$index = 0;
		foreach($values as $value) {
			$index++;
			$out[':' . $prefix . $index] = $value;
		}
		return $out;
	}
}


?>