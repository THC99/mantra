<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
ver:1.99y
*/
	header('Content-Type:text/plain;charset=UTF-8');	
	if($_SERVER['HTTP_USER_AGENT']!="MANTRA") exit(0);
	if(!isset($_SERVER['HTTP_ACCESSKEY'])) exit(0);

	require_once SYSPATH.'config.php';
	require_once SYSPATH.'common.php';
	require_once SYSPATH.'models.php';
	require_once SYSPATH.'apifunction.php';
	require_once SYSPATH.'apiconnector.php';
	require_once LIBDIR.'nusoap/nusoap.php';
		
	error_reporting(E_ERROR); 
	$result=false;	
	$xml="";
	$reqtype="";
	$workspaceid="";
	$trackid="illegal";
	$trackstatus="ERROR";
	$trackdata="";
	$trackprovider="unknown";
	$trackuser="unknown";
	$rootkeytag="response";
	$rs=array();
	$qapi=array();
	$axml=array();
	$pathnames=array("provider","service","method","reqpar");
	$paramTrue=true;

	//--------------------- Inisiasi HTTP ACCESSKEY
	$httpkey=$_SERVER['HTTP_ACCESSKEY'];
	if(substr($httpkey,0,strlen("idkey:"))=="idkey:"){
		$workspaceid=unserialize(dec64data(substr($httpkey,strlen("idkey:"))));
		list($idname,$idkey)=$workspaceid;
		if(substr($idkey,0,4)!="$2y$") $idkey=base64_decode($idkey);
		$validAccess=validUser(array($idname,$idkey),$dataid);
		if($validAccess){ 
			$reqtype="on";	
			$trackuser=$idname;
		}			
	}
	else{
		$reqtype=$httpkey;
	}
	
	//----------------------- Inisiasi Full URI	
	$_requri=$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].urldecode($_SERVER['REQUEST_URI']);
	$requested_uri=(substr($_requri,-1)=="/"?substr($_requri,0,-1):$_requri);

	//----------------------- Susun URI Path ke dalam Array 
	$apitype="api";
	$apipath=URL_BASEPATH."api/";
	$_uri=$_SERVER["REQUEST_URI"];
	
	if(strpos($_uri,URL_BASEPATH."xml/")!==false){
		$apitype="xml";
		$apipath=URL_BASEPATH."xml/";
	}
	if(strpos($_uri,URL_BASEPATH."json/")!==false){
		$apitype="json";
		$apipath=URL_BASEPATH."json/";
	}

	$requri=str_replace($apipath, "", $_SERVER['REQUEST_URI']);
	$requri.=substr($requri,-1)=="/"?"":"/";
	
	if(!empty($requri)){
		$vapi=explode("/",$requri);
		if(count($vapi)>count($pathnames)){
			$vapi=array_slice($vapi,0,count($pathnames));
		}
		else{
			$vapi=array_pad($vapi,count($pathnames),'');
		}
		$qapi=array_combine($pathnames,$vapi);
	}

	//------------- Periksa jenis request
	if(empty($reqtype)){
		$response=array('status'=>0,'code'=>10001,'message'=>'Operasi tidak memiliki izin, karena kunci akses tidak terdaftar (kosong)','data'=>'');	
		$axml=array($rootkeytag=>$response);
	}
	else{
		//----------- Inisiasi reqpar dari parameter GET atau POST
		$reqpar=array();
		if($qapi["reqpar"]==""){
			if(!empty($_POST)) $reqpar=$_POST;
		}
		else{
			parse_str($qapi["reqpar"],$reqpar);
		}
		//----------- Decode nilai parameter query get atau post				
		if(!empty($reqpar)) foreach($reqpar as $keyname=>$datavalue){
			$reqpar[$keyname]=urldecode($datavalue);
		}
		//----------- Ambil data resource API
		if($reqtype=="on") $rs=getResourceByScope($qapi["provider"],$qapi["service"],$qapi["method"]);
		else $rs=getOrderResourceByScope($reqtype,$qapi["provider"],$qapi["service"],$qapi["method"]);
		
		//------------------ Proses resource API sesuai request URI -------------------\\
		if(empty($rs)){
			$response=array('status'=>0,'code'=>10002,'message'=>'Fungsi operasi tidak terdaftar','data'=>'');	
			$axml=array($rootkeytag=>$response);
		}
		else{

			switch(strtolower($rs["methodtype"])){
			case "services":
				$result=false;
				$axml=array();
				$src=read_opws($rs["sourcecode"]);
				$wsendpoint=isset($src["endpoint"])?$src["endpoint"]:"";
				$wstype=isset($src["wstype"])?$src["wstype"]:"";
				$wskey=isset($src["accesskey"])?$src["accesskey"]:"";
				$wsmethod=isset($src["method"])?$src["method"]:"";
				$wsrequests=array();
				if(isset($src["request"]) && !empty($src["request"]) ){
					// Pengisian nilai pada parameter layanan yang sudah didefinisikan
					$wsreq=explode(",",$src["request"]);
					foreach($wsreq as $values){
						$key=str_replace(strstr($values,":"),"",$values);
						$datatype=str_replace(strstr($values,"="),"",strstr($values,":"));
						$data=str_replace("=","",strstr($values,"="));
						$data=str_replace("''","",$data);
						if(isset($reqpar[$key])){
							$data=empty($data)?$reqpar[$key]:$data;
						}
						if(empty($data)) $paramTrue=false;
						$wsrequests[$key]=$data;
					}
				}				
				if(!empty($wsendpoint) && $paramTrue==true){
				
					if($wstype=="soap"){ // Akses dengan SOAP adapter
						$data=array();
						$wsblock="";
						$wsdeep=1;   // method#deep#block contoh: kk#3#valid_data
						if(!empty($wsmethod)){
							$block=explode("#",$wsmethod);
							$wsmethod=$block[0];
							if(isset($block[1])) $wsdeep=intval($block[1]);
							if(isset($block[2])) $wsblock=$block[2];
						}
						$query=$wsrequests;						
						if($wsdeep>1) for($i=1;$i<$wsdeep;$i++){
							$query=array($query);
						}
						$soapapi=new nusoap_client($endpoint=$wsendpoint."?wsdl");
						$soapapi->soap_defencoding = 'UTF-8';
						//$soapapi->timeout=10;
						$soapapi->response_timeout=15;
						$err=$soapapi->getError();
						if($err){
							$response=array('status'=>0,'code'=>10006,'message'=>"SOAP Constructor Error: ".$err,'data'=>'' );
							$axml=array('response'=>$response);
						}
						else{
							$data = $soapapi->call($wsmethod, $query);
							if ($soapapi->fault){
								$response=array('status'=>0,'code'=>10007,'message'=>"The request contains an invalid SOAP body: ".print_r($data,true),'data'=>'' );
								$axml=array('response'=>$response);
							}
							else{
								$err=$soapapi->getError();
								if($err){
									$response=array('status'=>0,'code'=>10008,'message'=>$err,'data'=>'');
									$axml=array('response'=>$response);
								}
								else{
									if($data==null){
										$response=array('status'=>0,'code'=>10009,'message'=>"Hasil fungsi operasi SOAP \"".$qapi["provider"].":".$qapi["service"].".".$qapi["method"]."\" tidak ada (kosong)",'data'=>'');
										$axml=array('response'=>$response);
									}
									else{
										$tagapi='response';
										if($wsblock=="") $wsblock=$wsmethod;
										$acontent=array($wsblock=>$data);
										if(!isset($data[$tagapi]['status'])){
											$response=array('status'=>1,'code'=>200,'message'=>'OK','data'=>$acontent);
											$axml=array($rootkeytag=>$response);
										}
										else{
											$axml=$acontent;										
										}
										$result=count($axml)>0;
									}
								}
							}
						}
					}
					elseif(in_array($wstype,array('get','post','rest','restfull','restfullpar'))){ // Akses dengan HTTP/REST adapter
						$data=callAPI($endpoint=$wsendpoint,$operation=$wsmethod,$accesskey=$wskey,$parameter=$wsrequests,$callmethod=$wstype);
						if(count($data)>0){
							$tagapi=isset($data["valid_response"])?"valid_response":(isset($data["invalid_response"])?"invalid_response":"error_response");			
							if(isset($data[$tagapi])) $data=$data[$tagapi];
						}
						if(!isset($data[$rootkeytag]['status'])){
							$response=array('status'=>1,'code'=>200,'message'=>'OK','data'=>$data);
							$axml=array($rootkeytag=>$response);
						}
						else{
							$axml=$data;
						}
						$result=count($axml)>0;
					}
					
				}
				elseif(!$paramTrue){
					$response=array('status'=>0,'code'=>10005,'message'=>'Seluruh parameter fungsi operasi wajib diisi','data'=>'');
					$axml=array($rootkeytag=>$response);
				}
				elseif(empty($wsmethod)){
					$response=array('status'=>0,'code'=>10004,'message'=>'Nama fungsi operasi tidak terdefinisi','data'=>'');
					$axml=array($rootkeytag=>$response);
				}
				else{
					$response=array('status'=>0,'code'=>10003,'message'=>'Alamat (URL) akses layanan tidak terdefinisi','data'=>'');
					$axml=array($rootkeytag=>$response);
				}
				break;
				//endcase services
				
			case "database":
				$result=false;
				$rsc=array();
				$axml=array();
				
				$tagmethod=$qapi["method"];
				$tagmethod=str_replace(" ","_",str_replace(".","_",str_replace("-","_",str_replace(":","_",$tagmethod))));
				
				if(!isValidTagXML($tagmethod)){
					$response=array('status'=>0,'code'=>10010,'message'=>"Nama fungsi operasi data tidak direkomendasikan: {$tagmethod}",'data'=>'');
					$axml=array($rootkeytag=>$response);
				}
				else{
				
					$dsn=read_opds($rs["sourcecode"]);

					$tbl=$dsn["tbname"]==""?"":$dsn["tbname"];
					$col=$dsn["columns"]==""?"*":$dsn["columns"];
					if($tbl.$col=="*"){
						$response=array('status'=>0,'code'=>10011,'message'=>"Perintal SQL \"".$qapi["provider"].":".$qapi["service"].".".$qapi["method"]."\" gagal dioperasikan",'data'=>'');
						$axml=array($rootkeytag=>$response);
						$result=false;
						break;
					}
				
					$tbname=$tbl;
					$tbname=trim($tbname);
					$tbname=trim($tbname,"`");
					$tbname=trim($tbname,"\"");
					$tbname=ltrim($tbname,"[");
					$tbname=rtrim($tbname,"]");						
					$tbname=trim($tbname);
					if($dsn['dbtype']=="mysql" or $dsn['dbtype']=="mysqli"){
						$tbname="`".$tbname."`";
					}
					elseif($dsn['dbtype']=="mssql"){
						$tbname="[".$tbname."]";
					}
					elseif($dsn['dbtype']=="postgres"){
						$tbname="\"".$tbname."\"";
					}
					elseif($dsn['dbtype']=="oci8"){
						$tbname="\"".$tbname."\"";
					}

					$where="";$bindval=false;
					$existcond=strpos(strtoupper($dsn['conditions']),"WHERE");
					if($existcond===false){
						$onwhere="";
					}
					else{
						$bindval=array();
						$reqcond=explode('@@',$dsn['conditions']);
						foreach($reqcond as $value){				
							$value=trim($value," ");
							if(strrpos($value," ''")===false){
								$varname=str_replace("WHERE ","",$value);
								$where.=$varname." ";
							}
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
								$var_name=str_replace(" ","_",$varname);
													
								if($op!="") $where.=" ".$op." ";
				
								if($dsn['dbtype']=="mysql" or $dsn['dbtype']=="mysqli"){
									$where.=" `{$varname}` $comp ? ";
								}
								elseif($dsn['dbtype']=="mssql"){
									$where.=" [{$varname}] $comp ? ";
								}
								elseif($dsn['dbtype']=="postgres"){
									$where.=" \"{$varname}\" $comp ? ";
								}
								elseif($dsn['dbtype']=="oci8"){
									$where.=" \"{$varname}\" $comp :$var_name ";
								}

								if(count($reqpar)>0)
								foreach($reqpar as $key=>$valpar){
									if(strtolower($key)==strtolower($var_name) and $valpar!=""){
										$bindval[$var_name]=$valpar;
									}
								}
							
							}
						}
						if(!empty($where)) $onwhere="WHERE ".$where;
					}				
				
					$ord=$dsn["orders"]==""?"":"ORDER BY ".$dsn["orders"];

					if(in_array($dsn["dbtype"],array("mysql","postgres") )){
						$limrows=intval($dsn["limrows"])==0?"":$dsn["limrows"];
						$limoffset=intval($dsn["limoffset"])==0?"":$dsn["limoffset"];
						$limit="";
						if($limrows!=""){
							$limit=$limrows;
							if($limoffset!="") $limit.=" OFFSET ".$limoffset;
							$limit="LIMIT ".$limit;
						}
					}
				
					$sql="SELECT ".$col." FROM ".$tbname." ".$onwhere." ".$ord." ".$limit;
					
					if(!empty($reqpar) && empty($bindval)){
						$response=array('status'=>0,'code'=>10005,'message'=>'Seluruh parameter fungsi operasi wajib diisi','data'=>'');
						$axml=array($rootkeytag=>$response);
						$result=false;
						break;
					}
											
					$errcode=0;
					try{
						$adodb= newADOConnection($dsn['dbtype']);
						$adodb->debug=false;
						$adodb->setFetchMode(ADODB_FETCH_ASSOC); 
						if($dsn['port']!="") $dsn['hostname'].=":".$dsn['port'];
						$adodb->connect($dsn['hostname'],$dsn['dbuser'],$dsn['dbpassword'],$dsn['dbname']);
						$rsc=$adodb->getAll($sql,$bindval);
					}
					catch(exception $e){
						$response=array('status'=>0,'code'=>$e->getCode(),'message'=>'Koneksi database gagal atau perintah SQL keliru. '.$e->getMessage(),'data'=>'');
						$axml=array($rootkeytag=>$response);
						$errcode=$e->getCode();
					}
				
					if(!$errcode){
						$xml='';
						if(count($rsc)<1){
							$response=array('status'=>0,'code'=>10012,'message'=>"Hasil operasi data \"".$qapi["provider"].":".$qapi["service"].".".$qapi["method"]."\" tidak ada (kosong)",'data'=>'');
							$axml=array($rootkeytag=>$response);
							$result=false;
						}
						else{
							$tag=$qapi["method"];
							$tag=str_replace(" ","_",str_replace(".","_",str_replace("-","_",str_replace(":","_",$tag))));

							foreach($rsc as $rec){
								$axml[]=$rec;
							}
							if(count($axml)>0){
								$response=array('status'=>1,'code'=>200,'message'=>'OK','data'=>array($tag=>$axml));
								$axml=array($rootkeytag=>$response);
								$result=true;
							}
							else{
								$response=array('status'=>0,'code'=>10013,'message'=>"Hasil operasi data \"".$qapi["provider"].":".$qapi["service"].".".$qapi["method"]."\" tidak terhimpun",'data'=>'');
								$axml=array($rootkeytag=>$response);
							}
						}
					}
				}
				break;
				//endcase database	
				
			case "program":
			default:
				$axml=array();
				$result=false;

				$tagmethod=$qapi["method"];
				$tagmethod=str_replace(" ","_",str_replace(".","_",str_replace("-","_",str_replace(":","_",$tagmethod))));
				
				if(!isValidTagXML($tagmethod)){
					$response=array('status'=>0,'code'=>10014,'message'=>"Nama fungsi operasi program tidak direkomendasikan: {$tagmethod}",'data'=>'');
					$axml=array($rootkeytag=>$response);
				}
				else{
					$opln=read_opln($rs["sourcecode"]);
					$requestln="";
					if(isset($opln["request"]) && !empty($opln["request"]) ){
						$reqln=explode(",",$opln["request"]);
						// Pengisian data parameter hanya pada parameter layanan yang sudah didefinisikan
						foreach($reqln as $values){
							$key=str_replace(strstr($values,":"),"",$values);
							$datatype=str_replace(strstr($values,"="),"",strstr($values,":"));
							$data=str_replace("=","",strstr($values,"="));
							$data=str_replace("''","",$data);
							if(isset($reqpar[$key])){
								$data=empty($data)?"'".$reqpar[$key]."'":$data;
							}
							if(!empty($data)){
								$data=strip_tags($data);
								$data=stripslashes($data);
								//$data=htmlentities($data,ENT_QUOTES,"UTF-8");
							}
							$requestln.="$".$key."=".$data.";";
						}
					}
					//file_put_contents(TMPDIR.'apirequest.txt',$requestln."\n");
					$src=strip_tags($opln["code"]);
					$src=$requestln." ".$src;
					ob_start();
					$resultdata=null;
					$codes=eval($src);
					if($codes===false){
						$response=array('status'=>0,'code'=>10015,'message'=>"Operasi program \"".$qapi["provider"].":".$qapi["service"].".".$qapi["method"]."\" gagal",'data'=>'');	
						$axml=array($rootkeytag=>$response);
					}
					else{
						$resultcode=ob_get_clean();					
						
						$resultdata=unserialize($resultcode);
						if(!$resultdata) $resultdata=array('value'=>$resultcode);
						elseif(!is_array($resultdata)) $resultdata=array('value'=>$resultdata);
						if(empty($resultdata)){
							$response=array('status'=>0,'code'=>10016,'message'=>"Hasil operasi program \"".$qapi["provider"].":".$qapi["service"].".".$qapi["method"]."\" tidak ada (kosong)",'data'=>'');
							$axml=array($rootkeytag=>$response);						
						}
						else{
							$result=true;
							if(isset($resultdata[$rootkeytag]['status'])){
								$axml=$resultdata;
							}
							else{
								$response=array('status'=>1,'code'=>200,'message'=>"OK",'data'=>array($tagmethod=>$resultdata));
								$axml=array($rootkeytag=>$response);
							}  
						}
						
					}//endif($code==false)
					ob_end_clean();
				}//endif(!isValidTagXML($tagmethod)) 
				//endcase program
			}//endswitch
			
			//---------------- Periksa dan baca data dari dalam cache
			$cache=array();
			if(APP_CACHE){
				if(!$result){
					$cache=readCache(rawurldecode($requested_uri));
					if($cache["validity"]){
						$result=$cache["validity"];
						$axml=unserialize($cache["content"]);
					}
				}
			}
			
			//---------------- Periksa Kunci Indeks dan Tag
			if(!isset($axml[$rootkeytag]) ){
				$response=array('status'=>0,'code'=>10017,'message'=>"Kunci indeks/tag utama data fungsi operasi tidak dikenal: ".print_r($axml,true),'data'=>'');	
				$axml=array($rootkeytag=>$response);
			}
			
			//---------------- Simpan ke dalam cache 
			if(APP_CACHE){ 
				if(empty($cache) && $result) writeCache(rawurldecode($requested_uri),serialize($axml),$result);
			}
			
			//------------------------ Get Track Data
			$trackid=strtolower($rs["methodtype"]);
			$trackprovider=$rs['instance'];
			if($reqtype!="on"){
				if(isset($rs['userlog'])) $trackuser=$rs['userlog'];
			}
			$trackstatus=(isset($axml[$rootkeytag]["status"]) && $axml[$rootkeytag]["status"]==1)?"SUCCESS":"FAIL";
			if(strpos($rs["sourcecode"],"{")==0){
				$commandsource=json_decode($rs["sourcecode"],true);
				$commandsource['uri']=$requested_uri;
				$trackdata=json_encode(array("command"=>$commandsource,$rootkeytag=>$axml[$rootkeytag]),JSON_PRETTY_PRINT | JSON_FORCE_OBJECT | JSON_PARTIAL_OUTPUT_ON_ERROR);
			}
			else $trackdata="{\n\ruri: ".$requested_uri."\n\r".$rs["sourcecode"]."\n\r}\n\r".$rs["sourcecode"];

		}//endif(empty($rs))

	}//endif(empty($reqtype))


	//------------ Simpan Riwayat Akses ---------------------\\
	if(isset($axml[$rootkeytag])) addTrack(array("trackid"=>$trackid,"trackname"=>"api","trackstatus"=>$trackstatus,"tracknote"=>"","trackdata"=>stripslashes($trackdata)),$trackprovider,$trackuser);
				
	//------------ Konversi format data	(JSON/XML)
	$apidata="";
	if($apitype=="xml"){
		try{
			$xml=setArray2XML($rootkeytag,$axml[$rootkeytag]);
		}
		catch(exception $e){
			$response=array('status'=>0,'code'=>10018,'message'=>$e->getMessage().", Kunci indeks data tidak dapat digunakan sebagai Tag format XML = ".var_export($axml,true),'data'=>'');	
			$axml=array($rootkeytag=>$response);
			$xml=setArray2XML($rootkeytag,$axml[$rootkeytag]);
		}
		$xml=tidyXML($xml);
		$apidata=($xml);
	}
	elseif($apitype=="json"){
		$apidata=(json_encode($axml, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT | JSON_PARTIAL_OUTPUT_ON_ERROR));
	}
	else{
		$apidata=serialize($axml);
	}
	
	//------------ Periksa encode data
	if(!empty($apidata) && APP_ENC){
		echo enc64data($apidata);
	}
	else{
		echo $apidata;
	}
	
	//file_put_contents(TMPDIR.'apidata.txt',$apidata."\n\r");
	
