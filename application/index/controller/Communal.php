<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Cookie;
use think\Session;
use think\Db;

class Communal extends Controller{

    public $_info = [];

	public function _initialize(){
        $header = Request::instance()->header();
        Session_start();
        #  header 头登录状态（俩种方式 任选其一）
        // isset($header['content-info']) && $this ->_info = decode_function($header['content-info']);
        
        Cookie::has('user') && $this ->_info = json_decode(base64_decode(mydecrypt(Cookie::get('user'),config('encrypt_key_common'))),true);

        if(Cookie::has('user')){

            // !isset($_SERVER['HTTP_REFERER']) && die('调皮');
            // #  判断请求来源
            // $is_referer = strstr($_SERVER['HTTP_REFERER'],'www.ywd100.net/');
            // #  判断解密后数据真实性
            // if(!$is_referer || !isset($this ->_info['id'])){
            //     #  小程序兼容
            //    !isset($header['is_wx']) && die('调皮');
            // }

            $cookid = session_id();

            if(!isset($header['is_wx'])){

                // !isset($this ->_info['sessionId']) && Cookie::delete('user');

                if(isset($this ->_info['sessionId'])){
                    //获取数据库session_id
                    $res = Db::name('person')->where(['id'=>$this->_info['id']])->find();
                    if($this->_info['sessionId'] != $res['token']){
                        Cookie::delete('user');
                        $this->_info = [];
                        if(Request::instance()->isAjax()){
                            return jsonMsg('您的账号已在别处登录',3,'/');
                        }else{
                            echo "<script>alert('您的账号已在别处登录');</script>";
                        }
                    }
                    //$this ->_info['sessionId'] != $cookid && Cookie::delete('user');
                }
            }
        }

        // Cookie::has('user') && !isset($this ->_info['id']) && die('调皮');
        
        !empty($this ->_info) && $this ->assign('user',$this ->_info);

		$this ->setpv();
	}

	// 访问量统计
    public function setpv(){

        //Session_start();

       $cookid = session_id();
       #   微信判断不正确  后期完善
        $data['is_wx'] = 0;
        # 判断header 是否存在is_wx
        $info = Request::instance()->header();
        
        if(isset($info['is_wx'])){

            $data['is_wx'] = 1;
        }

        if(Request::instance()->isAjax()){

            return 1;
        }

        // if(isset($_SESSION['setpv'])){

        //     return 1;
        // }

        // 归属地  (请求接口较慢，需要时 换成函数  GetIpFrom() )
        $GetIpFrom = GetIpFrom();

        $data['froms'] = $GetIpFrom[0];

        $data['ip'] = $GetIpFrom[1];
        // 添加时间
        $data['add_time'] = time();
        // 操作系统
        $data['system'] = get_os();
        // 浏览器
        $data['browser'] = get_broswer();
        // 受访页面
        $data['pageview'] = '/'.Request::instance() ->pathinfo();
        // 来源链接
        if(!isset($_SERVER['HTTP_REFERER']) && empty($_SERVER['HTTP_REFERER'])){

            $data['source_link'] = ' ';
        }else{

            $data['source_link'] = $_SERVER['HTTP_REFERER'];
        }
        
        $res = Db::name('visitors') ->insert($data);

        // if($res){

        //     $_SESSION['setpv'] = 1;
        // }

        return 1;
        
    }
}