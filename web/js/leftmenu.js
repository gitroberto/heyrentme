$(function(){
    $(".leftmenu").each(function(){       
         if($(this).attr("id")== page){
             $(this).addClass("active-link");
         }           
    });
 });