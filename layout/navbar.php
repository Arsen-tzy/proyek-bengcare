<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm sticky-top">
    <div class="container-lg">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-tools me-2"></i>BengCare
        </a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#topNavBar" aria-controls="topNavBar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="topNavBar">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item me-3 d-none d-lg-block">
                    <span class="nav-link active font-monospace" style="font-size: 0.85rem;">
                        <i class="bi bi-calendar3 me-1"></i> <?= date('d M Y') ?>
                    </span>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle active fw-semibold" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i> 
                        <?= htmlspecialchars($karyawan_login['nama'] ?? 'Karyawan') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm mt-2">
                        <li><h6 class="dropdown-header">Akses: <?= htmlspecialchars(ucfirst($karyawan_login['role'] ?? '-')) ?></h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>