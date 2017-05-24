<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
ver: 1.99y
*/

function readSMTPConfig(){
	$startupfile="files/startup.ini";
	if( is_readable($startupfile) ){
        	$data=parse_ini_file($startupfile,true);
        	if(isset($data["smtp_config"])){
                	$smtpConfig=base64_decode($data["smtp_config"]);
	        }else{
	        	return 0;
	        }
	}
	return $smtpConfig;
}

function writeSMTPConfig($input){
	$result="";
	$data=array();
	
	if ( !isset($input["smtp"])  || !isset($input["port"])  || !isset($input["email"]) || !isset($input["password"]) ) return 'Konfigurasi tidak valid';
	if ( empty($input["smtp"]) || empty($input["port"]) || empty($input["email"]) || empty($input["password"]) ) return 'Data tidak boleh kosong';
	
	
	$startupfile="files/startup.ini";
	
	if ($input["isConfig"]==0) //belum pernah dibuat configurasi sebelumnya
	{	
		$data=array(
		"smtp_config"   =>array(
			"smtp"	=>$input["smtp"],
			"port"	=>$input["port"],
			"email"	=>$input["email"],
			"password"	=>base64_encode($input["password"]) 				
		)
		);
		
		if (file_put_contents($startupfile,arr2ini($data),FILE_APPEND)) $result="OK";
		else  $result="Inisiasi gagal! Solusi: $sudo chmod 755 mantra/files mantra/tmp && sudo chown www-data:www-data mantra/files mantra/tmp ";
		
	}elseif ($input["isConfig"]==1) //pernah dibuat configurasi sebelumnya
	{	
		$data=parse_ini_file($startupfile,true);
		
		$data["smtp_config"]["smtp"]=$input["smtp"];
		$data["smtp_config"]["port"]=$input["port"];
		$data["smtp_config"]["email"]=$input["email"];
		$data["smtp_config"]["password"]=base64_encode($input["password"]);
		
		if (file_put_contents($startupfile,arr2ini($data),LOCK_EX)) $result="OK";
		else $result="Inisiasi gagal! Solusi: $sudo chmod 755 mantra/files mantra/tmp && sudo chown www-data:www-data mantra/files mantra/tmp ";	
	}
	
	return $result;
	
}

function resetpagenotifications(){
	return array("smtp"=>"",
				   "port"=>"",
				   "email"=>"",
				   "password"=>"",
				   "isConfig"=>0);
}

function pagenotifications($interface){
	global $db,$urikeys,$puri,$interfaces,$validLogin;
	if(!$validLogin){
		header('Location: '.home_url().'masuk');
		return;
	}
	if(!is_administrator()) return;
	$interfaceuri=home_url().$interface."/";
	$message="";
	$vdialog=resetpagenotifications();//init scope
	$hasFinished=false;


	if((strtoupper($_SERVER['REQUEST_METHOD'])=='POST')
	&&(isset($_POST["f_dialog"]))){
		$vdialog=array_merge($vdialog,$_POST["f_dialog"]);
		if(isset($vdialog["submit"])){ // tombol simpan
			$ret=writeSMTPConfig($vdialog);
			if($ret=='OK'){
				addTrack(array("trackid"=>"Pengaturan SMTP", "trackname"=>$interface,"trackstatus"=>"EDIT","tracknote"=>"","trackdata"=>""));
				$message="Pemutakhiran data berhasil disimpan.";
				$hasFinished=true;
			}
			else {
				$message="Pemutakhiran data gagal disimpan.<br/>".$ret;
			}	
		}
	}
	
	if(!empty($message)){
			$_SESSION['message']=$message;
			if($hasFinished){
				if(!headers_sent()){ 
					header('location:'.home_url());
					exit;
				}
			} 
	}
	
	$config=readSMTPConfig();

	if(is_array($config)){
		$vdialog=array_merge($vdialog,$config);
		//$vdialog=esc_textarea_deep($vdialog);	
		$vdialog["isConfig"]=1;
	}
	?>
		<p style='padding:0 4px;font-size:28px;font-weight:bold;'>Konfigurasi SMTP Server</p>
		<hr/>
		<form name="f_dialog" method="post" action="" accept-charset="UTF-8">
		<div class="dialog">
			<div>SMTP Server:</div>
			<div><input type="text" name="f_dialog[smtp]" style="width:40em;" value="<?php echo esc_text($vdialog["smtp"]);?>" autofocus="autofocus"/></div>
			<div>Port SMTP:</div>
			<div><input type="text" name="f_dialog[port]"  style="width:40em;" value="<?php echo esc_text($vdialog["port"]);?>" onkeypress="return letterNumber(event,11)"/></div>
			<div>e-Mail:</div>
			<div><input type="text" name="f_dialog[email]" style="width:40em;" value="<?php echo esc_text($vdialog["email"]);?>" onkeypress="return letterNumber(event,4)" onchange="lowerCase(this)"/></div>
			<div>Kata Kunci (Password):*</div>
			<div><input type="password" name="f_dialog[password]" style="width:40em;" placeholder="********************" value="<?php echo esc_text($vdialog["password"]);?>" /></div>
			<input type="hidden" name="f_dialog[isConfig]" value="<?php echo esc_text($vdialog["isConfig"]);?>"/>
			<div><input type="submit" name="f_dialog[submit]" value="Simpan"/></div>
		</div>
		</form>
	<?php
	

}

