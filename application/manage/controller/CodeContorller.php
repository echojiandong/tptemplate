<?php
namespace app\manage\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\Image;
use think\Cache;
use vendor\phpoffic;
use app\manage\model\Code;

class codeContorller extends author
{
	/**
     * code列表页面
     * @author 韩春雷 2019.3.7
     */
	public function index()
	{
		//缓存初始化
		$options = [
		    // 缓存类型为File
		    'type'  =>  'File', 
		    // 缓存有效期为永久有效
		    'expire'=>  0, 
		    //缓存前缀
		    'prefix'=>  'think',
		     // 指定缓存目录
		    'path'  =>  APP_PATH.'runtime/cache/',
		];
		Cache::connect($options);
		return $this->fetch("code/codeList");
	}

	/**
     * 获取codeList
     * @author 韩春雷 2019.3.7
     * @return   [json]                  
     */
	public function getcodeList(Request $request)
	{
		$param = $request->param();
        if(array_key_exists('keyword',$param) && $param['keyword']!=''){
        	//获取当前user——id
            $userwhere['username']=['like',"%".$param['keyword']."%"];
        	$user = Db::name('user')->where($userwhere)->find();
        	$where['user_id'] = $user['uid'];
        }
        if(array_key_exists('status',$param) && !empty($param['status'])){
            $where['status']=$param['status'];
        }else{
        	$where['status']=['GT',0];
        }
        $where['is_forbidden']= 1;
        $page=$param['page'];
        $limit=$param['limit'];
        $data =  Code::getCodeList($where,$page,$limit);
        $count = $data['count'];
        unset($data['count']);
        //存入redis缓存
        Cache::store('redis')->set('where', $where);
        if($data){
        	$res = array(
					'code'=>0,
					'msg'=>'',
					'count'=>$count,
					'data'=>$data
				);
        	echo json_encode($res);
        }else{
        	jsonMsg("暂无数据",1);
        }
	}
	/**
     * code编辑页面
     * @author 韩春雷 2019.3.7
     */
	public function showCode(Request $request)
	{
		$param = $request->param();
		$where['id'] = $param['id'];

		$codeList = Db::name('code')->where($where)->select();
		//获取当前卡的信息
		//变量转换		
		$this->assign('list',$codeList);
		return view('code/editCode',['title'=>'编辑卡号']);
	}

	/**
	*@author 韩春雷 2019/3/8
	*code执行编辑
	*/
	public function editCode(Request $request)
	{
		$param=$request->param();
		$where['id'] = $param['id'];
		//获取卡号之前的状态
		$codeInfo = Db::name('code')->where($where)->find();
		$data['status'] = $param['status'];
		$info=new code();
		if($codeInfo['status'] != 2){
			$res=$info->where($where)->update($data);
			if($res)
			{
				return jsonMsg('修改成功',0);
			}
			else
			{
				return jsonMsg('修改失败',1);
			}
		}else{
			return jsonMsg('卡号已激活不能修改！',1);
		}
	}

	/**
	*@author  韩春雷 2019/3/8
	*code禁用
	*/
	public function forbidden(Request $request)
	{
		$param=$request->param();
		$where['id'] = $param['id'];
		$data['is_forbidden'] = 2;
		$info=new code();
		$res=$info->where($where)->update($data);
		if($res)
		{
			return jsonMsg('修改成功',0);
		}
		else
		{
			return jsonMsg('修改失败',1);
		}
	}
	/**
	*@author 韩春雷 2019/3/8
	*导出卡号
	*/
	public function output(Request $request)
	{
		$where = Cache::get('where');
		set_time_limit(0);
		//获取code信息
		$codeList =  Code::getCodeList($where);
		unset($codeList['count']);
		$objPHPExcel = new \PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()
                    ->setCellValue('A1', '编号')
                    ->setCellValue('B1', '卡号')
                    ->setCellValue('C1', '密码')
                    ->setCellValue('D1', '卡号类型')
                    ->setCellValue('E1', '卡号状态')
                    ->setCellValue('F1', '生成人')
                    ->setCellValue('G1', '生成时间')
                    ->setCellValue('H1', '所属管理员');
        foreach($codeList as $k=>$v){
        	$m = $k+2;
	        $objPHPExcel->getActiveSheet()
	        			->setCellValue("A".$m, $v['id'])
						->setCellValue("B".$m, $v['card'])
						->setCellValue("C".$m, $v['show_password'])
						->setCellValue("D".$m, $v['coursePackage'])
						->setCellValue("E".$m, $v['status_name'])
						->setCellValue("F".$m, $v['create_user_name'])
						->setCellValue("G".$m, $v['create_time'])
						->setCellValue("H".$m, $v['user_name']);
        }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="卡号信息.xlsx"');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        $objPHPExcel->disconnectWorksheets();
	}

	/**
	*@author 韩春雷 2019/3/11
	*code生成页面
	*/
	public function addCode(Request $request)
	{
		// $param = $request->param();
		//获取年级列表
		$gradeList = Db::name('grade')->select();
		//获取课程列表
		$subjectList = Db::name('subject')->select();

		$group_id = $_SESSION['think']['manageinfo']['group_id'];
		
		$code = new Code();
		$courseList = $code->getCourseByGradeId(); 
		
		$a = 1;
		$this->assign('gradeList',$gradeList);
		$this->assign('a',$a);
		$this->assign('subjectList',$subjectList);
		$this->assign('courseList', $courseList ? $courseList : array());
		$this->assign('isAdmin', $group_id == 1 ? true : false);

		return view('code/codeAdd',['title'=>'生成卡号']);
	}

	
	/**
	* code生成
	*@author 韩春雷 2019/3/11
	*@return   [json]
	*/
	public function doCodeAdd(Request $request)
	{
		$param=$request->param();
		
		// 验证是否有选择多个相同的班级及学期
		$result = $this->judge($param);

		$number = !empty($param['number'])? intval($param['number']) : '';
		$infoList = array();
		$m = 0;
		//将传递过来参数 组成一个数组
		for($i=0; $i<$number; $i++){
			if ($i == 1) {
				continue;
			}
			if(!empty($param['subject_id'.$i])){
				$subject_id =array_values($param['subject_id'.$i]);
				for($j=0; $j<count($subject_id); $j++){
					//查询对应video_class表里的信息
					$where['grade_id'] = $param['grade_id'.$i];
					$where['Semester'] = $param['Semester'.$i];
					$where['subject_id'] = $subject_id[$j];

					$res = Db::name('video_class')->where($where)->find();
					if($res){
						$infoList[$m]['coursePackage_id'] = $res['id'];
						$infoList[$m]['price'] = $res['price'];
						$infoList[$m]['Discount'] = $res['Discount'];
					}else{
						//获取课程列表
						$subjectList = Db::name('subject')->select();
						//获取年级列表
						$gradeList = Db::name('grade')->select();

						foreach ($subjectList as $key => $value) {
						$subjectListArr[$value['id']] = $value['subject'];
						}
						foreach ($gradeList as $key => $value) {
						$gradeListArr[$value['id']] = $value['grade'];
						}
						if($param['Semester'.$i] == 1){
						$semesterStr = '上学期';
						}elseif($param['Semester'.$i] == 2){
						$semesterStr = '下学期';
						}
						$grade_id = $param['grade_id'.$i];
						$subject_id = $subject_id[$j];
						return jsonMsg($gradeListArr[$grade_id].$subjectListArr[$subject_id].$semesterStr."课程不存在",1);
					}
					$m++;
				}
			}else{
				return jsonMsg('请选泽该卡的课程信息',1);
			}
		}

		
		$num = !empty($param['num'])? intval($param['num']) : '';
		
		//获取当前用户的权限 可生成的数量
		$user_id  = $_SESSION['think']['manageinfo']['uid'];
		$group_id = $_SESSION['think']['manageinfo']['group_id'];
		$coursePackage_id = array();
		$price = 0;
		//获取课程id数组coursePackage_id
		for($i=0; $i<count($infoList); $i++){
			if($group_id != 1){

				//获取当前管理员 的code_num(可生成数量)
				$userWhere['user_id'] = $user_id;
				$userWhere['video_class_id'] = $infoList[$i]['coursePackage_id'];
				$list = Db::name('user_code')->where($userWhere)->find();
				if($list){

					$code_num = $list['code_num'];    //当前用户可以生成的数量
					$code_num = !empty($code_num) ? $code_num : 0;
					// $code_num_all = !empty($code_num_all) ? $code_num_all : 0;
					//判断用户code-num
					if($code_num <= $num){
						//根据cursePackage_id 查询信息
						$videoClassInfo = Db::name('video_class')->where('id ='.$infoList[$i]['coursePackage_id'])->select();
						if($videoClassInfo[0]['Semester'] == 1){
							$videoClassInfo[0]['Semester_name'] = '上学期';
						}else{
							$videoClassInfo[0]['Semester_name'] = '下学期';
						}
						return jsonMsg($videoClassInfo[0]['name'].$videoClassInfo[0]['Semester_name'].'超过可生成的数量',1);
					}else{

						//判断通过 则添加到video_class_id 数组
						$coursePackage_id[$i] = $infoList[$i]['coursePackage_id'];
						$price =$price + $infoList[$i]['price']*$infoList[$i]['Discount'];
					}
				}else{
					//根据cursePackage_id 查询信息
					$videoClassInfo = Db::name('video_class')->where('id ='.$infoList[$i]['coursePackage_id'])->select();
					if($videoClassInfo[0]['Semester'] == 1){
						$videoClassInfo[0]['Semester_name'] = '上学期';
					}else{
						$videoClassInfo[0]['Semester_name'] = '下学期';
					}
					return jsonMsg($videoClassInfo[0]['name'].$videoClassInfo[0]['Semester_name'].'超过可生成的数量',1);
				}
			}else{

				$coursePackage_id[$i] = $infoList[$i]['coursePackage_id'];
				$price =$price + $infoList[$i]['price']*$infoList[$i]['Discount'] * $num ;
			}
		}
		//权限成功 生成卡号
		$codeList = code::makecode($num,$coursePackage_id,$user_id,$price);
		//修改user信息
		if($group_id != 1){
			for($i=0; $i<count($infoList); $i++){
				//获取当前管理员 的code_num(可生成数量)
				$userWhere['user_id'] = $user_id;
				$userWhere['video_class_id'] = $infoList[$i]['coursePackage_id'];
				$list = Db::name('user_code')->where($userWhere)->find();
				//修改管理圆的code可生成数量
				$data['code_num'] = $list['code_num'] - $num;
				Db::name('user_code')->where($userWhere)->update($data);
			}
		}
		//添加code
		$code  = new Code;
		$res = $code->saveAll($codeList);
		if($res)
		{
			return jsonMsg('生成成功',0);
		}else{
			return jsonMsg('生成失败',1);
		}
	}

	/**
	 * 卡生成 修改
	 * @author yjz 2019-06-18
	 */
	public function insertCardCode(Request $request)
	{
		$param = $request->param();
		
		$courseArr = $param['arr'];
		// $num = !empty($param['num'])? intval($param['num']) : '';

		if (!is_array($courseArr)) {
		  	return jsonMsg('参数错误', 1);
		}

		// 验证是否有选择多个相同的班级及学期
		$this->judgeLast($courseArr);
		
		//获取当前用户的权限 可生成的数量
		$user_id  = $_SESSION['think']['manageinfo']['uid'];
		$group_id = $_SESSION['think']['manageinfo']['group_id'];
		$coursePackage_id = array();

		$price=0;
		//参数处理
		$classList = $this->checkCourseData($courseArr);
		foreach ($classList as $key => $value) {
		  	if ($group_id != 1) {
				//获取当前管理员 的code_num(可生成数量)
				$userWhere['user_id'] = $user_id;
				$userWhere['video_class_id'] = $value['coursePackage_id'];
				$list = Db::name('user_code')->where($userWhere)->find();
				if($list){

					$code_num = $list['code_num'];    //当前用户可以生成的数量
					$code_num = !empty($code_num) ? $code_num : 0;
					
					//判断用户code-num
					if($code_num <= $value['order_num']){
						//根据cursePackage_id 查询信息
						$videoClassInfo = Db::name('video_class')->where(['id' =>$value['coursePackage_id']])->select();
						if($videoClassInfo[0]['Semester'] == 1){
							$videoClassInfo[0]['Semester_name'] = '上学期';
						}else{
							$videoClassInfo[0]['Semester_name'] = '下学期';
						}
						return jsonMsg($videoClassInfo[0]['name'].$videoClassInfo[0]['Semester_name'].'超过可生成的数量',1);
					}else{
						//判断通过 则添加到video_class_id 数组
						$coursePackage_id[$key] = $value['coursePackage_id'];
						$price =$price + $value['price']*$value['Discount'];

						//获取当前管理员 的code_num(可生成数量)
						$userWhere1['user_id'] = $user_id;
						$userWhere1['video_class_id'] = $value['coursePackage_id'];
						$list1 = Db::name('user_code')->where($userWhere1)->find();
						//修改管理圆的code可生成数量
						$data['code_num'] = $list1['code_num'] - $value['order_num'];
						Db::name('user_code')->where($userWhere1)->update($data);
					}
				} else {
					//根据cursePackage_id 查询信息
					$videoClassInfo = Db::name('video_class')->where(['id' =>$value['coursePackage_id']])->select();
					if($videoClassInfo[0]['Semester'] == 1){
						$videoClassInfo[0]['Semester_name'] = '上学期';
					}else{
						$videoClassInfo[0]['Semester_name'] = '下学期';
					}
					return jsonMsg($videoClassInfo[0]['name'].$videoClassInfo[0]['Semester_name'].'超过可生成的数量',1);
				}

			} else {
				$coursePackage_id[$key] = $value['coursePackage_id'];
				$price =$price + $value['price']*$value['Discount'] * $value['order_num'] ;
			}
			$codeList = code::makecode($value['order_num'], $value['coursePackage_id'], $user_id, $price);
			//添加code
			$code  = new Code;
			$res = $code->saveAll($codeList);
		}
		return jsonMsg('生成成功',0);
	}

	public function checkCourseData($data)
    {
      if (!is_array($data)) {
        return jsonMsg('参数错误', 1);
      }
      $dataList = [];
      foreach ($data as $key => $value) {
          $grade = $value['grade'];
		  $semester = $value['semester'];
		  $remark = isset($value['remark']) ? $value['remark'] : '';
          if (isset($value['course']) && !empty($value['course'])) {
            
            foreach ($value['course'] as $k => $v) {
              
              if (!$v['num']) {
                continue;
              }
              
              $dataList[] = [
                'grade_id' => $grade,
                'Semester' => $semester,
                'num'      => $v['num'],
				'subject_id' => $v['id'],
				'remark'   => $remark
              ];
              
            }
          }
      }

      if (!$dataList) {
        return jsonMsg('至少选择一个课程生成数量', 1);
      }

      foreach ($dataList as $key => $value) {
		$where['grade_id'] = $value['grade_id'];
		$where['Semester'] = $value['Semester'];
		$where['subject_id'] = $value['subject_id'];
		$res = Db::name('video_class')->where($where)->find();
		if ($res) {
			$infoList[$key]['video_class_id'] = $res['id'];
			$infoList[$key]['class_price'] = $res['price'];
			$infoList[$key]['Discount'] = $res['Discount'];
			$infoList[$key]['order_num'] = $value['num'];
			$infoList[$key]['coursePackage_id'] = $res['id'];
			$infoList[$key]['price'] = $res['price'];
			if (isset($value['remark'])) {
				$infoList[$key]['remark'] = !empty($value['remark']) ? $value['remark'] : '';
			}
			
		}
    }
    return $infoList;
}

	/**
     * code分配页面
     * @author 韩春雷 2019.3.13
     */
	public function codeAssign()
	{
		return $this->fetch("code/codeAssign");
	}
	/**
     * 获取管理员列表
     * @author 韩春雷 2019.3.13
     * @return [json]
     */
	public function getUserList(Request $request)
	{
		$param = $request->param();

		$page=$param['page'];
        $limit=$param['limit'];
		//获取管理员用户code列表信息
		$res =  Code::getUserCodeList($page,$limit);
		$count = $res['count'];
        unset($res['count']);
		if($res){
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
	/**
     * 获取管理员详细信息数据
     * @author 韩春雷 2019.3.13
     * @return [array]
     */
	public function getUserInfo(Request $request)
	{
		$param = $request->param();

        $uid    = $param['id'];
        //获取当前管理员的可生成权限
        $list = Db::name('user_code')->alias('uc')
        			->join('guard_video_class vc','uc.video_class_id = vc.id')
        			->join('guard_grade g','g.id = vc.grade_id')
        			->join('guard_subject s','s.id = vc.subject_id')
        			->where('uc.user_id = '.$uid)
        			->field('uc.code_num,g.grade,s.subject,vc.Semester')
        			->order('g.id')
        			->select();
        //获取赠送里列表
        $assignList = Db::name('user_assign_log')
        				->alias('ua')
        				->join('guard_user u','u.uid = ua.accept_user_id')
        				->where('ua.assign_user_id = '.$uid)
        				->field('ua.*,u.username')
        				->order('ua.add_time desc')
        				->select();
        //获取受赠里列表
        $acceptList = Db::name('user_assign_log')
        				->alias('ua')
        				->join('guard_user u','u.uid = ua.accept_user_id')
        				->where('ua.accept_user_id = '.$uid)
        				->field('u.username,ua.*')
        				->order('ua.add_time desc')
        				->select();
        $assignList = $this->getUserInfochange($assignList);
        $acceptList = $this->getUserInfochange($acceptList);
		
        $this->assign('list',$list);
        $this->assign('assignList',$assignList);
        $this->assign('acceptList',$acceptList);
		return view('code/userCodeInfo');
	}

	public function getUserInfochange($assignList){
		 foreach ($assignList as $key => $value) {
        	if($value['video_class_id']){
				$video_class_id = explode(',',$value['video_class_id']);
				$classInfo = array();
				//获取详细的数据
				for($i=0; $i<count($video_class_id); $i++){
					$classList = Db::name('video_class')
									->alias('vc')
									->where('vc.id='.$video_class_id[$i])
									->join('guard_grade g','g.id = vc.grade_id')
									->join('guard_subject s','s.id = vc.subject_id')
									->field('g.grade,s.subject,vc.Semester')
									->select();
					foreach ($classList as $ke => $val) {
						$classList[$ke]['Semester'] = $val['Semester'] == 1 ? '上':'下';
						$classInfo[$i] = $classList[$ke]['grade'].'-'.$classList[$ke]['subject'].'-'.$classList[$ke]['Semester'];
					}
				}
				
				$assignList[$key]['video_class'] = implode('，',$classInfo);
			}
			$assignList[$key]['add_time'] = date('Y-m-d',$value['add_time']);
        }

        return $assignList;
	}
	/**
     * 管理员分配code
     * @author 韩春雷 2019.3.13
     */
	public function userCodeAssign(Request $request)
	{

		$param = $request->param();
		$uid    = $param['id'];
		
		$group_id = $_SESSION['think']['manageinfo']['group_id'];
		
        //获取年级列表
		$gradeList = Db::name('grade')->select();
		//获取课程列表
		$subjectList = Db::name('subject')->select();
		$code = new Code();
		$courseList = $code->getCourseByGradeId(); 
		
		$a = 1;

		$this->assign('gradeList',$gradeList);
		$this->assign('a',$a);
		$this->assign('subjectList',$subjectList);
		$this->assign('id',$uid);
		$this->assign('courseList', $courseList);
		$this->assign('isAdmin', $group_id == 1 ? true : false);
		return view('code/userCodeAssign');
	}
	/**
     * 执行分配code
     * @author 韩春雷 2019.3.13
     */
	public function doUserCodeAssign(Request $request){

		$param = $request->param();
		$uid    = $param['id'];
		
		$this->judge($param);

        $code  = new Code;
        //获取当前管理圆的信息
        $now_group_id = $_SESSION['think']['manageinfo']['group_id'];
        $user_id = $_SESSION['think']['manageinfo']['uid'];
		
        $number = !empty($param['number'])? intval($param['number']) : '';
        $num = !empty($param['num'])? intval($param['num']) : "";
        $remark = !empty($param['remark'])? $param['remark'] : "";

		$infoList = array();
		$m = 0;
		//将传递过来参数 组成一个数组
		for($i=0; $i<$number; $i++){
			if ($i == 1) {
				continue ;
			}
			if(!empty($param['subject_id'.$i])){
				$subject_id = array_values($param['subject_id'.$i]);
				for($j=0; $j<count($subject_id); $j++){
					//查询对应video_class表里的信息
					$where['grade_id'] = $param['grade_id'.$i];
					$where['Semester'] = $param['Semester'.$i];
					$where['subject_id'] = $subject_id[$j];

					$res = Db::name('video_class')->where($where)->find();
					
					if($res){
						$infoList[$m]['coursePackage_id'] = $res['id'];
						$m++;
					}else{
						//获取课程列表
						$subjectList = Db::name('subject')->select();
						//获取年级列表
						$gradeList = Db::name('grade')->select();

						foreach ($subjectList as $key => $value) {
						$subjectListArr[$value['id']] = $value['subject'];
						}
						foreach ($gradeList as $key => $value) {
						$gradeListArr[$value['id']] = $value['grade'];
						}
						if($param['Semester'.$i] == 1){
						$semesterStr = '上学期';
						}elseif($param['Semester'.$i] == 2){
						$semesterStr = '下学期';
						}
						$grade_id = $param['grade_id'.$i];
						$subject_id = $subject_id[$j];
						return jsonMsg($gradeListArr[$grade_id].$subjectListArr[$subject_id].$semesterStr."课程不存在",1);
					}
				}
			}else{
				return jsonMsg('请选泽该卡的课程信息',1);
			}
		}
		$coursePackage_id = array();
		//获取课程id数组coursePackage_id
		for($i=0; $i<count($infoList); $i++){
			//如果不是管理员 判断当前code_num 数量
			if($now_group_id != 1){

				//获取当前管理员 的code_num(可生成数量)
				$userWhere['user_id'] = $user_id;
				$userWhere['video_class_id'] = $infoList[$i]['coursePackage_id'];
				$list = Db::name('user_code')->where($userWhere)->find();
				if($list){

					$code_num = $list['code_num'];    //当前用户可以生成的数量
					$code_num = !empty($code_num) ? $code_num : 0;
					// $code_num_all = !empty($code_num_all) ? $code_num_all : 0;
					//判断用户code-num
					if($code_num < $num){

						return jsonMsg('超过可生成的数量',1);
					}else{

						//判断通过 则添加到video_class_id 数组
						$coursePackage_id[$i] = $infoList[$i]['coursePackage_id'];
					}
				}else{

					return jsonMsg('超过可生成的数量',1);
				}
			}else{

				$coursePackage_id[$i] = $infoList[$i]['coursePackage_id'];
			}
		}

		//修改user_code信息
		for($i=0; $i<count($infoList); $i++){
			if($now_group_id != 1){
				//减去父级管理圆的user_code信息
				$userWhere['user_id'] = $user_id;
				$userWhere['video_class_id'] = $infoList[$i]['coursePackage_id'];
				$list = Db::name('user_code')->where($userWhere)->find();
				//修改管理圆的code可生成数量
				$data['code_num'] = $list['code_num'] - $num;
				Db::name('user_code')->where($userWhere)->update($data);
			}

			//添加user_code信息
			$userSonWhere['user_id'] = $uid;
			$userSonWhere['video_class_id'] = $infoList[$i]['coursePackage_id'];
			$userSonList = Db::name('user_code')->where($userSonWhere)->find();
			if($userSonList){
				$dataSon['code_num'] = $userSonList['code_num'] + $num;
				Db::name('user_code')->where($userSonWhere)->update($dataSon);
			}else{
				$dataSon['code_num'] = $num;
				$dataSon['user_id'] = $uid;
				$dataSon['video_class_id'] = $infoList[$i]['coursePackage_id'];
				Db::name('user_code')->insert($dataSon);
			}
		}

		//添加user_assign_log(管理员赠送记录表)
		$info = array();
		$info['video_class_id'] = implode(',',$coursePackage_id);
		$info['assign_user_id'] = $user_id;
		$info['accept_user_id'] = $uid;
		$info['add_time'] = time();
		$info['num'] = $num;
		$info['remark'] =  $remark;

		if($user_id != $uid){
			$res = Db::name('user_assign_log')->insert($info);
			if($res)
			{
				return jsonMsg('赠送成功',0);
			}else{
				return jsonMsg('赠送失败',1);
			}
		}else{
			return jsonMsg('自己赠送自己太没意思了！',1);
		}
	}

	/**
	 * 卡分配赠送提交修改后
	 * @author yangjizhou 2019-06-19
	 */

	public function doCardAssign(Request $request)
	{
		$param = $request->param();
		$courseArr = $param['arr'];
		if (!is_array($courseArr)) {
			return jsonMsg('参数错误', 1);
	  	}
		$uid = (int) $param['id'];
		if (!$uid) {
			return jsonMsg('非法修改', 1);
		}
		// 验证是否有选择多个相同的班级及学期
		$this->judgeLast($courseArr);
		//参数处理
		$classList = $this->checkCourseData($courseArr);
		
		$code  = new Code;
        //获取当前管理圆的信息
        $now_group_id = $_SESSION['think']['manageinfo']['group_id'];
        $user_id = (int) $_SESSION['think']['manageinfo']['uid'];
		if ($user_id == $uid) {
			return jsonMsg('不能赠送给自己', 1);
		}
		

		foreach ($classList as $key => $value) {
			if ($now_group_id != 1) {
				$where['user_id'] = $user_id;
				$where['video_class_id'] = $value['coursePackage_id'];
				$list = Db::name('user_code')->where($where)->find();

				if ($list) {
					$code_num = !empty($list['code_num']) ? $list['code_num'] : 0;
					if ($code_num < $value['order_num']) {
						return jsonMsg('超过可生成的数量', 1);
					} 
					//修改管理圆的code可生成数量
					$data['code_num'] = $list['code_num'] - $value['order_num'];
					Db::name('user_code')->where($userWhere)->update($data);
				} 
			} else {
				//添加user_code信息
				$userSonWhere['user_id'] = $uid;
				$userSonWhere['video_class_id'] = $value['coursePackage_id'];
				$userSonList = Db::name('user_code')->where($userSonWhere)->find();
				if($userSonList){
					$dataSon['code_num'] = $userSonList['code_num'] + $value['order_num'];
					Db::name('user_code')->where($userSonWhere)->update($dataSon);
				}else{
					$dataSon['code_num'] = $value['order_num'];
					$dataSon['user_id'] = $uid;
					$dataSon['video_class_id'] = $value['coursePackage_id'];
					Db::name('user_code')->insert($dataSon);
				}
			}

			//添加user_assign_log(管理员赠送记录表)
			$info = array();
			$info['video_class_id'] = $value['coursePackage_id'];
			$info['assign_user_id'] = $user_id;
			$info['accept_user_id'] = $uid;
			$info['add_time'] = time();
			$info['num'] = $value['order_num'];
			$info['remark'] =  $value['remark'];
			Db::name('user_assign_log')->insert($info);
		}
		return jsonMsg('赠送成功',0);
	}




	// /**
 //     * 课程包管理 
 //     * @author 韩春雷 2019.4.11
 //     */
	// public function classBag(Request $request){

	// 	return $this->fetch("code/classBagList");
	// }
	// /**
 //     * 获取课程包管理 
 //     * @author 韩春雷 2019.4.11
 //     */
	// public function classBagList(Request $request){
	// 	$param = $request->param();
 //        $page=$param['page'];
 //        $limit=$param['limit'];
 //        $code = new code();
 //        $data =  $code->getClassBagList($page,$limit);
 //        $count = $data['count'];
 //        unset($data['count']);
 //        if($data){
 //        	$res = array(
	// 				'code'=>0,
	// 				'msg'=>'',
	// 				'count'=>$count,
	// 				'data'=>$data
	// 			);
 //        	echo json_encode($res);
 //        }else{
 //        	jsonMsg("暂无数据",1);
 //        }
	// }
	// /**
 //     * 编辑课程包管理页面
 //     * @author 韩春雷 2019.4.11
 //     */
	// public function showClassBag(Request $request){
	// 	$param = $request->param();
 //        $id    = $param['id'];

	// 	$classBagList = Db::name('course_package')
	// 				->where('id = '.$id)
	// 				->find();
	// 	//变量转换
	// 	$subjectId = explode(',',$classBagList['subjectId']);
	// 	//获取年级列表
	// 	$gradeList = Db::name('grade')->select();
	// 	//获取课程列表
	// 	$subjectList = Db::name('subject')->select();
	// 	$this->assign('list',$classBagList);
	// 	$this->assign('subjectId',$subjectId);
	// 	$this->assign('gradeList',$gradeList);
	// 	$this->assign('subjectList',$subjectList);
	// 	return view('code/editClassBag');
	// }
	// /**
 //     * 执行编辑课程包管理页面
 //     * @author 韩春雷 2019.4.11
 //     */
	// public function editClassBag(Request $request){
	// 	$param=$request->param();
	// 	$where['id'] = $param['id'];
	// 	$data['pageName'] = $param['pageName'];
	// 	$data['price'] = $param['price'];
	// 	$data['gradeId'] = $param['gradeId'];
	// 	$data['status'] = $param['status'];
	// 	$data['uptime'] = time();
	// 	$data['upadmin'] = $_SESSION['think']['manageinfo']['uid'];
	// 	if($param['subjectId']){
	// 		$data['subjectId'] = implode(',',$param['subjectId']);
	// 	}
	// 	$res=Db::name('course_package')->where($where)->update($data);
	// 	if($res)
	// 	{
	// 		return jsonMsg('修改成功',0);
	// 	}
	// 	else
	// 	{
	// 		return jsonMsg('修改失败',1);
	// 	}
	// }
	// /**
 //     * 执行删除课程包
 //     * @author 韩春雷 2019.4.11
 //     */
	// public function delClassBag(Request $request){
	// 	$param=$request->param();
	// 	$where['id'] = $param['id'];
	// 	$res=Db::name('course_package')->where($where)->delete();
	// 	if($res)
	// 	{
	// 		return jsonMsg('删除成功',0);
	// 	}
	// 	else
	// 	{
	// 		return jsonMsg('删除失败',1);
	// 	}
	// }
	// /**
 //     * 添加课程包页面
 //     * @author 韩春雷 2019.4.11
 //     */
	// public function addClassBag(){

	// 	//获取年级列表
	// 	$gradeList = Db::name('grade')->select();
	// 	//获取课程列表
	// 	$subjectList = Db::name('subject')->select();
	// 	$this->assign('gradeList',$gradeList);
	// 	$this->assign('subjectList',$subjectList);
	// 	return view('code/addClassBag');
	// }
	// /**
 //     * 执行添加课程包页面
 //     * @author 韩春雷 2019.4.11
 //     */
	// public function doAddClassBag(Request $request){
	// 	$param=$request->param();
	// 	$data['pageName'] = $param['pageName'];
	// 	$data['price'] = $param['price'];
	// 	$data['gradeId'] = $param['gradeId'];
	// 	$data['status'] = 1;
	// 	$data['addtime'] = time();
	// 	$data['userId'] = $_SESSION['think']['manageinfo']['uid'];
	// 	if($param['subjectId']){
	// 		$data['subjectId'] = implode(',',$param['subjectId']);
	// 	}
	// 	$res=Db::name('course_package')->insert($data);
	// 	if($res)
	// 	{
	// 		return jsonMsg('添加成功',0);
	// 	}
	// 	else
	// 	{
	// 		return jsonMsg('添加失败',1);
	// 	}
	// }



	/**
	 * 根据年级获取相应的课程
	 * @author 2019-06-10 yangjizhou 
	 * 
	 */
	public function getCourseByClass(Request $request)
	{
		$gradeid = $request->param('gradeid'); // 年级
		$semesterid = $request->param('semesterid'); // 学期
		$group_id = $_SESSION['think']['manageinfo']['group_id'];
		$code = new Code();
		$courseList = $code->getCourseByGradeId($gradeid, $semesterid); 
		$isAdmin = $group_id == 1 ? true : false;
		if ($courseList) {
			return jsonMsg('success', 0, json_encode($courseList), $isAdmin);
		} else {
			return jsonMsg('没有相应课程', 1);
		}
	}


	/**
	 * 验证是否选择了两个及以上的相同的班级及学期-修改前的验证
	 * @author 2019-06-11 yangjizhou
	 */
	private function judge($param)
	{
		$number = !empty($param['number'])? intval($param['number']) : '';
		$judeg = [];

		for ($i = 0; $i < $number; $i++) {
			if (!empty($param['grade_id'.$i]) && !empty($param['Semester'.$i])) {
				$judeg[] = [
					'grade_id' => $param['grade_id'.$i],
					'semester' => $param['Semester'.$i]
				];
			}
		}
		
		if (empty($judeg)) {
			return jsonMsg('请选择班级或学期',1);
		}
		$oldCount = count($judeg);

		$resArr = $this->remove_duplicate($judeg);
		$newCout = count($resArr);
		$res = ($oldCount == $newCout) ? true : false;
		if (!$res) {
			return jsonMsg('不能同时选择两个及以上的相同的班级学期', 1);
		}
	}

	/**
	 * 验证是否选择了两个及以上的相同的班级及学期-修改后
	 * @author 2019-06-18 yangjizhou
	 */
	public function judgeLast($param)
	{
		$judeg = [];
		if (empty($param)) {
			return jsonMsg('没有选择课程', 1);
		}
		foreach ($param as $key => $value) {
			$judeg[] = [
				'grade_id' => $value['grade'],
				'semester' => $value['semester']
			];
		}

		$oldCount = count($judeg);

		$resArr = $this->remove_duplicate($judeg);
		$newCout = count($resArr);
		$res = ($oldCount == $newCout) ? true : false;
		if (!$res) {
			return jsonMsg('不能同时选择两个及以上的相同的班级学期', 1);
		}

	}




	/**
	 * 二维数组去重
	 * @author 2019-06-11 yangjizhou
	 */

	private function remove_duplicate($data)
	{
		foreach ($data[0] as $key => $value) {
			$arr_inner_key[] = $key;
		}
		foreach ($data as $k => $v) {
			$v = join(',', $v);
			$temp[$k] = $v;
		}

		$temp = array_unique($temp);
		foreach ($temp as $k => $v) {
			$a = explode(',', $v);
			$arr_after[$k] = array_combine($arr_inner_key, $a);
		}

		return $arr_after;
	}
}
