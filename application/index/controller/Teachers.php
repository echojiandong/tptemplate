<?php
namespace app\index\controller;
use app\index\model\PersonModel;
use app\index\model\IndexModel;
use think\Controller;
use think\Request;
use think\Session;
use think\Cookie;
use think\Db;
use think\image;
use think\Page;
class Teachers extends Communal
{
    private $person;                //用户
    public function _initialize(){
        parent::_initialize();
        
        //头部用户信息
        $this->person = new PersonModel($this ->_info);
        $this ->userinfo =  $this->person->GetPerson();
        $this->assign('personInfo',$this ->userinfo);
    }
    //教师列表页面
    public function teachersTeam()
    {
    	$param=input();
    	if(!isset($param['p'])){
    		$page=1;
    	}else{
            $page=$param['p'];
        }
        //教研老师
    	$teacherList = Db::name('teacher') ->field('t.*')
                                            ->alias('t')
                                            ->join('guard_subject s','s.id = t.subject_id')
                                            ->order('s.id,t.grade_id,t.type desc')
                                            ->where(['t.type'=>1])
                                            ->group('t.name')
                                            ->select();
        
        $school_1 = array_unique(array_column($teacherList, 'schoolid'));
        $school_arr = Db::name('school') ->field('id,s_name') ->where('id', 'in', $school_1) ->order('id desc') ->select();

        // $grade = Db::name('grade') ->field('id,grade') ->select();

        // if(!empty($grade)){

        //     $grade_arr = array_combine(array_column($grade, 'id'),array_column($grade, 'grade'));
        // }

        foreach($school_arr as $key =>&$val){
            foreach($teacherList as &$v){

                // if(isset($grade_arr)){

                //     $v['false_cont'] = $grade_arr[$v['grade_id']].$v['false_cont'];
                // }

                if($val['id'] == $v['schoolid']){

                    $val['son'][] = $v;
                }
            }
            $data[] = $val;
        }
    	$data1 = Db::name('teacher') ->field('t.*') 
                                        ->alias('t')
                                        ->join('guard_subject s','s.id = t.subject_id')
                                        ->order('s.id,t.grade_id')
                                        ->where(['t.type'=>0])
                                        ->select();//普通讲师
        // $school_1 = array_unique(array_column($teacherList, 'schoolid'));
        // $school_arr = Db::name('school') ->field('id,s_name') ->where('id', 'in', $school_1) ->select();
        // foreach($school_arr as $key =>&$val){
        //     foreach($teacherList as &$v){
        //         if($val['id'] == $v['schoolid']){

        //             $val['son'][] = $v;
        //         }
        //     }
        //     $data1[] = $val;
        // }
        $this->assign('data',$data);
        $this->assign('data1',$data1);
        return view('index/teachers/teachersTeam');
    }
    //教师详情页面
    public function teachersInfo()
    {
        $param=input();
        $where['id']=$param['id'];
        //教师信息
        $teacherInfo=Db::name('teacher')->where($where)->find();
        $this->assign('teacherInfo',$teacherInfo);
        //教师课程
        $teacherId['v.teacherId']=$param['id'];
        $teacherCourse=Db::name('video_class')->alias('v')
                       ->field('v.*,s.subject,g.grade,t.textbook')
                       ->join('guard_subject s','s.id=v.subject_id','left')
                       ->join('guard_grade g','g.id=v.grade_id','left')
                       ->join('guard_textbook t','t.id=v.edition_id','left')
                       ->where($teacherId)->limit(0,8)->select();
        foreach($teacherCourse as $k=>$v){
            if($v['Semester']==1){
                $teacherCourse[$k]['Semester']='上学期';
            }else{
                $teacherCourse[$k]['Semester']='下学期';
            }
            //统计一共有多少课时
            $whereLearn['kid']=$v['id'];//id为order表id
            //获取总课时数据
            $whereLearn['classhour']=array('neq','0:00');
            $IndexModel=new IndexModel();
            $teacherCourse[$k]['countClass']=$IndexModel->countClass($whereLearn);
        }
        $this->assign('teacherCourse',$teacherCourse);
        //推荐课程
        $RecommendCourse=Db::name('video_class')->alias('v')
                         ->field('v.*,s.subject,s.id cssid')
                         ->join('guard_subject s','s.id=v.subject_id','left')
                         ->order('purchase desc')->limit(0,8)->select();
        //统计课时
        foreach($RecommendCourse as $k=>$v){
            $classhour=Db::name('video')->field('classhour')->where(array('kid'=>$v['id']))->where(['display' => 1])->select();
            $b=0;
            if(isset($classhour)){
                foreach($classhour as $val){
                    if(!empty($val['classhour'])){
                        $a=explode(':', $val['classhour']);
                        $b+=$a[0]*60+$a[1];
                    }
                }
                $RecommendCourse[$k]['min']=intval(floor(  $b/60));//向下取整数
                $RecommendCourse[$k]['sec']=$b%60;//取余
            }else{
                $RecommendCourse[$k]['min']=0;//向下取整数
                $RecommendCourse[$k]['sec']=0;//取余
            } 
        }
        $this->assign('RecommendCourse',$RecommendCourse);
        return view('index/teachers/teachers-introduce');
    }
    //获取教师详情
    public function getTeacherInfo(){
        $param=input();
        $teacher_id = $param['id'];
        //获取教师信息
        $teacherList  = Db::name('teacher')->alias('t')
                            ->join('guard_subject s','s.id = t.subject_id')
                            ->where('t.id='.$teacher_id)
                            ->field('s.subject,t.*')
                            ->select();

        $grade = Db::name('grade') ->field('id,grade') ->select();

        if(!empty($grade)){

            $grade_arr = array_combine(array_column($grade, 'id'),array_column($grade, 'grade'));
        }

        foreach($teacherList as $key =>$val){

            $teacherList[$key]['subject'] = $grade_arr[$val['grade_id']].$val['subject'];
            $teacherList[$key]['content'] = htmlspecialchars_decode($val['content']);
        }

        if(!empty($teacherList)){
            return jsonMsg('获取成功',1,$teacherList);
        }else{
            return jsonMsg('获取失败',0);
        }
    }

    //获取教师详情 手机端
    public function getTeacherMobileInfo(){
        $param=input();
        $teacher_id = $param['id'];
        //获取教师信息
        $teacherList  = Db::name('teacher')->alias('t')
                            ->join('guard_subject s','s.id = t.subject_id')
                            ->where('t.id='.$teacher_id)
                            ->field('s.subject,t.*')
                            ->select();
        $this->assign('teacherList',$teacherList);
        return view('index/teachers/teacherInfo');
    }
}
