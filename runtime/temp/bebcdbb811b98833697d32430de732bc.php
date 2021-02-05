<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:68:"D:\tp\ywd100\application/../Template/manage/testPaper\makePaper.html";i:1598606566;}*/ ?>
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
<body class="childrenBody" style="margin-top: 2em">
	<form class="layui-form">
		<div class="layui-form-item">
			<div class="layui-inline">
				<label class="layui-form-label">试卷名称</label>
				<div class="layui-input-block">
				<input name="title" type="text" class="layui-input newsName" lay-verify="title" value="">
				</div>
			</div>
		</div>
		<div class="layui-form-item">
			<div class="layui-inline">
				<label class="layui-form-label">总分数</label>
				<div class="layui-input-block">
				<input name="score" type="text" class="layui-input newsName" lay-verify="score" value="">
				</div>
			</div>
		</div>
		<div class="layui-form-item">
			<div class="layui-inline">
				<label class="layui-form-label">考试时间</label>
				<div class="layui-input-block">
				<input name="testTime" type="text" class="layui-input" lay-verify="testTime" value="">
				</div>
			</div>
		</div>
		<div class="layui-form-item">
			<div class="layui-inline">
				<label class="layui-form-label">学员分配</label>
				<div class="layui-input-block">
					<button class="layui-btn" type="button" id="assign">学员分配</button>
					<input name="is_all" id="is_all" type="hidden" class="layui-input" value="">
					<input name="students" id="students" type="hidden" class="layui-input" value="">
				</div>
			</div>
		</div>
		<div class="layui-inline">
			<div class="layui-inline">
				<label class="layui-form-label">年级</label>
				<div class="layui-input-block">
					<select name="grade_id" lay-verify="required" lay-search="">
			          <option value="">请选择年级</option>
			          <?php if(is_array($gradeList) || $gradeList instanceof \think\Collection || $gradeList instanceof \think\Paginator): $i = 0; $__LIST__ = $gradeList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?>
				          <option value="<?php echo $list['id']; ?>"><?php echo $list['grade']; ?></option>
				      <?php endforeach; endif; else: echo "" ;endif; ?>
			        </select>
				</div>
			</div>
		</div>
		<div class="layui-inline">
			<div class="layui-inline">
			    <label class="layui-form-label">课程</label>
			    <div class="layui-input-block">
			    	<select name="subject_id" lay-verify="required" lay-search="">
				        <option value="">请选择学科</option>
				        <?php if(is_array($subjectList) || $subjectList instanceof \think\Collection || $subjectList instanceof \think\Paginator): $i = 0; $__LIST__ = $subjectList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$list): $mod = ($i % 2 );++$i;?>
					        <option value="<?php echo $list['id']; ?>"><?php echo $list['subject']; ?></option>
					    <?php endforeach; endif; else: echo "" ;endif; ?>
			        </select>
			    </div>
			</div>
	  	</div>
		<div class="layui-inline">
			<div class="layui-inline">
				<label class="layui-form-label">学期</label>
				<div class="layui-input-block">
					<select name="semester" lay-verify="required" lay-search="">
			          	<option value="">请选泽学期</option>
				        <option value="1">上学期</option>
				        <option value="2">下学期</option>
				        <option value="3">全册</option>
			        </select>
				</div>
			</div>
		</div>
		<div class="layui-form-item" style="width:90%;margin: 0 auto;">
		<table class="layui-table">
			<colgroup>
				<col width="10%">
				<col width="10%">
				<col width="10%">
				<col width="10%">
				<col width="10%">
				<col width="20%">
			</colgroup>
			<thead>
			<tr>
				<th>题型</th>
				<th>题数</th>
				<th>分值</th>
				<th>已选</th>
				<th>排序</th>
				<th>操作</th>
			</tr> 
			</thead>
			<tbody>
				<?php if(is_array($questionTypeList) || $questionTypeList instanceof \think\Collection || $questionTypeList instanceof \think\Paginator): $i = 0; $__LIST__ = $questionTypeList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$type): $mod = ($i % 2 );++$i;?>
				<tr>
					<td><?php echo $type['questype']; ?></td>
					<td><input name="num_<?php echo $type['quest_id']; ?>" type="text" class="layui-input newsName" lay-verify="score" value="" placeholder="题数"></td>
					<td><input name="everyScore_<?php echo $type['quest_id']; ?>" type="text" class="layui-input newsName" lay-verify="score" value="" placeholder="分值"></td>
					<td><input name="select_<?php echo $type['quest_id']; ?>" id="select_<?php echo $type['quest_id']; ?>" type="text" class="layui-input newsName" lay-verify="score" value="" placeholder="已选"></td>
					<td><input name="sort_<?php echo $type['quest_id']; ?>" type="text" class="layui-input newsName" lay-verify="score" value="" placeholder="排序"></td>
					<td><button type="button" class="layui-btn" onclick="showSelectedQuestion(<?php echo $type['quest_id']; ?>)">看题</button>
						<button type="button" class="layui-btn layui-btn-normal" onclick="selQuestion(<?php echo $type['quest_id']; ?>)">选题</button></td>
					<input name="selectQuest_<?php echo $type['quest_id']; ?>" id="selectQuest_<?php echo $type['quest_id']; ?>" type="hidden" class="layui-input newsName" lay-verify="score" value="">
				</tr>
				<?php endforeach; endif; else: echo "" ;endif; ?>
				<!-- <tr>
					<td>题帽题</td>
					<td><input name="num" type="text" class="layui-input newsName" lay-verify="score" value="" placeholder="题数"></td>
					<td><input name="everyScore" type="text" class="layui-input newsName" lay-verify="score" value="" placeholder="分值"></td>
					<td><input name="select" id="select" type="text" class="layui-input newsName" lay-verify="score" value="" placeholder="已选"></td>
					<td><input name="sort" type="text" class="layui-input newsName" lay-verify="score" value="" placeholder="排序"></td>
					<td><button type="button" class="layui-btn" onclick="showSelectedQuestion(0)">看题</button>
						<button type="button" class="layui-btn layui-btn-normal" onclick="selQuestion(0)">选题</button></td>
					<input name="selectQuest" id="selectQuest" type="hidden" class="layui-input newsName" lay-verify="score" value="">
				</tr> -->
			</tbody>
		</table>
		</div>
		<div class="layui-form-item">
			<div class="layui-input-block">
				<button class="layui-btn" type="button" lay-submit="" lay-filter="addMakePaper">立即提交</button>
				<button type="reset" class="layui-btn layui-btn-primary">重置</button>
		    </div>
		</div>
	</form>
	<script type="text/javascript" src="/static/manage/js/jquery-3.2.0.min.js"></script>
	<script type="text/javascript" src="/static/manage/layui/layui.js"></script>
	<script type="text/javascript" src="/static/manage/js/testPaper/makePaper.js"></script>
</body>
</html>