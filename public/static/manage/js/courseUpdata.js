layui.use(['form', 'layedit', 'laydate','table',"upload",'jquery'], function(){
    var form = layui.form;
        layer = layui.layer;
        layedit = layui.layedit;
        laydate = layui.laydate;
      //  table = layui.table;
        upload = layui.upload;
    	$ = layui.jquery;
    //拖拽上传
    upload.render({
        elem: '#test10'
        ,url: '/manage/course_contorller/upload',
        size:'60000'
        ,done: function(res){
            if(res.error_code==0){
                layer.msg('上传成功',{time:800},function(){
                    $("#testImg").attr("src",res.data);
                    $("#image").attr("value",res.data);
                    $("#testImg").show();

                });
            }else{
                layer.msg('上传失败',{time:800});
            }
        }
    });
    upload.render({
        elem: '#cover'
        ,url: '/manage/course_contorller/upload',
        size:'60000'
        ,done: function(res){
            if(res.error_code==0){
                layer.msg('上传成功',{time:800},function(){
                    $("#coverimg").attr("src",res.data);
                    $("#coverimage").attr("value",res.data);
                    $("#coverimg").show();
                });
            }else{
                layer.msg('上传失败',{time:800});
            }
        }
    });

    upload.render({
        elem: '#Audition_video'
        ,url: '/manage/course_contorller/upload',
        size:'60000'
        ,accept: 'video'
        ,done: function(res){
            if(res.error_code==0){
                layer.msg('上传成功',{time:800},function(){
                    $("#Auditionimg").attr("src",res.data);
                    $("#Auditionvideo").attr("value",res.data);
                    $(".site-demo-upload video").show();
                });
            }else{
                layer.msg('上传失败',{time:800});
            }
        }
    });

	//创建一个编辑器
    laydate.render({
        elem: '#test17'
        ,calendar: true
    });
 	form.on("submit(addNews)",function(data){
        var index = layer.load(1, {shade: [0.1,'#fff']});
        $.post("/manage/course_contorller/editcourse",data.field,function(data){
            layer.close(index);
            if(data.error_code==0){
                layer.msg('修改成功', {icon: 6,time:1500},function(){
                    location.reload();
                });
            }else{
                layer.msg('修改失败', {icon: 5,time:1500});
            }
        })
 		return false;
 	})

})