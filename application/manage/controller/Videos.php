<?php 
namespace app\manage\controller;

use think\Controller;
use think\Request;
use think\Db;
use think\image;
use think\Session;
use app\manage\model\Videos as Mv;
use app\manage\model\QiniuModel;

class Videos extends author{

	public $param;
	//默认学科语文
	public $subject = 1;
	//默认阶段初中
	public $grade = 1;
	//区分小学、初中、高中
	public $a_grade = [[1,2,3,4,5,6],[7,8,9],[10,11,12]];

	public function _initialize(){

		parent::_initialize();
		//初始化参数赋值
		$this ->param = Request::instance() ->param(true);
		//移除第一个键 (这是个坑  nginx 可注释  apache一定要放开注释！一定！)
		array_shift($this ->param);
		// 学段  0 ：小学 1：初中 2：高中 
		$this ->grade = isset($this ->param['g_type'])?$this ->param['g_type']:$this ->grade;
		//学科
		$this ->subject = isset($this ->param['s_type'])?$this ->param['s_type']:$this ->subject;
	}

	/**
	 * 文件上传获取七牛token
	 * 
	 */
	public function getToken()
	{
		//实例化七牛类
		$qiniu = new QiniuModel();
		//获取上传凭证
		$qiniuSpace = 'ydtvideo1080';
		$upToken=$qiniu->getQnToken($qiniuSpace);

		$data = [
			'domain' => 'http://www.ydtkt.com/',
			'uptoken' => $upToken
		];
		echo json_encode($data);
	}

	/**
	 * [videoList 视频库列表]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-21T18:03:42+0800
	 */
	public function videoList(){
		//学科
		$this ->assign('subject', $this ->subject);
		//学段
		$this ->assign('grade', $this ->grade);

		return $this ->fetch();
	}
	/**
	 * [videotree 组装tree所需数组]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-21T18:04:34+0800
	 */
	public function videotree(){
		//组装数据
		$list = Mv::newV() ->videotree($this ->grade, $this ->subject);
		echo json_encode($list);

	}
	/**
	 * [addcurriculum 添加课程页面跳转]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-21T18:05:36+0800
	 */
	public function addcurriculum(){
		// 根据id 判断 编辑修改
		$id = isset($this ->param['id']) ? $this ->param['id'] : 0;
		// 编辑时 对学段、科目重新赋值
		if(!empty($id)){
			$class_l = Db::name('video_class') ->field('id,grade_id,subject_id') 
											   ->where(['id' => $id]) 
											   ->find();
			// 判断 学段所在区间
			foreach($this ->a_grade as $key => &$val){

				if(in_array($class_l['grade_id'],$val)){

					$this ->grade = $key;
				}
			}

			$this ->subject = $class_l['subject_id'];
		}
		// 年级列表
		$grade_list = array_map(function($v){
					return ['id' => $v, 'name' => $v.'年级'];
				}, $this ->a_grade[$this ->grade]);
		//教材版本
		$edition = Db::name('textbook') ->field(true) ->select();
		//教师
		$t_list = Db::name('teacher') ->field('id,name') ->select();
		//编辑id
		$selfid = isset($this ->param['id']) ? $this ->param['id'] : 0;

		$this ->assign('t_list', $t_list);

		$this ->assign('edition', $edition);

		$this ->assign('grade_list', $grade_list);

		$this ->assign('subject', $this ->subject);

		$this ->assign('selfid', $selfid);

		return $this ->fetch();

	}
	/**
	 * [setcurriculumval 添加课程form初始赋值]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-21T18:31:25+0800
	 */
	public function setcurriculumval(){
		// 容错
		$id = isset($this ->param['id']) ? $this ->param['id'] : 0;

		if(empty($id)){
			ajaReturn('', 1001, 'error');
		}
		// 查询字段
		$field = "*";

		$list = Db::name('video_class') ->field($field) ->where(['id' => $id]) ->find();
		// 教师姓名
		$list['sname'] = $list['teacherId'].'-'.$list['sname'];
		// 解析html
		$list['content'] = htmlspecialchars_decode($list['content']);

		$this ->returns($list);

	}
	/**
	 * [curriculumedit 课程添加、编辑]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-21T18:35:53+0800
	 */
	public function curriculumedit(){
		//数据处理
		$res = Mv::newV($this ->param) ->curriculumedit();

		$this ->returns($res);
	}
	/**
	 * [addchapter 章节添加页面跳转]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-21T18:36:37+0800
	 */
	public function addchapter(){
		// 章节 kid
		$id = isset($this ->param['cid']) && !empty($this ->param['cid']) ? $this ->param['cid'] : 0;
		// 自身id
		$selfid = isset($this ->param['sid']) && !empty($this ->param['sid']) ? $this ->param['sid'] : 0;

		$this ->assign('id', $id);

		$this ->assign('sid', $selfid);

		return $this ->fetch();

	}
	/**
	 * [chapterFormval 章节编辑 表单初始赋值]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-21T18:38:21+0800
	 * @return   [type]                   [description]
	 */
	public function chapterFormval(){
		// 容错
		$id = isset($this ->param['id']) && !empty($this ->param['id']) ? $this ->param['id'] : 0;

		if($id === 0){

			ajaReturn('',1001,'error');
		}
		$chaptreval = Db::name('video') ->field('testclass,outline,courseware,courseware1,courseware2') 
									    ->where(['id' => $id]) 
									    ->find();
		$this ->returns($chaptreval);
	}
	/**
	 * [chapteredit 章节添加、编辑 接口]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-21T18:56:06+0800
	 */
	public function chapteredit(){
		if(isset($this ->param['kid']) && $this ->param['kid'] != 0){
			//章
			$this ->param['part'] = 0;
			//父id
			$this ->param['pid'] = 0;
			//添加时间
			$this ->param['time'] = time();

			$this ->param['videoTag'] = isset($this->param['ids']) ? trim($this->param['ids'],',') : 0;
			unset($this ->param['file']);
			unset($this ->param['ids']);

			$res = Db::name('video') ->insertGetId($this ->param);

			$res = Db::name('video') ->where(['id' => $res]) ->update(['sort' => $res]);
		}

		if(isset($this ->param['id']) && $this ->param['id'] != 0){
			//修改时间
			$this ->param['time'] = time();

			$this ->param['videoTag'] = isset($this->param['ids']) ? trim($this->param['ids'],',') : 0;
			unset($this ->param['ids']);

			unset($this ->param['file']);

			$res = Db::name('video') ->update($this ->param);
		}

		$this ->returns($res);

	}
	/**
	 * [addclasshour 添加课时或课时块]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-19T19:13:19+0800
	 */
	public function addclasshour(){
		// 父id （添加时）
		$pid = isset($this ->param['cid']) ? $this ->param['cid'] : 0;
		// 自身id （编辑时）
		$selfid = isset($this ->param['sid']) ? $this ->param['sid'] : 0;
		// 课时 或 课时块
		$part = isset($this ->param['part']) ? $this ->param['part'] : 1;
		// 教师列表
		$t_list = Db::name('teacher') ->field('id,name') ->select();

		$this ->assign('pid', $pid);

		$this ->assign('selfid', $selfid);

		$this ->assign('part', $part);

		$this ->assign('t_list', $t_list);

		return $this ->fetch();
	}
	/**
	 * [setformval 课时 或 课时块表单初始赋值]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-21T19:08:04+0800
	 */
	public function setformval(){
		// 课时 id
		$id = isset($this ->param['id']) ? $this ->param['id'] : '';

		$part = isset($this ->param['part']) ? $this ->param['part'] : 1 ;
		// 容错
		if($id == '' || $part == ''){

			ajaReturn('', 1001, 'error');
		}
		//表名
		$table = 'video';
		// 字段名拼接
		$field = 'testclass,outline,courseware,courseware1,courseware2';

		if($part == 2){

			$field .= ',link as link_1080,teacherid,audi_time,teachername,link_720,link_480,skill,audi,img';

		}

		$formval = Db::name($table) ->field($field) ->where(['id' => $id]) ->find();
		//教师值拼接
		if(!empty($formval) && $part == 2){

			$formval['teachername'] = $formval['teacherid'].'-'.$formval['teachername'];
			unset($formval['teacherid']);
		}

		$this ->returns($formval);
	}
	/**
	 * [addclassinterferce 添加、修改课时接口]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-21T19:10:55+0800
	 */
	public function addclassinterferce(){

		$selfid = isset($this ->param['selfid']) ? $this ->param['selfid'] : 0;

		$pid = isset($this ->param['pid']) ? $this ->param['pid'] : 0;

		$part = isset($this ->param['part']) ? $this ->param['part'] : 1;

		$this ->param['videoTag'] = isset($this->param['ids']) ? trim($this->param['ids'],',') : 0;
		unset($this ->param['ids']);

		//添加课时块
		if($selfid == 0 && $part == 1){

			$res = Mv::newV($this ->param) ->addclassinterferce1();
		}
		if($part == 2){
			//添加、修改课时
			$res = Mv::newV($this ->param) ->addclassinterferce2();
		}
		$this ->returns($res);
		
	}
	/**
	 * [addknowlenge 知识点页面跳转]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-21T19:12:08+0800
	 * @return   [type]                   [description]
	 */
	public function addknowlenge(){
		// 课时id
		$sid = isset($this ->param['sid']) ? $this ->param['sid'] : 0;
		// 自身id
		$selfid = isset($this ->param['cid']) ? $this ->param['cid'] : 0;

		$this ->assign('sid', $sid);

		$this ->assign('selfid', $selfid);

		return $this ->fetch();
	}
	/**
	 * [setknowlengeval 知识点 编辑 表单初始赋值]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-21T19:13:26+0800
	 */
	public function setknowlengeval(){
		//容错
		$id = isset($this ->param['id']) ? $this ->param['id'] : '';

		if($id == ''){
			ajaReturn('', 1001, 'error');
		}
		$list = Db::name('knowledge') ->field('k_name,k_content,start_time,end_time') 
									  ->where(['k_id' => $id]) 
									  ->find();
		if(empty($list)){
			ajaReturn('', 1001, 'error');
		}
		//开始时间
		$list['s_hour'] = $list['s_minute'] = $list['s_second'] = '00';
		//结束时间
		$list['e_hour'] = $list['e_minute'] = $list['e_second'] = '00';
		//拼接

		if(!empty($list['start_time'])){
			$start_time = strstr($list['start_time'], ':');
			if ($start_time) {
				$arr = $this->checkoutDate($list['start_time']);
				$list['s_hour'] = $arr['hour'];
				//拼接分钟数
				$list['s_minute'] = $arr['minute'];
				//拼接秒数
				$list['s_second'] = $arr['second'];
			} else {
				//拼接时长
				$list['s_hour'] = str_pad(intval($list['start_time']/3600),2,"0",STR_PAD_LEFT);
				//拼接分钟数
				$list['s_minute'] = str_pad(intval($list['start_time']%3600/60),2,"0",STR_PAD_LEFT);
				//拼接秒数
				$list['s_second'] = str_pad(intval($list['start_time']%60%60),2,"0",STR_PAD_LEFT);
			}
	     }

	     if(!empty($list['end_time'])){

			$end_time = strstr($list['end_time'], ':');
			if ($end_time) {
				$arr = $this->checkoutDate($list['end_time']);
				$list['e_hour'] = $arr['hour'];
				//拼接分钟数
				$list['e_minute'] = $arr['minute'];
				//拼接秒数
				$list['e_second'] = $arr['second'];
			} else {
				//时
				$list['e_hour'] = str_pad(intval($list['end_time']/3600),2,"0",STR_PAD_LEFT);
				//分钟数
				$list['e_minute'] = str_pad(intval($list['end_time']%3600/60),2,"0",STR_PAD_LEFT);
				//秒数
				$list['e_second'] = str_pad(intval($list['end_time']%60%60),2,"0",STR_PAD_LEFT);
			}
			
		 }
		 
	     ajaReturn($list, 0, 'success');
	}

	public function checkoutDate($strTime)
	{
		$arr = explode(':', $strTime);
		$count = count($arr);
		if ($count == 2) {
			$data = [
				'hour' => 0,
				'minute' => $arr[0],
				'second' => $arr[1]
			];
		} elseif ($count == 3) {
			$data = [
				'hour' => $arr[0],
				'minute' => $arr[1],
				'second' => $arr[2]
			];
		} else {
			$data = [
				'hour' => 0,
				'minute' => 0,
				'second' => 0
			];
		}

		return $data;
	}
	/**
	 * [knowlengeedit 知识点添加、编辑接口]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-21T19:15:15+0800
	 */
	public function knowlengeedit()
	{
		// 添加时  sid
		$pid = isset($this ->param['pid']) ? $this ->param['pid']: 0;
		// if (!$pid) {
		// 	jsonMsg('章节不存在', 1);
		// }
		// 自身id
		$sid = isset($this ->param['sid']) ? $this ->param['sid']: 0;
		// 知识点标题
		$data['k_name'] = isset($this ->param['k_name'])? $this ->param['k_name'] : '';
		// 知识点 内容
		$data['k_content'] = isset($this ->param['k_content'])? $this ->param['k_content'] : '';
		//开始时间
		$s_hour = (int) isset($this ->param['s_hour'])? $this ->param['s_hour'] : 0;

		$s_minute = (int) isset($this ->param['s_minute'])? $this ->param['s_minute'] : 0;

		$s_second = (int) isset($this ->param['s_second'])? $this ->param['s_second'] : 0;

		$start_time = $this->getDateStr($s_hour, $s_minute, $s_second);
		//结束时间
		$e_hour =  (int) isset($this ->param['e_hour'])? $this ->param['e_hour'] : 0;

		$e_minute = (int) isset($this ->param['e_minute'])? $this ->param['e_minute'] : 0;

		$e_second = (int) isset($this ->param['e_second'])? $this ->param['e_second'] : 0;

		$end_time = $this->getDateStr($e_hour, $e_minute, $e_second);

		// $data['start_time'] = (int)$s_hour * 3600 + (int)$s_minute * 60 + (int)$s_second;

		// $data['end_time'] = (int)$e_hour * 3600 + (int)$e_minute * 60 + (int)$e_second;
		// if ($end_time < $start_time) {
		// 	jsonMsg('知识点播放结束时间不能小于开始时间', 1);
		// }

		// 获取章节的视频时长,待完善
		// $videoTimeLength = Db::name('video')->where(['id' => $pid, 'part' => 2])->find();
		// if (!$videoTimeLength) {

		// }

		$data['start_time'] = $start_time;
		$data['end_time'] = $end_time;
		$data['update_time'] = time();

		$res = '';
		//添加
		if(!empty($pid)){

			$data['s_id'] = $pid;

			$data['created_time'] = time();

			$res = Db::name('knowledge') ->insertGetId($data);

			$res = Db::name('knowledge') ->where(['k_id' => $res]) ->update(['sort' => $res]);
		}
		// 修改
		if(!empty($sid)){

			$res = Db::name('knowledge') ->where(['k_id' => $sid]) ->update($data);
		}

		$this ->returns($res);

	}
	/**
	 * [updswitch 试听状态修改接口]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-21T19:19:56+0800
	 */
	public function updswitch(){

		$id = isset($this ->param['id']) ? $this ->param['id'] : '';
		// 表名
		$table =  isset($this ->param['table']) ? $this ->param['table'] : '';

		$v = isset($this ->param['v']) ? $this ->param['v'] : '';
		
		if(empty($id) || empty($table) || empty($v)){
			ajaReturn('', 1001, 'error');
		}

		$res = Db::name($table) ->where(['id' => $id]) ->update(['audi' => $v]);

		$this ->returns($res);
	}
	/**
	 * [recursiondel 页面删除操作]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-21T19:21:20+0800
	 * @return   [type]                   [description]
	 */
	public function recursiondel(){
		// 表名
		$table = isset($this ->param['t_name']) ? $this ->param['t_name'] : '' ;
		//要删除id
		$id = isset($this ->param['del_id']) ? $this ->param['del_id'] : '' ;
		// 容错
		if(empty($table) || empty($id)){

			ajaReturn('', 1001, 'error');
		}
		// 根据表名进行删除操作
		switch ($table){
			case 'video_class': 
				$res = Mv::newV() ->deleteVCdata($id);
			break;
			case 'video': 
				$id = [$id];
				$res = Mv::newV() ->deleteVdata($id);
			break;
			case 'knowledge': 
				$res = Mv::newV() ->deleteKLdata($id);
			break;
			default: 
				ajaReturn('', 1001, 'error');
			break;
		}

		$this ->returns($res);
	}

	public function sorting(){
		$table = isset($this ->param['t_name']) ? $this ->param['t_name'] : '';

		$id = isset($this ->param['s_id']) ? $this ->param['s_id'] : '';

		$status = isset($this ->param['status']) ? $this ->param['status'] : 1;

		// 根据表名进行删除操作
		switch ($table){
			case 'video_class': 
				$res = Mv::newV() ->sortingVC($id, $status);
			break;
			case 'video': 
				$res = Mv::newV() ->sortingV($id, $status);
			break;
			case 'knowledge': 
				$res = Mv::newV() ->sortingKL($id, $status);
			break;
			default: 
				ajaReturn('', 1001, 'error');
			break;
		}

		$this ->returns($res);
	}
	//视频上传
    public function uploadvideo(Request $request)
    {
        set_time_limit(0);

        $file = $request->file('file');

        $q_video = isset($this ->param['q_video']) ? $this ->param['q_video'] : '480';

        $info = $file ->getinfo();

        $result = '';

        if($file){
            //实例化七牛类
            $qiniu = new QiniuModel();
            // //获取上传凭证
            $qiniuSpace = 'ydtvideo'.$q_video;

            $uploadToken=$qiniu->getQnToken($qiniuSpace);

            $filePath=$info['tmp_name'];
            //视频上传不改变 原名字
            $fileName = $info['name'];
            // 上传图片 参数说明：uploadToken-上传凭证；filePath-本地图片路径或缓存路径；fileName-上传后文件名
            $qiniuSpaceHost = 'http://ydtvideo'.$q_video.'.ydtkt.com/';

            $result=$qiniu->uploadFile($uploadToken,$filePath,$fileName,$qiniuSpaceHost);
        }

        $this ->returns($result);
    }
    //图片上传
    public function uploadimg(){
        $file = request()->file('file');

        $info = $file->getinfo();

        $result = '';

        if($file){
            //实例化七牛类
            $qiniu = new QiniuModel();
            //获取上传凭证
            $qiniuSpace = 'ydtvlitpic';

            $uploadToken=$qiniu->getQnToken($qiniuSpace);

            $filePath=$info['tmp_name'];

            $fileName=$qiniu->getNewfilename('index',$info['name']);
            //上传图片 参数说明：uploadToken-上传凭证；filePath-本地图片路径或缓存路径；fileName-上传后文件名
            $qiniuSpaceHost = 'http://ydtvlitpic.ydtkt.com/';

            $result=$qiniu->uploadFile($uploadToken,$filePath,$fileName,$qiniuSpaceHost);
        }
        $this ->returns($result);
    }
    // 返回时判断
	public function returns($chapterval){

		$msg['data'] = $chapterval ? $chapterval : $chapterval;

		$msg['code'] = $chapterval ? 0 : 1001;

		$msg['msg'] = $chapterval ? 'success' : 'error';

		ajaReturn($msg['data'], $msg['code'], $msg['msg']);
	}

	/**
	 * 组装时间数据格式为00:00:00
	 * @param $hour int 时
	 * @param $minute int 分钟
	 * @param  $second int 秒
	 */
	public function getDateStr($hour = 0, $minute = 0, $second = 0) 
	{
		$str = '00:00:00';
		if ($hour < 0 && $minute < 0 && $second < 0) {
			return $str;
		}

		$totalSecond = ($hour * 3600) + ($minute * 60) + $second;

		$hour = floor($totalSecond/3600);
		$minute = floor(($totalSecond-3600*$hour)/60);
		$second = floor((($totalSecond-3600*$hour)-60*$minute)%60);
		if ($hour >= 0 && $hour < 10) {
			$hour = '0'. $hour;
		}
		if ($minute >= 0 && $minute < 10) {
			$minute = '0'. $minute;
		}

		if ($second >= 0 && $second < 10) {
			$second = '0'. $second;
		}
		
		return $hour.':'.$minute.':'.$second;
	}

	//视频标签树
	public function videoTagTree(){

		$param = input();
		//获取列表树格式
		$list = Mv::newV() ->videoTagTree($param['id'],$param['type']);
		//递归处理树结构
		$data = $this ->tree($list);
		ajaReturn($data,0);
	}

	private  function tree($data,$pid=0,$level=1){
	    $tree = [];
	    foreach($data as $k => $v)
	    {
	        $v['level']=$level;
	        if($v['tId'] == $pid)
	        {        
	            $v['children'] = $this ->tree($data, $v['id']);
	            if(empty($v['children'])){
	            	unset($v['children']);
	            }else{
	            	unset($v['checked']);
	            }
	            $tree[] = $v;
	        }
	    }
    	return $tree;
	}
}