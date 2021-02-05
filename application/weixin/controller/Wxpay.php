<?php 
namespace app\weixin\controller;
use app\index\model\CoursePayModel;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
vendor('WxPay.lib.WxPay.Data.php');
Vendor('WxPay.lib.WxPayApi');
class Wxpay extends Controller{
    //小程序支付
    public function pay(){
        $param  = input();
        // $pid = $param['openid'];
        $pid = 11;
        // //获取openid
        // $personInfo = Db::name('wx_person')->where(['pid'=>$pid])->find();
        // $openid = $personInfo['app_openid'];
        $openid = 'o5hkp44mwaG-Hj_DlpEapt8qSMw4';

        //下单
        //判断用户是否已经提交过试听课订单
        $CoursePay = new CoursePayModel();
        $where['person_id']=$pid;
        $where['is_audition']=1;
        $personOrderInfo = Db::name('order_person_son')->where($where)->order('endtime desc')->find();
        $payment = 5; //支付方式1支付宝app支付2支付宝扫码支付3微信支付4微信扫码支付5:小程序支付
        $video_class_id = isset($param['video_class_id']) ? $param['video_class_id'] : 0; // 课程id
        if(!empty($personOrderInfo)){
            //判断订单试听到期时间(210天试听时间)
            $expireTime = strtotime($personOrderInfo['endtime']) + 3600 * 24 * config('audition_day');
            if($expireTime > time() && $personOrderInfo['orderCheck'] == 2){
                return jsonMsg('您已经购买过试听课程，可直接学习',0);
            }else{
                //没有购买过试听课 生成试听课订单
                $res = $CoursePay->insertOrder($pid,$payment,$video_class_id);
                if(!$res){
                    return jsonMsg('订单提交失败',0);
                }
            }
        }else{
            //没有购买过试听课 生成试听课订单
            $res = $CoursePay->insertOrder($pid,$payment,$video_class_id);
            if(!$res){
                    return jsonMsg('订单提交失败',0);
            }
        }
        //调用微信统一下单
        if($res){
            $post['appid'] = config('WX_small_appId');
            $post['mch_id'] = config('mch_id');
            $post['body'] = 'E点就通试听课程';
            $post['nonce_str'] = \WxPayApi::getNonceStr();
            $post['notify_url'] = "http://".$_SERVER['SERVER_NAME']."/weixin/Wxpay/uppaystatus";
            $post['openid'] = $openid;
            $post['out_trade_no'] = $res;
            $post['spbill_create_ip'] = $_SERVER['REMOTE_ADDR'];
            $post['total_fee'] = config('audition_price') * 100;
            $post['trade_type'] = 'JSAPI';
            // $tool = new \WxPayDataBase($post);
            // $sign = $tool->MakeSign();
            $sign = $this->sign($post);
            $post_xml = '<xml>
               <appid>'.$post['appid'].'</appid>
               <body>'.$post['body'].'</body>
               <mch_id>'.$post['mch_id'].'</mch_id>
               <nonce_str>'.$post['nonce_str'].'</nonce_str>
               <notify_url>'.$post['notify_url'].'</notify_url>
               <openid>'.$post['openid'].'</openid>
               <out_trade_no>'.$post['out_trade_no'].'</out_trade_no>
               <spbill_create_ip>'.$post['spbill_create_ip'].'</spbill_create_ip>
               <total_fee>'.$post['total_fee'].'</total_fee>
               <trade_type>'.$post['trade_type'].'</trade_type>
               <sign>'.$sign.'</sign>
            </xml> ';
            //请求统一下单接口
            $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
            $xml = html_postdata($url,$post_xml);
            $array = \WxPayResults::Init($xml);
            //var_dump($array);die;//签名出错
             //print_r($array);
            if ($array['RETURN_CODE'] = 'SUCCESS' && $array['RESULT_CODE'] == 'SUCCESS'){
                $time = time();
                $tmp = '';
                $tmp['appId'] = $post['appid'];
                $tmp['nonceStr'] = $post['nonce_str'];
                $tmp['package'] = 'prepay_id='.$array['PREPAY_ID'];
                $tmp['signType'] = 'MD5';
                $tmp['signType'] = "$time";

                $data['state'] = 200;
                $data['timeStamp'] = "$time";//时间戳
                $data['nonceStr'] = $post['nonce_str'];
                $data['signType'] = 'MD5';
                $data['package'] ='prepay_id='.$array['PREPAY_ID'];
                $data['out_trade_no'] = $res;
                // $newTool = new \WxPayDataBase($tmp);
                // $newSign = $newTool->MakeSign();
                $newSign = $this->sign($tmp);
                $data['paySign'] = $newSign;

            }else{
                $data['state'] = 0;
                $data['text'] = "错误";
                $data['RETURN_CODE'] = $array['RETURN_CODE'];
                $data['RETURN_MSG'] = $array['RETURN_MSG'];
            }
    
        }
        echo json_encode($data);
    }

    //修改订单的支付状态
    public function uppaystatus(){
        // $xml = $GLOBALS['HTTP_RAW_POST_DATA']; //返回的xml
        $xml =file_get_contents('php://input');
        // file_put_contents(dirname(__FILE__).'/xml.txt',$xml); //记录日志 支付成功后查看xml.txt文件是否有内容 如果有xml格式文件说明回调成功
        $xmlObj=simplexml_load_string($xml,'SimpleXMLElement',LIBXML_NOCDATA);
        $xmlArr=json_decode(json_encode($xmlObj),true);
        $out_trade_no=$xmlArr['out_trade_no']; //订单号
        $result_code=$xmlArr['result_code']; //状态
        if($result_code == 'SUCCESS'){
            //判断该笔订单是否在商户网站中已经做过处理
            $res = Db::name('order_person')->where(['order' => $out_trade_no])->find();
            //判断订单是否已经完成支付
            if ($res['state'] == 1 && $res['orderCheck'] == 1) { //【1.待支付 2.已支付 3.支付失败 4.取消支付】
                //组装更改订单的数据
                $dataSon['endtime'] = date('Y-m-d H:i:s', time()); //订单支付的时间
                $dataSon['kcdqtime'] = time()+intval(3600 * 24 * config('audition_day')); //购买到期的时间 [在支付时间原基础上增加210天的时间戳] 
                $dataSon['state'] = 2;
                $dataSon['orderCheck'] = 2;
                //主订单
                $data['state'] = 2;
                $data['orderCheck'] = 2;
                $data['endtime'] = date('Y-m-d H:i:s', time());
                $data['u_time'] = date('Y-m-d H:i:s', time());
                //更改订单状态
                Db::name('order_person')->where(['order' => $out_trade_no])->update($data);
                Db::name('order_person_son')->where(['order_id' => $out_trade_no])->update($dataSon);
                return ;
            }
            exit;
        }else{
            //验证失败
            echo "fail"; 
            exit;
        }
    }
    //签名 $data要先排好顺序
    private function sign($data){
        $stringA = '';
        foreach($data as $key=>$value){
            if(!$value) continue;
            if($stringA) $stringA .= '&'.$key."=".$value;
            else $stringA = $key."=".$value;
        }
        $wx_key = '';
        $stringSignTemp = $stringA.'&key='.config('mch_key');
        return strtoupper(md5($stringSignTemp));
    }
}