<?php
namespace app\manage\model;
use think\Model;
use Phinx\Db\Table;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\Image;
class TextBook extends Model
{
	private $textbookDb;
	public function __construct()
	{
		$this->textbookDb=Db::name('textbook');
	}
	//获取教材版本列表
	public function GetTextBookList($param,$page,$limit)
	{
		if(isset($param['search'])){
			 $where['textbook']=array('like','%'.$param['search'].'%');
			 $list = $this->textbookDb->where($where)->page($page,$limit)->select();
			 $count = $this->textbookDb->where($where)->count();
		}else{
			$list = $this->textbookDb->page($page,$limit)->select();
			$count = $this->textbookDb->count();
		}
		$list['count'] = $count;
		return $list;
	}
	//添加教材版本
	public function textbookAdd($param)
	{
		$data['textbook']=$param['name'];
		return $this->textbookDb->insert($data);
	}
	//获取教材版本
	public function gettextbook($param)
	{
		$where['id']=$param['id'];
		return $this->textbookDb->where($where)->find();
	}
	//编辑教材版本
	public function update_textbook($param)
	{
		$where['id']=$param['id'];
		$data['textbook']=$param['name'];
		return $this->textbookDb->where($where)->update($data);
	}
	//删除教材版本
	public function DelTextBook($param)
	{
		$where['id']=$param['id'];
		return $this->textbookDb->where($where)->delete();
	}
}