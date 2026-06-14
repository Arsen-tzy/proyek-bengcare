<?php
session_start();
// Pastikan hanya Karyawan yang bisa masuk
if (empty($_SESSION['id_karyawan']) && empty($_SESSION['kode'])) {
    header("Location: ../login.php");
    exit;
}

include dirname(__DIR__) . "/koneksi.php";

// Ambil profil Karyawan
$karyawan_login = null;
if (!empty($_SESSION['kode'])) {
    $q_kar = mysqli_query($conn, "SELECT * FROM karyawan WHERE kode = '{$_SESSION['kode']}'");
    $karyawan_login = mysqli_fetch_array($q_kar);
} elseif (!empty($_SESSION['id_karyawan'])) {
    $q_kar = mysqli_query($conn, "SELECT * FROM karyawan WHERE id = '{$_SESSION['id_karyawan']}'");
    $karyawan_login = mysqli_fetch_array($q_kar);
}

// Ambil data untuk form
$q_layanan = mysqli_query($conn, "SELECT * FROM layanan WHERE id_kategori_layanan = 1 ORDER BY nama_layanan ASC");
$q_customers = mysqli_query($conn, "SELECT id, nama FROM customers ORDER BY nama ASC");
$q_kendaraan = mysqli_query($conn, "SELECT id, merk, tipe, pemilik FROM kendaraan WHERE jenis = 'Motor' ORDER BY merk ASC");

$error = "";
$sukses = "";

// Proses Simpan Pesanan (Menggunakan array input tersembunyi dari JavaScript)
if (isset($_POST['simpan_pesanan'])) {
    if (!empty($_POST['layanan_terpilih']) && !empty($_POST['customer_id']) && !empty($_POST['kendaraan_id'])) {
        $id_karyawan = $karyawan_login['id'] ?? 1;
        $customer_id = intval($_POST['customer_id']);
        $kendaraan_id = intval($_POST['kendaraan_id']);
        $keluhan = mysqli_real_escape_string($conn, $_POST['keluhan']);
        $tanggal = date('Y-m-d H:i:s');

        // Hitung total harga layanan
        $total = 0;
        $layanan_ids = $_POST['layanan_terpilih'];
        foreach ($layanan_ids as $id_lyn) {
            $q_harga = mysqli_query($conn, "SELECT harga FROM layanan WHERE id = '$id_lyn'");
            $total += mysqli_fetch_array($q_harga)['harga'];
        }

        $metode_pembayaran = mysqli_real_escape_string($conn, $_POST['metode_pembayaran'] ?? 'Bayar di tempat');

        // 1. Simpan ke tabel pemesanan
        $q_pesanan = "INSERT INTO pemesanan (tanggal, total, status, id_karyawan, customer_id, kendaraan_id, keluhan, metode_pembayaran) 
                      VALUES ('$tanggal', '$total', 'belum', '$id_karyawan', '$customer_id', '$kendaraan_id', '$keluhan', '$metode_pembayaran')";
        
        if (mysqli_query($conn, $q_pesanan)) {
            $id_pemesanan = mysqli_insert_id($conn);
            
            // 2. Simpan detail layanan ke keranjang
            foreach ($layanan_ids as $id_lyn) {
                $q_harga = mysqli_query($conn, "SELECT harga FROM layanan WHERE id = '$id_lyn'");
                $harga = mysqli_fetch_array($q_harga)['harga'];
                mysqli_query($conn, "INSERT INTO detail_pemesanan (id_layanan, id_pemesanan, qty, harga, subtotal) 
                                     VALUES ('$id_lyn', '$id_pemesanan', 1, '$harga', '$harga')");
            }
            $sukses = "Pesanan berhasil dibuat dan masuk ke antrian!";
        } else {
            $error = "Gagal menyimpan data pesanan!";
        }
    } else {
        $error = "Pilih minimal 1 layanan, Customer, dan Kendaraan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Pesanan - Admin BengCare</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <link rel="stylesheet" href="../assets/css/style.css?v=<?= time(); ?>">
</head>

<body id="page-staff" style="background: linear-gradient(180deg, #6BA9FC 0%, #D1D6F1 100%) !important; background-attachment: fixed !important; min-height: 100vh;">
    <div class="staff-wrap" style="background-color: transparent !important;">
        
        <?php include "../layout/sidebar.php"; ?>

        <main class="staff-main" style="background-color: transparent !important;">
            
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h1 class="panel-title text-white">Buat Pesanan Baru</h1>
                    <p class="panel-sub text-white-50">Form kasir interaktif untuk membuat pesanan pelanggan yang datang langsung.</p>
                </div>
                <a href="index.php" class="btn btn-outline-light rounded-pill px-4 py-2 fw-bold"><i class="bi bi-arrow-left"></i> Kembali</a>
            </div>
            <div class="panel-bar bg-white"></div>

            <?php if ($error): ?>
                <div class="alert alert-danger shadow-sm border-0"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error ?></div>
            <?php endif; ?>
            <?php if ($sukses): ?>
                <div class="alert alert-success shadow-sm border-0"><i class="bi bi-check-circle-fill me-2"></i><?= $sukses ?></div>
            <?php endif; ?>

            <form action="" method="POST" id="formKasir">
                <div class="order-layout mt-4">
                    
                    <div class="card border-0 shadow-sm h-100 rounded-4">
                        <div class="card-body p-4">
                            <h5 class="font-bold text-dark mb-4"><i class="bi bi-tools text-blue me-2"></i>1. Pilih Layanan</h5>
                            
                            <div class="service-picker-list pe-1">
                                <?php while($lyn = mysqli_fetch_array($q_layanan)): ?>
                                    <div class="svc-pick-item" onclick="toggleService(this, <?= $lyn['id'] ?>, <?= $lyn['harga'] ?>)">
                                        <div>
                                            <div class="svc-pick-name"><?= htmlspecialchars($lyn['nama_layanan']) ?></div>
                                            <div class="svc-pick-price">
                                                Rp <?= number_format($lyn['harga'], 0, ',', '.') ?>
                                                <span style="color: var(--text-muted); font-weight: normal; margin-left: 6px;">
                                                    • <?= htmlspecialchars($lyn['estimasi_waktu'] ?: '- Menit') ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="svc-pick-check d-none"><i class="bi bi-check-lg"></i></div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 d-flex flex-column h-100">
                        <div class="card-body p-4 d-flex flex-column h-100">
                            <h5 class="font-bold text-dark mb-4"><i class="bi bi-person-lines-fill text-blue me-2"></i>2. Identitas Pelanggan</h5>
                            
                            <div id="hiddenInputsArea"></div>

                            <div class="mb-3">
                                <label class="form-label">CUSTOMER <span class="text-danger">*</span></label>
                                <select class="form-control form-select" name="customer_id" required>
                                    <option value="" disabled selected>-- Pilih Customer --</option>
                                    <?php while($c = mysqli_fetch_array($q_customers)) { echo "<option value='{$c['id']}'>{$c['nama']}</option>"; } ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">KENDARAAN <span class="text-danger">*</span></label>
                                <select class="form-control form-select" name="kendaraan_id" required>
                                    <option value="" disabled selected>-- Pilih Kendaraan --</option>
                                    <?php while($k = mysqli_fetch_array($q_kendaraan)) { echo "<option value='{$k['id']}'>{$k['merk']} {$k['tipe']}</option>"; } ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">METODE PEMBAYARAN <span class="text-danger">*</span></label>
                                <select class="form-control form-select" name="metode_pembayaran" required>
                                    <option value="Bayar di tempat" selected>Bayar di Tempat</option>
                                    <option value="Transfer">Transfer</option>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">CATATAN MEKANIK (Opsional)</label>
                                <textarea class="form-control" name="keluhan" rows="2" placeholder="Tuliskan keluhan atau instruksi khusus..."></textarea>
                            </div>

                            <div class="mt-auto p-3 rounded mb-3" style="background-color: var(--blue-pale); border: 1px dashed var(--border2);">
                                <span class="d-block mb-1 text-muted" style="font-size: 0.8rem; font-weight: 600;">TOTAL BIAYA SERVIS</span>
                                <h2 class="font-bold text-blue mb-0" id="totalDisplay">Rp 0</h2>
                            </div>

                            <button type="submit" name="simpan_pesanan" class="btn btn-primary w-100 py-3 justify-content-center border-0 fs-6">
                                <i class="bi bi-floppy"></i> Proses Pesanan
                            </button>
                        </div>
                    </div>
                </div>
            </form>

        </main>
    </div>

    <script>
        let totalHarga = 0;

        function toggleService(element, id, harga) {
            const hiddenArea = document.getElementById('hiddenInputsArea');
            const checkIcon = element.querySelector('.svc-pick-check');
            const inputId = 'input_lyn_' + id;
            
            if (element.classList.contains('selected')) {
                element.classList.remove('selected');
                checkIcon.classList.add('d-none');
                document.getElementById(inputId).remove();
                totalHarga -= harga;
            } else {
                element.classList.add('selected');
                checkIcon.classList.remove('d-none');
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'layanan_terpilih[]';
                input.value = id;
                input.id = inputId;
                hiddenArea.appendChild(input);
                
                totalHarga += harga;
            }
            // Format Rupiah
            document.getElementById('totalDisplay').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalHarga);
        }
    </script>
</body>
</html>