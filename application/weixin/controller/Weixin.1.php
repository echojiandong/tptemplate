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
	//微信初始化
    public function _initialize(){
        // 开发者中心-配置项-AppID(应用ID)
        $appId = config('WX_appId');
        // 开发者中心-配置项-AppSecret(应用密钥)
        $appSecret = config('WX_appSecret');
        // 开发者中心-配置项-服务器配置-Token(令牌)
        $token = config('WX_token');
        // 开发者中心-配置项-服务器配置-EncodingAESKey(消息加解密密钥)
        $encodingAESKey = config('WX_encodingAESKey');

        // 模板消息ID
        $templateId = config('WX_templateId');
        // wechat模块 - 处理用户发送的消息和回复消息
        $this->wechat = new Wechat(array(
            'appId' => $appId,
            'token' => 	$token,
            'encodingAESKey' =>	$encodingAESKey //可选
        ));
        //@error_log('weixin controller start',0);
        // api模块 - 包含各种系统主动发起的功能
        $this->api = new Api(
            array(
                'appId' => $appId,
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
【<a href="https://www.ydtkt.com/index.php/Weixin/Login/bindAccount.html">绑定您的手机号</a>】
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
                $this->receiveText($msg->FromUserName,$msg->Content);
                break;
            default:
                // 默认回复默认信息
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
                        "type"     => "view",
                        "name"     => "个人中心",
                        "url"      => "http://www.ydtkt.com/weixin/weixing/test"
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
                    'key'  => !empty($vl['key']) ? $vl['key'] : '',
                    'url'  => !empty($vl['url']) ? $vl['url'] : '',
                    'appid' => !empty($vl['appid']) ? $vl['appid'] : '',
                    'pagepath' => !empty($vl['pagepath']) ? $vl['pagepath'] : ''
                ];
            }

           array_push($json['button'], $oneArr);
        }

        $json = json_encode($json, JSON_UNESCAPED_UNICODE);

        // 创建菜单参数为json字符串
        $res = $this->api->create_menu($json);
        if ($res['errcode'] == 0) {
            Db::name('wechat_nav')->where(['id' => ['in', $idArr]])->update(['is_tab' => 1]);
            return jsonMsg('修改成功', 0);
        } else {
            return jsonMsg($res['errmsg'], 1);
        }
    }


    /**
     * 发送模板消息
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
            $userStudy = $personModel->getStudyInfo($value['id'], $start_week_end['beginTime'], $start_week_end['endTime']);
            $courseName = !empty($userStudy) ? array_column($userStudy, 'coursename') : array();
            if (!$courseName) continue;
            $courseName = explode(',', $courseName);
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
                'keyword1' => [
                    'value' =>  $beginTime. '到'. $endTime 
                ],

                'keyword6' => [
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

    public function wechatView()
    {
        return $this->fetch('weixin/index');
    }

    /**
     * 公众号绑定手机号码
     */
    public function wechatBindPhone(Request $request)
    {
        $code = $request->param('code');
        if (!$code) {
            return jsonMsg('非法登录', 1);
        }
        $res = $this->getAccessToken($code);
        if (isset($res['error_code']) && $res['error_code'] != 0) {
            return jsonMsg('获取access_token失败', 1);
        }
        $access_token = $res['access_token'];
        $openid       = $res['openid'];
        if (!$access_token || !$openid) {
            return jsonMsg('access_token无效', 1);
        }
        $userinfo = $this->getUserInfo($access_token, $openid);
       
        if (isset($userinfo['errcode']) && $userinfo['errcode'] != 0) {
            return jsonMsg('获取用户失败', 1);
        }

        // 判断是否已绑定
        $isBind = Db::name('person')->where(['openid' => $userinfo['openid']])->find();
        if ($isBind && $isBind['act_status'] == 3) {
            return jsonMsg('当前账号存在异常，已被封禁，请联系代理商或者客服咨询！', 1);
        }

        if ($isBind) {
            // 已绑定、直接登录
            $rts['phone']=$isBind['phone'];
            $rts['id']=$isBind['id'];
            $pid = getmypid();
            time_nanosleep(0, 1000);  
            $timetick = microtime(TRUE)*1000;
            $uuid = hash('ripemd160', $pid.'+'.$timetick);
            $rts['token']=$uuid;
            Session::set('user',$rts);
        } else {
            // 未绑定 1.已注册号码（绑定）， 2未注册号码（新注册并绑定）
            // return jsonMsg('未绑定账号', 2, $userinfo);  // 前端根据错误码去调用页面
            return $this->fetch('/weixin/index');
        }
    }

    /**
     *  未绑定 1.已注册号码（绑定）， 2未注册号码（新注册并绑定）
     */
    public function bindUserInfo(Request $request)
    {
        $param = $request->param();
        $code = $param['code']; // 验证码
        $phone = $param['phone'];

        // 微信用户信息
        $openid = $param['openid'];
        $nickname = $param['nickname'];
        $sex      = $param['sex'];
        $photoUrl = $param['headimgurl'];
        $city     = $param['city'];
        $province = $param['province'];
        $country  = $param['country'];

        $sendCode = $this->verifySendCode($phone, $code);
        if (!$sendCode) {
            return jsonMsg('验证码错误,请重新输入', 1);
        }
        $userInfo = Db::name('person')->where(['phone' => $phone])->find();
        if ($userInfo) {
            // 1
            $data = [
                'openid' => $openid,
                'up_time' => time()
            ];
            Db::name('person')->where(['phone' => $phone])->update($data);
            // 执行session写入
            $rts['phone']=$userInfo['phone'];
            $rts['id']=$userInfo['id'];
            $pid = getmypid();
            time_nanosleep(0, 1000);  
            $timetick = microtime(TRUE)*1000;
            $uuid = hash('ripemd160', $pid.'+'.$timetick);
            $rts['token']=$uuid;
            Session::set('user',$rts);
        } else {
            $pass = user_md5(substr($phone, -1, 6));
            // 2
            $path = ROOT_PATH . 'public' . DS . 'upload/uploads/wechat/'; // 下载到的路径
            if (is_dir($path)) {
                mkdir($path, 0777, true);
            }

            $filename = $phone.'.jpg';  // 文件名
            $this->download($url, $path, $filename);  // 执行下载
            
            $data = [
                'phone'     => $phone,
                'nickName'  => $nickname,
                'litpic'    => $path.$filename,
                'gender'    => $sex,
                'city'      => $city,
                'province'  => $province,
                'country'   => $country,
                'openid'    => $openid,
                'addtime'   => date('Y-m-d H:i:s', time()),
                'password'  => $pass
            ];
            // 执行插入
            $id = Db::name('person')->insertGetId($data);
            $res = Db::name('person')->where(['id' => $id])->find();
            if ($id && $res) {
                // 执行session写入
                $rts['phone']=$res['phone'];
                $rts['id']=$res['id'];
                $pid = getmypid();
                time_nanosleep(0, 1000);  
                $timetick = microtime(TRUE)*1000;
                $uuid = hash('ripemd160', $pid.'+'.$timetick);
                $rts['token']=$uuid;
                Session::set('user',$rts);
            }
        }
    }

    /**
     * 微信公众号绑定账号获取验证码
     */
    public function wechatVerifyCode(Request $request)
    {
        $phone=$request->param('phone');
        if (empty($phone)) {
            return jsonMsg('请输入手机号码', 1);
        }
        $isPhone = checkphone($phone);
        if (false == $isPhone) {
            return jsonMsg('手机号码格式有误', 1);
        }
        // 发送验证码
        $sendSms = VerifyCode($phone);
        if ($sendSms) {
            return jsonMsg('验证码发送成功！', 0);
        }else{
            return jsonMsg('验证码发送失败！', 1);
        }
    }

    // 验证输入验证码是否正确
    private function verifySendCode($phone, $code)
    {
        $sendCode=Session::get($phone);
        if($code === $sendCode) {
            session($param['phone'],null);
            return true;
        }else{
            return false;
        }
    }


    /**
     * 根据图片url下载图片
     * @param $url
     * @param string $path
     * @param $filename
     */
    private function download($url, $path = '', $filename)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $file = curl_exec($ch);
        curl_close($ch);
        $resource = fopen($path . $filename, 'a');
        fwrite($resource, $file);
        fclose($resource);
    }

    /**
     * 获取access_token
     */
    private function getAccessToken($code)
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$this->appId&secret=$this->appSecret&code=$code&grant_type=authorization_code";
        // 验证access_token是否有效
        $html = file_get_contents($url);
        $res = json_decode($html, true);
        if (isset($res['errcode']) && $res['errcode'] != 0) {
            return jsonMsg($res['errmsg'], $res['errcode']);
        }
        return $res;
    }

    /**
     *  验证access_token是否有效
     */
    private function verifyAccessToken($access_token, $openid)
    {
        $url = "https://api.weixin.qq.com/sns/auth?access_token=$access_token&openid=$openid";
        // 验证access_token是否有效
        $html = file_get_contents($url);
        $res = json_decode($html, true);
        if ($res['errcode'] != 0) {
            $this->json_return('access_token无效', [], false, 422, -1);
        }
    }

    /**
     * 获取用户信息
     */
    private function getUserInfo($access_token, $openid)
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid&lang=zh_CN";
        // 验证access_token是否有效
        $html = file_get_contents($url);
        $res = json_decode($html, true);
        if (isset($res['errcode']) && $res['errcode'] != 0) {
            return jsonMsg($res['errmsg'], $res['errcode']);
        }
        return $res;
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