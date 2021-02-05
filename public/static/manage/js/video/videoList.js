var $,tab,skyconsWeather;
layui.config({
    base : "/static/manage/js/"
}).use(['laydate', 'laypage', 'layer', 'table', 'carousel', 'upload', 'element','bodyTab'], function(){
    var $ = layui.$, form = layui.form;
    laytpl = layui.laytpl;
    tab = layui.bodyTab({
        openTabNum : "50", //最大可打开窗口数量
        url : "json/navs.json" //获取菜单json地址
    });
    var laydate = layui.laydate //日期
  ,laypage = layui.laypage //分页
  layer = layui.layer //弹层
  ,table = layui.table //表格
  ,carousel = layui.carousel //轮播
  ,upload = layui.upload //上传
  ,element = layui.element; //元素操作

var id=$('#video_id').val();
    table.render({
        elem: '#test'
        ,url:'/manage/video_contorller/getVideo?id='+id
        ,height:632
        ,cols: [[
            {field:'learn',title:'学段'}
            ,{field:'grade',title:'年级'}
            ,{field:'Semester',title:'学期'}
            ,{field:'textbook',title:'教材版本'}
            ,{field:'name', title: '课程名称'}
            ,{field:'sname', title: '讲师'}
            ,{field:'price',title:'价钱'}
            ,{field:'Discount',title:'折扣'}
            ,{field:'DiscountPrice',title:'折扣价'}
            ,{field:'time',title: '添加日期',}
            ,{fixed: 'right', width: 225, align:'center', toolbar: '#barDemo'}
        ]]
        ,page: true
    });
  $('#sousuo').on('click', function(){
           var type = $(this).data('type');
            var demoReload = $('#demoReload');
            table.reload('testReload', {
                where: {
                    keyword: demoReload.val()
                }
            });      
   });
    laytpl.toDateString = function(nS){
        return new Date(parseInt(nS) * 1000).toLocaleString().replace(/:\d{1,2}$/,' ');
    };

    table.on('tool(demo)', function(obj){
        var data = obj.data;
        if(obj.event === 'shownews'){
            // var url="/manage/video_contorller/getOneVideo?id="+data.id;
            // tab.tabAdd($(this),url);
            layer.open({
                type: 2,
                title:"课程预览",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '100%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/video_contorller/getOneVideo?id="+data.id
            });
        } else if(obj.event === 'del'){
            layer.open({
                type: 2,
                title:"课程编辑",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/video_contorller/updateVideo?id="+data.id
            });
        }else if(obj.event === 'review'){
            var url="/manage/review_contorller/getReviewlist?id="+data.id;
            tab.tabAdd($(this),url);
            // layer.open({
            //     type: 2,
            //     title:"课程编辑",
            //     skin: 'layui-layer-demo', //样式类名
            //     closeBtn: 1, //不显示关闭按钮
            //     anim: 2,
            //     area: ['100%', '95%'],
            //     shadeClose: true, //开启遮罩关闭
            //     maxmin: true,
            //     content: "/manage/review_contorller/getReviewlist?id="+data.id});
        }else if(obj.event==='delvideo'){
                layer.confirm('真的删除行么', function(index){
                $.post("/manage/video_contorller/delvideo",{id:data.id},function(data){
                    if(data.error_code==0){
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
        addData: function(){
            layer.open({
                type:2,
                title:"添加文章",
                skin: 'layui-layer-molv', //加上边框
                area: ['100%','100%'], //宽高
                content: "/manage/video_contorller/videoAdd?id="+id,
                end:function(){
                    window.location.reload();
                }
            });
        }
    };

})
