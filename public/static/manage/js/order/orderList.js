layui.use(['form','layer','laypage','table','jquery','laytpl'], function(){
    var $ = layui.$, form = layui.form;
    table = layui.table;
    laytpl = layui.laytpl;
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
    // laytpl.toOrderCheck=function(ns){
    //     if(ns==1){
    //        return '待审核';
    //    }else if(ns==2){
    //        return '审核通过';
    //    }else if(ns==3){
    //        return '审核失败';
    //    }
    // };
    laytpl.toOrder_type=function(ns){
        if(ns==1){
           return '正式课';
       }else if(ns==2){
           return '试听课';
       }
    };
    var type = $('#type').val();
    var check = $('#check').val();
    table.render({
        elem: '#test'
        ,url:'/manage/order_contorller/getOrderList?type='+type+'&check='+check
        ,cellMinWidth:80
        ,totalRow: true //开启合计行
        ,cols: [[
            {field:'order', title: '订单号',sort: true,width:200, totalRowText: '合计'}
            ,{field:'username', title: '代理商',width:100}
            ,{field:'phone', title: '代理商联系方式'}
            //,{field:'money', title: '订单金额',width:100,totalRow: true}
            ,{field:'state', title: '是否已支付',width:100,templet: '#state'}
            ,{field:'strtime',  title: '下单时间'}
            ,{field:'order_type',  title: '订单类型',templet:'#order_type'}
            ,{field:'orderCheck_name',  title: '订单审核状态'}
            ,{fixed:'right',  title: '操作', align:'center', toolbar: '#barDemo'}
        ]]
        ,id: 'testReload'
        ,page: true
    });
     $('#sousuo').on('click', function(){
       // var type = $(this).data('type');
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
                orderCheck:$("[name='orderCheck']").val(),
                orderStartTime:$("[name='orderStartTime']").val(),
                orderEndTime:$("[name='orderEndTime']").val(),
                type:type,
                check:check,
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
                area: ['50%','100%'], //宽高
                content: "/manage/order_contorller/getOneOrder?id="+data.id
            });
        }else if(obj.event === 'editOrder'){
            layer.open({
                type: 2,
                title:"编辑订单",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/order_contorller/editOrder?id="+data.id+'&type='+data.type+'&check='+data.check
            });
        }else if(obj.event === 'delOrder'){
            layer.confirm('真的作废吗？', function(index){
                $.post("/manage/order_contorller/delOrder",{id:data.id},function(data){
                    if(data.error_code==0){
                        layer.msg(data.msg,{time:1400,icon: 6},function(){
                            obj.del();
                            layer.close(index);
                        });

                    }else{
                        layer.msg(data.msg,{time:1400,icon: 5});
                    }
                })

            });
        }
    });

    $('.demoTable .layui-btn').on('click', function(){
        var type = $(this).data('type');
        active[type] ? active[type].call(this) : '';
    });

    // active = {
    //     addCode: function(){
    //         layer.open({
    //             type:2,
    //             title:"生成卡号",
    //             skin: 'layui-layer-molv', //加上边框
    //             area: ['100%','100%'], //宽高
    //             content: "/manage/code_contorller/addCode",
    //             end:function(){
    //                 window.location.reload();
    //             }
    //         });
    //     }
    // };
     active = {
        addOrder: function(){
            layer.open({
                type:2,
                title:"生成正式课订单",
                skin: 'layui-layer-molv', //加上边框
                area: ['100%','100%'], //宽高
                content: "/manage/order_contorller/addOrder",
                end:function(){
                    window.location.reload();
                }
            });
        }
    }
    $('#addTextOrder').on('click', function(){
        layer.open({
            type:2,
            title:"生成试听课订单",
            skin: 'layui-layer-molv', //加上边框
            area: ['100%','100%'], //宽高
            content: "/manage/order_contorller/insertTextCourse",
            end:function(){
                window.location.reload();
            }
        });
    });
        
     $('#quickAddOrder').on('click', function(){
        var num = $('#num').val();
        if(num != '' && num != 0){
            $.post("/manage/order_contorller/quickAddOrder",{num:num},function(data){
                if(data.error_code==0){
                    layer.msg(data.msg,{time:1500},function(){
                        // obj.del();
                        // layer.close(index);
                    });

                }else{
                    layer.msg(data.msg,{time:1500});
                }
            })
        }else{
            layer.msg('请输入订单数量！',{time:1000});
        }
    });


}).define(['jquery', 'layer','form','element'],function(exports){
    $=layui.jquery;
    exports('addbanner', function(){
        layer.open({
            type:2,
            skin: 'layui-layer-molv', //加上边框
            area: ['100%','100%'], //宽高
            content: [link],
            end: function(){
                location.reload();
            }
        });
    });
    exports('edit', function(id){
        layer.open({
            type:2,
            skin: 'layui-layer-molv', //加上边框
            area: ['100%','100%'], //宽高
            content: editlink+"?id="+id,
            end: function(){
                location.reload();
            }
        });
    });

    exports('bigimg', function(e){
        var img =$(e).attr('src');
        console.log(img);
        layer.open({
            type: 1,
            title: false,
            closeBtn: 1,
            area: ['60%','95%'],
            skin: 'layui-layer-nobg', //没有背景色
            shadeClose: true,

            content: '<img src="'+img+'" style="width: 100%; height: auto" />'
        });

    });
});