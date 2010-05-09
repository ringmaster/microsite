<?php

class AuthController
{
	function __construct($path)
	{
	}
	
	function index($params){
		$components = array(
			'content'=>View::fragment('<form id="loginform" method="post" action="/auth/login"><label id="user" for="iuser">Username:</label><input id="iuser" type="text" name="user"><label id="pass" for="ipass">Password:</label><input id="ipass" type="password" name="pass"><input id="log_in" type="submit" value="Log In"></form>'),
			'page_id'=>'loginpage',
		);
		$v = new View($components);
		
		$out = $v->render('template');
		echo $out;
	}
	
	function login(){
		$user = $_POST['user'];
		$pass = $_POST['pass'];
		
		if(Auth::authorize($user, $pass)) {
			header('location: /');
			$content = View::fragment('<div id="login">Success</div><script type="text/javascript">location.href="/";</script>');
			die();
		}
		else {
			if(Config::get('open', false)) {
				Auth::add_user($user, $pass);
				Auth::authorize($user, $pass);
				header('location: /');
				$content = View::fragment('<div id="login">Success</div><script type="text/javascript">location.href="/";</script>');
				die();
			}
			else {
				header('location: ' . Config::get('Auth/path', '/auth') . (isset($_GET['path']) ? '?path=' . $_GET['path'] : ''));
			}
		}
		$v = new View(array('content'=>$content, 'page_id'=>'loginpage'));
		
		echo $v->render('template');
	}
	
	function logout(){
		Auth::logout();
		header('location: /');
	}
	
}
?>