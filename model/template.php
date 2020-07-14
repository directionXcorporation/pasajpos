<?php
class templateModelClass{
	protected $db_constructor, $langDecode_constructor, $lang;
	public function __construct($db_constructor,$langDecode_constructor,$lang) {
	        $this->db = $db_constructor;
	        $this->langDecode = $langDecode_constructor;
		$this->lang = $lang;
	}
	public function getTemplateDetails($pageCode='',$options=array()){
		/**
		* Gets the template filename and other details related to templateName in the user language or the lang parameter in options
		* $pageCode: code of page to look for in database
		* $options{
		*	pageLang: The language to override user's language
		*	isActive: true,false:get pages with isActive=1
		* }
		* (array)return{
		*	status: 0:unsuccessful, 1:successful
		*	msg: message decoded in user language
		*	fileName: name of template file in the requested language
		*	pageLang: Language code of the page
		*	ajaxLoad: Should this page be loaded in an Ajax iframe or is ti a full page
		*	parentPageId: What is the parent id of this page, if ajaxLoad is true then load the parent first
		*	(array)parameters: {language related parameters and their values that should be replaced in the template-so one template can be used for mutiple languages}
		* }
		*/

		$pageLang = "";
		$isActive = 1;
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
	        if(!$pageLang){
			$pageLang = $this->lang;
		}
		$fileName = "";
		$parameters = array();
	        $sql = "SELECT pageLang,pageCode,pageTitle,pageDescription,pageKeywords,ajaxLoad,fileName,isActive FROM pageDetails WHERE pageCode=:pageCode AND (pageLang=:pageLang OR pageLang='') AND isActive=:isActive LIMIT 0,1";
	        $vals = array(
	        		":pageCode"=>$pageCode,
	        		":pageLang"=>$pageLang,
	        		":isActive"=>$isActive
	        	);
	        $types= array(
	        		":pageCode"=>"s",
	        		":pageLang"=>"s",
	        		":isActive"=>"i"
	        	);
	        $result = $this->db->pdoSelect($sql,$vals,$types);
	        
	        if($result['status']){
		        if(isset($result['rows'][0]['pageCode'])){
		        	$parameters['PAGE_TITLE']=$result['rows'][0]['pageTitle'];
		        	$parameters['PAGE_LANGCODE']=$result['rows'][0]['pageLang'];
		        	$parameters['PAGE_DESCRIPTION']=$result['rows'][0]['pageDescription'];
		        	$parameters['PAGE_KEYWORDS']=$result['rows'][0]['pageKeywords'];
		        	$fileName = $result['rows'][0]['fileName'];
		        	$ajaxLoad = $result['rows'][0]['ajaxLoad'];
		        	$parentPageId = $result['rows'][0]['parentPageId'];
		        	$status = 1;
		        	$msg = "";
		        	
		        }else{
		        	$status = 0;
	                	$msgCode = "There was a system error fetching page details. Please try again. Administrator is informed of the problem.";
	                	$msgCat="SYS_ERR_MSG";
	                	$msg = $this->langDecode->decode($msgCode,$msgCat,$pageLang);
		        }
	        }else{
	        	$status = 0;
	        	$msg = $result['msg'];
	        }
	        return array(
		        	"status"=>$status,
		        	"msg"=>$msg,
		        	"pageLang"=>$pageLang,
		        	"fileName"=>$fileName,
		        	"ajaxLoad"=>$ajaxLoad,
		        	"parentPageId"=>$parentPageId,
		        	"parameters"=>$parameters
	        	);
	        	
	}
	public function getPageFiles($pageCode,$options=array()){
		/**
		* Gets the template filename and other details related to templateName in the user language or the lang parameter in options
		* $pageCode: code of page to look for in database
		* $options{
		*	pageLang: The language to override user's language
		*	isActive: true,false:get pages with isActive=1
		* }
		* (array)return{
		*	(array)results
		* }
		*/

		$pageLang = "";
		$isActive = 1;
		$pageCodeExplicit = 0;
		
		foreach($options as $key=>$element){
	            ${$key} = $element;
	        }
	        if(!$pageLang){
			$pageLang = $this->lang;
		}
		$fileName = "";
		$parameters = array();
	        $sql = "(SELECT id,filePath,fileType,pageCode,pageLang,addToHead,includeOrder,isActive,hasParameter  
	        	FROM pageFiles 
	        	FORCE INDEX(pageCode_Lang_Active)
	         	WHERE (pageCode=:pageCode ";
	         if(!$pageCodeExplicit){
	         	$sql .= " OR pageCode='' ";
	         }
	         
	         $sql .= " ) AND (pageLang=:pageLang OR pageLang='') AND isActive=:isActive) ";
	         
	         $sql .= " UNION ALL ";
	         $sql .= " (SELECT MAX(dynaPageModulesFiles.id) AS id, 
	         		CONCAT('dynaModules/pagefiles/', dynaPageModulesFiles.fileType, '/', dynaPageModulesFiles.fileName) AS filePath,
	         		MAX(dynaPageModulesFiles.fileType) AS fileType,
	         		'' AS pageCode,
	         		'' AS pageLang,
	         		0 AS addToHeader,
	         		MAX((1290 + dynaPageModulesFiles.includeOrder)) AS includeOrder,
	         		MAX(dynaPageModulesFiles.status) AS isActive,
	         		0 AS hasParameter
	         		FROM dynaPageModulesFiles 
	         		LEFT JOIN dynaPageModulesFiles AS dynaPageModulesFiles2 ON dynaPageModulesFiles.fileName = dynaPageModulesFiles2.fileName AND dynaPageModulesFiles2.onlineInsertTime GREATERTHAN dynaPageModulesFiles.onlineInsertTime AND dynaPageModulesFiles.moduleId = dynaPageModulesFiles2.moduleId 
	         		
	         		INNER JOIN dynaPageModulesStatus ON dynaPageModulesStatus.moduleId = dynaPageModulesFiles.moduleId 
	         		LEFT JOIN dynaPageModulesStatus AS dynaPageModulesStatus2 ON dynaPageModulesStatus.moduleId = dynaPageModulesStatus2.moduleId AND dynaPageModulesStatus2.onlineInsertTime GREATERTHAN dynaPageModulesStatus.onlineInsertTime 
	         		WHERE dynaPageModulesFiles.loadsWith = 'page' AND dynaPageModulesFiles2.fileName IS NULL AND dynaPageModulesFiles.status = 1 AND dynaPageModulesStatus2.moduleId IS NULL AND dynaPageModulesStatus.status = 1 GROUP BY filePath) ";
	         $sql .="	ORDER BY includeOrder ";
	        $vals = array(
	        		":pageCode"=>$pageCode,
	        		":pageLang"=>$pageLang,
	        		":isActive"=>$isActive
	        	);
	        $types= array(
	        		":pageCode"=>"s",
	        		":pageLang"=>"s",
	        		":isActive"=>"i"
	        	);

	        $result = $this->db->pdoSelect($sql,$vals,$types);
	        if($result['status']){
	        	$result = $result['rows'];
	        }else{
	        	$result = array();
	        }
	        return $result;
	}
}
?>