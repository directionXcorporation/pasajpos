<?php

class smartTicketControllerClass{
	protected $langDecode_constructor, $lang, $smartTicketModel_constructor, $filePath_constructor, $loginClass_constructor, $mainformModel_constructor;
	public function __construct($langDecode_constructor, $lang, $smartTicketModel_constructor, $filePath_constructor, $loginClass_constructor, $mainformModel_constructor) {
	    $this->langDecode = $langDecode_constructor;
		$this->lang = $lang;
		$this->smartTicketModel = $smartTicketModel_constructor;
		$this->filePath = $filePath_constructor;
		$this->loginClass = $loginClass_constructor;
		$this->mainformModel = $mainformModel_constructor;
	}
	public function searchIntro($description="", $debug=0){
		/**
			
		*/
		$status = 0;
		$msg = '';
		$intro = array();
		$options = array(
		    "introDescription"=>$description,
		    "searchable"=>1
		);
		$result = $this->smartTicketModel->SearchIntro($options, $debug);
		return $result;
	}
	
	public function searchStepsByPage($pageId="", $stepType="hint", $debug=0){
		/**
			
		*/
		$status = 0;
		$msg = '';
		$steps = array();
		$options = array(
		    "pageId"=>array("rows"=>array(array($pageId))),
		    "stepType"=>$stepType
		);
		$result = $this->smartTicketModel->SearchIntroSteps($options, $debug);
		return $result;
	}
	
	public function searchStepsByIntro($introIds=array(), $debug=0){
		/**
			
		*/
		$status = 0;
		$msg = '';
		$steps = array();
		$options = array(
		    "introId"=>array("rows"=>array($introIds))
		);
		$result = $this->smartTicketModel->SearchIntroSteps($options, $debug);
		return $result;
	}
}

?>