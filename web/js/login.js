
$(function(){
        var ajaxFormSubmit = function () {
            var $form = $(this);

            var options = {
                url: $form.attr("action"),
                type: $form.attr("method"),
                data: $form.serialize()
            };

            $.ajax(options).done(function (data) {
                var $target = $("#formLogin");
                                
                if (data.indexOf("User_Is_Logged") == -1){
                    var $newHtml = $(data);                
                    $target.replaceWith($newHtml);
                } else {              
                    var url = data.split(";")[1];
                    if (url != undefined && url != ""){
                        location.href = url;
                    } else {
                        location.reload();
                        //location.href = "provider/profil";
                    }
                }
                
            });

            return false;
        };
        
        $("#formLogin").submit(ajaxFormSubmit);
    });