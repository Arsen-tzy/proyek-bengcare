<?php
session_start();
if (empty($_SESSION['id_karyawan'])) {
    header("Location: ../login.php");
    exit;
}

include dirname(__DIR__) . "/koneksi.php";

$stmt_login = mysqli_prepare($conn, "SELECT * FROM karyawan WHERE id = ?");
mysqli_stmt_bind_param($stmt_login, "s", $_SESSION['id_karyawan']);
mysqli_stmt_execute($stmt_login);
$karyawan_login = mysqli_fetch_array(mysqli_stmt_get_result($stmt_login));

$id_edit = intval($_GET['id']);

$stmt_edit = mysqli_prepare($conn, "SELECT * FROM layanan WHERE id = ?");
mysqli_stmt_bind_param($stmt_edit, "i", $id_edit);
mysqli_stmt_execute($stmt_edit);
$data_edit = mysqli_fetch_array(mysqli_stmt_get_result($stmt_edit));

if (!$data_edit) {
    echo "Data layanan tidak ditemukan!"; exit;
}

$id_kategori_layanan = $data_edit['id_kategori_layanan'];
$nama_layanan = $data_edit['nama_layanan'];
$deskripsi = $data_edit['deskripsi'];
$estimasi_waktu = $data_edit['estimasi_waktu'];
$harga = $data_edit['harga'];
$sukses = ""; $error = "";

if (isset($_POST['simpan'])) {
    $id_kategori_layanan = $_POST['id_kategori_layanan'];
    $nama_layanan = $_POST['nama_layanan'];
    $deskripsi = $_POST['deskripsi'];
    $estimasi_waktu = $_POST['estimasi_waktu'];
    $harga = $_POST['harga'];
    $id_karyawan = $_POST['id_karyawan'];

    if ($error == "" && $id_kategori_layanan && $nama_layanan && $harga) {
        $stmt_update = mysqli_prepare($conn, "UPDATE layanan SET id_kategori_layanan=?, nama_layanan=?, deskripsi=?, estimasi_waktu=?, harga=?, id_karyawan=? WHERE id = ?");
        mysqli_stmt_bind_param($stmt_update, "isssiii", $id_kategori_layanan, $nama_layanan, $deskripsi, $estimasi_waktu, $harga, $id_karyawan, $id_edit);
        $result = mysqli_stmt_execute($stmt_update);

        if ($result) {
            $sukses = "Data layanan berhasil diperbarui!";
        } else {
            $error = "Gagal memperbarui data layanan!";
        }
    } elseif ($error == "") {
        $error = "Pastikan kolom ber-bintang terisi semua!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Layanan - BengCare</title>
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
                    <h1 class="panel-title text-white" style="font-size: 2.2rem;">Edit Data Layanan</h1>
                    <p class="panel-sub text-white-50">Perbarui rincian estimasi biaya, estimasi waktu, atau deskripsi layanan.</p>
                    <div class="panel-bar bg-white"></div>
                </div>
                <div>
                    <a href="index.php" class="btn btn-outline-light px-4 py-2 rounded-pill shadow-sm fw-bold">
                        <i class="bi bi-arrow-left me-2"></i> Kembali
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 col-lg-8">
                <div class="card-body p-4">
                    <?php if ($error) { ?>
                        <div class="alert alert-danger shadow-sm border-0" role="alert"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error; ?></div>
                    <?php } ?>

                    <?php if ($sukses) { ?>
                        <div class="alert alert-success shadow-sm border-0" role="alert"><i class="bi bi-check-circle-fill me-2"></i><?= $sukses; ?></div>
                        <script>setTimeout(function() { window.location.href = 'index.php'; }, 1500);</script>
                    <?php } ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_karyawan" value="<?= htmlspecialchars($karyawan_login['id']); ?>">

                        <input type="hidden" name="id_kategori_layanan" value="1">

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">NAMA LAYANAN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="nama_layanan" value="<?= htmlspecialchars($nama_layanan); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">DESKRIPSI</label>
                            <textarea class="form-control" name="deskripsi" rows="3"><?= htmlspecialchars($deskripsi); ?></textarea>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">ESTIMASI WAKTU</label>
                                <input type="text" class="form-control" name="estimasi_waktu" value="<?= htmlspecialchars($estimasi_waktu); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold">ESTIMASI BIAYA (Rp) <span class="text-danger">*</span></label>
                                <input type="number" min="0" class="form-control" name="harga" value="<?= htmlspecialchars($harga); ?>" required>
                            </div>
                        </div>



                        <hr class="my-4">
                        <div class="d-flex justify-content-end gap-2">
                            <button class="btn btn-primary rounded-pill px-4" type="submit" name="simpan"><i class="bi bi-check2-square"></i> Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>