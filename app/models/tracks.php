<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
*/

function initTrack(){
	global $db;
		
	$result='';
	$dbe=$db["default"];

	$create="CREATE TABLE IF NOT EXISTS `tracks` (".
		 "`id` BIGINT(20) NOT NULL AUTO_INCREMENT PRIMARY KEY, ".
		 "`userlog` VARCHAR(64) NOT NULL, ".
		 "`userip` VARCHAR(32) NOT NULL, ".
		 "`instance` VARCHAR(64) NOT NULL, ".
		 "`trackid` VARCHAR(128) NOT NULL, ".
		 "`trackname` VARCHAR(64) NOT NULL, ".
		 "`trackstatus` VARCHAR(16) NOT NULL, ".
		 "`tracknote` TEXT NOT NULL, ".
		 "`trackdata` MEDIUMTEXT NOT NULL, ".
		 "`registered` DATETIME NOT NULL ".
		 ") ".
		 "ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;  ";

	$rename="RENAME TABLE `trackers` TO `tracks`";

	$change="ALTER TABLE `tracks` ".
		 	"CHANGE `userlog` `userlog` VARCHAR(64) NOT NULL, ".
		 	"CHANGE `userip` `userip` VARCHAR(32) NOT NULL, ".
		 	"CHANGE `providername` `instance` VARCHAR(64) NOT NULL, ".
		 	"CHANGE `trackname` `trackname` VARCHAR(64) NOT NULL, ".
		 	"CHANGE `trackstatus` `trackstatus` VARCHAR(16) NOT NULL, ".
			"ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci; ";
	
	$tbnames=getTableNames(
		$dbdriver=$dbe['dbdriver'],
		$hostname=$dbe['hostname'],
		$username=$dbe["username"],
		$password=$dbe["password"],
		$dbname=$dbe["database"]
	);

	
	if(!in_array('tracks',$tbnames)){
		if(in_array('trackers',$tbnames)){
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
				$errprefix='initTrack()->rename'
			);	
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
					$errprefix='initTrack()->change'
				);		
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
				$errprefix='initTrack()->create'
			);	
		}
	}
}


/*------------------ Trackers Processing */
function addTrack($input,$instance="",$logname=""){
	global $db;

	$result='';
	$currInstance=empty($instance)?current_instance(current_instanceid()):$instance;
	$currLogname=empty($logname)?current_logname():$logname;
	if(empty($currInstance) || empty($currLogname) || empty($input["trackid"]) || empty($input["trackname"])  || empty($input["trackstatus"])) return $result;

	$dbe=$db["default"];
	$dml="INSERT INTO `tracks` (`instance`,`userlog`,`userip`,`trackid`,`trackname`,`trackstatus`,`tracknote`,`trackdata`,`registered`) ".
		 "VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, NOW() );";
	$data=array($currInstance,$currLogname,$_SERVER['REMOTE_ADDR'],$input['trackid'],$input['trackname'],$input['trackstatus'],$input['tracknote'],$input['trackdata']);

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
		$errprefix='addTrack()'
	);

	return $result;
}

function getTrackByID($id){
	global $db;

	$result=false;

	$dbe=$db["default"];
	$dml="SELECT * FROM `tracks` WHERE `id`=?";
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
		$errprefix='getTrackByID()'
	);
	if(is_object($adors)) $result=$adors->fields;

	return $result;
}

function getTrackStatusAPI($instance="",$trackid="",$page_row=0,$page_offset=0){
	global $db;
	$result=false;
	
	$instance.="%";
	$trackid.="%";
	$limit=($page_row>0?"LIMIT $page_row OFFSET $page_offset":"");

	$dbe=$db["default"];
	$dml="SELECT ".
		 "`trackname`,".
		 "`instance`,".
		 "`trackid`,".
	     "SUM(IF(`trackstatus`='SUCCESS',1,0)) as `success`,".
		 "SUM(IF(`trackstatus`='FAIL',1,0)) as `fail` ".
		 "FROM `tracks` GROUP BY `instance`,`trackid` HAVING `trackname` ='api' ".
		 "AND `instance` LIKE ? AND `trackid` LIKE ? ".$limit;
	$data=array($instance,$trackid);
	
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
		$errprefix='getTrackStatusAPI()'
	);
	if(is_object($adors)) $result=$adors->getRows();

	return $result;
	
}

function getTrackStatusAPIRows($instance="",$trackid=""){
	global $db, $ADODB_COUNTRECS;
	$result=false;
	
	$instance.="%";
	$trackid.="%";

	$dbe=$db["default"];
	$dml="SELECT ".
		 "`trackname`,".
		 "`instance`,".
		 "`trackid`,".
	     "SUM(IF(`trackstatus`='SUCCESS',1,0)) as `success`,".
		 "SUM(IF(`trackstatus`='FAIL',1,0)) as `fail` ".
		 "FROM `tracks` GROUP BY `instance`,`trackid` HAVING `trackname` ='api' ".
		 "AND `instance` LIKE ? AND `trackid` LIKE ?";
	$data=array($instance,$trackid);

	$ADODB_COUNTRECS=true;
	
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
		$errprefix='getTrackStatusAPIRows()'
	);
	if(is_object($adors)) $result=$adors->RecordCount();

	$ADODB_COUNTRECS=false;
	
	return $result;
	
}
function getTracks($logname="",$instance="",$startdate="",$enddate="",$page_row=0,$page_offset=0){
	global $db;

	$result=false;
	$limit=($page_row>0?"LIMIT $page_row OFFSET $page_offset":"");
	$logname.="%";
	$instance.="%";
	$startdate=substr($startdate,0,4)=='0000'?'':$startdate;
	$enddate=substr($enddate,0,4)=='0000'?'':$enddate;

	$select="SELECT t.`id`,t.`userlog`,t.`userip`,t.`instance`,t.`trackid`,t.`trackname`,t.`trackstatus`,t.`tracknote`,t.`trackdata`,t.`registered`
	FROM `tracks` t WHERE t.`userlog` LIKE ? AND t.`instance` LIKE ? ";
	$order="ORDER BY `registered` desc ";
	

	$dbe=$db["default"];
	$dateParam="";
	if (!empty($startdate) && !empty($enddate)) {
			$dateParam="AND t.`registered` between ? AND ? ";        
			$dml=$select.$dateParam.$order.$limit;
			$data=array($logname,$instance,$startdate,$enddate." 23:59:59");
	}
	elseif(!empty($startdate)){
			$dateParam="AND t.`registered` >= ? ";
			$dml=$select.$dateParam.$order.$limit;
			$data=array($logname,$instance,$startdate);
	}
	elseif(!empty($enddate)){
			$dateParam="AND t.`registered` <= ? ";
			$dml=$select.$dateParam.$order.$limit;
			$data=array($logname,$instance,$enddate." 23:59:59");
	}		
	else{
			$dml=$select.$order.$limit;
			$data=array($logname,$instance);
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
		$errprefix='getTracks()'
	);
	if(is_object($adors)) $result=$adors->getRows();
	
	return $result;
}

function getTrackRows($logname="",$instance="",$startdate="",$enddate="" ){
	global $db;//, $ADODB_COUNTRECS;
	$result=0;
	$logname.="%";
	$instance.="%";
	$order='';
	$limit='';
	$startdate=substr($startdate,0,4)=='0000'?'':$startdate;
	$enddate=substr($enddate,0,4)=='0000'?'':$enddate;

	$select="SELECT COUNT(*) AS `rows` FROM `tracks` t WHERE t.`userlog` LIKE ? AND t.`instance` LIKE ? ";
	
	$dbe=$db["default"];
	$dateParam="";
	if (!empty($startdate) && !empty($enddate)) {
			$dateParam="AND t.`registered` between ? AND ? ";        
			$dml=$select.$dateParam.$order.$limit;
			$data=array($logname,$instance,$startdate,$enddate." 23:59:59");
	}
	elseif(!empty($startdate)){
			$dateParam="AND t.`registered` >= ? ";
			$dml=$select.$dateParam.$order.$limit;
			$data=array($logname,$instance,$startdate);
	}
	elseif(!empty($enddate)){
			$dateParam="AND t.`registered` <= ? ";
			$dml=$select.$dateParam.$order.$limit;
			$data=array($logname,$instance,$enddate." 23:59:59");
	}		
	else{
			$dml=$select.$order.$limit;
			$data=array($logname,$instance);
	}	

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
		$errprefix='getTrackRows()'
	);
	if(is_object($adors)) $result=$adors->fields('rows');

	//$ADODB_COUNTRECS = false;
	
	return $result;
}
