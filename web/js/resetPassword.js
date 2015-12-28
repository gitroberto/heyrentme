
$(function(){
        var ajaxFormSubmit = function () {
            var $form = $(this);

            var options = {
                url: $form.attr("action"),
                type: $form.attr("method"),
                data: $form.serialize()
            };

            $.ajax(options).done(function (data) {
                var $target = $("#resetPassword");
                
                
                if (data.indexOf("Password_Changed") == -1){                                      
                    var $newHtml = $(data);
                    $target.replaceWith($newHtml);
                } else {              
                    //location.reload();
                    var url = data.split(";")[1];
                    location.href = url;
                    
                }
                
            });

            return false;
        };
        
        $("#resetPassword").submit(ajaxFormSubmit);
    });