<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
*/

function initInstance(){
	global $db;
		
	$result='';
	$dbe=$db["default"];

	$create="CREATE TABLE IF NOT EXISTS `instances` (".
		 "`id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY, ".
		 "`instance` VARCHAR(64) NOT NULL, ".
		 "`organization` TEXT NOT NULL, ".
		 "`webportal` TEXT, ".
		 "`email` TEXT, ".
		 "`descript` LONGTEXT, ".
		 "`registered` DATETIME NOT NULL, ".
		 "`updated` DATETIME NOT NULL, ".
		 "UNIQUE (`instance`) ".
		 ") ".
		 "ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;  ";

	$rename="RENAME TABLE `providers` TO `instances`";

	$change="ALTER TABLE `instances` ".
		 	"CHANGE `providername` `instance` VARCHAR(64) NOT NULL, ".
		 	"CHANGE `url` `webportal` TEXT, ".
			"ADD UNIQUE (`instance`), ".
			"CHANGE `email` `email` TEXT NULL DEFAULT NULL, ".
		 	"CHANGE `desc` `descript` LONGTEXT NULL, ".
 		  "ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci; ";
		 	
	
	$selectSystem="SELECT count(`instance`) as `jumlah` FROM `instances` WHERE `instance`='system'; ";
	$insert="INSERT INTO `instances` (`instance`,`organization`,`registered`,`updated`) VALUES('system','Default',NOW(),NOW()); ";
	$update="UPDATE `instances` SET `id`=0,`registered`=NOW(),`updated`=NOW() WHERE `instance`='system'; ";
	
	$tbnames=getTableNames(
		$dbdriver=$dbe['dbdriver'],
		$hostname=$dbe['hostname'],
		$username=$dbe["username"],
		$password=$dbe["password"],
		$dbname=$dbe["database"]
	);

	if(!in_array('instances',$tbnames)){
		if(in_array('providers',$tbnames)){
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
				$errprefix='initInstance()->rename'
			);	
			if($result!='OK') exit($result);
			if($result=='OK'){
				$result=dbExecute(
					$dbdriver=$dbe['dbdriver'],
					$hostname=$dbe['hostname'],
					$username=$dbe['rootname'],
					$password=$dbe['rootpass'],
					$dbname=$dbe['database'],
					$sql=$change,
					$bindfield=false,
					$trx=true,
					$debug=$dbe['db_debug'],
					$errprefix='initInstance()->change'
				);		
				if($result!='OK') exit($result);
				
				$adors=dbExecute(
					$dbdriver=$dbe['dbdriver'],
					$hostname=$dbe['hostname'],
					$username=$dbe['username'],
					$password=$dbe['password'],
					$dbname=$dbe['database'],
					$sql=$selectSystem,
					$bindfield=false,
					$trx=false,
					$debug=$dbe['db_debug'],
					$errprefix='initInstance()->checkSystemInstance'
				);
				if(is_object($adors)){
					if ($adors->fields['jumlah']==0)
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
							$errprefix='initInstance()->insert'
						);
					if($result!='OK') exit($result);	
					$result=dbExecute(
						$dbdriver=$dbe['dbdriver'],
						$hostname=$dbe['hostname'],
						$username=$dbe['username'],
						$password=$dbe['password'],
						$dbname=$dbe['database'],
						$sql=$update,
						$bindfield=false,
						$trx=true,
						$debug=$dbe['db_debug'],
						$errprefix='initInstance()->update'
					);
					if($result!='OK') exit($result);
				}			
			}
		}
		else{
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
				$errprefix='initInstance()->create'
			);	
			if($result!='OK') exit($result);
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
				$errprefix='initInstance()->insert'
			);		
			if($result!='OK') exit($result);
			$result=dbExecute(
				$dbdriver=$dbe['dbdriver'],
				$hostname=$dbe['hostname'],
				$username=$dbe['username'],
				$password=$dbe['password'],
				$dbname=$dbe['database'],
				$sql=$update,
				$bindfield=false,
				$trx=true,
				$debug=$dbe['db_debug'],
				$errprefix='initInstance()->update'
			);		
			if($result!='OK') exit($result);
		}
	}
}

function existInstanceByID($id){
	global $db;
	$result=0;

	$dbe=$db["default"];
	$dml="SELECT `organization` FROM `instances` WHERE `id` = ? LIMIT 1";
	$data=array($id);
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
		$errprefix='existInstanceByID()'
	);
	if(is_object($adors)) $result=$adors->RecordCount()>0;
	$ADODB_COUNTRECS = false;	

	return $result;
}

function existInstance($instance){
	global $db;
	$result=0;

	$dbe=$db["default"];
	$dml="SELECT `organization` FROM `instances` WHERE `instance` = ? LIMIT 1";
	$data=array($instance);

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
		$errprefix='existInstance()'
	);
	if(is_object($adors)) $result=$adors->RecordCount()>0;
	$ADODB_COUNTRECS = false;	

	return $result;
}

function addInstance($input){
	global $db;

	$result='';
	if(!isset($input["instance"]) || !isset($input["organization"])) return $result;
	if(empty($input["instance"]) || empty($input["organization"])) return $result;

	$dbe=$db["default"];
	$dml="INSERT INTO `instances` (`instance`,`organization`,`webportal`,`email`,`descript`,`registered`, `updated`) ".
		 "VALUES ( ?, ?, ?, ?, ?, NOW(), NOW() );";
	$data=array($input['instance'],$input['organization'],$input['webportal'],$input['email'],$input['descript']);

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
		$errprefix='addInstance()'
	);

	return $result;
}

function updateInstanceByID($input){
	global $db;

	$result='';
	
	if(!isset($input["instance"]) || !isset($input["organization"])) return $result;
	if(empty($input["instance"]) || empty($input["organization"])) return $result;

	$dbe=$db["default"];
	$dml="UPDATE `instances` SET `instance`=?,`organization`=?,`webportal`=?,`email`=?,`descript`=?, `updated`=NOW() ".
		 "WHERE `id`=?";
	$data=array($input['instance'],$input['organization'],$input['webportal'],$input['email'],$input['descript'],$input['id']);

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
		$errprefix='updateInstanceByID()'
	);

	return $result;
}

function deleteInstanceByID($id){
	global $db;

	$result='';

	$dbe=$db["default"];
	$dml="DELETE FROM `instances` WHERE `id`=?";
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
		$errprefix='deleteInstanceByID()'
	);

	return $result;
}

function deleteInstance($instance){
	global $db;

	$result='';

	$dbe=$db["default"];
	$dml="DELETE FROM `instances` WHERE `instance`=?";
	$data=array($instance);
	
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
		$errprefix='deleteInstance()'
	);

	return $result;
}

function getInstanceByID($id){
	global $db;

	$result=false;

	$dbe=$db["default"];
	$dml="SELECT * FROM `instances` WHERE `id`=?";
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
		$errprefix='getInstanceByID()'
	);
	if(is_object($adors)) $result=$adors->fields;

	return $result;
}

function getInstances($instance="",$page_row=0,$page_offset=0){
	global $db;

	$result=false;
	
	$limit=($page_row>0?"LIMIT $page_row OFFSET $page_offset":"");
	$instance=empty($instance)?"%":$instance;

	$dbe=$db["default"];
	$dml="SELECT * FROM `instances` WHERE `instance` LIKE ? AND `id`>0 ORDER BY `registered` DESC,`instance` $limit";
	$data=array($instance);
	
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
		$errprefix='getInstances()'
	);
	if(is_object($adors)) $result=$adors->getRows();


	return $result;
}


function getInstanceRows($instance=""){
	global $db;
	
	$result=0;
	$instance=empty($instance)?"%":$instance;

	$dbe=$db["default"];

	$dml="SELECT COUNT(`id`) AS `rows` FROM `instances` WHERE `instance` LIKE ? AND `id`>0 ";
	$data=array($instance);
	
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
		$errprefix='getInstanceRows()'
	);	
	if(is_object($adors)) $result=$adors->fields('rows');

	return $result;
}
