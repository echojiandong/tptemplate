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

    form.on('submit(editPerson)',function(){
        if(!$('input').val()){
            layer.msg('信息不完整，无法提交', {icon: 5});
            return false;
        }
        var checkID = [];//定义一个空数组 
        $("input[name='gradeAuth']:checked").each(function(i){//把所有被选中的复选框的值存入数组
            checkID[i] =$(this).val();
        });
       

        $.post("/manage/person_controller/editPersonal",
            {
                id:$("[name='id']").val(),
                nickName:$("[name='nickName']").val(),
                phone:$("[name='phone']").val(),
                birthday:$("[name='birthday']").val(),
                school:$("[name='school']").val(),
                wechat:$("[name='wechat']").val(),
                grade_id:$("[name='grade_id']").val(),
                user_id:$("[name='user_id']").val(),
                // email:$("[name='email']").val(),
                password:$("[name='password']").val(),
                gender:$("[name='gender']:checked").val(),
                // grade_id:$('#grade_id option:selected').val(),
                gradeAuth:checkID,
                act_status:$("[name='act_status']:checked").val(),
                province:$("[name='province']").val(),
                city:$("[name='city']").val(),
                county:$("[name='county']").val(),
                remark:$("[name='remark']").val(),
                to_media:$("[name='to_media']").val()
            },
            function(data){
                if(data.error_code==0){
                    layer.msg(data.msg, {icon: 6,time:1500},function(){
                        window.parent.location.reload();//刷新父页面
                    });
                }else{
                    layer.msg(data.msg, {icon: 5,time:1500});
                }
            });
    })
 //    //拖拽上传
 //    upload.render({
 //        elem: '#test10'
 //        ,url: '/manage/person_controller/upload',
 //        size:'6000'
 //        ,done: function(res){
 //            if(res.error_code==0){
 //                layer.msg('上传成功',{time:800},function(){
 //                    $(".site-demo-upload img").attr("src",res.data);
 //                    $("#image").attr("value",res.data);
 //                    $(".site-demo-upload img").show();

 //                });
 //            }else{
 //                layer.msg('上传失败',{time:800});
 //            }
 //        }
 //    });
	// //创建一个编辑器
 //    laydate.render({
 //        elem: '#test17'
 //        ,calendar: true
 //    });
})