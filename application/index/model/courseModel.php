<?php
namespace app\index\model;
use think\Db;
use think\Session;
class courseModel
{
	//获取年级
    public function getgrade()
    {
        // return Db::name('grade')->limit(6,3)->select();
        return Db::name('grade')->limit(6,3)->select();
    }
    //获取学科
    public function getsubject()
    {
        return Db::name('subject')->select();
    }
    //获取版本
    public function gettextbook()
    {
        return Db::name('textbook')->limit(0,2)->select();
    }
    //获取课程列表
    public function getCourseList($where,$page=null,$pageNum=null)
    {
        if($where){
            if($pageNum){
                $videoList = Db::name('video_class')->alias('v')
                    ->field('v.*')
                    ->where($where)
                    ->group('grade_id,subject_id,Semester')
                    ->limit($page,$pageNum)->select();

                $count = Db::name('video_class')->alias('v')
                            ->field('v.*')
                            ->where($where)
                            ->group('grade_id,subject_id,Semester')
                            ->count();
                $videoList['count'] = $count;
                $productList = Db::name('product')->alias('v')
                    ->field('v.*')
                    ->where($where)
                    ->limit($page,$pageNum)->select();

                if($productList){
                    foreach ($productList as $k=>$v){
                        array_push($videoList,$v);
                    }
                }
                return  $videoList;               
            }else{
                $videoList = Db::name('video_class')->alias('v')
                    ->field('v.*')
                    ->group('grade_id,subject_id,Semester')
                    ->where($where)
                    ->page($page,10)
                    ->select();
                $productList = Db::name('product')->alias('v')
                    ->field('v.*')
                    ->where($where)
                    ->page($page,10)->select();
                if($productList){
                    foreach ($productList as $k=>$v){
                        $productList[$k]['img'] = $v['productUrl'];
                        array_push($videoList,$productList[$k]);
                    }
                }
                return $videoList;
            }
        }
    }
    //获取课程总条数
    public function getCountCourse($where)
    {
        if($where){
            return Db::name('video_class')->alias('v')->where($where)->count();
        }else{
            return Db::name('video_class')->count();
        }
    }
    //统计课程的章节数目
    public function countClassChapter($where)
    {
        $where['part'] = 2;
        return Db::name('video')->where($where) ->where(['display' => 1])->count();
    }
    //统计产品的章节数目
    public function countProductChapter($product_id)
    {
        return Db::name('product_info')->where(['product_id'=>$product_id,'forbiden'=>1]) ->count();
    }
    //统计视频总时长
    public function classHour($id){
        $list  = Db::name('video_class')->alias('vc')
                    ->join('guard_video v','v.kid = vc.id')
                    ->field('v.classhour')
                    ->where(['v.display' => 1])
                    ->where('vc.id',$id)
                    ->select();
        $allTime = 0;
        foreach ($list as $key => $value) {
            $video_time  =explode(':', $value['classhour']);
            if(count($video_time) == 2){
                $allTime =$allTime + $video_time['0'] * 60 + $video_time['1'];
            }elseif(count($video_time) == 3){
                $allTime =$allTime + $video_time['0'] * 60 * 60 + $video_time['1'] * 60 + $video_time['2'];
            }elseif(count($video_time) == 1){
                $allTime +=(int)$video_time['0'];
            }
        }
        return $allTime;
    }
    //获取面包屑
    public function getCrumbs($where)
    {
        $wh['v.id'] = $where['id'];
        if(isset($where['productStatus']) && $where['productStatus']==1){
            $list =  Db::name('product')->alias('v')
                ->field('v.teacherId,v.content,v.Semester,v.subject_id,l.learn,g.grade,s.subject,t.textbook,v.courseware')
                ->join('guard_learn l','l.id=v.learn_id','left')
                ->join('guard_grade g','g.id=v.grade_id','left')
                ->join('guard_subject s','s.id=v.subject_id','left')
                ->join('guard_textbook t','t.id=v.edition_id','left')
                ->where($wh)
                ->find();
            return $list;
        }
        $list =  Db::name('video_class')->alias('v')
                ->field('v.teacherId,v.content,v.Semester,v.subject_id,l.learn,g.grade,s.subject,t.textbook,v.courseware')
                ->join('guard_learn l','l.id=v.learn_id','left')
                ->join('guard_grade g','g.id=v.grade_id','left')
                ->join('guard_subject s','s.id=v.subject_id','left')
                ->join('guard_textbook t','t.id=v.edition_id','left')
                ->where($wh)
                ->find();
        return $list;
    }
    //获取课程详情介绍
    public function getPreLoginCourseInfo($where)
    {
        return Db::name('video_class')->alias('v')
                ->field('v.*,l.learn,g.grade,s.subject,t.textbook,k.link,k.img litpic,k.outline')
                ->join('guard_learn l','l.id=v.learn_id','left')
                ->join('guard_grade g','g.id=v.grade_id','left')
                ->join('guard_subject s','s.id=v.subject_id','left')
                ->join('guard_textbook t','t.id=v.edition_id','left')
                ->join('guard_video k','k.kid=v.id','left')
                ->where(['k.display' => 1])
                ->where($where)
                ->find();
    }
    //获取课程的教师信息
    public function getPreLoginCourseTeacher($where)
    {
        return Db::name('video_class')->alias('v')
                ->join('guard_teacher t','t.id=v.teacherId','left')
                ->where($where)
                ->find();
    }
    //获取课程目录
    public function courseCatalogue($where)
    {
        return Db::name('video')->where($where)->select();
    }
    //获取热门课程
    public function RecommendCourse()
    {
        return Db::name('video_class')->alias('v')
                ->field('v.*,s.subject,s.id cssid')
                ->join('guard_subject s','s.id=v.subject_id','left')
                ->order('purchase desc')
                ->limit(0,2)
                ->select();
    }
    //获取课程的详细信息
    public function courseInfo($where)
    {
        return Db::name('video_class')->alias('v')
                ->field('v.*,l.learn,g.grade,s.subject,t.textbook,e.name,e.content,e.title,e.litpic,e.Audition_video,e.coverlitpic,e.teacherPosition,e.remarkVideo')
                ->join('guard_learn l','l.id=v.learn_id','left')
                ->join('guard_grade g','g.id=v.grade_id','left')
                ->join('guard_subject s','s.id=v.subject_id','left')
                ->join('guard_textbook t','t.id=v.edition_id','left')
                ->join('guard_teacher e','e.id=v.teacherId','left')
                ->where($where)
                ->find();
    }

    // 获取课程信息
    public function course($video_id,$productStatus=0)
    {
        if($productStatus==1){
            return  Db::name('product')->where(['id' => $video_id])->find();
        }else{
            return  Db::name('video_class')->where(['id' => $video_id])->find();
        }
    }
}