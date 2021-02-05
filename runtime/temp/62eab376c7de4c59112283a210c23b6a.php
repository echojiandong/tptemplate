<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:66:"D:\tp\ywd100\application/../Template/mobile/index\login\login.html";i:1598606566;}*/ ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="/static/mobile/js/mui.min.js"></script>
    <link href="/static/mobile/css/mui.min.css" rel="stylesheet"/>
    <!-- <link rel="stylesheet" href="/static/mobile/css/swiper.min.css"> -->
    <link rel="stylesheet" href="/static/mobile/css/style.css">
    <script src="/static/mobile/js/jquery-2.2.3.js"></script>
    <!-- <script src="/static/mobile/js/public.js"></script> -->
    <script type="text/javascript" charset="utf-8">
      mui.init();
    </script>
    <script src="/static/mobile/css/iconfont/iconfont.js"></script>
    <title>E点就通名校名师同步课堂</title>
</head>
<body>
  <header class="mui-bar mui-bar-nav common-header">
    <div class="header-back iconfont iconxiangzuojiantou"><a href="javascript:;"></a></div>
    <h1 class="common-title">密码登录</h1>
  </header>
  <div class="login-page">
    <div class="page-logo"><img src="/static/mobile/images/logo.png" alt=""></div>
    <div class="login-form">
      <!-- <div class="login-register-page">
        <div class="register-btn"><a href="javascript:;">注册</a></div>
        <div class="login-btn"><a href="javascript:;">登录</a></div>
      </div> -->
      <form action="" class="login-form-form">
        <div class="login-form-item">
          <i class="iconfont iconwebicon205"></i>
          <input type="text" class="login-form-input iconfont login-form-name" id="login-phone" placeholder="请输入手机号">
        </div>
        <div class="login-form-item">
          <i class="iconfont iconsuozi"></i>
          <input type="password" class="login-form-input iconfont login-form-pwd" id="login-pwd" placeholder="请输入密码">
        </div>
        <div class="login-form-item login-item">
          <a href="/index/login/forgetPwd">忘记密码？</a>
          <a href="/index/login/loginCode">使用验证码登录</a>
        </div>
        <div class="login-form-item">
          <button type="button" class="mui-btn login-submit-btn" id="login-pwd-submit">登录</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
<script src="/static/mobile/js/fastclick.js"></script>
<script>
  // mui('.mui-input-row input').input(); 
  //iphoneX兼容
  function isIphoneX() {
    return /iphone/gi.test(navigator.userAgent) && (screen.height == 812 && screen.width == 375)
  } 
  if(isIphoneX()) {
    $(".page-logo").css({"margin":"40.5% auto 0"})
  }
  let data = {
    loginPhone : '',
    loginPwd: ''
  }
 // 格式输入错误表框提示
 $('#login-phone, #login-pwd').blur(function() {
    if ( $(this).val() != '' ) {
      $(this).css({borderBottom:"1px solid #E3E3E3"})
    } else {
      $(this).css({borderBottom:"1px solid #0a3e82"})
    }
  })

// 登陆按钮点击事件
document.getElementById("login-pwd-submit").addEventListener('tap', function() {
  let loginPhone = $("#login-phone").val(),
      loginPwd = $("#login-pwd").val()
      data.loginPhone = loginPhone
      data.loginPwd = loginPwd
      console.log(data.loginPhone)
      console.log(data.loginPwd)
  if ( loginPhone.length == '' || loginPwd.length == '' ) {
    mui.toast('您的信息有误，请输入正确信息！');
  } else {
    window.location.href = '/index/course/goWx'
    // $.ajax({
    //   url: "/index/Login/pwdLogin",
    //   type: 'post',
    //   data: {
    //     phone: loginPhone,
    //     password: loginPwd
    //   },
    //   success: function (res) {
    //       // 登陆成功跳转到首页
    //         if(res.error_code==1){
    //           sessionStorage.setItem("flag",1)
    //           sessionStorage.setItem('userId',res.count);
    //           sessionStorage.setItem('_index',4)
    //           //登陆成功跳转个人中心页面
    //           window.location.href=res.data;
    //         }else{
    //           mui.toast(res.msg);
    //         }
    //   },
    //   error: function (jqXHR, textStatus, errorThrown) {
    //     console.log(jqXHR.responseText)
    //   }
    // })
  }
})
//页面回退
$('.header-back').click(function(){
  window.history.back(-1);
})

/*禁止ios缩放，双击和双指*/
window.onload=function () {
  document.addEventListener('touchstart',function (event) {
  if(event.touches.length>1){
  event.preventDefault();
  }
  });
  var lastTouchEnd=0;
  document.addEventListener('touchend',function (event) {
  var now=(new Date()).getTime();
  if(now-lastTouchEnd<=300){
  event.preventDefault();
  }
  lastTouchEnd=now;
  },false);
  document.addEventListener('gesturestart', function (event) {
  event.preventDefault();
  });

  FastClick.attach(document.body);
  document.documentElement.addEventListener('touchstart', function (event) {
    if (event.touches.length > 1) {
      event.preventDefault();
    }
  }, false);
}

</script>