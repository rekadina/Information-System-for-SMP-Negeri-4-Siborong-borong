<?php
session_start();
require_once 'db_config.php'; // Pastikan file ini ada dan berisi koneksi database

$facilities = [];
$facilities_query = $conn->query("SELECT * FROM facilities ORDER BY name ASC");
if ($facilities_query) {
    while ($row = $facilities_query->fetch_assoc()) {
        $facilities[] = $row;
    }
}

// Inisialisasi variabel dengan nilai default
$counts = [
    'teachers' => 0,
    'students' => 0,
    'rooms' => 0,
    'subjects' => 0,
    'news' => 0,
    'facilities' => 0,
    'gallery' => 0
];

$student_count = 0;
$subject_count = 0;
$room_count = 0;


try {
    // Proses update data dengan prepared statement
    if(isset($_POST['update'])){
        $id = (int)$_POST['id'];
        $isi = $conn->real_escape_string($_POST['isi']);
        
        $query = "UPDATE konten SET isi=? WHERE id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $isi, $id);
        $stmt->execute();
        
        $_SESSION['success'] = "Data berhasil diupdate";
    }

      // Fungsi untuk menghitung data
    function countData($conn, $table) {
        $result = $conn->query("SELECT COUNT(*) as total FROM $table");
        return $result ? $result->fetch_assoc()['total'] : 0;
    }

  // Hitung total data untuk setiap tabel
    $tables = ['teachers', 'students', 'rooms', 'subjects', 'news', 'facilities', 'gallery'];
    foreach ($tables as $table) {
        $counts[$table] = countData($conn, $table);
    }

    // Hitung total ruangan
    $result = $conn->query("SELECT SUM(total) as total FROM rooms");
    if ($result && $row = $result->fetch_assoc()) {
        $room_count = $row['total'] ?? 0;
    }
    $rooms_count = $room_count; // Variabel yang digunakan di stats section
    
    // Ambil data siswa terakhir
    $current_year = date('Y');
    $stmt = $conn->prepare("SELECT total FROM students WHERE year = ? ORDER BY updated_at DESC LIMIT 1");
    $stmt->bind_param("i", $current_year);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $student_count = $row['total'];
    }

    // Ambil data mata pelajaran terakhir
    $stmt = $conn->prepare("SELECT total FROM subjects WHERE year = ? ORDER BY updated_at DESC LIMIT 1");
    $stmt->bind_param("i", $current_year);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $subject_count = $row['total'];
    }
  }

catch (Exception $e) {
    error_log("Error in dashboard: " . $e->getMessage());
    $_SESSION['error'] = "Terjadi kesalahan sistem";
}

$query_guru = "SELECT * FROM teachers WHERE is_tu = 0 ORDER BY position DESC, name ASC LIMIT 4";
$guru = $conn->query($query_guru);

$query_tata_usaha = "SELECT * FROM teachers WHERE is_tu = 1 ORDER BY name ASC";
$tata_usaha = $conn->query($query_tata_usaha); 	


// Ambil data galeri dari database
$query = "SELECT * FROM gallery ORDER BY upload_date DESC";
$gallery_result = $conn->query($query);

if (!$gallery_result) {
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

    <!-- Hero Section -->
    <section id="home" class="hero section light-background">

      <img src="13.jpg" alt="" width="100%" height="100%" data-aos="fade-in">
      
      <div class="overlay"></div>
      <div class="container position-relative">

        <div class="welcome position-relative" data-aos="fade-down" data-aos-delay="100">
          <h2>WELCOME TO SMPN 4 <br>SIBORONG BORONG</h2>
          <p>Pantun hangoluan Tois hamagoan</p>
        </div><!-- End Welcome -->

        <div class="content row gy-4">
          <div class="col-lg-4 d-flex align-items-stretch">
            <div class="why-box" data-aos="zoom-out" data-aos-delay="200">
              <h3>About</h3>
              <p>
                Arti dari Pantun hangoluan Tois hamagoan adalah Perlu sopan santun untuk bertahan hidup; sementara sombong akan membawa kepada celaka.<br>
                Yang mengandung pesan bahwa sikap sopan santun dan ramah diperlukan untuk bertahan hidup, sedangkan sombong, angkuh, dan pembangkang akan membawa kepada celaka. 
              </p>
              <div class="text-center">
                <a href="#about" class="more-btn"><span>Learn More</span> <i class="bi bi-chevron-right"></i></a>
              </div>
            </div>
          </div><!-- End Why Box -->  

          <div class="col-lg-8 d-flex align-items-stretch">
            <div class="d-flex flex-column justify-content-center">
              <div class="row gy-4">

                <div class="col-xl-4 d-flex align-items-stretch">
                  <div class="icon-box" data-aos="zoom-out" data-aos-delay="300">
                    <i class="bi bi-trophy"></i>
                    <h4>Berprestasi</h4>
                    <p>Berpatisipasi dalam ajang perlombaan baik Akademik maupun non akademik</p>
                  </div>
                </div><!-- End Icon Box -->

                <div class="col-xl-4 d-flex align-items-stretch">
                  <div class="icon-box" data-aos="zoom-out" data-aos-delay="400">
                    <i class="bi bi-heart"></i>
                    <h4>Berkarakter</h4>
                    <p>Menjunjung nilai nilai karakter yang dibawa oleh  siswa ke masa depan</p>
                  </div>
                </div><!-- End Icon Box -->

                <div class="col-xl-4 d-flex align-items-stretch">
                  <div class="icon-box" data-aos="zoom-out" data-aos-delay="500">
                    <i class="bi bi-bank"></i>
                    <h4>Berbudaya</h4>
                    <p>Mengharrgai serta eksplor budaya luar untuk masa depan siswa</p>
                  </div>
                </div><!-- End Icon Box -->

              </div>
            </div>
          </div>
        </div><!-- End  Content-->

      </div>

    </section><!-- /Hero Section -->

    <!-- About Section -->
    <section id="about" class="about section">

      <div class="container">

        <div class="row gy-4 gx-5">

          <div class="col-lg-6 position-relative align-self-start" data-aos="fade-up" data-aos-delay="200">
            <img src="assets/img/IMG_20250308_125040_890.jpg" class="img-fluid" alt="">
            <a href="https://youtu.be/nPcnSqqLmzM?si=Z8AKQeW6RRT1M2qn" class="glightbox pulsating-play-btn"></a>
          </div>

          <div class="col-lg-6 content" data-aos="fade-up" data-aos-delay="100">
            <h3>Visi & Misi</h3><br>
            <p1>Visi adalah gambaran masa depan yang ingin dicapai oleh suatu organisasi atau individu.  
              Misi adalah langkah-langkah atau upaya strategis untuk mewujudkan visi tersebut.  
              Keduanya menjadi panduan dalam perencanaan dan pelaksanaan kegiatan.</p1>
            <ul>
              <li>
                <i class="fa-solid fa-bullseye"></i>
                <div>
                  <h5>Visi</h5>
                  <p1>Unggul Dalam Berprestasi dan Taqwa terhadap Tuhan Yang Maha Esa</p1>
                </div>
              </li>
              <li>
                <i class="fa-solid fa-rocket"></i>
                <div>
                  <h5>Misi</h5>
                  <p1>Berupaya meningkatkan prestasi melalui memotivasi siswa agar menjadi siswa yang berrmutu dan dapat berpotensi dan berwawasan kebangsaaan serta beriman</p1>
                </div>
              </li>
              
            </ul>
          </div>

        </div>

      </div>

    </section><!-- /About Section -->

    <!-- Stats Section -->
    <section id="about" class="stats section light-background">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4">

          <div class="col-lg-3 col-md-6 d-flex flex-column align-items-center">
            <i class="fa-solid fa-chalkboard-teacher"></i>
            <div class="stats-item">
              <span data-purecounter-start="0" data-purecounter-end="<?php echo htmlspecialchars($counts['teachers']); ?>" data-purecounter-duration="1" class="purecounter"></span>
              <p1>Guru</p1>
            </div>
          </div><!-- End Stats Item -->

          <div class="col-lg-3 col-md-6 d-flex flex-column align-items-center">
            <i class="fa-regular fa-user"></i>
            <div class="stats-item">
             <span data-purecounter-start="0" data-purecounter-end="<?php echo htmlspecialchars($counts['students']); ?>" data-purecounter-duration="1" class="purecounter"></span>
              <p1>Siswa</p1>
            </div>
          </div><!-- End Stats Item -->

          <div class="col-lg-3 col-md-6 d-flex flex-column align-items-center">
            <i class="fas fa-building"></i>
            <div class="stats-item">
               <span data-purecounter-start="0" data-purecounter-end="<?php echo htmlspecialchars($room_count); ?>" data-purecounter-duration="1" class="purecounter"></span>
              <p1>Ruangan</p1>
            </div>
          </div><!-- End Stats Item -->

          <div class="col-lg-3 col-md-6 d-flex flex-column align-items-center">
            <i class="fas fa-book"></i>
            <div class="stats-item">
             <span data-purecounter-start="0" data-purecounter-end="<?php echo htmlspecialchars($counts['subjects']); ?>" data-purecounter-duration="1" class="purecounter"></span>
              <p1>Mata Pelajaran</p1>
            </div>
          </div><!-- End Stats Item -->

        </div>

      </div>

    </section><!-- /Stats Section -->

    <!-- Services Section -->
     
   <!-- News Section -->
<!-- News Section -->
<section id="News" class="services section">

    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
        <h1>News</h1>
        <p1>Dapatkan informasi seputar kegiatan sekolah, prestasi siswa, pengumuman penting, dan berbagai hal menarik lainnya! Jangan lewatkan kabar terbaru yang pastinya bermanfaat untuk siswa dan orang tua.</p1>
    </div><!-- End Section Title -->

    <div class="container">
        <div class="row">
            <?php
            // Ambil 3 berita terbaru dari database
            $news_query = $conn->query("SELECT id, title, excerpt, image FROM news ORDER BY publish_date DESC");
            
            if ($news_query && $news_query->num_rows > 0) {
                while ($row = $news_query->fetch_assoc()) {
                    echo '<div class="col-lg-4 col-md-6 mb-4">';
                    echo '<div class="news-card">';
                    echo '<a href="berita_detail.php?id='.$row['id'].'" class="stretched-link">';
                    
                    // Tampilkan gambar jika ada
                    if (!empty($row['image'])) {
                        echo '<img src="'.$row['image'].'" class="card-img-top" alt="'.htmlspecialchars($row['title']).'">';
                    } else {
                        echo '<div class="placeholder-image" style="height: 200px; background-color: #f5f5f5;"></div>';
                    }
                    
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">'.htmlspecialchars($row['title']).'</h5>';
                    echo '<p class="card-text">'.htmlspecialchars($row['excerpt']).'</p>';
                    echo '</div>';
                    echo '</a>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                // Tampilkan berita statis jika tidak ada data dari database
                echo '<div class="col-lg-4 col-md-6 mb-4">';
                echo '<div class="news-card">';
                echo '<a href="news1.html" class="stretched-link">';
                echo '<img src="assets/img/Terima kasih atas kepemimpinanmu selama periode 2024-2025 Nicolas Simanungkalit! Semoga sukses selalu!.jpg" class="card-img-top" alt="Pemilihan Kepengurusan OSIS">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">Pemilihan Kepengurusan OSIS SMPN 4 Siborong-Borong Tahun 2025/2026</h5>';
                echo '<p class="card-text">Siapa Calon Ketua OSIS Terfavorit? Intip Serunya Pemilihan di SMPN 4 Siborong-Borong 2025/2026</p>';
                echo '</div>';
                echo '</a>';
                echo '</div>';
                echo '</div>';
                
                echo '<div class="col-lg-4 col-md-6 mb-4">';
                echo '<div class="news-card">';
                echo '<a href="news2.html" class="stretched-link">';
                echo '<img src="assets/img/12.jpg" class="card-img-top" alt="Pusat Olimpiade Sains Indonesia">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">Tapanuli Utara Science Olympiad 2025</h5>';
                echo '<p class="card-text">Mereka Bikin Bangga! Yuk Baca Kisah 4 Siswa SMP Ini yang Bawa Pulang Medali dari Olimpiade Sains 2025!</p>';
                echo '</div>';
                echo '</a>';
                echo '</div>';
                echo '</div>';
                
                echo '<div class="col-lg-4 col-md-6 mb-4">';
                echo '<div class="news-card">';
                echo '<a href="news3.html" class="stretched-link">';
                echo '<img src="assets/img/osis cup.png" class="card-img-top" alt="Tapanuli Utara Science Olympiad 2023">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">OSIS CUP 2024</h5>';
                echo '<p class="card-text">Yuk, intip keseruan OSIS Cup SMP Negeri 4 Siborong-borong yang bikin heboh satu sekolah!</p>';
                echo '</div>';
                echo '</a>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</section><!-- /News Section --><!-- /News Section -->

<style>
.news-card {
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    height: 100%;
    position: relative;
    background: white;
    margin-bottom: 20px;
}

.news-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
}

.news-image-container {
    height: 200px;
    overflow: hidden;
}

.news-card img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-bottom: 1px solid #eee;
}

.news-card .card-body {
    padding: 20px;
}

.news-card .card-title {
    font-size: 1.1rem;
    margin-bottom: 10px;
    color: #333;
}

.news-card .card-text {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0;
}

.placeholder-image {
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
}

.stretched-link::after {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 1;
    content: "";
}

.news-card:hover .news-image {
    transform: scale(1.05);
}

.news-content {
    padding: 15px;
}

.news-title {
    font-size: 1.1rem;
    margin-bottom: 10px;
    color: #333;
}

.news-excerpt {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 15px;
}

.news-meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.8rem;
    color: #888;
}

@media (max-width: 992px) {
    .news-card {
        flex: 0 0 calc(50% - 15px);
    }
}

@media (max-width: 768px) {
    .news-card {
        flex: 0 0 100%;
    }
}
</style><!-- /Services Section -->
       

    <!-- Departments Section -->
<!-- Departments Section -->
<section id="Facility" class="departments section">
    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
        <h1>Facility</h1>
        <p1>Fasilitas yang ada cukup memadai untuk mendukung kegiatan belajar mengajar.</p1>
    </div><!-- End Section Title -->

    <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row">
            <?php if (!empty($facilities)): ?>
            <div class="col-lg-3">
                <ul class="nav nav-tabs flex-column">
                    <?php foreach ($facilities as $index => $facility): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= $index === 0 ? 'active show' : '' ?>" 
                               data-bs-toggle="tab" 
                               href="#facility-tab-<?= $facility['id'] ?>">
                                <?= htmlspecialchars($facility['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="col-lg-9 mt-4 mt-lg-0">
                <div class="tab-content">
                    <?php foreach ($facilities as $index => $facility): ?>
                        <div class="tab-pane <?= $index === 0 ? 'active show' : '' ?>" id="facility-tab-<?= $facility['id'] ?>">
                            <div class="row">
                                <div class="col-lg-8 details order-2 order-lg-1">
                                    <h3><?= htmlspecialchars($facility['name']) ?></h3>
                                    <?php if (!empty($facility['description'])): ?>
                                        <p class="fst-italic"><?= nl2br(htmlspecialchars($facility['description'])) ?></p>
                                    <?php endif; ?>
                                    <div class="tab-details">
                                        <?= $facility['tab_content'] ?>
                                    </div>
                                </div>
                                <div class="col-lg-4 text-center order-1 order-lg-2">
                                    <?php if (!empty($facility['image_thumbnail'])): ?>
                                        <img src="<?= $facility['image'] ?>" 
                                             alt="<?= htmlspecialchars($facility['name']) ?>" 
                                             class="img-fluid"
                                             style="width: 300px; height: 230px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="placeholder-image" style="width: 100%; height: 300px; background-color: #f5f5f5; display: flex; align-items: center; justify-content: center;">
                                            No Image
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p>Tidak ada data fasilitas yang tersedia.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section id="teacher" class="doctors section">
    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
        <h1>Tenaga Pendidik</h1>
        <p1>Terima kasih kepada para tenaga pendidik yang terus berjuang menciptakan lingkungan belajar yang bermakna dan penuh semangat.</p1>
    </div><!-- End Section Title -->

    <div class="container">
        <div class="row gy-4">
            <?php
            $counter = 0;
            while ($teacher = $guru->fetch_assoc()):
                // Tentukan path foto
                $photo_path = '';
                
                // Jika ada foto yang diupload
                if (!empty($teacher['photo'])) {
                    // Bersihkan path dari ../../ jika ada
                    $clean_path = str_replace('../../', '', $teacher['photo']);
                    $photo_path = '/SMPN 4 SBB/' . $clean_path;
                    
                    // Cek apakah file benar-benar ada
                    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $photo_path)) {
                        $photo_path = '';
                    }
                }
                
                // Jika tidak ada foto, gunakan default berdasarkan gender
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
                </div><!-- End Team Member -->
            <?php 
                $counter++;
            endwhile; 
            ?>
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="teacher.php" class="btn btn-see-more">See More</a>
    </div>
</section>

    <style>
.btn-see-more {
  background-color: #007bff;      /* Biru Bootstrap */
  color: #fff;
  font-weight: 600;
  padding: 10px 25px;
  border: none;
  border-radius: 30px;
  transition: all 0.3s ease;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn-see-more:hover {
  background-color: #0056b3;
  color: #fff;
  transform: scale(1.05);
  box-shadow: 0 6px 14px rgba(0,0,0,0.15);
}
</style>


    <!-- Faq Section -->
    <section id="faq" class="faq section light-background">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h1>Pertanyaan yang sering di tanyakan</h1>
        <h5>Beberapa pertanyaan ini sering di tanyakan oleh beberapa masyarakat yang ingin bersekolah di SMP Negeri</h5>
      </div><!-- End Section Title -->

      <div class="container">

        <div class="row justify-content-center">

          <div class="col-lg-10" data-aos="fade-up" data-aos-delay="100">

            <div class="faq-container">

              <div class="faq-item faq-active">
                <h3>Kapan waktu pendaftaran siswa baru dibuka?</h3>
                <div class="faq-content">
                <p>Pendaftaran siswa baru biasanya dibuka pada bulan Juni - Juli setiap tahunnya, mengikuti jadwal dari Dinas Pendidikan 
                  setempat. Informasi resmi akan diumumkan melalui website sekolah dan papan pengumuman.</div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div><!-- End Faq item-->

              <div class="faq-item">
                <h3> Apakah SMP Negeri 4 menerima siswa pindahan?</h3>
                <div class="faq-content">
                  <p>Ya, SMP Negeri 4 menerima siswa pindahan selama ada kuota di kelas yang dituju dan memenuhi syarat administrasi serta hasil asesmen penempatan.

                  </p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div><!-- End Faq item-->

              <div class="faq-item">
                <h3>Apa saja ekstrakulikuler di SMP Negeri 4 Siborong-borong?</h3>
                <div class="faq-content">
                  <p>Ya,Di sekolah SMP Negeri 4 Siborong-Borong terdapat beberapa ekstrakulikuler yaitu Pramuka dan juga ada ekstrakulikuler di bagian olahraga yaitu Voli, Bulu tangkis, Futsal Dan Senam.</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div><!-- End Faq item-->

              <div class="faq-item">
                <h3>Apakah siswa dan siswi di SMP Negeri 4 Siborong-borong bayar uang sekolah/spp?</h3>
                <div class="faq-content">
                  <p>Untuk biaya spp atau uang sekolah di SMP Negeri 4 Siborong-Borong yaitu gratis, karena sudah dibiayai oleh pemerintah.Seperti dana dari BOS (Bantuan Operasional Sekolah) dan semacamnya.</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div><!-- End Faq item-->

              <div class="faq-item">
                <h3>Bagaimana sistem nilai atau penilaiannya?</h3>
                <div class="faq-content">
                  <p>Penilaian dilakukan melalui berbagai cara seperti ulangan harian, tugas, proyek, ujian tengah/akhir semester, dan sikap atau partisipasi. Nilai ditampilkan dalam raport kurikulum merdeka</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div><!-- End Faq item-->

              <div class="faq-item">
                <h3>Apakah sekolah ini menggunakan sistem zonasi?</h3>
                <div class="faq-content">
                  <p>Sistem zonasi pada umumnya di terapkan di sekolah SD,SMP dan SMA di indonesia.Di SMP Negeri 4 Siborong-Borong saat penerimaan siswa baru di tahun ajaran 2024/2025 menggunakan sistem zonasi.Namun untuk 
                    penerimaan siswa tahun ajaran 2025/2026 sudah tidak berlaku lagi menggunakan sistem zonasi.Dikarenakan banyak faktor yang mempertimbangkan untuk sistem zonasi ini di tiadakan.Contohnya seperti menimbulkan kecurangan administrasi.
                  </p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div><!-- End Faq item-->

            </div>

          </div><!-- End Faq Column-->

        </div>

      </div>

    </section><!-- /Faq Section -->

    <!-- Testimonials Section -->
    <section id="testimonials" class="testimonials section">

      <div class="container">

        <div class="row align-items-center">

          <div class="col-lg-5 info" data-aos="fade-up" data-aos-delay="100">
            <h3>Inspirasi Pendidikan</h3>
            <p1>
              Pendidikan adalah cahaya yang menerangi perjalanan hidup. Melalui kata-kata inspiratif dari para pahlawan dan tokoh pendidikan, kita dapat memahami betapa pentingnya ilmu pengetahuan, semangat belajar, dan ketekunan dalam meraih impian.
            </p1>
          </div>

          <div class="col-lg-7" data-aos="fade-up" data-aos-delay="200">

            <div class="swiper init-swiper">
              <script type="application/json" class="swiper-config">
                {
                  "loop": true,
                  "speed": 600,
                  "autoplay": {
                    "delay": 5000
                  },
                  "slidesPerView": "auto",
                  "pagination": {
                    "el": ".swiper-pagination",
                    "type": "bullets",
                    "clickable": true
                  }
                }
              </script>
              <div class="swiper-wrapper">

                <div class="swiper-slide">
                  <div class="testimonial-item">
                    <div class="d-flex">
                      <img src="assets/img/Ki_Hadjar_Dewantara.jpg" class="testimonial-img flex-shrink-0" alt="">
                      <div>
                        <h3>Ki Hajar Dewantara</h3>
                        <h4>Pelopor Pendidikan </h4>
                        <div class="stars">
                          <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        </div>
                      </div>
                    </div>
                    <p1>
                      <i class="bi bi-quote quote-icon-left"></i>
                      <span>Pendidikan itu menuntun segala kekuatan kodrat yang ada pada anak-anak, agar mereka sebagai manusia dan sebagai anggota masyarakat dapat mencapai keselamatan dan kebahagiaan yang setinggi-tingginya.</span>
                      <i class="bi bi-quote quote-icon-right"></i>
                    </p1>
                  </div>
                </div><!-- End testimonial item -->

                <div class="swiper-slide">
                  <div class="testimonial-item">
                    <div class="d-flex">
                      <img src="assets/img/nelson mandela.jpg" class="testimonial-img flex-shrink-0" alt="">
                      <div>
                        <h3>Nelson Mandela</h3>
                        <h4>Pejuang Pendidikan Dan Kesetaraan</h4>
                        <div class="stars">
                          <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        </div>
                      </div>
                    </div>
                    <p1>
                      <i class="bi bi-quote quote-icon-left"></i>
                      <span>Education is the most powerful weapon which you can use to change the world.<br>
                        (Pendidikan adalah senjata paling ampuh yang dapat kamu gunakan untuk mengubah dunia.)</span>
                      <i class="bi bi-quote quote-icon-right"></i>
                    </p1>
                  </div>
                </div><!-- End testimonial item -->

                <div class="swiper-slide">
                  <div class="testimonial-item">
                    <div class="d-flex">
                      <img src="assets/img/moh hatta.jpg" class="testimonial-img flex-shrink-0" alt="">
                      <div>
                        <h3>Mohammad Hatta</h3>
                        <h4>Wakli Presiden Pertama</h4>
                        <div class="stars">
                          <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        </div>
                      </div>
                    </div>
                    <p1>
                      <i class="bi bi-quote quote-icon-left"></i>
                      <span>Pendidikan itu bukan hanya untuk membuat manusia menjadi pintar, tetapi juga untuk membuat mereka bijaksana.</span>
                      <i class="bi bi-quote quote-icon-right"></i>
                    </p1>
                  </div>
                </div><!-- End testimonial item -->

                <div class="swiper-slide">
                  <div class="testimonial-item">
                    <div class="d-flex">
                      <img src="assets/img/malcom x.jpg" class="testimonial-img flex-shrink-0" alt="">
                      <div>
                        <h3>Malcom X</h3>
                        <h4>Pejuang Hak Asasi Manusia Dan Pendidikan</h4>
                        <div class="stars">
                          <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        </div>
                      </div>
                    </div>
                    <p1>
                      <i class="bi bi-quote quote-icon-left"></i>
                      <span>Education is the passport to the future, for tomorrow belongs to those who prepare for it today.<br>(Pendidikan adalah paspor menuju masa depan, karena hari esok adalah milik mereka yang mempersiapkannya hari ini)</span>
                      <i class="bi bi-quote quote-icon-right"></i>
                    </p1>
                  </div>
                </div><!-- End testimonial item -->

                <div class="swiper-slide">
                  <div class="testimonial-item">
                    <div class="d-flex">
                      <img src="assets/img/John Dewey.jpg " class="testimonial-img flex-shrink-0" alt="">
                      <div>
                        <h3>John Dewey</h3>
                        <h4> kritikus Sosial Tentang pendidikan</h4>
                        <div class="stars">
                          <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                        </div>
                      </div>
                    </div>
                    <p1>
                      <i class="bi bi-quote quote-icon-left"></i>
                      <span>Education is not preparation for life education is life it self.<br>
                        (Pendidikan bukan persiapan untuk hidup; pendidikan adalah hidup itu sendiri).</span>
                      <i class="bi bi-quote quote-icon-right"></i>
                    </p1>
                  </div>
                </div><!-- End testimonial item -->

              </div>
              <div class="swiper-pagination"></div>
            </div>

          </div>

        </div>

      </div>

    </section>

<!-- Gallery Section -->
<section id="gallery" class="gallery section">
    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
        <h1>Gallery</h1>
        <p1>Temukan keajaiban visual dalam setiap gambar di galeri kami.</p1>
    </div><!-- End Section Title -->
    <div class="container">
        <div class="row gy-4">
            <?php
            if ($gallery_result && $gallery_result->num_rows > 0) {
                while ($row = $gallery_result->fetch_assoc()) {
                    echo '<div class="col-xl-3 col-lg-4 col-md-6">'; // Perubahan utama di sini
                    echo '<div class="gallery-item">';
                    echo '<a href="' . htmlspecialchars($row['image']) . '" class="glightbox">';
                    echo '<img src="' . htmlspecialchars($row['image']) . '" class="img-fluid" alt="' . htmlspecialchars($row['title']) . '">';
                    echo '</a>';
                    echo '<div class="gallery-info">';
                    echo '<h4>' . htmlspecialchars($row['title']) . '</h4>';
                    if (!empty($row['description'])) {
                        echo '<p>' . htmlspecialchars($row['description']) . '</p>';
                    }
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="col-12 text-center">';
                echo '<p>Tidak ada data gallery yang tersedia.</p>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</section><!-- End Gallery Section -->

<style>
.gallery-item {
    position: relative;
    overflow: hidden;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    height: 100%;
    margin-bottom: 20px;
}

.gallery-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15);
}

.gallery-item img {
    width: 100%;
    height: 200px; /* Sesuaikan tinggi gambar */
    object-fit: cover;
    transition: transform 0.3s ease;
}

.gallery-item:hover img {
    transform: scale(1.05);
}

.gallery-info {
    padding: 15px;
    background: white;
}

.gallery-info h4 {
    font-size: 1rem;
    margin-bottom: 5px;
    color: #333;
}

.gallery-info p {
    color: #666;
    font-size: 0.8rem;
    margin-bottom: 0;
}

/* Responsive adjustments */
@media (max-width: 1200px) {
    .col-xl-3 {
        flex: 0 0 25%;
        max-width: 25%;
    }
}

@media (max-width: 992px) {
    .col-lg-4 {
        flex: 0 0 33.333%;
        max-width: 33.333%;
    }
}

@media (max-width: 768px) {
    .col-md-6 {
        flex: 0 0 50%;
        max-width: 50%;
    }
}

@media (max-width: 576px) {
    .gallery-item {
        flex: 0 0 100%;
        max-width: 100%;
    }
}
</style>

    <!-- Contact Section -->
    <section id="contact" class="contact section">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h1>Contact</h1>
        <p1>Temukan lokasi kami dengan mudah di peta di bawah ini. Jika Anda memiliki pertanyaan atau membutuhkan bantuan, jangan ragu untuk menghubungi kami!</p1>
      </div>

      <div class="container1">
      <div class="map">
      <div class="mb-5" data-aos="fade-up" data-aos-delay="200">
        <iframe style="border-radius:16px; width: 100%; height: 50vh;" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3986.845792302825!2d98.96635607472673!3d2.2118262977686176!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x302e119e1f20a293%3A0xce191c5e079a52a1!2sSMPN%204%20Siborongborong!5e0!3m2!1sid!2sid!4v1740708617122!5m2!1sid!2sid" frameborder="0" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      </div>
      </div>
      </div>

      <style>
        .container1 {
        display: flex;
        flex-direction: column;
        gap: 20px;
        padding: 20px;
        }
      </style>
       <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4">

          <div class="col-lg-4">
            <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="300">
              <i class="bi bi-geo-alt flex-shrink-0"></i>
              <div>
                <h3>Location</h3>
                <p1>Jl.Siswa No.16,Siborong-borong l Kecamatan Siborong-borong</p1>
              </div>
            </div><!-- End Info Item -->

            <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="400">
              <i class="bi bi-telephone flex-shrink-0"></i>
              <div>
                <h3>Call Us</h3>
                <p1>(021) 6620212</p1>
              </div>
            </div><!-- End Info Item -->

            <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="500">
              <i class="bi bi-envelope flex-shrink-0"></i>
              <div>
                <h3>Email Us</h3>
                <p1>smpnegeri4sbb@gmail.com</p1>
              </div>
            </div><!-- End Info Item -->

          </div>

          <div class="col-lg-8">
            <form action="forms/messaege.php" method="post" class="php-email-form" data-aos="fade-up" data-aos-delay="200">
              <div class="row gy-4">

                <div class="col-md-6">
                  <input type="text" name="name" class="form-control" placeholder="Your Name" required="">
                </div>

                <div class="col-md-6 ">
                  <input type="email" class="form-control" name="email" placeholder="Your Email" required="">
                </div>

                <div class="col-md-12">
                  <input type="text" class="form-control" name="subject" placeholder="Subject" required="">
                </div>

                <div class="col-md-12">
                  <textarea class="form-control" name="message" rows="6" placeholder="Message" required=""></textarea>
                </div>

                <div class="col-md-12 text-center">
                  <div class="loading">Loading</div>
                  <div class="error-message"></div>
                  <div class="sent-message">Your message has been sent. Thank you!</div>

                  <button type="submit">Send Message</button>
                </div>

              </div>
            </form>
          </div><!-- End Contact Form -->
        </div>
      </div>
    </section><!-- /Contact Section -->

  </main>

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