<?php
namespace app\manage\controller;
use think\Controller;
use think\Request;
use app\manage\model\Menu;
use think\Session;
use think\Cookie;
use think\Db;
use think\Image;
class author extends Controller
{
    public function _initialize() {
        //$userinfo = Session::get("manageinfo");
        $userinfo =Cookie::get("manageinfo");
        if(!$userinfo){return $this->redirect("/manage/login/showlogin");}
        $userinfo=mydecrypt($userinfo,config('encrypt_key_common'));
        $userinfo=base64_decode($userinfo);
        $userinfo=json_decode($userinfo,true);//转换成json
        Session::set("manageinfo",$userinfo);
        $pathinfo = '/'.Request::instance() ->pathinfo();
        $hrefs = '';
        // echo $pathinfo.'pppp';
        //权限拦截
        $where['u.groupid']=Session::get('manageinfo')['group_id'];
        $rs = Db::name("menu")
                            ->alias('m')
                            ->field('m.*')
                            ->join("guard_user_group u","m.id=u.menu_id")
                            ->where($where)
                            ->order("m.menuid",'asc')
                            ->select();
        foreach($rs as &$val){
        	$hrefs .= $val['href'];
        }
        unset($val);
        $rbac = strstr($hrefs,$pathinfo);
        // 首页菜单url
        $homepage = ['/manage/index/main'       // 首页内容展示
                    ,'/manage/index/getnavs'    // 左侧菜单栏
                    ,'/manage/admin_contorller/changePass'     //修改密码
                    ,'/manage/index/loginout'   // 退出登录
                    ,'/manage/index/index'
                    ,'/manage/admin_contorller/ChangePassword'
                    ,'/manage/index/incomefuc'//首页折线图
                    ,'/manage/index/visitorsfuc'];//首页折线图
        //权限拦截
        // if(!$rbac && !in_array($pathinfo, $homepage)){

        // 	die('非法访问');
        // }
    }
}
