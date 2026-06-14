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

// Ambil ID dari URL
$id_edit = mysqli_real_escape_string($conn, $_GET['id']);
$query_edit = "SELECT * FROM kategori_layanan WHERE id = '$id_edit'";
$result_edit = mysqli_query($conn, $query_edit);
$data_edit = mysqli_fetch_array($result_edit);

if (!$data_edit) {
    echo "Data kategori tidak ditemukan!";
    exit;
}

$nama_kategori = $data_edit['nama_kategori'];
$sukses = "";
$error = "";

// PROSES UPDATE
if (isset($_POST['simpan'])) {
    $nama_kategori = mysqli_real_escape_string($conn, trim($_POST['nama_kategori']));
    $id_karyawan   = mysqli_real_escape_string($conn, $_POST['id_karyawan']);

    if ($nama_kategori) {
        // Update data termasuk siapa yang terakhir kali mengedit (id_karyawan)
        $query = "UPDATE kategori_layanan SET nama_kategori='$nama_kategori', id_karyawan='$id_karyawan' WHERE id = '$id_edit'";
        $result = mysqli_query($conn, $query);

        if ($result) {
            $sukses = "Data kategori layanan berhasil diperbarui!";
            $data_edit['nama_kategori'] = $nama_kategori; // Update tampilan
        } else {
            $error = "Gagal memperbarui kategori layanan!";
        }
    } else {
        $error = "Nama kategori tidak boleh kosong!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kategori - BengCare</title>
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
                    <h1 class="panel-title text-white" style="font-size: 2.2rem;">Edit Kategori Servis</h1>
                    <p class="panel-sub text-white-50">Perbarui informasi kategori layanan servis.</p>
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
                        <script>
                            setTimeout(function() {
                                window.location.href = 'index.php';
                            }, 1500);
                        </script>
                    <?php } ?>

                    <form action="" method="POST">
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">DEDIT OLEH</label>
                            <input type="text" class="form-control bg-light" readonly value="<?= htmlspecialchars($karyawan_login['nama']); ?>">
                            <input type="hidden" name="id_karyawan" value="<?= htmlspecialchars($karyawan_login['id']); ?>">
                        </div>

                        <div class="mb-4">
                            <label for="nama_kategori" class="form-label text-muted small fw-bold">NAMA KATEGORI <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" value="<?= htmlspecialchars($nama_kategori); ?>" required>
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