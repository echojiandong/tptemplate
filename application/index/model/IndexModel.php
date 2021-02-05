<?php
namespace app\index\model;
use think\Db;
use think\Session;
use think\Cache;
class IndexModel
{
	private $PersonDb;

    public function __construct()
    {
        $this->PersonDb = Db::name('person');
    }
    //验证验证码
    public function Verification()
    {
    	$param = input();
        $where['phone']=$param['phone'];
        $where['expire_time']=array('egt',time());
        // $code_cook=Cookie::get($param['phone'].'_code',1800); //压入缓存  半小时内有效
        //获取验证码
        $codeList = Db::name('check_code')->where($where)->order('id desc')->find();
        if(!empty($codeList)){
            if($codeList['code'] == $param['code']){
                // Cache::set($param['phone'],null);
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
        
    }
    //注册用户有三天免费试听权限
    public function freeTrial($data)
    {
        //用户不存在生成用户账号并记录用户领取记录
        return $this->PersonDb->insert($data);
    }
    //用户账号存在但是用户没有领取过试听课程
    public function freeTrialUpdate($data)
    {
        return $this->PersonDb->update($data);
    }
    //获取版本数据
    public function getTextBook()
    {
        return Db::name('textbook')->limit(0,2)->select();
    }
    //根据用户选择的版本获取视频数据
    public function indexTextVideo()
    {
        $param=input();
        $where['v.edition_id'] = (int)$param['id'];
        dump($where);
        return Db::name('video_class')->alias('v')
                ->field('v.*,s.subject,s.id cssid')
                ->join('guard_subject s','s.id=v.subject_id')
                ->where($where)
                ->order('v.popularity desc')
                ->limit(0,12)->select();
    }
    //用户没有选版本的时候页面老师的数据
    public function indexTeacher()
    {
        return Db::name('teacher')->limit(0,1)->find();
    }
    //用户没有选版本前页面加载的视频数据
    public function indexVideo()
    {
        return Db::name('video_class')->alias('v')
                ->field('v.*,s.subject,s.id cssid')
                ->join('guard_subject s','s.id=v.subject_id')
                ->order('v.popularity desc')
                ->limit(0,12)->select();
    }
    //判断用户有没有购买过本课程
    public function personPlayVideo($where)
    {
        return Db::name('order')->where($where)->find();
    }
    //获取用户购买过的订单
    public function rateOfLearning($where)
    {
        return Db::name('order')->alias('o')
                   ->field('o.*,l.learn,g.grade,s.subject,v.img')
                   ->join('guard_learn l','l.id=o.learn_id')
                   ->join('guard_grade g','g.id=o.grade_id')
                   ->join('guard_subject s','s.id=o.subject_id')
                   ->join('guard_video_class v','v.id=o.video_class_id')
                   ->where($where)->select();
    }
    //个人中心获取用户购买过的订单
    public function peraonRateOfLearning($page,$where)
    {
        return Db::name('order')->alias('o')
                   ->field('o.*,l.learn,g.grade,s.subject,v.img')
                   ->join('guard_learn l','l.id=o.learn_id')
                   ->join('guard_grade g','g.id=o.grade_id')
                   ->join('guard_subject s','s.id=o.subject_id')
                   ->join('guard_video_class v','v.id=o.video_class_id')
                   ->where($where)->select();
    }
    //获取用户购买过的课时数目
    public function countClass($where)
    {
        return Db::name('video')->where($where) ->where(['display' => 1])->count();
    }
    //获取用户已经学习过的课时数目
    public function countHistoryClass($where)
    {
        return Db::name('videolog')->where($where)->count();
    }
}