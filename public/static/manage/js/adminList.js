layui.use(['form','layer','jquery','laypage','table','laytpl'],function(){
    var $ = layui.$, form = layui.form;
    table = layui.table;
    upload = layui.upload;
    laytpl = layui.laytpl;

	//加载页面数据
    function GetQueryString(name)
    {
        var reg = new RegExp("(^|&)"+ name +"=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if(r!=null)return  unescape(r[2]); return null;
    }

    //监听单元格编辑
    table.on('edit(demo)', function(obj){
        var value = obj.value //得到修改后的值
            ,data = obj.data //得到所在行所有键值
            ,field = obj.field; //得到字段
        $.post("/manage/news_contorller/update_news",{id:data.id,field:field,val:value},function(res){
            if(res.error_code==0){layer.msg('修改成功',{time:300});}else{layer.msg('修改失败',{time:300});}
        })


    });

    table.render({
        elem: '#test'
        ,url:'/manage/admin_contorller/getadminlist?type='+GetQueryString('type')
        ,cols: [[
            {type:'checkbox'},
            {field:'uid', title: 'ID', sort: true}
            ,{field:'username', title: '管理员名称'}
            ,{field:'title', title: '管理员分组 '}
            ,{field:'address', title: '管理员地址 '}
            ,{field:'phone', title: '管理员联系方式'}
            ,{field:'paytype', title: '收款方式 '}
            ,{field:'parent_id', title: '父级id',}
                     // ,{field:'images', title: '图片',  minWidth: 100,  unresize: true}
            ,{field:'lock',  title: '操作', toolbar: '#barDemo',width:250}
        ]]
         ,id: 'testReload'
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
    form.on('switch(sexDemo)', function(obj){
        var status=$(this).attr("bid");
        var dom = $(this);
        if(status==1){
            status=2
            var text="真的要隐藏么"
        }else{
           status=1
             var text="真的要展示么"
         }
              layer.confirm(text, function(index){
                $.post("/manage/news_contorller/update_news_status",{id:obj.value,val:status},function(data){
                    if(data.error_code==0){
                        layer.msg("修改成功",{time:400,shade:0.1},function(){
                            dom.attr("bid",status);
                        });

                    }else{
                        layer.msg("修改失败");
                    }
                })
            });
    });

    table.on('tool(demo)', function(obj){
        var data = obj.data;
        if(obj.event === 'Prohibition'){
            //封禁管理员账号
            layer.confirm('真的封禁么', function(index){
                $.post("/manage/admin_contorller/Prohibition",{id:data.uid},function(data){
                    if(data.error_code==0){
                        layer.msg("封禁成功",{time:400},function(){
                            location=location;
                        });

                    }else{
                        layer.msg("封禁失败",{time:400});
                    }
                })

            });
        }else if(obj.event === 'LiftingOfProhibition'){
            //解除管理员账号封禁状态
            layer.confirm('真的解除管理员账号封禁么', function(index){
                $.post("/manage/admin_contorller/LiftingOfProhibition",{id:data.uid},function(data){
                    if(data.error_code==0){
                        layer.msg("解除成功",{time:400},function(){
                            location=location;
                        });

                    }else{
                        layer.msg("解除失败",{time:400});
                    }
                })

            });
        }else if(obj.event === 'editAdmin'){
            //编辑管理员账号名称
               layer.open({
                    type: 2,
                    title:"编辑管理员账号",
                    skin: 'layui-layer-demo', //样式类名
                    closeBtn: 1, //不显示关闭按钮
                    anim: 2,
                    area: ['100%', '95%'],
                    shadeClose: true, //开启遮罩关闭
                    maxmin: true,
                    content: "/manage/admin_contorller/editAdmin?uid="+data.uid
                });
        }else if(obj.event === 'ChangePassword'){
            //修改管理员密码
           layer.open({
              title: '修改管理员密码'
              ,content: "<input type='hidden' id='id' value='"+data.uid+"'/><input type='text' id='ChangePassword' placeholder='请输入新密码' value='' style='margin:0 0 3px 0;height: 34px;padding-left: 10px;border: 1px solid #aaa;border-radius: 5px;'>",
              yes: function(index, layero){
                    if($('#ChangePassword').val()){
                        $.post('/manage/admin_contorller/ChangePassword', {id:$('#id').val(),ChangePassword:$('#ChangePassword').val()}, function(data){
                                layer.close(index);
                                if(data.error_code==0){
                                    layer.msg('操作成功', {icon: 6,time:1500},function(){
                                        location.reload();
                                    });
                                }else{
                                    layer.msg('操作失败', {icon: 5,time:1500});
                                }
                        })
                    }else{
                        layer.msg('请输入密码', {icon: 5,time:1500});
                    }
                  }
            });  
        } else if(obj.event === 'delAdmin'){
            // 删除管理员
            layer.confirm('真的删除行么', function(index){
                $.post("/manage/admin_contorller/delAdmin",{id:data.uid},function(data){
                    console.log(data);
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

   active = {
        addData: function(){
            layer.open({
                type: 2,
              title: '添加管理员 ',
              skin: 'layui-layer-demo', //样式类名
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true
              ,content: "/manage/admin_contorller/addadmin"
            });  
        }
    };

    $('.demoTable .layui-btn').on('click', function(){
        var type = $(this).data('type');
        active[type] ? active[type].call(this) : '';
    });








})
