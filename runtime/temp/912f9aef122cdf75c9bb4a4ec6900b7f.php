<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:81:"E:\phpStudy\WWW\ywd100\application/../Template/mobile/index\course\coursePay.html";i:1598606565;}*/ ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="/static/mobile/js/mui.min.js"></script>
    <link href="/static/mobile/css/mui.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="/static/mobile/css/style.css">
    <title>E点就通名校名师同步课堂</title>
    <style>
        /* .coursePay_con{
            text-align: center;
            margin-top: 3rem;
        } */
        
    </style>
</head>
<body class="mui-ios mui-ios-11 mui-ios-11-0">
    <div class="coursePay">
        <div class="coursepay_surplus">
            <div class="surplus_content">
                <p class="surplus_title">课程剩余名额 : </p>    
                <p class="surplus_number">仅剩<span class="course_number"></span>名</p>
            </div>
            <!-- <div class="surplus_content">
                <div class="surplus_number time">
                    <p id="time_d"></p>天
                    <p id="time_h"></p>:
                    <p id="time_m"></p>:
                    <p id="time_s"></p>
                </div>
                <div class="surplus_number time_overdue"></div>
                <p class="surplus_title">课程剩余时间</p>
            </div> -->
        </div>


        <div class="coursepay_form">
            <div class="coursepay_form_row">
                <label>手机号码</label>
                <!-- <input class="coursepay_form_row_ipt" type="text" placeholder="请输入手机号"> -->
                <input class="coursepay_form_row_ipt verification_phone" type="text" placeholder="请输入手机号">
            </div>
            <div class="coursepay_form_row_line"></div>
            <div class="coursepay_form_row">
                <label class="verification">验证码</label>
                <input class="coursepay_form_row_ipt verification_code1" type="text" placeholder="请输入验证码">
                <input class="verification_code" type="button" value="获取验证码" onclick="VerifyCode(this)">
                <!-- <div class="verification_code">获取验证码</div> -->
            </div>
        </div>


        <div class="coursepay_courseinfor">
            <p class="courseinfor_title">课程信息</p>
            <div class="courseinfor_cont">
                <div class="courseinfor_cont_left">
                    7-9年级名校名师同步课程
                    <span>(共5节)</span>
                </div>
                <div class="courseinfor_cont_right"><span>￥</span>9.9</div>
            </div>
        </div>

        <div class="coursepay_subject_all">
            <div class="coursepay_subject">
                <p class="subject_title">本课程包括以下学科</p>
                <p class="subject_content">语文、数学、英语、物理、化学</p>
            </div>
            <div class="subject_img">
                <img src="/static/mobile/images/yuwen.png" alt="" >
                <img src="/static/mobile/images/shuxue.png" alt="" >
                <img src="/static/mobile/images/yingyu.png" alt="" >
                <img src="/static/mobile/images/wuli.png" alt="" >
                <img src="/static/mobile/images/huaxue.png" alt="" >
            </div>
        </div>
    </div>

    <div class="coursepay_pay">
        <p class="coursepay_pay_left">总计：￥<span>9.9</span></p>
        <div class="coursepay_pay_primary" id="confir_pay">确认支付</div>
    </div>

    <div class="mui-backdrop"></div>
    <div class="coursepay_pay_model">
        <p class="model_title">选择支付方式</p>
        <ul class="model_menu">
            <li class="menu_li">
                <div class="model_menu_li_left">
                    <img src="/static/mobile/images/zfb.png" alt="">
                    支付宝
                </div>
                <div class="model_menu_li_right">
                    <i class="iconfont iconconfirm-line"></i>
                </div>
            </li>
            <li class="menu_li">
                <div class="model_menu_li_left">
                        <img src="/static/mobile/images/wx.png" alt="">
                    微信
                </div>
                <div class="model_menu_li_right">
                    <i class="iconfont iconconfirm-line"></i>
                </div>
            </li>
        </ul>
    </div>
    <?php if(isset($openId)){?>
        <input id="openid" type="hidden" value="<?php echo $openId?>" >
    <?php }?>



    <div class="default_coursePay_model">
        <div class="default_coursePay_model_content">
            <div class="default_coursePay_model_content_saoma display_none">
                <div class="default_coursePay_model_close">+</div>
                <div class="coursePay_model_content_img"></div>
                <div>请扫码支付</div>
            </div>
            
            <div class="default_coursePay_model_content_click display_none">
                <div class="default_coursePay_model_close">+</div>
                <img class="model_content_img" src="/static/default/images/logo.png" alt="">
                <p class="model_content_text">您已经购买了该课程</p>
                <div class="model_content_btn mobile_ok">我知道啦</div>
            </div>

            <div class="default_coursePay_model_content_yzm display_none">
                    <div class="default_coursePay_model_close">+</div>
                <img class="model_content_img" src="/static/default/images/logo.png" alt="">
                <p class="model_content_text">验证码错误，请重新获取</p>
                <div class="model_content_btn" id="yzm_know">我知道啦</div>
            </div>
            <div class="default_coursePay_model_content_success display_none">
                    <div class="default_coursePay_model_close">+</div>
                <img class="model_content_img" src="/static/default/images/logo.png" alt="">
                <p class="model_content_text">恭喜您购买成功</p>
                <p class="model_content_text_p">账户名及密码稍后会以短信</p>
                <p class="model_content_text_p model_content_text_p1">方式发送给您，请注意查收！</p>
                <div class="model_content_btn" id="success_know">我知道啦</div>
            </div>
        </div>
    </div>
</body>
</html>
<script src="/static/mobile/js/jquery-2.2.3.js"></script>
<script>
    function GetRandomNum(Min,Max){   
        var Range = Max - Min;   
        var Rand = Math.random();   
        return(Min + Math.round(Rand * Range));   
    }   
    var num = GetRandomNum(3,30);   
    $('.course_number').append(num);
    

    function is_weixin(){
        var ua = navigator.userAgent.toLowerCase();
        if(ua.match(/MicroMessenger/i)=="micromessenger") {
            //获取code
            var url = '<?php echo $url?>';
            if (url != '') {
                window.location = url;
            }
        }
    }
    is_weixin();
    $('.model_content_btn').click(function(){
        $('.default_coursePay_model').hide();
    })
    $('.default_coursePay_model_close').click(function(){
        $('.default_coursePay_model').hide();
    })
    //活动倒计时
    $(function(){ 
        show_time();
    }); 
    function show_time(){
        var time_start = new Date().getTime(); //设定当前时间
        var time_end =  new Date("2019/10/01 00:00:00").getTime(); //设定目标时间
        // 计算时间差 
        var time_distance = time_end - time_start; 
        if( time_distance <= 0 ){
            $(".time").hide();  
            $('.time_overdue').text('活动已过期');
        }
        // 天
        var int_day = Math.floor(time_distance/86400000) 
        time_distance -= int_day * 86400000; 
        // 时
        var int_hour = Math.floor(time_distance/3600000) 
        time_distance -= int_hour * 3600000; 
        // 分
        var int_minute = Math.floor(time_distance/60000) 
        time_distance -= int_minute * 60000; 
        // 秒 
        var int_second = Math.floor(time_distance/1000) 
        // 时分秒为单数时、前面加零 
        if(int_day <= 0){ 
            int_day = "0" + int_day; 
        }
        if(int_hour < 10){ 
            int_hour = "0" + int_hour; 
        } 
        if(int_minute < 10){ 
            int_minute = "0" + int_minute; 
        } 
        if(int_second < 10){
            int_second = "0" + int_second; 
        }
        // 显示时间 
        $("#time_d").html(int_day);
        $("#time_h").html(int_hour); 
        $("#time_m").html(int_minute);
        $("#time_s").html(int_second); 
        // 设置定时器
        setTimeout("show_time()",1000); 
    }

    //获取验证码
    function VerifyCode(obj){
        var phone = $('.coursepay_form_row_ipt').val();
        var myreg=/^[1][3,4,5,7,8][0-9]{9}$/;
        var seconds = 60;
        if (!myreg.test(phone)) {
            //弹出弹窗提示用户输入正确手机号
            // alert('请输入正确格式手机号');
            showModel();
            $('.model_content_text').text('请输入正确格式手机号');
            return false;
        }
        //请求接口获取验证码
        $.post('/index/login/VerifyCodePay',{phone:phone},function(res){
            if(res.error_code==1)
            {
                //验证码获取成功
                $(obj).val(seconds+'S');
                $(obj).attr("style","background:rgba(218,216,219,1) !important;color:#373538 !important;");
                $(obj).attr("disabled", true);//禁用按钮

                var time=60;
                $('.verification_code').val("已发送("+time+"s)")
                var interval=setInterval(function () {
                    if (time >=1 && time <= 60 ) {
                        time--;
                        $('.verification_code').val("已发送("+time+"s)").attr('disabled', true)
                    } else {
                        $('.verification_code').val("重新获取").attr('disabled', false)
                        clearInterval(interval);
                    }
                }, 1000);
            }else{
                //验证码获取失败再次获取
                $(obj).attr("style","background:#E66817 !important;color:#fff !important;");
                $(obj).val("重新获取").attr("disabled", false);//启用按钮
            }
        })
    }

    //点击确认支付显示支付方式并选择支付方式发起支付
    $('.model_menu li').click(function(){
        var index = $(this).index();
        $(this).find('.model_menu_li_right').addClass('menu_li_check')
               .parent().siblings().find('.model_menu_li_right').removeClass('menu_li_check');
        var phone =  $('.verification_phone').val();
        var money='9.9';
        if(index==0){
            //支付宝支付
            if(isMobile())
            {
                if(isWeixin())
                {
                    //手机端在微信内选中支付宝支付跳转到中间页面
                    // showModel();
                    // $('.model_content_text').text('请用手机浏览器发起支付宝支付!');
                    // show_Model();
                    // return false;
                    window.location.href="/index/course_pay/pay?phone="+phone+"&register_from=3&payment=1&money="+money;
                    // $.post('/index/course_pay/pay',{phone:phone,payment:1,register_from:1,money:money},function(res) {
                        
                    // })
                }else{
                    //手机端微信外选中支付宝支付跳转到支付宝内发起支付
                    $.post('/index/course_pay/createOrder',{phone:phone,payment:1,register_from:3,money:money},function(res){
                        if(res.error_code == 0)
                        {
                            $('.default_coursePay_model').show();
                            $('.default_coursePay_model_content_yzm').hide();
                            $('.default_coursePay_model_content_success').hide();
                            $('.default_coursePay_model_content_saoma').hide();
                            $('.default_coursePay_model_content_click').show();
                            $('.mui-backdrop').fadeOut(500);
                            $('.coursepay_pay_model').animate({height:'0'}, 500);
                            $('.default_coursePay_model_content_click .default_coursePay_model_close').click(function(){
                                location = location;
                            })
                            $('.default_coursePay_model_content_click .mobile_ok').click(function(){
                                location = location;
                            })
                        }else{
                            const div = document.createElement('div');
                            div.innerHTML = res; // html code
                            document.body.appendChild(div);
                            document.forms.alipaysubmit.submit();
                        }
                    })
                }
            }else{
                if(isWeixin())
                {
                    // showModel();
                    // $('.model_content_text').text('请用浏览器打开发起支付!');
                    // return false;
                    window.location.href="/index/course_pay/pay?phone="+phone+"&register_from=3&payment=1&money="+money;
                }else{
                    //pc端发起支付宝二维码支付
                    $.post('/index/course_pay/createOrder',{phone:phone,payment:2,register_from:1,money:money},function(res){
                            if(res.error_code==0)
                            {
                                showModel();
                                $('.model_content_text').text(res.msg);
                                return false;
                            }
                            const div = document.createElement('div');
                            div.innerHTML = res; // html code
                            document.body.appendChild(div);
                            document.forms.alipaysubmit.submit();
                    })
                }   
            }
        }else{
            //微信支付
            if(isMobile())
            {
                //是手机端选中微信支付
                if(isWeixin()){
                    //手机端在微信内选中微信支付发起微信支付
                    $.post('/index/course_pay/courseWxPay',{phone:phone,register_from:3,money:money,openId:$('#openid').val()},function(res){
                        if(res.error_code == 0) {	
                            var data = JSON.parse(res.data);
                            //调用微信支付
                            WeixinJSBridge.invoke(
                                    'getBrandWCPayRequest',
                                    data,
                                    function(resq){
                                        if(resq.err_msg == "get_brand_wcpay_request:ok" ) {
                                            window.location.href='/index/course_pay/courseSuccess';
                                            //alert("您已成功购买课程，账户名及密码稍后会以短信方式发送给您，请注意查收！");					           	   
                                        }else{
                                            // alert("支付失败，请重新支付！");
                                            showModel();
                                            $('.model_content_text').text('支付失败，请重新支付！');
                                        } 
                                    }
                                );	
                        } else {
                            // alert(res.message);
                            showModel();
                            $('.model_content_text').text(res.message);
                        }
                    })
                }else{
                    //手机端在微信外选中微信支付发起微信H5支付
                    window.location.href="/index/course_pay/coursePayH5?phone="+phone+"&register_from=3&money="+money;
                }
            }else{
                //pc端发起微信二维码支付
                $.post('/index/course_pay/courseWxCodePay',{phone:phone,register_from:1,money:money},function(res){
                    if(res.error_code == 1)
                    {
                        //$('.coursePay_model_content_img').append('<img src="http://jun.ydtkt.com/WxPay/example/qrcode.php?data='+res.data+'"/>');
                        $('.coursePay_model_content_img').append('<img src="/WxPay/example/qrcode.php?data='+res.data+'"/>');
                        $('.default_coursePay_model').show();
                        $('.default_coursePay_model_content_yzm').hide();
                        $('.default_coursePay_model_content_success').hide();
                        $('.default_coursePay_model_content_saoma').show();
                        $('.mobile_click').hide();
                        $('.default_coursePay_model_close').click(function(){
                            $('.default_coursePay_model').hide();
                            $('.coursePay_model_content_img img').remove();
                        })
                    }
                })
            }
        }
    })

    $('#confir_pay').click(function(){
        // $('.mui-backdrop').fadeIn(500);
        // $('.coursepay_pay_model').animate({height:'2rem'}, 500);
        var phone = $('.coursepay_form_row_ipt').val();
        var code = $('.verification_code1').val();
        if(phone == '')
        {
            // alert('手机号不能为空！');
            showModel();
            $('.model_content_text').text('手机号不能为空！');
            return false;
        }
        if(code == '')
        {
            showModel();
            $('.model_content_text').text('验证码不能为空！');
            // alert('验证码不能为空！');
            return false;
        }
        //验证验证码是否正确
        $.post('/index/login/verificationCode',{phone:phone,code:code},function(res){
            if(res.error_code==1)
            {
                // alert('获取验证嘛成功');
                //验证码正确出现支付方式
                $('.mui-backdrop').fadeIn(500);
                $('.coursepay_pay_model').animate({height:'2rem'}, 500);
            }else{
                //验证码失败弹窗提示验证码失败
                // alert('验证码错误请重新获取！');
                showModel();
                $('.model_content_text').text('验证码错误请重新获取！');
            }
        })      
    })
    $('.mui-backdrop').click(function(){
        $('.mui-backdrop').fadeOut(500);
        $('.coursepay_pay_model').animate({height:'0'}, 500);
    })

    //弹窗
    function showModel(){
        $('.default_coursePay_model').show();
        $('.default_coursePay_model_content_yzm').show();
        $('.default_coursePay_model_content_success').hide();
        $('.default_coursePay_model_content_saoma').hide();
        $('.default_coursePay_model_content_click').hide();
    }



    function isMobile()
    {
        if ((navigator.userAgent.match(/(phone|pad|pod|iPhone|iPod|ios|iPad|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i))) {
        
            return true;
        }else {
        
            return false;
        }
    }
    function isWeixin()
    {
        var ua = navigator.userAgent.toLowerCase(); 
        if(ua.match(/MicroMessenger/i) == 'micromessenger'){ 
            return true; 
        }else{ 
            return false; 
        }
    }
</script>
