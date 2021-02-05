<?php
namespace app\index\controller;
use app\index\model\PersonModel;
use think\Controller;
use think\Request;
use think\Db;
use think\Session;
use think\Cookie;
class Service extends Communal
{
	public function _initialize(){
		parent::_initialize();
		$this->person = new PersonModel($this ->_info);
        $this ->userinfo =  $this->person->GetPerson();
        $this->assign('personInfo',$this ->userinfo);
	}
	public function service()
	{
		return $this->fetch("index/service/service");
	}
	public function getServerWeiXin()
	{
		$param=input();
		$where['weixin']=$param['weixin'];
		$where['status']=1;
		$res=Db::name('serviceWeixin')->where($where)->find();
		$resq="已为您查询".$res['weixin']."是我们的客服，请放心咨询您的问题！";
		if($res){
			jsonMsg('success',1,$resq);
		}else{
			jsonMsg('暂时没有数据',0);
		}
	}
}