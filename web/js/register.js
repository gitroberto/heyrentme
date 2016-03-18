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
                $("#reg-modal").modal('hide');
                $("#reg-sent").modal();
            });

            return false;
        };
        
        $("#formRegister").submit(ajaxFormSubmit);
        
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
        
    });