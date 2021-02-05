<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Cookie;
use think\Db;
use think\Image;
class auth extends Communal
{
    public function _initialize() {
        parent::_initialize();

        if(empty($this ->_info)){
            
            return $this->redirect("/");
        }
        // else{
        // 	//用户登陆验证登陆信息
        // 	$res=Db::name('person')->alias('token')->where(array('id'=>$userinfo['id']))->find();
        // 	if($userinfo['token']!=$res['token']){
        // 		Session::delete('user');
        // 		echo "<script>alert('您的账号在别处登陆！');location=location;</script>";
        // 	}
        // 	$this->assign('user',$userinfo);
        // }
    }
}