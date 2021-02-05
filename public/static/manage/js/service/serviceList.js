var $,tab,skyconsWeather;
layui.config({
    base : "/static/manage/js/"
}).use(['laydate', 'laypage', 'layer', 'table', 'carousel', 'upload', 'element','bodyTab'], function(){
    var $ = layui.$, form = layui.form;
    laytpl = layui.laytpl;
    tab = layui.bodyTab({
        openTabNum : "50", //最大可打开窗口数量
        url : "json/navs.json" //获取菜单json地址
    });
    var laydate = layui.laydate //日期
  ,laypage = layui.laypage //分页
  layer = layui.layer //弹层
  ,table = layui.table //表格
  ,carousel = layui.carousel //轮播
  ,upload = layui.upload //上传
  ,element = layui.element; //元素操作

var id=$('#video_id').val();
    table.render({
        elem: '#test'
        ,url:'/manage/service/getServiceList?id='+id+'&page=1&limit=10'
        ,height:632
        ,cols: [[
            {field:'weixin',title:'微信号'}
            ,{field:'nickname',title:'昵称'}
            ,{field:'intime',title:'加入时间'}
            ,{field:'status',title:'状态'}
            ,{field:'uptime', title: '更新时间'}
            ,{field:'upid', title: '客服录入人'}
            ,{fixed: 'right', width: 225, align:'center', toolbar: '#barDemo'}
        ]]
        ,page: true
    });
  $('#sousuo').on('click', function(){
           var type = $(this).data('type');
            var demoReload = $('#demoReload');
            table.reload('test', {
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
        if(obj.event === 'show'){//查看微信客服详情
            layer.open({
                type: 2,
                title:"课程预览",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '100%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/service/showService?id="+data.id
            });
        } else if(obj.event === 'edt'){//编辑客服信息
            layer.open({
                type: 2,
                title:"课程编辑",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/service/edtService?id="+data.id
            });
        }else if(obj.event === 'del'){
            layer.confirm('真的删除行么', function(index){
                $.post("/manage/service/delService",{id:data.id},function(data){
                    if(data.error_code==1){
                        layer.msg("删除成功",{time:1000},function(){
                            obj.del();
                            layer.close(index);
                        });

                    }else{
                        layer.msg("删除失败",{time:1000});
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
                title:"添加微信客服",
                skin: 'layui-layer-molv', //加上边框
                area: ['100%','100%'], //宽高
                content: "/manage/service/Addservice?id="+id,
                end:function(){
                    window.location.reload();
                }
            });
        }
    };

})
