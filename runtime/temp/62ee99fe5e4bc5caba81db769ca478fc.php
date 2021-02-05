<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:69:"D:\tp\ywd100\application/../Template/default/index\course\course.html";i:1598606566;s:54:"D:\tp\ywd100\Template\default\index\public\header.html";i:1598606566;s:54:"D:\tp\ywd100\Template\default\index\public\footer.html";i:1598606566;}*/ ?>
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
    .zhangxueyan-content{
      width:1200px;
      margin:24px auto 0;
    }
    .content-left{
      width:241px;
      margin-right:25px;
      text-align: center;
      background: #fff;
      border-radius: 5px;
      float: left;
      box-shadow:0px 0px 20px 0px rgba(225,225,225,1);
    }
    .content-left img{
      width:241px;
      height: 300px;
      border-top-left-radius: 5px;
      border-top-right-radius: 5px;
    }
    .teacher-name{
      font-size:22px;
      font-weight:500;
      color:rgba(223,74,67,1);
      margin-top:15px;
    }
    .teacher-introduce{
      font-size:18px;
      font-weight:400;
      color:rgba(102,102,102,1);
      line-height: 30px;
      padding-bottom:20px;
      margin-top:5px;
    }
    /* 右边 */
    .content-right{
      width:934px;
      box-shadow:0px 0px 20px 0px rgba(225,225,225,1);
      float: left;
      margin-bottom:102px;
      background: #fff;
    }
    .content-right-top{
      width:100%;
      height: 62px;
      line-height: 62px;
      border-bottom:1px solid rgba(240,240,240,1);
      background:#fff;
    }
    .content-right-top-li{
      font-size:20px;
      float: left;
      margin-left:36px;
      text-align: center;
    }
    .content-right-top-li a{
      color:#B0B4BD;
    }
    .content-right-top-click{
      width:106px;
      background:rgba(255,250,250,1);
      position: relative;
    }
    .content-right-top-click a{
      color:#E8514A;
    }
    .content-right-top-click::before{
      content:"";
      position: absolute;
      top:0;
      left:0;
      width:100%;
      height:4px;
      background:rgba(223,74,67,1);
    }
    .content-right-introduce{
      width:100%;
    }
    .content-img{
      width:100%;
    }
    .content-right-course{
      width:100%;
      margin:44px 0 0 51px;
    }
    .course-title{
      display: inline-block;
      font-size:22px;
      color:rgba(223,74,67,1);
      padding:5px 30px 4px;
      background:rgba(223,74,67,0.08);
      border-radius:0px 19px 19px 0px;
      position: relative;
      margin-bottom:32px;
    }
    .course-title::before{
      content:"";
      position: absolute;
      top:0;
      left:0;
      width:6px;
      height:38px;
      background:rgba(223,74,67,1);
    }
    .course-list{
      width: 100%;
      margin-bottom:35px;
    }
    .course-lis{
      width:416px;
      height:264px;
      border:1px solid rgba(228,228,228,1);
      margin-right:16px;
      float: left;
      margin-bottom:15px;
    }
    .course-lis:nth-child(2n){
      margin-right: 0;
    }
    .course-lis-content{
      margin:29px 0 0 21px;
    }
    .course-lis-title{
      font-size:24px;
      color:#333;
    }
    .course-lis-item{
      display: inline-block;
      font-size:18px;
      font-family:Source Han Sans CN;
      font-weight:300;
      color:rgba(223,74,67,1);
      border:1px solid rgba(223,74,67,1);
      border-radius:5px;
      padding:1px 12px;
      margin:15px 0 13px;
    }
    .course-lis-num{
      font-size:15px;
      font-weight:400;
      color:rgba(153,153,153,1);
      margin-bottom:19px;
    }
    .course-lis-img{
      display: inline-block;
      width: 12px;
      height: 12px;
      margin-left:5px;
    }
    .course-lis-pic{
      width:70px;
      float: left;
    }
    .course-lis-pic img{
      width:100%;
      height: 70px;
      border-radius: 50%;
      margin-bottom:12px;
    }
    .course-lis-pic p{
      width:100%;
      font-size:18px;
      color:rgba(102,102,102,1);
      text-align: center;
      padding-bottom:24px;
    }
    .study-now{
      display: inline-block;
      position: relative;
      right:45px;
      top:68px;
      font-size:20px;
      color:#fff;
      padding:6px 18px;
      float: right;
      background:#DF4A43;
    }
    .layui-icon-right{
      color:#fff;
    }
    .study-now:hover{
      color:#fff;
      background:#C83731;
    }
    .study-now-click{
      color:#fff;
      background:#C83731;
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
<!-- 列表 -->
<div class="course-contain clearfix">
  <div class="course-select-list clearfix">
    <dl class="course-list course-list-grade clearfix" id="grade-arr">
      <?php if(is_array($getgrade) || $getgrade instanceof \think\Collection || $getgrade instanceof \think\Paginator): $i = 0; $__LIST__ = $getgrade;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
          <dd class="course-list-lis <?php if($key == 0): ?> course-list-lis-click <?php endif; ?>"" data-gid="<?php echo $v['gid']; ?>" data-sid="<?php echo $v['sid']; ?>"><?php echo $v['name']; ?></dd>
      <?php endforeach; endif; else: echo "" ;endif; ?>
      <dd class="course-list-lis" style="width:140px">教育专家张雪燕</dd>
    </dl>
  </div>
  <!-- 张雪燕老师课程部分开始 -->
  <div class="zhangxueyan-content" style="display:none">
    <div class="content-left c8learfix">
      <img src="/static/default/images/zhang.jpg" alt="">
      <p class="teacher-name">张雪燕</p>
      <p class="teacher-introduce">中国青少年教育专家</p>
    </div>
    <div class="content-right clearfix">
      <ul class="content-right-top">
        <li class="content-right-top-li content-right-top-click"><a href="#introduce">教师介绍</a></li>
        <li class="content-right-top-li"><a href="#course">主授课程</a></li>
      </ul>
      <div class="content-right-introduce clearfix" id="introduce">
        <div class="content-img" style="height:464px;background:url(/static/default/images/1.jpg) no-repeat top center;"></div>
        <div class="content-img" style="height:390px;background:url(/static/default/images/2.jpg) no-repeat top center;"></div>
        <div class="content-img" style="height:541px;background:url(/static/default/images/3.jpg) no-repeat top center;"></div>
      </div>
      <div class="content-right-course clearfix" id="course">
        <div class="course-title">主授课程</div>
        <ul class="course-list clearfix">
          <?php if(is_array($res) || $res instanceof \think\Collection || $res instanceof \think\Paginator): $i = 0; $__LIST__ = $res;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
          <li class="course-lis">
            <div class="course-lis-content">
              <p class="course-lis-title"><?php echo $v['outline']; ?></p>
              <span class="course-lis-item">教育培训</span>
              <div class="course-lis-num">已有<?php echo $v['likes']; ?>+人学习 <i class="course-lis-img" style="background: url(/static/default/images/listen.png) no-repeat top center;"></i></div>
              <div class="course-lis-pic clearfix">
                <img src="/static/default/images/litpic.png" alt="">
                <p>张雪燕</p>
              </div>
              <a href="/index/course/audio?id=<?php echo $v['id']; ?>" class="study-now">立即听课 <i class="layui-icon layui-icon-right"></i></a>
            </div>
          </li>
          <?php endforeach; endif; else: echo "" ;endif; ?>
         
        </ul>
      </div>
      
    </div>
  </div>
  <!-- 张雪燕老师课程部分结束 -->
  <!-- 课程列表 -->
  <ul class="course-list-contain clearfix">
    
  
  </ul>

  <div class="course-pages" style="display:none">
    <ul class="course-pages-num" id="page">
      <!-- <li class="course-pages-first course-pages-style">首页</li> -->
      <!-- <li class="course-pages-prev course-pages-style">上一页</li> -->
      <!-- <li class="course-pages-nums">1</li> -->
      <!-- <li class="course-pages-nums">2</li> -->
      <!-- <li class="course-pages-nums">3</li>
      <li class="course-pages-nums">4</li>
      <li class="course-pages-nums">5</li>
      <li class="course-pages-nums">6</li>
      <li class="course-pages-nums">7</li> -->
      <!-- <li class="course-pages-next course-pages-style">下一页</li> -->
      <!-- <li class="course-pages-last course-pages-style">尾页</li> -->
    </ul>
  </div>
</div>
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
<script src="/static/default/js/course.js"></script>
<script>
  $(document).on('click','.course-contain-lis-left a',function(){
    return loginpage();    
  })

  $(document).on('click','.course-right-study',function(){
    return loginpage();    
  })
</script>
</html>