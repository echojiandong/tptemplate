<?php
namespace app\weixin\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use \WechatPhpSdk\Wechat;
use \WechatPhpSdk\Api;
use app\weixin\model\PersonModel;
use app\manage\model\Wechatnav;

class Weixin extends Controller
{
    protected $templateId;  // 学习计划模板ID
    protected $appId;
    protected $order_success_templateId; // 审核成功模板id

	//微信初始化
    public function _initialize(){
        // 开发者中心-配置项-AppID(应用ID)
        $this->appId = config('WX_appId');
        // 开发者中心-配置项-AppSecret(应用密钥)
        $appSecret = config('WX_appSecret');
        // 开发者中心-配置项-服务器配置-Token(令牌)
        $token = config('WX_token');
        // 开发者中心-配置项-服务器配置-EncodingAESKey(消息加解密密钥)
        $encodingAESKey = config('WX_encodingAESKey');

        // 模板消息ID
        $this->templateId = config('WX_templateId');

        // 订单审核模板ID
        $this->order_success_templateId = config('WX_successTemplateId');

        // wechat模块 - 处理用户发送的消息和回复消息
        $this->wechat = new Wechat(array(
            'appId' => $this->appId,
            'token' => 	$token,
            'encodingAESKey' =>	$encodingAESKey //可选
        ));
        //@error_log('weixin controller start',0);
        // api模块 - 包含各种系统主动发起的功能
        $this->api = new Api(
            array(
                'appId' => $this->appId,
                'appSecret'	=> $appSecret,
                'get_access_token' => function(){
                    // 用户需要自己实现access_token的返回
                    @error_log('token get'.cache('wechat_token'),0);
                    //return Cache::get('wechat_token');
                },
                'save_access_token' => function($token) {
                    // 用户需要自己实现access_token的保存
                    @error_log('token save'.$token,0);

                    //Cache::set('wechat_token', $token->access_token,3600);
                },
                'get_jsapi_ticket' => function(){
                    // 用户需要自己实现ticket的返回
                    @error_log('ticket get'.cache('wechat_ticket'),0);
                    return Cache::get('wechat_ticket');
                },
                'save_jsapi_ticket' => function($ticket) {
                    // 用户需要自己实现ticket的保存
                    @error_log('ticket save'.$ticket,0);
                    Cache::set('wechat_ticket', $ticket,3600);
                }
            )
        );
    }
	public function index()
	{
		// 获取微信消息
        $msg = $this->wechat->serve();
  //       $param=input();
		// $timestamp = $param['timestamp'];
		// $nonce = $param['nonce'];
		// $token = 'ydtktbase183';
		// $signature = $param['signature'];
		// $array = array($timestamp,$nonce,$token);
		// sort($array);
		// $tmpstr = implode('',$array);
		// $tmpstr = shal($tmpstr);
		// if($tmpstr == $signature){
		// 	echo $param['echostr'];
		// 	exit;
		// }
		// 默认消息
        $default_msg = <<<EOF
您好，欢迎关注:
名师指导知识一点通公众号！

点击绑定知识一点通，实时查看您的课程
【<a href="https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxebce8b8eae2ddc33&redirect_uri=http%3A%2F%2Fwww.ydtkt.com%2Fweixin%2Fweixing%2FwechatBindPhone&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect">绑定您的手机号</a>】
点击申请20分钟免费试听课+测评报告
EOF;
        //事件推送
        //用户发送的消息类型判断
        switch ($msg->MsgType){
            case "event":
                //事件推送
                switch ($msg->Event){
                    case "subscribe":
                        // 用户关注微信号后 - 回复用户普通文本消息
                        $this->wechat->reply($default_msg);
                        $this->subscribe($msg->FromUserName,1);
                        break;
                    case "unsubscribe":
                        // 用户取消订阅
                        $this->subscribe($msg->FromUserName,2);
                        break;
                    case "CLICK":
                        switch ($msg->EventKey){
                            case "teacher5":
                                //$this->searchTeacher('Sh');
                                $this->wechat->reply("开发中，敬请期待！");
                                break;
                            case "recommend":
                                //被动->文字
                                //客服消息->图片消息
                                $this->recommendQr($msg->FromUserName);
                                $this->recommend($msg->FromUserName);
                                break;
                            default:
                                $this->wechat->reply("开发中，敬请期待！");
                        }
                }
                break;
            case "text":    //文本消息
                // $this->receiveText($msg->FromUserName,$msg->Content);
                // $msg['type'] = 'text';
                // $msg['content'] = '欢迎来到一点通公众号！';
                $msg = '欢迎来到名师指导知识一点通';
                $this->wechat->reply($msg);
                break;
            // 图片
            case "image":    //文本消息
                
                $msg = '你发送的是图片';
                $this->wechat->reply($msg);
                break;
            // 语音
            case "voice":
                $msg = '你发送的是语音';
                $this->wechat->reply($msg);
                break;
            // 视频
            case "video":
                $msg = '你发送的是视频';
                $this->wechat->reply($msg);
                break;
            case "music":
                $msg = '你发送的是音乐';
                $this->wechat->reply($msg);
            default:
                // 默认回复默认信息
                $default_msg = '欢迎来到名师指导知识一点通';
                $this->wechat->reply($default_msg);
                break;
        }
    }
    
	//微信菜单
    public function creatMenu(){
        $arr['button'] = array(
            array(
                "type"     => "miniprogram",
                "name"     => "免费预约",
                "url"      => "http://mp.weixin.qq.com",
                "appid"    => "wx0a503ddac9fdb1f3",//小程序appid
                "pagepath" => "pages/index/index"//跳转的小程序页面
            ),
            array(
                "name"=>"课程体系",
                "sub_button"=>array(
                    array(
                        "type"     => "miniprogram",
                        "name"     => "外教列表",
                        "url"      => "http://mp.weixin.qq.com",
                        "appid"    => "wx0a503ddac9fdb1f3",//小程序appid
                        "pagepath" => "pages/teacherList/index"//跳转的小程序页面
                    ),
                    array(
                        "type"     => "miniprogram",
                        "name"     => "课程介绍",
                        "url"      => "http://mp.weixin.qq.com",
                        "appid"    => "wx0a503ddac9fdb1f3",//小程序appid
                        "pagepath" => "pages/course/index"//跳转的小程序页面
                    )
                )
            ),
            array(
                "name"=>"我的异趣",
                "sub_button"=>array(
                    array(
                        "type"=>"view",
                        "name"=>"在线客服",
                        "url"=>config('SITEURL')."/index.php/Weixin/User/sobot.html"
                    ),
                    array(
                        "type"=>"miniprogram",
                        "name"=>"我的课程",
                        "url"      => "http://mp.weixin.qq.com",
                        "appid"    => "wx0a503ddac9fdb1f3",//小程序appid
                        "pagepath" => "pages/ucenter/index"//跳转的小程序页面
                    ),
                    array(
                        "type"=>"view",
                        "name"=>"我的荣誉",
                        "url"=>config('SITEURL')."/index.php/Weixin/Student/myRecording.html"
                    ),
                    array(
                        "type"     => "click",
                        "name"     => "我要推荐",
                        "key"      => "recommend"
                    ),
                    array(
                        "type"=>"view",
                        "name"=>"打卡赢课时",
                        "url"=>config('SITEURL')."/index.php/Weixin/student/match.html"
                    ),
                )
            )
        );
        $result = $this->api->create_menu(json_encode($arr,JSON_UNESCAPED_UNICODE));
        echo config('SITEURL');
        print_r($result);
    }


    /**
     * 自定义菜单创建接口
     * 一级栏目最多三个， 二级栏目最多5个
     */
    public function createMenu(request $request)
    {
        $idstr = $request->param('idstr');
        if (!$idstr) {
            return jsonMsg('没有选择选项', 1);
        }
        $idArr = explode(',', $idstr);
        // 最多只能生成3个一级，每个一级最多5个二级
        if (count($idArr) > 18) {
            return jsonMsg('推送栏目大于最大可生成栏目', 1);
        }
        $wechatnav = new Wechatnav();
        $navList = $wechatnav->getNavList($idArr);
        $navList = tree($navList);
        if (count($navList) < 1) {
            return jsonMsg('没有选项', 1);
        }

        if (count($navList) > 3) {
            return jsonMsg('一级栏目大于3了', 1);
        }

        foreach ($navList as $key => $value) {
            if (isset($value['children']) && count($value['children']) > 5) {
                return jsonMsg('二级栏目大于5了', 1);
            }
        }

        $json = [
            'button' => []
        ];

        foreach ($navList as $k => $v) {
            
            if ($v['children']) {
                $childrenArr = [];
                foreach ($v['children'] as $kl => $vl) {
                    $arr = [
                            'name' => $vl['name'],
                            'type' => $vl['type'],
                            'key'  => !empty($vl['key']) ? $vl['key'] : '',
                            'url'  => !empty($vl['url']) ? $vl['url'] : '',
                            'appid' => !empty($vl['appid']) ? $vl['appid'] : '',
                            'pagepath' => !empty($vl['pagepath']) ? $vl['pagepath'] : ''
                    ];
                    array_push($childrenArr, $arr);
                }
                $oneArr = [
                    'name' => $v['name'],
                    'sub_button' => $childrenArr
                ];

            } else {
                $oneArr = [
                    'name' => $v['name'],
                    'type' => $v['type'],
                    'key'  => !empty($v['key']) ? $v['key'] : '',
                    'url'  => !empty($v['url']) ? $v['url'] : '',
                    'appid' => !empty($v['appid']) ? $v['appid'] : '',
                    'pagepath' => !empty($v['pagepath']) ? $v['pagepath'] : ''
                ];
            }

           array_push($json['button'], $oneArr);
        }

        $json = json_encode($json, JSON_UNESCAPED_UNICODE);

        // 创建菜单参数为json字符串
        $res = $this->api->create_menu($json);
        Db::name('wechat_nav')->where(['id' => ['in', $idArr]])->update(['is_tab' => 1]);
        Db::name('wechat_nav')->where(['id' => ['not in', $idArr]])->update(['is_tab' => 0]);
        return jsonMsg('修改成功', 0);
        // if ($res['errcode'] == 0) {
        //     Db::name('wechat_nav')->where(['id' => ['in', $idArr]])->update(['is_tab' => 1]);
        //     return jsonMsg('修改成功', 0);
        // } else {
        //     return jsonMsg($res['errmsg'], 1);
        // }
    }


    /**
     * 发送学习计划模板消息
     */

    public function sendNotice()
    {
        $personModel = new PersonModel();

        // 获取关注微信公众号用户微信数据
        $userList = $personModel->getWeChatUser();
        if (empty($userList)) {
            return false;
        }

        $start_week_end = $this->getStartTimeOrEndTime();

        $beginTime = date('Y-m-d', $start_week_end['beginTime']);
        $endTime = date('Y-m-d', $start_week_end['endTime']);

        foreach ($userList as $key => $value) {
            $userStudy = $personModel->getStudyInfo($value['pid'], $start_week_end['beginTime'], $start_week_end['endTime']);
            $courseName = !empty($userStudy) ? array_column($userStudy, 'coursename') : array();
            if (!$courseName) continue;

            if (count($courseName) > 1) {
                $courseName = explode(',', $courseName);
            } else {
                $courseName = $courseName[0];
            }

            $studyTime = $this->getStudyTime($userStudy);
            // 组装推送周报数据
            $data = [
                'first' => [
                    'value' => '学习周报更新通知',
                ],

                'keyword1' => [
                    'value' =>  empty($userStudy[0]['nickName']) ? $userStudy[0]['nickName'] : '一点通用户' 
                ],
                'keyword2' => [
                    'value' => $courseName 
                ],
                'keyword3' => [
                    'value' =>  $beginTime. '到'. $endTime 
                ],

                'remark' => [
                    'value' => '点击查看详情'
                ]
            ];

            $json = [
                'touser' => $value['openid'],
                'template_id' => $this->templateId,
                'url' => 'http://www.ydtkt.com/weixin/weixin/notciInfo?uid='.$userStudy[0]['person_id'].'&beginTime='.$beginTime.'&endTime'.$endTime,
                'data' => $data,
                'appid' => $this->appId
            ];

            $json = json_encode($json);
          
            $this->api->sendTemp($json);
        }
    }

    
    public function sendOrderSuccess($order_id)
    {
        $order_id = input('order_id');

        if (!$order_id) {
            return false;
        }
        
        $orderInfo = Db::name('order_person')->where(['order' => $order_id, 'orderCheck' => 2])->find();
        if (!$orderInfo) {
            return false;
        }
        
        //用户是否绑定微信公众号
        $personInfo = Db::name('wx_person')->where(['pid' => $orderInfo['person_id'], 'openid' => ['neq', '']])->find();
        if (!$personInfo) {
            return false;
        }
       
        // 订单信息
        $orderSon = Db::name('order_person_son')->where(['person_id' => $orderInfo['person_id'], 'order_id' => $order_id, 'orderCheck' => 2])->select();
        if (!$orderSon) {
            return false;
        }
        
        $videoClassId = array_column($orderSon, 'video_class_id');
        
        $field = 'id, price, name, (case Semester
                        when 1 then "上学期"
                        when 2 then "下学期"
                        when 3 then "全册"
                        else ""
                    end
                    ) as semester
                ';
        $courseName = Db::name('video_class')
                    ->field($field)
                    ->where(['id' => ['in', $videoClassId]])
                    ->select();
                    
        if (!$courseName) {
            return false;
        }
        foreach ($courseName as $key => $value) {
            $courseName[$key]['courseName'] = $value['name'].$value['semester'];
        }
        
        $courseName = array_column($courseName, 'courseName');
        
        $courseName = implode(' / ', $courseName);
        
        // 组装推送周报数据
        $data = [
            'first' => [
                'value' => '恭喜您成功购买了课程',
            ],

            'keyword1' => [
                'value' =>  $order_id 
            ],
            'keyword2' => [
                'value' => $courseName 
            ],
            'keyword3' => [
                'value' =>  date('Y-m-d H:I:s', time()) 
            ],

            'remark' => [
                'value' => '可以去小程序或PC端激活课程并学习'
            ]
        ];
        
        $json = [
            'touser' => $personInfo['openid'],
            'template_id' => $this->order_success_templateId,
            // 'template_id' => '62Y5DXT1AgsUGc2MwwzQe76V69Ugv4eF9ztOaR5Xino',
            // 'url' => 'http://www.ydtkt.com/weixin/weixin/notciInfo?uid='.$userStudy[0]['person_id'].'&beginTime='.$beginTime.'&endTime'.$endTime,
            'data' => $data,
            'appid' => $this->appId
        ];
        $json = json_encode($json);
      
        $this->api->sendTemp($json);
    }


    /**
     * 模板消息的详情接口
     */
    public function notciInfo()
    {
        $personModel = new PersonModel();
        $param = input();
        
        if (!$param['uid']) {
            return jsonMsg('找不到学习计划',0);
        }
        $studyInfo = $personModel->getStudyInfo($param['uid'], $param['beginTime'], $param['endTime']);
        
        return $studyInfo;
    }

    /**
     * 发送关注公众号消息
     */
    public function sendFollowNotice()
    {
        // 获取微信消息
        $msg = $this->wechat->serve();
        
		// 默认消息
        $default_msg = <<<EOF
您好，欢迎关注:
名师指导知识一点通公众号！

点击绑定知识一点通，实时查看您的课程
【<a href="https://www.ydtkt.com/index.php/Weixin/Login/bindAccount.html">绑定您的手机号</a>】
点击申请20分钟免费试听课+测评报告
EOF;

        //用户发送的消息类型判断
        switch ($msg->MsgType){
            // 事件推送类型
            case "event":
                //事件推送
                switch ($msg->Event){
                    case "subscribe":
                        // 用户关注微信号后 - 回复用户普通文本消息
                        $this->wechat->reply($default_msg);
                        $this->subscribe($msg->FromUserName,1);
                        break;
                    case "unsubscribe":
                        // 用户取消订阅
                        $this->subscribe($msg->FromUserName,2);
                        break;
                    case "CLICK":
                        switch ($msg->EventKey){
                            case "teacher5":
                                //$this->searchTeacher('Sh');
                                $this->wechat->reply("开发中，敬请期待！");
                                break;
                            case "recommend":
                                //被动->文字
                                //客服消息->图片消息
                                $this->recommendQr($msg->FromUserName);
                                $this->recommend($msg->FromUserName);
                                break;
                            default:
                                $this->wechat->reply("开发中，敬请期待！");
                        }
                }
                break;
            case "text":    //文本消息

                // $this->receiveText($msg->FromUserName,$msg->Content);
                
                $msg['type'] = 'text';
                $msg['content'] = '欢迎来到一点通公众号！';
                $this->wechat->reply($msg);
                break;
            default:
                // 默认回复默认信息
                $this->wechat->reply($default_msg);
                break;
        }

    }

    /**
     * 根据学习周期获取学习时间
     */
    public function getStudyTime($weekStudy)
    {
        $time = 0;
        if (!isset($weekStudy) || !$weekStudy) {
            return $time;
        }

        $timeArr = array_column($weekStudy, 'totalTime');
        $num = count($timeArr);
        
        for ($i = 0; $i < $num; $i++) {
            if (is_numeric($timeArr[$i])) {
                $time += (int) $timeArr[$i];
            } else {
                $time += $this->getSecond($timeArr[$i]);
            }
        }

        return $time;
        
    }


    /**
     * 获取前一个星期的开始时间到结束时间的时间戳
     */
    private function getStartTimeOrEndTime($week_start = 1, $now_time = 0)
    {
        $now_time = $now_time > 0 ? $now_time : time();
        $now_week = date('w', $now_time);
        $week_start = in_array($now_week, [0, 1, 2, 3, 4, 5, 6]) ? $week_start : 1;
        $now_weekday = $now_week < $week_start ? $now_week + 7 : $now_week;
        $beginLastweek  = $now_time-($now_weekday+7-$week_start)*86400;
        $endLastweek    = $beginLastweek+(6*86400);

        return array(
            'beginTime' => strtotime(date('Y-m-d', $beginLastweek)),
            'endTime'   => strtotime(date('Y-m-d', $endLastweek))
        );
    }


    /**
     * 根据日期格式获取时间秒
     * @author yangjizhou 2019-06-12
     */
    public function getSecond($date)
    {
        $second = 0;
        if (!isset($date) || empty($date)) return $second;

        $timeArr = explode(':', $date);
        $count = count($timeArr);
        switch ($count) {
            case '2':
                $second = (int) $timeArr[0] * 60 + (int) $timeArr[1];
                break;
            case '3':
                $second = (int) $timeArr[0] * 60 * 60 + (int) $timeArr[1] * 60 + (int) $timeArr[2];
                break;
            default:
                break;
        }

        return $second;
    }
}