var discountTypePrefix = "DiscountType_";
var discountPercentPrefix = "Percent_";
var discountDurationPrefix = "Duration_";


$(function(){
    $(".placeholderStatusDashboard").each(function(){        
        $(this).attr("placeholder", defaultStatusText);
    });
    $(".btnSaveStatus").click(SaveStatusAndDiscount);        
    
    $(".selectpicker[id^="+discountTypePrefix+"]").change(function(){
        var id = $(this).attr("id").split("_")[1];
        if(id == undefined || id == null){
            return;
        }                
        var selectedVal = $(this).val();
        CreateDurationOptionsAndEnableDiscounts(selectedVal, id);

    });
    
    RestoreActiveDiscounts();
});

function RestoreActiveDiscounts(){    
    $(".selectpicker[id^="+discountTypePrefix+"]").each(function(i,item){
        var id = $(item).attr("id").split("_")[1];
        var discountTypeVal = $("#Hidden"+discountTypePrefix+id).val();
       
        if (discountTypeVal == undefined) {
            discountTypeVal = -1;
        }
       
        if (discountTypeVal == -1) {
            SetValueAndDisable($("#"+discountPercentPrefix+id), $("#Hidden"+discountPercentPrefix+id).val());                        
            SetValueAndDisable($("#"+discountDurationPrefix+id), $("#Hidden"+discountDurationPrefix+id).val());                    
        } else {
            SetValue($(item), discountTypeVal);            
                        
            SetValueAndDisable($("#"+discountPercentPrefix+id), $("#Hidden"+discountPercentPrefix+id).val());                        
            CreateDurationOptions(discountTypeVal, id);
            SetValueAndDisable($("#"+discountDurationPrefix+id), $("#Hidden"+discountDurationPrefix+id).val());                                                        
        }
    });
}

function SetValue($item, val){    
    $item.val(val);    
    $item.selectpicker("refresh");
    $item.selectpicker("val", val);
}

function SetValueAndDisable($item, val){    
    $item.attr("disabled", "disabled");
    SetValue($item, val)
}

function DisbleDiscounts(id){
    var $control = $("#"+discountDurationPrefix+id);    
    $control.html("");
    $control.append(CreateOption(-1, "Dauer"));
    DisableControl($control);
    $control = $("#"+discountPercentPrefix+id);    
    $control.val("-1");
    DisableControl($control);
}

function CreateDurationOptionsAndEnableDiscounts(selectedVal, id){
    if (selectedVal == 1 || selectedVal == 2){
        CreateDurationOptions(selectedVal, id);
        EnableDiscounts(id);    
    } else {
        DisbleDiscounts(id);
    }
}

function CreateDurationOptions(selectedVal, id){
    var maxValue=0;
    var type= "";

    if (selectedVal == 1){
        maxValue = 4;
        type = "week";        
    } else if (selectedVal == 2) {
        maxValue = 24;
        type = "hour";        
    }
        
    var $control = $("#"+discountDurationPrefix+id);        
    $control.html("");
    $control.append(CreateOption(-1, "Dauer"));
    var suffix = "";    
    for(var i = 1; i <= maxValue; i++){        
        if (i > 1){
            suffix = "s";
        }
        $control.append(CreateOption(i, type + suffix));
    }
}


function EnableDiscounts(id){
    $control = $("#"+discountDurationPrefix+id);        
    EnableControl($control);    
    $control = $("#"+discountPercentPrefix+id);        
    EnableControl($control);
}

function DisableControl($control){
    $control.attr("disabled","disabled");    
    $control.selectpicker('refresh');
}

function EnableControl($control){
    $control.removeAttr("disabled");
    $control.selectpicker('refresh');
}

function CreateOption(val, text){
    if (val == -1){
        return $("<option>").val(val).text(text);
    } else {
        return $("<option>").val(val).text(val+" "+text);
    }
}


var defaultStatusText = "Neues zu diesem Angebot.. Z.B. Zur Zeit nicht verfügbar,Aktuell ein wenig günstiger etc.";
function SaveStatusAndDiscount(){
    
    var id = $(this).attr("id").split("_")[1];
    var text = $("#TxtStatus_"+id).val();
    var $msgBox = $("#DivMessage_"+id);
    $msgBox.removeClass();
    $msgBox.html("");
    $msgBox.hide();
    
    var errors = [];
    
    
    if (text == "" || text == defaultStatusText ){
        errors.push("Please insert status text.");                
    }
    
    if (text.length > 255) {
        errors.push("Status can have maximum 255 chars, "+ text.length + " given.");        
    }
    
    var discountTypeValue = $(".selectpicker[id='"+discountTypePrefix+id+"']").val();
    var percentValue = $(".selectpicker[id='"+discountPercentPrefix+id+"']").val();
    var durationValue = $(".selectpicker[id='"+discountDurationPrefix+id+"']").val();
    
    if(discountTypeValue == ""){
        discountTypeValue = -1;
    }
    
    if (discountTypeValue != -1) {                        
        if (percentValue == -1){
            errors.push("Please select discount percent.");
        }                
        if (durationValue == -1 || durationValue == 0){
            errors.push("Please select discount duration.");
        }        
    }
   
    if (errors.length == 0) {
        var dataDict = {};
        if (discountTypeValue == -1 ) {
            dataDict = {
                "id": id,
                "text": text,
                "discountType": -1,
                "percent": -1,
                "duration": -1
            } 
        } else {
            dataDict = {
                "id": id,
                "text": text,
                "discountType": discountTypeValue,
                "percent": percentValue,
                "duration": durationValue
                
            } 
        }

        $.ajax({
            url: url,
            type: 'post',
            data: dataDict,
            success: function(){
                WriteMessage($msgBox,[ "Offer saved correctly." ], false);                
                //DisableControl($("#"+discountTypePrefix+id));
                if (discountTypeValue != -1 && discountTypeValue != 0){
                    RemoveOtherOptions($("#"+discountTypePrefix+id), discountTypeValue)
                } else {
                    location.reload();
                }
                DisableControl($("#"+discountPercentPrefix+id));
                DisableControl($("#"+discountDurationPrefix+id));                
            },
            error: function(data){
                
                if (data.responseJSON != undefined && data.responseJSON.length > 0) {
                    WriteMessage($msgBox, data.responseJSON, true);
                } else {
                    WriteMessage($msgBox,[ "Some error occured." ], true);
                }                
            }        
        });
    } else {
        WriteMessage($msgBox, errors, true);
    }
    
}

function RemoveOtherOptions($control, optionValue){
    $control.find("option").each(function(i, item){
        var currentOptionValue = $(item).val();
        
        if(currentOptionValue != optionValue && currentOptionValue != -1 && currentOptionValue != 0) {
            $(item).remove();
            
        }
    });
    $control.selectpicker("refresh");
}

function WriteMessage(container,messages, error){
    if (error){
        $(container).addClass("error");
    } else {
        $(container).addClass("correct");
    }
    for(var i=0; i < messages.length; i++){
        $(container).append($("<li>").text(messages[i]));
    }    
    $(container).show();
}