window.onload = function () {

};
function dot(id,index = 0) {
    index = parseInt(index);
    if (index > 0 && index <=4){
        for (var i=0;i<index;i++){
            url = 'url(img/step'+(i+1)+'.png) center no-repeat';
            $('#'+id+' li').eq(i).find('.dot').css('background',url);
        }
    }
}