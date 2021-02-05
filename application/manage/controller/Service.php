<?php
namespace app\manage\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\image;
use think\Page;
class service extends author{
	public function service()
	{
		$param=input();
		$this->assign('id',$param['type']);
		return view('service/service');
	}
	//获取官方微信客服列表
	public function getServiceList()
	{
		$param=input();
		if(isset($param['keyword'])){
			$where['s.weixin']=array('like','%'.$param['keyword'].'%');
			$where['s.nickname']=array('like','%'.$param['keyword'].'%');
		}else{
			$where='';
		}
		$res=Db::name('service_weixin')
             ->alias('s')
             ->field('s.*,u.username')
             ->join('guard_user u','u.uid=s.upid','left')
             ->whereOr($where)
		     ->order('intime desc,status desc')->page($param['page'],$param['limit'])->select();
		foreach($res as $k=>$v){
			$res[$k]['status']=($v['status']==1)?'在职':'离职';
			$res[$k]['intime']=date('Y-m-d H:i:s',$v['intime']);
			$res[$k]['uptime']=($v['uptime']==Null)?'--':date('Y-m-d H:i:s',$v['uptime']);
			$res[$k]['upid']=$v['username'];
		}
		if($res){
			$count=Db::name('service_weixin')->alias('s')->whereOr($where)->count();
			$arr=json_encode(
				array(
					'code'=>0,
					'msg'=>'',
					'count'=>$count,
					'data'=>$res
				)
			);
			echo $arr;
		}else{
			jsonMsg('暂时没有内容',1);
		}
	}
	//添加官方微信客服
	public function Addservice()
	{

		return view('service/Addservice');
	}
	//执行官方微信客服添加
	public function serviceAdd()
	{
		$info=session::get('manageinfo');
		$param=input();
		$data['weixin']=$param['weixin'];
		$data['nickname']=$param['nickname'];
		if(isset($param['editor'])){
			$data['content']=$param['editor'];
		}
		$data['intime']=time();
		$data['litpic']=$param['litpic'];
		$data['status']=1;
		$data['upid']=$info['uid'];
		$res=Db::name('service_weixin')->insert($data);
		if($res){
			jsonMsg('success',1);
		}else{
			jsonMsg('error',0);
		}
	}
	//查看官方微信客服
	public function showService()
	{
		$param=input();
		$where['id']=$param['id'];
		$res=Db::name('service_weixin')->where($where)->find();
		$this->assign('res',$res);
		return view('service/showService');
	}
	//编辑官方微信客服
	public function edtService()
	{
		$param=input();
		$where['id']=$param['id'];
		$res=Db::name('service_weixin')->where($where)->find();
		$this->assign('res',$res);
		return view('service/edtService');
	}
	//执行更新客服信息方法
	public function updateService()
	{
		$param=input();
		$where['id']=$param['id'];
		$data['weixin']=$param['weixin'];
		$data['nickname']=$param['nickname'];
		$data['content']=$param['editor'];
		$data['uptime']=time();
		$data['litpic']=$param['litpic'];
		$data['status']=$param['status'];
		$res=Db::name('service_weixin')->where($where)->update($data);
		if($res){
			jsonMsg('success',1);
		}else{
			jsonMsg('error',0);
		}
	}
	//删除官方微信客服
	public function delService()
	{
		$param=input();
		$where['id']=$param['id'];
		$res=Db::name('service_weixin')->where($where)->delete();
		if($res){
			jsonMsg('success',1);
		}else{
			jsonMsg('error',0);
		}
	}
	// 上传图片接口
    public function upload()
    {
    	$file = request()->file('file');
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'upload'. DS . 'serviceweixin');
            if($info){
                jsonMsg("成功",0,"/upload/serviceweixin/".$info->getSaveName());
            }else{
                jsonMsg("失败",1,$file->getError());
            }
        }
    }
}