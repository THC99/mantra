<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
*/


function enc64data($data){
	$encdata=base64_encode($data);
	return strrev($encdata);
}

function dec64data($data){
	$decdata=strrev($data);
	return base64_decode($decdata);
}

function encsay($str,$pass){
	$add=0;$newpass="";$result="";
	$div=strlen($str)/strlen($pass);
	while($add<=$div){
		$newpass.=$pass;
		$add++;
	}
	for($i=0;$i<strlen($str);$i++){
		$tmp=ord($str[$i])+ord($newpass[$i]);
		$tmp=$tmp>255?($tmp-256):$tmp;
		$result.=strtoupper(dechex($tmp));
	}
	return $result;
}

function decsay($str,$pass){
	$add=0;$newpass="";$result="";
	$div=intval(strlen($str)/2)/strlen($pass);
	while($add<=$div){
		$newpass.=$pass;
		$add++;
	}
	for($i=0,$j=0;$i<strlen($str);$i+=2,$j++){
		$hex=substr($str,$i,2);
		$tmp=hexdec($hex)-ord($newpass[$j]);
		$tmp=$tmp<0?($tmp+256):$tmp;
		$result.=chr($tmp);
	}
	return $result;
}


function generateCode($length = 6){
        $az = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $azr = rand(0, 51);
        $azs = substr($az, $azr, 10);
        $stamp = hash('sha256', time());
        $mt = hash('sha256', mt_rand(5, 20));
        $alpha = hash('sha256', $azs);
        $hash = str_shuffle($stamp . $mt . $alpha);
        $code = ucfirst(substr($hash, $azr, $length));
        return $code;
}

function crypto_rand_secure($min, $max) {
        $range = $max - $min;
        if ($range < 0) return $min; // not so random...
        $log = log($range, 2);
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);
        return $min + $rnd;
}

function getToken($length){
    $token = "";
    $codeAlphabet = "";//"ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
    $codeAlphabet.= "0123456789";
    for($i=0;$i<$length;$i++){
        $token .= $codeAlphabet[crypto_rand_secure(0,strlen($codeAlphabet))];
    }
    return $token;
}

function max_array_depth($a) {
	static $depth = 0;
	if(is_array($a)) 
	{
		$depth++;
		array_map("array_depth", $a);
	}
	return $depth;
}

function max_array_dimension($a){
	return max(array_map('count', $a));
}

function count_dimension($a, $counter = 0) {
   if(is_array($a)) {
      return count_dimension(current($a), ++$counter);
   } 
   else {
      return $counter;
   }
}

function array_subset( $a, $b ){
    if( count( array_diff( array_merge($a,$b), $b)) == 0 )
        return true;
    else
        return false;
}

function stop($message,$halt=false){
	file_put_contents(ERRLOG,$message);
	if($halt) die($message);
}

function is_ssl() {
	if ( isset($_SERVER['HTTPS']) ) {
		if ( 'on' == strtolower($_SERVER['HTTPS']) )
			return true;
		if ( '1' == $_SERVER['HTTPS'] )
			return true;
	} elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
		return true;
	}
	return false;
}

function base_url(){
	$result=( is_ssl() ? 'https://' : 'http://' ).$_SERVER['HTTP_HOST'];
	return $result;
}

function home_url(){
	$result=( is_ssl() ? 'https://' : 'http://' ).$_SERVER['HTTP_HOST'].URL_BASEPATH;
	return $result;
}

function full_uri(){
	$result=( is_ssl() ? 'https://' : 'http://' ).$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	return $result;
}

function getURIPart($uri){
	$pos=strpos($uri,"?");
	if($pos==false)
		return "?";
	else
		return "&";
}

function url_is_accessable_via_ssl($url){
	if (in_array('curl', get_loaded_extensions())) {
		$ssl = preg_replace( '/^http:\/\//', 'https://',  $url );

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $ssl);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

		curl_exec($ch);

		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close ($ch);

		if ($status == 200 || $status == 401) {
			return true;
		}
	}
	return false;
}

function cache_javascript_headers() {
	$expiresOffset = 864000; // 10 days
	header( "Content-Type: text/javascript; charset=" . get_bloginfo( 'charset' ) );
	header( "Vary: Accept-Encoding" ); // Handle proxies
	header( "Expires: " . gmdate( "D, d M Y H:i:s", time() + $expiresOffset ) . " GMT" );
}

function get_nocache_headers() {
	$headers = array(
		'Expires' => 'Tue, 1 Jan 1980 00:00:00 GMT',
		'Last-Modified' => gmdate( 'D, d M Y H:i:s' ) . ' GMT',
		'Cache-Control' => 'no-cache, must-revalidate, max-age=0',
		'Pragma' => 'no-cache',
	);
	return $headers;
}

function nocache_headers() {
	$headers = get_nocache_headers();
	foreach( $headers as $name => $field_value )
		@header("{$name}: {$field_value}");
}

function send_nosniff_header() {
	@header( 'X-Content-Type-Options: nosniff' );
}

function send_frame_options_header() {
	@header( 'X-Frame-Options: SAMEORIGIN' );
}

function send_web_header() {
	@header('X-Powered-By: MANTRA'); 
}




/**
 * Converts named entities into numbered entities.
 *
 * @since 1.5.1
 *
 * @param string $text The text within which entities will be converted.
 * @return string Text with converted entities.
 */
function ent2ncr($text) {
	$to_ncr = array(
		'&quot;' => '&#34;',
		'&amp;' => '&#38;',
		'&frasl;' => '&#47;',
		'&lt;' => '&#60;',
		'&gt;' => '&#62;',
		'|' => '&#124;',
		'&nbsp;' => '&#160;',
		'&iexcl;' => '&#161;',
		'&cent;' => '&#162;',
		'&pound;' => '&#163;',
		'&curren;' => '&#164;',
		'&yen;' => '&#165;',
		'&brvbar;' => '&#166;',
		'&brkbar;' => '&#166;',
		'&sect;' => '&#167;',
		'&uml;' => '&#168;',
		'&die;' => '&#168;',
		'&copy;' => '&#169;',
		'&ordf;' => '&#170;',
		'&laquo;' => '&#171;',
		'&not;' => '&#172;',
		'&shy;' => '&#173;',
		'&reg;' => '&#174;',
		'&macr;' => '&#175;',
		'&hibar;' => '&#175;',
		'&deg;' => '&#176;',
		'&plusmn;' => '&#177;',
		'&sup2;' => '&#178;',
		'&sup3;' => '&#179;',
		'&acute;' => '&#180;',
		'&micro;' => '&#181;',
		'&para;' => '&#182;',
		'&middot;' => '&#183;',
		'&cedil;' => '&#184;',
		'&sup1;' => '&#185;',
		'&ordm;' => '&#186;',
		'&raquo;' => '&#187;',
		'&frac14;' => '&#188;',
		'&frac12;' => '&#189;',
		'&frac34;' => '&#190;',
		'&iquest;' => '&#191;',
		'&Agrave;' => '&#192;',
		'&Aacute;' => '&#193;',
		'&Acirc;' => '&#194;',
		'&Atilde;' => '&#195;',
		'&Auml;' => '&#196;',
		'&Aring;' => '&#197;',
		'&AElig;' => '&#198;',
		'&Ccedil;' => '&#199;',
		'&Egrave;' => '&#200;',
		'&Eacute;' => '&#201;',
		'&Ecirc;' => '&#202;',
		'&Euml;' => '&#203;',
		'&Igrave;' => '&#204;',
		'&Iacute;' => '&#205;',
		'&Icirc;' => '&#206;',
		'&Iuml;' => '&#207;',
		'&ETH;' => '&#208;',
		'&Ntilde;' => '&#209;',
		'&Ograve;' => '&#210;',
		'&Oacute;' => '&#211;',
		'&Ocirc;' => '&#212;',
		'&Otilde;' => '&#213;',
		'&Ouml;' => '&#214;',
		'&times;' => '&#215;',
		'&Oslash;' => '&#216;',
		'&Ugrave;' => '&#217;',
		'&Uacute;' => '&#218;',
		'&Ucirc;' => '&#219;',
		'&Uuml;' => '&#220;',
		'&Yacute;' => '&#221;',
		'&THORN;' => '&#222;',
		'&szlig;' => '&#223;',
		'&agrave;' => '&#224;',
		'&aacute;' => '&#225;',
		'&acirc;' => '&#226;',
		'&atilde;' => '&#227;',
		'&auml;' => '&#228;',
		'&aring;' => '&#229;',
		'&aelig;' => '&#230;',
		'&ccedil;' => '&#231;',
		'&egrave;' => '&#232;',
		'&eacute;' => '&#233;',
		'&ecirc;' => '&#234;',
		'&euml;' => '&#235;',
		'&igrave;' => '&#236;',
		'&iacute;' => '&#237;',
		'&icirc;' => '&#238;',
		'&iuml;' => '&#239;',
		'&eth;' => '&#240;',
		'&ntilde;' => '&#241;',
		'&ograve;' => '&#242;',
		'&oacute;' => '&#243;',
		'&ocirc;' => '&#244;',
		'&otilde;' => '&#245;',
		'&ouml;' => '&#246;',
		'&divide;' => '&#247;',
		'&oslash;' => '&#248;',
		'&ugrave;' => '&#249;',
		'&uacute;' => '&#250;',
		'&ucirc;' => '&#251;',
		'&uuml;' => '&#252;',
		'&yacute;' => '&#253;',
		'&thorn;' => '&#254;',
		'&yuml;' => '&#255;',
		'&OElig;' => '&#338;',
		'&oelig;' => '&#339;',
		'&Scaron;' => '&#352;',
		'&scaron;' => '&#353;',
		'&Yuml;' => '&#376;',
		'&fnof;' => '&#402;',
		'&circ;' => '&#710;',
		'&tilde;' => '&#732;',
		'&Alpha;' => '&#913;',
		'&Beta;' => '&#914;',
		'&Gamma;' => '&#915;',
		'&Delta;' => '&#916;',
		'&Epsilon;' => '&#917;',
		'&Zeta;' => '&#918;',
		'&Eta;' => '&#919;',
		'&Theta;' => '&#920;',
		'&Iota;' => '&#921;',
		'&Kappa;' => '&#922;',
		'&Lambda;' => '&#923;',
		'&Mu;' => '&#924;',
		'&Nu;' => '&#925;',
		'&Xi;' => '&#926;',
		'&Omicron;' => '&#927;',
		'&Pi;' => '&#928;',
		'&Rho;' => '&#929;',
		'&Sigma;' => '&#931;',
		'&Tau;' => '&#932;',
		'&Upsilon;' => '&#933;',
		'&Phi;' => '&#934;',
		'&Chi;' => '&#935;',
		'&Psi;' => '&#936;',
		'&Omega;' => '&#937;',
		'&alpha;' => '&#945;',
		'&beta;' => '&#946;',
		'&gamma;' => '&#947;',
		'&delta;' => '&#948;',
		'&epsilon;' => '&#949;',
		'&zeta;' => '&#950;',
		'&eta;' => '&#951;',
		'&theta;' => '&#952;',
		'&iota;' => '&#953;',
		'&kappa;' => '&#954;',
		'&lambda;' => '&#955;',
		'&mu;' => '&#956;',
		'&nu;' => '&#957;',
		'&xi;' => '&#958;',
		'&omicron;' => '&#959;',
		'&pi;' => '&#960;',
		'&rho;' => '&#961;',
		'&sigmaf;' => '&#962;',
		'&sigma;' => '&#963;',
		'&tau;' => '&#964;',
		'&upsilon;' => '&#965;',
		'&phi;' => '&#966;',
		'&chi;' => '&#967;',
		'&psi;' => '&#968;',
		'&omega;' => '&#969;',
		'&thetasym;' => '&#977;',
		'&upsih;' => '&#978;',
		'&piv;' => '&#982;',
		'&ensp;' => '&#8194;',
		'&emsp;' => '&#8195;',
		'&thinsp;' => '&#8201;',
		'&zwnj;' => '&#8204;',
		'&zwj;' => '&#8205;',
		'&lrm;' => '&#8206;',
		'&rlm;' => '&#8207;',
		'&ndash;' => '&#8211;',
		'&mdash;' => '&#8212;',
		'&lsquo;' => '&#8216;',
		'&rsquo;' => '&#8217;',
		'&sbquo;' => '&#8218;',
		'&ldquo;' => '&#8220;',
		'&rdquo;' => '&#8221;',
		'&bdquo;' => '&#8222;',
		'&dagger;' => '&#8224;',
		'&Dagger;' => '&#8225;',
		'&bull;' => '&#8226;',
		'&hellip;' => '&#8230;',
		'&permil;' => '&#8240;',
		'&prime;' => '&#8242;',
		'&Prime;' => '&#8243;',
		'&lsaquo;' => '&#8249;',
		'&rsaquo;' => '&#8250;',
		'&oline;' => '&#8254;',
		'&frasl;' => '&#8260;',
		'&euro;' => '&#8364;',
		'&image;' => '&#8465;',
		'&weierp;' => '&#8472;',
		'&real;' => '&#8476;',
		'&trade;' => '&#8482;',
		'&alefsym;' => '&#8501;',
		'&crarr;' => '&#8629;',
		'&lArr;' => '&#8656;',
		'&uArr;' => '&#8657;',
		'&rArr;' => '&#8658;',
		'&dArr;' => '&#8659;',
		'&hArr;' => '&#8660;',
		'&forall;' => '&#8704;',
		'&part;' => '&#8706;',
		'&exist;' => '&#8707;',
		'&empty;' => '&#8709;',
		'&nabla;' => '&#8711;',
		'&isin;' => '&#8712;',
		'&notin;' => '&#8713;',
		'&ni;' => '&#8715;',
		'&prod;' => '&#8719;',
		'&sum;' => '&#8721;',
		'&minus;' => '&#8722;',
		'&lowast;' => '&#8727;',
		'&radic;' => '&#8730;',
		'&prop;' => '&#8733;',
		'&infin;' => '&#8734;',
		'&ang;' => '&#8736;',
		'&and;' => '&#8743;',
		'&or;' => '&#8744;',
		'&cap;' => '&#8745;',
		'&cup;' => '&#8746;',
		'&int;' => '&#8747;',
		'&there4;' => '&#8756;',
		'&sim;' => '&#8764;',
		'&cong;' => '&#8773;',
		'&asymp;' => '&#8776;',
		'&ne;' => '&#8800;',
		'&equiv;' => '&#8801;',
		'&le;' => '&#8804;',
		'&ge;' => '&#8805;',
		'&sub;' => '&#8834;',
		'&sup;' => '&#8835;',
		'&nsub;' => '&#8836;',
		'&sube;' => '&#8838;',
		'&supe;' => '&#8839;',
		'&oplus;' => '&#8853;',
		'&otimes;' => '&#8855;',
		'&perp;' => '&#8869;',
		'&sdot;' => '&#8901;',
		'&lceil;' => '&#8968;',
		'&rceil;' => '&#8969;',
		'&lfloor;' => '&#8970;',
		'&rfloor;' => '&#8971;',
		'&lang;' => '&#9001;',
		'&rang;' => '&#9002;',
		'&larr;' => '&#8592;',
		'&uarr;' => '&#8593;',
		'&rarr;' => '&#8594;',
		'&darr;' => '&#8595;',
		'&harr;' => '&#8596;',
		'&loz;' => '&#9674;',
		'&spades;' => '&#9824;',
		'&clubs;' => '&#9827;',
		'&hearts;' => '&#9829;',
		'&diams;' => '&#9830;'
	);

	return str_replace( array_keys($to_ncr), array_values($to_ncr), $text );
}

function strip_all_tags($string, $remove_breaks = false) {
	$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
	$string = strip_tags($string);

	if ( $remove_breaks )
		$string = preg_replace('/[\r\n\t ]+/', ' ', $string);

	return trim($string);
}


/**
 * Convert full URL paths to absolute paths.
 *
 * Removes the http or https protocols and the domain. Keeps the path '/' at the
 * beginning, so it isn't a true relative link, but from the web root base.
 *
 * @since 2.1.0
 *
 * @param string $link Full URL path.
 * @return string Absolute path.
 */
function url_make_link_relative( $link ) {
	return preg_replace( '|https?://[^/]+(/.*)|i', '$1', $link );
}

/**
 * Convert lone less than signs.
 *
 * KSES already converts lone greater than signs.
 *
 * @uses wp_pre_kses_less_than_callback in the callback function.
 * @since 2.3.0
 *
 * @param string $text Text to be converted.
 * @return string Converted text.
 */
function lone_pre_kses_less_than( $text ) {
	return preg_replace_callback('%<[^>]*?((?=<)|>|$)%', 'lone_pre_kses_less_than_callback', $text);
}

/**
 * Callback function used by preg_replace.
 *
 * @uses esc_html to format the $matches text.
 * @since 2.3.0
 *
 * @param array $matches Populated by matches to preg_replace.
 * @return string The text returned after esc_html if needed.
 */
function lone_pre_kses_less_than_callback( $matches ) {
	if ( false === strpos($matches[0], '>') )
		return esc_html($matches[0]);
	return $matches[0];
}

function url_basename( $path, $suffix = '' ) {
	return urldecode( basename( str_replace( '%2F', '/', urlencode( $path ) ), $suffix ) );
}

function urlencode_deep($value) {
	$value = is_array($value) ? array_map('urlencode_deep', $value) : urlencode($value);
	return $value;
}

function esc_text( $text ) {
	$safe_text = htmlspecialchars( $text, ENT_QUOTES );
	return $safe_text;
}

function esc_text_deep($value) {
	if ( is_array($value) ) {
		$value = array_map('esc_text_deep', $value);
	} 
	elseif ( is_object($value) ) {
		$vars = get_object_vars( $value );
		foreach ($vars as $key=>$data) {
			$value->{$key} = esc_text_deep( $data );
		}
	} 
	else {
		$value = esc_text($value);
	}

	return $value;
}

function tag_escape($tag_name) {
	$safe_tag = strtolower( preg_replace('/[^a-zA-Z_:]/', '', $tag_name) );
	return $safe_tag;
}

function like_escape($text) {
	return str_replace(array("%", "_"), array("\\%", "\\_"), $text);
}

function nostrip_parse_str( $string, &$array ) {
	parse_str( $string, $array );
	if ( get_magic_quotes_gpc() )
		$array = stripslashes_deep( $array );
}

function stripslashes_deep($value) {
	if ( is_array($value) ) {
		$value = array_map('stripslashes_deep', $value);
	} 
	elseif ( is_object($value) ) {
		$vars = get_object_vars( $value );
		foreach ($vars as $key=>$data) {
			$value->{$key} = stripslashes_deep( $data );
		}
	} 
	else {
		$value = stripslashes($value);
	}
	return $value;
}

function add_magic_quotes( $array ) {
	foreach ( (array) $array as $k => $v ) {
		if ( is_array( $v ) ) {
			$array[$k] = add_magic_quotes( $v );
		} 
		else {
			$array[$k] = addslashes( $v );
		}
	}
	return $array;
}

function set_magic_quotes() {
	// If already slashed, strip.
	if ( get_magic_quotes_gpc() ) {
		$_GET    = stripslashes_deep( $_GET    );
		$_POST   = stripslashes_deep( $_POST   );
		$_COOKIE = stripslashes_deep( $_COOKIE );
	}

	// Escape with wpdb.
	$_GET    = add_magic_quotes( $_GET    );
	$_POST   = add_magic_quotes( $_POST   );
	$_COOKIE = add_magic_quotes( $_COOKIE );
	$_SERVER = add_magic_quotes( $_SERVER );

	// Force REQUEST to be GET + POST.
	$_REQUEST = array_merge( $_GET, $_POST );
}

function byte2size($val,$unit=""){
    $val = trim($val);
    $unit = strtolower($unit);
	switch($unit){
        case 'g':
            $val /= 1024;
        case 'm':
            $val /= 1024;
        case 'k':
            $val /= 1024;
		default:
			$val /= 1;
	}

	return intval($val);
}

function size2byte($val) {
    $val = trim($val);
    $unit = strtolower($val{strlen($val)-1});
    switch($unit){
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
		default:
			$val *= 1;
    }

    return $val;
}

function objectToArray( $object )
{
	if( !is_object( $object ) && !is_array( $object ) )
	{
		return $object;
	}
	if( is_object( $object ) )
	{
		$object = get_object_vars( $object );
	}
	return array_map( 'objectToArray', $object );
}

function getcapacity(){
	$memory_limit=size2byte(ini_get("memory_limit"));
	$post_max_size=size2byte(ini_get("post_max_size"));
	$upload_max_filesize=size2byte(ini_get("upload_max_filesize"));
	$filecapacity=$post_max_size>$upload_max_filesize?$upload_max_filesize:$post_max_size;
	if($memory_limit>1) $filecapacity=$memory_limit>$filecapacity?$filecapacity:$memory_limit;
	return $filecapacity;
}

function do_upload_file($data){
	global $db;
	$result=false;
	$message="";
	$uploadfile = "";

	if(!empty($data)){
		$uploadfile_name=$data["name"];
		$uploadfile_type=$data["type"];
		$uploadfile_tmp_name=$data["tmp_name"];
		$uploadfile_size=$data["size"];
		$uploadfile_error=$data["error"];
		switch($uploadfile_error){
		case UPLOAD_ERR_OK:
			$uploadfile = $uploadfile_name;
			$result=move_uploaded_file($uploadfile_tmp_name, FILEDIR.$uploadfile);
			$message=$result?"":"Berkas {$uploadfile_name} tidak dapat diuanggah.";
			break;
		case UPLOAD_ERR_INI_SIZE:
			$message="Ukuran berkas melebihi kapasitas unggah berkas: (".reg_byte2size(getcapacity(),"M")."MB).";
			break;
		case UPLOAD_ERR_FORM_SIZE:
			$message="Ukuran berkas melebihi batas maksimum ketentuan: (".reg_byte2size($uploadfile_size,"M")."MB).";
			break;
		case UPLOAD_ERR_PARTIAL:
			$message="Berkas-berkas hanya dapat diunggah sebagian.";
			break;
		case UPLOAD_ERR_NO_FILE:
			$message="Tidak ada berkas yang diunggah.";
			break;
		case UPLOAD_ERR_NO_TMP_DIR:
			$message="Folder sementara tidak ada.";
			break;
		case UPLOAD_ERR_CANT_WRITE:
			$message="Gagal menyimpan berkas ke media penyimpanan.";
			break;
		case UPLOAD_ERR_EXTENSION:
			$message="Unggah berkas dihentikan oleh extension.";
			break;
		default:
			$message="Kesalahan unggah pada berkas {$uploadfile_name}.";
		}
		if(!$result) $db['default']['messages']=$message;
	}
	return $uploadfile;
}

function init_request_agent()
{
	// Simple browser detection
	global $agent, $is_Lynx, $is_Gecko, $is_IE, $is_WinIE, $is_MacIE, $is_Opera, $is_NS4, $is_Safari, $is_Chrome, $is_Firefox, $is_Netscape, $is_RV, $is_Iphone, $current_Browser;
	if(isset($_SERVER['HTTP_USER_AGENT']))
	if (strpos($_SERVER['HTTP_USER_AGENT'], 'Lynx') !== false) {
		$is_Lynx = true; $current_Browser = "Lynx";
	} elseif ( strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'chrome') !== false ) {
		$is_Chrome = true; $current_Browser = "Chrome";
	} elseif ( strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'safari') !== false ) {
		$is_Safari = true; $current_Browser = "Safari";
	} elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Gecko') !== false) {
		$is_Gecko = true; $current_Browser = "Gecko";
	} elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'Win') !== false) {
		$is_WinIE = true; $current_Browser = "WinMSIE";
	} elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'Mac') !== false) {
		$is_MacIE = true; $current_Browser = "MacMSIE";
	} elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') !== false) {
		$is_Opera = true; $current_Browser = "Opera";
	} elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Firefox') !== false) {
		$is_Firefox = true; $current_Browser = "Firefox";
	} elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Netscape') !== false) {
		$is_Netscape = true; $current_Browser = "Netscape";
	} elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'rv:1.7') !== false) {
		$is_RV = true; $current_Browser = "RV";
	} elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Nav') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'Mozilla/4.') !== false) {
		$is_NS4 = true; $current_Browser = "Navigator Mozilla/4.";
	}

	if(isset($_SERVER['HTTP_USER_AGENT']))
	if ( $is_Safari && strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'mobile') !== false )
		$is_Iphone = true; $current_Browser = "Mobile";

	$is_IE = ( $is_MacIE || $is_WinIE );

}

function Ymd2dMY($strDate,$separator="-"){
	$result=false;
	if($strDate){
		$aDate=explode($separator,$strDate);
		$result=date("d".$separator."M".$separator."Y",mktime(0,0,0,$aDate[1],$aDate[2],$aDate[0]));
	}
	return $result;
}

function indonesian_date ($timestamp = '', $date_format = 'j M Y', $suffix = '') {
	if (trim ($timestamp) == ''){
			$timestamp = time ();
	}
	elseif (!ctype_digit ($timestamp)){
		$timestamp = strtotime ($timestamp);
	}
	# remove S (st,nd,rd,th) there are no such things in indonesia :p
	$date_format = preg_replace ("/S/", "", $date_format);
	$pattern = array (
		'/Mon[^day]/','/Tue[^sday]/','/Wed[^nesday]/','/Thu[^rsday]/',
		'/Fri[^day]/','/Sat[^urday]/','/Sun[^day]/','/Monday/','/Tuesday/',
		'/Wednesday/','/Thursday/','/Friday/','/Saturday/','/Sunday/',
		'/Jan[^uary]/','/Feb[^ruary]/','/Mar[^ch]/','/Apr[^il]/','/May/',
		'/Jun[^e]/','/Jul[^y]/','/Aug[^ust]/','/Sep[^tember]/','/Oct[^ober]/',
		'/Nov[^ember]/','/Dec[^ember]/','/January/','/February/','/March/',
		'/April/','/June/','/July/','/August/','/September/','/October/',
		'/November/','/December/',
	);
	$replace = array ( 'Sen','Sel','Rab','Kam','Jum','Sab','Min',
		'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu',
		'Jan ','Feb ','Mar ','Apr ','Mei ','Jun ','Jul ','Ags ','Sep ','Okt ','Nov ','Des ',
		'Januari','Februari','Maret','April','Juni','Juli','Agustus','Sepember',
		'Oktober','November','Desember',
	);
	$date = date ($date_format, $timestamp);
	$date = preg_replace ($pattern, $replace, $date);
	$date = "{$date} {$suffix}";
	return $date;
} 


function reg_setfields($selectedfield){
	$o_alias=array();
	$f_select="*";
	if($selectedfield){
		$f_column=array();
		$o_alias=$selectedfield;
		foreach($o_alias as $key=>$val) $f_column[]="`$key` as `$val`";
		if(count($f_column)>0) $f_select=implode(", ",$f_column);
	}
	return $f_select;
}

function getPageList($rows,$page_num=1,$per_page=10,$page_interval=5){
	$result=array("rows"=>0,"pages"=>0,"pagenum"=>0,"perpage"=>0,"step"=>1,"startrow"=>0,"endrow"=>0,"prev"=>0,"next"=>0,"link"=>0);
	if($rows>0){
		$page_count = ceil($rows/$per_page); //Jumlah halaman data
		if($page_num<1) $page_num=1;                                       //nilai awal halaman
		if($page_num>$page_count) $page_num=$page_count;
		if($page_interval<1) $page_interval=1;
		$page_prev=$page_num-1<1?$page_num:$page_num-1;							
		$page_next=$page_num+1>$page_count?$page_num:$page_num+1;				
		
		$page_start = ($page_num - 1) * $per_page; //Offset baris data
		if($page_start<0) $page_start=0;
		$page_end = $page_start + $per_page; //Jumlah baris data
		
		$page_skip=($page_num>$page_interval?$page_num-$page_interval:0); //Jumlah tambahan baris
		$page_step=($page_count<$page_interval?$page_count:$page_interval); //Jumlah Interval

		$page_link=array();
		for($i=1;$i<=$page_step;$i++) $page_link[$i-1]=$page_skip+$i;
		
		$result["rows"]=$rows;
		$result["pages"]=$page_count;
		$result["pagenum"]=$page_num;
		$result["perpage"]=$per_page;
		$result["step"]=$page_interval;
		$result["startrow"]=$page_start;
		$result["endrow"]=$page_end;
		$result["prev"]=$page_prev;
		$result["next"]=$page_next;
		$result["link"]=$page_link;
	}
	return $result;
}

function setPageLink($pager,$plink="",$request,$prefix="top"){
	global $is_IE;

	if(!is_array($pager)) return;
	if(empty($plink)) return;
	if(empty($pager["rows"])) return;
	$alink=$plink;
	?>
	<form name="<?php echo $prefix;?>_page-form" action="<?php echo $plink;?>" method="post" >
	<?php if($is_IE && $prefix=="top"){?>
	<input type="submit" name="buttonpage" class="nav-button" style="width:0px;" value=""/>
	<?php }?>
	<button type="submit" name="firstpage" class="nav-button" value="1" >&laquo;</button>
	<button type="submit" name="prevpage" class="nav-button" value="<?php echo $pager["prev"];?>" >&lsaquo;</button>
	<?php if($pager["step"]>1){ ?>

	<?php foreach($pager["link"] as $page_link){$selected=$pager["pagenum"]==$page_link?"selected":"unselected";?>
	<button type="submit" name="pagenum" class="nav-button <?php echo $selected;?>"  value="<?php echo $page_link;?>"  ><?php echo $page_link;?></button>
	<?php }?>
	
	<?php }else{?>
	<input type="text" name="pagenum" class="pagenumber" size="1" value="<?php echo $pager["pagenum"];?>" onkeypress="return letterNumber(event,11)" onchange="this.form.submit();" />
	<input type="text" name="pages" class="pagenumbers" size="1" disabled="disabled" value="<?php echo "/ ".$pager["pages"];?>"/>
	<?php }?>
	
	<button type="submit" name="nextpage" class="nav-button"  value="<?php echo $pager["next"];?>">&rsaquo;</button>
	<button type="submit" name="lastpage" class="nav-button"  value="<?php echo $pager["pages"];?>" >&raquo;</button>
	
	<input type="hidden" name="page[first]" value="1"/>
	<input type="hidden" name="page[prev]" value="<?php echo $pager["prev"];?>"/>
	<input type="hidden" name="page[next]" value="<?php echo $pager["next"];?>"/>
	<input type="hidden" name="page[last]" value="<?php echo $pager["pages"];?>"/>
	
	<?php foreach($request as $key=>$val){?>
	<input type="hidden" name="filter[data][<?php echo $key;?>]" value="<?php echo $val;?>"/>
	<?php }?>
	</form>
	<?php	
}


/**
 * This file is part of the array_column library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Ben Ramsey (http://benramsey.com)
 * @license http://opensource.org/licenses/MIT MIT
 */

if (!function_exists('array_column')) {
    /**
     * Returns the values from a single column of the input array, identified by
     * the $columnKey.
     *
     * Optionally, you may provide an $indexKey to index the values in the returned
     * array by the values from the $indexKey column in the input array.
     *
     * @param array $input A multi-dimensional array (record set) from which to pull
     *                     a column of values.
     * @param mixed $columnKey The column of values to return. This value may be the
     *                         integer key of the column you wish to retrieve, or it
     *                         may be the string key name for an associative array.
     * @param mixed $indexKey (Optional.) The column to use as the index/keys for
     *                        the returned array. This value may be the integer key
     *                        of the column, or it may be the string key name.
     * @return array
     */
    function array_column($input = null, $columnKey = null, $indexKey = null){
        // Using func_get_args() in order to check for proper number of
        // parameters and trigger errors exactly as the built-in array_column()
        // does in PHP 5.5.
        $argc = func_num_args();
        $params = func_get_args();

        if ($argc < 2) {
            trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
            return null;
        }

        if (!is_array($params[0])) {
            trigger_error(
                'array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given',
                E_USER_WARNING
            );
            return null;
        }

        if (!is_int($params[1])
            && !is_float($params[1])
            && !is_string($params[1])
            && $params[1] !== null
            && !(is_object($params[1]) && method_exists($params[1], '__toString'))
        ) {
            trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
            return false;
        }

        if (isset($params[2])
            && !is_int($params[2])
            && !is_float($params[2])
            && !is_string($params[2])
            && !(is_object($params[2]) && method_exists($params[2], '__toString'))
        ) {
            trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
            return false;
        }

        $paramsInput = $params[0];
        $paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;

        $paramsIndexKey = null;
        if (isset($params[2])) {
            if (is_float($params[2]) || is_int($params[2])) {
                $paramsIndexKey = (int) $params[2];
            } else {
                $paramsIndexKey = (string) $params[2];
            }
        }

        $resultArray = array();

        foreach ($paramsInput as $row) {
            $key = $value = null;
            $keySet = $valueSet = false;

            if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                $keySet = true;
                $key = (string) $row[$paramsIndexKey];
            }

            if ($paramsColumnKey === null) {
                $valueSet = true;
                $value = $row;
            } elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                $valueSet = true;
                $value = $row[$paramsColumnKey];
            }

            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }

        }

        return $resultArray;
    }

}
