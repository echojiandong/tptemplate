<?php
namespace app\manage\model;
use think\Model;
use Phinx\Db\Table;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\Image;
class Product extends Model
{
	public static function videoTagTree($id=0)
	{
		//获取产品标签
		$res = Db::name('product')->field('tagId')->where(['id'=>$id])->find();
		$videoTagList = explode(',',$res['tagId']);
		
		//获取视频标签
		$list = Db::name('tag')->field('id,name as title,pid as tId')->where(['is_show'=>1,'type'=>2])->select();
		
		//如果是编辑，则初始赋值
		if($id != 0){
			foreach($list as $key =>$val){
				if(in_array($val['id'], $videoTagList)){
					$list[$key]['checked'] = true;
				}
			}
		}
		return $list;
	}
}
