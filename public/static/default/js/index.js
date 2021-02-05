
//选择版本后页面刷新出现的数据
function indexTextVideo()
{
  // console.log(sessionStorage.getItem("textStatus"));
  $.post('/index/index/indexTextVideo',{id:sessionStorage.getItem("textStatus")},function(data){
      var res=data.data;
      var html = "";
      for(x in res){
              html+="<li class='layui-col-md3 layui-recommond clearfix'>";
              html+="<a href='javascript:;' onclick='personPlayVideo("+res[x].id+")'>";
              html+="<div class='recommend-classroom-video'><img src='"+res[x].img+"' alt=''><div class='video-moel'><i class='iconfont icon-bofang'></i></div><div class='course-time'>"+res[x].min+"分"+res[x].sec+"秒</div></div>";
              html+="<div class='recommend-classroom-bottom clearfix'>";
              html+="<p class='recommend-classroom-title'>"+res[x].name+"</p>";
              html+="<div class='recommend-classroom-msg'>";
              html+="<div class='recommend-classroom-subject index-subject-"+res[x].cssid+"'>"+res[x].subject+"</div>";
              html+="<span class='recommend-classroom-learn'>"+res[x].popularity+"人已学习</span>";
              html+="</div>";
              html+="</div>";
              html+="</a>";
              html+="</li>";
      }
       $('#tbody').html(html);
  })
}
//首页课程进度
function rateOfLearning()
{
  $.post('index/index/rateOfLearning',function(res){
    var data=res.data;
    var html = "";
    for(x in data){
      html+='<div class="layui-col-md5 learn-progress-blocks">'+
          '<div class="learn-progress-img">'+
            '<img src="'+data[x].img+'" alt="">'+
          '</div>'+
          '<div class="learn-progress-center">'+
            '<h5 class="learn-progress-center-h5" title="'+data[x].learn+data[x].grade+data[x].subject+'（'+data[x].semester+'）">'+data[x].learn+data[x].grade+data[x].subject+'（'+data[x].semester+'）</h5>'+
            '<div class="layui-progress">'+
              '<div class="layui-progress-bar" lay-percent="'+data[x].rateOfLearning+'"></div>'+
            '</div>'+
            '<p class="learn-center-produce">课程共'+data[x].countClass+'节 已学习'+data[x].countHistory+'节课</p>'+
          '</div>'+
          '<div class="learn-progress-right"><a href="'+/index/person/myClass+'">继续学习</a></div>'+
        '</div>';
    }
      $('#rateOfLearning').html(html);
  })
}
window.onload = function() {
  edition_clicked = 0; //版本弹窗是否点击
  judge_session();
  // rateOfLearning();//学习进度
  if(sessionStorage.getItem("key")==1)
  {
    //选择版本后页面刷新出现的数据
    indexTextVideo();
  }

// layui框架
 layui.config({
   version: '1531663423583' //为了更新 js 缓存，可忽略
 });
 layui.use(['element','form','layer'], function(){
   var element = layui.element
       ,form = layui.form
       ,layer = layui.layer;

//名师课堂更多
// $('.learn-progress-more').on('click',function (){
//   $.ajax({
//     type:"post",
//     url:"/index/Teachers/teachersTeam",
//     success:function(res) {
//     },   
//     }) 
//   });
   
  //  访问页面显示选择版本弹框
  var commonData = {value:""}
  $(".select-idition").click(function() { 
    commonValue=$(this).data("value");
    commonData.value = commonValue;
    sessionStorage.setItem("textStatus",commonData.value);
    $(this).addClass("select-this").siblings().removeClass("select-this")
  })
  //版本选择框 点击确定请求本版本数据
  $(".select-idition-confirm").click(function() {
    if ( commonData.value ) {
      edition_clicked = 1;
      judge_session()
      $(this).css({background:"#DF4A43"})
             .parent(".common-select-idition").hide()
             .siblings(".common-modal").hide()
      // 请求不同版本的接口
      indexTextVideo();
    } else {
      layer.msg("请选择版本",{icon: 0,time: 1000});
      return;
    }
  })

 });
// 课程进度
$(".learn-progress-right").click(function() {
  $(this).addClass("learn-right-click")
         .children("a").css({"color":"#fff"})
         .parent()
         .parent().siblings().children("div.learn-progress-right")
         .removeClass("learn-right-click")
         .children("a").css({"color":"#666"})
  return
})
function judge_session() {
  var judge_session = sessionStorage.getItem('key');
  // 第一次进入并且未选择版本
  if ( judge_session == null) {
    $(".common-modal").show()
    $(".common-select-idition").show()
    if ( edition_clicked == 1 ) { 
      sessionStorage.setItem('key', '1') 
    }
  } else {
    // 已选择版本
    $(".common-modal").hide()
    $(".common-select-idition").hide()
  }
}
// 推荐课程列表样式
$(".layui-recommond-classroom").on('mouseenter', '.layui-recommond a .recommend-classroom-video',function() {
  $(this).siblings(".recommend-classroom-bottom")
        .children(".recommend-classroom-title")
        .addClass("recommend-title-new")
  return;
})
$(".layui-recommond-classroom").on('mouseleave','.layui-recommond a .recommend-classroom-video',function() {
  $(this).siblings(".recommend-classroom-bottom").children(".recommend-classroom-title")
         .removeClass("recommend-title-new")
})

//详情底部鼠标滑过样式
$(".layui-recommond-classroom").on('mouseenter','.layui-recommond',function() {
  $(this).children().find(".course-time")
         .addClass("dn").parent().siblings().children()
         .find(".recommend-classroom-title").css({color:"#DF4A43"})
  return;
})
$(".layui-recommond-classroom").on('mouseleave','.layui-recommond',function() {
  $(this).children().find(".course-time")
         .removeClass("dn").parent().siblings().children()
         .find(".recommend-classroom-title").css({color:"rgba(51,51,51,1)"})
  return;
})
}

