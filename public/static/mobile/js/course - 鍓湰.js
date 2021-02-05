//获取页面数据
function getCourseList(courseData3)
{
	$.post('/index/course/getCourseList',{courseData:courseData3},function(res){
		if(res.error_code==1){
			var data = res.data;
			var html='';
			for(x in data){
				html+=`
					<li class="mui-table-view-cell">
						<a href="courseParticulars?id=${data[x].id}">
						<img class="mui-media-object mui-pull-left course-list-img" src="${data[x].img}">
							<div class="mui-media-body">
								<p class="course-list-title">${data[x].name}(${data[x].Semester})</p>
            					<p class="course-list-num">课程数量：${data[x].countClassChapter}节</p>
            					<p class="course-list-people">已有${data[x].popularity}人学习了该课程</p>
							</div>
						</a>
					</li>
					`;					
			}
			$('#OA_task_1').html(html);
			// $('#page').html(res.msg);
		}else{
			var html=res.msg;
			$('#OA_task_1').html(html);
			// $('#page').html('');
		}
	})
}
window.onload=function(){
	// pagenow=1;
	courseData1={};
	sessionStorage.setItem("term",'');
	sessionStorage.setItem("grade",'');
	
	term=sessionStorage.getItem("term");
	courseData1.term=term;

	grade=sessionStorage.getItem("grade");
	courseData1.grade=grade;

	getCourseList(courseData1);
}
//分页js
// function pagebody(pagenow)
// {
// 	courseData2={};
// 	term=sessionStorage.getItem("term");
// 	courseData2.term=term;

// 	grade=sessionStorage.getItem("grade");
// 	courseData2.grade=grade;
	
// 	getCourseList(pagenow,courseData2);
// }

	
//点击确定按钮
function selectClass(){
	var courseData ={};
	var term = "";
	var grade = "";

	var term = $(".course-change-item .course-change-current").data("id");
	sessionStorage.setItem("term",term);
	courseData.term= term;
	var grade = $(".course-change-subject .course-change-current").data("value");
	sessionStorage.setItem("grade",grade);
	courseData.grade = grade;

	getCourseList(courseData);
	$(".course-list-modal").animate({left:"100%"},800);
}
  
 