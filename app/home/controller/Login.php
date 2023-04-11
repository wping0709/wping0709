<?php
declare(strict_types = 1);
namespace app\home\controller;

use think\facade\Db;
use think\facade\View;
use think\captcha\facade\Captcha;
use think\exception\ValidateException;
use app\validate\User;

class Login
{

    public function __construct()
    {
        // 查询基本信息
        $basic = Db::name('basic')->order('id DESC')->find();
        View::assign('basic', $basic);
    }

    public function index()
    {
        if (request()->url() == '/login')
            exit();
           
        if (request()->isPost()) {
            
            $data = input('post.');
            
            if (! $data['user']) {
                $return = [
                    'status' => 0,
                    'info' => '请填写帐号'
                ];
                return json($return);
            }
            
            if (! $data['password']) {
                $return = [
                    'status' => 0,
                    'info' => '请填写密码'
                ];
                return json($return);
            }
            
            if (! $data['verify']) {
                $return = [
                    'status' => 0,
                    'info' => '请填写验证码'
                ];
                return json($return);
            }
            
            if (! Captcha::check($data['verify'])) {
                
                $return = [
                    'status' => 0,
                    'info' => '验证码错误'
                ];
                return json($return);
            }
            
            try {
                validate(User::class)->check($data);
            } catch (ValidateException $e) {
                $return = [
                    'status' => 0,
                    'info' => $e->getError()
                ];
                return json($return);
            }
            
            $allwhere['user'] = array(
                'eq',
                $data['user']
            );
            $allwhere['password'] = array(
                'eq',
                MD5($data['password'])
            );
            $manage = Db::name('manage')->where($allwhere)->find();
            if (! $manage) {
                $return = [
                    'status' => 0,
                    'info' => '帐号或者密码错误'
                ];
                return json($return);
                exit();
            }
            
            session('manage', $manage['user']);
            $return = [
                'status' => 1,
                'info' => '登录成功'
            ];
            return json($return);
            exit();
        }
        
        return View('manage/login');
    }
}
