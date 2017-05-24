<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
ver: 1.99y
*/


function resetpageorders(){
	return   array("id"=>"",
				   "userlog"=>"",
				   "userinstance"=>"",
				   "userorg"=>"",
				   "accesskey"=>"",
				   "orderstatus"=>"",
				   "oldorderstatus"=>"",
				   "methodid"=>"",
				   "instance"=>"",
				   "organization"=>"",
				   "servicename"=>"",
				   "methodname"=>"",
				   "methodtype"=>"",
				   "descript"=>"");
}

function pageorders($interface){
	global $db,$urikeys,$puri,$interfaces,$validLogin;
	if(!$validLogin){
		header('Location: '.home_url().'masuk');
		return;
	}
	if(!is_provider() && !is_publisher() && !is_requester()) return;
	?>
	<p style='padding:0 4px;font-size:28px;font-weight:bold;'>Permintaan Akses Operasi</p>
	<?php

	tabmenu();
	$interfaceuri=home_url().$interface."/";
	$hasFinished=false;
	$currLogname=current_logname();
	$currRole=current_role();
	$currInstance=current_instance(current_instanceid());

	if(isset($puri["action"]) && $puri["action"]=="list") $puri["action"]="";
	if(isset($puri["action"]) && !empty($puri["action"])){
		$actionpage=$puri["action"];
		$message="";
		$vdialog=resetpageorders();//init scope

		if((strtoupper($_SERVER['REQUEST_METHOD'])=='POST')
		&&(isset($_POST[session_name()])) 
		&&($_SERVER['HTTP_USER_AGENT']==decsay($_POST[session_name()],$_SESSION['idform']))
		&&(isset($_POST["f_dialog"]))){
			$vdialog=array_merge($vdialog,$_POST["f_dialog"]);
			if(isset($vdialog["submit"])){ // tombol simpan
				switch ($actionpage){
				case "tambah":
					$vdialog["accesskey"]=getToken(10);
					$ret=addOrder($vdialog);
					$input=getMethodbyID($vdialog["methodid"]);
					$input['userorg']=$vdialog["userorg"];
					$input['username']=$currLogname;
					if($ret=='OK'){
						addTrack(array("trackid"=>$vdialog['accesskey'],"trackname"=>$interface,"trackstatus"=>"ADD","tracknote"=>"","trackdata"=>""));
						$message="Penambahan data '".$vdialog['accesskey']."' berhasil disimpan.";
						$message=$message."<br/>".sendNotifications($input,1);
						$hasFinished=true;
					}
					else
						$message="Penambahan data '".$vdialog['accesskey']."' gagal disimpan.<br/>".$ret;
					break;
				case "ubah":
					$ret=updateOrder($vdialog);
					if($ret=='OK'){
						addTrack(array("trackid"=>$vdialog['accesskey'],"trackname"=>$interface,"trackstatus"=>"EDIT","tracknote"=>"","trackdata"=>""));
						$message="Pemutakhiran data '".$vdialog['accesskey']."' berhasil disimpan.";
						if((is_provider() || is_publisher()) && $vdialog["orderstatus"]!==$vdialog["oldorderstatus"] ){
							if ($vdialog['orderstatus']=="on") $message=$message."<br/>".sendNotifications($vdialog,2);
							if ($vdialog['orderstatus']=="off") $message=$message."<br/>".sendNotifications($vdialog,3);
						}
						$hasFinished=true;
					}
					else
						$message="Pemutakhiran data '".$vdialog['accesskey']."' gagal disimpan.<br/>".$ret;
					break;
				case "hapus":
					$ret=deleteOrderByID($vdialog["id"]);
					if($ret=='OK'){
						addTrack(array("trackid"=>$vdialog['accesskey'],"trackname"=>$interface,"trackstatus"=>"DELETE","tracknote"=>"","trackdata"=>""));
						$message="Data '".$vdialog['accesskey']."' berhasil dihapus.";
						$vdialog=resetpageorders();
						$hasFinished=true;
					}
					else
						$message="Data '".$vdialog['accesskey']."' gagal dihapus.<br/>".$ret;
				}
			}
		}

		if(isset($puri["id"]) && !empty($puri["id"])){
			$vorders=getOrderByID($puri["id"]);
			if(is_array($vorders)){
				$vdialog=array_merge($vdialog,$vorders);
			}
		}
		else{
			$vdialog["userlog"]=$currLogname;			
			$vdialog["userorg"]=current_organization(current_instanceid());
		}
		
		if(isset($vdialog["retoken"])){ // tombol retoken
			if($actionpage=="ubah"){
				$vdialog["accesskey"]=getToken(10);
			}
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
		$methods=$actionpage=="tambah"?getMethods('','','',0):getMethodByID($vdialog["instance"],$vdialog["servicename"],$vdialog["methodname"],0);

		if(in_array($actionpage,array("tambah","ubah"))){
			if($hasFinished) return;
			if($actionpage=="tambah" && !is_requester()){
				echo "<br/>&nbsp;&nbsp;Restricted for Requester only.";
				return;
			}

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
			<div>ID Pelaksana:</div>
			<div><input type="text" name="f_dialog[userlog]" style="width:40em;" readonly="readonly" value="<?php echo esc_text($vdialog["userlog"]);?>"/></div>
			<div>Instansi Pengguna:</div>
			<div><input type="text" name="f_dialog[userorg]" style="width:40em;background:#eee;" disabled="disabled" value="<?php echo esc_text($vdialog["userorg"]);?>"/></div>
			<div>Akses Operasi:*</div>			
			<div>
		
			<?php if($actionpage=="tambah"){?>

			<select name="f_dialog[methodid]" size="10" autofocus="autofocus" style="width:40em;">
				<?php foreach($methods as $no=>$method){?>
				<option <?php echo ($vdialog["methodid"]==$method["id"])?"selected=\"selected\"":""?> value="<?php echo esc_text($method["id"]);?>" ><?php echo esc_text($method["instance"].":".$method["servicename"].".".$method["methodname"]);?></option>
				<?php }?>
			</select>
			<input type="hidden" name="f_dialog[userorg]" value="<?php echo esc_text($vdialog["userorg"]);?>"/>
			<?php }else{ ?>
			
			<div><input type="text" name="f_dialog[methodset]" style="width:40em;" disabled="disabled" value="<?php echo esc_text($vdialog["instance"].":".$vdialog["servicename"].".".$vdialog["methodname"]);?>"/></div>
			<input type="hidden" name="f_dialog[methodid]" value="<?php echo esc_text($vdialog["methodid"]);?>"/>
			<input type="hidden" name="f_dialog[methodname]" value="<?php echo esc_text($vdialog["methodname"]);?>"/>
			<input type="hidden" name="f_dialog[servicename]" value="<?php echo esc_text($vdialog["servicename"]);?>"/>
			<input type="hidden" name="f_dialog[organization]" value="<?php echo esc_text($vdialog["organization"]);?>"/>			
			<?php }?>
		
			</div>

			<div>Keterangan:</div>
			<div><textarea name="f_dialog[descript]" readonly="readonly" style="width:40em;" rows="10"><?php echo esc_text($vdialog["descript"]);?></textarea></div>

			<?php if($actionpage=="ubah"){?>
			<div>Kunci Akses:</div>
			<div>
				<input type="text" name="f_dialog[accesskey]" style="width:40em;" readonly="readonly" value="<?php echo esc_text($vdialog["accesskey"]);?>"/>
				<?php if(is_requester()){?>
				<input type="submit" name="f_dialog[retoken]" autofocus="autofocus" value="Ubah"/>
				<?php }?>
			</div>
			<?php }?>


			<?php if(is_provider() || is_publisher()){?>
			<div>Status:</div>
			<div>
				<select name="f_dialog[orderstatus]" autofocus="autofocus">
				<option <?php echo $vdialog["orderstatus"]=="off"?"selected=\"selected\"":""?> value="off">Off</option>
				<option <?php echo $vdialog["orderstatus"]=="on"?"selected=\"selected\"":""?> value="on">On</option>
				</select>
				<input type="hidden" name="f_dialog[oldorderstatus]" value="<?php echo esc_text($vdialog["orderstatus"]);?>"/>
			</div>
			<?php }else{?>
			<input type="hidden" name="f_dialog[orderstatus]" value="<?php echo esc_text($vdialog["orderstatus"]);?>"/>
			<?php }?>
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
		<form name="f_dialog" method="post" action="" accept-charset="UTF-8">
		<div class="dialog">
			<div>ID Pelaksana:</div>
			<div><input type="text" name="f_dialog[userlog]" style="width:40em;" readonly="readonly" value="<?php echo esc_text($vdialog["userlog"]);?>"/></div>
			<div>Instansi Pengguna:</div>
			<div><input type="text" name="f_dialog[userorg]" style="width:40em;background:#eee;" disabled="disabled" value="<?php echo esc_text($vdialog["userorg"]);?>"/></div>
			<div>Akses Operasi:</div>
			<div><input type="text" name="f_dialog[methodset]" style="width:40em;" readonly="readonly" value="<?php echo esc_text($vdialog["instance"].":".$vdialog["servicename"].".".$vdialog["methodname"]);?>"/></div>
			<div>Kunci Akses:</div>
			<div><input type="text" name="f_dialog[accesskey]" style="width:40em;" readonly="readonly" value="<?php echo esc_text($vdialog["accesskey"]);?>"/></div>
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
		if(is_requester())
			$aFilter=array("accesskey"=>array("label"=>"Kunci Akses","size"=>30,"attrs"=>"autofocus=\"autofocus\""));
		else
			$aFilter=array("userlog"=>array("label"=>"ID Pelaksana","size"=>30,"attrs"=>"autofocus=\"autofocus\""),
						   "accesskey"=>array("label"=>"Kunci Akses","size"=>30));
		$grid->setFilter($aFilter);
		$grid->rows=getOrderRows(is_requester()?$currLogname:$grid->query["userlog"],$grid->query["accesskey"],is_requester()?'':$currInstance);
		$grid->pager=getPageList($grid->rows,$grid->pagenum,10,1);
		$grid->listpager=getPageList($grid->rows,$grid->pagenum,10,5);
		$grid->pdf["data"]="orders/userlog=".(is_requester()?$currLogname:$grid->query["userlog"])."&accesskey=".$grid->query["accesskey"]."&instance=".(is_requester()?'':$currInstance);
		$grid->records=getOrders(is_requester()?$currLogname:$grid->query["userlog"],$grid->query["accesskey"],is_requester()?'':$currInstance,$grid->pager["perpage"],$grid->pager["startrow"]);
		$grid->colnames=array("userlog"=>array("label"=>"ID PELAKSANA"),
							  "userinstance"=>array("label"=>"ID INSTANSI"),
							  "accesskey"=>array("label"=>"KUNCI AKSES"),
							  "instance"=>array("label"=>"ID PENYEDIA"),
							  "servicename"=>array("label"=>"DIREKTORI"),
							  "methodname"=>array("label"=>"FUNGSI OPERASI"),
							  "orderstatus"=>array("label"=>"STATUS"),
							  "registered"=>array("label"=>"TGL.DAFTAR"),
							  "updated"=>array("label"=>"TGL.UBAH"));
		if(is_requester())
		$grid->operations=array(array("url"=>$interfaceuri."ubah/:id","title"=>"Ubah Data","icon"=>"ico/edit.png"),
								array("url"=>$interfaceuri."hapus/:id","title"=>"Hapus Data","icon"=>"ico/drop.png"));
		else
		$grid->operations=array(array("url"=>$interfaceuri."ubah/:id","title"=>"Ubah Data","icon"=>"ico/edit.png"));
		if($grid->display()==false){
		?>
		<div class="message"><b>Data belum ada</b></div>
		<?php
		}
	
	}
	echo "<br/>";
}


