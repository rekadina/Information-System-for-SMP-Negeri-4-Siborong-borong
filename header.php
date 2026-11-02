<?php
// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>SMP Negeri 4 Siborong-borong</title>
  
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/a-logo-for-smp-negeri-4-siborong-borong-_aIoq0z7QT_S5maIShrI0Hw_ygTK3avFSNeaidrUGLDnCw.jpeg" rel="icon">
  <link href="assets/img/a-logo-for-smp-negeri-4-siborong-borong-_aIoq0z7QT_S5maIShrI0Hw_ygTK3avFSNeaidrUGLDnCw.jpeg" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
   
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">

  <!-- =======================================================
  * Template Name: Medilab
  * Template URL: https://bootstrapmade.com/medilab-free-medical-bootstrap-theme/
  * Updated: Aug 07 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
   <style>
        .berita-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        .berita-header {
            margin-bottom: 30px;
            text-align: center;
        }
        .berita-title {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .berita-meta {
            color: #666;
            margin-bottom: 20px;
        }
        .berita-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .berita-content {
            line-height: 1.8;
            font-size: 1.1rem;
        }
        .gallery-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin: 30px 0;
        }
        .gallery-item {
            flex: 0 0 calc(33.333% - 15px);
        }
        .gallery-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }
        .video-container {
            margin: 30px 0;
        }
        .video-container video {
            width: 100%;
            border-radius: 8px;
        }
        .back-button {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>

<body class="index-page">

  <header id="header" class="header sticky-top">

    <div class="topbar d-flex align-items-center">
      <div class="container d-flex justify-content-center justify-content-md-between">
        <div class="contact-info d-flex align-items-center">
          <i class="bi bi-envelope d-flex align-items-center"><a href="mailto:smpnegeri4sbb@gmail.com"></a>smpnegeri4sbb@gmail.com</i>
          <i class="bi bi-phone d-flex align-items-center ms-4"><span>(021) 6620212</span></i>
        </div>
        <div class="social-links d-none d-md-flex align-items-center">
          <a href="#" class="phone"><i class="bi bi-whatsapp"></i></a>
          <a href="https://www.facebook.com/smpn4siborongborong?_rdc=1&_rdr#" class="facebook"><i class="bi bi-facebook"></i></a>
          <a href="https://www.instagram.com/osis_smpn_4_sbb/" class="instagram"><i class="bi bi-instagram"></i></a>
          <a href="https://www.youtube.com/@SMPNegeri4Siborongborong-tf2eo" class="youtube"><i class="bi bi-youtube"></i></a>
        </div>
      </div>
    </div><!-- End Top Bar -->

    <div class="branding d-flex align-items-center">

      <div class="container position-relative d-flex align-items-center justify-content-between">
        <a class="nav-link" href="index.php">
          <img src="assets/img/a-logo-for-smp-negeri-4-siborong-borong-_aIoq0z7QT_S5maIShrI0Hw_ygTK3avFSNeaidrUGLDnCw.jpeg" alt="Logo" width="100" height="100" class="me-3"></a>

        <nav id="navmenu" class="navmenu">
          <ul>
            <li><a href="#home" class="active">Home<br></a></li>
            <li class="dropdown"><a href="#"><span>School Dashboard</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
              <ul>
                <li><a href="Profile.html">Profile</a></li>
                 <li><a href="SchoolStructure.html">School Structure</a></li>
                <li><a href="SchoolRules.html">School Rules</a></li>
              </ul>
            </li>
            <li><a href="#about">About</a></li>
            <li><a href="#News">News</a></li>
            <li><a href="#Facility">Facility </a></li>
            <li><a href="#teacher">Teacher</a></li>
            <li><a href="#contact">Contact</a></li>
            <li><a href="PPDB.html">PPDB</a></li>
          </ul>
          <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>
      </div>

    </div>

  </header>
<main class="main">