<?php
session_start();
include dirname(__DIR__) . "/koneksi.php";

// Pastikan yang mengakses adalah Karyawan
if (empty($_SESSION['id_karyawan'])) {
    header("Location: ../login.php");
    exit();
}

// Cek apakah ada ID layanan yang dikirimkan
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Ambil data gambar sebelum menghapus dari database
    $result = mysqli_query($conn, "SELECT gambar FROM layanan WHERE id=$id");
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $gambar = $row['gambar'];
        
        // Hapus data dari database
        $delete_query = "DELETE FROM layanan WHERE id=$id";
        if (mysqli_query($conn, $delete_query)) {
            // Hapus file gambar fisik jika ada
            if ($gambar && file_exists("../assets/img/" . $gambar)) {
                unlink("../assets/img/" . $gambar);
            }
            // Set Flash Message Sukses
            $_SESSION['message'] = "Data layanan berhasil dihapus!";
        } else {
            // Set Flash Message Error
            $_SESSION['error'] = "Gagal menghapus data: " . mysqli_error($conn);
        }
    }
}

// Kembali ke halaman index layanan
header("Location: index.php");
exit();
?>
