<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
Programmed by : Didi Sukyadi, MTI.
*/

class option_item {
	public $name;
	public $label;
	public $attr;
	public $content;
	public $options;
	public $info;

	public function __construct(){
		$this->name="";
		$this->label="";
		$this->attr="";
		$this->content="";
		$this->options=array();
		$this->msginfo="";
	}

	public function __destruct(){
		$this->name="";
		$this->label="";
		$this->attr="";
		$this->content="";
		$this->options=array();
		$this->msginfo="";
	}

	public function add(){
		if(!isset($this)) return;
		if(empty($this->name)) return;
		if(array_key_exists($this->name,$this->options)) return;
		$this->options[$this->name]=array("label"=>$this->label,"attr"=>$this->attr,"content"=>$this->content,"msginfo"=>$this->msginfo);
		$this->name="";
		$this->label="";
		$this->attr="";
		$this->content="";
		$this->msginfo="";
	}

	public function items(){
		if(!isset($this)) return array();
		return $this->options;
	}
}

class control_item {
	public $name;
	public $type;
	public $desc;
	public $attrs;
	public $values;
	public $option;
	public $category;
	public $info;
	public $required;
	public $unsaved;
	public $controls;

	public function __construct(){
		$this->reseted();
		$this->controls=array();
	}

	public function __destruct(){
		$this->reseted();
		$this->controls=array();
	}

	public function reseted(){
		$this->name="";
		$this->type="";
		$this->desc="";
		$this->attrs="";
		$this->values="";
		$this->option="";
		$this->category="";
		$this->info="";
		$this->required=false;
		$this->unsaved=false;
		return;
	}

	public function add(){
		if(!isset($this)) return;
		if(empty($this->name)) return;
		if(array_key_exists($this->name,$this->controls)) return;
		$this->controls[$this->name]=array("desc"=>$this->desc,"type"=>$this->type,"attrs"=>$this->attrs,"values"=>$this->values,"category"=>$this->category,"option"=>$this->option,"required"=>$this->required,"unsaved"=>$this->unsaved,"info"=>$this->info);
		$this->reseted();
		return;
	}

	public function items(){
		if(!isset($this)) return array();
		return array("control_item"=>$this->controls);
	}
}

class control_interface {
	public $controlname="";
	public $type="text";
	public $value="";
	public $options=array();
	public $attribs="";
	public $info="";
	public $varname="f_reg";

	public function __construct($field,$type="text"){
		$this->controlname=$field;
		$this->type=$type;
	}
	
	public function __destruct(){
	}
	
	public function build(){
		if(!isset($this)) return;
		$field=$this->controlname;
		if(empty($field)) return;
		$type=$this->type;
		$value=$this->value;
		$options=is_array($this->options)?$this->options:array();
		$attribs=$this->attribs;
		$var=$this->varname;
		$var=empty($var)?"f_reg":$var;
		$field=strtolower($field);
		$type=strtolower($type);
		$info=$this->info;
	?>
	<?php if($type=="text"){?>
		<input type="<?php echo $type;?>" name="<?php echo "{$var}[{$field}]";?>" value="<?php echo $value;?>" <?php echo $attribs;?> />
		<?php echo $info;?>
	<?php }?>

	<?php if($type=="hidden"){?>
		<input type="<?php echo $type;?>" name="<?php echo "{$var}[{$field}]";?>" value="<?php echo $value;?>"  />
	<?php }?>

	<?php if($type=="password"){?>
		<input type="<?php echo $type;?>" name="<?php echo "{$var}[{$field}]";?>" value="<?php echo $value;?>" <?php echo $attribs;?> />
		<?php echo $info;?>
	<?php }?>
	
	<?php if($type=="radio"){?>
	<?php	foreach($options as $name=>$opt){ $checked=($name==$value)?"checked=\"checked\"":"";?>
				<input type="<?php echo $type;?>" name="<?php echo "{$var}[{$field}]";?>" value="<?php echo $name;?>" <?php echo $attribs." ".$checked;?>  />&nbsp;<?php echo $opt["content"];?>&nbsp;<?php echo $opt["msginfo"];?><br/>
	<?php	}?>
	<?php }?>
	
	<?php if($type=="checkbox"){?>
	<?php	if(count($options)>1){ ?>
	<?php		foreach($options as $option){ $checked=is_array($value)?in_array($option,$value)?"checked=\"checked\"":"":"";?>
					<input type="<?php echo $type;?>" name="<?php echo "{$var}[{$field}][]";?>" value="<?php echo $option;?>" <?php echo $attribs." ".$checked;?> />&nbsp;<?php echo ucwords($option);?>&nbsp;<?php echo $info;?><br/>
	<?php		}?>
	<?php	}else{?>
	<?php		foreach($options as $option){ $checked=$option==$value?" checked=\"checked\"":"";?>
					<input type="<?php echo $type;?>" name="<?php echo "{$var}[{$field}]";?>" value="<?php echo $option;?>" <?php echo $attribs.$checked;?> />&nbsp;<?php echo ucwords($option);?>&nbsp;<?php echo $info;?><br/>
	<?php		}?>
	<?php	}?>
	<?php }?>

	<?php if($type=="textarea"){?>
		<textarea name="<?php echo "{$var}[{$field}]";?>" <?php echo $attribs;?> ><?php echo $value;?></textarea>
		<?php echo $info;?>
	<?php }?>

	<?php if($type=="select"){?>
		<select name="<?php echo "{$var}[{$field}]";?>" <?php echo $attribs;?>>
	<?php	foreach($options as $name=>$opt){ $selected=$name==$value?"selected=\"selected\"":"";?>
			<option label="<?php echo $opt["label"];?>" value="<?php echo $name;?>" <?php echo $opt["attr"]." ".$selected;?>><?php echo $opt["content"];?></option>			
	<?php	}?>
		</select>
		<?php echo $info;?>
	<?php }?>

	<?php if(in_array($type,array("submit","reset","button","image"))){?>
		<input type="<?php echo $type;?>" name="<?php echo "{$var}[{$type}][{$field}]";?>" value="<?php echo ucwords($field);?>" <?php echo $attribs;?>/>
	<?php }?>

	<?php if($type=="file"){?>
	<?php	$filecapacity=getcapacity();?>
			<input type="<?php echo $type;?>" name="<?php echo "{$field}_file";?>" <?php echo $attribs;?> />&nbsp;&nbsp;
			<input type="submit" name="<?php echo "{$var}[upload]";?>" value="Upload"/>
			(Max.&nbsp;<?php echo byte2size($filecapacity,"M")?>&nbsp;MB)
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $filecapacity;?>" /><br/>
			<input type="text" name="<?php echo "{$var}[{$field}]";?>" value="<?php echo $value;?>" readonly="readonly"/>
			<?php echo $info;?>
	<?php }?>

	<?php
	}
}

class form_interface {
	public $cascade=true;
	public $buttons=array("Submit"=>"submit","Reset"=>"reset");
	public $formname="f_reg";
	public $action="";
	public $uncompleted=false;
	public $updated=false;
	public $saved=false;
	public $duplicated=false;
	public $requiredfields=array();
	public $requirednames=array();
	public $method="post";
	public $data=array();
	public $controls=array();
	public $uploadfile=array();

	public function __construct($controls=array(),$formname="f_reg"){
		if(empty($controls)) return;
		$this->controls=$controls["control_item"];

		if(!empty($formname)) $this->formname=$formname;
		$this->data=$_POST[$this->formname];
		$this->uncompleted=false;
		$this->updated=false;
		$this->saved=false;
		$this->duplicated=false;
		$this->requiredfields=array();
		$this->requirednames=array();

		foreach($this->controls as $field=>$attr){
			$field=strtolower($field);
			$desc=empty($attr["desc"])?$field:$attr["desc"];
			if($attr["required"]==true){
				$this->requiredfields[]=$field;
				$this->requirednames[]=$desc;
			}
		}
	}
	
	public function __destruct(){
	}

	public function do_upload($data){
		$result=false;
		$message="";
		$uploadfile = "";

		if(!empty($data)){
			$uploadfile_name=$data["name"];
			$uploadfile_type=$data["type"];
			$uploadfile_tmp_name=$data["tmp_name"];
			$uploadfile_size=$data["size"];
			$uploadfile_error=$data["error"];
			switch($uploadfile_error){
			case UPLOAD_ERR_OK:
				$uploaddir = "files/";
				$uploadfile = $uploaddir.$uploadfile_name;
				$result=move_uploaded_file($uploadfile_tmp_name, WP_CONTENT_DIR."/".$uploadfile);
				$message=$result?"":"Invalid upload file {$uploadfile_name}.";
				break;
			case UPLOAD_ERR_INI_SIZE:
				$message="The uploaded file exceeds the upload_max_filesize (".reg_byte2size(reg_getcapacity(),"M")."MB).";
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$message="The uploaded file exceeds the MAX_FILE_SIZE (".reg_byte2size($uploadfile_size,"M")."MB).";
				break;
			case UPLOAD_ERR_PARTIAL:
				$message="The uploaded file was only partially uploaded.";
				break;
			case UPLOAD_ERR_NO_FILE:
				$message="No file was uploaded.";
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$message="Missing a temporary folder.";
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$message="Failed to write file to disk.";
				break;
			case UPLOAD_ERR_EXTENSION:
				$message="File upload stopped by extension.";
				break;
			default:
				$message="Upload {$uploadfile_name} has error.";
			}
			if(!$result)
				reg_messagebox($result,$message);
		}
		return $uploadfile;
	}

	public function getrequiredfields(){
		return $this->requiredfields;
	}

	public function getrequirednames(){
		return $this->requirednames;
	}

	public function upload(){
		if(!isset($this)) return;
		if(isset($this->data["upload"])){
			$files=$_FILES;
			foreach($files as $filekey=>$filedesc){
				$fieldkey=str_replace("_file","",$filekey);
				$this->uploadfile[$fieldkey]=$this->do_upload($filedesc);
			}
		}
		return $this->uploadfile;
	}

	private function verified($data){}

	public function update($keys="",$table=""){}

	public function build($msg,$table=""){}
}

class grid_interface {
	public $rows;
	public $pagenum;
	public $pager;
	public $listpager;
	public $records;
	public $colnames;
	public $baseurl;
	public $operationstitle;
	public $operations;
	public $query;
	public $formstyle;
	public $pdf;
	public $request;
	public $urikeys;
	public $puri;
	public $interfaces;
	private $filter;

	public function __construct(){
		$this->init();
	}

	public function __destruct(){
		$this->init();
	}

	public function init(){
	
		$this->rows=0;
		$this->pagenum=1;
		$this->baseurl="";
		$this->pager=array();
		$this->listpager=array();
		$this->records=array();
		$this->colnames=array();
		$this->operationstitle="PROSES";
		$this->operations=array();
		$this->filter=array();
		$this->query=array();
		$this->pdf=array("data"=>"","icon"=>"ico/page_white_acrobat.png");
		$this->formstyle="program";
		$this->request=array();
		$this->urikeys="";
		$this->puri="";
		$this->interfaces="";


		if(isset($_POST["buttonpage"]))
			$this->pagenum=$_POST["pagenum"];
		elseif(isset($_POST["firstpage"]))
			$this->pagenum=is_numeric($_POST["firstpage"])?$_POST["firstpage"]:$_POST["page"]["first"];
		elseif(isset($_POST["prevpage"]))
			$this->pagenum=is_numeric($_POST["prevpage"])?$_POST["prevpage"]:$_POST["page"]["prev"];
		elseif(isset($_POST["nextpage"]))
			$this->pagenum=is_numeric($_POST["nextpage"])?$_POST["nextpage"]:$_POST["page"]["next"];
		elseif(isset($_POST["lastpage"]))
			$this->pagenum=is_numeric($_POST["lastpage"])?$_POST["lastpage"]:$_POST["page"]["last"];
		elseif(isset($_POST["pagenum"]))
			$this->pagenum=$_POST["pagenum"];
		elseif(isset($puri["id"]))
			$this->pagenum=$puri["id"]+0;
		else
			$this->pagenum=1;

		if(session_id()){
			if(isset($_SESSION["request"]) && !empty($_SESSION["request"])){
				parse_str($_SESSION["request"],$filters);
				$this->request=$filters;
			}
		}

		if(isset($_POST['filter']['data'])){
			$filters=$_POST['filter']['data'];
			/*if(isset($_POST['start_date'])){
				$filters["start_date"]=$_POST['start_date'];
			}
			if(isset($_POST['end_date'])){
				$filters["end_date"]=$_POST['end_date'];
			}*/
			$reqlist=http_build_query($filters);
			if(session_id()) $_SESSION["request"]= $reqlist;
			$this->request=$filters;
		}

		return;
	}

	public function setFilter($struct=array()){
		if(empty($struct)) return;
		$request=$this->request;
		$this->filter=$struct;
		foreach($this->filter as $field=>$attr){
			$val=isset($request[$field])?$request[$field]:"";
			$this->query[$field]=$val;
		}
	}
	
	public function display(){
		$urikeys=$this->urikeys;
		$puri=$this->puri;
		$interfaces=$this->interfaces;
	
		if(!isset($this)) return;

		$request=$this->request;

		$addrpage=isset($puri['page'])?$puri['page']:"";
		
		?>
		<div class="datalist">

		<?php
		if(!empty($this->filter)){
		?>
			<div class="queryform">
				<form name="filter" action="<?php echo $this->baseurl;?>" method="post">
					<input type="hidden" name="filter[data][page]" value="<?php echo $addrpage;?>"/>
					<?php foreach($this->filter as $field=>$attr){ ?>
					<?php $val=isset($request[$field])?$request[$field]:"";$val=htmlspecialchars($val);?>
					<div class="<?php echo $this->formstyle=="program"?"inlineform":"";?>">
						<label><?php echo $attr["label"];?>:</label><br/>
							<?php 
							if(strpos($field,'date')) { 
								$myCalendar = new tc_calendar("filter[data][".$field."]", true, false);
							  	//if(strpos($field,'date')) { $myCalendar = new tc_calendar($field, true, false);
								$myCalendar->setIcon("lib/calendar/images/iconCalendar.gif");
								$myCalendar->setPath("lib/calendar/");
								if($val!="" && !strstr($val, "0000")){
									$myCalendar->setDate(date('d', strtotime($val)), date('m', strtotime($val)), date('Y', strtotime($val)));
								}
								$myCalendar->setYearInterval(2000, 2015);
								$myCalendar->setDateFormat('j F Y'); 
								$myCalendar->writeScript(); 
							} 
							else { 
							?>
								<input type="text" name="filter[data][<?php echo $field;?>]" value="<?php echo $val;?>" size="<?php echo $attr["size"];?>" <?php echo isset($attr["attrs"])?$attr["attrs"]:"";?>/><br/>
							<?php 
							} 
							?>
					</div>
					<?php } ?>
					<div class="blockform">
						<input type="submit" name="filter[submit]" value="Cari"/>
					</div>
				</form>
			</div>		
		<?php
		}
		if($this->rows==0) return false;
		if(empty($this->pager)) return false;
		if(empty($this->records)) return false;
		if(empty($this->colnames)) return false;
		
		?>
			<a href="print/<?php echo $this->pdf["data"];?>" target="_top" title="Cetak" style="float:left;margin:8px;"><img src="<?php echo $this->pdf["icon"];?>"/></a>
			<div class="page-nav">
			<?php setPageLink($this->pager,$this->baseurl,$request,"top");?>
			</div>
			<div class='detail'>
			<table>
			<thead>
			<tr class="header">
				<td>NO.</td>

				<?php foreach($this->colnames as $key=>$val){?>
				<td><?php echo strtoupper($val["label"]);?></td>
				<?php }?>
				
				<?php if(!empty($this->operations)){?>
				<td><?php echo $this->operationstitle;?></td>
				<?php }?>
			</tr>
			</thead>
			<tbody id="datalist">
			<?php
			foreach($this->records as $no=>$rec){
				$artrow=($no%2)>0?"evenrow":"oddrow";
			?>
			<tr class="<?php echo $artrow;?>">
				<td class="seq"><?php echo $this->pager["startrow"]+$no+1;?></td>
				
				<?php foreach($this->colnames as $key=>$val){?>
					<?php $align=( isset($val["align"]) && !empty($val["align"]) )?"align=".$val["align"]:"";?>
					<?php $patern=( isset($val["patern"]) && !empty($val["patern"]) )?str_replace(":".$key,'$rec[$key]',$val["patern"]):"";?>
					<?php $display=empty($patern)?$patern:eval("return $patern");?>
				
				<td <?php echo $align;?>><?php echo empty($display)?$rec[$key]:$display;?></td>
				
				<?php }?>
				
				<?php if(!empty($this->operations)){?>
				<td class="opcol">
					<?php foreach($this->operations as $op){?>
					<?php $href=str_replace(":id",$rec["id"],$op["url"]); ?>
					<a href="<?php echo $href;?>" title="<?php echo $op["title"];?>" >
						<img src="<?php echo $op["icon"];?>"/>
					</a>&nbsp;
					<?php }?>
				</td>
				<?php }?>

			</tr>
			<?php
			}
			?>
			</tbody>
			</table>
			</div>
			<?php if(!empty($this->listpager)){?>
			<div class="page-nav">
			<?php setPageLink($this->listpager,$this->baseurl,$request,"bottom");?>
			</div>
			<?php }?>
		</div>

		<?php
		$this->init();
		return true;
	}

}

