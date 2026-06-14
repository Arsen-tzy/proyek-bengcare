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

$error = "";
$sukses = "";

// PROSES TAMBAH KENDARAAN BARU
if (isset($_POST['tambah_kendaraan'])) {
    $jenis = mysqli_real_escape_string($conn, $_POST['jenis'] ?? 'Motor');
    if (empty($jenis)) {
        $jenis = 'Motor';
    }
    $merk  = mysqli_real_escape_string($conn, $_POST['merk']);
    $tipe  = mysqli_real_escape_string($conn, $_POST['tipe']);

    if (!empty($jenis) && !empty($merk) && !empty($tipe)) {
        // Berdasarkan gambar struktur databasemu, nama kolom penghubungnya adalah "pemilik"
        $q_insert = "INSERT INTO kendaraan (pemilik, jenis, merk, tipe) VALUES ('$id_customer', '$jenis', '$merk', '$tipe')";
        if (mysqli_query($conn, $q_insert)) {
            $sukses = "Kendaraan berhasil ditambahkan!";
        } else {
            $error = "Gagal menambahkan kendaraan ke database.";
        }
    } else {
        $error = "Mohon lengkapi semua kolom!";
    }
}

// PROSES HAPUS KENDARAAN
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);
    // Pastikan hanya bisa menghapus kendaraan miliknya sendiri
    mysqli_query($conn, "DELETE FROM kendaraan WHERE id = '$id_hapus' AND pemilik = '$id_customer'");
    header("Location: kendaraan.php");
    exit;
}

// AMBIL DAFTAR KENDARAAN PELANGGAN
$q_kendaraan = mysqli_query($conn, "SELECT * FROM kendaraan WHERE pemilik = '$id_customer' AND jenis = 'Motor' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kendaraan Saya - BengCare</title>
    
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
        <div class="mb-4">
            <h1 class="panel-title text-blue" style="font-size: 2.2rem;">Data Kendaraan</h1>
    
            <p class="panel-sub text-dark" style="opacity: 0.8;">Daftarkan motor milik anda disini.</p>
    
        </div>

        <?php if (!empty($error)): ?>
            <div class="toast error show mb-4 position-relative transform-none max-w-100 w-100">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= $error ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($sukses)): ?>
            <div class="toast success show mb-4 position-relative transform-none max-w-100 w-100">
                <i class="bi bi-check-circle-fill"></i> <?= $sukses ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-4"><i class="bi bi-plus-circle-dotted text-blue me-2"></i>Tambah Baru</h5>
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label text-muted fw-bold" style="font-size:0.8rem;">MERK (Contoh: Honda, Yamaha)</label>
                                <input type="text" class="form-control" name="merk" required placeholder="Masukkan merk">
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-muted fw-bold" style="font-size:0.8rem;">TIPE (Contoh: Vario 150, Beat)</label>
                                <input type="text" class="form-control" name="tipe" required placeholder="Masukkan tipe">
                            </div>
                            <button type="submit" name="tambah_kendaraan" class="btn btn-primary w-100 border-0 py-2">
                                Simpan Kendaraan
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-4"><i class="bi bi-car-front text-blue me-2"></i>Daftar Kendaraan Anda</h5>
                        
                        <div class="row g-3">
                            <?php if(mysqli_num_rows($q_kendaraan) > 0): ?>
                                <?php while($k = mysqli_fetch_array($q_kendaraan)): ?>
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-3" style="background-color: var(--blue-pale); border-color: var(--border2) !important;">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center text-blue shadow-sm" style="width: 45px; height: 45px; font-size: 1.2rem;">
                                                        <i class="bi <?= ($k['jenis'] == 'Motor') ? 'bi-bicycle' : 'bi-car-front-fill' ?>"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="fw-bold text-dark mb-1"><?= htmlspecialchars($k['merk'] . ' ' . $k['tipe']) ?></h6>
                                                        <span class="badge bg-secondary opacity-75 fw-normal"><?= htmlspecialchars($k['jenis']) ?></span>
                                                    </div>
                                                </div>
                                                <a href="kendaraan.php?hapus=<?= $k['id'] ?>" class="text-danger" onclick="return confirm('Hapus kendaraan ini?')" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="empty-state py-4 border rounded-3">
                                        <i class="bi bi-inboxes empty-icon d-block mb-2"></i>
                                        <p class="text-muted mb-0">Belum ada kendaraan yang ditambahkan.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

</body>
</html>