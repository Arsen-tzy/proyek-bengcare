<?php 
$current_dir = basename(dirname($_SERVER['PHP_SELF'])); 
?>

<aside class="staff-sidebar no-print">
    <div class="sb-logo">
        <img src="../assets/logo.png" alt="Logo BengCare" height="32" class="me-1"> Beng<span>Care</span>
    </div>

    <div class="sb-nav">
        <a href="../home/index.php" class="sb-item <?= ($current_dir == 'home') ? 'active' : '' ?>">
            <div class="sb-icon"><i class="bi bi-speedometer2"></i></div>
            Dashboard
        </a>
        
        
        <a href="../layanan/index.php" class="sb-item <?= ($current_dir == 'pesanan' || $current_dir == 'layanan') ? 'active' : '' ?>">
            <div class="sb-icon"><i class="bi bi-receipt-cutoff"></i></div>
            Kelola Pesanan
        </a>
        
        
        <a href="../menu/index.php" class="sb-item <?= ($current_dir == 'menu') ? 'active' : '' ?>">
            <div class="sb-icon"><i class="bi bi-tools"></i></div>
            Data Layanan
        </a>
        <!--
        <a href="../kategoriServis/index.php" class="sb-item <?= ($current_dir == 'kategoriServis') ? 'active' : '' ?>">
            <div class="sb-icon"><i class="bi bi-tags"></i></div>
            Kategori Layanan
        </a>
        -->
        <a href="../karyawan/index.php" class="sb-item <?= ($current_dir == 'karyawan') ? 'active' : '' ?>">
            <div class="sb-icon"><i class="bi bi-people"></i></div>
            Data Karyawan
        </a>
        <a href="../laporan/index.php" class="sb-item <?= ($current_dir == 'laporan') ? 'active' : '' ?>">
            <div class="sb-icon"><i class="bi bi-file-earmark-bar-graph"></i></div>
            Laporan Keuangan
        </a>
    </div>

    <div class="sb-footer">
        <div class="sb-user"><?= htmlspecialchars($karyawan_login['nama'] ?? 'Karyawan') ?></div>
        <div class="sb-role"><?= htmlspecialchars($karyawan_login['role'] ?? 'Staff') ?></div>
        <a href="../logout.php" class="btn-logout text-center d-block text-decoration-none mt-2">
            <i class="bi bi-box-arrow-right me-1"></i> Keluar
        </a>
    </div>
</aside>