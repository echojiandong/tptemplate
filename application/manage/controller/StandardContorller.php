<?php
namespace app\manage\controller;
use think\contorller;
use think\Request;
use think\Session;
use think\Db;
use think\Image;
use app\manage\model\Standrd;
class StandardContorller extends author{
	public function __contruct()
	{
		parent::__contruct();
	}
	//章节列表页面
	public function chapterList(Request $request)
	{
		$param=$request->param();
		$info=new Standrd();
		$list=$info->chapterList($param);
		$this->assign('list',$list);
		return view('standard/chapterList',['title'=>'章节列表']);
	}
	//获取章节列表数据
	public function getChapterList()
	{
		
	}
}