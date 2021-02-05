<?php
namespace app\weixin\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\Cache;
use \WechatPhpSdk\Wechat;
use \WechatPhpSdk\Api;
use app\weixin\model\PersonModel;
use app\manage\model\Wechatnav;

class Weixing extends Controller
{
    protected $appId;
    protected $appSecret;

    public function _initialize()
    {
        $this->appId = config('WX_appId');
        $this->appSecret = config('WX_appSecret');
    }

    /**
     * 显示小程序二维码
     */
    public function goWx()
    {
        return $this->fetch('/weixin/go_weixin');
    }

    /**
     * 公众号绑定手机号码
     */
    public function wechatBindPhone(Request $request)
    {
        header("Content-type: text/html; charset=utf-8");
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($user_agent, 'MicroMessenger') === false) {
            $this->redirect('index/course/goWx');
        } 

        $code = $request->param('code');
        // $scop = 'snsapi_userinfo';
        // $res = $this->api->get_userinfo_by_authorize($scop, $code);
        $openid = Cache::get('openid'.$code);
        $code1 = Cache::get('code'.$code);
        if ($code1 && $code == $code1) {
            // 刷新
            $userinfo = Cache::get('userinfo'.$code);
        } else {
            if (!$openid) {
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
                $openid = $userinfo['openid'];
                Cache::set('openid'.$code, $openid, 900);
                Cache::set('code'.$code, $code, 900);
                Cache::set('userinfo'.$code, $userinfo, 900);
            }
        }
        

        // 判断是否已绑定
        // $isBind = Db::name('person')->where(['openid' => $userinfo['openid']])->find();
        
        // if ($isBind && $isBind['act_status'] == 3) {
        //     return jsonMsg('当前账号存在异常，已被封禁，请联系代理商或者客服咨询！', 1);
        // }
        $isBind = Db::name('wx_person')->where(['openid' => $openid])->find();

        // $bindCount = Db::name('wx_person')->where(['pid' => $isBind['pid']])->count();
        // if ($bindCount > 4) {
        //     return jsonMsg('最多只能绑定4个微信账号', 1);
        // }
        
        if ($isBind) {
            $this->assign('isbind', 1);
            return $this->goWx();
            // $user = Db::name('person')->where(['id' => $isBind['pid']])->find();
            // // 已绑定、直接登录
            // $rts['phone']=$user['phone'];
            // $rts['id']=$user['id'];
            // $pid = getmypid();
            // time_nanosleep(0, 1000);  
            // $timetick = microtime(TRUE)*1000;
            // $uuid = hash('ripemd160', $pid.'+'.$timetick);
            // $rts['token']=$uuid;
            // Session::set('user',$rts);
            // $url='http://www.ydtkt.com/';
            // header('location:'.$url);
        } else {
            // 未绑定 1.已注册号码（绑定）， 2未注册号码（新注册并绑定）
            // return jsonMsg('未绑定账号', 2, $userinfo);  // 前端根据错误码去调用页面
            
            $this->assign('userinfo', isset($userinfo) && !empty($userinfo) ? $userinfo : array());
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
        $unionid  = $param['unionid'];

        $sendCode = $this->verifySendCode($phone, $code);
        if (!$sendCode) {
            return jsonMsg('验证码错误,请重新输入', 1);
        }

        $userInfo = Db::name('person')->where(['phone' => $phone])->find();
        
        $wxPerson  = Db::name('wx_person')->where(['pid' => $userInfo['id'], 'openid' => $openid, 'unionid' => $unionid])->find();
        if ($wxPerson) {
            return jsonMsg('该微信号已绑定过公众号', 2);
        }

        if ($userInfo) {
            $person = Db::name('wx_person')->where(['pid' => $userInfo['id'], 'unionid' => $unionid])->find();
            if ($person) {
                Db::name('wx_person')->where(['pid' => $userInfo['id'], 'unionid' => $unionid])->update(['openid' => $openid]);
            } else {
                $data = [
                    'pid' => $userInfo['id'],
                    'nickName' => $nickname,
                    'openid' => $openid,
                    'addtime' => time(),
                    'unionid' => $unionid,
                    'province' => $province,
                    'city'    => $city,
                    'sex'     => $sex
                ];
                Db::name('wx_person')->insert($data);
            }
            

            return jsonMsg('绑定成功', 0);

            
            // return $this->wechatBindPhone();
            // header('location:'.$url);
           
            // echo " <script>$(function(){location.reload()})</script>";
            // return $this->redirect('/weixin/weixin/goWx');
            // return $this->goWx();
            // 执行session写入
            // $rts['phone']=$userInfo['phone'];
            // $rts['id']=$userInfo['id'];
            // $pid = getmypid();
            // time_nanosleep(0, 1000);  
            // $timetick = microtime(TRUE)*1000;
            // $uuid = hash('ripemd160', $pid.'+'.$timetick);
            // $rts['token']=$uuid;
            // Session::set('user',$rts);
            // $url='http://www.ydtkt.com/';
            // header('location:'.$url);
            
        } else {
            return jsonMsg('该手机号还未注册为会员，请先注册为会员', 1);

            // $pass = user_md5(substr($phone, -6));
            // // 2
            // $path = ROOT_PATH . 'public' . DS . 'upload/uploads/wechat/'; // 下载到的路径
            // if (!is_dir($path)) {
            //     mkdir($path, 0777, true);
            // }

            // $filename = $phone.'.jpg';  // 文件名
            // $this->download($photoUrl, $path, $filename);  // 执行下载
            
            // $data = [
            //     'phone'     => $phone,
            //     'nickName'  => $nickname,
            //     'litpic'    => 'wechat/'.$filename,
            //     'gender'    => $sex,
            //     'city'      => $city,
            //     'province'  => $province,
            //     'country'   => $country,
            //     'openid'    => $openid,
            //     'addtime'   => date('Y-m-d H:i:s', time()),
            //     'password'  => $pass
            // ];
            // // 执行插入
            // $id = Db::name('person')->insertGetId($data);
            // $res = Db::name('person')->where(['id' => $id])->find();
            // if ($id && $res) {
            //     // 执行session写入
            //     $rts['phone']=$res['phone'];
            //     $rts['id']=$res['id'];
            //     $pid = getmypid();
            //     time_nanosleep(0, 1000);  
            //     $timetick = microtime(TRUE)*1000;
            //     $uuid = hash('ripemd160', $pid.'+'.$timetick);
            //     $rts['token']=$uuid;
            //     Session::set('user',$rts);
            // }
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

        $personinfo = Db::name('person')->where(['phone' => $phone])->find();
        if (!$personinfo) {
            return jsonMsg('该手机号未注册，请先注册', 1);
        }
        $bindCount = Db::name('wx_person')->where(['pid' => $personinfo['id'], 'openid' => ['neq', '']])->count();
        if ($bindCount > 4) {
            return jsonMsg('一个手机号码最多绑定4个公众号', 1);
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
    public function verifySendCode($phone, $code)
    {
        // 使用数据库
        $res = Db::name('check_code')->where(['phone' => $phone, 'code' => $code])->find();
        // 时间
        $time = time();

        if ($res && $time > $res['expire_time']) {
            Db::name('check_code')->where(['id' => $res['id']])->delete();
            return jsonMsg('验证码已过期');
        } 

        if ($res) {
            Db::name('check_code')->where(['id' => $res['id']])->delete();
            return true;
        } else {
            return false;
        }

        // 使用缓存
        // $sendCode=Cache::get($phone);
        // if($code === $sendCode) {
        //     session($phone,null);
        //     return true;
        // }else{
        //     return false;
        // }
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

}
