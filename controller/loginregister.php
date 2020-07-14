<?php

class loginregisterController{
	protected $mainformControllerClass_constructor,$lang,$filePath_contructor,$usersModel_constructor,$loginModel_constructor,$langDecode_constructor;
	public function __construct($mainformControllerClass_constructor,$filePath_contructor,$usersModel_constructor,$loginModel_constructor,$langDecode_constructor,$lang="") {
        	$this->mainformControllerClass= $mainformControllerClass_constructor;
		$this->lang = $lang;
		$this->filePath = $filePath_contructor;
		$this->usersModel = $usersModel_constructor;
		$this->loginModel = $loginModel_constructor;
		$this->langDecode = $langDecode_constructor;
    	}
	public function createPage($options=array()){
		/**
		* Shows Entrance Page of application-Both Login/Register
		* $options{
		*	connectedServer: The server information that the user is connected to, if available
		* }
		* return html of Login form
		*/
		$connectedServer = "";
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
		$template_parameters = array(
			"connectedServer"=>$connectedServer,
			"Your registration is successful. However, your account needs to be activatd by administrator before you will be able to login"=>$this->langDecode->decode("Your registration is successful. However, your account needs to be activatd by administrator before you will be able to login","TXT_MSG",$this->lang),
			"Success"=>$this->langDecode->decode("Success","TXT_MSG",$this->lang),
			"Cancel"=>$this->langDecode->decode("Cancel","TXT_MSG",$this->lang),
			"Register"=>$this->langDecode->decode("Register","TXT_MSG",$this->lang),
			"Mr"=>$this->langDecode->decode("Mr","TXT_MSG",$this->lang),
			"Ms"=>$this->langDecode->decode("Ms","TXT_MSG",$this->lang),
			"First Name"=>$this->langDecode->decode("First Name","TXT_MSG",$this->lang),
			"Last Name"=>$this->langDecode->decode("Last Name","TXT_MSG",$this->lang),
			"Password"=>$this->langDecode->decode("Password","TXT_MSG",$this->lang),
			"Email"=>$this->langDecode->decode("Email","TXT_MSG",$this->lang),
			"Username"=>$this->langDecode->decode("Username","TXT_MSG",$this->lang),
			"Register"=>$this->langDecode->decode("Register","TXT_MSG",$this->lang),
			"Login"=>$this->langDecode->decode("Login","TXT_MSG",$this->lang),
			"Login using password or Fingerprint"=>$this->langDecode->decode("Login using password or Fingerprint","TXT_MSG",$this->lang),
			"Register new account"=>$this->langDecode->decode("Register new account","TXT_MSG",$this->lang),
			"Forgot password"=>$this->langDecode->decode("Forgot password","TXT_MSG",$this->lang),
			"Your login is successful"=>$this->langDecode->decode("Your login is successful","TXT_MSG",$this->lang)
		);

		$file = $this->filePath->getPath("view/".$this->lang."/loginregister.tpl",1);
		$loginRegisterPageContent = $this->filePath->showHTML($file,$template_parameters);
		return $loginRegisterPageContent;
	}
	public function registerUser($username,$password,$options=array()){
		/**
			* Register new User
			* $username:
			* $password
			* $options {
			*	offlineUserId: id of this user if created offline
			*	offlineInsertTime: Time that this entry happened if client was offile
			*	insertBy_userId: The users who created this userId
			*	firstName: First name of this user
			*	lastName: Last name of this user
			*	gender: gender of user.0:none,1:male,2:female
			*   hashedPinCode: hashed Pincode
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	userId: userId
			*	msg: message decoded in user lang
			*	name: first name + last Name
			* }
		*/
		$userId = "";
		$name = "";
		
		$offlineInsertTime = "";
		$insertBy_userId = "";
		$offlineUserId = "";
		$insertBy_userId = "";
		$firstName = "";
		$lastName = "";
		$gender = 0;
		
		foreach($options as $optionsKey=>$optionsElement){
	            ${$optionsKey} = $optionsElement;
	        }
	        $userId_options= array(
	        	"offlineInsertTime"=>$offlineInsertTime,
	        	"insertBy_userId"=>$insertBy_userId,
	        	"offlineUserId"=>$offlineUserId
	        );
	        $searchForUser = $this->loginModel->getUserIdByUsername($username);
	        
	        if(isset($searchForUser[0]['userId'])){
	        	$status = 0;
	        	$msgCode = "ERR_MSG";
	        	$msgText = "This username is already registered";
	        	$msg = $this->langDecode->decode($msgText,$msgCode,$this->lang);
	        }else{
	        
			$addUser_array = $this->usersModel->addUsersId($userId_options);
			
			if($addUser_array['status']){
				$userId = $addUser_array['userId'];
				if($userId){
					$userDetails_options = array(
						"offlineInsertTime"=>$offlineInsertTime,
						"insertBy_userId"=>$insertBy_userId,
						"firstName"=>$firstName,
						"lastName"=>$lastName,
						"gender"=>$gender
					);
					$addUserDetails_array = $this->usersModel->addUsersDetail($userId,$userDetails_options);
					if($addUserDetails_array['status']){
						$addUserStatus_array = $this->usersModel->addUserStatus($userId,0,1,1,1);
						if($addUserStatus_array['status']){
						///TO DO: ADD USER LOGIN PASSWORD
							$addPassword_options = array(
								"userId"=>$userId,
								"username"=>$username,
								"password"=>$password
							);
							$addPassword_array = $this->loginModel->setUsernamePassword($addPassword_options);
							if($addPassword_array['status']){
								$status = 1;
								$msgText = "User Added successfully";
								$msgCode = "OK_MSG";
								$msg = $this->langDecode->decode($msgText,$msgCode,$this->lang);
								$name = $firstName." ".$lastName;
							}else{
								$status = 0;
								$msg = $addPassword_array['msg'];
							}
						}else{
							$status = 0;
							$msg = $addUserStatus_array['msg'];
						}
					}else{
						$status = 0;
						$msg = $addUserDetails_array['msg'];
					}
					
				}
			}else{
				$status = 0;
				$msg = $addUser_array['msg'];
				$userId = '';
			}
		}
		return array("status"=>$status,"msg"=>$msg,"userId"=>$userId,"name"=>$name);
	}
	public function changePassword($userId, $hashedToken, $username, $password='', $hashedPinCode='', $insertTimeIntervalMinute=10, $debug=0){
		$status = 0;
		$msg = "";
		$tokenSearch = $this->loginModel->searchToken($userId, $hashedToken, $insertTimeIntervalMinute, $debug);
		if($tokenSearch['status']){
			if(count($tokenSearch['rows'])){
				$foundToken = $tokenSearch['rows'][0];
				if(($foundToken['status'] == 1) && ($foundToken['hashedToken'] == $hashedToken)){
					if($password){
						$options = array(
							"username"=>$username,
							"password"=>$password,
							"userId"=>$userId
						);
						$changeResult = $this->loginModel->setUsernamePassword($options);
					}
					if($hashedPinCode){
						$options = array(
							"hashedPinCode"=>$hashedPinCode,
							"userId"=>$userId
						);
						$changeResult = $this->loginModel->addUsersPinCode($options);
					}
					$msg = $changeResult['msg'];
					$status = $changeResult['status'];
				}else{
					$msg = "Token Expired or not found";
				}
			}else{
				$msg = "Token not found";
			}
		}
		$result = array("status"=>$status, "msg"=>$msg);
		return $result;
	}
}

?>