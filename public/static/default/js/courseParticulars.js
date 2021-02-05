function isLogin()
{
  var user_id = sessionStorage.getItem('userId');
  if(user_id == null)
  {
    location=location;
  }
}
 //视频右边知识点、资料下载切换
 $(document).on('click','.course_contain_tab li',function(){
  var index = $(this).index();
  $(this).addClass('course_contain_tab_active').siblings().removeClass('course_contain_tab_active');
  $('.course-particulars-knowledge').eq(index).show().siblings().hide();
})    
    
// 下载例题 下载资料
$('.layui-this-down-example').click(function(){
  // alert('学习指导');
})

$('.layui-this-down-data').click(function(){
  // alert('下载资料');
})

    
    // 三级目录列表
    // 一级目录
    // $(".course-login-list").on('click','.course-video-list-name',function() {
    //   var firstIndex = $(this).data("value")
    //   $(this).css({color:"#fff",background:'#272727'})
    //          .parent().siblings()
    //          .children(".course-video-list-name").css({color:"#666",background:'#2d2d2d'})
    //          .siblings()
    //          .children().find(".course-video-a")
    //          .css({color:"#666",background:"#2d2d2d"})
    //          .siblings().children()
    //          .find(".login-content-child-a")
    //          .removeClass("login-content-a-current")
    //   if($(this).children().hasClass("login-course-arrow-top")) {
    //     $(this).children().removeClass("login-course-arrow-top")
    //            .parent().siblings(".course-video-list").addClass("dn")
    //   } else{
    //     $(this).children().addClass("login-course-arrow-top")
    //            .parent().siblings(".course-video-list").removeClass("dn")
    //   }
    // })
    // 二级目录
    // $(".course-login-list").on('click','.course-video-list-lis .course-video-a', function() {
    //   var secondIndex = $(this).data("item")
    //   $(this).css({color:"#fff"})
    //       .parent().siblings()
    //       .children(".course-video-a").css({color:"#666",background:"#2d2d2d"})
    //       .siblings().children()
    //       .find(".login-content-child-a")
    //       .removeClass("login-content-a-current")
    //   if($(this).children().hasClass("login-course-arrow-top")) {
    //     $(this).children().removeClass("login-course-arrow-top")
    //       .parent().siblings(".login-content-child").addClass("dn")
    //   } else{
    //     $(this).children().addClass("login-course-arrow-top")
    //       .parent().siblings(".login-content-child").removeClass("dn")
    //   }
    // })
    // $(".course-login-list").on('click','.login-content-child-a',function() {
    //   src = $(this).data("val")
    //   var txt =$(this).text();
    //   videoId=$(this).data("id");
    //   videoKid=$(this).data("kid");
    //   $('#courseTitle').text(txt);
    //   // myVideoLog();
    //   $(this).addClass("login-content-a-current")
    //          .parent().siblings()
    //          .children().removeClass("login-content-a-current")
    //          .parents(".course-video-list-lis").siblings().children().find(".login-content-child-a")
    //          .removeClass("login-content-a-current")
    //          .parents(".course-login-list-lis").siblings().children().find(".course-video-list-name")
    //          .css({color:"#666",background:"#2d2d2d"})
    //          .siblings().children()
    //          .find(".course-video-a")
    //          .css({color:"#666",background:"#2d2d2d"})
    //          .siblings().children()
    //          .find(".login-content-child-a")
    //          .removeClass("login-content-a-current")
    // })
var link=$('#courseInfo').val();
    // 七牛云视频播放
    var container = document.getElementById("player");
    var player = new QPlayer({
      container: container,
      // muted: false,
      autoplay: false,
      defaultViewConfig: {
        showControls: true,              // 是否一直显示控件
      },
      qualityList: [  {
        url: link,
        name: "超清1080P",
      },{
        url: link,
        name: "高清720P",
      }, {
        url: link,
        name: "标清480P",
      }]
    });

$("#player").children().find("video").attr("controlsList","nodownload")
$("#player").children().find("video").attr("poster","/static/default/images/poster.jpg")
// var setTimer = null;
// var setTimer1 = null;
var timer;
var timer_play;
var videoData; //试听时间
var timestamp = 0;
var k_id = $('#k_id').val();
var videoId = $('#video').val();

window.onload = function(){
  video_points();   //视频播放页对应的知识点
  var start=$('#start_time').val();
  var video_id=$('#video').val();
  var kid=$('#video_class').val();
  var productStatus=$('#productStatus').val();
  $.ajax({
    type: "post",
    url: "/index/course/checkVideoPlay",
    data: {video_id:video_id,kid:kid,productStatus:productStatus},
    success: function (res) {
      videoData = res.data;
    }

    
  });
  // console.log(start);
  // var currentTime = videoPlay.currentTime;
  videoPlay.currentTime =start;
  player.on('play',playPlayer);//注册播放器开始播放事件
  player.on('pause',pausePlayer);//注册播放器暂停播放事件
  player.on("ended", handleReady);//注册视频播放完成事件
  player.on("timeupdate", timeupdate);//注册视频播放完成事件
  // player.on("error", error_start);
}
var video_code=1;
//刷新浏览器或者关闭浏览器更新用户观看记录
window.onbeforeunload=function(){
  var video_class_id=$('#video_class').val();
  var product_id=$('#product_id').val();
  var productStatus=$('#productStatus').val();
  var videoid=$('#video').val();
  var videoStartTime=$('#videoStartTime').val();
  var nowTime=Math.ceil(player.currentTime);
  nowTime = Math.floor(nowTime/60)+':'+(nowTime%60);
  var endtime = Date.parse(new Date());
  var study_time = endtime/1000 - timestamp/1000;
  study_time = Math.floor(study_time/60)+':'+(study_time%60);
  var person_id=$('#person_id').val();
  $.post('/index/course/closeUpdateCourseLog',{video_class_id:video_class_id,videoid:videoid,nowTime:nowTime,person_id:person_id,study_time:study_time,videoStartTime:videoStartTime,productStatus:productStatus,product_id:product_id})
}
window.onunload = function(){
  clearInterval(accumlationTime);
}

  // 听课时间是否超过一小时
var accumTime=0;
var accumlationTime = setInterval(myTimer, 1000);
function myTimer() {
  if (player.isPlay == true) {
    accumTime++;
    //判断是否等于一个小时
    switch (accumTime){
      case 3600: 
        Hours();
        $('.hours').text('1');
      break;
      case 7200: 
        Hours();
        $('.hours').text('2');
      break;
      case 10800: 
        Hours();
        $('.hours').text('3');
        clearInterval(accumlationTime);
      break;
    }
    return false;
  } 
  // 
  function Hours(){
    //暂停视频
    videoPlay.pause();
    player.config.defaultViewConfig.showControls= true;
    $('.hours_model_content').show();
    $('.hours_model').show();
  }
  //点击关闭弹框
  $('.hours_close, .hours_content_know').click(function(){
    $('.hours_model_content').hide();
    $('.hours_model').hide();
  })
}

//视频播放器点击开始播放事件
// var a = 1,b = 1,a1;
function playPlayer()
{
  timestamp = Date.parse(new Date());
  player.config.defaultViewConfig.showControls= false;
  console.log(videoPlay.currentTime)
  // videoPlay.currentTime = timer;
  /*console.log(player.currentTime+'点击播放打印')
  console.log(player.config.defaultViewConfig)*/
  // if(timer != undefined && timer != 0 && (a%2) == 1 && b == 2){
  //   setTimer1=setInterval(function(){
  //     // timer++;
  //     timer = Math.floor(player.currentTime)
  //     var timerNow = time_To_hhmmss(timer + 1);
  //     $(".contain_right_end_time span").html(timerNow)
  //   },1000);
  //   $(".course-particulars-collect").attr('title','停止')  //小浮框显示停止
  // } 
  // a++;
}
//视频播放时间更新
function timeupdate(e){
  var id = $('#video_class').val();
  var productStatus = $('#productStatus').val();
  if(videoData.is_audi == 1){
  if(videoData.is_buy == 0){
    if(e >= videoData.audi_time){
      videoPlay.pause();
      player.config.defaultViewConfig.showControls= true;
      if(videoData.is_login){
        // layer.msg('已经到达试听截止时间，如需继续观看，请先购买课程！',{icon:0,time:1500});
          $('.card-activation-power').text('已经到达试听截止时间，如需继续观看，请先购买课程！')
            $('.card-activation-confirm').show();
            $('.card-activation-modal').show();
            $('.card-activation-modal-confirm-close').click(function(){
              $('.card-activation-confirm').hide();
              $('.card-activation-modal').hide();
            })
            $('.card-activation-modal-confirm-btn').click(function(){
                $('.card-activation-confirm').hide();
                $('.card-activation-modal').hide();
            })
      }else{
        // layer.msg('已经到达试听截止时间，如需继续观看，请先登录！',{icon:0,time:1500});
        $('.card-activation-power').text('已经到达试听截止时间，如需继续观看，请先登录！')
            $('.card-activation-confirm').show();
            $('.card-activation-modal').show();
            $('.card-activation-modal-confirm-close').click(function(){
              $('.card-activation-confirm').hide();
              $('.card-activation-modal').hide();
            })
            $('.card-activation-modal-confirm-btn').click(function(){
              $('.card-activation-confirm').hide();
              $('.card-activation-modal').hide();
          })
      }
      videoPlay.currentTime = e;
      $.ajax({
        type: "post",
        url: "/index/course/myFunction",
        data: {videoid:videoData.video_id,nowTime:videoData.audi_time,id:id,productStatus:productStatus},
        success: function (res) {
          console.log(res);
        }
      });
      return false;
    }
  }
}
}
//视频播放器暂停事件
function pausePlayer()
{
  // console.log(setTimer != null);
  // console.log(setTimer1 != null);
  // if(setTimer != null){
  //   b=2;
  // }
  // if(setTimer1 != null){
  //   b=2;
  // }
  // clearInterval(setTimer);
  // clearInterval(setTimer1);
  player.config.defaultViewConfig.showControls= true;
  timer = Math.floor(player.currentTime);
}
//视频播放完成触发事件
function handleReady(){
  var video_class_id=$('#video_class').val();
  var product_id=$('#product_id').val();
  var productStatus=$('#productStatus').val();
  var videoid=$('#video').val();
  var videoStartTime=$('#videoStartTime').val();
  var endtime = Date.parse(new Date());
  var study_time = endtime/1000 - timestamp/1000;
  study_time = Math.floor(study_time/60)+':'+(study_time%60);
  $.post('/index/course/updateCourseLog',{video_class_id:video_class_id,videoid:videoid,study_time:study_time,videoStartTime:videoStartTime,productStatus:productStatus,product_id:product_id})
}
    // var customComponents = "<div></div><div class='course-particulars-collect course-particulars-collect-all' title='收藏'><i class='iconfont icon-shoucang'></i><span>收藏</span></div>";
    // $(".qplayer-controlbtns").before(customComponents);
    // 全屏之后的样式
    var fullscreenStyle = '<div class="course-fullscreen-list">'+
    '<div class="course-fullscreen-list-btn"><i class="icon-arrow-right"></i><p>本节知识点</p></div>'+
    '<dl class="course-fullscreen-list-contain clearfix"></dl>'+
    '</div>';
    $(".qplayer-controlswrapper").append(fullscreenStyle)
    window.onresize = function() {
      fullscreenchange()
    }
    fullscreenchange()
    function fullscreenchange() {
      if (player.config.view.player.fullscreenController.isFullScreen == true) {
        // $(".qplayer-controls .course-particulars-note").removeClass("course-particulars-video-note")
        // $(".qplayer-controls .course-particulars-collect").removeClass("course-particulars-video-collection")
        $(".qplayer-controls .course-particulars-note").show()
        $(".qplayer-controls .course-particulars-collect").show()
        $(".course-fullscreen-list").show()
        $(".particulars-contain-note-fixed-fullscreen").show()
      } else if (player.config.view.player.fullscreenController.isFullScreen == false) {
        // $(".qplayer-controls .course-particulars-note").addClass("course-particulars-video-note")
        // $(".qplayer-controls .course-particulars-collect").addClass("course-particulars-video-collection")
        $(".qplayer-controls .course-particulars-note").hide()
        $(".qplayer-controls .course-particulars-collect").hide()
        $(".course-fullscreen-list").hide()
        $(".particulars-contain-note-fixed-fullscreen").hide()
      }
    }

  //  全屏之后本章知识点点击事件
  $(".course-particulars-play").on('click','.course-fullscreen-list-btn',function() {
    if ($(".course-fullscreen-list-btn i").hasClass("icon-arrow-right")) {
      $(".course-fullscreen-list-btn i").removeClass("icon-arrow-right")
                                        .addClass("icon-arrow-left")
      $(".course-fullscreen-list").animate({
        right: "0"
      },500)
      //请求接口加载知识点
      var video_id=$('#video').val();
      var a=$('.course-knowledge-points-current').index();
      $.post('/index/course/videoSkillKnow',{video_id:video_id},function(res){
          var skill=res.data;
          var fullscreenStyle = '<div class="course-fullscreen-list-btn"><i class="icon-arrow-left"></i><p> 本节知识点 </p></div><dl class="course-fullscreen-list-contain clearfix"><dt class="course-particulars-position-title">本节知识点</dt>';
          for(x in skill){
              var key = parseInt(x)+1;
              if(x==a){
                fullscreenStyle += '<dd class="course-particulars-position-lis course-particulars-current" onclick="startplay('+skill[x].start_time+','+skill[x].end_time+')">'+key+'.' +skill[x].k_name+'</dd>';
              }else{
                fullscreenStyle += '<dd class="course-particulars-position-lis" onclick="startplay('+skill[x].start_time+','+skill[x].end_time+')">'+key+'.'+skill[x].k_name+'</dd>';
              }
              
            }
            //播放器全屏的时候的知识点内容列表
            fullscreenStyle += '</dl>';
          $(".course-fullscreen-list").html(fullscreenStyle)
      })
    } else {
      $(".course-fullscreen-list-btn i").removeClass("icon-arrow-left")
                                        .addClass("icon-arrow-right")
      $(".course-fullscreen-list").animate({
        right: "-440px"
      },500)
    }

  })

  // 未 全屏时的知识点样式
  $(".course-particulars-bottom-lis").click(function() {
    $(this).addClass("course-particulars-bottom-current")
           .siblings().removeClass("course-particulars-bottom-current")
  })

   // 全屏时的知识点样式
   $(".course-particulars-position-lis").click(function() {
    $(this).addClass("course-particulars-current")
           .siblings().removeClass("course-particulars-current")
    
   })

  //  点击便签事件
  $(document).on('click','.course-particulars-note-all', function() {
    var video_class_id=$('#video_class').val();
    var product_id=$('#product_id').val();
    var productStatus=$('#productStatus').val();
    var videoid=$('#video').val();
    // 视频播放时的便签点击事件
    if (player.config.view.player.fullscreenController.isFullScreen == true) {
      $.ajax({
        type: "post",
        url: "/index/course/jsGetKnowLedge",
        data: {k_id:video_class_id,s_id:videoid,productStatus:productStatus,product_id:product_id},
        async:false,
        success: function (res) {
          var data = res.data;
          var html = '';
          html += '<div class="particulars-contain-note-fixed-fullscreen"><div class="particulars-contain-note-full" id="personal-contain-note">'+
                '<div class="particulars-note-close particulars-note-close-fullscreen">x</div> <div class="particulars-container-note"><h3 class="particulars-study-note">学习便签</h3><div class="particulars-study-scroll">'+
                '<dl class="particulars-note-list" id="htmlNote">';
            for (json in data) {
              html +='<dt class="particulars-note-lis">${data[json].k_name}</dt><dd class="particulars-note-dts">'+data[json].k_content+'</dd>';
            }
    
            html += '</dl></div></div></div></div>';
            $(".particulars-contain-note-fixed-fullscreen").remove()
            $(".qplayer-controlswrapper").append(html)
        }
      });
      var dragFull = $(".particulars-contain-note-full")[0]
      drag(dragFull);//便签拖拽事件
    } else if (player.config.view.player.fullscreenController.isFullScreen == false){
      var dragNote = $(".particulars-contain-note")[0]
      drag(dragNote);//便签拖拽事件
      $(".particulars-contain-note-fixed-outside").show().children(".particulars-contain-note").show().children(".particulars-note-close").show()
    }
   
  })
  // 全屏便签点击事件
  $(document).on('click','.particulars-note-close-fullscreen',function() {
    $(this).parents(".particulars-contain-note-fixed-fullscreen").hide()
  })
  // 未全屏便签点击事件
  $(".particulars-note-close").click(function() {
    $(this).parents(".particulars-contain-note-fixed-outside").hide()
  })
// 目录中便签点击事件
$(document).on('click',".particulars-container-lis-name .icon-bianqian-",function() {
  drag($(this).parent().next().children()[0]);//便签拖拽事件
  $(this).parent()
         .next()
         .show()
         .parents(".particulars-course-container-lis")
        //  .siblings()
        //  .find(".particulars-contain-note-fixed")
        //  .hide()
})
$(".particulars-note-close").click(function() {
  $(".particulars-note-close").parents(".particulars-contain-note-fixed").hide()
})

  $(".knowledge_content").click(function() {
    $(".knowledge_content").removeClass("knowledge_content_current")
    $(this).addClass("knowledge_content_current")
  })
  $(document).on('click',".particulars-container-lis-name",function() {
    $('#kid3'+videoId).find('a').removeClass('current-now');
    $('.particulars-container-lis-title').removeClass('current-now');
    // $('.particulars-container-position-contain').css('display','none');
    $('.particulars-container-position-contain').stop().slideUp(500);
    $('.particulars-position-container-lis').find('a').removeClass('current-now');

    if($(this).attr('data-isshow') == 1){ //显示知识点
    $('.particulars-container-position-contain').stop().slideUp(500)
    $(this).children(".particulars-container-lis-title").addClass("current-now")
         .parents(".particulars-container-lis-div")
         .siblings(".particulars-container-position-contain").stop(500)
         .slideDown(500)
         .end()
         .parent(".particulars-course-container-lis")
         .siblings()
         .find(".particulars-container-lis-title")
         .removeClass("current-now")
         .end();

    $(this).children(".particulars-container-lis-title").addClass("current-now")
         .parents(".particulars-course-container")
         .siblings()
         .find(".particulars-container-lis-title")
         .removeClass("current-now")
         .end();

    var listIndex = $(this).parents(".layui-colla-item").index();
    itemIndex = $(this).parents(".particulars-course-container-lis").index();
  } else {
    $('.particulars-container-position-contain').stop().slideUp()
    $(this).children(".particulars-container-lis-title").children("a").addClass("current-now")
           .parents(".particulars-course-container")
           .siblings()
           .children(".particulars-course-container-lis")
           .children(".particulars-container-lis-div")
           .children(".particulars-container-lis-name")
           .find(".particulars-container-lis-title")
           .children("a")
           .removeClass("current-now")
           .end()
           .parents(".layui-colla-item")
           .siblings()
           .children(".layui-colla-content")
           .find(".particulars-course-container-lis")
           .children(".particulars-container-lis-div")
           .children()
           .find(".particulars-container-lis-title")
           .children("a")
           .removeClass("current-now")
           .end();
        
    var listIndex = $(this).parents(".layui-colla-item").index();
    itemIndex = $(this).parents(".particulars-course-container-lis").index();
  }
  })
  // 指导课程购买弹窗关闭事件
  $(".course-buy-close").click(function(){
    $(".course-buy-modal").hide()
  });

  // 收藏课程输入框关闭事件
  $(document).on('click',".course-name-close",function() {
    $(".course-name-model").hide()
    $(".course-particulars-collect").html("<i class='iconfont icon-shoucang'></i><span>收藏</span>");
    $('body,html').css('position','static');
    // player.play();
  });

// 点击知识点跳转到指定视频播放时间点事件
  var videoPlay = document.getElementsByClassName("qplayer-video")[0].children[0];

  var cur = 0;
  var flag = 0;
  var func = function () {
    if (cur > 0 && videoPlay.currentTime >= cur) {
      cur = 0;
      videoPlay.pause();
      player.config.defaultViewConfig.showControls= true;
      videoPlay.removeEventListener("timeupdate", func);
    }
  };
  function startplay(starttime, endtime) {
    if (flag == 0) {
      flag = 1;
      videoPlay.autoplay = "autoplay";
      videoPlay.load();
    }
    videoPlay.currentTime = starttime;
    console.log(videoPlay.currentTime)
    cur = endtime;
    videoPlay.addEventListener("timeupdate", func);
    //点击知识点 清除计时器
    // clearInterval(setTimer);
    // clearInterval(setTimer1);
    // b = 2;
    videoPlay.play();
    player.config.defaultViewConfig.showControls= false;
  };

  // 新版右侧知识点点击样式改变事件
  $(document).on('click',".course-particulars-knowledge-points",function() {
    $(".course-particulars-knowledge-points").removeClass("course-knowledge-points-current")
    $(this).addClass("course-knowledge-points-current")
    // 全屏
    $(".course-particulars-position-lis").removeClass("course-particulars-current")
    $(".course-particulars-position-lis").eq($(this).index()).addClass("course-particulars-current")
    // 目录中的知识点
    $(".particulars-container-lis-title.current-now")
                          .parents(".particulars-container-lis-div")
                          .siblings(".particulars-container-position-contain")
                          .children()
                          .eq($(this).index()+1).children("a")
                          .addClass("current-now")
                          .parent()
                          .siblings()
                          .children()
                          .removeClass("current-now")
  })
// 全屏之后知识点点击事件
$(document).on('click','.course-particulars-position-lis',function() {
  $(".course-particulars-position-lis").removeClass("course-particulars-current")
  $(this).addClass("course-particulars-current")
  $(".course-particulars-knowledge-points").removeClass("course-knowledge-points-current")
  $(".course-particulars-knowledge-points").eq($(this).index()-1).addClass("course-knowledge-points-current")
  $(".particulars-container-lis-title.current-now")
                          .parents(".particulars-container-lis-div")
                          .siblings(".particulars-container-position-contain")
                          .children()
                          .eq($(this).index()).children("a")
                          .addClass("current-now")
                          .parent()
                          .siblings()
                          .children()
                          .removeClass("current-now")
})
layui.use(['element','layer', 'form','carousel','laypage'], function(){
  var layer = layui.layer
      ,element = layui.element
      ,form = layui.form
      ,carousel = layui.carousel
      ,laypage = layui.laypage;
      layui.form.render();
// 点击收藏录制视频事件
// $(document).on('click','.course-particulars-collect-all', function() {
//   // b = 1
//   var collectVal = $(".course-particulars-collect-all span").html();//点击的时候获取页面文本
//   // console.log(player.
//    timer = Math.floor(player.currentTime);
//   // timer = player.currentTime;
//   //  console.log(player.currentTime);
//   //  console.log(player.durationchange) //总时长
//   if (collectVal == '收藏') {//目前页面显示文本收藏的时候
//   //  点击收藏之前 此时页面值为 '收藏'
//     sessionStorage.setItem("startTime",timer);//点击收藏记录开时收藏的时间点
//     // setTimer=setInterval(function(){
//     //   timer++;
//     //   var timerNow = formatSeconds(timer);
//     //   $(".course-particulars-collect-all").html(timerNow)
//     // },1000);
//     setTimer=setInterval(function(){
//       timer = Math.floor(player.currentTime);
//       var timerNow = time_To_hhmmss(timer + 1);
//       $("#end-time span").html(timerNow)
//     },1000);
//     //  $(".course-particulars-collect-all").attr('title','停止');  //小浮框显示停止
//     $('.particulars_bottom_contain_right').css('display','block')

//     $(".course-particulars-collect-all").html('停止');
//      var start_Time = time_To_hhmmss(sessionStorage.getItem('startTime')); //获取开始时间
//      $('#start-time span').html(start_Time);
//     //  a = 1;
//      player.play()
//      player.config.defaultViewConfig.showControls= false;
//   }else{//页面文本显示的是时长的时候
//     $("#end-time span").html(time_To_hhmmss(timer))
//     // 此时页面值为时长 此时鼠标滑过标签title为停止
//     player.pause()
//     player.config.defaultViewConfig.showControls= true;
//     // clearInterval(setTimer);
//     // setTimer = undefined;
//     endTime = Math.floor(player.currentTime);
//     // endTime = player.currentTime;
//     sessionStorage.setItem("endTime",endTime);
//     sessionStorage.setItem("allTime",(endTime-sessionStorage.getItem("startTime")))
//     // var end_Time = time_To_hhmmss(sessionStorage.getItem('endTime')); //获取结束时间
//     // $('#end-time span').html(end_Time);
//     var all_Time = time_To_hhmmss(sessionStorage.getItem('allTime')); //获取总时长
//     $('.course_name_model_top_text span').html(all_Time)
//     $('.course-particulars-collect-all').html(all_Time)

//     // var courseReName = 
//     //     '<div class="course-name-model">'+
//     //     '<div class="course-name-change">'+
//     //       '<h4>收藏课程</h4>'+
//     //       '<div class="course_name_model_top">'+
//     //         '<span class="course_model_title">{$getCrumbs.learn}{$getCrumbs.grade}{$getCrumbs.subject}{$getCrumbs.Semester} : {$courseInfo.testclass}{$courseInfo.outline}</span>'+
//     //           '<img src="'+sxh_img.img+'" alt="" class="course_name_model_top_img">'+
//     //         '<span class="course_name_model_top_text">总时长:<span>20:00</span></span>'+
//     //       '</div>'+ 
//     //       '<form class="layui-form course-name-form" action="" onkeydown="if(event.keyCode==13){return false}">'+
//     //         '<div class="course-name-title">请输入收藏名称:</div>'+
//     //         '<div class="layui-form-item">'+
//     //           '<div class="layui-input-block">'+
//     //             '<input type="text" name="newName" placeholder="请输入标题" required lay-verify="required" autocomplete="off" class="layui-input">'+
//     //           '</div>'+
//     //         '</div>'+
//     //         '<div class="layui-form-item">'+
//     //           '<div class="layui-input-block">'+
//     //             '<button class="layui-btn" lay-submit type="button" lay-filter="courseRename">确定</button>'+
//     //           '</div>'+
//     //         '</div>'+
//     //       '</form>'+
//     //       '<div class="course-name-close">x</div>'+
//     //     '</div>'+
//     //   '</div>';



//     //   $(".qplayer-controlswrapper").append(courseReName);
//       $('body,html').css({'position':'fixed','top':'0','left':'0','bottom':'0','right':'0'});
//     $(".course-name-model").show()  
//   }
// });

  // 收藏重命名表单提交
  // form.on('submit(courseRename)', function (data) {
  //   $(".course-particulars-collect-all").html("<i class='iconfont icon-shoucang'></i><span>收藏</span>")
  //   $(".course-particulars-collect-all").attr('title','收藏')
  //   $(".course-name-model").hide()
  //   $('body,html').css('position','static')
  //   data.field.video_class = $("#video_class").val();
  //   data.field.video = $("#video").val()
  //   data.field.subject_id = $("#subject_id").val()
  //   data.field.startTime=sessionStorage.getItem("startTime");//收藏视频开始的时间
  //   data.field.endTime=sessionStorage.getItem("endTime");//收藏视频结束的时间
  //   data.field.countTime=sessionStorage.getItem("allTime");//收藏视频总时间
  //   $.ajax({
  //     url: '/index/course/courseCollections',
  //     type: 'post',
  //     data: data.field,
  //     async:false,
  //     success: function (res) {
  //       if(res.error_code==1){
  //         layer.msg(res.msg,{icon:1});
  //       }else{
  //         layer.msg(res.msg,{icon: 3})
  //       }
  //       timer = 0;
  //     },
  //     error: function (jqXHR, textStatus, errorThrown) {
  //       // console.log(errorThrown)
  //     }
  //   })
  //   return false;
  // });


    // 购买课程成功弹出提示事件
  form.on('submit(buySuccess)', function (data) {
    $(".course-buy-modal").hide()
    $(".course-success-modal").show()
    return false;
  })
  // city插件
  // var ylc = yeluochenCity('area', (city) => {console.log(city)
  
  // if(city.province!=null){
  //     $("#province").val(city.province);
  //     console.log(city.province,222);
  // }
  // if(city.city!=null){
  //     $("#city").val(city.city);
  //     console.log(city.city,333);
  // }
  // if(city.district!=null){
  //     $("#country").val(city.district);
  //     console.log(city.district,444);
  // }
  // },{
  //     province: $("#province").val(),
  //     city: $("#city").val(),
  //     district: $("#country").val(),
  // })

});

// 购买报名成功弹窗关闭事件
$(".course-success-close").click(function() {
  $(".course-success-modal").hide();
})
$(".course-success-btn").click(function() {
  $(".course-success-modal").hide()
  $(this).css({"background":"#0a3e82"})
})

//课程播放页面点击课程列表知识点事件
function courseListKnow(videoid,video_class_id,k_id,start_time,kl,productStatus,product_id)
{//请求接口判断本视频用户是否可以直接观看
  $('.course-particulars-video-catalog').show();
  $('.course_particulars_right_note').hide();
  $('.right_note_sumbit').hide();
  $('.course-particulars-video-contain').animate({'width':'1200px'},500);
  $('.course-particulars-video-contain').css('margin','0 auto');
  $('.course-particulars-video-play').css('width','76%');
  $('.particulars_bottom_contain_cont_left').css('width','76%');
  $('.particulars_bottom_contain_right').css('margin-top','2px');
  var video_id=$('#video').val();
  var videoStartTime=$('#videoStartTime').val();    //视频开始观看的时间
  if(video_id==videoid){
      videoPlay.currentTime =start_time;
      $(".course-particulars-knowledge-points").eq(kl-1).addClass("course-knowledge-points-current").siblings().removeClass("course-knowledge-points-current")

  }else{
      var endtime = Date.parse(new Date());
      var study_time = endtime/1000 - timestamp/1000;
      var nowTime=Math.ceil(player.currentTime);
      nowTime = Math.floor(nowTime/60)+':'+(nowTime%60);
      study_time = Math.floor(study_time/60)+':'+(study_time%60);
      $.post('/index/course/courseVerification',{videoid:videoid,video_class_id:video_class_id,k_id:k_id,nowTime:nowTime,video_id:video_id,study_time:study_time,videoStartTime:videoStartTime,productStatus:productStatus,product_id:product_id},function(res){
        var data=res.data;
        if(res.error_code==1){//本视频用户具有观看权限
          videoData = res.count;
          //计时器存在时 关闭计时器
          // if(timer != 0 ){
          //     clearInterval(setTimer);
          //     clearInterval(setTimer1);
          //     $(".course-particulars-collect-all").html("<i class='iconfont icon-shoucang'></i><span>收藏</span>");
          //     $(".course-particulars-collect-all").attr('title','收藏');
          //     $(".course-name-model").hide();
          //     timer = 0;       
          // }
          $('#video').val(videoid);
          $('#videoStartTime').val(videoid);
         // videoPlay.load();
          videoPlay.src=data.link;
          player.play();
          player.config.defaultViewConfig.showControls= false;
          // videoPlay.addEventListener('play', function(){
          //   videoPlay.currentTime =start_time;
          //   // var customComponents = "<div></div>"+
          //   //                     "<div class='course-particulars-collect course-particulars-collect-all' title='收藏'><i class='iconfont icon-shoucang'></i><span>收藏</span></div>";
          //   // $(".course-particulars-note").remove();
          //   // $(".course-particulars-collect").remove();
          //   // $(".qplayer-controlbtns").before(customComponents)
          // })
          $('.course-particulars-video-title').html(data.testclass+' '+data.outline);
          $('.course-particulars-knowledge-title').html('<h5>'+data.testclass+' '+data.outline+'</h5>')
          var skill=data.skill;
          var html='';
          var fullscreenStyle = '<div class="course-fullscreen-list"><div class="course-fullscreen-list-btn"><i class="icon-arrow-right"></i><p> 本节知识点 </p></div><dl class="course-fullscreen-list-contain clearfix"><dt class="course-particulars-position-title">本节知识点</dt>';
          var htmlNote='';
          for(x in skill){
            var a = parseInt(x)+1;
            if(a==kl){
              html+='<dd class="course-particulars-knowledge-points course-knowledge-points-current" title="'+skill[x].k_name+'" data-id='+a+' onclick="startplay('+skill[x].start_time+')">'+a+'. '+skill[x].k_name+'</dd>';
              fullscreenStyle += '<dd class="course-particulars-position-lis course-particulars-current" onclick="startplay('+skill[x].start_time+')">'+a+'. '+skill[x].k_name+'</dd>';
            }else{
              html+='<dd class="course-particulars-knowledge-points" title="'+skill[x].k_name+'" data-id='+a+' onclick="startplay('+skill[x].start_time+')">'+a+'. '+skill[x].k_name+'</dd>';
              fullscreenStyle += '<dd class="course-particulars-position-lis" onclick="startplay('+skill[x].start_time+')">'+a+'. '+skill[x].k_name+'</dd>';
            }
            htmlNote+='<dt class="particulars-note-lis">'+skill[x].k_name+'</dt><dd class="particulars-note-dts">'+skill[x].k_content+'</dd>';
          }
          $('#skill').html(html);
          $('#htmlNote').html(htmlNote);
          //播放器全屏的时候的知识点内容列表
          fullscreenStyle += '</dl></div>';
          $(".qplayer-controlswrapper").html(fullscreenStyle)
        }else if(res.error_code==0){//本视频用户不具有观看权限
          // layer.msg(res.msg,{icon:0,time:2000});
          // alert(res.msg)
          console.log(res.msg);
          $('.free_audition').html(res.msg)
          $('.number_showmodel').show();
          $('.showmodel_btn').hide();
          $('.confirm_study').hide();
          $('.number_showmodel_iconfont').removeClass('iconfont icon-xuexi');
            $('.number_showmodel_iconfont').addClass('showmodel_bg');
          $('.number_showmodel_close').click(function(){
            $('.number_showmodel').hide();
          })
          $('.i_about_btn').click(function(){
            $('.number_showmodel').hide();
          })
          $('.confirm_study_btn').click(function(){
            $('.number_showmodel').hide();
            if(res.data){
              window.location.href = res.data;
            }
          })
          // $(".course-buy-modal").show()   弹出提示购买弹窗部分
          // $(this).addClass("current-now")
          //       .parents(".particulars-container-lis-div")
          //       .siblings(".particulars-container-position-contain")
          //       .hide()
        }else if(res.error_code==2){    //试听权限
          //试听 弹框
          $('.free_audition').html(res.msg)
          $('.number_showmodel').show();
          $('.showmodel_btn').show();
          $('.confirm_study').show();
          $('.number_showmodel_iconfont').removeClass('showmodel_bg');
            $('.number_showmodel_iconfont').addClass('iconfont icon-xuexi');
          $('.number_showmodel_close').click(function(){
            $('.number_showmodel').hide();
          })
          $('.i_about_btn').click(function(){
            $('.number_showmodel').hide();
          })
          $('.confirm_study_btn').click(function(){
            $('.number_showmodel').hide();
          })

          videoData = res.count;
          //计时器存在时 关闭计时器
          // if(timer != 0 ){
          //     clearInterval(setTimer);
          //     clearInterval(setTimer1);
          //     $(".course-particulars-collect-all").html("<i class='iconfont icon-shoucang'></i><span>收藏</span>");
          //     $(".course-particulars-collect-all").attr('title','收藏');
          //     $(".course-name-model").hide();
          //     timer = 0;       
          // }

          $('#video').val(videoid);
         // videoPlay.load();
          videoPlay.src=data.link;
          player.play();
          player.config.defaultViewConfig.showControls= false;
          videoPlay.addEventListener('play', function(){
            videoPlay.currentTime =start_time;
            // var customComponents = "<div></div>"+
            //                     "<div class='course-particulars-collect course-particulars-collect-all' title='收藏'><i class='iconfont icon-shoucang'></i><span>收藏</span></div>";
            // $(".course-particulars-note").remove();
            // $(".course-particulars-collect").remove();
            // $(".qplayer-controlbtns").before(customComponents)
          })
          $('.course-particulars-video-title').html(data.testclass+' '+data.outline);
          $('.course-particulars-knowledge-title').html('<h5>'+data.testclass+' '+data.outline+'</h5>')
          var skill=data.skill;
          var html='';
          var fullscreenStyle = '<div class="course-fullscreen-list"><div class="course-fullscreen-list-btn"><i class="icon-arrow-right"></i><p> 本节知识点 </p></div><dl class="course-fullscreen-list-contain clearfix"><dt class="course-particulars-position-title">本节知识点</dt>';
          var htmlNote='';
          for(x in skill){
            var a = parseInt(x)+1;
            if(a==kl){
              html+='<dd class="course-particulars-knowledge-points course-knowledge-points-current" title="'+skill[x].k_name+'" data-id='+a+' onclick="startplay('+skill[x].start_time+')">'+a+'. '+skill[x].k_name+'</dd>';
              fullscreenStyle += '<dd class="course-particulars-position-lis course-particulars-current" onclick="startplay('+skill[x].start_time+')">'+a+'. '+skill[x].k_name+'</dd>';
            }else{
              html+='<dd class="course-particulars-knowledge-points" title="'+skill[x].k_name+'" data-id='+a+' onclick="startplay('+skill[x].start_time+')">'+a+'. '+skill[x].k_name+'</dd>';
              fullscreenStyle += '<dd class="course-particulars-position-lis" onclick="startplay('+skill[x].start_time+')">'+a+'. '+skill[x].k_name+'</dd>';
            }
            htmlNote+='<dt class="particulars-note-lis">'+skill[x].k_name+'</dt><dd class="particulars-note-dts">'+skill[x].k_content+'</dd>';
          }
          $('#skill').html(html);
          $('#htmlNote').html(htmlNote);
          //播放器全屏的时候的知识点内容列表
          fullscreenStyle += '</dl></div>';
          $(".qplayer-controlswrapper").html(fullscreenStyle)
        }
        
      })
  }
      

}
// $(document).on('click','.particulars-position-container-lis',function() {
  
// })
// $(window).unload(function(){
//     //响应事件
//     alert("获取到了页面要关闭的事件了！"); 
// }); 
//传入秒数转化成时分秒格式数据
function formatSeconds(value) {
    var theTime = parseInt(value);// 秒
    var theTime1 = 0;// 分
    var theTime2 = 0;// 小时
    if(theTime > 60) {
      theTime1 = parseInt(theTime/60);
      theTime = parseInt(theTime%60);
      if(theTime1 > 60) {
        theTime2 = parseInt(theTime1/60);
        theTime1 = parseInt(theTime1%60);
      }
    }
    var result = ""+parseInt(theTime)+"秒";
    if(theTime1 > 0) {
      result = ""+parseInt(theTime1)+"分"+result;
    }
    if(theTime2 > 0) {
      result = ""+parseInt(theTime2)+"小时"+result;
    }
    return result;
}
//时间格式准环卫00:00
function time_To_hhmmss(seconds){  
  // var hh;  
  var mm;  
  var ss = parseInt(seconds);  
  if(seconds==null || seconds<=0){  
      return "00:00";  
  }  
  // hh = seconds/3600|0;  
  // seconds = parseInt(seconds)-hh*3600;  
  // if(parseInt(hh)<10){  
  //     hh = "0"+hh;  
  // }  

mm = seconds/60|0;  
  
ss = parseInt(seconds)-mm*60;  
if(parseInt(mm)<10){  
 mm = "0"+mm;  
}  
if(ss<10){  
  ss = "0"+ss;  
}  
return mm+":"+ss;  
}

$(document).on('click','.particulars-position-container-lis a',function() {
  $(this).addClass("current-now").parent().siblings().find('a').removeClass('current-now');
  $('#kid1'+k_id).parent().find('a').removeClass('current-now');
  $('#videoid_start'+videoId).parent().find('a').removeClass('current-now');
})

//点击左滑
$('.course-particulars-note-all').click(function(){
  $('.course_particulars_right_note').show();
  $('.course-particulars-video-catalog').hide();
  $('.course_particulars_right_note').animate({'width':'25%'},500);
  $('.course-particulars-video-contain').animate({'margin-left':'0px','width':'73.2%'},500);
  $('.course-particulars-video-play').css('width','100%');
  $('.particulars_bottom_contain_cont_left').css('width','76.22%');
  $('.particulars_bottom_contain_right').css('margin-top','2px');
  $('.right_note_sumbit').show();
  $('.particulars_bottom_contain_left').css('width','66%');
  // getNoteList();
  //  笔记初始赋值 
  // console.log('11');
  settextareaval();
})

//点击右边滑块小时
$('.right_note_title_clone').click(function(){
  // $('.course_particulars_right_note').animate({'width':'0'},500);
  $('.course-particulars-video-catalog').show();
  $('.course_particulars_right_note').hide();
  $('.right_note_sumbit').hide();
  $('.course-particulars-video-contain').animate({'width':'1200px'},500);
  $('.course-particulars-video-contain').css('margin','0 auto');
  $('.course-particulars-video-play').css('width','76%');
  $('.particulars_bottom_contain_cont_left').css('width','76%');
  $('.particulars_bottom_contain_right').css('margin-top','2px');
  // window.location.reload()
})

//限制文本框字数
// $(document).ready(function(){
//     $('.textarea_all_content').keyup(
//         function(){
//               var _length = $(this).val().length;
//               $('.textarea_number').html(_length)
//         }
//     );
// });
var textarea;
function addNote()
{
  var text = $('.textarea_all_content').val();
  if(text== '')
  {
    layer.msg('请输入内容！',{icon:3});
    return false;
  }
  // var subject_id=$('#subject_id').val();
  // var video_id=$('#video').val();
  // var video_class_id=$('#video_class').val();
  // var startTime=Math.ceil(player.currentTime);
  // $.post('/index/course/addNote',{text:text,subject_id:subject_id,video_id:video_id,video_class_id:video_class_id,startTime:startTime},function(res){
  //     if(res.code == 1001){
  //         layer.msg(res.msg, {icon:5,time:1200});
  //         return false;
  //     }
  //     layer.msg(res.msg,{icon:6,time:1200});
  // })
  var res = textarea;

  if(res.code == 1001){
      layer.msg(res.msg, {icon:5,time:1200});
      return false;
  }
  layer.msg(res.msg,{icon:6,time:1200});
  
}
//  文本域初始赋值
function settextareaval(){
    $('#textArea').text('');
    var video_id = $('#video').val();
    var kid = $('#video_class').val();
    var productStatus = $('#productStatus').val();

    $.ajax({
        url: '/index/course/settextareaval'
        ,data: {vid:video_id,kid:kid,productStatus:productStatus}
        ,dataType: 'json'
        ,type: 'post'
        ,success: function(e){
            if(e.code == 0){
              $('.textarea_all_content').val(e.data.test);
              $('#textArea').focus();
            }
        }
    })
}

//  文本失去焦点
$('#textArea').blur(function(){

    var text = $(this).val();
    if(text== '')
    {
      // layer.msg('请输入内容！',{icon:3});
      return false;
    }
    var subject_id=$('#subject_id').val();
    var video_id=$('#video').val();
    var video_class_id=$('#video_class').val();
    var product_id=$('#product_id').val();
    var productStatus=$('#productStatus').val();
    var startTime=Math.ceil(player.currentTime);
    $.ajax({
        url: '/index/course/addNote'
        ,dataType: 'json'
        ,type: 'post'
        ,data: {text:text,subject_id:subject_id,video_id:video_id,video_class_id:video_class_id,startTime:startTime,productStatus:productStatus,product_id:product_id}
        // ,async: false
        ,success: function(res){
          //  失去焦点时 不提示
          // if(res.code == 1001){
          //     layer.msg(res.msg, {icon:5,time:1200});
          //     return false;
          // }
          // layer.msg(res.msg,{icon:6,time:1200});
          textarea = res;
        }
    })
})

// function getNoteList()
// {
//     var subject_id=$('#subject_id').val();
//     var video_id=$('#video').val();
//     var video_class_id=$('#video_class').val();
//     var person_id=$('#person_id').val();
//     $.post('/index/course/getNoteList',{subject_id:subject_id,video_id:video_id,video_class_id:video_class_id,person_id:person_id},function(res){
//         var data=res.msg;
//         var html='';
//         if(res.error_code==1){
//           html +="<div class='right_my_note'>我的笔记记录</div>";
//           for(x in data)
//           {
//               html +="<div class='right_note_user'><div class='note_user_top'><div class='user_top_left'><img src='"+data[x].litpic+"' alt='' class='user_top_left_phone'><span class='user_top_left_name'>"+data[x].nickname+"</span></div><div class='user_top_right'>"+data[x].intime+"</div></div><div class='note_user_content'><p>"+data[x].noteText+"</p></div></div>";
//           }
//         }else{
//           html +="<div class='right_my_note'>我的笔记记录</div>";
//           html +="<div class='right_note_user right_no_note'>暂无数据</div>";
//         }
//         $('#noteList').html(html);
//     })
// }

$('.particulars-position-container-lis').click(function(){
  $('.particulars_bottom_contain_right').hide();
})

$('.particulars-container-lis-title').click(function(){
  $('.particulars_bottom_contain_right').hide();
})


//视频播放页对应的知识点
function video_points(){
// var URL = document.URL;
// var    url_ary = URL.split('&');
// var    url_url = url_ary[1];
// var    videoid = url_url.split('=')[1];
  if(videoId ){
    $('#kid3'+videoId).children('a').addClass('current-now');
  }
  var thisUrl = document.URL;
  // var      ary = thisUrl.split('&');
  // var      k_id_url = ary[3];
  // var      video_url = ary[1];
  // var      k_id_id = k_id_url.split('=')[1];
  // var      video_id = video_url.split('=')[1];
  if(k_id){
    $('#kid2'+k_id).addClass('course-knowledge-points-current')
                   .siblings()
                   .removeClass('course-knowledge-points-current');
    $('#kid1'+k_id).parents('.layui-colla-content').addClass('layui-show');
    $('#kid1'+k_id).parent().stop().slideDown();
    $('#kid1'+k_id).children('a').addClass('current-now')
    $('#kid1'+k_id).parent()
                   .siblings('.particulars-container-lis-div')
                   .find('.particulars-container-lis-title')
                   .addClass('current-now');
    $('#kid1'+k_id).parents('.layui-colla-item')
                   .siblings('.layui-colla-item')
                   .find('.layui-colla-content')
                   .removeClass('layui-show');
  }
  if( k_id == 0 ){
    // 开学第一课没有知识点
      $('#videoid_start'+videoId).parents('.layui-colla-content').addClass('layui-show');
      $('#videoid_start'+videoId).parent().stop().slideDown();
      $('#videoid_start'+videoId).children('a').addClass('current-now');
      $('#videoid_start'+videoId).parent()
                   .siblings('.particulars-container-lis-div')
                   .find('.particulars-container-lis-title')
                   .addClass('current-now');
      $('#videoid_start'+videoId).parents('.layui-colla-item')
                                 .siblings('.layui-colla-item')
                                 .find('.layui-colla-content')
                                 .removeClass('layui-show');
      // 课程目录中没有知识点
      $('#kid3'+videoId).parents('.layui-colla-content').addClass('layui-show');
      $('#kid3'+videoId).parent().stop().slideDown();
      $('#kid3'+videoId).children('a').addClass('current-now');
      $('#kid3'+videoId).parent()
                  .siblings('.particulars-container-lis-div')
                  .find('.particulars-container-lis-title')
                  .addClass('current-now');
      $('#kid3'+videoId).parents('.layui-colla-item')
                                .siblings('.layui-colla-item')
                                .find('.layui-colla-content')
                                .removeClass('layui-show');
  }
}