<?php
session_start();
if (empty($_SESSION['id_karyawan'])) { header("Location: ../login.php"); exit; }
include dirname(__DIR__) . "/koneksi.php";

// Aksi Ubah Status
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id_pemesanan = intval($_GET['id']);
    $aksi = $_GET['aksi'];
    
    if ($aksi == 'selesaikan') {
        mysqli_query($conn, "UPDATE pemesanan SET status = 'selesai' WHERE id = '$id_pemesanan'");
    } elseif ($aksi == 'batalkan') {
        mysqli_query($conn, "UPDATE pemesanan SET status = 'batal' WHERE id = '$id_pemesanan'");
    }
    header("Location: index.php"); exit;
}

// Logika Filter
$tgl_awal = $_GET['tanggal_awal'] ?? '';
$tgl_akhir = $_GET['tanggal_akhir'] ?? '';

if (!empty($tgl_awal) && !empty($tgl_akhir)) {
    $tgl_awal_safe = mysqli_real_escape_string($conn, $tgl_awal);
    $tgl_akhir_safe = mysqli_real_escape_string($conn, $tgl_akhir);
    $where_kondisi = "WHERE DATE(p.tanggal) BETWEEN '$tgl_awal_safe' AND '$tgl_akhir_safe'";
} else {
    $where_kondisi = "WHERE DATE(p.tanggal) = CURDATE()"; // Default Hari Ini
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Booking - BengCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?= time(); ?>">
</head>
<body id="page-staff" style="background: linear-gradient(180deg, #6BA9FC 0%, #D1D6F1 100%) !important; background-attachment: fixed !important; min-height: 100vh;">
    <div class="staff-wrap" style="background-color: transparent !important;">
        <?php include "../layout/sidebar.php"; ?>

        <main class="staff-main p-4 p-md-5" style="background-color: transparent !important;">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
                <div>
                    <h1 class="panel-title text-white" style="font-size: 2.2rem;">Manajemen Pesanan Servis</h1>
                    <p class="panel-sub text-white-50">Kelola antrian, update status pengerjaan, dan detail pemesanan di sini.</p>
                    <div class="panel-bar bg-white"></div>
                </div>
                <div>
                    <a href="create.php" class="btn btn-primary px-4 py-2 rounded-pill shadow-sm fw-bold">
                        <i class="bi bi-plus-lg me-2"></i> Input Pesanan
                    </a>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    
                    <form method="GET" action="index.php" class="row g-2 mb-4 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">DARI TANGGAL</label>
                            <input type="date" name="tanggal_awal" class="form-control" value="<?= $tgl_awal; ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small fw-bold">SAMPAI TANGGAL</label>
                            <input type="date" name="tanggal_akhir" class="form-control" value="<?= $tgl_akhir; ?>">
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-1"></i> Filter</button>
                            <a href="index.php" class="btn btn-outline-secondary w-100">Reset</a>
                        </div>
                        <div class="col-12 mt-2">
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Menampilkan data: <?= empty($tgl_awal) ? 'Hari Ini' : date('d M', strtotime($tgl_awal)) . ' - ' . date('d M Y', strtotime($tgl_akhir)); ?></small>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table id="tblBooking" class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th>Tanggal & Waktu</th>
                                    <th>Customer & Kendaraan</th>
                                    <th>Total</th>
                                    <th class="text-center">Status</th>
                                    <th width="20%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT p.*, c.nama AS nama_customer, knd.merk, knd.tipe 
                                          FROM pemesanan p 
                                          LEFT JOIN customers c ON p.customer_id = c.id 
                                          LEFT JOIN kendaraan knd ON p.kendaraan_id = knd.id 
                                          $where_kondisi ORDER BY p.id DESC";
                                $result = mysqli_query($conn, $query);

                                while ($data = mysqli_fetch_array($result)) {
                                    $status = $data['status'];
                                    $badge = ($status == 'belum') ? 'bg-warning text-dark' : (($status == 'selesai') ? 'bg-success' : 'bg-danger');
                                    $status_text = ($status == 'belum') ? 'Antrian' : ucfirst($status);
                                ?>
                                    <tr>
                                        <td class="text-center fw-bold text-dark">#<?= $data['id']; ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($data['tanggal'])); ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($data['nama_customer']); ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($data['merk'] . ' ' . $data['tipe']); ?></small><br>
                                            <small class="badge bg-light text-dark border"><i class="bi bi-wallet2 me-1"></i><?= htmlspecialchars($data['metode_pembayaran'] ?? 'Bayar di tempat'); ?></small>
                                        </td>
                                        <td class="fw-semibold text-success">Rp <?= number_format($data['total'], 0, ',', '.'); ?></td>
                                        <td class="text-center"><span class="badge <?= $badge; ?> rounded-pill px-3 py-2"><?= $status_text; ?></span></td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <a href="index.php?aksi=selesaikan&id=<?= $data['id']; ?>" class="btn btn-success btn-sm <?= ($status != 'belum') ? 'disabled' : ''; ?>" title="Selesai" onclick="return confirm('Tandai servis selesai?')"><i class="bi bi-check-lg"></i></a>
                                                <a href="index.php?aksi=batalkan&id=<?= $data['id']; ?>" class="btn btn-danger btn-sm <?= ($status != 'belum') ? 'disabled' : ''; ?>" title="Batal" onclick="return confirm('Batalkan pesanan?')"><i class="bi bi-x-lg"></i></a>
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

    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.3.8/js/dataTables.bootstrap5.js"></script>
    <script>
        $(document).ready(function(){ 
            $('#tblBooking').DataTable({
                "order": [[ 0, "desc" ]],
                "pageLength": 10,
                "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Semua"]]
            }); 
        });
    </script>
</body>
</html>