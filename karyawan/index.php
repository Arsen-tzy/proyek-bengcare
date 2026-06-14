<?php
session_start();
if (empty($_SESSION['id_karyawan'])) {
    header("Location: ../login.php");
    exit;
}

include dirname(__DIR__) . "/koneksi.php";

$query_login = "SELECT * FROM karyawan WHERE id = '$_SESSION[id_karyawan]'";
$result_login = mysqli_query($conn, $query_login);
$karyawan_login = mysqli_fetch_array($result_login);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Karyawan - BengCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?= time(); ?>">
</head>
<body id="page-staff" style="background: linear-gradient(180deg, #6BA9FC 0%, #D1D6F1 100%) !important; background-attachment: fixed !important; min-height: 100vh;">
    <div class="staff-wrap" style="background-color: transparent !important;">
        <?php include "../layout/sidebar.php"; ?>

        <main class="staff-main p-4 p-md-5" style="background-color: transparent !important;">
            
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <div>
                    <h1 class="panel-title text-white" style="font-size: 2.2rem;">Data Karyawan</h1>
                    <p class="panel-sub text-white-50">Kelola informasi karyawan bengkel, username, password, dan jabatan.</p>
                    <div class="panel-bar bg-white"></div>
                </div>
                <div>
                    <a href="create.php" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm fw-bold">
                        <i class="bi bi-plus-lg me-2"></i> Tambah Karyawan
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
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="text-center" width="8%">NO</th>
                                    <th scope="col">ID KARYAWAN</th>
                                    <th scope="col">NAMA LENGKAP</th>
                                    <th scope="col">ROLE / JABATAN</th>
                                    <th scope="col" class="text-center" width="20%">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT * FROM karyawan ORDER BY id ASC";
                                $result = mysqli_query($conn, $query);
                                $no = 1;

                                while ($data = mysqli_fetch_array($result)) {
                                ?>
                                    <tr>
                                        <th scope="row" class="text-center text-dark"><?= $no++; ?></th>
                                        <td><span class="badge bg-secondary rounded-pill px-3 py-2"><?= htmlspecialchars($data['kode']); ?></span></td>
                                        <td class="fw-semibold text-dark"><?= htmlspecialchars(ucwords($data['nama'])); ?></td>
                                        <td class="text-secondary"><?= htmlspecialchars(ucfirst($data['role'])); ?></td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="edit.php?id=<?= $data['id']; ?>" class="btn btn-warning btn-sm rounded-3" title="Edit">
                                                    <i class="bi bi-pencil-square text-dark"></i>
                                                </a>
                                                <a href="hapus.php?id=<?= $data['id']; ?>" class="btn btn-danger btn-sm rounded-3" title="Hapus" onclick="return confirm('Yakin ingin menghapus karyawan ini?');">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>