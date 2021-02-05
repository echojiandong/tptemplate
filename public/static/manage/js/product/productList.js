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
        ,url:'/manage/product_controller/getProductList'
        ,height:'full'
        ,cellMinWidth:80
        ,cols: [[
            {field:'id', title: 'ID'}
            ,{field:'name', title: '产品名称'}
            ,{field:'title', title: '产品副标题'}
            ,{field:'content', title: '产品介绍'}
            ,{field:'price', title: '价格'}
            ,{field:'purchase', title: '购买人数'}
            ,{field:'learn', title: '阶段'}
            ,{field:'grade', title: '年级'}
            ,{field:'subject', title: '科目'}
            ,{field:'textbook', title: '版本'}
            ,{field:'sname', title: '教师姓名'}
            ,{field:'Semester', title: '学期'}
            ,{field:'addTime', title: '添加时间'}
            ,{field:'lock',  title: '操作', width:250, toolbar: '#barDemo'}
        ]]
        ,page: true
    });
    // table.reload('test');
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
                    ,url:'/manage/product_controller/getProductList?keyword='+demoReload
                    ,height:'full'
                    ,cols: [[
                        {field:'id', title: 'ID'}
                        ,{field:'name', title: '产品名称'}
                        ,{field:'title', title: '产品副标题'}
                        ,{field:'content', title: '产品介绍'}
                        ,{field:'price', title: '价格'}
                        ,{field:'purchase', title: '购买人数'}
                        ,{field:'learn', title: '阶段'}
                        ,{field:'grade', title: '年级'}
                        ,{field:'subject', title: '科目'}
                        ,{field:'textbook', title: '版本'}
                        ,{field:'sname', title: '教师姓名'}
                        ,{field:'Semester', title: '学期'}
                        ,{field:'addTime', title: '添加时间'}
                        ,{field:'lock',  title: '操作', width:250, toolbar: '#barDemo'}
                    ]]
                    ,page: true
                });
            }   
    });

  
    // $('#add').on('click', function(){
    //     layer.open({
    //             type: 2,
    //             title:"添加产品",
    //             skin: 'layui-layer-demo', //样式类名
    //             closeBtn: 1, //不显示关闭按钮
    //             anim: 2,
    //             area: ['100%', '95%'],
    //             shadeClose: true, //开启遮罩关闭
    //             maxmin: true,
    //             content: "/manage/product_controller/add"
    //     });
    // });
    laytpl.toDateString = function(nS){
        return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');
    };

    table.on('tool(demo)', function(obj){
        var data = obj.data;
        var count = obj.count;
        if(obj.event === 'edit'){
            layer.open({
                type: 2,
                title:"编辑",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/product_controller/edit?id="+data.id
            });
        }else if(obj.event === 'del'){
            layer.confirm('真的删除吗？', function(index){
                    $.post("/manage/product_controller/del",{id:data.id},function(data){
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
        add: function(){
            layer.open({
                type:2,
                title:"生成卡号",
                skin: 'layui-layer-molv', //加上边框
                area: ['100%','100%'], //宽高
                content: "/manage/product_controller/add",
                end:function(){
                    window.location.reload();
                }
            });
        }
    };
    //  active = {
    //     output: function(){
    //         window.location.href="/manage/code_contorller/output";
    //     }
    // }

})
