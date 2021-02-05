<?php
namespace app\index\controller;
use app\index\model\PersonModel;
use app\index\model\IndexModel;
use app\index\model\courseModel;
use app\manage\model\QiniuModel;
use think\Controller;
use think\Request;
use think\Session;
use think\Cookie;
use think\Db;
class Index extends Communal
{
    private $person;                //用户
    protected $courseArr = array(); // 科目
    protected $subjArr = [1, 2, 3,10]; // 屏蔽刁这几个课程

    public function _initialize(){
        parent::_initialize();
        
        //头部用户信息
        $this->person = new PersonModel($this ->_info);
        $this ->userinfo =  $this->person->GetPerson();
        $this->assign('personInfo',$this ->userinfo);
        $this->courseArr = Db::name('subject')->where(['id' => ['in', $this->subjArr]])->column('subject');
    }

    public function index()
    {
        //获取首页试听列表信息
        $class_id = GetGlodalClassId();
        if($class_id==null){
            $class_id='7,8,9';
        }
        $videoList = Db::name('video')
                        ->alias('v')
                        ->join('guard_video_class vc','vc.id = v.kid')
                        ->join('guard_subject s','s.id = vc.subject_id')
                        ->join('guard_grade g','g.id = vc.grade_id')
                        ->where("v.audi=1 and v.outline != '' ")
                        ->field('s.subject,v.*,vc.img as image')
                        ->where(['v.display' => 1])
                        ->where('vc.grade_id in ('.$class_id.')')
                        ->limit(0,4)
                        ->group('vc.subject_id,vc.Semester')
                        ->select();
        //获取教师列表
        $teacherList = Db::name('teacher')  ->field('t.*')
                                            ->alias('t')
                                            ->join('guard_subject s','s.id = t.subject_id')
                                            ->order('t.type desc,t.schoolid desc,t.subject_id,t.grade_id,t.sort')
                                            ->group('t.name')
                                            ->where('t.is_show = 1')
                                            ->select();
        //获取个人学习进度
        $user = $this ->_info;
        $this->assign('teacherList',$teacherList);
        $this->assign('videoList',$videoList);
        return $this->fetch("index/index/index");
    }
    //小程序 获取首页数据
    public function getIndexInfo(Request $request)
    {
        $param = $request->param();
        $pid = isset($param['openid'])?$param['openid']:'';
        $clickNum = $param['clickNum'] - 1;
        $pageNum = $clickNum*4; 
        //获取首页试听列表信息
        // $class_id='7,8,9';
        // $videoList = Db::name('video')
        //                 ->alias('v')
        //                 ->join('guard_video_class vc','vc.id = v.kid')
        //                 ->join('guard_subject s','s.id = vc.subject_id')
        //                 ->join('guard_grade g','g.id = vc.grade_id')
        //                 ->where("v.audi=1 and v.outline != '' ")
        //                 ->field('s.subject,v.*,vc.img as image')
        //                 ->where('vc.grade_id in ('.$class_id.')')
        //                 ->limit($pageNum,4)
        //                 ->group('vc.subject_id,vc.Semester')
        //                 ->select();
        // $count = Db::name('video')
        //                 ->alias('v')
        //                 ->join('guard_video_class vc','vc.id = v.kid')
        //                 ->join('guard_subject s','s.id = vc.subject_id')
        //                 ->join('guard_grade g','g.id = vc.grade_id')
        //                 ->where("v.audi=1 and v.outline != '' ")
        //                 ->field('s.subject,v.*,vc.img as image')
        //                 ->where('vc.grade_id in ('.$class_id.')')
        //                 ->group('vc.subject_id')
        //                 ->count();
        //获取教师列表
        $teacherList = Db::name('teacher') 
                            ->field('t.*')
                            ->alias('t')
                            ->join('guard_subject s','s.id = t.subject_id')
                            ->order('t.type desc,t.schoolid desc,t.subject_id,t.grade_id,t.sort')
                            ->group('t.name')
                            ->where('t.is_show = 1')
                            ->select();
        //获取个人学习进度
        // if(!empty($pid)){
        //     $user = Db::name('wx_person')->where("app_openid ='".$app_openid."'")->find();
        // }
        // if(!empty($pid)){
        //     $where['vl.person_id'] = $pid;
        //     $studyList = Db::name('video_log')->alias('vl')
        //                     ->join('guard_subject s','s.id = vl.subject_id')
        //                     ->join('guard_grade g','g.id = vl.grade_id')
        //                     ->field('vl.*,s.subject,g.grade,s.color,s.bgimg')
        //                     ->where(['vl.type' => 0])
        //                     ->where($where)
        //                     ->order('vl.uptime')
        //                     ->select();
        //     $video_class_id = Db::name('video_log')->where("person_id=".$pid)->where(['type' => 0])->group('video_class_id')->limit(0,3)->select();

        //     $videoStudyList = array();
        //     //处理空数组
        //     foreach ($video_class_id as $key => $value) {
        //         if(empty($value['video_class_id'])){
        //             unset($video_class_id[$key]);
        //         }
        //     }
        //     //学习进度数据处理
        //     foreach ($video_class_id as $k => $val) {
        //         $studyTime = 0;
        //         $m = 0;  //以学课时

        //         foreach ($studyList as $key => $value) {
        //             if(!empty($val['video_class_id'])){
        //                 if($val['video_class_id'] == $value['video_class_id']){
        //                     $videoStudyList[$k]['grade'] = $value['grade']; 
        //                     $videoStudyList[$k]['subject'] = $value['subject']; 
        //                     $videoStudyList[$k]['semester'] = $value['semester'] == 1 ? '上' : '下';
        //                     $videoStudyList[$k]['video_class_id'] = $value['video_class_id'];
        //                     $videoStudyList[$k]['person_id'] = $value['person_id'];
        //                     $videoStudyList[$k]['color'] = $value['color'];
        //                     $videoStudyList[$k]['bgimg'] = $value['bgimg'];
        //                     //获取总时间
        //                     // $video_time =explode(':', $value['video_time']);
        //                     // if(count($video_time) == 2){
        //                     //     $allTime =$allTime + $video_time['0'] * 60 + $video_time['1'];
        //                     // }elseif(count($video_time) == 3){
        //                     //     $allTime =$allTime + $video_time['0'] * 60 * 60 + $video_time['1'] * 60 + $video_time['2'];
        //                     // }
        //                     //获取学习的时间
        //                     if($value['video_status'] == 2 || $value['video_status']==3){
                                
        //                         $study_time =explode(':', $value['study_time']);
                                
        //                         if($value['video_status'] == 2){
        //                             if(count($study_time) == 2){
        //                                 $studyTime =$studyTime + $study_time['0'] * 60 + $study_time['1'];
        //                             }elseif(count($study_time) == 3){
        //                                 $studyTime =$studyTime + $study_time['0'] * 60 * 60 + $study_time['1'] * 60 + $study_time['2'];
        //                             }
        //                             $videoStudyList[$k]['video_id'] = $value['video_id'];
        //                             $videoStudyList[$k]['study_time'] = $value['study_time'];
        //                         }elseif($value['video_status']==3){
        //                             if(count($study_time) == 2){
        //                                 $studyTime =$studyTime + ($study_time['0'] * 60 + $study_time['1']) * $value['study_num'];
        //                             }elseif(count($study_time) == 3){
        //                                 $studyTime =$studyTime + ($study_time['0'] * 60 * 60 + $study_time['1'] * 60 + $study_time['2']) * $value['study_num'];
        //                             }
        //                         }
        //                         $m++;
        //                         //获取学习中的视频的信息
        //                         if($value['video_status'] == 2){
        //                             $videoStudyList[$k]['video_id'] = $value['video_id'];
        //                             //转换
        //                             $haveStudy_time =explode(':', $value['study_time']);
        //                             $haveStudyTime = 0;
        //                             if(count($study_time) == 2){
        //                                 $haveStudyTime =$haveStudyTime + $haveStudy_time['0'] * 60 + $haveStudy_time['1'];
        //                             }elseif(count($study_time) == 3){
        //                                 $haveStudyTime =$haveStudyTime + $haveStudy_time['0'] * 60 * 60 + $haveStudy_time['1'] * 60 + $haveStudy_time['2'];
        //                             }
        //                             $videoStudyList[$k]['study_time'] = $haveStudyTime;
        //                         }
        //                     }else{
        //                         $videoStudyList[$k]['video_id'] = '';
        //                         $videoStudyList[$k]['study_time'] ='';
        //                     }
        //                 }
        //             }
        //         }
        //         //拼接
        //         $courseModel=new courseModel();
        //         //统计课程的章节数目
        //         $courseWhere['kid'] = $val['video_class_id'];
        //         $allPeriod=$courseModel->countClassChapter($courseWhere);
        //         $allTime=$courseModel->classHour($val['video_class_id']);
        //         $classAlltime = array();
        //         $classAlltime[0] = str_pad(floor($allTime/3600),2,0,STR_PAD_LEFT);
        //         $classAlltime[1] = str_pad(floor(($allTime%3600)/60),2,0,STR_PAD_LEFT);
        //         $classAlltime[2] = str_pad(($allTime-$classAlltime[0] * 3600 - $classAlltime[1] * 60),2,0,STR_PAD_LEFT);
        //         if($classAlltime[0] == '00'){
        //             unset($classAlltime[0]);
        //         }
        //         $videoStudyList[$k]['allTime'] = implode(':',$classAlltime);
        //         $classStudytime = array();
        //         $classStudytime[0] = str_pad(floor($studyTime/3600),2,0,STR_PAD_LEFT);
        //         $classStudytime[1] = str_pad(floor(($studyTime%3600)/60),2,0,STR_PAD_LEFT);
        //         $classStudytime[2] = str_pad(($studyTime-$classStudytime[0] * 3600 - $classStudytime[1] * 60),2,0,STR_PAD_LEFT);
        //         if($classStudytime[0] == '00'){
        //             unset($classStudytime[0]);
        //         }
        //         $videoStudyList[$k]['studyTime'] = implode(':',$classStudytime);
        //         $videoStudyList[$k]['allPeriod'] = $allPeriod;
        //         $videoStudyList[$k]['studyPeriod'] = $m;
        //         $videoStudyList[$k]['intro'] = $videoStudyList[$k]['grade'].$videoStudyList[$k]['subject'].$videoStudyList[$k]['semester'];
                
        //     }
        // }else{
        //     $videoStudyList=array();
        // }
        $res = array();
        // $res['videoStudyList'] = $videoStudyList;
        // $res['videoList'] = $videoList;
        $res['teacherList'] = $teacherList;
        // $res['count'] = $count;
        
        return jsonMsg('success',1,$res);
    }
    //用户没有选版本前页面的数据
    public function indexVideo()
    {
    	$indexModel=new indexModel();
    	$indexVideo =$indexModel->indexVideo();
    	return jsonMsg('获取成功','1',$indexVideo);
    }
    //根据用户选择获取版本课程
    public function indexTextVideo()
    {
    	$indexModel=new indexModel();
    	$indexTextVideo =$indexModel->indexTextVideo();
        //统计课时
        foreach($indexTextVideo as $k=>$v){
            $classhour=Db::name('video')->field('classhour')->where(array('kid'=>$v['id'])) ->where(['display' => 1])->select();
            $b=0;
            if(isset($classhour)){
                foreach($classhour as $val){
                    if(!empty($val['classhour'])){
                        $a=explode(':', $val['classhour']);
                        if(isset($a[1])){
                            $b+=$a[0]*60+$a[1];
                        }else{
                            $a=explode(':', $val['classhour']);
                            $b+=$a[0]*60+$a[1];
                        }
                    }else{
                        continue;
                    }
                }
                $indexTextVideo[$k]['min']=intval(floor(  $b/60));//向下取整数
                $indexTextVideo[$k]['sec']=$b%60;//取余
            }else{
                $indexTextVideo[$k]['min']=0;//向下取整数
                $indexTextVideo[$k]['sec']=0;//取余
            } 
        }
    	return jsonMsg('获取成功','1',$indexTextVideo);
    }
    //免费领取试听课程
    public function receiveClass(Request $request)
    {
    	$IndexModel=new IndexModel();
    	$IndexModelVerification=$IndexModel->Verification();

    	if($IndexModelVerification)
    	{
    		$param=input();
	        $data['phone']=$param['phone'];
	        $data['status']=2;
	        $data['free_time']=time()+259200;
	        $data['addtime']=time();
	        $where['phone']=$param['phone'];
	        //判断用户是否存在
	        $res=Db::name('person')->where($where)->find();
	        if($res){
	            //用户存在
	            if($res['status']==2){
	                //用户领取过免费试听
	                if($res['free_time']>time()){
	                    //用户试听时间已过，不能重复领取
	                    jsonMsg('试听时间已过，不能重复领取!',1);
	                }else{
	                    //用户已经领取过科直接进行试听
	                    jsonMsg('已经领取过科直接进行试听!',2);
	                }
	            }else{
	                //用户存在没有领取过免费试听
	                $rts=$IndexModel->freeTrialUpdate($data);
	                if($rts){
	                	Session::set('user',$where);
	                	jsonMsg('领取成功!登陆账号为手机号',3,'/index/person/buyClass');
	                }else{
	                	jsonMsg('领取失败！',4);
	                }
	            }
	        }else{
	            //用户不存在生成用户账号并记录用户领取记录
	           $data['password']=user_md5(123456);
	           $rts=$IndexModel->freeTrial($data);
	           if($rts){
                    $user=Db::name('person')->where($where)->find();
	           		Session::set('user',$user);
                	jsonMsg('领取成功！登陆账号为手机号，密码为123456。',3,'/index/person/buyClass');
                }else{
                	jsonMsg('领取失败！',4);
                }
	        }
    	}else{
    		//验证码错误
    		jsonMsg('验证码错误！',0);
    	}
    }
    //判断用户有没有观看视频的权限
    public function personPlayVideo()
    {
        $param=input();
        $user=$this ->_info;
        if($user['status']==1){
            //用户没有领取过免费试听权限判断用户有没有购买过本课程，并判断本课程是否在有效期内
            $indexModel=new IndexModel();
            $where['person_id']=$user['id'];
            $where['video_class_id']=$param['id'];
            $res=$indexModel->personPlayVideo($where);
            if($res){
                if($res['kcdqtime']>=time()){
                    //用户购买过本课程，课程观看权限未到期
                    return jsonMsg('success',1);
                }else{
                    //用户购买过本课程但是到期了
                    return jsonMsg('error',0);
                }
            }else{
                //用户没有购买过本课程
                return jsonMsg('error',0);
            }
        }else{
            //判断用户的免费试听权限是否已经到期
            if($user['free_time']>=time()){
                //用户试听权限有效
                return jsonMsg('success',1);
            }else{
                //用户试听权限无效
                return jsonMsg('error',0);
            }
        }
    }
    //获取用户的学习进度
    public function rateOfLearning()
    {
        $user=$this ->_info;
        $IndexModel=new IndexModel();
        //获取用户购买过的课程订单
        $where['person_id']=$user['id'];
        $where['state']=2;
        $rateOfLearning=$IndexModel->rateOfLearning($where);
        foreach($rateOfLearning as $k=>$v){
            $whereLearn['kid']=$v['video_class_id'];
            $whereLearn['classhour']=array('neq','');
            //获取总课时数据
            $countClass=$IndexModel->countClass($whereLearn);
            //获取学过的课时数目
            $whereL['type']=2;
            $whereL['person_id']=$user['id'];
            $whereL['video_class_id']=$v['video_class_id'];
            $countHistory=$IndexModel->countHistoryClass($whereL);
            $rateOfLearning[$k]['countClass']=$countClass;//课程一共多少节
            $rateOfLearning[$k]['countHistory']=$countHistory;//课程学过多少节
            @$rateOfLearning[$k]['rateOfLearning']=floor(($countHistory/$countClass)*100)."%";//学过多少百分比
            if($v['semester']==1){
                $rateOfLearning[$k]['semester']='上';
            }else{
                $rateOfLearning[$k]['semester']='下';
            }
        }
        return jsonMsg('success',1,$rateOfLearning);
    }

    public function getClassList()
    {
        $param = input();
        $where['kid']=$param['id'];
        $user=$this ->_info;
        $person_id=$user['id'];
        $res=Db::name('video')->where($where) ->where(['display' => 1])->select();
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
                if($person_id){
                    $wherelog['person_id']=$person_id;
                    $wherelog['video_id']=$v['id'];
                    $res[$k]['videoLog']=Db::name('video_log')->where(['type' => 0])->where($wherelog)->find();
                }else{
                    $res[$k]['videoLog']['video_status']=1;
                    $res[$k]['videoLog']['study_num']=2;
                }
            }
        }

        $res=getTree($res);//课程章节目录给i他

        //获取老师信息
        if (isset($res[0]['teacherid']) && $res[0]['teacherid']) {
            $teacherList = Db::name('teacher')->where('id='.$res[0]['teacherid'])->find();
        } else {
            $teacherList= array();
        }

        $this->assign('courseCatalogList',$res);
        $this->assign('teacherList',$teacherList);
        $this->assign('intro',$param['intro']);
        return $this->fetch("index/index/index-course-list");
    }

    /**
     * pc全局搜索
     * @author 杨继州    
     */
    public function glodalsearch()
    {
        $param = input();
        $userinfo =Cookie::get("user");
        if($userinfo)
        {
            $userinfo=mydecrypt($userinfo,config('encrypt_key_common'));
            $userinfo=base64_decode($userinfo);
            $user=json_decode($userinfo,true);//转换成json
        }else{
            $user='';
        }
        //获取分页参数
        $page = isset($param['page'])?$param['page']:1;
        $search_name = isset($param['gloalSearch']) && !empty($param['gloalSearch'])?$param['gloalSearch']:'语文';
        $search_name = preg_replace('# #', '', trim($search_name));  // 搜索关键词
        $data_val = isset($param['data_val']) && !empty($param['data_val'])?$param['data_val']:'1';

        // 判断是否是科目, 存在查询课程
        if (in_array($search_name, $this->courseArr)) {
            $subject = $this->getSubject($search_name);
        }

        $where = array();
        $where1 = array();

        // 收藏条件
        $where2['status'] = 1; 
        // 课程章节中存在关键词
        if (isset($search_name) && !empty($search_name)) {
            $where['outline'] = ['like', '%'.$search_name.'%'];
            $where['part'] = 2;
            // 知识点
            $where1['k_name|k_content'] = ['like', '%'.$search_name.'%'];
            $where2['a.ctitle'] = ['like', '%'.$search_name.'%'];

        }
        if ($user && !empty($user['id'])) {
            $where2['a.uid'] = $user['id'];
        }
        $subjectCount = isset($subject) ? count($subject) : 0;
        $chapterCount = count($this->getChapter($where));
        $chapterCount = $subjectCount + $chapterCount;
        $knowledgeCount = count($this->getKnowledge($where1));
        $collectCount = count($this->getCollect($where2));
        
        $totalCount = $chapterCount + $knowledgeCount + $collectCount;

        switch ($data_val) {
            case '1':
                $subject = $this->getSubject($search_name);
                $videoChapter = $this->getChapter($where);
                $knowledge = $this->getKnowledge($where1);
                $collect = $this->getCollect($where2);
                $list = array_merge($subject, $videoChapter,$knowledge, $collect);
                break;
            case '2':
                // 章节
                $subject = $this->getSubject($search_name);
                $videoChapter = $this->getChapter($where);
                $list = array_merge($subject, $videoChapter);
                
                break;
            case '3':
                // 知识点
                $knowledge = $this->getKnowledge($where1);
                $list = $knowledge;
                break;
            case '4':
                // 收藏
                $collect = $this->getCollect($where2);
                $list = $collect;
                break;
            default:
                break;
        }

        // list 结果集中type字段说明  ： 1课程， 2章节， 3知识点， 4收藏

        // dump($totalCount);
        
        $pagesize = 20;
        $limit = (intval($page) - 1)*$pagesize;
        $list_arr1 = [];
        for($i = $limit; $i < $limit+$pagesize; $i++){
            if(isset($list[$i])){
                $list_arr1[] = $list[$i];
            }
        }
        $pages=Fpage($page,count($list)-1,$pagesize);
        $count = [
            '0' => $totalCount ?? 0,
            '1'    => $chapterCount ?? 0,
            '2'  =>$knowledgeCount ?? 0,
            '3'    => $collectCount ?? 0
        ];

        //判断是否是ajax请求
        if(Request::instance()->isAjax()){
            ajaReturn(['count' => $count, 'list' => $list_arr1, 'pagesize' => $pages]);
        }

        $is_login = $user ? 1 : 0;

        $this ->assign('is_login',$is_login);
        return $this ->fetch('index/search/search',['list' =>$list_arr1,'count' =>$count,'search_name' => $search_name, 'pagesize' => $pages]);


    }
    /**
     * [glodalsearch 首页全局搜索]
     * @author 薛少鹏 xsp15135921754@163.com
     * @DateTime 2019-04-23T09:01:28+0800
     * @return   
     */
    // public function glodalsearch()
    // {
    //     $param = input();

    //     $user = Session::get('user');
    //     //获取分页参数
    //     $page = isset($param['page'])?$param['page']:1;
    //     $search_name = isset($param['gloalSearch']) && !empty($param['gloalSearch'])?$param['gloalSearch']:'语文';
    //     $search_name = trim($search_name);
    //     $data_val = isset($param['data_val']) && !empty($param['data_val'])?$param['data_val']:'1';
    //     //拼接查询条件
    //     if(!empty($search_name)){
    //         $c1_where = '(name like "%'.$search_name.'" and subject_id in (1,2,3)) or (title like "%'.$search_name.'%" and subject_id in (1,2,3)) or (content like "%'.$search_name.'%" and subject_id in (1,2,3))';            
    //         $c2_where = 'outline like "%'.$search_name.'%"';
    //         $c3_where = '(k.k_name like "%'.$search_name.'%") or (k.k_content like "%'.$search_name.'%")';
    //     }
    //     //查询video_class符合条件的数据
    //     $video = Db::name('video_class')->field('id as aid, subject_id') ->where($c1_where)->select();
    //     $id_arr = [];
    //     foreach($video as $val){
    //         $id_arr[] = $val['aid']; 
    //     }
       
    //     //查找video表中的数据
    //     $video1_id = Db::name('video')->field('id') ->where(['display' => 1])->where('part = 2') ->where('kid', 'in', $id_arr) ->select();

    //     $video2_id = Db::name('video') ->field('id') ->where($c2_where)->where(['display' => 1]) ->where('part = 2') ->select();
        
    //     //去重、后统计视频个数
    //     $count1 = array_map(function($v){
    //             $data = [];
    //             if(!empty($v)){
    //                 foreach($v as $val){
    //                     $data[] = $val['id'];
    //                 }
    //             }
    //             return $data;
    //         },[$video1_id,$video2_id]);
    //     $count_1 = count(array_merge($count1[0],$count1[1]));
    //     //统计知识点个数
    //     $count2 = Db::name('knowledge') ->alias('k') ->field('count(1) as counts') ->where($c3_where) ->find();
    //     //  我的收藏
    //     if($user){
    //         $c4_where = 'ctitle like "%'.$search_name.'%" and uid = '.$user['id'].' and status = 1';

    //         $count3 = Db::name('person_collect') ->field('count(1) as counts') ->where($c4_where) ->find();
    //         //组装数组
    //         $counts = [$count_1+$count2['counts']+$count3['counts'], $count_1, $count2['counts'],$count3['counts']];
    //     }else{
    //         //组装数组
    //         $counts = [$count_1+$count2['counts'], $count_1, $count2['counts']];
    //     }
        
    //     //根据对应条件查询
    //     switch($data_val){
    //         //全部
    //         case '1':
    //             $list1 = Db::name('video') ->field('kid,id,testclass,outline,img,part,audi') 
    //                                        ->where('id', 'in', implode(',', array_merge($count1[0],$count1[1])))
    //                                        ->where(['display' => 1]) 
    //                                        ->select();
    //             foreach($list1 as $k =>$v){
    //                 if($v['part'] == 2){
    //                     $list1[$k]['k_name'] = $v['testclass'].'--'.$v['outline'];
    //                 }else{
    //                     unset($list1[$k]);
    //                 }
    //             }
    //             $list2 = Db::name('knowledge') ->field('k.start_time,k.k_name,k.end_time,v.id,v.kid,v.part,v.testclass,v.outline,v.img,v.audi') 
    //                                            ->alias('k') 
    //                                            ->join('video v','k.s_id = v.id','LEFT')
    //                                            ->where(['v.display' => 1]) 
    //                                            ->where($c3_where) 
    //                                            ->select();
    //             //->fetchSql(true)->find(1);
    //             foreach($list2 as $key => $val){
    //                 if($val['part'] == 2){
    //                     $list2[$key]['k_name'] = $val['testclass'].'--'.$val['outline'].'--'.$val['k_name'];
    //                 }else{
    //                     unset($list2[$key]);
    //                 }
    //                 //判断时间格式
    //                 if(strstr($val['start_time'],':')){
    //                     //转换时间格式
    //                     $list2[$key]['start_time'] = $this->changeStartTime($val['start_time']);
    //                 }elseif(strstr($val['start_time'],'：')){

    //                     $start_time = str_replace('：',':',$val['start_time']);
    //                     $list2[$key]['start_time'] = $this->changeStartTime($start_time);
    //                 }
                    
    //             }
    //             if($user){

    //                 $c4_where = 'ctitle like "%'.$search_name.'%" and uid = '.$user['id'].' and status = 1';

    //                 $field = 'video_id as id,startTime as start_time,collectImg as img,video_class_id as kid,ctitle as k_name';

    //                 $list3 = Db::name('person_collect') ->field($field) ->where($c4_where) ->select();

    //                 if(!empty($list3)){

    //                     $video_id = array_column($list3, 'id');

    //                     $video_arr = Db::name('video') ->field('id,outline,testclass,audi') 
    //                                                    ->where('id', 'in', $video_id) 
    //                                                    ->where(['display' => 1])
    //                                                    ->select();
    //                     $outline_arr = array_combine(array_column($video_arr, 'id'), array_column($video_arr, 'outline'));

    //                     $testclass_arr = array_combine(array_column($video_arr, 'id'), array_column($video_arr, 'testclass'));

    //                     foreach($list3 as $key =>&$val){

    //                         $list3[$key]['k_name'] = $testclass_arr[$val['id']].'-'.$outline_arr[$val['id']].'-'.$val['k_name'];
    //                     }
    //                 }
    //                 $list_arr = array_merge($list1, $list2, $list3);
    //             }else{
    //                 $list_arr = array_merge($list1, $list2);
    //             }
    //             break;
    //         //课程
    //         case '2':
    //             $list_arr = Db::name('video') ->field('kid,id,testclass,outline,img,part,audi') 
    //                                           ->where('id', 'in', implode(',', array_merge($count1[0],$count1[1]))) 
    //                                           ->where(['display' => 1])
    //                                           ->select();
    //             foreach($list_arr as $k =>$v){
    //                 if($v['part'] == 2){
    //                     $list_arr[$k]['k_name'] = $v['testclass'].'--'.$v['outline'];
    //                 }else{
    //                     unset($list_arr[$k]);
    //                 }
    //             }
    //             break;
    //         //知识点
    //         case '3':
    //             $list2 = Db::name('knowledge') ->field('k.start_time,k.k_name,k.end_time,v.id,v.kid,v.part,v.testclass,v.outline,v.img') 
    //                                            ->alias('k') 
    //                                            ->join('video v','k.s_id = v.id','LEFT') 
    //                                            ->where(['v.display' => 1])
    //                                            ->where($c3_where) 
    //                                            ->select();
    //             foreach($list2 as $key => $val){
    //                 if($val['part'] == 2){
    //                     $list2[$key]['k_name'] = $val['testclass'].'--'.$val['outline'].'--'.$val['k_name'];
    //                 }else{
    //                     unset($list2[$key]);
    //                 }
    //                 //判断时间格式
    //                 if(strstr($val['start_time'],':')){
    //                     //转换时间格式
    //                     $list2[$key]['start_time'] = $this->changeStartTime($val['start_time']);
    //                 }elseif(strstr($val['start_time'],'：')){

    //                     $start_time = str_replace('：',':',$val['start_time']);
    //                     $list2[$key]['start_time'] = $this->changeStartTime($start_time);
    //                 }
                    
    //             }
    //             $list_arr = $list2;
    //             break;
    //         //  我的收藏
    //         case '4':
    //             if($user){

    //                 $c4_where = 'ctitle like "%'.$search_name.'%" and uid = '.$user['id'].' and status = 1';

    //                 $field = 'video_id as id,startTime as start_time,collectImg as img,video_class_id as kid,ctitle as k_name';

    //                 $list3 = Db::name('person_collect') ->field($field) ->where($c4_where) ->select();

    //                 if(!empty($list3)){

    //                     $video_id = array_column($list3, 'id');

    //                     $video_arr = Db::name('video') ->field('id,outline,testclass') 
    //                                                    ->where('id', 'in', $video_id)
    //                                                    ->where(['display' => 1]) 
    //                                                    ->select();
    //                     $outline_arr = array_combine(array_column($video_arr, 'id'), array_column($video_arr, 'outline'));

    //                     $testclass_arr = array_combine(array_column($video_arr, 'id'), array_column($video_arr, 'testclass'));

    //                     foreach($list3 as $key =>&$val){

    //                         $list3[$key]['k_name'] = $testclass_arr[$val['id']].'-'.$outline_arr[$val['id']].'-'.$val['k_name'];
    //                     }
    //                 }
    //                 $list_arr = $list3;
    //             }

    //             break;
    //     }
    //     $pagesize = 20;
    //     $limit = (intval($page) - 1)*$pagesize;
    //     $list_arr1 = [];
    //     for($i = $limit; $i < $limit+$pagesize; $i++){
    //         if(isset($list_arr[$i])){
    //             $list_arr1[] = $list_arr[$i];
    //         }
    //     }
    //     $pages=Fpage($page,count($list_arr)-1,$pagesize);
    //     //判断是否是ajax请求
    //     if(Request::instance()->isAjax()){
    //         ajaReturn(['count' => $counts, 'list' => $list_arr1, 'pagesize' => $pages]);
    //     }
    //     $is_login = $user ? 1 : 0;

    //     $this ->assign('is_login',$is_login);

    //     return $this ->fetch('index/search/search',['list' =>$list_arr1,'count' =>$counts,'search_name' => $search_name, 'pagesize' => $pages]);
    // }

    public function teachMsg(){
        $param = input();
        $msg = Db::name('teacher') ->field('name,content')->where(['id' => $param['id']]) ->find();
        echo json_encode($msg);
    }

    /**
     * 关闭浏览器执行
     * @param person_id  登陆用户id
     */
    public function closeBrowserIpLog()
    {
        $param = input();
        $uid = (int) $param['uid'];
        if (!$uid) {
            return false;
        }
        // // 调用IP记录函数
        ipLog($uid);
    }
    /*
     * 试听列表切换
     */
    public function auditionlist(){

        $param = input();

        $grade_id = isset($param['v']) ? $param['v'] : 7 ;

        $data = Db::name('video')
                        ->alias('v')
                        ->join('guard_video_class vc','vc.id = v.kid')
                        ->join('guard_subject s','s.id = vc.subject_id')
                        ->join('guard_grade g','g.id = vc.grade_id')
                        ->where("v.audi=1 and v.outline != '' ")
                        ->field('s.subject,v.*,vc.img as image,vc.popularity as popu')
                        ->where(['v.display' => 1])
                        ->where(['vc.grade_id' => $grade_id])
                        ->limit(0,8)
                        ->group('vc.Semester,vc.subject_id')
                        ->select();
        ajaReturn($data, 0, 'success');
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

    // 科目
    private function getSubject($keywords)
    {
        $subjects = ['语文','数学','英语'];
        if(in_array($keywords,$subjects)){
            $videoClass = Db::name('video_class')
                ->field("
                    id as video_class_id, img, name, title,
                    case Semester
                        when 1 then '上学期'
                        when 2 then '下学期'
                        when 3 then '全册'
                    end as semester,
                    concat(name, '(', 
                        case Semester
                            when 1 then '上学期'
                            when 2 then '下学期'
                            when 3 then '全册'
                        end
                        , ')') as Titles
                ")
                ->where(['title' => $keywords])
                ->where(['subject_id' => ['in', $this->subjArr]])
                ->where('id != 23')
                ->select();
            if (!$videoClass) {
                return array();
            }

            foreach ($videoClass as $key => $value) {
                $videoClass[$key]['type'] = 1; // 课程
                $courseNum = Db::name('video')->where(['kid' => $value['video_class_id']]) ->where(['display' => 1])->count('id');
                $videoClass[$key]['courseNum'] = $courseNum;
            }
            return $videoClass;
        }else{
            $videoClass = Db::name('product')
                ->field("
                    id as video_class_id, productUrl as img, name, title,productStatus,
                    case Semester
                        when 1 then '上学期'
                        when 2 then '下学期'
                        when 3 then '全册'
                    end as semester,
                    concat(name, '(', 
                        case Semester
                            when 1 then '上学期'
                            when 2 then '下学期'
                            when 3 then '全册'
                        end
                        , ')') as Titles
                ")
                ->where(['title' => $keywords])
                ->where(['subject_id' => ['in', $this->subjArr]])
                ->select();
            if (!$videoClass) {
                return array();
            }

            foreach ($videoClass as $key => $value) {
                $videoClass[$key]['type'] = 1; // 课程
                $courseNum = Db::name('product_info')->where(['product_id' => $value['video_class_id']]) ->where(['forbiden' => 1])->count('id');
                $videoClass[$key]['courseNum'] = $courseNum;
            }
            return $videoClass;
        }
    }

    // 章节
    private function getChapter($where)
    {
        $videoChapter = Db::name('video')->alias('a')
                        ->field("a.id as video_id, a.audi, a.kid as video_class_id, a.img, a.testclass, a.outline, a.link, a.link_720, a.link_480,b.productStatus,
                            concat(b.name, '(', 
                                case b.Semester
                                    when 1 then '上学期'
                                    when 2 then '下学期'
                                    when 3 then '全册'
                                end
                            , ')') as Titles
                        ")
                        ->join('video_class b', 'a.kid = b.id')
                        ->where(['b.subject_id' => ['in', $this->subjArr]])
                        ->where($where)->select();

        $product_info = Db::name('product')
                        ->alias('a')
                        ->field("a.id,b.video_id,a.productStatus,
                            concat(a.name,case a.Semester
                                    when 1 then '上学期'
                                    when 2 then '下学期'
                                    when 3 then '全册'
                                end
                            )as Titles")
                        ->join('product_info b','a.id=b.product_id')
                        ->where(['a.subject_id' => ['in', $this->subjArr]])
                        ->select();

        $video_id_info = array_column($product_info,'video_id');
        $videoChapter2 = Db::name('video')->alias('a')
            ->field("a.id as video_id, a.audi, a.kid, a.img, a.testclass, a.outline, a.link, a.link_720, a.link_480")
            ->where(['a.id' => ['in', $video_id_info]])
            ->where($where)->select();

        foreach($product_info as $k=>$v){
            foreach ($videoChapter2 as $key => $value) {
                if($v['video_id']==$value['video_id']){
                    $videoChapter2[$key]['Titles'] = $v['Titles'];
                    $videoChapter2[$key]['productStatus'] = $v['productStatus'];
                    $videoChapter2[$key]['video_class_id'] = $v['id'];
                }
            }
        }
        if (!$videoChapter && $videoChapter2) {
            return array();
        }
        $videoChapter = array_merge($videoChapter,$videoChapter2);
        foreach ($videoChapter as $key => $value) {
            $videoChapter[$key]['type'] = 2; // 章节
        }
//        $videoChapter = assoc_unique($videoChapter,'video_id');
        return $videoChapter;
    }

    // 知识点
    private function getKnowledge($where)
    {
        $knowledge = Db::name('knowledge')->alias('a')
                    ->field("a.k_id, a.s_id as video_id, a.k_name, a.start_time as startTime, b.id, b.img, b.testclass,  b.audi, b.outline, b.link, b.link_720, b.link_480,c.productStatus,
                            concat(c.name, '(', 
                                case c.Semester
                                    when 1 then '上学期'
                                    when 2 then '下学期'
                                    when 3 then '全册'
                                end
                            , ')') as Titles, b.kid as video_class_id
                    
                    ")
                    ->where($where)
                    ->where(['b.part' => 2])
                    ->where(['b.display' => 1])
                    ->join('video b', 'a.s_id = b.id')
                    ->join('video_class c', 'b.kid = c.id')
                    ->where(['c.subject_id' => ['in', $this->subjArr]])
                    ->order('a.sort asc,a.start_time asc')
                    ->select();
        $product_info = Db::name('product')
            ->alias('a')
            ->field("a.id,b.video_id,a.productStatus,
                            concat(a.name,case a.Semester
                                    when 1 then '上学期'
                                    when 2 then '下学期'
                                    when 3 then '全册'
                                end
                            )as Titles")
            ->join('product_info b','a.id=b.product_id')
            ->where(['a.subject_id' => ['in', $this->subjArr]])
            ->select();
        $video_id_info = array_column($product_info,'video_id');
        array_push($video_id_info,1651);
        $knowledge2 = Db::name('knowledge')->alias('a')
            ->field("a.k_id, a.s_id as video_id, a.k_name, a.start_time as startTime, b.id, b.img, b.testclass,  b.audi, b.outline, b.link, b.link_720, b.link_480, b.kid
                    ")
            ->join('video b', 'a.s_id = b.id')
            ->where($where)
            ->where(['b.part' => 2,'b.display' => 1])
            ->whereIn('b.id',$video_id_info)
            ->order('a.sort asc,a.start_time asc')
            ->select();
        foreach($product_info as $k=>$v){
            foreach ($knowledge2 as $key => $value) {
                if($v['video_id']==$value['video_id']){
                    $knowledge2[$key]['Titles'] = $v['Titles'];
                    $knowledge2[$key]['productStatus'] = $v['productStatus'];
                    $knowledge2[$key]['video_class_id'] = $v['id'];
                }
            }
        }
        if (!$knowledge && !$knowledge2) {
            return array();
        }
        $knowledge = array_merge($knowledge,$knowledge2);
        foreach ($knowledge as $key => $value) {
            $knowledge[$key]['type'] = 3;
        }
//        $knowledge = assoc_unique($knowledge,'video_id');
        if (!$knowledge) {
            return array();
        }

        return $knowledge;
    }

    // 收藏
    public function getCollect($where)
    {
        $user = $this ->_info;
        $collect = Db::name('person_collect')->alias('a')
                ->field("a.video_class_id,a.product_id,a.uid, a.video_id, a.startTime, a.ctitle, a.noteText, a.collectImg as img, a.intime, b.outline, b.outline as Titles, b.audi")
                ->where($where)
                ->where(['b.display' => 1])
                ->join('guard_video b', 'a.video_id = b.id')
                ->select();
        if (!$collect) {
            return array();
        }
        foreach ($collect as $key => $value) {
            $collect[$key]['intime'] = date('Y-m-d', $value['intime']);
            $collect[$key]['type'] = 4;
        }
        return $collect;
    }
}
