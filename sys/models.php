<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
ver:1.99y
*/


require_once LIBDIR.'adodb5/adodb-exceptions.inc.php';
require_once LIBDIR.'adodb5/adodb.inc.php';	 
require_once LIBDIR.'adodb5/adodb-active-record.inc.php';

$ADODB_COUNTRECS = false;

$reqmodel=glob(APP_MODEL.'*.php');
foreach($reqmodel as $reqfile){
require_once $reqfile;	 
}

function createDB($dbdriver="",$hostname="",$username="",$password="",$dbname=""){
global $db;
	$result='';
	try{
		$adodb= newADOConnection($dbdriver);
		$adodb->debug=false;
		$connected=$adodb->connect($hostname,$username,$password);
		$dict=NewDataDictionary($adodb);
		$sql=$dict->CreateDatabase($dbname);
		if($dict->ExecuteSQLArray($sql)==2) $adodb->connect($hostname,$username,$password,$dbname);
		$result='OK';
	}
	catch(exception $e){
		switch($e->getCode()){
		case 1045:
			$message='Database connection failed!';
			break;
		default:
			$message=$e->getMessage();
		}
		$result='createDB(): '.$message;
		$db['default']['messages']=$result;
	}
	return $result;
}

function getDBNames($dbdriver="",$hostname="",$username="",$password=""){
global $db;
	$result=array();
	if($dbdriver=="" || $hostname=="" || $username=="") return $result;
	try{
		$adodb= newADOConnection($dbdriver);
		$adodb->debug=false;
		$adodb->connect($hostname,$username,$password);
		$result=$adodb->metaDatabases();
	}
	catch(exception $e){
		$db['default']['messages']="getDBNames(): ".$e->getMessage();
	}
	return $result;
}

function getTableNames($dbdriver="",$hostname="",$username="",$password="",$dbname=""){
global $db;
	$result=array();
	if($dbdriver=="" || $hostname=="" || $username=="" || $dbname=="") return $result;
	try{
		$adodb= newADOConnection($dbdriver);
		$adodb->debug=false;
		$adodb->connect($hostname,$username,$password,$dbname); 
		$result=$adodb->metaTables();
	}
	catch(exception $e){
		$db['default']['messages']="getTableNames(): ".$e->getMessage();
	}
	return $result;
}

function getColumnNames($dbdriver="",$hostname="",$username="",$password="",$dbname="",$tbname=""){
global $db;
	$result=array();
	if($dbdriver=="" || $hostname=="" || $username=="" || $dbname=="" || $tbname=="") return $result;

	if(in_array($dbdriver,array("mysql","mysqli"))){
		$tbname=trim($tbname,"`");
	}
	elseif(in_array($dbdriver,array("postgres","oci8"))){
		$tbname=trim($tbname,"\"");
	}
	elseif($dbdriver=="mssql"){
		$tbname=ltrim($tbname,"[");
		$tbname=rtrim($tbname,"]");
	}

	try{
		$adodb= newADOConnection($dbdriver);
		$adodb->debug=false;
		$adodb->connect($hostname,$username,$password,$dbname); 
		if($dbdriver=="oci8"){
			$fields=array();			
			$adodb->setFetchMode(ADODB_FETCH_ASSOC);
			$adors=$adodb->execute("SELECT COLUMN_NAME FROM ALL_TAB_COLS WHERE TABLE_NAME='{$tbname}' ORDER BY COLUMN_ID");
			if($adors) $fields=array_column($adors->getRows(),'COLUMN_NAME');
			$result=$fields;
		}
		else $result=$adodb->MetaColumnNames($tbname);
	}
	catch(exception $e){
		$db['default']['messages']="getColumnNames(): ".$e->getMessage();
	}
	return $result;
}


function setFieldValue($dbdriver,$afield=array()){
	$akey=array_keys($afield);
	$fields=implode(', ',$akey);
	if($dbdriver=='oci8'){
		$aval=array();
		foreach($akey as $field){
			$aval[]=':'.$field;
		}
	}
	else{
		$aval=array_fill(0,count($afield),'?');
	}
	$values=implode(', ',$aval);
	return array('fields'=>$fields,'values'=>$values);
}

function getFieldValue($dbdriver,$afield=array()){
	$result=array();
	foreach($afield as $key=>$val){
		$result[]=$key.'='.($dbdriver=='oci8'?':'.$key:'?');
	}
	return implode(', ',$result);
}

function dbInsert($dbdriver='',$hostname='',$username='',$password='',$dbname='',$tbname='',$bindfield=array()){
	if(empty($dbdriver) or empty($hostname) or empty($username) or empty($dbname) or empty($tbname) or empty($bindfield)) return 'Incomplete parameter dbInsert()';
	extract(setFieldValue($dbdriver,$bindfield)); //extract variable from array keys
	$dml="INSERT INTO {$tbname} ($fields) VALUES ({$values})";
	return dbExecute($dbdriver,$hostname,$username,$password,$dbname,$dml,$bindfield,true);
}

function dbUpdate($dbdriver='',$hostname='',$username='',$password='',$dbname='',$tbname='',$bindfield=array(),$where=''){
	if(empty($dbdriver) or empty($hostname) or empty($username) or empty($dbname) or empty($tbname) or empty($bindfield)) return 'Incomplete parameter dbInsert()';
	$fields=getFieldValue($dbdriver,$bindfield); 
	$dml="UPDATE {$tbname} SET {$fields} ".(empty($where)?"":"WHERE {$where}");
	return dbExecute($dbdriver,$hostname,$username,$password,$dbname,$dml,$bindfield,true);
}

function dbDelete($dbdriver='',$hostname='',$username='',$password='',$dbname='',$tbname='',$where=''){
	if(empty($dbdriver) or empty($hostname) or empty($username) or empty($dbname) or empty($tbname) or empty($where)) return 'Incomplete parameter dbInsert()';
	$dml="DELETE FROM {$tbname} ".(empty($where)?"WHERE 1":"WHERE {$where}");
	return dbExecute($dbdriver,$hostname,$username,$password,$dbname,$dml,false,true);
}

function dbExecute($dbdriver='',$hostname='',$username='',$password='',$dbname='',$sql,$bindfield=false,$trx=false,$debug=false,$errprefix=''){
global $db;
	$result='';
	
	if(empty($dbdriver) or empty($hostname) or empty($username) or empty($dbname) or empty($sql) ) return 'Incomplete parameter dbExecute()';
	if(is_array($bindfield) and empty($bindfield) ) return 'Empty bind parameter dbExecute()';
	try{
		$adodb= newADOConnection($dbdriver);
		$adodb->debug=$debug;
		$adodb->setFetchMode(ADODB_FETCH_ASSOC); 
		$adodb->connect($hostname,$username,$password,$dbname);
		if($trx) $adodb->startTrans();
		if($trx){
			if(is_array($sql)){
				foreach($sql as $dml) $adodb->execute($dml,$bindfield);
			}
			else{		
				$adodb->execute($sql,$bindfield);
			}
			$result='OK';
			$adodb->completeTrans();
		}
		else{
			if(is_array($sql)){
				$result=array();
				foreach($sql as $dml){
					$adors=$adodb->execute($dml,$bindfield);
					if($adors) $result[]=$adors;
				}
			}
			else{
				$adors=$adodb->execute($sql,$bindfield);
				if($adors) $result=$adors;
			}
		} 
	}
	catch(exception $e){
		$result=$errprefix.': '.$e->getMessage();
		$db['default']['messages']=$result;
		if($trx) $adodb->failTrans();
	}
	return $result;
}

function getRecordCount($table="",$condition=""){
global $db;
	$result=0;
	$dbe=$db["default"];

	$condition=empty($condition)?"":"WHERE ".$condition;
	$dml="SELECT COUNT(*) AS `rows` FROM {$table} {$condition}";

	$adors=dbExecute(
		$dbdriver=$dbe['dbdriver'],
		$hostname=$dbe['hostname'],
		$username=$dbe['username'],
		$password=$dbe['password'],
		$dbname=$dbe['database'],
		$sql=$dml,
		$bindfield=$data,
		$trx=false,
		$debug=$dbe['db_debug'],
		$errprefix='getRecordCount()'
	);
	if(is_object($adors)) $result=$adors->fields('rows');
	
	return $result;
}


function logAccept($signin){
	$result=false;
	if(isset($signin) && (count($signin)>0) ){
		if(!headers_sent()){ 
			if(!session_id()) session_start();
			if (isset($_SESSION['captcha']) && !empty($_SESSION['captcha'])){
				$captcha_code = trim(strip_tags($signin['captcha']));
				$validCaptcha = password_verify(strtoupper($captcha_code),$_SESSION['captcha']);			
				if($validCaptcha && $signin["passkey"]!=""){ 
					if(validUser(array($signin["logname"],$signin["passkey"]),$userdata)){
						if(session_id()){
							session_unset();
							session_regenerate_id(true);
							$_SESSION["request"]="";
							$_SESSION["workspaceinstanceid"]=$userdata['instanceid'];
							$_SESSION["workspacerole"]=$userdata['role'];
							$workspaceid=serialize(array($userdata['logname'],$userdata['passkey']));
							$_SESSION["workspaceid"]=enc64data($workspaceid);
							$_SESSION["workstation"]=$_SERVER[base64_decode('SFRUUF9VU0VSX0FHRU5U')];					
							addTrack(array("trackid"=>session_id(),"trackname"=>"login","trackstatus"=>"ONLINE","tracknote"=>$_SESSION["workspacerole"],"trackdata"=>$workspaceid));
						}
						$result=true;
						header("Location: ".home_url());
						exit;
					}
				}
			}
		}
	}
	return $result;
}

function logClose($quit=false){
	if(isset($_GET["keluar"]) || $quit){
		if(!session_id()) session_start();
		if(!headers_sent()){
			addTrack(array("trackid"=>session_id(),"trackname"=>"logout","trackstatus"=>"OFFLINE","tracknote"=>$_SESSION["workspacerole"],"trackdata"=>$_SESSION["workspaceid"]));
			session_unset();
			session_regenerate_id(true);
			header("Location: ".home_url().'masuk');
			exit;
		}
	}
}

function logOpen(){
	global $validLogin,$sess_name;
	$result=false;
	$validLogin=false;
	
	if(!headers_sent()){
		if(!session_id()) session_start();
		if(isset($_SESSION["workstation"]) && ($_SESSION["workstation"]!=$_SERVER[base64_decode('SFRUUF9VU0VSX0FHRU5U')])){
			session_unset();			
		}	
		elseif(isset($_SESSION['captcha']) && !isset($_POST['f_login']['submit'])){
			session_unset();
		}
		elseif(isset($_SESSION["workspaceid"]) && !empty($_SESSION["workspaceid"])){
				$workspaceid=unserialize(dec64data($_SESSION["workspaceid"]));
				if(substr($workspaceid[1],0,4)!="$2y$") $workspaceid[1]=base64_decode($workspaceid[1]);
				$validLogin=validUser($workspaceid,$userdata);
				if($validLogin){
					session_regenerate_id();
				}				
				$result=$validLogin;
		}
	}
	
	if(!$result){
		if(session_id() && (count($_SESSION)==0) ){
			$sessname=session_name();
			$sessparams=session_get_cookie_params();
			session_unset();
			session_destroy();
			setcookie($sessname, '', time() - 42000,
				$sessparams["path"], $sessparams["domain"],
				$sessparams["secure"], $sessparams["httponly"]
			);
		}
	}
	return $result;
}

function valPreprocess(&$value){
	$value=strtoupper(trim($value));
}

function checkPrivilegesOnDB($dbInformation){
	$pattern="/^(GRANT)\s+(.+)\s+ON\s+(.+)\s+TO\s+(.+)\s*$/";
	$pos=false;
	$val=array();
	$addPrivileges=array();
	$grant="";
	
	try {
		$adodb= newADOConnection($dbInformation["dbdriver"]);
		$adodb->debug=false;
		$adodb->setFetchMode(ADODB_FETCH_NUM); 
        $adodb->connect($dbInformation["hostname"],$dbInformation["rootname"],$dbInformation["rootpass"]); 
        $query="show grants for '".$dbInformation["username"]."'@'".$dbInformation["hostname"]."' ;";
        $adors=$adodb->execute($query);
        
        
        foreach($adors->getRows() as $row=>$columns){
        	$pos=strpos($columns[0],$dbInformation["database"]);
            if ($pos!==false){
            	preg_match($pattern,$columns[0],$val);
            }
        }
                
        if (!empty($val)){
        	$reqPrivileges=array('SELECT','INSERT','UPDATE','DELETE');
        	$currPrivileges=explode(',',$val[2]);
        	
        	array_walk($currPrivileges,'valPreprocess');
        	
			$i=0;
			foreach($reqPrivileges as $key=>$privilege) if(!in_array($privilege,$currPrivileges)){
				$addPrivileges[$i]=$privilege;
				$i++;
			}
			
			if (!empty($addPrivileges)){
        		$grant="GRANT ".implode(", ",$addPrivileges).", ".$val[2]." ON `".$dbInformation["database"]."`.* TO '".
    	    		$dbInformation["username"]."'@'".$dbInformation["hostname"]."' ; ";
    	    	
    	    	$revoke="REVOKE ALL PRIVILEGES ON `".$dbInformation["database"]."`.* FROM '".
        			$dbInformation["username"]."'@'".$dbInformation["hostname"]."' ; ";
    	    	
    	    	$adodb->execute($revoke);
     		    $adodb->completeTrans();
        	}
			
        }else{
        	$grant="GRANT SELECT, INSERT, UPDATE, DELETE ON `".$dbInformation["database"]."`.* TO '".
    	    		$dbInformation["username"]."'@'".$dbInformation["hostname"]."' ; ";
        }
        
        if(!empty($grant)){
          $adodb->execute($grant);
          $adodb->completeTrans();
        } 
	}
	catch(exception $e){
		$errmsg=$e->getMessage();
		$adodb->failTrans();
	}
	
}

function init_database(){
global $db;
		
	$dbe=$db["default"];
	$dbases=getDBNames(
		$dbdriver=$dbe['dbdriver'],
		$hostname=$dbe['hostname'],
		$username=$dbe["rootname"],
		$password=$dbe["rootpass"]
	);
	if(in_array($dbe["database"],$dbases)){

		//check privilege untuk user mysql
		checkPrivilegesOnDB($dbe);
		
		$tbnames=getTableNames(
			$dbdriver=$dbe['dbdriver'],
			$hostname=$dbe['hostname'],
			$username=$dbe["rootname"],
			$password=$dbe["rootpass"],
			$dbname=$dbe["database"]
		);

		if(in_array('providers',$tbnames)){
			syncMethodServices();
			init_table();
		}
		elseif(in_array('notifications',$tbnames)){
			setNotifications();
		}		
	}
	else{
		$result=createDB(
			$dbdriver=$dbe['dbdriver'],
			$hostname=$dbe['hostname'],
			$username=$dbe["rootname"],
			$password=$dbe["rootpass"],
			$dbname=$dbe["database"]
		);	
		if($result!='OK') exit($result);
		
		//check privilege untuk user mysql
		checkPrivilegesOnDB($dbe);
		
		init_table();
	}	
}

function init_table(){
	initCache();
	initCode();
	initInstance();
	initUser();
	initOrder();
	initService();
	initMethod();
	initTrack();
	initNotification();
}



//----------------- Current User Session 
function current_role(){
	$result='visitor';
	if(session_id()){
		if(isset($_SESSION["workspacerole"])) $result=$_SESSION["workspacerole"];
	}
	return $result;
}

function current_instanceid(){
	$result="";
	if(session_id()){
		if(isset($_SESSION["workspaceinstanceid"])) $result=$_SESSION["workspaceinstanceid"]>=0?$_SESSION["workspaceinstanceid"]:-1;
	}
	return $result;
}

function current_instance($id){
	$result="";
	$instances=getInstanceByID($id);
	if(count($instances)>0)	$result=$instances["instance"];
	return $result;
}

function current_organization($id){
	$result="";
	$instances=getInstanceByID($id);
	if(count($instances)>0)	$result=$instances["organization"];
	return $result;
}

function current_logname(){
	$result="";
	$data=array();
	if(session_id()){
		if(isset($_SESSION["workspaceid"])) $data=unserialize(dec64data($_SESSION["workspaceid"]));
	}
	if(isset($data[0])) $result=$data[0];
	return $result;
}

function current_passkey(){	
	$result="";
	$data=array();
	if(session_id()){
		if(isset($_SESSION["workspaceid"])) $data=unserialize(dec64data($_SESSION["workspaceid"]));
	}
	if(isset($data[1])) $result=$data[1];
	return $result;
}

function current_logpass(){	
	$result=array();
	if(session_id()){
		if(isset($_SESSION["workspaceid"])) $result=unserialize(dec64data($_SESSION["workspaceid"]));
	}
	return $result;
}

//------------- Current User Level 

function content_roles(){
	$roles=array(
	'0'=>'requester',
	'2'=>'provider',
	'1'=>'publisher',
/*
	'3'=>'composer',
	'4'=>'choreographer',
*/
	'8'=>'administrator',
	'9'=>'supervisor'
	);
	return $roles;
}

function is_requester($role=''){
	$result=false;
	if(!empty($role)) $result=$role=='requester';
	else $result=current_role()=='requester';
	return $result;
}

function is_provider($role=''){
	$result=false;
	if(!empty($role)) $result=$role=='provider';
	else $result=current_role()=='provider';
	return $result;
}

function is_publisher($role=''){
	$result=false;
	if(!empty($role)) $result=$role=='publisher';
	else $result=current_role()=='publisher';
	return $result;
}

function is_composer($role=''){
	$result=false;
	if(!empty($role)) $result=$role=='composer';
	else $result=current_role()=='composer';
	return $result;
}

function is_choreographer($role=''){
	$result=false;
	if(!empty($role)) $result=$role=='choreographer';
	else $result=current_role()=='choreographer';
	return $result;
}

function is_supervisor($role=''){
	$result=false;
	if(!empty($role)) $result=$role=='supervisor';
	else $result=current_role()=='supervisor';
	return $result;
}

function is_administrator($role=''){
	$result=false;
	if(!empty($role)) $result=$role=='administrator';
	else $result=current_role()=='administrator';
	return $result;
}

function is_visitor($role=''){
	$result=false;
	if(!empty($role)) $result=$role=='visitor';
	else $result=current_role()=='visitor';
	return $result;
}

//------------- User Status 

function content_activity(){
	$status=array('off','on');
	return $status;
}


//-------------- Services, DSN, Command Format
function write_opln($code="",$request=""){
	$data=array(
		"code"=>$code,
		"request"=>$request
	);
	$result=json_encode($data,JSON_FORCE_OBJECT | JSON_PARTIAL_OUTPUT_ON_ERROR);
	return $result;
}

function read_opln($srcstr){
	if(strpos($srcstr,"code=")===false){
		$srv=json_decode($srcstr,true);
	}
	else{
		parse_str($srcstr,$srv);
		$srv["code"]=rawurldecode($srv["code"]);
		$srv["request"]=rawurldecode($srv["request"]);
	}
	return $srv;
}

function write_opws($wstype="",$endpoint="",$method="",$accesskey="",$request=""){
	$data=array(
		"wstype"=>$wstype,
		"endpoint"=>$endpoint,
		"method"=>$method,
		"accesskey"=>$accesskey,
		"request"=>$request
	);
	$result=json_encode($data,JSON_FORCE_OBJECT | JSON_PARTIAL_OUTPUT_ON_ERROR);
	return $result;
}

function read_opws($srcstr){
	if(strpos($srcstr,"wstype=")===false){
		$srv=json_decode($srcstr,true);
	}
	else{
		parse_str($srcstr,$srv);
	}
	return $srv;
}

function write_opds($dbtype,$hostname,$port,$user,$password,$dbname,$tbname,$columns,$conditions,$orders,$limrows,$limoffset){
	if($dbtype=="mysqli") $port="";
	if($dbtype=="mssql" && strrpos($hostname,"\\")!==false ) $port="";

	$data=array(
		"dbtype"=>$dbtype,
		"hostname"=>$hostname,
		"port"=>$port,
		"dbuser"=>$user,
		"dbpassword"=>$password,
		"dbname"=>$dbname,
		"tbname"=>$tbname,
		"columns"=>$columns,
		"conditions"=>$conditions,
		"orders"=>$orders,
		"limrows"=>$limrows,
		"limoffset"=>$limoffset		
	);
	$result=json_encode($data,JSON_FORCE_OBJECT | JSON_PARTIAL_OUTPUT_ON_ERROR);
	return $result;
}

function read_opds($dsnstr){
	if(strpos($dsnstr,"dbtype=")===false){
		$dsn=json_decode($dsnstr,true);
	}
	else{
		parse_str($dsnstr,$dsn);
	}
	return $dsn;
}

function get_dbport($dbtype){
	$port="";
	switch ($dbtype){
	case "oci8":
		$port="1521";
		break;
	case "postgres":
		$port="5432";
		break;
	case "mssql":
		$port="1443";
		break;
	case "mysql":
	default:
		$port="3306";
	}
	return $port;
}

function getAppIcon(){
	$result='img/favicon.ico';
	$rs=getCodes("appsys","icon");
	if(count($rs)>0){
		$res=$rs[0];
		if($res['value']!='') $result="files/".$res['value'];
	}
	return $result;
}

function getAppImage(){
	$result='';
	$rs=getCodes("appsys","image");
	if(count($rs)>0){
		$res=$rs[0];
		if($res['value']!='') $result=$res['value'];
	}
	return $result;
}

function getAppInstance(){
	$result='';
	$rs=getCodes("appsys","instance");
	if(count($rs)>0){
		$res=$rs[0];
		if($res['value']!='') $result=$res['value'];
	}
	return $result;
}


