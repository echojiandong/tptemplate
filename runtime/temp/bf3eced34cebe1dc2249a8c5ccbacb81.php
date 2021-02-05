<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:71:"E:\phpStudy\WWW\ywd100\application/../Template/manage/person\index.html";i:1598606565;}*/ ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>用户列表</title>
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
	.laytable-cell-1-0-11{
		width: 170px !important;
	}
</style>
<body class="childrenBody" style="margin-top: 1em">
<div class="layui-form demoTable">
    用户搜索：
    <div class="layui-inline padding">
        <input class="layui-input" name="keyword" id="demoReload"  placeholder="搜索用户昵称或手机号">
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
	
	<div class="layui-inline padding" >
		用户学习状态：
		<div class="layui-input-inline" style="width: 100px;">
			<select name="status" class="newsLook layui-input"  >
				<option value="">请选择</option>
				<option value="1">待试听</option>
				<option value="2">已试听</option>
				<option value="3">已下单</option>
				<option value="4">已购买</option>
			</select>
		</div>
	</div>
	<div class="layui-inline padding" >
		用户状态：
		<div class="layui-input-inline" style="width: 100px;">
			<select name="act_status" class="newsLook layui-input"  >
				<option value="">请选择</option>
				<option value="1">正常</option>
				<option value="3">禁用</option>
			</select>
		</div>
	</div>
	<?php if($type != 1): ?>
	<div class="layui-inline padding">
		<?php if($isAdmin): ?>
		所属代理商：
		<?php else: ?>
		所属员工：
		<?php endif; ?>
		<div class="layui-input-inline">
			<select name="uid"  class="newsLook layui-input" id="uid" lay-search='' lay-filter="uid">
				<option value="">全部</option>
				<?php if($isAdmin): ?>
				<option value="-1">游客</option>
				<?php endif; if(is_array($userList) || $userList instanceof \think\Collection || $userList instanceof \think\Paginator): $i = 0; $__LIST__ = $userList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
				<option value="<?php echo $v['uid']; ?>"><?php echo $v['username']; ?></option>
				<?php endforeach; endif; else: echo "" ;endif; ?>
			</select>
		</div>
	</div>
	<?php endif; ?>
	<div class="layui-inline padding">
		所属媒体：
		<div class="layui-input-inline">
			<select name="to_media" id="to_media" class="newsLook layui-input"  lay-verify="" lay-filter="to_media" lay-search>
				<option value="">全部</option>
				<?php if(isset($mediaList) && $mediaList): if(is_array($mediaList) || $mediaList instanceof \think\Collection || $mediaList instanceof \think\Paginator): $i = 0; $__LIST__ = $mediaList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?>
					<option value="<?php echo $v['id']; ?>" ><?php echo $v['name']; ?></option>
					<?php endforeach; endif; else: echo "" ;endif; endif; ?>
			</select>
		</div>
	</div>
	<div class="layui-form layui-inline padding" id="area-picker">
		<div class="layui-input-inline" style="width: 150px;">
			<select name="province" class="province-selector" data-value="请选择省" lay-filter="province-1">
				<!-- <option value="">请选择省</option> -->
			</select>
		</div>
		<div class="layui-input-inline" style="width: 150px;">
			<select name="city" class="city-selector" data-value="请选择市" lay-filter="city-1">
				<!-- <option value="">请选择市</option> -->
			</select>
		</div>
		<div class="layui-input-inline" style="width: 150px;">
			<select name="county" class="county-selector" data-value="请选择区" lay-filter="county-1">
				<!-- <option value="">请选择区</option> -->
			</select>
		</div>
	</div>
    <!-- <div class="layui-inline">
		用户状态：
		<div class="layui-input-inline">
			<select name="act_status" class="newsLook" lay-filter="browseLook" id="act_status" lay-verify="Type">
				<option value="">请选择</option>
				<option value="0">未激活</option>
				<option value="1">已激活</option>
			</select>
		</div>
	</div> -->
    <button class="layui-btn" data-type="reload" id="sousuo" style="margin-left: 5px;">搜索</button>
	<!-- <button class="layui-btn" lay-event="addPerson" id="addPerson">添加</button> -->
	<!-- <button class="layui-btn" data-type="output">导出</button> -->
</div>

<table class="layui-hide" id="test" lay-filter="demo"></table>

<input type="hidden" name="type" value="<?php echo $type; ?>" id="type">
<input type="hidden" name="isAdmin" value="<?php echo $isAdmin; ?>" id="isAdmin">

<script type="text/html" id="xuhao">
    {{d.LAY_TABLE_INDEX+1}}
</script>
<script type="text/html" id="switchTpl">
	<input type="checkbox" name="sex" value="{{d.id}}" bid="{{d.isShow}}" lay-skin="switch" lay-text="显示|隐藏" lay-filter="sexDemo" {{ d.isShow == 1 ? 'checked' : '' }}>
</script>

<script type="text/html" id="images">
	<img src="{{d.pic}}" onclick='layui.bigimg(this)'/>;
</script>
<script type="text/html" id="timestamp">
	{{laytpl.toDateString(d.createTime)}}
</script>

<script type="text/html" id='usernameTpl'>
	{{# if(d.risk == 1){ }}
		<span>{{ d.nickName ? d.nickName : '' }} &nbsp;&nbsp;&nbsp;&nbsp;<svg t="1561463880502" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="4846" width="18" height="18"><path d="M512 48c255.848 0 464 208.152 464 464s-208.152 464-464 464S48 767.848 48 512 256.152 48 512 48m0-48C229.224 0 0 229.224 0 512s229.224 512 512 512c282.76 0 512-229.224 512-512S794.768 0 512 0z" p-id="4847" fill="#f82307"></path><path d="M348.992 360h209.08c9.12-16 19.448-32 28.56-48h-190.24c-14.584 16-29.776 32-47.4 48z" p-id="4848" fill="#f82307"></path><path d="M512 94.616c-230.52 0-417.384 186.864-417.384 417.384 0 230.512 186.864 417.384 417.384 417.384 230.504 0 417.376-186.864 417.376-417.384 0.008-230.52-186.872-417.384-417.376-417.384zM378.768 470.328c0 88.128-16.408 215.16-100.288 292.96-9.72-13.984-39.504-40.12-55.312-48.624C299.144 644.16 307.04 543.872 307.04 469.12v-69.288a784.016 784.016 0 0 1-35.856 26.744c-10.336-18.232-33.432-46.192-48.624-57.744 80.832-49.232 134.928-116.696 164.712-175.648l78.408 15.8a1005.552 1005.552 0 0 1-23.704 41.328h185.984l11.544-4.256 52.272 37.68c-14.584 22.488-34.04 52.272-53.488 79.624h147.688v65.64H378.768v41.328z m302.072 285.656H538.616c-88.128 0-116.088-20.056-116.088-94.2V470.328h282.016s0 16.408-0.608 24.92c-4.256 79.624-10.336 117.304-24.312 133.104-11.544 12.768-26.136 17.624-44.368 20.056-16.416 1.832-44.976 1.832-76.584 1.216-1.208-19.456-8.504-44.976-18.832-62 25.52 2.44 49.224 3.04 59.56 3.04 9.728 0 14.584-1.208 20.056-6.08 4.856-5.472 8.504-20.664 11.544-52.264h-137.36v128.24c0 25.528 7.296 29.784 50.448 29.784h132.504c38.288 0 43.76-11.544 48.624-82.656 15.8 11.544 46.8 23.096 66.248 26.744-9.728 95.416-31.616 121.552-110.624 121.552z" p-id="4849" fill="#f82307"></path></svg></span>
	{{# } else { }}
		<span>{{ d.nickName ? d.nickName : '' }} </span>
	{{# }}}
</script>




<script type="text/html" id="barDemo">
	<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="personInfo">查看</a>
	<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="editPerson">编辑</a>
	<!-- <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="addPersonSon">添加子用户</a> -->

	{{# if(d.act_status != 3) { }}
	<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="forbidden">禁用</a>
	{{# } else { }}
	<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="forbidden">解禁</a>
	{{# } }}

	<!-- 跟进 -->
	<!-- {{# if(d.status == 1) { }}
	<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="followUp">跟进</a>
	{{# }}} -->

	{{# if(d.risk == 1) { }}
	<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="abnormal">异常</a>
	{{# }}}

	{{# if(d.isRenew == 1) { }}
	<!-- <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="isRenew">续费</a> -->
	{{# }}}

</script>

<script type="text/javascript" src="/static/manage/layui/layui.js"></script>
<script type="text/javascript" src="/static/manage/js/person/index.js"></script>


<script>
		layui.config({
			base: '/static/manage/layui/js/',
		})
	
		layui.use(['laydate','layer','form','layarea'], function(){
			var laydate = layui.laydate
				,form = layui.form
				,$ = layui.$
				,layarea = layui.layarea
				,layer = layui.layer;
	
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
	
			layarea.render({
				elem: '#area-picker',
				change: function (res) {
					
				}
			});

			form.on('select(uid)', function(data) {
				var isAdmin = '<?php echo $isAdmin ? $isAdmin : 0; ?>';
				
				if (isAdmin == 0) {
					return false;
				}
				var uid = data.value;
				
				// if (uid == '') {
				// 	form.render('select', 'to_media');
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