<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:61:"D:\tp\ywd100\application/../Template/manage/tomedia\list.html";i:1598606566;}*/ ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>销售渠道</title>
	<meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="format-detection" content="telephone=no">
	<link rel="stylesheet" href="/static/manage/layui//css/layui.css" media="all" />
</head>
<body class="childrenBody" style="margin-top: 1em">
<div class="demoTable">
	<button class="layui-btn" data-type="addData">添加</button>
</div>
<div class="layui-form  demoTable">
	媒体名称：
    <div class="layui-inline padding">
        <input class="layui-input" name="keyword" id="demoReload"  placeholder="搜索名称">
	</div>
	<div class="layui-inline padding">
		开始：
		<div class="layui-input-inline">
			<input type="text" class="layui-input" id="orderStartTime" autocomplete="off" name="orderStartTime" placeholder="yyyy-MM-dd">
		</div>
	</div>
	<div class="layui-inline padding">
		结束：
		<div class="layui-input-inline">
			<input type="text" class="layui-input" id="orderEndTime" autocomplete="off" name="orderEndTime" placeholder="yyyy-MM-dd">
		</div>
	</div>
	<?php if($isAdmin): ?>
	<div class="layui-inline padding">
		所属代理商：
		<div class="layui-input-inline" style="width: 120px;">
			<select name="agent_id"  class="newsLook layui-input" lay-search=''>
				<option value="">全部</option>
				<?php if($agentList): if(is_array($agentList) || $agentList instanceof \think\Collection || $agentList instanceof \think\Paginator): $i = 0; $__LIST__ = $agentList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
					<option value="<?php echo $v['uid']; ?>"><?php echo $v['username']; ?></option>
					<?php endforeach; endif; else: echo "" ;endif; endif; ?>
			</select>
		</div>
	</div>
	<?php endif; ?>
	<button class="layui-btn" data-type="reload" id="sousuo" style="margin-left: 5px;">搜索</button>
</div>
<table class="layui-hide" id="test" lay-filter="test" ></table>


<script type="text/html" id="barDemo">
	<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="editschool">编辑</a>
	<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delschool">删除</a>
</script>

<script type="text/html" id="zizeng">
	{{d.LAY_TABLE_INDEX+1}}
</script>

<script type="text/javascript" src="/static/manage/layui//layui.js"></script>
</body>
</html>
<script type="text/javascript">
	
	layui.use(['layer', 'laydate', 'table', 'form'], function(){
		var table = layui.table
			,laydate = layui.laydate
			,layer = layui.layer
			,form = layui.form
			,$ = layui.$;

		var myDate = new Date();//获取系统当前时间
		var nowTime = myDate.toLocaleDateString();
		//执行一个laydate实例
		laydate.render({
			elem: '#orderStartTime', //指定元素
			max: nowTime,
		});
		
		//执行一个laydate实例
		laydate.render({
			elem: '#orderEndTime', //指定元素
			max: nowTime
		});

		// layarea.render({
		// 	elem: '#area-picker',
		// 	change: function (res) {
				
		// 	}
		// });
		table.render({
	        elem: '#test'
	        ,url:'/manage/tomedia/tomediatable'
	        ,height:'full'
	        ,cellMinWidth:80
	        ,cols: [[
				// {field:'id', title: 'ID'}
				{field:'zizeng', width:80, title: '序号',fixed: 'left',templet:'#zizeng'}
	            ,{field:'name', title: '媒体名称'}
	            ,{field:'username', title: '所属代理'}
	            ,{field:'count', title: '用户量', width:80}
	            ,{fixed: 'right', width: 165, align:'center', toolbar: '#barDemo'}
	        ]]
	        ,page: true
		});
		
		$('#sousuo').on('click', function(e) {
			var keyword = $("[name='keyword']").val();
			var orderStartTime = $("[name='orderStartTime']").val();
			var orderEndTime = $("[name='orderEndTime']").val()
			var agent_id = $("[name='agent_id']").val();

			if (orderEndTime && orderEndTime < orderStartTime) {
				layer.msg('搜索结束时间不能小于开始时间',{icon:2,time:2000})
				return false;
			}

			var param = 'keyword='+keyword +'&orderStartTime='+orderStartTime + '&orderEndTime='+orderEndTime + '&agent_id='+agent_id;
			table.render({
				elem: '#test'
				,url:'/manage/tomedia/tomediatable?'+param
				,height:'full'
				,cellMinWidth:80
				,cols: [[
					// {field:'id', title: 'ID'}
					{field:'zizeng', width:80, title: '序号',fixed: 'left',templet:'#zizeng'}
					,{field:'name', title: '媒体名称'}
					,{field:'username', title: '所属代理'}
					,{field:'count', title: '用户量', width:80}
					,{fixed: 'right', width: 165, align:'center', toolbar: '#barDemo'}
				]]
				,page: true
			});
		});

	    /*
	     *  添加删除
	     */
	    var addedit = function(id = 0){
            var url = '/manage/tomedia/addpage?id='+id
              ,title = id == 0?'添加':'编辑';
            layer.open({
                    type: 2
                    ,title: title
                    ,content: url
                    ,area: ['42%', '52%']
                    ,btn: ['确定', '取消']
                    ,yes: function(index, layero){

                        var iframeWindow = window['layui-layer-iframe'+ index]
                        ,submitID = 'LAY-user-back-submit'
                        ,submit = layero.find('iframe').contents().find('#'+ submitID);
                        //监听提交
                        iframeWindow.layui.form.on('submit('+ submitID +')', function(data){
                          var field = data.field;
                          $.ajax({
                            url: '/manage/tomedia/add'
                            ,type: 'post'
                            ,dataType: 'json'
                            ,data: field
                            ,async: false
                            ,success: function(res){
                              if(res.code == 0){
                                layer.msg(res.msg,{icon:1,time:1500},function(){
                                  table.reload('test') //数据刷新
                                  layer.close(index); //关闭弹层
                                })
                              }else{
                                layer.msg(res.msg,{icon:2,time:2000})
                              }
                                  
                            }
                          });
                        });  
                        submit.trigger('click');
                    }
                  })
          }
		/*
		 * 监听左侧菜单栏
		 */
	    table.on('tool(test)', function(obj){
	    	var data = obj.data;
	    	if(obj.event === 'delschool'){
				layer.confirm('真的要删除吗？', function(index){
					$.ajax({
						url: '/manage/tomedia/delete'
						,data: {id:data.id}
						,dataType: 'json'
						,type: 'post'
						,success: function(e){
							if(e.code == 1001){
								layer.msg(e.msg, {icon: 5, time: 1500});
								return false;
							}
							layer.msg(e.msg, {icon: 6, time: 1200},function(){
								table.reload('test')
							});
						}
					})
				});
	    		
	    	}
	    	if(obj.event === 'editschool'){
	    		var id = data.id;
	    		addedit(id);
	    	}
	    })

	    $('.demoTable .layui-btn').on('click', function(){
	        var type = $(this).data('type');
	        if(type == 'addData'){

	        	addedit();
	        }	
	    });


	})
</script>