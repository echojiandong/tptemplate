<?php 	
namespace app\manage\controller;
use think\Controller;
use think\Request;
use think\Db;
use think\Session;
use think\Image;
class Tag extends author{
	// +----------------------------------------------------------------------
	// | 下载资料标签表
	// +----------------------------------------------------------------------
	//标签列表页面
	public function index(){
		return $this->fetch('/tag/index');
	}
	//获取标签列表
	public function getTagList(){
		$list = Db::name('tag')->field('*,name as title')->select();
		foreach ($list as $key => $value) {
			if($value['type'] == 1){
				$list[$key]['type_name'] = '下载标签';
			}else{
				$list[$key]['type_name'] = '视频标签';
			}	
		}
		echo json_encode($list);
	}
	// //修改
	public function updstatus(){
		$param = input();
		// 要修改的id
		$where['id'] = $param['id'];
		//状态值
		$where['is_show'] = $param['is_show'];
		$res= Db::name('tag') ->update($where);
		if($res){
			jsonMsg('修改成功',1);
		}
	}
	//删除
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
		$res = Db::name('tag') ->delete($id);
		//查询对应id下的子节点
		$list = Db::name('tag') ->field('id,pid') ->where('pid','in',$id) ->select();
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
	//添加
	public function tagAdd(){
		$param = input();
		if(Request::instance()->isAjax()){
			//权限名称
			$where['name'] = isset($param['name'])?$param['name']:'';
			//父id
			$where['pid'] = $param['pid'];
			//标签类型
			if(isset($param['type'])){
				$where['type'] = $param['type'];
			}
			if($param['isadd'] == 0){
				//添加
				$res = Db::name('tag') ->insert($where);
			}else if($param['isadd'] == 1){
				//修改
				$where['id'] = $param['pid'];
				unset($where['pid']);
				$res = Db::name('tag') ->update($where);
			}
			if($res){
				jsonMsg('success',1);
			}
		}
		$this ->assign('isadd',$param['isadd']);
		$this ->assign('pid',$param['pid']);
		$this ->assign('type',$param['type']);
		return $this ->fetch();
	}
	//获取表单数据
	public function getFormData(){
		$param = input();
		//编辑的id
		$id = $param['pid'];
		$list = Db::name('tag') ->field('name') ->where(['id' =>$id]) ->find();
		//拼装数据
		$_list['name'] = $list['name'];
		ajaReturn($_list,0,'success');
	}
	public function selectTagList(){
		$list = Db::name('tag')->field('id,name,pid as tId')->select();
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
	public function tagsAdd(){
		$param = input();
		//判断是否是ajax请求
		if(Request::instance()->isAjax()){
			//权限名称
			$where['name'] = isset($param['name'])?$param['name']:'';
			//父id
			$where['pid'] = $param['pid'];
			$where['type'] = $param['type'];
			$res = Db::name('tag') ->insert($where);
			jsonMsg('success',1);
		}
		return $this ->fetch();
	}
}