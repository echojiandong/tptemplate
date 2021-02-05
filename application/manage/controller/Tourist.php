<?php
namespace app\manage\controller;
use app\manage\model\Person;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\image;
use think\Page;
class Tourist extends author{
    protected $members = array();// 代理商下的用户
    public function index(){
        return view('/tourist/index',['title'=>"游客列表"]);
    }
    public function getTouristList(Request $request){
        $param = $request->param();
        if(array_key_exists('keyword',$param) && $param['keyword']!=''){
            $where['p.nickName|p.phone']=['like',"%".trim($param['keyword'])."%"];
        }
        $page=$param['page'];
        $limit=$param['limit'];
        $where['p.is_tourist'] = 0;
        $uid = array();
        if($_SESSION['think']['manageinfo']['group_id'] != 1 && $_SESSION['think']['manageinfo']['group_id'] != 2){
            if($_SESSION['think']['manageinfo']['user_type'] == 1){
                //获取代理上下的所有员工
                $userList = Db::name('user')->where(['agent_id'=>$_SESSION['think']['manageinfo']['uid']])->select();
                foreach ($userList as $key => $value) {
                    $uid[] = $value['uid'];
                }
                $uid[] = $_SESSION['think']['manageinfo']['uid'];
            }else{
                //获取该员工下的所有员工
                // $userList = $this->getAllUser($_SESSION['think']['manageinfo']['uid']);
                // foreach ($userList as $key => $value) {
                //     $uid[] = $value['uid'];
                // }
                $uid[] = $_SESSION['think']['manageinfo']['uid'];
            }
            $where['p.user_id'] = array('in',$uid);
        }
        $data = Db::name('person')->alias('p')
                                ->join('user u','u.uid = p.user_id','left')
                                ->field('p.*,u.username')
                                ->where($where)
                                ->page($page,$limit)
                                ->order('p.addtime desc')
                                ->select();
        $count = Db::name('person')->alias('p')
                                ->join('user u','u.uid = p.user_id','left')
                                ->where($where)
                                ->count();
        foreach ($data as $key => $value) {
            $data[$key]['addtime'] = date('Y-m-d',$value['addtime']);
            if($value['status'] == 1){
                $data[$key]['status_name'] = '待试听';
            }elseif($value['status'] == 2){
                $data[$key]['status_name'] = '已试听';
            }elseif($value['status'] == 3){
                $data[$key]['status_name'] = '有订单未审核通过';
            }elseif($value['status'] == 4){
                $data[$key]['status_name'] = '有订单且审核通过';
            }
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
    public function allotPerson(){
        $param = input();
        $id = $param['id'];
        //获取所属管理员
        if($_SESSION['think']['manageinfo']['group_id'] == 1 || $_SESSION['think']['manageinfo']['group_id'] == 2){
            $userList = Db::name('user')->where(['parent_id'=>$_SESSION['think']['manageinfo']['uid'],'user_type'=>1])->select();
        }else{
            if($_SESSION['think']['manageinfo']['user_type'] == 1){
                $userList = Db::name('user')->where(['agent_id'=>$_SESSION['think']['manageinfo']['uid']])->select();
            }else{
                $userList = $this->getAllOrgSon($_SESSION['think']['manageinfo']['org_id']);
            }
        }
        $this->assign('id',$id);
        $this->assign('type',1);
        $this->assign('userList',$userList);
        return view('tourist/allotPerson',['title'=>'管理员分配']);
    }
    public function doAllotPerson(){
        $param = input();
        if($param['type'] == 1){
            $where['id'] = $param['id'];
            $data['user_id'] = $param['user_id'];
            $res = Db::name('person')->where($where)->update($data);
            //记录分配log
            $logList = [
                'assign_user_id'=>$_SESSION['think']['manageinfo']['uid'],
                'accept_user_id'=>$param['user_id'],
                'person_id'=>$param['id'],
                'add_time'=>time(),
            ];
            if($res){
                Db::name('person_assign_log')->insert($logList);
                jsonMsg('修改成功',0);
            }
        }else{  
            $id = explode(',',$param['id']);
            $info = new person();
            $data = array();
            for ($i=0; $i <count($id) ; $i++){
                $data[$i]['id'] = $id[$i];
                $data[$i]['user_id'] =$param['user_id'] ;
                //记录分配log
                $logList[$i] = [
                    'assign_user_id'=>$_SESSION['think']['manageinfo']['uid'],
                    'accept_user_id'=>$param['user_id'],
                    'person_id'=>$id[$i],
                    'add_time'=>time(),
                ];
            }
            $res = $info->saveAll($data);
            if($res){
                Db::name('person_assign_log')->insertAll($logList);
                jsonMsg('修改成功',0);
            }
        }
    }
    public function allotAllPerson(){
        $param = input();
        //获取所属管理员
        if($_SESSION['think']['manageinfo']['group_id'] == 1 || $_SESSION['think']['manageinfo']['group_id'] == 2){
            $userList = Db::name('user')->where(['parent_id'=>$_SESSION['think']['manageinfo']['uid'],'user_type'=>1])->select();
        }else{
            if($_SESSION['think']['manageinfo']['user_type'] == 1){
                $userList = Db::name('user')->where(['agent_id'=>$_SESSION['think']['manageinfo']['uid']])->select();
            }else{
                $userList = $this->getAllOrgSon($_SESSION['think']['manageinfo']['org_id']);
            }
        }
        $this->assign('id',$param['id']);
        $this->assign('type',2);
        $this->assign('userList',$userList);
        return view('tourist/allotPerson',['title'=>'管理员分配']);
    }
    public function getAllOrgSon($uid)
    {
        $agent_id = $_SESSION['think']['manageinfo']['agent_id'];
        //查找当前用户分组下面的子分组
        $res=Db::name('user_organization')->where(['pid' => $uid, 'agent_id' => $agent_id])->select();
        if($res)
        {//我的下面有子组织分组，继续查找子分组下面的组织分组
            foreach($res as $v)
            {
                $userList = Db::name('user')->where(['org_id' => $v['id'], 'agent_id' => $agent_id,'status'=>1,'user_type' => 2])->select();
                $this->members = array_merge($this->members, $userList);
                $this->getAllOrgSon($v['id']);
            }   
        }
        return $this->members;
    }
}