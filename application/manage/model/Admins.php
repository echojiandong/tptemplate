<?php
namespace app\manage\model;
use think\Model;
use think\Request;
use think\Session;
use think\Db;

class Admins extends Model{
	public $parmes;		
	protected $members = array();// 用户uid数组
	protected $org = array(); // 部门id数组
	protected $groupArr = array(); // 分组数组
	//参数列表
	public static function newM($parme = []){
		$model = new self();
		$model ->parmes = $parme;
		return $model;
	}
	/**
	 * [getreelist 获取tabletree所需数据]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-11T16:24:34+0800
	 * @return   [type]                   [description]
	 */
	public function getreelist(){
		$list = Db::name('menu')   ->field('id,parentid as pid,href,title,status')
								   ->order('menuid asc')
								   ->select();
		return $list;
	}
	/**
	 * [getselectree 获取下拉树所需数据]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-11T16:25:01+0800
	 * @return   [type]                   [description]
	 */
	public function getselectree(){
		$list = Db::name('menu')	->field('id,title as name,parentid as tId')
									->order('menuid asc')
									->select();
		return $list;
	}
	/**
	 * [deltree 递归删除menu(默认)子节点]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-11T16:25:24+0800
	 * @param    [array]                   $id [要删除的id（最高父节点）]
	 */
	public function deltree($id, $table = 'menu'){
		$res = Db::name($table) ->delete($id);
		//查询对应id下的子节点
		$list = Db::name($table) ->field('id,parentid') ->where('parentid','in',$id) ->select();
		if(!empty($list)){
			foreach($list as $key =>&$val){
				$ids[] = $val['id']; 
			}
			//去重后递归删除
			$ids = array_unique($ids);
			return $this ->deltree($ids, $table);
		}else{
			return 1;
		}
	}

	/**
	 * [delusertree 递归删除用户表中的数据]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-13T09:31:23+0800
	 * @param  [array]           $id   [要删除的id（最高父节点)]
	 */
	// public function delusertree($id){

	// 	$res = Db::name('user')->delete($id);
	// 	//查询对应id下的子节点
	// 	$list = Db::name('user') ->field('uid,parent_id') ->where('agent_id','in',$id) ->select();
	// 	if(!empty($list)){
	// 		foreach($list as $key =>&$val){
	// 			$ids[] = $val['uid']; 
	// 		}
	// 		unset($val);
	// 		//去重后递归删除
	// 		$ids = array_unique($ids);
	// 		return $this ->delusertree($ids);
	// 	}else{
	// 		return 1;
	// 	}
	// }

	public function delusertree($id, $type)
	{


		Db::startTrans();
		
		try {
			$res = Db::name('user')->where(['uid' => $id])->update(['status' => 2]);
			//查询对应id下的子节点
			$list = Db::name('user')->field('uid,parent_id')->where(['agent_id' => $id, 'user_type' => 2])->select();
			$userId = array_column($list, 'uid');
			Db::commit();
			jsonMsg('删除成功', 0);
		} catch(\Exention $e) {

			Db::rollback();
			jsonMsg('删除失败', 1);
		}
		
		
		//查询对应id下的子节点
		$list = Db::name('user') ->field('uid,parent_id') ->where('agent_id','in',$id) ->select();
		if(!empty($list)){
			foreach($list as $key =>&$val){
				$ids[] = $val['uid']; 
			}
			unset($val);
			//去重后递归删除
			$ids = array_unique($ids);
			return $this ->delusertree($ids);
		}else{
			return 1;
		}
	}

	/**
	 * 获取当前用户分组下的所有分组
	 */
	public function getUserGroup($group_id)
	{
		$list = Db::name('group')->alias('a')->field('a.*, b.title as pidName')->join('guard_group b', 'a.pid = b.id', 'left')->where(['a.pid' => $group_id, 'a.status' => 1])->select();
		
		$this->groupArr = array_merge($this->groupArr, $list);

		foreach ($list as $key => $value) {
			$res = Db::name('group')->where(['pid' => $value['id']])->select();
			if ($res) {
				$this->getUserGroup($value['id']);
			}
			// $this->groupArr = array_merge($this->groupArr, $res);
			// $this->getUserGroup($value['id']);
		}

		return $this->groupArr;
	}

	public function resort($data,$parentid=0,$level=0)
	{
		static $ret=array();
		foreach($data as $k=>$v){
			if($v['pid']==$parentid){
				$v['level']=$level;
				$ret[]=$v;
				$this->resort($data,$v['id'],$level+1);
			}
		}
		return $ret;
	}

	/**
	 * [rolemenutree 处理layui.tree 所需格式]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-13T11:11:02+0800
	 * @param    [int]                   $id 
	 */
	public function rolemenutree($id)
	{
		//查找登陆人员的权限
		$userinfo = Session::get('manageinfo');
		$where['groupid']=$userinfo['group_id'];
		if($where['groupid'] != 1){
			$res=Db::name('user_group')->field('menu_id')->where($where)->select();
		}else{
			$res=Db::name('menu')->field('id as menu_id')->select();
		}
		$string = empty($res) ? [0] : array_column($res, 'menu_id');
		//查询菜单列表
		$list = Db::name('menu')	->field('id,title,parentid as tId')
									->where('id', 'in', $string)
									->order('menuid asc')
									->select();
		//如果是编辑，则初始赋值
		if($id != 0){
			$role_menu = Db::name('user_group') ->field('groupid,menu_id')
												->where(['groupid' => $id])
												->select();
			//循环对比进行初始赋值
			$rolemenulist = array();
			foreach($role_menu as &$val){
				$rolemenulist[] = $val['menu_id'];
			}
			unset($val);
			foreach($list as $key =>&$val){
				if(in_array($val['id'], $rolemenulist)){
					$list[$key]['checked'] = true;
				}
			}
		}
		return $list;

	}
	/**
	 * [setrolemenus 用户菜单权限的修改]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-13T11:15:25+0800
	 * @param    [array]                   $param [ajax 请求的参数值]
	 */
	public function setrolemenus($param){
		$menu_ids = trim($param['ids'],',');
		//表单提交的菜单id
		$ids = explode(',', $menu_ids);
		$data['title'] = $param['name'];
		$userinfo = Session::get('manageinfo');
		if ($userinfo['user_type'] == 1) {
			$agent_id = $userinfo['uid'];
		} else {
			$agent_id = $userinfo['agent_id'];
		}
		$data['agent_id'] = $agent_id;
		$data['time'] = time();
		$data['remarks']=$param['remarks'];
		$data['pid'] = $param['pid'];
		if($param['type'] ==2){
			//修改角色分组
			$where['id'] = $param['id'];		//要修改的组id
			$res = Db::name('group') ->where($where)->update($data);
			//查询用户之前的权限菜单id
			$menu = Db::name('user_group') ->field('id,menu_id,groupid')
										   ->where(['groupid' => $param['id']]) 
										   ->select();
			foreach($menu as &$val){
				$menu_id[] = $val['menu_id'];
			}
			unset($val);
			if(empty($menu_id) && !empty($ids)){
				//新增的菜单id
				foreach($ids as &$val){
					$add_arr[] = ['groupid' => $param['id'], 'menu_id' => $val];
				}
				$res1 = Db::name('user_group') ->insertAll($add_arr);
				return $res1?1:0;
			}
			if(empty($ids)){
				return 1;
			}
			$del_id = array_diff($menu_id,$ids);		//差集，要删除的菜单id
			$add_id = array_diff($ids,$menu_id);		//差集，要添加的菜单id
			if(!empty($add_id)){
				//新增的菜单id
				foreach($add_id as &$val){
					$add_arr[] = ['groupid' => $param['id'], 'menu_id' => $val];
				}
				$res1 = Db::name('user_group') ->insertAll($add_arr);
			}else{
				$res1 = 1;
			}
			if(!empty($del_id)){
				//查找要删除的菜单id 的主键id
				foreach($menu as &$val){
					if(in_array($val['menu_id'],$del_id)){
						$del_arr[] = $val['id']; 
					}
				}
				$res2 = Db::name('user_group') ->delete($del_arr);
			}else{
				$res2 = 1;
			}
			return $res1 && $res2?1:0;
			
		}
		if($param['type'] == 1){
			//添加角色分组
			$group_id = Db::name('group') ->insertGetId($data);
			foreach($ids as &$val){
				$arr[] = ['groupid' => $group_id, 'menu_id' => $val];
			}
			unset($val);
			$res = Db::name('user_group') ->insertAll($arr);
			return $res?1:0;
		}
	}


	public function usertreetable($param, $type)
	{
		$userInfo = Session::get('manageinfo');
		$user_type = $_SESSION['think']['manageinfo']['user_type'];
		$org_id = $_SESSION['think']['manageinfo']['org_id'];
		$agent_id = $_SESSION['think']['manageinfo']['agent_id'];

		$page = isset($param['page'])?$param['page']:1;
		$limit = isset($param['limit'])?$param['limit']:10;
		$where = ' user_type = 2 ';

		$orgid = isset($param['org_id']) && !empty($param['org_id']) ? $param['org_id'] : '';

		if ($type == 1) {
			if($userInfo['uid'] == 24){//超级管理员看到所有的代理商
				$where='u.user_type = 1';
			}else{//普通代理看到自己创建的代理的
				$where = 'u.user_type = 1  and u.agent_id = '.$userInfo['uid'].' ';
			}
		} else {
			$where1 = '';
			$user_uid = $userInfo['uid'];
			// 员工
			if ($user_type == 1) {
				// 当前登录为代理商
			
				$userId = Db::name('user')->where(['agent_id' => $user_uid, 'user_type' => 2])->select();
				$uidArr = array_column($userId, 'uid');
				if ($uidArr) {
					$uidArr[] = $user_uid;
					$userarr = implode(',', $uidArr);
				
					$where1 = " u.uid in ( $userarr)";
				} else {
					// $where1['u.uid'] = $user_uid;
					$where1 =" u.uid = $user_uid";
				}

				// $userId = $this->getAllPersonSon($user_uid);
			} else {
				// 当前登录为员工
				$org = $this->getAllOrgSon($org_id);
				if ($org) {
					$org = array_column($org, 'id');
					$org = implode(',', $org);
					$where1 = " u.org_id in ( $org ) ";
				} else {
					$where1 = " u.parent_id =  ".$userInfo['uid'].' ';
				}
				// $org = array_column($org, 'id');
				// $org = implode(',', $org);
				// $where1 = " u.org_id in ( $org ) ";
			}
			

			// $where = '';
			// $user_uid = $userInfo['uid'];
			
			// $userId = $this->getAllPersonSon($user_uid);
			// $uidArr = array_column($userId, 'uid');
			// if ($uidArr) {
			// 	$uidArr[] = $user_uid;
			// 	$userarr = implode(',', $uidArr);
			// 	$where = " u.user_type = 2 and u.uid in ( $userarr)";
			// } else {
			// 	$where =" u.user_type = 2 and u.uid = $user_uid";
			// }


			// $userarr = implode(',', $uidArr);
			// $where = " u.user_type =2 and u.uid in ($userarr)";
		}
		


		// 代理账号
		$where .= isset($param['name']) && $param['name'] != '' ? ' and u.username like "%'.$param['name'].'%"' : '' ;
		// 代理手机
		$where .= isset($param['phone']) && $param['phone'] != '' ? ' and u.phone like "%'.$param['phone'].'%"' : '' ;
		
		// 部门id
		$orgid = isset($param['org_id']) && !empty($param['org_id']) ? $param['org_id'] : '';
		
		if ($orgid) {
			unset($where1);
			$this->org = array(); // 清空条件
			$orgs = $this->getAllOrgSon($orgid);
			
			$org_idarr = array_column($orgs, 'id');
			if ($org_idarr) {
				$org_idarr[] = $orgid;
				$org_idarr = implode(',', $org_idarr);
				
				$where .= " and u.org_id in ( $org_idarr)";
			} else {
				$where .= " and u.org_id = $orgid";
				
			}
		} else {
			if (isset($where1) && !empty($where1)) {
				$where .= ' and '. $where1;
			}
		} 
		
		//超管 ，获取所有用户列表
		
		$user_arr = Db::name('user')->alias('u')
					->field('u.uid,u.username,u.phone, u.org_id,u.parent_id,u.paytype,u.wechat_num,u.status,u.address,u.agent_id,g.title as group_id, b.name as orgname')
					->join('guard_group g','u.group_id = g.id', 'left')
					->join('guard_user_organization b', 'u.org_id = b.id', 'left')
					->where($where)
					->page($page,$limit)
					->select();
	
		$array=$user_arr;
		foreach($user_arr as $k=>$v)
		{
			if($v['paytype']==1)
			{
				$user_arr[$k]['paytype']='支付宝';
			}else{
				$user_arr[$k]['paytype']='微信';
			}
			foreach($array as $val)
			{
				if($v['agent_id']==$val['uid'])
				{
					$user_arr[$k]['pid']=$val['username'];
				}
			}
		}
		$count = Db::name('user')->alias('u') ->where($where) ->field('count(1) as counts') ->find();
		return ['t' => $user_arr, 'c' => $count];
	}



	public function userAccountTreetable($param)
	{
		$userInfo = Session::get('manageinfo');
		$page = isset($param['page'])?$param['page']:1;
		$limit = isset($param['limit'])?$param['limit']:10;
	
		if($userInfo['uid'] == 24){//超级管理员看到所有的代理商
			$where='u.user_type = 1';
		}else{//普通代理看到自己创建的代理的
			$where = 'u.user_type = 1  and u.uid = '.$userInfo['uid'].' ';
		}


		// 代理账号
		$where .= isset($param['name']) && $param['name'] != '' ? ' and u.username like "%'.$param['name'].'%"' : '' ;
		// 代理手机
		$where .= isset($param['phone']) && $param['phone'] != '' ? ' and u.phone like "%'.$param['phone'].'%"' : '' ;
		
		//超管 ，获取所有用户列表
		
		$user_arr = Db::name('user')->alias('u')
					->field('u.uid,u.username,u.phone,u.parent_id,u.paytype,u.wechat_num,u.status,u.address,u.agent_id,g.title as group_id')
					->join('guard_group g','g.id=u.group_id')
					->where($where)
					->page($page,$limit)
					->select();
		$array=$user_arr;
		foreach($user_arr as $k=>$v)
		{
			if($v['paytype']==1)
			{
				$user_arr[$k]['paytype']='支付宝';
			}else{
				$user_arr[$k]['paytype']='微信';
			}
			foreach($array as $val)
			{
				if($v['agent_id']==$val['uid'])
				{
					$user_arr[$k]['pid']=$val['username'];
				}
			}
		}
		$count = Db::name('user')->alias('u') ->where($where) ->field('count(1) as counts') ->find();
		return ['t' => $user_arr, 'c' => $count];

	}

	/**
	 * 获取admin下的所有员工的id
	 */
	public function getAllPersonSon($uid)
	{
		$group_id = $_SESSION['think']['manageinfo']['group_id'];
		if ($group_id == 1) {
			$userList = Db::name('user')->where(['agent_id' => $uid, 'user_type' => 2])->select();
		} else {
			$userList = Db::name('user')->where(['agent_id' => $uid, 'user_type' =>2])->select();
		}
		
		$this->members = array_merge($this->members, $userList);
		if (count($userList) > 0) {
			foreach ($userList as $key => $value) {
				$this->getAllPersonSon($value['uid']);
			}
		}
		
		return $this->members;
	}

	/**
	 * 获取当前部门下的所有子部门
	 */
	public function getAllOrgSon($org_id)
	{
		
		$org = Db::name('user_organization')->where(['pid' => $org_id])->select();
		
		$this->org = array_merge($this->org, $org);
		
		if (count($org) > 0) {
			foreach ($org as $key => $value) {
				$this->getAllOrgSon($value['id']);
			}
		}
		
		return $this->org;
	}
	
}