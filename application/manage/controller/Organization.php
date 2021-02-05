<?php 	
namespace app\manage\controller;

use think\Controller;
use think\Request;
use think\Db;
use think\Session;
use think\Image;
// use app\manage\model\organizational;
class Organization extends author{
	// +----------------------------------------------------------------------
	// | 组织架构管理
	// +----------------------------------------------------------------------
	//组织架构列表页面
	public function organization(){
		return $this->fetch('/organization/organization');
	}

	public function getOrganizationalList(){
		if( $_SESSION['think']['manageinfo']['user_type'] ==1){
			$agent_id = $_SESSION['think']['manageinfo']['uid'];
		}else{
			$agent_id = $_SESSION['think']['manageinfo']['agent_id'];
		}
		$list = Db::name('user_organization')->alias('uo')
				->join('guard_user u','u.uid = uo.agent_id')
				// ->join('guard_group g','g.id = uo.group_id')
				->field('uo.id,uo.pid,uo.status,uo.name as title,u.username as user_name')
				->where(['uo.agent_id'=>$agent_id])
				->select();
		echo json_encode($list);
	}
	public function updstatus(){
		$param = input();
		// 要修改的id
		$where['id'] = $param['id'];
		//状态值
		$where['status'] = $param['status'];
		$res= Db::name('user_organization') ->update($where);
		if($res){
			jsonMsg('修改成功',1);
		}
	}
	public function deltree(){
		$param = input();
		//将id组装成数组格式
		$id = explode(',', $param['id']);
		$res = $this ->delAllData($id);
		if($res){
			jsonMsg('删除成功',1);
		}
	}
	//递归删除
	public function delAllData($id){
		$res = Db::name('user_organization') ->delete($id);
		//查询对应id下的子节点
		$list = Db::name('user_organization') ->field('id,pid') ->where('pid','in',$id) ->select();
		if(!empty($list)){
			foreach($list as $key =>&$val){
				$ids[] = $val['id']; 
			}
			//去重后递归删除
			$ids = array_unique($ids);
			return $this ->delAllData($ids);
		}else{
			return true;
		}
	}
	public function organizationAdd(){
		$param = input();
		if(Request::instance()->isAjax()){
			//权限名称
			$where['name'] = isset($param['name'])?$param['name']:'';
			//父id
			$where['pid'] = $param['pid'];
			if($param['isadd'] == 0){
				if($_SESSION['think']['manageinfo']['user_type'] == 1){
					//添加
					$where['create_user'] = $_SESSION['think']['manageinfo']['uid'];
					if($_SESSION['think']['manageinfo']['user_type'] ==1){
						$agent_id = $_SESSION['think']['manageinfo']['uid'];
					}else{
						$agent_id = $_SESSION['think']['manageinfo']['agent_id'];
					}
					$where['agent_id'] = $agent_id;
					$where['create_time'] = time();
					$res = Db::name('user_organization') ->insert($where);
				}else{
					jsonMsg('只有代理商才能添加哦',0);
				}
			}else if($param['isadd'] == 1){
				//修改
				$where['id'] = $param['pid'];
				unset($where['pid']);
				$res = Db::name('user_organization') ->update($where);
			}
			if($res){
				jsonMsg('success',1);
			}
		}
		$this ->assign('isadd',$param['isadd']);
		$this ->assign('pid',$param['pid']);
		return $this ->fetch();
	}
	//获取表单数据
	public function getFormData(){
		$param = input();
		//编辑的id
		$id = $param['pid'];
		$list = Db::name('user_organization') ->field('name') ->where(['id' =>$id]) ->find();
		//拼装数据
		$_list['name'] = $list['name'];
		ajaReturn($_list,0,'success');
	}
	public function selectOrganizationalList(){
		if($_SESSION['think']['manageinfo']['user_type'] ==1){
			$agent_id = $_SESSION['think']['manageinfo']['uid'];
		}else{
			$agent_id = $_SESSION['think']['manageinfo']['agent_id'];
		}
		$list = Db::name('user_organization')->where(['agent_id'=>$agent_id,'status'=>1])->field('id,name,pid as tId')->select();
		//组装树 数据
		foreach($list as $key => &$val){
			$list[$key]['open'] = false;
			$list[$key]['checked'] = false;
		}
		unset($val);
		echo json_encode($this->tree($list));
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
	public function organizationsAdd(){
		$param = input();
		//判断是否是ajax请求
		if(Request::instance()->isAjax()){
			//权限名称
			if($_SESSION['think']['manageinfo']['user_type'] == 1){
				$where['name'] = isset($param['name'])?$param['name']:'';
				//父id
				$where['pid'] = $param['pid'];
				$where['create_user'] = $_SESSION['think']['manageinfo']['uid'];
					if($_SESSION['think']['manageinfo']['user_type'] ==1){
						$agent_id = $_SESSION['think']['manageinfo']['uid'];
					}else{
						$agent_id = $_SESSION['think']['manageinfo']['agent_id'];
					}
				$where['agent_id'] = $agent_id;
				$where['create_time'] = time();
				$res = Db::name('user_organization') ->insert($where);
				jsonMsg('success',1);
			}else{
				jsonMsg('只有代理商才能添加哦!',0);
			}
		}
		return $this ->fetch();
	}
}