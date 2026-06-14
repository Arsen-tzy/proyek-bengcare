<?php
session_start();
// Pastikan hanya admin/karyawan yang bisa akses
if (empty($_SESSION['id_karyawan']) && empty($_SESSION['kode'])) {
    header("Location: ../login.php");
    exit;
}

include dirname(__DIR__) . "/koneksi.php";

// Pagination logic
$limit = 6;
$page = isset($_GET['halaman']) ? intval($_GET['halaman']) : 1;
if ($page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $limit;

// Ambil total data
$q_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM tipe_kendaraan");
$total_data = mysqli_fetch_assoc($q_total)['total'] ?? 0;
$total_halaman = ceil($total_data / $limit);

// Ambil data
$q_tipe = mysqli_query($conn, "SELECT * FROM tipe_kendaraan ORDER BY id DESC LIMIT $limit OFFSET $offset");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Tipe Kendaraan - Admin BengCare</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    
    <link rel="stylesheet" href="../assets/css/style.css?v=<?= time(); ?>">
    
    <style>
        .search-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 50px;
            padding: 5px 15px;
            display: flex;
            align-items: center;
        }
        .search-box input {
            border: none;
            background: transparent;
            outline: none;
            width: 100%;
            font-size: 0.9rem;
        }
        .vehicle-img {
            width: 60px;
            height: 45px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background-color: #f1f5f9;
        }
    </style>
</head>

<body id="page-staff" style="background: linear-gradient(180deg, #6BA9FC 0%, #D1D6F1 100%) !important; background-attachment: fixed !important; min-height: 100vh;">

    <div class="staff-wrap" style="background-color: transparent !important;">
        
        <?php include "../layout/sidebar.php"; ?>

        <main class="staff-main p-4 p-md-5" style="background-color: transparent !important;">
            
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <div>
                    <h1 class="panel-title text-white" style="font-size: 2.2rem;">Data Tipe Kendaraan</h1>
                    <p class="panel-sub text-white-50">Kelola katalog tipe kendaraan pelanggan (Kawasaki, Yamaha, Honda, Suzuki, dll).</p>
                </div>
                <div>
                    <a href="create.php" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm fw-bold">
                        <i class="bi bi-plus-lg me-2"></i> Tambah Tipe
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> <?= $_SESSION['message']; unset($_SESSION['message']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background-color: #f8fafc;">
                                <tr class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">
                                    <th width="5%" class="rounded-start ps-3">NO</th>
                                    <th width="15%">GAMBAR</th>
                                    <th>MERK / BRAND</th>
                                    <th>TIPE KENDARAAN</th>
                                    <th width="15%" class="text-center rounded-end">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($q_tipe) > 0): ?>
                                    <?php $no = $offset + 1; while($row = mysqli_fetch_assoc($q_tipe)): ?>
                                        <tr>
                                            <td class="ps-3 fw-bold text-dark"><?= $no++ ?></td>
                                            <td>
                                                <?php 
                                                $img_src = "../assets/default_vehicle.jpg"; // Default fallback
                                                if (!empty($row['gambar'])) {
                                                    if (file_exists("../assets/" . $row['gambar'])) {
                                                        $img_src = "../assets/" . $row['gambar'];
                                                    } elseif (file_exists("../assets/img/" . $row['gambar'])) {
                                                        $img_src = "../assets/img/" . $row['gambar'];
                                                    }
                                                }
                                                ?>
                                                <img src="<?= $img_src; ?>" class="vehicle-img" alt="Foto <?= htmlspecialchars($row['merk'] . ' ' . $row['tipe']) ?>">
                                            </td>
                                            <td class="fw-semibold text-dark"><?= htmlspecialchars($row['merk']) ?></td>
                                            <td class="fw-bold text-blue"><?= htmlspecialchars($row['tipe']) ?></td>
                                            <td class="text-center">
                                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm text-dark rounded-3 me-1" title="Edit">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <a href="hapus.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm rounded-3" onclick="return confirm('Yakin ingin menghapus tipe kendaraan ini?')" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-inboxes d-block fs-1 mb-3" style="color: #cbd5e1;"></i>
                                            Belum ada data tipe kendaraan yang ditambahkan.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <span class="text-muted small">
                            Menampilkan <?= min($offset + 1, $total_data) ?> - <?= min($offset + $limit, $total_data) ?> dari <?= $total_data ?> data tipe kendaraan.
                        </span>
                        <ul class="pagination pagination-sm mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?halaman=<?= ($page - 1) ?>">Sebelumnya</a>
                                </li>
                            <?php endif; ?>

                            <?php
                            $start = max(1, $page - 2);
                            $end = min($total_halaman, $page + 2);
                            for ($i = $start; $i <= $end; $i++):
                            ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?halaman=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_halaman): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?halaman=<?= ($page + 1) ?>">Selanjutnya</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>

                </div>
            </div>

        </main>
    </div>
</body>
</html>
