<?php
namespace app\index\controller;
use think\Controller;
class common extends Controller
{
	// 初始化判断用户是否登陆
	public function _initialize()
	{
		if(!isset($_SESSION['userInfo'])){
			// 如果用户还没登陆返回到首页
			return view('index/index/index');
		}
	}
	/**
     * @desc 获取get参数
     * @param $name
     * @param string $type
     * @param string $default
     * @return int|string
     */
    protected function getParam($name, $type = 'string', $default = '')
    {
        if (!isset($_GET[$name])) {
            return $default;
        }

        if ($type == 'string') {
            return trim($_GET[$name]);
        } elseif ($type == 'int') {
            return intval($_GET[$name]);
        } else {
            return trim($_GET[$name]);
        }
    }

    /**
     * @desc 获取post参数
     * @param $name
     * @param string $type
     * @param string $default
     * @return int|string
     */
    protected function postParam($name, $type = 'string', $default = '')
    {
        if (!isset($_POST[$name])) {
            return $default;
        }

        if ($type == 'string') {
            return trim($_POST[$name]);
        } elseif ($type == 'int') {
            return intval($_POST[$name]);
        } else {
            return trim($_POST[$name]);
        }
    }
    /**
     * @desc 获取server 变量
     * @param $name
     * @param string $type
     * @param string $default
     * @return int|string
     */
    protected function serverParam($name, $type = 'string', $default = '')
    {
        if (!isset($_SERVER[$name])) {
            return $default;
        }

        if ($type == 'int') {
            return intval($_SERVER[$name]);
        }

        return trim($_SERVER[$name]);
    }
    
    public function apiReturn($code, $msg, $data = [])
    {
        $returnData = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ];
        
        return json($returnData);
    }
}