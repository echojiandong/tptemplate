<?php
namespace app\manage\model;
use think\Model;
use Phinx\Db\Table;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\Image;
class Video extends Model
{
    // 设置当前模型对应的完整数据表名称
    private $videoClass;

    public function __construct()
    {
        $this->videoClass = Db::name('video_class');
    }
    //添加视频
    public function videoAdd($data)
    {
        return $this->videoClass->insertGetId($data);
    }
    public static function getVideolist($data,$page=null,$limit=null){
      // dump($data);
        if($page){
            return Db::name('video_class')->alias('v')
                ->field('v.*,g.grade,t.textbook,l.learn')
                ->join('guard_grade g','g.id=v.grade_id','left')
                ->join('guard_textbook t','t.id=v.edition_id','left')
                ->join('guard_learn l','l.id=v.learn_id')
                ->where($data)->order('v.id asc,v.Semester')->page($page,$limit)->select();
        }else{
            return Db::name('video_class')->alias('v')
                ->field('v.*,g.grade,t.textbook,l.learn')
                ->join('guard_grade g','g.id=v.grade_id','left')
                ->join('guard_textbook t','t.id=v.edition_id','left')
                ->join('guard_learn l','l.id=v.learn_id')
                ->where($data)
                ->count();
        }
    }
    //根据课程id查看课程详情
    public static function getOneVideo($data){
        $where['v.id']=$data;
        return Db::name('video_class')->alias('v')
                ->field('v.*,g.grade,t.textbook,l.learn,s.subject')
                ->join('guard_grade g','g.id=v.grade_id','left')
                ->join('guard_textbook t','t.id=v.edition_id','left')
                ->join('guard_learn l','l.id=v.learn_id')
                ->join('guard_subject s','s.id=v.subject_id')
                ->where($where)->find();
    }
    //编辑课程获取课程现有数据
    public static function getOneUpdateVideo($data)
    {
        $where['id']=$data;
        return Db::name('video_class')
                ->where($where)->find();
    }
    //更新课程
    public function updateVido($data,$where)
    {
        return $this->videoClass->where($where)->update($data);
    }
    //删除视频
    public function delVideo($where)
    {
        return $this->videoClass->where($where)->delete();
    }
}