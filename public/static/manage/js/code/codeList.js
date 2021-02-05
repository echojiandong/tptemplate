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

    table.render({
        elem: '#test'
        ,url:'/manage/code_contorller/getcodeList'
        ,height:'full'
        ,cellMinWidth:80
        ,cols: [[
             {type: 'checkbox', fixed: 'left'}
            ,{field:'id', title: 'ID'}
            ,{field:'card', title: '卡号',width:220}
            ,{field:'password', title: '密码'}
            ,{field:'status_name', title: '状态'} 
            ,{field:'price', title: '卡号价格'} 
            ,{field:'coursePackage', title: '卡号类型'} 
            ,{field:'user_name', title: '所属管理员'}
            ,{field:'create_user_name', title: '生成人员'}
            ,{field:'create_time', title: '生成时间'}
            ,{field:'lock',  title: '操作', width:250, toolbar: '#barDemo'}
        ]]
        ,page: true
    });
    // table.reload('test');
  $('#sousuo').on('click', function(){
            //var type = $(this).data('type');
            var demoReload = $('#demoReload').val();
            var status = $('#status').val();
            if(!demoReload && !status && !grade_id){
                //当用户没有输入内容直接点击查询的时候提示
                layer.msg("查询内容不能为空",{time:1000});
                return false;
            }else{
                //请求接口根据用户搜索查找卡号
                table.render({
                    elem: '#test'
                    ,url:'/manage/code_contorller/getcodeList?keyword='+demoReload+'&status='+status
                    ,height:'full'
                    ,cols: [[
                         {type: 'checkbox', fixed: 'left'}
                        ,{field:'id', title: 'ID'}
                        ,{field:'card', title: '卡号',width:220}
                        ,{field:'password', title: '密码'}
                        ,{field:'status_name', title: '状态'} 
                        ,{field:'price', title: '卡号价格'} 
                        ,{field:'coursePackage', title: '卡号类型'} 
                        ,{field:'user_name', title: '所属管理员'}
                        ,{field:'create_user_name', title: '生成人员'}
                        ,{field:'create_time', title: '生成时间'}
                        ,{field:'lock',  title: '操作', width:250, toolbar: '#barDemo'}
                    ]]
                    ,page: true
                });
            }   
    });
    $('#addCode').on('click', function(){
        layer.open({
                type: 2,
                title:"生成卡号",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/code_contorller/addCode"
        });
    });
    laytpl.toDateString = function(nS){
        return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');
    };

    table.on('tool(demo)', function(obj){
        var data = obj.data;
        var count = obj.count;
        if(obj.event === 'editCode'){
            layer.open({
                type: 2,
                title:"卡号信息预览",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/code_contorller/showCode?id="+data.id
            });
        }else if(obj.event==='forbidden'){
                layer.confirm('真的禁用吗？', function(index){
                    $.post("/manage/code_contorller/forbidden",{id:data.id},function(data){
                    if(data.error_code==0){
                        layer.msg("禁用成功",{time:400},function(){
                            obj.del();
                            layer.close(index);
                        });

                    }else{
                        layer.msg("禁用失败",{time:400});
                    }
                })

            });
        }
    });


    $('.demoTable .layui-btn').on('click', function(){
        var type = $(this).data('type');
        active[type] ? active[type].call(this) : '';
    });

    // active = {
    //     addCode: function(){
    //         layer.open({
    //             type:2,
    //             title:"生成卡号",
    //             skin: 'layui-layer-molv', //加上边框
    //             area: ['100%','100%'], //宽高
    //             content: "/manage/code_contorller/addCode",
    //             end:function(){
    //                 window.location.reload();
    //             }
    //         });
    //     }
    // };
     active = {
        output: function(){
            window.location.href="/manage/code_contorller/output";
        }
    }

})
