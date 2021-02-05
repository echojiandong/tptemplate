<?php
namespace app\manage\model;
use think\Model;
use Phinx\Db\Table;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\Image;
class Standrd extends Model
{
	public function chapterList()
	{
		$param=input();		
		$res=Db::name('standard')->select();
		return $res;
	}
}