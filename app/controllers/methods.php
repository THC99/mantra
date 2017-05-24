<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
ver:1.99y
*/


function resetpagemethods(){
	return   array("servicename"=>"",
				   "serviceid"=>"",
				   "methodname"=>"",
				   "restricted"=>"",
				   "accesskey"=>"",
				   "methodtype"=>"",
				   "dbtype"=>"",
				   "hostname"=>"",
				   "port"=>"",
				   "dbuser"=>"",
				   "dbpass"=>"",
				   "dbname"=>"",
				   "tbname"=>"",
		       "columns"=>"",
		       "conditions"=>"",
		       "orders"=>"",
				   "limoffset"=>"",
		       "limrows"=>"",
				   "sql"=>"",
				   "code"=>"",
				   "sourcecode"=>"",
				   "instance"=>"",
		       "method"=>"",
		       "endpoint"=>"",
		       "wstype"=>"",
				   "reqname"=>"",
				   "reqtype"=>"",
				   "reqvalue"=>"",
				   "reqlist"=>"",
				   "request"=>"",
				   "reqlistln"=>"",
				   "requestln"=>"",
				   "descript"=>"");

}

function pagemethods($interface){
	global $db,$urikeys,$puri,$interfaces,$validLogin;
	if(!$validLogin){
		header('Location: '.home_url().'masuk');
		return;
	}
	if(!is_provider() && !is_publisher()) return;
	?>
	<p style='padding:0 4px;font-size:28px;font-weight:bold;'>Fungsi Operasi</p>
	<?php
	tabmenu();
	$interfaceuri=home_url().$interface."/";
	$hasFinished=false;
	$currInstance="";
	$currRole="";
	if(is_provider() || is_publisher()){
	 	$currInstance=current_instance(current_instanceid());
		$currRole=current_role();
	}
	
	if(isset($puri["action"]) && $puri["action"]=="list") $puri["action"]="";
	if(isset($puri["action"]) && !empty($puri["action"])){
		$actionpage=$puri["action"];
		$message=$dbcaption=$dblocation=$dbtypefocus=$methodtypefocus=$tbname=$col=$columnlst=$whr=$whrlst=$ord=$ordlst="";
		$dbnames=$tbnames=$clnames=$colnames=array();
		$vdialog=resetpagemethods(); //init scope
		$vdialog["restricted"]="off";
		
		//----------------- Initialize ServiceName, Provider, URL -----------------------\\
		$services=getServices($currInstance,$currRole); //get Services Information
		foreach($services as $service){	
			if($vdialog["serviceid"]==$service["id"]){
				$vdialog["instance"]=$service["instance"];
				$vdialog["servicename"]=$service["servicename"];
			}
		}

		//------------------------ Confirm Form ---------------------------\\
		if((strtoupper($_SERVER['REQUEST_METHOD'])=='POST')
		&&(isset($_POST[session_name()])) 
		&&($_SERVER['HTTP_USER_AGENT']==decsay($_POST[session_name()],$_SESSION['idform']))
		&&(isset($_POST["f_dialog"]))){
			
			$vdialog=array_merge($vdialog,$_POST["f_dialog"]);
			if(isset($vdialog["opendb"])){ // tombol buka database
				if($vdialog["methodtype"]=="database"){
					$host=$vdialog['hostname'];
					if($vdialog['dbtype']=="mysqli") $vdialog['port']="";
					if($vdialog['dbtype']=="mssql" && strrpos($host,"\\")!==false ){
						$vdialog['port']="";
					}
					if($vdialog['port']!="") $host.=":".$vdialog['port'];
					$dbnames=getDBNames($vdialog["dbtype"],$host,$vdialog["dbuser"],$vdialog["dbpass"]);
					$vdialog["clnameoptions"]='';
					$vdialog["dbname"]=$vdialog["tbname"]=$vdialog["columns"]=$vdialog["conditions"]=$vdialog["orders"]="";
					$vdialog["sourcecode"]=write_opds($vdialog["dbtype"],$vdialog["hostname"],$vdialog["port"],$vdialog["dbuser"],$vdialog["dbpass"],$vdialog["dbname"],$vdialog["tbname"],$vdialog["columns"],$vdialog["conditions"],$vdialog["orders"],$vdialog["limrows"],$vdialog["limoffset"]);
				}
			}
			elseif(isset($vdialog["opentb"])){ // tombol buka tabel data
				if($vdialog["methodtype"]=="database"){
					$host=$vdialog['hostname'];
					if($vdialog['dbtype']=="mysqli") $vdialog['port']="";
					if($vdialog['dbtype']=="mssql" && strrpos($host,"\\")!==false ){
						$vdialog['port']="";
					}
					if($vdialog['port']!="") $host.=":".$vdialog['port'];
					$dbnames=getDBNames($vdialog["dbtype"],$host,$vdialog["dbuser"],$vdialog["dbpass"]);
					$tbnames=getTableNames($vdialog["dbtype"],$host,$vdialog["dbuser"],$vdialog["dbpass"],$vdialog["dbname"]);
					$vdialog["clnameoptions"]='';
					$vdialog["tbname"]=$vdialog["columns"]=$vdialog["conditions"]=$vdialog["orders"]="";
					$vdialog["sourcecode"]=write_opds($vdialog["dbtype"],$vdialog["hostname"],$vdialog["port"],$vdialog["dbuser"],$vdialog["dbpass"],$vdialog["dbname"],$vdialog["tbname"],$vdialog["columns"],$vdialog["conditions"],$vdialog["orders"],$vdialog["limrows"],$vdialog["limoffset"]);
				}
			}
			elseif(isset($vdialog["opencl"])){ // tombol buka kolom data
				if($vdialog["methodtype"]=="database"){
					$host=$vdialog['hostname'];
					if($vdialog['dbtype']=="mysqli") $vdialog['port']="";
					if($vdialog['dbtype']=="mssql" && strrpos($host,"\\")!==false ){
						$vdialog['port']="";
					}
					if($vdialog['port']!="") $host.=":".$vdialog['port'];
					$dbnames=getDBNames($vdialog["dbtype"],$host,$vdialog["dbuser"],$vdialog["dbpass"]);
					$tbnames=getTableNames($vdialog["dbtype"],$host,$vdialog["dbuser"],$vdialog["dbpass"],$vdialog["dbname"]);
					$clnames=getColumnNames($vdialog["dbtype"],$host,$vdialog["dbuser"],$vdialog["dbpass"],$vdialog["dbname"],$vdialog["tbname"]);
					$vdialog["columns"]=$vdialog["conditions"]=$vdialog["orders"]="";
					$vdialog["sourcecode"]=write_opds($vdialog["dbtype"],$vdialog["hostname"],$vdialog["port"],$vdialog["dbuser"],$vdialog["dbpass"],$vdialog["dbname"],$vdialog["tbname"],$vdialog["columns"],$vdialog["conditions"],$vdialog["orders"],$vdialog["limrows"],$vdialog["limoffset"]);
				}
			}
			elseif(isset($vdialog['methodtypeselected']) && !empty($vdialog['methodtypeselected'])){
				if($vdialog['methodtypeselected']=="program"){
				}
				elseif($vdialog['methodtypeselected']=="database"){
					$vdialog['dbtype']='mysql';
					$vdialog['hostname']='localhost';
					$vdialog['port']='3306';
					$vdialog['dbuser']='user';
					$vdialog["clnameoptions"]='';
					$vdialog["dbpass"]=$vdialog["dbname"]=$vdialog["tbname"]=$vdialog["columns"]=$vdialog["conditions"]=$vdialog["orders"]=$vdialog["limrows"]=$vdialog["limoffset"]="";
					$vdialog["sourcecode"]=write_opds($vdialog["dbtype"],$vdialog["hostname"],$vdialog["port"],$vdialog["dbuser"],$vdialog["dbpass"],$vdialog["dbname"],$vdialog["tbname"],$vdialog["columns"],$vdialog["conditions"],$vdialog["orders"],$vdialog["limrows"],$vdialog["limoffset"]);
				}
				elseif($vdialog['methodtypeselected']=="services"){
				}
				$methodtypefocus='autofocus="autofocus"';
			}
			elseif(isset($vdialog['dbtypeselected']) && !empty($vdialog['dbtypeselected'])){
				$dbtype=$vdialog['dbtypeselected'];
				if($dbtype=='mysql') {
					$vdialog['hostname']='localhost';
					$vdialog['port']='3306';
					$vdialog['dbuser']='user';
				}
				if($dbtype=='mysqli') {
					$vdialog['hostname']='localhost';
					$vdialog['port']='';
					$vdialog['dbuser']='user';
				}
				if($dbtype=='oci8') {
					$vdialog['hostname']='localhost';
					$vdialog['port']='1521';
					$vdialog['dbuser']='user';
				}
				if($dbtype=='postgres') {
					$vdialog['hostname']='localhost';
					$vdialog['port']='5432';
					$vdialog['dbuser']='user';
				}
				if($dbtype=='mssql') {
					$vdialog['hostname']='localhost';
					$vdialog['port']='1443';
					$vdialog['dbuser']='user';
				}
				$vdialog["clnameoptions"]='';
				$vdialog["dbpass"]=$vdialog["dbname"]=$vdialog["tbname"]=$vdialog["columns"]=$vdialog["conditions"]=$vdialog["orders"]=$vdialog["limrows"]=$vdialog["limoffset"]="";
				$vdialog["sourcecode"]=write_opds($vdialog["dbtype"],$vdialog["hostname"],$vdialog["port"],$vdialog["dbuser"],$vdialog["dbpass"],$vdialog["dbname"],$vdialog["tbname"],$vdialog["columns"],$vdialog["conditions"],$vdialog["orders"],$vdialog["limrows"],$vdialog["limoffset"]);
				$dbtypefocus='autofocus="autofocus"';
			}
			elseif(isset($vdialog["allcolumn"])){ 
				if(isset($vdialog["allcolnames"]) && !empty($vdialog["allcolnames"])){
					$vdialog["columns"]=$vdialog["allcolnames"];
					$vdialog["sourcecode"]=write_opds($vdialog["dbtype"],$vdialog["hostname"],$vdialog["port"],$vdialog["dbuser"],$vdialog["dbpass"],$vdialog["dbname"],$vdialog["tbname"],$vdialog["columns"],$vdialog["conditions"],$vdialog["orders"],$vdialog["limrows"],$vdialog["limoffset"]);
				}
			}
			elseif(isset($vdialog["addcolumn"])){
				if(isset($vdialog["clnameoption"]) && is_array($vdialog["clnameoption"])){
					$columns=array();
					if(!empty($vdialog["columns"])) $columns=explode(",",$vdialog["columns"]);
					foreach($vdialog["clnameoption"] as $value){
						if(!in_array($value,$columns)){
							$columns[]=$value;
						}
					}
					$vdialog["columns"]=implode(",",$columns);
					$vdialog["sourcecode"]=write_opds($vdialog["dbtype"],$vdialog["hostname"],$vdialog["port"],$vdialog["dbuser"],$vdialog["dbpass"],$vdialog["dbname"],$vdialog["tbname"],$vdialog["columns"],$vdialog["conditions"],$vdialog["orders"],$vdialog["limrows"],$vdialog["limoffset"]);
				}
			}
			elseif(isset($vdialog["delcolumn"])){
				if(isset($vdialog["setcolumn"]) && !empty($vdialog["setcolumn"]) && isset($vdialog["columns"]) && !empty($vdialog["columns"])){
					$coldel=$vdialog["setcolumn"];
					$columns=explode(",",$vdialog["columns"]);
					if(!empty($columns)){
						$columns=array_diff($columns,$coldel);
						$vdialog["columns"]=implode(",",$columns);
					}
					$vdialog["sourcecode"]=write_opds($vdialog["dbtype"],$vdialog["hostname"],$vdialog["port"],$vdialog["dbuser"],$vdialog["dbpass"],$vdialog["dbname"],$vdialog["tbname"],$vdialog["columns"],$vdialog["conditions"],$vdialog["orders"],$vdialog["limrows"],$vdialog["limoffset"]);
				}
			}
			elseif(isset($vdialog["addwhere"])){
				if(isset($vdialog["conditions"]) && isset($vdialog["colname"]) && isset($vdialog["coltype"]) && isset($vdialog["colcompare"]) && isset($vdialog["colinput"]) && empty($vdialog["conditions"]) && !empty($vdialog["colname"])){
					$inputvalue=($vdialog["coltype"]=="numeric" && !empty($vdialog["colinput"]))?$vdialog["colinput"]:"'".$vdialog["colinput"]."'";
					$condition="WHERE ".$vdialog["colname"]." ".$vdialog["colcompare"]." ".$inputvalue;
					$whrlst=$vdialog["conditions"]==""?array():explode("@@",$vdialog["conditions"]);
					if(!in_array($condition,$whrlst)){
						$whrlst[]=$condition;
						$vdialog["conditions"]=implode("@@",$whrlst);
						$vdialog["sourcecode"]=write_opds($vdialog["dbtype"],$vdialog["hostname"],$vdialog["port"],$vdialog["dbuser"],$vdialog["dbpass"],$vdialog["dbname"],$vdialog["tbname"],$vdialog["columns"],$vdialog["conditions"],$vdialog["orders"],$vdialog["limrows"],$vdialog["limoffset"]);
					}
				}
			}
			elseif(isset($vdialog["andwhere"])){
				if(isset($vdialog["conditions"]) && isset($vdialog["colname"]) && isset($vdialog["coltype"]) && isset($vdialog["colcompare"]) && isset($vdialog["colinput"]) && !empty($vdialog["conditions"]) && !empty($vdialog["colname"])){
					$inputvalue=($vdialog["coltype"]=="numeric" && !empty($vdialog["colinput"]))?$vdialog["colinput"]:"'".$vdialog["colinput"]."'";
					$condition="AND ".$vdialog["colname"]." ".$vdialog["colcompare"]." ".$inputvalue;
					$whrlst=$vdialog["conditions"]==""?array():explode("@@",$vdialog["conditions"]);
					if(!in_array($condition,$whrlst)){
						$whrlst[]=$condition;
						$vdialog["conditions"]=implode("@@",$whrlst);
						$vdialog["sourcecode"]=write_opds($vdialog["dbtype"],$vdialog["hostname"],$vdialog["port"],$vdialog["dbuser"],$vdialog["dbpass"],$vdialog["dbname"],$vdialog["tbname"],$vdialog["columns"],$vdialog["conditions"],$vdialog["orders"],$vdialog["limrows"],$vdialog["limoffset"]);
					}
				}
			}
			elseif(isset($vdialog["orwhere"])){
				if(isset($vdialog["conditions"]) && isset($vdialog["colname"]) && isset($vdialog["coltype"]) && isset($vdialog["colcompare"]) && isset($vdialog["colinput"]) && !empty($vdialog["conditions"]) && !empty($vdialog["colname"])){
					$inputvalue=($vdialog["coltype"]=="numeric" && !empty($vdialog["colinput"]))?$vdialog["colinput"]:"'".$vdialog["colinput"]."'";
					$condition="OR ".$vdialog["colname"]." ".$vdialog["colcompare"]." ".$inputvalue;
					$whrlst=$vdialog["conditions"]==""?array():explode("@@",$vdialog["conditions"]);
					if(!in_array($condition,$whrlst)){
						$whrlst[]=$condition;
						$vdialog["conditions"]=implode("@@",$whrlst);
						$vdialog["sourcecode"]=write_opds($vdialog["dbtype"],$vdialog["hostname"],$vdialog["port"],$vdialog["dbuser"],$vdialog["dbpass"],$vdialog["dbname"],$vdialog["tbname"],$vdialog["columns"],$vdialog["conditions"],$vdialog["orders"],$vdialog["limrows"],$vdialog["limoffset"]);
					}
				}
			}
			elseif(isset($vdialog["delwhere"])){
				if(isset($vdialog["wherelist"]) && !empty($vdialog["wherelist"]) && isset($vdialog["conditions"]) && !empty($vdialog["conditions"])){
					$whrdel=$vdialog["wherelist"];//echo var_export($whrdel,true)."<br/>";echo $vdialog["colname"]."<br/>";
					$whrlst=explode("@@",$vdialog["conditions"]);//echo var_export($whrlst,true)."<br/>";
					$whrlst=array_diff($whrlst,$whrdel);//echo var_export($whrlst,true)."<br/>";
					reset($whrlst);$whrkey=key($whrlst);
					if(count($whrlst)>0 && strpos($whrlst[$whrkey],"WHERE ")===false){
						if(strpos($whrlst[$whrkey],"AND ")==0) $whrlst[$whrkey]=str_replace("AND","WHERE",$whrlst[$whrkey]);
						if(strpos($whrlst[$whrkey],"OR ")==0) $whrlst[$whrkey]=str_replace("OR","WHERE",$whrlst[$whrkey]);
					}
					
					$vdialog["conditions"]=implode("@@",$whrlst);
					$vdialog["sourcecode"]=write_opds($vdialog["dbtype"],$vdialog["hostname"],$vdialog["port"],$vdialog["dbuser"],$vdialog["dbpass"],$vdialog["dbname"],$vdialog["tbname"],$vdialog["columns"],$vdialog["conditions"],$vdialog["orders"],$vdialog["limrows"],$vdialog["limoffset"]);
				}
			}
			elseif(isset($vdialog["addorder"])){
				if(isset($vdialog["orders"]) && isset($vdialog["colorder"]) && isset($vdialog["colseq"]) && !empty($vdialog["colorder"])){
					$order=$vdialog["colorder"]." ".$vdialog["colseq"];
					$ordlst=$vdialog["orders"]==""?array():explode(",",$vdialog["orders"]);
					if(!in_array($order,$ordlst)){
						$ordlst[]=$order;
						$vdialog["orders"]=implode(",",$ordlst);
						$vdialog["sourcecode"]=write_opds($vdialog["dbtype"],$vdialog["hostname"],$vdialog["port"],$vdialog["dbuser"],$vdialog["dbpass"],$vdialog["dbname"],$vdialog["tbname"],$vdialog["columns"],$vdialog["conditions"],$vdialog["orders"],$vdialog["limrows"],$vdialog["limoffset"]);
					}
				}
			}
			elseif(isset($vdialog["delorder"])){
				if(isset($vdialog["orderlist"]) && !empty($vdialog["orderlist"]) && isset($vdialog["orders"]) && !empty($vdialog["orders"])){
					$orddel=$vdialog["orderlist"];
					$ordlst=explode(",",$vdialog["orders"]);
					$ordlst=array_diff($ordlst,$orddel);
					$vdialog["orders"]=implode(",",$ordlst);
					$vdialog["sourcecode"]=write_opds($vdialog["dbtype"],$vdialog["hostname"],$vdialog["port"],$vdialog["dbuser"],$vdialog["dbpass"],$vdialog["dbname"],$vdialog["tbname"],$vdialog["columns"],$vdialog["conditions"],$vdialog["orders"],$vdialog["limrows"],$vdialog["limoffset"]);
				}
			}
			elseif(isset($vdialog["setlimit"])){
				$vdialog["sourcecode"]=write_opds($vdialog["dbtype"],$vdialog["hostname"],$vdialog["port"],$vdialog["dbuser"],$vdialog["dbpass"],$vdialog["dbname"],$vdialog["tbname"],$vdialog["columns"],$vdialog["conditions"],$vdialog["orders"],$vdialog["limrows"],$vdialog["limoffset"]);
			}
			elseif(isset($vdialog["addreqln"])){
				if(isset($vdialog["requestln"]) && isset($vdialog["reqnameln"]) && isset($vdialog["reqtypeln"]) && isset($vdialog["reqvalueln"]) && !empty($vdialog["reqnameln"])){
					$inputvalue=($vdialog["reqtypeln"]=="numeric" && !empty($vdialog["reqvalueln"]))?$vdialog["reqvalueln"]:"'".$vdialog["reqvalueln"]."'";
					$reqtxt=$vdialog["reqnameln"].":".$vdialog["reqtypeln"]."=".$inputvalue;
					$reqlst=$vdialog["requestln"]==""?array():explode(",",$vdialog["requestln"]);
					if(!in_array($reqtxt,$reqlst)){
						$reqlst[]=$reqtxt;
						$vdialog["requestln"]=implode(",",$reqlst);
						$vdialog["sourcecode"]=write_opln($vdialog["code"],$vdialog["requestln"]);
					}
				}
			}
			elseif(isset($vdialog["delreqln"])){
				if(isset($vdialog["reqlistln"]) && !empty($vdialog["reqlistln"]) && isset($vdialog["requestln"]) && !empty($vdialog["requestln"])){
					$reqdel=$vdialog["reqlistln"];
					$reqlst=explode(",",$vdialog["requestln"]);
					$reqlst=array_diff($reqlst,$reqdel);
					$vdialog["requestln"]=implode(",",$reqlst);
					$vdialog["sourcecode"]=write_opln($vdialog["code"],$vdialog["requestln"]);
				}
			}
			elseif(isset($vdialog["addrequest"])){
				if(isset($vdialog["request"]) && isset($vdialog["reqname"]) && isset($vdialog["reqtype"]) && isset($vdialog["reqvalue"]) && !empty($vdialog["reqname"])){
					$inputvalue=($vdialog["reqtype"]=="numeric" && !empty($vdialog["reqvalue"]))?$vdialog["reqvalue"]:"'".$vdialog["reqvalue"]."'";
					$reqtxt=$vdialog["reqname"].":".$vdialog["reqtype"]."=".$inputvalue;
					$reqlst=$vdialog["request"]==""?array():explode(",",$vdialog["request"]);
					if(!in_array($reqtxt,$reqlst)){
						$reqlst[]=$reqtxt;
						$vdialog["request"]=implode(",",$reqlst);
						$vdialog["sourcecode"]=write_opws($vdialog["wstype"],$vdialog["endpoint"],$vdialog["method"],$vdialog["accesskey"],$vdialog["request"]);
					}
				}
			}
			elseif(isset($vdialog["delrequest"])){
				if(isset($vdialog["reqlist"]) && !empty($vdialog["reqlist"]) && isset($vdialog["request"]) && !empty($vdialog["request"])){
					$reqdel=$vdialog["reqlist"];
					$reqlst=explode(",",$vdialog["request"]);
					$reqlst=array_diff($reqlst,$reqdel);
					$vdialog["request"]=implode(",",$reqlst);
					$vdialog["sourcecode"]=write_opws($vdialog["wstype"],$vdialog["endpoint"],$vdialog["method"],$vdialog["accesskey"],$vdialog["request"]);
				}			
			}
			elseif(isset($vdialog["submit"])){ // tombol simpan
				switch ($actionpage){
				case "tambah":
					if($vdialog["methodtype"]=="program")
						$vdialog["sourcecode"]=write_opln($vdialog["code"],$vdialog["requestln"]);
					elseif($vdialog["methodtype"]=="database")
						$vdialog["sourcecode"]=write_opds($vdialog["dbtype"],$vdialog["hostname"],$vdialog["port"],$vdialog["dbuser"],$vdialog["dbpass"],$vdialog["dbname"],$vdialog["tbname"],$vdialog["columns"],$vdialog["conditions"],$vdialog["orders"],$vdialog["limrows"],$vdialog["limoffset"]);
					elseif($vdialog["methodtype"]=="services")
						$vdialog["sourcecode"]=write_opws($vdialog["wstype"],$vdialog["endpoint"],$vdialog["method"],$vdialog["accesskey"],$vdialog["request"]);
					$ret=addMethod($vdialog);
					if($ret=='OK'){
						addTrack(array("trackid"=>$vdialog['methodname'],"trackname"=>$interface,"trackstatus"=>"ADD","tracknote"=>"","trackdata"=>""));
						$message="Penambahan data '".$vdialog['methodname']."' berhasil disimpan.";
						$hasFinished=true;
					}
					else
						$message="Data wajib isi (*) tidak lengkap, penambahan data '".$vdialog['methodname']."' gagal disimpan.<br/>".$ret;
					break;
				case "ubah":
					$vmethod=getMethodByID($vdialog["id"]);
					if($vdialog["methodtype"]=="program")
						$vdialog["sourcecode"]=write_opln($vdialog["code"],$vdialog["requestln"]);
					elseif($vdialog["methodtype"]=="database")
						$vdialog["sourcecode"]=write_opds($vdialog["dbtype"],$vdialog["hostname"],$vdialog["port"],$vdialog["dbuser"],$vdialog["dbpass"],$vdialog["dbname"],$vdialog["tbname"],$vdialog["columns"],$vdialog["conditions"],$vdialog["orders"],$vdialog["limrows"],$vdialog["limoffset"]);
					elseif($vdialog["methodtype"]=="services")
						$vdialog["sourcecode"]=write_opws($vdialog["wstype"],$vdialog["endpoint"],$vdialog["method"],$vdialog["accesskey"],$vdialog["request"]);
					$ret=updateMethod($vdialog);
					if($ret=='OK'){
						addTrack(array("trackid"=>$vmethod['methodname'],"trackname"=>$interface,"trackstatus"=>"EDIT","tracknote"=>"","trackdata"=>""));
						$message="Pemutakhiran data '".$vdialog['methodname']."' berhasil disimpan.";
						$hasFinished=true;
					}
					else
						$message="Data wajib isi (*) tidak lengkap, pemutakhiran data '".$vdialog['methodname']."' gagal disimpan.<br/>".$ret;
					break;
				case "hapus":
					$vmethod=getMethodByID($vdialog["id"]);
					$ret=deleteMethodByID($vdialog["id"]);
					if($ret=='OK'){
						addTrack(array("trackid"=>$vmethod['methodname'],"trackname"=>$interface,"trackstatus"=>"DELETE","tracknote"=>"","trackdata"=>""));
						$message="Data '".$vdialog['methodname']."' berhasil dihapus.";
						$vdialog=resetpagemethods();
						$hasFinished=true;
					}
					else
						$message="Data '".$vdialog['methodname']."' gagal dihapus.<br/>".$ret;
				}
			}
		}


		//----------------------------- Get Method for Edit ----------------------------\\
		$loadfocus='';
		if(isset($puri["id"]) && !empty($puri["id"])){
			if(isset($vdialog["opendb"]) || isset($vdialog["opentb"]) || isset($vdialog["opencl"]) ){
			}
			else{
				$vmethod=getMethodByID($puri["id"]);
				if(is_array($vmethod) && !isset($_POST["f_dialog"])) $vdialog=array_merge($vdialog,$vmethod);
			}
		}
		else{
			//------------------- Initialize Method Type ----------------//
			if($puri['action']=='tambah' && $vdialog["methodtype"]==''){
				if(is_provider()){ 
					$vdialog["methodtype"]="database";
					$vdialog['dbtype']='mysql';
					$vdialog['hostname']='localhost';
					$vdialog['port']='3306';
					$vdialog['dbuser']='user';
					$dbnames=$tbnames=$clnames=$colnames=array();
					$vdialog["clnameoptions"]='';
					$vdialog["dbpass"]=$vdialog["dbname"]=$vdialog["tbname"]=$vdialog["columns"]=$vdialog["conditions"]=$vdialog["orders"]=$vdialog["limrows"]=$vdialog["limoffset"]="";
					$vdialog["sourcecode"]=write_opds($vdialog["dbtype"],$vdialog["hostname"],$vdialog["port"],$vdialog["dbuser"],$vdialog["dbpass"],$vdialog["dbname"],$vdialog["tbname"],$vdialog["columns"],$vdialog["conditions"],$vdialog["orders"],$vdialog["limrows"],$vdialog["limoffset"]);
				}
				if(is_publisher()) $vdialog["methodtype"]="services";
				$loadfocus='autofocus="autofocus"';
			}
		}

		if($vdialog["methodtype"]=="program"){ 
			$codes=read_opln($vdialog["sourcecode"]);
			$vdialog["code"]=isset($codes["code"])?$codes["code"]:$vdialog["code"];
			$vdialog["requestln"]=isset($codes["request"])?$codes["request"]:$vdialog["requestln"];
			//------------------- Initialize Command Request -----------//
			if($vdialog["requestln"]=="") $requestsln=array();
			else $requestsln=explode(",",$vdialog["requestln"]);
			if($vdialog["code"]==""){
				$vdialog["code"]="/*\n".
				"//========Contoh perintah baca data ke format JSON:\n".
				"\$result=dbExecute(\n\t".
				"\$dbdriver='mysqli',\n\t".
				"\$hostname='localhost',\n\t".
				"\$username='user',\n\t".
				"\$password='password',\n\t".
				"\$dbname='dbName',\n\t".
				"\$sql='SELECT * FROM tbName WHERE name=?',\n\t".
				"\$bindfield=array('name'=>\$name),\n\t".
				"\$trx=false\n".
				");\r".
				"if(is_string(\$result)){\n\t".
				"echo \$result;\n".
				"}\r".
				"else{\n\t".
				"\$data=\$result->getRows();\n\t".
				"if(count(\$data)>0) echo serialize(\$data);\n".
				"}\n\r".
				"//========Contoh perintah tambah data:\n".
				"\$result=dbInsert(\n\t".
				"\$dbdriver='mysqli',\n\t".
				"\$hostname='localhost',\n\t".
				"\$username='user',\n\t".
				"\$password='password',\n\t".
				"\$dbname='dbName',\n\t".
				"\$tbname='tbName',\n\t".
				"\$bindfield=array('name'=>'myname')\n".
				");\r".
				"if(is_string(\$result)){\n\t".
				"echo \$result;\n".
				"}\r".
				"else{\n\t".
				"\$data=\$result->getRows();\n\t".
				"if(count(\$data)>0) echo serialize(\$data);\n".
				"}\n\r".
				"//========Contoh perintah ubah data:\n".
				"\$result=dbUpdate(\n\t".
				"\$dbdriver='mysqli',\n\t".
				"\$hostname='localhost',\n\t".
				"\$username='user',\n\t".
				"\$password='password',\n\t".
				"\$dbname='dbName',\n\t".
				"\$tbname='tbName',\n\t".
				"\$bindfield=array('name'=>'myname'),\n\t".
				"\$where='id=1'\n".
				");\r".
				"if(is_string(\$result)){\n\t".
				"echo \$result;\n".
				"}\r".
				"else{\n\t".
				"\$data=\$result->getRows();\n\t".
				"if(count(\$data)>0) echo serialize(\$data);\n".
				"}\n\r".
				"//========Contoh perintah hapus data:\n".
				"\$result=dbDelete(\n\t".
				"\$dbdriver='mysqli',\n\t".
				"\$hostname='localhost',\n\t".
				"\$username='user',\n\t".
				"\$password='password',\n\t".
				"\$dbname='dbName',\n\t".
				"\$tbname='tbName',\n\t".
				"\$where='id=1'\n".
				");\r".
				"if(is_string(\$result)){\n\t".
				"echo \$result;\n".
				"}\r".
				"else{\n\t".
				"\$data=\$result->getRows();\n\t".
				"if(count(\$data)>0) echo serialize(\$data);\n".
				"}\n\r".
				"*/";
			}
		}
		if($vdialog["methodtype"]=="database"){
			$dsn=read_opds($vdialog["sourcecode"]);
			$vdialog["dbtype"]=isset($dsn["dbtype"])?$dsn["dbtype"]:$vdialog["dbtype"];
			$vdialog["hostname"]=isset($dsn["hostname"])?$dsn["hostname"]:$vdialog["hostname"];
			$vdialog["port"]=isset($dsn["port"])?$dsn["port"]:$vdialog["port"];
			$vdialog["dbuser"]=isset($dsn["dbuser"])?$dsn["dbuser"]:$vdialog["dbuser"];
			$vdialog["dbpass"]=isset($dsn["dbpassword"])?$dsn["dbpassword"]:$vdialog["dbpass"];
			$vdialog["dbname"]=isset($dsn["dbname"])?$dsn["dbname"]:$vdialog["dbname"];
			$vdialog["tbname"]=isset($dsn["tbname"])?$dsn["tbname"]:$vdialog["tbname"];
			$vdialog["columns"]=isset($dsn["columns"])?$dsn["columns"]:$vdialog["columns"];
			$vdialog["conditions"]=isset($dsn["conditions"])?$dsn["conditions"]:$vdialog["conditions"];
			$vdialog["orders"]=isset($dsn["orders"])?$dsn["orders"]:$vdialog["orders"];
			$vdialog["limrows"]=isset($dsn["limrows"])?$dsn["limrows"]:$vdialog["limrows"];
			$vdialog["limoffset"]=isset($dsn["limoffset"])?$dsn["limoffset"]:$vdialog["limoffset"];
			if(empty($clnames)){
				if(isset($vdialog["clnameoptions"]) && !empty($vdialog["clnameoptions"])){
					$clnames=explode(",",$vdialog["clnameoptions"]);
				}
				else{
					$host=$vdialog['hostname'];
					if($vdialog['port']!="") $host.=":".$vdialog['port'];
					if(!empty($host) && !empty($vdialog["dbuser"]) && !empty($vdialog["dbname"]) && !empty($vdialog["tbname"]))
						$clnames=getColumnNames($vdialog["dbtype"],$host,$vdialog["dbuser"],$vdialog["dbpass"],$vdialog["dbname"],$vdialog["tbname"]);
				}
			}
			//-------------------- Initialize SQL Variable ----------------------//
			$vdialog["sql"]="";
			$columnlst=$whrlst=$ordlst=array();
			$colnames=array();
			if(count($clnames)>0)	foreach($clnames as $clkey=>$clname){ 
				if(in_array($vdialog["dbtype"],array("mysql","mysqli"))){
					$colnames[$clkey]="`".$clname."`";
				}
				elseif(in_array($vdialog["dbtype"],array("postgres","oci8"))){
					$colnames[$clkey]="\"".$clname."\"";
				}
				elseif($vdialog["dbtype"]=="mssql"){
					$colnames[$clkey]="[".$clname."]";
				}
				else{
					$colnames[$clkey]=$clname;
				}
			}
			if($vdialog["methodtype"]=="database" && $vdialog["tbname"]!=""){
				$tblname=$vdialog["tbname"];
				$col=$vdialog["columns"]==""?"*":$vdialog["columns"];
				$columnlst=$vdialog["columns"]==""?array():explode(",",$vdialog["columns"]);
				$whr=$vdialog["conditions"]==""?"":str_replace("@@"," ",$vdialog["conditions"]);
				$whrlst=$vdialog["conditions"]==""?array():explode("@@",$vdialog["conditions"]);
				$ord=$vdialog["orders"]==""?"":"ORDER BY ".$vdialog["orders"];
				$ordlst=$vdialog["orders"]==""?array():explode(",",$vdialog["orders"]);
				$vdialog["sql"]="SELECT ".$col." \nFROM ".$tblname." \n".$whr." \n".$ord;
				if(in_array($vdialog["dbtype"],array("mysql","mysqli","postgres") )){
					$limrows=intval($vdialog["limrows"])==0?"":$vdialog["limrows"];
					$limoffset=intval($vdialog["limoffset"])==0?"":$vdialog["limoffset"];
					$limit="";
					if($limrows!=""){
						$limit=$limrows;
						if($limoffset!="") $limit.=" OFFSET ".$limoffset;
						$limit=" LIMIT ".$limit;
					}
					$vdialog["sql"].=$limit;
				}
			}

		}
		if($vdialog["methodtype"]=="services"){
			$codes=read_opws($vdialog["sourcecode"]);
			$vdialog["wstype"]=isset($codes["wstype"])?$codes["wstype"]:$vdialog["wstype"];
			$vdialog["endpoint"]=isset($codes["endpoint"])?$codes["endpoint"]:$vdialog["endpoint"];
			$vdialog["method"]=isset($codes["method"])?$codes["method"]:$vdialog["method"];
			$vdialog["accesskey"]=isset($codes["token"])?$codes["token"]:(isset($codes["accesskey"])?$codes["accesskey"]:$vdialog["accesskey"]);
			$vdialog["request"]=isset($codes["request"])?$codes["request"]:$vdialog["request"];
			//------------------- Initialize Services Request -----------//
			if($vdialog["request"]=="") $requests=array();
			else $requests=explode(",",$vdialog["request"]);
		}
		
		//------------------ Initialize Display Message ---------------------\\
		if(!empty($db['default']['messages'])) $message=$db['default']['messages'];
		if(!empty($message)){
			$_SESSION['message']=$message;
			if($hasFinished){
				if(!headers_sent()){ 
					header('location:'.$interfaceuri);
					exit;
				}
			} 
		}
				
		//---------------------- Initialize Method View --------------------\\
		$readonly=in_array($vdialog["methodtype"],array("services","database"))?"readonly=\"readonly\" style=\"background-color:#f0f0f0;\"":"";

		if(in_array($actionpage,array("tambah","ubah"))){
			if($hasFinished) return;
			
			$_SESSION['idform']=dechex(time()).strrev(time());
			$code=encsay($_SERVER['HTTP_USER_AGENT'],$_SESSION['idform']);

			$token=array(
				'name'=>session_name(),
				'code'=>$code
			);
						
		?>
		<br/>
		<form name="f_dialog" method="post" action="" accept-charset="UTF-8" >
		<div class="dialog">
			<div>Direktori Operasi*:</div>
			<div>
				<select name="f_dialog[serviceid]" size="5" <?php echo $loadfocus;?> style="width:39.5em;" onchange="">
				<?php foreach($services as $no=>$service){?>
				<option <?php echo $vdialog["serviceid"]==$service["id"]?"selected=\"selected\"":""?> 
				    value="<?php echo esc_text($service["id"]);?>" 
						id="<?php echo $service["instanceid"].";".$service["id"]?>">
						<?php echo $service["instance"].":".$service["servicename"];?>
				</option>
				<?php }?>
				</select>
			</div>
			<input type="hidden" name="f_dialog[servicename]" value="<?php echo esc_text($vdialog["servicename"]);?>" />
			<div>Fungsi Operasi*:</div>
			<div><input type="text" name="f_dialog[methodname]" style="width:39em;" value="<?php echo esc_text($vdialog["methodname"]);?>" onkeypress="return letterNumber(event,5)" onchange="lowerCase(this)"/></div>
			<?php if(is_provider() or is_publisher()){ ?>
			<div>Akses Terbatas: <input type="checkbox" name="f_dialog[restricted]" <?php echo $vdialog["restricted"]=="on"?"checked=\"checked\"":"";?> /></div>
			<?php }else{?>
			<input type="hidden" name="f_dialog[restricted]" value="<?php echo esc_text($vdialog["restricted"]);?>" />
			<?php } 
			?>
			<div>Jenis Operasi*:</div>
			<div>
				<select name="f_dialog[methodtype]" <?php if(!empty($metodtypefocus)) echo $metodtypefocus;?> onchange="this.form['f_dialog[methodtypeselected]'].value=this.value;this.form.submit();">
				<?php if(is_provider()){?>
				<option <?php echo $vdialog["methodtype"]=="program"?"selected=\"selected\"":""?> value="program">Program</option>
				<option <?php echo $vdialog["methodtype"]=="database"?"selected=\"selected\"":""?> value="database">Data</option>
				<?php }?>
				<?php if(is_publisher()){?>
				<option <?php echo $vdialog["methodtype"]=="services"?"selected=\"selected\"":""?> value="services">Proxy</option>
				<?php }?>
				</select>
				<input type="hidden" name="f_dialog[methodtypeselected]" value="" />
				
				<?php 
					if($vdialog["methodtype"]=="database"){
						if($vdialog["dbtype"]=="oci8") $dbcaption="(ServiceName | SID=ServiceName)";				
						if($vdialog["dbtype"]=="mssql") $dblocation='(hostname\instance)';				
				?>

				<div id="dbcriteria">
					<div>Sistem Basis Data (Database)*:</div>
					<select name="f_dialog[dbtype]" <?php if(!empty($dbtypefocus)) echo $dbtypefocus;?> onchange="this.form['f_dialog[dbtypeselected]'].value=this.value;this.form.submit();">
					<option <?php echo $vdialog["dbtype"]=="mysql"?"selected=\"selected\"":""?> value="mysql">MySQL</option>
					<option <?php echo $vdialog["dbtype"]=="mysqli"?"selected=\"selected\"":""?> value="mysqli">MySQLi</option>
					<option <?php echo $vdialog["dbtype"]=="oci8"?"selected=\"selected\"":""?> value="oci8">Oracle</option>
					<option <?php echo $vdialog["dbtype"]=="postgres"?"selected=\"selected\"":""?> value="postgres">PostgreSQL</option>
					<option <?php echo $vdialog["dbtype"]=="mssql"?"selected=\"selected\"":""?> value="mssql">Microsoft SQL Server</option>
					</select>
					<input type="hidden" name="f_dialog[dbtypeselected]" value="" />
					<div>Alamat Lokasi Server <?php echo esc_text($dblocation);?> *:</div>
					<input type="text" name="f_dialog[hostname]" style="width:99%;"  value='<?php echo esc_text($vdialog["hostname"]);?>'/>
					<?php if($vdialog["dbtype"]!="mysqli"){?>
					<div>Nomor Port*:</div>
					<input type="text" name="f_dialog[port]" value="<?php echo esc_text($vdialog["port"]);?>" onkeypress="return letterNumber(event,11)"/>
					<?php }?>
					<div>ID Pengguna*:</div>
					<input type="text" name="f_dialog[dbuser]" style="width:99%;" value='<?php echo esc_text($vdialog["dbuser"]);?>'/>
					<div>Kata Kunci (Password):</div>
					<input type="password" name="f_dialog[dbpass]" style="width:99%;" value='<?php echo esc_text($vdialog["dbpass"]);?>'/>

					<div>
						Nama Basis Data <?php echo $dbcaption;?> *: <input type="submit" name="f_dialog[opendb]" value="Seleksi Basis Data" <?php echo isset($_POST['f_dialog']['opendb'])?'autofocus="autofocus"':'';?> style="float:right;<?php echo in_array($vdialog["dbtype"],array("oci8","mssql"))?"display:none":"display:inline";?>"/>
					</div>
					<?php if(count($dbnames)>0 && count($tbnames)==0 && $vdialog["dbname"]==""){?>
					<select name="f_dialog[dbnameoption]" size="5" style="width:100%;<?php echo in_array($vdialog["dbtype"],array("oci8","mssql"))?"display:none":"display:inline";?>" onchange="this.form['f_dialog[dbname]'].value=this.value;">
					<?php 		foreach($dbnames as $dbname){?>
					<option value='<?php echo esc_text($dbname);?>'><?php echo esc_text($dbname);?></option>
					<?php 		}?>
					</select>
					<?php }?>
					<input type="text" name="f_dialog[dbname]" style="width:99%;"  value='<?php echo esc_text($vdialog["dbname"]);?>' <?php echo in_array($vdialog["dbtype"],array("oci8","mssql"))?"":"readonly=\"readonly\"";?>  />

					<div>
						Nama Tabel Data*: <input type="submit" name="f_dialog[opentb]" value="Seleksi Tabel Data" <?php echo isset($_POST['f_dialog']['opentb'])?'autofocus="autofocus"':'';?> style="float:right;"/>
					</div>
					<?php if(count($tbnames)>0  && count($clnames)==0 && $vdialog["tbname"]==""){?>
					<select name="f_dialog[tbnameoption]" size="5" style="width:100%;<?php echo $vdialog["dbname"]==""?"display:none":"display:inline";?>" onchange="this.form['f_dialog[tbname]'].value=this.value;">
					<?php 	foreach($tbnames as $tbname){
										if(in_array($vdialog["dbtype"],array("mysql","mysqli"))){
											$tbname="`".$tbname."`";
										}
										elseif(in_array($vdialog["dbtype"],array("postgres","oci8"))){
											$tbname="\"".$tbname."\"";
										}
										elseif($vdialog["dbtype"]=="mssql"){
											$tbname="[".$tbname."]";
										}
					?>
					<option value='<?php echo esc_text($tbname);?>'><?php echo esc_text($tbname);?></option>
					<?php 	}?>
					</select>
					<?php }?>
					<input type="text" name="f_dialog[tbname]" style="width:99%;"  value='<?php echo esc_text($vdialog["tbname"]);?>'  readonly="readonly" />

					<div>
						Nama Kolom Data*: <input type="submit" name="f_dialog[opencl]" value="Seleksi Kolom Data" <?php echo isset($_POST['f_dialog']['opencl'])?'autofocus="autofocus"':'';?> style="float:right;"/>
					</div>
					<select name="f_dialog[clnameoption][]" multiple="multiple" size="5" style="width:100%;" >
					<?php if(count($colnames)>0) foreach($colnames as $colname){ ?>
					<option value='<?php echo esc_text($colname);?>'><?php echo esc_text($colname);?></option>
					<?php }?>
					</select>
					<input type="hidden" name="f_dialog[allcolnames]" value='<?php echo !empty($colnames)?esc_text(implode(",",$colnames)):"";?>' />
					<input type="hidden" name="f_dialog[clnameoptions]" value='<?php echo !empty($clnames)?esc_text(implode(",",$clnames)):"";?>' />
					<div>
						Susunan Kolom Data:
						<span style="float:right">
						<input type="submit" name="f_dialog[allcolumn]" value="Semua Kolom" <?php echo isset($_POST['f_dialog']['allcolumn'])?'autofocus="autofocus"':'';?> />
						<input type="submit" name="f_dialog[addcolumn]" value="Tambah Kolom" <?php echo isset($_POST['f_dialog']['addcolumn'])?'autofocus="autofocus"':'';?> />
						<input type="submit" name="f_dialog[delcolumn]" value="Eliminasi Kolom" <?php echo isset($_POST['f_dialog']['delcolumn'])?'autofocus="autofocus"':'';?> />
						</span>
					</div>

					<select name="f_dialog[setcolumn][]" size="5" multiple="multiple" style="width:38em;" >
					<?php if(count($columnlst)>0) foreach($columnlst as $columnitm){?>
					<option value='<?php echo esc_text($columnitm);?>'><?php echo esc_text($columnitm);?></option>
					<?php }?>
					</select>
					<input type="hidden" name="f_dialog[columns]" value='<?php echo esc_text($vdialog["columns"]);?>'/>

					<div>Kondisi/Batasan per Kolom Data:</div>
					<select name="f_dialog[colname]" style="width:11.5em;" >
					<?php if(count($colnames)>0) foreach($colnames as $colname){?>
					<option <?php echo (isset($vdialog["colname"]) && $vdialog["colname"]==$colname)?"selected=\"selected\"":"";?>><?php echo esc_text($colname);?></option>
					<?php }?>
					</select>

					<select name="f_dialog[coltype]"  style="width:6.5em;">
					<option value="text">Teks</option>
					<option value="numeric">Numerik</option> 
					</select>
					<select name="f_dialog[colcompare]"  style="width:4.5em;">
					<option value="="><?php echo "=";?></option>
					<option value="LIKE"><?php echo "LIKE";?></option>
					<option value="<>"><?php echo "<>";?></option>
					<option value=">"><?php echo ">";?></option>
					<option value=">="><?php echo ">=";?></option>
					<option value="<"><?php echo "<";?></option>
					<option value="<="><?php echo "<=";?></option>
					</select>
					<input type="text" name="f_dialog[colinput]"  style="width:14em;" />
					<br/>
					<input type="submit" name="f_dialog[addwhere]" value="Sisip" <?php echo isset($_POST['f_dialog']['addwhere'])?'autofocus="autofocus"':'';?> />
					<input type="submit" name="f_dialog[andwhere]" value="Dan" <?php echo isset($_POST['f_dialog']['andwhere'])?'autofocus="autofocus"':'';?> />
					<input type="submit" name="f_dialog[orwhere]" value="Atau" <?php echo isset($_POST['f_dialog']['orwhere'])?'autofocus="autofocus"':'';?> />
					<input type="submit" name="f_dialog[delwhere]" value="Hapus" <?php echo isset($_POST['f_dialog']['delwhere'])?'autofocus="autofocus"':'';?> />
					<select name="f_dialog[wherelist][]" size="5"  multiple="multiple" style="width:100%;" >
					<?php if(count($whrlst)>0) foreach($whrlst as $whritm){?>
					<option><?php echo esc_text($whritm);?></option>
					<?php }?>
					</select>
					<input type="hidden" name="f_dialog[conditions]" value="<?php echo esc_text($vdialog["conditions"]);?>"/>
					
					<div>Urutan Data:</div>
					<select name="f_dialog[colorder]" style="width:20em;" >
					<?php $ords=$col=="*"?$colnames:$columnlst;if(count($ords)>0) foreach($ords as $columnitm){?>
					<option  <?php echo (isset($vdialog["colorder"]) && $vdialog["colorder"]==$columnitm)?"selected=\"selected\"":"";?>><?php echo esc_text($columnitm);?></option>
					<?php }?>
					</select>
					<select name="f_dialog[colseq]"  style="width:8em;">
					<option value="ASC">Naik</option>
					<option value="DESC">Turun</option>
					</select>
					<input type="submit" name="f_dialog[addorder]" value="Sisip" <?php echo isset($_POST['f_dialog']['addorder'])?'autofocus="autofocus"':'';?> />
					<input type="submit" name="f_dialog[delorder]" value="Hapus" <?php echo isset($_POST['f_dialog']['delorder'])?'autofocus="autofocus"':'';?> />
					<select name="f_dialog[orderlist][]" size="5" multiple="multiple" style="width:100%;">
					<?php if(count($ordlst)>0) foreach($ordlst as $orditm){?>
					<option value='<?php echo esc_text($orditm);?>'><?php echo esc_text($orditm);?></option>
					<?php }?>
					</select>
					<input type="hidden" name="f_dialog[orders]" value='<?php echo esc_text($vdialog["orders"]);?>'/>

					<div>
						Jumlah Baris:&nbsp;
						<input type="text" name="f_dialog[limrows]" size="5" value="<?php echo esc_text($vdialog["limrows"]);?>"  onkeypress="return letterNumber(event,11);"/>
						&nbsp;&nbsp;&nbsp;
						Awal Baris:&nbsp;
						<input type="text" name="f_dialog[limoffset]" size="5" value="<?php echo esc_text($vdialog["limoffset"]);?>" onkeypress="return letterNumber(event,11);"/>
						<input type="submit" name="f_dialog[setlimit]" value="Sisip" <?php echo isset($_POST['f_dialog']['setlimit'])?'autofocus="autofocus"':'';?> />
					</div>

					<div>Perintah Pengolahan Data (SQL)*:</div>										
					<textarea name="f_dialog[sql]" style="width:99%;" rows="10" wrap="off" readonly="readonly" ><?php echo esc_text($vdialog["sql"]);?></textarea>
				</div>
				<?php }?>
				
				<?php if($vdialog["methodtype"]=="services"){?>
				<div id="wsformat"">
					<div>Model Interkoneksi Web-API:</div>
					<select name="f_dialog[wstype]"  style="width:10em;" onchange="onChangeServiceType(this,'f_dialog')">
					<option <?php echo $vdialog["wstype"]=="rest"?"selected=\"selected\"":""?> value="rest">REST/MANTRA</option>
					<option <?php echo $vdialog["wstype"]=="restfull"?"selected=\"selected\"":""?> value="restfull">RESTFULL</option>
					<option <?php echo $vdialog["wstype"]=="restfullpar"?"selected=\"selected\"":""?> value="restfullpar">RESTFULLPARAMETER</option>
					<option <?php echo $vdialog["wstype"]=="soap"?"selected=\"selected\"":""?> value="soap">SOAP</option>
					<option <?php echo $vdialog["wstype"]=="get"?"selected=\"selected\"":""?> value="get">HTTP/GET</option>
					<option <?php echo $vdialog["wstype"]=="post"?"selected=\"selected\"":""?> value="post">HTTP/POST</option>
					</select>
<pre>
Format (URL/Endpoint):
* REST        : http://{alamat-aplikasi}[/{direktori-operasi}]
                [/{fungsi-operasi}][/{parameter}]
* MANTRA      : http://{alamat-aplikasi}/api|xml|json/{nama-instansi-penyedia}/{direktori-operasi}
                [/{fungsi-operasi}][/{parameter}]
* RESTFULL    : http://{alamat-aplikasi}[/{direktori-operasi}]
                [/{fungsi-operasi}][?{parameter}]
* RESTFULLPAR : http://{alamat-aplikasi}[/{direktori-operasi}]
                [/{fungsi-operasi}][/{parameter}]
* SOAP        : http://{alamat-aplikasi}/{direktori-operasi[.svc]}
* HTTP/GET    : http://{alamat-aplikasi}[/{direktori-operasi}]
                [/{fungsi-operasi}][?{parameter}]
* HTTP/POST   : http://{alamat-aplikasi}[/{direktori-operasi}]
                [/{fungsi-operasi}]

</pre>
				</div>
				<div id="wscriteria">
					<div>Alamat Web-API (URL/Endpoint):</div>
					<input type="text" name="f_dialog[endpoint]"  style="width:100%;"  value="<?php echo esc_text($vdialog["endpoint"]);?>" onkeypress="return letterNumber(event,9)"/>
					<div>Nama Fungsi Operasi Web-API*:</div>
					<input type="text" name="f_dialog[method]" value="<?php echo esc_text($vdialog["method"]);?>"  style="width:100%;" onkeypress="return letterNumber(event,9);" onblur="onBlurMethod(this,'f_dialog')"/>
					<div>Kunci Akses:</div>
					<input type="text" name="f_dialog[accesskey]" style="width:100%;" value="<?php echo esc_text($vdialog["accesskey"]);?>" onkeypress="return letterNumber(event,9)" />
					<div>Parameter Operasi Web-API*:</div>
					<input type="text" name="f_dialog[reqname]"  style="width:11.5em;" onkeypress="return letterNumber(event,12);"/>
					<select name="f_dialog[reqtype]"  style="width:7em;">
					<option value="text">Teks</option>
					<option value="password">Sandi</option>
					<option value="numeric">Numerik</option>
					</select>
					<input type="text" name="f_dialog[reqvalue]"  style="width:12em;" />
					<input type="submit" name="f_dialog[addrequest]" value="Tambah" <?php echo isset($_POST['f_dialog']['addrequest'])?'autofocus="autofocus"':'';?>/>
					<br/>
					<select name="f_dialog[reqlist][]" size="5" multiple="multiple"  style="width:32.5em;">
					<?php foreach($requests as $reqval){?>
					<?php 
									$vardata=explode('=',$reqval,2);
									$vartype=explode(':',$vardata[0],2);
									$vardata[1]=$vartype[1]=="password"?str_repeat('*',strlen($vardata[1])+1):$vardata[1];
					?>
					<option value="<?php echo esc_text($reqval);?>"><?php echo esc_text(implode('=',$vardata));?></option>
					<?php }?>
					</select>
					<input type="submit" name="f_dialog[delrequest]" value="Hapus" <?php echo isset($_POST['f_dialog']['delrequest'])?'autofocus="autofocus"':'';?> />
					<input type="hidden" name="f_dialog[request]" value="<?php echo esc_text($vdialog["request"]);?>"/>
				</div>
				<input type="hidden" name="f_dialog[instance]" value="<?php echo esc_text($vdialog["instance"]);?>"/>
				<?php }?>

				<?php if($vdialog["methodtype"]=="program"){?>
				<div id="cmcriteria">
					<div>Parameter Operasi Program*:</div>
					<input type="text" name="f_dialog[reqnameln]"  style="width:11.5em;" onkeypress="return letterNumber(event,12);"/>
					<select name="f_dialog[reqtypeln]"  style="width:7em;">
					<option value="text">Teks</option>
					<option value="numeric">Numerik</option>
					</select>
					<input type="text" name="f_dialog[reqvalueln]"  style="width:12em;" />
					<input type="submit" name="f_dialog[addreqln]" value="Tambah" <?php echo isset($_POST['f_dialog']['addreqln'])?'autofocus="autofocus"':'';?> />
					<br/>
					<select name="f_dialog[reqlistln][]" size="5" multiple="multiple"  style="width:32.5em;">
					<?php foreach($requestsln as $reqval){?>
					<option value="<?php echo esc_text($reqval);?>"><?php echo esc_text($reqval);?></option>
					<?php }?>
					</select>
					<input type="submit" name="f_dialog[delreqln]" value="Hapus" <?php echo isset($_POST['f_dialog']['delreqln'])?'autofocus="autofocus"':'';?>/>
					<input type="hidden" name="f_dialog[requestln]" value="<?php echo esc_text($vdialog["requestln"]);?>"/>
					<div>Perintah Operasi*:</div>
					<div><textarea name="f_dialog[code]" style="width:37.5em;" rows="10" wrap="off" <?php echo $readonly;?> ><?php echo esc_text($vdialog["code"]);?></textarea></div>
				</div>
				<?php }?>
				
			</div>
			<div>Keterangan:</div>
			<div><textarea name="f_dialog[descript]" style="width:39em;" rows="5" wrap="off"><?php echo esc_text($vdialog["descript"]);?></textarea></div>
			<hr/>
			<div><input type="submit" name="f_dialog[submit]" value="Simpan" /></div>
			<input name="<?php echo $token['name'];?>" type="hidden" value="<?php echo esc_text($token['code']);?>"/>
			<input type="hidden" name="f_dialog[sourcecode]" value="<?php echo esc_text($vdialog["sourcecode"]);?>"/>
			<?php if($actionpage=="ubah"){?>
			<input type="hidden" name="f_dialog[id]" value="<?php echo esc_text($vdialog["id"]);?>"/>
			<?php }?>
		</div>
		</form>
		<?php
		}
		
		
		if($actionpage=="hapus"){
			if($hasFinished) return;

			$_SESSION['idform']=dechex(time()).strrev(time());
			$code=encsay($_SERVER['HTTP_USER_AGENT'],$_SESSION['idform']);

			$token=array(
				'name'=>session_name(),
				'code'=>$code
			)

		?>
		<br/>
		<form name="f_dialog" method="post" action="" accept-charset="UTF-8">
		<div class="dialog">
			<div>Direktori Operasi:</div>
			<div><input type="text" name="f_dialog[servicename]" style="width:40em;" readonly="readonly" value="<?php echo esc_text($vdialog["servicename"]);?>"/></div>
			<div>Fungsi Operasi:</div>
			<div><input type="text" name="f_dialog[methodname]" style="width:40em;" readonly="readonly" value="<?php echo esc_text($vdialog["methodname"]);?>"/></div>
			<div>Jenis Operasi:</div>
			<div><input type="text" name="f_dialog[methodtype]" style="width:40em;" readonly="readonly" value="<?php echo esc_text($vdialog["methodtype"]);?>"/></div>
			<div>Perintah Operasi:</div>
			<div><textarea name="f_dialog[sourcecode]" style="width:40em;" rows="10" readonly="readonly" ><?php echo esc_text($vdialog["sourcecode"]);?></textarea></div>
			<div>Keterangan:</div>
			<div><textarea name="f_dialog[descript]" style="width:40em;" rows="5" readonly="readonly" ><?php echo esc_text($vdialog["descript"]);?></textarea></div>
			<hr/>
			<div><input type="submit" name="f_dialog[submit]" value="Hapus"/></div>
			<input name="<?php echo $token['name'];?>" type="hidden" value="<?php echo esc_text($token['code']);?>"/>
			<input type="hidden" name="f_dialog[restricted]" value="<?php echo esc_text($vdialog["restricted"]);?>"/>
			<input type="hidden" name="f_dialog[accesskey]" value="<?php echo esc_text($vdialog["accesskey"]);?>"/>
			<input type="hidden" name="f_dialog[id]" value="<?php echo esc_text($vdialog["id"]);?>"/>
			<input type="hidden" name="f_dialog[instance]" value="<?php echo esc_text($vdialog["instance"]);?>"/>
		</div>
		</form>
		<?php
		}
		unset($_POST);
	}
	else{
		$filters=array();
		if($currInstance=="") 
		$filters["instance"]=array("label"=>"ID Penyedia","size"=>30);
		$filters["servicename"]=array("label"=>"Direktori Operasi","size"=>30,"attrs"=>"autofocus=\"autofocus\"");
		$filters["methodname"]=array("label"=>"Fungsi Operasi","size"=>30);

		$grid=new grid_interface;
		$grid->urikeys=$urikeys;
		$grid->puri=$puri;
		$grid->interfaces=$interfaces;
		$grid->baseurl=$interfaceuri;
		$grid->setFilter($filters);
		$grid->rows=getMethodRows($currInstance==""?$grid->query["instance"]:$currInstance,$grid->query["servicename"],$grid->query["methodname"]);
		$grid->pager=getPageList($grid->rows,$grid->pagenum,10,1);
		$grid->listpager=getPageList($grid->rows,$grid->pagenum,10,5);
		$grid->pdf["data"]="methods/instance=".($currInstance==""?$grid->query["instance"]:$currInstance)."&servicename=".$grid->query["servicename"]."&methodname=".$grid->query["methodname"];
		$grid->records=getMethods($currInstance==""?$grid->query["instance"]:$currInstance,$grid->query["servicename"],$grid->query["methodname"],$grid->pager["perpage"],$grid->pager["startrow"]);
		$grid->colnames=array("servicename"=>array("label"=>"DIREKTORI"),
							  "methodname"=>array("label"=>"FUNGSI OPERASI"),
							  "methodtype"=>array("label"=>"JENIS","patern"=>":methodtype=='program'?'Program':(:methodtype=='database'?'Data':(:methodtype=='services'?'Proxy':''));"),
							  "registered"=>array("label"=>"TGL.DAFTAR"),
							  "updated"=>array("label"=>"TGL.UBAH"));
		$grid->operations=array(array("url"=>$interfaceuri."ubah/:id","title"=>"Ubah Data","icon"=>"ico/edit.png"),
								array("url"=>$interfaceuri."hapus/:id","title"=>"Hapus Data","icon"=>"ico/drop.png"));
		if($grid->display()==false){
		?>
		<div class="message"><b>Data belum ada</b></div>
		<?php
		}
	}
	echo "<br/>";

}


