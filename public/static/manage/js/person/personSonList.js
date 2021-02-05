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
        ,url:'/manage/person_controller/getPersonSonList'
        ,height:'full'
        ,cellMinWidth:80
        ,cols: [[
             {type: 'checkbox', fixed: 'left'}
            ,{field:'id', title: 'ID',width:50}
            ,{field:'son_nickName', title: '用户昵称',width:100}
            ,{field:'grade', title: '年级',width:120}
            ,{field:'son_addtime', title: '添加时间'}
            ,{field:'act_status_name', title: '是否已经激活',width:120}
            ,{field:'gradeAuth', title: '用户的听课权限'}
            ,{field:'nickName', title: '父级用户'}
            ,{field:'username', title: '所属管理员',width:100}
            ,{field:'lock',  title: '操作', toolbar: '#barDemo'}
        ]]
        ,page: true
    });
    // $('#sousuo').on('click', function(){
    //     var type = $(this).data('type');
    //     var demoReload = $('#demoReload');
    //     table.reload('testReload', {
    //         where: {
    //             keyword: demoReload.val()
    //         }
    //     });      
    // });
    $('#sousuo').on('click', function(){
            //var type = $(this).data('type');
            var demoReload = $('#demoReload').val();
            if(!demoReload){
                //当用户没有输入内容直接点击查询的时候提示
                layer.msg("查询内容不能为空",{time:1000});
                return false;
            }else{
                //请求接口根据用户搜索查找卡号
                table.render({
                    elem: '#test'
                    ,url:'/manage/person_controller/getPersonSonList?keyword='+demoReload
                    ,height:'full'
                    ,cols: [[
                         {type: 'checkbox', fixed: 'left'}
                        ,{field:'id', title: 'ID',width:50}
                        ,{field:'son_nickName', title: '用户昵称',width:100}
                        ,{field:'grade', title: '年级',width:120}
                        ,{field:'son_addtime', title: '添加时间'}
                        ,{field:'act_status_name', title: '是否已经激活',width:120}
                        ,{field:'gradeAuth', title: '用户的听课权限'}
                        ,{field:'nickName', title: '父级用户'}
                        ,{field:'username', title: '所属管理员',width:100}
                        ,{field:'lock',  title: '操作', toolbar: '#barDemo'}
                    ]]
                    ,page: true
                });
            }   
    });
    laytpl.toDateString = function(nS){
        return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');
    };

    table.on('tool(demo)', function(obj){
        var data = obj.data;
        if(obj.event === 'editPersonSon'){
            layer.open({
                type: 2,
                title:"编辑子用户信息",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/person_controller/editPersonSon?id="+data.id
            });
        }
    });
    form.on('submit(editPerson)',function(data){
        if(!$('input').val()){
            layer.msg('信息不完整，无法提交', {icon: 5});
            return false;
        }
        $.post("/manage/person_controller/doEditPersonSon",data.field,function(data){
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
