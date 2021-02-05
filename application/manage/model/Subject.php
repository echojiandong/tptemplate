<?php
namespace app\manage\model;
use think\Model;
use think\Db;
class Subject extends Model
{
	public function getSubjectList($page,$limit)
	{
		$subject['list'] = Db::name('subject')->page($page,$limit)->select();
		$subject['count'] = Db::name('subject')->count();
		return $subject;
	}
}