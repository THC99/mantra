<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
*/


function initCache(){
	global $db;
		
	$result='';
	$dbe=$db["default"];

	$create="CREATE TABLE IF NOT EXISTS `caches` (".
		 "`id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY, ".
		 "`uri` TEXT NOT NULL, ".
		 "`content` LONGTEXT, ".
		 "`validity` TINYINT(1) NOT NULL, ".
		 "`cached` DATETIME NOT NULL ".
		 ") ".
		 "ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;  ";

	$change="ALTER TABLE `caches` ".
		 	    "CHANGE `valid` `validity` TINYINT(1) NOT NULL, ".
		 			"ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci; ";
	
	$tbnames=getTableNames(
		$dbdriver=$dbe['dbdriver'],
		$hostname=$dbe['hostname'],
		$username=$dbe["username"],
		$password=$dbe["password"],
		$dbname=$dbe["database"]
	);
	
	if(!in_array('caches',$tbnames)){
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
			$errprefix='initCache()->create'
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
			$tbname='caches'
		);
		if(isset($clnames["VALID"])){	
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
				$errprefix='initCache()->change'
			);	
			if($result!='OK') exit($result);
		}
	}
}


/*------------------ Caches Processing */
function writeCache($uri,$content,$valid=0){
	global $db;

	$result='';
	if(empty($uri) || empty($content)) return $result;

	$dbe=$db["default"];
	$dml="DELETE FROM `caches` WHERE `uri`=?";
	$data=array($uri);
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
		$errprefix='writeCache()'
	);

	$dml="INSERT INTO `caches` (`uri`,`content`,`validity`,`cached`) VALUES (?,?,?,NOW())";
	$data=array($uri,$content,$valid);
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
		$errprefix='writeCache()'
	);

	return $result;
}

function readCache($uri){
	global $db;

	$result=false;

	$dbe=$db["default"];
	$dml="SELECT * FROM `caches` WHERE `uri`=? LIMIT 1";
	$data=array($uri);

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
		$errprefix='readCache()'
	);
	if(is_object($adors)){
		$result=$adors->fields;
	}

	return $result;
}

function getCacheByID($id){
	global $db;

	$result=false;

	$dbe=$db["default"];
	$dml="SELECT * FROM `caches` WHERE `id`=?";
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
		$errprefix='getCacheByID()'
	);
	if(is_object($adors)){
		$result=$adors->fields;
	}
	
	return $result;
}

function getCaches($uri="",$page_row=0,$page_offset=0){

	global $db;

	$result=false;
	$limit=($page_row>0?"LIMIT $page_row OFFSET $page_offset":"");
	$uri=empty($uri)?"%":$uris;

	$dbe=$db["default"];
	$dml="SELECT * FROM `caches` WHERE `uri` LIKE ? ORDER BY `cached` DESC  $limit";
	$data=array($uri);

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
		$errprefix='getCaches()'
	);
	if(is_object($adors)){
		$result=$adors->getRows();
	}

	return $result;
}

function getCachesRows($uri=""){
	global $db;
	$result=0;
	$uri=empty($uri)?"%":$uris;

	$dbe=$db["default"];
	$dml="SELECT COUNT(*) AS `rows` FROM `caches` WHERE `uri` LIKE ? ";
	$data=array($uri);


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
		$errprefix='getCachesRows()'
	);
	if(is_object($adors)){
		$result=$adors->fields('rows');
	}

	return $result;
}

