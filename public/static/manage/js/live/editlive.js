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
    //拖拽上传
    upload.render({
        elem: '#test10'
        ,url: '/manage/live_contorller/upload',
        size:'6000'
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
	//创建一个编辑器
    laydate.render({
        elem: '#test17'
        ,calendar: true
    });
 	form.on("submit(addNews)",function(data){
        var index = layer.load(1, {shade: [0.1,'#fff']});
        $.post("/manage/live_contorller/liveEdit",data.field,function(data){
            layer.close(index);
            if(data.error_code==0){
                layer.msg('添加成功', {icon: 6,time:1500},function(){
                    window.parent.location.reload();//刷新父页面
                });
            }else{
                layer.msg('添加失败', {icon: 5,time:1500});
            }
        })
 		return false;
 	})

})