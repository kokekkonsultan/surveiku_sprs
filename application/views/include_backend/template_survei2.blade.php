<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>{{ $title }}</title>
  <meta content="" name="description">
  <meta content="" name="keywords">
  <link rel="shortcut icon" href="{{ base_url() }}assets/img/site/logo/favicon.ico" />
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Raleway:300,300i,400,400i,500,500i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">
  <link href="{{ base_url() }}assets/themes/one-page/assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="{{ base_url() }}assets/themes/one-page/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="{{ base_url() }}assets/themes/one-page/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="{{ base_url() }}assets/themes/one-page/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="{{ base_url() }}assets/themes/one-page/assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="{{ base_url() }}assets/themes/one-page/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="{{ base_url() }}assets/themes/one-page/assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="{{ base_url() }}assets/themes/one-page/assets/css/style.css" rel="stylesheet">
  @yield('style')
</head>
<body>

  {{-- @include('include_backend/partials_survei/header') --}}

  @include('include_backend/partials_survei/hero_section')

  <main id="main">
    @yield('content')
  </main>

  @include('include_backend/partials_survei/footer')

  <div id="preloader"></div>
  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <script src="{{ base_url() }}assets/themes/one-page/assets/vendor/purecounter/purecounter.js"></script>
  <script src="{{ base_url() }}assets/themes/one-page/assets/vendor/aos/aos.js"></script>
  <script src="{{ base_url() }}assets/themes/one-page/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="{{ base_url() }}assets/themes/one-page/assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="{{ base_url() }}assets/themes/one-page/assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>
  <script src="{{ base_url() }}assets/themes/one-page/assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="{{ base_url() }}assets/themes/one-page/assets/vendor/php-email-form/validate.js"></script>
  <script src="{{ base_url() }}assets/themes/one-page/assets/js/main.js"></script>
  @yield('javascript')
</body>
</html>