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
        ,url:'/manage/review_contorller/getknowlist?pid='+id
        ,height:632
        ,cols: [[
            {field:'k_id',title:'知识点ID'}
            ,{field:'k_name', title: '知识点标题'}
            ,{field:'k_content',title:'知识点内容'}
            ,{field:'s_id',title:'所属课时(SID)'}
            ,{field:'start_time',title:'开始时间'}
            ,{field:'end_time',title:'结束时间'}
            ,{field:'created_time',title: '添加日期',}
            ,{field:'update_time',title: '更新日期',}
            ,{fixed: 'right',  toolbar: '#barDemo',title: '操作',}
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
        if(obj.event === 'show'){
            layer.open({
                type: 2,
                title:"课程预览",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/review_contorller/getOneKnow?id="+data.k_id
            });
        } else if(obj.event === 'edit'){
            layer.open({
                type: 2,
                title:"知识点编辑",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/review_contorller/editknowledge?id="+data.k_id
            });
        }else if(obj.event==='del'){
                layer.confirm('真的删除此知识点么？', function(index){
                $.post("/manage/review_contorller/delknowdege",{id:data.k_id},function(data){
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
