<?php
namespace app\manage\model;
use think\Model;
use Phinx\Db\Table;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\Image;
class TestPaper extends Model
{
	public static function getAllTestPaperList($where=null)
	{
		$where['pid'] =  0;
		$where['status'] = 1;
		$res = Db::name('test_paper')->field('id,title')->where($where)->order('id desc')->select();
		return $res;
	}
	//获取学员提交试卷列表
	public static function getAllStudentTaperList($where,$page=null,$limit=null)
	{
		$where['tp.status'] = 1;
		$res = Db::name('test_paper')->alias('tp')->field('tp.*,log.fraction,log.review,log.person_id,p.nickName,p.phone')
					->where($where)
					->join('question_log log','log.t_id = tp.id','Inner')
					->join('person p','p.id = log.person_id','left')
					->group('log.t_id , log.person_id')
					->order('log.intime desc,log.uptime desc')
					->page($page,$limit)
					->select();
		$count = Db::name('test_paper')->alias('tp')
					->where($where)
					->join('question_log log','log.t_id = tp.id','Inner')
					->join('person p','p.id = log.person_id','left')
					->group('log.t_id')
					->order('log.intime desc,log.uptime desc')
					->count();
		$data['getAllStudentTaperList'] = self::change($res);
		$data['count'] = $count;
		return $data;
	}
	public static function getTestPaperList($where,$page=null,$limit=null){

		$testPaperList = Db::name('test_paper')
							->where($where)
							->page($page,$limit)
							->order('addTime desc')
							->select();
		$count = Db::name('test_paper')
							->where($where)
							->count();

		$testPaperList = self::change($testPaperList);
		$testPaperList['count'] =  $count;

		return $testPaperList;
	}

	//数据转换
	public static function change($list){

		foreach ($list as $key => $value) {
			if($value['semester']){
				if($value['semester'] == '1'){
					$list[$key]['semester_name'] = '上学期'; 
				}elseif($value['semester'] == '2'){
					$list[$key]['semester_name'] = '下学期'; 
				}
			}
			if($value['type']){
				if($value['type'] == '1'){
					$list[$key]['type_name'] = '手工试卷'; 
				}elseif($value['type'] == '2'){
					$list[$key]['type_name'] = '随机试卷'; 
				}
			}
			if(isset($value['review'])){
				if($value['review'] == 1){
					$list[$key]['review'] = '已评阅'; 
				}elseif($value['review'] == '2'){
					$list[$key]['review'] = '未评阅'; 
				}
			}
		}

		return $list;
	} 
}
