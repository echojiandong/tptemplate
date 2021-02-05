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
        var param;
        var type;
    form.on('submit(addOrder)',function(data){
        // if(!$('input').val()){
        //     layer.msg('信息不完整，无法提交', {icon: 5});
        //     return false;
        // }
        var listInfo = new Array();
        param = data.field;
        type = data.field.order_type;
        delete data.field.order_type;
        for(x in data.field){
            if(data.field[x] == '' || data.field[x] == 0){
                delete data.field[x];
            }else{
                listInfo[$('input[name="'+x+'"]').attr('data-aa')]=data.field[x];
            }
        }
        var html = '';
        if($.isEmptyObject(listInfo)){
            layer.msg('请输入课程的购买数量', {icon: 5,time:1500});
            return false;
        }
        for(x in listInfo){
            if(listInfo[x] < 0){
                layer.msg('购买的课程数量必须大于零', {icon: 5,time:1500});
                return false;
            }
            html +=x+':'+listInfo[x]+'<br/>';
        }
        $(".msg-list").html(html)
        $("#msgList").show()
       
    });
// 确定下单时间
form.on("submit(checkOrder)",function(data) {
    console.log(data.field);
    if( data.field.ok != 'on'){
      layui.use('layer',function(){
        layui.layer.msg('请勾选，同意下单购买课程权限！',{icon:0, time:1500});
      })
      return false;
    }
    param.order_type = type;
    console.log(param);
     $.post("/manage/order_contorller/doInsertCourse",param,function(data){
        if(data.error_code==0){
            layer.msg(data.msg, {icon: 6,time:1500},function(){
                window.parent.location.reload();//刷新父页面
            });
        }else{
            layer.msg(data.msg, {icon: 5,time:1500});
        }
     });
})
//取消下单事件
form.on("submit(cancelOrder)",function(data) {
     $("#msgList").hide()

    return false;
})
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
            
            if(arr) {
                var html='';
                for(var i =0; i < arr.length; i++) {
                   
                    if (arr[i].courseNum > 0) {
                        html+='<label style="width:70px;padding: 9px 3px;">' + arr[i].subject +' </label>';
                        html+='<div class="layui-inline" style="width:50px">';
                        html+='    <input name="arr['+sid+']['+"course"+']['+arr[i].id+']['+"id"+']" type="hidden" value="'+arr[i].id+'" >';
                        html+='    <input name="arr['+sid+']['+"course"+']['+arr[i].id+']['+"num"+']" type="text" class="layui-input newsName" lay-verify="order_num"  value="" >';
                        html+='</div>';
                    } else {
                       
                        html+='<label style="width:70px;padding: 9px 3px;">' + arr[i].subject +' </label>';
                        html+='<div class="layui-inline" style="width:50px">';
                        html+='    <input name="arr['+sid+']['+"course"+']['+arr[i].id+']['+"id"+']" type="hidden" value="'+arr[i].id+'" >';
                        html+='    <input name="arr['+sid+']['+"course"+']['+arr[i].id+']['+"num"+']" type="text" class="layui-input newsName" lay-verify="order_num"  value="" >';
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