<?php
namespace app\manage\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\Image;
use app\manage\model\Code;
use app\manage\model\Person;
use app\manage\model\OrderUser;
use app\manage\model\OrderPerson;
use app\manage\model\TestPaper;
class recycleContorller extends author
{
	/**
     *回收站codeList页面
     *@author 韩春雷 2019.3.8
     */
	public function codeList()
	{
		return $this->fetch("recycle/recycleCode");
	}
	/**
     *获取回收站codeList
     *@author 韩春雷 2019.3.8
     *@return [json]                  
     */
	public function getRecyclecCode(Request $request)
	{
		$param = $request->param();
        if(array_key_exists('keyword',$param) && $param['keyword']!=''){
            $where['card']=['like',"%".$param['keyword']."%"];
        }
        $where['is_forbidden'] = 2;
        $page=$param['page'];
        $limit=$param['limit'];
        $data =  Code::getCodeList($where,$page,$limit);
        $count = $data['count'];
        unset($data['count']);
        if($data){
        	$res = array(
					'code'=>0,
					'msg'=>'',
					'count'=>$count,
					'data'=>$data
				);
        	echo json_encode($res);
        }else{
        	jsonMsg("暂无数据",1);
        }
	}
	/**
     *获取回收站还原页面
     *@author 韩春雷 2019.3.8
     */
	public function restoreCode(Request $request)
	{
		$param = $request->param();
		$where['id'] = $param['id'];
		$codeList = Db::name('code')->where($where)->select();

		$this->assign('list',$codeList);
		return view('recycle/restoreCode',['title'=>'编辑卡号']);
	}
	/**
     *执行回收站还原页
     *@author 韩春雷 2019.3.8
     *@return   [json]                  
     */
	public function restore(Request $request)
	{
		$param=$request->param();
		$where['id'] = $param['id'];
		$data['is_forbidden'] = 1;
		$info=new code();
		$res=$info->where($where)->update($data);
		if($res)
		{
			return jsonMsg('还原成功',0);
		}
		else
		{
			return jsonMsg('还原失败',1);
		}
	}
	/**
     *code删除
     *@author 韩春雷 2019.3.8
     *@return [json]                  
     */
	public function delCode(Request $request)
	{
		$param=$request->param();
		$where['id'] = $param['id'];
		$info=new code();
		$res=$info->where($where)->delete();
		if($res)
		{
			return jsonMsg('删除成功',0);
		}
		else
		{
			return jsonMsg('删除失败',1);
		}
	}
	/**
     *回收站personList页面
     *@author 韩春雷 2019.3.20
     */
	public function personList()
	{
		return $this->fetch("recycle/recyclePerson");
	}
	/**
     *获取回收站PersonList
     *@author 韩春雷 2019.3.20
     *@return [json]                  
     */
	public function getRecyclecPerson(Request $request)
	{
		$param = $request->param();
        if(array_key_exists('keyword',$param) && $param['keyword']!=''){
            $where['card']=['like',"%".$param['keyword']."%"];
        }
        $where['act_status'] = 3;
        if($_SESSION['think']['manageinfo']['group_id'] == 1){
        	$type = 2;
        }else{
        	$type = 1;
        }
        $page=$param['page'];
		$limit=$param['limit'];
		$person = new Person();
        $data =  $person->getPersonList($page,$limit,$where,$type);
        $count = $data['count'];
        unset($data['count']);
        // 判断当前用户是否有购买的课程
        foreach ($data as $key => $value) {
	        // 判断用户学习状态
	        switch($value['status']) {
	            case '1' :
	                $statusTxt = '待试听';
	                break;
	            case '2' :
	                $statusTxt = '已试听';
	                break;
	            case '3' :
	                $statusTxt = '已下单';
	                break;
	            case '4' :
	                $statusTxt = '已购买';
	                break;
	            default:
	                break;
	        }

	        switch($value['act_status']) {
	            case '1':
	            case '2':
	                $actstatusTxt = '正常';
	                break;
	            case '3' :
	                $actstatusTxt = '已禁用';
	                break;
	            default:
	                $actstatusTxt = "未知";
	                break;
	        }
	        $data[$key]['statusTxt'] = $statusTxt;
            $data[$key]['actstatusTxt'] = $actstatusTxt;
            // 判断你是否有订单
            $isRenew = Db::name('order_person_son')->where(['person_id' => $value['id'], 'type' => ['neq', 0]])->count('id');
            $data[$key]['isRenew'] = $isRenew > 0 ? 1 : 0;
	    }
        if($data){
        	$res = array(
					'code'=>0,
					'msg'=>'',
					'count'=>$count,
					'data'=>$data
				);
        	echo json_encode($res);
        }else{
        	jsonMsg("暂无数据",1);
        }
	}
	/**
     *执行回收站Person还原
     *@author 韩春雷 2019.3.20
     *@return [json]                  
     */
	public function restorePerson(Request $request)
	{
		$param=$request->param();
		$where['id'] = $param['id'];
		$info=new person();
		//获取该用户的信息
		$personList = $info->where($where)->select();
		if(!empty($personList[0]['gradeAuth'])){
			$data['act_status'] = 2;
			$res=$info->where($where)->update($data);
		}else{
			$data['act_status'] = 1;
			$res=$info->where($where)->update($data);
		}
		if($res)
		{
			return jsonMsg('还原成功',0);
		}
		else
		{
			return jsonMsg('还原失败',1);
		}
	}
	/**
     *Person删除
     *@author 韩春雷 2019.3.20
     *@return [json]                  
     */
	public function delPerson(Request $request)
	{
		$param=$request->param();
		$where['id'] = $param['id'];
		$info=new person();
		$res=$info->where($where)->delete();
		if($res)
		{
			return jsonMsg('删除成功',0);
		}
		else
		{
			return jsonMsg('删除失败',1);
		}
	}
	/**
     *回收站orderList页面
     *@author 韩春雷 2019.3.20
     */
	public function orderList()
	{
		return $this->fetch("recycle/recycleOrder");
	}
	/**
     *获取回收站orderList
     *@author 韩春雷 2019.3.20
     *@return [json]                  
     */
	public function getRecyclecOrder(Request $request)
	{
		$param = $request->param();
        if(array_key_exists('keyword',$param) && $param['keyword']!=''){
             $where['u.username|u.phone']=['like',"%".$param['keyword']."%"];
        }
        if(array_key_exists('state',$param) && !empty($param['state'])){
            $where['o.state']=$param['state'];
        }else{
            $where['o.state']=['GT',0];
        }
        if(array_key_exists('orderCheck',$param) && !empty($param['orderCheck'])){
            $where['o.orderCheck']=$param['orderCheck'];
        }
        $where['o.is_forbidden'] = 1;
        $page=$param['page'];
        $limit=$param['limit'];
		if($_SESSION['think']['manageinfo']['user_type'] == 1){
			$user_id  = $_SESSION['think']['manageinfo']['uid'];
		}else{
			$user_id  = $_SESSION['think']['manageinfo']['agent_id'];
		}
		//我的采购订单
		$where['o.user_id'] = $user_id;
        $data =  OrderUser::getOrderList($where,1,2,$page,$limit);
        $count= OrderUser::getOrderList($where,1,2);
        foreach ($data as $key => $value) {
            $data[$key]['strtime'] = date('Y-m-d H:i:s',$value['strtime']);
        }
        if($data){
          $res = array(
          'code'=>0,
          'msg'=>'success',
          'count'=>$count,
          'data'=>$data
        );
          echo json_encode($res);
        }else{
          jsonMsg("暂无数据",1);
        }
	}
	/**
     *执行回收站Order还原
     *@author 韩春雷 2019.3.20
     *@return [json]                  
     */
	public function restoreOrder(Request $request)
	{
		$param=$request->param();
		$where['id'] = $param['id'];
		$info=new OrderUser();

		$data['is_forbidden'] = 0;
		$res=$info->where($where)->update($data);
	
		if($res)
		{
			return jsonMsg('还原成功',0);
		}
		else
		{
			return jsonMsg('还原失败',1);
		}
	}
	/**
     *order删除
     *@author 韩春雷 2019.3.20
     *@return [json]                  
     */
	public function delOrder(Request $request)
	{
		$param=$request->param();
		$where['id'] = $param['id'];
		$info=new OrderUser();
		$res=$info->where($where)->delete();
		if($res)
		{
			Db::name('order_user_info')->where('order_id',$param['id'])->delete();
			return jsonMsg('删除成功',0);
		}
		else
		{
			return jsonMsg('删除失败',1);
		}
	}
	/**
     *回收站testPaperList页面
     *@author 韩春雷 2019.3.20
     */
	public function testPaperList()
	{
		return $this->fetch("recycle/recycleTestPaper");
	}
	/**
     *获取回收站testPaperList
     *@author 韩春雷 2019.3.20
     *@return [json]                  
     */
	public function getRecyclecTestPaper(Request $request)
	{
		$param = $request->param();
        if(array_key_exists('type',$param) && !empty($param['type'])){
            $where['type']=$param['type'];
        }else{
        	$where['type']=['GT',0];
        }
        if(array_key_exists('keyword',$param) && $param['keyword']!=''){
            $where['title']=['like',"%".$param['keyword']."%"];
        }
        $where['pid'] = 0;
        $where['status'] = 2;
        $page=$param['page'];
        $limit=$param['limit'];
        $data =  TestPaper::getTestPaperList($where,$page,$limit);
        $count = $data['count'];
        unset($data['count']);
        if($data){
        	$res = array(
					'code'=>0,
					'msg'=>'',
					'count'=>$count,
					'data'=>$data
				);
        	echo json_encode($res);
        }else{
        	jsonMsg("暂无数据",1);
        }
	}
	/**
     *执行回收站试卷还原
     *@author 韩春雷 2019.3.20
     *@return [json]                  
     */
	public function restoreTestPaper(Request $request)
	{
		$param=$request->param();
		$where['id'] = $param['id'];
		$info=new TestPaper();

		$data['status'] = 1;
		$res=$info->where($where)->update($data);
		if($res)
		{
			return jsonMsg('还原成功',0);
		}
		else
		{
			return jsonMsg('还原失败',1);
		}
	}
	/**
     *试卷删除
     *@author 韩春雷 2019.3.20
     *@return [json]                  
     */
	public function delTestPaper(Request $request)
	{
		$param=$request->param();
		$where['id'] = $param['id'];
		$info=new TestPaper();
		//试题删除
		$quesWhere['pid'] = $param['id'];
		$res=$info->where($where)->delete();
		if($res)
		{
			$info->where($quesWhere)->delete();
			return jsonMsg('删除成功',0);
		}
		else
		{
			return jsonMsg('删除失败',1);
		}
	}
	/**
	 * [delquestlist 题库]
	 * @author 薛少鹏 xsp15135921754@163.com
	 * @DateTime 2019-05-30T18:08:52+0800
	 */
	public function delquestlist(){
		//科目列表
		$subList = Db::name('subject') ->select();
		//类型列表
		$typeList = Db::name('questype') ->select();
		// $this ->assign('gradeId', $this ->gradeId);
		$this ->assign('typeList',$typeList);
		$this ->assign('subList',$subList);
		return $this ->fetch('recycle/delquestlist');
	}

	/**
     *回收站personOrder页面
     *@author 韩春雷 2019.3.8
     */
	public function personOrderList()
	{
		return $this->fetch("recycle/recyclePersonOrder");
	}
	/**
     *获取回收站personOrder
     *@author 韩春雷 2019.3.8
     *@return [json]                  
     */
	public function getPersonOrderList(Request $request)
	{
      $orderPerson = new OrderPerson();
      $param = $request->param();

      $type = 1;  //1我的用户订单，3审核用户订单

      if(array_key_exists('keyword',$param) && $param['keyword']!=''){
          $where['p.nickName|p.phone']=['like',"%".$param['keyword']."%"];
      }
      if(array_key_exists('state',$param) && !empty($param['state'])){
          $where['o.state']= $param['state'];
      }
      if(array_key_exists('orderCheck',$param) && !empty($param['orderCheck'])){
          $where['o.orderCheck']= $param['orderCheck'];
	  }
	  
	  // 时间搜索优化
	  $orderStartTime = isset($param['orderStartTime']) && !empty($param['orderStartTime']) ? $param['orderStartTime'].' 00:00:00' : '';
	  $orderEndTime = isset($param['orderEndTime']) && !empty($param['orderEndTime']) ? strtotime($param['orderEndTime']).' 23:59:59' : '';

	  if ($orderEndTime && $orderEndTime < $orderStartTime) {
		  jsonMsg('结束时间不能小于开始时间', 1);
	  } 
	  
	  if ($orderEndTime && $orderStartTime && $orderEndTime >= $orderStartTime) {
		  $where['o.strtime'] = ['between time', [$orderStartTime, $orderEndTime]];
	  }elseif ($orderStartTime && !$orderEndTime) {
		  $where['o.strtime']=['GT', $orderStartTime];
	  } elseif($orderEndTime && !$orderStartTime) {
		  $where['o.strtime']=['ELT', $orderEndTime];
	  }

    //   if(array_key_exists('orderStartTime',$param) && $param['orderStartTime']!=''){
    //       $where['o.strtime']=['GT', $param['orderStartTime'].' 00:00:00'];
    //   }
    //   if(array_key_exists('orderEndTime',$param) && $param['orderEndTime']!=''){
    //       $where['o.strtime']=['ELT', $param['orderEndTime'].' 23:59:59'];
	//   }
	  
      $where['o.is_forbidden'] = 1;
      $page=$param['page'];
      $limit=$param['limit'];
      $data =  $orderPerson->getOrderPersonList($where,$page,$limit, $type);
      
      foreach ($data as $key => $value) {
        $yesAudition = Db::name('order_person_son')->where(['order_id' => $value['order'], 'is_audition' => 1])->count('id');
        $noAudition = Db::name('order_person_son')->where(['order_id' => $value['order'], 'is_audition' => 0])->count('id');
        $data[$key]['num'] = "试听课程".$yesAudition."门——正常课程".$noAudition."门";
        $data[$key]['type'] = $type;
      }
      $count= $orderPerson->getOrderPersonList($where, $type);
      if($data){
        $res = array(
        'code'=>0,
        'msg'=>'success',
        'count'=>$count,
        'data'=>$data
      );
        echo json_encode($res);
      }else{
        jsonMsg("暂无数据",1);
      }
	}
	/**
     *执行回收站还原
     *@author 韩春雷 2019.3.8
     *@return   [json]                  
     */
	public function restorePersonOrder(Request $request)
	{
		$param=$request->param();
		$where['id'] = $param['id'];
		$data['is_forbidden'] = 0;
		$info=new orderPerson();
		$res=$info->where($where)->update($data);
		if($res)
		{
			return jsonMsg('还原成功',0);
		}
		else
		{
			return jsonMsg('还原失败',1);
		}
	}
	/**
     *PersonOrder删除
     *@author 韩春雷 2019.3.8
     *@return [json]                  
     */
	public function delPersonOrder(Request $request)
	{
		$param=$request->param();
		$where['id'] = $param['id'];
		$info=new orderPerson();
		$list = $info->where($where)->find();
		$res=$info->where($where)->delete();
		//获取订单号id
		if($res)
		{
			//删除子订单
			Db::name('order_person_son')->where('order_id',$list['order'])->delete();
			return jsonMsg('删除成功',0);
		}
		else
		{
			return jsonMsg('删除失败',1);
		}
	}
}