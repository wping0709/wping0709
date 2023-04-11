<!doctype html>
<html lang="zh-cmn-Hans">
<head>
<meta charset="utf-8">
<title>熊海博客 V3.0 安装向导</title>
<link rel="stylesheet" type="text/css" href="css/style.css" />
</head>
<body>
<div class="view">
<h1>熊海博客 V3.0 安装向导</h1>
<?PHP
ob_start();
//error_reporting(0);
if(file_exists('InstallLock.txt')){
exit("<t>你已经成功安装熊海博客系统，如果需要重新安装请删除install目录下的InstallLock.txt</t>");
}
$save=$_POST['save'];
$user=$_POST['user'];
$password=md5($_POST['password']);
$dbhost=$_POST['dbhost'];
$dbport=$_POST['dbport'];
$dbuser=$_POST['dbuser'];
$dbpwd=$_POST['dbpwd'];
$dbname=$_POST['dbname'];
if ($save<>""){
$v=PHP_VERSION;
  if($v < 7.2){
      exit( "<li><a>最低PHP版本：7.2.5，你的PHP版本：".PHP_VERSION."，请升级您的PHP版本。</a></li>");
  }  
    
if ($user==""){
exit("<li><a>管理用户名不能为空！</a></li>");
}
if ($_POST['password']==""){
exit("<li><a>管理密码不能为空！</a></li>");	
}
if ($dbhost==""){
exit("<li><a>数据库地址不能为空！</a></li>");	
}
if ($dbport==""){
exit("<li><a>数据库端口不能为空！</a></li>");
}
if ($dbname==""){
exit("<li><a>数据库名不能为空！</a></li>");
}
if ($dbuser==""){
exit("<li><a>数据库用户名不能为空！</a></li>");	
}
if ($dbpwd==""){
exit("<li><a>数据库密码不能为空！</a></li>");	
}
include 'db.class.php';	
$data=array('dbhost'=>$dbhost,'dbport'=>$dbport,'dbuser'=>$dbuser,'dbpw'=>$dbpwd,'dbname'=>$dbname);
$obj=new ImportData($data);
$obj->delete_data();
$obj->import_data('imxh.sql');
$manage=array('user'=>$user,'password'=>$password,'name'=>$user);
$obj->write_data($manage);
$content="<?php
\$db['hostname']='".$dbhost."';
\$db['database']='".$dbname."';
\$db['username']='".$dbuser."';
\$db['password']='".$dbpwd."';
\$db['hostport']='".$dbport."';
";
$db=file_put_contents('../../config/db.php', $content);
if($db){
  echo "<li>数据库连接信息配置成功。</li>";
}
$content = "熊海博客V3.0\r\n安装时间：".date('Y-m-d H:i:s');
$db=file_put_contents('InstallLock.txt', $content);
?>
<li>为防止重复安装，安装锁已经生成。</li>
<font>恭喜您,熊海博客系统已经成功安装，您现在可以开始使用了。<a href="/manage">管理后台</a></font>
</div>
</body>
</html>
<?PHP
ob_end_flush();
exit;
}
?>

<div class="content">
熊海博客系统是由熊海于2021年5月开发的一款可广泛应用于个人博客，个人网站的一套管理系统。
目前系统已经集成：多主题安装切换、素材管理、代码高亮、文件图片上传、智能头像，互动邮件通知等。部份模块添加多种实用功能，欢迎体验。
<br />
程序作者：熊海  <span>官方博客：<a href="http://www.imxh.cn" target="_blank">www.imxh.cn</a></span> <span>获取主题：<a href="http://www.imxh.cn" target="_blank">www.imxh.cn</a></span><br />
交流QQ群：<a>22206973</a>， 因本人工作繁忙，恕不接待程序安装及使用问题，如有问题请加群或在官网反馈。
<br />
<div class="v">当前版本：<a>PHP <?php echo PHP_VERSION; ?></a>  <span>系统要求：<a>PHP >= 7.2.5</a></span></div>
<div class="sm">声明：请勿将本程序用于任何非法目的，一切后果与作者无关。</div>
</div>
<h1>系统配置</h1>
<div class="info">
<ul>
<form id="form1" name="form1" method="post" action="">
<li><span>管理帐号：</span><input type="text"  name="user" value="admin" placeholder="后台管理帐号"/></li>
<li><span>管理密码：</span><input name="password" type="text"  placeholder="后台管理密码" /></li>

<li><span>数据库服务器：</span><input type="text" name="dbhost" value="localhost"  placeholder="数据库服务器" /></li>
<li><span>数据库端口：</span><input name="dbport" type="text" value="3306"  placeholder="数据库端口"/></li>
<li><span>数据库名称：</span><input name="dbname" type="text"  placeholder="数据库名称" /></li>
<li><span>数据库用户名：</span><input name="dbuser" type="text"  placeholder="数据库用户名" /></li>
<li><span>数据库密码：</span><input name="dbpwd" type="text"  placeholder="数据库密码"/></li>
<div class="qcfd"></div>
<input name="save" type="submit" value="开始安装" class="save"/>
</form>
</ul>
<div class="qcfd"></div>
</div>
</div>
</body>
</html>
