<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
ver: 1.99y
*/

require_once SYSPATH.'config.php';
require_once SYSPATH.'common.php';
require_once SYSPATH.'captcha.php';
require_once SYSPATH.'models.php';
require_once SYSPATH.'controllers.php';
require_once SYSPATH.'notifications.php';
require_once LIBDIR.'mantra-ui/mantra-class.php';
require_once LIBDIR.'calendar/classes/tc_calendar.php';

logClose();
if(!headers_sent()){	
	nocache_headers();
	send_nosniff_header();
	send_frame_options_header();
	send_web_header();
}
init_database();
logOpen();
require_once APP_VIEW.'dashboard.php';


