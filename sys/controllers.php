<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
*/

function init_request_page(){
global $urikeys,$puri,$interfaces;

	if(URL_BASEPATH=="/"){
		$appuri=substr($_SERVER['REQUEST_URI'],1);
	}
	else{
		$appuri=str_replace(URL_BASEPATH, "", $_SERVER['REQUEST_URI']);
	}

	if(!empty($appuri)){ 
		$vuri=explode("/",$appuri,3);
		if(count($vuri)<count($urikeys)) $vuri=array_merge($vuri,array_fill(count($vuri),count($urikeys)-count($vuri),""));
		$puri=array_combine($urikeys,$vuri);
	}

	if(in_array($puri["page"],array("tinjauan","unduh","keterangan","metadata","rincian") )){
		$puri["id"]=$puri["action"];
		$puri["action"]=$puri["page"];
		$puri["page"]="";
	}
	elseif($puri["page"]=="list"){
		$puri["id"]=$puri["action"];
		$puri["action"]=$puri["page"];
		$puri["page"]="";
	}
	if($puri["page"]=="masuk") postlogin();
}

function postlogin(){
global $validLogin,$sess_name;
	$data=array();
	if(!$validLogin){
		if(!session_id()) session_start();
		if((strtoupper($_SERVER['REQUEST_METHOD'])=='POST') 
		&& isset($_POST["f_login"]) 
		&& isset($_POST["f_login"][$sess_name]) 
		&& isset($_SESSION['order'])){
			$postdata=decsay(($_POST["f_login"][$sess_name]),$_SESSION['order']);
			list($data["captcha"],$data["logname"],$data["passkey"])=explode(str_repeat(chr(27),3),$postdata);		
			if(!empty($data) && !logAccept($data)) $_SESSION['message']='Otentikasi Gagal!';
		}
	}
}

function titlebar(){
	ob_start();
	?>
	<div class="titlebar">
	<img src="<?php echo APP_ICON;?>" title="<?php echo APP_NAME;?>"/>
	</div>
	<?php
	return ob_get_clean();
}

function clockbar(){
	ob_start();
	?>
	<div class="timebar"><p id="timer"></p></div>
	<?php
	return ob_get_clean();
}

function logbar(){
global $validLogin;
	ob_start();
	?>
	<div class="logbar">
	<?php if($validLogin){?>
	<strong><?php echo current_logname()."@".$_SERVER['REMOTE_ADDR'];?></strong>&nbsp;(<?php echo current_role();?>.<?php echo current_instance(current_instanceid());?>)
	<?php }?>
	</div>
	<?php
	return ob_get_clean();
}


function headmenu(){
global $urikeys,$puri,$interfaces,$validLogin;
ob_start();
	
	if(!in_array("beranda",$interfaces)) $interfaces['home']="beranda";
	if(!in_array("masuk",$interfaces)) $interfaces['login']="masuk";
	if(!in_array("prakata",$interfaces)) $interfaces['about']="prakata";
	$activepage=array_combine($interfaces,array_fill(0,count($interfaces),""));
	if(isset($puri["page"]) && in_array($puri["page"],$interfaces)){
		if(in_array($puri["page"],array('kodefikasi','metadata','elemen.profil','pemutakhiran'))){
			$activepage[$interfaces["settings"]]="class=\"current_page\""; 
		}
		else{
			$activepage[$puri["page"]]="class=\"current_page\""; 
		}
	}
	else{
		$activepage[$interfaces["home"]]="class=\"current_page\"";
	}
?>
<div class="headmenu">
	<ul>
	<li <?php echo $activepage[$interfaces["home"]];?>><a href="<?php echo URL_BASEPATH;?>"><?php echo ucwords($interfaces["home"]);?></a></li>
	<li <?php echo $activepage[$interfaces["about"]];?>><a href="<?php echo URL_BASEPATH.$interfaces["about"];?>"><?php echo ucwords($interfaces["about"]);?></a></li>
	<?php if($validLogin){?>
		
		<?php if(is_administrator()){?>
		<li <?php echo $activepage[$interfaces["instances"]];?>><a href="<?php echo URL_BASEPATH.$interfaces["instances"];?>"><?php echo ucwords($interfaces["instances"]);?></a></li>
		<?php }?>
		<?php //if(!is_supervisor()){?>
		<li <?php echo $activepage[$interfaces["users"]];?>><a href="<?php echo URL_BASEPATH.$interfaces["users"];?>"><?php echo ucwords($interfaces["users"]);?></a></li>
		<?php //}?>

		<?php if(is_provider() || is_publisher()){?>
		<li <?php echo $activepage[$interfaces["services"]];?>><a href="<?php echo URL_BASEPATH.$interfaces["services"];?>"><?php echo ucwords($interfaces["services"]);?></a></li>
		<li <?php echo $activepage[$interfaces["methods"]];?>><a href="<?php echo URL_BASEPATH.$interfaces["methods"];?>"><?php echo ucwords($interfaces["methods"]);?></a></li>
		<?php }?>

		<?php if(is_provider() || is_publisher() || is_requester()){?>
		<li <?php echo $activepage[$interfaces["orders"]];?>><a href="<?php echo URL_BASEPATH.$interfaces["orders"];?>"><?php echo ucwords($interfaces["orders"]);?></a></li>
		<?php }?>

		<?php if(is_supervisor() || is_administrator() || is_provider() || is_publisher()){?>
		<li <?php echo $activepage[$interfaces["tracks"]];?>><a href="<?php echo URL_BASEPATH.$interfaces["tracks"];?>"><?php echo ucwords($interfaces["tracks"]);?></a></li>
		<?php }?>
		<?php if(is_administrator()){?>
		<li <?php echo $activepage[$interfaces["settings"]];?>>
			<a><?php echo ucwords($interfaces["settings"]);?></a>
			<ul>
			<li><a href="<?php echo URL_BASEPATH.$interfaces["profile"];?>"><?php echo ucwords(str_replace('.',' ',$interfaces["profile"]));?></a></li>
			<!--li><a href="<?php echo URL_BASEPATH.$interfaces["codefication"];?>"><?php echo ucwords($interfaces["codefication"]);?></a></li-->
			<li><a href="<?php echo URL_BASEPATH.$interfaces["notifications"];?>"><?php echo ucwords(str_replace('.',' ',$interfaces["notifications"]));?></a></li>
			</ul>
		</li>
		<?php }?>

		<li <?php echo $activepage[$interfaces["login"]];?>><a href="<?php echo URL_BASEPATH;?>?keluar">Keluar</a></li>
	
	<?php }else{?>
		<li <?php echo $activepage[$interfaces["login"]];?>><a href="<?php echo URL_BASEPATH;?>masuk">Masuk</a></li>
	<?php }?>
	</ul>
</div>
<?php
return ob_get_clean();
}

function container(){
global $urikeys,$puri,$interfaces,$validLogin;
	ob_start();
	$interface="";
	$funcname=array_combine($interfaces,array_keys($interfaces));
	if(is_writeable("files/startup.ini")){
		echo "<div style='padding:4px;color:red'>Untuk alasan keamanan, segera pasang atribut readonly pada file mantra/files/startup.ini dalam Windows OS,<br/>atau perintah atribut readonly file pada Unix/Linux OS: \$sudo chmod 444 mantra/files/startup.ini</div>"; 
	}
	if(isset($puri["page"])) $interface=$puri["page"];
	if(!empty($interface)){
		if(function_exists("page".$funcname[$interface])){
			$result=call_user_func("page".$funcname[$interface], $interface);
		}
		else{
			if(!headers_sent()) header('Location: '.home_url());		
		}
	}
	else{
		pagehome();
	}
	return ob_get_clean();
}

$reqcontroller=glob(APP_CONTROLLER.'*.php');
foreach($reqcontroller as $reqfile){
require_once $reqfile;	 
}

