<?php
declare(strict_types = 1);
namespace app\home\controller;

use think\facade\Db;
use think\facade\View;
use think\facade\Cookie;

class Index{
    public function __construct()
    {
        $nav = request()->action();
        if($nav=='detail')$nav='archives';
        if($nav=='photo')$nav='album';
        View::assign('nav', $nav);
        
        // 查询基本信息
        $basic = Db::name('basic')->order('id DESC')->find();
        View::assign('basic', $basic);

        if (! $basic['status']) {
            exit('博主正在与生活对线，站点暂时关闭^_^');
        }
        
        //常用
        $commonl['class'] = Db::name('class')->order('id ASC')->select();
       
        $commonl['img_article']=db::name('article')->limit(4)->where([['status','=',1],['img', '<>','']])->orderRand()->select();
        $commonl['click_article']=db::name('article')->limit(7)->where([['status','=',1]])->orderRand('click DESC')->select();
        $commonl['link']=db::name('link')->where([['status','=',1]])->order('id DESC')->select();

        
        $commonl['tags']=db::name('article')->limit(10)->where([['tag','<>','']])->order('id DESC')->field('tag')->select()
        ->each(function ($item, $key) {
            $item['tag']=explode(',',$item['tag']);
            return $item;
        });
        $commonl['tags']=arr_foreach($commonl['tags']);
        $commonl['tags']=array_unique($commonl['tags']);
        
        $commonl['menu']=db::name('menu')->where('status',1)->order('sort ASC')->select()
        ->each(function ($item, $key) {
            $item['class']=db::name('class')->where([['status','=',1],['menu','=',$item['id']]])->select();
            return $item;
        });
        
        View::assign('view', $commonl);
        
        //定义模板路径
        $theme=db::name('theme')->where([['status','=',1]])->value('route');
        if(!$theme)$theme='default';
        $GLOBALS['temp_route'] ='/'.$theme.'/'.Request()->action();
    }

    public function index(){
        $list['basic']['article'] = db::name('article')->where([['status', '=',1],['top','<>',1]])
            ->limit(4)
            ->order('id DESC')
            ->select()
            ->each(function ($item, $key) {
                $item['content']=delhtml($item['content']);
                $item['comment']=db::name('guestbook')->where([['status','=',1],['aid','=',$item['id']]])->count();
                $item['class']=db::name('class')->where('id',$item['class'])->find();
                $item['is_img']="";
                if(!$item['simg'])$item['is_img']=0;
                if(!$item['simg'])$item['simg']=db::name('material')->where([['status','=',1],['type','=',2]])->orderRand()->value('img');
                if($item['time'])$item['time']=tranTime($item['time']);
                return $item;
            });
            
            
          $list['top']['article'] = db::name('article')->where([['status', '=',1],['top','=',1]])
                ->order('id DESC')
                ->find();
          if($list['top']['article']){
          $list['top']['article']['content']=delhtml($list['top']['article']['content']);
          $list['top']['article']['comment']=db::name('guestbook')->where([['status','=',1],['aid','=',$list['top']['article']['id']]])->count();
          $list['top']['article']['class']=db::name('class')->where('id',$list['top']['article']['class'])->find();
          if($list['top']['article']['time'])$list['top']['article']['time']=tranTime($list['top']['article']['time']);
          $list['top']['article']['is_img']="";
          if(!$list['top']['article']['simg'])$list['top']['article']['is_img']=0;
          }
        View::assign('list', $list);
        return view($GLOBALS['temp_route']);
    }

    public function detail($id)
    {

        $list['article'] = db::name('article')->where([
            [
                'url',
                '=',
                $id
            ]
        ], [
            [
                'status',
                '=',
                1
            ]
        ])->find();
        
        if(!$list['article'])exit("404");
        
        $list['article']['number']=mb_strlen($list['article']['content'], 'UTF-8');
        
        db::name('article')->where('url', $id)
            ->inc('click')
            ->update();
        ;
        
        if($list['article']['tag']){
        $list['article']['tag']=explode(",", $list['article']['tag']);
        }
        
        $list['article']['time']=tranTime($list['article']['time']);

        $list['comment']=db::name('guestbook')->where([['aid','=',$list['article']['id']],['status','=',1],['up','=',0]])->select() ->each(function ($item, $key) {
            $item['down']=db::name('guestbook')->where([['aid','=',$item['aid']],['status','=',1],['up','=',$item['id']]])->select();
            if($item['ip'])$item['ip']=convertip($item['ip']);
            if($item['time'])$item['time']=tranTime($item['time']);
            if($item['time'])$item['replytime']=tranTime($item['replytime']);
            $item['portrait']=db::name('material')->where([['status','=',1],['id','=',$item['portrait']]])->orderRand()->value('img');
            return $item;
        });

   
        
        $list['number']=sizeof($list['comment']);
        
       
        $list['link']['up']=db::name('article')->order('id src')->where([['id','>',$list['article']['id']],['status','=',1]])->limit(1)->find();
        $list['link']['down']=db::name('article')->order('id DESC')->where([['id','<',$list['article']['id']],['status','=',1]])->limit(1)->find();

        View::assign('list', $list);
        return view($GLOBALS['temp_route']);
    }

    public function archives($id='')
    {

        if($id){
            $list['class']=db::name('class')->where('pinyin',$id)->find();
            $where[]=['class','=',$list['class']['id']];
            $list['title']=$list['class']['name'].' / 分类';
        }else{
            $list['title']='全部 / 分类';
        }
        
        
        $where[]=['status','=',1];
        
        $list['article'] = Db::name('article')->where($where)
        ->order('id DESC')
        ->paginate(9, false)
        ->each(function ($item, $key) {
            $item['content']=delhtml($item['content']);
            $item['comment']=db::name('guestbook')->where([['status','=',1],['aid','=',$item['id']]])->count();
            $item['class']=db::name('class')->where('id',$item['class'])->find();
            $item['is_img']="";
            if(!$item['simg'])$item['is_img']=0;
            if(!$item['simg'])$item['simg']=db::name('material')->where([['status','=',1],['type','=',2]])->orderRand()->value('img');
            if($item['time'])$item['time']=tranTime($item['time']);
            return $item;
        });
        
        
            $page = $list['article']->render();
            View::assign('total', $list['article']->total());
            View::assign('page', $page);
            View::assign('list', $list);
        return view($GLOBALS['temp_route']);
    }
    
    
    public function about()
    {
        $list=db::name('menu')->where('pinyin','about')->find();
        View::assign('list', $list);
        return view($GLOBALS['temp_route']);
    }

    public function album()
    {

        $where[]=['status','=',1];
        $list['album'] = Db::name('album')->where($where)
        ->order('id DESC')
        ->select()
        ->each(function ($item, $key) {
            $item['img']=db::name('photo')->where('class',$item['id'])->order('id DESC')->value('img');
            if($item['time'])$item['time']=tranTime($item['time']);
            return $item;
        });
        View::assign('list', $list);
        return view($GLOBALS['temp_route']);
    }
    
    public function photo($id)
    {
        
            $list['album'] = Db::name('album')->where('pinyin',$id)->find();
            if(!$list['album'])exit("404");
            $list['album']['photo']=db::name('photo')->where([['status','=',1],['class','=',$list['album']['id']]])->select()
            ->each(function ($item, $key) {
                if($item['time'])$item['time']=tranTime($item['time']);
                return $item;
            });
            db::name('album')->where('pinyin', $id)->inc('click')->update();
            
            View::assign('list', $list);
            return view($GLOBALS['temp_route']);
    }
    

    public function link(){
        
        $list['link']=db::name('link')->where([['status','=',1]])->select();
        $list['number']=sizeof($list['link']);
        
        View::assign('list', $list);
        return view($GLOBALS['temp_route']);
    }

    public function guestbook()
    {
        
        
        $list['comment'] = Db::name('guestbook')->where([['status','=',1],['aid','=',0],['up','=',0]])
        ->order('id DESC')
        ->paginate(10,false)
        ->each(function ($item, $key) {
            $item['down']=db::name('guestbook')->where([['aid','=',0],['status','=',1],['up','=',$item['id']]])->select();
            if($item['ip'])$item['ip']=convertip($item['ip']);
            if($item['time'])$item['time']=tranTime($item['time']);
            if($item['time'])$item['replytime']=tranTime($item['replytime']);
            $item['portrait']=db::name('material')->where([['status','=',1],['id','=',$item['portrait']]])->orderRand()->value('img');
            return $item;
        });
        
   
            $page = $list['comment']->render();
            View::assign('total', $list['comment']->total());
            View::assign('page', $page);
            View::assign('list', $list);
        
        
        return view($GLOBALS['temp_route']);
    }

    public function search($tag){


        $list['search'] = Db::name('article')->where([['status','=',1],['title|tag','like','%'.$tag.'%']])
        ->order('id DESC')
        ->paginate(6, false)
        ->each(function ($item, $key) {
            $item['content']=delhtml($item['content']);
            $item['comment']=db::name('guestbook')->where([['status','=',1],['aid','=',$item['id']]])->count();
            $item['class']=db::name('class')->where('id',$item['class'])->find();
            
            $item['is_img']="";
            if(!$item['simg'])$item['is_img']=0;
            
            if(!$item['simg'])$item['simg']=db::name('material')->where([['status','=',1],['type','=',2]])->orderRand()->value('img');
            if($item['time'])$item['time']=tranTime($item['time']);
            return $item;
        });
        
            
            $page = $list['search']->render();
            View::assign('total', $list['search']->total());
            View::assign('keywords', $tag);
            View::assign('page', $page);
            View::assign('list', $list);

        return view($GLOBALS['temp_route']);
    }

    public function data($channel){
        $advanced=db::name('advanced')->where('id',1)->find();
        $basic=db::name('basic')->where('id',1)->find();
        if ($channel == 'comment') {
            if (request()->isPost()) {
                $data = input('post.');
                $dataa['aid']=trim($data['aid']);
                $dataa['up']=trim($data['up']);
                $dataa['type']=trim($data['type']);
                $dataa['url']=trim($data['url']);
                $dataa['email']=trim($data['email']);
                $dataa['content']=trim($data['content']);
                $dataa['name']=$data['name'];
                $dataa['time']=time();
                $dataa['ip']=$_SERVER['REMOTE_ADDR'];
                if($advanced['shenhe_switch']==1){
                    $dataa['status']=0;
                }else{
                    $dataa['status']=1;
                }
                $dataa['portrait']=db::name('material')->where([['status','=',1],['type','=',1]])->orderRand()->value('id');
   
                if(!$dataa['name']){
                    $return = ['status'=>0,'msg'=>'请填写昵称'];
                    return json($return);
                }
                if(!$dataa['email']){
                    $return = ['status'=>0,'msg'=>'请填写邮箱'];
                    return json($return);
                }
                if(!$dataa['content']){
                    $return = ['status'=>0,'msg'=>'请填写评论内容'];
                    return json($return);
                }
                
                if($data['url'])$data['url']=ishttp($data['url']);
                
                Cookie::set('name',$data['name'],3600*24*30);
                Cookie::set('email',$data['email'],3600*24*30);
                Cookie::set('url',$data['url'],3600*24*30);
                
                $email['msg']='';
                switch ($data['type']){
                    case 1:
                        if($data['email'] && $advanced['pinlun_switch']){
                            $email['title']=$basic['name']."上有新的留言";
                            $email['body']=
                            '
                         <div style="border:1px double #f60;">
                         <div style="background:#F60; padding:10px 10px 10px 20px; color:#FFF; font-size:16px;">
                         <a style="text-decoration:none;color:#fff;" href="http://'.$basic['url'].'" target="_blank">'.$basic['name'].'</a> 上有新的留言：
                         </div>
                         <div style=" padding:10px 10px 5px 20px; font-size:12px">亲爱的 [ '.$basic['author'].' ] ：您好!</div>
                         <div style=" padding:5px 10px 10px 20px; font-size:12px">[ '.$data['name'].' ] 在 [ '.$basic['name'].' ] 上发表了留言：</div>
                         <div style="padding:10px 10px 10px 10px; font-size:12px; background:#f2f2f2;border:1px double #ccc; margin:0px 15px 0px 15px; line-height:25px;">'.$data['content'].'</div>
                         <div style=" padding:10px 10px 10px 20px; font-size:12px">→ 您可以点击 <a style="text-decoration:none;" href="http://'.$basic['url'].url('/guestbook').'#comment" target="_blank">查看完整內容</a></div>
                         <div style=" padding:10px 10px 10px 20px; font-size:12px"><strong>温馨提示</strong> 本邮件由系统自动发出，可以直接回复！</div>
                         </div>
                        ';
                        $send=send_email($advanced['noticeemail'],$advanced['noticename'],$email['title'],$email['body']);
                        if($send['status'])$email['msg']='，并邮件通知站长。';
                        }
                    break;
                    case 2:
                        if($data['email'] && $advanced['liuyan_switch']){
                            $article=db::name('article')->where('id',$data['aid'])->find();
                            $email['title']=$basic['name']."上有新的评论";
                            $email['body']=
                            '
                        <div style="border:1px double #090;">
                        <div style="background:#090; padding:10px 10px 10px 20px; color:#FFF; font-size:16px;">
                        <a style="text-decoration:none;color:#fff;" href="http://'.$basic['url'].'" target="_blank">'.$basic['name'].'</a> 上有新的文章评论：
                        </div>
                        <div style=" padding:10px 10px 5px 20px; font-size:12px">亲爱的 [ '.$basic['author'].' ] ：您好!</div>
                        <div style=" padding:5px 10px 10px 20px; font-size:12px">[ '.$data['name'].' ] 在 [ '.$basic['name'].' ] 上对文章: 《 <a style="text-decoration:none;" href="http://'.$basic['url'].url('/detail/'.$article['url']).'" target="_blank"> '.$article['title'].' </a> 》发表了评论：</div>
                        <div style="padding:10px 10px 10px 10px; font-size:12px; background:#f2f2f2;border:1px double #ccc; margin:0px 15px 0px 15px; line-height:25px;">'.$data['content'].'</div>
                        <div style=" padding:10px 10px 10px 20px; font-size:12px">→ 您可以点击 <a style="text-decoration:none;" href="http://'.$basic['url'].url('/detail/'.$article['url']).'#comment" target="_blank">查看完整內容</a></div>
                        <div style=" padding:10px 10px 10px 20px; font-size:12px"><strong>温馨提示</strong> 本邮件由系统自动发出，可以直接回复！</div>
                        </div>
                        ';
                        $send=send_email($advanced['noticeemail'],$advanced['noticename'],$email['title'],$email['body']);
                        if($send['status'])$email['msg']='，并邮件通知站长。';
                        }
                    break;
                }
               $save=db::name('guestbook')->insertGetId($dataa);
                if($save){
                    $return = ['status'=>1,'msg'=>'发表成功'.$email['msg']];;
                }else{
                    $return = ['status'=>0,'msg'=>'发表失败'];
                }

                return json($return);
            }
        }
    }
    public function sitemap() {
        $url=db::name('basic')->where('id',1)->value('url');
        $map[]=[url('/'),1.0,'daily','Today'];
        $map[]=[url('/about'),0.8,'monthly',time()];
        $map[]=[url('/archives'),0.8,'monthly',time()];
        $map[]=[url('/album'),0.8,'monthly',time()];
        $map[]=[url('/guestbook'),0.8,'monthly',time()];
        
        //分类
        $class=db::name('class')->where('status',1)->field('pinyin')->select();
        foreach ($class as $itemss){
            $map[]=[url('/archives/'.$itemss['pinyin']),0.7,'daily',time()];
        }
        
        //相册
        $album=db::name('album')->where('status',1)->field('pinyin')->select();
        foreach ($album as $items){
            $map[]=[url('/album/'.$items['pinyin']),0.6,'daily',time()];
        }
        
        //文章
        $class=db::name('article')->where('status',1)->field('url,time')->select();
        foreach ($class as $item){
            $map[]=[url('/detail/'.$item['url']),0.5,'monthly',date('c', $item['time'])];
        }
        
        get_sitemap('http://'.$url,$map);
        header("Location: /sitemap.xml");
    }
    public function chat()
    {
        // echo 'chat';
        // 设置请求参数
        $api_key = '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';
        $model = 'image-alpha-001';
        $prompt = 'A happy panda';

        // 设置 API 请求 URL
        $url = 'https://api.openai.com/v1/images/generations';

        // 设置 API 请求头
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        );

        // 设置 API 请求体
        $data = array(
            'model' => $model,
            'prompt' => $prompt,
            'num_images' => 1,
            'size' => '256x256',
            'response_format' => 'url'
        );

        // 发送 API 请求
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        // 解析 API 响应
        $response_data = json_decode($response, true);
        $image_url = $response_data['data'][0]['url'];

        // 下载图片并保存为文件
        $image_data = file_get_contents($image_url);
        file_put_contents('generated_image.png', $image_data);
    }
}
