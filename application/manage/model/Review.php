<?php
namespace app\manage\model;
use think\Model;
use Phinx\Db\Table;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
use think\Image;
class Review extends Model
{
    // 设置当前模型对应的完整数据表名称
    private $review;

    public function __construct()
    {
        $this->review = Db::name('video');
    }
    //添加视频
    public function videoAdd($data)
    {

        return $this->review->insertGetId($data);
    }


    /**
     * @马桂婵  2019/3/13
     * 获取列表
     */

    public static function getReviewlist($data,$page=null,$limit=null){
        // dump($data);
        if($page){
            return Db::name('video')->alias('v')
                ->field('v.*,c.id as classid')
                ->join('video_class c','c.id=v.kid','left')
                ->where(['v.pid' => 0])
                ->where($data)
                ->order('v.id desc , v.part')
                ->page($page,$limit)
                ->select();
        }else{
            return Db::name('video')->alias('v')
                ->field('v.*,c.id as classid')
                ->join('video_class c','c.id=v.kid','left')
                ->where(['v.pid' => 0])
                ->where($data)
                ->count();
        }
    }

    

    public function getvideo()
    {
        $res=Db::name('video')->select();
        return $res;
    }
    public function getvideo_class()
    {
        $res= Db::name('video_class')->select();
        return $res;
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

    /**
     * 节
     */
    public function getlist($id,$page = null,$limit = null)
    {
        if($page){
            return Db::name('video')
                ->where('pid' , $id)
                ->order('id desc')
                ->select();
        }else{
            return Db::name('video')
                ->where('pid' , $id)
                ->order('id desc')
                ->count();
        }
    }

    /**
     * 知识点列表
     */
    public function knowlist($section_id)
    {
        $res = Db::name('knowledge')
            ->where('s_id' , $section_id)
            ->select();
        return $res;
    }
}