<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
*/
function initNotification(){
	global $db;
		
	$result='';
	$dbe=$db["default"];

	$create="CREATE TABLE IF NOT EXISTS `notifications` (".
  				"`id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, ".
		    	"`nama` varchar(30) DEFAULT NULL, ".
 					"`level` varchar(20) NOT NULL, ".
  				"`tipe` varchar(10) DEFAULT NULL, ".
  				"`subyek` varchar(50) DEFAULT NULL, ".
  				"`pesan` varchar(300) DEFAULT NULL ".
  				") ".
   		    "ENGINE=InnoDB CHARACTER SET utf8 COLLATE utf8_unicode_ci;  ";

	$insert=array();
	$insert[0]="INSERT INTO `notifications` (`id`, `nama`, `level`, `tipe`, `subyek`, `pesan`) VALUES(1, 'tambah pesanan', '0,1', 'info', '[MANTRA INFO] Permohonan Akses Layanan', 'Permohonan akses ditambahkan oleh requester:<br/><br/>\nID Requester = value1<br/>\nNama Instansi = value2<br/>\nNama Layanan = value3<br/>\nNama Metode = value4<br/><br/>\nSilahkan login pada Aplikasi MANTRA untuk melihat daftar pesanan akses layanan Anda.');";
	$insert[1]="INSERT INTO `notifications` (`id`, `nama`, `level`, `tipe`, `subyek`, `pesan`) VALUES(2, 'persetujuan akses', '2', 'info', '[MANTRA INFO] Persetujuan Akses Layanan', 'Permohonan Anda telah disetujui oleh Penyedia Layanan.<br/><br/>\nID Requester = value1<br/>\nNama Layanan = value2<br/>\nNama Fungsi = value3<br/>\nInstansi Penyedia = value4<br/><br/>\nSilahkan login pada Aplikasi MANTRA untuk mengunduh adapter layanan.');";
	$insert[2]="INSERT INTO `notifications` (`id`, `nama`, `level`, `tipe`, `subyek`, `pesan`) VALUES(3, 'penutupan akses', '2', 'info', '[MANTRA INFO] Penutupan Akses Layanan', 'Akses Anda terhadap layanan di bawah ini telah dinonaktifkan<br/><br/>\nID Requester = value1<br/>\nNama layanan = value2<br/>\nNama Fungsi = value3<br/>\nInstansi Penyedia = value4<br/><br/>');";
			
	$tbnames=getTableNames(
		$dbdriver=$dbe['dbdriver'],
		$hostname=$dbe['hostname'],
		$username=$dbe["username"],
		$password=$dbe["password"],
		$dbname=$dbe["database"]
	);

	if(!in_array('notifications',$tbnames)){
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
			$errprefix='initNotification()->create'
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
			$errprefix='initNotification()->insert'
		);		
		if($result!='OK') exit($result);
	}
}

function setNotifications()
{
	global $db;

	$result='';
	$insert=array();
	$update=array();
	$dbe=$db["default"];
	$select="SELECT `id` FROM `notifications`";

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
					$errprefix='setNotifications()->select'
				);
				
	if(is_object($adors)){
		$operations=array('1'=>'insert','2'=>'insert','3'=>'insert');
		
		foreach($adors->getRows() as $row=>$col) {
			if ($col['id']=='1'){
				$update[]="UPDATE `notifications` SET `nama`='tambah pesanan',`level`='0,1',`tipe`='info', `subyek`='[MANTRA INFO] Permohonan Akses Layanan', `pesan`='Permohonan akses ditambahkan oleh requester:<br/><br/>\nID Requester = value1<br/>\nNama Instansi = value2<br/>\nNama Layanan = value3<br/>\nNama Metode = value4<br/><br/>\nSilahkan login pada Aplikasi MANTRA untuk melihat daftar pesanan akses layanan Anda.' WHERE `id`=1 ;";
				$operations[$col['id']]='update';
			}elseif ($col['id']=='2'){
				$update[]="UPDATE `notifications` SET `nama`='persetujuan akses',`level`='2',`tipe`='info', `subyek`='[MANTRA INFO] Persetujuan Akses Layanan' , `pesan`='Permohonan Anda telah disetujui oleh Penyedia Layanan.<br/><br/>\nID Requester = value1<br/>\nNama Layanan = value2<br/>\nNama Fungsi = value3<br/>\nInstansi Penyedia = value4<br/><br/>\nSilahkan login pada Aplikasi MANTRA untuk mengunduh adapter layanan.' WHERE `id`=2 ;";
				$operations[$col['id']]='update';
			}elseif ($col['id']=='3'){
				$update[]="UPDATE `notifications` SET `nama`='penutupan akses',`level`='2',`tipe`='info', `subyek`='[MANTRA INFO] Penutupan Akses Layanan' , `pesan`='Akses Anda terhadap layanan di bawah ini telah dinonaktifkan<br/><br/>\nID Requester = value1<br/>\nNama layanan = value2<br/>\nNama Fungsi = value3<br/>\nInstansi Penyedia = value4<br/><br/>' WHERE `id`=3 ;";		
				$operations[$col['id']]='update';
			}
		}
		
		foreach($operations as $key=>$val) {
			if($val=='insert'){
				if ($key=='1')
					$insert[]="INSERT INTO `notifications` (`id`, `nama`, `level`, `tipe`, `subyek`, `pesan`) VALUES(1, 'tambah pesanan', '0,1', 'info', '[MANTRA INFO] Permohonan Akses Layanan', 'Permohonan akses ditambahkan oleh requester:<br/><br/>\nID Requester = value1<br/>\nNama Instansi = value2<br/>\nNama Layanan = value3<br/>\nNama Metode = value4<br/><br/>\nSilahkan login pada Aplikasi MANTRA untuk melihat daftar pesanan akses layanan Anda.');";
				elseif ($key=='2')
					$insert[]="INSERT INTO `notifications` (`id`, `nama`, `level`, `tipe`, `subyek`, `pesan`) VALUES(2, 'persetujuan akses', '2', 'info', '[MANTRA INFO] Persetujuan Akses Layanan', 'Permohonan Anda telah disetujui oleh Penyedia Layanan.<br/><br/>\nID Requester = value1<br/>\nNama Layanan = value2<br/>\nNama Fungsi = value3<br/>\nInstansi Penyedia = value4<br/><br/>\nSilahkan login pada Aplikasi MANTRA untuk mengunduh adapter layanan.');";
				elseif ($key=='3')
					$insert[]="INSERT INTO `notifications` (`id`, `nama`, `level`, `tipe`, `subyek`, `pesan`) VALUES(3, 'penutupan akses', '2', 'info', '[MANTRA INFO] Penutupan Akses Layanan', 'Akses Anda terhadap layanan di bawah ini telah dinonaktifkan<br/><br/>\nID Requester = value1<br/>\nNama layanan = value2<br/>\nNama Fungsi = value3<br/>\nInstansi Penyedia = value4<br/><br/>');";
					
			}
		}			
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
			$errprefix='setNotifications()->insert'
		);
		if($result!='OK') exit($result);
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
			$errprefix='setNotifications()->update'
		);
		if($result!='OK') exit($result);
	}			
		
	return $result;
}

function getNotificationsByID($id="")
{
	global $db;
	$result=false;

	$dbe=$db["default"];
	$dml="SELECT `level`,`subyek`,`pesan` FROM `notifications` WHERE `id` = ? ";
	$data=array($id);

	$adors=dbExecute(
		$dbms=$dbe['dbdriver'],
		$hostname=$dbe['hostname'],
		$username=$dbe['username'],
		$password=$dbe['password'],
		$database=$dbe['database'],
		$sql=$dml,
		$bindfield=$data,
		$trx=false,
		$debug=$dbe['db_debug'],
		$errprefix='getResourceByID()'
	);
	if(is_object($adors)) $result=$adors->fields;
	
	return $result;
}

function getUserEmail($logname=""){
	global $db;
	$result=false;

	$dbe=$db["default"];
	$dml="SELECT `email` FROM `users`
			WHERE `logname`=? and `activity`='on' ";
	$data=array($logname);

	$adors=dbExecute(
		$dbms=$dbe['dbdriver'],
		$hostname=$dbe['hostname'],
		$username=$dbe['username'],
		$password=$dbe['password'],
		$database=$dbe['database'],
		$sql=$dml,
		$bindfield=$data,
		$trx=false,
		$debug=$dbe['db_debug'],
		$errprefix='getUserEmail'
	);
	
	if(is_object($adors)) $result=$adors->getRows();
	
	return $result;
}

function getUsersEmail($instance="",$methodid="",$serviceid=""){
	global $db;
	$result=false;

	$dbe=$db["default"];
	$dml="SELECT `email` FROM `users`
			WHERE `instanceid`= (select `id` from `instances` where `instance` = ? ) and `role` =
				(select IF(`methodtype`='services', 'publisher', 'provider') as `user_role` from `methods`
					where `id` = ? and `serviceid` = ?) and `activity`='on' ";
	$data=array($instance,$methodid,$serviceid);

	$adors=dbExecute(
		$dbms=$dbe['dbdriver'],
		$hostname=$dbe['hostname'],
		$username=$dbe['username'],
		$password=$dbe['password'],
		$database=$dbe['database'],
		$sql=$dml,
		$bindfield=$data,
		$trx=false,
		$debug=$dbe['db_debug'],
		$errprefix='getUserEmail'
	);
	
	if(is_object($adors)) $result=$adors->getRows();
	
	return $result;
}

function getProviderEmail($instance=""){
	global $db;
	$result=false;

	$dbe=$db["default"];
	$dml="SELECT `email` FROM `instances`
			WHERE `instance`= ?";
	$data=array($instance);

	$adors=dbExecute(
		$dbms=$dbe['dbdriver'],
		$hostname=$dbe['hostname'],
		$username=$dbe['username'],
		$password=$dbe['password'],
		$database=$dbe['database'],
		$sql=$dml,
		$bindfield=$data,
		$trx=false,
		$debug=$dbe['db_debug'],
		$errprefix='getProviderEmail'
	);
	
	if(is_object($adors)) $result=$adors->getRows();
	
	return $result;
}