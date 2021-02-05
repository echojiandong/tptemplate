<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:64:"D:\tp\ywd100\application/../Template/manage/service\service.html";i:1598606566;}*/ ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>、微信客服列表</title>
	<meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="format-detection" content="telephone=no">
	<link rel="stylesheet" href="/static/manage/layui/css/layui.css" media="all" />
</head>
<body class="childrenBody" style="margin-top: 1em">
<input type="hidden" value="<?php echo $id; ?>" id="video_id">
<div class="demoTable">
    微信客服：
    <div class="layui-inline">
        <input class="layui-input" name="keyword" id="demoReload" placeholder="请输入微信号/昵称" value="" autocomplete="off">
    </div>
    <button class="layui-btn" data-type="reload" id="sousuo">搜索</button>
	<button class="layui-btn" data-type="addData">添加</button>
</div>
<table class="layui-hide" id="test" lay-filter="demo"></table>

<script type="text/html" id="switchTpl">
	<input type="checkbox" name="sex" value="{{d.id}}" bid="{{d.isShow}}" lay-skin="switch" lay-text="显示|隐藏" lay-filter="sexDemo" {{ d.isShow == 1 ? 'checked' : '' }}>
</script>

<script type="text/html" id="images">
	<img src="{{d.pic}}" onclick='layui.bigimg(this)'/>;
</script>
<script type="text/html" id="timestamp">
	{{laytpl.toDateString(d.createTime)}}
</script>

<script type="text/html" id="barDemo">
	<a class="layui-btn layui-btn-xs" lay-event="show">详情</a>
	<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="edt">编辑</a>
	<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
</script>

<script type="text/javascript" src="/static/manage/layui/layui.js"></script>
<script type="text/javascript" src="/static/manage/layui/layui.all.js"></script>
<script type="text/javascript" src="/static/manage/js/service/serviceList.js"></script>

</body>
</html>