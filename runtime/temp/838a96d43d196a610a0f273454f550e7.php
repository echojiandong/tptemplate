<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:70:"D:\tp\ywd100\application/../Template/manage/testPaper\selQuestion.html";i:1598606566;}*/ ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>生成手工试卷</title>
	<meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="format-detection" content="telephone=no">
	<link rel="stylesheet" href="/static/manage/layui/css/layui.css" media="all" />
</head>
<style type="text/css">
	#test1{
		text-align: center;
	}
</style>
<body style="margin-top: 2em">
	<?php if((isset($videoList))): ?>
	<div class="demoTable">
	   	关键词：
	    <div class="layui-inline">
	        <input class="layui-input" name="keyword" id="demoReload" autocomplete="off">
	    </div>
	    <div class="layui-inline">
			章节：
			<div class="layui-input-inline">
				<select name="kid" class="newsLook" lay-filter="browseLook" id="kid" lay-verify="video_class">
					<option value="">请选择</option>
					<?php if(is_array($videoList) || $videoList instanceof \think\Collection || $videoList instanceof \think\Paginator): $i = 0; $__LIST__ = $videoList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?>
			        	<option value="<?php echo $val['id']; ?>"><?php echo $val['testclass']; ?></option>
			        <?php endforeach; endif; else: echo "" ;endif; ?>
				</select>
			</div>
		</div>
		<div class="layui-inline">
			课时：
			<div class="layui-input-inline" id="videoList">
				<select name="chapter_id" class="newsLook" lay-filter="browseLook" id="chapter_id" lay-verify="Type">
					<option value="">请选择</option>
				</select>
			</div>
		</div>
	    <button class="layui-btn" data-type="reload" id="sousuo">搜索</button>
	    <button class="layui-btn" data-type="reload" id="getSelectquest">获取选中题目</button>
	</div>
	<?php else: ?>
		<p>该年级不存在该科目</p>
	<?php endif; ?>
	<table class="layui-hide" id="test" lay-filter="demo"></table>
	<input  type="hidden" name="semester" id="semester" value="<?php echo $semester; ?>">
	<input  type="hidden" name="grade_id" id="grade_id" value="<?php echo $grade_id; ?>">
	<input  type="hidden" name="subject_id" id="subject_id" value="<?php echo $subject_id; ?>">
	<input  type="hidden" name="typeId" id="typeId" value="<?php echo $typeId; ?>">
	<script type="text/javascript" src="/static/manage/js/jquery-2.2.3.js"></script>
	<script type="text/javascript" src="/static/manage/layui/layui.js"></script>
	<script type="text/javascript" src="/static/manage/js/testPaper/makePaper.js"></script>
	
</body>
</html>