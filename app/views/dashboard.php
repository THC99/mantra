<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
ver: 1.99y
*/
init_request_page();
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<title><?php echo APP_TITLE;?></title>
<base href="<?php echo home_url();?>" />
<meta charset="utf-8" />
<meta content-type="text/html" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name='robots' content='noindex,nofollow' />
<meta name='author' content='Didi Sukyadi' />
<meta name='keywords' content='MANTRA GSB INTEROPERABILITAS API WEB-SERVICE WEB-API ' />
<meta name='description' content='Manajemen Integrasi Informasi dan Pertukaran Data Badan Pemerintahan' />
<meta name='geo.position' content='49.33;-86.59' />

<link rel='icon' href='<?php echo getAppIcon();?>' type='image/x-icon' />
<link rel='shortcut icon' href='<?php echo getAppIcon();?>' type='image/ico' />
<link rel="stylesheet" media='screen' href="lib/calendar/calendar.css" type="text/css">
<link rel='stylesheet' media='screen' href='css/style.css' type='text/css'/>

<script runat="server" type='text/javascript' src='js/jquery-1.7.2.min.js'></script>
<script runat="server" type='text/javascript' src='js/highcharts.js'></script>
<script runat="server" type='text/javascript' src='js/exporting.js'></script>
<script runat="server" type='text/javascript' src='js/jquery.pngFix.pack.js'></script>
<script runat="server" type='text/javascript' src='lib/calendar/calendar.js'></script>
<script runat="server" type='text/javascript' src='js/app.js'></script>

</head>

<body>
<noscript>
	<div class="message">
		<b>Aplikasi Web ini didukung penggunaan perintah Javascript, aktifkan dukungan konfigurasi Javascript pada Browser ini.</b>
	</div>
	<style>#mainpage { display:none; }</style>
</noscript>
<div id="mainpage">
	<div class="headbar">
	<?php echo titlebar();?>
	<?php echo headmenu();?>		
	<?php echo clockbar();?>
	</div>
	<?php echo logbar();?>
	<div id="messagebox"></div>
	<div id="wrap">
		<div id="content">
		<?php echo container();?>
		</div>
	</div>
</div>

<?php 
if(isset($_SESSION['message']) || !empty($db['default']['messages'])){
$currentmessage=(isset($_SESSION['message'])?trim($_SESSION['message']):"").(!empty($db['default']['messages'])?", ".addslashes($db['default']['messages']):"");
?>

<script runat="server" type="text/javascript" autoload="true">
$(document).ready(function(){
	today=new Date('<?php echo date("F j, Y H:i:s");?>');
	$('#messagebox').html("<?php echo $currentmessage;?>").show().fadeOut(20000);
});
</script>

<?php 
unset($_SESSION['message']);
$db['default']['messages']='';
$currentmessage='';
}?>

</body>
</html>


