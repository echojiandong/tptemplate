<?php 
namespace app\manage\controller;

use think\Session;
use think\Request;
use think\Db;
use app\manage\model\QiniuModel;
use app\manage\model\QuestModel as Mq;

class Quest extends author{
	public $param;							//参数列表
	public $gradeId;						//年级id
	public $checkbox = ['A','B','C','D','E','F','G'];    //选项
	public function _initialize(){
		parent::_initialize();
		$this ->param = Request::instance() ->param();
		$this ->gradeId = isset($this ->param['gradeId'])?$this ->param['gradeId']:'';
	}
	/**
	 * [index 视图跳转]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-05-20T14:25:46+0800
	 */
	public function index(){
		//科目列表
		$subList = Db::name('subject') ->select();
		//类型列表
		$typeList = Db::name('questype') ->select();
		$this ->assign('gradeId', $this ->gradeId);
		$this ->assign('typeList',$typeList);
		$this ->assign('subList',$subList);
		return $this ->fetch();
	}
	/**
	 * [questList table渲染接口]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-05-20T16:59:31+0800
	 * @return   [json]                   符合layui的返回格式
	 */
	public function questList(){
		//拼接查询条件
		$where = array('q_parent' => $this ->param['q_parent']);
		$where1 = [];
		//年级
		if($this ->gradeId != ''){
			$where['q_gradeid'] = $this ->gradeId;
		}
		//问题状态（回收站可能会用到）
		$where['q_status'] = isset($this ->param['q_status'])?$this ->param['q_status']:1;
		//科目
		if(!empty($this ->param['subjectId'])){
			$where['q_subjectid'] = $this ->param['subjectId'];
		}
		//学期
		if(!empty($this ->param['semeId'])){
			$where['q_seme'] = $this ->param['semeId'];
		}
		//章节id
		if(!empty($this ->param['chapter'])){
			$where['chapter_id'] = $this ->param['chapter'];
		}
		//课时id
		if(!empty($this ->param['videClass'])){
			$where['video_id'] = $this ->param['videClass'];
		}
		//题干
		if(!empty($this ->param['stem'])){
			$where['q_stem'] = ['like','%'.$this ->param['stem'].'%'];
		}
		//题目类型
		if(!empty($this ->param['type'])){
			$where['q_type'] = $this ->param['type'];
		}
		//开始时间
		if(!empty($this ->param['qtime'])){
			$where['creat_time'] = ['>',strtotime($this ->param['qtime'])];
		}
		//结束时间
		if(!empty($this ->param['jtime'])){
			$where1['creat_time'] = ['<',strtotime($this ->param['jtime'])];
		}
		//录入人
		if(!empty($this ->param['teacherName'])){
			$where['teacher_name'] = ['like','%'.$this ->param['teacherName'].'%'];
		}
		//总条数
		$counts = Db::name('question') ->where($where) ->count();
		//当前页
		$page =	isset($this ->param['page'])?$this ->param['page']:1; 
		//每页显示条数
		$limit = isset($this ->param['limit'])?$this ->param['limit']:10;
		//偏移量
		$limits = ($page - 1) * $limit;

		$list = Db::name('question') ->where($where) 
									 ->where($where1)
									 ->limit($limits,$limit)
									 ->order('creat_time desc')
									 ->select();
		//题目类型
		$typeList = Db::name('questype') ->select();
		$typelist1 = array_combine(array_column($typeList,'quest_id'),array_column($typeList,'questype'));
		//科目列表
		$subList = Db::name('subject') ->select();
		$subList1 = array_combine(array_column($subList,'id'),array_column($subList,'subject'));
		foreach($list as $key => &$val){
			//创建时间
			$list[$key]['creat_time'] = date('Y-m-d H:i:s',$val['creat_time']);
			//题目类型
			$list[$key]['q_type'] = $typelist1[$val['q_type']];
			//科目
			$list[$key]['q_subjectid'] = $subList1[$val['q_subjectid']];
		}
		ajaReturn($list,0,'success',$counts);
	}
	/**
	 * [linkAge 科目、章节、课时联动]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-05-21T09:57:47+0800
	 * @return   [json] 
	 */
	public function linkAge(){
		$where['grade_id'] = $this ->gradeId;
		//科目
		$where['subject_id'] = isset($this ->param['subjectId'])?$this ->param['subjectId']:1;
		//上下册 
		if(isset($this ->param['semeId'])){
			$where['Semester'] = $this ->param['semeId'];
		}
		//章节
		if(!isset($this ->param['chapter'])){
			$wheres['part'] = 0;
		}else{
			$wheres['pid'] = $this ->param['chapter'];
		}
		$data = Mq::newM() ->MlinkAge($where, $wheres);

		ajaReturn($data,0,'success');
	}
	/**
	 * [addquest 添加、修改 题目]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-05-21T16:10:09+0800
	 */
	public function addquest(){
		//科目列表
		$subList = Db::name('subject') ->select();
		//类型列表
		$typeList = Db::name('questype') ->select();
		//教师列表
		$teachList = Db::name('teacher') ->field('id,name') ->select();
		if($this ->param['isadd'] == 0){

			$q_id = $this ->param['q_id'];
			$this ->assign('q_id',$q_id);
		}
		$this ->assign('teachList',$teachList);
		$this ->assign('gradeId', $this ->gradeId);
		$this ->assign('typeList',$typeList);
		$this ->assign('sublist', $subList);
		return $this ->fetch();
	}
	/**
	 * [setUpdVal 修改时表单初始赋值]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-05-23T11:24:14+0800
	 */
	public function setUpdVal(){
		$q_id = $this ->param['q_id'];
		$semearr = [1 => '上册', 2 => '下册', 3 => '全册'];
		$data = Db::name('question') ->where(['q_id' => $q_id]) ->find();
		//查询知识点 对应关系
		$subject = Db::name('subject') ->field('id,subject') ->where(['id' =>$data['q_subjectid']]) ->find();
		//章节
		$chapter = Db::name('video') ->field('id,testclass') ->where(['id' =>$data['chapter_id']]) ->find();
		//视频
		$video = Db::name('video') ->field('id,testclass') ->where(['id' =>$data['video_id']]) ->find();
		//知识点
		$knowledge = Db::name('knowledge') ->field('k_id,k_name') ->where(['k_id' =>$data['knowledge_id']]) ->find();
		//学期
		$semeter = $semearr[$data['q_seme']];
		//拼接知识点
		$field['knowleng'] = $subject['subject'].'->'.$semeter.'->'.$chapter['testclass'].'->'.$video['testclass'].'->'.$knowledge['k_name'];
		//知识点id
		$field['pointsId'] = $knowledge['k_id'];
		//选项
		$field['q_type'] = $data['q_type'];
		//教师
		$field['teacher'] = $data['teacher_id'].'-'.$data['teacher_name'];
		//题干
		$field['demo'] = htmlspecialchars_decode($data['q_stem']);
		//题干图片
		$field['q_stem_img'] = $data['q_stem_img'];
		//当为 单选、多选、判断的时候 有备选项
		if($data['q_type'] == 1 || $data['q_type'] == 2 || $data['q_type'] == 3 || $data['q_type'] >= 7){
			$field['demo1'] = htmlspecialchars_decode($data['q_select']);		//选项
		}
		//为填空问答时
		if($data['q_type'] == 5 || $data['q_type'] == 6){
			$field['demo4'] = htmlspecialchars_decode($data['q_answer']);		//答案
		}else{
			if($data['q_type'] != 2){
				$field['q_answer'] = htmlspecialchars_decode($data['q_answer']);
			}else{
				//为多选时
				$answer_arr = array_flip(str_split($data['q_answer']));
				$checkbox = array_flip($this ->checkbox);
				$intersect_key = array_intersect_key($checkbox, $answer_arr);
				foreach($intersect_key as $key => &$val){
					$field['q_answer['.$val.']'] = true;
				}
			}
		}
		//问题详解
		$field['demo2'] = htmlspecialchars_decode($data['q_describe']);
		//难易程度
		$field['q_level'] = $data['q_level'];
		ajaReturn($field,0,'success');
	}
	/**
	 * [addquestLinkage 添加题目时知识点的选择]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-05-22T10:09:19+0800
	 * @return   [json]                   
	 */
	public function addquestLinkage(){
		$data = Mq::newM($this ->param) ->addquestLinkage();
		ajaReturn($data,0,'success');
	}
	/**
	 * [ajaxAddQuest 添加、修改题目]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-05-23T11:14:24+0800
	 */
	public function ajaxAddQuest(){
		$q_id = $this ->param['q_id'];
		//pointsId
		$field['knowledge_id'] = $this ->param['pointsId'];
		//根据知识点向上查找
		$field['video_id'] = Db::name('knowledge') ->field('s_id') ->where(['k_id' => $this ->param['pointsId']]) ->find()['s_id'];

		$list = Db::name('video') ->field('id,kid,pid,part') ->where(['id' => $field['video_id']]) ->find();

		$video = Db::name('video_class') ->field('grade_id,subject_id,Semester') ->where(['id' => $list['kid']]) ->find();

		$chapterId = Db::name('video') ->field('id,kid,pid,part') ->where(['id' => $list['pid']]) ->find();

		if($chapterId['part'] != 0){
			$chapterId = Db::name('video') ->field('id,kid,pid,part') ->where(['id' => $chapterId['pid']]) ->find();
		}
		//pid 默认为0
		$field['q_parent'] = $this ->param['q_parent'];
		//科目
		$field['q_subjectid'] = $video['subject_id'];
		//年级
		$field['q_gradeid'] = $video['grade_id'];
		//学期
		$field['q_seme'] = $video['Semester'];
		//章节
		$field['chapter_id'] = $chapterId['id'];
		//题干
		$field['q_stem'] = $this ->param['q_stem'];

		if($q_id == 0){
			//创建时间
			$field['creat_time'] = time();
		}else{
			$field['update_time'] = time();
		}
		//问题详解
		$field['q_describe'] =  $this ->param['q_describe'];
		//难易程度
		$field['q_level'] = $this ->param['q_level'];
		//问题类型
		$field['q_type'] = $this ->param['q_type'];
		//出题人id
		$field['teacher_id'] = explode('-', $this ->param['teacher'])[0];
		//出题人姓名
		$field['teacher_name'] = explode('-', $this ->param['teacher'])[1];
		//当类型为 单选、多选、判断时
		if($this ->param['q_type'] == 1 || $this ->param['q_type'] == 2 || $this ->param['q_type'] == 3 || $this ->param['q_type'] >= 7){
			//答案
			$field['q_answer'] = $this ->param['q_type'] == 2?implode('',$this ->param['q_answer']):$this ->param['q_answer'];
			//备选项
			$field['q_select'] = $this ->param['q_select']; 

		}
		//定值填空
		if($this ->param['q_type'] == 4){
			$field['q_answer'] = $this ->param['q_answer'];
		}
		//填空、问答
		if($this ->param['q_type'] == 5 || $this ->param['q_type'] == 6){
			$field['q_answer'] = $this ->param['q_answers'];
		}
		$field['q_stem_img'] = $this ->param['q_stem_img'];
		if($q_id == 0){
			$res = Db::name('question') ->insert($field);
		}else{
			$res = Db::name('question') ->where('q_id',$q_id) ->update($field);
		}
		$code = $res?0:1001;
		ajaReturn($res,$code);
	}
	/**
	 * [PseudoDeletion 真、伪删、还原]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-05-27T09:34:50+0800
	 */
	public function PseudoDeletion(){
		//伪删
		if($this ->param['isdel'] == 0){
			$res = Db::name('question') ->where('q_id', 'in', $this ->param['ids']) ->update(['q_status' => 2]);
			$msg = '删除成功';
		}
		//真删
		if($this ->param['isdel'] == 1){
			$res = Db::name('question') ->where('q_id', 'in', $this ->param['ids']) ->delete();
			$msg = '删除成功';
		}
		//还原
		if($this ->param['isdel'] == 2){
			$res = Db::name('question') ->where('q_id', 'in', $this ->param['ids']) ->update(['q_status' => 1]);
			$msg = '还原成功';
		}
		
		$code = $res?0:1001;
		ajaReturn($msg,$code);
	}
	/**
	 * [rowsmanage 题帽题列表]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-05-27T11:35:26+0800
	 */
	public function rowsmanage(){
		//科目列表
		$subList = Db::name('subject') ->select();
		//类型列表
		$typeList = Db::name('questype') ->select();
		$this ->assign('gradeId', $this ->gradeId);
		$this ->assign('typeList',$typeList);
		$this ->assign('subList',$subList);
		return $this ->fetch();
	}
	/**
	 * [questionrows 题帽题渲染接口]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-05-27T12:00:16+0800
	 * @return   [json]                
	 */
	public function questionrows(){
		//拼接查询条件
		$where = array('qr_grade' => $this ->gradeId);				//年级
		//问题状态（回收站可能会用到）
		$where['qr_status'] = isset($this ->param['q_status'])?$this ->param['q_status']:1;
		//科目
		if(!empty($this ->param['subjectId'])){
			$where['qr_subject'] = $this ->param['subjectId'];
		}
		//学期
		if(!empty($this ->param['semeId'])){
			$where['qr_semester'] = $this ->param['semeId'];
		}
		//题目类型
		if(!empty($this ->param['type'])){
			$where['qr_type'] =  $this ->param['type'];
		}
		//章节id
		if(!empty($this ->param['chapter'])){
			$where['qr_chapter'] = $this ->param['chapter'];
		}
		//课时id
		if(!empty($this ->param['videClass'])){
			$where['qr_video'] = $this ->param['videClass'];
		}
		//题干
		if(!empty($this ->param['stem'])){
			$where['qr_question'] = ['like','%'.$this ->param['stem'].'%'];
		}
		//录入人
		if(!empty($this ->param['teacherName'])){
			$where['qr_username'] = ['like','%'.$this ->param['teacherName'].'%'];
		}
		//总条数
		$counts = Db::name('questionrows') ->where($where) ->count();
		//当前页
		$page =	isset($this ->paran['page'])?$this ->paran['page']:1; 
		//每页显示条数
		$limit = isset($this ->paran['limit'])?$this ->paran['limit']:10;
		//偏移量
		$limits = ($page - 1) * $limit;

		$list = Db::name('questionrows') ->where($where) 
									 ->limit($limits,$limit)
									 ->select();
		//题目类型
		$typeList = Db::name('questype') ->select();
		$typelist1 = array_combine(array_column($typeList,'quest_id'),array_column($typeList,'questype'));
		//科目列表
		$subList = Db::name('subject') ->select();
		$subList1 = array_combine(array_column($subList,'id'),array_column($subList,'subject'));
		foreach($list as $key => &$val){
			//题目类型
			$list[$key]['qr_type'] = $typelist1[$val['qr_type']];
			//科目
			$list[$key]['qr_subject'] = $subList1[$val['qr_subject']];
		}
		ajaReturn($list,0,'success',$counts);
	}
	/**
	 * [addquestrows 添加、修改 题帽题]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-05-27T12:03:02+0800
	 * @return   [type]                   [description]
	 */
	public function addquestrows(){
		//科目列表
		$subList = Db::name('subject') ->select();
		//类型列表
		$typeList = Db::name('questype') ->select();
		//教师列表
		$teachList = Db::name('teacher') ->field('id,name') ->select();
		if($this ->param['isadd'] == 0){

			$q_id = $this ->param['q_id'];
			$this ->assign('q_id',$q_id);
		}
		$this ->assign('teachList',$teachList);
		$this ->assign('gradeId', $this ->gradeId);
		$this ->assign('typeList',$typeList);
		$this ->assign('sublist', $subList);
		return $this ->fetch();
	}
	/**
	 * [setUpdrowsVal 题帽题初始赋值]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-05-27T13:28:46+0800
	 */
	public function setUpdrowsVal(){
		$q_id = $this ->param['q_id'];
		$semearr = [1 => '上册', 2 => '下册', 3 => '全册'];
		$data = Db::name('questionrows') ->where(['qr_id' => $q_id]) ->find();
		//查询知识点 对应关系
		$subject = Db::name('subject') ->field('id,subject') ->where(['id' =>$data['qr_subject']]) ->find();
		//章节
		$chapter = Db::name('video') ->field('id,testclass') ->where(['id' =>$data['qr_chapter']]) ->find();
		//视频
		$video = Db::name('video') ->field('id,testclass') ->where(['id' =>$data['qr_video']]) ->find();
		//知识点
		$knowledge = Db::name('knowledge') ->field('k_id,k_name') ->where(['k_id' =>$data['qr_knowledge']]) ->find();
		//学期
		$semeter = $semearr[$data['qr_semester']];
		//拼接知识点
		$field['knowleng'] = $subject['subject'].'->'.$semeter.'->'.$chapter['testclass'].'->'.$video['testclass'].'->'.$knowledge['k_name'];
		//知识点id
		$field['pointsId'] = $knowledge['k_id'];
		//选项
		$field['q_type'] = $data['qr_type'];
		//教师
		$field['teacher'] = $data['qr_userid'].'-'.$data['qr_username'];
		//题干
		$field['demo'] = htmlspecialchars_decode($data['qr_question']);
		//难易程度
		$field['q_level'] = $data['qr_level'];
		ajaReturn($field,0,'success');
	}
	/**
	 * [questRowsAdd 题帽题的添加、修改]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-05-27T15:20:25+0800
	 * @return   [json]
	 */
	public function questRowsAdd(){
		$q_id = $this ->param['q_id'];
		//pointsId
		$field['qr_knowledge'] = $this ->param['pointsId'];
		//根据知识点向上查找
		$field['qr_video'] = Db::name('knowledge') ->field('s_id') ->where(['k_id' => $this ->param['pointsId']]) ->find()['s_id'];

		$list = Db::name('video') ->field('id,kid,pid,part') ->where(['id' => $field['qr_video']]) ->find();

		$video = Db::name('video_class') ->field('grade_id,subject_id,Semester') ->where(['id' => $list['kid']]) ->find();

		$chapterId = Db::name('video') ->field('id,kid,pid,part') ->where(['id' => $list['pid']]) ->find();

		if($chapterId['part'] != 0){
			$chapterId = Db::name('video') ->field('id,kid,pid,part') ->where(['id' => $chapterId['pid']]) ->find();
		}
		//科目
		$field['qr_subject'] = $video['subject_id'];
		//年级
		$field['qr_grade'] = $video['grade_id'];
		//学期
		$field['qr_semester'] = $video['Semester'];
		//章节
		$field['qr_chapter'] = $chapterId['id'];
		//题干
		$field['qr_question'] = $this ->param['q_stem'];
		//难易程度
		$field['qr_level'] = $this ->param['q_level'];
		//问题类型
		$field['qr_type'] = $this ->param['q_type'];
		//出题人id
		$field['qr_userid'] = explode('-', $this ->param['teacher'])[0];
		//出题人姓名
		$field['qr_username'] = explode('-', $this ->param['teacher'])[1];

		if($q_id == 0){
			$res = Db::name('questionrows') ->insert($field);
		}else{
			$res = Db::name('questionrows') ->where('qr_id',$q_id) ->update($field);
		}
		$code = $res?0:1001;
		ajaReturn($res,$code);
	}
	/**
	 * [rowsonlist 子试题列表]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-05-27T15:21:11+0800
	 */
	public function rowsonlist(){
		$qr_question = Db::name('questionrows') ->field('qr_question') ->where(['qr_id' => $this ->param['qr_id']]) ->find()['qr_question'];
		//省略超出长度的字符
		if(strlen($qr_question) >150){
			$qr_question = substr($qr_question,0,150).'.....';
		}
		$this ->assign('qr_question', $qr_question);
		$this ->assign('gradeId', $this ->gradeId);
		$this ->assign('q_parent', $this ->param['qr_id']);
		return $this ->fetch();
	}
	/**
	 * [PseudoDeletionRows 删除题帽题、以及对应的子列表]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-05-27T15:44:27+0800
	 */
	public function PseudoDeletionRows(){
		$ids = $this ->param['ids'];
		Db::transaction(function() use ($ids){
			$res1 = Db::name('questionrows') ->where('qr_id', 'in', $ids) ->update(['qr_status' => 2]);
			$res2 = Db::name('question') ->where('q_parent', 'in', $ids) ->update(['q_status' => 2]);
		});
		ajaReturn('',0);
	}
	//上传课程图片 空间名：ydtvlitpic
    public function upload(){
        $file = request()->file('file');
        $info = $file->getinfo();
        if($file){
            //实例化七牛类
            $qiniu = new QiniuModel();
            //获取上传凭证
            // $qiniuSpace = 'ydtvlitpic';
            $qiniuSpace = 'litpic';
            $uploadToken=$qiniu->getQnToken($qiniuSpace);
            $filePath=$info['tmp_name'];
            $fileName=$qiniu->getNewfilename('question-',$info['name']);
            //上传图片 参数说明：uploadToken-上传凭证；filePath-本地图片路径或缓存路径；fileName-上传后文件名
            $qiniuSpaceHost = 'http://litpic.ydtkt.com/';
            $result=$qiniu->uploadFile($uploadToken,$filePath,$fileName,$qiniuSpaceHost);
            // var_dump($result);die;
            $data = array();
            $data['src'] = $result;
            $data['title'] = '试题';
            if($result){
                ajaReturn($data,0,'成功');
            }else{
                jsonMsg("失败",1,$file->getError());
            }
        }
    }
}