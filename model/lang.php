<?php

class langDecodeClass{
	protected $db_constructor, $filePath_constructor, $lang;
	public function __construct($db_constructor,$filePath_constructor,$lang) {
        	$this->db = $db_constructor;
        	$this->filePath = $filePath_constructor;
        	$this->lang = $lang;
    }
    public function decode($msg,$msgCat,$lang){
    		return $msg;
    }

    public function searchDynaLang($options=array()){
        /**
			* Search translate table
			* Options {
			*	(array) $type: {fieldName: 'e.g dynatranslate_type', rows:[{dynatranslate_type: 'text'},...]} The type. empty for all types
			*	(array) $langCode: {fieldName: 'e.g dynatranslate_langISO2', rows:[{dynatranslate_langISO2: 'en'},...]}
			*	(array) $countryCode: {fieldName: 'e.g dynatranslate_countryISO2', rows:[{dynatranslate_countryISO2: 'US'},...]}
			*   countOnly: 0
			*   availableLanguages: 0
			* }
			* returns{
			*	status: 0:failed, 1: success
			*	msg: message decoded in user lang
			* }
		*/
		try{
            $status = 1;
    		$limit = -1;
    		$start = 0;
    		$orderBy = " dynaTranslate.onlineInsertTime DESC, dynaTranslate.id  ";
    		$countOnly = 0;
    		$availableLanguages = 0;
    		
    		$vals = array();
    		$types = array();
    		
    		foreach($options as $key=>$element){
                ${$key} = $element;
            }
            $sql = "SELECT ";
            if($availableLanguages){
                $sql .= " DISTINCT(CONCAT(dynaTranslate.langISO2, '_' ,dynaTranslate.countryISO2)) AS `lang` ";
            }else{
                $sql .=" MAX(dynaTranslate.id) AS `dynaTranslate_id`, 
                    MAX(dynaTranslate.keyword) AS `dynaTranslate_keyword`, 
                    MAX(dynaTranslate.translation) AS `dynaTranslate_translation`, 
                    MAX(dynaTranslate.countryISO2) AS `dynaTranslate_countryISO2`, 
                    MAX(dynaTranslate.langISO2) AS `dynaTranslate_langISO2`, 
                    MAX(dynaTranslate.type) AS `dynaTranslate_type`, 
                    MAX(dynaTranslate.status) AS `dynaTranslate_status` ";
            }
                
            $sql .= " FROM dynaTranslate 
                LEFT JOIN dynaTranslate AS dynaTranslate2 ON dynaTranslate.keyword = dynaTranslate2.keyword AND 
                dynaTranslate.countryISO2 = dynaTranslate2.countryISO2 AND 
                dynaTranslate.langISO2 = dynaTranslate2.langISO2 AND 
                dynaTranslate.type = dynaTranslate2.type AND 
                dynaTranslate2.onlineInsertTime > dynaTranslate.onlineInsertTime 
            ";
            
            $sql .= " WHERE dynaTranslate2.id IS NULL ";
            
            if(isset($type) && isset($type['rows']) && !empty(array_filter($type['rows']))>0){
	        	$sql .= " AND (dynaTranslate.type = :type ";
	        	if(isset($type['fieldName']) && $type['fieldName']){
	        	    $fieldName = $type['fieldName'];
	        	    $vals[':type'] = $type['rows'][0][$fieldName];
	        	}else{
	        	    $vals[':type'] = $type['rows'][0];
	        	}
	        	
	        	$types[':type'] = "s";
	        	foreach($type['rows'] as $key=>$element){
	        	    if(isset($type['fieldName']) && $type['fieldName']){
    	        	    $fieldName = $type['fieldName'];
    	        	    $vals[':type'.$key] = $element[$fieldName];
    	        	}else{
    	        	    $vals[':type'.$key] = $element;
    	        	}
	        		$sql .= " OR dynaTranslate.type = :type".$key;
	        		$types[':type'.$key] = "s";
	        	}
	        	$sql .= ") ";
            }
            
            if($onlineInsertTime_start){
	        	$sql .= " AND (DATE_FORMAT(dynaTranslate.onlineInsertTime, '%Y-%m-%d %H:%i:%s') >= DATE_FORMAT(:onlineInsertTime_start, '%Y-%m-%d %H:%i:%s') ";
	        	$date = date_create($onlineInsertTime_start);
	        	$onlineInsertTime_start = date_format($date, 'Y-m-d H:i:s');
	        	$vals[':onlineInsertTime_start'] = $onlineInsertTime_start;
	        	$types[':onlineInsertTime_start'] = "s";
	
	        	$sql .= ")";
	        }
	        if($onlineInsertTime_end){
	        	$sql .= " AND (DATE(dynaTranslate.onlineInsertTime) <= DATE(:onlineInsertTime_end)) ";
	        	$date = date_create($onlineInsertTime_end);
	        	$onlineInsertTime_end = date_format($date, 'Y-m-d');
	        	$vals[':onlineInsertTime_end'] = $onlineInsertTime_end;
	        	$types[':onlineInsertTime_end'] = "s";
	        }
            
            if($status>-1){
	        	$sql .= " AND dynaTranslate.status = :status ";
	        	$vals[':status'] = $status;
	        	$types[':status'] = "i";
	        }
	        
	        if(!$countOnly){
	            $sql .= " GROUP BY dynaTranslate.keyword, dynaTranslate.langISO2, dynaTranslate.countryISO2 ";
	        	$sql .= " ORDER BY ".$this->filePath->toSafeString($orderBy);
	        }
	        
	        if($limit>0 && !$countOnly){
	        	$sql .= " LIMIT ".$this->filePath->toSafeInt($start).",".$this->filePath->toSafeInt($limit);
	        }
	        
	        $results = $this->db->pdoSelect($sql,$vals,$types);
	        $results['sortableColumns'] = array(
	        	"dynaTranslate_keyword",
	        	"dynaTranslate_onlineInsertTime"
	        );
	        $results['sortBy'] = trim($orderBy);
		}
        catch(Exception $e) {
			$msg = $e->getMessage();
			$results = array("status"=>0, "msg"=>$msg, "rows"=>array(), "sortBy"=>trim($orderBy), "sortableColumns"=>array());
		}
		return $results;
    }
}
?>