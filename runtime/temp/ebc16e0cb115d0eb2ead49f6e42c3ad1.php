<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:72:"D:\tp\ywd100\application/../Template/manage/testPaper\testPaperList.html";i:1598606566;}*/ ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>试卷列表</title>
	<meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="format-detection" content="telephone=no">
	<link rel="stylesheet" href="/static/manage/layui/css/layui.css" media="all" />
</head>
<body class="childrenBody" style="margin-top: 1em">
<div class="demoTable">
   	试卷名称：
    <div class="layui-inline">
        <input class="layui-input" name="keyword" id="demoReload" autocomplete="off">
    </div>
    <div class="layui-inline">
		所属年级：
		<div class="layui-input-inline">
			<select name="grade_id" class="newsLook" lay-filter="browseLook" id="grade_id" lay-verify="Type">
					<option value="">请选择</option>
		          	<option value="1">一年级</option>
					<option value="2">二年级</option>
					<option value="3">三年级</option>
					<option value="4">四年级</option>
					<option value="5">五年级</option>
					<option value="6">六年级</option>
					<option value="7">七年级</option>
					<option value="8">八年级</option>
					<option value="9">九年级</option>
					<option value="10">高一</option>
					<option value="11">高二</option>
					<option value="12">高三</option>
			</select>
		</div>
	</div>
	<div class="layui-inline">
		所属科目：
		<div class="layui-input-inline">
			<select name="subject_id" class="newsLook" lay-filter="browseLook" id="subject_id" lay-verify="Type">
				<option value="">请选择</option>
	          	<option value="1">语文</option>
				<option value="2">数学</option>
	          	<option value="3">英语</option>
	          	<option value="4">物理</option>
	          	<option value="5">化学</option>
	          	<option value="6">政治</option>
	          	<option value="7">历史</option>
	          	<option value="8">地理</option>
	          	<option value="9">生物</option>
			</select>
		</div>
	</div>
	<div class="layui-inline">
		所属学期：
		<div class="layui-input-inline">
			<select name="semester" class="newsLook" lay-filter="browseLook" id="semester" lay-verify="Type">
				<option value="">请选择</option>
	          	<option value="1">上学期</option>
				<option value="2">下学期</option>
			</select>
		</div>
	</div>
	<div class="layui-inline">
		试卷类型：
		<div class="layui-input-inline">
			<select name="type" class="newsLook" lay-filter="browseLook" id="type" lay-verify="Type">
				<option value="">请选择</option>
	          	<option value="1">手工组卷</option>
				<option value="2">随机组卷</option>
			</select>
		</div>
	</div>
    <button class="layui-btn" data-type="reload" id="sousuo">搜索</button>
	<!-- <button class="layui-btn" lay-event="addCode" id="addCode">添加</button> -->
</div>
<table class="layui-hide" id="test" lay-filter="demo"></table>

<script type="text/html" id="grade">
	{{laytpl.toGrade(d.grade_id)}}
</script>
<script type="text/html" id="subject">
	{{laytpl.toSubject(d.subject_id)}}
</script>
<script type="text/html" id="timestamp">
	{{laytpl.toDateString(d.addTime)}}
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

<script type="text/html" id="barDemo">
	<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="showPaper">查看</a>
	<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="editPaper">编辑</a>
	<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="forbidden">禁用</a>
</script>

<script type="text/javascript" src="/static/manage/layui/layui.js"></script>
<script type="text/javascript" src="/static/manage/js/testPaper/testPaperList.js"></script>

</body>
</html>