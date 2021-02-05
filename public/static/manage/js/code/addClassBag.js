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

    form.on('submit(addClassBag)',function(data){
        if(!$('input').val()){
            layer.msg('信息不完整，无法提交', {icon: 5});
            return false;
        }
        var checkID = [];//定义一个空数组 
        $("input[name='subjectId']:checked").each(function(i){//把所有被选中的复选框的值存入数组
            checkID[i] =$(this).val();
        });
        $.post("/manage/code_contorller/doAddClassBag",
        {
            id:$("[name='id']").val(),
            pageName:$("[name='pageName']").val(),
            price:$("[name='price']").val(),
            gradeId:$('#gradeId option:selected').val(),
            subjectId:checkID,
        },
        function(data){
            if(data.error_code==0){
                layer.msg('添加成功', {icon: 6,time:1500},function(){
                    location.reload();
                });
            }else{
                layer.msg('添加失败', {icon: 5,time:1500});
            }
        });
    })
})