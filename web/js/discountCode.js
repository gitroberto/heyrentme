$(function(){
       
    $("#DivGenerate").click(CheckNumberAndGenerateCode);
    
    $("#selectAll").click(function(){ SelectDeselectAll(true); });
    $("#deselectAll").click(function(){ SelectDeselectAll(false); });
    $("#revertSelection").click(RevertSelection);
    
    $("#DivCancel").click(CancelCodes);
    
});

function CancelCodes(){
    var url = $(this).attr("data-target");
    
    var ids = "";
    
    $(".CheckboxItems").each(function(){
        if(this.checked){
            if(ids.length > 0){
                ids += ",";
            }
            ids+= $(this).val();
        }
    })
    
    $("#CancelDiscountMessage").text("");
    
    dataDict = {
        "ids": ids
    } 
    
    $.ajax({
        url: url,
        type: 'post',
        data: dataDict,
        success: function(){
           $('#jqgrid').trigger( 'reloadGrid' );   
        },
        error: function(data){
            $("#CancelDiscountMessage").text("Some problem occurred");
        }        
    });
    
}

function SelectDeselectAll(selection){
    $(".CheckboxItems").each(function(){
        this.checked = selection;
    });
}

function RevertSelection(){
    $(".CheckboxItems").each(function(){
        this.checked = !this.checked;        
    });
}

function CheckNumberAndGenerateCode(){

    var number = $("#NumberOfCodes").val();
    $("#ValidationMessage").text("");
    if (number == undefined || number == null || number == ""){
        $("#ValidationMessage").text("Please enter number of new code.");
        return;
    }    
 
    if(parseInt(number) == NaN){
        $("#ValidationMessage").text("Please enter valid number of new codes.");
        return;
    }
   
    location.href = $("#DivGenerate").attr("data-target")+number;
}
