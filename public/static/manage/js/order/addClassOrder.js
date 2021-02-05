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

 	  // form.on("submit(addClassOrder)",function(data){
    //     $.post("/manage/order_contorller/doAddClassOrder",data.field,function(data){
    //         if(data.error_code==0){
    //             layer.msg(data.msg,{icon: 6,time:1500},function(){
    //                 window.parent.location.reload();//刷新父页面
    //             });
    //         }else{
    //             layer.msg(data.msg,{icon: 5,time:1500});
    //         }
    //     })
 		// return false;
    // })

    form.on("submit(addClassOrder)",function(data){
     
      var totalPrice = parseFloat(data.field.totalPrice); // 订单总价格
      var dicount = parseFloat(data.field.dicount); // 优惠价格
      
      var final = data.field.final; // 实际支付价格
      if (dicount > totalPrice) {
        layer.msg('优惠金额不能大于订单总价',{icon: 5,time:1500});
        return false;
      } 
      if (dicount == 0) {
        dicount = 0;
      }
      
      if (final == '') {
        final = totalPrice;
      }

      $.ajax({
        type: 'post',
        url:'/manage/order_contorller/getPreorderInfo',
        data:data.field,
        async:false,
        success:function(da) {
          if (da.error_code == 1) {
            layer.msg(da.msg,{icon: 5,time:1500});
            return false;
          }
         
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
          html += '    </tr>'



          html += '  </tbody>'
          html += '</table>'
          layer.confirm(html, {
            area: ['50%', '80%'],
            title:'请确认订单信息',
            btn: ['确定', '取消'] //可以无限个按钮
          }, function(index, layero) {
            //按钮【按钮一】的回调
            $.post("/manage/order_contorller/doAddClassOrder",data.field,function(res){
              
              if(res.error_code==0){
                  layer.msg(res.msg,{icon: 6,time:1500},function(){
                      window.parent.location.reload();//刷新父页面
                  });
              }else{
                  layer.msg(res.msg,{icon: 5,time:1500});
              }
            })
            return false;
          }, function(index) {
            //按钮【按钮二】的回调
            
          });
        },
        error:function(e) {
          layer.msg(e.msg,{icon: 5,time:1500});
        }
      })

      return false;



      // $.post("/manage/order_contorller/getPreorderInfo",data.field,function(da) {
      //   var html = '';
      //   html += '<table class="layui-table">'
      //   html += '   <thead>'
      //   html += '    <tr>'
      //   html += '      <th>课程名称</th>'
      //   html += '      <th>价格</th>'
      //   html += '    </tr> '
      //   html += '  </thead>'
      //   html += '  <tbody>';
      //   for(var i= 0; i<da.data.data.length; i++) {
      //     html += '    <tr>'
      //     html += '      <td>'+da.data.data[i].courseName+da.data.data[i].isAudition+'</td>'
      //     html += '      <td>'+da.data.data[i].price+'</td>'
      //     html += '    </tr>';
      //   }
      //   html += '    <tr>'
      //   html += '      <td>总价格</td>'
      //   html += '      <td>'+ da.data.totalPrice+'</td>'
      //   html += '    </tr>'
      //   html += '  </tbody>'
      //   html += '</table>'

      //   layer.confirm(html, {
      //     area:["880px"],
      //     title:'请确认订单信息',
      //     btn: ['确定', '取消'] //可以无限个按钮
      //   }, function(index, layero) {
      //     //按钮【按钮一】的回调
      //     $.post("/manage/order_contorller/doAddClassOrder",data.field,function(data){
      //       if(data.error_code==0){
      //           layer.msg(data.msg,{icon: 6,time:1500},function(){
      //               window.parent.location.reload();//刷新父页面
      //           });
      //       }else{
      //           layer.msg(data.msg,{icon: 5,time:1500});
      //       }
      //     })
      //     return false;
      //   }, function(index) {
      //     //按钮【按钮二】的回调
          
      //   });
      //   return false;
      // })
      
      
      //     $.post("/manage/order_contorller/getPreorderInfo",data.field,function(data){
              
      //         // if(data.error_code==0){
      //         //     layer.msg(data.msg,{icon: 6,time:1500},function(){
      //         //         window.parent.location.reload();//刷新父页面
      //         //     });
      //         // }else{
      //         //     layer.msg(data.msg,{icon: 5,time:1500});
      //         // }
      //     })
      // return false;
    })
})

