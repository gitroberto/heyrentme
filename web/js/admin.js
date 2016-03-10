
var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-={}[]|?><';
var digits = '0123456789';

function generate(array, length) {
    var l = array.length;
    var pwd = '';
    for (var i = 0; i < length; i++) 
        pwd += array[Math.round(Math.random() * (l - 1), 0)];
    return pwd;
}

$(function() {
    $("#pwd-safe").click(function(e) {
        e.preventDefault();
        $("#form_password").val(generate(chars, 12));
    });
    $("#pwd-temp").click(function(e) {
        e.preventDefault();
        $("#form_password").val('temp_' + generate(digits, 5));
    });
});
