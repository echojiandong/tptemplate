layui.use(['element','layer', 'form','laypage','laydate'], function(){
    var layer = layui.layer
        ,element = layui.element
        ,form = layui.form
        ,laypage = layui.laypage
        ,laydate = layui.laydate
        ,glode_grade = 7;
        // 生日日期选择
        var date = new Date();
        localDate = date.toLocaleDateString().replace(/\//g,"-");
        laydate.render({
          elem: '#birthday' //指定元素      
          ,min: '1995-1-1'
          ,max:localDate
        });
  $(document).on('click','.particulars-container-lis-name',function(){
    // $(".particulars-container-lis-title").removeClass("personal-learn-now")
    if($(this).attr('data-isshow') == 1){

    $('.particulars-container-position-contain').stop().slideUp();
    if(!($(this).parent().hasClass("personal-not-buy-style"))) {
      $(this).children(".particulars-container-lis-title").addClass("personal-learn-now")
         .parents(".particulars-container-lis-div")
        //  .addClass("particulars-click-now")
         .siblings(".particulars-container-position-contain").stop()
         .slideToggle()
        //  .siblings(".particulars-container-select-contain")
        //  .toggle()
         .end()
         .parent(".personal-course-container-lis")
         .siblings()
         .find(".particulars-container-lis-div")
         .removeClass("particulars-click-now")
         .find(".particulars-container-lis-title")
         .removeClass("personal-learn-now")
    } else {
      $(this).parents(".particulars-container-lis-div")
      .siblings(".particulars-container-position-contain").stop()
      .slideToggle()
      .end()
      .parent(".personal-course-container-lis")
      .siblings()
      .find(".particulars-container-lis-div")
      .removeClass("particulars-click-now")
      .find(".particulars-container-lis-title")
      .removeClass("personal-learn-now")
    }  
  }
  })
  //点击蒙版关闭蒙版
  $('.loginone_showmodel').click(function(){
    $('.loginone_img').addClass('animated bounceOut');
    $('.loginone_showmodel').addClass('animated fadeOut');
    setTimeout(function(){
      $('.loginone_img').hide();
      $('.loginone_showmodel').hide();
    },1000)
  })

  // 课程目录收藏按钮
  // $(document).on('click',".personal-course-container-lis .particulars-container-lis-name .icon-shoucang",function() {
  //   $(this).css({color:"#0a3e82"})
  //         .parents(".particulars-container-lis-div")
  //         .siblings(".particulars-container-position-contain")
  //         .toggle()
  //         .siblings(".particulars-container-select-contain")
  //         .toggle()
  // })

  $('.particulars-container-lis-title').eq(0).click();
    // var ylc = yeluochenCity('area', function(city) {
  
    //   if(city.province!=null){
    //       $("#province").val(city.province);
    //       // console.log(city.province,222);
    //   }
    //   if(city.city!=null){
    //       $("#city").val(city.city);
    //       // console.log(city.city,333);
    //   }
    //   if(city.district!=null){
    //       $("#country").val(city.district);
    //       // console.log(city.district,444);
    //   }
    // },{
    //     province: $("#province").val(),
    //     city: $("#city").val(),
    //     district: $("#country").val(),
    // })
    //个人信息提交
    // 日期选择控制时间区间
    form.on('submit(formDemoPersonInfo)',function(data){

        $.post('/index/person/updatePerson',data.field,function(data){
            if(data.error_code==1){
              layer.msg(data.msg,{icon: 1,time: 2000})
              // location.reload(true)
            }else{
              layer.msg(data.msg,{icon: 2,time: 2000})
            }
        });
        return false;
    })
    //注册卡激活
    form.on('submit(cardSubmit)', function(data){
      
      var zz = /^[0-9]{8}\w{9}[0-9]{6}$/;
      if(!(zz.test(data.field.title))){
        layer.msg('卡号格式有误，请重新输入！',{icon:2,time:2000});
        return false;
      }
      $.post("/index/person/activationCode",data.field,function(data){
          var icon_code = data.code == 1?1:2;
          if (data.code == 1) {          
            layer.msg(data.msg,{icon: 6,time: 2000},function () {});
            var showTime = setInterval(function() {
              $(".persoanl-card-activation-first").show()
            },1000);
            clearInterval(showTime);
          } else {
            layer.msg(data.msg,{icon: icon_code,time: 2000},function () {
            });
          }
          return;
      })
    })

// 课程目录便签部分
$(document).on('click','.personal-course-container-lis .particulars-container-lis-name .icon-bianqian-',function(){
  drag($(this).parent().next().children()[0]);//便签拖拽事件
   $(this).css({color:"#0a3e82"}).parent(".particulars-container-lis-name")
          .next()
          .show()
          .children(".particulars-contain-note")
          .show()
          .children(".particulars-note-close")
          .show()
          .end()
          .parent(".personal-course-container-lis")
          .siblings()
          .children()
          .find(".particulars-contain-note-fixed")
          .hide()
          .children()
          .find(".particulars-contain-note")
          .hide()
})
$(document).on('click',".particulars-note-close",function() {
  $(this).hide()
          .parent(".particulars-contain-note")
          .hide()
          .parent(".particulars-contain-note-fixed")
          .hide()
          .end()
})

  // 便签滚动条
$(window).on("load",function(){
  // $(".iscroll-wrapper").mCustomScrollbar();
  drag("personal-contain-note")
});

  // $("#evaluation-click").load("person/evaluation.html")
  // // 个人中心左侧按钮选项
  $(".personal-tab-left-lis").click(function() {
    // alert(123)
    $(".personal-tab-left-lis").removeClass("personal-left-current").children("i").removeClass("icon-current")
    $(this).addClass("personal-left-current").children("i").addClass("icon-current")
    $(".personal-tab-right-lis").removeClass("active")
    $(".personal-tab-right-lis").eq($(this).index()).addClass("active")
    var a=$(this).attr('data-val');
    if(a==2){
      //我的收藏
      if($(".personal-collect-top-edit").html() == '完成'){
        $(".personal-collect-top-edit").click();
      }
      //科目列表
      myCollectionSubjectList();
      var subject=0;
      var pagenow=1;
      myCollection(subject,pagenow);
    }else if(a==4){       //个人信息
      personInfo();
    }else if(a==5){       //消息中心
      console.log(555)
      var status=2;      //默认显示全部消息
      var pagenow=1;
      $('.getList-person-message').removeClass('personal-message-no-read');
      $('.getList-person-message').eq(0).addClass('personal-message-no-read');
      message(pagenow,status);
    }else if(a == 6){      
      // cardactivation();//激活课程
      //科目信息
      var subject_id = 1;
      var semester = 1;
      var pagenow = 1;
      testPaperList(subject_id,semester,pagenow);   //评测信息
      $("#paperList").show();
      $('#evaluation-particular').hide(); 
    }

    if(a == 3){
        centerindex_2(0)
    }

  })
  //测试列表
  function testPaperList(subject_id,semester,pagenow){
    $.post('/index/person/getTestPaperList',{gid:glode_grade,subject_id:subject_id,semester:semester,pagenow:pagenow},function(res){
      if(res.error_code==1){
        var data=res.data;
        var html = '';
        for(x in data){
          html+='<li class="evaluation-infomation-lis" data-val="'+data[x].id+'">'+
              '<div class="evaluation-infomation-title">'+
                ''+data[x].title+''+
             '</div>'+
              '<div class="evaluation-infomation-right">'+
                '<span>'+data[x].create_at+'</span>'+
                '<span class="evaluation-infomation-status">未完成</span>'+
              '</div>'+
            '</li>';
        }
        $('#getTestPaperList').html(html);
        $('#myTestPaperPage').html(res.msg);
        $(".personal-collect-contain-none").hide();
        $("#paperList").show();
        $("#getTestPaperList").show();
        $('#myTestPaperPage').show();   //分页显示
      }else{
        $('#myTestPaperPage').hide();
        $(".personal-collect-contain-none").show();
        $("#getTestPaperList").hide();
      }
    })
  }
   //测评信息分页
  window.TestPaperPageBody = function(pagenow,subject_id,semester,subjectId){
    testPaperList(subjectId,semester,pagenow);
    // if($(".personal-collect-top-edit").html() == '完成'){
    //     $(".personal-collect-select-lis-last").show()
    //     $(".personal-collect-contain-delete").show()
    // }
  }

  function myCollectionSubjectList()
  {
    $.post('/index/person/getsubjectlist',{gid:glode_grade},function(res){
      if(res.code==1){
        var data=res.data;
        var html='<li class="personal-collect-select-lis personal-collect-select-lis-current" data-val="0">全部</li>';
        for(x in data){
          html+='<li class="personal-collect-select-lis" data-val="'+data[x].id+'">'+data[x].subject+'</li>';
        }
        $('#myCollectionSubjectList').html(html);
      }
    })
  }
  //我的收藏部分
  function myCollection(subject,pagenow)
  {
    //同步
    $.ajaxSettings.async = false;
    $.post('/index/person/collect',{subject:subject,pagenow:pagenow,gid:glode_grade},function(res){
        if(res.error_code==1){
          var data=res.data;
          var html='';
          for(x in data){
            html+='<li class="personal-collect-contain-lis" data-id="'+data[x].id+'">'+
                '<span class="personal_model_title">'+data[x].outline+'</span>'+
                '<div class="personal-collect-contain-img ">';
            if(data[x].collectImg != null){
              html+= '<img src="'+data[x].collectImg+'" alt="">';
            }else{
              html+= '<img src="'+data[x].img+'" alt="">';
            }
            html+= '<a href="/index/course/courseParticulars?id='+data[x].video_class_id+'&videoid='+data[x].video_id+'&startTime='+data[x].startTime+'">'+
                      '<div class="personal-collect-contain-model "><i class="iconfont icon-video"></i></div>'+
                    '</a>'+
                    '<div class="personal-collect-contain-timer">'+data[x].minute_count+'分'+data[x].second_count+'秒</div>'+
                    '<div class="personal-collect-contain-delete" onclick="delMyCollection('+data[x].id+','+subject+')">x</div>'+
                  '</div>'+
                  '<div class="personal-collect-contain-bottom clearfix">'+
                    '<span class="personal-collect-contain-title" title="'+data[x].ctitle+'" data-id="'+data[x].id+'">'+data[x].ctitle+'</span>'+
                    '<span class="personal-collect-contain-subject video-subject-chinese">'+data[x].subject+'</span>'+
                  '</div>'+
                  '<div class="personal-collect-contain-changename">'+
                    '<input type="text" class="personal-collect-contain-input" value="'+data[x].ctitle+'">'+
                    '<span class="personal-collect-contain-subject video-subject-chinese">'+data[x].subject+'</span>'+
                  '</div>'+
                '</li>';
          }
          $('#myCollection').html(html);
          $('#myCollectionPage').html(res.msg);
          $(".personal-collect-contain-none").hide()
          $(".personal-collect-has-select").show()
        }else{
          // $('#myCollection').html(res.msg)
          $(".personal-collect-contain-none").show()
          $(".personal-collect-has-select").hide()
        }
    })
    // $.ajaxSettings.async = true;
  }
  // 我的收藏重新命名得焦失焦事件
  $(document).on('focus',".personal-collect-contain-input",function() {
    $(this).parent().css({border:"1px solid #FFDDAD"})
  });
  $(document).on('blur',".personal-collect-contain-input",function() {
    var spanVal = $(this).parent().siblings(".personal-collect-contain-bottom").children(".personal-collect-contain-title")
        ,inputVal = $(this).val();
        if(spanVal.text() === inputVal || inputVal == ""){
          return false
        }
        videoId = $(this).parents(".personal-collect-contain-lis").attr("data-id")
        spanVal.text(inputVal);
        $.ajax({
          url:"/index/person/updCollectName",
          data:{name:inputVal,id:videoId},
          sync:false,
          success:function(e) {
            if(e.code == 1001){
              layer.msg(e.msg,{icon:6,time:1500});
            }
          }
        })
        // console.log()
        // if(spanVal.text() === inputVal){

        //   return false
        // }
        
    // $.ajax({
    //   url:"/index/person/updCollectName",
    //   data:{name:inputVal,id:videoId},
    //   sync:false,
    //   success:function(e) {
    //     if(e.code == 1001){
    //       layer.msg(e.msg,{icon:6,time:1500});
    //     } else {
    //       spanVal.text(inputVal);
    //     }
    //   }
    // })
  })
  //点击科目筛选我的收藏
  $('.personal-collect-select-list').on('click','.personal-collect-select-lis',function(){
    $(this).addClass("personal-collect-select-lis-current")
           .siblings()
           .removeClass("personal-collect-select-lis-current")
    var subject=$(this).attr('data-val');
    var pagenow=1;
    myCollection(subject,pagenow);
    if($(".personal-collect-top-edit").html() == '完成'){
        $(".personal-collect-select-lis-last").show()
        $(".personal-collect-contain-delete").show()
    }
  })
  //我的收藏分页
  window.collectPageBody = function(pagenow,subject){
    myCollection(subject,pagenow);
    if($(".personal-collect-top-edit").html() == '完成'){
        $(".personal-collect-select-lis-last").show()
        $(".personal-collect-contain-delete").show()
    }
  }
  //删除我的收藏
  var del_id,_sid;
  window.delMyCollection = function(id,subject)
  {
    $.ajaxSettings.async = false;
    $('.card-activation-modal').show();
    $('.messsage-delete-modal').show();
    del_id = id;
    _sid = subject;
    
  }
  // 确认删除我的收藏
  $('.messsage-delete-modal-contain-btn').click(function(){
    $.post('/index/person/delMyCollection',{id:del_id},function(res){
        if(res.error_code==1){
          layer.msg(res.msg,{icon: 1,time: 1000},function(){
            var pagenow=1;
            myCollection(_sid,pagenow);
            $(".personal-collect-select-lis-last").show()
            $(".personal-collect-contain-delete").show()
            $('.card-activation-modal').hide();
            $('.messsage-delete-modal').hide();
          })
        }else{
          layer.msg(res.msg,{icon: 2,time: 2000})
        }
    })
    $.ajaxSettings.async = true;
  })
  $(".messsage-delete-modal-confirm-close").click(function() {
    $('.card-activation-modal').hide();
    $('.messsage-delete-modal').hide();
  })
  //请求接口查询用户个人信息
  function personInfo()
  {
    $.post('/index/person/infor',function(data){
      $('.personal-info-prefect-percent').html(data.msg);
      $('.infor-sucess').css('width',data.msg);
      if(data.msg == '100%'){
        $('.info-smile').html('<img  src="/static/default/images/icon-smile.png" alt="" />');
      }
    })
  }
  //消息中心未读已读切换
  $('.getList-person-message').click(function(){
      $(this).addClass('personal-message-no-read').siblings().removeClass('personal-message-no-read');
      var status=$(this).attr('data-val');
      var pagenow=1;
      message(pagenow,status);
  })
  //消息中心
  function message(pagenow,status)
  {
    // if ( status == 1 ) {
    //   status = '';
    // }
    $.post('/index/person/message',{pagenow:pagenow,status:status},function(res){
      //console.log(res)
      if(res.error_code==1){
        // 已读
        var data=res.data;
        var html='';
        var className = 'personal-message-msg-noread';
        for(x in data){
          html+='<li class="personal-message-lis" data-val="'+data[x].id+'">';
          if(data[x].status == 0) {
            html+='<p class="personal-message-lis-msg '+className+'" title="'+data[x].title+'">'+data[x].title+'</p>';
          } else {
            html+='<p class="personal-message-lis-msg " title="'+data[x].title+'">'+data[x].title+'</p>';
          }    
            html+='<span class="personal-message-lis-time" title="'+data[x].create_time+'">'+data[x].create_time+'</span>'+
                '</li>';
        }
        $('#message').html(html);
        $('#messagePage').html(res.msg)
        $(".personal-message-contain-none").hide()
        $("#message").show()
      }else if(res.error_code==0){
        // 暂时没有数据
        var data=res.data;
        var html='';
        var className = 'personal-message-msg-noread';
        for(x in data){
          html+='<li class="personal-message-lis" data-val="'+data[x].id+'">'+
                  '<p class="personal-message-lis-msg '+className+'" title="'+data[x].title+'">'+data[x].title+'</p>'+
                  '<span class="personal-message-lis-time" title="'+data[x].create_time+'">'+data[x].create_time+'</span>'+
                '</li>';
        }
        $('#message').html(html);
        // $('#message').html(res.msg);
        $(".personal-message-contain-none").show()
        $(".personal-message-list").hide()
      }
      
    })
  }
  //消息中心分页
  window.messagePageBody = function(pagenow,status){
    message(pagenow,status);
  }
  //点击查看消息详情
  $(".personal-message-list").on('click','.personal-message-lis',function() {
    $(".personal-message-center").hide()
    $(".personal-message-particulars").show()
    var id=$(this).attr('data-val');
    $.post('/index/person/msg_info',{id:id},function(res){
      if(res.error_code==1){
        var data=res.msg;
        var html='';
        html='<div class="personal-message-center-top clearfix person_message_select">'+
            '<i class="iconfont icon-fanhui-copy-copy"></i>'+
            '<h5>消息中心</h5>'+
            '<span class="personal-message-center-read" onclick="delMssage('+data.id+')">删除</span></div>'+
            '<div class="personal-message-particulars-contain"><h3>'+data.title+'</h3>'+
            '<span>'+data.create_time+'</span>'+
            data.desc+'</div>';
        $('#messageInfo').html(html);
      }else{
        $('#messageInfo').html(res.msg);
      }  
    })
  })
  //消息详情中删除消息
  window.delMssage = function(id)
  {
    $.post('/index/person/delMssage',{id:id},function(res){
      if(res.error_code==1){
        layer.msg(res.msg,{icon: 1,time: 2000},function () {
           $('.personal-tab-left-lis').eq(3).click();
        });
      }else{
        layer.msg(res.msg,{icon: 2,time: 2000},function () {
           //window.location.reload();//刷新页面
        });
      }
    })
  }

  //删除已读消息
  function del_msg()
  {
    $.post('/index/person/del_msg',function(res){
      if(res.error_code==1){
        layer.msg(res.msg,{icon: 1,time: 2000},function () {
           window.location.reload();//删除成功刷新页面
        });
      }else{
        layer.msg(res.msg,{icon: 2,time: 2000},function () {
           //window.location.reload();//刷新页面
        });
      }
    })
  }





  // 我的课程tab选项部分
  // $(document).on('click',".person-center-course-subject",function() {
  //   var dataVal = $(this).attr("data-val"),_this = $(this),classname = 'personal-subject-not-buy';
  //   var class_ = $(".person-center-course-subject");
  //   $.each(class_,function(i,v){
  //       if(class_.eq(i).attr('data-val') == 0){
  //         class_.eq(i).addClass('personal-subject-default');

  //         if(class_.eq(i).hasClass('personal-subject-not-buy')){
  //            class_.eq(i).removeClass('personal-subject-not-buy');
  //         }
  //       }
  //       class_.eq(i).removeClass('personal-course-subject-active');
  //   })
  //   if(dataVal == 1){
  //     classname = 'personal-course-subject-active';
  //   }
  //   $(this).removeClass("personal-subject-default")
  //          .addClass(classname)
  //   // $(".personal-course-contain-lis").removeClass("active")
  //   // $(".personal-course-contain-lis").eq($(this).index()).addClass("active")
  //   //科目id
  //   var subject_id = $(this).attr('data-id'),active_class = 'personal-volume-not-buy-style';

  //   if(!$('.personal-center-volume').hasClass('personal-volume-not-buy-style')){
  //     active_class = 'personal-volume-buy-active';
  //   }
  //   var seme_id = $('.'+active_class).attr('data-val');
  //   var obj_html = $('.personal-course-contain-lis').eq(0);
  //   //  index值（$(this).index()）
  //   seme_id = 1;
  //   var gid = glode_grade;
  //   $('.course_select').children('option').attr('data-v',subject_id);

  //   subject_list(subject_id,seme_id, obj_html,gid);
  //   //$(".personal-course-container").eq(0).children("dd.personal-course-container-lis").eq(0).children("ul.particulars-container-position-contain").show()
  //   element.init('collapse')
  //   obj_html.find('.particulars-container-lis-title').eq(0).click();
  // })
  
  // 上下册点击样式
  // $(".personal-center-course-tab").on('click','.personal-center-volume', function() {
  //   //点击上下册进行切换。获取当前值是上册还是下册，获取科目年级参数请求接口
  //   var subject_id =  $(this).attr('data-subject')
  //       ,seme_id = $(this).attr('data-value')
  //       ,obj_html = $('.personal-course-contain-lis').eq(0)
  //       // index 值  （$('li[data-id="'+subject_id+'"]').index()）
  //       ,gid = glode_grade;
  //   subject_list(subject_id,seme_id, obj_html, gid);
  //   element.init('collapse')
  //   obj_html.find('.particulars-container-lis-title').eq(0).click();
  // })

  // 年级选择
  form.on('select(grade)', function(e){
      glode_grade = e.value;

      var _index = $('.personal-tab-left-lis').index(),indexval;

      for(var i = 0; i < _index; i++){
          if($('.personal-tab-left-lis').eq(i).hasClass('personal-left-current')){
              indexval = i;
          }
      }
      //   我的课程
      centerindex_0(glode_grade)
      //   我的收藏
      centerindex_1(glode_grade);

       //测评
      //默认值
      var subject_id = 1;
      var semester = 1;
      var pagenow = 1;
      testPaperList(subject_id,semester,pagenow);   //评测信息
      //   学习笔记
      // centerindex_2(e)
      // switch(indexval){
      //     case 0: 
      //       // 课程列表
      //         centerindex_0(e)
      //     break;
      //     case 1: 
      //       //  我的收藏
      //         centerindex_1(e);
      //         centerindex_0(e)
      //     break;
      //     case 2: 
      //       //   我的笔记
      //         centerindex_2(e)
      //     break;
      //     default: break;
      // }
  })
  // 新版年级选择
  // $(document).on("click",".personal-center-top-grade",function() {
  //   glode_grade = $(this).attr("data-value");
  //   var _index = $('.personal-tab-left-lis').index(),indexval;

  //   for(var i = 0; i < _index; i++){
  //       if($('.personal-tab-left-lis').eq(i).hasClass('personal-left-current')){
  //           indexval = i;
  //       }
  //   }
  //   $(".personal-center-top-grade").removeClass("personal-center-top-grade-click")
  //   $(this).addClass("personal-center-top-grade-click")
  //     //   我的课程
  //     centerindex_0(glode_grade)
  //     //   我的收藏
  //     centerindex_1(glode_grade);
  //     //测评
  //     //默认值
  //     subject_id = 1;
  //     semester = 1;
  //     testPaperList(subject_id,semester);   //评测信息

  // })
  //  年级选择  个人中心 我的笔记变化
  function centerindex_2(e){
      // 年级、上下册渲染
      $.ajax({
          url: '/index/person/noteSubject'
          ,dataType: 'json'
          ,type: 'post'
          ,data: {gid:glode_grade}
          ,async: false
          ,success: function(res){
              if(res.data == ''){
                  // $('.course_note').html('这是文字代替的图片');
                  $('.personal-message-contain-none').show();
                  $('.course_pages_number').hide();
                  $('.course_note').hide()
                  return false;
              }
              //   科目列表
              var ul_html = '<ul class="course_note_menu clearfix">',active_cname = '';
              for(x in res.data.slist){
                  if(x == 0){
                      active_cname = 'course_note_menu_li_active';
                  }
                  ul_html += '<li class="course_note_menu_li '+active_cname+'" data-subjectid="'+res.data.slist[x].id+'">'+
                              '<span class="">'+res.data.slist[x].subject+'</span>'+
                              '</li>';
                  active_cname = ''
              }
              ul_html += '</ul>';

              var volumn_html = '<div class="course_note_volume clearfix">',v_name = '上册';

              for(x in res.data.volumn){
                  if(x == 0){
                      active_cname = 'course_note_volume_active';
                  }
                  if(res.data.volumn[x] == 2){

                      v_name = '下册';
                  }
                  if(res.data.volumn[x] == 3){

                      v_name = '全册';
                  }
                  volumn_html += '<div class="course_note_volume_text '+active_cname+'" data-volumn="'+res.data.volumn[x]+'">'+v_name+'</div>';
                  active_cname = '';
                  v_name = '上册';
              }
              volumn_html += '</div>'
              $('.course_note').show().empty().append(ul_html).append(volumn_html);
              $('.personal-message-contain-none').hide();
              courseNote();
          }
      }) 

  }

  window.courseNote = function (page){
    var page = page || 1;
      var sid = $('.course_note_menu_li_active').attr('data-subjectid')
          ,vid = $('.course_note_volume_active').attr('data-volumn');

          $.ajax({
              url: '/index/person/mynoteList'
              ,dataType: 'json'
              ,data: {sid:sid,vid:vid,gid:glode_grade,page:page}
              ,type: 'post'
              ,success: function(e){
                  $('.course_note_content').remove();
                  $('#mynotePage_div').remove();
                  if(e.data == '' || e.code == 1001){

                      var null_html = '<div class="course_note_content"><div class="personal-message-contain-none">'+
                                      '<div class="personal-collect-none-img"><img src="/static/default/images/message-nothing.png" alt=""></div>'+
                                      '<p class="personal-collect-none-introduce"><a href="javascript:;">您还没有提交笔记哦!</a></p>'+
                                      '</div></div>';
                        $('.course_note').append(null_html);
                        $('.course_note_content').children('.personal-message-contain-none').show();
                      return false;
                  }
                  
                  var notelist = '<div class="course_note_content">';
                  for(x in e.data.list){
                      notelist += '<div class="note_content_title">'+
                                  '<span href="javascript:;" class="content_title_name">'+e.data.list[x].videoname+'</span>'+
                                  '</div>'+
                                  '<div class="note_content_text">'+e.data.list[x].content+'</div>'+
                                  '<div class="note_content_bottom clearfix">'+
                                  '<div class="note_content_bottom_left">'+
                                  '<i class="iconfont icon-bofang11"></i>'+
                                  '<a href="/index/course/courseParticulars?id='+e.data.list[x].video_class_id+'&startTime='+e.data.list[x].startTime+'&videoid='+e.data.list[x].video_id+'"><span class="note_content_start_time">'+e.data.list[x].startTime+'</span></a>'+
                                  '</div><div class="note_content_bottom_right">'+
                                  '<span class="note_content_deleat" data-volumnid="'+e.data.list[x].id+'">删除</span>'+
                                  '<span class="note_date">'+e.data.list[x].intime+'</span>'+
                                  '</div></div>';
                  }
                  notelist += '</div>';
                  var pagelist = '<div class="common-pages" id="mynotePage_div"><ul class="common-pages-num" id="mynotePage">'+e.data.page+'</ul></div>'
                  $('.course_note').append(notelist).append(pagelist);
              }
          })
  }

  //  年级选择  个人中心 我的收藏变化
  function centerindex_1(e){
      //我的收藏
      if($(".personal-collect-top-edit").html() == '完成'){
        $(".personal-collect-top-edit").click();
      }
      //科目列表
      myCollectionSubjectList();
      var subject=0;
      var pagenow=1;
      myCollection(subject,pagenow);
  }

  //  年级选择  个人中心 课程列表变化
  function centerindex_0(e){
      var gid = e
          // ,sid = $(e.elem).find('option:selected').attr('data-v')
          // $('li[data-id="'+sid+'"]').index()
          ,sid = 1
          ,semid = 1
          ,obj_html = $('.personal-course-contain-lis').eq(0);

      // 科目列表
      $.ajax({
            url: '/index/person/getsubjectlist'
            ,data: {gid:gid}
            ,dataType: 'json'
            ,type: 'post'
            ,success: function(e){
                var class3 = '', class1 = '',class2 = '',html = '',_li = '';
                for(x in e.data){
                    if(x == 0 && e.data[x].is_buy == 0){
                        class1 = 'personal-subject-not-buy';
                    }
                    if(x == 0 && e.data[x].is_buy == 1){
                        class2 = 'personal-course-subject-active';
                    }
                    if(x != 0 && e.data[x].is_buy == 0){
                      class3 = 'personal-subject-default';
                    }
                    html += '<li class="person-center-course-subject  '+class1+' '+class2+' '+class3+'" data-seme="1"';
                    html += ' data-val="'+e.data[x].is_buy+'" data-id="'+e.data[x].id+'">';
                    html += '<span class="personal-subject">'+e.data[x].subject+'</span></li>';
                    class1 = ''
                    class2 = ''
                    class3 = ''
                    _li += '<li class="personal-center-icon-lis"><img src="/static/default/images/icon'+e.data[x].subject+e.data[x].is_buy+'.png" alt=""></li>';
                }
                $('.personal-center-top-icon').html(_li);
                $('#myClassSubject').html(html);
            }
      })
      // subject_list(sid,semid,obj_html,gid);
      studyCourseList(gid);
      element.init('collapse')
      obj_html.find('.particulars-container-lis-title').eq(0).click();
  }
  // 我的收藏点击编辑时样式变化
  $(".personal-collect-top-edit").click(function() {
    var editValue = $(this).html();
    if (editValue == "编辑" ) {
      $(this).css({background:"#DF4A43"})
           .html("完成")
      $(".personal-collect-select-lis-last").show()
      $(".personal-collect-contain-delete").show()
      $(".personal-collect-contain-bottom").hide()
      $(".personal-collect-contain-changename").show()
    } else {
      $(this).css({background:"#B0B4BD"})
           .html("编辑")
      $(".personal-collect-select-lis-last").hide()
      $(".personal-collect-contain-delete").hide()
      $(".personal-collect-contain-bottom").show()
      $(".personal-collect-contain-changename").hide()
    }
    
  })
  // 鼠标移入移出样式变化
  $(".personal-collect-contain-lis").on("mouseenter", function() {
    var editValue = $(".personal-collect-top-edit").html();
    var editValue1 = "编辑",
        editValue2 = "完成"
    if (editValue == editValue1) {
      $(this).animate({},500,function() {
        $(this).css({"transform":"translateY(-10px)"})
      })
      return;
    }
  })
  $(".personal-collect-contain-lis").on("mouseleave", function() {
    var editValue = $(".personal-collect-top-edit").html();
    var editValue1 = "编辑",
        editValue2 = "完成";
    if (editValue == editValue1) {
      $(this).animate({},500,function() {
        $(this).css({"transform":"translateY(0)"})
      })
      return;
    } else {

    }
  })
//获取注册卡列表页面数据
function getPersonCodeList(pagenow)
{
  $.post('/index/person/getPersonCodeList',{pagenow:pagenow},function(res){
    if(res.error_code==1){
      $(".persoanl-card-record").show()
      $(".personal-collect-card-none").hide()
      var data = res.data;
      var html='';
      for(x in data){
        html+='<li class="persoanl-card-record-lis">'+
              '<p class="persoanl-card-record-number">卡号'+data[x].card+'已激活</p>'+
              '<span class="persoanl-card-record-time">'+data[x].update_time+'</span>'+
              '</li>';
      }
      $('#codeList').html(html);

      //$('#page').html(res.msg);
    }else{
      // var html=res.msg;
      // $('#codeList').html(html);
      //$('#page').html('');
      $(".persoanl-card-record").hide()
      $(".personal-collect-card-none").show()
    }
  })
}
  // 修改头像
  $(".personal-center-change-pic").click(function(){
    window.location.href = "../html/personal-headPortrait.html";
  })
  // 信息中心刷新页面
  $(".personal-msg-center").click(function(){
    $(".personal-message-particulars").hide()
    $(".personal-message-center").show()
  })

// 卡号激活年级选择框
// $(document).on("click",".persoanl-card-grade-lis",function() {
//   var id = $(this).attr("data-gid")
//   cardactivation(id)
// })
//  监听激活按钮
 var cid;
$(document).on('click',".personal-course-new-activated-continue-xuexi",function(){
      cid = $(this).attr('data-v');
      // 激活提示框弹出
      $(".card-activation-modal").show()
      $(".card-activation-confirm").show()
   
})

  // function cardactivation(id){
  //   var id = id || 0;
  //     $.ajax({
  //         url: '/index/person/cardactivation'
  //         ,data: {gid:id}
  //         ,dataType: 'json'
  //         ,type: 'post'
  //         ,success: function(e){
  //             if(e.code == 1001){
  //             }
  //             // <!-- persoanl-card-activation-all-btn   激活按钮选中状态 -->
  //             // <!-- persoanl-card-activation-buy-not-active 已购买未激活科目才有的按钮样式 -->
  //             // <!-- persoanl-card-activation-not-all  鼠标滑过展示的样式 不需要在此类名上做文章 -->
            
  //             var html = '';
  //             for(x in e.data){
  //               var class_name = '',btn_name = '激活',btn_class_name = '',btn_click = '';
  //               if(e.data[x].is_activate == 0){
  //                 class_name = 'persoanl-card-activation-select-notbuy';
  //                 btn_name = '未购买';
  //               }
  //               // 可激活
  //               if(e.data[x].is_activate == 1){
  //                 btn_class_name = 'persoanl-card-activation-all-btn';
  //                 btn_click = 'activation';
  //               }
  //               // 剩余天数
  //               if(e.data[x].is_activate == 2){
  //                 class_name = 'persoanl-card-activation-select-already-activate';
  //                 btn_name = e.data[x]['residue_day'];
  //               }
  //               if(e.data[x].is_activate == 3){
  //                 class_name = 'persoanl-card-activation-select-already-activate';
  //                 btn_name = '已过期';
  //               }
  //               if(e.data[x].is_activate == 4){
  //                 class_name = 'persoanl-card-activation-all-btn';
  //                 btn_name = '试听课程';
  //               }
  //               if(e.data[x].is_activate == 5){
  //                 btn_class_name = 'persoanl-card-activation-all-btn';
  //                 btn_name = '超级vip';
  //               }
  //               html += '<div class="persoanl-card-activation-lis '+class_name+'">'+
  //                         '<div class="persoanl-card-activation-checkbox">'+e.data[x].name+'</div>'+
  //                         '<div class="persoanl-card-activation-btn persoanl-card-activation-not-all '+btn_class_name+'" name="'+btn_click+'" data-v="'+e.data[x].id+'">'+btn_name+'</div>'+
  //                       '</div>';
  //             }

  //             $('.persoanl-card-activation-all').html(html);
  //             $(".persoanl-card-grade-lis").removeClass("persoanl-card-grade-lis-click");
  //             $(".persoanl-card-grade-lis[data-gid='"+e.count+"']").addClass("persoanl-card-grade-lis-click")

  //         }
  //     })
  // }

  // 确定激活
  form.on('submit(agreeBtn)', function(data){
    if(data.field.ok == undefined){
      layui.use('layer',function(){
        layui.layer.msg('请同意激活本课程！',{icon:0, time:1500});
      })
      return false;
    }
    $.ajax({
      url : '/index/person/activeCourses'
      ,data: {cid:cid}
      ,dataType: 'json'
      ,type: 'post'
      ,async: false
      ,success :function(e){ 
          if(e.code == 0){
              // cardactivation(e.data);
              $(".card-activation-confirm").hide()
              $(".card-activation-modal-contain").show()
          }
      }
   })
  });
  // 取消激活弹窗
  $(".card-activation-modal-confirm-close, .card-activation-modal-contain-close").click(function() {
    $(".card-activation-modal").hide()
    $(".card-activation-confirm").hide()
    $(".card-activation-modal-contain").hide()
    $(".persoanl-card-activation-buy-not-active").removeClass("persoanl-card-activation-all-btn")
    var gid = glode_grade;
    console.log($(".select_form .layui-anim-upbit dd['lay-value="+gid+"']"))
  });
  // 确定激活按钮
  $(".card-activation-modal-contain-btn").click(function() {
    var gid= glode_grade;
    $(".card-activation-modal").hide()
    // $(".card-activation-confirm").hide()
    $(".card-activation-modal-contain").hide()
    window.location = '/index/person/person?gid='+gid;
  });

  // 消息点击
  var _type = $('#_type').val();
  if(_type == 1){
      $('.personal-tab-left-lis').eq(3).click();
  } 


  //学习笔记内容js
  $(document).on('click','.course_note_menu li',function(){
    $('.course_note_menu_li').removeClass('course_note_menu_li_active');
    $(this).addClass('course_note_menu_li_active');
    courseNote();
  })

  $(document).on('click','.course_note_volume_text',function(){
      $('.course_note_volume_text').removeClass('course_note_volume_active');
      $(this).addClass('course_note_volume_active');
      courseNote();
  })

  //点击删除笔记
  $(document).on('click','.note_content_deleat',function(){
    var pageNow = $('.common-pages-nums-click').html()
        ,id = $(this).attr('data-volumnid')
        ,sid = $('.course_note_menu_li_active').attr('data-subjectid')
        ,vid = $('.course_note_volume_active').attr('data-volumn');;

        $.ajax({
              url: '/index/person/deletevolumn'
              ,dataType: 'json'
              ,data: {id:id,sid:sid,vid:vid,gid:glode_grade}
              ,type: 'post'
              ,async: false
              ,success: function(e){
                  if(e.code == 0){

                      courseNote(pageNow);
                  }
              }
        })

    // $(this).parents('.course_note_content').remove();
  })

  //验证试听权限
  layui.clickurl = function(id,audi,that){
    var url = $(that).attr('data-url');
    $.ajax({
       type: "POST",  
       url: "/index/course/checkAudiVideo",  
       data: {videoid:id},  
       dataType: "json",  
       success: function(data){
           if(data.error_code == 1){
            $('.free_audition').html(data.msg)
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
              if(data.data){
                window.location.href = data.data;
              }else{
                window.location.href = url;
              }
            })
           }else if(data.error_code == 0){
            //  layer.msg(data.msg,{time:1500,icon:0});
            $('.free_audition').html(data.msg)
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
            })
           }else if(data.error_code == 2){
            window.location.href = url;
           }
        },  
       error:function(e){  

       }  
  })

  }

// 我的课程新版
  function studyCourseList(gid){
  $.ajax({
    url: '/index/person/myNewClassList'
    ,data: {class_id:gid}
    ,dataType: 'json'
    ,async: false
    ,type: 'post'
    ,success: function(res){
      console.log(res.data.notActiveCourseList)
      var html="";
      var nohtml = '';
      var nobuyhtml = '';
      var data = res.data.courseList;
      if(res.data.classList != '')
      {
        var obj = document.getElementById("personal-course-new-activated");
        obj.style.cssText = "display:block";
        html = '<dt class="personal-course-new-activated-state">已激活</dt>'+
                  '<dd></dd>';
      }
      if(res.data.notActiveCourseList != '')
      {
        var obj = document.getElementById("personal-course-new-activated-no");
        obj.style.cssText = "display:block";
        nohtml ='<dt class="personal-course-new-activated-state">未激活</dt>';
      }
      nobuyhtml = '<dt class="personal-course-new-recommend-state">'+
              '<span>推荐课程</span>'+
              '<a class="personal-course-new-recommend-more" href="javascript:;">更多 >></a>'+
            '</dt>';
      for(x in data){
        var studyPeriod = data[x].studyPeriod;
        var allPeriod = data[x].countClassChapter;
        var percent = studyPeriod/allPeriod;
        if(data[x].is_buy == 1){
           
          html +='<dd class="personal-course-new-activated-lis">'+
                  '<span class="personal-course-new-activated-subject">'+data[x].title+'</span>'+
                  '<div class="personal-course-new-activated-img">'+
                   ' <img src="'+data[x].img+'" alt="">'+
                  '</div>'+
                  '<div class="personal-course-new-activated-right clearfix">'+
                    '<h4 class="personal-course-new-activated-title">'+data[x].name+data[x].Semester+'</h4>'+
                    '<p class="personal-course-new-activated-num">课程数量：'+data[x].countClassChapter+'节   已学'+data[x].studyPeriod+'节</p>'+
                   ' <div class="layui-progress">'+
                     ' <div class="layui-progress-bar" lay-percent="'+percent * 100 +'%"></div>'+
                   ' </div>'+
                    '<div class="personal-course-new-activated-study">';
                    if(data[x].is_activate == 1){
                      html += '  <span class="personal-course-new-activated-day">剩余'+data[x].expireTime+'天</span>';
                    }else{
                      html +='  <span class="personal-course-new-activated-day">'+data[x].expireTime+'</span>';
                    }
                    if(data[x].video_id){
                      html +=' <a class="personal-course-new-activated-continue" href="/index/course/courseParticulars?id='+data[x].id+'&startTime='+data[x].studyTime+'&videoid='+data[x].video_id+'">继续学习</a>';
                    }else{
                      html += '  <a class="personal-course-new-activated-continue" href="/index/course/courseParticulars?id='+data[x].id+'">立即学习</a>';
                    }
                   html += '</div>'+
                  '</div>'+
                '</dd>';
        }
        if(data[x].is_buy == 0){

          nohtml += '<dd class="personal-course-new-activated-lis">'+
                      '<span class="personal-course-new-activated-subject">'+data[x].title+'</span>'+
                      '<div class="personal-course-new-activated-img">'+
                       ' <img src="'+data[x].img+'" alt="">'+
                      '</div>'+
                      '<div class="personal-course-new-activated-right clearfix">'+
                        '<h4 class="personal-course-new-activated-title">'+data[x].name+data[x].Semester+'</h4>'+
                        '<p class="personal-course-new-activated-num">课程数量：'+data[x].countClassChapter+'节 </p>'+
                       ' <div class="personal-course-new-activated-study personal-course-new-activated-no">'+
                         ' <a class="personal-course-new-activated-continue personal-course-new-activated-continue-xuexi" href="javascript:;" data-v="'+data[x].id+'">激活</a>'+
                       ' </div>'+
                     ' </div>'+
                    '</dd>';
        }
    }
    if(res.count){
          var count = res.count;
          for(x in count){
            nobuyhtml += '<dd class="personal-course-new-recommend-lis">'+
              '<div class="personal-course-new-recommend-img">'+
               ' <a href="/index/course/courseParticulars?id='+count[x].id+'"><img src="'+count[x].img+'" alt=""></a>'+
             ' </div>'+
              '<div class="personal-course-new-recommend-bottom clearfix">'+
               ' <span class="personal-course-new-recommend-title">'+count[x].name+'</span>'+
                '<span class="personal-course-new-recommend-subject personal-course-new-recommend-subject-2">'+count[x].title+'</span>'+
             ' </div>'+
           ' </dd>';
          }
      }
            // var count = res.count;
            // for(x in count){
            //   nobuyhtml += '<dd class="personal-course-new-recommend-lis">'+
            //     '<div class="personal-course-new-recommend-img">'+
            //     ' <a href="/index/course/courseParticulars?id='+count[x].id+'"><img src="'+count[x].img+'" alt=""></a>'+
            //   ' </div>'+
            //     '<div class="personal-course-new-recommend-bottom clearfix">'+
            //     ' <span class="personal-course-new-recommend-title">'+count[x].name+'</span>'+
            //       '<span class="personal-course-new-recommend-subject personal-course-new-recommend-subject-2">'+count[x].title+'</span>'+
            //   ' </div>'+
            // ' </dd>';
            // }
        $('#personal-course-new-activated').html(html);
        $('#personal-course-new-activated-no').html(nohtml);
        $('#personal-course-new-recommend').html(nobuyhtml);
        element.render();
      }
    });
  }
    // 2020-2-7王莹 评测信息
  $(".evaluation-infomation-ul li").click(function() {
    $(".evaluation-infomation-ul li").removeClass("evaluation-infomation-li-active")
    $(this).addClass("evaluation-infomation-li-active")
    var testPaperSubject=$('.evaluation-infomation-li-active').attr('data-subject');
    sessionStorage.setItem("testPaperSubject",testPaperSubject)
    var semester =  sessionStorage.getItem("testPaperSemester");
    if(semester == null){     //如果不存在，默认为1
      var semester = 1;
    }
    var pagenow = 1;
    testPaperList(testPaperSubject,semester,pagenow);
  })
  $(".evaluation-infomation-ce span").click(function() {
    $(".evaluation-infomation-ce span").removeClass("evaluation-infomation-ce-active")
    $(this).addClass("evaluation-infomation-ce-active")
    var testPaperSemester=$('.evaluation-infomation-ce-active').attr('data-semester');
    sessionStorage.setItem("testPaperSemester",testPaperSemester)
    var testPaperSubject =  sessionStorage.getItem("testPaperSubject");
    if(testPaperSubject == null){     //如果不存在，默认为1
      var testPaperSubject = 1;
    }
    var pagenow = 1;
    testPaperList(testPaperSubject,testPaperSemester,pagenow);
  })
  // var ue = UE.getEditor('editor',{initialFrameWidth:"90.8%",initialFrameHeight:300,zIndex:1});
  $(document).on('click','.evaluation-infomation-lis',function() {
    $(".evaluation-infomation-lis").removeClass("evaluation-infomation-title-click")
    $(this).addClass("evaluation-infomation-title-click")
    // $("#evaluation-particular").load("/index/person/person/evaluationParticular.html")
     var id = $(this).attr("data-val");
     $.ajax({
          url: '/index/person/testPaperInfo'
          ,dataType: 'json'
          ,data: {id:id}
          ,type: 'post'
          ,async: false
          ,success: function(e){
            
              if(e.error_code == 1){
                var data = e.data;
                var html = "";
                html +='<h3 class="evaluation-particular-title">第十一章 三角形 '+
                          '<span>测试中</span> '+
                          '<div class="evaluation-particular-close">+</div> '+
                       '</h3>';
                html += '<div class="evaluation-particular-list">'+
                          '<h4 class="evaluation-particular-list-title">'+data[0]['title']+'（100分）</h4>'+
                          '<ul class="evaluation-particular-ul">';
                for(x in data){
                  if(data[x].q_type == 1){    //选择题
                    var regExp = /<p>(.*?)<\/p>/gi;
                    res = data[x]['q_select'].match(regExp);
                    var i = parseInt(x)+1;
                    var q_stem = data[x]['q_stem'].replace(/<p>/gm,'');
                    var q_stem1 = q_stem.replace(/<\/p>/gm,'');
                    html+='<li class="evaluation-particular-lis">'+
                          '<p class="evaluation-particular-lis-menu">'+
                           ''+i+'、【'+data[x]['questype']+'】'+q_stem1+''+
                           ' <!-- 做对题给一个小红花 -->'+
                           // ' <img src="flower.png" alt="">'+
                         ' </p>'+
                         ' <div class="evaluation-particular-lis-chiose layui-form">'+
                           ' <div class="layui-input-block">';
                             for(x in res){
                              var str = res[x].replace(/<p>/gm,'');
                              var str1 = str.replace(/<\/p>/gm,'');
                              html+= '<input type="radio" name="sex" value="'+str1+'" title="'+str1+'">';
                             }
                             // return ;
                          html+= '</div>'+
                          //' </div>'+
                          // '<span class="evaluation-particular-tips-right">恭喜你，答对啦</span>'+
                          // '<!-- evaluation-particular-tips-error -->'+
                          // '<div class="evaluation-particular-lis-answer">'+
                          // '  解答：'+
                          // '  <p>解：因为，正方开$周长长X4，所以J正方形周长+边长=4.'+
                          // '  答：正方形的周长是边长的4倍.'+
                          // '  故选：A.</p>'+
                          // '</div>'+
                          '</li>';
                  }else if(data[x].q_type == 6){ //问答题
                      var i = parseInt(x)+1;
                      var q_stem = data[x]['q_stem'].replace(/<p>/gm,'');
                      var q_stem1 = q_stem.replace(/<\/p>/gm,'');
                      html+='<li class="evaluation-particular-lis">'+
                      '<p class="evaluation-particular-lis-menu">'+
                        ''+i+'、【'+data[x]['questype']+'】'+q_stem1+''+
                      '</p>'+
                    '<div class="evaluation-particular-lis-topicimg">';
                      if(data[x]['q_stem_img'] != ''){
                        html+='<img src="'+data[x]['q_stem_img']+'" alt="">';
                      }
                      // '<img src="flower.png" alt="">'+
                    html+='</div>'+
                      '<div class="evaluation-particular-lis-upload clearfix">'+
                        '<div class="evaluation-particular-lis-left">'+
                          '上传答案'+
                          '<div class="evaluation-particular-lis-loadimg" id="">+</div>'+
                        '</div>'+
                        '<div class="evaluation-particular-lis-img">'+
                          '<img src="" alt="">'+
                        '</div>'+
                      '</div>'+
                    '</li>';
                  }else if(data[x].q_type == 2){    //多选题
                      var i = parseInt(x)+1;
                      var q_stem = data[x]['q_stem'].replace(/<p>/gm,'');
                      var q_stem1 = q_stem.replace(/<\/p>/gm,'');
                      var regExp = /<p>(.*?)<\/p>/gi;
                      res = data[x]['q_select'].match(regExp);
                      html+='<li class="evaluation-particular-lis">'+
                            '<p class="evaluation-particular-lis-menu">'+
                              ''+i+'、【'+data[x]['questype']+'】'+q_stem1+''+
                              '<!-- 做对题给一个小红花 -->'+
                              // '<img src="flower.png" alt="">'+
                            '</p>'+
                            '<div class="evaluation-particular-lis-chiose layui-form clearfix">'+
                              '<div class="layui-input-block">';
                                for(x in res){
                                  var str = res[x].replace(/<p>/gm,'');
                                  var str1 = str.replace(/<\/p>/gm,'');
                                  html+='<div class="layui-input-block">'+
                                    '<input type="checkbox" name="" title="'+str1+'" lay-skin="primary">'+
                                  '</div>';
                                }
                              '</div>'+
                            '</div>'+
                            '<span class="evaluation-particular-tips-right">恭喜你，答对啦</span>'+
                            '<!-- evaluation-particular-tips-error -->'+
                            '<!-- <div class="evaluation-particular-lis-answer">'+
                              '解答：'+
                              '<p>解：因为，正方开$周长长X4，所以J正方形周长+边长=4.'+
                              '答：正方形的周长是边长的4倍.'+
                              '故选：A.</p>'+
                            '</div> -->'+
                          '</li>';
                  }else if(data[x].q_type == 3){   //判断题
                      var i = parseInt(x)+1;
                      var q_stem = data[x]['q_stem'].replace(/<p>/gm,'');
                      var q_stem1 = q_stem.replace(/<\/p>/gm,'');
                      html+='<li class="evaluation-particular-lis">'+
                            // '<p class="evaluation-particular-lis-menu">'+
                            //   '6、【判断题】巧思妙断，判断对错。（ 正确为Y，错误为N。'+
                            //   '<!-- 做对题给一个小红花 -->'+
                            //   '<img src="flower.png" alt="">'+
                            // '</p>'+
                            '<div class="evaluation-particular-lis-chiose layui-form clearfix">'+
                              '<div class="layui-input-block">'+
                                '<div class="layui-input-blanks layui-input-yn">'+
                                  ''+i+'、【'+data[x]['questype']+'】'+q_stem1+'(<input type="text" name="title" required lay-verify="required" autocomplete="off" class="layui-input">)'+              
                                '</div>'+
                              '</div>'+
                            '</div>'+
                          '</li>';
                  }
                  
                }
                html+="</ul></div>";
                $("#paperList").hide();
                // $('#myTestPaperPage').hide();   
                $('#evaluation-particular').show(); 
                $('#paperInfo').html(html);
                form.render();
              }
          }
    })
  })

  // 2020-02-10王莹 试卷
// 试卷关闭
$(document).on('click','.evaluation-particular-close',function() {
  $("#paperList").show();
  $('#evaluation-particular').hide(); 
  $('#paperInfo').html( )
})
})



