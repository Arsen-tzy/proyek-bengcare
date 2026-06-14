<?php
session_start();
if (empty($_SESSION['id_karyawan'])) { header("Location: ../login.php"); exit; }
include dirname(__DIR__) . "/koneksi.php";

$id_pemesanan = mysqli_real_escape_string($conn, $_GET['id']);

// Ambil info master pesanan
$q_pesanan = "SELECT p.*, c.nama as nama_customer, c.hp, knd.merk, knd.tipe, knd.jenis, k.nama as kasir 
              FROM pemesanan p 
              LEFT JOIN customers c ON p.customer_id = c.id 
              LEFT JOIN kendaraan knd ON p.kendaraan_id = knd.id
              LEFT JOIN karyawan k ON p.id_karyawan = k.id
              WHERE p.id='$id_pemesanan'";
$data_pesanan = mysqli_fetch_assoc(mysqli_query($conn, $q_pesanan));

// Ambil Rincian Layanan
$q_detail = "SELECT dp.*, l.nama_layanan 
             FROM detail_pemesanan dp
             JOIN layanan l ON dp.id_layanan = l.id
             WHERE dp.id_pemesanan = '$id_pemesanan'";
$r_detail = mysqli_query($conn, $q_detail);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Booking - BengCare</title>
    <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.min.css">
    <script type="text/javascript" src="../assets/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">
    <?php include "../layout/navbar.php"; ?>

    <div class="container-lg">
        <div class="row justify-content-center">
            <div class="col-lg-8 mt-4 mb-5">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-receipt"></i> Invoice #<?= $id_pemesanan; ?></h5>
                        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
                    </div>
                    
                    <div class="card-body p-4">
                        <div class="row mb-4">
                            <div class="col-sm-6">
                                <h6 class="text-muted mb-1">Informasi Pelanggan:</h6>
                                <strong><?= htmlspecialchars($data_pesanan['nama_customer']); ?></strong><br>
                                <i class="bi bi-telephone text-muted"></i> <?= htmlspecialchars($data_pesanan['hp']); ?><br>
                                <i class="bi <?= (strtolower($data_pesanan['jenis'] ?? '') === 'motor') ? 'bi-bicycle' : 'bi-car-front' ?> text-muted"></i> <?= htmlspecialchars($data_pesanan['merk'] . ' ' . $data_pesanan['tipe']); ?>
                            </div>
                            <div class="col-sm-6 text-sm-end mt-3 mt-sm-0">
                                <h6 class="text-muted mb-1">Detail Booking:</h6>
                                <span>Tanggal: <?= date('d M Y, H:i', strtotime($data_pesanan['tanggal'])); ?></span><br>
                                <span>Pembayaran: <strong><?= htmlspecialchars($data_pesanan['metode_pembayaran'] ?? 'Bayar di tempat'); ?></strong></span><br>
                                <span>Kasir/Admin: <?= htmlspecialchars($data_pesanan['kasir']); ?></span><br>
                                <?php
                                    $stat = $data_pesanan['status'];
                                    $bg = ($stat=='belum')?'bg-warning text-dark':(($stat=='selesai')?'bg-success':'bg-danger');
                                ?>
                                Status: <span class="badge <?= $bg; ?> ms-1"><?= ($stat=='belum')?'Antrian':ucfirst($stat); ?></span>
                            </div>
                        </div>

                        <?php if(!empty($data_pesanan['keluhan'])): ?>
                            <div class="alert alert-light border mb-4">
                                <strong>Keluhan Pelanggan:</strong><br>
                                <?= nl2br(htmlspecialchars($data_pesanan['keluhan'])); ?>
                            </div>
                        <?php endif; ?>

                        <table class="table table-bordered mb-0 text-center">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-start">Layanan Servis</th>
                                    <th>Harga</th>
                                    <th>Qty</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_array($r_detail)): ?>
                                <tr>
                                    <td class="text-start"><?= htmlspecialchars($row['nama_layanan']); ?></td>
                                    <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                                    <td><?= $row['qty']; ?></td>
                                    <td class="text-end">Rp <?= number_format($row['subtotal'], 0, ',', '.'); ?></td>
                                </tr>
                                <?php endwhile; ?>
                                <tr class="table-group-divider">
                                    <td colspan="3" class="text-end fw-bold">TOTAL BAYAR</td>
                                    <td class="text-end fw-bold text-success fs-5">Rp <?= number_format($data_pesanan['total'], 0, ',', '.'); ?></td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class="text-end mt-4">
                            <button onclick="window.print()" class="btn btn-secondary"><i class="bi bi-printer"></i> Cetak Struk</button>
                        </div>

                    </div>
                </div>
                
                <?php include "../layout/footer.php"; ?>
            </div>
        </div>
    </div>
</body>
</html>