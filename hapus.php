<?php
session_start();
include dirname(__DIR__) . "/koneksi.php";

if (empty($_SESSION['id_karyawan'])) {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $result = mysqli_query($conn, "SELECT gambar FROM layanan WHERE id=$id");
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $gambar = $row['gambar'];
        
        // Hapus data dari database
        $delete_query = "DELETE FROM layanan WHERE id=$id";
        if (mysqli_query($conn, $delete_query)) {
            if ($gambar && file_exists("../assets/img/" . $gambar)) {
                unlink("../assets/img/" . $gambar);
            }
            $_SESSION['message'] = "Data layanan berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus data: " . mysqli_error($conn);
        }
    }
}

header("Location: index.php");
exit();
?>