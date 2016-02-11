$(function(){
        var ajaxFormSubmit = function () {
            var $form = $(this);

            var options = {
                url: $form.attr("action"),
                type: $form.attr("method"),
                data: $form.serialize()
            };

            $.ajax(options).done(function (data) {    
                var $target = $("#formReportOffert");
                var $newHtml = $(data);        
                
                $target.replaceWith($newHtml);
                if (data.indexOf("Report_Offert_Saved") != -1){                    
                    $("#ReportOffertConfirmationDiv").show();                   
                }
            });

            return false;
        };
        
        $("#formReportOffert").submit(ajaxFormSubmit);
    });