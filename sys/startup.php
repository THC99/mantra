<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require_once "lib/adodb5/adodb-exceptions.inc.php";
require_once "lib/adodb5/adodb.inc.php";      
require_once "lib/adodb5/adodb-active-record.inc.php";


$errmsg="";
	
function arr2ini(array $a, array $parent = array())
{
    $out = '';
    foreach ($a as $k => $v)
    {
        if (is_array($v))
        {
            //subsection case
            //merge all the sections into one array...
            $sec = array_merge((array) $parent, (array) $k);
            //add section information to the output
            $out .= '[' . join('.', $sec) . ']' . PHP_EOL;
            //recursively traverse deeper
            $out .= arr2ini($v, $sec);
        }
        else
        {
            //plain key->value case
	    		if(is_numeric($v)){
            	$out .= "$k=$v" . PHP_EOL;
	    		}
	    		else{
            	$out .= "$k=\"$v\"" . PHP_EOL;
	    		}
        }
    }
    return $out;
}

$has_setup=false;
$startupfile="files/startup.ini";

if(isset($_POST["proses"])){

	$data=array(
		"dbconnection"	=>array(
			"dbdriver"	=>$_POST["dbdriver"],
			"hostname"	=>$_POST["hostname"],
			"dbname"		=>$_POST["dbname"],
			"rootname"	=>base64_encode($_POST["rootname"]),
			"rootpass"	=>base64_encode($_POST["rootpass"]),
			"username"	=>base64_encode($_POST["username"]),
			"password"	=>base64_encode($_POST["password"])
		),
		"dbsupported"   =>array(
			"mysql"			=>"on",
			"postgresql"=>(isset($_POST["postgresql"]) && $_POST["postgresql"]=="on")?"on":"off",
			"oracle"		=>(isset($_POST["oracle"]) && $_POST["oracle"]=="on")?"on":"off",
			"mssql"			=>(isset($_POST["mssql"]) && $_POST["mssql"]=="on")?"on":"off" 				
		)
	);	
	file_put_contents($startupfile,arr2ini($data),LOCK_EX);
	if(!is_file($startupfile)) $errmsg="Inisiasi gagal! <br/>Solusi: $sudo chmod 755 mantra/files mantra/tmp && sudo chown www-data:www-data mantra/files mantra/tmp";
}

$db["dbdriver"]="";
$db["hostname"]="";
$db["rootname"]="";
$db["rootpass"]="";
$db["username"]="";
$db["password"]="";
$db["dbname"]="";
$dbs["mysql"]="1";
$dbs["postgresql"]="";
$dbs["oracle"]="";
$dbs["mssql"]="";

if(is_readable($startupfile)){
	$data=parse_ini_file($startupfile,true);
	
	if(isset($data["dbconnection"])){ 
		$db=$data["dbconnection"];
		$db['rootname']=base64_decode($db["rootname"]);	
		$db['rootpass']=base64_decode($db["rootpass"]);
		$db['username']=base64_decode($db["username"]);
		$db['password']=base64_decode($db["password"]);
	
		try{
					$adodb= newADOConnection($db["dbdriver"]);
					$adodb->debug=false;
					$adodb->setFetchMode(ADODB_FETCH_NUM); 
                	$adodb->connect($db["hostname"],$db["rootname"],$db["rootpass"]); 
                	//cek root user
                	$query="show grants for'".$db["rootname"]."'@'".$db["hostname"]."' ;";
                	$adors=$adodb->execute($query);
                	$rootPrivileges="GRANT ALL PRIVILEGES ON *.* TO '".$db["rootname"]."'@'".$db["hostname"]."'";
                	foreach($adors->getRows() as $row=>$columns){
                		$pos=strpos($columns[0],$rootPrivileges);
                		if ($pos!==false) $isRoot=true;
                	}
                	
                	if (!$isRoot) $errmsg="User ".$db["rootname"]." tidak memiliki hak akses sebagai administrator basis data";
                	else{
                		$adodb->connect($db["hostname"],$db["username"],$db["password"]); 
                		$has_setup=true;
                	}
		}
		catch(exception $e){
					$errmsg=$e->getMessage();
		}
	}
	if(isset($data["dbsupported"])){
		$dbs=$data["dbsupported"];
		$has_setup=$has_setup and true;
	}	
}

if(!$has_setup){
?>
<html>
<head>
<link rel='stylesheet' media='screen' href='css/style.css' type='text/css'/>
<style>
h1{
	font-size:20px;
}
u{
	font-size:inherit;
	text-decoration:underline;
}
#mainform{
	padding:10px;
}
</style>
</head>
<body>
<div id="mainform">
	<h1>Inisiasi Database Aplikasi</h1>
	<?php if(!empty($errmsg)) echo "<div style='color:#fff;background:#ff0000;padding:10px;'>{$errmsg}</div>";?> 
	<br/>
	<form name="startup" action="" method="post" accept-charset="UTF-8">
	<table>
	<tr>
		<td><label>Pengendali Database:</label></td>
		<td>
		<select name="dbdriver">
			<option value="mysqli" <?php echo $db['dbdriver']=="mysqli"?'selected="selected"':'' ;?>>MySQLi</option>
			<option value="mysql" <?php echo $db['dbdriver']=="mysql"?'selected="selected"':'';?>>MySQL</option>
		</select>
		</td>
	</tr>
	<tr>
		<td><label>Lokasi Database:</label></td>
		<td><input name="hostname" type="text" size="40" value="<?php echo $db['hostname'];?>"/></td>
	</tr>
	<tr>
		<td><label>Nama Database:</label></td>
		<td><input name="dbname" type="text" size="40" value="<?php echo $db['dbname'];?>"/></td>
	</tr>
	<tr>
		<td><label>Nama Administrator:</label></td>
		<td><input name="rootname" type="text" size="40" value="<?php echo $db['rootname'];?>"/></td>
	</tr>
	<tr>
		<td><label>Sandi Administrator:</label></td>
		<td><input name="rootpass" type="password" size="40" value="<?php echo $db['rootpass'];?>"/></td>
	</tr>
	<tr>
		<td><label>Nama Pengguna:</label></td>
		<td><input name="username" type="text" size="40" value="<?php echo $db['username'];?>"/></td>
	</tr>
	<tr>
		<td><label>Sandi Pengguna:</label></td>
		<td><input name="password" type="password" size="40" value="<?php echo $db['password'];?>"/></td>
	</tr>
	<tr>
		<td><label>Dukungan Database:</label></td>
		<td>
			<input name="mysql" type="checkbox" checked="checked" disabled="disabled"/> MySQL<br/>
			<input name="postgresql" type="checkbox" <?php echo $dbs["postgresql"]=="1"?'checked="checked"':''?>/> PostgreSQL<br/>
			<input name="oracle" type="checkbox" <?php echo $dbs["oracle"]=="1"?'checked="checked"':'';?>/> ORACLE<br/>
			<input name="mssql" type="checkbox" <?php echo $dbs["mssql"]=="1"?'checked="checked"':'';?>/> Microsoft SQL-Server / Sybase<br/>
		</td>
	</tr>
	<tr>
		<td><input name="proses" type="submit" value="Proses"/></td>
	</tr>
	</table>
	</form>
</div>
</body>
</html> 
<?php
	exit;
}


$dbs=array();
$startupfile="files/startup.ini";
if( is_readable($startupfile) ){
				$data=parse_ini_file($startupfile,true);
				if(isset($data["dbsupported"])){
								$dbs=$data["dbsupported"];
				}
}

/*
 *--------------------------------------------------------------------
 *  Check for mandatory extension/module
 *--------------------------------------------------------------------
 */


$unsetmodule=0;
if(function_exists('apache_get_modules')){
	$modules=apache_get_modules();
	$reqmodule=array(
		'rewrite'=>'mod_rewrite',
		'mime'=>'mod_mime'
	);
	$ainfo=array();
	foreach($reqmodule as $key=>$modname) if(!in_array($modname,$modules)){
		$ainfo[$key]=$modname;
		$unsetmodule++;
	}
	if(count($ainfo)>0){
		echo "<pre>";
		echo "<strong style='color:red;'>Modul Apache yang belum terpasang</strong>:<ul>";
		foreach($ainfo as $key=>$val){
			echo "<li>$key: $val</li>";
		}
		echo "</ul>";
		echo "Hubungi Pengelola Web Server untuk memasang Modul Apache tersebut, agar Aplikasi ini dapat digunakan.<br><br/>";
		/*
		echo "Modul Apache yang sudah terpasang:<ul>";
		foreach($modules as $val){
			echo "<li>".$val."</li>";
		}
		echo "</ul>";
		*/
		echo "<pre>";
	}
}


if(function_exists('get_loaded_extensions')){
	$modules=get_loaded_extensions();
	$reqmodule=array(
		'curl'=>'php5-curl.so / php_curl.dll',
		'dom'=>'php5-dom.so / php_dom.dll',
		'fileinfo'=>'php5-fileinfo.so / php_fileinfo.dll',
		'gd'=>'php5-gd.so / php_gd.dll',
		'mbstring'=>'php5-mbstring.so / php5-mbstring.dll',
		'openssl'=>'php5-openssl.so / php5_openssl.dll',
		'mysql'=>'php5-mysql.so / php_mysql.dll',
		'mysqli'=>'php5-mysqli.so / php_mysqli.dll',
		'session'=>'php5-session.so / php5_session.dll',
		'tidy'=>'php5-tidy.so / php5_tidy.dll',
		'xml'=>'php5-xml.so / php_xml.dll'
	);
	if(isset($dbs['mssql']) && $dbs['mssql']=='1') $reqmodule['mssql']='php5-sybase.so / php5_mssql.dll';
	if(isset($dbs['oracle']) && $dbs['oracle']=='1' ) $reqmodule['oci8']='oci8.so / oci8.dll';
	if(isset($dbs['postgresql']) && $dbs['postgresql']=='1') $reqmodule['pgsql']='php5-pgsql.so / php5_pgsql.dll';

	$ainfo=array();
	foreach($reqmodule as $key=>$modname) if(!in_array($key,$modules)){
		$ainfo[$key]=$modname;
		$unsetmodule++;
	}
	if(count($ainfo)>0){
		echo "<pre>";
		echo "<strong style='color:red;'>Modul Extension PHP yang belum terpasang</strong>:<ul>";
		foreach($ainfo as $key=>$val){
			echo "<li>$key: $val</li>";
		}
		echo "</ul>";
		echo "Hubungi Pengelola Web Server untuk memasang Modul Extension PHP tersebut, agar Aplikasi ini dapat digunakan.<br><br/>";
		/*
		echo "Modul Extension PHP yang sudah terpasang:<ul>";
		foreach($modules as $val){
			echo "<li>".$val."</li>";
		}
		echo "</ul>";
		*/
		echo "<pre>";
	}
}

if($unsetmodule>0) exit;

