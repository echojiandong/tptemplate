layui.use(['form', 'layedit', 'laydate','table',"upload",'jquery'], function(){
    var form = layui.form;
        layer = layui.layer;
        layedit = layui.layedit;
        laydate = layui.laydate;
        //直播时间范围日期时间选择器
          laydate.render({
            elem: '#test6'
            ,type: 'datetime'
            ,range: true
          });
      //  table = layui.table;
        upload = layui.upload;
    	$ = layui.jquery;

    form.on('submit(submit)',function(data){
        if(!$('input').val()){
            layer.msg('信息不完整，无法提交', {icon: 5});
            return false;
        }
        $.post("/manage/admin_contorller/doEditAdmin",data.field,  function(data){
            if(data.error_code==0){
                layer.msg(data.msg, {icon: 6,time:1500},function(){
                    window.parent.location.reload();//刷新父页面
                });
            }else{
                layer.msg(data.msg, {icon: 5,time:1500},function(){
                });
            }   
        });
    })
 //拖拽上传
    upload.render({
        elem: '#test10'
        ,url: '/manage/admin_contorller/uploadImg'
        ,done: function(res){
            if(res.error_code==0){
                layer.msg('上传成功',{time:800},function(){
                    var img = res.data;
                    var img = img.replace("\\","/");
                    $("#testImg").attr("src",img);
                    $("#image").attr("value",img);
                    $("#testImg").show();
                });
            }else{
                layer.msg('上传失败',{time:800});
            }
        }
    });
})