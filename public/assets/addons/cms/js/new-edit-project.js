//判断所属领域
var industryCodeStr = $("#thindustryCode").text(),
	industryArr = industryCodeStr.split(','),
	projectId = $("#projectId").val(),
	windowHeight = $(window).height() / 2;

$("#nodelet,#delMember,#joined,#resultTip,#delete").find('.modal-dialog').css({
	'margin-top': windowHeight - 130
});
$("#commonModal").find('.modal-dialog').css({
	'margin-top': windowHeight - 150
});
$("#team,#editTeacher,#teacher").find('.modal-dialog').css({
	'margin-top': windowHeight - 280
});
//文件改变事件
var files = {};
files["doc"] = $("#doc").next().html();
files["ppt"] = $("#ppt").next().html();
files["mp4"] = $("#mp4").next().html();

if($("#commonModal").length>0){
	$("#commonModal").modal("show");
	$("#commonModal").on("hidden.bs.modal",function(){
		window.location.href="/talentproject/edit/"+$("#projectId").val();
	});
}

if (tutorErr == 'false') {
	$('#teacher').collapse('show');
}
//后台编辑教师错误

if (EdittutorErr == 'false') {
	$('#editTeacher').collapse('show');
	var action = $('#editTeacher form').attr("action")
	$('#editTeacher form').attr("action",
		action + "/" + $("#teachId").val());
}
//后台添加专利错误

if (patentErr == 'false') {
	$('#patent').collapse('show');
}
//后台编辑专利错误

if (EditpatentErr == 'false') {
	$('#editPatent').collapse('show');
	var action = $('#editPatent form').attr("action")
	$('#editPatent form').attr("action",
		action + "/" + $("#patentId").val())
}

function getFlieName(e, format, type) {
	$(e).change(function (i) {
		var $parent = $(e).parent();
		if ($(e)[0].files.length > 0) {
			var val = $(e)[0].files[0].name;
			var fileTypes = format.split(",");
			var fileTypeFlag = "0";

			var newFileName = val.split('.');
			newFileName = newFileName[newFileName.length - 1];
			for (var i = 0; i < fileTypes.length; i++) {
				if (fileTypes[i] == newFileName) {
					fileTypeFlag = "1";
				}
			}
			if (fileTypeFlag == "1") {
				$parent.next().empty().html('<span class="fixed-width">' + val + '</span>').append('<img src="https://t2.chei.com.cn/ncss/cy/web/img/success.svg" width=15 height=15>');
			}
			if (fileTypeFlag == "0") {
				$parent.next().empty().html(val);
			}
		} else {
			$parent.next().empty().html(files[type]);
		}
	});
}

function del(e) {
	e.click(function () {
		var value = $(this).attr('value').split('_');
		$('.delete').attr('action', value[0]);
		$('.del-html').html('确认删除 ' + value[1] + ' ' + value[2]);
		$('#mySmallModalLabel').html($(this).attr('title'));
	})
}
//指导老师、专利删除
del($('.team-del'));

$(".patentTime").datetimepicker({
	format: 'yyyy-mm-dd',
	autoclose: true,
	todayBtn: true,
	language: 'zh-CN',
	minView: "month",
	endDate: new Date()
});

function otherFn() {
	//编辑教师
	$("#teacherBox").on("click", ".edit-teacher", function () {
		$("#editTeacher").find(".help-block").hide();
		$("#editTeacher").find(".form-group").removeClass("has-error");
		$("#editTeacher").find(".btn-primary").removeAttr("disabled");
		$("#teacher").collapse("hide");
		var value = $(this).attr('value').split('_');
		$('#editTeacher').find('form').attr('action', '/talentproject/edittutor/' + value[0]);
		$("#teachId").val(value[0]);
		$('#editTeacher').find('input[name="name"]').val(value[1]);
		$('#editTeacher').find('input[name="phone"]').val(value[2]);
		$('#editTeacher').find('input[name="email"]').val(value[3]);
		$('#editTeacher').find('input[name="schoolName"]').val(value[5]);
		$('#editTeacher').find('input[name="department"]').val(value[6]);
		$('#editTeacher').find('input[name="jobTitle"]').val(value[7]);
		$('#schoolCode2').html('<option selected="selected" value="' + value[4] + '">' + value[5] + '</option>');
		$('#select2-schoolCode2-container').html(value[5]);
		$("#editTeacher form").data('bootstrapValidator').resetForm()
		$("#editTeacher").collapse("show");
	});
	$("#addTeacher").on("click", function () {
		$("#editTeacher").collapse("hide");
		$("#teacher").collapse("show");
	})
	$('#teacher').on("hidden.bs.collapse", function () {
		$("#teacher").find(".help-block").hide();
		$("#teacher").find(".form-group").removeClass("has-error");
		$("#teacher").find(".btn-primary").removeAttr("disabled");
	});
	//编辑专利
	$("#patentBox").on("click", ".edit-patent", function () {
		$("#editPatent").find(".help-block").hide();
		$("#editPatent").find(".form-group").removeClass("has-error");
		$("#editPatent").find(".btn-primary").removeAttr("disabled");
		$("#patent").collapse("hide");
		var value = $(this).attr('value').split('_');
		$('#editPatent').find('form').attr('action', '');
		$('#editPatent').find('form').attr('action', '/talentproject/editpatent/' + value[0]);
		$("#patentId").val(value[0]);
		$('#editPatent').find('input[name="name"]').val(value[1]);
		$('#editPatent').find('select[name="patentCategoryCode"] option').each(function () {
			if ($(this).val() == value[2]) {
				$(this).attr('selected', true);
			}
		});
		$('#editPatent').find('input[name="gainTime"]').val(value[3]);
		$('#editPatent').find('input[name="patentCode"]').val(value[4]);
		$("#editPatent").collapse("show");
	});
	$("#addPatent").on("click", function () {
		$("#editPatent").collapse("hide");
		$("#patent").collapse("show");
	});
	$('#patent').on("hidden.bs.collapse", function () {
		$("#patent").find(".help-block").hide();
		$("#patent").find(".form-group").removeClass("has-error");
		$("#patent").find(".btn-primary").removeAttr("disabled");
	});
	$('*[name]').on("blur", function () {
		$(this).parent().nextAll(".back-err").hide();
	});
	$("#schoolCode1").change(function () {
		$("#schoolName1").val($(this).find("option:selected").text())
		$("#schoolName1").attr("data-text", $(this).find("option:selected").val())
	})
	if ($("#schoolName1").val()) {
		$("#schoolCode1").html("<option selected value='" + $("#schoolName1").attr("data-text") + "'>" + $("#schoolName1").val() + "</option>")
	}
	$("#schoolCode2").change(function () {
		$("#schoolName2").val($(this).find("option:selected").text())
		$("#schoolName2").attr("data-text", $(this).find("option:selected").val())
	})
	if ($("#schoolName2").val()) {
		$("#schoolCode2").html("<option selected value='" + $("#schoolName2").attr("data-text") + "'>" + $("#schoolName2").val() + "</option>")
	}
	$(".school-code").select2({
		placeholder: "请选择院校",
		language: 'zh-CN',
		minimumInputLength: 2,
		//tags:true,
		ajax: {
			url: "/talent/schools",
			dataType: 'json',
			data: function (term, page) {
				return {
					key: term.term,
				};
			},
			processResults: function (data, params) {
				params.page = params.page || 1;
				var rst = [];
				// 修改成合适的数据格式 [{id:text}]
				for (var i in data) {
					rst.push({
						id: data[i].schoolCode,
						text: data[i].schoolName
					});
				};
				return {
					results: rst,
					pagination: {
						more: (params.page * 30) < data.total_count
					}
				};
			},
		},
		cache: true
	});
	
	//删除成员
	$("#memberBox").on("click", ".member-del", function () {
		var $this = $(this);
		if ($this.attr("data-id")) {
			memberId = $this.attr("data-id");
			memberType = $this.attr("data-type");
			$("#delMember .member-name").text($this.attr("data-name"));
		}
	});

	$("#delMember .modal-btn-info-save").on("click", function () {
		if (memberType == "old") {
			$.ajax({
				url: "/talentproject/deletemembernow",
				type: "post",
				data: {
					projectId: projectId,
					oldMemberId: memberId
				},
				success: function (data) {
					if (data.flag == false) {
						$("#delMember").modal("hide");
						$("#resultTip").modal("show");
						$("#resultMessage").text(data.message);
						$("#resultTitle").text("删除失败");
						$("#resultIcon").attr("class", "project-modal-fail");
					} else {
						window.location.href = "/talentproject/edit/" + projectId;
					}
				}
			});
		} else {
			$.ajax({
				url: "/talentproject/deletemembernow",
				type: "post",
				data: {
					projectId:projectId,
					memberId: memberId
				},
				success: function (data) {
					if (data.flag == false) {
						$("#resultTip").modal("show");
						$("#resultMessage").text(data.message);
						$("#resultTitle").text("删除失败");
						$("#resultIcon").attr("class", "project-modal-fail");
					} else {
						window.location.href = "/talentproject/edit/" + projectId;
					}
				}
			});
		}
	});
	//验证团队
	$('#team').bootstrapValidator({
		fields: {
			name: {
				trigger: 'blur',
				validators: {
					notEmpty: {
						message: '用户姓名不能为空'
					}
				}
			},
			loginName: {
				trigger: 'blur',
				validators: {
					notEmpty: {
						message: '手机号码不能为空'
					},
					regexp: {
						regexp: /^\d+$/,
						message: '请输入正确的手机号码'
					}
				}
			}
		}
	});
	$(".teacher-form").bootstrapValidator({
		fields: {
			name: {
				trigger: 'blur',
				validators: {
					notEmpty: {
						message: '姓名不能为空！'
					},
				}
			},
			phone: {
				trigger: 'blur',
				validators: {
					notEmpty: {
						message: '手机号码不能为空'
					},
					regexp: {
						regexp: /^\d+$/,
						message: '请输入正确的手机号码'
					}
				}
			},
			email: {
				trigger: 'blur',
				validators: {
					notEmpty: {
						message: '邮箱不能为空！'
					},
					emailAddress: {
						message: '请输入正确的邮箱地址'
					},
				}
			},
			schoolCode: {
				trigger: 'blur',
				trigger: 'change',
				validators: {
					notEmpty: {
						message: '学院名称不能为空！'
					},
				},
			},
			department: {
				trigger: 'blur',
				validators: {
					notEmpty: {
						message: '所在部门不能为空！'
					},
				}
			},
			jobTitle: {
				trigger: 'blur',
				validators: {
					notEmpty: {
						message: '导师职称不能为空！'
					},
				}
			},
		}
	})
	$(".patent-form").bootstrapValidator({
		fields: {
			name: {
				trigger: 'blur',
				validators: {
					notEmpty: {
						message: '专利名称不能为空！'
					},
				}
			},
			patentCategoryCode: {
				trigger: 'blur',
				validators: {
					notEmpty: {
						message: '专利类别必须选填一项！'
					},
				}
			},
			patentCode: {
				trigger: 'blur',
				validators: {
					notEmpty: {
						message: '专利号不能为空！'
					},
				}
			},
			gainTime: {
				trigger: 'blur',
				trigger: 'change',
				validators: {
					notEmpty: {
						message: '专利获得时间不能为空！'
					},
					date: {
						format: 'YYYY-MM-DD',
						message: '请输入正确的日期！格式：2017-01-01',
					}
				}
			},
		}
	})
	//var projectId = $("#projectId").text();
	$("#saveInfoEdit").on("click", function () {
		//$("#team").bootstrapValidator('validate');
		$("#team").data('bootstrapValidator').validate();
		if ($("#team").data('bootstrapValidator').isValid()) {
			$.ajax({
				type: "get",
				url: "/talentproject/findtalentsformember", //+ projectId
				data: {
					projectId: projectId,
					name: $("#team input[name='name']").val(),
					loginName: $("#team input[name='loginName']").val()
				},
				success: function (data) {
					if (data.flag == true) {
						$(".member-result").html('<p>已为您搜索到以下用户：</p><p><span>姓名：' + data.name + '</span><span>手机号：' + data.loginName + '</span></p><p><button class="btn btn-primary" data-id="' + data.memberId + '">邀请该用户成为我的团队成员</button></p>');
					} else {
						$(".member-result").html("<p>" + data.errorMessage + "</p>");
					}
				},
				error: function (data) {
					$(".member-result").html("<p>出现异常，请稍后再试。</p>");
				}
			});
		} else {
			return false;
		}
	});
	$("#team input").on("keyup", function (e) {
		if (e.keyCode == 13) {
			$("#saveInfoEdit").click();
		}

	});

	$(".member-result").delegate(".btn-primary", "click", function () {
		var userId = $(this).attr("data-id");
		var memName = $("#memberName").val();
		$.ajax({
			type: "post",
			url: "/talentproject/invitation/",
			data: {
				projectId: projectId,
				memberId: userId
			},
			success: function (data) {
				//$("#team").collapse("hide");
				$("#resultTip").modal("show");
				if (data.flag == true) {
					$("#resultMessage").text("已成功发送邀请，请通知你的团队成员登录大创网→进入个人中心→点击“我的消息”，接受你的邀请。");
					$("#resultTitle").text("发送成功");
					$("#resultIcon").attr("class", "project-modal-success");
					/* $("#resultTip").on("hidden.bs.modal", function () {
						window.location.href = "/talentproject/edit/" + projectId;
					}); */
					var flag = true;
					$(".unconfirm").each(function(){
						console.log(memName,$(this).find("h4 span").text())
						if($(this).find("h4 span").text()==memName){
							flag = false;
						}
					});
					if(flag){
						var temp = '<li class="unconfirm"><div class="pic-box"><img src="//t2.chei.com.cn/ncss/cy/web/img/head_none.png"></div><div class="content-box"><h4><span style="max-width:150px;"> '+memName+'</span></h4><p>等待对方确认</p></div></li>';
						$(".member-list").append(temp);
					}
					$('#team').find("input").val("");
					$(".member-result").html("");
					$("#team").find(".help-block").hide();
					$("#team").find(".form-group").removeClass("has-error");
				} else {
					$("#resultMessage").text(data.message);
					$("#resultTitle").text("发送失败");
					$("#resultIcon").attr("class", "project-modal-fail");
				}
			},
			error: function (data) {
				//$("#team").modal("hide");
				$("#resultTip").modal("show");
				$("#resultTip").find(".project-modal-del").text("发生异常，请稍后再试。");
				$("#resultTitle").text("发送失败");
				$("#resultIcon").attr("class", "project-modal-prompt");
			}
		});
	});
	$('#team').on("hidden.bs.collapse", function () {
		var $this = $(this);
		$this.find("input").val("");
		$(".member-result").html("");
		$("#team").find(".help-block").hide();
		$("#team").find(".form-group").removeClass("has-error");
	});

	//领域选择
	var timer;
	$("#industryCodeBlocks").on("click", "li", function () {
		clearTimeout(timer);
		var $this = $(this);
		if ($this.hasClass("selected")) {
			if ($("#industryCode option:selected").length > 1) {
				$("#indusErr").addClass("hidden");
				$this.removeClass("selected");
				$("#industryCode option[value='" + $this.attr("data-value") + "']").prop('selected', false);
			} else {
				$("#indusErr").removeClass("hidden").text("所属领域不能为空！");
				timer = setTimeout(function () {
					$("#indusErr").addClass("hidden");
					clearTimeout(timer);
				}, 1500);
			}

		} else {
			if ($("#industryCode option:selected").length < 3) {
				$("#indusErr").addClass("hidden")
				$this.addClass("selected");
				$("#industryCode option[value='" + $this.attr("data-value") + "']").prop('selected', true);
			} else {
				$("#indusErr").removeClass("hidden").text("所属领域不能超过3个！");
				timer = setTimeout(function () {
					$("#indusErr").addClass("hidden");
					clearTimeout(timer);
				}, 1500);
			}
		}
	});
}

$(function () {
	$("form").on("submit", function () {
		var flag = true;
		if ($("#progress2")[0].checked) {
			if ($("input[name='isInvest']:checked").val() == "1") {
				$(".invest-list .form-control").each(function () {
					var $this = $(this),
						small = '.' + $this.attr("data-small"),
						emperr = $this.attr("data-emperr"),
						val = $this.val();
					if (val == ""|| val == null) {
						$this.addClass("has-error").nextAll(small).removeClass("hidden").text(emperr);
					}
					if ($this.hasClass("has-error")) {
						flag = false;
					}
				});
				$(".invest-item").each(function (index, ele) {
					$(this).find(".form-control").each(function () {
						var $this = $(this);
						var newName = $this.attr("name").replace(/\[\d+\]/, '[' + index + ']');
						$this.attr("name", newName);
					});
				});
			};

			$(".structure-list .form-control").each(function () {
				var $this = $(this);
				var small = '.' + $this.attr("data-small");
				var emperr = $this.attr("data-emperr");
				var val = $this.val();
				if (val == "" || val == null) {
					$this.addClass("has-error").nextAll(small).removeClass("hidden").text(emperr);
				}
				if ($this.hasClass("has-error")) {
					flag = false;
				}
			});

			$(".structure-item").each(function (index, ele) {
				$(this).find(".form-control").each(function () {
					var $this = $(this);
					var newName = $this.attr("name").replace(/\[\d+\]/, '[' + index + ']');
					$this.attr("name", newName);
				});
			});
		}
		if (!($("#errorMes").hasClass("hide"))) {
			$('html,body').scrollTop(0);
			flag = false;
		}

		if (!flag) {
			$("#hiddenSub").removeAttr("disabled");
			return false;
		}
	});

	/* var isIE = (function () {
		var ua = window.navigator.userAgent.toLowerCase();
		if (ua.indexOf("msie") > 0 || ua.indexOf("trident") > 0) {
			return true;
		} else {
			return false;
		}

	}());
	if (isIE) {
		$("#ieTip").removeClass("hidden");
	} */
	$("#subForm").on("click", function () {
		$("#hiddenSub").removeAttr("disabled");
		/* if (isIE) {
			$("#hiddenSub").trigger("click");
			$("#specialtyName").blur();
			$("#hiddenSub").trigger("click");
			$("#hiddenSub").trigger("click");
			$("form").submit();
			$("form").submit();
		} else {
			$("#hiddenSub").trigger("click");
		} */
		$("#hiddenSub").trigger("click");
	});


	//选择图片
	$("#checkimg").click(function () {
		$("#inputImage").val("");
		$('#userPic').removeClass("hide");
		$('#getCroppedCanvasModal').addClass("hide");
		$("#subForm").removeAttr("disabled");
		//$("#inputImage").click();
		//return false;
	});
	//头像裁剪上传
	$('#inputImage').on('change', function (e) {
		var filemaxsize = 1024 * 3;
		var target = $(e.target);
		if (target[0].files.length == 0) {
			$('#userPic').removeClass("hide");
			$('#getCroppedCanvasModal').addClass("hide");

		} else {
			var Size = target[0].files[0].size / 1024;

			if (!this.files[0].type.match(/image.(gif|jpg|jpeg|png|GIF|JPG|JPEG|PNG)$/)) {
				//$('#checkimg').next().html('请选择正确的图片!').show();
				$('#errorMes').html("请选择正确的图片!").removeClass("hide");
				$('#errorMes').next().css("display", "none");
				return false;
			} else if (Size > filemaxsize) {
				$('#errorMes').html("图片过大，请重新选择!").removeClass("hide");
				$('#errorMes').next().css("display", "none");
				return false;
			} else {
				$("#exampleModal").modal('show');
				var texts = document.querySelector("#inputImage").value;
				$("#photo").val("aaa");
				$('#errorMes').addClass("hide");
			}
		}
	});

    $('#avatar-save').on('click', function (e) {
		$('#imgWidth').val($('#dataWidth').val());
		$('#imgHeight').val($('#dataHeight').val());
		$('#imgX').val($('#dataX').val());
		$('#imgY').val($('#dataY').val());

		$("#userPic").addClass("hide");
		$('#errorMes').addClass("hide");
	});
	$('#avatar-cal').click(function () {
		$("#inputImage").val('');
		$('#userPic').removeClass("hide");
		$('#getCroppedCanvasModal').addClass("hide");

	});

	// 表单验证
	if ($("#pptExist").length > 0) {
		$('#mainForm').bootstrapValidator({
			fields: {
				name: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '项目名称不能为空！'
						},
						stringLength: {
							max: 50,
							message: '项目名称不多于50字！'
						}
					}
				},
				locationCode: {
					trigger: 'change',
					validators: {
						notEmpty: {
							message: '所在省市不能为空！'
						},
					}
				},
				cityCode: {
					trigger: 'change',
					validators: {
						notEmpty: {
							message: '所在省市不能为空！'
						},
					}
				},
				/* industryCode: {
					trigger: 'change',
					validators: {
						notEmpty: {
							message: '所属领域不能为空！'
						},
						choice: {
							max: 3,
							message: '所属领域最多选择三个！'
						}
					}
				}, */
				wasBindUniTechnology: {
					trigger: 'change',
					validators: {
						notEmpty: {
							message: '请选择项目是否与高校科技结果相结合！'
						},
					}
				},
				isCreateTogeter: {
					trigger: 'change',
					validators: {
						notEmpty: {
							message: '请选择是否为师生共创！'
						},
					}
				},
				isAchievementOwner: {
					trigger: 'change',
					validators: {
						notEmpty: {
							message: '请选择创始人是否为科技成果的完成人或所有人！'
						},
					}
				},
				wasEquityStructure: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '请选择股权结构！'
						},
					}
				},
				isInvest: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '请选择是否已获投资！'
						},
					}
				},
				synopsis: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '项目概述不能为空！'
						},
						stringLength: {
							min: 100,
							max: 1000,
							message: '请简介您的项目，字数在100-1000字间，空格也计入字数！'
						}
					}
				},
				founderSynopsis: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '创始人简介不能为空！'
						},
						stringLength: {
							max: 50,
							message: '请填写创始人简介，字数在50字以内，空格也计入字数！'
						}
					}
				},
				progress: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '项目进展必选一项！'
						},
					}
				},
				companyName: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '公司名称不能为空！'
						},
					}
				},
				legalRepresent: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '法人代表姓名不能为空！'
						},
					}
				},
				job: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '担任职务不能为空！'
						},
					}
				},
				isSupport:{
					trigger: 'change',
					validators: {
						notEmpty: {
							message: '请选择获得资金支持情况！'
						},
					}
				},
				registerCapital: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '注册资金不能为空！'
						},
						regexp: {
							regexp: /^((\d+)|(\d+\.\d{0,2}))$/,
							message: '资金只能为数字，请输入正确的金额！',
						}
					}
				},
				registerDate: {
					trigger: 'blur',
					trigger: 'change',
					validators: {
						notEmpty: {
							message: '注册时间不能为空！'
						},
						date: {
							format: 'YYYY-MM-DD',
							message: '请输入正确的日期！格式：2017-01-01。',
						}
					}
				},
				registerProvinceCode: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '注册所在地不能为空！'
						},
					}
				},
				registerAddress: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '注册所在地不能为空！'
						},
					}
				},
				organizationCode: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '统一社会信用代码不能为空！'
						},
					}
				},
				secretStatus: {
					validators: {
						notEmpty: {
							message: '隐私设置至少选择一项！'
						},
					}
				},
				physicalFile: {
					trigger: 'change',
					validators: {
						file: {
							extension: 'pdf,doc,docx',
							maxSize: 20 * 1024 * 1024,
							message: '格式为pdf、word，不超过20M，文件数量限一个！',
						},
					}
				},
				pptFile: {
					trigger: 'change',
					validators: {
						file: {
							extension: 'ppt,pptx',
							maxSize: 20 * 1024 * 1024,
							message: '格式为ppt、pptx，不超过20M，文件数量限一个！',
						},
					}
				},
				videoFile: {
					trigger: 'change',
					validators: {
						file: {
							extension: 'mp4',
							maxSize: 20 * 1024 * 1024,
							message: '请使用MP4格式的视频，文件大小不超过20M，时长不超过1分钟；生成视频时，视频编码为H.264，音频编码为AAC，分辨率为800*600。',
						}
					}
				}
			}
		});
	} else {
		$('#mainForm').bootstrapValidator({
			fields: {
				name: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '项目名称不能为空！'
						},
						stringLength: {
							max: 50,
							message: '项目名称不多于50字！'
						}
					}
				},
				teamName: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '团队名称不能为空！'
						},
						stringLength: {
							max: 20,
							message: '团队名称不多于20字！'
						}
					}
				},
				locationCode: {
					trigger: 'change',
					validators: {
						notEmpty: {
							message: '所在省市不能为空！'
						},
					}
				},
				cityCode: {
					trigger: 'change',
					validators: {
						notEmpty: {
							message: '所在省市不能为空！'
						},
					}
				},
				/* industryCode: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '所属领域不能为空！'
						},
						choice: {
							max: 3,
							message: '所属领域最多选择三个！'
						}
					}
				}, */
				wasBindUniTechnology: {
					trigger: 'change',
					validators: {
						notEmpty: {
							message: '请选择项目是否与高校科技结果相结合！'
						},
					}
				},
				isCreateTogeter: {
					trigger: 'change',
					validators: {
						notEmpty: {
							message: '请选择是否为师生共创！'
						},
					}
				},
				isAchievementOwner: {
					trigger: 'change',
					validators: {
						notEmpty: {
							message: '请选择创始人是否为科技成果的完成人或所有人！'
						},
					}
				},
				wasEquityStructure: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '请选择股权结构！'
						},
					}
				},
				isInvest: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '请选择是否已获投资！'
						},
					}
				},
				synopsis: {
					trigger: 'blur',
					trigger: 'change',
					validators: {
						notEmpty: {
							message: '项目简介不能为空！'
						},
						stringLength: {
							min: 100,
							max: 1000,
							message: '请简介您的项目，字数在100-1000字间，空格也计入字数！'
						}
					}
				},
				founderSynopsis: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '创始人简介不能为空！'
						},
						stringLength: {
							max: 50,
							message: '请填写创始人简介，字数在50字以内，空格也计入字数！'
						}
					}
				},
				progress: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '项目进展必选一项！'
						},
					}
				},
				companyName: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '公司名称不能为空！'
						},
					}
				},
				legalRepresent: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '法人代表不能为空！'
						},
					}
				},
				job: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '担任职务不能为空！'
						},
					}
				},
				isSupport:{
					trigger: 'change',
					validators: {
						notEmpty: {
							message: '请选择获得资金支持情况！'
						},
					}
				},
				registerCapital: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '注册资金不能为空！'
						},
						regexp: {
							regexp: /^((\d+)|(\d+\.\d{0,2}))$/,
							message: '资金只能为数字，请输入正确的金额！',
						}
					}
				},
				registerDate: {
					trigger: 'blur',
					trigger: 'change',
					validators: {
						notEmpty: {
							message: '注册时间不能为空！'
						},
						date: {
							format: 'YYYY-MM-DD',
							message: '请输入正确的日期！格式：2017-01-01。',
						}
					}
				},
				registerProvinceCode: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '注册所在地不能为空！'
						},
					}
				},
				registerAddress: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '注册所在地不能为空！'
						},
					}
				},
				organizationCode: {
					trigger: 'blur',
					validators: {
						notEmpty: {
							message: '统一社会信用代码不能为空！'
						},
					}
				},
				secretStatus: {
					validators: {
						notEmpty: {
							message: '隐私设置至少选择一项！'
						},
					}
				},
				physicalFile: {
					trigger: 'change',
					validators: {
						file: {
							extension: 'pdf,doc,docx',
							maxSize: 20 * 1024 * 1024,
							message: '格式为pdf、word，不超过20M，文件数量限一个！',
						},
					}
				},
				pptFile: {
					trigger: 'change',
					validators: {
						notEmpty: {
							message: '项目ppt不能为空！'
						},
						file: {
							extension: 'ppt,pptx',
							maxSize: 20 * 1024 * 1024,
							message: '格式为ppt、pptx，不超过20M，文件数量限一个！',
						},
					}
				},
				videoFile: {
					trigger: 'change',
					validators: {
						notEmpty: {
							message: '项目视频不能为空！'
						},
						file: {
							extension: 'mp4',
							maxSize: 20 * 1024 * 1024,
							message: '请使用MP4格式的视频，文件大小不超过20M，时长不超过1分钟；生成视频时，视频编码为H.264，音频编码为AAC，分辨率为800*600。',
						}
					}
				}
			}
		});
	}

	industryArr.forEach(function (v, i) {
		$("#industryCode option[value='" + v + "']").prop('selected', true);
		$("#industryCodeBlocks li[data-value='" + v + "']").addClass("selected");
	});

	//省市联动
	$('.locationCode').change(function () {
		var val = $(this).val();
		if (!val) return false;
		var url = 'xxxxx?pppp=' + val;
		$.ajax({
			url: url,
			success: function (data) {
				if (!data) {
					alert('获取失败，请重新获取！');
				}
				var html = '<option value="">请选择市</option>';
				for (var i in data) {
					html += '<option value=' + data[i].code + '>' + data[i].name + '</option>';
				}
				$('.cityCode').empty();
				$('.cityCode').append(html).trigger("click");
				//获取后台给的城市代码
				var cityCode = $("#thcityCode").text();
				//循环判断项目所属城市
				$('.cityCode option').each(function () {
					if ($(this).val() == cityCode) {
						$(this).attr('selected', true);
					}
				});
			}
		});
	});
	$('.locationCode').trigger('change');
	//时间插件
	$(".datetime").datetimepicker({
		format: 'yyyy-mm-dd',
		autoclose: true,
		todayBtn: true,
		language: 'zh-CN',
		minView: "month",
		endDate: new Date(),
	});
	var address = window.location.href.split("?")[1];
	if (address == "add") {
		$("html,body").scrollTop($("#contestPpt").offset().top);
	}
	otherFn();
});