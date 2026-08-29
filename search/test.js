$(function(){
  var sysurl ="/search";
  //$.get(sysurl+"/api/content",{category:2},function(data){
  $.get(sysurl+"/api/content",function(data){
    var str = '<div class="owl-carousel owl-loaded owl-drag" data-owl-nav="false" data-owl-margin="30" data-owl-xs="1" data-owl-sm="1" data-owl-md="1" data-owl-lg="2" data-owl-xl="2" data-owl-autoplay="true">';
    var json = JSON.parse(data)
    for(var i=0;i<json.length;i++){
      var date = new Date(json[i].created.replace(/-/g,"/"));
      var dateStr = (date.getFullYear()*1)+"."+zeroPadding((date.getMonth()*1)+1,2)+"."+zeroPadding((date.getDate()*1),2);
      str+= '<div class="infomationItems">';
        str+= '<div class="hoverbox-9">';
          str+= '<a href="'+sysurl+'/content/detail?id='+json[i].id+'">';
          if(json[i].thumb == "/img/" || json[i].thumb == ""){
            str+= '<img src="'+sysurl+'/image/noImage.png" alt="">';
          }else{
            str+= '<img src="'+sysurl+json[i].thumb+'" alt="">';
          }
          str+= '</a>';
          str+= '<span class="categoryName">'+json[i].cName+'</span>';
        str+= '</div>';
        str+= '<div class="margin-top-20">';
          str+= '<span>'+dateStr+'</span>';
          str+= '<h5 class="font-weight-normal margin-0">';
          str+= '<a href="'+sysurl+'/content/detail?id='+json[i].id+'">'+json[i].name+'</a>';
          str+= '</h5>';
        str+= '</div>';
      str+= '</div>';
    }
    str+= '</div>';
    $("#infomation").html(str);
    $("#infomation .owl-carousel").owlCarousel();

    $(".infomationItems").css("position","relative");
    $(".categoryName").css("color","#fff").css("position","absolute").css("left","5px").css("top","5px").css("background","#000").css("font-size","16px").css("z-index","100").css("padding","0.5em");
  });
});
function zeroPadding(NUM, LEN){
   return ( Array(LEN).join('0') + NUM*1 ).slice( -LEN );
}
