layui.use(['laydate', 'laypage', 'layer', 'table', 'carousel', 'upload', 'element'], function(){
    var $ = layui.$, form = layui.form;
    laytpl = layui.laytpl;

    var laydate = layui.laydate //日期
  ,laypage = layui.laypage //分页
  ,layer = layui.layer //弹层
  ,table = layui.table //表格
  ,carousel = layui.carousel //轮播
  ,upload = layui.upload //上传
  ,element = layui.element; //元素操作
  // layui.use('form',function(){});
    laytpl.toGrade=function(ns){
        if(ns==1){
           return '一年级';
        }else if(ns==2){
           return '二年级';
        }else if(ns==3){
           return '三年级';
        }else if(ns==4){
            return '四年级';
        }else if(ns==5){
           return '五年级';
        }else if(ns==6){
           return '六年级';
        }else if(ns==7){
            return '七年级';
        }else if(ns==8){
           return '八年级';
        }else if(ns==9){
           return '九年级';
        }else if(ns==10){
            return '高一';
        }else if(ns==11){
           return '高二';
        }else if(ns==12){
           return '高三';
        }
    };
    laytpl.toSubject=function(ns){
        if(ns==1){
           return '语文';
       }else if(ns==2){
           return '数学';
       }else if(ns==3){
           return '英语';
       }else if(ns==4){
           return '物理';
       }else if(ns==5){
           return '化学';
       }else if(ns==6){
           return '政治';
       }else if(ns==7){
           return '历史';
       }else if(ns==8){
           return '地理';
       }else if(ns==9){
           return '生物';
       }
    };
    //页面加载渲染数据
      table.render({
          elem: '#test'
          ,url:'/manage/studentPaper_controller/getTestPaperList'
          ,height:'full'
          ,cellMinWidth:80
          ,cols: [[
               {type: 'checkbox', fixed: 'left'}
              ,{field:'id', title: 'ID'}
              ,{field:'nickName', title: '学员姓名'}
              ,{field:'phone', title: '手机号'}
              ,{field:'title', title: '试卷名称'}
              ,{field:'type_name', title: '试卷类型'}
              ,{field:'grade_id', title: '年级' ,templet:'#grade'} 
              ,{field:'subject_id', title: '科目' ,templet:'#subject'} 
              ,{field:'semester_name', title: '学期'}
              ,{field:'score', title: '总分'}
              ,{field:'fraction', title: '学员得分'}
              ,{field:'intime', title: '添加时间' ,templet:'#timestamp'}
              ,{field:'review', title: '评改状态'}
              ,{field:'lock',  title: '操作', width:250, toolbar: '#barDemo'}
          ]]
          ,id:'testReload'
          ,page:true
      });
    var grade_id='',subject_id='',semester='',type='',reviewState='',t_id='';
    //所属年级  科目 学期 类型与条件试卷进行联动
    form.on('select', function(data){
      var selectid = data.elem.id;
      var val = data.value;
      if(selectid == 'grade_id'){//所属年级
        grade_id = val;
      }else if(selectid == 'subject_id'){//所属科目
        subject_id = val;
      }else if(selectid == 'semester'){//所属学期
        semester = val;
      }else if(selectid == 'type'){//试卷类型
        type = val;
      }else if(selectid == 'review'){
        reviewState = val;
      }else if(selectid == 't_id'){
        t_id =val;
      }
      if(selectid != 't_id'){
        //根据条件请求接口分别获取试卷列表与学员提交试卷列表
        $.post('/manage/studentPaper_controller/getstudentTestPaperList',{grade_id:grade_id,subject_id:subject_id,semester:semester,type:type,reviewState:reviewState,t_id:t_id},function(res){
            var getTestPaperList = res.data.getTestPaperList;
            var html = "";
            html += '<select name="testPaper_id" class="newsLook" lay-verify="type" lay-search id="t_id">'+
                    '<option value="0">全部</option>';
            for(i in getTestPaperList){
                html+='<option value="'+getTestPaperList[i].id+'">'+getTestPaperList[i].title+'</option>';
            }
            html +='</select>';
            $('#testPaper_id').html(html);
            form.render('select');
        })
      }
      table.render({
          elem: '#test'
          ,url:'/manage/studentPaper_controller/getTestPaperList?grade_id='+grade_id+'&subject_id='+subject_id+'&semester='+semester+'&type='+type+'&reviewState='+reviewState+'&t_id='+t_id
          ,height:'full'
          ,cellMinWidth:80
          ,cols: [[
               {type: 'checkbox', fixed: 'left'}
              ,{field:'id', title: 'ID'}
              ,{field:'nickName', title: '学员姓名'}
              ,{field:'phone', title: '手机号'}
              ,{field:'title', title: '试卷名称'}
              ,{field:'type_name', title: '试卷类型'}
              ,{field:'grade_id', title: '年级' ,templet:'#grade'} 
              ,{field:'subject_id', title: '科目' ,templet:'#subject'} 
              ,{field:'semester_name', title: '学期'}
              ,{field:'score', title: '总分'}
              ,{field:'fraction', title: '学员得分'}
              ,{field:'intime', title: '添加时间' ,templet:'#timestamp'}
              ,{field:'review', title: '评改状态'}
              ,{field:'lock',  title: '操作', width:250, toolbar: '#barDemo'}
          ]]
          ,page:true
      });
    });
  //点击搜索查找数据
  $('#sousuo').on('click', function(){
            var taperName = $('#taperName').val();
            var userPhone = $('#userPhone').val();
            if(!grade_id && !subject_id && !semester && !type && !reviewState && !taperName && !userPhone){
                //当用户没有输入内容直接点击查询的时候提示
                layer.msg("查询内容不能为空",{time:1000});
                return false;
            }else{
                //请求接口根据用户搜索查找卡号
                table.reload('testReload', {
                  where: {
                      grade_id: grade_id
                      ,subject_id:subject_id
                      ,semester:semester
                      ,type:type
                      ,reviewState:reviewState
                      ,title:taperName
                      ,phone:userPhone
                      ,t_id:t_id
                  }
                });
                return false;
            }   
    });
    laytpl.toDateString = function(nS){
        return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');
    };

    table.on('tool(demo)', function(obj){
        var data = obj.data;
        var count = obj.count;
        if(obj.event === 'reviewState'){
          if(data.type == 1){
            layer.open({
                type: 2,
                title:"评阅学员 "+data.nickName+" "+data.phone+" 试卷",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/studentPaper_controller/reviewState?id="+data.id+"&person_id="+data.person_id
            });
          }
        }
    });
    // $('.demoTable .layui-btn').on('click', function(){
    //     var type = $(this).data('type');
    //     active[type] ? active[type].call(this) : '';
    // });
})
