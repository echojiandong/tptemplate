layui.use(['form', 'layedit', 'laydate','table',"upload",'jquery'], function(){
    var form = layui.form;
        layer = layui.layer;
        layedit = layui.layedit;
        laydate = layui.laydate;
        upload = layui.upload;
        $ = layui.jquery;
        
    // 文件上传
    upload.render({
        elem: '#coursewarefile'
        ,url: '/manage/datafile_controller/upload',
        size:'60000'
        ,done: function(res){
            if(res.error_code==0){
                layer.msg('上传成功',{time:800},function(){
                    $("#link").attr("value",res.data);
                });
            }else{
                layer.msg('上传失败',{time:800});
            }
        }
    });

    form.on('submit(doAdd)',function(data) {
        $.ajax({
            type: 'post',
            url:'/manage/datafile_controller/update',
            data:data.field,
            async:false,
            success:function(dt) {
                if(dt.error_code==0){
                    layer.msg(dt.msg, {icon: 6,time:1500},function(){
                        window.parent.location.reload();//刷新父页面
                    });
                }else{
                    layer.msg(dt.msg, {icon: 5,time:1500});
                }
            }
        })
              
        return false;
       
    });
})