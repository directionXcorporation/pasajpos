<?php
require_once('includes/filepath.inc.php');
$filePath = new filePath();
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
	$_POST = $_REQUEST = json_decode( file_get_contents( 'php://input' ), true );
}
if(isset($_POST)){
    foreach($_POST as $key=>$element){
        $_POST[$key] = $filePath->tosafestring($element);
    }
}
if(isset($_GET)){
    foreach($_GET as $key=>$element){
        $_GET[$key] = $filePath->tosafestring($element);
    }
}
if(isset($_REQUEST)){
    foreach($_REQUEST as $key=>$element){
        $_REQUEST[$key] = $filePath->tosafestring($element);
    }
}
require_once($filePath->getpath('includes/includes.inc.php',1));
require_once($filepath->getpath('controller/users.php',1));

require_once($filepath->getpath('model/smartTicket.php',1));
require_once($filepath->getpath('controller/smartTicket.php',1));

$pageCode = 'smartTicket';

$db = new pdoDb();


if($SecureSessionHandlerClass->get('lang')){
	$lang = $SecureSessionHandlerClass->get('lang');
}else{
	$lang = "en";
}

$langDecodeClass = new langDecodeClass($db,$filePath,$lang);
$loginClass = new loginClass($db,$filePath,$langDecodeClass,$SecureSessionHandlerClass,$lang);
$templateModelClass = new templateModelClass($db,$langDecodeClass,$lang);
$loginClass = new loginClass($db,$filePath,$langDecodeClass,$SecureSessionHandlerClass,$lang);
$usersModelClass = new usersModel($db,$filePath,$langDecodeClass,$loginClass ,$lang);
$mainformModelClass = new mainformModelClass($db,$filePath,$langDecodeClass,$loginClass,$lang);
$smartTicketModelClass = new smartTicketModelClass($db,$filePath,$langDecodeClass,$loginClass,$lang);

$mainformControllerClass = new mainformControllerClass($langDecodeClass,$lang,$templateModelClass,$filePath, $loginClass,$mainformModelClass,$usersModelClass);
$loginregisterControllerClass = new loginregisterController($mainformControllerClass,$filePath,$usersModelClass,$loginClass,$langDecodeClass,$lang);
$usersControllerClass = new usersControllerClass($langDecodeClass,$lang,$usersModelClass,$filePath,$loginClass,$mainformModelClass);
$smartTicketControllerClass = new smartTicketControllerClass($langDecodeClass,$lang,$smartTicketModelClass,$filePath,$loginClass,$mainformModelClass);

$loginArray = $loginClass->loginCheck();
if($loginArray['status'] && $loginArray['userId']){
	$userId = $loginArray['userId'];
}
if($userId){
	$userGroups = $usersModelClass->getUserGroups($userId);
}else{
	$userGroups = array();
}
if(isset($_POST['action'])){
    if(!isset($_POST['debug'])){
        $_POST['debug'] = 0;
    }
	if($_POST['action']=="searchIntro"){
		$result = array(
			"status"=>0,
			"msg"=>"",
			"rows"=>array()
		);
		if($_POST['description']){
		    $result = $smartTicketControllerClass->searchIntro($_POST['description'], $_POST['debug']);
		}
		
		$content = json_encode($result);
	}else if($_POST['action']=="searchHintsByPage"){
		$result = array(
			"status"=>0,
			"msg"=>"",
			"rows"=>array()
		);
		if($_POST['pageCode']){
		    $result = $smartTicketControllerClass->searchStepsByPage($_POST['pageCode'], "hint", $_POST['debug']);
		}
		
		$content = json_encode($result);
	}else if($_POST['action']=="searchStepsByIntro"){
		$result = array(
			"status"=>0,
			"msg"=>"",
			"rows"=>array()
		);
		if($_POST['introIds']){
		    $result = $smartTicketControllerClass->searchStepsByIntro($_POST['introIds'], $_POST['debug']);
		}
		
		$content = json_encode($result);
	}
}
print_r($content);
?>