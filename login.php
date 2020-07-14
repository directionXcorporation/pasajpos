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
require_once($filepath->getpath('model/dynaPage.php',1));
require_once($filepath->getpath('controller/dynaPage.php',1));
require_once($filepath->getpath('controller/communication.php',1));

$pageCode = 'login';

$db = new pdoDb();


if($SecureSessionHandlerClass->get('lang')){
	$lang = $SecureSessionHandlerClass->get('lang');
}else{
	$lang = "en";
}

$langDecodeClass = new langDecodeClass($db,$filePath,$lang);
$loginClass = new loginClass($db,$filePath,$langDecodeClass,$SecureSessionHandlerClass,$lang);
$templateModelClass = new templateModelClass($db,$langDecodeClass,$lang);
$usersModelClass = new usersModel($db,$filePath,$langDecodeClass,$loginClass ,$lang);
$mainformModelClass = new mainformModelClass($db,$filePath,$langDecodeClass,$loginClass,$lang);
$dynaPageModelClass = new dynaPageModel($db,$filePath,$langDecodeClass,$loginClass ,$lang);


$mainformControllerClass = new mainformControllerClass($langDecodeClass,$lang,$templateModelClass,$filePath, $loginClass, $mainformModelClass, $usersModelClass);
$loginregisterControllerClass = new loginregisterController($mainformControllerClass,$filePath,$usersModelClass,$loginClass,$langDecodeClass,$lang);
$dynaPageControllerClass = new dynaPageController($langDecodeClass,$lang,$usersModelClass,$filePath, $loginClass,$mainformModelClass,$dynaPageModelClass,$mainformControllerClass);
$communicationControllerClass = new emailControllerClass($db, $filePath, $langDecodeClass, $SecureSessionHandlerClass ,$lang);

$templateParameters = array(

);
$options = array(
	"pageLang"=>$lang,
	"loadInAjax"=>true,
	"filePath"=>"",
	"templateParameters"=>$templateParameters
);

if(isset($_POST['action'])){
	$userId = "";
	$userGroups = array();
	if($_POST['action']=="login"){
	    $loginClass->logout();
		$loginArray = $loginClass->passwordLogin($_POST['username'],$_POST['password']);
		$status = $loginArray['status'];
		if($status){
			$userId = $loginArray['userId'];
		}
		$msg = $loginArray['msg'];
	}else if($_POST['action']=="register"){
		$options = array(
			"firstName"=>$_POST['firstname'],
			"lastName"=>$_POST['lastname'],
			"gender"=>$_POST['gender'],
			"hashedPinCode"=>$_POST['hashedPinCode']
		);
		$registerArray = $loginregisterControllerClass->registerUser($_POST['username'],$_POST['password'],$options);
		$msg = $registerArray['msg'];
		$status = $registerArray['status'];
	}else if($_POST['action']=="logout"){
	    if(!$_POST['userId']){
	        $_POST['userId'] = '';
	    }
        $loginClass->logout();
	}else if($_POST['action']=="recoverPassword"){
	    if(!$_POST['userId'] && $_POST['username']){
			$userArray = $loginClass->getUserIdByUsername($_POST['username']);
			if(count($userArray)){
				$token = $loginClass->generateToken($_POST['username'], 4);
				$userId = $userArray[0]['userId'];
				$result = $loginClass->saveToken($userId, $token, 1);
				$status = $result['status'];
				$msg = $result['msg'];
				if($status){
					$emailParameters = array(
						"_TOKEN"=>$token
					);
					$communicationControllerClass->prepareEmail($_POST['username'], "Password reset request", $emailParameters, "token.tpl", 1);
				}
			}else{
				$status = 0;
				$userId = '';
				$msg = "user not found";
			}
		}
	}else if($_POST['action']=="changePassword"){
	    if($_POST['username'] && ($_POST['password'] || $_POST['hashedPinCode']) && $_POST['hashedToken']){
			$userArray = $loginClass->getUserIdByUsername($_POST['username']);
			if(count($userArray)){
				$userIdToChange = $userArray[0]['userId'];
				$result = $loginregisterControllerClass->changePassword($userIdToChange, $_POST['hashedToken'], $_POST['username'], $_POST['password'], $_POST['hashedPinCode'], 10);
				$status = $result['status'];
				$msg = $result['msg'];
				if($status){
					$userId = $userIdToChange;
				}
			}
		}
	}else{
		$status = 0;
		$msg = $langDecodeClass->decode("Invalid Action","ERR_MSG",$lang);
	}
	if($userId){
	    $parameters = array(
	        'usersDetail_userId'=>$userId,
	        'usersDetail_userId_nullcheck'=>$userId,
	        'usersDetail_status'=>1,
	        'usersDetail_status_nullcheck'=>1,
	        'usersLoginStatus_status'=>1,
	        'usersLoginStatus_status_nullcheck'=>1,
	        'groupsStatus_status'=>1,
	        'groupsStatus_status_nullcheck'=>1,
	        'start'=>0,
	        'limit'=>1
	    );
	    $user = array();
	    $userDetails = $dynaPageControllerClass->runServerCommand('usersSearch', $parameters, array('commandIndex'=>0));
	    if($userDetails && $userDetails['usersDetail'] && $userDetails['usersDetail']['result'] && $userDetails['usersDetail']['result']['rows'] && $userDetails['usersDetail']['result']['rows'][0]){
	        $user = $userDetails['usersDetail']['result']['rows'][0];
	    }
	    $groups = array();
	    $usersGroup = $dynaPageControllerClass->runServerCommand('usersSearch', $parameters, array('commandIndex'=>1));
	    if($usersGroup && $usersGroup['usersGroup'] && $usersGroup['usersGroup']['result'] && $usersGroup['usersGroup']['result']['rows'] && $usersGroup['usersGroup']['result']['rows'][0]){
	        $groups = $usersGroup['usersGroup']['result']['rows'];
	    }
	}
	$content = json_encode(array_merge(array("status"=>$status, "msg"=>$msg, "userId"=>$userId, "usersGroup"=>$groups), $user));
}else if(isset($_GET['action'])){
	if($_GET['action']=="checkLoginStatus"){
		$loginArray = $loginClass->loginCheck();
		$content = json_encode(array("status"=>$loginArray['status'],"userId"=>$loginArray['userId']));
	}else if($_GET['action']=="logout"){
	    $loginClass->logout();
	}
}else{
	$loginArray = $loginClass->loginCheck();
	if($loginArray['status'] && $loginArray['userId']){
		$status = 0;
		$msg = $langDecodeClass->decode("You are already logged in and should not be here!","SYS_ERR_MSG",$lang);
		$content = json_encode(array("status"=>$status, "msg"=>$msg));
	}else{
		$content = $loginregisterControllerClass->createPage();
	}
}
print_r($content);
?>