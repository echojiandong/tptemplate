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
	//创建一个编辑器
    laydate.render({
        elem: '#test17'
        ,calendar: true
    });
 	var editIndex = layedit.build('news_content');
 	var addNewsArray = [],addNews;
 	form.on("submit(addNews)",function(data){
        $.post("/manage/service/serviceAdd",data.field,function(data){
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
