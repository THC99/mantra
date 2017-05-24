<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
*/

function getResourceByID($id=""){
	global $db;
	$result=false;

	$dbe=$db["default"];
	$dml="SELECT m.*,i.`instance`,i.`organization`,s.`servicename`,s.`servicetype` ".
			"FROM `methods` m ".
			"LEFT JOIN `services` s ON m.`serviceid`=s.`id` ".
			"LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ".
			"WHERE m.`id` = ? ";
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
		$errprefix='getResourceByID()'
	);
	if(is_object($adors)) $result=$adors->fields;
	
	return $result;
}

function getResourceByScope($instance,$servicename,$methodname){
	global $db;
	$result=false;

	$dbe=$db["default"];
	$dml="SELECT m.*,i.`instance`,i.`organization`,s.`servicename`,s.`servicetype` ".
			"FROM `methods` m ".
			"LEFT JOIN `services` s ON m.`serviceid`=s.`id` ".
			"LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ".
			"WHERE i.`instance` = ? AND s.`servicename` = ? AND m.`methodname` = ? ";

	$data=array($instance,$servicename,$methodname);

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
		$errprefix='getResourceByScope()'
	);
	if(is_object($adors)) $result=$adors->fields;

	return $result;
}

function getResources($resource,$page_row=0,$page_offset=0){
	global $db;
	$result=false;
	if(!isset($resource["instance"]) || !isset($resource["servicename"]) || !isset($resource["methodname"])) return $result;
	$limit=($page_row>0?"LIMIT $page_row OFFSET $page_offset":"");
	$resource["instance"]=empty($resource["instance"])?"%":$resource["instance"];
	$resource["servicename"]=empty($resource["servicename"])?"%":$resource["servicename"];
	$resource["methodname"]=empty($resource["methodname"])?"%":$resource["methodname"];

	$dbe=$db["default"];
	$cond="";
	$cond=is_publisher()?" AND m.`methodtype` = 'services' ":$cond;
	$cond=is_provider()?" AND m.`methodtype` != 'services' ":$cond;

	$dml="SELECT m.*,i.`instance`,i.`organization`,s.`servicename`,s.`servicetype` ".
			"FROM `methods` m ".
			"LEFT JOIN `services` s ON m.`serviceid`=s.`id` ".
			"LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ".
			"WHERE i.`instance` LIKE ? AND s.`servicename` LIKE ? AND m.`methodname` LIKE ? $cond".
			"ORDER BY m.`registered` DESC ".
			"$limit";

	$data=array($resource["instance"],$resource["servicename"],$resource["methodname"]);
	
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
		$errprefix='getResources()'
	);
	if(is_object($adors)) $result=$adors->getRows();
	
	return $result;
}

function getResourceRows($resource){
	global $db;
	$result=0;
	if(!isset($resource["instance"]) || !isset($resource["servicename"]) || !isset($resource["methodname"])) return $result;
	$resource["instance"]=empty($resource["instance"])?"%":$resource["instance"];
	$resource["servicename"]=empty($resource["servicename"])?"%":$resource["servicename"];
	$resource["methodname"]=empty($resource["methodname"])?"%":$resource["methodname"];

	$dbe=$db["default"];
	$cond="";
	$cond=is_publisher()?" AND m.`methodtype` = 'services' ":$cond;
	$cond=is_provider()?" AND m.`methodtype` != 'services' ":$cond;

	$dml="SELECT COUNT(*) AS `rows` ".
			"FROM `methods` m ".
			"LEFT JOIN `services` s ON m.`serviceid`=s.`id` ".
			"LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ".
			"WHERE i.`instance` LIKE ? AND s.`servicename` LIKE ? AND m.`methodname` LIKE ? $cond";

	$data=array($resource["instance"],$resource["servicename"],$resource["methodname"]);

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
		$errprefix='getResourceRows()'
	);
	if(is_object($adors)) $result=$adors->fields('rows');
	
	return $result;
}

function getPubResources($resource,$page_row=0,$page_offset=0){
	global $db;
	$result=false;
	if(!isset($resource["instance"]) || !isset($resource["servicename"]) || !isset($resource["methodname"])) return $result;
	$limit=($page_row>0?"LIMIT $page_row OFFSET $page_offset":"");
	$resource["instance"]=empty($resource["instance"])?"%":$resource["instance"];
	$resource["servicename"]=empty($resource["servicename"])?"%":$resource["servicename"];
	$resource["methodname"]=empty($resource["methodname"])?"%":$resource["methodname"];

	$dbe=$db["default"];
	$dml="SELECT m.*,i.`instance`,i.`organization`,s.`servicename`,s.`servicetype` ".
			"FROM `methods` m ".
			"LEFT JOIN `services` s ON m.`serviceid`=s.`id` ".
			"LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ".
			"WHERE i.`instance` LIKE ? AND s.`servicename` LIKE ? AND m.`methodname` LIKE ? AND m.`restricted`='off' ".
			"ORDER BY m.`registered` DESC ".
			"$limit";

	$data=array($resource["instance"],$resource["servicename"],$resource["methodname"]);
	
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
		$errprefix='getPubResources()'
	);
	if(is_object($adors)) $result=$adors->getRows();
	
	return $result;
}

function getPubResourceRows($resource){
	global $db;
	$result=0;
	if(!isset($resource["instance"]) || !isset($resource["servicename"]) || !isset($resource["methodname"])) return $result;
	$resource["instance"]=empty($resource["instance"])?"%":$resource["instance"];
	$resource["servicename"]=empty($resource["servicename"])?"%":$resource["servicename"];
	$resource["methodname"]=empty($resource["methodname"])?"%":$resource["methodname"];

	$dbe=$db["default"];

	$dml="SELECT COUNT(*) AS `rows` ".
		 "FROM `methods` m ".
		 "LEFT JOIN `services` s ON m.`serviceid`=s.`id` ".
		 "LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ".
		 "WHERE i.`instance` LIKE ? AND s.`servicename` LIKE ? AND m.`methodname` LIKE ? AND m.`restricted`='off' ";

	$data=array($resource["instance"],$resource["servicename"],$resource["methodname"]);

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
		$errprefix='getPubResourceRows()'
	);
	if(is_object($adors)) $result=$adors->fields('rows');
	
	return $result;
}

/*---------------------- Order Resource ------------------------*/

function getOrderResourceByID($id=""){
	global $db;
	$result=false;

	$dbe=$db["default"];
	$dml="SELECT o.*,i.`instance`,i.`organization`,s.`servicename`,s.`servicetype`,m.`methodname`,m.`methodtype`,m.`restricted`,m.`sourcecode`,m.`descript` ".
			"FROM `orders` o ".
			"LEFT JOIN `methods` m ON o.`methodid`= m.`id` ".
			"LEFT JOIN `services` s ON m.`serviceid`=s.`id` ".
			"LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ".
			"WHERE o.`id` = ? ";
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
		$errprefix='getOrderResourceByID()'
	);
	if(is_object($adors)) $result=$adors->fields;
	
	return $result;
}

function getOrderResourceByScope($accesskey,$instance,$servicename,$methodname){
	global $db;
	$result=false;

	$dbe=$db["default"];
	$dml="SELECT o.*,i.`instance`,i.`organization`,s.`servicename`,s.`servicetype`,m.`methodname`,m.`methodtype`,m.`restricted`,m.`sourcecode`,m.`descript` ".
			"FROM `orders` o ".
			"LEFT JOIN `methods` m ON o.`methodid`=m.`id` ".
			"LEFT JOIN `services` s ON m.`serviceid`=s.`id` ".
			"LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ".
			"WHERE o.`orderstatus`='on' AND o.`accesskey`= ? AND i.`instance` LIKE ? AND s.`servicename` LIKE ? AND m.`methodname` LIKE ? ";

	$data=array($accesskey,$instance,$servicename,$methodname);
	
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
		$errprefix='getOrderResourceByScope()'
	);
	if(is_object($adors)) $result=$adors->fields;
	
	return $result;
}

function getOrderResources($resource,$page_row=0,$page_offset=0){
	global $db;
	$result=false;
	if(!isset($resource["instance"]) || !isset($resource["servicename"]) || !isset($resource["methodname"])) return $result;
	$limit=($page_row>0?"LIMIT $page_row OFFSET $page_offset":"");
	$resource["instance"]=empty($resource["instance"])?"%":$resource["instance"];
	$resource["servicename"]=empty($resource["servicename"])?"%":$resource["servicename"];
	$resource["methodname"]=empty($resource["methodname"])?"%":$resource["methodname"];

	$dbe=$db["default"];
	$dml="SELECT o.*,i.`instance`,i.`organization`,s.`servicename`,s.`servicetype`,m.`methodname`,m.`methodtype`,m.`restricted`,m.`sourcecode`,m.`descript` ".
			"FROM `orders` o ".
			"LEFT JOIN `methods` m ON o.`methodid`=m.`id` ".
			"LEFT JOIN `services` s ON m.`serviceid`=s.`id` ".
			"LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ".
			"WHERE o.`orderstatus`='on' AND o.`userlog`= ? AND i.`instance` LIKE ? AND s.`servicename` LIKE ? AND m.`methodname` LIKE ? ".
			"ORDER BY o.`registered` DESC ".
			"$limit";

	$data=array($resource["userlog"],$resource["instance"],$resource["servicename"],$resource["methodname"]);
	
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
		$errprefix='getOrderResources()'
	);
	if(is_object($adors)) $result=$adors->getRows();
	
	return $result;
}

function getOrderResourceRows($resource){
	global $db;
	$result=0;
	if(!isset($resource["instance"]) || !isset($resource["servicename"]) || !isset($resource["methodname"])) return $result;

	$resource["instance"]=empty($resource["instance"])?"%":$resource["instance"];
	$resource["servicename"]=empty($resource["servicename"])?"%":$resource["servicename"];
	$resource["methodname"]=empty($resource["methodname"])?"%":$resource["methodname"];

	$dbe=$db["default"];
	$dml="SELECT COUNT(*) AS `rows` ".
			"FROM `orders` o ".
			"LEFT JOIN `methods` m ON o.`methodid`=m.`id` ".
			"LEFT JOIN `services` s ON m.`serviceid`=s.`id` ".
			"LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ".
			"WHERE o.`orderstatus`='on' AND o.`userlog` = ? AND i.`instance` LIKE ? AND s.`servicename` LIKE ? AND m.`methodname` LIKE ? ";
			
	$data=array($resource["userlog"],$resource["instance"],$resource["servicename"],$resource["methodname"]);
	
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
		$errprefix='getOrderResourceRows()'
	);
	if(is_object($adors)) $result=$adors->fields('rows');
	
	return $result;
}
