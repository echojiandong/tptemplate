layui.use(['form', 'layedit',"upload",'jquery','layer'], function(){
    var form = layui.form;
        layer = layui.layer;
        layedit = layui.layedit;
        upload = layui.upload;
    	$ = layui.jquery;
    upload.render({
        elem: '#test10'
        ,url: '/manage/teacher_contorller/upload',
        size:'60000'
        ,done: function(res){
            if(res.error_code==0){
                layer.msg('上传成功',{time:800},function(){
                    $("#img").attr("value",res.data);
                    $("#image").attr("src",res.data);
                    $("#image").show();
                });
            }else{
                layer.msg('上传失败',{time:800});
            }
        }
    });

    upload.render({
        elem: '#cover'
        ,url: '/manage/teacher_contorller/upload',
        size:'1000000'
        ,done: function(res){
            if(res.error_code==0){
                layer.msg('上传成功',{time:800},function(){
                    $("#coverimage").attr("value",res.data);
                    $("#coverimg").attr("src",res.data);
                    $("#coverimg").show();
                });
            }else{
                layer.msg('上传失败',{time:800});
            }
        }
    });
    upload.render({
        elem: '#teacher_booth'
        ,url: '/manage/teacher_contorller/upload',
        size:'60000'
        ,done: function(res){
            if(res.error_code==0){
                layer.msg('上传成功',{time:800},function(){
                    $("#boothimg").attr("src",res.data);
                    $("#teacherBooth").attr("value",res.data);
                    $("#boothimg").show();
                });
            }else{
                layer.msg('上传失败',{time:800});
            }
        }
    });
    upload.render({
        elem: '#teacher_booth_hover'
        ,url: '/manage/teacher_contorller/upload',
        size:'60000'
        ,done: function(res){
            if(res.error_code==0){
                layer.msg('上传成功',{time:800},function(){
                    $("#boothhoverimg").attr("src",res.data);
                    $("#teacherboothhover").attr("value",res.data);
                    $("#boothhoverimg").show();
                });
            }else{
                layer.msg('上传失败',{time:800});
            }
        }
    });
    upload.render({
        elem: '#Audition_video'
        ,url: '/manage/teacher_contorller/uploadVideo'
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

 	form.on("submit(upCourse)",function(data){
        var index = layer.load(1, {shade: [0.1,'#fff']});
       $.post("/manage/teacher_contorller/update_teacher",data.field,function(data){
            layer.close(index);
            if(data.error_code==0){
                layer.msg('操作成功', {icon: 6,time:1500},function(){
                    location.reload();
                });
            }else{
                layer.msg('操作失败', {icon: 5,time:1500});
            }
        })
 		return false;
 	})
	
})