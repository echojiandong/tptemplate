
/*!
 * 获取浏览器和操作系统的信息
 */
(function(h){var f={},d=navigator.userAgent.toLowerCase(),b,a=d.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i)||[];/trident/i.test(a[1])&&(b=/\brv[ :]+(\d+)/g.exec(d)||[],a[1]="IE",a[2]=b[1]||"");"Chrome"===a[1]&&(b=d.match(/\b(OPR|Edge)\/(\d+)/),null!=b&&(a[1]="Opera",a[2]=b[1]||""));a=a[2]?[a[1],a[2]]:[navigator.appName,navigator.appVersion,"-?"];null!=(b=d.match(/version\/(\d+)/i))&&a.splice(1,1,b[1]);f.browser=a[0];f.browserVersion=a[1];var d=[{s:"Windows 10",r:/(Windows 10.0|Windows NT 10.0)/},{s:"Windows 8.1",r:/(Windows 8.1|Windows NT 6.3)/},{s:"Windows 8",r:/(Windows 8|Windows NT 6.2)/},{s:"Windows 7",r:/(Windows 7|Windows NT 6.1)/},{s:"Windows Vista",r:/Windows NT 6.0/},{s:"Windows Server 2003",r:/Windows NT 5.2/},{s:"Windows XP",r:/(Windows NT 5.1|Windows XP)/},{s:"Windows 2000",r:/(Windows NT 5.0|Windows 2000)/},{s:"Windows ME",r:/(Win 9x 4.90|Windows ME)/},{s:"Windows 98",r:/(Windows 98|Win98)/},{s:"Windows 95",r:/(Windows 95|Win95|Windows_95)/},{s:"Windows NT 4.0",r:/(Windows NT 4.0|WinNT4.0|WinNT|Windows NT)/},{s:"Windows CE",r:/Windows CE/},{s:"Windows 3.11",r:/Win16/},{s:"Android",r:/Android/},{s:"Open BSD",r:/OpenBSD/},{s:"Sun OS",r:/SunOS/},{s:"Linux",r:/(Linux|X11)/},{s:"iOS",r:/(iPhone|iPad|iPod)/},{s:"Mac OS X",r:/Mac OS X/},{s:"Mac OS",r:/(MacPPC|MacIntel|Mac_PowerPC|Macintosh)/},{s:"QNX",r:/QNX/},{s:"UNIX",r:/UNIX/},{s:"BeOS",r:/BeOS/},{s:"OS/2",r:/OS\/2/},{s:"Search Bot",r:/(nuhk|Googlebot|Yammybot|Openbot|Slurp|MSNBot|Ask Jeeves\/Teoma|ia_archiver)/}],e,c,g;for(g in d)if(b=d[g],b.r.test(navigator.userAgent)){e=b.s;break}/Windows/.test(e)&&(c=/Windows (.*)/.exec(e)[1],e="Windows");switch(e){case "Mac OS X":c=/Mac OS X (10[\.\_\d]+)/.exec(navigator.userAgent)[1];break;case "Android":c=/Android ([\.\_\d]+)/.exec(navigator.userAgent)[1];break;case "iOS":c=/OS (\d+)_(\d+)_?(\d+)?/.exec(navigator.appVersion),c=c[1]+"."+c[2]+"."+(c[3]|0)}f.os=e;f.osVersion=c;h.boInfo=f})(window);
 //判断用户使用的是否是谷歌浏览器
   /* var isChrome = window.navigator.userAgent.indexOf("Chrome") !== -1;
    var is_obj = window.chrome? window.chrome.runtime : "";*/
    function checkBrowser(){  
		var ua = navigator.userAgent.toLocaleLowerCase(); 
		console.log(ua) 
		// console.log(ua.match(/msie/))
    	var browserType=null;  
    	if (ua.match(/msie/) != null || ua.match(/trident/) != null) {  
			browserType = "IE";  
			browserVersion = ua.match(/msie ([\d.]+)/) != null ? ua.match(/msie ([\d.]+)/)[1] : ua.match(/rv:([\d.]+)/)[1];  	   
    	} else if (ua.match(/firefox/) != null) {  
    	       browserType = "火狐";  
    	}else if (ua.match(/ubrowser/) != null) {  
    	       browserType = "UC";  
    	}else if (ua.match(/opera/) != null) {  
    	       browserType = "欧朋";  
    	} else if (ua.match(/bidubrowser/) != null) {  
    	       browserType = "百度";    
    	}else if (ua.match(/metasr/) != null) {  
    	       browserType = "搜狗";    
    	}else if (ua.match(/tencenttraveler/) != null || ua.match(/qqbrowse/) != null) {  
    	       browserType = "QQ";  
    	}else if (ua.match(/maxthon/) != null) {  
    	       browserType = "遨游";  
    	}else if(ua.match(/edge/) != null) {
    		browserType = "edge";  
    	}else if (ua.match(/chrome/) != null) {  
			
    	var is360 = _mime("type", "application/vnd.chromium.remoting-viewer");  
    	function _mime(option, value) {  
    	            var mimeTypes = navigator.mimeTypes;  
    	            for (var mt in mimeTypes) {  
    	            if (mimeTypes[mt][option] == value) {  
    	                   return true;  
    	              }  
    	            }  
    	            return false;  
    	        }  
    	if(is360){                 
    		browserType = '360';    
	     }else{    
		browserType = "谷歌";   
	     }    
    	         
    	}else if (ua.match(/safari/) != null) {  
    	       browserType = "Safari";  
    	}  
    	return browserType;  
	}  
	// 判断是否出现下载横条
	if (checkBrowser() != "IE" || browserVersion >= 10.0) {
    } else {
        $(".check_brower_2").removeClass("hide")	
    }
    //获取当前系统及版本
    var os = boInfo.os;
    var osVersion = boInfo.osVersion;
    var cpuClass = "";
    var agent=navigator.userAgent.toLowerCase();
    if(agent.indexOf("win64")>=0||agent.indexOf("wow64")>=0){
        cpuClass = "x64";
    }
    //点击下载
    var chromeXP = 'https://www.ydtkt.com/software/49.0.2623.112_chrome_installer_xp.exe';
    var chromeWin32 = 'https://www.ydtkt.com/software/68.0.3440.106_chrome_installer-x32.exe';
    var chromeWin64 = 'https://www.ydtkt.com/software/ChromeStandalone_64.0.3282.119_Setup.exe';
    var chromeMacUrl = 'https://www.ydtkt.com/software/googlechrome.dmg';
    $(".brower_upload_edition").on("click", function() {
        var _this = $(this);
        if(os == "Windows") {
            if(osVersion == "XP"){
                downSrc = chromeXP;
            } else if(cpuClass !== "x64") {
                downSrc = chromeWin32;
            } else {
                downSrc = chromeWin64;
            }
        } else {
            downSrc = chromeMacUrl;
        }
        downLoad(downSrc)
    })
    //下载软件
    function downLoad(softSrc){  
	    var elemIF = document.createElement("iframe");     
	    elemIF.src = softSrc;    
	    elemIF.style.display = "none";     
	    document.body.appendChild(elemIF);  
	} 