<?php if (!defined('THINK_PATH')) exit(); /*a:3:{s:77:"D:\tp\ywd100\application/../Template/default/index\teachers\teachersTeam.html";i:1598606566;s:54:"D:\tp\ywd100\Template\default\index\public\header.html";i:1598606566;s:54:"D:\tp\ywd100\Template\default\index\public\footer.html";i:1598606566;}*/ ?>
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
      .b{
        display:none;
      }
    </style>
  </head>
  <body>
  <div class="all-pages">
    <!-- header -->
    
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
  <!-- 教师列表 -->
  <div class="teachers-team-contain clearfix">
      <!-- banner -->
    <div class="teachers-team-banner" style="background:url(http://ydtvlitpic.ydtkt.com/teacher-banner.jpg)no-repeat center;"></div>
    <!-- 教研老师列表 -->
    <div class="teachers-team-container clearfix">
      <!-- <div class="teachers-team-container-top"><h4 class="teachers-team-container-title">名校名师</h4></div> -->
      <!-- tab切换 -->
      <ul class="teachers_team_tab">
        <li class="teachers_team_tab_active">教研团队</li>
        <li>明星讲师</li>
        <li>名师寄语</li>
      </ul>

      <div class="teachers_team_tab_content_all  clearfix">
        <!-- 教研团队 -->
        <div class="teachers_team_tab_content teachers_team_content_style active">
          <?php if(is_array($data) || $data instanceof \think\Collection || $data instanceof \think\Paginator): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
              <div class="teachers_team_title">
                <span class="teachers_team_title_line"></span>
                <span class="teachers_team_title_name"><?php echo $vo['s_name']; ?></span>
              </div>
              <ul class="teachers-team-list clearfix">
                <?php if(is_array($vo['son']) || $vo['son'] instanceof \think\Collection || $vo['son'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['son'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
                <li class="teachers-team-lis" data-id="<?php echo $v['id']; ?>">
                  <div class="teachers-team-lis-img">
                    <img src="<?php echo $v['litpic']; ?>" alt="<?php echo $v['name']; ?>">
                    <p class="teachers-team-lis-name"><?php echo $v['name']; ?></p>
                  </div>
                  <p class="teachers-team-lis-title1"><?php echo $v['title']; ?></p>
                  <p class="teachers-team-lis-msg"><?php echo $v['false_cont']; ?> </p>
                </li>
                <?php endforeach; endif; else: echo "" ;endif; ?>
              </ul>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
          <!-- 教研团队  END -->
          <!-- 明星讲师 -->
          <div class="teachers-team-list-star teachers_team_content_style clearfix">
            <div class="teachers-team-lis-star clearfix">
              <ul class="teachers-team-lis-ul clearfix">
                <li class="teachers-team-ul-subject">
                  <ol class="teachers-team-ol clearfix">
                    <li class="teachers-team-ol-subject">
                      <p class="teachers-team-ol-subject-p">学科</p>
                      <p class="teachers-team-ol-subject-p">年级</p>
                    </li>
                    <li class="teachers-team-ol-subject">语文</li>
                    <li class="teachers-team-ol-subject">数学</li>
                    <li class="teachers-team-ol-subject">英语</li>
                    <li class="teachers-team-ol-subject">物理</li>
                    <li class="teachers-team-ol-subject">化学</li>
                  </ol>
                </li>
                <li class="teachers-team-ul-grade clearfix">
    
                  <ol class="teachers-team-ol-grade">
                    <li class="teachers-team-ol-grade-left">初一</li>
                    <li class="teachers-team-ol-grade-introduce" data-id="61">
                      <div class="teachers-team-ol-grade-img">
                        <img src="/static/default/images/lihua.png" alt="">
                      </div>
                      <div class="teachers-team-ol-grade-hover">
                        <img src="/static/default/images/lihua1.png" alt="">
                      </div>
                      <p class="teachers-team-ol-grade-name">李华</p>
                      <p class="teachers-team-ol-grade-position">北京市骨干教师</p>
                    </li>
                    <li class="teachers-team-ol-grade-introduce" data-id="65">
                      <div class="teachers-team-ol-grade-img">
                        <img src="/static/default/images/wubo.png" alt="">
                      </div>
                      <div class="teachers-team-ol-grade-hover">
                        <img src="/static/default/images/wubo1.png" alt="">
                      </div>
                      <p class="teachers-team-ol-grade-name">吴波</p>
                      <p class="teachers-team-ol-grade-position">统考命题组成员</p>
                    </li>
                    <li class="teachers-team-ol-grade-introduce" data-id="63">
                      <div class="teachers-team-ol-grade-img">
                        <img src="/static/default/images/yibo.png" alt="">
                      </div>
                      <div class="teachers-team-ol-grade-hover">
                        <img src="/static/default/images/yibo1.png" alt="">
                      </div>
                      <p class="teachers-team-ol-grade-name">易波</p>
                      <p class="teachers-team-ol-grade-position">人大附英语教师</p>
                    </li>
                    <li class="teachers-team-ol-grade-introduce"></li>
                    <li class="teachers-team-ol-grade-introduce"></li>
                  </ol>
                  
                </li>
                <li class="teachers-team-ul-grade clearfix">
                  <ol class="teachers-team-ol-grade">
                    <li class="teachers-team-ol-grade-left teachers-team-ol-grade-two">初二</li>
                    <li class="teachers-team-ol-grade-introduce" data-id="61">
                      <div class="teachers-team-ol-grade-img">
                        <img src="/static/default/images/lihua.png" alt="">
                      </div>
                      <div class="teachers-team-ol-grade-hover">
                        <img src="/static/default/images/lihua1.png" alt="">
                      </div>
                      <p class="teachers-team-ol-grade-name">李华</p>
                      <p class="teachers-team-ol-grade-position">北京市骨干教师</p>
                    </li>
                    <li class="teachers-team-ol-grade-introduce" data-id="65">
                      <div class="teachers-team-ol-grade-img">
                        <img src="/static/default/images/wubo.png" alt="">
                      </div>
                      <div class="teachers-team-ol-grade-hover">
                        <img src="/static/default/images/wubo1.png" alt="">
                      </div>
                      <p class="teachers-team-ol-grade-name">吴波</p>
                      <p class="teachers-team-ol-grade-position">统考命题组成员</p>
                    </li>
                    <li class="teachers-team-ol-grade-introduce" data-id="62">
                      <div class="teachers-team-ol-grade-img">
                        <img src="/static/default/images/maxueling.png" alt="">
                      </div>
                      <div class="teachers-team-ol-grade-hover">
                        <img src="/static/default/images/maxueling1.png" alt="">
                      </div>
                      <p class="teachers-team-ol-grade-name">麻雪玲</p>
                      <p class="teachers-team-ol-grade-position" style="left:34%;">海淀区青年骨干教师</p>
                    </li>
                    <li class="teachers-team-ol-grade-introduce" data-id="66">
                      <div class="teachers-team-ol-grade-img">
                        <img src="/static/default/images/jiaolei.png" alt="">
                      </div>
                      <div class="teachers-team-ol-grade-hover">
                        <img src="/static/default/images/jiaolei1.png" alt="">
                      </div>
                      <p class="teachers-team-ol-grade-name">焦磊</p>
                      <p class="teachers-team-ol-grade-position">最酸的物理教师</p>
                    </li>
                    <li class="teachers-team-ol-grade-introduce"></li>
                  </ol>
                </li>
                <li class="teachers-team-ul-grade clearfix">
                  <ol class="teachers-team-ol-grade">
                    <li class="teachers-team-ol-grade-left teachers-team-ol-grade-three">初三</li>
                    <li class="teachers-team-ol-grade-introduce" data-id="60">
                      <div class="teachers-team-ol-grade-img">
                        <img src="/static/default/images/panhaiyan.png" alt="">
                      </div>
                      <div class="teachers-team-ol-grade-hover">
                        <img src="/static/default/images/panhaiyan1.png" alt="">
                      </div>
                      <p class="teachers-team-ol-grade-name">潘海燕</p>
                      <p class="teachers-team-ol-grade-position">朝阳区学科带头人</p>
                    </li>
                    <li class="teachers-team-ol-grade-introduce" data-id="64">
                      <div class="teachers-team-ol-grade-img">
                        <img src="/static/default/images/zhangqin.png" alt="">
                      </div>
                      <div class="teachers-team-ol-grade-hover">
                        <img src="/static/default/images/zhangqin1.png" alt="">
                      </div>
                      <p class="teachers-team-ol-grade-name">张钦</p>
                      <p class="teachers-team-ol-grade-position">海淀区学科带头人</p>
                    </li>
                    <li class="teachers-team-ol-grade-introduce" data-id="62">
                      <div class="teachers-team-ol-grade-img">
                        <img src="/static/default/images/maxueling.png" alt="">
                      </div>
                      <div class="teachers-team-ol-grade-hover">
                        <img src="/static/default/images/maxueling1.png" alt="">
                      </div>
                      <p class="teachers-team-ol-grade-name">麻雪玲</p>
                      <p class="teachers-team-ol-grade-position" style="left:34%;">海淀区青年骨干教师</p>
                    </li>
                    <li class="teachers-team-ol-grade-introduce" data-id="66">
                      <div class="teachers-team-ol-grade-img">
                        <img src="/static/default/images/jiaolei.png" alt="">
                      </div>
                      <div class="teachers-team-ol-grade-hover">
                        <img src="/static/default/images/jiaolei1.png" alt="">
                      </div>
                      <p class="teachers-team-ol-grade-name">焦磊</p>
                      <p class="teachers-team-ol-grade-position">最酸的物理教师</p>
                    </li>
                    <li class="teachers-team-ol-grade-introduce" data-id="69">
                      <div class="teachers-team-ol-grade-img">
                        <img src="/static/default/images/yuanchaole.png" alt="">
                      </div>
                      <div class="teachers-team-ol-grade-hover">
                        <img src="/static/default/images/yuanchaole1.png" alt="">
                      </div>
                      <p class="teachers-team-ol-grade-name">袁朝乐</p>
                      <p class="teachers-team-ol-grade-position">国务院特殊津贴专家</p>
                    </li>
                  </ol>
                </li>
              </ul>
            </div>
          </div>
          <div class="teachers-team-waterfull teachers_team_content_style clearfix">
            <ul class="teachers-team-waterfull-list clearfix">
              <li class="teachers-team-waterfull-lis clearfix">
                <div class="teachers-team-waterfull-img">
                  <img src="/static/default/images/word1.png" alt="">
                </div>
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydttlitpic.ydtkt.com/index763038515.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">王大绩</p>
                    <p class="teachers-team-waterfull-position">国务院特殊津贴专家</p>
                  </div>
                </div>
              </li>
              <li class="teachers-team-waterfull-lis clearfix">
                <div class="teachers-team-waterfull-img">
                  <img src="/static/default/images/word2.png" alt="">
                </div>
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydttlitpic.ydtkt.com/index047618959.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">丁益祥</p>
                    <p class="teachers-team-waterfull-position">国培项目主讲教师</p>
                  </div>
                </div>
              </li>
              <li class="teachers-team-waterfull-lis">
                  <div class="teachers-team-waterfull-img">
                    <img src="/static/default/images/word3.png" alt="">
                  </div>
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydttlitpic.ydtkt.com/index258866188.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">李俊和</p>
                    <p class="teachers-team-waterfull-position">国培计划专家库成员</p>
                  </div>
                </div>
              </li>
              <li class="teachers-team-waterfull-lis">
                <div class="teachers-team-waterfull-img">
                  <img src="/static/default/images/word4.png" alt="">
                </div>
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydttlitpic.ydtkt.com/index740886551.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">袁朝乐</p>
                    <p class="teachers-team-waterfull-position">国务院特殊津贴专家</p>
                  </div>
                </div>
              </li>
              <li class="teachers-team-waterfull-lis">
                <div class="teachers-team-waterfull-img">
                  <img src="/static/default/images/word5.png" alt="">
                </div>
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydttlitpic.ydtkt.com/index079502781.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">余燕</p>
                    <p class="teachers-team-waterfull-position">黄冈市优秀学科带头人</p>
                  </div>
                </div>
              </li>
              <li class="teachers-team-waterfull-lis">
                <div class="teachers-team-waterfull-img">
                  <img src="/static/default/images/word6.png" alt="">
                </div>
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydttlitpic.ydtkt.com/index711765256.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">李华</p>
                    <p class="teachers-team-waterfull-position">北京市骨干教师</p>
                  </div>
                </div>
              </li>
              <li class="teachers-team-waterfull-lis">
                <div class="teachers-team-waterfull-img">
                  <img src="/static/default/images/word7.png" alt="">
                </div>
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydttlitpic.ydtkt.com/index127451470.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">曹玉珍</p>
                    <p class="teachers-team-waterfull-position">中考英语命题成员</p>
                  </div>
                </div>
              </li>
              <li class="teachers-team-waterfull-lis">
                <div class="teachers-team-waterfull-img">
                  <img src="/static/default/images/word8.png" alt="">
                </div>
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydtvideoimg.lxuemall.com/index127076497.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">孟祥征</p>
                    <p class="teachers-team-waterfull-position">化学教研负责人</p>
                  </div>
                </div>
              </li>
              <li class="teachers-team-waterfull-lis">
                <div class="teachers-team-waterfull-img">
                  <img src="/static/default/images/word9.png" alt="">
                </div>
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydttlitpic.ydtkt.com/index217678637.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">焦磊</p>
                    <p class="teachers-team-waterfull-position">最酸的物理教师</p>
                  </div>
                </div>
              </li>
              <li class="teachers-team-waterfull-lis">
                <div class="teachers-team-waterfull-img">
                  <img src="/static/default/images/word10.png" alt="">
                </div>
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydttlitpic.ydtkt.com/index267263171.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">吴波</p>
                    <p class="teachers-team-waterfull-position">统考命题组成员</p>
                  </div>
                </div>
              </li>
              <li class="teachers-team-waterfull-lis">
                <div class="teachers-team-waterfull-img">
                  <img src="/static/default/images/word11.png" alt="">
                </div>
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydttlitpic.ydtkt.com/index039334700.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">黄君</p>
                    <p class="teachers-team-waterfull-position">黄冈市骨干教师</p>
                  </div>
                </div>
              </li>
              <li class="teachers-team-waterfull-lis">
                  <div class="teachers-team-waterfull-img">
                <img src="/static/default/images/word12.png" alt="">
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydtvideoimg.lxuemall.com/index672771453.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">高拉明</p>
                    <p class="teachers-team-waterfull-position">河北省骨干教师</p>
                  </div>
                </div>
              </li>
              <li class="teachers-team-waterfull-lis">
                <div class="teachers-team-waterfull-img">
                  <img src="/static/default/images/word13.png" alt="">
                </div>
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydttlitpic.ydtkt.com/index765764414.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">朱珉</p>
                    <p class="teachers-team-waterfull-position">黄冈市骨干教师</p>
                  </div>
                </div>
              </li>
              <li class="teachers-team-waterfull-lis">
                <div class="teachers-team-waterfull-img">
                  <img src="/static/default/images/word14.png" alt="">
                </div>
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydttlitpic.ydtkt.com/index246591869.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">王学兵</p>
                    <p class="teachers-team-waterfull-position">湖北优秀化学教师</p>
                  </div>
                </div>
              </li>
              <li class="teachers-team-waterfull-lis">
                <div class="teachers-team-waterfull-img">
                  <img src="/static/default/images/word15.png" alt="">
                </div>
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydttlitpic.ydtkt.com/index527855682.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">潘海燕</p>
                    <p class="teachers-team-waterfull-position">朝阳区学科带头人</p>
                  </div>
                </div>
              </li>
              <li class="teachers-team-waterfull-lis">
                <div class="teachers-team-waterfull-img">
                  <img src="/static/default/images/word16.png" alt="">
                </div>
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydtvideoimg.lxuemall.com/index763885338.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">樊清</p>
                    <p class="teachers-team-waterfull-position">衡水优秀教研组长</p>
                  </div>
                </div>
              </li>
              <li class="teachers-team-waterfull-lis">
                <div class="teachers-team-waterfull-img">
                  <img src="/static/default/images/word17.png" alt="">
                </div>
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydttlitpic.ydtkt.com/index077032983.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">易波</p>
                    <p class="teachers-team-waterfull-position">人大附英语教师</p>
                  </div>
                </div>
              </li>
              <li class="teachers-team-waterfull-lis">
                <div class="teachers-team-waterfull-img">
                  <img src="/static/default/images/word18.png" alt="">
                </div>
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydttlitpic.ydtkt.com/index736827323.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">方红梅</p>
                    <p class="teachers-team-waterfull-position">湖北国培计划辅导教师</p>
                  </div>
                </div>
              </li>
              <li class="teachers-team-waterfull-lis">
                <div class="teachers-team-waterfull-img">
                  <img src="/static/default/images/word19.png" alt="">
                </div>
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydttlitpic.ydtkt.com/index439111155.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">孙绪廷</p>
                    <p class="teachers-team-waterfull-position">语文教研组负责人</p>
                  </div>
                </div>
              </li>
              <li class="teachers-team-waterfull-lis">
                <div class="teachers-team-waterfull-img">
                  <img src="/static/default/images/word20.png" alt="">
                </div>
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydttlitpic.ydtkt.com/index475685893.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">项中心</p>
                    <p class="teachers-team-waterfull-position">黄冈中学数学备课组长</p>
                  </div>
                </div>
              </li>
              <li class="teachers-team-waterfull-lis">
                <div class="teachers-team-waterfull-img">
                  <img src="/static/default/images/word21.png" alt="">
                </div>
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydttlitpic.ydtkt.com/index606156724.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">王国恩</p>
                    <p class="teachers-team-waterfull-position">物理教研组长</p>
                  </div>
                </div>
              </li>
              <li class="teachers-team-waterfull-lis">
                <div class="teachers-team-waterfull-img">
                  <img src="/static/default/images/word22.png" alt="">
                </div>
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydttlitpic.ydtkt.com/index614296978.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">凃秉清</p>
                    <p class="teachers-team-waterfull-position">黄冈市骨干教师</p>
                  </div>
                </div>
              </li>
              <li class="teachers-team-waterfull-lis">
                <div class="teachers-team-waterfull-img" style="width:98%;margin-left:1%;">
                  <img src="/static/default/images/word23.png" alt="">
                </div>
                <div class="teachers-team-waterfull-content">
                  <div class="teachers-team-waterfull-pic"><img src="http://ydttlitpic.ydtkt.com/index766566944.jpg" alt=""></div>
                  <div class="teachers-team-waterfull-right">
                    <p class="teachers-team-waterfull-name">吴援朝</p>
                    <p class="teachers-team-waterfull-position">黄冈中学语文教研组长</p>
                  </div>
                </div>
              </li>
            </ul>
          </div>
        <!-- 明星讲师  END -->
        <!-- 名师寄语 -->
      </div>
      
  </div>
  <!-- 教师详情弹窗 -->
  <div class="teachers-team-modal-all"></div>
  <div class="teachers-team-modal">
    <div class="teachers-team-modal-contain"></div>
  </div>
  <div class="teachers-waterfull-modal clearfix"></div>
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

  </div>
  </body>
  <script>
    $(".teachers-team-lis-msg").each(function() {
      var maxwidth = 40;
      if ($(this).text().length > maxwidth) {
        $(this).text($(this).text().substring(0, maxwidth));
        $(this).html($(this).html() + '...');
      }
    })
    $(".teachers-team-lis-title1").each(function() {
      var maxwidth = 10;
      if ($(this).text().length > maxwidth) {
        $(this).text($(this).text().substring(0, maxwidth));
        $(this).html($(this).html() + '...');
      }
    })
    // 点击教师出现教师详情
    $("li.teachers-team-lis, .teachers-team-ol-grade-introduce").click(function() {
      var teachersId = $(this).data("id")
      if( teachersId != undefined ){
        $.ajax({
          url:"/index/Teachers/getTeacherInfo",
          data:{id:teachersId},
          type: 'post',
          async: false,
          dataType: 'json',
          success:function(res) {
            var data = res.data;
            var html = '';
            html = ' <div class="teachers-team-modal-close">x</div>'+
                    '<div class="teachers-team-modal-img">'+
                      '<img src="'+data[0].litpic+'">'+
                    '</div>'+
                    '<div class="teachers-team-modal-right">'+
                      '<p class="teachers-team-modal-name">'+data[0].title+'</span>'+
                      '<a href="/index/course/course?pageNum=10" class="go_study">去学习</a></p>'+
                      '<p class="teachers-team-modal-msg">'+data[0].content+'</p>'+
                    '</div>';
            $(".teachers-team-modal-contain").html(html)
          },
          error:function(res) {
            console.log(res)
          }
        })
        $("div.teachers-team-modal-all").show().siblings("div.teachers-team-modal").show().end()
      }else{
        return false;
      }
    return false;
  })
    // 教师详情窗口关闭 
    $(document).on("click",".teachers-team-modal-close",function() {
      $(this).parents(".teachers-team-modal").hide().siblings("div.teachers-team-modal-all").hide()
    })
    layui.use(['element','layer', 'form','carousel','laypage'], function(){
      var layer = layui.layer
          ,element = layui.element
          ,form = layui.form
          ,carousel = layui.carousel
          ,laypage = layui.laypage;


    });

    //tab切换
    $('.teachers_team_tab li').click(function(){
      var index = $(this).index();
      $(this).addClass('teachers_team_tab_active').siblings().removeClass('teachers_team_tab_active');
      $('.teachers_team_content_style').eq(index).show().siblings().hide();
      itemWaterfull();
    })
    // 瀑布流
  function itemWaterfull() {
      var margin = 0;  
      var items = $(".teachers-team-waterfull-lis"); 
      var item_width = items[0].offsetWidth + margin; 
      $(".teachers-team-waterfull-list").css("padding", "0"); 
      var container_width = $(".teachers-team-waterfull-list")[0].offsetWidth; 
      var n = parseInt(container_width / item_width); 
      var container_padding = (container_width - (n * item_width)) / 2; 
      $(".teachers-team-waterfull-list").css("padding", "0 " + container_padding + "px");
      //寻找数组最小高度的下标
      function findMinIndex(arr) {
          var len = arr.length, min = 999999, index = -1;
          for(var i = 0; i < len; i++) {
              if(min > arr[i]) {
                  min = arr[i];
                  index = i;
              }
          }
          return index;
      }
      //放置item
      function putItem() {
      var items_height = [];  
      var len = items.length;  
      for(var i = 0; i < len; i++) {
          var item_height = items[i].offsetHeight; 
          //放置在第一行的item
          if(i < n) {
              items_height[i] = item_height; 
              items.eq(i).css("top", 0);
              items.eq(i).css("left", i * item_width);

          } else {    
              var final_row_fir = parseInt(len / n) * n;
              //处于最后一行
              if(final_row_fir <= i) {
                  var index = i - final_row_fir;  
                  items.eq(i).css("top", items_height[index] + margin);
                  items.eq(i).css("left", index * item_width);
                  items_height[index] += item_height + margin;
              } else {      
                  var min_index = findMinIndex(items_height);  //寻找最小高度
                  if(min_index == -1) {
                      return ;
                  }
                  items.eq(i).css("top", items_height[min_index] + margin);
                  items.eq(i).css("left", min_index * item_width);
                  items_height[min_index] += item_height + margin;  //高度数组更新
              }
          }
      }
      var max_height = Math.max.apply(null, items_height);
      $(".teachers-team-waterfull-list").css("height", max_height);   //最后更新容器高度
  }
      putItem();
  }
 
  window.onresize = function() {itemWaterfull();}; //在窗口大小改变后，item重新放置

  $(".teachers-team-waterfull-lis").click(function() {
    var index = $(this).index()+1;
    var html = '';
    html = '<div class="waterfull-close">x</div>'+
          '<div class="waterfull-img">'+
            '<img src="/static/default/images/word'+index+'.png" alt="">'+
          '</div>';
    $(".teachers-waterfull-modal").html(html).show()
    $(".teachers-team-modal-all").show()
  })
  $(document).on('click','.waterfull-close',function() {
    $(".teachers-waterfull-modal").hide()
    $(".teachers-team-modal-all").hide()
  })
  </script> 
  </html>