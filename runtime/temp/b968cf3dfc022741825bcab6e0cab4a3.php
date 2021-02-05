<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:63:"D:\tp\ywd100\application/../Template/manage/order\consumer.html";i:1598606566;}*/ ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>订单列表</title>
	<meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="format-detection" content="telephone=no">
	<link rel="stylesheet" href="/static/manage/layui/css/layui.css" media="all" />
</head>
<body class="childrenBody" style="margin-top: 1em">
<input type="hidden" value="<?php echo $type; ?>" id="type">
<input type="hidden" value="<?php echo $check; ?>" id="check">
<div class="demoTable">
	搜索：
	<div class="layui-inline">
		<input class="layui-input" name="keyword" id="demoReload" autocomplete="off" placeholder="账号/手机号">
	</div>
	<div class="layui-inline">
		订单状态：
		<div class="layui-input-inline" >
			<select name="state" class="newsLook layui-input" lay-filter="browseLook" lay-verify="Type" style="width:80px;">
				<option value="">请选择</option>
				<option value="1">待支付</option>
				<option value="2">已支付</option>
			</select>
		</div>
	</div>
	<?php if(($type== 1)): ?>
	<div class="layui-inline">
		审核状态：
		<div class="layui-input-inline">
			<select name="orderCheck" class="newsLook layui-input" lay-filter="browseLook" lay-verify="Type">
				<option value="">请选择</option>
				<option value="1">内部待审核</option>
				<option value="3">上级待审核</option>
				<option value="4">审核完成</option>
				<option value="5">审核不通过</option>
			</select>
		</div>
	</div>
	<?php else: ?>
	<div class="layui-inline">
		审核状态：
		<div class="layui-input-inline">
			<select name="orderCheck" class="newsLook layui-input" lay-filter="browseLook" lay-verify="Type">
				<option value="">请选择</option>
				<option value="3">待审核</option>
				<option value="4">审核完成</option>
				<option value="5">审核不通过</option>
			</select>
		</div>
	</div>
	<?php endif; ?>
	<div class="layui-inline">
		开始：
		<div class="layui-input-inline">
			<input type="text" class="layui-input" id="orderStartTime" autocomplete="off" name="orderStartTime" placeholder="yyyy-MM-dd">
		</div>
	</div>
	<div class="layui-inline">
		结束：
		<div class="layui-input-inline">
			<input type="text" class="layui-input" id="orderEndTime" autocomplete="off" name="orderEndTime" placeholder="yyyy-MM-dd">
		</div>
	</div>
	<button class="layui-btn" data-type="reload" id="sousuo">搜索</button>
	<!-- <?php if($type == 1): ?>
	<div style="padding-top: 20px;">
      <button class="layui-btn layuiadmin-btn-useradmin" data-type="addOrder">正式课下单</button> -->
      <!-- <button class="layui-btn layuiadmin-btn-useradmin" data-type="addTextOrder" id="addTextOrder">试听课下单</button> -->
    <!-- </div>
    <?php endif; ?> -->
	<!-- 取消一键下单 -->
	<!-- <div class="layui-inline">
		<input class="layui-input" name="num" id="num" autocomplete="off">
	</div>
	
	<button class="layui-btn" data-type="quickAddOrder" id="quickAddOrder">一键下单</button> -->
</div>
		<table class="layui-hide" id="test" lay-filter="demo" ></table>

		<script type="text/html" id="state">
			{{laytpl.toState(d.state)}}
		</script>
		<!-- <script type="text/html" id="orderCheck">
			{{laytpl.toOrderCheck(d.orderCheck)}}
		</script> -->
		<script type="text/html" id="order_type">
			{{laytpl.toOrder_type(d.order_type)}}
		</script>
		<!-- <script type="text/html" id="timestamp">
			{{laytpl.toDateString(d.strtime)}}
		</script> -->
		<script type="text/html" id="barDemo">
			<a class="layui-btn layui-btn-xs" lay-event="edit">查看</a>
			{{# if(d.check== 1){ }}
			<a class="layui-btn layui-btn-xs" lay-event="editOrder">审核</a>
			{{# } else { }}
			<a class="layui-btn layui-btn-xs" lay-event="editOrder">修改</a>
			{{# }}}
			<a class="layui-btn layui-btn-xs" lay-event="delOrder">作废</a>
		</script>

	<script type="text/javascript" src="/static/manage/layui/layui.js"></script>
	<script type="text/javascript" src="/static/manage/js/order/orderList.js"></script>

</body>
</html>
<script>
layui.use('laydate', function(){
  var laydate = layui.laydate;
  
  //执行一个laydate实例
  laydate.render({
    elem: '#orderStartTime' //指定元素
  });
  //执行一个laydate实例
  laydate.render({
    elem: '#orderEndTime' //指定元素
  });
});
</script>