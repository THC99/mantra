<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
ver: 1.99y
*/


function resetpageprofile(){
	return   array("groupcode"=>"appsys",
				   "groupname"=>"Application System",
				   "instanceDisplayName"=>"Nama Pengelola (Provinsi/Kota/Kabupaten/Instansi)",
				   "instance"=>"",
				   "iconDisplayName"=>"Icon Aplikasi",
				   "icon"=>"",
				   "imageDisplayName"=>"Logo Pengelola",
				   "image"=>"",
				   "code"=>"",
				   "name"=>"",
				   "seq"=>"",
				   "value"=>"",
				   "parentcode"=>"",
				   "descript"=>"",
				   "registered"=>"");
}

function pageprofile($interface){
	global $db,$urikeys,$puri,$interfaces,$validLogin;
	if(!$validLogin){
		header('Location: '.home_url().'masuk');
		return;
	}
	if(!is_administrator()) return;
	$interfaceuri=home_url().$interface."/";
	$message="";
	$vdialog=resetpageprofile();//init scope

	if(isset($_POST["f_dialog"])){
		$vdialog=array_merge($vdialog,$_POST["f_dialog"]);

		if(isset($vdialog["uploadicon"])){
			$files=$_FILES;
			$datafiles=array();
			$datafiles["name"]=$files["icon_file"]["name"];
			$datafiles["type"]=$files["icon_file"]["type"];
			$datafiles["tmp_name"]=$files["icon_file"]["tmp_name"];
			$datafiles["error"]=$files["icon_file"]["error"];
			$datafiles["size"]=$files["icon_file"]["size"];
			$vdialog["icon"]=do_upload_file($datafiles);
		}
		if(isset($vdialog["uploadimage"])){
			$files=$_FILES;
			$datafiles=array();
			$datafiles["name"]=$files["image_file"]["name"];
			$datafiles["type"]=$files["image_file"]["type"];
			$datafiles["tmp_name"]=$files["image_file"]["tmp_name"];
			$datafiles["error"]=$files["image_file"]["error"];
			$datafiles["size"]=$files["image_file"]["size"];
			$vdialog["image"]=do_upload_file($datafiles);
		}

		if(isset($vdialog["submit"])){ // tombol simpan
			
			switch ($vdialog["submit"]){
			case "Simpan":
				$ret=0;
				$vdata=$vdialog;
				$vdata["code"]="instance";
				$vdata["name"]=$vdata["instanceDisplayName"];
				$vdata["seq"]=0;
				$vdata["value"]=$vdata["instance"];
				$ret+=saveCode($vdata);
				
				$vdata["code"]="icon";
				$vdata["name"]=$vdata["iconDisplayName"];
				$vdata["seq"]=1;
				$vdata["value"]=$vdata["icon"];
				$ret+=saveCode($vdata);
				
				$vdata["code"]="image";
				$vdata["name"]=$vdata["imageDisplayName"];
				$vdata["seq"]=2;
				$vdata["value"]=$vdata["image"];
				$ret+=saveCode($vdata);
				
				if($ret==0)
					$message="Penyimpanan data gagal disimpan.";
				else
					$message="Penyimpanan data berhasil disimpan.";
				break;
			case "Hapus":
				if(deleteCodeByGroupCode($vdialog["groupcode"])){
					addTrack(array("trackid"=>$vdialog["groupcode"],"trackname"=>$interface,"trackstatus"=>"DELETE","tracknote"=>"","trackdata"=>""));
					$message="Data berhasil dihapus.";
					$vdialog=resetpageprofile();
				}
				else
					$message="Data gagal dihapus.";
			}

		}
	}

	if(count($_FILES)==0){
		$vappsys=getCodeByGroupCode($vdialog["groupcode"]);
		if(count($vappsys)>0){
			foreach($vappsys as $rows){
				if($rows["code"]=="instance"){
					$vdialog["instanceDisplayName"]=$rows["name"];
					$vdialog["instance"]=$rows["value"];
				}
				if($rows["code"]=="icon"){
					$vdialog["iconDisplayName"]=$rows["name"];
					$vdialog["icon"]=$rows["value"];
				}
				if($rows["code"]=="image"){
					$vdialog["imageDisplayName"]=$rows["name"];
					$vdialog["image"]=$rows["value"];
				}
				$vdialog["registered"]=$rows["registered"];
			}
		}
	}


	if(!empty($db['default']['messages'])) $message=$db['default']['messages'];
	if(!empty($message)){
		$_SESSION['message']=$message;
	}

	
	$filecapacity=getcapacity();
	?>
		<p style='padding:0 4px;font-size:28px;font-weight:bold;'>Elemen Profil Laporan</p>
		<hr/>
		<form name="f_dialog" method="post" action="" accept-charset="UTF-8" enctype="multipart/form-data">
		<div class="dialog">
			<div><?php echo $vdialog["instanceDisplayName"];?>:*</div>
			<div><input type="text" name="f_dialog[instance]" autofocus="autofocus" style="width:40em;" value="<?php echo esc_text($vdialog["instance"]);?>"/></div>
			<div><?php echo $vdialog["iconDisplayName"];?> (Max.&nbsp;<?php echo byte2size($filecapacity,"M")?>&nbsp;MB):</div>
			<div>
				<input type="file" name="icon_file" style="width:450px;"/>
				<input type="submit" name="f_dialog[uploadicon]" value="Unggah"/>
			</div>
			<div><input type="text" name="f_dialog[icon]" style="width:40em;" readonly="readonly" value="<?php echo esc_text($vdialog["icon"]);?>"/></div>
			
			<div><?php echo $vdialog["imageDisplayName"];?> (Max.&nbsp;<?php echo byte2size($filecapacity,"M")?>&nbsp;MB):</div>
			<div>
				<input type="file" name="image_file" style="width:450px;"/>
				<input type="submit" name="f_dialog[uploadimage]" value="Unggah"/>
			</div>
			<div><input type="text" name="f_dialog[image]" style="width:40em;" readonly="readonly" value="<?php echo esc_text($vdialog["image"]);?>"/></div>

			<hr/>
			<div>
				<input type="submit" name="f_dialog[submit]" value="Simpan"/>
				<input type="submit" name="f_dialog[submit]" value="Hapus"/>
			</div>
			<input type="hidden" name="f_dialog[groupcode]" value="<?php echo esc_text($vdialog["groupcode"]);?>"/>
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo esc_text($filecapacity);?>" />
			<input type="hidden" name="f_dialog[registered]" value="<?php echo esc_text($vdialog["registered"]);?>"/>
		</div>
		</form>
		<script type="text/javascript">
			document.forms["f_dialog"]["f_dialog[instance]"].focus();
		</script>
		<br/>
	<?php

}

