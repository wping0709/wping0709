<?php
namespace app\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'user'  =>  'require|min:3|max:30',
        'password' =>  'require',

    ];
    protected $message=[
        'user.require'=>'用户名不能为空！',
        'user.min'=>'用户名不能少于3位！',
        'user.max'=>'用户名不能超过30位！',
        'password.require'=>'登录密码不能为空！',
        
    ];
    //验证场景
    protected $scene=[
        'query'=>['user','passwrod'],
    ];
}
