<?php
session_start();
// Pastikan yang mengakses halaman ini adalah Customer
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

// Ambil kendaraan milik customer ini
$q_kendaraan = mysqli_query($conn, "SELECT * FROM kendaraan WHERE pemilik = '$id_customer' AND jenis = 'Motor'");// Ambil daftar layanan dari master data
$q_layanan = mysqli_query($conn, "SELECT * FROM layanan WHERE id_kategori_layanan = 1 ORDER BY nama_layanan ASC");

$error = "";

// Proses ketika tombol "Kirim Pesanan" ditekan
if (isset($_POST['kirim_pesanan'])) {
    if (!empty($_POST['layanan_terpilih']) && !empty($_POST['kendaraan_id'])) {
        $kendaraan_id = intval($_POST['kendaraan_id']);
        $keluhan = mysqli_real_escape_string($conn, $_POST['keluhan']);
        $tanggal = date('Y-m-d H:i:s');
        
        // Hitung grand total
        $total = 0;
        $layanan_ids = $_POST['layanan_terpilih']; 
        foreach ($layanan_ids as $id_lyn) {
            $q_harga = mysqli_query($conn, "SELECT harga FROM layanan WHERE id = '$id_lyn'");
            $total += mysqli_fetch_array($q_harga)['harga'];
        }

        $metode_pembayaran = mysqli_real_escape_string($conn, $_POST['metode_pembayaran'] ?? 'Bayar di tempat');

        // 1. Simpan ke master pesanan
        $q_pesanan = "INSERT INTO pemesanan (tanggal, total, status, customer_id, kendaraan_id, keluhan, metode_pembayaran) 
                      VALUES ('$tanggal', '$total', 'belum', '$id_customer', '$kendaraan_id', '$keluhan', '$metode_pembayaran')";
        
        if (mysqli_query($conn, $q_pesanan)) {
            $id_pemesanan = mysqli_insert_id($conn);
            
            // 2. Simpan ke detail pesanan (keranjang)
            foreach ($layanan_ids as $id_lyn) {
                $q_harga = mysqli_query($conn, "SELECT harga FROM layanan WHERE id = '$id_lyn'");
                $harga = mysqli_fetch_array($q_harga)['harga'];
                mysqli_query($conn, "INSERT INTO detail_pemesanan (id_layanan, id_pemesanan, qty, harga, subtotal) 
                                     VALUES ('$id_lyn', '$id_pemesanan', 1, '$harga', '$harga')");
            }
            
            $_SESSION['message'] = "Pesanan servis berhasil dibuat! Silakan tunggu konfirmasi.";
            header("Location: riwayat.php"); 
            exit;
        } else {
            $error = "Gagal memproses pesanan. Coba lagi.";
        }
    } else {
        $error = "Mohon pilih minimal 1 layanan dan kendaraan Anda!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Servis - BengCare</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="background: linear-gradient(180deg, #6BA9FC 0%, #D1D6F1 100%) !important; background-attachment: fixed !important; background-repeat: no-repeat !important; min-height: 100vh;">    <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top py-3 no-print">
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
    <main class="cust-content mt-4" style="background-color: transparent !important;">        
        <div class="mb-4">
            <h1 class="panel-title text-blue" style="font-size: 2.2rem;">Pesan Servis</h1>
    
            <p class="panel-sub text-dark" style="opacity: 0.8;">Pilih layanan yang dibutuhkan dan isi detail kendaraanmu.</p>
    
        </div>

        <?php if (!empty($error)): ?>
            <div class="toast error show mb-4 position-relative transform-none w-100 max-w-100">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" id="formBooking">
            <div class="order-layout">
                
                <div class="order-card shadow-sm border-0 h-100">
                    <h5 class="font-bold text-dark mb-4">1. Pilih Layanan</h5>
                    
                    <div class="service-picker-list pe-1">
                        <?php while($lyn = mysqli_fetch_array($q_layanan)): 
                            $is_selected = (isset($_GET['layanan_id']) && $_GET['layanan_id'] == $lyn['id']);
                        ?>
                            <div class="svc-pick-item <?= $is_selected ? 'selected' : '' ?>" data-id="<?= $lyn['id'] ?>" data-harga="<?= $lyn['harga'] ?>" onclick="toggleService(this, <?= $lyn['id'] ?>, <?= $lyn['harga'] ?>)">
                                <div>
                                    <div class="svc-pick-name"><?= htmlspecialchars($lyn['nama_layanan']) ?></div>
                                    <div class="svc-pick-price">
                                        Rp <?= number_format($lyn['harga'], 0, ',', '.') ?> 
                                        <span style="color: var(--text-muted); font-weight: normal; margin-left: 6px;">
                                            • <?= htmlspecialchars($lyn['estimasi_waktu'] ?: '- Menit') ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="svc-pick-check <?= $is_selected ? '' : 'd-none' ?>"><i class="bi bi-check-lg"></i></div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <div id="servicePagination" class="mt-3"></div>
                </div>

                <div class="order-card shadow-sm border-0 d-flex flex-column h-100">
                    <h5 class="font-bold text-dark mb-4">2. Detail Pesanan</h5>
                    
                    <div id="hiddenInputsArea">
                        <?php if (isset($_GET['tanggal'])): ?>
                            <input type="hidden" name="tanggal_booking" value="<?= htmlspecialchars($_GET['tanggal']) ?>">
                        <?php endif; ?>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">KENDARAAN <span class="text-danger">*</span></label>
                        <select class="form-control" name="kendaraan_id" required>
                            <option value="" disabled selected>-- Pilih Kendaraan Anda --</option>
                            <?php if(mysqli_num_rows($q_kendaraan) > 0): ?>
                                <?php while($knd = mysqli_fetch_array($q_kendaraan)): ?>
                                    <option value="<?= $knd['id'] ?>">
                                        <?= htmlspecialchars($knd['merk'] . ' ' . $knd['tipe']) ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option value="" disabled>Belum ada kendaraan terdaftar.</option>
                            <?php endif; ?>
                        </select>
                        <div class="mt-2 text-end">
                            <a href="kendaraan.php" class="text-blue" style="font-size: 0.8rem; font-weight: 600;">+ Tambah Kendaraan</a>
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">KELUHAN / CATATAN (Opsional)</label>
                        <textarea class="form-control" name="keluhan" rows="3" placeholder="Jelaskan keluhan mesin atau request tambahan..."></textarea>
                    </div>

                    <div class="form-group mb-4">
                        <label class="form-label">METODE PEMBAYARAN <span class="text-danger">*</span></label>
                        <select class="form-control" name="metode_pembayaran" required>
                            <option value="Bayar di tempat" selected>Bayar di Tempat</option>
                            <option value="Transfer">Transfer</option>
                        </select>
                    </div>

                    <div class="mt-auto p-3 rounded mb-4" style="background-color: var(--blue-pale); border: 1px solid var(--border2);">
                        <span class="d-block mb-1" style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Estimasi Total</span>
                        <h2 class="font-bold text-blue mb-0" id="totalDisplay">Rp 0</h2>
                    </div>

                    <button type="submit" name="kirim_pesanan" class="btn-primary w-100 py-3 d-flex justify-content-center border-0" style="font-size: 1.05rem;">
                         Kirim Pesanan
                    </button>
                </div>

            </div>
        </form>
    </main>

    <script>
        let totalHarga = 0;

        function toggleService(element, id, harga) {
            const hiddenArea = document.getElementById('hiddenInputsArea');
            const checkIcon = element.querySelector('.svc-pick-check');
            const inputId = 'input_lyn_' + id;
            
            // Jika diklik dan sudah dalam status "selected" -> Batal pilih
            if (element.classList.contains('selected')) {
                element.classList.remove('selected');
                checkIcon.classList.add('d-none');
                
                // Hapus input hidden
                const inputToRemove = document.getElementById(inputId);
                if(inputToRemove) inputToRemove.remove();
                
                // Kurangi total
                totalHarga -= harga;
            } 
            // Jika belum dipilih -> Pilih layanan ini
            else {
                element.classList.add('selected');
                checkIcon.classList.remove('d-none');
                
                // Tambah input hidden agar datanya ikut terkirim ke PHP
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'layanan_terpilih[]';
                input.value = id;
                input.id = inputId;
                hiddenArea.appendChild(input);
                
                // Tambah total
                totalHarga += harga;
            }

            // Update tulisan Rupiah di layar
            let formatRupiah = new Intl.NumberFormat('id-ID').format(totalHarga);
            document.getElementById('totalDisplay').innerText = 'Rp ' + formatRupiah;
        }

        // DOM Content Loaded Handler for Pagination & Pre-selected Restore
        document.addEventListener("DOMContentLoaded", function() {
            const items = document.querySelectorAll('.svc-pick-item');
            const hiddenArea = document.getElementById('hiddenInputsArea');
            
            // 1. Restore hidden inputs and calculate initial total price for selected items
            items.forEach(item => {
                if (item.classList.contains('selected')) {
                    const id = item.getAttribute('data-id');
                    const harga = parseInt(item.getAttribute('data-harga')) || 0;
                    
                    // Add hidden input
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'layanan_terpilih[]';
                    input.value = id;
                    input.id = 'input_lyn_' + id;
                    hiddenArea.appendChild(input);
                    
                    // Add to total
                    totalHarga += harga;
                }
            });
            
            // Update initial price display
            let formatRupiah = new Intl.NumberFormat('id-ID').format(totalHarga);
            document.getElementById('totalDisplay').innerText = 'Rp ' + formatRupiah;

            // 2. Setup Client-Side Pagination (5 items per page)
            const itemsPerPage = 5;
            const totalPages = Math.ceil(items.length / itemsPerPage);
            const paginationContainer = document.getElementById('servicePagination');
            
            if (totalPages <= 1) return; // No pagination UI needed if 5 or fewer items

            let currentPage = 1;

            // Find page with the selected item to open it initially
            const firstSelectedItem = document.querySelector('.svc-pick-item.selected');
            if (firstSelectedItem) {
                const itemsArray = Array.from(items);
                const selectedIndex = itemsArray.indexOf(firstSelectedItem);
                if (selectedIndex !== -1) {
                    currentPage = Math.floor(selectedIndex / itemsPerPage) + 1;
                }
            }

            function showPage(page) {
                currentPage = page;
                items.forEach((item, index) => {
                    const itemPage = Math.floor(index / itemsPerPage) + 1;
                    if (itemPage === page) {
                        item.style.setProperty('display', 'flex', 'important');
                    } else {
                        item.style.setProperty('display', 'none', 'important');
                    }
                });
                renderPaginationButtons();
            }

            function renderPaginationButtons() {
                let html = '<ul class="pagination pagination-sm mb-0 justify-content-center">';
                
                // Previous button
                html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault(); window.changeServicePage(${currentPage - 1})">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>`;

                for (let i = 1; i <= totalPages; i++) {
                    html += `<li class="page-item ${currentPage === i ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="event.preventDefault(); window.changeServicePage(${i})">${i}</a>
                    </li>`;
                }

                // Next button
                html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault(); window.changeServicePage(${currentPage + 1})">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>`;

                html += '</ul>';
                paginationContainer.innerHTML = html;
            }

            window.changeServicePage = function(page) {
                if (page < 1 || page > totalPages) return;
                showPage(page);
            };

            showPage(currentPage);
        });
    </script>
</body>
</html>