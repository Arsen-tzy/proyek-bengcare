<?php
session_start();
if (empty($_SESSION['id_customer'])) {
    header("Location: ../login.php");
    exit;
}

include dirname(__DIR__) . "/koneksi.php";
$current_page = basename($_SERVER['PHP_SELF']);
$id_customer = $_SESSION['id_customer'];

// Ambil profil customer
$q_cust = mysqli_query($conn, "SELECT * FROM customers WHERE id = '$id_customer'");
$customer = mysqli_fetch_array($q_cust);

// Ambil data riwayat pesanan (Join dengan tabel kendaraan)
// Catatan: Jika relasinya di tabel pemesanan menggunakan kolom customer_id, pastikan query ini sesuai.
$q_riwayat = mysqli_query($conn, "
    SELECT p.*, k.merk, k.tipe, k.jenis 
    FROM pemesanan p 
    LEFT JOIN kendaraan k ON p.kendaraan_id = k.id 
    WHERE p.customer_id = '$id_customer' 
    ORDER BY p.tanggal DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - BengCare</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="../assets/css/style.css?v=<?= time(); ?>">
</head>
<body style="background: linear-gradient(180deg, #6BA9FC 0%, #D1D6F1 100%) !important; background-attachment: fixed !important; background-repeat: no-repeat !important; min-height: 100vh;">
    <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top py-3 no-print">
        <div class="container-xl">
            <a class="navbar-brand fw-bold text-blue fs-4" href="../index.php">
                <img src="../assets/logo.png" alt="Logo BengCare" height="30"> BengCare
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenuCust">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMenuCust">
                <ul class="navbar-nav mx-auto gap-md-4 fw-medium text-secondary">
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'booking.php') ? 'active text-blue fw-bold' : 'text-dark' ?>" href="booking.php">
                            <i class="bi bi-wrench-adjustable me-1 d-lg-none"></i> Pesan Servis
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'riwayat.php') ? 'active text-blue fw-bold' : 'text-dark' ?>" href="riwayat.php">
                            <i class="bi bi-receipt me-1 d-lg-none"></i> Riwayat Pesanan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= ($current_page == 'kendaraan.php') ? 'active text-blue fw-bold' : 'text-dark' ?>" href="kendaraan.php">
                            <i class="bi bi-car-front me-1 d-lg-none"></i> Kendaraan Saya
                        </a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0 border-lg-start ps-lg-3">
                    <div class="d-flex align-items-center gap-2">
                        <div style="width: 36px; height: 36px; background: var(--blue); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-family: var(--f-display);">
                            <?= strtoupper(substr($customer['nama'] ?? 'C', 0, 1)); ?>
                        </div>
                        <span class="fw-semibold text-muted d-lg-none d-xl-block" style="font-size: 0.9rem;">
                            Halo, <?= htmlspecialchars(explode(' ', trim($customer['nama'] ?? 'Pelanggan'))[0]); ?>
                        </span>
                    </div>
                    <a href="../logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-4 fw-bold">Keluar</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="cust-content" style="background-color: transparent;">

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?= $_SESSION['message']; unset($_SESSION['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-10">
                <?php if(mysqli_num_rows($q_riwayat) > 0): ?>
                    <?php while($row = mysqli_fetch_array($q_riwayat)): ?>
                        <?php
                            // Pewarnaan Badge Status
                            $badge = match($row['status']) {
                                'selesai' => 'bg-success',
                                'batal'   => 'bg-danger',
                                default   => 'bg-warning text-dark',
                            };
                            $label = match($row['status']) {
                                'selesai' => 'Selesai',
                                'batal'   => 'Batal',
                                default   => 'Menunggu Diproses',
                            };
                        ?>
                        <div class="card border-0 shadow-sm mb-3 rounded-4" style="transition: transform 0.2s;">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                    <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i> <?= date('d M Y, H:i', strtotime($row['tanggal'])) ?></span>
                                    <span class="badge <?= $badge ?> px-3 py-2 rounded-pill"><?= $label ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <h5 class="fw-bold text-dark mb-1"><i class="bi <?= (strtolower($row['jenis'] ?? '') === 'motor') ? 'bi-bicycle' : 'bi-car-front' ?> text-blue me-2"></i> <?= htmlspecialchars($row['merk'] . ' ' . $row['tipe']) ?></h5>
                                        <p class="text-muted small mb-0">Total Tagihan:</p>
                                        <h5 class="fw-bold text-blue mb-2">Rp <?= number_format($row['total'], 0, ',', '.') ?></h5>
                                        <div class="small text-secondary">
                                            <i class="bi bi-wallet2 me-1"></i> Pembayaran: <strong class="text-dark"><?= htmlspecialchars($row['metode_pembayaran'] ?? 'Bayar di tempat') ?></strong>
                                        </div>
                                    </div>
                                    <div>
                                        <button class="btn btn-outline-primary btn-sm rounded-pill px-3" disabled>Nota #<?= $row['id'] ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state bg-white rounded-4 shadow-sm py-5 border-0 mt-2">
                        <i class="bi bi-inboxes empty-icon d-block mb-3" style="font-size: 3rem; color: #cbd5e1;"></i>
                        <h5 class="fw-bold text-dark">Belum ada riwayat pesanan</h5>
                        <p class="text-muted">Anda belum pernah melakukan booking servis.</p>
                        <a href="booking.php" class="btn btn-primary-custom rounded-pill mt-2">Buat Pesanan Sekarang</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

</body>
</html>