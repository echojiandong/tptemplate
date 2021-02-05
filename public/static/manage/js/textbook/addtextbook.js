layui.use(['form', 'layedit', 'laydate','table',"upload",'jquery'], function(){
    var form = layui.form;
        layer = layui.layer;
        layedit = layui.layedit;
        laydate = layui.laydate;
    	$ = layui.jquery;
 	form.on("submit(addNews)",function(data){
        var index = layer.load(1, {shade: [0.1,'#fff']});
        $.post("/manage/textbook_contorller/textbookAdd",data.field,function(data){
            layer.close(index);
            if(data.error_code==0){
                layer.msg('添加成功', {icon: 6,time:1500},function(){
                    location.reload();
                });
            }else{
                layer.msg('添加失败', {icon: 5,time:1500});
            }
        })
 		return false;
 	})

})