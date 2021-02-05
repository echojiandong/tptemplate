<?php
namespace app\manage\model;

use think\Model;
use think\Request;
use think\Session;
use think\Db;

class QuestModel extends Model{
	public $parmes;											//参数列表
	public static function newM($parme = []){
		$model = new QuestModel();
		$model ->parmes = $parme;
		return $model;
	}
	/**
	 * [MlinkAge table 顶部学科、学期、单元选择]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-05-21T16:24:46+0800
	 * @param    [array]                   $where  [video_class表查询条件]
	 * @param    [array]                   $wheres [video表查询条件]
	 */
	public function MlinkAge($where, $wheres){
		$videoId = Db::name('video_class') ->field('id') ->where($where) ->select();
		if(!empty($videoId)){
			foreach($videoId as &$val){
				$ids[] = $val['id'];
			}
			unset($val);
		}
		//查询对应单元下的最小课时
		if(isset($ids)){
			$video_arr = Db::name('video') ->field('id,testclass,part') 
										   ->where('kid', 'in', implode(',', $ids))
										   ->where($wheres)
										   ->select();
			if(!empty($video_arr) && $video_arr[0]['part'] == 1){
				foreach($video_arr as &$val){
					$pid[] = $val['id'];
				}
				unset($val);
				$video_arr = Db::name('video') ->field('id,testclass,part') 
											   ->where('pid', 'in', implode(',', $pid))
											   ->select();
			}
		}
		return isset($video_arr)?$video_arr:[];
	}
	/**
	 * [addquestLinkage 添加题目时科目、章节、课时、知识点的选择]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-05-21T18:01:16+0800
	 * @return   [array]
	 */
	public function addquestLinkage(){
		if(isset($this ->parmes['videoId'])){
			//查询知识点
			$data = Db::name('knowledge') ->field('k_id,k_name')
							   ->where(['s_id' => $this ->parmes['videoId']])
							   ->select();
		}
		if(isset($this ->parmes['chapterId'])){
			//查询对应章节的最小课时
			$data = Db::name('video') ->field('id,testclass,outline,part')
							   ->where(['pid' =>$this ->parmes['chapterId']])
							   ->select();
			if(!empty($data) && $data[0]['part'] == 1){
				foreach($data as &$val){
					$ids[] = $val['id'];
				}
				unset($val);
				$data = Db::name('video') ->field('id,outline,testclass,part')
								   ->where('pid', 'in', implode(',', $ids))
								   ->select();
			}
		}
		if(isset($this ->parmes['subjectId']) && isset($this ->parmes['semeterId']) && isset($this ->parmes['gradeId'])){
			//根据年级、科目、上下册筛选单元
			$where = [
					   'subject_id' => $this ->parmes['subjectId']
					  ,'grade_id'  => $this ->parmes['gradeId']
					  ,'Semester'  => $this ->parmes['semeterId']
							];
			//查询对应章节的kid
			$videoId = Db::name('video_class') ->field('id') ->where($where) ->find();

			if(empty($videoId)){
				$videoId['id'] = 0;
			}

			$data = Db::name('video') ->field('id,testclass,outline')
									  ->where(['kid' => $videoId['id'],'part' => 0])
									  ->select();
		}
		return $data;

	}
}