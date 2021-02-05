<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:74:"E:\phpStudy\WWW\ywd100\application/../Template/manage/order\orderAuth.html";i:1598606565;}*/ ?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>卡号分配</title>
	<meta name="renderer" content="webkit">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<meta name="apple-mobile-web-app-status-bar-style" content="black">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="format-detection" content="telephone=no">
	<link rel="stylesheet" href="/static/manage/layui/css/layui.css" media="all" />
</head>
<body class="childrenBody">
	<div class="layui-tab">
    	<ul class="layui-tab-title">
		    <li class="layui-this">可生成列表</li>
		    <!-- <li>赠送列表</li> -->
		    <!-- <li>受赠列表</li> -->
    	</ul>
	    <div class="layui-tab-content">
		    <div class="layui-tab-item layui-show">
		    	<div class="layui-form">
				  <table class="layui-table">
				    <colgroup>
				      <col width="150">
				      <col width="200">
				      <col width="150">
				      <!-- <col width="150"> -->
				      <col width="150">
				    </colgroup>
				    <thead>
				      <tr>
				        <th>学科</th>
				        <th>可生成数量</th>
				        <th>类型</th>
				      </tr> 
				    </thead>
				    <tbody>
				      	<?php if(is_array($userCodeList) || $userCodeList instanceof \think\Collection || $userCodeList instanceof \think\Paginator): $i = 0; $__LIST__ = $userCodeList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?>
				      <tr>
				        <td><?php echo $val['title']; ?></td>
				        <td><?php echo $val['code_num']; ?></td>
				        <td><?php echo $val['type_name']; ?></td>
				      </tr>
				        <?php endforeach; endif; else: echo "" ;endif; ?>
				    </tbody>
				  </table>
				</div>
			</div>
		    <div class="layui-tab-item">
		    	<div class="layui-form">
				  <table class="layui-table">
				    <colgroup>
				      <col width="150">
				      <col width="200">
				      <col width="150">
				      <col width="150">
				      <col width="150">
				    </colgroup>
				    <thead>
				      <tr>
				        <th>接受人</th>
				        <th>数量</th>
				        <th>类型</th>
				        <th>赠送时间</th>
				        <th>备注</th>
				      </tr> 
				    </thead>
				    <tbody>
					
						<?php if(is_array($assignList) || $assignList instanceof \think\Collection || $assignList instanceof \think\Paginator): $i = 0; $__LIST__ = $assignList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?>	
						<tr>
							<td><?php echo $val['username']; ?></td>
							<td><?php echo $val['num']; ?></td>
							<td><?php echo $val['video_class']; ?></td>
							<td><?php echo $val['add_time']; ?></td>
							<td><?php echo $val['remark']; ?></td>
						</tr>
					  	<?php endforeach; endif; else: echo "" ;endif; ?>
					
				    </tbody>
				  </table>
				</div>
		    </div>
		   	<div class="layui-tab-item">
		    	<div class="layui-form">
				  <table class="layui-table">
				    <colgroup>
				      <col width="150">
				      <col width="200">
				      <col width="150">
				      <col width="150">
				      <col width="150">
				    </colgroup>
				    <thead>
				      <tr>
				        <th>接受人</th>
				        <th>数量</th>
				        <th>类型</th>
				        <th>赠送时间</th>
				        <th>备注</th>
				      </tr> 
				    </thead>
				    <tbody>
				    <?php if(is_array($acceptList) || $acceptList instanceof \think\Collection || $acceptList instanceof \think\Paginator): $i = 0; $__LIST__ = $acceptList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?>	
				      <tr>
				    	<td><?php echo $val['username']; ?></td>
				        <td><?php echo $val['num']; ?></td>
				        <td><?php echo $val['video_class']; ?></td>
				        <td><?php echo $val['add_time']; ?></td>
				        <td><?php echo $val['remark']; ?></td>
				    </tr>
				      <?php endforeach; endif; else: echo "" ;endif; ?>
				    </tbody>
				  </table>
				</div>
		    </div>
		</div>
	</div>
	<div id="page"></div>
	<script type="text/javascript" src="/static/manage/layui/layui.js"></script>
	<script type="text/javascript" src="/static/manage/js/code/codeAssign.js"></script>
</body>
</html>