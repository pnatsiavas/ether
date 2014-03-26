<?php
// check ellinika
//***************************ΠΙΠΑΒΙΡ************************************
/* gglib.php (c) 2006 Kilintzis Vassilis - Antoniadis Antonis billyk18278@gmail.com
*
* TERMS OF USAGE:
* This file was written and developed by Kilintzis Vassilis - Antoniadis Antonis.
* You are hereby granted the rights to use and modify. The only
* requirement is that you must retain this notice, without modifications, at
* the top of your source code.  No warranties or guarantees are expressed or
* implied. DO NOT use this code in a production environment without
* understanding the limitations and weaknesses pretaining to or caused by the
* use of these scripts, directly or indirectly. USE AT YOUR OWN RISK!
*/
function VLID2CAPTION($vlid,$dbh){
//helper function 
$row=db_fetch_assoc(db_query("Select vl_caption from values_list where vl_id like '{$vlid}';",$dbh,"","","","Switching vl id to vl_caption"));
return $row["vl_caption"];
}
function GetResponseData($pcid,$vlid,$dbh){
    // pcid, vlid in comma separated list mysql .eg. AKO2,DSDS
$res=db_query("Select VL_CARDINALITY from values_list where vl_id like '{$vlid}' and vl_type not like 'TITLE' and vl_id not in (select vl_id from values_list where vl_type like 'TEXTAREA' and vl_showspecial=3) order by vl_askorder;",$dbh,"","","","Get cardinality of question {$vlid}");    
while ($row=db_fetch_assoc($res)){

if ($row["VL_CARDINALITY"]==2){

$vlidList="`".$vlid."-RE`".",`".$vlid."-LE`";
								}elseif($row["VL_CARDINALITY"]==1){							
								$vlidList=$vlid;
}
}



if ($vlidList!=""){
    
$query="Select {$vlidList} from short_queries_view where pc_id={$pcid};";
$res=db_query($query,$dbh,"","","","Selecting category data");
$row=db_fetch_assoc($res);
 if ($row[$vlid]!=null){
     return $row[$vlid];
 }else{
     return false;
 }   
}
return false;
}
function GetCategoryData($pcid,$cid,$dbh,$settings=""){
//Returns the data of the specified category for the specified contact as an associative array doews not return drawing
//Requires short_queries_view
$res=db_query("Select vl_id,vl_cardinality from values_list where vl_category={$cid} and vl_type not like 'TITLE' and vl_id not in (select vl_id from values_list where vl_type like 'TEXTAREA' and vl_showspecial=3) order by vl_askorder;",$dbh,"","","","Get category DATA");
$vlidList=" ";
while ($row=db_fetch_assoc($res)){

if ($row["vl_cardinality"]==2){

$vlidList.="`".$row["vl_id"]."-RE`".",`".$row["vl_id"]."-LE`,";
								}elseif($row["vl_cardinality"]==1){							
								$vlidList.="`".$row["vl_id"]."`,";
}
}
if ($vlidList!=" "){
$vlidList=substr($vlidList, 0, -1);
$query="Select {$vlidList} from short_queries_view where pc_id={$pcid};";
$res=db_query($query,$dbh,"","","","Selecting category data");
$row=db_fetch_assoc($res);
$result=array();
foreach($row as $key=>$value){
$caption=VLID2CAPTION($key,$dbh);
if (($value!="empty")&&(trim($value)!="")){
array_push_associative($result,array($caption=>$value));
}

}
}else {
$result=array();
}
return $result;
}
function CreateID($dbh,$CFG,$cc){
if ($CFG->PatIdAutoInc){
$prefixlen=strlen($CFG->projectcodeid);
if ($CFG->UseCCidAsPrefix){$prefixlen+=strlen($cc);}// 2 digits for CC id
$queryid="SELECT  max(right(PC_PATIENT_ID,CHARACTER_LENGTH(PC_PATIENT_ID)-{$prefixlen})) as maxid FROM patient_contacts where PC_PATIENT_ID in (SELECT PC_PATIENT_ID from patient_contacts where Left(PC_PATIENT_ID,".strlen($cc).")='".$cc."');";
//$queryid="SELECT max(right(PC_PATIENT_ID,CHARACTER_LENGTH(PC_PATIENT_ID)-".$prefixlen.")) as maxid FROM patient_contacts ;";this produces codes with autoinc independent of centre
$quidmaxid=db_query($queryid,$dbh,"","","","Getting max id");
$newidrow=db_fetch_array($quidmaxid);
$newid=$CFG->projectcodeid.str_pad((intval($newidrow["maxid"])+1),$CFG->IdLength-strlen($CFG->projectcodeid),'0',STR_PAD_LEFT);
if ($CFG->UseCCidAsPrefix){$newid=$cc.$newid;}
}
return $newid;
}
function Visit2PCID($pat_id,$visit,$dbh){
$res=db_query("Select PC_ID from patient_contacts t  where t.pc_patient_id like '{$pat_id}'  order by pc_date;",$dbh,"","","","Finding PC ID from Visit");
$v=0;$pcid=false;
while ($row=db_fetch_array($res)) {
$v++;
if ($v==$visit){$pcid=$row["PC_ID"];break;}
}
return $pcid;
}
function Visit2PCIDMySQLFunction($pat_id,$visit,$dbh){
$res=db_query("Select VISIT2PCID('{$pat_id}',{$visit});",$dbh,"","","","Finding PC ID from Visit");
$row=db_fetch_array($res);

return $row[0];
}

function PCID2VISIT($pc_id,$dbh){
  if ($pc_id==""){ return false;}
$res=db_query("Select PC_ID from patient_contacts t  where t.pc_patient_id in (Select pc_patient_id from patient_contacts where pc_id={$pc_id} ) order by pc_date;",$dbh,"","","","Finding Visit");
$v=0;$visit=0;
while ($row=db_fetch_array($res)) {
$v++;
if ($row["PC_ID"]==$pc_id){$visit=$v;break;}
}
return $visit;



}
function PCID2VISITMySQLFunction($pc_id,$dbh){
$res=db_query("SELECT PCID2VISIT({$pc_id});",$dbh,"","","","Finding Visit");

$row=db_fetch_array($res);
return $row[0];



}

function ParseFloat($floatString){ 
    $LocaleInfo = localeconv();
	
if (strpos($floatString,',')!==false){	

	if (($LocaleInfo["mon_decimal_point"]==',')&&($LocaleInfo["decimal_point"]==',')){
    $floatstring=floatval($floatString);
	$floatString = str_replace($LocaleInfo["mon_thousands_sep"] , "", $floatString); 
    $floatString = str_replace($LocaleInfo["mon_decimal_point"] , ".", $floatString); 
	return $floatString;
	}else{
    return floatval($floatString); 
	}
	}else{
	return $floatString;
	}
	
} 

function generate_schema($TBL2XMLsettings,$table_id)
{
switch ($TBL2XMLsettings['add_category_info']){
case 0:
$schema='<?xml version="1.0" encoding="utf-8"?>
<xs:schema id="'.$table_id.'" xmlns="" xmlns:xs="http://www.w3.org/2001/XMLSchema" >
  <xs:element name="'.$table_id.'" >
    <xs:complexType>
      <xs:choice minOccurs="0" maxOccurs="unbounded">
        <xs:element name="'.$TBL2XMLsettings['TR'].'">
          <xs:complexType>
                 <xs:sequence minOccurs="0" maxOccurs="unbounded">              
                          <xs:element name="'.$TBL2XMLsettings['TD'].'" nillable="true" minOccurs="0" maxOccurs="unbounded" type="xs:string" />
                          <xs:element name="'.$TBL2XMLsettings['VAL'].'" nillable="true" minOccurs="0" maxOccurs="unbounded" type="xs:string"/>                     
                  </xs:sequence>
           </xs:complexType>       
        </xs:element>
      </xs:choice>
    </xs:complexType>
  </xs:element>
</xs:schema>';
break;
case 1:
$schema='<?xml version="1.0" encoding="utf-8"?>
<xs:schema id="'.$table_id.'" xmlns="" xmlns:xs="http://www.w3.org/2001/XMLSchema" >
  <xs:element name="'.$table_id.'" >
    <xs:complexType>
      <xs:choice minOccurs="0" maxOccurs="unbounded">
        <xs:element name="'.$TBL2XMLsettings['TR'].'">
          <xs:complexType>
            <xs:sequence>
              <xs:element name="'.$TBL2XMLsettings['add_category_info_tags']['category_tag'].'" minOccurs="0" maxOccurs="unbounded">
                <xs:complexType>
                  <xs:sequence minOccurs="0" maxOccurs="unbounded">                  
                          <xs:element name="'.$TBL2XMLsettings['TD'].'" nillable="true" minOccurs="0" maxOccurs="unbounded" type="xs:string" />
                          <xs:element name="'.$TBL2XMLsettings['VAL'].'" nillable="true" minOccurs="0" maxOccurs="unbounded" type="xs:string"/>                                            
                  </xs:sequence>
                  <xs:attribute name="'.$TBL2XMLsettings['add_category_info_tags']['caption_attr'].'" type="xs:string" />
                  <xs:attribute name="'.$TBL2XMLsettings['add_category_info_tags']['termin_attr'].'" type="xs:string" />
                  <xs:attribute name="'.$TBL2XMLsettings['add_category_info_tags']['code_attr'].'" type="xs:string" />
                </xs:complexType>
              </xs:element>
            </xs:sequence>
          </xs:complexType>
        </xs:element>
      </xs:choice>
    </xs:complexType>
  </xs:element>
</xs:schema>';
break;
default:
$schema='<?xml version="1.0" encoding="utf-8"?>
<xs:schema id="'.$table_id.'" xmlns="" xmlns:xs="http://www.w3.org/2001/XMLSchema" >
  <xs:element name="'.$table_id.'" >
    <xs:complexType>
      <xs:choice minOccurs="0" maxOccurs="unbounded">
        <xs:element name="'.$TBL2XMLsettings['TR'].'">
          <xs:complexType>
            <xs:sequence>
              <xs:element name="'.$TBL2XMLsettings['add_category_info_tags']['category_tag'].'" minOccurs="0" maxOccurs="unbounded">
                <xs:complexType>
                  <xs:sequence minOccurs="0" maxOccurs="unbounded">
                    <xs:element name="'.$TBL2XMLsettings['add_category_info_tags']['section_tag'].'" minOccurs="0" maxOccurs="unbounded">
                      <xs:complexType>
                        <xs:sequence minOccurs="0" maxOccurs="unbounded">
                          <xs:element name="'.$TBL2XMLsettings['TD'].'" nillable="true" minOccurs="0" maxOccurs="unbounded" type="xs:string" />
                          <xs:element name="'.$TBL2XMLsettings['VAL'].'" nillable="true" minOccurs="0" maxOccurs="unbounded" type="xs:string"/>                     
                        </xs:sequence>
                        <xs:attribute name="'.$TBL2XMLsettings['add_category_info_tags']['caption_attr'].'" type="xs:string" />
                        <xs:attribute name="'.$TBL2XMLsettings['add_category_info_tags']['termin_attr'].'" type="xs:string" />
                        <xs:attribute name="'.$TBL2XMLsettings['add_category_info_tags']['code_attr'].'" type="xs:string" />
                      </xs:complexType>
                    </xs:element>
                  </xs:sequence>
                  <xs:attribute name="'.$TBL2XMLsettings['add_category_info_tags']['caption_attr'].'" type="xs:string" />
                  <xs:attribute name="'.$TBL2XMLsettings['add_category_info_tags']['termin_attr'].'" type="xs:string" />
                  <xs:attribute name="'.$TBL2XMLsettings['add_category_info_tags']['code_attr'].'" type="xs:string" />
                </xs:complexType>
              </xs:element>
            </xs:sequence>
          </xs:complexType>
        </xs:element>
      </xs:choice>
    </xs:complexType>
  </xs:element>
</xs:schema>';
}
return $schema;
}
function report2XML($htmlsrc,$TBL2XMLsettings,$outputfile)
{


//billyk 1/4/2011

// TABLE MUST HAVE in firsrt row HEADERS th
// $htmlsrc <table ....</table> as string
// $TBL2XMLsettings['mode'] 0(simple) to use th as node names.1 define node names  
//!!!!!!!!!!!!!!! THESE AFFECT THE XSD!!!!!!!!!!!!!!!!!
// $TBL2XMLsettings['TR'] the name of the element of each row
// $TBL2XMLsettings['TD'] the name of the element of each collumn
// $TBL2XMLsettings['VAL'] the name of the element of the value
//table id attribute is used as root element, if null 'root' is the root node.
// $TBL2XMLsettings['add_category_info'] (0,1,2) -> 0 default do not addd, 1 add one level for category , 2 add level for caterogy and section element for 
// $TBL2XMLsettings['add_category_info_tags'] array ('category_tag'->'Questionnaire','section_tag'->'SECTION','caption_attr'->'DESCRIPTION',
// ,'termin_attr'->'TERMINOLOGY','code_attr'->'CODE') attributes will be used for both section and category.
//table id attribute is used as root element, if null 'root' is the root node.
  


//check if settings are set
if (!isset($TBL2XMLsettings['TR'])){
$TBL2XMLsettings['TR']='SUBJECT';
}
if (!isset($TBL2XMLsettings['TD'])){
$TBL2XMLsettings['TD']='QUESTION';
}
if (!isset($TBL2XMLsettings['VAL'])){
$TBL2XMLsettings['VAL']='ANSWER';
}
if (!isset($TBL2XMLsettings['mode'])){
$TBL2XMLsettings['mode']=1;
}
if (!isset($TBL2XMLsettings['add_category_info'])){
$TBL2XMLsettings['add_category_info']=1;
}
if ((!isset($TBL2XMLsettings['add_category_info_tags']))||(sizeof($TBL2XMLsettings['add_category_info_tags'])<5)){
$TBL2XMLsettings['add_category_info_tags']=array ('category_tag'=>'CATEGORY','section_tag'=>'SECTION',
  'caption_attr'=>'DESCRIPTION','termin_attr'=>'TERMINOLOGY','code_attr'=>'CODE');
}

$xmlout = new DOMDocument('1.0', 'utf-8');

$dom = new DOMDocument;
$dom->preserveWhiteSpace = false;
@$dom->loadHTML('<meta http-equiv="content-type" content="text/html; charset=utf-8">'.$htmlsrc);

$nodelist=$dom->getElementsByTagName('table');
$rootelem=$nodelist->item(0)->getAttributeNode('id')->nodeValue;
if ($rootelem==""){$rootelem='root';}

$nodelist=$dom->getElementsByTagName('th');
$nodecnt=$nodelist->length;
//store headers in array
if ($nodecnt>0){
for ($idx = 0; $idx < $nodecnt; $idx++) {
if (($TBL2XMLsettings['add_category_info']>0)&&(isset($TBL2XMLsettings['add_category_info_tags']))){
$category_attr[]=$nodelist->item($idx)->getAttributeNode('category')->nodeValue;
$section_attr[]=$nodelist->item($idx)->getAttributeNode('section')->nodeValue;
$s_termin_attr[]=$nodelist->item($idx)->getAttributeNode('section_termin')->nodeValue;
$c_termin_attr[]=$nodelist->item($idx)->getAttributeNode('category_termin')->nodeValue;
$s_code_attr[]=$nodelist->item($idx)->getAttributeNode('section_code')->nodeValue;
$c_code_attr[]=$nodelist->item($idx)->getAttributeNode('category_code')->nodeValue;
}
    $html=$nodelist->item($idx)->nodeValue;
if ($TBL2XMLsettings['mode']==1){
	$headers[]=$html;
	}else{
	$headers[]=preg_replace('/[^a-zA-Z0-9]/', '', $html);
	}

}

}

//creating xml out root element
$rootnode = $xmlout->createElement($rootelem);
$newnode = $xmlout->appendChild($rootnode);
//read data
$nodelist=$dom->getElementsByTagName('tr');
$nodecnt=$nodelist->length;
//store headers in array

if ($nodecnt>1){
for ($idx = 1; $idx < $nodecnt; $idx++) {
$category_tag_counter=0;
$section_tag_counter=0;

$personnode = $xmlout->createElement($TBL2XMLsettings['TR']);
$rootnode->appendChild($personnode);


	$celllist=$nodelist->item($idx)->getElementsByTagName('td');
$numofcells=$celllist->length;

//iterate in TD and create data nodes
for ($cellid=0;$cellid<$numofcells;$cellid++){
if (($TBL2XMLsettings['add_category_info']>0)&&(isset($TBL2XMLsettings['add_category_info_tags']))){ //if semantic is needed
if (($category_tag_counter==0)||($category_attr[$category_tag_counter]!=$category_attr[$category_tag_counter-1])){
$categorynode = $xmlout->createElement($TBL2XMLsettings['add_category_info_tags']['category_tag']);
$personnode->appendChild($categorynode);
$categorynode->setAttributeNode(new DOMAttr($TBL2XMLsettings['add_category_info_tags']['caption_attr'], $category_attr[$cellid]));
$categorynode->setAttributeNode(new DOMAttr($TBL2XMLsettings['add_category_info_tags']['termin_attr'], $c_termin_attr[$cellid]));
$categorynode->setAttributeNode(new DOMAttr($TBL2XMLsettings['add_category_info_tags']['code_attr'], $c_code_attr[$cellid]));
}
$category_tag_counter++;

if (($TBL2XMLsettings['add_category_info']==2)&&(isset($TBL2XMLsettings['add_category_info_tags']))){//if per section needed

if (($section_tag_counter==0)||($section_attr[$section_tag_counter]!=$section_attr[$section_tag_counter-1])){
$sectionnode = $xmlout->createElement($TBL2XMLsettings['add_category_info_tags']['section_tag']);
$categorynode->appendChild($sectionnode);
$sectionnode->setAttributeNode(new DOMAttr($TBL2XMLsettings['add_category_info_tags']['caption_attr'], $section_attr[$cellid]));
$sectionnode->setAttributeNode(new DOMAttr($TBL2XMLsettings['add_category_info_tags']['termin_attr'], $s_termin_attr[$cellid]));
$sectionnode->setAttributeNode(new DOMAttr($TBL2XMLsettings['add_category_info_tags']['code_attr'], $s_code_attr[$cellid]));
}
$section_tag_counter++;
}else{
$sectionnode=$categorynode;
}

}else{
$sectionnode=$personnode;
}

if ($TBL2XMLsettings['mode']==1){
$qnode = $xmlout->createElement($TBL2XMLsettings['TD'],$headers[$cellid]);
$sectionnode->appendChild($qnode);
	$cellnode = $xmlout->createElement($TBL2XMLsettings['VAL'],($celllist->item($cellid)->nodeValue));
	$sectionnode->appendChild($cellnode);
	}else{
$cellnode = $xmlout->createElement($headers[$cellid],($celllist->item($cellid)->nodeValue));
$sectionnode->appendChild($cellnode);
	}



	}
}

}
$result=array('XML'=>$xmlout->saveXML(),'XSD'=>generate_schema($TBL2XMLsettings,$rootelem));
if (isset($outputfile)){
file_put_contents($outputfile.".xls",$htmlsrc,LOCK_EX);
file_put_contents($outputfile.".xml",$result['XML'],LOCK_EX);
file_put_contents($outputfile.".xsd",$result['XSD'],LOCK_EX);
}
return($result);	

}
function remove_tildes($string){
	//α,ε,η,ι,ο,υ
	$output = str_replace("ά","α",$string);
	$output = str_replace("έ","ε",$output);
	$output = str_replace("ή","η",$output);
	$output = str_replace("ί","ι",$output);
	$output = str_replace("ό","ο",$output);
	$output = str_replace("ύ","υ",$output);
	$output = str_replace("Ά","Α",$output);
	$output = str_replace("Έ","Ε",$output);
	$output = str_replace("Ή","Η",$output);
	$output = str_replace("Ί","Ι",$output);
	$output = str_replace("Ό","Ο",$output);
	$output = str_replace("Ύ","Υ",$output);
	return $output;
}

function report_table_html($dbh,$settings,$vl_IDs=null,$pc_IDs=null){
//billyk 2/10/2013
//using VIEW
//various chanches for custom report
//kkostopo
//09 10 2010 updated so that 'show_caption_for_enum' has also option 2  

	/* $dbh database handler
		$settings assoc array labels 
			'show_pcid'->(0,1)   show contact id column /den paizei
			'show_caption_headers'->(0,1,2) on 1 show values_list captions on 0 show vl_id, on 2 add the previous title to the caption header
			'show_caption_for_enum'->(0,1,2) on true 1  show the description of the answer, on false 0 the matching al_id(ea_value) and on 2 show equi_map.em_equi (does not work with view)
			'add_autoinc'->(0,1,2) on true add a column with auto increment integer,on 2 create link to first contact of patient.
			'patients_private_fields'-> if empty do not show any patient private fields, show fields in array e.x('PATIENT_ID','SURNAME','FORNAME','GENDER','CONFESSION','DOB')
			'classname'-> array with 0,1 or 3 string elements ('table_class','odd_row_class','even_row_class')
                        'all_if_no_vlid' -> (0,1) if 1 show all questions if no question is selected, otherwise (0) show only selected history
			'text_2_capitals'->(0,1,2) on 0 transform to lower_case, on 1 tranform to upper_case,on 2 as they are (only for private fields)
			'remove_tildes'->(0,1) on true remove tildes, on false texts as they are (only for private fields)
			'custom_column'->array ('caption','row1 data',row2 data ....) check with pc_IDs  length
			'custom_column_index'->int null or undefined->index=0
			'custom_column_attributes'->array('category'=>'xxx','section'=>'xxx', 'category_code'=>'xxx', 'category_termin'=>'xxx','section_code'=>'xxx','section_termin'='xxx') 
			'table_id' ->string, id attribute of html table tag default report_table
			'add_category_info' ->(0[default],1,2) 0 do not add, 1 add category descripton and previous title description as attribute 'category' and 'section' of th,2 add 'category_code' 'category_termin','section_code','section_termin'  attributes to th.
			'from_terminology' -> The final part of the terminology PURL leave empty for any.
			
	*/
	
/*
$time = microtime();
$time = explode(" ", $time); 
$time = $time[1]+ $time[0];
$time1 = $time;
*/

if ($settings['table_id']==""){$settings['table_id']="report_table";}
$resulthtml="";

$viewname='`short_queries_view`';$columns=0;
	//1) Validate vl_ids
$vl_types=array();	
$vl_specials=array();	
	$vl_ids_list="(";
	if ($vl_IDs!=null) {
		//validate all vlids
		foreach ($vl_IDs as $vlid){
			
			$query = "SELECT count(VL_ID),VL_CARDINALITY,VL_TYPE,VL_SHOWSPECIAL FROM values_list where values_list.VL_ID like '".$vlid."' and vl_type not like 'TITLE' group by vl_id";
			$res = db_query($query,$dbh,"","","","Validate VL_ID ".$vlid);
			$row = db_fetch_array($res);
			
                        if ($row===false) {
				$row["VL_CARDINALITY"]=1;
                                $row['VL_TYPE']='FREETEXT';
                                $row['VL_SHOWSPECIAL']=0;                               
			}
			
			if ($row["VL_CARDINALITY"]==1){
			$columns++;
			$vl_ids_list=$vl_ids_list."'".$vlid."',";
			$vl_types[]=$row['VL_TYPE'];
			$vl_specials[]=$row['VL_SHOWSPECIAL'];
			}elseif ($row["VL_CARDINALITY"]==2){
	$columns+=2;		
			$vl_ids_list=$vl_ids_list."'".$vlid."-RE',";
			$vl_ids_list=$vl_ids_list."'".$vlid."-LE',";
			$vl_types[]=$row['VL_TYPE'];
			$vl_types[]=$row['VL_TYPE'];
			$vl_specials[]=$row['VL_SHOWSPECIAL'];
			$vl_specials[]=$row['VL_SHOWSPECIAL'];
			}
			mysql_freeresult($res);
		}
	}elseif ($settings['all_if_no_vlid']) {
		$vl_IDs=array();
		
		$query="select VL_ID,vl_cardinality,VL_TYPE,VL_SHOWSPECIAL  from values_list";
		$res=db_query($query,$dbh,"","","","δεν δόθηκε vl_id και τα επιλέγουμε όλα");
		
		while ($row=db_fetch_array($res)) {
			array_push($vl_IDs,$row['VL_ID']);
			if ($row["vl_cardinality"]==1){
			$columns++;
			$vl_ids_list=$vl_ids_list."'".$vlid."',";
			$vl_types[]=$row['VL_TYPE'];
			}elseif ($row["vl_cardinality"]==2){
	$columns+=2;
			$vl_ids_list=$vl_ids_list."'".$vlid."-RE',";
			$vl_ids_list=$vl_ids_list."'".$vlid."-LE',";
			$vl_types[]=$row['VL_TYPE'];
			$vl_types[]=$row['VL_TYPE'];
			}
		}
		mysql_free_result($res);
	}
	$vl_ids_list=substr($vl_ids_list,0,strlen($vl_ids_list)-1).")";	

//2) Validate pc_ids
	$pc_id_list="(";
	if ($pc_IDs!=null) {
		foreach ($pc_IDs as $pc_id){
			$pc_id_list=$pc_id_list.$pc_id.",";
			$query = "select count(PC_ID) from patient_contacts  where  PC_ID=".$pc_id;
			
			$res = db_query($query,$dbh,"","","","Validate PC_ID ".$pcid);
			$row = db_fetch_array($res);
			
			if ($row['count(PC_ID)']!=1) {
				print "Το πεδίο '".$pc_id."' δεν είναι έγκυρο!<br>Παρακαλώ δοκιμάστε ξανά χωρίς τον κωδικό".$pc_id."<br>";
				die();
			}
			
			mysql_freeresult($res);
		}
	}else {
		$pc_IDs=array();
		$query="SELECT pc_id FROM patient_contacts";
		$res=db_query($query,$dbh,"","","","δεν δόθηκε pc_id και τα επιλέγουμε όλα");
		while ($row=db_fetch_array($res)) {
			array_push($pc_IDs,$row['pc_id']);
			$pc_id_list=$pc_id_list.$row['pc_id'].",";			
		}
		mysql_free_result($res);
	}
	$pc_id_list=substr($pc_id_list,0,strlen($pc_id_list)-1).")";	
//3)validate $settings
	if (!isset($settings['show_pcid'])) {
		$settings['show_pcid']=0;
	}
	if (!isset($settings['show_caption_headers'])) {
		$settings['show_caption_headers']=0;
	}
	if (!isset($settings['add_category_info'])) {
		$settings['add_category_info']=0;
	}
	if (!isset($settings['show_caption_for_enum'])) {
		$settings['show_caption_for_enum']=1;
	}
	if (!isset($settings['add_autoinc'])) {
		$settings['add_autoinc']=0;
	}	
	if (!isset($settings['text_2_capitals'])) {
		$settings['text_2_capitals']=2;
	}
	if (!isset($settings['remove_tildes'])) {
		$settings['remove_tildes']=0;
	}
	$total_columns = count($settings['patients_private_fields'])+$columns;
	if ($settings['show_pcid']==1) {$total_columns = $total_columns +1;}
	if ($settings['add_autoinc']>0) {$total_columns = $total_columns +1;}
	if (!isset($settings['custom_column_index'])) {
		$settings['custom_column_index']=1;
		$insert_custom_index = true;
	}elseif ($settings['custom_column_index']>$total_columns+1) {
			$settings['custom_column_index']=$total_columns+1;
			$insert_custom_index = true;
	}elseif ($settings['custom_column_index']<1) {
		$settings['custom_column_index']=1;
	}
	if (!isset($settings['custom_column'])) {
		$insert_custom_index=false;
	}elseif (count($settings['custom_column'])!=count($pc_IDs)+1 ) {//+1 gia to caption
			$correct=count($pc_IDs)+1;
			$resulthtml.= "Η στήλη που θέλετε να προσθέσετε έχει ".count($settings['custom_column'])." στοιχεία ενω θα έπρεπε να έχει ".$correct." και για αυτό δε θα ληφθεί υπόψη.";
			$insert_custom_index=false;
	}else {
		$insert_custom_index = true;
	}
	if (!isset($settings['custom_column_index'])) {
		$settings['custom_column_index']=0;
	}
	
	
	
/*den ta kano validate
			'patients_private_fields'-> if empty do not show any patient private fields, show fields in array e.x('PATIENT_ID','SURNAME','FORNAME','GENDER','CONFESSION','DOB')
			'classname'-> array with 0,1 or 3 string elements ('table_class','odd_row_class','even_row_class')
*/
//4)Create and Run queries
   //4a)Create queries
	$report_table_captions=array();
	$report_table_attributes=array();
	if($settings['add_autoinc']==1){array_push($report_table_captions,"N");
		if($settings['add_category_info']>0){
	$report_table_attributes[0]=" category=\"Auto Increment\" section=\"Auto Increment\" ";
											}
	}
	if($settings['add_autoinc']==2){array_push($report_table_captions,"View");
		if($settings['add_category_info']>0){
	$report_table_attributes[0]=" category=\"Auto Increment\" section=\"Auto Increment\" ";
											}
	}
	if($settings['show_pcid']==1){
		array_push($report_table_captions,"ID");
	}
	if (isset($settings['patients_private_fields'])) {
		foreach ($settings['patients_private_fields'] as $key=>$pat_prive) {
			array_push($report_table_captions,$key);
			$patients_private_attributes=" category=\"Personal Information\" section=\"Personal Information\" ";
			if($settings['add_category_info']==2){
			$patients_private_attributes.=" category_code=\"CL421205\" section_code=\"CL421205\" category_termin=\"http://purl.bioontology.org/ontology/NCIM\" section_termin=\"http://purl.bioontology.org/ontology/NCIM\"";
			}
		array_push($report_table_attributes,$patients_private_attributes);	
		}
	}
	foreach($vl_IDs as $vlid){
		if($settings['show_caption_headers']>0){
			$query="SELECT vl_caption,vl_cardinality,vl_category,vl_askorder FROM values_list where vl_id like '".$vlid."'";
			
			$res=db_query($query,$dbh,"","","","επιλογή του vl_caption για το vlid ".$vlid);
			$row=db_fetch_array($res);
                        
			$titletext="";
			
			if ($row==false){
                            $row['vl_caption']=$vlid;
                            $row["vl_cardinality"]=1;
                        }elseif($settings['show_caption_headers']==2){
			$titlequery = "select vl_id,vl_caption from values_list where vl_type like ('TITLE')   and vl_category={$row[2]} and vl_askorder in (SELECT max(vl_askorder) FROM values_list where vl_type like 'TITLE' and vl_askorder<{$row[3]} and vl_category={$row[2]} group by vl_type)order by vl_category,vl_askorder";
			$titleres=db_query($titlequery,$dbh,"","","","επιλογή του vl_caption για το vlid ".$vlid);
			$titlerow=db_fetch_array($titleres);
			$titletext="(".$titlerow['vl_caption'].")";
			}
			
			if ($row["vl_cardinality"]==1){
			array_push($report_table_captions,$titletext.$row['vl_caption']);//the captions
			}elseif ($row["vl_cardinality"]==2){
			array_push($report_table_captions,$titletext.$row['vl_caption']."-RE");//the captions
			array_push($report_table_captions,$titletext.$row['vl_caption']."-LE");//the captions
			}
			
		}else{
			$query="SELECT vl_id,vl_cardinality FROM values_list where vl_id like '".$vlid."'";
			$res=db_query($query,$dbh,"","","","επιλογή του vl_caption για το vlid ".$vlid);
			$row=db_fetch_array($res);
                        if ($row==false){
                            $row['vl_id']=$vlid;
                            $row["vl_cardinality"]=1;
                        }
			if ($row["vl_cardinality"]==1){
			array_push($report_table_captions,$row['vl_id']);//the captions
			}elseif ($row["vl_cardinality"]==2){
			array_push($report_table_captions,$row['vl_id']."-RE");//the captions
			array_push($report_table_captions,$row['vl_id']."-LE");//the captions
			}
			}
			//for category meta data
		if ($settings['from_terminology']!=""){
		$fromtermin=" se_terminology like '%{$settings['from_terminology']}' and ";
		}
		if($settings['add_category_info']>0){
		$categoryquery="SELECT se_code,se_terminology,se_nci_code,c_id,c_description,vl_cardinality,vl_askorder FROM categories join values_list on c_id=vl_category left outer join semantic s on c_id=se_c_id where {$fromtermin} vl_id like '".$vlid."' limit 1;";
		$secategoriesres=db_query($categoryquery,$dbh,"","","","Selection from category semantic for vlid ".$vlid);
		
			$se_c_row=db_fetch_array($secategoriesres);
//previous title			
		$titlequery = "SELECT se_code,se_terminology,vl_id,vl_caption from values_list left outer join semantic on se_vl_id=vl_id where {$fromtermin} vl_type like ('TITLE')   and vl_category={$se_c_row[3]} and vl_askorder in (SELECT max(vl_askorder) FROM values_list where vl_type like 'TITLE' and vl_askorder<{$se_c_row[6]} and vl_category={$se_c_row[3]} group by vl_type)order by vl_category,vl_askorder limit 1;";
			
			$titleres=db_query($titlequery,$dbh,"","","","Selection from semantic for vlid ".$vlid);
		
			$titlerow=db_fetch_array($titleres);
			$attributes=" category=\"{$se_c_row["c_description"]}\" section=\"{$titlerow["vl_caption"]}\" ";
		if($settings['add_category_info']==2){
		$attributes.=" category_code=\"{$se_c_row["se_code"]}\" section_code=\"{$titlerow["se_code"]}\" category_termin=\"{$se_c_row["se_terminology"]}\" section_termin=\"{$titlerow["se_terminology"]}\"";
		}
		
			if ($se_c_row["vl_cardinality"]==1){
			array_push($report_table_attributes,$attributes);//the attributes
			}elseif ($se_c_row["vl_cardinality"]==2){
			array_push($report_table_attributes,$attributes);//the attributes
			array_push($report_table_attributes,$attributes);//the attributes
			}
			
			
		
			}
	
	}
	//print_r($report_table_attributes);die();
	mysql_free_result($res);
	//4b)run queries
	//----------using view----------------------------------------
	$vlidlist=substr($vl_ids_list,0,-1);
	$vlidlist=str_replace("(","",$vlidlist);
	$vlidlist=str_replace("'","`",$vlidlist);
     
	//$query="select {$vlidlist} from {$viewname} where pc_id in ".$pc_id_list.";";
	
	//$viewdatares=db_query($query,$dbh,"","","","Getting data from view");
        //bk 24/9/13
        
	//$viewdatares = mysql_query($query) or die("A MySQL error has occurred.<br />Your Query: " . $query . "<br /> Error: (" . mysql_errno() . ") " . mysql_error());
	
	//---------using view----------------------------------------
	
	$private_data_array=array();//
	if (isset($settings['patients_private_fields'])) {
		$query="select pc_id,";
		foreach ($settings['patients_private_fields'] as $pat_prive) {
			$query=$query.$pat_prive.",";
		}
		$query=substr($query,0,strlen($query)-1)." from patients_private join patient_contacts on patient_contacts.pc_patient_id=patients_private.patient_id where patient_contacts.pc_id in ".$pc_id_list;
		$res=db_query($query,$dbh,"","","","Επιλογή προσωπικών στοιχείων για τα pc_id ".$pc_id_list);
		while ($row=db_fetch_array($res)) {
			$private_data_array[$row['pc_id']]=$row;
		}		
		mysql_free_result($res);
	}
	//$resulthtml.= "private data:<br><br>";print_r($private_data_array);	
	
	$patients_ids_array = null;
	if (isset($settings['show_pcid']) && $settings['show_pcid']==1) {
		$query="select pc_id,pc_patient_id from patient_contacts where pc_id in ".$pc_id_list;
		$res=db_query($query,$dbh,"","","","Επιλογή pc_patients_id");
		while ($row=db_fetch_array($res)) {
			$patients_ids_array[$row['pc_id']]=$row['pc_patient_id'];
		}
		mysql_free_result($res);
	}
	
//5)Manage data dislay

	$row_classname=array(true=>$settings['classname']['odd_row_class'],false=>$settings['classname']['even_row_class']);
	$class_row=false;	
	$resulthtml.= "<table id=\"{$settings['table_id']}\" class=\"".$settings['classname']['table_class']."\">";
	$resulthtml.= "\n<thead><tr>";

	$class_row=!$class_row;
	$arithmos_stilon=0;
	foreach ($report_table_captions as $caption) {
		$arithmos_stilon = $arithmos_stilon+1;
		if ($insert_custom_index==true && $arithmos_stilon==$settings['custom_column_index']) {
		if($settings['add_category_info']>0){
	$custom_column_attributes=" category=\"{$settings['custom_column_attributes']['category']}\" section=\"{$settings['custom_column_attributes']['section']}\" ";
	if($settings['add_category_info']==2){
		$custom_column_attributes.=" category_code=\"{$settings['custom_column_attributes']['category_code']}\" section_code=\"{$settings['custom_column_attributes']['section_code']}\" category_termin=\"{$settings['custom_column_attributes']['category_termin']}\" section_termin=\"{$settings['custom_column_attributes']['section_termin']}\"";
		}
											}
			$resulthtml.= "<th {$custom_column_attributes}>".$settings['custom_column'][0]."</th>";
			$arithmos_stilon=$arithmos_stilon+1;
		}		
		$resulthtml.= "<th ".$report_table_attributes[$arithmos_stilon-1].">".$caption."</th>";
	}
	$resulthtml.= "</tr></thead>\n<tfoot><tr>";
	IF($settings['add_autoinc']>0){$aas=1;$resulthtml.= "<th><input type=\"hidden\" name=\"search_col0\" value=\"Search\" class=\"search_init\" /></th>";}
	for ($aas;$aas<$arithmos_stilon;$aas++){
	$resulthtml.="<th><input type=\"text\" name=\"search_col{$aas}\" value=\"Search\" class=\"search_init\" /></th>\n";
	}
	$resulthtml.= "</tr></tfoot>\n<tbody>";
	$auxon_arithmos=0;
	foreach ($pc_IDs as $pc_id) {
		$auxon_arithmos=$auxon_arithmos+1;
		$arithmos_stilon=1;//restart counting
		if ($row_classname[$class_row]!="") {
			$resulthtml.= "\n<tr class=\"".$row_classname[$class_row]."\">";
		}else{
			$resulthtml.= "\n<tr>";
		}
		$class_row=!$class_row;
				
		if($settings['add_autoinc']==1){			
			if ($insert_custom_index==true && $arithmos_stilon==$settings['custom_column_index']) {
				$resulthtml.= "<td>".$settings['custom_column'][$auxon_arithmos]."</td>";
				$arithmos_stilon=$arithmos_stilon+1;
			}			
			$resulthtml.= "<td>".$auxon_arithmos."</td>";
			$arithmos_stilon = $arithmos_stilon +1;
		}		
		if($settings['add_autoinc']==2){			
			if ($insert_custom_index==true && $arithmos_stilon==$settings['custom_column_index']) {
				$resulthtml.= "<td>".$settings['custom_column'][$auxon_arithmos]."</td>";
				$arithmos_stilon=$arithmos_stilon+1;
			}			
			$resulthtml.= "<td><a href=\"listofforms.php?pc_id=".$pc_id."\"><img width=\"25\" src=\"template/images/Magnifying-Glass-icon-sm.png\"></a></td>";
			$arithmos_stilon = $arithmos_stilon +1;
		}
		if($settings['show_pcid']==1) {			
			if ($insert_custom_index==true && $arithmos_stilon==$settings['custom_column_index']) {
				$resulthtml.= "<td>".$settings['custom_column'][$auxon_arithmos]."</td>";
				$arithmos_stilon=$arithmos_stilon+1;
			}
			$resulthtml.= "<td>".$patients_ids_array[$pc_id]."</td>";
			$arithmos_stilon = $arithmos_stilon +1;
		}
		if (isset($settings['patients_private_fields'])) {
			foreach ($settings['patients_private_fields'] as $private_field) {				
				if ($insert_custom_index==true && $arithmos_stilon==$settings['custom_column_index']) {
					$resulthtml.= "<td>".$settings['custom_column'][$auxon_arithmos]."</td>";
					$arithmos_stilon=$arithmos_stilon+1;
				}
				$echome = $private_data_array[$pc_id][$private_field];
				if ($settings['text_2_capitals']==0) {
					$echome = mb_strtolower($echome,"utf-8");
				}elseif ($settings['text_2_capitals']==1){
					$echome = mb_strtoupper($echome,"utf-8");
				}
				if ($settings['remove_tildes']==1) {
					$echome = remove_tildes($echome);
				}
				$resulthtml.= "<td>".$echome."</td>";
				$arithmos_stilon = $arithmos_stilon +1;
			}			
		}
		//$viewdatarow=db_fetch_array($viewdatares);
	//	print_r($viewdatarow);//debug
	$vlidnow=strtok($vlidlist,',');
        
			 $vlidnow=str_replace('`','',$vlidnow);
        $viewdatarow[$vlidnow]=calculate_field($vlidnow, $pc_id, $dbh); //implementation without queries
           
      //  $viewdatarow[$vlidnow]=$vlidnow; //implementation without queries
    
		for ($cc=0;$cc<$columns;$cc++) { 
	
			if ($insert_custom_index==true && $arithmos_stilon==$settings['custom_column_index']) {
				$resulthtml.= "<td>".$settings['custom_column'][$auxon_arithmos]."</td>";
				$arithmos_stilon=$arithmos_stilon+1;
			}
			//print "<br> ".$vlidnow." ARI8.STHL:".$arithmos_stilon; //debug
			
		IF (($vl_types[$cc]=='BINARY')&&($viewdatarow[$vlidnow]!="")){



			$resulthtml.="<td><A HREF=\"showstoredbinary.php?id=".$viewdatarow[$vlidnow]."\" target=\"_blank\"><img src=\"lib/images/download.png\" height=15 width=20></a></td>";
			}elseIF (($vl_types[$cc]=='TEXTAREA')&&($viewdatarow[$vlidnow]!="")&&($vl_specials[$cc]==3))
			{
			$resulthtml.="<td><img src=\"lib/images/brush-iconsm.gif\" onclick=\" var div1 = document.getElementById('draw{$vlidnow}')

    if (div1.style.visibility == 'hidden') {

        div1.style.visibility = ''

    } else {

        div1.style.visibility = 'hidden'

    }\">".
			'<div id="draw'.$vlidnow.'" style="visibility:hidden;">
<img src="'.'bsvg/backgrounds/'.$vlidnow.'.png" width="240px" height="240px" style="position:absolute;" />
<div id="drawing'.$vlidnow.'" style="position:absolute;">'.urldecode($viewdatarow[$vlidnow]).'</div></div></td>';
			}else{
			
			$resulthtml.= "<td>".$viewdatarow[$vlidnow]."</td>";
			}
				$vlidnow=strtok(',');
                                	 $vlidnow=str_replace('`','',$vlidnow);
                         
                                        $viewdatarow[$vlidnow]=calculate_field($vlidnow, $pc_id, $dbh); //implementation without queries
                                
			$arithmos_stilon = $arithmos_stilon +1;
		}
		
		$resulthtml.= "</tr>";
	}	
	$resulthtml.= '</tbody></table>';
return $resulthtml;
}


        function http_response_code($code = NULL) {

            if ($code !== NULL) {

                switch ($code) {
                    case 100: $text = 'Continue'; break;
                    case 101: $text = 'Switching Protocols'; break;
                    case 200: $text = 'OK'; break;
                    case 201: $text = 'Created'; break;
                    case 202: $text = 'Accepted'; break;
                    case 203: $text = 'Non-Authoritative Information'; break;
                    case 204: $text = 'No Content'; break;
                    case 205: $text = 'Reset Content'; break;
                    case 206: $text = 'Partial Content'; break;
                    case 300: $text = 'Multiple Choices'; break;
                    case 301: $text = 'Moved Permanently'; break;
                    case 302: $text = 'Moved Temporarily'; break;
                    case 303: $text = 'See Other'; break;
                    case 304: $text = 'Not Modified'; break;
                    case 305: $text = 'Use Proxy'; break;
                    case 400: $text = 'Bad Request'; break;
                    case 401: $text = 'Unauthorized'; break;
                    case 402: $text = 'Payment Required'; break;
                    case 403: $text = 'Forbidden'; break;
                    case 404: $text = 'Not Found'; break;
                    case 405: $text = 'Method Not Allowed'; break;
                    case 406: $text = 'Not Acceptable'; break;
                    case 407: $text = 'Proxy Authentication Required'; break;
                    case 408: $text = 'Request Time-out'; break;
                    case 409: $text = 'Conflict'; break;
                    case 410: $text = 'Gone'; break;
                    case 411: $text = 'Length Required'; break;
                    case 412: $text = 'Precondition Failed'; break;
                    case 413: $text = 'Request Entity Too Large'; break;
                    case 414: $text = 'Request-URI Too Large'; break;
                    case 415: $text = 'Unsupported Media Type'; break;
                    case 500: $text = 'Internal Server Error'; break;
                    case 501: $text = 'Not Implemented'; break;
                    case 502: $text = 'Bad Gateway'; break;
                    case 503: $text = 'Service Unavailable'; break;
                    case 504: $text = 'Gateway Time-out'; break;
                    case 505: $text = 'HTTP Version not supported'; break;
                    default:
                        exit('Unknown http status code "' . htmlentities($code) . '"');
                    break;
                }

                $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

                header($protocol . ' ' . $code . ' ' . $text);

                $GLOBALS['http_response_code'] = $code;

            } else {

                $code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);

            }

            return $code;

        }
    
function swap_contact_patient_id($id,$dbh,$is_patid=true,$contact=1){
	//DINW $id=contact_id h patient_id, $is_patid=TRUE gia patient_id false gia contactid id,$contact an exei pola contacts pio 8eloume. 1=to pio palio
	if ($is_patid){
		$sql="Select PC_ID from patient_contacts where pc_patient_id = '$id' order by pc_date asc";
	}else{
		$sql="Select PC_PATIENT_ID from patient_contacts where pc_id = '$id' order by pc_date asc limit 1;";
	}
	$qid=db_query($sql,$dbh);
	if (mysqli_affected_rows($dbh)<$contact) {return false;}
	for ($i=0;$i<$contact;$i++){
	$row=db_fetch_array($qid);
	}
	return $row[0];
}

function get_rdf_header(){
    return '<?xml version="1.0"?>
<!DOCTYPE rdf:RDF [
    <!ENTITY owl "http://www.w3.org/2002/07/owl#" >
    <!ENTITY xsd "http://www.w3.org/2001/XMLSchema#" >
    <!ENTITY rdfs "http://www.w3.org/2000/01/rdf-schema#" >
    <!ENTITY basic-ehr-ontology "http://example.com/basic-ehr-ontology#" >
    <!ENTITY rdf "http://www.w3.org/1999/02/22-rdf-syntax-ns#" >
]>
<rdf:RDF xmlns="http://example.com/basic-ehr-individuals#"
     xml:base="http://example.com/basic-ehr-individuals"
     xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
     xmlns:owl="http://www.w3.org/2002/07/owl#"
     xmlns:xsd="http://www.w3.org/2001/XMLSchema#"
     xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
     xmlns:basic-ehr-ontology="http://example.com/basic-ehr-ontology#">
    <owl:Ontology rdf:about="http://example.com/basic-ehr-individuals">
        <owl:imports rdf:resource="http://example.com/basic-ehr-ontology"/>
    </owl:Ontology>';
}
function purl2vlid($purl,$dbh){
    $qid=db_query("SELECT VL_ID,VL_TYPE,VL_TYPE_URI,VL_URI FROM values_list where VL_PURL like '{$purl}';", $dbh);
     $row=db_fetch_assoc($qid);
     db_free_result($qid);
    if ($row["VL_ID"]==""){
        return false;
    }else{
        return $row;
    }
}

function get_all_records($pcid, $dbh){
$qid=  db_query("Select VL_PURL,NA_VL_ID as VL_ID from num_answers join values_list on NA_VL_ID=VL_ID where NA_CONTACT_ID={$pcid} group by NA_CONTACT_ID
union 
Select VL_PURL,EA_VL_ID as VL_ID from enum_answers join values_list on EA_VL_ID=VL_ID where EA_CONTACT_ID={$pcid} group by EA_CONTACT_ID
union 
Select VL_PURL,BA_VL_ID as VL_ID from bin_answers join values_list on BA_VL_ID=VL_ID where BA_CONTACT_ID={$pcid} group by BA_CONTACT_ID
union 
Select VL_PURL,TA_VL_ID as VL_ID from text_answers join values_list on TA_VL_ID=VL_ID where TA_CONTACT_ID={$pcid} group by TA_CONTACT_ID;",$dbh);

while ($row = db_fetch_array($qid)) {
   
    $record[$row["VL_ID"]]=  get_purl_value($pcid, $row["VL_PURL"], $dbh);
}
while(mysqli_more_results($dbh) && mysqli_next_result($dbh)) {
    $extraResult = mysqli_use_result($dbh);
    if($extraResult instanceof mysqli_result){
        mysqli_free_result($dbh);
    }
}

return $record;
}
function create_full_rdf_forAll($limit,$dbh){
    $q="Select PC_PATIENT_ID,PC_ID from patient_contacts limit {$limit};";
    $qid=  db_query($q, $dbh);$r=0;$RDF="";
    while ($row = db_fetch_assoc($qid)) {
    $pdetails=get_personal_details($row["PC_PATIENT_ID"], $dbh);    
    $RDF.=  create_full_rdf($row["PC_ID"], $pdetails, $dbh,false);
    $r++;
    }
    if ($r>0){
        return get_rdf_header().$RDF."</rdf:RDF>";
        
    }else{
        return false;
    }
}
function create_full_rdf($pcid,$pdetails,$dbh,$includeheader=true){
    $allrecords=  get_all_records($pcid, $dbh);
    
    foreach ($allrecords as $vlid => $record) {   
        $rdfpervlid[$vlid]=create_record_RDF(array($record[0]["VL_PURL"],$record[0]["VL_URI"]), $record, false);
    }
    
    $recordinfo="";
    foreach ($rdfpervlid as $vlid=>$arr){
        $recordinfo.=$arr[0];
        $mis.=$arr[1];
    }
    if ($includeheader){
       $res=  create_pdetails_RDF($pdetails,$includeheader,$recordinfo);
        return mb_substr($res,0,-10).$mis."</rdf:RDF>";
    }else{
        $res=  create_pdetails_RDF($pdetails,$includeheader,$recordinfo);
        return $res.$mis;
    }
}
function get_purl_value($pc_id,$purl,$dbh){
    
    $row=purl2vlid($purl,$dbh);
    
    $caption=  VLID2CAPTION($row["VL_ID"], $dbh);
    
    if ($row==false){
        return false;
    }else{
         
        $qid=db_query("CALL getRes('{$row['VL_ID']}',{$pc_id});", $dbh);
        $result=array();$r=0;
        while ($res=db_fetch_assoc($qid)){
          
            $result[$r]=array('VL_URI'=>$row["VL_URI"],'VL_PURL'=>$purl,'TYPEURI'=>$row["VL_TYPE_URI"],'VL_ID'=>$row['VL_ID'],'CAPTION'=>$caption,'VALUE'=>$res["VALUE"],'TIMESTAMP'=>$res["TS"]);
            $r++;
        }
        while(mysqli_more_results($dbh) && mysqli_next_result($dbh)) {
    $extraResult = mysqli_use_result($dbh);
    if($extraResult instanceof mysqli_result){
        mysqli_free_result($dbh);
    }
}
        if ($result!=array()){
            
        return $result;}  else {
return FALSE;    
}
    }
}
function store_purl_value($pc_id,$purl,$value,$dbh){
    $row=purl2vlid($purl,$dbh);
    
    if ($row==false){
        return false;
    }else{
        store_value($pc_id, $row["VL_ID"], $value, $row["VL_TYPE"], $dbh);
    }
}
function get_personal_details($pid,$dbh){
    $pdetails=db_fetch_assoc(db_query("SELECT SURNAME AS NAME ,PATIENT_ID AS SSN,DOB FROM patients_private WHERE PATIENT_ID like '{$pid}' limit 1;", $dbh));
    if ($pdetails["DOB"]!=""){ 
    $dateof = DateTime::createFromFormat('Y-m-d',$pdetails["DOB"]);      
     $pdetails["DOB"]=$dateof->format('Y-m-d\TH:i:s\Z');
    }
     return $pdetails;
}
function put_personal_details($pdetails,$dbh){
     $dateof = DateTime::createFromFormat('Y-m-d\TH:i:s\Z',$pdetails["DOB"]); 
     if (!is_object($dateof)){
         echo "WRONG DATE FORMAT".$dateof;
         return;
     }
     $pdetails["DOB"]=$dateof->format('Y-m-d');
    db_query("INSERT INTO patients_private (PATIENT_ID,SSN,SURNAME,DOB) VALUES('{$pdetails["SSN"]}','{$pdetails["SSN"]}','{$pdetails["NAME"]}','{$pdetails["DOB"]}');", $dbh);
    db_query("INSERT INTO patient_contacts(PC_PATIENT_ID,PC_DATE) values('{$pdetails["SSN"]}',CURDATE());",$dbh);
    return ;
}
function update_personal_details($pdetails,$dbh){
        $dateof = DateTime::createFromFormat('Y-m-d\TH:i:s\Z',$pdetails["DOB"]);      
     $pdetails["DOB"]=$dateof->format('Y-m-d');
   $q= "UPDATE patients_private SET  SURNAME='{$pdetails["NAME"]}',DOB='{$pdetails["DOB"]}' where PATIENT_ID='{$pdetails["SSN"]}';";
    return db_fetch_assoc(db_query($q, $dbh));
}
function create_pdetails_RDF($pdetails,$includeheader=true,$recordinfo=""){
     
     
    $rdfheader=get_rdf_header();
    $individualrdf='<owl:NamedIndividual rdf:about="http://example.com/basic-ehr-individuals#'.$pdetails["SSN"].'">
        <rdf:type rdf:resource="&basic-ehr-ontology;Patient"/>
        <basic-ehr-ontology:dob rdf:datatype="&xsd;dateTime">'.$pdetails["DOB"].'</basic-ehr-ontology:dob>
        <basic-ehr-ontology:ssn>'.$pdetails["SSN"].'</basic-ehr-ontology:ssn>
        <basic-ehr-ontology:name>'.$pdetails["NAME"].'</basic-ehr-ontology:name>'.$recordinfo.'        
    </owl:NamedIndividual>';
    if ($includeheader){
        return $rdfheader.$individualrdf."</rdf:RDF>";
    }else{
        return $individualrdf;
    }
}
function create_record_RDF($purl,$records,$includeheader=false,$pdetails=""){
    
    // include header=1 : sent header+pdetails+mi
    // include header=2 : sent pdetails+mi
    // include header false : return array with [0]=>(record info) [1]->mi
     $RDF="";
     $recordinfo="";
    
    
    
        foreach ($records as $key => $record) {
             $date = DateTime::createFromFormat('Y-m-d H:i:s',$record["TIMESTAMP"]);                  
        if ($record["TYPEURI"]=="http://example.com/basic-ehr-ontology#Question"){
            
$recordinfo.='<basic-ehr-ontology:record rdf:resource="http://example.com/basic-ehr-individuals#a_'.$record["VL_ID"]."_".$key.'"/>';     
if ($key==0){
    $RDF='<!-- '.$purl[0].' -->

    <owl:NamedIndividual rdf:about="'.$purl[0].'">
        <rdf:type rdf:resource="&basic-ehr-ontology;Question"/>
        <basic-ehr-ontology:questionDescription>'.$record["CAPTION"].'</basic-ehr-ontology:questionDescription>
    </owl:NamedIndividual>';
}
     $RDF.='<!-- http://example.com/basic-ehr-individuals#a_'.$record["VL_ID"]."_".$key.' -->

    <owl:NamedIndividual rdf:about="http://example.com/basic-ehr-individuals#a_'.$record["VL_ID"]."_".$key.'">
        <rdf:type rdf:resource="&basic-ehr-ontology;Answer"/>
        <basic-ehr-ontology:timestamp rdf:datatype="&xsd;dateTimeStamp">'.$date->format('Y-m-d\TH:i:s\Z').'</basic-ehr-ontology:timestamp>
        <basic-ehr-ontology:answerValue>'.$record["VALUE"].'</basic-ehr-ontology:answerValue>
        <basic-ehr-ontology:ofQuestion rdf:resource="'.$purl[0].'"/>
    </owl:NamedIndividual>';
        }elseif ($record["TYPEURI"]=="http://example.com/basic-ehr-ontology#BiologicalProperty"){

$recordinfo.='<basic-ehr-ontology:record rdf:resource="http://example.com/basic-ehr-individuals#m_'.$record["VL_ID"]."_".$key.'"/>';
if ($key==0){ //for the first record add VL instance
    $RDF='<!-- '.$purl[1].' -->

       <owl:NamedIndividual rdf:about="'.$purl[1].'">
        <rdf:type rdf:resource="&basic-ehr-ontology;BiologicalProperty"/>
        <basic-ehr-ontology:conceptURI rdf:datatype="&xsd;anyURI">'.$purl[0].'</basic-ehr-ontology:conceptURI>
        <basic-ehr-ontology:description>'.$record["CAPTION"].'</basic-ehr-ontology:description>
    </owl:NamedIndividual>';
}
    $RDF.='<!-- http://example.com/basic-ehr-individuals#m_'.$key.' -->

    <owl:NamedIndividual rdf:about="http://example.com/basic-ehr-individuals#m_'.$record["VL_ID"]."_".$key.'">
        <rdf:type rdf:resource="&basic-ehr-ontology;SingleValueMeasurement"/>
        <basic-ehr-ontology:timestamp rdf:datatype="&xsd;dateTimeStamp">'.$date->format('Y-m-d\TH:i:s\Z').'</basic-ehr-ontology:timestamp>
        <basic-ehr-ontology:hasValue>'.$record["VALUE"].'</basic-ehr-ontology:hasValue>
        <basic-ehr-ontology:measurementOfBiologicalProperty rdf:resource="'.$purl[1].'"/>
    </owl:NamedIndividual>';
        }
        }
        if ($includeheader===FALSE){
        return  array($recordinfo,$RDF);
        }elseif ($includeheader==1) {
            $res=  create_pdetails_RDF($pdetails,true,$recordinfo);
        return mb_substr($res,0,-10).$RDF."</rdf:RDF>";
    }elseif ($includeheader==2) {
            $res=  create_pdetails_RDF($pdetails,false,$recordinfo);
        return $res.$RDF;
    }
}
function store_value($pc_id,$field,$value,$type,$dbh,$target='GENERAL'){
    
	//CAUTION for binary $value must be $_files[$fieldname] Validation must be performed earlier
switch ($type)
{
	case 'NUMERICAL':
		$query_insert="INSERT INTO `num_answers` ( `NA_ID` , `NA_CONTACT_ID` , `NA_VL_ID` , `NA_VALUE` , `NA_TARGET` )
VALUES (NULL , '$pc_id', '$field','$value', '$target');";
		break;
	case 'ENUMERATED':
$query_insert="INSERT INTO `enum_answers` ( `EA_ID` , `EA_CONTACT_ID` , `EA_VL_ID` , `EA_VALUE` , `EA_TARGET` )
VALUES (NULL , '$pc_id', '$field', '$value', '$target');";
		break;
		case "DATES":
	case 'FREETEXT':
	case 'TEXTAREA':
		$query_insert="INSERT INTO `text_answers` ( `TA_ID` , `TA_CONTACT_ID` , `TA_VL_ID` , `TEXT` , `TA_TARGET` )
VALUES (NULL , '$pc_id', '$field','$value', '$target');";
		break;
	case 'BINARY':
			//CAUTION $value must be $_files[$fieldname] Validation must be performed earlier
								$fp=fopen($value['tmp_name'],'rb');
								$fir=fread($fp,$value['size']);
								$ext=strtok($value['name'],".");
								while ($ext=strtok(".")){
									$ftype=$ext;
								}
							$query_insert="INSERT INTO `bin_answers` ( `BA_ID` , `BA_CONTACT_ID` , `BA_VL_ID` , `BA_VALUE` , `BA_TARGET`,`BA_FTYPE` )
VALUES (NULL , '$pc_id', '$field', '".addslashes($fir)."', '$target','$ftype');";
	default:
		break;
}
db_query($query_insert,$dbh,$GLOBALS['DB_DEBUG'],$GLOBALS['DB_DIE_ON_FAIL'],'','Storing data');
	
}
function calculate_field($field,$pc_id,$dbh)
{
	// field= vL_ID
	switch ($field){
	case 'Age Now':
date_default_timezone_set('Europe/Athens');
 $today=new DateTime(date("d-m-Y"));
$query="SELECT DOB from patient_contacts,patients_private where pc_patient_id=patient_id and pc_id='{$pc_id}';";
		$qid=db_query($query,$dbh,"","","","Selecting date of birth");
		$row=db_fetch_array($qid);
$dob=new DateTime($row["DOB"]);
$int=date_diff($dob,$today);
if (substr($row["DOB"],0,4)=="0000"){
    return "Year of birth undefined";
}else{
return $int->y;
}
case 'Visit Date':
$query="SELECT PC_DATE from patient_contacts WHERE PC_ID='{$pc_id}';";
		$qid=db_query($query,$dbh,"","","","Selecting date of visit");
		$row=db_fetch_array($qid);
                return $row["PC_DATE"];
    
    break;
case 'AGE':

$query="SELECT PC_DATE,DOB from patient_contacts,patients_private where pc_patient_id=patient_id and pc_id='{$pc_id}';";
		$qid=db_query($query,$dbh,"","","","Selecting date of birth");
		$row=db_fetch_array($qid);
$dob=new DateTime($row["DOB"]);
$vdate=new DateTime($row["PC_DATE"]);
$int=date_diff($dob,$vdate);
if (substr($row["DOB"],0,4)=="0000"){
    return "Year of birth undefined";
}else{
return $int->y;
}
Case "Visit":
    return PCID2VISIT($pc_id, $dbh);
    break;
	CASE 'ΣΥΝΕ':
		$result=$_SESSION["USER"]["user"]["firstname"]." ".$_SESSION["USER"]["user"]["lastname"];
		
		return $result;
		break;
         case "Histology AC":
             $AC=calculate_field("AssistCoding", $pc_id, $dbh);
             return $AC["Histology"][0];
             break;
         case "Colposcopy AC":
             $AC=calculate_field("AssistCoding", $pc_id, $dbh);
             return $AC["Colposcopy"][0];
             break;
         case "Cytology AC":
             $AC=calculate_field("AssistCoding", $pc_id, $dbh);
             return $AC["Cytology"][0];
             break;
        CASE 'AssistCoding':
//Calculate Histology
$histrow=db_fetch_array(db_query("SELECT EC_AL_ORDER-1 as AC,AL_DESCRIPTION,EA_VL_ID FROM enum_answers join answer_list  on EA_VALUE=AL_ID join enum_constraint on EC_VL_ID=EA_VL_ID and EC_AL_ID=AL_ID where EA_VL_ID='HIST' and EA_CONTACT_ID={$pc_id};",$dbh,"","","","SELECTING HISTOLOGY RESULT"));
            
            $vis=PCID2VISIT($pc_id, $dbh);
             
            if (!$histrow){
                     $histrow["AL_DESCRIPTION"]="Data empty for current visit({$vis})";
                $hphist= array ("NA","Normal","Atypia","Mild dysplasia or mild dyskaryosis","Moderate dysplasia or moderate dyskaryosis","Severe dysplasia or severe dyskaryosis","Carcinoma in-situ","Invasive carcinoma");
             $histpriv=db_fetch_array(db_query("SELECT HISTRESULT from patients_private where PATIENT_ID='".  swap_contact_patient_id($pc_id, $dbh,false)."';",$dbh,"","","","SELECTING COLPOSCOPY RESULT"));
            
             if (($histpriv["HISTRESULT"]!="")&&($histpriv["HISTRESULT"]!="NA")&&  ($vis==1)){
                 $histrow["AL_DESCRIPTION"]=$histpriv["HISTRESULT"]."(from Patient History)";
                 $histrow["AC"]=  array_search($histpriv["HISTRESULT"], $hphist)-1;
                 
             }else{
             $histrow["AC"]="";//if NA or empty in history or not existing at all
             if ($vis==1){
        //     $histrow["AL_DESCRIPTION"]="Data empty for first visit and from Patient History";
             }
             }
             }else{ //add the comment
                   $histrow["AL_DESCRIPTION"]=$histrow["AL_DESCRIPTION"]."<hr/>".calculate_field("HISC", $pc_id, $dbh);  
            }
            switch ($histrow["AC"]){
                case 0:
                    break;
                case 1:
                case 2:
                    $histrow["AC"]=1;
                    break;
                case 3:
                case 4:
                case 5:
                    $histrow["AC"]=2;
                    break;
                case 6:
                    $histrow["AC"]=3;
                    break;         
               
               
            }            
//Calculate Colposcopy
            $colprow=db_fetch_array(db_query("SELECT EC_AL_ORDER-1 as AC,AL_DESCRIPTION,EA_VL_ID FROM enum_answers join answer_list  on EA_VALUE=AL_ID join enum_constraint on EC_VL_ID=EA_VL_ID and EC_AL_ID=AL_ID where EA_VL_ID='COLP' and EA_CONTACT_ID={$pc_id};",$dbh,"","","","SELECTING COLPOSCOPY RESULT"));
            
            
            
            if (!$colprow){
            
             $colprow["AC"]="";//if NA or empty in colpory or not existing at all
             $colprow["AL_DESCRIPTION"]="Data empty for current visit";
            
            }else{//add coment
                $colprow["AL_DESCRIPTION"]=$colprow["AL_DESCRIPTION"]."<hr/>".calculate_field("COLC", $pc_id, $dbh);
                        
            }
//Calculate Cytology
            $cytores=db_query("SELECT EA_VL_ID,VL_CAPTION,EC_AL_ID FROM enum_answers join answer_list  on EA_VALUE=AL_ID join enum_constraint on EC_VL_ID=EA_VL_ID and EC_AL_ID=AL_ID join values_list on EA_VL_ID=VL_ID where (EC_AL_ID=1 or EC_AL_ID=2) and EA_VL_ID in (SELECT VL_ID from values_list where VL_CATEGORY=5 and VL_TYPE like 'ENUMERATED') and EA_CONTACT_ID={$pc_id};",$dbh,"","","","SELECTING CYTOLOGY RESULTS");
                                    
            if (db_num_rows($cytores)==0){
            
             $cytorow["AC"]="";//if NA or empty in colpory or not existing at all
             $cytorow["AL_DESCRIPTION"]="Data empty for current visit";
            
            }else{
                $cytodesc=array();
            while ($cytosrow=  db_fetch_assoc($cytores)){
                if ($cytosrow["EC_AL_ID"]==1) {array_push($cytodesc, $cytosrow["VL_CAPTION"]);}
                
                switch ($cytosrow["EA_VL_ID"]){
                    case 'ELSA':
                        if ($cytosrow["EC_AL_ID"]==2){$cytorow["AC"]="N/E";
                        array_push($cytodesc,"Not Eligible Sample");}
                        break;
case 'NEGA':
case 'RECH':
case 'REWI':
case 'REWR':
case 'REWC':
case 'GCHT':
case 'ATRO':                       
                        $cytorow["AC"]=max(0,$cytorow["AC"]);
                        break;
case 'ASQC':
case 'ASCU':
case 'LSIL':
case 'LSIC':
case 'CNOS':
case 'GNOS':                    
                    $cytorow["AC"]=max(1,$cytorow["AC"]);
                        break;
case 'ASCH':
case 'HSIL':
case 'HSID':
case 'HSIS':
case 'CAFN':
case 'GAFN':
case 'ECAD':                    
                    $cytorow["AC"]=max(2,$cytorow["AC"]);
                        break;
case 'SQCC':
case 'ADEN':
case 'ADEC':
case 'ACOS':                    
                    $cytorow["AC"]=max(3,$cytorow["AC"]);
                        break;
case 'OTEC':
case 'MNOS':
case 'ADEM':
case 'ADEU':                   
                   $cytorow["AC"]=max(-1,$cytorow["AC"]);
                        break;
                        default:
                            break;
                }
            }
             array_push($cytodesc, calculate_field("ADDC", $pc_id, $dbh)); //add coment
            }
            if ($cytorow["AC"]==-1){
                $cytorow["AC"]='Not Defined';
            }
             
        if ($cytodesc!=array())
            $cytorow["AL_DESCRIPTION"]=implode("<Hr/>", $cytodesc);
            return array('Cytology'=>array($cytorow["AC"],$cytorow["AL_DESCRIPTION"]),'Colposcopy'=>array($colprow["AC"],$colprow["AL_DESCRIPTION"]),'Histology'=>array($histrow["AC"],$histrow["AL_DESCRIPTION"]));
            break;
        case 'HPVDNA':
            //Calculate HPV
            $hpvres=db_query("SELECT AL_ID,AL_DESCRIPTION,EA_VL_ID,VL_CAPTION FROM enum_answers 
join answer_list  on EA_VALUE=AL_ID 
join enum_constraint on EC_VL_ID=EA_VL_ID and EC_AL_ID=AL_ID 
join values_list on EA_VL_ID=VL_ID 
where  EA_VL_ID in 
(SELECT VL_ID from values_list where VL_CATEGORY=6 and VL_TYPE like 'ENUMERATED')
 and EA_CONTACT_ID={$pc_id} order by VL_ASKORDER;",$dbh,"","","","SELECTING HPV DNA RESULTS");
                                    
            if (db_num_rows($hpvres)==0){
                
             $hpvrow["RESULT"]="";//if NA or empty in colpory or not existing at all
             $hpvrow["AL_DESCRIPTION"]="Data empty for current visit";
            }else{
                $hpvdesc=array();
            while ($hpvsrow=  db_fetch_assoc($hpvres)){
                
                switch ($hpvsrow["EA_VL_ID"]){
              case 'DETY':     
                  if ($hpvsrow["AL_DESCRIPTION"]=='NEGATIVE'){
                  $hpvrow["RESULT"].=$hpvsrow["AL_DESCRIPTION"]." ";
                  }
              case 'HPVR':
              case 'HPV2':
              case 'HPV3':
              case 'HPV4':
              case 'HPV5':
              case 'HPV6':
                  if ($hpvsrow["EA_VL_ID"]!='DETY'){
                  $hpvrow["RESULT"].=$hpvsrow["AL_DESCRIPTION"]." ";
                  }
                  array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                  break;
              default:
                  if ($hpvsrow["AL_ID"]==1){
                  array_push($hpvdesc, $hpvsrow["VL_CAPTION"]);
                  }elseif ($hpvsrow["AL_ID"]!=2){
                  array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                  }
              break;
            }
            }
            
            $hpvrow["AL_DESCRIPTION"]=implode('<hr>',$hpvdesc);
            }
            
return array(trim($hpvrow["RESULT"]),$hpvrow["AL_DESCRIPTION"]);            
            break;
            
//ELISA
            
            
              case 'E7ELISARAPI':
            //Calculate ELISA RAPID BIOSYNEX
                  $numOfNotEligibleSamples=0;
            $hpvres=db_query("SELECT AL_ID,AL_DESCRIPTION,EA_VL_ID,VL_CAPTION FROM enum_answers 
join answer_list  on EA_VALUE=AL_ID 
join enum_constraint on EC_VL_ID=EA_VL_ID and EC_AL_ID=AL_ID 
join values_list on EA_VL_ID=VL_ID 
where  EA_VL_ID in 
(SELECT VL_ID from values_list where VL_CATEGORY =3 and VL_TYPE like 'ENUMERATED')
 and EA_CONTACT_ID={$pc_id} order by VL_ASKORDER;",$dbh,"","","","SELECTING HPV DNA RESULTS");
                                    
            if (db_num_rows($hpvres)==0){
                
             $hpvrow["RESULT"]="";//if NA or empty in colpory or not existing at all
             $hpvrow["AL_DESCRIPTION"]="Data empty for current visit";
            }else{
                $hpvdesc=array();
            while ($hpvsrow=  db_fetch_assoc($hpvres)){
                
                switch ($hpvsrow["EA_VL_ID"]){
              case 'HR7R':     
              case 'H16R':
              case 'H18R':    //gia oles tis sxetikes me to result apanthseis
                  switch($hpvsrow["AL_ID"]){
                  case 1: //an einai YES tote den me noiazei ti htan prin
                      $hpvrow["RESULT"]=$hpvsrow["AL_DESCRIPTION"];           
                  array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                      break;                  
                  case 202://an einai BORDERLINE tote an den einai yes to result na ginei border
                      if ($hpvrow["RESULT"]!='YES'){
                          $hpvrow["RESULT"]=$hpvsrow["AL_DESCRIPTION"];           
                  array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                      }
                      break;
                  case 2: //an einai no apla metraw posa no einai kai pros8eto to apotelesma sto popup
                      $numOfNotEligibleSamples++;
                      array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                      break;
                  default:
                      break;
                  }
                  
                  break;  
              case 'ESDR':     
                  if ($hpvsrow["AL_ID"]==2){
                  $hpvrow["RESULT"]='Sample not Eligible';       
                  }
                  array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                  break;
              default:
                  if ($hpvsrow["AL_ID"]==1){
                  //array_push($hpvdesc, $hpvsrow["VL_CAPTION"]);
                     array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                  }elseif ($hpvsrow["AL_ID"]!=2){
                  array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                  }
              //elegxos an einai kai ta 3 NO
              if ($numOfNotEligibleSamples==3){
                $hpvrow["RESULT"]='NO';   
              }    
                 
              break;
            }
            }
             array_push($hpvdesc, "<i>".  calculate_field("RAPC", $pc_id, $dbh)."</i>"); //add coment
            $hpvrow["AL_DESCRIPTION"]=implode('<hr>',$hpvdesc);
            }
            
return array(trim($hpvrow["RESULT"]),$hpvrow["AL_DESCRIPTION"]);            
            break;
               case 'E7ELISATPUI':
            //Calculate ELISA Thinprep UIBK
                   $thisanswer=calculate_field('ESDE', $pc_id, $dbh);
                     $hpvdesc=array();
                  if ($thisanswer=='NO'){
                  $hpvrow["RESULT"]='N/E';       
                  array_push($hpvdesc, "Eligible ThinPrep sample for Elisa:".$thisanswer);
             $hpvrow["AL_DESCRIPTION"]=implode('<hr>',$hpvdesc);     
                  return array(trim($hpvrow["RESULT"]),$hpvrow["AL_DESCRIPTION"]);            
                  }elseif ($thisanswer=='' || $thisanswer=='empty') {
                            $hpvrow["RESULT"]="";//if NA or empty in colpory or not existing at all
             $hpvrow["AL_DESCRIPTION"]="Data empty for current visit";               
                  return array(trim($hpvrow["RESULT"]),$hpvrow["AL_DESCRIPTION"]);            
                    }
                    
           
            $hpvres=db_query("select VL_ID,VL_CAPTION from values_list where (vl_category=4 and vl_type not like 'title' )
                order by vl_category,vl_askorder;",$dbh,"","","","SELECTING E7 UIBK THINPREP RESULTS");
                                    
            if (db_num_rows($hpvres)==0){
                
             $hpvrow["RESULT"]="";//if NA or empty in colpory or not existing at all
             $hpvrow["AL_DESCRIPTION"]="Data empty for current visit";
            }else{
             
            while ($hpvsrow=  db_fetch_assoc($hpvres)){
                $thisanswer=calculate_field($hpvsrow["VL_ID"], $pc_id, $dbh);
                
                  
                switch ($hpvsrow["VL_ID"]){
              
              case 'ODU1':
                  case 'ODU2':
                      case 'ODU3':
                  if ($thisanswer!='NULL' && $thisanswer!='empty' && $thisanswer!=''){
                  $hpvrow["RESULT"].=$thisanswer." ";
                     array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$thisanswer);
                  } elseif ($thisanswer!='NULL'){
                      $hpvrow["RESULT"].="NULL ";
                  }                             
              break;
              
                  
              default:
                  if ($thisanswer!='NULL' && $thisanswer!='empty' && $thisanswer!=''){
                  
                     array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$thisanswer);
                  }                              
              break;
            }
            }
       
            $hpvrow["AL_DESCRIPTION"]=implode('<hr>',$hpvdesc);
            }
            
return array(trim($hpvrow["RESULT"]),$hpvrow["AL_DESCRIPTION"]);            
            break;
            case 'E7ELISADIUI':
            //Calculate ELISA Thinprep UIBK
                  $numOfNotEligibleSamples=0;
            $hpvres=db_query("SELECT AL_ID,AL_DESCRIPTION,EA_VL_ID,VL_CAPTION FROM enum_answers 
join answer_list  on EA_VALUE=AL_ID 
join enum_constraint on EC_VL_ID=EA_VL_ID and EC_AL_ID=AL_ID 
join values_list on EA_VL_ID=VL_ID 
where  EA_VL_ID in 
(SELECT VL_ID from values_list where VL_CATEGORY =11 and VL_TYPE like 'ENUMERATED')
 and EA_CONTACT_ID={$pc_id} order by VL_ASKORDER;",$dbh,"","","","SELECTING HPV DNA RESULTS");
                                    
            if (db_num_rows($hpvres)==0){
                
             $hpvrow["RESULT"]="";//if NA or empty in colpory or not existing at all
             $hpvrow["AL_DESCRIPTION"]="Data empty for current visit";
            }else{
                $hpvdesc=array();
            while ($hpvsrow=  db_fetch_assoc($hpvres)){
                
                switch ($hpvsrow["EA_VL_ID"]){
              case 'HR72':     
              case 'H162':
              case 'H182':    //gia oles tis sxetikes me to result apanthseis
                  switch($hpvsrow["AL_ID"]){
                  case 1: //an einai YES tote den me noiazei ti htan prin
                      $hpvrow["RESULT"]=$hpvsrow["AL_DESCRIPTION"];           
                  array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                      break;                  
                  case 202://an einai BORDERLINE tote an den einai yes to result na ginei border
                      if ($hpvrow["RESULT"]!='YES'){
                          $hpvrow["RESULT"]=$hpvsrow["AL_DESCRIPTION"];           
                  array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                      }
                      break;
                  case 2: //an einai no apla metraw posa no einai kai pros8eto to apotelesma sto popup
                      $numOfNotEligibleSamples++;
                      array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                      break;
                  default:
                      break;
                  }
                  
                  break;  
              case 'ESD2':     
                  if ($hpvsrow["AL_ID"]==2){
                  $hpvrow["RESULT"]='Sample not Eligible';       
                  }
                  array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                  break;
              default:
                  if ($hpvsrow["AL_ID"]==1){
                  //array_push($hpvdesc, $hpvsrow["VL_CAPTION"]);
                     array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                  }elseif ($hpvsrow["AL_ID"]!=2){
                  array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                  }
              //elegxos an einai kai ta 3 NO
              if ($numOfNotEligibleSamples==3){
                $hpvrow["RESULT"]='NO';   
              }    
                 
              break;
            }
            }
             array_push($hpvdesc, "<i>".  calculate_field("ELC2", $pc_id, $dbh)."</i>"); //add coment
            $hpvrow["AL_DESCRIPTION"]=implode('<hr>',$hpvdesc);
            }
            
return array(trim($hpvrow["RESULT"]),$hpvrow["AL_DESCRIPTION"]);            
            break;
            case 'E7ELISATPMI':
              //Calculate ELISA Thinprep mikrogen
                   $thisanswer=calculate_field('ESD1', $pc_id, $dbh);
                     $hpvdesc=array();
                  if ($thisanswer=='NO'){
                  $hpvrow["RESULT"]='N/E';       
                  array_push($hpvdesc, "Eligible ThinPrep sample for Elisa:".$thisanswer);
             $hpvrow["AL_DESCRIPTION"]=implode('<hr>',$hpvdesc);     
                  return array(trim($hpvrow["RESULT"]),$hpvrow["AL_DESCRIPTION"]);            
                  }elseif ($thisanswer=='' || $thisanswer=='empty') {
                            $hpvrow["RESULT"]="";//if NA or empty in colpory or not existing at all
             $hpvrow["AL_DESCRIPTION"]="Data empty for current visit";               
                  return array(trim($hpvrow["RESULT"]),$hpvrow["AL_DESCRIPTION"]);            
                    }
                    
           
            $hpvres=db_query("select VL_ID,VL_CAPTION from values_list where (vl_category=10 and vl_type not like 'title' )
                order by vl_category,vl_askorder;",$dbh,"","","","SELECTING E7 mikrogen THINPREP RESULTS");
                                    
            if (db_num_rows($hpvres)==0){
                
             $hpvrow["RESULT"]="";//if NA or empty in colpory or not existing at all
             $hpvrow["AL_DESCRIPTION"]="Data empty for current visit";
            }else{
             
            while ($hpvsrow=  db_fetch_assoc($hpvres)){
                $thisanswer=calculate_field($hpvsrow["VL_ID"], $pc_id, $dbh);
                
                  
                switch ($hpvsrow["VL_ID"]){
              
              case 'ODSW':
                  case 'ODDI':
                      
                  if ($thisanswer!='NULL' && $thisanswer!='empty' && $thisanswer!=''){
                  $hpvrow["RESULT"].=$thisanswer." ";
                     array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$thisanswer);
                  }  elseif ($thisanswer!='NULL'){
                      $hpvrow["RESULT"].="NULL ";
                  }                             
              break;
              
                  
              default:
                  if ($thisanswer!='NULL' && $thisanswer!='empty' && $thisanswer!=''){
                  
                     array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$thisanswer);
                  }                              
              break;
            }
            }
       
            $hpvrow["AL_DESCRIPTION"]=implode('<hr>',$hpvdesc);
            }
            
return array(trim($hpvrow["RESULT"]),$hpvrow["AL_DESCRIPTION"]);            
            break;
         case 'E7ELISADIMI':
            //Calculate ELISA Thinprep UIBK
                  $numOfNotEligibleSamples=0;
            $hpvres=db_query("SELECT AL_ID,AL_DESCRIPTION,EA_VL_ID,VL_CAPTION FROM enum_answers 
join answer_list  on EA_VALUE=AL_ID 
join enum_constraint on EC_VL_ID=EA_VL_ID and EC_AL_ID=AL_ID 
join values_list on EA_VL_ID=VL_ID 
where  EA_VL_ID in 
(SELECT VL_ID from values_list where VL_CATEGORY =12 and VL_TYPE like 'ENUMERATED')
 and EA_CONTACT_ID={$pc_id} order by VL_ASKORDER;",$dbh,"","","","SELECTING HPV DNA RESULTS");
                                    
            if (db_num_rows($hpvres)==0){
                
             $hpvrow["RESULT"]="";//if NA or empty in colpory or not existing at all
             $hpvrow["AL_DESCRIPTION"]="Data empty for current visit";
            }else{
                $hpvdesc=array();
            while ($hpvsrow=  db_fetch_assoc($hpvres)){
                
                switch ($hpvsrow["EA_VL_ID"]){
              case 'HR73':     
              case 'H163':
                //gia oles tis sxetikes me to result apanthseis
                  switch($hpvsrow["AL_ID"]){
                  case 1: //an einai YES tote den me noiazei ti htan prin
                      $hpvrow["RESULT"]=$hpvsrow["AL_DESCRIPTION"];           
                  array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                      break;                  
                  case 202://an einai BORDERLINE tote an den einai yes to result na ginei border
                      if ($hpvrow["RESULT"]!='YES'){
                          $hpvrow["RESULT"]=$hpvsrow["AL_DESCRIPTION"];           
                  array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                      }
                      break;
                  case 2: //an einai no apla metraw posa no einai kai pros8eto to apotelesma sto popup
                      $numOfNotEligibleSamples++;
                      array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                      break;
                  default:
                      break;
                  }
                  
                  break;  
              case 'ESD3':     
                  if ($hpvsrow["AL_ID"]==2){
                  $hpvrow["RESULT"]='Sample not Eligible';       
                  }
                  array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                  break;
              default:
                  if ($hpvsrow["AL_ID"]==1){
                  //array_push($hpvdesc, $hpvsrow["VL_CAPTION"]);
                     array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                  }elseif ($hpvsrow["AL_ID"]!=2){
                  array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                  }
              //elegxos an einai kai ta 2 NO
              if ($numOfNotEligibleSamples==2){
                $hpvrow["RESULT"]='NO';   
              }    
                 
              break;
            }
            }
             array_push($hpvdesc, "<i>".  calculate_field("ELC3", $pc_id, $dbh)."</i>"); //add coment
            $hpvrow["AL_DESCRIPTION"]=implode('<hr>',$hpvdesc);
            }
            
return array(trim($hpvrow["RESULT"]),$hpvrow["AL_DESCRIPTION"]);            
            break; 
        
        case 'STRIPTEST':
            $numOfNotEligibleSamples=0;
            $hpvres=db_query("SELECT AL_ID,AL_DESCRIPTION,EA_VL_ID,VL_CAPTION FROM enum_answers 
join answer_list  on EA_VALUE=AL_ID 
join enum_constraint on EC_VL_ID=EA_VL_ID and EC_AL_ID=AL_ID 
join values_list on EA_VL_ID=VL_ID 
where  EA_VL_ID in 
(SELECT VL_ID from values_list where VL_CATEGORY =13 and VL_TYPE like 'ENUMERATED')
 and EA_CONTACT_ID={$pc_id} order by VL_ASKORDER;",$dbh,"","","","SELECTING STRIPTEST RESULTS");
                                    
            if (db_num_rows($hpvres)==0){
                
             $hpvrow["RESULT"]="";//if NA or empty in colpory or not existing at all
             $hpvrow["AL_DESCRIPTION"]="Data empty for current visit";
            }else{
                $hpvdesc=array();
            while ($hpvsrow=  db_fetch_assoc($hpvres)){
                
                switch ($hpvsrow["EA_VL_ID"]){
              case 'H96R':                                
              case 'H98R':                     
                      
                  if ($hpvsrow["AL_ID"]==1){
                  $hpvrow["RESULT"]="YES";           
                  array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                  }elseif (($hpvsrow["AL_ID"]==202)&&($hpvrow["RESULT"]!="YES")){
                      $hpvrow["RESULT"]="BORDERLINE";           
                  array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                  }elseif ((($hpvrow["RESULT"]!="BORDERLINE")&&($hpvrow["RESULT"]!="YES")&&(($hpvsrow["AL_ID"]==2)))) {
                                $hpvrow["RESULT"]="NO";           
                  array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                            }
                  break;
                case 'E916':     
                case 'E945':
                  if ($hpvsrow["AL_ID"]==2){
                  $numOfNotEligibleSamples++;
                  }
                  array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                  break;
              default:
                  if ($hpvsrow["AL_ID"]==1){
                  //array_push($hpvdesc, $hpvsrow["VL_CAPTION"]);
                         array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                  }elseif ($hpvsrow["AL_ID"]!=2){
                  array_push($hpvdesc, $hpvsrow["VL_CAPTION"].":".$hpvsrow["AL_DESCRIPTION"]);
                  }
              break;
            }
            }
            
            $hpvrow["AL_DESCRIPTION"]=implode('<hr>',$hpvdesc);
            }
            if ($numOfNotEligibleSamples==2){
                $hpvrow["RESULT"]='Sample not Eligible'; 
            }
return array(trim($hpvrow["RESULT"]),$hpvrow["AL_DESCRIPTION"]);            
            break;    
	default:
            
		return GetResponseData($pc_id, $field, $dbh);
	}
}

//_________________________________________________________________________
function access_log_function($username,$reason,$dbh){
	if ($username!=""){

		$query="INSERT into `access_log` (`LOG_USERNAME`,`LOG_TIME`,`LOG_EVENT`) VALUES ('$username',CURRENT_TIMESTAMP,'$reason')";
		db_query($query,$dbh,"","","","Administration while writing access log ");
		return true;}else{return false;}
}


//------------------------------------------------------------------

function calculate_subjects($cc_id,$admin_dbh)
/* Give cc_id returns an array of previously newly control and family member subjects+their status based on administration db */
{
	$numberof=array();
	for ($j=0;$j<3;$j++){
		$numberof["prev"][$j]=0;
		$numberof["ctrl"][$j]=0;
		$numberof["new"][$j]=0;
		$numberof["family"][$j]=0;
	}
	for ($i=0;$i<3;$i++){
		$query="SELECT count(st_type) as \"PLITHOS\" ,ST_TYPE FROM subject_types where st_cc_id='".$cc_id."' and st_status=".$i." group by st_type;";
		$statusqid=db_query($query,$admin_dbh,"","","","Administration");

		while ($statusofrow[$i]=db_fetch_array($statusqid)) {





			switch ($statusofrow[$i]["ST_TYPE"]) {
				case 0:
				$numberof["prev"][$i]=$statusofrow[$i]["PLITHOS"];
				break;
				case 1:
				$numberof["new"][$i]=$statusofrow[$i]["PLITHOS"];
				break;
				case 2:
				$numberof["ctrl"][$i]=$statusofrow[$i]["PLITHOS"];
				break;
				case 3:
				$numberof["family"][$i]=$statusofrow[$i]["PLITHOS"];
				break;
			}

		}
	}

		return $numberof;
}
function ratio_compliance ($Np,$Nn,$Nc,$CFGprev,$CFGnewly,$CFGctrl)
/* returns if passsed set P/N/C complies with ratio given on config.
returns array array[0]=1 for passed 0 for failed array[1]=integer needed newly diagnosed array[2]=integer needed controls */
{
	$result=array();
	$failedstring="<font color=\"red\">Failed</font>";
	$result[0]="<font color=\"green\">Passed</font>";
	$result[1]=0;
	$result[2]=0;

	if ($Np>=$CFGprev)
	{
		$K=$Np-($Np%$CFGprev);
		if (($Nn/$K)<($CFGnewly / $CFGprev)){
		$result[0]=$failedstring;
			$result[1]=ceil(($CFGnewly / $CFGprev)*$K-$Nn);
		}

		if (($Nc/$K)<($CFGctrl / $CFGprev)){
		$result[0]=$failedstring;
			$result[2]=ceil(($CFGctrl / $CFGprev)*$K-$Nc);
		}
	}
	return $result;
}
function validate_importance($pc_id,$importance="G2",$dbh)
{
/* PROSOXH H SYNARTHSH EINAI OBSOLETE */
	unset($result);
	$result=array();

	$query="SELECT * FROM values_list WHERE VL_IMPORTANCE='".$importance."';";

	$qid=db_query($query,$dbh);

	while ($valuerow=db_fetch_array($qid)){
		$value_id=$valuerow["VL_ID"];
		if ($valuerow["VL_CARDINALITY"]==1){
			$res=validate_field($pc_id,$value_id,$dbh,"GENERAL",$importance);
			if (!$res[0]){
				$result[0]=false;
				$result[1]="At least the field ".$valuerow["VL_CAPTION"]." is empty unexpectedly.";
				return $result;
			}
		}else{
			$res=validate_field($pc_id,$value_id,$dbh,"RE",$importance);
			if (!$res[0]){
				$result[0]=false;
				$result[1]="At least the field ".$valuerow["VL_CAPTION"]." for Right Eye is empty unexpectedly.";
				return $result;
			}
			$res=validate_field($pc_id,$value_id,$dbh,"LE",$importance);
			if (!$res[0]){
				$result[0]=false;
				$result[1]="At least the field ".$valuerow["VL_CAPTION"]." for Left Eye is empty unexpectedly.";
				return $result;
			}
		}

	}
	$res[0]=true;
	return $res;
}



//----------------------------------------------------------------
function validate_field($pc_id,$value_id,$dbh,$target="GENERAL",$required="1")
{
	unset($result);
	$result=array();
	$query="SELECT * FROM values_list WHERE VL_ID='".$value_id."';";

	$valuerow=db_fetch_array(db_query($query,$dbh,"","","","Επιλέγω τα χαρακτηριστικά του πεδίου για έλεγχο πληρότητας."));

	switch ($valuerow["VL_TYPE"])
	{
		case "DATES":
		case "FREETEXT":
		case "TEXTAREA":
		$query="SELECT * FROM text_answers WHERE TA_VL_ID='$value_id' and TA_TARGET='$target' and TA_CONTACT_ID=".$pc_id." ;";
		$qidfieldstatus=db_query($query,$dbh,"","","","Επιλέγω τις απαντήσεις για συγκεκριμένο πεδίο για έλεγχο πληρότητας.");
		break;
		case "ENUMERATED":
		$query="SELECT * FROM enum_answers WHERE EA_VL_ID='$value_id' and EA_TARGET='$target' and EA_CONTACT_ID=".$pc_id." ;";
		$qidfieldstatus=db_query($query,$dbh,"","","","Επιλέγω τις απαντήσεις για συγκεκριμένο πεδίο για έλεγχο πληρότητας.");

		break;
		case "NUMERICAL":
		$query="SELECT * FROM num_answers WHERE NA_VL_ID='$value_id' and NA_TARGET='$target' and NA_CONTACT_ID=".$pc_id." ;";
		$qidfieldstatus=db_query($query,$dbh,"","","","Επιλέγω τις απαντήσεις για συγκεκριμένο πεδίο για έλεγχο πληρότητας.");
		break;
		case "BINARY":
		$query="SELECT `BA_CONTACT_ID`,`BA_VL_ID`,`BA_FTYPE`,`BA_TARGET` FROM bin_answers WHERE BA_VL_ID='$value_id' and BA_TARGET='$target' and BA_CONTACT_ID=".$pc_id." ;";
		$qidfieldstatus=db_query($query,$dbh,"","","","Επιλέγω τις απαντήσεις για συγκεκριμένο πεδίο για έλεγχο πληρότητας.");
		break;
		default:
		$result[0]=true;
		$result[1]="Field title";
		return $result;
	}

	if ($row=db_fetch_array($qidfieldstatus)){ //einai symplhrwmeno
		
	if ((($valuerow["VL_TYPE"]=="ENUMERATED")&&($row["EA_VALUE"]!=101))||(($valuerow["VL_TYPE"]=="DATES" || $valuerow["VL_TYPE"]=="FREETEXT" || $valuerow["VL_TYPE"]=="TEXTAREA")&&(trim($row["TEXT"])!=""))||(($valuerow["VL_TYPE"]=="NUMERICAL")&&(isset($row["NA_VALUE"])))||(($valuerow["VL_TYPE"]=="BINARY")&&($row["BA_FTYPE"]!="empty"))){
		$result[0]=true;
		$result[1]="Field Filled";//na mhn allaxtei ayto to string giati ginete elegxos me ayto..
		return $result;}else{
			//$errormsg="Το πεδίο είναι κενό ή έχει τιμή empty";
                    $errormsg="Field is not filled or it is empty";
		}
	}



	if ($valuerow["VL_REQUIRED"]!=$required){//mhpws den einai aparaithto?
	$result[0]=true;
	$result[1]="Field not required";
	return $result;
	}

	//an ftaseis ws edw, oxi symplhrwmeno  ara psa3e dependencies

	$query="SELECT * FROM dependencies where `DE_VL_ID`='$value_id' and `DE_VL_TARGET`='$target';";
	$qiddependencies=db_query($query,$dbh);
	$rowofdependencies=db_fetch_array($qiddependencies);

	if (!$rowofdependencies){
		$result[0]=false;
		//$result[1]="Είναι κενό";
                $result[1]="is empty";
		return $result;

	}

	//exei dependencies, as ta elg3oume...

	do {
		$query="SELECT * FROM values_list WHERE VL_ID='".$rowofdependencies["DE_LI_VL_ID"]."';";

		$limiterow=db_fetch_array(db_query($query,$dbh));

		switch ($limiterow["VL_TYPE"])
		{

			case "ENUMERATED":
			$query="SELECT * FROM enum_answers WHERE EA_VL_ID='".$rowofdependencies["DE_LI_VL_ID"]."' and EA_TARGET='".$rowofdependencies["DE_LI_VL_TARGET"]."' and EA_CONTACT_ID=".$pc_id." ;";
			$qidfieldstatus=db_query($query,$dbh);

			if (!($rowoflimeteranswer=db_fetch_array($qidfieldstatus))){//an den exei parei apanthsh gia to pedio limiter
			//$errormsg="Αποτυχία εύρεσης πεδίου περιορισμού";
                            $errormsg="Failed to find target field";
			}else{
				if ($rowoflimeteranswer["EA_VALUE"]==$rowofdependencies["DE_EA_AL_ID"]){
					$result[0]=true;
					$result[1]="Το πεδιο είναι κενό επειδή επηρεάζεται απο άλλο και δεν χρειάζεται(σαν να μην είναι κενό).";
					return $result;

				}else {
					//$errormsg="Είναι κενό, υπάρχει κάτι σχετικό αλλά δεν είναι ορθώς κενό";
                                    $errormsg="Field is empty, it is part of dependency rule but it does not relax restriction";
                                }
			}
			break;
			case "NUMERICAL":
			$query="SELECT * FROM num_answers WHERE NA_VL_ID='".$rowofdependencies["DE_LI_VL_ID"]."' and NA_TARGET='".$rowofdependencies["DE_LI_VL_TARGET"]."' and NA_CONTACT_ID=".$pc_id." ;";
			$qidfieldstatus=db_query($query,$dbh);
			if (!($rowoflimeteranswer=db_fetch_array($qidfieldstatus))){//an den exei parei apanthsh gia to pedio limiter
			//$errormsg="Αποτυχία εύρεσης πεδίου περιορισμού";
                            $errormsg="Error finding target field";
			}else{

				//elegxos gia diasthma
				$string = $rowofdependencies["DE_NUM_ANS"];
				/* Use coma , as tokenizing characters as well  */
				$tok = strtok($string, ",");
				$lower=$tok;
				while ($tok) {
					$upper=$tok;
					$tok = strtok(",");
				}
				if (($rowoflimeteranswer["NA_VALUE"]>$lower)and($rowoflimeteranswer["NA_VALUE"]<$upper)){
					$result[0]=true;
					$result[1]="Το πεδιο είναι κενό επειδή επηρεάζεται απο άλλο και δεν χρειάζεται(σαν να μην είναι κενό).";
					return $result;
				}else{
					//$errormsg="Κακώς κενό, υπάρχει όριο σε κάποιο άλλο αριθμητικό πεδίο αλλά δεν είναι μέσα στο όριο";
                                        $errormsg="Field empty, there is a numerical dependency but it does not have the appropriate value to relax restriction ";
				}
			}
			break;

			default:
			$result[0]=false;
			$result[1]="Failed, incorect form of dependency";
			return $result;

		}




	}while($rowofdependencies=db_fetch_array($qiddependencies));
	$result[0]=false;
	$result[1]=$errormsg;
	return $result;
}


function validate_category($pc_id,$c_id,$dbh)
{

	$result=array();
	$result[0]=true;
	$query="SELECT `VL_ID`,`VL_CAPTION`,`VL_CARDINALITY` FROM values_list where `VL_CATEGORY`=$c_id order by `VL_ASKORDER` asc;";
	$qid=db_query($query,$dbh,"","","","Επιλέγω τα πεδία της καθε κατηγορίας για έλεγχο πληρότητας.");
	while ($row=db_fetch_array($qid)) {

		if ($row["VL_CARDINALITY"]==1)
		{
			$validation=validate_field($pc_id,$row["VL_ID"],$dbh,"GENERAL");
			if (!$validation[0]){ $result[0]=false;$result[1][$row["VL_CAPTION"]]=$validation[1];}

		}else{

			$validation=validate_field($pc_id,$row["VL_ID"],$dbh,"RE");
			if (!$validation[0]){ $result[0]=false;$result[1][$row["VL_CAPTION"]." - RE"]=$validation[1];}

			$validation=validate_field($pc_id,$row["VL_ID"],$dbh,"LE");
			if (!$validation[0]){ $result[0]=false;$result[1][$row["VL_CAPTION"]." - LE"]=$validation[1];}
		}


	}
	return $result;
}

function validate_list_of_forms_item($pc_id,$c_id,$dbh)
{
	/*
	returns
	0 -> green empty form
	1 -> orange half form
	2 -> red fully complete form
         * 3 -> mandatory fields filled
	*/
	$result=0;
	$sumofright=0;
	$sumofwrong=0;
$sumofnotrequired=0;
	$query="SELECT `VL_ID`,`VL_TYPE`,`VL_CAPTION`,`VL_CARDINALITY` FROM values_list where `VL_CATEGORY`=$c_id order by `VL_ASKORDER` asc;";
	$qid=db_query($query,$dbh);
	while ($row=db_fetch_array($qid)) {

		//print_r($validation);
		if (($row["VL_TYPE"]=="ENUMERATED")||($row["VL_TYPE"]=="NUMERICAL")||($row["VL_TYPE"]=="BINARY")||($row["VL_TYPE"]=="FREETEXT")||($row["VL_TYPE"]=="TEXTAREA")||($row["VL_TYPE"]=="DATES")){
			if ($row["VL_CARDINALITY"]==1)
			{
				$validation=validate_field($pc_id,$row["VL_ID"],$dbh,"GENERAL");
				if ($validation[0]){
                                    if ($validation[1]=="Field Filled"){

				
					$sumofright++;
                                    }else{
                                        $sumofnotrequired++;
                                    }
				}else{

					$sumofwrong++;
				}

			}elseif ($row["VL_CARDINALITY"]==2){
				$validation=validate_field($pc_id,$row["VL_ID"],$dbh,"LE");
				if ($validation[0]){
                                    if ($validation[1]=="Field Filled"){

				
					$sumofright++;
                                    }else{
                                        $sumofnotrequired++;
                                    }
				}else{

					$sumofwrong++;
				}
								$validation=validate_field($pc_id,$row["VL_ID"],$dbh,"RE");
				if ($validation[0]){
                                    if ($validation[1]=="Field Filled"){

				
					$sumofright++;
                                    }else{
                                        $sumofnotrequired++;
                                    }
				}else{

					$sumofwrong++;
				}
			}
		}
		//print "ole".$row["VL_TYPE"]."-".$row["VL_ID"]; print_r($validation);
	}
	if (($sumofright!=0)&&($sumofwrong==0)){$result=2;}
        	if (($sumofright!=0)&&($sumofnotrequired!=0)&&($sumofwrong==0)){$result=3;}
	if (($sumofright!=0)&&($sumofwrong!=0)){$result=1;}

	
	
//	print "sum of right ".$sumofright." sum of wrong ".$sumofwrong." result=".$result; //debug
	
	

	return $result;
}
function show_cat_result($res,$noprint=false){

	if (!$res[0])
	{
		if ($noprint){ $result.="<UL>";}else{
		print "<UL>";}
		
		foreach ($res[1] as $key => $value){
		if ($noprint){ $result.="<h3>$key, $value</h3>\n";}else{
			print "<li>Problem in the field: <B>$key</b>($value)</li>\n";
																					}
		}
		if ($noprint){ $result.="</UL>";}else{
		print "</UL>";}
	}else{
	if ($noprint){ $result.="<ul><li>Φόρμα συμπληρωμένη</ul>";}else{
		print "<ul><li>Form OK</ul>";
																	}
	}
if ($noprint){return $result;}
	}

// select category details syntax gg_c_description(c_id) returns description(string)
function gg_c_description($cid,$link_id)
{
	$query="SELECT C_DESCRIPTION FROM categories WHERE C_ID=".$cid;
	$qid=db_query($query,$link_id);
	$row=db_fetch_array($qid);
	return $row["C_DESCRIPTION"];
}
function redirect($url, $message="", $delay=0) {
	/* redirects to a new URL using meta tags */
    if ($url=='CLOSE'){
        if (!empty($message)) echo "<br><br><H1>$message</H1>";
        print '<script type="text/javascript">setTimeout("window.close();",'.($delay*1000).');</script>';
    }else{
	echo "<meta http-equiv='Refresh' content='$delay; url=$url'>";
	if (!empty($message)) echo "<br><br><H1>$message</H1>";
    }
	die;
}
function strip_querystring($url) {
	/* takes a URL and returns it without the querystring portion */

	if ($commapos = strpos($url, '?')) {
		return substr($url, 0, $commapos);
	} else {
		return $url;
	}
}
function show_warnings($errors = '') {



	if ($errors) {
		print '<h2>Form has been updated. The following empty fields have been discovered:</h2> <ul>';
		print '<li>';
		print implode('</li><li>',$errors);

		print '</li></ul>';

	}

}
function validate_postfile($my_file,$my_desc ) {

	



	if ($_FILES[$my_file]['error'] == UPLOAD_ERR_INI_SIZE){
$errors = 'Μέγεθος αρχείου μεγαλύτερο από το επιτρεπόμενο ('.ini_get('upload_max_filesize').') για '.$my_desc;
		//$errors[  ] = 'Uploaded file is too big for: '.$my_desc;

	} elseif ($_FILES[$my_file]['error'] == UPLOAD_ERR_FORM_SIZE) {

		//$errors[  ] = 'Uploaded file is too big for '.$my_desc;
		$errors = 'Μέγεθος αρχείου μεγαλύτερο από το επιτρεπόμενο στη φόρμα για '.$my_desc;

	} elseif ($_FILES[$my_file]['error'] == UPLOAD_ERR_PARTIAL) {
		//$errors[  ] = 'File upload was interrupted for '.$my_desc;
		$errors = 'Διακόπηκε το ανέβασμα του αρχείου για '.$my_desc;


	}elseif ($_FILES[$my_file]['size'] == 0) {

		//$errors[  ] = 'No file selected or zero size file for '.$my_desc;
				$errors = 'Δεν επιλέχθηκε αρχείο για '.$my_desc;

	}
	if ($errors) {
		return $errors;

	}

	return;
}
function maxupload(){
	return min(return_bytes(ini_get('post_max_size')),return_bytes(ini_get('upload_max_filesize')),return_bytes(ini_get('memory_limit')));
}
function getfilesize($size) {
	//if ($size < 2) return "$size byte";
	$units = array(' B', ' KiB', ' MiB', ' GiB', ' TiB');
	for ($i = 0; $size > 1024; $i++) { $size /= 1024; }
	return round($size, 2).$units[$i];
}

function return_bytes($val) {
	$val = trim($val);
	if (empty($val)) return pow(1024,3);
	$last = strtolower($val{(strlen($val)-1)});
	switch($last) {
		// The 'G' modifier is available since PHP 5.1.0
		case 'g':
		$val *= 1024;
		case 'm':
		$val *= 1024;
		case 'k':
		$val *= 1024;
	}
	return $val;
}
function checkresult($r1,$r2){
/*
Vassilis Kilintzis 15/6/2007
*/
require_once("sqltableclass-jssort.php");
$nr1=mysql_num_rows($r1);
$nr2=mysql_num_rows($r2);

print "<p>The following fields/values differ between the two results.</p>";
print "<table border=\"2\">";
$i=1;
while (($row1=mysql_fetch_assoc($r1))&& ($row2=mysql_fetch_assoc($r2)))
{

$dif=array_diff_assoc($row1,$row2);
if(!$dif){
		$okrows++;
		 }else{
		$notokrows++;
print "<tr><td colspan=".sizeof($row1).">Difference {$notokrows}</td></tr><tr bgcolor='#FFF3C6'>";
print "<tD>";
print implode("</td><td>",$row1);
print "</tD></tr><tr bgcolor='#FFCC00'><td>";
print implode("</td><td>",$row2);
print "</tD>";
print "</tr>";
}


}
print "</table>";
print "<P>From compared ".($okrows+$notokrows)." rows ".$okrows." were found identical.</p>";
			if ($nr1!=$nr2){
									print "<p>Results do not have the same number of rows {$nr1} vs {$nr2} here what exceeds.</p>";
									if ($nr1>$nr2){
									$r=$r1;
									mysql_data_seek($r,$nr2);
									}else
									{
									$r=$r2;

									mysql_data_seek($r,$nr1);
									}	
									
$tbl=new sqlresult;									 
 $tbl->displaytable($r,'',20);								
				 }
}
function array_push_associative(&$arr) {
   $args = func_get_args();
   foreach ($args as $arg) {
       if (is_array($arg)) {
           foreach ($arg as $key => $value) {
               $arr[$key] = $value;
               $ret++;
           }
       }else{
           $arr[$arg] = "";
       }
   }
   return $ret;
}
?>