<?php

/*
*@马桂婵 2019/3/11
*课程操作 
*Model层
*/

namespace app\manage\model;
use think\Model;
use Phinx\Db\Table;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\Image;
class Videoclass extends Model
{
    // 设置当前模型对应的完整数据表名称
    // private $videoClass;

    // public function __construct()
    // {
        // $this->videoClass = Db::name('video_class');
    // }

    // 获取课程列表
    public function getcourselist()
    {
        $param=input();
        $courseList = Db::name("video_class")
                       ->alias('c')
                       ->field("c.id,c.name,c.grade_id,c.sname,from_unixtime(c.uptime , '%Y-%m-%d') AS updatetime,c.price,g.grade,b.textbook,s.subject")
                       ->join('guard_textbook b' , 'c.edition_id = b.id' )
                       ->join('guard_subject s' , 'c.subject_id = s.id' )
                       ->join('guard_grade g' , 'c.grade_id = g.id')
                       ->page($param['page'],$param['limit'])
                       ->select();

        $count = Db::name("video_class")
                       ->alias('c')
                       ->field("c.id,c.name,c.grade_id,c.sname,from_unixtime(c.uptime , '%Y-%m-%d') AS updatetime,c.price,g.grade,b.textbook,s.subject")
                       ->join('guard_textbook b' , 'c.edition_id = b.id' )
                       ->join('guard_subject s' , 'c.subject_id = s.id' )
                       ->join('guard_grade g' , 'c.grade_id = g.id')
                       ->count();

        // foreach($courseList as $k=>$v){
        //     if($v['grade']==1){
        //         $teacherList[$k]['grade']='小学';
        //     }elseif($v['grade']==2){
        //         $teacherList[$k]['grade']='初中';
        //     }elseif($v['grade']==3){
        //         $teacherList[$k]['grade']='高中';
        //     }
        // }
        $courseList['count'] =$count;
        return $courseList;
    }

    // 查看课程详细信息
    public function courseshow($param)
    {
        $param=input();
        $where['l.id']=$param['id'];
        $res = Db::name("video_class")
                ->alias('c')
                ->field("c.id,c.name,c.grade_id,c.sname,from_unixtime(c.uptime , '%Y-%m-%d') AS updatetime,c.price,g.grade,b.textbook,s.subject")
                ->join('guard_textbook b' , 'c.edition_id = b.id' )
                ->join('guard_subject s' , 'c.subject_id = s.id' )
                ->join('guard_grade g' , 'c.grade_id = g.id')
                ->select();
        return $param;
    }

    // 添加课程
    public function addCourse($param)
    {
        $param=input(); 
        $tname = Db::name('teacher')->where('id' , $param['teacher_id'])->find();  //查找教师名称
        $sname = $tname['name'];    //获取教师名称

        // 判断学段
        if($param['grade_id'] <= 6){
            $grade_name = "小学";
        }else if($param['grade_id'] >= 6 || $param['grade_id'] <= 9){
            $grade_name = "初中";
        }else{
            $grade_name = "高中";
        }

        //判断学科
        if($param['subject_id'] == 1){
            $sub_name = "语文";
        }else if($param['subject_id'] == 2){
            $sub_name = "数学";
        }else if($param['subject_id'] == 3){
            $sub_name = "英语";
        }else if($param['subject_id'] == 4){
            $sub_name = "物理";
        }else if($param['subject_id'] == 5){
            $sub_name = "化学";
        }else if($param['subject_id'] == 6){
            $sub_name = "政治";
        }else if($param['subject_id'] == 7){
            $sub_name = "历史";
        }else if($param['subject_id'] == 8){
            $sub_name = "地理";
        }else if($param['subject_id'] == 9){
            $sub_name = "生物";
        }else{
            $sub_name = "专项";
        }

        $key = $grade_name .= $sub_name;    //拼接字段获取pid
        $pid = Db::name('menu')->where('title', 'like' , "%{$key}%")->order('id desc')->select();//模糊查询获取ID

        $data['name'] = $param['name']; //课程名称
        $data['title'] = $param['title']; //课程副标题
        $data['teacherId'] = $param['teacher_id']; //教师ID
        $data['sname'] = $sname; //教师名称
        $data['learn_id'] = $param['learn']; //学段ID
        $data['grade_id'] = $param['grade_id']; //课程年级ID
        $data['subject_id'] = $param['subject_id']; //科目ID
        $data['edition_id'] = $param['textbook_id'];   //课程版本
        $data['img'] = $param['litpic'];  //课程主图
        $data['content'] = $param['editor'];    //课程简述
        $data['price'] = $param['price'];    //课程单价
        $data['time'] = time();
        $data['audi'] = $param['audi'];    //点赞
        $data['pid'] = $pid[0]['id'];    //pid
        $res=Db::name('video_class')->insert($data);

        return $res;
    }

    // 课程修改
    public function courseupdate($param)
    {
        $param=input();
        $tname = Db::name('teacher')->where('id' , $param['teacher_id'])->find();  //查找教师名称
        $sname = $tname['name'];

        // 判断学段
        if($param['grade_id'] <= 6){
            $grade_name = "小学";
        }else if($param['grade_id'] >= 6 || $param['grade_id'] <= 9){
            $grade_name = "初中";
        }else{
            $grade_name = "高中";
        }

        //判断学科
        if($param['subject_id'] == 1){
            $sub_name = "语文";
        }else if($param['subject_id'] == 2){
            $sub_name = "数学";
        }else if($param['subject_id'] == 3){
            $sub_name = "英语";
        }else if($param['subject_id'] == 4){
            $sub_name = "物理";
        }else if($param['subject_id'] == 5){
            $sub_name = "化学";
        }else if($param['subject_id'] == 6){
            $sub_name = "政治";
        }else if($param['subject_id'] == 7){
            $sub_name = "历史";
        }else if($param['subject_id'] == 8){
            $sub_name = "地理";
        }else if($param['subject_id'] == 9){
            $sub_name = "生物";
        }else{
            $sub_name = "专项";
        }

        $key = $grade_name .= $sub_name;    //拼接字段获取pid
        $pid = Db::name('menu')->where('title', 'like' , "%{$key}%")->order('id desc')->select();//模糊查询获取ID

        $data['name'] = $param['name'];
        $data['title'] = $param['title'];
        $data['teacherId'] = $param['teacher_id'];  //教师ID
        $data['sname'] = $sname; //教师名称
        $data['learn_id'] = $param['learn'];   
        $data['grade_id'] = $param['grade_id'];
        $data['subject_id'] = $param['subject_id'];
        $data['edition_id'] = $param['textbook_id']; //课程版本
        $data['price'] = $param['price'];
        $data['audi'] = $param['audi'];
        $data['img'] = $param['litpic'];
        $data['content'] = $param['editor'];  //课程简介
        $data['uptime'] = time();   //更新时间
        $data['pid'] = $pid[0]['id'];    //pid
        
        $res = Db::name('video_class')->where('id' , $param['id'])->update($data);
        return $res;
    }

}