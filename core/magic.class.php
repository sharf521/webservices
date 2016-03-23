<?php

/******************************
 * $File: magic.class.php
 * $Description: 模板引擎
 * $Author: ahui 
 * $Time:2010-03-09
 * $Vesion:1.0
 * $Update:None 
 * $UpdateDate:None 
******************************/

/**
 * 定义magic的目录
 */
if (!defined('MAGIC_DIR')) {
    define('MAGIC_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
}

class Magic 
{
	var $template_dir = "themes";//定义模板目录
	
	var $compile_dir = "data/compile";//定义模板缓存目录
	
	var $plugins_dir = "include/magic";//定义插件目录
	
	var $left_tag = "{";//左边的样式
	
	var $right_tag = "}";//右边的样式
	
	var $mysql_file = 'mysql.class.php';//mysql 文件
	
	var $mysql_open = false;//数据库文件是否打开
	
	var $magic_vars = array();//magic的定义变量
	
    var $file_perms = 0644;//文件的属性
	
	var $dir_perms = 0711;//文件的属性
	
	var $is_compile   =  true;//模板编译检查
	
	var $force_compile = false;//是否一直编译
	
	var $template_error = false;//模板编译错误
	
	/**
	 * 构造函数
	 */
	function Magic() {
		global $mysql;
		$this->mysql = $mysql;
		$this->assign('SCRIPT_NAME', isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME']: @$GLOBALS['HTTP_SERVER_VARS']['SCRIPT_NAME']);
	}
	
	/**
     * 定义模板的值
     *
     * @param array|string $tpl_var the template variable name(s)
     * @param mixed $value the value to assign
     */
    function assign($tpl_var, $value = "") {
        if (is_array($tpl_var)){
            foreach ($tpl_var as $key => $value) {
                if ($key != '') {
                    $this->magic_vars[$key] = $value;
                }
            }
        } else {
            if ($tpl_var != '')
                $this->magic_vars[$tpl_var] = $value;
        }
    }
	
	/**
     * 清除定义的变量
     *
     * @param string $tpl_var 
     */
    function assign_clear($tpl_var) {
        if (is_array($tpl_var)) {
            foreach ($tpl_var as $curr_var){
                unset($this->magic_vars[$curr_var]);
			}
        }else{
            unset($this->magic_vars[$tpl_var]);
		}
    }
	
	 /**
     * 清除所有的变量
     *
     */
    function assign_clear_all() {
        $this->_tpl_vars = array();
    }
	
	
	function magic_include($parse){
		$include_file = $parse['file'];
		$template_dir = empty($parse['vars']['template_dir'])?$this->template_dir:$parse['vars']['template_dir'];
		$this->magic_vars = array_merge($this->magic_vars, $parse['vars']);
		$this->compile_file($include_file,$template_dir);
	}
	
	
	/**编译的文件名路劲**/
	function compile_path($template_name){
		$new_compile_name = "%".urlencode($template_name).".php";
		if ($this->compile_dir != ""){
			$this->compile_path = $this->compile_dir."/".$new_compile_name;
		}else{
			$this->compile_path =  $new_compile_name;
		}
	}
	
	/**模板的文件名路劲**/
	function template_path($template_name,$template_dir=""){
		if ($template_dir!=""){
			$this->template_path =  $template_dir."/".$template_name;
		}elseif ($this->template_dir != "" ){
			$this->template_path =  $this->template_dir."/".$template_name;
		}else{
			$this->template_path = $template_name;
		}
		
	}
	
	function template_read($filename) {
        if ( file_exists($filename) && is_readable($filename) && ($fd = @fopen($filename, 'rb')) ) {
            $contents = '';
            while (!feof($fd)) {
                $contents .= fread($fd, 8192);
            }
            fclose($fd);
            return $contents;
        } else {
            return false;
        }
    }
	
	//创建文件
	function template_write($compile_path,$contents) {
		$this->template_create_dir(dirname($compile_path));//先创建目录
    	if (!($fd = @fopen($compile_path, 'wb'))) {
    	    $_tmp_file = $compile_path . DIRECTORY_SEPARATOR . uniqid('wrt');
       		if (!($fd = @fopen($_tmp_file, 'wb'))) {
				$this->trigger_error("系统无法写入文件'$_tmp_file'");
				return false;
        	}
		}
    	fwrite($fd, $contents);
   		fclose($fd);
		@chmod($template_path, $this->file_perms);
		return true;
	}
	
	/* 循环创建目录 */
	function template_create_dir($compile_path) {
		if (DIRECTORY_SEPARATOR!='/') {
            $compile_path = str_replace('\\','/', $compile_path);
        }
		if (is_dir($compile_path)){
			return true;
		}
		
		if (@ mkdir($compile_path, $this->dir_perms)){
			return true;
		}

		if (!$this->template_create_dir(dirname($compile_path))){
			return false;
		}
		
		return mkdir($compile_path, $this->dir_perms);
	}
	
	function check_compiled($template_path, $compile_path){
		if ($this->force_compile) {
			return false;
		}elseif (!$this->is_compile) {
			return true;//不需要检查编译
		}elseif (!file_exists($compile_path)){
			return false;
		}

		if (filemtime($template_path) <= filemtime($compile_path)) {
			return true;
		} else {
			return false;
		}
	}
	
	function gethtml($template_name,$template_dir){
		$this->compile_path($template_name);//编译文件的完整路劲
		//return $this->compile_path;
		$this->template_path($template_name,$template_dir);//模板的完整路劲
		$_template_path = $this->template_path;
		
		$_template_dir = $this->template_dir ;
		$this->template_dir = $template_dir;
		$this->template_path = $template_dir."/".$template_name;
		/* 判断文件是否存在 */
		$_tpldir = $this->magic_vars["tpldir"];
		$this->magic_vars["tpldir"] = $this->magic_vars['sys_info']['con_webpath'].$template_dir;
		if (!file_exists($this->template_path)){
			$this->trigger_error("the template $this->template_path not exitst");
		}
		
		$_contents = $this->template_read($this->template_path);//读取模板的内容
		$contents = $this->compile_content($_contents);//内容编译
		
		/** 检查是否需要编译，是则自动编译并读取相关的文件 **/
		if (!$this->check_compiled($this->template_path,$this->compile_path)){
			$this->template_write($this->compile_path,$contents);
		}
		
		if(!$this->is_compile){
			$this->compile_path = tempnam ("/tmp", "FOO");
			$this->template_write($this->compile_path,$contents);
		}
		ob_start();
		include $this->compile_path ;
		$contents = ob_get_contents();
		ob_end_clean();
		$this->magic_vars["tpldir"] = $_tpldir;
		$this->template_path = $_template_path;
		$this->template_dir = $_template_dir;
		return $contents;
		
	}
	
	 /**
     * 执行显示模板文件
     *
     * @param string $resource_name
     * @param string $cache_id
     * @param string $compile_id
     */
    function display($template_name,$template_dir=null,$type=null) {
		$this->compile_file($template_name,$template_dir,$type);
	}
	
	function compile_file($template_name,$template_dir="",$type=""){
		$this->template_path($template_name,$template_dir);//模板的完整路劲
		$this->compile_path($template_name);//编译文件的完整路劲
		
		/* 判断文件是否存在 */
		if (!file_exists($this->template_path)){
			$this->trigger_error("the template $this->template_path) not exitst");
		}
		
		$_contents = $this->template_read($this->template_path);//读取模板的内容
		$contents = $this->compile_content($_contents);//内容编译
		

		/** 检查是否需要编译，是则自动编译并读取相关的文件 **/
		if (!$this->check_compiled($this->template_path,$this->compile_path)){
			$this->template_write($this->compile_path,$contents);
		}
		
		if(!$this->is_compile){
			$this->compile_path = tempnam ("/tmp", "FOO");
			$this->template_write($this->compile_path,$contents);
		}
		
		ob_start();
		include $this->compile_path ;
		$contents = ob_get_contents();
		ob_end_clean();
		echo $contents;
		
	}
	
	function compile_content($content){
		if ($content == "") return "";
		$left_tag = preg_quote($this->left_tag, '~');
        $right_tag = preg_quote($this->right_tag, '~');
		$text_blocks = array();
		$compiled_tags = array();
		
        /* 用替换的思想替换掉literal */
		$ldq = preg_quote($this->left_tag, '~');
        $rdq = preg_quote($this->right_tag, '~');
		$search = "~({$ldq}\s*literal\s*{$rdq})(.*?)({$ldq}\s*/literal\s*{$rdq})|{$ldq}\s*php\s*{$rdq}(.*?){$ldq}\s*/php\s*{$rdq}~s";
		preg_match_all($search, $content, $__match);
		$magic_literal = array();
		foreach ($__match[2] as $key => $value){
			$_m = "magicliteral".$key;
			$$_m = $value;
			$content = str_replace($value,$_m,$content);
		}
		
        preg_match_all("~{$left_tag}\s*(.*?)\s*{$right_tag}~s", $content, $_match);
        $template_tags = $_match[1];
        /* 区分所有的模块 */
        $text_blocks = preg_split("~{$left_tag}.*?{$right_tag}~s", $content);
		
		

			
		/* 模块的集成 */
		for ($i = 0; $i < count($template_tags); $i++) {
			$compiled_tags[] = $this->compile_tag($template_tags[$i]);
		}
		
		
		/* 模块的集成 */
		$content = "";
        for ($i = 0; $i <  count($text_blocks); $i++) {
			if (strpos($text_blocks[$i],"magicliteral")!==false){
				$__m = $text_blocks[$i];
				$content .= $$__m ;
			}else{
				$content .= $text_blocks[$i];
			}
			if (isset($compiled_tags[$i])){
				$content .= $compiled_tags[$i];
			}
        }
		return $content;
	}
	
	
	function compile_tag($template_tag){

		preg_match_all('~( (?>[^"\"\'=\s]+))+ | [=]~x', $template_tag, $match);
		$tag_str = $match[0];
        $tag_command = $tag_str[0];
		/* 变量 */

		if ($tag_command{0} == '$' ){
			return $this->compile_variable_tag($template_tag);
		}	
		/* if判断语句 */
		elseif ($tag_command == 'if' || $tag_command == 'else' || $tag_command == 'elseif' || $tag_command == '/if'){
			return $this->compile_if_tag($tag_command,$template_tag);
		}
		
		/* foreach */
		elseif ($tag_command == 'foreach' || $tag_command == '/foreach'){
			$parse_var = $this->compile_parse_var($tag_str);
			return $this->compile_foreach_tag($tag_command,$parse_var);
		}
		/* literal */
		elseif ($tag_command == 'literal' || $tag_command == '/literal'){
			//return $this->compile_literal_tag($tag_command,$parse_var);
		}
		
		
		/* include */
		elseif ($tag_command == 'include'){
			$parse_var = $this->compile_parse_var($tag_str);
			return $this->compile_include_tag($tag_command,$parse_var);
		}
		/* editor */
		elseif ($tag_command == 'editor'){
			$parse_var = $this->compile_parse_var($tag_str);
			return $this->compile_editor_tag($parse_var);
		}
		//其他
		else{
			$parse_var = $this->compile_parse_var($tag_str);
			if ($output=$this->magic_function($tag_command, $parse_var)) {
				return $output;
			}elseif ($output=$this->magic_sqlfunc_tag($tag_command, $parse_var)) {
				return $output;
			}elseif ($output=$this->magic_sql_tag($tag_command, $template_tag)) {
				return $output;
			}elseif ($output=$this->magic_block($tag_command, $parse_var)) {
				return $output;
			} else {
				$this->trigger_error("unrecognized tag '$tag_command'", E_USER_ERROR, __FILE__, __LINE__);
			}
		}
		
	}
	
	function compile_variable_tag($template_tag){
		$_tpl_var = explode("|",$template_tag);
		$result = $this->compile_var_tag($_tpl_var[0]);
		$_output = "";
		$cline = "&*&%$##";//将\|转换为此码，最后再转义
		$aline = "&@@#$&&";//
		$_res = str_replace('\|',$cline,strstr($template_tag,"|"));
		preg_match_all('/(\|)(([a-zA-Z0-9_])+)(([:])?)/',$_res,$res);//标签
		$res = $res[0];
		foreach($res as $key){
			$_res = str_replace($key,$aline,$_res);//将标签替换
		}
		foreach (explode($aline,$_res) as  $key ){
			$_rea[] = str_replace($cline,"|",$key);
		}
		for($i=0;$i<count($res);$i++){
			$__tpl_var = str_replace(array("|",":"),"",$res[$i]);
			$__var = $this->compile_var_tag(str_replace(array("\"","'"),"",$_rea[$i+1]));
			if ($__var=="") $__var = null;
			if ($__var{0}!="$"){
				$__var = "\"".$__var."\"";
			}else{
				$_output .= " if (!isset($__var)) $__var = '';\r\n";
			}
			$_output .= "\$_tmp = \$this->magic_modifier(\"".$__tpl_var."\",\$_tmp,".$__var.");";
		}
		$chars = preg_split('/([%|-|+|*|\/|\(|\)])/',$result, -1, PREG_SPLIT_DELIM_CAPTURE);//运算符
		if ($_output == ""){
			/*return  "<? echo $result; ?>";*/
			$_result = "";
			foreach ($chars as $tag_str){
				if ($tag_str{0} == "$"){
					$_result .= "if (!isset($tag_str)) $tag_str = '';";
				}
			}
			return "<? $_result echo $result; ?>";
		}else{
			//$output = "<? if (!isset($result)) $result = '';\n";
			$output ="<?";
			foreach ($chars as $tag_str){
				if ($tag_str{0} == "$"){
					$output .= " if (!isset($tag_str)) $tag_str = '';";
				}
			}
			$output .= "\$_tmp = $result;";
			$output .= $_output."echo \$_tmp;unset(\$_tmp); ?>";
			return $output;
		}
	}
	
	//处理foreachif等的
	function compile_parse_var($tag_str){
		$parse_var = array();
		if (is_array($tag_str)){
			foreach($tag_str as $key => $value){
				if ($value == "=" ){
					$parse_var[$tag_str[$key-1]] = $this->compile_var_tag($tag_str[$key+1]);
				}
			}
			return $parse_var;		
		}else{
			return $tag_str;
		}
	}
	
	function magic_modifier ($tag_str,$string,$parse_var){
		$_file = $this->plugins_dir."/modifier.".$tag_str.".php";
		if (file_exists($_file)){
			include_once $_file;
			$_func = "magic_modifier_".$tag_str;
			if (function_exists($_func)){
				return $_func($string,$parse_var,$this->magic_vars);
			}else{
				return $this->trigger_error("modifier: missing function '$_func' attribute");
			}
		}
	}
	
	function magic_function ($tag_str,$parse_var){
		$_file = $this->plugins_dir."/function.".$tag_str.".php";
		if (file_exists($_file)){
			include_once $_file;
			$_func = "magic_function_".$tag_str;
			if (function_exists($_func)){
				return $_func($parse_var);
			}else{
				return $this->trigger_error("function: missing function '$_func' attribute");
			}
		}
	}
	//直接获取的函数
	function magic_sqlfunc_tag ($tag_str,$parse){
		$_file = $this->plugins_dir."/sqlfunc.".$tag_str.".php";
		if (file_exists($_file)){
			$output = "";
			$parse_var  = 'array'.'(';
			foreach ($parse as $key => $value){
				$_parse_var[] = '"'.$key.'"=>"'.$value.'"';
			}
			if (isset($_parse_var)){
			$parse_var .= join(",",$_parse_var);
			}
			$parse_var .= ")";
			$_tmp = "\$this->magic_vars['sqlfunc']";
			$output .= "<?  $_tmp = \$this->magic_sqlfunc(\"".$tag_str."\",$parse_var);\n";
			$output .= "echo $_tmp;?>";
			return $output;
		}
	}
	
	//直接获取的函数
	function magic_sqlfunc ($tag_str,$parse_var){
		$_file = $this->plugins_dir."/sqlfunc.".$tag_str.".php";
		if (file_exists($_file)){
			include_once $_file;
			$_func = "magic_sqlfunc_".$tag_str;
			if (function_exists($_func)){
				return $_func($parse_var,$this->magic_vars,$this->mysql);
			}else{
				return $this->trigger_error("function: missing function '$_func' attribute");
			}
		}
	}
	function magic_sql_tag ($tag_str,$template_tag){
		$_tag_str = str_replace("/","",$tag_str);
		$_file = $this->plugins_dir."/sql.".$_tag_str.".php";
		preg_match_all('~( (?>[^"\"=\s]+))+ | [ \"=]~x', $template_tag, $match);
		
		$tag_str = $match[0];
        $tag_command = $tag_str[0];
		$_var = array();
		$m=0;
		$var = "";
		$q=1;
		$a = "b";
		$d =0;
		$n = 1;
		for ($i=1 ;$i<count($tag_str);$i++){
			
			if ( $i>$q && $tag_str[$i]!=" " &&  $a == "b"){//判断第一个的key
				$a = $tag_str[$i];
				$_var[$a] = "";
			}
			if($tag_str[$i]=="\"" && $m==0){
				$q = $i;
				$m=1;
			}
			if($tag_str[$i] == "="  ){
				$d=$i;
			}
			if ($m==1 &&  $i>$q && $i>$d && $tag_str[$i]!="\"" &&  $a != "b" ){
					$_var[$a] .= $this->compile_var_tag($tag_str[$i]);
			}
			
			if ($i>$q && $tag_str[$i]=="\"" && $m==1 ){
				$q = $i;
				$a = "b";
				$m=0;
			}
		}
		$parse_var  = 'array'.'(';
		foreach ($_var as $key => $value){
			$_parse_var[] = '"'.$key.'"=>"'.$value.'"';
		}
		if (isset($_parse_var)){
		$parse_var .= join(",",$_parse_var);
		}
		$parse_var .= ")";
		$var_name = !isset($_var['var'])?"var":$_var['var'];
		$_default = !isset($_var['default'])?"":$_var['default'];
		
		if (file_exists($_file)){
			if ($tag_str[0]{0} != "/"){
				$output = "";
				$_tmp = "\$this->magic_vars['list']";
				$output .= "<? \$default = '$_default'; $_tmp = \$this->magic_sql(\"".$_tag_str."\",$parse_var,\$this->magic_vars,\$this->mysql);\n";
				$output .= " if (count($_tmp"."['result'])>0):foreach ($_tmp"."['result'] as \$this->magic_vars['key'] => \$this->magic_vars['$var_name']):\n;?>";
			}else{
		    	$output = "<? endforeach;else:echo \"\$default\";endif; ?>";
			}
			return $output;

		}
	}
	
	function magic_sql ($tag_str,$parse_var,$magic_vars,$mysql){
		$_file = $this->plugins_dir."/sql.".$tag_str.".php";
		include_once $_file;
		$_func = "magic_sql_".$tag_str;
		if (function_exists($_func)){
			return $_func($parse_var,$magic_vars,$mysql);
		}else{
			return $this->trigger_error("modifier: missing function '$_func' attribute");
		}
		
	}
	
	
	function magic_block ($tag_str,$parse_var){
		$_tag_str = str_replace("/","",$tag_str);
		$_file = $this->plugins_dir."/block.".$_tag_str.".php";
		if (file_exists($_file)){
			include_once $_file;
			$_func = "magic_block_".$_tag_str;
			if (function_exists($_func)){
				return $_func($tag_str,$parse_var,$this->magic_vars);
			}else{
				return $this->trigger_error("block: missing function '$_func' attribute");
			}
		}
	}
	
	
	
	function compile_literal_tag(){
		return "";
	}
	//变量处理
	function compile_var_tag($tag_str){
		$result = "";
		$chars = preg_split('/([%|-|+|*|\/|\(|\)])/',$tag_str, -1, PREG_SPLIT_DELIM_CAPTURE);//运算符
		foreach ($chars as $tag_str){
			if ($tag_str!="" && $tag_str{0} == '$' ){
				$tag_str = str_replace("$","",$tag_str);
				$_var = explode(".",$tag_str);
				if (count($_var)==1){
					if (isset($this->magic_vars['$tag_str'])) { $this->magic_vars['$tag_str'] = "";} 
					
					$result .= "\$this->magic_vars['$tag_str']";
				}elseif ($_var[0] == "magic"){
					$__var = "";
					for($i=2;$i<count($_var);$i++){
						$__var .= "['$_var[$i]']";
					}
					if ($_var[1] == "request"){
						$result .= "\$_REQUEST".$__var;
					}
					if ($_var[1] == "server"){
						$result .= "\$_SERVER".$__var;
					}
					if ($_var[1] == "get"){
						$result .= "\$_GET".$__var;
					}
					if ($_var[1] == "post"){
						$result .= "\$_POST".$__var;
					}
					if ($_var[1] == "session"){
						$result .= "\$_SESSION".$__var;
					}
				}else{
					
					$result .= "\$this->magic_vars";
					foreach($_var as $key ){
						$result .= "['$key']";
					}
				}
				
			}else{
				 $result .= $tag_str;
			}
		}
		return $result;
	}
	
	function compile_if_tag ($tag_command,$template_tag){
		if ($tag_command == "if" || $tag_command == "elseif"){
			$_preg_match = " |!==|*|-|+|\/|%|===|==|!=|<>|<<|>>|<=|>=|\&\&|\|\||>|<|\(|\)|\"\"|\'";
			preg_match_all('~( (?>[^"\''.$_preg_match.'\s]+))+ | ['.$_preg_match.']~x', $template_tag, $match);
			$tag_str = $match[0];
			array_shift($tag_str);
			$_result = "";
			$result = "";
			$_res = "";
			foreach ($tag_str as $key){
				
					if ($key{0} == "$"){
						$_result = $this->compile_var_tag($key);
						$result .= " $_result";
						$_res .= "if (!isset($_result)) $_result='';";
					}elseif ($key == "magic"){
						$result = "";
					}else{
						$result .= $key;
					}
			}
			$result = "<? $_res ;$tag_command ($result): ?>";
			return $result;
		}elseif ($tag_command == "else" ){
			return "<? else: ?>";
		}elseif ($tag_command == "/if" ){
			return "<? endif; ?>";
		}
	}
	
	function compile_include_tag($tag_command,$parse_var) {
		$arg_list = array();
		if (empty($parse_var['file'])) {
			return $this->trigger_error("include: missing 'file' attribute");
		}
		$_file = $parse_var['file'];
		$_arg = "";
		foreach ($parse_var as $key => $value) {
			if ($key != "file"){
				if ($value{0} != "$"){
					$arg_list[] = "'$key' => '$value'";
				}else{
					$arg_list[] = "'$key' => $value";
					$_arg .= "if (!isset($value)) $value=''; ";
				}
			}else{
				$include_file = $value;
			}
		}
		if ($_file{0} != "$"){
			return  "<? \$this->magic_include(array('file' => \"" . $include_file . "\", 'vars' => array(".implode(',', (array)$arg_list).")));?>";
		}else{
			$output  = "<? if (!isset($_file)) $_file='';$_arg; \$_from = $_file; ";
			$output  .= " \$this->magic_include(array('file' => \$_from, 'vars' => array(".implode(',', (array)$arg_list)."))); unset(\$_from);?>";
			return $output; 
		}
		
	}
	function compile_editor_tag($parse_var){
		$_name = $parse_var['name'];
		if (empty($_name)){
			return $this->trigger_error("editor: missing 'name' attribute");
		}
		$_code = empty($parse_var['code'])?"sinaeditor":$parse_var['code'];
		$_value = $parse_var['value'];
		if ($_value{0} != "$"){
			$_value = "\"$_value\"";
		}else{
			$_var = " if (!isset($_value)) $_value='';";
		}
		$result = $this->mysql->db_select("editor","code = '$_code'");
		return "<? $_var ; \$name = \"$_name\" ; \$value = $_value;".html_entity_decode($result['api'])." ?>";
	}
	/*
	function compile_loop_tag($tag_command,$parse_var){
		if  ($tag_command == "loop" ) {
			$_table = "";
			if (isset($parse_var['tablename'])){
				$_table = $parse_var['tablename'];
			}
			if (empty($_table) && !empty($parse_var['table'])){
				$_table = $parse_var['table'];
			}else{	
				return $this->trigger_error("loop: missing 'table' attribute");
			}
			$var_name = empty($parse_var['name'])?"var":$parse_var['name'];
			$where = empty($parse_var['where'])?"":"where ".$parse_var['where'];
			$mysql_result = $this->mysql->db_fetch_arrays("select * from {".$_table."} $where");
			$this->magic_vars["loop_".$var_name] = $mysql_result;
			$_magic_vars = "\$this->magic_vars['loop_$var_name']";
			$result = '<? ';
			$result .= " if(!isset($_magic_vars)) $_magic_vars = array(); \$_from = $_magic_vars; \n if (!is_array(\$_from) && !is_object(\$_from)) { settype(\$_from, 'array'); } \n";
			if (isset($name)) {
				$foreach_props = "\$this->_foreach[$name]";
				$result .= "{$foreach_props} = array('total' => count(\$_from), 'iteration' => 0);\n";
				$result .= "if ({$foreach_props}['total'] > 0):\n";
				$result .= "    foreach (\$_from as $key_part\$this->magic_vars['$item']):\n";
				$result .= "        {$foreach_props}['iteration']++;\n";
			} else {
				$result .= "if (count(\$_from)):\n";
				$result .= "    foreach (\$_from as \$this->magic_vars['key'] => \$this->magic_vars['$var_name']):\n";
			}
			$result .= '?>';
			return $result;
		}else if ($tag_command == "/loop"){
			return "<? endforeach; endif; unset(\$_from); ?>";
		}
	}
	*/
	function compile_foreach_tag($tag_command,$parse_var) {
		if  ($tag_command == "foreach" ) {
			if (empty($parse_var['from'])) {
				return $this->trigger_error("foreach: missing 'from' attribute");
			}
			$from = $parse_var['from'];
			
			if (empty($parse_var['item'])) {
				return $this->trigger_error("foreach: missing 'item' attribute");
			}
			$item = $parse_var['item'];
			
			if (isset($parse_var['key'])) {
				$key  = $parse_var['key'];
				if (!preg_match('~^\w+$~', $key)) {
					return $this->trigger_error("foreach: 'key' must to be a variable name (literal string)");
				}
				$key_part = "\$this->magic_vars['$key'] => ";
			} else {
				$key = null;
				$key_part ="\$this->magic_vars['key'] => ";
			}
			//var_dump(count($this->magic_vars['move_list']));
			//var_dump(count($from));
			$result = '<? ';
			$result .= " if(!isset($from) || $from=='') $from = array();  \$_from = $from; \n if (!is_array(\$_from) && !is_object(\$_from)) { settype(\$_from, 'array'); } \n";
			if (isset($name)) {
				$foreach_props = "\$this->_foreach[$name]";
				$result .= "{$foreach_props} = array('total' => count(\$_from), 'iteration' => 0);\n";
				$result .= "if ({$foreach_props}['total'] > 0):\n ";
				$result .= "    foreach (\$_from as $key_part\$this->magic_vars['$item']):\n";
				$result .= "        {$foreach_props}['iteration']++;\n";
			} else {
				$result .= "if (count(\$_from)>0):\n;";
				$result .= "    foreach (\$_from as $key_part \$this->magic_vars['$item']):\n";
				
			}
			$result .= '?>';
			return $result;
		}else if ($tag_command == "/foreach"){
			return "<? endforeach; endif; unset(\$_from); ?>";
		}
	}

	

	 /**
     * 触发器错误
     *
     * @param string $error_msg
     * @param integer $error_type
     */
    function trigger_error($error_msg, $error_type = E_USER_WARNING) {
        trigger_error("Magic error: $error_msg", $error_type);
    }
}
?>