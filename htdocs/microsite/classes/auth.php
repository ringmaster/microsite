<?php

class Auth
{
	private static $user = null;
	private static $optionsset = false;
	
	static function user_id()
	{
		if(isset(self::$user)) {
			return self::$user->id;
		}
		return 0;
	}
	
	static function user()
	{
		return self::$user;
	}
	
	function loggedin()
	{
		$key = $_COOKIE['sp_key'];
		if(isset($_POST['sp_key_md5'])) {
			self::$user = DB::get()->row('SELECT users.*, sessions.session_key, sessions.pingtime FROM users INNER JOIN sessions ON users.id = sessions.user_id WHERE md5(session_key) = :key', array('key' => $_POST['sp_key_md5']));
		}
		else {
			self::$user = DB::get()->row('SELECT users.*, sessions.session_key, sessions.pingtime FROM users INNER JOIN sessions ON users.id = sessions.user_id WHERE session_key = :key', array('key' => $key));
		}
		if(self::$user) {
			DB::get()->query('UPDATE sessions SET pingtime = NOW() WHERE user_id = :userid', array('userid'=>self::$user->id));
			DB::get()->query('UPDATE users SET lastping = NOW() WHERE id = :userid', array('userid'=>self::$user->id));
		}
		return self::$user->id;
	}
	
	function authorize($user, $pass)
	{
		$params = array('user' => $user, 'pass' => md5($pass));
		$user = DB::get()->row('SELECT * FROM users WHERE username = :user AND password = :pass', $params);
		if($user) {
			$key = DB::get()->val('SELECT session_key FROM sessions WHERE user_id = :userid', array('userid' => $user->id));
			if(is_null($key)) {
				$opts = preg_split('//', 'abcdefghijklmnopqrstuvwxyz0123456789_');
				for($z = 0; $z < 32; $z++) {
					$key .= $opts[array_rand($opts, 1)];
				}
				DB::get()->query('DELETE FROM sessions WHERE pingtime < :old', array('old' => time()-3*24*60*60));
				DB::get()->query('INSERT INTO sessions (user_id, session_key) VALUES (:userid, :key)', array('userid' => $user->id, 'key' => $key));
			}
			else {
				DB::get()->query('UPDATE sessions SET pingtime = :now WHERE user_id = :userid', array('now' => time(), 'userid'=>$user->id));
			}
			setcookie('sp_key', $key, 0, '/');
			self::$user = $user;
			return $user->id;
		}
		return false;
		
	}
	
	function logout(){
		DB::get()->query('DELETE FROM sessions WHERE session_key = :key', array('key' => $_COOKIE['sp_key']));
		setcookie('sp_key', '', time() - 3600);
	}
	
	function required() 
	{
		if(!Auth::loggedin()) {
			header('location: ' . Config::get('Auth/path', '/auth') . '?path=' . Application::$fullpath);
			exit;
		}
	}
	
	function add_user($user, $pass)
	{
		DB::get()->query('INSERT INTO users (username, password) VALUES (:username, :password)', array('username'=>$user, 'password'=>md5($pass)));
	}
}

?>