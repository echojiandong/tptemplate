// function drag(id){
// 		var oDiv = document.getElementById(id);
// 		// var oDiv = document.getElementsByClassName(className)
// 		var disX = 0;
// 		var disY = 0;
// 		oDiv.onmousedown=function(ev){
// 			var oEvent = ev||event;
// 			disX = oEvent.clientX-oDiv.offsetLeft;
// 			disY = oEvent.clientY-oDiv.offsetTop;
// 			document.onmousemove=function(ev){
//               var oEvent = ev||event;
//               var l = oEvent.clientX-disX;
//               var t = oEvent.clientY-disY;
//               if (l<50) {
//                  l=0;
//               } else if(l>document.documentElement.clientWidth-oDiv.offsetWidth-50){
//               	l=document.documentElement.clientWidth-oDiv.offsetWidth;
//               }
//               if (t<50) {
//                    t=0;
//               } else if(t>document.documentElement.clientHeight-oDiv.offsetHeight-50){
//               	t = document.documentElement.clientHeight-oDiv.offsetHeight;
//               }
//               oDiv.style.left=l+'px';
//               oDiv.style.top=t+'px';
// 			}
// 			document.onmouseup=function(){
// 				document.onmousemove=null;
// 				document.onmouseup=null;
// 			}
// 			return false;
// 		}
// 	}

function drag(obj) {
  // console.log(obj)
  var disx = 0;
  var disy = 0;
  obj.onmousedown = function(e){
    
      var e = e || event;
      disx = e.clientX - obj.offsetLeft;
      disy = e.clientY - obj.offsetTop;
      document.onmousemove = function(e){
        // console.log(e)
        var e = e || event;
        var l = e.clientX-disx;
        var t = e.clientY-disy;
        if (l<50) {
            l=0;
          } else if(l>document.documentElement.clientWidth-obj.offsetWidth-50){
              l=document.documentElement.clientWidth-obj.offsetWidth;
          }
          if (t<50) {
              t=0;
          } else if(t>document.documentElement.clientHeight-obj.offsetHeight-50){
              t = document.documentElement.clientHeight-obj.offsetHeight;
          }
          obj.style.left=l+'px';
          obj.style.top=t+'px';
      }
      document.onmouseup = function(){
          document.onmousemove = null;
          document.onmouseup = null;
      }
      return false;
  }
}