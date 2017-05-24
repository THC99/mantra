<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
*/

require_once LIBDIR.'phpmailer/PHPMailerAutoload.php';
require_once SYSPATH.'models.php';


function sendMailNotifications($body="",$subject="",$addressess="")
{
	global $smtp_mail;
	$mail             = new PHPMailer();
	
	/*
	$mail->SMTPOptions = array(
    	'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    	)
	);
	*/
		
	$mail->IsSMTP(); // telling the class to use SMTP
	$mail->Host       = $smtp_mail['smtp']; //SMTP Server
	$mail->SMTPAuth   = true;                  // enable SMTP authentication
	$mail->Port       = $smtp_mail['port']; //SMTP Port
	$mail->Username   = $smtp_mail['email']; //Email Account 
	$mail->Password   = $smtp_mail['password']; //Email Account Password
	$mail->SetFrom($smtp_mail['email'], 'MANTRA GSB');
	$mail->Subject    = $subject;
	$mail->MsgHTML($body);
	
	//$mail->Mailer="smtp";
	//$mail->SMTPSecure = "tls";
	//$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
                                           // 1 = errors and messages
                                           // 2 = messages only
	//$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

	foreach($addressess as $key => $value){
		$mail->AddAddress($value);		
	}

	if(!$mail->Send()) {
  		return "Tidak dapat mengirimkan notifikasi melalui email.";
  		//return "Notifikasi gagal dikirim " . $mail->ErrorInfo;
	} else {
  		return "Notifikasi akses layanan berhasil dikirim.";
	}
}


function verifyAddressess($addressess){
	$result=array();
	
	foreach($addressess as $key => $value){
			if (!is_null($value['email'])) $result[]=$value['email'];
	}

	return $result;
}


function sendNotifications($input,$id)
{
	$result=false;
	$vData=getNotificationsByID($id);
	$notifLevels=explode(",",$vData['level']);
	$addressess=array();
	$replacedString=array();
	
	foreach($notifLevels as $key => $value){
		if ($value==0){ //notifikasi permintaan akses dikirim ke instansi penyedia layanan
			$addressess=array_merge($addressess,getProviderEmail($input['instance']));
			$replacerString=array($input['username'],$input['userorg'],$input['servicename'],$input['methodname']);
		}elseif ($value==1){ //notifikasi permintaan akses dikirim ke user provider
			$addressess=array_merge($addressess,getUsersEmail($input['instance'], $input['id'], $input['serviceid']));
			$replacerString=array($input['username'],$input['userorg'],$input['servicename'],$input['methodname']);
		}elseif ($value==2){ //notifikasi on/off akses dikirim ke user requester
			$addressess=array_merge($addressess,getUserEmail($input['userlog']));
			$replacerString=array($input['userlog'],$input['servicename'],$input['methodname'],$input['organization']);
		}
	}

	$verifiedAddressess=verifyAddressess($addressess);

	if (count($verifiedAddressess)>0){
		$body=$vData['pesan'];
		preg_match_all('/\bvalue\d\b/',$body,$sValues,PREG_SET_ORDER);
		foreach($sValues as $key => $value){
			$replacedString[]=$value[0];
		}
		$body = str_replace($replacedString, $replacerString, $body);

		return sendMailNotifications($body,$vData['subyek'],$verifiedAddressess);
		
	}else{
		return "Tidak dapat mengirimkan notifikasi, alamat email kosong";
	}	
}