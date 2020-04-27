var date = new Date(),
	index1 = parseInt($("#structuresIndex").text()),
	index2 = parseInt($("#investsIndex").text());
index1 = index1 == '0' ? 0 : (index1 - 1);
index2 = index2 == '0' ? 0 : (index2 - 1);
var structureItem = '<div class="structure-item form-item" style="display:none;"><div class="hr"></div><div class="invest-line"><span class="invest-key">股东身份：</span><select class="form-control type-input" name="shareholderType[]" data-small="type-err" data-emperr="请选择股东身份！"><option value="" disabled selected>请选择股东身份</option><option value="教师">教师</option><option value="学生">学生</option><option value="其他">其他</option></select><small class="help-block type-err hidden">请选择股东身份！</small></div><div class="invest-line"><span class="invest-key">股东：</span><input type="text" maxlength="64" class="form-control name-input" name="shareholder[]" data-small="name-err" data-emperr="股东姓名不能为空！" placeholder="请填写人名或机构名"><small class="help-block ml5 name-err hidden" >股东姓名不能为空！</small></div><div class="invest-line money-line"><span class="invest-key">持股比例：</span><input type="text" class="form-control percent-input" maxlength="6" name="shareholderRatio[]" data-small="percent-err" data-emperr="持股比例不能为空！" data-patenterr="0-100，保留小数点后两位。"  data-patent="^((\\d{1,2})|(\\d{1,2}\\.\\d{0,2})|(100|100\\.0|100\\.00))$" data-patent="^(\\d{1,2}\\.\\d{2})$|(100.00)$" data-trim="^\\d{1,}(\\.(\\d{1,})?)?$" placeholder="小数点后两位"><small class="help-block ml5 percent-err hidden" >持股比例不能为空！</small><span class="percent">%</span></div><span class="del oprate">删除</span></div>';


var investItem = '<div class="invest-item form-item" style="display:none;"><div class="hr"></div><div class="invest-line"><span class="invest-key">投资机构名称：</span><input type="text" maxlength="64" class="form-control  investname-input" name="orgName[]" data-small="investname-err" data-emperr="投资机构名称不能为空！" placeholder="请填写"><small class="help-block ml5 investname-err hidden" >投资机构名称不能为空</small> </div><div class="invest-line"><span class="invest-key">融资阶段：</span><select class="form-control investstage-input" name="investStageCode[]" data-small="investstage-err" data-emperr="请选择项目处的融资阶段！" ><option value="">请选择项目处的融资阶段</option><option value="种子轮">种子轮</option><option value="天使轮">天使轮</option><option value="A轮">A轮</option><option value="B轮">B轮</option><option value="C轮">C轮</option><option value="D轮">D轮</option><option value="E轮及以后">E轮及以后</option></select><small class="help-block ml5 investstage-err hidden" >请选择项目处的融资阶段！</small></div><div class="invest-line money-line"><span class="invest-key">投资金额：</span><input type="text" class="form-control  investmoney-input" name="investMoney[]" maxlength="10" data-small="investmoney-err" data-emperr="投资金额不能为空！" data-patenterr="请填写数字，最多保留小数点后两位。" data-patent="^((\\d+)|(\\d+\\.\\d{0,2}))$" data-trim="^\\d+(\\.(\\d+)?)?$" placeholder="请填写"><span class="addon">万元</span><small class="help-block ml5 investmoney-err hidden" >投资金额不能为空！</small></div><div class="invest-line"><span class="invest-key">获得时间：</span><input type="text" class="form-control  investtime-input" name="gainDate[]" placeholder="请填写" readonly data-small="investtime-err" data-emperr="获得时间不能为空！"><small class="help-block ml5 investtime-err hidden" >获得时间不能为空！</small></div><span class="del oprate">删除</span></div>';

function setDate() {
	$(".investtime-input").datetimepicker({
		format: 'yyyy-mm-dd',
		autoclose: true,
		language: 'zh-CN',
		minView: "month",
		endDate: date
	});
}

function setStrName() {
	$(".structure-item").each(function (index, ele) {
		$(this).find(".form-control").each(function () {
			var $this = $(this);
			var newName = $this.attr("name").replace(/\[\d+\]/, '[' + index + ']');
			$this.attr("name", newName);
		});
	});
}

function setInvName() {
	$(".invest-item").each(function (index, ele) {
		$(this).find(".form-control").each(function () {
			var $this = $(this);
			var newName = $this.attr("name").replace(/\[\d+\]/, '[' + index + ']');
			$this.attr("name", newName);
		});
	});
}

function initFn() {
	//文本框输入提示

	$(".textarea-box").each(function () {
		var $this = $(this);
		var $textarea = $this.find(".intro-text");
		$this.find(".count").text($textarea.val().length);
		$textarea.on("keyup paste click", function () {
			$this.find(".count").text($textarea.val().length);
			if ($textarea.val().length > 0) {
				$this.find(".tip").removeClass("hidden");
			} else {
				$this.find(".tip").addClass("hidden");
			}
		}).on("blur", function () {
			$this.find(".tip").addClass("hidden");
		});
	});
	//省份select提示
	$(".select-box").each(function () {
		var $this = $(this);
		var $select = $this.find(".tip-control");
		var $tip = $this.find(".tip");
		$select.on("focus", function () {
			if ($select.hasClass("input-control")) {
				$select.on("keyup paste click", function () {
					if ($select.val() != "") {
						$tip.removeClass("hidden");
					} else {
						$tip.addClass("hidden");
					}
				});
			} else {
				$tip.removeClass("hidden");
			}

		}).on("blur", function () {
			$tip.addClass("hidden");
		});
	});

	//股权添加

	$(".structure-list .add").on("click", function () {
		index1 += 1;
		var $structureItem = $(structureItem);
		$structureItem.find(".form-control").each(function () {
			var $this = $(this);
			var newName = $this.attr("name").replace(/\d/, index1);
			$this.attr("name", newName);
		});
		if ($('input[name="isCreateTogeter"]:checked').val() != '1') {
			$structureItem.find(".type-input").find("option[value='1']").wrap("<span style='display:none'></span>");
		}
		$(".structure-list").append($structureItem);
		$structureItem.slideDown(300);
	});

	$('.structure-list').delegate('.del', 'click', function () {
		if ($(".structure-item").length == 1) {
			$("#resultTip").modal("show");
			$("#resultMessage").text("股权结构不能为空！");
			$("#resultTitle").text("删除失败");
			$("#resultIcon").attr("class", "project-modal-fail");
		} else {
			index1 -= 1;
			var $thisParent = $(this).parents(".structure-item");
			$thisParent.slideUp(200, function () {
				$thisParent.remove();
				setStrName();
			});
		}
	});

	//融资添加

	$('.invest-list').delegate('.del', 'click', function () {
		if ($(".invest-item").length == 1) {
			$("#resultTip").modal("show");
			$("#resultMessage").text("已获融资不能为空！");
			$("#resultTitle").text("删除失败");
			$("#resultIcon").attr("class", "project-modal-fail");
		} else {
			index2 -= 1;
			var $thisParent = $(this).parents(".invest-item");
			$thisParent.slideUp(150, function () {
				$thisParent.remove();
				setInvName();
			});
		}
	}).find(".add").on("click", function () {
		index2 += 1;
		var $investItem = $(investItem);
		$investItem.find(".form-control").each(function () {
			var $this = $(this);
			var newName = $this.attr("name").replace(/\d/, index1);
			$this.attr("name", newName);
		});
		$(".invest-list").append($investItem);
		$investItem.slideDown(150);
		$(".invest-list").append($investItem);
		setDate();
	});

	$('.form-list').delegate('input.form-control:not(".investtime-input")', 'blur', function () {
		var $this = $(this),
			small = '.' + $this.attr("data-small"),
			emperr = $this.attr("data-emperr"),
			patent = $this.attr("data-patent"),
			patenterr = $this.attr("data-patenterr"),
			val = $this.val();
		if (val == "") {
			$this.addClass("has-error").nextAll(small).removeClass("hidden").text(emperr);
		} else {

			if (patent) {
				var reg = new RegExp(patent);
				if (!reg.test(val)) {
					$this.addClass("has-error").nextAll(small).removeClass("hidden").text(patenterr);
				} else {
					if ($this.hasClass("percent-input")) {
						var str1 = val.split(".")[0];
						var str2 = val.split(".")[1];
						if (typeof (str2) === 'undefined' || str2 == '') {
							str2 = '00';
						} else {
							str2 = str2.length == 1 ? (str2 + '0') : str2;
						}
						$this.val(str1 + '.' + str2);
						if(val>100){
							$this.val('100.00')
						}
					}
					$this.removeClass("has-error").nextAll(small).addClass("hidden");
				}
			} else {
				$this.removeClass("has-error").nextAll(small).addClass("hidden");
			};
		}
	}).delegate('.investtime-input,select', 'change', function () {
		var $this = $(this);
		var small = '.' + $this.attr("data-small");
		var emperr = $this.attr("data-emperr");
		var val = $this.val();
		if (val == "") {
			$this.addClass("has-error").nextAll(small).removeClass("hidden").text(emperr);
		} else {
			$this.removeClass("has-error").nextAll(small).addClass("hidden");
		}
	});

	//点击input、错误提示消失
	$('*[name]').each(function () {
		$(this).focus(function () {
			$(this).parents(".form-group").find('.back-err').hide();
		});
	});

	//师生共创
	$('input[name="isCreateTogeter"]').change(function () {
		if ($(this).val() == '1') {
			var optionP = $(".type-input").find("span");
			optionP.each(function(){
				var $this = $(this)
				$this.children().clone().replaceAll($this);
			});
		} else {
			$(".type-input>option[value='1']").removeAttr("selected").wrap("<span style='display:none'></span>");
		}
	});
	
	$('.structure-list').delegate('.type-input', 'change', function () {
		if ($(this).val() == '1') {
			$('input[name="isCreateTogeter"][value="0"]').attr("disabled","disabled").parents("label").attr("title","股权结构不符合要求");
		} else {
			setDisabled()
		}
	});
	if ($('input[name="isCreateTogeter"]:checked').val() != '1') {
		$(".type-input").find("option[value='1']").wrap("<span style='display:none'></span>");
	}

	var _con = $("#ex-drop");
  $(document).on("click", function (event) {
    if($("#explain").is(event.target) || $(event.target).parents(".structure-list").length>0){
      _con.show();
    }else{
      if(!_con.is(event.target) && _con.has(event.target).length === 0){
        if(_con.is(':visible')){
          _con.hide();
        }
      }
    }
  });



	//项目进展的选择
	$('input[name="progress"]').change(function () {
		var $this = $(this);
		if ($this.val() == '1') {
			$('#company').show();
			$('#phy-txt').show();
			setComForm('1');
			/* $('#company').find(".form-group:not(#invest):not(#structure)").removeClass("has-error").find(".help-block").hide();
			$('#invest,#structure').removeClass("has-error").find(".help-block").addClass("hidden"); */
		} else if($this.val() == '2') {
			$('#company').show();
			$('#phy-txt').show();
			setComForm('2');
			/* $('#company').find(".form-group:not(#invest):not(#structure)").removeClass("has-error").find(".help-block").hide();
			$('#invest,#structure').find(".has-error").removeClass("has-error").end().find(".help-block").addClass("hidden"); */
		}else{
			$('#company').hide();
			$('#phy-txt').hide();
			setComForm('0');
		}
	});
	if ($('input[name="progress"]:checked').val() == '1') {
		$('#company').show();
		$('#phy-txt').show();
		setComForm('1');
	} else if ($('input[name="progress"]:checked').val() == '2') {
		$('#company').show();
		$('#phy-txt').show();
		setComForm('2');
	}else{
		$('#company').hide();
		$('#phy-txt').hide();
		setComForm('0');
	}


	//股权结构显示隐藏效果
	$('input[name="wasBindUniTechnology"]').change(function () {
		if ($(this).val() == '1') {
			$('#technologyTip').show();
		} else {
			$('#technologyTip').hide();
		}
	});
	if ($('input[name="wasBindUniTechnology"]:checked').val() == '1') {
		$('#technologyTip').show();
	} else {
		$('#technologyTip').hide();
	}

	//是否已获投资

	$('input[name="isInvest"]').change(function () {
		if ($(this).val() == '1') {
			$('#invest').show();
		} else {
			$('#invest').hide();
		}
	});
	if ($('input[name="isInvest"]:checked').val() == '1') {
		$('#invest').show();
	} else {
		$('#invest').hide();
	}

	//隐私设置
	$('input[name="secretStatus"]').change(function () {
		var text = '向投资人展示项目的项目概述、团队成员、融资情况、专利情况、工商注册信息';
		if ($(this).val() === '1') {
			text = '只展示项目概述模块';
		}
		$("#secretTip").text(text);
	});
	//公司证件提示
	$("#registerProvinceCode").on("change", function () {
		var thisVal = $(this).val();
		if (thisVal == '71' || thisVal == '81' || thisVal == '82' || thisVal == '90') {
			$("#orgCode").text("有效证件号码");
			$("#orgHolder").attr("placeholder", $('input[name="progress"]:checked').val() == '2'?"请填写社会组织有效证件号码":"请填写公司有效证件号码");
		} else {
			$("#orgCode").text("统一社会信用代码");
			$("#orgHolder").attr("placeholder", "统一社会信用代码或组织机构代码");
		}
	});

	//法人代表类型
	$("#legalRepresentType").on("change",function(){
		if($(this).val()=='教师'){
			$("#legalRepresent").removeClass("color-readonly").removeAttr("readonly");
		}else{
			$("#legalRepresent").val($("#userName").val()).trigger("blur").addClass("color-readonly").attr("readonly","readonly");
		}
	});
	if($("#legalRepresentType").val()=='教师'){
		$("#legalRepresent").removeClass("color-readonly").removeAttr("readonly");
	}

	
}

function setDisabled(){
	var flag = true;
	$(".type-input").each(function(index){
		if($(this).find("option:selected:visible").val()=='1'){
			flag = false;
			$('input[name="isCreateTogeter"][value="0"]').attr("disabled","disabled").parents("label").attr("title","股权结构不符合要求");
			return false;
		}
	});
	if(flag){
		$('input[name="isCreateTogeter"][value="0"]').removeAttr("disabled").parents("label").removeAttr("title");
	}
}

function setComForm(flag){
	var thisVal = $("#registerProvinceCode").val();
	if(flag == '2'){
		$("#comT").text("社会组织名称").parents(".form-group").find(".help-block").text("社会组织名称不能为空！");
		$("#companyName").attr("placeholder","请填写民办非企业单位登记证书上的组织名称");
		$(".com-only").addClass("hidden");
		$(".org-only").removeClass("hidden");
		$("#nameT").text("法人代表");
		$('#legalRepresent').attr("placeholder","请填写民办非企业单位登记证书上的法定代表人");
		$('#job').attr("placeholder","请填写您在该组织中担任的最高职务");
		if (thisVal == '71' || thisVal == '81' || thisVal == '82' || thisVal == '90') {
			$("#orgHolder").attr("placeholder", "请填写社会组织有效证件号码");
		} else {
			$("#orgHolder").attr("placeholder", "请填写");
		}
		$("#legalRepresent").removeClass("color-readonly").removeAttr("readonly");
		$('input[name="isCreateTogeter"][value="0"]').removeAttr("disabled").parents("label").removeAttr("title");
	}else if(flag == '1'){
		$("#comT").text("公司名称").parents(".form-group").find(".help-block").text("公司名称不能为空！");
		$("#companyName").attr("placeholder","请填写营业执照上的公司名称");
		$(".com-only").removeClass("hidden");
		$(".org-only").addClass("hidden");
		$("#nameT").text("法人姓名");
		$('#legalRepresent').attr("placeholder","请填写");
		$('#job').attr("placeholder","请填写您在该公司中担任的最高职务");
		if (thisVal == '71' || thisVal == '81' || thisVal == '82' || thisVal == '90') {
			$("#orgHolder").attr("placeholder", "请填写公司有效证件号码");
		} else {
			$("#orgHolder").attr("placeholder", "统一社会信用代码或组织机构代码");
		}

		if($("#legalRepresentType").val()=='1'){
			$("#legalRepresent").removeClass("color-readonly").removeAttr("readonly");
		}else{
			$("#legalRepresent").val($("#userName").val()).trigger("blur").addClass("color-readonly").attr("readonly","readonly");
		}
		setDisabled()
	}else{
		$('input[name="isCreateTogeter"][value="0"]').removeAttr("disabled").parents("label").removeAttr("title");
	}
}


$(function () {
	//setDisabled()
	setDate();
	initFn();

	$(".percent-input").each(function () {
		var $this = $(this),
			val = $this.val(),
			str1 = val.split(".")[0],
			str2 = val.split(".")[1];
		if (typeof (str2) === 'undefined') {
			str2 = '00';
		} else {
			if (str2.length == 1) {
				str2 = str2 + '0';
				$this.val(str1 + '.' + str2);
			}

		}
	});
	if($("#topError").length>0){
		$("a[href=#topError]").trigger("click");
		window.location.hash="#topError";
	}
});