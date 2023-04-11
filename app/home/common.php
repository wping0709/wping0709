<?php
// 这是系统自动生成的公共文件
use think\facade\Db;

/**
 *
 * 生成key
 *
 * @param unknown $length            
 * @return string
 */
function Random_en($length)
{
    if (! $length)
        $length = 10;
    $strs = "QWERTYUIOPASDFGHJKLZXCVBNM1234567890qwertyuiopasdfghjklzxcvbnm";
    $strs = substr(str_shuffle($strs), mt_rand(0, strlen($strs) - ($length + 1)), $length);
    return $strs;
}

/**
 * 301转向
 * 不带www情况下跳转带www，IP地址时不跳转
 * 
 * @param unknown $url            
 */
function transfer($url)
{
    $thisurl = $_SERVER['HTTP_HOST'];
    $Parameters = $_SERVER['REQUEST_URI'];
    $number = str_replace('.', '', $thisurl);
    if ($thisurl != $url && ! is_numeric($number)) {
        echo header("HTTP/1.1 301 Moved Permanently");
        echo header("Location: http://" . $url . $Parameters);
        exit();
    }
}

/**
 * 单文件上传
 *
 * @param unknown $filename            
 * @param unknown $route            
 * @param string $size            
 * @param string $zoom            
 * @param string $ext            
 */
function upload($filename, $path, $imgx = '', $imgy = '', $simgx = '', $simgy = '',$fileSize = 5*1024*1024, $ext = 'jpg,jpeg,png,gif')
{

    // 上传文件错误或者文件验证不通过时，都会抛出异常，所以要使用try来捕捉异常
    try {
        // 获取上传的文件，如果有上传错误，会抛出异常
        // $file = \think\facade\Request::file('file');
        $file = \think\facade\Request::file($filename);
        // 如果上传的文件为null，手动抛出一个异常，统一处理异常
        if (null === $file) {
            // 异常代码使用UPLOAD_ERR_NO_FILE常量，方便需要进一步处理异常时使用
            throw new \Exception('请上传文件', UPLOAD_ERR_NO_FILE);
        }
        
        // 使用验证器验证上传的文件
        validate([
            'file' => [
                // 限制文件大小(单位b)，这里限制为4M
                // 'fileSize' => 4 * 1024 * 1024,
                'fileSize' => $fileSize,
                // 限制文件后缀，多个后缀以英文逗号分割
                'fileExt' => $ext
            ]
            // 更多规则请看“上传验证”的规则，文档地址https://www.kancloud.cn/manual/thinkphp6_0/1037629#_444
            
        ])->check([
            'file' => $file
        ]);
        
        
        
        // 保存路径，实际保存路径为“磁盘路径” + “avatar”
        // $path = 'avatar';
        // 文件名规则，默认是当前时间。可以使用哈希算法，如：md5/sha1等，还可以传入匿名函数，详细可以看后面
        //$rule = 'md5';
        $rule = date('ymd');
        // 将文件保存public磁盘，文件名为$rule指定的规则。然后将文件路径赋值给$path
        $path = \think\facade\Filesystem::disk('public')->putFile($path, $file, $rule);
        // 拼接URL路径
        $url = \think\facade\Filesystem::getDiskConfig('public', 'url') . '/' . str_replace('\\', '/', $path);

    } catch (\Exception $e) {
        // 如果上传时有异常，会执行这里的代码，可以在这里处理异常
        return ([
            'code' => 0,
            'msg' => $e->getMessage()
        ]);
    }
    

    $info = [
        // 文件路径：avatar/a4/e7b9e4ce42e2097b0df2feb8832d28.jpg
        'path' => $path,
        // URL路径：/storage/avatar/a4/e7b9e4ce42e2097b0df2feb8832d28.jpg
        'url' => $url,
        // 文件大小（字节）
        'size' => $file->getSize(),
        // 文件名：读书顶个鸟用.jpg
        'name' => $file->getFilename(),
        // 文件MINE：image/jpeg
        //'mime' => $file->getMime()
    ];
    
  

    $path=substr($info['url'],1);
    // 裁剪图片 $image->thumb
    if ($imgx && $imgy) {
        $image = \think\Image::open($path);
        $image->thumb($imgx, $imgy,\think\Image::THUMB_CENTER)->save($path);
    }
    
    // 裁剪图片 $image->thumb
    if ($simgx && $simgy) {
        $image = \think\Image::open($path);
        /*处理并改名*/
        $filename = basename($path);
        $path=substr($path,0,strrpos($path,"/"))."/s-".$filename;
        $image->thumb($simgx, $simgy,\think\Image::THUMB_CENTER)->save($path);
    }
    
    // halt($info);
    return ([
        'code' => 1,
        'msg' => '上传成功',
        'data' => $info
    ]);
}

/**
 * ZIP解压文件
 * 
 * @param ZIP文件 $filePath
 * @param 解压中径  $unPath
 */

function uploadfile_zip($filename,$path,$unPath,$fileSize=5*1024*1024,$ext='zip'){

    try {
        $file = \think\facade\Request::file($filename);
        if (null === $file) {
            // 异常代码使用UPLOAD_ERR_NO_FILE常量，方便需要进一步处理异常时使用
            throw new \Exception('请上传文件', UPLOAD_ERR_NO_FILE);
        }
    
        validate([
            'file' => [
                'fileSize' => $fileSize,
                'fileExt' => $ext
            ]
    
        ])->check([
            'file' => $file
        ]);

        $rule = date('ymd');
        $path = \think\facade\Filesystem::disk('public')->putFile($path, $file, $rule);
        // 拼接URL路径
        $url = \think\facade\Filesystem::getDiskConfig('public', 'url') . '/' . str_replace('\\', '/', $path);
    
    } catch (\Exception $e) {
        return ([
            'code' => 0,
            'msg' => $e->getMessage()
        ]);
    }
    
    $info = [
        // 文件路径：avatar/a4/e7b9e4ce42e2097b0df2feb8832d28.jpg
        'path' => $path,
        // URL路径：/storage/avatar/a4/e7b9e4ce42e2097b0df2feb8832d28.jpg
        'url' => $url,
        // 文件大小（字节）
        'size' => $file->getSize(),
        // 文件名：读书顶个鸟用.jpg
        'name' => $file->getFilename(),
        //原来的文件名称
        'route' => $unPath.substr($_FILES['file']['name'],0,strrpos($_FILES['file']['name'], '.')),
        // 文件MINE：image/jpeg
        //'mime' => $file->getMime()
    ];

    $filePath='.'.$info['url'];
    $unPath='.'.$unPath;
    $zip = new \ZipArchive() ;
    //打开zip文档，如果打开失败返回提示信息
    if ($zip->open($filePath,\ZipArchive::CREATE) !== TRUE) {
        return ([
            'code' => 0,
            'msg' => '文件打开失败'
        ]);
    }else{
        //将压缩文件解压到指定的目录下
        $zip->extractTo($unPath);
        //关闭zip文档
        $zip->close();
        unlink($filePath);
       return ([
            'code' => 1,
            'msg' => '解压成功',
            'data'=>$info
        ]);
    }
    
}

/**
 +-----------------------------------------------------------------------------------------
 * 删除目录及目录下所有文件或删除指定文件
 +-----------------------------------------------------------------------------------------
 * @param str $path   待删除目录路径
 * @param int $delDir 是否删除目录，1或true删除目录，0或false则只删除文件保留目录（包含子目录）
 +-----------------------------------------------------------------------------------------
 * @return bool 返回删除状态
 +-----------------------------------------------------------------------------------------
 */
function delDirAndFile($path, $delDir = FALSE) {
    if (is_array($path)) {
        foreach ($path as $subPath)
            delDirAndFile($subPath, $delDir);
    }
    if (is_dir($path)) {
        $handle = opendir($path);
        if ($handle) {
            while (false !== ( $item = readdir($handle) )) {
                if ($item != "." && $item != "..")
                    is_dir("$path/$item") ? delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
            }
            closedir($handle);
            if ($delDir)
                return rmdir($path);
        }
    } else {
        if (file_exists($path)) {
            return unlink($path);
        } else {
            return FALSE;
        }
    }
    clearstatcache();
}


/**
 * 多文件上传
 *
 * @param unknown $filename            
 * @param unknown $route            
 * @param string $size            
 * @param string $ext            
 */
function uploadsaa($filename, $route, $size = '2097152', $ext = 'jpg,jpeg,png,gif')
{
    // 获取表单上传文件
    $files = request()->file($filename);
    foreach ($files as $file) {
        if (! $route) {
            $catalog = ROOT_PATH . 'public' . DS . 'uploads/';
        } else {
            $catalog = ROOT_PATH . 'public/' . 'uploads/' . $route;
        }
        
        $info = $file->validate([
            'size' => $size,
            'ext' => $ext
        ])->move($catalog);
        if ($info) {
            $data['state'] = 1;
            $data['url'] = '/uploads/' . $route . '/' . $info->getSaveName();
        } else {
            $data['state'] = 0;
            $data['info'] = $file->getError();
        }
        return $data;
    }
}


/**
 *+----------------------------------------------------------
 * 文件下载
 *+----------------------------------------------------------
 * @static
 * @access public
 *+----------------------------------------------------------
 * @param string $fileurl 文件的URL地址
 *+----------------------------------------------------------
 * @param string $savename 保存的文件名，不带后缀名
 *+----------------------------------------------------------
 * @echo string
 *+----------------------------------------------------------
 */

function downfile($fileurl,$savename)
{
    $filename=$fileurl;
    $type=pathinfo($fileurl)['extension'];
    $file  =  fopen($filename, "rb");
    Header( "Content-type:  application/octet-stream ");
    Header( "Accept-Ranges:  bytes ");
    Header( "Content-Disposition:  attachment;  filename= ".$savename.'.'.$type);
    $contents = "";
    while (!feof($file)) {
        $contents .= fread($file, 8192);
    }
    echo $contents;
    fclose($file);
}
/**
 * 数字显示效果
 */
function number_view($number,$unit){
    $number=$number >=$unit ? $number/$unit : $number;
    return sprintf("%.1f",substr(sprintf("%.2f", $number), 0, -1));
}

function pinyin($str){
    if($str){
        require_once(root_path() .'/vendor/pinyin/Pinyin.php');
        $pinyin = new Pinyin();
        $str=$pinyin->qupinyin($str);
        return $str;
    }else{
        return false;
    }
}


/**
 * 获取地理位置
 */
function convertip($ip) {
    $ip1num = 0;
    $ip2num = 0;
    $ipAddr1 ="";
    $ipAddr2 ="";
    //$dat_path = './ipdata/qqwry.dat';
    $dat_path =root_path() .'/vendor/ipdata/qqwry.dat';
    if(!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip)) {
        return 'IP Address Error';
    }
    if(!$fd = @fopen($dat_path, 'rb')){
        return 'IP date file not exists or access denied';
    }
    $ip = explode('.', $ip);
    $ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];
    $DataBegin = fread($fd, 4);
    $DataEnd = fread($fd, 4);
    $ipbegin = implode('', unpack('L', $DataBegin));
    if($ipbegin < 0) $ipbegin += pow(2, 32);
    $ipend = implode('', unpack('L', $DataEnd));
    if($ipend < 0) $ipend += pow(2, 32);
    $ipAllNum = ($ipend - $ipbegin) / 7 + 1;
    $BeginNum = 0;
    $EndNum = $ipAllNum;
    while($ip1num>$ipNum || $ip2num<$ipNum) {
        $Middle= intval(($EndNum + $BeginNum) / 2);
        fseek($fd, $ipbegin + 7 * $Middle);
        $ipData1 = fread($fd, 4);
        if(strlen($ipData1) < 4) {
            fclose($fd);
            return 'System Error';
        }
        $ip1num = implode('', unpack('L', $ipData1));
        if($ip1num < 0) $ip1num += pow(2, 32);

        if($ip1num > $ipNum) {
            $EndNum = $Middle;
            continue;
        }
        $DataSeek = fread($fd, 3);
        if(strlen($DataSeek) < 3) {
            fclose($fd);
            return 'System Error';
        }
        $DataSeek = implode('', unpack('L', $DataSeek.chr(0)));
        fseek($fd, $DataSeek);
        $ipData2 = fread($fd, 4);
        if(strlen($ipData2) < 4) {
            fclose($fd);
            return 'System Error';
        }
        $ip2num = implode('', unpack('L', $ipData2));
        if($ip2num < 0) $ip2num += pow(2, 32);
        if($ip2num < $ipNum) {
            if($Middle == $BeginNum) {
                fclose($fd);
                return 'Unknown';
            }
            $BeginNum = $Middle;
        }
    }
    $ipFlag = fread($fd, 1);
    if($ipFlag == chr(1)) {
        $ipSeek = fread($fd, 3);
        if(strlen($ipSeek) < 3) {
            fclose($fd);
            return 'System Error';
        }
        $ipSeek = implode('', unpack('L', $ipSeek.chr(0)));
        fseek($fd, $ipSeek);
        $ipFlag = fread($fd, 1);
    }
    if($ipFlag == chr(2)) {
        $AddrSeek = fread($fd, 3);
        if(strlen($AddrSeek) < 3) {
            fclose($fd);
            return 'System Error';
        }
        $ipFlag = fread($fd, 1);
        if($ipFlag == chr(2)) {
            $AddrSeek2 = fread($fd, 3);
            if(strlen($AddrSeek2) < 3) {
                fclose($fd);
                return 'System Error';
            }
            $AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
            fseek($fd, $AddrSeek2);
        } else {
            fseek($fd, -1, SEEK_CUR);
        }
        while(($char = fread($fd, 1)) != chr(0))
            $ipAddr2 .= $char;
            $AddrSeek = implode('', unpack('L', $AddrSeek.chr(0)));
            fseek($fd, $AddrSeek);
            while(($char = fread($fd, 1)) != chr(0))
                $ipAddr1 .= $char;
    } else {
        fseek($fd, -1, SEEK_CUR);
        while(($char = fread($fd, 1)) != chr(0))
            $ipAddr1 .= $char;
            $ipFlag = fread($fd, 1);
            if($ipFlag == chr(2)) {
                $AddrSeek2 = fread($fd, 3);
                if(strlen($AddrSeek2) < 3) {
                    fclose($fd);
                    return 'System Error';
                }
                $AddrSeek2 = implode('', unpack('L', $AddrSeek2.chr(0)));
                fseek($fd, $AddrSeek2);
            } else {
                fseek($fd, -1, SEEK_CUR);
            }
            while(($char = fread($fd, 1)) != chr(0)){
                $ipAddr2 .= $char;
            }
    }
    fclose($fd);
    if(preg_match('/http/i', $ipAddr2)) {
        $ipAddr2 = '';
    }
    $ipaddr = "$ipAddr1"; //$ipAddr2运营商
    $ipaddr = preg_replace('/CZ88.NET/is', '', $ipaddr);
    $ipaddr = preg_replace('/^s*/is', '', $ipaddr);
    $ipaddr = preg_replace('/s*$/is', '', $ipaddr);
    if ($ipaddr=="IANA"){$ipaddr="本地局域网";};
    if(preg_match('/http/i', $ipaddr) || $ipaddr == '') {
        $ipaddr = 'Unknown';
    }
    $code=mb_detect_encoding($ipaddr,array('UTF-8','GBK','LATIN1','BIG5'));
    if ($code != 'UTF-8'){
        $ipaddr=iconv("GBK", "UTF-8", $ipaddr);
    }

    return $ipaddr;

}



/**
 *+----------------------------------------------------------
 * 字符串截取，支持中文和其他编码
 *+----------------------------------------------------------
 * @static
 * @access public
 *+----------------------------------------------------------
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 *+----------------------------------------------------------
 * @return string
 *+----------------------------------------------------------
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true){
    if(function_exists("mb_substr")){
        if($suffix){
            if(mb_strlen($str) > $length){
                return mb_substr($str, $start, $length, $charset)."...";
            }else{
                return mb_substr($str, $start, $length, $charset);
            }
        }else{
            return mb_substr($str, $start, $length, $charset);
        }
    }elseif(function_exists('iconv_substr')) {
        if($suffix){
            return iconv_substr($str,$start,$length,$charset);
        }else{
            return iconv_substr($str,$start,$length,$charset);
        }
    }
}

/**
 * array_multisort(array1,sorting order, sorting type,array2,array3..）是对多个数组或多维数组进行排序的函数。
 * array1 必需。规定输入的数组。
 * sorting order 可选。规定排列顺序。可能的值是 SORT_ASC 和 SORT_DESC。
 * sorting type 可选。规定排序类型。可能的值是SORT_REGULAR、SORT_NUMERIC和SORT_STRING。
 * array2 可选。规定输入的数组。
 * array3 可选。规定输入的数组。
 *
 * @param unknown $arrays
 * @param unknown $sort_key
 * @param string $sort_order
 * @param string $sort_type
 */
function array_sort($arrays, $sort_key, $sort_order = SORT_ASC, $sort_type = SORT_NUMERIC)
{
    if (is_array($arrays)) {
        foreach ($arrays as $array) {
            if (is_array($array)) {
                $key_arrays[] = $array[$sort_key];
            } else {
                return false;
            }
        }
    } else {
        return false;
    }
    array_multisort($key_arrays, $sort_order, $sort_type, $arrays);
    return $arrays;
}

//多维数组转一维
function arr_foreach($array,$return=[]){
    array_walk_recursive($array,function($value)use(&$return){$return[]=$value;});
    return $return;
}


function content_keywords($str,$sum,$separate) {
    //Vendor( 'SplitWord\splitword_full' );
    require_once(root_path() .'/vendor/SplitWord/splitword_full.php');
    $code=mb_detect_encoding($str,array('UTF-8','GBK','LATIN1','BIG5'));
    if ($code != 'UTF-8'){
        $str=iconv("GBK", "UTF-8", $str);
    }
    preg_match_all('/[\x{4e00}-\x{9fff}]+/u', $str, $matches);
    $str = join('', $matches[0]);
    //开始分词啦
    $sp = new SplitWord();
    $str=$sp->SplitRMM($str);
    $str = explode(' ', $str);
    foreach ($str as $i=>$list){
        if (strlen($str[$i])< 4){
            //删除一个字的
            unset($str[$i]);
        }
    }
    $str= array_count_values($str);
    if ($sum > count($str)){$sum=count($str);}//如果定义数量大于实际数量
    for($i=1;$i<=$sum;$i++){
        $keywords[$i]=(array_keys($str, max($str)));
        $keywords[$i]=$keywords[$i][0];
        unset($str[$keywords[$i]]);
    }
    $sp->Clear(); //清理
    return implode($separate,$keywords);
}

/**
 * 获取最近七天所有日期及星期
 *
 * @param string $time
 * @param string $format
 * @return string
 */
function get_weeks($time = '', $format = 'Y-m-d')
{
    $time = $time != '' ? $time : time();
    // 组合数据
    $date['data'] = [];
    for ($i = 1; $i <= 7; $i ++) {
        
        $date['data'][$i] = date($format, strtotime( $i - 7 . ' day', $time));
        
    }
    return $date;
}


function get_sitemap($url,$map_array) {
    require_once(root_path() .'/vendor/sitemap/Mysitemap.php');
    $sitemap = new Mysitemap($url);
    foreach ($map_array as $i=>$items){
        $sitemap->addItem($items[0],$items[1],$items[2],$items[3]);
    }
    $sitemap->endSitemap();
}

/**
 * SMTP发送邮件
 * @param unknown $toemail
 * @param unknown $toname
 * @param unknown $title
 * @param unknown $body
 * @return string
 */
function send_email($toemail,$toname,$title,$body){
    $config=db::name('advanced')->where('id',1)->find();
    $mail = new \PHPMailer();
    $mail->isSMTP();
    $mail->CharSet = "utf8";
    $mail->Host = $config['smtp']; 
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->SMTPSecure = $config['smtpsecure'];
    $mail->Port = $config['port'];

    $mail->setFrom($config['sendemail'],$config['sendname']);
    $mail->addAddress($toemail,$toname);
    $mail->addReplyTo($config['replyemail'],$config['replyename']);
    //$mail->addCC("xxx@163.com"); // 设置邮件抄送人，可以只写地址，上述的设置也可以只写地址(这个人也能收到邮件)
    //$mail->addBCC("xxx@163.com");// 设置秘密抄送人(这个人也能收到邮件)
    //$mail->addAttachment("bug0.jpg");// 添加附件
    $mail->IsHTML(true);
    $mail->Subject = $title;
    $mail->Body = $body;

    if(!$mail->send()){
        $send['status']=false;
        $send['msg']=$mail->ErrorInfo;
    }else{
        $send['status']=true;
        $send['msg']="发送成功！";
    }
    return $send;
}



/**
 * 去掉HTML标签和其他字符
 * @param unknown $str
 */

function delhtml($str){ 
    $str = preg_replace("/(\s|\&nbsp\;|　|\xc2\xa0)/", " ", strip_tags($str));
    return $str;
}


function ishttp($str){
    $preg = "/^http(s)?:\\/\\/.+/";
    if(preg_match($preg,$str))
    {
        return $str;
    }else
    {
        return "http://".$str;
    }    
}

//时间
function tranTime($time) {
    $rtime = date("m-d H:i",$time);
    $rtime2 = date("Y-m-d H:i",$time);
    $htime = date("H:i",$time);
    $time = time() - $time;
    if ($time < 60) {
        $str = '刚刚';
    }
    elseif ($time < 60 * 60) {
        $min = floor($time/60);
        $str = $min.' 分钟前';
    }
    elseif ($time < 60 * 60 * 24) {
        $h = floor($time/(60*60));
        $str = $h.'小时前 '.$htime;
    }
    elseif ($time < 60 * 60 * 24 * 3) {
        $d = floor($time/(60*60*24));
        if($d==1)
            $str = '昨天 '.$htime;
            else
                $str = '前天 '.$htime;
    }
    elseif ($time < 60 * 60 * 24 * 7) {
        $d = floor($time/(60*60*24));
        $str = $d.' 天前 '.$htime;
    }	elseif ($time < 60 * 60 * 24 * 30) {
        $str = $rtime;
    }
    else {
        $str = $rtime2;
    }
    return $str;
}

function manage()
{
    // 取得管理员身份
    if (session('manage')) {
        $manage = db::name('manage')->where([
            [
                'user',
                '=',
                session('manage')
            ]
        ])->find();
        if ($manage) {
            foreach ($manage as $list) {
                switch ($manage['class']) {
                    case 1:
                        $manage['classtype'] = "超级管理";
                        break;
                    case 2:
                        $manage['classtype'] = "管理员";
                        break;
                    default:
                        $manage['classtype'] = "未知";
                }
            }
            return $manage;
        } else {
            return false;
        }
    } else {
        return false;
    }
}