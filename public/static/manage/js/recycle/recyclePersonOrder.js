layui.use(['form','layer','laypage','table','jquery','laytpl'], function(){
    var $ = layui.$, form = layui.form;
    table = layui.table;
    laytpl = layui.laytpl;
    var type = $("#type").val(); // 获取订单类型：1我的订单，2全部用户订单
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
        ,url:'/manage/recycle_contorller/getPersonOrderList?type='+1
        ,totalRow: true //开启合计行
        ,height:'full'
        ,cols: [[
            {field:'order', title: '订单号',sort: true, totalRowText: '合计', width:160}
            ,{field:'nickName', title: '用户昵称', width:100}
            ,{field:'phone', title: '用户联系方式', width:120}
            ,{field:'payMoney', title: '支付金额',totalRow: true, width:90}
            ,{field:'state', title: '是否已支付',templet: '#state', width:100}
            ,{field:'strtime',  title: '下单时间',templet: '#timestamp', sort:true}
            // ,{field:'coursePackage',  title: '订单类型'}
            ,{field:'orderCheck',  title: '订单审核状态',templet:'#orderCheck'}
            ,{field:'username',  title: '所属管理员', width:100}
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
        console.log(data);
        if(obj.event === 'edit'){
            layer.open({
                type:2,
                skin: 'layui-layer-molv', //加上边框
                area: ['100%', '95%'],//宽高
                content: "/manage/order_contorller/getOneOrderPerson?id="+data.id
            });
        } else if(obj.event === 'editOrderPerson'){
            layer.confirm('真的还原吗？', function(index){
                    $.post("/manage/recycle_contorller/restorePersonOrder",{id:data.id},function(data){
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
        } else if(obj.event === 'delOrderPerson'){
            layer.confirm('真的删除吗？', function(index){
                    $.post("/manage/recycle_contorller/delPersonOrder",{id:data.id},function(data){
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
});