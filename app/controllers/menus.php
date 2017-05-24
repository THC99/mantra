<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
*/

function tabmenu(){
	global $urikeys,$puri,$interfaces;
	$interface="";
	$captions=array_combine($interfaces,$interfaces);
	if(isset($puri["page"]) && in_array($puri["page"],$interfaces)) $interface=$puri["page"];
	if(empty($interface)) return;
	if(in_array($interface,array("beranda","prakata","masuk"))) return;
	$interfaceuri="".$interface;
	$actionpage="";
	$active=array("info"=>"","tambah"=>"","ubah"=>"","hapus"=>"");
	if(isset($puri["action"]) && !empty($puri["action"])){
		$actionpage=$puri["action"]=="list"?"":$puri["action"];
		$active[$actionpage==""?"info":$actionpage]="active";
	}
	else $active["info"]="active";
	
	if($interface=="akses")
		$canadd=is_requester();
	else
		$canadd=true;
?>
<div class="menu">
	<ul>
		<li class="<?php echo $active["info"];?>"><a href="<?php echo $interfaceuri;?>" title="Tabel Data">Seleksi<?php //echo ucwords($captions[$interface]);?></a></li>
		<?php if(empty($actionpage)){ if($canadd){?>
		<li class="<?php echo $active["tambah"];?>"><a href="<?php echo $interfaceuri;?>/tambah" title="Tambah Data">Tambah</a></li>
		<?php } }else{?>
		<li class="<?php echo $active[$actionpage];?>"><span><?php echo ucfirst($actionpage);?></span></li>
		<?php }?>
	</ul>
</div>
<?php
}

function panelmenu(){
	global $urikeys,$puri,$interfaces,$validLogin;

	$interface="";
	if(!$validLogin) return;
	if(isset($puri["page"]) && !in_array($puri["page"],array("","home")) )return;
	$interfaceuri="";
	$actionpage="";
	$active=array("info"=>"","tinjauan"=>"");
	if(isset($puri["action"]) && !empty($puri["action"])){
		$actionpage=$puri["action"]=="list"?"":$puri["action"];
		$active[$actionpage==""?"info":$actionpage]="active";
	}
	else
		$active["info"]="active";

?>
<div class="menu">
	<ul>
		<li class="<?php echo $active["info"];?>"><a href="<?php echo home_url();?>" title="Layanan Web">Seleksi</a></li>
		<?php if(!empty($actionpage)){?>
		<li class="<?php echo $active[$actionpage];?>"><span><?php echo ucfirst($actionpage);?></span></li>
		<?php }?>
	</ul>
</div>
<?php
}


function historymenu(){
	global $urikeys,$puri,$interfaces;
	$interface="";
	if(isset($puri["page"]) && in_array($puri["page"],$interfaces)) $interface=$puri["page"];
	if(empty($interface)) return;
	$interfaceuri="".$interface;
	$actionpage="";
	$active=array("info"=>"","statistik"=>"");
	if(isset($puri["action"]) && !empty($puri["action"])){
		$actionpage=$puri["action"]=="list"?"":$puri["action"];
		$active[$actionpage==""?"info":$actionpage]="active";
	}
	else
		$active["info"]="active";
	
?>
<div class="menu">
	<ul>
		<li class="<?php echo $active["info"];?>"><a href="<?php echo $interfaceuri;?>" title="Riwayat Penggunaan">Riwayat Proses</a></li>
		<?php if(empty($actionpage) || $actionpage=="statistik"){?>
		<li class="<?php echo $active["statistik"];?>"><a href="<?php echo $interfaceuri;?>/statistik" title="Statistik Penggunaan">Analisa Operasi</a></li>
		<?php }else{?>
		<li class="<?php echo $active[$actionpage];?>"><span><?php echo ucfirst($actionpage);?></span></li>
		<?php }?>
	</ul>
</div>
<?php
}

function pubtab(){
	global $urikeys,$puri,$interfaces;

	$interface="";
	$interfaceuri="";
	$actionpage="";
	$active=array("info"=>"","keterangan"=>"");
	if(isset($puri["action"]) && !empty($puri["action"])){
		$actionpage=$puri["action"]=="list"?"":$puri["action"];
		$active[$actionpage==""?"info":$actionpage]="active";
	}
	else
		$active["info"]="active";

?>
<div class="menu">
	<ul>
		<li class="<?php echo $active["info"];?>"><a href="<?php echo home_url();?>" title="Antarmuka Layanan Akses Data">Seleksi</a></li>
		<?php if(!empty($actionpage)){?>
		<li class="<?php echo $active[$actionpage];?>"><span><?php echo ucfirst($actionpage);?></span></li>
		<?php }?>
	</ul>
</div>
<?php
}
