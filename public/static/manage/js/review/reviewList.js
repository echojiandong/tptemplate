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

// var id=$('#pid').val();//原来的
var id=$('#view_id').val();
    table.render({
        elem: '#tests'
        ,url:'/manage/review_contorller/getReview?pid='+id
        ,height:632
        ,cols: [[
            {field:'id',title:'课时ID'}
            ,{field:'testclass', title: '章/单元名称'}
            ,{field:'audi',title:'是否试听'}
            ,{field:'className',title:'对应课程'}
            ,{field:'time',title: '添加日期',}
            ,{fixed: 'right',  toolbar: '#barDemo',title: '操作',}
        ]]
        ,page: true
    });
    
    // 搜索
    $('#sousuo').on('click', function(){
        var demoReload = $('#demoReload').val(); 
        if(!demoReload){
            layer.msg("查询内容不能为空",{time:1000});
            return false;
        }else{
            //请求接口根据用户搜索查找匹配项
            table.render({
                elem: '#tests'
                ,url:'/manage/review_contorller/getReview?keyword='+demoReload
                ,height:'full'
                ,cols: [[
                    {field:'id',title:'课时ID'}
                    ,{field:'testclass', title: '章/单元名称'}
                    ,{field:'audi',title:'是否试听'}
                    ,{field:'className',title:'对应课程'}
                    ,{field:'time',title: '添加日期',}
                    ,{fixed: 'right',  toolbar: '#barDemo',title: '操作',}
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
        if(obj.event === 'shownews'){
            layer.open({
                type: 2,
                title:"课程预览",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/video_contorller/getOneVideo?id="+data.id
            });
        } else if(obj.event === 'edit'){
            layer.open({
                type: 2,
                title:"章节编辑",
                skin: 'layui-layer-demo', //样式类名
                closeBtn: 1, //不显示关闭按钮
                anim: 2,
                area: ['100%', '95%'],
                shadeClose: true, //开启遮罩关闭
                maxmin: true,
                content: "/manage/review_contorller/updateChapter?id="+data.id
            });
        }else if(obj.event === 'review'){
            var url="/manage/review_contorller/getsectionlist?id="+data.id;
            tab.tabAdd($(this),url);
            // layer.open({
            //     type: 2,
            //     title:"课时管理",
            //     skin: 'layui-layer-demo', //样式类名
            //     closeBtn: 1, //不显示关闭按钮
            //     anim: 2,
            //     area: ['100%', '95%'],
            //     shadeClose: true, //开启遮罩关闭
            //     maxmin: true,
            //     content: "/manage/review_contorller/getsectionlist?id="+data.id});
        }else if(obj.event==='del'){
                layer.confirm('真的删除行么?(包括此章节下的所有课时)', function(index){
                $.post("/manage/review_contorller/delchapter",{id:data.id},function(data){
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
                title:"添加章",
                skin: 'layui-layer-molv', //加上边框
                area: ['100%','100%'], //宽高
                content: "/manage/review_contorller/addChapter?id="+id,
                end:function(){
                    window.location.reload();
                }
            });
        }
    };

})
