<?
function setdefault(&$var, $default="") {
/* if $var is undefined, set it to $default.  otherwise leave it alone */

	if (! isset($var)) {
		$var = $default;
	}
}

function nvl(&$var, $default="") {
/* if $var is undefined, return $default, otherwise return $var */

	return isset($var) ? $var : $default;
}

function evl(&$var, $default="") {
/* if $var is empty, return $default, otherwise return $var */

	return empty($var) ? $var : $default;
}

function ov(&$var) {
/* returns $var with the HTML characters (like "<", ">", etc.) properly quoted,
 * or if $var is undefined, will return an empty string.  note this function
 * must be called with a variable, for normal strings or functions use o() */

	return o(nvl($var));
}

function pv(&$var) {
/* prints $var with the HTML characters (like "<", ">", etc.) properly quoted,
 * or if $var is undefined, will print an empty string.  note this function
 * must be called with a variable, for normal strings or functions use p() */

	p(nvl($var));
}

function o($var) {
/* returns $var with HTML characters (like "<", ">", etc.) properly quoted,
 * or if $var is empty, will return an empty string. */

	return empty($var) ? "" : htmlSpecialChars(stripslashes($var));
}

function p($var) {
/* prints $var with HTML characters (like "<", ">", etc.) properly quoted,
 * or if $var is empty, will print an empty string. */

	echo o($var);
}

function jstring($var) {
/* returns string that is quoted for javascript */

	return addslashes($var);
}

function db_query_loop($query, $prefix, $suffix, $found_str, $default="") {
/* this is an internal function and normally isn't called by the user.  it
 * loops through the results of a select query $query and prints HTML
 * around it, for use by things like listboxes and radio selections
 *
 * NOTE: this function uses dblib.php */

	$output = "";
	$result = db_query($query);
	while (list($val, $label) = db_fetch_row($result)) {
		if (is_array($default))
			$selected = empty($default[$val]) ? "" : $found_str;
		else
			$selected = $val == $default ? $found_str : "";

		$output .= "$prefix value='$val' $selected>$label$suffix";
	}

	return $output;
}

function db_listbox($query, $default="", $suffix="\n") {
/* generate the <option> statements for a <select> listbox, based on the
 * results of a SELECT query ($query).  any results that match $default
 * are pre-selected, $default can be a string or an array in the case of
 * multi-select listboxes.  $suffix is printed at the end of each <option>
 * statement, and normally is just a line break */

	return db_query_loop($query, "<option", $suffix, "selected", $default);
}
function get_referer() {
/* returns the URL of the HTTP_REFERER, less the querystring portion */

	return strip_querystring(nvl($_SERVER["HTTP_REFERER"]));
}

function me() {
/* returns the name of the current script, without the querystring portion.
 * this function is necessary because PHP_SELF and REQUEST_URI and PATH_INFO
 * return different things depending on a lot of things like your OS, Web
 * server, and the way PHP is compiled (ie. as a CGI, module, ISAPI, etc.) */

	if (isset($_SERVER["REQUEST_URI"])) {
		$me = $_SERVER["REQUEST_URI"];

	} elseif ($_SERVER["PATH_INFO"]) {
		$me = $_SERVER["PATH_INFO"];

	} elseif ($_SERVER["PHP_SELF"]) {
		$me = $_SERVER["PHP_SELF"];
	}

	return strip_querystring($me);
}

function qualified_me() {
/* like me() but returns a fully URL */

	$protocol = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") ? "https://" : "http://";
	$url_prefix = "$protocol$_SERVER[HTTP_HOST]";
	return $url_prefix . me();
}

function match_referer($good_referer = "") {
/* returns true if the referer is the same as the good_referer.  If
 * good_refer is not specified, use qualified_me as the good_referer */

	if ($good_referer == "") { $good_referer = qualified_me(); }
	return $good_referer == get_referer();
}

/* login.php (c) 2000 Ying Zhang (ying@zippydesign.com)
 *
 * TERMS OF USAGE:
 * This file was written and developed by Ying Zhang (ying@zippydesign.com)
 * for educational and demonstration purposes only.  You are hereby granted the
 * rights to use, modify, and redistribute this file as you like.  The only
 * requirement is that you must retain this notice, without modifications, at
 * the top of your source code.  No warranties or guarantees are expressed or
 * implied. DO NOT use this code in a production environment without
 * understanding the limitations and weaknesses pretaining to or caused by the
 * use of these scripts, directly or indirectly. USE AT YOUR OWN RISK!
 */

/******************************************************************************
 * MAIN
 *****************************************************************************/
function is_logged_in() {
/* this function will return true if the user has logged in.  a user is logged
 * in if the $USER["user"] is set (by the login.php page) and also if the
 * remote IP address matches what we saved in the session ($USER["ip"])
 * from login.php -- this is not a robust or secure check by any means, but it
 * will do for now */

	global $USER;

	return isset($USER["user"])
		&& !empty($USER["user"]["username"])
		&& nvl($USER["ip"]) == $_SERVER["REMOTE_ADDR"];
}

function require_login() {
/* this function checks to see if the user is logged in.  if not, it will show
 * the login screen before allowing the user to continue */

	global $CFG, $USER;

	if (! is_logged_in()) {
		$USER["wantsurl"] = qualified_me();
		redirect("$CFG->wwwroot/login.php");
	}
}

function require_priv($priv) {
/* this function checks to see if the user has the privilege $priv.  if not,
 * it will display an Insufficient Privileges page and stop */

	global $USER;


	if ($USER["user"]["priv"] < $priv) {
		$USER["wantsurl"] = qualified_me();
	redirect("insufficient_priviledges.php");
		
	}
}

function require_privs_only($priv) {
/* this function checks to see if the user has the privilege inside $priv ARRAY.  if not,
 * it will display an Insufficient Privileges page and stop */

	global $USER;

        
	if (array_search($USER["user"]["priv"], $priv)===FALSE) {
		$USER["wantsurl"] = qualified_me();
	redirect("insufficient_priviledges.php");		
	}
}
function has_priv($priv) {
/* returns true if the user has the privilege $priv */

	global $USER;

	return $USER["user"]["priv"] == $priv;
} 
function verify_login($username, $password) {
/* verify the username and password.  if it is a valid login, return an array
 * with the username, firstname, lastname, and email address of the user */

	if (empty($username) || empty($password)) return false;

	$qid = mysql_query("SELECT username, firstname, lastname,priv,pass,us_cc_id
	FROM users
where username like '$username' and pass like '$password'
	");

	return mysql_fetch_array($qid);
}

?>