<?php
namespace app\manage\model;
use think\Model;
use Phinx\Db\Table;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\Image;
class Teacher extends Model
{
    private $TeacherDb;

    public function __construct()
    {
        $this->TeacherDb = Db::name('teacher');
    }
    // 获取教师列表
    public function getTeacherList()
    {
        $param=input();
        $teacherList = Db::name("teacher")
                       ->alias('t')
                       ->field('t.*,guard_grade.grade,b.textbook,l.learn,s.subject')
                       ->join('guard_grade','guard_grade.id=t.grade_id','left')
                       ->join('guard_textbook b','b.id=t.textbook_id','left')
                       ->join('guard_learn l','l.id=t.learn','left')
                       ->join('guard_subject s','s.id=t.subject_id','left')
                       ->page($param['page'],$param['limit'])->select();
        $count = Db::name("teacher")
                       ->alias('t')
                       ->field('t.id,t.name,t.grade_id,guard_grade.grade,b.textbook,l.learn,s.subject')
                       ->join('guard_grade','guard_grade.id=t.grade_id','left')
                       ->join('guard_textbook b','b.id=t.textbook_id','left')
                       ->join('guard_learn l','l.id=t.learn','left')
                       ->join('guard_subject s','s.id=t.subject_id','left')
                       ->count();

        foreach($teacherList as $k=>$v){
            if($v['grade']==1){
                $teacherList[$k]['grade']='小学';
            }elseif($v['grade']==2){
                $teacherList[$k]['grade']='初中';
            }elseif($v['grade']==3){
                $teacherList[$k]['grade']='高中';
            }
            if($v['is_show']==1){
                $teacherList[$k]['showName']='显示';
            }else{
                $teacherList[$k]['showName']='不显示';
            }
        }
        $teacherList['count'] =$count;
        return $teacherList;
    }
    // 获取教师详细信息
    public function teacherShow()
    {
        $param = input();
        $where['t.id']=$param['id'];
        $res=Db::name('teacher')
                   ->alias('t')
                   ->field('t.*,guard_subject.subject,guard_textbook.textbook,guard_grade.grade')
                   ->join('guard_grade','guard_grade.id=t.grade_id','left')
                   ->join('guard_subject','guard_subject.id=t.subject_id','left')
                   ->join('guard_textbook','guard_textbook.id=t.textbook_id','left')
                   ->where($where)->find();
        return $res;
    }
    //更新教师信息
    public function updateTeacher()
    {
        $param=input();
        $where['id']=$param['id'];
         $data['name']=$param['name'];
        if(isset($param['editor'])){
            if(strlen(strip_tags($param['editor'])) > 245) {
                $data['false_cont']=substr(strip_tags($param['editor']),0,245);//假内容 用于教师页面的展示
                $data['content']=strip_tags($param['editor']);
            }else{
                $data['content']=strip_tags($param['editor']);//过滤掉富文本的HTML标签
                $data['false_cont']=strip_tags($param['editor']);//假内容 用于教师页面的展示
            }
        }
        if(isset($param['remarkVideo'])){
            $data['remarkVideo']=$param['remarkVideo'];
        }
        $data['title']=$param['title'];
        $data['litpic']=$param['litpic'];
        $data['subject_id']=$param['subject_id'];//科目
        $data['grade_id']=$param['grade_id'];//年纪
        $data['textbook_id']=$param['textbook_id'];//教材版本
        $data['learn']=$param['learn'];//学段
        $data['schoolid'] = $param['schoolid'];
        $data['Audition_video']=$param['Audition_video'];//精品课程视频
        $data['coverlitpic']=$param['coverlitpic'];//教师精品课封面图
        $data['teacherPosition']=$param['teacherPosition'];//教师职称
        $data['type']=$param['type'];//教师所属团队
        $data['is_show']=$param['is_show'];//是否在首页显示
        $data['sort']=$param['sort'];//排序
        $data['teacher_booth']=$param['teacher_booth'];//教师大头贴
        $data['teacher_booth_hover']=$param['teacher_booth_hover'];//教师大头贴
        $res=Db::name('teacher')->where($where)->update($data);
        return $res;
    }
    // 添加教师信息的方法
    public function addTeacher()
    {
        $param=input();
        $data['name']=$param['name'];
        if(isset($param['editor'])){
            if(strlen(strip_tags($param['editor'])) > 245) {
                $data['false_cont']=substr(strip_tags($param['editor']),0,245);//假内容 用于教师页面的展示
                $data['content']=strip_tags($param['editor']);
            }else{
                $data['content']=strip_tags($param['editor']);//过滤掉富文本的HTML标签
                $data['false_cont']=strip_tags($param['editor']);//假内容 用于教师页面的展示
            }
        }
        if(isset($param['remarkVideo'])){
            $data['remarkVideo']=$param['remarkVideo'];
        }
        $data['title']=$param['title'];
        $data['litpic']=$param['litpic'];
        $data['subject_id']=!empty($param['subject_id'])?$param['subject_id']:1;//科目
        $data['grade_id']=!empty($param['grade_id'])?$param['grade_id']:7;//年纪
        $data['textbook_id']=!empty($param['textbook_id'])?$param['textbook_id']:1;//教材版本
        $data['learn']=!empty($param['learn'])?$param['learn']:2;//学段
        $data['schoolid'] = $param['schoolid'];
        $data['teacher_booth'] = $param['teacher_booth'];
        $data['teacher_booth_hover']=$param['teacher_booth_hover'];//教师大头贴
        $data['Audition_video']=$param['Auditionvideo'];//精品课程视频
        $data['coverlitpic']=$param['coverlitpic'];//教师精品课封面图
        $data['teacherPosition']=$param['teacherPosition'];//教师职称
        $data['type']=!empty($param['type'])?$param['type']:0;//教师所属团队
        $data['is_show']=!empty($param['is_show'])?$param['is_show']:0;//是否在首页显示
        $data['sort']=!empty($param['sort'])?$param['sort']:0;//排序
        $res=Db::name('teacher')->insert($data);
        return $res;
    }
    //根据条件查询老师
    public function checkTeacherList($where)
    {
        $res = $this->TeacherDb
                    ->alias('t')
                    ->field('t.*,guard_grade.grade,guard_textbook.textbook')
                    ->join('guard_grade','guard_grade.id=t.grade_id','left')
                   ->join('guard_subject','guard_subject.id=t.subject_id','left')
                    ->join('guard_textbook','guard_textbook.id=t.textbook_id','left')
                    ->whereor($where)->select();
        $count = $this->TeacherDb
                    ->alias('t')
                    ->field('t.id,t.name,t.grade_id,guard_grade.grade,guard_textbook.textbook')
                    ->join('guard_grade','guard_grade.id=t.grade_id','left')
                   ->join('guard_subject','guard_subject.id=t.subject_id','left')
                    ->join('guard_textbook','guard_textbook.id=t.textbook_id','left')
                    ->whereor($where)->count();
       foreach($res as $k=>$v){
            if($v['grade']==1){
                $res[$k]['grade']='小学';
            }elseif($v['grade']==2){
                $res[$k]['grade']='初中';
            }elseif($v['grade']==3){
                $res[$k]['grade']='高中';
            }
            if($v['is_show']==1){
                $teacherList[$k]['showName']='显示';
            }else{
                $teacherList[$k]['showName']='不显示';
            }
        }
        $res['count'] =  $count;
       return $res; 
    }
    public function getlearn()
    {
        return Db::name('learn')->select();
    }
    //获取年级
    public function getgrade()
    {
        $res=Db::name('grade')->select();
        return $res;
    }
    public function getsubject()
    {
        $res= Db::name('subject')->select();
        return $res;
    }
}