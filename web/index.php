<?php include('header.php'); ?>
    <style>
    html {
       background: url(img/home-bg.jpg) no-repeat center fixed;
       -webkit-background-siz: cover;
       -moz-background-size: cover;
       -o-background-size: cover;
       background-size: cover;
       background-color: transparent!important;
    }
    #feedback {
      display: none;
    }
    body {
        background-color: transparent!important;
    }
    #feedback-modal {
        padding-left: 0px !important;
    }
    .video-close-icon {
        position: absolute;
        top: 50px;
        right: 50px;
        background-color: red;
        display: inline-block;
        width: 100px;
        height: 100px;
    }
    .close {
        position: absolute;
        z-index: 9999999999;
        right: 33px;
        top: 33px;
        width: 34px;
        height: 35px;
        font-size: 30px;
        padding: 1px 1px 7px 2px !important;
        background-size: contain;
    }
        .movie {
            width: 100% !important;
            height: 97vh!important;
        }
        .position-absolute {
            padding-bottom: 4px;
            font-weight: 300;
            position: fixed;
            bottom: 0px;
        }
    </style>
      <section class="welcome text-center">
          <h1 class="text-big">it's time to make more of <span>the things you have!</span></h1>
          <a id="play-ico" class="home-playicon desktop-hide-inline" data-toggle="modal" data-target="#feedback-modal" href="#"></a>
          <div>
              <a href="/heyrentme.php" class="button-yellow">Equipment mieten</a><a href="/heybook.php" class="button-yellow">Talente buchen</a>    </div>
          <a id="play-ico" class="home-playicon mobile-hidden" data-toggle="modal" data-target="#feedback-modal" href="#"></a>
      </section>
      <img src="/img/logo.svg" alt="Hey!Rent me logo" class="main-page-logo"/>
      <div id="feedback-modal" class="modal fade" role="dialog" style="padding: 0px;width: 100%;padding-left: 0px!important;">
          <div class="modal-dialog" style="max-width:100%;width: 100%;margin: 0px;">
              <!-- Modal content-->
              <div class="modal-content" style="max-width: 100%;background-color: transparent;">
                <button style="color:white;" id="pause-button" type="button" class="close" data-dismiss="modal"><span class="close-start-video"></span> </button>
                  <iframe id="video" class="movie" src="https://player.vimeo.com/video/133739188?api=1"  frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
              </div>
          </div>
        </div>
    </div>
    <footer class="position-absolute mobile-hidden">
        <div class="container">
          <div class="social-footer">
              <a class="twitter-follow-button" href="https://twitter.com/hey_tsc" data-size="false" data-show-count="false" data-show-screen-name="false">Folgen</a>
              <div class="fb-follow" data-href="https://www.facebook.com/heyTSC" data-layout="button_count" data-show-faces="false"></div>
          </div>
          <nav class="pull-right margin-top-6">
            <p class="float-right">Site by hey! The Sharing Community GmbH</p>
        </nav>
        </div>
    </footer>
    <script src="/js/vendors/vimeo.js"></script>
    <script>
        var iframe = document.getElementById('video');

        // $f == Froogaloop
        var player = $f(iframe);

        var playButton = document.getElementById("play-ico");
        playButton.addEventListener("click", function() {
            player.api("play");
        });

        var pauseButton = document.getElementById("pause-button");
        pauseButton.addEventListener("click", function() {
            player.api("pause");
        });
    </script>
<?php include('footer.php'); ?>
