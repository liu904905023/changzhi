/**
 * 全选调用方法，例如：selectAllFn('clickpubish','textmain','td-select'); 
 * 在页面重置的时候，例如搜索、tab页面切换时，需要重置全选，：$('#clickpubish').prop('checked',false);
 * @param  {String} selectAllId   全选checkbox的id
 * @param  {String} parentCName   列表中checkbox的父级元素的classname
 * @param  {String} checkboxCName 列表中checkbox的classname
 * @return {Jquery} 返回全选checkbox的jquery对象
 * hh 20170824              
 */
function selectAllFn(selectAllId,parentCName,checkboxCName){
    var $all = $("#"+selectAllId),
        $parent = $("."+parentCName);
    //全选
    $all.click(function() {
        $("."+parentCName).find("."+checkboxCName).prop('checked',$(this).prop('checked'));
        $("."+parentCName).find("."+checkboxCName).eq(0).trigger("change");
        var allNmum = $parent.find("."+checkboxCName).length;
        if (allNmum != 0) {
            var $all = $("#"+selectAllId);
            if ( $parent.find("."+checkboxCName+":checked").length == allNmum) {
                $all.prop('checked',true);
            } else {
                $all.prop('checked',false);
            }
        }
    });
    //单个选中时，判断是否全选勾选
    $parent.delegate("."+checkboxCName ,"click",function(){
        if ($parent.find("."+checkboxCName+":checked").length 
            == $parent.find("."+checkboxCName).length) { 
            $all.prop('checked',true);
        } else {
            $all.prop('checked',false);
        }
    })
    return $all;
};


$(function(){
	// 导航栏相关
    $(".drop-hover").mouseover(function(){
      $(this).addClass("dropup");
      $(".drop-menu").removeClass("hidden");
    }).mouseout(function(){
      $(".drop-menu").addClass("hidden");
      $(this).removeClass("dropup");
    });
    
    $(".drop-menu").mouseover(function(){
      $(".drop-hover").addClass("dropup");
      $(".drop-menu").removeClass("hidden");
    }).mouseout(function(){
      $(".drop-menu a").mouseover(function(){
        $(this).addClass("text-primary");
      });
      $(".drop-menu").addClass("hidden");
      $(".drop-hover").removeClass("dropup");
    });
});