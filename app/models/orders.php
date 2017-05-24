<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
*/

function initOrder(){
	global $db;
		
	$result='';
	$dbe=$db["default"];

	$create="CREATE TABLE IF NOT EXISTS `orders` (".
		 "`id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY, ".
		 "`userlog` VARCHAR(64) NOT NULL, ".
		 "`accesskey` VARCHAR(64) NOT NULL, ".
		 "`methodid` BIGINT(20) NOT NULL, ".
		 "`orderstatus` ENUM('off','on') NOT NULL, ".
		 "`registered` DATETIME NOT NULL, ".
		 "`updated` DATETIME NOT NULL, ".
		 "INDEX (`userlog`), ".
		 "FOREIGN KEY (`userlog`) REFERENCES `users`(`logname`) ON DELETE CASCADE ON UPDATE CASCADE ".
		 ") ".
		 "ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;  ";

	$rename="RENAME TABLE `tokens` TO `tmporders`";

	$select="SELECT t.*,m.id AS `method_id` FROM `tmporders` t LEFT JOIN `methods` m ON t.`servicename`=m.`servicename` AND t.`methodname`=m.`methodname` ; ";
		 	
	$remove="DROP TABLE `tmporders` ";
	
	$tbnames=getTableNames(
		$dbdriver=$dbe['dbdriver'],
		$hostname=$dbe['hostname'],
		$username=$dbe["username"],
		$password=$dbe["password"],
		$dbname=$dbe["database"]
	);
	

	if(!in_array('orders',$tbnames)){
		if(in_array('tokens',$tbnames)){
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
				$errprefix='initOrder()->rename'
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
				$errprefix='initOrder()->create'
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
					$errprefix='initOrder()->select'
				);

				if(is_object($adors)){
					$insert=array();
					foreach($adors->getRows() as $row=>$col)
					if(isset($col['methodname'])){
						$orderstatus=$col['tokenstatus']==1?'on':'off';
						$insert[]="INSERT INTO `orders` (`id`,`userlog`,`accesskey`,`methodid`,`orderstatus`,`registered`,`updated`) VALUES ".
						"( ".$col['id'].",'".$col['userlog']."', '".$col['token']."', '".$col['method_id']."', '".$orderstatus."', NOW(), NOW() ); ";
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
							$errprefix='initOrder()->insert'
						);
						if($result!='OK') exit($result);
					}
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
				$errprefix='initOrder()->remove'
			);
			if($result!='OK') exit($result);
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
				$errprefix='initOrder()->create'
			);	
		}
	}
}

function existOrder($accesskey=''){
	global $db;
	$result=0;

	$dbe=$db["default"];
	$dml="SELECT o.* FROM `orders` o ".
		 "LEFT JOIN `methods` m ON o.`methodid`=m.`id` ".
		 "LEFT JOIN `services` s ON m.`serviceid`=s.`id` ".
		 "LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ".		 
		 "WHERE o.`accesskey` = ? LIMIT 1";
	$data=array($accesskey);

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
		$errprefix='existOrder()'
	);
	if(is_object($adors)) $result=$adors->RecordCount()>0;

	return $result;
}

function userOrder($input){
	global $db;
	$result=0;

	$dbe=$db["default"];
	$dml="SELECT o.* FROM `orders` o ".
		 "LEFT JOIN `methods` m ON o.`methodid`=m.`id` ".
		 "LEFT JOIN `services` s ON m.`serviceid`=s.`id` ".
		 "LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ".		 
		 "WHERE o.`userlog` = ? AND i.`instance`= ? AND s.`servicename` = ? AND m.`methodname` = ? LIMIT 1";
	$data=array($input[0],$input[1],$input[2],$input[3]);

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
		$errprefix='userOrder()'
	);
	if(is_object($adors)) $result=$adors->fields;
	
	return $result;
}


/*------------------ Orders Processing */

function addOrder($input){
	global $db;

	$result='';
	
	if(empty($input["accesskey"]) || empty($input["userlog"]) || empty($input["methodid"])) return $result;
	$input["orderstatus"]=empty($input["orderstatus"])?"off":$input["orderstatus"];


	$dbe=$db["default"];
	$dml="INSERT INTO `orders` (`accesskey`,`userlog`,`methodid`,`orderstatus`,`registered`,`updated`) ".
		 "VALUES ( ?, ?, ?, ?, NOW(), NOW() );";
	
	$data=array($input['accesskey'],$input['userlog'],$input['methodid'],$input['orderstatus']);

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
		$errprefix='addOrder()'
	);

	return $result;
}

function updateOrder($input){
	global $db;
	$result='';
	if(empty($input["accesskey"]) || empty($input["userlog"]) || empty($input["methodid"])) return $result;
	$input["orderstatus"]=empty($input["orderstatus"])?"off":$input["orderstatus"];

	$dbe=$db["default"];
	$dml="UPDATE `orders` SET `accesskey`=?,`userlog`=?,`methodid`=?,`orderstatus`=?, `updated`=NOW() ".
		 "WHERE `id`=?";
	$data=array($input['accesskey'],$input['userlog'],$input['methodid'],$input['orderstatus'],$input['id']);

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
		$errprefix='updateOrder()'
	);

	return $result;
}


function deleteOrderByID($id){

	global $db;

	$result='';

	$dbe=$db["default"];
	$dml="DELETE FROM `orders` WHERE `id`=?";
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
		$errprefix='deleteOrderByID()'
	);

	return $result;
}

function getOrderByID($id){
	global $db;

	$result=false;

	$dbe=$db["default"];
	$dml="SELECT o.*,ui.`instance` AS `userinstance`,ui.`organization` AS `userorg`,i.`instance`,i.`organization`,s.`servicename`,m.`methodname`,m.`methodtype` ".
		 "FROM `orders` o ".
		 "LEFT JOIN `users` u ON o.`userlog`=u.`logname` ".
		 "LEFT JOIN `instances` ui ON u.`instanceid`=ui.`id` ".
		 "LEFT JOIN `methods` m ON o.`methodid`=m.`id` ".
		 "LEFT JOIN `services` s ON m.`serviceid`=s.`id` ".
		 "LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ".		 
		 "WHERE o.`id`=?";
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
		$errprefix='getOrderByID()'
	);
	if(is_object($adors)) $result=$adors->fields;

	return $result;
}

function getOrders($logname="",$accesskey="",$instance="",$page_row=0,$page_offset=0){

	global $db;

	$result=false;
	$limit=($page_row>0?"LIMIT $page_row OFFSET $page_offset":"");
	$logname=empty($logname)?"%":$logname;
	$accesskey=empty($accesskey)?"%":$accesskey;
	$instance=empty($instance)?"%":$instance;
	
	$dbe=$db["default"];
	$cond="";
	$cond=is_publisher()?" AND m.`methodtype` = 'services' ":$cond;
	$cond=is_provider()?" AND m.`methodtype` != 'services' ":$cond;

	$dml="SELECT o.*,ui.`instance` AS `userinstance`,ui.`organization` AS `userorg`,i.`instance`,i.`organization`,s.`servicename`,m.`methodname`,m.`methodtype` ".
		 "FROM `orders` o ".
		 "LEFT JOIN `users` u ON o.`userlog`=u.`logname` ".
		 "LEFT JOIN `instances` ui ON u.`instanceid`=ui.`id` ".
		 "LEFT JOIN `methods` m ON o.`methodid`=m.`id` ".
		 "LEFT JOIN `services` s ON m.`serviceid`=s.`id` ".
		 "LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ".
		 "WHERE o.`userlog` LIKE ? AND o.`accesskey` LIKE ? AND i.`instance` LIKE ? $cond ".
		 "ORDER BY `userlog` ASC,`registered` DESC $limit";
	$data=array($logname,$accesskey,$instance);

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
		$errprefix='getOrders()'
	);
	if(is_object($adors)) $result=$adors->getRows();

	return $result;
}

function getOrderRows($logname="",$accesskey="",$instance=""){
	global $db;
	$result=0;
	$logname=empty($logname)?"%":$logname;
	$accesskey=empty($accesskey)?"%":$accesskey;
	$instance=empty($instance)?"%":$instance;

	$dbe=$db["default"];
	$cond="";
	$cond=is_publisher()?" AND m.`methodtype` = 'services' ":$cond;
	$cond=is_provider()?" AND m.`methodtype` != 'services' ":$cond;

	$dml="SELECT COUNT(o.`id`) AS `rows` ".
			"FROM `orders` o ".
		 	"LEFT JOIN `users` u ON o.`userlog`=u.`logname` ".
			"LEFT JOIN `instances` ui ON u.`instanceid`=ui.`id` ".
			"LEFT JOIN `methods` m ON o.`methodid`=m.`id` ".
			"LEFT JOIN `services` s ON m.`serviceid`=s.`id` ".
			"LEFT JOIN `instances` i ON s.`instanceid`=i.`id` ".
			"WHERE o.`userlog` LIKE ? AND o.`accesskey` LIKE ? AND i.`instance` LIKE ? $cond ";

	$data=array($logname,$accesskey,$instance);

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
		$errprefix='getOrderRows()'
	);
	if(is_object($adors)) $result=$adors->fields('rows');

	return $result;
}

