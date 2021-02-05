<?php
namespace app\manage\model;
use think\Model;
use Phinx\Db\Table;
use think\Db;
use think\Image;
class OrderPerson extends Model
{
    //设置当前模型对应的完整数据表名称
    protected $table = 'guard_order_person';
    protected $members = array();// 代理商下的用户
    public $state=array(1=>'待支付 ',2=>'已支付',3=>'支付失败', 4=>'取消支付');
    public $orderCheck=array(1=>'待审核 ',2=>'审核通过',3=>'审核未通过');
    public $code_status=array(1=>'未激活',2=>'以激活',3=>'禁用',4=>'已卖出');
    /**
     * 获取order列表数据
     * @author 韩春雷 2019.3.26
     * @DateTime 2019-02-27T14:28:40+0800
     * @param    array       $where [筛选条件]
     * @param    int         $page  [页码]
     * @param    int         $limit [每页数量]
     * @return   [array]                  
     */
    public function getOrderPersonList($where, $type=0, $page=null,$limit=null)
    {
        $group_id = $_SESSION['think']['manageinfo']['group_id'];
        $user_id  = $_SESSION['think']['manageinfo']['uid'];

        $user_type = $_SESSION['think']['manageinfo']['user_type'];
        $agent_id = $_SESSION['think']['manageinfo']['agent_id'];

        $uid = array();
        if($group_id == 1 || $group_id == 2){//登录用户是管理员时
            $isAdmin = isset($where['isAdmin']) && !empty($where['isAdmin']) ? true : false;
            if ($isAdmin) {
                unset($where['isAdmin']);
            }
            if ($type == 1) {// 我的用户订单
                $where['o.user_id'] = $user_id;
                if($page){
                    $list =  Db::name("order_person")->alias('o')
                            ->field('o.*,u.username,u.phone,c.card,p.nickName,p.phone,c.coursePackage_id')
                            ->join('guard_person p',' o.person_id = p.id','LEFT') 
                            ->join('guard_user u','o.user_id = u.uid','LEFT')
                            ->join('guard_code c','o.code_id = c.id','LEFT')
                            ->where($where)
                            ->page($page,$limit)
                            ->order('o.strtime DESC')
                            ->select();
                    return $list;
                }else{
                    $count = Db::name("order_person")->alias('o')
                            ->join('guard_person p','p.id = o.person_id','LEFT')
                            ->join('guard_user u','o.user_id = u.uid','LEFT')
                            ->join('guard_code c','o.code_id = c.id','LEFT')
                            ->where($where)
                            ->count();
                    return $count;
                }
            } elseif ($type == 2 || $type == 3 || $type == 4) {  // 所有用户订单、审核订单
                $userIdArr = array();
                // 所有用户
                
				if (isset($where['o.user_id']) && !empty($where['o.user_id'])) {
					$agent_id = $where['o.user_id'];
					// 获取赛选代理商下的所有用户
					$userId = Db::name('user')->where(['agent_id' => $agent_id, 'status' => 1, 'user_type' => 2])->select();
                    $res = array_column($userId, 'uid');
                    $userIdArr = $res;
                    $userIdArr[] = $where['o.user_id'];
           
                    unset($where['o.user_id']);
                    // 获取所有
					if($page){
                        $list =  Db::name("order_person")->alias('o')
                                ->field('o.*,u.username,u.phone,c.card,p.nickName,p.phone,c.coursePackage_id')
                                ->join('guard_person p',' o.person_id = p.id','LEFT') 
                                ->join('guard_user u','o.user_id = u.uid','LEFT')
                                ->join('guard_code c','o.code_id = c.id','LEFT')
                                ->where($where)
                                ->where(['o.user_id' => ['in', $userIdArr]])
                                ->page($page,$limit)
                                ->order('o.strtime DESC')
                                ->select();
                        if ($isAdmin) {
                            foreach ($list as $key =>$value) {
                                if ($value['user_id']) {
                                    $info = Db::name('user')->where(['uid' => $value['user_id']])->find();
                                    if ($info['user_type'] == 1) {
                                        $username = $info['username'];
                                    } else {
                                        $username = Db::name('user')->where(['uid' => $info['agent_id']])->value('username');
                                    }
                                    $list[$key]['username'] = $username;
                                }
                            }
                        }
                       
                        return $list;
                    }else{
                        $count = Db::name("order_person")->alias('o')
                                ->join('guard_person p','p.id = o.person_id','LEFT')
                                ->join('guard_user u','o.user_id = u.uid','LEFT')
                                ->join('guard_code c','o.code_id = c.id','LEFT')
                                ->where($where)
                                ->where(['o.user_id' => ['in', $userIdArr]])
                                ->count();
                        return $count;
                    }
				} else {
                    if ($group_id == 2) {
						// 获取超级管理员
						$userid = Db::name('user')->where(['group_id' => 1])->value('uid');
						$userId = Db::name('user')->where(['agent_id' => $userid, 'status' => 1, 'user_type' => 2])->select();
						$res = array_column($userId, 'uid');
                        if($type == 4){
                            $res[] = $user_id;
                        }
						$res[] = $userid;
						$where['o.user_id'] = ['not in', $res];
					}else{
                        if($type == 4){
                            $where['o.user_id'] = ['neq', $user_id];
                        }
                    }
					// 获取所有
					if($page){
                        $list =  Db::name("order_person")->alias('o')
                                ->field('o.*,u.username,u.phone,c.card,p.nickName,p.phone,c.coursePackage_id')
                                ->join('guard_person p',' o.person_id = p.id','LEFT') 
                                ->join('guard_user u','o.user_id = u.uid','LEFT')
                                ->join('guard_code c','o.code_id = c.id','LEFT')
                                ->where($where)
                                ->page($page,$limit)
                                ->order('o.strtime DESC')
                                ->select();
                        if ($isAdmin) {
                            foreach ($list as $key =>$value) {
                                if ($value['user_id']) {
                                    $info = Db::name('user')->where(['uid' => $value['user_id']])->find();
                                    if ($info['user_type'] == 1) {
                                        $username = $info['username'];
                                    } else {
                                        $username = Db::name('user')->where(['uid' => $info['agent_id']])->value('username');
                                    }
                                    $list[$key]['username'] = $username;
                                }
                            }
                        }
                        
                        return $list;
                    }else{
                        $count = Db::name("order_person")->alias('o')
                                ->join('guard_person p','p.id = o.person_id','LEFT')
                                ->join('guard_user u','o.user_id = u.uid','LEFT')
                                ->join('guard_code c','o.code_id = c.id','LEFT')
                                ->where($where)
                                ->count();
                        return $count;
                    }
				}
            }
            
            //修改前的， 以上为修改后
            // if($page){
            //     $list =  Db::name("order_person")->alias('o')
            //             ->field('o.*,u.username,u.phone,c.card,p.nickName,p.phone,c.coursePackage_id')
            //             ->join('guard_person p',' o.person_id = p.id','LEFT') 
            //             ->join('guard_user u','o.user_id = u.uid','LEFT')
            //             ->join('guard_code c','o.code_id = c.id','LEFT')
            //             ->where($where)
            //             ->page($page,$limit)
            //             ->order('o.strtime DESC')
            //             ->select();
            //     // $list =self::changeOrderList($list);
            //     return $list;
            // }else{
            //     $count = Db::name("order_person")->alias('o')
            //             ->join('guard_person p','p.id = o.person_id','LEFT')
            //             ->join('guard_user u','o.user_id = u.uid','LEFT')
            //             ->join('guard_code c','o.code_id = c.id','LEFT')
            //             ->where($where)
            //             ->count();
            //     return $count;
            // }
        }else{//当前用户不是管理员
            // 判断当前用户的代理用户id，
           // $userAgentId = $this->getCheckFinance($user_id);
            if ($user_type != 1) {
                $user_orgid = $_SESSION['think']['manageinfo']['org_id'];
                $yid = $_SESSION['think']['manageinfo']['agent_id'];
            }else{
                $user_orgid = $user_id;
                $yid = $user_id;
            } 

            // 我的订单
            if ($type == 2 || $type == 4) {//查看全部用户订单或者全部代理商用户订单
                
                $this->getAllPersonSon($user_orgid,$type);
                $user = array_column($this->members, 'uid');
                if ($user) {
                    $uid = $user;
                }
                if($type != 4){
                    $uid[] = $user_id;
                }
            } elseif ($type == 3) { // 审核订单
                $user = Db::name('user')->where(['agent_id' => $yid, 'user_type' => 2, 'status' => 1])->select();
                if ($user) {
                    $uid = array_column($user, 'uid');
                }

                $uid[] = $yid;
            } else {  // 我的用户订单
                $uid[] = $user_id;
            }

            
            // //获取所有的子类代理商
            // $idList = Db::name('user')->alias('u')
            //         ->join('guard_group g','g.id = u.group_id')
            //         ->field('u.uid')
            //         ->where('u.parent_id = '.$user_id)
            //         ->select();
            
            // if(isset($idList)){
            //     foreach ($idList as $key => $value) {
            //         $uid[] = $value['uid'];
            //     }
            // }
            
            
   
            //转换数据类型
            $uid = implode(',',$uid);
            //dump($uid);
            if (!isset($where['o.user_id']) || empty($where['o.user_id'])) {
                $where['o.user_id'] = array('in',$uid);
            }
            
            if($page){
                $list =  Db::name("order_person")->alias('o')
                    ->field('o.*,u.username,u.phone,c.card,p.nickName,p.phone,c.coursePackage_id')
                    ->join('guard_person p','p.id = o.person_id','LEFT') 
                    ->join('guard_user u','o.user_id = u.uid','LEFT')
                    ->join('guard_code c','o.code_id = c.id','LEFT')
                    ->where($where)
                    ->order('o.strtime DESC')
                    ->page($page,$limit)
                    ->select();
                return $list;
            }else{
                $count = Db::name("order_person")->alias('o')
                    ->join('guard_person p','p.id = o.person_id','LEFT')
                    ->join('guard_user u','o.user_id = u.uid','LEFT')
                    ->join('guard_code c','o.code_id = c.id','LEFT')
                    ->where($where)
                    ->count();
                return $count;
            }
        }
    }

    /**
	 * 获取用户下的所有员工的id ，除了代理商
	 */

	// public function getAllPersonSon($uid)
	// {
 //        $user_type = $_SESSION['think']['manageinfo']['user_type'];
 //        if($user_type==1)
 //        {//当前用户是代理商
 //            $userList = Db::name('user')->where(['agent_id' => $uid, 'user_type' => 2])->select();
 //            $this->members = array_merge($this->members, $userList);
 //        } else {//当前用户是员工

 //            $agent_id = $_SESSION['think']['manageinfo']['agent_id'];
 //            //查找当前用户分组下面的子分组
 //            $res=Db::name('user_organization')->where(['pid' => $uid, 'agent_id' => $agent_id])->select();
            
 //            if($res)
 //            {//我的下面有子组织分组，继续查找子分组下面的组织分组
 //                foreach($res as $v)
 //                {
 //                    $userList = Db::name('user')->where(['org_id' => $v['id'], 'agent_id' => $agent_id,'status'=>1,'user_type' => 2])->select();
 //                    $this->members = array_merge($this->members, $userList);
 //                    $this->getAllPersonSon($v['id']);
 //                }   
 //            }else{//我下面没有子组织分组返回我的组织分组
 //                $this->members = array_merge($this->members, $res);
 //            }
 //        }
            
 //        return $this->members;
 //    }

    public function getAllPersonSon($uid,$type)
    {
        $user_type = $_SESSION['think']['manageinfo']['user_type'];
        //当前用户是代理商
        if($user_type==1)
        {
            //------------------查看所有的所属代理商以及代理商所有的员工------------------//
            if($type == 4){
                $res=Db::name('user')->where(['agent_id' => $uid, 'user_type' => 1])->select();
                $this->members = array_merge($this->members, $res);
                if($res)
                {
                    foreach($res as $v)
                    {
                        // $this->members = array_merge($this->members, $agentList);
                        //查询下面所有的员工
                        $userList = Db::name('user')->where(['agent_id' => $v['uid'], 'user_type' => 2])->select();
                        $this->members = array_merge($this->members, $userList);
                        $this->getAllPersonSon($v['uid'],$type);
                    }   
                }
                // $userList = Db::name('user')->where(['agent_id' => $uid, 'user_type' => 2])->select();
                //$this->members = array_merge($this->members, $userList);
            }else{
                ///---------------------------只展示我的用户-----------------------------//
                    //查看我所有的员工
                    $userList = Db::name('user')->where(['agent_id' => $uid, 'user_type' => 2])->select();
                    $this->members = array_merge($this->members, $userList);
                //-------------------------------------------------------//
            }
        }else{//当前用户是员工
            if($type != 4){
                $agent_id = $_SESSION['think']['manageinfo']['agent_id'];
                //查找当前用户分组下面的子分组
                $res=Db::name('user_organization')->where(['pid' => $uid, 'agent_id' => $agent_id])->select();

                if($res)
                {//我的下面有子组织分组，继续查找子分组下面的组织分组
                    foreach($res as $v)
                    {
                        $userList = Db::name('user')->where(['org_id' => $v['id'], 'agent_id' => $agent_id,'status'=>1,'user_type' => 2])->select();
                        $this->members = array_merge($this->members, $userList);
                        $this->getAllPersonSon($v['id'],$type);
                    }   
                }else{//我下面没有子组织分组返回我的组织分组
                    $this->members = array_merge($this->members, $res);
                }
            }
        }
        return $this->members;
    }

    public function getAllOrgSon($uid)
	{
        $user_type = $_SESSION['think']['manageinfo']['user_type'];
        if($user_type==1)
        {//当前用户是代理商
            $userList = Db::name('user')->where(['agent_id' => $uid, 'status' => 1, 'user_type' => 2])->select();
            $this->members = array_merge($this->members, $userList);
        }else{//当前用户是员工
            $agent_id = $_SESSION['think']['manageinfo']['agent_id'];
            //查找当前用户分组下面的子分组
            $res=Db::name('user_organization')->where(['pid' => $uid, 'agent_id' => $agent_id])->select();
            if($res)
            {//我的下面有子组织分组，继续查找子分组下面的组织分组
                foreach($res as $v)
                {
                    $userList = Db::name('user')->where(['org_id' => $v['id'], 'agent_id' => $agent_id,'status'=>1,'user_type' => 2])->select();
                    $this->members = array_merge($this->members, $userList);
                    $this->getAllOrgSon($v['id']);
                }   
            }else{//我下面没有子组织分组返回我的组织分组
                $this->members = array_merge($this->members, $res);
            }
        }
            
        return $this->members;
    }




    /**
     * 获取order详细数据
     * @author 韩春雷 2019.3.26
     * @DateTime 2019-02-27T14:28:40+0800
     * @param    int       $data [order ID]
     * @return   [array]                  
     */
    public static function getOneOrderPerson($data){
        $where['o.id']=$data;
        $list =  Db::name("order_person")->alias('o')
                    ->field('o.*,u.username,c.coursePackage_id,u.phone as userPhone,c.card,p.nickName,p.phone as personPhone,o.money as price,c.status as code_status')
                    ->join('guard_person p','p.id = o.person_id','LEFT')
                    ->join('guard_user u','o.user_id = u.uid')
                    ->join('guard_code c','o.code_id = c.id','LEFT')
                    ->where($where)
                    ->select();
        $list =self::changeOrderList($list);
        return $list;
    }

    /**
     * 获取order详细数据
     * @author 杨继州 2019-07-02
     * @param int $id
     */
    public static function getOrderInfoById($id)
    {
        if (!$id) {
            return false;
        }
        
        // 大订单
        $field = 'a.*, b.nickName, b.phone personphone, c.username, c.phone userphone';
        $res = Db::name('order_person')->alias('a')
                    ->field($field)
                    ->join('guard_person b', 'a.person_id = b.id', 'left')
                    ->join('guard_user c', 'a.user_id = c.uid', 'left')
                    ->where(['a.id' => $id])
                    ->find();
        if (!$res) {
            return false;
        }

        // 子订单
        $orderSon = Db::name('order_person_son')->where(['order_id' => $res['order']])->select();
        
        if (!$orderSon) {
            return false;
        }
        
        
        // 子订单里课程相关信息组合
        $orderType = self::checkData($orderSon);
        if (!$orderType) {
            return false;
        }
        $res['orderType'] = $orderType;

        return $res;
        
    }

    /**
     * 看课权限数据组合
     * 2019-07-02
     * @param $data array  子订单列表
     */

    public static function checkData($data)
    {
        $orderType = array();
        if (!$data) {
            return false;
        }
        foreach ($data as $key => $value) {
            if ($value['video_class_id']) {
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
                    ->where(['a.id' => $value['video_class_id']])
                    ->find();
                if ($res) {
                    $res['title'] = $res['title'].'-'. (($value['is_audition'] == 1) ? '可试听' : '不可试听');
                    array_push($orderType, $res['title']);
                }
            }elseif($value['product_id']){
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
                    ->where(['a.id' => $value['product_id']])
                    ->find();
                if ($res) {
                    $res['title'] = $res['title'].'-'. (($value['is_audition'] == 1) ? '可试听' : '不可试听');
                    array_push($orderType, $res['title']);
                }
            }
            else {
                $res['title'] = '试听课';
                array_push($orderType, $res['title']);
            }
           
            
            // if ($res) {
            //     $res['title'] = $res['title'].'-'. (($value['is_audition'] == 1) ? '可试听' : '不可试听');
            //     array_push($orderType, $res['title']);
            // }
        }
        if (!$orderType) {
            return false;
        }
        return implode('  ', $orderType);
    }

    /**
     * 数据转换
     * @author   韩春雷 2019.4.17
     * @param    array       $data
     * @return   [array]                  
     */
    public static function changeOrderList($data){
        foreach ($data as $key => $value) {
            if($value['order_type'] == 1){
                $coursePackage_id = explode(',',$value['coursePackage_id']);
            }else{
                $coursePackage_id = explode(',',$value['video_class_id']);
            }
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
                // $data[$key]['state']=$this->state[$value['state']];
                // $data[$key]['orderCheck']=$this->orderCheck[$value['orderCheck']];
            }
            $data[$key]['coursePackage'] = implode('，',$classInfo);
        }
        return $data;
    }

    /**
     * 根据用户获取用户的看课权限
     * @author yangjizhou 2019-07-03
     * @param $person_id 用户id int
     */
    public function getCourseByPersonId($person_id)
    {
        $orderTypeTxt = '';
        if (!$person_id) return $orderTypeTxt;
        // 获取订单列表
        $orderList = Db::name('order_person')->where(['person_id' => $person_id, 'orderCheck' => 2])->select();

        if (!$orderList) return $orderTypeTxt;

        foreach ($orderList as $key => $value) {
            $orderSonList = $this->getOrderSonByOrderId($value['order']); 
            $orderType = self::checkData($orderSonList);
            $orderList[$key]['orderType'] = $orderType;
        }
        $orderTypeTxt = implode('  ',array_column($orderList, 'orderType'));
        return $orderTypeTxt;
    }

    /**
     * 获取子订单列表
     * @author 杨继州 2019-07-03
     * @param $order_id  大订单列表
     */
    public function getOrderSonByOrderId($order_id)
    {
        return Db::name('order_person_son')->where(['order_id' => $order_id])->select();
    }

    /**
     * 根据person_id 获取子订单列表
     */
    public function getOrderSonListByPersonId($person_id)
    {
        $sonList = Db::name('order_person_son')->where(['person_id' => $person_id])->select();
        if (!$sonList) {
            return array();
        }

        foreach ($sonList as $key => $value) {
            if ($value['video_class_id'] && $value['is_audition'] != 1) {
                $productStatus = 0;
                if ($value['type'] == 0) {
                    $effectivedays = Db::name('video_class')->where(['id' => $value['video_class_id']])->value('effectivedays');
                    if (!empty($effectivedays)) {
                        $daytext = $effectivedays.'天';
                    } else {
                        $daytext = '190天';
                    }
                    $getVideoLog['remTime'] = $daytext;
                } elseif($value['type'] == 1) {
                    // 已激活
                    $isAudition = 0;
                    $getVideoLog = $this->isChecked($value['person_id'], $value['video_class_id'], $isAudition,$productStatus);
                } else {
                    // 禁用
                    $isAudition = 1;
                    $getVideoLog = $this->isChecked($value['person_id'], $value['video_class_id'], $isAudition,$productStatus);
                }
                $courseName = $this->getVideoClassName($value['video_class_id'], $value['is_audition'],$productStatus);
    
                $yesAudition = Db::name('order_person_son')->where(['person_id' => $person_id, 'video_class_id' => $value['video_class_id'], 'is_audition' => 0, 'id' => ['neq', $value['id']]])->find();
                // if ($yesAudition) {
                //     unset($sonList[$key]);
                //     continue;
                // } 
    
                // $sonList[$key]['isActive'] = $value['type'] == 0 ? '待激活' : (($value['type'] == 1) ? '已激活' : '已停用');
                if ($value['is_forbidden'] == 1) {
                    $sonList[$key]['isActive'] = '订单已作废';
                } else {
                    $sonList[$key]['isActive'] =  $value['orderCheck'] == 1 ? '待审核' : (($value['orderCheck'] == 2 && $value['type'] ==1) ? '已激活' : (($value['orderCheck'] == 2 && $value['type'] ==0) ? '待激活' : '已停用'));
    
                }
                
                $sonList[$key]['remTime'] = $getVideoLog['remTime'];
                $sonList[$key]['courseName'] = $courseName;
            }elseif($value['product_id'] && $value['is_audition'] != 1){
                $productStatus = 1;
                if ($value['type'] == 0) {
                    $effectivedays = Db::name('product')->where(['id' => $value['product_id']])->value('effectivedays');
                    if (!empty($effectivedays)) {
                        $daytext = $effectivedays.'天';
                    } else {
                        $daytext = '190天';
                    }
                    $getVideoLog['remTime'] = $daytext;
                } elseif($value['type'] == 1) {
                    // 已激活
                    $isAudition = 0;
                    $getVideoLog = $this->isChecked($value['person_id'], $value['product_id'], $isAudition,$productStatus);
                } else {
                    // 禁用
                    $isAudition = 1;
                    $getVideoLog = $this->isChecked($value['person_id'], $value['product_id'], $isAudition,$productStatus);
                }

                $courseName = $this->getVideoClassName($value['product_id'], $value['is_audition'],$productStatus);

                $yesAudition = Db::name('order_person_son')->where(['person_id' => $person_id, 'product_id' => $value['product_id'], 'is_audition' => 0, 'id' => ['neq', $value['id']]])->find();
                // if ($yesAudition) {
                //     unset($sonList[$key]);
                //     continue;
                // }

                // $sonList[$key]['isActive'] = $value['type'] == 0 ? '待激活' : (($value['type'] == 1) ? '已激活' : '已停用');
                if ($value['is_forbidden'] == 1) {
                    $sonList[$key]['isActive'] = '订单已作废';
                } else {
                    $sonList[$key]['isActive'] =  $value['orderCheck'] == 1 ? '待审核' : (($value['orderCheck'] == 2 && $value['type'] ==1) ? '已激活' : (($value['orderCheck'] == 2 && $value['type'] ==0) ? '待激活' : '已停用'));

                }

                $sonList[$key]['remTime'] = $getVideoLog['remTime'];
                $sonList[$key]['courseName'] = $courseName;
            }
            else {
                if ($value['type'] == 0) {
                    $daytext = '210天';
                    $getVideoLog['remTime'] = $daytext;
                } elseif ($value['type'] == 1) {
                    $time = time();
                    $ordertime = strtotime($value['endtime']) + 210 * 24 * 3600;
                    $row = $ordertime - $time;
                    if ($row < 0) {
                        $getVideoLog['remTime'] = '已过期';
                    } else {
                        $getVideoLog['remTime'] = $this->time2string($row);
                    }
                    
                } else {
                    $time = time();
                    $ordertime = strtotime($value['endtime']) + 210 * 24 * 3600;
                    $row = $ordertime - $time;
                    if ($row < 0) {
                        $getVideoLog['remTime'] = '已过期';
                    } else {
                        $getVideoLog['remTime'] = $this->time2string($row);
                    }

                }
                
                $courseName = '试听课';
    
                if ($value['is_forbidden'] == 1) {
                    $sonList[$key]['isActive'] = '订单已作废';
                } else {
                    $sonList[$key]['isActive'] = ($value['orderCheck'] == 1) ? '待审核' : (($value['orderCheck'] == 2 && $value['type'] == 1) ? '已激活' : (($value['orderCheck'] == 2 && $value['type'] ==0) ? '待激活' : '已停用'));

                }

                $sonList[$key]['remTime'] = $getVideoLog['remTime'];
                $sonList[$key]['courseName'] = $courseName;
            }
            
        }

        return $sonList;
    }

    /**
     * 根據order_id 獲取課程權限
     */
    public function getCourseAuthByOrderId($order_id)
    {
        $sonList = Db::name('order_person_son')->where(['order_id' => $order_id])->select();
        if (!$sonList) {
            return array();
        }
  
        foreach ($sonList as $key => $value) {
            if ($value['video_class_id'] && $value['is_audition'] != 1) {
                if ($value['type'] == 0) {
                    $effectivedays = Db::name('video_class')->where(['id' => $value['video_class_id']])->value('effectivedays');
                    if (!empty($effectivedays)) {
                        $daytext = $effectivedays.'天';
                    } else {
                        $daytext = '210天';
                    }
                    $getVideoLog['remTime'] = $daytext;
                } elseif ($value['type'] == 1) {
                    $isAudition = 0;
                    $getVideoLog = $this->isChecked($value['person_id'], $value['video_class_id'], $isAudition);
                } else {
                    $isAudition = 1;
                    $getVideoLog = $this->isChecked($value['person_id'], $value['video_class_id'], $isAudition);
                }
                
                $courseName = $this->getVideoClassName($value['video_class_id'], $value['is_audition']);
    
                // $sonList[$key]['isActive'] = $value['type'] == 0 ? '待激活' : (($value['type'] == 1) ? '已激活' : '已停用');
                if ($value['is_forbidden'] == 1) {
                    $sonList[$key]['isActive'] = '订单已作废';
                } else {
                    $sonList[$key]['isActive'] = ($value['orderCheck'] == 1) ? '待审核' : (($value['orderCheck'] == 2 && $value['type'] == 1) ? '已激活' : (($value['orderCheck'] == 2 && $value['type'] ==0) ? '待激活' : '已停用'));
    
                }
                
                $sonList[$key]['remTime'] = $getVideoLog['remTime'];
                $sonList[$key]['courseName'] = $courseName;
            }
            elseif ($value['product_id'] && $value['is_audition'] != 1) {
                $productStatus=1;
                if ($value['type'] == 0) {
                    $effectivedays = Db::name('product')->where(['id' => $value['product_id']])->value('effectivedays');
                    if (!empty($effectivedays)) {
                        $daytext = $effectivedays.'天';
                    } else {
                        $daytext = '210天';
                    }
                    $getVideoLog['remTime'] = $daytext;
                } elseif ($value['type'] == 1) {
                    $isAudition = 0;
                    $getVideoLog = $this->isChecked($value['person_id'], $value['product_id'], $isAudition,$productStatus);
                } else {
                    $isAudition = 1;
                    $getVideoLog = $this->isChecked($value['person_id'], $value['product_id'], $isAudition,$productStatus);
                }

                $courseName = $this->getVideoClassName($value['product_id'], $value['is_audition'],$productStatus);

                // $sonList[$key]['isActive'] = $value['type'] == 0 ? '待激活' : (($value['type'] == 1) ? '已激活' : '已停用');
                if ($value['is_forbidden'] == 1) {
                    $sonList[$key]['isActive'] = '订单已作废';
                } else {
                    $sonList[$key]['isActive'] = ($value['orderCheck'] == 1) ? '待审核' : (($value['orderCheck'] == 2 && $value['type'] == 1) ? '已激活' : (($value['orderCheck'] == 2 && $value['type'] ==0) ? '待激活' : '已停用'));

                }

                $sonList[$key]['remTime'] = $getVideoLog['remTime'];
                $sonList[$key]['courseName'] = $courseName;
            }
            else {
                if ($value['type'] == 0) {
                    $daytext = '210天';
                    $getVideoLog['remTime'] = $daytext;
                } elseif ($value['type'] == 1) {
                    $time = time();
                    $ordertime = strtotime($value['endtime']) + 210 * 24 * 3600;
                    $row = $ordertime - $time;
                    if ($row < 0) {
                        $getVideoLog['remTime'] = '已过期';
                    } else {
                        $getVideoLog['remTime'] = $this->time2string($row);
                    }
                    
                } else {
                    $time = time();
                    $ordertime = strtotime($value['endtime']) + 210 * 24 * 3600;
                    $row = $ordertime - $time;
                    if ($row < 0) {
                        $getVideoLog['remTime'] = '已过期';
                    } else {
                        $getVideoLog['remTime'] = $this->time2string($row);
                    }

                }
                
                $courseName = '试听课';
    
                if ($value['is_forbidden'] == 1) {
                    $sonList[$key]['isActive'] = '订单已作废';
                } else {
                    $sonList[$key]['isActive'] = ($value['orderCheck'] == 1) ? '待审核' : (($value['orderCheck'] == 2 && $value['type'] == 1) ? '已激活' : (($value['orderCheck'] == 2 && $value['type'] ==0) ? '待激活' : '已停用'));
    
                }

                $sonList[$key]['remTime'] = $getVideoLog['remTime'];
                $sonList[$key]['courseName'] = $courseName;
            }
            
        }

        return $sonList;

    }

    /**
     * 根据person_id 、 video_class_id、 is_auduation 查询video_log表 ,获取卡是否激活、剩余时间
     */
    public function isChecked($person_id, $video_class_id, $isAudition,$productStatus=0)
    {
        $arr = array();
        if($productStatus==1){
            $videoLog = Db::name('video_log')->where(['person_id' => $person_id, 'product_id' => $video_class_id, 'type' => $isAudition])->find();
        }else{
            $videoLog = Db::name('video_log')->where(['person_id' => $person_id, 'video_class_id' => $video_class_id, 'type' => $isAudition])->find();
        }
        
        if ($isAudition == 1) {
            // 禁用
            $time = $videoLog['expireTime'];
        } else {
            // 激活
            $time = $videoLog['expireTime'] - time();
            if ($time <= 0) {
                $time = 0;
            }
        }


        if ($videoLog) {
            $arr = [
                'isActive' => 1,
                'remTime'  => $this->time2string($time)
            ];
        } else {
            $arr = [
                'isActive' => 0,
                'remTime'  => ''
            ];
        }
        return $arr;
    }

    public function getVideoClassName($video_class_id, $isAudition,$productStatus=0)
    {
        if($productStatus==1){
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
                ->where(['a.id' => $video_class_id])
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
                ->where(['a.id' => $video_class_id])
                ->find();
        }

            if ($res) {
                $courseName = $res['title'].'-'. (($isAudition == 1) ? '(试听)' : '');
                $sub = substr($courseName,-1);
                if($sub == '-'){
                    $courseName = substr($courseName,0,-1);
                }
            }

        return $courseName;
    }

    public function getCourseNameString($video_class_id,$product_id)
    {
        $res = Db::name('video_class')->alias('a')
                    ->field("a.*, b.subject,
                            concat(c.grade, b.subject,
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
                    ->where(['a.id' => ['in', $video_class_id]])
                    ->select();
        $res2 = Db::name('product')->alias('a')
            ->field("a.*, b.subject,
                            concat(c.grade, b.subject,
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
            ->where(['a.id' => ['in', $product_id]])
            ->select();

        if (!$res && !$res2) {
            return false;
        }
        if ($res && $res2){
            $ress = array_merge($res,$res2);
            $arr = array_column($ress, 'title');
            return implode('—', $arr);
        }
        if($res){
            $arr = array_column($res, 'title');
            return implode('—', $arr);
        }
        if($res2){
            $arr = array_column($res2, 'title');
            return implode('—', $arr);
        }

    }

    /**
     * 获取当前管理员的试听课、正式课数量
     * @param $video_class_id 课程id
     * @param $type  课程类型  1正常 2试听
     */
    public function getCourseNum($video_class_id, $type)
    {
        $uid = $_SESSION['think']['manageinfo']['uid']; 
        $group_id = $_SESSION['think']['manageinfo']['group_id']; // 1管理员
        $count = 0;
        if ($group_id != 1) {
            $agentId = $this->judgeIsAgent($uid);
            if (!$agentId) {
                jsonMsg('该用户分组或代理分组不存在', 1);
            }
            $count = Db::name('user_code')->where(['video_class_id' => $video_class_id, 'user_id' => $agentId, 'type' => $type])->value('code_num');
        }
        return $count;
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
     * 判断当前登录用户是不是代理
     * @param $uie int 用户id
     * 
     */
    public function judgeIsAgent($uid)
    {
        // 查看用户分组
        $userGroup = Db::name('user')->where(['uid' => $uid])->find();
        
        if (!$userGroup) {
            return false;
        }

        if ($userGroup['user_type'] == 1) {
            return $userGroup['uid'];
        } else {
            return $userGroup['agent_id'];
        }
        
        // return $userGroup['agent_id'];
        // if (!$group) {
        //     return false;
        // }
        // if ($group['type'] == 1) {
        //     // 代理
        //     return $agentId = $userGroup['uid'];
        // } else {
        //     // 查找上级代理
        //     return self::judgeIsAgent($userGroup['parent_id']);
        // }
    }


    /**
     * 判断用户属于什么角色
     * @param $uid int 用户id
     */
    public function getUserGroup($uid)
    {
        $type = false;
        // 查看用户分组
        $userGroup = Db::name('user')->where(['uid' => $uid])->find();
        if (!$userGroup) {
            $type = false;
        }
        

        // 查看分组对应的角色
        $group = Db::name('group')->where(['id' => $userGroup['group_id'], 'status' => 1])->find();
        if ($group) {
            $type = $group['type'];
        }
        return $type;
    }

    /**
     * 判断用户的代理id
     * @param $uid int 用户id
     */
    public function getCheckFinance($uid)
    {
        $type = false;
        // 查看用户分组
        $userGroup = Db::name('user')->where(['uid' => $uid])->find();
        if (!$userGroup) {
            return $type = false;
        }

        if ($userGroup['user_type'] == 1) {
            return $type = $userGroup['uid'];
        }
        // 查看分组对应的角色
        $group = Db::name('group')->where(['id' => $userGroup['group_id'], 'status' => 1])->find();
        
        if (!$group) {
            $type = false;
        } else {
            $type = $group['agent_id'];
        }

        return $type;
    }

    /**
     * 判断用户是否有订单并返回订单信息
     */
    public function judgeOrder($video_class_id, $person_id,$subjectid=0)
    {
        //科目>9的是产品
        if($subjectid>9){
// 正常课
            $res = Db::name('order_person_son')
                ->field('product_id as video_class_id, orderCheck, type, is_audition')
                ->where(['product_id' => $video_class_id, 'person_id' => $person_id, 'orderCheck' => ['in', [1, 2]], 'is_forbidden' => 0, 'is_audition' => 0])
                ->find();
            if (!$res) {
                // 试听课
                $aud = Db::name('order_person_son')
                    ->field('product_id as video_class_id, orderCheck, type, is_audition')
                    ->where(['product_id' => $video_class_id, 'person_id' => $person_id, 'orderCheck' => ['in', [1, 2]], 'is_forbidden' => 0, 'is_audition' => 1])
                    ->find();
            }
        }else{
            // 正常课
            $res = Db::name('order_person_son')
                ->field('video_class_id, orderCheck, type, is_audition')
                ->where(['video_class_id' => $video_class_id, 'person_id' => $person_id, 'orderCheck' => ['in', [1, 2]], 'is_forbidden' => 0, 'is_audition' => 0])
                ->find();
            if (!$res) {
                // 试听课
                $aud = Db::name('order_person_son')
                    ->field('video_class_id, orderCheck, type, is_audition')
                    ->where(['video_class_id' => $video_class_id, 'person_id' => $person_id, 'orderCheck' => ['in', [1, 2]], 'is_forbidden' => 0, 'is_audition' => 1])
                    ->find();
            }
        }


        $result = !empty($res) ? $res : (!empty($aud) ? $aud : array());

        // 查询课程剩余时间
        if ($result) {
            $suplerTime = $this->getSuplerCourseTime($result['video_class_id'], $person_id,$subjectid);
            $result['suplerTime'] = $suplerTime;
        }

        return $result;

    }

    /**
     * 获取课程剩余时间
     */
    public function getSuplerCourseTime($video_class_id, $person_id,$subjectid=0)
    {
        $suplerTime = 0;
        if($subjectid>9){
            $videoLog = Db::name('video_log')->field('product_id, type, expireTime')->where(['product_id' => $video_class_id, 'person_id' => $person_id])->find();
        }else{
            $videoLog = Db::name('video_log')->field('video_class_id, type, expireTime')->where(['video_class_id' => $video_class_id, 'person_id' => $person_id])->find();
        }

        if ($videoLog['type'] == 1) {
            $suplerTime = $videoLog['expireTime'];
        } else {
            $suplerTime = $videoLog['expireTime'] - time();
        }
        $suplerTime = timeTostring($suplerTime);
        return $suplerTime;
    }

    /**
     * 时间戳转换格式
     */
    public function time2string($second)
    {
        if ($second < 60) {
            return '已过期';
        }
        $day = floor($second/(3600*24));
        $second = $second%(3600*24);//除去整天之后剩余的时间
        $hour = floor($second/3600);
        $second = $second%3600;//除去整小时之后剩余的时间 
        $minute = floor($second/60);
        $second = $second%60;//除去整分钟之后剩余的时间 
        //返回字符串

        return $day.'天'.$hour.'小时'.$minute.'分';
    }

}