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
    laytpl.toOrderCheck=function(ns){
        if(ns==0){
           return '待审核';
       }else if(ns==2){
           return '审核通过';
       }else if(ns==3){
           return '审核失败';
       }
    };
    // laytpl.toPayment=function(ns){
    //     if(ns==1){
    //         return '支付宝支付';
    //     }else if(ns==2){
    //         return '支付宝扫码支付';
    //     }else if(ns==3){
    //         return '微信支付';
    //     }else if(ns==4){
    //         return '微信扫码支付';
    //     }
    // };

    table.render({
        elem: '#test'
        ,url:'/manage/recycle_contorller/getRecyclecOrder'
        ,cellMinWidth:80
        ,totalRow: true //开启合计行
        ,cols: [[
            {field:'order', title: '订单号',sort: true,width:200, totalRowText: '合计'}
            ,{field:'username', title: '代理商',width:100}
            ,{field:'phone', title: '代理商联系方式'}
            //,{field:'money', title: '订单金额',width:100,totalRow: true}
            ,{field:'state', title: '是否已支付',width:100,templet: '#state'}
            ,{field:'strtime',  title: '下单时间'}
            ,{field:'orderCheck_name',  title: '订单审核状态'}
            ,{fixed:'right',  title: '操作', align:'center', toolbar: '#barDemo'}
        ]]
        ,id: 'testReload'
        ,page: true
    });
     $('#sousuo').on('click', function(){
       var type = $(this).data('type');
        var demoReload = $('#demoReload');
        table.reload('testReload', {
            where: {
                keyword: demoReload.val(),
                state: $("[name='state']").val(),
                orderCheck:$("[name='orderCheck']").val()
            }
        });      
   });
    $('#rebate').on('click', function(){
        $("#layui-btn").css('display','block');
    });
    table.on('tool(demo)', function(obj){
        var data = obj.data;
        if(obj.event === 'edit'){
            layer.confirm('真的还原吗？', function(index){
                    $.post("/manage/recycle_contorller/restoreOrder",{id:data.id},function(data){
                    if(data.error_code==0){
                        layer.msg(data.msg,{time:400},function(){
                            obj.del();
                            layer.close(index);
                        });

                    }else{
                        layer.msg(data.msg,{time:400});
                    }
                })
            });
        }else if(obj.event === 'delOrder'){
            layer.confirm('真的删除吗？', function(index){
                    $.post("/manage/recycle_contorller/delOrder",{id:data.id},function(data){
                    if(data.error_code==0){
                        layer.msg(data.msg,{time:400},function(){
                            obj.del();
                            layer.close(index);
                        });

                    }else{
                        layer.msg(data.msg,{time:400});
                    }
                })

            });
        }
    });

    $('.demoTable .layui-btn').on('click', function(){
        var type = $(this).data('type');
        active[type] ? active[type].call(this) : '';
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