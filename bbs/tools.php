<?php

/*
	[Discuz!] Tools (C)2001-2007 Comsenz Inc.
	This is NOT a freeware, use is subject to license terms

	$Id: tools.php 1265 2007-10-24 08:03:15Z$
*/

$tool_password = 'Em123456'; // ������� ��������һ�����߰��ĸ�ǿ�����룬����Ϊ�գ��������

$lockfile = 'forumdata/tool.lock';
$target_fsockopen = '0'; //ʹ�ú��ַ�ʽ�������ӷ����� 0=����, 1=IP ��ʹ��IP��ʽ��Ҫ��֤IP��ַ�����������ʵ�����վ�㣩

$alertmsg = ' onclick="alert(\'���ȷ����ʼ����,������Ҫһ��ʱ��,���Ժ�\');"';
if(!file_exists('./config.inc.php') || !is_writeable('./forumdata')) {
	$alertmsg = '';
	errorpage('��������������̳��Ŀ¼�²�������ʹ��.');
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
	errorpage("<h6>�������ѹرգ����迪��ֻҪͨ�� FTP ɾ�� forumdata �µ� tool.lock �ļ����ɣ� </h6>");
} elseif ($tool_password == ''){
	$alertmsg = '';
	errorpage('<h6>����������Ĭ��Ϊ�գ���һ��ʹ��ǰ�����޸ı��ļ���$tool_password�������룡</h6>');
}

if($_POST['action'] == 'login') {
	setcookie('toolpassword', $_POST['toolpassword'], 0);
	echo '<meta http-equiv="refresh" content="2 url=?">';
	errorpage("<h6>���Եȣ������¼�У�</h6>");
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
			cexit("<h4>�����ϴ�config�ļ��Ա�֤�������ݿ����������ӣ�</h4>");
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
			echo '<h4>����޸����ݿ�</h4>
			    <h5>�����:</h5>
					<table>
						<tr><th>����(��)</th><th>������(��)</th><th>�����(��)</th><th>������(��)</th></tr>
						<tr><td>'.$counttables.'</td><td>'.$oktables.'</td><td>'.$rapirtables.'</td><td>'.$errortables.'</td></tr>
					</table>
				<p>�����û�д�����뷵�ع�������ҳ��֮������޸�</p>
				<p><b><a href="tools.php?action=repair">�����޸�</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="tools.php">������ҳ</a></b></p>
				</td></tr></table>';
			specialdiv();
		}
	} else {
		htmlheader();
		echo "<h4>����޸����ݿ�</h4>
		<div class='specialdiv'>
				������ʾ��
				<ul>
				<li>������ͨ������ķ�ʽ�޸��Ѿ��𻵵����ݿ⡣����������ĵȴ��޸������</li>
				<li>����������޸����������ݿ���󣬵��޷���֤�����޸����е����ݿ����(��Ҫ MySQL 3.23+)</li>
				</ul>
				</div>
				<h5>������</h5>
				<ul>
				<li><a href=\"?action=repair&check=1&nohtml=1&simple=1\">��鲢�����޸����ݿ�1��</a>
				<li><a href=\"?action=repair&check=1&iterations=5&nohtml=1&simple=1\">��鲢�����޸����ݿ�5��</a> (��Ϊ���ݿ��д��ϵ������ʱ��Ҫ���޸����β�����ȫ�޸��ɹ�)
				</ul>";
		specialdiv();
	}
	htmlfooter();
} elseif ($action == 'doctor') {
	//��̳ҽ������
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
			//�õ����صĽ��
			$contents = "";
			while (!feof($fp)) {
				$contents .= fread($fp, 1024);
			}
			fclose($fp);
			$array = split("\n\r", $contents, "2");
			return trim($array[1]);
		}
		//��̳ģʽ��ʽ�������
		$ok_style_s = '[color=RoyalBlue][b]';
		$error_style_s = '[color=Red][b]';
		$style_e = '[/b][/color]';
		$title_style_s = '[b]';
		$title_style_e = '[/b]';

		$phpfile_array = array('discuzroot', 'templates', 'cache');//�ļ��������е�Ŀ¼����Ӧ����($dir_array)
		$dir_array = array('��̳��Ŀ¼', 'ģ�建��Ŀ¼(forumdata/templates)', '��������Ŀ¼(forumdata/cache)');
		$doctor_top = count($phpfile_array) - 1;

		if(@!include("./config.inc.php")) {
			if(@!include("./config.php")) {
			cexit("<h4>�����ϴ�config�ļ��Ա�֤�������ݿ����������ӣ�</h4>");
			}
		}
	if($doctor_step == $doctor_top) {

		//���Config.inc.php�ļ�����
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
			'dbhost' => '���ݿ������',
			'dbuser' => '���ݿ��û���',
			'dbpw' => '���ݿ�����',
			'dbname' => '���ݿ���',
			'pconnect' => '���ݿ��Ƿ�־�����',
			'cookiepre' => 'cookie ǰ׺',
			'cookiedomain' => 'cookie ������',
			'cookiepath' => 'cookie ����·��',
			'tablepre' => '����ǰ׺',
			'dbcharset' => 'MySQL�����ַ���',
			'charset' => '��̳�ַ���',
			'headercharset' => 'ǿ����̳ҳ��ʹ��Ĭ���ַ���',
			'tplrefresh' => '��̳���ģ���Զ�ˢ�¿���',
			'forumfounders' => '��̳��ʼ��uid',
			'dbreport' => '�Ƿ��ʹ��󱨸������Ա',
			'errorreport' => '�Ƿ����γ��������Ϣ',
			'attackevasive' => '��̳��������',
			'admincp[\'forcesecques\']' => '������Ա�Ƿ�������ð�ȫ���ʲ��ܽ���ϵͳ����',
			'admincp[\'checkip\']' => '��̨��������Ƿ���֤����Ա�� IP',
			'admincp[\'tpledit\']' => '�Ƿ��������߱༭��̳ģ��',
			'admincp[\'runquery\']' => '�Ƿ������̨���� SQL ���',
			'admincp[\'dbimport\']' => '�Ƿ������̨�ָ���̳����',
		);
		$comment = array(
			'pconnect' => '�ǳ־�����',
			'cookiepre' => '�����',
			'cookiepath' => '�����',
			'charset' => '�����',
			'adminemail' => '�����',
			'admincp' => '��������',
		);
		@mysql_connect($carray['dbhost'], $carray['dbuser'], $carray['dbpw']) or $mysql_errno = mysql_errno();
		!$mysql_errno && @mysql_select_db($carray['dbname']) or $mysql_errno = mysql_errno();
		$comment_error = "{$error_style_s}����{$style_e}";
		if ($mysql_errno == '2003') {
			$comment['dbhost'] = "{$error_style_s}�˿����ó���{$style_e}";
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
		$comment['pconnect'] = '�ǳ־�����';
		$carray['pconnect'] == 1 && $comment['pconnect'] = '�־�����';
		if ($carray['cookiedomain'] && substr($carray['cookiedomain'], 0, 1) != '.') {
			$comment['cookiedomain'] = "{$error_style_s}���� . ��ͷ,��Ȼͬ����¼�����{$style_e}";
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
				$carray['dbcharset'] .= $error_style_s.'����������̳���ݿ��ַ���Ϊ '.$dzdbcharset.' ���뽫�������ó� '.$dzdbcharset.$style_e;
			}
		}
		if(!in_array($carray['charset'],array('gbk', 'big5', 'utf-8'))) {
			$carray['charset'] .= $error_style_s."  ����Ŀǰ�ַ���ֻ֧��'gbk', 'big5', 'utf-8'".$style_e;
		}
		if ($carray['headercharset'] == 0) {
			$comment['headercharset'] = $title_style_s.'δ����'.$title_style_e;
		} else {
			$comment['headercharset'] = $ok_style_s.'����'.$style_e;
		}
		if ($carray['tplrefresh'] == 0) {
			$comment['tplrefresh'] = $title_style_s.'�ر�'.$title_style_e;
		} else {
			$comment['tplrefresh'] = $ok_style_s.'����'.$style_e;
		}
		if (preg_match('/[^\d,]/i', str_replace(' ', '', $carray['forumfounders']))) {
			$comment['forumfounders'] = $error_style_s.'�������зǷ��ַ�����������ֻ�ܺ������ֺͰ�Ƕ��� ,'.$style_e;
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
					$comment['forumfounders'] = $error_style_s.'������ʼ�����޹���Ա'.$style_e;
				} elseif ($notadmin) {
					$comment['forumfounders'] = $error_style_s.'���棺��ʼ�����зǹ���Ա��uid���£�'.$notadminids.$style_e;
				}
			} else {
				$comment['forumfounders'] = $error_style_s.'���棺��ʼ������Ϊ�գ��������Ա���в��ɿ���Ա�������а�ȫ����'.$style_e;
			}
		}
		$comment['dbreport'] = $carray['dbreport'] == 0 ? '�����ʹ��󱨸�' : '���ʹ��󱨸�';
		$comment['errorreport'] = $carray['errorreport'] == 1 ? '���γ������' : '�����γ������';
		if (preg_match('/[^\d|]/i', str_replace(' ', '', $carray['attackevasive']))) {
			$carray['attackevasive'] .= $error_style_s.'�������зǷ��ַ�,��������ֻ�ܺ������ֺͰ�Ƕ���,'.$style_e;
		} else {
			if (preg_match('/[8]/i', $carray['attackevasive']) && @mysql_result(@mysql_query("SELECT COUNT(*) FROM {$carray[tablepre]}members")) < 1) {
				$carray['attackevasive'] .= $error_style_s.'�����������˻ش�����(8)����δ�����֤����ʹ� ,'.$style_e;
			}
		}
		$comment_admincp_error = "�� > {$error_style_s}���棺�а�ȫ����{$style_e}";
		$comment_admincp_ok = "�� > {$error_style_s}���棺�а�ȫ����{$style_e}";
		if ($carray['admincp[\'forcesecques\']'] == 1) {
			$comment['admincp[\'forcesecques\']'] = "{$ok_style_s}��{$style_e}";
		} else {
			$comment['admincp[\'forcesecques\']'] = $comment_admincp_error;
		}
		if ($carray['admincp[\'checkip\']'] == 0) {
			$comment['admincp[\'checkip\']'] = $comment_admincp_error;
		} else {
			$comment['admincp[\'checkip\']'] = "{$ok_style_s}��{$style_e}";
		}
		if ($carray['admincp[\'tpledit\']'] == 1) {
			$comment['admincp[\'tpledit\']'] = $comment_admincp_ok;
		} else {
			$comment['admincp[\'tpledit\']'] = "{$title_style_s}��{$title_style_e}";
		}
		if ($carray['admincp[\'runquery\']'] == 1) {
			$comment['admincp[\'runquery\']'] = $comment_admincp_ok;
		} else {
			$comment['admincp[\'runquery\']'] = "{$title_style_s}��{$title_style_e}";
		}
		if ($carray['admincp[\'dbimport\']'] == 1) {
			$comment['admincp[\'dbimport\']'] = $comment_admincp_ok;
		} else {
			$comment['admincp[\'dbimport\']'] = "{$title_style_s}��{$title_style_e}";
		}
		foreach($carray as $key => $keyfield) {
			$clang[$key] == '' && $clang[$key] = '&nbsp;';
			strpos('comma'.$comment[$key], '����') && $comment[$key] = $comment[$key];
			strpos('comma'.$comment[$key], '����') && $comment[$key] = $comment[$key];
			$comment[$key] == '' && $comment[$key] = "{$ok_style_s}����{$style_e}";
			if(in_array($key, array('dbuser', 'dbpw'))) {
				$keyfield = '**����**';
			}
			$keyfield == '' && $keyfield = '��';
			if(!in_array($key, array('dbhost','dbuser','dbpw','dbname'))) {
				if(in_array($key, array('pconnect', 'headercharset', 'tplrefresh', 'dbreport', 'errorreport', 'admincp[\'forcesecques\']', 'admincp[\'checkip\']', 'admincp[\'tpledit\']', 'admincp[\'runquery\']', 'admincp[\'dbimport\']'))) {
					$doctor_config .= "\n\t{$title_style_s}$key{$title_style_e} ---> $clang[$key] ---> $comment[$key]\n";
				} elseif(in_array($key, array('cookiepre', 'cookiepath', 'cookiedomain', 'charset', 'dbcharset', 'attackevasive'))) {
					$doctor_config .= "\n\t{$title_style_s}$key{$title_style_e} ---> $clang[$key] ---> $keyfield\n";
				} else {
					$doctor_config .= "\n\t{$title_style_s}$key{$title_style_e} ---> $clang[$key] ---> $keyfield ---> $comment[$key]\n";
				}
			} else {
				if(strstr($comment[$key], '����')) {
					strstr($doctor_config_db, '����') && $doctor_config_db = '';
					$doctor_config_db .= "{$title_style_s}$key{$title_style_e} ---> $clang[$key] ---> $comment[$key]";
				} else {
					if(empty($doctor_config_db)) {
						$doctor_config_db ="\n\t{$ok_style_s}���ݿ���������.{$style_e}";
					}
				}
			}

		}
		$doctor_config = "\n".$doctor_config_db.$doctor_config;
		//У�黷���Ƿ�֧��DZ/SS���鿴���ݿ�ͱ���ַ�����������Ϣ charset,dbcharset, php,mysql,zend,php �̱��

		$msg = '';
		$curr_os = PHP_OS;

		if(!function_exists('mysql_connect')) {
			$curr_mysql = $error_style_s.'��֧��'.$style_e;
			$msg .= "���ķ�������֧��MySql���ݿ⣬�޷���װ��̳����";
			$quit = TRUE;
		} else {
			if(@mysql_connect($dbhost, $dbuser, $dbpw)) {
				$curr_mysql =  mysql_get_server_info();
			} else {
				$curr_mysql = $ok_style_s.'֧��'.$style_e;
			}
		}
			if(function_exists('mysql_connect')) {
					$authkeylink = @mysql_connect($dbhost, $dbuser, $dbpw);
					mysql_select_db($dbname, $authkeylink);
					$authkeyresult = mysql_result(mysql_query("SELECT `value` FROM {$tablepre}settings WHERE `variable`='authkey'", $authkeylink), 0);
					if($authkeyresult) {
							$authkeyexist = $ok_style_s.'����'.$style_e;
					} else {
							$authkeyexist = $error_style_s.'������'.$style_e;
					}
			}
		$curr_php_version = PHP_VERSION;
		if($curr_php_version < '4.0.6') {
			$msg .= "���� PHP �汾С�� 4.0.6, �޷�ʹ�� Discuz! / SuperSite��";
		}

		if(ini_get('allow_url_fopen')) {
			$allow_url_fopen = $ok_style_s.'����'.$style_e;
		} else {
			$allow_url_fopen = $title_style_s.'������'.$title_style_e;
		}

		$max_execution_time = get_cfg_var('max_execution_time');
		$max_execution_time == 0 && $max_execution_time = '������';

		$memory_limit = get_cfg_var('memory_limit');

		$curr_server_software = $_SERVER['SERVER_SOFTWARE'];

		if(function_exists('ini_get')) {
			if(!@ini_get('short_open_tag')) {
				$curr_short_tag = $title_style_s.'������'.$title_style_e;
				$msg .='�뽫 php.ini �е� short_open_tag ����Ϊ On�������޷�ʹ����̳��';
			} else {
				$curr_short_tag = $ok_style_s.'����'.$style_e;
			}

			if(@ini_get(file_uploads)) {
				$max_size = @ini_get(upload_max_filesize);
				$curr_upload_status = '�������ϴ����������ߴ�: '.$max_size;
			} else {
				$msg .= "�����ϴ�����ز�������������ֹ��";
			}
		} else {
			$msg .= 'php.ini�н�����ini_get()����.���ֻ��������޷����.';
		}

		if(!defined('OPTIMIZER_VERSION')) define('OPTIMIZER_VERSION','û�а�װ��汾�ϵ�');
		if(OPTIMIZER_VERSION < 3.0) {
			$msg .="����ZEND�汾����3.0,���޷�ʹ��SuperSite.";
		}
			//��ʱĿ¼�ļ��
			if(@is_writable(@ini_get('upload_tmp_dir'))){
					$tmpwritable = $ok_style_s.'��д'.$style_e;
			} elseif(!@ini_get('upload_tmp_dir') & @is_writable($_ENV[TEMP])) {
					$tmpwritable = $ok_style_s.'��д'.$style_e;
			} else {
					$tmpwritable = $title_style_s.'����д'.$title_style_e;
			}

		if(@ini_get('safe_mode') == 1) {
			$curr_safe_mode = $ok_style_s.'����'.$style_e;
		} else {
			$curr_safe_mode = $title_style_s.'�ر�'.$title_style_e;
		}
		if(@diskfreespace('.')) {
			$curr_disk_space = intval(diskfreespace('.') / (1024 * 1024)).'M';
		} else {
			$curr_disk_space = '�޷����';
		}
		if(function_exists('xml_parser_create')) {
			$curr_xml = $ok_style_s.'����'.$style_e;
		} else {
			$curr_xml = $title_style_s.'������'.$title_style_e;
		}

		if(function_exists('file')) {
				$funcexistfile = $ok_style_s.'����'.$style_e;
		} else {
				$funcexistfile = $title_style_s.'������'.$title_style_e;
		}

		if(function_exists('fopen')) {
				$funcexistfopen = $ok_style_s.'����'.$style_e;
		} else {
				$funcexistfopen = $title_style_s.'������'.$title_style_e;
		}

		if(@ini_get('display_errors')) {
			$curr_display_errors = $ok_style_s.'����'.$style_e;
		} else {
			$curr_display_errors = $title_style_s.'�ر�'.$title_style_e;
		}
		if(!function_exists('ini_get')) {
			$curr_display_errors = $tmpwritable = $curr_safe_mode = $curr_upload_status = $curr_short_tag = '�޷����';
		}
		//Ŀ¼Ȩ�޼��
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
					$dir_perm .= "\n\t\t".(is_dir($fullentry) ? 'Ŀ¼' : '�ļ�')." ./$entry {$error_style_s}�޷�д��.{$style_e}";
					$msg .= "\n\t\t".(is_dir($fullentry) ? 'Ŀ¼' : '�ļ�')." ./$entry {$error_style_s}�޷�д��.{$style_e}";
					$fault = 1;
				}
			}
		}
		$dir_perm .= $fault ? '' : $ok_style_s.'�ļ���Ŀ¼����ȫ����ȷ'.$style_e;

		/**
		 * gd�����躯���ļ��
		 */
		$gd_check = '';
		if(!extension_loaded('gd')) {
			$gd_check .= '����php.iniδ����extension=php_gd2.dll(windows)����δ����gd��(linux).';
		} elseif(!function_exists('gd_info') && phpversion() < '4.3') {
			$gd_check .= 'php�汾����4.3.0����֧�ָ߰汾��gd�⣬����������php�汾.';
		} else {
			$ver_info = gd_info();
			preg_match('/([0-9\.]+)/', $ver_info['GD Version'], $match);
			if($match[0] < '2.0') {
				$gd_check .= "\n\t\tgd�汾����2.0,����������gd�汾��֧��gd����֤���ˮӡ.";
			} elseif(!(function_exists('imagecreatefromjpeg') && function_exists('imagejpeg')) ) {
				$gd_check .= "\n\t\tgd�汾��֧��jpeg����֤���ˮӡ.";
			} elseif(!(function_exists('imagecreatefromgif') && function_exists('imagegif')) ) {
				$gd_check .= "\n\t\tgd�汾��֧��gif����֤���ˮӡ.";
			} elseif(!(function_exists('imagecreatefrompng') && function_exists('imagepng')) ) {
				$gd_check .= "\n\t\tgd�汾��֧��png����֤���ˮӡ.";
			} else {
				$gd_check .= '��������';
			}
		}
		if($gd_check != '��������') {
			$gd_check = $error_style_s.$gd_check.$style_e;
		} else {
			$gd_check = $ok_style_s.$gd_check.$style_e;
		}

		/**
		 * ���ming�⣬Ŀ��Ϊ����Ƿ�֧��flash��֤��
		 */
		 $ming_check = '';
		if(extension_loaded('ming')) {
			if(substr($curr_os,0,3) == 'WIN') {
				$ming_check .= '����php.iniδ����extension=php_ming.dll�������޷�֧��flash��֤��';
			} else {
				$ming_check .= '��δ����ming�⣬�����޷�֧��flash��֤��';
			}
		} else {
			$ming_check .= '����ϵͳ֧��flash��֤�룬������޷�ʹ��flash��֤��Ļ����п���������php�汾̫��';
		}

		/**
		 *���ϵͳ�Ƿ����ִ��ImageMagick������
		 */
		 $imagemagick_check = '';
		if(!function_exists('exec')) {
			$imagemagick_check .='����php.ini����߿ռ��̽�ֹ��ʹ��exec�������޷�ʹ��ImageMagick';
		} else {
			$imagemagick_check .='������ֻ�谲װ��ImageMagick��Ȼ�����ú���ز����Ϳ���ʹ��ImageMagick(ʹ��֮ǰ����ʹ�ú�̨��Ԥ���������������ImageMagick�Ƿ�װ��)';
		}
		if($msg == '') {
			$msg = "{$ok_style_s}û�з���ϵͳ��������.{$style_e}";
		} else {
			$msg = $error_style_s.$msg.$style_e;
		}
			$doctor_env = "
	����ϵͳ--->$curr_os

	WEB ���� --->$curr_server_software

	PHP �汾--->$curr_php_version

	MySQL �汾--->$curr_mysql

	Zend �汾--->".OPTIMIZER_VERSION."

	���������ʱ��(max_execution_time)--->{$max_execution_time}��

	�ڴ��С(memory_limit)--->$memory_limit

	�Ƿ������Զ���ļ�(allow_url_fopen)--->$allow_url_fopen

	�Ƿ�����ʹ�ö̱��(short_open_tag)--->$curr_short_tag

	��ȫģʽ(safe_mode)--->$curr_safe_mode

	������ʾ(display_errors)--->$curr_display_errors

	XML ������--->$curr_xml

	authkey �Ƿ����--->$authkeyexist

	ϵͳ��ʱĿ¼--->$tmpwritable

	���̿ռ�--->$curr_disk_space

	�����ϴ�--->$curr_upload_status

	���� file()--->$funcexistfile

	���� fopen()--->$funcexistfopen

	Ŀ¼Ȩ��---$dir_perm

	GD ��--->$gd_check

	ming ��--->$ming_check

	ImageMagick --->$imagemagick_check

	ϵͳ����������ʾ\r\n\t$msg";
	}
	if(!$doctor_step) {
		$doctor_step = '0';
		@unlink('./forumdata/doctor_cache.cache');
	}
	//php������
				$dberrnomsg = array (
					'1008' => '���ݿⲻ���ڣ�ɾ�����ݿ�ʧ��',
					'1016' => '�޷��������ļ�',
					'1041' => 'ϵͳ�ڴ治��',
					'1045' => '�������ݿ�ʧ�ܣ��û������������',
					'1046' => 'ѡ�����ݿ�ʧ�ܣ�����ȷ�������ݿ�����',
					'1044' => '��ǰ�û�û�з������ݿ��Ȩ��',
					'1048' => '�ֶβ���Ϊ��',
					'1049' => '���ݿⲻ����',
					'1051' => '���ݱ�����',
					'1054' => '�ֶβ�����',
					'1062' => '�ֶ�ֵ�ظ������ʧ��',//���ж�
					'1064' => '����ԭ��1.���ݳ��������Ͳ�ƥ�䣻2.���ݿ��¼�ظ�',//���ж�
					'1065' => '��Ч��SQL��䣬SQL���Ϊ��',//���ж�
					'1081' => '���ܽ���Socket����',
					'1129' => '���ݿ�����쳣�����������ݿ�',
					'1130' => '�������ݿ�ʧ�ܣ�û���������ݿ��Ȩ��',
					'1133' => '���ݿ��û�������',
					'1141' => '��ǰ�û���Ȩ�������ݿ�',
					'1142' => '��ǰ�û���Ȩ�������ݱ�',
					'1143' => '��ǰ�û���Ȩ�������ݱ��е��ֶ�',
					'1146' => '���ݱ�����',
					'1149' => 'SQL����﷨����',
					'1169' => '�ֶ�ֵ�ظ������¼�¼ʧ��',//���ж�
					'2003' => '�������ݿ�������˿������Ƿ���ȷ��Ĭ�϶˿�Ϊ 3306',
					'2005' => '���ݿ������������',
					'1114' => 'Forum onlines reached the upper limit',
				);

	$display_errorall = '';
	$tempdir = $phpfile_array[$doctor_step];
	$dirname = $dir_array[$doctor_step];
	//foreach($phpfile_array as $tempdir=>$dirname) {
		$display_error = '';
		$mtime = explode(' ', microtime());
		$time_start = $mtime[1] + $mtime[0];
		if(!in_array($tempdir, array('templates', 'cache', 'discuzroot'))) exit('��������');

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
								$display_error .= "\t{$error_style_s}$file ---����:{$style_e}";
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
				echo "$dirĿ¼�����ڻ򲻿ɶ�ȡ.";
		   }
		}
		@unlink('./forumdata/checkfile.php');
		if($display_error == '') {
			$dot = '�����ļ�';
			$dir == './' && $dot = 'php�ļ�';
			$display_errorall .= "\n---------{$ok_style_s}{$dirname}{$style_e}��û�м�⵽�д����$dot.\n";
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
	$records_style = "\n\n==={$title_style_s}�����ļ����{$title_style_e}=================================================$doctor_config\n==={$title_style_s}ϵͳ�������{$title_style_e}=================================================\n$doctor_env\n==={$title_style_s}�ļ�������{$title_style_e}=================================================\n$display_errorall\n==={$title_style_s}������{$title_style_e}=====================================================";
	$search_style_all = array($error_style_s, $style_e, $ok_style_s, $title_style_s, $title_style_e);
	$replace_style_all = array('', '', '', '', '');
	$records = str_replace($search_style_all, '', $records_style);
	echo "<h4>��̳ҽ����Ͻ��</h4><br /><p id=records style=\"display:\"><textarea name=\"contents\" readonly=\"readonly\">$records</textarea><br><br><input value=\"��̳��ʽ����\" onclick=\"records.style.display='none';records_style.style.display='';\"  type=\"button\">  <input value=\"�����븴�Ƶ��ҵļ��а�\" onclick=\"copytoclip($('contents'))\" type=\"button\"></p>
	<p id=records_style style=\"display:none\"><textarea name=\"contents_style\" readonly=\"readonly\">$records_style</textarea><br><br><input value=\"�����ʽ����\" onclick=\"records_style.style.display='none';records.style.display='';\"  type=\"button\"> <input value=\"�����븴�Ƶ��ҵļ��а�\" onclick=\"copytoclip($('contents_style'))\" type=\"button\"></p>
	";
	htmlfooter();
} elseif ($action == 'filecheck') {
	if(!file_exists("./config.inc.php") && !file_exists("config.php")){
		htmlheader();
		cexit("<h4>�����ϴ�config�ļ��Ա�֤�������ݿ����������ӣ�</h4>");
	}
	require_once './include/common.inc.php';

	@set_time_limit(0);

	$do = isset($do) ? $do : 'advance';

	$lang = array(
		'filecheck_fullcheck' => '����δ֪�ļ�',
		'filecheck_fullcheck_select' => '����δ֪�ļ� - ѡ����Ҫ������Ŀ¼',
		'filecheck_fullcheck_selectall' => '[����ȫ��Ŀ¼]',
		'filecheck_fullcheck_start' => '��ʼʱ��:',
		'filecheck_fullcheck_current' => '��ǰʱ��:',
		'filecheck_fullcheck_end' => '����ʱ��:',
		'filecheck_fullcheck_file' => '��ǰ�ļ�:',
		'filecheck_fullcheck_foundfile' => '����δ֪�ļ���: ',
		'filecheck_fullcheck_nofound' => 'û�з����κ�δ֪�ļ�'
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

		echo '<h4>����δ֪�ļ�</h4>
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
	errorpage("<h6>���ѳɹ��˳�,��ӭ�´�ʹ��.ǿ�ҽ������ڲ�ʹ��ʱɾ�����ļ�.</h6>");
} elseif ($action == 'mysqlclear') {
	ob_implicit_flush();

	define('IN_DISCUZ', TRUE);
	if(@!include("./config.inc.php")) {
		if(@!include("./config.php")) {
			htmlheader();
			cexit("<h4>�����ϴ�config�ļ��Ա�֤�������ݿ����������ӣ�</h4>");
		}
	}
	require './include/db_'.$database.'.class.php';

	$db = new dbstuff;
	$db->connect($dbhost, $dbuser, $dbpw, $dbname, $pconnect);
	$db->select_db($dbname);

	if(!get_cfg_var('register_globals')) {
		@extract($_GET, EXTR_SKIP);
	}

	$rpp			=	"1000"; //ÿ�δ������������
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
						'1'=>'����ظ���������',
						'2'=>'���฽����������',
						'3'=>'�����Ա��������',
						'4'=>'��������������',
						'5'=>'���������������',
						'6'=>'������Ϣ����',
						'7'=>'���������������'
					);
	$steps			=	count($action);
	$actionnow		=	isset($action[$step]) ? $action[$step] : '����';
	$maxid			=	isset($maxid) ? $maxid : 0;
	$tableid		=	isset($tableid) ? $tableid : 1;

	htmlheader();
	if($step==0){
	?>
		<h4>���ݿ�������������</h4>
		<h5>������Ŀ��ϸ��Ϣ</h5>
		<table>
		<tr><th width="30%">Posts�������</th><td>[<a href="?action=mysqlclear&step=1&stay=1">��������</a>]</td></tr>
		<tr><th width="30%">Attachments�������</th><td>[<a href="?action=mysqlclear&step=2&stay=1">��������</a>]</td></tr>
		<tr><th width="30%">Members�������</th><td>[<a href="?action=mysqlclear&step=3&stay=1">��������</a>]</td></tr>
		<tr><th width="30%">Forums�������</th><td>[<a href="?action=mysqlclear&step=4&stay=1">��������</a>]</td></tr>
		<tr><th width="30%">Pms�������</th><td>[<a href="?action=mysqlclear&step=5&stay=1">��������</a>]</td></tr>
		<tr><th width="30%">Threads�������</th><td>[<a href="?action=mysqlclear&step=6&stay=1">��������</a>]</td></tr>
		<tr><th width="30%">���б������</th><td>[<a href="?action=mysqlclear&step=1&stay=0">ȫ������</a>]</td></tr>
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
					$attachment = $db->num_rows($query) ? 1 : 0;//�޸�����
					$query  = $db->query("SELECT pid, subject, rate FROM {$tablepre}posts WHERE tid='".$threads['tid']."' AND invisible='0' ORDER BY dateline LIMIT 1");
					$firstpost = $db->fetch_array($query);
					$firstpost['subject'] = addslashes($firstpost['subject']);
					@$firstpost['rate'] = $firstpost['rate'] / abs($firstpost['rate']);//�޸�����
					$query  = $db->query("SELECT author, dateline FROM {$tablepre}posts WHERE tid='".$threads['tid']."' AND invisible='0' ORDER BY dateline DESC LIMIT 1");
					$lastpost = $db->fetch_array($query);//�޸������
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
		echo '<h4>���ݿ�������������</h4><table>
			  <tr><th>���������������</th></tr><tr>
			  <td><br>������������������.&nbsp;������<font color=red>'.$allconvertedrows.'</font>������.<br><br></td></tr></table>';

	}
	htmlfooter();
} elseif ($action == 'repair_auto') {
	if(@!include("./config.inc.php")) {
		if(@!include("./config.php")) {
			htmlheader();
			cexit("<h4>�����ϴ�config�ļ��Ա�֤�������ݿ����������ӣ�</h4>");
		}
	}
	htmlheader();
	echo '<h4>Discuz! �������ֶ��޸� </h4>';
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
			errorpage("<h4>�ܱ�Ǹ���ù���Ŀǰֻ֧��Discuz!5.5�汾��Discuz!6.0�汾��</h4>",'',0);
		}
	}else {
		errorpage("./discuz_version.php�ļ������ڣ���ȷ�����ļ��Ĵ��ڡ�",'',0);
	}

	echo '<h5>�����</h5>
	<table>
		<tr><th width="25%">���ݱ���</th><th width="25%">�ֶ���</th><th width="25%">������״̬</th></tr>';
	foreach($querysql as $key => $keyfield) {
		$tablestate = '����';
		echo '<tr><td width="25%">'.$tablepre.$key.'</td><td width="25%">'.$keyfield.'</td>';
		if($query = @mysql_query("Describe $tablepre$key $keyfield")) {
			if(@mysql_num_rows($query) > 0) {
				$field = @mysql_fetch_array($query);
				if($field[3] != 'PRI') {
					@mysql_query("ALTER TABLE $tablepre$key ADD PRIMARY KEY ($keyfield)");
					$tablestate = '<font color="green"><b>�Ѿ��޸�</b></font>';
				}
				if(empty($field[5])) {
					mysql_query("ALTER TABLE $tablepre$key CHANGE $keyfield $keyfield $field[1] NOT NULL AUTO_INCREMENT");
					$tablestate = '<font color="green"><b>�Ѿ��޸�</b></font>';
				}
			} else {
				$tablestate = '<font color="red">�ֶβ�����</font>';
			}
		} else {
			$tablestate = '<font color="red">������</font>';
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
			cexit("<h4>�����ϴ������°汾�ĳ����ļ��������б���������</h4>");
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
	<h4>���ݿ�ָ�ʵ�ù��� </h4>

	<?php
	echo "<div class=\"specialdiv\">������ʾ��<ul>
		<li>ֻ�ָܻ�����ڷ�����(Զ�̻򱾵�)�ϵ������ļ�,����������ݲ��ڷ�������,���� FTP �ϴ�</li>
		<li>�����ļ�����Ϊ Discuz! ������ʽ,��������Ӧ����ʹ PHP �ܹ���ȡ</li>
		<li>�뾡��ѡ�����������ʱ�β���,�Ա��ⳬʱ.����򳤾�(���� 10 ����)����Ӧ,��ˢ��</li></ul></div>";

	if($file) {
		if(strtolower(substr($file, 0, 7)) == "http://") {
			echo "��Զ�����ݿ�ָ����� - ��ȡԶ������:<br><br>";
			echo "��Զ�̷�������ȡ�ļ� ... ";

			$sqldump = @fread($fp, 99999999);
			@fclose($fp);
			if($sqldump) {
				echo "�ɹ�<br><br>";
			} elseif (!$multivol) {
				cexit("ʧ��<br><br><b>�޷��ָ�����</b>");
			}
		} else {
			echo "<div class=\"specialtext\">�ӱ��ػָ����� - ��������ļ�:<br><br>";
			if(file_exists($file)) {
				echo "�����ļ� $file ���ڼ�� ... �ɹ�<br><br>";
			} elseif (!$multivol) {
				cexit("�����ļ� $file ���ڼ�� ... ʧ��<br><br><br><b>�޷��ָ�����</b></div>");
			}

			if(is_readable($file)) {
				echo "�����ļ� $file �ɶ���� ... �ɹ�<br><br>";
				@$fp = fopen($file, "r");
				@flock($fp, 3);
				$sqldump = @fread($fp, filesize($file));
				@fclose($fp);
				echo "�ӱ��ض�ȡ���� ... �ɹ�<br><br>";
			} elseif (!$multivol) {
				cexit("�����ļ� $file �ɶ���� ... ʧ��<br><br><br><b>�޷��ָ�����</b></div>");
			}
		}

		if($multivol && !$sqldump) {
			cexit("�־��ݷ�Χ��� ... �ɹ�<br><br><b>��ϲ��,�����Ѿ�ȫ���ɹ��ָ�!��ȫ���,�����ɾ��������.</b></div>");
		}

		echo "�����ļ� $file ��ʽ��� ... ";
		@list(,,,$method, $volume) = explode(',', base64_decode(preg_replace("/^# Identify:\s*(\w+).*/s", "\\1", preg_replace("/^(.+)/", "\\1", substr($sqldump, 0, 256)))));
		if($method == 'multivol' && is_numeric($volume)) {
			echo "�ɹ�<br><br>";
		} else {
			cexit("ʧ��<br><br><b>���ݷ� Discuz! �־��ݸ�ʽ,�޷��ָ�</b></div>");
		}

		if($onlysave == "yes") {
			echo "�������ļ����浽���ط����� ... ";
			$filename = DISCUZ_ROOT.'./forumdata'.strrchr($file, "/");
			@$filehandle = fopen($filename, "w");
			@flock($filehandle, 3);
			if(@fwrite($filehandle, $sqldump)) {
				@fclose($filehandle);
				echo "�ɹ�<br><br>";
			} else {
				@fclose($filehandle);
				die("ʧ��<br><br><b>�޷���������</b>");
			}
			echo "�ɹ�<br><br><b>��ϲ��,�����Ѿ��ɹ����浽���ط����� <a href=\"".strstr($filename, "/")."\">$filename</a>.��ȫ���,�����ɾ��������.</b></div>";
		} else {
			$sqlquery = splitsql($sqldump);
			echo "��ֲ������ ... �ɹ�<br><br>";
			unset($sqldump);

			echo "���ڻָ�����,��ȴ� ... </div>";
			foreach($sqlquery as $sql) {
				$sql = syntablestruct(trim($sql), $db->version() > '4.1', $dbcharset);
				if(trim($sql)) {
					@$db->query($sql);
				}
			}
		if($auto == 'off'){
			$nextfile = str_replace("-$volume.sql", '-'.($volume + 1).'.sql', $file);
			cexit("<ul><li>�����ļ� <b>$volume#</b> �ָ��ɹ�,�������Ҫ������ָ������������ļ�</li><li>����<b><a href=\"?action=restore&file=$nextfile&multivol=yes\">ȫ���ָ�</a></b>	�������ָ���һ�������ļ�<b><a href=\"?action=restore&file=$nextfile&multivol=yes&auto=off\">�����ָ���һ�����ļ�</a></b></li></ul>");
		} else {
			$nextfile = str_replace("-$volume.sql", '-'.($volume + 1).'.sql', $file);
			echo "<ul><li>�����ļ� <b>$volume#</b> �ָ��ɹ�,���ڽ��Զ����������־�������.</li><li><b>����ر���������жϱ���������</b></li></ul>";
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

			$exportinfo = '<h5>���ݱ�����Ϣ</h5>
	<table>
	<caption>&nbsp;&nbsp;&nbsp;���ݿ��ļ���</caption>
	<tr>
	<th>������Ŀ</th><th>�汾</th>
	<th>ʱ��</th><th>����</th>
	<th>�鿴</th><th>����</th></tr>';
			foreach($exportlog as $dateline => $info) {
				$info['dateline'] = is_int($dateline) ? gmdate("Y-m-d H:i", $dateline + 8*3600) : 'δ֪';
					switch($info['type']) {
						case 'full':
							$info['type'] = 'ȫ������';
							break;
						case 'standard':
							$info['type'] = '��׼����(�Ƽ�)';
							break;
						case 'mini':
							$info['type'] = '��С����';
							break;
						case 'custom':
							$info['type'] = '�Զ��屸��';
							break;
					}
				$info['volume'] = $info['method'] == 'multivol' ? $info['volume'] : '';
				$info['method'] = $info['method'] == 'multivol' ? '���' : 'shell';
				$info['url'] = str_replace(".sql", '', str_replace("-$info[volume].sql", '', substr(strrchr($info['filename'], "/"), 1)));
				$exportinfo .= "<tr>\n".
					"<td>".$info['url']."</td>\n".
					"<td>$info[version]</td>\n".
					"<td>$info[dateline]</td>\n".
					"<td>$info[type]</td>\n";
				if($info['bakentry']){
				$exportinfo .= "<td><a href=\"?action=restore&bakdirname=".$info['url']."\">�鿴</a></td>\n".
					"<td><a href=\"?action=restore&file=$info[bakentry]&importsubmit=yes\">[ȫ������]</a></td>\n</tr>\n";
				} else {
				$exportinfo .= "<td><a href=\"?action=restore&filedirname=".$info['url']."\">�鿴</a></td>\n".
					"<td><a href=\"?action=restore&file=$info[filename]&importsubmit=yes\">[ȫ������]</a></td>\n</tr>\n";
				}
			}
		$exportinfo .= '</table>';
		echo $exportinfo;
		unset($exportlog);
		unset($exportinfo);
		echo "<br>";
	//��ǰ�汾�����õ��ı������
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
								<caption>&nbsp;&nbsp;&nbsp;���ݿ��ļ��б�</caption>
								<tr>
								<th>�ļ���</th><th>�汾</th>
								<th>ʱ��</th><th>����</thd>
								<th>��С</th><td>��ʽ</th>
								<th>���</th><th>����</th></tr>';
				foreach($exportlog as $dateline => $info) {
					$info['dateline'] = is_int($dateline) ? gmdate("Y-m-d H:i", $dateline + 8*3600) : 'δ֪';
						switch($info['type']) {
							case 'full':
								$info['type'] = 'ȫ������';
								break;
							case 'standard':
								$info['type'] = '��׼����(�Ƽ�)';
								break;
							case 'mini':
								$info['type'] = '��С����';
								break;
							case 'custom':
								$info['type'] = '�Զ��屸��';
								break;
						}
					$info['volume'] = $info['method'] == 'multivol' ? $info['volume'] : '';
					$info['method'] = $info['method'] == 'multivol' ? '���' : 'shell';
					$exportinfo .= "<tr>\n".
						"<td><a href=\"$info[filename]\" name=\"".substr(strrchr($info['filename'], "/"), 1)."\">".substr(strrchr($info['filename'], "/"), 1)."</a></td>\n".
						"<td>$info[version]</td>\n".
						"<td>$info[dateline]</td>\n".
						"<td>$info[type]</td>\n".
						"<td>".get_real_size($info[size])."</td>\n".
						"<td>$info[method]</td>\n".
						"<td>$info[volume]</td>\n".
						"<td><a href=\"?action=restore&file=$info[filename]&importsubmit=yes&auto=off\">[����]</a></td>\n</tr>\n";
				}
			$exportinfo .= '</table>';
			echo $exportinfo;
		}
	// 5.5�汾�õ�����ϸ�������
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
					<caption>&nbsp;&nbsp;&nbsp;���ݿ��ļ��б�</caption>
					<tr>
					<th>�ļ���</th><th>�汾</th>
					<th>ʱ��</th><th>����</th>
					<th>��С</th><th>��ʽ</th>
					<th>���</th><th>����</th></tr>';
			foreach($exportlog as $dateline => $info) {
				$info['dateline'] = is_int($dateline) ? gmdate("Y-m-d H:i", $dateline + 8*3600) : 'δ֪';
				switch($info['type']) {
					case 'full':
						$info['type'] = 'ȫ������';
						break;
					case 'standard':
						$info['type'] = '��׼����(�Ƽ�)';
						break;
					case 'mini':
						$info['type'] = '��С����';
						break;
					case 'custom':
						$info['type'] = '�Զ��屸��';
						break;
				}
				$info['volume'] = $info['method'] == 'multivol' ? $info['volume'] : '';
				$info['method'] = $info['method'] == 'multivol' ? '���' : 'shell';
				$exportinfo .= "<tr>\n".
						"<td><a href=\"$info[filename]\" name=\"".substr(strrchr($info['filename'], "/"), 1)."\">".substr(strrchr($info['filename'], "/"), 1)."</a></td>\n".
						"<td>$info[version]</td>\n".
						"<td>$info[dateline]</td>\n".
						"<td>$info[type]</td>\n".
						"<td>".get_real_size($info[size])."</td>\n".
						"<td>$info[method]</td>\n".
						"<td>$info[volume]</td>\n".
						"<td><a href=\"?action=restore&file=$info[filename]&importsubmit=yes&auto=off\">[����]</a></td>\n</tr>\n";
			}
			$exportinfo .= '</table>';
			echo $exportinfo;
		}
		echo "<br>";
		cexit("");
	}
} elseif ($action == 'replace') {
	htmlheader();
	$rpp			=	"500"; //ÿ�δ������������
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
		echo "<h4>�������������滻</h4><table>
					<tr>
						<th>��ͣ�滻</th>
					</tr>";
		$threads_banned > 0 && print("<tr><td><br><li>".$threads_banned."�����ⱻ�������վ.</li><br></td></tr>");
		$threads_mod > 0 && print("<tr><td><br><li>".$threads_mod."�����ⱻ��������б�.</li><br></td></tr>");
		$posts_mod > 0 && print("<tr><td><br><li>".$posts_mod."���ظ�����������б�.</li><br></td></tr>");
		echo "<tr><td><br><li>�滻��".$convertedrows."������</li><br><br></td></tr>";
		echo "<tr><td><br><a href='?action=replace&step=".$step."&start=".($end + 1 - $rpp * 2)."&stay=$stay&totalrows=$totalrows&convertedrows=$convertedrows&maxid=$maxid&replacesubmit=1&threads_banned=$threads_banned&threads_mod=$threads_mod&posts_mod=$posts_mod'>����</a><br><br></td></tr>";
		echo "</table>";
		htmlfooter();
	}
	ob_implicit_flush();
	define('IN_DISCUZ', TRUE);
	if(@!include("./config.inc.php")) {
		if(@!include("./config.php")) {
			cexit("<h4>�����ϴ�config�ļ��Ա�֤�������ݿ����������ӣ�</h4>");
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
				echo "<h4>�������������滻</h4><table><tr><th>��ʾ��Ϣ</th></tr><tr><td>����û��ѡ��Ҫ���˵Ĵ���. &nbsp [<a href=tools.php?action=replace>����</a>]</td></tr></table>";
				htmlfooter();
			} else {
				$fp = @fopen($selectwords_cache,w);
				$content = "<?php \n";
				$selectwords = implode(',',$selectwords);
				$content .= "\$selectwords = '$selectwords';\n?>";
				if(!@fwrite($fp,$content)) {
					echo "д�뻺���ļ�$selectwords_cache ����,��ȷ��·���Ƿ��д. &nbsp [<a href=tools.php?action=replace>����</a>]";
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
		$query = $db->query("SELECT find,replacement from {$tablepre}words where id in($selectwords)");//������й���{BANNED}�Ż���վ {MOD}�Ž�����б�
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
		function topattern_array($source_array) { //����������
			$source_array = preg_replace("/\{(\d+)\}/",".{0,\\1}",$source_array);
			foreach($source_array as $key => $value) {
				$source_array[$key] = '/'.$value.'/i';
			}
			return $source_array;
		}
		$array_find = topattern_array($array_find);
		$array_findmod = topattern_array($array_findmod);
		$array_findbanned = topattern_array($array_findbanned);

		//��ѯposts��׼���滻
		$sql = "SELECT pid, tid, first, subject, message from {$tablepre}posts where pid >= $start and pid <= $end";
		$query = $db->query($sql);
		while($row = $db->fetch_array($query)) {
			$pid = $row['pid'];
			$tid = $row['tid'];
			$subject = $row['subject'];
			$message = $row['message'];
			$first = $row['first'];
			$displayorder = 0;//  -2��� -1����վ
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
				if($displayorder == '-2' && $first == 0) {//��������Ƶ���˻ظ�
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
			echo "<h4>�������������滻</h4><table>
						<tr>
							<th>�����滻���</th>
						</tr>";
			$threads_banned > 0 && print("<tr><td><br><li>".$threads_banned."�����ⱻ�������վ.</li><br></td></tr>");
			$threads_mod > 0 && print("<tr><td><br><li>".$threads_mod."�����ⱻ��������б�.</li><br></td></tr>");
			$posts_mod > 0 && print("<tr><td><br><li>".$posts_mod."���ظ�����������б�.</li><br></td></tr>");
			echo "<tr><td><br><li>�滻��".$convertedrows."������</li><br><br></td></tr>";
			echo "</table>";
			@unlink($selectwords_cache);
		}
	} else {
		$query = $db->query("select * from {$tablepre}words");
		$i = 1;
		if($db->num_rows($query) < 1) {
			echo "<h4>�������������滻</h4><table><tr><th>��ʾ��Ϣ</th></tr><tr><td><br>�Բ���,���ڻ�û�й��˹���,��<a href=\"./admincp.php?action=censor\" target='_blank'>������̳��̨����</a>.<br><br></td></tr></table>";
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
				<h4>�����滻��������</h4>
				<table>
					<tr>
						<th><input class="checkbox" name="chkall" onclick="checkall(this.form)" type="checkbox" checked>���</th>
						<th>��������</th>
						<th>�滻Ϊ</th></tr>
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
				<input type="submit" name=replacesubmit value="��ʼ�滻">
		</form>
	<div class="specialdiv">
	<h6>ע�⣺</h6>
	<ul>
	<li>������ᰴ����̳���й��˹������������������.�����޸���<a href="./admincp.php?action=censor" target='_blank'>����̳��̨</a>��</li>
	<li>�ϱ��г�������̳��ǰ�Ĺ��˴���.</li>
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
			$clearmsg .= './forumdata/'.$dir.'���ʧ��.<br>';
		}
	}
	htmlheader();
	echo '<h4>���»���</h4><table><tr><th>��ʾ��Ϣ</th></tr><tr><td>';
	if($clearmsg == '') $clearmsg = '���»������.';
	echo $clearmsg.'</td></tr></table>';
	htmlfooter();
} elseif ($action == 'runquery') {
	if(!file_exists("./config.inc.php") && !file_exists("config.php")){
		htmlheader();
		cexit("<h4>�����ϴ�config�ļ��Ա�֤�������ݿ����������ӣ�</h4>");
	}
	define('IN_DISCUZ',TRUE);
	require_once "./include/common.inc.php";
	if($admincp['runquery'] != 1) {
		errorpage('ʹ�ô˹�����Ҫ�� config.inc.php ���е� $admincp[\'runquery\'] �����޸�Ϊ 1��','���ݿ�����');
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

		errorpage($sqlerror? $sqlerror : "���ݿ������ɹ�,Ӱ������: &nbsp;$affected_rows",'���ݿ�����');
		if(strpos($queries,'settings')) {
			require_once './include/cache.func.php';
			updatecache('settings');
		}
		}
		htmlheader();
		echo "<h4>���ݿ�����</h4>
		<form method=\"post\" action=\"tools.php?action=runquery\">
		<h5>�뽫���ݿ��������ճ��������</h4>
    		<select name=\"queryselect\" onChange=\"queries.value = this.value\">
			<option value = ''>��ѡ��TOOLS�����������</option>
			<option value = \"REPLACE INTO ".$tablepre."settings (variable, value) VALUES ('seccodestatus', '0')\">�ر�������֤�빦��</option>
			<option value = \"REPLACE INTO ".$tablepre."settings (variable, value) VALUES ('supe_status', '0')\">�ر���̳�е�supersite����</option>
		</select>
		<br />
		<br /><textarea name=\"queries\">$queries</textarea><br />
		<input type=\"submit\" name=\"sqlsubmit\" value=\"�� &nbsp; ��\">
		</form>";
	}
	htmlfooter();
} elseif ($action == 'setadmin') {
	$info = "������Ҫ���óɹ���Ա���û���";
	htmlheader();
	?>
	<h4>���ù���Ա�ʺ�</h4>

	<?php

	if(!empty($_POST['loginsubmit'])){
		require './config.inc.php';
		mysql_connect($dbhost, $dbuser, $dbpw);
		mysql_select_db($dbname);
		$passwordsql = empty($_POST['password']) ? '' : ', password = \''.md5($_POST['password']).'\'';
		$passwordsql .= empty($_POST['issecques']) ? '' : ', secques = \'\'';
		$passwordinfo = empty($_POST['password']) ? '���뱣�ֲ���' : '�����������޸�Ϊ '.$_POST['password'].'';
		$query = "SELECT uid from {$tablepre}members WHERE $_POST[loginfield] = '$_POST[username]'";
		if(@mysql_num_rows(mysql_query($query)) < 1){
				$info = '<font color="red">�޴��û��������û����Ƿ���ȷ��</font>��<a href="?action=setadmin">��������</a> ��������ע��.<br><br>';
		} else {
			$query = "UPDATE {$tablepre}members SET adminid='1', groupid='1' $passwordsql WHERE $_POST[loginfield] = '$_POST[username]' limit 1";
			if(mysql_query($query)){
				$mysql_affected_rows = mysql_affected_rows();
				$_POST[loginfield] = $_POST[loginfield] == 'username' ? '�û���' : 'UID����';
				$info = "�ѽ�$_POST[loginfield]Ϊ $_POST[username] ���û����óɹ���Ա��$passwordinfo<br><br>";
			} else {
				$info = '<font color="red">ʧ������Mysql����config.inc.php</font>';
			}
		}

	?>
	<form action="?action=setadmin" method="post"><input type="hidden" name="action" value="login" />
	<?
		errorpage($info,'���ù���Ա�ʺ�',0,0);
	?>
	</form>
	<?php
	} else {?>
	<form action="?action=setadmin" method="post">
	<h5><?=$info?></h5>
		<table>
			<tr><th width="30%"><input class="radio" type="radio" name="loginfield" value="username" checked class="radio">�û���<input class="radio" type="radio" name="loginfield" value="uid" class="radio">UID</th><td width="70%"><input class="textinput" type="text" name="username" size="25" maxlength="40"></td></tr>
			<tr><th width="30%">����������</th><td width="70%"><input class="textinput" type="text" name="password" size="25"></td></tr>
			<tr><th width="30%">�Ƿ������ȫ����</th><td width="70%"><input class="radio" type="radio" name="issecques" value="1" checked >��&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input class="radio" type="radio" name="issecques" value="" class="radio">��</td></tr>
		</table>
		<input type="submit" name="loginsubmit" value="�� &nbsp; ��">
	</form>
	<?php
	}
	specialdiv();
	htmlfooter();
} elseif ($action == 'setlock') {
	touch($lockfile);
	if(file_exists($lockfile)) {
		echo '<meta http-equiv="refresh" content="3 url=?">';
		errorpage("<h6>�ɹ��رչ����䣡ǿ�ҽ������ڲ���Ҫ�������ʱ��ʱ����ɾ��</h6>",'����������');
	} else {
		errorpage('ע������Ŀ¼û��д��Ȩ�ޣ������޷������ṩ��ȫ���ϣ���ɾ����̳��Ŀ¼�µ�tool.php�ļ���','����������');
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

			$msg = '<font color="red">���ñ�����ϣ�</font>';

			if($_POST['sendtest']) {

				define('IN_DISCUZ', true);

				define('DISCUZ_ROOT', './');
				define('TPLDIR', './templates/default');
				require './include/global.func.php';

				$test_tos = explode(',', $_POST['mailcfg_new']['test_to']);
				$date = date('Y-m-d H:i:s');

				switch($_POST['mailsend_new']) {
					case 1:
						$title = '��׼��ʽ���� Email';
						$message = "ͨ�� PHP ������ UNIX sendmail ����\n\n���� {$_POST['mailcfg_new']['test_from']}\n\n����ʱ�� ".$date;
						break;
					case 2:
						$title = 'ͨ�� SMTP ������(SOCKET)���� Email';
						$message = "ͨ�� SOCKET ���� SMTP ����������\n\n���� {$_POST['mailcfg_new']['test_from']}\n\n����ʱ�� ".$date;
						break;
					case 3:
						$title = 'ͨ�� PHP ���� SMTP ���� Email';
						$message = "ͨ�� PHP ���� SMTP ���� Email\n\n���� {$_POST['mailcfg_new']['test_from']}\n\n����ʱ�� ".$date;
						break;
				}

				$bbname = '�ʼ���������';
				sendmail($test_tos[0], $title.' @ '.$date, "$bbname\n\n\n$message", $_POST['mailcfg_new']['test_from']);
				$bbname = '�ʼ�Ⱥ������';
				sendmail($_POST['mailcfg_new']['test_to'], $title.' @ '.$date, "$bbname\n\n\n$message", $_POST['mailcfg_new']['test_from']);

				$msg = '���ñ�����ϣ�<br>����Ϊ��'.$title.' @ '.$date.'���Ĳ����ʼ��Ѿ�������';

			}

		} else {
			$msg = '�޷�д���ʼ������ļ� ./mail_config.inc.php��Ҫʹ�ñ����������ô��ļ��Ŀ�д��Ȩ�ޡ�';
		}
	}

	define('IN_DISCUZ', TRUE);

	if(@include("./discuz_version.php")) {
		if(substr(DISCUZ_VERSION, 0, 1) >= 6) {
			errorpage('�������Ѿ��ƶ���Disuz!��̳��̨�����е��ʼ�����.&nbsp; <a href="./admincp.php?action=settings&do=mail" target="_blank">������̳��̨</a>.','�ʼ�����/���Թ���');
		}
	} else {
		errorpage("./discuz_version.php�ļ������ڣ���ȷ�����ļ��Ĵ��ڡ�",'�ʼ�����/���Թ���');
	}
	htmlheader();
	@include './mail_config.inc.php';
	?>
	<script>
	function $(id) {
		return document.getElementById(id);
	}
	</script>
	<h4>�ʼ�����/���Թ���</h4>
	<?

	if($msg) {
		errorpage($msg,'�ʼ�����/���Թ���',0,0);
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

	echo '<tr><th width="30%">�����ʼ������е�ȫ��������ʾ</th><td>';
	echo ' <input class="checkbox" type="checkbox" name="sendmail_silent_new" value="1"'.($sendmail_silent ? ' checked' : '').'>';
	echo '</td></tr>';
	echo '<tr><th>�ʼ�ͷ�ķָ���</th><td>';
	echo ' <input class="radio" type="radio" name="maildelimiter_new" value="1"'.($maildelimiter ? ' checked' : '').'> ʹ�� CRLF ��Ϊ�ָ���<br>';
	echo ' <input class="radio" type="radio" name="maildelimiter_new" value="0"'.(!$maildelimiter ? ' checked' : '').'> ʹ�� LF ��Ϊ�ָ���';
	echo '</td></tr>';
	echo '<tr><th>�ռ����а����û���</th><td>';
	echo ' <input class="checkbox" type="checkbox" name="mailusername_new" value="1"'.($mailusername ? ' checked' : '').'>';
	echo '</td></tr>';

	echo '<tr><th>�ʼ����ͷ�ʽ</th><td>';
	echo ' <input class="radio" type="radio" name="mailsend_new" value="1"'.($mailsend == 1 ? ' checked' : '').' onclick="$(\'hidden1\').style.display=\'none\';$(\'hidden2\').style.display=\'none\'"> ͨ�� PHP ������ UNIX sendmail ����(�Ƽ��˷�ʽ)<br>';
	echo ' <input class="radio" type="radio" name="mailsend_new" value="2"'.($mailsend == 2 ? ' checked' : '').' onclick="$(\'hidden1\').style.display=\'\';$(\'hidden2\').style.display=\'\'"> ͨ�� SOCKET ���� SMTP ����������(֧�� ESMTP ��֤)<br>';
	echo ' <input class="radio" type="radio" name="mailsend_new" value="3"'.($mailsend == 3 ? ' checked' : '').' onclick="$(\'hidden1\').style.display=\'\';$(\'hidden2\').style.display=\'none\'"> ͨ�� PHP ���� SMTP ���� Email(�� win32 ����Ч, ��֧�� ESMTP)';
	echo '</td></tr>';

	$mailcfg['server'] = $mailcfg['server'] == '' ? $saved_mailcfg['server'] : $mailcfg['server'];
	$mailcfg['port'] = $mailcfg['port'] == '' ? $saved_mailcfg['port'] : $mailcfg['port'];
	$mailcfg['auth'] = $mailcfg['auth'] == '' ? $saved_mailcfg['auth'] : $mailcfg['auth'];
	$mailcfg['from'] = $mailcfg['from'] == '' ? $saved_mailcfg['from'] : $mailcfg['from'];
	$mailcfg['auth_username'] = $mailcfg['auth_username'] == '' ? $saved_mailcfg['auth_username'] : $mailcfg['auth_username'];
	$mailcfg['auth_password'] = $mailcfg['auth_password'] == '' ? $saved_mailcfg['auth_password'] : $mailcfg['auth_password'];

	echo '<tbody id="hidden1" style="display:'.($mailsend == 1 ? ' none' : '').'">';
	echo '<tr><th>SMTP ������</th><td>';
	echo ' <input class="textinput" type="text" name="mailcfg_new[server]" value="'.$mailcfg['server'].'"><br>';
	echo '</tr>';
	echo '<tr><th>SMTP �˿�, Ĭ�ϲ����޸�</th><td>';
	echo ' <input class="textinput" type="text" name="mailcfg_new[port]" value="'.$mailcfg['port'].'"><br>';
	echo '</tr>';
	echo '</tbody>';
	echo '<tbody id="hidden2" style="display:'.($mailsend != 2 ? ' none' : '').'">';
	echo '<tr><th>�Ƿ���Ҫ AUTH LOGIN ��֤</th><td>';
	echo ' <input class="checkbox" type="checkbox" name="mailcfg_new[auth]" value="1"'.($mailcfg['auth'] ? ' checked' : '').'><br>';
	echo '</tr>';
	echo '<tr><th >�����˵�ַ (�����Ҫ��֤,����Ϊ����������ַ)</th><td>';
	echo ' <input class="textinput" type="text" name="mailcfg_new[from]" value="'.$mailcfg['from'].'"><br>';
	echo '</tr>';
	echo '<tr><th>��֤�û���</th><td>';
	echo ' <input class="textinput" type="text" name="mailcfg_new[auth_username]" value="'.$mailcfg['auth_username'].'"><br>';
	echo '</tr>';
	echo '<tr><th>��֤����</th><td>';
	echo ' <input class="textinput" type="text" name="mailcfg_new[auth_password]" value="'.$mailcfg['auth_password'].'"><br>';
	echo '</tr>';
	echo '</tbody>';

	?>
	</table>
	<input type="submit" name="submit" value="��������"><br /><br />
	<?

	echo '<table><tr><th width="30%">���Է�����</th><td>';
	echo ' <input class="textinput" type="text" name="mailcfg_new[test_from]" value="'.$saved_mailcfg['test_from'].'" size="30">';
	echo '</tr>';
	echo '<tr><th>�����ռ���</th><td>';
	echo ' <input class="textinput" type="text" name="mailcfg_new[test_to]" value="'.$saved_mailcfg['test_to'].'" size="45">';
	echo '</tr>';

	?>
    </table>
	<input type="submit" name="submit" onclick="this.form.sendtest.value = 1" value="�������ò����Է���"><br /><br />
	</form>
	<?php
	htmlfooter();

} elseif ($action == 'moveattach') {
	if(!file_exists("./config.inc.php") && !file_exists("config.php")){
		errorpage("<h4>�����ϴ�config�ļ��Ա�֤�������ݿ����������ӣ�</h4>");
	}
	require_once './include/common.inc.php';
	htmlheader();
	echo "<h4>�������淽ʽ</h4>";
	$atoption = array(
		'0' => '��׼(ȫ������ͬһĿ¼)',
		'1' => '����̳���벻ͬĿ¼',
		'2' => '���ļ����ʹ��벻ͬĿ¼',
		'3' => '���·ݴ��벻ͬĿ¼',
		'4' => '������벻ͬĿ¼',
	);
	if (!empty($_POST['moveattsubmit']) || $step == 1) {
		$rpp		=	"500"; //ÿ�δ������������
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
			$msg = "$atoption[$newattachsave] �ƶ��������<br><li>����".$totalrows."����������</li><br /><li>�ƶ���".$convertedrows."������</li>";
			errorpage($msg,'',0,0);
		}

	} else {
		$attachsave = $db->result($db->query("SELECT value FROM {$tablepre}settings WHERE variable = 'attachsave' LIMIT 1"), 0);
		$checked[$attachsave] = 'checked';
		echo "<form method=\"post\" action=\"tools.php?action=moveattach\" onSubmit=\"return confirm('��ȷ���Ѿ����ݺ����ݿ�͸���\\n���Խ��и����ƶ�����ô��');\">
		<table>
		<tr>
		<th>�����ý����¹淶���и����Ĵ�ŷ�ʽ��<font color=\"red\">ע�⣺Ϊ��ֹ�������⣬��ע�ⱸ�����ݿ�͸�����</font></th></tr><tr><td>";
		foreach($atoption as $key => $val){
			echo "<li style=\"list-style:none;\"><input class=\"radio\" name=\"newattachsave\" type=\"radio\" value=\"$key\" $checked[$key]>&nbsp; $val</input></li><br>";
		}
		echo "
		</td></tr></table>
		<input type=\"hidden\" id=\"oldattachsave\" name=\"oldattachsave\" style=\"display:none;\" value=\"$attachsave\">
		<input type=\"submit\" name=\"moveattsubmit\" value=\"�� &nbsp; ��\">
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
			errorpage("����û�а�װSupeSite, �����޸���", '', 1, 1);
		} else if($supe['value']) {
			errorpage("����SupeSite��SupeSite��վ���ַ�Ǵ��ڵģ������޸���������ģ��뵽Discuz!��̨�����޸ġ�", '', 1, 1);
		} else {
			htmlheader();
		?>
			<form action="?action=setsiteurl" method="post">
			<h4>����SupeSite վ��url</h4>
				<table>
					<tr><th width="30%">������SupeSite վ��url��</th><td width="70%"><input class="textinput" type="text" name="supe_siteurl" size="40"></td></tr>
				</table>
				<input type="submit" name="setsiteurlsubmit" value="�� &nbsp; ��">
			</form>
			<div class="specialdiv">
				<h6>ע�⣺</h6>
				<ul>
				<li>�����Ҫ�޸���ȷʵ��װ��SupeSite��������Ϊվ��url Ϊ�ն������ں�̨����SupeSite������ʱ����֡�ϵͳ��⵽����û�а�װ SupeSite��������װ���ٽ������á������⡣</li>
				<li>����ʹ�����Discuz! ϵͳά�������������������������ȷ��ϵͳ�İ�ȫ���´�ʹ��ǰֻ��Ҫ��/forumdataĿ¼��ɾ��tool.lock�ļ����ɿ�ʼʹ�á�</li></ul>
			</div>
		<?php
			htmlfooter();
		}
	} else {
		$supe_siteurl = trim($supe_siteurl);
		$db->query("REPLACE INTO {$tablepre}settings (variable, value) VALUES ('supe_siteurl', '$supe_siteurl')");
		errorpage("�ɹ��޸�SupeSite վ��url ���ã����¼Discuz!��̨������Ӧ��SupeSite���á�", '�޸�SupeSite վ��url', 1, 1);
	}

} else {
	htmlheader();
	?>

	<h4>��ӭ��ʹ�� Discuz! ϵͳά��������<?=VERSION?></h4>
	<tr><td><br>

	<h5>Discuz! ϵͳά�������书�ܼ�飺</h5>
	<ul>
	<li>��̳ҽ�����Զ����������̳�����ļ������ϵͳ������Ϣ�Լ����󱨸档</li>
	<li>�����޸����ݿ⣺���������ݱ���м���޸�������</li>
	<li>�������ݿⱸ�ݣ�һ���Ե�����̳���ݱ��ݡ�</li>
	<li>���ù���Ա�˺ţ�������ָ���Ļ�Ա����Ϊ����Ա��</li>
	<li>�ʼ����ò��ԣ����Discuz!6.0.0��ǰ�汾�����ʼ����á�</li>
	<li>���ݿ�������������:���������ݽ�����Ч�Լ�飬ɾ������������Ϣ��</li>
	<li>�������淽ʽ���������ڵĸ����洢��ʽ����ָ����ʽ����Ŀ¼�ṹ���������´洢��</li>
	<li>����δ֪�ļ��������̳����Ŀ¼�µķ�Discuz!�ٷ��ļ���</li>
	<li>���ݿ�������������������SQL��䣬�����ã�</li>
	<li>�������������滻��������̳��̨�����õĴ�������б���ѡ���ԵĶ��������ӽ��д������ӽ����չ��˹�����д���</li>
	<li>�ֶ��������޸����Զ�������̳���е����ݱ����޸������ֶζ�ʧ�����⡣</li>
	<li>SupeSiteվ���ַ���޸��Ѿ���װ��SupeSite������Ϊվ��url Ϊ�ն�������Discuz!��̨�޷�����SupeSite���������⡣</li>
	<li>���»��棺�����̳�Ļ��档</li>
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
		echo "<tr bgcolor='#CCCCCC'><td colspan=4 align='center'>������ݱ� Checking table $table</td></tr>";
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
			$view = '����';
			$errortables += 1;
		} else {
			unset($bgcolor);
			unset($nooptimize);
			$view = '����';
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
			echo "<tr><td colspan=4 align='center'>�����޸��� / Repairing table $table</td></tr>";
		} else {
			if(!$simple) {
				echo ">>>>>>>>�����޸��� / Repairing Table $table<br>\n";
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
			echo "<tr><td colspan=4 align='center'>�Ż����ݱ� Optimizing table $table</td></tr>";
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
		echo '<h4>��̳ҽ��</h4><br><table>
		<tr><th>���ڽ��м��,���Ժ�</th></tr><tr><td>';
		echo "<br><a href=\"".$url."\">��������������ʱ��û���Զ���ת���������</a><br><br>";
		echo '</td></tr></table>';
	} elseif($action == 'replace') {
		echo '<h4>���ݴ�����</h4><table>
		<tr><th>���ڽ���'.$actionnow.'</th></tr><tr><td>';
		echo "���ڴ��� $start ---- $end ������[<a href='$url&stop=1' style='color:red'>ֹͣ����</a>]";
		echo "<br><br><a href=\"".$url."\">��������������ʱ��û���Զ���ת���������</a>";
		echo '</td></tr></table>';
	} else {
		echo '<h4>���ݴ�����</h4><table>
		<tr><th>���ڽ���'.$actionnow.'</th></tr><tr><td>';
		echo "���ڴ��� $start ---- $end ������[<a href='?action=$action' style='color:red'>ֹͣ����</a>]";
		echo "<br><br><a href=\"".$url."\">��������������ʱ��û���Զ���ת���������</a>";
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
		<title>Discuz! ϵͳά��������</title>
		<style type="text/css">
		<!--
		body {font-family: Arial, Helvetica, sans-serif, "����";font-size: 12px;color:#000;line-height: 120%;padding:0;margin:0;background:#DDE0FF;overflow-x:hidden;word-break:break-all;white-space:normal;scrollbar-3d-light-color:#606BFF;scrollbar-highlight-color:#E3EFF9;scrollbar-face-color:#CEE3F4;scrollbar-arrow-color:#509AD8;scrollbar-shadow-color:#F0F1FF;scrollbar-base-color:#CEE3F4;}
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
		<h2>Discuz! ϵͳά��������</h2>
		<h3>[ <a href="?" target="_self">��������ҳ</a> ]
		[ <a href="?action=setlock" target="_self">����������</a> ]</h3>
		</div>
		<div id="nav">
		<ul>
		<li>[ <a href="?action=doctor" target="_self" '.$alertmsg.'>��̳ҽ��</a> ]</li>
		<li>[ <a href="?action=repair" target="_self">�����޸����ݿ�</a> ]</li>
		<li>[ <a href="?action=restore" target="_self">�������ݿⱸ��</a> ]</li>
		<li>[ <a href="?action=setadmin" target="_self">���ù���Ա�ʺ�</a> ]</li>
		<li>[ <a href="?action=testmail" target="_self">�ʼ����ò���</a> ]</li>
		<li>[ <a href="?action=mysqlclear" target="_self">���ݿ�������������</a> ]</li>
		<li>[ <a href="?action=moveattach" target="_self">�������淽ʽ</a> ]</li>
		<li>[ <a href="?action=filecheck" target="_self">����δ֪�ļ�</a> ]</li>
		<li>[ <a href="?action=runquery" target="_self">���ݿ�����</a> ]</li>
		<li>[ <a href="?action=replace" target="_self">�������������滻</a> ]</li>
		<li>[ <a href="tools.php?action=repair_auto" '.$alertmsg.'>�ֶ��������޸�</a> ]</li>
		<li>[ <a href="?action=setsiteurl" target="_self">SupeSiteվ���ַ</a> ]</li>
		<li>[ <a href="tools.php?action=updatecache">���»���</a> ]</li>
		<li>[ <a href="?action=logout" target="_self">�˳�</a> ]</li>
		</ul></div>
		<div id="content">
		<div id="textcontent">';
}

function htmlfooter(){
	echo '
		</div></div>
		<div id="footer"><p>Discuz! Board ϵͳά�������� &nbsp;
		��Ȩ���� &copy;2001-2007 <a href="http://www.comsenz.com" style="color: #888888; text-decoration: none">
		��ʢ����(����)�Ƽ����޹�˾ Comsenz Inc.</a></font></td></tr><tr style="font-size: 0px; line-height: 0px; spacing: 0px; padding: 0px; background-color: #698CC3">
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
		$message ='<h4>�������¼</h4>
				<form action="?" method="post">
					<table class="specialtable"><tr>
					<td width="20%"><input class="textinput" type="password" name="toolpassword"></input></td>
					<td><input class="specialsubmit" type="submit" value="�� ¼"></input></td></tr></table>
					<input type="hidden" name="action" value="login">
				</form>';
	} else {
		$message = "<h4>$title</h4><br><br><table><tr><th>��ʾ��Ϣ</th></tr><tr><td>$message</td></tr></table>";
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
	echo "<br><br><a href=\"$url\">������������û���Զ���ת����������</a>";
	cexit("");
}

/**
 * ���Ŀ¼���µ��ļ�Ȩ�޺���
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
	echo '<h4>���ݿ�������������</h4><table>
			<tr"><th>���ڽ���'.$actionnow.'</th></tr><tr>
			<td>';
	if($stay) {
		$actions = isset($action[$nextstep]) ? $action[$nextstep] : '����';
		echo "$actionnow �������.������<font color=red>{$convertedrows}</font>������.".($stay == 1 ? "&nbsp;&nbsp;&nbsp;&nbsp;" : '').'<br><br>';
		echo "<a href='?action=mysqlclear&step=".$nextstep."&stay=1'>���������һ������( $actions )���������</a><br>";
	} else {
		if(isset($action[$nextstep])) {
			echo '�������룺'.$action[$nextstep].'......';
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
		echo "[<a href='?action=mysqlclear' style='color:red'>ֹͣ����</a>]<br><br><a href=\"".$scriptname."?step=".$nextstep."\">��������������ʱ��û���Զ���ת���������</a>";
	}

	echo '</td></tr></table>';
}

function loadtable($table, $force = 0) {	//������ݿ���ַ�������
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

function validid($id, $table) {//������ݱ��������С id ֵ
	global $start, $maxid, $db, $tablepre;
	$sql = $db->query("SELECT MIN($id) AS minid, MAX($id) AS maxid FROM {$tablepre}$table");
	$result = $db->fetch_array($sql);
	$start = $result['minid'] ? $result['minid'] - 1 : 0;
	$maxid = $result['maxid'];
}

function specialdiv() {
	echo '<div class="specialdiv">
		<h6>ע�⣺</h6>
		<ul>
		<li>�����ݿ�������ܻ������������ķ������ƻ����������ȱ��ݺ����ݿ��ٽ���������������������ѡ�������ѹ���Ƚ�С��ʱ�����һЩ�Ż�������</li>
		<li>����ʹ�����Discuz! ϵͳά�������������������������ȷ��ϵͳ�İ�ȫ���´�ʹ��ǰֻ��Ҫ��/forumdataĿ¼��ɾ��tool.lock�ļ����ɿ�ʼʹ�á�</li></ul></div>';
}
?>