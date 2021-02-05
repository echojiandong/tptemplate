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
var id=$('#view_id').val();
    table.render({
        elem: '#tests'
        ,url:'/manage/review_contorller/getpartlist?pid='+id
        ,height:632
        ,cols: [[
            {field:'id',title:'课时ID'}
            ,{field:'testclass',title:'课时数'}
            ,{field:'outline', title: '课时名称'}
            // ,{field:'teachername',title:'授课教师'}
            // ,{field:'classhour',title:'课时时长'}
            // ,{field:'audi',title:'是否试听'}
            ,{field:'pid',title: '所属【PID】',}
            // ,{field:'time',title: '添加日期',}
            ,{fixed: 'right',  toolbar: '#barDemo',title: '操作',}
        ]]
        ,page: true
    });

    // 搜索
    $('#sousuo').on('click', function(){
        // var type = $(this).data('type');
        var demoReload = $('#demoReload').val();
        if(!demoReload){
            layer.msg("查询内容不能为空",{time:1000});
            return false;
        }else{
            //请求接口根据用户搜索查找匹配项
            table.render({
                elem: '#tests'
                ,url:'/manage/review_contorller/getpartlist?keyword='+demoReload
                ,height:'full'
                ,cols: [[
                    {field:'id',title:'课时ID'}
                    ,{field:'testclass',title:'课时数'}
                    ,{field:'outline', title: '课时名称'}
                    // ,{field:'teachername',title:'授课教师'}
                    // ,{field:'classhour',title:'课时时长'}
                    // ,{field:'audi',title:'是否试听'}
                    ,{field:'pid',title: '所属【PID】',}
                    // ,{field:'time',title: '添加日期',}
                    ,{fixed: 'right',  toolbar: '#barDemo',title: '操作',}
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
        if(obj.event === 'show'){
            layer.open({
                type: 2,
                title:"知识点列表",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/review_contorller/secknowledge?id="+data.id
            });
        } else if(obj.event === 'edit'){
            layer.open({
                type: 2,
                title:"块课时编辑",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/review_contorller/updatesection?id="+data.id
            });
        }else if(obj.event==='del'){
                layer.confirm('真的删除么？', function(index){
                $.post("/manage/review_contorller/delknowdege",{id:data.id},function(data){
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
                title:"知识点添加",
                skin: 'layui-layer-molv', //加上边框
                area: ['100%','100%'], //宽高
                content: "/manage/review_contorller/addknowledge?id="+id,
                end:function(){
                    window.location.reload();
                }
            });
        },
        //添加课时
        addsec: function(){
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
        }

    };

})
