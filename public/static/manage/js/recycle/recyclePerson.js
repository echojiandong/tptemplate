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
        ,url:'/manage/recycle_contorller/getRecyclecPerson'
        ,height:'full'
        ,cols: [[
             {type: 'checkbox', fixed: 'left'}
            ,{field:'id', title: 'ID', width:80, align:'center'}
            ,{field:'nickName', title: '用户昵称', templet: '#usernameTpl', width:150, align:'center'}
            ,{field:'phone', title: '手机号码', width:120, align:'center'}
            ,{field:'city', title: '城市', width:150, align:'center'} 
            ,{field:'province', title: '省份', width:100, align:'center'}
            ,{field:'addtime', title: '添加时间', width:120, align:'center'}
            ,{field:'statusTxt', title: '用户学习状态',width:100, align:'center'}
            ,{field:'actstatusTxt', title: '用户状态',width:100, align:'center'}
            ,{field:'username', title: '所属代理商', width:100, align:'center'}
            ,{field:'lock',  title: '操作', toolbar: '#barDemo', align:'center'}
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
                    ,url:'/manage/recycle_contorller/getRecyclecPerson?keyword='+demoReload
                    ,height:'full'
                    ,cols: [[
                         {type: 'checkbox', fixed: 'left'}
                        ,{field:'id', title: 'ID', width:80, align:'center'}
                        ,{field:'nickName', title: '用户昵称', templet: '#usernameTpl', width:150, align:'center'}
                        ,{field:'phone', title: '手机号码', width:120, align:'center'}
                        ,{field:'city', title: '城市', width:150, align:'center'} 
                        ,{field:'province', title: '省份', width:100, align:'center'}
                        ,{field:'addtime', title: '添加时间', width:120, align:'center'}
                        ,{field:'statusTxt', title: '用户学习状态',width:100, align:'center'}
                        ,{field:'actstatusTxt', title: '用户状态',width:100, align:'center'}
                        ,{field:'username', title: '所属代理商', width:100, align:'center'}
                        ,{field:'lock',  title: '操作', toolbar: '#barDemo', align:'center'}
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
        if(obj.event === 'delPerson'){
            layer.confirm('真的删除吗？', function(index){
                $.post("/manage/recycle_contorller/delPerson",{id:data.id},function(data){
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
        }else if(obj.event==='restorePerson'){
            layer.confirm('真的还原吗？', function(index){
                $.post("/manage/recycle_contorller/restorePerson",{id:data.id},function(data){
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
