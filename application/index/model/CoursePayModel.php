<?php
namespace app\index\model;
use think\Db;
use think\Session;
class CoursePayModel{
	//生成试听客订单
	public function insertOrder($person_id,$payment,$video_class_id){

		$order = time().mt_rand(100000,999999);
		$data = [
                'order' => $order,
                'money' => config('audition_price'),
                'person_id' => $person_id,
                'strtime' => date('Y-m-d H:i:s', time()),
                'payment' => $payment,
                'payMoney' => config('audition_price'),
                'discount_price' => 0
        ];
        $dataSon = [
            'order_son_id' => time().mt_rand(100000,999999), // 小订单号
            'person_id' => $person_id,
            'order_id' => $order,
            'money' => config('audition_price'),
            'strtime' => date('Y-m-d H:i:s', time()),
            'payment' => $payment,
            'payMoney' => config('audition_price'),
            'num' => 1,
            'type' => 1,
            'is_audition'=>1,
            'video_class_id'=>$video_class_id,
        ];
        $order_id = Db::name('order_person')->insertGetId($data);
        $order_son_id = Db::name('order_person_son')->insertGetId($dataSon);
        if($order_id && $order_son_id){
        	return $order;
        }else{
        	return false;
        }
	}

}