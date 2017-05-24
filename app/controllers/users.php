<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
ver: 1.99y
*/


function resetpageusers(){
	return array("instanceid"=>"",
				   "fullname"=>"",
				   "email"=>"",
				   "logname"=>"",
				   "passkey"=>"",
				   "role"=>"",
				   "activity"=>"");
}

function profileusers($interface){
	global $db,$urikeys,$puri,$interfaces;
	$interfaceuri=home_url().$interface."/";
	$user=array(current_logname(),current_instanceid());
	$message="";
	$vdialog=resetpageusers();

	if((strtoupper($_SERVER['REQUEST_METHOD'])=='POST')
	&&(isset($_POST[session_name()])) 
	&&($_SERVER['HTTP_USER_AGENT']==decsay($_POST[session_name()],$_SESSION['idform']))
	&&(isset($_POST["f_dialog"]))){
		$vdialog=array_merge($vdialog,$_POST["f_dialog"]);
		if(isset($vdialog["submit"])){ // tombol simpan
			$vdialog['passkey']=empty($vdialog['password'])?dec64data($vdialog['passkey']):password_hash($vdialog['password'], PASSWORD_DEFAULT);
			$ret=updateUserByLogname($vdialog);
			if($ret=='OK'){
				addTrack(array("trackid"=>$vdialog['logname'],"trackname"=>$interface,"trackstatus"=>"EDIT","tracknote"=>"","trackdata"=>""));
				$message="Pemutakhiran data '".$vdialog['logname']."' berhasil disimpan.";
				logClose(true);
			}
			else
				$message="Pemutakhiran data '".$vdialog['logname']."' gagal disimpan.<br/>".$ret;
		}
	}

	if(!empty($db['default']['messages'])) $message=$db['default']['messages'];
	if(!empty($message)){
		$_SESSION['message']=$message;
	}
	$rs=infoUser($user);
	if(count($rs)>0){
		$vdialog=array_merge($vdialog,$rs);
		$vdialog['passkey']=enc64data($vdialog['passkey']);
		
		$_SESSION['idform']=dechex(time()).strrev(time());
		$code=encsay($_SERVER['HTTP_USER_AGENT'],$_SESSION['idform']);

		$token=array(
			'name'=>session_name(),
			'code'=>$code
		)
	?>
		<p style='padding:0 4px;font-size:28px;font-weight:bold;'>Profil Pelaksana</p>
		<hr/>
		<form name="f_dialog" method="post" action="" accept-charset="UTF-8">
		<div class="dialog">
			<div>Instansi:</div>
			<div><input type="text" name="f_dialog[instanceset]" style="width:40em;background:#eee;" disabled="disabled" value="<?php echo esc_text(current_organization($vdialog["instanceid"]));?>"/></div>
			<div>ID Pelaksana:</div>
			<div><input type="text" name="f_dialog[logname]"  style="width:40em;background:#eee;" readonly="readonly" value="<?php echo esc_text($vdialog["logname"]);?>"/></div>
			<div>Nama Pelaksana:*</div>
			<div><input type="text" name="f_dialog[fullname]" autofocus="autofocus" style="width:40em;" value="<?php echo esc_text($vdialog["fullname"]);?>" onkeypress="return noNumbers(event)"  onchange="upperCase(this)"/></div>
			<div>e-Mail:*</div>
			<div><input type="text" name="f_dialog[email]" style="width:40em;" value="<?php echo esc_text($vdialog["email"]);?>" onkeypress="return letterNumber(event,4)" onchange="lowerCase(this)"/></div>
			<div>Kata Kunci (Password):*</div>
			<div><input type="password" name="f_dialog[password]" style="width:40em;" placeholder="********************" value="" /></div>
			<div>Peran:</div>
			<div><input type="text" name="f_dialog[roleset]" style="width:40em;background:#eee;" disabled="disabled" value="<?php echo esc_text($vdialog["role"]);?>"/></div>
			<div>Status:</div>
			<div><input type="text" name="f_dialog[activityset]" style="width:40em;background:#eee;" disabled="disabled" value="<?php echo esc_text($vdialog["activity"]);?>"/></div>
			*: Dapat diubah
			<hr/>
			<div><input type="submit" name="f_dialog[submit]" value="Simpan"/></div>
			<input name="<?php echo $token['name'];?>" type="hidden" value="<?php echo esc_text($token['code']);?>"/>
			<input type="hidden" name="f_dialog[passkey]" value="<?php echo esc_text($vdialog["passkey"]);?>"/>
			<input type="hidden" name="f_dialog[instance]" value="<?php echo esc_text($vdialog["instance"]);?>"/>
			<input type="hidden" name="f_dialog[role]" value="<?php echo esc_text($vdialog["role"]);?>"/>
			<input type="hidden" name="f_dialog[activity]" value="<?php echo esc_text($vdialog["activity"]);?>"/>
			<input type="hidden" name="f_dialog[instanceid]" value="<?php echo esc_text($vdialog["instanceid"]);?>"/>
		</div>
		</form>
	<?php
	}
}

function pageusers($interface){
	global $db,$urikeys,$puri,$interfaces,$validLogin;
	if(!$validLogin){
		header('Location: '.home_url().'masuk');
		return;
	}
	if(!is_administrator()){ 
		profileusers($interface);
		return;
	}
	?>
	<p style='padding:0 4px;font-size:28px;font-weight:bold;'>Pelaksana</p>
	<?php
	tabmenu();
	$interfaceuri=home_url().$interface."/";
	$hasFinished=false;
	$currInstance="";
	if(isset($puri["action"]) && $puri["action"]=="list") $puri["action"]="";
	if(isset($puri["action"]) && !empty($puri["action"])){
		$actionpage=$puri["action"];
		$message="";
		$vdialog=resetpageusers();//init scope

		$instances=getInstances($currInstance);

		if(count($instances)>0)
			if(empty($vdialog["instanceid"])) $vdialog["instanceid"]=$instances[0]["id"];

		if((strtoupper($_SERVER['REQUEST_METHOD'])=='POST')
		&&(isset($_POST[session_name()])) 
		&&($_SERVER['HTTP_USER_AGENT']==decsay($_POST[session_name()],$_SESSION['idform']))
		&&(isset($_POST["f_dialog"]))){
			$vdialog=array_merge($vdialog,$_POST["f_dialog"]);
			if(isset($vdialog["submit"])){ // tombol simpan
				$vdialog['passkey']=empty($vdialog['password'])?dec64data($vdialog['passkey']):password_hash($vdialog['password'], PASSWORD_DEFAULT);
				switch ($actionpage){
				case "tambah":
					if(is_supervisor($vdialog["role"]) || is_administrator($vdialog["role"])) $vdialog["instanceid"]=0;
					$ret=addUser($vdialog);
					if($ret=='OK'){
						addTrack(array("trackid"=>$vdialog['logname'],"trackname"=>$interface,"trackstatus"=>"ADD","tracknote"=>"","trackdata"=>""));
						$message="Penambahan data '".$vdialog['logname']."' berhasil disimpan.";
						$hasFinished=true;
					}
					else
						$message="Penambahan data '".$vdialog['logname']."' gagal disimpan.<br/>".$ret;
					break;
				case "ubah":
					$vusers=getUserByID($vdialog["id"]);
					if(is_supervisor($vdialog["role"]) || is_administrator($vdialog["role"])) $vdialog["instanceid"]=0;
					$ret=updateUserByID($vdialog);
					if($ret=='OK'){
						addTrack(array("trackid"=>$vusers['logname'],"trackname"=>$interface,"trackstatus"=>"EDIT","tracknote"=>"","trackdata"=>""));
						$message="Pemutakhiran data '".$vdialog['logname']."' berhasil disimpan.";
						$hasFinished=true;
						if(is_administrator($vdialog["role"]) && $vdialog["logname"]==current_logname() && $vdialog["passkey"]!=current_passkey() )
							logClose(true);
					}
					else
						$message="Pemutakhiran data '".$vdialog['logname']."' gagal disimpan.<br/>".$ret;
					break;
				case "hapus":
					$vusers=getUserByID($vdialog["id"]);
					$ret=deleteUserByID($vdialog["id"]);
					if($ret=='OK'){
						addTrack(array("trackid"=>$vusers['logname'],"trackname"=>$interface,"trackstatus"=>"DELETE","tracknote"=>"","trackdata"=>""));
						$message="Data '".$vdialog['logname']."' berhasil dihapus.";
						$vdialog=resetpageusers();
						$hasFinished=true;
					}
					else
						$message="Data '".$vdialog['logname']."' gagal dihapus.<br/>".$ret;
				}
			}
		}

		if(isset($puri["id"]) && !empty($puri["id"])){
			$vusers=getUserByID($puri["id"]);
			if(is_array($vusers)) $vdialog=array_merge($vdialog,$vusers);
			$vdialog['passkey']=enc64data($vdialog['passkey']);
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
		$roles=content_roles();
		$activities=content_activity();

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
			<div>Instansi:*</div>
			<div>
			<select name="f_dialog[instanceid]" size="10" autofocus="autofocus" style="width:40em;" >
				<?php foreach($instances as $no=>$instance){?>
				<option <?php echo $vdialog["instanceid"]==$instance["id"]?"selected=\"selected\"":""?> 
				 value="<?php echo esc_text($instance["id"]);?>" ><?php echo esc_text($instance["instance"]." (".$instance["organization"].")");?></option>
				<?php }?>
			</select>
			</div>
			<div>Nama Pelaksana:*</div>
			<div><input type="text" name="f_dialog[fullname]" style="width:40em;" value="<?php echo esc_text($vdialog["fullname"]);?>" onkeypress="return noNumbers(event)"  onchange="upperCase(this)"/></div>
			<div>e-Mail:</div>
			<div><input type="text" name="f_dialog[email]" style="width:40em;" value="<?php echo esc_text($vdialog["email"]);?>" onkeypress="return letterNumber(event,4)" onchange="lowerCase(this)"/></div>
			<div>ID Pelaksana:*</div>
			<div><input type="text" name="f_dialog[logname]" style="width:40em;" value="<?php echo esc_text($vdialog["logname"]);?>"  onkeypress="return letterNumber(event,2)" onchange="lowerCase(this)"/></div>
			<div>Kata Kunci (Password):*</div>
			<div><input type="password" name="f_dialog[password]" style="width:40em;" placeholder="********************" value="" /></div>
			<div>Peran:</div>
			<div>
				<select name="f_dialog[role]">
				<?php foreach($roles as $val=>$text){?>
				<option <?php echo $vdialog["role"]==$text?"selected=\"selected\"":""?> value="<?php echo esc_text($text);?>"><?php echo esc_text($text);?></option>
				<?php }?>
				</select>
			</div>
			<div>Status:</div>
			<div>
				<select name="f_dialog[activity]">
				<?php foreach($activities as $val=>$text){?>
				<option <?php echo $vdialog["activity"]==$text?"selected=\"selected\"":""?> value="<?php echo esc_text($text);?>"><?php echo esc_text($text);?></option>
				<?php }?>
				</select>
			</div>
			*: Wajib diisi
			<hr/>
			<div><input type="submit" name="f_dialog[submit]" value="Simpan" /></div>
			<input name="<?php echo $token['name'];?>" type="hidden" value="<?php echo esc_text($token['code']);?>"/>
			<?php if($actionpage=="ubah"){?>
			<input type="hidden" name="f_dialog[id]" value="<?php echo esc_text($vdialog["id"]);?>"/>
			<input type="hidden" name="f_dialog[passkey]" value="<?php echo esc_text($vdialog["passkey"]);?>"/>
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
			<div>Instansi:</div>
			<div><input type="text" name="f_dialog[instanceset]" style="width:40em;" readonly="readonly" value="<?php echo esc_text(current_organization($vdialog["instanceid"]));?>"/></div>
			<div>Nama Pelaksana:</div>
			<div><input type="text" name="f_dialog[fullname]" style="width:40em;" readonly="readonly" value="<?php echo esc_text($vdialog["fullname"]);?>"/></div>
			<div>e-Mail:</div>
			<div><input type="text" name="f_dialog[email]" style="width:40em;" readonly="readonly" value="<?php echo esc_text($vdialog["email"]);?>"/></div>
			<div>ID Pelaksana:</div>
			<div><input type="text" name="f_dialog[logname]" style="width:40em;" readonly="readonly" value="<?php echo esc_text($vdialog["logname"]);?>"/></div>
			<div>Peran:</div>
			<div><input type="text" name="f_dialog[role]" style="width:40em;" readonly="readonly" value="<?php echo esc_text($vdialog["role"]);?>"/></div>
			<div>Status:</div>
			<div><input type="text" name="f_dialog[activity]" style="width:40em;" readonly="readonly" value="<?php echo esc_text($vdialog["activity"]);?>"/></div>
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
		$grid->setFilter(array("logname"=>array("label"=>"ID Pelaksana","size"=>30,"attrs"=>"autofocus=\"autofocus\"")));
		$grid->rows=getUserRows($grid->query["logname"]);
		$grid->pager=getPageList($grid->rows,$grid->pagenum,10,1);
		$grid->listpager=getPageList($grid->rows,$grid->pagenum,10,5);
		$grid->pdf["data"]="users/logname=".$grid->query["logname"];
		$grid->records=getUsers($grid->query["logname"],$grid->pager["perpage"],$grid->pager["startrow"]);
		$grid->colnames=array("instance"=>array("label"=>"ID INSTANSI"),
							  "fullname"=>array("label"=>"NAMA PELAKSANA"),
							  "logname"=>array("label"=>"ID PELAKSANA"),
							  "role"=>array("label"=>"PERAN"),
							  "activity"=>array("label"=>"STATUS"),
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


