<?php
session_start();
include dirname(__DIR__) . "/koneksi.php";

// Pastikan yang mengakses adalah Karyawan
if (empty($_SESSION['id_karyawan'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Hapus data dari database
    $delete_query = "DELETE FROM karyawan WHERE id=$id";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['message'] = "Data karyawan berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Gagal menghapus data: " . mysqli_error($conn);
    }
}

// Kembali ke halaman index karyawan
header("Location: index.php");
exit();
?>
