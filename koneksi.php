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


// Fungsi untuk upload file tipe kendaraan kustom
function uploadFile($file, $target_dir = "../assets/") {
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . basename($file["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Cek apakah file adalah gambar
    if (isset($_POST["submit"]) || isset($_POST["simpan"])) {
        $check = getimagesize($file["tmp_name"]);
        if ($check !== false) {
            $uploadOk = 1;
        } else {
            return array('success' => false, 'message' => 'File bukan gambar.');
        }
    }
    
    // Cek ukuran file (max 5MB)
    if ($file["size"] > 5000000) {
        return array('success' => false, 'message' => 'File terlalu besar. Maksimal 5MB.');
    }
    
    // Hanya allow format tertentu
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        return array('success' => false, 'message' => 'Hanya format JPG, JPEG, PNG & GIF yang diizinkan.');
    }
    
    // Generate nama file unik
    $new_filename = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return array('success' => true, 'filename' => $new_filename);
    } else {
        return array('success' => false, 'message' => 'Error saat upload file.');
    }
}
?>