layui.use(['laydate', 'laypage', 'layer', 'table', 'carousel', 'upload', 'element'], function(){
    var $ = layui.$, form = layui.form;
    laytpl = layui.laytpl;

    var laydate = layui.laydate //日期
  ,laypage = layui.laypage //分页
  layer = layui.layer //弹层
  ,table = layui.table //表格
  ,carousel = layui.carousel //轮播
  ,upload = layui.upload //上传
  ,element = layui.element; //元素操作

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

    table.render({
        elem: '#test'
        ,url:'/manage/recycle_contorller/getRecyclecTestPaper'
        ,height:'full'
        ,cellMinWidth:80
        ,cols: [[
             {type: 'checkbox', fixed: 'left'}
            ,{field:'id', title: 'ID'}
            ,{field:'title', title: '试卷名称'}
            ,{field:'type_name', title: '试卷类型'}
            ,{field:'grade_id', title: '年级' ,templet:'#grade'} 
            ,{field:'subject_id', title: '科目' ,templet:'#subject'} 
            ,{field:'semester_name', title: '学期'}
            ,{field:'score', title: '总分'}
            ,{field:'addTime', title: '添加时间' ,templet:'#timestamp'}
            ,{field:'lock',  title: '操作', width:250, toolbar: '#barDemo'}
        ]]
        ,page: true
    });
    $('#sousuo').on('click', function(){
            //var type = $(this).data('type');
            var demoReload = $('#demoReload').val();
            var type = $('#type').val();
            if(!demoReload && !type){
                //当用户没有输入内容直接点击查询的时候提示
                layer.msg("查询内容不能为空",{time:1000});
                return false;
            }else{
                //请求接口根据用户搜索查找卡号
                table.render({
                    elem: '#test'
                    ,url:'/manage/recycle_contorller/getRecyclecTestPaper?keyword='+demoReload+'&type='+type
                    ,height:'full'
                    ,cellMinWidth:80
                    ,cols: [[
                         {type: 'checkbox', fixed: 'left'}
                        ,{field:'id', title: 'ID'}
                        ,{field:'title', title: '试卷名称'}
                        ,{field:'type_name', title: '试卷类型'}
                        ,{field:'grade_id', title: '年级' ,templet:'#grade'} 
                        ,{field:'subject_id', title: '科目' ,templet:'#subject'} 
                        ,{field:'semester_name', title: '学期'}
                        ,{field:'score', title: '总分'}
                        ,{field:'addTime', title: '添加时间' ,templet:'#timestamp'}
                        ,{field:'lock',  title: '操作', width:250, toolbar: '#barDemo'}
                    ]]
                    ,page: true
                });
            }   
   });
    laytpl.toDateString = function(nS){
        return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');
    };

    table.on('tool(demo)', function(obj){
        var data = obj.data;
        if(obj.event === 'forbidden'){
            layer.confirm('真的删除吗？', function(index){
                $.post("/manage/recycle_contorller/delTestPaper",{id:data.id},function(data){
                    if(data.error_code==0){
                        layer.msg("删除成功",{time:400},function(){
                            obj.del();
                            layer.close(index);
                        });
                    }else{
                        layer.msg("删除失败",{time:400});
                    }
                })
            });
        }else if(obj.event==='editPaper'){
            layer.confirm('真的还原吗？', function(index){
                $.post("/manage/recycle_contorller/restoreTestPaper",{id:data.id},function(data){
                    if(data.error_code==0){
                        layer.msg("还原成功",{time:400},function(){
                            obj.del();
                            layer.close(index);
                        });

                    }else{
                        layer.msg("还原失败",{time:400});
                    }
                })

            });
        }else if(obj.event==='showPaper'){
            layer.open({
                type: 2,
                title:"查看试卷",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/testpaper_controller/showPaper?id="+data.id
            });
        }
    });


    $('.demoTable .layui-btn').on('click', function(){
        var type = $(this).data('type');
        active[type] ? active[type].call(this) : '';
    });

    form.on('submit(editCode)',function(){
        if(!$('input').val()){
            layer.msg('信息不完整，无法提交', {icon: 5});
            return false;
        }
        console.log(111)
        $.post("/manage/recycle_contorller/restore",
            {
                status:$("[name='status']:checked").val(),
                id:$("[name='id']").val(),
            },
            function(data){
                if(data.error_code==0){
                    layer.msg('修改成功', {icon: 6,time:1500},function(){
                        window.parent.location.reload();//刷新父页面
                    });
                }else{
                    layer.msg('修改失败', {icon: 5,time:1500});
                }
            });
    })
    active = {
        addData: function(){
            layer.open({
                type:2,
                title:"添加直播课程",
                skin: 'layui-layer-molv', //加上边框
                area: ['100%','100%'], //宽高
                content: "/manage/live_contorller/liveAdd",
                end:function(){
                    window.location.reload();
                }
            });
        }
    };

})
