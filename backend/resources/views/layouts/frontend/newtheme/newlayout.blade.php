<!DOCTYPE html>
<html>

<head>
  <!-- Basic -->
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <!-- Mobile Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <!-- Site Metas -->
  <meta name="title" content="Arbuda Bangles - Metal, Plastic, & Customized Bangles | All Over India" />
  <meta name="keywords" content="metal bangles, plastic bangles, customized bangles, wholesale bangles, bangles for women, traditional bangles, Indian bangles, designer bangles, bangle sets, bangles online, Arbuda Bangles" />
  <meta name="description" content="Explore a wide range of metal, plastic, and customized bangles at Arbuda Bangles. We offer quality bangles with unique designs, available all over India. Shop now for traditional and modern styles, perfect for every occasion." />
  <meta name="author" content="" />

  <title>@yield('title')</title>

  <!-- slider stylesheet -->
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.1.3/assets/owl.carousel.min.css" />

  <!-- bootstrap core css -->
  <link rel="stylesheet" type="text/css" href="{{asset('assets/frontend/newtheme/css/bootstrap.css')}}" />

  <!-- fonts style -->
  <link href="https://fonts.googleapis.com/css?family=Baloo+Chettan|Poppins:400,600,700&display=swap" rel="stylesheet">
  <!-- Custom styles for this template -->
  <link href="{{asset('assets/frontend/newtheme/css/style.css')}}" rel="stylesheet" />
  <!-- responsive style -->
  <link href="{{asset('assets/frontend/newtheme/css/responsive.css')}}" rel="stylesheet" />
   @yield('topjscss')
</head>

<body>

  <div class="">
    <!-- header section strats -->
    <header class="header_section">
      <div class="container-fluid">
        <nav class="navbar navbar-expand-lg custom_nav-container ">
            <a href="/home" class="navbar-brand"><img src="{{asset('assets/images/company/ablogo.png')}}" alt="Arbuda Bangles" /></a>
         <a class="nav-link navbar-toggler text-white bg-success text-center" href="/login">&#128129;<br>Login</a>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <div class="d-flex ml-auto flex-column flex-lg-row align-items-center">
              <ul class="navbar-nav  ">
                <li class="nav-item active">
                  <a class="nav-link" href="/home">Home <span class="sr-only">(current)</span></a>
                </li>
                <!--<li class="nav-item">
                  <a class="nav-link" href="about.html"> About</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="contact.html">Contact us</a>
                </li>
                --><li class="nav-item">
                  <a class="nav-link" href="/login">Login</a>
                </li>
              </ul>

            </div>
            <div class="quote_btn-container ">
              <a href="">
                <img src="{{asset('assets/frontend/newtheme/images/cart.png')}}" alt="">
                <div class="cart_number">
                  0
                </div>
              </a>
              <form class="form-inline">
                <button class="btn  my-2 my-sm-0 nav_search-btn" type="submit"></button>
              </form>
            </div>
          </div>
        </nav>
      </div>
    </header>
    <!-- end header section -->
  </div>
<!-- slider section -->
   
    <!-- end slider section -->
  <!-- item section -->
  
  <!-- BEST ITem -->
  @yield('main')
  
  
  <!-- info section -->
  <section class="info_section ">
    <div class="container">
      <div class="info_container text-center">
        <div class="row">
          <div class="col-md-3">
            <div class="info_logo">
              <a href="">
                <img src="{{asset('assets/images/company/ablogo.png')}}" alt="">
              </a>
            </div>
          </div>
          <div class="col-md-3">
            <div class="info_contact">
              <a href="">
                <img src="{{asset('assets/frontend/newtheme/images/location.png')}}" alt=""><br>
                <span>
                  Rangati Kapada Bazar, Nr. Sutharwada Pole, Astodia,<br>
Ahmedabad 380001. Gujarat.
                </span>
              </a>
            </div>
          </div>
          <div class="col-md-3">
            <div class="info_contact">
              <a href="">
                <img src="{{asset('assets/frontend/newtheme/images/phone.png')}}" alt=""><br>
                <span>
                  7863883549<br>
                  7863867139
                </span>
              </a>
            </div>
          </div>
          <div class="col-md-3">
            <div class="info_contact">
              <a href="">
                <img src="{{asset('assets/frontend/newtheme/images/mail.png')}}" alt="email"><br>
                <span>
                  info@abbangles.com<br>
                  support@abbangles.com
                </span>
              </a>
            </div>
          </div>
        </div>
        <!--<div class="info_form">
          <div class="d-flex justify-content-center">
            <h5 class="info_heading">
              Newsletter
            </h5>
          </div>
          <form action="">
            <div class="email_box">
              <label for="email2">Enter Your Email</label>
              <input type="text" id="email2">
            </div>
            <div>
              <button>
                subscribe
              </button>
            </div>
          </form>
        </div>-->
        <div class="info_social">
          <div class="d-flex justify-content-center">
            <h5 class="info_heading">
              Follow Us
            </h5>
          </div>
          <div class="social_box">
            <a href="https://www.facebook.com/profile.php?id=100078301658398" target="_blank">
              <img src="{{asset('assets/frontend/newtheme/images/fb.png')}}" alt=""><!--
            </a>
            <a href="" target="_blank">
              <img src="{{asset('assets/frontend/newtheme/images/twitter.png')}}" alt="">
            </a>
            <a href="" target="_blank">
              <img src="{{asset('assets/frontend/newtheme/images/linkedin.png')}}" alt="">
            </a>-->
            <a href="https://www.instagram.com/arbudabangles1721?utm_source=qr&igsh=OXdxNjhnOWM5eHF2" target="_blank">
              <img src="{{asset('assets/frontend/newtheme/images/insta.png')}}" alt="">
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- end info_section -->

  <!-- footer section -->
  <section class="container-fluid footer_section">
    <p>
      &copy; <span id="displayYear"></span> All Rights Reserved By
      <a href="https://abbangles.com/">Arbuda Bangles</a>
    </p>
  </section>
  <!-- footer section -->

  <script type="text/javascript" src="{{asset('assets/frontend/newtheme/js/jquery-3.4.1.min.js')}}"></script>
  <script type="text/javascript" src="{{asset('assets/frontend/newtheme/js/bootstrap.js')}}"></script>
  <script type="text/javascript" src="{{asset('assets/frontend/newtheme/js/custom.js')}}"></script>
  <script>
      $(document).ready(function() {
    
 $('.ytVideo').mouseenter(function(){

  $(this).find('iframe').attr("src",$(this).find('iframe').attr("src") + "&autoplay=1");
   
 });

$(".ytVideo").mouseleave(function(){
    var src= $(this).find('iframe').attr("src");

  var arr_str = src.split("&");
   $(this).find('iframe').attr("src",arr_str[0]+'&'+arr_str[1]+'&'+arr_str[2]);
  });
   

});
  </script>
</body>

</html>