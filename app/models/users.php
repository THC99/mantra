<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
*/

function initUser(){
	global $db;
		
	$result='';
	$dbe=$db["default"];

	$create="CREATE TABLE IF NOT EXISTS `users` (".
		 "`id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY, ".
		 "`instanceid` BIGINT(20) NOT NULL, ".
		 "`fullname` VARCHAR(128) NOT NULL, ".
		 "`email` TEXT NULL, ".
		 "`logname` VARCHAR(64) NOT NULL, ".
		 "`passkey` VARCHAR(64) NOT NULL, ".
		 "`role` ENUM('administrator','supervisor','choreographer','composer','publisher','provider','requester','visitor') NOT NULL, ".
		 "`activity` ENUM('off','on') NOT NULL, ".
		 "`registered` DATETIME NOT NULL, ".
		 "`updated` DATETIME NOT NULL, ".
		 "INDEX (`instanceid`), ".
		 "UNIQUE (`logname`), ".
		 "FOREIGN KEY (`instanceid`) REFERENCES `instances`(`id`) ON DELETE CASCADE ON UPDATE CASCADE ".
		 ") ".
		 "ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;  ";

	//$copy="CREATE TABLE IF NOT EXISTS dbmantra.users (PRIMARY KEY (`id`)) SELECT * FROM dbmantra_1.users";
	//$move="CREATE TABLE IF NOT EXISTS dbmantra.users LIKE dbmantra_1.users";

	$rename="RENAME TABLE `users` TO `tmpusers`;";
	
	$select="SELECT i.`id` AS `instance_id`, t.* FROM `tmpusers` t LEFT JOIN `instances` i ON t.`userprovider`=i.`instance`;";

	$replace="INSERT INTO `users` (`instanceid`,`fullname`,`logname`,`passkey`,`role`,`activity`,`registered`,`updated`) VALUES(0,'ADMINISTRATOR','admin','".password_hash('1234', PASSWORD_DEFAULT)."','administrator','on',NOW(),NOW()); ";
	
	$remove="DROP TABLE `tmpusers`; ";
	
	$tbnames=getTableNames(
		$dbdriver=$dbe['dbdriver'],
		$hostname=$dbe['hostname'],
		$username=$dbe["username"],
		$password=$dbe["password"],
		$dbname=$dbe["database"]
	);

	if(!in_array('users',$tbnames)){
		$result=dbExecute(
			$dbdriver=$dbe['dbdriver'],
			$hostname=$dbe['hostname'],
			$username=$dbe['rootname'],
			$password=$dbe['rootpass'],
			$dbname=$dbe['database'],
			$sql=$create,
			$bindfield=false,
			$trx=true,
			$debug=$dbe['db_debug'],
			$errprefix='initUser()->create'
		);	
		if($result!='OK') exit($result);
		$result=dbExecute(
			$dbdriver=$dbe['dbdriver'],
			$hostname=$dbe['hostname'],
			$username=$dbe['username'],
			$password=$dbe['password'],
			$dbname=$dbe['database'],
			$sql=$replace,
			$bindfield=false,
			$trx=true,
			$debug=$dbe['db_debug'],
			$errprefix='initUser()->replace'
		);		
		if($result!='OK') exit($result);
	}
	else{	
		$clnames=getColumnNames(
			$dbdriver=$dbe['dbdriver'],
			$hostname=$dbe['hostname'],
			$username=$dbe["username"],
			$password=$dbe["password"],
			$dbname=$dbe["database"],
			$tbname='users'
		);
		if(isset($clnames['USERLEVEL'])){
			$result=dbExecute(
				$dbdriver=$dbe['dbdriver'],
				$hostname=$dbe['hostname'],
				$username=$dbe['rootname'],
				$password=$dbe['rootpass'],
				$dbname=$dbe['database'],
				$sql=$rename,
				$bindfield=false,
				$trx=true,
				$debug=$dbe['db_debug'],
				$errprefix='initUser()->rename'
			);	
			if($result!='OK') exit($result);

			$result=dbExecute(
				$dbdriver=$dbe['dbdriver'],
				$hostname=$dbe['hostname'],
				$username=$dbe['rootname'],
				$password=$dbe['rootpass'],
				$dbname=$dbe['database'],
				$sql=$create,
				$bindfield=false,
				$trx=true,
				$debug=$dbe['db_debug'],
				$errprefix='initUser()->create'
			);	
			if($result!='OK') exit($result);
		    
			if($result=='OK'){
				$adors=dbExecute(
					$dbdriver=$dbe['dbdriver'],
					$hostname=$dbe['hostname'],
					$username=$dbe['username'],
					$password=$dbe['password'],
					$dbname=$dbe['database'],
					$sql=$select,
					$bindfield=false,
					$trx=false,
					$debug=$dbe['db_debug'],
					$errprefix='initUser()->select'
				);

				if(is_object($adors)){
					$insert=array();
					$roles=content_roles();
					foreach($adors->getRows() as $row=>$col)
					if(isset($col['userlevel']) && isset($col['userstatus'])){
						$role=isset($roles[$col['userlevel']])?$roles[$col['userlevel']]:'visitor';
						$activity=$col['userstatus']==1?'on':'off';
						$insert[]="INSERT INTO `users` (`instanceid`,`fullname`,`email`,`logname`,`passkey`,`role`,`activity`,`registered`,`updated`) VALUES ".
						"( '".$col['instance_id']."', '".addslashes($col['username'])."', '', '".$col['userlog']."', '".$col['userpass']."', '".$role."', '".$activity."', NOW(), NOW() ); ";
					}
					if(!empty($insert)){
						$result=dbExecute(
							$dbdriver=$dbe['dbdriver'],
							$hostname=$dbe['hostname'],
							$username=$dbe['username'],
							$password=$dbe['password'],
							$dbname=$dbe['database'],
							$sql=$insert,
							$bindfield=false,
							$trx=true,
							$debug=$dbe['db_debug'],
							$errprefix='initUser()->insert'
						);
						if($result!='OK') exit($result);
					}
				}
				
				$result=dbExecute(
					$dbdriver=$dbe['dbdriver'],
					$hostname=$dbe['hostname'],
					$username=$dbe['rootname'],
					$password=$dbe['rootpass'],
					$dbname=$dbe['database'],
					$sql=$remove,
					$bindfield=false,
					$trx=true,
					$debug=$dbe['db_debug'],
					$errprefix='initUser()->remove'
				);
				if($result!='OK') exit($result);
			}	
		}	
	}
}

function infoUser($input){
	global $db;
	$result=array();

	$dbe=$db["default"];
	if(is_array($input)){
		$dml="SELECT * ".
			 "FROM `users` ".
			 "WHERE `logname` = ? AND `instanceid` = ? LIMIT 1";
		$data=$input;
	}
	else{
		$dml="SELECT * ".
			 "FROM `users` ".
			 "WHERE `logname` = ? LIMIT 1";
		$data=array($input);
	}
	
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
		$errprefix='infoUser()'
	);
	if(is_object($adors)){
		$result=$adors->fields;
	}
	
	return $result;
}


function validUser($input=array('',''),&$output){
	global $db,$ADODB_COUNTRECS;
	$result=false;

	$dbe=$db["default"];
	
	list($logname,$passkey)=$input;

	//$dml="SELECT * FROM `users` WHERE `logname` = ? AND `passkey` = ? AND `activity` = 'on' LIMIT 1";
	$dml="SELECT * FROM `users` WHERE `logname` = ? AND `activity` = 'on' LIMIT 1";

	$data=array($logname);

	$ADODB_COUNTRECS = true;

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
		$errprefix='validUser()'
	);
	if(is_object($adors)){
		if($adors->RecordCount()>0){
			$rs=$adors->fields;
			if(substr($passkey,0,4)=="$2y$") $result=($passkey==$rs['passkey']);
			elseif(substr($rs['passkey'],0,4)=="$2y$") $result=password_verify($passkey,$rs['passkey']);
			else $result=(base64_encode($passkey)==trim($rs['passkey']));
			if($result) $output=$rs;
		}
	}
	$ADODB_COUNTRECS = false;

	return $result;
}

function existUser($userlog){
	global $db,$ADODB_COUNTRECS;
	$result=0;

	$dbe=$db["default"];
	$dml="SELECT `fullname` FROM `users` WHERE `logname` = ? LIMIT 1";
	$data=array($userlog);

	$ADODB_COUNTRECS = true;
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
		$errprefix='existUser()'
	);
	if(is_object($adors)) $result=$adors->RecordCount()>0;
	$ADODB_COUNTRECS = false;

	return $result;
}

/*------------------ Users Processing */

function addUser($input){
	global $db;

	$result='';
	if(!isset($input["instanceid"])  || !isset($input["fullname"])  || !isset($input["logname"]) || !isset($input["passkey"]) || !isset($input["role"])) return 'Invalid fieldname!';
	if( (empty($input["instanceid"]) && $input["instanceid"]!="0") || empty($input["fullname"]) || empty($input["logname"]) || empty($input["passkey"]) || empty($input["role"])) return 'Empty absolute fieldname!';

	$dbe=$db["default"];
	$dml="INSERT INTO `users` (`instanceid`,`fullname`,`email`,`logname`,`passkey`,`role`,`activity`,`registered`,`updated`) ".
		 "VALUES ( ?, ?, ?, ?, ?, ?, ?, NOW(), NOW() );";
	$data=array($input['instanceid'],$input['fullname'],$input['email'],$input['logname'],$input['passkey'],$input['role'],$input['activity']);

	$result=dbExecute(
		$dbdriver=$dbe['dbdriver'],
		$hostname=$dbe['hostname'],
		$username=$dbe['username'],
		$password=$dbe['password'],
		$dbname=$dbe['database'],
		$sql=$dml,
		$bindfield=$data,
		$trx=true,
		$debug=$dbe['db_debug'],
		$errprefix='addUser()'
	);

	return $result;
}

function updateUserByID($input){
	global $db;

	$result='';
	if(!isset($input["instanceid"])  || !isset($input["fullname"])  || !isset($input["logname"]) || !isset($input["passkey"]) || !isset($input["role"])) return 'Invalid fieldname!';
	if((empty($input["instanceid"]) && $input["instanceid"]!="0") || empty($input["fullname"]) || empty($input["logname"]) || empty($input["passkey"]) || empty($input["role"])) return 'Empty absolute fieldname!';
	
	$dbe=$db["default"];
	$dml="UPDATE `users` SET `instanceid`=?,`fullname`=?,`email`=?,`logname`=?,`passkey`=?,`role`=?, `activity`=?, `updated`=NOW() ".
		 "WHERE `id`=?";
	$data=array($input['instanceid'],$input['fullname'],$input['email'],$input['logname'],$input['passkey'],$input['role'],$input['activity'],$input['id']);

	$result=dbExecute(
		$dbdriver=$dbe['dbdriver'],
		$hostname=$dbe['hostname'],
		$username=$dbe['username'],
		$password=$dbe['password'],
		$dbname=$dbe['database'],
		$sql=$dml,
		$bindfield=$data,
		$trx=true,
		$debug=$dbe['db_debug'],
		$errprefix='updateUserByID()'
	);

	return $result;
}

function updateUserByLogname($input){
	global $db;

	$result='';
	if(!isset($input["instanceid"])  || !isset($input["fullname"])  || !isset($input["logname"]) || !isset($input["passkey"]) || !isset($input["role"])) return 'Invalid fieldname!';
	if((empty($input["instanceid"]) && $input["instanceid"]!="0") || empty($input["fullname"]) || empty($input["logname"]) || empty($input["passkey"]) || empty($input["role"])) return 'Empty absolute fieldname!';

	$dbe=$db["default"];
	$dml="UPDATE `users` SET `instanceid`=?,`fullname`=?,`email`=?,`logname`=?,`passkey`=?,`role`=?, `activity`=?, `updated`=NOW() ".
		 "WHERE `logname`=?";
	$data=array($input['instanceid'],$input['fullname'],$input['email'],$input['logname'],$input['passkey'],$input['role'],$input['activity'],$input['logname']);

	$result=dbExecute(
		$dbdriver=$dbe['dbdriver'],
		$hostname=$dbe['hostname'],
		$username=$dbe['username'],
		$password=$dbe['password'],
		$dbname=$dbe['database'],
		$sql=$dml,
		$bindfield=$data,
		$trx=true,
		$debug=$dbe['db_debug'],
		$errprefix='updateUserByLogname()'
	);

	return $result;
}

function deleteUserByID($id){
	global $db;
	$result='';

	$dbe=$db["default"];
	$dml="DELETE FROM `users` WHERE `id`=?";
	$data=array($id);
	
	$result=dbExecute(
		$dbdriver=$dbe['dbdriver'],
		$hostname=$dbe['hostname'],
		$username=$dbe['username'],
		$password=$dbe['password'],
		$dbname=$dbe['database'],
		$sql=$dml,
		$bindfield=$data,
		$trx=true,
		$debug=$dbe['db_debug'],
		$errprefix='deleteUserByID()'
	);
	
	return $result;
}

function deleteUserByLogname($logname){
	global $db;
	$result='';

	$dbe=$db["default"];
	$dml="DELETE FROM `users` WHERE `logname`=?";
	$data=array($logname);
	
	$result=dbExecute(
		$dbdriver=$dbe['dbdriver'],
		$hostname=$dbe['hostname'],
		$username=$dbe['username'],
		$password=$dbe['password'],
		$dbname=$dbe['database'],
		$sql=$dml,
		$bindfield=$data,
		$trx=true,
		$debug=$dbe['db_debug'],
		$errprefix='deleteUserByLogname()'
	);
	
	return $result;
}


function getUserByID($id){
	global $db;

	$result=false;

	$dbe=$db["default"];
	$dml="SELECT u.*, i.`instance`, i.`organization` ".
		 "FROM `users` u LEFT JOIN `instances` i ON u.`instanceid`=i.`id` ".
		 "WHERE u.`id`=?";
	$data=array($id);

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
		$errprefix='getUserByID()'
	);
	if(is_object($adors)) $result=$adors->fields;

	return $result;
}

function getUsers($logname="",$page_row=0,$page_offset=0){

	global $db;

	$result=false;
	$limit=($page_row>0?"LIMIT $page_row OFFSET $page_offset":"");
	$logname=empty($logname)?"%":$logname;

	$dbe=$db["default"];
	$dml="SELECT u.*, i.`instance`,i.`organization` ".
		 "FROM `users` u LEFT JOIN `instances` i ON u.`instanceid`=i.`id` ".
		 "WHERE `logname` LIKE ? ORDER BY `registered` DESC,`instance` ASC,`logname` ASC $limit";
	$data=array($logname);

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
		$errprefix='getUsers()'
	);
	if(is_object($adors)) $result=$adors->getRows();

	return $result;
}

function getUserRows($logname=""){
	global $db;//,$ADODB_COUNTRECS;
	$result=0;
	$logname=empty($logname)?"%":$logname;

	$dbe=$db["default"];
	$dml="SELECT COUNT(`id`) AS `rows` FROM `users` WHERE `logname` LIKE ?  ";
	$data=array($logname);

	//$ADODB_COUNTRECS = true;

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
		$errprefix='getUserRows()'
	);
	if(is_object($adors)) $result=$adors->fields('rows');

	//$ADODB_COUNTRECS = false;
	
	return $result;
}
