<?php if (!defined('THINK_PATH')) exit(); /*a:2:{s:68:"D:\tp\ywd100\application/../Template/mobile/index\course\course.html";i:1598606566;s:53:"D:\tp\ywd100\Template\mobile\index\public\footer.html";i:1598606566;}*/ ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <title>E点就通名校名师同步课堂</title>
    <link href="/static/mobile/css/mui.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="/static/mobile/css/style.css">
    <link rel="stylesheet" href="/static/mobile/css/zhangxueyan.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mescroll.js@1.4.1/mescroll.min.css">
    <style>
      .common-course{
        width: 22%;
        line-height: .42rem;
        float: left;
        font-size: .16rem;
        font-weight: 400;
        color: rgba(255,255,255,1);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        float: left;
        position: relative;
      }
    </style>
</head>
<body>
  <!-- 公共头部header -->
  <header class="mui-bar mui-bar-nav common-header">
      <div class="common-course">更多课程</div>
    <!-- <div class="common-select-grade common-header-select iconfont">选择年级</div> -->
    <h1 class="common-title">E点就通</h1>
    <?php if((!isset($user))): ?>
    <div class="login-register">
      <div class="common-login"><a href="/index/course/goWx">登录</a></div>
      <!-- <div class="common-register"><a href="/index/login/commonRegister">注册</a></div> -->
    </div>
    <?php else: ?>
      <div class="after-login"><img src="http://ydtvlitpic.ydtkt.com/<?php echo !empty($personInfo['litpic'])?$personInfo['litpic']:'photo.jpg'; ?>" alt=""></div>
      <?php endif; ?>
  </header>
  <!-- 顶部导航栏选择年级弹出部分 -->
  <!-- content -->
    <div class="course-search">
      <form action="web_search" class="course-search-form">
        <div class="course-search-btn iconfont iconguanbi"></div>
        <input type="text" class="mui-input-clear" name='name' placeholder="">
      </form>
      <div class="course-search-screen iconfont iconshaixuan"></div>
    </div>
    <!-- 张雪燕老师开始部分 -->
    <div class="zhangxueyan-content" style="padding-top:24%;display:none">
    <!-- banner -->
    <div class="zhang-banner">
      <img src="/static/mobile/images/banner-7.jpg" alt="">
    </div>
    <!-- tab -->
    <ul class="zhang-tab-ul">
      <li class="current">老师介绍</li>
      <li>主授课程</li>
    </ul>
    <div class="zhang-content">
      <div class="content-lis content-introuce clearfix">
        <div class="introduce-box">
          <h3 class="introduce-title"><i class="teacher-img"></i>教师风采</h3>
          <ul class="introduce-ul">
            <li><i class="introduce-ul-i"></i>国家高师级心理咨询师</li>
            <li><i class="introduce-ul-i"></i>家庭教育专家团专家</li>
            <li><i class="introduce-ul-i"></i>拥有12年家庭教育工作专业经历</li>
            <li><i class="introduce-ul-i"></i>讲授全国大型讲座1800余场著作：《爱的正能量》、《父母卷》等</li>
            <li><i class="introduce-ul-i"></i>一对一辅导帮助家庭3000余个</li>
            <li><i class="introduce-ul-i"></i>帮助无数孩子成功逆袭，其中不乏目前已就读清华大学、北京大学、悉尼大学、中国人民大学等世界一流大学</li>
            <li><i class="introduce-ul-i"></i>著作：《爱的正能量》、《父母卷》等</li>
          </ul>
        </div>
        <div class="introduce-box">
          <h3 class="famouse-title"><i class="teacher-img"></i>张雪燕老师擅长</h3>
          <ul class="introduce-ul famouse-ul">
            <li><i class="introduce-ul-i"></i>通过孩子的行为模式发现孩子内心世界</li>
            <li><i class="introduce-ul-i"></i>孩子成长动力和学习状态调整</li>
            <li><i class="introduce-ul-i"></i>帮助孩子走出青春期的迷茫</li>
            <li><i class="introduce-ul-i"></i>一对一辅导各种问题家庭</li>
            <li><i class="introduce-ul-i"></i>引领家长成为智慧父母</li>
            <li><i class="introduce-ul-i"></i>解决家长在教育孩子的过程中遇到的问题</li>
            <li><i class="introduce-ul-i"></i>引导父母和孩子建立和谐的亲子关系</li>
            <li><i class="introduce-ul-i"></i>手把手教会父母正确教育孩子的方式方法</li>
          </ul>
        </div>
        <div class="introduce-box">
          <h3 class="famouse-title book-title"><i class="teacher-img"></i>张雪燕老师著作</h3>
          <ul class="book-img-ul">
            <li>
              <img src="/static/mobile/images/zhangxueyan.png" alt="">
              <p>《爱的正能量》</p>
            </li>
            <li>
              <img src="/static/mobile/images/book-zhang.png" alt="">
              <p>《父母卷》</p>
            </li>
          </ul>
        </div>  
        <div class="introduce-box">
          <h3 class="famouse-title course-title"><i class="teacher-img"></i>上课场景</h3>
          <ul class="course-img-ul">
            <li><img src="/static/mobile/images/img-8.png" alt=""></li>
            <li><img src="/static/mobile/images/img-9.png" alt=""></li>
            <li><img src="/static/mobile/images/img-10.png" alt=""></li>
            <li><img src="/static/mobile/images/img-11.png" alt=""></li>
            <li><img src="/static/mobile/images/img-12.png" alt=""></li>
          </ul>
        </div>                    
      </div>
      <div class="content-lis content-course" style="display: none;">
        <div class="zhang-course-list">
          <?php if(is_array($res) || $res instanceof \think\Collection || $res instanceof \think\Paginator): $i = 0; $__LIST__ = $res;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
            <div class="zhang-course-lis">
              <h3 class="zhang-course-lis-title"><?php echo $v['outline']; ?></h3>
              <span class="zhang-course-lis-item">教育培训</span>
              <div class="zhang-course-lis-img clearfix">
                <img src="/static/mobile/images/litpic.png" alt="">
                <div class="zhang-course-lis-right">
                  <span class="zhang-course-lis-teacher">授课</span>
                  <span class="zhang-course-lis-name">张雪燕</span>
                </div>
              </div>
              <div class="line"></div>
              <div class="zhang-course-lis-num">
                <span>已有<?php echo $v['likes']; ?>+人学习 <i class="zhang-course-lis-listen"></i></span>
                <a href="/index/course/audio?id=<?php echo $v['id']; ?>">立即听课 <i class="zhang-course-lis-arrow"></i></a>
              </div>
            </div>
          <?php endforeach; endif; else: echo "" ;endif; ?>
        </div>
      </div>
    </div>
  </div>
    <!-- 张雪燕老师结束部分 -->
    <!-- 7-9年级课程列表部分 -->
    <div class="mui-content course-page mui-scroll-wrapper clearfix">

    <!-- content -->
      <div class="mescroll mui-scroll course-contain"  id="mescroll">
        <ul id="OA_task_1" class="mui-table-view course-list">
          
        </ul>
      </div>

    </div>
      <!-- 点击筛选按钮出现的蒙版页面 -->
      <div class="course-list-modal">
        <div class="course-list-div">
          <p class="course-list-p">点击此处返回<span class="iconfont iconxiala"></span></p>
        </div>
        <div class="course-list-change">
          <dl class="course-change-list course-change-subject clearfix">
            <?php if(is_array($getgrade) || $getgrade instanceof \think\Collection || $getgrade instanceof \think\Paginator): $i = 0; $__LIST__ = $getgrade;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
            <dd class="course-change-lis <?php if($key == 0): ?> course-change-current <?php endif; ?>" data-gid="<?php echo $vo['gid']; ?>" data-sid="<?php echo $vo['sid']; ?>" data-type="2"><?php echo $vo['name']; ?></dd>
            <?php endforeach; endif; else: echo "" ;endif; ?>
            <dd class="course-change-lis" data-type="1" style="width:66.245%">教育专家张雪燕</dd>
          </dl>
          <div class="course-change-btn">
            <div class="course-change-reset">重置</div>
            <div class="course-change-confirm reset-confirm-this" onclick="selectClass()">确定</div>
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

  <!-- footer -->
</body>
</html>
<script src="/static/mobile/js/mui.min.js"></script>
<script src="/static/mobile/js/jquery-2.2.3.js"></script>
<script src="/static/mobile/js/fastclick.js"></script>
<script src="/static/mobile/js/public.js"></script>
<script src="/static/mobile/js/iconfont/iconfont.js"></script>
<script src="/static/mobile/js/course.js"></script>
<script src="https://cdn.jsdelivr.net/npm/mescroll.js@1.4.1/mescroll.min.js" charset="utf-8"></script>
<script>
  $(".zhang-tab-ul li").click(function() {
    $(".zhang-tab-ul li").removeClass("current")
    $(this).addClass("current")
    $(".content-lis").hide()
    $(".content-lis").eq($(this).index()).show()
  })
</script>
<script>
  function isIphoneX() {
    return /iphone/gi.test(navigator.userAgent) && (screen.height == 812 && screen.width == 375)
  } 
  if(isIphoneX()) {
    $(".course-change-list:first-child").css({"margin":"20% 0 0 4%"})
    $(".course-list-change").css({height:"94%"})
    $(".course-search").css({top:"5.2%"})
  }
  // 顶部选择年级点击事件
  $(".common-header .iconfont").click(function() {
    if ($(this).hasClass("common-select-grade")) {
      $(this).removeClass("common-select-grade").addClass("common-grade-click")
    } else {
      $(this).removeClass("common-grade-click").addClass("common-select-grade")
    }
  })
  // 
  $(".course-change-lis").click(function() {
    $(this).addClass("course-change-current")
           .siblings()
           .removeClass("course-change-current")

  })
  // 点击筛选按钮左滑出现页面
  $(".common-course").click(function() {
    $(".course-list-modal").animate({left:"0"},800)
  })
  $(".course-search-screen").click(function() {
    $(".course-list-modal").animate({left:"0"},800)
  })
  $(".course-list-div").click(function() {
    $(".course-list-modal").animate({left:"100%"},800)
  })
   var mescroll = new MeScroll('mescroll',{
        //下拉刷新
        down: {
            callback: function(){
                mescroll.endSuccess();
            }
        },
        //上拉加载
        up: {
            callback: getData,          //回调

            page: {
                num: 1,
                size: 5
            },
            htmlNodata: '<p class="upwarp-nodata">-- 我是有底线的 --</p>',
            noMoreSize: 5,
            empty: {
                //列表第一页无任何数据时,显示的空提示布局; 需配置warpId才显示
                warpId: "mescroll", //父布局的id (1.3.5版本支持传入dom元素)
                icon: "", //图标,默认null,支持网络图
                tip: "暂无相关数据~" //提示
            },

        }
    })
  function getData(){
    var type = $('.course-change-current').attr('data-type');
    if(type == 1)
    {
      return false;
    }
    var courseData ={};
    var term = "";
    var grade = "";
    if(sessionStorage.getItem("page")){
      pager.page = sessionStorage.getItem("page");
    }
    courseData.term = $('.course-change-current').attr('data-sid');

    courseData.grade = $('.course-change-current').attr('data-gid');

    $.ajax("/index/course/getCourseList",{
      data:{
          "pagenow":pager.page,
          "pageNum":pager.size,
          "courseData":courseData
      },
      dataType:'json',
      type:'post',
      success:function(data){
        var res = data.data;
        if(data.error_code==1){
        var html='';
        for(x in res){
          html+='<li class="mui-table-view-cell">'+
              '<img class="mui-media-object mui-pull-left course-list-img" src="'+res[x].img+'">'+
                '<div class="mui-media-body">'+
                  '<p class="course-list-title">'+res[x].name +'('+res[x].Semester+')</p>'+
                  '<p class="course-list-num">课程数量：'+res[x].countClassChapter+'节</p>'+
                  '<p class="course-list-people">已有'+res[x].popularity+'人学习了该课程</p>'+
                  '<span class="course-list-study"><a href="/index/course/goWx">立即学习</a></span>'+
                '</div></li>';          
        }
        $('#OA_task_1').html(html);
        // $('#page').html(res.msg);
      }else{
        var html=data.msg;
        $('#OA_task_1').html(html);
        // $('#page').html('');
      }
             
      　　　　//这里很重要，这里获取页码 公式：总条数/每页显示条数 （这里的处理是根据接口返回数据来做的处理 最下面有接口返回数据的格式）
          totalPage = data.count%pager.size!=0?parseInt(data.count/pager.size)+1:data.count/pager.size;
          //总页码等于当前页码，停止上拉下拉
          if(totalPage > pager.page){
            pager.page++;
            sessionStorage.setItem("page",pager.page);
            mescroll.endSuccess(5*parseInt(pager.page)+1, true);
          }else{
             mescroll.endSuccess(5*parseInt(pager.page)+1, false);
          }          
      },
      error:function(){
          //异常处理；
          mescroll.endErr();
      }
  })
}

//  课程页面重置
$(".course-change-btn .course-change-reset").click(function() {
  $(".course-change-item").children(".course-change-lis")
                                   .removeClass("course-change-current")
                                   .eq(0)
                                   .addClass("course-change-current")
  $(".course-change-subject").children(".course-change-lis")
                                   .removeClass("course-change-current")
                                   .eq(0)
                                   .addClass("course-change-current")
})
</script>