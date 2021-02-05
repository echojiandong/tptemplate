<?php
namespace app\manage\model;
use think\Model;
use Phinx\Db\Table;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\Image;
use app\common\controller\PinyinController;
class Code extends Model
{
	const LENT = 9;					//随机数长度
	/**
     * 获取卡号列表数据
     * @author 韩春雷 2019.3.7
     * @DateTime 2019-02-27T14:28:40+0800
     * @param    array       $where [筛选条件]
     * @param    int         $page  [页码]
     * @param    int         $limit [每页数量]
     * @return   [array]                  
     */
	public static function getCodeList($where,$page=null,$limit=null)
	{
		$group_id = $_SESSION['think']['manageinfo']['group_id'];
		$user_id  = $_SESSION['think']['manageinfo']['uid'];
		if($group_id == 1 || $group_id == 2){
			if($page){
				$codeList =DB::name('code')->where($where)->page($page,$limit)->select();
			}else{
				$codeList =DB::name('code')->where($where)->select();
			}
			$count=DB::name('code')->where($where)->count();
		}else{
			//判断管理员身份
			$title_name = Db::name('group')->field('title')->where('id = '.$group_id)->find();
			if($title_name['title'] == '客服'){
				$where['user_id'] = $user_id;
				if($page){
					$codeList = DB::name('code')->where($where)
								 ->page($page,$limit)
								 ->select();
				}else{
					$codeList =DB::name('code')->where($where)->select();
				}
				$count  = DB::name('code')
							->where($where)
						  	->count();
			}else{
				//获取所有的子类代理商
				$idList = Db::name('user')->alias('u')
										  ->join('guard_group g','g.id = u.group_id')
										  ->field('u.uid')
										  ->where('u.parent_id = '.$user_id.' and g.title != "客服"')
										  ->select();
				$uid = array();
				if(isset($idList)){
					foreach ($idList as $key => $value) {
						$uid[] = $value['uid'];
					}
				}
				$uid[] = $user_id;
				//转换数据类型
				$uid = implode(',',$uid);
				if($page){
					$codeList = DB::name('code')->where($where)
						  		 ->where('user_id in ('.$uid.')')
						  		 ->page($page,$limit)
						  		 ->select();
				}else{
					$codeList = DB::name('code')->where($where)
						  		 ->where('user_id in ('.$uid.')')
						  		 ->select();
				}
				$count  = DB::name('code')->where($where)
						  		 ->where('user_id in ('.$uid.')')
						  		 ->count();
			}			
		}

		$codeList =self::changeList($codeList);
		$codeList['count'] = $count;
		
		return $codeList;

	}

	/**
     * 数据转换
     * @author 韩春雷 2019.3.11
     * @DateTime 2019-02-27T14:28:40+0800
     * @param    array  $list [转换的数组]
     * @return   [array]                  
     */
	public static function changeList($list){
		//获取管理员数据
		$user = Db::name('user')->field('uid,username')->select();
		foreach ($user as $key => $value) {
			$userList[$value['uid']] = $value['username'];
		}
		//获取年级列表
		$grade = Db::name('grade')->select();
		foreach ($grade as $key => $value) {
			$gradeList[$value['id']] = $value['grade'];
		}
		//获取课程列表
		$subject = Db::name('subject')->select();
		foreach ($subject as $key => $value) {
			$subjectList[$value['id']] = $value['subject'];
		}
		//转换数据
		foreach ($list as $key => $value) {
			if(!empty($value['user_id'])){
				if(!empty($userList[$value['user_id']])){
					$list[$key]['user_name'] = $userList[$value['user_id']];
				}
			}
			if(!empty($value['create_user_id'])){
				if(!empty($userList[$value['create_user_id']])){
					$list[$key]['create_user_name'] = $userList[$value['create_user_id']];
				}
			}
			if($value['coursePackage_id']){
				$coursePackage_id = explode(',',$value['coursePackage_id']);
				$classInfo = array();
				//获取详细的数据
				for($i=0; $i<count($coursePackage_id); $i++){
					$classList = Db::name('video_class')
									->alias('vc')
									->where('vc.id='.$coursePackage_id[$i])
									->join('guard_grade g','g.id = vc.grade_id')
									->join('guard_subject s','s.id = vc.subject_id')
									->field('g.grade,s.subject,vc.Semester')
									->select();
					foreach ($classList as $ke => $val) {
						$classList[$ke]['Semester'] = $val['Semester'] == 1 ? '上':'下';
						$classInfo[$i] = $classList[$ke]['grade'].'-'.$classList[$ke]['subject'].'-'.$classList[$ke]['Semester'];
					}
				}
				
				$list[$key]['coursePackage'] = implode('，',$classInfo);
			}
			// $list[$key]['type_name'] = $value['type'] == 0 ? '否' : '是';
			if($value['status']){
				if($value['status'] == '1'){
					$list[$key]['status_name'] = '未激活'; 
				}elseif($value['status'] == '2'){
					$list[$key]['status_name'] = '已激活'; 
				}elseif($value['status'] == '3'){
					$list[$key]['status_name'] = '禁用';
				}elseif(($value['status'] == '4')){
					$list[$key]['status_name'] = '已卖出';
				}
			}
			$list[$key]['create_time'] = date('Y-m-d',$value['create_time']);
			$list[$key]['update_time'] = date('Y-m-d',$value['update_time']);
			$list[$key]['now_group_id'] = $_SESSION['think']['manageinfo']['group_id'];
		}
		return $list;
	}

	/**
     * 生成卡号
     * @author 韩春雷 2019.3.11
     * @DateTime 2019-02-27T14:28:40+0800
     * @param    int       $num [生成数量]
     * @param    int       $gradeId  [年级id]
     * @return   [array]                  
     */
	public static  function makecode($num,$coursePackage_id,$user_id,$price){

		//时间
		$time = date('Ymd',time());
		//变量转换 逗号拼接
		if (is_array($coursePackage_id)) {
			$coursePackage_id = implode(',',$coursePackage_id);
		}
		//获取当卡号的排序
		$c_list = Db::name('code')->order('code_sort desc')->select();
		if(!empty($c_list)){
			$sort_num =$c_list[0]['code_sort'];
		}else{
			$sort_num = 0;
		}
		// //获取该年级的年级名称 大写首字母
		// $grade_name = Db::name('grade')->field('grade')->where('id = '.$coursePackageList['gradeId'])->find();
		// $grade_name = self::pinYin($grade_name['grade']);

		$codeList = array();
		$j =0;
		for($i = $sort_num+1; $i<=$sort_num+$num;$i++){
			//六位随机数
			$strRand = self::strRand();
			$codeList[$j]['card'] = $time.$strRand.str_pad($i, 6, '0', STR_PAD_LEFT);
			$codeList[$j]['show_password'] = rand(100000,999999);
			$codeList[$j]['password'] = md5($codeList[$j]['show_password']);
			$codeList[$j]['status'] = 1;
			$codeList[$j]['create_user_id'] = $_SESSION['think']['manageinfo']['uid'];
			$codeList[$j]['coursePackage_id'] = $coursePackage_id;
			$codeList[$j]['create_time'] = time();
			$codeList[$j]['code_sort'] = $i;
			$codeList[$j]['user_id'] = $user_id;
			$codeList[$j]['price'] = $price;
			$j++;                                  
		}

		return $codeList;
	}

	
	/**
     * 生成六位随机数
     * @author 韩春雷 2019.3.11
     * @return [string]                  
     */
	public static function strRand(){
    	$pattern = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHKLMNPQRSTUVWXYZ';
        $len = strlen($pattern);
        $string = '';
        for($i = 1;$i <= self::LENT; $i++){
        	$string .= $pattern[mt_rand(0, $len-1)];
        }
        return $string;
    }
	/**
     * 转化首字母大写
     * @author 韩春雷 2019.3.11
     * @DateTime 2019-02-27T14:28:40+0800
     * @param    string       $string [年级名称]
     * @return   [string]                  
     */
    private static function pinYin($string){
        $PY = new PinyinController();
        $name = rtrim(strtoupper($PY->getFirstPY($string)), '');

        return str_replace('.', '', $name);
    }
    /**
     * 获取管理员code列表信息
     * @author 韩春雷 2019.3.11
     * @DateTime 2019-02-27T14:28:40+0800
     * @param    int         $page  [页码]
     * @param    int         $limit [每页数量]
     * @return   [string]                  
     */
    public static function getUserCodeList($page,$limit){

    	if($_SESSION['think']['manageinfo']['group_id'] != 1){

			//获取当前子类uid
			$id_list = Db::name('user')
						->field('uid')
						->where('parent_id = '.$_SESSION['think']['manageinfo']['uid'])
						->page($page,$limit)
						->select();
			if(isset($id_list)){
				foreach ($id_list as $key => $value) {
					$uid[] = $value['uid']; 
				}
			}
			$uid[] = $_SESSION['think']['manageinfo']['uid'];

			//获取管理员数据
			$where['u.uid'] = array('in',$uid);
			//只获取代理商和客服的信息
			$where['g.id'] = array('in','5,6,8,9');
			$res=Db::name('user')
				->alias('u')
				->join('guard_group g','u.group_id=g.id')
				->field('u.*,g.title')
				->where($where)
				->group('u.uid')
				->page($page,$limit)
				->select();
			$count=Db::name('user')
					->alias('u')
					->join('guard_group g','u.group_id=g.id')
					->field('u.*,g.title')
					->where($where)
					->page($page,$limit)
					->count();

		}else{
			//只获取代理商和客服的信息
			$where['g.id'] = array('in','5,6,8,9');
			$res=Db::name('user')
				->distinct(true)
				->alias('u')
				->join('guard_group g','u.group_id=g.id')
				->field('u.*,g.title')
				->where($where)
				->page($page,$limit)
				->select();
			// $res = Db::query('select distinct u.*,g.auth,g.title,count(c.id) as num from guard_user as u join guard_group as g on u.group_id=g.id join guard_code as c on u.uid = c.user_id where g.id in (5,6,8,9) group by u.uid  order by u.uid desc');
			
			$count=Db::name('user')
					->distinct(true)
					->alias('u')
					->join('guard_group g','u.group_id=g.id')
					->field('u.*,g.title')
					->where($where)
					->page($page,$limit)
					->count();
		}
		//获取用户的code数量
		$codeList = Db::name('user')
							->alias('u')
							->distinct(true)
							->join('guard_code c','c.user_id=u.uid')
							->field('u.uid,count(c.id) as num')
							->group('u.uid')
							->select();
		foreach ($res as $k => $val) {
			$res[$k]["num"] = 0;
			foreach ($codeList as $key => $value) {
				if($value['uid'] == $val['uid']){
					$res[$k]["num"] = $value["num"];
				}
				if($val['paytype']){
					$res[$k]['paytype_name'] = $val['paytype'] == 1 ? '微信' : '支付宝';
				}else{
					$res[$k]['paytype_name'] = '暂无上传';
				}
				$res[$k]['now_group_id'] = $_SESSION['think']['manageinfo']['group_id'];
			}
		}
		$res['count'] = $count;
		return $res;
    }

    /**
     * 获取管理员code列表详细信息
     * @author 韩春雷 2019.3.11
     * @DateTime 2019-02-27T14:28:40+0800
     * @param    int         $page   [页码]
     * @param    array       $where  [筛选条件]
     * @param    int         $uid    [用户id]
     * @param    int         $limit  [每页数量]
     * @return   [array]                  
     */
    public static function getUserCodeInfo($uid,$where,$page,$limit){
    	//获取用户列表数据
    	$res = Db::name('user')
    			 ->alias('u')
    			 ->join('guard_code c','u.uid = c.user_id')
    			 ->field('c.*,u.username')
    			 ->where($where)
    			 ->page($page,$limit)
    			 ->group('c.id')
    			 ->select();
    	$count = Db::name('user')
    			 ->alias('u')
    			 ->join('guard_code c','u.uid = c.user_id')
    			 ->field('c.*,u.username')
    			 ->where($where)
    			 ->group('c.id')
    			 ->count();
    	$person_list = Db::name('code')
    					->alias('c')
    			 		->join('guard_person p','p.id = c.person_id')
    			 		->field('p.nickName,c.user_id')
    			 		->where('c.user_id ='.$uid.' and c.status = 1')
    			 		->select();
    	if($where['c.status'] == 1){
    		foreach ($person_list as $key => $value) {
    			foreach ($c as $k => $val) {
    				if($value['user_id'] == $val['user_id']){
    					$res['$k']['nickName'] = $value['nickName'];
    				}
    			}
    		}
    	}
    	$res =self::changeList($res);
    	$res['count'] = $count; 
    	return $res;
    }
   //  /**
   //   * 获取课程包
   //   * @author 韩春雷 2019.3.11
   //   * @DateTime 2019-02-27T14:28:40+0800
   //   * @param    int         $page   [页码]
   //   * @param    int         $limit  [每页数量]
   //   * @return   [array]                  
   //   */
   //  public static function getClassBagList($page,$limit){
   //  	$res = Db::name('course_package')
   //  			->alias('cp')
   //  			->join('guard_grade g','g.id = cp.gradeId')
   //  			->join('guard_user u','u.uid = cp.userId')
   //  			->page($page,$limit)
   //  			->field('cp.*,g.grade,u.username')
   //  			->select();
   //  	$count = Db::name('course_package')
   //  			->alias('cp')
   //  			->join('guard_grade g','g.id = cp.gradeId')
   //  			->join('guard_user u','u.uid = cp.userId')
   //  			->field('cp.*,g.grade,u.username')
   //  			->count();
   //  	//获取课程列表
   //  	$subjectList = Db::name('subject')->select();
   //  	foreach ($subjectList as $key => $value) {
   //  		$list[$value['id']] = $value['subject']; 
   //  	}
   //  	//变量转换 subjectId
   //  	$subjectArr=array();
   //  	foreach ($res as $key => $value) {
			// if($value['subjectId']){
			// 	$subject = explode(',',$value['subjectId']);
			// 	for ($i=0; $i < count($subject); $i++) {
			// 		$subjectArr[$i] = $list[$subject[$i]];
			// 	}
   //  		}
   //  		$res[$key]['subjectId'] = implode(',',$subjectArr);
   //  		$res[$key]['addtime'] = date('Y-m-d H:i:s',$value['addtime']);
   //   	}
   //   	$res['count'] = $count;
   //   	return $res;
   //  }


  
	/**
	 * 获取年级课程， 默认七年级上册
	 * @param $grade_id 年级
	 * @param $semesterid 学期
	 * 
	 */
	public function getCourseByGradeId($grade_id = 7, $semesterid = 1)
	{
		
		$subject = Db::name('video_class')->field('id, grade_id, subject_id')->where(['grade_id'=> $grade_id, 'Semester' => $semesterid])->group('subject_id')->select();
		$subject = $this->convert_arr_key($subject, 'subject_id');
		if (empty($subject)) return ;
		// 获取键
		$subjectId = array_keys($subject);

		$courseName = Db::name('subject')->where(['id'=>['in', $subjectId]])->select();
		if (empty($subject)) return ;

		$uid = $_SESSION['think']['manageinfo']['uid'];
		
		foreach ($courseName as $key => $val) {
			$courseName[$key]['class_id'] = $subject[$val['id']]['id'];
			$courseName[$key]['courseNum'] = $this->getCourseNum($subject[$val['id']]['id'], $uid);
		}
		
		return $courseName;

	}

	private function getCourseNum($class_id,$uid,$type="",$productStatus=0)
	{
        if($productStatus==1){
            if(empty($type)){
                return  Db::name('user_code')->where(['user_id' => $uid, 'product_id' => $class_id])->value('code_num');
            }else{
                return  Db::name('user_code')->where(['user_id' => $uid, 'product_id' => $class_id, 'type'=>$type])->value('code_num');
            }
        }
		if(empty($type)){
			return  Db::name('user_code')->where(['user_id' => $uid, 'video_class_id' => $class_id])->value('code_num');
		}else{
			return  Db::name('user_code')->where(['user_id' => $uid, 'video_class_id' => $class_id, 'type'=>$type])->value('code_num');
		}
	}


	/**
	 *  给某个数组值作为键
	 */
	public function convert_arr_key($arr, $key_name) 
	{ 
		$arr2 = []; 
		foreach($arr as $key => $val){ 
			$arr2[$val[$key_name]] = $val;
		} 
		return $arr2; 
	} 
	public function convert_key($arr, $key_name) 
	{ 
		$arr2 = []; 
		foreach($arr as $key => $val){ 
			$arr2[$val[$key_name]][]= $val;
		} 
		return $arr2; 
	} 

	/**
	 * 获取年级课程的数量
	 * @param $grade_id 年级
	 * @param $semesterid 学期
	 * 
	 */
	public function getCourseSurplusNum($grade=7, $semester= 1, $subject=array(1, 2), $type=1)
	{
		$subject = Db::name('video_class')->field('id, grade_id, subject_id')->where(['grade_id' => $grade, 'Semester' => $semester, 'subject_id' => ['in', $subject]])->group('subject_id')->order('subject_id')->select();

		$subject = $this->convert_arr_key($subject, 'subject_id');

		if (empty($subject)) return ;
		// 获取键
		$subjectId = array_keys($subject);

		$courseName = Db::name('subject')->field('id, subject')->where(['id'=>['in', $subjectId]])->select();
	
		if (empty($subject)) return ;

		// 获取课程键值
		$uid = $_SESSION['think']['manageinfo']['uid'];

		foreach ($courseName as $key => $val) {
			$courseName[$key]['class_id'] = $subject[$val['id']]['id'];
			$courseName[$key]['courseNum'] = $this->getCourseNum($subject[$val['id']]['id'], $uid, $type);
			
			$courseName[$key]['isAudition'] = $this->getCourseNum($subject[$val['id']]['id'], $uid, 2);
		}
		
		return $courseName;

	}
	//获取同课程所有年级的数量
	public function getCourseSubNum($subject_id, $grade, $type=1)
	{
		$subject = Db::name('video_class')->alias('vc')
					->join('guard_subject s','s.id = vc.subject_id')
					->field('vc.id, grade_id, subject_id,Semester,s.subject')
					->where(['subject_id' => ['in', $subject_id], 'grade_id' => ['in', $grade]])
					->order('subject_id')
					->select();
        $pro_subject = Db::name('product')->alias('vc')
            ->join('guard_subject s','s.id = vc.subject_id')
            ->field('vc.id, grade_id, subject_id,Semester,s.subject,vc.productUrl')
            ->where(['subject_id' => ['in', $subject_id], 'grade_id' => ['in', $grade]])
            ->order('subject_id')
            ->select();
        $subject = array_merge($subject,$pro_subject);

		//判断是否为代理商			
		if($_SESSION['think']['manageinfo']['user_type'] != 1){
			$uid = $_SESSION['think']['manageinfo']['agent_id'];
		}else{
			$uid = $_SESSION['think']['manageinfo']['uid'];
		}
		foreach ($subject as $key => $val) {
            $productStatus=0;
		    if(isset($val['ptoductUrl'])){
		        $productStatus=1;
            }
			$classNum = $this->getCourseNum($val['id'], $uid, $type,$productStatus);
			if(empty($classNum)){
 				$classNum = 0;
			}
			$subject[$key]['classNum'] = $classNum;
		}
		$subject = $this->convert_key($subject, 'subject_id');
		return $subject;
	}
}