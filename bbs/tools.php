<?php

/*
	[Discuz!] Tools (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: tools.php 1265 2007-10-24 08:03:15Z$
*/

$tool_password = 'Em123456'; // ☆★☆★☆★ 请您设置一个工具包的高强度密码，不能为空！☆★☆★☆★

$lockfile = 'forumdata/tool.lock';
$target_fsockopen = '0'; //使用何种方式进行连接服务器 0=域名, 1=IP （使用IP方式需要保证IP地址可以正常访问到您的站点）

$alertmsg = ' onclick="alert(\'点击确定开始运行,可能需要一段时间,请稍候\');"';
if(!file_exists('./config.inc.php') || !is_writeable('./forumdata')) {
	$alertmsg = '';
	errorpage('工具箱必须放在论坛根目录下才能正常使用.');
}
define('DISCUZ_ROOT', dirname(__FILE__).'/');
define('VERSION', '2.1.0');
error_reporting(E_ERROR | E_WARNING | E_PARSE);
@set_time_limit(0);
foreach(array('_COOKIE', '_POST', '_GET') as $_request) {
	foreach($$_request as $_key => $_value) {
		($_key{0} != '_' && $_key != 'tool_password' && $_key != 'lockfile') && $$_key = $_value;
	}
}

if(@file_exists($lockfile)) {
	$alertmsg = '';
	errorpage("<h6>工具箱已关闭，如需开启只要通过 FTP 删除 forumdata 下的 tool.lock 文件即可！ </h6>");
} elseif ($tool_password == ''){
	$alertmsg = '';
	errorpage('<h6>工具箱密码默认为空，第一次使用前请您修改本文件中$tool_password设置密码！</h6>');
}

if($_POST['action'] == 'login') {
	setcookie('toolpassword', $_POST['toolpassword'], 0);
	echo '<meta http-equiv="refresh" content="2 url=?">';
	errorpage("<h6>请稍等，程序登录中！</h6>");
}

if(isset($_COOKIE['toolpassword'])) {
	if($_COOKIE['toolpassword'] != $tool_password) {
		$alertmsg = '';
		errorpage("login");
	}
} else {
	$alertmsg = '';
	errorpage("login");
}

$action = $_GET['action'];

if($action == 'repair') {
	$check = $_GET['check'];
	$nohtml = $_GET['nohtml'];
	$iterations = $_GET['iterations'];
	$simple = $_GET['simple'];

	if(@!include("./config.inc.php")) {
		if(@!include("./config.php")) {
			htmlheader();
			cexit("<h4>请先上传config文件以保证您的数据库能正常链接！</h4>");
		}
	}
	mysql_connect($dbhost, $dbuser, $dbpw);
	mysql_select_db($dbname);
	$counttables = $oktables = $errortables = $rapirtables = 0;

	if($check) {

		$tables=mysql_query("SHOW TABLES");

		if(!$nohtml) {
			echo "<html><head></head><body>";
		}

		if($iterations) {
			$iterations --;
		}
		while($table=mysql_fetch_row($tables)) {
			if(substr($table[0], -8) != 'sessions') {
				$counttables += 1;
				$answer=checktable($table[0],$iterations);
				if(!$nohtml) {
					echo "<tr><td colspan=4>&nbsp;</td></tr>";
				} elseif (!$simple) {
					flush();
				}
			}
		}

		if(!$nohtml) {
			echo "</body></html>";
		}

		if($simple) {
		htmlheader();
			echo '<h4>检查修复数据库</h4>
			    <h5>检查结果:</h5>
					<table>
						<tr><th>检查表(张)</th><th>正常表(张)</th><th>错误表(张)</th><th>错误数(个)</th></tr>
						<tr><td>'.$counttables.'</td><td>'.$oktables.'</td><td>'.$rapirtables.'</td><td>'.$errortables.'</td></tr>
					</table>
				<p>检查结果没有错误后请返回工具箱首页反之则继续修复</p>
				<p><b><a href="tools.php?action=repair">继续修复</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="tools.php">返回首页</a></b></p>
				</td></tr></table>';
			specialdiv();
		}
	} else {
		htmlheader();
		echo "<h4>检查修复数据库</h4>
		<div class='specialdiv'>
				操作提示：
				<ul>
				<li>您可以通过下面的方式修复已经损坏的数据库。点击后请耐心等待修复结果！</li>
				<li>本程序可以修复常见的数据库错误，但无法保证可以修复所有的数据库错误。(需要 MySQL 3.23+)</li>
				</ul>
				</div>
				<h5>操作：</h5>
				<ul>
				<li><a href=\"?action=repair&check=1&nohtml=1&simple=1\">检查并尝试修复数据库1次</a>
				<li><a href=\"?action=repair&check=1&iterations=5&nohtml=1&simple=1\">检查并尝试修复数据库5次</a> (因为数据库读写关系可能有时需要多修复几次才能完全修复成功)
				</ul>";
		specialdiv();
	}
	htmlfooter();
} elseif ($action == 'doctor') {
	//论坛医生功能
	htmlheader();
		echo "<script language=\"javascript\">
					function copytoclip(obj) {
						var userAgent = navigator.userAgent.toLowerCase();
						var is_opera = userAgent.indexOf('opera') != -1 && opera.version();
						var is_ie = (userAgent.indexOf('msie') != -1 && !is_opera) && userAgent.substr(userAgent.indexOf('msie') + 5, 3);
						if(is_ie && obj.style.display != 'none') {
							var rng = document.body.createTextRange();
							rng.moveToElementText(obj);
							rng.scrollIntoView();
							rng.select();
							rng.execCommand(\"Copy\");
							rng.collapse(false);
						}
					}
					function $(id) {
						return document.getElementById(id);
					}
					function openerror(error){
						obj = document.getElementById(error);
						if(obj.style.display == ''){
							obj.style.display='none';
						}else{
							obj.style.display='';
						}
					}
			  </script>";
		function create_checkfile() {
			global $dir;
			$fp = @fopen('./forumdata/checkfile.php',w);
			$includedir = $dir != './' ?  str_replace('forumdata/','./',$dir) : '../';
			$content = "<?php
			define('IN_DISCUZ',TRUE);
			if(function_exists('ini_set')) @ini_set('display_errors',1);
			if(\$_GET['file'] != 'config.inc.php') include '../include/common.inc.php';
			error_reporting(E_ERROR | E_WARNING | E_PARSE);
			include '$includedir'.\$_GET['file'];\n?>";
			fwrite($fp, $content);
			fclose($fp);
		}
		function http_fopen($host, $path, $port="80") {
			global $target_fsockopen;
			$conn_host = $target_fsockopen == 1 ? gethostbyname($host) : $host;
			$conn_port = $port;
			$abs_url = "http://$host:$port$path";
			$query="GET   $abs_url   HTTP/1.0\r\n".
				  "HOST:$host:$port\r\n".
				  "User-agent:PHP/class   http   0.1\r\n".
				  "\r\n";
			$fp=fsockopen($conn_host, $conn_port);
			if(!$fp){
			   return   false;
			}
			fputs($fp,$query);
			//得到返回的结果
			$contents = "";
			while (!feof($fp)) {
				$contents .= fread($fp, 1024);
			}
			fclose($fp);
			$array = split("\n\r", $contents, "2");
			return trim($array[1]);
		}
		//论坛模式样式代码变量
		$ok_style_s = '[color=RoyalBlue][b]';
		$error_style_s = '[color=Red][b]';
		$style_e = '[/b][/color]';
		$title_style_s = '[b]';
		$title_style_e = '[/b]';

		$phpfile_array = array('discuzroot', 'templates', 'cache');//文件错误检查中的目录及对应名称($dir_array)
		$dir_array = array('论坛根目录', '模板缓存目录(forumdata/templates)', '其它缓存目录(forumdata/cache)');
		$doctor_top = count($phpfile_array) - 1;

		if(@!include("./config.inc.php")) {
			if(@!include("./config.php")) {
			cexit("<h4>请先上传config文件以保证您的数据库能正常链接！</h4>");
			}
		}
	if($doctor_step == $doctor_top) {

		//检查Config.inc.php文件配置
		$carray = $clang = $comment = array();
		$doctor_config = $doctor_config_db = '';
		$configfilename = file_exists('./config.inc.php') ? './config.inc.php' : './config.php';
		$fp = @fopen($configfilename, 'r');
		$configfile = @fread($fp, @filesize($configfilename));
		@fclose($fp);
		preg_match_all("/[$]([\w\[\]\']+)\s*\=\s*[\"']?(.*?)[\"']?;/is", $configfile, $cmatch);
		foreach($cmatch[1] as $key => $var) {
			if(!in_array($var, array('database','adminemail','admincp'))) {
				$carray[$var] = $cmatch[2][$key];
			}
		}
		$clang = array(
			'dbhost' => '数据库服务器',
			'dbuser' => '数据库用户名',
			'dbpw' => '数据库密码',
			'dbname' => '数据库名',
			'pconnect' => '数据库是否持久连接',
			'cookiepre' => 'cookie 前缀',
			'cookiedomain' => 'cookie 作用域',
			'cookiepath' => 'cookie 作用路径',
			'tablepre' => '表名前缀',
			'dbcharset' => 'MySQL链接字符集',
			'charset' => '论坛字符集',
			'headercharset' => '强制论坛页面使用默认字符集',
			'tplrefresh' => '论坛风格模板自动刷新开关',
			'forumfounders' => '论坛创始人uid',
			'dbreport' => '是否发送错误报告给管理员',
			'errorreport' => '是否屏蔽程序错误信息',
			'attackevasive' => '论坛防御级别',
			'admincp[\'forcesecques\']' => '管理人员是否必须设置安全提问才能进入系统设置',
			'admincp[\'checkip\']' => '后台管理操作是否验证管理员的 IP',
			'admincp[\'tpledit\']' => '是否允许在线编辑论坛模板',
			'admincp[\'runquery\']' => '是否允许后台运行 SQL 语句',
			'admincp[\'dbimport\']' => '是否允许后台恢复论坛数据',
		);
		$comment = array(
			'pconnect' => '非持久连接',
			'cookiepre' => '不检测',
			'cookiepath' => '不检测',
			'charset' => '不检测',
			'adminemail' => '不检测',
			'admincp' => '非设置项',
		);
		@mysql_connect($carray['dbhost'], $carray['dbuser'], $carray['dbpw']) or $mysql_errno = mysql_errno();
		!$mysql_errno && @mysql_select_db($carray['dbname']) or $mysql_errno = mysql_errno();
		$comment_error = "{$error_style_s}出错{$style_e}";
		if ($mysql_errno == '2003') {
			$comment['dbhost'] = "{$error_style_s}端口设置出错{$style_e}";
		} elseif ($mysql_errno == '2005') {
			$comment['dbhost'] = $comment_error;
		} elseif ($mysql_errno == '1045') {
			$comment['dbuser'] = $comment_error;
			$comment['dbpw'] = $comment_error;
		} elseif ($mysql_errno == '1049') {
			$comment['dbname'] = $comment_error;
		} elseif (!empty($mysql_errno)) {
			$comment['dbhost'] = $comment_error;
			$comment['dbuser'] = $comment_error;
			$comment['dbpw'] = $comment_error;
			$comment['dbname'] = $comment_error;
		}
		$comment['pconnect'] = '非持久链接';
		$carray['pconnect'] == 1 && $comment['pconnect'] = '持久连接';
		if ($carray['cookiedomain'] && substr($carray['cookiedomain'], 0, 1) != '.') {
			$comment['cookiedomain'] = "{$error_style_s}请以 . 开头,不然同步登录会出错{$style_e}";
		}
		(!$mysql_errno && !mysql_num_rows(mysql_query('SHOW TABLES LIKE \''.$carray['tablepre'].'posts\''))) && $comment['tablepre'] = $comment_error;
		if (!$comment['tablepre'] && !$mysql_errno && @mysql_get_server_info() > '4.1') {
			$tableinfo = loadtable('threads');
			$dzdbcharset = substr($tableinfo['subject']['Collation'], 0, strpos($tableinfo['subject']['Collation'], '_'));
			if(!$carray['dbcharset'] && in_array(strtolower($carray['charset']), array('gbk', 'big5', 'utf-8'))) {
				$ckdbcharset = str_replace('-', '', $carray['charset']);
			} else {
				$ckdbcharset = $carray['dbcharset'];
			}
			if ($dzdbcharset != $ckdbcharset && $ckdbcharset != '') {
				$carray['dbcharset'] .= $error_style_s.'出错，您的论坛数据库字符集为 '.$dzdbcharset.' ，请将本项设置成 '.$dzdbcharset.$style_e;
			}
		}
		if(!in_array($carray['charset'],array('gbk', 'big5', 'utf-8'))) {
			$carray['charset'] .= $error_style_s."  出错，目前字符集只支持'gbk', 'big5', 'utf-8'".$style_e;
		}
		if ($carray['headercharset'] == 0) {
			$comment['headercharset'] = $title_style_s.'未开启'.$title_style_e;
		} else {
			$comment['headercharset'] = $ok_style_s.'开启'.$style_e;
		}
		if ($carray['tplrefresh'] == 0) {
			$comment['tplrefresh'] = $title_style_s.'关闭'.$title_style_e;
		} else {
			$comment['tplrefresh'] = $ok_style_s.'开启'.$style_e;
		}
		if (preg_match('/[^\d,]/i', str_replace(' ', '', $carray['forumfounders']))) {
			$comment['forumfounders'] = $error_style_s.'出错：含有非法字符，该项设置只能含有数字和半角逗号 ,'.$style_e;
		} elseif(!$comment['tablepre'] && !$mysql_errno) {
			if ($carray['forumfounders']) {
				$founderarray = explode(',', str_replace(' ', '', $carray['forumfounders']));
				$adminids = $notadminids = '';
				$notadmin = 0;
				foreach($founderarray as $fdkey) {
					if (@mysql_result(@mysql_query("SELECT adminid FROM {$carray[tablepre]}members WHERE uid = '$fdkey' LIMIT 1"), 0) == 1) {
						$isadmin ++;
						$iscomma = $isadmin > 1 ? ',' : '';
						$adminids .= $iscomma.$fdkey;
					} else {
						$notadmin ++;
						$notcomma = $notadmin > 1 ? ',' : '';
						$notadminids .= $notcomma.$fdkey;
					}
				}
				if (!$isadmin) {
					$comment['forumfounders'] = $error_style_s.'出错：创始人中无管理员'.$style_e;
				} elseif ($notadmin) {
					$comment['forumfounders'] = $error_style_s.'警告：创始人中有非管理员，uid如下：'.$notadminids.$style_e;
				}
			} else {
				$comment['forumfounders'] = $error_style_s.'警告：创始人设置为空，如果管理员中有不可靠成员，将会有安全问题'.$style_e;
			}
		}
		$comment['dbreport'] = $carray['dbreport'] == 0 ? '不发送错误报告' : '发送错误报告';
		$comment['errorreport'] = $carray['errorreport'] == 1 ? '屏蔽程序错误' : '不屏蔽程序错误';
		if (preg_match('/[^\d|]/i', str_replace(' ', '', $carray['attackevasive']))) {
			$carray['attackevasive'] .= $error_style_s.'出错：含有非法字符,该项设置只能含有数字和半角逗号,'.$style_e;
		} else {
			if (preg_match('/[8]/i', $carray['attackevasive']) && @mysql_result(@mysql_query("SELECT COUNT(*) FROM {$carray[tablepre]}members")) < 1) {
				$carray['attackevasive'] .= $error_style_s.'出错：您设置了回答问题(8)，但未添加验证问题和答案 ,'.$style_e;
			}
		}
		$comment_admincp_error = "否 > {$error_style_s}警告：有安全隐患{$style_e}";
		$comment_admincp_ok = "是 > {$error_style_s}警告：有安全隐患{$style_e}";
		if ($carray['admincp[\'forcesecques\']'] == 1) {
			$comment['admincp[\'forcesecques\']'] = "{$ok_style_s}是{$style_e}";
		} else {
			$comment['admincp[\'forcesecques\']'] = $comment_admincp_error;
		}
		if ($carray['admincp[\'checkip\']'] == 0) {
			$comment['admincp[\'checkip\']'] = $comment_admincp_error;
		} else {
			$comment['admincp[\'checkip\']'] = "{$ok_style_s}是{$style_e}";
		}
		if ($carray['admincp[\'tpledit\']'] == 1) {
			$comment['admincp[\'tpledit\']'] = $comment_admincp_ok;
		} else {
			$comment['admincp[\'tpledit\']'] = "{$title_style_s}否{$title_style_e}";
		}
		if ($carray['admincp[\'runquery\']'] == 1) {
			$comment['admincp[\'runquery\']'] = $comment_admincp_ok;
		} else {
			$comment['admincp[\'runquery\']'] = "{$title_style_s}否{$title_style_e}";
		}
		if ($carray['admincp[\'dbimport\']'] == 1) {
			$comment['admincp[\'dbimport\']'] = $comment_admincp_ok;
		} else {
			$comment['admincp[\'dbimport\']'] = "{$title_style_s}否{$title_style_e}";
		}
		foreach($carray as $key => $keyfield) {
			$clang[$key] == '' && $clang[$key] = '&nbsp;';
			strpos('comma'.$comment[$key], '警告') && $comment[$key] = $comment[$key];
			strpos('comma'.$comment[$key], '出错') && $comment[$key] = $comment[$key];
			$comment[$key] == '' && $comment[$key] = "{$ok_style_s}正常{$style_e}";
			if(in_array($key, array('dbuser', 'dbpw'))) {
				$keyfield = '**隐藏**';
			}
			$keyfield == '' && $keyfield = '空';
			if(!in_array($key, array('dbhost','dbuser','dbpw','dbname'))) {
				if(in_array($key, array('pconnect', 'headercharset', 'tplrefresh', 'dbreport', 'errorreport', 'admincp[\'forcesecques\']', 'admincp[\'checkip\']', 'admincp[\'tpledit\']', 'admincp[\'runquery\']', 'admincp[\'dbimport\']'))) {
					$doctor_config .= "\n\t{$title_style_s}$key{$title_style_e} ---> $clang[$key] ---> $comment[$key]\n";
				} elseif(in_array($key, array('cookiepre', 'cookiepath', 'cookiedomain', 'charset', 'dbcharset', 'attackevasive'))) {
					$doctor_config .= "\n\t{$title_style_s}$key{$title_style_e} ---> $clang[$key] ---> $keyfield\n";
				} else {
					$doctor_config .= "\n\t{$title_style_s}$key{$title_style_e} ---> $clang[$key] ---> $keyfield ---> $comment[$key]\n";
				}
			} else {
				if(strstr($comment[$key], '出错')) {
					strstr($doctor_config_db, '正常') && $doctor_config_db = '';
					$doctor_config_db .= "{$title_style_s}$key{$title_style_e} ---> $clang[$key] ---> $comment[$key]";
				} else {
					if(empty($doctor_config_db)) {
						$doctor_config_db ="\n\t{$ok_style_s}数据库正常链接.{$style_e}";
					}
				}
			}

		}
		$doctor_config = "\n".$doctor_config_db.$doctor_config;
		//校验环境是否支持DZ/SS，查看数据库和表的字符集，敏感信息 charset,dbcharset, php,mysql,zend,php 短标记

		$msg = '';
		$curr_os = PHP_OS;

		if(!function_exists('mysql_connect')) {
			$curr_mysql = $error_style_s.'不支持'.$style_e;
			$msg .= "您的服务器不支持MySql数据库，无法安装论坛程序";
			$quit = TRUE;
		} else {
			if(@mysql_connect($dbhost, $dbuser, $dbpw)) {
				$curr_mysql =  mysql_get_server_info();
			} else {
				$curr_mysql = $ok_style_s.'支持'.$style_e;
			}
		}
			if(function_exists('mysql_connect')) {
					$authkeylink = @mysql_connect($dbhost, $dbuser, $dbpw);
					mysql_select_db($dbname, $authkeylink);
					$authkeyresult = mysql_result(mysql_query("SELECT `value` FROM {$tablepre}settings WHERE `variable`='authkey'", $authkeylink), 0);
					if($authkeyresult) {
							$authkeyexist = $ok_style_s.'存在'.$style_e;
					} else {
							$authkeyexist = $error_style_s.'不存在'.$style_e;
					}
			}
		$curr_php_version = PHP_VERSION;
		if($curr_php_version < '4.0.6') {
			$msg .= "您的 PHP 版本小于 4.0.6, 无法使用 Discuz! / SuperSite。";
		}

		if(ini_get('allow_url_fopen')) {
			$allow_url_fopen = $ok_style_s.'允许'.$style_e;
		} else {
			$allow_url_fopen = $title_style_s.'不允许'.$title_style_e;
		}

		$max_execution_time = get_cfg_var('max_execution_time');
		$max_execution_time == 0 && $max_execution_time = '不限制';

		$memory_limit = get_cfg_var('memory_limit');

		$curr_server_software = $_SERVER['SERVER_SOFTWARE'];

		if(function_exists('ini_get')) {
			if(!@ini_get('short_open_tag')) {
				$curr_short_tag = $title_style_s.'不允许'.$title_style_e;
				$msg .='请将 php.ini 中的 short_open_tag 设置为 On，否则无法使用论坛。';
			} else {
				$curr_short_tag = $ok_style_s.'允许'.$style_e;
			}

			if(@ini_get(file_uploads)) {
				$max_size = @ini_get(upload_max_filesize);
				$curr_upload_status = '您可以上传附件的最大尺寸: '.$max_size;
			} else {
				$msg .= "附件上传或相关操作被服务器禁止。";
			}
		} else {
			$msg .= 'php.ini中禁用了ini_get()函数.部分环境参数无法检测.';
		}

		if(!defined('OPTIMIZER_VERSION')) define('OPTIMIZER_VERSION','没有安装或版本较低');
		if(OPTIMIZER_VERSION < 3.0) {
			$msg .="您的ZEND版本低于3.0,将无法使用SuperSite.";
		}
			//临时目录的检查
			if(@is_writable(@ini_get('upload_tmp_dir'))){
					$tmpwritable = $ok_style_s.'可写'.$style_e;
			} elseif(!@ini_get('upload_tmp_dir') & @is_writable($_ENV[TEMP])) {
					$tmpwritable = $ok_style_s.'可写'.$style_e;
			} else {
					$tmpwritable = $title_style_s.'不可写'.$title_style_e;
			}

		if(@ini_get('safe_mode') == 1) {
			$curr_safe_mode = $ok_style_s.'开启'.$style_e;
		} else {
			$curr_safe_mode = $title_style_s.'关闭'.$title_style_e;
		}
		if(@diskfreespace('.')) {
			$curr_disk_space = intval(diskfreespace('.') / (1024 * 1024)).'M';
		} else {
			$curr_disk_space = '无法检测';
		}
		if(function_exists('xml_parser_create')) {
			$curr_xml = $ok_style_s.'可用'.$style_e;
		} else {
			$curr_xml = $title_style_s.'不可用'.$title_style_e;
		}

		if(function_exists('file')) {
				$funcexistfile = $ok_style_s.'存在'.$style_e;
		} else {
				$funcexistfile = $title_style_s.'不存在'.$title_style_e;
		}

		if(function_exists('fopen')) {
				$funcexistfopen = $ok_style_s.'存在'.$style_e;
		} else {
				$funcexistfopen = $title_style_s.'不存在'.$title_style_e;
		}

		if(@ini_get('display_errors')) {
			$curr_display_errors = $ok_style_s.'开启'.$style_e;
		} else {
			$curr_display_errors = $title_style_s.'关闭'.$title_style_e;
		}
		if(!function_exists('ini_get')) {
			$curr_display_errors = $tmpwritable = $curr_safe_mode = $curr_upload_status = $curr_short_tag = '无法检测';
		}
		//目录权限检查
		$envlogs = array();
		$entryarray = array (
			'attachments',
			'forumdata',
			'forumdata/threadcaches',
			'forumdata/logs',
			'forumdata/templates',
			'forumdata/cache',
			'customavatars',
			'forumdata/viewcount.log',
			'forumdata/dberror.log',
			'forumdata/errorlog.php',
			'forumdata/ratelog.php',
			'forumdata/cplog.php',
			'forumdata/modslog.php',
			'forumdata/illegallog.php'
		);

		foreach(array('templates', 'forumdata/logs', 'forumdata/cache', 'forumdata/templates') as $directory) {
			getdirentry($directory);
		}
		$fault = 0;
		foreach($entryarray as $entry) {
			$fullentry = './'.$entry;
			if(!is_dir($fullentry) && !file_exists($fullentry)) {
				continue;
			} else {
				if(!is_writeable($fullentry)) {
					$dir_perm .= "\n\t\t".(is_dir($fullentry) ? '目录' : '文件')." ./$entry {$error_style_s}无法写入.{$style_e}";
					$msg .= "\n\t\t".(is_dir($fullentry) ? '目录' : '文件')." ./$entry {$error_style_s}无法写入.{$style_e}";
					$fault = 1;
				}
			}
		}
		$dir_perm .= $fault ? '' : $ok_style_s.'文件及目录属性全部正确'.$style_e;

		/**
		 * gd库所需函数的检查
		 */
		$gd_check = '';
		if(!extension_loaded('gd')) {
			$gd_check .= '您的php.ini未开启extension=php_gd2.dll(windows)或者未编译gd库(linux).';
		} elseif(!function_exists('gd_info') && phpversion() < '4.3') {
			$gd_check .= 'php版本低于4.3.0，不支持高版本的gd库，请升级您的php版本.';
		} else {
			$ver_info = gd_info();
			preg_match('/([0-9\.]+)/', $ver_info['GD Version'], $match);
			if($match[0] < '2.0') {
				$gd_check .= "\n\t\tgd版本低于2.0,请升级您的gd版本以支持gd的验证码和水印.";
			} elseif(!(function_exists('imagecreatefromjpeg') && function_exists('imagejpeg')) ) {
				$gd_check .= "\n\t\tgd版本不支持jpeg的验证码和水印.";
			} elseif(!(function_exists('imagecreatefromgif') && function_exists('imagegif')) ) {
				$gd_check .= "\n\t\tgd版本不支持gif的验证码和水印.";
			} elseif(!(function_exists('imagecreatefrompng') && function_exists('imagepng')) ) {
				$gd_check .= "\n\t\tgd版本不支持png的验证码和水印.";
			} else {
				$gd_check .= '正常开启';
			}
		}
		if($gd_check != '正常开启') {
			$gd_check = $error_style_s.$gd_check.$style_e;
		} else {
			$gd_check = $ok_style_s.$gd_check.$style_e;
		}

		/**
		 * 检查ming库，目的为检查是否支持flash验证码
		 */
		 $ming_check = '';
		if(extension_loaded('ming')) {
			if(substr($curr_os,0,3) == 'WIN') {
				$ming_check .= '您的php.ini未开启extension=php_ming.dll，所以无法支持flash验证码';
			} else {
				$ming_check .= '您未编译ming库，所以无法支持flash验证码';
			}
		} else {
			$ming_check .= '您的系统支持flash验证码，如果还无法使用flash验证码的话，有可能是您的php版本太低';
		}

		/**
		 *检查系统是否可以执行ImageMagick的命令
		 */
		 $imagemagick_check = '';
		if(!function_exists('exec')) {
			$imagemagick_check .='您的php.ini里或者空间商禁止了使用exec函数，无法使用ImageMagick';
		} else {
			$imagemagick_check .='您现在只需安装好ImageMagick，然后配置好相关参数就可以使用ImageMagick(使用之前请先使用后台的预览功能来检查您的ImageMagick是否安装好)';
		}
		if($msg == '') {
			$msg = "{$ok_style_s}没有发现系统环境问题.{$style_e}";
		} else {
			$msg = $error_style_s.$msg.$style_e;
		}
			$doctor_env = "
	操作系统--->$curr_os

	WEB 引擎 --->$curr_server_software

	PHP 版本--->$curr_php_version

	MySQL 版本--->$curr_mysql

	Zend 版本--->".OPTIMIZER_VERSION."

	程序最长运行时间(max_execution_time)--->{$max_execution_time}秒

	内存大小(memory_limit)--->$memory_limit

	是否允许打开远程文件(allow_url_fopen)--->$allow_url_fopen

	是否允许使用短标记(short_open_tag)--->$curr_short_tag

	安全模式(safe_mode)--->$curr_safe_mode

	错误提示(display_errors)--->$curr_display_errors

	XML 解析器--->$curr_xml

	authkey 是否存在--->$authkeyexist

	系统临时目录--->$tmpwritable

	磁盘空间--->$curr_disk_space

	附件上传--->$curr_upload_status

	函数 file()--->$funcexistfile

	函数 fopen()--->$funcexistfopen

	目录权限---$dir_perm

	GD 库--->$gd_check

	ming 库--->$ming_check

	ImageMagick --->$imagemagick_check

	系统环境错误提示\r\n\t$msg";
	}
	if(!$doctor_step) {
		$doctor_step = '0';
		@unlink('./forumdata/doctor_cache.cache');
	}
	//php错误检查
				$dberrnomsg = array (
					'1008' => '数据库不存在，删除数据库失败',
					'1016' => '无法打开数据文件',
					'1041' => '系统内存不足',
					'1045' => '连接数据库失败，用户名或密码错误',
					'1046' => '选择数据库失败，请正确配置数据库名称',
					'1044' => '当前用户没有访问数据库的权限',
					'1048' => '字段不能为空',
					'1049' => '数据库不存在',
					'1051' => '数据表不存在',
					'1054' => '字段不存在',
					'1062' => '字段值重复，入库失败',//不中断
					'1064' => '可能原因：1.数据超长或类型不匹配；2.数据库记录重复',//不中断
					'1065' => '无效的SQL语句，SQL语句为空',//不中断
					'1081' => '不能建立Socket连接',
					'1129' => '数据库出现异常，请重启数据库',
					'1130' => '连接数据库失败，没有连接数据库的权限',
					'1133' => '数据库用户不存在',
					'1141' => '当前用户无权访问数据库',
					'1142' => '当前用户无权访问数据表',
					'1143' => '当前用户无权访问数据表中的字段',
					'1146' => '数据表不存在',
					'1149' => 'SQL语句语法错误',
					'1169' => '字段值重复，更新记录失败',//不中断
					'2003' => '请检查数据库服务器端口设置是否正确，默认端口为 3306',
					'2005' => '数据库服务器不存在',
					'1114' => 'Forum onlines reached the upper limit',
				);

	$display_errorall = '';
	$tempdir = $phpfile_array[$doctor_step];
	$dirname = $dir_array[$doctor_step];
	//foreach($phpfile_array as $tempdir=>$dirname) {
		$display_error = '';
		$mtime = explode(' ', microtime());
		$time_start = $mtime[1] + $mtime[0];
		if(!in_array($tempdir, array('templates', 'cache', 'discuzroot'))) exit('参数错误');

		$tempdir == 'discuzroot' ?  $dir = './' : $dir = 'forumdata/'.$tempdir.'/';
		create_checkfile();
		if (is_dir($dir)) {
		   if ($dh = dir($dir)) {
			   $PHP_SELF = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
				$BASESCRIPT = basename($PHP_SELF);
				$host = htmlspecialchars($_SERVER['HTTP_HOST']);
				$boardurl = preg_replace("/\/+(api|archiver|wap)?\/*$/i", '', substr($PHP_SELF, 0, strrpos($PHP_SELF, '/'))).'/';
			   while (($file = $dh->read()) !== false) {
				   if ($file != '.' && $file != '..' && $file != 'index.htm' && $file != 'checkfile.php' && $file != 'tools.php' && !is_dir($file)) {
					   $extnum	=	strrpos($file, '.') + 1;
					   $exts	=	strtolower(substr($file, $extnum));
					   if($exts == 'php') {
						   $content = '';
						   if($dir == './') {
								$content = http_fopen($host, "{$boardurl}{$file}");
						   } else {
								$content = http_fopen($host, "{$boardurl}/forumdata/checkfile.php?file=$file");
						   }
						   $content = str_replace(':  Call to undefined function:  ','',$content);
						   $content = str_replace(':  Call to undefined function  ','',$content);
						   $out = $out_mysql = array();
						   if(preg_match_all("/<b>.+<\/b>:.* on line <b>\d+<\/b>/",$content,$out) || preg_match_all("/<b>Error<\/b>:.+<br \/>\n<b>Errno.<\/b>:\s{2}([1-9][0-9]+)/",$content,$out_mysql)) {
								$display_error .= "\t{$error_style_s}$file ---错误:{$style_e}";
								foreach ($out[0] as $value) {
									$display_error .= "\n\t\t".$value."\n";
								}
								foreach ($out_mysql[0] as $key =>$value) {
									$display_error .= "\n\t\t{$error_style_s}".$dberrnomsg[$out_mysql[1][$key]].$style_e;
									$display_error .= "\n\t\t".str_replace("\n", '', $value);
								}
						   }
					   }
				   }
			   }
			   $dh->close();
		   } else {
				echo "$dir目录不存在或不可读取.";
		   }
		}
		@unlink('./forumdata/checkfile.php');
		if($display_error == '') {
			$dot = '缓存文件';
			$dir == './' && $dot = 'php文件';
			$display_errorall .= "\n---------{$ok_style_s}{$dirname}{$style_e}下没有检测到有错误的$dot.\n";
		} else {
			$display_errorall .= "\n---------{$error_style_s}{$dirname}{$style_e}\n".$display_error;
		}
	$fp = @fopen('./forumdata/doctor_cache.cache', 'ab');
	@fwrite($fp, $display_errorall);
	@fclose($fp);
	if($doctor_step < $doctor_top) {
		$doctor_step ++;
		continue_redirect('doctor', "&doctor_step=$doctor_step");
		htmlfooter();
	}
	$fp = @fopen('./forumdata/doctor_cache.cache','rb');
	$display_errorall = @fread($fp, @filesize('./forumdata/doctor_cache.cache'));
	@fclose($fp);
	@unlink('./forumdata/doctor_cache.cache');
	//}
	$display_errorall = str_replace('<b>', '', $display_errorall);
	$display_errorall = str_replace('</b>', '', $display_errorall);
	$display_errorall = str_replace('<br />', '', $display_errorall);
	$records_style = "\n\n==={$title_style_s}配置文件检查{$title_style_e}=================================================$doctor_config\n==={$title_style_s}系统环境检查{$title_style_e}=================================================\n$doctor_env\n==={$title_style_s}文件错误检查{$title_style_e}=================================================\n$display_errorall\n==={$title_style_s}检查完毕{$title_style_e}=====================================================";
	$search_style_all = array($error_style_s, $style_e, $ok_style_s, $title_style_s, $title_style_e);
	$replace_style_all = array('', '', '', '', '');
	$records = str_replace($search_style_all, '', $records_style);
	echo "<h4>论坛医生诊断结果</h4><br /><p id=records style=\"display:\"><textarea name=\"contents\" readonly=\"readonly\">$records</textarea><br><br><input value=\"论坛样式代码\" onclick=\"records.style.display='none';records_style.style.display='';\"  type=\"button\">  <input value=\"将代码复制到我的剪切板\" onclick=\"copytoclip($('contents'))\" type=\"button\"></p>
	<p id=records_style style=\"display:none\"><textarea name=\"contents_style\" readonly=\"readonly\">$records_style</textarea><br><br><input value=\"清除样式代码\" onclick=\"records_style.style.display='none';records.style.display='';\"  type=\"button\"> <input value=\"将代码复制到我的剪切板\" onclick=\"copytoclip($('contents_style'))\" type=\"button\"></p>
	";
	htmlfooter();
} elseif ($action == 'filecheck') {
	if(!file_exists("./config.inc.php") && !file_exists("config.php")){
		htmlheader();
		cexit("<h4>请先上传config文件以保证您的数据库能正常链接！</h4>");
	}
	require_once './include/common.inc.php';

	@set_time_limit(0);

	$do = isset($do) ? $do : 'advance';

	$lang = array(
		'filecheck_fullcheck' => '搜索未知文件',
		'filecheck_fullcheck_select' => '搜索未知文件 - 选择需要搜索的目录',
		'filecheck_fullcheck_selectall' => '[搜索全部目录]',
		'filecheck_fullcheck_start' => '开始时间:',
		'filecheck_fullcheck_current' => '当前时间:',
		'filecheck_fullcheck_end' => '结束时间:',
		'filecheck_fullcheck_file' => '当前文件:',
		'filecheck_fullcheck_foundfile' => '发现未知文件数: ',
		'filecheck_fullcheck_nofound' => '没有发现任何未知文件'
	);

	if(!$discuzfiles = @file('admin/discuzfiles.md5')) {
		cpmsg('filecheck_nofound_md5file');
	}
	htmlheader();
	if($do == 'advance') {
		$dirlist = array();
		$starttime = date('Y-m-d H:i:s');
		$cachelist = $templatelist = array();
		if(empty($checkdir)) {
			checkdirs('./');
		} elseif($checkdir == 'all') {
			echo "\n<script>var dirlist = ['./'];var runcount = 0;var foundfile = 0</script>";
		} else {
			$checkdir = str_replace('..', '', $checkdir);
			$checkdir = $checkdir{0} == '/' ? '.'.$checkdir : $checkdir;
			checkdirs($checkdir.'/');
			echo "\n<script>var dirlist = ['$checkdir/'];var runcount = 0;var foundfile = 0</script>";
		}

		echo '<h4>搜索未知文件</h4>
			<table>
			<tr><th class="specialtd">'.(empty($checkdir) ? '<a href="tools.php?action=filecheck&do=advance&start=yes&checkdir=all">'.$lang['filecheck_fullcheck_selectall'].'</a>' : $lang['filecheck_fullcheck'].($checkdir != 'all' ? ' - '.$checkdir : '')).'</th></tr>
			<script language="JavaScript" src="include/javascript/common.js"></script>';
		if(empty($checkdir)) {
			echo '<tr><td class="specialtd"><br><ul>';
			foreach($dirlist as $dir) {
				$subcount = count(explode('/', $dir));
				echo '<li>'.str_repeat('-', ($subcount - 2) * 4);
				echo '<a href="tools.php?action=filecheck&do=advance&start=yes&checkdir='.rawurlencode($dir).'">'.basename($dir).'</a></li>';
			}
			echo '</ul></td></tr></table><br />';
		} else {
			echo '<tr><td>'.$lang['filecheck_fullcheck_start'].' '.$starttime.'<br><span id="msg"></span><br /><br /><div id="checkresult"></div></td></tr></table><br />
				<iframe name="checkiframe" id="checkiframe" style="display: none"></iframe>';
			echo "<script>checkiframe.location = 'tools.php?action=filecheck&do=advancenext&start=yes&dir=' + dirlist[runcount];</script>";
		}
		htmlfooter();
	} elseif($do == 'advancenext') {
		$nopass = 0;
		foreach($discuzfiles as $line) {
			$md5files[] = trim(substr($line, 34));
		}
		$foundfile = checkfullfiles($dir);

		echo "<script>";
		if($foundfile) {
			echo "parent.foundfile += $foundfile;";
		}
		echo "parent.runcount++;
		if(parent.dirlist.length > parent.runcount) {
			parent.checkiframe.location = 'tools.php?action=filecheck&do=advancenext&start=yes&dir=' + parent.dirlist[parent.runcount];
		} else {
			var msg = '';
			msg = '$lang[filecheck_fullcheck_end] ".addslashes(date('Y-m-d H:i:s'))."';
			if(parent.foundfile) {
				msg += '<br>$lang[filecheck_fullcheck_foundfile] ' + parent.foundfile;
			} else {
				msg += '<br>$lang[filecheck_fullcheck_nofound]';
			}
			parent.$('msg').innerHTML = msg;
		}</script>";
		exit;
	}
} elseif ($action == 'logout') {
	setcookie('toolpassword', '', -86400 * 365);
	errorpage("<h6>您已成功退出,欢迎下次使用.强烈建议您在不使用时删除此文件.</h6>");
} elseif ($action == 'mysqlclear') {
	ob_implicit_flush();

	define('IN_DISCUZ', TRUE);
	if(@!include("./config.inc.php")) {
		if(@!include("./config.php")) {
			htmlheader();
			cexit("<h4>请先上传config文件以保证您的数据库能正常链接！</h4>");
		}
	}
	require './include/db_'.$database.'.class.php';

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);

	if(!get_cfg_var('register_globals')) {
		@extract($_GET, EXTR_SKIP);
	}

	$rpp			=	"1000"; //每次处理多少条数据
	$totalrows		=	isset($totalrows) ? $totalrows : 0;
	$convertedrows		=	isset($convertedrows) ? $convertedrows : 0;
	$start			=	isset($start) && $start > 0 ? $start : 0;
	$sqlstart		=	isset($start) && $start > $convertedrows ? $start - $convertedrows : 0;
	$end			=	$start + $rpp - 1;
	$stay			=	isset($stay) ? $stay : 0;
	$converted		=	0;
	$step			=	isset($step) ? $step : 0;
	$info			=	isset($info) ? $info : '';
	$action			=	array(
						'1'=>'冗余回复数据清理',
						'2'=>'冗余附件数据清理',
						'3'=>'冗余会员数据清理',
						'4'=>'冗余板块数据清理',
						'5'=>'冗余短信数据清理',
						'6'=>'主题信息清理',
						'7'=>'完成数据冗余清理'
					);
	$steps			=	count($action);
	$actionnow		=	isset($action[$step]) ? $action[$step] : '结束';
	$maxid			=	isset($maxid) ? $maxid : 0;
	$tableid		=	isset($tableid) ? $tableid : 1;

	htmlheader();
	if($step==0){
	?>
		<h4>数据库冗余数据清理</h4>
		<h5>清理项目详细信息</h5>
		<table>
		<tr><th width="30%">Posts表的清理</th><td>[<a href="?action=mysqlclear&step=1&stay=1">单步清理</a>]</td></tr>
		<tr><th width="30%">Attachments表的清理</th><td>[<a href="?action=mysqlclear&step=2&stay=1">单步清理</a>]</td></tr>
		<tr><th width="30%">Members表的清理</th><td>[<a href="?action=mysqlclear&step=3&stay=1">单步清理</a>]</td></tr>
		<tr><th width="30%">Forums表的清理</th><td>[<a href="?action=mysqlclear&step=4&stay=1">单步清理</a>]</td></tr>
		<tr><th width="30%">Pms表的清理</th><td>[<a href="?action=mysqlclear&step=5&stay=1">单步清理</a>]</td></tr>
		<tr><th width="30%">Threads表的清理</th><td>[<a href="?action=mysqlclear&step=6&stay=1">单步清理</a>]</td></tr>
		<tr><th width="30%">所有表的清理</th><td>[<a href="?action=mysqlclear&step=1&stay=0">全部清理</a>]</td></tr>
		</table>
	<?php
	specialdiv();
	} elseif ($step == '1'){
		if($start == 0) {
			validid('pid','posts');
		}
		$query = "SELECT pid, tid FROM {$tablepre}posts WHERE pid >= $start AND pid <= $end";
		$posts=$db->query($query);
			while ($post = $db->fetch_array($posts)){
				$query = $db->query("SELECT tid FROM {$tablepre}threads WHERE tid='".$post['tid']."'");
				if ($db->result($query, 0)) {
					} else {
						$convertedrows ++;
						$db->query("DELETE FROM {$tablepre}posts WHERE pid='".$post['pid']."'");
					}
				$converted = 1;
				$totalrows ++;
		}
			if($converted || $end < $maxid) {
				continue_redirect();
			} else {
				stay_redirect();
			}

	} elseif ($step == '2'){
		if($start == 0) {
			validid('aid','attachments');
		}
		$query = "SELECT aid,pid,attachment FROM {$tablepre}attachments WHERE aid >= $start AND aid <= $end";
		$posts=$db->query($query);
			while ($post = $db->fetch_array($posts)){
				$query = $db->query("SELECT pid FROM {$tablepre}posts WHERE pid='".$post['pid']."'");
				if ($db->result($query, 0)) {
					} else {
						$convertedrows ++;
						$db->query("DELETE FROM {$tablepre}attachments WHERE aid='".$post['aid']."'");
						$attachmentdir = DISCUZ_ROOT.'./attachments/';
						@unlink($attachmentdir.$post['attachment']);
					}
				$converted = 1;
				$totalrows ++;
		}
			if($converted || $end < $maxid) {
				continue_redirect();
			} else {
				stay_redirect();
			}

	} elseif ($step == '3'){
		if($start == 0) {
			validid('uid','memberfields');
		}
		$query = "SELECT uid FROM {$tablepre}memberfields WHERE uid >= $start AND uid <= $end";
		$posts=$db->query($query);
			while ($post = $db->fetch_array($posts)){
				$query = $db->query("SELECT uid FROM {$tablepre}members WHERE uid='".$post['uid']."'");
					if ($db->result($query, 0)) {
					} else {
						$convertedrows ++;
						$db->query("DELETE FROM {$tablepre}memberfields WHERE uid='".$post['uid']."'");
					}
				$converted = 1;
				$totalrows ++;
		}
			if($converted || $end < $maxid) {
				continue_redirect();
			} else {
				stay_redirect();
			}

	} elseif ($step == '4'){
		if($start == 0) {
			validid('fid','forumfields');
		}
		$query = "SELECT fid FROM {$tablepre}forumfields WHERE fid >= $start AND fid <= $end";
		$posts=$db->query($query);
			while ($post = $db->fetch_array($posts)){
				$query = $db->query("SELECT fid FROM {$tablepre}forums WHERE fid='".$post['fid']."'");
				if ($db->result($query, 0)) {
					} else {
						$convertedrows ++;
						$db->query("DELETE FROM {$tablepre}forumfields WHERE fid='".$post['fid']."'");
					}
				$converted = 1;
				$totalrows ++;
		}
			if($converted || $end < $maxid) {
				continue_redirect();
			} else {
				stay_redirect();
			}

	} elseif ($step == '5'){
		if($start == 0) {
			validid('pmid','pms');
		}
		$query = "SELECT msgtoid FROM {$tablepre}pms WHERE pmid >= $start AND pmid <= $end";
		$posts=$db->query($query);
			while ($post = $db->fetch_array($posts)){
				$query = $db->query("SELECT uid FROM {$tablepre}members WHERE uid='".$post['msgtoid']."'");
				if ($db->result($query, 0)) {
					} else {
						$convertedrows ++;
						$db->query("DELETE FROM {$tablepre}pms WHERE msgtoid='".$post['msgtoid']."'");
					}
				$converted = 1;
				$totalrows ++;
		}
			if($converted || $end < $maxid) {
				continue_redirect();
			} else {
				stay_redirect();
			}

	} elseif ($step == '6'){
		if($start == 0) {
			validid('tid','threads');
		}
		$query = "SELECT tid FROM {$tablepre}threads WHERE tid >= $start AND tid <= $end";
		$posts=$db->query($query);
			while ($threads = $db->fetch_array($posts)){
				$query = $db->query("SELECT COUNT(*) FROM {$tablepre}posts WHERE tid='".$threads['tid']."' AND invisible='0'");
				$replynum = $db->result($query, 0) - 1;
				if ($replynum < 0) {
					$db->query("DELETE FROM {$tablepre}threads WHERE tid='".$threads['tid']."'");
				} else {
					$query = $db->query("SELECT a.aid FROM {$tablepre}posts p, {$tablepre}attachments a WHERE a.tid='".$threads['tid']."' AND a.pid=p.pid AND p.invisible='0' LIMIT 1");
					$attachment = $db->num_rows($query) ? 1 : 0;//修复附件
					$query  = $db->query("SELECT pid, subject, rate FROM {$tablepre}posts WHERE tid='".$threads['tid']."' AND invisible='0' ORDER BY dateline LIMIT 1");
					$firstpost = $db->fetch_array($query);
					$firstpost['subject'] = addslashes($firstpost['subject']);
					@$firstpost['rate'] = $firstpost['rate'] / abs($firstpost['rate']);//修复发帖
					$query  = $db->query("SELECT author, dateline FROM {$tablepre}posts WHERE tid='".$threads['tid']."' AND invisible='0' ORDER BY dateline DESC LIMIT 1");
					$lastpost = $db->fetch_array($query);//修复最后发帖
					$db->query("UPDATE {$tablepre}threads SET subject='".$firstpost['subject']."', replies='$replynum', lastpost='".$lastpost['dateline']."', lastposter='".addslashes($lastpost['author'])."', rate='".$firstpost['rate']."', attachment='$attachment' WHERE tid='".$threads['tid']."'", 'UNBUFFERED');
					$db->query("UPDATE {$tablepre}posts SET first='1', subject='".$firstpost['subject']."' WHERE pid='".$firstpost['pid']."'", 'UNBUFFERED');
					$db->query("UPDATE {$tablepre}posts SET first='0' WHERE tid='".$threads['tid']."' AND pid<>'".$firstpost['pid']."'", 'UNBUFFERED');
				}
				$converted = 1;
				$totalrows ++;
			}
			if($converted || $end < $maxid) {
				continue_redirect();
			} else {
				stay_redirect();
			}

	} elseif ($step=='7'){
		echo '<h4>数据库冗余数据清理</h4><table>
			  <tr><th>完成冗余数据清理</th></tr><tr>
			  <td><br>所有数据清理操作完毕.&nbsp;共处理<font color=red>'.$allconvertedrows.'</font>条数据.<br><br></td></tr></table>';

	}
	htmlfooter();
} elseif ($action == 'repair_auto') {
	if(@!include("./config.inc.php")) {
		if(@!include("./config.php")) {
			htmlheader();
			cexit("<h4>请先上传config文件以保证您的数据库能正常链接！</h4>");
		}
	}
	htmlheader();
	echo '<h4>Discuz! 自增长字段修复 </h4>';
	mysql_connect($dbhost, $dbuser, $dbpw);
	mysql_select_db($dbname);
	@set_time_limit(0);
	$querysql = array(
		'activityapplies' => 'applyid',
		'adminnotes' => 'id',
		'advertisements' => 'advid',
		'announcements' => 'id',
		'attachments' => 'aid',
		'attachtypes' => 'id',
		'banned' => 'id',
		'bbcodes' => 'id',
		'crons' => 'cronid',
		'faqs' => 'id',
		'forumlinks' => 'id',
		'forums' => 'fid',
		'itempool' => 'id',
		'magicmarket' => 'mid',
		'magics' => 'magicid',
		'medals' => 'medalid',
		'members' => 'uid',
		'pluginhooks' => 'pluginhookid',
		'plugins' => 'pluginid',
		'pluginvars' => 'pluginvarid',
		'pms' => 'pmid',
		'pmsearchindex' => 'searchid',
		'polloptions' => 'polloptionid',
		'posts' => 'pid',
		'profilefields' => 'fieldid',
		'projects' => 'id',
		'ranks' => 'rankid',
		'searchindex' => 'searchid',
		'smilies' => 'id',
		'styles' => 'styleid',
		'stylevars' => 'stylevarid',
		'templates' => 'templateid',
		'threads' => 'tid',
		'threadtypes' => 'typeid',
		'words' => 'id'
	);

	$sqladd = array(
		'imagetypes' => 'typeid',
		'tradecomments' => 'id',
		'typemodels' => 'id',
		'typeoptions' => 'optionid'
	);
	define('IN_DISCUZ', TRUE);
	if(@include DISCUZ_ROOT.'./discuz_version.php') {
		if(substr(DISCUZ_VERSION, 0, 1) == 6) {
			$querysql = array_merge($querysql, $sqladd);
		}else if(substr(DISCUZ_VERSION, 0, 3) != '5.5') {
			errorpage("<h4>很抱歉，该功能目前只支持Discuz!5.5版本和Discuz!6.0版本。</h4>",'',0);
		}
	}else {
		errorpage("./discuz_version.php文件不存在，请确定该文件的存在。",'',0);
	}

	echo '<h5>检查结果</h5>
	<table>
		<tr><th width="25%">数据表名</th><th width="25%">字段名</th><th width="25%">自增长状态</th></tr>';
	foreach($querysql as $key => $keyfield) {
		$tablestate = '正常';
		echo '<tr><td width="25%">'.$tablepre.$key.'</td><td width="25%">'.$keyfield.'</td>';
		if($query = @mysql_query("Describe $tablepre$key $keyfield")) {
			if(@mysql_num_rows($query) > 0) {
				$field = @mysql_fetch_array($query);
				if($field[3] != 'PRI') {
					@mysql_query("ALTER TABLE $tablepre$key ADD PRIMARY KEY ($keyfield)");
					$tablestate = '<font color="green"><b>已经修复</b></font>';
				}
				if(empty($field[5])) {
					mysql_query("ALTER TABLE $tablepre$key CHANGE $keyfield $keyfield $field[1] NOT NULL AUTO_INCREMENT");
					$tablestate = '<font color="green"><b>已经修复</b></font>';
				}
			} else {
				$tablestate = '<font color="red">字段不存在</font>';
			}
		} else {
			$tablestate = '<font color="red">表不存在</font>';
		}
		echo '<td width="25%">'.$tablestate.'</td></tr>';
	}
	echo '</table>';
	specialdiv();
	echo '<br />';
	htmlfooter();
} elseif ($action == 'restore') {
	ob_implicit_flush();
	define('IN_DISCUZ', TRUE);
	if(@(!include("./config.inc.php")) || @(!include('./include/db_'.$database.'.class.php'))) {
		if(@(!include("./config.php")) || @(!include('./include/db_'.$database.'.class.php'))) {
			htmlheader();
			cexit("<h4>请先上传所有新版本的程序文件后再运行本升级程序！</h4>");
		}
	}
	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);

	if(!get_cfg_var('register_globals')) {
		@extract($HTTP_GET_VARS);
	}
	$sqldump = '';
	htmlheader();
	?>
	<h4>数据库恢复实用工具 </h4>

	<?php
	echo "<div class=\"specialdiv\">操作提示：<ul>
		<li>只能恢复存放在服务器(远程或本地)上的数据文件,如果您的数据不在服务器上,请用 FTP 上传</li>
		<li>数据文件必须为 Discuz! 导出格式,并设置相应属性使 PHP 能够读取</li>
		<li>请尽量选择服务器空闲时段操作,以避免超时.如程序长久(超过 10 分钟)不反应,请刷新</li></ul></div>";

	if($file) {
		if(strtolower(substr($file, 0, 7)) == "http://") {
			echo "从远程数据库恢复数据 - 读取远程数据:<br><br>";
			echo "从远程服务器读取文件 ... ";

			$sqldump = @fread($fp, 99999999);
			@fclose($fp);
			if($sqldump) {
				echo "成功<br><br>";
			} elseif (!$multivol) {
				cexit("失败<br><br><b>无法恢复数据</b>");
			}
		} else {
			echo "<div class=\"specialtext\">从本地恢复数据 - 检查数据文件:<br><br>";
			if(file_exists($file)) {
				echo "数据文件 $file 存在检查 ... 成功<br><br>";
			} elseif (!$multivol) {
				cexit("数据文件 $file 存在检查 ... 失败<br><br><br><b>无法恢复数据</b></div>");
			}

			if(is_readable($file)) {
				echo "数据文件 $file 可读检查 ... 成功<br><br>";
				@$fp = fopen($file, "r");
				@flock($fp, 3);
				$sqldump = @fread($fp, filesize($file));
				@fclose($fp);
				echo "从本地读取数据 ... 成功<br><br>";
			} elseif (!$multivol) {
				cexit("数据文件 $file 可读检查 ... 失败<br><br><br><b>无法恢复数据</b></div>");
			}
		}

		if($multivol && !$sqldump) {
			cexit("分卷备份范围检查 ... 成功<br><br><b>恭喜您,数据已经全部成功恢复!安全起见,请务必删除本程序.</b></div>");
		}

		echo "数据文件 $file 格式检查 ... ";
		@list(,,,$method, $volume) = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", preg_replace("/^(.+)/", "\\1", substr($sqldump, 0, 256)))));
		if($method == 'multivol' && is_numeric($volume)) {
			echo "成功<br><br>";
		} else {
			cexit("失败<br><br><b>数据非 Discuz! 分卷备份格式,无法恢复</b></div>");
		}

		if($onlysave == "yes") {
			echo "将数据文件保存到本地服务器 ... ";
			$filename = DISCUZ_ROOT.'./forumdata'.strrchr($file, "/");
			@$filehandle = fopen($filename, "w");
			@flock($filehandle, 3);
			if(@fwrite($filehandle, $sqldump)) {
				@fclose($filehandle);
				echo "成功<br><br>";
			} else {
				@fclose($filehandle);
				die("失败<br><br><b>无法保存数据</b>");
			}
			echo "成功<br><br><b>恭喜您,数据已经成功保存到本地服务器 <a href=\"".strstr($filename, "/")."\">$filename</a>.安全起见,请务必删除本程序.</b></div>";
		} else {
			$sqlquery = splitsql($sqldump);
			echo "拆分操作语句 ... 成功<br><br>";
			unset($sqldump);

			echo "正在恢复数据,请等待 ... </div>";
			foreach($sqlquery as $sql) {
				$sql = syntablestruct(trim($sql), $db->version() > '4.1', $dbcharset);
				if(trim($sql)) {
					@$db->query($sql);
				}
			}
		if($auto == 'off'){
			$nextfile = str_replace("-$volume.sql", '-'.($volume + 1).'.sql', $file);
			cexit("<ul><li>数据文件 <b>$volume#</b> 恢复成功,如果有需要请继续恢复其他卷数据文件</li><li>请点击<b><a href=\"?action=restore&file=$nextfile&multivol=yes\">全部恢复</a></b>	或许单独恢复下一个数据文件<b><a href=\"?action=restore&file=$nextfile&multivol=yes&auto=off\">单独恢复下一数据文件</a></b></li></ul>");
		} else {
			$nextfile = str_replace("-$volume.sql", '-'.($volume + 1).'.sql', $file);
			echo "<ul><li>数据文件 <b>$volume#</b> 恢复成功,现在将自动导入其他分卷备份数据.</li><li><b>请勿关闭浏览器或中断本程序运行</b></li></ul>";
			redirect("?action=restore&file=$nextfile&multivol=yes");
		}
		}
	} else {
			$exportlog = array();
			if(is_dir(DISCUZ_ROOT.'./forumdata')) {
				$dir = dir(DISCUZ_ROOT.'./forumdata');
				while($entry = $dir->read()) {
					$entry = "./forumdata/$entry";
					if(is_file($entry) && preg_match("/\.sql/i", $entry)) {
						$filesize = filesize($entry);
						$fp = @fopen($entry, 'rb');
						@$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
						@fclose ($fp);
							if(preg_match("/\-1.sql/i", $entry) || $identify[3] == 'shell'){
								$exportlog[$identify[0]] = array(	'version' => $identify[1],
													'type' => $identify[2],
													'method' => $identify[3],
													'volume' => $identify[4],
													'filename' => $entry,
													'size' => $filesize);
							}
					} elseif (is_dir($entry) && preg_match("/backup\_/i", $entry)) {
						$bakdir = dir($entry);
							while($bakentry = $bakdir->read()) {
								$bakentry = "$entry/$bakentry";
								if(is_file($bakentry)){
									@$fp = fopen($bakentry, 'rb');
									@$bakidentify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
									@fclose ($fp);
									if(preg_match("/\-1\.sql/i", $bakentry) || $bakidentify[3] == 'shell') {
										$identify['bakentry'] = $bakentry;
									}
								}
							}
							if(preg_match("/backup\_/i", $entry)){
								$exportlog[filemtime($entry)] = array(	'version' => $bakidentify[1],
													'type' => $bakidentify[2],
													'method' => $bakidentify[3],
													'volume' => $bakidentify[4],
													'bakentry' => $identify['bakentry'],
													'filename' => $entry);
							}
					}
				}
				$dir->close();
			} else {
				echo 'error';
			}
			krsort($exportlog);
			reset($exportlog);

			$exportinfo = '<h5>数据备份信息</h5>
	<table>
	<caption>&nbsp;&nbsp;&nbsp;数据库文件夹</caption>
	<tr>
	<th>备份项目</th><th>版本</th>
	<th>时间</th><th>类型</th>
	<th>查看</th><th>操作</th></tr>';
			foreach($exportlog as $dateline => $info) {
				$info['dateline'] = is_int($dateline) ? gmdate("Y-m-d H:i", $dateline + 8*3600) : '未知';
					switch($info['type']) {
						case 'full':
							$info['type'] = '全部备份';
							break;
						case 'standard':
							$info['type'] = '标准备份(推荐)';
							break;
						case 'mini':
							$info['type'] = '最小备份';
							break;
						case 'custom':
							$info['type'] = '自定义备份';
							break;
					}
				$info['volume'] = $info['method'] == 'multivol' ? $info['volume'] : '';
				$info['method'] = $info['method'] == 'multivol' ? '多卷' : 'shell';
				$info['url'] = str_replace(".sql", '', str_replace("-$info[volume].sql", '', substr(strrchr($info['filename'], "/"), 1)));
				$exportinfo .= "<tr>\n".
					"<td>".$info['url']."</td>\n".
					"<td>$info[version]</td>\n".
					"<td>$info[dateline]</td>\n".
					"<td>$info[type]</td>\n";
				if($info['bakentry']){
				$exportinfo .= "<td><a href=\"?action=restore&bakdirname=".$info['url']."\">查看</a></td>\n".
					"<td><a href=\"?action=restore&file=$info[bakentry]&importsubmit=yes\">[全部导入]</a></td>\n</tr>\n";
				} else {
				$exportinfo .= "<td><a href=\"?action=restore&filedirname=".$info['url']."\">查看</a></td>\n".
					"<td><a href=\"?action=restore&file=$info[filename]&importsubmit=yes\">[全部导入]</a></td>\n</tr>\n";
				}
			}
		$exportinfo .= '</table>';
		echo $exportinfo;
		unset($exportlog);
		unset($exportinfo);
		echo "<br>";
	//以前版本备份用到的备份情况
	if(!empty($filedirname)){
			$exportlog = array();
			if(is_dir(DISCUZ_ROOT.'./forumdata')) {
					$dir = dir(DISCUZ_ROOT.'./forumdata');
					while($entry = $dir->read()) {
						$entry = "./forumdata/$entry";
						if(is_file($entry) && preg_match("/\.sql/i", $entry) && preg_match("/$filedirname/i", $entry)) {
							$filesize = filesize($entry);
							@$fp = fopen($entry, 'rb');
							@$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
							@fclose ($fp);

							$exportlog[$identify[0]] = array(	'version' => $identify[1],
												'type' => $identify[2],
												'method' => $identify[3],
												'volume' => $identify[4],
												'filename' => $entry,
												'size' => $filesize);
						}
					}
					$dir->close();
				} else {
				}
				krsort($exportlog);
				reset($exportlog);

				$exportinfo = '<table>
								<caption>&nbsp;&nbsp;&nbsp;数据库文件列表</caption>
								<tr>
								<th>文件名</th><th>版本</th>
								<th>时间</th><th>类型</thd>
								<th>大小</th><td>方式</th>
								<th>卷号</th><th>操作</th></tr>';
				foreach($exportlog as $dateline => $info) {
					$info['dateline'] = is_int($dateline) ? gmdate("Y-m-d H:i", $dateline + 8*3600) : '未知';
						switch($info['type']) {
							case 'full':
								$info['type'] = '全部备份';
								break;
							case 'standard':
								$info['type'] = '标准备份(推荐)';
								break;
							case 'mini':
								$info['type'] = '最小备份';
								break;
							case 'custom':
								$info['type'] = '自定义备份';
								break;
						}
					$info['volume'] = $info['method'] == 'multivol' ? $info['volume'] : '';
					$info['method'] = $info['method'] == 'multivol' ? '多卷' : 'shell';
					$exportinfo .= "<tr>\n".
						"<td><a href=\"$info[filename]\" name=\"".substr(strrchr($info['filename'], "/"), 1)."\">".substr(strrchr($info['filename'], "/"), 1)."</a></td>\n".
						"<td>$info[version]</td>\n".
						"<td>$info[dateline]</td>\n".
						"<td>$info[type]</td>\n".
						"<td>".get_real_size($info[size])."</td>\n".
						"<td>$info[method]</td>\n".
						"<td>$info[volume]</td>\n".
						"<td><a href=\"?action=restore&file=$info[filename]&importsubmit=yes&auto=off\">[导入]</a></td>\n</tr>\n";
				}
			$exportinfo .= '</table>';
			echo $exportinfo;
		}
	// 5.5版本用到的详细备份情况
	if(!empty($bakdirname)){
			$exportlog = array();
			$filedirname = DISCUZ_ROOT.'./forumdata/'.$bakdirname;
			if(is_dir($filedirname)) {
					$dir = dir($filedirname);
					while($entry = $dir->read()) {
						$entry = $filedirname.'/'.$entry;
						if(is_file($entry) && preg_match("/\.sql/i", $entry)) {
							$filesize = filesize($entry);
							@$fp = fopen($entry, 'rb');
							@$identify = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
							@fclose ($fp);

							$exportlog[$identify[0]] = array(	'version' => $identify[1],
												'type' => $identify[2],
												'method' => $identify[3],
												'volume' => $identify[4],
												'filename' => $entry,
												'size' => $filesize);
						}
					}
					$dir->close();
			}
			krsort($exportlog);
			reset($exportlog);

			$exportinfo = '<table>
					<caption>&nbsp;&nbsp;&nbsp;数据库文件列表</caption>
					<tr>
					<th>文件名</th><th>版本</th>
					<th>时间</th><th>类型</th>
					<th>大小</th><th>方式</th>
					<th>卷号</th><th>操作</th></tr>';
			foreach($exportlog as $dateline => $info) {
				$info['dateline'] = is_int($dateline) ? gmdate("Y-m-d H:i", $dateline + 8*3600) : '未知';
				switch($info['type']) {
					case 'full':
						$info['type'] = '全部备份';
						break;
					case 'standard':
						$info['type'] = '标准备份(推荐)';
						break;
					case 'mini':
						$info['type'] = '最小备份';
						break;
					case 'custom':
						$info['type'] = '自定义备份';
						break;
				}
				$info['volume'] = $info['method'] == 'multivol' ? $info['volume'] : '';
				$info['method'] = $info['method'] == 'multivol' ? '多卷' : 'shell';
				$exportinfo .= "<tr>\n".
						"<td><a href=\"$info[filename]\" name=\"".substr(strrchr($info['filename'], "/"), 1)."\">".substr(strrchr($info['filename'], "/"), 1)."</a></td>\n".
						"<td>$info[version]</td>\n".
						"<td>$info[dateline]</td>\n".
						"<td>$info[type]</td>\n".
						"<td>".get_real_size($info[size])."</td>\n".
						"<td>$info[method]</td>\n".
						"<td>$info[volume]</td>\n".
						"<td><a href=\"?action=restore&file=$info[filename]&importsubmit=yes&auto=off\">[导入]</a></td>\n</tr>\n";
			}
			$exportinfo .= '</table>';
			echo $exportinfo;
		}
		echo "<br>";
		cexit("");
	}
} elseif ($action == 'replace') {
	htmlheader();
	$rpp			=	"500"; //每次处理多少条数据
	$totalrows		=	isset($totalrows) ? $totalrows : 0;
	$convertedrows	=	isset($convertedrows) ? $convertedrows : 0;
	$start			=	isset($start) && $start > 0 ? $start : 0;
	$end			=	$start + $rpp - 1;
	$converted		=	0;
	$maxid			=	isset($maxid) ? $maxid : 0;
	$threads_mod	=	isset($threads_mod) ? $threads_mod : 0;
	$threads_banned =	isset($threads_banned) ? $threads_banned : 0;
	$posts_mod		=	isset($posts_mod) ? $posts_mod : 0;
	if($stop == 1) {
		echo "<h4>帖子内容批量替换</h4><table>
					<tr>
						<th>暂停替换</th>
					</tr>";
		$threads_banned > 0 && print("<tr><td><br><li>".$threads_banned."个主题被放入回收站.</li><br></td></tr>");
		$threads_mod > 0 && print("<tr><td><br><li>".$threads_mod."个主题被放入审核列表.</li><br></td></tr>");
		$posts_mod > 0 && print("<tr><td><br><li>".$posts_mod."个回复被放入审核列表.</li><br></td></tr>");
		echo "<tr><td><br><li>替换了".$convertedrows."个帖子</li><br><br></td></tr>";
		echo "<tr><td><br><a href='?action=replace&step=".$step."&start=".($end + 1 - $rpp * 2)."&stay=$stay&totalrows=$totalrows&convertedrows=$convertedrows&maxid=$maxid&replacesubmit=1&threads_banned=$threads_banned&threads_mod=$threads_mod&posts_mod=$posts_mod'>继续</a><br><br></td></tr>";
		echo "</table>";
		htmlfooter();
	}
	ob_implicit_flush();
	define('IN_DISCUZ', TRUE);
	if(@!include("./config.inc.php")) {
		if(@!include("./config.php")) {
			cexit("<h4>请先上传config文件以保证您的数据库能正常链接！</h4>");
		}
	}
	require './include/db_'.$database.'.class.php';
	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);
	$selectwords_cache = './forumdata/cache/selectwords_cache.php';
	if(isset($replacesubmit) || $start > 0) {
	if($maxid ==0) {
		validid('pid','posts');
	}
		if(!file_exists($selectwords_cache) || is_array($selectwords)){
			if(count($selectwords) < 1) {
				echo "<h4>帖子内容批量替换</h4><table><tr><th>提示信息</th></tr><tr><td>您还没有选择要过滤的词语. &nbsp [<a href=tools.php?action=replace>返回</a>]</td></tr></table>";
				htmlfooter();
			} else {
				$fp = @fopen($selectwords_cache,w);
				$content = "<?php \n";
				$selectwords = implode(',',$selectwords);
				$content .= "\$selectwords = '$selectwords';\n?>";
				if(!@fwrite($fp,$content)) {
					echo "写入缓存文件$selectwords_cache 错误,请确认路径是否可写. &nbsp [<a href=tools.php?action=replace>返回</a>]";
					htmlfooter();
				} else {
					require_once "$selectwords_cache";
				}
				@fclose($fp);
			}
		} else {
			require_once "$selectwords_cache";
		}
		$array_find = $array_replace = $array_findmod = $array_findbanned = array();
		$query = $db->query("SELECT find,replacement from {$tablepre}words where id in($selectwords)");//获得现有规则{BANNED}放回收站 {MOD}放进审核列表
		while($row = $db->fetch_array($query)) {
			$find = preg_quote($row['find'], '/');
			$replacement = $row['replacement'];
			if($replacement == '{BANNED}') {
				$array_findbanned[] = $find;
			} elseif($replacement == '{MOD}') {
				$array_findmod[] = $find;
			} else {
				$array_find[] = $find;
				$array_replace[] = $replacement;
			}

		}
		function topattern_array($source_array) { //将数组正则化
			$source_array = preg_replace("/\{(\d+)\}/",".{0,\\1}",$source_array);
			foreach($source_array as $key => $value) {
				$source_array[$key] = '/'.$value.'/i';
			}
			return $source_array;
		}
		$array_find = topattern_array($array_find);
		$array_findmod = topattern_array($array_findmod);
		$array_findbanned = topattern_array($array_findbanned);

		//查询posts表准备替换
		$sql = "SELECT pid, tid, first, subject, message from {$tablepre}posts where pid >= $start and pid <= $end";
		$query = $db->query($sql);
		while($row = $db->fetch_array($query)) {
			$pid = $row['pid'];
			$tid = $row['tid'];
			$subject = $row['subject'];
			$message = $row['message'];
			$first = $row['first'];
			$displayorder = 0;//  -2审核 -1回收站
			if(count($array_findmod) > 0) {
				foreach($array_findmod as $value){
					if(preg_match($value,$subject.$message)){
						$displayorder = '-2';
						break;
					}
				}
			}
			if(count($array_findbanned) > 0) {
				foreach($array_findbanned as $value){
					if(preg_match($value,$subject.$message)){
						$displayorder = '-1';
						break;
					}
				}
			}
			if($displayorder < 0) {
				if($displayorder == '-2' && $first == 0) {//如成立就移到审核回复
					$posts_mod ++;
					$db->query("UPDATE {$tablepre}posts SET invisible = '$displayorder' WHERE pid = $pid");
				} else {
					if($db->affected_rows($db->query("UPDATE {$tablepre}threads SET displayorder = '$displayorder' WHERE tid = $tid and displayorder >= 0")) > 0) {
						$displayorder == '-2' && $threads_mod ++;
						$displayorder == '-1' && $threads_banned ++;
					}
				}
			}

			$subject = preg_replace($array_find,$array_replace,addslashes($subject));
			$message = preg_replace($array_find,$array_replace,addslashes($message));
			if($subject != addslashes($row['subject']) || $message != addslashes($row['message'])) {
				if($db->query("UPDATE {$tablepre}posts SET subject = '$subject', message = '$message' WHERE pid = $pid")) {
					$convertedrows ++;
				}
			}

			$converted = 1;
		}
		if($converted  || $end < $maxid) {
			continue_redirect('replace',"&replacesubmit=1&threads_banned=$threads_banned&threads_mod=$threads_mod&posts_mod=$posts_mod");
		} else {
			echo "<h4>帖子内容批量替换</h4><table>
						<tr>
							<th>批量替换完毕</th>
						</tr>";
			$threads_banned > 0 && print("<tr><td><br><li>".$threads_banned."个主题被放入回收站.</li><br></td></tr>");
			$threads_mod > 0 && print("<tr><td><br><li>".$threads_mod."个主题被放入审核列表.</li><br></td></tr>");
			$posts_mod > 0 && print("<tr><td><br><li>".$posts_mod."个回复被放入审核列表.</li><br></td></tr>");
			echo "<tr><td><br><li>替换了".$convertedrows."个帖子</li><br><br></td></tr>";
			echo "</table>";
			@unlink($selectwords_cache);
		}
	} else {
		$query = $db->query("select * from {$tablepre}words");
		$i = 1;
		if($db->num_rows($query) < 1) {
			echo "<h4>帖子内容批量替换</h4><table><tr><th>提示信息</th></tr><tr><td><br>对不起,现在还没有过滤规则,请<a href=\"./admincp.php?action=censor\" target='_blank'>进入论坛后台设置</a>.<br><br></td></tr></table>";
			htmlfooter();
		}
	?>
		<form method="post" action="tools.php?action=replace">
		<script language="javascript">
			function checkall(form, prefix, checkall) {
				var checkall = checkall ? checkall : 'chkall';
				for(var i = 0; i < form.elements.length; i++) {
					var e = form.elements[i];
					if(e.name != checkall && (!prefix || (prefix && e.name.match(prefix)))) {
						e.checked = form.elements[checkall].checked;
					}
				}
			}
		</script>
				<h4>批量替换帖子内容</h4>
				<table>
					<tr>
						<th><input class="checkbox" name="chkall" onclick="checkall(this.form)" type="checkbox" checked>序号</th>
						<th>不良词语</th>
						<th>替换为</th></tr>
					<?
						while($row = $db->fetch_array($query)) {
					?>
					<tr>
						<td><input class="checkbox" name="selectwords[]" value="<?=$row['id']?>" type="checkbox" checked>&nbsp <?=$i++?></td>
						<td>&nbsp <?=$row['find']?></td>
						<td>&nbsp <?=stripslashes($row['replacement'])?></td>
					</tr>
					<?}?>
				</table>
				<input type="submit" name=replacesubmit value="开始替换">
		</form>
	<div class="specialdiv">
	<h6>注意：</h6>
	<ul>
	<li>本程序会按照论坛现有过滤规则操作所有帖子内容.如需修改请<a href="./admincp.php?action=censor" target='_blank'>进论坛后台</a>。</li>
	<li>上表列出了您论坛当前的过滤词语.</li>
	</ul></div><br><br>
	<?
	}
	htmlfooter();
} elseif ($action == 'updatecache') {
	$cachedir = array('cache','templates');
	$clearmsg = '';
	foreach($cachedir as $dir) {
		if($dh = dir('./forumdata/'.$dir)) {
			while (($file = $dh->read()) !== false) {
				if ($file != "." && $file != ".." && $file != "index.htm") {
					unlink('./forumdata/'.$dir.'/'.$file);
				}
			}
		} else {
			$clearmsg .= './forumdata/'.$dir.'清除失败.<br>';
		}
	}
	htmlheader();
	echo '<h4>更新缓存</h4><table><tr><th>提示信息</th></tr><tr><td>';
	if($clearmsg == '') $clearmsg = '更新缓存完毕.';
	echo $clearmsg.'</td></tr></table>';
	htmlfooter();
} elseif ($action == 'runquery') {
	if(!file_exists("./config.inc.php") && !file_exists("config.php")){
		htmlheader();
		cexit("<h4>请先上传config文件以保证您的数据库能正常链接！</h4>");
	}
	define('IN_DISCUZ',TRUE);
	require_once "./include/common.inc.php";
	if($admincp['runquery'] != 1) {
		errorpage('使用此功能需要将 config.inc.php 当中的 $admincp[\'runquery\'] 设置修改为 1。','数据库升级');
	} else {
		if(!empty($_POST['sqlsubmit']) && $_POST['queries']) {
		$sqlquery = splitsql(str_replace(array(' cdb_', ' {tablepre}', ' `cdb_'), array(' '.$tablepre, ' '.$tablepre, ' `'.$tablepre), $queries));
		$affected_rows = 0;
		foreach($sqlquery as $sql) {
			$sql = syntablestruct(trim($sql), $db->version() > '4.1', $dbcharset);
			if(trim($sql) != '') {
				$db->query(stripslashes($sql), 'SILENT');
				if($sqlerror = $db->error()) {
					break;
				} else {
					$affected_rows += intval($db->affected_rows());
				}
			}
		}

		errorpage($sqlerror? $sqlerror : "数据库升级成功,影响行数: &nbsp;$affected_rows",'数据库升级');
		if(strpos($queries,'settings')) {
			require_once './include/cache.func.php';
			updatecache('settings');
		}
		}
		htmlheader();
		echo "<h4>数据库升级</h4>
		<form method=\"post\" action=\"tools.php?action=runquery\">
		<h5>请将数据库升级语句粘贴在下面</h4>
    		<select name=\"queryselect\" onChange=\"queries.value = this.value\">
			<option value = ''>可选择TOOLS内置升级语句</option>
			<option value = \"REPLACE INTO ".$tablepre."settings (variable, value) VALUES ('seccodestatus', '0')\">关闭所有验证码功能</option>
			<option value = \"REPLACE INTO ".$tablepre."settings (variable, value) VALUES ('supe_status', '0')\">关闭论坛中的supersite功能</option>
		</select>
		<br />
		<br /><textarea name=\"queries\">$queries</textarea><br />
		<input type=\"submit\" name=\"sqlsubmit\" value=\"提 &nbsp; 交\">
		</form>";
	}
	htmlfooter();
} elseif ($action == 'setadmin') {
	$info = "请输入要设置成管理员的用户名";
	htmlheader();
	?>
	<h4>重置管理员帐号</h4>

	<?php

	if(!empty($_POST['loginsubmit'])){
		require './config.inc.php';
		mysql_connect($dbhost, $dbuser, $dbpw);
		mysql_select_db($dbname);
		$passwordsql = empty($_POST['password']) ? '' : ', password = \''.md5($_POST['password']).'\'';
		$passwordsql .= empty($_POST['issecques']) ? '' : ', secques = \'\'';
		$passwordinfo = empty($_POST['password']) ? '密码保持不变' : '并将其密码修改为 '.$_POST['password'].'';
		$query = "SELECT uid from {$tablepre}members WHERE $_POST[loginfield] = '$_POST[username]'";
		if(@mysql_num_rows(mysql_query($query)) < 1){
				$info = '<font color="red">无此用户！请检查用户名是否正确。</font>请<a href="?action=setadmin">重新输入</a> 或者重新注册.<br><br>';
		} else {
			$query = "UPDATE {$tablepre}members SET adminid='1', groupid='1' $passwordsql WHERE $_POST[loginfield] = '$_POST[username]' limit 1";
			if(mysql_query($query)){
				$mysql_affected_rows = mysql_affected_rows();
				$_POST[loginfield] = $_POST[loginfield] == 'username' ? '用户名' : 'UID号码';
				$info = "已将$_POST[loginfield]为 $_POST[username] 的用户设置成管理员，$passwordinfo<br><br>";
			} else {
				$info = '<font color="red">失败请检查Mysql设置config.inc.php</font>';
			}
		}

	?>
	<form action="?action=setadmin" method="post"><input type="hidden" name="action" value="login" />
	<?
		errorpage($info,'重置管理员帐号',0,0);
	?>
	</form>
	<?php
	} else {?>
	<form action="?action=setadmin" method="post">
	<h5><?=$info?></h5>
		<table>
			<tr><th width="30%"><input class="radio" type="radio" name="loginfield" value="username" checked class="radio">用户名<input class="radio" type="radio" name="loginfield" value="uid" class="radio">UID</th><td width="70%"><input class="textinput" type="text" name="username" size="25" maxlength="40"></td></tr>
			<tr><th width="30%">请输入密码</th><td width="70%"><input class="textinput" type="text" name="password" size="25"></td></tr>
			<tr><th width="30%">是否清除安全提问</th><td width="70%"><input class="radio" type="radio" name="issecques" value="1" checked >是&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input class="radio" type="radio" name="issecques" value="" class="radio">否</td></tr>
		</table>
		<input type="submit" name="loginsubmit" value="提 &nbsp; 交">
	</form>
	<?php
	}
	specialdiv();
	htmlfooter();
} elseif ($action == 'setlock') {
	touch($lockfile);
	if(file_exists($lockfile)) {
		echo '<meta http-equiv="refresh" content="3 url=?">';
		errorpage("<h6>成功关闭工具箱！强烈建议您在不需要本程序的时候及时进行删除</h6>",'锁定工具箱');
	} else {
		errorpage('注意您的目录没有写入权限，我们无法给您提供安全保障，请删除论坛根目录下的tool.php文件！','锁定工具箱');
	}
} elseif ($action == 'testmail') {
	$msg = '';
	if($_POST['action'] == 'save') {

		if(is_writeable('./mail_config.inc.php')) {
			$_POST['sendmail_silent_new'] = intval($_POST['sendmail_silent_new']);
			$_POST['mailsend_new'] = intval($_POST['mailsend_new']);
			$_POST['maildelimiter_new'] = intval($_POST['maildelimiter_new']);
			$_POST['mailusername_new'] = intval($_POST['mailusername_new']);
			$_POST['mailcfg_new']['server'] = addslashes($_POST['mailcfg_new']['server']);
			$_POST['mailcfg_new']['port'] = intval($_POST['mailcfg_new']['port']);
			$_POST['mailcfg_new']['auth'] = intval($_POST['mailcfg_new']['auth']);
			$_POST['mailcfg_new']['from'] = addslashes($_POST['mailcfg_new']['from']);
			$_POST['mailcfg_new']['auth_username'] = addslashes($_POST['mailcfg_new']['auth_username']);
			$_POST['mailcfg_new']['auth_password'] = addslashes($_POST['mailcfg_new']['auth_password']);

	$savedata = <<<EOF
	<?php

	\$sendmail_silent = $_POST[sendmail_silent_new];
	\$maildelimiter = $_POST[maildelimiter_new];
	\$mailusername = $_POST[mailusername_new];
	\$mailsend = $_POST[mailsend_new];

EOF;

			if($_POST['mailsend_new'] == 2) {

	$savedata .= <<<EOF

	\$mailcfg['server'] = '{$_POST[mailcfg_new][server]}';
	\$mailcfg['port'] = {$_POST[mailcfg_new][port]};
	\$mailcfg['auth'] = {$_POST[mailcfg_new][auth]};
	\$mailcfg['from'] = '{$_POST[mailcfg_new][from]}';
	\$mailcfg['auth_username'] = '{$_POST[mailcfg_new][auth_username]}';
	\$mailcfg['auth_password'] = '{$_POST[mailcfg_new][auth_password]}';

EOF;

			} elseif ($_POST['mailsend_new'] == 3) {

	$savedata .= <<<EOF

	\$mailcfg['server'] = '{$_POST[mailcfg_new][server]}';
	\$mailcfg['port'] = '{$_POST[mailcfg_new][port]}';

EOF;

			}

			setcookie('mail_cfg', base64_encode(serialize($_POST['mailcfg_new'])), time() + 86400);

	$savedata .= <<<EOF

	?>
EOF;

			@$fp = fopen('./mail_config.inc.php', 'w');
			@fwrite($fp, $savedata);
			@fclose($fp);

			$msg = '<font color="red">设置保存完毕！</font>';

			if($_POST['sendtest']) {

				define('IN_DISCUZ', true);

				define('DISCUZ_ROOT', './');
				define('TPLDIR', './templates/default');
				require './include/global.func.php';

				$test_tos = explode(',', $_POST['mailcfg_new']['test_to']);
				$date = date('Y-m-d H:i:s');

				switch($_POST['mailsend_new']) {
					case 1:
						$title = '标准方式发送 Email';
						$message = "通过 PHP 函数及 UNIX sendmail 发送\n\n来自 {$_POST['mailcfg_new']['test_from']}\n\n发送时间 ".$date;
						break;
					case 2:
						$title = '通过 SMTP 服务器(SOCKET)发送 Email';
						$message = "通过 SOCKET 连接 SMTP 服务器发送\n\n来自 {$_POST['mailcfg_new']['test_from']}\n\n发送时间 ".$date;
						break;
					case 3:
						$title = '通过 PHP 函数 SMTP 发送 Email';
						$message = "通过 PHP 函数 SMTP 发送 Email\n\n来自 {$_POST['mailcfg_new']['test_from']}\n\n发送时间 ".$date;
						break;
				}

				$bbname = '邮件单发测试';
				sendmail($test_tos[0], $title.' @ '.$date, "$bbname\n\n\n$message", $_POST['mailcfg_new']['test_from']);
				$bbname = '邮件群发测试';
				sendmail($_POST['mailcfg_new']['test_to'], $title.' @ '.$date, "$bbname\n\n\n$message", $_POST['mailcfg_new']['test_from']);

				$msg = '设置保存完毕！<br>标题为“'.$title.' @ '.$date.'”的测试邮件已经发出！';

			}

		} else {
			$msg = '无法写入邮件配置文件 ./mail_config.inc.php，要使用本工具请设置此文件的可写入权限。';
		}
	}

	define('IN_DISCUZ', TRUE);

	if(@include("./discuz_version.php")) {
		if(substr(DISCUZ_VERSION, 0, 1) >= 6) {
			errorpage('本功能已经移动至Disuz!论坛后台管理中的邮件配置.&nbsp; <a href="./admincp.php?action=settings&do=mail" target="_blank">进入论坛后台</a>.','邮件配置/测试工具');
		}
	} else {
		errorpage("./discuz_version.php文件不存在，请确定该文件的存在。",'邮件配置/测试工具');
	}
	htmlheader();
	@include './mail_config.inc.php';
	?>
	<script>
	function $(id) {
		return document.getElementById(id);
	}
	</script>
	<h4>邮件配置/测试工具</h4>
	<?

	if($msg) {
		errorpage($msg,'邮件配置/测试工具',0,0);
	}

	?>
	<table>
	<form method="post">
	<input type="hidden" name="action" value="save"><input type="hidden" name="sendtest" value="0">
	<?
	$saved_mailcfg = empty($_COOKIE['mail_cfg']) ? array(
		'server' => 'smtp.21cn.com',
		'port' => '25',
		'auth' => 1,
		'from' => 'Discuz <username@21cn.com>',
		'auth_username' => 'username@21cn.com',
		'auth_password' => '2678hn',
		'test_from' => 'user <my@mydomain.com>',
		'test_to' => 'user1 <test1@test1.com>, user2 <test2@test2.net>'
	) : unserialize(base64_decode($_COOKIE['mail_cfg']));

	echo '<tr><th width="30%">屏蔽邮件发送中的全部错误提示</th><td>';
	echo ' <input class="checkbox" type="checkbox" name="sendmail_silent_new" value="1"'.($sendmail_silent ? ' checked' : '').'>';
	echo '</td></tr>';
	echo '<tr><th>邮件头的分隔符</th><td>';
	echo ' <input class="radio" type="radio" name="maildelimiter_new" value="1"'.($maildelimiter ? ' checked' : '').'> 使用 CRLF 作为分隔符<br>';
	echo ' <input class="radio" type="radio" name="maildelimiter_new" value="0"'.(!$maildelimiter ? ' checked' : '').'> 使用 LF 作为分隔符';
	echo '</td></tr>';
	echo '<tr><th>收件人中包含用户名</th><td>';
	echo ' <input class="checkbox" type="checkbox" name="mailusername_new" value="1"'.($mailusername ? ' checked' : '').'>';
	echo '</td></tr>';

	echo '<tr><th>邮件发送方式</th><td>';
	echo ' <input class="radio" type="radio" name="mailsend_new" value="1"'.($mailsend == 1 ? ' checked' : '').' onclick="$(\'hidden1\').style.display=\'none\';$(\'hidden2\').style.display=\'none\'"> 通过 PHP 函数及 UNIX sendmail 发送(推荐此方式)<br>';
	echo ' <input class="radio" type="radio" name="mailsend_new" value="2"'.($mailsend == 2 ? ' checked' : '').' onclick="$(\'hidden1\').style.display=\'\';$(\'hidden2\').style.display=\'\'"> 通过 SOCKET 连接 SMTP 服务器发送(支持 ESMTP 验证)<br>';
	echo ' <input class="radio" type="radio" name="mailsend_new" value="3"'.($mailsend == 3 ? ' checked' : '').' onclick="$(\'hidden1\').style.display=\'\';$(\'hidden2\').style.display=\'none\'"> 通过 PHP 函数 SMTP 发送 Email(仅 win32 下有效, 不支持 ESMTP)';
	echo '</td></tr>';

	$mailcfg['server'] = $mailcfg['server'] == '' ? $saved_mailcfg['server'] : $mailcfg['server'];
	$mailcfg['port'] = $mailcfg['port'] == '' ? $saved_mailcfg['port'] : $mailcfg['port'];
	$mailcfg['auth'] = $mailcfg['auth'] == '' ? $saved_mailcfg['auth'] : $mailcfg['auth'];
	$mailcfg['from'] = $mailcfg['from'] == '' ? $saved_mailcfg['from'] : $mailcfg['from'];
	$mailcfg['auth_username'] = $mailcfg['auth_username'] == '' ? $saved_mailcfg['auth_username'] : $mailcfg['auth_username'];
	$mailcfg['auth_password'] = $mailcfg['auth_password'] == '' ? $saved_mailcfg['auth_password'] : $mailcfg['auth_password'];

	echo '<tbody id="hidden1" style="display:'.($mailsend == 1 ? ' none' : '').'">';
	echo '<tr><th>SMTP 服务器</th><td>';
	echo ' <input class="textinput" type="text" name="mailcfg_new[server]" value="'.$mailcfg['server'].'"><br>';
	echo '</tr>';
	echo '<tr><th>SMTP 端口, 默认不需修改</th><td>';
	echo ' <input class="textinput" type="text" name="mailcfg_new[port]" value="'.$mailcfg['port'].'"><br>';
	echo '</tr>';
	echo '</tbody>';
	echo '<tbody id="hidden2" style="display:'.($mailsend != 2 ? ' none' : '').'">';
	echo '<tr><th>是否需要 AUTH LOGIN 验证</th><td>';
	echo ' <input class="checkbox" type="checkbox" name="mailcfg_new[auth]" value="1"'.($mailcfg['auth'] ? ' checked' : '').'><br>';
	echo '</tr>';
	echo '<tr><th >发信人地址 (如果需要验证,必须为本服务器地址)</th><td>';
	echo ' <input class="textinput" type="text" name="mailcfg_new[from]" value="'.$mailcfg['from'].'"><br>';
	echo '</tr>';
	echo '<tr><th>验证用户名</th><td>';
	echo ' <input class="textinput" type="text" name="mailcfg_new[auth_username]" value="'.$mailcfg['auth_username'].'"><br>';
	echo '</tr>';
	echo '<tr><th>验证密码</th><td>';
	echo ' <input class="textinput" type="text" name="mailcfg_new[auth_password]" value="'.$mailcfg['auth_password'].'"><br>';
	echo '</tr>';
	echo '</tbody>';

	?>
	</table>
	<input type="submit" name="submit" value="保存设置"><br /><br />
	<?

	echo '<table><tr><th width="30%">测试发件人</th><td>';
	echo ' <input class="textinput" type="text" name="mailcfg_new[test_from]" value="'.$saved_mailcfg['test_from'].'" size="30">';
	echo '</tr>';
	echo '<tr><th>测试收件人</th><td>';
	echo ' <input class="textinput" type="text" name="mailcfg_new[test_to]" value="'.$saved_mailcfg['test_to'].'" size="45">';
	echo '</tr>';

	?>
    </table>
	<input type="submit" name="submit" onclick="this.form.sendtest.value = 1" value="保存设置并测试发送"><br /><br />
	</form>
	<?php
	htmlfooter();

} elseif ($action == 'moveattach') {
	if(!file_exists("./config.inc.php") && !file_exists("config.php")){
		errorpage("<h4>请先上传config文件以保证您的数据库能正常链接！</h4>");
	}
	require_once './include/common.inc.php';
	htmlheader();
	echo "<h4>附件保存方式</h4>";
	$atoption = array(
		'0' => '标准(全部存入同一目录)',
		'1' => '按论坛存入不同目录',
		'2' => '按文件类型存入不同目录',
		'3' => '按月份存入不同目录',
		'4' => '按天存入不同目录',
	);
	if (!empty($_POST['moveattsubmit']) || $step == 1) {
		$rpp		=	"500"; //每次处理多少条数据
		$totalrows	=	isset($totalrows) ? $totalrows : 0;
		$convertedrows	=	isset($convertedrows) ? $convertedrows : 0;
		$start		=	isset($start) && $start > 0 ? $start : 0;
		$end		=	$start + $rpp - 1;
		$converted	=	0;
		$maxid		=	isset($maxid) ? $maxid : 0;
		$newattachsave	=	isset($newattachsave) ? $newattachsave : 0;
		$step		=	1;
		if ($start <= 1) {
			$db->query("UPDATE {$tablepre}settings SET value = '$newattachsave' WHERE variable = 'attachsave'");
			$cattachdir = $db->result($db->query("SELECT value FROM {$tablepre}settings WHERE variable = 'attachdir'"), 0);
			validid('aid', 'attachments');
		}
		$attachpath	=	isset($cattachdir) ? DISCUZ_ROOT.$cattachdir : DISCUZ_ROOT.'./attachments';
		$query = $db->query("SELECT aid, tid, dateline, filename, filetype, attachment FROM {$tablepre}attachments WHERE aid >= $start AND aid <= $end");
		while ($a = $db->fetch_array($query)) {
			$aid = $a['aid'];
			$tid = $a['tid'];
			$dateline = $a['dateline'];
			$filename = $a['filename'];
			$filetype = $a['filetype'];
			$attachment = $a['attachment'];
			$oldpath = $attachpath.'/'.$attachment;
			if (file_exists($oldpath)) {
				$realname = substr(strrchr('/'.$attachment, '/'), 1);
				if ($newattachsave == 1) {
					$fid = $db->result($db->query("SELECT fid FROM {$tablepre}threads WHERE tid = '$tid' LIMIT 1"), 0);
					$fid = $fid ? $fid : 0;
				} elseif ($newattachsave == 2) {
					$extension = strtolower(fileext($filename));
				}

				if ($newattachsave) {
					switch($newattachsave) {
						case 1: $attach_subdir = 'forumid_'.$fid; break;
						case 2: $attach_subdir = 'ext_'.$extension; break;
						case 3: $attach_subdir = 'month_'.gmdate('ym', $dateline); break;
						case 4: $attach_subdir = 'day_'.gmdate('ymd', $dateline); break;
					}
					$attach_dir = $attachpath.'/'.$attach_subdir;
					if(!is_dir($attach_dir)) {
						mkdir($attach_dir, 0777);
						@fclose(fopen($attach_dir.'/index.htm', 'w'));
					}
					$newattachment = $attach_subdir.'/'.$realname;
				} else {
					$newattachment = $realname;
				}
				$newpath = $attachpath.'/'.$newattachment;
				$asql1 = "UPDATE {$tablepre}attachments SET attachment = '$newattachment' WHERE aid = '$aid'";
				$asql2 = "UPDATE {$tablepre}attachments SET attachment = '$attachment' WHERE aid = '$aid'";
				if ($db->query($asql1)) {
					if (rename($oldpath, $newpath)) {
						$convertedrows ++;
					} else {
						$db->query($asql2);
					}
				}
				$totalrows ++;
			}
		}
		if($converted || $end < $maxid) {
			continue_redirect('moveattach', '&newattachsave='.$newattachsave.'&cattachdir='.$cattachdir);
		} else {
			$msg = "$atoption[$newattachsave] 移动附件完毕<br><li>共有".$totalrows."个附件数据</li><br /><li>移动了".$convertedrows."个附件</li>";
			errorpage($msg,'',0,0);
		}

	} else {
		$attachsave = $db->result($db->query("SELECT value FROM {$tablepre}settings WHERE variable = 'attachsave' LIMIT 1"), 0);
		$checked[$attachsave] = 'checked';
		echo "<form method=\"post\" action=\"tools.php?action=moveattach\" onSubmit=\"return confirm('您确认已经备份好数据库和附件\\n可以进行附件移动操作么？');\">
		<table>
		<tr>
		<th>本设置将重新规范所有附件的存放方式。<font color=\"red\">注意：为防止发生意外，请注意备份数据库和附件。</font></th></tr><tr><td>";
		foreach($atoption as $key => $val){
			echo "<li style=\"list-style:none;\"><input class=\"radio\" name=\"newattachsave\" type=\"radio\" value=\"$key\" $checked[$key]>&nbsp; $val</input></li><br>";
		}
		echo "
		</td></tr></table>
		<input type=\"hidden\" id=\"oldattachsave\" name=\"oldattachsave\" style=\"display:none;\" value=\"$attachsave\">
		<input type=\"submit\" name=\"moveattsubmit\" value=\"提 &nbsp; 交\">
		</form>";
		specialdiv();
	}

	htmlfooter();
} elseif ($action == 'setsiteurl') {
	require_once "./include/common.inc.php";

	if(empty($_POST['setsiteurlsubmit'])) {
		$query = $db->query("SELECT variable, value FROM {$tablepre}settings WHERE variable='supe_siteurl'");
		$supe = $db->fetch_array($query);

		if(!$supe['variable']) {
			errorpage("您还没有安装SupeSite, 无需修复。", '', 1, 1);
		} else if($supe['value']) {
			errorpage("您的SupeSite的SupeSite的站点地址是存在的，无需修复。如需更改，请到Discuz!后台配置修改。", '', 1, 1);
		} else {
			htmlheader();
		?>
			<form action="?action=setsiteurl" method="post">
			<h4>设置SupeSite 站点url</h4>
				<table>
					<tr><th width="30%">请输入SupeSite 站点url：</th><td width="70%"><input class="textinput" type="text" name="supe_siteurl" size="40"></td></tr>
				</table>
				<input type="submit" name="setsiteurlsubmit" value="提 &nbsp; 交">
			</form>
			<div class="specialdiv">
				<h6>注意：</h6>
				<ul>
				<li>这个主要修复您确实安装了SupeSite，但是因为站点url 为空而导致在后台配置SupeSite参数的时候出现“系统检测到您还没有安装 SupeSite，请您安装后再进行设置”的问题。</li>
				<li>当您使用完毕Discuz! 系统维护工具箱后，请点击锁定工具箱以确保系统的安全！下次使用前只需要在/forumdata目录下删除tool.lock文件即可开始使用。</li></ul>
			</div>
		<?php
			htmlfooter();
		}
	} else {
		$supe_siteurl = trim($supe_siteurl);
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('supe_siteurl', '$supe_siteurl')");
		errorpage("成功修复SupeSite 站点url 设置，请登录Discuz!后台设置相应的SupeSite设置。", '修复SupeSite 站点url', 1, 1);
	}

} else {
	htmlheader();
	?>

	<h4>欢迎您使用 Discuz! 系统维护工具箱<?=VERSION?></h4>
	<tr><td><br>

	<h5>Discuz! 系统维护工具箱功能简介：</h5>
	<ul>
	<li>论坛医生：自动检查您的论坛配置文件情况，系统环境信息以及错误报告。</li>
	<li>检查或修复数据库：对所有数据表进行检查修复工作。</li>
	<li>导入数据库备份：一次性导入论坛数据备份。</li>
	<li>重置管理员账号：将把您指定的会员设置为管理员。</li>
	<li>邮件配置测试：针对Discuz!6.0.0以前版本进行邮件设置。</li>
	<li>数据库冗余数据清理:对您的数据进行有效性检查，删除冗余数据信息。</li>
	<li>附件保存方式：将您现在的附件存储方式按照指定方式进行目录结构调整并重新存储。</li>
	<li>搜索未知文件：检查论坛程序目录下的非Discuz!官方文件。</li>
	<li>数据库升级：可以运行任意SQL语句，请慎用！</li>
	<li>帖子内容批量替换：按照论坛后台中设置的词语过滤列表，可选择性的对所有帖子进行处理。帖子将按照过滤规则进行处理。</li>
	<li>字段自增长修复：自动检索论坛所有的数据表，可修复自增字段丢失的问题。</li>
	<li>SupeSite站点地址：修复已经安装了SupeSite，但因为站点url 为空而导致在Discuz!后台无法配置SupeSite参数的问题。</li>
	<li>更新缓存：清除论坛的缓存。</li>
	</ul>
	<?
	specialdiv();
	htmlfooter();
}

function cexit($message){
	echo $message;
	specialdiv();
	htmlfooter();
}

function checktable($table, $loops = 0) {
	global $db, $nohtml, $simple, $counttables, $oktables, $errortables, $rapirtables;

	$result = mysql_query("CHECK TABLE $table");

	if(!$result) {
		$counttables --;
		return ;
	}

	if(!$nohtml) {
		echo "<tr bgcolor='#CCCCCC'><td colspan=4 align='center'>检查数据表 Checking table $table</td></tr>";
		echo "<tr><td>Table</td><td>Operation</td><td>Type</td><td>Text</td></tr>";
	} else {
		if(!$simple) {
			echo "\n>>>>>>>>>>>>>Checking Table $table\n";
			echo "---------------------------------<br>\n";
		}
	}
	$error = 0;
	while($r = mysql_fetch_row($result)) {
		if($r[2] == 'error') {
			if($r[3] == "The handler for the table doesn't support check/repair") {
				$r[2] = 'status';
				$r[3] = 'This table does not support check/repair/optimize';
				unset($bgcolor);
				$nooptimize = 1;
			} else {
				$error = 1;
				$bgcolor = 'red';
				unset($nooptimize);
			}
			$view = '错误';
			$errortables += 1;
		} else {
			unset($bgcolor);
			unset($nooptimize);
			$view = '正常';
			if($r[3] == 'OK') {
				$oktables += 1;
			}
		}
		if(!$nohtml) {
			echo "<tr><td>$r[0]</td><td>$r[1]</td><td bgcolor='$bgcolor'>$r[2]</td><td>$r[3] / $view </td></tr>";
		} else {
			if(!$simple) {
			echo "$r[0] | $r[1] | $r[2] | $r[3]<br>\n";
			}
		}
	}

	if($error) {
		if(!$nohtml) {
			echo "<tr><td colspan=4 align='center'>正在修复中 / Repairing table $table</td></tr>";
		} else {
			if(!$simple) {
				echo ">>>>>>>>正在修复中 / Repairing Table $table<br>\n";
			}
		}
		$result2=mysql_query("REPAIR TABLE $table");
		while($r2 = mysql_fetch_row($result2)) {
			if($r2[3] == 'OK') {
				$bgcolor='blue';
				$rapirtables += 1;
			} else {
				unset($bgcolor);
			}
			if(!$nohtml) {
				echo "<tr><td>$r2[0]</td><td>$r2[1]</td><td>$r2[2]</td><td bgcolor='$bgcolor'>$r2[3]</td></tr>";
			} else {
				if(!$simple) {
					echo "$r2[0] | $r2[1] | $r2[2] | $r2[3]<br>\n";
				}
			}
		}
	}
	if(($result2[3]=='OK'||!$error)&&!$nooptimize) {
		if(!$nohtml) {
			echo "<tr><td colspan=4 align='center'>优化数据表 Optimizing table $table</td></tr>";
		} else {
			if(!$simple) {
			echo ">>>>>>>>>>>>>Optimizing Table $table<br>\n";
			}
		}
		$result3=mysql_query("OPTIMIZE TABLE $table");
		$error=0;
		while($r3=mysql_fetch_row($result3)) {
			if($r3[2]=='error') {
				$error=1;
				$bgcolor='red';
			} else {
				unset($bgcolor);
			}
			if(!$nohtml) {
				echo "<tr><td>$r3[0]</td><td>$r3[1]</td><td bgcolor='$bgcolor'>$r3[2]</td><td>$r3[3]</td></tr>";
			} else {
				if(!$simple) {
					echo "$r3[0] | $r3[1] | $r3[2] | $r3[3]<br><br>\n";
				}
			}
		}
	}
	if($error && $loops) {
		checktable($table,($loops-1));
	}
}

function checkfullfiles($currentdir) {
	global $db, $tablepre, $md5files, $cachelist, $templatelist, $lang, $nopass;
	$dir = @opendir(DISCUZ_ROOT.$currentdir);

	while($entry = @readdir($dir)) {
		$file = $currentdir.$entry;
		$file = $currentdir != './' ? preg_replace('/^\.\//', '', $file) : $file;
		$mainsubdir = substr($file, 0, strpos($file, '/'));
		if($entry != '.' && $entry != '..') {
			echo "<script>parent.$('msg').innerHTML = '$lang[filecheck_fullcheck_current] ".addslashes(date('Y-m-d H:i:s')."<br>$lang[filecheck_fullcheck_file] $file")."';</script>\r\n";
			if(is_dir($file)) {
				checkfullfiles($file.'/');
			} elseif(is_file($file) && !in_array($file, $md5files)) {
				$pass = FALSE;
				if(in_array($file, array('./favicon.ico', './config.inc.php', './mail_config.inc.php', './robots.txt'))) {
					$pass = TRUE;
				}
				if($entry == 'index.htm' && filesize($file) < 5) {
					$pass = TRUE;
				}

				switch($mainsubdir) {
					case 'attachments' :
						if(!preg_match('/\.(php|phtml|php3|php4|jsp|exe|dll|asp|cer|asa|shtml|shtm|aspx|asax|cgi|fcgi|pl)$/i', $entry)) {
							$pass = TRUE;
						}
					break;
					case 'images' :
						if(preg_match('/\.(gif|jpg|jpeg|png|ttf|wav|css)$/i', $entry)) {
							$pass = TRUE;
						}
					case 'customavatars' :
						if(preg_match('/\.(gif|jpg|jpeg|png)$/i', $entry)) {
							$pass = TRUE;
						}
					break;
					case 'mspace' :
						if(preg_match('/\.(gif|jpg|jpeg|png|css|ini)$/i', $entry)) {
							$pass = TRUE;
						}
					break;
					case 'forumdata' :
						$forumdatasubdir = str_replace('forumdata', '', dirname($file));
						if(substr($forumdatasubdir, 0, 8) == '/backup_') {
							if(preg_match('/\.(zip|sql)$/i', $entry)) {
								$pass = TRUE;
							}
						} else {
							switch ($forumdatasubdir) {
								case '' :
									if(in_array($entry, array('dberror.log', 'install.lock'))) {
										$pass = TRUE;
									}
								break;
								case '/templates':
									if(empty($templatelist)) {
										$query = $db->query("SELECT templateid, directory FROM {$tablepre}templates");
										while($template = $db->fetch_array($query)) {
											$templatelist[$template['templateid']] = $template['directory'];
										}
									}
									$tmp = array();
									$entry = preg_replace('/(\d+)\_(\w+)\.tpl\.php/ie', '$tmp = array(\1,"\2");', $entry);
									if(!empty($tmp) && file_exists($templatelist[$tmp[0]].'/'.$tmp[1].'.htm')) {
										$pass = TRUE;
									}

								break;
								case '/logs':
									if(preg_match('/(runwizardlog|\_cplog|\_errorlog|\_banlog|\_illegallog|\_modslog|\_ratelog|\_medalslog)\.php$/i', $entry)) {
										$pass = TRUE;
									}
								break;
								case '/cache':
									if(preg_match('/\.php$/i', $entry)) {
										if(empty($cachelist)) {
											$cachelist = checkcachefiles('forumdata/cache/');
											foreach($cachelist[1] as $nopassfile => $value) {
												$nopass++;
												echo "<script>parent.$('checkresult').innerHTML += '$nopassfile<br>';</script>\r\n";
											}
										}
										$pass = TRUE;
									} elseif(preg_match('/\.(css|log)$/i', $entry)) {
										$pass = TRUE;
									}
								break;
								case '/threadcaches':
									if(preg_match('/\.htm$/i', $entry)) {
										$pass = TRUE;
									}
								break;
							}
						}

					break;
					case 'templates' :
						if(preg_match('/\.(lang\.php|htm)$/i', $entry)) {
							$pass = TRUE;
						}
					break;
					case 'include' :
						if(preg_match('/\.table$/i', $entry)) {
							$pass = TRUE;
						}
					break;
					case 'ipdata' :
						if($entry == 'wry.dat' || preg_match('/\.txt$/i', $entry)) {
							$pass = TRUE;
						}
					break;
					case 'admin' :
						if(preg_match('/\.md5$/i', $entry)) {
							$pass = TRUE;
						}
					break;
				}

				if(!$pass) {
					$nopass++;
					echo "<script>parent.$('checkresult').innerHTML += '$file<br>';</script>\r\n";
				}
			}
			ob_flush();
			flush();
		}
	}
	return $nopass;
}

function checkdirs($currentdir) {
	global $dirlist;
	$dir = @opendir(DISCUZ_ROOT.$currentdir);

	while($entry = @readdir($dir)) {
		$file = $currentdir.$entry;
		if($entry != '.' && $entry != '..') {
			if(is_dir($file)) {
				$dirlist[] = $file;
				checkdirs($file.'/');
			}
		}
	}
}

function checkcachefiles($currentdir) {
	global $authkey;
	$dir = opendir($currentdir);
	$exts = '/\.php$/i';
	$showlist = $modifylist = $addlist = array();
	while($entry = readdir($dir)) {
		$file = $currentdir.$entry;
		if($entry != '.' && $entry != '..' && preg_match($exts, $entry)) {
			@$fp = fopen($file, 'rb');
			@$cachedata = fread($fp, filesize($file));
			@fclose($fp);

			if(preg_match("/^<\?php\n\/\/Discuz! cache file, DO NOT modify me!\n\/\/Created: [\w\s,:]+\n\/\/Identify: (\w{32})\n\n(.+?)\?>$/s", $cachedata, $match)) {
				$showlist[$file] = $md5 = $match[1];
				$cachedata = $match[2];

				if(md5($entry.$cachedata.$authkey) != $md5) {
					$modifylist[$file] = $md5;
				}
			} else {
				$showlist[$file] = $addlist[$file] = '';
			}
		}

	}

	return array($showlist, $modifylist, $addlist);
}

function continue_redirect($action = 'mysqlclear', $extra = '') {
	global $scriptname, $step, $actionnow, $start, $end, $stay, $convertedrows, $allconvertedrows, $totalrows, $maxid;
	if($action == 'doctor') {
		$url = "?action=$action{$extra}";
	} else {
		$url = "?action=$action&step=".$step."&start=".($end + 1)."&stay=$stay&totalrows=$totalrows&convertedrows=$convertedrows&maxid=$maxid&allconvertedrows=$allconvertedrows".$extra;
	}
	$timeout = $GLOBALS['debug'] ? 5000 : 2000;
	echo "<script>\r\n";
	echo "<!--\r\n";
	echo "function redirect() {\r\n";
	echo "	window.location.replace('".$url."');\r\n";
	echo "}\r\n";
	echo "setTimeout('redirect();', $timeout);\r\n";
	echo "-->\r\n";
	echo "</script>\r\n";
	if($action == 'doctor') {
		echo '<h4>论坛医生</h4><br><table>
		<tr><th>正在进行检查,请稍候</th></tr><tr><td>';
		echo "<br><a href=\"".$url."\">如果您的浏览器长时间没有自动跳转，请点击这里！</a><br><br>";
		echo '</td></tr></table>';
	} elseif($action == 'replace') {
		echo '<h4>数据处理中</h4><table>
		<tr><th>正在进行'.$actionnow.'</th></tr><tr><td>';
		echo "正在处理 $start ---- $end 条数据[<a href='$url&stop=1' style='color:red'>停止运行</a>]";
		echo "<br><br><a href=\"".$url."\">如果您的浏览器长时间没有自动跳转，请点击这里！</a>";
		echo '</td></tr></table>';
	} else {
		echo '<h4>数据处理中</h4><table>
		<tr><th>正在进行'.$actionnow.'</th></tr><tr><td>';
		echo "正在处理 $start ---- $end 条数据[<a href='?action=$action' style='color:red'>停止运行</a>]";
		echo "<br><br><a href=\"".$url."\">如果您的浏览器长时间没有自动跳转，请点击这里！</a>";
		echo '</td></tr></table>';
	}
}

function dirsize($dir) {
	$dh = @opendir($dir);
	$size = 0;
	while($file = @readdir($dh)) {
		if ($file != '.' && $file != '..') {
			$path = $dir.'/'.$file;
			if (@is_dir($path)) {
				$size += dirsize($path);
			} else {
				$size += @filesize($path);
			}
		}
	}
	@closedir($dh);
	return $size;
}

function get_real_size($size) {

	$kb = 1024;
	$mb = 1024 * $kb;
	$gb = 1024 * $mb;
	$tb = 1024 * $gb;

	if($size < $kb) {
		return $size.' Byte';
	} else if($size < $mb) {
		return round($size/$kb,2).' KB';
	} else if($size < $gb) {
		return round($size/$mb,2).' MB';
	} else if($size < $tb) {
		return round($size/$gb,2).' GB';
	} else {
		return round($size/$tb,2).' TB';
	}
}

function htmlheader(){
	global $alertmsg;
	echo '<html>
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
		<title>Discuz! 系统维护工具箱</title>
		<style type="text/css">
		<!--
		body {font-family: Arial, Helvetica, sans-serif, "宋体";font-size: 12px;color:#000;line-height: 120%;padding:0;margin:0;background:#DDE0FF;overflow-x:hidden;word-break:break-all;white-space:normal;scrollbar-3d-light-color:#606BFF;scrollbar-highlight-color:#E3EFF9;scrollbar-face-color:#CEE3F4;scrollbar-arrow-color:#509AD8;scrollbar-shadow-color:#F0F1FF;scrollbar-base-color:#CEE3F4;}
        a:hover {color:#60F;}
		ul {padding:2px 0 10px 0;margin:0;}
		textarea,table,td,th,select{border:1px solid #868CFF;border-collapse:collapse;}
		input{margin:10px 0 0px 30px;border-width:1px;border-style:solid;border-color:#FFF #64A7DD #64A7DD #FFF;padding:2px 8px;background:#E3EFF9;}
			input.radio,input.checkbox,input.textinput,input.specialsubmit {margin:0;padding:0;border:0;padding:0;background:none;}
			input.textinput,input.specialsubmit {border:1px solid #AFD2ED;background:#FFF;height:24px;}
			input.textinput {padding:4px 0;} 			input.specialsubmit {border-color:#FFF #64A7DD #64A7DD #FFF;background:#E3EFF9;padding:0 5px;}
		option {background:#FFF;}
		select {background:#F0F1FF;}
		#header {height:60px;width:100%;padding:0;margin:0;}
		    h2 {font-size:24px;font-weight:bold;position:absolute;top:24px;left:20px;padding:10px;margin:0;}
		    h3 {font-size:14px;position:absolute;top:28px;right:20px;padding:10px;margin:0;}
		#content {height:510px;background:#F0F1FF;overflow-x:hidden;z-index:1000;}
		    #nav {top:60px;left:0;height:510px;width:180px;border-right:1px solid #DDE0FF;position:absolute;z-index:2000;}
		        #nav ul {padding:0 10px;padding-top:30px;}
		        #nav li {list-style:none;}
		        #nav li a {font-size:14px;line-height:180%;font-weight:400;color:#000;}
		        #nav li a:hover {color:#60F;}
		    #textcontent {padding-left:200px;height:510px;width:100%;line-height:160%;overflow-y:auto;overflow-x:hidden;}
			    h4,h5,h6 {padding:4px;font-size:16px;font-weight:bold;margin-top:20px;margin-bottom:5px;color:#006;}
				h5,h6 {font-size:14px;color:#000;}
				h6 {color:#F00;padding-top:5px;margin-top:0;}
				.specialdiv {width:70%;border:1px dashed #C8CCFF;padding:0 5px;margin-top:20px;background:#F9F9FF;}
				#textcontent ul {margin-left:30px;}
				textarea {width:78%;height:320px;text-align:left;border-color:#AFD2ED;}
				select {border-color:#AFD2ED;}
				table {width:74%;font-size:12px;margin-left:18px;margin-top:10px;}
				    table.specialtable,table.specialtable td {border:0;}
					td,th {padding:5px;text-align:left;}
				    caption {font-weight:bold;padding:8px 0;color:#3544FF;text-align:left;}
				    th {background:#D9DCFF;font-weight:600;}
					td.specialtd {text-align:left;}
				.specialtext {background:#FCFBFF;margin-top:20px;padding:5px 40px;width:64.5%;margin-bottom:10px;color:#006;}
		#footer p {padding:0 5px;text-align:center;}
		-->
		</style>
		</head>

		<body>
        <div id="header">
		<h2>Discuz! 系统维护工具箱</h2>
		<h3>[ <a href="?" target="_self">工具箱首页</a> ]
		[ <a href="?action=setlock" target="_self">锁定工具箱</a> ]</h3>
		</div>
		<div id="nav">
		<ul>
		<li>[ <a href="?action=doctor" target="_self" '.$alertmsg.'>论坛医生</a> ]</li>
		<li>[ <a href="?action=repair" target="_self">检查或修复数据库</a> ]</li>
		<li>[ <a href="?action=restore" target="_self">导入数据库备份</a> ]</li>
		<li>[ <a href="?action=setadmin" target="_self">重置管理员帐号</a> ]</li>
		<li>[ <a href="?action=testmail" target="_self">邮件配置测试</a> ]</li>
		<li>[ <a href="?action=mysqlclear" target="_self">数据库冗余数据清理</a> ]</li>
		<li>[ <a href="?action=moveattach" target="_self">附件保存方式</a> ]</li>
		<li>[ <a href="?action=filecheck" target="_self">搜索未知文件</a> ]</li>
		<li>[ <a href="?action=runquery" target="_self">数据库升级</a> ]</li>
		<li>[ <a href="?action=replace" target="_self">帖子内容批量替换</a> ]</li>
		<li>[ <a href="tools.php?action=repair_auto" '.$alertmsg.'>字段自增长修复</a> ]</li>
		<li>[ <a href="?action=setsiteurl" target="_self">SupeSite站点地址</a> ]</li>
		<li>[ <a href="tools.php?action=updatecache">更新缓存</a> ]</li>
		<li>[ <a href="?action=logout" target="_self">退出</a> ]</li>
		</ul></div>
		<div id="content">
		<div id="textcontent">';
}

function htmlfooter(){
	echo '
		</div></div>
		<div id="footer"><p>Discuz! Board 系统维护工具箱 &nbsp;
		版权所有 &copy;2001-2007 <a href="http://www.comsenz.com" style="color: #888888; text-decoration: none">
		康盛创想(北京)科技有限公司 Comsenz Inc.</a></font></td></tr><tr style="font-size: 0px; line-height: 0px; spacing: 0px; padding: 0px; background-color: #698CC3">
		</p></div>
		</body>
		</html>';
	exit;
}

function errorpage($message,$title = '',$isheader = 1,$isfooter = 1){
	if($isheader) {
		htmlheader();
	}
	!$isheader && $title = '';
	if($message == 'login'){
		$message ='<h4>工具箱登录</h4>
				<form action="?" method="post">
					<table class="specialtable"><tr>
					<td width="20%"><input class="textinput" type="password" name="toolpassword"></input></td>
					<td><input class="specialsubmit" type="submit" value="登 录"></input></td></tr></table>
					<input type="hidden" name="action" value="login">
				</form>';
	} else {
		$message = "<h4>$title</h4><br><br><table><tr><th>提示信息</th></tr><tr><td>$message</td></tr></table>";
	}
	echo $message;
	if($isfooter) {
		htmlfooter();
	}
}

function redirect($url) {
	echo "<script>";
	echo "function redirect() {window.location.replace('$url');}\n";
	echo "setTimeout('redirect();', 2000);\n";
	echo "</script>";
	echo "<br><br><a href=\"$url\">如果您的浏览器没有自动跳转，请点击这里</a>";
	cexit("");
}

/**
 * 检查目录里下的文件权限函数
 *
 * @param unknown_type $directory
 */
function getdirentry($directory) {
	global $entryarray;
	$dir = dir('./'.$directory);
	while($entry = $dir->read()) {
		if($entry != '.' && $entry != '..') {
			if(is_dir('./'.$directory.'/'.$entry)) {

				$entryarray[] = $directory.'/'.$entry;
				getdirentry($directory."/".$entry);
			} else {
				$entryarray[] = $directory.'/'.$entry;
			}
		}
	}
	$dir->close();
}

function splitsql($sql){
	$sql = str_replace("\r", "\n", $sql);
	$ret = array();
	$num = 0;
	$queriesarray = explode(";\n", trim($sql));
	unset($sql);
	foreach($queriesarray as $query) {
		$queries = explode("\n", trim($query));
		foreach($queries as $query) {
			$ret[$num] .= $query[0] == "#" ? NULL : $query;
		}
		$num++;
	}
	return($ret);
}

function syntablestruct($sql, $version, $dbcharset) {

	if(strpos(trim(substr($sql, 0, 18)), 'CREATE TABLE') === FALSE) {
		return $sql;
	}

	$sqlversion = strpos($sql, 'ENGINE=') === FALSE ? FALSE : TRUE;

	if($sqlversion === $version) {

		return $sqlversion && $dbcharset ? preg_replace(array('/ character set \w+/i', '/ collate \w+/i', "/DEFAULT CHARSET=\w+/is"), array('', '', "DEFAULT CHARSET=$dbcharset"), $sql) : $sql;
	}

	if($version) {
		return preg_replace(array('/TYPE=HEAP/i', '/TYPE=(\w+)/is'), array("ENGINE=MEMORY DEFAULT CHARSET=$dbcharset", "ENGINE=\\1 DEFAULT CHARSET=$dbcharset"), $sql);

	} else {
		return preg_replace(array('/character set \w+/i', '/collate \w+/i', '/ENGINE=MEMORY/i', '/\s*DEFAULT CHARSET=\w+/is', '/\s*COLLATE=\w+/is', '/ENGINE=(\w+)(.*)/is'), array('', '', 'ENGINE=HEAP', '', '', 'TYPE=\\1\\2'), $sql);
	}
}

function stay_redirect() {
	global $action, $actionnow, $step, $stay, $convertedrows, $allconvertedrows;
	$nextstep = $step + 1;
	echo '<h4>数据库冗余数据清理</h4><table>
			<tr"><th>正在进行'.$actionnow.'</th></tr><tr>
			<td>';
	if($stay) {
		$actions = isset($action[$nextstep]) ? $action[$nextstep] : '结束';
		echo "$actionnow 操作完毕.共处理<font color=red>{$convertedrows}</font>条数据.".($stay == 1 ? "&nbsp;&nbsp;&nbsp;&nbsp;" : '').'<br><br>';
		echo "<a href='?action=mysqlclear&step=".$nextstep."&stay=1'>如果继续下一步操作( $actions )，请点击这里！</a><br>";
	} else {
		if(isset($action[$nextstep])) {
			echo '即将进入：'.$action[$nextstep].'......';
		}
		$allconvertedrows = $allconvertedrows + $convertedrows;
		$timeout = $GLOBALS['debug'] ? 5000 : 2000;
		echo "<script>\r\n";
		echo "<!--\r\n";
		echo "function redirect() {\r\n";
		echo "	window.location.replace('?action=mysqlclear&step=".$nextstep."&allconvertedrows=".$allconvertedrows."');\r\n";
		echo "}\r\n";
		echo "setTimeout('redirect();', $timeout);\r\n";
		echo "-->\r\n";
		echo "</script>\r\n";
		echo "[<a href='?action=mysqlclear' style='color:red'>停止运行</a>]<br><br><a href=\"".$scriptname."?step=".$nextstep."\">如果您的浏览器长时间没有自动跳转，请点击这里！</a>";
	}

	echo '</td></tr></table>';
}

function loadtable($table, $force = 0) {	//检查数据库表字符集函数
	global $carray;
	$discuz_tablepre = $carray['tablepre'];
	static $tables = array();

	if(!isset($tables[$table])) {
		if(mysql_get_server_info() > '4.1') {
			$query = @mysql_query("SHOW FULL COLUMNS FROM {$discuz_tablepre}$table");
		} else {
			$query = @mysql_query("SHOW COLUMNS FROM {$discuz_tablepre}$table");
		}
		while($field = @mysql_fetch_assoc($query)) {
			$tables[$table][$field['Field']] = $field;
		}
	}
	return $tables[$table];
}

function validid($id, $table) {//获得数据表的最大和最小 id 值
	global $start, $maxid, $db, $tablepre;
	$sql = $db->query("SELECT MIN($id) AS minid, MAX($id) AS maxid FROM {$tablepre}$table");
	$result = $db->fetch_array($sql);
	$start = $result['minid'] ? $result['minid'] - 1 : 0;
	$maxid = $result['maxid'];
}

function specialdiv() {
	echo '<div class="specialdiv">
		<h6>注意：</h6>
		<ul>
		<li>对数据库操作可能会出现意外现象的发生及破坏，所以请先备份好数据库再进行上述操作！另外请您选择服务器压力比较小的时候进行一些优化操作。</li>
		<li>当您使用完毕Discuz! 系统维护工具箱后，请点击锁定工具箱以确保系统的安全！下次使用前只需要在/forumdata目录下删除tool.lock文件即可开始使用。</li></ul></div>';
}
?>