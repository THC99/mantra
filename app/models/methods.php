<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
*/

function initMethod(){
	global $db;
		
	$result='';
	$dbe=$db["default"];

	$create="CREATE TABLE IF NOT EXISTS `methods` (".
		 "`id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY, ".
		 "`serviceid` BIGINT(20) NOT NULL, ".
		 "`methodname` VARCHAR(128) NOT NULL, ".
		 "`methodtype` VARCHAR(16) NOT NULL, ".
		 "`restricted` ENUM('off','on') NOT NULL, ".
		 "`sourcecode` LONGTEXT NOT NULL, ".
		 "`descript` LONGTEXT NULL, ".
		 "`registered` DATETIME NOT NULL, ".
		 "`updated` DATETIME NOT NULL, ".
		 "INDEX (`serviceid`), ".
		 "UNIQUE (`serviceid`,`methodname`), ".
		 "FOREIGN KEY (`serviceid`) REFERENCES `services`(`id`) ON DELETE CASCADE ON UPDATE CASCADE ".
		 ") ".
		 "ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;  ";
	
	$rename="RENAME TABLE `methods` TO `tmpmethods`;";
	
	$select="SELECT s.`id` AS `service_id`, t.* FROM `tmpmethods` t LEFT JOIN `services` s ON t.`serviceid`=s.`id`;";

	$remove="DROP TABLE `tmpmethods`; ";

	$tbnames=getTableNames(
		$dbdriver=$dbe['dbdriver'],
		$hostname=$dbe['hostname'],
		$username=$dbe["username"],
		$password=$dbe["password"],
		$dbname=$dbe["database"]
	);

	
	if(!in_array('methods',$tbnames)){
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
			$errprefix='initMethod()->create'
		);	
	}
	else{
		$clnames=getColumnNames(
			$dbdriver=$dbe['dbdriver'],
			$hostname=$dbe['hostname'],
			$username=$dbe["username"],
			$password=$dbe["password"],
			$dbname=$dbe["database"],
			$tbname='methods'
		);
		if(isset($clnames["SERVICENAME"])){
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
				$errprefix='initMethod()->rename'
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
				$errprefix='initMethod()->create'
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
					$errprefix='initMethod()->select'
				);
				if(is_object($adors)){
					$insert=array();
					foreach($adors->getRows() as $row=>$col)
					if(isset($col['servicename'])){
						$restricted=$col['published']==1?'off':'on';
						$insert[]="INSERT INTO `methods` (`id`,`serviceid`,`methodname`,`methodtype`,`restricted`,`sourcecode`,`descript`,`registered`,`updated`) VALUES ".
						"( ".$col['id'].",'".$col['service_id']."', '".$col['methodname']."', '".$col['methodtype']."', '".$restricted."', '".addslashes($col['sourcecode'])."', '".addslashes($col['desc'])."', NOW(), NOW() ); ";
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
							$errprefix='initMethod()->insert'
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
					$errprefix='initMethod()->remove'
				);
				if($result!='OK') exit($result);
			}
		}	
	}	
}

function syncMethodServices(){
	global $db;
		
	$result='';
	$dbe=$db["default"];

	$dml="SELECT m.*, s.`tns`, s.`url`, p.`organization` ".
		 "FROM `methods` m ".
		 "LEFT JOIN `services` s ON m.`servicename`=s.`servicename` ".
		 "LEFT JOIN `providers` p ON s.`provider`=p.`providername` ".
		 "WHERE s.`tns`='publisher' ";
	
	$adors=dbExecute(
		$dbdriver=$dbe['dbdriver'],
		$hostname=$dbe['hostname'],
		$username=$dbe['username'],
		$password=$dbe['password'],
		$dbname=$dbe['database'],
		$sql=$dml,
		$bindfield=false,
		$trx=false,
		$debug=$dbe['db_debug'],
		$errprefix='syncMethodServices()->getRows()'
	);
	if(is_object($adors)){
		$update=array();
		$methods=$adors->getRows();
		foreach($methods as $row=>$col)
		if(isset($col['sourcecode'])){
			$codes=read_opws($col["sourcecode"]);
			$_wstype=isset($codes["wstype"])?$codes["wstype"]:'rest';
			$_endpoint=isset($codes["endpoint"])?$codes["endpoint"]:$col["url"];
			$_method=isset($codes["method"])?$codes["method"]:'';
			$_accesskey=$col["accesskey"];
			$_request=isset($codes["request"])?$codes["request"]:'';
			$col["sourcecode"]=write_opws($_wstype,$_endpoint,$_method,$_accesskey,$_request);			
			$update[]="UPDATE `methods` SET `sourcecode`='".$col['sourcecode']."', `updated`=NOW() WHERE `id`=".$col['id'];
		}
		if(!empty($update)){
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
				$errprefix='syncMethodServices()->update'
			);
			if($result!='OK') exit($result);
		}			
	}
}


function existMethod($input){
	global $db;
	$result=0;

	$dbe=$db["default"];
	$dml="SELECT i.`instance`,s.`servicename`,m.`methodname` ".
		 "FROM `methods` m ".
		 "LEFT JOIN `services` s ON m.`serviceid`=s.`id` ".
		 "LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ".
		 "WHERE i.`instance` = ? AND s.`servicename` = ? AND m.`methodname` = ? LIMIT 1";

	$data=array($input[0],$input[1],$input[2]);

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
		$errprefix='existMethod()'
	);
	if(is_object($adors)) $result=$adors->RecordCount()>0;
	
	return $result;
}

function addMethod($input){
	global $db;

	$result='';
	if(empty($input["methodname"]) || empty($input["serviceid"])) return $result;

	$dbe=$db["default"];
	$dml="INSERT INTO `methods` (`methodname`, `serviceid`,`restricted`,`methodtype`,`sourcecode`,`descript`,`registered`,`updated`) ".
		 "VALUES ( ?, ?, ?, ?, ?, ?, NOW(), NOW() );";
	$data=array($input['methodname'],$input['serviceid'],$input['restricted'],$input['methodtype'],$input['sourcecode'],$input['descript']);

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
		$errprefix='addMethod()'
	);

	return $result;
}

function updateMethod($input){

	global $db;

	$result='';
	if(empty($input["methodname"]) || empty($input["serviceid"])) return $result;

	$dbe=$db["default"];
	$dml="UPDATE `methods` SET `methodname`=?, `serviceid`=?,`restricted`=?,`methodtype`=?,`sourcecode`=?, `descript`=?, `updated`=NOW() ".
		 "WHERE `id`=?";
	$data=array($input['methodname'], $input['serviceid'], $input['restricted'],$input['methodtype'],$input['sourcecode'],$input['descript'],$input['id']);

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
		$errprefix='updateMethod()'
	);

	return $result;
}

function deleteMethodByID($id){
	global $db;

	$result='';
	$dbe=$db["default"];
	$dml="DELETE FROM `methods` WHERE `id`=?";
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
		$errprefix='deleteMethodByID()'
	);

	return $result;//=='OK';
}

function getMethodByID($id){
	global $db;

	$result=false;

	$dbe=$db["default"];
	$dml="SELECT m.*, s.`servicename`, s.`servicetype`, i.`instance`, i.`organization` ".
		 "FROM `methods` m ".
		 "LEFT JOIN `services` s ON m.`serviceid`=s.`id` ".
		 "LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ".
		 "WHERE m.`id`=?";
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
		$errprefix='getMethodByID()'
	);
	if(is_object($adors)) $result=$adors->fields;

	return $result;
}

function getMethods($instance="",$servicename="",$methodname="",$page_row=0,$page_offset=0){
	global $db;
	$result=false;
	$limit=($page_row>0?"LIMIT $page_row OFFSET $page_offset":"");
	$instance=empty($instance)?"%":$instance;
	$servicename=empty($servicename)?"%":$servicename;
	$methodname=empty($methodname)?"%":$methodname;

	$dbe=$db["default"];

	$cond="";
	$cond=is_publisher()?" AND m.`methodtype` = 'services' ":$cond;
	$cond=is_provider()?" AND m.`methodtype` != 'services' ":$cond;
	
	$dml="SELECT m.*, s.`servicename`, s.`servicetype`, i.`instance`, i.`organization` ".
		 "FROM `methods` m ".
		 "LEFT JOIN `services` s ON m.`serviceid`=s.`id` ".
		 "LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ".
		 "WHERE m.`methodname` LIKE ? ".
		 "AND s.`servicename` LIKE ? ".
		 "AND i.`instance` LIKE ? $cond".
		 "ORDER BY `instance`,`servicename`,`registered` DESC $limit";
	
	$data=array($methodname,$servicename,$instance);
	
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
		$errprefix='getMethods()'
	);
	if(is_object($adors)) $result=$adors->getRows();
	
	return $result;
}


function getMethodRows($instance="",$servicename="",$methodname=""){
	global $db;
	$result=0;
	$instance=empty($instance)?"%":$instance;
	$servicename=empty($servicename)?"%":$servicename;
	$methodname=empty($methodname)?"%":$methodname;

	$dbe=$db["default"];
	$cond="";
	$cond=is_publisher()?" AND m.`methodtype` = 'services' ":$cond;
	$cond=is_provider()?" AND m.`methodtype` != 'services' ":$cond;
	
	$dml="SELECT COUNT(m.`id`) AS `rows`  ".
		 "FROM `methods` m ".
		 "LEFT JOIN `services` s ON m.`serviceid`=s.`id` ".
		 "LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ".
		 "WHERE m.`methodname` LIKE ? ".
		 "AND s.`servicename` LIKE ? ".
		 "AND i.`instance` LIKE ? $cond";
	
	$data=array($methodname,$servicename,$instance);
	
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
		$errprefix='getMethodRows()'
	);
	if(is_object($adors)) $result=$adors->fields('rows');
	

	return $result;
}
