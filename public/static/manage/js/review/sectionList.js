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

// var id=$('#pid').val();//原来的
var id=$('#section_id').val();
    table.render({
        elem: '#tests'
        ,url:'/manage/review_contorller/sectionlist?pid='+id
        ,height:632
        ,cols: [[
            {field:'id',title:'课时ID'}
            ,{field:'outline', title: '课程名称/块名称'}
            ,{field:'teachername', title: '授课教师'}
            ,{field:'classhour',title:'课时时长'}
            ,{field:'audi',title:'是否试听'}
            ,{field:'reviewName', title: '对应章节名称'}
            ,{field:'part',title:'类型'}
            ,{field:'time',title: '添加日期'}
            ,{fixed: 'right',  toolbar: '#barDemo',title: '操作',width:200}
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
        if(obj.event === 'part'){
            layer.open({
                type: 2,
                title:"块课时/知识点",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/review_contorller/partlist?id="+data.id
            });
        } else if(obj.event === 'edit'){//编辑
            if(data.part == '课时'){
                layer.open({
                    type: 2,
                    title:"课程编辑",
                    skin: 'layui-layer-demo', //样式类名
                    closeBtn: 1, //不显示关闭按钮
                    anim: 2,
                    area: ['100%', '95%'],
                    shadeClose: true, //开启遮罩关闭
                    maxmin: true,
                    content: "/manage/review_contorller/updatesection?id="+data.id
                });
            }else if(data.part == '课时块'){
                layer.open({
                    type: 2,
                    title:"课程编辑",
                    skin: 'layui-layer-demo', //样式类名
                    closeBtn: 1, //不显示关闭按钮
                    anim: 2,
                    area: ['100%', '95%'],
                    shadeClose: true, //开启遮罩关闭
                    maxmin: true,
                    content: "/manage/review_contorller/updatepart?id="+data.id
                });
            }

        }else if(obj.event === 'review'){
            layer.open({
                type: 2,
                title:"课时管理",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/review_contorller/getsctionlist?id="+data.id});
        }else if(obj.event === 'delsec'){//课时删除
                layer.confirm('真的删除行么', function(index){
                $.post("/manage/review_contorller/delsection",{id:data.id},function(data){
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
        addsection: function(){
            layer.open({
                type:2,
                title:"课时添加",
                skin: 'layui-layer-molv', //加上边框
                area: ['100%','100%'], //宽高
                content: "/manage/review_contorller/addsection?id="+id,
                end:function(){
                    window.location.reload();
                }
            });
        },

        //课时小模块添加
        addPart: function(){
            layer.open({
                type:2,
                title:"课时小块添加",
                skin: 'layui-layer-molv', //加上边框
                area: ['100%','100%'], //宽高
                content: "/manage/review_contorller/addpart?id="+id,
                end:function(){
                    window.location.reload();
                }
            });
        }

    };

})
