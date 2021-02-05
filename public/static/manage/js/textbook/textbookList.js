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
        ,url:'/manage/textbook_contorller/GetTextBookList'
        ,height:332
        ,cols: [[
            {field:'id', title: '序号'}
            ,{field:'textbook', title: '版本名称'}
            ,{fixed: 'right', width: 165, align:'center', toolbar: '#barDemo'}
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
                //请求接口根据用户搜索查找老师
                table.render({
                    elem: '#test'
                    ,url:'/manage/textbook_contorller/GetTextBookList?search='+demoReload
                    ,height:332
                    ,cols: [[
                        {field:'id', title: '序号'}
                        ,{field:'textbook', title: '版本名称'}
                        ,{fixed: 'right', width: 165, align:'center', toolbar: '#barDemo'}
                    ]]
                    ,page: true
                });
            }
            // console.log(demoReload);
            // table.reload('testReload', {
            //     where: {
            //         keyword: demoReload
            //     }
            // });      
   });
    laytpl.toDateString = function(nS){
        return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');
    };

    table.on('tool(demo)', function(obj){
        var data = obj.data;
        if(obj.event === 'editTextBook'){
            layer.open({
                type: 2,
                title:"教师信息编辑",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/textbook_contorller/EditTextBook?id="+data.id
            });
        }else if(obj.event==='delTextBook'){
                layer.confirm('真的删除行么', function(index){
                $.post("/manage/textbook_contorller/DelTextBook",{id:data.id},function(data){
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
                title:"添加教师",
                skin: 'layui-layer-molv', //加上边框
                area: ['100%','100%'], //宽高
                content: "/manage/textbook_contorller/AddTextBook",
                end:function(){
                    window.location.reload();
                }
            });
        }
    };

})
