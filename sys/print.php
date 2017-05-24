<?php  if ( !defined('BASEPATH') ) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
*/

require_once SYSPATH.'config.php';
require_once SYSPATH.'common.php';
require_once SYSPATH.'models.php';
require_once LIBDIR.'fpdf/fpdf.php';

class PDF extends FPDF{
	//Page header
	public $title;
	public $printedHeader=true;
	function PDF($title){
		$this->FPDF();
		$this->title=$title;
		$this->AliasNbPages();
		$this->AddPage();
		$this->SetFont('Arial','B',8);
		$this->SetFillColor(240,240,240);
	}

	function Header(){
		//Logo
		$this->Image(IMGDIR.'smallmantra.png',10,12);
		$logo=getAppImage();
		if(!empty($logo)) $this->Image(FILEDIR.$logo,190,12,0,8);

		$this->SetFont('Arial','',10);
		$this->Cell(0,10,APP_TITLE,'',0,'C');
		$this->Ln(5);
		$instance=getAppInstance();
		if(!empty($instance)) $this->Cell(0,10,$instance,'',0,'C');
		$this->Ln(15);
		$this->SetFont('Arial','B',15);
		//Move to the right
		//$this->Cell(80);
		//Title
		$this->Cell(0,10,$this->title,'',0,'C');
		//Line break
		$this->Ln(10);
	}
	 
	function is_NewPage(){
		$h=8;
		if($this->y+$h > $this->PageBreakTrigger){
			$this->printedHeader=true;
		}
		$prn=$this->printedHeader;
		$this->printedHeader=false;
		return $prn;
	}

	//Page footer
	function Footer(){
		//Position at 1.5 cm from bottom
		$this->SetY(-15);
		//Arial italic 8
		$this->SetFont('Arial','I',8);
		//Page number
		$this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb}',0,0,'R');
	}
}

$reqpar=array();
$reqdata=str_replace(URL_BASEPATH."print/", "", $_SERVER['REQUEST_URI']);
$onreq=explode("/",$reqdata,2);
$datasource=$onreq[0];

if($datasource=="pubresources"){
	if(!isset($onreq[1])){
		$reqpar=array("instance"=>"","servicename"=>"","methodname"=>"");
	}
	else{
		parse_str($onreq[1],$reqpar);
	}
	$data=getPubResources($reqpar);
	if(count($data)>0){
		printPubResources("Tabel Akses Operasi Terbuka",$data);
	}
}

if(logOpen()){
	$currLogname=current_logname();
	switch($datasource){
	case "orderresources":
		if(!isset($onreq[1])){
			$reqpar=array("logname"=>"","instance"=>"","servicename"=>"","methodname"=>"");
		}
		else{
			parse_str($onreq[1],$reqpar);
		}
		$reqpar["logname"]=$currLogname;
		$data=getOrderResources($reqpar);

		//file_put_contents(TMPDIR.'print.txt',rawurldecode(http_build_query($data)));

		if(!empty($data)) printResources("Tabel Pengguna Akses Operasi",$data);
		break;

	case "resources":
		if(!isset($onreq[1])){
			$reqpar=array("instance"=>"","servicename"=>"","methodname"=>"");
		}
		else{
			parse_str($onreq[1],$reqpar);
		}
		$data=getResources($reqpar);
		if(!empty($data)) printResources("Tabel Akses Operasi",$data);
		break;
	case "instances":
		if(!isset($onreq[1])){
			$reqpar=array("instance"=>"");
		}
		else{
			parse_str($onreq[1],$reqpar);
		}
		$data=getInstances($reqpar["instance"]);
		if(!empty($data)) printInstances("Tabel Instansi Pengelola Operasi",$data);
		break;
	case "users":
		if(!isset($onreq[1])){
			$reqpar=array("logname"=>"");
		}
		else{
			parse_str($onreq[1],$reqpar);
		}
		$data=getUsers($reqpar["logname"]);
		if(!empty($data)) printUsers("Tabel Pelaksana Operasi",$data);
		break;
	case "services":
		if(!isset($onreq[1])){
			$reqpar=array("instance"=>"","servicetype"=>"","servicename"=>"");
		}
		else{
			parse_str($onreq[1],$reqpar);
		}
		$data=getServices($reqpar["instance"],$reqpar["servicetype"],$reqpar["servicename"]);
		if(!empty($data)) printServices("Tabel Direktori Operasi",$data);
		break;
	case "methods":
		if(!isset($onreq[1])){
			$reqpar=array("instance"=>"","servicename"=>"","methodname"=>"");
		}
		else{
			parse_str($onreq[1],$reqpar);
		}
		$data=getMethods($reqpar["instance"],$reqpar["servicename"],$reqpar["methodname"]);
		if(!empty($data)) printMethods("Tabel Fungsi Operasi",$data);
		break;
	case "orders":
		if(!isset($onreq[1])){
			$reqpar=array("userlog"=>"","accesskey"=>"","instance"=>"");
		}
		else{
			parse_str($onreq[1],$reqpar);
		}
		$data=getOrders($reqpar["userlog"],$reqpar["accesskey"],$reqpar["instance"]);
		if(!empty($data)) printOrders("Tabel Permintaan Akses Operasi",$data);
		break;
	case "tracks":
		if(!isset($onreq[1])){
			$reqpar=array("instance"=>"","userlog"=>"","startdate"=>"","enddate"=>"");
		}
		else{
			parse_str($onreq[1],$reqpar);
		}
		$data=getTracks($reqpar["userlog"],$reqpar["instance"],$reqpar["startdate"],$reqpar["enddate"]);
		if(!empty($data)) printTracks("Riwayat Proses",$data);
		break;
	case "apitracks":
		if(!isset($onreq[1])){
			$reqpar=array("instance"=>"","methodtype"=>"");
		}
		else{
			parse_str($onreq[1],$reqpar);
		}
		$data=getTrackStatusAPI($reqpar["instance"],$reqpar["methodtype"]);
		if(!empty($data)) printAPITracks("Analisa Operasi",$data);
		break;
	}
}	

function printPubResources($title,$data){
	$pdf=new PDF($title);
	$header=array("NO.","INST.PENYEDIA","DIREKTORI","FUNGSI OPERASI","TGL.DAFTAR");
	$wcol=array(10,48,42,60,30);
	$wdat=array(4,30,25,42,19);
	$no=0;

	foreach($data as $rows){
		if($pdf->is_NewPage()){
			foreach($header as $key=>$value)
				$pdf->Cell($wcol[$key],8,$value,1,0,'C',true);
			$pdf->Ln();
		}
		$i=0;
		$no++;
		$pdf->Cell($wcol[0],8,substr($no,0,$wdat[0]),1,0,'R',false);
		$pdf->Cell($wcol[1],8,substr($rows["instance"],0,$wdat[1]),1,0,'L',false);
		$pdf->Cell($wcol[2],8,substr($rows["servicename"],0,$wdat[2]),1,0,'L',false);
		$pdf->Cell($wcol[3],8,substr($rows["methodname"],0,$wdat[3]),1,0,'L',false);
		$pdf->Cell($wcol[4],8,substr($rows["registered"],0,$wdat[4]),1,0,'L',false);
		$pdf->Ln();
	}

	$pdf->Output('akses-operasi-terbuka.pdf','D');
}


function printResources($title,$data){
	//Instanciation of inherited class
	$pdf=new PDF($title);
	$header=array("NO.","INST.PENYEDIA","DIREKTORI","FUNGSI OPERASI","JENIS","TGL.DAFTAR");
	$wcol=array(10,48,42,35,25,30);
	$wdat=array(4,30,25,20,10,19);
	$no=0;
	foreach($data as $rows){
		if($pdf->is_NewPage()){
			foreach($header as $key=>$value)
				$pdf->Cell($wcol[$key],8,$value,1,0,'C',true);
			$pdf->Ln();
		}
		$i=0;
		$no++;
		$pdf->Cell($wcol[0],8,substr($no,0,$wdat[0]),1,0,'R',false);
		$pdf->Cell($wcol[1],8,substr($rows["instance"],0,$wdat[1]),1,0,'L',false);
		$pdf->Cell($wcol[2],8,substr($rows["servicename"],0,$wdat[2]),1,0,'L',false);
		$pdf->Cell($wcol[3],8,substr($rows["methodname"],0,$wdat[3]),1,0,'L',false);
		$pdf->Cell($wcol[4],8,substr($rows["methodtype"],0,$wdat[4]),1,0,'L',false);
		$pdf->Cell($wcol[5],8,substr($rows["registered"],0,$wdat[5]),1,0,'L',false);
		$pdf->Ln();
	}

	$pdf->Output('akses-operasi.pdf','D');
}

function printInstances($title,$data){
	//Instanciation of inherited class
	$pdf=new PDF($title);
	$header=array("NO.","ID INSTANSI","NAMA INSTANSI","E-MAIL","TGL.DAFTAR");
	$wcol=array(10,48,50,50,30);
	$wdat=array(4,30,26,31,19);
	$no=0;
	foreach($data as $rows){
		if($pdf->is_NewPage()){
			foreach($header as $key=>$value)
				$pdf->Cell($wcol[$key],8,$value,1,0,'C',true);
			$pdf->Ln();
		}
		$no++;
		$pdf->Cell($wcol[0],8,substr($no,0,$wdat[0]),1,0,'R',false);
		$pdf->Cell($wcol[1],8,substr(strtolower($rows["instance"]),0,$wdat[1]),1,0,'L',false);
		$pdf->Cell($wcol[2],8,substr($rows["organization"],0,$wdat[2]),1,0,'L',false);
		$pdf->Cell($wcol[3],8,substr(strtolower($rows["email"]),0,$wdat[3]),1,0,'L',false);
		$pdf->Cell($wcol[4],8,substr($rows["registered"],0,$wdat[4]),1,0,'L',false);
		$pdf->Ln();
	}
	$pdf->Output('instansi-pengelola-operasi.pdf','D');
}

function printUsers($title,$data){
	//Instanciation of inherited class
	$pdf=new PDF($title);
	$header=array("NO.","NAMA LENGKAP","ID PELAKSANA","ID INSTANSI","PERAN","STATUS","TGL.DAFTAR");
	$wcol=array(10,30,30,48,25,15,30);
	$wdat=array(4,14,16,30,15,5,19);
	$no=0;
	foreach($data as $rows){
		if($pdf->is_NewPage()){
			foreach($header as $key=>$value)
				$pdf->Cell($wcol[$key],8,$value,1,0,'C',true);
			$pdf->Ln();
		}
		$no++;
		$pdf->Cell($wcol[0],8,substr($no,0,$wdat[0]),1,0,'R',false);
		$pdf->Cell($wcol[1],8,substr($rows["fullname"],0,$wdat[1]),1,0,'L',false);
		$pdf->Cell($wcol[2],8,substr(strtolower($rows["logname"]),0,$wdat[2]),1,0,'L',false);
		$pdf->Cell($wcol[3],8,substr(strtolower($rows["instance"]),0,$wdat[3]),1,0,'L',false);
		//$pdf->Cell($wcol[4],8,substr(strtolower($rows["role"]==0?'ADMINISTRATOR':($rows["role"]==1?'PROVIDER':'REQUESTER')),0,$wdat[4]),1,0,'L',false);
		$pdf->Cell($wcol[4],8,substr(strtolower($rows["role"]),0,$wdat[4]),1,0,'L',false);
		//$pdf->Cell($wcol[5],8,substr(strtolower($rows["activity"]==1?'AKTIF':'PASIF'),0,$wdat[5]),1,0,'L',false);
		$pdf->Cell($wcol[5],8,substr(strtolower($rows["activity"]),0,$wdat[5]),1,0,'L',false);
		$pdf->Cell($wcol[6],8,substr($rows["registered"],0,$wdat[6]),1,0,'L',false);
		$pdf->Ln();
	}

	$pdf->Output('pelaksana-operasi.pdf','D');
}

function printServices($title,$data){
	//Instanciation of inherited class
	$pdf=new PDF($title);
	$header=array("NO.","ID PENYEDIA","DIREKTORI","KETERANGAN","TGL.DAFTAR");
	$wcol=array(10,48,50,50,30);
	$wdat=array(4,30,32,32,19);
	$no=0;
	foreach($data as $rows){
		if($pdf->is_NewPage()){
			foreach($header as $key=>$value)
				$pdf->Cell($wcol[$key],8,$value,1,0,'C',true);
			$pdf->Ln();
		}
		$no++;
		$pdf->Cell($wcol[0],8,substr($no,0,$wdat[0]),1,0,'R',false);
		$pdf->Cell($wcol[1],8,substr(strtolower($rows["instance"]),0,$wdat[1]),1,0,'L',false);
		$pdf->Cell($wcol[2],8,substr(strtolower($rows["servicename"]),0,$wdat[2]),1,0,'L',false);
		$pdf->Cell($wcol[3],8,substr(strtolower($rows["descript"]),0,$wdat[3]),1,0,'L',false);
		$pdf->Cell($wcol[4],8,substr($rows["registered"],0,$wdat[4]),1,0,'L',false);
		$pdf->Ln();
	}

	$pdf->Output('direktori-operasi.pdf','D');
}

function printMethods($title,$data){
	//Instanciation of inherited class
	$pdf=new PDF($title);
	$header=array("NO.","ID PENYEDIA","DIREKTORI","FUNGSI OPERASI","JENIS","KET","TGL.DAFTAR");
	$wcol=array(10,48,35,35,20,14,30);
	$wdat=array(4,30,20,20,10,7,19);
	$no=0;
	foreach($data as $rows){
		if($pdf->is_NewPage()){
			foreach($header as $key=>$value)
				$pdf->Cell($wcol[$key],8,$value,1,0,'C',true);
			$pdf->Ln();
		}
		$no++;
		$pdf->Cell($wcol[0],8,substr($no,0,$wdat[0]),1,0,'R',false);
		$pdf->Cell($wcol[1],8,substr(strtolower($rows["instance"]),0,$wdat[1]),1,0,'L',false);
		$pdf->Cell($wcol[2],8,substr(strtolower($rows["servicename"]),0,$wdat[2]),1,0,'L',false);
		$pdf->Cell($wcol[3],8,substr(strtolower($rows["methodname"]),0,$wdat[3]),1,0,'L',false);
		$pdf->Cell($wcol[4],8,substr(strtolower($rows["methodtype"]),0,$wdat[4]),1,0,'L',false);
		$pdf->Cell($wcol[5],8,substr($rows["descript"],0,$wdat[5]),1,0,'L',false);
		$pdf->Cell($wcol[6],8,substr($rows["registered"],0,$wdat[6]),1,0,'L',false);
		$pdf->Ln();
	}

	$pdf->Output('fungsi-operasi.pdf','D');
}

function printOrders($title,$data){
	//Instanciation of inherited class
	$pdf=new PDF($title);
	$header=array("NO.","ID PENGGUNA","INST.PENGGUNA","AKSES","INST.PENYEDIA","DIREKTORI","FUNGSI OPERASI","STATUS","TGL.DAFTAR");
	$wcol=array(10,28,24,15,24,28,28,8,28);
	$wdat=array(4,14,10,8,10,14,14,3,16);
	$no=0;
	foreach($data as $rows){
		if($pdf->is_NewPage()){
			foreach($header as $key=>$value)
				$pdf->Cell($wcol[$key],8,$value,1,0,'C',true);
			$pdf->Ln();
		}
		$no++;
		$pdf->Cell($wcol[0],8,substr($no,0,$wdat[0]),1,0,'R',false);
		$pdf->Cell($wcol[1],8,substr(strtolower($rows["userlog"]),0,$wdat[1]),1,0,'L',false);
		$pdf->Cell($wcol[2],8,substr(strtolower($rows["userinstance"]),0,$wdat[2]),1,0,'L',false);
		$pdf->Cell($wcol[3],8,substr(strtolower($rows["accesskey"]),0,$wdat[3]),1,0,'L',false);
		$pdf->Cell($wcol[4],8,substr(strtolower($rows["instance"]),0,$wdat[4]),1,0,'L',false);
		$pdf->Cell($wcol[5],8,substr(strtolower($rows["servicename"]),0,$wdat[5]),1,0,'L',false);
		$pdf->Cell($wcol[6],8,substr(strtolower($rows["methodname"]),0,$wdat[6]),1,0,'L',false);
		$pdf->Cell($wcol[7],8,substr(strtolower($rows["orderstatus"]),0,$wdat[7]),1,0,'L',false);
		$pdf->Cell($wcol[8],8,substr($rows["registered"],0,$wdat[8]),1,0,'L',false);
		$pdf->Ln();
	}

	$pdf->Output('permintaan-akses.pdf','D');
}

function printTracks($title,$data){
	//Instanciation of inherited class
	$pdf=new PDF($title);
	$header=array("NO.","ID PENYEDIA","ID PENGGUNA","ID PROSES","PROSES","STATUS","TGL.PROSES");
	$wcol=array(10,48,32,35,20,17,30);
	$wdat=array(4,30,17,20,10,10,19);
	$no=0;
	foreach($data as $rows){
		if($pdf->is_NewPage()){
			foreach($header as $key=>$value)
				$pdf->Cell($wcol[$key],8,$value,1,0,'C',true);
			$pdf->Ln();
		}
		$no++;
		$pdf->Cell($wcol[0],8,substr($no,0,$wdat[0]),1,0,'R',false);
		$pdf->Cell($wcol[1],8,substr($rows["instance"],0,$wdat[1]),1,0,'L',false);
		$pdf->Cell($wcol[2],8,substr($rows["userlog"],0,$wdat[2]),1,0,'L',false);
		$pdf->Cell($wcol[3],8,substr($rows["trackid"],0,$wdat[3]),1,0,'L',false);
		$pdf->Cell($wcol[4],8,substr($rows["trackname"],0,$wdat[4]),1,0,'L',false);
		$pdf->Cell($wcol[5],8,substr($rows["trackstatus"],0,$wdat[5]),1,0,'L',false);
		$pdf->Cell($wcol[6],8,substr($rows["registered"],0,$wdat[6]),1,0,'L',false);
		$pdf->Ln();
	}

	$pdf->Output('riwayat-proses.pdf','D');
}

function printAPITracks($title,$data){
	//Instanciation of inherited class
	$pdf=new PDF($title);
	$header=array("NO.","ID PENYEDIA","JENIS OPERASI","SUKSES","GAGAL");
	$wcol=array(10,78,58,20,20);
	$wdat=array(4,60,40,10,10);
	$no=0;
	foreach($data as $rows){
		if($pdf->is_NewPage()){
			foreach($header as $key=>$value)
				$pdf->Cell($wcol[$key],8,$value,1,0,'C',true);
			$pdf->Ln();
		}
		$no++;
		$pdf->Cell($wcol[0],8,substr($no,0,$wdat[0]),1,0,'R',false);
		$pdf->Cell($wcol[1],8,substr($rows["instance"],0,$wdat[1]),1,0,'L',false);
		$pdf->Cell($wcol[2],8,substr($rows["trackid"],0,$wdat[2]),1,0,'L',false);
		$pdf->Cell($wcol[3],8,substr($rows["success"],0,$wdat[3]),1,0,'R',false);
		$pdf->Cell($wcol[4],8,substr($rows["fail"],0,$wdat[4]),1,0,'R',false);
		$pdf->Ln();
	}

	$pdf->Output('analisa-operasi.pdf','D');
}


