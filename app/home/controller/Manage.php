<?php
declare(strict_types = 1);
namespace app\home\controller;

use think\facade\Db;
use think\facade\View;

class Manage
{

    public function __construct()
    {

        $manage = manage();
        if (!$manage) {
            redirect('/manage/login')->send();
            exit();
        }
        View::assign('manage', $manage);
        
        // 查询基本信息
        $basic = Db::name('basic')->order('id DESC')->find();
        $basic['name']='熊海博客管理系统';
        View::assign('basic', $basic);
        
        $nav = request()->action();
        View::assign('nav', $nav);

    }

    public function index()
    {

        $list['data']['article']=db::name('article')->count();
        $list['data']['click']=db::name('article')->sum('click');
        $list['data']['guestbook']=db::name('guestbook')->count();
        $list['data']['reply']=db::name('guestbook')->where([['star','<>',1],['reply','=',null]])->count();

        $starttime=strtotime(date('Y-m-d')." 00:00:00");
        $endtime=strtotime(date('Y-m-d')." 23:59:59");
        
        $where['time'] = array(array('egt',$starttime),array('elt',$endtime));
        
        $getdate=strtotime(date("Y-m-d", strtotime("-1 day")));
        $weeks=get_weeks($getdate);

        foreach ($weeks['data'] as $i=>$items){
            $starttime=strtotime($items." 00:00:00");
            $endtime=strtotime($items." 23:59:59");
            $datawhere = [['time','>=',$starttime],['time','<=',$endtime]];
            $list['d']['days']['date'][$i]=date('m-d',strtotime($items));
            $list['d']['article']['data'][$i]=Db::name('article')->where($datawhere)->count();
            $list['d']['guestbook']['data'][$i]=Db::name('guestbook')->where($datawhere)->count();
        }

        $list['d']['all']['days']=json_encode(arr_foreach($list['d']['days']['date']));
        $list['d']['article']['data']=json_encode(arr_foreach($list['d']['article']['data']));
        $list['d']['guestbook']['data']=json_encode(arr_foreach($list['d']['guestbook']['data']));
        
        $list['show']['guestbook']=db::name('guestbook')->limit(5)->order('id DESC')->select()
         ->each(function ($item, $key) {
            
            switch ($item['aid']) {
                case 0:
                    $item['type'] = '留言';
                    break;
                default:
                    $item['type'] = '评论';
                    break;
            }
    
            return $item;
        });
        
        View::assign('list', $list);
        return view();
    }


    public function classify($used = '', $dataid = '', $status = '', $keyword = '')
    {
        if ($used == 'delete' && $dataid) {
            $where[] = array(
                'id',
                'in',
                $dataid
            );
            $delere = Db::name('class')->where($where)->delete();
            if ($delere) {
                $return = [
                    'status' => 1,
                    'info' => '删除成功'
                ];
            } else {
                $return = [
                    'status' => 0,
                    'info' => '删除失败'
                ];
            }
            return json($return);
            exit();
        }
        
        
        if($used=='state' && $dataid){
            $data['status']=input('get.state');
           Db::name('class')->where('id',$dataid)->update($data);
            if($data['status']==1){
                $return = ['status'=>1,'info'=>"启用成功"];
            }else{
                $return = ['status'=>1,'info'=>"禁用成功"];
            }
        
            return json($return);
            exit;
        }
    
        $where = null;
    
    
        if ($keyword) {
            $where[] = [
                'name|pinyin',
                '=',
                 $keyword
            ];
        }
    
        $list['class'] = Db::name('class')->where($where)
        ->order('id DESC')
        ->select()
        ->each(function ($item, $key) {
    
    
            return $item;
        });
    
            View::assign('list', $list);
            return view();
    }
    
    public function addclassify(){
    
        if(request()->isPost()){
            $data=input('post.');
    
            if (empty($data['name'])){
                $return = ['status'=>0,'info'=>'请填写名称'];
                return json($return);
                exit;
            }
    
            if(empty($data['status']))$data['status']=0;
            if(!$data['pinyin'])$data['pinyin']=pinyin($data['name']);
            $url_inspect=db::name('class')->where('pinyin',$data['pinyin'])->value('pinyin');
            if($url_inspect){
                $return = ['status'=>0,'info'=>'URL已经存在'];
                return json($return);
            }
            $data['menu']=3;
            $save=Db::name('class')->insertGetId($data);
            if($save){
                $return = ['status'=>1,'info'=>'新增成功'];
            }else{
                $return = ['status'=>1,'info'=>'新增失败'];
            }
    
            return json($return);
            exit;
        }
    
        return view();
    }
    
    
    public function editclassify($id){
    
    
        if(request()->isPost()){
            $data=input('post.');
    
            if (empty($data['name'])){
                $return = ['status'=>0,'info'=>'请填写名称'];
                return json($return);
                exit;
            }
    
             
            if(empty($data['status']))$data['status']=0;
            if(!$data['pinyin'])$data['pinyin']=pinyin($data['name']);
            $data['id']=$id;
            $save=Db::name('class')->update($data);
            if($save){
                $return = ['status'=>1,'info'=>'编辑成功'];
            }else{
                $return = ['status'=>1,'info'=>'编辑失败'];
            }
    
            return json($return);
            exit;
        }
    
        $list['class']=db::name('class')->where('id',$id)->find();
    
         
        View::assign('list', $list);
        return view();
    }
    
    
    
    public function article($used = '', $dataid = '', $class = '', $status = '', $keyword = '')
    {
        if ($used == 'delete' && $dataid) {
            $where[] = array(
                'id',
                'in',
                $dataid
            );
            $delere = Db::name('article')->where($where)->delete();
            if ($delere) {
                $return = [
                    'status' => 1,
                    'info' => '删除成功'
                ];
            } else {
                $return = [
                    'status' => 0,
                    'info' => '删除失败'
                ];
            }
            return json($return);
            exit();
        }
        
        $where = null;
        if ($keyword) {
            $where[] = [
                'title',
                'like',
                "%" . $keyword . "%"
            ];
        }
        if ($class) {
            $where[] = [
                'class',
                '=',
                $class
            ];
        }
        if ($status == '0' || $status == '1') {
            $where[] = [
                'status',
                '=',
                $status
            ];
        }
        
        $list['article'] = Db::name('article')->where($where)
            ->order('id DESC')
            ->paginate(10, false)
            ->each(function ($item, $key) {
            $item['classname'] = db::name('class')->where('id', $item['class'])
                ->value('name');
            
            switch ($item['status']) {
                case 1:
                    $item['status'] = '<font class="text-success">显示</font>';
                    break;
                case 0:
                    $item['status'] = '<font class="text-lighter">隐藏</font>';
                    break;
            }
            
            return $item;
        });
        
        $list['class'] = db::name('class')->select();
        $page = $list['article']->render();
        View::assign('total', $list['article']->total());
        View::assign('page', $page);
        View::assign('list', $list);
        return view();
    }

    
    public function addarticle(){
        
        if(request()->isPost()){
            $data=input('post.');
            if (empty($data['title'])){
                $return = ['status'=>0,'info'=>'请填写标题'];
                return json($return);
                exit;
            }
        
            if(!$data['class']){
                $return = ['status'=>0,'info'=>'请选择分类'];
                return json($return);
                exit;
            }
        
            if(!$data['content']){
                $return = ['status'=>0,'info'=>'请填写内容'];
                return json($return);
                exit;
            }
            
            if(empty($data['status']))$data['status']=0;
            if(empty($data['top']))$data['top']=0;
            //检测URL是否被占用
            $data['url']=strtolower(Random_en(10));
            //分词
            // if(!$data['tag'])$data['tag']=content_keywords($data['content'],2,',');
            // if(!$data['tag'])unset($data['tag']);
            
            if($data['img']){
                $filename = basename($data['img']);
                $data['simg']=substr($data['img'],0,strrpos($data['img'],"/"))."/s-".$filename;;
            }
            
            $url_inspect=db::name('article')->where('url',$data['url'])->value('url');
            if($url_inspect){
                $return = ['status'=>0,'info'=>'URL已经存在，请重试'];
                return json($return);
            }
            
            $data['click']=0;
            $data['time']=time();
            $save=Db::name('article')->insertGetId($data);
            if($save){
                $return = ['status'=>1,'info'=>'新增成功'];
            }else{
                $return = ['status'=>1,'info'=>'新增失败'];
            }
        
            return json($return);
            exit;
        }
        
        
        $list['class'] = db::name('class')->select();
        View::assign('list', $list);
        return view();
    }
    
    
    public function editarticle($id){
 

        if(request()->isPost()){
            $data=input('post.');
    
            if (empty($data['title'])){
                $return = ['status'=>0,'info'=>'请填写标题'];
                return json($return);
                exit;
            }
    
            if(!$data['class']){
                $return = ['status'=>0,'info'=>'请选择分类'];
                return json($return);
                exit;
            }
    
            if(!$data['content']){
                $return = ['status'=>0,'info'=>'请填写内容'];
                return json($return);
                exit;
            }
    
            //分词
        //    if(!$data['tag'])$data['tag']=content_keywords($data['content'],2,',');
           if(!$data['tag'])unset($data['tag']);
           
           if($data['img']){
               $filename = basename($data['img']);
               $data['simg']=substr($data['img'],0,strrpos($data['img'],"/"))."/s-".$filename;;
           }
           
           
            $data['content']=preg_replace('/title=\"[\w\.]+?\"/','',$data['content']);
            $data['content']=preg_replace('/alt=\"[\w\.]+?\"/','alt="'.$data['title'].'"',$data['content']);
           
            if(empty($data['status']))$data['status']=0;
            if(empty($data['top']))$data['top']=0;
            $data['edittime']=time();
            $data['id']=$id;
            $save=Db::name('article')->update($data);
            if($save){
                $return = ['status'=>1,'info'=>'编辑成功'];
            }else{
                $return = ['status'=>1,'info'=>'编辑失败'];
            }
    
            return json($return);
            exit;
        }
    
        $list['article']=db::name('article')->where('id',$id)->find();

    
        $list['class'] = db::name('class')->select();
        View::assign('list', $list);
        return view();
    }
    
    
    public function link($used = '', $dataid = '', $status = '', $keyword = '')
    {
        if ($used == 'delete' && $dataid) {
            $where[] = array(
                'id',
                'in',
                $dataid
            );
            $delere = Db::name('link')->where($where)->delete();
            if ($delere) {
                $return = [
                    'status' => 1,
                    'info' => '删除成功'
                ];
            } else {
                $return = [
                    'status' => 0,
                    'info' => '删除失败'
                ];
            }
            return json($return);
            exit();
        }
    
        $where = null;

        
        if ($keyword) {
            $where[] = [
                'name',
                'like',
                "%" . $keyword . "%"
            ];
        }

        if ($status == '0' || $status == '1') {
            $where[] = [
                'status',
                '=',
                $status
            ];
        }
        
        
        $list['link'] = Db::name('link')->where($where)
        ->order('id DESC')
        ->paginate(10, false)
        ->each(function ($item, $key) {
            
            switch ($item['status']) {
                case 1:
                    $item['status'] = '<font class="text-success">显示</font>';
                    break;
                case 0:
                    $item['status'] = '<font class="text-lighter">隐藏</font>';
                    break;
            }
    
            return $item;
        });
    
            $page = $list['link']->render();
            View::assign('total', $list['link']->total());
            View::assign('page', $page);
            View::assign('list', $list);
            return view();
    }
    
    public function addlink(){
    
        if(request()->isPost()){
            $data=input('post.');
    
            if (empty($data['name'])){
                $return = ['status'=>0,'info'=>'请填写名称'];
                return json($return);
                exit;
            }
    
            if(!$data['url']){
                $return = ['status'=>0,'info'=>'请填写网址'];
                return json($return);
                exit;
            }
    
            if(empty($data['status']))$data['status']=0;
    
            $url_inspect=db::name('link')->where('url',$data['url'])->value('url');
            if($url_inspect){
                $return = ['status'=>0,'info'=>'URL已经存在'];
                return json($return);
            }
    
            $data['url']=ishttp($data['url']);
            $data['time']=time();
            $save=Db::name('link')->insertGetId($data);
            if($save){
                $return = ['status'=>1,'info'=>'新增成功'];
            }else{
                $return = ['status'=>1,'info'=>'新增失败'];
            }
    
            return json($return);
            exit;
        }
    
        return view();
    }
    
    
    public function editlink($id){
    
    
        if(request()->isPost()){
            $data=input('post.');
    
            if (empty($data['name'])){
                $return = ['status'=>0,'info'=>'请填写名称'];
                return json($return);
                exit;
            }
    
            if(!$data['url']){
                $return = ['status'=>0,'info'=>'请填写网址'];
                return json($return);
                exit;
            }
    
             
            $data['url']=ishttp($data['url']);
            if(empty($data['status']))$data['status']=0;
            $data['id']=$id;
            $save=Db::name('link')->update($data);
            if($save){
                $return = ['status'=>1,'info'=>'编辑成功'];
            }else{
                $return = ['status'=>1,'info'=>'编辑失败'];
            }
    
            return json($return);
            exit;
        }
    
        $list['link']=db::name('link')->where('id',$id)->find();
    
         
        View::assign('list', $list);
        return view();
    }
    

    
    public function album($used = '', $dataid = '', $status = '', $keyword = ''){
        if ($used == 'delete' && $dataid) {
            $where[] = array(
                'id',
                'in',
                $dataid
            );
            $delere = Db::name('album')->where($where)->delete();
            if ($delere) {
                $return = [
                    'status' => 1,
                    'info' => '删除成功'
                ];
            } else {
                $return = [
                    'status' => 0,
                    'info' => '删除失败'
                ];
            }
            return json($return);
            exit();
        }
        
        
        if($used=='state' && $dataid){
            $data['status']=input('get.state');
            Db::name('album')->where('id',$dataid)->update($data);
            if($data['status']==1){
                $return = ['status'=>1,'info'=>"启用成功"];
            }else{
                $return = ['status'=>1,'info'=>"禁用成功"];
            }
        
            return json($return);
            exit;
        }
        
        $where = null;
        
        
        if ($keyword) {
            $where[] = [
                'name|pinyin',
                '=',
                $keyword
            ];
        }
        
        $list['album'] = Db::name('album')->where($where)
        ->order('id DESC')
        ->select()
        ->each(function ($item, $key) {
        
        
            return $item;
        });
        
            View::assign('list', $list);
            return view();
    }
   
    
    public function addalbum(){
    
        if(request()->isPost()){
            $data=input('post.');
    
            if (empty($data['name'])){
                $return = ['status'=>0,'info'=>'请填写名称'];
                return json($return);
                exit;
            }
    
    
            if(empty($data['status']))$data['status']=0;
            if(!$data['pinyin'])$data['pinyin']=pinyin($data['name']);
            $url_inspect=db::name('album')->where('pinyin',$data['pinyin'])->value('pinyin');
            if($url_inspect){
                $return = ['status'=>0,'info'=>'URL已经存在'];
                return json($return);
            }
            $data['time']=time();
            $save=Db::name('album')->insertGetId($data);
            if($save){
                $return = ['status'=>1,'info'=>'新增成功'];
            }else{
                $return = ['status'=>1,'info'=>'新增失败'];
            }
    
            return json($return);
            exit;
        }
    
        return view();
    }
    
    
    public function editalbum($id){
    
    
        if(request()->isPost()){
            $data=input('post.');
    
            if (empty($data['name'])){
                $return = ['status'=>0,'info'=>'请填写名称'];
                return json($return);
                exit;
            }
    
             
            if(empty($data['status']))$data['status']=0;
            if(!$data['pinyin'])$data['pinyin']=pinyin($data['name']);
            $data['id']=$id;
            $save=Db::name('album')->update($data);
            if($save){
                $return = ['status'=>1,'info'=>'编辑成功'];
            }else{
                $return = ['status'=>1,'info'=>'编辑失败'];
            }
    
            return json($return);
            exit;
        }
    
        $list['class']=db::name('album')->where('id',$id)->find();
    
         
        View::assign('list', $list);
        return view();
    }
    
    
    

    public function photo($used = '', $dataid = '', $status = '', $keyword = '',$class='')
    {
        if ($used == 'delete' && $dataid) {
            $where[] = array(
                'id',
                'in',
                $dataid
            );
            $delere = Db::name('photo')->where($where)->delete();
            if ($delere) {
                $return = [
                    'status' => 1,
                    'info' => '删除成功'
                ];
            } else {
                $return = [
                    'status' => 0,
                    'info' => '删除失败'
                ];
            }
            return json($return);
            exit();
        }
    
        $where = null;
    
    
        if ($keyword) {
            $where[] = [
                'name',
                'like',
                "%" . $keyword . "%"
            ];
        }
        if ($class) {
            $where[] = [
                'class',
                '=',
                $class
            ];
        }
        
        
        
        if ($status == '0' || $status == '1') {
            $where[] = [
                'status',
                '=',
                $status
            ];
        }
    
    
        $list['photo'] = Db::name('photo')->where($where)
        ->order('id DESC')
        ->paginate(10, false)
        ->each(function ($item, $key) {
    
            switch ($item['status']) {
                case 1:
                    $item['status'] = '<font class="text-success">显示</font>';
                    break;
                case 0:
                    $item['status'] = '<font class="text-lighter">隐藏</font>';
                    break;
            }
            
            $item['class'] = db::name('album')->where('id',$item['class'])->value('name');
    
            return $item;
        });
        
            $list['album']=db::name('album')->order('id asc')->select();
    
            $page = $list['photo']->render();
            View::assign('total', $list['photo']->total());
            View::assign('page', $page);
            View::assign('list', $list);
            return view();
    }
    
    
    public function addphoto(){
    
        if(request()->isPost()){
            $data=input('post.');
    

            if(empty($data['status']))$data['status']=0;
    

            $data['time']=time();
            $save=Db::name('photo')->insertGetId($data);
            if($save){
                $return = ['status'=>1,'info'=>'新增成功'];
            }else{
                $return = ['status'=>1,'info'=>'新增失败'];
            }
    
            return json($return);
            exit;
        }
    
        $list['class']=db::name('album')->select();
        View::assign('list', $list);
        return view();
    }
    
    
    public function editphoto($id){
    
    
        if(request()->isPost()){
            $data=input('post.');  
            if(empty($data['status']))$data['status']=0;
            $data['id']=$id;
            $save=Db::name('photo')->update($data);
            if($save){
                $return = ['status'=>1,'info'=>'编辑成功'];
            }else{
                $return = ['status'=>1,'info'=>'编辑失败'];
            }
    
            return json($return);
            exit;
        }
    
        $list['photo']=db::name('photo')->where('id',$id)->find();
        $list['class']=db::name('album')->select();
         
        View::assign('list', $list);
        return view();
    }
    
    
    
    public function comment($used = '', $dataid = '', $status = '', $keyword = '')
    {
        if ($used == 'delete' && $dataid) {
            $where[] = array(
                'id',
                'in',
                $dataid
            );
            $delere = Db::name('guestbook')->where($where)->delete();
            if ($delere) {
                $return = [
                    'status' => 1,
                    'info' => '删除成功'
                ];
            } else {
                $return = [
                    'status' => 0,
                    'info' => '删除失败'
                ];
            }
            return json($return);
            exit();
        }
    
        $where[] = ['aid','<>',0];
    
    
        if ($keyword) {
            $where[] = [
                'name|content',
                'like',
                "%" . $keyword . "%"
            ];
        }
    
        if ($status == '0' || $status == '1') {
            $where[] = [
                'status',
                '=',
                $status
            ];
        }
    
    
        $list['comment'] = Db::name('guestbook')->where($where)
        ->order('id DESC')
        ->paginate(10, false)
        ->each(function ($item, $key) {
    
            switch ($item['status']) {
                case 1:
                    $item['status'] = '<font class="text-success">显示</font>';
                    break;
                case 0:
                    $item['status'] = '<font class="text-lighter">隐藏</font>';
                    break;
            }
    
            $item['title']= db::name('article')->where('id',$item['aid'])->find();
        

            return $item;
        });
    
            $page = $list['comment']->render();
            View::assign('total', $list['comment']->total());
            View::assign('page', $page);
            View::assign('list', $list);
            return view();
    }
    
   
    public function guestbook($used = '', $dataid = '', $status = '', $keyword = '')
    {
        if ($used == 'delete' && $dataid) {
            $where[] = array(
                'id',
                'in',
                $dataid
            );
            $delere = Db::name('guestbook')->where($where)->delete();
            if ($delere) {
                $return = [
                    'status' => 1,
                    'info' => '删除成功'
                ];
            } else {
                $return = [
                    'status' => 0,
                    'info' => '删除失败'
                ];
            }
            return json($return);
            exit();
        }
    
        $where[] = ['aid','=',0];
    
    
        if ($keyword) {
            $where[] = [
                'content',
                'like',
                "%" . $keyword . "%"
            ];
        }
    
        if ($status == '0' || $status == '1') {
            $where[] = [
                'status',
                '=',
                $status
            ];
        }
    
    
        $list['comment'] = Db::name('guestbook')->where($where)
        ->order('id DESC')
        ->paginate(10, false)
        ->each(function ($item, $key) {
    
            switch ($item['status']) {
                case 1:
                    $item['status'] = '<font class="text-success">显示</font>';
                    break;
                case 0:
                    $item['status'] = '<font class="text-lighter">隐藏</font>';
                    break;
            }
    
            switch ($item['aid']) {
                case 0:
                    $item['type'] = '<font class="text-success">留言</font>';
                    $item['title']['title']='-';
                    $item['title']['url']='-';
                    break;
                default:
                    $item['title']= db::name('article')->where('id',$item['aid'])->find();
                    $item['type'] = '<font class="text-lighter">评论</font>';
            }
    
            return $item;
        });
    
            $page = $list['comment']->render();
            View::assign('total', $list['comment']->total());
            View::assign('page', $page);
            View::assign('list', $list);
            return view();
    }
    
    
    public function reply($id){
        $advanced=db::name('advanced')->where('id',1)->find();
        $basic=db::name('basic')->where('id',1)->find();
        
        $list['comment']=db::name('guestbook')->where('id',$id)->find();
        
        switch ($list['comment']['type']) {
            case 1:
                $list['comment']=db::name('guestbook')->where('id',$id)->find();
                $list['comment']['article']['title'] = '在线留言';
                $list['comment']['article']['url'] = '#';
                break;
            case 2:
                $list['comment']['article']=db::name('article')->where('id',$list['comment']['aid'])->find();
                break;
        }
        
        
        
        if(request()->isPost()){
            $data=input('post.');
    
            if (empty($data['name'])){
                $return = ['status'=>0,'info'=>'请填写名称'];
                return json($return);
                exit;
            }
    
            
            if(!$data['content']){
                $return = ['status'=>0,'info'=>'请填写内容'];
                return json($return);
                exit;
            }

            if(empty($data['notice']))$data['notice']=0;
            
            $email['msg']='';
            if($list['comment']['email'] && $data['notice']){
                switch ($list['comment']['type']){
                    case 1:
                            $email['title']="您在".$basic['name']."上的留言有了回复";
                            $email['body']=
                            '
                            <div style="border:1px double #2681e9;">
                            <div style="background:#2681e9; padding:10px 10px 10px 20px; color:#FFF; font-size:16px;">
                                                                            您在 <a style="text-decoration:none;color:#fff;" href="http://'.$basic['url'].'" target="_blank">'.$basic['name'].'</a> 上的留言有了回复：
                            </div>
                            <div style=" padding:10px 10px 5px 20px; font-size:12px">亲爱的 [ '.$list['comment']['name'].' ] ：您好!</div>
                            <div style=" padding:5px 10px 10px 20px; font-size:12px">您曾对 [ '.$basic['name'].' ] 留言 《 <a style="text-decoration:none;" href="http://'.$basic['url'].url('/guestbook').'#comment" target="_blank"> 留言 </a> 》说到：</div>
                            <div style="padding:10px 10px 10px 10px; font-size:12px; background:#f2f2f2;border:1px double #ccc; margin:0px 15px 0px 15px; line-height:25px;">'.$list['comment']['content'].'</div>
                            <div style=" padding:10px 10px 10px 20px; font-size:12px">[ '.$basic['author'].' ] 给您的回复如下：</div>
                            <div style="padding:10px 10px 10px 10px; font-size:12px; background:#f2f2f2;border:1px double #ccc;margin:0px 15px 0px 15px; line-height:25px;">'.$data['reply'].'</div>
                            <div style=" padding:10px 10px 10px 20px; font-size:12px">→ 您可以点击 <a style="text-decoration:none;" href="http://'.$basic['url'].url('/guestbook').'#comment" target="_blank">查看完整內容</a></div>
                            <div style=" padding:10px 10px 10px 20px; font-size:12px"><strong>温馨提示</strong> 本邮件由系统自动发出，可以直接回复！</div>
                            </div>
                            ';
                            $send=send_email($data['email'],$list['comment']['name'],$email['title'],$email['body']);
                            if($send['status'])$email['msg']='，并发送通知。';
                            break;
                    case 2:
                            $article=db::name('article')->where('id',$list['comment']['aid'])->find();
                            $email['title']="您在".$basic['name']."上的文章评论有了回复";
                            $email['body']=
                            '
                            <div style="border:1px double #2681e9;">
                            <div style="background:#2681e9; padding:10px 10px 10px 20px; color:#FFF; font-size:16px;">
                                                                            您在 <a style="text-decoration:none;color:#fff;" href="http://'.$basic['url'].'" target="_blank">'.$basic['name'].'</a> 上的文章评论有了回复：
                            </div>
                            <div style=" padding:10px 10px 5px 20px; font-size:12px">亲爱的 [ '.$list['comment']['name'].' ] ：您好!</div>
                            <div style=" padding:5px 10px 10px 20px; font-size:12px">您曾对 [ '.$basic['name'].' ] 文章《 <a style="text-decoration:none;" href="http://'.$basic['url'].url('/detail/'.$article['url']).'" target="_blank"> '.$article['title'].' </a> 》评论：</div>
                            <div style="padding:10px 10px 10px 10px; font-size:12px; background:#f2f2f2;border:1px double #ccc; margin:0px 15px 0px 15px; line-height:25px;">'.$list['comment']['content'].'</div>
                            <div style=" padding:10px 10px 10px 20px; font-size:12px">[ '.$basic['author'].' ] 给您的回复如下：</div>
                            <div style="padding:10px 10px 10px 10px; font-size:12px; background:#f2f2f2;border:1px double #ccc;margin:0px 15px 0px 15px; line-height:25px;">'.$data['reply'].'</div>
                            <div style=" padding:10px 10px 10px 20px; font-size:12px">→ 您可以点击 <a style="text-decoration:none;" href="http://'.$basic['url'].url('/detail/'.$article['url']).'#comment" target="_blank">查看完整內容</a></div>
                            <div style=" padding:10px 10px 10px 20px; font-size:12px"><strong>温馨提示</strong> 本邮件由系统自动发出，可以直接回复！</div>
                            </div>
                            ';
                            $send=send_email($data['email'],$list['comment']['name'],$email['title'],$email['body']);
                            if($send['status'])$email['msg']='，并发送通知。';
                        break;
                }
            
            }
            
            if($data['url'])$data['url']=ishttp($data['url']);
            if(empty($data['status']))$data['status']=0;
            $data['id']=$id;
            if($data['reply']){
                $data['star']=1;
                $data['replytime']=time();
            }
            unset($data['notice']);
            $save=Db::name('guestbook')->update($data);
            if($save){
                $return = ['status'=>1,'info'=>'回复成功'.$email['msg']];;
            }else{
                $return = ['status'=>0,'info'=>'回复失败'];
            }
            
            return json($return);
            exit;
        }
    

  
        
         
        View::assign('list', $list);
        return view();
    }
    
    
    public function menu($used = '', $dataid = '', $status = '', $keyword = '')
    {
        if ($used == 'delete' && $dataid) {
            $where[] = array(
                'id',
                'in',
                $dataid
            );
            $delere = Db::name('class')->where($where)->delete();
            if ($delere) {
                $return = [
                    'status' => 1,
                    'info' => '删除成功'
                ];
            } else {
                $return = [
                    'status' => 0,
                    'info' => '删除失败'
                ];
            }
            return json($return);
            exit();
        }
    
    
        if($used=='state' && $dataid){
            $data['status']=input('get.state');
            Db::name('menu')->where('id',$dataid)->update($data);
            if($data['status']==1){
                $return = ['status'=>1,'info'=>"启用成功"];
            }else{
                $return = ['status'=>1,'info'=>"禁用成功"];
            }
    
            return json($return);
            exit;
        }
    
        $where[] = ['view','=',1];
    
    
        if ($keyword) {
            $where[] = [
                'name|pinyin',
                '=',
                $keyword
            ];
        }
    
        $list['menu'] = Db::name('menu')->where($where)
        ->order('id ASC')
        ->select()
        ->each(function ($item, $key) {
    
    
            return $item;
        });
    
            View::assign('list', $list);
            return view();
    }
    
    public function editmenu($id){
    
    
        if(request()->isPost()){
            $data=input('post.');
    
            if (empty($data['name'])){
                $return = ['status'=>0,'info'=>'请填写名称'];
                return json($return);
                exit;
            }
    
             
            if(empty($data['status']))$data['status']=0;
            if(!$data['pinyin'])$data['pinyin']=pinyin($data['name']);
            $data['id']=$id;
            $save=Db::name('menu')->update($data);
            if($save){
                $return = ['status'=>1,'info'=>'编辑成功'];
            }else{
                $return = ['status'=>1,'info'=>'编辑失败'];
            }
    
            return json($return);
            exit;
        }
    
        $list['menu']=db::name('menu')->where('id',$id)->find();
    
         
        View::assign('list', $list);
        return view();
    }
    
    
    
    
    public function basicsetting(){

        if(request()->isPost()){
            $data=input('post.');
            if(!$data['name']){
                $return = ['status'=>0,'info'=>'请填写系统名称'];
                return json($return);
                exit;
            }
            if(!$data['url']){
                $return = ['status'=>0,'info'=>'请填写网址'];
                return json($return);
                exit;
            }
    
    
            if(empty($data['status']))$data['status']=0;
            $data['id']=1;
            $save=Db::name('basic')->update($data);
    
            if($save){
                $return = ['status'=>1,'info'=>'配置修改成功'];
            }else{
                $return = ['status'=>1,'info'=>'配置修改成功'];
            }
    
            return json($return);
            exit;
        }
        
        $list=db::name('basic')->order('id DESC')->find();
        
        View::assign('list', $list);
        return View();
    }
    
    
    
    public function advanced(){
        $list['advanced']=db::name('advanced')->where('id',1)->find();
        if(request()->isPost()){
            $data=input('post.');
    
            if(empty($data['shenhe_switch']))$data['shenhe_switch']=0;
            if(empty($data['pinlun_switch']))$data['pinlun_switch']=0;
            if(empty($data['liuyan_switch']))$data['liuyan_switch']=0;
            
            $data['id']=1;
            $save=Db::name('advanced')->update($data);
    
            if($save){
                $return = ['status'=>1,'info'=>'配置修改成功'];
            }else{
                $return = ['status'=>1,'info'=>'配置修改成功'];
            }
    
            return json($return);
            exit;
        }
    
        
        View::assign('list', $list);
        return View();
    }
    
    public function theme($used = '', $dataid = '', $status = '', $keyword = ''){
        if ($used == 'delete' && $dataid) {
            $route=Db::name('theme')->where('id',$dataid)->value('route');
            $where[] = array('id','in',$dataid);
            $delere = Db::name('theme')->where($where)->delete();
            if($route){
            delDirAndFile('./template/'.$route,true);
            }
            if ($delere) {
                $return = ['status' => 1,'info' => '删除成功，目录'.$route.'也一并删除！'];
            } else {
                $return = ['status' => 0,'info' => '删除失败'];
            }
            return json($return);
            exit();
        }
    
    
        if($used=='state' && $dataid){
            $all['status']=0;
            Db::name('theme')->where('id','<>',$dataid)->update($all);
            
            $view_menu=db::name('theme')->where('id',$dataid)->value('menu');
            
            //处理菜单
            $up['status']=0;
            $up['view']=0;
            db::name('menu')->whereNotIn('id',$view_menu)->update($up);
            $up['status']=1;
            $up['view']=1;
            db::name('menu')->whereIn('id',$view_menu)->update($up);

            $data['status']=input('get.state');
            Db::name('theme')->where('id',$dataid)->update($data);
            if($data['status']==1){
                $return = ['status'=>1,'info'=>"启用成功"];
            }else{
                $return = ['status'=>1,'info'=>"禁用成功"];
            }
    
            return json($return);
            exit;
        }
    
        $where = null;
    
    
        if ($keyword) {
            $where[] = [
                'name|website',
                '=',
                $keyword
            ];
        }
    
        $list['theme'] = Db::name('theme')->where($where)
        ->order('id DESC')
        ->select()
        ->each(function ($item, $key) {
    
    
            return $item;
        });
    
            View::assign('list', $list);
            return view();
    }

    public function installtheme(){
        if(request()->isPost()){
            $data=input('post.');
            if (!$_FILES['file']['name']){
                $return = ['status'=>0,'info'=>'请上传文件'];
                return json($return);
                exit;
            }

            $zip=uploadfile_zip('file','uploads/theme','/template/');
            if(!$zip['code']){
                $return = ['status'=>0,'info'=>$zip['msg']];
                return json($return);
            }
            if($zip['code']){
                $file='.'.$zip['data']['route'].'/theme.xml';
                $xmltheme=get_object_vars(simplexml_load_file($file));
                if(!$xmltheme){
                    $return = ['status'=>0,'info'=>'主题不正确'];
                    return json($return);
                }
          
                $is_theme=db::name('theme')->where('route',$xmltheme['route'])->find();
                if($is_theme){
                    $return = ['status'=>0,'info'=>'已经存在此主题'];
                    return json($return);
                    exit;
                }

                $xmltheme['status']=0;
                $xmltheme['time']=strtotime($xmltheme['date']);
                unset($xmltheme['date']);
                
                $save=db::name('theme')->insertGetId($xmltheme);
                if($save){
                    $return = ['status'=>1,'info'=>"主题安装成功"];
                }else{
                    $return = ['status'=>0,'info'=>"主题安装失败"];
                }
            }
            
            return json($return);
            exit;
        }
    
        return view();
    }
    
    
    public function material($used = '', $dataid = '', $status = '', $keyword = '',$type='')
    {
        if ($used == 'delete' && $dataid) {
            $where[] = array(
                'id',
                'in',
                $dataid
            );
            $delere = Db::name('material')->where($where)->delete();
            if ($delere) {
                $return = [
                    'status' => 1,
                    'info' => '删除成功'
                ];
            } else {
                $return = [
                    'status' => 0,
                    'info' => '删除失败'
                ];
            }
            return json($return);
            exit();
        }
    
        if($used=='state' && $dataid){
            $data['status']=input('get.state');
            Db::name('material')->where('id',$dataid)->update($data);
            if($data['status']==1){
                $return = ['status'=>1,'info'=>"启用成功"];
            }else{
                $return = ['status'=>1,'info'=>"禁用成功"];
            }
        
            return json($return);
            exit;
        }
        
        $where = null;

        if ($keyword) {
            $where[] = [
                'img',
                'like',
                "%" . $keyword . "%"
            ];
        }
        
        if ($type) {
            $where[] = [
                'type',
                '=',
                $type
            ];
        }
    
        if ($status == '0' || $status == '1') {
            $where[] = [
                'status',
                '=',
                $status
            ];
        }
    
    
        $list['material'] = Db::name('material')->where($where)
        ->order('id DESC')
        ->paginate(10, false)
        ->each(function ($item, $key) {
    
            switch ($item['type']) {
                case 1:
                    $item['type'] = '头像';
                    break;
                case 2:
                    $item['type'] = '配图';
                    break;
                case 3:
                    $item['type'] = '其他';
                    break;
            }
            $item['imgname']=basename($item['img']);
    
            return $item;
        });
    
            $page = $list['material']->render();
            View::assign('total', $list['material']->total());
            View::assign('page', $page);
            View::assign('list', $list);
            return view();
    }
    
    
    
    public function materialview($id){
        
        
        $list=db::name('material')->where('id',$id)->find();
        View::assign('list', $list);
        return View();
    }
    
    public function addmaterial(){
    
        if(request()->isPost()){
            $data=input('post.');
            if (empty($data['type'])){
                $return = ['status'=>0,'info'=>'请选择类型'];
                return json($return);
                exit;
            }
            
            if (empty($data['img'])){
                $return = ['status'=>0,'info'=>'请上传图片'];
                return json($return);
                exit;
            }
            
            if(empty($data['status']))$data['status']=0;
            $data['time']=time();
            $save=Db::name('material')->insertGetId($data);
            if($save){
                $return = ['status'=>1,'info'=>'新增成功'];
            }else{
                $return = ['status'=>1,'info'=>'新增失败'];
            }
    
            return json($return);
            exit;
        }
    
        $list['class']=db::name('album')->select();
        View::assign('list', $list);
        return view();
    }
     
    
    
    public function profile(){
        $manage=manage();
        if(request()->isPost()){
            $data=input('post.');
            if(!$data['name']){
                $return = ['status'=>0,'info'=>'请填写昵称'];
                return json($return);
                exit;
            }
    
            
            if($_FILES['img']['name']){
                $upload=upload('img','uploads',80,80);
                if($upload['code']==1){
                    $data['img']=$upload['data']['url'];
                }
            }else{
                unset($data['img']);
            }
  
    
            if($data['password']){
                $where['id']=array('eq',$manage['id']);
                $where['password']=array('eq',md5($data['password']));
                $is_manage=db::name('manage')->where($where)->find();
                if(!$is_manage){
                    $return = ['status'=>0,'info'=>'原始密码错误'];
                    return json($return);
                    exit;
                }
            }else{
                unset($data['password']);
            }
    
    
            if($data['password2']){
                if($data['password2']!=$data['password3']){
                    $return = ['status'=>0,'info'=>'两次密码不一致'];
                    return json($return);
                    exit;
                }
                 
                $data['password']=MD5($data['password2']);
                unset($data['password2']);
                unset($data['password3']);
            }else{
                unset($data['password2']);
                unset($data['password3']);
            }
    
    
    
    
            $data['id']=$manage['id'];
            $save=Db::name('manage')->update($data);
    
            if($save){
                $return = ['status'=>1,'info'=>'配置修改成功'];
            }else{
                $return = ['status'=>1,'info'=>'配置修改成功'];
            }
    
            return json($return);
            exit;
        }
    
    
        return View();
    }
    
    public function cache(){
        $path='../runtime/log';
        $cache=delDirAndFile($path, false);
        $path='../runtime/home';
        $cache=delDirAndFile($path, false);
        $path='../runtime/temp';
        $cache=delDirAndFile($path, false);
        $cache=1;
        if($cache){
            $return = ['status'=>1,'info'=>'缓存清空完成'];
        }else{
            $return = ['status'=>0,'info'=>'缓存清空失败'];
        }
        return json($return);
    }
    
    
    
    public function data($type,$x='',$y=''){
        
        
        switch ($type){
            case "ajaxupload":
                $upload=upload(input('post.file'),'uploads/images',770,340,370,280);
            break;
            case "ajaxphoto":
                $upload=upload(input('post.file'),'uploads/photos',800,600);
            break;
            case "ajaxmaterial":
                $upload=upload(input('post.file'),'uploads/material',$x,$y);
            break;
            
            case "ajaxportrait":
                $upload=upload(input('post.file'),'uploads/portrait',100,100);
            break;
            
            case "ajaxtheme":
                //$upload=upload(input('post.file'),'uploads/theme','','','','',5*1024*1024,'rar,zip');
                
                uploadfile_zip(input('post.file'),'uploads/theme');
 
                
                break;
        }
        
        if($upload['code']==1){
            $data = ['status'=>1,'msg'=>$upload['msg'],'url'=>$upload['data']['url']];
        }else{
            $data = ['status'=>0,'msg'=>$upload['msg']];
        }
        return json($data);

    }
    
    


    
    public function logout()
    {
        session('manage', null);
        redirect('/manage/login')->send();
    }
}
