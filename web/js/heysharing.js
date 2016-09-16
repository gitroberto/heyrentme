$(function(){
        var ajaxFormRecoverySubmit = function () {
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
        
        $("#formRecovery").submit(ajaxFormRecoverySubmit);
        
        var ajaxFormResetPasswordSubmit = function () {
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
        
        $("#resetPassword").submit(ajaxFormResetPasswordSubmit);

        var ajaxFormRegisterSubmit = function () {
            //$("#facebookValidation").hide();
            var $form = $(this);

            var options = {
                url: $form.attr("action"),
                type: $form.attr("method"),
                data: $form.serialize()
            };

            $.ajax(options).done(function (data) {    
                $("#reg-modal").modal('hide');
                $("#reg-sent").modal();
            });

            return false;
        };
        
        $("#formRegister").submit(ajaxFormRegisterSubmit);
        
        $("#facebookValidation").hide();
        $("#facebookValidationAge").hide();
        
        $("input[type='submit']").click(function(){
            $("#facebookValidation").hide();
            $("#facebookValidationAge").hide();
        });
        
        $("#facebookRegistration").click(function(e){
            $("#facebookValidation").hide();
            $("#facebookValidationAge").hide();
            if (!$("#fos_user_registration_form_accept").prop('checked')){
                $("#facebookValidation").show();
                e.preventDefault();           
            }   
            
            if (!$("#fos_user_registration_form_ageCheck").prop('checked')){
                $("#facebookValidationAge").show();
                e.preventDefault();           
            }   
            
        });
        var ajaxFormLoginSubmit = function () {
            var $form = $(this);
            $("#DivLoginMessage").hide();
            var options = {
                url: $form.attr("action"),
                type: $form.attr("method"),
                data: $form.serialize()
            };

            $.ajax(options).done(function (data) {
                var $target = $("#formLogin");
                                
                if (data.indexOf("User_Is_Logged") == -1){
                    if (data.indexOf("User_Is_Not_Logged") == -1){
                        var $newHtml = $(data);                
                        $target.replaceWith($newHtml);
                    } else {
                        var message = data.split(";")[1];
                        $("#DivLoginMessage").html(message);
                        $("#DivLoginMessage").show();
                    }                    
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
        $("input[type='submit']").click(function(){
            $("#DivLoginMessage").show();
        })
        
        $("#formLogin").submit(ajaxFormLoginSubmit);

        var ajaxFormFeedbackSubmit = function () {
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
        
        $("#formFeedback").submit(ajaxFormFeedbackSubmit);

        var ajaxFormReportOfferSubmit = function () {
            var $form = $(this);

            var options = {
                url: $form.attr("action"),
                type: $form.attr("method"),
                data: $form.serialize()
            };

            $.ajax(options).done(function (data) {    
                var $target = $("#formReportOffer");
                var $newHtml = $(data);        
                
                $target.replaceWith($newHtml);
                if (data.indexOf("Report_Offer_Saved") != -1){                    
                    $("#ReportOfferConfirmationDiv").show();                   
                }
            });

            return false;
            
        };

        $("#formReportOffer").submit(ajaxFormReportOfferSubmit);
});