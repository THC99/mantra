<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
*/


require_once SYSPATH.'config.php';
require_once SYSPATH.'common.php';
require_once SYSPATH.'models.php';

if(!logOpen()) exit('No user access allowed');

$reqpar=array();
$reqdata=str_replace(URL_BASEPATH."download/", "", $_SERVER['REQUEST_URI']);
parse_str($reqdata,$reqpar);
if(isset($reqpar['name'])) $filename=base64_decode($reqpar['name']);
if(isset($reqpar['url'])) $url=base64_decode($reqpar['url']);
if(isset($reqpar['method'])) $method=base64_decode($reqpar['method']);
if(isset($reqpar['accesskey'])) $accesskey=base64_decode($reqpar['accesskey']);
if(isset($reqpar['input'])) $input=html_entity_decode(base64_decode($reqpar['input']));

$file=REPODIR.$filename;
if ($file && is_readable($file)){
	header('content-type: application/text');//.mime_content_type($file));
	header('content-disposition: attachment; filename='.rawurlencode(basename($filename)));
	// Display the file
	ob_start();
	readfile($file);
	$buff=ob_get_contents();
	if(isset($input)) $buff=str_replace("%INPUT%",$input,$buff);
	if(isset($url)) $buff=str_replace("%URL%",$url,$buff);
	if(isset($method)) $buff=str_replace("%METHOD%",$method,$buff);
	if(isset($accesskey)) $buff=str_replace("%ACCESSKEY%",$accesskey,$buff);
	ob_end_clean();
	echo $buff;
}
