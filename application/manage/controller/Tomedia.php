<?php
namespace app\manage\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;

class Tomedia extends author{

	public $agent_id;

	public $is_manage;
	public $group_id;

	public function _initialize(){

		parent::_initialize();

		$this->agent_id = Session::get('manageinfo')['uid'];

		$this ->group_id = Session::get('manageinfo')['group_id'];

		$this ->is_manage = Session::get('manageinfo')['user_type'];

		if($this->is_manage !=1){
			$this ->agent_id = Session::get('manageinfo')['agent_id'];
		}
	}

	public function list()
	{
		if ($this->group_id) {
			switch ($this->group_id) {
				case '1':
					$agentList = Db::name('user')->field('uid, username')->where(['user_type' => 1, 'group_id' => ['not in', [1, 2]]])->select();
					break;
				case '2':
					$agentList = Db::name('user')->field('uid, username')->where(['user_type' => 1, 'group_id' => ['not in', [1, 2]]])->select();
					break;
				default:
					break;
			}
		}

		$this->assign('agentList', isset($agentList) && $agentList ? $agentList : array());
		$this->assign('isAdmin', ($this->group_id == 1 || $this->group_id == 2) ? true : false);

		return $this->fetch('/tomedia/list');
	}
	
	/*
	 * table 初始赋值
	 */
	public function tomediatable()
	{
		$param = input();

		$page = isset($param['page']) ? $param['page'] : 1;

		$limit = isset($param['limit']) ? $param['limit'] : 10;
		$where = array();
		$where1 = array();

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
		} elseif ($orderStartTime && !$orderEndTime) {
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

		if(array_key_exists('keyword',$param) && $param['keyword']!=''){
            $where1['a.name']=['like',"%".$param['keyword']."%"];
		}
		
		if ($this->group_id != 1) {
			if ($this->group_id == 2) {
				$agentid = Db::name('user')->where(['group_id' => 1])->value('uid');
				$where1['a.agent_id'] = ['neq', $agentid];
			} else {
				$where1['a.agent_id'] = $this->agent_id;
			}
		}
		
		if(array_key_exists('agent_id',$param) && $param['agent_id'] != '' && $param['agent_id'] != 'undefined'){
            $where1['a.agent_id'] = $param['agent_id'];
		}
		

		$data = Db::name('to_media')->alias('a')
				->field('a.id, a.name, b.username')
				->join('guard_user b', 'a.agent_id = b.uid')
				->where($where1)
				->page($page, $limit)->select();

		foreach ($data as $key => $value) {
			$data[$key]['count'] = Db::name('person')->where(['to_media' => $value['id']])->where($where)->count(1);
		}
		
		$count = Db::name('to_media')->alias('a')->where($where1)->count();

		ajaReturn($data, 0, 'success',$count);

	}


	/*
	 * 表单初始赋值
	 */
	public function doadd()
	{
		$param = input();

		$id = isset($param['id']) ? $param['id'] : '';

		if($id == ''){

			ajaReturn('error', 1001, []);
		}

		$data = Db::name('to_media') ->field('name') ->where(['id' => $id]) ->find();

		ajaReturn($data, 0, 'success');
	}
	/*
	 * 页面跳转
	 */
	public function addpage(){

		$param = input();

		$id = isset($param['id']) ? $param['id'] : 0;

		$this ->assign('id', $id);

		return $this ->fetch('/tomedia/add');
	}


	/*
	 * 
	 */
	public function add()
	{
		$param = input();

		$id = isset($param['id']) ? $param['id'] : '';

		$data['name'] = isset($param['name']) ? $param['name'] : '';

		$list = Db::name('to_media')->where(['name' => $data['name'], 'agent_id' => $this ->agent_id])->select();

		if(!empty($list)){
			ajaReturn('', 1001, '该媒体已存在');
		}

		if($id == 0){
			$data['agent_id'] = $this ->agent_id;
			$res = Db::name('to_media') ->insert($data);
		}

		if($id != 0){

			$res = Db::name('to_media') ->where(['id' => $id]) ->update($data);
		}

		$msg = $res ? ['msg' => 'success', 'code' => 0] : ['msg' => 'error', 'code' => 1001] ;

		ajaReturn('', $msg['code'], $msg['msg']);

	}

	/*
	 * 删除院校
	 */
	public function delete(){

		$param = input();
		
		$id = isset($param['id']) ? $param['id'] : '';

		if($id == ''){
			ajaReturn('error', 1001, []);
		}

		$res = Db::name('to_media')->where(['id' => $id])->delete();

		$msg = $res ? ['msg' => 'success', 'code' => 0] : ['msg' => 'error', 'code' => 1001] ;

		ajaReturn('', $msg['code'], $msg['msg']);

	}
}