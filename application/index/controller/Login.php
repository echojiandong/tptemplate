<?php
namespace app\index\controller;
use app\index\model\PersonModel;
use app\index\model\IndexModel;
use app\index\model\LoginModel;
use think\Controller;
use think\Request;
use think\Session;
use think\Cookie;
use think\Cache;
use think\Db;
use think\image;
use think\Page;
use think\Config; 

class Login extends Communal
{    
    private $person; //用户
    public function _initialize() {
        parent::_initialize();
        $this->url=Config('sendUrl');
        $this->url1=Config('smsConf');
        //头部用户信息
        $this->person = new PersonModel($this ->_info);
        $this ->userinfo =  $this->person->GetPerson();
        $this->assign('personInfo',$this ->userinfo);
    }
	// 登陆方法 密码登录
    public function pwdLogin()
    {
    	$LoginDb=new LoginModel();
        $param=input();
        //判断用户登录的账号是子账号还是父账号
        if(preg_match("/^1[3456789]{1}\d{9}$/", $param['phone'])){

            $where['phone']=$param['phone'];
            $where['password']=user_md5($param['password']);
            $res = Db::name('person')->where($where)->find();
            if(!empty($res)){
                if($res['act_status'] != 3){
                    $rts['phone']=$res['phone'];
                    $rts['id']=$res['id'];
                    $rts['first_login']=$res['first_login'];
                    $rts['addtime']=$res['addtime'];
                    $pid = getmypid();
                    time_nanosleep(0, 1000);  
                    $timetick = microtime(TRUE)*1000;
                    $uuid = hash('ripemd160', $pid.'+'.$timetick);
                    $rts['token']=$uuid;
                    $rts['sessionId'] = session_id();
                    $data_person['token'] = $rts['sessionId'];
                    //更新session_id
                    Db::name('person')->where(['id'=>$res['id']])->update($data_person);
                    ipLog($rts['id']);
                    //数据加密
                    $rts=json_encode($rts,true);//转换成json
                    $rts=base64_encode($rts);//做base64加密
                    $rts=myencrypt($rts,config('encrypt_key_common'));
                    cookie::set('user',$rts);
                    // Session::set('user',$rts);
                    // 储存客户端IP
                    if($res['first_login'] == 1){
                        $data['first_login'] = 0;
                        Db::name('person')->where($where)->update($data);
                    }
                    jsonMsg("登陆成功,正在跳转....",1,'/index/person/person',$res['id']);
                }else{
                     jsonMsg("当前账号存在异常，已被封禁，请联系代理商或者客服咨询！", 2);
                }
            }else{
                jsonMsg("账号或密码错误",2);
            }
        }else{
            jsonMsg("手机号格式错误，请重新输入",2);
        }
        // else{
        //     $where['son_nickName']=$param['phone'];
        //     $where['son_password']=user_md5($param['password']);

        //     $res = Db::name('person_son')->where($where)->find();
        //     if($res){
        //         $rts['son_nickName']=$res['son_nickName'];
        //         $rts['id']=$res['person_id'];
        //         $rts['person_son_id']=$res['id'];
        //         $pid = getmypid();
        //         time_nanosleep(0, 1000);  
        //         $timetick = microtime(TRUE)*1000;
        //         $uuid = hash('ripemd160', $pid.'+'.$timetick);
        //         $rts['token']=$uuid;
        //         Session::set('user',$rts);
        //         // 储存客户端IP
        //         ipLog($res['id']);
        //         jsonMsg("子用户登陆成功",1,'/index/person/person',$res['id']);
        //     }else{
        //         jsonMsg("账号或密码错误",2);
        //     }
        // }
    }
    //存入全局年级选择
    public function setGlodalClassId(){
        $param = input();
        $grade_id = $param['grade_id'];
        $res = Session::set('Global_grade_id',$grade_id);
        echo $res;
    }
    // 注册
    public function registerPerson()
    {
        $param = input();
        $IndexModel=new IndexModel();
        $IndexModel=$IndexModel->Verification();
        if($IndexModel){
            $LoginDb=new LoginModel();
            //判断用户账号是不是存在
            $res=$LoginDb->judgeRegister();
            if($res){
                //用户已经注册过账号
                return jsonMsg('账号存在，请直接登陆！',1);
            }else{
                $rts=$LoginDb->registerPerson();
                if($rts){
                     // 注册发送一条用户注册消息
                    $title = '用户注册消息提示';
                    $content = config('message')['registerMsg'];
                    insertMessage($rts, $title, $content);
                    //登录成功删除验证码
                    Db::name('check_code')->where(['phone'=>$param['phone']])->delete();
                    return jsonMsg('注册成功！请登陆学习',3,'/index/index/index');
                }else{
                    return jsonMsg('注册失败！',4);
                }
            }
        }else{
            return jsonMsg('验证码错误！',5);
        }
        
    }
    //验证码登陆
    public function VerifyCodeLogin()
    {
        $IndexModel=new IndexModel();
        $IndexModel=$IndexModel->Verification();
        if($IndexModel){
            //验证码正确的情况
            $LoginDb=new LoginModel();
            $res=$LoginDb->VerifyCodeLogin();
            if($res){
                $rts['phone']=$res['phone'];
                $rts['id']=$res['id'];
                $rts['first_login']=$res['first_login'];
                $rts['addtime']=$res['addtime'];
                $pid = getmypid();
                time_nanosleep(0, 1000);  
                $timetick = microtime(TRUE)*1000;
                $uuid = hash('ripemd160', $pid.'+'.$timetick);
                $rts['token']=$uuid;
                $rts['sessionId'] = session_id();

                //更新session_id
                Db::name('person')->where(['id'=>$res['id']])->update(['token'=>$rts['sessionId']]);
                if($res['first_login'] == 1){
                    $data_person['first_login'] = 0;
                    Db::name('person')->where($where)->update($data_person);
                }
                //数据加密
                $rts=json_encode($rts,true);//转换成json
                $rts=base64_encode($rts);//做base64加密
                $rts=myencrypt($rts,config('encrypt_key_common'));
                cookie::set('user',$rts);
                //Session::set('user',$rts);
                //登录成功删除验证码
                Db::name('check_code')->where(['phone'=>$res['phone']])->delete();
                jsonMsg('登陆成功,正在跳转....',1,'/index/person/person',$res['id']);
            }else{
                jsonMsg('对不起，您还不是会员哦！',2);
            }
        }else{
            //验证码错误
            jsonMsg('验证码错误！',3);
        }
    }
    // 忘记密码
    public function forgetPassword()
    {
        $param = input();
        $IndexModel=new IndexModel();
        $IndexModel=$IndexModel->Verification();
        if($IndexModel){
            $LoginDb=new LoginModel();
            $rts=$LoginDb->judgeRegister();
            if($rts){
                $res=$LoginDb->forgetPassword();
                if($res){
                    //修改成功删除验证码
                    Db::name('check_code')->where(['phone'=>$param['phone']])->delete();
                    return jsonMsg('修改成功！',1);
                }else{
                    return jsonMsg('修改失败！',2);
                }
            }else{
                return jsonMsg("账号不存在！",3);
            }  
        }else{
            //验证码错误
            return jsonMsg('验证码错误！',4);
        }
    }
    //判断用户是否登陆
    public function judgeLogin()
    {
        
        if($this ->_info)
        {
            $user=$this ->_info;
            $where['person_id'] = $user['id'];
            //用户登陆获取用户登陆信息 //判断用户是否购买课程
            $videoList = Db::name('video_log')->where(['type' => 0])->where($where)->find();
            $res = Db::name('person')->where('id = '.$user['id'])->find();
            $msg_count = Db::name('message') ->field('count(1) as counts') 
                                             ->where('uid = '.$user['id']) 
                                             ->where(['status' => 0])
                                             ->find();
            //用户已激活卡号 显示学习进度
            if(!empty($videoList)){
                $first_login = $user['first_login'];
                $user['first_login'] =  0;
                //加密
                $user=json_encode($user,true);//转换成json
                $user=base64_encode($user);//做base64加密
                $user=myencrypt($user,config('encrypt_key_common'));
                cookie::set('user',$user);

                jsonMsg(3,$res,$msg_count,$first_login);
            }else{
                $first_login = $user['first_login'];
                $user['first_login'] =  0;
                //加密
                $user=json_encode($user,true);//转换成json
                $user=base64_encode($user);//做base64加密
                $user=myencrypt($user,config('encrypt_key_common'));
                cookie::set('user',$user);
                
                jsonMsg(2,$res,$msg_count,$first_login);
            }
        }else{
            //用户没有登陆
            
            jsonMsg(1);
        }
    }
    //退出登陆
    public function quitLanding()
    {
        Cookie::delete('user');
        $this->redirect("Index/index");
    }
    // 获取验证码
    public function VerifyCode(Request $request)
    {
        $param=$request->param();
        if($param['phone']){
            //判断手机号是否存在
            $LoginDb=new LoginModel();
            //判断用户账号是不是存在
            $res=$LoginDb->judgeRegister();
            if(!empty($res)){
                //获取页面传递过来的号码
                $tel=$param['phone'];
                $aa = VerifyCode($tel);
                if ($aa) {
                    return jsonMsg('验证码发送成功！',1);
                }else{
                    return jsonMsg('验证码发送失败！',0);
                }
            }else{
                return jsonMsg('手机号不存在，请重新输入！',0);
            }
        }else{
            jsonMsg('请输入手机号',0);
        }
    }
    //注册 获取验证码
    public function RegVerifyCode(Request $request)
    {
        $param=$request->param();
        if($param['phone']){
            $LoginDb=new LoginModel();
            //判断用户账号是不是存在
            $res=$LoginDb->judgeRegister();
            if(empty($res)){
                //获取页面传递过来的号码
                $tel=$param['phone'];

                $aa = VerifyCode($tel);
                if ($aa) {
                    return jsonMsg('验证码发送成功！',1);
                }else{
                    return jsonMsg('验证码发送失败！',0);
                }
            }else{
                return jsonMsg('账号存在，请直接登陆！',0);
            }
            
        }else{
            jsonMsg('请输入手机号',0);
        }
    }
    //手机密码登陆页面
    public function commonLogin(){
        return $this->fetch('/index/login/login');
    }
    //手机注册页面
    public function commonRegister(){
        return $this->fetch('/index/login/register');
    }
    // //手机验证码登陆页面
    public function loginCode(){
        return $this->fetch('/index/login/login-code');
    }
    // //手机忘记密码页面
    public function forgetPwd(){
        return $this->fetch('/index/login/forget-pwd');
    }
    //pc 用户协议
    public function agreement(){
        return $this->fetch('/index/public/agreement');
    }
    //pc 404页面
    public function notfound(){
        return $this->fetch('/index/public/404');
    }
    //验证验证码
    public function verificationCode()
    {
        $param=input();
        $IndexModel=new IndexModel();
        $IndexModel=$IndexModel->Verification();
        if($IndexModel)
        {
            //登录成功删除验证码
            Db::name('check_code')->where(['phone'=>$param['phone']])->delete();
            return jsonMsg('验证码正确!',1);
        }else{
            return jsonMsg('验证码错误!',0);
        }
    }
    //购买试听课程获取验证码
    public function VerifyCodePay(Request $request)
    {
        $param=$request->param();
        if($param['phone']){
            //获取页面传递过来的号码
            $tel=$param['phone'];
            $aa = VerifyCode($tel);
            if ($aa) {
                return jsonMsg('验证码发送成功！',1);
            }else{
                return jsonMsg('验证码发送失败！',0);
            }
        }else{
            jsonMsg('请输入手机号',0);
        }
    }
}
