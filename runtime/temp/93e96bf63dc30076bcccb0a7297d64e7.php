<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:75:"D:\tp\ywd100\application/../Template/mobile/index\teachers\teacherInfo.html";i:1598606566;s:53:"D:\tp\ywd100\Template\mobile\index\public\header.html";i:1598606566;s:53:"D:\tp\ywd100\Template\mobile\index\public\footer.html";i:1598606566;}*/ ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
  <title>E点就通名校名师同步课堂</title>
  <script src="/static/mobile/js/mui.min.js"></script>
  <link href="/static/mobile/css/mui.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="/static/mobile/css/style.css">
</head>
<body>
  <!-- header -->
  <header class="mui-bar mui-bar-nav common-header">
	<!-- <div class="common-select-grade common-header-select iconfont">选择年级</div> -->
	<h1 class="common-title">E点就通</h1>
	<?php if((!isset($user))): ?>
	<div class="login-register">
		<div class="common-login"><a href="/index/login/commonLogin">登录</a></div>
		<!-- <div class="common-register"><a href="/index/login/commonRegister">注册</a></div> -->
	</div>
	<?php else: ?>
    <div class="after-login"><img src="http://ydtvlitpic.ydtkt.com/<?php echo !empty($personInfo['litpic'])?$personInfo['litpic']:'photo.jpg'; ?>" alt=""></div>
    <?php endif; ?>
</header>
<nav class="header-nav-list">
	<ul class="header-select-grade">
		<li class="header-select-grade-lis" data-val='7'>初一年级</li>
		<li class="header-select-grade-lis" data-val='8'>初二年级</li>
		<!--  header-select-current -->
		<li class="header-select-grade-lis" data-val='9'>初三年级</li>
	</ul>
</nav>

  <!-- 顶部导航栏选择年级弹出部分 -->
  <!-- content -->
  <div class="mui-content teachers-page clearfix">
    <div class="teachers-info">
      <div class="teachers-info-top clearfix">
        <div class="teachers-info-img">
          <img src="<?php echo $teacherList['0']['litpic']; ?>" />
        </div>
        <div class="teachers-info-right clearfix">
          <div class="teachers-info-title clearfix">
            <span class="teachers-info-name"><?php echo $teacherList['0']['name']; ?></span>
            <span class="teachers-info-position">[<?php echo $teacherList['0']['subject']; ?>老师]</span>
            <span class="teachers-info-star"></span>
          </div>
          <div class="teachers-info-age"><?php echo $teacherList['0']['teacherPosition']; ?></div>
        </div>
      </div>
      <div class="teachers-info-introduce">
        <div class="teachers-info-introduce-title">讲师简介</div>
        <p class="teachers-info-introduce-p">
            <?php echo $teacherList['0']['content']; ?>
        </p>
      </div>
    </div>
  </div>
<footer class="navigation-bar">
    <ul class="navigation-bar-list">
      <li>
        <a href="javascript:;" class='is_footer'>
          <svg class="icon" aria-hidden="true">
            <use xlink:href="#iconshouye"></use>
          </svg>
          <p class="navigation-bar-p current">首页</p>
        </a>
      </li>
      <li>
        <a href="javascript:;" class='is_footer'>
          <svg class="icon" aria-hidden="true">
            <use  xlink:href="#iconshipinbofang"></use>
          </svg>
          <p class="navigation-bar-p">同步课程</p>
        </a>
      </li>
      <li>
        <a href="javascript:;" class='is_footer'>
          <!-- /index/teachers/teachersTeam-->
          <svg class="icon" aria-hidden="true">
            <use xlink:href="#iconjiaoshituandui"></use>
          </svg>
          <p class="navigation-bar-p">名校名师</p>
        </a>
      </li>
      <!-- <li>
        <a href="javascript:;" class='is_footer'>
          <svg class="icon" aria-hidden="true">
            <use xlink:href="#iconkefuyanzheng"></use>
          </svg>
          <p class="navigation-bar-p">客服验证</p>
        </a>
      </li> -->
      <li>
        <a href="javascript:;" class='is_footer'>
          <svg class="icon" aria-hidden="true">
            <use xlink:href="#icongerenzhongxin"></use>
          </svg>
          <p class="navigation-bar-p">个人中心</p>
        </a>
      </li>
    </ul>
  </footer>

</body>
</html>
 <script src="/static/mobile/js/jquery-2.2.3.js"></script>
 <script src="/static/mobile/js/fastclick.js"></script>
<script src="/static/mobile/js/iconfont/iconfont.js"></script>
<script src="/static/mobile/js/public.js"></script>
<script>
  mui.init();
  function isIphoneX() {
    return /iphone/gi.test(navigator.userAgent) && (screen.height == 812 && screen.width == 375)
  } 
  if(isIphoneX()) {
    $(".course-change-list:first-child").css({"margin":"20% 0 0 4%"})
    $(".course-list-change").css({height:"94%"})
  }
  // $(".teachers-tab-lis").click(function() {
  //   $(".teachers-tab-lis").removeClass("teachers-tab-current")
  //   $(this).addClass("teachers-tab-current")
  //   $(".teachers-lis").removeClass("active")
  //   $(".teachers-lis").eq($(this).index()).addClass("active")
  // })
</script>