<?php
session_start();

// Koneksi ke database
include "koneksi.php"; // Sesuaikan lokasi file koneksi.php

// Jika sudah login, langsung arahkan ke index
if (isset($_SESSION['id_karyawan']) || isset($_SESSION['id_customer'])) {
    header("Location: index.php");
    exit();
}

$error = "";
$sukses = "";

// PROSES REGISTER CUSTOMER
if (isset($_POST['register'])) {
    // Tangkap data dari form dan cegah SQL Injection
    $nama     = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $email    = mysqli_real_escape_string($conn, trim(strtolower($_POST['email'])));
    $hp       = mysqli_real_escape_string($conn, trim($_POST['hp']));
    $password = mysqli_real_escape_string($conn, $_POST['password']); // Idealnya gunakan password_hash() untuk keamanan

    // 1. Cek apakah email sudah terdaftar
    $cek_email = mysqli_query($conn, "SELECT email FROM customers WHERE email = '$email'");
    
    if (mysqli_num_rows($cek_email) > 0) {
        $error = "Email sudah terdaftar! Silakan gunakan email lain atau Sign In.";
    } else {
        // 2. Jika belum, masukkan data ke tabel customers
        $query_insert = "INSERT INTO customers (nama, email, hp, password) VALUES ('$nama', '$email', '$hp', '$password')";
        
        if (mysqli_query($conn, $query_insert)) {
            $sukses = "Akun berhasil dibuat! Silakan Sign In.";
        } else {
            $error = "Terjadi kesalahan sistem saat mendaftar!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - BengCare</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        /* Konversi CSS Figma ke Custom CSS Flexbox */
        body {
            /* Background gradient langit biru sesuai request */
            background: linear-gradient(180deg, #6BA9FC 0%, #D1D6F1 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            padding: 20px;
        }

        /* Card Utama */
        .auth-card {
            background: #B9DEF6;
            border: 1px solid #255498;
            border-radius: 21px;
            width: 100%;
            max-width: 380px;
            padding: 35px 30px;
            box-shadow: 0px 10px 30px rgba(37, 84, 152, 0.2);
            position: relative;
        }

        /* Sign Up Title */
        .auth-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 28.8px;
            color: #255498;
            text-align: center;
            margin-bottom: 25px;
            line-height: 1.2;
        }

        /* Label Form */
        .auth-label {
            font-family: 'Inter', sans-serif;
            font-size: 11.5px;
            font-weight: 600;
            color: #255498;
            margin-bottom: 5px;
            display: block;
        }

        /* Input Area */
        .auth-input-group {
            background: #FFFFFF;
            border: 0.67px solid #EDF1F3;
            box-shadow: 0px 0.67px 1.34px rgba(228, 229, 231, 0.24);
            border-radius: 10px;
            display: flex;
            align-items: center;
            padding: 12px 14px;
            margin-bottom: 16px;
            transition: border-color 0.3s;
        }
        
        .auth-input-group:focus-within {
            border-color: #255498;
            box-shadow: 0px 0px 0px 2px rgba(37, 84, 152, 0.2);
        }

        .auth-input-group i {
            color: #ACB5BB;
            font-size: 16px;
            margin-right: 12px;
        }

        .auth-input {
            border: none;
            outline: none;
            width: 100%;
            background: transparent;
            font-size: 13.5px;
            font-weight: 500;
            color: #6C7278;
        }
        .auth-input::placeholder { color: #ACB5BB; }

        /* Submit Button */
        .btn-submit {
            background: #F8FAFF;
            box-shadow: 0px 0px 30px rgba(75, 175, 242, 0.6);
            border-radius: 11px;
            color: #255498;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 14px;
            width: 150px;
            padding: 11px;
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
            display: block;
            margin: 25px auto 10px auto;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0px 0px 40px rgba(75, 175, 242, 0.8);
        }

        /* Switch Link */
        .switch-link {
            text-align: center;
            font-family: 'Poppins', sans-serif;
            font-size: 12.67px;
            font-weight: 600;
            color: #8B8F91;
            margin-top: 15px;
        }
        .switch-link a {
            color: #255498;
            text-decoration: none;
        }
        .switch-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    

    <div class="auth-card">
        <div class="mb-3 text-start">
            <a href="index.php" class="text-decoration-none fw-bold small" style="color: #255498 !important;"><i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda</a>
        </div>
        <h1 class="auth-title">Sign Up</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger p-2 text-center" style="font-size: 12px; border-radius: 8px;">
                <i class="bi bi-exclamation-circle me-1"></i> <?= $error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($sukses)): ?>
            <div class="alert alert-success p-2 text-center" style="font-size: 12px; border-radius: 8px;">
                <i class="bi bi-check-circle me-1"></i> <?= $sukses; ?>
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 2000); // Otomatis pindah ke login.php setelah 2 detik
            </script>
        <?php endif; ?>

        <form method="POST" action="">
            <label class="auth-label">Full Name</label>
            <div class="auth-input-group">
                <i class="bi bi-person-circle"></i>
                <input type="text" name="nama" class="auth-input" placeholder="Nama Lengkap" required autocomplete="off">
            </div>

            <label class="auth-label">Email</label>
            <div class="auth-input-group">
                <i class="bi bi-envelope"></i>
                <input type="email" name="email" class="auth-input" placeholder="alamat@email.com" required autocomplete="off">
            </div>
            
            <label class="auth-label">Nomor WhatsApp / HP</label>
            <div class="auth-input-group">
                <i class="bi bi-telephone"></i>
                <input type="number" name="hp" class="auth-input" placeholder="0812xxxx" required autocomplete="off">
            </div>

            <label class="auth-label">Set Password</label>
            <div class="auth-input-group">
                <i class="bi bi-lock"></i>
                <input type="password" name="password" id="password" class="auth-input" placeholder="Password" required>
                <i class="bi bi-eye-slash" id="togglePassword" style="cursor: pointer; margin-right: 0; margin-left: 10px;"></i>
            </div>

            <button type="submit" name="register" class="btn-submit">Continue</button>
        </form>

        <div class="switch-link">
            Already have an account? <a href="login.php">Sign In</a>
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    </script>
</body>
</html>