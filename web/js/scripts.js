$(document).ready(function () {

    $(document).ready(function () {

        var mySlidebars = new $.slidebars();
        $('#mobile-icon').on('click', function () {
            mySlidebars.slidebars.open('left');
        });

        $('#share-mobile-button').on('click', function () {
            mySlidebars.slidebars.open('right');
        });
    });



    $(document).on('show.bs.modal', '.modal', function () {
        var zIndex = 1040 + (10 * $('.modal:visible').length);
        $(this).css('z-index', zIndex);
        setTimeout(function() {
            $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
        }, 0);
    });





    // Owl Carusel
    $("#owl-demo, #owl-demo1").owlCarousel({
        navigation: true,
        slideSpeed: 300,
        paginationSpeed: 400,
        singleItem: true,
        navigationText: ["", ""],
        rewindNav: true,
        scrollPerPage: false,
    });

    // Sustom select
    $('.selectpicker').selectpicker({
        style: 'btn-info',
        size: 4
    });

    $('.selectpicker').selectpicker('val', 'Mustard');

    $('.selectpickerspc').selectpicker({
        style: 'btn-info kaup-show',
        size: 4
    });

    // Get val of span of select menu, and go to location.
    $('.link-selected').on('click', function (event) {
        var href = $(this).attr('href');
        window.location.href = href;
    });


    //set height of map div like his sibling
    $('#googleMap').css('min-height', $('#map-sibling p').innerHeight());

    // Assign accordion
    $('.panel-heading').click(function () {
        $(this).find('span.toggle-icon').toggleClass('glyphicon glyphicon-triangle-top glyphicon glyphicon-triangle-bottom');
        $(this).toggleClass('accordion-yellow')
    });



    $('#panel-heading').on('click', function () {
        $('.collapse2').collapse('toggle');
    });



    $('#panel-heading1').on('click', function () {
        $('.collapse1').collapse('toggle');
    });


    $('#panel-heading3').on('click', function () {
        $('.collapse3').collapse('toggle');
    });

    $('#panel-heading4').on('click', function () {
        $('.collapse4').collapse('toggle');
    });

    $('#panel-heading5').on('click', function () {
        $('.collapse5').collapse('toggle');
    });


    // Kaup controls
    $('.spc').on('click', function () {
      if($(this).hasClass("open")) {
        $('#kaup-hidden').hide();
      }
      else {
        $('#kaup-hidden').show();
      }
    });
    $('#kategorie-select').on('click', function () {
      if($(this).hasClass("open")) {
        $('#kaup-hidden').hide();
      }
      else {
        $('#kaup-hidden').show();
      }
      $(this).toggleClass("open");
    });
    $('#close-haup').on('click', function () {
        $('#kaup-hidden').hide();
    });
    $('input,textarea').focus(function(){
       $(this).data('placeholder',$(this).attr('placeholder'))
              .attr('placeholder','');
    }).blur(function(){
       $(this).attr('placeholder',$(this).data('placeholder'));
    });
    $('[data-toggle="tooltip"]').tooltip();
    $(document).mouseup(function (e) {
        var container = $("#kaup-hidden");
        if (!container.is(e.target) // if the target of the click isn't the container...
            && container.has(e.target).length === 0) // ... nor a descendant of the container
        {
            container.hide();
        }
        if(!($(".dropdown-menu-span").is(e.target))
            && $(".dropdown-menu-span").has(e.target).length === 0)
        {
            $(".dropdown-menu-span").removeClass("active");
            $("#open-menu").removeClass("active");
            $(".dropdown-menu-span").parent().find("p").removeClass("active");
        }
    });


    // Footer animation controls
    var footerButton = $('#footer-toggle');
    var footerContent = $('#full-footer');

    footerButton.on('click', function (e) {
        $(this).html('+');
        if (($('#full-footer')).is(":hidden")) {
            $('#full-footer').fadeIn(10);
            $(this).html('-');
        } else {
            $('#full-footer').fadeOut(10);
        }
        e.stopPropagation();
    });

    $(document).find('body').children().not('#footer').on('click', function () {
        footerButton.html('+');
        if (($(footerContent)).is(":visible")) {
            $(footerContent).fadeOut(10);
        }
    });
    function isNumber(n) {
      return !isNaN(parseFloat(n)) && isFinite(n);
    }
    $("#preis_pro_tag").on('change keydown keypress keyup mousedown click mouseup', function() {
      var preis = $("#preis_pro_tag").val();
      if(isNumber(preis)) {
        var days = parseInt($("#range_30").val());
        var sumf = days * preis;

        $(".price").html(sumf + " €");
      }
    });
    $("#open-menu").click(function(e) {
      e.preventDefault();
      $(".dropdown-menu-span").toggleClass("active");
      $("#open-menu").toggleClass("active");
        $(".dropdown-menu-span").parent().find("p").toggleClass("active");
    });
    $("body").on('change keydown keypress keyup mousedown click mouseup', "#range_30", function() {
      var preis = $("#preis_pro_tag").val();
      if(isNumber(preis)) {
        var days = parseInt($("#range_30").val());
        var sumf = days * preis;

        $(".price").html(sumf + " €");
      }
    });
});

$(window).on("scroll touchmove", function () {
    //$('#heading-fixed').toggleClass('tiny', $(document).scrollTop() > 0);
    //$('#heading-fixed .button-black').toggleClass('tiny', $(document).scrollTop() > 0);
    //$('#heading-fixed .logo').toggleClass('small-logo', $(document).scrollTop() > 0);
    $('#footer').toggleClass('footer-on', $(document).scrollTop() > 0);
})
