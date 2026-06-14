<?php
session_start();
include dirname(__DIR__) . "/koneksi.php";

if (empty($_SESSION['id_karyawan'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Ambil data gambar sebelum menghapus dari database
    $result = mysqli_query($conn, "SELECT gambar FROM tipe_kendaraan WHERE id=$id");
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $gambar = $row['gambar'];
        
        // Hapus data dari database
        $delete_query = "DELETE FROM tipe_kendaraan WHERE id=$id";
        if (mysqli_query($conn, $delete_query)) {
            // Hapus file gambar fisik jika ada
            if ($gambar && file_exists("../assets/" . $gambar)) {
                unlink("../assets/" . $gambar);
            }
            $_SESSION['message'] = "Data tipe kendaraan berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus data: " . mysqli_error($conn);
        }
    }
}

header("Location: index.php");
exit();
?>
