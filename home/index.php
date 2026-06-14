<?php
session_start();
// Cek apakah karyawan sudah login
if (empty($_SESSION['id_karyawan']) && empty($_SESSION['kode'])) {
    header("Location: ../login.php");
    exit;
}

include dirname(__DIR__) . "/koneksi.php";

// Query mengambil data karyawan
$id_karyawan = $_SESSION['id_karyawan'] ?? $_SESSION['kode'];
$query_karyawan = "SELECT * FROM karyawan WHERE id = '$id_karyawan' OR kode = '$id_karyawan'";
$result_karyawan = mysqli_query($conn, $query_karyawan);
$karyawan_login = mysqli_fetch_array($result_karyawan);

// Data Statistik Dashboard
$today = date('Y-m-d');
$jumlah_today = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pemesanan WHERE DATE(tanggal) = '$today'"))['total'] ?? 0;
$jumlah_antrian = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pemesanan WHERE status = 'belum'"))['total'] ?? 0;
$jumlah_selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM pemesanan WHERE status = 'selesai'"))['total'] ?? 0;
$total_income = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total) as income FROM pemesanan WHERE status = 'selesai'"))['income'] ?? 0;

// 5 pesanan terbaru
$query_recent = "SELECT p.*, c.nama AS nama_customer, k.merk, k.tipe
                 FROM pemesanan p
                 LEFT JOIN customers c ON p.customer_id = c.id
                 LEFT JOIN kendaraan k ON p.kendaraan_id = k.id
                 ORDER BY p.tanggal DESC LIMIT 5";
$result_recent = mysqli_query($conn, $query_recent);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - BengCare</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="../assets/css/style.css?v=<?= time(); ?>">
</head>
<body id="page-staff" style="background: linear-gradient(180deg, #6BA9FC 0%, #D1D6F1 100%) !important; background-attachment: fixed !important; min-height: 100vh;">
    <div class="staff-wrap" style="background-color: transparent !important;">
        
        <?php include "../layout/sidebar.php"; ?>

        <main class="staff-main p-4 p-md-5" style="background-color: transparent !important;">
            
            <!-- Hero Banner Landing-page Style -->
            <div class="card border-0 rounded-4 mb-4 text-white overflow-hidden shadow-sm" style="background: linear-gradient(135deg, #255498 0%, #6BA9FC 100%); transition: transform 0.3s ease; border-bottom: 5px solid #6BA9FC !important;">
                <div class="card-body p-4 p-md-5 position-relative">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h1 class="fw-bold display-6 mb-2" style="font-family: 'Plus Jakarta Sans', sans-serif;">Selamat Datang Kembali, <?= htmlspecialchars(explode(' ', trim($karyawan_login['nama'] ?? 'Admin'))[0]); ?></h1>
                            <p class="mb-0 opacity-75 fs-5">Pantau, kelola, dan tingkatkan performa operasional bengkel Anda secara real-time dari satu tempat.</p>
                        </div>
                        <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                            <a href="../layanan/create.php" class="btn btn-light btn-lg rounded-pill px-4 py-3 fw-bold text-blue shadow-sm hover-up" style="font-size: 0.95rem; transition: transform 0.2s;">
                                <i class="bi bi-plus-circle-fill me-2 text-blue"></i> Buat Pesanan Baru
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($jumlah_antrian > 0): ?>
            <div class="alert alert-warning d-flex align-items-center gap-2 mb-4 border-0 shadow-sm" role="alert" style="border-radius: var(--r-md); background-color: #fffbeb; border-left: 4px solid #ffc107 !important;">
                <i class="bi bi-exclamation-triangle-fill fs-5 text-warning"></i>
                <div>
                    Ada <strong><?= $jumlah_antrian ?> pesanan</strong> menunggu untuk diproses.
                    <a href="../layanan/index.php" class="alert-link ms-1 text-decoration-none text-dark fw-bold">Lihat Antrian →</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Stats Grid -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 p-4 bg-white" style="transition: transform 0.2s; border-left: 4px solid #255498 !important;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 1px;">PESANAN HARI INI</div>
                            <div class="bg-blue-light text-blue rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 1.25rem; background-color: rgba(37, 84, 152, 0.1);"><i class="bi bi-calendar-check"></i></div>
                        </div>
                        <h2 class="fw-bolder text-blue mb-1"><?= $jumlah_today ?></h2>
                        <div class="text-secondary small mt-auto"><i class="bi bi-clock me-1"></i> <?= date('d M Y') ?></div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 p-4 bg-white" style="transition: transform 0.2s; border-left: 4px solid #ffc107 !important;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 1px;">ANTRIAN SERVIS</div>
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 1.25rem; background-color: rgba(255, 193, 7, 0.1);"><i class="bi bi-hourglass-split"></i></div>
                        </div>
                        <h2 class="fw-bolder text-warning mb-1"><?= $jumlah_antrian ?></h2>
                        <div class="text-secondary small mt-auto"><i class="bi bi-exclamation-circle me-1"></i> Belum Selesai</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 p-4 bg-white" style="transition: transform 0.2s; border-left: 4px solid #198754 !important;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 1px;">PESANAN SELESAI</div>
                            <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 1.25rem; background-color: rgba(25, 135, 84, 0.1);"><i class="bi bi-check-circle"></i></div>
                        </div>
                        <h2 class="fw-bolder text-success mb-1"><?= $jumlah_selesai ?></h2>
                        <div class="text-secondary small mt-auto"><i class="bi bi-infinity me-1"></i> Sepanjang waktu</div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-4 h-100 p-4 bg-white" style="transition: transform 0.2s; border-left: 4px solid #0dcaf0 !important;">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 1px;">TOTAL PENDAPATAN</div>
                            <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; font-size: 1.25rem; background-color: rgba(13, 202, 240, 0.1);"><i class="bi bi-cash-stack"></i></div>
                        </div>
                        <h3 class="fw-bolder text-info mb-1" style="font-size: 1.35rem; font-family: 'Plus Jakarta Sans', sans-serif;">Rp <?= number_format($total_income, 0, ',', '.') ?></h3>
                        <div class="text-secondary small mt-auto"><i class="bi bi-wallet2 me-1"></i> Dari pesanan selesai</div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Layout (Full Width) -->
            <div class="row g-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                            <div class="card-title text-blue fw-bold m-0"><i class="bi bi-clock-history me-2"></i>Pesanan Terbaru</div>
                        </div>
                        <div class="card-body p-4 mt-1">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">
                                            <th>CUSTOMER</th>
                                            <th>KENDARAAN</th>
                                            <th>TOTAL</th>
                                            <th class="text-center">STATUS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($result_recent && mysqli_num_rows($result_recent) > 0): ?>
                                            <?php while ($row = mysqli_fetch_assoc($result_recent)): ?>
                                                <?php
                                                    $badge = match($row['status']) { 'selesai' => 'bg-success', 'batal' => 'bg-danger', default => 'bg-warning text-dark' };
                                                    $label = match($row['status']) { 'selesai' => 'Selesai', 'batal' => 'Batal', default => 'Antrian' };
                                                ?>
                                                <tr>
                                                    <td class="fw-bold text-dark"><?= htmlspecialchars($row['nama_customer'] ?? '-') ?></td>
                                                    <td class="text-secondary" style="font-size: 0.9rem;"><?= htmlspecialchars(($row['merk'] ?? '') . ' ' . ($row['tipe'] ?? '')) ?></td>
                                                    <td class="fw-bold text-success">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                                    <td class="text-center"><span class="badge <?= $badge ?> rounded-pill px-3 py-2"><?= $label ?></span></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="text-center py-4 text-muted"><i class="bi bi-inboxes d-block fs-3 mb-2"></i>Belum ada pesanan.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <style>
                .hover-up:hover {
                    transform: translateY(-3px);
                }
            </style>

        </main>
    </div>
</body>
</html>