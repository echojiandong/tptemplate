layui.use(['laydate', 'laypage', 'layer', 'table', 'carousel', 'upload', 'element'], function(){
    var $ = layui.$, form = layui.form;
    laytpl = layui.laytpl;

    var laydate = layui.laydate //日期
  ,laypage = layui.laypage //分页
  layer = layui.layer //弹层
  ,table = layui.table //表格
  ,carousel = layui.carousel //轮播
  ,upload = layui.upload //上传
  ,element = layui.element; //元素操作

    var type = $("#type").val(); // 获取用户类型：1我的用户，2全部用户
    var isAdmin = $("#isAdmin").val(); // 获取当前是不是admin登录
    var username = '所属员工';
    if (isAdmin) {
        username = '所属代理商';
    } 
    table.render({
        elem: '#test'
        ,url:'/manage/person_controller/getPersonList?type='+ type+'&isAdmin='+isAdmin
        ,height:'full'
        ,cols: [[
             {type: 'checkbox', fixed: 'left'}
            ,{title: '序号', width:80, align:'center',templet:'#xuhao'}
            ,{field:'nickName', title: '用户昵称', templet: '#usernameTpl', width:150, align:'center'}
            ,{field:'phone', title: '手机号码', width:120, align:'center'}
            ,{field:'city', title: '城市', width:150, align:'center'} 
            ,{field:'province', title: '省份', width:100, align:'center'}
            ,{field:'addtime', title: '添加时间', width:168, align:'center'}
            ,{field:'medianame', title: '所属媒体', width:120, align:'center'}
            ,{field:'statusTxt', title: '用户学习状态',width:100, align:'center'}
            ,{field:'actstatusTxt', title: '用户状态',width:100, align:'center'}
            ,{field:'username', title: username, width:100, align:'center'}
            ,{field:'lock',  title: '操作', toolbar: '#barDemo', align:'center'}
        ]]
        ,page: true
    });
    // table.reload('test');
    $('#sousuo').on('click', function(e) {
        //var type = $(this).data('type');
        var demoReload = $('#demoReload').val();
        var orderStartTime = $("[name='orderStartTime']").val();
        var orderEndTime = $("[name='orderEndTime']").val()
        var province = $("[name='province']").val()
        var city = $("[name='city']").val()
        var county = $("[name='county']").val()
        var uid = $("[name='uid']").val();
        var status = $("[name='status']").val()
        var act_status = $("[name='act_status']").val()
        var to_media = $("[name='to_media']").val()

        if (orderEndTime && orderEndTime < orderStartTime) {
            layer.msg('搜索结束时间不能小于开始时间',{icon:2,time:2000})
            return false;
        }
        // if(!demoReload && !act_status){
        //     //当用户没有输入内容直接点击查询的时候提示
        //     layer.msg("查询内容不能为空",{time:1000});
        //     return false;
        // }else{
            //请求接口根据用户搜索查找卡号
            var param = '&province='+province+'&city='+city+'&county='+county+'&uid='+uid+'&status='+status+'&type='+type+'&act_status='+act_status+'&isAdmin='+isAdmin+'&to_media='+to_media;

            table.render({
                elem: '#test'
                ,url:'/manage/person_controller/getPersonList?keyword='+demoReload+'&orderStartTime='+orderStartTime+'&orderEndTime='+orderEndTime+param
                ,height:'full'
                    ,cols: [[
                    {type: 'checkbox', fixed: 'left'}
                    ,{field:'id', title: 'ID', width:80, align:'center'}
                    ,{field:'nickName', title: '用户昵称', templet: '#usernameTpl', width:150, align:'center'}
                    ,{field:'phone', title: '手机号码', width:120, align:'center'}
                    ,{field:'city', title: '城市', width:150, align:'center'} 
                    ,{field:'province', title: '省份', width:100, align:'center'}
                    ,{field:'addtime', title: '添加时间', width:168, align:'center'}
                    ,{field:'medianame', title: '所属媒体', width:120, align:'center'}
                    ,{field:'statusTxt', title: '用户学习状态',width:100, align:'center'}
                    ,{field:'actstatusTxt', title: '用户状态',width:100, align:'center'}
                    ,{field:'username', title: username, width:100, align:'center'}
                    ,{field:'lock',  title: '操作', width: 170, toolbar: '#barDemo', align:'center'}
                ]]
                ,page: true
            });
        // }   
    });
    $('#addPerson').on('click', function(){
        layer.open({
                type: 2,
                title:"生成用户与订单",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/person_controller/addPerson"
        });
    });
    laytpl.toDateString = function(nS){
        return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');
    };

    table.on('tool(demo)', function(obj){
        var data = obj.data;
        
        if(obj.event === 'editPerson'){
            layer.open({
                type: 2,
                title:"用户信息编辑",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/person_controller/editPerson?id="+data.id
            });
        }else if(obj.event==='forbidden'){
            var act_status = data.act_status;
            var msg = '真的要禁用吗？';
            if (act_status == 3) {
                msg = '真的要解禁吗？';
            }
            layer.confirm(msg, function(index){
                $.post("/manage/person_controller/forbidden",{id:data.id},function(data){
                    if(data.error_code==0){
                        layer.msg(data.msg,{time:400},function(){
                            table.reload('test');
                        });
                        
                    }else{
                        layer.msg(data.msg,{time:400});
                    }
                })
               
            });

        }else if(obj.event === 'addPersonSon'){
            layer.open({
                type: 2,
                title:"添加子用户",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/person_controller/addPersonSon?id="+data.id
            });
        }else if(obj.event === 'personInfo'){
            layer.open({
                type: 2,
                title:"用户信息详情",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/person_controller/personInfo?id="+data.id
            });
        } else if (obj.event === 'abnormal') {
            layer.open({
                type: 2,
                title: "账号异常详情",
                skin: 'layui-layer-demo',
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/person_controller/abnormal?id="+data.id

            })
        } else if (obj.event === 'isRenew') {
            layer.open({
                type: 2,
                title:"用户续费",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/person_controller/isRenew?id="+data.id
            });
        } else if (obj.event === 'forban') {
            layer.confirm('真的要回复用户使用吗？', function(index){
                $.post("/manage/person_controller/forban",{id:data.id},function(data){
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
        } else if (obj.event === 'followUp') {
            layer.confirm('确定要跟进用户吗？', function(index){
                $.post("/manage/person_controller/followUp",{id:data.id},function(data){
                    if(data.error_code==0){
                        layer.msg(data.msg,{time:400},function(){
                            obj.del();
                            layer.close(index);
                            window.parent.location.reload();//刷新父页面
                        });
                    }else{
                        layer.msg(data.msg,{time:400});
                    }
                })
            });
        }
    });


    // $('.demoTable .layui-btn').on('click', function(){
    //     var type = $(this).data('type');
    //     active[type] ? active[type].call(this) : '';
    // });

})
