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
    laytpl.toStatus=function(ns){
        if(ns==1){
           return '开启';
       }else if(ns==2){
           return '禁用';
       }
    };
    table.render({
        elem: '#test'
        ,url:'/manage/code_contorller/classBagList'
        ,height:'full'
        ,cellMinWidth:80
        ,cols: [[
             {type: 'checkbox', fixed: 'left'}
            ,{field:'pageName', title: '课程包名称'}
            ,{field:'price', title: '课程包价格',width:220}
            ,{field:'grade', title: '年级'}
            ,{field:'username', title: '添加管理员'} 
            ,{field:'status', title: '状态',templet:'#status'}
            ,{field:'subjectId', title: '课程包'} 
            ,{field:'addtime', title: '添加时间'}
            ,{field:'lock',  title: '操作', width:250, toolbar: '#barDemo'}
        ]]
        ,page: true
    });
    // table.reload('test');
  $('#sousuo').on('click', function(){
            //var type = $(this).data('type');
            var demoReload = $('#demoReload').val();
            var status = $('#status').val();
            var grade_id = $('#grade_id').val();
            if(!demoReload && !status && !grade_id){
                //当用户没有输入内容直接点击查询的时候提示
                layer.msg("查询内容不能为空",{time:1000});
                return false;
            }else{
                //请求接口根据用户搜索查找卡号
                table.render({
                    elem: '#test'
                    ,url:'/manage/code_contorller/getcodeList?keyword='+demoReload+'&status='+status+'&grade_id='+grade_id
                    ,height:'full'
                    ,cols: [[
                         {type: 'checkbox', fixed: 'left'}
                        ,{field:'pageName', title: '课程包名称'}
                        ,{field:'price', title: '课程包价格',width:220}
                        ,{field:'gradeId', title: '年级'}
                        ,{field:'user_name', title: '状态'} 
                        ,{field:'subjectId', title: '课程'} 
                        ,{field:'addtime', title: '生成人员'}
                        ,{field:'lock',  title: '操作', width:250, toolbar: '#barDemo'}
                    ]]
                    ,page: true
                });
            }   
    });
    $('#addCode').on('click', function(){
        layer.open({
                type: 2,
                title:"添加课程包",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/code_contorller/addClassBag"
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
                title:"课程包信息预览",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/code_contorller/showClassBag?id="+data.id
            });
        }else if(obj.event==='del'){
            layer.confirm('真的删除吗？', function(index){
                $.post("/manage/code_contorller/delClassBag",{id:data.id},function(data){
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

    // active = {
    //     addCode: function(){
    //         layer.open({
    //             type:2,
    //             title:"添加课程包",
    //             skin: 'layui-layer-molv', //加上边框
    //             area: ['100%','100%'], //宽高
    //             content: "/manage/code_contorller/addClassBag",
    //             end:function(){
    //                 window.location.reload();
    //             }
    //         });
    //     }
    // };

})
