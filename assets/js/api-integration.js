// Fungsi untuk mengambil data dari API
async function fetchData(endpoint, params = {}) {
    const baseUrl = 'https://admin.smpn4sbb.com/api.php'; // Ganti dengan URL admin panel Anda
    const queryString = new URLSearchParams({endpoint, ...params}).toString();
    const response = await fetch(`${baseUrl}?${queryString}`);
    return await response.json();
}

// Contoh penggunaan:
// 1. Untuk menampilkan daftar guru
async function loadTeachers() {
    const teachers = await fetchData('teachers');
    const container = document.getElementById('teachers-container');
    
    teachers.forEach(teacher => {
        container.innerHTML += `
            <div class="teacher-card">
                <img src="${teacher.photo || 'default-teacher.jpg'}" alt="${teacher.name}">
                <h3>${teacher.name}</h3>
                <p>${teacher.position}</p>
                <p>${teacher.subject}</p>
            </div>
        `;
    });
}

// 2. Untuk menampilkan jumlah siswa
async function updateStudentCount() {
    const data = await fetchData('students_count');
    document.getElementById('student-count').textContent = data.count;
}

// 3. Untuk menampilkan jumlah ruangan
async function updateRoomCount() {
    const data = await fetchData('rooms_count');
    document.getElementById('room-count').textContent = data.count;
}

// 4. Untuk menampilkan jumlah mata pelajaran
async function updateSubjectCount() {
    const data = await fetchData('subjects_count');
    document.getElementById('subject-count').textContent = data.count;
}

// 5. Untuk menampilkan galeri
async function loadGallery() {
    const galleryItems = await fetchData('gallery');
    const container = document.getElementById('gallery-container');
    
    galleryItems.forEach(item => {
        container.innerHTML += `
            <div class="gallery-item">
                <img src="${item.image}" alt="${item.title}">
                <h4>${item.title}</h4>
            </div>
        `;
    });
}

// 6. Untuk menampilkan berita terbaru
async function loadLatestNews() {
    const news = await fetchData('news', {limit: 3});
    const container = document.getElementById('news-container');
    
    news.forEach(item => {
        container.innerHTML += `
            <div class="news-item">
                <img src="${item.image}" alt="${item.title}">
                <h3>${item.title}</h3>
                <p>${item.content.substring(0, 100)}...</p>
                <small>${new Date(item.publish_date).toLocaleDateString()}</small>
            </div>
        `;
    });
}

// Panggil fungsi-fungsi di atas saat halaman dimuat
document.addEventListener('DOMContentLoaded', () => {
    loadTeachers();
    updateStudentCount();
    updateRoomCount();
    updateSubjectCount();
    loadLatestNews();
    loadGallery();
    // Tambahkan pemanggilan fungsi lainnya sesuai kebutuhan
});
