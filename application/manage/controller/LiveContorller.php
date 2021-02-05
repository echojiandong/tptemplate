<?php
namespace app\manage\controller;
use think\contorller;
use think\Request;
use think\Session;
use think\Db;
use think\Image;
use app\manage\model\Live;
class LiveContorller extends author
{
	public function __construct()
	{
		parent::__construct();
	}
	// 上传图片接口
    public function upload()
    {
    	$file = request()->file('file');
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'live');
            if($info){
                jsonMsg("成功",0,"/public/live/".$info->getSaveName());
            }else{
                jsonMsg("失败",1,$file->getError());
            }
        }
    }
	// 直播课程列表
	public function liveList()
	{
		return view('live/liveList',['title'=>'直播课程列表']);
	}
	//获取直播课程列表数据
	public function getLiveList()
	{
		$info=new Live();
		$res=$info->getLiveList();
		if($res)
		{
			$count=count($res);
			$arr=json_encode(
				array(
					'code'=>0,
					'msg'=>'',
					'count'=>$count,
					'data'=>$res
				)
			);
			echo $arr;
		}
		else
		{
			jsonMsg('暂时没有内容',1);
		}
	}
	//添加直播课程
	public function liveadd()
	{
		$teacher=Db::name('teacher')->select();
		$this->assign('teacher',$teacher);
		return view('live/liveadd',['title'=>'添加直播课程']);
	}
	// 执行添加直播课程的方法
	public function addLive(Request $request)
	{
		$param=$request->param();
		$info=new Live();
		$res=$info->addLive($param);
		if($res)
		{
			return jsonMsg('添加成功',0);
		}
		else
		{
			return jsonMsg('添加失败',1);
		}
	}
	// 打开编辑直播课程页面并传送数据
	public function editlive(Request $request)
	{
		$param=$request->param();
		$info=new Live();
		$res=$info->editlive($param);
		$teacher=Db::name('teacher')->select();
		$this->assign('teacher',$teacher);
		$this->assign('res',$res);
		return view('live/editlive',['title'=>'编辑直播课程']);
	}
	// 执行编辑的方法
	public function liveEdit(Request $request)
	{
		$param=$request->param();
		$info=new Live();
		$res=$info->liveEdit($param);
		if($res)
		{
			return jsonMsg('修改成功',0);
		}
		else
		{
			return jsonMsg('修改失败',1);
		}
	}
	// 查看直播课程详细信息
	public function showlive(Request $request)
	{
		$param=$request->param();
		$info=new  Live();
		$res=$info->showlive($param);
		$this->assign('res',$res);
		return view('live/showlive',['title'=>'直播课程详细信息']);
	}
	// 删除直播课程
	public function delLive(Request $request)
	{
		$param=$request->param();
		$where['id']=$param['id'];
		$res=Db::name('live')->where($where)->delete();
		if($res)
		{
			return jsonMsg('删除成功',0);
		}
		else
		{
			return jsonMsg('删除失败',1);
		}
	}
}