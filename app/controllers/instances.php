<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
ver: 1.99y
*/


function resetpageinstances(){
	return   array("instance"=>"",
				   "organization"=>"",
				   "webportal"=>"",
				   "email"=>"",
				   "descript"=>"");
}

function pageinstances($interface){
	global $db,$urikeys,$puri,$interfaces,$validLogin;
	if(!$validLogin){
		header('Location: '.home_url().'masuk');
		return;
	}
	if(!is_administrator()) return;
	?>
	<p style='padding:0 4px;font-size:28px;font-weight:bold;'>Instansi Pengelola</p>
	<?php
	tabmenu();
	$interfaceuri=home_url().$interface."/";
	$hasFinished=false;
	$currInstance="";
	if(isset($puri["action"]) && $puri["action"]=="list") $puri["action"]="";
	if(isset($puri["action"]) && !empty($puri["action"])){
		$actionpage=$puri["action"];
		$message="";
		$vdialog=resetpageinstances();//init scope

		if((strtoupper($_SERVER['REQUEST_METHOD'])=='POST')
		&&(isset($_POST[session_name()])) 
		&&($_SERVER['HTTP_USER_AGENT']==decsay($_POST[session_name()],$_SESSION['idform']))
		&&(isset($_POST["f_dialog"]))){
			$vdialog=array_merge($vdialog,$_POST["f_dialog"]);
			if(isset($vdialog["submit"])){ // tombol simpan
				switch ($actionpage){
				case "tambah":
					$ret=addInstance($vdialog);
					if($ret=='OK'){
						addTrack(array("trackid"=>$vdialog['instance'],"trackname"=>$interface,"trackstatus"=>"ADD","tracknote"=>"","trackdata"=>""));
						$message="Penambahan data '".$vdialog['instance']."' berhasil disimpan.";
						$hasFinished=true;
					}
					else $message="Penambahan data '".$vdialog['instance']."' gagal disimpan.<br/>".$ret;
					break;
				case "ubah":
					$vinstance=getInstanceByID($vdialog["id"]);
					$ret=updateInstanceByID($vdialog);
					if($ret=='OK'){
						addTrack(array("trackid"=>$vinstance['instance'],"trackname"=>$interface,"trackstatus"=>"EDIT","tracknote"=>"","trackdata"=>""));
						$message="Pemutakhiran data '".$vdialog['instance']."' berhasil disimpan.";
						$hasFinished=true;
					}
					else $message="Pemutakhiran data '".$vdialog['instance']."' gagal disimpan.<br/>".$ret;
					break;
				case "hapus":
					$vinstance=getInstanceByID($vdialog["id"]);
					$ret=deleteInstanceByID($vdialog["id"]);
					if($ret=='OK'){
						addTrack(array("trackid"=>$vinstance['instance'],"trackname"=>$interface,"trackstatus"=>"DELETE","tracknote"=>"","trackdata"=>""));
						$message="Data '".$vdialog['instance']."' berhasil dihapus.";
						$vdialog=resetpageinstances();
						$hasFinished=true;
					}
					else $message="Data '".$vdialog['instance']."' gagal dihapus.<br/>".$ret;
				}
			}
		}

		if(isset($puri["id"]) && !empty($puri["id"])){
			$vinstance=getInstanceByID($puri["id"]);
			if(is_array($vinstance)) $vdialog=array_merge($vdialog,$vinstance);
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
		?>

		<br/>
		<form name="f_dialog" method="post" action="" accept-charset="UTF-8">
		<div class="dialog">
			<div>ID Instansi:*</div>
			<div><input type="text" name="f_dialog[instance]" autofocus="autofocus" style="width:40em;" value="<?php echo esc_text($vdialog["instance"]);?>" onkeypress="return letterNumber(event,5)" onchange="lowerCase(this)"/></div>
			<div>Nama Instansi/Organisasi:*</div>
			<div><input type="text" name="f_dialog[organization]" style="width:40em;" value="<?php echo esc_text($vdialog["organization"]);?>"/></div>
			<div>Alamat Website/Portal Instansi (URL):</div>
			<div><input type="text" name="f_dialog[webportal]" style="width:40em;" value="<?php echo esc_text($vdialog["webportal"]);?>"  onkeypress="return letterNumber(event,9)" /></div>
			<div>e-Mail Instansi:</div>
			<div><input type="text" name="f_dialog[email]" style="width:40em;" value="<?php echo esc_text($vdialog["email"]);?>" onkeypress="return letterNumber(event,4)" onchange="lowerCase(this)"/></div>
			<div>Keterangan:</div>
			<div><textarea name="f_dialog[descript]" style="width:40em;" rows="10"><?php echo esc_text($vdialog["descript"]);?></textarea></div>
			*: Wajib diisi
			<hr/>
			<div><input type="submit" name="f_dialog[submit]" value="Simpan"/></div>
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
		<form name="f_dialog" method="post" action="" accept-charset="UTF-8">
		<div class="dialog">
			<div>ID Instansi:</div>
			<div><input type="text" name="f_dialog[instance]" style="width:40em;" readonly="readonly" value="<?php echo esc_text($vdialog["instance"]);?>"/></div>
			<div>Nama Instansi/Organisasi:</div>
			<div><input type="text" name="f_dialog[organization]" style="width:40em;" readonly="readonly" value="<?php echo esc_text($vdialog["organization"]);?>"/></div>
			<div>Alamat Website/Portal Instansi (URL):</div>
			<div><input type="text" name="f_dialog[webportal]" style="width:40em;" readonly="readonly" value="<?php echo esc_text($vdialog["webportal"]);?>"/></div>
			<div>e-Mail Instansi:</div>
			<div><input type="text" name="f_dialog[email]" style="width:40em;" readonly="readonly" value="<?php echo esc_text($vdialog["email"]);?>"/></div>
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
		$grid=new grid_interface;
		$grid->urikeys=$urikeys;
		$grid->puri=$puri;
		$grid->interfaces=$interfaces;
		$grid->baseurl=$interfaceuri;
		$grid->setFilter(array("instance"=>array("label"=>"ID Instansi","size"=>30,"attrs"=>"autofocus=\"autofocus\"")));
		$grid->rows=getInstanceRows($grid->query["instance"]);
		$grid->pager=getPageList($grid->rows,$grid->pagenum,10,1);
		$grid->listpager=getPageList($grid->rows,$grid->pagenum,10,5);
		$grid->pdf["data"]="instances/instance=".($currInstance==""?$grid->query["instance"]:$currInstance);
		$grid->records=getInstances($grid->query["instance"],$grid->pager["perpage"],$grid->pager["startrow"]);
		$grid->colnames=array("instance"=>array("label"=>"ID INSTANSI"),
							  "organization"=>array("label"=>"NAMA INSTANSI"),
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

