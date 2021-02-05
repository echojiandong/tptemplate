<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:68:"D:\tp\ywd100\application/../Template/default/index\course\audio.html";i:1598606566;s:54:"D:\tp\ywd100\Template\default\index\public\header.html";i:1598606566;s:54:"D:\tp\ywd100\Template\default\index\public\footer.html";i:1598606566;}*/ ?>
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
  <link rel="stylesheet" href="/static/default/css/layui.css">
  <link rel="stylesheet" href="/static/default/css/common.css">
  <link rel="stylesheet" href="/static/default/css/style.css">
  <script src="/static/default/js/jquery-2.2.3.js"></script>
  <style>
    .clearfix:after{content:".";clear:both;display:block;height:0;overflow:hidden;visibility:hidden;}
    .clearfix{zoom:1;}
    .listen-content{
      width: 1200px;
      margin:110px auto;
    }
    .bread-crumb{
      width:100%;
      font-size:18px;
      font-weight:400;
      color:rgba(102,102,102,1);
      line-height: 26px;
    }
    .now-page{
      color:#DF4A43;
    }
    .zhang-content{
      width:100%;
      background:rgba(255,255,255,1);
      box-shadow:0px 0px 20px 0px rgba(225,225,225,1);
      border-radius:10px;
      margin-top:32px;
    }
    .zhang-content-top{
     width: 100%;
    }
    .zhang-img{
      width:202px;
      margin:62px 52px 94px 84px;
      float: left;
      position: relative;
    }
    .zhang-img-left{
      width:176px;
      height: 176px;
      background:url(/static/default/images/zhang-bg.png)no-repeat center;
    }
    .zhang-img-left img{
      width:118px;
      height: 118px;
      margin:29px;
    }
    .zhang-img-bang{
      display: inline-block;
      width:38px;
      height: 115px;
      position: absolute;
      top:14px;
      right:0;
    }
    .zhang-img-bang-click{
      transform: rotate(24deg);
      right: 16px;
      top: 14px;
    }
    .rotate{
      transition: 0.5s;
      animation: rotate 10s linear infinite;  /*开始动画后无限循环，用来控制rotate*/
    }
    .zhang-right{
      float: left;
    }
    .zhang-title{
      font-size:26px;
      color:#000;
      margin:69px 0 21px;
    }
    .zhang-item{
      padding:3px 5px;
      border:1px solid rgba(223,74,67,1);
      border-radius:4px;
      font-size:16px;
      color:#DF4A43;
    }
    .zhang-time{
      color:#666;
      font-size: 16px;
      margin:0 10px;
    }
    .zhang-num{
      color:#666;
      font-size: 16px;
    }
    .zhang-bg{
      display: inline-block;
      width:12px;
      height: 12px;
      background:url(/static/default/images/listen.jpg)no-repeat;
    }
    .zhang-btn{
      width:63px;
      height: 63px;
      margin-top:48px;
      position: relative;
      overflow: hidden;
    }
    .zhang-btn img{
      width:100%;
      height: 100%;
    }
    /* 简介 */
    .zhang-course-introduce{
     
    }
    .zhang-course-introduce-title{
      font-size:24px;
      color:#DF4A43;
      position: relative;
      padding:0 0 21px 84px;
      border-bottom:1px solid rgba(241,241,241,1);
    }
    .zhang-course-introduce-title::after{
      content:"";
      position: absolute;
      bottom:0;
      left:78px;
      width:109px;
      height:4px;
      background:rgba(223,74,67,1);
    }
    .zhang-course-introduce-p{
      color:#666666;
      font-size:20px;
      line-height: 32px;
      margin:20px 0 58px 84px;
    }
    .zhang-introduce-img{
      width:254px;
      height:318px;
      float: left;
      margin:39px 40px 60px 94px;
    }
    .zhang-introduce-img img{
      width:100%;
      height: 100%;
    }
    .zhang-introduce-right{
      width:714px;
      float: left;
    }
    .zhang-introduce-name{
      font-size:26px;
      color:#333333;
      margin:33px 0 13px;
    }
    .zhang-introduce-position{
      font-size:18px;
      color:#DF4A43;
      margin-bottom:23px;
    }
    .zhang-introduce-ul{
      margin-bottom:54px;
    }
    .zhang-introduce-lis{
      font-size: 20px;
      color:#666;
      line-height: 30px;
      position: relative;
    }
    .dot{
      display: inline-block;
      width:10px;
      height:10px;
      background:#fff;
      border:1px solid rgba(223,74,67,0.4);
      border-radius:50%;
      margin:10px 16px 0 0;
      position: relative;
      float: left;
    }
    .dot::after{
      content:"";
      position: absolute;
      top:50%;
      left:50%;
      width:6px;
      height:6px;
      transform: translate(-50%, -50%);
      background:rgba(223,74,67,1);
      border-radius:50%;
    }
    .dot-first{
      margin-right:14px;
    }
    .zhang-introduce-lis span{
      display: inline-block;
      width: 95%;
      float: left;
    }
    /*.zhang-bottom{
      position: relative;
      bottom:0;
      left:0;
      width:100%;
      height:51px;
      background:linear-gradient(0deg,rgba(0,0,0,0.8) 0%,rgba(68,68,68,0.8) 100%);
    }
    .zhang-bottom-audio{
      width:1200px;
      margin:0 auto;
    }
    .zhang-bottom-audio audio{
      width:100%;
      height:51px;
      line-height: 51px;
      -webkit-appearance : none ;
    }
    audio::-webkit-media-controls-enclosure, video::-webkit-media-controls.audio-only [pseudo="-webkit-media-controls-enclosure"] {
      background: transparent; 
    }*/
    @keyframes rotate{
      from{transform: rotate(0deg)}
      to{transform: rotate(360deg)}
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
  <!-- 公共头部 -->
  <div class="listen-content clearfix">
    <!-- 面包屑 -->
    <div class="bread-crumb">
      <span class="breadcrumb">
        <a href="/">首页 <i class="layui-icon layui-icon-right"></i></a>
        <a href="/index/course/course?pageNum=10">教育专家张雪燕 <i class="layui-icon layui-icon-right"></i></a>
        <a><cite class="now-page"><?php echo $res['outline']; ?></cite></a>
      </span>
    </div>
    <!-- 主体 -->
    <div class="zhang-content clearfix">
      <div class="zhang-content-top clearfix">
        <div class="zhang-img">
          <div class="zhang-img-left">
            <img src="/static/default/images/litpic.png" alt="">
          </div>
          <img class="zhang-img-bang"  src="/static/default/images/zhang-1.png" alt="">
        </div>  
        <div class="zhang-right">
          <p class="zhang-title"><?php echo $res['outline']; ?></p>
          <div class="zhang-right-center clearfix">
            <span class="zhang-item">教育培训</span>
            <span class="zhang-time"><?php echo $res['time']; ?></span>
            <span class="zhang-num"><i class="zhang-bg"></i> <?php echo $res['likes']; ?>+</span>
          </div>
          <div class="zhang-btn">
            <img id="pause" src="/static/default/images/pause.png" alt="">
            <img id="play" src="/static/default/images/play.png" alt="">
          </div>
      </div>
    </div>
     <!-- 简介 -->
     <div class="zhang-course-introduce">
       <h3 class="zhang-course-introduce-title">课程简介</h3>
       <p class="zhang-course-introduce-p">
        <?php echo $res['skill']; ?>
      </p>
     </div>
      <!-- 简介 -->
      <div class="zhang-course-introduce">
          <h3 class="zhang-course-introduce-title">老师介绍</h3>
         <div class="zhang-introduce-img">
           <img src="/static/default/images/zhang.jpg" alt="">
         </div>
         <div class="zhang-introduce-right">
           <h2 class="zhang-introduce-name">张雪燕</h2>
           <p class="zhang-introduce-position">中国青少年教育专家</p>
           <ul class="zhang-introduce-ul clearfix">
             <li class="zhang-introduce-lis"><i class="dot dot-first"></i><span>国家高师级心理咨询师</span></li>
             <li class="zhang-introduce-lis"><i class="dot"></i><span>家庭教育专家团专家 </span></li>
             <li class="zhang-introduce-lis"><i class="dot"></i><span>拥有12年家庭教育工作专业经历</span></li>
             <li class="zhang-introduce-lis"><i class="dot"></i><span>讲授全国大型讲座1800余场</span></li>
             <li class="zhang-introduce-lis"><i class="dot"></i><span>一对一辅导帮助家庭3000余个</span></li>
             <li class="zhang-introduce-lis"><i class="dot"></i><span>帮助无数孩子成功逆袭，其中不乏目前已就读清华大学、北京大学、悉尼大学、中国人民大学等世界一流大学</span></li>
             <li class="zhang-introduce-lis"><i class="dot"></i><span>著作：《爱的正能量》、《父母卷》等</span> </li>
           </ul>
         </div>
        </div>
      </div>

  </div>

  <audio  id="audio"> 
    <source src="<?php echo $res['link']; ?>">
  </audio>

<!-- 底部导航  -->
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

<!-- 底部导航  -->
</body>
<script src="../layui.js"></script>
</html>

<script>
  window.onload = function() {
    var audioPlay = $("#audio")[0];
    $(".zhang-btn #pause").click(function() {
      $(this).hide()
      $(".zhang-img-left").addClass("rotate")
      $(".zhang-img-bang").addClass("zhang-img-bang-click")
      audioPlay.play()
    })
    $(".zhang-btn #play").click(function() {
      $("#pause").show()
      $(".zhang-img-left").removeClass("rotate")
      $(".zhang-img-bang").removeClass("zhang-img-bang-click")
      audioPlay.pause()
    })

    // audioPlay.addEventListener("play", function () {   
    //   $("#pause").hide()
    //   $(".zhang-img-left").addClass("rotate")
    //   $(".zhang-img-bang").addClass("zhang-img-bang-click")
    // });
    // audio.addEventListener("pause", function () {   
    //   $("#pause").show()
    //   $(".zhang-img-left").removeClass("rotate")
    //   $(".zhang-img-bang").removeClass("zhang-img-bang-click")
    // });
    
  }

</script>