$(function(){
        var ajaxFormSubmit = function () {
            var $form = $(this);

            var options = {
                url: $form.attr("action"),
                type: $form.attr("method"),
                data: $form.serialize()
            };

            $.ajax(options).done(function (data) {
                var $target = $("#formRecovery");
                var $newHtml = $(data);                
                $target.replaceWith($newHtml);
                //if ($newHtml.text().indexOf("User_Is_Logged") != -1){                    
                //    $target.replaceWith($newHtml);
                //} else {
                //    location.reload();
                //}
                
            });

            return false;
        };
        
        $("#formRecovery").submit(ajaxFormSubmit);
    });