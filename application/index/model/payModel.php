<?php
namespace app\index\model;
use think\Db;
use think\Session;
class payModel
{
	//查找用户是否有历史订单
   public function historyOrder($where)
   {
        return Db::name('order')->where($where)->find();
   }
   //获取课程信息
   public function vidoeClassInfo($video_class_id)
   {
        return Db::name('video_class')->where(array('id'=>$video_class_id))->find();
   }
   //创建订单
   public function insertOrder($data)
   {
        return Db::name('order')->insert($data);
   }
}