<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi
Assisted by	  : Agung Basuki
ver: 1.99y
*/


function pagetracks($interface){
	global $urikeys,$puri,$interfaces,$validLogin;

	if(!$validLogin){
		header('Location: '.home_url().'masuk');
		return;
	}
	if(!is_supervisor() && !is_administrator() && !is_provider() && !is_publisher()) return;

	?>
	<p style='padding:0 4px;font-size:28px;font-weight:bold;'>Riwayat & Statistik</p>
	<?php

	historymenu();
	$currInstance="";
	if(is_provider() || is_publisher()) $currInstance=current_instance(current_instanceid());
	$interfaceuri=home_url().$interface."/";
	$hasFinished=false;
	if(isset($puri["action"]) && $puri["action"]=="list") $puri["action"]="";
	if(isset($puri["action"]) && !empty($puri["action"])){
		if($puri["action"]=="rincian"){
			$rs=getTrackByID($puri["id"]);
			if($rs){
			?>
			<div class="dialog">
			<div>ID Penyedia:</div>
			<div><input type="text" style="width:40em;" readonly="readonly" value="<?php echo $rs["instance"];?>"/></div>
			<div>ID Pengguna:</div>
			<div><input type="text" style="width:40em;" readonly="readonly" value="<?php echo $rs["userlog"];?>"/></div>
			<div>IP Pengguna:</div>
			<div><input type="text" style="width:40em;" readonly="readonly" value="<?php echo $rs["userip"];?>"/></div>
			<div>ID Proses:</div>
			<div><input type="text" style="width:40em;" readonly="readonly" value="<?php echo $rs["trackid"];?>"/></div>
			<div>Nama Proses:</div>
			<div><input type="text" style="width:40em;" readonly="readonly" value="<?php echo $rs["trackname"];?>"/></div>
			<div>Status:</div>
			<div><input type="text" style="width:40em;" readonly="readonly" value="<?php echo $rs["trackstatus"];?>"/></div>
			<div>Catatan:</div>
			<div><textarea style="width:40em;" rows="10" readonly="readonly" wrap="off" ><?php echo $rs["tracknote"];?></textarea></div>
			<div>Hasil:</div>
			<div><textarea style="width:40em;" rows="10" readonly="readonly" wrap="off" ><?php echo $rs["trackdata"];?></textarea></div>
			</div>
			<?php
			}
		}
		else{
			$filters=array();
			if($currInstance=="") 
			$filters["instance"]=array("label"=>"ID Penyedia","size"=>30);
			$filters["methodtype"]=array("label"=>"Jenis Operasi","size"=>30,"attrs"=>"autofocus=\"autofocus\"");
			$grid=new grid_interface;
			$grid->urikeys=$urikeys;
			$grid->puri=$puri;
			$grid->interfaces=$interfaces;
			$grid->baseurl=$interfaceuri."statistik/";
			$grid->setFilter($filters);
			$grid->rows=getTrackStatusAPIRows(($currInstance==""?$grid->query["instance"]:$currInstance),$grid->query["methodtype"]);
			$grid->pager=getPageList($grid->rows,$grid->pagenum,10,1);
			$grid->listpager=getPageList($grid->rows,$grid->pagenum,10,5);
			$grid->pdf["data"]="apitracks/instance=".($currInstance==""?$grid->query["instance"]:$currInstance)."&methodtype=".$grid->query["methodtype"];
			$rs=getTrackStatusAPI(($currInstance==""?$grid->query["instance"]:$currInstance),$grid->query["methodtype"]);;
			$grid->records=getTrackStatusAPI(($currInstance==""?$grid->query["instance"]:$currInstance),$grid->query["methodtype"],$grid->pager["perpage"],$grid->pager["startrow"]);
			$grid->colnames=array("instance"=>array("label"=>"ID PENYEDIA"),
								  "trackid"=>array("label"=>"JENIS OPERASI","patern"=>"(:trackid=='database'?'Data':(:trackid=='program'?'Program':(:trackid=='services'?'Proxy':'Sistem')));"),
								  "success"=>array("label"=>"TERLAKSANA","align"=>"right"),
								  "fail"=>array("label"=>"TERKENDALA","align"=>"right"));
			if($grid->display()==false){?>
			<div class="message"><b>Data belum ada</b></div>
			<?php
			}
			if(is_array($rs) && count($rs)>0){
				$cat=array();$categories=array();$data=array();$total=array('success'=>0,'fail'=>0);
				foreach($rs as $rows){
					if(!in_array($rows['instance'],$cat)){
						$cat[]=$rows['instance'];
						$categories[]="'".ucwords($rows['instance'])."'";
					}
					if(in_array($rows['instance'],$cat)){
						switch ($rows['trackid']){
						case 'program':$trackname='Program';break;
						case 'database':$trackname='Data';break;
						case 'services':$trackname='Proxy';break;
						default:$trackname='Sistem';
						}
						$data[$trackname]['Terlaksana'][$rows['instance']]=$rows['success'];
						$data[$trackname]['Terkendala'][$rows['instance']]=$rows['fail'];
						$total['success']+=$rows['success'];
						$total['fail']+=$rows['fail'];
					}
				}

				$i=0;$max=count($data);$dummseries='';
				foreach($data as $groupkey=>$colgroup){
					foreach($colgroup as $colkey=>$coldata){
						$dataseries=implode(',',$coldata);	
						$dummseries.="{";
						$dummseries.="data: [".$dataseries."],";
						$dummseries.="stack: ".$i.",";
						$dummseries.="name: 'Operasi ".ucwords($groupkey)." ".ucwords($colkey)."'";
						$dummseries.="},";
					}
					$i++;
				}
				//$dummseries=substr($dummseries,0,-1);
				$dummseries.="{";
				$dummseries.="type:'pie',";
				$dummseries.="name:'Total Operasi',";
				$dummseries.="center:[80,-120],";
				$dummseries.="size:150,";
				$dummseries.="softConnector: false,";
				$dummseries.="shadow:false,";
				$dummseries.="showInLegend:false,";
				$dummseries.="dataLabels:{style:{color:'#6D869F',fontWeight:'bold'},padding:0,y:0,x:0,distance:20,connectorPadding:3,color:'blue',formatter:function(){return this.point.name+': '+this.percentage.toFixed(2)+'%'},enabled:true},";
				$dummseries.="data: [{name:'Terlaksana',sliced: false,y:{$total['success']}},{name:'Terkendala',y:{$total['fail']}}]";
				$dummseries.="}";
				
			?>
			<hr/>
			<script runat="server" type="text/javascript" autoload="true">
			$(function () {
				var chart;
				$(document).ready(function() {
					chart = new Highcharts.Chart({
						chart: {
							renderTo: 'trackapichar',
							type: 'bar', 
							plotBorderWidth: 1,
							borderRadius: 5,
							margin: [250,50,50,150], /* Top, Right, Bottom, Left */
							shadow:true
						},
						title: {
							align:'center',
							style: {
									color: '#6D869F',
									fontWeight: 'bold'
							},
							text: 'Analisa Operasi'
						},
						subtitle: {
							align:'center',
							text: 'sumber: gsb.layanan.go.id'
						},
						xAxis: {
							categories: [<?php echo implode(',',$categories);?>],
							labels: {
								align:'right',
								style: {
									color: '#6D869F',
									fontWeight: 'bold'
								},
								rotation:315
							},
							title: {
								text: 'Penyedia Operasi',
								align: 'high',
								margin: 0,
								offset: 10,
								rotation:0
							}
						},
						yAxis: {
							min: 0,
							title: {
								text: 'Jumlah Operasi',
								align: 'middle'
							}
						},
						plotOptions: {
							bar: {
								dataLabels: {
									align: 'center',
									enabled: false
								},
								stacking:'normal',
								borderRadius: 0,
								/*borderColor: '#333333',*/
								borderWidth: 1,
								colorByPoint: false,
								shadow: false
							}
						},
						legend: {
							layout: 'vertical',
							align: 'right',
							verticalAlign: 'top',
							x:-10,
							y:50,
							floating: true,
							borderWidth: 2,
							backgroundColor: '#eeeeee',
							itemStyle: {
								paddingBottom: '10px'
							},
							shadow: false
						},
						credits: {
							enabled: false
						},
						series: [<?php echo $dummseries;?>]
					});
				});
				
			});
			</script>
			<div id="trackapichar" style="min-width: 600px; min-height: 500px; margin: 20px;"></div>
			<?php
			}
		}
	}
	else{
		$filters=array();
		if($currInstance=="") 
		$filters["instance"]=array("label"=>"Inst.Pengguna","size"=>30);
		$filters["userlog"]=array("label"=>"ID Pengguna","size"=>30,"attrs"=>"autofocus=\"autofocus\"");
		$filters["userip"]=array("label"=>"IP Pengguna","size"=>15);
		$filters["start_date"]=array("label"=>"Tgl.Proses Awal","size"=>15);
		$filters["end_date"]=array("label"=>"Tgl.Proses Akhir","size"=>15);
	
		$grid=new grid_interface;
		$grid->urikeys=$urikeys;
		$grid->puri=$puri;
		$grid->interfaces=$interfaces;
		$grid->baseurl=$interfaceuri;
		$grid->setFilter($filters);
		$grid->rows=getTrackRows($grid->query["userlog"],$currInstance==""?$grid->query["instance"]:$currInstance,$grid->query["start_date"],$grid->query["end_date"]);
		$grid->pager=getPageList($grid->rows,$grid->pagenum,10,1);
		$grid->listpager=getPageList($grid->rows,$grid->pagenum,10,5);
		$grid->pdf["data"]="tracks/instance=".($currInstance==""?$grid->query["instance"]:$currInstance)."&userlog=".$grid->query["userlog"]."&startdate=".$grid->query["start_date"]."&enddate=".$grid->query["end_date"];
		$grid->records=getTracks($grid->query["userlog"],$currInstance==""?$grid->query["instance"]:$currInstance,$grid->query["start_date"],$grid->query["end_date"],$grid->pager["perpage"],$grid->pager["startrow"]);
		$grid->colnames=array(
								"instance"=>array("label"=>"INST.PENGGUNA"),
							  "userlog"=>array("label"=>"ID PENGGUNA"),
							  "userip"=>array("label"=>"IP PENGGUNA"),
							  "trackid"=>array("label"=>"ID PROSES"),
							  "trackname"=>array("label"=>"PROSES"),
							  "trackstatus"=>array("label"=>"STATUS"),
							  "registered"=>array("label"=>"TGL.PROSES")
		);
		$grid->operationstitle="RINCIAN";
		$grid->operations=array(array("url"=>$interfaceuri."rincian/:id","title"=>"Rincian Penggunaan","icon"=>"ico/application_view_detail.png"));
		if($grid->display()==false){
		?>
		<div class="message"><b>Data belum ada</b></div>
		<?php
		}
	
	}
	echo "<br/>";
}


