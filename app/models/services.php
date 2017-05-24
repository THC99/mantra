<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
*/

function initService(){
	global $db;
		
	$result='';
	$dbe=$db["default"];

	$create="CREATE TABLE IF NOT EXISTS `services` (".
		 "`id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY, ".
		 "`instanceid` BIGINT(20) NOT NULL, ".
		 "`servicename` VARCHAR(64) NOT NULL, ".
		 "`servicetype` VARCHAR(64) NOT NULL, ".
		 "`descript` LONGTEXT, ".
		 "`registered` DATETIME NOT NULL, ".
		 "`updated` DATETIME NOT NULL, ".
		 "INDEX (`instanceid`), ".
		 "UNIQUE (`instanceid`,`servicename`), ".
		 "FOREIGN KEY (`instanceid`) REFERENCES `instances`(`id`) ON DELETE CASCADE ON UPDATE CASCADE ".
		 ") ".
		 "ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;  ";
	
	$rename="RENAME TABLE `services` TO `tmpservices`;";
	
	$select="SELECT i.`id` AS `instance_id`, t.* FROM `tmpservices` t LEFT JOIN `instances` i ON t.`provider`=i.`instance`;";

	$remove="DROP TABLE `tmpservices`; ";

	
	$tbnames=getTableNames(
		$dbdriver=$dbe['dbdriver'],
		$hostname=$dbe['hostname'],
		$username=$dbe["username"],
		$password=$dbe["password"],
		$dbname=$dbe["database"]
	);
	
	if(!in_array('services',$tbnames)){
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
			$errprefix='initService()->create'
		);	
	}
	else{	
		$clnames=getColumnNames(
			$dbdriver=$dbe['dbdriver'],
			$hostname=$dbe['hostname'],
			$username=$dbe["username"],
			$password=$dbe["password"],
			$dbname=$dbe["database"],
			$tbname='services'
		);
	
		if(isset($clnames["TNS"])){
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
				$errprefix='initService()->rename'
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
				$errprefix='initService()->create'
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
					$errprefix='initService()->select'
				);
				if(is_object($adors)){
					$insert=array();
					foreach($adors->getRows() as $row=>$col)
					if(isset($col['tns'])){
						$insert[]="INSERT INTO `services` (`id`,`instanceid`,`servicename`,`servicetype`,`descript`,`registered`,`updated`) VALUES ".
						"( ".$col['id'].",'".$col['instance_id']."', '".$col['servicename']."', '".$col['tns']."', '".addslashes($col['desc'])."', NOW(), NOW() ); ";
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
							$errprefix='initService()->insert'
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
					$errprefix='initService()->remove'
				);
				if($result!='OK') exit($result);
			}
		}
	}
}

function existService($input){
	global $db,$ADODB_COUNTRECS;
	$result=0;

	$dbe=$db["default"];
	$dml="SELECT s.*, i.`instance`, i.`organization` ".
		 "FROM `services` s ".
		 "LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ". 
		 "WHERE s.`servicename` = ? AND i.`instance` = ? ".
		 "LIMIT 1 ";
	$data=array($input['servicename'],$input['provider']);
	
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
		$errprefix='existService()'
	);
	if(is_object($adors)) $result=$adors->RecordCount()>0;
	$ADODB_COUNTRECS = false;
	
	return $result;
}

function addService($input){
	global $db;

	$result='';
	if(empty($input["servicename"])) return $result;

	$dbe=$db["default"];
	
	$dml="INSERT INTO `services` (`servicename`,`servicetype`,`instanceid`,`descript`,`registered`,`updated`) ".
		 "VALUES ( ?, ?, ?, ?, NOW(), NOW() );";
	$data=array($input['servicename'],$input['servicetype'],$input['instanceid'],$input['descript']);
	
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
		$errprefix='addService()'
	);
	
	return $result;
}

function updateService($input){
	global $db;
	$result='';
	if(empty($input["servicename"])) return $result;

	$dbe=$db["default"];
	
	$dml="UPDATE `services` SET `servicename`=?,`servicetype`=?,`instanceid`=?,`descript`=?, `updated`=NOW() ".
		 "WHERE `id`=?";
	$data=array($input['servicename'],$input['servicetype'],$input['instanceid'],$input['descript'],$input['id']);

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
		$errprefix='updateService()'
	);

	return $result;
}

function deleteServiceByID($id){
	global $db;

	$result='';

	$dbe=$db["default"];
	$dml="DELETE FROM `services` WHERE `id`=?";
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
		$errprefix='deleteServiceByID()'
	);


	return $result;
}

function getServiceByID($id){
	global $db;

	$result=false;

	$dbe=$db["default"];
	$dml="SELECT s.*, i.`instance`, i.`organization` ".
		 "FROM `services` s ".
		 "LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ".
		 "WHERE s.`id`=?";
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
		$errprefix='getServiceByID()'
	);
	if(is_object($adors)) $result=$adors->fields;

	return $result;
}

function getServices($instance="",$servicetype="",$servicename="",$page_row=0,$page_offset=0){
	global $db;

	$result=false;
	$limit=($page_row>0?"LIMIT $page_row OFFSET $page_offset":"");
	$instance=empty($instance)?"%":$instance;
	$servicetype=empty($servicetype)?"%":$servicetype;
	$servicename=empty($servicename)?"%":$servicename;

	$dbe=$db["default"];
	$dml="SELECT s.*, i.`instance`, i.`organization` ".
		 "FROM `services` s ".
		 "LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ".
		 "WHERE i.`instance` LIKE ? AND s.`servicetype` LIKE ? AND s.`servicename` LIKE ? ".
		 "ORDER BY `instance`,`servicename`,`registered` DESC $limit";
	$data=array($instance,$servicetype,$servicename);
	
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
		$errprefix='getServices()'
	);
	if(is_object($adors)) $result=$adors->getRows();
	
	return $result;
}

function getServiceRows($instance="",$servicetype="",$servicename=""){
	global $db;
	$result=0;
	$instance=empty($instance)?"%":$instance;
	$servicetype=empty($servicetype)?"%":$servicetype;
	$servicename=empty($servicename)?"%":$servicename;

	$dbe=$db["default"];
	$dml="SELECT COUNT(s.`id`) AS `rows` ".
		 "FROM `services` s ".
		 "LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ".
		 "WHERE i.`instance` LIKE ? AND s.`servicetype` LIKE ? AND s.`servicename` LIKE ? ";
	$data=array($instance,$servicetype,$servicename);

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
		$errprefix='getServiceRows()'
	);
	if(is_object($adors)) $result=$adors->fields('rows');

	return $result;
}
