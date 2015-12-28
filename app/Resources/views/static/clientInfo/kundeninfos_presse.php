<?php include('header.php'); ?>
<?php include('home_header.php'); ?>
    <div class="main-header ">
        <img src="/rentme/img/info2.jpg" alt="rent me heading." class="img-responsive max-1600"/>
        <div class="container rent-heading help-heading">
            <div class="row">
                <div class="col-sm-8 text-white">
                    <h1>Kundeninfos</h1><br>
                    <a href="#" class="button-black backgound-yellow text-black">Jetzt kostenlos anmelden</a>
                </div>
            </div>
        </div>
    </div>
  </div>
  </header>
  <section>
    <div class="container background-white rent-container">
        <select class="mobile-menu-select desktop-hide" name="forma" onchange="location = this.options[this.selectedIndex].value;">
          <option value="pages/info">KUNDEINFOS</option>
          <option value="info">AGB</option>
          <option value="datenschutz">DATENSCHUTZ</option>
          <option value="presse">PRESSE</option>
          <option value="kontakt">KONTAKT</option>
          <option value="impressum">IMPRESSUM</option>
        </select>
        <nav class="crumbs">
            <ul class="col-xs-10">
                <li><a href="/rentme/pages">hey! TSC</a></li>
                <li><a href="/rentme/heyrentme">hey! rentme</a></li>
                <li><a href="/rentme/kontakt" class="active-crumb">Kontakt</a></li>
            </ul>
        </nav>
        <span class="clearfix"></span>
        <!-- Left nav -->
        <div class="row help-page">
            <div class="col-sm-3 sidebar mobile-hidden">
                <nav>
                    <ul>
                        <li><a href="/rentme/kundeninfos">AGb</a></li>
                        <li><a href="/rentme/datenschutz">Datenschutz</a></li>
                        <li><a href="/rentme/presse">Presse</a></li>
                        <li><a href="/rentme/kontakt" class="active-link">Kontakt</a></li>
                        <li><a href="/rentme/pages/impressum">Impressum</a></li>
                    </ul>
                </nav>
            </div>
            <div class="col-md-9 content">
                <p class="text-bold">Presse</p>
                <div class="presse">
                    <div>
                        <span><img src="/rentme/img/presse-logo.svg" alt=""/></span>
                        <p><a href="#">Pressematerial gesammelt vom 21. August 2015</a><br>Bildmaterial, Pressetexte, Video</p>
                    </div>
                    <div>
                        <span><img src="/rentme/img/presse-logo2.svg" alt=""/></span>
                        <p><a>Artikel Wiener Zeitung vom 12. September</a><br>Urbanes Innovationspotential<p>
                    </div>
                </div>
            </div>
        </div>
  </section>
    <span class="add-margin mobile-hidden"></span>
  </div>
<?php include("page_footer.php"); ?>
<?php include('footer.php'); ?>
