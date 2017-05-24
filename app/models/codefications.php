<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
*/

function initCode(){
	global $db;
		
	$result='';
	$dbe=$db["default"];

	$create="CREATE TABLE IF NOT EXISTS `codefications` (".
		 "`id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY, ".
		 "`groupcode` VARCHAR(64) NOT NULL, ".
		 "`groupname` TINYTEXT NOT NULL, ".
		 "`code` VARCHAR(64) NOT NULL, ".
		 "`name` TINYTEXT NOT NULL, ".
		 "`seq` BIGINT(20) NOT NULL, ".
		 "`value` TEXT NOT NULL, ".
		 "`parentcode` VARCHAR(64) NOT NULL, ".
		 "`descript` LONGTEXT NULL, ".
		 "`registered` DATETIME NOT NULL, ".
		 "`updated` DATETIME NOT NULL, ".
		 "UNIQUE (`groupcode`,`code`) ".
		 ") ".
		 "ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;  ";

	$change="ALTER TABLE `codefications` ".
		 	"CHANGE `groupcode` `groupcode` VARCHAR(64) NOT NULL, ".
		 	"CHANGE `code` `code` VARCHAR(64) NOT NULL, ".
		 	"CHANGE `parentcode` `parentcode` VARCHAR(64) NOT NULL, ".
		 	"CHANGE `desc` `descript` LONGTEXT NOT NULL, ".
		 	"ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci; ";
	
	$tbnames=getTableNames(
		$dbdriver=$dbe['dbdriver'],
		$hostname=$dbe['hostname'],
		$username=$dbe["username"],
		$password=$dbe["password"],
		$dbname=$dbe["database"]
	);
	
	if(!in_array('codefications',$tbnames)){
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
			$errprefix='initCode()->create'
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
			$tbname='codefications'
		);
		if(isset($clnames["DESC"])){		
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
				$errprefix='initCode()->change'
			);	
			if($result!='OK') exit($result);
		}
	}
}


function existCode($groupCode='',$code=''){
	global $db;
	$result=0;

	$dbe=$db["default"];
	$dml="SELECT `code` FROM `codefications` WHERE `groupcode` = ? AND `code` = ? LIMIT 1";
	$data=array($groupCode,$code);

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
		$errprefix='existCode()'
	);
	if(is_object($adors)) $result=$adors->RecordCount()>0;

	return $result;
}

function addCode($input){
	global $db;

	$result='';
	if(empty($input["groupcode"]) || empty($input["code"])) return $result;
	
	$dbe=$db["default"];
	$dml="INSERT INTO `codefications` (`groupcode`,`groupname`,`code`,`name`,`seq`,`value`,`parentcode`,`descript`,`registered`,`updated`) ".
		 "VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW() );";
	$data=array($input['groupcode'],$input['groupname'],$input['code'],$input['name'],$input['seq'],$input['value'],$input['parentcode'],$input['descript']);
	
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
		$errprefix='addCode()'
	);
	
	return $result;
}

function updateCodeByID($input){
	global $db;

	$result='';
	if(empty($input["groupcode"]) || empty($input["code"])) return $result;

	$dbe=$db["default"];
	$dml="UPDATE `codefications` SET `groupcode`=?,`groupname`=?,`code`=?,`name`=?,`seq`=?,`value`=?,`parentcode`=?,`descript`=?, `updated`=NOW() ".
		 "WHERE `id`=?";
	$data=array($input['groupcode'],$input['groupname'],$input['code'],$input['name'],$input['seq'],$input['value'],$input['parentcode'],$input['descript'],$input['id']);

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
		$errprefix='updateCodeByID()'
	);

	return $result;
}

function saveCode($input){
	global $db;

	$result='';
	if(empty($input["groupcode"]) || empty($input["code"])) return $result;

	$dbe=$db["default"];
	$registered=$input['registered']==''?'NOW()':"'".$input['registered']."'";
	
	$dml="DELETE FROM `codefications` WHERE `groupcode`=? AND `code`=?";
	$data=array($input['groupcode'],$input['code']);
	dbExecute(
		$dbdriver=$dbe['dbdriver'],
		$hostname=$dbe['hostname'],
		$username=$dbe['username'],
		$password=$dbe['password'],
		$dbname=$dbe['database'],
		$sql=$dml,
		$bindfield=$data,
		$trx=true,
		$debug=$dbe['db_debug'],
		$errprefix='saveCode()'
	);

	$dml="INSERT INTO `codefications` (`groupcode`,`groupname`,`code`,`name`,`seq`,`value`,`parentcode`,`descript`,`registered`,`updated`) ".
		 "VALUES (?,?,?,?,?,?,?,?,?,NOW())";
	$data=array($input['groupcode'],$input['groupname'],$input['code'],$input['name'],$input['seq'],$input['value'],$input['parentcode'],$input['descript'],$registered);
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
		$errprefix='saveCode()'
	);

	return $result;
}


function deleteCodeByID($id){
	global $db;

	$result='';

	$dbe=$db["default"];
	$dml="DELETE FROM `codefications` WHERE `id`=?";
	$dataarray($id);
	
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
		$errprefix='deleteCodeByID()'
	);
	
	return $result;
}


function deleteCodeByGroupCode($groupCode){
	global $db;

	$result='';

	$dbe=$db["default"];
	$dml="DELETE FROM `codefications` WHERE `groupcode`=?";
	$data=array($groupCode);
	
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
		$errprefix='deleteCodeByGroupCode()'
	);
	
	return $result;
}

function getCodeByID($id){
	global $db;

	$result=false;

	$dbe=$db["default"];
	$dml="SELECT * FROM `codefications` WHERE `id`=?";
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
		$errprefix='getCodeByID()'
	);
	if(is_object($adors)) $result=$adors->fields;
	
	return $result;
}

function getCodeByGroupCode($groupCode){
	global $db;

	$result=false;

	$dbe=$db["default"];
	$dml="SELECT * FROM `codefications` WHERE `groupcode`=?";
	$data=array($groupCode);
	
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
		$errprefix='getCodeByGroupCode(): '
	);
	if(is_object($adors)) $result=$adors->getRows();
	
	return $result;
}

function getCodes($groupCode="",$code="",$page_row=0,$page_offset=0){
	global $db;

	$result=false;
	
	$limit=($page_row>0?"LIMIT $page_row OFFSET $page_offset":"");
	$groupCode=empty($groupCode)?"%":$groupCode;
	$code=empty($code)?"%":$code;

	$dbe=$db["default"];
	$dml="SELECT * FROM `codefications` WHERE `groupcode` LIKE ? AND `code` LIKE ? ORDER BY `groupcode`,`code`,`seq` $limit";
	$data=array($groupCode,$code);

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
		$errprefix='getCodes()'
	);
	if(is_object($adors)) $result=$adors->getRows();

	return $result;
}


function getCodeRows($groupCode="",$code=""){
	global $db;
	$result=0;
	$groupCode=empty($groupCode)?"%":$groupCode;
	$code=empty($code)?"%":$code;

	$dbe=$db["default"];
	$dml="SELECT COUNT(*) AS `rows` FROM `codefications` WHERE `groupcode` LIKE ? AND `code` LIKE ?  ";
	$data=array($groupCode,$code);

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
		$errprefix='getCodeRows()'
	);
	if(is_object($adors)) $result=$adors->fields('rows');

	return $result;
}
