layui.use(['form','layer','jquery','layedit','laydate'],function(){
	var form = layui.form,
		layer = parent.layer === undefined ? layui.layer : parent.layer,
		laypage = layui.laypage,
		layedit = layui.layedit,
		laydate = layui.laydate,
		$ = layui.jquery;
    form.verify({
        name: function(value){
            if(value.length < 1){
                return '必须填写';
            }
        }
    });

	form.on('submit(addnav)',function(data) {
        
		if(!$('input').val()){
            layer.msg('信息不完整，无法提交', {icon: 5});
            return false;
        }
        
        var field = data.field;
        if (!field.type) {
            layer.msg('请选择菜单类型', {icon:5});
            return false;
        }
        if ((field.type == 'view' || field.type == 'miniprogram') && !field.url) {
            layer.msg('URL不能为空');
            return false;
        }
        if (field.type == 'click' && !field.key) {
            layer.msg('KEY不能为空');
            return false;
        }

        if (field.type == 'miniprogram' && (!field.appid || !field.pagepath)) {
            layer.msg('小程序类型的appid或路径不能为空');
            return false;
        }
        
        $.post("/manage/wechat_controller/wechatnavadd",
            {
                name:$("[name='name']").val(),
                status:$("[name='status']:checked").val(),
                parentid:$("[name='parentid']").val(),
                url:$("[name='url']").val(),
                key:$("[name='key']").val(),
                type:$("[name='type']").val(),
                appid:$("[name='appid']").val(),
                pagepath:$("[name='pagepath']").val()
            },
            function(data){
                if(data.error_code==0){
                    layer.msg('操作成功', {icon: 6,time:1500},function(){
                        window.parent.location.reload();//刷新父页面
                    });
                }else{
                    layer.msg(data.msg, {icon: 5,time:1500});
                }
            });
	})

	$(".menu-icon").click(function(){
        var index = parent.layer.getFrameIndex(window.name);
        layer.open({
            type: 2,
			title:"选择图标",
            skin: 'layui-layer-rim', //加上边框
            area: ['50%', '80%'], //宽高
            anim:2,
            content:"/manage/index/showicon",
			end:function(){
                var ico = window.localStorage.getItem("ico");
                $(".menu-icon").html(ico);
                $(".layui-focus").val(ico);
                //window.location.reload();
			}
        });
	})


	
})