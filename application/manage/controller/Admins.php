<?php 	
namespace app\manage\controller;

use think\Controller;
use think\Request;
use think\Db;
use think\Session;
use think\Image;
use app\manage\model\Admins as Ma;
/**
 * @author 薛少鹏 xsp15135921754@163.com
 * @DateTime 2019-06-06T10:32:16+0800
 *重写rbac
 */
class Admins extends author{
	/**
	 * [menuManagement 菜单管理]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-06T10:37:05+0800
	 */
	public function menuManagement(){

		return $this ->fetch();
	}
	/**
	 * [getreelist 获取tabletree列表]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-11T16:14:59+0800
	 * @return   [json]
	 */
	public function getreelist(){
		$list = Ma::newM() ->getreelist();
		echo json_encode($list);
	}
	/**
	 * [menuadd 顶部导航栏的添加]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-11T16:15:52+0800
	 * @return   [json]
	 */
	public function menuadd(){
		$param = input();
		//判断是否是ajax请求
		if(Request::instance()->isAjax()){
			//权限名称
			$where['title'] = isset($param['name'])?$param['name']:'';
			//权限状态
			$where['status'] = isset($param['status'])?$param['status']:'';
			//跳转链接
			$where['href'] = isset($param['link'])?$param['link']:'';
			//父id
			$where['parentid'] = $param['pid'];
			//菜单介绍
			$where['body'] = isset($param['body'])?$param['body']:'';
			$res = Db::name('menu') ->insert($where);
			$msg['data'] = $res?$res:'';
			$msg['code'] = $res?0:1001;
			$msg['msg'] = $res?'请求成功':'请求失败';
			ajaReturn($msg['data'],$msg['code'],$msg['msg']);
		}
		return $this ->fetch();
	}
	/**
	 * [menusadd table右侧添加]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-11T16:18:23+0800
	 * @return   [json]
	 */
	public function menusadd(){
		$param = input();
		if(Request::instance()->isAjax()){
			//权限名称
			$where['title'] = isset($param['name'])?$param['name']:'';
			//权限状态
			$where['status'] = isset($param['status'])?$param['status']:'';
			//跳转链接
			$where['href'] = isset($param['link'])?$param['link']:'';
			//父id
			$where['parentid'] = $param['pid'];
			//菜单介绍
			$where['body'] = isset($param['body'])?$param['body']:'';
			if($param['isadd'] == 0){
				//添加
				$res = Db::name('menu') ->insert($where);
			}else if($param['isadd'] == 1){
				//修改
				$where['id'] = $param['pid'];
				unset($where['parentid']);
				$res = Db::name('menu') ->update($where);
			}
			$msg['data'] = $res?$res:'';
			$msg['code'] = $res?0:1001;
			$msg['msg'] = $res?'请求成功':'请求失败';
			ajaReturn($msg['data'],$msg['code'],$msg['msg']);
		}
		$this ->assign('isadd',$param['isadd']);
		$this ->assign('pid',$param['pid']);
		return $this ->fetch();
	}
	/**
	 * [setformval 编辑表单初始赋值]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-11T16:19:36+0800
	 * @return   [type]                   [description]
	 */
	public function setformval(){
		$param = input();
		//编辑的id
		$id = $param['pid'];
		$list = Db::name('menu') ->field('title,status,href,body') ->where(['id' =>$id]) ->find();
		//拼装数据
		$_list['name'] = $list['title'];
		$_list['body'] = $list['body'];
		$_list['link'] = $list['href'];
		$_list['status'] = (string)$list['status'];
		ajaReturn($_list,0,'success');
	}
	/**
	 * [selectree 下拉树]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-11T16:20:40+0800
	 * @return   [json]
	 */
	public function selectree(){
		//获取列表
		$list = Ma::newM() ->getselectree();
		//组装树 数据
		foreach($list as $key => &$val){
			$list[$key]['open'] = false;
			$list[$key]['checked'] = false;
		}
		unset($val);
		echo json_encode($this ->tree($list));

	}
	/**
	 * [deltree 递归删除树的子节点]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-11T16:22:17+0800
	 */
	public function deltree(){
		$param = input();
		//将id组装成数组格式
		$id = explode(',', $param['id']);
		$res = Ma::newM() ->deltree($id);
		$msg['code'] = $res?0:1001;
		$msg['msg'] = $res?'success':'error';
		ajaReturn('', $msg['code'], $msg['msg']);
	}
	/**
	 * [updstatus 状态开关按钮的修改]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-11T16:23:14+0800
	 */
	public function updstatus(){
		$param = input();
		// 要修改的id
		$where['id'] = $param['id'];
		//状态值
		$where['status'] = $param['status'];
		$res= Db::name('menu') ->update($where);
		$msg['code'] = $res?0:1001;
		$msg['msg'] = $res?'success':'error';
		ajaReturn('', $msg['code'], $msg['msg']);
	}
	// +----------------------------------------------------------------------
	// | 角色管理
	// +----------------------------------------------------------------------
	public function roleManagement(){

		return $this ->fetch();
	}
	/**
	 * [roletable 角色列表接口]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-13T11:02:34+0800
	 */
	// public function roletable(){
	// 	$param = input();
	// 	//查询条件
	// 	$userinfo = Session::get('manageinfo');
	// 	$where['agent_id'] = $userinfo['uid'];
	// 	// 查询字段
	// 	$field = "id,title,time,status,agent_id,remarks";
	// 	$page = isset($param['page'])?$param['page']:1;
	// 	$limit = isset($param['limit'])?$param['limit']:10;
	// 	$list = Db::name('group') ->field($field) 
	// 							  ->where($where)
	// 							  ->page($page,$limit)
	// 							  ->select();
	// 	$lists = Db::name('group') ->field($field) 
	// 							   ->select();
	// 	foreach($lists as &$val){
	// 		$data[$val['id']] = $val['title'];
	// 	}
	// 	foreach($list as $key =>&$val){
	// 		$list[$key]['time'] = date('Y-m-d',$val['time']);
	// 	}
	// 	$count = Db::name('group') ->field("count(1) as counts") 
	// 							   ->where($where)
	// 							   ->find();
	// 	ajaReturn($list,0,'success',$count['counts']);
	// }

	/**
	 * 权限列表
	 * @author yangjihzou 
	 */
	public function roletable()
	{
		$param = input();
		$page = isset($param['page'])?$param['page']:1;
		$limit = isset($param['limit'])?$param['limit']:10;

		$group_id = $_SESSION['think']['manageinfo']['group_id'];
		$agent_id = $_SESSION['think']['manageinfo']['agent_id'];
		$user_id = $_SESSION['think']['manageinfo']['uid'];
		$user_type = $_SESSION['think']['manageinfo']['user_type'];
		if ($group_id == 1) {
			$list = Db::name('group')->alias('a')->field('a.*, b.title as pidName')->join('guard_group b', 'a.pid = b.id', 'left')->where(['a.agent_id' => $user_id])->page($page,$limit)->select();
			$count = Db::name('group')->where(['agent_id' => $user_id])->count(1);
		} else {
			if ($user_type == 1) {
				// 代理
				$list = Db::name('group')->alias('a')->field('a.*, b.title as pidName')->join('guard_group b', 'a.pid = b.id', 'left')->where(['a.agent_id' => $user_id])->page($page, $limit)->select();
				$count = Db::name('group')->where(['agent_id' => $user_id])->count(1);
			} else {
				// 员工
				$list = Ma::newM()->getUserGroup($group_id);
				$count = count($list);
				
				$list = array_slice($list, $limit*($page - 1), $limit); // 分页
			}
		}
		ajaReturn($list,0,'success',$count);
	}

	/**
	 * [updrolemsg 添加编辑时的页面跳转]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-13T11:03:46+0800   添加角色管理2019-7-19高金辉
	 */
	public function updrolemsg()
	{
		$param = input();
		$user_id = $_SESSION['think']['manageinfo']['uid'];
		$user_type = $_SESSION['think']['manageinfo']['user_type'];
		$group_id = $_SESSION['think']['manageinfo']['group_id'];
		$agent_id = $_SESSION['think']['manageinfo']['agent_id'];
		$groupList = array();
		if ($group_id == 1) {
			$groupList = Db::name('group')->where(['agent_id' => $user_id])->select();
		} else {
			if ($user_type == 1) {
				// 代理
				$groupList = Db::name('group')->where(['agent_id' => $user_id])->select();
			} else {
				// 员工
				$groupList = Ma::newM()->getUserGroup($group_id);
			}
		}
		
		$userGroup = Db::name('group')->where(['id' => $group_id])->find();
		
		//有权限组
		if ($groupList) {

			$userGroup['level'] = 0; // 最大级
			$groupList[] = $userGroup;

			$groupList = $this->resort($groupList, $group_id);  // 组装数据
			$groupList[] = $userGroup;
		} else {
			// 没有权限组=》 显示最大权限组
			$groupList[] = $userGroup;
		}
		
		$this->assign('groupList', !empty($groupList) ? $groupList : array());
		//区分添加（!= 0）、编辑( = 0)
		$this ->assign('id', $param['id']);
		$this ->assign('type', $param['type']);
		return $this ->fetch();
	}	

	
	/**
	 * [setgroupval role表单初始赋值]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-13T11:05:03+0800
	 */
	public function setgroupval(){
		$param = input();
		// 权限等级
		// $field = 'title as name,auth,parentid';
		$field = 'title as name,remarks, pid';
		$list = Db::name('group') ->field($field) ->where(['id' => $param['id']]) ->find();
		ajaReturn($list,0);
	}
	/**
	 * [updrolestatus 角色锁定操作]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-13T11:05:49+0800
	 */
	public function updrolestatus(){
		$param = input();
		$id = isset($param['id'])?$param['id']:'';
		$status = isset($param['status'])?$param['status']:'';
		if($id == '' || $status == ''){
			ajaReturn('',1001,'error');
		}
		$res = Db::name('group') ->where(['id' => $id]) ->update(['status' => $status]);

		$msg['code'] = $res?0:1001;
		$msg['msg'] = $res?'success':'error';
		ajaReturn('', $msg['code'], $msg['msg']);
	}
	/**
	 * [rolemenutree 角色菜单树接口]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-13T11:06:33+0800
	 */
	public function rolemenutree(){
		$param = input();
		
		//获取列表树格式
		$list = Ma::newM() ->rolemenutree($param['id']);
		//递归处理树结构
		$data = $this ->tree($list);
		ajaReturn($data,0);

	}
	/**
	 * [setrolemenus 添加、编辑角色时的处理]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-13T11:08:00+0800
	 */
	public function setrolemenus(){
		$param = input();
		$res = Ma::newM() ->setrolemenus($param);
		$msg['code'] = $res?0:1001;
		$msg['msg'] = $res?'success':'error';
		ajaReturn('', $msg['code'], $msg['msg']);
	}
	/**
	 * [deleterole 删除角色时，对删除数据的冗余操作]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-13T11:08:38+0800
	 */
	public function deleterole(){
		$param = input();

		$roleid = $param['id'];
		// 数据库类型改为innob 时可以回滚

		$group = Db::name('group')->where(['pid' => $roleid])->select();
		if ($group) {
			// jsonMsg('该角色下有子角色， 不能删除， 请先删除子角色', 1001);
			ajaReturn('', 1001, '该角色下有子角色， 不能删除， 请先删除子角色');
		}
		// 删除关联表的数据
		$is_del = Db::name('user_group') ->where(['groupid' => $roleid]) ->delete();
		//查找管理员表中的管理员
		// $userids = Db::name('user') ->field('uid') ->where(['group_id' => $roleid]) ->select();
		// 删除角色组
		$group_arr = Db::name('group') ->where(['id' => $roleid]) ->delete();
		//删除角色下面的权限
		$res=Db::name('user_group')->where(['groupid' => $roleid])->delete();
		//最后删除管理员中的管理员
		// $userids = Db::name('user') ->where(['group_id' => $roleid]) ->delete();
		
		$msg['code'] = $is_del && $group_arr && $res ? 0 : 1001;

		$msg['msg'] = $is_del && $group_arr && $res ?'success':'error';
		ajaReturn('', $msg['code'], $msg['msg']);

	}

	// +----------------------------------------------------------------------
	// | 用户管理
	// +----------------------------------------------------------------------
	public function userManagement()
	{
		$group_id = $_SESSION['think']['manageinfo']['group_id'];

		$this->assign('isAdmin', $group_id == 1 ? true : false);
		return $this ->fetch();
	}
	/**
	 * [usertreetable 用户table接口（不显示）]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-14T11:27:20+0800
	 */
	public function usertreetable(){
		//测试需要（开发时可以去掉）
		$param = input();
		$type = isset($param['type']) && !empty($param['type']) ? $param['type'] : 1; 
		
		$tree_list = Ma::newM()->usertreetable($param, $type);
		
		
		ajaReturn($tree_list['t'],0,'success',$tree_list['c']['counts']);
	}
	/**
	 * [upduserstatus 用户状态修改接口（不显示）]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-14T11:28:37+0800
	 */
	public function upduserstatus(){
		$param = input();
		
		$id = isset($param['id'])?$param['id']:'';
		$status = isset($param['status'])?$param['status']:'';
		if($id == '' || $status == ''){
			ajaReturn('',1001,'error');
		}
		$res = Db::name('user') ->where(['uid' => $id]) ->update(['status' => $status]);

		$msg['code'] = $res?0:1001;
		$msg['msg'] = $res?'success':'error';
		ajaReturn('', $msg['code'], $msg['msg']);
	}
	/**
	 * [deluserstree 递归删除用户列表（不显示）]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-14T11:30:49+0800
	 * @return   [type]                   [description]
	 */
	public function deluserstree()
	{
		$group_id = $_SESSION['think']['manageinfo']['group_id'];
        $user_type = $_SESSION['think']['manageinfo']['user_type'];
		$org_id = $_SESSION['think']['manageinfo']['org_id'];
		
		$param = input();
		//将id组装成数组格式
		$id = $param['id'];
		if (empty($id)) {
			jsonMsg('删除账号不存在', 1);
		}
		$isAgent = Db::name('user')->where(['uid' => $id])->find();
		
		if ($isAgent['user_type'] == 1) {
			// 代理
			$list = Db::name('user')->where(['agent_id' => $id, 'user_type' => 2])->column('uid');
			$list[] = $id;
			
		} else {
			// 员工
			// $userOrg = Db::name('user')->where(['uid' => $id])->find();
			// $orgid = Ma::newM()->getAllOrgSon($userOrg['org_id']);
			// $orgid = array_column($orgid, 'id');
			// $list = Db::name('user')->where(['org_id' => ['in', $orgid]])->column('uid');
			
			$list[] = $id;
		}

		$status = $isAgent['status'] == 2 ? 1 : 2;
		
		Db::startTrans();
		try {
			Db::name('user')->where(['uid' => ['in', $list]])->update(['status' => $status]);
			Db::commit();
			jsonMsg('禁用成功', 0);
		} catch(\Exention $e) {
			Db::rollback();
			jsonMsg('禁用失败', 1);
		}

	}

	
	/**
	 * [useradd 用户添加接口（不显示）]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-14T11:31:17+0800
	 */
	public function useradd(){
		$param = input();
		$type = isset($param['type']) ? true : false;
		$userinfo = Session::get('manageinfo');
		$group_id = $_SESSION['think']['manageinfo']['group_id'];
		$user_id  = $_SESSION['think']['manageinfo']['uid'];
		$user_type  = $_SESSION['think']['manageinfo']['user_type'];
		$org_id  = $_SESSION['think']['manageinfo']['org_id'];
		$agent_id  = $_SESSION['think']['manageinfo']['agent_id'];

		// if($userinfo['parent_id'] == 0){
		// 	$role_list = Db::name('group') ->field('id,title') ->select();
		// }

		if($group_id == 1)
		{
			$agentList = Db::name('user') ->field('uid,username') ->where('user_type = 1')->select();
			$this->assign('agentList',$agentList);
		}

		// if(!isset($role_list) && $userinfo['uid'] != $param['id']){
		// 	$role_list = Db::name('group') ->field('id,title')
		// 							   	   ->where(['status' => 1]) 
		// 							   	   ->select();
		// }
		// if(!isset($role_list)){

		// 	$role_list = Db::name('group') ->field('id,title')
		// 								   ->where(['id' => $userinfo['group_id']])
		// 							   	   ->where(['status' => 1]) 
		// 							   	   ->select();
		// }

		if ($group_id == 1) {
			// admin
			$role_list = Db::name('group')->where(['status' => 1])->select();
		} else {
			if ($user_type == 1) {
				// 代理
				$role_list = Db::name('group')->where(['agent_id' => $user_id, 'status' => 1])->select();
			} else {
				// 员工
				// $role_list = Db::name('group')->field('id, title')->where(['agent_id' => $agent_id, 'status' => 1])->select();
				$role_list = Ma::newM()->getUserGroup($group_id);
				
			}
		}
		
		// 查找当前登录的权限组
		$userGroup = Db::name('group')->where(['id' => $group_id])->find();
		array_push($role_list, $userGroup);
		
		if ($type) {
			$uidAgent = Db::name('user')->alias('a')->field('a.*, b.agent_id')->join('guard_group b', 'a.group_id = b.id')->where(['a.uid' => $userinfo['uid']])->find();
			if ($uidAgent['user_type'] == 1) {
				$agent_id = $uidAgent['uid'];
			}else {
				$agent_id = $uidAgent['agent_id'];
			}
			$orgList = Db::name('user_organization')->where(['agent_id' => $agent_id])->select();
			$orgList = $this->resort($orgList, $uidAgent['org_id']);
			$this->assign('userOrg', $uidAgent['org_id']);
		}

		$role_list = $this->resorts($role_list, $group_id);

		$this ->assign('role_list',$role_list);
		$this ->assign('id', $userinfo['uid']);//当前登陆人的id
		$this ->assign('eid', $param['id']);//id=0添加代理商id不等于0编辑代理商
		$this->assign('type', $type);
		$this->assign('adminTxt', $type ? '员工' : '管理员');
		$this->assign('orgList', isset($orgList) ? $orgList : array());

		return $this ->fetch();
	}

	public function resorts($data,$parentid=0,$level=0)
	{
		static $rets=array();
		
		foreach($data as $k=>$v){
			
			if($v['pid']==$parentid){
				$v['level']=$level;
				$rets[]=$v;
				$this->resorts($data,$v['id'],$level+1);
			}
		}
		
		return $rets;
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
	 * [uploadimg 上传图片接口（不显示）]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-14T11:32:09+0800
	 */
	public function uploadimg(){
		$file = request()->file('file');
        if($file){
            $info = $file->move(ROOT_PATH . 'public/upload' . DS . 'admin');
            if($info){
            	$file = $info->getSaveName();
            	unset($info);
            	ajaReturn("/upload/admin/".$file,0,'success');
            }else{
            	$error = $file->getError();
            	unset($info);
            	ajaReturn($file->getError(),1001,'error');
            }
        }
	}
	/**
	 * [setuserval 用户编辑表单初始赋值]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-14T11:41:04+0800
	 */
	public function setuserval(){
		$param = input();
		$list = Db::name('user') ->field('username, org_id, phone,wechat_num,address,group_id,paytype,payImg,remark') 
					     ->where(['uid' => $param['id']]) 
						 ->find();
		
		ajaReturn($list,0,'success');
	}
	/**
	 * [updusermsg 后台用户添加、修改接口（不显示）]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-06-14T11:41:42+0800  添加与编辑代理商2019-7-19 高金辉
	 */
	public function updusermsg()
	{
		$param = input();

		$user_type  = $_SESSION['think']['manageinfo']['user_type'];
		$agent_id  = $_SESSION['think']['manageinfo']['agent_id'];
		$user_id  = $_SESSION['think']['manageinfo']['uid'];
		$group  = $_SESSION['think']['manageinfo']['group_id'];
		$type = isset($param['type']) ? $param['type'] : '';
		unset($param['type']);
		// （大坑） nginx 注释掉   apache 放开注释（多一个参数）
		//array_shift($param);
		$userinfo = Session::get('manageinfo');
		
		unset($param['/manage/admins/updusermsg']);
		$group_id = isset($param['group_id']) && !empty($param['group_id']) ? $param['group_id'] : '';
		if($param['eid']==0)
		{//添加代理商
			$username = str_replace(' ', '', $param['username']);
			if (!$username) {
				ajaReturn('',1001,'用户名不能为空');
			}

			if($group_id && $group_id == 1){
				ajaReturn('',1001,'超管只能一个哦');
			}
			$_is = Db::name('user') ->where(['username' => $username]) 
									->find();
			if($_is){
				ajaReturn('',1001,'账号名已存在！');
			}

			$param['password'] = isset($param['password'])?md5(md5($param['password'])):'';
			unset($param['file']);
			unset($param['eid']);
			// if(isset($param['u_id'])){
			// 	$param['parent_id'] = (int)$param['u_id'];
			// 	$param['agent_id'] = (int)$param['u_id'];
			// }else{
			// 	$param['parent_id'] = Session::get('manageinfo')['uid'];
			// 	$param['agent_id'] = Session::get('manageinfo')['uid'];
			// }
			// unset($param['u_id']);
			$param['status'] = 1;
			
			if ($type) {
				$param['user_type'] = 2; // 员工
				if (!isset($param['org_id']) || empty($param['org_id'])) {
					ajaReturn('', 1001, '请选择所属部门');
				}
			} else {
				$param['user_type']=1;//标记为代理商
			}

			if ($group == 1) {
				if(isset($param['u_id'])){
					$param['parent_id'] = (int)$param['u_id'];
					$param['agent_id'] = (int)$param['u_id'];
				}else{
					$param['parent_id'] = Session::get('manageinfo')['uid'];
					$param['agent_id'] = $agent_id;
				}
			} else {
				if ($user_type == 1) {
					$param['agent_id'] = $user_id;
				} else {
					$param['agent_id'] = $agent_id;
				}
				$param['parent_id'] = Session::get('manageinfo')['uid'];
			}
			unset($param['u_id']);
			
			$res = Db::name('user') ->insert($param);
			$msg['code'] = $res?0:1001;
			$msg['msg'] = $res?'success':'error';
			ajaReturn('',$msg['code'],$msg['msg']);
		}else{//编辑代理商
			// unset($param['password']);
			// $img_url = (Db::name('user') ->field('payImg') ->where(['uid' => $param['id']]) ->find())['payImg'];
			// if(file_exists(ROOT_PATH . 'public/'.$img_url) && is_file(ROOT_PATH . 'public/'.$img_url) && $img_url != $param['payImg']){
			// 	unlink(ROOT_PATH . 'public'.$img_url);
			// }
			$param['password'] = isset($param['password']) && !empty($param['password']) ? md5(md5($param['password'])) : '';
			if (!$param['password']) {
				unset($param['password']);
			}

			
			$_id=$param['eid'];
			if (!$_id) {
				ajaReturn('',1001,'非法操作，编辑信息不存在');
			}

			//当编辑信息是超管时，编辑信息不变
			if($_id == '24'){
				$param['group_id'] = '1';
				$param['parent_id'] = '0';
				$param['agent_id'] ='0';
			}


			unset($param['eid']);
			unset($param['file']);

			// if(isset($param['u_id'])){
			// 	$param['parent_id'] = (int)$param['u_id'];
			// 	$param['agent_id'] = (int)$param['u_id'];
			// }else{
			// 	$param['parent_id'] = Session::get('manageinfo')['uid'];
			// 	$param['agent_id'] = Session::get('manageinfo')['uid'];
			// }

			$_is = Db::name('user') ->where(['username' => $param['username'], 'uid' => ['neq', $_id]])->find();
			if($_is){
				ajaReturn('',1001,'账号名已存在！');
			}

			
			$param['status'] = 1;
			if ($type) {
				$param['user_type'] = 2; // 员工
				if (!isset($param['org_id']) || empty($param['org_id'])) {
					ajaReturn('', 1001, '请选择所属部门');
				}
			} else {
				$param['user_type']=1;//标记为代理商
			}

			if ($group == 1) {
				if(isset($param['u_id'])){
					if($_id != 24){
						$param['parent_id'] = (int)$param['u_id'];
						$param['agent_id'] = (int)$param['u_id'];
					}
				}else{
					$param['parent_id'] = Session::get('manageinfo')['uid'];
					$param['agent_id'] = (int)$param['u_id'];
				}
			} else {
				if ($user_type == 1) {
					$param['agent_id'] = $user_id;
				} else {
					$param['agent_id'] = $agent_id;
				}
				$param['parent_id'] = Session::get('manageinfo')['uid'];
			}
			unset($param['u_id']);
			
			$res = Db::name('user') ->where(['uid' => $_id])->update($param);
			ajaReturn('',0, 'success');
		}	
	}


	/**
	 * 员工数据列表
	 */
	public function employeeAccount()
	{
		$user_id = $_SESSION['think']['manageinfo']['uid'];
		$group_id = $_SESSION['think']['manageinfo']['group_id'];
		
		$uidAgent = Db::name('user')->alias('a')->field('a.*, b.agent_id')->join('guard_group b', 'a.group_id = b.id')->where(['a.uid' => $user_id])->find();
		if ($uidAgent['user_type'] == 1) {
			$agentid = $user_id;
		} else {
			$agentid = $uidAgent['agent_id'];
		}
		$orgList = Db::name('user_organization')->where(['agent_id' => $agentid])->select();
		
		$orgList = $this->resort($orgList, $uidAgent['org_id']);
		$this->assign('orgList', isset($orgList) && !empty($orgList) ? $orgList : array());
		return $this->fetch();
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