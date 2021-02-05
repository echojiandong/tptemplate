<?php
namespace app\manage\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\image;
use think\Page;
use app\manage\model\Question;
class questionController extends author{
	//题库列表
    public function questionList(Request $request)
    {
        $param=$request->param();
        $res = Db::name('question')->where('menu_id' , $param['type'])->select();
        $this->assign('id',$param['type']);
        $this->assign('res',$res);
        return view('/question/questionList',['title'=>"题库"]);
    }
}