<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:60:"D:\tp\ywd100\application/../Template/manage/login\login.html";i:1598606566;}*/ ?>
<!DOCTYPE HTML>
<html>
<head>
	<title>管理员登录</title>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta name="description" content="" />
	<meta name="keywords" content="" />
	<!--[if lte IE 8]><script src="css/ie/html5shiv.js"></script><![endif]-->
	<script src="/static/manage/js/skel.min.js"></script>
	<script src="/static/manage/js/init.js"></script>
	<link rel="stylesheet" href="/static/manage/layui//css/layui.css" media="all" />
	<link rel="stylesheet" href="/static/manage/css/login.css" media="all" />
	<noscript>
		<link rel="stylesheet" href="css/skel.css" />
		<link rel="stylesheet" href="css/style.css" />
		<link rel="stylesheet" href="css/style-wide.css" />
		<link rel="stylesheet" href="css/style-noscript.css" />
	</noscript>
	<!--[if lte IE 9]><link rel="stylesheet" href="css/ie/v9.css" /><![endif]-->
	<!--[if lte IE 8]><link rel="stylesheet" href="css/ie/v8.css" /><![endif]-->

</head>
<body class="loading">
<div id="wrapper">
	<div id="bg"></div>
	<div id="overlay"></div>
	<div id="main">
		<div class="logo"><img src="/static/default/images/logo.png" alt=""></div>
		<div class="login-contain">
			<div class="login-contain-left">
        <h3>Hello<br> Welcome</h3>
        <p>知识一点通<br>让每一个孩子都能享受到高端的教育资源</p>
      </div>
      
      <div class="login">

          <h1>后台管理系统登录</h1>
          <form class="layui-form login-form">
            <div class="layui-form-item">
              <input class="layui-input login-form-user" name="username" placeholder="用户名" lay-verify="required" type="text" autocomplete="off">
            </div>
            <div class="layui-form-item">
              <input class="layui-input login-form-pwd" name="userpwd" placeholder="密码" lay-verify="required" type="password" autocomplete="off">
            </div>
            <div class="layui-form-item layui-form-item-remember">
								<input class="login-form-remember" type="checkbox" name="" value="记住密码" title="记住密码" lay-skin="primary"> 
              </div>
            <!--  -->
            <button class="layui-btn login_btn" lay-submit="" lay-filter="login">登录</button>
          </form>
        </div>
		</div>
		<!-- Header -->
		<!-- <header id="header"> -->
			

		<!-- </header> -->

		<!-- Footer -->
		<footer id="footer">
			<span class="copyright">&copy; Untitled. Design.</span>
		</footer>

	</div>
</div>
<script type="text/javascript" src="/static/manage/layui/layui.js"></script>
<script type="text/javascript" src="/static/manage/js/login.js"></script>
</body>
</html>