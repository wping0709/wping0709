<?php
use think\facade\Db;
// +----------------------------------------------------------------------
// | 模板设置
// +----------------------------------------------------------------------
$route = explode('/', trim($_SERVER['REQUEST_URI'], '/'))[0];
$theme=db::name('theme')->where([['status','=',1]])->value('route');
if(!$theme)$theme='default';
return [
    // 模板引擎类型使用Think
    'type'          => 'Think',
    // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写 3 保持操作方法
    'auto_rule'     => 1,
    // 模板目录名
    'view_dir_name' => 'public/template',
    // 模板后缀
    'view_suffix'   => 'html',
    // 模板文件名分隔符
    'view_depr'     => DIRECTORY_SEPARATOR,
    // 模板引擎普通标签开始标记
    'tpl_begin'     => '{',
    // 模板引擎普通标签结束标记
    'tpl_end'       => '}',
    // 标签库标签开始标记
    'taglib_begin'  => '{',
    // 标签库标签结束标记
    'taglib_end'    => '}',
    // 模板路径
    'view_path'=>app()->getRootPath().'/public/template/',
   
    //模板参数替换
	'tpl_replace_string'  =>  [
		'__CSS__' => '/template/'.$theme.'/css',
	    '__JS__' => '/template/'.$theme.'/js',
	    '__IMG__' => '/template/'.$theme.'/images',
	    '__ICO__' => '/template/system/iconfonts',
	    '__LAYER__' => '/template/system/layer',
	    '__SYSTEM__' => '/template/system',
	    
	    '__MCSS__' => '/template/'.$route.'/css',
	    '__MIMG__' => '/template/'.$route.'/images',
	    '__MJS__' => '/template/'.$route.'/js',
    ],
    // 是否开启模板编译缓存,设为false则每次都会重新编译
    'tpl_cache'          => true,
];