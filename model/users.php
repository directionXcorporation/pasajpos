<?php

class usersModel{
	protected $db_constructor, $filePath_constructor, 
		$langDecode_constructor, $loginClass_constructor, $lang;
	public function __construct($db_constructor,$filePath_constructor,$langDecode_constructor,$loginClass_constructor,$lang="") {
	        $this->db = $db_constructor;
	        $this->langDecode = $langDecode_constructor;
	        $this->filePath = $filePath_constructor;
	        $this->loginClass = $loginClass_constructor;
		$this->lang = $lang;
	}
	public function addUsersId($options=array()) {
		/**
			* Creates a unique userId and add it to database
			* Options {
			*	$offlineInsertTime: Time that this entry happened if client was offile
			*	$offlineUserId: userId created while client was offline
			*	$insertBy_userId: The users who created this userId
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	userId: Created userId
			*	msg: message decoded in user lang
			* }
		*/
		$offlineInsertTime = "";
		$userId = "";
		$insertBy_userId = "";
		$offlineUserId = "";
        
        foreach($options as $optionsKey=>$optionsElement){
            ${$optionsKey} = $optionsElement;
        }
		
		if(!$userId){
			$userId = $this->loginClass->generateSecureId("usersId",$offlineUserId);
		}
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		
        $col = array(
            "userId",
            "insertBy_userId",
			"insertIp"
        );
        $val = array(
            "userId"=>$userId,
            "insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
        );
        $type = array(
            "userId"=>"s",
            "insertBy_userId"=>"s",
			"insertIp"=>"s"
        );
        if($offlineInsertTime){
        	array_push($col,"offlineInsertTime");
        	$val['offlineInsertTime'] = $offlineInsertTime;
        	$type['offlineInsertTime'] = "s";
        }
        $result = $this->db->pdoInsert("usersId",$col,$val,$type);
        if(isset($result['status'])){
            if($result['status']>0){
                $status = 1;
                $msgCode = "user id updated successfuly";
                $msgCat = "OK_MSG";
                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
            }else{
                $status = 0;
                $msgCode = "There was a system error updating user id. Please try again. Administrator is informed of the problem.EX74";
                $msgCat="SYS_ERR_MSG";
                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
            }
        }else{
            $status = 0;
            $msgCode = "There was a system error updating user id. Please try again. Administrator is informed of the problem.EX80";
            $msgCat="SYS_ERR_MSG";
            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
        }
        
        return array("userId"=>$userId,"status"=>$status,"msg"=>$msg);
	}
	
	public function addUsersDetail($userId,$options=array()){
		/**
			* add User details to the database
			* $userId: Created uder id
			* $options {
			*	offlineId: id of this record if created offline
			*	offlineInsertTime: Time that this entry happened if client was offile
			*	insertBy_userId: The users who created this userId
			*	firstName: First name of this user
			*	lastName: Last name of this user
			*	gender: gender of user
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	userId: userId
			*	id: id of this record
			*	msg: message decoded in user lang
			* }
		*/
		$offlineInsertTime = "";
		$offlineId = "";
		$insertBy_userId = "";
		$firstName = "";
		$lastName = "";
		$offlineId = "";
		$gender = 0;
        
	        foreach($options as $optionsKey=>$optionsElement){
	            ${$optionsKey} = $optionsElement;
	        }
		
		if(!$offlineId){
			$id = $this->loginClass->generateSecureId("usersDetail",$offlineId);
		}else{
			$id = $offlineId;
		}
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		
        $col = array(
        	"id",
            "userId",
            "firstName",
            "lastName",
            "gender",
            "insertBy_userId",
			"insertIp"
        );
        $val = array(
        	"id"=>$id,
            "userId"=>$userId,
            "firstName"=>$firstName,
            "lastName"=>$lastName,
            "gender"=>$gender,
            "insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
        );
        $type = array(
        	"id"=>"s",
            "userId"=>"s",
            "firstName"=>"s",
            "lastName"=>"s",
            "gender"=>$gender,
            "insertBy_userId"=>"s",
			"insertIp"=>"s"
        );
        if($offlineInsertTime){
        	array_push($col,"offlineInsertTime");
        	$val['offlineInsertTime'] = $offlineInsertTime;
        	$type['offlineInsertTime'] = "s";
        }
        $result = $this->db->pdoInsert("usersDetail",$col,$val,$type);
        if(isset($result['status'])){
            if($result['status']>0){
                $status = 1;
                $msgCode = "user details updated successfuly";
                $msgCat = "OK_MSG";
                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
            }else{
                $status = 0;
                $msgCode = "There was a system error updating user details. Please try again. Administrator is informed of the problem.";
                $msgCat="SYS_ERR_MSG";
                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
            }
        }else{
            $status = 0;
            $msgCode = "There was a system error updating user details. Please try again. Administrator is informed of the problem.";
            $msgCat="SYS_ERR_MSG";
            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
        }
        return array("userId"=>$userId,"status"=>$status,"id"=>$id,"msg"=>$msg);
	}
	
	public function addUserStatus($userId='',$userStatus=0,$passwordLoginStatus=0,$fingerprintLoginStatus=0,$pinLoginStatus=0,$startDate="0000-00-00",$endDate="0000-00-00",$insertBy_userId=''){
	/**
		* Sets users status to usersStatus table
		* $userId: Id of user to set status. Default to current logged in user
		* $status: overall status of user
		* $passwordLoginStatus: is user allowed to login using pasword? default to 0
		* $fingerprintLoginStatus: is user allowed to login using fingerprint? Default to 0
		* $pinLoginStatus: is user allowed to login with pin? Default to 0
		* $startDate: Whe nthe user active status becomes in use. Default to 0000-00-00:no time
		* $endDate: When the user's active status becomes void. Default to 0000-00-00: no time
		* $insertBy_userId: userId who ntered this record
	*/
	if(!$userId || !$insertBy_userId){
		$loginCheck = $this->loginClass->loginCheck();
		if(!$insertBy_userId){
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		if(!$userId){
			if($loginCheck['status']){
				$userId = $loginCheck['userId'];
			}
		}
	}
        $cols = array(
            "userId",
            "status",
            "passwordLoginStatus",
            "fingerprintLoginStatus",
            "pinLoginStatus",
            "startDate",
            "endDate",
            "insertBy_userId"
        );
        $vals = array(
            "userId"=>$userId,
            "status"=>$userStatus,
            "passwordLoginStatus"=>$passwordLoginStatus,
            "fingerprintLoginStatus"=>$fingerprintLoginStatus,
            "pinLoginStatus"=>$pinLoginStatus,
            "startDate"=>$startDate,
            "endDate"=>$endDate,
            "insertBy_userId"=>$insertBy_userId
        );
        $types = array(
            "userId"=>"s",
            "status"=>"i",
            "passwordLoginStatus"=>"i",
            "fingerprintLoginStatus"=>"i",
            "pinLoginStatus"=>"i",
            "startDate"=>"s",
            "endDate"=>"s",
            "insertBy_userId"=>"s"
        );
        $result = $this->db->pdoInsert("usersStatus",$cols,$vals,$types,0);
        if(isset($result['status'])){
            if($result['status']){
                $status = 1;
                $msgCode = "user status updated successfuly";
                $msgCat = "OK_MSG";
                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
            }else{
                $status = 0;
                $msgCode = "There was a system error updating status. Please try again. Administrator is informed of the problem.";
                $msgCat="SYS_ERR_MSG";
                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
            }
        }else{
            $status = 0;
            $msgCode = "There was a system error updating status. Please try again. Administrator is informed of the problem.";
            $msgCat="SYS_ERR_MSG";
            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
        }
        return array("status"=>$status,"msg"=>$msg,"id"=>$id);
    }
    public function addUsersAreaCode($areaCodeId,$userId,$options=array()){
    	/**
			* add User AreaCode to the database
			* $areaCodeId:areaCode of this user
			* $userId: userId
			* $options {
			*	$offlineInsertTime: id of this record if created offline
			*	$insertBy_userId: The users who created this userId
			*	$offlineTakeNumber: takeNumber of this round if created offline
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	msg: message decoded in user lang
			* }
		*/
		$offlineInsertTime = "";
		$insertBy_userId = "";
		$offlineTakeNumber = "";
        
        foreach($options as $optionsKey=>$optionsElement){
            ${$optionsKey} = $optionsElement;
        }
		
		if(!$offlineTakeNumber){
			$takeNumber = $this->loginClass->generateSecureId("usersAreaCode",$offlineId);
		}else{
			$takeNumber = $offlineTakeNumber;
		}
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		
        $col = array(
        	"areaCode_id",
        	"takeNumber",
            "userId",
            "insertBy_userId",
			"insertIp"
        );
        $val = array(
        	"areaCode_id"=>$areaCode_id,
        	"takeNumber"=>$takeNumber,
            "userId"=>$userId,
            "insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
        );
        $type = array(
        	"areaCode_id"=>"i",
        	"takeNumber"=>"s",
            "userId"=>"s",
            "insertBy_userId"=>"s",
			"insertIp"=>"s"
        );
        if($offlineInsertTime){
        	array_push($col,"offlineInsertTime");
        	$val['offlineInsertTime'] = $offlineInsertTime;
        	$type['offlineInsertTime'] = "s";
        }
        $result = $this->db->pdoInsert("usersAreaCode",$col,$val,$type);
        if(isset($result['status'])){
            if($result['status']>0){
                $status = 1;
                $msgCode = "user details updated successfuly";
                $msgCat = "OK_MSG";
                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
            }else{
                $status = 0;
                $msgCode = "There was a system error updating user areaCode. Please try again. Administrator is informed of the problem.";
                $msgCat="SYS_ERR_MSG";
                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
            }
        }else{
            $status = 0;
            $msgCode = "There was a system error updating user areaCode. Please try again. Administrator is informed of the problem.";
            $msgCat="SYS_ERR_MSG";
            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
        }
        return array("status"=>$status,"msg"=>$msg);
    }
	
    public function searchUsersDetail($options=array()){
    	/**
			* Search among UsersDetail table items
			* $options {
			*	(array)userId: {an array of user ids}
			*	(array)firstName: {an array of the first names}
			*	(array)lastName: {an array of the last name}
			*	(array)insertBy_userId: {an array of the user id of the inserter}
			*	offlineInsertTime_start: search records with offlineInsertTime greater than or equal to this. format: 2017-08-21
			*	offlineInsertTime_end: search records with offlineInsertTime less than this. format: 2017-08-21
			*	limit: limit number of results default to 20
			*	start: start pointer of results to fetch. Default to 0
			*	getallusers: if 1 it gets the users which does not have a group
			*	(array)searchLoginDetails: {
			*		isActive: search in login password and usernames.default to 0
			*	}
			*	(array)searchStatusDetails: {
			*		isActive: search in user status.default to 1
			*	}
			*	orderBy: order by. Default to usersDetail.offlineInsertTime DESC, usersPassword.id DESC
			*	(array)searchUsersAreaCode: {
			*		usersAreaCode_active: inner join results with UsersAreaCode to get and search in the usersAreaCode as well. Default:false to improve speed if not neccessary,
			*		(array)(only used if active is true)areaCode_id: {array of id of the area code to search for users in that}
			*		(array)(only used if active is true)areaCode_userId: {array of Users ids of users in the area code. Default to userId in options}
			*		(array)(only used if active is true)areaCode_insertBy_userId: {array of user ids to search for inserter in areaCode. Default to empty}
			* }
			*	(array)searchAreaCodes: {
			*		areaCodes_active: inner join results with areaCodes to get and search in the areaCodes as well. Default:false to improve speed if not neccessary,
			*	}
			*	(array)groupsId: {array('groupId'=>),...}
			* returns(array){
			*	status: 0:failed, 1: success
			*	id
			*	userId
			*	firstName
			*	lastName
			*	insertBy_userId
			*	insertIp
			*	onlineInsertTime
			*	offlineInsertTime
			*	(array)usersAreaCode{ Only if active, else empty
			*		userId
			*		areaCode_id
			*	}
			*	(array)areaCodes{ Only if active, else empty
			*		areaName
			*		parentId
			*	}
			* }
		*/
		$searchLoginDetails = array('isActive'=>0);
		$searchStatusDetails = array('isActive'=>1);
		$userId = array();
		$firstName = array();
		$lastName = array();
		$insertBy_userId = array();
		$offlineInsertTime_start = "";
		$offlineInsertTime_end = "";
		$searchLoginDetails['isActive'] = 1;
		$searchStatusDetails['isActive'] = 1;
		$limit = 20;
		$start = 0;
		$orderBy = " usersDetail.offlineInsertTime DESC ";
		$getallusers = 0;

		$groupsId = array();
		
		$vals = array();
		$types = array();
		
		foreach($options as $key=>$element){
			${$key} = $element;
		}
		if(isset($searchLoginDetails['isActive']) && $searchLoginDetails['isActive']){
			$orderBy .= ", usersPassword.id DESC ";
		}
        
        $sql = "SELECT usersDetail.id AS id, usersDetail.userId AS userId, usersDetail.firstName AS firstName, usersDetail.lastName AS lastName, usersDetail.offlineInsertTime 
		AS offlineInsertTime, usersDetail.onlineInsertTime AS onlineInsertTime, usersDetail.insertBy_userId AS insertBy_userId,usersDetail.insertIp AS insertIp,
		usersDetail.gender AS gender ";
	if(isset($searchLoginDetails['isActive']) && $searchLoginDetails['isActive']){
        	$sql .= 	" ,usersPassword.username AS username ";
        }
        if(isset($searchStatusDetails['isActive']) && $searchStatusDetails['isActive']){
        	$sql .=	",usersStatus.status AS userStatus, usersStatus.passwordLoginStatus AS passwordLoginStatus, usersStatus.fingerprintLoginStatus AS fingerprintLoginStatus, usersStatus.pinLoginStatus AS pinLoginStatus, usersStatus.startDate AS statusStartDate, usersStatus.endDate AS statusEndDate ";
        }
        if(!empty(array_filter($groupsId))>0 && !$getallusers){
        	$sql .= ",GROUP_CONCAT(usersGroup.groupId) AS groupsConcat";
        }
        $sql .=		" FROM usersDetail 
        		INNER JOIN usersStatus ON usersStatus.userId = usersDetail.userId 
        		LEFT JOIN usersStatus AS usersStatus2 ON usersStatus2.userId = usersStatus.userId AND usersStatus2.onlineInsertTime GREATERTHAN usersStatus.onlineInsertTime 
        		LEFT JOIN usersDetail AS usersDetail2 ON usersDetail.userId = usersDetail2.userId AND usersDetail2.offlineInsertTime GREATERTHAN usersDetail.offlineInsertTime ";
        		if(isset($searchLoginDetails['isActive']) && $searchLoginDetails['isActive']){
        			$sql .= " LEFT JOIN usersPassword ON usersPassword.userId = usersDetail.userId 
        		LEFT JOIN usersPassword AS usersPassword2 ON usersPassword.userId = usersPassword2.userId AND usersPassword2.onlineInsertTime GREATERTHAN usersPassword.onlineInsertTime ";
        		}

        		if(!empty(array_filter($groupsId))>0){
        			if($getallusers){
        				$sql .= " LEFT JOIN ";
        			}else{
        				$sql .= " INNER JOIN ";
        			}
        			$sql .=" usersGroup ON usersGroup.userId=usersDetail.userId 
        			LEFT JOIN usersGroup AS usersGroup2 ON usersGroup.userId = usersGroup2.userId AND usersGroup.groupId=usersGroup2.groupId AND usersGroup2.onlineInsertTime GREATERTHAN usersGroup.onlineInsertTime ";
        			if($getallusers){
        				$sql .= " LEFT JOIN ";
        			}else{
        				$sql .= " INNER JOIN ";
        			}
        			$sql .=" groupsStatus ON groupsStatus.groupId = usersGroup.groupId 
        			LEFT JOIN groupsStatus AS groupsStatus2 ON groupsStatus2.groupId = groupsStatus.groupId AND groupsStatus2.onlineInsertTime GREATERTHAN groupsStatus.onlineInsertTime ";
        		}
       $sql .= 		" WHERE usersDetail2.id IS NULL AND usersStatus2.id IS NULL ";
       if(isset($searchLoginDetails['isActive']) && $searchLoginDetails['isActive']){
		$sql .= " AND (usersPassword.id IS NULL OR (usersPassword.id IS NOT NULL AND usersPassword2.id IS NULL)) AND usersPassword2.id IS NULL ";
       }
        if(!empty(array_filter($userId))>0){
        	$sql .= " AND (usersDetail.userId = :userId ";
        	$vals[':userId'] = $userId[0]['userId'];
        	$types[':userId'] = "s";
        	foreach($userId as $key=>$element){
        		$sql .= " OR usersDetail.userId = :userId".$key;
        		$vals[':userId'.$key] = $element['userId'];
        		$types[':userId'.$key] = "s";
        	}
        	$sql .= ")";
        }
        if(!empty(array_filter($firstName))>0){
        	$sql .= " AND (usersDetail.firstName = :firstName ";
        	$vals[':firstName'] = $firstName[0];
        	$types[':firstName'] = "s";
        	foreach($firstName as $key=>$element){
        		$sql .= " OR usersDetail.firstName = :firstName".$key;
        		$vals[':firstName'.$key] = $firstName[0];
        		$types[':firstName'.$key] = "s";
        	}
        	$sql .= ")";
        }
        if(!empty(array_filter($lastName))>0){
        	$sql .= " AND (usersDetail.lastName = :lastName ";
        	$vals[':lastName'] = $lastName[0];
        	$types[':lastName'] = "s";
        	foreach($lastName as $key=>$element){
        		$sql .= " OR usersDetail.lastName = :lastName".$key;
        		$vals[':lastName'.$key] = $lastName[0];
        		$types[':lastName'.$key] = "s";
        	}
        	$sql .= ")";
        }
        if(!empty(array_filter($insertBy_userId))>0){
        	$sql .= " AND (usersDetail.insertBy_userId = :insertBy_userId ";
        	$vals[':insertBy_userId'] = $insertBy_userId[0];
        	$types[':insertBy_userId'] = "s";
        	foreach($insertBy_userId as $key=>$element){
        		$sql .= " OR usersDetail.insertBy_userId = :insertBy_userId".$key;
        		$vals[':insertBy_userId'.$key] = $insertBy_userId[0];
        		$types[':insertBy_userId'.$key] = "s";
        	}
        	$sql .= ")";
        }
        if($offlineInsertTime_start){
        	$sql .= " AND (DATE(usersDetail.offlineInsertTime) GREATERTHAN= DATE(:offlineInsertTime_start)) ";
        	$date = date_create($offlineInsertTime_start);
        	$offlineInsertTime_start = date_format($date, 'Y-m-d');
        	$vals[':offlineInsertTime_start'] = $offlineInsertTime_start;
        	$types[':offlineInsertTime_start'] = "s";	
        }
        if($offlineInsertTime_end){
        	$sql .= " AND (DATE(usersDetail.offlineInsertTime) LESSTHAN= DATE(:offlineInsertTime_end)) ";
        	$date = date_create($offlineInsertTime_end);
        	$offlineInsertTime_end = date_format($date, 'Y-m-d');
        	$vals[':offlineInsertTime_end'] = $offlineInsertTime_end;
        	$types[':offlineInsertTime_end'] = "s";	
        }
        if(!empty(array_filter($groupsId))>0){
        	if($getallusers){
        		$getAllUsersSql = " OR groupsStatus.id IS NULL ";
        		$getAllUsersSql2 = " OR usersGroup.id IS NULL ";
        	}else{
        		$getAllUsersSql = "";
        		$getAllUsersSql2 = "";
        	}
        	$sql .= " AND groupsStatus2.id IS NULL AND usersGroup2.id IS NULL ";
        	$sql .=" AND (usersGroup.groupId = :groupId ";
        	$vals[":groupId"] = $groupsId[0]['groupId'];
		$types[":groupId"] = "s";
        	foreach($groupsId as $groupsIdKey => $groupsIdElement){
        		$sql .= " OR usersGroup.groupId = :groupId".$groupsIdKey;
        		$vals[":groupId".$groupsIdKey] = $groupsIdElement['groupId'];
			$types[":groupId".$groupsIdKey] = "s";
        	}
        	$sql .= " ".$getAllUsersSql2.") ";
        	
        	$sql .= " AND ((groupsStatus.startDate='0000-00-00' OR DATE(groupsStatus.startDate) LESSTHAN= CURDATE() ) AND (groupsStatus.endDate='0000-00-00' OR DATE(groupsStatus.endDate) GREATERTHAN= CURDATE())  AND (groupsStatus.status = 1) ".$getAllUsersSql.") ";
        	$sql .= " AND ((usersGroup.accessStart='0000-00-00' OR DATE(usersGroup.accessStart) LESSTHAN= CURDATE() ) AND (usersGroup.accessEnd='0000-00-00' OR DATE(usersGroup.accessEnd) GREATERTHAN= CURDATE() ) AND (usersGroup.isActive=1) ".$getAllUsersSql2.")";
        }
        $sql .= " GROUP BY usersDetail.userId ";
        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
        $sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);

        $results = $this->db->pdoSelect($sql,$vals,$types);

        if($results['status']){
        	$results = $results['rows'];
        }else{
        	$results = array();
        }
        return $results;
    }
    
    public function searchGroups($options=array()){
    	/**
			* Search among Groups table items
			* $options {
			*	(array)userId: {an array of user ids}
			*	(array)groupsId: an array of group ids in form of array('groupId'=>value)
			*	(array)groupName: {an array of the first names}
			*	(array)groupDescription: {an array of the last name}
			*	(array)insertBy_userId: {an array of the user id of the inserter}
			*	onlineInsertTime_start: search records with onlineInsertTime greater than or equal to this. format: 2017-08-21
			*	onlineInsertTime_end: search records with onlineInsertTime less than this. format: 2017-08-21
			*	status: status of group. Default to 1:Active
			*	limit: limit number of results default to -1; -1: no limit
			*	start: start pointer of results to fetch. Default to 0
			*	orderBy: order by. Default to groupsDetail.onlineInsertTime DESC
			*	searchStores: get stores of the group. default to 0
			*	(array)searchUsersGroup: {
			*		users_active: inner join results with usersGroup to get and search in the usersGroup as well. Default:false to improve speed if not neccessary,
			*		(array)(only used if active is true)usersGroup_userId: {array of id of the area code to search for users in that}
			* }
			* returns(array){
			*	status: 0:failed, 1: success
			*	id
			*	groupId
			*	groupName
			*	insertBy_userId
			*	insertIp
			*	onlineInsertTime
			*	(array)usersGroup{ Only if active, else empty
			*		userId
			*	}
			* }
		*/
		$groupsId = array();
		$userId = array();
		$groupName = array();
		$groupDescription = array();
		$insertBy_userId = array();
		$onlineInsertTime_start = "";
		$onlineInsertTime_end = "";
		$status = 1;
		$limit = -1;
		$start = 0;
		$orderBy = " groupsDetail.onlineInsertTime DESC ";
		$searchStores = 0;
		$searchUsersGroup = array("users_active"=>false);
		
		$vals = array();
		$types = array();
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
        
        $sql = "SELECT groupsDetail.id AS id, groupsDetail.groupId AS groupId, groupsDetail.groupName AS groupName, groupsDetail.groupDescription AS groupDescription, 
        	groupsDetail.onlineInsertTime AS onlineInsertTime, groupsDetail.insertBy_userId AS insertBy_userId, groupsDetail.insertIp AS insertIp, 
        	groupsParent.parent_groupId AS parent_groupId, 
        	groupsStatus.status AS status, groupsStatus.startDate AS startDate, groupsStatus.endDate AS endDate ";
        if(!empty(array_filter($searchUsersGroup))){
        	if($searchUsersGroup['users_active']){
        		$sql .= " , GROUP_CONCAT(usersGroup.userId) AS userId, GROUP_CONCAT(usersGroup.accessStart) AS accessStart, GROUP_CONCAT(usersGroup.accessEnd) AS accessEnd ";
        	}
        }
        if($searchStores){
        	$sql .= ", GROUP_CONCAT(groupsIdstoreId.storeId) AS storesId_group ";
        }
        $sql .=" FROM groupsDetail 
        		INNER JOIN groupsStatus ON groupsStatus.groupId = groupsDetail.groupId 
        		LEFT JOIN groupsStatus AS groupsStatus2 ON groupsStatus2.groupId = groupsStatus.groupId AND groupsStatus2.onlineInsertTime GREATERTHAN groupsStatus.onlineInsertTime 
        		LEFT JOIN groupsDetail AS groupsDetail2 ON groupsDetail.groupId = groupsDetail2.groupId AND groupsDetail2.onlineInsertTime GREATERTHAN groupsDetail.onlineInsertTime 
        		LEFT JOIN groupsParent ON groupsParent.groupId = groupsDetail.groupId 
        		LEFT JOIN groupsParent AS groupsParent2 ON groupsParent.groupId = groupsParent2.groupId AND groupsParent.parent_groupId = groupsParent2.parent_groupId AND groupsParent2.onlineInsertTime GREATERTHAN groupsParent.onlineInsertTime ";
        if($searchStores){
        	$sql .= " LEFT JOIN groupsIdstoreId ON groupsIdstoreId.groupId = groupsDetail.groupId 
        		LEFT JOIN groupsIdstoreId AS groupsIdstoreId2 ON groupsIdstoreId.groupId = groupsIdstoreId2.groupId AND groupsIdstoreId.storeId = groupsIdstoreId2.storeId AND groupsIdstoreId2.onlineInsertTime GREATERTHAN groupsIdstoreId.onlineInsertTime ";
        }
        if(!empty(array_filter($searchUsersGroup))){
        	if($searchUsersGroup['users_active']){
        		$sql .= " LEFT JOIN usersGroup ON usersGroup.groupId = groupsDetail.groupId 
        		LEFT JOIN usersGroup AS usersGroup2 ON usersGroup.groupId = usersGroup2.groupId AND usersGroup.userId = usersGroup2.userId AND usersGroup2.onlineInsertTime GREATERTHAN usersGroup.onlineInsertTime ";
        	}
        }
        $sql .=" WHERE groupsDetail2.id IS NULL AND groupsStatus2.id IS NULL AND (groupsParent.id IS NULL OR (groupsParent.id IS NOT NULL AND groupsParent2.id IS NULL)) AND groupsParent2.id IS NULL ";
        $sql .= " AND (((DATE(groupsStatus.startDate) LESSTHAN= (CURDATE()) OR DATE(groupsStatus.startDate)='0000-00-00') AND (DATE(groupsStatus.endDate) GREATERTHAN= (CURDATE()) OR DATE(groupsStatus.endDate)='0000-00-00')) OR groupsStatus.startDate IS NULL) ";
        if($status){
        	$sql .= " AND groupsStatus.status=1 ";
        }
        if(!empty(array_filter($searchUsersGroup))){
        	if($searchUsersGroup['users_active']){
        		$sql .=" AND (usersGroup.id IS NULL OR (usersGroup.id IS NOT NULL AND usersGroup2.id IS NULL)) AND usersGroup2.id IS NULL ";
        	}
        }
        if($searchStores){
        	$sql .= " AND groupsIdstoreId2.groupId IS NULL AND groupsIdstoreId.status = 1 ";
        }
        if(!empty(array_filter($groupsId))>0){
        	$sql .= " AND (groupsDetail.groupId = :groupId ";
        	$vals[':groupId'] = $groupsId[0]['groupId'];
        	$types[':groupId'] = "s";
        	foreach($groupsId as $key=>$element){
        		$sql .= " OR groupsDetail.groupId = :groupId".$key;
        		$vals[':groupId'.$key] = $element['groupId'];
        		$types[':groupId'.$key] = "s";
        	}
        	$sql .= ")";
        }
        if(!empty(array_filter($userId))>0){
        	$sql .= " AND (usersDetail.userId = :userId ";
        	$vals[':userId'] = $userId[0];
        	$types[':userId'] = "s";
        	foreach($userId as $key=>$element){
        		$sql .= " OR usersDetail.userId = :userId".$key;
        		$vals[':userId'.$key] = $element[$key];
        		$types[':userId'.$key] = "s";
        	}
        	$sql .= ")";
        }

        if(!empty(array_filter($insertBy_userId))>0){
        	$sql .= " AND (usersDetail.insertBy_userId = :insertBy_userId ";
        	$vals[':insertBy_userId'] = $insertBy_userId[0];
        	$types[':insertBy_userId'] = "s";
        	foreach($insertBy_userId as $key=>$element){
        		$sql .= " OR usersDetail.insertBy_userId = :insertBy_userId".$key;
        		$vals[':insertBy_userId'.$key] = $element[$key];
        		$types[':insertBy_userId'.$key] = "s";
        	}
        	$sql .= ")";
        }
        if($offlineInsertTime_start){
        	$sql .= " AND (DATE(usersDetail.offlineInsertTime) GREATERTHAN= DATE(:offlineInsertTime_start)) ";
        	$date = date_create($offlineInsertTime_start);
        	$offlineInsertTime_start = date_format($date, 'Y-m-d');
        	$vals[':offlineInsertTime_start'] = $offlineInsertTime_start;
        	$types[':offlineInsertTime_start'] = "s";	
        }
        if($offlineInsertTime_end){
        	$sql .= " AND (DATE(usersDetail.offlineInsertTime) LESSTHAN= DATE(:offlineInsertTime_end)) ";
        	$date = date_create($offlineInsertTime_end);
        	$offlineInsertTime_end = date_format($date, 'Y-m-d');
        	$vals[':offlineInsertTime_end'] = $offlineInsertTime_end;
        	$types[':offlineInsertTime_end'] = "s";	
        }

        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
        if($limit>0){
        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
        }

        $results = $this->db->pdoSelect($sql,$vals,$types);
        if($results['status']){
        	$results = $results['rows'];
        }else{
        	$results = array();
        }
        return $results;
    }

    public function getUserGroups($userId='',$options=array()){
    	/**
    	* Get gtroup details of a user
    	* $userId: get the group details related to one particular user
    	* (Array) $options{
    	*	groupBy: default usersGroup.groupId
    	*	orderBy: default groupsDetail.onlineInsertTime DESC
    	*	start: start of record default = 0
    	*	limit: limit of number of records fetched. default -1//TODO: paginate
    	*	count: only fetches count if 1 (for pagination). default to 0
    	* }
    	*/
    	$limit = -1;
	$start = 0;
	$orderBy = " groupsDetail.onlineInsertTime DESC ";
        $groupsId = array();
        $groupBy = " usersGroup.groupId ";
        $count = 0;
	foreach($options as $optionsKey=>$optionsElement){
	            ${$optionsKey} = $optionsElement;
	}
		
	if(!$userId){
		$loginCheck = $this->loginClass->loginCheck();
		if($loginCheck['status']){
			$userId = $loginCheck['userId'];
		}
	}
		
	$vals = array();
	$types = array();
		
    	$sql = "SELECT usersGroup.groupId AS groupId, DATE_FORMAT(usersGroup.accessStart,'%Y-%m-%d') AS accessStart, DATE_FORMAT(usersGroup.accessEnd,'%Y-%m-%d') AS accessEnd, usersGroup.onlineInsertTime AS onlineInsertTime,  
    		groupsDetail.groupName AS groupName, groupsDetail.groupDescription AS groupDescription,  
    		GROUP_CONCAT(IF(groupsParent.isActive,groupsParent.parent_groupId,NULL)) AS parent_groupId,
    		groupsStatus.status AS status, groupsStatus.startDate AS startDate , groupsStatus.endDate AS endDate 
    		FROM usersGroup 
    		LEFT JOIN usersGroup AS usersGroup2 ON usersGroup.groupId = usersGroup2.groupId AND usersGroup.userId = usersGroup2.userId AND usersGroup2.onlineInsertTime GREATERTHAN usersGroup.onlineInsertTime 
    		
    		INNER JOIN groupsDetail ON groupsDetail.groupId = usersGroup.groupId 
    		LEFT JOIN groupsDetail AS groupsDetail2 ON groupsDetail.groupId = groupsDetail2.groupId AND groupsDetail2.onlineInsertTime GREATERTHAN groupsDetail.onlineInsertTime 
    		INNER JOIN groupsStatus ON groupsStatus.groupId = groupsDetail.groupId 
        	LEFT JOIN groupsStatus AS groupsStatus2 ON groupsStatus2.groupId = groupsStatus.groupId AND groupsStatus2.onlineInsertTime GREATERTHAN groupsStatus.onlineInsertTime 
        	
    		LEFT JOIN groupsParent ON groupsParent.groupId = groupsDetail.groupId 
        	LEFT JOIN groupsParent AS groupsParent2 ON groupsParent.groupId = groupsParent2.groupId AND groupsParent.parent_groupId = groupsParent2.parent_groupId AND groupsParent2.onlineInsertTime GREATERTHAN groupsParent.onlineInsertTime ";

        $sql .= " WHERE usersGroup2.id IS NULL AND groupsDetail2.id IS NULL AND groupsStatus2.id IS NULL AND ((groupsParent.id IS NULL OR (groupsParent.id IS NOT NULL AND groupsParent2.id IS NULL)) AND groupsParent2.id IS NULL) ";
        $sql .= " AND (( (DATE(groupsStatus.startDate) LESSTHAN= CURDATE() OR DATE(groupsStatus.startDate)='0000-00-00' OR groupsStatus.startDate IS NULL) AND (DATE(groupsStatus.endDate) GREATERTHAN= CURDATE() OR DATE(groupsStatus.endDate)='0000-00-00' OR groupsStatus.endDate IS NULL) AND groupsStatus.status = 1) OR groupsStatus.startDate IS NULL) ";
	$sql .= " AND (((DATE(usersGroup.accessStart) LESSTHAN= CURDATE() OR DATE(usersGroup.accessStart)='0000-00-00' OR usersGroup.accessStart IS NULL) AND (DATE(usersGroup.accessEnd) GREATERTHAN= CURDATE() OR usersGroup.accessEnd ='' OR usersGroup.accessEnd IS NULL)) AND usersGroup.isActive=1) ";
	if($userId){
		$sql .= " AND usersGroup.userId=:userId ";
		$vals[":userId"] = $userId;
		$types[":userId"] = "s";
        }
        $sql .= " GROUP BY ".$this->filePath->toSafeString($groupBy);
        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
        if($limit>0){
        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
        }

        $results = $this->db->pdoSelect($sql,$vals,$types);
        
        if($results['status']){
        	$results = $results['rows'];
        }else{
        	$results = array();
        }
        
        return $results;
    }

	public function addUsersGroup($options=array()){
		/**
		* Adds user group to a user
		* (array)$options{
		* 	$userId: userId of the user. Default to current user
		*	(array) $groups{
		*		"groupId"=>$groupId,
		*		"isActive"=>$isActive,
		*		"accessStart"=>$accessStart,
		*		"accessEnd"=>$accessEnd
		*	}
		*	$insertBy_userId
		* }
		*/
		$userId = "";
		$insertBy_userId = "";
		$groups = array();
        
	        foreach($options as $optionsKey=>$optionsElement){
	            ${$optionsKey} = $optionsElement;
	        }
		
		$loginCheck = $this->loginClass->loginCheck();
		if(!$userId){
			if($loginCheck['status']){
				$userId = $loginCheck['userId'];
			}
		}
		
		if(!$insertBy_userId){
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}

		$col = array();
		$val = array();
		$type = array();

		$col = array(
	       		"groupId",
	        	"userId",
	        	"isActive",
	        	"accessStart",
	        	"accessEnd",
			"insertBy_userId",
			"insertIp"
	        );
		foreach($groups as $groups_element){
		        $val[] = array(
		        	"groupId"=>$groups_element['groupId'],
		        	"userId"=>$userId,
		        	"isActive"=>$groups_element['isActive'],
		        	"accessStart"=>$groups_element['accessStart'],
		        	"accessEnd"=>$groups_element['accessEnd'],
				"insertBy_userId"=>$insertBy_userId,
				"insertIp"=>$this->filePath->getUserIp()
		        );
		}
	        $type = array(
	        	"groupId"=>"s",
	        	"userId"=>"s",
	        	"isActive"=>"i",
	        	"accessStart"=>"s",
	        	"accessEnd"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );

	        $result = $this->db->pdoInsert("usersGroup",$col,$val,$type);
	        if(isset($result['status'])){
	            if($result['status']>0){
	                $status = 1;
	                $msgCode = "user groups updated successfuly";
	                $msgCat = "OK_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }else{
	                $status = 0;
	                $msgCode = "There was a system error updating user group. Please try again. Administrator is informed of the problem.";
	                $msgCat="SYS_ERR_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }
	        }else{
	            $status = 0;
	            $msgCode = "There was a system error updating user group. Please try again. Administrator is informed of the problem.";
	            $msgCat="SYS_ERR_MSG";
	            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	        }

	        return array("status"=>$status,"msg"=>$msg);
	}
	public function getUserGroupStatus($userId,$groupId){
		$output = array();
		$sql = "SELECT usersGroup.isActive, usersGroup.accessStart, usersGroup.accessEnd 
			FROM usersGroup 
			WHERE usersGroup.groupId=:groupId AND usersGroup.userId=:userId ORDER BY usersGroup.onlineInsertTime DESC, usersGroup.id DESC 
			LIMIT 0,1";
		$vals = array(
			":groupId"=>$groupId,
			":userId"=>$userId
		);
		$types = array(
			":groupId"=>"s",
			":userId"=>"s"
		);
		$results = $this->db->pdoSelect($sql,$vals,$types);

        	if($results['status']){
        		$output['usersGroups'] = $results['rows'];
			if($results['isActive'] && $results['accessEnd']>date("Y-m-d") && $results['accessStart']<date("Y-m-d")){
				$output['usersGroups']['status'] = 1;
			}else{
				$output['usersGroups']['status'] = 0;
			}
        	}else{
        		$output['usersGroups'] = array();
        	}

		$sql = "SELECT groupsStatus.status AS status, groupsStatus.startDate AS startDate, groupsStatus.endDate AS endDate 
			FROM groupsStatus 
			WHERE groupId = :groupId 
			ORDER BY onlineInsertTime DESC, id DESC 
			LIMIT 0,1";
		$vals = array(
			":groupId"=>$groupId,
			":userId"=>$userId
		);
		$types = array(
			":groupId"=>"s",
			":userId"=>"s"
		);
		$results = $this->db->pdoSelect($sql,$vals,$types);
		if($results['status']){
        		$output['group'] = $results['rows'];
			if($results['status'] && $results['endDate']>date("Y-m-d") && $results['startDate']<date("Y-m-d")){
				$output['group']['status'] = 1;
			}else{
				$output['group']['status'] = 0;
			}
        	}else{
        		$output['group'] = array();
        	}
		if($output['usersGroups']['status'] && $output['group']['status']){
			$output['status'] = 1;
		}
        	return $output;
	}
	
	public function getUserFunctions($options = array()){
		/**
			* Get an array of lists of allowable functions and menucodes based on userid or check the allowability of one function
			* Options {
			*	$userGroups
			*	(array)$functionCodes: array of codes of the functions to check the access or empty for all function
			*	$userId: userId or empty for current user
			*	$getFunctionDetails: get the details of function. default to 0
			* }
			* returns{
			*	(Array)rows[functionCode,menuCode]
			* }
		*/
		$userId='';
		$userGroups=0;
		$functionCodes=array();
		$getFunctionDetails = false;
		foreach($options as $optionsKey=>$optionsElement){
	            ${$optionsKey} = $optionsElement;
	        }
		if(!$userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$userId = $loginCheck['userId'];
			}
		}
		if($userGroups == false){
		                                		
		}else{
			$userGroups = $this->getUserGroups($userId);
		}
		$output = array();
		$vals = array();
		$types = array();
		$sql = "SELECT usersFunctionAccess.functionCode AS functionCode, functionMenu.menuCode AS menuCode, usersFunctionAccess.userId AS userId, usersFunctionAccess.groupId AS groupId, groupsDetail.groupName ";
		if($getFunctionDetails){
			$sql .= ", function.categoryCode AS categoryCode ";
		}
		$sql .= " FROM usersFunctionAccess 
		        INNER JOIN groupsDetail ON groupsDetail.groupId=usersFunctionAccess.groupId ";
		 if($getFunctionDetails){
		 	$sql .= " INNER JOIN function ON function.functionCode = usersFunctionAccess.functionCode ";
		 }
		 $sql .=" LEFT JOIN usersFunctionAccess AS usersFunctionAccess2 ON ((usersFunctionAccess2.userId=usersFunctionAccess.userId AND usersFunctionAccess2.functionCode=usersFunctionAccess.functionCode) OR (usersFunctionAccess2.groupId=usersFunctionAccess.groupId AND usersFunctionAccess2.functionCode=usersFunctionAccess.functionCode)) AND usersFunctionAccess2.onlineInsertTime GREATERTHAN usersFunctionAccess.onlineInsertTime 
		        LEFT JOIN functionMenu ON functionMenu.functionCode = usersFunctionAccess.functionCode 
		        INNER JOIN groupsStatus ON groupsStatus.groupId = usersFunctionAccess.groupId 
		        LEFT JOIN groupsStatus AS groupsStatus2 ON groupsStatus.groupId = groupsStatus2.groupId AND groupsStatus2.onlineInsertTime GREATERTHAN groupsStatus.onlineInsertTime 
		        WHERE usersFunctionAccess2.id IS NULL AND usersFunctionAccess.status=1 AND groupsStatus.status=1 AND groupsStatus2.id IS NULL AND ( (usersFunctionAccess.userId = '' AND usersFunctionAccess.groupId = '') ";
		        if($userId){
		        	$sql .=" OR usersFunctionAccess.userId = :userId ";
			        $vals[':userId'] = $userId;
			        $types[':userId'] = 's';
		        }
		        if(!empty(array_filter($userGroups))>0){
		        	$sql .= " OR usersFunctionAccess.groupId = :userGroups ";
		        	$vals[':userGroups'] = $userGroups[0]['groupId'];
		        	$types[':userGroups'] = 's';
		        	foreach($userGroups as $key=> $userGroupsElement){
		        		$sql .= " OR usersFunctionAccess.groupId = :userGroups".$key;
		        		$vals[':userGroups'.$key] = $userGroupsElement['groupId'];
		        		$types[':userGroups'.$key] = 's';
		        	}
		        }
		$sql .= ") ";
		if(!empty(array_filter($functionCodes))>0){
			$sql .= " AND (usersFunctionAccess.functionCode=:functionCode ";
			if(isset($functionCode[0]['functionCode'])){
				$vals[':functionCode'] = $functionCode[0]['functionCode'];
			}else{
				$vals[':functionCode'] = $functionCode[0];
			}
		        $types[':functionCode'] = 's';
			foreach($functionCodes as $key => $functionCodesElement){
				$sql .= " OR usersFunctionAccess.functionCode=:functionCode".$key;
				if(isset($functionCodesElement['functionCode'])){
					$vals[':functionCode'.$key] = $functionCodesElement['functionCode'];
				}else{
					$vals[':functionCode'.$key] = $functionCodesElement;
				}
			        $types[':functionCode'.$key] = 's';
		        }
		        $sql .= ") ";
		        $sql .= " GROUP BY usersFunctionAccess.functionCode ";
		        $ql .= " LIMIT 0,".count($functionCodes);
		}
		
		$results = $this->db->pdoSelect($sql,$vals,$types);
		if($results['status']){
			$output = $results['rows'];
		}
		return $output;
	}
	
	public function checkFunctionsAccess($options=array()){
		/**
			* Search in all functions available in the system
			* Options {
			*	(array)$searchCategory{
			*		array('categoryCode'=>value),
			*		...
			*	}
			*	(array)groupsId{
			*		array('groupId'=>value)
			*	}
			* }
			* returns{
			*	(Array)
			* }
		*/
		foreach($options as $optionsKey=>$optionsElement){
            ${$optionsKey} = $optionsElement;
        }

		$vals = array();
		$types = array();
		
		$sql = "SELECT 
		    dynaPageFunction.id AS `dynaPageFunction_id`, 
		    dynaPageFunction.functionId AS `dynaPageFunction_functionId`, 
		    dynaPageFunction.inParameters AS `dynaPageFunction_inParameters`, 
		    dynaPageFunctionId_usersGroupId.groupId AS `dynaPageFunctionId_usersGroupId_groupId`, 
		    dynaPageFunctionId_usersGroupId.restriction AS `dynaPageFunctionId_usersGroupId_restriction`, 
		    dynaPageFunctionId_usersGroupId.status AS `dynaPageFunctionId_usersGroupId_status`, 
		    dynaPageFunctionStatus.status AS `dynaPageFunctionStatus_status`, 
			
			FROM dynaPageFunction 
			LEFT JOIN dynaPageFunction AS dynaPageFunction2 ON dynaPageFunction2.functionId = dynaPageFunction.functionId ";
			
		$sql .= " INNER JOIN dynaPageFunctionStatus ON dynaPageFunctionStatus.functionId = dynaPageFunction.functionId 
		        LEFT JOIN dynaPageFunctionStatus AS dynaPageFunctionStatus2 ON dynaPageFunctionStatus2.functionId = dynaPageFunctionStatus.functionId";
		        
		$sql .= " LEFT JOIN dynaPageFunctionId_usersGroupId ON dynaPageFunctionId_usersGroupId.functionId = dynaPageFunction.functionId 
		        LEFT JOIN dynaPageFunctionId_usersGroupId AS dynaPageFunctionId_usersGroupId2 ON dynaPageFunctionId_usersGroupId2.functionId = dynaPageFunctionId_usersGroupId.functionId AND 
		        dynaPageFunctionId_usersGroupId2.groupId = dynaPageFunctionId_usersGroupId.groupId AND
		        dynaPageFunctionId_usersGroupId2.restriction = dynaPageFunctionId_usersGroupId.restriction ";
				
		$sql .= " WHERE dynaPageFunction2.functionId IS NULL AND dynaPageFunctionStatus2.functionId IS NULL  ";


		$results = $this->db->pdoSelect($sql,$vals,$types);
		return $results;
	}
	
	public function addGroupsDetail($groupId,$options){
		/**
		* Add/Change Group Details
		* groupId : Id of group to change
		* (array) groupsOptions{
		*	groupName: Name of group
		*	groupDescription: Description of Group
		*	insertBy_userId: who inserted. Default current user
		* }
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	groupId: Id of group trying to change
		*	id: id of this record
		* }
		*/
		$groupName = "";
		$groupDescription = "";
		$insertBy_userId = "";
		$id = '';
        
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
	        	"groupId",
	        	"groupName",
	        	"groupDescription",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"groupId"=>$groupId,
	        	"groupName"=>$groupName,
	            	"groupDescription"=>$groupDescription,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"groupId"=>"s",
	        	"groupName"=>"s",
			"groupDescription"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
		$result = $this->db->pdoInsert("groupsDetail",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "group details updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating group details. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating group details. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("groupId"=>$groupId,"status"=>$status,"id"=>$id,"msg"=>$msg);
	}
	
	public function addGroupStatus($groupId,$groupStatus,$startDate='0000-00-00',$endDate='0000-00-00',$insertBy_userId=''){
		/**
		* Add/Change Group Status
		* groupId : Id of group to change
		* groupStatus: Status of group. 0:inActive, 1:active
		* startDate: Date that the group is valid from
		* endDate: Date that the group is valid to
		* insertBy_userId: who inserted. Default current user
		* return array{
		*	status: 0:failed, 1:success
		*	msg: error message decoded in users language if failed
		*	groupId: Id of group trying to change
		*	id: id of this record
		* }
		*/
		$id = '';
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		
	        $col = array(
	        	"groupId",
	        	"status",
	        	"startDate",
	        	"endDate",
	        	"insertBy_userId",
	        	"insertIp"
	        );
	        $val = array(
	        	"groupId"=>$groupId,
	            	"status"=>$groupStatus,
	            	"startDate"=>$startDate,
	        	"endDate"=>$endDate,
	            	"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
	        	"groupId"=>"s",
			"status"=>"s",
			"startDate"=>"s",
	        	"endDate"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
		$result = $this->db->pdoInsert("groupsStatus",$col,$val,$type);
		if(isset($result['status'])){
			if($result['status']>0){
				$status = 1;
				$msgCode = "group status updated successfuly";
				$msgCat = "OK_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
				$status = 0;
				$msgCode = "There was a system error updating group status. Please try again. Administrator is informed of the problem.";
				$msgCat="SYS_ERR_MSG";
				$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
		}else{
			$status = 0;
			$msgCode = "There was a system error updating group status. Please try again. Administrator is informed of the problem.";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
		}
		return array("groupId"=>$groupId,"status"=>$status,"id"=>$id,"msg"=>$msg);
	}
	
	public function addGroupsFunction($groupId,$options=array()){
		/**
		* Adds functions to group
		* groupId: groupId to add functions to
		* (array)$options{
		* 	
		*	(array) $functions{
		*		"functionCode"=>$functionCode,
		*		"status"=>$status
		*	}
		*	insertBy_userId
		* }
		*/
		$insertBy_userId = "";
		$functions = array();
        
	        foreach($options as $optionsKey=>$optionsElement){
	            ${$optionsKey} = $optionsElement;
	        }
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}

		$col = array();
		$val = array();
		$type = array();

		$col = array(
	       		"groupId",
	        	"functionCode",
	        	"status",
			"insertBy_userId",
			"insertIp"
	        );
		foreach($functions as $functions_element){
		        $val[] = array(
		        	"groupId"=>$groupId,
		        	"functionCode"=>$functions_element['functionCode'],
		        	"status"=>$functions_element['status'],
				"insertBy_userId"=>$insertBy_userId,
				"insertIp"=>$this->filePath->getUserIp()
		        );
		}
	        $type = array(
	        	"groupId"=>"s",
	        	"functionCode"=>"s",
	        	"status"=>"i",
	        	"insertBy_userId"=>"s",
	        	"insertIp"=>"s"
	        );

	        $result = $this->db->pdoInsert("usersFunctionAccess",$col,$val,$type);
	        if(isset($result['status'])){
	            if($result['status']>0){
	                $status = 1;
	                $msgCode = "group functions updated successfuly";
	                $msgCat = "OK_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }else{
	                $status = 0;
	                $msgCode = "There was a system error updating group functions. Please try again. Administrator is informed of the problem.";
	                $msgCat="SYS_ERR_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }
	        }else{
	            $status = 0;
	            $msgCode = "There was a system error updating group functions. Please try again. Administrator is informed of the problem.";
	            $msgCat="SYS_ERR_MSG";
	            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	        }

	        return array("status"=>$status,"msg"=>$msg,"groupId"=>$groupId);
	}
	
	public function addGroupsParent($groupId,$options=array()){
		/**
		* Adds parents to group
		* groupId: groupId to add parents to
		* (array)$options{
		*	(array) $parents{
		*		"groupId"=>$functionCode,
		*		"status"=>$status
		*	}
		*	insertBy_userId
		* }
		*/
		$insertBy_userId = "";
		$parents = array();
        
	        foreach($options as $optionsKey=>$optionsElement){
	            ${$optionsKey} = $optionsElement;
	        }
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}

		$col = array();
		$val = array();
		$type = array();

		$col = array(
	       		"groupId",
	        	"parent_groupId",
	        	"isActive",
			"insertBy_userId",
			"insertIp"
	        );
		foreach($parents as $parents_element){
		        $val[] = array(
		        	"groupId"=>$groupId,
		        	"parent_groupId"=>$parents_element['groupId'],
		        	"isActive"=>$parents_element['status'],
				"insertBy_userId"=>$insertBy_userId,
				"insertIp"=>$this->filePath->getUserIp()
		        );
		}
	        $type = array(
	        	"groupId"=>"s",
	        	"parent_groupId"=>"s",
	        	"isActive"=>"i",
	        	"insertBy_userId"=>"s",
	        	"insertIp"=>"s"
	        );

	        $result = $this->db->pdoInsert("groupsParent",$col,$val,$type);
	        if(isset($result['status'])){
	            if($result['status']>0){
	                $status = 1;
	                $msgCode = "group Parents updated successfuly";
	                $msgCat = "OK_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }else{
	                $status = 0;
	                $msgCode = "There was a system error updating group Parents. Please try again. Administrator is informed of the problem.";
	                $msgCat="SYS_ERR_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }
	        }else{
	            $status = 0;
	            $msgCode = "There was a system error updating group Parents. Please try again. Administrator is informed of the problem.";
	            $msgCat="SYS_ERR_MSG";
	            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	        }

	        return array("status"=>$status,"msg"=>$msg,"groupId"=>$groupId);
	}
	
	public function addGroupsStore($groupId,$options=array()){
		/**
		* Adds stores to group
		* groupId: 
		* (array) $options{
		*	(array) $stores{
		*		"storesDetail_storeId"=>$storeId,
		*		"status"=>$status
		*	}
		*	insertBy_userId
		* }
		*/
		$insertBy_userId = "";
		$stores = array();
        
	        foreach($options as $optionsKey=>$optionsElement){
	            ${$optionsKey} = $optionsElement;
	        }
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}

		$col = array();
		$val = array();
		$type = array();

		$col = array(
	       		"storeId",
	        	"groupId",
	        	"status",
			"insertBy_userId",
			"insertIp"
	        );
	        $userIp = $this->filePath->getUserIp();
	        
		foreach($stores as $stores_element){
		        $val[] = array(
		        	"storeId"=>$stores_element['storesDetail_storeId'],
		        	"groupId"=>$groupId,
		        	"status"=>$stores_element['status'],
				"insertBy_userId"=>$insertBy_userId,
				"insertIp"=>$userIp
		        );
		}
	        $type = array(
	        	"storeId"=>"s",
	        	"groupId"=>"s",
	        	"status"=>"i",
	        	"insertBy_userId"=>"s",
	        	"insertIp"=>"s"
	        );

	        $result = $this->db->pdoInsert("groupsIdstoreId",$col,$val,$type);
	        
	        if(isset($result['status'])){
	            if($result['status']>0){
	                $status = 1;
	                $msgCode = "Group stores updated successfuly";
	                $msgCat = "OK_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }else{
	                $status = 0;
	                $msgCode = "There was a system error updating group stores. Please try again. Administrator is informed of the problem.";
	                $msgCat="SYS_ERR_MSG";
	                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	            }
	        }else{
	            $status = 0;
	            $msgCode = "There was a system error updating group stores. Please try again. Administrator is informed of the problem.";
	            $msgCat="SYS_ERR_MSG";
	            $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	        }

	        return array("status"=>$status,"msg"=>$msg,"groupId"=>$groupId);
	}
	
	public function addGroupId($options){
		/**
		* Adds a new group id
		*/
		$offlinegroupId = '';
		$groupId = '';
		$insertBy_userId = '';
		
		foreach($options as $optionsKey=>$optionsElement){
	            ${$optionsKey} = $optionsElement;
	        }
		
		if(!$insertBy_userId){
			$loginCheck = $this->loginClass->loginCheck();
			if($loginCheck['status']){
				$insertBy_userId = $loginCheck['userId'];
			}
		}
		
		if(!$groupId){
			$groupId = $this->loginClass->generateSecureId("groupsId",$offlinegroupId);
		}
		$col = array(
			"groupId",
			"insertBy_userId",
			"insertIp"
        	);
	        $val = array(
			"groupId"=>$groupId,
			"insertBy_userId"=>$insertBy_userId,
			"insertIp"=>$this->filePath->getUserIp()
	        );
	        $type = array(
			"groupId"=>"s",
			"insertBy_userId"=>"s",
			"insertIp"=>"s"
	        );
        	$result = $this->db->pdoInsert("groupsId",$col,$val,$type);
	        if(isset($result['status'])){
			if($result['status']>0){
		                $status = 1;
		                $msgCode = "Group id created successfuly";
		                $msgCat = "OK_MSG";
		                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}else{
		                $status = 0;
		                $msgCode = "There was a system error creating group id. Please try again. Administrator is informed of the problem.EX1410";
		                $msgCat="SYS_ERR_MSG";
		                $msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
			}
	        }else{
			$status = 0;
			$msgCode = "There was a system error creating group id. Please try again. Administrator is informed of the problem.EX1416";
			$msgCat="SYS_ERR_MSG";
			$msg = $this->langDecode->decode($msgCode,$msgCat,$this->lang);
	        }
        
        	return array("groupId"=>$groupId,"status"=>$status,"msg"=>$msg);
	}
	
	public function getGroupTills($groupIds=array(), $storeIds=array()){
		$sql = "SELECT tillsDetail.tillId AS `tillsDetail_tillId`, tillsDetail.tillCode AS `tillsDetail_tillCode`, tillsDetail.tillNumber AS `tillsDetail_tillNumber`, storesDetail.storeCode AS `storesDetail_storeCode`, storesDetail.storeName AS `storesDetail_storeName`, storesDetail.storeId AS `storesDetail_storeId`  ";
		$sql .= " FROM tillsDetail 
			LEFT JOIN tillsDetail AS tillsDetail2 ON tillsDetail.tillId = tillsDetail2.tillId AND tillsDetail2.onlineInsertTime GREATERTHAN tillsDetail.onlineInsertTime 
			
			INNER JOIN tillsStatus ON tillsStatus.tillId = tillsDetail.tillId 
			LEFT JOIN tillsStatus AS tillsStatus2 ON tillsStatus.tillId = tillsStatus2.tillId AND tillsStatus2.onlineInsertTime GREATERTHAN tillsStatus.onlineInsertTime 
			
			INNER JOIN storesDetail ON storesDetail.storeId = tillsDetail.storeId 
			LEFT JOIN storesDetail AS storesDetail2 ON  storesDetail.storeId =  storesDetail2.storeId AND  storesDetail2.onlineInsertTime GREATERTHAN  storesDetail.onlineInsertTime 
			
			INNER JOIN storesStatus ON storesStatus.storeId = storesDetail.storeId 
			LEFT JOIN storesStatus AS storesStatus2 ON storesStatus.storeId = storesStatus2.storeId AND storesStatus2.onlineInsertTime GREATERTHAN storesStatus.onlineInsertTime 
			
			INNER JOIN groupsIdstoreId ON groupsIdstoreId.storeId = storesDetail.storeId 
			LEFT JOIN groupsIdstoreId AS groupsIdstoreId2 ON groupsIdstoreId.groupId=groupsIdstoreId2.groupId AND groupsIdstoreId.storeId = groupsIdstoreId2.storeId AND groupsIdstoreId2.onlineInsertTime GREATERTHAN groupsIdstoreId.onlineInsertTime ";
			
		$sql .= " WHERE tillsDetail2.tillId IS NULL AND tillsStatus2.tillId IS NULL AND tillsStatus.status = 1 AND storesDetail2.storeId IS NULL AND storesStatus2.storeId IS NULL AND storesStatus.status = 1 AND groupsIdstoreId2.storeId IS NULL AND groupsIdstoreId.status = 1 ";
		if(!empty(array_filter($groupIds))>0){
		        $sql .= " AND ( groupsIdstoreId.groupId = :groupId ";
		        $vals[':groupId'] = $groupIds[0]['groupId'];
		        $types[':groupId'] = 's';
		        foreach($groupIds as $key=> $groupIdsElement){
		        	$sql .= " OR groupsIdstoreId.groupId = :groupId".$key;
		        	$vals[':groupId'.$key] = $groupIdsElement['groupId'];
		        	$types[':groupId'.$key] = 's';
		        }
		        $sql .= " ) ";
		}
		if(!empty(array_filter($storeIds))>0){
		        $sql .= " AND ( groupsIdstoreId.storeId = :storeId ";
		        $vals[':storeId'] = $storeIds[0]['storeId'];
		        $types[':storeId'] = 's';
		        foreach($storeIds as $key=> $storeIdsElement){
		        	$sql .= " OR groupsIdstoreId.storeId = :storeId".$key;
		        	$vals[':storeId'.$key] = $storeIdsElement['storeId'];
		        	$types[':storeId'.$key] = 's';
		        }
		        $sql .= " ) ";
		}
		$results = $this->db->pdoSelect($sql,$vals,$types);
		return $results;
	}
	public function getStoreUsers($storeIds){
		/**
			Get users of specific stores
			storeIds: array(array('storeId'=>),...)
		*/
		$vals = array();
		$types = array();
		
		$sql = "SELECT DISTINCT(usersGroup.userId) AS `userId`  
			FROM usersGroup 
			LEFT JOIN usersGroup AS usersGroup2 ON usersGroup.userId = usersGroup2.userId AND usersGroup.groupId = usersGroup2.groupId AND usersGroup2.onlineInsertTime GREATERTHAN usersGroup.onlineInsertTime 
			
			INNER JOIN usersStatus ON usersStatus.userId = usersGroup.userId 
			LEFT JOIN usersStatus AS usersStatus2 ON usersStatus.userId = usersStatus2.userId AND usersStatus2.onlineInsertTime GREATERTHAN usersStatus.onlineInsertTime 
			
			INNER JOIN groupsStatus ON groupsStatus.groupId = usersGroup.groupId 
			LEFT JOIN groupsStatus AS groupsStatus2 ON groupsStatus.groupId = groupsStatus2.groupId AND groupsStatus2.onlineInsertTime GREATERTHAN groupsStatus.onlineInsertTime 
			
			INNER JOIN groupsIdstoreId ON groupsIdstoreId.groupId = usersGroup.groupId 
			LEFT JOIN groupsIdstoreId AS groupsIdstoreId2 ON groupsIdstoreId.groupId = groupsIdstoreId2.groupId AND groupsIdstoreId.storeId = groupsIdstoreId2.storeId AND groupsIdstoreId2.onlineInsertTime GREATERTHAN groupsIdstoreId.onlineInsertTime ";
			
		$sql .= " AND groupsStatus2.groupId IS NULL AND ((groupsStatus.startDate='0000-00-00' OR DATE(groupsStatus.startDate) LESSTHAN= CURDATE() ) AND (groupsStatus.endDate='0000-00-00' OR DATE(groupsStatus.endDate) GREATERTHAN= CURDATE())  AND (groupsStatus.status = 1)) ";
		
        	$sql .= " AND usersGroup2.groupId IS NULL AND ((usersGroup.accessStart='0000-00-00' OR DATE(usersGroup.accessStart) LESSTHAN= CURDATE() ) AND (usersGroup.accessEnd='0000-00-00' OR DATE(usersGroup.accessEnd) GREATERTHAN= CURDATE() ) AND (usersGroup.isActive=1)) ";
        	
        	$sql .= " AND usersStatus2.userId IS NULL AND groupsStatus.status = 1 AND groupsIdstoreId2.groupId IS NULL AND groupsIdstoreId.status = 1 ";
        	
        	if(!empty(array_filter($storeIds))>0){
		        $sql .= " AND ( groupsIdstoreId.storeId = :storeId ";
		        $vals[':storeId'] = $storeIds[0]['storeId'];
		        $types[':storeId'] = 's';
		        foreach($storeIds as $key=> $storeIdsElement){
		        	$sql .= " OR groupsIdstoreId.storeId = :storeId".$key;
		        	$vals[':storeId'.$key] = $storeIdsElement['storeId'];
		        	$types[':storeId'.$key] = 's';
		        }
		        $sql .= " ) ";
		}
		$results = $this->db->pdoSelect($sql,$vals,$types);
		return $results;
	}
	
	public function getPinLogin($userIds){
		$vals = array();
		$types = array();
		$sql = "SELECT usersPin.pinCode AS `usersPin_pinCode`, usersPin.userId AS `usersPin_userId` 
			FROM usersPin 
			LEFT JOIN usersPin AS usersPin2 ON usersPin.userId = usersPin2.userId AND usersPin2.onlineInsertTime GREATERTHAN usersPin.onlineInsertTime 
			
			INNER JOIN usersStatus ON usersStatus.userId = usersPin.userId 
			LEFT JOIN usersStatus AS usersStatus2 ON usersStatus.userId = usersStatus2.userId AND usersStatus2.onlineInsertTime GREATERTHAN usersStatus.onlineInsertTime 
			
			WHERE usersPin2.userId IS NULL AND usersStatus2.userId IS NULL AND usersStatus.pinLoginStatus = 1 ";
			
		if(!empty(array_filter($userIds))>0){
		        $sql .= " AND ( usersPin.userId = :userId ";
		        $vals[':userId'] = $userIds[0]['userId'];
		        $types[':userId'] = 's';
		        foreach($userIds as $key=> $userIdsElement){
		        	$sql .= " OR usersPin.userId = :userId".$key;
		        	$vals[':userId'.$key] = $userIdsElement['userId'];
		        	$types[':userId'.$key] = 's';
		        }
		        $sql .= " ) ";
		}
		$results = $this->db->pdoSelect($sql,$vals,$types);
		return $results;
	}
}

?>