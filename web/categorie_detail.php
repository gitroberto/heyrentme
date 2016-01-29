<?php include('header.php'); ?>
  <div class="paddings"></div>
<?php include('home_header_bg.php'); ?>
  <section class="main-categories main-offers detail-page container left-right-border background-light-gray">
        <nav class="crumbs">
            <ul class="col-xs-7">
                <li><a href="/rentme/pages">hey! VIENNA</a></li>
                <li><a href="/rentme/heyrentme">hey! rentme</a></li>
                <li><a href="/rentme/kategorie/index/sport">Sport &amp; Freizeit</a></li>
                <li><a href="/rentme/detail/index/Cube-Mountainbike-RX27" class="active-crumb">Cube Mountainbike RX27</a></li>
            </ul>
            <ul class="col-xs-5 pull-right text-right kaup-select">
                <li id="kaup-hidden">
                    <p class="display-block"><a class="haup-link" href="#">Talente buchen / hey! bookme</a></p>
                    <span id="close-haup" class="haup-close-icon"></span>
                    <p>
                        <img src="/rentme/img/categories/1.jpg" alt=""/>
                        <a href="#">Haus & Garten</a>
                    </p>
                    <p>
                        <img src="/rentme/img/categories/2.jpg" alt=""/>
                        <a href="#">Freizeit & Events</a>
                    </p>
                    <p>
                        <img src="/rentme/img/categories/3.jpg" alt=""/>
                        <a href="#">lebenshilfe</a>
                    </p>
                    <p>
                        <img src="/rentme/img/categories/4.jpg" alt=""/>
                         <a href="#">Körper & Geist</a>
                    </p>
                    <p>
                        <img src="/rentme/img/categories/5.jpg" alt=""/>
                        <a href="#">office</a>
                    </p>
                    <span class="clearfix"></span>
                    <p class="display-block"><a class="haup-link">Equipment mieten / hey! rentme</a></p>
                    <p>
                        <img src="/rentme/img/categories/6.jpg" alt=""/>
                        <a href="#">Werkzeug</a>
                    </p>
                    <p>
                        <img src="/rentme/img/categories/7.jpg" alt=""/>
                         <a href="#">Sport-equipment</a>
                    </p>
                    <p>
                        <img src="/rentme/img/categories/8.jpg" alt=""/>
                        <a href="#">Kinderartikel</a>
                    </p>
                    <p>
                        <img src="/rentme/img/categories/9.jpg" alt=""/>
                        <a href="#">Technik</a>
                    </p>
                </li>
                <li id="kategorie-select"><a class="active-crumb haup-ico" href="#">Hauptkategorien &nbsp;&nbsp;&nbsp;</a></li>
                <li><a class="active-crumb" href="#">In dieser Kategorie anbieten</a></li>
            </ul>
        </nav>
        <span class="clearfix"></span>
  </section>
  <section class="product-detail container background-light-gray container">
    <div class="row">
        <div class="col-md-8 left-side">
            <div class="row">
                <div class="col-sm-8 col-xs-7"><h1>Cube Mountainbike RX27</h1></div>
                <div class="col-sm-4 col-xs-5 detail-controls">
                    <a href="#"> <span class="left-icon"></span></a>
                    <a href="#"><span class="right-icon"></span></a>
                    <span class="back-icon" id="sharing-button">
                      <span id="social-share">
                      <a class="social fb" href="#"></a>
                      <a class="social flash" href="#"></a>
                      <a class="social tweet" href="#"></a>
                      <a class="social email" href="#"></a>
                      </span>
                    </span>
                </div>
            </div>
            <div class="product-info">
                <ul>
                    <li><span class="marked glyphicon glyphicon-star"></span> </li>
                    <li><span class="marked glyphicon glyphicon-star"></span> </li>
                    <li><span class="marked glyphicon glyphicon-star"></span> </li>
                    <li><span class="glyphicon glyphicon-star"></span> </li>
                    <li><span class="glyphicon glyphicon-star"></span> </li>
                </ul>
            </div>
            <!-- VIDEO or GALLERY -->
            <span class="status">Letzer Status für dieses Angebot:</span>
            <p class="detail-notice">Das ist eine Statusmeldung. Mir geht es gut und ich fühle mich heute super wohl!</p>
            <div class="position-relative" id="media-container">
              <img src="/rentme/img/cart.svg" alt="cart" class="cart">
              <div class="discount discount-big">
                <span>-20%</span>
              </div>
              <div id="media-content">
                  <img src="/rentme/img/placeholder/video2.jpg" alt=""/>    </div>

              <iframe id="player"></iframe>
              <div class="media-list" id="media-list">

                  <span class="media-img"><!--<a id="videos-ico"></a>--><img src="/rentme/img/placeholder/video2.jpg" alt=""/></span>
                  <span class="media-img"> <img src="/rentme/img/placeholder/video3.jpg" alt=""/>  </span>
                  <span><a class="videos-ico" id="3"></a><img src="/rentme/img/placeholder/video2.jpg" id="3" alt=""/>   </span>

              </div>
            </div>
        <script src="/rentme/js/vendors/vimeo.js"></script>
        <script>
          $(document).ready(function(){

              $(document).on('click', "#videos-ico", function (evt) {
                  $('#videos-ico').remove();
                  $iframeSRC = "http://player.vimeo.com/video/133739188?api=1&player_id=player";
                  $("#player").attr("src",$iframeSRC);
              });

              $('#videos-ico').show();
              var containerHieght = $('#media-content').height();
              $("#player").css('height',containerHieght);
              var player = $f(document.getElementById('player'));
              var media = $('#media-list .media-img img');
              var mediaContainer = $('#media-content');


              $(media).on('click',function(e){
                  $("#player").attr("src",'');
                  var vals = $(this).parent().html();
                  $(mediaContainer).html(vals);
                  console.log (vals);
                  $(".product-detail .left-side .cart, .discount.discount-big").show();
              });


              $("#3").click(function() {
                  $('#videos-ico').remove();
                  $iframeSRC = "http://player.vimeo.com/video/133739188?api=1&player_id=player";
                  $("#player").attr("src",$iframeSRC);
                  $(".product-detail .left-side .cart, .discount.discount-big").hide();
              });
          })
        </script>
            <div>
                <div class="row">
                    <div class="col-md-8 col-xs-12 product-description" id="map-sibling">
                        <p class="no-top-margin text-padding">
                            <span class="text-bold mobile-hidden =">Beschreibung:</span><br class="mobile-hidden"><br class="mobile-hidden">
                            Das ist eine Tischler Beschreibung consed dolore conse ex ex exerciduis at. Duipisl ilis acidunt inci exeriusto core dolummod eu feugait at wisisci bla consenibh enibh et ad tet autpate velesto coreetum nullumsandio odit nullaorem num volore.
                            Tincip erostrud dolore magnis atum quisse
                            d eugiam, sis nisit nullaorpero duis nim nit nonseni ssismolesto commy nullum nos adio odolorem iriusto ex esto diatet alisim ipisl il doluptate tat, corpero ex elenibh ea facidunt lummolor am inim nim ing eriliquat.
                        </p>
                    </div>
                    <div class="col-md-4 col-xs-12" id="googleMap">
                    </div>
                </div>
            </div>
            <div class="panel-group" id="panel-quote-group">
                <div class="panel panel-default">
                    <div class="panel-heading" id="panel-heading">
                        <p class="panel-title">
                           Fragen und Antworten
                            <span class="pull-right">
                                <a>
                                    <span class="toggle-icon glyphicon glyphicon-triangle-bottom"></span>
                                </a>
                            </span>
                        </p>
                    </div>
                    <div id="collapseQuote" class="panel-collapse collapse2 collapse">
                        <div class="panel-body">
                           <p class="panel-head">Das besondere an deinem Angebot?</p>
                            <p>
                                Das ist eine Mountainbike Beschreibung consed dolore conse ex ex exerciduis at. Duipisl ilis acidunt inci exeriusto core dolummod eu feugait at wisisci bla consenibh enibh et ad tet autpate velesto coreetum nullumsandio odit nullaorem num volore.
                            </p>
                            <p class="panel-head">Bester Einsatzbereich?</p>
                            <p>
                                Das ist eine Mountainbike Beschreibung consed dolore conse ex ex exerciduis at. Duipisl ilis acidunt inci exeriusto core dolummod eu feugait at wisisci bla consenibh enibh et ad tet autpate velesto coreetum nullumsandio odit nullaorem num volore.
                            </p>
                            <p class="panel-head">Was sollte man noch darüber wissen?</p>
                            <p>
                                Das ist eine Mountainbike Beschreibung consed dolore conse ex ex exerciduis at. Duipisl ilis acidunt inci exeriusto core dolummod eu feugait at wisisci bla consenibh enibh et ad tet autpate velesto coreetum nullumsandio odit nullaorem num volore.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-group desktop-hide" id="panel-quote-group2">
                <div class="panel panel-default">
                    <div class="panel-heading" id="panel-heading1">
                        <p class="panel-title">
                            BEWERTUNGEN
                            <span class="pull-right">
                                <a>
                                    <span class="toggle-icon glyphicon glyphicon-triangle-bottom"></span>
                                </a>
                            </span>
                        </p>
                    </div>
                    <div id="collapseQuote" class="panel-collapse collapse1 collapse">
                        <div class="panel-body">
                            <div class="row user-notes">
                                <div class="col-md-4">
                                    <div class="product-info">
                                        <ul>
                                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                                            <li><span class="glyphicon glyphicon-star"></span> </li>
                                            <li><span class="glyphicon glyphicon-star"></span> </li>
                                        </ul>
                                        <p>
                                            Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="product-info">
                                        <ul>
                                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                                            <li><span class="glyphicon glyphicon-star"></span> </li>
                                            <li><span class="glyphicon glyphicon-star"></span> </li>
                                        </ul>
                                        <p>
                                            Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="product-info">
                                        <ul>
                                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                                            <li><span class="glyphicon glyphicon-star"></span> </li>
                                            <li><span class="glyphicon glyphicon-star"></span> </li>
                                        </ul>
                                        <p>
                                            Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget.
                                        </p>
                                    </div>
                                </div>
                                <!-- row end -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- notes area -->
            <div class="row user-notes mobile-hidden">
                <div class="col-md-4">
                    <div class="product-info">
                        <ul>
                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                            <li><span class="glyphicon glyphicon-star"></span> </li>
                            <li><span class="glyphicon glyphicon-star"></span> </li>
                        </ul>
                        <p>
                            Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="product-info">
                        <ul>
                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                            <li><span class="glyphicon glyphicon-star"></span> </li>
                            <li><span class="glyphicon glyphicon-star"></span> </li>
                        </ul>
                        <p>
                            Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="product-info">
                        <ul>
                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                            <li><span class="glyphicon glyphicon-star"></span> </li>
                            <li><span class="glyphicon glyphicon-star"></span> </li>
                        </ul>
                        <p>
                            Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget.
                        </p>
                    </div>
                </div>
                <!-- row end -->
            </div>
            <div class="row user-notes mobile-hidden">
                <div class="col-md-4">
                    <div class="product-info">
                        <ul>
                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                            <li><span class="glyphicon glyphicon-star"></span> </li>
                            <li><span class="glyphicon glyphicon-star"></span> </li>
                        </ul>
                        <p>
                            Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="product-info">
                        <ul>
                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                            <li><span class="glyphicon glyphicon-star"></span> </li>
                            <li><span class="glyphicon glyphicon-star"></span> </li>
                        </ul>
                        <p>
                            Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="product-info">
                        <ul>
                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                            <li><span class="marked glyphicon glyphicon-star"></span> </li>
                            <li><span class="glyphicon glyphicon-star"></span> </li>
                            <li><span class="glyphicon glyphicon-star"></span> </li>
                        </ul>
                        <p>
                            Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget.
                        </p>
                    </div>
                </div>
                <!-- row end -->
                <div class="col-xs-12">
                    <a data-toggle="modal" data-target="#feedback-modal" class="green-big-button" href="#">Jetzt Anfragen</a>
                </div>
            </div>
        <!-- LEFT side ends -->
        </div>
        <div class="col-md-4 right-side">
            <div class="offer-dates">
                <h3>Anfrage für:</h3>
                <h4>Cube Mountainbike RX27</h4>
                <div class="date-picker">
                    <!-- Date -->
                    <div class="date-container  position-relative">
                      <p class="mobile-hidden">Beginn / Ende</p>
                      <div class="input-group date" id="datetimepicker1">
                          <input type="text" class="form-control" id="date-range" />
                      </div>
                      <div class="input-group date display-none" id="datetimepicker2">
                          <input type="text" class="form-control" id="date-range1" />
                      </div>
                    </div>
                    <!-- Time -->
                    <div class="time-container">
                        <p class="mobile-hidden">Uhrzeit</p>
                        <select id="tests" name="gender" class="selectpicker bs-select-hidden">
                            <option value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 01:00">01:00 Uhr</option>
                            <option value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 02:00">02:00 Uhr</option>
                            <option value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 03:00">03:00 Uhr</option>
                            <option value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 04:00">04:00 Uhr</option>
                            <option value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 05:00">05:00 Uhr</option>
                            <option value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 06:00">06:00 Uhr</option>
                            <option value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 07:00">07:00 Uhr</option>
                            <option value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 08:00">08:00 Uhr</option>
                            <option value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 09:00">09:00 Uhr</option>
                            <option selected="selected" value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 10:00">10:00 Uhr</option>
                            <option value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 11:00">11:00 Uhr</option>
                            <option value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 12:00">12:00 Uhr</option>
                            <option value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 13:00">13:00 Uhr</option>
                            <option value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 14:00">14:00 Uhr</option>
                            <option value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 15:00">15:00 Uhr</option>
                            <option value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 16:00">16:00 Uhr</option>
                            <option value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 17:00">17:00 Uhr</option>
                            <option value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 18:00">18:00 Uhr</option>
                            <option value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 19:00">19:00 Uhr</option>
                            <option value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 20:00">20:00 Uhr</option>
                            <option value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 21:00">21:00 Uhr</option>
                            <option value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 22:00">22:00 Uhr</option>
                            <option value="Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp; 23:00">23:00 Uhr</option>
                        </select>
                        <select id="tests2" name="gender" class="selectpicker bs-select-hidden">
                            <option value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 01:00">01:00 Uhr</option>
                            <option value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 02:00">02:00 Uhr</option>
                            <option value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 03:00">03:00 Uhr</option>
                            <option value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 04:00">04:00 Uhr</option>
                            <option value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 05:00">05:00 Uhr</option>
                            <option value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 06:00">06:00 Uhr</option>
                            <option value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 07:00">07:00 Uhr</option>
                            <option value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 08:00">08:00 Uhr</option>
                            <option value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 09:00">09:00 Uhr</option>
                            <option value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 10:00">10:00 Uhr</option>
                            <option value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 11:00">11:00 Uhr</option>
                            <option value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 12:00">12:00 Uhr</option>
                            <option value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 13:00">13:00 Uhr</option>
                            <option value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 14:00">14:00 Uhr</option>
                            <option value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 15:00">15:00 Uhr</option>
                            <option value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 16:00">16:00 Uhr</option>
                            <option value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 17:00">17:00 Uhr</option>
                            <option selected="selected" value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 18:00">18:00 Uhr</option>
                            <option value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 19:00">19:00 Uhr</option>
                            <option value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 20:00">20:00 Uhr</option>
                            <option value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 21:00">21:00 Uhr</option>
                            <option value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 22:00">22:00 Uhr</option>
                            <option value="Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp; 23:00">23:00 Uhr</option>
                        </select>
                    </div>
                    <p>Deine Gesamtkosten<span class="info-icon" data-toggle="tooltip" title="Hooray!"></span></p>
                    <p class="price"><span>ab</span><b class="price-calc">30</b>,-<span class="euro-sign">&#8364;</span></p>
                    <button  data-toggle="modal" data-target="#feedback-modal"  class="green-big-button">Jetzt anfragen!</button>
                </div>
            </div>
            <div class="user-data">
                <h3>Details:</h3>
                <img src="/rentme/img/placeholder/ulli.png" alt="Ulli" class="user-avatar"/>
                <p class="user-name">Ulli</p>
                <div class="user-data-tabs">
                  <div class="user-tab euro">
                    <p>
                      15 € pro Tag
                    </p>
                  </div>
                  <div class="user-tab cal">
                    <p>
                      Vorm. / Nachm. / WE
                    </p>
                  </div>
                  <div class="user-tab steps">
                    <p>
                      Treffpunkt an deinem Wunschort
                    </p>
                  </div>
                  <div class="user-tab gruppen">
                    <p>
                      Bietet auch Gruppenstunden an
                    </p>
                  </div>
                </div>
            </div>
            <div class="user-info">
                <img src="/rentme/img/placeholder/info.jpg" alt="info"/>
                <div class="row">
                    <div class="col-xs-9">
                        <p>Die 10 besten mountainbike
                        strecken in österreich</p>
                    </div>
                    <div class="col-xs-3 grey-info">
                        INFO
                    </div>
                </div>
                <img src="/rentme/img/info-icon.svg" alt="info-icon" class="icon-info"/>
              </div>
        </div>
        <!-- End of right side -->
    </div>
  </section>
  <!-- Bottom featured items -->
  <section class="main-categories main-offers featured-offers mobile-visible">
    <div class="container border-light-gray">
        <div class="row">
            <p class="see-also mobile-hidden">Das könnte dich auch interessieren:</p>
            <div class="col-sm-3">
                <div>
                <img src="/rentme/img/placeholder/bike.png" alt="placeholder"/>
                <img src="/rentme/img/cart-pro.svg" alt="cart" class="cart"/>
                <div class="product-info">
                    <div class="row">
                        <div class="col-xs-6">
                            <ul>
                                <li><span class="marked glyphicon glyphicon-star"></span> </li>
                                <li><span class="marked glyphicon glyphicon-star"></span> </li>
                                <li><span class="marked glyphicon glyphicon-star"></span> </li>
                                <li><span class="marked glyphicon glyphicon-star"></span> </li>
                                <li><span class="glyphicon glyphicon-star"></span> </li>
                            </ul>
                            <p class="product-name">
                                cannondale
                            </p>
                        </div>
                        <div class="col-xs-6">
                            <p class="price">
                                15.00 &#8364;
                            </p>
                            <p class="tag">
                                pro tag
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <div class="col-sm-3">
                <div>
                <img src="/rentme/img/placeholder/bike2.png" alt="placeholder"/>
                <div class="product-info">
                    <div class="row">
                        <div class="col-xs-6">
                            <ul>
                                <li><span class="marked glyphicon glyphicon-star"></span> </li>
                                <li><span class="marked glyphicon glyphicon-star"></span> </li>
                                <li><span class="marked glyphicon glyphicon-star"></span> </li>
                                <li><span class="glyphicon glyphicon-star"></span> </li>
                                <li><span class="glyphicon glyphicon-star"></span> </li>
                            </ul>
                            <p class="product-name">
                                votec
                            </p>
                        </div>
                        <div class="col-xs-6">
                            <p class="price">
                                12.00 &#8364;
                            </p>
                            <p class="tag">
                                statt 20 € pro Std
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <div class="col-sm-3">
                <div>
                <img src="/rentme/img/placeholder/bike2.png" alt="placeholder"/>
                <div class="product-info">
                    <div class="row">
                        <div class="col-xs-6">
                            <ul>
                                <li><span class="marked glyphicon glyphicon-star"></span> </li>
                                <li><span class="marked glyphicon glyphicon-star"></span> </li>
                                <li><span class="marked glyphicon glyphicon-star"></span> </li>
                                <li><span class="glyphicon glyphicon-star"></span> </li>
                                <li><span class="glyphicon glyphicon-star"></span> </li>
                            </ul>
                            <p class="product-name">
                                specialized
                            </p>
                        </div>
                        <div class="col-xs-6">
                            <p class="price">
                                12.00 &#8364;
                            </p>
                            <p class="tag">
                                pro tag
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <div class="col-sm-3">
                <div>
                <img src="/rentme/img/placeholder/bike2.png" alt="placeholder"/>
                <div class="product-info">
                    <div class="row">
                        <div class="col-xs-6">
                            <ul>
                                <li><span class="marked glyphicon glyphicon-star"></span> </li>
                                <li><span class="marked glyphicon glyphicon-star"></span> </li>
                                <li><span class="marked glyphicon glyphicon-star"></span> </li>
                                <li><span class="glyphicon glyphicon-star"></span> </li>
                                <li><span class="glyphicon glyphicon-star"></span> </li>
                            </ul>
                            <p class="product-name">
                                votec
                            </p>
                        </div>
                        <div class="col-xs-6">
                            <p class="price">
                                12.00 &#8364;
                            </p>
                            <p class="tag">
                                pro tag
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
  </section>
  <section class="detail-bottom container border-light-gray">
    <div class="row">
        <div class="col-xs-12">
            <a href="#" class="button-black backgound-yellow text-black">zurück zur übersicht </a>
            <div class="detail-controls">
                <a href="#"> <span class="left-icon"></span></a>
                <a href="#"><span class="right-icon"></span></a>
                <span class="back-icon" id="sharing-button2">
                      <span id="social-share-bottom">
                      <a class="social fb" href="#"></a>
                      <a class="social flash" href="#"></a>
                      <a class="social tweet" href="#"></a>
                      <a class="social email" href="#"></a>
                      </span>
                    </span>
            </div>
            <a class="add-offer" href="#" data-toggle="modal" data-target="#melden">Angebot melden</a>
        </div>
    </div>
  </section>
  <!-- Modal -->
  <div id="feedback-modal" class="modal fade" role="dialog">
    <div class="modal-dialog rent-dialog angrafge-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><img src="/rentme/img/close-modal.svg" alt="close"/></button>
                <h4 class="modal-title">Anfrage</h4>
                <p><span class="text-bold" >Abholung:</span>  <span id="from-data"></span>, <span id="from-time">&nbsp; &nbsp; &nbsp; &nbsp; 10:00</span> </p>
                <p><span class="text-bold">Rückgabe:</span> <span id="to-data"></span>, <span id="to-time">&nbsp; &nbsp; &nbsp; &nbsp; 18:00</span></p>
                <p><span class="text-bold">Gesamtkosten Miete: </span><span class="price-calc">30</span>,- €</p>
                <p><span class="text-bold">Kaution:</span> 100,- €</p>
                <p><span class="text-bold">Preis bei Kauf:</span> 800,- €</p>
                <form method="post" accept-charset="utf-8" class="rent" action="/rentme/detail/index/1"><div style="display:none;"><input type="hidden" name="_method" value="POST"></div><input type="text" name="name" placeholder="DEIN NAME*" id="name" value=""><input type="text" name="name2" placeholder="DEIN EMAIL*" id="name2" value=""><textarea name="desc" placeholder="NACHRICHT: WOFÜR BENÖTIGST DU DAS EQUIPMENT?*" id="desc" rows="5"></textarea><input type="submit" value="abschicken"></form>            </div>
        </div>
    </div>
  </div>
  <div id="melden" class="modal fade" role="dialog">
    <div class="modal-dialog" role="document">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><img src="/rentme/img/close-modal.svg" alt="close"/></button>
                <h4 class="modal-title">Angebot melden</h4>
                <form method="post" accept-charset="utf-8">
                  <input type="text" name="mail" placeholder="ANGEBOT MELDEN">
                  <textarea placeholder="DEINE NACHRICHT..." class="place_black"></textarea>
                  <input type="submit" value="Abschicken"></form>
            </div>
        </div>
    </div>
  </div>
</div>
  <script>
    function addData(tag,data) {
        var tag  = tag;
        var data = data;
        var date = data.replace('AB', '');
        $(tag).html(date);
    };
    $(document).ready(function(){
        var t1 = $('#date-range').val(moment().format('[AB] DD.MM.YYYY'));
        var t2 = (moment().add(1, "days").format('[BIS] DD.MM.YYYY'));

        $('#date-range').val(t1.val() + ' ' + t2);

        $('#date-range').val().replace('AB', '');
        $('#date-range1').val().replace('BIS', '');

        addData('#from-data',$('#date-range').val());
        addData('#to-data',$('#date-range1').val());

        // Date range picker
        var configObject = {
            separator : 'Bis ',
            language:'de',
            format:'DD.MM.YYYY',
            autoClose: true,
            startDate: new Date(),
            selectForward: true,
            duration: 0,
            container:'.date-container',
            singleMonth: true,
            showShortcuts: false,
            showTopbar: false,

            getValue: function()
            {
                if ($('#date-range').val() && $('#date-range1').val() )
                    //return $('#date-range').val() + ' Bis ' + $('#date-range1').val();
                //else
                    return '';
            },
            setValue: function(s,s1,s2)
            {
                $('#date-range').val('AB '+ s1);
                $('#date-range1').val('BIS '+s2);

                addData('#from-data',$('#date-range').val());
                addData('#to-data',$('#date-range1').val());

                var startData = $('#date-range').val().replace('AB', '');
                var endData   = $('#date-range1').val().replace('BIS', '');

                // split the date into days, months, years array
                var x = startData.split('.');
                var y = endData.split('.');

                var a = new Date(x[2],x[1],x[0]);
                var b = new Date(y[2],y[1],y[0]);

                var c = ( b - a );
                var d = c / (1000 * 60 * 60 * 24);

                $('.price-calc').html((Math.round(d)+1)*30);
                $('#date-range').val('AB '+ s1 + ' BIS '+s2);
            }
        };
        $('#datetimepicker1').dateRangePicker(configObject).bind('datepicker-open',function()  {
            $(this).children('input').css('background-color','#f0c814').css('color','white').addClass('calendar-icon-hover');
        }).bind('datepicker-closed',function()
        {
            $(this).children('input').css('background-color','white').css('color','black').removeClass('calendar-icon-hover');
        });

        $('#datetimepicker2').dateRangePicker(configObject).bind('datepicker-open',function()  {
            $(this).children('input').css('background-color','#f0c814').css('color','white').addClass('calendar-icon-hover');
        }).bind('datepicker-closed',function()
        {
            $(this).children('input').css('background-color','white').css('color','black').removeClass('calendar-icon-hover');
        });

        $('#tests').change(function(){
            var time1 = $(this).val();
            $('#from-time').html(time1.replace('Uhrzeit ab ',''));
            $(this).parent().find('.bootstrap-select').eq(0).find('.dropdown-toggle').text($(this).val()).prepend('<span class="caret"></span>');
        });
        $('#tests2').change(function(){
            var time1 = $(this).val();
            $('#to-time').html(time1.replace('Uhrzeit bis ',''));
            $(this).parent().find('.bootstrap-select').eq(1).find('.dropdown-toggle').text($(this).val()).prepend('<span class="caret"></span>');
        });
        $('#myVideoPlay').on('click',function(){
            $(this).hide();
        })
    });
    $(window).load(function(){
        $('#tests').parent().find('.bootstrap-select').eq(0).find('.dropdown-toggle').html('Uhrzeit ab &nbsp; &nbsp; &nbsp; &nbsp;   10:00').prepend('<span class="caret"></span>');
        $('#tests2').parent().find('.bootstrap-select').eq(1).find('.dropdown-toggle').html('Uhrzeit bis &nbsp; &nbsp; &nbsp; &nbsp;  18:00').prepend('<span class="caret"></span>');
    });
  </script>
  <script src="http://maps.googleapis.com/maps/api/js?language=de"></script>
  <script>
    var map;
    var lat=48.209206;
    var lng=16.372778;
    var zoom=16;

    function initialize() {
        var myLatlng = new google.maps.LatLng(lat,lng);
        var myOptions = {
            zoom: zoom,
            draggable: false,
            center: myLatlng,
                        mapTypeId: google.maps.MapTypeId.ROADMAP
        }
        var map = new google.maps.Map(document.getElementById("googleMap"), myOptions);

        var markers = [

            ['Poznań', 48.209206, 16.372778]
        ];

        var image = 'img/marker.png';

        for (var i = 0; i < markers.length; i++) {
            var draftMarker = markers[i];
            var myLatLng = new google.maps.LatLng(draftMarker[1], draftMarker[2]);
            var marker = new google.maps.Marker({
                position: myLatLng,
                map: map,
                title: draftMarker[0],
                icon: image
            });
        }
    }
    google.maps.event.addDomListener(window, 'load', initialize);
  </script>
  </div>
<?php include("page_footer.php"); ?>
<?php include('footer.php'); ?>
