<?php
$config = array (	
		//应用ID,您的APPID。
		'app_id' => "2019081666212942",

		//商户私钥，您的原始格式RSA私钥
    	'merchant_private_key' => "MIIEowIBAAKCAQEAr/i6msTgqqbON4uuUZM4aD1a5UtKdcfa/unt7Ft5S4IqHd3Ehzju4ghLNzIeHdr7Fhp9Speb4EBFAuyeJvZ58ErBxT1Oo4GT/Ah6sgwQTMvbhn1SCIhf0uSlt5BTQs2RJ5slTa/GXsSl+/iATPSNTwawNSlzsSWeBYMcHyr9Est9pvgQWs43vhhla0CbdFLAfSsLpg68djfceD7H4HHmJqfTxU+DZeSNYaKihRflOaeW/WWjjcHCWkRR5TWIP7RVPCVRzs4NWkgUIj2Rs5y+pUrZWGoKiKTnNm/KaIGHBDsmCQ1nfrk0ZUFEacFSxlYzkXGjGuhkh0eqa0AaUC6LPwIDAQABAoIBADsqYvt0pKFcEkvkwAiXeki6EFy18uQk/UHmP2PP4bl8m1KNZO2eO9tttHRremrJXNCyuVuiG/vLQCjai9ZH2vnq+LmrgkxBXhNIepBws+5OQJI4gqNxzt4mmH1enlAAtARgCqvTJNxqO/C7PdAiDXz7n7VQKaDvJ/FsgsEMACkYCLOIMwwlzWKAP8iA1uqni1IRosycvf6gvW7TWEPrUIZ7uAzBDlNb4AwFwBx5L0aRZL3kbfDqpMsVI2Vpd9c5A2snxdpvul+6um8l4zsOCjctk730lIFUFkUngyJ2sHHOwvWDT1TD18gKMpnqGswu6woUdiN9n7j1a5py2EvviskCgYEA69LUVDwq3m9+q8iUt3kFq6IC79Ypn7ZO0ZAzQeAjc5/Cacyr43NuzG2oOLJhc0V+GkbuuEMW2K3tIiv8F3Ge0EuzNApxyPTYXhBP2IImg0bl75s8cWyMF9YJlF2PRjd6coG6wIcgJshtLhZSql+dLzouOBjoBrpZIBIg/QPUcQsCgYEAvwb7B9pEwvHk4L59MYN9/jeisMAckarud6qcu/mMucUnTeuRXE2Qp/Dg6RsillgFhrZhlzKKx/YXd+kgYMCwugrouR5wT2qR/mGTuhalBo0ipUTEQ9jhbqqg4MeHoBKBDLRK5K40qB7DWZqPcmLqVQ/aokBbe6PcZfxWABH5Vx0CgYByqSLWBh8t4fYsHi24+533MqHM7Ut1vVWbbUqO2CVEncQQXxSgVcrkmNi3IHxjaMGEk0KU0wHzvrVS7SH4YCVAz93FCuMCO5JMQhAgjD+fisRX+RmtaILBQC+ONONp16WwsUUVQV4vnToAs5fkAPNTOC2q1ulSBB87ibUCcG9FuwKBgDGLt/RTcl41qy13erUq67TR7Up2qXJGqU80Wy6ODSfpsMYPAUa1f31vhoYvWYsxDU3hY/J5U3GFrJSXTKSLlcrLJY1ODccrVu5plI1BblACrye6bEVErUOs0ck7NzwXUI1g/cPOQy4PvI8y/V2ntiuVzxkiJBgvaeyxxASEpO0pAoGBAL4usvEknbJzeSd+Rh/iIKVMjyOrkSeWcfvxcxIr6YahSUAYMJs3K5P3VNTxwf1GHnREiBp3D3DQ3/e4Pf1LtYEFSBDJTsHx2W8RfT9SN3RuF3xHiklkD71yNnsYM7zoJmKZvXPvfYrgP7KC+2hcBopEsW/nlNt1UX0OMz54xMiF",
    
        //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' =>"MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA9FY66+vC5KvSuqwgq3uz7ImMN6qL8aMKnOqExOyrSeK+tca/+LbFmStiBAqHR0P3NkgqD1ElzKnl2t9qKqWpLuxxfIcJVatrjugNpJEZgEml7xmt594zvHlPoN5lIuIIogDWyWm3IVffREEwXyEZf//ybuTSjbUNYOANndD4ecpJGOlDya0M5iU9TB9HsbBTi5KIq4ezzeBjUtDdy6xIkdFvVF3OisvSk2iSG6XW5T9U67O4nNVUefFZFtOj7v8cUOE8hpbQZy1YyX1ZZMotSmKszy19bRoss8pgXReqzNZVpqenDgvNOYpnNIYxP76BP+LKSWxvmJmDpSPLszy4mwIDAQAB",
    
		//异步通知地址
		 'notify_url' => "http://jun.ydtkt.com/index/course_pay/successUrl",
		//'notify_url' => "http://www.yangydt.com/index/course_pay/successUrl",
		
		//同步跳转
		 'return_url' => "http://jun.ydtkt.com/index/course_pay/return_url",
		//'return_url' => "http://www.yangydt.com/index/course_pay/return_url",

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",
);