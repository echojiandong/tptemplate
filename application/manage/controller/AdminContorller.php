<?php
namespace app\manage\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\Image;
use app\manage\model\Menu;
use app\manage\model\User;
class AdminContorller extends author
{
	public  $paytype = array(
		array(
			'id'=>1,
			'name'=>'微信'
		),
		array(
			'id'=>2,
			'name'=>'支付宝'
		)
	);
	/*分组管理*/
	public function property()
	{
		return $this->fetch("admin/adminGroup");
	}
	// 调取分组管理数据
	public function getadmingrouplist()
	{
		 //$res=User::getadmingrouplist();
		 $data = DB::name('group')->select();
		  $count= DB::name('group')->count();
		if($data){
			//jsonMsg("success","0",$data,$count);
			$arr=json_encode(
				array(
					'code'=>0,
					'msg'=>'',
					'count'=>$count,
					'data'=>$data
				)
			);
			echo $arr;
		}else{
			jsonMsg('暂时没有内容',1);
		}
	}
	// 添加分组页面
	public function showaddgroup()
	{
		return $this->fetch("admin/adminGroup");
	}
	// 添加功能方法
	public function addgroup(Request $request)
	{
		$param = $request->param();
		    $data = DB::name('menu')->field('id,title,parentid,icon')->where(['type'=>1])->order('menuid asc')->select();
		    $menu=Menu::getMenuId($param['uid']);
		    $menu=array_column($menu,'menu_id');
		    foreach($data as $k=>$v){
		        if(in_array($v['id'],$menu)){
		            $data[$k]['check']='on';
		        }else{
		            $data[$k]['check']='off';
		        }
		    }
		    $result = tree($data);
		    $this->assign("list",$result);
		    $this->assign('uid',$param['uid']);
		return $this->fetch("admin/addgroup");
	}
	public function addgroupList(Request $request)
	{
		$param=$request->param();
	}
	public function groupList()
	{
		$rts=Db::name('menu')->select();
		$List=array();
		$i=0;
		foreach($rts as $k=>$v){
			if($v['id']!=6){
				$List[$i]['id']=$v['id'];
				$List[$i]['pId']=$v['parentid'];
				$List[$i]['name']=$v['title'];
				if($v['parentid']==0){
					$List[$i]['open']=true;
				}
				$i++;
			}
		}
		$List=json_encode($List);
		jsonMsg("success","0",$List);
	}
	public function powermenu(Request $request){//显示菜单管理界面
	    $param = $request->param();
	    $data = DB::name('menu')->field('id,title,parentid,icon')->where(['type'=>1])->order('menuid asc')->select();
	    $menu=DB::name('user_group')->field('menu_id')->where(['groupid'=>$param['id']])->select();
	    $menu=array_column($menu,'menu_id');
	    foreach($data as $k=>$v){
	        if(in_array($v['id'],$menu)){
	            $data[$k]['check']='on';
	        }else{
	            $data[$k]['check']='off';
	        }
	    }
	    $result = tree($data);
	    $this->assign("list",$result);
	    $this->assign('groupid',$param['id']);
	    return $this->fetch('power/menulist');
	}

	//后台分组管理-权限编辑（给管理员授予menu管理权限）
	public function grouppower(Request $request){
	    $param = $request->param();
	    $arr=array();
	    $where['groupid']=!empty($param['groupid']) ? $param['groupid'] : '';
	    DB::name('user_group')->where($where)->delete();
	    if(!empty($param['menu'])){
		    foreach($param['menu'] as $k=>$v)
		    {
		        $arr[$k]['menu_id']=$v;
		        $arr[$k]['groupid']=$param['groupid'];
		    }
		    $rs=DB::name('user_group')->insertAll($arr);
		    if($rs){jsonMsg("成功",0);}else{jsonMsg("失败",1);}
		}
		return jsonMsg("成功",0);
	}
	public function powerid(Request $request){
		//根据当前用户的groupID获取权限等级
		$auth = Db::name('group')->field('auth')->where('id = '.$_SESSION['think']['manageinfo']['group_id'])->select();

		$param = $request->param();
		$data['title']=$param['name'];
		$data['auth']=$param['auth'];

		//限制权限等级
		if(!$auth || intval($data['auth']) > $auth[0]['auth']){

			jsonMsg("失败",1);
		}
		$data['time']=time();
		if(isset($param['id'])){
			$data['id']=$param['id'];
			$res=Db::name('group')->update($data);
		}else{
			$res=Db::name('group')->insert($data);
		}
	
		if($res){
			jsonMsg("成功",0);
		}else{
			jsonMsg("失败",1);
		}	
	}
	// 后台 管理员 列表页面
	public function adminList()
	{
		return $this->fetch("admin/adminList");
	}
	public  function getadminlist(Request $request)
	{
		$param = $request->param();
		$page = $param['page'];
		$limit = $param['limit'];
		if($_SESSION['think']['manageinfo']['group_id'] != 1){

			//获取当前子类uid
			$id_list = Db::name('user')
						->field('uid')
						->where('parent_id = '.$_SESSION['think']['manageinfo']['uid'])
						->select();
			$uid = array();
			if(isset($id_list)){
				foreach ($id_list as $key => $value) {
					$uid[] = $value['uid']; 
				}
			}
			$uid[] = $_SESSION['think']['manageinfo']['uid'];

			//获取管理员数据
			$where['u.uid'] = array('in',$uid);
			$res=Db::name('user')
				->alias('u')
				->join('guard_group g','u.group_id=g.id')
				->where($where)
				->field('u.*,g.auth,g.title')
				->page($page,$limit)
				->select();
			$count=Db::name('user')
					->alias('u')
					->join('guard_group g','u.group_id=g.id')
					->where($where)
					->field('u.*,g.auth,g.title')
					->count();

		}else{	
			$res=Db::name('user')
				->alias('u')
				->join('guard_group g','u.group_id=g.id')
				->field('u.*,g.auth,g.title')
				->page($page,$limit)
				->select();
			$count=Db::name('user')
					->alias('u')
					->join('guard_group g','u.group_id=g.id')
					->field('u.*,g.auth,g.title')
					->count();
		}
		foreach ($res as $key => $value) {
			if($value['paytype'] == 1){
				$res[$key]['paytype'] = '微信';
			}elseif($value['paytype'] == 2){
				$res[$key]['paytype'] = '支付宝';
			}
		}
		if($res){
			//jsonMsg("success","0",$data,$count);
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
	// 添加 管理员 页面
	public function addadmin()
	{
		//获得当前管理员的权限等级
		$auth_now = Db::name('group')->field('auth')->where('id = '.$_SESSION['think']['manageinfo']['group_id'])->select();

		if($_SESSION['think']['manageinfo']['group_id'] != 1){
			$res=Db::name('group')->where('auth <'.$auth_now[0]['auth'])->select();
		}else{
			$res=Db::name('group')->select();
		}
		$this->assign('group',$res);
		$this->assign('paytype',$this->paytype);
		return $this->fetch("admin/addadmin");
	}
	
	// 上传图片接口
    public function uploadImg()
    {
    	$file = request()->file('file');
        if($file){
            $info = $file->move(ROOT_PATH . 'public/upload' . DS . 'admin');
            if($info){
                jsonMsg("成功",0,"/upload/admin/".$info->getSaveName());
            }else{
                jsonMsg("失败",1,$file->getError());
            }
        }
    }

	// 添加用户方法
	public function  adminAddt(Request $request)
	{
		$param=$request->param();
		$data['username']=$param['username'];
		// if(!preg_match('/^[A-Za-z]{1}[A-Za-z0-9_-]{3,10}$/',$data['username'] )){
        //         return jsonMsg('请输入3-10位的数字或者字母组合！',1);
        // }
		$data['phone']=$param['phone'];
		$data['address']=$param['address'];
		$data['password']=md5(md5($param['password']));
		$data['remark']=$param['remark'];
		$data['wechat_num']=$param['wechat_num'];
		$data['nickName']=$param['nickName'];
		$data['group_id']=$param['newsType'];
		$data['paytype']=$param['paytype'];
		$data['payImg']=$param['payImg'];

		//管理添加 //当前用户的权限等级
		$auth_now = Db::name('group')->field('auth')->where('id = '.$_SESSION['think']['manageinfo']['group_id'])->select();  
		$auth_add = Db::name('group')->field('auth')->where('id = '.$data['group_id'])->select();  //添加用户的权限等级
  
  		if($auth_now[0]['auth'] <= $auth_add[0]['auth']){
  			jsonMsg("添加失败","1");
  		}
		//添加父级ID
		if($data['group_id'] != 1){
			$data['parent_id'] = $_SESSION['think']['manageinfo']['uid'];
		}else{
			$data['parent_id'] = 0;
		}
		$data['user_type']=0;
		//判断 用户昵称是否存在
		$result = Db::name('user')->where('username = "'.$data['username'].'"')->select();
		if(empty($result)){
			$res=Db::name('user')->insert($data);
			if($res){
				jsonMsg("添加成功","0");
			}else{
				jsonMsg("添加失败","1");
			}
		}else{
			jsonMsg("该账号已存在！","1");
		}
	}
	//删除管理员分组
	public function delAdminGroup(Request $request)
	{
		$param=$request->param();
		$where['id']=$param['id'];

		//管理删除 //当前用户的权限等级
		$auth_now = Db::name('group')->field('auth')->where('id = '.$_SESSION['think']['manageinfo']['group_id'])->select();  
		$auth_del = Db::name('user')->alias('u')->join('guard_group g','u.group_id = g.id')->field('g.auth')->where('g.id = '.$where['id'])->select();  //添加用户的权限等级
  
  		if($auth_now[0]['auth'] <= $auth_del[0]['auth']){
  			jsonMsg("删除失败","1");
  		}

		$group=new menu();
		$res=$group->delAdminGroup($where);
		if($res){
			jsonMsg("success","0");
		}
	}
	//封禁管理员账号
	public function Prohibition(Request $request)
	{
		$param=$request->param();
		$where['uid']=$param['id'];
		$data['status']=2;
		$user=new User();
		$res=$user->Prohibition($where,$data);
		if($res){
			jsonMsg('success','0');
		}else{
			jsonMsg('error','1');
		}
	}
	//解除封禁管理员账号
	public function LiftingOfProhibition(Request $request)
	{
		$param=$request->param();
		$where['uid']=$param['id'];
		$data['status']=1;
		$user=new User();
		$res=$user->LiftingOfProhibition($where,$data);
		if($res){
			jsonMsg('success','0');
		}else{
			jsonMsg('error','1');
		}
	}
	//编辑管理员账号名称
	public function editAdmin(Request $request)
	{
		$param=$request->param();
		$where['uid']=$param['uid'];
		//获取目前登录的管理员信息
		$auth_now = Db::name('group')->field('auth')->where('id = '.$_SESSION['think']['manageinfo']['group_id'])->select(); 
		//获取管理员信息
		$userList = Db::name('user')->where($where)->select();
		if($_SESSION['think']['manageinfo']['group_id'] != 1){
			$res=Db::name('group')->where('auth <'.$auth_now[0]['auth'])->select();
		}else{
			$res=Db::name('group')->select();
		}
		$host = $request->domain();
		$this->assign('group',$res);
		$this->assign('host',$host);
		$this->assign('userList',$userList);
		$this->assign('paytype',$this->paytype);
		return $this->fetch('admin/editAdmin');
	}
	//执行编辑管理员账号名称
	public function doEditAdmin(Request $request)
	{
		$param=$request->param();
		$where['uid']=$param['uid'];
		$data['username']=$param['username'];
		if(!preg_match('/^[A-Za-z]{1}[A-Za-z0-9_-]{3,8}$/',$data['username'] )){
                return jsonMsg('请输入3-8位的数字或者字母组合！',1);
        }
		$data['phone']=$param['phone'];
		$data['address']=$param['address'];
		$data['remark']=$param['remark'];
		$data['wechat_num']=$param['wechat_num'];
		$data['nickName']=$param['nickName'];
		$data['group_id']=$param['group_id'];
		$data['payImg']=$param['payImg'];
		$data['paytype']=$param['paytype'];
		$user=new User;
		$result = Db::name('user')->where('username = "'.$data['username'].'" and uid !='.$param['uid'])->select();
		if(empty($result)){
			$res=$user->editAdmin($where,$data);
			if($res){
				jsonMsg('修改成功','0');
			}else{
				jsonMsg('修改失败','1');
			}
		}else{
			jsonMsg("该账号已存在！","1");
		}
	}
	//修改管理员密码页面
	public function changePass(){
		$uid = $_SESSION['think']['manageinfo']['uid'];
		$user = Db::name('user')->where('uid='.$uid)->select();
		$this->assign('userInfo',$user);
		return $this->fetch('/admin/editPassword');
	}
	//修改管理员密码
	public function ChangePassword(Request $request)
	{
		$param=$request->param();
		$where['uid']=$param['id'];
		$data['password']=md5(md5($param['ChangePassword']));
		$user=new User();
		$res=$user->ChangePassword($where,$data);
		if($res){
			jsonMsg('success','0');
		}else{
			jsonMsg('error','1');
		}
	}
	//删除管理员账号
	public function delAdmin(Request $request)
	{
		$param=$request->param();
		$where['uid']=$param['id'];
		//管理删除 //当前用户的权限等级
		$auth_now = Db::name('group')->field('auth')->where('id = '.$_SESSION['think']['manageinfo']['group_id'])->select();  
		$auth_del = Db::name('user')->alias('u')->join('guard_group g','u.group_id = g.id')->field('g.auth')->where('u.uid = '.$where['uid'])->select();  //删除用户的权限等级
  
  		if($auth_now[0]['auth'] <= $auth_del[0]['auth']){
  			jsonMsg("删除失败","1");
  		}
		$user=new User();
		$res=$user->delAdmin($where);
		if($res){
			jsonMsg("success","0");
		}else{
			jsonMsg("success","1");
		}
	}
}
