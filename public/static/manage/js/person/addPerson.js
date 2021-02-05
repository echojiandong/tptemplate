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

    form.on('submit(addPerson)',function(data){
       
        var totalPrice = parseFloat(data.field.totalPrice); // 订单总价格
        var dicount = parseFloat(data.field.dicount); // 优惠价格
        var final = data.field.final; // 实际支付价格

        var isAudi = parseFloat($("#isAudi").attr('data-price'));
        var ischecked = $("#isAudi").attr('data-checked');

        

        if (dicount > totalPrice) {
            layer.msg('优惠金额不能大于订单总价',{icon: 5,time:1500});
            return false;
        }
        if (dicount == 0) {
            dicount = 0;
        }
        
        if (final == '') {
            final = totalPrice;
            $("[name='final']").val(totalPrice);
        }
       
        $.ajax({
            type: 'post',
            url:'/manage/person_controller/judgeIsOrderInsert',
            data:data.field,
            async:false,
            success:function(dt) {
               if (dt.code == 1) {
                    $.ajax({
                        type: 'post',
                        url:'/manage/person_controller/getOrderNow',
                        data:data.field,
                        async:false,
                        success:function(da){
                            console.log(da);
                            // 下单验证是否是当前账号的管理员
                            // if (da.error_code == 1) {
                            //     layer.msg(da.msg,{icon: 5,time:1500});
                            // }
                            var html = '';
                            html += '<table class="layui-table">'
                            html += '   <thead>'
                            html += '    <tr>'
                            html += '      <th>课程名称</th>'
                            html += '      <th>价格</th>'
                            html += '    </tr> '
                            html += '  </thead>'
                            html += '  <tbody>';
                            for(var i= 0; i<da.data.data.length; i++) {
                            html += '    <tr>'
                            html += '      <td>'+da.data.data[i].courseName+da.data.data[i].isAudition+'</td>'
                            html += '      <td>'+da.data.data[i].price+'</td>'
                            html += '    </tr>';
                            }

                            if (ischecked == 'false') {
                                html += '    <tr>'
                                html += '      <td>试听课程</td>'
                                html += '      <td>'+isAudi +'</td>'
                                html += '    </tr>';
                            }
                            
                            html += '    <tr>'
                            html += '      <td  style="font-size:16px;">总价格</td>'
                            html += '      <td style="font-size:16px;color:red;font-weight: 700;">'+ totalPrice+'</td>'
                            html += '    </tr>'
                            html += '    <tr>'
                            html += '      <td  style="font-size:16px;">优惠价格</td>'
                            html += '      <td style="font-size:16px;color:red;font-weight: 700;">'+ dicount +'</td>'
                            html += '    </tr>'
                            html += '    <tr>'
                            html += '      <td  style="font-size:16px; color:red;">实际支付价格</td>'
                            html += '      <td style="font-size:16px;color:red;font-weight: 700;">'+ final +'</td>'
                            html += '    </tr>';

                            for(var j= 0; j<da.data.paytype.length; j++) {
                            html += '    <tr>'
                            html += '      <td>'+da.data.paytype[j].paytype+'</td>'
                            html += '      <td>'+da.data.paytype[j].money+'</td>'
                            html += '    </tr>';
                            }

                            // html += '    <tr>'
                            // html += '      <td>订单总价格</td>'
                            // html += '      <td>'+ totalPrice+'</td>'
                            // html += '    </tr>'
                            // html += '    <td>优惠金额</td>'
                            // html += '      <td>'+ dicount+'</td>'
                            // html += '    </tr>'
                            // html += '    </tr>'
                            // html += '    <td>实际支付价格</td>'
                            // html += '      <td>'+ final+'</td>'
                            // html += '    </tr>'

                            html += '  </tbody>'
                            html += '</table>'
                
                            layer.confirm(html, {
                                area: ['50%', '80%'],
                                title:'请确认订单信息',
                                btn: ['确定', '取消'] //可以无限个按钮
                            }, function(index, layero) {
                                //按钮【按钮一】的回调
                                $.post("/manage/person_controller/doAddPerson",data.field,function(data){
                                    if(data.error_code==0){
                                        layer.msg(data.msg,{icon: 6,time:1500},function(){
                                            window.parent.location.reload();//刷新父页面
                                        });
                                    }else{
                                        layer.msg(data.msg,{icon: 5,time:1500});
                                    }
                                })
                            }, function(index) {
                            //按钮【按钮二】的回调
                            
                            });
                            return false;
                        },
                        error:function(e) {
                            console.log('失败')
                        }

                    })
                    return false;
                } else {
                    $.post("/manage/person_controller/doAddPerson",data.field,function(data){
                        if(data.error_code==0){
                            layer.msg(data.msg,{icon: 6,time:1500},function(){
                                window.parent.location.reload();//刷新父页面
                            });
                        }else{
                            layer.msg(data.msg,{icon: 5,time:1500});
                        }
                    })
                    return false;
                }
            }
        })
              
        return false;
        // $.post("/manage/person_controller/doAddPerson",
        //     {
        //         nickName:$("[name='nickName']").val(),
        //         phone:$("[name='phone']").val(),
        //         birthday:$("[name='birthday']").val(),
        //         school:$("[name='school']").val(),
        //         email:$("[name='email']").val(),
        //         password:$("[name='password']").val(),
        //         // gender:$("[name='gender']:checked").val(),
        //         // grade_id:$('#grade_id option:selected').val(),
        //         gradeAuth:checkID,
        //         // act_status:$("[name='act_status']:checked").val(),
        //         subject_id:$("[name='subject_id[9][2]']:checked").val()
        //     },
        //     function(data){
        //         if(data.error_code==0){
        //             layer.msg(data.msg, {icon: 6,time:1500},function(){
        //                 window.parent.location.reload();//刷新父页面
        //             });
        //         }else{
        //             layer.msg(data.msg, {icon: 5,time:1500});
        //         }
        //     });

        // $.post("/manage/order_contorller/getPreorderInfo",data.field,function(da) {
        //     var html = '';
        //     html += '<table class="layui-table">'
        //     html += '   <thead>'
        //     html += '    <tr>'
        //     html += '      <th>课程名称</th>'
        //     html += '      <th>价格</th>'
        //     html += '    </tr> '
        //     html += '  </thead>'
        //     html += '  <tbody>';
        //     for(var i= 0; i<da.data.data.length; i++) {
        //       html += '    <tr>'
        //       html += '      <td>'+da.data.data[i].courseName+da.data.data[i].isAudition+'</td>'
        //       html += '      <td>'+da.data.data[i].price+'</td>'
        //       html += '    </tr>';
        //     }
        //     html += '    <tr>'
        //     html += '      <td>总价格</td>'
        //     html += '      <td>'+ da.data.totalPrice+'</td>'
        //     html += '    </tr>'
        //     html += '  </tbody>'
        //     html += '</table>'
    
        //     layer.confirm(html, {
        //       area:["880px"],
        //       title:'请确认订单信息',
        //       btn: ['确定', '取消'] //可以无限个按钮
        //     }, function(index, layero) {
        //       //按钮【按钮一】的回调
        //       $.post("/manage/person_controller/doAddPerson",data.field,function(data){
        //         if(data.error_code==0){
        //             layer.msg(data.msg,{icon: 6,time:1500},function(){
        //                 window.parent.location.reload();//刷新父页面
        //             });
        //         }else{
        //             layer.msg(data.msg,{icon: 5,time:1500});
        //         }
        //       })
        //     }, function(index) {
        //       //按钮【按钮二】的回调
              
        //     });
        //   })

        $.post("/manage/person_controller/doAddPerson",data.field,function(data){
            if(data.error_code==0){
                layer.msg(data.msg,{icon: 6,time:1500},function(){
                    window.parent.location.reload();//刷新父页面
                });
            }else{
                layer.msg(data.msg,{icon: 5,time:1500});
            }
        })
 		return false;


    });



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