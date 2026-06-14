<?php
session_start();
include "koneksi.php";



// Deteksi status login untuk menentukan tombol dan tujuan arah (redirect)
$is_logged_in = false;
$link_tujuan = "login.php"; // Default jika belum login
$q_riwayat = null;

if (isset($_SESSION['kode']) || isset($_SESSION['id_karyawan'])) {
    // Jika Karyawan/Admin yang login
    $is_logged_in = true;
    $link_tujuan = "home/index.php"; 
} elseif (isset($_SESSION['id_customer'])) {
    // Jika Customer yang login
    $is_logged_in = true;
    $link_tujuan = "customer/booking.php"; 
    
    // Ambil data riwayat pesanan singkat untuk landing page
    $id_customer = $_SESSION['id_customer'];
    $q_riwayat = mysqli_query($conn, "
        SELECT p.*, k.merk, k.tipe, k.jenis 
        FROM pemesanan p 
        LEFT JOIN kendaraan k ON p.kendaraan_id = k.id 
        WHERE p.customer_id = '$id_customer' 
        ORDER BY p.tanggal DESC 
        LIMIT 3
    ");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BengCare – Servis Motor Tepat Waktu & Terpercaya</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            
            background: linear-gradient(180deg, #6BA9FC 0%, #D1D6F1 100%);
            background-attachment: fixed;
            background-repeat: no-repeat;
            min-height: 100vh;
        }
        
        h1, h2, h3, h4, h5, h6, .navbar-brand { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        .text-blue { color: #255498; }
        .bg-blue-light { background-color: #BDD9FF; }
        
        .btn-primary-custom { 
            background-color: #255498; 
            color: white; 
            border: 1.5px solid #255498; 
            border-radius: 12px !important; 
            font-weight: 600; 
            padding: 10px 24px;
        }
        .btn-primary-custom:hover { 
            background-color: #1a3c6d; 
            border-color: #1a3c6d; 
            color: white; 
        }
        .btn-outline-custom { 
            border: 1.5px solid #255498; 
            color: #255498; 
            background: transparent; 
            border-radius: 12px !important; 
            font-weight: 600; 
            padding: 10px 24px;
        }
        .btn-outline-custom:hover, .btn-outline-custom.active { 
            background-color: #255498; 
            border-color: #255498; 
            color: white; 
        }
        
        .fade-up { opacity: 0; transform: translateY(25px); transition: opacity 0.6s ease-out, transform 0.6s ease-out; }
        .fade-up.visible { opacity: 1; transform: translateY(0); }
        
        .feature-icon-box {
            width: 60px; height: 60px;
            background: #BDD9FF; color: #255498;
            border-radius: 12px; display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; margin-bottom: 1rem;
        }
        
        .service-card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .service-card:hover { transform: translateY(-5px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-md bg-white shadow-sm sticky-top py-3">
        <div class="container-xl">
            <a class="navbar-brand fw-bold text-blue fs-4" href="#">
                <img src="assets/logo.png" alt="Logo BengCare" height="30"></i> BengCare
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav mx-auto gap-md-4 fw-medium text-secondary">
                    <li class="nav-item"><a class="nav-link text-dark" href="#kenapa">Tentang Kami</a></li>
                    <li class="nav-item"><a class="nav-link text-dark" href="#booking">Booking</a></li>
                </ul>
                
                <div class="d-flex gap-2 mt-3 mt-md-0">
                    <?php if ($is_logged_in): ?>
                        <a href="<?= $link_tujuan ?>" class="btn btn-primary-custom rounded-3 px-4 fw-medium">Menu Booking</a>
                        <a href="logout.php" class="btn btn-outline-danger rounded-3 px-4 fw-medium">Keluar</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-custom rounded-3 px-4 fw-medium">Masuk</a>
                        <a href="register.php" class="btn btn-primary-custom rounded-3 px-4 fw-medium">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <section id="hero" class="py-5 mb-5">
        <div class="container-xl py-4">
            <div class="row align-items-center g-5">
                <div class="col-12 col-md-6 fade-up">
                    <h1 class="display-4 fw-bolder mb-3" style="color: #112A46;">
                        Servis <em class="text-blue" style="font-style: normal;">Motormu,</em><br>
                        Tepat Waktu & Terpercaya
                    </h1>
                    <p class="text-muted mb-4 fs-5 lh-base">
                        Bengcare menghadirkan pengalaman servis motor yang profesional, transparan, dan efisien layaknya klinik presisi. Nikmati kemudahan booking dan pantau prosesnya secara real-time.
                    </p>
                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a href="<?= $link_tujuan ?>" class="btn btn-primary-custom btn-lg rounded-3 px-4 fs-6">
                            <i class="bi bi-wrench-adjustable me-2"></i> Pesan Sekarang
                        </a>
                    </div>
                    
                    <div class="row mt-5 text-center text-md-start">
                        <div class="col-4 border-end">
                            <h3 class="fw-bold mb-0 text-blue">500+</h3>
                            <small class="text-muted">Pelanggan Puas</small>
                        </div>
                        <div class="col-4 border-end">
                            <h3 class="fw-bold mb-0 text-blue">3+</h3>
                            <small class="text-muted">Tahun Pengalaman</small>
                        </div>
                        <div class="col-4">
                            <h3 class="fw-bold mb-0 text-blue">4.9</h3>
                            <small class="text-muted">Rating Layanan</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-12 col-md-6 fade-up" style="transition-delay:.15s">
                    <div class="card border-0 mx-auto" style="background-color: #ffffff; border-radius: 20px; max-width: 420px; box-shadow: 0 15px 35px rgba(37, 84, 152, 0.15);">
                        <div class="card-body p-4 text-start">
                    
                            <?php if (isset($_SESSION['id_customer']) && $q_riwayat !== null): ?>
                                <!-- JIKA CUSTOMER SUDAH LOGIN: Tampilkan Riwayat Servis Singkat -->
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="fw-bold mb-0" style="color: #112A46; font-family: 'Plus Jakarta Sans', sans-serif;">Riwayat Servis Anda</h5>
                                    <a href="customer/riwayat.php" class="text-blue text-decoration-none fw-bold small">Lihat Semua</a>
                                </div>

                                <?php if (mysqli_num_rows($q_riwayat) > 0): ?>
                                    <?php while($row = mysqli_fetch_assoc($q_riwayat)): ?>
                                        <?php
                                            $badge_class = match($row['status']) {
                                                'selesai' => 'bg-success',
                                                'batal'   => 'bg-danger',
                                                default   => 'bg-warning text-dark',
                                            };
                                            $status_label = match($row['status']) {
                                                'selesai' => 'Selesai',
                                                'batal'   => 'Batal',
                                                default   => 'Antrian',
                                            };
                                            $icon = (strtolower($row['jenis'] ?? '') === 'mobil') ? 'bi-car-front' : 'bi-bicycle';
                                        ?>
                                        <div class="d-flex align-items-center p-3 mb-3" style="background-color: #F4F8FF; border: 1px solid #E2EAF4; border-radius: 16px;">
                                            <div class="d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; background-color: #BDD9FF; border-radius: 12px; color: #255498;">
                                                <i class="bi <?= $icon ?> fs-5"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <h6 class="fw-bold mb-1 text-truncate" style="color: #112A46; max-width: 160px;"><?= htmlspecialchars($row['merk'] . ' ' . $row['tipe']) ?></h6>
                                                <span class="small text-muted"><?= date('d M Y', strtotime($row['tanggal'])) ?></span>
                                            </div>
                                            <span class="badge <?= $badge_class ?> rounded-pill px-2.5 py-1.5 small"><?= $status_label ?></span>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-4 text-muted">
                                        <i class="bi bi-clock-history d-block fs-1 mb-2" style="opacity: 0.4;"></i>
                                        Belum ada riwayat pesanan servis.
                                    </div>
                                <?php endif; ?>

                                <a href="customer/booking.php" class="btn w-100 fw-bold py-3 mt-2" style="background-color: #255498; color: white; border-radius: 12px; font-family: 'Plus Jakarta Sans', sans-serif; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#1a3c6d'" onmouseout="this.style.backgroundColor='#255498'">
                                    Buat Booking Baru &rarr;
                                </a>

                            <?php else: ?>
                                <!-- JIKA BELUM LOGIN / STAFF: Tampilkan Layanan Populer -->
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="fw-bold mb-0" style="color: #112A46; font-family: 'Plus Jakarta Sans', sans-serif;">Layanan Populer</h5>
                                </div>

                                <div class="d-flex align-items-center p-3 mb-3" style="background-color: #F4F8FF; border: 1px solid #E2EAF4; border-radius: 16px; transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                                    <div class="d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; background-color: #BDD9FF; border-radius: 12px;">
                                        <i class="bi bi-droplet-half fs-5" style="color: #255498;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1" style="color: #112A46;">Ganti Oli Mesin</h6>
                                        <span class="fw-bold" style="color: #112A46;">Rp 75.000</span>
                                    </div>
                                    <div class="text-muted small fw-medium">30 menit</div>
                                </div>

                                <div class="d-flex align-items-center p-3 mb-3" style="background-color: #F4F8FF; border: 1px solid #E2EAF4; border-radius: 16px; transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                                    <div class="d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; background-color: #BDD9FF; border-radius: 12px;">
                                        <i class="bi bi-gear-wide-connected fs-5" style="color: #255498;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1" style="color: #112A46;">Tune Up Motor</h6>
                                        <span class="fw-bold" style="color: #112A46;">Rp 100.000</span>
                                    </div>
                                    <div class="text-muted small fw-medium">1 jam</div>
                                </div>

                                <div class="d-flex align-items-center p-3 mb-4" style="background-color: #F4F8FF; border: 1px solid #E2EAF4; border-radius: 16px; transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                                    <div class="d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px; background-color: #BDD9FF; border-radius: 12px;">
                                        <i class="bi bi-record-circle fs-5" style="color: #255498;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold mb-1" style="color: #112A46;">Ganti Ban Motor</h6>
                                        <span class="fw-bold" style="color: #112A46;">Rp 150.000</span>
                                    </div>
                                    <div class="text-muted small fw-medium">30 menit</div>
                                </div>

                                <a href="<?= $link_tujuan ?>" class="btn w-100 fw-bold py-3" style="background-color: #255498; color: white; border-radius: 12px; font-family: 'Plus Jakarta Sans', sans-serif; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#1a3c6d'" onmouseout="this.style.backgroundColor='#255498'">
                                    Mulai Pesan &rarr;
                                </a>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <section id="kenapa" class="py-5" style="background-color: transparent !important;">
    <!-- Ini kuncinya: Container diubah jadi kotak putih mengambang -->
        <div class="container bg-white rounded-4 shadow-sm p-4 p-md-5">
            <div class="text-center mb-5 fade-up">
                <span class="text-blue fw-bold text-uppercase tracking-wider small">Kenapa BengCare?</span>
                <h2 class="fw-bold mt-2">Kami Berbeda <span class="text-blue">Karena</span></h2>
            </div>
            <div class="row g-4 justify-content-center">

                <div class="col-12 col-sm-6 col-lg-3 fade-up" style="transition-delay:.05s">
                    <div class="card border-0 bg-transparent h-100">
                        <div class="feature-icon-box"><i class="bi bi-award"></i></div>
                        <h5 class="fw-bold">Mekanik Bersertifikat</h5>
                        <p class="text-muted small">Tim kami memiliki sertifikasi resmi dan pengalaman lebih dari 5 tahun di bidangnya.</p>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3 fade-up" style="transition-delay:.15s">
                    <div class="card border-0 bg-transparent h-100">
                        <div class="feature-icon-box"><i class="bi bi-nut"></i></div>
                        <h5 class="fw-bold">Spare Part Original</h5>
                        <p class="text-muted small">Hanya menggunakan spare part original bergaransi untuk menjaga performa kendaraan.</p>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-lg-3 fade-up" style="transition-delay:.25s">
                    <div class="card border-0 bg-transparent h-100">
                        <div class="feature-icon-box"><i class="bi bi-phone"></i></div>
                        <h5 class="fw-bold">Booking Online</h5>
                        <p class="text-muted small">Pesan servis kapan saja lewat website, tanpa perlu antri lama di lokasi.</p>
                    </div>
                </div>
                
                <div class="col-12 col-sm-6 col-lg-3 fade-up" style="transition-delay:.35s">
                    <div class="card border-0 bg-transparent h-100">
                        <div class="feature-icon-box"><i class="bi bi-clock-history"></i></div>
                        <h5 class="fw-bold">Tepat Waktu</h5>
                        <p class="text-muted small">Setiap pekerjaan dikerjakan sesuai estimasi. Kendaraan siap tepat pada waktunya.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <section id="cta" class="py-5" style="background-color: transparent !important;">
        <div class="container-xl bg-white rounded-4 shadow p-5 text-center fade-up">
        
            <div class="feature-icon-box mx-auto mb-4" style="width: 70px; height: 70px; display: flex; align-items: center; justify-content: center;">
                <img src="assets/logo.png" alt="Logo BengCare" style="height: 36px; object-fit: contain;">
            </div>

            <h2 class="fw-bold display-6 mb-3 text-blue">Motormu Butuh Perhatian?</h2>
            <p class="mb-4 fs-5 text-muted">Daftar sekarang dan booking servis pertamamu dalam hitungan menit.</p>
        
            <div class="d-flex justify-content-center gap-3 flex-wrap mt-2">
                <a href="<?= $is_logged_in ? $link_tujuan : 'register.php' ?>" class="btn btn-primary-custom fw-bold btn-lg rounded-pill px-5 py-3 shadow-sm">
                    <?= $is_logged_in ? 'Ke Menu Booking' : 'Daftar & Pesan Sekarang' ?>
                </a>
            </div>
        
        </div>
    </section>

    <footer id="booking" class="pt-5 pb-4 bg-light text-dark">
        <div class="container-xl">
            <div class="row g-4 align-items-start mb-5">
                
                <div class="col-12 col-md-4">
                    <h4 class="fw-bold text-blue mb-3">
                        <img src="assets/logo.png" alt="Logo BengCare" height="30" class="me-2"> BengCare
                    </h4>
                    <p class="text-muted small pe-md-4">
                        Solusi terpercaya untuk perawatan dan perbaikan motor Anda. Harga transparan, mekanik handal, dan booking yang mudah.
                    </p>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <h6 class="fw-bold mb-3">Lokasi & Kontak</h6>
                    <ul class="list-unstyled text-muted small lh-lg">
                        <li><i class="bi bi-geo-alt me-2 text-blue"></i> Jl. Magelang No. 45, Sleman, Yogyakarta</li>
                        <li><i class="bi bi-telephone me-2 text-blue"></i> +62 812-3456-7890</li>
                        <li><i class="bi bi-envelope me-2 text-blue"></i> cs@bengcare.com</li>
                    </ul>
                </div>

                <div class="col-12 col-sm-6 col-md-4">
                    <h6 class="fw-bold mb-3">Jam Operasional</h6>
                    <ul class="list-unstyled text-muted small lh-lg">
                        <li>Senin - Jumat : 08.00 - 17.00 WIB</li>
                        <li>Sabtu - Minggu : 09.00 - 15.00 WIB</li>
                        <li>Tanggal Merah : Tutup</li>
                    </ul>
                </div>

            </div>

            <div class="text-center pt-4 border-top">
                <small class="text-muted">
                    &copy; 2026 <strong>BengCare</strong> – Sistem Pemesanan Servis Bengkel Motor Online.<br>
                </small>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmxc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script>


        const observer = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('visible');
                    observer.unobserve(e.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', e => {
                const targetId = a.getAttribute('href');
                if(targetId === '#') return;
                
                const target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>

</html>