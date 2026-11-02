<?php
require_once 'db_config.php';

$news_query = $conn->query("SELECT id, title, excerpt, image, publish_date, author FROM news ORDER BY publish_date DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Berita - SMPN4SBB</title>
    <!-- Gunakan CSS yang sama dengan index.php -->
    <link href="assets/css/main.css" rel="stylesheet">
    <style>
        .all-news-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .page-title {
            text-align: center;
            margin-bottom: 30px;
        }
        .news-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
        }
        .news-item {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .news-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        .news-image-container {
            height: 200px;
            overflow: hidden;
        }
        .news-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .news-item:hover .news-image {
            transform: scale(1.05);
        }
        .news-content {
            padding: 20px;
        }
        .news-title {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #333;
        }
        .news-excerpt {
            color: #666;
            margin-bottom: 15px;
        }
        .news-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: #888;
        }
        .read-more {
            display: inline-block;
            margin-top: 10px;
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
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
<body>
    <?php include 'header.php'; ?>

    <main class="main">
        <div class="all-news-container">
            <h1 class="page-title">Semua Berita</h1>
            
            <div class="news-grid">
                <?php
                if ($news_query && $news_query->num_rows > 0) {
                    while ($row = $news_query->fetch_assoc()) {
                        echo '<div class="news-item">';
                        echo '<a href="berita_detail.php?id='.$row['id'].'" class="text-decoration-none">';
                        
                        // Tampilkan gambar jika ada
                        if (!empty($row['image']) && file_exists($row['image'])) {
                            echo '<div class="news-image-container">';
                            echo '<img src="'.$row['image'].'" class="news-image" alt="'.htmlspecialchars($row['title']).'">';
                            echo '</div>';
                        } else {
                            echo '<div class="news-image-container" style="background-color: #f5f5f5; display: flex; align-items: center; justify-content: center;">';
                            echo '<i class="fas fa-image" style="font-size: 50px; color: #ccc;"></i>';
                            echo '</div>';
                        }
                        
                        echo '<div class="news-content">';
                        echo '<h3 class="news-title">'.htmlspecialchars($row['title']).'</h3>';
                        
                        // Gunakan excerpt jika ada, jika tidak buat ringkasan
                        $excerpt = !empty($row['excerpt']) ? $row['excerpt'] : 'Yuk simak berita selengkapnya...';
                        echo '<p class="news-excerpt">'.htmlspecialchars($excerpt).'</p>';
                        
                        echo '<div class="news-meta">';
                        echo '<span>'.date('d M Y', strtotime($row['publish_date'])).'</span>';
                        echo '<span>'.htmlspecialchars($row['author']).'</span>';
                        echo '</div>';
                        
                        echo '<span class="read-more">Baca Selengkapnya →</span>';
                        echo '</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="col-12"><div class="alert alert-info">Tidak ada berita tersedia saat ini.</div></div>';
                }
                ?>
            </div>
            
            <div class="text-center">
                <a href="index.php#News" class="back-button">Kembali ke Beranda</a>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>