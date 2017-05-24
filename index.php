<?php
if(floatval(phpversion())<5.5){
	echo "Saat ini server anda menggunakan PHP versi ".phpversion().", aplikasi ini hanya dapat digunakan pada PHP versi >= 5.5";
	exit(0); 
}
require_once 'sys/init.php';
$modname=array('api','print','download','php');
$rootapp='';
if(!in_array($rootapp,$modname)) 
	$rootapp=strstr($_SERVER['REQUEST_URI'],"/api/")==""?$rootapp:"api";
if(!in_array($rootapp,$modname)) 
	$rootapp=strstr($_SERVER['REQUEST_URI'],"/xml/")==""?$rootapp:"api";
if(!in_array($rootapp,$modname)) 
	$rootapp=strstr($_SERVER['REQUEST_URI'],"/json/")==""?$rootapp:"api";
if(!in_array($rootapp,$modname)) 
	$rootapp=strstr($_SERVER['REQUEST_URI'],"/print/")==""?$rootapp:"print";
if(!in_array($rootapp,$modname)) 
	$rootapp=strstr($_SERVER['REQUEST_URI'],"/download/")==""?$rootapp:"download";
if(!in_array($rootapp,$modname)) 
	$rootapp=strstr($_SERVER['REQUEST_URI'],"php");

ob_start();
switch ($rootapp):
case "api":
case "print":
case "download":
	require_once SYSPATH.$rootapp.'.php';
	break;
case "php":
	phpinfo();
	break;
default:
	require_once APP_START;
endswitch;
echo ob_get_clean();

