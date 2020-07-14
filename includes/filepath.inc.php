<?php
require_once('includes/htmlpurifier/library/HTMLPurifier.auto.php');
class filePath{
    public function replaceParameters($string, $parameters=array()){
		if(is_array($parameters) && $parameters!=  array()){
			foreach($parameters as $main => $rep){
				$string = str_replace("{{".$main."}}",$rep,$string);
			}
		}
		return $string;
	}
    public function getpath($path,$isphp=0){
        if($isphp==1){
            $prepath = $_SERVER['DOCUMENT_ROOT']."/";
        }else{
            $prepath = "/";
        }
        return $prepath.$path;
    }
    public function toSafeString($text,$hardclean=1){
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        if(!is_array($text)){
            $clean_html = $purifier->purify(trim($text));
            $clean_html = str_ireplace("information_schema","",$clean_html);
            $clean_html = str_ireplace("BENCHMARK","",$clean_html);
            $clean_html = str_ireplace("SLEEP","",$clean_html);
            $clean_html = str_ireplace("VERSION","",$clean_html);
            if($hardclean){
            	$clean_html = str_ireplace("IF(","",$clean_html);
            }
            $clean_html = str_ireplace("ASCII","",$clean_html);
            $clean_html = str_ireplace("ANALYS","",$clean_html);
            $result = $clean_html;
        }else{
            foreach($text as $key2=>$text_element){
                $clean_html = "";
                if(!is_array($text_element)){
                    $clean_html = $purifier->purify(trim($text_element));
                    $clean_html = str_ireplace("information_schema","",$clean_html);
                    $clean_html = str_ireplace("BENCHMARK","",$clean_html);
                    $clean_html = str_ireplace("SLEEP","",$clean_html);
                    $clean_html = str_ireplace("VERSION","",$clean_html);
                    $clean_html = str_ireplace("IF(","",$clean_html);
                    $clean_html = str_ireplace("ASCII","",$clean_html);
                    $clean_html = str_ireplace("ANALYS","",$clean_html);
                    $result[$key2]=$clean_html;
                }else{
                    $result[$key2]=$text_element;
                }
            }
        }
        return $result;
    }
    public function toSafeInt($int){
        if(!is_array($int)){
            $int = str_ireplace("information_schema","",$int);
            $int = str_ireplace("BENCHMARK","",$int);
            $int = str_ireplace("SLEEP","",$int);
            $int = str_ireplace("VERSION","",$int);
            $int = str_ireplace("IF(","",$int);
            $int = str_ireplace("ASCII","",$int);
            $int = str_ireplace("ANALYS","",$int);
            $result = intval(round($int));
        }else{
            foreach($int as $key2=>$int_element){
                if(!is_array($int_element)){
                    $int_temp = str_ireplace("information_schema","",$int_element);
                    $int_temp = str_ireplace("BENCHMARK","",$int_temp);
                    $int_temp = str_ireplace("SLEEP","",$int_temp);
                    $int_temp = str_ireplace("VERSION","",$int_temp);
                    $int_temp = str_ireplace("IF(","",$int_temp);
                    $int_temp = str_ireplace("ASCII","",$int_temp);
                    $int_temp = str_ireplace("ANALYS","",$int_temp);
                    $result[$key2] = intval(round($int_temp));
                }else{
                    $result[$key2] = $int_element;
                }
            }
        }
        return $result;
    }
    
    public function getUserIp(){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    
    public function showHTML($file,$parameters=array()){
		/**
		* Shows the html template of the template found in $file and replaces the dynamic values
		* (array)$parameters{
		*	The dynamic parameters that should be replaced in the page template file
		* }
		* return html of template
		*/

		$fp = fopen ($file, "r");
	    	if(!$fp){
	    		$buffer = "File '".$file."' cannot be opened or does not exist";
	    		return '';
	    	}
    		if(filesize($file)>0){
    			$buffer = fread($fp, filesize($file));
    			$buffer = $this->replaceParameters($buffer, $parameters);
		}else{
			$buffer = '';
		}
		fclose ($fp);
		return $buffer;
	}
}
?>