<?php
namespace app\manage\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\Image;
use think\File;
use app\manage\model\Teacher;
use app\manage\model\QiniuModel;
use Redis;
class TeacherContorller extends author
{
	
    //上传教师图片 空间名：ydttlitpic 域名: ydttlitpic.ydtkt.com
    public function upload(){
    	set_time_limit(0);
        $file = request()->file('file');
        $info = $file->getinfo();
        if($file){
            //实例化七牛类
            $qiniu = new QiniuModel();
            //获取上传凭证
            $qiniuSpace = 'ydttlitpic';
            $uploadToken=$qiniu->getQnToken($qiniuSpace);
            $filePath=$info['tmp_name'];
            $fileName=$qiniu->getNewfilename('index',$info['name']);
            //上传图片 参数说明：uploadToken-上传凭证；filePath-本地图片路径或缓存路径；fileName-上传后文件名
            $qiniuSpaceHost = 'http://ydttlitpic.ydtkt.com/';
            $result=$qiniu->uploadFile($uploadToken,$filePath,$fileName,$qiniuSpaceHost);
            if($result){
                jsonMsg("成功",0,$result);
            }else{
                jsonMsg("失败",1,$file->getError());
            }
        }
    }
    //上传视频 空间名：ydtteachervideo,域名：http://ydtteachervideo.ydtkt.com/
    public function uploadVideo(Request $request)
    {
        set_time_limit(0);
        $file = $request->file('file');
        $info = $file ->getinfo();
        if($file){
            //实例化七牛类
            $qiniu = new QiniuModel();
            // //获取上传凭证
            $qiniuSpace = 'ydtteachervideo';
            $uploadToken=$qiniu->getQnToken($qiniuSpace);
            $filePath=$info['tmp_name'];
            // $fileName=$qiniu->getNewfilename('index',$info['name']);
            //视频上传不改变 原名字
            $fileName = $info['name'];
            // 上传图片 参数说明：uploadToken-上传凭证；filePath-本地图片路径或缓存路径；fileName-上传后文件名
            $qiniuSpaceHost = 'http://ydtteachervideo.ydtkt.com/';
            $result=$qiniu->uploadFile($uploadToken,$filePath,$fileName,$qiniuSpaceHost);
            if($result){
                jsonMsg("成功",0,$result);
            }else{
                jsonMsg("失败",1,$file->getError());
            }
        }
    }
	// 显示教师列表页面
	public function teacherList()
	{
		return view('teacher/teacherList',['title'=>'教师列表']);
	}
	// 教师列表页面获取数据接口
	public function getTeacherList(Request $request)
	{
		$param=$request->param();
		$info=new Teacher();
		if(isset($param['search'])){
			 $where['name']=array('like','%'.$param['search'].'%');
			 if($param['search']=='小学'){
			 	$where['guard_grade.grade']=array('like','%1%');
			 }elseif($param['search']=='初中'){
			 	$where['guard_grade.grade']=array('like','%2%');
			 }elseif($param['search']=='高中'){
			 	$where['guard_grade.grade']=array('like','%3%');
			 }else{
			 	$where['guard_grade.grade']=array('like','%'.$param['search'].'%');
			 }
			 $where['guard_textbook.textbook']=array('like','%'.$param['search'].'%');
			$teacherList=$info->checkTeacherList($where);
		}else{
			$teacherList=$info->getTeacherList();
		}
		if($teacherList)
		{
			$count=$teacherList['count'];
			unset($teacherList['count']);
			$arr=json_encode(
				array(
					'code'=>0,
					'msg'=>'',
					'count'=>$count,
					'data'=>$teacherList
				)
			);
			echo $arr;
		}
		else
		{
			jsonMsg('暂时没有内容',1);
		}
	}
	// 教师信息预览详情
	public function showTeacher(Request $request)
	{
		$param=$request->param();
		$info=new Teacher();
		$teacherShow=$info->teacherShow($param);
		$this->assign('res',$teacherShow);
		return view('teacher/teacherShow',['title'=>'教师信息预览']);
	}
	// 编辑教师信息展示教师原有信息页面
	public function editTeacher(Request $request)
	{
		$param=$request->param();
		$info=new Teacher();
		$teacherShow=$info->teacherShow($param);
		// var_dump($teacherShow);die;
		$this->assign('res',$teacherShow);
		$info=new teacher();
		//获取学段
		$getlearn=$info->getlearn();
		$this->assign('getlearn',$getlearn);
		//获取年级
		$getgrade=$info->getgrade();
		$this->assign('getgrade',$getgrade);

		// 获取校区
		$school = Db::name('school') ->field('id,s_name') ->select();
		$this ->assign('schoollist', $school);
		
        //获取学科
        $getsubject=$info->getsubject();
        $this->assign('getsubject',$getsubject);
		//获取教材版本
		$res=Db::name('textbook')->select();
		$this->assign('textBook_res',$res);
		return view('teacher/editTeacher',['title'=>'编辑教师信息']);
	}
	// 更新教师信息
	public function update_teacher(Request $request)
	{
		$param=$request->param();
		$teacherId = intval($param['id']);
		//删除教师同时删除教师的图片
		$qiniu = new QiniuModel();
		$imgPath = Db::name('teacher')->where('id='.$teacherId)->select();
        if(!empty($imgPath[0]['litpic']) && $imgPath[0]['litpic'] != $param['litpic']){
            $collectImgPath = str_replace("http://ydttlitpic.ydtkt.com/","",$imgPath[0]['litpic']);
            $qiniuSpace = 'ydttlitpic';
            $qiniu->delFile($collectImgPath,$qiniuSpace);
        }
        if(!empty($imgPath[0]['coverlitpic']) && $imgPath[0]['coverlitpic'] != $param['coverlitpic']){
            $coverlitpicImgPath = str_replace("http://ydttlitpic.ydtkt.com/","",$imgPath[0]['coverlitpic']);
            $qiniuSpace = 'ydttlitpic';
            $qiniu->delFile($coverlitpicImgPath,$qiniuSpace);
        }
        if(!empty($imgPath[0]['teacher_booth']) && $imgPath[0]['teacher_booth'] != $param['teacher_booth']){
            $coverlitpicImgPath = str_replace("http://ydttlitpic.ydtkt.com/","",$imgPath[0]['teacher_booth']);
            $qiniuSpace = 'ydttlitpic';
            $qiniu->delFile($coverlitpicImgPath,$qiniuSpace);
        }
        if(!empty($imgPath[0]['teacher_booth_hover']) && $imgPath[0]['teacher_booth_hover'] != $param['teacher_booth_hover']){
            $coverlitpicImgPath = str_replace("http://ydttlitpic.ydtkt.com/","",$imgPath[0]['teacher_booth_hover']);
            $qiniuSpace = 'ydttlitpic';
            $qiniu->delFile($coverlitpicImgPath,$qiniuSpace);
        }
        if(!empty($imgPath[0]['Audition_video']) && $imgPath[0]['Audition_video'] != $param['Audition_video']){
            $remarkVideoPath = str_replace("http://ydtteachervideo.ydtkt.com/","",$imgPath[0]['Audition_video']);
            $qiniuSpace = 'ydtteachervideo';
            $qiniu->delFile($remarkVideoPath,$qiniuSpace);
        }
		$info=new teacher();
		$res=$info->updateTeacher($param);
		if($res){
		 	return jsonMsg("修改成功", 0);
        } else {
            return jsonMsg("修改失败", 1);
        }
	}
	// 删除教师
	public function delTeacher(Request $request)
	{
		$param=$request->param();
		$where['id']=$param['id'];
		//删除教师同时删除教师的图片
		$qiniu = new QiniuModel();
		$imgPath = Db::name('teacher')->where('id='.$param['id'])->select();
        if(!empty($imgPath[0]['litpic'])){
            $collectImgPath = str_replace("http://ydttlitpic.ydtkt.com/","",$imgPath[0]['litpic']);
            $qiniuSpace = 'ydttlitpic';
            $qiniu->delFile($collectImgPath,$qiniuSpace);
        }
        if(!empty($imgPath[0]['coverlitpic'])){
            $coverlitpicImgPath = str_replace("http://ydttlitpic.ydtkt.com/","",$imgPath[0]['coverlitpic']);
            $qiniuSpace = 'ydttlitpic';
            $qiniu->delFile($coverlitpicImgPath,$qiniuSpace);
        }
        if(!empty($imgPath[0]['Audition_video'])){
            $remarkVideoPath = str_replace("http://ydtteachervideo.ydtkt.com/","",$imgPath[0]['Audition_video']);
            $qiniuSpace = 'ydtteachervideo';
            $qiniu->delFile($remarkVideoPath,$qiniuSpace);
        }
		$res=Db::name('teacher')->where($where)->delete();
		if($res){
		 	return jsonMsg("删除成功", 0);
        } else {
            return jsonMsg("删除失败", 1);
        }
	}
	//添加教师
	public function teacherAdd()
	{
		$info=new teacher();
		//获取学段
		$getlearn=$info->getlearn();
		$this->assign('getlearn',$getlearn);
		//获取年级
		$getgrade=$info->getgrade();
		$this->assign('getgrade',$getgrade);
		//获取学科
		$getsubject=$info->getsubject();
		$this->assign('getsubject',$getsubject);

		// 获取校区
		$school = Db::name('school') ->field('id,s_name') ->select();
		$this ->assign('schoollist', $school);
		//获取教材版本
		$res=Db::name('textbook')->select();
		$this->assign('textBook_res',$res);
		return $this->fetch('/teacher/teacherAdd',['title'=>'添加教师']);
	}
	//执行添加教师信息的方法
	public function addTeacher(Request $request)
	{
		$param=$request->param();
		$info=new teacher();
		$res=$info->addTeacher($param);
		if($res){
		 	return jsonMsg("添加成功", 0);
        } else {
            return jsonMsg("添加失败", 1);
        }
	}
	// ++--------------------------------------------------------
	// +	院校管理
	// ++--------------------------------------------------------
	public function schoolmanage(){

		return $this ->fetch('/teacher/schoolmanage');
	}
	/*
	 * table 初始赋值
	 */
	public function schooltable(){
		$param = input();

		$page = isset($param['page']) ? $param['page'] : 1;

		$limit = isset($param['limit']) ? $param['limit'] : 10;

		$data = Db::name('school') ->field('id,s_name as name,time') ->page($page, $limit) ->select();

		$count = (Db::name('school') ->field('count(1) as counts') ->find())['counts'];

		if(!empty($data)){

			foreach($data as $key =>&$val){

				$data[$key]['time'] = date('y-m-d H:i:s', $val['time']);
			}
		}

		ajaReturn($data, 0, 'success',$count);

	}
	/*
	 * 表单初始赋值
	 */
	public function setschoolformval(){
		$param = input();

		$id = isset($param['id']) ? $param['id'] : '';

		if($id == ''){

			ajaReturn('error', 1001, []);
		}

		$data = Db::name('school') ->field('s_name as name') ->where(['id' => $id]) ->find();

		ajaReturn($data, 0, 'success');
	}
	/*
	 * 页面跳转
	 */
	public function addschoolpage(){

		$param = input();

		$id = isset($param['id']) ? $param['id'] : 0;

		$this ->assign('id', $id);

		return $this ->fetch('/teacher/addschoolpage');
	}
	/*
	 * 院校添加
	 */
	public function addschool(){
		$param = input();

		$id = isset($param['id']) ? $param['id'] : '';

		$data['s_name'] = isset($param['name']) ? $param['name'] : '';

		$list = Db::name('school') ->where(['s_name' => $data['s_name']]) 
								   ->where('id', '<>', $id) 
								   ->select();

		if(!empty($list)){

			ajaReturn('', 1001, '院校名称已存在');
		}

		if($id == 0){

			$data['time'] = time();

			$res = Db::name('school') ->insert($data);
		}

		if($id != 0){

			$res = Db::name('school') ->where(['id' => $id]) ->update($data);
		}

		$msg = $res ? ['msg' => 'success', 'code' => 0] : ['msg' => 'error', 'code' => 1001] ;

		ajaReturn('', $msg['code'], $msg['msg']);

	}
	/*
	 * 删除院校
	 */
	public function delschool(){

		$param = input();
		
		$id = isset($param['id']) ? $param['id'] : '';

		if($id == ''){
			ajaReturn('error', 1001, []);
		}

		$res = Db::name('school') ->delete($id);

		$msg = $res ? ['msg' => 'success', 'code' => 0] : ['msg' => 'error', 'code' => 1001] ;

		ajaReturn('', $msg['code'], $msg['msg']);

	}
}