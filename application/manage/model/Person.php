<?php
namespace app\manage\model;
use think\Model;
use Phinx\Db\Table;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\Image;
class Person extends Model
{
	protected $members = array();
	/**
     * 获取用户列表数据
     * @author 韩春雷 2019.3.19
     * @DateTime 2019-02-27T14:28:40+0800
     * @param    array       $where [筛选条件]
     * @param    int         $page  [页码]
     * @param    int         $limit [每页数量]
     * @return   [array]                  
     */
	
	public function getPersonList($page=null,$limit=null, $where1=null, $type=0)
	{
		$group_id = $_SESSION['think']['manageinfo']['group_id'];
		$user_id  = $_SESSION['think']['manageinfo']['uid'];
		$user_type  = $_SESSION['think']['manageinfo']['user_type'];
		$agent_id = $_SESSION['think']['manageinfo']['agent_id'];
		
		$uid = array();
		if($group_id == 1 || $group_id == 2){
			$userIdArr = array();
			$isAdmin = isset($where1['isAdmin']) && !empty($where1['isAdmin']) ? true : false;
			if ($isAdmin) {
				unset($where1['isAdmin']);
			}
			if ($type == 1) {
				// 我的用户
				$where1['user_id'] = $user_id;
				$personList = Db::name('person')->where($where1)->page($page,$limit)->order('addtime desc')->select();
				$count = Db::name('person')->where($where1)->count();
			} else {
				// 所有用户
				if (isset($where1['user_id']) && !empty($where1['user_id'])) {
					$agent_id = $where1['user_id'];
					// 获取赛选代理商下的所有用户
					$userId = Db::name('user')->where(['agent_id' => $where1['user_id'], 'status' => 1, 'user_type' => 2])->select();
					$res = array_column($userId, 'uid');
					
					$userIdArr = $res;
					$userIdArr[] = $where1['user_id'];
					unset($where1['user_id']);
					$personList = Db::name('person')->where($where1)->where(['user_id' => ['in', $userIdArr]])->page($page,$limit)->order('addtime desc')->select();
					$count = Db::name('person')->where($where1)->where(['user_id' => ['in', $userIdArr]])->count();
				} else {
					if ($group_id == 2) {
						// 获取超级管理员
						$userid = Db::name('user')->where(['group_id' => 1])->value('uid');
						$userId = Db::name('user')->where(['agent_id' => $userid, 'status' => 1, 'user_type' => 2])->select();
						$res = array_column($userId, 'uid');
						$res[] = $userid;
						//只查看我的代理商
						if($type == 3){
							$res[] = $user_id;
						}
						$where1['user_id'] = ['not in', $res];
					}else{
                        if($type == 3){
                            $where1['user_id'] = ['neq', $user_id];
                        }
                    }
					// 获取所有
					$personList = Db::name('person')->where($where1)->page($page,$limit)->order('addtime desc')->select();
					$count = Db::name('person')->where($where1)->count();
				}

			}
			
			//获取用户列表
			
			// $personList = Db::name('person')->where($where1)->page($page,$limit)->order('addtime desc')->select();
			// $count = Db::name('person')->where($where1)->count();
			$personList = self::changPersonList($personList);
	
			if ($isAdmin) {
				foreach ($personList as $key =>$value) {
					if ($value['user_id']) {
						$info = Db::name('user')->where(['uid' => $value['user_id']])->find();
						if ($info['user_type'] == 1) {
							$username = $info['username'];
						} else {
							$username = Db::name('user')->where(['uid' => $info['agent_id']])->value('username');
						}
						$personList[$key]['username'] = $username;
					}
				}
			}

			$personList['count'] = $count;
		} else {
			if ($user_type != 1) {
                $user_orgid = $_SESSION['think']['manageinfo']['org_id'];
            }else{
                $user_orgid = $user_id;
			} 
			
			// 我的用户
			if ($type != 1) {
				$this->getAllPersonSon($user_orgid,$type);
				$uid = $this->members;
				$uid = array_column($uid, 'uid');
			}
			//当type！= 3的时候查看我的用户
			if($type != 3){
				$uid[] = $user_id;
			}

			//转换数据类型
			// $uid = implode(',',$uid);
			$where['user_id'] = array('in',$uid);
		
			//获取用户列表
			// $personList = Db::name('person')->where($where)->where($where1)->where($whereOr)->order('addtime desc')->page($page,$limit)->fetchSql(true)->select();
			$personList = Db::name('person')->where($where)->where($where1)->order('addtime desc')->page($page,$limit)->select();

			$count = Db::name('person')->where($where)->where($where1)->count();
			$personList = self::changPersonList($personList);
			$personList['count'] = $count;
			
		}
		return $personList;
	}

	/**
     * 变量转换
     * @author 韩春雷 2019.3.19
     * @DateTime 2019-02-27T14:28:40+0800
     * @param    array       $list [筛选条件]
     * @return   [array]                  
     */
	public static function changPersonList($list){
		//获取年级列表
		$gradeList = Db::name('grade')->select();
		foreach ($gradeList as $key => $value) {
			$grade[$value['id']] = $value['grade'];
		}
		if($list){
			foreach ($list as $key => $value) {
				if(isset($value['act_status'])){
					if($value['act_status'] == 1){
						$list[$key]['act_status_name'] = '未激活';
					}elseif($value['act_status'] == 2){
						$list[$key]['act_status_name'] = '已激活';
					}else{
						$list[$key]['act_status_name'] = '停用';
					}
				}
				// if(isset($value['gradeAuth'])){
				// 	$gradeAuth = explode(',',$value['gradeAuth']);
				// 	for($i=0; $i<count($gradeAuth); $i++){
				// 		$gradeAuth[$i] = $grade[$gradeAuth[$i]];
				// 	}
				// 	$list[$key]['gradeAuth'] = implode(',',$gradeAuth);
				// }else{
				// 	$list[$key]['gradeAuth'] = '暂无看课权限';
				// }
				if(isset($value['up_time'])){
					$list[$key]['up_time'] = date('Y-m-d',$value['up_time']);
				}
				// if(isset($value['grade_id'])){
				// 	$list[$key]['grade'] = $grade[$value['grade_id']];
				// }
				if(isset($value['addtime'])){
					$list[$key]['addtime'] = date('Y-m-d H:i:s', $value['addtime']);
				}
				if(isset($value['son_addtime'])){
					$list[$key]['son_addtime'] = date('Y-m-d', $value['son_addtime']);
				}
				if(isset($value['birthday'])){
					$list[$key]['birthday'] = date('Y-m-d', $value['birthday']);
				}
				if(isset($value['user_id'])){
					$userList = Db::name('user')->where('uid = '.$value['user_id'])->find();
					if(!empty($userList)){
						$list[$key]['username'] = $userList['username'];
					}else{
						$list[$key]['username'] = '游客';
					}
				}else{
					$list[$key]['username'] = '游客';
				}
			}
		}
		return $list;
	}

	/**
	 * 获取用户下的所有员工的id ，除了代理商
	 */

	public function getAllPersonSon($uid,$type)
	{
		$user_type = $_SESSION['think']['manageinfo']['user_type'];
		//当前用户是代理商
		if($user_type==1)
        {
        	//------------------查看所有的所属代理商以及代理商所有的员工------------------//
        	if($type == 3){
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
        	if($type != 3){
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



	/**
	 * 获取用户详情
	 * @author 杨继州 2019-07-03
	 * @param $id  int 用户id
	 */
	public function getPersonInfoById($id)
	{
		if (!$id) return false;
		$personInfo = Db::name('person')->alias('a')->field('a.*, b.name as media_name')->join('guard_to_media b', 'a.to_media = b.id', 'left')->where(['a.id' => $id])->find();
		if (!$personInfo) {
			return false;
		}
		
		return $personInfo;
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
        // 查看分组对应的角色
        $group = Db::name('group')->where(['id' => $userGroup['group_id'], 'status' => 1])->find();
       
        if (!$group) {
            return false;
        }
        if ($group['type'] == 1) {
            // 代理
            return $agentId = $userGroup['uid'];
        } else {
            // 查找上级代理
            return self::judgeIsAgent($userGroup['parent_id']);
        }
    }
}