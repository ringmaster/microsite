<?php

class AuthController
{
	function __construct($path)
	{
	}
	
	function index($params){
		$components = array(
			'content'=>'<form id="loginform" method="post" action="/auth/login"><label id="user" for="iuser">Username:</label><input id="iuser" type="text" name="user"><label id="pass" for="ipass">Password:</label><input id="ipass" type="password" name="pass"><input id="log_in" type="submit" value="Log In"></form>',
		);
				
		$v = new View($components);
		
		$v->render('login');
	}
	
	function login(){
		$user = $_POST['user'];
		$pass = $_POST['pass'];
		
		if(Auth::authorize($user, $pass)) {
			header('location: /');
			$content = '<div id="login">Success</div><script type="text/javascript">location.href="/";</script>';
			die();
		}
		else {
			if(DB::get()->val("SELECT value FROM options WHERE name = 'Open Registration' AND grouping = 'Registration';")) {
				DB::get()->query('INSERT INTO users (username, password) VALUES (:username, :password)', array('username'=>$user, 'password'=>md5($pass)));
				Auth::authorize($user, $pass);
				header('location: /');
				$content = '<div id="login">Success</div><script type="text/javascript">location.href="/";</script>';
				die();
			}
			else {
				header('location: /auth');
			}
		}
		$v = new View(array('content'=>$content));
		
		$v->render('login');
	}
	
	function logout(){
		Auth::logout();
		header('location: /');
	}
	
}
?>