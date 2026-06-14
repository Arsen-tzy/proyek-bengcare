<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$host     = "localhost";
$username = "root";
$password = "";
$database = "bengcare_db"; 
$port     = 3306; 

$conn = mysqli_connect($host, $username, $password, $database, $port); 

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}


function isStaffLoggedIn() {
    return isset($_SESSION['id_karyawan']);
}

function requireStaffLogin() {
    if (!isStaffLoggedIn()) {
        header("Location: ../login.php");
        exit();
    }
}

function isCustomerLoggedIn() {
    return isset($_SESSION['id_customer']);
}


function uploadImage($file, $target_dir = "../assets/img/") {
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return array('success' => false, 'message' => 'File yang diupload bukan gambar.');
    }
    
    if ($file["size"] > 2000000) {
        return array('success' => false, 'message' => 'File terlalu besar. Maksimal 2MB.');
    }
    
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        return array('success' => false, 'message' => 'Hanya format JPG, JPEG, dan PNG yang diizinkan.');
    }
    
    $new_filename = uniqid('bengcare_') . '.' . $imageFileType;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return array('success' => true, 'filename' => $new_filename);
    } else {
        return array('success' => false, 'message' => 'Terjadi kesalahan sistem saat mengupload gambar.');
    }
}

function deleteImage($filename, $dir = "../assets/img/") {
    if ($filename && file_exists($dir . $filename)) {
        unlink($dir . $filename);
        return true;
    }
    return false;
}
?>