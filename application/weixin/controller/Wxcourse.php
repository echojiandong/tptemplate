<?php 
namespace app\weixin\controller;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use app\index\model\courseModel;
use app\weixin\model\PersonModel as WxM;

class Wxcourse extends Controller{
    //微信小程序播放页面数据
    public function courseParticulars(){
        $param = input();
        $productStatus = $param['productStatus']??0;
        if(isset($productStatus) && $productStatus==1){
            $where['productStatus']=$param['productStatus'];
        }
        //查找面包屑与课程简介
        $where['id']=$param['id'];
        $getCrumbs=$this->getCrumbs($where);
        //查找课程老师信息
        $whereTeacher['t.id']=$getCrumbs['teacherId'];
        $teacherInfo=Db::name('teacher')->alias('t')->join('guard_subject s','s.id = t.subject_id')->field('t.*,s.subject')->where($whereTeacher)->find();
        //查找播放课程知识点
        if($productStatus!=1){
            $whereCourse['kid']=$param['id'];
        }
        $whereCourse['part']=2;
        //获取用户openid 并获得用户person_id
        $pid = $param['openid'];
        // $personList = Db::name('wx_person')->where("app_openid='".$openid."'")->find();
        if(!empty($param['videoid'])){
            if($pid){//url地址存在videoid用户登陆了并且，判断用户是否购买过本节视频与是否到期
                $wherelog['person_id']=$pid;
                if($productStatus==1){
                    $wherelog['product_id']=$param['id'];
                }else{
                    $wherelog['video_class_id']=$param['id'];
                }
                $wherelog['expireTime']=array('egt',time());
                $res=Db::name('video_log')->where($wherelog)->where(['type' => 0])->find();
                if($res){//用户购买过本课程直接查看本节课内容
                    $whereCourse['id']=$param['videoid'];
                }else{
                    if(in_array($pid,config('user_vip'))){
                        $whereCourse['id']=$param['videoid'];
                    }else{
                        //用户没有激活 判断是否可以试听
                        $videoTestWhere['id'] = $param['videoid'];
                        $videoTestWhere['audi'] = array('in','2,3');
                        $videoTestList = Db::name('video')->where($videoTestWhere)->where(['display' => 1])->find();
                        $audiNum = Db::name('audition_log')->where(['person_id'=>$pid])->group('video_id')->count();
                        if($productStatus==1){
                            $audiInfo = Db::name('audition_log')->where(['person_id'=>$pid,'video_id'=>$param['videoid'],'product_id'=>$param['id']])->find();
                        }else{
                            $audiInfo = Db::name('audition_log')->where(['person_id'=>$pid,'video_id'=>$param['videoid'],'video_class_id'=>$param['id']])->find();
                        }

                        //用户没有购买本视频或者过期的判断本节课是否可以试听
                        // if(!empty($videoTestList)){
                            //判断新老用户 老用户可以免费试听 新用户购买试听
                            //根据新功能 上线时间判断新老用户
                            $time = config('new_functions_online_time');
                            //获取用户信息
                            $personInfo = Db::name('person')->where(['id'=>$pid])->find();
                            // if($personInfo['addtime'] < $time) {
                                if(empty($audiInfo) && $pid > 27){  //可以试听
                                    if($audiNum < 5 || $videoTestList['audi'] ==3){
                                        $whereCourse['id']=$param['videoid'];
                                    }else{
                                        return jsonMsg('仅限试听五个视频哦！',0);
                                    }
                                }else{
                                    $whereCourse['id']=$param['videoid'];
                                }
                            // }else{
                            //     //判断新用户是否购买试听课程
                            //     $orderTestInfo = Db::name('order_person_son')->where(['person_id'=>$pid,'is_audition'=>1,'orderCheck'=>2])->order('endtime desc')->find();
                            //     if(empty($orderTestInfo)){
                            //         return jsonMsg('你不是试听用户，如需观看，请先购买！',0);
                            //     }else{
                            //         //判断试听 期限210天
                            //         $timeExpire = strtotime($orderTestInfo['endtime']) + 3600 * 24 * 210;
                            //         if($timeExpire > time()){
                            //             $whereCourse['id']=$param['videoid'];
                            //         }else{
                            //             //跳到试听课支付页面
                            //             return jsonMsg('您的试听时间已到期，如要试听，请重新购买',0);
                            //         }
                            //     }
                            // }
                        // }else{
                        //     return jsonMsg('本课程不可观看请先购买！',0);  //不可以试听
                        // }
                    }
                }
            }
            // else{
            //     //用户没有登录 判断是否可以试听
            //     $videoTestWhere['id'] = $param['videoid'];
            //     $videoTestWhere['audi'] = 1;
            //     $videoTestList = Db::name('video')->where($videoTestWhere)->select();
            //     if(!empty($videoTestList)){
            //         //可以试听跳到视频播放
            //         $whereCourse['id']=$param['videoid'];
            //     }
            // }
        }else{//url地址不存在video的情况下
            if(!empty($pid)){//用户登陆了
                //url地址videoid为空用户登陆了判断本视频用户有没有观看记录
                $wherelog['expireTime']=array('eq',time());
                if($productStatus==1){
                    $whereUser="person_id=".$pid." and product_id=".$param['id']." and study_time !=0";
                    $wherelog['product_id']=$param['id'];
                }else{
                    $whereUser="person_id=".$pid." and video_class_id=".$param['id']." and study_time !=0";
                    $wherelog['video_class_id']=$param['id'];
                }
                $req=Db::name('video_log')->field('video_id,study_time,video_time')->where(['type' => 0])->where($whereUser)->order('id desc')->find();
                //判断该用户是否激活视频
                $wherelog['person_id']=$pid;
                $wherelog['expireTime']=array('egt',time());
                $result = Db::name('video_log')->where(['type' => 0])->where($wherelog)->find();
                if($result){
                    if(!empty($req)){//用户购买过本视频并且有学习的记录有观看学习的记录比较用户上次学习记录
                        $totlTime=explode(':',$req['video_time']);
                        if(strstr($req['study_time'],':')){
                            $nowTime=explode(':',$req['study_time']);
                            $a=$totlTime[0]*60+$totlTime[1]-($nowTime[0]*60+$nowTime[1]);
                        }else{
                            $a=$totlTime[0]*60+$totlTime[1]-$req['study_time'];
                        }   
                        if($a>10){//用户本节课程还没有学习完
                            $whereCourse['id']=$req['video_id'];
                            if(strstr($req['study_time'],':')){
                                $starttime=$nowTime[0]*60+$nowTime[1];
                            }else{
                                $starttime=$req['study_time'];
                            }
                        }else{//用户本节课程已经学完了
                            if($productStatus==1){
                                $whereUser="person_id=".$pid." and product_id=".$param['id']." and study_time =0";
                            }else{
                                $whereUser="person_id=".$pid." and video_class_id=".$param['id']." and study_time =0";
                            }

                            $rtq=Db::name('video_log')->where(['type' => 0])->field('video_id,study_time,video_time')->where($whereUser)->order('id asc')->find();
                            $whereCourse['id']=$rtq['video_id'];
                            $starttime=0;
                        } 
                    }else{//用户还未开始学习
                        if($productStatus==1){
                            $whereUser="person_id=".$pid." and product_id=".$param['id']." and study_time =0";
                        }else{
                            $whereUser="person_id=".$pid." and video_class_id=".$param['id']." and study_time =0";
                        }
                        $rtq=Db::name('video_log')->where(['type' => 0])->field('video_id,study_time,video_time')->where($whereUser)->order('id asc')->find();
                        if($rtq['video_id']){
                            $whereCourse['id']=$rtq['video_id'];
                        }
                        $starttime=0;
                    }
                }
                else{
                    //用户没有激活 获取课程的可是听视频 判断是否有试听的权限
                    // return $this->wxPayCourse($param['id']);
                    // return jsonMsg('fail',2,$param['id']);
                    if(in_array($pid,config('user_vip'))){

                        if($productStatus==1){
                            $videoInfo = Db::name('product_info')->where(['product_id'=>$param['id']])->find();
                            $whereCourse['id']=$videoInfo['video_id'];
                        }else{
                            $videoInfo = Db::name('video')->where(['kid'=>$param['id'],'part'=>2])->find();
                            $whereCourse['id']=$videoInfo['id'];
                        }
                    }else{
                        if($productStatus==1){
                            $pro_Info = Db::name('product_info')->field('video_id')->where(['product_id'=>$param['id']])->select();
                            $pro_videos = array_column($pro_Info,'video_id');
                            $audiWhere['audi']  =array('in','2,3');
                            $videoAudiInfo = Db::name('video')->where($audiWhere)->whereIn('id',$pro_videos)->find();
                        }else{
                            $audiWhere['audi']  =array('in','2,3');
                            $audiWhere['kid'] = $param['id'];
                            $videoAudiInfo = Db::name('video')->where($audiWhere)->find();
                        }

                       // if(!empty($videoAudiInfo)){
                            //可以试听跳到视频播放 判断试听次数 没人只能试听五次
                            // $audiNum = Db::name('audition_log')->where(['person_id'=>$pid])->group('video_id')->count();
                            // $audiInfo = Db::name('audition_log')->where(['person_id'=>$pid,'video_class_id'=>$param['id']])->find();
                            // if(empty($audiInfo) && $pid > 27){
                            //     if($audiNum >= 5){
                            //         return jsonMsg('仅限试听五个视频哦！',0);
                            //     }else{
                            //         $whereCourse['id']=$videoAudiInfo['id'];
                            //     }
                            // }else{
                            //     $whereCourse['id']=$videoAudiInfo['id'];
                            // }
                            //判断新老用户 老用户可以免费试听 新用户购买试听
                            //根据新功能 上线时间判断新老用户
                            $time = config('new_functions_online_time');
                            //获取用户信息
                            $personInfo = Db::name('person')->where(['id'=>$pid])->find();
                            // if($personInfo['addtime'] < $time) {
                                //可以试听跳到视频播放 判断试听次数 没人只能试听五次
                                $audiNum = Db::name('audition_log')->where(['person_id'=>$pid])->group('video_id')->count();
                                if($productStatus==1){
                                    $audiInfo = Db::name('audition_log')->where(['person_id'=>$pid,'product_id'=>$param['id']])->find();
                                }else{
                                    $audiInfo = Db::name('audition_log')->where(['person_id'=>$pid,'video_class_id'=>$param['id']])->find();
                                }
                                if(empty($audiInfo) && $pid > 27){  //可以试听
                                    if($audiNum < 5 || $videoAudiInfo['audi'] == 3){
                                        $whereCourse['id']=$videoAudiInfo['id'];
                                    }else{
                                        return jsonMsg('仅限试听五个视频哦！',0);
                                    }
                                }else{
                                    $whereCourse['id']=$videoAudiInfo['id'];
                                }
                            // }else{
                            //     //判断新用户是否购买试听课程
                            //     $orderTestInfo = Db::name('order_person_son')->where(['person_id'=>$pid,'is_audition'=>1,'orderCheck'=>2])->order('endtime desc')->find();
                            //     if(empty($orderTestInfo)){
                            //         return jsonMsg('你不是试听用户，如需观看，请先购买！',0);
                            //     }else{
                            //         //判断试听 期限210天
                            //         $timeExpire = strtotime($orderTestInfo['endtime']) + 3600 * 24 * 210;
                            //         if($timeExpire > time()){
                            //             $whereCourse['id']=$videoAudiInfo['id'];
                            //         }else{
                            //             //跳到试听课支付页面
                            //             return jsonMsg('您的试听时间已到期，如要试听，请重新购买',0);
                            //         }
                            //     }
                            // }
                        // }else{
                        //     return jsonMsg('本课程暂无试听，如需观看，请先购买',0);
                        // }
                    }
                }
            }
            // else{
            //     //用户没有登录 跳到课程支付页面
            //     // return $this->wxPayCourse($param['id']);
            //         return jsonMsg('fail',2,$param['id']);
            // }
        }
        //url地址videoid不存在，并且没有用户登陆
        $courseCatalogList=$this->videolist($param['id'],$pid,$productStatus); //章节目录
        //获取视频信息
        $courseInfo=Db::name('video')->where($whereCourse)->where(['display' => 1])->order('id asc')->find();
        //获取笔记信息
        if(!empty($whereCourse['id'])){
            $getNoteList = $this->getNoteList($param['id'],$whereCourse['id'],$pid,$productStatus);
        }
        $whereKnow['s_id']=$courseInfo['id'];
        $videoId=!empty($param['videoid'])?$param['videoid']:$courseInfo['id'];
        $start_time=!empty($param['startTime'])?$param['startTime']:0;
        //查找本节课程知识点
        $knowLedge=Db::name('knowledge')->where($whereKnow)->order('k_id asc')->select();
        foreach ($knowLedge as $key => $value) {
            //判断时间格式
            if(strstr($value['start_time'],':')){
                //转换时间格式
                $knowLedge[$key]['start_time'] = $this->changeStartTime($value['start_time']);
            }elseif(strstr($value['start_time'],'：')){

                $start_time = str_replace('：',':',$value['start_time']);
                $knowLedge[$key]['start_time'] = $this->changeStartTime($start_time);
            }
        }
        $data = array();
        foreach($courseCatalogList as $k=>$v)
        {
            if(is_array($v))
            {
                foreach($v as $key=>$val)
                {
                    if(!isset($val['son']))
                    {
                        $courseCatalogList[$k][$key]['son']=1;
                        break;
                    }
                }
            }
        }

        $data['courseCatalogList'] = $courseCatalogList;
        $data['courseInfo'] = $courseInfo;
        $data['start_time'] = $start_time;
        if(!empty($getNoteList)){
            $data['noteList'] = $getNoteList;
        }
        $data['videoid'] = $videoId;
        $data['knowLedge'] = $knowLedge;
        $data['getCrumbs'] = $getCrumbs;
        $data['teacherInfo'] = $teacherInfo;
        jsonMsg('success',1,$data);
    }
    //转换时间格式  将00:02:07转换成秒
    public function changeStartTime($start_time){
        $study_time =explode(':',$start_time);
        $studyTime = 0;
        if(count($study_time) == 2){
            $studyTime =$studyTime + $study_time['0'] * 60 + $study_time['1'];
        }elseif(count($study_time) == 3){
            $studyTime =$studyTime + $study_time['0'] * 3600 + $study_time['1'] * 60 + $study_time['2'];
        }elseif(count($study_time) == 1){
            $studyTime =$studyTime + $study_time['0'];
        }
        return $studyTime;
    }
    //获取本节课历史笔记资料
    public function  getNoteList($id,$video_id,$pid,$productStatus=0)
    {
        $param=input();
        $where['n.video_id']=$video_id;
        if($productStatus==1){
            $where['n.product_id']=$id;
        }else{
            $where['n.video_class_id']=$id;
        }
        $where['n.uid']=$pid;
        $where['n.status']=2;
        $res=Db::name('person_collect')
                     ->alias('n')
                     ->field('p.nickname,p.litpic,from_unixtime(n.intime) intime,n.noteText')
                     ->join('guard_person p','p.id=n.uid')
                     ->where($where)
                     ->order('n.intime desc')
                     ->find();
        return $res;
    }
    //获取面包屑
    public function getCrumbs($where)
    {
        $courseModel=new courseModel();
        $getCrumbs=$courseModel->getCrumbs($where);
        if($getCrumbs['Semester']==1){
            $getCrumbs['Semester']="上";
        }else{
            $getCrumbs['Semester']="下";
        }
        if($getCrumbs){
            return $getCrumbs;
        }
    }
    public function videolist($kid,$pid,$productStatus=0){
        // $pid = Db::name('wx_person') ->field('pid') ->where(['app_openid' => $openid]) ->find()['pid'];
        // 是否是课程播放页面传参
        if($kid){

            $where = ['id' => $kid];
        }
        // 组装数据
        $data = (new WxM()) ->videolist($where, $pid,$productStatus);

        $treelist = $data[0];

        if(!$treelist){
            jsonMsg('error', 1001);
        }
        
        $videolist = getTree($treelist);
        foreach($videolist as $key => &$val){

            $videolist[$key]['shows'] = $key == 0 ? true : false;
        }

        return [$videolist,$data[1],$data[2]];
    }

    private  function tree($data,$pid=0){
        $tree = [];
        foreach($data as $k => $v)
        {
            if($v['pid'] == $pid)
            {    
                $v['son'] = $this ->tree($data, $v['id']);
                if(empty($v['son'])){

                    unset($v['son']);
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }

    //小程序 点击知识点  （个人觉得 这个接口没必要 （薛少鹏））
    // public function clickKnowledge(){
    //     $param = input();
    //     $openid = $param['openid'];
    //     $videoid = $param['videoid'];
    //     $k_id = $param['k_id'];
    //     //获取用户id
    //     $personList = Db::name('wx_person')->where("app_openid='".$openid."'")->find();
    //     $knowledgeList = Db::name('knowledge')->where('k_id',$k_id)->find();
    //     if($personList){
    //         //用户登陆判断用户是否购买过本课程是否过期了
    //         $wherelog['person_id']=$personList['pid'];
    //         $wherelog['video_id']=$videoid;
    //         $wherelog['expireTime']=array('egt',time());
    //         $rts=Db::name('video_log')->where($wherelog)->find();
    //         if($rts){
    //             //更新播放记录
    //             //根据知识点获取当前知识点的时间
    //             if($rts['video_status'] !=3){
    //                 if($knowledgeList['start_time']==$rts['video_time']){
    //                     $data['video_status']=3;
    //                     $data['study_num']=$rts['study_num']+1;
    //                     $data['study_time']=$knowledgeList['start_time'];
    //                     $data['uptime']=time();
    //                     Db::name('video_log')->where($wherelog)->update($data);
    //                 }else{
    //                     $data['video_status']=2;
    //                     $data['study_time']=$knowledgeList['start_time'];
    //                     $data['uptime']=time();
    //                     Db::name('video_log')->where($wherelog)->update($data);
    //                 }  
    //             } 
    //             return jsonMsg('success',1,$knowledgeList['start_time']);
    //         }else{
    //             //用户没有购买本视频或者过期的判断本节课是否可以试听
    //             $whereVideo['id']=$videoid;
    //             $res=Db::name('video')->where($whereVideo)->find();
    //             if($res['audi']!=1){
    //                 jsonMsg('本课程不可观看请先购买！',0);
    //             }else{
    //                 return jsonMsg('success',1,$knowledgeList['start_time']);
    //             }
    //         }
    //     }else{
    //         //用户没有登陆判断本视频是否可以试听
    //         $whereVideo['id']=$videoid;
    //         $res=Db::name('video')->where($whereVideo)->find();
    //         if($res['audi']!=1)
    //         {
    //             jsonMsg('本课程不可观看请先购买！',0);
    //         }else{
    //             return jsonMsg('success',1,$knowledgeList['start_time']);
    //         }
    //     }
    // }
    //小程序 点击课程列表目录
    public function tabVideoClass(){
        $param = input();
        $videoid=$param['videoid'];
        $lastVideoid=$param['lastVideoid'];
        $currentTime=(int)$param['currentTime']; //上次视频的播放时间
        $study_time = $this->ChangePlayTime($param['study_time']);
        $currentTime = $this->ChangePlayTime($currentTime);
        $pid=$param['openid'];
        $whereVideo['id']=$videoid;
        $result=Db::name('video')->where($whereVideo)->where(['display' => 1])->find();
        //获取用户id
        // $personList = Db::name('wx_person')->where("app_openid='".$openid."'")->find();
        if(!empty($pid)){
            //更新观看记录
            $wherelog['person_id']=$pid;
            $wherelog['video_id']=$lastVideoid;
            if($param['productStatus']==1){
                $wherelog['product_id']=$param['kid'];
            }else{
                $wherelog['video_class_id']=$param['kid'];
            }
            $rts=Db::name('video_log')->where($wherelog)->where(['type' => 0])->find();
            if($rts){
                if($currentTime != '00:00'){
                    if($rts['video_status'] !=3){
                        if($currentTime == $rts['video_time']){
                            $data['video_status']=3;
                            $data['study_num']=$rts['study_num']+1;
                            $data['study_time']=$currentTime;
                            $data['uptime']=time();
                            Db::name('video_log')->where($wherelog)->where(['type' => 0])->update($data);
                        }elseif(!empty($currentTime)){
                            $data['video_status']=2;
                            $data['study_time']=$currentTime;
                            $data['uptime']=time();
                            Db::name('video_log')->where($wherelog)->where(['type' => 0])->update($data);
                        }
                    }else{
                        $data['study_num']=$rts['study_num']+1;
                        $data['study_time']=$currentTime;
                        $data['uptime']=time();
                        Db::name('video_log')->where($wherelog)->where(['type' => 0])->update($data);
                    }
                    $this->checkVideoStudy($param);
                }
            }else{
                if($pid != 0){
                    if(!in_array($pid,config('user_vip'))){
                        if($param['lastVideoid'] != $param['videoid']){
                            if($study_time != '00:00' && $currentTime != '00:00'){
                                if($param['productStatus']==1){
                                    $lastAudiInfo = Db::name('audition_log')->where(['person_id'=>$pid,'video_id'=>$param['lastVideoid'],'product_id'=>$param['kid']])->order('id desc')->find();
                                }else{
                                    $lastAudiInfo = Db::name('audition_log')->where(['person_id'=>$pid,'video_id'=>$param['lastVideoid'],'video_class_id'=>$param['kid']])->order('id desc')->find();
                                }

                                //获取视频信息
                                $lastVideoList = Db::name('video')->where('id',$param['lastVideoid']) ->where(['display' => 1])->find();
                                if($lastVideoList['audi'] !=3){
                                    if(empty($lastAudiInfo)){
                                        if($param['productStatus']==1){
                                            $audiDate = [
                                                'person_id' => $pid,
                                                'product_id' => $param['kid'],
                                                'video_id' => $param['lastVideoid'],
                                                'video_time' => $lastVideoList['classhour'],
                                                'create_time' => time(),
                                                'study_time' =>$study_time,
                                            ];
                                        }else{
                                            $audiDate = [
                                                'person_id' => $pid,
                                                'video_class_id' => $param['kid'],
                                                'video_id' => $param['lastVideoid'],
                                                'video_time' => $lastVideoList['classhour'],
                                                'create_time' => time(),
                                                'study_time' =>$study_time,
                                            ];
                                        }

                                        Db::name('audition_log')->insert($audiDate);
                                    }else{
                                        //修改
                                        // $audiDate = [
                                        //     'update_time' => time(),
                                        //     'study_time' =>$param['currentTime'],
                                        // ];
                                        // Db::name('audition_log')->where(['id' => $lastAudiInfo['id']])->update($audiDate);
                                        if($lastAudiInfo['study_time'] != $study_time || $lastAudiInfo['video_id'] != $param['lastVideoid']){
                                            if($param['productStatus']==1){
                                                $audiDate = [
                                                    'person_id' => $pid,
                                                    'product_id' => $param['kid'],
                                                    'video_id' => $param['lastVideoid'],
                                                    'video_time' => $lastVideoList['classhour'],
                                                    'create_time' => time(),
                                                    'study_time' =>$study_time,
                                                    'num'=>$lastAudiInfo['num']+1,
                                                ];
                                            }else{
                                                $audiDate = [
                                                    'person_id' => $pid,
                                                    'video_class_id' => $param['kid'],
                                                    'video_id' => $param['lastVideoid'],
                                                    'video_time' => $lastVideoList['classhour'],
                                                    'create_time' => time(),
                                                    'study_time' =>$study_time,
                                                    'num'=>$lastAudiInfo['num']+1,
                                                ];
                                            }

                                            Db::name('audition_log')->insert($audiDate);
                                        }
                                    }
                                    //获取用户状态
                                    $personInfo = Db::name('person')->where('id',$pid)->find();
                                    if($personInfo['status'] == 1){
                                        $data['status'] = 2;
                                        $data['up_time'] = time();
                                        Db::name('person')->where('id',$pid)->update($data);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            // 更新学习记录表
            $params = [
                'videoid' => $param['lastVideoid'],
                'openid' => $param['openid'],
                'currentTime' => $param['currentTime']
            ];

            //判断用户是否购买此视频
            $where['person_id'] = $pid;
            $where['video_id']=$videoid;
            $where['expireTime']=array('egt',time());
            if($param['productStatus']==1){
                $where['product_id']=$param['kid'];
            }else{
                $where['video_class_id']=$param['kid'];
            }
            $res=Db::name('video_log')->where($where)->where(['type' => 0])->find();
            //获取本视频的知识点
            $knowledgeList = Db::name('knowledge')->where('s_id',$result['id'])->select();
            $getNoteList = $this->getNoteList($result['kid'],$param['videoid'],$pid,$param['productStatus']);
            foreach ($knowledgeList as $key => $value) {
                //判断时间格式
                if(strstr($value['start_time'],':')){
                    //转换时间格式
                    $knowledgeList[$key]['start_time'] = $this->changeStartTime($value['start_time']);
                }elseif(strstr($value['start_time'],'：')){

                    $start_time = str_replace('：',':',$value['start_time']);
                    $knowledgeList[$key]['start_time'] = $this->changeStartTime($start_time);
                }
            }
            $knowledgeList['knowledgeList'] = $knowledgeList;
            $knowledgeList['getNoteList'] = $getNoteList;
            $result['start_time'] = 0;
            if(in_array($pid,config('user_vip'))){
                return jsonMsg('success',1,$result,$knowledgeList);
            }
            if($res){
                //用户有权限 可以观看
                return jsonMsg('success',1,$result,$knowledgeList);
            }else{
                //if($result['audi']==2){

                    // if(empty($audiInfo) && $pid > 27){
                    //     if($audiNum < 5){
                    //         //免费试听课
                    //         return jsonMsg("每个用户只能试听五个视频，您已观看".$audiNum.'个',2,$result,$knowledgeList);
                    //     }else{
                    //         jsonMsg('每个用户只能试听五个视频',0);
                    //     }
                    // }else{
                    //     //免费试听课 已观看 再次试听
                    //     return jsonMsg('每个用户只能试听五个视频，您已观看'.$audiNum.'个',2,$result,$knowledgeList);
                    // }
                    //判断新老用户 老用户可以免费试听 新用户购买试听
                    //根据新功能 上线时间判断新老用户
                    $time = config('new_functions_online_time');
                    //获取用户信息
                    $personInfo = Db::name('person')->where(['id'=>$pid])->find();
                    // if($personInfo['addtime'] < $time) {
                        //用户没有购买本视频或者过期的判断本节课是否可以试听
                        $audiNum = Db::name('audition_log')->where(['person_id'=>$pid])->group('video_id')->count();
                if($param['productStatus']==1){
                    $audiInfo = Db::name('audition_log')->where(['person_id'=>$pid,'video_id'=>$videoid,'product_id'=>$param['kid']])->order('id desc')->find();
                }else {
                    $audiInfo = Db::name('audition_log')->where(['person_id'=>$pid,'video_id'=>$videoid,'video_class_id'=>$param['kid']])->find();
                }
                        if(empty($audiInfo) && $pid > 27){  //可以试听
                            if($audiNum < 5 || $result['audi']==3){
                                 return jsonMsg("每个用户只能试听五个视频，您已观看".$audiNum.'个',2,$result,$knowledgeList);
                            }else{
                                jsonMsg('每个用户只能试听五个视频',0);
                            }
                        }else{
                            //免费试听课 已观看 再次试听
                            return jsonMsg('每个用户只能试听五个视频，您已观看'.$audiNum.'个',2,$result,$knowledgeList);
                        }
                    // }else{
                    //     //判断新用户是否购买试听课程
                    //     $orderTestInfo = Db::name('order_person_son')->where(['person_id'=>$pid,'is_audition'=>1,'orderCheck'=>2])->order('endtime desc')->find();
                    //     if(empty($orderTestInfo)){
                    //         return jsonMsg('你不是试听用户，如需观看，请先购买！',0);
                    //     }else{
                    //         //判断试听 期限210天
                    //         $timeExpire = strtotime($orderTestInfo['endtime']) + 3600 * 24 * 210;
                    //         if($timeExpire > time()){
                    //             return jsonMsg("您已购买试听视频，点击确定继续学习！",2,$result,$knowledgeList);
                    //         }else{
                    //             //跳到试听课支付页面
                    //             return jsonMsg('您的试听时间已到期，如要试听，请重新购买',0);
                    //         }
                    //     }
                    // }
                // }else{
                //     jsonMsg('本课程不可观看请先购买！',0);
                // }
            }
        }
        // else{
        //     $knowledgeList = Db::name('knowledge')->where('s_id',$result['id'])->select();
        //     //用户没有登陆判断本视频是否可以试听
        //     $whereVideo['id']=$videoid;
        //     $result=Db::name('video')->where($whereVideo)->find();
        //     if($result['audi']!=1){
        //         jsonMsg('本课程不可观看请先购买！',0);
        //     }else{
        //         $result['start_time'] = 0;
        //         return jsonMsg('success',1,$result,$knowledgeList);
        //     }
        // }
    }
    //小程序 停止播放更新观看记录
    public function videoPause()
    {
        $param = input();
        $videoid = $param['videoid'];
        $currentTime = (int)$param['currentTime'];
        $currentTime = $this->ChangePlayTime($currentTime);
        $study_time = $this->ChangePlayTime($param['study_time']);
        $pid = $param['openid'];
        $id = $param['id']??0;
        $productStatus = $param['productStatus']??0;
        //获取用户id
        // $personList = Db::name('wx_person')->where("app_openid='".$openid."'")->find();
        if(!empty($pid)){
            //更新观看记录
            $wherelog['person_id']=$pid;
            $wherelog['video_id']=$videoid;
            if($productStatus==1){
                $wherelog['product_id']=$id;
            }else{
                $wherelog['video_class_id']=$id;
            }
            $rts=Db::name('video_log')->where($wherelog)->where(['type' => 0])->find();
            if($rts){
                if($currentTime != '00:00'){
                    if($rts['video_status'] !=3){
                        if($currentTime == $rts['video_time']){
                            $data['video_status']=3;
                            $data['study_num']=$rts['study_num']+1;
                            $data['study_time']=$currentTime;
                            //数据转换
                            $data['uptime']=time();
                            Db::name('video_log')->where($wherelog)->where(['type' => 0])->update($data);
                        }elseif(!empty($currentTime)){
                            $data['video_status']=2;
                            $data['study_time']=$currentTime;
                            $data['uptime']=time();
                            Db::name('video_log')->where($wherelog)->where(['type' => 0])->update($data);
                        }
                        return jsonMsg('success',1);
                    }else{
                        $data['study_num']=$rts['study_num']+1;
                        $data['study_time']=$currentTime;
                        $data['uptime']=time();
                        Db::name('video_log')->where($wherelog)->where(['type' => 0])->update($data);
                        return jsonMsg('success',1);
                    }
                }
            }else{
                if($pid != 0){
                    if(!in_array($pid,config('user_vip'))){
                        if($productStatus==1){
                            $lastAudiInfo = Db::name('audition_log')->where(['person_id'=>$pid,'video_id'=>$param['videoid'],'product_id'=>$id])->order('id desc')->find();
                        }else{
                            $lastAudiInfo = Db::name('audition_log')->where(['person_id'=>$pid,'video_id'=>$param['videoid'],'video_class_id'=>$id])->order('id desc')->find();
                        }
                            //获取视频信息
                        $lastVideoList = Db::name('video')->where('id',$param['videoid']) ->where(['display' => 1])->find();
                        if($lastVideoList['audi'] !=3){
                            if($study_time != '00:00' && $currentTime != '00:00'){
                                if(empty($lastAudiInfo)){
                                    if($productStatus==1){
                                        $audiDate = [
                                            'person_id' => $pid,
                                            'product_id' => $id,
                                            'video_id' => $param['videoid'],
                                            'video_time' => $lastVideoList['classhour'],
                                            'create_time' => time(),
                                            'study_time' =>$study_time,
                                        ];
                                    }else{
                                        $audiDate = [
                                            'person_id' => $pid,
                                            'video_class_id' => $id,
                                            'video_id' => $param['videoid'],
                                            'video_time' => $lastVideoList['classhour'],
                                            'create_time' => time(),
                                            'study_time' =>$study_time,
                                        ];
                                    }
                                    Db::name('audition_log')->insert($audiDate);
                                }else{
                                    if($lastAudiInfo['study_time'] != $study_time || $lastAudiInfo['video_id'] != $param['videoid']){
                                        // $audiDate = [
                                        //     'update_time' => time(),
                                        //     'study_time' =>$param['currentTime'],
                                        // ];
                                        // Db::name('audition_log')->where(['id' => $lastAudiInfo['id']])->update($audiDate);
                                        if($productStatus==1){
                                            $audiDate = [
                                                'person_id' => $pid,
                                                'product_id' => $id,
                                                'video_id' => $param['videoid'],
                                                'video_time' => $lastVideoList['classhour'],
                                                'create_time' => time(),
                                                'study_time' =>$study_time,
                                                'num'=>$lastAudiInfo['num']+1,
                                            ];
                                        }else{
                                            $audiDate = [
                                                'person_id' => $pid,
                                                'video_class_id' => $id,
                                                'video_id' => $param['videoid'],
                                                'video_time' => $lastVideoList['classhour'],
                                                'create_time' => time(),
                                                'study_time' =>$study_time,
                                                'num'=>$lastAudiInfo['num']+1,
                                            ];
                                        }

                                        Db::name('audition_log')->insert($audiDate);
                                    }
                                }
                                //获取用户状态
                                $personInfo = Db::name('person')->where('id',$pid)->find();
                                if($personInfo['status'] == 1){
                                    $data['status'] = 2;
                                    $data['up_time'] = time();
                                    Db::name('person')->where('id',$pid)->update($data);
                                }
                            }
                        }
                    }
                }
            }
            // 添加学习记录
            $this->checkVideoStudy($param);
        }
    }

    //小程序 视频播放结束 更新观看记录
    public function videoEnd()
    {
        $param = input();
        $videoid = $param['videoid'];
        $currentTime = (int)$param['currentTime'];
        $currentTime = $this->ChangePlayTime($currentTime);
        $study_time = $this->ChangePlayTime($param['study_time']);
        $pid = $param['openid'];
        $id = $param['id']??0;
        $productStatus = $param['productStatus']??0;
        //获取用户id
        // $personList = Db::name('wx_person')->where("app_openid='".$openid."'")->find();
        if(!empty($pid)){
            //更新观看记录
            $wherelog['person_id']=$pid;
            $wherelog['video_id']=$videoid;
            if($productStatus==1){
                $wherelog['product_id']=$id;
            }else{
                $wherelog['video_class_id']=$id;
            }
            $rts=Db::name('video_log')->where($wherelog)->where(['type' => 0])->find();
            if($rts){
                $data['video_status']=3;
                $data['study_num']=$rts['study_num']+1;
                $data['study_time']=$currentTime;
                $data['uptime']=time();
                Db::name('video_log')->where($wherelog)->where(['type' => 0])->update($data);
                return jsonMsg('success',1);
            }else{
                if($pid != 0){
                    if($study_time != '00:00' && $currentTime != '00:00'){
                        if(!in_array($pid,config('user_vip'))){
                            if($productStatus==1){
                                $lastAudiInfo = Db::name('audition_log')->where(['person_id'=>$pid,'video_id'=>$param['videoid'],'product_id'=>$id])->order('id desc')->find();
                            }else{
                                $lastAudiInfo = Db::name('audition_log')->where(['person_id'=>$pid,'video_id'=>$param['videoid'],'video_class_id'=>$id])->order('id desc')->find();
                            }
                            //获取视频信息
                            $lastVideoList = Db::name('video')->where('id',$param['videoid']) ->where(['display' => 1])->find();
                            if($lastVideoList['audi'] !=3){
                                if(empty($lastAudiInfo)){
                                    if($productStatus==1){
                                        $audiDate = [
                                            'person_id' => $pid,
                                            'product_id' => $id,
                                            'video_id' => $param['videoid'],
                                            'video_time' => $lastVideoList['classhour'],
                                            'create_time' => time(),
                                            'study_time' =>$study_time,
                                        ];
                                    }else{
                                        $audiDate = [
                                            'person_id' => $pid,
                                            'video_class_id' => $id,
                                            'video_id' => $param['videoid'],
                                            'video_time' => $lastVideoList['classhour'],
                                            'create_time' => time(),
                                            'study_time' =>$study_time,
                                        ];
                                    }

                                    Db::name('audition_log')->insert($audiDate);
                                }else{
                                    // $audiDate = [
                                    //     'update_time' => time(),
                                    //     'study_time' =>$param['currentTime'],
                                    // ];
                                    // Db::name('audition_log')->where(['id' => $lastAudiInfo['id']])->update($audiDate);
                                    if($lastAudiInfo['study_time'] != $study_time || $lastAudiInfo['video_id'] != $param['videoid']){
                                        if($productStatus==1){
                                            $audiDate = [
                                                'person_id' => $pid,
                                                'product_id' => $id,
                                                'video_id' => $param['videoid'],
                                                'video_time' => $lastVideoList['classhour'],
                                                'create_time' => time(),
                                                'study_time' =>$study_time,
                                                'num'=>$lastAudiInfo['num']+1,
                                            ];
                                        }else{
                                            $audiDate = [
                                                'person_id' => $pid,
                                                'video_class_id' => $id,
                                                'video_id' => $param['videoid'],
                                                'video_time' => $lastVideoList['classhour'],
                                                'create_time' => time(),
                                                'study_time' =>$study_time,
                                                'num'=>$lastAudiInfo['num']+1,
                                            ];
                                        }
                                        Db::name('audition_log')->insert($audiDate);
                                    }
                                }
                                //获取用户状态
                                $personInfo = Db::name('person')->where('id',$pid)->find();
                                if($personInfo['status'] == 1){
                                    $data['status'] = 2;
                                    $data['up_time'] = time();
                                    Db::name('person')->where('id',$pid)->update($data);
                                }
                            }    
                        }
                    }
                }
            }
            $this->checkVideoStudy($param);
        }
    }
    /**
     * 播放完处理学习记录数据
     * 增加学习记录数据 表video_watch_log 
     * @author yangjizhou 2019-06-19
     */
    public function checkVideoStudy($param)
    {
        $person_id = isset($param['openid']) ? $param['openid'] : '';
        $id = $param['id']??0;
        $productStatus = $param['productStatus']??0;
        if (!$person_id) {
            return false;
        }
        $time = date('Y-m-d', time());
        $videoid = $param['videoid'];
        $currentTime = (int)$param['currentTime'];

        // 获取课程id
//        $video_class_id = Db::name('video')->where(['id' => $videoid])->value('kid');

        $courseModel = new courseModel();
        $wherelog['person_id']=$person_id;
        if($productStatus==1){
            $wherelog['product_id']=$id;
        }else{
            $wherelog['video_class_id']=$id;
        }
        $wherelog['video_id']=$param['videoid'];
        $wherelog['time'] = $time;

        $rts=Db::name('video_watch_log')->where($wherelog)->find();

        // 累计学习时间 start
        $videotime = Db::name('video')->where(['id' => $param['videoid']]) ->where(['display' => 1])->value('classhour');

        if (!is_numeric($rts['study_time'])) {
            $studytime  = isset($rts['study_time']) && !empty($rts['study_time']) ? $this->getSecond($rts['study_time']) : 0;
        } else {
            $studytime  = $rts['study_time'] ? $rts['study_time'] : 0;
        }
        $restime    = (int) $studytime + (int)($videotime ? $this->getSecond($videotime) : 0);
        // end
        $data['video_status'] = 3;

        if ($rts) {
            $data['study_num'] = isset($rts['study_num']) ? $rts['study_num'] + 1 : 1;
            $data['study_time'] = $restime;
            $req=Db::name('video_watch_log')->where($wherelog)->update($data);

        } else {
            $courseinfo = $courseModel->course($id);
            $data['study_time'] = $videotime ? $this->getSecond($videotime) : 0;
            $data['grade_id']       = $courseinfo['grade_id'] ? $courseinfo['grade_id'] : '';
            $data['subject_id']       = $courseinfo['subject_id'] ? $courseinfo['subject_id'] : '';
            $data['semester']       = $courseinfo['Semester'] ? $courseinfo['Semester'] : '';
            $data['study_num'] = 1;
            $data['time']           = date('Y-m-d', time());
            $data['person_id']      = $person_id;
            if($productStatus==1){
                $data['product_id'] = $id;
            }else{
                $data['video_class_id'] = $id;
            }
            $data['video_id']       = $param['videoid'];
            Db::name('video_watch_log')->insert($data);
        }
        
    }

    //小程序 视频播放 验证
    public function startPlay()
    {
        $param = input();
        $videoid=$param['videoid'];
        $pid=$param['openid'];
        //获取用户id
        // $personList = Db::name('wx_person')->where("app_openid='".$openid."'")->find();
        if(!empty($pid)){
            //判断用户是否购买此视频
            $where['person_id'] = $pid;
            $where['video_id']=$videoid;
            $where['expireTime']=array('egt',time());
            $res=Db::name('video_log')->where($where)->where(['type' => 0])->find();
            $whereVideo['id']=$videoid;
            $result=Db::name('video')->where($whereVideo)->where(['display' => 1])->find();
            if($res){
                //用户有权限 可以观看
                return jsonMsg('success',1);
            }else{
                //用户没有购买本视频或者过期的判断本节课是否可以试听
                if($result['audi']!=2){
                    jsonMsg('本课程不可观看请先购买！',0);
                }else{
                    jsonMsg('success',1);
                }
            }
        }else{
            //用户没有登陆判断本视频是否可以试听
            $whereVideo['id']=$videoid;
            $result=Db::name('video')->where($whereVideo)->where(['display' => 1])->find();
            if($result['audi']!=2){
                jsonMsg('本课程不可观看请先购买！',0);
            }else{
                jsonMsg('success',1);
            }
        }
    }
    //用户收藏视频
    public function courseCollections()
    {
        $param=input();
        $productStatus = $param['productStatus']??0;
        $pid=$param['openid'];
        //获取用户id
        // $personList = Db::name('wx_person')->where("app_openid='".$openid."'")->find();
        //根据video_id 获取当前图片
        if($productStatus==1){
            $videoList = Db::name('product')->field('subject_id')->where('id='.$param['id'])->find();
            //获取当前视频的图片
            $coursePayList = Db::name('video')
                ->field("img,outline")
                ->where('id',$param['videoid'])
                ->find();
        }else{
            $videoList = Db::name('video_class')->where('id='.$param['id'])->find();
            //获取当前视频的图片
            $coursePayList = Db::name('video_class')->alias('vc')
                ->join('guard_video v','v.kid = vc.id')
                ->join('guard_subject s','s.id = vc.subject_id')
                ->join('guard_grade g','g.id = vc.grade_id')
                ->field("v.img,outline,
                                concat(g.grade, s.subject, 
                                    case vc.Semester
                                        when 1 then '上学期'
                                        when 2 then '下学期'
                                        when 3 then '全册'
                                    end
                                ) as title")
                ->where(['v.display' => 1])
                ->where('v.id',$param['videoid'])
                ->find();
        }


        //收藏截图的名称
        // $newImgName = "collect".time().".jpg";
        // //获取封面截图 返回七牛云图片路径
        // $imgPath = getNewImgPath($newImgName,$param['startTime'],$videoPath['link']);
        $data['collectImg'] = $coursePayList['img'];
        if(!empty($pid)){
            $data['uid']=$pid;
            $data['video_id']=$param['videoid'];
            $data['subject_id']=$videoList['subject_id'];
            $data['startTime']=(int)$param['beginTime'];
            $data['endTime']=(int)$param['currentTime'];
            $data['countTime']=$data['endTime']-$data['startTime'];
            if($productStatus==1){
                $data['product_id']=$param['id'];
            }else{
                $data['video_class_id']=$param['id'];
            }
            $data['intime']=time();
            $data['ctitle']=$param['ctitle'];
            $data['noteText']=$coursePayList['outline'];
            $res=Db::name('person_collect')->insert($data);
            if($res){
                return jsonMsg('收藏成功',1);
            }else{
                return jsonMsg('收藏失败！',0);
            }
        }else{
            return jsonMsg('请先登陆！',0);
        }
    }
    public function wxPayCourse(){
        $id = input()['id'];
        $courseCatalogList=$this->videolist($id,0);//url地址videoid不存在，并且没有用户登陆

        //获取课程信息
        $coursePayList = Db::name('video_class')->alias('vc')
                            ->join('guard_video v','v.kid = vc.id')
                            ->join('guard_teacher t','t.id = vc.teacherId')
                            ->join('guard_subject s','s.id = vc.subject_id')
                            ->join('guard_grade g','g.id = vc.grade_id')
                            ->field("count(v.id) as classNum,t.content,t.name,vc.popularity,vc.img,vc.price,vc.Discount,
                                concat(g.grade, s.subject, 
                                    case vc.Semester
                                        when 1 then '上学期'
                                        when 2 then '下学期'
                                        when 3 then '全册'
                                    end
                                ) as title")
                            ->where(['v.display' => 1])
                            ->where('vc.id',$id)
                            ->find();
        $data['courseCatalogList'] = $courseCatalogList;
        $data['coursePayList'] = $coursePayList;
        // $data = json_encode($data);
        return jsonMsg('success',2,$data);
    }
    //视频播放时间数据转换
    public function ChangePlayTime($allTime){
        $classAlltime = array();
        $classAlltime[0] = str_pad(floor($allTime/3600),2,0,STR_PAD_LEFT);
        $classAlltime[1] = str_pad(floor(($allTime%3600)/60),2,0,STR_PAD_LEFT);
        $classAlltime[2] = str_pad($allTime - $classAlltime[0] * 3600 - $classAlltime[1] * 60,2,0,STR_PAD_LEFT);
        if($classAlltime[0] == '00'){
            unset($classAlltime[0]);
        }
        $currentTime = implode(':',$classAlltime);
        return $currentTime;
    }
    //插入笔记
    public function addNote()
    {
        $param=input();
        $pid = $param['openid'];
        $productStatus = $param['productStatus']??0;
        if(!empty($pid))
        {
            //获取当前数据
            $list=Db::name('person_collect')
                     ->alias('n')
                     ->field('p.nickname,p.litpic,from_unixtime(n.intime) intime,n.noteText,n.id')
                     ->join('guard_person p','p.id=n.uid')
                     ->where(['n.uid'=>$pid,'n.status'=>2,'n.video_id'=>$param['video_id']])
                     ->order('n.intime desc')
                     ->find();
            $data['noteText']=$param['text'];
            $data['intime']=time();
            $data['uid']=$pid;
            $data['video_id']=$param['video_id'];
            if($productStatus==1){
                $data['product_id']=$param['video_class_id'];
            }else{
                $data['video_class_id']=$param['video_class_id'];
            }

            $data['startTime']=$param['startTime'];
            $data['subject_id']=$param['subject_id'];
            $data['status']=2;
            if($list){
                $res=Db::name('person_collect')->where('id',$list['id'])->update($data);
                if($res){
                    jsonMsg('success','1',$data['noteText']);
                }else{
                    jsonMsg('提交失败！','2');
                }
            }else{
                $res=Db::name('person_collect')->insert($data);
                if($res){
                    jsonMsg('success','1',$data['noteText']);
                }else{
                    jsonMsg('提交失败！','2');
                }
            }
        }else{
            jsonMsg('请先登陆！','0');
        }
    }
    //判断视频播放权限
    public function checkVideoPlay(){
        $param = input();
        $pid = $param['openid'];
        $id = $param['id'];
        $productStatus = $param['productStatus'];
        $video_id = isset($param['video_id']) ? $param['video_id'] : '';
        //  登录状态
        $data['is_login'] = $pid ? 1 : 0;
        $audi = Db::name('video') ->field('audi,audi_time') ->where(['id' => $video_id]) ->where(['display' => 1]) ->find();
        //  是否试听
        $data['is_audi'] = $audi['audi'] == 2 ? 2 : 0;
        $is_buy = 0;
        if($pid){
            if($productStatus==1){
                $is_buy = Db::name('video_log') ->field('id')
                    ->where(['person_id' => $pid, 'video_id' => $video_id, 'type' => 0,'product_id'=>$id])
                    ->find();
            }else{
                $is_buy = Db::name('video_log') ->field('id')
                    ->where(['person_id' => $pid, 'video_id' => $video_id, 'type' => 0,'video_class_id'=>$id])
                    ->find();
            }

        }
        //  是否购买
        $data['is_buy'] = $is_buy ? 1 : 0;
        // 试听时长
        $data['audi_time'] = $audi['audi_time'];

        jsonMsg('success',1,$data);

    }
    //获取课程列表
    public function getCourseList()
    {
        $param=input();
        $courseData=$param['courseData'];
        if(is_string($courseData)){
            $courseData = json_decode($courseData,true);
        }
        $whereList = array();
        if(!empty($courseData)){
            if(isset($courseData['term'])){
                if($courseData['term']!=0){
                    if($courseData['grade'] == 9){
                        $whereList['v.Semester'] = array('in',''.(int)$courseData['term'].',3');
                    }else{
                        $whereList['v.Semester']=(int)$courseData['term'];
                    }
                }else{
                    $whereList['v.Semester']=1;
               }  
            }
            if(isset($courseData['grade'])){
                if($courseData['grade']!=0){
                    $whereList['v.grade_id']=(int)$courseData['grade'];
                }else{
                    $whereList['v.grade_id']=7;
               } 
            }     
        }else{
            $whereList='';
        }
        //去掉物理、化学
        // $whereList['v.subject_id'] = array('not in','4,5');
        $courseModel=new courseModel();
        //获取课程列表
        $pagenow=$param['pagenow'];
        if($param['pageNum'] == 6){
            //小程序
            $pageNum=$param['pageNum'];
            $limit = $pageNum*($pagenow-1);
            $CourseList=$courseModel->getCourseList($whereList,$limit,$pageNum);
            $count = $CourseList['count'];
            unset($CourseList['count']);
        }
         //判断用户的登录
        $pid = $param['openid'];
        if(!empty($pid)){
            //获取用户购买的课程
            $classList = Db::name('video_log') ->where('person_id',$pid)
                                               ->where(['type' => 0])
                                               ->order('uptime')
                                               ->select();
        }

        foreach($CourseList as $k=>$v)
        {
            // 是否购买
            $CourseList[$k]['is_buy'] = 0;
            if($v['Semester']==1){
                $CourseList[$k]['Semester']='上学期';
            }elseif($v['Semester']==2){
                $CourseList[$k]['Semester']='下学期';
            }else{
                $CourseList[$k]['Semester']='全册';
            }
            $CourseList[$k]['content'] = strip_tags($v['content']);
            //统计课程的章节数目
            $where['kid']=$v['id'];
            if(!isset($v['productUrl'])) {
                $CourseList[$k]['countClassChapter'] = $courseModel->countClassChapter($where);
            }else{
                $CourseList[$k]['countClassChapter']=$courseModel->countProductChapter($v['id']);

            }
            if(!empty($pid)){
                $CourseList[$k]['img'] = $v['imgNo'];
                foreach ($classList as $key => $value){
                    if(in_array($pid,config('user_vip'))){
                        $CourseList[$k]['img'] = $v['img'];
                        $CourseList[$k]['is_buy'] = 1;
                    }else{
                        if($value['video_class_id'] == $v['id'] && !isset($v['productUrl'])){
                            $CourseList[$k]['img'] = $v['img'];
                            $CourseList[$k]['is_buy'] = 1;
                        }
                        if($value['product_id'] == $v['id'] && isset($v['productUrl'])){
                            $CourseList[$k]['img'] = $v['productUrl'];
                            $CourseList[$k]['is_buy'] = 1;
                        }
                    }
                }
            }
            if(isset($v['productUrl'])){
                $CourseList[$k]['productStatus'] = 1;
            }else{
                $CourseList[$k]['productStatus'] = 0;
            }
        }
        //小程序
        if($CourseList){
            return jsonMsg('获取成功',1,$CourseList,$count);
        }else{
            return jsonMsg('暂时没有符合条件的数据！',0,$CourseList,$count);
        }
    }
    //试听结束更新观看记录
    public function  myFunction()
    {
        $param=input();
        $pid  = $param['openid'];
        $id  = $param['id'];
        $productStatus  = $param['productStatus'];
        //判断用户有没有购买过本课程，免费试听的不存进度
        $whereOrder['person_id']=$pid;
        $whereOrder['video_id']=$param['videoid'];
        if($productStatus==1){
            $whereOrder['product_id']=$id;
        }else{
            $whereOrder['video_class_id']=$id;
        }
        $r=Db::name('video_log')->where($whereOrder)->find();
        $study_time = $this->ChangePlayTime($param['study_time']);
        if(empty($r)){
            if(!in_array($pid,config('user_vip'))){
                if($productStatus==1){
                    $audiInfo = Db::name('audition_log')->where(['person_id'=>$pid,'video_id'=>$param['videoid'],'product_id'=>$id])->order('id desc')->find();
                }else{
                    $audiInfo = Db::name('audition_log')->where(['person_id'=>$pid,'video_id'=>$param['videoid'],'video_class_id'=>$id])->order('id desc')->find();
                }
                //获取视频信息
                $videoList = Db::name('video')->where('id',$param['videoid']) ->where(['display' => 1]) ->find();
                if($videoList['audi'] !=3){
                    if(empty($audiInfo)){
                        if($productStatus==1){
                            $audiDate = [
                                'person_id' => $pid,
                                'product_id' => $id,
                                'video_id' => $param['videoid'],
                                'video_time' => $videoList['classhour'],
                                'create_time' => time(),
                                'study_time' =>$study_time,
                            ];
                        }else{
                            $audiDate = [
                                'person_id' => $pid,
                                'video_class_id' => $id,
                                'video_id' => $param['videoid'],
                                'video_time' => $videoList['classhour'],
                                'create_time' => time(),
                                'study_time' =>$study_time,
                            ];
                        }

                        $res = Db::name('audition_log')->insert($audiDate);
                    }else{
                        // $audiDate = [
                        //     'update_time' => time(),
                        //     'study_time' =>$param['currentTime']?$param['currentTime']:0,
                        //     'num' =>$audiInfo['num']+1,
                        // ];
                        // $res = Db::name('audition_log')->where(['id' => $audiInfo['id']])->update($audiDate);
                        if($productStatus==1){
                            $audiDate = [
                                'person_id' => $pid,
                                'product_id' => $id,
                                'video_id' => $param['videoid'],
                                'video_time' => $videoList['classhour'],
                                'create_time' => time(),
                                'study_time' =>$study_time,
                                'num' =>$videoList['num']+1,
                            ];
                        }else{
                            $audiDate = [
                                'person_id' => $pid,
                                'video_class_id' => $id,
                                'video_id' => $param['videoid'],
                                'video_time' => $videoList['classhour'],
                                'create_time' => time(),
                                'study_time' =>$study_time,
                                'num' =>$videoList['num']+1,
                            ];
                        }

                        $res = Db::name('audition_log')->insert($audiDate);
                    }
                    //获取用户状态
                    $personInfo = Db::name('person')->where('id',$pid)->find();
                    if($personInfo['status'] == 1){
                        $data['status'] = 2;
                        $data['up_time'] = time();
                        Db::name('person')->where('id',$pid)->update($data);
                    }
                }
            }
        }
        if($res){
            return jsonMsg('success',0);
        }
    }
    public function checkAudiVideo(){
        $param = input();
        $productStatus = $param['productStatus']??0;
        $video_id = '';
        if(!isset($param['videoid']) || empty($param['videoid'])){
            if($productStatus==1){
                $product_id = $param['id'];
                //$videoList = Db::name('video')->where(['audi'=>1,'kid'=>$video_class_id])->find();
                $videoList = Db::name('product_info')->field('video_id')->where(['product_id'=>$product_id])->where('forbiden',1)->find();
                $video_id = $videoList['video_id'];
            }else{
                $video_class_id = $param['id'];
                //$videoList = Db::name('video')->where(['audi'=>1,'kid'=>$video_class_id])->find();
                $videoList = Db::name('video')->where(['kid'=>$video_class_id])->where('pid','<>',0)->find();
                //if(!empty($videoList)){
                $video_id = $videoList['id'];
                // }else{
                //     return jsonMsg('该视频不可以试听哦！',0);
                // }
            }
        }
        if(empty($video_id)){
            $video_id = $param['videoid'];
        }
        $pid = $param['openid'];
        if(!empty($video_id)){
            if(!empty($pid)){//url地址存在videoid用户登陆了并且，判断用户是否购买过本节视频与是否到期
                $wherelog['person_id']=$pid;
                $wherelog['video_id']=$video_id;
                if($productStatus==1){
                    $wherelog['product_id']=$param['id'];
                }else{
                    $wherelog['video_class_id']=$param['id'];
                }
                $wherelog['expireTime']=array('egt',time());
                $res=Db::name('video_log')->where($wherelog) ->where(['type' => 0])->find();
                if($res){//用户购买过本课程直接查看本节课内容
                    return jsonMsg('点击确定，跳转视频播放。。',2);
                }else{
                    if(!in_array($pid,config('user_vip'))){
                        //用户没有激活 判断是否可以试听
                        $videoTestWhere['id'] = $video_id;
                        $videoTestWhere['audi'] = array('in','2,3');
                        $videoTestList = Db::name('video')->where($videoTestWhere) ->where(['display' => 1])->find();
                        // if(empty($videoTestList)){   //视频不可以试听
                        //     //跳到课程购买
                        //     return jsonMsg('该视频不可以试听哦！',0);
                        // }else{
                            //可以试听 判断新老用户 老用户可以免费试听 新用户购买试听
                            $time = config('new_functions_online_time');
                            //获取用户信息['audi']
                            $personInfo = Db::name('person')->where(['id'=>$pid])->find();
                            //if($personInfo['addtime'] < $time) {   2019/11/29暂时注释 因购买试听未正式上线上线后放开
                                //可以试听跳到视频播放 判断试听次数 没人只能试听五次
                                $audiNum = Db::name('audition_log')->where(['person_id'=>$pid])->group('video_id')->count();
                                if($productStatus==1){
                                    $audiInfo = Db::name('audition_log')->where(['person_id'=>$pid,'video_id'=>$video_id,'product_id'=>$param['id']])->find();
                                }else{
                                    $audiInfo = Db::name('audition_log')->where(['person_id'=>$pid,'video_id'=>$video_id,'video_class_id'=>$param['id']])->find();
                                }

                                if(empty($audiInfo) && $pid > 27){
                                    if($audiNum >= 5 && $videoTestList['audi'] !=3){
                                        return jsonMsg('只能免费试听五个视频,您已经超出了哦！',0);
                                    }else{
                                        return jsonMsg('每个用户能免费试听五个视频,您已试听'.$audiNum.'个',1);
                                    }
                                }else{
                                     return jsonMsg('每个用户能免费试听五个视频,您已试听'.$audiNum.'个',1);
                                }

                            // }else{
                            //     //判断新用户是否购买试听课程
                            //     $orderTestInfo = Db::name('order_person_son')->where(['person_id'=>$pid,'is_audition'=>1,'orderCheck'=>2])->order('endtime desc')->find();
                            //     if(empty($orderTestInfo)){
                            //         //跳到试听课支付页面
                            //         return jsonMsg('您不是试听用户，点击确认跳转试听购买页面',0);
                            //     }else{
                            //          //判断试听 期限210天
                            //         $timeExpire = strtotime($orderTestInfo['endtime'])+3600 * 24 * 210;
                            //         if($timeExpire > time()){
                            //             return jsonMsg('您已购买试听视频，点击确定继续学习！',1);
                            //         }else{
                            //             //跳到试听课支付页面
                            //             return jsonMsg('您的试听时间已到期，如要试听，请重新购买',0);
                            //         }
                            //     }
                            // }
                       // }
                    }else{
                        return jsonMsg('点击确定，跳转视频播放。。',2);
                    }
                }
            }
        }
    }

    /**
     * 根据日期格式获取时间秒
     * @author yangjizhou 2019-06-12
     */
    public function getSecond($date)
    {
        $second = 0;
        if (!isset($date) || empty($date)) return $second;

        $timeArr = explode(':', $date);
        $count = count($timeArr);
        switch ($count) {
            case '2':
                $second = (int) $timeArr[0] * 60 + (int) $timeArr[1];
                break;
            case '3':
                $second = (int) $timeArr[0] * 60 * 60 + (int) $timeArr[1] * 60 + (int) $timeArr[2];
                break;
            default:
                break;
        }

        return $second;
    }
    // 获取张雪燕老师课程数据
    public function getzhangxueyan()
    {
        $res = Db::name('video')->where('kid',23)->limit(1,5)->select();
        if($res){
            return jsonMsg('获取成功',1,$res,5);
        }else{
            return jsonMsg('暂时没有符合条件的数据！',0,$res,5);
        }
    } 
    //张雪燕老师音频文件详情页面
    public function audio()
    { 
        $param = input();
        $res = Db::name('video')->where('id',$param['id'])->find();
        $res['time'] = date('Y-m-d H:i:s',$res['time']);
        $data['likes'] = $res['likes'] + 1;
        Db::name('video')->where('id',$param['id'])->update($data);
        $res['likes'] = $data['likes'];
        if($res){
            return jsonMsg('获取成功',1,$res,5);
        }else{
            return jsonMsg('暂时没有符合条件的数据！',0,$res,5);
        }
    }
}