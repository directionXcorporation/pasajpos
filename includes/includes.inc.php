<?php
date_default_timezone_set('UTC');
$filepath = new filepath();
require_once($filepath->getpath('includes/pdo.php',1));
require_once($filepath->getpath('includes/session.php',1));
require_once($filepath->getpath('includes/Uuid/autoload.php',1));
require_once($filepath->getpath('includes/rjson.lib.php',1));
$RJson = new RJson();
$SecureSessionHandlerClass = new SecureSessionHandler($_SERVER,'pasaj',TRUE,'pasajpos.com','/',0,$_COOKIE);

$SecureSessionHandlerClass->start();

require_once($filepath->getpath('model/lang.php',1));
require_once($filepath->getpath('model/loginregister.php',1));
require_once($filepath->getpath('model/template.php',1));
require_once($filepath->getpath('model/users.php',1));
require_once($filepath->getpath('model/mainform.php',1));

require_once($filepath->getpath('controller/mainform.php',1));
require_once($filepath->getpath('controller/loginregister.php',1));
//use Phossa2\Uuid\Uuid;
//$uuid = Uuid::get();
//setcookie("uuid",$uuid);

?>