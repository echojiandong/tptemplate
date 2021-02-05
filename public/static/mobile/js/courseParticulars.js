const scroll = new BScroll('.wrapper',{
    scrollX: true,
    eventPassthrough:'vertical',
    probeType: 3
  })
  // 我的课程横向滚动条
  $(".course-nav-item .course-nav-list").on('click','.nav-item',function() {
    $(".nav-item").removeClass("nav-item-current")
    $(this).addClass("nav-item-current")
    var subject_id = $(this).attr('data-id');
    var seme_id = $('.course-change-current').attr('data-val');
    subject_list(subject_id,seme_id);
  })
$(".select-delect-cycle").click(function() {
  $(this).addClass("cycle-current")
})
//课程目录中点击后出现便签
  $('.particulars-container-lis-title').click(function(){
    $(this).addClass("personal-learn-now")
         .parents(".particulars-container-lis-div")
         .siblings(".particulars-container-position-contain")
         .toggle()
         .end()
         .parent(".personal-course-container-lis")
         .siblings()
         .find(".particulars-container-lis-title")
         .removeClass("personal-learn-now")
  })

// 课程目录便签部分
  //  点击便签事件
    $('.iconbianqian-').click(function(){
    $(".particulars-contain-note-fixed").show()
                                        .children(".particulars-contain-note")
                                        .show()
                                        .children(".particulars-note-close")
                                        .show()
  })
  $(".particulars-note-close").click(function() {
    $(".particulars-note-close").hide()
                                .parent(".particulars-contain-note")
                                .hide()
                                .parent(".particulars-contain-note-fixed")
                                .hide()
  })
 // 便签滚动条


//  课程目录里面的知识点点击事件
  $('.particulars-position-container-lis').click(function(){
  $(this).addClass("personal-learn-now").siblings().removeClass("personal-learn-now")
})

  // 点击筛选按钮左滑出现页面
  $(".my-course-select").click(function() {
    $(".course-list-modal").animate({left:"0"},800)
  })
  $(".course-list-div").click(function() {
    $(".course-list-modal").animate({left:"100%"},800)
  })
  $(".course-change-lis").click(function() {
    $(this).addClass("course-change-current")
           .siblings()
           .removeClass("course-change-current")

  })

  $(".iscroll-wrapper").mCustomScrollbar();
  $('.course-change-confirm').click(function(){
      let seme_id = $('.course-change-item .course-change-current').attr('data-val');
      let subject_id = $('.nav-item-current').attr('data-id');
      $(".course-list-div").click()
      // console.log(seme_id);
      // console.log(subject_id);
      subject_list(subject_id,seme_id);
  })
  function subject_list(subject_id,seme_id){
    //myCourse
    $.ajax({
      url: '/index/person/myCourse'
      ,data: {subject_id:subject_id,seme_id:seme_id}
      ,dataType: 'json'
      ,type: 'post'
      ,success:function(res){
          $('.center-course-list-num').html('课程节数 '+res.data.counts+' 节');
          if(res.data.data_arr[0] == undefined){
              $('.course-list-content').empty().html('暂无数据');
          }
          if(res.data.data_arr[0] != undefined){
            $('.course-list-content').empty()
              $.each(res.data.data_arr[0],function(i,v){
                  var active_1 = i == 0?'mui-active':'';
                  var li = $('<li class="mui-table-view-cell mui-collapse course-list-unit '+active_1+'"></li>');
                  li.append('<a class="mui-navigate-right" href="#">'+v.testclass+'</a>');
                  var div = $('<div class="mui-collapse-content"></div>');
                  var dl = $('<dl class="personal-course-container clearfix"></dl>');
                  var dd = $('<dd class="personal-course-container-lis current-now clearfix"></dd>');
                  $.each(v.treelist,function(i_1,v_1){
                    
                    if(v_1.treelist != undefined && v_1.part == 1){
                        dl.append('<dt class="personal-course-container-title">'+v_1.testclass+'</dt>');
                        $.each(v_1.treelist,function(i_2,v_2){
                            var div_1 = $('<div class="particulars-container-lis-div clearfix"></div>');
                            div_1.append('<div class="particulars-container-lis-name"><span class="particulars-container-lis-title"><i class="iconfont icon-bofang11"></i>'+v_2.testclass+v_2.outline+'</span><i class="iconfont iconbianqian-"></i></div>');
                            var div_2 = $('<div class="particulars-contain-note"></div>');
                            div_2.append('<div class="particulars-note-close">x</div>');
                            var div_3 = $('<div class="particulars-container-note iscroll-wrapper"></div>');
                            div_3.append('<h3 class="particulars-study-note">学习便签</h3>');
                            if(v_2.treelist_1 != undefined){
                                var dl_1 = $('<dl class="particulars-note-list"></dl>')
                                $.each(v_2.treelist_1,function(i_3,v_3){
                                    dl_1.append(' <dt class="particulars-note-lis">'+v_3.title+'</dt><dd '+v_3.content+'</dd>');
                                })
                                div_3.append(dl_1);
                            }
                            div_2.append(div_3);
                            div_1.append(div_2);
                            var study_status = v_2.study_num != undefined && v_2.study_num !=0?'已学':'未学';
                            div_1.append('<span class="personal-container-lis-frequency">'+study_status+'</span>');
                            dd.append(div_1);
                            if(v_2.treelist != undefined){
                                var ul_1 = $('<ul class="particulars-container-position-contain clearfix"></ul>');
                                $.each(v_2.treelist,function(i_4,v_4){
                                  ul_1.append('<li class="particulars-position-container-lis"><i class="iconfont iconlocation"></i>'+v_4.k_name+'</li>');
                                })
                                dd.append(ul_1);
                            }
                            dl.append(dd);
                            dd = $('<dd class="personal-course-container-lis current-now clearfix"></dd>');
                        })
                        div.append(dl);
                        dl = $('<dl class="personal-course-container clearfix"></dl>')
                    }else{
                        // var dd = $('<dd class="personal-course-container-lis current-now clearfix"></dd>');
                        var div_1 = $('<div class="particulars-container-lis-div clearfix"></div>');
                        div_1.append('<div class="particulars-container-lis-name"><span class="particulars-container-lis-title"><i class="iconfont icon-bofang11"></i>'+v_1.testclass+v_1.outline+'</span><i class="iconfont iconbianqian-"></i></div>');
                        var div_2 = $('<div class="particulars-contain-note"></div>');
                        div_2.append('<div class="particulars-note-close">x</div>');
                        var div_3 = $('<div class="particulars-container-note iscroll-wrapper"></div>');
                        div_3.append('<h3 class="particulars-study-note">学习便签</h3>');
                        if(v_1.treelist_1 != undefined){
                            var dl_1 = $('<dl class="particulars-note-list"></dl>')
                            $.each(v_1.treelist_1,function(i_3,v_3){
                                dl_1.append(' <dt class="particulars-note-lis">'+v_3.title+'</dt><dd '+v_3.content+'</dd>');
                            })
                            div_3.append(dl_1);
                        }
                        div_2.append(div_3);
                        div_1.append(div_2);
                        var study_status = v_1.study_num != undefined && v_1.study_num !=0?'已学':'未学';
                        div_1.append('<span class="personal-container-lis-frequency">'+study_status+'</span>');
                        dd.append(div_1);
                        if(v_1.treelist != undefined){
                            var ul_1 = $('<ul class="particulars-container-position-contain clearfix"></ul>');
                            $.each(v_1.treelist,function(i_4,v_4){
                              ul_1.append('<li class="particulars-position-container-lis"><i class="iconfont iconlocation"></i>'+v_4.k_name+'</li>');
                            })
                            dd.append(ul_1);
                        }
                        dl.append(dd);
                        dd = $('<dd class="personal-course-container-lis current-now clearfix"></dd>');
                    }
                    div.append(dl);
                  })
                    li.append(div);
                  $('.mui-table-view').append(li);
              })
          }
      }
    })
  }