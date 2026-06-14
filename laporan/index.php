<?php
session_start();
if (empty($_SESSION['id_karyawan']) && empty($_SESSION['kode'])) {
    header("Location: ../login.php");
    exit;
}

include dirname(__DIR__) . "/koneksi.php";

$id_karyawan = $_SESSION['id_karyawan'] ?? $_SESSION['kode'];
$query_login = "SELECT * FROM karyawan WHERE id = '$id_karyawan' OR kode = '$id_karyawan'";
$result_login = mysqli_query($conn, $query_login);
$karyawan_login = mysqli_fetch_array($result_login);

$search        = isset($_GET['search']) ? $_GET['search'] : '';
$filter_dari   = isset($_GET['dari'])   && $_GET['dari']   !== '' ? $_GET['dari']   : date('Y-m-d');
$filter_sampai = isset($_GET['sampai']) && $_GET['sampai'] !== '' ? $_GET['sampai'] : date('Y-m-d');
$filter_status = isset($_GET['status']) && $_GET['status'] !== '' ? $_GET['status'] : '';

$search_esc        = mysqli_real_escape_string($conn, $search);
$filter_dari_esc   = mysqli_real_escape_string($conn, $filter_dari);
$filter_sampai_esc = mysqli_real_escape_string($conn, $filter_sampai);
$filter_status_esc = mysqli_real_escape_string($conn, $filter_status);

// Alias 'p' diganti ke alias tabel/view secara umum agar fleksibel
$where_tanggal = "DATE(tanggal) BETWEEN '$filter_dari_esc' AND '$filter_sampai_esc'";
$where_status  = $filter_status_esc !== '' ? "AND status = '$filter_status_esc'" : '';
$where_search  = $search_esc !== '' ? "AND (nama_customer LIKE '%$search_esc%' OR merk LIKE '%$search_esc%' OR id LIKE '%$search_esc%')" : '';

// ─── STATISTIK ─────────────────
// Masih pakai tabel asli untuk statistik
$join_clause = "LEFT JOIN customers c ON p.customer_id = c.id LEFT JOIN kendaraan knd ON p.kendaraan_id = knd.id";
$q_pendapatan = "SELECT SUM(p.total) as pendapatan FROM pemesanan p $join_clause WHERE DATE(p.tanggal) BETWEEN '$filter_dari_esc' AND '$filter_sampai_esc' AND p.status = 'selesai'";
$data_pendapatan = mysqli_fetch_assoc(mysqli_query($conn, $q_pendapatan));
$total_pendapatan = $data_pendapatan['pendapatan'] ?? 0;

$q_stat = "SELECT
               SUM(CASE WHEN p.status = 'belum'   THEN 1 ELSE 0 END) AS belum,
               SUM(CASE WHEN p.status = 'selesai' THEN 1 ELSE 0 END) AS selesai,
               SUM(CASE WHEN p.status = 'batal'   THEN 1 ELSE 0 END) AS batal
           FROM pemesanan p $join_clause WHERE DATE(p.tanggal) BETWEEN '$filter_dari_esc' AND '$filter_sampai_esc'";
$stat   = mysqli_fetch_assoc(mysqli_query($conn, $q_stat));

$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Menghitung data menggunakan VIEW
$q_count = "SELECT COUNT(*) as total FROM vw_pesanan WHERE $where_tanggal $where_status $where_search";
$total_data = mysqli_fetch_assoc(mysqli_query($conn, $q_count))['total'];
$total_pages = ceil($total_data / $limit);

$url_params = "&search=" . urlencode($search) . "&dari=" . urlencode($filter_dari) . "&sampai=" . urlencode($filter_sampai) . "&status=" . urlencode($filter_status);

// ─── DAFTAR PESANAN MENGGUNAKAN VIEW & FUNCTION ───────────────────────────
// Pemanggilan vw_pesanan (View) dan hitung_pajak() (Function)
$q_pesanan = "SELECT *, hitung_pajak(total) AS pajak_ppn
              FROM vw_pesanan
              WHERE $where_tanggal $where_status $where_search
              ORDER BY tanggal DESC 
              LIMIT $limit OFFSET $offset";
$r_pesanan = mysqli_query($conn, $q_pesanan);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - Admin BengCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css?v=<?= time(); ?>">
</head>

<body id="page-staff" style="background: linear-gradient(180deg, #6BA9FC 0%, #D1D6F1 100%) !important; background-attachment: fixed !important; min-height: 100vh;">

    <div class="staff-wrap" style="background-color: transparent !important;">
        <?php include "../layout/sidebar.php"; ?>

        <main class="staff-main p-4 p-md-5" style="background-color: transparent !important;">
            <div class="mb-4">
                <h1 class="panel-title text-white" style="font-size: 2.2rem;">Laporan Keuangan</h1>
                <p class="panel-sub text-white-50">Pantau pendapatan dan statistik pesanan servis berdasarkan periode waktu.</p>
                <div class="panel-bar bg-white"></div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 p-4 bg-white" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 1.5rem;"><i class="bi bi-cash-stack"></i></div>
                            <div>
                                <div class="text-muted fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">TOTAL PENDAPATAN</div>
                                <h2 class="fw-bolder text-success mb-0">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h2>
                            </div>
                        </div>
                        <div class="text-secondary small mt-auto border-top pt-2"><i class="bi bi-info-circle me-1"></i> Dari pesanan berstatus Selesai.</div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row g-3 h-100">
                        <div class="col-4"><div class="card bg-white border-0 shadow-sm rounded-4 h-100 p-3 text-center d-flex flex-column justify-content-center" style="border-bottom: 4px solid #ffc107 !important;"><div class="text-muted fw-bold mb-1" style="font-size: 0.7rem;">ANTRIAN</div><h3 class="fw-bolder text-warning mb-0"><?= (int)($stat['belum'] ?? 0) ?></h3></div></div>
                        <div class="col-4"><div class="card bg-white border-0 shadow-sm rounded-4 h-100 p-3 text-center d-flex flex-column justify-content-center" style="border-bottom: 4px solid #198754 !important;"><div class="text-muted fw-bold mb-1" style="font-size: 0.7rem;">SELESAI</div><h3 class="fw-bolder text-success mb-0"><?= (int)($stat['selesai'] ?? 0) ?></h3></div></div>
                        <div class="col-4"><div class="card bg-white border-0 shadow-sm rounded-4 h-100 p-3 text-center d-flex flex-column justify-content-center" style="border-bottom: 4px solid #dc3545 !important;"><div class="text-muted fw-bold mb-1" style="font-size: 0.7rem;">BATAL</div><h3 class="fw-bolder text-danger mb-0"><?= (int)($stat['batal'] ?? 0) ?></h3></div></div>
                    </div>
                </div>
            </div>

            <div class="card bg-white border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <form method="GET" action="" class="row g-3 align-items-end">
                        <div class="col-md-12 mb-2">
                            <label class="form-label text-muted fw-bold" style="font-size: 0.8rem;">PENCARIAN DATA</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                                <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Cari Nama Pelanggan, Merk Kendaraan, atau ID..." value="<?= htmlspecialchars($search) ?>">
                            </div>
                        </div>
                        <div class="col-md-3"><label class="form-label text-muted fw-bold" style="font-size: 0.8rem;">DARI TANGGAL</label><input type="date" name="dari" class="form-control" value="<?= htmlspecialchars($filter_dari) ?>"></div>
                        <div class="col-md-3"><label class="form-label text-muted fw-bold" style="font-size: 0.8rem;">SAMPAI TANGGAL</label><input type="date" name="sampai" class="form-control" value="<?= htmlspecialchars($filter_sampai) ?>"></div>
                        <div class="col-md-3">
                            <label class="form-label text-muted fw-bold" style="font-size: 0.8rem;">STATUS PESANAN</label>
                            <select name="status" class="form-select">
                                <option value="">Semua Status</option>
                                <option value="belum"   <?= $filter_status === 'belum'   ? 'selected' : '' ?>>Antrian / Belum Selesai</option>
                                <option value="selesai" <?= $filter_status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                <option value="batal"   <?= $filter_status === 'batal'   ? 'selected' : '' ?>>Dibatalkan</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-2"></i>Terapkan</button>
                            <a href="laporan.php" class="btn btn-light border w-100"><i class="bi bi-arrow-clockwise me-1"></i>Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card bg-white border-0 shadow-sm rounded-4 mb-5">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-center">
                    <div class="card-title text-blue fw-bold m-0"><i class="bi bi-table me-2"></i>Rincian Pesanan</div>
                </div>
                <div class="card-body p-4 mt-2">
                    <?php if (mysqli_num_rows($r_pesanan) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="width:100%">
                            <thead style="background-color: #f8fafc;">
                                <tr class="text-muted" style="font-size: 0.8rem; letter-spacing: 1px;">
                                    <th class="rounded-start ps-3">ID</th>
                                    <th>TANGGAL</th>
                                    <th>CUSTOMER</th>
                                    <th>KENDARAAN</th>
                                    <th>TOTAL (Rp)</th>
                                    <th>STATUS</th>
                                    <th class="rounded-end text-center">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($r_pesanan)): 
                                    $status_lower = strtolower($row['status']);
                                    $badge_class = match($status_lower) { 'selesai' => 'bg-success', 'batal' => 'bg-danger', default => 'bg-warning text-dark' };
                                ?>
                                    <tr>
                                        <td class="ps-3 fw-bold text-dark">#<?= htmlspecialchars($row['id']) ?></td>
                                        <td class="text-secondary small"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
                                        <td class="fw-bold text-dark"><?= htmlspecialchars($row['nama_customer'] ?? 'Pelanggan') ?></td>
                                        <td class="text-secondary small"><?= htmlspecialchars($row['merk'] . ' ' . $row['tipe']) ?></td>
                                        <td>
                                            <div class="fw-bold text-success">Rp <?= number_format($row['total'], 0, ',', '.') ?></div>
                                            <div class="text-muted" style="font-size: 0.7rem;">+ PPN: Rp <?= number_format($row['pajak_ppn'], 0, ',', '.') ?></div>
                                        </td>
                                        <td><span class="badge <?= $badge_class ?> rounded-pill px-3"><?= ucfirst($row['status']) ?></span></td>
                                        <td class="text-center">
                                            <a href="../pesanan/detail.php?id=<?= $row['id'] ?>" class="btn btn-light btn-sm border rounded-3"><i class="bi bi-eye text-primary"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($total_pages > 1): ?>
                    <nav class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Menampilkan Halaman <?= $page ?> dari <?= $total_pages ?></span>
                        <ul class="pagination pagination-sm mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?= ($page-1) ?><?= $url_params ?>">Sebelumnya</a></li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?= ($i == $page) ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?><?= $url_params ?>"><?= $i ?></a></li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?= ($page+1) ?><?= $url_params ?>">Selanjutnya</a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>

                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">Tidak ada pesanan ditemukan.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>