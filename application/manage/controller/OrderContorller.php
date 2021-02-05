<?php
namespace app\manage\controller;
use think\Controller;
use think\Request;
use think\db\Query;
use think\Session;
use think\Db;
use think\Image;
use app\manage\model\Menu;
use app\manage\model\OrderUser;
use app\manage\model\OrderPerson;
use app\manage\model\Code;
use app\weixin\controller\Weixin;

  class OrderContorller extends author
  {
      public $state=array(1=>'待支付 ',2=>'已支付');
      public $orderCheck=array(1=>'待审核 ',2=>'总代理审核通过',3=>'财务审核通过','4'=>'审核完成');
      public $code_status=array(1=>'未激活',2=>'以激活',3=>'禁用',4=>'已卖出');
      /**
       * order列表页面
       * @author 韩春雷 2019.3.21
       */
      public function getOrder()
      {
        $param = input();
        $this->assign('type',$param['type']);
        $this->assign('check',$param['check']);
        return view('/order/consumer',['title'=>"订单列表"]);
      }
      /**
       * 获取order列表
       * @author 韩春雷 2019.3.21
       * @return   [json]     
       */
      public function getOrderList(Request $request)
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
            $where['o.orderCheck'] = $param['orderCheck'];
          }

          // 时间搜索优化
          $orderStartTime = isset($param['orderStartTime']) && !empty($param['orderStartTime']) ? strtotime($param['orderStartTime']) : '';
          $orderEndTime = isset($param['orderEndTime']) && !empty($param['orderEndTime']) ? strtotime($param['orderEndTime']) : '';

          if ($orderEndTime && $orderEndTime < $orderStartTime) {
            jsonMsg('结束时间不能小于开始时间', 1);
          } 
          if ($orderEndTime) {
            $orderEndTime = $orderEndTime+ 86400;
          }
          
          if ($orderEndTime && $orderStartTime && $orderEndTime >= $orderStartTime) {
            $where['o.strtime'] = ['between time', [$orderStartTime, $orderEndTime]];
          }elseif ($orderStartTime && !$orderEndTime) {
            $where['o.strtime']=['GT', $orderStartTime];
          } elseif($orderEndTime && !$orderStartTime) {
            $where['o.strtime']=['ELT', $orderEndTime];
          }

          //判断当前用户是代理商还是普通员工
          if($_SESSION['think']['manageinfo']['user_type'] == 1){
            $user_id  = $_SESSION['think']['manageinfo']['uid'];
          }else{
            $user_id  = $_SESSION['think']['manageinfo']['agent_id'];
          }
          //我的采购订单（当前代理商的采购订单）
          //type == 1 我的订单 | type=2 我的经销商 | type ==3  我的前台用户订单 | type ==4  代理商前台用户订单
          //check== 2 我的采购订单审核 |  check == 1 代理商采购订单审核
          if($param['type'] == 1){
            $where['o.user_id'] = $user_id;
          }
          $where['o.is_forbidden'] = 0;
          $page=$param['page'];
          $limit=$param['limit'];
          $data =  OrderUser::getOrderList($where,$param['type'],$param['check'],$page,$limit);
          $count= OrderUser::getOrderList($where,$param['type'],$param['check']);
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
       * 获取order详细信息
       * @author 韩春雷 2019.3.21
       */
      public function getOneOrder(Request $request){
        $param=$request->param();
        $data=OrderUser::getOneOrder($param['id']);

        foreach ($data as $key => $value) {
            if($value['video_class_id']){
              $classList = Db::name('video_class')
                    ->alias('vc')
                    ->where('vc.id='.$value['video_class_id'])
                    ->join('guard_grade g','g.id = vc.grade_id')
                    ->join('guard_subject s','s.id = vc.subject_id')
                    ->field('g.grade,s.subject,vc.Semester')
                    ->select();
              foreach ($classList as $ke => $val) {
                if($val['Semester'] == 1){
                  $classList[$ke]['Semester'] = '上';
                }elseif($val['Semester'] ==2){
                  $classList[$ke]['Semester'] = '下';
                }elseif($val['Semester'] == 3){
                  $classList[$ke]['Semester'] = '全册';
                }
              }
              $data[$key]['classViderInfo'] = $classList[0]['grade'].'-'.$classList[0]['subject'].'-'.$classList[0]['Semester'];
            }elseif($value['product_id']){
                $classList = Db::name('product')
                    ->alias('vc')
                    ->where('vc.id='.$value['product_id'])
                    ->join('guard_grade g','g.id = vc.grade_id')
                    ->join('guard_subject s','s.id = vc.subject_id')
                    ->field('g.grade,s.subject,vc.Semester')
                    ->select();
                foreach ($classList as $ke => $val) {
                    if($val['Semester'] == 1){
                        $classList[$ke]['Semester'] = '上';
                    }elseif($val['Semester'] ==2){
                        $classList[$ke]['Semester'] = '下';
                    }elseif($val['Semester'] == 3){
                        $classList[$ke]['Semester'] = '全册';
                    }
                }
                $data[$key]['classViderInfo'] = $classList[0]['grade'].'-'.$classList[0]['subject'].'-'.$classList[0]['Semester'];
            }
            else{
              $data[$key]['classViderInfo'] = '试听课';
            }
            $data[$key]['state']=$this->state[$value['state']];
            $data[$key]['orderCheck']=$this->orderCheck[$value['orderCheck']];
        }
        $this->assign("info",$data);
        return $this->fetch("order/consumerSave");
      }
     /**
       * 修改order页面
       * @author 韩春雷 2019.3.21
       */
      public function editOrder(Request $request){
        $param=$request->param();
        $data=OrderUser::getOneOrder($param['id']);
        //获取年级列表信息
        $gradeList = Db::name('grade')->select();

        foreach ($data as $key => $value) {
          if($value['video_class_id']){
            $classList = Db::name('video_class')
                  ->alias('vc')
                  ->where('vc.id='.$value['video_class_id'])
                  ->join('guard_grade g','g.id = vc.grade_id')
                  ->join('guard_subject s','s.id = vc.subject_id')
                  ->field('g.grade,s.subject,vc.Semester')
                  ->select();
            foreach ($classList as $ke => $val) {
              if($val['Semester'] == 1){
                $classList[$ke]['Semester'] = '上';
              }elseif($val['Semester'] ==2){
                $classList[$ke]['Semester'] = '下';
              }elseif($val['Semester'] == 3){
                $classList[$ke]['Semester'] = '全册';
              }
            }
            $data[$key]['classViderInfo'] = $classList[0]['grade'].'-'.$classList[0]['subject'].'-'.$classList[0]['Semester'];
          }elseif($value['product_id']){
                $classList = Db::name('product')
                    ->alias('vc')
                    ->where('vc.id='.$value['product_id'])
                    ->join('guard_grade g','g.id = vc.grade_id')
                    ->join('guard_subject s','s.id = vc.subject_id')
                    ->field('g.grade,s.subject,vc.Semester')
                    ->select();
                foreach ($classList as $ke => $val) {
                    if($val['Semester'] == 1){
                        $classList[$ke]['Semester'] = '上';
                    }elseif($val['Semester'] ==2){
                        $classList[$ke]['Semester'] = '下';
                    }elseif($val['Semester'] == 3){
                        $classList[$ke]['Semester'] = '全册';
                    }
                }
                $data[$key]['classViderInfo'] = $classList[0]['grade'].'-'.$classList[0]['subject'].'-'.$classList[0]['Semester'];
            }
          else{
            $data[$key]['classViderInfo'] = '试听课';
          }
        }
        $this->assign("info",$data);
        $this->assign("type",$param['type']);
        $this->assign("check",$param['check']);
        $this->assign("gradeList",$gradeList);
        return $this->fetch("order/editOrder");
      }
     /**
       * 执行修改order页面
       * @author 韩春雷 2019.3.25
       * @return   [json]   
       */
      public function doEditOrder(Request $request){
        $param= $request->param();
        //获取当前订单管理的父级
        $orderList = Db::name('order_user')
                        ->alias('o')
                        ->join('guard_user u','u.uid = o.user_id')
                        ->field('u.*,o.user_id,o.orderCheck')
                        ->where('o.id = '.$param['id'])->select();

        $group_id = $_SESSION['think']['manageinfo']['group_id'];
        $uid = $_SESSION['think']['manageinfo']['uid'];
        //审核流程
        // $orderCheck = end($param['orderCheck']);
        // if($orderList['0']['orderCheck'] == 1){
        //   if($orderCheck > 2){
        //     return jsonMsg('代理商审核未通过!',1);
        //   }
        //   if($orderCheck == 2){
        //     if($_SESSION['think']['manageinfo']['user_type'] != 1){
        //       return jsonMsg('您没有权限执行此操作,需要代理商审核!',1);
        //     }
        //   }
        // }
        // if($orderList['0']['orderCheck'] == 2){
        //   if($orderCheck > 3){
        //     return jsonMsg('财务审核未通过!',1);
        //   }
        //   if($orderCheck == 3){
        //     if($_SESSION['think']['manageinfo']['user_type'] != 2){
        //       return jsonMsg('您没有权限执行此操作，需要财务审核!',1);
        //     }
        //   }
        // }
        // if($orderList['0']['orderCheck'] == 3){
        //   if($orderCheck == 4){
        //     if($uid != $orderList['0']['parent_id']){
        //       return jsonMsg('您没有权限执行此操作，需要父级代理审核!',1);
        //     }
        //   }
        // }
        //获取该代理下的可修改、审核人员名单
        // $checkUserList = Db::name('user')->where(['parent_id'=>$orderList['0']['uid'],'user_type'=>2])->field('uid')->select();
        // $userList = [];
        // foreach ($checkUserList as $key => $value) {
        //   $userList[] =  $value['uid'];
        // }
        // $userList[] = $orderList['0']['uid'];
        // $userList[] = $orderList['0']['parent_id'];        
        // //审核人只能是父级或者超级管理员
        // if(in_array($uid,$userList)){
          $data = array();
          $where['id'] = $param['id'];
          $data['state'] = $param['state'];
          // $data['orderCheck'] = isset($param['orderCheck'])?$param['orderCheck']:'';
          if(isset($param['orderCheck'])){
            $data['orderCheck'] = $param['orderCheck'];
          }
          $data['money'] = $param['money'];
          // $data['payMoney'] = $param['payMoney'];
          $data['remark'] = isset($param['remark'])?$param['remark']:'';
          $data['u_time'] =time();
          $data['u_admin'] = $uid;
          $order_num = $param['order_num'];
          $order_user_id = $param['order_user_id'];
          $video_class_id = $param['video_class_id'];
          $product_id = $param['product_id'];
          $order_type = $param['order_type'];
          //审核通过，修改user_code表可生成卡号的数量
          //获取订单之前的状态 父级代理审核通过后不可修改
          if($orderList['0']['orderCheck'] != 4){
            if(isset($data['orderCheck'])){
            if($data['orderCheck'] == 4){
              //订单支付后才能审核通过
              if($data['state'] == 2){
                //判断最终审核通过人员所属经销商与订单所属经销商是否为同一经销商
                $agent_id=$_SESSION['think']['manageinfo']['agent_id'];
                $myself_id=$_SESSION['think']['manageinfo']['uid'];
                $myself_type=$_SESSION['think']['manageinfo']['user_type'];//审核员身份
                $myself_id_agent_id=$_SESSION['think']['manageinfo']['agent_id'];
                //获取本订单的经销商
                $user_id=Db::name('order_user')->field('user_id')->where($where)->find();
                $userInfoAgent=Db::name('user')->field('user_type,agent_id')->where(['uid' => $user_id['user_id'], 'user_type' => 1])->find();
                if($userInfoAgent['user_type'] != 1)
                {//本订单是员工下的查找员工的老板在查找老板的上级经销商
                  $userInfoAgent=Db::name('user')->field('user_type,agent_id')->where(['uid' => $userInfoAgent['agent_id'], 'user_type' => 1])->find();
                }
                if($myself_type == 1)
                {//如果我是老板直接用待审核的订单的上级经销商id与我id相比较
                  if($myself_id==$userInfoAgent['agent_id'])
                  {
                    $a=true;
                  }else{
                    $a=false;
                  }
                }else{//如果我是员工直接用待审核的订单的上级经销商id与我老板id相比较
                  if($myself_id_agent_id==$userInfoAgent['agent_id'])
                  {
                    $a=true;
                  }else{
                    $a=false;
                  }
                }
                if($a)
                {
                  $userInfo   = array();
                  $parentInfo = array();
                  $userwhere['user_id'] = $orderList['0']['user_id'];
                  if($orderList['0']['parent_id']==24){
                    //下单代理为一级代理
                    //修改订单用户的可生成数量
                    // if($param['order_type'] == 1){
                        for ($i=0; $i <count($order_num); $i++) {
                          //修改user_code
                          $userwhere['video_class_id'] = $video_class_id[$i];
                          $userwhere['product_id'] = $product_id[$i];
                          $userwhere['type'] = $order_type;
                          $userCodeList = Db::name('user_code')->where($userwhere)->find();
                          if($userCodeList){
                            $userInfo['code_num'] = $userCodeList['code_num'] + $order_num[$i];
                            Db::name('user_code')->where($userwhere)->update($userInfo);
                          }else{
                            $userwhere['code_num'] = $order_num[$i];
                            Db::name('user_code')->insert($userwhere);
                          }
                        }
                      // }
                      // else{
                      //   for ($i=0; $i <count($order_num); $i++) {
                      //     //修改user_code
                      //      $videoClassList = explode(',',$video_class_id[$i]);
                      //      for($j=0; $j <count($videoClassList); $j++){
                      //         $userwhere['video_class_id'] = $videoClassList[$j];
                      //         $userCodeList = Db::name('user_code')->where($userwhere)->find();
                      //         if($userCodeList){
                      //           $userInfo['code_num'] = $userCodeList['code_num'] + $order_num[$i];
                      //           Db::name('user_code')->where($userwhere)->update($userInfo);
                      //         }else{
                      //           $userwhere['code_num'] = $order_num[$i];
                      //           Db::name('user_code')->insert($userwhere);
                      //         }
                      //       }
                      //   }
                      // }
                      $res = Db::name('order_user')->where($where)->update($data);
                  }else{
                    $parentwhere['user_id'] = $orderList['0']['parent_id'];
                    //下单代理为二级代理
                    // if($param['order_type'] == 1){
                      for ($i=0; $i <count($order_num); $i++) {
                        //获取父级的信息
                        $parentwhere['video_class_id'] = $video_class_id[$i];
                        $parentwhere['product_id'] = $product_id[$i];

                        $parentwhere['type'] = $order_type;
                        $parentList = Db::name('user_code')
                                    ->where($parentwhere)
                                    ->select();
                        //修改user_code
                        if(empty($parentList) || $parentList[0]['code_num'] < $order_num[$i]){
                          jsonMsg("超出当前管理员的卡号数量",1);
                        }
                      }
                      for($i=0; $i <count($order_num); $i++){
                        //获取父级的信息
                        $parentwhere['video_class_id'] = $video_class_id[$i];
                        $parentwhere['product_id'] = $product_id[$i];
                        $parentwhere['type'] = $order_type;
                        $parentList = Db::name('user_code')
                                    ->where($parentwhere)
                                    ->select();
                        $parentInfo['code_num'] = $parentList[0]['code_num'] - $order_num[$i];
                          //权限足够 则修改user_code
                          $userwhere['video_class_id'] = $video_class_id[$i];
                          $userwhere['product_id'] = $product_id[$i];
                          $userwhere['type'] = $order_type;
                          $userCodeList = Db::name('user_code')->where($userwhere)->find();
                          if($userCodeList){
                            $userInfo['code_num'] = $userCodeList['code_num'] + $order_num[$i];
                            Db::name('user_code')->where($userwhere)->update($userInfo);
                          }else{
                            $userwhere['code_num'] = $order_num[$i];
                            Db::name('user_code')->insert($userwhere);
                          }
                          Db::name('user_code')->where($parentwhere)->update($parentInfo);
                      }                    
                    // }else{
                    //   for ($i=0; $i <count($order_num); $i++) {
                    //     //修改user_code
                    //     $videoClassList = explode(',',$video_class_id[$i]);
                    //     for($j=0; $j <count($videoClassList); $j++){
                    //       //获取父级的信息
                    //       $parentwhere['video_class_id'] = $videoClassList[$j];
                    //       $parentList = Db::name('user_code')
                    //                       ->where($parentwhere)
                    //                       ->select();
                    //       if(empty($parentList) || $parentList[0]['code_num'] < $order_num[$i]){
                    //         jsonMsg("超出当前管理员的卡号数量",1);
                    //       }
                    //     }
                    //   }
                    //   for ($i=0; $i <count($order_num); $i++) { 
                    //     $videoClassList = explode(',',$video_class_id[$i]);
                    //     for($j=0; $j <count($videoClassList); $j++){
                    //       //获取父级的信息
                    //       $parentwhere['video_class_id'] = $videoClassList[$j];
                    //       $parentList = Db::name('user_code')
                    //                       ->where($parentwhere)
                    //                       ->select();
                    //         $parentInfo['code_num'] = $parentList[0]['code_num'] - $order_num[$i];
                    //         //权限足够 则修改user_code
                    //         $userwhere['video_class_id'] = $videoClassList[$j];
                    //         $userCodeList = Db::name('user_code')->where($userwhere)->find();
                    //         if($userCodeList){
                    //           $userInfo['code_num'] = $userCodeList['code_num'] + $order_num[$i];
                    //           Db::name('user_code')->where($userwhere)->update($userInfo);
                    //         }else{
                    //           $userwhere['code_num'] = $order_num[$i];
                    //           Db::name('user_code')->insert($userwhere);
                    //         }
                    //         Db::name('user_code')->where($parentwhere)->update($parentInfo);
                    //     }                   
                    //   }
                    // }
                    $res = Db::name('order_user')->where($where)->update($data);
                  }
                }else{
                  jsonMsg("无订单审核权限",1);
                }
              }else{
                jsonMsg("订单未支付",1);
              }
            }else{
              $res = Db::name('order_user')->where($where)->update($data);
            }
          }else{
            $res = Db::name('order_user')->where($where)->update($data);
          }
          }else{
            jsonMsg("该订单上级已审核成功，不能修改",1);
          }
          if($res)
          {
            return jsonMsg('修改成功',0);
          }
          else
          {
            return jsonMsg('修改失败',1);
          }
        // }else{
        //   jsonMsg("您没有权限执行此操作",1);
        // }
      }
   /**
    *@author  韩春雷 2019/3/21
    *order删除
    *@return   [json]   
    */
    public function delOrder(Request $request)
    {
      $param=$request->param();
      $where['id'] = $param['id'];
      //获取订单信息
      $orderInfo = Db::name('order_user')->where($where)->find();
      //如果订单已审核通过则不能删除
      if($orderInfo['orderCheck'] != 4){
        $data['is_forbidden'] = 1;
        $info=new orderUser();
        $res=$info->where($where)->update($data);
        if($res)
        {
          return jsonMsg('修改成功',0);
        }
        else
        {
          return jsonMsg('修改失败',1);
        }
      }else{
        return jsonMsg('该订单已审核通过不能作废',1);
      }
    }

    /**
     * 添加order页面
     * @author 韩春雷 2019.3.25
     */
    public function addOrder(Request $request)
    {
      // $host=$request->domain();
      //获取年级列表信息
      // $gradeList = Db::name('grade')->select();
      // $a = 1;
      // //获取管理员列表
      // $uid = $_SESSION['think']['manageinfo']['uid'];
      // $group_id = $_SESSION['think']['manageinfo']['group_id'];

      // $uidList = Db::name('user')->where('parent_id='.$uid.' and group_id in("5","6","8","9")')->select();
      // $uidData =array();
      // if($uidList){
      //   foreach ($uidList as $key => $value) {
      //     $uidData[] = $value['uid'];
      //   }
      // }
      // $uidData[] = $uid;
      // $uidData = implode(',',$uidData);
      // $userList = Db::name('user')->where('uid in('.$uidData.')')->select();

      // 课程 版本二
      // $code = new Code();
      // $courseList = $code->getCourseByGradeId(); 
    
      // $this->assign("host",$host);
      // $this->assign("a",$a);
      // $this->assign("userList",$userList);
      // $this->assign("gradeList",$gradeList);
      // $this->assign('isAdmin', $group_id == 1 ? true : false);
      // $this->assign('courselist', $courseList ? $courseList : array());
      // 版本二end

      // 版本三
      // 获取当前管理代理商的课程剩余数量
      // $code = new Code();
      // 七年级
      // $sevenGradeUpperSem = $code->getCourseSurplusNum($grade = 1, $semester=1, $array = array(1, 2, 3),$type=1);
      // $sevenGradeLowerSem = $code->getCourseSurplusNum($grade = 7, $semester=2, $array = array(1, 2, 3),$type=1);
      // // 八年级
      // $eightGradeUpperSem = $code->getCourseSurplusNum($grade = 8, $semester=1, $array = array(1, 2, 3, 4),$type=1);
      // $eightGradeLowerSem = $code->getCourseSurplusNum($grade = 8, $semester=2, $array = array(1, 2, 3, 4),$type=1);
      // // 九年级
      // $nineGradeUpperSem = $code->getCourseSurplusNum($grade = 9, $semester=1, $array = array(1, 2, 3, 4, 5),$type=1);
      // $ninenGradeLowerSem = $code->getCourseSurplusNum($grade = 9, $semester=2, $array = array(1, 2, 3, 4, 5),$type=1);

      // $this->assign('sevenGradeUpperSem', $sevenGradeUpperSem);
      // $this->assign('sevenGradeLowerSem', $sevenGradeLowerSem);
      // $this->assign('eightGradeUpperSem', $eightGradeUpperSem);
      // $this->assign('eightGradeLowerSem', $eightGradeLowerSem);
      // $this->assign('nineGradeUpperSem', $nineGradeUpperSem);
      // $this->assign('ninenGradeLowerSem', $ninenGradeLowerSem);

      //版本四
      $code = new Code();
      $list = $code->getCourseSubNum($subject = array(1,2,3,4,5,10), $array = array(7,8,9),$type=1);
      //获取年级列表
      $gradeList = Db::name('grade')->where('id in (7,8,9)')->select();

      $subjectList = Db::name('subject')->select();
      foreach ($subjectList as $key => $value) {
        $subjectInfo[$value['id']] = $value['subject'];
      }
      foreach ($subjectList as $key => $value) {
        $subjectList[$key] = $value['subject'];
      }
      $semesterList = array(1,2);
      $gradeInfo = array();
      $k = 0;
      for($i=1; $i<=2; $i++) {
        foreach ($gradeList as $ke => $val) {
          $gradeInfo[$k]['semester'] = $i;
          $gradeInfo[$k]['grade_id'] = $val['id'];
          $gradeInfo[$k]['grade'] = $val['grade'];
          $gradeInfo[$k]['name'] = $i==1?'上':'下';
          $gradeInfo[$k]['className'] = $gradeInfo[$k]['grade'].$gradeInfo[$k]['name'];
          $k++;
        }
      }
      $gradeInfo[$k]['semester'] = 3;
      $gradeInfo[$k]['grade_id'] = 9;
      $gradeInfo[$k]['className'] = '九年级全册';
      for($i=1;$i<11;$i++){
          if($i>5 && $i!=10){
              continue;
          }
          foreach ($gradeInfo as $k => $val) {
            $classInfo[$i][$k]['is_have'] = 0;
            foreach ($list[$i] as $key => $value) {
              $classInfo[$i]['subject'] = $subjectList[$i-1];
              if($val['grade_id'] == $value['grade_id'] && $val['semester'] == $value['Semester']){
                $classInfo[$i][$k]['is_have'] = 1;
                $classInfo[$i][$k]['classNum'] = $value['classNum'];
                $classInfo[$i][$k]['grade_id'] = $value['grade_id'];
                $classInfo[$i][$k]['semester'] = $value['Semester'];
                $classInfo[$i][$k]['id'] = $value['id'];
              }
          }
        }
      }
      foreach ($gradeList as $key => $value) {
        $gradeList[$value['id']] = $value['grade'];
      }
      $semesterList = array('1'=>'上','2'=>'下',3=>'全册');
      $this->assign('classInfo', $classInfo);
      $this->assign('gradeList', $gradeList);
      $this->assign('semesterList',$semesterList);
      $this->assign('subjectList', $subjectInfo);
      $this->assign('uid', $_SESSION['think']['manageinfo']['uid']);
      $this->assign('gradeInfo', $gradeInfo);
      return $this->fetch("order/addOrder");
    }
    /**
     * 
     * 下单提交接口修改 版本二
     * @author yjz 2019-06-18
     */
    // public function doInsertCourse(Request $request)
    // {
    //   $param = $request->param();
    //   $courseArr = $param['arr'];

    //   if (!is_array($courseArr)) {
    //     return jsonMsg('参数错误', 1);
    //   }
    //   $this->judgeLast($courseArr);
    //   $price=0;
    //   //参数处理
    //   $classList = $this->checkCourseData($courseArr);
    //   foreach ($classList as $key => $value) {
    //     $price = $price + $value['class_price'] * $value['order_num'] * $value['Discount'];
    //   }
    //   $data = array();
    //   //生成订单号
    //   $data['order'] = time().mt_rand(100000,999999);
    //   $data['payMoney'] =  0;
    //   $data['money'] = $price;
    //   $data['user_id'] = $_SESSION['think']['manageinfo']['uid'];
    //   $data['strtime'] =  time();
    //   $info = new OrderUser;
    //   $info->save($data);
    //   $order_id = $info->id;

    //   foreach ($classList as $key => $value) {
    //     $classList[$key]['order_id'] = $order_id;
    //   }
    //   //添加order_user_info 表
    //   $res = Db::name('order_user_info')->insertAll($classList);
    //   if($res) {
    //     return jsonMsg('下单成功',0);
    //   } else {
    //     return jsonMsg('下单失败',1);
    //   }

    // }

    /**
     * 
     * 管理员下单 下单提交接口修改 版本三 正式课
     * @author yjz 2019-07-02
     */
    public function doInsertCourse(Request $request)
    {
      $param = $request->param();
      //只有代理商-采购可以下单  判断权限
      $uid = $_SESSION['think']['manageinfo']['uid'];
      //如果该管理员是采购的话 可以下单
      if($_SESSION['think']['manageinfo']['user_type'] == 1){
        $user_id = $_SESSION['think']['manageinfo']['uid'];
      }else{
        $user_id = $_SESSION['think']['manageinfo']['agent_id'];
      }

      if($param['order_type'] == 1){
        if(empty($param['subject_id'])){
          return jsonMsg('购买数量不能为空', 1);
        }
        $datas = $param['subject_id'];
        if (!$datas) {
          return jsonMsg('参数错误', 1);
        }
        $price=0;
        //参数处理
        // 接收数据处理
        $arr = array();
        foreach ($datas as $key => $value) {
          foreach ($value as $kl => $vl) {
            foreach ($vl as $k => $v) {
              $arr[] = [
                'grade_id' => $key,
                'semester' => $kl,
                'num'      => $v['num'],
                'subject_id' => $k,
                // 'isAudition' => $isAudition
              ];
            }
          }
        }

        //获取购买数量
        $gradeList = Db::name('grade')->select();
        $subjectList = Db::name('subject')->select();
        foreach ($gradeList as $key => $value) {
          $gradeList[$value['id']] = $value['grade'];
        }
        foreach ($subjectList as $key => $value) {
          $subjectList[$value['id']] = $value['subject'];
        }
        // 订单数据插入
        $infoList = array();
        $price = 0;
        $k=0;
        foreach ($arr as $key => $value) {
          if(!empty($value['num'])){
            if($value['num'] < 0){
              return jsonMsg('购买的数量必须大于零', 1);
            }
            if($value['subject_id']>9){
                $res = Db::name('product')->where(['grade_id' => $value['grade_id'], 'Semester' => $value['semester'], 'subject_id' => $value['subject_id']])->find();
            }else{
                $res = Db::name('video_class')->where(['grade_id' => $value['grade_id'], 'Semester' => $value['semester'], 'subject_id' => $value['subject_id']])->find();
            }
            if ($res) {
              $infoList[$k]['class_price'] =  $res['price'] * $value['num'];
              $price+=$infoList[$k]['class_price']*$res['Discount'];
                if($value['subject_id']>9){
                    $infoList[$k]['product_id'] = $res['id'];
                }else{
                    $infoList[$k]['video_class_id'] = $res['id'];
                }
              $infoList[$k]['order_num'] = $value['num'];
              $infoList[$k]['discount'] = $res['Discount'];
              $k++;
            } else {
              return jsonMsg('购买的课程不存在', 1);
            }
          }
        }
        if(empty($infoList)){
          return jsonMsg('购买的课程数量不能为零', 1);
        }
        $arrList = array();
        $m = 0;
        foreach ($arr as $key => $value) {
          if(!empty($value['num'])){
            $arrList[$m]['subject'] = $subjectList[$value['subject_id']];
            $arrList[$m]['grade'] = $gradeList[$value['grade_id']];
            if($value['semester'] == 1){
              $arrList[$m]['name'] = '上';
            }else{
              $arrList[$m]['name'] = '下';
            }
            $arrList[$m]['className'] = $arrList[$m]['grade'].$arrList[$m]['subject'].$arrList[$m]['name'];
            $arrList[$m]['num'] = $value['num'];
            $m++;
          }
        }
        $data = array();
        //生成订单号
        $data['order'] = time().mt_rand(100000,999999);
        $data['payMoney'] =  $price;
        $data['money'] = $price;
        $data['user_id'] = $user_id;
        $data['strtime'] =  time();
        $data['order_type'] = $param['order_type'];
        $info = new OrderUser;
        $info->save($data);
        $order_id = $info->id;

        foreach ($infoList as $key => $value) {
          $infoList[$key]['order_id'] = $order_id;
        }
        //添加order_user_info 表
        $res = Db::name('order_user_info')->insertAll($infoList);
        //获取当前管理员的父级的二维码支付图片
        $userList = Db::name('user')->where('uid',$_SESSION['think']['manageinfo']['parent_id'])->find();
        $userList['price'] = $price;
        if($res) {
          return jsonMsg('下单成功',0,$userList,$arrList);
        } else {
          return jsonMsg('下单失败',1);
        }
      }
      else{
        $data = array();
        //生成订单号
        $data['order'] = time().mt_rand(100000,999999);
        $data['payMoney'] =  config('audition_price') * $param['num'];
        $data['money'] = config('audition_price') * $param['num'];
        $data['user_id'] = $user_id;
        $data['strtime'] =  time();
        $data['order_type'] = $param['order_type'];
        $info = new OrderUser;
        $info->save($data);
        $order_id = $info->id;
        $dataInfo = array();
        $dataInfo[0]['class_price'] =  config('audition_price') * $param['num'];
        $dataInfo[0]['video_class_id'] = 0;
        $dataInfo[0]['order_num'] = $param['num'];
        $dataInfo[0]['order_id'] = $order_id;
        $res = Db::name('order_user_info')->insertAll($dataInfo);
        if($res) {
          return jsonMsg('下单成功',0);
        } else {
          return jsonMsg('下单失败',1);
        }
      }
    }
    /**
     * 管理员下单页面 下单提交接口修改 版本三 试听课
     * @author yjz 2019-07-02
     */
    public function insertTextCourse(){
      //版本四
      $code = new Code();
      $list = $code->getCourseSubNum($subject = array(1,2,3,4,5), $array = array(7,8,9),$type=2);
      //获取年级列表
      $gradeList = Db::name('grade')->where('id in (7,8,9)')->select();

      $subjectList = Db::name('subject')->select();
      foreach ($subjectList as $key => $value) {
        $subjectList[$key] = $value['subject'];
      }
      $semesterList = array(1,2);
      $gradeInfo = array();
      $k = 0;
      for($i=1; $i<=2; $i++) {
        foreach ($gradeList as $ke => $val) {
          $gradeInfo[$k]['semester'] = $i;
          $gradeInfo[$k]['grade_id'] = $val['id'];
          $gradeInfo[$k]['grade'] = $val['grade'];
          $gradeInfo[$k]['name'] = $i==1?'上':'下';
          $gradeInfo[$k]['className'] = $gradeInfo[$k]['grade'].$gradeInfo[$k]['name'];
          $k++;
        }
      }
      $gradeInfo[$k]['semester'] = 3;
      $gradeInfo[$k]['grade_id'] = 9;
      $gradeInfo[$k]['className'] = '九年级全册';
      // for($i=1;$i<6;$i++){
      for($i=1;$i<6;$i++){
          foreach ($gradeInfo as $k => $val) {
            $classInfo[$i][$k]['is_have'] = 0;
            foreach ($list[$i] as $key => $value) {
              $classInfo[$i]['subject'] = $subjectList[$i-1];
              if($val['grade_id'] == $value['grade_id'] && $val['semester'] == $value['Semester']){
                $classInfo[$i][$k]['is_have'] = 1;
                $classInfo[$i][$k]['classNum'] = $value['classNum'];
                $classInfo[$i][$k]['grade_id'] = $value['grade_id'];
                $classInfo[$i][$k]['semester'] = $value['Semester'];
                $classInfo[$i][$k]['id'] = $value['id'];
              }
          }
        }
      }
      foreach ($gradeList as $key => $value) {
        $gradeList[$value['id']] = $value['grade'];
      }
      $semesterList = array('1'=>'上','2'=>'下',3=>'全册');
      $this->assign('classInfo', $classInfo);
      $this->assign('subjectList', $subjectList);
      $this->assign('semesterList', $semesterList);
      $this->assign('gradeList', $gradeList);
      $this->assign('uid', $_SESSION['think']['manageinfo']['uid']);
      $this->assign('gradeInfo', $gradeInfo);
      return $this->fetch("order/addTextOrder");
    }
    //获取管理员库存
    public function getMyUserCode(){
      $param = input();
      // if(isset($param['uid'])){
      //   $uid = $param['uid'];
      // }else{
        if($_SESSION['think']['manageinfo']['user_type'] != 1){
          $uid = $_SESSION['think']['manageinfo']['agent_id'];
        }else{
          $uid = $_SESSION['think']['manageinfo']['uid'];
        }
      // }
      //获取可生成权限表
      $userCodeList = Db::name('user_code')->alias('uc')
                      ->join('guard_video_class vc','vc.id = uc.video_class_id','left')
                      ->join('guard_subject s','s.id = vc.subject_id')
                      ->join('guard_grade g','g.id = vc.grade_id')
                      ->field("uc.*,concat(g.grade, s.subject, 
                                    case vc.Semester
                                        when 1 then '上'
                                        when 2 then '下'
                                        when 3 then '全册'
                                    end
                                ) as title, 
                                case uc.type 
                                      when 1 then '正式课数量'    
                                      when 2 then '试听课数量'
                                      end as type_name")
                      ->where(['uc.user_id'=>$uid])
                    ->select();
      //获取试听课数量
      $testCodeList = Db::name('user_code')->where(['video_class_id'=>0,'user_id'=>$uid])->select();
      if($testCodeList){
        $testCodeList[0]['title'] = '试听课';
        $testCodeList[0]['type_name'] = '试听课数量';
        $list = array_merge($userCodeList,$testCodeList);
      }else{
        $list = $userCodeList;
      }
      //获取赠送里列表
      $assignList = Db::name('user_assign_log')
              ->alias('ua')
              ->join('guard_user u','u.uid = ua.accept_user_id')
              ->where('ua.assign_user_id = '.$uid)
              ->field('ua.*,u.username')
              ->order('ua.add_time desc')
              ->select();
      //获取受赠里列表
      $acceptList = Db::name('user_assign_log')
              ->alias('ua')
              ->join('guard_user u','u.uid = ua.accept_user_id')
              ->where('ua.accept_user_id = '.$uid)
              ->field('u.username,ua.*')
              ->order('ua.add_time desc')
              ->select();
      $assignList = $this->getUserInfochange($assignList);
      $acceptList = $this->getUserInfochange($acceptList);
      $this->assign('assignList',$assignList);
      $this->assign('acceptList',$acceptList);
      $this->assign('userCodeList',$list);
      return $this->fetch('/order/orderAuth');
    }
    //数据转换 赠送列表
    public function getUserInfochange($assignList){
     foreach ($assignList as $key => $value) {
          if($value['video_class_id']){
        $video_class_id = explode(',',$value['video_class_id']);
        $classInfo = array();
        //获取详细的数据
        for($i=0; $i<count($video_class_id); $i++){
          $classList = Db::name('video_class')
                  ->alias('vc')
                  ->where('vc.id='.$video_class_id[$i])
                  ->join('guard_grade g','g.id = vc.grade_id')
                  ->join('guard_subject s','s.id = vc.subject_id')
                  ->field('g.grade,s.subject,vc.Semester')
                  ->select();
          foreach ($classList as $ke => $val) {
            $classList[$ke]['Semester'] = $val['Semester'] == 1 ? '上':'下';
            $classInfo[$i] = $classList[$ke]['grade'].'-'.$classList[$ke]['subject'].'-'.$classList[$ke]['Semester'];
          }
        }
        
        $assignList[$key]['video_class'] = implode('，',$classInfo);
      }
      $assignList[$key]['add_time'] = date('Y-m-d',$value['add_time']);
        }
      return $assignList;
  }
  //子代理库存
  // public function 

  //----------------------------------------管理员订单end-----------------------------------------------//


    public function checkCourseData($data)
    {
      if (!is_array($data)) {
        return jsonMsg('参数错误', 1);
      }
      $dataList = [];
      foreach ($data as $key => $value) {
          $grade = $value['grade'];
          $semester = $value['semester'];
          if (isset($value['course']) && !empty($value['course'])) {
            
            foreach ($value['course'] as $k => $v) {
              
              if (!$v['num']) {
                continue;
              }
              
              $dataList[] = [
                'grade_id' => $grade,
                'Semester' => $semester,
                'num'      => $v['num'],
                'subject_id' => $v['id']
              ];
              
            }
          }
      }

      if (!$dataList) {
        return jsonMsg('至少选择一个课程生成数量', 1);
      }

      foreach ($dataList as $key => $value) {
        $where['grade_id'] = $value['grade_id'];
        $where['Semester'] = $value['Semester'];
        $where['subject_id'] = $value['subject_id'];
        $res = Db::name('video_class')->where($where)->find();
        if ($res) {
          $infoList[$key]['video_class_id'] = $res['id'];
          $infoList[$key]['class_price'] = $res['price'];
          $infoList[$key]['Discount'] = $res['Discount'];
          $infoList[$key]['order_num'] = $value['num'];
        }
      }
      return $infoList;
    }
    /**
     * orderPerson列表页面
     * @author 韩春雷 2019.3.26
     */
    public function getOrderPerson()
    {
        
        // 1为我的用户订单， 2为全部用户用户订单， 3为审核员的用户订单,4为全部代理商订单
        $type = input('type');
        if(!$type) {
          $type = 2;
        }
        $group_id = $_SESSION['think']['manageinfo']['group_id'];
        $user_id  = $_SESSION['think']['manageinfo']['uid'];
        $user_type  = $_SESSION['think']['manageinfo']['user_type'];
        $org_id = $_SESSION['think']['manageinfo']['org_id'];
        $agent_id = $_SESSION['think']['manageinfo']['agent_id'];

        // 获取代理商
        if ($group_id == 1) {
            $userList = Db::name('user')->where(['user_type' => 1, 'status' => 1, 'group_id' => ['not in', [1, 2]]])->select();
        } elseif ($group_id == 2) {
            $userList = Db::name('user')->where(['user_type' => 1, 'status' => 1, 'group_id' => ['not in', [1, 2]]])->select();
        } else {
          //获取所有的子类
          $orderPerson = new OrderPerson();
          // if ($user_type == 1) {
          //   $user_oid = $user_id;
          // } else {
          //   $user_oid = $_SESSION['think']['manageinfo']['org_id'];
          // }
          // $userList = $orderPerson->getAllOrgSon($user_oid);
          
          // // 获取自己的
          // $myuser = Db::name('user')->where(['uid' => $user_id])->find();
          // array_push($userList, $myuser);


          if ($type == 3) { // 订单审核, 显示该代理商下的所有订单

            if ($user_type == 1) {
              $agent_id = $user_id;
            } else {
              $agent_id = $agent_id;
            }
            
            $userList = Db::name('user')->where(['agent_id' => $agent_id, 'status' => 1])->select();
            $myuser = Db::name('user')->where(['uid' => $agent_id])->find();
            array_push($userList, $myuser);
          } elseif ($type == 2) { // 全部订单
            if ($user_type == 1) {
              $user_oid = $user_id;
            } else {
              $user_oid = $_SESSION['think']['manageinfo']['org_id'];
            }
            $userList = $orderPerson->getAllOrgSon($user_oid);
            $myuser = Db::name('user')->where(['uid' => $user_id])->find();
            array_push($userList, $myuser);
          } else {
            $userList = $orderPerson->getAllOrgSon($user_id);
          }


        }
        
        $last_names = array_column($userList,'uid');
        array_multisort($last_names,SORT_ASC,$userList);

        if ($type != 1) {
          // 获取媒体
          $where = [];
          if ($group_id == 1 || $group_id == 2) {
              if ($group_id == 2) {
                  $uid = Db::name('user')->where(['group_id' => 1])->value('uid');
                  $where['agent_id'] = ['neq', $uid];
              }
          } else {
              if ($user_type == 1) {
                  $agent_id = $user_id;
              }
              $where['agent_id'] = $agent_id;
          }
        } else {
          if ($user_type == 1) {
              $agent_id = $user_id;
          }
          $where['agent_id'] = $agent_id;
        }
        


        // 媒体
        $medialist = Db::name('to_media')->where($where)->select();
        
        
        // $userList = $this->resort_new($userList, $user_id);
        $this->assign('mediaList', $medialist);
        $this->assign('type', $type);
        $this->assign('userList', $userList);
        $this->assign('isAdmin', ($group_id == 1 || $group_id == 2) ? true : false);
        return view('/order/orderPerson',['title'=>"用户列表"]);
    }
    public function resort_new($data,$pid=0,$level=0)
    {
      static $ret=array();
      foreach($data as $k=>$v){
          if($v['pid']==$parentid){
              $v['level']=$level;
              $ret[]=$v;
              $this->resort_new($data,$v['uid'],$level+1);
          }
      }
      return $ret;
    }

    public function resort($data,$parentid=0,$level=0)
    {
      static $ret=array();
      foreach($data as $k=>$v){
          if($v['parent_id']==$parentid){
              $v['level']=$level;
              $ret[]=$v;
              $this->resort($data,$v['uid'],$level+1);
          }
      }
      return $ret;
    }
    /**
     * getorderPerson列表页面
     * @author 韩春雷 2019.3.26
     */
    public function getOrderPersonList(Request $request)
    {
      $orderPerson = new OrderPerson();
      $param = $request->param();

      $type = (isset($param['type']) && $param['type']) ? $param['type'] : 2; // 2全部的用户订单 1我的用户订单，3审核用户订单
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
      $orderEndTime = isset($param['orderEndTime']) && !empty($param['orderEndTime']) ? $param['orderEndTime'] .' 23:59:59': '';
  
      if ($orderEndTime && $orderEndTime < $orderStartTime) {
        jsonMsg('结束时间不能小于开始时间', 1);
      } 
      
      if ($orderEndTime && $orderStartTime && $orderEndTime >= $orderStartTime) {
        $where['o.strtime'] = ['between time', [$orderStartTime, $orderEndTime]];
      } elseif ($orderStartTime && !$orderEndTime) {
        $where['o.strtime']=['GT', $orderStartTime];
      } elseif($orderEndTime && !$orderStartTime) {
        $where['o.strtime']=['ELT', $orderEndTime];
      }


      // if(array_key_exists('orderStartTime',$param) && $param['orderStartTime']!=''){
      //     $where['o.strtime']=['GT', $param['orderStartTime'].' 00:00:00'];
      // }
      // if(array_key_exists('orderEndTime',$param) && $param['orderEndTime']!=''){
      //     $where['o.strtime']=['ELT', $param['orderEndTime'].' 23:59:59'];
      // }

      if(array_key_exists('uid',$param) && $param['uid']!=''){
          $where['o.user_id']=$param['uid'];
      }

      if(array_key_exists('to_media',$param) && $param['to_media']!=''){
        $where['o.to_media']=$param['to_media'];
      }

      if(array_key_exists('is_forbidden',$param) && $param['is_forbidden'] == 1){
          $where['o.is_forbidden'] = $param['is_forbidden'];
      } else {
        $where['o.is_forbidden'] = ['in', [0,  3]];
      }
      
      // 判断是不是admin
      $isAdmin = isset($param['isAdmin']) && $param['isAdmin'] ? 1: 0;
      if ($isAdmin) {
          $where['isAdmin'] = 1;
      }
      
      // $where['o.is_forbidden'] = ['in', [0,  3]];
     
      $page=$param['page'];
      $limit=$param['limit'];
      $data =  $orderPerson->getOrderPersonList($where, $type,$page,$limit);
      
      foreach ($data as $key => $value) {
        $yesAudition = Db::name('order_person_son')->where(['order_id' => $value['order'], 'is_audition' => 1])->count('id');
        $noAudition = Db::name('order_person_son')->where(['order_id' => $value['order'], 'is_audition' => 0])->count('id');
        $data[$key]['num'] = "试听课程".$yesAudition."门——正常课程".$noAudition."门";
        $data[$key]['type'] = $type;

        $data[$key]['medianame'] = Db::name('to_media')->where(['id' =>$value['to_media']])->value('name');
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
     * 获取order详细信息
     * @author 韩春雷 2019.3.28
     */
    // public function getOneOrderPerson(Request $request){
    //   $param=$request->param();
    //   $data=OrderPerson::getOneOrderPerson($param['id']);
    //   $data[0]['state']=$this->state[$data[0]['state']];
    //   if($data[0]['code_status']){
    //     $data[0]['code_status']=$this->code_status[$data[0]['code_status']];
    //   }
    //   $data[0]['orderCheck']=$this->orderCheck[$data[0]['orderCheck']];
    //   $this->assign("info",$data);
    //   return $this->fetch("order/orderPersonInfo");
    // }

    /**
     * order详细信息
     * @author 杨继州 2019-07-02
     */
    public function getOneOrderPerson(Request $request)
    {
      $param=$request->param();
      $id = (int) $param['id'];
      
      if (!$id) {
        jsonMsg('无效订单数据', 1);
      }

      $data=OrderPerson::getOrderInfoById($id);

      if ($data) {
        $data['medianame'] = Db::name('to_media')->where(['id' => $data['to_media']])->value('name');
      }
     
      $data['state']=$this->state[$data['state']];
      if ($data['orderCheck'] ==1) {
          $data['orderCheck'] = '待审核';
      } elseif($data['orderCheck'] == 2) {
        $data['orderCheck'] = '审核通过';
      } elseif($data['orderCheck'] == 3) {
        $data['orderCheck'] = '审核不通过';
      }

      $paytype = Db::name('order_person_paytype')->alias('a')
            ->field('a.*, b.strtime, c.pay_type')
            ->join('guard_order_person b', 'a.order_id = b.order', 'left')
            ->join('guard_paytype c', 'a.pay_type_id = c.id')
            ->where(['a.order_id' => $data['order']])
            ->select();

      $this->assign("info",$data);
      $this->assign('paytype', $paytype);
      return $this->fetch("order/orderPersonInfo");
    }

    /**
     * 
    * @author  韩春雷 2019/3/28
    * orderPerson删除  已么有删除功能 2019-07-08 yjz
    * @return   [json]   
    */
    public function delOrderPerson(Request $request)
    {
      $param=$request->param();
      $where['id'] = $param['id'];
      $data['is_forbidden'] = 1;
      $orderinfo = Db::name('order_person')->where(['id' => $param['id'], 'is_forbidden' => 3])->find();
      if (!$orderinfo) {
        jsonMsg('该订单不存在或没有发起作废', 1);
      }

      $order_id = $orderinfo['order'];
      // 获取所有子订单总的video_class_id
      $orderSon = Db::name('order_person_son')->where(['order_id' => $order_id])->select();
      $info=new orderPerson();
      Db::startTrans();
      try {
        $info->where($where)->update($data);
        Db::name('order_person_son')->where(['order_id' => $orderinfo['order']])->update(['is_forbidden' => 1]);
        foreach ($orderSon as $key => $value) {
          Db::name('video_log')->where(['video_class_id' => $value['video_class_id'], 'person_id' => $value['person_id']])->delete();
        }
        Db::commit();
        jsonMsg('操作成功', 0);
      } catch(\Exention $e) {
        Db::rollback();
        jsonMsg('操作失败', 1);
      }
    }

    /**
     * 销售发起订单作废
     */
    public function sendOrderDelete(Request $request)
    {
      $param=$request->param();
      $id = $param['id'];
      if (!$id) {
        jsonMsg('该订单不存在', 1);
      }

      $orderinfo = Db::name('order_person')->where(['id' => $id, 'is_forbidden' => 0])->find();
      if (!$orderinfo) {
        jsonMsg('订单不存在或已作废', 1);
      }
      $order_id = $orderinfo['order'];
     
      Db::startTrans();
      try {
        Db::name('order_person_son')->where(['order_id' => $order_id])->update(['is_forbidden' => 3]);
        Db::name('order_person')->where(['id' => $id])->update(['is_forbidden' => 3]);
        Db::commit();
        jsonMsg('发起成功', 0);
      } catch (\Exention $e) {
          Db::rollback();
          jsonMsg('发起作废失败', 1);
      }
    }

    /**
     * 修改orderPerson页面
     * @author 韩春雷 2019.3.28
     */
    //  public function editOrderPerson(Request $request){
    //     $param=$request->param();
    //     $data=OrderPerson::getOneOrderPerson($param['id']);
        
    //     //获取年级列表信息
    //     // $gradeList = Db::name('grade')->select();

    //     if($data[0]['code_status']){
    //       $data[0]['code_status']=$this->code_status[$data[0]['code_status']];
    //     }
    //     $this->assign("info",$data);
    //     // $this->assign("gradeList",$gradeList);
    //     return $this->fetch("order/editOrderPerson");
    // }

    /**
     * 修改订单信息页面
     * @author yangjizhou 2019-07-02
     */

    public function editOrderPerson(Request $request)
    {
      $orderPerson = new OrderPerson();
      $uid = $_SESSION['think']['manageinfo']['uid'];
      $group_id = $_SESSION['think']['manageinfo']['group_id'];
      $agent_id = $_SESSION['think']['manageinfo']['agent_id'];
      $user_type = $_SESSION['think']['manageinfo']['user_type'];

      $param=$request->param();

      $type = isset($param['type']) && !empty($param['type']) ? $param['type'] : 2;

      $id = (int) $param['id'];
      if (!$id) {
        jsonMsg('无效订单数据', 1);
      }

      $data=OrderPerson::getOrderInfoById($id);
      // 获取当前用户角色
      // $userGroupType = $orderPerson->getUserGroup($uid);
      
      // if ($userGroupType && $userGroupType== 2) {
      //   $isFinance = true;
      // } else {
      //   $isFinance = false;
      // }

      // if ($group_id == 1) {
      //   $isFinance = true;
      // }
      // 支付方式
      $paytype = Db::name('order_person_paytype')->alias('a')
            ->field('a.*, b.strtime, c.pay_type')
            ->join('guard_order_person b', 'a.order_id = b.order', 'left')
            ->join('guard_paytype c', 'a.pay_type_id = c.id')
            ->where(['a.order_id' => $data['order']])
            ->select();

      if ($user_type == 1) {
        $agent_id = $uid;
      }
      // 媒体
      $medialist = Db::name('to_media')->where(['agent_id' => $agent_id])->select();

      $this->assign('type', $type);
      $this->assign("info",$data);
      $this->assign('isFinance', $isFinance = '');
      $this->assign('paytype', !empty($paytype) ? $paytype : array());
      $this->assign('mediaList', $medialist);

      return $this->fetch("order/editOrderPerson");
    }

    /**
     * 执行修改操作
     * @author yangjizhou 2019-07-02
     */

    public function doEditOrderPerson(Request $request)
    {
      $param= $request->param();
      $orderPerson = new OrderPerson();
      $payMoney = $param['payMoney'];
      $money = $param['money'];
      $id = $param['id'];
      $remark = isset($param['remark']) ? $param['remark'] : '';
      $to_media = isset($param['to_media']) ? $param['to_media'] : '';
      $state = isset($param['state']) ? $param['state'] : 1;
      $orderCheck = isset($param['orderCheck']) ? $param['orderCheck'] : 1;
      $order_type = $param['order_type'];

      $orderinfo = Db::name('order_person')->where(['id' => $id])->find();

      $uid = $_SESSION['think']['manageinfo']['uid'];

      $data['state'] = $state;
      $data['orderCheck'] = $orderCheck;
      $data['money'] = $money;
      $data['payMoney'] = $payMoney;
      $data['remark'] = $remark;
      $data['u_admin'] = $uid;
      $data['to_media'] = $to_media;
      $data['u_time'] = date('Y-m-d H:i:s', time());
      
      if (!$orderinfo) {
        jsonMsg('订单不存在', 1);
      }

      if ($orderinfo['orderCheck'] != 1) {
        jsonMsg('该订单已审核不能修改', 1);
      }

      // 子订单信息
      $ordersonInfo = Db::name('order_person_son')->where(['order_id' => $orderinfo['order']])->count('id');
      if (!$ordersonInfo) {
        jsonMsg('该订单异常，没有子订单', 1);
      }

      $isAuditionOrderSon = Db::name('order_person_son')->where(['order_id' => $orderinfo['order'], 'is_audition' => 1])->column('video_class_id');

      Db::startTrans();
      try {
        if($orderCheck == 2) {
          //订单支付后才能审核通过
          if($state == 2) {
            //如果该订单是直接下单
            if($order_type == 2) {
              if($orderinfo['state'] != 2){
                $data['kcdqtime'] = date('Y-m-d H:i:s', time());
                $sondata['endtime'] = date('Y-m-d H:i:s', time());
                $data['endtime'] = date('Y-m-d H:i:s', time());
              }
              $updateOrder = Db::name('order_person')->where(['id' => $id])->update($data);
             
            }
          } else {
            jsonMsg("订单未支付, 不能审核通过", 1);
          }
        } else {
          //订单支付时间
          if($state == 2){
            $data['kcdqtime'] = date('Y-m-d H:i:s', time());
            $sondata['endtime'] = date('Y-m-d H:i:s', time());
          }
          $updateOrder = Db::name('order_person')->where(['id' => $id])->update($data);
        }

        if ($updateOrder) {
          $sondata['state'] = $state;
          $sondata['orderCheck'] = $orderCheck;
          
          Db::name('order_person_son')->where(['order_id' => $orderinfo['order']])->update($sondata);

          // 试听课程
          if ($isAuditionOrderSon && $state == 2 && $orderCheck == 2) {
              // 拼接video_class_id 字符串
              $ordersonClassId = implode(',', $isAuditionOrderSon);
              $sondata['type'] = 1;
              Db::name('order_person_son')->where(['order_id' => $orderinfo['order'], 'is_audition' => 1])->update($sondata);
              // setVideoLog($ordersonClassId, $orderinfo['person_id']);
          }



        } 

        if ($orderCheck == 2) {
            // 发送消息——往message表插入一条消息
            $orderSonVideoClassIds = Db::name('order_person_son')->field('video_class_id,product_id')->where(['order_id' => $orderinfo['order']])->select();
            $video_class_ids = array_column($orderSonVideoClassIds,'video_class_id');
            $product_ids = array_column($orderSonVideoClassIds,'product_id');

            $courseName = $orderPerson->getCourseNameString($video_class_ids,$product_ids);

            $msg = "您购买的课程 {$courseName} 已通过审核, 可以前往个人中心进行查看并激活";
            insertMessage($orderinfo['person_id'], '订单审核消息', $msg, 2);

            // 审核通过后推送公众号
//            $weixin = new Weixin();
//            $weixin->sendOrderSuccess($orderinfo['order']);

            // 审核成功后修改用户状态为已购买
            
            $personinfo = Db::name('person')->where(['id' => $orderinfo['person_id']])->find();
            if ($personinfo['status'] != 4) {
                Db::name('person')->where(['id' => $orderinfo['person_id']])->update(['status' => 4]);
            }
        }
        

        Db::commit();
        jsonMsg('修改成功', 0);

      } catch(\Exception $e) {
        Db::rollback();
        return $this->error($e->getMessage());
        jsonMsg('修改失败', 1);
      }
   }





    /**
     * 执行修改orderperson页面
     * @author 韩春雷 2019.3.28
     * @return   [json]   
     */
    //  public function doEditOrderPerson(Request $request){
    //     $param= $request->param();
    //     //获取当前订单的管理员
    //     $orderList = Db::name('order_person')
    //                     ->alias('o')
    //                     ->join('guard_user u','u.uid = o.user_id')
    //                     ->field('u.*,o.user_id,o.orderCheck,o.person_id,o.video_class_id,o.state')
    //                     ->where('o.id = '.$param['id'])->select();

    //     $group_id = $_SESSION['think']['manageinfo']['group_id'];
    //     $uid = $_SESSION['think']['manageinfo']['uid'];
    //     $data = array();
    //     $where['id'] = $param['id'];
    //     $data['state'] = $param['state'];
    //     $data['orderCheck'] = $param['orderCheck'];
    //     $data['payMoney'] = $param['payMoney'];
    //     $data['remark'] = $param['remark'];
    //     $data['u_time'] =time();
    //     $data['u_admin'] =$_SESSION['think']['manageinfo']['uid'];

    //     //获取订单之前的状态 只审核一次(审核成功不能在审核)
    //     if($orderList['0']['orderCheck'] != 2){
    //       if($data['orderCheck'] == 2){
    //         //订单支付后才能审核通过
    //         if($data['state'] == 2){
    //           //如果该订单是直接下单
    //           if($param['order_type'] == 2){
    //             //审核成功 添加用户权限表
    //             $personVideo['class_id'] = $orderList[0]['video_class_id'];
    //             $personVideo['person_id'] = $orderList[0]['person_id'];
    //             $personVideo['user_id'] = $orderList[0]['user_id'];
    //             $personVideo['order_id'] = $param['id'];
    //             $personVideo['add_time'] = time();
    //             Db::name('person_video')->insert($personVideo);
    //             //添加video_log
    //             setVideoLog($orderList[0]['video_class_id'],$orderList[0]['person_id']);
    //           }
    //           if($orderList['0']['state'] != 2){
    //             $data['kcdqtime'] = time();
    //           }
    //           $res = Db::name('order_person')->where($where)->update($data);
    //         }else{
    //           jsonMsg("订单未支付",1);
    //         }
    //       }else{
    //         //订单支付时间
    //         if($data['state'] == 2){
    //           $data['kcdqtime'] = time();
    //         }
    //         $res = Db::name('order_person')->where($where)->update($data);
    //       }
    //     }else{
    //       jsonMsg("该订单已审核成功，不能修改",1);
    //     }

    //     if($res)
    //     {
    //       return jsonMsg('修改成功',0);
    //     }
    //     else
    //     {
    //       return jsonMsg('修改失败',1);
    //     }
    //  }


     /**
     * 添加orderperson页面 卡号下单页面
     * @author 韩春雷 2019.3.28
     */
    public function addOrderPerson(Request $request){
      return $this->fetch("order/addOrderPerson");
    }
    /**
     *执行添加orderPerson 卡号下单 用户下单
     *@author 韩春雷 2019.3.28
     *@return   [json]  
     */
    public function doAddOrderPerson(Request $request){
      $param = $request->param();

      $data = array();
      $data['user_id'] = $_SESSION['think']['manageinfo']['uid'];
      $data['card'] = !empty($param['card']) ? $param['card'] : '';
      $data['nickName'] = !empty($param['nickName']) ? trim($param['nickName']) : '';
      $data['phone'] = !empty($param['phone']) ? trim($param['phone']) : '';
      //获取卡号id
      if($data['card']){
        $codeList = Db::name('code')->where('card ="'.trim($data['card']).'"')->select();
        if(!empty($codeList)){
          if($codeList[0]['person_id'] == 0 && $codeList[0]['status'] == 1){
            $data['code_id'] = $codeList['0']['id'];
          }else{
            return jsonMsg('该卡号已被使用！',1);
          }
        }else{
          return jsonMsg('该卡号不存在！',1);
        }
      }
      unset($data['card']);
      //获取person_id
      if($data['nickName'] && $data['phone']){
        $personList = Db::name('person')->where('nickName ="'.$data['nickName'].'" and phone ="'.$data['phone'].'"')->select();
        if(!empty($personList)){
          $data['person_id'] = $personList['0']['id'];
        }else{
          return jsonMsg('请输入正确的用户信息',1);      
        }
      }
      //根据获取code_id 和person_id 判断课程是否重复购买
      //从code表获取video_class_id
      $codeResult = Db::name('code')->where('id = '.$data['code_id'])->find();
      $result = Db::name('video_log')->where('person_id = '.$data['person_id'].' and video_class_id in ('.$codeResult['coursePackage_id'].')')->select();
      if(!empty($result)){
        return jsonMsg('存在重复购买的课程！',1);
      }

      unset($data['nickName']);
      unset($data['phone']);

      //生成订单号
      $data['order'] = time().mt_rand(100000,999999);
      $data['is_forbidden'] =  0;
      $data['orderCheck'] =  1;
      $data['state'] =  1;
      $data['payMoney'] =  0;
      $data['money'] =  0;
      $data['strtime'] =  time();
      $info = new OrderPerson;
      $res = $info->save($data);
      if($res)
      {
        //下单成功修改code_status
        $codeStatus['status'] = 4;
        Db::name('code')->where('id='.$data['code_id'])->update($codeStatus);
        //下单成功 修改用户的所属管理员
        if($personList[0]['user_id'] != $_SESSION['think']['manageinfo']['uid']){
          $updateDate['user_id'] = $_SESSION['think']['manageinfo']['uid'];
          Db::name('person')->where('id ='.$data['person_id'])->update($updateDate);
        }
        return jsonMsg('下单成功',0);
      }
      else
      {
        return jsonMsg('下单失败',1);
      }
    }
    /**
     *一键下单 管理员下单
     *@author 韩春雷 2019.4.16
     *@return [json]  
     */
    public function quickAddOrder(Request $request){
      $param = $request->param();
      $num = !empty($param['num']) ? $param['num'] : '';
      //获取当前管理员的id
      $uid = $_SESSION['think']['manageinfo']['uid'];
      //获取目前一点通上架的年级
      $grade_id = Db::name('config')->where('cfg_field = "grade_id" and cfg_table = "guard_grade"')
                    ->field('cfg_value')
                    ->find();
      //获取所有的课程
      $classList = Db::name('video_class')->where('grade_id in ('.$grade_id['cfg_value'].')')
                    ->group('subject_id,grade_id,Semester')
                    ->order('id')
                    ->select();
      $video_class = array();
      $video_price = 0;
      $price = 0;
      foreach ($classList as $key => $value) {
        $video_class[$key] = $value['id'];
        $video_price = $video_price + $value['price']* $value['Discount'] * $num;
        $price = $price + $value['price'];
      }
      $video_class_id = implode(',',$video_class);

      $data = array();
      //生成订单号
      $data['order'] = time().mt_rand(100000,999999);
      $data['payMoney'] =  0;
      $data['order_type'] = 2;
      $data['money'] = $video_price;
      $data['user_id'] = $uid;
      $data['strtime'] =  time();
      $info = new OrderUser;
      $info->save($data);
      $order_id = $info->id;

      $infoList = array();
      $infoList['order_id'] = $order_id;
      $infoList['video_class_id'] = $video_class_id;
      $infoList['order_num'] = $num;
      $infoList['class_price'] = $price;

      //添加order_user_info 表
      $res = Db::name('order_user_info')->insert($infoList);

      if($res)
      {
        return jsonMsg('下单成功',0);
      }
      else
      {
        return jsonMsg('下单失败',1);
      }
    }

    // public function addClassOrder(Request $request)
    // {
    //   $phone = $request->param('phone');
    //   $subjectArr = [1, 2, 3, 4, 5];
    //   $phone = trim($phone);
    //   $uid = $_SESSION['think']['manageinfo']['uid']; 
    //   $group_id = $_SESSION['think']['manageinfo']['group_id']; // 1管理员

    //   $personInfo = Db::name('person')->where(['phone' => $phone])->find();

    //   // 输入用户不存在
    //   // if (!$phone || !$personInfo) {
    //   //     return $this->fetch("/order/addClassOrder");
    //   // }
    //   // $person_id = $personInfo['id'];
    //   // 查询课程列表
    //   $subject = Db::name('subject')->field('id, subject')->where(['id' => ['in', $subjectArr]])->select();
    //   $subject = $this->convert_arr_key($subject, 'id');
      
    //   // 年级
    //   $grade = [7, 8, 9];
    //   // 学期
    //   $semester = [1, 2, 3];
    //   // 结果
    //   $result = array();
    //   $results = array();
    //   $course = array();
    //   foreach ($subject as $key => $value) {
    //     foreach ($grade as $kl => $vl) {
    //       foreach ($semester as $k => $v) {
    //         if (($vl == 7 || $vl == 8) && $v == 3) {
    //           continue ;
    //         }
    //         // 判断课程是否存在
    //         $videoClassId = Db::name('video_class')->where(['grade_id' => $vl, 'subject_id' => $key, 'Semester' => $v])->value('id');
            
    //         if (!$videoClassId) {
    //           // 当前年级的课程不存在，数据库灭有这个课程
    //           $subject[$key][$vl][$v]['exist'] = 0;  // 该课程不存在年级学期中

    //           $result['exist'] =  0;

    //         } else {
    //           $subject[$key][$vl][$v]['exist'] = 1; // 该课程存在

    //           $result['exist'] =  1;
              
    //           // $subject[$key][$vl][$v]['audition'] = $this->getCourseNum($videoClassId, 1); // 正式课数量
    //           // $subject[$key][$vl][$v]['noaudition'] = $this->getCourseNum($videoClassId, 2); // 试听课数量
    //           $result['audition'] =  $this->getCourseNum($videoClassId, 1);;
    //           $result['noaudition'] =  $this->getCourseNum($videoClassId, 2);
    //           // 用户存在
    //           if ($personInfo) {
    //             $judegOrder = $this->judgeOrder($videoClassId, $person_id); // 是否有订单
    //             if ($judegOrder) {
    //               // $subject[$key][$vl][$v]['isOrder'] = 1; // 有订单
    //               // $subject[$key][$vl][$v]['orderCheck'] = !empty($judegOrder['orderCheck']) ? $judegOrder['orderCheck'] : ''; // 订单审核状态，1待审核， 2审核通过
    //               // $subject[$key][$vl][$v]['type'] = $judegOrder['type']; // 订单激活状态， 0待激活， 1激活， -1禁用
    //               // $subject[$key][$vl][$v]['is_audition'] = $judegOrder['is_audition']; // 课程，0正常课， 1试听课
    //               // $subject[$key][$vl][$v]['suplerTime'] = $judegOrder['suplerTime']; // 剩余时间
    //               $result['isOrder'] = 1;
    //               $result['orderCheck'] =  $judegOrder['orderCheck'];
    //               $result['type'] =  $judegOrder['type'];
    //               $result['is_audition'] =  $judegOrder['is_audition'];
    //               $result['suplerTime'] =  $judegOrder['suplerTime'];
    //             } else {
    //               // $subject[$key][$vl][$v]['isOrder'] = 0; // 没有订单
    //               $result['isOrder'] =  0;

    //             }
                
    //           } 
    //         }
    //         // $subject[$key][$vl][$v]['isAdmin'] = ($group_id == 1) ? true : false;
    //         $result['isAdmin'] = ($group_id == 1) ? true : false;
    //         array_push($results, $result);
    //       }
    //     }
    //     array_push($course, $results);
       
    //   }
    //   dump($course);die;
    //   $this->assign('list', $subject);
    //   return $this->fetch("/order/addClassOrder");
    // }

    /**
     * 老用户下单 页面
     * @author 杨继州
     */
    public function addClassOrder(Request $request)
    {
      $phone = $request->param('phone');
      $subjectArr = [1, 2, 3, 4, 5];
      $phone = trim($phone) ? $phone : '';
      $uid = $_SESSION['think']['manageinfo']['uid']; 
      $group_id = $_SESSION['think']['manageinfo']['group_id']; // 1管理员

      $orderPerson = new OrderPerson();
      $personInfo = Db::name('person')->where(['phone' => $phone])->find();

      // 输入用户不存在
      if ($phone || $personInfo) {
        $person_id = $personInfo['id'];
      }
      
      // 查询课程列表
      $subject = Db::name('subject')->field('id, subject')->where(['id' => ['in', $subjectArr]])->select();
      $subject = $this->convert_arr_key($subject, 'id');
      
      // 年级
      $grade = [7, 8, 9];
      // 学期
      $semester = [1, 2, 3];
      // 结果
      $result = array();
      foreach ($subject as $key => $value) {
        foreach ($grade as $kl => $vl) {
          foreach ($semester as $k => $v) {
            if (($vl == 7 || $vl == 8) && $v == 3) {
              continue ;
            }
            // 判断课程是否存在
            $videoClassId = Db::name('video_class')->where(['grade_id' => $vl, 'subject_id' => $key, 'Semester' => $v])->find();
            
            if (!$videoClassId) {
              // 当前年级的课程不存在，数据库灭有这个课程
              $subject[$key][$vl][$v]['exist'] = 0;  // 该课程不存在年级学期中

            } else {
              $subject[$key][$vl][$v]['exist'] = 1; // 该课程存在
              
              $subject[$key][$vl][$v]['audition'] = $orderPerson->getCourseNum($videoClassId['id'], 1); // 正式课数量
              $subject[$key][$vl][$v]['noaudition'] = $orderPerson->getCourseNum($videoClassId['id'],2); // 试听课数量
              $subject[$key][$vl][$v]['price'] = !empty($videoClassId['price']) ? $videoClassId['price'] : 0; // 试听课数量
              // 用户存在
              if ($personInfo) {
                $judegOrder = $this->judgeOrder($videoClassId['id'], $person_id); // 是否有订单
                if ($judegOrder) {
                  $subject[$key][$vl][$v]['isOrder'] = 1; // 有订单
                  $subject[$key][$vl][$v]['orderCheck'] = !empty($judegOrder['orderCheck']) ? $judegOrder['orderCheck'] : ''; // 订单审核状态，1待审核， 2审核通过
                  $subject[$key][$vl][$v]['type'] = $judegOrder['type']; // 订单激活状态， 0待激活， 1激活， -1禁用
                  $subject[$key][$vl][$v]['is_audition'] = $judegOrder['is_audition']; // 课程，0正常课， 1试听课
                  $subject[$key][$vl][$v]['suplerTime'] = $judegOrder['suplerTime']; // 剩余时间
                } else {
                  $subject[$key][$vl][$v]['isOrder'] = 0; // 没有订单

                }
              } 
            }
          }
        }
      }

      $this->assign('isAdmin', ($group_id == 1) ? true : false);
      $this->assign('list', $subject);
      $this->assign('phone', $phone);
      return $this->fetch("/order/addClassOrder");
    }
  

    /**
     * 获取当前管理员的试听课、正式课数量
     * @param $video_class_id 课程id
     * @param $type  课程类型  1正常 2试听
     */
    public function getCourseNum($video_class_id, $type)
    {
      $uid = $_SESSION['think']['manageinfo']['uid']; 
      $group_id = $_SESSION['think']['manageinfo']['group_id']; // 1管理员
      $count = 0;
      if ($group_id != 1) {
        $count = Db::name('user_code')->where(['video_class_id' => $video_class_id, 'user_id' => $uid, 'type' => $type])->value('code_num');
      }
      return $count;
    }

    /**
     * 判断用户是否有订单并返回订单信息
     */
    public function judgeOrder($video_class_id, $person_id)
    {
      // 正常课
      $res = Db::name('order_person_son')
            ->field('video_class_id, orderCheck, type, is_audition')
            ->where(['video_class_id' => $video_class_id, 'person_id' => $person_id, 'orderCheck' => ['in', [1, 2]], 'is_forbidden' => 0, 'is_audition' => 0])
            ->find();
      if (!$res) {
        // 试听课
        $aud = Db::name('order_person_son')
                ->field('video_class_id, orderCheck, type, is_audition')
                ->where(['video_class_id' => $video_class_id, 'person_id' => $person_id, 'orderCheck' => ['in', [1, 2]], 'is_forbidden' => 0, 'is_audition' => 1])
                ->find();
      }

      $result = !empty($res) ? $res : (!empty($aud) ? $aud : array());

      // 查询课程剩余时间
      if ($result) {
        $suplerTime = $this->getSuplerCourseTime($result['video_class_id'], $person_id);
        $result['suplerTime'] = $suplerTime;
      }

      return $result;

    }

    /**
     * 获取课程剩余时间
     */
    public function getSuplerCourseTime($video_class_id, $person_id)
    {
      $suplerTime = 0;
      $videoLog = Db::name('video_log')->field('video_class_id, type, expireTime')->where(['video_class_id' => $video_class_id, 'person_id' => $person_id])->find();
      if ($videoLog['type'] == 1) {
        $suplerTime = $videoLog['expireTime'];
      } else {
        $suplerTime = $videoLog['expireTime'] - time();
      }
      $suplerTime = $this->time2string($suplerTime);
      return $suplerTime;
    }

    /**
     * 根据科目、年级、学期、用户查询处理课程剩余量或用户订情况
     */
    public function getCheckOrderByperson($subject_id, $grade_id, $semester, $person_id = null)
    {
      $uid = $_SESSION['think']['manageinfo']['uid']; 
      $group_id = $_SESSION['think']['manageinfo']['group_id']; // 1管理员

      if (!$person_id) {
        // 查询当前管理员剩余数量
        if ($group_id != 1) {

        }
      }
    }

    // public function checkOrderSonCourse($personOrderSon)
    // {
    //     $arr = array();

    //     $res = Db::name('video_class')->alias('a')
    //                 ->field("a.*, b.subject,
    //                         concat(c.grade, b.subject,
    //                         case a.Semester
    //                             when 1 then '上学期'
    //                             when 2 then '下学期'
    //                             when 3 then '全册'
    //                         end
    //                     ) as title,
    //                     c.grade
    //                 ")
    //                 ->join('guard_subject b', 'a.subject_id = b.id')
    //                 ->join('guard_grade c', 'a.grade_id = c.id')
    //                 // ->join('')
    //                 ->where(['a.id' => ['in', $personOrderSon]])
    //                 // ->group('title')
    //                 ->select();
       
    //     foreach ($videoClassId as $key => $value) {

    //     }
    // }


    /**
     *直接下单 用户下单
     *@author 韩春雷 2019.4.17
     *@return [json]  
     */
    // public function addClassOrder(Request $request){
    //   // //获取年级列表
    //   $gradeList = Db::name('grade')->select();
    //   //获取课程列表
    //   $subjectList = Db::name('subject')->select();
    //   $a = 1;
    //   $this->assign('gradeList',$gradeList);
    //   $this->assign('a',$a);
    //   $this->assign('subjectList',$subjectList);

    //   // 修改为固定课程 2019-07-01 杨继州

    //   // 判断是否是管理员
    //   $group_id = $_SESSION['think']['manageinfo']['group_id'];
      
    //   $this->assign('isAdmin', $group_id == 1 ? true : false);
      
    //   // 获取当前管理代理商的课程剩余数量
    //   $code = new Code();
    //   // 七年级
    //   $sevenGradeUpperSem = $code->getCourseSurplusNum($grade = 7, $semester=1, $array = array(1, 2, 3));
    //   $sevenGradeLowerSem = $code->getCourseSurplusNum($grade = 7, $semester=2, $array = array(1, 2, 3));
    //   // 八年级
    //   $eightGradeUpperSem = $code->getCourseSurplusNum($grade = 8, $semester=1, $array = array(1, 2, 3, 4));
    //   $eightGradeLowerSem = $code->getCourseSurplusNum($grade = 8, $semester=2, $array = array(1, 2, 3, 4));

    //   // 九年级
    //   $nineGradeUpperSem = $code->getCourseSurplusNum($grade = 9, $semester=1, $array = array(1, 2, 3, 4, 5));
    //   $ninenGradeLowerSem = $code->getCourseSurplusNum($grade = 9, $semester=2, $array = array(1, 2, 3, 4, 5));

    //   $this->assign('sevenGradeUpperSem', $sevenGradeUpperSem);
    //   $this->assign('sevenGradeLowerSem', $sevenGradeLowerSem);
    //   $this->assign('eightGradeUpperSem', $eightGradeUpperSem);
    //   $this->assign('eightGradeLowerSem', $eightGradeLowerSem);
    //   $this->assign('nineGradeUpperSem', $nineGradeUpperSem);
    //   $this->assign('ninenGradeLowerSem', $ninenGradeLowerSem);

    //   return $this->fetch("/order/addClassOrder");
    // }
    /**
     *执行直接下单 用户下单
     *@author 韩春雷 2019.4.17
     */
    // public function doAddClassOrder(Request $request){
    //   $param=$request->param();
      
    //   $data = array();
    //   $number = !empty($param['number'])? intval($param['number']) : '';
    //   $nickName = !empty($param['nickName'])? $param['nickName'] : '';
    //   $phone = !empty($param['phone'])? $param['phone'] : '';

    //   $infoList = array();
    //   $m = 0;
    //   //将传递过来参数 组成一个数组
    //   for($i=0; $i<$number; $i++){
    //     if(!empty($param['subject_id'.$i])){
    //       $subject_id = array_values($param['subject_id'.$i]);
    //       for($j=0; $j<count($subject_id); $j++){
    //         //查询对应video_class表里的信息
    //         $where['grade_id'] = $param['grade_id'.$i];
    //         $where['Semester'] = $param['Semester'.$i];
    //         $where['subject_id'] = $subject_id[$j];
    //         $res = Db::name('video_class')->where($where)->find();
    //         if($res){
    //           $infoList[$m]['coursePackage_id'] = $res['id'];
    //           $infoList[$m]['price'] = $res['price'];
    //           $infoList[$m]['Discount'] = $res['Discount'];
    //         }else{
    //           //获取课程列表
    //           $subjectList = Db::name('subject')->select();
    //           //获取年级列表
    //           $gradeList = Db::name('grade')->select();

    //           foreach ($subjectList as $key => $value) {
    //             $subjectListArr[$value['id']] = $value['subject'];
    //           }
    //           foreach ($gradeList as $key => $value) {
    //             $gradeListArr[$value['id']] = $value['grade'];
    //           }
    //           if($param['Semester'.$i] == 1){
    //             $semesterStr = '上学期';
    //           }elseif($param['Semester'.$i] == 2){
    //             $semesterStr = '下学期';
    //           }
    //           $grade_id = $param['grade_id'.$i];
    //           $subject_id = $subject_id[$j];
    //           return jsonMsg($gradeListArr[$grade_id].$subjectListArr[$subject_id].$semesterStr."课程不存在",1);
    //         }
    //         $m++;
    //       }
    //     }else{
    //       return jsonMsg('请选泽课程信息',1);
    //     }
    //   }
    //   //获取课程id数组coursePackage_id
    //   $coursePackage_id = array();
    //   $price = 0;
    //   for($i=0; $i<count($infoList); $i++){
    //       $coursePackage_id[$i] = $infoList[$i]['coursePackage_id'];
    //       $price =$price + $infoList[$i]['price']*$infoList[$i]['Discount'];
    //   }
    //   if($nickName && $phone){
    //     $personList = Db::name('person')->where('nickName ="'.$nickName.'" and phone ="'.$phone.'"')->select();

    //     if($personList){
    //       $data['person_id'] = $personList['0']['id'];
    //     }else{
    //       return jsonMsg('请输入正确的用户信息',1);      
    //     }
    //   }
    //   //生成订单号
    //   $data['order'] = time().mt_rand(100000,999999);
    //   $data['payMoney'] =  0;
    //   $data['money'] =  $price;
    //   $data['video_class_id'] = implode(',',$coursePackage_id);
    //   //判断 video_class 是否存在该课程
    //   $result = Db::name('video_log')->where('person_id = '.$data['person_id'].' and video_class_id in ('.$data['video_class_id'].')')->select();
    //   if(!empty($result)){
    //     return jsonMsg('存在重复购买的课程！',1);
    //   }
    //   $data['order_type'] = 2;
    //   $data['user_id'] = $_SESSION['think']['manageinfo']['uid'];
    //   $data['strtime'] =  time();

    //   $info = new OrderPerson;
    //   $res = $info->save($data);
    //   if($res)
    //   {
    //     //下单成功 修改用户的所属管理员
    //     if($personList[0]['user_id'] != $_SESSION['think']['manageinfo']['uid']){
    //       $updateDate['user_id'] = $_SESSION['think']['manageinfo']['uid'];
    //       Db::name('person')->where('id ='.$data['person_id'])->update($updateDate);
    //     }
    //     return jsonMsg('下单成功',0);
    //   }
    //   else
    //   {
    //     return jsonMsg('下单失败',1);
    //   }
    // }


    /**
     *执行直接下单 用户下单
     *@author 杨继州 2019.7.1
     */
    public function doAddClassOrder(Request $request)
    {
      $orderPerson = new OrderPerson();
      $param=$request->param();
      $data = array();
      $phone = $param['phone'];
      $datas = isset($param['subject_id']) ? $param['subject_id'] : array();
     
      $dicount = (float) (isset($param['dicount']) && !empty($param['dicount'])) ? $param['dicount'] : 0;
      $payMoney = (float) (isset($param['final']) && !empty($param['final'])) ? $param['final'] : 0;
      $money = (float) (isset($param['totalPrice']) && !empty($param['totalPrice'])) ? $param['totalPrice'] : 0;
      
      if ($dicount > $money) {
        jsonMsg('优惠金额不能大于订单总额', 1);
      }
      
      if (!$phone) {
        return jsonMsg('请输入手机号码', 1);
      }
      if (!$datas) {
        return jsonMsg('请输入下单的课程', 1);
      }

      $person = Db::name('person')->where(['phone' => $phone])->find();

      if($person){
        $person_id = $person['id'];
      }else{
        return jsonMsg('请输入正确的用户手机信息', 1);      
      }

      if ($person['act_status'] == 3) {
        jsonMsg('当前账号已被禁用，请联系管理员处理', 1);
      }
      
     
      // 接收数据处理
      $arr = array();
      foreach ($datas as $key => $value) {
        foreach ($value as $kl => $vl) {
          foreach ($vl as $k => $v) {
            
            if (isset($v['isAudition'])) {
              if ($v['isAudition'] == 1) {
                // 试听
                $isAudition = 1;
              } elseif ($v['isAudition'] == 0) {
                // 所有课程
                $isAudition = 0;
              }
            } else {
              continue ;
            }
            $arr[] = [
              'grade_id' => $key,
              'semester' => $kl,
              'subject_id' => $k,
              'isAudition' => $isAudition
            ];

          }
        }
      }
      
      $group_id = $_SESSION['think']['manageinfo']['group_id'];
      // 订单数据插入

      // $payMoney = 0; // 订单实际支付金额
      // $money = 0; // 订单金额
      $user_id = $_SESSION['think']['manageinfo']['uid'];

      // 获取代理id：说明如果当前登录用户属于代理，则返回当前用户id，如果不是反回最近的上级代理ID
      $agendId = $orderPerson->judgeIsAgent($user_id);
      if ($group_id !== 1) {
        if (!$agendId) {
          jsonMsg('该用户或所属代理不存在', 1);
        }
      }

      $orderSon = array();
      foreach ($arr as $key => $value) {

        $res = Db::name('video_class')->where(['grade_id' => $value['grade_id'], 'Semester' => $value['semester'], 'subject_id' => $value['subject_id']])->find();
        
        if ($res) {
          // $payMoney +=  empty($res['Discount']) ? ($res['price']) : ($res['price'] * $res['Discount']);
          // $money +=  $res['price'];
          
          // 除了超级管理员有无限生成
          if ($group_id !== 1) {
            // 判断user_code 是否还有剩余数量
            if ($value['isAudition'] == 1) {
              $isAudition = 2;
            } elseif ($value['isAudition'] == 0) {
              $isAudition = 1;
            }

            $supler = Db::name('user_code')->where(['user_id' => $agendId, 'video_class_id' => $res['id'], 'type' => $isAudition])->value('code_num');
      
            if ($supler < 1) {
              $msg = '您的'. $res['name'] . (($res['Semester'] == 1) ? '上学期' : '下学期'). (($value['isAudition'] == 1) ? '试听课权限' : '正常课权限') .'剩余量不足！';
              jsonMsg($msg, 1);
            }
          }

          $orderSon[$key]['video_class_id'] = $res['id'];
          $orderSon[$key]['num'] = 1;
          $orderSon[$key]['is_audition'] = $value['isAudition'];
          $orderSon[$key]['person_id'] = $person_id;
          $orderSon[$key]['money'] = $res['price'];
          $orderSon[$key]['payMoney'] = empty($res['Discount']) ? ($res['price']) : ($res['price'] * $res['Discount']);
          $orderSon[$key]['person_id'] = $person_id;
          $orderSon[$key]['order_type'] = 2;

          // 判断是否有购买
          $isBuy = $this->isBuyByVideoId($res['id'], $person_id, $value['isAudition']);
          if ($isBuy && $isBuy['is_audition'] != 1) {
            $msg = '您的'. $res['name'] . (($res['Semester'] == 1) ? '上学期' : '下学期').'课程已购买！';
            jsonMsg($msg, 1);
          } elseif ($isBuy && ($isBuy['is_audition'] == 0) && $value['isAudition'] == 0) {
            $msg = '您的'. $res['name'] . (($res['Semester'] == 1) ? '上学期' : '下学期').'试听课程已购买！';
            jsonMsg($msg, 1);
          }
        } else {
            jsonMsg('购买的课程不存在', 1);
        }
      }

      $info = new OrderPerson;
      $orderid = time().mt_rand(100000,999999);
      $order['order'] = $orderid;
      $order['money']  = $money;
      $order['person_id'] = $person_id;
      $order['strtime'] = date('Y-m-d H:i:s', time());
      $order['payMoney'] = $payMoney;
      $order['user_id'] = $user_id;
      $order['order_type'] = 2;
      $order['discount_price'] = $dicount; // 订单优惠金额

      // 下单执行事务
      Db::startTrans();
      try {
       
        $res = $info->save($order);
        
        if($res) {
          foreach ($orderSon as $key => $value) {
            $orderSon[$key]['order_id'] = $orderid;
            $orderSon[$key]['order_son_id'] = time().mt_rand(100000,999999);
            $orderSon[$key]['strtime'] = date('Y-m-d H:i:s', time());
            
            // 把试听课程生成video_log表
            // if ($value['is_audition'] == 1) {
            //   $orderSon[$key]['orderCheck'] = 1;
            //   $orderSon[$key]['state'] = 2;
            //   $orderSon[$key]['type'] = 1;
            //   setVideoLog($value['video_class_id'], $person_id);
            // }
            Db::name('order_person_son')->insert($orderSon[$key]);
          }
          
          // 除了超级管理员有无限生成
          if ($group_id !== 1) {
            foreach ($orderSon as $key => $value) {
              if ($value['is_audition'] == 1) {
                $isAudition = 2;
              } elseif ($value['is_audition'] == 0) {
                $isAudition = 1;
              }
              Db::name('user_code')->where(['user_id' => $agendId, 'video_class_id' => $value['video_class_id'], 'type' => $isAudition])->setDec('code_num');
            }
          }

          // 修改的课程video_class_id 
          $videoClassId = array_column($orderSon, 'video_class_id');
          $this->updateVideoClassPurchase($videoClassId);

          // 修改用户状态
          if ($person['status'] == 1) {
            Db::name('person')->where(['id' => $person['id']])->update(['status' => 3]);
          }

          Db::commit();
          jsonMsg('下单成功',0);
        } 
      } catch (\Exception $e) {
        // 回滚事务
        Db::rollback();
        return $this->error($e->getMessage());
        // jsonMsg('添加失败', 1);
      }

    }

  /**
   * 下单点击提交按钮获取预下单信息
   *  @author 杨继州 2019-07-08
   * 
   */
  public function getPreorderInfo(Request $request)
  {
    $param=$request->param();
    $data = array();

    $phone = $param['phone'];

    $datas = isset($param['subject_id']) ? $param['subject_id'] : array();
    if (!$phone) {
      jsonMsg('请输入手机号', 1);
    }
    if (!$datas) {
      jsonMsg('请选择要下单的课程', 1);
    }

    $person = Db::name('person')->where(['phone' => $phone])->find();

    if($person) {
      $person_id = $person['id'];
    } else {
      return jsonMsg('请输入正确的用户手机信息', 1);      
    }

    if ($person['act_status'] == 3) {
      jsonMsg('当前账号已被禁用，请联系管理员处理', 1);
    }
    
    
    // 接收数据处理
    $arr = array();
    foreach ($datas as $key => $value) {
      foreach ($value as $kl => $vl) {
        foreach ($vl as $k => $v) {
          
          if (isset($v['isAudition'])) {
            if ($v['isAudition'] == 1) {
              // 试听
              $isAudition = 1;
            } elseif ($v['isAudition'] == 0) {
              // 所有课程
              $isAudition = 0;
            }
          } else {
            continue ;
          }
          $arr[] = [
            'grade_id' => $key,
            'semester' => $kl,
            'subject_id' => $k,
            'isAudition' => $isAudition
          ];
        }
      }
    }
    
    $result = array();
    $price = 0;
    foreach ($arr as $key => $value) {
      $res = Db::name('video_class')->alias('a')
            ->field("a.*, b.subject,
                    concat(c.grade, '-', b.subject, '-', 
                    case a.Semester
                        when 1 then '上学期'
                        when 2 then '下学期'
                        when 3 then '全册'
                    end
                ) as title,
                c.grade
            ")
            ->join('guard_subject b', 'a.subject_id = b.id')
            ->join('guard_grade c', 'a.grade_id = c.id')
            ->where(['a.grade_id' => $value['grade_id'], 'Semester' => $value['semester'], 'subject_id' => $value['subject_id']])
            ->find();
            
      $price += ($value['isAudition'] == 1) ? (!empty($res['audition_price']) ? $res['audition_price'] : 0) : $res['price'];
      $data['courseName'] = $res['title'];
      $data['price'] = ($value['isAudition'] == 1) ? (!empty($res['audition_price']) ? $res['audition_price'] : 0) : $res['price'];
      $data['isAudition'] = $value['isAudition'] == 1 ? '(试听)': '';
      array_push($result, $data);
    }

    $datas = [
      'data' => $result,
      'totalPrice' => $price
    ];
    jsonMsg('success', 0, $datas);
  }

  /**
   * 判断是否已购买
   */
  public function isBuyByVideoId($video_class_id, $person_id)
  {
      $res = Db::name('order_person_son')->where(['video_class_id' => $video_class_id, 'person_id' => $person_id, 'is_forbidden' => 0])->find();
      return $res;
  }

    /**
	 * 验证是否选择了两个及以上的相同的班级及学期-修改后
	 * @author 2019-06-18 yangjizhou
	 */
	public function judgeLast($param)
	{
		$judeg = [];
		if (empty($param)) {
			return jsonMsg('没有选择课程', 1);
		}
		foreach ($param as $key => $value) {
			$judeg[] = [
				'grade_id' => $value['grade'],
				'semester' => $value['semester']
			];
		}

		$oldCount = count($judeg);

		$resArr = $this->remove_duplicate($judeg);
		$newCout = count($resArr);
		$res = ($oldCount == $newCout) ? true : false;
		if (!$res) {
			return jsonMsg('不能同时选择两个及以上的相同的班级学期', 1);
		}

  }
  
  /**
	 * 二维数组去重
	 * @author 2019-06-11 yangjizhou
	 */

	private function remove_duplicate($data)
	{
		foreach ($data[0] as $key => $value) {
			$arr_inner_key[] = $key;
		}
		foreach ($data as $k => $v) {
			$v = join(',', $v);
			$temp[$k] = $v;
		}

		$temp = array_unique($temp);
		foreach ($temp as $k => $v) {
			$a = explode(',', $v);
			$arr_after[$k] = array_combine($arr_inner_key, $a);
		}

		return $arr_after;
  }
  
  /**
   * 根据订单查看看课权限
   */
  public function checkCourseListByOrderId(Request $request)
  {
      $order_id = $request->param('order_id');
     
      $orderPerson = new OrderPerson();
      $courseList = $orderPerson->getCourseAuthByOrderId($order_id);

      $res = array(
        'code'=>0,
        'msg'=>'success',
        'data'=>$courseList
      );
      echo json_encode($res);

  }

  /**
   * 下单成功后修改课程的购买人数
   * @author 杨继州  2019-07-05
   * 
   */
  private function updateVideoClassPurchase($video_class_id)
  {
      Db::name('video_class')->where(['id' => ['in', $video_class_id]])->setInc('purchase');
  }

  /**
	 *  给某个数组值作为键
	 */
	public function convert_arr_key($arr, $key_name) 
	{ 
		$arr2 = []; 
		foreach($arr as $key => $val){ 
			$arr2[$val[$key_name]] = $val;         
		} 
		return $arr2; 
  } 
  
  /**
     * 时间戳转换格式
     */
    public function time2string($second)
    {
        if ($second < 60) {
            return '已过期';
        }
        $day = floor($second/(3600*24));
        $second = $second%(3600*24);//除去整天之后剩余的时间
        $hour = floor($second/3600);
        $second = $second%3600;//除去整小时之后剩余的时间 
        $minute = floor($second/60);
        $second = $second%60;//除去整分钟之后剩余的时间 
        //返回字符串

        return $day.'天'.$hour.'小时'.$minute.'分';
    }

  /**
   * 临时订单生成
   */
  public function getOrderNow(Request $request)
  {
    $param=$request->param();
    $data = array();
    
    $datas = isset($param['subject_id']) ? $param['subject_id'] : array();
    dump($datas);die;
    // 接收数据处理
    $arr = array();
    foreach ($datas as $key => $value) {
      foreach ($value as $kl => $vl) {
        foreach ($vl as $k => $v) {
          
          if (isset($v['isAudition'])) {
            if ($v['isAudition'] == 1) {
              // 试听
              $isAudition = 1;
            } elseif ($v['isAudition'] == 0) {
              // 所有课程
              $isAudition = 0;
            }
          } else {
            continue ;
          }
          $arr[] = [
            'grade_id' => $key,
            'semester' => $kl,
            'subject_id' => $k,
            'isAudition' => $isAudition
          ];
        }
      }
    }
    $result = array();
    $price = 0;
    foreach ($arr as $key => $value) {
      $res = Db::name('video_class')->alias('a')
            ->field("a.*, b.subject,
                    concat(c.grade, '-', b.subject, '-', 
                    case a.Semester
                        when 1 then '上学期'
                        when 2 then '下学期'
                        when 3 then '全册'
                    end
                ) as title,
                c.grade
            ")
            ->join('guard_subject b', 'a.subject_id = b.id')
            ->join('guard_grade c', 'a.grade_id = c.id')
            ->where(['a.grade_id' => $value['grade_id'], 'Semester' => $value['semester'], 'subject_id' => $value['subject_id']])
            ->find();

      $price += ($value['isAudition'] == 1) ? (!empty($res['audition_price']) ? $res['audition_price'] : 0) : $res['price'];
      $data['courseName'] = $res['title'];
      $data['price'] = ($value['isAudition'] == 1) ? (!empty($res['audition_price']) ? $res['audition_price'] : 0) : $res['price'];
      $data['isAudition'] = $value['isAudition'] == 1 ? '(试听)': '';
      array_push($result, $data);
    }

    $datas = [
      'data' => $result,
      'totalPrice' => $price
    ];
    jsonMsg('success', 0, $datas);
  }

  /**
   * 用户订单作废
   */
  public function personOrderRefund()
  {
      $param = input();
      dump($param);
  }
  
}