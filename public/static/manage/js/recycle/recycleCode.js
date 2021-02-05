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
        ,url:'/manage/recycle_contorller/getRecyclecCode'
        ,height:'full'
        ,cols: [[
             {field:'code_sort', title: '序列'}
            ,{field:'card', title: '卡号'}
            ,{field:'password', title: '密码'}
            ,{field:'status_name', title: '状态'}
            ,{field:'user_name', title: '所属人员'}
            ,{field:'create_time', title: '生成时间'}
            ,{field:'create_user_name', title: '生成人员'}
            ,{field:'lock',  title: '操作', width:330, toolbar: '#barDemo'}
        ]]
        ,page: true
    });
    $('#sousuo').on('click', function(){
            //var type = $(this).data('type');
            var demoReload = $('#demoReload').val();
            if(!demoReload){
                //当用户没有输入内容直接点击查询的时候提示
                layer.msg("查询内容不能为空",{time:1000});
                return false;
            }else{
                //请求接口根据用户搜索查找卡号
                table.render({
                    elem: '#test'
                    ,url:'/manage/recycle_contorller/getRecyclecCode?keyword='+demoReload
                    ,height:'full'
                    ,cols: [[
                         {field:'code_sort', title: '序列'}
                        ,{field:'card', title: '卡号'}
                        ,{field:'password', title: '密码'}
                        ,{field:'status_name', title: '状态'}
                        ,{field:'agency_name', title: '代理人员'}
                        ,{field:'service_name', title: '客服人员'}
                        ,{field:'create_time', title: '生成时间'}
                        ,{field:'create_user_name', title: '生成人员'}
                        ,{field:'lock',  title: '操作', width:330, toolbar: '#barDemo'}
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
        if(obj.event === 'delCode'){
            layer.confirm('真的删除吗？', function(index){
                $.post("/manage/recycle_contorller/delCode",{id:data.id},function(data){
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
        }else if(obj.event==='restore'){
            layer.confirm('真的还原吗？', function(index){
                $.post("/manage/recycle_contorller/restore",{id:data.id},function(data){
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
