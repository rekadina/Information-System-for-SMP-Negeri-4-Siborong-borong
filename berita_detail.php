<?php
require_once 'db_config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = (int)$_GET['id'];
$stmt = $conn->prepare("SELECT * FROM news WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit();
}

$berita = $result->fetch_assoc();
$additional_images = json_decode($berita['additional_images'], true);

// Set judul halaman
$page_title = htmlspecialchars($berita['title']) . ' - SMPN4SBB'; 
require 'header.php'; // Hanya satu pemanggilan header.php
?>

<div class="berita-container">
    <div class="berita-header">
        <h1 class="berita-title"><?php echo htmlspecialchars($berita['title']); ?></h1>
        <div class="berita-meta">
            <span>Dipublikasikan: <?php echo date('d F Y', strtotime($berita['publish_date'])); ?></span> | 
            <span>Penulis: <?php echo htmlspecialchars($berita['author']); ?></span>
        </div>
    </div>

    <?php if (!empty($berita['image'])): ?>
        <img src="<?php echo $berita['image']; ?>" alt="<?php echo htmlspecialchars($berita['title']); ?>" class="berita-image">
    <?php endif; ?>

    <div class="berita-content">
        <?php echo nl2br(htmlspecialchars($berita['content'])); ?>
    </div>

    <?php if (!empty($additional_images) && is_array($additional_images)): ?>
        <h3>Galeri Foto</h3>
        <div class="gallery-container">
            <?php foreach ($additional_images as $image): ?>
                <?php if (file_exists($image)): ?>
                    <div class="gallery-item">
                        <img src="<?php echo $image; ?>" alt="Galeri Berita">
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($berita['video'])): ?>
        <div class="video-container">
            <h3>Video Terkait</h3>
            <video controls>
                <source src="<?php echo $berita['video']; ?>" type="video/mp4">
                Browser Anda tidak mendukung pemutar video.
            </video>
        </div>
    <?php endif; ?>

    <a href="index.php#News" class="back-button">Kembali ke Daftar Berita</a>
</div>

<?php require 'footer.php'; ?>