<?php


ini_set("session.name", $CFG->sessionname);
@session_start();


/* initialize the USER object if necessary */
if (! isset($_SESSION["USER"])) {
	$_SESSION["USER"] = array();
}

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 3600)) {
    // last request was more than 30 minutes ago
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

$USER = &$_SESSION["USER"];
?>
