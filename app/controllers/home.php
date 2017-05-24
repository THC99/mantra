<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
ver:1.99y
*/


require_once SYSPATH.'apiconnector.php';
require_once SYSPATH.'apifunction.php';

function pagehome(){
	$currentrole=current_role();
	if($currentrole=="visitor"){
		?>
		<div class="logo"><img src="img/bigmantra.png"/></div>
		<?php
		public_home();
	}
	else private_home();
}

function public_home(){
	global $urikeys,$puri,$interfaces;
		
	pubtab();
	$interfaceuri=home_url();

	if(isset($puri["action"]) && $puri["action"]=="list") $puri["action"]="";
	
	if(isset($puri["action"]) && !empty($puri["action"])){
		$actionpage=$puri["action"];
		if($actionpage=="metadata") pagedescription();
	}
	else{

		$grid=new grid_interface;
		$grid->urikeys=$urikeys;
		$grid->puri=$puri;
		$grid->interfaces=$interfaces;
		$grid->baseurl=home_url();
		$grid->pdf["icon"]="ico/page_white_acrobat.png";
		$aFilter=array("instance"=>array("label"=>"ID Instansi","size"=>30,"attrs"=>"autofocus=\"autofocus\""),
					   "servicename"=>array("label"=>"Direktori Operasi","size"=>30),
					   "methodname"=>array("label"=>"Fungsi Operasi","size"=>30));
		$grid->setFilter($aFilter);
		$grid->rows=getPubResourceRows(array("instance"=>$grid->query["instance"],"servicename"=>$grid->query["servicename"],"methodname"=>$grid->query["methodname"]));
		$grid->pager=getPageList($grid->rows,$grid->pagenum,10,1);
		$grid->listpager=getPageList($grid->rows,$grid->pagenum,10,5);
		$resources=getPubResources(array("instance"=>$grid->query["instance"],"servicename"=>$grid->query["servicename"],"methodname"=>$grid->query["methodname"]),$grid->pager["perpage"],$grid->pager["startrow"]);
		$grid->pdf["data"]="pubresources/instance=".$grid->query["instance"]."&servicename=".$grid->query["servicename"]."&methodname=".$grid->query["methodname"];
		$grid->records=$resources;
		$grid->colnames=array("organization"=>array("label"=>"INSTANSI PENYEDIA"),
							  "servicename"=>array("label"=>"DIREKTORI"),
							  "methodname"=>array("label"=>"FUNGSI OPERASI"),
							  "registered"=>array("label"=>"TGL.DAFTAR"));
		$grid->operationstitle="METADATA";
		$grid->operations=array(array("url"=>$interfaceuri."metadata/:id","title"=>"Metadata","icon"=>"ico/application_view_detail.png"));

		$grid->display();
	
	}
}

function pagedescription(){
	global $puri;

	if(isset($puri["id"]) && !empty($puri["id"])){
		$pars="";
		$input="";
		$output="";
		$id=$puri["id"];
		$rs=getResourceByID($id);
		$uri=home_url()."api|json|xml/{$rs['instance']}/{$rs['servicename']}/{$rs['methodname']}";
		$url=home_url()."json/{$rs['instance']}/{$rs['servicename']}/{$rs['methodname']}";
		if($rs){

			if($rs['methodtype']=='services'){
				$input="";$pars="";
				$src=read_opws($rs['sourcecode']);
				$req=explode(',',$src['request']);
				foreach($req as $value) if(!empty($value)){
					$par=explode('=',$value,2);
					if(isset($par[1]) && in_array($par[1],array("","''"))){
						$var=explode(':',$par[0],2);
						if(isset($var[0])) $varname=$var[0];
						if(isset($var[1])) $vartype=$var[1];
						$varname=trim($varname);
						$pars.="\"$varname\"=>urlencode(\"...\"),";
						$input.="<li>".$varname."</li>";
					}
				}
				if($input!='') $input="<ul>".$input."</ul>";
				
			}

			if($rs['methodtype']=='database'){
				$input="";$pars="";
				$src=read_opds($rs['sourcecode']);
				$existcond=strpos(strtoupper($src['conditions']),"WHERE");
				if($existcond===false){}
				else{
					$req=explode('@@',$src['conditions']);
					foreach($req as $value) if(!empty($value)){
						if(strrpos($value," ''")===false){}
						else{
							$varname=trim($value);
							$varname=ltrim($varname,"WHERE ");
							$varname=ltrim($varname,"AND ");
							$varname=ltrim($varname,"OR ");
							$varname=rtrim($varname," ''");
							$varname=rtrim($varname," LIKE");
							$varname=rtrim($varname," <>");
							$varname=rtrim($varname," <=");
							$varname=rtrim($varname," >=");
							$varname=rtrim($varname," <");
							$varname=rtrim($varname," >");
							$varname=rtrim($varname," =");
							$varname=trim($varname,"`");
							$varname=trim($varname,"\"");
							$varname=ltrim($varname,"[");
							$varname=rtrim($varname,"]");						
							$varname=trim($varname);
							$varname=str_replace(" ","_",$varname);
							$pars.="\"{$varname}\"=>urlencode(\"...\"),";
							$input.="<li>".$varname."</li>";
						}
					}
				}
				if($input!=''){
					$input="<ul>".$input."</ul>";
					$pars=substr($pars,-1)==","?substr($pars,0,-1):$pars;
				}
				$req=explode(',',$src['columns']);//var_export($req);
				$output="";
				foreach($req as $value) if(!empty($value)){
					$varname=trim($value);
					$varname=trim($varname,"`");
					$varname=trim($varname,"\"");
					$varname=ltrim($varname,"[");
					$varname=rtrim($varname,"]");
					$varname=trim($varname);
					$output.="<li>".$varname."</li>";
				}
				if($output!='') $output="<ul>".$output."</ul>";
			}

			if($rs['methodtype']=='program'){
				$input="";$pars="";
				$src=read_opln($rs['sourcecode']);
				$req=explode(',',$src['request']);
				foreach($req as $value) if(!empty($value)){
					$par=explode('=',$value,2);
					if(isset($par[1]) && in_array($par[1],array("","''"))){
						$var=explode(':',$par[0],2);
						if(isset($var[0])) $varname=$var[0];
						if(isset($var[1])) $vartype=$var[1];
						$varname=trim($varname);
						$pars.="\"$varname\"=>urlencode(\"...\"),";
						$input.="<li>".$varname."</li>";
					}
				}
				if($input!='') $input="<ul>".$input."</ul>";
			}
			
			$desc=$rs['descript'];


		?>
		<div class="dialog">
		
		<div>Web-API (REST-MANTRA) Endpoint/URL:</div>
		<div style="padding-left:2em;font-weight:bold"><?php echo "<pre>".$uri."</pre>";?></div>

		<div>Instansi Penyedia:</div>
		<div style="padding-left:2em;font-weight:bold"><?php echo $rs['organization'];?></div>

		<div>Direktori Operasi:</div>
		<div style="padding-left:2em;font-weight:bold"><?php echo $rs['servicename'];?></div>

		<div>Fungsi Operasi:</div>
		<div style="padding-left:2em;font-weight:bold"><?php echo $rs['methodname'];?></div>

		<div>Parameter Masukan:</div>
		<div style="padding-left:2em;font-weight:bold">
		<?php if($input){?>
		<?php echo $input;?>
		<?php }else{?>
		-
		<?php }?>
		</div>
		
		<div>Elemen Data Keluaran:</div>
		<div style="padding-left:2em;font-weight:bold">
		<?php if($output){?>
		<?php echo $output;?>
		<?php }else{?>
		-
		<?php }?>
		</div>
		
		<div>Keterangan:</div>
		<div style="padding-left:2em;font-weight:bold">
		<?php if($desc){?>
		<?php echo nl2br($desc);?>
		<?php }else{?>
		-
		<?php }?>
		</div>

		<div>Contoh Akses:</div>
		<div style="padding-left:2em;font-weight:bold">
<pre>
$url="<?php echo $url;?>";
$accesskey="..."; //Kunci akses diperoleh dari permohonan akses requester 
<?php if($input){?>
$pardata=array(<?php echo $pars;?>);
$par="/".http_build_query($pardata);
<?php }else{?>
$par="";
<?php }?>
$options=array('http'=>
	array(
		'method'=>'GET',
		'header'=>"User-Agent: MANTRA\r\n".
		          "AccessKey: $accesskey"
	)
);
$context=stream_context_create($options);
$content=file_get_contents($url.$par,false,$context);
echo $content;


</pre>
		</div>
		
		</div>
		<?php

		}
	}
	else return;
}

function private_home()
{
	global $urikeys,$puri,$interfaces,$validLogin;
	if(!$validLogin) return;
	?>
	<p style='padding:0 4px;font-size:28px;font-weight:bold;'>Akses Operasi</p>
	<?php

	panelmenu();
	$interfaceuri=home_url();
	$currInstance="";
	$currLogname=current_logname();
	$currRole=current_role();
	if(is_provider() || is_publisher()) $currInstance=current_instance(current_instanceid());
	if(isset($puri["action"]) && $puri["action"]=="list") $puri["action"]="";
	if(isset($puri["action"]) && !empty($puri["action"])){
		$actionpage=$puri["action"];
		if($actionpage=="tinjauan") pagetrial($currLogname,$currRole);
		if($actionpage=="unduh"){
			if(is_requester()){
				pagedownload($currLogname,$currRole); 
			}
			else{
				echo "<br/>&nbsp;&nbsp;Restricted for Requester only.";
			}
		}
	}
	else{
		$grid=new grid_interface;
		$grid->urikeys=$urikeys;
		$grid->puri=$puri;
		$grid->interfaces=$interfaces;
		$grid->baseurl=$interfaceuri;
		$grid->pdf["icon"]="ico/page_white_acrobat.png";
		if(is_provider() || is_publisher()){
			$aFilter=array("servicename"=>array("label"=>"Direktori Operasi","size"=>30,"attrs"=>"autofocus=\"autofocus\""),
						   "methodname"=>array("label"=>"Fungsi Operasi","size"=>30));
		}
		else{
			$aFilter=array("instance"=>array("label"=>"ID Instansi Penyedia","size"=>30,"attrs"=>"autofocus=\"autofocus\""),
						   "servicename"=>array("label"=>"Direktori Operasi","size"=>30),
						   "methodname"=>array("label"=>"Fungsi Operasi","size"=>30));
		}
		$grid->setFilter($aFilter);
		$setInstance=(is_provider() || is_publisher())?$currInstance:$grid->query["instance"];
		if(is_requester()){
			$grid->rows=getOrderResourceRows(array("userlog"=>$currLogname,"instance"=>$setInstance,"servicename"=>$grid->query["servicename"],"methodname"=>$grid->query["methodname"]));
		}
		else{
			$grid->rows=getResourceRows(array("instance"=>$setInstance,"servicename"=>$grid->query["servicename"],"methodname"=>$grid->query["methodname"]));
		}
		$grid->pager=getPageList($grid->rows,$grid->pagenum,10,1);
		$grid->listpager=getPageList($grid->rows,$grid->pagenum,10,5);
		if(is_requester()){
			$resources=getOrderResources(array("userlog"=>$currLogname,"instance"=>$setInstance,"servicename"=>$grid->query["servicename"],"methodname"=>$grid->query["methodname"]),$grid->pager["perpage"],$grid->pager["startrow"]);
			$grid->pdf["data"]="orderresources/instance=".$setInstance."&servicename=".$grid->query["servicename"]."&methodname=".$grid->query["methodname"];
		}
		else{
			$resources=getResources(array("instance"=>$setInstance,"servicename"=>$grid->query["servicename"],"methodname"=>$grid->query["methodname"]),$grid->pager["perpage"],$grid->pager["startrow"]);
			$grid->pdf["data"]="resources/instance=".$setInstance."&servicename=".$grid->query["servicename"]."&methodname=".$grid->query["methodname"];
		}
		$grid->records=$resources;
		$grid->colnames=array("organization"=>array("label"=>"INST.PENYEDIA"),
							  "servicename"=>array("label"=>"DIREKTORI"),
							  "methodname"=>array("label"=>"FUNGSI OPERASI"),
							  "methodtype"=>array("label"=>"JENIS","patern"=>":methodtype=='program'?'Program':(:methodtype=='database'?'Data':(:methodtype=='services'?'Proxy':''));"),
							  "restricted"=>array("label"=>"AKSES","patern"=>":restricted=='on'?'Terbatas':'Terbuka';"),
							  "registered"=>array("label"=>"TGL.DAFTAR"));
		$grid->operationstitle="INFO";			  
		if(is_requester()){
		$grid->operations=array(array("url"=>$interfaceuri."tinjauan/:id","title"=>"Tinjauan","icon"=>"ico/application_view_list.png"),
								array("url"=>$interfaceuri."unduh/:id","title"=>"Unduh","icon"=>"ico/folder_page.png"));
		}
		else{
		$grid->operations=array(array("url"=>$interfaceuri."tinjauan/:id","title"=>"Tinjauan","icon"=>"ico/application_view_list.png"));
		}
		$grid->display();
	}
}


function pagetrial($currLogname,$currRole)
{
	global $urikeys,$puri,$interfaces,$messageAPI;
	$actionurl=home_url().$_SERVER['QUERY_STRING'];
	$keys=array("instance","service","method","reqtype","reqpar");
	$vtry=array("url"=>"","result"=>"","array"=>"","var"=>array());
	$avar=array();
	$uri="";
	$qry="";
	$url="";
	$method="";
	$accesskey="";
	$atable=array();
	
	if(isset($puri["id"]) && !empty($puri["id"])){
		$id=$puri["id"];
		if(is_requester()){
			$rs=getOrderResourceByID($id);
			$uri=home_url()."api/{$rs['instance']}/{$rs['servicename']}/{$rs['methodname']}";
			$url=home_url()."api/{$rs['instance']}/{$rs['servicename']}/";
			$method=$rs['methodname'];
			$accesskey=$rs['accesskey'];
		}
		else{		
			$rs=getResourceByID($id);
			$uri=home_url()."api/{$rs['instance']}/{$rs['servicename']}/{$rs['methodname']}";
			$url=home_url()."api/{$rs['instance']}/{$rs['servicename']}/";
			$method=$rs['methodname'];
			$accesskey='on';
		}
	}
	else return;

	$param="";
	if(isset($_POST["f_try"])){
		$vtry=array_merge($vtry,$_POST["f_try"]);
		if(isset($vtry["submit"]) && !empty($vtry["url"]) && isset($rs['methodname']) ){

			$par=$vtry["var"];
			$param=http_build_query($vtry["var"]);
			if($param!='') $param='/'.$param;
			$workspaceid=isset($_SESSION["workspaceid"])?$_SESSION["workspaceid"]:"";
			$skey=$accesskey=="on"?"idkey:".$workspaceid:$accesskey;
			$xmlcontent=callAPI($endpoint=$url,$operation=$method,$skey,$parameter=$par,$callmethod="REST","xml");			
			$vtry["result"]=htmlspecialchars($xmlcontent,ENT_QUOTES | ENT_XML1,'UTF-8');
			if($xmlcontent){
				$avar=setXML2Array($xmlcontent);
				$atable=$avar;
				$keyvar='response';
				if(isset($avar[$keyvar])){
					$tmp=$avar[$keyvar]['data'];
					$adim=count_dimension($tmp);
					if($adim>2){
						$arow=reset($tmp);
						if($arow){
							$akey=key($arow);
							if(is_numeric($akey)) $atable=$arow;
							else $atable=$tmp;	
						}
					}		
					elseif($adim==2){
						$atable=$tmp;
					}
				}			
			}	
			else $avar=array();
			$vtry["url"]=$uri.htmlentities(urldecode($param),ENT_QUOTES,"UTF-8");	
		}
	}
	else $vtry["url"]=$uri;


	$arrval="";
	$jsonval="";
	$serival="";
	if(count($avar)>0){
		$jsonval=(json_encode($avar,JSON_PRETTY_PRINT | JSON_FORCE_OBJECT | JSON_PARTIAL_OUTPUT_ON_ERROR));
		$arrval=str_replace("\r","#",var_export($avar,true));
		$serival=str_replace("\r","#",serialize($avar));
	}

	$input="";
	$output="";
	$varinput=array();
	if($rs){
		if($rs['methodtype']=='services'){
			$input="";
			$src=read_opws($rs['sourcecode']);
			$req=explode(',',$src['request']);
			foreach($req as $value)  if(!empty($value)){
				$par=explode('=',$value,2);
				if(isset($par[1]) && in_array($par[1],array("","''"))){
					$var=explode(':',$par[0],2);
					$vartype="text";
					if(isset($var[0])){
						$varname=$var[0];
						$varinput[]="".$varname."=?";
					}
					if(isset($var[1])) $vartype=$var[1];
					$event="";
					$val=isset($vtry["var"][$varname])?$vtry["var"][$varname]:"";
					if($vartype=="numeric") $event="onkeypress=\"return letterNumber(event,13)\"";
					$input.=$varname.": <br/><input type=\"text\" name=\"f_try[var][$varname]\" value=\"$val\" autofocus=\"autofocus\" style=\"width:20em;\" $event /><br/>";
				}
			}
		}

		if($rs['methodtype']=='database'){
			$input="";
			$src=read_opds($rs['sourcecode']);
			$existcond=strpos(strtoupper($src['conditions']),"WHERE");
			if($existcond===false){}
			else{
				$req=explode('@@',$src['conditions']);
				$opcount=0;
				foreach($req as $value) if(!empty($value)){
					if(strrpos($value," ''")===false){}
					else{
						$op="";
						if(substr($value,0,4)=="AND ") $op="AND";
						elseif(substr($value,0,3)=="OR ") $op="OR";
					
						$comp="";
						if(strrpos($value," LIKE ")===false){
							if(strrpos($value," <> ")===false){
								if(strrpos($value," <= ")===false){
									if(strrpos($value," >= ")===false){
										if(strrpos($value," < ")===false){
											if(strrpos($value," > ")===false){
												if(strrpos($value," != ")===false){
													if(strrpos($value," = ")===false){
														$comp="";
													}
													else $comp="=";
												}
												else $comp="<>";
											}
											else $comp=">";
										}
										else $comp="<";
									}
									else $comp=">=";
								}
								else $comp="<=";
							}
							else $comp="<>";
						}
						else $comp="LIKE";
			
						$varname=trim($value);
						$varname=ltrim($varname,"WHERE ");
						$varname=ltrim($varname,"AND ");
						$varname=ltrim($varname,"OR ");
						$varname=rtrim($varname," ''");
						$varname=rtrim($varname," LIKE");
						$varname=rtrim($varname," <>");
						$varname=rtrim($varname," <=");
						$varname=rtrim($varname," >=");
						$varname=rtrim($varname," <");
						$varname=rtrim($varname," >");
						$varname=rtrim($varname," =");
						$varname=trim($varname,"`");
						$varname=trim($varname,"\"");
						$varname=ltrim($varname,"[");
						$varname=rtrim($varname,"]");						
						$varname=trim($varname);
						$varname=str_replace(" ","_",$varname);
											
						$varinput[]="{$varname}=?";
						$val=isset($vtry["var"][$varname])?$vtry["var"][$varname]:"";
						if($opcount==0) $op="";
						$input.=$op." ".$varname." (".$comp."): <br/><input type=\"text\" name=\"f_try[var][$varname]\" value=\"$val\" autofocus=\"autofocus\" style=\"width:20em;\" /><br/>";
						$opcount++;
					}
				}
			}
			
			$cols=explode(',',$src['columns']);
			$output="";
			foreach($cols as $value) if(!empty($value)){
				$varname=trim($value);
				$varname=trim($varname,"`");
				$varname=trim($varname,"\"");
				$varname=ltrim($varname,"[");
				$varname=rtrim($varname,"]");
				$varname=trim($varname);
				$val=isset($vtry["var"][$varname])?$vtry["var"][$varname]:"";
				$output.="<li>".$varname."</li>";
			}
			if($output!='') $output="<ul>".$output."</ul>";
		}

		if($rs['methodtype']=='program'){
			$input="";
			$src=read_opln($rs['sourcecode']);
			$req=explode(',',$src['request']);
			foreach($req as $value) if(!empty($value)){
				$par=explode('=',$value,2);
				if(isset($par[1]) && in_array($par[1],array("","''"))){
					$var=explode(':',$par[0],2);
					$vartype="text";
					if(isset($var[0])){
						$varname=$var[0];
						$varinput[]="".$varname."=?";
					}
					if(isset($var[1])) $vartype=$var[1];
					$event="";
					$val=isset($vtry["var"][$varname])?htmlspecialchars($vtry["var"][$varname],ENT_QUOTES,'UTF-8'):"";
					if($vartype=="numeric") $event="onkeypress=\"return letterNumber(event,13)\"";
					$input.=$varname.": <br/><input type=\"text\" name=\"f_try[var][$varname]\" value=\"".$val."\" autofocus=\"autofocus\" style=\"width:20em;\" $event /><br/>";
				}
			}
		}
	}
	
	if(empty($param) && !empty($varinput)){
		$varinputs=implode('&',$varinput);
		$vtry["url"].='/'.$varinputs;
	}
	
	?>
	
	<div class="dialog">
	<form name="f_try" action="" method="post" enctype="application/x-www-form-urlencoded">
	Endpoint/URL Web-API (REST-MANTRA): <br/><input type="text" name="f_try[url]" value="<?php echo ($vtry["url"]);?>" readonly="readonly" style="width:99%;"/><br/>
	<?php if($accesskey!="on"){?>
	<span style="text-decoration:underline;">Kunci Akses</span><br/>
	<?php echo $accesskey;?>
	<br/>
	<?php }?>
	<?php if($output){?>
	<span style="text-decoration:underline;">Elemen Data Keluaran</span><br/>
	<?php echo $output;?>
	<br/>
	<?php }?>
	<?php if($input){?>
	<span style="text-decoration:underline;">Parameter Masukan</span><br/>
	<?php echo $input;?>
	<br/>
	<?php }?>
	<input type="submit" name="f_try[submit]" value="Pratinjau"/>
	<hr/>

	<?php $i=0;$maxdata=count($atable);?>
	<?php if($maxdata>0){?>
	<p>Data Format HTML (Hyper Text Markup Language):</p>
	<?php navdata(1,$maxdata);?>
	<?php foreach($atable as $no=>$grows){?>
	<div id="<?php echo "datalist".($i+1);?>" class="datalist preview" style="overflow-x:scroll;<?php echo $i==0?"display:block":"display:none";?>;" >
	<?php viewdata($no,$grows);$i++;?>
	</div>
	<?php }?>
	<?php }?>
	<br/>
	<div style="<?php echo $maxdata>0?"display:block":"display:none";?>">
	
	<p>Data Format XML (eXtensible Markup Language):</p>
	<pre style="max-height:30em;overflow-y:scroll;padding-left:.5em;font-family:consolas;background-color:#fafafa;border:1px solid silver"><?php echo $vtry["result"];?></pre>
	
	<p><br/>Data Format JSON (Java Script Object Notation):</p>
	<pre style="max-height:30em;overflow-y:scroll;padding-left:.5em;font-family:consolas;background-color:#fafafa;border:1px solid silver"><?php echo $jsonval;?></pre>
	
	<p><br/>Data Format Array PHP:</p>
	<pre style="max-height:30em;overflow-y:scroll;padding-left:.5em;font-family:consolas;background-color:#fafafa;border:1px solid silver"><?php echo $arrval;?></pre>	
	
	<p><br/>Data Format Serial PHP:</p>
	<pre style="height:3em;overflow-y:scroll;padding-left:.5em;font-family:consolas;background-color:#fafafa;border:1px solid silver"><?php echo $serival;?></pre>
	
	</div>
	</form>
	</div>
	
	<?php
}

function navdata($navid,$maxdata)
{	
	if($maxdata>1){
	?>
	<div class="page-nav" style="text-align:right;">
	<span>Jumlah Data: <?php echo $maxdata;?> Baris.&nbsp;</span>
	<button type="button" class="nav-button" onclick="goPageTable(<?php echo $navid;?>,'datalist','<','<?php echo $maxdata;?>');">&laquo;</button>
	<button type="button" class="nav-button" onclick="goPageTable(<?php echo $navid;?>,'datalist','-','<?php echo $maxdata;?>');">&lsaquo;</button>
	<span id="nav-pagenum<?php echo $navid;?>" >
		<script runat="server" type="text/javascript" autoload="true">	
			goPageTable(<?php echo $navid;?>,'datalist','<','<?php echo $maxdata;?>');			
		</script>
	</span>
	<button type="button" class="nav-button" onclick="goPageTable(<?php echo $navid;?>,'datalist','+','<?php echo $maxdata;?>');">&rsaquo;</button>
	<button type="button" class="nav-button" onclick="goPageTable(<?php echo $navid;?>,'datalist','>','<?php echo $maxdata;?>');">&raquo;</button>
	</div>
	<?php
	}
}


function viewdata($keys,$grows)
{
?>
	<table>

	<tr class="header">
		<td nowrap="nowrap">Nama Elemen</td>
		<td width="100%">Data</td>
	</tr>
	
	<?php foreach($grows as $key=>$rows){?>
	<?php $artrow=($keys%2)>0?"evenrow":"oddrow";?>
	<tr>
		<td style="background-color:#dfdfdf;"><?php echo $key;?></td>
		<td style="background-color:#efefef;">
		<?php 
			if(is_array($rows)){
				viewdata($key,$rows);
			}
			else{
				echo html_entity_decode($rows, ENT_QUOTES);
			}
		?>
		</td>
	</tr>
	<?php }?>

	</table>
<?php
}

function pagedownload($currLogname,$currRole)
{
	global $urikeys,$puri,$interfaces;


	$samplejsonjava='sampleJSON.java';
	$samplejson='sample-json.php';
	$samplexml='sample-xml.php';
	$sample='sample.php';
	$adapter='adapter.php';
	$uri="";
	$url="";
	$urlxml="";
	$urljson="";
	$urljsonjava="";
	$method="";
	
	if(isset($puri["id"]) && !empty($puri["id"])){
		$id=$puri["id"];
		if(is_requester()){
			$rs=getOrderResourceByID($id);
			$uri=home_url()."api/{$rs['instance']}/{$rs['servicename']}/{$rs['methodname']}/";
			$url=home_url()."api/{$rs['instance']}/{$rs['servicename']}/";
			$urixml=home_url()."xml/{$rs['instance']}/{$rs['servicename']}/{$rs['methodname']}/";
			$urlxml=home_url()."xml/{$rs['instance']}/{$rs['servicename']}/";
			$urijson=home_url()."json/{$rs['instance']}/{$rs['servicename']}/{$rs['methodname']}/";
			$urljson=home_url()."json/{$rs['instance']}/{$rs['servicename']}/";
			$urijsonjava=home_url()."json/{$rs['instance']}/{$rs['servicename']}/{$rs['methodname']}/";
			$urljsonjava=home_url()."json/{$rs['instance']}/{$rs['servicename']}/";
			$method=$rs['methodname'];
			$accesskey=$rs['accesskey'];
		}
		else{		
			$rs=getResourceByID($id);
			$uri=home_url()."api/{$rs['instance']}/{$rs['servicename']}/{$rs['methodname']}/";
			$url=home_url()."api/{$rs['instance']}/{$rs['servicename']}/";
			$urixml=home_url()."xml/{$rs['instance']}/{$rs['servicename']}/{$rs['methodname']}/";
			$urlxml=home_url()."xml/{$rs['instance']}/{$rs['servicename']}/";
			$urijson=home_url()."json/{$rs['instance']}/{$rs['servicename']}/{$rs['methodname']}/";
			$urljson=home_url()."json/{$rs['instance']}/{$rs['servicename']}/";
			$urijsonjava=home_url()."json/{$rs['instance']}/{$rs['servicename']}/{$rs['methodname']}/";
			$urljsonjava=home_url()."json/{$rs['instance']}/{$rs['servicename']}/";
			$method=$rs['methodname'];
			$accesskey='on';
		}
	}
	else return;

	if($rs){
		if( ($rs['methodtype']=='services') ){
			$input="";$inputjava="";
			$src=read_opws($rs['sourcecode']);
			$req=explode(',',$src['request']);
			foreach($req as $value) if(!empty($value)){
				$par=explode('=',$value,2);
				if(isset($par[1]) && in_array($par[1],array("","''"))){
					$var=explode(':',$par[0],2);
					if(isset($var[0])) $varname=$var[0];
					if(isset($var[1])) $vartype=$var[1];
					$varname=trim($varname);
					if($varname!=''){
						$input.="<div>".$varname.":</div>\n<input type=\"text\" name=\"par[$varname]\" value=\"\" size=\"40\"/>\n";
						$inputjava.=($inputjava==""?$varname:",".$varname);
					}
				}
			}
		}

		if($rs['methodtype']=='database'){
			$input="";$inputjava="";
			$src=read_opds($rs['sourcecode']);
			$existcond=strpos(strtoupper($src['conditions']),"WHERE");
			if($existcond===false){}
			else{
				$req=explode('@@',$src['conditions']);
				foreach($req as $value) if(!empty($value)){
					if(strrpos($value," ''")===false){}
					else{
						$varname=trim($value);
						$varname=ltrim($varname,"WHERE ");
						$varname=ltrim($varname,"AND ");
						$varname=ltrim($varname,"OR ");
						$varname=rtrim($varname," ''");
						$varname=rtrim($varname," LIKE");
						$varname=rtrim($varname," <>");
						$varname=rtrim($varname," <=");
						$varname=rtrim($varname," >=");
						$varname=rtrim($varname," <");
						$varname=rtrim($varname," >");
						$varname=rtrim($varname," =");
						$varname=trim($varname,"`");
						$varname=trim($varname,"\"");
						$varname=ltrim($varname,"[");
						$varname=rtrim($varname,"]");						
						$varname=trim($varname);
						$varname=str_replace(" ","_",$varname);
						if($varname!=''){
							$input.="<div>".$varname.":</div>\n<input type=\"text\" name=\"par[$varname]\" value=\"\" size=\"40\"/>\n";
							$inputjava.=($inputjava==""?$varname:",".$varname);
						}
					}
				}
			}
		}

		if( ($rs['methodtype']=='program') ){
			$input="";$inputjava="";
			$src=read_opln($rs['sourcecode']);
			$req=explode(',',$src['request']);
			foreach($req as $value) if(!empty($value)){
				$par=explode('=',$value,2);
				if(isset($par[1]) && in_array($par[1],array("","''"))){
					$var=explode(':',$par[0],2);
					if(isset($var[0])) $varname=$var[0];
					if(isset($var[1])) $vartype=$var[1];
					$varname=trim($varname);
					if($varname!=''){
						$input.="<div>".$varname.":</div>\n<input type=\"text\" name=\"par[$varname]\" value=\"\" size=\"40\"/>\n";
						$inputjava.=($inputjava==""?$varname:",".$varname);
					}
				}
			}
		}
		
	}
	?>
	<div class="dialog">
		<p>
		Sisipkan berkas berikut ini pada aplikasi web anda, sehingga dapat melakukan koneksi ke API/Webservice MANTRA dan memanfaatkan metode/fungsi pengolahan data yang tersedia. 
		Unduh berkas dengan cara 'click mouse' pada nama berkas dibawah ini.
		</p>
	<?php
	if(file_exists(REPODIR.$adapter)){
		$strfile=file_get_contents(REPODIR.$adapter,FILE_TEXT);
		?>
		<p><a style="text-decoration:underline;" href="download/name=<?php echo base64_encode($adapter);?>"><strong><?php echo $adapter;?></strong></a></p>
		<textarea wrap="off" class="sourcecode" rows="15" readonly="readonly" style="width:99%;"><?php echo htmlspecialchars($strfile,ENT_QUOTES);?></textarea>
		<br/>
		<br/>
		<?php
	}

	if(file_exists(REPODIR.$sample)){
		$strfile=file_get_contents(REPODIR.$sample,FILE_TEXT);
		$strfile=str_replace('%INPUT%',$input,$strfile);
		$strfile=str_replace('%URL%',$url,$strfile);
		$strfile=str_replace('%METHOD%',$method,$strfile);
		$strfile=str_replace('%ACCESSKEY%',$accesskey,$strfile);
		?>
		<p><a style="text-decoration:underline;" href="download/name=<?php echo base64_encode($sample);?>&url=<?php echo base64_encode($url);?>&method=<?php echo base64_encode($method);?>&accesskey=<?php echo base64_encode($accesskey);?>&input=<?php echo base64_encode(htmlentities($input));?>"><strong><?php echo $sample;?></strong></a></p>
		<textarea wrap="off" class="sourcecode" rows="15" readonly="readonly" style="width:99%;"><?php echo htmlspecialchars($strfile,ENT_QUOTES);?></textarea>
		<br/>
		<?php
	}

	if(file_exists(REPODIR.$samplexml)){
		$strfile=file_get_contents(REPODIR.$samplexml,FILE_TEXT);
		$strfile=str_replace('%INPUT%',$input,$strfile);
		$strfile=str_replace('%URL%',$urlxml,$strfile);
		$strfile=str_replace('%METHOD%',$method,$strfile);
		$strfile=str_replace('%ACCESSKEY%',$accesskey,$strfile);
		?>
		<p><a style="text-decoration:underline;" href="download/name=<?php echo base64_encode($samplexml);?>&url=<?php echo base64_encode($urlxml);?>&method=<?php echo base64_encode($method);?>&accesskey=<?php echo base64_encode($accesskey);?>&input=<?php echo base64_encode(htmlentities($input));?>"><strong><?php echo $samplexml;?></strong></a></p>
		<textarea wrap="off" class="sourcecode" rows="15" readonly="readonly" style="width:99%;"><?php echo htmlspecialchars($strfile,ENT_QUOTES);?></textarea>
		<br/>
		<?php
	}

	if(file_exists(REPODIR.$samplejson)){
		$strfile=file_get_contents(REPODIR.$samplejson,FILE_TEXT);
		$strfile=str_replace('%INPUT%',$input,$strfile);
		$strfile=str_replace('%URL%',$urljson,$strfile);
		$strfile=str_replace('%METHOD%',$method,$strfile);
		$strfile=str_replace('%ACCESSKEY%',$accesskey,$strfile);
		?>
		<p><a style="text-decoration:underline;" href="download/name=<?php echo base64_encode($samplejson);?>&url=<?php echo base64_encode($urljson);?>&method=<?php echo base64_encode($method);?>&accesskey=<?php echo base64_encode($accesskey);?>&input=<?php echo base64_encode(htmlentities($input));?>"><strong><?php echo $samplejson;?></strong></a></p>
		<textarea wrap="off" class="sourcecode" rows="15" readonly="readonly" style="width:99%;"><?php echo htmlspecialchars($strfile,ENT_QUOTES);?></textarea>
		<br/>
		<?php
	}

	if(file_exists(REPODIR.$samplejsonjava)){
		$strfile=file_get_contents(REPODIR.$samplejsonjava,FILE_TEXT);
		$strfile=str_replace('%INPUT%',$inputjava,$strfile);
		$strfile=str_replace('%URL%',$urljsonjava,$strfile);
		$strfile=str_replace('%METHOD%',$method,$strfile);
		$strfile=str_replace('%ACCESSKEY%',$accesskey,$strfile);
		?>
		<p><a style="text-decoration:underline;" href="download/name=<?php echo base64_encode($samplejsonjava);?>&url=<?php echo base64_encode($urijsonjava);?>&method=<?php echo base64_encode($method);?>&accesskey=<?php echo base64_encode($accesskey);?>&input=<?php echo base64_encode(htmlentities($inputjava));?>"><strong><?php echo $samplejsonjava;?></strong></a></p>
		<textarea wrap="off" class="sourcecode" rows="15" readonly="readonly" style="width:99%;"><?php echo htmlspecialchars($strfile,ENT_QUOTES);?></textarea>
		<br/>
		<?php
	}

	?>
	</div>
	<?php
}
