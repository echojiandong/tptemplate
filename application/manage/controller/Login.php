<?php
namespace app\manage\controller;
use app\manage\model\User;
use Phinx\Db\Table;
use think\Controller;
use think\Request;
use think\Session;
use think\Cookie;
use think\Db;
use think\Image;
class Login extends Controller
{
    public function login(Request $request)
    {
        if($request->isAjax()){
            $user = new User();
            $rts=$user->getuser();
            if($rts){
                //数据加密
                $rts=json_encode($rts,true);//转换成json
                $rts=base64_encode($rts);//做base64加密
                $rts=myencrypt($rts,config('encrypt_key_common'));
                Cookie::set('manageinfo',$rts);
                //Session::set("manageinfo",$userinfo);

                jsonMsg("登录成功","0");
               // return json(['error_code'=>0,'msg'=>'登录成功']);
            }else{
                jsonMsg("登录失败,请检查用户名和密码是否正确","1");
            }
        }else{
            jsonMsg("非法提交","1");
        }
    }

    public function showlogin(){
        return view("/login/login");
    }
}
