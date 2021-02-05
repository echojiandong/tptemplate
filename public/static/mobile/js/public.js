function isIphoneX() {
  return /iphone/gi.test(navigator.userAgent) && (screen.height == 812 && screen.width == 375)
} 

//点击蒙版消失

if(isIphoneX()) {
  $(".header-nav-list").css({"top":"4.7%"})
}
  // 顶部选择年级
    $(document).on('click','.common-header .common-header-select',function(){
    if ($(this).hasClass("common-select-grade")) {
      $(this).removeClass("common-select-grade").addClass("common-grade-click")
      $(".header-nav-list").fadeIn(400)
    } else {
      $(this).removeClass("common-grade-click").addClass("common-select-grade")
      $(".header-nav-list").fadeOut(400)
    }
    
  })
  $('.header-nav-list').click(function(){
    $(this).fadeOut(400);
  })
  //选择年级
  $('.header-select-grade-lis').click(function(){
    $('.header-select-grade-lis').removeClass('header-select-current');
    $(this).addClass('header-select-current');
    var grade_id = $(this).attr('data-val');
    sessionStorage.setItem('glodalGuard',grade_id);
    $.ajax({
        url:'/index/login/setGlodalClassId'
        ,data: {grade_id:grade_id}
        ,dataType: 'json'
        ,type: 'post'
        ,success:function(res){
            window.location.reload();
        }
    })
  })
  //全局年级渲染
  var grade_id = sessionStorage.getItem('glodalGuard');
  if(grade_id != null){
      let _html = $('li[data-val="'+grade_id+'"]').html();
      $('.common-header-select').html(_html);
  }

    //单点登录
  var user_id = sessionStorage.getItem('userId');
  var ws = new WebSocket("ws://127.0.0.1:2346");
  ws.onopen = function() {
        if(user_id != null){
          user_id = user_id+'phone';
          ws.send(user_id);
        }
        console.log('连接socket，当前userid：'+user_id);
  };
  ws.onmessage = function(evt){
      var res = evt.data;
      if(JSON.parse(res).code == 1001){
        mui.alert(JSON.parse(res).msg,'',function(){
          // ws.close();
          sessionStorage.removeItem('userId');
          window.location.href = '/index/login/quitLanding';
        });
      }
  }

  //导航栏中全局搜索
  $(".index-nav-search").focus(function() {
    $(this).val("")
  })
  $(".index-nav-search").blur(function() {
    let gloalSearch = $(this).val();
    if(gloalSearch != ''){
        window.location.href="/index/index/glodalsearch?gloalSearch="+gloalSearch;
    }
  })

// 用户进入页面判断是否登录
flag = 0; //是否登录
isLogin();
function isLogin() {
  $.ajax({
      url:"/index/login/judgeLogin",
      dataType:'json',
      async:false,
      success:function(data){

        if(data.msg!=1){
          flag = 1;
          sessionStorage.setItem("flag",1)
            //手机端课程的学习进度显示
            // if(data.msg==3){
            //   $(".index-login-progress").css({display:"block"})
            //   $(".index-listen-video").css({display:"none"})
            // }
        }else{
              sessionStorage.setItem("flag",0)
        }
      }
  })
}

mui('body').on('tap','a',function(){
  var forThis = this;
  //底部导航
  if($(forThis).hasClass('is_footer')){
      var url = new Array('/','/index/course/course?pageNum=5',"/index/teachers/teachersTeam",'/index/course/goWx'); //,'/index/service/service'
      var _index = $(forThis).parents('li').index();
      sessionStorage.setItem('_index',_index);
      if(_index != 3){
        document.location.href=url[_index];
      }else{
        var flag = sessionStorage.getItem('flag');
        console.log(flag)
        if(flag == 1){
          document.location.href=url[_index];
        }else{
          document.location.href='/index/course/goWx';
        }
      }
  }else if($(forThis).hasClass('exit-login')){
      //退出登录
      sessionStorage.setItem('_index',1);
      sessionStorage.removeItem('userId');
      sessionStorage.setItem('_index',0)
      document.location.href = '/index/login/quitLanding';
  }else{
      //其他a标签跳转
      document.location.href=this.href
  }
})

// 底部导航栏的样式
var  _ul = $('.navigation-bar-list li');
var arr = new Array('iconshouye','iconshipinbofang',"iconjiaoshituandui",'icongerenzhongxin');//"iconkefuyanzheng",
// 'icongerenzhongxin2','iconshipinbofang2',"iconjiaoshituandui2",'icongerenzhongxin2'
var _index = sessionStorage.getItem('_index') == null?0:sessionStorage.getItem('_index');
$.each(_ul,function(i,v){
  if(i != _index){
    _ul.eq(i).find('use').attr('xlink:href','#'+arr[i]);  
  }else{
    _ul.eq(i).find('use').attr('xlink:href','#'+arr[i]+'2');
    _ul.find(".navigation-bar-p").removeClass("current");
    _ul.eq(i).find(".navigation-bar-p").addClass("current");
  }
  _ul.eq(i).children('a').attr('href','javascript:;');
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

  document.documentElement.addEventListener('touchstart', function (event) {
    if (event.touches.length > 1) {
      event.preventDefault();
    }
  }, false);
}

$(function() {    
  FastClick.attach(document.body);    
});  
