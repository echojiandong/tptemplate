<?php
/* *
 * 配置文件
 * 版本：3.4
 * 修改日期：2016-03-08
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 * 安全校验码查看时，输入支付密码后，页面呈灰色的现象，怎么办？
 * 解决方法：
 * 1、检查浏览器配置，不让浏览器做弹框屏蔽设置
 * 2、更换浏览器或电脑，重新登录查询。
 */
 
//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
//合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://b.alipay.com/order/pidAndKey.htm
$alipay_config['partner']		= '2088911445944393';

//收款支付宝账号，以2088开头由16位纯数字组成的字符串，一般情况下收款账号就是签约账号
$alipay_config['seller_id']	= $alipay_config['partner'];

// MD5密钥，安全检验码，由数字和字母组成的32位字符串，查看地址：https://b.alipay.com/order/pidAndKey.htm
$alipay_config['key']			= 'asxtddj848uz7589fbyxsobvtkjpql5c';

// 服务器异步通知页面路径  需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
$alipay_config['notify_url'] = "http://www.ywd100.com/index/person/myClass";
$alipay_config['notify_urls'] = "http://www.ywd100.com/index/person/myClass";

// 页面跳转同步通知页面路径 需http://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
$alipay_config['return_url'] = "http://www.ywd100.com/index/person/myClass";
$alipay_config['return_urls'] = "http://www.ywd100.com/index/person/myClass";

//签名方式
$alipay_config['sign_type']    = strtoupper('MD5');

//字符编码格式 目前支持 gbk 或 utf-8
$alipay_config['input_charset']= strtolower('utf-8');

//ca证书路径地址，用于curl中ssl校验
//请保证cacert.pem文件在当前文件夹目录中
$alipay_config['cacert']    = getcwd().'\\cacert.pem';

//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
$alipay_config['transport']    = 'http';

// 支付类型 ，无需修改
$alipay_config['payment_type'] = "1";
		
// 产品类型，无需修改
$alipay_config['service'] = "create_direct_pay_by_user";

//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑


//↓↓↓↓↓↓↓↓↓↓ 请在这里配置防钓鱼信息，如果没开通防钓鱼功能，为空即可 ↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
	
// 防钓鱼时间戳  若要使用请调用类文件submit中的query_timestamp函数
$alipay_config['anti_phishing_key'] = "";
	
// 客户端的IP地址 非局域网的外网IP地址，如：221.0.0.1
$alipay_config['exter_invoke_ip'] = "";
		
//↑↑↑↑↑↑↑↑↑↑请在这里配置防钓鱼信息，如果没开通防钓鱼功能，为空即可 ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

/**
 * 新的支付宝参数
 */
$config = array (
    //签名方式,默认为RSA2(RSA2048)
    'sign_type' => "RSA2",

    //支付宝公钥
    'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqT3ctPNzXZFnPwC6ovX7+EV6xwLWdtItPPtfCO9cELTCB8FEavHQcwzmnsZ6QGZ1LWU8hK4hdTPEXp63whpWOwetE3qj1Uv0nNGm5hB5H/aD/jSQcgc5t0nKQjzJwQTsEnNYVaHEFebXRdYQyarjvCPWm8z+AOfZ6HV+76ozEfx+UNMvl6MegawMVCmOrqKwgHjGtykIj5HqDbz160gaVuZzCepjAD15DOCcLvoZWYKQLXqKgc+HVwIlhJpiLxBEHI/DzgYQ1bVM12AE5FoKEXln7iX3KPniVGnQJegVMontsHrY3jrH9rfmfO1jLLOwYQqXazg4eNKGy9ezWzuzdwIDAQAB",

    //商户私钥
    'merchant_private_key' =>"MIIEpAIBAAKCAQEAqT3ctPNzXZFnPwC6ovX7+EV6xwLWdtItPPtfCO9cELTCB8FEavHQcwzmnsZ6QGZ1LWU8hK4hdTPEXp63whpWOwetE3qj1Uv0nNGm5hB5H/aD/jSQcgc5t0nKQjzJwQTsEnNYVaHEFebXRdYQyarjvCPWm8z+AOfZ6HV+76ozEfx+UNMvl6MegawMVCmOrqKwgHjGtykIj5HqDbz160gaVuZzCepjAD15DOCcLvoZWYKQLXqKgc+HVwIlhJpiLxBEHI/DzgYQ1bVM12AE5FoKEXln7iX3KPniVGnQJegVMontsHrY3jrH9rfmfO1jLLOwYQqXazg4eNKGy9ezWzuzdwIDAQABAoIBAQCSS2pcdNF1wXn5KR0sPuxXOWAfZaGTxqzaOQR2icoQmt/aqkMFGxCwUyeLelA1GRwTaJ5+prM/R8dob1SzEecWUdVXsDys5WKGqGfYGcdKTOLeO/vcxcgrWzreNQPFuQaEEHb8VsBUml3d/WQ3n2QKvwQFW5b5m3FvExnt/Db3RNxAWZLwvsw8lpYPH1En/PgJpldckY4oiJIQZooZLtu7P94JfuJQy259LSRJtwMPEQAJSDO/X2XgL28jPvQkS5+YJzLuKsCNMjVAQCZzlG6jNQ2PfJss8yPVyieooG8h/ldeYT+hVrcmZ+YTWS8VDNMQkN4RlBYmSV/FkCUcL2W5AoGBAOrvrbqAm/pKOOqPg44eqU3LHhqlUNrqJBFP/oBbHiLiaMpf+2B5cxoppsujFj2p10qG+7cMPsBdhnd4ESDZ6iH8+VVlOUmQdUf2PPvS3tydefAsNFx5iLN4ozI6Ys+U9CpDtLjLhpaMiRRwapvx3CDW0QjkKM+5sbhI4Xj72jfLAoGBALhqV9LdWZIcIkEZVwPgvWWpNc+7tcuV8/3tZJy+RBKapU1Eeck5Tl7wZ5R1loSsrDh3rI6ey5gVdnWHLuAicYgVkfYzV8Ih/FZoRKOoC/rm38qYdszcUocfNPUPEgFIjQEOC8i2Ctc1QLoJ8o0bU+JR4QdY6HLO+TrchtfdGUWFAoGBAMgp7u2Qt9QWusl1/tLuYrcKEJsJMItjo9kwO5jmiRWVq0yJXGZE84Fd7WNgjwl4lBpGSV4ay7gftvpAzO8dWvMcUt6kAJwhvRrTu/+eGQ0ECqlLME19qq+mX/zia9+KfEEqMGal2QSZtg04/kvhU/iSrcSSiAb7omRti9UFV1XjAoGABy8VVsCKsbdo2jJbgmop1qD92KbOUSz5QUYTKkv7KOJfWCA3wk/6Lpac4lqmT2rOlrCKx0+GTNlhMPjgKZkN5Sg59GZRn91lEBSlv95XASInS6Qe1KIskNj1XiqFIAmsfIMIkHwO9g08BUNH8JlE7kcURg3/mCFZ8iIPGq+hQWUCgYAQulQntpmvxX8naEdTIoBU4b65U2SX4SXg1ggT/qClQgYn69/8vCqHAb1YO+iCcExysNy3rKPRJDC/ySyHXiu46AU0CKE35NED6WX952CIAlLkqYUEvRhKCqgvEK+uSDVw6xt10o0gpZdokF+zi6GSR268CSy1GoV/ZQBDh1FyqA==",

    //编码格式
    'charset' => "UTF-8",

    //支付宝网关
    'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

    //应用ID
    'app_id' => "2019081666212942",

    //异步通知地址,只有扫码支付预下单可用
    'notify_url' => "http://www.baidu.com",

    //最大查询重试次数
    'MaxQueryRetry' => "10",

    //查询间隔
    'QueryDuration' => "3"
);

?>