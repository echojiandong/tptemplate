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
        ,url:'/manage/datafile_controller/getList'
        ,height:'full'
        ,cols: [[
            {title: '序号', width:80, align:'center',templet:'#xuhao'}
            ,{field:'name', title: '名称', width:150, align:'center'}
            ,{field:'grade', title: '年级', width:150, align:'center'}
            ,{field:'stmesterText', title: '学期', width:150, align:'center'}
            ,{field:'video_className', title: '课程名称', width:150, align:'center'}
            ,{field:'addTime', title: '添加时间', width:168, align:'center'}
            ,{field:'lock',  title: '操作', toolbar: '#barDemo', align:'center'}
        ]]
        ,page: true
    });
    // table.reload('test');
    $('#sousuo').on('click', function(e) {
        //var type = $(this).data('type');
        var keyword = $('#keyword').val();
        var orderStartTime = $("[name='orderStartTime']").val();
        var orderEndTime = $("[name='orderEndTime']").val()
        var province = $("[name='province']").val()
        var city = $("[name='city']").val()
        var county = $("[name='county']").val()
        var uid = $("[name='uid']").val();
        var status = $("[name='status']").val()
        var act_status = $("[name='act_status']").val()
        var to_media = $("[name='to_media']").val()

        //请求接口根据用户搜索查找卡号
        var param = 'keyword='+ keyword;

        table.render({
            elem: '#test'
            ,url:'/manage/datafile_controller/getList?'+param
            ,height:'full'
                ,cols: [[
                    {title: '序号', width:80, align:'center',templet:'#xuhao'}
                    ,{field:'name', title: '名称', width:150, align:'center'}
                    ,{field:'grade', title: '年级', width:150, align:'center'}
                    ,{field:'stmesterText', title: '学期', width:150, align:'center'}
                    ,{field:'video_className', title: '课程名称', width:150, align:'center'}
                    ,{field:'addTime', title: '添加时间', width:168, align:'center'}
                    ,{field:'lock',  title: '操作', toolbar: '#barDemo', align:'center'}
            ]]
            ,page: true
        });
    });


    $('#add').on('click', function(){
        layer.open({
                type: 2,
                title:"添加文件或新闻",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/datafile_controller/add"
        });
    });
    laytpl.toDateString = function(nS){
        return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');
    };

    table.on('tool(demo)', function(obj){
        var data = obj.data;
        
        if(obj.event == 'edit'){
            layer.open({
                type: 2,
                title:"用户信息编辑",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/datafile_controller/edit?id="+data.id
            });
        }else if(obj.event =='delete'){
            var msg = '真的要删除吗？';
            layer.confirm(msg, function(index){
                $.post("/manage/datafile_controller/delete",{id:data.id},function(data){
                    if(data.error_code==0){
                        layer.msg(data.msg,{time:400},function(){
                            table.reload('test');
                        });
                        
                    }else{
                        layer.msg(data.msg,{time:400});
                    }
                })
               
            });

        } else if(obj.event === 'info'){
            layer.open({
                type: 2,
                title:"用户信息详情",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/datafile_controller/info?id="+data.id
            });
        }  
    });
})
