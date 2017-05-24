<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
*/


$startupfile="files/startup.ini";
$dbc=array('hostname'=>'','dbdriver'=>'','dbname'=>'','rootname'=>'','rootpass'=>'','username'=>'','password'=>'');
$smtpconf=array('smtp'=>'','port'=>'','email'=>'','password'=>'');
if( is_readable($startupfile) ){
        $data=parse_ini_file($startupfile,true);
        if(isset($data["dbconnection"])){ 
					$dbc=$data["dbconnection"];
					$dbc['rootname']=base64_decode($dbc["rootname"]);	
					$dbc['rootpass']=base64_decode($dbc["rootpass"]);
					$dbc['username']=base64_decode($dbc["username"]);
					$dbc['password']=base64_decode($dbc["password"]);      
        }
        if(isset($data["smtp_config"])){ 
					$smtpconf=$data["smtp_config"];
					$smtpconf['password']=base64_decode($smtpconf["password"]);      
        }
}

//--------------  Interface --------------------\\
$urikeys=array("page","action","id");
$puri=array_combine($urikeys,array_fill(0,3,""));
$interfaces=array("home","about");

$interfaces["instances"]="instansi";
$interfaces["users"]="pelaksana";
$interfaces["orders"]="akses";
$interfaces["services"]="direktori";
$interfaces["methods"]="fungsi";
$interfaces["tracks"]="riwayat";
$interfaces["settings"]="setelan";
//$interfaces["codefication"]="kodefikasi";
$interfaces["profile"]="elemen.profil";
$interfaces["notifications"]="notifikasi.email";



/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
ALTER TABLE `users` ADD FOREIGN KEY (`userprovider`) REFERENCES `dbmantra0`.`providers`(`providername`) ON DELETE RESTRICT ON UPDATE RESTRICT;

*/

$active_group = 'default';
$active_record = TRUE;

$db['default']['dbdriver'] = $dbc['dbdriver'];
$db['default']['hostname'] = $dbc['hostname'];
$db['default']['rootname'] = $dbc['rootname'];
$db['default']['rootpass'] = $dbc['rootpass'];
$db['default']['username'] = $dbc['username'];
$db['default']['password'] = $dbc['password'];
$db['default']['database'] = $dbc['dbname'];
$db['default']['dbprefix'] = '';
$db['default']['pconnect'] = TRUE;
$db['default']['db_debug'] = FALSE;
$db['default']['cache_on'] = FALSE;
$db['default']['cachedir'] = '';
$db['default']['char_set'] = 'utf8';
$db['default']['dbcollat'] = 'utf8_general_ci';
$db['default']['swap_pre'] = '';
$db['default']['autoinit'] = TRUE;
$db['default']['stricton'] = FALSE;
$db['default']['messages'] = ''; 

$config['charset'] = 'UTF-8';
$config['system_date_format'] = 'Y-m-d H:i:s';
$config['info_header'] = FALSE;

$smtp_mail['smtp'] = $smtpconf['smtp'];
$smtp_mail['port'] = $smtpconf['port'];
$smtp_mail['email'] = $smtpconf['email'];
$smtp_mail['password'] = $smtpconf['password'];
/*
|--------------------------------------------------------------------------
| Session Variables
|--------------------------------------------------------------------------
|
| 'sess_cookie_name'		= the name you want for the cookie
| 'sess_expiration'			= the number of SECONDS you want the session to last.
|   by default sessions last 7200 seconds (two hours).  Set to zero for no expiration.
| 'sess_expire_on_close'	= Whether to cause the session to expire automatically
|   when the browser window is closed
| 'sess_encrypt_cookie'		= Whether to encrypt the cookie
| 'sess_use_database'		= Whether to save the session data to a database
| 'sess_table_name'			= The name of the session database table
| 'sess_match_ip'			= Whether to match the user's IP address when reading the session data
| 'sess_match_useragent'	= Whether to match the User Agent when reading the session data
| 'sess_time_to_update'		= how many seconds between CI refreshing Session Information
|
*/
$config['sess_cookie_name']		= 'gsb_session';
$config['sess_expiration']		= 7200;
$config['sess_expire_on_close']	= FALSE;
$config['sess_encrypt_cookie']	= FALSE;
$config['sess_use_database']	= FALSE;
$config['sess_table_name']		= 'gsb_sessions';
$config['sess_match_ip']		= FALSE;
$config['sess_match_useragent']	= TRUE;
$config['sess_time_to_update']	= 300;

/*
|--------------------------------------------------------------------------
| Cookie Related Variables
|--------------------------------------------------------------------------
|
| 'cookie_prefix' = Set a prefix if you need to avoid collisions
| 'cookie_domain' = Set to .your-domain.com for site-wide cookies
| 'cookie_path'   =  Typically will be a forward slash
|
*/
$config['cookie_prefix']	= '';
$config['cookie_domain']	= '';
$config['cookie_path']		= '/';

