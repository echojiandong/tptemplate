<?php
namespace app\index\controller;
use app\index\controller\Auth;
use app\index\model\CoursePayModel;
use app\index\model\PersonModel;
use think\Controller;
use think\Request;
use think\Session;
use think\Cookie;
use think\Db;

Vendor('Alipay1.alipayconfig');
Vendor('Alipay1.alipay_submit');
Vendor('WxPay.WxPayApi');
Vendor('WxPay.JsApiPay');
Vendor('WxPay.H5ApiPay');
Vendor('WxPay.NativePay');
Vendor('WxPay/example/phpqrcode.phpqrcode');
header("Content-type: text/html; charset=utf-8");
class CoursePay extends Communal
{
    public function _initialize(){
        parent::_initialize();
        
        //头部用户信息
        $this->person = new PersonModel($this ->_info);
        $this ->userinfo =  $this->person->GetPerson();
        // $this->assign('personInfo',$this ->userinfo);
        // $this->courseArr = Db::name('subject')->where(['id' => ['in', $this->subjArr]])->column('subject');
    }

    protected $order_id = ''; // 订单id

    // 支付类型
    protected $payMethod= [
        'alipay' => [
            'scan' => '扫码支付',
            'wap' => '手机端网站支付',
        ],
        'wechat' => [
            'wap' => 'H5支付',
            'web' => 'PC端网站支付',
            'scan' => '扫码支付',
        ],
    ];
    public function coursePay(){
        //①、获取用户openid
        $param = input();
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== FALSE){ //微信公众号支付      
            if(Cookie::get('openid'))
            {
                $openId = Cookie::get('openid');
            }else{
                $tools = new \JsApiPay();
                $openId = $tools->GetOpenid();
                Cookie::set('openid',$openId);
            }
			if (!isset($param['code'])) {
				$this->assign('url',$openId);
				$this->assign('openId',0);
			} else {
				$this->assign('url','');
				$this->assign('openId',$openId);				
			}
        }else{
            $this->assign('url','');
        }            
        return $this->fetch('/index/course/coursePay');
    }
	/*手机端打开支付页面但是不是在微信端打开的但是要发起微信支付*/
	/**
     * @函数   coursePayH5()
     * @功能   微信h5支付(购买试听课)
     */
    public function coursePayH5()
    {
        $param=input();
        //注册用户
        $person=$this->insertPerson($param['phone'],$param['register_from']);
        if($person)
        {
            $person_id = $person;
        }else{
            echo "<script>alert('数据又误请重新提交');window.location.href='/index/course_pay/coursePay';</script>";exit;
        }
        // $money = $param['money']; //打折前的金额
        // $moneys = $param['moneys']; //打折后的金额
        $money = config('audition_price');
        $payMoney = $param['money']; // 订单金额
        //判断参数是否正常
        if ($person_id == "" || $money == "") {
            echo "<script>alert('数据又误请重新提交');window.location.href='/index/course_pay/coursePay';</script>";exit;
        }
        // 验证价格
        // if ($payMoney != $money) {
        //     echo "<script>alert('数据又误请重新提交');</script>";exit;
        // }
        //判断用户是否已经提交过试听课订单
        $CoursePay = new CoursePayModel();
        $where['person_id']=$person_id;
        $where['is_audition']=1;
        $personOrderInfo = Db::name('order_person_son')->where($where)->order('endtime desc')->find();
        $payment = 3; //支付方式1支付宝app支付2支付宝扫码支付3微信支付4微信扫码支付
        $video_class_id = isset($param['video_class_id']) ? $param['video_class_id'] : ''; // 课程id
        if(!empty($personOrderInfo)){
        	//判断订单试听到期时间(210天试听时间)
        	$expireTime = strtotime($personOrderInfo['endtime']) + 3600 * 24 * config('audition_day');
        	if($expireTime > time() && $personOrderInfo['orderCheck'] == 2){
                echo "<script>alert('您已经购买过试听课程，可直接学习');window.location.href='/index/course_pay/coursePay';</script>";exit;
        	}else{
        		//没有购买过试听课 生成试听课订单
        		$res = $CoursePay->insertOrder($person_id,$payment,$video_class_id=0);
                if(!$res){
                    echo "<script>alert('订单提交失败!请重新提交');window.location.href='/index/course_pay/coursePay';</script>";exit;
                }
        	}
        }else{
        	//没有购买过试听课 生成试听课订单
        	$res = $CoursePay->insertOrder($person_id,$payment,$video_class_id=0);
            if(!$res){
                echo "<script>alert('订单提交失败!请重新提交');window.location.href='/index/course_pay/coursePay';</script>";exit;
            }
        }
        $appid = config('WX_appId');
        $mch_id = config('mch_id');//商户号
        $key = config('mch_key');//商户key
        $notify_url = "http://".$_SERVER['HTTP_HOST']."/index/course_pay/h5success";//回调地址
        $wechatAppPay = new \wechatAppPay($appid, $mch_id, $notify_url, $key);
        $params['body'] = '知识一点通在线课';                       //商品描述
        $params['out_trade_no'] = $res;   //自定义的订单号
        $params['total_fee'] = config('audition_price') * 100;  //订单金额 只能为整数 单位为分
        $params['trade_type'] = 'MWEB';                   //交易类型 JSAPI | NATIVE | APP | WAP 
        $params['scene_info'] = '{"h5_info": {"type":"Wap","wap_url": "https://api.lanhaitools.com/wap","wap_name": "知识一点通在线课"}}';
        $result = $wechatAppPay->unifiedOrder( $params );
        $url = $result['mweb_url'].'&redirect_url=http%3A%2F%2F'.$_SERVER['HTTP_HOST'].'/index/course_pay/courseSuccess';//redirect_url 是支付完成后返回的页面
       /* return $url;    */
        header("Location: $url"); 
    }

    /*h5支付成功回调*/
    public function h5success()
    {
        $xml =file_get_contents('php://input');
        //file_put_contents('gaojinhui_wxpay1.log',file_get_contents('php://input'),FILE_APPEND);
        $xmlObj=simplexml_load_string($xml,'SimpleXMLElement',LIBXML_NOCDATA);
        $xmlArr=json_decode(json_encode($xmlObj),true);
        $out_trade_no=$xmlArr['out_trade_no']; //订单号
        $result_code=$xmlArr['result_code']; //状态
        //file_put_contents('gaojinhui_wxpay.log',var_export($xmlArr,TRUE),FILE_APPEND);
        //file_put_contents (APP_PATH."/runtime/log/wxlog.txt", date ( "Y-m-d H:i:s" ) . "  " . $xmlArr . "\r\n", FILE_APPEND );
        if(strtolower($result_code)=='success'){
            //判断该笔订单是否在商户网站中已经做过处理
            $res = Db::name('order_person')->where(['order' => $out_trade_no])->find();
            //判断订单是否已经完成支付
            if ($res['state'] == 1 && $res['orderCheck'] == 1) { //【1.待支付 2.已支付 3.支付失败 4.取消支付】
                //组装更改订单的数据
                $dataSon['endtime'] = date('Y-m-d H:i:s', time()); //订单支付的时间
                $dataSon['kcdqtime'] = time()+intval(3600 * 24 * config('audition_day')); //购买到期的时间 [在支付时间原基础上增加210天的时间戳] 
                $dataSon['state'] = 2;
                $dataSon['orderCheck'] = 2;
                $dataSon['payment']=5;

                //主订单
                $data['state'] = 2;
                $data['orderCheck'] = 2;
                $data['endtime'] = date('Y-m-d H:i:s', time());
                $data['u_time'] = date('Y-m-d H:i:s', time());
                $data['payment']=5;
                //更改订单状态
                Db::name('order_person')->where(['order' => $out_trade_no])->update($data);
                Db::name('order_person_son')->where(['order_id' => $out_trade_no])->update($dataSon);
                return ;
            }
            exit;
        }else{
            echo 'fail';
            exit; 
        }
    }
    //微信扫码支付
    public function courseWxCodePay(){
        $param=input();
        //注册用户
        $person=$this->insertPerson($param['phone'],$param['register_from']);
        if($person)
        {
            $person_id = $person;
        }else{
            jsonMsg('请重新提交', 1);die;
        }
        $money = config('audition_price');
        $payMoney = $param['money']; // 订单金额
        //判断参数是否正常
        if ($person_id == "" || $money == "") {
        	return jsonMsg('数据有误',0);
        }
        // 验证价格
        // if ($payMoney != $money) {
        //     return jsonMsg('订单价格有误，请重新下单！', 1);
        // }
        //判断用户是否已经提交过试听课订单
        $CoursePay = new CoursePayModel();
        $where['person_id']=$person_id;
        $where['is_audition']=1;
        $personOrderInfo = Db::name('order_person_son')->where($where)->order('endtime desc')->find();
        $payment = 4; //支付方式1支付宝app支付2支付宝扫码支付3微信支付4微信扫码支付
        $video_class_id = isset($param['video_class_id']) ? $param['video_class_id'] : ''; // 课程id
        if(!empty($personOrderInfo)){
            //判断订单试听到期时间(210天试听时间)
            $expireTime = strtotime($personOrderInfo['endtime']) + 3600 * 24 * config('audition_day');
            if($expireTime > time() && $personOrderInfo['orderCheck'] == 2){
                return jsonMsg('您已经购买过试听课程，可直接学习',0);
            }else{
                //没有购买过试听课 生成试听课订单
                $res = $CoursePay->insertOrder($person_id,$payment,$video_class_id=0);
                if(!$res){
                    return jsonMsg('订单提交失败',0);
                }
            }
        }else{
            //没有购买过试听课 生成试听课订单
            $res = $CoursePay->insertOrder($person_id,$payment,$video_class_id=0);
            if(!$res){
                    return jsonMsg('订单提交失败',0);
            }
        }
        if($res){
            //调用微信统一下单
            $notify = new \NativePay(); //实例化微信类
            $input = new \WxPayUnifiedOrder();
            $input->SetBody('试听课');
            $input->SetAttach('试听课');
            $input->SetOut_trade_no($res);
            $input->SetTotal_fee($money*100);
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $input->SetGoods_tag('试听课');
            $input->SetNotify_url("http://".$_SERVER['SERVER_NAME']."/index/Course_pay/uppaystatus"); // /mid/{$mid}
            $input->SetTrade_type("NATIVE");
            $input->SetProduct_id($res);
            $result = $notify->GetPayUrl($input);
            $codeurl = $result["code_url"];
            if ($result["return_code"] == "SUCCESS") {
                return jsonMsg('二维码生成成功!',1,$codeurl);
            }else{
                return jsonMsg('二维码生成失败,请刷新后重试!',0);
            }
        }
    }

    //获取微信扫码二维码
    public function getQrcode(){
        error_reporting(E_ERROR);
        // require_once 'phpqrcode/phpqrcode.php';
        $notify = new \QRcode(); //实例化微信类
        $url = urldecode($_GET["data"]);
        $notify->png($url);
    }
    
    //微信支付
    public function courseWxPay(){
        $param=input();
        //注册用户
        $person=$this->insertPerson($param['phone'],$param['register_from']);
        if($person)
        {
            $person_id = $person;
        }else{
            jsonMsg('请重新提交', 1);die;
        }
        $money = config('audition_price');
        $payMoney = $param['money']; // 订单金额
        //判断参数是否正常
        if ($person_id == "" || $money == "") {
        	return jsonMsg('数据有误',0);
        }
        // 验证价格
        // if ($payMoney != $money) {
        //     return jsonMsg('订单价格有误，请重新下单！', 1);
        // }
        //判断用户是否已经提交过试听课订单
        $CoursePay = new CoursePayModel();
        $where['person_id']=$person_id;
        $where['is_audition']=1;
        $personOrderInfo = Db::name('order_person_son')->where($where)->order('endtime desc')->find();
        $payment = 3; //支付方式1支付宝app支付2支付宝扫码支付3微信支付4微信扫码支付
        $video_class_id = isset($param['video_class_id']) ? $param['video_class_id'] : ''; // 课程id
        if(!empty($personOrderInfo)){
            //判断订单试听到期时间(210天试听时间)
            $expireTime = strtotime($personOrderInfo['endtime']) + 3600 * 24 * config('audition_day');
            if($expireTime > time() && $personOrderInfo['orderCheck'] == 2){
                return jsonMsg('您已经购买过试听课程，可直接学习',0);
            }else{
                //没有购买过试听课 生成试听课订单
                $res = $CoursePay->insertOrder($person_id,$payment,$video_class_id=0);
                if(!$res){
                    return jsonMsg('订单提交失败',0);
                }
            }
        }else{
            //没有购买过试听课 生成试听课订单
            $res = $CoursePay->insertOrder($person_id,$payment,$video_class_id=0);
            if(!$res){
                    return jsonMsg('订单提交失败',0);
            }
        }
        $openId = empty($param['openId']) ? "0" : $param['openId'];
        //①、获取用户openid
        $tools = new \JsApiPay();
        //②、统一下单
        $input = new \WxPayUnifiedOrder();
        $input->SetBody('知识一点通试听课');
        $input->SetAttach('知识一点通试听课');
        $input->SetOut_trade_no($res);
        $input->SetTotal_fee($money*100);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag('知识一点通试听课');
        $input->SetNotify_url("http://".$_SERVER['SERVER_NAME']."/index/course_pay/uppaystatus");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $order = \WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($order); 
        return jsonMsg('请求成功',0,$jsApiParameters);exit;   
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
    /*******************************************支付宝支付*********************************************
    /**
     * 扫码支付
     * 
     */
    // public function scanPay($order_id)
    public function scanPay($order_id)
    {
        //引入支付宝第三方配置
        require_once(__DIR__.'/Alipay/scan/config.php');
        require_once(__DIR__.'/Alipay/scan/pagepay/service/AlipayTradeService.php');
        require_once(__DIR__.'/Alipay/scan//pagepay/buildermodel/AlipayTradePagePayContentBuilder.php');

        //商户订单号
        $out_trade_no = $order_id;
        //订单名称
        $orderName =  'E点就通试听课程';
        //付款金额，必填
        $payMoney =config('audition_price');
        //商品描述，可空
        $body = "E点就通试听课程";

        // 支付超时，线下扫码交易定义为5分钟
	    $timeExpress = "5m";
       
        // 创建请求builder，设置请求参数


        //构造参数
        $payRequestBuilder = new \AlipayTradePagePayContentBuilder();
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($orderName);
        $payRequestBuilder->setTotalAmount($payMoney);
        $payRequestBuilder->setOutTradeNo($out_trade_no);
        $aop = new \AlipayTradeService($config);
        $response = $aop->pagePay($payRequestBuilder,$config['return_url'],$config['notify_url']);
        //输出表单
        dump($response);
    }

    /**
     * 网站支付
     */
    public function wapPay($order_id)
    {
        require_once(__DIR__.'/Alipay/wap/wappay/service/AlipayTradeService.php');
        require_once(__DIR__.'/Alipay/wap/wappay/buildermodel/AlipayTradeWapPayContentBuilder.php');
        require_once(__DIR__.'/Alipay/wap/config.php');

        //商户订单号，商户网站订单系统中唯一订单号，必填
        $out_trade_no = $order_id;

        //订单名称，必填
        $subject = 'E点就通试听课程';

        //付款金额，必填
        $total_amount = config('audition_price');

        //商品描述，可空
        $body = '';

        //超时时间
        $timeout_express="1m";

        $payRequestBuilder = new \AlipayTradeWapPayContentBuilder();
        
        $payRequestBuilder->setBody($body);
        $payRequestBuilder->setSubject($subject);
        $payRequestBuilder->setOutTradeNo($out_trade_no);
        $payRequestBuilder->setTotalAmount($total_amount);
        $payRequestBuilder->setTimeExpress($timeout_express);
        $payResponse = new \AlipayTradeService($config);

        $result=$payResponse->wapPay($payRequestBuilder, $config['return_url'], $config['notify_url']);
        
        return ;
    }


    /**
     * 微信内支付宝页面
     */
    public function pay(Request $request)
    {
        $param = $request->param();
        $phone = $param['phone'];
        $money = $param['money'];
        $register_from = $param['register_from'];
        $payment = 1;

        $this->assign('phone', $phone);
        $this->assign('money', $money);
        $this->assign('register_from', $register_from);
        $this->assign('payment', $payment);

        return $this->fetch('/index/course/pay');
    }



    /***************************************公共方法************************************************/

    /**
     * 支付成功回调
     */
    public function successUrl(Request $request)
    {
        require_once(__DIR__.'/Alipay/scan/config.php');
        require_once(__DIR__.'/Alipay/scan/pagepay/service/AlipayTradeService.php');
        require_once(__DIR__.'/Alipay/scan//pagepay/buildermodel/AlipayTradeQueryContentBuilder.php');
        $param = $request->param();
        $out_trade_no = $param['out_trade_no']; // 商户订单号
    
        $trade_no = $param['trade_no']; // 支付宝交易号
        $RequestBuilder = new \AlipayTradeQueryContentBuilder();
        $RequestBuilder->setTradeNo($trade_no);
        $RequestBuilder->setOutTradeNo($out_trade_no);
    
        $Response = new \AlipayTradeService($config);
        $result=$Response->Query($RequestBuilder);
        
        $code = $result->code; // 成功码 ， 1000成功， 其它失败
        $trade_status = $result->trade_status;

        
        if ($code == 10000) {
            //判断该笔订单是否在商户网站中已经做过处理
            $res = Db::name('order_person')->where(['order' => $out_trade_no])->find();
            //判断订单是否已经完成支付
            if ($res['state'] == 1 && $res['orderCheck'] == 1) { //【1.待支付 2.已支付 3.支付失败 4.取消支付】
                //组装更改订单的数据
                $dataSon['endtime'] = date('Y-m-d H:i:s', time()); //订单支付的时间
                $dataSon['kcdqtime'] = time()+intval(3600 * 24 * config('audition_day')); //购买到期的时间 [在支付时间原基础上增加210天的时间戳] 
                $dataSon['state'] = 2;
                $dataSon['orderCheck'] = 2;
                $dataSon['payment'] = 1;
                
                //主订单
                $data['state'] = 2;
                $data['orderCheck'] = 2;
                $data['payment'] = 1;
                $data['endtime'] = date('Y-m-d H:i:s', time());
                $data['u_time'] = date('Y-m-d H:i:s', time());
                //更改订单状态
                Db::name('order_person')->where(['order' => $out_trade_no])->update($data);
                Db::name('order_person_son')->where(['order_id' => $out_trade_no])->update($dataSon);
            }
        }
        
    }

    public function return_url()
    {
        // 回调后重定向到网站
        $this->redirect('/index/course_pay/courseSuccess'); 
    }
    public function courseSuccess(){
        
        return $this->fetch('/index/course/coursePay_success');
    }
    /**
     * 购买生成用户
     * @param phone string 手机号
     * @param code  string 验证码
     * @return 
     * 
     */
    public function insertPerson($phone,$register_from)
    {
        // 判断该号码是否已经是用户
        $isPerson = Db::name('person')->where(['phone' => $phone])->find();
        if ($isPerson) {
            return $isPerson['id'];
        }else{
            // 用户注册信息
            $password = user_md5(substr($phone, -6)); // 密码手机后6位
            $data = [
                'phone' => $phone,
                'addtime' => time(),
                'register_from' => $register_from,
                'password' => $password
            ];
            $person_id = Db::name('person')->insertGetId($data);
            if ($person_id) {
                return $person_id;
            }else{
                return false;
            }
        }
    }

    /**
     * 生成订单
     * @param person_id int 用户id
     * @param video_class_id int 课程id
     * @param is_audi int 购买试听还是正式  1：正常， 2：试听 
     * 
     */
    public function createOrder(Request $request)
    {
        $param = $request->param();
        
        $person=$this->insertPerson($param['phone'],$param['register_from']);
        
        if($person)
        {
            $person_id = $person;
        }else{
            jsonMsg('请重新提交', 1);die;
        }
        /** ---------大订单参数--------- */
        $money = $param['money']; // 订单金额
        $payment = $param['payment']; //支付方式
        
        $payMoney = config('audition_price'); // 支付价格
        // 验证价格
        // if ($payMoney != $money) {
        //     jsonMsg('订单价格有误，请重新下单！', 1);die;
        // }

        /** ---------验证订单--------- */
        $where['person_id']=$person_id;
        $where['is_audition']=1;
        $personOrderInfo = Db::name('order_person_son')->where($where)->order('endtime desc')->find();
        $CoursePay = new CoursePayModel();
        
        if(!empty($personOrderInfo)){
        	//判断订单试听到期时间(210天试听时间)
        	$expireTime = strtotime($personOrderInfo['endtime']) + 3600 * 24 * config('audition_day');
        	if($expireTime > time() && $personOrderInfo['orderCheck'] == 2){
        		return jsonMsg('您已经购买过试听课程，可直接学习',0);
        	}else{
        		//订单已经到期再次生成订单购买
        		$res = $CoursePay->insertOrder($person_id,$payment,$video_class_id=0);
                if(!$res){
                    return jsonMsg('订单提交失败',0);
                }
        	}
        }else{
        	//没有购买过试听课 生成试听课订单
        	$res = $CoursePay->insertOrder($person_id,$payment,$video_class_id=0);
            if(!$res){
                    return jsonMsg('订单提交失败',0);
            }
        }
        
        if($payment == 1){
            //发起支付宝app支付
            $this->wapPay($res);
        } elseif ($payment == 2) {
            //发起支付宝二维码支付
            $this->scanPay($res);
        }

    }
}