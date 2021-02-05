<?php
namespace app\manage\controller;
use think\Controller;
use think\Request;
use think\Db;
use app\manage\model\Wechatnav;

class WechatController extends author
{
	
    public function navList()
    {
        $data = DB::name('wechat_nav')->where(['del_status' => 1])->order('sort desc')->select();
		$result = tree($data);
	
		$this->assign("list",$result);

		return $this->fetch('wechat/navlist');
		
	}
	
	//输出添加菜单页面
	public function navadd()
	{
		$result = Db::name('wechat_nav')->where(['status'=>1, 'parentid' => 0, 'del_status' => 1])->select();
		
		$this->assign("wechatnavlist",$result);
		
        return $this->fetch('wechat/navadd');
	}
	
	public function wechatnavadd(Request $request)
	{
		$wechat = new Wechatnav();
		$param = $request->param();
		
		if (!$param['name']) {
			return jsonMsg('名称不能为空', 1);
		}

		$name = Db::name('wechat_nav')->where(['name' => $param['name']])->find();
		if ($name) {
			return jsonMsg("该名称已经存在",1);
		}
		$data = [
			'name' => $param['name'],
			'parentid'  => (int) $param['parentid'],
			'status' => (int) $param['status'],
			'url' => $param['url'],
			'url' => $param['type'],
			'key' => $param['key'],
			'addtime' => date('Y-m-d H:i:s', time()),
			'appid'   => $param['appid'],
			'pagepath' => $param['pagepath']
		];
		$res = $wechat->insertWechatnav($data);
		if ($res) {
			return jsonMsg('添加成功', 0);
		}
		return jsonMsg('添加失败', 1);
	}

	public function navedit(Request $request)
	{
		$wechat = new Wechatnav();
		$id = $request->param('id');
		if (!$id) {
			return jsonMsg('获取不到当前数据', 1);
		}

		$result = Db::name('wechat_nav')->where(['del_status' => 1, 'parentid' => 0])->select();
		$info = $wechat->wechatnavInfo($id);
		
		$this->assign('info', $info);
		$this->assign("wechatnavlist",$result);

		return $this->fetch('wechat/navedit');

	}

	public function wechatnavedit(Request $request)
	{
		$wechat = new Wechatnav();
		$param = $request->param();
		if (empty($param['name'])) {
			return jsonMsg('名称不能为空', 1);
		}

		$info = Db::name('wechat_nav')->where(['name' => $param['name']])->find();
		if ($info && $info['id'] != $param['id']) {
			return jsonMsg("该名称已经存在",1);
		}

		$data = [
			'name' => $param['name'],
			'parentid' => $param['parentid'],
			'url' => $param['url'],
			'status' => $param['status'],
			'type' => $param['type'],
			'key'  => $param['key'],
			'appid'   => $param['appid'],
			'pagepath' => $param['pagepath'],
			'updatetime' => date('Y-m-d H:i:s', time())
		];

		$res = $wechat->updateWechatnav($param['id'], $data);
		if ($res) {
			return jsonMsg('修改成功', 0);
		}
		return jsonMsg('修改失败', 1);

	}

	public function deleteNav(Request $request)
	{
		$id = $request->param('id');
		if (!$id) {
			return jsonMsg('该栏目不存在', 1);
		}

		$wechat = new Wechatnav();
		$res = $wechat->deleteNav($id);
		if ($res) {
			return jsonMsg('删除成功', 0);
		}
		return jsonMsg('删除失败', 1);
	}

	public function navSort(Request $request)
	{
		$param = $request->param();
		$id = (int) $param['id'];
		$sort = (int) $param['sort'];
		if (!$id) {
			return jsonMsg('没有改数据', 1);
		}

		$wechat = new Wechatnav();
		$res = $wechat->navSort($id, $sort);

		if ($res) {
			return jsonMsg('更改成功', 0);
		}
		return jsonMsg('更改失败', 1);
	}


    // 微信栏目列表
	public function getWechatList()
	{
		$data = DB::name('wechat_nav')->where(['status' => 1])->select();
		if($data){
			$arr=json_encode(
				array(
					'code'=>0,
					'msg'=>'',
					'count'=>count($data),
					'data'=>$data
				)
			);
			echo $arr;
		}else{
			jsonMsg('暂时没有内容',1);
		}
	}
}
 