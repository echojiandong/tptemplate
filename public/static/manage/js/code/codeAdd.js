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

 	form.on("submit(addNews)",function(data) {

        $.post("/manage/code_contorller/insertCardCode",data.field,function(data) {
            
            if (data.error_code==0) {
                layer.msg(data.msg, {icon: 6,time:1500},function(){
                    window.parent.location.reload();//刷新父页面
                });
            } else {
                layer.msg(data.msg, {icon: 5,time:1500});
            }
        })

        // var num  = data.field.num;  
        // if (!num) {
        //      //当用户没有输入内容直接点击查询的时候提示
        //     layer.msg("内容不能为空",{time:1000});
        // } else {
        //     var index = layer.load(1, {shade: [0.1,'#fff']});
        //     $.post("/manage/code_contorller/insertCardCode",data.field,function(data) {
        //         layer.close(index);
        //         if (data.error_code==0) {
        //             layer.msg(data.msg, {icon: 6,time:1500},function(){
        //                 window.parent.location.reload();//刷新父页面
        //             });
        //         } else {
        //             layer.msg(data.msg, {icon: 5,time:1500});
        //         }
        //     })
     	// 	return false;
        // }
    });


    form.on("select(selectClass)",function(data) {
        
        var that = $(this);
        var thisParent = that.parent().parent().parent().parent().parent();

        var sid = that.parent().parent().parent().attr('sid'); 
        
         // 年级
         var gradeid = thisParent.find('.grade_id').val(); 
         // 学期
        var semesterid = thisParent.find('.semester').val();

        $.post("/manage/code_contorller/getCourseByClass", {gradeid:gradeid, semesterid:semesterid}, function(data) {
            
            if (data.error_code != 0) {
                thisParent.find('.course').html('');
                thisParent.find('.course').append('暂无课程，请联系管理员!');
                layer.msg(data.msg, {icon: 5,time:1500});
            }
           
            var arr = JSON.parse(data.data);
            // 判断是否是超级管理员
            var isAdmin = data.count;
            var number = '';
            var disab = '';
            if (!isAdmin) {
                number = '';
            }

            if(arr) {
                var html='';
                for(var i =0; i < arr.length; i++) {
                    if (!isAdmin) {
                        number = '剩余 '+ arr[i].courseNum;
                        disab = 'disabled';
                    }
                    if (arr[i].courseNum > 0) {
                        html+='<label style="width:70px;padding: 9px 3px;">' + arr[i].subject + number +' </label>';
                        html+='<div class="layui-inline" style="width:50px">';
                        html+='    <input name="arr['+sid+']['+"course"+']['+arr[i].id+']['+"id"+']" type="hidden" value="'+arr[i].id+'" >';
                        html+='    <input name="arr['+sid+']['+"course"+']['+arr[i].id+']['+"num"+']" type="text" class="layui-input newsName" lay-verify="order_num"  value="" >';
                        html+='</div>';
                    } else {
                        number = '剩余 0';
                        html+='<label style="width:70px;padding: 9px 3px;">' + arr[i].subject  + number +' </label>';
                        html+='<div class="layui-inline" style="width:50px">';
                        html+='    <input name="arr['+sid+']['+"course"+']['+arr[i].id+']['+"id"+']" type="hidden" value="'+arr[i].id+'" >';
                        html+='    <input name="arr['+sid+']['+"course"+']['+arr[i].id+']['+"num"+']" '+ disab +' type="text" class="layui-input newsName" lay-verify="order_num"  value="" >';
                        html+='</div>';
                    }

                }
                
                thisParent.find('.course').html('');
                thisParent.find('.course').append(html);

                form.render();
                sid++
            }
        })
    })

    
})


