
// layui框架
 layui.config({
   version: '1531663423583' //为了更新 js 缓存，可忽略
 });
 layui.use(['element','form','layer'], function(){
   var element = layui.element
       ,form = layui.form
       ,layer = layui.layer;
       $ = layui.jquery;

       $.ajaxSetup({
            beforeSend: function(request) {
              if(sessionStorage.getItem('_info') != null){
                  request.setRequestHeader("content-info",sessionStorage.getItem('_info'));
              }
            }
       })
  isLogin();
  // 用户进入页面判断是否登录
flag = 0; //是否登录
function isLogin() {
  $.ajax({
      url:"/index/Login/judgeLogin",
      dataType:'json',
      success:function(data){
        if(data.msg!=1){
          
          //判断是否第一次登陆
          if( data.count == 1 ){
            $('.loginone_showmodel').show();
            $('.loginone_img').show();
            setTimeout(function(){
              $('.loginone_img').addClass('animated bounceOut');
              $('.loginone_showmodel').addClass('animated fadeOut');
            },5000)
            setTimeout(function(){
              $('.loginone_img').hide();
              $('.loginone_showmodel').hide();
            },6000)
            // data.count == 0;
          }
          if(data.data.counts != 0){
            $('.after-login-message-news').children('span').text(data.data.counts).show();
          } else if(data.data.counts >= 99) {
            $('.after-login-message-news').children('span').text("…").show()
          }

          
          flag = 1;
          sessionStorage.setItem("flag",1)

            // if(data.msg==3){
            //   $(".listen-free-video-style").css({display:"none"})
            //   $(".index-learning-rade-all").css({display:"block"})
            //   $(".index-learning-rade-contain").css({display:"block"})
            //   $(".index-listen-free").css({display:"none"})
            // }
            $(".index-after-login").addClass("active")
            $(".index-before-login").removeClass("active")
            
            $(".common-header-contain .layui-nav").children().eq(4).html("<a href='/index/person/person'>个人中心</a>")
            // .addClass("layui-this").siblings().removeClass("layui-this");
            navChangeStyle() //导航样式
        } else {
          sessionStorage.removeItem('userId');
          clearCookie();
          // 没登录时 点击个人中心按钮 出现提示登录的弹框
          $(".common-header-contain .layui-nav").children().eq(4).click(function() {
            $(".login-register-modal").show()
            $(".register-login-contain").show()
            $(".register-login-button").eq(1).addClass("current")
                                      .siblings().removeClass("current")
            $(".register-login-content-lis").eq(1).addClass("active")
                                            .siblings().removeClass("active")
          })
        }
      }
  })
}
  // var guade_id = sessionStorage.getItem('glodalGuard');
  // if(guade_id != ''){
  //   var t_html = $('a[data-id='+guade_id+']').html();
  //   $('.getglodalid').parent().removeClass('layui-this');
  //   $('a[data-id='+guade_id+']').parent().addClass('layui-this');
  //   $('a[data-id='+guade_id+']').parent().parent().prev().html(t_html+"<span class='layui-nav-more'></span>");
  // }

  

  //单点登录
  var user_id = sessionStorage.getItem('userId');
  // var ws = new WebSocket("ws://39.106.90.110:2346");
  // ws.onopen = function() {
  //   // console.log("连接成功"); 
  //       if(user_id != null){
  //           ws.send(user_id);
  //           console.log('消息发送成功！');
  //       }
  //       console.log('连接socket，当前userid：'+user_id);
  // };
  // ws.onmessage = function(evt){
  //   console.log(evt);
  //     var res = evt.data;
  //     if(JSON.parse(res).code == 1001){
        
  //       layer.msg(JSON.parse(res).msg,{icon:6,time:2000},function(){
  //          // ws.close();
  //           sessionStorage.removeItem('userId');
  //           sessionStorage.removeItem('flag');
  //           window.location.href = '/index/login/quitLanding';
  //           $(".common-after-login").removeClass("active")
  //           $(".common-before-login").addClass("active")
  //           //$(".exit-login").click();
  //         });
  //     }
  // }
  //导航栏中全局搜索
  $(".index-nav-search").focus(function() {
    $(this).val("")
  })
  $(".index-nav-search").blur(function() {
    var gloalSearch = $(this).val();
    if(gloalSearch != ''){
        window.location.href="/index/index/glodalsearch?gloalSearch="+gloalSearch;
    }
  })
  // 全局搜索请求接口
  form.on('submit(searchMsg)', function (data) {
      window.location.href="/index/index/glodalsearch?gloalSearch="+data.field.gloalSearch;
      return false;
  })
  //全局glodal_id
  // $('.getglodalid').click(function(){
  //   var grade_id = $(this).attr('data-id');
  //   var _this = $(this);
  //   var t_html = _this.html();
  //   $.ajax({
  //       url:'/index/login/setGlodalClassId'
  //       ,data: {grade_id:grade_id}
  //       ,dataType: 'json'
  //       ,type: 'post'
  //       ,success:function(res){
  //           _this.parent().parent().prev().html(t_html+"<span class='layui-nav-more'></span>");
  //           sessionStorage.setItem('glodalGuard',grade_id);
  //           window.location.reload();
  //       }
  //   })
  // })

  // 表单验证
  form.verify({
    username: function (value, item) { //value：表单的值、item：表单的DOM对象
      if (!new RegExp("^[\u4e00-\u9fa5\\s·]+$").test(value)) {
        return '请填写2~4位汉字';
      }
      if (/(^\_)|(\__)|(\_+$)/.test(value)) {
        return '用户名首尾不能出现下划线\'_\'';
      }
      if (/^\d+\d+\d$/.test(value)) {
        return '用户名不能全为数字';
      }
    },
    phone: [/^(((13[0-9]{1})|(14[0-9]{1})|(17[0-9]{1})|(15[0-3]{1})|(15[5-9]{1})|(18[0-9]{1}))+\d{8})$/, "请输入正确的手机号格式"],
    email: [/^[a-z\d]+(\.[a-z\d]+)*@([\da-z](-[\da-z])?)+(\.{1,2}[a-z]+)+$/, "请输入正确的邮箱格式"],
    password:[/^[0-9a-z]{6,15}$/,'密码必须6到12位，且不能出现空格']
  });
  
  //注册监听提交
  form.on('submit(register)', function(data){
    $.ajax({
      url: '/index/login/registerPerson',
      type: 'post',
      data: {
        phone: data.field.registerPhone,
        code: data.field.registerCode,
        password:data.field.registerPwd,
        type:1,
      },
      success: function (res) {
            if(res.error_code == 1){
              layer.msg(res.msg,{icon:0,time: 1000})
          }else if(res.error_code == 3){
            layer.msg(res.msg,{icon:6,time:2000},function(){
              location=location;
            })
          }else if(res.error_code == 4){
            layer.msg(res.msg,{icon:5,time:2000})
          }else if(res.error_code == 5){
            layer.msg(res.msg,{icon:0,time:2000})
          }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        
      }
    })
    return false;
  });
   
  //密码登录监听提交
  form.on('submit(pwdLogin)', function (data) {
    $.ajax({
      url: "/index/Login/pwdLogin",
      type: 'post',
      data: {
        phone: data.field.loginPhone,
        password: data.field.loginPwd
      },
      success: function (res) {
          // 登陆成功跳转到首页
        if(res.error_code==1){
          
          sessionStorage.setItem("flag",1)
          // console.log(res.count);
          sessionStorage.setItem('userId',res.count);
          console.log(res.count);
          // ws.send(res.count);
          //登陆成功跳转个人中心页面
          layer.msg(res.msg,{icon:6,time:2000},function(){
            window.location.href = res.data;
          })
        } else {
          layer.msg(res.msg,{icon:5,time:2000},function(){
          })
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR.responseText)
      }
    })
    return false;
  })
  //验证码登录监听提交
  form.on('submit(codeLogin)', function (data) {
    $.ajax({
      url: "/index/Login/VerifyCodeLogin",
      type: 'post',
      data: {
        phone: data.field.loginCodePhone,
        code: data.field.loginCode
      },
      success: function (res) {
        if(res.error_code==1) {
          sessionStorage.setItem("flag",1)
          sessionStorage.setItem('userId',res.count);
          ws.send(res.count);
          layer.msg('登录成功',{icon:6,time:2000},function(){
            window.location.href = res.data;
          })
          // 登陆成功跳转到首页
        }else{
          // 登陆失败
          layer.msg(res.msg,{icon:5,time:2000});
          return;
        } 
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR.responseText)
      }
    })
    return false;
  })
  //注册忘记密码 找回密码
  form.on('submit(forget)', function(data){
    $.ajax({
      url: '/index/login/forgetPassword',
      type: 'post',
      data: {
        phone: data.field.forgetrPhone,
        code: data.field.forgetCode,
        password:data.field.forgetPwd
      },
      success: function (res) {
        if(res.error_code==1){
          layer.msg(res.msg,{icon: 6,time: 1000},function () {
            location=location;
          });
        } else{
          layer.msg(res.msg,{icon: 5,time: 1000});
        }
        
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.log(errorThrown)
      }
    })
    return false;
  });

  // 发送手机验证码
var data = {freePhone:''}
$("#free-phone, #register-phone, #login-phone, #register-phone-num, #forget-phone").blur(function() {
  var freePhone = $(this).val()
  data.freePhone = freePhone
  // console.log(data.freePhone);
})


 // 获取 验证码
$('.get-phonecode').on('click', function () {
  var id = $(this).data("id")
  // bannar=0,注册=1 验证码登录=2 忘记密码=3
  $.ajax({
    type:"post",
    url:"/index/login/VerifyCode",
    data:{
      phone: data.freePhone
    },
    success:function(res) {
      if(res.error_code==1) {
        var time=60;
        $('.get-phonecode'+id).val("已发送("+time+"s)")
          var interval=setInterval(function () {
              if (time >=1 && time <= 60 ) {
                time--;
                $('.get-phonecode'+id).val("已发送("+time+"s)").attr('disabled', true)
              } else {
                $('.get-phonecode'+id).val("重新获取").attr('disabled', false)
                clearInterval(interval);
              }
          }, 1000);
        }else{
          layer.msg(res.msg,{time: 1000});
        }
      },  
    }) 
  });
 //注册 获取 验证码
$('.get-phonecode-reg').on('click', function () {
  var id = $(this).data("id")
  // bannar=0,注册=1 验证码登录=2 忘记密码=3
  $.ajax({
    type:"post",
    url:"/index/login/RegVerifyCode",
    data:{
      phone: data.freePhone
    },
    success:function(res) {
      if(res.error_code==1) {
        var time=60;
        $('.get-phonecode-reg'+id).val("已发送("+time+"s)")
          var interval=setInterval(function () {
              if (time >=1 && time <= 60 ) {
                time--;
                $('.get-phonecode-reg'+id).val("已发送("+time+"s)").attr('disabled', true)
              } else {
                $('.get-phonecode-reg'+id).val("重新获取").attr('disabled', false)
                clearInterval(interval);
              }
          }, 1000);
        }else{
          layer.msg(res.msg,{time: 1000});
        }
      },
      // error:function(res) {
      //   // console.log("error");
      //   console.log(1111);
      // }    
    }) 
  });


 });



 


// 操作页面样式部分
//  登录注册弹窗样式
$(".register-login-button").click(function() {
    $(".register-login-button").removeClass("current")
    $(this).addClass("current")
    $(".register-login-content-lis").removeClass("active")
    $(".register-login-content-lis").eq($(this).index()).addClass("active")
  })
  // 使用验证码登陆
  $(".login-code-methods").click(function() {
    $(this).parent().parent().parent(".login-pwd")
           .removeClass("active")
           .siblings().addClass("active")
  })
  // 使用密码登陆
  $(".login-pwd-methods").click(function() {
    $(this).parent().parent().parent(".login-code")
          .removeClass("active")
          .siblings().addClass("active")
  })
  // 忘记密码
  $(".login-methods").click(function() {
    $(".register-login-contain").hide()
    $(".forget-password").show()
  })
  // 关闭登录注册弹窗
  $(".login-register-modal").click(function() {
    $(this).hide()
    $(".register-login-contain").hide()
    $(".forget-password").hide()
    $(".buy-modal").hide()
    navChangeStyle()
    return
  })
  // 导航栏登录注册切换
  $(".header-login-btn").click(function() {
    $(".login-register-modal").show()
    $(".register-login-contain").show()
    $(".register-login-button").eq(1).addClass("current")
                               .siblings().removeClass("current")
    $(".register-login-content-lis").eq(1).addClass("active")
                                    .siblings().removeClass("active")
  })
  $(".header-register-btn").click(function() {
    $(".login-register-modal").show()
    $(".register-login-contain").show()
    $(".register-login-button").eq(0).addClass("current")
                               .siblings().removeClass("current")
    $(".register-login-content-lis").eq(0).addClass("active")
                                    .siblings().removeClass("active")
  })
//   $(".login-register-modal").click(function() {
//     $(this).hide()
//     $(".register-login-contain").hide()
//   })
  // 退出登录
$(".after-login-position").mouseenter(function() {
  // $(".exit-login").fadeIn()
})
$(".exit-login").click(function() {
  sessionStorage.removeItem('userId');
  sessionStorage.removeItem('flag');
  window.location.href = '/index/login/quitLanding';
  $(".common-after-login").removeClass("active")
  $(".common-before-login").addClass("active")
})
// $(".layui-nav-logout").mouseleave(function() {
//   $(this).fadeOut()
// })


function clearCookie(){
  var keys=document.cookie.match(/[^ =;]+(?=\=)/g);
  if (keys) {
  for (var i = keys.length; i--;)
    document.cookie=keys[i]+'=0;expires=' + new Date( 0).toUTCString()
  }
}
// 导航栏部分样式改变
// 导航栏部分样式改变
navChangeStyle()
function navChangeStyle() {
  var url = window.location.href;
  var urlLink = url.split('://')[1].substr(0,13); //截取结果为www.ydtkt.com
  var urlLink1 = url.split('://')[1].split('/')[1]; //截取结果为' '
  var urlLink2 = url.split('://')[1].split('/')[3]; //截取结果为'index.html '
  if (url.indexOf("course") >= 0 || url.indexOf("glodalsearch") >= 0 || url.indexOf('getClassList') >= 0) {
    $(".common-header-list").children("li").eq(1).addClass("layui-this").siblings().removeClass("layui-this")
    $(".select-subject").hide()
  } else if (url.indexOf("teachers") >= 0 ){
    $(".common-header-list").children().eq(3).addClass("layui-this").siblings().removeClass("layui-this")
    $(".select-subject").hide()
  }
  // else if (url.indexOf("service") >= 0 ){
  //   $(".common-header-list").children().eq(4).addClass("layui-this").siblings().removeClass("layui-this")
  //   $(".select-subject").hide()
  // } 
   else if (url.indexOf("person") >= 0 ){
    $(".common-header-list").children().eq(4).addClass("layui-this").siblings().removeClass("layui-this")
  } else if (urlLink1 == "" || urlLink2 == "index.html") {
    $(".common-header-list").children("li").eq(0).addClass("layui-this").siblings().removeClass("layui-this")
    $(".select-subject").hide()
  }
}

// 右侧悬浮栏样式
$(".positions-list").on('mouseenter','.list-lis .wechat-erweima', function() {
  $(this).attr("src","/static/default/images/wechat-two.png")
  $(".wechat-img").fadeIn()
})
$(".positions-list").on('mouseleave','.list-lis .wechat-erweima', function() {
  $(this).attr("src","/static/default/images/wechat-one.png")
  $(".wechat-img").fadeOut()
})
$(".positions-list").on('mouseenter','.list-lis .wechat-consult', function() {
  $(this).attr("src","/static/default/images/consult-two.png")
})
$(".positions-list").on('mouseleave','.list-lis .wechat-consult', function() {
  $(this).attr("src","/static/default/images/consult-one.png")
})
//返回顶部
$(".positions-list").on('mouseenter','.list-lis .wechat-top', function() {
  $(this).attr("src","/static/default/images/go-top-two.png")
})
$(".positions-list").on('mouseleave','.list-lis .wechat-top', function() {
  $(this).attr("src","/static/default/images/go-top-one.png")
})
$(".positions-list").on('click','.list-lis .wechat-top',function(){
  $(this).attr("src","/static/default/images/go-top-two.png")
  $("body,html").css({"display":"block"})
                .animate({
                  scrollTop:0
                },500,function(){
                  $(".go-top").hide()
                })
})
$(window).scroll(function(){
  var htmlTop = $(document).scrollTop();
  if( htmlTop > 0){
      $(".go-top").fadeIn();    
  }else{
      $(".go-top").fadeOut();
  }
});


//用户点击视频进行播放
/*function personPlayVideo(id)
{
    if(sessionStorage.getItem("flag")==1){
      //用户登陆了判断用户有没有购买过本课程，或者用户有没有领取过免费试听的权限，权限是否还有效
      $.post('/index/index/personPlayVideo',{id:id},function(res){
          if(res.error_code==1){
              //用户有权限直接观看视频
              window.location.href='/index/course/courseParticulars?id='+id;
          }else{
              //用户没有权限直接观看视频跳转到视频播放页
              window.location.href='/index/course/preLoginCourse?id='+id;
          }
      })
    }else{
      //用户没有登陆跳转到课程详情页面
      window.location.href='/index/course/preLoginCourse?id='+id;
    }
}

// 商桥部分
$(function() {
  //点击按钮时判断 百度商桥代码中的"我要咨询"按钮的元素是否存在，存在的话就执行一次点击事件  
  $("#online").click(function(event) {
    if($('#nb_icon_wrap').length > 0) {
      $('#nb_icon_wrap').click();
    }
  });
});
$(function() {
        //点击按钮时判断 百度商桥代码中的"我要咨询"按钮的元素是否存在，存在的话就执行一次点击事件  
        $("#online1").click(function(event) {
          if($('#nb_icon_wrap').length > 0) {
            $('#nb_icon_wrap').click();
          }
        });
      });
$(function() {
        //点击按钮时判断 百度商桥代码中的"我要咨询"按钮的元素是否存在，存在的话就执行一次点击事件  
        $("#getPreLoginCourseInfo").on('click','#zixun',function(event) {
          if($('#nb_icon_wrap').length > 0) {
            $('#nb_icon_wrap').click();
          }
        });
      });*/

       //个性title提示<i></i>样式
 $(function() {
  $("a").each(function(b) {//这里是控制标签
      if (this.title) {
          var c = this.title; //把title的赋给自定义属性 myTilte ，屏蔽自带提示
          var a = 30; //设置提示框相对于偏移位置，防止遮挡鼠标
          $(this).mouseover(function(d) { //鼠标移上事件
              this.title = "";
              $("body").append('&lt;div id="<i></i>tooltip"&gt;' + c + "&lt;/div&gt;"); //创建提示框,添加到页面中
              $("#tooltip").css({
                  left: (d.pageX + a) + "px",
                  top: d.pageY + "px",
                  opacity: "0.8"
              }).show(250) //设置提示框的坐标，并显示
          }).mouseout(function() { //鼠标移出事件
              this.title = c; //重新设置title
              $("#tooltip").remove() //移除弹出框
          }).mousemove(function(d) { //跟随鼠标移动事件
              $("#tooltip").css({
                  left: (d.pageX + a) + "px",
                  top: d.pageY + "px"
              })
          })
      }
  })
});

//  未登录时   登录页面显示
function loginpage(){
  
  var flag = sessionStorage.getItem("flag");
    if(flag != 1){
      // $(".login-register-modal").show()
      //       $(".register-login-contain").show()
      //       $(".register-login-button").eq(1).addClass("current")
      //                                 .siblings().removeClass("current")
      //       $(".register-login-content-lis").eq(1).addClass("active")
      //                                       .siblings().removeClass("active")
      $(".login-register-modal").show()
      $(".register-login-contain").show()
      $(".register-login-button").eq(1).addClass("current")
                                 .siblings().removeClass("current")
      $(".register-login-content-lis").eq(1).addClass("active")
                                      .siblings().removeClass("active")
      return false;
    }else{

      return true
    }
}

$(document).on('keydown', function() {
  if(event.keyCode == 13) {
    //密码登录
    if($('.login-pwd').hasClass('active') && $('.login-pwd').parent('li').hasClass('active') && $('.forget-password').css('display') == 'none'){
      $("#common-login").click();
    }
    //验证码登录
    if($('.login-code').hasClass('active') && $('.login-pwd').parent('li').hasClass('active') && $('.forget-password').css('display') == 'none'){
      $("#common-login-code").click();
    }
    //注册
    if($('.register-form').parent('li').hasClass('active')){
      $("#common-register").click();
    }
    //忘记密码
    if($('.forget-password').css('display') == 'block'){
      $("#common-login-forget").click();
    }
  }
});