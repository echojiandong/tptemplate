<?php
namespace app\manage\controller;
use think\Controller;
use think\Model;
use think\Request;
use think\Session;
use think\Db;
use think\Image;
use think\Cache;
use vendor\phpoffic;
use app\manage\model\Person;
use app\manage\model\PersonSon;
use app\manage\model\OrderPerson;
use app\manage\model\Code;
class PersonController extends author
{
    private $act_status = array(
        '1' => '未激活',
        '2' => '已激活',
        '3' => '已禁用'
    );
    /**
     *用户列表页面
     *@author 韩春雷 2019.3.7
     */
	public function index()
	{
        // 我的用户为1； 默认为全部用户，不存在
        $type = input('type');
        
        $orderPerson = new OrderPerson();
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

        $group_id = $_SESSION['think']['manageinfo']['group_id'];
        $user_id  = $_SESSION['think']['manageinfo']['uid'];
        $user_type  = $_SESSION['think']['manageinfo']['user_type'];
        $agent_id  = $_SESSION['think']['manageinfo']['agent_id'];
        
        // 获取代理商
        if ($group_id == 1) {
            $userList = Db::name('user')->where(['user_type' => 1, 'status' => 1, 'group_id' => ['not in', [1, 2]]])->select();

        } elseif ($group_id == 2) {
            $userList = Db::name('user')->where(['user_type' => 1, 'status' => 1, 'group_id' => ['not in', [1, 2]]])->select();
        } else {
            //获取所有的子类代理商
            if ($user_type == 1) {
                $user_oid = $user_id;
            } else {
                $user_oid = $_SESSION['think']['manageinfo']['org_id'];
            }

            $userList = $orderPerson->getAllOrgSon($user_oid);
              // 获取自己的
            $myuser = Db::name('user')->where(['uid' => $user_id])->find();
            array_push($userList, $myuser);
        }

        $last_names = array_column($userList,'uid');
        array_multisort($last_names,SORT_ASC,$userList);
        // $userList = $this->resort($userList, $user_id);

        // 1为我的用户， 2为全部用户
        $type = !isset($type) ? 2 : $type; // 1获取我的用户

        if ($type != 1) {
            // 获取媒体
            $where = [];
            if ($group_id == 1 || $group_id == 2) {
                if ($group_id == 2) {
                    $uid = Db::name('user')->where(['group_id' => 1])->value('uid');
                    $where['agent_id'] = ['neq', $uid];
                }
            } else {
                if ($user_type == 1) {
                    $agent_id = $user_id;
                }
                $where['agent_id'] = $agent_id;
            }
        } else {
            if ($user_type == 1) {
                $agent_id = $user_id;
            }
            $where['agent_id'] = $agent_id;
        }
       
        // 媒体
        $medialist = Db::name('to_media')->where($where)->select();
        
        $this->assign('userList', $userList);
        $this->assign('type', $type);
        $this->assign('isAdmin', ($group_id == 1 || $group_id == 2) ? true : false);
        $this->assign('mediaList', $medialist);
		return $this->fetch("person/index");
    }

    public function resort($data,$parentid=0,$level=0){
        static $ret=array();
        foreach($data as $k=>$v){
            if($v['parent_id']==$parentid){
                $v['level']=$level;
                $ret[]=$v;
                $this->resort($data,$v['uid'],$level+1);
            }
        }
        return $ret;
    }

   
	/**
     *获取PersonList
     *@author 韩春雷 2019.3.19
     *@param  type    int  1:我的用户管理，2:全部用户管理， 3:代理商用户管理
     *@return [json]                  
     */

	public function getPersonList(Request $request)
	{
        $param = $request->param();
        $type = !isset($param['type']) ? 2 : $param['type']; // 1获取我的用户
        $whereOr = array();
        $where = array();
        if(array_key_exists('keyword',$param) && $param['keyword']!=''){
            $where['nickName|phone']=['like',"%".$param['keyword']."%"];
        }

        // 时间搜索优化
        $orderStartTime = isset($param['orderStartTime']) && !empty($param['orderStartTime']) ? strtotime($param['orderStartTime']) : '';
		$orderEndTime = isset($param['orderEndTime']) && !empty($param['orderEndTime']) ? strtotime($param['orderEndTime']) : '';

		if ($orderEndTime && $orderEndTime < $orderStartTime) {
			jsonMsg('结束时间不能小于开始时间', 1);
		} 
        if ($orderEndTime) {
			$orderEndTime = $orderEndTime+ 86400;
		}
		if ($orderEndTime && $orderStartTime && $orderEndTime >= $orderStartTime) {
			$where['addtime'] = ['between time', [$orderStartTime, $orderEndTime]];
		}elseif ($orderStartTime && !$orderEndTime) {
			$where['addtime']=['GT', $orderStartTime];
		} elseif($orderEndTime && !$orderStartTime) {
			$where['addtime']=['ELT', $orderEndTime];
		}

        // if(array_key_exists('orderStartTime',$param) && $param['orderStartTime']!=''){
        //     $where['addtime']=['GT', strtotime($param['orderStartTime'])];
        // }
        // if(array_key_exists('orderEndTime',$param) && $param['orderEndTime']!=''){
        //     $where['addtime']=['ELT', strtotime($param['orderEndTime'])];
        // }

        // 地区搜索
        if(array_key_exists('province', $param) && $param['province'] != '' && $param['province'] != '请选择省'){
            $where['province'] = $param['province'];
        }

        if(array_key_exists('city', $param) && $param['city'] != '' && $param['city'] != '请选择市'){
            $where['city'] = $param['city'];
        }

        if(array_key_exists('county', $param) && $param['county'] != '' && $param['county'] != '请选择区'){
            $where['country'] = $param['county'];
        }
        
        // 代理商查询
        if ($type != 1) {
            $uid = isset($param['uid']) && $param['uid'] ? $param['uid'] : '';
            if (isset($uid) && $uid) {
                if ($uid != -1) {
                    $where['user_id'] = $uid;
                } else {
                    $where['user_id'] = '';
                }
            }
        }

        // 判断是不是admin
        $isAdmin = isset($param['isAdmin']) && $param['isAdmin'] ? 1: 0;
        if ($isAdmin) {
            $where['isAdmin'] = 1;
        }

        // 用户学习状态查询
        if(array_key_exists('status', $param) && $param['status'] != ''){
            $where['status'] = (int) $param['status'];
        }

        // 用户状态查询
        if(array_key_exists('act_status', $param) && $param['act_status'] != ''){
            $where['act_status'] = (int) $param['act_status'];
        }

        // 媒体
        if(array_key_exists('to_media', $param) && $param['to_media'] != ''){
            $where['to_media'] = (int) $param['to_media'];
        }

        $page=$param['page'];
        $limit=$param['limit'];
        $person = new Person();
        
        $data =  $person->getPersonList($page,$limit, $where, $type);
        
        $count = $data['count'];
        unset($data['count']);
        
        // 判断当前用户是否有购买的课程
        foreach ($data as $key => $value) {
            // 判断用户学习状态
            switch($value['status']) {
                case '1' :
                    $statusTxt = '待试听';
                    break;
                case '2' :
                    $statusTxt = '已试听';
                    break;
                case '3' :
                    $statusTxt = '已下单';
                    break;
                case '4' :
                    $statusTxt = '已购买';
                    break;
                default:
                    break;
            }

            switch($value['act_status']) {
                case '1':
                case '2':
                    $actstatusTxt = '正常';
                    break;
                case '3' :
                    $actstatusTxt = '已禁用';
                    break;
                default:
                    $actstatusTxt = "未知";
                    break;
            }
            $data[$key]['statusTxt'] = $statusTxt;
            $data[$key]['actstatusTxt'] = $actstatusTxt;
            // 判断你是否有订单
            $isRenew = Db::name('order_person_son')->where(['person_id' => $value['id'], 'type' => ['neq', 0]])->count('id');
            $data[$key]['isRenew'] = $isRenew > 0 ? 1 : 0;

            $media = Db::name('to_media')->where(['id' => $value['to_media']])->value('name');
            $data[$key]['medianame'] = $media ? $media : '';
        }
        
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
     * 用户编辑页面
     * @author 杨继州 2019-07-03
     * 
     */
    public function editPerson(Request $request)
    {
        $id = $request->param('id');
        if (!$id) {
            jsonMsg('用户不存在', 1);
        }

        $group_id = $_SESSION['think']['manageinfo']['group_id'];
        $user_type = $_SESSION['think']['manageinfo']['user_type'];
        $agent_id = $_SESSION['think']['manageinfo']['agent_id'];
        $user_id  = $_SESSION['think']['manageinfo']['uid'];
        $person = new Person();
        $orderPerson = new OrderPerson();
        // 用户信息
        $personInfo = $person->getPersonInfoById($id);

        if (!$personInfo) {
            jsonMsg('无效用户', 1);
        }
        //获取管理员列表
        // $userList = Db::name('user')->select();

        // 获取代理商
        if ($group_id == 1) {
            $userList = Db::name('user')->where(['user_type' => 1])->select();
            // 判断用户所属代理商
            if ($personInfo['user_id']) {
                $personAgent = Db::name('user')->where(['uid' => $personInfo['user_id']])->value('agent_id');
                $personAgent = $personAgent ? $personAgent : 24;
                $this->assign('agent', $personAgent);
            }
        } else {
            //获取所有的子类代理商
            if ($user_type == 1) {
                $user_oid = $user_id;
            } else {
                $user_oid = $_SESSION['think']['manageinfo']['org_id'];
            }
            $userList = $orderPerson->getAllOrgSon($user_oid);
            $personInfos = Db::name('user')->where(['uid' => $user_id])->find();
            
            array_push($userList, $personInfos);
        }

        
        // 用户看客权限
        $personClassType = $orderPerson->getCourseByPersonId($personInfo['id']);
        $personInfo['courseAuth'] = $personClassType;
        if ($user_type == 1) {
            $agent_id = $user_id;
        } 
        // 媒体
        $medialist = Db::name('to_media')->where(['agent_id' => $agent_id])->select();
        // dump($userList);
        $this->assign('list',$personInfo);
        $this->assign('userList',$userList);
        $this->assign('mediaList', $medialist);
        $this->assign('isAdmin', $group_id == 1 ? true : false);

        return $this->fetch('person/editPerson');
    }

    /**
     *获取用户编辑页面
     *@author 韩春雷 2019.3.19
     */
    // public function editPerson(Request $request){
    //     $param = $request->param();
    //     $where['id'] = $param['id'];

    //     $personList = Db::name('person')->where($where)->select();


    //     //获取年级列表
    //     $gradeList = Db::name('grade')->select();
    //     // var_dump($personList);
    //     // $gradeAuth = explode(',',$personList[0]['gradeAuth']);

    //     //获取管理员列表
    //     $userList = Db::name('user')->select();
    //     $this->assign('list',$personList);
    //     $this->assign('userList',$userList);
    //     // $this->assign('gradeAuth',$gradeAuth);
    //     $this->assign('gradeList',$gradeList);
    //     return view('person/editPerson',['title'=>'用户信息预览']);
    // }
    /**
     *执行用户编辑
     *@author 韩春雷 2019.3.19
     *@return   [json]
     */
    public function editPersonal(Request $request)
    {
        $param = $request->param();

        $data  = array();
        $data['id'] = $param['id'];
        $data['nickName'] = !empty($param['nickName']) ? $param['nickName'] : '';
        // if(!preg_match('/^[A-Za-z]{1}[A-Za-z0-9_-]{3,10}$/',$data['nickName'] )){
        //     return jsonMsg('请输入3-10位的数字或者字母组合！',1);
        // }
        
        if (empty($param['phone'])) {
            jsonMsg('手机号码不能为空', 1);
        }
      
        $isPhone = checkphone($param['phone']);
        if (!$isPhone) {
            jsonMsg('手机号码格式有误', 1);
        }

        $data['birthday'] = !empty($param['birthday']) ? strtotime($param['birthday']) : time();
        $data['gender']   = !empty($param['gender']) ? $param['gender'] : '';
        $data['phone'] = !empty($param['phone']) ? $param['phone'] : '';
        // $data['email'] = !empty($param['email']) ? $param['email'] : '';
        $data['user_id'] = !empty($param['user_id']) ? $param['user_id'] : '';
        // $data['gradeAuth'] = !empty($param['gradeAuth']) ? $param['gradeAuth'] : '';
        $data['act_status'] = !empty($param['act_status']) ? $param['act_status'] : 1;

        $data['wechat'] = !empty($param['wechat']) ? $param['wechat'] : '';
        $data['school'] = !empty($param['school']) ? $param['school'] : '';
        $data['grade_id'] = !empty($param['grade_id']) ? $param['grade_id'] : '';

        $data['province']= (isset($param['province']) && $param['province'] != '请选择省') ? $param['province'] : '';
        $data['city']= (isset($param['city']) && $param['city'] != '请选择市') ? $param['city'] : '';
        $data['country']= (isset($param['county']) && $param['county'] != '请选择区') ? $param['county'] : '';
        $data['remark'] = (isset($param['remark']) && $param['remark']) ? $param['remark'] : '';
        $data['to_media'] = (isset($param['to_media']) && $param['to_media']) ? $param['to_media'] : '';

        $data['up_time'] = time();
        if(!empty($param['password'])){
            $data['password']= user_md5($param['password']);
        }
        //获取当前管理员信息
        $uid_now = $_SESSION['think']['manageinfo']['uid'];
        $group_id = $_SESSION['think']['manageinfo']['group_id'];
        $personInfo = Db::name('person')->where('id = '.$data['id'])->select();
        if(!empty($personInfo[0]['user_id'])){
            //获取父级管理员
            $userLsit = Db::name('user')->where('uid = '.$personInfo[0]['user_id'])->select();
            $uidList = array();
            $uidList[] = $personInfo[0]['user_id'];
            if(!empty($userLsit)){
                foreach ($userLsit as $key => $value) {
                    $uidList[] = $value['parent_id'];
                }
            }
            if(in_array($uid_now,$uidList) || $group_id == 1){
                $res = Db::name('person')->update($data);
                if($res){
                    return jsonMsg('修改成功',0);
                }else{
                    return jsonMsg('修改失败',1);
                }
            }else{
                return jsonMsg('您没有权限修改',1);
            }
        }elseif($group_id == 1){

            $res = Db::name('person')->update($data);
            if($res){
                return jsonMsg('修改成功',0);
            }else{
                return jsonMsg('修改失败',1);
            }
        }else{
            return jsonMsg('您没有权限修改',1);
        }
    }
    /**
    *禁用
    *@author  韩春雷 2019/3/8
    *@return   [json]
    */
    public function forbidden(Request $request)
    {
        $param=$request->param();
        $where['id'] = $param['id'];
        $data['act_status'] = 3;
        
        //获取当前管理员信息
        $uid_now = $_SESSION['think']['manageinfo']['uid'];
        $group_id = $_SESSION['think']['manageinfo']['group_id'];
        $user_type = $_SESSION['think']['manageinfo']['user_type'];
        $org_id = $_SESSION['think']['manageinfo']['org_id'];
        $personInfo = Db::name('person')->where(['id' => $param['id']])->find();
        if (!$personInfo) {
            jsonMsg('用户不存在', 1);
        }
        // admin 有无限开火权,代理商也有当前全县
        if ($group_id != 1 && $user_type != 1) {
            $person = new Person();
            // 获取当前用户部门下的所有员工
            $user = $person->getAllPersonSon($org_id);

            $userArr = array_column($user, 'uid');
            $userArr[] = $uid_now;
            // 查询当前员工下所有员工的用户
            $value = Db::name('person')->where(['user_id' => ['in', $userArr]])->column('id');
            if (!in_array($param['id'], $value)) {
                //获取父级管理员
                jsonMsg('没有权限', 1);
            }
        }
        
        $act_status = $personInfo['act_status'] == 3 ? 1 : 3;
        $res = Db::name('person')->where(['id' => $param['id']])->update(['act_status' => $act_status]);
        if ($res) {
            jsonMsg('修改成功', 0);
        } else {
            jsonMsg('修改失败', 1);
        }
    }

    /**
     * 解除用户禁用状态
     * @author 杨继州  2019-07-08
     */
    public function forban()
    {}
    /**
     *用户添加页面
     *@author 韩春雷 2019.3.19
     *@return   [json]
     */
    public function addPerson()
    {
        //获取年级列表
        $orderPerson = new OrderPerson();
        // 判断是否是管理员
        $group_id = $_SESSION['think']['manageinfo']['group_id'];
        
        $uid = $_SESSION['think']['manageinfo']['uid']; 
        
        $this->assign('isAdmin', $group_id == 1 ? true : false);

        // 查询课程列表
        $subject = Db::name('subject')->field('id, subject')->where('id','<',6)->whereOr('id','>',9)->select();
        $subject = convert_arr_key($subject, 'id');
        // 年级
        $grade = [7, 8, 9];
        // 学期
        $semester = [1, 2, 3];
        foreach ($subject as $key => $value) {
            foreach ($grade as $kl => $vl) {
                foreach ($semester as $k => $v) {
                    if (($vl == 7 || $vl == 8) && $v == 3) {
                        continue ;
                    }
                    if($key<=9){
                        // 判断课程是否存在
                        $videoClassId = Db::name('video_class')->where(['grade_id' => $vl, 'subject_id' => $key, 'Semester' => $v])->find();

                        if (!$videoClassId) {
                            // 当前年级的课程不存在，数据库灭有这个课程
                            $subject[$key][$vl][$v]['exist'] = 0;  // 该课程不存在年级学期中

                        } else {
                            $subject[$key][$vl][$v]['exist'] = 1; // 该课程存在
                            $subject[$key][$vl][$v]['audition'] = $orderPerson->getCourseNum($videoClassId['id'], 1); // 正式课数量
                            $subject[$key][$vl][$v]['noaudition'] = $orderPerson->getCourseNum($videoClassId['id'],2); // 试听课数量
                            $subject[$key][$vl][$v]['price'] = $videoClassId['price'] ? $videoClassId['price'] : 0; // 课程价格
                            $subject[$key][$vl][$v]['audit_price'] = !empty($videoClassId['audition_price']) ? $videoClassId['audition_price'] : 0;; // 试听课价格
                        }
                    }else{
                        // 判断课程是否存在
                        $productId = Db::name('product')->where(['grade_id' => $vl, 'subject_id' => $key, 'Semester' => $v])->find();

                        if (!$productId) {
                            // 当前年级的课程不存在，数据库灭有这个课程
                            $subject[$key][$vl][$v]['exist'] = 0;  // 该课程不存在年级学期中

                        } else {
                            $subject[$key][$vl][$v]['exist'] = 1; // 该课程存在
                            $subject[$key][$vl][$v]['audition'] = $orderPerson->getCourseNum($productId['id'], 1); // 正式课数量
                            $subject[$key][$vl][$v]['noaudition'] = $orderPerson->getCourseNum($productId['id'],2); // 试听课数量
                            $subject[$key][$vl][$v]['price'] = $productId['price'] ? $productId['price'] : 0; // 课程价格
                            $subject[$key][$vl][$v]['audit_price'] = !empty($productId['audition_price']) ? $productId['audition_price'] : 0;; // 试听课价格
                        }
                    }
                }
            }
        }

        // 获取当前用户的代理id，是代理则返回置身uid
        $user = Db::name('user')->where(['uid' => $uid])->find();
        if ($user['user_type'] == 1) {
            $agent_id = $user['uid'];
        } else {
            $agent_id = $user['agent_id'];
        }
        $paytype = Db::name('paytype')->where(['agent_id' => $agent_id])->select();
        // 媒体
        $medialist = Db::name('to_media')->where(['agent_id' => $agent_id])->select();

        // 试听剩余数量
        $audiNum = $orderPerson->getAudiNum();

        $this->assign('paytypelist', !empty($paytype) ? $paytype : array());
        $this->assign('isBool', false);
        $this->assign('list', $subject);
        $this->assign('info', array());
        $this->assign('personStatus', 1);
        $this->assign('mediaList', $medialist);
        $this->assign('audiNum', $audiNum);

    //   // 获取当前管理代理商的课程剩余数量
    //   $code = new Code();
    //   // 七年级
    //   $sevenGradeUpperSem = $code->getCourseSurplusNum($grade = 7, $semester=1, $array = array(1, 2, 3));
    //   $sevenGradeLowerSem = $code->getCourseSurplusNum($grade = 7, $semester=2, $array = array(1, 2, 3));
    //   // 八年级
    //   $eightGradeUpperSem = $code->getCourseSurplusNum($grade = 8, $semester=1, $array = array(1, 2, 3, 4));
    //   $eightGradeLowerSem = $code->getCourseSurplusNum($grade = 8, $semester=2, $array = array(1, 2, 3, 4));
    //   // 九年级
    //   $nineGradeUpperSem = $code->getCourseSurplusNum($grade = 9, $semester=1, $array = array(1, 2, 3, 4, 5));
    //   $ninenGradeLowerSem = $code->getCourseSurplusNum($grade = 9, $semester=2, $array = array(1, 2, 3, 4, 5));
     
    //   $this->assign('sevenGradeUpperSem', $sevenGradeUpperSem);
    //   $this->assign('sevenGradeLowerSem', $sevenGradeLowerSem);
    //   $this->assign('eightGradeUpperSem', $eightGradeUpperSem);
    //   $this->assign('eightGradeLowerSem', $eightGradeLowerSem);
    //   $this->assign('nineGradeUpperSem', $nineGradeUpperSem);
    //   $this->assign('ninenGradeLowerSem', $ninenGradeLowerSem);

        return view('person/addPerson',['title'=>'用户添加']); 
    }


    /**
     * 用户添加
     * @author 杨继州 2019-07-03
     */
    public function doAddPerson(Request $request)
    {
        $orderPerson = new OrderPerson();
        $param = $request->param();

        $user_id = $_SESSION['think']['manageinfo']['uid'];
        $group_id = $_SESSION['think']['manageinfo']['group_id'];
        $phone = $param['phone'];
        // $email = $param['email'];
        $nickName = $param['nickName'];
       
        $password = (isset($param['password']) && !empty($param['password'])) ? $param['password'] : substr($phone, -6);
        
        $birthday = !empty($param['birthday']) ? strtotime($param['birthday']) : time();


        $subject_id = isset($param['subject_id']) && !empty($param['subject_id']) ? $param['subject_id'] : array();
        if (empty($phone)) {
            jsonMsg('手机号码不能为空', 1);
        }
        if (empty($param['grade_id'])) {
            jsonMsg('年级不能为空', 1);
        }
        // if(!preg_match('/^[A-Za-z]{1}[A-Za-z0-9_-]{3,10}$/',$nickName )){
        //     jsonMsg('请输入3-10位的数字或者字母组合账号！',1);
        // }
        $isPhone = checkphone($phone);
        if (!$isPhone) {
            jsonMsg('手机号码格式有误', 1);
        }

        $userPerson = Db::name('person')->where(['phone' => $phone])->find();
        
        // 当前用户只有他的代理可以给下单
        // if ($userPerson) {
        //     if ($userPerson['user_id']) {
        //         $user_type = $_SESSION['think']['manageinfo']['user_type'];
        //         $agent_id = $_SESSION['think']['manageinfo']['agent_id'];
        //         $org_id = $_SESSION['think']['manageinfo']['org_id'];
        //         if ($user_type == 1) {
        //             // 代理
        //             // 获取代理下的所有员工
        //             $allUser = Db::name('user')->where(['user_type' => 2, 'agent_id' => $user_id])->column('uid');
        //             $allUser[] = $user_id;
        //         } else {
        //             // 员工
        //             $allUser = $orderPerson->getAllOrgSon($org_id);
        //             $allUser = array_column($allUser, 'uid');
        //             $allUser[] = $user_id;
        //         }
                
        //         if (!in_array($userPerson['user_id'], $allUser)) {
        //             jsonMsg('该账号已有所属客服，不能下单', 1);
        //         }
        //     } else {
        //         $data['user_id'] = $user_id;
        //     }
        // }

        // if ($userPerson) {
        //     jsonMsg('该手机号已注册', 1);
        // }

        // $isEmail = checkemail($email);
        // if (!$isEmail) {
        //     jsonMsg('邮箱格式有误', 1);
        // }

        // if ($birthday && $birthday > (time() - (365 * 24 * 60 * 60))) {
        //     jsonMsg('用户年龄必须大于一岁', 1);
        // } 

        // 是否选择了试听课
        $isaudi = isset($param['isAudi']) ? 1 : 0;
        $audiPrice = isset($param['audiPrice']) ? $param['audiPrice'] : 0;


        $dicount = (float) (isset($param['dicount']) && !empty($param['dicount'])) ? $param['dicount'] : 0;
        $payMoney = (float) (isset($param['final']) && !empty($param['final'])) ? $param['final'] : 0;
        $money = (float) (isset($param['totalPrice']) && !empty($param['totalPrice'])) ? $param['totalPrice'] : 0;
        // 判断订单金额，实际支付金额是否正确
        $payMoney1 = $money - $dicount;
//        if ($payMoney != $payMoney1) {
            $payMoney = $payMoney1;
//        }
        
        $data['nickName'] = $nickName;
        $data['birthday'] = $birthday;
        $data['gender']   = isset($param['gender']) && !empty($param['gender']) ? $param['gender'] : 1;

        $data['phone'] = $phone;
        // $data['email'] = $email;
        
        $data['user_id'] = $user_id;
        $data['addtime'] = time();
        $data['password'] = user_md5($password);

        $data['wechat'] = isset($param['wechat']) ? $param['wechat'] : '';
        $data['grade_id'] = isset($param['grade_id']) ? $param['grade_id'] : '';
        $data['school'] = isset($param['school']) ? $param['school'] : '';
        $data['province']= (isset($param['province']) && $param['province'] != '请选择省') ? $param['province'] : '';
        $data['city']= (isset($param['city']) && $param['city'] != '请选择市') ? $param['city'] : '';
        $data['country']= (isset($param['county']) && $param['county'] != '请选择区') ? $param['county'] : '';
        $data['remark'] = (isset($param['remark']) && $param['remark']) ? $param['remark'] : '';
        $to_media = (isset($param['to_media']) && $param['to_media']) ? $param['to_media'] : '';
        $data['to_media'] =$to_media;
        // 获取代理id：说明如果当前登录用户属于代理，则返回当前用户id，如果不是反回最近的上级代理ID
        $agendId = $orderPerson->judgeIsAgent($user_id);
        if ($group_id != 1) {
            if (!$agendId) {
                jsonMsg('该用户或所属代理不存在', 1);
            }
        }

        ///支付方式处理
        $paytype = isset($param['paytype']) && !empty($param['paytype']) ? $param['paytype'] : array();
        $paytypedata = array();
        $m = 0;
        if ($paytype) {
            foreach ($paytype as $key => $value) {
                if (isset($value['id']) && !empty($value['id']) && !empty($value['money'])) {
                    $paytypedata[$m]['pay_type_id'] = $value['id'];
                    $paytypedata[$m]['money'] = $value['money'];
                } 
                $m++;
            }
        }

        Db::startTrans();
        try {
            if ($userPerson) {
                if (!isset($param['password']) || empty($param['password'])) {
                    unset($data['password']);
                }
                unset($data['addtime']); 
                unset($data['user_id']);
                $data['up_time'] = time();
                // 修改
                Db::name('person')->where(['id' => $userPerson['id']])->update($data);
                $person_id = $userPerson['id'];
            } else {
                // 新增
                $person_id = Db::name('person')->insertGetId($data);

                // 注册发送一条用户注册消息
                $title = '用户注册消息提示';
                $content = config('message')['registerMsg'];
                insertMessage($person_id, $title, $content);
            }

            if ($subject_id) {
                // 禁用的用户不能下单
                
                if ($userPerson && $userPerson['act_status'] == 3) {
                    jsonMsg('该账号被禁用，不能下单', 1);
                }
                // 处理subject数据
                $subject = $this->checkedSubjectData($subject_id);
                // 选择有课程
                // $payMoney = 0; // 订单实际支付金额
                // $money = 0; // 订单金额
                $orderSon = array();
                foreach ($subject as $key => $value) {
                    //如果是产品，学科就大于9
                    if($value['subject_id']>9){
                        $res = Db::name('product')->where(['grade_id' => $value['grade_id'], 'Semester' => $value['semester'], 'subject_id' => $value['subject_id']])->find();
                    }else{
                        $res = Db::name('video_class')->where(['grade_id' => $value['grade_id'], 'Semester' => $value['semester'], 'subject_id' => $value['subject_id']])->find();
                    }

                    if ($res) {
                        // $payMoney +=  empty($res['Discount']) ? ($res['price']) : ($res['price'] * $res['Discount']);
                        // $money +=  $res['price'];
                        // 除了超级管理员有无限生成
                        if ($group_id != 1) {
                            // 判断user_code 是否还有剩余数量
                            // 判断user_code 是否还有剩余数量
                            if ($value['isAudition'] == 1) {
                                $isAudition = 2;
                            } elseif ($value['isAudition'] == 0) {
                                $isAudition = 1;
                            }
                            if($value['subject_id']>9){
                                $supler = Db::name('user_code')->where(['user_id' => $agendId, 'product_id' => $res['id'], 'type' => $isAudition])->value('code_num');
                            }else{
                                $supler = Db::name('user_code')->where(['user_id' => $agendId, 'video_class_id' => $res['id'], 'type' => $isAudition])->value('code_num');
                            }

                            if ($supler < 1) {
                                $msg = '您的'. $res['name'] . (($res['Semester'] == 1) ? '上学期' : '下学期'). (($value['isAudition'] == 1) ? '试听课权限' : '正常课权限') .'剩余量不足！';
                                jsonMsg($msg, 1);
                            }
                        }
                        if($value['subject_id']>9){
                            $orderSon[$key]['product_id'] = $res['id'];
                        }else{
                            $orderSon[$key]['video_class_id'] = $res['id'];
                        }
                        $orderSon[$key]['num'] = 1;
                        $orderSon[$key]['is_audition'] = $value['isAudition'];
                        $orderSon[$key]['person_id'] = $person_id;
                        $orderSon[$key]['money'] = $res['price'];
                        $orderSon[$key]['payMoney'] = empty($res['Discount']) ? ($res['price']) : ($res['price'] * $res['Discount']);
                        $orderSon[$key]['order_type'] = 2;
                        // 判断是否有购买

                        $isBuy = $this->isBuyByVideoId($res['id'], $person_id,$value['subject_id']);
                        if ($isBuy && $isBuy['is_audition'] != 1) {
                            $msg = '您的'. $res['name'] . (($res['Semester'] == 1) ? '上学期' : '下学期').'课程已购买！';
                            jsonMsg($msg, 1);
                        } elseif ($isBuy && ($isBuy['is_audition'] == 0) && $value['isAudition'] == 0) {
                            $msg = '您的'. $res['name'] . (($res['Semester'] == 1) ? '上学期' : '下学期').'试听课程已购买！';
                            jsonMsg($msg, 1);
                        }
                    } else {
                        jsonMsg('购买的课程不存在', 1);
                    }
                }

                // 判断是否选择试听
                if ($isaudi) {
                    $personOrder = Db::name('order_person_son')->where(['person_id' => $person_id, 'is_audition' => 1])->find();
                    if ($personOrder) {
                        $time = strtotime($personOrder['endtime']) * 210 * 24 * 3600;
                        if ($time < time()) {
                            jsonMsg('该账号已购买试听课程', 1);
                        }
                    }

                    $orderSon[] = [
                        'num' => 1,
                        'is_audition' => 1, 
                        'person_id' => $person_id,
                        'money' => $audiPrice,
                        'payMoney' => $audiPrice,
                        'order_type' => 2
                    ];
                }

                $info = new OrderPerson;
                $orderid = time().mt_rand(100000,999999);
                $order['order'] = $orderid;
                // $order['money']  = $money;
                // $order['payMoney'] = $payMoney;
                
                $order['money']  = $money;
                $order['payMoney'] = $payMoney;
                $order['person_id'] = $person_id;
                $order['strtime'] = date('Y-m-d H:i:s', time());
                
                $order['user_id'] = $user_id;
                $order['order_type'] = 2;
                $order['discount_price'] = $dicount; // 订单优惠金额
                $order['to_media'] = $to_media;
                
                $res = Db::name('order_person')->insert($order);

                if($res) {
                    foreach ($orderSon as $key => $value) {
                        $orderSon[$key]['order_id'] = $orderid;
                        $orderSon[$key]['order_son_id'] = time().mt_rand(100000,999999);
                        $orderSon[$key]['strtime'] = date('Y-m-d H:i:s', time());
                        // 把试听课程生成video_log表
                        // if ($value['is_audition'] == 1) {
                        //     $orderSon[$key]['orderCheck'] = 1;
                        //     $orderSon[$key]['state'] = 2;
                        //     $orderSon[$key]['type'] = 1;
                        //     setVideoLog($value['video_class_id'], $person_id);
                        // }

                        Db::name('order_person_son')->insert($orderSon[$key]);

                    }
//                   Db::name('order_person_son')->fetchSql()->insertAll($orderSon);

                    // 除了超级管理员有无限生成
                    if ($group_id !== 1) {
                        foreach ($orderSon as $key => $value) {
                            if ($value['is_audition'] == 1) {
                                Db::name('user_code')->where(['user_id' => $agendId,  'type' => 2])->setDec('code_num');
                            } else {
                                $is_audition = $value['is_audition'] == 1 ? 2 : 1;
                                Db::name('user_code')->where(['user_id' => $agendId, 'video_class_id' => $value['video_class_id'], 'type' => $is_audition])->setDec('code_num');
                            }
                            
                        }
                    }
                } 

                if ($paytypedata) {
                    foreach ($paytypedata as $key => $value) {
                        $paytypedata[$key]['order_id'] = $orderid;
                    }

                    Db::name('order_person_paytype')->insertAll($paytypedata);
                }

                if ($orderSon) {
                    // 修改的课程video_class_id 
                    $videoClassId = array_column($orderSon, 'video_class_id');
                    $pruductId = array_column($orderSon, 'product_id');
                    if(isset($pruductId) && !empty($pruductId)){
                        Db::name('product')->where(['id' => ['in', $pruductId]])->setInc('purchase');
                    }
                    if(isset($videoClassId) && !empty($videoClassId)){
                        Db::name('video_class')->where(['id' => ['in', $videoClassId]])->setInc('purchase');
                    }
                }

                // 修改用户状态为已下单
                Db::name('person')->where(['id' => $person_id])->update(['status' => 3]);
            }
            elseif (!$subject_id && $isaudi) {
                // 只有购买试听课
                if ($userPerson && $userPerson['act_status'] == 3) {
                    jsonMsg('该账号被禁用，不能下单', 1);
                }
                
               
                $personOrder = Db::name('order_person_son')->where(['person_id' => $person_id, 'is_audition' => 1])->find();
                if ($personOrder) {
                    $time = strtotime($personOrder['endtime']) * 210 * 24 * 3600;
                    if ($time < time()) {
                        jsonMsg('该账号已购买试听课程', 1);
                    }
                }


                $orderid = time().mt_rand(100000,999999);
                $order['order'] = $orderid;
                // $order['money']  = $money;
                // $order['payMoney'] = $payMoney;
                
                $order['money']  = $audiPrice;
                $order['payMoney'] = $payMoney;
                $order['person_id'] = $person_id;
                $order['strtime'] = date('Y-m-d H:i:s', time());
                
                $order['user_id'] = $user_id;
                $order['order_type'] = 2;
                $order['discount_price'] = $dicount; // 订单优惠金额
                $order['to_media'] = $to_media;
                
                $res = Db::name('order_person')->insert($order);

                // 判断是否选择试听
                $orderSon = [
                    'num' => 1,
                    'order_id' => $orderid,
                    'is_audition' => 1, 
                    'person_id' => $person_id,
                    'money' => $audiPrice,
                    'payMoney' => $payMoney,
                    'order_type' => 2,
                    'order_son_id' => time().mt_rand(100000,999999),
                    'strtime' => date('Y-m-d H:i:s', time())
                ];
                Db::name('order_person_son')->insert($orderSon);
                Db::name('user_code')->where(['user_id' => $agendId,  'type' => 2])->setDec('code_num');
            }

            // 提交事务
            Db::commit(); 
            
           jsonMsg('提交成功',0);
        }
        catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // return $this->error($e->getMessage());
            jsonMsg('提交失败', 1);
        }


        // $person_id = Db::name('person')->insertGetId($data);

        // if (!$person_id) {
        //     jsonMsg('添加失败', 1);
        // }

        // if ($subject_id) {
        //     // 处理subject数据
        //     $subject = $this->checkedSubjectData($subject_id);
        //     // 选择有课程
        //     $payMoney = 0; // 订单实际支付金额
        //     $money = 0; // 订单金额
        //     $orderSon = array();
        //     foreach ($subject as $key => $value) {
        //         $res = Db::name('video_class')->where(['grade_id' => $value['grade_id'], 'Semester' => $value['semester'], 'subject_id' => $value['subject_id']])->find();
                
        //         if ($res) {
        //             $payMoney +=  empty($res['Discount']) ? ($value['num'] * $res['price']) : ($value['num'] * $res['price'] * $res['Discount']);
        //             $money +=  $value['num'] * $res['price'];
                    
        //             // 除了超级管理员有无限生成
        //             if ($group_id !== 1) {
        //                 // 判断user_code 是否还有剩余数量
        //                 $supler = Db::name('user_code')->where(['user_id' => $user_id, 'video_class_id' => $value['video_class_id'], 'type' => $value['isAudition']])->value('code_num');
        //                 if ($supler < 1) {
        //                     $msg = '您的'. $res['name'] . (($res['Semester'] == 1) ? '上学期' : '下学期'). (($value['isAudition'] == 1) ? '试听课权限' : '正常课权限') .'剩余量不足！';
        //                     jsonMsg($msg, 1);
        //                 }
        //             }

        //             $orderSon[$key]['video_class_id'] = $res['id'];
        //             $orderSon[$key]['num'] = $value['num'];
        //             $orderSon[$key]['is_audition'] = $value['isAudition'];
        //             $orderSon[$key]['person_id'] = $person_id;
        //             $orderSon[$key]['money'] = $value['num'] * $res['price'];
        //             $orderSon[$key]['payMoney'] = empty($res['Discount']) ? ($value['num'] * $res['price']) : ($value['num'] * $res['price'] * $res['Discount']);
        //             $orderSon[$key]['order_type'] = 2;

        //             // 判断是否有购买
        //             $isBuy = $this->isBuyByVideoId($res['id'], $person_id, $value['isAudition']);
        //             if ($isBuy && $isBuy['is_audition'] != 1) {
        //                 $msg = '您的'. $res['name'] . (($res['Semester'] == 1) ? '上学期' : '下学期').'课程已购买！';
        //                 jsonMsg($msg, 1);
        //             } elseif ($isBuy && ($isBuy['is_audition'] == 0) && $value['isAudition'] == 0) {
        //                 $msg = '您的'. $res['name'] . (($res['Semester'] == 1) ? '上学期' : '下学期').'试听课程已购买！';
        //                 jsonMsg($msg, 1);
        //             }
        //         } else {
        //             jsonMsg('购买的课程不存在', 1);
        //         }
        //     }

        //     $info = new OrderPerson;
        //     $orderid = time().mt_rand(100000,999999);
        //     $order['order'] = $orderid;
        //     $order['money']  = $money;
        //     $order['person_id'] = $person_id;
        //     $order['strtime'] = date('Y-m-d H:i:s', time());
        //     $order['payMoney'] = $payMoney;
        //     $order['user_id'] = $user_id;
        //     $order['order_type'] = 2;
        //     $res = $info->save($order);
        //     if($res) {
        //         foreach ($orderSon as $key => $value) {
        //             $orderSon[$key]['order_id'] = $orderid;
        //             $orderSon[$key]['order_son_id'] = time().mt_rand(100000,999999);
        //             $orderSon[$key]['strtime'] = date('Y-m-d H:i:s', time());
        //         }
        
        //         Db::name('order_person_son')->insertAll($orderSon);
                
        //         // 除了超级管理员有无限生成
        //         if ($group_id !== 1) {
        //             foreach ($orderSon as $key => $value) {
        //                 Db::name('user_code')->where(['user_id' => $data['user_id'], 'video_class_id' => $value['video_class_id']])->setDec('code_num');
        //             }
        //         }
        //         return jsonMsg('添加成功',0);
        
        //       } else {
        //         return jsonMsg('添加失败', 1);
        //       }
        // }

        // jsonMsg('添加成功', 0);
    }

    /**
     * 课程年级学期数据处理
     */
    public function checkedSubjectData($data)
    {
        if (empty($data)) {
            return array();
        }
        // 接收数据处理
        $arr = array();
        foreach ($data as $key => $value) {
            foreach ($value as $kl => $vl) {
                foreach ($vl as $k => $v) {

                    if (isset($v['isAudition'])) {
                        if ($v['isAudition'] == 1) {
                          // 试听
                          $isAudition = 1;
                        } elseif ($v['isAudition'] == 0) {
                          // 所有课程
                          $isAudition = 0;
                        }
                      } else {
                        continue ;
                      }

                    $arr[] = [
                        'grade_id' => $key,
                        'semester' => $kl,
                        'subject_id' => $k,
                        'isAudition' => $isAudition
                    ];

                }
            }
        }

        return $arr ? $arr : array();
    }

    /**
   * 判断是否已购买
   */
    public function isBuyByVideoId($video_class_id, $person_id,$subject_id)
    {
        if($subject_id>9){
            $res = Db::name('order_person_son')->where(['product_id' => $video_class_id, 'person_id' => $person_id, 'is_forbidden' => 0])->whereNotIn('orderCheck',[4])->find();
        }else{
            $res = Db::name('order_person_son')->where(['video_class_id' => $video_class_id, 'person_id' => $person_id, 'is_forbidden' => 0])->whereNotIn('orderCheck',[4])->find();
        }
        return $res;
    }
    /**
     *执行用户添加
     *@author 韩春雷 2019.3.19
     *@return   [json]
     */
    // public function doAddPerson(Request $request){
    //     $group_id = $_SESSION['think']['manageinfo']['group_id'];
    //     if($group_id == 1 || $group_id == 5 || $group_id == 6 || $group_id == 8 || $group_id == 9){
    //         $param = $request->param();
    //         $data  = array();
    //         $data['nickName'] = !empty($param['nickName']) ? $param['nickName'] : '';
    //         if(!preg_match('/^[A-Za-z]{1}[A-Za-z0-9_-]{3,10}$/',$data['nickName'] )){
    //             return jsonMsg('请输入3-10位的数字或者字母组合！',1);
    //         }
    //         $data['birthday'] = !empty($param['birthday']) ? strtotime($param['birthday']) : '';
    //         $data['gender']   = !empty($param['gender']) ? $param['gender'] : '';
    //         $data['phone'] = !empty($param['phone']) ? $param['phone'] : '';
    //         $data['email'] = !empty($param['email']) ? $param['email'] : '';
    //         // $data['gradeAuth'] = !empty($param['gradeAuth']) ? $param['gradeAuth'] : '';
    //         $data['act_status'] = !empty($param['act_status']) ? $param['act_status'] : '';
    //         $data['user_id'] = $_SESSION['think']['manageinfo']['uid'];
    //         $data['addtime'] = date('Y-m-d H:i:s',time());
    //         if(!empty($param['password'])){
    //             $data['password']= user_md5($param['password']);
    //         }
    //         //转换变量
    //         // if($data['gradeAuth']){
    //         //     if(count($data['gradeAuth']) > 1){
    //         //         $data['gradeAuth'] = implode(',',$data['gradeAuth']);
    //         //     }else{
    //         //         $data['gradeAuth'] = $data['gradeAuth'][0];
    //         //     }
    //         // }
    //         $info = new person();
    //         $res=$info->save($data);
    //         if($res){
    //             return jsonMsg('添加成功',0);
    //         }else{
    //             return jsonMsg('添加失败',1);
    //         }
    //     }else{
    //          return jsonMsg('您没有权限添加',1);
    //     }
    // }
    /**
     *子用户添加页面
     *@author 韩春雷 2019.3.27
     */
    public function addPersonSon(Request $request){
        $param = $request->param();
        //获取年级列表
        $gradeList = Db::name('grade')->select();

        $this->assign('gradeList',$gradeList);
        $this->assign('id',$param['id']);


        return view('person/addPersonSon',['title'=>'子用户添加']); 
    }
    /**
     *执行子用户添加
     *@author 韩春雷 2019.3.27
     *@return   [json]
     */
    public function doAddPersonSon(Request $request){
        $group_id = $_SESSION['think']['manageinfo']['group_id'];
        if($group_id == 1 || $group_id == 5 || $group_id == 6 || $group_id == 8 || $group_id == 9){
            $param = $request->param();
            $data  = array();
            $data['person_id'] = !empty($param['id']) ? $param['id'] : '';
            $data['son_nickName'] = !empty($param['nickName']) ? $param['nickName'] : '';
            $data['son_birthday'] = !empty($param['birthday']) ? strtotime($param['birthday']) : '';
            $data['son_gender']   = !empty($param['gender']) ? $param['gender'] : '';
            $data['grade_id'] = !empty($param['grade_id']) ? $param['grade_id'] : '';
            $data['school'] = !empty($param['school']) ? $param['school'] : '';
            $data['son_addtime'] = date('Y-m-d H:i:s',time());
            if(!empty($param['password'])){
                $data['son_password']= user_md5($param['password']);
            }
            //判断用户是否添加过子级账号
            $result = Db::name('person_son')->where('person_id = '.$data['person_id'])->select();
            if(empty($result)){
                $info = new PersonSon();
                $res=$info->save($data);
                if($res){
                    return jsonMsg('添加成功',0);
                }else{
                    return jsonMsg('添加失败',1);
                }
            }else{
                return jsonMsg('每个账号只能添加一个子账户',1);
            }
        }else{
             return jsonMsg('您没有权限添加',1);
        }
    }

    /**
     * 用户详情信息
     * @author 杨继州 2019-07-03
     * 
     */
    public function personInfo(Request $request)
    {
        $id = (int) $request->param('id');
       
        if (!$id) {
            jsonMsg('无效用户数据', 1);
        }
        $person = new Person();
        $orderPerson = new OrderPerson();
        // 用户信息
        $personInfo = $person->getPersonInfoById($id);
        
        if (!$personInfo) {
            jsonMsg('无效用户', 1);
        }
        // 用户看客权限
        $personClassType = $orderPerson->getCourseByPersonId($personInfo['id']);
        $personInfo['courseAuth'] = $personClassType;
       

        $this->assign('list', $personInfo);
       
        return $this->fetch('person/personInfo');
    }

    /**
     * 试听记录
     */
    public function getPersonAuditionLog()
    {
        $personId = input();
        $id = $personId['person_id'];
        // 获取试听记录
        $audition = Db::name('audition_log')->alias('a')
                ->field('a.*, b.outline, c.name, c.Semester')
                ->join('guard_video b', 'a.video_id = b.id')
                ->join('guard_video_class c', 'a.video_class_id = c.id')
                ->where(['a.person_id' => $id])
                ->order('a.create_time desc')

                ->select();
        if ($audition) {
            foreach ($audition as $key => $value) {
                // if (!$value['update_time']) {
                //     $audition[$key]['update_time'] = date('Y-m-d H:i:s', $value['create_time']);
                // } else {
                //     $audition[$key]['update_time'] = date('Y-m-d H:i:s', $value['update_time']);
                // }
                $audition[$key]['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
                $semester = ($value['Semester'] == 1) ? '上册' : (($value['Semester'] == 2) ? '下册' : '全册'); 
                $audition[$key]['name'] = $value['name'].'-'.$semester;
                $time = 0;
                // 处理学习时长数据
                if (strstr($value['study_time'], ':')) {
                    $strtime = explode(':', $value['study_time']);

                    $strcount = count($strtime);
                    switch ($strcount) {
                        case '2':
                            $time = (int) $strtime[0] * 60 + (int) $strtime[1];
                            break;
                        case '3':
                            $time = (int) $strtime[0] * 60 * 60 + (int) $strtime[1] * 60 + (int) $strtime[2];
                            break;
                        default:
                            break;
                    }
                } else {
                    $time = ceil($value['study_time']);
                }

                $hour = floor($time/3600);
                $second = $time%3600;//除去整小时之后剩余的时间 
                $minute = floor($second/60);
                $second = $second%60;//除去整分钟之后剩余的时间 

                if ($minute && $minute < 10) {
                    $minute = '0'.$minute;
                }
                if ($second && $second < 10) {
                    $second = '0'.$second;
                }
                $audition[$key]['study_time'] = ($hour ? $hour : '00') . ':' .($minute ? $minute : '00') .':'. ($second ? $second : '00');
            }
        }
        $res = array(
            'code'=>0,
            'msg'=>'success',
            'data'=>$audition
            );
        echo json_encode($res);
        
    }
    /**
     *查看用户详情页面
     *@author 韩春雷 2019.3.27
     */
    // public function personInfo(Request $request){
    //     $param = $request->param();
    //     //获取用户信息
    //     $personInfo = Db::name('person')->alias('p')
    //                     ->join('guard_person_son ps','p.id = ps.person_id','LEFT')
    //                     ->where('p.id = '.$param['id'])
    //                     ->field('p.*,ps.*')
    //                     ->select();
    //     $personInfo = person::changPersonList($personInfo);
    //     //获取当前用户的看课权限
    //     $codeList = Db::name('code')->where('person_id = '.$param['id'].' and status = 2')->select();
    //     $personVider = Db::name('person_video')->where('person_id = '.$param['id'])->select();
    //     $videoClassId = '';
    //     if($codeList){
    //         foreach ($codeList as $key => $value) {
    //             $videoClassId .= ','.$value['coursePackage_id'];
    //         }
    //     }
    //     if($personVider){
    //         foreach ($personVider as $key => $value) {
    //             $videoClassId .= ','.$value['class_id'];
    //         }
    //     }
    //     $classInfo =array();
    //     if($videoClassId){
    //         $videoClassId =strtr(trim($videoClassId,','),'，',',');
    //         $videoClassId = explode(',',$videoClassId);
    //         for($i=0; $i <count($videoClassId) ; $i++) { 
    //             $classList = Db::name('video_class')
    //                                 ->alias('vc')
    //                                 ->where('vc.id='.$videoClassId[$i])
    //                                 ->join('guard_grade g','g.id = vc.grade_id')
    //                                 ->join('guard_subject s','s.id = vc.subject_id')
    //                                 ->field('g.grade,s.subject,vc.Semester')
    //                                 ->select();
    //             foreach ($classList as $ke => $val) {
    //                 $classList[$ke]['Semester'] = $val['Semester'] == 1 ? '上':'下';
    //                 $classInfo[$i] = $classList[$ke]['grade'].'-'.$classList[$ke]['subject'].'-'.$classList[$ke]['Semester'];
    //             }
    //         }
    //     }
    //     $personInfo[0]['videoClass'] = implode(',',$classInfo);
    //     $this->assign('list',$personInfo);
    //     return view('person/personInfo',['title'=>'子用户添加']);
    // }
    /**
     *子用户列表页面
     *@author 韩春雷 2019.3.27
     */
    public function personSon()
    {
        return $this->fetch("person/personSonList");
    }
    /**
     *获取PersonSonList
     *@author 韩春雷 2019.3.27
     *@return [json]                  
     */
    public function getPersonSonList(Request $request)
    {
        $param = $request->param();
        $where = '';
        $where .= "where p.act_status != 3";
        if(array_key_exists('keyword',$param) && $param['keyword']!=''){
            $where .= " and p.nickName like '%".$param['keyword']."%' or ps.son_nickName like '%".$param['keyword']."%'";
        }else{
            $where .= '';
        }
        $page=$param['page'];
        $limit=$param['limit'];
        $data =  personSon::getPersonSonList($where,$page,$limit);
        $count = $data['count'];
        unset($data['count']);
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
     *获取子用户编辑页面
     *@author 韩春雷 2019.3.27
     */
    public function editPersonSon(Request $request){
        $param = $request->param();
        $where['id'] = $param['id'];

        $personList = Db::name('personSon')->where($where)->select();


        //获取年级列表
        $gradeList = Db::name('grade')->select();

        $this->assign('list',$personList);
        $this->assign('gradeList',$gradeList);
        return view('person/editPersonSon',['title'=>'子用户编辑']);
    }
    /**
     *执行子用户编辑
     *@author 韩春雷 2019.3.27
     *@return [json]
     */
    public function doEditPersonSon(Request $request){
        $param = $request->param();
        $data  = array();
        $data['id'] = $param['id'];
        $data['son_nickName'] = !empty($param['son_nickName']) ? $param['son_nickName'] : '';
        $data['son_birthday'] = !empty($param['son_birthday']) ? strtotime($param['son_birthday']) : '';
        $data['son_gender']   = !empty($param['son_gender']) ? $param['son_gender'] : '';
        $data['school'] = !empty($param['school']) ? $param['school'] : '';
        $data['grade_id'] = !empty($param['grade_id']) ? $param['grade_id'] : '';
        $data['son_up_time'] = time();
        if(!empty($param['son_password'])){
            $data['son_password']= user_md5($param['son_password']);
        }
        //获取当前管理员信息
        $uid_now = $_SESSION['think']['manageinfo']['uid'];
        $group_id = $_SESSION['think']['manageinfo']['group_id'];
        $personInfo = Db::name('personSon')->alias('ps')->join('guard_person p','p.id = ps.person_id')->field('p.user_id')->where('ps.id = '.$data['id'])->select();
        if(!empty($personInfo[0]['user_id'])){
            //获取父级管理员
            $userLsit = Db::name('user')->where('uid = '.$personInfo[0]['user_id'])->select();
            $uidList = array();
            $uidList[] = $personInfo[0]['user_id'];
            if(!empty($userLsit)){
                foreach ($userLsit as $key => $value) {
                    $uidList[] = $value['parent_id'];
                }
            }
            if(in_array($uid_now,$uidList) || $group_id == 1){
                $res = Db::name('personSon')->update($data);
                if($res){
                    return jsonMsg('修改成功',0);
                }else{
                    return jsonMsg('修改失败',1);
                }
            }else{
                return jsonMsg('您没有权限修改',1);
            }
        }elseif($group_id == 1){

            $res = Db::name('personSon')->update($data);
            if($res){
                return jsonMsg('修改成功',0);
            }else{
                return jsonMsg('修改失败',1);
            }
        }else{
            return jsonMsg('您没有权限修改',1);
        }
    }

    /**
     * 异常账号修改
     */
    public function abnormal()
    {
        $id = input('id');
        return $this->fetch('person/abnormal', ['id' => $id]);
    }

    public function userAbnormalList()
    {
        $id = input('id');
        $userAbnormal = Db::name('ip_log')->alias('a')
                    ->field('a.*, b.phone, b.nickname')
                    ->join('person b', 'a.uid = b.id')
                    ->where(['uid' => $id])->select();
        if (!$userAbnormal) {
            jsonMsg("暂无数据",1);
        }
        $res = array(
            'code'=>0,
            'msg'=>'',
            'data'=>$userAbnormal
        );
        echo json_encode($res);
    }

    public function removeAbnormal()
    {
        $id = input('id');
        if (!$id) {
            return false;
        }
        $res = Db::name('ip_log')->where(['uid' => $id])->delete();
        if ($res) {
            Db::name('person')->where(['id' => $id])->update(['risk' => 0]);
            return jsonMsg('账号异常解除成功', 0);
        } else {
            return jsonMsg('解除失败', 1);
        }

    }


    /**
     * 获取用户的课程
     */
    public function getOrderByPersonId(Request $request)
    {
        $person_id = input('person_id');
        $orderPerson = new OrderPerson();

        $courseList = $orderPerson->getOrderSonListByPersonId($person_id);

        $res = array(
            'code'=>0,
            'msg'=>'success',
            'data'=>$courseList
          );
        echo json_encode($res);
    }

    /**
     * 禁用
     */
    public function disabled(Request $request)
    {
        $id = $request->param('id');
        if (!$id) {
            jsonMsg('无效订单', 1);
        }

        $orderSonInfo = Db::name('order_person_son')->where(['id' => $id])->find();
        if (!$orderSonInfo) {
            jsonMsg('无效订单', 1);
        }

        if ($orderSonInfo['type'] != 1) {
            jsonMsg('订单未激活，不能停用', 1);
        }
        Db::startTrans();
        try {
             // 执行禁用 修改video_log表type 状态
            $videoLog = $this->checkVideoLog($orderSonInfo['person_id'], $orderSonInfo['video_class_id']);
            Db::name('order_person_son')->where(['id' => $id])->update(['type' => -1]);
            
            // 提交事务
            Db::commit(); 
            jsonMsg('已停用', 0);

        } catch(\Exception $e) {
            Db::rollback();
            jsonMsg('禁用失败', 1);
        }
    }


    public function checkVideoLog($person_id, $video_class_id)
    {
        $videoclass = Db::name('video_class')->where(['id' => $video_class_id])->find();
        if (!$videoclass) {
            jsonMsg('该课程已不存在', 1);
        }
        $videoLog = Db::name('video_log')->where(['person_id' => $person_id, 'video_class_id' => $video_class_id, 'type' => 0])->select();
        if (!$videoLog) {
            jsonMsg('停用的课程不存在', 1);
        }

        // 课程剩余时间
        $expireTime = Db::name('video_log')->where(['person_id' => $person_id, 'video_class_id' => $video_class_id, 'type' => 0])->find();

        $addtime = $expireTime['expireTime'] - time();
        $res = Db::name('video_log')->where(['person_id' => $person_id, 'video_class_id' => $video_class_id])->update(['type' => 1, 'expireTime' => $addtime]);

        if ($res) {
            return true;
        }
        return false;
    }

    /**
     * 用户续费页面
     * @author 杨继州 2019-07-05
     */
    public function isRenew()
    {
        $group_id = $_SESSION['think']['manageinfo']['group_id'];
        $uid = $_SESSION['think']['manageinfo']['uid']; 
        $orderPerson = new OrderPerson();
        $person = new Person();
        $this->assign('isAdmin', $group_id == 1 ? true : false);
        $id = input('id');
        // // 用户信息
        $personInfo = $person->getPersonInfoById($id);

        if (!$id || empty($personInfo) || $personInfo['act_status'] == 3) {
            jsonMsg('要续费的用户不存在', 1);
        }
        $person_id = $personInfo['id'];
        // 查询课程列表
        $subject = Db::name('subject')->field('id, subject')->where('id','<',6)->whereOr('id','>',9)->select();
        $subject = convert_arr_key($subject, 'id');


        // 年级
        $grade = [7, 8, 9];
        // 学期
        $semester = [1, 2, 3];
        // 结果
        $result = array();
        foreach ($subject as $key => $value) {
            foreach ($grade as $kl => $vl) {
                foreach ($semester as $k => $v) {
                    if (($vl == 7 || $vl == 8) && $v == 3) {
                        continue ;
                    }
                    // 判断课程是否存在
                    $videoClassId = Db::name('video_class')->where(['grade_id' => $vl, 'subject_id' => $key, 'Semester' => $v])->value('id');
                    
                    if (!$videoClassId) {
                        // 当前年级的课程不存在，数据库灭有这个课程
                        $subject[$key][$vl][$v]['exist'] = 0;  // 该课程不存在年级学期中

                    } else {
                        $subject[$key][$vl][$v]['exist'] = 1; // 该课程存在
                        
                        $subject[$key][$vl][$v]['audition'] = $orderPerson->getCourseNum($videoClassId, 1); // 正式课数量
                        $subject[$key][$vl][$v]['noaudition'] = $orderPerson->getCourseNum($videoClassId,2); // 试听课数量
                        // 用户存在
                        if ($personInfo) {
                            $judegOrder = $orderPerson->judgeOrder($videoClassId, $person_id); // 是否有订单
                            if ($judegOrder) {
                                
                                $subject[$key][$vl][$v]['isOrder'] = 1; // 有订单
                                $subject[$key][$vl][$v]['orderCheck'] = !empty($judegOrder['orderCheck']) ? $judegOrder['orderCheck'] : ''; // 订单审核状态，1待审核， 2审核通过
                                $subject[$key][$vl][$v]['type'] = $judegOrder['type']; // 订单激活状态， 0待激活， 1激活， -1禁用
                                $subject[$key][$vl][$v]['is_audition'] = $judegOrder['is_audition']; // 课程，0正常课， 1试听课
                                $subject[$key][$vl][$v]['suplerTime'] = $judegOrder['suplerTime']; // 剩余时间
                            } else {
                                $subject[$key][$vl][$v]['isOrder'] = 0; // 没有订单
                            }
                        } 
                    }
                }
            }
        }
        $this->assign('isAdmin', ($group_id == 1) ? true : false);
        $this->assign('list', $subject);
        $this->assign('person',$personInfo);

        // 获取当前管理代理商的课程剩余数量
        // $code = new Code();
        // $person = new Person();
        // // 七年级
        // $sevenGradeUpperSem = $code->getCourseSurplusNum($grade = 7, $semester=1, $array = array(1, 2, 3));
        // $sevenGradeLowerSem = $code->getCourseSurplusNum($grade = 7, $semester=2, $array = array(1, 2, 3));
        // // 八年级
        // $eightGradeUpperSem = $code->getCourseSurplusNum($grade = 8, $semester=1, $array = array(1, 2, 3, 4));
        // $eightGradeLowerSem = $code->getCourseSurplusNum($grade = 8, $semester=2, $array = array(1, 2, 3, 4));
        // // 九年级
        // $nineGradeUpperSem = $code->getCourseSurplusNum($grade = 9, $semester=1, $array = array(1, 2, 3, 4, 5));
        // $ninenGradeLowerSem = $code->getCourseSurplusNum($grade = 9, $semester=2, $array = array(1, 2, 3, 4, 5));

        // //获取管理员列表
        // $userList = Db::name('user')->select();
        
        
        // $this->assign('userList', $userList);
        

        // $this->assign('sevenGradeUpperSem', $sevenGradeUpperSem);
        // $this->assign('sevenGradeLowerSem', $sevenGradeLowerSem);
        // $this->assign('eightGradeUpperSem', $eightGradeUpperSem);
        // $this->assign('eightGradeLowerSem', $eightGradeLowerSem);
        // $this->assign('nineGradeUpperSem', $nineGradeUpperSem);
        // $this->assign('ninenGradeLowerSem', $ninenGradeLowerSem);

        return view('person/renew',['title'=>'用户续费']); 
    }

    /**
     * 提交续费
     * @author 杨继州 2019-07-06
     */
    public function doRenew(Request $request)
    {
        $param = $request->param();
        $id = $param['id'];
        $subject_id = $param['subject_id'];
        $person = new Person();
        $userinfo = $person->getPersonInfoById($id);

        $user_id = $_SESSION['think']['manageinfo']['uid'];
        $group_id = $_SESSION['think']['manageinfo']['group_id'];

        if (!$userinfo) {
            jsonMsg('该用户不存在', 1);
        }
        if (!$subject_id) {
            jsonMsg('请选择要续费的课程', 1);
        }
        $person_id = $userinfo['id'];

        // 处理subject数据
        $subject = $this->checkedSubjectData($subject_id);
        if (!$subject) {
            jsonMsg('请选择要续费的课程', 1);
        }

        Db::startTrans();
        try{
            // 选择有课程
            $payMoney = 0; // 订单实际支付金额
            $money = 0; // 订单金额
            $orderSon = array();

            foreach ($subject as $key => $value) {
                $res = Db::name('video_class')->where(['grade_id' => $value['grade_id'], 'Semester' => $value['semester'], 'subject_id' => $value['subject_id']])->find();
                
                if ($res) {
                    $payMoney +=  empty($res['Discount']) ? ($res['price']) : ($res['price'] * $res['Discount']);
                    $money +=  $res['price'];
                    
                    // 除了超级管理员有无限生成
                    if ($group_id !== 1) {
                        // 判断user_code 是否还有剩余数量
                        if ($value['isAudition'] == 1) {
                            $isAudition = 2;
                        } elseif ($value['isAudition'] == 0) {
                            $isAudition = 1;
                        }
                        $supler = Db::name('user_code')->where(['user_id' => $user_id, 'video_class_id' => $res['id'], 'type' => $isAudition])->value('code_num');
                        if ($supler < 1) {
                            $msg = '您的'. $res['name'] . (($res['Semester'] == 1) ? '上学期' : '下学期'). (($value['isAudition'] == 1) ? '试听课权限' : '正常课权限') .'剩余量不足！';
                            jsonMsg($msg, 1);
                        }
                    }


                    // 查询当前选择的课程是否过期，
                    $personOrder = Db::name('order_person_son')->where(['video_class_id' => $res['id'], 'person_id' => $person_id])->find();

                    // 没有购买过的是不能续费的
                    if (!$personOrder) {
                        jsonMsg('您的课程 '. $res['name'] .(($res['Semester'] == 1) ? '上学期' : '下学期').'从来没有购买过，不能执行续费，请在用户订单中下单', 1);
                    }

                    // 购买订单还为审核
                    if ($personOrder['orderCheck'] == 1) jsonMsg('您的课程 '. $res['name'] .(($res['Semester'] == 1) ? '上学期' : '下学期').'已购买', 1);

                    // 订单待激活的
                    if ($personOrder['orderCheck'] == 2 && $personOrder['type'] == 2) jsonMsg('您的课程'.$res['name'].(($res['Semester'] == 1) ? '上学期' : '下学期'). '已存在，只是未激活', 1);

                    // 查询课程是否过期
                    $personVideoClassOver = $this->selectCourseTime($res['id'], $person_id);

                    // 激活还为过期
                    if (($personOrder['type'] == 1 && $personOrder['is_audition'] == 0) && ($personVideoClassOver - time()) > 0 ) {
                        jsonMsg('您的课程'.$res['name'].(($res['Semester'] == 1) ? '上学期' : '下学期').'已购买', 1);
                    } else {
                        // 修改订单状态为过期
                        Db::name('order_person_son')->where(['id' => $personOrder['id'], 'orderCheck' => 2])->update(['orderCheck' => 4]);
                    }

                    // 禁用还剩余时间
                    if (($personOrder['type'] == -1 && $personOrder['orderCheck'] == 2) && $personVideoClassOver > 0) {
                        jsonMsg('您的课程'.$res['name'].(($res['Semester'] == 1) ? '上学期' : '下学期').'已购买', 1);
                    } else {
                        // 修改订单状态为过期
                        Db::name('order_person_son')->where(['id' => $personOrder['id'], 'orderCheck' => 2])->update(['orderCheck' => 4]);
                    }

                    // 查看某一大订单下所有子订单是否还有过期的， 如果全部为过期的则修改大订单状态也为过期
                    $order_id = $personOrder['order_id'];
                    $ordersonover = Db::name('order_person_son')->where(['order_id' => $order_id, 'orderCheck' => 2])->count('id');
                    if (!$ordersonover) {
                        Db::name('order_person')->where(['order'=> $order_id])->update(['orderCheck' => 4]);
                    }



                    $orderSon[$key]['video_class_id'] = $res['id'];
                    $orderSon[$key]['num'] = 1;
                    $orderSon[$key]['is_audition'] = $value['isAudition'];
                    $orderSon[$key]['person_id'] = $person_id;
                    $orderSon[$key]['money'] = $res['price'];
                    $orderSon[$key]['payMoney'] = empty($res['Discount']) ? ($res['price']) : ($res['price'] * $res['Discount']);
                    $orderSon[$key]['order_type'] = 2;
                    
                } else {
                    jsonMsg('续费课程不存在', 1);
                }
            }

            $info = new OrderPerson;
            $orderid = time().mt_rand(100000,999999);
            $order['order'] = $orderid;
            $order['money']  = $money;
            $order['person_id'] = $person_id;
            $order['strtime'] = date('Y-m-d H:i:s', time());
            $order['payMoney'] = $payMoney;
            $order['user_id'] = $user_id;
            $order['order_type'] = 2;
            
            $info->save($order);

            foreach ($orderSon as $key => $value) {
                $orderSon[$key]['order_id'] = $orderid;
                $orderSon[$key]['order_son_id'] = time().mt_rand(100000,999999);
                $orderSon[$key]['strtime'] = date('Y-m-d H:i:s', time());

                Db::name('order_person_son')->insert($orderSon[$key]);

            }
          
            // 除了超级管理员有无限生成
            if ($group_id !== 1) {
                foreach ($orderSon as $key => $value) {
                    Db::name('user_code')->where(['user_id' => $data['user_id'], 'video_class_id' => $value['video_class_id'], 'type' => 1])->setDec('code_num');
                }
            }

            // 修改的课程video_class_id 
            $videoClassId = array_column($orderSon, 'video_class_id');
            Db::name('video_class')->where(['id' => ['in', $videoClassId]])->setInc('purchase');

            Db::commit();
            jsonMsg('续费成功', 0);
        } catch (\Exception $e) {
            Db::rollback();
            return $this->error($e->getMessage());
            jsonMsg('续费失败', 1);
        }

    }

    /**
     * 查询课程剩余时间
     * @author 杨继州 
     */
    private function selectCourseTime($video_class_id, $person_id)
    {
        return Db::name('video_log')->where(['video_class_id' => $video_class_id, 'person_id' => $person_id])->value('expireTime');
    }

    /**
     * 用户注册是否有订单
     */
    public function judgeIsOrderInsert(Request $request)
    {
        $param = $request->param();
        $subject = !empty($param['subject_id']) ? $param['subject_id'] : array();
        $audi = isset($param['isAudi']) && !empty($param['isAudi']) ? $param['isAudi'] : '';
        if (!empty($subject) || !empty($audi)) {
            $data = [
                'code' => 1
            ];
        } else {
            $data = [
                'code' => 0
            ];
        }
        return $data;
    }


    /**
   * 临时订单生成
   */
    public function getOrderNow(Request $request)
    {
        $param=$request->param();
        $orderPerson = new OrderPerson();
        $datas = isset($param['subject_id']) ? $param['subject_id'] : array();

        $phone = $param['phone'];
        $userPerson = Db::name('person')->where(['phone' => $phone])->find();
        
        // 当前用户只有他的代理可以给下单
        // if ($userPerson) {
        //     if ($userPerson['user_id']) {
        //         $user_type = $_SESSION['think']['manageinfo']['user_type'];
        //         $agent_id = $_SESSION['think']['manageinfo']['agent_id'];
        //         $org_id = $_SESSION['think']['manageinfo']['org_id'];
        //         $user_id = $_SESSION['think']['manageinfo']['uid'];
        //         if ($user_type == 1) {
        //             // 代理
        //             // 获取代理下的所有员工
        //             $allUser = Db::name('user')->where(['user_type' => 2, 'agent_id' => $user_id])->column('uid');
        //             $allUser[] = $user_id;
        //         } else {
        //             // 员工
        //             $allUser = $orderPerson->getAllOrgSon($org_id);
        //             $allUser = array_column($allUser, 'uid');
        //             $allUser[] = $user_id;
        //         }
    
        //         if (!in_array($userPerson['user_id'], $allUser)) {
        //             jsonMsg('该账号已有所属客服，不能下单', 1);
        //         }
        //     }
        // }

        // $phone = isset($param['phone']) && !empty($param['phone']) ? trim($param['phone']) : ''; 
        // if (!$phone) {
           
        //     jsonMsg('手机号不能为空', 1);
        // }

        // $person = Db::name('person')->where(['phone' => $phone])->find();
        // if ($person && $person['act_status'] == 3 && !empty($subject)) {
            
        //     // jsonMsg('该账号目前被禁用中，不能下单，请联系管理员', 1);
        //     jsonMsg('手机号被禁用', 1);
        // }

        // 支付方式
        $paytype = isset($param['paytype']) && !empty($param['paytype']) ? $param['paytype'] : array(); 
        $payArr = array();

        $m = 0;
        if ($paytype) {
            foreach ($paytype as $key => $value) {
                if (isset($value['id']) && !empty($value['id']) && !empty($value['money'])) {
                    $payname = Db::name('paytype')->where(['id' => $value['id']])->find();
                    $payArr[$m]['paytype'] = $payname['pay_type'];
                    $payArr[$m]['money'] = $value['money'];
                } 
                $m++;
            }
        }

        // 接收数据处理
        $arr = array();
        foreach ($datas as $key => $value) {
            foreach ($value as $kl => $vl) {
                foreach ($vl as $k => $v) {

                if (isset($v['isAudition'])) {
                    if ($v['isAudition'] == 1) {
                    // 试听
                    $isAudition = 1;
                    } elseif ($v['isAudition'] == 0) {
                    // 所有课程
                    $isAudition = 0;
                    }
                } else {
                    continue ;
                }
                $arr[] = [
                    'grade_id' => $key,
                    'semester' => $kl,
                    'subject_id' => $k,
                    'isAudition' => $isAudition
                ];
                }
            }
        }

        $result = array();
        $price = 0;
        foreach ($arr as $key => $value) {
            if($value['subject_id']>9){
                $res = Db::name('product')->alias('a')
                    ->field("a.*, b.subject,
                        concat(c.grade, '-', b.subject, '-', 
                        case a.Semester
                            when 1 then '上学期'
                            when 2 then '下学期'
                            when 3 then '全册'
                        end
                    ) as title,
                    c.grade
                ")
                    ->join('guard_subject b', 'a.subject_id = b.id')
                    ->join('guard_grade c', 'a.grade_id = c.id')
                    ->where(['a.grade_id' => $value['grade_id'], 'Semester' => $value['semester'], 'subject_id' => $value['subject_id']])
                    ->find();
            }else{
                $res = Db::name('video_class')->alias('a')
                    ->field("a.*, b.subject,
                        concat(c.grade, '-', b.subject, '-', 
                        case a.Semester
                            when 1 then '上学期'
                            when 2 then '下学期'
                            when 3 then '全册'
                        end
                    ) as title,
                    c.grade
                ")
                    ->join('guard_subject b', 'a.subject_id = b.id')
                    ->join('guard_grade c', 'a.grade_id = c.id')
                    ->where(['a.grade_id' => $value['grade_id'], 'Semester' => $value['semester'], 'subject_id' => $value['subject_id']])
                    ->find();
            }

            $price += ($value['isAudition'] == 1) ? (!empty($res['audition_price']) ? $res['audition_price'] : 0) : $res['price'];
            $data['courseName'] = $res['title'];
            $data['price'] = ($value['isAudition'] == 1) ? (!empty($res['audition_price']) ? $res['audition_price'] : 0) : $res['price'];
            $data['isAudition'] = $value['isAudition'] == 1 ? '(试听)': '';
            array_push($result, $data);
        }

        $datas = [
            'data' => $result,
            'totalPrice' => $price,
            'paytype' => $payArr
        ];
        jsonMsg('success', 0, $datas);
    }

    /**
     * 跟进状态修改
     */
    public function followUp()
    {
        $id = input('id');
        if (!$id) {
            jsonMsg('用户不存在', 1);
        }
        $personinf = Db::name('person')->where(['id' => $id])->find();
        if (!$personinf || $personinf['status'] != 1) {
            jsonMsg('用户状态不能被修改', 1);
        }

        $res = Db::name('person')->where(['id' => $id])->update(['status' => 2]);
        if ($res) {
            jsonMsg('用户状态已修改为跟进状态', 0);
        } else {
            jsonMsg('修改失败', 1);
        }
    }

    /**
     * 判断手机号码是否注册
     */
    public function isPersonExist()
    {
        $orderPerson = new OrderPerson();
        $phone = input('phone');
        if (!$phone) {
            // jsonMsg('手机号码不能为空', 1);
            echo "<script>alert('手机号码不能为空')</script>";
        }
        $isPhone = checkphone($phone);
        if (!$isPhone) {
            // jsonMsg('手机号码格式有误', 1);
            echo "<script>alert('手机号码格式有误')</script>";
        }

        $uid = $_SESSION['think']['manageinfo']['uid']; 
        $group_id = $_SESSION['think']['manageinfo']['group_id']; // 1管理员

        $personInfo = Db::name('person')->where(['phone' => $phone])->find();
        // 查询课程列表
        $subject = Db::name('subject')->field('id, subject')->where('id','<',6)->whereOr('id','>',9)->select();
        $subject = $this->convert_arr_key($subject, 'id');

        // 年级
        $grade = [7, 8, 9];
        // 学期
        $semester = [1, 2, 3];

        if (!empty($personInfo)) {

            if ($phone && $personInfo) {
                $person_id = $personInfo['id'];
            }

            // 结果
            $result = array();
            foreach ($subject as $key => $value) {
                foreach ($grade as $kl => $vl) {
                foreach ($semester as $k => $v) {
                    if (($vl == 7 || $vl == 8) && $v == 3) {
                    continue ;
                    }
                    //科目大于9的是产品
                    // 判断课程是否存在
                    if($key>9){
                        $videoClassId = Db::name('product')->where(['grade_id' => $vl, 'subject_id' => $key, 'Semester' => $v])->find();
                    }else{
                        $videoClassId = Db::name('video_class')->where(['grade_id' => $vl, 'subject_id' => $key, 'Semester' => $v])->find();
                    }
                    
                    if (!$videoClassId) {
                    // 当前年级的课程不存在，数据库灭有这个课程
                    $subject[$key][$vl][$v]['exist'] = 0;  // 该课程不存在年级学期中

                    } else {
                    $subject[$key][$vl][$v]['exist'] = 1; // 该课程存在
                    
                    $subject[$key][$vl][$v]['audition'] = $orderPerson->getCourseNum($videoClassId['id'], 1); // 正式课数量
                    $subject[$key][$vl][$v]['noaudition'] = $orderPerson->getCourseNum($videoClassId['id'],2); // 试听课数量
                    $subject[$key][$vl][$v]['price'] = !empty($videoClassId['price']) ? $videoClassId['price'] : 0; // 试听课数量
                    $subject[$key][$vl][$v]['audit_price'] = !empty($videoClassId['audition_price']) ? $videoClassId['audition_price'] : 0;; // 试听课价格
                    // 用户存在
                    if ($personInfo) {
                        $judegOrder = $orderPerson->judgeOrder($videoClassId['id'], $person_id,$key); // 是否有订单
                        if ($judegOrder) {
                        $subject[$key][$vl][$v]['isOrder'] = 1; // 有订单
                        $subject[$key][$vl][$v]['orderCheck'] = !empty($judegOrder['orderCheck']) ? $judegOrder['orderCheck'] : ''; // 订单审核状态，1待审核， 2审核通过
                        $subject[$key][$vl][$v]['type'] = $judegOrder['type']; // 订单激活状态， 0待激活， 1激活， -1禁用
                        $subject[$key][$vl][$v]['is_audition'] = $judegOrder['is_audition']; // 课程，0正常课， 1试听课
                        $subject[$key][$vl][$v]['suplerTime'] = $judegOrder['suplerTime']; // 剩余时间
                        } else {
                        $subject[$key][$vl][$v]['isOrder'] = 0; // 没有订单

                        }
                    } 
                    }
                }
                }
            }
           
            $personInfo['birthday']= date('Y-m-d', $personInfo['birthday']);

            // 已经购买过试听课且未到期的
            $orderPerson = Db::name('order_person_son')->where(['person_id' => $personInfo['id'], 'orderCheck' => 2])->find();
            if ($orderPerson && $orderPerson['is_audition'] == 1) { // 试听
                $time = time();
                $ordertime = strtotime($orderPerson['endtime']) + 210 * 24 * 3600;
                $this->assign('isBuyAudi', $ordertime > $time ? true : false);
            }
             elseif ($orderPerson && $orderPerson['is_audition'] == 0) { // 正式课
                $personVideoLog = Db::name('video_log')->where(['person_id' => $personInfo['id']])->select();
                $nowTime = time();
                $isBuyAudi = false;
                foreach ($personVideoLog as $key => $value) {
                    if ($value['type'] == 1 && $value['expireTime'] > 0) {
                        $isBuyAudi = true;
                        break;
                    }elseif ($value['type'] == 0 && $value['expireTime'] > $nowTime) {
                        $isBuyAudi = true;
                        break;
                    }
                }
            
                $this->assign('isBuyAudi', $isBuyAudi);
            }

            $this->assign('isBool', true);
            $this->assign('info', $personInfo);
        } else {
            foreach ($subject as $key => $value) {
                foreach ($grade as $kl => $vl) {
                    foreach ($semester as $k => $v) {
                        if (($vl == 7 || $vl == 8) && $v == 3) {
                            continue ;
                        }
                        // 判断课程是否存在
                        if($key>9){
                            $videoClassId = Db::name('product')->where(['grade_id' => $vl, 'subject_id' => $key, 'Semester' => $v])->find();
                        }else{
                            $videoClassId = Db::name('video_class')->where(['grade_id' => $vl, 'subject_id' => $key, 'Semester' => $v])->find();
                        }
                        
                        if (!$videoClassId) {
                            // 当前年级的课程不存在，数据库灭有这个课程
                            $subject[$key][$vl][$v]['exist'] = 0;  // 该课程不存在年级学期中

                        } else {
                            $subject[$key][$vl][$v]['exist'] = 1; // 该课程存在
                            $subject[$key][$vl][$v]['audition'] = $orderPerson->getCourseNum($videoClassId['id'], 1); // 正式课数量
                            $subject[$key][$vl][$v]['noaudition'] = $orderPerson->getCourseNum($videoClassId['id'],2); // 试听课数量
                            $subject[$key][$vl][$v]['price'] = $videoClassId['price'] ? $videoClassId['price'] : 0; // 课程价格
                            $subject[$key][$vl][$v]['audit_price'] = !empty($videoClassId['audition_price']) ? $videoClassId['audition_price'] : 0;; // 试听课价格
                        }
                    }
                }
            }
            $this->assign('isBool', false);
        }

        $user = Db::name('user')->where(['uid' => $uid])->find();
        if ($user['user_type'] == 1) {
            $agent_id = $user['uid'];
        } else {
            $agent_id = $user['agent_id'];
        }

        $person = Db::name('person')->where(['phone' => $phone])->find();

        if ($person && $person['act_status'] == 3) {
            $this->assign('personStatus', 3);
        } else {
            $this->assign('personStatus', 1);
        }

        $paytype = Db::name('paytype')->where(['agent_id' => $agent_id])->select();
        // 媒体
        $medialist = Db::name('to_media')->where(['agent_id' => $agent_id])->select();

        // 试听剩余数量
        $audiNum = $this->getAudiNum();

        $this->assign('paytypelist', !empty($paytype) ? $paytype : array());
        $this->assign('isAdmin', $group_id == 1 ? true : false);
        $this->assign('list', $subject);
        $this->assign('phone', $phone);
        $this->assign('mediaList', $medialist);
        $this->assign('audiNum', $audiNum);

        return $this->fetch('person/addPerson');
    }


    /**
     * 获取当前管理员的试听数量
     * 
     */
    public function getAudiNum()
    {
        $uid = $_SESSION['think']['manageinfo']['uid']; 
        $user_type = $_SESSION['think']['manageinfo']['user_type']; 
        $agent_id = $_SESSION['think']['manageinfo']['agent_id']; 
        $group_id = $_SESSION['think']['manageinfo']['group_id']; // 1管理员
        $count = 0;
        if ($group_id != 1) {
            if ($user_type == 1) {
                $uid = $uid;
            } else {
                $uid = $agent_id;
            }
            $count = Db::name('user_code')->where(['user_id' => $uid, 'type' => 2])->value('code_num');
        }
      
        return $count;
    }

    /**
     * 获取某一个代理下的媒体列表
     * @param int agent_id 代理id
     */
    public function getTomediaList()
    {
        $agent_id = input('agent_id');
        if (!$agent_id) {
            $user_type  = $_SESSION['think']['manageinfo']['user_type'];
            $agent_id  = $_SESSION['think']['manageinfo']['agent_id'];
            $group_id  = $_SESSION['think']['manageinfo']['group_id'];
            $user_id = $_SESSION['think']['manageinfo']['uid'];
            if ($group_id == 1 || $group_id == 2) {
                $where1 = [];
                if ($group_id == 2) {
                    $uid = Db::name('user')->where(['group_id' => 1])->value('uid');
                    $where1['agent_id'] = ['neq', $uid];
                }
                $list = Db::name('to_media')->where($where1)->select();
            } else {
                if ($user_type == 1) {
                    $agent_id = $_SESSION['think']['manageinfo']['uid'];
                }
                $list = Db::name('to_media')->where(['agent_id' => $agent_id])->select();
            }
            
        } else {
            $list = Db::name('to_media')->where(['agent_id' => $agent_id])->select();
        }
       
        jsonMsg('success', 0, $list);
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
  




    /**
     * 用户订单作废
     */
    public function personOrderRefund()
    {
        $param = input();
        dump($param);
    }
}
