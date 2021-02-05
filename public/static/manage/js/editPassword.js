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

    form.on('submit(submit)', function(data){
        $.post("/manage/admin_contorller/ChangePassword",data.field,function(data){
            if(data.error_code==0){
                layer.msg(data.msg,{icon: 1,time: 1000},function () {
                    //var domain = document.domain;
                   //window.location.href='http://'+domain+'/manage/admin_contorller/adminList';
                    //window.location.href='http://www.ywd100.net/manage/admin_contorller/adminList';
                    location=location;
                });
            }else{
                layer.msg(data.msg);
            }
        })
        return  false;
    });
})