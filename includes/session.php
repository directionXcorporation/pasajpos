<?php

class SecureSessionHandler extends SessionHandler {

    protected $server, $name, $secure, $domain, $path, $cookie, $limit;

    public function __construct($server = array(), $name = 'PASAJPOS', $secure = TRUE, $domain = '', $path = '/', $limit = 0, $cookie = array()){
    	$this->name = $name;
    	$this->server = $server['SERVER_NAME'];
    	$this->secure = $secure;
    	$this->domain = $domain;
    	$this->path = $path;
    	$this->limit = $limit;
    	$this->cookie = $cookie;
    }

    public function start()
    {
      // Set the cookie name before we start.
      session_name($this->name . '_Session');

      // Set the domain to default to the current domain.
      $domain = ($this->domain!=='') ? ($this->domain) : $this->server['SERVER_NAME'];

      // Set the default secure value to whether the site is being accessed with SSL
      $https = isset($secure) ? $secure : isset($this->server['HTTPS']);

      // Set the cookie settings and start the session
      session_set_cookie_params($this->limit, $this->path, $this->domain, $this->secure, true);
      session_start();
      // Make sure the session hasn't expired, and destroy it if it has
	if($this->validateSession())
	{
		// Check to see if the session is new or a hijacking attempt
		if(!$this->preventHijacking())
		{
			// Reset session data and regenerate id
			$_SESSION = array();
			$_SESSION['IPaddress'] = $_SERVER['REMOTE_ADDR'];
			$_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
			session_commit();
			$this->regenerateSession();
		}
	}else{
		$_SESSION = array();
		session_destroy();
		session_start();
	}
    }
    private function validateSession(){
	if( isset($_SESSION['OBSOLETE']) && !isset($_SESSION['EXPIRES']) )
		return false;

	if(isset($_SESSION['EXPIRES']) && $_SESSION['EXPIRES'] < time())
		return false;

	return true;
}
    private function regenerateSession()
{
	// If this session is obsolete it means there already is a new id
	if(isset($_SESSION['OBSOLETE']) || $_SESSION['OBSOLETE'])
		return;

	// Set current session to expire in 10 seconds
	$_SESSION['OBSOLETE'] = true;
	$_SESSION['EXPIRES'] = time() + 100;

	// Create new session without destroying the old one
	session_regenerate_id(false);

	// Grab current session ID and close both sessions to allow other scripts to use them
	$newSession = session_id();
	session_write_close();
	session_commit();
	// Set session ID to the new one, and start it back up again
	session_id($newSession);
	session_start();

	// Now we unset the obsolete and expiration values for the session we want to keep
	unset($_SESSION['OBSOLETE']);
	unset($_SESSION['EXPIRES']);
}
	
	private function preventHijacking()
{
	if(!isset($_SESSION['IPaddress']) || !isset($_SESSION['userAgent'])){
		return false;
	}
	if ($_SESSION['IPaddress'] != $_SERVER['REMOTE_ADDR']){
		return false;
	}

	if( $_SESSION['userAgent'] != $_SERVER['HTTP_USER_AGENT']){
		return false;
	}

	return true;
}
    public function forget()
    {
        if (session_id() === '') {
            return false;
        }

        $_SESSION = array();

        setcookie(
            $this->name,
            '',
            time() - 42000,
            $this->cookie['path'],
            $this->cookie['domain'],
            $this->cookie['secure'],
            $this->cookie['httponly']
        );
        session_destroy();
        //$this->regenerateSession();
        //session_destroy();
        return true;
    }

    public function get($name)
    {
        $result = $_SESSION[$name];

        return $result;
    }

    public function put($name, $value)
    {
        $_SESSION[$name] = $value;
        return true;
    }
    public function isValid($time='')
    {
        return $this->validateSession();
    }

}
/*
$session = new SecureSessionHandler('cheese');

ini_set('session.save_handler', 'files');
session_set_save_handler($session, true);
session_save_path(__DIR__ . '/sessions');

$session->start();

if ( ! $session->isValid(5)) {
    $session->destroy();
}

$session->put('hello.world', 'bonjour');

echo $session->get('hello.world'); // bonjour
*/
?>