<?php
namespace app\manage\controller;
use think\contorller;
use think\session;
use think\Request;
use think\Db;
use app\manage\model\Textbook;
class TextbookContorller extends author
{
	private $textBookModel;

	public function __construct()
	{
		parent::__construct();
		$this->textBookModel = new TextBook();
	}
	public function TextBook()
	{

		return $this->fetch('textbook/TextBook',['title'=>'教材版本列表']);
	}
	//获取教材版本列表
	public function GetTextBookList(Request $request)
	{
		$param=$request->param();
		$page=$param['page'];
        $limit=$param['limit'];
		$res=$this->textBookModel->GetTextBookList($param,$page,$limit);
		if($res){
			$count=$res['count'];
			unset($res['count']);
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
	//添加教材版本
	public function AddTextBook()
	{
		return $this->fetch('textbook/AddTextBook',['title'=>'添加教材版本']);
	}
	//添加教材
	public function textbookAdd(Request $request)
	{
		$param=$request->param();
		$res=$this->textBookModel->textbookAdd($param);
		if($res){
			jsonMsg('添加成功','0');
		}else{
			jsonMsg('添加失败','1');
		}
	}
	//编辑教材版本
	public function EditTextBook(Request $request)
	{
		$param=$request->param();
		//获取教材版本
		$res=$this->textBookModel->gettextbook($param);
		$this->assign('res',$res);
		return $this->fetch('textbook/EditTextBook',['title'=>'编辑教材版本']);
	}
	public function update_textbook(Request $request)
	{
		$param=$request->param();
		$res=$this->textBookModel->update_textbook($param);
		if($res){
			jsonMsg('添加成功','0');
		}else{
			jsonMsg('添加失败','1');
		}
	}
	//删除教材版本
	public function DelTextBook(Request $request)
	{
		$param=$request->param();
		$res=$this->textBookModel->DelTextBook($param);
		if($res){
			jsonMsg('删除成功','0');
		}else{
			jsonMsg('删除失败','1');
		}
	}
}