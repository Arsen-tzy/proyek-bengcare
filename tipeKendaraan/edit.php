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
$stmt_edit = mysqli_prepare($conn, "SELECT * FROM tipe_kendaraan WHERE id = ?");
mysqli_stmt_bind_param($stmt_edit, "i", $id_edit);
mysqli_stmt_execute($stmt_edit);
$data_edit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_edit));

if (!$data_edit) {
    $_SESSION['error'] = "Tipe kendaraan tidak ditemukan!";
    header("Location: index.php");
    exit;
}

$jenis = $data_edit['jenis'];
$merk = $data_edit['merk'];
$tipe = $data_edit['tipe'];
$gambar_lama = $data_edit['gambar'];
$error = "";

if (isset($_POST['simpan'])) {
    $jenis = $_POST['jenis'];
    $merk = trim($_POST['merk']);
    $tipe = trim($_POST['tipe']);
    
    if (empty($merk) || empty($tipe)) {
        $error = "Mohon lengkapi merk dan tipe kendaraan!";
    } else {
        $filename = $gambar_lama;
        
        // Cek jika ada file baru yang diunggah
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = uploadFile($_FILES['gambar'], "../assets/");
            if ($upload['success']) {
                $filename = $upload['filename'];
                // Hapus gambar lama jika ada
                if (!empty($gambar_lama) && file_exists("../assets/" . $gambar_lama)) {
                    unlink("../assets/" . $gambar_lama);
                }
            } else {
                $error = $upload['message'];
            }
        }
        
        if (empty($error)) {
            $stmt_update = mysqli_prepare($conn, "UPDATE tipe_kendaraan SET jenis=?, merk=?, tipe=?, gambar=? WHERE id=?");
            mysqli_stmt_bind_param($stmt_update, "ssssi", $jenis, $merk, $tipe, $filename, $id_edit);
            $result = mysqli_stmt_execute($stmt_update);
            
            if ($result) {
                $_SESSION['message'] = "Berhasil memperbarui tipe kendaraan!";
                header("Location: index.php");
                exit;
            } else {
                $error = "Gagal memperbarui data tipe kendaraan: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tipe Kendaraan - BengCare</title>
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
                    <h1 class="panel-title text-white" style="font-size: 2.2rem;">Edit Tipe Kendaraan</h1>
                    <p class="panel-sub text-white-50">Perbarui rincian merk, tipe, atau foto kendaraan.</p>
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
                        <div class="alert alert-danger shadow-sm border-0" role="alert"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error); ?></div>
                    <?php } ?>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">JENIS KENDARAAN <span class="text-danger">*</span></label>
                            <select class="form-control form-select" name="jenis" required>
                                <option value="Motor" <?= $jenis == 'Motor' ? 'selected' : '' ?>>Motor</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">MERK / BRAND <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="merk" value="<?= htmlspecialchars($merk); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">TIPE <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="tipe" value="<?= htmlspecialchars($tipe); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold">GAMBAR KENDARAAN</label>
                            <?php if (!empty($gambar_lama)): ?>
                                <div class="mb-2">
                                    <?php 
                                    $img_src = "../assets/" . $gambar_lama;
                                    if (!file_exists($img_src) && file_exists("../assets/img/" . $gambar_lama)) {
                                        $img_src = "../assets/img/" . $gambar_lama;
                                    }
                                    ?>
                                    <img src="<?= $img_src; ?>" class="rounded" style="width: 120px; height: 90px; object-fit: cover; border: 1px solid #cbd5e1;">
                                    <span class="text-muted d-block small">Gambar Saat Ini</span>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" name="gambar" accept="image/*">
                            <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle me-1"></i> Pilih file baru jika ingin mengganti gambar. Maksimal 5MB.</small>
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
