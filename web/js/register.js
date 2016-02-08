$(function(){
        var ajaxFormSubmit = function () {
            //$("#facebookValidation").hide();
            var $form = $(this);

            var options = {
                url: $form.attr("action"),
                type: $form.attr("method"),
                data: $form.serialize()
            };

            $.ajax(options).done(function (data) {    
                var $target = $("#formRegister");
                var $newHtml = $(data);        
                $target.replaceWith($newHtml);                                
                                
                if (data.indexOf("User_Is_Registered") != -1){                                                      
                   $("#confirmationDiv").show();                   
                }
            });

            return false;
        };
        
        $("#formRegister").submit(ajaxFormSubmit);
        
        $("#facebookValidation").hide();
        
        $("input[type='submit']").click(function(){
            $("#facebookValidation").hide();
        });
        
        $("#facebookRegistration").click(function(e){
            $("#facebookValidation").hide();
            if (!$("#fos_user_registration_form_accept").prop('checked')){
                $("#facebookValidation").show();
                e.preventDefault();           
            }   
        });
        
    });