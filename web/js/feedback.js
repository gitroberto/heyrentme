$(function(){
        var ajaxFormSubmit = function () {
            var $form = $(this);

            var options = {
                url: $form.attr("action"),
                type: $form.attr("method"),
                data: $form.serialize()
            };

            $.ajax(options).done(function (data) {    
                var $target = $("#formFeedback");
                var $newHtml = $(data);        
                
                $target.replaceWith($newHtml);
                if (data.indexOf("User_Feedback_Saved") != -1){                    
                    $("#FeedbackConfirmationDiv").show();                   
                }
            });

            return false;
        };
        
        $("#formFeedback").submit(ajaxFormSubmit);
    });