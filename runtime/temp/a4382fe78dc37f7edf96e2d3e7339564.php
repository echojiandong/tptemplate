<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:66:"D:\tp\ywd100\application/../Template/mobile/index\index\index.html";i:1598606566;s:53:"D:\tp\ywd100\Template\mobile\index\public\header.html";i:1598606566;s:53:"D:\tp\ywd100\Template\mobile\index\public\footer.html";i:1598606566;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <title>E点就通名校名师同步课堂</title>
    <script src="/static/mobile/js/mui.min.js"></script>
    <link href="/static/mobile/css/mui.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="/static/mobile/css/swiper.min.css">
    <link rel="stylesheet" href="/static/mobile/css/style.css">
    <style>
        .index_banner .swiper-container{
          width:100%;
          margin:0 auto 0;
          /* height: 3.46rem; */
          height: 1.9rem;
          /* border-radius:.2rem; */
        }

        .index_banner .swiper-wrapper{
          width: 100%;
          height: 100%;
        }

        .index_banner .swiper-slide{
          width: 100%;
          height: 100%;
        }

        .index_banner .swiper-slide img{
          width: 100%;
          height: 100%;
          border-radius: 0;
        } 

        .index_banner .index_content{
          width: 100%;
        }

        .index_content_img{
          width:92%;
          margin:1% auto 0;
        }

        .index_content_img img{
          width: 100%;
          height: 100%;
        }

        .index_content_img_two img{
          width: 100%;
          height: 100%;
        }
        .index-free-btn{
          width:107px;
          height:37px;
          background:rgba(242,90,90,1);
          box-shadow:0px 2px 22px 0px rgba(0, 0, 0, 0.17);
          border-radius:5px;
          text-align: center;
          line-height: 37px;
          position: absolute;
          bottom:7%;
          left:39%;
        }
        .index-free-btn a{
          font-size:12px;
          font-family:SourceHanSansCN-Bold;
          font-weight:bold;
          color:rgba(255,255,255,1);
        }
        @media (min-width:768px) {
          .index_banner .swiper-container {
            height: 2.9rem;
          }
          .swiper-container-teachers .swiper-wrapper {
            margin-top: 7%;
          }
          .swiper-container-teachers .swiper-slide.swiper-slide-active {
            margin-right: 17px;
          }
          .index-teachers-team .swiper-container .swiper-slide {
              margin-right: 17px;
          }
          .index-swiper-teachers-lis::before {
            top: -9%;
          }
          .index-swiper-teachers {
            margin: 10.5% 0 6.5% 2%;
          }
          .index-teachers-team {
            margin-bottom: 1.5%;
          }
          .index-free-btn{
            width: 28%;
            height: 64px;
            line-height: 64px;
            bottom: 7%;
            left: 38%;
          }
          .index-free-btn a{
            font-size:24px;
          }
        }
        @media (min-width:834px){
          /* .index_banner .swiper-container {
            height: 2.9rem;
          } */
          .swiper-container-teachers .swiper-wrapper {
            margin-top: 7%;
          }
          .swiper-container-teachers .swiper-slide.swiper-slide-active {
            margin-right: 17px;
          }
          .index-teachers-team .swiper-container .swiper-slide {
              margin-right: 17px;
          }
          .index-swiper-teachers-lis::before {
            top: -9%;
          }
          .index-free-btn{
            width: 28%;
            height: 74px;
            line-height: 74px;
            bottom: 7%;
            left: 38%;
          }
          .index-free-btn a{
            font-size:26px;
          }
        }
      </style>
</head>
<body>
  <!-- 公共头部header -->
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
  <div class="mui-content clearfix">
    <!-- banner -->
  	<!-- <div class="index-banner">
      <img src="/static/mobile/images/banner.jpg" alt="">
      <div class="index-banner-position">
        <div class="index-banner-msg clearfix">
          <p>让每个孩子<br>都享有优质的教育资源</p>
          <p class="banner-english">Every Child Get The Best Education</p>
          <div class="index-banner-video"><i class="iconfont iconbofang"></i></div>
        </div>
      </div>
    </div> -->
    <!-- swiper轮播 -->
    <div class="index_banner">
      <div class="swiper-container swiper-container1">
        <div class="swiper-wrapper">
            <!-- <div class="swiper-slide"><img src="/static/mobile/images/banner-0.png" alt=""></div> -->
            <div class="swiper-slide"><img src="http://ydtvlitpic.ydtkt.com/banner-1.jpg" alt=""></div>
            <div class="swiper-slide"><a href="/index/teachers/teachersTeam"><img src="http://ydtvlitpic.ydtkt.com/banner-2.jpg" alt="" style="height:190px;"></a></div>
            <div class="swiper-slide"><a href="/index/course/goWx"><img src="http://ydtvlitpic.ydtkt.com/banner-3.jpg" alt="" style="height:190px;"></a></div>
        </div>
      </div>
    </div>
    <!-- swiper轮播  END -->


    <!-- 内容图片 -->
    <div class="index_content">
      <div class="index_content_img_two">
          <img src="http://ydtvlitpic.ydtkt.com/good-teacher.png" alt="">
      </div>
      <div class="index_content_img_two">
          <img src="http://ydtvlitpic.ydtkt.com/edition-img.png" alt="">
      </div>
      <div class="index_content_img_two">
          <img src="http://ydtvlitpic.ydtkt.com/system.jpg" alt="">
      </div>
      <div class="index_content_img_two">
          <img src="http://ydtvlitpic.ydtkt.com/intrest.jpg" alt="">
      </div>
      <div class="index_content_img_two">
          <img src="http://ydtvlitpic.ydtkt.com/special.png" alt="">
          <img src="http://ydtvlitpic.ydtkt.com/special-2.jpg" alt="">
      </div>
    </div>
    <!-- 内容图片 END -->


    <!-- 教师简介 -->

    <!-- <div class="index_teacher_content">
      <div class="index_teacher_title">名校名师</div>
      <div class="swiper-container swiper-container2">
        <div class="swiper-wrapper">
            <div class="swiper-slide">
                <div class="slide_img">
                  <img src="" alt="">
                </div>
                <div class="swiper_slide_text">
                  <span class="slide_text_name">方红梅</span>
                  <span class="slide_text_nianji">七年级物理老师</span>
                  <p class="slide_text">物理高级教师，理综调研组张，黄冈市骨干教师，黄冈市骨干教师，黄冈</p>
                </div>
            </div>
            <div class="swiper-slide">
                <div class="slide_img">
                  <img src="" alt="">
                </div>
                <div class="swiper_slide_text">
                  <span class="slide_text_name">方红梅</span>
                  <span class="slide_text_nianji">七年级物理老师</span>
                  <p class="slide_text">物理高级教师，理综调研组张，黄冈市骨干教师，黄冈市骨干教师，黄冈</p>
                </div>
            </div>
        </div>
      </div>
    </div> -->

    <!-- 教师简介 END -->

   


    <!-- 试听视频 -->
    <!-- <div class="index-listen-video clearfix">
      <h3 class="index-listen-h3">试听视频</h3>
      <ul class="index-listen-list clearfix">
        <?php if(is_array($videoList) || $videoList instanceof \think\Collection || $videoList instanceof \think\Paginator): $i = 0; $__LIST__ = $videoList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
        <li class="index-listen-lis"> -->
          <!-- <a href="/index/course/courseParticulars?id=<?php echo $vo['kid']; ?>&videoid=<?php echo $vo['id']; ?>&startTime=0"> -->
          <!-- <a href="/index/course/goWx">
            <div class="index-listen-lis-top">
              <img src="<?php echo $vo['image']; ?>" alt="">
              <div class="index-listen-modal">
                <div class="index-listen-subject subject-chinese"><?php echo $vo['subject']; ?></div>
                <div class="index-listen-icon">
                  <i class="iconfont iconbofang"></i>
                </div>
                <span class="index-listen-time"><?php echo $vo['classhour']; ?></span>
              </div>
            </div>
            <p class="index-listen-title"><?php echo $vo['outline']; ?></p>
          </a>
        </li>
        <?php endforeach; endif; else: echo "" ;endif; ?>
      </ul>
    </div> -->
    <!-- 登陆后开始学习后的学习进度 -->
    <!-- <?php if(isset($videoStudyList) && !empty($videoStudyList)): ?>
    <div class="index-login-progress clearfix" style="display: none;">
      <h3 class="index-login-progress-title">学习进度</h3>
      <div class="swiper-container swiper-container-progress">
        <div class="swiper-wrapper">
          
          <?php if(is_array($videoStudyList) || $videoStudyList instanceof \think\Collection || $videoStudyList instanceof \think\Paginator): $k = 0; $__LIST__ = $videoStudyList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($k % 2 );++$k;if(($k < 4 )): ?>
          <div class="swiper-slide">
            <?php if((!empty($val['studyPeriod']))): ?>
              <a href="/index/course/goWx" class="clearfix">
              <?php else: ?>
             
              <a href="/index/course/goWx" class="clearfix">
              <?php endif; ?>
              <div class="progress-slide-top">
                <h5 class="progress-slide-top-name"><?php echo $val['grade']; ?><?php echo $val['subject']; ?><?php echo $val['semester']; ?></h5>
                <span class="progress-slide-total">课程共<?php echo $val['allPeriod']; ?>节</span>
                <span class="progress-slide-top-time">总时长<?php echo $val['allTime']; ?></span>
              </div>
              <div class="index-study-progress">
                <div id="progress-slide-progress" class="mui-progressbar">
                  <span><?php echo $val['studyPeriod']/$val['allPeriod']*100; ?></span>
                </div>
              </div>
              <p class="progress-slide-bottom">已学习<?php echo $val['studyPeriod']; ?>节课 &nbsp;&nbsp;<?php if(($val['studyPeriod'] != 0)): ?>已学时长<?php echo $val['studyTime']; else: ?>未学习<?php endif; ?></p>
              <div class="progress-slide-go-now"><?php if((empty($val['studyPeriod']))): ?>开始学习<?php else: ?>继续学习<?php endif; ?></div>
            </a>
          </div>
          <?php endif; endforeach; endif; else: echo "" ;endif; ?>
          
        </div>
        <div class="swiper-pagination"></div>
      </div>
    </div>
    <?php endif; ?> -->
    <!-- 四大优势 -->
    <!-- <div class="index-fourth-advantage clearfix">
      <div class="index-advantage clearfix">
        <h3 class="index-fourth-title">选择我们的四大优势</h3>
        <p class="index-fourth-msg">为什么选择我们，我们的优势介绍 我们主打的课程介绍名师团队介绍各种设备支持介绍智能课前预课后复习介绍</p>
        <ul class="index-fourth-list">
          <li class="index-fourth-lis">
            <img src="/static/mobile/images/advantage-1.png" alt="">
            <p class="index-fourth-lis-title"> 同步课堂<br>原版教材</p>
          </li>
          <li class="index-fourth-lis">
            <img src="/static/mobile/images/advantage-2.png" alt="">
            <p class="index-fourth-lis-title">名校名师<br>专业团队</p>
          </li>
          <li class="index-fourth-lis">
            <img src="/static/mobile/images/advantage-3.png" alt="">
            <p class="index-fourth-lis-title"> 课前预习<br>课后复习</p>
          </li>
          <li class="index-fourth-lis">
            <img src="/static/mobile/images/advantage-4.png" alt="">
            <p class="index-fourth-lis-title"> 支持各种设备<br>随时随地学</p>
          </li>
        </ul>
      </div>
    </div> -->
    <!-- 多年自主研发适合个地区同步课堂的课程 -->
    <!-- <div class="index-sync-course">
      <h3 class="index-sync-course-title">多年自主研发适合个地区同步课堂的课程</h3>
      <p class="index-sync-msg">历经500000个小时耗费上千万聘请全国各地名校名师录课专为不同地区不同版本教材的学生定制</p>
      <ul class="index-sync-list">
        <li class="index-sync-lis">课堂同步名师课程无限循环听</li>
        <li class="index-sync-lis">支持各地区不同版本</li>
        <li class="index-sync-lis">学习不花冤枉钱</li>
        <li class="index-sync-lis">成绩突飞猛进</li>
      </ul>
    </div> -->
    <!-- 高标准严选师资团队 -->
    <div class="index-teachers-team clearfix">
      <h4 class="index-teachers-title">高标准严选师资团队</h4>
       <!-- Swiper -->
      <div class="swiper-container swiper-container-teachers">
        <div class="swiper-wrapper">
        <?php if(is_array($teacherList) || $teacherList instanceof \think\Collection || $teacherList instanceof \think\Paginator): $i = 0; $__LIST__ = $teacherList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
          <div class="swiper-slide swiper-slide-teachers" data-id='<?php echo $v['id']; ?>' title='<?php echo $v['name']; ?>'>
            <img src="<?php echo $v['litpic']; ?>" alt="">
          </div>
        <?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
        <!-- 教师简介文字部分 -->
        <div class="index-swiper-teachers">
          <ul class="index-swiper-teachers-list clearfix">
            <?php if(is_array($teacherList) || $teacherList instanceof \think\Collection || $teacherList instanceof \think\Paginator): $k = 0; $__LIST__ = $teacherList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($k % 2 );++$k;?>
            <li class="index-swiper-teachers-lis" data-id='<?php echo $v['id']; ?>'>
              <h4 class="index-swiper-teachers-name"><?php echo $v['name']; ?></h4>
              <p class="index-swiper-teachers-p"><?php echo $v['content']; ?></p>
            </li>
            <?php endforeach; endif; else: echo "" ;endif; ?>
          </ul>
        </div>
      </div>
    </div>
    <!-- 我们的承诺-->
    <!-- <div class="index-promise">
      <div class="index-promise-contain clearfix">
        <h4 class="index-promise-title">我们的承诺</h4>
        <p class="index-promise-p">Our commitment</p>
        <ul class="index-promise-list clearfix">
          <li class="index-promise-lis">
            <i class="iconfont icondiannao"></i>
            <div class="index-promise-bottom">
              <h5 class="index-promise-name">试听承诺 </h5>
              <p class="index-promise-msg">保证试听课程全部截取与老师的精彩课程等等······</p>
            </div>
          </li>
          <li class="index-promise-lis">
            <i class="iconfont iconkaoshi"></i>
            <div class="index-promise-bottom">
              <h5 class="index-promise-name">试听承诺 </h5>
              <p class="index-promise-msg">保证试听课程全部截取与老师的精彩课程等等······</p>
            </div>
          </li>
          <li class="index-promise-lis">
            <i class="iconfont icontuandui"></i>
            <div class="index-promise-bottom">
              <h5 class="index-promise-name">试听承诺 </h5>
              <p class="index-promise-msg">保证试听课程全部截取与老师的精彩课程等等······</p>
            </div>
          </li>
        </ul>
      </div>
    </div> -->
     <!-- 内容图片 -->
     <div class="index_content">
        <div class="index_content_img_two" style="position: relative;">
            <img src="http://ydtvlitpic.ydtkt.com/question.png" alt="">
            <div class="index-free-btn"><a href="/index/course/goWx">立即免费试听</a></div>
        </div>
        <div class="index_content_img_two">
            <img src="http://ydtvlitpic.ydtkt.com/learn.png" alt="">
        </div>
      </div>
  </div>
  <!-- footer -->
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
<script src="/static/mobile/js/swiper.min.js"></script>
<script src="/static/mobile/js/iconfont/iconfont.js"></script>
<script src="/static/mobile/js/public.js"></script>
<script>
  // 轮播
  var mySwiper = new Swiper('.swiper-container1',{
    loop: true, 
    autoplay: true, 
    speed: 1000,
  });  
  var mySwiper = new Swiper('.swiper-container2',{
    // loop: true, 
    // autoplay: true, 
    // speed: 1000,
    freeMode : true,
  });  


  mui.init();
  // swiper  swiper-container-teachers
  var swiper1 = new Swiper('.swiper-container-teachers', {
    slidesPerView: 5.5,
    spaceBetween: 11,
    // slidesPerView: 'auto',
    centerInsufficientSlides: true,
    loop:true,
    // active下 居中显示
	  centeredSlides: true,	
    // autoplay:true,
    on: {
      slideChangeTransitionStart: function(){
        var id = $('.swiper-container-teachers .swiper-slide-active').attr('data-id');
        $('.index-swiper-teachers-lis').removeClass('active');
        $('.index-swiper-teachers-lis[data-id="'+id+'"]').addClass('active');
        // $.post('/index/index/teachMsg',{id:id},function(res){
        //   var res = JSON.parse(res);
        //   html = '<li class="index-swiper-teachers-lis active"><h4 class="index-swiper-teachers-name">'+res.name+'</h4><p class="index-swiper-teachers-p">'+res.content+'</p></li>';
        //   $('.index-swiper-teachers-list').html(html)
        // });
      },
    },
  });
  // 进度条
  var swiper = new Swiper('.swiper-container-progress', {
      slidesPerView: 'auto',
      spaceBetween: 30,
      centerInsufficientSlides: true,
      loop:false,
      // active下 居中显示
	    centeredSlides: true,	
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },
    });
  // 学习进度 进度条
  var num = $('#progress-slide-progress span').html();
  if(num){
    mui('#progress-slide-progress').progressbar({progress:num}).show();
  }
</script>