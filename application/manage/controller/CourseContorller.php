<?php

/*
*@马桂婵 2019/3/9
*课程管理
*/

namespace app\manage\controller;
use think\contorller;
use think\Request;
use think\Session;
use think\Db;
use think\Image;
use app\manage\model\Videoclass;
class courseContorller extends author
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
            $info = $file->move(ROOT_PATH . 'public/upload' . DS . 'live');
            if($info){
                jsonMsg("成功",0,"/public/live/".$info->getSaveName());
            }else{
                jsonMsg("失败",1,$file->getError());
            }
        }
    }
	// 课程列表
	public function courseList()
	{
		return view('course/courseList',['title'=>'课程列表']);
	}

	//获取课程列表数据
	public function getcourseList()
	{
		$info=new Videoclass();
		$res=$info->getcourseList();
		$count = $res['count'];
		unset($res['count']);
		if($res)
		{
			// $count=count($res);
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
	//添加课程页面
	public function courseAdd(Request $request)
	{
		
		//学段

		$learn=Db::name('learn')->select();
		$this->assign('getlearn' , $learn);

		// 年级
		$getgrade=Db::name('grade')->select();
		$this->assign('getgrade',$getgrade);

		// 科目
		$getsubject=Db::name('subject')->select();
		$this->assign('getsubject',$getsubject);

		// var_dump($sub);
		return view('course/courseAdd',['title'=>'添加课程']);
	}

	// 执行添加直播课程的方法
	public function addLive(Request $request)
	{
		$learn = Db::name('learn')->select();
		$this->assign('getlearn' , $learn);

		// 年级
		$getgrade = Db::name('grade')->select();
		$this->assign('getgrade',$getgrade);

		// 科目
		$getsubject = Db::name('subject')->select();
		$this->assign('getsubject',$getsubject);

		// 版本
		$text_book = Db::name('textbook')->select();
		$this->assign('textbook' , $text_book);

		// 教师
		$teacher = Db::name('teacher')->field('id,name')->select();
		$this->assign('teacher' , $teacher);
		
		return view('course/courseAdd',['title'=>'添加课程']);
	}

	// 执行添加课程的方法
	public function addcourse(Request $request)
	{
		$param=$request->param();
		$info = new Videoclass();
		$res=$info->addCourse($param);
		
		if($res)
		{
			return jsonMsg('添加成功',0);
		}
		else
		{
			return jsonMsg('添加失败',1);
		}
	}

	// 打开编辑课程页面
//	public function courseedit(Request $request)
//	{
//		$param=$request->param();
//		$where = "id=".$param['id'];
//		$res=Db::name('video_class')->where($where)->find();
//		$this->assign('data',$res);
//		return view('course/courseUpdate',['title'=>'编辑课程']);
//	}

	// 打开编辑课程页面
	public function courseedit(Request $request)
	{
		$param=$request->param();
		$where = "id=".$param['id'];
		$res=Db::name('video_class')->where($where)->find();

		//学段
		$learn = Db::name('learn')->select();
		$this->assign('getlearn' , $learn);

		// 年级
		$getgrade = Db::name('grade')->select();
		$this->assign('getgrade',$getgrade);

		// 科目
		$getsubject = Db::name('subject')->select();
		$this->assign('getsubject',$getsubject);

		// 版本
		$text_book = Db::name('textbook')->select();
		$this->assign('textbook' , $text_book);

		// 教师
		$teacher = Db::name('teacher')->field('id,name')->select();
		$this->assign('teacher' , $teacher);

		$this->assign('data',$res);
		return view('course/courseUpdate',['title'=>'编辑课程']);
	}

	// 执行课程编辑的方法
	public function editcourse(Request $request)
	{
		$param=$request->param();
		$info=new Videoclass();
		$res=$info->courseupdate($param);
		if($res)
		{
			return jsonMsg('修改成功',0);
		}
		else
		{
			return jsonMsg('修改失败',1);
		}
	}

	// 查看课程详细信息
	public function courseshow(Request $request)
	{
		$param=$request->param();
		$where = "id=".$param['id'];
		$res=Db::name('video_class')->where($where)->find();
		$this->assign('data',$res);

		return view('course/courseshow',['title'=>'课程详细信息']);
	}

	// 删除课程
	public function coursedel(Request $request)
	{
		$param=$request->param();
		$where['id']=$param['id'];
		$res=Db::name('video_class')->where($where)->delete();
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