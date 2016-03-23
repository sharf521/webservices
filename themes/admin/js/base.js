// JavaScript Document

//防止重复提交
function setdisabled() 
{ 
	$("input[type='submit']").attr("disabled",true); 
	return true;
}

function init_menu()
{	
	//一级菜单点击事件
	var tablei = $("li",'.nav');
	var udiv = $('.menu');	 
	tablei.click(function(){
		var index = tablei.index(this);
		$(tablei[index]).addClass("checkit").siblings().removeClass("checkit");
		udiv.addClass("hide");
		$(udiv[index]).removeClass("hide");
		
		//左侧第一个菜单选中，内容显示中间iframe
		//var firstli=$('li:first',udiv[index]);
		//$('#iframe_main').attr('src',$('a',firstli).attr('href'));
		//$("li",'.leftpanel').removeClass("menuSelectd");		
		//$(firstli).addClass("menuSelectd");
	});
	
	//左侧菜单选中样式
	var umenu = $("li",'.menu');	 
	umenu.click(function(){
		var i = umenu.index(this);
		$(umenu[i]).addClass("menuSelectd").siblings().removeClass("menuSelectd");
	})
}
function initwh()
{
	$('.leftpanel').css('height',($(window).height()-90-50));
	$('#iframe_main').css('width',($(window).width()-200));
	$('#iframe_main').css('height',($(window).height()-90-50));	
}


//proxy.php   start
function changeProvince(value)
{
	document.getElementById('province').value=value;
	var sel=document.getElementById('city');
	if(value!='0')
	{
		changeSel(sel,value);
	}
	else
	{
		sel.options.length=0;
	}
	document.getElementById('county').options.length=0;
}
function changeCity(value)
{
	document.getElementById('city').value=value;
	var sel=document.getElementById('county');
	changeSel(sel,value);
}
function changeSel(sel,id,str)
{
	$.post("/index.php/ajax/region_substring/"+id,{},function(str){
		var arr =str.split("[#]");
		sel.options.length=0;
		sel.options.add(new Option('请选择','0'));
		for(v in arr)
		{
			var v=arr[v].split("::");
			if(v[0]!='')
				sel.options.add(new Option(v[1],v[0]));				
		}
	});		
}
//proxy.php   end

//car_view.php   start
function cmycurd(num,id){  //转成人民币大写金额形式 
  var str1 = '零壹贰叁肆伍陆柒捌玖';  //0-9所对应的汉字 
  var str2 = '万仟佰拾亿仟佰拾万仟佰拾元角分'; //数字位所对应的汉字 
  var str3;    //从原num值中取出的值 
  var str4;    //数字的字符串形式 
  var str5 = '';  //人民币大写金额形式 
  var i;    //循环变量 
  var j;    //num的值乘以100的字符串长度 
  var ch1;    //数字的汉语读法 
  var ch2;    //数字位的汉字读法 
  var nzero = 0;  //用来计算连续的零值是几个
  var spanid = id;  //显示转换结果的位置 
  
  num = Math.abs(num).toFixed(2);  //将num取绝对值并四舍五入取2位小数 
  str4 = (num * 100).toFixed(0).toString();  //将num乘100并转换成字符串形式 
  j = str4.length;      //找出最高位 
  if (j > 15){return '溢出';} 
  str2 = str2.substr(15-j);    //取出对应位数的str2的值。如：200.55,j为5所以str2=佰拾元角分 
  
  //循环取出每一位需要转换的值 
  for(i=0;i<j;i++){ 
    str3 = str4.substr(i,1);   //取出需转换的某一位的值 
    if (i != (j-3) && i != (j-7) && i != (j-11) && i != (j-15)){    //当所取位数不为元、万、亿、万亿上的数字时 
		if (str3 == '0'){ 
			ch1 = ''; 
			ch2 = ''; 
		nzero = nzero + 1; 
		} 
		else{ 
			if(str3 != '0' && nzero != 0){ 
				ch1 = '零' + str1.substr(str3*1,1); 
				ch2 = str2.substr(i,1); 
				nzero = 0; 
			} 
			else{ 
				ch1 = str1.substr(str3*1,1); 
				ch2 = str2.substr(i,1); 
				nzero = 0; 
			} 
		} 
	} 
	else{ //该位是万亿，亿，万，元位等关键位 
      	if (str3 != '0' && nzero != 0){ 
        	ch1 = "零" + str1.substr(str3*1,1); 
        	ch2 = str2.substr(i,1); 
        	nzero = 0; 
      	} 
      	else{ 
			if (str3 != '0' && nzero == 0){ 
				ch1 = str1.substr(str3*1,1); 
				ch2 = str2.substr(i,1); 
				nzero = 0; 
			} 
			else{ 
				if (str3 == '0' && nzero >= 3){ 
					ch1 = ''; 
					ch2 = ''; 
					nzero = nzero + 1; 
				} 
       			else{ 
      				if (j >= 11){ 
              			ch1 = ''; 
              			nzero = nzero + 1; 
   					} 
   					else{ 
					 	ch1 = ''; 
					 	ch2 = str2.substr(i,1); 
					 	nzero = nzero + 1; 
   					} 
          		} 
  			} 
   		} 
	} 
    if (i == (j-11) || i == (j-3)){  //如果该位是亿位或元位，则必须写上 
        ch2 = str2.substr(i,1); 
    } 
    str5 = str5 + ch1 + ch2; 
    
    if (i == j-1 && str3 == '0' ){   //最后一位（分）为0时，加上"整" 
      str5 = str5 + '整'; 
    } 
  } 
  if (num == 0){ 
    str5 = '零元整'; 
  }
  document.getElementById(spanid).innerHTML=str5;
} 
//car_view.php   end

//article.php  start
function getsel(idnum,id)
{
	str=cate_arr[id];
	if(str!='' && str!=undefined)
	{							
		var sel=appendSelect(idnum);
		var arr =str.split("[SER]");
		sel.options.length=0;
		for(v in arr)
		{
			var v=arr[v].split("#");
			sel.options.add(new Option(v[1],v[0]));				
		}		
	}
	else
	{
		removeSelect(idnum);			
	}
	document.getElementById('category'+idnum).value=id;
}
//获取下一个select  id命名规格category+编号
function appendSelect(idnum)
{
	var thisnum=idnum+1;
	if(document.getElementById('category'+thisnum))
	{
		sel = document.getElementById('category'+thisnum);
		removeSelect(thisnum);//移除后面的分类
	}
	else 
	{
		sel = document.createElement("select");
		sel.id = 'category'+thisnum;
		sel.name='categoryid[]';
		sel.multiple='multiple';
		sel.className='multiple';
		sel.onchange=function(){getsel(thisnum,this.value);}
		document.getElementById('div_category').appendChild(sel);
	}
	return sel;
}
//移除其它的select
function removeSelect(idnum)
{
	var thisnum=idnum+1;
	while(document.getElementById('category'+thisnum))
	{
		document.getElementById('div_category').removeChild(document.getElementById('category'+thisnum));
		thisnum=thisnum+1;
	}
}

//上传图片
function upload_image(id,type)
{
	$('#upload_span_'+id).html('上传中...');
	$.ajaxFileUpload({
		url:'/comupload/ajaxFileUpload/?type='+type,
		fileElementId :'upload_'+id,
		dataType:'json',
		success : function (result, status){
        	if(result.status == 1){
				var path=result.data;
        		$('#'+id).val(path);
				var _str="<a href='"+path+"' target='_blank'><img src='"+path+"' height='100'/></a>";
				$('#upload_span_'+id).html(_str);
        	}else{
        		alert(result.data);
        	}
		},
		error: function (result, status, e){
			alert(e);
		}
	});
	return false;		
}

//article.php   end

//user.tpl.php  start
function openlist(o)
{
	var ul = $(o).parent().find('ul').first();
	ul.slideToggle("fast",function(){
			if (ul.is(':hidden')) 
			{
				$(o).attr("class","col1");
			}
			else
			{				
				$(o).attr("class","col1-1");
			}
		});
}
//user.tpl.php  end

//goods.tpl.php  start
$(function(){ 
	$('.up_btn').click(function (){
		var i=$(this).parent().parent().index();//当前行的id
		if(i>1){//不是表头，也不是第一行，则可以上移
			var tem=$(this).parent().parent().prev().clone(true);
			$(this).parent().parent().prev().remove();
			$(this).parent().parent().after(tem);
		}	
	});
	$('.down_btn').click(function(){
		var l=$("#MySpecTB tr").length;//总行数
		var i=$(this).parent().parent().index();//当前行的id
		if(i<l-1){//不是最后一行，则可以下移
		  var tem=$(this).parent().parent().next().clone(true);
		  $(this).parent().parent().next().remove();
		  $(this).parent().parent().before(tem);
		}	
	});	
	$('.delete_btn').click(function(){
		var l=$("#MySpecTB tr").length;//总行数
		if(l>3)//最少保留两行
		{
			$(this).parent().parent().remove();
		}
	});
	$('.add_btn').click(function(){
		var tem=$("#MySpecTB tr:last").clone(true);
		$("#MySpecTB tr:last").after(tem);
	});
});

function calculation()
{
	var price=$("#price").val();
	var orginal_price=$("#original_price").val();
	var discount_rate=(price/orginal_price).toFixed(3);
	$("#discount_span").html(discount_rate*10+'折');
	$('#discount_rate').val(discount_rate);
}
function upload_goods_image(type)
{
	$('.LeapFTP_upload_f').html("上传中。。。");
	$.ajaxFileUpload({
		url:'/comupload/ajaxFileUpload/?type='+type,
		fileElementId :'upload_image',
		dataType:'json',
		success : function (result, status){
        	if(result.status == 1){
				var path=result.data;
				var id=result.id;
        		$('#goods_image').val($('#goods_image').val()+id+',');
				var _str="<li><a href='"+path+"' target='_blank'><img src='"+path+"'/></a><div class='delete'><a onclick='set_cover(this,"+id+")'>设为封面</a><a onclick='delete_image(this,"+id+")'>删除</a></div></li>";
				$('.LeapFTP-upimg').before(_str);
        	}else{
        		alert(result.data);
        	}
			$('.LeapFTP_upload_f').html("");
		},
		error: function (result, status, e){
			alert(e);
		}
	});
	return false;		
}
function set_cover(o,id)
{
	var li = $($(o).parent()).parent();
	var cover = li.parent().find('#cover');
	if(cover.length > 0)
	{
		var a = (cover.find('div')).find('a').first();
		a.html("设为封面");
		cover.attr("id","");
	}
	$(o).html("封面");
	li.attr("id","cover");
	$('#goods_cover').val(id);
}
function delete_image(o,id)
{
	var goods_id = Number($('#goods_id').val());
	var li = $($(o).parent()).parent();
	var car_image = $('#goods_image').val();
	car_image=car_image.replace(','+id+',',',');
	$('#goods_image').val(car_image);
	li.remove();
	var ajax=$.ajax({url:'/index.php/comupload/ajaxGoodsDelete/?id='+id+'&goods_id='+goods_id});
}
//goods.tpl.php  end

//message.tpl.php  start
function change_reply(o)
{
	var target=$(o).parent().find('div').first();
	var textarea=target.find('textarea');
	$(o).hide();
	$(target).show();
	textarea.focus();
}
function back_reply(o)
{
	var target=$($(o).parent()).parent().find('.click-hide');
	if($(o).val() != "")
	{
		return false;
	}
	$(o).parent().hide();
	$(target).show();
}
//message.tpl.php  end


