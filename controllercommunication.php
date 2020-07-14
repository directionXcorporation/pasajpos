<?php
class email{
	protected $db_constructor, $filePath_constructor, 
		$langDecode_constructor, $SecureSessionHandler_constructor, $lang;
	public function __construct($db_constructor,$filePath_constructor,$langDecode_constructor,$SecureSessionHandler_constructor,$lang="") {
	        $this->db = $db_constructor;
	        $this->langDecode = $langDecode_constructor;
	        $this->filePath = $filePath_constructor;
	        $this->SecureSessionHandler = $SecureSessionHandler_constructor;
		$this->lang = $lang;
	    }
}
?>