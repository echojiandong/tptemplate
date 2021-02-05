<?php
namespace app\index\model;
use app\index\model\courseModel;
use think\Db;
use think\Session;
class PersonModel
{
    private $PersonDb;
    private $person_id;
    private $_info;
    public function __construct($_info = [])
    {
        $this->PersonDb = Db::name('person');

        $this ->_info = $_info;

        if($_info)
        {
            $this ->person_id = $_info['id'];
        }
    }
    /**
     * [GetUserClass description]
     * @author 薛少鹏 xsp15135921754@163.com
     * @DateTime 2019-04-13T17:04:14+0800
     * @param    integer                   $class_id    [年级]
     * @param    integer                  $subject_id  [课程id]
     * @param    integer                  $semester_id [上下册]
     */
    public function GetUserClass($class_id,$subject_id = 1,$semester_id = 1){
        //查询用户购买的subject_id
        $subject_arr = Db::name('video_log') ->field('id,subject_id,grade_id,semester')
                                             ->where(['person_id' => $this ->person_id, 'grade_id' => $class_id, 'subject_id' => $subject_id, 'semester' => $semester_id])
                                             ->where('expireTime','>',time())
                                             ->where(['type' => 0])
                                             ->select();
        //查询class_id课程列表
        $list = Db::name('video_class') ->field('id,grade_id,subject_id,Semester')
                                        ->where(['grade_id' => $class_id, 'subject_id' => $subject_id, 'semester' => $semester_id])
                                        ->order('sort')
                                        ->find();        
        //空数据时默认返回
        if(empty($list)){
            return ['data_arr' => [], 'counts' => 0];
        }
        if(!empty($list)){

            $list['person_id'] = $this ->_info['id'];
            //获取视频总节数
            $count_video = Db::name('video') ->field('count(1) as counts') ->where("kid = ".$list['id']." and part = 2") ->where(['display' => 1]) ->find();
            //获取组装数据列表
            $data_arr = array_map('GetSubjectList',[$list]);
            return ['data_arr' => $data_arr, 'counts' => $count_video['counts']];
        }
    }
    /**
     * [getSubjectUser 获取科目并且高亮显示]
     * @author 薛少鹏 xsp15135921754@163.com
     * @DateTime 2019-04-15T09:26:30+0800
     * @param    [int]                   $class_id  全局年级id
     * @return   [array]                            
     */
    public function getSubjectUser($class_id){
        //查询用户购买的subject_id
        $subject_arr = Db::name('video_log') ->field('id,subject_id,grade_id,semester')
                                             ->where(['person_id' => $this ->person_id, 'grade_id' => $class_id])
                                             ->where('expireTime','>',time())
                                             ->where(['type' => 0])
                                             ->group('subject_id')
                                             ->select();
        //转化为一维数组
        $subject_user_buy = [];
        if(!empty($subject_arr)){
            foreach($subject_arr as $key =>$val){
                $subject_user_buy[] = $val['subject_id'];
            }
        }
        $subject_user_buy = array_unique($subject_user_buy);
        //获取课程列表
        // $subject_list = $this ->getsubject();
        #   语、数、英  新增科目时可将 limit去掉
        $subject_list = Db::name('video_class') ->field('subject_id') 
                                                ->where(['grade_id' => $class_id]) 
                                                ->group('subject_id') 
                                                ->select();
        $subject_list = Db::name('subject') ->field('id,subject') 
                                            ->where('id', 'in', array_column($subject_list, 'subject_id'))
                                            ->select();
        foreach($subject_list as $key => $val){
            $subject_list[$key]['is_buy'] = in_array($val['id'],$subject_user_buy)?1:0;

            $subject_list[$key]['is_buy'] = in_array($this ->person_id, config('user_vip')) ? 1 : $subject_list[$key]['is_buy'];
        }
        return $subject_list;
    }
    /**
     * [getSemeStatus 年级对应课程上下册的高亮显示]
     * @author 薛少鹏 xsp15135921754@163.com
     * @DateTime 2019-04-15T13:54:50+0800
     * @param    [int]                   $class_id   年级id
     * @param    integer                  $subject_id 科目id 默认语文
     * @return   [array]                               
     */
    public function getSemeStatus($class_id, $subject_id = 1,$seme_id = 1){
        $res = [];
        if($seme_id == 3){

            $res[] = Db::name('video_class') ->where(['grade_id' => $class_id, 'subject_id' => $subject_id, 'Semester' =>  3])  
                                             ->field('id')
                                             ->find();
        }else{
                //年级 对应课程 上册
            $res[] = Db::name('video_class') ->where(['grade_id' => $class_id, 'subject_id' => $subject_id, 'Semester' =>  1])
                                             ->field('id')
                                             ->find();
            //年级对应课程下册
            $res[] = Db::name('video_class') ->where(['grade_id' => $class_id, 'subject_id' => $subject_id, 'Semester' => 2])
                                             ->field('id')
                                             ->find();
        }
        $data = array_map(function($v){
                    if(!empty($v)){
                        $person_msg = Db::name('video_log') -> field('id') ->where(['video_class_id' => $v['id'], 'person_id' =>$this ->person_id]) ->where('expireTime','>',time()) ->where(['type' => 0]) ->find();
                    }else{
                        $person_msg = [];
                    }

                    $person_msg = in_array($this ->person_id, config('user_vip')) ? [1] : $person_msg;

                    return empty($person_msg)?0:1;
                },$res);
        return $data;
    }
    //获取我的课程学习进度
    public function getMystudyClass($class_id){
        $user = $this ->_info;
        if($user){
            //获取用户购买的课程
            $CourseList = Db::name('order_person_son')->alias('ps')
                            ->join('guard_video_class vc','vc.id = ps.video_class_id')
                            ->field("vc.*")
                            ->where(['vc.grade_id'=>$class_id,'is_audition'=>0,'person_id'=>$user['id']])
                            ->select();
            //获取用户已激活的课程
            $classList = Db::name('video_log') ->where(['grade_id'=>$class_id,'person_id'=>$user['id']])
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
            $courseModel=new courseModel();
            $where['kid']=$v['id'];
            $whereLearn['classhour']=array('neq','0:00');
            $CourseList[$k]['countClassChapter']=$courseModel->countClassChapter($where);
            // if($user)
            // {
            //     if(in_array($user['id'],config('user_vip')))
            //     {
            //         $m = 1;
            //     }else{
            //         $m = 0;
            //     }
            // }else{
            //     $m = 0;
            // } 
            $m = 0;       
            $studyTime = 0;
            if(!empty($user)){
                // $CourseList[$k]['img'] = $v['imgNo'];
                foreach ($classList as $key => $value){
                    if($value['video_class_id'] == $v['id']){
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
                        if($value['type'] == 0 && $value['expireTime'] > time()){
                            // 计算剩余天数
                            $minute = $value['expireTime'] - time();
                            $CourseList[$k]['is_activate'] = 1;
                            $residue_day = $minute/(3600*24);
                            $CourseList[$k]['expireTime'] = $residue_day > 1 ? ceil($residue_day) : '0';
                        }
                        if($value['type'] == 0 && $value['expireTime'] < time()){
                            //  状态3 表示前台应展示 已过期
                            $CourseList[$k]['is_activate'] = 2;
                            $CourseList[$k]['expireTime'] = '已过期';
                        }
                    }
                }
                // if(in_array($user['id'],config('user_vip')))
                // {
                //     $CourseList[$k]['is_buy'] = 1;//超级vip
                //     $CourseList[$k]['img'] = $v['img'];//超级vip显示彩色图片
                // }
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
            //到期时间
            // $CourseList[$k]['expireTime'] = ceil(($value['expireTime'] - time()) / (3600 * 24));
        }
        return $CourseList;
    }
    //获取我的课程  推荐课程
    public function recommendClass($class_id){
        $user = $this ->_info;
        //获取未购买的课程
        //获取已购买的
        $CourseList = Db::name('order_person_son')->alias('ps')
                            ->join('guard_video_class vc','vc.id = ps.video_class_id')
                            ->field("vc.id")
                            ->where(['vc.grade_id'=>$class_id,'is_audition'=>0,'person_id'=>$user['id']])
                            ->select();
        $classList = [];

        foreach ($CourseList as $key => $value) {
            $classList[] =  $value['id'];
        }
        // $classList = implode(',',$classList);
        $noBuyClass = Db::name('video_class')
                            ->field("*")
                            ->where(['grade_id'=>$class_id,'id'=>array('not in',$classList)])
                            ->limit(0,4)
                            ->select();
        return $noBuyClass;
    }
    //获取用户信息
    public function GetPerson()
    {
        $user =  $this ->_info;
        if($user)
        {
            return  Db::name('person')->where(array('id'=>$user['id']))->find();
        }
    }
    public function getGrade(){
        return  Db::name('grade')->select();
    }
    public function gettextbook(){
        return  Db::name('textbook')->select();
    }
    public function getsubject(){
        return  Db::name('subject')->select();
    }
    public function getVideoclass(){
       return  Db::name('video_class')->limit(0,4)->select();
    }
    //获取购买过的课程信息
    public function myClassBuy($where)
    {
        return Db::name('video_class')->alias('v')
               ->join('guard_person_video pv','pv.class_id=v.id','left')
               ->where($where)
               ->group('pv.class_id')
               ->find();
    }
    //没有买过的课程的章节列表
    public function noBuyChapterList($where)
    {
        return Db::name('video')
               ->alias('v')
               ->field('v.*')
               ->join('guard_video_class c','c.id=v.kid','left')
               ->where(['v.display' => 1])
               ->where($where)
               ->select();
    }
    // 验证注册卡账号密码是否正确
    public function verifyTheRegistrationCard($where)
    {
        return Db::name('code')->where($where)->find();
    }
    //验证注册卡是否已经被激活
    public function verifyTheRegistrationCardlog($where)
    {
        $where['status']=2;
        return Db::name('code')->where($where)->find();
    }
    //更新注册卡状态
    public function updateRegistrationCardActivation($where,$data,$user_id)
    {
        $res = Db::name('person') ->where(['id' =>$data['person_id']]) ->update(['user_id' =>$user_id]);
        $res1 = Db::name('code')->where($where)->update($data);
        return $res && $res1?true:false;
    }

    /**
     * [cardactivation 个人中心激活课程列表]
     * @author 薛少鹏 xsp15135921754@163.com
     * @DateTime 2019-07-04T10:43:54+0800
     * @param    [int]                   $gid [年级]
     * @param    [int]                   $uid [用户id]
     * @return   [array]                 
     */
    public function cardactivation($gid, $uid){
        $sem_arr = [1 => '上册', 2 => '下册',3 => '全册'];
        // 查询所有年级对应科目列表
        $classlist = Db::name('video_class') ->field('subject_id,id,grade_id,Semester')
                                             ->where(['grade_id' => $gid]) 
                                             ->select();
                                            //  ,'subject_id'=>array('not in','4,5')//暂时屏蔽物理化学
        // 科目列表
        $subjectlist = Db::name('subject') ->field(true) ->select();
        // 转化为一维数组
        $sblist = array_combine(array_column($subjectlist, 'id'), array_column($subjectlist, 'subject'));

        $user_vip = config('user_vip');

        $new_subjectlist = array_map(function($v) use ($sem_arr, $sblist, $uid, $user_vip){

                    $data['id'] = $v['id'];

                    $data['name'] = $sblist[$v['subject_id']].$sem_arr[$v['Semester']];
                    // 初始赋值（不可激活）
                    $data['is_activate'] = in_array($uid, $user_vip) ? 5 : 0;

                    return $data;

                },$classlist);
        $class_id = array_column($new_subjectlist,'id');
        // 查询审核通过的正式课订单
        $order = Db::name('order_person_son') ->field('is_audition,person_id,video_class_id,type') 
                                              ->where(['person_id' => $uid])
                                              ->where(['orderCheck' => 2, 'is_audition' => 0, 'is_forbidden' => 0])
                                              ->where('video_class_id', 'in', $class_id)
                                              ->select();
        if(!empty($order)){

            $one_order = array_combine(array_column($order, 'video_class_id'),array_column($order, 'type'));

            foreach($new_subjectlist as $key =>&$val){

                // 如果课程存在订单表中（审核通过），改变可激活状态
                if(isset($one_order[$val['id']]) && !in_array($uid, $user_vip)){

                    $new_subjectlist[$key]['is_activate'] = $one_order[$val['id']] == 1 ? 2 : 1;
                }
                
            }

            unset($val);

        }
        // 查询课程激活记录
        $active_log = Db::name('video_log') ->field('person_id,video_class_id,type,expireTime') 
                                     ->where(['person_id' => $uid, 'type' => 0])
                                     ->where('video_class_id', 'in', $class_id) 
                                     ->group('video_class_id,person_id') 
                                     ->select();   
        if(!empty($active_log) && !in_array($uid, config('user_vip'))){

            foreach($new_subjectlist as $key =>&$val){

                foreach($active_log as $k =>&$v){

                    if($val['id'] == $v['video_class_id']){

                        if($v['type'] == 0 && $v['expireTime'] > time() && $val['is_activate'] == 2){
                            // 计算剩余天数
                            $minute = $v['expireTime'] - time();

                            $residue_day = $minute/(3600*24);

                            $new_subjectlist[$key]['residue_day'] = $residue_day > 1 ? '剩余'.ceil($residue_day).'天' : '即将到期';

                            $new_subjectlist[$key]['name'] .= '<span>(已激活)</span>';
                        }
                        //  注： 可以重复购买课程时 ，这里判断可以去掉
                        if($v['type'] == 0 && $v['expireTime'] < time() && $val['is_activate'] == 2){
                            //  状态3 表示前台应展示 已过期
                            $new_subjectlist[$key]['is_activate'] = 3;

                            $new_subjectlist[$key]['name'] .= '(已过期)';
                        }

                    }

                }
                if($val['is_activate'] == 1){

                    $new_subjectlist[$key]['name'] .= '<span>(已购买)</span>';
                }

                // if($val['is_activate'] == 0){

                //     $new_subjectlist[$key]['name'] .= '(未购买)';
                // }

            }

        }     

        // 没有激活记录
        if(empty($active_log) && !in_array($uid, config('user_vip'))){
            foreach($new_subjectlist as $key =>&$val){

                if($val['is_activate'] == 1){

                    $new_subjectlist[$key]['name'] .= '<span>(已购买)</span>';
                }
                //  前台显示  ：  例 （语文上册）
                // if($val['is_activate'] == 0){

                //     $new_subjectlist[$key]['name'] .= '(未购买)';
                // }

            }
        }                            

        return $new_subjectlist;
    }
    /**
     * [activeCourses 激活课程（单个）]
     * @author 薛少鹏 xsp15135921754@163.com
     * @DateTime 2019-07-04T13:23:55+0800
     * @param    [int]                   $cid [课程对应id]
     * @param    [int]                   $uid [用户id]
     * @return   [int]                  
     */
    public function activeCourses($cid, $uid){

        $where = ['person_id' => $uid, 'is_audition' => 0, 'video_class_id' => $cid, 'orderCheck' => 2];
        // 判断订单是否存在
        $is_true = Db::name('order_person_son') ->field('type') 
                                                ->where($where) 
                                                ->where('type', 'in', [0,-1])
                                                ->find();
        if(empty($is_true)){

            ajaReturn('', 1001, '非法操作');
        }
        // 状态禁用（恢复到期时间）
        if($is_true['type'] == -1){

            Db::transaction(function() use ($cid, $uid, $where){
                // 查询课程剩余秒数
                $surplus_time = Db::name('video_log') ->field('expireTime') 
                                                      ->where(['person_id' => $uid, 'video_class_id' => $cid])
                                                      ->find();
                // // 秒数不为 负(需要时 可加)
                // if($surplus_time['expireTime'] > 0){
                    // 课程到期时间
                    $expiretime = time() + (int)$surplus_time['expireTime'];

                    Db::name('video_log') ->where(['person_id' => $uid, 'video_class_id' => $cid]) 
                                          ->update(['expireTime' => $expiretime, 'type' => 0 ]);

                    Db::name('order_person_son') ->where($where) ->update(['type' => 1]);
                // }
            });
            // 判断状态是否修改 (因为tp5事务操作没有返回值，所以需要这一步)
            $is_upd = Db::name('order_person_son') ->field('type') 
                                                   ->where($where) 
                                                   ->where(['type' => 1])
                                                   ->find();
            return $is_upd ? 1 : 0;

        }

        // 获取主课程对应信息
        $video_msg = Db::name('video_class') ->field('id,grade_id,subject_id,Semester,effectivedays') 
                                             ->where(['id' => $cid]) 
                                             ->find();
        // 课程到期时间
        $dq_time = time() + (3600*24*190);

        if($video_msg['Semester'] == 3){
            // 全册
            $dq_time = time() + (3600*24*400);
        }

        if($video_msg['effectivedays'] != 0){
            // 后台设置课程到期时间
            $dq_time = time() + (3600*24*$video_msg['effectivedays']);
        }
        // 查询是否是续费操作
        $is_renew = Db::name('order_person_son') ->field('id') 
                                                 ->where(['person_id' => $uid, 'is_audition' => 0, 'video_class_id' => $cid, 'orderCheck' => 4]) 
                                                 ->find();
        if(!empty($is_renew)){

            Db::transaction(function() use ($cid, $uid, $where,$dq_time){
                // 查询课程剩余秒数
                $surplus_time = Db::name('video_log') ->field('expireTime') 
                                                      ->where(['person_id' => $uid, 'video_class_id' => $cid])
                                                      ->find();
                // 秒数不为 负
                if($surplus_time['expireTime'] > time()){
                    // 课程到期时间
                    $expiretime = (int)$surplus_time['expireTime'] + $dq_time;
                }

                if($surplus_time['expireTime'] < time()){
                    // 课程到期时间
                    $expiretime = time() + $dq_time;
                }

                Db::name('video_log') ->where(['person_id' => $uid, 'video_class_id' => $cid]) 
                                      ->update(['expireTime' => $expiretime]);

                Db::name('order_person_son') ->where($where) ->update(['type' => 1]);

            });
            // 判断状态是否修改 (因为tp5事务操作没有返回值，所以需要这一步)
            $is_upd = Db::name('order_person_son') ->field('type') 
                                                   ->where($where) 
                                                   ->where(['type' => 1])
                                                   ->find();
            return $is_upd ? 1 : 0;

        }
        // 查询是否购买试听课
        $where['is_audition'] = 1;

        $is_buyAudition = Db::name('order_person_son') ->field('type') 
                                                       ->where($where) 
                                                       ->where(['type' => 1])
                                                       ->find();

        $condition = ['part' => 2];
        // 初始赋值
        $surplus_time1 = 1;
        // 修改试听课程的到期时间
        if(!empty($is_buyAudition)){

            $condition['audi'] = 2;

            $surplus_time1 = Db::name('video_log') ->where(['person_id' => $uid, 'video_class_id' => $cid]) 
                                                   ->update(['expireTime' => $dq_time]);
        }

        //获取对应的 视频信息
        $video_id = Db::name('video') ->where(['kid' => $cid])
                                      ->where('pid','<>',0)
                                      ->where($condition)
                                      ->where(['display' => 1])
                                      ->field('id as video_id,kid,classhour')
                                      ->select();
        //数据组装
        $data = [];
        foreach($video_id as $k =>$v){
            // 用户id
            $v['person_id'] = $uid;
            // 年级
            $v['grade_id'] = $video_msg['grade_id'];
            // 科目
            $v['subject_id'] = $video_msg['subject_id'];
            // 学期
            $v['semester'] = $video_msg['Semester'];
            // 视频总时长
            $v['video_time'] = $v['classhour'];
            // 激活时间
            $v['intime'] = time();
            // 课程到期时间
            $v['expireTime'] = $dq_time;
            // class  id
            $v['video_class_id'] = $v['kid'];

            unset($v['kid']);

            unset($v['classhour']);

            $data[] = $v;
        }
        //批量插入
        $log_msg = [];

        $is_add = 1;
        while(!empty($data)){

            $log_msg[] = array_shift($data);
            //批量插入 100条
            if(count($log_msg) == 100){

                $is_add = Db::name('video_log') ->insertAll($log_msg);
            }
        }

        $is_add1 = 1;
        //不够100条时，将剩余数据压入数据库
        if(!empty($log_msg)){

                $is_add1 = Db::name('video_log') ->insertAll($log_msg);
        }

        $is_upd1 = Db::name('order_person_son') ->where(['person_id' => $uid, 'is_audition' => 0, 'video_class_id' => $cid]) ->update(['type' => 1]);

        return $surplus_time1 && $is_add && $is_add1 && $is_upd1 ? 1 : 0;
    }
    //获取用户年级
    public function getUserGrade()
    {
        $user = $this ->_info;
        if($user){
            //获取用户已激活的课程
            $where['person_id'] = $user['id'];
            $where['expireTime'] = array('egt',time());
            $grade = Db::name('video_log') ->field('grade_id')->where($where)
                                               ->group('grade_id')
                                               ->order('grade_id asc')
                                               ->limit(0,1)
                                               ->find();
            if(!$grade)
            {
                //如果用户部存在已经激活的课程查找是否存在已购买未激活的课程
                $whereClass['person_id'] = $user['id'];
                $whereClass['orderCheck'] = 2;
                $whereClass['kcdqtime'] = array('egt',time());
                $whereClass['is_audition'] = 0;
                $grade = Db::name('order_person_son')->alias('ps')
                            ->join('guard_video_class vc','vc.id = ps.video_class_id')
                            ->field("vc.grade_id")
                            ->where($whereClass)
                            ->order('vc.grade_id asc')
                            ->limit(0,1)
                            ->select();
                if(!$grade)
                {
                    //如果当前用户没有订单
                    $grade = 7;
                }else{
                    $grade = $grade['grade_id'];
                }
            }else{
                $grade = $grade['grade_id'];
            }
        }else{
            $grade = 7;
        }
        return $grade;
    }
    //获取用户是否激活过课程
    public function getClassList($class_id)
    {
        $user = $this ->_info;
        $where['person_id'] = $user['id'];
        $where['expireTime'] = array('egt',time());
        $where['grade_id'] = $class_id;
        $where['type']=0;
        if($user){
            //获取用户已激活的课程
            $classList = Db::name('video_log') ->where($where)
                                               ->order('uptime')
                                               ->select();
        }else{
            $classList = '';
        }
        return $classList;
    }
}