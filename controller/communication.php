<?php
class emailControllerClass{
	protected $db_constructor, $filePath_constructor, 
		$langDecode_constructor, $SecureSessionHandler_constructor, $lang;
	public function __construct($db_constructor,$filePath_constructor,$langDecode_constructor,$SecureSessionHandler_constructor,$lang="") {
	    $this->db = $db_constructor;
	    $this->langDecode = $langDecode_constructor;
	    $this->filePath = $filePath_constructor;
	    $this->SecureSessionHandler = $SecureSessionHandler_constructor;
		$this->lang = $lang;
	}
	
	public function prepareEmail($emailAddress, $subject, $parameters, $fileName, $send=1){
		$templateFilePath = $this->filePath->getPath("view/email/".$fileName,1);
		$content = $this->filePath->showHTML($templateFilePath,$parameters);
		if($send){
			$sendResult = $this->sendEmail($emailAddress, $subject, $content);
		}
		return array("content"=>$content, "sendResult"=>$sendResult);
	}
	private function sendEmail($emailAddress, $subject, $content){
		$headers = "From: info@pasajpos.ca";

		$result = mail($emailAddress,$subject,$content,$headers);
		return $result;
	}
}
?>