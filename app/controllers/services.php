<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
ver: 1.99y
*/

function resetpageservices(){
	return   array("servicename"=>"",
		           "servicetype"=>"",
				   "instanceid"=>"",
				   "descript"=>"",
				   "role"=>"",
				   "activity"=>"");
}

function pageservices($interface){	
	global $db,$urikeys,$puri,$interfaces,$validLogin;
	if(!$validLogin){
		header('Location: '.home_url().'masuk');
		return;
	}
	if(!is_provider() && !is_publisher()) return;
	?>
	<p style='padding:0 4px;font-size:28px;font-weight:bold;'>Direktori Operasi</p>
	<?php

	tabmenu();
	$interfaceuri=home_url().$interface."/";
	$hasFinished=false;
	$currInstance="";
	$currRole="";
	if(is_provider() || is_publisher()){
	 	$currInstance=current_instance(current_instanceid());
		$currRole=current_role();
	}
	if(isset($puri["action"]) && $puri["action"]=="list") $puri["action"]="";
	if(isset($puri["action"]) && !empty($puri["action"])){
		$actionpage=$puri["action"];
		$message="";
		$vdialog=resetpageservices();//init scope
		$vdialog['instanceid']=current_instanceid();
		$vdialog['servicetype']=$currRole;
		
		if((strtoupper($_SERVER['REQUEST_METHOD'])=='POST')
		&&(isset($_POST[session_name()])) 
		&&($_SERVER['HTTP_USER_AGENT']==decsay($_POST[session_name()],$_SESSION['idform']))
		&&(isset($_POST["f_dialog"]))){
			$vdialog=array_merge($vdialog,$_POST["f_dialog"]);
			if(isset($vdialog["submit"])){ // tombol simpan
				switch ($actionpage){
				case "tambah":
					$ret=addService($vdialog);
					if($ret=='OK'){
						addTrack(array("trackid"=>$vdialog['servicename'],"trackname"=>$interface,"trackstatus"=>"ADD","tracknote"=>"","trackdata"=>""));
						$message="Penambahan data '".$vdialog['servicename']."' berhasil disimpan.";
						$hasFinished=true;
					}
					else
						$message="Penambahan data '".$vdialog['servicename']."' gagal disimpan.<br/>".$ret;
					break;
				case "ubah":
					$vservices=getServiceByID($vdialog["id"]);
					$ret=updateService($vdialog);
					if($ret=='OK'){
						addTrack(array("trackid"=>$vservices['servicename'],"trackname"=>$interface,"trackstatus"=>"EDIT","tracknote"=>"","trackdata"=>""));
						$message="Pemutakhiran data '".$vdialog['servicename']."' berhasil disimpan.";
						$hasFinished=true;
					}
					else
						$message="Pemutakhiran data '".$vdialog['servicename']."' gagal disimpan.<br/>".$ret;
					break;
				case "hapus":
					$vservices=getServiceByID($vdialog["id"]);
					$ret=deleteServiceByID($vdialog["id"]);
					if($ret=='OK'){
						addTrack(array("trackid"=>$vservices['servicename'],"trackname"=>$interface,"trackstatus"=>"DELETE","tracknote"=>"","trackdata"=>""));
						$message="Data '".$vdialog['servicename']."' berhasil dihapus.";
						$vdialog=resetpageservices();
						$hasFinished=true;
					}
					else
						$message="Data '".$vdialog['servicename']."' gagal dihapus.<br/>".$ret;
				}
			}
		}

		if(isset($puri["id"]) && !empty($puri["id"])){
			$vservices=getServiceByID($puri["id"]);
			if(is_array($vservices))
				$vdialog=array_merge($vdialog,$vservices);
		}

		if(!empty($db['default']['messages'])) $message=$db['default']['messages'];
		if(!empty($message)){
			$_SESSION['message']=$message;
			if($hasFinished){
				if(!headers_sent()){ 
					header('location:'.$interfaceuri);
					exit;
				}
			}			 
		}
		//display scope

		if(in_array($actionpage,array("tambah","ubah"))){
			if($hasFinished) return;
			$_SESSION['idform']=dechex(time()).strrev(time());
			$code=encsay($_SERVER['HTTP_USER_AGENT'],$_SESSION['idform']);

			$token=array(
				'name'=>session_name(),
				'code'=>$code
			)
			//
		?>
		<br/>
		<form name="f_dialog" method="post" accept-charset="UTF-8" action="">
		<div class="dialog">
	
			<div>Direktori Operasi:*</div>
			<div><input type="text" name="f_dialog[servicename]" autofocus="autofocus" style="width:40em;" value="<?php echo esc_text($vdialog["servicename"]);?>" onkeypress="return letterNumber(event,5)" onchange="lowerCase(this)"/></div>
			<input type="hidden" name="f_dialog[servicetype]" value="<?php echo esc_text($vdialog["servicetype"]);?>"/>
			<div>Keterangan:</div>
			<div><textarea name="f_dialog[descript]" style="width:40em;" rows="10"><?php echo esc_text($vdialog["descript"]);?></textarea></div>
			<hr/>
			<div><input type="submit" name="f_dialog[submit]" value="Simpan" /></div>
			<input name="<?php echo $token['name'];?>" type="hidden" value="<?php echo esc_text($token['code']);?>"/>
			<?php if($actionpage=="ubah"){?>
			<input type="hidden" name="f_dialog[id]" value="<?php echo esc_text($vdialog["id"]);?>"/>
			<?php }?>
		</div>
		</form>
		<?php
		}

		if($actionpage=="hapus"){
			if($hasFinished) return;
			$_SESSION['idform']=dechex(time()).strrev(time());
			$code=encsay($_SERVER['HTTP_USER_AGENT'],$_SESSION['idform']);

			$token=array(
				'name'=>session_name(),
				'code'=>$code
			)
		?>
		<br/>
		<form name="f_dialog" method="post" accept-charset="UTF-8" action="">
		<div class="dialog">
			<div>Direktori Operasi:</div>
			<div><input type="text" name="f_dialog[servicename]" autofocus="autofocus" style="width:40em;" readonly="readonly" value="<?php echo esc_text($vdialog["servicename"]);?>"/></div>
			<div>Keterangan:</div>
			<div><textarea name="f_dialog[descript]" style="width:40em;" rows="10" readonly="readonly" ><?php echo esc_text($vdialog["descript"]);?></textarea></div>
			<hr/>
			<div><input type="submit" name="f_dialog[submit]" value="Hapus"/></div>
			<input name="<?php echo $token['name'];?>" type="hidden" value="<?php echo esc_text($token['code']);?>"/>
			<input type="hidden" name="f_dialog[id]" value="<?php echo esc_text($vdialog["id"]);?>"/>
		</div>
		</form>
		<?php
		}
	}
	else{
		$filters=array();
		if($currInstance=="") 
			$filters["instance"]=array("label"=>"ID Penyedia","size"=>30);
		$filters["servicename"]=array("label"=>"Direktori Operasi","size"=>30,"attrs"=>"autofocus=\"autofocus\"");

		$grid=new grid_interface;
		$grid->urikeys=$urikeys;
		$grid->puri=$puri;
		$grid->interfaces=$interfaces;
		$grid->baseurl=$interfaceuri;
		$grid->setFilter($filters);
		$grid->rows= getServiceRows($currInstance==""?$grid->query["instance"]:$currInstance,$currRole,$grid->query["servicename"]);
		$grid->pager=getPageList($grid->rows,$grid->pagenum,10,1);
		$grid->listpager=getPageList($grid->rows,$grid->pagenum,10,5);
		$grid->pdf["data"]="services/instance=".($currInstance==""?$grid->query["instance"]:$currInstance)."&servicetype=".$currRole."&servicename=".$grid->query["servicename"];
		$grid->records=getServices($currInstance==""?$grid->query["instance"]:$currInstance,$currRole,$grid->query["servicename"],$grid->pager["perpage"],$grid->pager["startrow"]);
		$grid->colnames=array("servicename"=>array("label"=>"DIREKTORI"),
							  "registered"=>array("label"=>"TGL.DAFTAR"),
							  "updated"=>array("label"=>"TGL.UBAH"));
		$grid->operations=array(array("url"=>$interfaceuri."ubah/:id","title"=>"Ubah Data","icon"=>"ico/edit.png"),
								array("url"=>$interfaceuri."hapus/:id","title"=>"Hapus Data","icon"=>"ico/drop.png"));
		if($grid->display()==false){
		?>
		<div class="message"><b>Data belum ada</b></div>
		<?php
		}
	}
	echo "<br/>";

}



