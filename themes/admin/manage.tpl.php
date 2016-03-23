<?php require 'header.php';?>
<div class="topbox">
	<img src="/themes/images/logo.png" width="200px;"/>
    <ul class="nav">
    	<?
		//输出一级菜单
		$num=0;
		foreach($menu as $i=>$m)
		{
			$num++;
			if($num==1)					
				echo "<li class='checkit'>{$m['name']}</li>";	
			else
				echo "<li>{$m['name']}</li>";	
		}
		?>
    </ul>
    <ul class="topnav">
        <li class="nihao">您好，<?=$this->username?>！</li>
        <li class="tuichu"><?=$this->anchor('changepwd','[修改密码]','target="iframe_main"')?></li>
        <li class="tuichu"><?=$this->anchor('logout','[退出]')?></li>
        <!--<li class="shangcheng"><a href="/" target="_blank">[网站首页]</a></li>
        <li class="shuaxin"><a href="#">刷新</a></li>
        <li class="gengxin"><a href="#">更新缓存</a></li>
        <li class="houtai"><a href="#">后台导航</a></li>-->
    </ul>
</div>
<div class="neirong">
	<div class="leftpanel">
    	<?
		$num=0;
        foreach($menu as $i=>$m)
		{
			$num++;
			//每个一级菜单输出一个div
			?>
            <div class="menu <? if($num>1){echo 'hide';}?>">
            <h1><?=$menu[$i]['name']?></h1>
        	<ul>
				<?
                  //显示左侧二级菜单
                  if(isset($m['son']) && is_array($m['son']))
                  {
                      //$num1=0;
                      foreach($m['son'] as $li)
                      {
                          ?>					
                          <li><?=$this->anchor($li['url'],$li['name'],'target="iframe_main"')?></li>
                          <?
                      }
                  }
                ?>
            </ul></div>
            <?
		}
		?>
    </div>
    <div class="rightpanel">
   		<iframe marginheight="0" width="100%" marginwidth="0" frameborder="0" id="iframe_main" name="iframe_main" src=""></iframe>
    </div>
    <div class="clear"></div>
</div>

<div class="footer">Copyright  &nbsp;2012-2020 &nbsp;璞胜创投  Inc.,All Rights reserved. 
</div>
<script>
$(document).ready(function() 
{	 
	initwh();
	init_menu();
	$(window).resize(function(){
		initwh();
	});
})
</script>
</body>
</html>
