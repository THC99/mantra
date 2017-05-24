/*
Programmed by : Didi Sukyadi
ver: 1.99y
*/

today=new Date();

$(function(){
	runClock();
	setInterval('runClock()',1000);	
});

//-------------------------  Device Controller Function ---------------------------\\

function encsay(str,pass){
	add=0;newpass="";result="";
	div=str.length/pass.length;
	while(add<=div){
		newpass+=pass;
		add++;
	}
	for(i=0;i<str.length;i++){
		tmp=str.charCodeAt(i)+newpass.charCodeAt(i);
		tmp=tmp>255?(tmp-256):tmp;
		result+=tmp.toString(16).toUpperCase();
	}
	return result;
}

function submitLogin(sender,action,sesskey,order){			
	keycode=String.fromCharCode(27,27,27);
	sessval=sender[action+'[captcha]'].value+keycode+
			sender[action+'[logname]'].value+keycode+
			sender[action+'[passkey]'].value+keycode;
	sender[action+'['+sesskey+']'].value=encsay(sessval,order);
	sender[action+'[logname]'].value='';
	sender[action+'[passkey]'].value='';
	sender[action+'[captcha]'].value='';	
}


Date.prototype.getDayID=function(){
	dow=new Array('Min','Sen','Sel','Rab','Kam','Jum','Sab');
	return dow[this.getDay()];
}

Date.prototype.getMonthID=function(){
	moy=new Array('Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nop','Des');
	return moy[this.getMonth()];
}

Date.prototype.getDateStr=function(){
	return ('0'+this.getDate()).slice(-2);
}

Date.prototype.getMonthStr=function(){
	return ('0'+this.getMonth()).slice(-2);
}

Date.prototype.getHourStr=function(){
	return ('0'+this.getHours()).slice(-2);
}

Date.prototype.getMinuteStr=function(){
	return ('0'+this.getMinutes()).slice(-2);
}

Date.prototype.getSecondStr=function(){
	return ('0'+this.getSeconds()).slice(-2);
}

Date.prototype.getMillisecondStr=function(){
	return ('0'+this.getMilliseconds()).slice(-2);
}

Date.prototype.getFullDateTimeStrID=function(){
	return this.getDayID()+", "+this.getDateStr()+" "+this.getMonthID()+" "+this.getFullYear()+" / "+this.getHourStr()+":"+this.getMinuteStr()+":"+this.getSecondStr();
}

Date.prototype.getFullDateTimeStr=function(){
	return this.getDay()+", "+this.getDateStr()+" "+this.getMonth()+" "+this.getFullYear()+" / "+this.getHourStr()+":"+this.getMinuteStr()+":"+this.getSecondStr();
}

Date.prototype.getFullDateStrID=function(){
	return this.getDayID()+", "+this.getDateStr()+" "+this.getMonthID()+" "+this.getFullYear();
}

Date.prototype.getFullDateStr=function(){
	return this.getDay()+", "+this.getDateStr()+" "+this.getMonth()+" "+this.getFullYear();
}

Date.prototype.getFullTimeStr=function(){
	return this.getHourStr()+":"+this.getMinuteStr()+":"+this.getSecondStr();
}

function runClock(){
	today.setTime(today.getTime()+1000);
	txtTime=today.getFullDateTimeStrID();
	el=document.getElementById('timer');
	if(el){
		if(!el.hasChildNodes()){
			el.appendChild(document.createTextNode(''));
		}
		el.firstChild.nodeValue = txtTime;
	}
}

function eraseChild(el){
	if(el) while(el.hasChildNodes()) el.removeChild(el.firstChild);
}

function createPageNumber(navID,className,pageNum,maxPage,interval){
	if(interval<1) interval=1;
	skip=pageNum>interval?pageNum-interval:0;
	step=maxPage<interval?maxPage:interval;
	idxPage=new Array();
	for(i=1;i<=step;i++) idxPage[i-1]=skip+i;

	el = document.getElementById('nav-pagenum'+navID);
	if(el){
		eraseChild(el);
		for(i=0;i<idxPage.length;i++) {
			obj=document.createElement('button');
			obj.style.margin='0 2px';
			obj.style.padding='4px';
			obj.style.minWidth='32px';
			//obj.style.border='1px solid silver';
			obj.style.border='0px';
			if(pageNum==idxPage[i]){
				obj.style.backgroundColor='#007aca';
				obj.style.color='#fff';
			}
			else{
				obj.style.backgroundColor='#f9f9f9';
				obj.style.color='#333';
			}
			obj.style.cursor='pointer';
			obj.style.outline='none';
			obj.appendChild(document.createTextNode(idxPage[i]));
			obj.onclick=function(){
				goPageTable(navID,className,this.firstChild.nodeValue,maxPage);
			}
			el.appendChild(obj);	
		}
	}
}

function goPageTable(navID,className,sign,maxPage)
{
	currentPage=0;
	for(i=1;i<=maxPage;i++){
		el = document.getElementById(className+i);
		if(el){
			if(el.style.display=='block') currentPage=i;
			el.style.display='none';
		}
	}

	switch(sign){
		case '<':pageNum=1;break;
		case '-':pageNum=currentPage-1<1?1:currentPage-1;break;
		case '+':pageNum=currentPage+1>maxPage?maxPage:currentPage+1;break;
		case '>':pageNum=maxPage;break;
		default :pageNum=parseInt(sign);break;
	}
	createPageNumber(navID,className,pageNum,maxPage,5);

	el=document.getElementById(className+pageNum);
	if(el) el.style.display='block';
}



function stringOf(ch, len) {
	var arr = [];
	arr.length = len + 1;
	return arr.join(ch);
}

function hilightbarTable(el){
	if(!el) return;
	objTag=el.getElementsByTagName('tr');
	if(objTag && objTag.length>0) for(var i=0;i<objTag.length;i++){
		objTag[i].onmouseover = function(e) {
			if(this.cells.length>0) for(var x=0;x<this.cells.length;x++)
			{
				this.cells[x].name=this.cells[x].style.backgroundColor;
				this.cells[x].style.backgroundColor="#aadcff";
			}
			this.style.cursor="default";
		}

		objTag[i].onmouseout = function(e) {
			if(this.cells.length>0) for(var x=0;x<this.cells.length;x++)
			{
				this.cells[x].style.backgroundColor=this.cells[x].name;
			}
 			this.style.cursor="default";
		}
	}
}


function checkAlpha(e){
	if(window.event) // IE 
		key = e.keyCode;
	else if(e.which) // Netscape/Firefox/Opera
		key = e.which;

	if (key >= 65 && key <= 90) return true; 
	else return false;
}

function upperCase(e){
	e.value=e.value.toUpperCase();
}

function lowerCase(e){
	e.value=e.value.toLowerCase();
}

function noNumbers(e){
	var keynum;
	var keychar;
	var numcheck;

	if(window.event) // IE
		keynum = e.keyCode;
	else if(e.which) // Netscape/Firefox/Opera
		keynum = e.which;


	// control keys
	if ((keynum==null) || (keynum==0) || (keynum==8) || 
		(keynum==9) || (keynum==13) || (keynum==27) )
		return true;

	if (e.ctrlKey) return false;

	keychar = String.fromCharCode(keynum);
	numcheck = /\d/;

	return !numcheck.test(keychar);
}

// copyright 1999 Idocs, Inc. http://www.idocs.com
// Distribute this script freely but keep this notice in place
function numbersOnly(myfield, e, dec){
	var key;
	var keychar;

	if(window.event) key = window.event.keyCode;
	else if(e) key = e.which;
	else return true;
	keychar = String.fromCharCode(key);

	// control keys
	if((key==null) || (key==0) || (key==8) || 
		(key==9) || (key==13) || (key==27) )
	   return true;
	// numbers
	else if((("0123456789").indexOf(keychar) > -1))
	   return true;
	// decimal point jump
	else if(dec && (keychar == ".")){
	   myfield.form.elements[dec].focus();
	   return false;
	}
	else return false;
}


// copyright 1999 Idocs, Inc. http://www.idocs.com
// Distribute this script freely but keep this notice in place
function letterNumber(e,idx){
	var key;
	var keychar;
	var scantype;

	if(idx==undefined) idx=0;

	scantype=new Array();
	scantype[0]="ABCDEFGHIJKLMNOPQRSTUVWXYZ "; // city/name
	scantype[1]="ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789.- "; // address
	scantype[2]="abcdefghijklmnopqrstuvwxyz0123456789_.-"; // userid																		
	scantype[3]="abcdefghijklmnopqrstuvwxyz0123456789_"; // id/name
	scantype[4]="abcdefghijklmnopqrstuvwxyz0123456789@.-"; // email
	scantype[5]="abcdefghijklmnopqrstuvwxyz0123456789_"; // servicename
	scantype[6]="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_"; // tagname
	scantype[7]="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_#"; // methodname
	scantype[8]="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_:/.\\-?#"; // url
	scantype[9]="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_:/.-?#=&;+% "; // advance url
	scantype[10]="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_.-="; // dbname
	scantype[11]="0123456789"; // number																		
	scantype[12]="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_"; // fieldname
	scantype[13]="0123456789.,-"; // float																		

	if(window.event) key = window.event.keyCode;
	else if(e) key = e.which;
	else return true;
	
	keychar = String.fromCharCode(key);

	// control keys
	if ((key==null) || (key==0) || (key==8) || 
		(key==9) || (key==13) || (key==27) )
	   	return true;
	// alphas and numbers
	else if((scantype[idx].indexOf(keychar) > -1)) return true;
	else return false;
}

function specialSubmit(myfield,e){
	var keycode;
	if(window.event) keycode = window.event.keyCode;
	else if(e) keycode = e.which;
	else return true;

	if(keycode == 13){
	   myfield.form.submit();
	   return false;
	}
	else return true;
}

function addSlashes(textdata){
    return textdata.replace(/\\/g, '\\\\').
        				replace(/\u0008/g, '\\b').
        				replace(/\t/g, '\\t').
        				replace(/\n/g, '\\n').
        				replace(/\f/g, '\\f').
        				replace(/\r/g, '\\r').
        				replace(/'/g, '\\\'').
        				replace(/"/g, '\\"');
}



