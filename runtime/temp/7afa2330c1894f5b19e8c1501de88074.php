<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:82:"E:\phpStudy\WWW\ywd100\application/../Template/default/index\course\coursePay.html";i:1609384556;s:64:"E:\phpStudy\WWW\ywd100\Template\default\index\public\header.html";i:1598606565;s:64:"E:\phpStudy\WWW\ywd100\Template\default\index\public\footer.html";i:1598606565;}*/ ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <meta name="applicable-device" content="pc">
  <meta name="renderer" content="webkit"/>
  <meta name="force-rendering" content="webkit"/>
  <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1"/>
  <title>E点就通名校名师同步课堂</title>
    <!-- <link rel="icon" type="image/x-icon" href="./images/bit.ico" /> -->
  <link rel="stylesheet" href="/static/default/css/layui.css">
  <link rel="stylesheet" href="/static/default/css/common.css">
  <link rel="stylesheet" href="/static/default/css/course.css">
  <link rel="stylesheet" href="/static/default/css/style.css">
  <style>
      .coursePay_model_content_img img{
          width: 100%;
          height: 100%;
      }
      .display_none{
        display: none;
      }
  </style>

  
</head>
<body>
  <!-- 公共头部 -->
    
  <div class="common-header">
    <div class="common-header-contain">
      
      <ul class="layui-nav common-header-list" lay-filter="">
        <div class="logo"><a href="/"><img src="/static/default/images/logo.png" alt="" /></a></div>
        <li class="layui-nav-item common-header-home layui-this"><a href="/">首页</a></li>
        <li class="layui-nav-item"><a href="/index/course/course?pageNum=10">同步课程</a></li>
        <li class="layui-nav-item"><a href="/index/teachers/teachersTeam">名校名师</a></li>
        <!-- <li class="layui-nav-item"><a href="/index/service/service">客服验证</a></li>
        <li class="layui-nav-item"><a href="javascript:;">答疑</a></li> -->
        <li class="layui-nav-item"><a href="javascript:;">个人中心</a></li>
        <li class="golobal-search">
          <form class="layui-form index-nav-form" action="/index/index/glodalsearch">
            <div class="layui-input-block nav-form-global">
              <button class="layui-btn nav-button iconfont icon-sousuo" lay-submit lay-filter="searchMsg"></button>
              <input type="text" name="gloalSearch" required  placeholder="课程名称" autocomplete="off" class="index-nav-search">
            </div>
          </form>
        </li>
      </ul>
        <!-- 登录注册部分 -->
        <div class="common-header-right">
          <div class="login-register">
             <?php if((!isset($user))): ?>
            <!-- 登录前 -->
            <div class="common-before-login clearfix" style="display: block;">
                <div class="header-login-btn">登录</div>
                <div class="header-register-btn">注册</div>
            </div>
            <?php else: ?>
              <!-- 登录后 -->
              <div class="common-after-login active clearfix">
                <div class="after-login-message">
                  <a class="after-login-message-news" href="/index/person/person?type=1"><i class="iconfont icon-xiaoxitongzhitixinglingshenglingdang"></i><span></span></a>
                </div>
                <div class="after-login-position">
                  <input type="hidden" id="person_id" value="<?php echo !empty($personInfo['id'])?$personInfo['id'] : 0; ?>"/>
                  <a href="javascript:;" class="personal-id">
                    <img src="<?php echo empty($personInfo['litpic'])?'/upload/uploads/photo.jpg':'http://ydtvlitpic.ydtkt.com/'.$personInfo['litpic']; ?>" class="layui-nav-img">
                    <!--  -->
                  </a>
                  <div class="exit-login">
                    <div class="exit-login-div">
                      <i class="iconfont icon-exit icon-tuichu"></i>
                      退出登录
                    </div>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

    </div>
  </div>
  <!-- 登录注册模块 -->
  <div class="login-register-modal"></div>
  <div class="register-login-contain">
    <div class="register-login-title clearfix">
      <div class="register-login-button current">注册</div>
      <div class="register-login-button">登录</div>
      <!-- <div class="forget-password-title"><h5>学员登录</h5></div> -->
    </div>
    <ul class="register-login-content clearfix">
      <!-- 注册 -->
      <li class="register-login-content-lis">
        <form class="layui-form register-form" action="">
          <div class="layui-form-item">
            <input type="text" name="registerPhone" id="register-phone-num" required  lay-verify="required|phone" placeholder="请输入手机号" autocomplete="off" class="layui-input index-register-input">
          </div>
          <div class="layui-form-item index-get-code">
            <input type="text" name="registerCode" id="register-get-code" required maxlength="6" lay-verify="required" placeholder="请输入验证码" autocomplete="off" class="layui-input index-register-input">
            <input type="button" class="register-btncode get-phonecode-reg get-phonecode-reg1" data-id=1 value="获取验证码">
          </div>
          <div class="layui-form-item">
            <input type="password" name="registerPwd" id="register-pwd" required  lay-verify="required" placeholder="请输入密码" autocomplete="off" class="layui-input index-register-input">
          </div>
          
          <div class="layui-form-item index-banner-button index-register-button">
            <div class="index-layui-btn" lay-submit lay-filter="register" id='common-register'>注册</div>
          </div>
        </form>
        <p class="register-xieyi">点击注册表示已阅读并同意<a href="/index/login/agreement">《用户协议》</a></p>
      </li>
      <!-- 登录 -->
      <li class="register-login-content-lis">
        <!-- 密码登录 -->
        <div class="login-pwd active">
          <form class="layui-form" action="" >
            <div class="layui-form-item">
              <input type="text" name="loginPhone" id="login-phone-num" required  lay-verify="required" placeholder="请输入手机号/卡号" autocomplete="off" class="layui-input index-login-input">
            </div>
            <div class="layui-form-item index-get-code">
              <input type="password" name="loginPwd" id="login-pwd" required lay-verify="required"  placeholder="请输入密码" autocomplete="off" class="layui-input index-login-input">
              <a class="login-methods " href="javascript:;">忘记密码？</a>
              <a class="login-code-methods login-methods-active" href="javascript:;">使用验证码登录</a>
            </div>
            <div class="layui-form-item index-banner-button">
              <div class="index-layui-btn" lay-submit lay-filter="pwdLogin" id='common-login'>登录</div>
            </div>
          </form>
        </div>
        <!-- 验证码登录 -->
        <div class="login-code">
          <form class="layui-form" action="" >
            <div class="layui-form-item">
              <input type="text" id="login-phone" name="loginCodePhone" id="login-phone-num" required  lay-verify="required" placeholder="请输入手机号" autocomplete="off" class="layui-input index-code-input">
            </div>
            <div class="layui-form-item index-get-code">
              <input type="text" name="loginCode" id="login-get-code" required lay-verify="required" maxlength="6" placeholder="请输入验证码" autocomplete="off" class="layui-input index-code-input">
              <input type="button" class="login-btncode get-phonecode get-phonecode2" data-id=2 value="获取验证码">
              <!-- <a class="login-methods" href="javascript:;">忘记密码？</a> -->
              <a class="login-pwd-methods login-methods-active" href="javascript:;">使用密码登录</a>
            </div>
            <div class="layui-form-item index-banner-button">
              <div class="index-layui-btn" lay-submit lay-filter="codeLogin" id='common-login-code'>登录</div>
            </div>
          </form>
        </div>
      </li>
    </ul>
  </div>
  <!-- 忘记密码 -->
  <div class="forget-password">
    <div class="forget-password-title"><h5>找回密码</h5></div>
    <div class="forget-pwd"> 
      <form class="layui-form forget-pwd-form" action="" >
        <div class="layui-form-item">
          <input type="text" id="forget-phone" name="forgetrPhone" id="register-phone-num" required  lay-verify="required|phone" placeholder="请输入手机号" autocomplete="off" class="layui-input index-forget-input">
        </div>
        <div class="layui-form-item index-get-code">
          <input type="text" value="" name="forgetCode" id="forget-get-code" required lay-verify="required" maxlength="6" placeholder="请输入验证码"  autocomplete="new-password" class="layui-input index-forget-input">
          <input type="button" class="forget-btncode get-phonecode get-phonecode3" data-id=3 value="获取验证码">
        </div>
        <div class="layui-form-item">
          <input type="password" name="forgetPwd" id="forget-pwd" required  lay-verify="required" placeholder="请输入新密码"  autocomplete="new-password" class="layui-input index-forget-input">
        </div>
        <div class="layui-form-item index-banner-button">
          <div class="index-layui-btn" lay-submit lay-filter="forget" id='common-login-forget'>提交</div>
        </div>
      </form>
    </div>
  </div>

  <script>
    window.onbeforeunload =function(){
      var uid = $('#person_id').val();
      $.post('/index/index/closeBrowserIpLog',{uid:uid})
    }
  </script>
<!-- 课程详情 -->
<div class="course-shows clearfix">
  <!-- 当前位置 -->
  <div class="course-shows-top clearfix">
    <div class="course-shows-position">
        <a href="javascript:location.reload();">当前位置:</a>
        <a href="../index/index.html">首页></a>
        <a href="../course/course.html">同步课程></a>
        <a href="javascript:location.reload();" class="course-shows-position-now">支付页面</a>
    </div>
  </div>
  <div class="default_coursePay">
    <div class="default_coursePay_center clearfix">

        <div class="coursePay_surplus_all clearfix">
            <div class="coursePay_surplus clearfix">
                <p class="surplus_number">剩余<span class="course_number"></span>名</p>
                <p class="surplus_title">优惠剩余名额 : </p>
            </div>
            <!-- <div class="coursePay_surplus clearfix">
                <div class="surplus_number clearfix">
                    <p id="time_d"></p>
                    <p id="time_h"></p>
                    <p id="time_m"></p>
                    <p id="time_s"></p>
                </div>
                <div class="surplus_number time_overdue"></div>
                <p class="surplus_title">课程剩余时间:</p>
            </div> -->
        </div>


        <form class="coursePay_form">
            <div class="coursePay_form_list">
                <input type="text" name="title" required lay-verify="required" placeholder="请输入手机号" autocomplete="off" class="layui-input coursePay_phone">    
            </div>
            <div class="coursePay_form_list">
                <input type="text" name="title" required lay-verify="required" placeholder="请输入验证码" autocomplete="off" class="layui-input coursePay_code coursePay_phone">    
                <input type="button" value="获取验证码" class="coursePay_form_btn" onclick="VerifyCode(this)">
            </div>
        </form>


        <div class="coursePay_infor clearfix">
            <p class="coursePay_infor_title">课程信息</p>
            <span class="coursePay_infor_left">
                7-9年级名校名师课程
                <span>(共5节)</span>
            </span>
            <span class="coursePay_infor_right">
                <span>￥</span>9.9
            </span>
        </div>


        <div class="coursePay_culum_list">
            <p class="coursePay_culum_list_title">
                本课程包括以下学科：语文、数学、英语、物理、化学
            </p>
            <img src="/static/default/images/yuwen.png" alt="">
            <img src="/static/default/images/shuxue.png" alt="">
            <img src="/static/default/images/yingyu.png" alt="">
            <img src="/static/default/images/wuli.png" alt="">
            <img src="/static/default/images/huaxue.png" alt="">
        </div>



        <form class="layui-form layui_method clearfix" action="">
            <div class="layui-form-item layui_method_div">
                <div class="layui-input-block">
                    <img src="/static/default/images/zfb.png" alt="">
                    <input class="choice" type="radio" name="sex" value="支付宝" title="支付宝">
                    <img src="/static/default/images/wx.png" alt="">
                    <input class="choice" type="radio" name="sex" value="微信" title="微信">
                </div>
            </div>
            <div class="coursePay_payment clearfix">
                    <p class="coursePay_payment_number">
                        总计：
                        <span>0.01</span>
                    </p>
                    <div class="coursePay_payment_btn">确认支付</div>
                </div>
        </form>



        

    </div>
  </div> 
</div>

<div class="default_coursePay_model">
    <div class="default_coursePay_model_content">
        <div class="default_coursePay_model_content_saoma display_none">
            <div class="default_coursePay_model_close">+</div>
            <div class="coursePay_model_content_img"></div>
            <div>请扫码支付</div>
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
  <!-- footer -->
  <div class="common-footer">
  <div class="common-footer-contain clearfix">
    <div class="common-footer-logo"><img src="/static/default/images/bottom-logo.png" alt=""></div>
    <ul class="common-footer-center clearfix">
      <li>北京知识一点通科技发展有限公司   <span>Copyright@2019 BY E点就通 All Rights Reserved. <a href="http://beian.miit.gov.cn" target="view_window">ICP证：京ICP备19010770号-1</a></span></li>
      <!-- 京ICP备090290号 -->
      <!-- <li></li> -->
    </ul>
    <!-- <ul class="common-footer-right">
      <li><i class="iconfont icon-dianhua"></i><span>客服热线： 400-100-6648（免长途费）</span></li>
      <li><i class="iconfont icon-xinxi-copy"></i><span>企业邮箱：ydtkt@ydtkt.com</span></li>
    </ul> -->
  </div>
</div>
<!-- 右侧悬浮框 -->
<div class="right-position clearfix">
  <ul class="positions-list">
    <li class="list-lis">
      <img class="list-lis-imgs wechat-erweima" src="/static/default/images/wechat-one.png" alt="">
      <div class="wechat-img">
        <img class="erweima-img" src="/static/default/images/erweiman.jpg" alt="">
        <p class="get-wechat">微信扫一扫</p>
        <p class="get-wechat">获得更多课程</p>
      </div>
    </li>
    <!-- <li class="list-lis" id="online1">
      <a href="javascript:;">
        <img class="list-lis-imgs wechat-consult" src="/static/default/images/consult-one.png" alt="">
      </a>
    </li> -->
    <li class="list-lis go-top">
      <img class="list-lis-imgs wechat-top" src="/static/default/images/go-top-one.png" alt="">
    </li>
  </ul>
</div>

<!-- 判断浏览器是否是谷歌，若不是建议下载谷歌 -->
<!-- 浏览器检测 -->
<div class="check_brower check_brower_2 hide">
  <div class="wrong_brower text-center">
    <img src="/static/default/images/icon_1020_chrome.png">
    <div class="browser-bottom">
      <p class="brower_name">为保证您拥有优质的学习体验，强烈建议您使用Google Chrome浏览器;</p>
      <p>请您下载并安装Google Chrome浏览器后，重新登录学习平台。</p>
    </div>  
    <div class="brower_upload text-center">
      <span class="brower_upload_edition brower_upload_edition_btn">立即下载</span>
    </div>
  </div>
</div>
<script src="/static/manage/layui/layui.js"></script>
<script src="/static/default/js/jquery-2.2.3.js"></script>
<script src="/static/default/js/public.js"></script>
<script src="/static/default/js/browser.js"></script>

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
        $("#time_d").html(int_day+'天');
        $("#time_h").html(int_hour+':'); 
        $("#time_m").html(int_minute+':');
        $("#time_s").html(int_second); 
        // 设置定时器
        setTimeout("show_time()",1000); 
    }

    //获取验证码接口
    function VerifyCode(obj){
        var phone = $('.coursePay_phone').val();
        var myreg=/^[1][3,4,5,7,8][0-9]{9}$/;
        if (!myreg.test(phone)) {
            //弹出弹窗提示用户输入正确手机号
            // alert('请输入正确格式手机号');
            showModel();
            $('.model_content_text').text('请输入正确格式手机号');
            return false;
        }
        //调用验证码接口
        $.ajax({
            type:'post',
            dataType:'JSON',
            url:'/index/login/VerifyCodePay',
            data: {phone: phone},
            success:function(res){
                if( res.error_code == 1 ){
                    $(obj).attr("style","background:rgba(218,216,219,1);color:#373538;");
                    $(obj).attr("disabled", true);//禁用按钮
                    var time = 60;
                    $('.coursePay_form_btn').val("已发送("+time+"s)")
                    var setinter = setInterval(function(){
                        if( time >= 2 && time <= 60 ){
                            time--;
                            $('.coursePay_form_btn').val("已发送("+time+"s)").attr('disabled', true);
                        }else{
                            $('.coursePay_form_btn').css({'background':'#e97226','color':'#fff'}).val("重新获取").attr('disabled', false);
                            clearInterval(setinter);
                        }
                    },1000)
                }else{
                    $(obj).css({'background':'#e97226','color':'#fff'});
                    $('.coursePay_form_btn').val("重新获取").attr('disabled', false);
                }
            }
        })
    }

    $('.coursePay_payment_btn').click(function(){
        var phone =  $('.coursePay_phone').val();
        var code = $('.coursePay_code').val();
        var radio_choice = $("input[name='sex']:checked").val();
        var money = 9.9;
        // if(phone == '')
        // {
        //     // alert('手机号不能为空！');
        //     showModel();
        //     $('.model_content_text').text('手机号不能为空！');
        //     return false;
        // }
        // if(code == '')
        // {
        //     // alert('验证码不能为空！');
        //     showModel();
        //     $('.model_content_text').text('验证码不能为空！');
        //     return false;
        // }
        //验证验证码是否正确
        $.post('/index/login/verificationCode',{phone:phone,code:code},function(res){
            // if(res.error_code==1)
            // {
                if( radio_choice == undefined ){
                    showModel();
                    $('.model_content_text').text('请选择支付方式!');
                    return false;
                }
                if( radio_choice == '支付宝' ){
                    //支付宝支付
                    if(isMobile())
                    {
                        if(isWeixin())
                        {
                            //手机端在微信内选中支付宝支付跳转到中间页面
                            // showModel();
                            // $('.model_content_text').text('请用手机浏览器发起支付宝支付!');
                            // return false;

                            window.location.href="/index/course_pay/pay?phone="+phone+"&register_from=3&payment=1&money="+money;
                        }else{
                            //手机端微信外选中支付宝支付跳转到支付宝内发起支付
                            $.post('/index/course_pay/createOrder',{phone:phone,payment:1,register_from:3,money:money},function(res){
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
                            $.post('/index/course_pay/courseWxPay',{phone:phone,register_from:3,money:money},function(res){
                                var data = JSON.parse(res.data);
                                if(res.error_code == 0) {	
                                    var data = eval("("+res.data+")");
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
                                // $('.coursePay_model_content_img').append('<img src="/WxPay/example/qrcode.php?data='+res.data+'"/>');
                                $('.coursePay_model_content_img').append('<img src="/index/course_pay/getQrcode?data='+res.data+'"/>');
                                $('.default_coursePay_model').show();
                                $('.default_coursePay_model_content_yzm').hide();
                                $('.default_coursePay_model_content_success').hide();
                                $('.default_coursePay_model_content_saoma').show();
                                $('.default_coursePay_model_close').click(function(){
                                    $('.default_coursePay_model').hide();
                                    $('.coursePay_model_content_img img').remove();
                                })
                            }else{
                                showModel();
                                $('.model_content_text').text('您已经购买过课程，无需重新购买');
                            }
                        })
                    }
                }
            // }else{
            //     showModel();
            //     $('.model_content_text').text('验证码错误请重新获取！');
            //     return false;
            // }
        })
    })
    //弹窗
    function showModel(){
        $('.default_coursePay_model').show();
        $('.default_coursePay_model_content_yzm').show();
        $('.default_coursePay_model_content_success').hide();
        $('.default_coursePay_model_content_saoma').hide();
    }
    







    function isMobile()
    {
        if ((navigator.userAgent.match(/(phone|pad|pod|iPhone|iPod|ios|iPad|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i))) {
        
            return true;
        }else {
        
            return false;
        }
    }
    isMobile();
    function isWeixin()
    {
        var ua = window.navigator.userAgent.toLowerCase(); 
        if(ua.match(/MicroMessenger/i) == 'micromessenger'){ 
            return true; 
        }else{ 
            return false; 
        }
    }
    isWeixin();
</script>