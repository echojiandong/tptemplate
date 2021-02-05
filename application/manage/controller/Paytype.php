<?php
namespace app\manage\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;

class Paytype extends author{

	public $agent_id;

	public $is_manage;

	public function _initialize(){

		parent::_initialize();

		$this ->agent_id = Session::get('manageinfo')['uid'];

		$this ->is_manage = Session::get('manageinfo')['status'];

		if($this ->is_manage != 1){

			$this ->agent_id = Session::get('manageinfo')['agent_id'];
		}
	}

	public function paymanage(){

		return $this ->fetch('/paytype/paymanage');
	}
	/*
	 * table 初始赋值
	 */
	public function paytypetable(){
		$param = input();

		$page = isset($param['page']) ? $param['page'] : 1;

		$limit = isset($param['limit']) ? $param['limit'] : 10;

		$data = Db::name('paytype') ->field('id,pay_type as name')
									->where(['agent_id' => $this ->agent_id])
								    ->page($page, $limit) ->select();

		$count = (Db::name('paytype') ->field('count(1) as counts') ->where(['agent_id' => $this ->agent_id]) ->find())['counts'];

		ajaReturn($data, 0, 'success',$count);

	}
	/*
	 * 表单初始赋值
	 */
	public function setpayformval(){
		$param = input();

		$id = isset($param['id']) ? $param['id'] : '';

		if($id == ''){

			ajaReturn('error', 1001, []);
		}

		$data = Db::name('paytype') ->field('pay_type as name') ->where(['id' => $id]) ->find();

		ajaReturn($data, 0, 'success');
	}
	/*
	 * 页面跳转
	 */
	public function addpaypage(){

		$param = input();

		$id = isset($param['id']) ? $param['id'] : 0;

		$this ->assign('id', $id);

		return $this ->fetch('/paytype/addpaypage');
	}
	/*
	 * 院校添加
	 */
	public function addpaytype(){
		$param = input();

		$id = isset($param['id']) ? $param['id'] : '';

		$data['pay_type'] = isset($param['name']) ? $param['name'] : '';

		$list = Db::name('paytype') ->where(['pay_type' => $data['pay_type']]) 
								   ->where(['agent_id' => $this ->agent_id]) 
								   ->select();

		if(!empty($list)){

			ajaReturn('', 1001, '付款方式已存在');
		}

		if($id == 0){

			$data['agent_id'] = $this ->agent_id;

			$res = Db::name('paytype') ->insert($data);
		}

		if($id != 0){

			$res = Db::name('paytype') ->where(['id' => $id]) ->update($data);
		}

		$msg = $res ? ['msg' => 'success', 'code' => 0] : ['msg' => 'error', 'code' => 1001] ;

		ajaReturn('', $msg['code'], $msg['msg']);

	}
	/*
	 * 删除院校
	 */
	public function delpaytype(){

		$param = input();
		
		$id = isset($param['id']) ? $param['id'] : '';

		if($id == ''){
			ajaReturn('error', 1001, []);
		}

		$res = Db::name('paytype') ->delete($id);

		$msg = $res ? ['msg' => 'success', 'code' => 0] : ['msg' => 'error', 'code' => 1001] ;

		ajaReturn('', $msg['code'], $msg['msg']);

	}
}