<?php
namespace app\weixin\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use \WechatPhpSdk\Wechat;
use \WechatPhpSdk\Api;
use think\Cache;
use app\weixin\model\PersonModel;
use app\index\model\LoginModel;
vendor('WxBizDataCrypt.WXBizDataCrypt');
class Wxapplet extends Controller
{
	//微信初始化
    private $appId;
    private $appSecret;
    public function _initialize(){
        // 开发者中心-配置项-AppID(应用ID)
        $this->appId = config('WX_small_appId');
        // 开发者中心-配置项-AppSecret(应用密钥)
        $this->appSecret = config('WX_small_appSecret');
    }
    //获取用户小程序openid
    public function getOpenid(Request $request){
        $param = input();
        // $phone = $param['phone'];
        $code = $param['code'];
        if(!$code){
            jsonMsg('code不存在',0);
        }
        $url ="https://api.weixin.qq.com/sns/jscode2session?appid=$this->appId&secret=$this->appSecret&js_code=$code&grant_type=authorization_code";
        $info = juhecurl($url);
        $info = json_decode($info,true);
        if (isset($info['errcode']) && $info['errcode'] != 0) {
            // 容错处理
            jsonMsg($info['errmsg'],0,$info['errcode']);
        }

        $appid = $this->appId;

        $sessionKey = $info['session_key'];

        $encryptedData = $param['encryptedData'];
        $iv = $param['iv'];
        $pc = new \WXBizDataCrypt($appid, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data );
        if ($errCode != 0) {
            //解密失败
            jsonMsg('error',0);
        }

        $person = new PersonModel();

        $userinfo = $person->getUserInfoByOpenid($data, $param['uid']);

        if ($userinfo) {

            ipLog($param['uid']);
        }else{

            jsonMsg('获取授权失败',0);
        }

        jsonMsg('success',1,$param['uid']);
    }

    //用户账号密码注册登录
    // public function registerLogin(){
    //     $param = input();
    //     $phone = $param['phone'];
    //     //判断手机号是否存在
    //     $result = Db::name('person')->where('phone',$phone)->find();
    //     if($result){  //用户登录
    //         $where['phone']=$param['phone'];
    //         $where['password']=user_md5($param['password']);
    //         $res = Db::name('person')->where($where)->find();
    //         if(!empty($res)){
    //             if($res['act_status'] != 3){
    //                 jsonMsg("登陆成功",1,'success',$res['id']);
    //             }else{
    //                 jsonMsg("用户被禁用",0);
    //             }
    //         }else{
    //             jsonMsg("账号密码错误",0);
    //         }
    //     }else{   //用户注册
    //         $LoginDb=new LoginModel();
    //         if(preg_match("/^1[3456789]{1}\d{9}$/", $param['phone'])){
    //             $rts=$LoginDb->registerPerson();
    //             if($rts){
    //                 //获取用户id
    //                 $res = Db::name('person')->where('phone',$phone)->find();
    //                 return jsonMsg('注册成功！请登陆学习',1,'success',$res['id']);
    //             }else{
    //                 return jsonMsg('注册失败！',0);
    //             }
    //         }else{
    //             jsonMsg("手机号格式错误",0);
    //         }
    //     }
    // }

    public function register(){

        $param = input();

        $phone = isset($param['phone']) ? $param['phone'] : '';

        $code = isset($param['code']) ? $param['code'] : '';

        // $_code = Cache::get($phone);
        //获取验证码
        $where['phone']=$param['phone'];
        $where['expire_time']=array('egt',time());
        $codeList = Db::name('check_code')->where($where)->order('id desc')->find();

        if($code != $codeList['code'] || $code == ''){

            jsonMsg('验证码错误',0);
        }

        $is_zhuce = Db::name('person') ->field('id') ->where(['phone' => $phone]) ->find();

        if(!empty($is_zhuce)){

            jsonMsg('该账号已存在',0);
        }

        $password = isset($param['registerPwd']) ? $param['registerPwd'] : '';

        if($phone == '' || $password == ''){

            jsonMsg('请填写完整信息',0);
        }

        $data = ['phone' => $phone, 'password' => user_md5($password), 'addtime' => time(), 'register_from' => 2, 'is_tourist' => 0];

        $id = Db::name('person') ->insertGetId($data);
        // $id = 30;

        if($id){
            // 注册发送一条用户注册消息
            $title = '用户注册消息提示';
            $content = config('message')['registerMsg'];
            insertMessage($id, $title, $content);

            jsonMsg('success', 1, $id);
        }else{

            jsonMsg('注册失败', 0);
        }

    }

    public function procedureslogin(){
        $param = input();

        $phone = $param['phone'];
        //判断手机号是否存在
        $where['phone']=$param['phone'];

        $where['password']=user_md5($param['password']);

        $res = Db::name('person')->where($where)->find();

        if(empty($res)){

            jsonMsg("账号密码错误",0);
        }

        if($res['act_status'] == 3){

            jsonMsg("用户被禁用",0);
        }

        jsonMsg("登陆成功",1,'success',$res['id']);

    }

}