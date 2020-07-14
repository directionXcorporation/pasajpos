<?php
class loginClass{
	protected $db_constructor, $filePath_constructor, 
		$langDecode_constructor, $SecureSessionHandler_constructor, $lang;
	public function __construct($db_constructor,$filePath_constructor,$langDecode_constructor,$SecureSessionHandler_constructor,$lang="") {
	        $this->db = $db_constructor;
	        $this->langDecode = $langDecode_constructor;
	        $this->filePath = $filePath_constructor;
	        $this->SecureSessionHandler = $SecureSessionHandler_constructor;
		$this->lang = $lang;
	    }
    public function sec_session_start() {
        /**
         * Starts a session securly keeping the lang parameter of the session $_SESSION['lang'] for visitor's selected language code
        */
            
        if(!$this->SecureSessionHandler->get('user_id')){
            if($this->SecureSessionHandler->get('lang')){
                $selectedLang = $this->SecureSessionHandler->get('lang');
            }
            $this->SecureSessionHandler->start();
            if ( ! $this->SecureSessionHandler->isValid(5)) {
                $this->SecureSessionHandler->forget();
            }
            $this->SecureSessionHandler->put('lang', $selectedLang);
        }
    }
    
    public function DestroySession($debug=''){
        /**
         * Destroys a session securly keeping the lang parameter of the session $_SESSION['lang'] for visitor's selected language code
        */
        if ($this->SecureSessionHandler->get('lang')){
            $selectedLang = $this->SecureSessionHandler->get('lang');
        }
        $this->SecureSessionHandler->forget();
        $this->SecureSessionHandler->start();
        if ( ! $this->SecureSessionHandler->isValid(5)) {
            $this->SecureSessionHandler->forget();
        }
        $this->SecureSessionHandler->put('lang', $selectedLang);
    }
    
    public function generateSecureId($tableName="",$offlineId=""){
    	if($offlineId && $offlineId!=""){
    		return $offlineId;
    	}else{
			if (function_exists('com_create_guid') === true){
				return trim(com_create_guid(), '{}');
			}
			$data = openssl_random_pseudo_bytes(16);
			$data[6] = chr(ord($data[6]) & 0x0f | 0x40);
			$data[8] = chr(ord($data[8]) & 0x3f | 0x80);
			return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
		}
    }
    
    private function AddLoginAttempt($userId,$status,$loginType,$options=array()){
        $offlineInsertTime = "";
        $sessionId = session_id();
        
        $id = $this->generateSecureId("loginAttempt");
        foreach($options as $optionsKey=>$optionsElement){
            ${$optionsKey} = $optionsElement;
        }
        $col = array(
            "id",
            "sessionId",
            "activeSession",
            "loginType",
            "userId",
            "userIp"
        );
        $val = array(
            "id"=>$id,
            "sessionId"=>$sessionId,
            "activeSession"=>$status,
            "loginType"=>$loginType,
            "userId"=>$userId,
            "userIp"=>$this->filePath->getUserIp()
        );
        $type = array(
            "id"=>"s",
            "sessionId"=>"s",
            "activeSession"=>"i",
            "loginType"=>"i",
            "userId"=>"s",
            "userIp"=>"s"
        );
        if($offlineInsertTime){
        	array_push($col,"offlineInsertTime");
        	$val['offlineInsertTime'] = $offlineInsertTime;
        	$type['offlineInsertTime'] = "s";
        }
        $result = $this->db->pdoInsert("loginAttempts",$col,$val,$type);
        return array("id"=>$id,"status"=>$result['status']);
    }
    public function addUsersPinCode($options=array()){
		/**
			* add User details to the database
			* $userId: Created uder id
			* $options {
			*	hashedPinCode
			*	insertBy_userId: The users who created this userId
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	userId: userId
			*	id: id of this record
			*	msg: message decoded in user lang
			* }
		*/
		$hashedPinCode = "";
        
	        foreach($options as $optionsKey=>$optionsElement){
	            ${$optionsKey} = $optionsElement;
	        }
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		
        $col = array(
        	"pinCode",
        	"userId",
        	"insertBy_userId",
		    "insertIp"
        );
        $val = array(
        	"pinCode"=>$hashedPinCode,
    		"userId"=>$userId,
    		"insertBy_userId"=>$insertBy_userId,
    		"insertIp"=>$this->filePath->getUserIp()
        );
        $type = array(
        	"pinCode"=>"s",
    		"userId"=>"s",
    		"insertBy_userId"=>"s",
    		"insertIp"=>"s"
        );

        $result = $this->db->pdoInsert("usersPin",$col,$val,$type);
        if(isset($result['status'])){
            if($result['status']>0){
                $status = 1;
                $msgCode = "user pin updated successfuly";
                $msgCat = "OK_MSG";
                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
            }else{
                $status = 0;
                $msgCode = "There was a system error updating user pin. Please try again. Administrator is informed of the problem.";
                $msgCat="SYS_ERR_MSG";
                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
            }
        }else{
            $status = 0;
            $msgCode = "There was a system error updating user pin. Please try again. Administrator is informed of the problem.";
            $msgCat="SYS_ERR_MSG";
            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
        }
        return array("userId"=>$userId,"status"=>$status,"id"=>"","msg"=>$msg);
   }
    public function passwordLogin($username, $password,$options=array()) {
        /**
         * Login using username and password
         * $username: username of the user
         * $password:  sha512 hashed of the password entered by user
         * returns {
            * msg:msg text in the visitor language,
            * status{0:unsucessful,1:sucessful},
            * userId{0:unsucessful,int>0:userId of user loggedin}
         * }
         * Options {
            * numberOfAcceptableAttempts: Maximum number a user can login unsuccessfuly in a 2 hour period before being locked out, default to 10
         * }
        */
        $numberOfAcceptableAttempts=10;
        $userId = 0;
        
        foreach($options as $optionsKey=>$optionsElement){
            ${$optionsKey} = $optionsElement;
        }
        
        //LOG the user in ifthe username and password are correct
        $sql = "SELECT usersPassword.userId, usersPassword.password, usersPassword.salt 
                FROM usersPassword 
                FORCE INDEX(username) 
                WHERE usersPassword.username = :username 
                ORDER BY usersPassword.onlineInsertTime DESC 
                LIMIT 0,1";
        $vals = array(
            ":username"=>$username
        );
        $types = array(
            ":username"=>"s"
        );
        $results = $this->db->pdoSelect($sql,$vals,$types);
     	if($results['status']){
	        if(isset($results['rows'][0]['userId'])) { // If the user exist
	            $salt = $results['rows'][0]['salt'];
	            $userId = $results['rows'][0]['userId'];
	            $dbPassword = $results['rows'][0]['password'];
	            
	            $userStatus = $this->getUserActiveStatus($userId);
	            if( $userStatus['status'] ){
	                if($userStatus['passwordLoginStatus']){
	                    $password = hash('sha512', $password.$salt); // hash the password with the unique salt.
	                     // We check if the account is locked from too many login attempts
	                     $checkBruteAttack = $this->CheckBruteAttack($userId,array("numberOfAcceptableAttempts"=>$numberOfAcceptableAttempts));
	                     if($checkBruteAttack['status']) {
	                        $this->AddLoginAttempt($userId,0,1);
	                        $status = 0;
	                        $msg = $checkBruteAttack['msg'];
	                     } else {
	                        if(password_verify($password,$dbPassword)) { // Check if the password in the database matches the password the user submitted. 
	                        // Password is correct!
	                           $ipAddress = $_SERVER['REMOTE_ADDR']; // Get the IP address of the user. 
	                           $userBrowser = $_SERVER['HTTP_USER_AGENT']; // Get the user-agent string of the user.
	                           $userId = $this->filePath->toSafeString($userId); // XSS protection as we might print this value 
	                           // Login successful.
	                           
	                           if($this->logout($userId,159)){
	                           
	                            $loginAdded = $this->AddLoginAttempt($userId,1,1);
	                            if(isset($loginAdded['status'])){
	                                if($loginAdded['status']){
	                                    $this->SecureSessionHandler->put('user_id', $userId);
	                                    $this->SecureSessionHandler->put('login_string', password_hash(hash('sha512', $userId.$dbPassword.$ipAddress.$userBrowser.$username."VENESFEOON"), PASSWORD_DEFAULT));
	                                    $status = 1;
	                                    $msg = $this->langDecode->decode("Login was successful","OK_MSG",$this->lang);
	                                }else{
	                                    $status = 0;
	                                    $msg = $this->langDecode->decode("Login unsuccessful becasue an error occured while registering your login, administrator is informed-Error Code:ESEC144","SYS_ERR_MSG",$this->lang);
	                                }
	                            }else{
	                                $status = 0;
	                                $msg = $this->langDecode->decode("Login unsuccessful becasue an error occured while registering your login, administrator is informed-Error Code:ESEC148","SYS_ERR_MSG",$this->lang);
	                            }
	                           }else{
	                            $status = 0;
	                            $msg = $this->langDecode->decode("Login unsuccessful because userid is changed or not found","ERR_MSG",$this->lang);
	                           }
	                     } else {
	                        // Password is not correct
	                        // We record this attempt in the database
	                        $this->AddLoginAttempt($userId,0,1);
	                        $status = 0;
	                        $msg = $this->langDecode->decode("Wrong Password","ERR_MSG",$this->lang);
	                     }
	                  }
	              }else{
	                $this->AddLoginAttempt($userId,0,1);
	                $status = 0;
	                $msg = $this->langDecode->decode("Password login for the user is disabled by administrator","ERR_MSG",$this->lang);
	              }
	          }else{
	            $this->AddLoginAttempt($userId,0,1);
	            $status = 0;
	            $msg = $this->langDecode->decode("User is disabled by administrator","ERR_MSG",$this->lang);
	          }
	        } else {
	             $status = 0;
	             $msg = $this->langDecode->decode("Wrong Username","ERR_MSG",$this->lang);
	        }
        }else{
        	$status = 0;
        	$msg = $results['msg'];
        }
        return array("status"=>$status,"msg"=>$msg,"userId"=>$userId);
    }
    
    private function CheckBruteAttack($userId,$options=array()) {
        /**
         * Check if brute attack is happening
         * $userId: user id of the user trying to login
         * returns {
            * msg:msg text in the visitor language,
            * status{0:no attack,1:attack happening}
         * }
         * Options {
            * numberOfAcceptableAttempts: Maximum number a user can login unsuccessfuly in a 2 hour period before being locked out, default to 10
         * }
        */
        $numberOfAcceptableAttempts=10;
        
        $msg = '';
        foreach($options as $optionsKey=>$optionsElement){
            ${$optionsKey} = $optionsElement;
        }
       // All login attempts are counted from the past 2 hours. 
        $sql = "SELECT COUNT(activeSession) AS count FROM loginAttempts 
                FORCE INDEX(userId_activeSession) 
                WHERE userId = :userId AND activeSession=0 AND onlineInsertTime GREATERTHAN DATE_SUB(NOW(), INTERVAL 2 HOUR) 
                LIMIT 0,11";
        $vals = array(
            ":userId"=>$userId
        );
        $types = array(
            ":userId"=>"s"
        );
        $results = $this->db->pdoSelect($sql,$vals,$types);
        if($results['status']){
	        if (isset($results['rows'][0]['count'])) { 
	            if($results['rows'][0]['count']>$numberOfAcceptableAttempts){
	                $status = 1;
	                $msgCode = "You have too many unsuccessful logins, please wait for 1 hour and try again or contact sIT support";
	                $msgCat = "ERR_MSG";
	            }else{
	                $status = 0;
	                $msgCode = "";
	                $msgCat = "OK_MSG";
	            }
	        }else{
	            $status = 1;
	            $msgCode = "There was a server error while validating your login, administrator is informed-ERR_CODE:ESEC146";
	            $msgCat = "SYS_ERR_MSG";
	        }
        }else{
        	$status = 0;
        	$msg = $results['msg'];
        }
        if(!$msg){
            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
        }
        return array("status"=>$status,"msg"=>$msg);
    }

    public function loginCheck() {
        /**
         * Checks if the visitor is logged in or not
         * no parameters
         * returns {
            * msg:msg text in the visitor language,
            * status{0:unsucessful,1:sucessful},
            * userId{0:unsucessful,int>0:userId of user loggedin}
         * }
        */
        $msg = '';
        $userId = '';
        // Check if all session variables are set
        if($this->SecureSessionHandler->get('user_id') && $this->SecureSessionHandler->get('login_string')) {
            $userId = $this->SecureSessionHandler->get('user_id');
            $loginString = $this->SecureSessionHandler->get('login_string');
            $ipAddress = $_SERVER['REMOTE_ADDR']; // Get the IP address of the user. 
            $userBrowser = $_SERVER['HTTP_USER_AGENT']; // Get the user-agent string of the user.
         
            $sql = "SELECT password AS password,username AS username FROM usersPassword 
                    FORCE INDEX(userId) 
                    WHERE userId = :userId  
                    ORDER BY onlineInsertTime DESC 
                    LIMIT 0,1";
            $vals = array(
                ":userId" => $userId
            );
            $types = array(
                ":userId" => "s"
            );
            $results = $this->db->pdoSelect($sql,$vals,$types);
            if($results['status']){
	            if(isset($results['rows'][0]['password'])) { // If the user exists
	                $userStatus = $this->getUserActiveStatus($userId);
	                if( $userStatus['status'] ){
	                    $loginCheck = hash('sha512', $userId.$results['rows'][0]['password'].$ipAddress.$userBrowser.$results['rows'][0]['username']."VENESFEOON");
	                    if(password_verify ($loginCheck, $loginString)) {
	                        // Logged In!!!!
	                        $sql = "SELECT loginAttempts1.`sessionId` AS sessionId FROM loginAttempts loginAttempts1 
	                                LEFT JOIN loginAttempts loginAttempts2 ON loginAttempts1.userId=loginAttempts2.userId AND loginAttempts2.activeSession=2 AND loginAttempts1.onlineInsertTime LESSTHAN loginAttempts2.onlineInsertTime 
	                                WHERE loginAttempts1.userId=:userId AND loginAttempts1.activeSession=1 
	                                AND loginAttempts2.activeSession IS NULL 
	                                ORDER BY loginAttempts1.onlineInsertTime DESC 
	                                LIMIT 0,1";
	                        $vals = array(
	                            ":userId"=>$userId
	                        );
	                        $types = array(
	                            ":userId"=>"s"
	                        );
	                        $results = $this->db->pdoSelect($sql,$vals,$types);
	                        if($results['status']){
		                        if (isset($results['rows'][0]['sessionId'])) {
		                            if($results['rows'][0]['sessionId']==session_id()){
		                                $status = 1;
		                                $msgCode = "You are logged in";
		                                $msgCat="OK_MSG";
		                                
		                            }else{
		                                $this->DestroySession(314);
		                                $status = 0;
		                                $msgCode = "You are logged out because session does not match seerver";
		                                $msgCat="ERR_MSG";
		                            }
		                        }else{
		                            $this->DestroySession(321);
		                            $status = 0;
		                            $msgCode = "You are logged out because session data is not valid";
		                            $msgCat="ERR_MSG";
		                        }
	                        }else{
			        	$status = 0;
			        	$msgCode = $results['msg'];
			        	$msgCat = "SYS_ERR_MSG";
			        }
	                   } else {
	                        $this->DestroySession(327);
	                        $status = 0;
	                        $msgCode = "You are logged out because your ip is changed";
	                        $msgCat="ERR_MSG";
	                   }
	               }else{
	                    // User disabled
	                    $this->DestroySession(334);
	                    $status = 0;
	                    $msgCode = "You are logged out becasue user is blocked";
	                    $msgCat="ERR_MSG";
	               }
	            }else {
	                // Not logged in
	                $this->DestroySession(341);
	                $status = 0;
	                $msgCode = "You are logged out becasue username is changed";
	                $msgCat="ERR_MSG";
	            }
            }else{
        	$status = 0;
        	$msgCode = $results['msg'];
        	$msgCat = "SYS_ERR_MSG";
            }
        }else {
            $status = 0;
            $msgCode = "You are not logged in";
            $msgCat="ERR_MSG";
        }
        if(!$msg){
            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
        }
        return array("status"=>$status,"msg"=>$msg,"userId"=>$userId);
    }
    
    public function logout($userId="",$debug=''){
        /**
         * Closes all active sessions that the user has and logs him out of anywhere he is logged in
         * $userId: id of the user or 0 for currently loggedin user
         * returns{
            * true
            * false
         * }
        */
        $id = 0;

        if($userId==""){
            $userId = $this->SecureSessionHandler->get('user_id');
        }
        if($userId){
            $result = $this->AddLoginAttempt($userId,2,0);
        }
        $this->DestroySession($debug);
        if(isset($result['status'])){
            return $result['status'];
        }else if(!$userId){
            return true;
        }else{
            return false;
        }
    }
    
    public function setUsernamePassword($options=array()){
        /**
         * sets for first time or changes username, password for a userId provided or already loggedin userId
         * no parameters
         * returns status{0,1}, msg: message text in the visitor language
         * Options {
            * userId: set for action on this userId or 0 to change the username and password of an already logedin userId,
            * username: to set a new username or change the username for userId or empty to not change
            * password: to set a new password or change the password for userId or empty to not change
            * insertBy_userId: userId who changed this user login
         * }
        */
        $userId = 0;
        $password = "";
        $username = "";
        $insertBy_userId = "";
        
        $msg = "";
        foreach($options as $optionsKey=>$optionsElement){
            ${$optionsKey} = $optionsElement;
        }

        if( $password || $username || $userId ){
        	$loginCheck = $this->loginCheck();
            if(!$userId){
                if($loginCheck['status']){
                    $userId = $loginCheck['userId'];
                }else{
                    $status = 0;
                    $msg = $loginCheck['msg'];
                }
            }
            if(!$insertBy_userId){
                if($loginCheck['status']){
                    $insertBy_userId = $loginCheck['userId'];
                }else{
                    $status = 0;
                    $msg = $loginCheck['msg'];
                }
            }
            if($userId){
                if($password){
                    $salt = $this->getToken(10);
                    $hashedPassword = hash('sha512', $password.$salt);
                    $doubleHashedPassword = password_hash($hashedPassword, PASSWORD_DEFAULT);
                    if(!$username){
                        $username = $this->getUsername($userId);
                    }
                    if($username){
                        $cols = array(
                            "userId",
                            "username",
                            "password",
                            "salt",
                            "insertIp"
                        );
                        $vals = array(
                            "userId"=>$userId,
                            "username"=>$username,
                            "password"=>$doubleHashedPassword,
                            "salt"=>$salt,
                            "insertIp"=>$this->filePath->getUserIp()
                        );
                        $types = array(
                            "userId"=>"s",
                            "username"=>"s",
                            "password"=>"s",
                            "salt"=>"s",
                            "insertIp"=>"s"
                        );
                        $result = $this->db->pdoInsert("usersPassword",$cols,$vals,$types,0);
                        if(isset($result['status'])){
                            if($result['status']){
                                $status = 1;
                                $msgCode = "user login detail updated successfuly";
                                $msgCat = "OK_MSG";
                                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
                            }else{
                                $status = 0;
                                $msgCode = "X547: There was a system error updating your details. Please try again. Administrator is informed of the problem.".$result['msg'];
                                $msgCat="SYS_ERR_MSG";
                                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
                            }
                        }else{
                            $status = 0;
                            $msgCode = "X554: There was a system error updating your details. Please try again. Administrator is informed of the problem.";
                            $msgCat="SYS_ERR_MSG";
                            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
                        }
                    }else{
                        $status = 0;
                        $msgCode = "X560: There was a system error fetching your details. Please try again. Administrator is informed of the problem.";
                        $msgCat="SYS_ERR_MSG";
                        $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
                    }
                }else{
                    $status = 0;
                    $msgCode = "Password is required to perform this action";
                    $msgCat="ERR_MSG";
                    $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
                }
            }else{
                $status = 0;
                $msgCode = "There was a system error verifying your identity. Please try again. Administrator is informed of the proble.";
                $msgCat="SYS_ERR_MSG";
                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
            }
        }else{
            $status = 1;
            $msg = $this->langDecode->decode("No changes to make","OK_MSG",$this->lang);
        }
        return array("status"=>$status,"msg"=>$msg);
    }
    
    private function crypto_rand_secure($min, $max) {
        $range = $max - $min;
        if ($range < 1) return $min; // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd > $range);
        return $min + $rnd;
    }
    
    private function getToken($length) {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet.= "0123456789";
        $max = strlen($codeAlphabet); // edited
    
        for ($i=0; $i < $length; $i++) {
            $token .= $codeAlphabet[$this->crypto_rand_secure(0, $max-1)];
        }
    
        return $token;
    }
    public function getUserActiveStatus($userId){
        /**
         * Get user active status from usersLoginStatus and usersDetail
         * $userId: id of the user
         * return{
            * status{0:disabled,1:active}
         * }
        */
        $status = 0;
        $passwordLoginStatus = 0;
        $fingerprintLoginStatus = 0;
        $pinLoginStatus = 0;
        $startDate = "0000-00-00";
        $endDate = "0000-00-00";

        $sql = "SELECT usersDetail.userId AS `userId`,
                usersDetail.status AS `status`,
                usersLoginStatus.passwordLoginStatus AS `passwordLoginStatus`, 
                usersLoginStatus.fingerprintLoginStatus AS `fingerprintLoginStatus`,
                usersLoginStatus.pinLoginStatus AS `pinLoginStatus`,
                usersLoginStatus.startDate AS `startDate`,
                usersLoginStatus.endDate AS `endDate`
                
                FROM usersLoginStatus 
                LEFT JOIN usersLoginStatus AS usersLoginStatus2 ON usersLoginStatus.userId = usersLoginStatus2.userId AND usersLoginStatus2.onlineInsertTime > usersLoginStatus.onlineInsertTime 
                
                INNER JOIN usersDetail ON usersLoginStatus.userId = usersDetail.userId 
                LEFT JOIN usersDetail AS usersDetail2 ON usersDetail.userId = usersDetail2.userId AND usersDetail2.onlineInsertTime > usersDetail.onlineInsertTime 
                
                WHERE usersDetail2.userId IS NULL AND usersLoginStatus2.userId IS NULL 
                AND usersLoginStatus.userId=:userId  
                ORDER BY usersLoginStatus.onlineInsertTime DESC 
                LIMIT 0,1";
        $vals = array(
            ":userId"=>$userId
        );
        $types = array(
            ":userId"=>"s"
        );
        $result = $this->db->pdoSelect($sql,$vals,$types);
        
        if($result['status']){
	        if(isset($result['rows'][0]['userId'])){
	            $userStatus = $result['rows'][0];
	            if($userStatus['status'] && ($userStatus['startDate']>=date("Y-m-d") || $userStatus['startDate']=="0000-00-00") && ($userStatus['endDate']<=date("Y-m-d")  || $userStatus['endDate']=="0000-00-00")){
    	            $status = 1;
    	            $passwordLoginStatus = $result['rows'][0]['passwordLoginStatus'];
    	            $fingerprintLoginStatus = $result['rows'][0]['fingerprintLoginStatus'];
    	            $pinLoginStatus = $result['rows'][0]['pinLoginStatus'];
    	        }
	            $startDate = $result['rows'][0]['startDate'];
	            $endDate = $result['rows'][0]['endDate'];
	        }
        }
        return array("status"=>$status,"passwordLoginStatus"=>$passwordLoginStatus,"fingerprintLoginStatus"=>$fingerprintLoginStatus,"pinLoginStatus"=>$pinLoginStatus,"startDate"=>$startDate,"endDate"=>$endDate);
    }
    
    private function getUsername($userId){
        /**
         * Get username from usersPassword
         * $userId: id of the user
         * return{
            * username
         * }
        */
        $sql = "SELECT username 
                FROM usersPassword 
                WHERE userId=:userId 
                ORDER BY inssertTime DESC 
                LIMIT 0,1";
        $vals = array(
            ":userId"=>$userId
        );
        $types = array(
            ":userId"=>"s"
        );
        $result = $this->db->pdoSelect($sql,$vals,$types);
        if($result['status']){
	        if(isset($result['rows'][0]['ussername'])){
	            $username = $result['rows'][0]['ussername'];
	        }else{
	            $username = "";
	        }
        }else{
        	$username = "";
        }
        return $username;
    }
    
    public function setFingerprint($options=array()){
        /**
         * sets for first time or changes fingerprint for a userId provided or already loggedin userId
         * no parameters
         * returns status{0,1}, msg: message text in the visitor language
         * Options {
            * userId: set for action on this userId or 0 to change the username and password of an already logedin userId,
            * fingerprint: to set a new username or change the username for userId or empty to not change
            * thisTakeNumber: Is this ralated to a previous take or zero to create new one. Default: 0
         * }
        */

        $thisTakeNumber = 0;
        $userId = 0;
        $fingerprint = "";
        $msg = "";
        foreach($options as $optionsKey=>$optionsElement){
            ${$optionsKey} = $optionsElement;
        }

        if( $fingerprint ){
            if(!$userId){
                $loginCheck = $this->loginCheck();
                if($loginCheck['status']){
                    $userId = $loginCheck['userId'];
                }else{
                    $status = 0;
                    $msg = $loginCheck['msg'];
                }
            }
            if($userId){
                if($fingerprint){
                    $salt = $this->getToken(10);
                    $hashedFingerprint = hash('sha512', $fingerprint.$salt);
                    if(!$newTakeNumber){
                        $lastTakeNumber = $this->getLastTakeNumber($userId);
                    }
                    if($lastTakeNumber==0){
                        $thisTakeNumber = 1;
                    }else{
                        $thisTakeNumber = int($lastTakeNumber) + 1;
                    }
                    if($thisTakeNumber>255){
                        $cols = array(
                            "userId",
                            "takeNumber",
                            "figerprint",
                            "insertIp",
                            "salt"
                        );
                        $vals = array(
                            "userId"=>$userId,
                            "takeNumber"=>$newTakeNumber,
                            "figerprint"=>$hashedFingerprint,
                            "insertIp"=>$this->filePath->getUserIp(),
                            "salt"=>$salt
                        );
                        $types = array(
                            "userId"=>"s",
                            "takeNumber"=>"i",
                            "figerprint"=>"s",
                            "insertIp"=>"s",
                            "salt"=>"s"
                        );
                        $result = $this->db->pdoInsert("usersFingerprint",$cols,$vals,$types,0);
                        if(isset($result['status'])){
                            if($result['status']){
                                $status = 1;
                                $msgCode = "user fingerprint details updated successfuly";
                                $msgCat = "OK_MSG";
                                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
                            }else{
                                $status = 0;
                                $msgCode = "There was a system error updating your details. Please try again. Administrator is informed of the proble.";
                                $msgCat="SYS_ERR_MSG";
                                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
                            }
                        }else{
                            $status = 0;
                            $msgCode = "There was a system error updating your details. Please try again. Administrator is informed of the proble.";
                            $msgCat="SYS_ERR_MSG";
                            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
                        }
                    }else{
                        $status = 0;
                        $msgCode = "You have reached maximum number of fingerprint takes. Please try again. Administrator is informed of the proble.";
                        $msgCat="SYS_ERR_MSG";
                        $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
                    }
                }else{
                    $status = 0;
                    $msgCode = "Fingerprint is required to perform this action";
                    $msgCat="ERR_MSG";
                    $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
                }
            }else{
                $status = 0;
                $msgCode = "There was a system error verifying your identity. Please try again. Administrator is informed of the proble.";
                $msgCat="SYS_ERR_MSG";
                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
            }
        }else{
            $status = 1;
            $msg = $this->langDecode->decode("No changes to make","OK_MSG",$this->lang);
        }
        return array("status"=>$status,"msg"=>$msg);
    }
    
    private function getLastTakeNumber($userId){
        $takeNumber = 0;
        $sql = "SELECT takeNumber FROM usersFingerprint WHERE userId=:userId
                ORDER BY onlineInsertTime DESC LIMIT 0,1";
        $vals = array(
            ":userId"=>$userId
        );
        $types = array(
            ":userId"=>"s"
        );
        $result = $this->db->pdoSelect($sql,$vals,$types);
        if($result['status']){
	        if(isset($result['rows'][0]['takeNumber'])){
	            $takeNumber = $result['rows'][0]['takeNumber'];
	        }
        }else{
        	$takeNumber = "";
        }
        return $takeNumber;
    }
    
    public function fingerprintLogin($fingerprint,$options=array()) {
        /**
         * Login using fingerprint
         * $fingerprint:  sha512 hashed of the fingerprint scanned from user
         * returns {
            * msg:msg text in the visitor language,
            * status{0:unsucessful,1:sucessful},
            * userId{0:unsucessful,int>0:userId of user loggedin}
         * }
         * Options {
            * numberOfAcceptableAttempts: Maximum number a user can login unsuccessfuly in a 2 hour period before being locked out, default to 10
         * }
        */
        $numberOfAcceptableAttempts=10;
        $userId = 0;
        foreach($options as $optionsKey=>$optionsElement){
            ${$optionsKey} = $optionsElement;
        }
        //LOG the user in ifthe username and password are correct
        $sql = "SELECT userId,takeNumber,fingerprint,salt  
                FROM usersFingerprint  
                FORCE INDEX(fingerprint_takeNumber) 
                WHERE fingerprint = :fingerprint 
                ORDER BY takeNumber DESC 
                LIMIT 0,1";
        $vals = array(
            ":fingerprint"=>$fingerprint
        );
        $types = array(
            ":fingerprint"=>"s"
        );
        $results = $this->db->pdoSelect($sql,$vals,$types);
     	if($results['status']){
	        if(isset($results['rows'][0]['userId'])) { // If the user exists
	            $salt = $results['rows'][0]['salt'];
	            $userId = $results['rows'][0]['userId'];
	            $dbFingerprint = $results['rows'][0]['fingerprint'];
	            $userStatus = $this->getUserActiveStatus($userId);
	            $lastTakeNumber = $this->getLastTakeNumber($userId);
	            if($userStatus['status']){
	                if($userStatus['fingerprintLoginStatus']){
	                    $fingerprint = hash('sha512', $fingerprint.$salt); // hash the fingerprint with the unique salt.
	                     // We check if the account is locked from too many login attempts
	                     $checkBruteAttack = $this->CheckBruteAttack($userId,array("numberOfAcceptableAttempts"=>$numberOfAcceptableAttempts));
	                     if($checkBruteAttack['status']) {
	                        $this->AddLoginAttempt($userId,0,1);
	                        $status = 0;
	                        $msg = $checkBruteAttack['msg'];
	                     } else {
	                        if($fingerprint==$dbFingerprint) { // Check if the fingerprint in the database matches the password the user submitted. 
	                        // fingerprint is correct!
	             
	                           $ipAddress = $_SERVER['REMOTE_ADDR']; // Get the IP address of the user. 
	                           $userBrowser = $_SERVER['HTTP_USER_AGENT']; // Get the user-agent string of the user.
	                           $userId = $this->filePath->toSafeString($userId); // XSS protection as we might print this value 
	                           // Login successful.
	                           if($this->logout($userId,757)){
	                            $loginAdded = $this->AddLoginAttempt($userId,1,2);
	                            if(isset($loginAdded['status'])){
	                                if($loginAdded['status']){
	                                    $loginDetails = $this->getLoginDetails($userId);
	                                    if(isset($loginDetails[0]['password'])){
	                                        $dbPassword = $loginDetails[0]['password'];
	                                        $this->SecureSessionHandler->put('user_id', $userId);
	                                        $this->SecureSessionHandler->put('login_string', password_hash(hash('sha512', $userId.$dbPassword.$ipAddress.$userBrowser.$username."VENESFEOON"), PASSWORD_DEFAULT));
	                                        $status = 1;
	                                        $msg = $this->langDecode->decode("Login was successful","OK_MSG",$this->lang);
	                                    }else{
	                                        $status = 0;
	                                        $msg = $this->langDecode->decode("Login unsuccessfull because user is not registered with a username and password","ERR_MSG",$this->lang);
	                                    }
	                                }else{
	                                    $status = 0;
	                                    $msg = $this->langDecode->decode("Login unsuccessful becasue an error occured while registering your login, administrator is informed-Error Code:ESEC144","SYS_ERR_MSG",$this->lang);
	                                }
	                            }else{
	                                $status = 0;
	                                $msg = $this->langDecode->decode("Login unsuccessful becasue an error occured while registering your login, administrator is informed-Error Code:ESEC148","SYS_ERR_MSG",$this->lang);
	                            }
	                           }else{
	                            $status = 0;
	                            $msg = $this->langDecode->decode("Login unsuccessful because userid is changed or not found","ERR_MSG",$this->lang);
	                           }
	                     } else {
	                        // Password is not correct
	                        // We record this attempt in the database
	                        $this->AddLoginAttempt($userId,0,1);
	                        $status = 0;
	                        $msg = $this->langDecode->decode("Wrong Password","ERR_MSG",$this->lang);
	                     }
	                  }
	              }else{
	                $this->AddLoginAttempt($userId,0,1);
	                $status = 0;
	                $msg = $this->langDecode->decode("Fingerprint login for the user is disabled by administrator","ERR_MSG",$this->lang);
	              }
	          }else{
	            $this->AddLoginAttempt($userId,0,1);
	            $status = 0;
	            $msg = $this->langDecode->decode("User is disabled by administrator","ERR_MSG",$this->lang);
	          }
	        } else {
	             $status = 0;
	             $msg = $this->langDecode->decode("Fingerprint not found","ERR_MSG",$this->lang);
	        }
        }else {
             $status = 0;
             $msg = $results['msg'];
        }
        return array("status"=>$status,"msg"=>$msg,"userId"=>$userId);
    }
    
    private function getLoginDetails($userId){
        $sql = "SELECT password FROM usersPassword 
                WHERE userId=:userId 
                ORDER BY onlineInsertTime DESC 
                LIMIT 0,1";
        $vals = array(
            ":userId"=>$userId
        );
        $types = array(
            ":userId"=>"s"
        );
        $result = $this->db->pdoSelect($sql,$vals,$types);
        if($results['status']){
        	$results = $results['rows'];
        }else{
        	$results = array();
        }
        return $result;
    }
    public function getUserIdByUsername($username){
    
    	$sql = "SELECT userId FROM usersPassword WHERE username = :username ORDER BY onlineInsertTime DESC LIMIT 0,1";
    	$vals = array(
            ":username"=>$username
        );
        $types = array(
            ":username"=>"s"
        );
        $result = $this->db->pdoSelect($sql,$vals,$types);
        if($result['status']){
        	$result = $result['rows'];
        }else{
        	$result = array();
        }
        return $result;
    }
	public function generateToken($username, $len=4){
		$binhash = md5($username, true);
		$numhash = unpack('N2', $binhash);
		$hash = $numhash[1] . $numhash[2];
		if($len && is_int($len)) {
			$hash = substr($hash, 0, $len);
		}
		$token = $hash."".random_int(1000000, 9999999);
		return $token;
	}
	public function saveToken($userId, $token, $status=0){
		
		$result = array("status"=>0, "msg"=>"User not found");
		if($userId){
			$cols = array(
				"userId",
				"token",
				"status",
				"insertIp"
			);
			$vals = array(
				"userId"=>$userId,
				"token"=>$token,
				"status"=>$status,
				"insertIp"=>$this->filePath->getUserIp()
			);
			$types = array(
				"userId"=>"s",
				"token"=>"s",
				"status"=>"i",
				"insertIp"=>"s"
			);
			$result = $this->db->pdoInsert("recoverPasswords",$cols,$vals,$types,0);
		}
		return $result;
	}
	public function searchToken($userId, $hashedToken, $insertTimeIntervalMinute=10, $debug=0){
		$sql = "SELECT recoverPasswords.userId AS `userId`, 
						UPPER(CONCAT('*',SHA2(MD5(SHA2(recoverPasswords.token, 512)), 512))) AS `hashedToken`,
						recoverPasswords.status AS `status`
				FROM recoverPasswords 
				LEFT JOIN recoverPasswords AS recoverPasswords2 ON 
					recoverPasswords2.userId = recoverPasswords.userId 
					AND recoverPasswords2.onlineInsertTime > recoverPasswords.onlineInsertTime
				WHERE 
					recoverPasswords2.userId IS NULL
					AND recoverPasswords.userId = :userId 
					AND DATE_FORMAT(recoverPasswords.onlineInsertTime, '%Y-%m-%d %H:%i:%s') > DATE_FORMAT(DATE_SUB(NOW(), INTERVAL :insertTimeIntervalMinute MINUTE), '%Y-%m-%d %H:%i:%s')
				
				GROUP BY recoverPasswords.userId
				ORDER BY recoverPasswords.onlineInsertTime
				LIMIT 0,1
		";
		$vals = array(
            ":userId"=>$userId,
			":insertTimeIntervalMinute"=>$insertTimeIntervalMinute
        );
        $types = array(
            ":userId"=>"s",
			":insertTimeIntervalMinute"=>"i"
        );
        $result = $this->db->pdoSelect($sql,$vals,$types);
		if($debug){
			$result['sql'] = $sql;
			$result['vals'] = $vals;
		}
		return $result;
	}
}
?>