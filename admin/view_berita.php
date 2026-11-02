<?php
session_start();
require_once 'db_config.php';

// Ambil data berita berdasarkan ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    header('Location: index.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM news WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$berita = $result->fetch_assoc();

if (!$berita) {
    header('Location: index.php');
    exit();
}

$additional_images = json_decode($berita['additional_images'], true);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($berita['title']); ?> - SMPN4SBB</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
        .news-title { color: rgb(8, 29, 123); margin-bottom: 10px; }
        .news-meta { color: #666; margin-bottom: 20px; font-size: 0.9em; }
        .news-image { max-width: 100%; height: auto; margin: 20px 0; }
        .news-content { margin-bottom: 30px; }
        .additional-images { display: flex; flex-wrap: wrap; gap: 10px; margin: 20px 0; }
        .additional-image { max-width: 200px; max-height: 200px; }
        .video-container { margin: 20px 0; }
        .back-link { display: inline-block; margin-top: 20px; color: rgb(8, 29, 123); text-decoration: none; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1 class="news-title"><?php echo htmlspecialchars($berita['title']); ?></h1>
    <div class="news-meta">
        Diposting pada: <?php echo date('d/m/Y', strtotime($berita['publish_date'])); ?> | 
        Penulis: <?php echo htmlspecialchars($berita['author']); ?>
    </div>
    
    <?php if ($berita['image']): ?>
        <img src="<?php echo $berita['image']; ?>" alt="<?php echo htmlspecialchars($berita['title']); ?>" class="news-image">
    <?php endif; ?>
    
    <div class="news-content">
        <?php echo nl2br(htmlspecialchars($berita['content'])); ?>
    </div>
    
    <?php if (!empty($additional_images)): ?>
        <h3>Galeri Foto</h3>
        <div class="additional-images">
            <?php foreach ($additional_images as $img): ?>
                <?php if (file_exists($img)): ?>
                    <img src="<?php echo $img; ?>" alt="Foto Berita" class="additional-image">
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($berita['video']): ?>
        <div class="video-container">
            <h3>Video</h3>
            <video width="100%" controls>
                <source src="<?php echo $berita['video']; ?>" type="video/mp4">
                Browser Anda tidak mendukung pemutar video.
            </video>
        </div>
    <?php endif; ?>
    
    <a href="berita.php" class="back-link">← Kembali ke Daftar Berita</a>
</body>
</html>