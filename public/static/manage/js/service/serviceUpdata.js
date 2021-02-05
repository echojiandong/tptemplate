layui.use(['form', 'layedit',"upload",'jquery','layer'], function(){
    var form = layui.form;
        layer = layui.layer;
        layedit = layui.layedit;
        upload = layui.upload;
    	$ = layui.jquery;
    upload.render({
        elem: '#test10'
        ,url: '/manage/service/upload',
        size:'60000'
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

 	form.on("submit(upCourse)",function(data){
       $.post("/manage/service/updateService",data.field,function(data){ 
            if(data.error_code==1){
                layer.msg('操作成功', {icon: 6,time:1500},function(){
                    window.parent.location.reload();
                    var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                    parent.layer.close(index);
                });
            }else{
                layer.msg('操作失败', {icon: 5,time:1500});
            }
        })
 		return false;
 	})
	
})
