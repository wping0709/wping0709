<?php


namespace app\cyymodules\api\controllers;

use app\cyy\BaseApiResponse;
use app\cyymodels\ActiveLog;
use app\cyymodels\DistrictArr;
use app\cyymodels\IntegralConsume;
use app\cyymodels\IntegralLogNew;
use app\cyymodels\IntegralTrun;
use app\cyymodels\Mch;
use app\cyymodels\MchUser;
use app\cyymodels\TickeActiveLog;
use app\cyymodels\TickeCash;
use app\cyymodels\TickeList;
use app\cyymodels\TickeLog;
use app\cyymodels\TickeReleaseLog;
use app\cyymodels\TickeSetting;
use app\cyymodels\User;
use app\cyymodules\api\behaviors\LoginBehavior;
use app\cyymodules\enum\PlatFromEnum;
use app\cyymodules\models\GoodsForm;
use app\cyymodules\models\IntegralMchUserForm;
use app\cyymodules\models\WtArticleForm;
use app\cyymodules\models\WtArticleLikeForm;
use app\cyymodules\models\WtFocusForm;
use app\cyymodules\models\WtPrivateChatForm;
use app\cyymodules\models\WtReviewForm;
use app\cyymodules\models\WtTopicForm;
use app\cyymodules\models\WtUserForm;
use app\cyyutils\WechatContentSafe;
use yii\data\Pagination;

class TickeController extends Controller
{
    private $user_id;

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->user_id = \Yii::$app->user->id;
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'login' => [
                'class' => LoginBehavior::className(),
            ],
        ]);
    }


    /**
     * 抢票票页面
     * @return void
     */
    public function actionTickeInfo()
    {

        $get  = \Yii::$app->request->get();

        if (!$get['mch_id']) {
            return new BaseApiResponse(['code' => 1, 'msg' => '商户信息不存在']);
        }

        $mch = Mch::find()->where(['id' => $get['mch_id'], 'is_delete' => 0])->one();

        $mch_user = MchUser::find()->where(['mch_id' => $get['mch_id'], 'is_delete' => 0, 'user_id' => \Yii::$app->user->id])->one();

        if (!$mch_user) {
            return new BaseApiResponse(['code' => 1, 'msg' => '该用户未激活']);
        }
        //查询当前积分
        $mch_integral = $mch_user->integral ?? 0;
        //查询持有票数
        $ticke_num = $mch_user->ticke_num ?? 0;
        //查询活跃值
        $active_num = $mch_user->active_num ?? 0;
        //查询今日上车票价
        $ticke_price = $mch->ticke_price ?? 0;

        //抢票票主图
        $ticke_pic_url = $mch->ticke_pic_url;

        #当天时间戳

        $s = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $l = mktime(23, 59, 59, date("m"), date("d"), date("Y"));

        //查询今日你列车信息
        $ticke_list = TickeList::find()
            ->alias('tl')
            ->leftJoin(['ts' => TickeSetting::tableName()], 'ts.id=tl.car_id')
            ->where(['tl.mch_id' => $get['mch_id'], 'tl.is_delete' => 0])
            ->andWhere(['in', 'tl.status', [0, 1]])
            //  ->andWhere(['between','tl.addtime', $s, $l])
            ->groupBy('tl.car_id')
            ->orderBy('tl.addtime desc')
            ->select('tl.*,ts.name,ts.pic_url')
            ->limit(3)
            ->asArray()
            ->all();
        if ($ticke_list) {
            foreach ($ticke_list as $k => $v) {
                //处理日期
                $ticke_list[$k]['date'] = date('m-d', $v['addtime']);
                $ticke_list[$k]['integral'] = $ticke_price;

                //处理如果没有头像获取默认头像
                //  https://19601test.tianxin100.vip/web/statics/image-new/ticke/i2.png
                if (!$v['pic_url']) {
                    $ticke_list[$k]['pic_url'] = \Yii::$app->request->hostInfo . '/web/statics/image-new/ticke/i2.png';
                }
            }
        }


        //查询上车成功记录
        $ticke_log = TickeLog::find()
            ->alias('tl')
            ->leftJoin(['t' => TickeList::tableName()], 't.id=tl.ticke_id')
            ->leftJoin(['ts' => TickeSetting::tableName()], 'ts.id=t.car_id')
            ->leftJoin(['mu' => MchUser::tableName()], 'mu.id=tl.mu_uid')
            ->where(['tl.mch_id' => $get['mch_id'], 'tl.is_delete' => 0, 'tl.type' => 0])
            ->select('tl.*,t.shift,ts.name,t.up_start,mu.mobile')
            ->orderBy('tl.addtime desc')
            ->limit(20)
            ->asArray()
            ->all();

        if ($ticke_log) {
            foreach ($ticke_log as $k => $v) {
                //处理手机号

                $ticke_log[$k]['mobile'] = substr_replace($v['mobile'], '****', 3, 4);

                //处理时间
                $ticke_log[$k]['addtime'] = date('Y-m-d', $v['addtime']);
            }
        }

        $data = [
            'code' => 0,
            'mch_integral' => $mch_integral, //查询当前积分
            'ticke_num' => $ticke_num, //查询持有票数
            'active_num' => $active_num, //查询活跃值
            'ticke_price' => $ticke_price, //查询今日上车票价
            'ticke_pic_url' => $ticke_pic_url, //抢票票主图
            'ticke_list' => $ticke_list, //查询今日你列车信息
            'ticke_log' => $ticke_log, //查询上车成功记录

        ];

        return new BaseApiResponse($data);
    }

    /**
     * 抢票票列车列表
     * @return void
     */
    public function actionTickeList()
    {

        $get  = \Yii::$app->request->get();

        if (!$get['mch_id']) {
            return new BaseApiResponse(['code' => 1, 'msg' => '商户信息不存在']);
        }

        $mch =  Mch::find()->where(['id' => $get['mch_id'], 'is_delete' => 0])->one();

        $type = $get['type'];

        $car_id = $get['car_id'] ?? 0;


        $query = TickeList::find()
            ->alias('tl')
            ->leftJoin(['t' => TickeSetting::tableName()], 't.id=tl.car_id')
            ->andWhere(['tl.mch_id' => $get['mch_id'], 'tl.is_delete' => 0]);

        //如果传入某个列车
        if ($car_id) {
            $query->andWhere(['tl.car_id' => $car_id]);
        }

        if ($type == 0) {
            //查询即将发车
            $query->andWhere(['tl.status' => 0]);
        } elseif ($type == 1) {
            //查询正在上车
            $query->andWhere(['tl.status' => 1]);
        } elseif ($type == 2) {
            //查询行驶中
            $query->andWhere(['tl.status' => 2]);
        } elseif ($type == 3) {
            //查询已到站
            $query->andWhere(['tl.status' => 3]);
        }

        //获取积分名称
        $mch_integral_name = Mch::find()->andWhere(['id' => $get['mch_id']])->select('integral_name')->scalar();

        //票名
        $mch_ticke_name = Mch::find()->where(['id' => $get['mch_id']])->select('ticke_name')->scalar();

        //当日积分
        $mch_integral = Mch::find()->andWhere(['id' => $get['mch_id']])->select('ticke_price')->scalar();

        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $get['page'] - 1, 'pageSize' => 10]);

        $list = $query
            ->select('tl.*,t.ticket_mom,t.name,t.pic_url')
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->orderBy('tl.status ASC,tl.addtime DESC')
            ->asArray()
            ->all();

        if ($list) {
            foreach ($list as $k => $v) {
                //查看是几份

                if ($v['ticket_num'] > 1) {

                    $mom_arr = [];
                    for ($i = 0; $i < $v['ticket_num']; $i++) {
                        $mom_arr[$i]['integral'] = ($i + 1) * $v['integral'];
                        $mom_arr[$i]['mom'] = ($i + 1);
                    }
                    $list[$k]['mom_arr'] = $mom_arr;
                    $list[$k]['is_mom'] = 1;
                } else {
                    $list[$k]['is_mom'] = 0;
                }

                //查询已上车
                $board_count = TickeLog::find()->where(['ticke_id' => $v['id'], 'is_delete' => 0])->count() ?? 0;
                //查询列车上车占比
                $ticke_bili = intval(($board_count / $v['ticke_peopel_num']) * 100);

                $list[$k]['ticke_bili'] = $ticke_bili;

                $list[$k]['board_count'] = $board_count;

                //当日积分
                $list[$k]['integral'] = $mch_integral;

                if ($v['status'] == 3) {
                    //已到站
                    $list[$k]['lock_num'] += 1;
                }

                if ($v['status'] == 2) {
                    //行驶中
                    $list[$k]['lock_num'] += 1;
                }


                //查询剩余上车
                $remain_count = $v['ticke_peopel_num'] - $board_count;
                $list[$k]['remain_count'] = $remain_count;

                //时间配置
                //预计到站时间
                if ($v['status'] == 2) {

                    //拼满时间
                    $pin_time =  $v['pin_time'];
                    //正常到站时间
                    $over_time = $pin_time + ($v['period'] * 86400);

                    //拼满日期
                    $pin_date = date('Y-m-d', $pin_time);
                    //到站日期
                    $over_date = date('Y-m-d', $over_time);

                    $special_info = $mch->special_list;

                    if ($special_info) {
                        $special_list = json_decode($special_info, true);
                        if ($special_list) {
                            $rest_day = 0;
                            foreach ($special_list as $kk => $vv) {
                                //判断拼满时间-正常到站时间之间是否存在特殊日子
                                if ($over_date > $vv['start_date'] && $over_date < $vv['end_date'] && $pin_date < $vv['start_date']) {
                                    //$rest_day += ($this->diff_date($vv['start_date'],$vv['end_date'])+1);
                                }
                            }
                        }
                        //到站时间延长
                        $pin_time += ($rest_day * 86400);
                    }

                    $pin_time +=  ($v['period'] * 86400);

                    $list[$k]['time'] = date('Y-m-d H:i', $pin_time);
                    //                    $list[$k]['time'] = date('Y-m-d H:i',($v['pin_time'] + ($v['period']* 86400)));
                }

                //到站点时间
                if ($v['status'] == 3) {
                    $list[$k]['time'] = date('Y-m-d H:i', $v['over_time']);
                }

                //预计发车时间
                if ($v['status'] == 0) {
                    $list[$k]['gain_time'] = $v['addtime'] + $v['gap_time'];
                }


                //处理日期
                $list[$k]['date'] = date('m-d', $v['addtime']);


                if (!$v['pic_url']) {
                    $list[$k]['pic_url'] = \Yii::$app->request->hostInfo . '/web/statics/image-new/ticke/i2.png';
                }
            }
        }


        $data =  [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'row_count' => $count,
                'page_count' => $pagination->pageCount,
                'list' => $list,
                'mch_integral_name' => $mch_integral_name,
                'mch_ticke_name' => $mch_ticke_name
            ],
        ];
        return new BaseApiResponse($data);
    }



    /**
     * 规则详情加商户名称
     * @return void
     */
    public function actionTickeRuleInfo()
    {

        $get  = \Yii::$app->request->get();

        if (!$get['mch_id']) {
            return new BaseApiResponse(['code' => 1, 'msg' => '商户信息不存在']);
        }

        $mch = Mch::find()->where(['id' => $get['mch_id'], 'is_delete' => 0])->one();

        $data = [
            'code' => 0,
            'name' => $mch->name,
            'cash_ticke_rule' => $mch->cash_ticke_rule ?? '', //提票规则
            'recharge_ticke_rule' => $mch->recharge_ticke_rule ?? '', //充票规则

        ];

        return new BaseApiResponse($data);
    }


    /**
     * 查询持有票数
     * @return BaseApiResponse
     */
    public function actionFetchTickeInfo()
    {
        $get  = \Yii::$app->request->get();

        if (!$get['mch_id']) {
            return new BaseApiResponse(['code' => 1, 'msg' => '商户信息不存在']);
        }

        //查询是否有足够票

        $mch_user = MchUser::find()->where(['mch_id' => $get['mch_id'], 'is_delete' => 0, 'user_id' => \Yii::$app->user->id])->one();

        $mch_integral_name = Mch::find()->where(['id' => $get['mch_id']])->select('ticke_name')->scalar();
        if (!$mch_user) {
            return new BaseApiResponse(['code' => 1, 'msg' => '该用户未激活']);
        }

        $data = [
            'code' => 0,
            'ticke_num' => $mch_user->ticke_num,
            'integral_name' => $mch_integral_name,

        ];

        return new BaseApiResponse($data);
    }

    /**
     * 充票提票记录
     * @return void
     */
    public function actionTickeCashList()
    {


        $get  = \Yii::$app->request->get();

        if (!$get['mch_id']) {
            return new BaseApiResponse(['code' => 1, 'msg' => '商户信息不存在']);
        }

        $type = $get['type'];

        $mch_user = MchUser::find()->where(['user_id' => \Yii::$app->user->id, 'mch_id' => $get['mch_id'], 'is_delete' => 0])->one();

        $query = TickeCash::find()
            ->alias('tc')
            ->andWhere(['tc.mch_id' => $get['mch_id'], 'tc.is_delete' => 0, 'tc.user_id' => $mch_user->user_id]);

        if ($type == 0) {
            //充票
            $query->andWhere(['tc.type' => 0]);
        } elseif ($type == 1) {
            //提票
            $query->andWhere(['tc.type' => 1]);
        }



        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $get['page'] - 1, 'pageSize' => 10]);

        $list = $query
            ->select('tc.*')
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->orderBy('tc.addtime DESC')
            ->asArray()
            ->all();

        if ($list) {
            foreach ($list as $k => $v) {
                //时间
                $list[$k]['addtime'] = date('Y-m-d H:i:s', $v['addtime']);

                if ($v['type'] == 0) {
                    $list[$k]['type_text'] = '充票';
                } elseif ($v['type'] == 1) {
                    $list[$k]['type_text'] = '提票';
                }

                if ($v['is_pass'] == 0) {
                    $list[$k]['status_text'] = '审核中';
                } elseif ($v['is_pass'] == 1) {
                    $list[$k]['status_text'] = '成功';
                } elseif ($v['is_pass'] == 2) {
                    $list[$k]['status_text'] = '失败';
                }
            }
        }

        $data =  [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'row_count' => $count,
                'page_count' => $pagination->pageCount,
                'list' => $list,
            ],
        ];
        return new BaseApiResponse($data);
    }



    /**
     * 抢票票行程订单
     * @return void
     */
    public function actionTickeOrderList()
    {

        $get  = \Yii::$app->request->get();

        //    if (!$get['mch_id']){
        //        return new BaseApiResponse(['code'=>1,'msg'=>'商户信息不存在']);
        //    }

        $type = $get['type'];

        $query = TickeLog::find()
            ->alias('tl')
            ->leftJoin(['t' => TickeList::tableName()], 't.id=tl.ticke_id')
            ->leftJoin(['ts' => TickeSetting::tableName()], 'ts.id=t.car_id')
            ->leftJoin(['m' => Mch::tableName()], 'm.id=tl.mch_id')
            ->andWhere(['tl.user_id' => \Yii::$app->user->id])
            //    ->andWhere(['tl.mch_id'=>$get['mch_id'],'tl.is_delete'=>0]);
            ->andWhere(['tl.is_delete' => 0]);

        if ($type == 0) {
            //行驶中
            $query->andWhere(['tl.status' => 0]);
        } elseif ($type == 1) {
            //已到站
            $query->andWhere(['tl.status' => 1]);
        } elseif ($type == 2) {
            //正在上车
            $query->andWhere(['tl.status' => 2]);
        }

        //获取积分名称
        // $mch_integral_name = Mch::find()->andWhere(['id'=>$get['mch_id']])->select('integral_name')->scalar();

        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $get['page'] - 1, 'pageSize' => 10]);

        $list = $query
            ->select('tl.*,t.shift,t.ticke_peopel_num,t.period,ts.ticket_mom,ts.name,t.up_start,t.over_time,m.name as mch_name,t.lock_num,t.integral,ts.pic_url')
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->orderBy('tl.addtime DESC')
            ->asArray()
            ->all();

        if ($list) {
            foreach ($list as $k => $v) {

                $list[$k]['time'] = date('Y-m-d H:i', $v['addtime']);
                $list[$k]['date'] = date('m-d', $v['addtime']);
                //获得票时间
                $list[$k]['over_time'] =  date('Y-m-d H:i:s', $v['over_time']);

                if (!$v['pic_url']) {
                    $list[$k]['pic_url'] = \Yii::$app->request->hostInfo . '/web/statics/image-new/ticke/i2.png';
                }
            }
        }


        $data =  [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'row_count' => $count,
                'page_count' => $pagination->pageCount,
                'list' => $list,
                //                'mch_integral_name'=>$mch_integral_name
            ],
        ];
        return new BaseApiResponse($data);
    }



    /**
     *奖励记录
     */
    public function actionAwardList()
    {
        $get  = \Yii::$app->request->get();

        if (!$get['mch_id']) {
            return new BaseApiResponse(['code' => 1, 'msg' => '商户信息不存在']);
        }


        $query = TickeLog::find()
            ->alias('tl')
            ->leftJoin(['t' => TickeList::tableName()], 't.id=tl.ticke_id')
            ->leftJoin(['ts' => TickeSetting::tableName()], 'ts.id=t.car_id')
            ->andWhere(['tl.mch_id' => $get['mch_id'], 'tl.is_delete' => 0])
            ->andWhere(['user_id' => \Yii::$app->user->id]);


        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $get['page'] - 1, 'pageSize' => 10]);

        $list = $query
            ->select('tl.*,t.shift,t.ticke_peopel_num,t.period,ts.ticket_mom,t.addtime as ttime,t.lock_num,t.integral,ts.name,t.up_start,ts.pic_url')
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->orderBy('tl.addtime DESC')
            ->asArray()
            ->all();

        if ($list) {
            foreach ($list as $k => $v) {
                $list[$k]['time'] = date('Y-m-d H:i', $v['addtime']);
                //处理日期
                $list[$k]['date'] = date('m-d', $v['ttime']);

                if (!$v['pic_url']) {
                    $list[$k]['pic_url'] = \Yii::$app->request->hostInfo . '/web/statics/image-new/ticke/i2.png';
                }
            }
        }

        $data =  [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'row_count' => $count,
                'page_count' => $pagination->pageCount,
                'list' => $list,
            ],
        ];
        return new BaseApiResponse($data);
    }


    /**
     * 提票或充票
     */
    public function actionCashActive()
    {

        $post  = \Yii::$app->request->post();

        if (!$post['mch_id']) {
            return new BaseApiResponse(['code' => 1, 'msg' => '商户信息不存在']);
        }

        $mch =  Mch::find()->where(['id' => $post['mch_id'], 'is_delete' => 0])->one();

        if (!$post['num']) {
            return new BaseApiResponse(['code' => 1, 'msg' => '请输入数量']);
        }

        //判断用户是否存在
        $mch_user = MchUser::find()->where(['user_id' => \Yii::$app->user->id, 'mch_id' => $post['mch_id'], 'is_delete' => 0, 'is_activate' => 1])->one();

        if (!$mch_user) {
            return new BaseApiResponse(['code' => 1, 'msg' => '该用户不存在']);
        }

        if ($post['type'] == 0) {
            //充票
            $ticke_cash = new TickeCash();
            $ticke_cash->mu_uid = $mch_user->id;
            $ticke_cash->store_id = $this->store->id;
            $ticke_cash->mch_id = $post['mch_id'];
            $ticke_cash->user_id = \Yii::$app->user->id;
            $ticke_cash->addtime = time();
            $ticke_cash->is_delete = 0;
            $ticke_cash->sort = 0;
            $ticke_cash->pass_time = '';
            $ticke_cash->type = 0;
            $ticke_cash->is_pass = 0;
            $ticke_cash->num = $post['num'];
            $ticke_cash->desc = '充票记录';
            if ($ticke_cash->save()) {
                return new BaseApiResponse(['code' => 0, 'msg' => '成功提交审核']);
            } else {
                return new BaseApiResponse(['code' => 1, 'msg' => '充票失败']);
            }
        } else {
            //提票
            $ticke_cash_sum = TickeCash::find()->where(['mch_id' => $post['mch_id'], 'user_id' => \Yii::$app->user->id, 'is_pass' => 0, 'type' => 1])->sum('num');
            $remain_ticket_num = $ticke_cash_sum + $mch_user->ticke_num;



            //判断今日提票是否超过限额
            $s = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
            $l = mktime(23, 59, 59, date("m"), date("d"), date("Y"));

            $ticke_cash_sum_info = TickeCash::find()
                ->where(['user_id' => \Yii::$app->user->id, 'mch_id' => $post['mch_id'], 'type' => 1])
                ->andWhere(['!=', 'is_pass', 2])
                ->andWhere(['between', 'addtime', $s, $l])
                ->sum('num') ?? 0;

            if (($ticke_cash_sum_info + $post['num']) > $mch->ticke_limit) {
                return new BaseApiResponse(['code' => 1, 'msg' => '提票超过今日限额']);
            }



            if ($post['num'] > $remain_ticket_num) {
                return new BaseApiResponse(['code' => 1, 'msg' => '提票余额不足']);
            }

            $ticke_cash = new TickeCash();
            $ticke_cash->mu_uid = $mch_user->id;
            $ticke_cash->store_id = $this->store->id;
            $ticke_cash->mch_id = $post['mch_id'];
            $ticke_cash->user_id = \Yii::$app->user->id;
            $ticke_cash->addtime = time();
            $ticke_cash->is_delete = 0;
            $ticke_cash->sort = 0;
            $ticke_cash->pass_time = '';
            $ticke_cash->type = 1;
            $ticke_cash->is_pass = 0;
            $ticke_cash->num = $post['num'];
            $ticke_cash->desc = '提票记录';
            if ($ticke_cash->save()) {

                $mch_user->ticke_num -= $post['num'];
                $mch_user->save();

                return new BaseApiResponse(['code' => 0, 'msg' => '成功提交审核']);
            } else {
                return new BaseApiResponse(['code' => 1, 'msg' => '提票失败']);
            }
        }
    }


    /**
     * 上车操作
     */
    public function actionTickeGetOn()
    {
        $post  = \Yii::$app->request->post();

        if (!$post['mch_id']) {
            return new BaseApiResponse(['code' => 1, 'msg' => '商户信息不存在']);
        }

        if (!$post['ticke_id']) {
            return new BaseApiResponse(['code' => 1, 'msg' => '列车id不存在']);
        }

        if (!$post['ticke_mom']) {
            return new BaseApiResponse(['code' => 1, 'msg' => '请选择份数']);
        }


        //判断用户是否存在
        $mch_user = MchUser::find()->where(['user_id' => \Yii::$app->user->id, 'mch_id' => $post['mch_id'], 'is_delete' => 0, 'is_activate' => 1])->one();
        if (!$mch_user) {
            return new BaseApiResponse(['code' => 1, 'msg' => '用户未激活']);
        }
        //查询列车

        $ticke_info = TickeList::find()
            ->alias('tl')
            ->leftJoin(['t' => TickeSetting::tableName()], 't.id=tl.car_id')
            ->leftJoin(['m' => Mch::tableName()], 'm.id=tl.mch_id')
            ->andWhere(['tl.mch_id' => $post['mch_id'], 'tl.is_delete' => 0, 'tl.id' => $post['ticke_id']])
            ->select('tl.*,m.ticke_price,t.parent_num,t.parent_num,t.parent_parent_num,t.take_num,t.parent_num,t.parent_parent_num')
            ->asArray()
            ->one();

        if (!$ticke_info) {

            return new BaseApiResponse(['code' => 1, 'msg' => '列车不存在']);
        }

        //所需票数

        //判断是否大于设置份数
        if ($post['ticke_mom'] > $ticke_info['ticket_num']) {
            return new BaseApiResponse(['code' => 1, 'msg' => '选择份数大于设置份数']);
        }
        //lock_num 票数
        //ticket_num 份数
        //票数* 份数 =需要支付票数
        $pay_num = $ticke_info['lock_num'] *  $post['ticke_mom'];
        //先判断票是否够

        if ($mch_user->ticke_num < $pay_num) {
            return new BaseApiResponse(['code' => 1, 'msg' => '您持有的票数不足']);
        }

        $mch = Mch::find()->where(['id' => $post['mch_id'], 'is_delete' => 0])->one();

        $pay_integral = $mch['ticke_price'] *  $post['ticke_mom'];

        //判断积分是否充足
        if ($mch_user->integral <  $pay_integral) {
            return new BaseApiResponse(['code' => 1, 'msg' => '您持有的积分不足']);
        }

        //判断列车状态是否正在上车

        if ($ticke_info['status'] != 1) {
            return new BaseApiResponse(['code' => 1, 'msg' => '此列车不在发车状态']);
        }

        $t = \Yii::$app->db->beginTransaction();
        try {
            //判断列车是否拼满
            $ticke_pin_count = TickeLog::find()->where(['ticke_id' => $ticke_info['id'], 'is_delete' => 0, 'mch_id' => $post['mch_id'], 'status' => 2])->with('FOR UPDATE')->count();

            if ($ticke_pin_count == $ticke_info['ticke_peopel_num']) {
                $t->rollback();
                return new BaseApiResponse(['code' => 1, 'msg' => '此列车已拼满']);
            }
            //判断是否拼满能否上车
            if ($ticke_pin_count + 1 > $ticke_info['ticke_peopel_num']) {
                $t->rollback();
                return new BaseApiResponse(['code' => 1, 'msg' => '此列车已拼满']);
            }






            //判断今日基础票数上车次数是否充足
            //这里比较复杂
            // 1.需要判断是否用完今日次数
            //2判断昨日的直推奖励和间推奖励是否使用完，如果没有则提示上车次数已用完

            //先查询拥有的次数
            //查询昨日直推和间推的奖励次数
            $s = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
            $l = mktime(0, 0, 0, date('m'), date('d'), date('Y')) - 1;
            $ticke_active_count_one = TickeActiveLog::find()
                ->where(['mch_id' => $post['mch_id'], 'san_mu_uid' => $mch_user->id, 'store_id' => $this->store->id])
                ->andWhere(['between', 'addtime', $s, $l])
                ->andWhere(['type' => 0])
                ->count() ?? 0;

            $ticke_active_count_two = TickeActiveLog::find()
                ->where(['mch_id' => $post['mch_id'], 'san_mu_uid' => $mch_user->id, 'store_id' => $this->store->id])
                ->andWhere(['between', 'addtime', $s, $l])
                ->andWhere(['type' => 1])
                ->count() ?? 0;

            //今日可上车次数
            //举例子，张三的市场昨天有3直推上车，7间推上车。
            //T可以设置基础2+直推奖励0次+间推奖励0次，那T就可以参与2+0*3+0*7=2次T车
            //D可以设置基础2+直推奖励1次+间推奖励0次，那D就可以参与2+1*3+0*7=5次D车
            //G可以设置基础3+直推奖励0次+间推奖励2次，那G就可以参与2+0*3+2*7=16次G车
            //$ticke_active_count_one *  $ticke_info['parent_num'] 直推
            //$ticke_active_count_two *  $ticke_info['parent_parent_num'] 间推

            //可上车次数
            $ticke_up_count = $ticke_info['take_num'] + ($ticke_active_count_one * $ticke_info['parent_num']) + ($ticke_active_count_two * $ticke_info['parent_parent_num']);


            $ss = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
            $ll = mktime(23, 59, 59, date("m"), date("d"), date("Y"));

            $ticke_count = TickeLog::find()
                ->alias('tl')
                ->leftJoin(['t' => TickeList::tableName()], 't.id=tl.ticke_id')
                ->leftJoin(['ts' => TickeSetting::tableName()], 't.car_id=ts.id')
                ->andWhere(['between', 'tl.addtime', $ss, $ll])
                ->andWhere(['tl.mch_id' => $post['mch_id'], 'tl.is_delete' => 0, 'tl.mu_uid' => $mch_user->id, 't.car_id' => $ticke_info['car_id']])
                ->count() ?? 0;

            if ($ticke_up_count <= $ticke_count) {

                return new BaseApiResponse(['code' => 1, 'msg' => '今日上车次数已用完']);
            }



            //判断今日此列车是否已上车

            $ticke_log = TickeLog::find()->andWhere(['mch_id' => $post['mch_id'], 'is_delete' => 0, 'mu_uid' => $mch_user->id, 'ticke_id' => $ticke_info['id']])->one();

            if ($ticke_log) {
                return new BaseApiResponse(['code' => 1, 'msg' => '此列车已上车，请等待下一辆']);
            }


            $ticke_log_info = TickeLog::find()->andWhere(['mch_id' => $post['mch_id'], 'is_delete' => 0, 'ticke_id' => $ticke_info['id']])->orderBy('id desc')->one();
            //上车操作

            $ticke_seat_no = $ticke_log_info->seat_no ?? 0;
            $ticke_log = new TickeLog();
            $ticke_log->mu_uid = $mch_user->id;
            $ticke_log->ticke_id = $ticke_info['id'];
            $ticke_log->store_id = $this->store->id;
            $ticke_log->mch_id = $post['mch_id'];
            $ticke_log->user_id = \Yii::$app->user->id;
            $ticke_log->pay_integral = $pay_integral;
            $ticke_log->pay_ticke_num = $pay_num;
            $ticke_log->gs_uid = $mch_user->gs_uid;
            $ticke_log->status = 2;
            $ticke_log->addtime = time();

            $ticke_log->award_num = $post['ticke_mom']; //奖励票数
            $ticke_log->gain_num = $pay_num + $post['ticke_mom']; //获得票数

            $ticke_log->seat_no = sprintf('%03d', $ticke_seat_no + 1);; //座位号

            $ticke_log->order_no = $this->getTickeOrderId();; //订单号

            $ticke_log->ticke_mom = $post['ticke_mom']; //选择份数


            if ($ticke_log->save()) {

                /**
                 * 上车成功后如果最后一个上车更新状态
                 */
                // $ticke_pin_count_res = TickeLog::find()->where(['ticke_id'=>$ticke_info['id'],'is_delete'=>0,'mch_id'=>$post['mch_id'],'status'=>2])->count();
                //如果是最后一个上车的就发车
                if ($ticke_pin_count + 1 == $ticke_info['ticke_peopel_num']) {


                    $ticke_up_start = $ticke_info['up_start'] ?? 0;
                    $ticke_end_start = $ticke_info['up_end'] ?? 0;
                    if ($ticke_end_start) {
                        $up_time = explode(':', $ticke_up_start);
                        $end_time = explode(':', $ticke_end_start);
                        if (time() + $ticke_info['gap_time'] >  mktime($end_time[0], $end_time[1], 0, date('m'), date('d'), date('Y'))) {
                            $plan_time =  mktime($up_time[0], $up_time[1], 0, date('m'), date('d') + 1, date('Y'));
                        } else {
                            $plan_time = time();
                        }
                    } else {
                        $plan_time = time();
                    }

                    //更改列车状态
                    $ticke_list_info = TickeList::find()->where(['id' => $post['ticke_id']])->one();
                    $ticke_list_info->status = 2;
                    $ticke_list_info->plan_time = $plan_time;
                    $ticke_list_info->pin_time = time();
                    $ticke_list_info->save();

                    //更改上车记录状态
                    TickeLog::updateAll(['status' => 0], ['mch_id' => $post['mch_id'], 'ticke_id' => $post['ticke_id'], 'is_delete' => 0, 'status' => 2]);
                }
                /**
                 * 上车成功后如果最后一个上车更新状态
                 */

                //需要扣除用户积分和票数
                $mch_user->integral -= $pay_integral;
                $mch_user->ticke_num -= $pay_num;

                if ($mch_user->save()) {
                    //添加积分变化记录
                    $integral_log_new = new IntegralLogNew();
                    $integral_log_new->user_id = $mch_user->user_id;
                    $integral_log_new->content = '上车扣除积分';
                    $integral_log_new->integral = $pay_integral;
                    $integral_log_new->addtime = time();
                    $integral_log_new->store_id = $this->store->id;
                    $integral_log_new->type = 4;
                    $integral_log_new->mch_id = $post['mch_id'];
                    $integral_log_new->status = 1;
                    $integral_log_new->gs_uid = $mch_user->gs_uid;
                    $integral_log_new->is_get = 0;
                    $integral_log_new->is_pass = 0;
                    $integral_log_new->save();
                }






                //奖励直接上级积分
                $ticke_res =  TickeList::find()->where(['id' => $post['ticke_id']])->one();
                $parent_bili = $ticke_res->parent_bili;
                $parent_parent_bili = $ticke_res->parent_parent_bili;

                $mch_user_info = MchUser::find()->where(['user_id' => \Yii::$app->user->id, 'mch_id' => $post['mch_id'], 'is_delete' => 0, 'is_activate' => 1])->one();

                //发放积分 1.直属上级，2间推
                //查询上级
                debug_log('奖励上车直推');
                debug_log($parent_bili);
                debug_log($parent_parent_bili);
                if ($mch_user_info['parent_id']) {
                    debug_log($mch_user_info['parent_id']);
                    $mch_user_parent = MchUser::find()->where(['id' => $mch_user_info['parent_id'], 'mch_id' => $post['mch_id']])->one();

                    if ($mch_user_parent && $parent_bili) {
                        debug_log('奖励上车直推2');
                        $mch_user_parent->integral += round(($pay_integral * $parent_bili / 100), 2);
                        $mch_user_parent->save();

                        debug_log('奖励上车直推3');
                        debug_log($mch_user_info->store_id);
                        debug_log($mch_user_info->mch_id);
                        debug_log($post['ticke_id']);
                        debug_log(ceil($pay_integral * $parent_bili / 100));
                        debug_log($mch_user_parent->id);
                        debug_log($mch_user_parent->user_id);
                        debug_log($ticke_log->id);
                        debug_log($mch_user_info['id']);

                        //写入车辆到站释放记录
                        $parent_ticke_release_log = new TickeReleaseLog();
                        $parent_ticke_release_log->store_id = $mch_user_info->store_id;
                        $parent_ticke_release_log->mch_id = $mch_user_info->mch_id;
                        $parent_ticke_release_log->ticke_id = $post['ticke_id'];
                        $parent_ticke_release_log->give_integral = ceil($pay_integral * $parent_bili / 100);
                        $parent_ticke_release_log->mu_uid = $mch_user_parent->id;
                        $parent_ticke_release_log->user_id = $mch_user_parent->user_id;
                        $parent_ticke_release_log->log_id = $ticke_log->id;
                        $parent_ticke_release_log->type = 0;
                        $parent_ticke_release_log->addtime = time();
                        $parent_ticke_release_log->ticke_mu_id = $mch_user_info['id'];

                        if ($parent_ticke_release_log->save()) {
                            $integral_log_new = new IntegralLogNew();
                            $integral_log_new->user_id = $mch_user_parent->user_id;
                            $integral_log_new->content = '直推上车奖励';
                            $integral_log_new->integral = ceil($pay_integral * $parent_bili / 100);
                            $integral_log_new->addtime = time();
                            $integral_log_new->store_id = $this->store->id;
                            $integral_log_new->type = 5;
                            $integral_log_new->mch_id = $post['mch_id'];
                            $integral_log_new->status = 0;
                            $integral_log_new->gs_uid = $mch_user_parent->gs_uid;
                            $integral_log_new->is_get = 0;
                            $integral_log_new->is_pass = 0;
                            $integral_log_new->save();
                        }



                        debug_log('奖励上车直推4');

                        //查询间推
                        if ($mch_user_parent['parent_id']) {
                            debug_log('奖励上车直推5');
                            $mch_user_parent_parent = MchUser::find()->where(['id' => $mch_user_parent['parent_id'], 'mch_id' => $post['mch_id']])->one();
                            debug_log($mch_user_parent['parent_id']);
                            if ($mch_user_parent_parent && $parent_parent_bili) {

                                $mch_user_parent_parent->integral += round(($pay_integral * $parent_parent_bili / 100), 2);
                                $mch_user_parent_parent->save();

                                debug_log('奖励上车直推6');
                                debug_log($mch_user_info->store_id);
                                debug_log($mch_user_info->mch_id);
                                debug_log($post['ticke_id']);
                                debug_log(round(($pay_integral * $parent_parent_bili / 100), 2));
                                debug_log($mch_user_parent_parent->id);
                                debug_log($mch_user_parent_parent->user_id);
                                debug_log($ticke_log->id);
                                debug_log($mch_user_info['id']);

                                //写入车辆到站释放记录
                                $parent_parent_ticke_release_log = new TickeReleaseLog();
                                $parent_parent_ticke_release_log->store_id = $mch_user_info->store_id;
                                $parent_parent_ticke_release_log->mch_id = $mch_user_info->mch_id;
                                $parent_parent_ticke_release_log->ticke_id = $post['ticke_id'];
                                $parent_parent_ticke_release_log->give_integral = round(($pay_integral * $parent_parent_bili / 100), 2);
                                $parent_parent_ticke_release_log->mu_uid = $mch_user_parent_parent->id;
                                $parent_parent_ticke_release_log->user_id = $mch_user_parent_parent->user_id;
                                $parent_parent_ticke_release_log->log_id = $ticke_log->id;
                                $parent_parent_ticke_release_log->type = 1;
                                $parent_parent_ticke_release_log->addtime = time();
                                $parent_parent_ticke_release_log->ticke_mu_id = $mch_user_info['id'];


                                if ($parent_parent_ticke_release_log->save()) {
                                    $integral_log_new = new IntegralLogNew();
                                    $integral_log_new->user_id = $mch_user_parent_parent->user_id;
                                    $integral_log_new->content = '间推上车奖励';
                                    $integral_log_new->integral = round(($pay_integral * $parent_parent_bili / 100), 2);
                                    $integral_log_new->addtime = time();
                                    $integral_log_new->store_id = $this->store->id;
                                    $integral_log_new->type = 6;
                                    $integral_log_new->mch_id = $post['mch_id'];
                                    $integral_log_new->status = 0;
                                    $integral_log_new->gs_uid = $mch_user_parent_parent->gs_uid;
                                    $integral_log_new->is_get = 0;
                                    $integral_log_new->is_pass = 0;
                                    $integral_log_new->save();
                                }


                                debug_log('奖励上车直推7');
                            }
                        }
                    }
                }



                //上车成功后增加活跃值

                //需要判断是否超过或者低于活跃值

                #当天时间戳
                $s = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
                $l = mktime(23, 59, 59, date("m"), date("d"), date("Y"));

                $acitve_log_sum = ActiveLog::find()->where(['user_id' => \Yii::$app->user->id, 'mch_id' => $post['mch_id'], 'status' => 4])
                    ->andWhere(['between', 'addtime', $s, $l])
                    ->sum('num') ?? 0;

                if ($acitve_log_sum <= 1) {
                    $data = [
                        'store_id' => $ticke_log->store_id,
                        'mch_id' => $ticke_log->mch_id,
                        'gs_uid' => $ticke_log->gs_uid,
                        'user_id' => $ticke_log->user_id,
                    ];
                    $this->activeLog($data, 4, 0, 1, '参加小游戏');
                }

                //上车成功后给直推活跃值
                $mch_parent_user =  MchUser::find()->where(['id' => $mch_user->parent_id, 'mch_id' => $post['mch_id'], 'is_delete' => 0, 'is_activate' => 1])->one();

                if ($mch_parent_user) {

                    $data2 =  [
                        'store_id' => $mch_parent_user->store_id,
                        'mch_id' => $mch_parent_user->mch_id,
                        'gs_uid' => $mch_parent_user->gs_uid,
                        'user_id' => $mch_parent_user->user_id,
                    ];
                    //增加活跃值
                    $this->activeLog($data2, 1, 0, 2, '直接邀请上级');


                    //这里判断奖励给上级的次数是否超过今日限制

                    //查询今日奖励的次数
                    $ticke_active_num =  TickeActiveLog::find()->where(['san_mu_uid' => $mch_parent_user->id, 'mch_id' => $post['mch_id'], 'type' => 0])
                        ->andWhere(['between', 'addtime', $s, $l])
                        ->sum('num') ?? 0;

                    if ($ticke_active_num + 1 <= $mch->parent_limit) {
                        //增加上车奖励次数记录
                        $ticke_active_log_parent = new TickeActiveLog();
                        $ticke_active_log_parent->store_id = $ticke_info['store_id'];
                        $ticke_active_log_parent->mch_id = $post['mch_id'];
                        $ticke_active_log_parent->mu_uid = $mch_user->id;
                        $ticke_active_log_parent->user_id = $mch_user->user_id;
                        $ticke_active_log_parent->type = 0;
                        $ticke_active_log_parent->addtime = time();
                        $ticke_active_log_parent->desc = '直推上车奖励次数';
                        $ticke_active_log_parent->num = 1;
                        $ticke_active_log_parent->san_mu_uid = $mch_parent_user->id;
                        $ticke_active_log_parent->save();
                    }




                    //上车成功后给间推活跃值
                    $mch_parent_parent_user =  MchUser::find()->where(['id' => $mch_parent_user->parent_id, 'mch_id' => $post['mch_id'], 'is_delete' => 0, 'is_activate' => 1])->one();

                    if ($mch_parent_parent_user) {

                        $data3 =  [
                            'store_id' => $mch_parent_parent_user->store_id,
                            'mch_id' => $mch_parent_parent_user->mch_id,
                            'gs_uid' => $mch_parent_parent_user->gs_uid,
                            'user_id' => $mch_parent_parent_user->user_id,
                        ];
                        //增加活跃值
                        $this->activeLog($data3, 2, 0, 1, '间推邀请上级');


                        //查询今日奖励的次数
                        $ticke_active_two =  TickeActiveLog::find()->where(['san_mu_uid' => $mch_parent_parent_user->id, 'mch_id' => $post['mch_id'], 'type' => 1])
                            ->andWhere(['between', 'addtime', $s, $l])
                            ->sum('num') ?? 0;


                        if ($ticke_active_two + 1 <= $mch->parent_parent_limit) {

                            //增加上车奖励次数记录
                            $ticke_active_log_parent = new TickeActiveLog();
                            $ticke_active_log_parent->store_id = $ticke_info['store_id'];
                            $ticke_active_log_parent->mch_id = $post['mch_id'];
                            $ticke_active_log_parent->mu_uid = $mch_user->id;
                            $ticke_active_log_parent->user_id = $mch_user->user_id;
                            $ticke_active_log_parent->type = 1;
                            $ticke_active_log_parent->addtime = time();
                            $ticke_active_log_parent->desc = '间推上车奖励次数';
                            $ticke_active_log_parent->num = 1;
                            $ticke_active_log_parent->san_mu_uid = $mch_parent_parent_user->id;
                            $ticke_active_log_parent->save();
                        }
                    }
                }


                $t->commit();
                return new BaseApiResponse(['code' => 0, 'msg' => '上车成功']);
            } else {
                $t->rollBack();
                return new BaseApiResponse(['code' => 1, 'msg' => '上车失败']);
            }
        } catch (\Exception $e) {
            // 出现异常，回滚事务
            $t->rollback();
            return new BaseApiResponse(['code' => 1, 'msg' => $e->getMessage()]);
            debug_log($e->getMessage());
            //              throw $e;
        }
    }

    /**
     * type 0自己购买商品，1直接邀请上级，2间接邀请上级，3自然月减少，4每天参加小游戏
     * 生成活跃值记录
     * @return void
     */
    public function activeLog($data, $type, $status, $num, $desc)
    {

        $mch_user_info = MchUser::find()->where(['mch_id' => $data['mch_id'], 'is_delete' => 0, 'user_id' => $data['user_id']])->one();
        //判断用户的活跃值
        if ($status == 1) {
            //减活跃值
            //判断用户活跃值是否够减去
            if ($mch_user_info->active_num < $num) {
                $num = $mch_user_info->active_num;
            }
        } else {
            //加活跃值
            //判断用户是否超过100活跃值
            if ($mch_user_info->active_num + $num > 100) {
                $num = 0;
            } else if ($mch_user_info->active_num == 100) {
                $num = 0;
            }
        }

        $active_log = new ActiveLog();
        $active_log->store_id = $data['store_id'];
        $active_log->mch_id = $data['mch_id'];
        $active_log->gs_uid = $data['gs_uid'];
        $active_log->user_id = $data['user_id'];
        $active_log->type = $status;
        $active_log->status = $type;
        $active_log->addtime = time();
        $active_log->num = $num;
        $active_log->desc = $desc;

        if ($active_log->save()) {


            $mch_user_info->active_num += $num;
            $mch_user_info->save();
        }
    }

    public function getTickeOrderId()
    {
        $order_no = null;
        while (true) {
            $order_no = date('YmdHis') . mt_rand(100000, 999999);
            $exist_unicode_id = TickeLog::find()->where(['order_no' => $order_no])->exists();
            if (!$exist_unicode_id) {
                break;
            }
        }
        return $order_no;
    }

    /**
     *市场订单和礼包订单需要选择的商户列表
     */
    public function actionMchList()
    {
        $get  = \Yii::$app->request->get();



        $query = Mch::find()
            ->andWhere(['store_id' => $this->store->id, 'is_delete' => 0, 'review_status' => 1, 'is_open' => 1]);

        if ($get['keyword']) {
            $query->andWhere(['like', 'name', $get['keyword']]);
        }

        $count = $query->count();
        $pagination = new Pagination(['totalCount' => $count, 'page' => $get['page'] - 1, 'pageSize' => 10]);

        $list = $query
            ->select('id,name,logo,service_tel,address,summary')
            ->limit($pagination->limit)
            ->offset($pagination->offset)
            ->orderBy('addtime DESC')
            ->asArray()
            ->all();

        //        if ($list){
        //            foreach ($list as $k=>$v){
        //                $list[$k]['time'] = date('Y-m-d H:i',$v['addtime']);
        //                //处理日期
        //                $list[$k]['date'] = date('m-d',$v['ttime']);
        //            }
        //        }

        $data =  [
            'code' => 0,
            'msg' => 'success',
            'data' => [
                'row_count' => $count,
                'page_count' => $pagination->pageCount,
                'list' => $list,
            ],
        ];
        return new BaseApiResponse($data);
    }
}
