layui.use(['element'], function(){
	var element = layui.element
		,pageNum = 10; 
//获取页面数据
function getCourseList(pagenow,courseData3,pageNum)
{
	$.post('/index/course/getCourseList',{pagenow:pagenow,courseData:courseData3,pageNum:pageNum},function(res){
		if(res.error_code==1){
			var data = res.data;
			var html='';
			for(x in data){

				var studyPeriod = data[x].studyPeriod;
				var allPeriod = data[x].countClassChapter;
				var rateOfLearning = studyPeriod/allPeriod;
				var productStatus = (data[x].productUrl==null)?0:1;
console.log(productStatus);
				html+='<li class="course-list-contain-lis clearfix">';
			
				if(data[x].is_buy == 1){
					html += '<span class="course-list-contain-lis-buy">已买</span>';
					html += '<div class="course-contain-lis-left">';
					//   '<a href="courseParticulars?id='+data[x].id+'"></a>'+
					if(rateOfLearning == 0){
						html += '<a class="course-right-study" href="courseParticulars?id='+data[x].id+'&productStatus='+productStatus+'&type=1'+'" >'+
						'<img src="'+data[x].img+'"></a>';
					}else{
						html += '<a class="course-right-study" href="courseParticulars?id='+data[x].id+'&productStatus='+productStatus+'&startTime='+data[x].study_time+'&videoid='+data[x].video_id+'&type=1'+'" >'+
						'<img src="'+data[x].img+'"></a>';
					}
					//   '<img src="'+data[x].img+'">'+
					html += '</div>'+
					'<div class="course-contain-lis-right">'+
					'<div class="lis_right_title">'+
						'<span class="course-right-title">'+data[x].name+'('+data[x].Semester+')</span>'+
						'<span class="course-right-title lis_right_title_span">课程数量：'+data[x].countClassChapter+'节</span>'+
						// '<span class="course-right-title lis_right_title_span">总时长：'+data[x].allTime+'</span>'+
					'</div>'+
					'<div class=" layui-progress">'+
						'<div class="layui-progress-bar" lay-percent="'+rateOfLearning*100+'%"></div>'+
					'</div>'+
					'<div class="course-right-num">'+
						'<span>已学习：'+data[x].studyPeriod+'节</span>'+
						'<span>已学习：'+data[x].studyTime+'</span>'+
						// '<span class="course-right-num-de"><a href="/index/index/getClassList?id='+data[x].video_class_id+'&intro='+data[x].name+'">查看目录</a></span>'+
					'</div>';
					if( rateOfLearning == 0) {
						html += '<a class="course-right-study" href="courseParticulars?id='+data[x].id+'&productStatus='+productStatus+'" >立即学习</a>';
					} else {
						html += '<a class="course-right-study" href="courseParticulars?id='+data[x].id+'&productStatus='+productStatus+'&startTime='+data[x].study_time+'&videoid='+data[x].video_id+'" >继续学习</a>';
					}        	
					html+='</div></li>';	
				} else if (data[x].is_buy == 0) {
					// html += '<span class="course-list-contain-lis-not-buy">未买</span>';
					html += '<div class="course-contain-lis-left">'+
						'<a href="courseParticulars?id='+data[x].id+'&productStatus='+productStatus+'">'+
								'<img src="'+data[x].img+'"></a>'+
								'</div>'+
								'<div class="course-contain-lis-right">'+
								'<div class="course-contain-lis-right-top"> '+
								'<h4>'+data[x].name+'('+data[x].Semester+')</h4><span>课程数量：'+data[x].countClassChapter+'节</span>'+
								'</div>'+
								'<div class="course-right-num">'+
								'<p class="course-right-introduce">课程介绍：'+data[x].content+'</p>'+
								'</div>'+
								'<a class="course-right-study course-look-contnet" href="courseParticulars?id='+data[x].id+'&productStatus='+productStatus+'" >查看详情</a>'+
								'</div></li>';
        		}
      
			}
			$('.course-list-contain').html(html);
			element.render();
      		$('#page').html(res.msg);
      		// 限制课程字数
			$(".course-right-num .course-right-introduce").each(function() {
				  var maxwidth = 86;
				  if ($(this).text().length > maxwidth) {
				    $(this).text($(this).text().substring(0, maxwidth));
				    $(this).html($(this).html() + '...');
				  }
			});
		}else{
			var html=res.msg;
			$('.course-list-contain').html(html);
			$('#page').html('');
		}
	})
}
	
// window.ooad=function(){
$(function(){
	var courseData1 = {};

	courseData1.grade = $('.course-list-lis-click').attr('data-gid');

	courseData1.term = $('.course-list-lis-click').attr('data-sid');
	// console.log(courseData1);
	 getCourseList(1,courseData1,pageNum);
// }
})
//分页js
window.pagebody = function(pagenow)
{
	var courseData1 = {};

	courseData1.grade = $('.course-list-lis-click').attr('data-gid');

	courseData1.term = $('.course-list-lis-click').attr('data-sid');
	getCourseList(pagenow,courseData1,pageNum);
}

// 年级，学科，版本三级联动
  var courseData ={};
	var term = "",
		grade = "";

	$('.course-list-lis').click(function(){
		$('.course-list-lis').removeClass('course-list-lis-click');

		$(this).addClass('course-list-lis-click');

		if ($(this).index() == 6 ) {
			//点击张雪燕老师课程部分
			$('.zhangxueyan-content').show();
			$('.course-list-contain').hide();
			$('.course-pages').hide();
		} else {
			//点击正常课程
			$('.zhangxueyan-content').hide();
			$('.course-list-contain').show();
			$('.course-pages').show();
			var courseData1 = {};
			courseData1.grade = $(this).attr('data-gid');
			courseData1.term = $(this).attr('data-sid');
			getCourseList(1,courseData1,pageNum);
		}
	})

})