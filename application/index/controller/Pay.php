<?php
namespace app\index\controller;
use app\index\model\payModel;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;
Vendor('Alipay1.alipayconfig');
Vendor('Alipay1.alipay_submit');
Vendor('WxPay.WxPayApi');
Vendor('WxPay.JsApiPay');
Vendor('WxPay.H5ApiPay');
Vendor('WxPay.NativePay');
class Pay extends Controller
{
	public function models_web(){
        //①、获取用户openid
        if(strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== FALSE){ //微信公众号支付      
            $tools = new \JsApiPay();
		    $openId = $tools->GetOpenid();
			if (!isset($_GET['code'])) {
				$this->assign('url',$openId);
				$this->assign('openId',0);
			} else {
				$this->assign('url','');
				$this->assign('openId',$openId);				
			}
        }               
        $this->display();
    }
 /******************* STR支付宝STR  ****************************************************************/

    /**
    * @函数   wlalipay()
    * @功能   外链支付宝支付
    * @时间   2019-1-15
    * @作者   高金辉
    */
    public function wlalipay(){
        //获取页面传来的参数课程id和用户id
        $param=input();
        $user=Session::get('user');
        $person_id =$user['id'];//用户id
        $video_class_id =$param['video_class_id'];//课程id
        $money = $param['money']; //打折前的金额
        $moneys = $param['moneys']; //打折后的金额
        //判断参数是否正常
        if ($person_id == "" || $video_class_id == "" || $moneys == "" || $money == "") {
            echo '<script type="text/javascript">alert(\'传参有误,请重新传参!\');</script>';exit;
        }
        //判断用户是否已经提交过订单
        $payModel=new payModel();
        $where['person_id']=$person_id;
        $where['video_class_id']=$video_class_id;
        $historyOrder=$payModel->historyOrder($where);
        //获取课程信息
        $vidoeClassInfo=$payModel->vidoeClassInfo($video_class_id);
        //生成订单信息
    	$orderid = time().rand(1000000000,9999999999);
    	$data["order"] = $orderid;//唯一订单号
    	$data["grade_id"] = $vidoeClassInfo['grade_id'];//订单的商品所属年级
    	$data["subject_id"] = $vidoeClassInfo['subject_id'];//订单的商品所属的科目
    	$data["money"] = $moneys;//订单的金额
    	$data["person_id"] = $person_id;//购买用户的ID
    	$data["state"] = 1;//订单的状态 【1.待支付 2.已支付 3.支付失败 4.取消支付】
    	$data["strtime"] = time();//订单生成的时间
    	$data["kcdqtime"] = time()+15552000;//课程到期的时间有效期半年
    	$data["payment"] = 2;//支付方式1支付宝app支付2支付宝扫码支付3微信支付4微信扫码支付
    	$data['textbook_id'] = $vidoeClassInfo['edition_id'];//商品版本id
    	$data['learn_id'] = $vidoeClassInfo['learn_id'];//商品学段id
    	$data['semester'] = $vidoeClassInfo['Semester'];//1上学期2下学期
    	$data['video_class_id'] = $video_class_id;//对应课程主id
        if($historyOrder){
        	//订单存在判断订单是否已经支付
        	if($historyOrder['state']==2){
        		//订单支付成功判断订单是否已经到期
        		if($historyOrder['kcdqtime']>time()){
        			//订单支付成功且订单没有到期
        			echo '<script type="text/javascript">alert(\'您已经购买过本课程，可直接到个人中心学习！\');</script>';exit;
        		}else{
        			//订单支付成功但是订单到期了，重新生成新的订单！
        			$cg = $payModel->insertOrder($data);
        			if (!$cg) {
			            echo '<script type="text/javascript">alert(\'订单生成失败,请重新提交!\');</script>';exit;
			        }
        		}
        	}
        }else{
        	//创建新订单
        	$cg = $payModel->insertOrder($data);
        	if (!$cg) {
	            echo '<script type="text/javascript">alert(\'订单生成失败,请重新提交!\');</script>';exit;
	        }
        }    
    /************************************----- STR支付宝支付STR -----***************************************/
            //引入支付宝第三方配置
             require_once(__DIR__."/Alipay1/alipay.configs.php");
             require_once(__DIR__."/Alipay1/lib/alipay_submit.class.php");
            /**************************请求参数**************************/

            //商户订单号，商户网站订单系统中唯一订单号，必填
            $out_trade_no = $orderid;//$_POST['WIDout_trade_no'];
            //订单名称，必填
            $subject = $vidoeClassInfo['name'];//$_POST['WIDsubject'];
            //付款金额，必填
            $total_fee = $moneys;//$_POST['WIDtotal_fee'];
            //收银台页面上，商品展示的超链接，必填
            $show_url = "http://www.ywd100.com/";
            //商品描述，可空
            $body = "";
            /************************************************************/

            //构造要请求的参数数组，无需改动
            $parameter = array(
                "service"       => $alipay_config['service'],
                "partner"       => $alipay_config['partner'],
                "seller_id"  => $alipay_config['seller_id'],
                "payment_type"  => $alipay_config['payment_type'],
                "notify_url"    => $alipay_config['notify_urls'],
                "return_url"    => $alipay_config['return_urls'],
                "_input_charset"    => trim(strtolower($alipay_config['input_charset'])),
                "out_trade_no"  => $out_trade_no,
                "subject"   => $subject,
                "total_fee" => $total_fee,
                "show_url"  => $show_url,
                "app_pay" => "Y",//启用此参数能唤起钱包APP支付宝
                "body"  => $body
                //其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.2Z6TSk&treeId=60&articleId=103693&docType=1
                //如"参数名"    => "参数值"   注：上一个参数末尾需要“,”逗号。
                
            );
            //建立请求
            $alipaySubmit = new \AlipaySubmit($alipay_config);
            $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
            echo $html_text;
    /************************************----- END支付宝支付END -----***************************************/
    }
//支付宝扫码支付
public function wlalipay1(){
        //获取页面传来的参数课程id和用户id
        $param=input();
        $user=Session::get('user');
        $person_id =$user['id'];//用户id
        $video_class_id =$param['video_class_id'];//课程id
        $money = $param['money']; //打折前的金额
        $moneys = $param['moneys']; //打折后的金额
        //判断参数是否正常
        if ($person_id == "" || $video_class_id == "" || $moneys == "" || $money == "") {
            echo '<script type="text/javascript">alert(\'传参有误,请重新传参!\');</script>';exit;
        }
        //判断用户是否已经提交过订单
        $payModel=new payModel();
        $where['person_id']=$person_id;
        $where['video_class_id']=$video_class_id;
        $historyOrder=$payModel->historyOrder($where);
        //获取课程信息
        $vidoeClassInfo=$payModel->vidoeClassInfo($video_class_id);
        //生成订单信息
    	$orderid = time().rand(1000000000,9999999999);
    	$data["order"] = $orderid;//唯一订单号
    	$data["grade_id"] = $vidoeClassInfo['grade_id'];//订单的商品所属年级
    	$data["subject_id"] = $vidoeClassInfo['subject_id'];//订单的商品所属的科目
    	$data["money"] = $moneys;//订单的金额
    	$data["person_id"] = $person_id;//购买用户的ID
    	$data["state"] = 1;//订单的状态 【1.待支付 2.已支付 3.支付失败 4.取消支付】
    	$data["strtime"] = time();//订单生成的时间
    	$data["kcdqtime"] = time()+15552000;//课程到期的时间有效期半年
    	$data["payment"] = 2;//支付方式1支付宝app支付2支付宝扫码支付3微信支付4微信扫码支付
    	$data['textbook_id'] = $vidoeClassInfo['edition_id'];//商品版本id
    	$data['learn_id'] = $vidoeClassInfo['learn_id'];//商品学段id
    	$data['semester'] = $vidoeClassInfo['Semester'];//1上学期2下学期
    	$data['video_class_id'] = $video_class_id;//对应课程主id
        if($historyOrder){
        	//订单存在判断订单是否已经支付
        	if($historyOrder['state']==2){
        		//订单支付成功判断订单是否已经到期
        		if($historyOrder['kcdqtime']>time()){
        			//订单支付成功且订单没有到期
        			echo '<script type="text/javascript">alert(\'您已经购买过本课程，可直接到个人中心学习！\');</script>';exit;
        		}else{
        			//订单支付成功但是订单到期了，重新生成新的订单！
        			$cg = $payModel->insertOrder($data);
        			if (!$cg) {
			            echo '<script type="text/javascript">alert(\'订单生成失败,请重新提交!\');</script>';exit;
			        }
        		}
        	}
        }else{
        	//创建新订单
        	$cg = $payModel->insertOrder($data);
        	if (!$cg) {
	            echo '<script type="text/javascript">alert(\'订单生成失败,请重新提交!\');</script>';exit;
	        }
        }    
    /************************************----- STR支付宝支付STR -----***************************************/
             //引入支付宝第三方配置
            require_once(__DIR__."/Alipay1/alipay.config.php");
            require_once(__DIR__."/Alipay1/lib/alipay_submit.class.php");
            /**************************请求参数**************************/

            //商户订单号，商户网站订单系统中唯一订单号，必填
            $out_trade_no = $orderid;//$_POST['WIDout_trade_no'];
            //订单名称，必填
            $subject =  $vidoeClassInfo['name'];//$_POST['WIDsubject'];
            //付款金额，必填
            $total_fee = $moneys;//$_POST['WIDtotal_fee'];
            //商品描述，可空
            $body = "";
            /************************************************************/

            //构造要请求的参数数组，无需改动
            $parameter = array(
                "service"       => $alipay_config['service'],
                "partner"       => $alipay_config['partner'],
                "seller_id"  => $alipay_config['seller_id'],
                "payment_type"  => $alipay_config['payment_type'],
                "notify_url"    => $alipay_config['notify_urls'],
                "return_url"    => $alipay_config['return_urls'],

               "anti_phishing_key"=>$alipay_config['anti_phishing_key'],
                "exter_invoke_ip"=>$alipay_config['exter_invoke_ip'],
                "out_trade_no"  => $out_trade_no,
                "subject"   => $subject,
                "total_fee" => $total_fee,
                "body"  => $body,
                "_input_charset"    => trim(strtolower($alipay_config['input_charset']))
                //其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
                //如"参数名"=>"参数值"
            );
            //建立请求
            $alipaySubmit = new \AlipaySubmit($alipay_config);
            $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
            echo $html_text;
    /************************************----- END支付宝支付END -----***************************************/
    }
/******************************************************  STR支付宝STR  ****************************************************************/
    /**
    * @函数   wlnotifyurl()
    * @功能   外链支付宝异步回调通知
    * @时间   2019-1-15
    * @作者   高金辉
    */
    public function wlnotifyurl(){
        //引入支付宝第三方配置
        require_once(__DIR__."/Alipay1/alipay.config.php");
        require_once(__DIR__."/Alipay1/lib/alipay_notify.class.php");
        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        // file_put_contents(dirname(__FILE__).'/xml.txt',$_POST); //记录日志 支付成功后查看xml.txt文件是否有内容 如果有xml格式文件说明回调成功
        if($verify_result) {//验证成功
            //商户订单号
            $out_trade_no = $_POST['out_trade_no'];
            //支付宝交易号
            $trade_no = $_POST['trade_no'];
            //交易状态
            $trade_status = $_POST['trade_status'];
            if ($trade_status == 'TRADE_SUCCESS') {
                //判断该笔订单是否在商户网站中已经做过处理
                $res = Db::name('order')->field("person_id,video_class_id,state,person_id")->where("order = '".$out_trade_no."'")->find();
                //判断订单是否已经完成支付
                if ($res['state'] == "1") { //【1.待支付 2.已支付 3.支付失败 4.取消支付】
                    //组装更改订单的数据
                    $data['endtime'] = time(); //订单支付的时间
                    $data['kcdqtime'] = time()+intval(15552000); //购买到期的时间 [在支付时间原基础上增加半年的时间戳] 
                    $data['state'] = "2";
                    //更改订单状态
                    $asd = Db::name('order')->where("order = '".$out_trade_no."'")->update($data);
                }
            }
        }else {
            //验证失败
            echo "fail"; 
            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
        }
    }

/******************************  END支付宝END  ****************************************************************/

/******************************  STR微信支付STR  ****************************************************************/
/**
    * @函数   weixinpay()
    * @功能   微信支付
    * @时间   2019-1-15
    * @作者   高金辉
    *//*微信二维码支付*/
    public function weixinpay(){
        $param=input();
        $user=Session::get('user');
        $person_id =$user['id'];//用户id
        $video_class_id =$param['video_class_id'];//课程id
        $money = $param['money']; //打折前的金额
        $moneys = $param['moneys']; //打折后的金额
        //判断参数是否正常
        if ($person_id == "" || $video_class_id == "" || $moneys == "" || $money == "") {
            echo '<script type="text/javascript">alert(\'传参有误,请重新传参!\');</script>';exit;
        }
        //判断用户是否已经提交过订单
        $payModel=new payModel();
        $where['person_id']=$person_id;
        $where['video_class_id']=$video_class_id;
        $historyOrder=$payModel->historyOrder($where);
        //获取课程信息
        $vidoeClassInfo=$payModel->vidoeClassInfo($video_class_id);
        //生成订单信息
    	$orderid = time().rand(1000000000,9999999999);
    	$data["order"] = $orderid;//唯一订单号
    	$data["grade_id"] = $vidoeClassInfo['grade_id'];//订单的商品所属年级
    	$data["subject_id"] = $vidoeClassInfo['subject_id'];//订单的商品所属的科目
    	$data["money"] = $moneys;//订单的金额
    	$data["person_id"] = $person_id;//购买用户的ID
    	$data["state"] = 1;//订单的状态 【1.待支付 2.已支付 3.支付失败 4.取消支付】
    	$data["strtime"] = time();//订单生成的时间
    	$data["kcdqtime"] = time()+15552000;//课程到期的时间有效期半年
    	$data["payment"] = 2;//支付方式1支付宝app支付2支付宝扫码支付3微信支付4微信扫码支付
    	$data['textbook_id'] = $vidoeClassInfo['edition_id'];//商品版本id
    	$data['learn_id'] = $vidoeClassInfo['learn_id'];//商品学段id
    	$data['semester'] = $vidoeClassInfo['Semester'];//1上学期2下学期
    	$data['video_class_id'] = $video_class_id;//对应课程主id
        if($historyOrder){
        	//订单存在判断订单是否已经支付
        	if($historyOrder['state']==2){
        		//订单支付成功判断订单是否已经到期
        		if($historyOrder['kcdqtime']>time()){
        			//订单支付成功且订单没有到期
        			echo '<script type="text/javascript">alert(\'您已经购买过本课程，可直接到个人中心学习！\');</script>';exit;
        		}else{
        			//订单支付成功但是订单到期了，重新生成新的订单！
        			$cg = $payModel->insertOrder($data);
        			if (!$cg) {
			            echo '<script type="text/javascript">alert(\'订单生成失败,请重新提交!\');</script>';exit;
			        }
        		}
        	}else{
                //订单存在但是没有支付
                $cg = $payModel->insertOrder($data);
                if (!$cg) {
                    echo '<script type="text/javascript">alert(\'订单生成失败,请重新提交!\');</script>';exit;
                }
            }
        }else{
        	//创建新订单
        	$cg = $payModel->insertOrder($data);
        	if (!$cg) {
	            echo '<script type="text/javascript">alert(\'订单生成失败,请重新提交!\');</script>';exit;
	        }
        }
        if($cg){
            //调用微信统一下单
            $notify = new \NativePay(); //实例化微信类
            $input = new \WxPayUnifiedOrder();
            $input->SetBody($vidoeClassInfo['name']);
            $input->SetAttach($vidoeClassInfo['name']);
            $input->SetOut_trade_no($orderid);
            $input->SetTotal_fee($moneys*100);
            $input->SetTime_start(date("YmdHis"));
            $input->SetTime_expire(date("YmdHis", time() + 600));
            $input->SetGoods_tag($vidoeClassInfo['name']);
            $input->SetNotify_url("http://www.ywd100.com/index/Pay/uppaystatus"); // /mid/{$mid}
            $input->SetTrade_type("NATIVE");
            $input->SetProduct_id($orderid);
            $result = $notify->GetPayUrl($input);
            $codeurl = $result["code_url"];
            if ($result["return_code"] == "SUCCESS") {
                return jsonMsg($res['r_order'],0,$codeurl);
            }else{
                return jsonMsg('二维码生成失败,请刷新后重试!',1);
            }
        }
    }

    //修改订单的支付状态
    public function uppaystatus(){
        $xml = $GLOBALS['HTTP_RAW_POST_DATA']; //返回的xml
        // file_put_contents(dirname(__FILE__).'/xml.txt',$xml); //记录日志 支付成功后查看xml.txt文件是否有内容 如果有xml格式文件说明回调成功
        $xmlObj=simplexml_load_string($xml,'SimpleXMLElement',LIBXML_NOCDATA);
        $xmlArr=json_decode(json_encode($xmlObj),true);
        $out_trade_no=$xmlArr['out_trade_no']; //订单号
        $result_code=$xmlArr['result_code']; //状态

        if($result_code == 'SUCCESS'){
            //判断该笔订单是否在商户网站中已经做过处理
            $res = Db::name('order')->field("person_id,video_class_id,state,person_id")->where("order = '".$out_trade_no."'")->find();
            //判断订单是否已经完成支付
            if ($res['state'] == "1") { //【1.待支付 2.已支付 3.支付失败 4.取消支付】
                //组装更改订单的数据
                $data['endtime'] = time(); //订单支付的时间
                $data['kcdqtime'] = time()+intval(15552000); //购买到期的时间 [在支付时间原基础上增加半年的时间戳] 
                $data['state'] = "2";
                //更改订单状态
                $asd = Db::name('order')->where("order = '".$out_trade_no."'")->update($data);
            }
            echo "success";     //请不要修改或删除
        }else{
            //验证失败
            echo "fail"; 
            exit;
        }
    }
    
    /*******************************  END微信支付END  ****************************************************************/
    /**
     * @函数   weixinpay1()
     * @功能   微信支付
     * @时间   2019-1-15
     * @作者   高金辉
     */
    public function weixinpay1(){
        $param=input();
        $user=Session::get('user');
        $person_id =$user['id'];//用户id
        $video_class_id =$param['video_class_id'];//课程id
        $money = $param['money']; //打折前的金额
        $moneys = $param['moneys']; //打折后的金额
        //判断参数是否正常
        if ($person_id == "" || $video_class_id == "" || $moneys == "" || $money == "") {
            echo '<script type="text/javascript">alert(\'传参有误,请重新传参!\');</script>';exit;
        }
        //判断用户是否已经提交过订单
        $payModel=new payModel();
        $where['person_id']=$person_id;
        $where['video_class_id']=$video_class_id;
        $historyOrder=$payModel->historyOrder($where);
        //获取课程信息
        $vidoeClassInfo=$payModel->vidoeClassInfo($video_class_id);
        //生成订单信息
    	$orderid = time().rand(1000000000,9999999999);
    	$data["order"] = $orderid;//唯一订单号
    	$data["grade_id"] = $vidoeClassInfo['grade_id'];//订单的商品所属年级
    	$data["subject_id"] = $vidoeClassInfo['subject_id'];//订单的商品所属的科目
    	$data["money"] = $moneys;//订单的金额
    	$data["person_id"] = $person_id;//购买用户的ID
    	$data["state"] = 1;//订单的状态 【1.待支付 2.已支付 3.支付失败 4.取消支付】
    	$data["strtime"] = time();//订单生成的时间
    	$data["kcdqtime"] = time()+15552000;//课程到期的时间有效期半年
    	$data["payment"] = 2;//支付方式1支付宝app支付2支付宝扫码支付3微信支付4微信扫码支付
    	$data['textbook_id'] = $vidoeClassInfo['edition_id'];//商品版本id
    	$data['learn_id'] = $vidoeClassInfo['learn_id'];//商品学段id
    	$data['semester'] = $vidoeClassInfo['Semester'];//1上学期2下学期
    	$data['video_class_id'] = $video_class_id;//对应课程主id
        if($historyOrder){
        	//订单存在判断订单是否已经支付
        	if($historyOrder['state']==2){
        		//订单支付成功判断订单是否已经到期
        		if($historyOrder['kcdqtime']>time()){
        			//订单支付成功且订单没有到期
        			echo '<script type="text/javascript">alert(\'您已经购买过本课程，可直接到个人中心学习！\');</script>';exit;
        		}else{
        			//订单支付成功但是订单到期了，重新生成新的订单！
        			$cg = $payModel->insertOrder($data);
        			if (!$cg) {
			            echo '<script type="text/javascript">alert(\'订单生成失败,请重新提交!\');</script>';exit;
			        }
        		}
        	}
        }else{
        	//创建新订单
        	$cg = $payModel->insertOrder($data);
        	if (!$cg) {
	            echo '<script type="text/javascript">alert(\'订单生成失败,请重新提交!\');</script>';exit;
	        }
        }
        $openId = empty($param['openId']) ? "0" : $param['openId'];
        //①、获取用户openid
        $tools = new \JsApiPay();
        //②、统一下单
        $input = new \WxPayUnifiedOrder();
        $input->SetBody($vidoeClassInfo['name']);
        $input->SetAttach($vidoeClassInfo['name']);
        $input->SetOut_trade_no($orderid);
        $input->SetTotal_fee($moneys*100);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag($vidoeClassInfo['name']);
        $input->SetNotify_url("http://".$_SERVER['SERVER_NAME']."/index/pay/uppaystatus");
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $order = \WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($order); 
        return jsonMsg('请求成功',0,$jsApiParameters);exit;    
    }          
    /*手机端打开支付页面但是不是在微信端打开的但是要发起微信支付*/
    public function wlalipayH5(){
        $param=input();
        $user=Session::get('user');
        $person_id =$user['id'];//用户id
        $video_class_id =$param['video_class_id'];//课程id
        $money = $param['money']; //打折前的金额
        $moneys = $param['moneys']; //打折后的金额
        //判断参数是否正常
        if ($person_id == "" || $video_class_id == "" || $moneys == "" || $money == "") {
            echo '<script type="text/javascript">alert(\'传参有误,请重新传参!\');</script>';exit;
        }
        //判断用户是否已经提交过订单
        $payModel=new payModel();
        $where['person_id']=$person_id;
        $where['video_class_id']=$video_class_id;
        $historyOrder=$payModel->historyOrder($where);
        //获取课程信息
        $vidoeClassInfo=$payModel->vidoeClassInfo($video_class_id);
        //生成订单信息
    	$orderid = time().rand(1000000000,9999999999);
    	$data["order"] = $orderid;//唯一订单号
    	$data["grade_id"] = $vidoeClassInfo['grade_id'];//订单的商品所属年级
    	$data["subject_id"] = $vidoeClassInfo['subject_id'];//订单的商品所属的科目
    	$data["money"] = $moneys;//订单的金额
    	$data["person_id"] = $person_id;//购买用户的ID
    	$data["state"] = 1;//订单的状态 【1.待支付 2.已支付 3.支付失败 4.取消支付】
    	$data["strtime"] = time();//订单生成的时间
    	$data["kcdqtime"] = time()+15552000;//课程到期的时间有效期半年
    	$data["payment"] = 2;//支付方式1支付宝app支付2支付宝扫码支付3微信支付4微信扫码支付
    	$data['textbook_id'] = $vidoeClassInfo['edition_id'];//商品版本id
    	$data['learn_id'] = $vidoeClassInfo['learn_id'];//商品学段id
    	$data['semester'] = $vidoeClassInfo['Semester'];//1上学期2下学期
    	$data['video_class_id'] = $video_class_id;//对应课程主id
        if($historyOrder){
        	//订单存在判断订单是否已经支付
        	if($historyOrder['state']==2){
        		//订单支付成功判断订单是否已经到期
        		if($historyOrder['kcdqtime']>time()){
        			//订单支付成功且订单没有到期
        			echo '<script type="text/javascript">alert(\'您已经购买过本课程，可直接到个人中心学习！\');</script>';exit;
        		}else{
        			//订单支付成功但是订单到期了，重新生成新的订单！
        			$cg = $payModel->insertOrder($data);
        			if (!$cg) {
			            echo '<script type="text/javascript">alert(\'订单生成失败,请重新提交!\');</script>';exit;
			        }
        		}
        	}
        }else{
        	//创建新订单
        	$cg = $payModel->insertOrder($data);
        	if (!$cg) {
	            echo '<script type="text/javascript">alert(\'订单生成失败,请重新提交!\');</script>';exit;
	        }
        }
        $appid = 'wx6c21cac1d603c940';
        $mch_id = '1431693102';//商户号
        $key = '291z31cfbf3y57f9462e4fa6cf7dc7ed';//商户key
        $notify_url = "http://www.ywd100.com/index/pay/h5success";//回调地址
        $wechatAppPay = new \wechatAppPay($appid, $mch_id, $notify_url, $key);
        $params['body'] = '一点通在线课';                       //商品描述
        $params['out_trade_no'] = $orderid;   //自定义的订单号
        $params['total_fee'] = $moneys*100;                    //订单金额 只能为整数 单位为分
        $params['trade_type'] = 'MWEB';                   //交易类型 JSAPI | NATIVE | APP | WAP 
        $params['scene_info'] = '{"h5_info": {"type":"Wap","wap_url": "https://api.lanhaitools.com/wap","wap_name": "一点通在线课"}}';
        $result = $wechatAppPay->unifiedOrder( $params );
        $url = $result['mweb_url'].'&redirect_url=http%3A%2F%2Fwww.ywd100.com';//redirect_url 是支付完成后返回的页面
       /* return $url;    */  
        header("Location: $url"); 
    }
    /*h5支付成功回调*/
    public function h5success(){
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
         $xmlObj=simplexml_load_string($xml,'SimpleXMLElement',LIBXML_NOCDATA);
         $xmlArr=json_decode(json_encode($xmlObj),true);
         $out_trade_no=$xmlArr['out_trade_no']; //订单号
         $result_code=$xmlArr['result_code']; //状态 
         file_put_contents('gaojinhui_wxpay.log',var_export($xmlArr,TRUE),FILE_APPEND);
       if(strtolower($result_code)=='success'){
       		//判断该笔订单是否在商户网站中已经做过处理
            $res = Db::name('order')->field("person_id,video_class_id,state,person_id")->where("order = '".$out_trade_no."'")->find();
            //判断订单是否已经完成支付
            if ($res['state'] == "1") { //【1.待支付 2.已支付 3.支付失败 4.取消支付】
                //组装更改订单的数据
                $data['endtime'] = time(); //订单支付的时间
                $data['kcdqtime'] = time()+intval(15552000); //购买到期的时间 [在支付时间原基础上增加半年的时间戳] 
                $data['state'] = "2";
                //更改订单状态
                $asd = Db::name('order')->where("order = '".$out_trade_no."'")->update($data);
            }
            echo "success";
            exit;
        }else{
            echo 'fail';
            exit;   
        }
    }
}