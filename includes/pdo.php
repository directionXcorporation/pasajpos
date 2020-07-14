<?php

include("includes/db.inc.php");

define('PHP_FIREWALL_REQUEST_URI', strip_tags( $_SERVER['REQUEST_URI'] ) );
define('PHP_FIREWALL_ACTIVATION', true );
define('PHP_FIREWALL_ADMIN_MAIL', '' );  // write your email, php firewall mail you  each attack detected
  define('PHP_FIREWALL_PUSH_MAIL', false ); // active email rapport true or false deactive
  define('PHP_FIREWALL_LOG_FILE', 'logs' );  // filename logs for php firewall
  define('PHP_FIREWALL_PROTECTION_RANGE_IP_DENY', false ); // IPs reserved blocker
  define('PHP_FIREWALL_PROTECTION_RANGE_IP_SPAM', false );  // IPs spam blocker
  define('PHP_FIREWALL_PROTECTION_URL', true );  // URL protection
  define('PHP_FIREWALL_PROTECTION_REQUEST_SERVER', true ); // Request protection
  define('PHP_FIREWALL_PROTECTION_SANTY', true ); // Santy worm protection
  define('PHP_FIREWALL_PROTECTION_BOTS', false ); // Bad bots protection
  define('PHP_FIREWALL_PROTECTION_REQUEST_METHOD', true ); // Bad method protection
  define('PHP_FIREWALL_PROTECTION_DOS', true ); // Mini dos protection
  define('PHP_FIREWALL_PROTECTION_UNION_SQL', true ); // Union sql protection
  define('PHP_FIREWALL_PROTECTION_CLICK_ATTACK', true ); // Include files protection
  define('PHP_FIREWALL_PROTECTION_XSS_ATTACK', true ); // XSS protection
  define('PHP_FIREWALL_PROTECTION_COOKIES', true ); // sanitize cookies
  define('PHP_FIREWALL_PROTECTION_POST', true ); // Sanitize POST vars
  define('PHP_FIREWALL_PROTECTION_GET', true );  // sanitize GET vars
if ( is_file( @dirname(__FILE__).'/php-firewall/firewall.php' ) ){
        include_once( @dirname(__FILE__).'/php-firewall/firewall.php' );
}

    $pdoDb = new pdoDb();
    $security = new security();
    $url = $pdoDb->toSafeString($security->full_url($_SERVER));
    $ip = $security->getUserIp();
    /*
    if ($security->findmaldata($url) && strlen($url)>36) {
        $sql = "SELECT count(id) AS count FROM userip WHERE TIME_TO_SEC(TIMEDIFF(NOW(), accesstime)) LESSTHAN= 60 AND ip=:ip";
        $result = $pdoDb->pdoSelect($sql,array(":ip"=>$ip),array(":ip"=>"s"));
        if($result['status']){
	        $result = $result['rows']
	        $result_element = $result[0];
	        $count = $result_element['count'];
        }else{
        	$count = 0;
        }
        if($count>=1){
            $userblock = true;
        }else{
            $userblock = false;
            $pdoDb->pdoInsert("userip",array("ip","requesturl"),array("ip"=>$ip,"requesturl"=>$url),array("ip"=>"s","requesturl"=>"s"));
        }
    }
    */
    
if($userblock==true){
    header('HTTP/1.1 405 Method Not Allowed');
    header('Status: 405 Method Not Allowed');
    header('Retry-After: 60');//60 seconds
    die("Your request has been blocked by firewal. Please try again later or contact administrator");
}


	class pdoDb {
		/*public function __construct($user, $password, $database, $host = 'localhost') {
			$this->user = $user;
			$this->password = $password;
			$this->database = $database;
			$this->host = $host;
		}
        */
		protected function connect_read() {
		      global $databasename_database_read;
              global $hostname_database_read;
              global $username_database_read;
              global $password_database_read;
              try {
		    $dbConnection = new PDO('mysql:dbname='.$databasename_database_read.';host='.$hostname_database_read.';charset=utf8', $username_database_read, $password_database_read);

            $dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbConnection->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND,"SET NAMES utf8;SET time_zone = 'UTC'");
            }
            catch(PDOException $e)
            {
            die($e->getMessage());
            }
			return $dbConnection;
		}
        protected function connect_write() {
		      global $databasename_database_write;
              global $hostname_database_write;
              global $username_database_write;
              global $password_database_write;
              try {
		    $dbConnection = new PDO('mysql:dbname='.$databasename_database_write.';host='.$hostname_database_write.';charset=utf8', $username_database_write, $password_database_write);

            $dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbConnection->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND,"SET NAMES utf8;SET time_zone = 'UTC'");
            }
            catch(PDOException $e)
            {
            die($e->getMessage());
            }
			return $dbConnection;
		}
        protected function connect_delete() {
		      global $databasename_database_delete;
              global $hostname_database_delete;
              global $username_database_delete;
              global $password_database_delete;
              try {
		    $dbConnection = new PDO('mysql:dbname='.$databasename_database_delete.';host='.$hostname_database_delete.';charset=utf8', $username_database_delete, $password_database_delete);

            $dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            catch(PDOException $e)
            {
            die($e->getMessage());
            }
			return $dbConnection;
		}
        protected function connect_update() {
		      global $databasename_database_update;
              global $hostname_database_update;
              global $username_database_update;
              global $password_database_update;
              try {
		    $dbConnection = new PDO('mysql:dbname='.$databasename_database_update.';host='.$hostname_database_update.';charset=utf8', $username_database_update, $password_database_update);

            $dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbConnection->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND,"SET NAMES utf8;SET time_zone = 'UTC'");
            }
            catch(PDOException $e)
            {
            die($e->getMessage());
            }
			return $dbConnection;
		}
        
        public function pdoInsert($tablename,$colnames=array(),$values=array(),$type=array(), $debug=0){
            $db = $this->connect_write();
            $tables = $this->buildTables($tablename);
            $id = 0;
            $result = array('msg'=>'insert started', 'status'=>0);
            if(count(array_intersect($colnames, $tables["colnames"])) == count($colnames) && ( (is_array($values[0]) && count($values[0])==count($colnames)) || count($values)==count($colnames) )){
                $tablename_safe = $tables['tablename'];
                $colnames_temp = array_diff($tables['colnames'], $colnames);
                $colnames_safe = array_diff($tables['colnames'],$colnames_temp);
                $cols = "";
                $vals = "";
                if(is_array($values[0])){
                	foreach($colnames_safe as $colnames_element){
		        	$cols .= $colnames_element.",";
		        }
		        
			foreach($values as $key => $values_element){
				$vals .= "(";
				foreach($colnames_safe as $colnames_element){
		                    $vals .= ":".$colnames_element.$key.",";
		                }
		                $vals = rtrim($vals, ',');
		                $vals .= "), ";
		        }
	                $cols = rtrim($cols, ',');
	                $vals = rtrim($vals, ', ');
	                $sql = "INSERT INTO ".$tablename_safe." (".$cols.") VALUES ".$vals."";
                }else{
	                foreach($colnames_safe as $colnames_element){
	                    $cols .= $colnames_element.",";
	                    $vals .= ":".$colnames_element.",";
	                }
	                $cols = rtrim($cols, ',');
	                $vals = rtrim($vals, ',');
	                $sql = "INSERT INTO ".$tablename_safe." (".$cols.") VALUES (".$vals.")";
                }
            
                $stmt = $db->prepare($sql);
                if(is_array($values[0])){
                	foreach($values as $key => $values_element){
				        foreach($colnames_safe as $colnames_element){
		                    $val = ":".$colnames_element.$key;
		                    if($type[$colnames_element]=="s"){
		                        $stmt->bindValue($val, $this->toSafeString($values_element[$colnames_element]), PDO::PARAM_STR);
		                    }else{
		                        $stmt->bindValue($val, intval($values_element[$colnames_element]), PDO::PARAM_INT);
		                    }
		                }
		        }
                }else{
	                foreach($colnames_safe as $colnames_element){
	                    $val = ":".$colnames_element;
	                    if($type[$colnames_element]=="s"){
	                        $stmt->bindValue($val, $this->toSafeString($values[$colnames_element]), PDO::PARAM_STR);
	                    }else{
	                        $stmt->bindValue($val, intval($values[$colnames_element]), PDO::PARAM_INT);
	                    }
	                    
	                }
                }
                try{
                	$status = $stmt->execute();
                	if(!$status){
                		$result['msg'] = $stmt->error;
                	}
                }
                catch(Exception $e){
                	$status = 0;
                	$result['msg'] = $e->getMessage();
                }
                $stmt = NULL;
            }else{
                $result['msg'] = "Table or columns not found";
            }
            $result['status'] = $status;
            return $result;
        }
        public function pdoDelete($tablename,$id,$colname='id'){
            $result = false;
            $db = $this->connect_delete();
            $tables = $this->buildTables($tablename);
            $tablename_safe = $tables['tablename'];
            if($id>0 && in_array($colname,$tables['colnames'])){
                $sql = "DELETE FROM ".$tablename_safe." WHERE ".$colname."=:id LIMIT 1";
                $select_array[':id'] = intval($id);
                $stmt = $db->prepare($sql);
                $result = $stmt->execute($select_array);
                $stmt = NULL;
            }
            $data = array("id"=>$id,"result"=>$result);
            return $data;
        }
        public function pdoUpdate($tablename,$id,$colnames=array(),$values=array(),$types=array(),$idcolname="id"){
            $result = false;
            $db = $this->connect_update();
            $tables = $this->buildTables($tablename);
            $tablename_safe = $tables['tablename'];
            if($tablename_safe){
            if(count(array_intersect($colnames, $tables["colnames"])) == count($colnames) && count($values)==count($colnames) && in_array($idcolname,$tables['colnames'])){
                if($id>0){
                    $tablename_safe = $tables['tablename'];
                    $colnames_temp = array_diff($tables['colnames'], $colnames);
                    $colnames_safe = array_diff($tables['colnames'],$colnames_temp);
                    $cols = "";
                    foreach($colnames_safe as $colnames_element){
                        $cols .= $colnames_element."=:".$colnames_element.",";
                    }
                    $cols = rtrim($cols, ',');
                    $sql = "UPDATE ".$tablename_safe." SET ".$cols." WHERE ".$idcolname."=:id LIMIT 1";
                    $stmt = $db->prepare($sql);
                    $stmt->bindValue(":id", intval($id), PDO::PARAM_INT);
                    
                    foreach($colnames_safe as $colnames_element){
                        
                        if($types[$colnames_element]=="s"){
                            $val = $this->toSafeString($values[$colnames_element]);
                            $stmt->bindValue(":".$colnames_element, $val, PDO::PARAM_STR);
                        }else{
                            $val = intval($values[$colnames_element]);
                            $stmt->bindValue(":".$colnames_element, $val, PDO::PARAM_INT);
                        }
                    }
                    $result = $stmt->execute();
                    $stmt = NULL;
                }
            }
            $data = array("id"=>$id,"result"=>$result);
            return $data;
        }else{
            return false;
        }
        }
        public function pdoSelect($query,$params=array(),$types=array(),$debug=0){
        	$msg = "";
        	$rows = array();
        	$status = 1;
           $query = $this->toSafeString($query,0);
$query = str_replace("LESSTHAN","<",$query);
$query = str_replace("GREATERTHAN",">",$query);
$query = str_replace("&lt;","<",$query);
$query = str_replace("&gt;",">",$query);
$query = str_replace(";","",$query);
$query = str_replace("information_schema","",$query);

            if (strtolower(strpos($query,'update ')) === false || strtolower(strpos($query,'insert ')) === false || strtolower(strpos($query,'delete ')) === false) {
               // die("ERROR");
            }
            try {
                
                $db = $this->connect_read();
                $sql = trim($query);
                foreach($params as $key=>$param){
                    if($types[$key]=="s"){
                        $key = $this->toSafeString($key);
                        $select_array[$key] = $this->toSafeString($param);
                    }else{
                        $key = $this->toSafeString($key);
                        $select_array[$key] = intval($param);
                    }
                }
                
                $stmt = $db->prepare($sql);
                $stmt->execute($select_array);
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt = NULL;
                $status = 1;
            }
            catch(Exception $e) {
                    $msg = $e->getMessage();
                    $status = 0;
            }

            return array("rows"=>$rows,"status"=>$status, "msg"=>$msg); 
        }

        private function buildTables( $tablename='error' ) {
            $tablename = $this->toSafeString($tablename);
            $tbl = false;
            $cols = array();
            switch($tablename){
            	//Mainform
                case 'pageFiles':
                    $tbl = "pageFiles";
                break;
                case 'loginAttempts':
                    $tbl = "loginAttempts";
                break;
                case 'pageDetails':
                    $tbl = "pageDetails";
                break;
                case 'deviceDetails':
                    $tbl = "deviceDetails";
                break;
                case 'log':
                	$tbl = "log";
                break;
                
                //Users
                case 'usersFingerprint':
                    $tbl = "usersFingerprint";
                break;
                case 'usersAreaCode':
                    $tbl = "usersAreaCode";
                break;
                case 'usersPassword':
                    $tbl = "usersPassword";
                break;
                case 'usersStatus':
                    $tbl = "usersStatus";
                break;
                case 'usersFunctionAccess':
                    $tbl = "usersFunctionAccess";
                break;
                case 'usersIdstoresId':
                    $tbl = "usersIdstoresId";
                break;
                case 'areaCodes':
                    $tbl = "areaCodes";
                break;
                case 'usersDetail':
                    $tbl = "usersDetail";
                break;
                case 'usersGroup':
                	$tbl = "usersGroup";
                break;
                case 'dynaPageFunctionId_usersGroupId':
                    $tbl = "dynaPageFunctionId_usersGroupId";
                break;
                case 'usersPin':
                	$tbl = "usersPin";
                break;
                case 'recoverPasswords':
                	$tbl = "recoverPasswords";
                break;
                
                //Groups
                case 'groupsDetail':
                	$tbl = "groupsDetail";
                break;
                case 'groupsParent':
                	$tbl = "groupsParent";
                break;
                case 'groupsStatus':
                	$tbl = "groupsStatus";
                break;
                
                //Brands
                case 'brandsId':
                	$tbl = "brandsId";
                break;
                case 'brandsDetail':
                	$tbl = "brandsDetail";
                break;
                case 'brandsParent':
                	$tbl = "brandsParent";
                break;
                case 'brandsStatus':
                	$tbl = "brandsStatus";
                break;
                
                //Stores
                case 'storesDetail':
                	$tbl = "storesDetail";
                break;
                case 'storesIdbrandsId':
                	$tbl = "storesIdbrandsId";
                break;
                case 'storesStatus':
                	$tbl = "storesStatus";
                break;
                case 'storesContact':
                	$tbl = "storesContact";
                break;
                case 'storesIdreceiptsId':
                	$tbl = "storesIdreceiptsId";
                break;
                
                //Tills
                case 'tillsId':
                	$tbl = "tillsId";
                break;
                case 'tillsDetail':
                	$tbl = "tillsDetail";
                break;
                case 'tillsStatus':
                	$tbl = "tillsStatus";
                break;
                case 'tillId_deviceId':
                    $tbl = "tillId_deviceId";
                break;
                
                //Items
                case 'itemsId':
                	$tbl = "itemsId";
                break;
                case 'itemsDetail':
                	$tbl = "itemsDetail";
                break;
                case 'itemsStatus':
                	$tbl = "itemsStatus";
                break;
                case 'itemsIdbrandsId':
                	$tbl = "itemsIdbrandsId";
                break;
                case 'itemsPrice':
                	$tbl = "itemsPrice";
                break;
                
                //Inventory Shot
                case 'inventoryShotDetail':
                	$tbl = "inventoryShotDetail";
                break;
                case 'inventoryShotHeader':
                	$tbl = "inventoryShotHeader";
                break;
                
                //Inventory Movements
                case 'inventoryMovementsDetails':
                	$tbl = "inventoryMovementsDetails";
                break;
                case 'inventoryMovementsHeader':
                	$tbl = "inventoryMovementsHeader";
                break;
                case 'inventoryMovementsStatus':
                	$tbl = "inventoryMovementsStatus";
                break;
                
                //tableViews
                case 'tableViewDetail':
                	$tbl = "tableViewDetail";
                break;
                case 'tableViewStatus':
                	$tbl = "tableViewStatus";
                break;
                case 'tableViewId_usersGroupId':
                	$tbl = "tableViewId_usersGroupId";
                break;
                
                //Sales Screen
                case 'salesScreenId':
                	$tbl = "salesScreenId";
                break;
                case 'salesScreenDetail':
                	$tbl = "salesScreenDetail";
                break;
                case 'salesScreenModule':
                	$tbl = "salesScreenModule";
                break;
                case 'salesScreenStatus':
                	$tbl = "salesScreenStatus";
                break;
                case 'salesScreenIdstoresId':
                	$tbl = "salesScreenIdstoresId";
                break;
                
                //Receipts
                case 'receiptsId':
                	$tbl = "receiptsId";
                break;
                case 'receiptsDetail':
                	$tbl = "receiptsDetail";
                break;
                case 'receiptsModule':
                	$tbl = "receiptsModule";
                break;
                case 'receiptsStatus':
                	$tbl = "receiptsStatus";
                break;
                
                //basicSettings
                case 'cashTypesId':
                	$tbl = "cashTypesId";
                break;
                case 'cashTypesDetail':
                	$tbl = "cashTypesDetail";
                break;
                case 'cashTypesStatus':
                	$tbl = "cashTypesStatus";
                break;
                case 'taxId':
                	$tbl = "taxId";
                break;
                case 'taxDetail':
                	$tbl = "taxDetail";
                break;
                case 'taxStatus':
                	$tbl = "taxStatus";
                break;
                case 'markdownsId':
                	$tbl = "markdownsId";
                break;
                case 'markdownsDetail':
                	$tbl = "markdownsDetail";
                break;
                case 'markdownsStatus':
                	$tbl = "markdownsStatus";
                break;
                case 'paymentExtsId':
                	$tbl = "paymentExtsId";
                break;
                case 'paymentExtsDetail':
                	$tbl = "paymentExtsDetail";
                break;
                case 'paymentExtsStatus':
                	$tbl = "paymentExtsStatus";
                break;
                case 'paymentMethodsId':
                	$tbl = "paymentMethodsId";
                break;
                case 'paymentMethodsDetail':
                	$tbl = "paymentMethodsDetail";
                break;
                case 'paymentMethodsStatus':
                	$tbl = "paymentMethodsStatus";
                break;
                case 'basicSettings':
                	$tbl = "basicSettings";
                break;
                
                //Till Operration
                case 'tillOperation':
                	$tbl = "tillOperation";
                break;
                case 'tillPaymentDiscrepancy':
                	$tbl = "tillPaymentDiscrepancy";
                break;
                case 'tillPaymentMovement':
                	$tbl = "tillPaymentMovement";
                break;
                case 'tillPaymentShot':
                	$tbl = "tillPaymentShot";
                break;
                case 'tillCashShot':
                	$tbl = "tillCashShot";
                break;
                
                //Sales
                case 'salesHeader':
                	$tbl = "salesHeader";
                break;
                case 'salesHeaderDiscount':
                	$tbl = "salesHeaderDiscount";
                break;
                case 'salesHeaderTax':
                	$tbl = "salesHeaderTax";
                break;
                case 'salesHeaderIdkeyword':
                    $tbl = "salesHeaderIdkeyword";
                break;
                case 'salesItem':
                	$tbl = "salesItem";
                break;
                case 'salesItemDiscount':
                	$tbl = "salesItemDiscount";
                break;
                case 'salesPayment':
                	$tbl = "salesPayment";
                break;
                case 'salesItemTax':
                	$tbl = "salesItemTax";
                break;
                case 'usersIdsalesHeaderId':
                	$tbl = "usersIdsalesHeaderId";
                break;
                
                //Promotions
                case 'promotionsBenefit':
                        $tbl = "promotionsBenefit";
                break;
                case 'promotionsBenefitStatus':
                        $tbl = "promotionsBenefitStatus";
                break;
                case 'promotionsDetail':
                        $tbl = "promotionsDetail";
                break;
                case 'promotionsStatus':
                        $tbl = "promotionsStatus";
                break;
                
                //Dyna Tables
                case 'dynaPageFreeTable':
                	$tbl = "dynaPageFreeTable";
                break;
                case 'dynaPageFreeTableStatus':
                	$tbl = "dynaPageFreeTableStatus";
                break;
            }
            $sql = "SHOW COLUMNS FROM ".$tbl;
            $result=$this->pdoSelect($sql);
            foreach($result['rows'] as $result_element){
                $cols[]= $result_element['Field'];
            }
            return array("tablename"=>$tbl,"colnames"=>$cols);
        }
        
        public function toSafeString($name,$hardclean=1){
            $filepath = new filepath();
            return $filepath->toSafeString($name,$hardclean);
        }
    }
 
 
 class security{
    public function toSafeString($name,$hardclean=1){
            $filepath = new filepath();
            return $filepath->toSafeString($name,$hardclean);
        }
    private function url_origin($s, $use_forwarded_host=false)
    {
        $ssl = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on') ? true:false;
        $sp = strtolower($s['SERVER_PROTOCOL']);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        $port = $s['SERVER_PORT'];
        $port = ((!$ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
        $host = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
        $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
        return $protocol . '://' . $host;
    }
    public function full_url($s, $use_forwarded_host=false)
    {
        return $s['REQUEST_URI'];
    }
    public function getUserIp(){
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $this->toSafeString($_SERVER['HTTP_CLIENT_IP']);
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $this->toSafeString($_SERVER['HTTP_X_FORWARDED_FOR']);
            } else {
                $ip = $this->toSafeString($_SERVER['REMOTE_ADDR']);
            }
            return $ip;
    }
    public function findmaldata($url){
        if(strpos(strtolower($url),'select') !== false || strpos(strtolower($url),'order%20by') !== false || strpos(strtolower($url),'union') !== false || strpos(strtolower($url),'--') !== false || strpos(strtolower($url),'waitfor') !== false 
        || strpos(strtolower($url),'ddbms_pipe') !== false || strpos(strtolower($url),'delay') !== false || strpos(strtolower($url),'concat') !== false 
        || strpos(strtolower($url),'char') !== false  || strpos(strtolower($url),'convert') !== false
        || strpos(strtolower($url),'table_name') !== false || strpos(strtolower($url),'where') !== false || strpos(strtolower($url),'document') !== false || strpos(strtolower($url),'cookie') !== false
        || strpos(strtolower($url),'alert') !== false || strpos(strtolower($url),'script') !== false || strpos(strtolower($url),'include') !== false){
            return true;
        }else{
            return false;
        }
    }
}


?>