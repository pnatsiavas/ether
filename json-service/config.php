<?php
// check ellinika
//***************************ΠΙΠΑΒΙΡ************************************
/*
 * Configuration options
 */
class obj {};

$CFG = new obj;
$CFG->template="templatestyle_rel_teal";
/* administration centre phenotyping database configuration */
$CFG->dbhost = "127.0.0.1:3306";//"127.0.0.1:3307";
$CFG->dbname = "fp";
$CFG->dbuser = "root";
$CFG->dbpass = "ps!p";


$CFG->projectcodeid="RF";
// $CFG->projectcodebloodid="BLOO";
$CFG->projectfullname="REST SERVICE";
$CFG->projectshortname="PV";
$CFG->wwwroot     = ".";
$CFG->dirroot     = dirname(__FILE__);
$CFG->libdir      = "lib";
$CFG->imagedir    = "$CFG->libdir/images";
$CFG->bannerdir   = "$CFG->imagedir/banners";
$CFG->version     = "beta";
$CFG->sessionname = "lomiprotocol";
$CFG->validationscript="lib/jsvalbk.js";
$CFG->dateformat='dmy';
$CFG->dateseperator='-';



$CFG->mysql = "c:\\MySQL\\bin\\";
$CFG->z7 = "c:\\Program Files\\7-Zip\\7z.exe";
$CFG->defaultBackupPath="e:\\pipavir\\";


$GLOBALS['DB_DEBUG']=false; //now also prints $_POST
$GLOBALS['DB_DIE_ON_DEBUG']=false;
$GLOBALS['DB_DIE_ON_FAIL']=true;

/* Set locale to greek */
setlocale(LC_ALL, 'en');
date_default_timezone_set('Europe/Athens');
//To enable multiple contacts that can be manipulated in list of forms
?>
