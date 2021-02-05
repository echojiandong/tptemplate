layui.use(['element','layer', 'form','laypage','laydate','upload'], function(){
    var layer = layui.layer
        ,element = layui.element
        ,form = layui.form
        ,laypage = layui.laypage
        ,laydate = layui.laydate
        ,upload = layui.upload
        ,glode_grade = 7;
        var time = '';//试卷测评倒计时
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

    }else if(a == 7){     //错题本
      var subject_id = 1;
      var semester = 1;
      var pagenow = 1;
      wrongQuestion(subject_id,semester,pagenow);
    }else if(a == 8){    //学习周报
      var subject_id = 1;
      studyWeeklyResport(subject_id);   //学习周报
    }

    if(a == 3){
        centerindex_2(0)
    }

  })
  
  //学习周报
  function studyWeeklyResport(subject_id,timeStart,timeEnd){
    var timeStart = timeStart || '';
    var timeEnd = timeEnd || '';
    var subject_id = subject_id || 1;
    $.post('/index/person/studyWeeklyResport',{subject_id:subject_id,timeStart:timeStart,timeEnd:timeEnd},function(result){
      var html= '';
      if(result.error_code==1){
        var studyTime = 0;
        var maxFraction = 0;
        var minFraction = 0;
        var avgFraction = 0;
        var content = '暂无数据';
        if(result.data.data != null){
          var studyTime = Math.ceil(result.data.data.studyTime/60);
          var avgFraction = Math.ceil(result.data.data.avgFraction);
          var maxFraction = Math.ceil(result.data.data.maxFraction);
          var minFraction = Math.ceil(result.data.data.minFraction);
        }
        if(result.data.teacherAdvise != null){
          var content = result.data.teacherAdvise.content;
        }
        html +='<div class="personl-weekly-contain">'+
            '<ul class="personl-weekly-top clearfix">'+
              '<li>'+
                '<div class="personl-weekly-title">'+
                  '<h5>学习总时长</h5>'+
                '</div>'+
                '<p class="personl-weekly-num" id="studyTime">'+ studyTime+'<span>分钟</span></p>'+
              '</li>'+
              '<li>'+
                '<div class="personl-weekly-title">'+
                  '<h5>测验最高分</h5>'+
                '</div>'+
                '<p class="personl-weekly-num" id="maxFraction">'+maxFraction+'<span>分</span> </p>'+
              '</li>'+
              '<li>'+
                '<div class="personl-weekly-title">'+
                  '<h5>测验最低分</h5>'+
                '</div>'+
                '<p class="personl-weekly-num" id="minFraction">'+minFraction+'<span>分</span> </p>'+
              '</li>'+
              '<li>'+
                '<div class="personl-weekly-title">'+
                  '<h5>测验平均分</h5>'+
                '</div>'+
                '<p class="personl-weekly-num" id="avgFraction">'+avgFraction+'<span>分</span></p>'+
              '</li>'+
            '</ul>'+
            '<div class="personl-weekly-echarts">'+
              '<div id="container" class="personl-weekly-echarts-container" style="width:973px; height:535px;"></div>'+
              '<div class="personl-weekly-echarts-select layui-form">'+
                '<div class="layui-input-inline">'+
                  '<input type="text" class="layui-input" id="date1">'+
                '</div>'+
                '<div class="layui-input-inline">'+
                  '<select name="" lay-verify="required" lay-filter="test">'+
                    '<!-- <option value=""></option> -->'+
                    '<option value="1" >语文</option>'+
                    '<option value="2" >数学</option>'+
                    '<option value="3" >英语</option>'+
                    '<option value="4" >物理</option>'+
                    '<option value="5" >化学</option>'+
                  '</select>'+
                '</div>'+
              '</div>'+
              '<div class="personl-weekly-suggestions">'+
                '<h3>学习建议</h3>'+
                '<p id="content">'+content +'</p>'+
              '</div>'+
            '</div>'+
          '</div>';
          
          $('#studyWeeklyResport').html(html);
          $('#studyWeeklyResport').show();
          // console.log(html); return false;
          form.render();
          // 周报
          var startWeekTime = WeekTime().startWeekTime;
          var endWeektime = WeekTime().endWeektime;
          laydate.render({
            elem: '#date1',
            format: "yyyy/MM/dd-yyyy/MM/dd"
            ,value: startWeekTime +'-'+ endWeektime
            ,btns: ['clear', 'now'],
            trigger: 'click',
            done: function(value, date, endDate){
                if(value!="" && value.length>0){
                    var today=new Date(value.substring(0,10));
                    var weekday=today.getDay();
                    var monday;
                    var sunday;
                    if (weekday==0) {
                        monday=new Date(1000*60*60*24*(weekday-6) + today.getTime());
                    }  else {
                        monday=new Date(1000*60*60*24*(1-weekday) + today.getTime());
                    }
                    if (weekday==0) {
                        sunday=today;
                    }  else {
                        sunday=new Date(1000*60*60*24*(7-weekday) + today.getTime());
                    }
                    var month = monday.getMonth()+1;
                    if(month<10)
                    {
                        month = "0"+month;
                    }
                    var day1 = monday.getDate();
                    if(day1<10)
                    {
                        day1 = "0"+day1;
                    }
                    var start = ""+monday.getFullYear()+"/"+month+"/"+day1;
                    var month2 = sunday.getMonth()+1;
                    if(month2<10)
                    {
                        month2 = "0"+month2;
                    }
                    var day2 = sunday.getDate();
                    if(day2<10)
                    {
                        day2 = "0" + day2;
                    }
                    var end = ""+sunday.getFullYear()+"/"+month2+"/"+day2;
                    $("#date1").val(start+"-"+end);
                }else{
                    $("#date1").val('');
                }
                sessionStorage.setItem("weeklyReposrtStart",start)
                sessionStorage.setItem("weeklyReposrtEnd",end)
                // var semester =  sessionStorage.getItem("errorBookSemester");
                var subject_id =sessionStorage.getItem("weeklyReposrtSubject");
                if(subject_id == ''){
                  subject_id = 1;
                }
                clickStudyWeeklyResport(subject_id,start,end);   //学习周报
              }
          });
          myEcharts();
          var dom = document.getElementById("container");
          var myChart = echarts.init(dom);
          myChart.setOption({        //加载数据图表
            series: [
                      {
                        name: '观看视频时长',
                        type: 'line',
                        stack: '总量1',
                        data: result.data.myChart,
                        color:'#00A0E9'
                      },
                      {
                        name: '科目平均分',
                        type: 'line',
                        stack: '总量2',
                        data: result.data.testDataList,
                        color:'#E8514A'
                      },
                    ]
          });   
         
      }
    });
  }
  // 封装日期插件
function WeekTime() {
  var today=new Date();
  var weekday=today.getDay();
  var monday;
  var sunday;
  if (weekday==0) {
      monday=new Date(1000*60*60*24*(weekday-6) + today.getTime());
  }  else {
      monday=new Date(1000*60*60*24*(1-weekday) + today.getTime());
  }
  if (weekday==0) {
      sunday=today;
  }  else {
      sunday=new Date(1000*60*60*24*(7-weekday) + today.getTime());
  }
  var month = monday.getMonth()+1;
  if(month<10)
  {
      month = "0"+month;
  }
  var day1 = monday.getDate();
  if(day1<10)
  {
      day1 = "0"+day1;
  }
  var startWeekTime = ""+monday.getFullYear()+"/"+month+"/"+day1;
  var month2 = sunday.getMonth()+1;
  if(month2<10)
  {
      month2 = "0"+month2;
  }
  var day2 = sunday.getDate();
  if(day2<10)
  {
      day2 = "0" + day2;
  }
  var endWeektime = ""+sunday.getFullYear()+"/"+month2+"/"+day2;
  // $("#date1").val(start+"-"+end);
  return {startWeekTime,endWeektime};
}
  //学习周报
  function clickStudyWeeklyResport(subject_id,timeStart,timeEnd){
    var timeStart = timeStart || '';
    var timeEnd = timeEnd || '';
    var subject_id = subject_id || 1;
    $.post('/index/person/studyWeeklyResport',{subject_id:subject_id,timeStart:timeStart,timeEnd:timeEnd},function(result){
      var html= '';
      if(result.error_code==1){
        var studyTime = 0;
        var maxFraction = 0;
        var minFraction = 0;
        var avgFraction = 0;
        var content = '暂无数据';
        if(result.data.data != null){
          var studyTime = Math.ceil(result.data.data.studyTime/60)+'分钟';
          var avgFraction = Math.ceil(result.data.data.avgFraction)+'分';
          var maxFraction = Math.ceil(result.data.data.maxFraction)+'分';
          var minFraction = Math.ceil(result.data.data.minFraction)+'分';
        }
        if(result.data.teacherAdvise != null){
          var content = result.data.teacherAdvise.content;
        }
        $('#content').html(content);
        $('#studyTime').html(studyTime);
        $('#avgFraction').html(avgFraction);
        $('#maxFraction').html(maxFraction);
        $('#minFraction').html(minFraction);
        myEcharts();
        var dom = document.getElementById("container");
        var myChart = echarts.init(dom);
        myChart.setOption({        //加载数据图表
          series: [
                    {
                      name: '观看视频时长',
                      type: 'line',
                      stack: '总量1',
                      data: result.data.myChart,
                      color:'#00A0E9'
                    },
                    {
                      name: '科目平均分',
                      type: 'line',
                      stack: '总量2',
                      data: result.data.testDataList,
                      color:'#E8514A'
                    },
                  ]
        });   
      }
    });
  }

  function myEcharts(){
    var dom = document.getElementById("container");
    var myChart = echarts.init(dom);
    var app = {};
    option = null;
    option = {
        title: {
            text: '学习周报'
        },
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            data: ['观看视频时长', '科目平均分'],
            // orient:"horizontal",
            x:'right',
            top:'8%',
            right:'-45%',
            // width:'100'
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        toolbox: {
          show:false,
          feature: {
            saveAsImage: {}
          }
        },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: ['周一', '周二', '周三', '周四', '周五', '周六', '周日']
        },
        yAxis: {
            type: 'value',
            name:'分钟',
            splitLine:{
              show:true,
              lineStyle:{
                type:'dashed'
              }
            },
            axisLine:{
              show:false
            },
            axisTick:{
              show:false
            }
        },
        series: [
            {
                name: '语文',
                type: 'line',
                stack: '总量1',
                data: [10, 13, 11, 34, 40, 30, 20],
                color:'#00A0E9'
            },
            {
                name: '数学',
                type: 'line',
                stack: '总量2',
                data: [20, 12, 31, 34, 50, 30, 30],
                color:'#E8514A'
            }
        ]
    };
    if (option && typeof option === "object") {
      myChart.setOption(option, true);
    }
  }
  //监听学习周报科目
  form.on('select(test)', function(data){
    var subject_id = data.value;
    var start =sessionStorage.getItem("weeklyReposrtStart");
    var end =sessionStorage.getItem("weeklyReposrtEnd");
    if(start == ''){
      start = '';
    }
    if(end == ''){
      end = '';
    }
    clickStudyWeeklyResport(subject_id,start,end);   //学习周报
  });
  //错题列表
  function wrongQuestion(subject_id,semester,pagenow){
    $.post('/index/person/wrongQuestion',{gid:glode_grade,subject_id:subject_id,semester:semester,pagenow:pagenow},function(result){
      if(result.error_code==1){
        var html = '';
        var data=result.data;
        for(x in data){
          if(data[x].q_type == 1){
            var regExp = /<p[^>]*>([\S\s]*?)<\/p>/gi;
            res = data[x]['q_select'].match(regExp);
            var k = parseInt(x)+1;
            var q_stem = data[x]['q_stem'].replace(/<p>/gm,'');
            var q_stem1 = q_stem.replace(/<\/p>/gm,'');
            html +='<div class="evaluation-particular-lis-list layui-form">'+
                '<p class="evaluation-particular-lis-menu-title evaluation-particular-error-title">'+
                 ''+k+'、'+q_stem1+''+
                  '<span  class="evaluation-particular-lis-again" data-id="'+data[x]['q_id']+'">再做一次</span>'+
                '</p>'+
                '<div class="evaluation-particular-lis-chiose clearfix">'+
                  '<div class="layui-input-block layui-input-left">';
                    for(m in res){
                      // var str = res[m].replace(/<p>/gm,'');
                      // var str1 = str.replace(/<\/p>/gm,'');
                      var str1 = res[m].replace(/<\/?.+?>/g,"");
                      var q_select = str1.substring(0,1).toLocaleUpperCase();
                      html+='<input type="radio" name="radio" value="'+str1+'" title="'+str1+'">';
                    }
            html+='</div>'+
                  '<div class="layui-input-right">'+
                    '<img src="'+data[x]['q_stem_img']+'" alt="">'+
                  '</div>'+
                '</div>'+
                '<div class="evaluation-particular-lis-answer">'+
                  '解答：'+data[x].q_describe+
                  '故选：'+data[x].q_answer+
                '</div>'+
              '</div>';
          }else if(data[x].q_type == 2){
            var regExp = /<p[^>]*>([\S\s]*?)<\/p>/gi;
            res = data[x]['q_select'].match(regExp);
            var k = parseInt(x)+1;
            var q_stem = data[x]['q_stem'].replace(/<p>/gm,'');
            var q_stem1 = q_stem.replace(/<\/p>/gm,'');
            html +='<div class="evaluation-particular-lis-list layui-form">'+
                '<p class="evaluation-particular-lis-menu-title evaluation-particular-error-title">'+
                 ''+k+'、'+q_stem1+''+
                  '<span class="evaluation-particular-lis-again" data-id="'+data[x]['q_id']+'">再做一次</span>'+
                '</p>'+
                '<div class="evaluation-particular-lis-chiose clearfix">'+
                  '<div class="layui-input-block layui-input-left">';
                    // '<input type="radio" name="sex" value="A" title="A、4倍" checked>'+
                    // '<input type="radio" name="sex" value="B" title="B、3倍">'+
                    // '<input type="radio" name="sex" value="C" title="C、2倍">'+
                    for(m in res){
                      // var str = res[m].replace(/<p>/gm,'');
                      // var str1 = str.replace(/<\/p>/gm,'');
                      var str1 = res[m].replace(/<\/?.+?>/g,"");
                      var q_select = str1.substring(0,1).toLocaleUpperCase();
                      html+='<div class="layui-input-block"><input lay-skin="primary" type="checkbox" value="'+str1+'" title="'+str1+'"></div>';
                    }
            html+='</div>'+
                  '<div class="layui-input-right">'+
                    '<img src="'+data[x]['q_stem_img']+'" alt="">'+
                  '</div>'+
                '</div>'+
                '<div class="evaluation-particular-lis-answer">'+
                  '解答：'+data[x].q_describe+
                  '故选：'+data[x].q_answer+
                '</div>'+
              '</div>';
          }else if(data[x].q_type == 3){
            var k = parseInt(x)+1;
            var q_stem = data[x]['q_stem'].replace(/<p>/gm,'');
            var q_stem1 = q_stem.replace(/<\/p>/gm,'');
            html+='<div class="evaluation-particular-lis-list layui-input-blanks evaluation-particular-error-title layui-input-yn layui-form">'+
                ''+k+'、'+q_stem1+''+
                '<input type="radio" name="radio" value="是" title="是"> <input type="radio" name="radio" value="否" title="否">'+
                '<span class="evaluation-particular-lis-again" data-id="'+data[x]['q_id']+'">再做一次</span>'+    
              '</div>'+
              '<div class="evaluation-particular-lis-answer">'+
                  '解答：'+data[x].q_describe+
                  '故选：'+data[x].q_answer+
              '</div>';
          }
        }
        // console.log(res.msg)
        $('#wrongQuestionList').html(html);
        $('#myWrongQuestionPage').html(result.msg);
        $(".personal-collect-contain-none").hide();
        // $("#wrongQuerstSub").show();
        $("#wrongQuestionList").show();
        $('#myWrongQuestionPage').show();   //分页显示
         
      }else{
        $('#myWrongQuestionPage').hide();
        $(".personal-collect-contain-none").show();
        $("#wrongQuestionList").hide();
      }
      form.render()
    })
  }
  //错题本分页点击事件
  window.wrongQuestionFpage = function(pagenow,subject_id,semester,subjectId){
    wrongQuestion(subjectId,semester,pagenow);
    // if($(".personal-collect-top-edit").html() == '完成'){
    //     $(".personal-collect-select-lis-last").show()
    //     $(".personal-collect-contain-delete").show()
    // }
  }
  //错题本点击学科、学期事件
  $(".errorbook-infomation-ul li").click(function() {
    $(".errorbook-infomation-ul li").removeClass("errorbook-infomation-li-active")
    $(this).addClass("errorbook-infomation-li-active")
    var testPaperSubject=$('.errorbook-infomation-li-active').attr('data-subject');
    sessionStorage.setItem("errorBookSubject",testPaperSubject)
    var semester =  sessionStorage.getItem("errorBookSemester");
    if(semester == null){     //如果不存在，默认为1
      var semester = 1;
    }
    var pagenow = 1;
    wrongQuestion(testPaperSubject,semester,pagenow);
  })
  $(".errorbook-infomation-ce span").click(function() {
    $(".errorbook-infomation-ce span").removeClass("errorbook-infomation-ce-active")
    $(this).addClass("errorbook-infomation-ce-active")
    var testPaperSemester=$('.errorbook-infomation-ce-active').attr('data-semester');
    sessionStorage.setItem("errorBookSemester",testPaperSemester)
    var testPaperSubject =  sessionStorage.getItem("errorBookSubject");
    if(testPaperSubject == null){     //如果不存在，默认为1
      var testPaperSubject = 1;
    }
    var pagenow = 1;
    wrongQuestion(testPaperSubject,testPaperSemester,pagenow);
  })

  //错题本
  //监听提交
  form.on('submit(errQuestionSub)', function(data){
    var datas = JSON.stringify(data.field);
    console.log(datas)
    $.post('/index/person/errorQuestionSubmit',{data:datas},function(res){
      if(res.error_code == 1){
        layer.msg(res.msg,{time:1500,icon:6},function(){
            // layer.close(index);
            form.render();
        });
      }else if(res.error_code == 2){
        layer.msg(res.msg,{time:5000,icon:5},function(){
            // layer.close(index);
            form.render();
        });
      }else if(res.error_code == 0){
        layer.msg("提交失败",{time:1500,icon:5},function(){
            // layer.close(index);
            form.render();
        });
      }
    })
  });
  function doQestionInfo(data,count){
    var html = '';
    if(data.q_type == 1){
        var regExp = /<p[^>]*>([\S\s]*?)<\/p>/gi;
        res = data['q_select'].match(regExp);
        var q_stem = data['q_stem'].replace(/<p>/gm,'');
        var q_stem1 = q_stem.replace(/<\/p>/gm,'');

        html+='<div class="evaluation-particular-error-close">+</div><div class="evaluation-particular-lis-list  layui-form" id="evaluation-particular-modal-content">'+
              '<p class="evaluation-particular-lis-menu-title">'+
                ''+q_stem1+''+
              '</p>'+
              '<div class="evaluation-particular-lis-chiose clearfix">'+
                '<div class="layui-input-block layui-input-left">';
                  for(m in res){
                    // var str = res[m].replace(/<p>/gm,'');
                    // var str1 = str.replace(/<\/p>/gm,'');
                    var str1 = res[m].replace(/<\/?.+?>/g,"");
                    var q_select = str1.substring(0,1).toLocaleUpperCase();
                    html+='<input type="radio" name="answer_'+data.q_type+'_'+data['q_id']+'" value="'+str1+'" title="'+str1+'">';
                  }
        html+='</div>'+
              '</div>'+
              '<div class="evaluation-particular-error-change clearfix">';
              if(count != ''){
                html+='<span class="evaluation-particular-error-next" data-id="'+count+'">下一题</span>';
              }else{
                html+='<span class="evaluation-particular-error-next evaluation-particular-error-none" data-id="'+count+'">下一题</span>';
              }
        html+= '<input type="hidden" name="q_id" value="'+data['q_id']+'"><span class="evaluation-particular-error-submit"  id="errQuestionSub" lay-submit lay-filter="errQuestionSub">提交</span>'+
              '</div>'+
            '</div>';
        
      }else if(data.q_type == 2){    //多选
        var regExp = /<p[^>]*>([\S\s]*?)<\/p>/gi;
        res = data['q_select'].match(regExp);
        var q_stem = data['q_stem'].replace(/<p>/gm,'');
        var q_stem1 = q_stem.replace(/<\/p>/gm,'');
        html+='<div class="evaluation-particular-error-close">+</div><div class="evaluation-particular-lis-list  layui-form" id="evaluation-particular-modal-content">'+
              '<p class="evaluation-particular-lis-menu-title">'+
                ''+q_stem1+''+
              '</p>'+
              '<div class="evaluation-particular-lis-chiose clearfix">'+
                '<div class="layui-input-block layui-input-left">';
                  for(m in res){
                    // var str = res[m].replace(/<p>/gm,'');
                    // var str1 = str.replace(/<\/p>/gm,'');
                    var str1 = res[m].replace(/<\/?.+?>/g,"");
                    var q_select = str1.substring(0,1).toLocaleUpperCase();
                    html+='<div class="layui-input-block"><input type="checkbox" lay-skin="primary" name="answer_'+data.q_type+'_'+data['q_id']+'_[]" value="'+str1+'" title="'+str1+'"></div>';
                  }
        html+='</div>'+
              '</div>'+
              '<div class="evaluation-particular-error-change clearfix">';
              if(count != ''){
                html+='<span class="evaluation-particular-error-next" data-id="'+count+'">下一题</span>';
              }else{
                html+='<span class="evaluation-particular-error-next evaluation-particular-error-none" data-id="'+count+'">下一题</span>';
              }
        html+= '<input type="hidden" name="q_id" value="'+data['q_id']+'"><span class="evaluation-particular-error-submit" id="errQuestionSub" lay-filter="errQuestionSub" lay-submit>提交</span>'+
              '</div>'+
            '</div>';
      }else if(data.q_type == 3){
        var q_stem = data['q_stem'].replace(/<p>/gm,'');
        var q_stem1 = q_stem.replace(/<\/p>/gm,'');
        console.log(data.q_type)
        html+='<div class="evaluation-particular-error-close">+</div><div class="evaluation-particular-lis-list layui-form clearfix" id="evaluation-particular-modal-content">'+
                '<div class="evaluation-particular-error-block">'+
                  '<div class="layui-input-blanks evaluation-particular-error-title layui-input-yn ">'+
                    ''+q_stem1+''+
                    '<input type="radio" name="answer_'+data.q_type+'_'+data['q_id']+'" value="是" title="是"> <input type="radio" name="answer_'+data.q_type+'_'+data['q_id']+'" value="否" title="否">'+
                  '</div>'+
                  '<div class="evaluation-particular-error-change clearfix">';
        if(count != ''){
          html+='<span class="evaluation-particular-error-next" data-id="'+count+'">下一题</span>';
        }else{
          html+='<span class="evaluation-particular-error-next evaluation-particular-error-none" data-id="'+count+'">下一题</span>';
        }
        html+='<input type="hidden" name="q_id" value="'+data['q_id']+'"><span class="evaluation-particular-error-submit" lay-filter="errQuestionSub" id="errQuestionSub" lay-submit>提交</span>'+
                  '</div>'+
                '</div>'+
              '</div>';
      }
    $('#answerQuest').html(html)
    $(".evaluation-particular-error-modal").show()
    form.render();
  }
  // 点击再做一次，弹窗出现
  $(document).on('click','.evaluation-particular-error-title span',function() {
    //获取点击再做一次的data-id
    var id = $(this).attr('data-id');
    var subject_id =  sessionStorage.getItem("errorBookSubject");
    if(subject_id == null){     //如果不存在，默认为1
      var subject_id = 1;
    }
    var semester =  sessionStorage.getItem("errorBookSemester");
    if(semester == null){     //如果不存在，默认为1
      var semester = 1;
    }
    $.post('/index/person/doOneQuestion',{gid:glode_grade,subject_id:subject_id,semester:semester,id:id},function(result){
      if(result.error_code == 1){
        doQestionInfo(result.data,result.count);
      }
    })
  })
  // 关闭弹窗
  $(document).on('click',".evaluation-particular-error-close",function() {
    $(".evaluation-particular-error-modal").hide()
  })
  //点击下一题
  $(document).on('click',".evaluation-particular-error-next",function() {
    //获取点击再做一次的data-id
    var id = $(this).attr('data-id');
    var subject_id =  sessionStorage.getItem("errorBookSubject");
    if(subject_id == null){     //如果不存在，默认为1
      var subject_id = 1;
    }
    var semester =  sessionStorage.getItem("errorBookSemester");
    if(semester == null){     //如果不存在，默认为1
      var semester = 1;
    }
    $.post('/index/person/doOneQuestion',{gid:glode_grade,subject_id:subject_id,semester:semester,id:id},function(result){
      if(result.error_code == 1){
        doQestionInfo(result.data,result.count);
      }
    })
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
                '<span>'+data[x].create_at+'</span>';

                if(data[x].review != null){
                  if(data[x].review == 1){
                    html+='<span class="evaluation-infomation-status">已评阅</span>';
                  }else if(data[x].review == 2){
                    html+='<span class="evaluation-infomation-status">评阅中</span>';
                  }
                }else{
                  html+='<span class="evaluation-infomation-status">未完成</span>';
                }


              html+='</div>'+
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
      wrongQuestion(subject_id,semester,pagenow);   //错题本信息
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
                                  '<a href="/index/course/courseParticulars?id='+e.data.list[x].video_class_id+'&startTime='+e.data.list[x].startTime+'&videoid='+e.data.list[x].video_id+'&productStatus='+e.data.list[x].productStatus+'"><span class="note_content_start_time">'+e.data.list[x].startTime+'</span></a>'+
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
  });
  // 确定激活按钮
  $(".card-activation-modal-contain-btn").click(function() {
    var gid= glode_grade;
    $(".card-activation-modal").hide()
    // $(".card-activation-confirm").hide()
    $(".card-activation-modal-contain").hide()
    // window.location = '/index/person/person?gid='+gid;
    window.location = '/index/person/person';
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
                      html +=' <a class="personal-course-new-activated-continue" href="/index/course/courseParticulars?id='+data[x].id+'&startTime='+data[x].studyTime+'&videoid='+data[x].video_id+'&productStatus='+data[x].productStatus+'">继续学习</a>';
                    }else{
                      html += '  <a class="personal-course-new-activated-continue" href="/index/course/courseParticulars?id='+data[x].id+'&productStatus='+data[x].productStatus+'">立即学习</a>';
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
                         ' <a class="personal-course-new-activated-continue personal-course-new-activated-continue-xuexi" href="javascript:;" data-v="'+data[x].id+'+'+data[x].productStatus+'">激活</a>'+
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
            // console.log(e.data)
              if(e.error_code == 1){
                // var data = e.data;
                var newList = e.data.newList;   //普通试题列表
                var id = e.data.id;   //普通试题列表
                var testPaperList = e.data.testPaperList;  //试卷信息
                    testAllTime = testPaperList[0].testTime;
                    time = testAllTime*60;//秒
                var newQuestionrowsList = e.data.newQuestionrowsList;   //题帽题
                var count = parseInt(e.data.newList.length);   //数组的长度
                var bit = e.count;
                var html = "";
                var sort =0;//定义动态多上
                // html +='<h3 class="evaluation-particular-title">第十一章 三角形 '+
                //           '<span>测试中</span> '+
                //           '<div class="evaluation-particular-close">+</div> '+
                //        '</h3>';
                html += '<div class="evaluation-particular-list layui-form">'+
                          '<h4 class="evaluation-particular-list-title">'+testPaperList[0]['title']+'（100分）</h4>';
                          if(testPaperList[0]['testTime'] > 0 && e.data.log_fraction == null){
                            html += '<div class="error-endtime">剩余时间：<span>'+testPaperList[0]['testTime']+'</span></div>';
                            $(".error-endtime span").text('')
                            setTimer();
                          }else if(e.data.log_fraction != null){
                            html += '<div class="error-endtime">测试得分：<span>'+e.data.log_fraction+' 分</span></div>';
                          }else{
                            html += '<div class="error-endtime">本次测试不计时</div>';
                          }
                          html += '<ul class="evaluation-particular-ul">';
                for(x in newList){
                  var sonList = newList[x]['son'];
                  if(newList[x].q_type == 1){   //选择题
                    html+='<li class="evaluation-particular-lis">'+
                    '<p class="evaluation-particular-lis-menu">'+
                      ''+bit[x]+'、【'+newList[x]['questype']+'】选择正确的答案'+
                    '</p>';
                        // <!-- 每一个模块下的列表 -->
                        for(i in sonList){
                          var regExp = /<p[^>]*>([\S\s]*?)<\/p>/gi;
                          // var strSelect = sonList[i]['q_select'].replace(regExp,'<p>');
                          // var reg = /<p>([\S\s]*?)<\/p>/gi;
                          res = sonList[i]['q_select'].match(regExp);
                          var k = parseInt(i)+1;
                          var q_stem = sonList[i]['q_stem'].replace(/<p>/gm,'');
                          var q_stem1 = q_stem.replace(/<\/p>/gm,'');
                          html+='<div class="evaluation-particular-lis-list  layui-form">'+
                            '<p class="evaluation-particular-lis-menu-title">'+
                              ''+k+'、'+q_stem1+''+
                              '<!-- 做对题给一个小红花  做错没有-->';
                              // if(sonList[i]['log_status'] == 1){//学员本题答对了
                              //   html += '<img src="flower.png" alt="">';
                              // }
                            html += '</p>'+
                            '<div class="evaluation-particular-lis-chiose clearfix ">'+
                              '<div class="layui-input-block layui-input-left">';
                                for(m in res){
                                  // var str = res[m].replace(/<p>/gm,'');
                                  // var str1 = str.replace(/<\/p>/gm,'');
                                  var str1 = res[m].replace(/<\/?.+?>/g,"");
                                  var q_select = str1.substring(0,1).toLocaleUpperCase();
                                  if(sonList[i]['log_answer'] != null){
                                    var log_answer = sonList[i]['log_answer'].toLocaleUpperCase();
                                  }else{
                                    var log_answer ='';
                                  }
                                  if(q_select == log_answer){
                                    html+='<input type="radio" name="answer_'+newList[x].q_type+'_'+sonList[i]['q_id']+'" value="'+str1+'" title="'+str1+'" checked >';
                                  }else{
                                    html+='<input type="radio" name="answer_'+newList[x].q_type+'_'+sonList[i]['q_id']+'" value="'+str1+'" title="'+str1+'">';
                                  }
                                }
                              html+='</div>'+
                                  '<div class="layui-input-right">'+
                                    '<img src="'+sonList[i]['q_stem_img']+'" alt="">'+
                                  '</div>'+
                                '</div>';
                                if(sonList[i]['log_status'] == 1){//学员本题答对了
                                  html+= '<span class="evaluation-particular-tips-right">恭喜你，答对啦</span>';
                                }else if(sonList[i]['log_status'] == 2 || sonList[i]['log_status'] == 0){//学员本题答错了
                                  html+= '<span class="evaluation-particular-tips-right">太遗憾了</span>'+
                                            '<!-- evaluation-particular-tips-error -->'+
                                          '<div class="evaluation-particular-lis-answer">'+
                                            '解答：'+sonList[i].q_describe+
                                              '所以正确答案：'+sonList[i].q_answer+
                                          '</div>';
                                } 
                              html += '</div>';
                        }
                        html+='</li>';
                  }else if(newList[x].q_type == 2){ //多选题
                      html+='<li class="evaluation-particular-lis">'+
                        '<p class="evaluation-particular-lis-menu">'+
                          ''+bit[x]+'、【'+newList[x]['questype']+'】选择正确的答案'+
                        '</p>';
                        // <!-- 每一个模块下的列表 -->
                        for(i in sonList){
                          var regExp = /<p[^>]*>([\S\s]*?)<\/p>/gi;
                          res = sonList[i]['q_select'].match(regExp);
                          var k = parseInt(i)+1;
                          var q_stem = sonList[i]['q_stem'].replace(/<p>/gm,'');
                          var q_stem1 = q_stem.replace(/<\/p>/gm,'');
                          html+='<div class="evaluation-particular-lis-list  layui-form">'+
                            '<p class="evaluation-particular-lis-menu-title">'+
                              ''+k+'、'+q_stem1+''+
                              '<!-- 做对题给一个小红花  做错没有-->';
                              // if(sonList[i]['log_status'] == 1){//学员本题答对了
                              //   html += '<img src="flower.png" alt="">';
                              // }
                            html += '</p>'+
                            '<div class="evaluation-particular-lis-chiose clearfix ">'+
                              '<div class="layui-input-block layui-input-left">';
                                for(m in res){
                                  // var str = res[m].replace(/<p>/gm,'');
                                  // var str1 = str.replace(/<\/p>/gm,'');
                                  var str1 = res[m].replace(/<\/?.+?>/g,"");
                                  var q_select = str1.substring(0,1).toLocaleUpperCase();
                                  if(sonList[i]['log_answer'] != null){
                                    var log_answer = sonList[i]['log_answer'].toLocaleUpperCase();
                                  }else{
                                    var log_answer ='';
                                  }
                                  if(log_answer.search(q_select) != -1){
                                    html+='<div class="layui-input-block"><input type="checkbox" lay-skin="primary" name="answer_'+newList[x].q_type+'_'+sonList[i]['q_id']+'_[]" value="'+str1+'" title="'+str1+'" checked></div>';
                                  }else{
                                    html+='<div class="layui-input-block"><input type="checkbox" lay-skin="primary" name="answer_'+newList[x].q_type+'_'+sonList[i]['q_id']+'_[]" value="'+str1+'" title="'+str1+'" ></div>';
                                  }
                                }
                              html+='</div>'+
                                  '<div class="layui-input-right">'+
                                    '<img src="'+sonList[i]['q_stem_img']+'" alt="">'+
                                  '</div>'+
                                '</div>';
                                if(sonList[i]['log_status'] == 1){//学员本题答对了
                                  html+= '<span class="evaluation-particular-tips-right">恭喜你，答对啦</span>';
                                }else if(sonList[i]['log_status'] == 2 || sonList[i]['log_status'] == 0){//学员本题答错了
                                  html+= '<span class="evaluation-particular-tips-right">太遗憾了</span>'+
                                            '<!-- evaluation-particular-tips-error -->'+
                                          '<div class="evaluation-particular-lis-answer">'+
                                            '解答：'+sonList[i].q_describe+
                                              '所以正确答案：'+sonList[i].q_answer+
                                          '</div>';
                                } 
                              html += '</div>';
                        }
                        html+='</li>';
                  }else if(newList[x].q_type == 3){    //判断题
                      html+='<li class="evaluation-particular-lis">'+
                            '<p class="evaluation-particular-lis-menu">'+
                              ''+bit[x]+'、【'+newList[x]['questype']+'】选择正确的答案'+
                            '</p>';
                      for(i in sonList){
                          var k = parseInt(i)+1;
                          var q_stem = sonList[i]['q_stem'].replace(/<p>/gm,'');
                          var q_stem1 = q_stem.replace(/<\/p>/gm,'');
                          html+='<div class="evaluation-particular-lis-chiose layui-form clearfix">'+
                                '<div class="layui-input-block">'+
                                  '<div class="layui-input-blanks layui-input-yn">'+
                                    ''+k+'、'+q_stem1+'';
                                    if(sonList[i].log_answer == 'A'){
                                        html+='<input type="radio" name="answer_'+newList[x].q_type+'_'+sonList[i]['q_id']+'" value="是" title="是" checked>'+
                                            '<input type="radio" name="answer_'+newList[x].q_type+'_'+sonList[i]['q_id']+'" value="否" title="否">';
                                      }else{
                                        html+='<input type="radio" name="answer_'+newList[x].q_type+'_'+sonList[i]['q_id']+'" value="是" title="是">'+
                                            '<input type="radio" name="answer_'+newList[x].q_type+'_'+sonList[i]['q_id']+'" value="否" title="否" checked>';
                                      }
                                 html += '</div>'+
                                '</div>'+
                              '</div>'+
                              '<div class="evaluation-particular-lis-answer">';
                              if(sonList[i]['log_status'] == 1){//学员本题答对了
                                  html+= '<span class="evaluation-particular-tips-right">恭喜你，答对啦</span>';
                                }else if(sonList[i]['log_status'] == 2 || sonList[i]['log_status'] == 1){//学员本题答错了
                                  html+= '<span class="evaluation-particular-tips-right">太遗憾了</span>'+
                                            '<!-- evaluation-particular-tips-error -->'+
                                          '<div class="evaluation-particular-lis-answer">'+
                                            '解答：'+sonList[i].q_describe+
                                              '所以正确答案：'+sonList[i].q_answer+
                                          '</div>';
                                } 
                                // 解答：
                                // <p>解：因为，正方开$周长长X4，所以J正方形周长+边长=4.
                                //   答：正方形的周长是边长的4倍.
                                //   故选：A.</p>
                              html += '</div>';
                        }
                        html+='</li>';
                  }else if(newList[x].q_type == 5){   //填空题
                        html+='<li class="evaluation-particular-lis">'+
                              '<p class="evaluation-particular-lis-menu">'+
                                ''+bit[x]+'、【'+newList[x]['questype']+'】在空白处填写正确的答案'+
                              '</p>';
                        // <!-- 每一个模块下的列表 -->
                        for(i in sonList){
                          var k = parseInt(i)+1;
                          //替换p标签
                          var q_stem = sonList[i]['q_stem'].replace(/<p>/gm,'');
                          var q_stem1 = q_stem.replace(/<\/p>/gm,'');
                          //替换括号，换为可输入的input
                          var q_stem1 = q_stem1.replace(/（/gm,'(');    //将中文括号换位英文括号
                          var q_stem1 = q_stem1.replace(/）/gm,')');    //将中文括号换位英文括号
                          if(!sonList[i].log_answer){
                            var q_stem1 = q_stem1.replace(/(\([^\)]*\))/gm,'<input type="text" name="answer_'+newList[x].q_type+'_'+sonList[i]['q_id']+'_[]"  autocomplete="off" class="layui-input" value="">');  //将括号换位input框
                          }else{
                            var log_answer = sonList[i].log_answer.split(",");
                            for(m in log_answer){
                              var q_stem1 = q_stem1.replace(/(\([^\)]*\))/m,'<input type="text" name="answer_'+newList[x].q_type+'_'+sonList[i]['q_id']+'_[]"  autocomplete="off" class="layui-input" value="'+log_answer[m]+'">');  //将括号换位input框
                            }
                          }
                          html+='<div class="evaluation-particular-lis-chiose  layui-form clearfix">'+
                            '<div class="layui-input-block">'+
                              '<div class="layui-input-blanks">'+
                               ''+k+'、'+q_stem1+''+
                                '<!-- <img src="flower.png" alt="">    -->'+
                              '</div>'+
                            '</div>'+
                          '</div>'+
                          '<div class="evaluation-particular-lis-answer">';
                                if(sonList[i]['log_status'] == 1){//学员本题答对了
                                  html+= '<span class="evaluation-particular-tips-right">恭喜你，答对啦</span>';
                                }else if(sonList[i]['log_status'] == 2 || sonList[i]['log_status'] == 0){//学员本题答错了
                                  html+= '<span class="evaluation-particular-tips-right">太遗憾了</span>'+
                                            '<!-- evaluation-particular-tips-error -->'+
                                          '<div class="evaluation-particular-lis-answer">'+
                                            '解答：'+sonList[i].q_describe+
                                              '所以正确答案：'+sonList[i].q_answer+
                                          '</div>';
                                }
                            // '解答：'+
                            // '<p>解：因为，正方开$周长长X4，所以J正方形周长+边长=4.'+
                            // '答：正方形的周长是边长的4倍.'+
                            // '故选：A.</p>'+
                          html += '</div>';
                        }
                      html+='</li>';
                  }else if(newList[x].q_type == 6){  //问答题   、【应用题】
                      html+='<li class="evaluation-particular-lis">'+
                                '<p class="evaluation-particular-lis-menu">'+
                                  ''+bit[x]+'、【'+newList[x]['questype']+'】在空白处填写正确的答案'+
                                '</p>';
                      for(i in sonList){
                            var k = parseInt(i)+1;
                            //替换p标签
                            var q_stem = sonList[i]['q_stem'].replace(/<p>/gm,'');
                            var q_stem1 = q_stem.replace(/<\/p>/gm,'');
                            html+='<div class="evaluation-particular-lis-block">'+
                                  '<p class="evaluation-particular-lis-name">'+
                                  ''+k+'、'+q_stem1+'</p>'+
                                  '<div class="evaluation-particular-lis-upload clearfix">'+
                                    '<div class="evaluation-particular-lis-left test" id="test'+sort+'">'+
                                      '上传答案'+
                                      '<div class="evaluation-particular-lis-loadimg">+</div>'+
                                    '</div>'+
                                    '<div class="evaluation-particular-lis-img">'+
                                      '<img src="'+sonList[i]['q_stem_img']+'" alt="">'+
                                    '</div>'+
                                  '</div>'+
                                  '<div class="evaluation-particular-lis-img-show">'+
                                    '<div class="evaluation-particular-lis-img-close">+</div>'+
                                    '<img src="'+sonList[i]['log_answer']+'" alt="" id="demo'+sort+'">'+
                                    '<input type="hidden" name="answer_'+newList[x].q_type+'_'+sonList[i]['q_id']+'" value="" id="picUrl'+sort+'">'+
                                  '</div>';
                                  html += '<div class="evaluation-particular-lis-answer">';
                                  if(sonList[i]['log_status'] == 1){//学员本题答对了
                                    html+= '<span class="evaluation-particular-tips-right">恭喜你，答对啦</span>';
                                  }else if(sonList[i]['log_status'] == 2 || sonList[i]['log_status'] == 0){//学员本题答错了
                                    html+= '解答：'+sonList[i].q_describe+
                                            '所以正确答案：'+sonList[i].q_answer;
                                  }
                                html += '</div></div>';
                          sort++;
                      }
                      html+='</li>';
                  }
                }
                // if(newQuestionrowsList == ''){    //题帽题
                //   html+='<li class="evaluation-particular-lis">'+
                //                 '<p class="evaluation-particular-lis-menu">'+
                //                   ''+bit[count]+'、【题帽题】阅读下面的文章，回答问题'+
                //                 '</p>';
                //   html+='</li>';
                // }
                for(x in newQuestionrowsList){
                  if(newQuestionrowsList[x].qr_type == 7){   //完形填空
                      html += '<li class="evaluation-particular-lis">'+
                        '<p class="evaluation-particular-lis-menu">'+
                          ''+bit[count]+' 、【完形填空】完形填空。'+
                        '</p>'+
                        '<div class="evaluation-particular-lis-menu-content">'+newQuestionrowsList[x].qr_question+'</div>'+
                        
                        '<div class="evaluation-particular-lis-list">';
                        var sonList = newQuestionrowsList[x].son;
                            for(s in sonList){
                              var regExp = /<p[^>]*>([\S\s]*?)<\/p>/gi;
                              res = sonList[s]['q_select'].match(regExp);
                              var num=parseInt(s)+1;
                              html += '<div class="evaluation-particular-lis-block">'+
                                '<div class="evaluation-particular-lis-chiose evaluation-particular-lis-english clearfix">'+
                                  '<span>'+num+'.</span>';
                                  html += '<div class="layui-input-block layui-input-yn  layui-form" style="margin-left:0px">';
                                  for(m in res){
                                    // var str = res[m].replace(/<p>/gm,'');
                                    // var str1 = str.replace(/<\/p>/gm,'');
                                    var str1 = res[m].replace(/<\/?.+?>/g,"");
                                    var q_select = str1.substring(0,1).toLocaleUpperCase();
                                    if(sonList[s]['log_answer'] != null){
                                      var log_answer = sonList[s]['log_answer'].toLocaleUpperCase();
                                    }else{
                                      var log_answer ='';
                                    }

                                    if(q_select == log_answer){
                                      html+='<input type="radio" name="answer_'+newQuestionrowsList[x].qr_type+'_'+newQuestionrowsList[x].qr_id+'_'+sonList[s]['q_id']+'" value="'+str1+'" title="'+str1+'" checked>';
                                    }else{
                                      html+='<input type="radio" name="answer_'+newQuestionrowsList[x].qr_type+'_'+newQuestionrowsList[x].qr_id+'_'+sonList[s]['q_id']+'" value="'+str1+'" title="'+str1+'">';
                                    }
                                  }
                                  html += '</div>'+
                                '</div>'+
                              '</div>';
                            }
                          html += '<div class="evaluation-particular-lis-answer">';
                              if(sonList[s]['log_status'] == 1){//学员本题答对了
                                html+= '<span class="evaluation-particular-tips-right">恭喜你，答对啦</span>';
                              }else if(sonList[s]['log_status'] == 2 || sonList[s]['log_status'] == 0){//学员本题答错了
                                html+= '解答：'+sonList[s].q_describe+
                                        '所以正确答案：'+sonList[s].q_answer;
                              }
                            // 解答：
                            // <p>解：因为，正方开$周长长X4，所以J正方形周长+边长=4.
                            // 答：正方形的周长是边长的4倍.
                            // 故选：A.</p>
                          html += '</div>';
                        html +='</div>';
                      html +='</li>';
                      count++;
                  }else if(newQuestionrowsList[x].qr_type == 8){   //语文阅读理解
                      html += '<li class="evaluation-particular-lis">'+
                                  '<p class="evaluation-particular-lis-menu">'+
                                    ''+bit[count]+'、【阅读理解】</p>'+
                                  '<div class="evaluation-particular-lis-menu-content"> '+newQuestionrowsList[x].qr_question+'</div>'+
                                  '<div class="valuation-particular-lis-list clearfix">';
                                var sonList = newQuestionrowsList[x].son;
                                for(s in sonList){
                                  var num=parseInt(s)+1;
                                  html += '<div class="evaluation-particular-lis-block">'+num+'、'+sonList[s].q_stem+
                                            '<div class="evaluation-particular-lis-upload clearfix">'+
                                              '<div class="evaluation-particular-lis-left evaluation-particular-lis-img-show">'+
                                                '上传答案'+
                                                '<div class="evaluation-particular-lis-loadimg" id="test'+sort+'">+</div>'+
                                              '</div>'+
                                            '</div>'+
                                            '<div class="evaluation-particular-lis-img-show">'+
                                              '<div class="evaluation-particular-lis-img-close">+</div>'+
                                              '<img src="'+sonList[s]['log_answer']+'" alt="" id="demo'+sort+'">'+
                                              '<input type="hidden" name="answer_'+newQuestionrowsList[x].qr_type+'_'+newQuestionrowsList[x].qr_id+'_'+sonList[s]['q_id']+'" value="" id="picUrl'+sort+'">'+
                                            '</div>'+
                                          '</div>'+
                                           
                                            // '<div class="evaluation-particular-lis-solution">主要运用了动作描写。形象地表现了父亲当年挣钱的不易和对来之不易的收入的珍惜及兴奋满足之情。</div>'+
                                            '<div class="evaluation-particular-lis-answer">';
                                              if(sonList[s]['log_status'] == 1){//学员本题答对了
                                                html+= '<span class="evaluation-particular-tips-right">恭喜你，答对啦</span>';
                                              }else if(sonList[s]['log_status'] == 2 || sonList[s]['log_status'] == 0){//学员本题答错了
                                                html+= '解答：'+sonList[s].q_describe+
                                                        '所以正确答案：'+sonList[s].q_answer;
                                              }
                                              // 解答：
                                              // <p>解：因为，正方开$周长长X4，所以J正方形周长+边长=4.
                                              // 答：正方形的周长是边长的4倍.
                                              // 故选：A.</p>
                                            html +='</div>';
                                          // '</div>';
                                      sort++;
                                }
                                html += '</div>';
                      html += '</li>';
                      count++;
                  }else if(newQuestionrowsList[x].qr_type == 9){   //英语阅读理解
                      html += '<li class="evaluation-particular-lis">'+
                        '<p class="evaluation-particular-lis-menu">'+
                          ''+bit[count]+' 、【阅读理解】阅读短文，选择正确的答案。'+
                        '</p>'+
                        '<div class="evaluation-particular-lis-menu-content">'+newQuestionrowsList[x].qr_question+'</div>'+

                        '<div class="evaluation-particular-lis-list">';
                        var sonList = newQuestionrowsList[x].son;
                        for(s in sonList){
                          var regExp = /<p[^>]*>([\S\s]*?)<\/p>/gi;
                          res = sonList[s]['q_select'].match(regExp);
                          var num=parseInt(s)+1;
                          html += '<div class="evaluation-particular-lis-block  layui-form">'+
                            '<div class="evaluation-particular-lis-menu-title">'+num+'、'+sonList[s].q_stem+'</div>'+
                            '<div class="evaluation-particular-lis-chiose clearfix">'+
                              '<div class="layui-input-block">';
                              for(m in res){
                                // var str = res[m].replace(/<p>/gm,'');
                                // var str1 = str.replace(/<\/p>/gm,'');
                                var str1 = res[m].replace(/<\/?.+?>/g,"");
                                var q_select = str1.substring(0,1).toLocaleUpperCase();
                                if(sonList[s]['log_answer'] != null){
                                  var log_answer = sonList[s]['log_answer'].substring(0,1).toLocaleUpperCase();
                                }else{
                                  var log_answer ='';
                                }
                                if(q_select == log_answer){
                                  html+='<input type="radio" name="answer_'+newQuestionrowsList[x].qr_type+'_'+newQuestionrowsList[x].qr_id+'_'+sonList[s]['q_id']+'" value="'+str1+'" title="'+str1+'" checked>';
                                }else{
                                  html+='<input type="radio" name="answer_'+newQuestionrowsList[x].qr_type+'_'+newQuestionrowsList[x].qr_id+'_'+sonList[s]['q_id']+'" value="'+str1+'" title="'+str1+'">';
                                }
                              }
                              html +='</div>'+
                            '</div>'+
                          '</div>';
                        }
                          html +='<div class="evaluation-particular-lis-answer">';
                                if(sonList[s]['log_status'] == 1){//学员本题答对了
                                  html+= '<span class="evaluation-particular-tips-right">恭喜你，答对啦</span>';
                                }else if(sonList[s]['log_status'] == 2 || sonList[s]['log_status'] == 0){//学员本题答错了
                                  html+= '解答：'+sonList[s].q_describe+
                                          '所以正确答案：'+sonList[s].q_answer;
                                }
                            // 解答：
                            // <p>解：因为，正方开$周长长X4，所以J正方形周长+边长=4.
                            // 答：正方形的周长是边长的4倍.
                            // 故选：A.</p>
                          html += '</div>';
                        html += '</div>';
                      html += '</li>';  
                      count++;     
                  }

                }
               if(e.data.log_fraction == null){
                  html+='</ul><input hidden name="id" value="'+id+'"><div class="evaluation-particular-submit" id="formDemo" lay-submit lay-filter="formDemo">提交</div></div>';
               }
                $("#paperList").hide();
                // $('#myTestPaperPage').hide();   
                $('#evaluation-particular').show(); 
                $('#paperInfo').html(html);
                form.render();
                for(var x=0;x<=sort;x++){
                    picUpload("#test" + x + "", "#demo" + x + "","#picUrl"+x+"")
                }   
              }else{
                alert('该试卷无题目');
              }
          }
  })
function picUpload(id,pic,picUrl){
    upload.render({
      elem: id
      ,url: '/index/person/uploadQuestAnswer' //改成您自己的上传接口
      ,multiple: true
      // ,before: function(obj){
      //   //预读本地文件示例，不支持ie8
      //   obj.preview(function(index, file, result){
      //     $(pic).attr('src', result); //图片链接（base64）
      //     $(picUrl).attr('value',result)
      //   });
      // }
      ,done: function(res){
        //如果上传失败
        if(res.code == 1){
          return layer.msg('上传失败!请稍后在上传');
        }else{
          //多图片上传
          // if($(picUrl).val() != ''){
          //    var dataArr = $(picUrl).val();
          // }else{
          //   var dataArr = '';
          // }
          // dataArr += ','+res.data.src;
          // $(pic).after('<div class="evaluation-particular-lis-img-close">+</div><img src="'+res.data.src+'" alt="" id="'+pic+'">')  //图片链接
          // $(picUrl).attr('value',dataArr);  //input hidden
          // $(".evaluation-particular-lis-img-close").show()
          //单图片上传
          // if($(picUrl).val() != ''){
          //    var dataArr = $(picUrl).val();
          // }
          var dataArr =res.data.src;
          $(pic).attr('src',dataArr)  //图片链接
          $(picUrl).attr('value',dataArr);  //input hidden
          $(".evaluation-particular-lis-img-close").show()
        }
      }
    });
}

// 上传的图片删除
$(document).on('click','.evaluation-particular-lis-img-close',function() {
  // console.log($(this).siblings())
  
})
     
 // 试卷倒计时 
  function showTime(){  
    //剩余天数 
    var day = parseInt(time/3600/24); 
    //剩余的小时数 
    var hour = parseInt( (time-day*3600*24) /3600) ; 
    //剩余的分钟数 
    var mimute = parseInt( (time - hour*3600-day*3600*24) / 60 ) ; 
    //剩余的秒数 
    var seconds = parseInt( time - hour*3600-day*3600*24 - mimute * 60 );   
    if (hour<10){
      hour="0"+hour;
    }
    if (mimute<10){
      mimute="0"+mimute;
    }
    if (seconds<10){
      seconds="0"+seconds;
    }
    $(".error-endtime span").text(hour+":"+mimute+":"+seconds)
  }
   function  setTimer(){
    var timer = setInterval(function (){ 
      time--; 
      if( time <= 0 ){ 
        $(".error-endtime span").text('00:00:00')
        clearInterval(timer); 
        layer.confirm('时间到，考试结束啦！', {
          btn: ['提交', '退出'] //可以无限个按钮
        }, function(index, layero){
          //提交
          $("#formDemo").click()
        }, function(index){
          //退出

        });
      }else{ 
              showTime(); 
      } 
    } , 1000)
   }
    $('.personal-tab-left-lis:eq(5)').click(function() {
    clearInterval(timer); 
  });
  // 2020-02-10王莹 试卷
// 试卷关闭
    $(document).on('click','.evaluation-particular-close',function() {
      $("#paperList").show();
      $('#evaluation-particular').hide(); 
      $('#paperInfo').html( );
    })
  })
//2020-03-20 提交试卷
  form.on('submit(formDemo)', function(data){
    // layer.msg(JSON.stringify(data.field));
    // return false;
    var datas = JSON.stringify(data.field);
    // console.log(datas);
    $.post('/index/person/questionSubmit',{data:datas},function(res){
      if(res.error_code == 1){
        layer.msg("提交成功",{time:1500,icon:6},function(){
            // layer.close(index);
            form.render();
        });
      }else{
        layer.msg("提交失败",{time:1500,icon:5},function(){
            // layer.close(index);
            form.render();
        });
      }
    })
  });

})     //layui 结束

// 考试时间浮层
$(window).scroll(function(){
  var htmlTop = $(document).scrollTop();
  // console.log(htmlTop)
  if( htmlTop > 400){
      $(".error-endtime").addClass("error-endtime-fixed");    
  }else{
    $(".error-endtime").removeClass("error-endtime-fixed");    
  }
});

