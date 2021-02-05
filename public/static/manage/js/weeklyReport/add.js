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

    form.on('submit(add)',function(data){
        var dataList = data.field;
        $.post("/manage/WeeklyReport_controller/addDefaultAdvise",{data:dataList},function(data){
            if(data.error_code==1){
                layer.msg('添加成功', {icon: 6,time:1500},function(){
                    window.parent.location.reload();//刷新父页面
                });
            }else{
                layer.msg(data.msg, {icon: 5,time:1500});
            }
        });
    })
    form.on('submit(update)',function(data){
        var dataList = data.field;
        $.post("/manage/WeeklyReport_controller/updateDefaultAdvise",{data:dataList},function(data){
            if(data.error_code==1){
                layer.msg('修改成功', {icon: 6,time:1500},function(){
                    window.parent.location.reload();//刷新父页面
                });
            }else{
                layer.msg(data.msg, {icon: 5,time:1500});
            }
        });
    })
})