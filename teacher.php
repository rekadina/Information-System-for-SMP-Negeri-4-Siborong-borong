<?php
require_once 'db_config.php';

$query_guru = "SELECT * FROM teachers WHERE is_tu = 0 ORDER BY position DESC, name ASC";
$guru = $conn->query($query_guru);

$query_tata_usaha = "SELECT * FROM teachers WHERE is_tu = 1 ORDER BY name ASC";
$tata_usaha = $conn->query($query_tata_usaha); 	

if (!$guru || !$tata_usaha) {
    die("Query error: " . $conn->error);
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
</head>

<body class="index-page">

  <header id="header" class="header sticky-top">

    <div class="topbar d-flex align-items-center">
      <div class="container d-flex justify-content-center justify-content-md-between">
        <div class="contact-info d-flex align-items-center">
          <i class="bi bi-envelope d-flex align-items-center"><a href="smpn4_siborongborong@yahoo.com">smpn4_siborongborong@yahoo.com</a></i>
          <i class="bi bi-phone d-flex align-items-center ms-4"><span>(021) 6620212</span></i>
        </div>
        <div class="social-links d-none d-md-flex align-items-center">
          <a href="#" class="twitter"><i class="bi bi-twitter-x"></i></a>
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
            <li><a href="index.php#home" >Home<br></a></li>
            <li class="dropdown"><a href="#" class="active"><span>School Dashboard</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
              <ul>
                <li><a href="Profile.html">Profile</a></li>

                 <li><a href="SchoolStructure.html">School Structure</a></li>
                <li><a href="SchoolRules.html">School Rules</a></li>
              </ul>
            </li>
            <li><a href="index.php#about">About</a></li>
            <li><a href="index.php#News">News</a></li>
            <li><a href="index.php#Facility">Facility </a></li>
            <li><a href="index.php#teacher">Teacher</a></li>
            <li><a href="index.php#contact">Contact</a></li>
            <li><a href="PPDB.php" class="active">PPDB</a></li>
          </ul>
          <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>
      </div>

    </div>

  </header>

    <section id="teacher" class="doctors section">
        <div class="container section-title" data-aos="fade-up">
            <h1>Tenaga Pendidik</h1>
            <p1>Terima kasih kepada para tenaga pendidik yang terus berjuang menciptakan lingkungan belajar yang bermakna dan penuh semangat.</p1>
        </div>

        <div class="container">
            <!-- Bagian Guru Biasa dan Kepala Sekolah -->
            <div class="row gy-4">
                <?php
                $counter = 0;
                while ($teacher = $guru->fetch_assoc()):
                    // Tentukan path foto
                    $photo_path = '';
                    
                    if (!empty($teacher['photo'])) {
                        $clean_path = str_replace('../../', '', $teacher['photo']);
                        $photo_path = '/SMPN 4 SBB/' . $clean_path;
                        
                        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $photo_path)) {
                            $photo_path = '';
                        }
                    }
                    
                    if (empty($photo_path)) {
                        $photo_path = '/SMPN 4 SBB/assets/img/doctors/' . 
                                     ($teacher['gender'] == 'female' ? 'female_user.png' : 'male user.webp');
                    }
                    ?>
                    <div class="col-lg-6" data-aos="fade-up" data-aos-delay="<?= ($counter % 2 == 0) ? '100' : '200' ?>">
                        <div class="team-member d-flex align-items-start">
                            <div class="pic">
                                <img src="<?= $photo_path ?>" 
                                     class="img-fluid" 
                                     alt="<?= htmlspecialchars($teacher['name']) ?>"
                                     style="width: 100px; height: 100px; object-fit: cover;">
                            </div>
                            <div class="member-info">
                                <h4><?= htmlspecialchars($teacher['name']) ?></h4>
                                <span><?= htmlspecialchars($teacher['position']) ?></span>
                                <?php if (!empty($teacher['subject'])): ?>
                                    <p1>Mata Pelajaran: <?= htmlspecialchars($teacher['subject']) ?></p1>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php 
                    $counter++;
                endwhile; 
                ?>
            </div>

            <div class="row mt-5">
                <div class="container section-title" data-aos="fade-up">
                    <h1>Pegawai Tata Usaha</h1>
                </div>

                <?php
                $counter = 0;
                while ($staff = $tata_usaha->fetch_assoc()):
                    // Tentukan path foto
                    $photo_path = '';

                    if (!empty($staff['photo'])) {
                        $clean_path = str_replace('../../', '', $staff['photo']);
                        $photo_path = '/SMPN 4 SBB/' . $clean_path;

                        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $photo_path)) {
                            $photo_path = '';
                        }
                    }

                    if (empty($photo_path)) {
                        $photo_path = '/SMPN 4 SBB/assets/img/doctors/' .
                                     ($staff['gender'] == 'female' ? 'female_user.png' : 'male user.webp');
                    }
                ?>
                    <div class="col-lg-6 gy-4" data-aos="fade-up" data-aos-delay="<?= ($counter % 2 == 0) ? '100' : '200' ?>">
                        <div class="team-member d-flex align-items-start">
                            <div class="pic">
                                <img src="<?= $photo_path ?>" 
                                     class="img-fluid" 
                                     alt="<?= htmlspecialchars($staff['name']) ?>"
                                     style="width: 100px; height: 100px; object-fit: cover;">
                            </div>
                            <div class="member-info">
                                <h4><?= htmlspecialchars($staff['name']) ?></h4>
                                <span><?= htmlspecialchars($staff['position']) ?></span>
                                <p1>Pegawai Tata Usaha</p1>
                            </div>
                        </div>
                    </div>
                <?php 
                    $counter++;
                endwhile; 
                ?>
            </div>
        </div>
    </section>

<footer style="text-align: center; padding: 40px 20px; background-color: #f1f6fb;">
  <h1>SMPN 4 Siborong-borong</h1>
  <p1>Jl. Siswa No.16, Siborong-borong<br>
     Kecamatan Siborong-borong</p1>
  <p1><strong>Phone:</strong> (021) 6620212</p1>
  <p1><strong>Email:</strong> smpnegeri4sbb@gmail.com</p1>

  <!-- Ikon Sosial Media -->
  <div style="display: flex; justify-content: center; gap: 15px; margin-top: 20px;">
    <a href="https://www.facebook.com/smpn4siborongborong?_rdc=1&_rdr#"><i class="bi bi-facebook" style="font-size: 24px;"></i></a>
    <a href="https://www.instagram.com/osis_smpn_4_sbb/"><i class="bi bi-instagram" style="font-size: 24px;"></i></a>
    <a href="https://www.youtube.com/@SMPNegeri4Siborongborong-tf2eo"><i class="bi bi-youtube" style="font-size: 24px;"></i></a>
  </div>

  <p1>&copy; SMP NEGERI 4 SIBORONG-BORONG</p1>
  <p1>Created by Institut Teknologi Del 2025</p1>
</footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/tes1.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>

  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>
