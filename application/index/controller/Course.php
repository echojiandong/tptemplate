<?php
namespace app\index\controller;
use app\index\model\PersonModel;
use app\index\model\courseModel;
use app\index\model\IndexModel;
use app\index\controller\Auth;
use think\Controller;
use think\Request;
use think\Session;
use think\Cookie;
use think\Db;
use think\image;
use think\Page;
Vendor('WxPay.JsApiPay');
class Course extends Communal
{
    private $person;                //用户
    public function _initialize(){
        parent::_initialize();
        //头部用户信息
        $this->person = new PersonModel($this ->_info);
        $this ->userinfo =  $this->person->GetPerson();
        $this->assign('personInfo',$this ->userinfo);
    }
    public function videoList()
    {
        $where['link'] = array('neq','null');
        $res=Db::name('video')->field('id,link')->where($where) ->where(['display' => 1]) ->order('id asc')->select();
        $this->assign('list',count($res));
        $this->assign('videoList',$res);
        return $this->fetch('index/course/videoList');
    }
    public function courseUpdaTime()
    {
        $param=input();
        $arrayList=$param['arrayList'];
        foreach($arrayList as $v)
        {
            if(isset($v[1]))
            {
                $data['classhour']=floor($v[1]/60).':'.($v[1]%60);
                $where['id']=$v[0];
                $res=Db::name('video')->where($where) ->where(['display' => 1])->update($data);
            }
        }
    }
	//课程列表页面
    public function course()
    {
        $volumn_arr = [1 => '上', 2 => '下', 3 => '全册'];

        $param=input();
        // $courseModel=new courseModel();
        // //获取年级
        // $getgrade=$courseModel->getgrade();
        // $this->assign('getgrade',$getgrade);
        $gradelist = Db::name('video_class') ->field('grade_id,Semester')
                                             ->where('Semester','<>',3)
                                             ->group('grade_id,Semester')
                                             ->order('sort,Semester')
                                             ->select();

        $grade_arr = Db::name('grade') ->field('id,grade')->select();


        $list = array_combine(array_column($grade_arr, 'id'), array_column($grade_arr, 'grade'));

        $getgrade = array_map(function($v) use ($list, $volumn_arr){
                if($v['Semester'] !=3)
                {
                    $data['name'] = $list[$v['grade_id']].$volumn_arr[$v['Semester']];

                    $data['gid'] = (int)$v['grade_id'];

                    $data['sid'] = $v['Semester'];

                    return $data;
                }

            },$gradelist);
        $this->assign('getgrade',$getgrade);
        // 获取张雪燕老师五个课程数据
        $res = Db::name('video')->where('kid',23)->limit(1,5)->select();
        $this->assign('res',$res);
        return $this->fetch('index/course/course');
    }
    //获取课程列表
    public function getCourseList()
    {
        $param=input();
        $courseData=$param['courseData'];
        if(is_string($courseData)){
            $courseData = json_decode($courseData,true);
        }
        if(!empty($courseData)){
            if(isset($courseData['term'])){
                if($courseData['term']!=0){
                    if($courseData['grade']==9)
                    {
                        $whereList['v.Semester']=array('in',(int)$courseData['term'].',3');
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
        //获取总课程数

         $CountCourse=$courseModel->getCountCourse($whereList);
        //获取课程列表
        $pagenow=$param['pagenow'];
        if($param['pageNum'] == 5){
            //手机端请求
            $pageNum=$param['pageNum'];
            $CourseList=$courseModel->getCourseList($whereList,0,$pageNum*$pagenow);
            $count = $CourseList['count'];
            unset($CourseList['count']);
        }elseif($param['pageNum'] == 6){
            //小程序
            $pageNum=$param['pageNum'];
            $limit = $pageNum*($pagenow-1);
            $CourseList=$courseModel->getCourseList($whereList,$limit,$pageNum);
            $count = $CourseList['count'];
            unset($CourseList['count']);
        }else{
            //pc
            $CourseList=$courseModel->getCourseList($whereList,$pagenow);
        }
         //判断用户的登录
        $user = $this ->_info;
        if(isset($param['openid'])){
            //获取用户id
            $pid = $param['openid'];
            // $personList = Db::name('wx_person')->where("app_openid='".$openid."'")->find();
            $user['id'] = $pid;
        }
        if($user){
            //获取用户购买的课程
            $classList = Db::name('video_log') ->where('person_id',$user['id'])
                                               ->where(['type' => 0,'expireTime'=>array('egt',time())])
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
            $whereLearn['classhour']=array('neq','0:00');
            if(!isset($v['productUrl'])) {
                $CourseList[$k]['countClassChapter'] = $courseModel->countClassChapter($where);
            }else{
                $CourseList[$k]['countClassChapter']=$courseModel->countProductChapter($v['id']);

            }
            if($user)
            {
                if(in_array($user['id'],config('user_vip')))
                {
                    $m = 1;
                }else{
                    $m = 0;
                }
            }else{
                $m = 0;
            }
            $studyTime = 0;

            if(!empty($user)){

                $CourseList[$k]['img'] = $v['imgNo'];
                foreach ($classList as $key => $value){
                    if($value['video_class_id'] == $v['id'] && !isset($v['productUrl'])){
                        $CourseList[$k]['img'] = $v['img'];
                        $CourseList[$k]['is_buy'] = 1;
                        //获取学习进度
                        if($value['video_status'] == 2 || $value['video_status']==3){
                            if($value['video_status'] == 2){
                                if(strstr($value['study_time'],':')){
                                    $study_time =explode(':', $value['study_time']);
                                    if(count($study_time) == 2){
                                        $studyTime =$studyTime + $study_time['0'] * 60 + $study_time['1'];
                                    }elseif(count($study_time) == 3){
                                        $studyTime =$studyTime + $study_time['0'] * 60 * 60 + $study_time['1'] * 60 + $study_time['2'];
                                    }
                                }else{
                                    $studyTime = $studyTime + $value['study_time'];
                                }
                            }elseif($value['video_status']==3){
                                $study_time =explode(':', $value['video_time']);
                                if(count($study_time) == 2){
                                    $studyTime =$studyTime + ($study_time['0'] * 60 + $study_time['1']) * $value['study_num'];
                                }elseif(count($study_time) == 3){
                                    $studyTime =$studyTime + ($study_time['0'] * 60 * 60 + $study_time['1'] * 60 + $study_time['2']) * $value['study_num'];
                                }
                            }
                            $m++;
                            //获取学习中的视频的信息
                            if($value['video_status'] == 2){
                                $CourseList[$k]['video_id'] = $value['video_id'];
                                //转换
                                $haveStudyTime = 0;
                                if(strstr($value['study_time'],':')){
                                    $haveStudy_time =explode(':', $value['study_time']);
                                    if(count($study_time) == 2){
                                        $haveStudyTime =$haveStudyTime + $haveStudy_time['0'] * 60 + $haveStudy_time['1'];
                                    }elseif(count($study_time) == 3){
                                        $haveStudyTime =$haveStudyTime + $haveStudy_time['0'] * 60 * 60 + $haveStudy_time['1'] * 60 + $haveStudy_time['2'];
                                    }
                                    $CourseList[$k]['study_time'] = $haveStudyTime;
                                }else{
                                    $CourseList[$k]['study_time'] = $value['study_time'];
                                }
                            }
                        }
                    }
                    elseif ($value['product_id'] == $v['id'] && isset($v['productUrl'])){
                        $CourseList[$k]['img'] = $v['productUrl'];
                        $CourseList[$k]['is_buy'] = 1;
                        //获取学习进度
                        if($value['video_status'] == 2 || $value['video_status']==3){
                            if($value['video_status'] == 2){
                                if(strstr($value['study_time'],':')){
                                    $study_time =explode(':', $value['study_time']);
                                    if(count($study_time) == 2){
                                        $studyTime =$studyTime + $study_time['0'] * 60 + $study_time['1'];
                                    }elseif(count($study_time) == 3){
                                        $studyTime =$studyTime + $study_time['0'] * 60 * 60 + $study_time['1'] * 60 + $study_time['2'];
                                    }
                                }else{
                                    $studyTime = $studyTime + $value['study_time'];
                                }
                            }elseif($value['video_status']==3){
                                $study_time =explode(':', $value['video_time']);
                                if(count($study_time) == 2){
                                    $studyTime =$studyTime + ($study_time['0'] * 60 + $study_time['1']) * $value['study_num'];
                                }elseif(count($study_time) == 3){
                                    $studyTime =$studyTime + ($study_time['0'] * 60 * 60 + $study_time['1'] * 60 + $study_time['2']) * $value['study_num'];
                                }
                            }
                            $m++;
                            //获取学习中的视频的信息
                            if($value['video_status'] == 2){
                                $CourseList[$k]['video_id'] = $value['video_id'];
                                //转换
                                $haveStudyTime = 0;
                                if(strstr($value['study_time'],':')){
                                    $haveStudy_time =explode(':', $value['study_time']);
                                    if(count($study_time) == 2){
                                        $haveStudyTime =$haveStudyTime + $haveStudy_time['0'] * 60 + $haveStudy_time['1'];
                                    }elseif(count($study_time) == 3){
                                        $haveStudyTime =$haveStudyTime + $haveStudy_time['0'] * 60 * 60 + $haveStudy_time['1'] * 60 + $haveStudy_time['2'];
                                    }
                                    $CourseList[$k]['study_time'] = $haveStudyTime;
                                }else{
                                    $CourseList[$k]['study_time'] = $value['study_time'];
                                }
                            }
                        }
                    }
                }
                if(in_array($user['id'],config('user_vip')))
                {
                    if(isset($v['productUrl'])){
                        $CourseList[$k]['img'] = $v['productUrl'];//超级vip显示彩色图片
                    }else{
                        $CourseList[$k]['img'] = $v['img'];//超级vip显示彩色图片
                    }
                    $CourseList[$k]['is_buy'] = 1;//超级vip
                }
            }
            //拼接
            $allTime=$courseModel->classHour($v['id']); //视频总时长
            $classAlltime = array();
            $classAlltime[0] = str_pad(floor($allTime/3600),2,0,STR_PAD_LEFT);
            $classAlltime[1] = str_pad(floor(($allTime%3600)/60),2,0,STR_PAD_LEFT);
            $classAlltime[2] = str_pad(($allTime-$classAlltime[0] * 3600 - $classAlltime[1] * 60),2,0,STR_PAD_LEFT);
            if($classAlltime[0] == '00'){
                unset($classAlltime[0]);
            }
            $CourseList[$k]['allTime'] = implode(':',$classAlltime);
            $classStudytime = array();
            $classStudytime[0] = str_pad(floor($studyTime/3600),2,0,STR_PAD_LEFT);
            $classStudytime[1] = str_pad(floor(($studyTime%3600)/60),2,0,STR_PAD_LEFT);
            $classStudytime[2] = str_pad(($studyTime-$classStudytime[0] * 3600 - $classStudytime[1] * 60),2,0,STR_PAD_LEFT);
            if($classStudytime[0] == '00'){
                unset($classStudytime[0]);
            }
            $CourseList[$k]['studyTime'] = implode(':',$classStudytime);
            $CourseList[$k]['studyPeriod'] = $m;
            $CourseList[$k]['video_class_id'] = $v['id'];
        }

        if($param['pageNum'] == 5){
            //手机端请求
            if($CourseList){
                return jsonMsg('获取成功',1,$CourseList,$count);
            }else{
                return jsonMsg('暂时没有符合条件的数据！',0,$CourseList,$count);
            }
        }elseif($param['pageNum'] == 6){
            //小程序
            if($CourseList){
                return jsonMsg('获取成功',1,$CourseList,$count);
            }else{
                return jsonMsg('暂时没有符合条件的数据！',0,$CourseList,$count);
            }
        }else{
            $pagesize=10;
           //获取分页 pc
            $page=Fpage($pagenow,$CountCourse,$pagesize);
            if($CourseList){
                return jsonMsg($page,1,$CourseList,$CountCourse);
            }else{
                return jsonMsg('暂时没有符合条件的数据！',0,$CourseList,$CountCourse);
            }
        }
    }

    /**
     * 递归实现无限极分类
     * @param $array 分类数据
     * @param $pid 父ID
     * @param $level 分类级别
     * @return $list 分好类的数组 直接遍历即可 $level可以用来遍历缩进
     */

    function getTree($array){
        //第一步 构造数据
        $items = array();
        foreach($array as $value){
            $items[$value['id']] = $value;
        }
        //第二部 遍历数据 生成树状结构
        $tree = array();
        foreach($items as $key => $value){
            if(isset($items[$value['pid']])){
                $items[$value['pid']]['son'][] = &$items[$key];
            }else{
                $tree[] = &$items[$key];
            }
        }
        return $tree;
    }
    //课程播放页面
    public function courseParticulars()
    {
        $param=input();
        //查找面包屑与课程简介
        $where['id']=$param['id'];
        $param['productStatus'] = $param['productStatus'] ?? 0;

        if(isset($param['productStatus']) && $param['productStatus']==1){
            $where['productStatus']=$param['productStatus'];
        }
        $this->assign('id',$param['id']);
        $this->assign('productStatus',$param['productStatus']);
        if(isset($param['k_id'])){
            $k_id=$param['k_id'];
            $this->assign('k_id',$k_id);
        }
        $getCrumbs=$this->getCrumbs($where);
        $this->assign('getCrumbs',$getCrumbs);
        //查找课程老师信息
        $whereTeacher['id']=$getCrumbs['teacherId'];
        $teacherInfo=Db::name('teacher')->where($whereTeacher)->find();
        $this->assign('teacherInfo',$teacherInfo);
        //查找播放课程知识点
        if(!isset($param['productStatus']) || $param['productStatus']==0){
            $whereCourse['kid']=$param['id'];
        }
        $whereCourse['part']=2;
        $info = $this ->_info;

        if($info){
            $this->assign('person_id',$info['id']);
        }else{
            return $this->redirect("/");
        }
        if(isset($param['type']) && $param['type'] == 1){
            return $this->getPayCourse($param['id'],$param['productStatus']);
        }
        $starttime=0;
        if(isset($param['videoid']) && !empty($param['videoid']) && $param['videoid'] != 'undefined'){
            if($info){//url地址存在videoid用户登陆了并且，判断用户是否购买过本节视频与是否到期
                $wherelog['person_id']=$info['id'];
                if(isset($param['productStatus']) && $param['productStatus']==1){
                    $wherelog['product_id']=$param['id'];
                }else{
                    $wherelog['video_class_id']=$param['id'];
                }
                $wherelog['expireTime']=array('egt',time());
                $res=Db::name('video_log')->where($wherelog) ->where(['type' => 0])->find();
                if($res){//用户购买过本课程直接查看本节课内容
                    $whereCourse['id']=$param['videoid'];
                }else{
                    if(in_array($info['id'],config('user_vip'))){
                        $whereCourse['id']=$param['videoid'];
                    }else{
                        //判断本视频是否可以试听
                        $videoTestWhere['id'] = $param['videoid'];
                        //$videoTewstWhere['audi'] = 2;
                        $videoTestList = Db::name('video')->where($videoTestWhere) ->where(['display' => 1])->find();
                        // if(empty($videoTestList)){
                        //     //用户没有激活 跳到课程支付页面
                        //     if(!isMobile()){
                        //         return $this->getPayCourse($param['id']);
                        //     }
                        //     return $this ->goWx();
                        // }else{
                            //判断新老用户 老用户可以免费试听 新用户购买试听
                            //根据新功能 上线时间判断新老用户
                            $time = config('new_functions_online_time');
                            // if($info['addtime'] < $time) {
                                //可以试听跳到视频播放 判断试听次数 每人只能试听五次
                                $audiNum = Db::name('audition_log')->where(['person_id'=>$info['id']])->group('video_id')->count();
                                if($param['productStatus']==1){
                                    $audiInfo = Db::name('audition_log')->where(['person_id'=>$info['id'],'video_id'=>$param['videoid'],'product_id'=>$param['id']])->find();
                                }else{
                                    $audiInfo = Db::name('audition_log')->where(['person_id'=>$info['id'],'video_id'=>$param['videoid'],'video_class_id'=>$param['id']])->find();
                                }

                                if(empty($audiInfo) && $info['id'] > 27){
                                    if($audiNum >= 5 && $videoTestList['audi'] !=3){

                                        return $this->getPayCourse($param['id'],$param['productStatus']);
                                    }else{
                                        $whereCourse['id']=$param['videoid'];
                                    }
                                }else{
                                    $whereCourse['id']=$param['videoid'];
                                }
                            // }else{
                            //     //判断新用户是否购买试听课程
                            //     $orderTestInfo = Db::name('order_person_son')->where(['person_id'=>$info['id'],'is_audition'=>1,'orderCheck'=>2])->order('endtime desc')->find();
                            //     if(empty($orderTestInfo)){
                            //         //跳到试听课支付页面
                            //         return $this->view('/index/course_pay/coursePay');
                            //     }else{
                            //         //判断试听 期限210天
                            //         $timeExpire = strtotime($orderTestInfo['endtime'])+3600 * 24 * config('audition_day');
                            //         if($timeExpire > time()){
                            //             $whereCourse['id']=$param['videoid'];
                            //         }else{
                            //             //跳到试听课支付页面
                            //             return $this->view('/index/course_pay/coursePay');
                            //         }
                            //     }
                            // }
                        // }
                    }
                }
            }
            else{
                //用户没有登录
                $videoTestWhere['id'] = $param['videoid'];
                $videoTestWhere['audi'] = 2;
                $videoTestList = Db::name('video')->where($videoTestWhere) ->where(['display' => 1])->select();
                if(empty($videoTestList)){
                    //跳到课程购买
                    //用户没有激活 跳到课程支付页面
                    if(isMobile()){
                        return $this ->goWx();
                    }
                }else{
                    //跳到课程购买
                    //用户没有激活 跳到课程支付页面
                    if(isMobile()){
                        $whereCourse['id']=$param['videoid'];
                    }
                }
            }
        }
        else{//url地址不存在video的情况下
            if(!empty($info)){//用户登陆了
                //url地址videoid为空用户登陆了判断本视频用户有没有观看记录
                $wherelog['expireTime']=array('eq',time());

                if($param['productStatus']==1) {
                    $whereUser = "person_id=" . $info['id'] . " and product_id=" . $param['id'] . " and study_time !=0";
                    $wherelog['product_id']=$param['id'];
                }else{
                    $whereUser = "person_id=" . $info['id'] . " and video_class_id=" . $param['id'] . " and study_time !=0";
                    $wherelog['video_class_id']=$param['id'];
                }
                $req=Db::name('video_log')->field('video_id,study_time,video_time')
                        ->where(['type' => 0,'expireTime'=>array('egt',time())])
                        ->where($whereUser)
                        ->order('id desc')
                        ->find();

                //判断该用户是否激活视频
                $wherelog['person_id']=$info['id'];

                $wherelog['expireTime']=array('egt',time());
                $result = Db::name('video_log')->where($wherelog) ->where(['type' => 0,'expireTime'=>array('egt',time())]) ->find();
                if($result){
                    if($req){//用户购买过本视频并且有学习的记录有观看学习的记录比较用户上次学习记录
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
                            if($param['productStatus']==1) {
                                $whereUser = "person_id=" . $info['id'] . " and product_id=" . $param['id'] . " and study_time =0";
                            }else{
                                $whereUser = "person_id=" . $info['id'] . " and video_class_id=" . $param['id'] . " and study_time =0";
                            }
                            $rtq=Db::name('video_log')->field('id,video_id,study_time,video_time') ->where(['type' => 0,'expireTime'=>array('egt',time())])  ->where($whereUser)->order('id asc')->find();
                            $whereCourse['id']=$rtq['video_id'];
                            $starttime=0;
                        }
                    }else{//用户还未开始学习
                        if($param['productStatus']==1) {
                            $whereUser = "person_id=" . $info['id'] . " and product_id=" . $param['id'] . " and study_time =0";
                        }else{
                            $whereUser = "person_id=" . $info['id'] . " and video_class_id=" . $param['id'] . " and study_time =0";
                        }
                        $rtq=Db::name('video_log')->field('video_id,study_time,video_time') ->where(['type' => 0,'expireTime'=>array('egt',time())])  ->where($whereUser)->order('id asc')->find();
                        if($rtq['video_id']){
                            $whereCourse['id']=$rtq['video_id'];
                        }
                        $starttime=0;
                    }
                }
                else{
                    //用户没有激活 跳到课程支付页面
                    if(in_array($info['id'],config('user_vip')))
                    {
                        if($param['productStatus']==1){
                            $videoInfo = Db::name('product_info')->where(['product_id'=>$param['id']])->find();
                            $whereCourse['id']=$videoInfo['video_id'];
                        }else{
                            $videoInfo = Db::name('video')->where(['kid'=>$param['id'],'part'=>2])->find();
                            $whereCourse['id']=$videoInfo['id'];
                        }
                    }else{
                        if(!isMobile()){
                            return $this->getPayCourse($param['id'],$param['productStatus']);
                        }
                        return $this ->goWx();
                    }
                }
            }else{
                //用户没有登录
                if(isMobile()){
                    return $this ->goWx();
                }
            }
        }
        $courseCatalogList=$this->courseCatalogList($param['id'],$info,$param['productStatus']);//url地址videoid不存在，并且没有用户登陆
        $courseCatalogList=getTree($courseCatalogList);//课程章节目录

        $this->assign('courseCatalogList',$courseCatalogList);
        $courseInfo=Db::name('video')->field('id,img,testclass,outline,link')->where($whereCourse) ->where(['display' => 1])->order('id asc')->find();

        $this->assign('courseInfo',$courseInfo);
        $whereKnow['s_id']=$courseInfo['id'];
        if(isset($param['videoid'])){
            $videoId=!empty($param['videoid'])?$param['videoid']:$courseInfo['id'];
        }else{
            $videoId = $courseInfo['id'];
        }
        $this->assign('videoId',$videoId);
        $this->assign('videoStartTime',time());   //视频开始观看的时间
        $start_time=isset($param['startTime'])?$param['startTime']:0;
        if(strstr($start_time,':')){
            //转换时间格式
            $start_time = $this->changeStartTime($start_time);
        }elseif(strstr($start_time,'：')){
            $start_time = str_replace('：',':',$start_time);
            $start_time = $this->changeStartTime($start_time);
        }
        $this->assign('start_time',$start_time);
        //查找本节课程知识点
        $knowLedge=Db::name('knowledge')->where($whereKnow)->order('sort asc,start_time asc')->select();
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
        $this->assign('knowLedge',$knowLedge);
        if($param['productStatus']==1){
            $sxh_img = Db::name('product') ->field('productUrl as img') ->where(['id' => $param['id']]) ->find();
        }else{
            $sxh_img = Db::name('video_class') ->field('img') ->where(['id' => $param['id']]) ->find();
        }
        $this->assign('sxh_img',$sxh_img);
        return $this->fetch('index/course/course-particulars');
    }
    public function getPayCourse($id,$productStatus='')
    {
        $productStatus = !empty($productStatus)?$productStatus:0;
        if($this ->_info){
            $courseCatalogList=$this->courseCatalogList($id,$this ->_info,$productStatus);//url地址videoid不存在，并且没有用户登陆

        }else{
            $courseCatalogList=$this->courseCatalogList($id,$productStatus);
        }

        $courseCatalogList=getTree($courseCatalogList);//课程章节目录.

//         echo "<pre>";
//         var_dump($courseCatalogList);die;
        //获取课程信息
        $courseModel = new courseModel();
        if(!empty($productStatus)){
            $coursePayList = Db::name('product')->alias('vc')
                ->join('guard_teacher t','t.id = vc.teacherId')
                ->join('guard_subject s','s.id = vc.subject_id')
                ->join('guard_grade g','g.id = vc.grade_id')
                ->field("t.content,t.name,vc.popularity,vc.productUrl as img,vc.price,vc.Discount, vc.purchase,
                                concat(g.grade, s.subject, 
                                    case vc.Semester
                                        when 1 then '上学期'
                                        when 2 then '下学期'
                                        when 3 then '全册'
                                    end
                                ) as title")
                ->where('vc.id',$id)
                ->find();

            $classNum = Db::name('product_info')
                        ->field('id')
                        ->where('product_id',$id)
                        ->count();
            $coursePayList['classNum']=$coursePayList['classNum']=$courseModel->countProductChapter($id);
            $info = $this ->_info;
            $isbay = Db::name('video_log')->where(['person_id' => $info['id'], 'product_id' => $id, 'type' => 0,'expireTime'=>array('egt',time())])->find();
        }else{
            $coursePayList = Db::name('video_class')->alias('vc')
                ->join('guard_video v','v.kid = vc.id')
                ->join('guard_teacher t','t.id = vc.teacherId')
                ->join('guard_subject s','s.id = vc.subject_id')
                ->join('guard_grade g','g.id = vc.grade_id')
                ->field("t.content,t.name,vc.popularity,vc.img,vc.price,vc.Discount, vc.purchase,
                                concat(g.grade, s.subject, 
                                    case vc.Semester
                                        when 1 then '上学期'
                                        when 2 then '下学期'
                                        when 3 then '全册'
                                    end
                                ) as title")
                ->where('vc.id',$id)
                ->find();
            $where['kid'] = $id;
            $coursePayList['classNum']=$courseModel->countClassChapter($where);
            $info = $this ->_info;
            $isbay = Db::name('video_log')->where(['person_id' => $info['id'], 'video_class_id' => $id, 'type' => 0,'expireTime'=>array('egt',time())])->find();
        }
        $this->assign('coursePayList',$coursePayList);
        $this->assign('isbay',isset($isbay) && !empty($isbay) ? 1 : 0);
        $this->assign('courseCatalogList',$courseCatalogList);
        $this->assign('productStatus',$productStatus);
        $this->assign('id',$id);
        // var_dump($courseCatalogList);die;
        return $this->fetch('index/course/course-pay');
    }
    //js获取指定课程指定章节的知识点
    public function jsGetKnowLedge()
    {
        $param=input();
        if(isset($param['s_id'])&&!empty($param['s_id'])&&$param['s_id']!=null){
            $s_id=$param['s_id'];
        }else{
            $whereVideo['kid']=$param['id'];
            $whereVideo['part']=2;
            $s_id=Db::name('video')->field('id')->where($whereVideo) ->where(['display' => 1])->order('id asc')->find();
            $s_id=$s_id['id'];
        }
        $where['s_id']=$s_id;
        $knowLedge=Db::name('knowledge')->where($where)->order('sort asc,start_time asc')->select();
        if($knowLedge){
            jsonMsg('sucess',1,$knowLedge);
        }else{
            jsonMsg('error',0);
        }
    }
    //获取课程章节目录
    public function courseCatalogList($id,$info="",$productStatus='')
    {
        if(!empty($productStatus)){
            $proList=Db::name('product_info')->field('video_id,video_pid')->where(['product_id'=>$id,'forbiden' => 1])->select();
            $proVideo = array_column($proList,'video_id');
            $Pidvideo = array_unique(array_column($proList,'video_pid'));
            $proVideo = array_merge($proVideo,$Pidvideo);
            $res=Db::name('video')->whereIn('id',$proVideo) ->where(['display' => 1])->order('sort')->select();
            $_personid = isset($info['id']) ? $info['id'] : 0;
            $rts=Db::name('video_log') ->where(['type' => 0,'product_id'=>$id,'person_id' => $_personid,'expireTime'=>array('egt',time())])->find();
        }else{
            $where['kid']=$id;
            $res=Db::name('video')->where($where) ->where(['display' => 1])->order('sort')->select();
            $_personid = isset($info['id']) ? $info['id'] : 0;
            $rts=Db::name('video_log') ->where(['type' => 0,'video_class_id'=>$id,'person_id' => $_personid,'expireTime'=>array('egt',time())])->find();
        }

        foreach($res as $k=>$v){
            if($v['part']==2){
                $whereknow['s_id']=$v['id'];
                $res[$k]['skill']=Db::name('knowledge')->where($whereknow)->order('sort asc,start_time asc')->select();
                foreach ($res[$k]['skill'] as $key => $value) {
                    //判断时间格式
                    if(strstr($value['start_time'],':')){
                        //转换时间格式
                        $res[$k]['skill'][$key]['start_time'] = $this->changeStartTime($value['start_time']);
                    }elseif(strstr($value['start_time'],'：')){

                        $start_time = str_replace('：',':',$value['start_time']);
                        $res[$k]['skill'][$key]['start_time'] = $this->changeStartTime($start_time);
                    }
                }
                if($info){
                    $wherelog['person_id']=$info['id'];
                    $wherelog['video_id']=$v['id'];
                    if(!empty($productStatus)){
                        $wherelog['product_id']=$id;
                    }else{
                        $wherelog['video_class_id']=$id;
                    }
                    $res[$k]['videoLog']=Db::name('video_log') ->where(['type' => 0,'expireTime'=>array('egt',time())]) ->where($wherelog)->find();
                }else{
                    $res[$k]['videoLog']['video_status']=1;
                    $res[$k]['videoLog']['study_num']=2;
                }
            }
            if($info)
            {
                if(in_array($info['id'],config('user_vip'))){
                    $res[$k]['isbay']= 1;
                }else{
                    $res[$k]['isbay']=$rts?1:0;
                }
            }else{
                $res[$k]['isbay']=$rts?1:0;
            }
        }
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
//验证用户是否具有播放本视频的权限(点击视频播放页目录知识点)
public function courseVerification()
{
    $param=input();
    $videoid=$param['videoid'];
    $video_class_id=$param['video_class_id'];
    $product_id=$param['product_id'];
    $productStatus=$param['productStatus']??0;
    $k_id=$param['k_id'];
    $video_id = isset($param['videoid']) ? $param['videoid'] : '';
    $info = $this ->_info;
    if(!$info){
        return $this->redirect("/");
    }
    //  登录状态  //检测视频试听权限 ---start----
    $datas['is_login'] = $info ? 1 : 0;

    $audi = Db::name('video') ->field('audi,audi_time') ->where(['id' => $video_id]) ->where(['display' => 1]) ->find();
    //  是否试听
    $datas['is_audi'] = $audi['audi'] == 2 ? 2 : 0;

    $is_buy = 0;

    if($info){
        if($productStatus==1){
            $is_buy = Db::name('video_log') ->field('id')
                ->where(['person_id' => $info['id'], 'video_id' => $video_id, 'type' => 0,'expireTime'=>array('egt',time()),'product_id'=>$param['product_id']])
                ->find();
        }else{
            $is_buy = Db::name('video_log') ->field('id')
                ->where(['person_id' => $info['id'], 'video_id' => $video_id, 'type' => 0,'expireTime'=>array('egt',time()),'video_class_id'=>$param['video_class_id']])
                ->find();
        }

    }
    //  是否购买
    $datas['is_buy'] = $is_buy ? 1 : 0;
    // 试听时长
    $datas['audi_time'] = $audi['audi_time'];
    $datas['video_id'] = $video_id;
    //检测视频试听权限 ---end----
    //用户有权限观看本课程
    $whereVideo['id']=$videoid;
    $res=Db::name('video')->where($whereVideo) ->where(['display' => 1])->find();
    $whereKnow['s_id']=$videoid;
    $res['skill']=Db::name('knowledge')->where($whereKnow)->order('sort asc,start_time asc')->select();
    $res['videoStartTime'] = time();  //视频开始观看的时间
    //转换时间格式
    foreach ($res['skill'] as $key => $value) {
        //判断时间格式
        if(strstr($value['start_time'],':')){
            //转换时间格式
            $res['skill'][$key]['start_time'] = $this->changeStartTime($value['start_time']);
        }elseif(strstr($value['start_time'],'：')){

            $start_time = str_replace('：',':',$value['start_time']);
            $res['skill'][$key]['start_time'] = $this->changeStartTime($start_time);
        }
    }
    if($info){  //用户登陆判断用户是否购买过本课程是否过期了
        $wherelog['person_id']=$info['id'];
        if($productStatus==1){
            $wherelog['product_id']=$param['product_id'];
        }else{
            $wherelog['video_class_id']=$param['video_class_id'];
        }

        $wherelog['expireTime']=array('egt',time());
        $rts=Db::name('video_log') ->where($wherelog) ->where(['type' => 0,'expireTime'=>array('egt',time())]) ->find();
        if($rts){
            //更新上一视频播放记录
            $wherelog['video_id']=$param['video_id'];
            $rts=Db::name('video_log')->where(['type' => 0]) ->where($wherelog)->find();
            // 记录学习时间记录表
            $param['person_id'] = $info['id'];
           $this->checkCloseBrowLog($param,$rts['study_time']);
           if($rts){
                if($param['nowTime'] != '0:0'){
                    if($param['nowTime']==$rts['video_time']){
                        $data['video_status']=3;
                        $data['study_num']=$rts['study_num']+1;
                        $data['study_time']=$param['nowTime'];
                        $data['uptime']=time();
                    }else{
                        $data['video_status']=2;
                        $data['study_time']=$param['nowTime'];
                        $data['uptime']=time();
                    }
                    $req=Db::name('video_log')->where(['type' => 0])->where($wherelog)->update($data);
                }
           }
           jsonMsg('sucess',1,$res,$datas);
        }else{
            if(!in_array($info['id'],config('user_vip'))){
                if($param['study_time'] != '0:0' && $param['nowTime'] != '0:0'){
                    $lastAudiInfo = Db::name('audition_log')->where(['person_id'=>$info['id'],'video_id'=>$param['video_id']])->order('id desc')->find();
                    //获取视频信息
                    $lastVideoList = Db::name('video')->where('id',$param['video_id']) ->where(['display' => 1])->find();
                    if($lastVideoList['audi'] !=3){//开学第一课不记录试听记录
                        if(empty($lastAudiInfo)){
                            if($productStatus==1){
                                $audiDate = [
                                    'person_id' => $info['id'],
                                    'product_id' => $param['product_id'],
                                    'video_id' => $param['video_id'],
                                    'video_time' => $lastVideoList['classhour'],
                                    'create_time' => time(),
                                    'study_time' =>$param['study_time'],
                                ];
                                Db::name('audition_log')->insert($audiDate);
                            }else{
                                $audiDate = [
                                    'person_id' => $info['id'],
                                    'video_class_id' => $param['video_class_id'],
                                    'video_id' => $param['video_id'],
                                    'video_time' => $lastVideoList['classhour'],
                                    'create_time' => time(),
                                    'study_time' =>$param['study_time'],
                                ];
                                Db::name('audition_log')->insert($audiDate);
                            }
                        }else{
                            //修改
                            // $audiDate = [
                            //     'update_time' => time(),
                            //     'study_time' =>$param['nowTime'],
                            // ];
                            // Db::name('audition_log')->where(['id' => $lastAudiInfo['id']])->update($audiDate);
                            if($lastAudiInfo['study_time'] != $param['study_time'] || $lastAudiInfo['video_id'] != $param['video_id']){
                                if($productStatus==1){
                                    $audiDate = [
                                        'person_id' => $info['id'],
                                        'product_id' => $param['product_id'],
                                        'video_id' => $param['video_id'],
                                        'video_time' => $lastVideoList['classhour'],
                                        'create_time' => time(),
                                        'study_time' =>$param['study_time'],
                                        'num' =>$lastAudiInfo['num'] + 1,
                                    ];
                                    Db::name('audition_log')->insert($audiDate);
                                }else{
                                    $audiDate = [
                                        'person_id' => $info['id'],
                                        'video_class_id' => $param['video_class_id'],
                                        'video_id' => $param['video_id'],
                                        'video_time' => $lastVideoList['classhour'],
                                        'create_time' => time(),
                                        'study_time' =>$param['study_time'],
                                        'num' =>$lastAudiInfo['num'] + 1,
                                    ];
                                    Db::name('audition_log')->insert($audiDate);
                                }

                            }
                        }
                    }
                }
            }else{
                jsonMsg('sucess',1,$res,$datas);
            }
            //获取用户状态
            $personInfo = Db::name('person')->where('id',$info['id'])->find();
            if($personInfo['status'] == 1){
                $data['status'] = 2;
                $data['up_time'] = time();
                Db::name('person')->where('id',$info['id'])->update($data);
            }

            //可以试听跳到视频播放 判断试听次数 没人只能试听五次
            $audiNum = Db::name('audition_log')->where(['person_id'=>$info['id']])->group('video_id')->count();
            $audiInfo = Db::name('audition_log')->where(['person_id'=>$info['id'],'video_id'=>$param['videoid']])->find();
            // if($res['audi']==2){
                //判断新老用户 老用户免费试听 新用户购买试听
                $time = config('new_functions_online_time');
                // if($info['addtime'] < $time) {
                    if(empty($audiInfo) && $info['id'] > 27){
                        if($audiNum < 5){
                            jsonMsg("每个用户能免费试听<span style='color: #0a3e82;'>5</span>个视频<p class='number_p audited'>您已观看<span style='color: #0a3e82;'>".$audiNum."</span>个</p>",2,$res,$datas);
                        }else{
                            jsonMsg('<p style="font-size: 24px;">你的试听次数用完啦！</p><p class="number_p audited" style="padding-top: 19px">若想继续学习,请先购买</p>',0);
                        }
                    }else{
                        jsonMsg("每个用户能免费试听<span style='color: #0a3e82;'>5</span>个视频<p class='number_p audited'>您已观看<span style='color: #0a3e82;'>".$audiNum."</span>个</p>",2,$res,$datas);
                    }
                // }else{
                //     //判断新用户是否购买试听课程
                //     $orderTestInfo = Db::name('order_person_son')->where(['person_id'=>$info['id'],'is_audition'=>1,'orderCheck'=>2])->order('endtime desc')->find();
                //     if(empty($orderTestInfo)){
                //         jsonMsg('<p style="font-size: 24px;">您不属于试听用户</p><p class="number_p audited" style="padding-top: 19px">若想继续学习,请先购买</p>',0,'/index/course_pay/coursePay');
                //     }else{
                //         //判断试听 期限210天
                //         $timeExpire = strtotime($orderTestInfo['endtime'])+3600 * 24 * config('audition_day');
                //         if($timeExpire > time()){
                //             jsonMsg('您已购买试听视频，点击确定继续学习！',2,$res,$datas);
                //         }else{
                //             jsonMsg('<p style="font-size: 24px;">您不属于试听用户</p><p class="number_p audited" style="padding-top: 19px">若想继续学习,请先购买</p>',0,'/index/course_pay/coursePay');
                //         }
                //     }
                // }
            // }else{
            //     jsonMsg('<p style="font-size: 24px;">该课程不属于试听课程</p><p class="number_p audited" style="padding-top: 19px">若想继续学习,请先购买</p>',0);
            //     // ,'/index/course_pay/coursePay'
            // }
        }
    }
    // else{
    //     //用户没有登陆判断本视频是否可以试听
    //     $whereVideo['id']=$videoid;
    //     $res=Db::name('video')->where($whereVideo)->find();
    //     //可以试听跳到视频播放 判断试听次数 没人只能试听五次
    //     $audiNum = Db::name('audition_log')->where(['person_id'=>$info['id']])->count();
    //     $audiInfo = Db::name('audition_log')->where(['person_id'=>$info['id'],'video_id'=>$videoid])->find();
    //     if(empty($audiInfo)){
    //         if($audiNum < 5){
    //             if($res['audi']==1){
    //                 $whereKnow['s_id']=$videoid;
    //                 $res['skill']=Db::name('knowledge')->where($whereKnow)->select();
    //                 jsonMsg('sucess',1,$res);
    //             }else{
    //                 jsonMsg('本课程不可观看请先购买！',0);
    //             }
    //         }else{
    //             jsonMsg('每个用户只能试听五个视频',0);
    //         }
    //     }
    // }
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
//获取指定节课程的知识点
public function videoSkillKnow()
{
    $param=input();
    $where['s_id']=$param['video_id'];
    $res=Db::name('knowledge')->where($where)->order('start_time asc')->select();
    //转换时间格式
    foreach ($res as $key => $value) {
        //判断时间格式
        if(strstr($value['start_time'],':')){
            //转换时间格式
            $res[$key]['start_time'] = $this->changeStartTime($value['start_time']);
        }elseif(strstr($value['start_time'],'：')){

            $start_time = str_replace('：',':',$value['start_time']);
            $res[$key]['start_time'] = $this->changeStartTime($start_time);
        }
    }
    jsonMsg('success',1,$res);
}

//视频播放结束更新记录用户观看视频记录
public function updateCourseLog()
{
    $info = $this ->_info;
    $param=input();
    $productStatus = $param['productStatus']??0;
    $time = date('Y-m-d', time());
    if($info){//用户登陆判断用户是否购买过本课程是否过期了
        //判断用户是否购买过此视频
        $wherelog['person_id']=$info['id'];
        if($productStatus==1){
            $wherelog['product_id']=$param['product_id'];
        }else{
            $wherelog['video_class_id']=$param['video_class_id'];
        }
        $wherelog['video_id']=$param['videoid'];
        $rts=Db::name('video_log')->where(['type' => 0,'expireTime'=>array('egt',time())])->where($wherelog)->find();
        // 记录学习时间记录表
        $this->checkVideoStudy($param,$rts['study_time']);
        if($rts){
            $data['video_status']=3;
            $data['study_num']=$rts['study_num']+1;
            $data['study_time']=$rts['video_time'];
            $data['uptime']=time();
            $req=Db::name('video_log')->where(['type' => 0])->where($wherelog)->update($data);
        }else{
            if(!in_array($info['id'],config('user_vip'))){
                $lastAudiInfo = Db::name('audition_log')->where(['person_id'=>$info['id'],'video_id'=>$param['videoid']])->order('id desc')->find();
                //获取视频信息
                $lastVideoList = Db::name('video')->where('id',$param['videoid']) ->where(['display' => 1])->find();
                if($lastVideoList['audi'] !=3){
                    if(empty($lastAudiInfo)){
                        if($productStatus==1){
                            $audiDate = [
                                'person_id' => $info['id'],
                                'product_id' => $param['product_id'],
                                'video_id' => $param['videoid'],
                                'video_time' => $lastVideoList['classhour'],
                                'create_time' => time(),
                                'study_time' =>$param['study_time'],
                            ];
                        }else{
                            $audiDate = [
                                'person_id' => $info['id'],
                                'video_class_id' => $param['video_class_id'],
                                'video_id' => $param['videoid'],
                                'video_time' => $lastVideoList['classhour'],
                                'create_time' => time(),
                                'study_time' =>$param['study_time'],
                            ];
                        }

                        Db::name('audition_log')->insert($audiDate);
                    }else{
                        //修改
                        // $audiDate = [
                        //     'update_time' => time(),
                        //     'study_time' =>$param['nowTime'],
                        // ];
                        // Db::name('audition_log')->where(['id' => $lastAudiInfo['id']])->update($audiDate);
                        if($lastAudiInfo['study_time'] != $lastVideoList['video_time'] || $lastAudiInfo['video_id'] != $param['videoid']){
                            if($productStatus==1){
                                $audiDate = [
                                    'person_id' => $info['id'],
                                    'product_id' => $param['product_id'],
                                    'video_id' => $param['videoid'],
                                    'video_time' => $lastVideoList['classhour'],
                                    'create_time' => time(),
                                    'study_time' =>$param['study_time'],
                                    'num' =>$lastAudiInfo['num'] + 1,
                                ];
                            }else{
                                $audiDate = [
                                    'person_id' => $info['id'],
                                    'video_class_id' => $param['video_class_id'],
                                    'video_id' => $param['videoid'],
                                    'video_time' => $lastVideoList['classhour'],
                                    'create_time' => time(),
                                    'study_time' =>$param['study_time'],
                                    'num' =>$lastAudiInfo['num'] + 1,
                                ];
                            }
                            Db::name('audition_log')->insert($audiDate);
                        }
                    }
                    //获取用户状态
                    $personInfo = Db::name('person')->where('id',$info['id'])->find();
                    if($personInfo['status'] == 1){
                        $data['status'] = 2;
                        $data['up_time'] = time();
                        Db::name('person')->where('id',$info['id'])->update($data);
                    }
                }
            }
        }
    }
}

/**
 * 播放完处理学习记录数据
 * 增加学习记录数据 表video_watch_log
 * @author yangjizhou 2019-06-19
 */
 public function checkVideoStudy($param,$last_study_time)
 {
    $productStatus = $param['productStatus']??0;
    $courseModel = new courseModel();
    $info = $this ->_info;
    $time = date('Y-m-d', time());
    if ($info) {
        $wherelog['person_id']=$info['id'];
        if($productStatus==1){
            $wherelog['product_id']=$param['product_id'];
        }else{
            $wherelog['video_class_id']=$param['video_class_id'];
        }
        $wherelog['video_id']=$param['videoid'];
        $wherelog['time'] = $time;

        $rts=Db::name('video_watch_log')->where($wherelog)->find();
        // 累计学习时间 start
        $videotime = Db::name('video')->where(['id' => $param['videoid']]) ->where(['display' => 1])->value('classhour');

        // if (!is_numeric($rts['study_time'])) {
        //     $studytime  = isset($rts['study_time']) && !empty($rts['study_time']) ? $this->getSecond($rts['study_time']) : 0;
        // } else {
        //     $studytime  = $rts['study_time'] ? $rts['study_time'] : 0;
        // }

        // $restime    = (int) $studytime + (int)($videotime ? $this->getSecond($videotime) : 0);
        // end
        $data['video_status'] = 3;

        // if ($rts) {
        //     $data['study_num'] = isset($rts['study_num']) ? $rts['study_num'] + 1 : 1;
        //     $data['study_time'] = $restime;
        //     $req=Db::name('video_watch_log')->where($wherelog)->update($data);

        // } else {
        //如果观看时长少于两分钟 不记录
        if(!empty($param['videoStartTime']) && time() - $param['videoStartTime'] >= 120){
            $courseinfo = $courseModel->course($param['video_class_id']);
            $data['study_time']          = $videotime ? $videotime : 0;   //本次观看的时间节点
            $data['last_study_time']     = $last_study_time ? $last_study_time : 0;  //上次观看的时间节点
            $data['all_watch_time']      = time() - $param['videoStartTime']; //实际观看的时间
            $data['grade_id']       = $courseinfo['grade_id'] ? $courseinfo['grade_id'] : '';
            $data['subject_id']     = $courseinfo['subject_id'] ? $courseinfo['subject_id'] : '';
            $data['semester']       = $courseinfo['Semester'] ? $courseinfo['Semester'] : '';
            $data['study_num']      = isset($rts['study_num']) ? $rts['study_num'] + 1 : 1;
            $data['time']           = time();
            $data['person_id']      = $info['id'];
            if($productStatus==1){
                $data['product_id'] = $param['product_id'];
            }else{
                $data['video_class_id'] = $param['video_class_id'];
            }
            $data['video_id']       = $param['videoid'];
            Db::name('video_watch_log')->insert($data);
        }
        // }
    }
 }

//刷新浏览器或者关闭浏览器更新用户观看记录
public function closeUpdateCourseLog()
{
    $param=input();
    $productStatus = $param['productStatus']??0;
    $info = $this ->_info;
    if($param['person_id']>=1){
        $wherelog['person_id']=$param['person_id'];
        if($productStatus==1){
            $wherelog['product_id']=$param['product_id'];
        }else{
            $wherelog['video_class_id']=$param['video_class_id'];
        }
        $wherelog['video_id']=$param['videoid'];
        $rts=Db::name('video_log')->where(['type' => 0,'expireTime'=>array('egt',time())])->where($wherelog)->find();
        $this->checkCloseBrowLog($param,$rts['study_time']);     //记录学习时长
       if($rts){
            if($param['nowTime'] != '0:0'){
                $data['video_status']=2;
                $data['study_time']=$param['nowTime'];
                $data['uptime']=time();
                $req=Db::name('video_log')->where(['type' => 0])->where($wherelog)->update($data);
            }
       }else{
            if(!in_array($info['id'],config('user_vip'))){
                if($param['study_time'] != '0:0' && $param['nowTime'] != '0:0'){
                    if($productStatus==1){
                        $audiInfo = Db::name('audition_log')->where(['person_id'=>$info['id'],'video_id'=>$param['videoid'],'product_id'=>$param['product_id']])->order('id desc')->find();
                    }else{
                        $audiInfo = Db::name('audition_log')->where(['person_id'=>$info['id'],'video_id'=>$param['videoid'],'video_class_id'=>$param['video_class_id']])->order('id desc')->find();
                    }
                    //获取视频信息
                    $videoList = Db::name('video')->where('id',$param['videoid']) ->where(['display' => 1])->find();
                    if($videoList['audi'] !=3){
                        if(empty($audiInfo)){
                            if($productStatus==1){
                                $audiDate = [
                                    'person_id' => $info['id'],
                                    'product_id' => $param['product_id'],
                                    'video_id' => $param['videoid'],
                                    'video_time' => $videoList['classhour'],
                                    'create_time' => time(),
                                    'study_time' =>$param['study_time'],
                                ];
                            }else{
                                $audiDate = [
                                    'person_id' => $info['id'],
                                    'video_class_id' => $param['video_class_id'],
                                    'video_id' => $param['videoid'],
                                    'video_time' => $videoList['classhour'],
                                    'create_time' => time(),
                                    'study_time' =>$param['study_time'],
                                ];
                            }

                            Db::name('audition_log')->insert($audiDate);
                        }else{
                            //修改
                            // $audiDate = [
                            //     'update_time' => time(),
                            //     'study_time' =>$param['nowTime'],
                            // ];
                            // Db::name('audition_log')->where(['id' => $lastAudiInfo['id']])->update($audiDate);
                            if($audiInfo['study_time'] != $param['nowTime'] || $audiInfo['video_id'] != $param['videoid']){
                                if($productStatus==1){
                                    $audiDate = [
                                        'person_id' => $info['id'],
                                        'product_id' => $param['product_id'],
                                        'video_id' => $param['videoid'],
                                        'video_time' => $videoList['classhour'],
                                        'create_time' => time(),
                                        'study_time' =>$param['study_time'],
                                        'num' =>$audiInfo['num'] + 1,
                                    ];
                                }else{
                                    $audiDate = [
                                        'person_id' => $info['id'],
                                        'video_class_id' => $param['video_class_id'],
                                        'video_id' => $param['videoid'],
                                        'video_time' => $videoList['classhour'],
                                        'create_time' => time(),
                                        'study_time' =>$param['study_time'],
                                        'num' =>$audiInfo['num'] + 1,
                                    ];
                                }
                                Db::name('audition_log')->insert($audiDate);
                            }
                        }
                    }

                }
                //获取用户状态
                $personInfo = Db::name('person')->where('id',$info['id'])->find();
                if($personInfo['status'] == 1){
                    $data['status'] = 2;
                    $data['up_time'] = time();
                    Db::name('person')->where('id',$info['id'])->update($data);
                }
            }
        }
    }
}

/**
 * 刷新或关闭浏览器后学习记录数据处理或者切换视频更新时长
 * @author yangjizhou 2019-06-19
 */
public function checkCloseBrowLog($param,$last_study_time)
{
    if (!$param) {
        return false;
    }
    $productStatus=$param['productStatus']??0;
    $courseModel = new courseModel();
    $time = date('Y-m-d', time());
    if ($param['person_id'] >= 1) {
        $wherelog['person_id']=$param['person_id'];
        if($productStatus==1){
            $wherelog['product_id']=$param['product_id'];
        }else{
            $wherelog['video_class_id']=$param['video_class_id'];
        }
        $wherelog['video_id']=$param['videoid'];
        $wherelog['time'] = $time;
        $rts=Db::name('video_watch_log')->where($wherelog)->find();
        // if (!is_numeric($rts['study_time'])) {
        //     $studytime  = isset($rts['study_time']) ? $this->getSecond($rts['study_time']) : 0;
        // } else {
        //     $studytime  = $rts['study_time'] ? $rts['study_time'] : 0;
        // }
        // $nowtime    = isset($param['nowTime']) && !empty($param['nowTime']) ? $this->getSecond($param['nowTime']) : 0;
        // $restime    = (int) $studytime + (int) $nowtime;
        // if ($rts) {
        //     $data['study_time'] = $restime;
        //     $data['video_status'] = (isset($rts['video_status']) && $rts['video_status'] == 3) ? 3 : 2;
        //     $req=Db::name('video_watch_log')->where($wherelog)->update($data);

        // } else {
        //如果观看时长少于两分钟 不记录
        if(!empty($param['videoStartTime']) && time() - $param['videoStartTime'] >= 120){
            $courseinfo = $courseModel->course($param['video_class_id']);
            $data['grade_id']       = $courseinfo['grade_id'] ? $courseinfo['grade_id'] : '';
            $data['subject_id']     = $courseinfo['subject_id'] ? $courseinfo['subject_id'] : '';
            $data['semester']       = $courseinfo['Semester'] ? $courseinfo['Semester'] : '';
            $data['video_status']   = 2;
            // $videotime = Db::name('video')->where(['id' => $param['videoid']]) ->where(['display' => 1])->value('classhour');
            $data['study_time']          = $param['nowTime'];     //本次观看的时间节点
            $data['last_study_time']     = $last_study_time ? $last_study_time : 0;  //上次观看的时间节点
            $data['all_watch_time']      = time() - $param['videoStartTime'];   //实际观看时长
            $data['time']           = time();
            $data['study_num']      = isset($rts['study_num']) ? $rts['study_num'] + 1 : 1;
            $data['person_id']      = $param['person_id'];
            if($productStatus==1){
                $data['product_id'] = $param['product_id'];
            }else{
                $data['video_class_id'] = $param['video_class_id'];
            }
            $data['video_id']       = $param['videoid'];
            Db::name('video_watch_log')->insert($data);
        }

        // }
    }

}


public function getVideoTime($videoid)
{
    return Db::name('video')->where(['id' => $videoid]) ->where(['display' => 1])->value('classhour');
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

/**
 * 根据时间秒获取时间格式
 * @author yangjizhou 2019-06-12
 */
public function getdate($time)
{
    if(is_numeric($time)) {
        $value = array(
            "hours" => 0,
            "minutes" => 0,
            "seconds" => 0
        );

        if($time >= 3600){
            $value["hours"] = floor($time/3600);
            $time = ($time%3600);
        }

        if($time >= 60){
            $value["minutes"] = floor($time/60);
            $time = ($time%60);
        }

        $value["seconds"] = floor($time);

        return (!empty($value['hours']) ? $value['hours'].':' : '00:'). (!empty($value['minutes']) ? $value['minutes'].':' : '00:').$value['seconds'];
    }

}

 //用户收藏视频
    public function courseCollections()
    {
        $param=input();
        var_dump($param);die;
        $productStatus = $param['productStatus']??0;
        $user = $this ->_info;
        //根据video_id 获取当前图片
        // $videoPath = Db::name('video')->where('id='.$param['video'])->find();
        // //收藏截图的名称
        // $newImgName = "collect".time().".jpg";
        // //获取封面截图 返回七牛云图片路径
        // $imgPath = getNewImgPath($newImgName,$param['startTime'],$videoPath['link']);
        //获取当前视频的图片
        $videoPath = Db::name('video')->where('id',$param['video']) ->where(['display' => 1])->find();
        //获取课程信息

        $coursePayList = Db::name('video_class')->alias('vc')
            ->join('guard_video v','v.kid = vc.id')
            ->join('guard_subject s','s.id = vc.subject_id')
            ->join('guard_grade g','g.id = vc.grade_id')
            ->field("v.img,outline,
                            concat(g.grade, s.subject, 
                                case vc.Semester
                                    when 1 then '上学期'
                                    when 2 then '下学期'
                                end
                            ) as title")
            ->where('v.id',$param['video'])
            ->where(['v.display' => 1])
            ->find();

        $data['collectImg'] = $coursePayList['img'];
        if($user){
            $data['uid']=$user['id'];
            $data['video_id']=$param['video'];
            $data['subject_id']=$param['subject_id'];
            $data['startTime']=$param['startTime'];
            $data['endTime']=$param['endTime'];
            $data['countTime']=$param['countTime'];
            if(isset($productStatus) && $productStatus==1){
                $data['product_id']=$param['product_id'];
            }else{
                $data['video_class_id']=$param['video_class'];
            }
            $data['intime']=time();
            $data['ctitle']=$param['newName'];
            $data['noteText']=$coursePayList['outline'];
            $res=Db::name('person_collect')->insert($data);
            if($res){
                return jsonMsg('收藏成功 ，可以到(  个人中心>我的收藏  )里面查看！',1);
            }else{
                return jsonMsg('收藏失败！',0);
            }
        }else{
            return jsonMsg('请先登陆！',0);
        }
    }
    //记录用户开始观看视频       没改产品相关
    public function  myVideoLog()
    {
        $param=input();
        $user = $this ->_info;
        //判断用户有没有购买过本课程，免费试听的不存进度
        $where['person_id']=$user['id'];
        $where['video_class_id']=$param['kid'];
        $time=time();
        $where['kcdqtime']=array('egt',$time);
        $where['state']=2;
        $r=Db::name('order')->where($where)->find();
        if($r){
            $data['person_id']=$user['id'];
            $data['video_class_id']=$param['kid'];
            $data['video_id']=$param['id'];
            //判断本条记录是否存在
            $re=Db::name('videolog')->where($data)->find();
            if(!$re){//假如记录不存在
                $data['type']=1;
                $data['order_time']=time();
                $res=Db::name('videolog')->insert($data);
            }
        }

    }
    //更新用户观看完视频的状态
    public function  myFunction()
    {
        $param=input();
        $id = $param['id']??0;
        $productStatus = $param['productStatus']??0;
        $info = $this ->_info;
        //判断用户有没有购买过本课程，免费试听的不存进度
        $whereOrder['person_id']=$info['id'];
        $whereOrder['video_id']=$param['videoid'];
        if($productStatus==1){
            $whereOrder['video_class_id']=$id;
        }else{
            $whereOrder['product_id']=$id;
        }
        $whereOrder['expireTime']=array('egt',time());
        $r=Db::name('video_log')->where($whereOrder)->find();
        if(empty($r)){
            if($productStatus==1){
                $audiInfo = Db::name('audition_log')->where(['person_id'=>$info['id'],'video_id'=>$param['videoid'],'product_id'=>$id])->find();
            }else{
                $audiInfo = Db::name('audition_log')->where(['person_id'=>$info['id'],'video_id'=>$param['videoid'],'video_class_id'=>$id])->find();
            }

            //获取视频信息
            $videoList = Db::name('video')->where('id',$param['videoid'])->find();
            if(empty($audiInfo)){
                if($productStatus==1){
                    $audiDate = [
                        'person_id' => $info['id'],
                        'product_id' => $id,
                        'video_id' => $param['videoid'],
                        'video_time' => $videoList['classhour']?$videoList['classhour']:0,
                        'create_time' => time(),
                        'study_time' =>$param['nowTime'],
                    ];
                }else{
                    $audiDate = [
                        'person_id' => $info['id'],
                        'video_class_id' => $id,
                        'video_id' => $param['videoid'],
                        'video_time' => $videoList['classhour']?$videoList['classhour']:0,
                        'create_time' => time(),
                        'study_time' =>$param['nowTime'],
                    ];
                }

                $res = Db::name('audition_log')->insert($audiDate);
            }else{

                // $audiDate = [
                //     'update_time' => time(),
                //     'study_time' =>$param['nowTime'],
                //     'num' =>$audiInfo['num']+1,
                // ];
                // $res = Db::name('audition_log')->where(['id' => $audiInfo['id']])->update($audiDate);
                if($audiInfo['study_time'] != $param['nowTime'] || $audiInfo['video_id'] != $param['videoid']){
                    if($productStatus==1){
                        $audiDate = [
                            'person_id' => $info['id'],
                            'product_id' => $id,
                            'video_id' => $param['videoid'],
                            'video_time' => $videoList['classhour']?$videoList['classhour']:0,
                            'create_time' => time(),
                            'study_time' =>$param['nowTime'],
                            'num' =>$audiInfo['num']+1,
                        ];
                    }else{
                        $audiDate = [
                            'person_id' => $info['id'],
                            'video_class_id' => $id,
                            'video_id' => $param['videoid'],
                            'video_time' => $videoList['classhour']?$videoList['classhour']:0,
                            'create_time' => time(),
                            'study_time' =>$param['nowTime'],
                            'num' =>$audiInfo['num']+1,
                        ];
                    }
                    $res = Db::name('audition_log')->insert($audiDate);
                }
            }
            //获取用户状态
            $personInfo = Db::name('person')->where('id',$info['id'])->find();
            if($personInfo['status'] == 1){
                $data['status'] = 2;
                $data['up_time'] = time();
                Db::name('person')->where('id',$info['id'])->update($data);
            }
        }
        if($res){
            return jsonMsg('success',0);
        }
    }
    //查询课程   没改产品相关
    public function searchCourse()
    {
        $searchWhere = Session::get('searchWhere');
        if($searchWhere){
            $where=$searchWhere;
        }else{
            $param=input();
            $gloalSearch=$param['gloalSearch'];
            $where['name']=array('like',"%$gloalSearch%");
            $where['title']=array('like',"%$gloalSearch%");
            $where['sname']=array('like',"%$gloalSearch%");
            $where['content']=array('like',"%$gloalSearch");
            Session::set('searchWhere',$where);
        }
        if(!isset($param['p'])){
            $page=1;
        }else{
            $page=$param['p'];
        }
        $res=Db::name('video_class')->alias('v')
            ->field('v.*,t.textbook')
            ->join('guard_textbook t','t.id=v.edition_id','left')
            ->whereOr($where)->page($page,16)->select();
        foreach($res as $k=>$v){
            //统计一共有多少课时
            $whereLearn['kid']=$v['id'];//id为order表id
            //获取总课时数据
            $whereLearn['classhour']=array('neq','0:00');
            $IndexModel=new IndexModel();
            $res[$k]['countClassChapter']=$IndexModel->countClass($whereLearn);
        }
        $page=Fpage($page,count($res),16);
        $this->assign('res',$res);
        $this->assign('where',$where);
        $this->assign('page',$page);
        return $this->fetch('/index/course/searchCourse');
    }
    /*
    *手机端全局课程搜索
     */
    public function web_search(){
        $param = input();
        //获取分页参数
        $page = isset($param['page'])?$param['page']:1;
        $search_name = isset($param['name']) && !empty($param['name'])?$param['name']:'语文';
        $search_name = trim($search_name);
        $data_val = isset($param['data_val']) && !empty($param['data_val'])?$param['data_val']:'1';
        //拼接查询条件
        if(!empty($search_name)){
            $c1_where = '(name like "%'.$search_name.'") or (title like "%'.$search_name.'%") or (content like "%'.$search_name.'%")';
            $c2_where = 'outline like "%'.$search_name.'%"';
            $c3_where = '(k.k_name like "%'.$search_name.'%") or (k.k_content like "%'.$search_name.'%")';
        }
        //查询video_class符合条件的数据
        $video = $video2 = array();
        $video = Db::name('video_class')->field('id as aid') ->where($c1_where) ->select();
        $video2 = Db::name('product')->field('id') ->where($c1_where) ->select();
        $product_info_id = array_column($video2,'id');
        $video2_info = Db::name('product_info')
            ->alias('p')
            ->join('video v','v.id=p.video_id')
            ->field('v.id')
            ->where('p.product_id','in',$product_info_id)
            ->where(['display' => 1])->where('part = 2')
            ->select();

        $id_arr = [];
        foreach($video as $val){
            $id_arr[] = $val['aid'];
        }
        //查找video表中的数据
        $video1_id = Db::name('video') ->field('id') ->where('kid', 'in', $id_arr) ->where(['display' => 1])->where('part = 2') ->select();
        $video1_id = array_merge($video1_id,$video2_info);
        $video2_id = Db::name('video') ->field('id') ->where($c2_where) ->where(['display' => 1])->where('part = 2') ->select();
        //去重、后统计视频个数
        $count1 = array_map(function($v){
                $data = [];
                if(!empty($v)){
                    foreach($v as $val){
                        $data[] = $val['id'];
                    }
                }
                return $data;
            },[$video1_id,$video2_id]);
        $count_1 = count(array_merge($count1[0],$count1[1]));
        //统计知识点个数
        $count2 = Db::name('knowledge') ->alias('k') ->field('count(*) as counts') ->where($c3_where)->order('k.sort asc,k.start_time asc') ->find();
        //组装数组
        $counts = [$count_1+$count2['counts'], $count_1, $count2['counts']];
        //根据对应条件查询
        switch($data_val){
            //全部
            case '1':
                $list1 = Db::name('video') ->field('kid,id,testclass,outline,img,part')
                                           ->where('id', 'in', implode(',', array_merge($count1[0],$count1[1])))
                                           ->where(['display' => 1])
                                           ->select();
                foreach($list1 as $k =>$v){
                    if($v['part'] == 2){
                        $list1[$k]['k_name'] = $v['testclass'].'--'.$v['outline'];
                    }else{
                        unset($list1[$k]);
                    }
                }
                $list2 = Db::name('knowledge') ->field('k.start_time,k.k_name,k.end_time,v.id,v.kid,v.part,v.testclass,v.outline,v.img')
                                               ->alias('k')
                                               ->join('video v','k.s_id = v.id','LEFT')
                                               ->where(['v.display' => 1])
                                               ->where($c3_where)
                                               ->order('k.sort asc,k.start_time asc')
                                               ->select();
                //转换时间格式
                foreach ($list2 as $key => $value) {
                    //判断时间格式
                    if(strstr($value['start_time'],':')){
                        //转换时间格式
                        $list2[$key]['start_time'] = $this->changeStartTime($value['start_time']);
                    }elseif(strstr($value['start_time'],'：')){

                        $start_time = str_replace('：',':',$value['start_time']);
                        $list2[$key]['start_time'] = $this->changeStartTime($start_time);
                    }
                }
                //->fetchSql(true)->find(1);
                foreach($list2 as $key => $val){
                    if($val['part'] == 2){
                        $list2[$key]['k_name'] = $val['testclass'].'--'.$val['outline'].'--'.$val['k_name'];
                    }else{
                        unset($list2[$key]);
                    }

                }
                $list_arr = array_merge($list1, $list2);
                break;
            //课程
            case '2':
                $list_arr = Db::name('video') ->field('kid,id,testclass,outline,img,part')
                                              ->where('id', 'in', implode(',', array_merge($count1[0],$count1[1])))
                                              ->where(['display' => 1])
                                              ->select();
                foreach($list_arr as $k =>$v){
                    if($v['part'] == 2){
                        $list_arr[$k]['k_name'] = $v['testclass'].'--'.$v['outline'];
                    }else{
                        unset($list_arr[$k]);
                    }
                }
                break;
            //知识点
            case '3':
                $list2 = Db::name('knowledge') ->field('k.start_time,k.k_name,k.end_time,v.id,v.kid,v.part,v.testclass,v.outline,v.img')
                                               ->alias('k')
                                               ->join('video v','k.s_id = v.id','LEFT')
                                               ->where(['v.display' => 1])
                                               ->where($c3_where)
                                               ->order('k.sort asc,k.start_time asc')
                                               ->select();
                foreach($list2 as $key => $val){
                    if($val['part'] == 2){
                        $list2[$key]['k_name'] = $val['testclass'].'--'.$val['outline'].'--'.$val['k_name'];
                    }else{
                        unset($list2[$key]);
                    }
                    //判断时间格式
                    if(strstr($val['start_time'],':')){
                        //转换时间格式
                        $list2[$key]['start_time'] = $this->changeStartTime($val['start_time']);
                    }elseif(strstr($val['start_time'],'：')){

                        $start_time = str_replace('：',':',$val['start_time']);
                        $list2[$key]['start_time'] = $this->changeStartTime($start_time);
                    }

                }
                $list_arr = $list2;
                break;
        }
        $pagesize = 20;
        $limit = (intval($page) - 1)*$pagesize;
        $list_arr1 = [];
        for($i = $limit; $i < $limit+$pagesize; $i++){
            if(isset($list_arr[$i])){
                $list_arr1[] = $list_arr[$i];
            }
        }
        //判断是否是ajax请求
        if(Request::instance()->isAjax()){
            ajaReturn(['count' => $counts, 'list' => $list_arr1]);
        }
        return $this ->fetch('/index/course/search',['list' =>$list_arr1,'count' =>$counts,'search_name' => $search_name]);
    }
    //手机端播放页面跳转微信二维码页面
    public function goWx(){
        return $this->fetch('index/course/go_weixin');
    }
    //插入笔记
    public function addNote()
    {
        $param=input();
        $productStatus = $param['productStatus']??0;
        $user=$this ->_info;

        if(!$user){

            ajaReturn('', 1001, '请先登录呢。');
        }

        $noteText = isset($param['text']) ? $param['text'] : '';

        $video_id = isset($param['video_id']) ? $param['video_id'] : '';

        if(empty($noteText) || empty($video_id)){

            ajaReturn('', 1001, '亲、写点东西再上传哦！');
        }
        if(isset($productStatus) && $productStatus==1){
            $data = Db::name('person_collect') ->field('id')
                ->where(['uid' => $user['id'],'video_id' => $video_id,'status' => 2,'product_id'=>$param['product_id']])
                ->find();
        }else{
            $data = Db::name('person_collect') ->field('id')
                ->where(['uid' => $user['id'],'video_id' => $video_id,'status' => 2,'video_class_id'=>$param['video_class_id']])
                ->find();
        }

        if(!empty($data)){

            $res = Db::name('person_collect') ->where(['id' => $data['id']]) ->update(['noteText' => $noteText]);

        }

        if(empty($data)){

            $data['uid'] = $user['id'];

            $data['intime'] = time();
            if(isset($productStatus) && $productStatus==1){
                $data['product_id'] = $param['product_id'];
            }else{
                $data['video_class_id'] = $param['video_class_id'];
            }

            $data['startTime'] = $param['startTime'];

            $data['subject_id'] = $param['subject_id'];

            $data['status'] = 2;

            $data['noteText'] = $noteText;

            $data['video_id'] = $video_id;

            $res = Db::name('person_collect') ->insert($data);

        }

        $msg['code'] = $res ? 0 : 1001;

        $msg['msg'] = $res ? 'success' : '亲、多写点东西在上传呐';

        ajaReturn('', $msg['code'], $msg['msg']);

    }

    public function settextareaval(){
        $param = input();
        $kid = $param['kid']??0;
        $productStatus = $param['productStatus']??0;
        $user = $this ->_info;

        if(!$user){

            ajaReturn('', 0, 'success');
        }

        if($productStatus==1){
            $data = Db::name('person_collect') ->field('noteText')
                ->where(['uid' => $user['id'], 'video_id' => $param['vid'], 'status' => 2,'product_id'=>$kid])
                ->find();
        }else{
            $data = Db::name('person_collect') ->field('noteText')
                ->where(['uid' => $user['id'], 'video_id' => $param['vid'], 'status' => 2,'video_class_id'=>$kid])
                ->find();
        }

        if(empty($data)){

            ajaReturn(['test' => ''], 0, 'success');
        }

        ajaReturn(['test' => $data['noteText']], 0, 'success');
    }
    //获取本节课历史笔记资料    没改产品相关
    public function  getNoteList()
    {
        $param=input();
        $where['n.video_id']=$param['video_id'];
        $where['n.video_class_id']=$param['video_class_id'];
        $where['n.uid']=$param['person_id'];
        $where['n.subject_id']=$param['subject_id'];
        $where['n.status']=2;
        $res=Db::name('person_collect')
                     ->alias('n')
                     ->field('p.nickname,p.litpic,from_unixtime(n.intime) intime,n.noteText')
                     ->join('guard_person p','p.id=n.uid')
                     ->where($where)
                     ->order('n.intime desc')
                     ->select();
        if($res)
        {
            jsonMsg($res,'1');
        }else{
            jsonMsg('暂无数据',0);
        }
    }
    //判断视频播放权限
    public function checkVideoPlay(){
        $param = input();
        $kid = $param['kid']??0;
        $productStatus = $param['productStatus']??0;
        $video_id = isset($param['video_id']) ? $param['video_id'] : '';

        $user = $this ->_info;
        //  登录状态
        $data['is_login'] = $user ? 1 : 0;

        $audi = Db::name('video') ->field('audi,audi_time') ->where(['id' => $video_id, 'display' => 1]) ->find();
        //  是否试听
        $data['is_audi'] = $audi['audi'] == 1 ? 1 : 0;

        $is_buy = 0;

        if($user){
            if($productStatus==1){
                $is_buy = Db::name('video_log') ->field('id')
                    ->where(['person_id' => $user['id'], 'video_id' => $video_id, 'type' => 0,'expireTime'=>array('egt',time()),'product_id'=>$kid])
                    ->find();
            }else{
                $is_buy = Db::name('video_log') ->field('id')
                    ->where(['person_id' => $user['id'], 'video_id' => $video_id, 'type' => 0,'expireTime'=>array('egt',time()),'product_id'=>$kid])
                    ->find();
            }

        }
        //  是否购买
        $data['is_buy'] = $is_buy ? 1 : 0;
        $data['video_id'] = $video_id;
        // 试听时长
        $data['audi_time'] = $audi['audi_time'];
        jsonMsg('success',1,$data);
    }
    public function checkAudiVideo(){
        $param = input();
        $kid = $param['kid']??0;
        $productStatus = $param['productStatus']??0;
        $video_id = $param['videoid'];
        $info =  $this ->_info;
        if(in_array($info['id'],config('user_vip'))){
            return jsonMsg('点击确定，跳转视频播放。。',2);
        }

        if(!empty($video_id)){
            if($info){//url地址存在videoid用户登陆了并且，判断用户是否购买过本节视频与是否到期
                $wherelog['person_id']=$info['id'];
                $wherelog['video_id']=$video_id;
                if($productStatus==1){
                    $wherelog['product_id']=$kid;
                }
                if($productStatus==0){
                    $wherelog['video_class_id']=$kid;
                }
                $wherelog['expireTime']=array('egt',time());
                $res=Db::name('video_log')->where($wherelog) ->where(['type' => 0,'expireTime'=>array('egt',time())])->find();
                $audio = Db::name('video')->field('audi')->where(['id' => $video_id])->find();
                if($res){//用户购买过本课程直接查看本节课内容
                    return jsonMsg('点击确定，跳转视频播放。。',2);
                }else{
                    if(!in_array($info['id'],config('user_vip'))){
                        //用户没有激活 判断是否可以试听
                        $videoTestWhere['id'] = $param['videoid'];
                        $videoTestWhere['audi'] = 2;
                        $videoTestList = Db::name('video')->where($videoTestWhere) ->where(['display' => 1])->select();
                        // if(empty($videoTestList)){   //视频不可以试听
                        //     //跳到课程购买
                        //     return jsonMsg('<p style="font-size: 24px;">该课程不属于试听课程</p><p class="number_p audited" style="padding-top: 19px">若想继续学习,请先购买</p>',0);
                        // }else{
                            //可以试听 判断新老用户 老用户可以免费试听 新用户购买试听
                            $time = config('new_functions_online_time');
                            // if($info['addtime'] < $time) { 2019/11/29暂时注释 因购买试听未正式上线上线后放开
                                //可以试听跳到视频播放 判断试听次数 没人只能试听五次
                                $audiNum = Db::name('audition_log')->where(['person_id'=>$info['id']])->group('video_id')->count();
                                if($productStatus==1){
                                    $audiInfo = Db::name('audition_log')->where(['person_id'=>$info['id'],'video_id'=>$param['videoid'],'product_id'=>$kid])->find();
                                }elseif($productStatus==0){
                                    $audiInfo = Db::name('audition_log')->where(['person_id'=>$info['id'],'video_id'=>$param['videoid'],'video_class_id'=>$kid])->find();
                                }else{
                                    $audiInfo = Db::name('audition_log')->where(['person_id'=>$info['id'],'video_id'=>$param['videoid']])->find();
                                }
                                if(empty($audiInfo) && $info['id'] > 27){
                                    if($audiNum >= 5 && $audio['audi'] !=3){
                                        return jsonMsg('<p style="font-size: 24px;">你的试听次数用完啦！</p><p class="number_p audited" style="padding-top: 19px">若想继续学习,请先购买</p>',0);
                                    }else{
                                        return jsonMsg('每个用户能免费试听<span style="color: #0a3e82;">5</span>个视频<p class="number_p audited">您已试听<span style="color: #0a3e82;">'.$audiNum.'</span>个</p>',1);
                                    }
                                }else{
                                     return jsonMsg('每个用户能免费试听<span style="color: #0a3e82;">5</span>个视频<p class="number_p audited">您已试听<span style="color: #0a3e82;">'.$audiNum.'</span>个</p>',1);
                                }
                            // }else{
                            //     //判断新用户是否购买试听课程
                            //     $orderTestInfo = Db::name('order_person_son')->where(['person_id'=>$info['id'],'is_audition'=>1,'orderCheck'=>2])->order('endtime desc')->find();
                            //     if(empty($orderTestInfo)){
                            //         //跳到试听课支付页面
                            //         return jsonMsg('您不是试听用户，点击确认跳转试听购买页面',1,'/index/course_pay/coursePay',$videoTestList[0]['kid']);
                            //     }else{
                            //          //判断试听 期限210天
                            //         $timeExpire = strtotime($orderTestInfo['endtime'])+3600 * 24 * config('audition_day');
                            //         if($timeExpire > time()){
                            //             return jsonMsg('您已购买试听视频，点击确定继续学习！',1);
                            //         }else{
                            //             //跳到试听课支付页面
                            //             return jsonMsg('您的试听时间已到期，如要试听，请重新购买',1,'/index/course_pay/coursePay',$videoTestList[0]['kid']);
                            //         }
                            //     }
                            // }
                        //}
                    }else{
                        return jsonMsg('点击确定，跳转视频播放。。',2);
                    }
                }
            }
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
        $this->assign('res',$res);
        return $this->fetch('index/course/audio');
    }
}
