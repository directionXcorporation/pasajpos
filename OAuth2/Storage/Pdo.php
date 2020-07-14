<?php

namespace OAuth2\Storage;

use OAuth2\OpenID\Storage\UserClaimsInterface;
use OAuth2\OpenID\Storage\AuthorizationCodeInterface as OpenIDAuthorizationCodeInterface;

/**
 * Simple PDO storage for all storage types
 *
 * NOTE: This class is meant to get users started
 * quickly. If your application requires further
 * customization, extend this class or create your own.
 *
 * NOTE: Passwords are stored in plaintext, which is never
 * a good idea.  Be sure to override this for your application
 *
 * @author Brent Shaffer <bshafs at gmail dot com>
 */
class Pdo implements
    AuthorizationCodeInterface,
    AccessTokenInterface,
    ClientCredentialsInterface,
    UserCredentialsInterface,
    RefreshTokenInterface,
    JwtBearerInterface,
    ScopeInterface,
    PublicKeyInterface,
    UserClaimsInterface,
    OpenIDAuthorizationCodeInterface
{
    protected $db;
    protected $config;
    protected $pdodb;

    public function __construct($config = array(),$pdodb_object='')
    {
        /*if(count($connection)>0){
            if (!$connection instanceof \PDO) {
                if (is_string($connection)) {
                    $connection = array('dsn' => $connection);
                }
                if (!is_array($connection)) {
                    throw new \InvalidArgumentException('First argument to OAuth2\Storage\Pdo must be an instance of PDO, a DSN string, or a configuration array');
                }
                if (!isset($connection['dsn'])) {
                    throw new \InvalidArgumentException('configuration array must contain "dsn"');
                }
                // merge optional parameters
                $connection = array_merge(array(
                    'username' => null,
                    'password' => null,
                    'options' => array(),
                ), $connection);
                $connection = new \PDO($connection['dsn'], $connection['username'], $connection['password'], $connection['options']);
            }
            $this->db = $connection;
    
            // debugging
            $connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }*/

        $this->config = array_merge(array(
            'client_table' => 'oauth_clients',
            'access_token_table' => 'oauth_access_tokens',
            'refresh_token_table' => 'oauth_refresh_tokens',
            'code_table' => 'oauth_authorization_codes',
            'user_table' => 'generalusers',
            'jwt_table'  => 'oauth_jwt',
            'jti_table'  => 'oauth_jti',
            'scope_table'  => 'oauth_scopes',
            'public_key_table'  => 'oauth_public_keys',
        ), $config);
        $this->pdodb = $pdodb_object;
    }

    /* OAuth2\Storage\ClientCredentialsInterface */
    public function checkClientCredentials($client_id, $client_password = null)
    {
        $client_dets = $this->getClientDetails($client_id);
        if(count($client_dets)>0){
            $client_userid = $client_dets['user_id'];
            $sql = "SELECT emailaddress FROM generalusers WHERE id=:id LIMIT 0,1";
            $client_email = $this->pdodb->pdoselect($sql,array(":id"=>$client_userid),array(":id"=>"i"));
            if(isset($client_email[0]['emailaddress'])){
                $client_email = $client_email[0]['emailaddress'];
            }else{
                $client_email = "";
            }
            $user_id = login_oauth($client_email,$client_password);
            if($user_id){
                $result = true;
            }else{
                $result = false;
            }
        }else{
            $result = false;
        }
        // make this extensible
        return $result;
    }

    public function isPublicClient($client_id)
    {
        $result = array();
        $sql = sprintf('SELECT * FROM %s WHERE client_id = :client_id AND ispublic=1  LIMIT 0,1', $this->config['client_table']);
        $result = $this->pdodb->pdoselect($sql,array(":client_id"=>$client_id),array(":client_id"=>"s"));
        if(!isset($result[0])){
            $return = false;
        }else{
            $return = $return[0];
        }
        return $return;
    }
    
    /* OAuth2\Storage\ClientInterface */
    public function getClientDetails($client_id)
    {
        $result = array();
        $sql = sprintf('SELECT * from %s where client_id = :client_id', $this->config['client_table']);
        $result = $this->pdodb->pdoselect($sql,array(":client_id"=>$client_id),array(":client_id"=>"s"));
        if(!isset($result[0])){
            $return = array();
        }else{
            $return = $result[0];
        }
        return $return;
    }

    public function setClientDetails($client_id, $client_company = null, $redirect_uri = null, $grant_types = null, $scope = null, $user_id = null)
    {
        $cols = array(
            "client_company",
            "redirect_uri",
            "grant_types",
            "scope",
            "user_id"
        );
        $vals = array(
            "client_company"=>$client_company,
            "redirect_uri"=>$redirect_uri,
            "grant_types"=>$grant_types,
            "scope"=>$scope,
            "user_id"=>$user_id
        );
        $types = array(
            "client_company"=>"s",
            "redirect_uri"=>"s",
            "grant_types"=>"s",
            "scope"=>"s",
            "user_id"=>"s"
        );
        $id = $this->pdodb->pdoupdate($this->config['client_table'],$client_id,$cols,$vals,$types,"client_id");
        if($id){
            $return = true;
        }else{
            $return = false;
        }
        return $return;
    }

    public function checkRestrictedGrantType($client_id, $grant_type)
    {
        $details = $this->getClientDetails($client_id);
        if (isset($details['grant_types'])) {
            $grant_types = explode(' ', $details['grant_types']);

            return in_array($grant_type, (array) $grant_types);
        }

        // if grant_types are not defined, then none are restricted
        return true;
    }

    /* OAuth2\Storage\AccessTokenInterface */
    public function getAccessToken($access_token)
    {
        $token = NULL;
        $sql = sprintf('SELECT * from %s where access_token = :access_token LIMIT 0,1', $this->config['access_token_table']);
        $result = $this->pdodb->pdoselect($sql,array(":access_token"=>$access_token),array(":access_token"=>"s"));
        if(count($result)>0){
            $token = $result[0];
        }
        if (count($token)>0) {
            // convert date string back to timestamp
            $token['expires'] = strtotime($token['expires']);
        }

        return $token;
    }

    public function setAccessToken($access_token, $client_id, $user_id, $expires, $scope = null)
    {
        // convert expires to datestring
        $id = 0;
        $expires = date('Y-m-d H:i:s', $expires);
        $cols = array(
            "access_token",
            "client_id",
            "user_id",
            "expires",
            "scope"
        );
        $vals= array(
            "access_token"=>$access_token,
            "client_id"=>$client_id,
            "user_id"=>$user_id,
            "expires"=>$expires,
            "scope"=>$scope
        );
        $types = array(
            "access_token"=>"s",
            "client_id"=>"s",
            "user_id"=>"i",
            "expires"=>"s",
            "scope"=>"s"
        );
        // if it exists, update it.
        if ($this->getAccessToken($access_token)) {
            unset($cols['access_token']);
            unset($vals['access_token']);
            unset($types['access_token']);
            $id = $this->pdodb->pdoupdate($this->config['access_token_table'],$access_token,$cols,$vals,$types,"access_token");
        } else {
            $id = $this->pdodb->pdoinsert($this->config['access_token_table'],$cols,$vals,$types);
        }
        if($id){
            $result = true;
        }else{
            $result = false;
        }
        return $result;
    }

    public function unsetAccessToken($access_token)
    {
        $id = $this->pdodb->pdodelete($this->config['access_token_table'],$access_token,"access_token");
        if($id){
            $result = true;
        }else{
            $result = false;
        }

        return $result;
    }

    /* OAuth2\Storage\AuthorizationCodeInterface */
    public function getAuthorizationCode($code='',$client_id='',$user_id=0,$scope='')
    {
        $result = array();
        if($code && $client_id==''){
            $sql = sprintf('SELECT * from %s where authorization_code = :code', $this->config['code_table']);
            $result = $this->pdodb->pdoselect($sql,array(":code"=>$code),array(":code"=>"s"));
        }else if(!$code && $client_id && $user_id){
            $sql = sprintf("SELECT authorization_code FROM %s WHERE client_id=:client_id AND user_id=:user_id AND scope=:scope AND (expires GREATERTHAN= CONVERT_TZ(NOW(),@@global.time_zone,'+03:30') OR 1) ORDER BY expires DESC LIMIT 0,1",$this->config['code_table']);
            $result = $this->pdodb->pdoselect($sql,array(":client_id"=>$client_id,":user_id"=>$user_id,":scope"=>$scope),array(":client_id"=>"s",":user_id"=>"i",":scope"=>"s"));
        }

        if (isset($result[0])) {
            // convert date string back to timestamp
            $result[0]['expires'] = strtotime($result[0]['expires']);
        }else{
            $result[0] = false;
        }

        return $result[0];
    }

    public function setAuthorizationCode($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null, $id_token = null)
    {
        $id = 0;
        if (func_num_args() > 6) {
            // we are calling with an id token
            return call_user_func_array(array($this, 'setAuthorizationCodeWithIdToken'), func_get_args());
        }

        // convert expires to datestring
        $expires = date('Y-m-d H:i:s', $expires);
        $cols = array(
            "authorization_code",
            "client_id",
            "user_id",
            "redirect_uri",
            "expires",
            "scope"
        );
        $vals= array(
            "authorization_code"=>$code,
            "client_id"=>$client_id,
            "user_id"=>$user_id,
            "redirect_uri"=>$redirect_uri,
            "expires"=>$expires,
            "scope"=>$scope
        );
        $types = array(
            "authorization_code"=>"s",
            "client_id"=>"s",
            "user_id"=>"i",
            "redirect_uri"=>"s",
            "expires"=>"s",
            "scope"=>"s"
        );
        
        // if it exists, update it.
        if ($this->getAuthorizationCode($code)) {
            unset($cols['authorization_code']);
            unset($vals['authorization_code']);
            unset($types['authorization_code']);
            $id = $this->pdodb->pdoupdate($this->config['code_table'],$code,$cols,$vals,$types,"authorization_code");
        } else {
            $id = $this->pdodb->pdoinsert($this->config['code_table'],$cols,$vals,$types);
        }
        if($id){
            $result = true;
        }else{
            $result = false;
        }
        return $result;
    }

    private function setAuthorizationCodeWithIdToken($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null, $id_token = null)
    {
        $id = 0;
        // convert expires to datestring
        $expires = date('Y-m-d H:i:s', $expires);
        
        $cols = array(
            "authorization_code",
            "client_id",
            "user_id",
            "redirect_uri",
            "expires",
            "scope",
            "id_token"
        );
        $vals= array(
            "authorization_code"=>$code,
            "client_id"=>$client_id,
            "user_id"=>$user_id,
            "redirect_uri"=>$redirect_uri,
            "expires"=>$expires,
            "scope"=>$scope,
            "id_token"=>$id_token
        );
        $types = array(
            "authorization_code"=>"s",
            "client_id"=>"s",
            "user_id"=>"i",
            "redirect_uri"=>"s",
            "expires"=>"s",
            "scope"=>"s",
            "id_token"=>"s"
        );
        
        // if it exists, update it.
        if ($this->getAuthorizationCode($code)) {
            unset($cols['authorization_code']);
            unset($vals['authorization_code']);
            unset($types['authorization_code']);
            $id = $this->pdodb->pdoupdate($this->config['code_table'],$code,$cols,$vals,$types,"authorization_code");
        } else {
            $id = $this->pdodb->pdoinsert($this->config['code_table'],$cols,$vals,$types);
        }
        if($id){
            $result = true;
        }else{
            $result = false;
        }
        return $result;
    }

    public function expireAuthorizationCode($code)
    {
        $id = 0;
        $id = $this->pdodb->pdodelete($this->config['code_table'],$code,"authorization_code");
        return $id;
    }

    /* OAuth2\Storage\UserCredentialsInterface */
    public function checkUserCredentials($username, $password)
    {
        $user_id = login_oauth($username,$password);
        if($user_id){
            return array(
                "user_id"=>$user_id,
                "scope"=>""
            );
        }else{
            return false;
        }
    }

    public function getUserDetails($user_id)
    {
        //$sql = "SELECT id,name,emailaddress,tellnumber,regdate,recievenewsletter,parameterid,blocked FROM generalusers WHERE emailaddress=:emailaddress LIMIT 0,1";
        //$result = $this->pdodb->pdoselect($sql,array(":emailaddress"=>$user_email),array(":emailaddress"=>"s"));
        //if(isset($result[0])){
            
        //}
        $result = getuserdetails_oauth($user_id);
        return array_merge(array(
            "user_id"=>$user_id
        ),$result);
    }

    /* UserClaimsInterface */
    public function getUserClaims($user_id, $claims)
    {
        if (!$userDetails = $this->getUserDetails($user_id)) {
            return false;
        }

        $claims = explode(' ', trim($claims));
        $userClaims = array();

        // for each requested claim, if the user has the claim, set it in the response
        $validClaims = explode(' ', self::VALID_CLAIMS);
        foreach ($validClaims as $validClaim) {
            if (in_array($validClaim, $claims)) {
                if ($validClaim == 'address') {
                    // address is an object with subfields
                    $userClaims['address'] = $this->getUserClaim($validClaim, $userDetails['address'] ?: $userDetails);
                } else {
                    $userClaims = array_merge($userClaims, $this->getUserClaim($validClaim, $userDetails));
                }
            }
        }

        return $userClaims;
    }

    protected function getUserClaim($claim, $userDetails)
    {
        $userClaims = array();
        $claimValuesString = constant(sprintf('self::%s_CLAIM_VALUES', strtoupper($claim)));
        $claimValues = explode(' ', $claimValuesString);

        foreach ($claimValues as $value) {
            $userClaims[$value] = isset($userDetails[$value]) ? $userDetails[$value] : null;
        }

        return $userClaims;
    }

    /* OAuth2\Storage\RefreshTokenInterface */
    public function getRefreshToken($refresh_token)
    {
        $sql = sprintf('SELECT * FROM %s WHERE refresh_token = :refresh_token LIMIT 0,1', $this->config['refresh_token_table']);
        $result = $this->pdodb->pdoselect($sql,array(":refresh_token"=>$refresh_token),array(":refresh_token"=>"s"));
        if (isset($result[0])) {
            $token = $result[0];
            // convert expires to epoch time
            $token['expires'] = strtotime($token['expires']);
        }else{
            $token = false;
        }

        return $token;
    }

    public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = null)
    {
        // convert expires to datestring
        $expires = date('Y-m-d H:i:s', $expires);
        $cols = array(
            "refresh_token",
            "client_id",
            "user_id",
            "expires",
            "scope"
        );
        $vals = array(
            "refresh_token"=>$refresh_token,
            "client_id"=>$client_id,
            "user_id"=>$user_id,
            "expires"=>$expires,
            "scope"=>$scope
        );
        $types = array(
            "refresh_token"=>"s",
            "client_id"=>"s",
            "user_id"=>"i",
            "expires"=>"s",
            "scope"=>"s"
        );
        $id = $this->pdodb->pdoinsert($this->config['refresh_token_table'],$cols,$vals,$types);
        if($id){
            return true;
        }else{
            return false;
        }
    }

    public function unsetRefreshToken($refresh_token)
    {
        $id = $this->pdodb->pdodelete($this->config['refresh_token_table'],$refresh_token,"refresh_token");
        if($id){
            return true;
        }else{
            return false;
        }
    }

    // plaintext passwords are bad!  Override this for your application
    protected function checkPassword($user, $password)
    {
        return login_oauth($user['emailaddress'],$password);
    }

    public function getUser($username)
    {
        $sql = sprintf('SELECT id,name,emailaddress,tellnumber,regdate,recievenewsletter,blocked,parameterid FOM %s WHERE emailaddress=:emailaddress LIMIT 0,1', $this->config['user_table']);
        $result = $this->pdodb->pdoselect($sql,array(":emailaddress"=>$username),array(":emailaddress"=>"s"));

        if (!isset($result[0])) {
            return false;
        }else{
            $result = $result[0];
        }

        // the default behavior is to use "username" as the user_id
        return array_merge(array(
            'user_id' => $result['id']
        ), $result);
    }

    public function setUser($username, $password, $firstName = null, $lastName = null)
    {
        //
    }

    /* ScopeInterface */
    public function scopeExists($scope)
    {
        $scope = explode(' ', $scope);
        $whereIn = implode(',', $scope);
        $sql = sprintf('SELECT count(scope) as count FROM %s WHERE scope IN (:wherein)', $this->config['scope_table']);
        $result1 = $this->pdodb->pdoselect($sql,array(":wherein"=>$whereIn),array(":wherein"=>"s"));
        if ($result1[0]['count']>0) {
            return $result1[0]['count'] == count($scope);
        }
        return false;
    }

    public function getDefaultScope($client_id = null)
    {
        /*$stmt = $this->db->prepare(sprintf('SELECT scope FROM %s WHERE is_default=:is_default', $this->config['scope_table']));
        $stmt->execute(array('is_default' => true));

        if ($result = $stmt->fetchAll(\PDO::FETCH_ASSOC)) {
            $defaultScope = array_map(function ($row) {
                return $row['scope'];
            }, $result);

            return implode(' ', $defaultScope);
        }
        */
        return null;
    }

    /* JWTBearerInterface */
    public function getClientKey($client_id, $subject)
    {
        /*
        $stmt = $this->db->prepare($sql = sprintf('SELECT public_key from %s where client_id=:client_id AND subject=:subject', $this->config['jwt_table']));

        $stmt->execute(array('client_id' => $client_id, 'subject' => $subject));

        return $stmt->fetchColumn();
        */
    }

    public function getClientScope($client_id)
    {
        if (!$clientDetails = $this->getClientDetails($client_id)) {
            return false;
        }

        if (isset($clientDetails['scope'])) {
            return $clientDetails['scope'];
        }

        return null;
    }

    public function getJti($client_id, $subject, $audience, $expires, $jti)
    {
        /*
        $stmt = $this->db->prepare($sql = sprintf('SELECT * FROM %s WHERE issuer=:client_id AND subject=:subject AND audience=:audience AND expires=:expires AND jti=:jti', $this->config['jti_table']));

        $stmt->execute(compact('client_id', 'subject', 'audience', 'expires', 'jti'));

        if ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return array(
                'issuer' => $result['issuer'],
                'subject' => $result['subject'],
                'audience' => $result['audience'],
                'expires' => $result['expires'],
                'jti' => $result['jti'],
            );
        }
        */
        return null;
    }

    public function setJti($client_id, $subject, $audience, $expires, $jti)
    {
        /*
        $stmt = $this->db->prepare(sprintf('INSERT INTO %s (issuer, subject, audience, expires, jti) VALUES (:client_id, :subject, :audience, :expires, :jti)', $this->config['jti_table']));

        return $stmt->execute(compact('client_id', 'subject', 'audience', 'expires', 'jti'));
        */
    }

    /* PublicKeyInterface */
    public function getPublicKey($client_id = null)
    {
        /*
        $stmt = $this->db->prepare($sql = sprintf('SELECT public_key FROM %s WHERE client_id=:client_id OR client_id IS NULL ORDER BY client_id IS NOT NULL DESC', $this->config['public_key_table']));

        $stmt->execute(compact('client_id'));
        if ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $result['public_key'];
        }
        */
    }

    public function getPrivateKey($client_id = null)
    {
        /*
        $stmt = $this->db->prepare($sql = sprintf('SELECT private_key FROM %s WHERE client_id=:client_id OR client_id IS NULL ORDER BY client_id IS NOT NULL DESC', $this->config['public_key_table']));

        $stmt->execute(compact('client_id'));
        if ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $result['private_key'];
        }
        */
    }

    public function getEncryptionAlgorithm($client_id = null)
    {
        /*
        $stmt = $this->db->prepare($sql = sprintf('SELECT encryption_algorithm FROM %s WHERE client_id=:client_id OR client_id IS NULL ORDER BY client_id IS NOT NULL DESC', $this->config['public_key_table']));

        $stmt->execute(compact('client_id'));
        if ($result = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            return $result['encryption_algorithm'];
        }
        */
        return 'RS256';
    }

    /**
     * DDL to create OAuth2 database and tables for PDO storage
     *
     * @see https://github.com/dsquier/oauth2-server-php-mysql
     */
    public function getBuildSql($dbName = 'oauth2_server_php')
    {
        $sql = "
        CREATE TABLE {$this->config['client_table']} (
          client_id             VARCHAR(80)   NOT NULL,
          client_company         VARCHAR(255),
          redirect_uri          VARCHAR(2000),
          grant_types           VARCHAR(80),
          scope                 VARCHAR(4000),
          user_id               BIGINT(255),
          PRIMARY KEY (client_id)
        );

        CREATE TABLE {$this->config['access_token_table']} (
          access_token         VARCHAR(40)    NOT NULL,
          client_id            VARCHAR(80)    NOT NULL,
          user_id              BIGINT(255),
          expires              TIMESTAMP      NOT NULL,
          scope                VARCHAR(4000),
          PRIMARY KEY (access_token)
        );

        CREATE TABLE {$this->config['code_table']} (
          authorization_code  VARCHAR(40)    NOT NULL,
          client_id           VARCHAR(80)    NOT NULL,
          user_id             BIGINT(255),
          redirect_uri        VARCHAR(2000),
          expires             TIMESTAMP      NOT NULL,
          scope               VARCHAR(4000),
          id_token            VARCHAR(1000),
          PRIMARY KEY (authorization_code)
        );

        CREATE TABLE {$this->config['refresh_token_table']} (
          refresh_token       VARCHAR(40)    NOT NULL,
          client_id           VARCHAR(80)    NOT NULL,
          user_id             VARCHAR(80),
          expires             TIMESTAMP      NOT NULL,
          scope               VARCHAR(4000),
          PRIMARY KEY (refresh_token)
        );

        CREATE TABLE {$this->config['scope_table']} (
          scope               VARCHAR(80)  NOT NULL,
          is_default          BOOLEAN,
          PRIMARY KEY (scope)
        );

        CREATE TABLE {$this->config['jwt_table']} (
          client_id           VARCHAR(80)   NOT NULL,
          subject             VARCHAR(80),
          public_key          VARCHAR(2000) NOT NULL
        );

        CREATE TABLE {$this->config['jti_table']} (
          issuer              VARCHAR(80)   NOT NULL,
          subject             VARCHAR(80),
          audiance            VARCHAR(80),
          expires             TIMESTAMP     NOT NULL,
          jti                 VARCHAR(2000) NOT NULL
        );

        CREATE TABLE {$this->config['public_key_table']} (
          client_id            VARCHAR(80),
          public_key           VARCHAR(2000),
          private_key          VARCHAR(2000),
          encryption_algorithm VARCHAR(100) DEFAULT 'RS256'
        )
";

        return $sql;
    }
}
