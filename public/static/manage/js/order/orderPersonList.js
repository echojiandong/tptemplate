layui.use(['form','layer','laypage','table','jquery','laytpl'], function(){
    var $ = layui.$, form = layui.form;
    table = layui.table;
    laytpl = layui.laytpl;
    var type = $("#type").val(); // 获取订单类型：1我的订单，2全部用户订单
    var isAdmin = $("#isAdmin").val(); // 获取是否是admin 登录 true 是
    var username = '所属员工';
    if (isAdmin) {
        username = '所属管理员';
    }
//数字前置补零
    laytpl.toDateString = function(nS){
        return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');
    };
    laytpl.toState=function(ns){
        if(ns==1){
           return '待支付';
       }else if(ns==2){
           return '已支付';
       }else if(ns==3){
           return '支付失败';
       }else if(ns==4){
            return '取消支付';
       }
    };
    laytpl.toOrderCheck=function(ns){
        if(ns==1){
           return '待审核';
       }else if(ns==2){
           return '审核通过';
       }else if(ns==3){
           return '审核失败';
       }
    };
    laytpl.toGrade_id=function(ns){
        if(ns==1){
           return '一年级';
       }else if(ns==2){
           return '二年级';
       }else if(ns==3){
           return '三年级';
       }else if(ns==4){
           return '四年级';
       }else if(ns==5){
            return '五年级';
       }else if(ns==6){
            return '六年级';
       }else if(ns==7){
            return '七年级';
       }else if(ns==8){
            return '七年级';
       }else if(ns==9){
            return '九年级';
       }else if(ns==10){
            return '高一';
       }else if(ns==11){
            return '高二';
       }else if(ns==12){
            return '高三';
       }
    };
    table.render({
        elem: '#test'
        ,url:'/manage/order_contorller/getOrderPersonList?type='+type+'&isAdmin='+isAdmin
        ,totalRow: true //开启合计行
        ,height:'full'
        ,cols: [[
            {field:'order', title: '订单号',sort: true, totalRowText: '合计', width:160}
            ,{field:'nickName', title: '用户昵称', width:100}
            ,{field:'phone', title: '用户联系方式', width:120}
            ,{field:'payMoney', title: '支付金额',totalRow: true, width:90}
            ,{field:'state', title: '是否已支付',templet: '#state', width:100}
            ,{field:'strtime',  title: '下单时间',templet: '#timestamp', sort:true, width:168}
            ,{field:'medianame', title: '所属媒体', width:120, align:'center'}
            // ,{field:'coursePackage',  title: '订单类型'}
            ,{field:'orderCheck',  title: '订单审核状态',templet:'#orderCheck', width:120}
            ,{field:'username',  title: username, width:100}
            ,{field:'num', title:'课程数量'}
            ,{fixed:'right',  title: '操作',width:200, toolbar: '#barDemo'}
        ]]
        ,id: 'testReload'
        ,page: true
    });
     $('#sousuo').on('click', function(){
       var type = $(this).data('type');
        var demoReload = $('#demoReload');

        var orderStartTime = $("[name='orderStartTime']").val();
        var orderEndTime = $("[name='orderEndTime']").val();

        if (orderEndTime && orderEndTime < orderStartTime) {
            layer.msg('搜索结束时间不能小于开始时间',{icon:2,time:2000})
            return false;
        }

        table.reload('testReload', {
            where: {
                keyword: demoReload.val(),
                state: $("[name='state']").val(),
                grade_id:$("[name='grade_id']").val(),
                orderCheck:$("[name='orderCheck']").val(),
                orderStartTime:$("[name='orderStartTime']").val(),
                orderEndTime:$("[name='orderEndTime']").val(),
                uid:$("[name='uid']").val(),
                is_forbidden:$("[name='is_forbidden']").val(),
                to_media:$("[name='to_media']").val()
            },
            page: {
                curr: 1
            }
        });      
   });
    $('#rebate').on('click', function(){
        $("#layui-btn").css('display','block');
    });
    table.on('tool(demo)', function(obj){
        var data = obj.data;
        if(obj.event === 'edit'){
            layer.open({
                type:2,
                skin: 'layui-layer-molv', //加上边框
                area: ['100%', '95%'],//宽高
                content: "/manage/order_contorller/getOneOrderPerson?id="+data.id
            });
        } else if(obj.event === 'editOrderPerson'){
            
            layer.open({
                type: 2,
                title:"修改订单信息",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/order_contorller/editOrderPerson?id="+data.id+'&type='+data.type
            });
        } else if(obj.event === 'delOrderPerson'){
            layer.confirm('真的确定作废吗？', function(index){
                    $.post("/manage/order_contorller/delOrderPerson",{id:data.id},function(data){
                    if(data.error_code==0){
                        layer.msg(data.msg,{time:400},function(){
                            obj.del();
                            // layer.close(index);
                            table.reload('testReload');
                        });

                    }else{
                        layer.msg(data.msg,{time:400});
                    }
                })

            });
        } else if(obj.event === 'sendDelete'){
            layer.confirm('确定要发起作废吗？', function(index){
                    $.post("/manage/order_contorller/sendOrderDelete",{id:data.id},function(data){
                    if(data.error_code==0){
                        layer.msg(data.msg,{time:400},function(){
                            obj.del();
                            // layer.close(index);
                            table.reload('testReload');
                        });

                    }else{
                        layer.msg(data.msg,{time:400});
                    }
                })

            });
        } else if(obj.event === 'cancelOrderPerson'){

            if (data.orderCheck != 2 && data.state != 2) {
                layer.msg('没有审核通过不能退费',{time:400});
                return false;
            }
            var html = '';
            html += '<table class="layui-table">'
            html += '  <tbody>'
            html += '    <tr>'
            html += '      <td>订单总价格</td>'
            html += '      <td>'+ data.money+'</td>'
            html += '    </tr>'
            html += '    <tr>'
            html += '      <td>优惠价格</td>'
            html += '      <td>'+ data.discount_price +'</td>'
            html += '    </tr>'
            html += '    <tr>'
            html += '      <td  style="font-size:16px; color:red;">实际支付价格</td>'
            html += '      <td style="font-size:16px;color:red;font-weight: 700;">'+ data.payMoney +'</td>'
            html += '    </tr>'
            html += '    <tr>'
            html += '      <td>现退款金额</td>'
            html += '      <td><input type="text" class="layui-input" id="refund" name="refund" value=""></td>'
            html += '    </tr>'
            html += '    <tr>'
            html += '      <td>退款理由</td>'
            html += '      <td><textarea name="reason" style="margin: 0px; height: 70px; width: 450px;"></textarea></td>'
            html += '    </tr>'
            html += '  </tbody>'
            html += '</table>'

            layer.confirm(html, {
                area: ['50%', '80%'],
                title:'请确认订单信息',
                btn: ['确定', '取消'] //可以无限个按钮
            }, function(index, layero) {
               
                var refund = $("#refund").val();
                if (refund > data.payMoney) {
                    layer.msg('退费金额不能大于购买实际支付金额',{icon: 5,time:1500});
                }
                //按钮【按钮一】的回调

                $.post("/manage/order_contorller/personOrderRefund",{refund:refund, id:data.id},function(data){
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
        }
    });

    $('.demoTable .layui-btn').on('click', function(){
        var type = $(this).data('type');
        active[type] ? active[type].call(this) : '';
    });

    active = {
        addOrder: function(){
            layer.open({
                type:2,
                title:"添加用户卡号订单",
                skin: 'layui-layer-molv', //加上边框
                area: ['100%','100%'], //宽高
                content: "/manage/order_contorller/addOrderPerson",
                end:function(){
                    window.location.reload();
                }
            });
        }
    }
    $('#addClassOrder').on('click', function(){
        layer.open({
                type: 2,
                title:"生成卡号",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/order_contorller/addClassOrder"
        });
    });
});