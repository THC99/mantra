<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
ver:1.99y
*/


function pagelogin(){
global $validLogin,$sess_name;
	if(!$validLogin){
		$authid=dechex(time()).strrev(time());
		$_SESSION['order']=$authid;
		$image=gencaptcha($captcha);
		?>
		<div id="panel">
			<div class="logintitle">Otentikasi</div>
			<hr/>
			<form name="f_login" method="post" action="" accept-charset="UTF-8" onsubmit="submitLogin(this,'f_login','<?php echo $sess_name;?>','<?php echo $authid;?>')">
			<div class="item">
				<input type="hidden" name="f_login[<?php echo $sess_name;?>]"/>
				<div>ID Pengguna:</div>
				<div><input type="text" id="logname" name="f_login[logname]" value="" size="35" autofocus="autofocus"/></div>
				<div>Kata Kunci:</div>
				<div><input type="password" name="f_login[passkey]" value="" size="35" /></div>
				<div>Kode Verifikasi: (isi sesuai tulisan dibawah)</div>
				<div><input type="text" name="f_login[captcha]" value="" size="35" style="text-transform:uppercase;" /></div>
				<div style="width:64px; height:40px;">
					<img src=data:image/png;base64,<?php echo $image;?> alt="<?php echo $captcha;?>"/>					
				</div>	
				<div><input type="submit" name="f_login[submit]" value="Proses"/></div>
			</div>
			</form>
		</div>
		<br/>
		<?php
	}
}
