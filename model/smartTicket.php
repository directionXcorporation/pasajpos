<?php

class smartTicketModelClass{
	protected $db_constructor, $filePath_constructor, 
		$langDecode_constructor, $loginClass_constructor, $lang;
	public function __construct($db_constructor,$filePath_constructor,$langDecode_constructor,$loginClass_constructor, $lang="") {
	        $this->db = $db_constructor;
	        $this->langDecode = $langDecode_constructor;
	        $this->filePath = $filePath_constructor;
	        $this->loginClass = $loginClass_constructor;
		$this->lang = $lang;
	}
	
	public function SearchIntro($options=array(), $debug=0){
		/**
			
		*/
		$introDescription = "";
		$introId = array();
		$searchable = 1;
		$status = 1;
		$start = 0;
		$limit = -1;
		$orderBy = "dynaIntro.introId, dynaIntro.onlineInsertTime";
		
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		
		$vals = array();
		$types = array();
		try{
    		$sql = "
    		SELECT 
    		    MAX(dynaIntro.introId) AS `dynaIntro_introId`,
    		    MAX(dynaIntro.introTitle) AS `dynaIntro_introTitle`,
            	MAX(dynaIntro.introDescription) AS `dynaIntro_introDescription`,
            	MAX(dynaIntro.introAlt) AS `dynaIntro_introAlt`,
            	MAX(dynaIntro.status) AS `dynaIntro_status`
            	
            FROM dynaIntro 
            	LEFT JOIN 
            		dynaIntro AS dynaIntro2 ON 
            			dynaIntro2.introId = dynaIntro.introId AND
            			dynaIntro2.onlineInsertTime > dynaIntro.onlineInsertTime
            
            WHERE 
            	dynaIntro2.introId IS NULL 
    	    ";
    	    if(isset($searchable)){
    	        $sql .= " AND dynaIntro.searchable = :searchable ";
    	        $vals[':searchable'] = $searchable;
    	        $types[':searchable'] = "i";
    	    }
    	    if(isset($introDescription) && $introDescription !=""){
    	        $sql .= " AND 
    	            ( 
    	                ( dynaIntro.introDescription LIKE :dynaIntro_introDescription ) OR 
    	                ( dynaIntro.introAlt LIKE :dynaIntro_introAlt ) 
    	            ) ";
    	        $vals[':dynaIntro_introDescription'] = $introDescription;
    	        $types[':dynaIntro_introDescription'] = "s";
    	        $vals[':dynaIntro_introAlt'] = $introDescription;
    	        $types[':dynaIntro_introAlt'] = "s";
    	    }
    	    if(isset($introId) && isset($introId['rows']) && !empty(array_filter($introId['rows']))>0){
    	        $sql .= " AND (dynaIntro.introId = :dynaIntro_introId ";
    	        if(isset($introId['fieldName']) && $introId['fieldName']){
    	            $fieldName = $introId['fieldName'];
    	        }else{
    	            $fieldName = 0;
    	        }
    	        $vals[':dynaIntro_introId'] = $introId['rows'][0][$fieldName];
    	        $types[':dynaIntro_introId'] = "s";
    	        foreach($introId['rows'] as $key=>$element){
    	            if(isset($introId['fieldName']) && $introId['fieldName']){
        	       	    $fieldName = $introId['fieldName'];
        	       	}else{
        	       	    $fieldName = $key;
        	       	}
    	        	$sql .= " OR dynaIntro.introId = :dynaIntro_introId".$key;
    	        	$vals[':dynaIntro_introId'.$key] = $element[$fieldName];
    	        	$types[':dynaIntro_introId'.$key] = "s";
    	        }
    	        $sql .= ") ";
    	    }
    	    if(isset($status) && $status>-1){
    	        $sql .= " AND dynaIntro.status = :dynaIntro_status ";
    	        $vals[':dynaIntro_status'] = $status;
    	        $types[':dynaIntro_status'] = "i";
    	    }
    	    
    		$sql .= " GROUP BY dynaIntro.introId ";
    		if(isset($orderBy) && $orderBy != ""){
    	        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
    		}
    	    
    	    if(isset($limit) && $limit>0){
    	        $sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
    	    }
    	        
    	    $results = $this->db->pdoSelect($sql, $vals, $types);
    	    $results['sortableColumns'] = array(
    	        "dynaIntro_introId",
    	        "dynaIntro_onlineInsertTime"
    	    );
    	    $results['sortBy'] = trim($orderBy);
	    }
		catch(Exception $e) {
			$msg = $e->getMessage();
			$results = array("status"=>0, "msg"=>$msg, "rows"=>array(), "sortBy"=>trim($orderBy), "sortableColumns"=>array());
		}
		if($debug==1){
		    $results['sql'] = $sql;
		    $results['vals'] = $vals;
		    $results['types'] = $types;
		}
		return $results;
	}
    public function SearchIntroSteps($options=array(), $debug=0){
		/**
			
		*/
		$stepDescription = "";
		$introId = array();
		$stepId = array();
		$pageId = array();
		$screenId = array();
		$stepStatus = 1;
		$introStepStatus = 1;
		$stepType = "";
		$start = 0;
		$limit = -1;
		$orderBy = "dynaIntroId_stepId.introId, dynaIntroId_stepId.stepOrder";
		
		foreach($options as $optionsKey=>$optionsElement){
			${$optionsKey} = $optionsElement;
		}
		
		$vals = array();
		$types = array();
		try{
    		$sql = "
    		SELECT 
            	dynaIntroSteps.stepId AS `dynaIntroSteps_stepId`,
            	dynaIntroSteps.type AS `dynaIntroSteps_type`,
            	dynaIntroSteps.stepDescription AS `dynaIntroSteps_stepDescription`,
            	dynaIntroSteps.status AS `dynaIntroSteps_status`,
            	dynaIntroStep_pageId.stepId AS `dynaIntroStep_pageId_stepId`,
            	dynaIntroStep_pageId.pageId AS `dynaIntroStep_pageId_pageId`,
            	dynaIntroStep_pageId.screenId AS `dynaIntroStep_pageId_screenId`,
            	dynaIntroStep_pageId.element AS `dynaIntroStep_pageId_element`,
            	dynaIntroStep_pageId.position AS `dynaIntroStep_pageId_position`,
            	dynaIntroStep_pageId.javascript AS `dynaIntroStep_pageId_javascript`,
            	dynaIntroStep_pageId.stepCondition AS `dynaIntroStep_pageId_stepCondition`,
            	dynaIntroStep_pageId.subIntro AS `dynaIntroStep_pageId_subIntro`, 
            	dynaIntroId_stepId.stepId AS `dynaIntroId_stepId_stepId`, 
            	dynaIntroId_stepId.introId AS `dynaIntroId_stepId_introId`, 
            	dynaIntroId_stepId.stepOrder AS `dynaIntroId_stepId_stepOrder`, 
            	dynaIntroId_stepId.status AS `dynaIntroId_stepId_status` 
            FROM
            	dynaIntroSteps
            	LEFT JOIN 
            		dynaIntroSteps AS dynaIntroSteps2 ON 
            			dynaIntroSteps2.stepId = dynaIntroSteps.stepId AND
            			dynaIntroSteps2.onlineInsertTime > dynaIntroSteps.onlineInsertTime 
            		
            INNER JOIN 
            	dynaIntroStep_pageId ON dynaIntroStep_pageId.stepId = dynaIntroSteps.stepId 
            	LEFT JOIN 
            		dynaIntroStep_pageId AS dynaIntroStep_pageId2 ON 
            			dynaIntroStep_pageId2.stepId = dynaIntroStep_pageId.stepId AND 
            			dynaIntroStep_pageId2.pageId = dynaIntroStep_pageId.pageId AND 
            			dynaIntroStep_pageId2.screenId = dynaIntroStep_pageId.screenId AND 
            			dynaIntroStep_pageId2.onlineInsertTime > dynaIntroStep_pageId.onlineInsertTime 
            			
            INNER JOIN 
            	dynaIntroId_stepId ON 
            	    dynaIntroId_stepId.stepId = dynaIntroSteps.stepId 
            	LEFT JOIN 
            		dynaIntroId_stepId AS dynaIntroId_stepId2 ON 
            			dynaIntroId_stepId2.stepId = dynaIntroId_stepId.stepId AND 
            			dynaIntroId_stepId2.introId = dynaIntroId_stepId.introId AND 
            			dynaIntroId_stepId2.onlineInsertTime > dynaIntroId_stepId.onlineInsertTime 
            			
            WHERE 
            	dynaIntroSteps2.stepId IS NULL AND dynaIntroStep_pageId2.stepId IS NULL AND dynaIntroId_stepId2.stepId IS NULL 
    	    ";
    	    if(isset($stepDescription) && $stepDescription !=""){
    	        $sql .= " AND 
    	            ( 
    	                ( dynaIntroSteps.introAlt LIKE :dynaIntroSteps_stepDescription ) 
    	            ) ";
    	        $vals[':dynaIntroSteps_stepDescription'] = $stepDescription;
    	        $types[':dynaIntroSteps_stepDescription'] = "s";
    	    }
    	    if(isset($stepType) && $stepType !=""){
    	        $sql .= " AND 
    	            ( 
    	                ( dynaIntroSteps.type = :dynaIntroSteps_type ) 
    	            ) ";
    	        $vals[':dynaIntroSteps_type'] = $stepType;
    	        $types[':dynaIntroSteps_type'] = "s";
    	    }
    	    if(isset($introId) && isset($introId['rows']) && !empty(array_filter($introId['rows']))>0){
    	        $sql .= " AND (dynaIntroId_stepId.introId = :dynaIntroId_stepId_introId ";
    	        if(isset($introId['fieldName']) && $introId['fieldName']){
        	        $fieldName = $introId['fieldName'];
        	    }else{
        	        $fieldName = 0;
        	    }
        	    $vals[':dynaIntroId_stepId_introId'] = $introId['rows'][0][$fieldName];
    	        $types[':dynaIntroId_stepId_introId'] = "s";
    	        foreach($introId['rows'] as $key=>$element){
    	            if(isset($introId['fieldName']) && $introId['fieldName']){
        	       	    $fieldName = $introId['fieldName'];
        	       	}else{
        	       	    $fieldName = $key;
        	       	}
    	        	$sql .= " OR dynaIntroId_stepId.introId = :dynaIntroId_stepId_introId".$key;
    	        	$vals[':dynaIntroId_stepId_introId'.$key] = $element[$fieldName];
    	        	$types[':dynaIntroId_stepId_introId'.$key] = "s";
    	        }
    	        $sql .= " ) ";
    	    }
    	    
    	    if(isset($pageId) && isset($pageId['rows']) && !empty(array_filter($pageId['rows']))>0){
    	        $sql .= " AND (dynaIntroStep_pageId.pageId = '' ";
    	        foreach($pageId['rows'] as $key=>$element){
    	            if(isset($introId['fieldName']) && $pageId['fieldName']){
        	       	    $fieldName = $introId['fieldName'];
        	       	}else{
        	       	    $fieldName = $key;
        	       	}
    	        	$sql .= " OR dynaIntroStep_pageId.pageId = :dynaIntroStep_pageId_pageId".$key;
    	        	$vals[':dynaIntroStep_pageId_pageId'.$key] = $element[$fieldName];
    	        	$types[':dynaIntroStep_pageId_pageId'.$key] = "s";
    	        }
    	        $sql .= ") ";
    	    }
    	    
    	    if(isset($stepStatus) && $stepStatus>-1){
    	        $sql .= " AND dynaIntroSteps.status = :dynaIntroSteps_status ";
    	        $vals[':dynaIntroSteps_status'] = $stepStatus;
    	        $types[':dynaIntroSteps_status'] = "i";
    	    }
    	    
    	    if(isset($introStepStatus) && $introStepStatus>-1){
    	        $sql .= " AND dynaIntroId_stepId.status = :dynaIntroId_stepId_status ";
    	        $vals[':dynaIntroId_stepId_status'] = $introStepStatus;
    	        $types[':dynaIntroId_stepId_status'] = "i";
    	    }
    	    
    	    
    		$sql .= " GROUP BY dynaIntroSteps.stepId, dynaIntroId_stepId.introId, dynaIntroStep_pageId.pageId, dynaIntroStep_pageId.screenId ";
    		if(isset($orderBy) && $orderBy != ""){
    	        $sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
    		}
    	    
    	    if(isset($limit) && $limit>0){
    	        $sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
    	    }
    	        
    	    $results = $this->db->pdoSelect($sql, $vals, $types);
    	    $results['sortableColumns'] = array(
    	        "dynaIntroStep_pageId_pageId",
    	        "dynaIntroId_stepId_stepOrder"
    	    );
    	    $results['sortBy'] = trim($orderBy);
	    }
		catch(Exception $e) {
			$msg = $e->getMessage();
			$results = array("status"=>0, "msg"=>$msg, "rows"=>array(), "sortBy"=>trim($orderBy), "sortableColumns"=>array());
		}
		if($debug==1){
		    $results['sql'] = $sql;
		    $results['vals'] = $vals;
		    $results['types'] = $types;
		}
		return $results;
	}
}
?>