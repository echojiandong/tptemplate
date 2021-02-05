layui.use(['element','form','jquery','layer'], function(){
    var  $ = layui.jquery, form = layui.form,
        layer=layui.layer;
        element = layui.element; //导航的hover效果、二级菜单等功能，需要依赖element模块
    //监听导航点击
    element.on('nav(demo)', function(elem){
        //console.log(elem)
        layer.msg(elem.text());
    });

    form.on('checkbox(allChoose)', function(data){
        var child = $(data.elem).parents('table').find('tbody input[type="checkbox"]');
        child.each(function(index, item){
            item.checked = data.elem.checked;
        });
        form.render('checkbox');
    });

    form.on('submit(delbtn)', function(data){
        var child = $(data.elem).parents('blockquote').siblings().find('tbody input[type="checkbox"]');
        var idarr=[];
        child.each(function(n,v){
            if($(this).is(':checked')==true){
                idarr.push($(this).attr("value"));
            }
        });
        layer.confirm('确定要删除选中项吗？', {
            skin: 'layui-layer-molv',
            btn: ['确定','取消'] //按钮
        }, function(){
            if(idarr.length==0){
                layer.msg('没有选中任何项目', {icon: 5});
                return false;
            }
            $.post(delink,
                {
                    id:idarr
                },
                function(data){
                    if(data.code==1){
                        layer.msg('操作成功', {icon: 6},function(){
                            location.reload();
                        });
                    }else{
                        layer.msg('操作失败', {icon: 5});
                    }
                });
        }, function(){
            layer.msg('已取消');
        });

    });


    }).define(['jquery','layer'],function(exports){
    var $ = layui.jquery;
    layer = layui.layer;
    exports('switch', function(id,status){
        layer.confirm('确定要修改吗？', {
            skin: 'layui-layer-molv',
            btn: ['确定','取消'], //按钮,
            end:function(){
                var ico = window.localStorage.getItem("ico");
                $(".menu-icon").html(ico);
                $(".layui-focus").val(ico);
            }
        }, function(){
            $.post("/manage/wechat_controller/menuswitch",
                {
                    id:id,
                    status:status==1?0:1
                },
                function(data){
                    if(data.error_code==0){
                        layer.msg('操作成功', {icon: 6,time:1500},function(){
                            location.reload();
                        });
                    }else{
                        layer.msg('操作失败', {icon: 5,time:1500});
                    }
                });
        }, function(){
            layer.msg('已取消');
        });


    });

    exports('delete', function(id,status){
        layer.confirm('确定删除吗？', {
            skin: 'layui-layer-molv',
            btn: ['确定','取消'] //按钮
        }, function(){
            $.post("/manage/wechat_controller/deleteNav",
                {
                    id:id
                },
                function(data){
                    if(data.error_code==0){
                        layer.msg('操作成功', {icon: 6,time:1500},function(){
                            window.location.reload();
                        });
                    }else{
                        layer.msg('操作失败', {icon: 5,time:1500});
                    }
                });
        }, function(){
            layer.msg('已取消',{time:1500});
        });
    });
    exports('navadd', function(id,status){
        
        layer.open({
            type: 2,
            skin: 'layui-layer-rim', //加上边框
            area: ['100%', '100%'], //宽高
            anim:2,
            content:"/manage/wechat_controller/navadd",
            end:function(){
                window.location.reload();
            }

        });
    });
    exports('edit', function(id,status){
        layer.open({
            type: 2,
            skin: 'layui-layer-rim', //加上边框
            area: ['100%', '100%'], //宽高
            anim:2,
            content:"/manage/wechat_controller/navedit?id="+id,
            end:function(){
               window.location.reload();
            }
        });
    });

    exports('numord', function(e){
        $.post("/manage/wechat_controller/navSort",
            {
                id:$(e).attr('v-id'),
                sort:$(e).val()
            },
            function(data){
                if(data.error_code==0){
                    layer.msg('修改成功',{time:800});
                }else{
                    layer.msg('操作失败', {icon: 5});
                }
            });

    });

    exports('pushWechat', function(e){
        var id_array=new Array(); 
        $('input[name="id"]:checked').each(function(){ 
            id_array.push($(this).val());//向数组中添加元素 
        });

        var idstr=id_array.join(',');
        $.post("/weixin/weixin/createMenu",
            {
                idstr:idstr
            },
            function(data){
                if(data.error_code==0){
                    layer.msg('推送成功',{time:800});
                }else{
                    layer.msg(data.msg, {icon: 5});
                }
            });

    });

});
