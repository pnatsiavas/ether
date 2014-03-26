<?
/* dblib.php (c) 2000 Ying Zhang (ying@zippydesign.com)
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
if (!isset($DB_DIE_ON_FAIL)) { $DB_DIE_ON_FAIL = true; }
if (!isset($DB_DEBUG)) { $DB_DEBUG = false; }

function db_maxval($id,$table,$dbh,$location="Local"){
   $query = 'SELECT MAX('.$id.') AS last_id FROM '.$table;
   $result = db_query($query,$dbh,"","","",$location);
   $result = db_fetch_array($result);
   return $result[last_id];
}
function backup_db($CFG,$outPath="",$onlyKB=false){
    $h=explode(":",$CFG->dbhost);
$host=$h[0];
$port=$h[1];  
  $today = getdate();
  $file1 = $CFG->dbname."_".$today['weekday']."_".$today['mday']."_".$today['month']."_".$today['year'].".sql";
  $file2 = $CFG->dbname."_".$CFG->admindbname."_".$today['weekday']."_".$today['mday']."_".$today['month']."_".$today['year'].".sql";
  $new_file = "Backup.zip";
  $output=shell_exec("del sql\\*.zip");
 
if ($onlyKB){
    $codm="\"".$CFG->mysql."mysqldump.exe\" -h$host -P$port -u ".$CFG->dbuser." -p".$CFG->dbpass." --skip-triggers --compact --no-create-info --hex-blob --complete-insert  ".$CFG->dbname." categories values_list answer_list enum_constraint num_constraint dependencies > \"./sql/".$file1."\"";
}else{
  $codm="\"".$CFG->mysql."mysqldump.exe\" -h$host -P$port -u ".$CFG->dbuser." -p".$CFG->dbpass." --hex-blob --routines  ".$CFG->dbname."  > \"./sql/".$file1."\"";
}
if ($GLOBALS["DB_DEBUG"]){
print "<br><p>$codm</p>".shell_exec($codm);
}else{  $output=shell_exec($codm);}
  $codm="\"".$CFG->mysql."mysqldump.exe \" -h$host -P$port -u ".$CFG->admindbuser." -p".$CFG->admindbpass." --hex-blob  --routines ".$CFG->admindbname."  > \"./sql/".$file2."\"";
if ($GLOBALS["DB_DEBUG"]){
print "<br><p>$codm</p>".shell_exec($codm);
}else{  $output=shell_exec($codm);}    
  $codm="\"".$CFG->z7."\" a ./sql/".$new_file." ./sql/".$file1." ./sql/".$file2;  
  if ($GLOBALS["DB_DEBUG"]){
print "<br><p>$codm</p>".shell_exec($codm);
}else{  $output=shell_exec($codm);
}
if (filesize("./sql/".$new_file)<1024){
    return false;
}
if ($outPath!=""){
    $where = $outPath.$CFG->projectshortname."_".$today['weekday']."_".$today['mday']."_".$today['month']."_".$today['year'].".zip";
   if (!copy("./sql/".$new_file, $where)) return false;
   $output=shell_exec("del sql\\*.zip"); //if you copied zip delete it
}else{
}
 $output=shell_exec("del sql\\*.sql");//delete sql
    
  return true;
}


function db_connect($dbhost, $dbname, $dbuser, $dbpass) {
/* connect to the database $dbname on $dbhost with the user/password pair
 * $dbuser and $dbpass. */
$link = mysqli_init();
if (!$link) {
    die('mysqli_init failed');
}

if (!mysqli_options($link, MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT = 1')) {
    die('Setting MYSQLI_INIT_COMMAND failed');
}

if (!mysqli_options($link, MYSQLI_OPT_CONNECT_TIMEOUT, 5)) {
    die('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
}

if (!mysqli_real_connect($link, $dbhost, $dbuser, $dbpass,$dbname)) {
    die('Connect Error (' . mysqli_connect_errno() . ') '
            . mysqli_connect_error());
}

	

	mysqli_query($link,"SET NAMES 'UTF8';");
mysqli_query($link,"SET lower_case_table_names=1;");
	return $link;
}

function db_disconnect() {
/* disconnect from the database, we normally don't have to call this function
 * because PHP will handle it */

	mysqli_close();
}

/*
KK Log
*/
//19 04 2010
function db_log($query,$executedto){
	//$temp = $_SERVER['DOCUMENT_ROOT']."\\pk\\errorlog\\";
	//$default_folder = str_replace("/","\\",$temp);	
	$file_name = "./errorlog/querylog.html";//$default_folder."querylog.html";
	$file_handler = fopen($file_name,"a");
	$serializedPost = serialize($_POST);
	$message = "<br><i>executed at: ".date('l jS \of F Y h:i:s A')." details: ".$executedto."</i><br><b>".$query."  </b>".$serializedPost."<br>";	
	fwrite($file_handler,$message);
	fclose($file_handler);
}
/*
END OF KK Log
*/
function LowTblNames($str, $token_symbols) {
/*HELPER FUNCTION FOR PORT TO LINUX*/
 
 $word = strtok($str, $token_symbols);
    while (false !== $word) {
        // do something here...
		$dot=strpos($word,".");
		if ($dot>0){
		
		//print "<br>Left:";
		$left=strtolower(substr($word,0,$dot));
		print $_SERVER["PHP_SELF"]."   ".$left."<br>";
		//print "<br>Right:";
		 $right=substr($word,$dot);
		
		$res.=$token_symbols.$left.$right;
		}else{
		$res.=$token_symbols.$word;
		}
        $word = strtok($token_symbols);
    }
	return substr($res,1);
}
function db_query($query, $link_id,$debug=false, $die_on_debug=true, $silent=false,$executedto="Local") {
/* run the query $query against the current database.  if $debug is true, then
 * we will just display the query on screen.  if $die_on_debug is true, and
 * $debug is true, then we will stop the script after printing he debug message,
 * otherwise we will run the query.  if $silent is true then we will surpress
 * all error messages and return false, otherwise we will print out that a database error has
 * occurred */
  if ($link_id!="mlks"){
	
      	
        
	if ($GLOBALS['DB_DEBUG']) {
		echo "<pre>" . htmlspecialchars($query) . "(".$executedto.")</pre>";
if ($die_on_debug&&$GLOBALS['DB_DIE_ON_DEBUG']) die;
	
	}
/*
$query=(substr($query,0,stripos($query,"FROM"))).strtolower(substr($query,stripos($query,"FROM")));
$query=LowTblNames($query,",");
*/
	$qid = mysqli_query($link_id,$query);


	if (! $qid && ! $silent) {
		if ($GLOBALS['DB_DEBUG']||$debug) {
			echo "<h2>Can't execute query</h2>";
			echo "<pre>" . htmlspecialchars($query) . "</pre>";
			echo "<p><b>MySQL Error</b>: ", mysqli_error($link_id);
		} else {
			$file=fopen("./errorlog/mysqlerrors.html","ab");
			fwrite($file,"<p>user ".$GLOBALS['USER']["user"]["firstname"]." ".$GLOBALS['USER']["user"]["lastname"]." from ".$GLOBALS['USER']["user"]["us_cc_id"]." at ".date('r')."</p>");
			fwrite($file,"<h2>Can't execute query</h2>"."<pre>" . htmlspecialchars($query) . "</pre>"."<p><b>MySQL Error</b>: ".mysqli_error($link_id));
			$serializedPost = serialize($_POST);
	$res = date('l jS \of F Y h:i:s A');
	
	$message = "<br><i>executed at: ".$res." details: ".$executedto."</i><br><b>".$query."  </b>".$serializedPost."<br>";	
	fwrite($file,$message);
			fclose($file);
			echo "<h2>Database error encountered</h2>";
		}

		if (($GLOBALS['DB_DIE_ON_FAIL'])&&(!$silent)) {
			echo "<p>Oops, problem at :</p><p>  $executedto </p>";

			db_log($query,$executedto);
			die();
		}
	}elseif(! $qid && $silent){
            return $qid;
        }
	//db_log($query,$executedto);
        if ($debug) {
		echo "<pre>" . htmlspecialchars($query) . "(".$executedto.")</pre>";
        }
	return $qid;
 }
 //db_log($query,$executedto);
 return "aaa";
}
function db_multi_query($query, $link_id,$debug=false, $die_on_debug=true, $silent=false,$executedto="Local") {
/* run the query $query against the current database.  if $debug is true, then
 * we will just display the query on screen.  if $die_on_debug is true, and
 * $debug is true, then we will stop the script after printing he debug message,
 * otherwise we will run the query.  if $silent is true then we will surpress
 * all error messages and return false, otherwise we will print out that a database error has
 * occurred */
  if ($link_id!="mlks"){
	
        
	if ($GLOBALS['DB_DEBUG']||$debug) {
		echo "<pre>" . htmlspecialchars($query) . "(".$executedto.")</pre>";
if ($die_on_debug&&$GLOBALS['DB_DIE_ON_DEBUG']) die;
	
	}
/*
$query=(substr($query,0,stripos($query,"FROM"))).strtolower(substr($query,stripos($query,"FROM")));
$query=LowTblNames($query,",");
*/
        if (mysqli_multi_query($link_id,$query)) { 
    $i = 0; 
    do { 
        $i++; 
    } while ($res[]=mysqli_next_result($link_id)); 
} 
if (mysqli_errno($link_id)) { 
    echo "Batch execution prematurely ended on statement $i.\n"; 
    var_dump(mysqli_error($link_id)); 
} 
  	
 //db_log($query,$executedto);
 return $res;
  }
}

function db_querym($query, $i,$link_id, $link_id2="" , $debug=false, $die_on_debug=true, $silent=false) {
/* run the query $query against the  database of link 1 and link 2.query array i array index.
 * prefer linkid for local db and link 2 for central db. query will be executed in link 2 first if exists
 * if $debug is true, then
 * we will just display the query on screen.  if $die_on_debug is true, and
 * $debug is true, then we will stop the script after printing he debug message,
 * otherwise we will run the query.  if $silent is true then we will surpress
 * all error messages, otherwise we will print out that a database error has
 * occurred */

	

	if ($debug) {
		echo "<pre>" . htmlspecialchars($query[$i]) . "</pre>";

		if ($die_on_debug) die;
	}

	if ($link_id2!=""){
	$qid = db_query($query[$i],$link_id2);}

	$qid = db_query($query[$i],$link_id,"","","","Central");

	return $qid;
}

function db_fetch_array($qid) {
/* grab the next row from the query result identifier $qid, and return it
 * as an associative array.  if there are no more results, return FALSE */
if (($qid)&&($qid!="aaa")){
	return mysqli_fetch_array($qid);
}
return $row=array();
}

function db_fetch_assoc($qid) {
/* grab the next row from the query result identifier $qid, and return it
 * as an associative array.  if there are no more results, return FALSE */
if (($qid)&&($qid!="aaa")){
	return mysqli_fetch_assoc($qid);
}
return $row=array();
}
function db_fetch_row($qid) {
/* grab the next row from the query result identifier $qid, and return it
 * as an array.  if there are no more results, return FALSE */

	return mysqli_fetch_row($qid);
}

function db_fetch_object($qid) {
/* grab the next row from the query result identifier $qid, and return it
 * as an object.  if there are no more results, return FALSE */

	return mysqli_fetch_object($qid);
}

function db_num_rows($qid) {
/* return the number of records (rows) returned from the SELECT query with
 * the query result identifier $qid. */
if ($qid){
	return mysqli_num_rows($qid);}else {return 0;}
}

function db_affected_rows() {
/* return the number of rows affected by the last INSERT, UPDATE, or DELETE
 * query */

	return mysqli_affected_rows();
}

function db_insert_id($link) {
/* if you just INSERTed a new row into a table with an autonumber, call this
 * function to give you the ID of the new autonumber value */

	return mysqli_insert_id($link);
}

function db_free_result($qid) {
/* free up the resources used by the query result identifier $qid */

	mysqli_free_result($qid);
}

function db_num_fields($qid) {
/* return the number of fields returned from the SELECT query with the
 * identifier $qid */

	return mysqli_num_fields($qid);
}

function db_field_name($qid, $fieldno) {
/* return the name of the field number $fieldno returned from the SELECT query
 * with the identifier $qid */

	return mysqli_fetch_field_direct($qid, $fieldno);
}

function db_data_seek($qid, $row) {
/* move the database cursor to row $row on the SELECT query with the identifier
 * $qid */

	if (db_num_rows($qid)) { return mysqli_data_seek($qid, $row); }
}
function db_trans_start($dbh) {
/* start transaction for specific handler
*/
db_query("SET FOREIGN_KEY_CHECKS = 1;",$dbh,"","","","Disabling foreign key checks");  
db_query("SET autocommit=0;",$dbh,"","","","Setting auto commit to 0");
db_query("START TRANSACTION;",$dbh,"","","","Starting transaction");

}
function db_trans_stop($dbh) {
/*commit transaction for specific handler
*/
db_query("COMMIT;",$dbh,"","","","Committing transaction");
db_query("SET autocommit=1;",$dbh,"","","","Setting auto commit to 1");
db_query("SET FOREIGN_KEY_CHECKS = 1;",$dbh,"","","","Enabling foreign key checks");
}
function db_rollback($dbh) {
/*commit transaction for specific handler
*/
db_query("ROLLBACK;",$dbh,"","","","Rolling back transaction");
db_query("SET autocommit=1;",$dbh,"","","","Setting auto commit to 1");
}

//generic helper function to make dblib myslqli agnostic
function db_list_fields($dbh,$tblname){
  
$res=db_query("SHOW COLUMNS FROM {$tblname}",$dbh);
if ($res){
  
    return $res;
}else{
  
    return false;
}
}
function db_field_len($result,$field_nr){
  
$f=mysqli_fetch_field_direct($result, $field_nr);
return $f->max_length;

}

function db_real_escape_string($qstring){
    global $admin_dbh;
return mysqli_real_escape_string($admin_dbh,$qstring);
}
function db_error($dbh){
return mysqli_error($dbh);
}
function db_field_flags($res,$other){
 return   mysqli_fetch_fields($res);
    
}
function db_select_db($dbh,$db){
    
 return   mysqli_select_db($dbh,$db);
    
}

?>