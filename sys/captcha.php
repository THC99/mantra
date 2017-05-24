<?php

function gencaptcha(&$captcha){
	$data= "ABCDEFGHIJKLMNPRSTUVWXYZ0123456789";
	$code= substr(str_shuffle($data), 0, 6);
	$captcha=$code;
	if(function_exists('imagecreatetruecolor')){
		$im = imagecreatetruecolor(172, 32);
		$bkg_color = imagecolorallocate($im, 0xff, 0xff, 0xff);
		$txt_color = imagecolorallocate($im, 0xf0, 0x00, 0x00);
		imagefill($im,0,0,$bkg_color);
		if(function_exists('imagettftext')){
			imagettftext($im, 32, 0, 0, 32, $txt_color, 'lib/fonts/Consolas.ttf', $code);
		}
		else{
			$font = imageloadfont('lib/fonts/consolas28.gdf');
			imagestring($im,$font,0,0,$code,$txt_color);
		}
		ob_start();
		imagepng($im);
		$content = ob_get_clean();
		imagedestroy($im);
		if(session_id()) $_SESSION['captcha']=password_hash($code, PASSWORD_DEFAULT);
		return base64_encode($content);
	}
	else return '';
}


