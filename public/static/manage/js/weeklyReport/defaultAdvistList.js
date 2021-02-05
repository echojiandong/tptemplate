layui.use(['laydate', 'laypage', 'layer', 'table', 'carousel', 'upload', 'element'], function(){
    var $ = layui.$, form = layui.form;
    laytpl = layui.laytpl;

    var laydate = layui.laydate //日期
  ,laypage = layui.laypage //分页
  ,layer = layui.layer //弹层
  ,table = layui.table //表格
  ,carousel = layui.carousel //轮播
  ,upload = layui.upload //上传
  ,element = layui.element; //元素操作

    table.render({
        elem: '#test'
        ,url:'/manage/WeeklyReport_controller/getDefaultAdviseList'
        ,height:'full'
        ,cellMinWidth:80
        ,cols: [[
             {field:'id', title: 'ID'}
             ,{field:'content', title: '内容'}
             ,{field:'grade', title: '年级'}
             ,{field:'timeSole', title: '视频观看时间段'}
             ,{field:'scoreSole', title: '测试平均分段'}
             ,{field:'createTime', title: '添加时间'}
             ,{field:'lock',  title: '操作', width:250, toolbar: '#barDemo'}
        ]]
        ,page: true
    });
    // table.reload('test');
  // $('#sousuo').on('click', function(){
  //           //var type = $(this).data('type');
  //           var demoReload = $('#demoReload').val();
  //           if(!demoReload){
  //               //当用户没有输入内容直接点击查询的时候提示
  //               layer.msg("查询内容不能为空",{time:1000});
  //               return false;
  //           }else{
  //               //请求接口根据用户搜索查找卡号
  //               table.render({
  //                   elem: '#test'
  //                   ,url:'/manage/WeeklyReport_controller/getDefaultAdviseList?keyword='+demoReload
  //                   ,height:'full'
  //                   ,cols: [[
  //                       ,{field:'person_id', title: 'ID'}
		// 	            ,{field:'nickName', title: '姓名'}
		// 	            ,{field:'phone', title: '手机'}
		// 	            ,{field:'is_have', title: '老师是否提交建议'}
		// 	            ,{field:'studyTime', title: '本周学习总时长'}
		// 	            ,{field:'minFraction', title: '本周最低分'}
		// 	            ,{field:'maxFraction', title: '本周最高分'}
  //                       ,{field:'lock',  title: '操作', width:250, toolbar: '#barDemo'}
  //                   ]]
  //                   ,page: true
  //               });
  //           }   
  //   });
    $('#add').on('click', function(){
        layer.open({
                type: 2,
                title:"添加默认建议",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/WeeklyReport_controller/add"
        });
    });
    laytpl.toDateString = function(nS){
        return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');
    };

    table.on('tool(demo)', function(obj){
        var data = obj.data;
        var count = obj.count;
        if(obj.event === 'edit'){
            layer.open({
                type: 2,
                title:"编辑",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/WeeklyReport_controller/update?id="+data.id
            });
        }else if(obj.event === 'del'){
            layer.confirm('真的删除行么', function(index){
                $.post("/manage/WeeklyReport_controller/del",{id:data.id},function(data){
                    if(data.error_code==1){
                        layer.msg("删除成功",{time:400},function(){
                            obj.del();
                            layer.close(index);
                        });
                    }else{
                        layer.msg("删除失败",{time:400});
                    }
                })
            });
        }
    });


    $('.demoTable .layui-btn').on('click', function(){
        var type = $(this).data('type');
        active[type] ? active[type].call(this) : '';
    });

    active = {
        addCode: function(){
            layer.open({
                type:2,
                title:"生成卡号",
                skin: 'layui-layer-molv', //加上边框
                area: ['100%','100%'], //宽高
                content: "/manage/code_contorller/addCode",
                end:function(){
                    window.location.reload();
                }
            });
        }
    };
    //  active = {
    //     output: function(){
    //         window.location.href="/manage/code_contorller/output";
    //     }
    // }

})
