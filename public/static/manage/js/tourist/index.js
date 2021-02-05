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

    table.render({
        elem: '#test'
        ,url:'/manage/tourist/getTouristList'
        ,height:'full'
        ,cellMinWidth:80
        ,cols: [[
             {type: 'checkbox', fixed: 'left'}
            ,{title: '序号',templet:'#xuhao'}
            ,{field:'nickName', title: '用户名'}
            ,{field:'phone', title: '手机号'}
            ,{field:'username', title: '所属管理员'}
            ,{field:'addtime', title: '注册时间'} 
            ,{field:'status_name', title: '用户状态'} 
            ,{field:'lock',  title: '操作', width:250, toolbar: '#barDemo'}
        ]]
        ,page: true
    });
    // table.reload('test');
  $('#sousuo').on('click', function(){
            //var type = $(this).data('type');
            var demoReload = $('#demoReload').val();
            if(!demoReload && !status && !grade_id){
                //当用户没有输入内容直接点击查询的时候提示
                layer.msg("查询内容不能为空",{time:1000});
                return false;
            }else{
                //请求接口根据用户搜索查找卡号
                table.render({
                    elem: '#test'
                    ,url:'/manage/tourist/getTouristList?keyword='+demoReload
                    ,height:'full'
                    ,cols: [[
                         {type: 'checkbox', fixed: 'left'}
                        ,{title: '序号',templet:'#xuhao'}
                        ,{field:'nickName', title: '用户名'}
                        ,{field:'phone', title: '手机号'}
                        ,{field:'username', title: '所属管理员'}
                        ,{field:'addtime', title: '注册时间'} 
                        ,{field:'status_name', title: '用户状态'} 
                        ,{field:'lock',  title: '操作', width:250, toolbar: '#barDemo'}
                    ]]
                    ,page: true
                });
            }   
    });
    // $('#add').on('click', function(){
    //     layer.open({
    //             type: 2,
    //             title:"生成卡号",
    //             skin: 'layui-layer-demo', //样式类名
    //             closeBtn: 1, //不显示关闭按钮
    //             anim: 2,
    //             area: ['100%', '95%'],
    //             shadeClose: true, //开启遮罩关闭
    //             maxmin: true,
    //             content: "/manage/tourist/allotAllPerson?id="+
    //     });
    // });
    laytpl.toDateString = function(nS){
        return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');
    };
    $('#add').on('click', function(){
        var id = table.checkStatus('test')['data'];
        var idList = [];
        for(x in id){
            idList.push(id[x].id);
        }
        if(idList == ''){
            layer.msg('选择的用户不能为空', {icon: 5,time:1500});
            return false;
        }
        layer.open({
                type: 2,
                title:"生成卡号",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['40%', '40%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/tourist/allotAllPerson?id="+idList
        });
    })
    table.on('tool(demo)', function(obj){
        var data = obj.data;
        var count = obj.count;
        if(obj.event === 'allotPerson'){
            layer.open({
                type: 2,
                title:"管理员分配",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['40%', '40%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/tourist/allotPerson?id="+data.id
            });
        }
    });
    form.on('submit(editCode)',function(data){
        if(!$('input').val()){
            layer.msg('信息不完整，无法提交', {icon: 5});
            return false;
        }
        $.post("/manage/tourist/doAllotPerson",data.field,function(data){
            if(data.error_code==0){
                layer.msg(data.msg, {icon: 6,time:1500},function(){
                    window.parent.location.reload();//刷新父页面
                });
            }else{
                layer.msg(data.msg, {icon: 5,time:1500});
            }
        });
    })
})
