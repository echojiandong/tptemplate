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

    table.render({
        elem: '#test'
        ,url:'/manage/course_contorller/getcourseList'
        ,height:332
        ,cols: [[
            {field:'name', title: '课程名称'}
            ,{field:'sname', title: '讲师'}
            ,{field:'subject', title: '科目'}
            ,{field:'grade', title: '年级'}
            ,{field:'price', title: '课程价格'}
            ,{field:'updatetime', title: '更新时间'}
            ,{fixed: 'right', width: 165, align:'center', toolbar: '#barDemo'}
        ]]
        ,page: true
    });
  $('#sousuo').on('click', function(){
           var type = $(this).data('type');
            var demoReload = $('#demoReload');
            table.reload('testReload', {
                where: {
                    keyword: demoReload.val()
                }
            });      
   });
    laytpl.toDateString = function(nS){
        return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');
    };

    table.on('tool(demo)', function(obj){
        var data = obj.data;
        if(obj.event === 'courseshow'){
            layer.open({
                type: 2,
                title:"课程预览",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/course_contorller/courseshow?id="+data.id
            });
        } else if(obj.event === 'edit'){
            layer.open({
                type: 2,
                title:"编辑课程",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/course_contorller/courseedit?id="+data.id
            });
        }else if(obj.event==='del'){
                layer.confirm('真的删除行么', function(index){
                $.post("/manage/course_contorller/coursedel",{id:data.id},function(data){
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
        }
    });


    $('.demoTable .layui-btn').on('click', function(){
        var type = $(this).data('type');
        active[type] ? active[type].call(this) : '';
    });

    active = {
        addData: function(){
            layer.open({
                type:2,
                title:"添加课程",
                skin: 'layui-layer-molv', //加上边框
                area: ['100%','100%'], //宽高
                content: "/manage/course_contorller/courseAdd",
                end:function(){
                    window.location.reload();
                }
            });
        }
    };

})
