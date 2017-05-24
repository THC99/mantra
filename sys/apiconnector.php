<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
ver:1.99x
*/


function callAPI($endpoint,$operation,$accesskey='',$parameter=array(),$callmethod='REST',$outputformat='array'){
	$result=false;
	$axml=array();
	$agent="MANTRA";
	$rootkeytag='response';
	$callmethod=strtoupper($callmethod);
	
	if(empty($endpoint)){ 
		$response=array('status'=>0,'code'=>20001,'message'=>'URL/EndPoint tidak terdefinisi (kosong)','data'=>'');
		$axml=array($rootkeytag=>$response);	
	}
	else{		
		//persiapkan parameter untuk method REST, RESTFULL, dan RESTFULLPAR
		$rest_pars='';
		$http_pars=array();
		if($callmethod=='REST' && !empty($parameter)){ 
			$apar=array();
			foreach($parameter as $key=>$value){
				$apar[$key]=urlencode($value); 
			}
			$rest_pars=http_build_query($apar);
		}

		if($callmethod=='RESTFULL' && !empty($parameter)){
			$apar=array();
			foreach($parameter as $key=>$value){
				$apar[$key]=urlencode($value); 
			}
			$rest_pars=implode('/',$apar);
		}

		if($callmethod=='RESTFULLPAR' && !empty($parameter)){
			$rest_pars="";
			foreach($parameter as $key=>$value){
				$rest_pars.='/'.$key.'/'.urlencode($value);
			}
			$rest_pars=substr($rest_pars,1);
		}

		if(in_array($callmethod,array('GET','POST')) && !empty($parameter)){ 
			$http_pars=http_build_query($parameter);
		}
	
		//susun uri
		$uri=$endpoint;
		if (!empty($operation)) {
			$uri.=substr($uri,-1)=='/'?$operation:'/'.$operation;
		}
		if (!empty($rest_pars)){ //tambah parameter untuk method REST, RESTFULL, dan RESTFULLPAR
			$uri.=substr($uri,-1)=='/'?$rest_pars:'/'.$rest_pars;
		}
		if (!empty($http_pars) && $callmethod=='GET'){ //tambah parameter untuk method GET
			$uri=substr($uri,-1)=='/'?substr($uri,0,-1):$uri;
			$uri.=strpos($uri,"?")===false?"?".$http_pars:"&".$http_pars;
		}
	
		//file_put_contents(TMPDIR.'apiconnector.txt',$uri);

		if(empty($uri)){
			$response=array('status'=>0,'code'=>20002,'message'=>'URI tidak terdefinisi (kosong)','data'=>'');
			$axml=array($rootkeytag=>$response);
		}
		else{
			$uri=substr($uri,-1)=='/'?substr($uri,0,-1):$uri;
			$ch = curl_init();
			// URL target koneksi
			curl_setopt($ch, CURLOPT_URL, $uri);            
			if($agent!='') curl_setopt($ch, CURLOPT_USERAGENT, $agent);
			// Output dengan header=true hanya untuk meta document (xml/json)
			curl_setopt($ch, CURLOPT_HEADER, FALSE);         
			if($accesskey!='') curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:text/plain;charset=utf-8","AccessKey:".$accesskey));
			// Mendapatkan tanggapan
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);  
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, FALSE);
			curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);

			// Menggunakan metode HTTP GET
			if(in_array($callmethod,array('GET','REST','RESTFULL','RESTFULLPAR')) ){
				curl_setopt($ch, CURLOPT_HTTPGET, TRUE);         
			}
		
			// Menggunakan metode HTTP POST 
			if($callmethod=='POST'){
				curl_setopt($ch, CURLOPT_POST, TRUE);       
				// Sisipkan parameter    
				curl_setopt($ch, CURLOPT_POSTFIELDS,$http_pars);  
			}

			// Buka koneksi dan dapatkan tanggapan
			$content=curl_exec($ch);     
			$errno=curl_errno($ch);
			$errmsg=curl_strerror($errno);                   

			// Periksa kesalahan
			if ($errno!=0){                            
				$response=array('status'=>0,'code'=>$errno,'message'=>$errmsg,'data'=>'');
				$axml=array($rootkeytag=>$response);
			}
			else{
				if(APP_ENC) $content=dec64data($content);
				if(substr($content,0,5)=='<?xml' || substr($content,0,5)=='<ows:'){
					$acontent=setXML2Array($content);
					if(!isset($acontent[$rootkeytag]['status'])){
						$response=array('status'=>1,'code'=>200,'message'=>'OK','data'=>$acontent);
						$axml=array($rootkeytag=>$response);
					}
					else $axml=$acontent;
				}
				elseif(substr($content,0,1)=='{' && substr($content,-1)=='}'){
					$acontent=json_decode($content,true);
					if(!isset($acontent[$rootkeytag]['status'])){
						$response=array('status'=>1,'code'=>200,'message'=>'OK','data'=>$acontent);
						$axml=array($rootkeytag=>$response);
					}
					else $axml=$acontent;
				}
				else{
					$acontent=unserialize($content);
					if(!$acontent) $acontent=$content;
					if(!isset($acontent[$rootkeytag]['status'])){
						$response=array('status'=>1,'code'=>200,'message'=>'OK','data'=>$acontent);
						$axml=array($rootkeytag=>$response);
					}
					else $axml=$acontent;
				}
			}		
			curl_close($ch);
		}
	}

	if(!empty($axml))
	switch ($outputformat){
	case "xml":
		try{
			$result=setArray2XML($rootkeytag,$axml[$rootkeytag]);
		}
		catch(exception $e){
			$response=array('status'=>0,'code'=>20004,'message'=>$e->getMessage(),'data'=>'');
			$axml=array($rootkeytag=>$response);
			$result=setArray2XML($rootkeytag,$axml[$rootkeytag]);
		}
		break;
	case "json":
		$result=json_encode($axml,JSON_PRETTY_PRINT | JSON_FORCE_OBJECT | JSON_PARTIAL_OUTPUT_ON_ERROR);
		break;
	case "array":
	default:
		$result=$axml;
	}

	return $result;
}


