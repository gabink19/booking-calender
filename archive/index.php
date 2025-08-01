<!DOCTYPE html>
<html lang="en">

  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="description" content="">
    <meta name="author" content="Tooplate">
    <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700" rel="stylesheet">

    <title>Booking Page</title>

    <!-- Bootstrap core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.min.css?version=<?=time()?>" rel="stylesheet">

    <!-- Additional CSS Files -->
    <link rel="stylesheet" href="assets/css/fontawesome.css?version=<?=time()?>">
    <link rel="stylesheet" href="assets/css/tooplate-main.css?version=<?=time()?>">
    <link rel="stylesheet" href="assets/css/owl.css?version=<?=time()?>">
    <link rel="stylesheet" href="assets/css/booking.css?version=<?=time()?>">

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  </head>
  <body>

    <div class="sequence">
  
      <div class="seq-preloader">
        <svg width="39" height="16" viewBox="0 0 39 16" xmlns="http://www.w3.org/2000/svg" class="seq-preload-indicator"><g fill="#F96D38"><path class="seq-preload-circle seq-preload-circle-1" d="M3.999 12.012c2.209 0 3.999-1.791 3.999-3.999s-1.79-3.999-3.999-3.999-3.999 1.791-3.999 3.999 1.79 3.999 3.999 3.999z"/><path class="seq-preload-circle seq-preload-circle-2" d="M15.996 13.468c3.018 0 5.465-2.447 5.465-5.466 0-3.018-2.447-5.465-5.465-5.465-3.019 0-5.466 2.447-5.466 5.465 0 3.019 2.447 5.466 5.466 5.466z"/><path class="seq-preload-circle seq-preload-circle-3" d="M31.322 15.334c4.049 0 7.332-3.282 7.332-7.332 0-4.049-3.282-7.332-7.332-7.332s-7.332 3.283-7.332 7.332c0 4.05 3.283 7.332 7.332 7.332z"/></g></svg>
      </div>
      
    </div>
        <nav>
          <ul>
            <li><a href="#1" onclick="renderTodayBookingTable()"><span class="fa fa-home" style="font-size: 1.7em;color: white !important;"></span> <em>Todays</em></a></li>
            <li><a href="#2"><span class="fa fa-calendar" style="font-size: 1.7em;color: white !important;"></span> <em>Booking</em></a></li>
            <li><a href="#3"><span class="fa fa-gavel" style="font-size: 1.7em;color: white !important;"></span> <em>Rules and Regulations</em></a></li>
            <li><a href="#4"><span class="fa fa-envelope" style="font-size: 1.7em;color: white !important;"></span> <em>Contact Us</em></a></li>
          </ul>
        </nav>
        
        <div class="slides">
          <div class="slide" id="1">
            <div class="content second-content">
                <div class="container-fluid">
                  <h2>Todays</h2>
                  <div class="booking-calendar-responsive">
                  <div id="today-booking-table"></div>
                  </div>
                </div>
            </div>
        </div>
        <div class="slide" id="2">
            <div class="content second-content">
                <div class="container-fluid">
                  <h2>Booking Calendar</h2>
                  <div class="booking-calendar-responsive">
                    <div id="booking-calendar"></div>
                  </div>
                </div>
            </div>
        </div>
        <div class="slide" id="3">
            <div class="content third-content">
                <section class='tabs-content'>
                  <article id='tabs-1'>
                    <h2>Rules and Regulations</h2>
                    <ul style="font-size:1.15em; margin-top: 1em; margin-bottom: 1em;text-align: justify;">
                      <li>1 unit apartemen hanya bisa booking <b>2 jam/minggu</b>.</li>
                      <li>Booking hanya bisa untuk <b>1 minggu ke depan</b>.</li>
                      <li>Jadwal untuk minggu berikutnya akan dibuka tiap <b>Senin pkl 00.01</b>.</li>
                    </ul>
                  </article>
                </section>
            </div>
        </div>
        <div class="slide" id="4">
            <div class="content fourth-content">
                <div class="container-fluid">
                    <form id="contact" action="" method="post">
                        <div class="row">
                          <div class="col-md-12">
                            <h2>Contact Us !</h2>
                            <div class="form-group">
                              <label>
                                <span class="fa fa-phone" style="font-size:1.5em; margin-right:8px;"></span>
                                0822938494849
                              </label>
                            </div>
                          </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Tampilkan ukuran layar -->
    <div id="screen-size-info" style="position:fixed;bottom:10px;right:10px;z-index:99999;background:#222;color:#fff;padding:6px 14px;border-radius:8px;font-size:14px;opacity:0.8;">
      0 x 0
    </div>



    <!-- Bootstrap core JavaScript -->
    <script src="vendor/jquery/jquery.min.js?version=<?=time()?>"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js?version=<?=time()?>"></script>

    <!-- Additional Scripts -->
    <script src="assets/js/owl.js?version=<?=time()?>"></script>
    <script src="assets/js/accordations.js?version=<?=time()?>"></script>
    <script src="assets/js/main.js?version=<?=time()?>"></script>
    <script src="assets/js/booking.js?version=<?=time()?>"></script>

    <script type="text/javascript">
        $(document).ready(function() {

            // navigation click actions 
            $('.scroll-link').on('click', function(event){
                event.preventDefault();
                var sectionID = $(this).attr("data-id");
                scrollToID('#' + sectionID, 750);
            });
            // scroll to top action
            $('.scroll-top').on('click', function(event) {
                event.preventDefault();
                $('html, body').animate({scrollTop:0}, 'slow');         
            });
            // mobile nav toggle
            $('#nav-toggle').on('click', function (event) {
                event.preventDefault();
                $('#main-nav').toggleClass("open");
            });
        });
        // scroll function
        function scrollToID(id, speed){
            var offSet = 0;
            var targetOffset = $(id).offset().top - offSet;
            var mainNav = $('#main-nav');
            $('html,body').animate({scrollTop:targetOffset}, speed);
            if (mainNav.hasClass("open")) {
                mainNav.css("height", "1px").removeClass("in").addClass("collapse");
                mainNav.removeClass("open");
            }
        }
        if (typeof console === "undefined") {
            console = {
                log: function() { }
            };
        }
    </script>
<script>
function updateScreenSizeInfo() {
  const w = window.innerWidth;
  const h = window.innerHeight;
  var el = document.getElementById('screen-size-info');
  if (el) {
    el.style.display = 'block';
    el.textContent = w + ' x ' + h;
  }
}
window.addEventListener('resize', updateScreenSizeInfo);
window.addEventListener('orientationchange', updateScreenSizeInfo);
window.addEventListener('focus', updateScreenSizeInfo);
window.addEventListener('touchstart', updateScreenSizeInfo);
document.addEventListener('DOMContentLoaded', updateScreenSizeInfo);
</script>
  </body>
</html>