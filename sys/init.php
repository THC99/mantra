<?php
/*
Programmed by 	: Didi Sukyadi
Assisted by	: Agung Basuki
*/

/*
 * -------------------------------------------------------------------
 *  Initialize application constants
 * -------------------------------------------------------------------
 */
	$basepath=pathinfo($_SERVER['SCRIPT_NAME'],PATHINFO_DIRNAME);
	$basepath=substr($basepath,-1)=='/'?$basepath:$basepath.'/';
	define('URL_BASEPATH',$basepath);
	define('APP_NAME','MANTRA ver:1.99y');
	define('APP_TITLE','Manajemen Integrasi Informasi dan Pertukaran Data');
	define('APP_ICON','img/tinymantra.png');
	define('APP_CACHE',FALSE);
	define('APP_ENC',FALSE);


	if(!defined('FS_CHMOD_DIR')) define('FS_CHMOD_DIR', 0755 );
	if(!defined('FS_CHMOD_FILE')) define('FS_CHMOD_FILE', 0644 );

/*
 *-----------------------------------------------------------------
 *  Initialize global variable
 *-----------------------------------------------------------------
 */
	$agent = $is_Lynx = $is_Gecko = $is_IE = $is_WinIE = $is_MacIE = $is_Opera = $is_NS4 = $is_Safari = $is_Chrome = $is_Firefox = $is_Netscape = $is_RV = $is_Iphone = false;
	$current_Browser = "Unknown";
	$validLogin=false;
	$messageAPI="";
	
	date_default_timezone_set("Asia/Jakarta");
	$sess_name="MANTRA1".$_SERVER[base64_decode('SFRUUF9VU0VSX0FHRU5U')].date('_Ymd');
	$sess_name=md5(sha1(strrev($sess_name)));

/*
 * ---------------------------------------------------------------
 *  Initialize error reporting, system path and application folder
 * ---------------------------------------------------------------
 */

	ini_set("allow_url_fopen","on");
	ini_set("allow_url_include","off");
	ini_set("default_mimetype","text/html");
	ini_set("default_socket_timeout","60");
	ini_set("display_errors","on"); // on for debug, off for production
	ini_set("display_startup_errors","on");
	ini_set("error_reporting","on"); // on for debug, off for production
	ini_set("html_errors","on");
	ini_set("ignore_repeated_errors","off");
	ini_set("ignore_repeated_source","off");
	ini_set("implicit_flush","off");
	ini_set("log_errors","on");
	ini_set("magic_quotes_gpc","off");
	ini_set("magic_quotes_runtime","off");
	ini_set("magic_quotes_sybase","off");
	ini_set("max_execution_time","3000");
	ini_set("memory_limit","-1");
	ini_set("report_memleaks","on");
	
	ini_set("session.bug_compat_42","off");
	ini_set("session.bug_compat_warn","off");
	ini_set("session.cookie_lifetime","0"); // Destroy after 0 (close) or 1800 (30 minutes) or 3600 (1 hour)
	ini_set("session.name",$sess_name);
	ini_set("session.cache_limiter","nocache");
	ini_set("session.use_cookies","1");
	ini_set("session.use_only_cookies","1");
	ini_set("session.use_trans_sid","0");    // Trasnparent share SID for PHP < 5 (0:Disabled 1:Enabled) 
	ini_set("session.use_strict_mode","1");  // Strict mode using SID for PHP > 5
	ini_set("session.cookie_httponly","1");
	ini_set("session.gc_divisor","1000");
	ini_set("session.gc_maxlifetime","0");
	ini_set("session.hash_function","sha256");  // untuk sistem 64bit gunakan sha512
	ini_set("session.hash_bits_per_character","6");
	
	ini_set("short_open_tag","off");
	ini_set("track_errors","off");
	ini_set("url_rewriter.tags","a=href,area=href,frame=src,input=src,form=fakeentry");
	ini_set("user_agent","");
	ini_set("y2k_compliance","on");
	ini_set("zlib.output_compression","off");

	error_reporting(E_ERROR); // E_STRICT for debug, E_ERROR for production
	session_cache_limiter('nocache');

	$root_path=pathinfo($_SERVER['SCRIPT_FILENAME'],PATHINFO_DIRNAME);
	$root_path=substr($root_path,-1)=='/'?$root_path:$root_path.'/';
	$system_path = $root_path.'sys';
	$application_folder = $root_path.'app';
 	$application_startup = "gsb";

/*
 * ---------------------------------------------------------------
 *  Resolve the system path for increased reliability
 * ---------------------------------------------------------------
 */
	if (realpath($system_path) !== FALSE){
		$system_path = realpath($system_path).'/';
	}

	// ensure there's a trailing slash
	$system_path = rtrim($system_path, '/').'/';

	// Is the system path correct?
	if ( ! is_dir($system_path)){
		exit("Your system folder path does not appear to be set correctly. Please open the following file and correct this: ".pathinfo(__FILE__, PATHINFO_BASENAME));
	}

/*
 * -------------------------------------------------------------------
 *  Now that we know the path, set the main path constants
 * -------------------------------------------------------------------
 */
	// The name of THIS file
	define('SELF', pathinfo(__FILE__, PATHINFO_BASENAME));

	// The PHP file extension
	define('EXT', '.php');

	// Path to the system folder
	define('BASEPATH', str_replace("\\", "/", $root_path."/"));

	define('SYSPATH', str_replace("\\", "/", $system_path));


	// Path to the front controller (this file)
	define('FCPATH', str_replace(SELF, '', __FILE__));

	// Name of the "system folder"
	define('SYSDIR', trim(strrchr(trim(SYSPATH, '/'), '/'), '/'));

	define('FILEDIR', BASEPATH.'files/');
	define('ICONDIR', BASEPATH.'ico/');
	define('LIBDIR', BASEPATH.'lib/');
	define('IMGDIR', BASEPATH.'img/');
	define('REPODIR', BASEPATH.'rep/');
	define('TMPDIR', BASEPATH.'tmp/');


	// The path to the "application" folder
	if(is_dir($application_folder)){
		define('APPPATH', $application_folder.'/');
	}
	else{
		if(!is_dir(SYSPATH.$application_folder.'/')){
			exit("Your application folder path does not appear to be set correctly. Please open the following file and correct this: ".SELF);
		}
		define('APPPATH', SYSPATH.$application_folder.'/');
	}

	define('APP_CONTROLLER', APPPATH.'controllers/');
	define('APP_VIEW', APPPATH.'views/');
	define('APP_MODEL', APPPATH.'models/');
	define('APP_START', APP_CONTROLLER.$application_startup.EXT);

	define('ERRLOG',BASEPATH.'tmp/error.txt');

	define('OWNER_CACHE','chown -R www-data:www-data '.BASEPATH.'tmp');
	define('GROUP_CACHE','chgrp -R www-data:www-data '.BASEPATH.'tmp'); 


	require_once 'sys/startup.php';
