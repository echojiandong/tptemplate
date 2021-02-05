function getCourseList(courseData3)
{
	$.post('/index/course/getCourseList',{pagenow:1,courseData:courseData3,pageNum:5},function(res){
		if(res.error_code==1){
			var data = res.data;
			var html='';
			for(x in data){
				html+='<li class="mui-table-view-cell"><img class="mui-media-object mui-pull-left course-list-img" src="'+data[x].img+'">'+
						'<div class="mui-media-body"><p class="course-list-title">'+data[x].name+'('+data[x].Semester+')</p>'+
							'<p class="course-list-num">课程数量：'+data[x].countClassChapter+'节</p>'+
							'<p class="course-list-people">已有'+data[x].popularity+'人学习了该课程</p>'+
							'<span class="course-list-study"><a href="/index/course/goWx">立即学习</a></span>'+
						'</div></li>';					
			}
			$('#OA_task_1').html(html);
		}else{
			var html=res.msg;
			$('#OA_task_1').html(html);
		}
	})
}
var pager = {};
var totalPage,courseData={};
pager['page'] = 1;//页码 
pager['size'] = 5;//页码
courseData.term = $('.course-change-current').attr('data-sid');

courseData.grade = $('.course-change-current').attr('data-gid');


sessionStorage.setItem("page",pager.page);
	
//点击确定按钮
function selectClass(){
	// var pager = {};
	// pager.page = 2;//页码 
	// sessionStorage.setItem("page",pager.page);
	var courseData ={};
	var term = "";
	var grade = "";

	// var term = $(".course-change-item .course-change-current").data("id");
	// sessionStorage.setItem("term",term);
	// courseData.term= term;
	// var grade = $(".course-change-subject .course-change-current").data("value");
	// sessionStorage.setItem("grade",grade);
	// courseData.grade = grade;
	var type = $('.course-change-current').attr('data-type');
	if(type == 1)
	{
		//张雪燕老师部分
		$('.zhangxueyan-content').show();
		$('.mui-content').hide();
	}else{
		$('.zhangxueyan-content').hide();
		$('.mui-content').show();
		courseData.term = $('.course-change-current').attr('data-sid');

		courseData.grade = $('.course-change-current').attr('data-gid');

		getCourseList(courseData);
	}	
	$(".course-list-modal").animate({left:"100%"},800);
}

$('.course-search-btn').click(function(){
	console.log(1);
	$('.course-search-form').submit();
})
  
 