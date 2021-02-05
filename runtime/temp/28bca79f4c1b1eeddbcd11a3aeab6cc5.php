<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:77:"E:\phpStudy\WWW\ywd100\application/../Template/default/index\index\index.html";i:1598606565;s:64:"E:\phpStudy\WWW\ywd100\Template\default\index\public\header.html";i:1598606565;s:64:"E:\phpStudy\WWW\ywd100\Template\default\index\public\footer.html";i:1598606565;}*/ ?>
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
  <link rel="icon" type="image/x-icon" href="/static/default/images/bit1.ico" />
  <link rel="stylesheet" href="/static/default/css/animate.min.css">
  <link rel="stylesheet" href="/static/default/css/swiper.min.css">
  <link rel="stylesheet" href="/static/default/css/layui.css">
  <link rel="stylesheet" href="/static/default/css/common.css">
  <link rel="stylesheet" href="/static/default/css/style.css">
  <script src="/static/default/js/jquery-2.2.3.js"></script>
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
  <!-- contain -->
  <div class="index-container clearfix">
    <!-- swiper -->
    <!-- <div class="index-banner">
      <img src="http://59.110.29.237/ydtkt/public/static/default/images/banner.jpg" alt="">
      <div class="index-banner-modal"></div>
      	<div class="index_banner_auto">
        <div class="index-banner-msg">
          <p class="index-banner-title">让每个孩子</p>
          <p class="index-banner-title">都享有优质的教育资源</p>
          <p class="index-banner-english">Every Child Get The Best Education</p>
        </div>
        <div class="index-video-play">
          <div class="index-video-play-shadow">
            <div class="index-video-play-modal">
              <video class="index-video video" autoplay="autoplay" muted  controls controlsList="nodownload">
                <source autoplay src="http://ydtvideo.rjt-stirling.com/ceshiindex.mp4">
              </video>
            </div>
          </div>
          <div class="index-listen-video">
          </div>
        </div>
      </div>

    </div> -->
    <!-- <div class="index-video-model-position">
      <div class="index-video-model">
        <video id="video" class="video" autoplay muted controls controlsList="nodownload" poster="/static/default/images/line.png">
          <source src="http://ydtvideo.rjt-stirling.com/ceshiindex.mp4" type="video/mp4" />
        </video>
      </div>
    </div> -->
<div style="width:100%;height:832px;background:url(http://ydtvlitpic.ydtkt.com/banner.png) no-repeat center;"></div>
<div style="width:100%;height:755px;background:url(http://ydtvlitpic.ydtkt.com/teachers-team.png) no-repeat center;"></div>
<div style="width:100%;height:681px;background:url(http://ydtvlitpic.ydtkt.com/tongbu.jpg) no-repeat center;"></div>
<div style="width:100%;height:673px;background:url(http://ydtvlitpic.ydtkt.com/tixi.png) no-repeat center;"></div>
<div style="width:100%;height:735px;background:url(http://ydtvlitpic.ydtkt.com/xingqu.jpg) no-repeat center;"></div>

  <!-- 开始学习之前显示部分 -->
  <!-- <div class="index-container-before-learn">
    <div class="layui-container listen-free-video-style clearfix">    
      <div class="index-video-title">
        <h4 class="index-video-h4">试听课程</h4>
      </div>
      <div class="index-video-select clearfix">
        <h5 class="index-video-select-title">选择年级:</h5>
        <ul class="index-video-select-ul">
          <li class="index-video-select-lis index-video-select-current" data-val="7">七年级</li>
          <li class="index-video-select-lis" data-val="8">八年级</li>
          <li class="index-video-select-lis" data-val="9">九年级</li>
        </ul>
      </div>
      <div class="layui-row index-video-list">
      </div>
    </div>

  </div> -->


<!-- 四大优势 -->
<!-- <div class="index-four-advantage">
  <div class="index-advantage-model" style="filter: progid:DXImageTransform.Microsoft.gradient(startcolorstr=#c8DF4A43, endcolorstr=#c8DF4A43);">
    <div class="index-advantage-title clearfix">
      <h3 class="index-advantage-h3">选择我们的四大优势</h3>
      <p class="index-advantage-msg">为什么选择我们，我们的优势介绍 我们主打的课程介绍名师团队介绍<br>各种设备支持介绍智能课前预课后复习介绍</p>
    </div>
    <ul class="index-advantage-list clearfix">
      <div class="index-line-move"></div>
      <li class="index-list-lis index-list-lis-one">
        <img class="index-list-lis-img" src="/static/default/images/advantage-1.png" alt="">
        <p class="index-lis-title">同步课堂<br>原版教材</p>
      </li>
      <li class="index-list-lis index-list-lis-two">
        <img class="index-list-lis-img" src="/static/default/images/advantage-2.png" alt="">
        <p class="index-lis-title">同步课堂<br>原版教材</p>
      </li>
      <li class="index-list-lis index-list-lis-three">
        <img class="index-list-lis-img" src="/static/default/images/advantage-3.png" alt="">
        <p class="index-lis-title">同步课堂<br>原版教材</p>
      </li>
      <li class="index-list-lis index-list-lis-four">
        <img class="index-list-lis-img" src="/static/default/images/advantage-4.png" alt="">
        <p class="index-lis-title">同步课堂<br>原版教材</p>
      </li>
    </ul>
  </div>
</div> -->

<!-- E点就通——激发孩子学习兴趣 -->
<div class="index-inspire-interest clearfix">
  <div class="index-inspire-interest-contain clearfix">
    <h3 class="index-inspire-interest-title">E点就通名校名师课堂十大特色</h3>
    <!-- <p class="index-inspire-interest-contain">
      E点就通名校名师课堂，特邀教学经验丰富的北京一线名师讲授，课堂风格激情洋溢，风趣幽默，<br>
      课程讲解思路清晰、严谨，教会孩子学习方法、解题思路，学出成就感，学习一点就通。同时课程中重难点突出，<br>
      重点知识的课堂设计了有趣的重点知识与技巧兴趣教学，激发学习兴趣，让孩子爱上学习。
    </p> -->
    <ul class="index-inspire-interest-list clearfix">

      <li class="index-inspire-interest-lis">
        <div class="index-inspire-interest-lis-img">
          <img src="/static/default/images/course-1.png" alt="">
          <div class="index-inspire-interest-lis-modal">
            <p class="index-inspire-interest-modal-p">专家给予学期<br>学习建议</p>
          </div>
        </div>
        <div class="index-inspire-interest-lis-title">学习建议</div>
      </li>

      <li class="index-inspire-interest-lis">
        <div class="index-inspire-interest-lis-img">
          <img src="/static/default/images/course-2.png" alt="">
          <div class="index-inspire-interest-lis-modal">
            <p class="index-inspire-interest-modal-p">专家编写单元<br>学习指导</p>
          </div>
        </div>
        <div class="index-inspire-interest-lis-title">学习指导</div>
      </li>

      <li class="index-inspire-interest-lis">
        <div class="index-inspire-interest-lis-img">
          <img src="/static/default/images/course-3.png" alt="">
          <div class="index-inspire-interest-lis-modal">
            <p class="index-inspire-interest-modal-p">名师联合提供<br>学习笔记</p>
          </div>
        </div>
        <div class="index-inspire-interest-lis-title">名师笔记</div>
      </li>

      <li class="index-inspire-interest-lis">
        <div class="index-inspire-interest-lis-img">
          <img src="/static/default/images/course-4.png" alt="">
          <div class="index-inspire-interest-lis-modal">
            <p class="index-inspire-interest-modal-p">方法、口诀<br>简单易学</p>
          </div>
        </div>
        <div class="index-inspire-interest-lis-title">知识技巧</div>
      </li>

      <li class="index-inspire-interest-lis">
        <div class="index-inspire-interest-lis-img">
          <img src="/static/default/images/course-5.png" alt="">
          <div class="index-inspire-interest-lis-modal">
            <p class="index-inspire-interest-modal-p">特色教学<br>知识强化</p>
          </div>
        </div>
        <div class="index-inspire-interest-lis-title">重点难点</div>
      </li>
      <li class="index-inspire-interest-lis">
        <div class="index-inspire-interest-lis-img">
          <img src="/static/default/images/course-6.png" alt="">
          <div class="index-inspire-interest-lis-modal">
            <p class="index-inspire-interest-modal-p">提供思路<br>答案速查</p>
          </div>
        </div>
        <div class="index-inspire-interest-lis-title">习题解析</div>
      </li>
  
      <li class="index-inspire-interest-lis">
          <div class="index-inspire-interest-lis-img">
            <img src="/static/default/images/course-7.png" alt="">
            <div class="index-inspire-interest-lis-modal">
              <p class="index-inspire-interest-modal-p">对话、听力<br>全程外教录制</p>
            </div>
          </div>
          <div class="index-inspire-interest-lis-title">外教参与</div>
        </li>
  
        <li class="index-inspire-interest-lis">
          <div class="index-inspire-interest-lis-img">
            <img src="/static/default/images/course-8.png" alt="">
            <div class="index-inspire-interest-lis-modal">
              <p class="index-inspire-interest-modal-p">知识点、例题<br>一点就看</p>
            </div>
          </div>
          <div class="index-inspire-interest-lis-title">任意播放</div>
        </li>
  
        <li class="index-inspire-interest-lis">
          <div class="index-inspire-interest-lis-img">
            <img src="/static/default/images/course-9.png" alt="">
            <div class="index-inspire-interest-lis-modal">
              <p class="index-inspire-interest-modal-p">减少操作<br>喜欢的片段直接收藏</p>
            </div>
          </div>
          <div class="index-inspire-interest-lis-title">碎片收藏</div>
        </li>
  
        <li class="index-inspire-interest-lis">
          <div class="index-inspire-interest-lis-img">
            <img src="/static/default/images/course-10.png" alt="">
            <div class="index-inspire-interest-lis-modal">
              <p class="index-inspire-interest-modal-p">在线记录<br>随时查看</p>
            </div>
          </div>
          <div class="index-inspire-interest-lis-title">随堂笔记</div>
        </li>
  
    </ul>
  </div>
</div>

<!-- 适合各个地区同步课堂的课程 -->
<!-- <div class="index-fit-course clearfix">
  <div class="index-fit-course-contain">
    <div class="index-fit-title clearfix">
      <h3 class="index-fit-title-h3">多年自主研发适合个地区同步课堂的课程</h3>
      <p class="index-fit-title-msg">历经500000个小时耗费上千万聘请全国各地名校名师录课，专为不同地区不同版本教材的学生定制，</p>
    </div>
    <ul class="index-fit-contain clearfix">
      <li class="index-fit-contain-lis index-fit-contain-lis-one">支持各地区不同版本</li>
      <li class="index-fit-contain-lis index-fit-contain-lis-two "> 课堂同步名师课程无限循环听</li>
      <li class="index-fit-contain-lis index-fit-contain-lis-three">学习不花冤枉钱</li>
      <li class="index-fit-contain-lis index-fit-contain-lis-four">成绩突飞猛进</li>
    </ul>
  </div>
</div> -->

  <!-- 高标准严选师资团队 -->
  <div class="index-teachers-team clearfix">
    <h4 class="index-teachers-title">名师风采</h4>
    <p class="index-standard-teachers-msg">为了让每个孩子都享有优质的教育资源，<br>我们邀请到了国培专家，北京、黄冈、衡水三地名师，齐聚E点就通名师课堂。</p>
    <div class="index-teachers-swiper">
      <!-- Swiper -->
      <div class="swiper-container swiper-container-teachers">
        <div class="swiper-wrapper">
            <?php if(is_array($teacherList) || $teacherList instanceof \think\Collection || $teacherList instanceof \think\Paginator): $i = 0; $__LIST__ = $teacherList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?>
          <div class="swiper-slide swiper-slide-teachers" data-id='<?php echo $val['id']; ?>' title='<?php echo $val['name']; ?>'>
            <div class="swiper-slide-teachers-active-img">
              <img src="<?php echo $val['litpic']; ?>" alt="">
            </div>
            <p class="swiper-slide-teachers-introduce"><?php echo $val['name']; ?><br><?php echo $val['teacherPosition']; ?></p>
          </div>
        <?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
      </div>
      <!-- 教师简介文字部分 -->
      <div class="index-swiper-teachers">
        <ul class="index-swiper-teachers-list clearfix">
          <i class="iconfont icon-baojiaquotation2"></i>
          <?php if(is_array($teacherList) || $teacherList instanceof \think\Collection || $teacherList instanceof \think\Paginator): $k = 0; $__LIST__ = $teacherList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($k % 2 );++$k;?>
          <li class="index-swiper-teachers-lis" data-id='<?php echo $v['id']; ?>'>
            <!-- <h4 class="index-swiper-teachers-name"><?php echo $v['name']; ?> <?php echo $v['title']; ?></h4> -->
            <h4 class="index-swiper-teachers-name"><?php echo $v['title']; ?></h4>
            <div class="index-swiper-teachers-line"></div>
            <p class="index-swiper-teachers-p"><?php echo $v['content']; ?></p>
          </li>
          <?php endforeach; endif; else: echo "" ;endif; ?>
          <i class="iconfont icon-baojiaquotation"></i>
        </ul>
      </div>
      <!-- 左右箭头 -->
      <div class="swiper-button-prev">&#139;</div>
      <div class="swiper-button-next">&#155;</div>
    </div>
  </div>
  <!--  -->
  <div style="width:100%;height:865px;background:url(http://ydtvlitpic.ydtkt.com/free.jpg) no-repeat center;">
    <div class="index-free-register-contian">

      <div class="index-free-register">立即免费试听</div>

    </div>
  </div>
  <div style="width:100%;height:947px;background:url(http://ydtvlitpic.ydtkt.com/shangke.png) no-repeat center;"></div>
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
<script src="/static/default/js/swiper.min.js"></script>
<script>
layui.use(['element','layer', 'form','carousel','laypage'], function(){
  var layer = layui.layer
      ,element = layui.element
      ,carousel = layui.carousel
      ,laypage = layui.laypage;
  // layer.msg('Hello World');

  $(document).on('click','.index-video-top a',function(){
    return loginpage();
  })
});
//  名师团队轮播图
var swiper = new Swiper('.swiper-container-teachers', {
    slidesPerView: 5,
    spaceBetween: 55,
    centerInsufficientSlides: true,
    loop:true,
    // active下 居中显示
	  centeredSlides: true,	
    // autoplay:true,
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
    on: {
      slideChangeTransitionStart: function(){
        var id = $('.swiper-container-teachers .swiper-slide-active').attr('data-id');
        $('.index-swiper-teachers-lis').removeClass('active');
        $('.index-swiper-teachers-lis[data-id="'+id+'"]').addClass('active');
      },
    },
  });

// $(".swiper-container").mouseenter(function() {
//   swiper.stopAutoplay();
// }).mouseleave(function() {
//   swiper.startAutoplay();
// });
$(".swiper-slide-all-position div").each(function() {
  var maxwidth = 66;
  if ($(this).text().length > maxwidth) {
    $(this).text($(this).text().substring(0, maxwidth));
    $(this).html($(this).html() + '...');
  }
});
$(".index-swiper-teachers-p").each(function() {
  var maxwidth = 96;
  if ($(this).text().length > maxwidth) {
    $(this).text($(this).text().substring(0, maxwidth));
    $(this).html($(this).html() + '...');
  }
});
$(".index-standard-teachers").on("mouseenter",".swiper-slide", function() {
    $(this).children(".swiper-slide-bottom").stop().animate({bottom:"-100%"},500)
    $(this).children(".swiper-slide-all-position").stop().animate({top:"0"},500)
})
$(".index-standard-teachers").on("mouseleave",".swiper-slide", function() {
    $(this).children(".swiper-slide-bottom").stop().animate({bottom:"0"},500)
    $(this).children(".swiper-slide-all-position").stop().animate({top:"100%"},500)
})
$(".index-standard-teachers").on("click",".swiper-button-prev", function() {
  $(this).addClass("swiper-button-current").siblings(".swiper-button-next").removeClass("swiper-button-current")
})
$(".index-standard-teachers").on("click",".swiper-button-next", function() {
  $(this).addClass("swiper-button-current").siblings(".swiper-button-prev").removeClass("swiper-button-current")
})
// jQuery(".picScroll-left").slide({titCell:".hd ul",mainCell:".bd ul",autoPage:true,effect:"left",autoPlay:true,vis:3,trigger:"click"});
// 判断浏览器版本
// var browser = navigator.appName;
// var b_version = navigator.appVersion;
// var version = b_version.split(";");
// var trim_Version = version[1].replace(/[ ]/g, "");
// version = trim_Version.substring(4,8)
// console.log(version)
// if (browser == "Microsoft Internet Explorer" && version <=9.0) {     
//   console.log("ie9")
// }

$(".index-listen-video").click(function() {
  $(".index-video-model-position").show()
  $(".index-video-model").show()
})
$(".index-video-model-position").click(function() {
  $(".index-video-model-position").hide()
  $(".index-video-model").hide()
})
$(".index-standard-teachers-p").each(function() {
  var maxwidth = 30;
  if ($(this).text().length > maxwidth) {
    $(this).text($(this).text().substring(0, maxwidth));
    $(this).html($(this).html() + '...');
  }
});


$(window).resize(function () { 
  // widthChange();
})
// widthChange();
// function widthChange()
// {
//   var windowWidth =document.documentElement.offsetWidth||document.body.offsetWidth;
//   if(windowWidth >= 1750) {
//     $(".index-video-play").css({left:"350px"})
//   } else if(windowWidth >= 1700 && windowWidth < 1750) {
//     $(".index-video-play").css({left:"320px"})
//   } else if(windowWidth>1400 && windowWidth < 1700 ) {
//     var clac =Math.floor(windowWidth*0.13);
//     $(".index-video-play").css({left:clac})
//   } else if (windowWidth>1150 && windowWidth < 1449) {
//     var clac =Math.floor(windowWidth*0.1);
//     $(".index-video-play").css({left:clac})
//   }
// }
$(window).scroll(function(){
  // console.log($(".index-four-advantage").offset().top)
   var htmlTop = $(document).scrollTop();
  if( htmlTop > 700 && htmlTop < 800){
      $(".index-list-lis").addClass("animated fadeInUp");   
    
  } else if (htmlTop >= 1200 && htmlTop <= 1500) {
    $(".index-fit-contain .index-fit-contain-lis-one").addClass("animated fadeInLeft");    
      $(".index-fit-contain .index-fit-contain-lis-three").addClass("animated fadeInLeft");    
      $(".index-fit-contain .index-fit-contain-lis-two").addClass("animated fadeInRight");    
      $(".index-fit-contain .index-fit-contain-lis-four").addClass("animated fadeInRight"); 
  }
});
//试听视频
$(".index-video-select-lis").click(function() {
  $(".index-video-select-lis").removeClass("index-video-select-current")
  $(this).addClass("index-video-select-current")
  var val = $(this).attr('data-val');
  audilist(val);
})
audilist();

  function audilist(val){
    val = val || 7;
    $.ajax({
      url: '/index/index/auditionlist'
      ,data: {v:val}
      ,dataType: 'json'
      ,type: 'post'
      ,success: function(e){
        if(e.code == 0 && e.data != ''){
          var html = '';
            for(x in e.data){
              html += '<div class="layui-col-xs6 layui-col-sm6 layui-col-md4 index-video-list-lis">';
              html += '<div class="index-video-top">';
              html += '<img src="'+e.data[x].image+'" alt="">';
              html += '<a href="/index/course/courseParticulars?id='+e.data[x].kid+'&videoid='+e.data[x].id+'"><div class="index-video-modal">';
              html += '<i class="iconfont icon-bofang"></i></div></a>';
              html += '</div><div class="index-video-bottom">';
              html += '<span class="index-video-bottom-left">'+e.data[x].outline+'</span>';
              html += '<div class="index-video-bottom-last clearfix">';
              html += '<span class="index-video-bottom-right index-video-subject-chinese">'+e.data[x].subject+'</span>';
              html += '<span class="index-video-already-study">'+e.data[x].popu+'人已学习</span>';
              html += '</div></div></div>';
          }
          $('.index-video-list').html(html);
        }
      }
    })
  };

  // 家长关心的问题点击注册按钮
  $(".index-free-register").click(function() {
    var flag=sessionStorage.getItem("flag");
    if (flag == 1) {
      window.location="/index/person/person"
    } else {
      $(".login-register-modal").show()
      $(".register-login-contain").show()
      $(".register-login-button").eq(1).addClass("current")
                                 .siblings().removeClass("current")
      $(".register-login-content-lis").eq(1).addClass("active")
                                      .siblings().removeClass("active")
      }
  })
</script> 
</html>