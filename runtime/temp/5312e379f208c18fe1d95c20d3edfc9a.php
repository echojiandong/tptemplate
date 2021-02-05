<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:66:"D:\tp\ywd100\application/../Template/manage/order\orderPerson.html";i:1598606566;}*/ ?>
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
<style>
	.padding {
		padding: 5px 2px 5px 0;
	}
</style>
<body class="childrenBody" style="margin-top: 1em">
<div class="demoTable">
	搜索：
	<div class="layui-inline padding">
		<input class="layui-input" name="keyword" id="demoReload" autocomplete="off" placeholder="昵称或手机号搜索">
	</div>
	<div class="layui-inline padding">
		订单状态：
		<div class="layui-input-inline">
			<select name="state" class="newsLook layui-input" lay-filter="browseLook" lay-verify="Type">
				<option value="">全部</option>
				<option value="1">待支付</option>
				<option value="2">已支付</option>
				<option value="3">支付失败</option>
				<option value="4">取消支付</option>
			</select>
		</div>
	</div>
	<div class="layui-inline padding">
		审核状态：
		<div class="layui-input-inline">
			<select name="orderCheck" class="newsLook layui-input" lay-filter="browseLook" lay-verify="Type">
				<option value="">全部</option>
				<option value="1">待审核</option>
				<option value="2">审核通过</option>
				<option value="3">审核不通过</option>
			</select>
		</div>
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
	<?php if($type != 1): ?>
	<div class="layui-inline padding">
		<?php if($isAdmin): ?>
		所属代理商：
		<?php else: ?>
		所属员工：
		<?php endif; ?>
		<div class="layui-form layui-input-inline">
			<select name="uid" class="newsLook layui-input" lay-filter="uid"  lay-verify="" lay-search>
				<option value="">全部</option>
				<?php if(is_array($userList) || $userList instanceof \think\Collection || $userList instanceof \think\Paginator): $i = 0; $__LIST__ = $userList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
				<option value="<?php echo $v['uid']; ?>"><?php echo $v['username']; ?></option>
				<?php endforeach; endif; else: echo "" ;endif; ?>
			</select>
		</div>
	</div>
	<?php endif; ?>

	<div class="layui-inline padding">
		所属媒体：
		<div class="layui-input-inline" style="width: 120px;">
			<select name="to_media" id="to_media" class="newsLook layui-input" >
				<option value="">全部</option>
				<?php if(isset($mediaList) && $mediaList): if(is_array($mediaList) || $mediaList instanceof \think\Collection || $mediaList instanceof \think\Paginator): $i = 0; $__LIST__ = $mediaList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
					<option value="<?php echo $v['id']; ?>" ><?php echo $v['name']; ?></option>
					<?php endforeach; endif; else: echo "" ;endif; endif; ?>
			</select>
		</div>
	</div>

	<?php if(isset($type) && $type == 3): ?>
	<div class="layui-inline padding">
		是否作废：
		<div class="layui-input-inline">
			<select name="is_forbidden" class="newsLook layui-input"  lay-verify="" lay-search>
				<option value="0">正常</option>
				<option value="1">已作废</option>
			</select>
		</div>
	</div>
	<?php endif; ?>
	<input type="hidden" name="type" value="<?php echo $type; ?>" id="type">
	<input type="hidden" name="isAdmin" value="<?php echo $isAdmin; ?>" id="isAdmin">

	<!-- <div class="layui-inline">
		下单时间：
		<div class="layui-input-inline">
			<select name="orderCheck" class="newsLook" lay-filter="browseLook" lay-verify="Type">
				<option value="">请选择</option>
				<option value="1">待审核</option>
				<option value="2">审核通过</option>
				<option value="3">审核不通过</option>
			</select>
		</div>
	</div> -->
	<button class="layui-btn" data-type="reload" id="sousuo">搜索</button>
	<!-- <button class="layui-btn" data-type="addOrder">卡号下单</button> -->
	<!-- <button class="layui-btn" data-type="addClassOrder" id="addClassOrder">直接下单</button> -->
</div>


		<table class="layui-hide" id="test" lay-filter="demo" ></table>

		<script type="text/html" id="state">
			{{laytpl.toState(d.state)}}
		</script>
		<script type="text/html" id="orderCheck">
			{{laytpl.toOrderCheck(d.orderCheck)}}
		</script>
		<script type="text/html" id="grade_id">
			{{laytpl.toGrade_id(d.grade_id)}}
		</script>
		<script type="text/html" id="timestamp">
			<!-- {{laytpl.toDateString(d.strtime)}} -->
			{{d.strtime}}
		</script>

		<script type="text/html" id="barDemo">
			<a class="layui-btn layui-btn-xs" lay-event="edit">查看</a>
			<!-- 只有我的订单且还未审核的订单有编辑 ， 我的审核只有审核-->
			{{# if (d.type != 2)  { }}
				{{# if (d.type == 1 && d.orderCheck == 1 && d.is_forbidden != 3)  { }}
					<a class="layui-btn layui-btn-xs" lay-event="editOrderPerson">编辑</a>
				{{# } else if (d.type == 3 && d.orderCheck == 1 && d.is_forbidden == 0) { }}
					<a class="layui-btn layui-btn-xs" lay-event="editOrderPerson">审核</a>
				{{# }}}

				{{# if (d.type == 1 && d.is_forbidden == 0) { }}
					<a class="layui-btn layui-btn-xs" lay-event="sendDelete">发起作废</a>
				{{# } else if (d.type == 1 && d.is_forbidden == 3) { }}
					<span style="color:red;">已发起作废,等待审核</span>
				{{# }}}

			{{# }}}

			<!-- 只有审核员可以删除订单  改为 审核后也可以删除 d.orderCheck != 2 && d.type == 3-->
			{{# if ( d.is_forbidden == 3 && d.type == 3)  { }} 
			<a class="layui-btn layui-btn-xs" lay-event="delOrderPerson">确定作废</a>
			{{# } else { }}
			<!-- <a class="layui-btn layui-btn-xs" lay-event="cancelOrderPerson">作废</a> -->
			{{# }}}
			
		</script>

	<script type="text/javascript" src="/static/manage/layui/layui.js"></script>
	<script type="text/javascript" src="/static/manage/js/order/orderPersonList.js"></script>
<script>
layui.use(['laydate','layer','form'], function(){
  var laydate = layui.laydate
  				,form = layui.form
				,$ = layui.$;
	var myDate = new Date();//获取系统当前时间
	var nowTime = myDate.toLocaleDateString();
	//执行一个laydate实例
	laydate.render({
		elem: '#orderStartTime', //指定元素
		max: nowTime
		
	});
	//执行一个laydate实例
	laydate.render({
		elem: '#orderEndTime', //指定元素
		max: nowTime
	});

  form.on('select(uid)', function(data) {
	  	// 只有超管 和一点通有权限 执行
		var isAdmin = '<?php echo $isAdmin ? $isAdmin : 0; ?>';
		if (isAdmin == 0) {
			return false;
		}

		var uid = data.value;
		
		// if (uid == '') {
		// 	form.render('select');
		// 	return false;
		// } else
		
		if (uid == -1) {
			$("#to_media").html('');
			var html = '<option value="">全部</option>';
			$("#to_media").append(html);
			form.render('select');
			return false;
		}

		$.ajax({
			type:'post',
			url:'/manage/person_controller/getTomediaList?agent_id='+uid,
			success:function(da) {
				if (da.error_code == 0) {
					if (da.data.length == 0) {
						$("#to_media").html('');
						var html = '<option value="">全部</option>';
						$("#to_media").append(html);
						form.render('select');
					} else {
						$("#to_media").html('');
						var html = '<option value="">全部</option>';
						for(var i= 0; i<da.data.length; i++) {
							html += '<option value="'+ da.data[i].id +'">'+ da.data[i].name +'</option>'
						}

						$("#to_media").append(html);
						form.render('select');
					}
				} else {
					layer.msg(data.msg,{time:400});
				}
			}
			
		})
	})
});
</script>
</body>
</html>