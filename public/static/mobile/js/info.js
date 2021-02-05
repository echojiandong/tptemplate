 //页面回退
 $('.header-back').click(function(){
    window.history.back(-1);
  })
  function isIphoneX() {
    return /iphone/gi.test(navigator.userAgent) && (screen.height == 812 && screen.width == 375)
  } 
  if(isIphoneX()) {
    $(".info-submit").css({"margin":"7.3% auto 0"})
    $(".course-list-change").css({height:"94%"})
  }
  // 点击筛选按钮左滑出现页面
  $(".course-search-screen").click(function() {
    $(".course-list-modal").animate({left:"0"},800)
  })
  $(".course-list-div").click(function() {
    $(".course-list-modal").animate({left:"100%"},800)
  })
	var data ={
		nickName:'',
		birthday:'',
		phone:'',
		email:'',
		wechat:'',
		school:'',
	},birthday = $('#birthday'),area= $('#area'),grade_id = $('#grade_id');
  var _this = this;
    (function($) {
      $.init();
      var result = $("#result")[0];
      var btns = $(".btn");
      btns.each(function(i, btn) {
        btn.addEventListener(
          "tap",
          function() {
            var _self = this;
            if (_self.picker) {
              _self.picker.show(function(rs) {
                console.log(rs.value);
                console.log(i);
                btn.innerText = rs.text;
                _this.setLimitData(i, rs.text);
                _self.picker.dispose();
                _self.picker = null;
              });
            } else {
              var optionsJson = this.getAttribute("data-options") || "{}";
              var options = JSON.parse(optionsJson);
              var id = this.getAttribute("id");
              /*
							 * 首次显示时实例化组件
							 * 示例为了简洁，将 options 放在了按钮的 dom 上
							 * 也可以直接通过代码声明 optinos 用于实例化 DtPicker
							 */
              _self.picker = new mui.DtPicker(options);
              _self.picker.show(function(rs) {
								btn.innerText =  rs.text;
								data.birthday = btn.innerText
								birthday.val(btn.innerText)
                _self.picker.dispose();
                _self.picker = null;
              });
            }
          },
          false
        );
      });

    })(mui);
    (function($, doc) {
				$.init();
				$.ready(function() {
					/**
					 * 获取对象属性的值
					 * 主要用于过滤三级联动中，可能出现的最低级的数据不存在的情况，实际开发中需要注意这一点；
					 * @param {Object} obj 对象
					 * @param {String} param 属性名
					 */
					var _getParam = function(obj, param) {
						return obj[param] || '';
					};
					//普通示例
					var userPicker = new $.PopPicker();
					userPicker.setData([{
						value: '7',
						text: '初一'
					}, {
						value: '8',
						text: '初二'
					}, {
						value: '9',
						text: '初三'
					}]);
					var showUserPickerButton = doc.getElementById('showUserPicker');
					// var userResult = doc.getElementById('userResult');
					showUserPickerButton.addEventListener('tap', function(event) {
						userPicker.show(function(items) {
							showUserPickerButton.innerText = JSON.stringify(items[0].text).replace('\"', "").replace('\"',"");
							data.grade_id = items[0].value
							grade_id.val(items[0].value);
						});
					}, false);
					//-----------------------------------------
					//					//级联示例
					var cityPicker3 = new $.PopPicker({
						layer: 3
					});
					cityPicker3.setData(cityData3);
					var showCityPickerButton = doc.getElementById('showCityPicker3');
					// var cityResult3 = doc.getElementById('cityResult3');
					showCityPickerButton.addEventListener('tap', function(event) {
						cityPicker3.show(function(items) {
							showCityPickerButton.innerText = _getParam(items[0], 'text') + "-" + _getParam(items[1], 'text') + "-" + _getParam(items[2], 'text');
							data.city = _getParam(items[0], 'text');
							data.province = _getParam(items[1], 'text')
							data.country = _getParam(items[2], 'text')
							area.val(showCityPickerButton.innerText);

						});
					}, false);
				});
			})(mui, document);

  //  提交按钮
  document.getElementById("info-submit").addEventListener('tap', function() {

		var username = $(".info-input-name").val(),
				gender = $("input[name='gender']:checked").val();
				phone = $(".info-input-phone").val(),
				email = $(".info-input-email").val(),
				wechat = $(".info-input-wechat").val(),
				school = $(".info-input-school").val();
				birthday = $("input[name='birthday']").val();
				grade_id = $("input[name='grade_id']").val();
				area = $("input[name='area']").val();
				data.nickName = username
				data.phone = phone
				data.email = email
				data.wechat= wechat
				data.school = school
				if (username.length == '' || phone.length =='' || email.length == '' || wechat.length == '' || school.length == '' || birthday.length == '' || grade_id.length == '' || area.length == '' || gender == undefined) {
					mui.alert("请填写完整信息");
				}else if(!(/^(((13[0-9]{1})|(14[0-9]{1})|(17[0-9]{1})|(15[0-3]{1})|(15[5-9]{1})|(18[0-9]{1}))+\d{8})$/.test(phone))){
					mui.alert("请填写正确手机号");
				} else if(!(/^[a-z\d]+(\.[a-z\d]+)*@([\da-z](-[\da-z])?)+(\.{1,2}[a-z]+)+$/.test(email))){
					mui.alert("请填写正确邮箱");
				}else {
					data.gender = gender
					mui.post('/index/person/updatePerson',data,function(res){
						if(res.error_code == 1){
							mui.alert('修改成功','',function(){
								window.location.href = '/index/person/person'
							});
						}
					},'json')
				}
				return false;
			});