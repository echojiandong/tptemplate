<?php
namespace app\index\model;
use think\Db;
class LoginModel
{
	private $LoginDb;

    public function __construct()
    {
        $this->LoginDb = Db::name('person');
    }
    //注册用户账号
    public function registerPerson()
    {
        $param=input();
        $data['phone']=$param['phone'];
        $data['password']=user_md5($param['password']);
        $data['status']=1;
        $data['is_tourist']=0;   //标记身份是游客
        $data['register_from']=$param['type'];   //标记注册来源
        $data['addtime']=time();
        $data['birthday']=time();
        return $this->LoginDb->insertGetId($data);
    }
    //判断用户是否已经注册过账号
    public function judgeRegister()
    {
        $param=input();
        $where['phone']=$param['phone'];
        return $this->LoginDb->where($where)->find();
    }
    //用户账号密码登陆
    public function pwdLogin()
    {
        $param=input();
        $where['phone']=$param['phone'];
        $where['password']=user_md5($param['password']);
        return $this->LoginDb->where($where)->find();
    }
    //验证码登陆
    public function VerifyCodeLogin()
    {
        $param=input();
        $where['phone']=$param['phone'];
        return $this->LoginDb->where($where)->find();
    }
    //修改密码
    public function forgetPassword()
    {
        $param=input();
        $where['phone']=$param['phone'];
        $data['password']=user_md5($param['password']);
        return $this->LoginDb->where($where)->update($data);
    }
}