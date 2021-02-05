layui.use(['form','layer','jquery','layedit',"upload",'laydate'],function(){
	var form = layui.form,
		layer = parent.layer === undefined ? layui.layer : parent.layer,
		laypage = layui.laypage,
		layedit = layui.layedit,
		laydate = layui.laydate,
		$ = layui.jquery;
    form.verify({
        title: function(value){
            if(value.length < 1){
                return '必须填写';
            }
        }
    });

	form.on('submit(addmenu)',function(data){
		if(!$('input').val()){
            layer.msg('信息不完整，无法提交', {icon: 5});
            return false;
		}
        $.post("/manage/index/menuupdate_handle",data.field,function(data){
                if(data.error_code==0){
                    layer.msg('操作成功', {icon: 6,time:1500},function(){
                        window.parent.location.reload();//刷新父页面
                    });
                }else{
                    layer.msg('操作失败', {icon: 5,time:1500});
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
    upload = layui.upload;
        $ = layui.jquery;
    //拖拽上传
    upload.render({
        elem: '#test10'
        ,url: '/manage/index/upload'
        ,done: function(res){
            if(res.error_code==0){
                layer.msg('上传成功',{time:800},function(){
                    $(".site-demo-upload img").attr("src",res.data);
                    $("#image").attr("value",res.data);
                    $(".site-demo-upload img").show();

                });
            }else{
                layer.msg('上传失败',{time:800});
            }
        }
    });
})