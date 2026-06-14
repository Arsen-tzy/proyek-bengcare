<?php
session_start();
include "koneksi.php";

// ==========================================
// 0. CEK JIKA SUDAH LOGIN (BYPASS)
// ==========================================
// Jika Karyawan sudah login, arahkan ke Dashboard Admin
if (isset($_SESSION['id_karyawan']) || isset($_SESSION['kode'])) {
    header("Location: home/index.php");
    exit();
} 
// Jika Customer sudah login, arahkan ke Landing Page
elseif (isset($_SESSION['id_customer'])) {
    header("Location: index.php");
    exit();
}

$error = "";

// 1. PROSES LOGIN CUSTOMER//
if (isset($_POST['login_customer'])) {
    $email = mysqli_real_escape_string($conn, trim(strtolower($_POST['email'])));
    $password = $_POST['password'];

    $q_customer = "SELECT * FROM customers WHERE email = '$email'";
    $r_customer = mysqli_query($conn, $q_customer);

    if (mysqli_num_rows($r_customer) > 0) {
        $user = mysqli_fetch_assoc($r_customer);
        if ($password === $user['password']) {
            $_SESSION['id_customer'] = $user['id'];
            $_SESSION['nama'] = $user['nama'];
            
            // CUSTOMER DIARAHKAN KE SINI
            header("Location: index.php");
            exit();
        } else {
            $error = "Password Customer salah!";
        }
    } else {
        $error = "Email Customer tidak ditemukan!";
    }
}

// 2. PROSES LOGIN KARYAWAN (STAFF)
if (isset($_POST['login_staff'])) {
    $id_karyawan = mysqli_real_escape_string($conn, trim(strtoupper($_POST['id_karyawan'])));
    $password = $_POST['password'];

    $q_karyawan = "SELECT * FROM karyawan WHERE kode = '$id_karyawan'";
    $r_karyawan = mysqli_query($conn, $q_karyawan);

    if (mysqli_num_rows($r_karyawan) > 0) {
        $user = mysqli_fetch_assoc($r_karyawan);
        if ($password === $user['sandi']) {
            $_SESSION['id_karyawan'] = $user['id'];
            $_SESSION['kode'] = $user['kode']; 
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['role'] = $user['role'];
            
            header("Location: home/index.php");
            exit();
        } else {
            $error = "Password Karyawan salah!";
        }
    } else {
        $error = "ID Karyawan tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - BengCare</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(180deg, #6BA9FC 0%, #D1D6F1 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            padding: 20px;
        }

        .auth-card {
            background: #B9DEF6;
            border: 1px solid #255498;
            border-radius: 21px;
            width: 100%;
            max-width: 360px;
            padding: 40px 30px;
            box-shadow: 0px 10px 30px rgba(37, 84, 152, 0.2);
            position: relative;
        }

        .auth-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 28.8px;
            color: #255498;
            text-align: center;
            margin-bottom: 20px;
        }
        .auth-role-row {
            display: flex;
            gap: 8px;
            margin-bottom: 25px;
        }
        .auth-role-btn {
            flex: 1;
            padding: 8px 10px;
            border: 1px solid rgba(37,84,152,0.35);
            border-radius: 50px;
            background: rgba(255,255,255,0.6);
            color: #255498;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .auth-role-btn.active {
            background: #255498;
            color: #fff;
            border-color: #255498;
            box-shadow: 0 4px 10px rgba(37,84,152,0.3);
        }

        .auth-label {
            font-size: 11px;
            font-weight: 600;
            color: #1A1A1A;
            margin-bottom: 5px;
            display: block;
        }

        .auth-input-group {
            background: #FFFFFF;
            border: 0.67px solid #EDF1F3;
            box-shadow: 0px 0.67px 1.34px rgba(228, 229, 231, 0.24);
            border-radius: 6.7px;
            display: flex;
            align-items: center;
            padding: 10px 14px;
            margin-bottom: 15px;
        }
        .auth-input-group:focus-within {
            border-color: #255498;
            box-shadow: 0px 0px 0px 2px rgba(37, 84, 152, 0.2);
        }
        .auth-input-group i { color: #95989A; font-size: 14px; margin-right: 10px; }
        .auth-input { border: none; outline: none; width: 100%; background: transparent; font-size: 13px; font-weight: 500; color: #6C7278; }

        .btn-submit {
            background: #F8FAFF;
            box-shadow: 0px 0px 50px #4BAFF2;
            border-radius: 13px;
            color: #255498;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 14.18px;
            width: 100%;
            padding: 12px;
            border: none;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0px 0px 60px #4BAFF2; }

        .register-link { text-align: center; font-family: 'Poppins', sans-serif; font-size: 12.67px; font-weight: 600; color: #8B8F91; margin-top: 25px; }
        .register-link a { color: #255498; text-decoration: none; }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="mb-3 text-start">
            <a href="index.php" class="text-decoration-none fw-bold small" style="color: #255498 !important;"><i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda</a>
        </div>
        <h1 class="auth-title">Sign In</h1>

        <div class="auth-role-row">
            <button type="button" class="auth-role-btn active" id="btn-role-customer" onclick="switchRole('customer')"> Customer</button>
            <button type="button" class="auth-role-btn" id="btn-role-staff" onclick="switchRole('staff')">Karyawan</button>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger p-2 text-center" style="font-size: 12px; border-radius: 8px;">
                <i class="bi bi-exclamation-circle me-1"></i> <?= $error; ?>
            </div>
        <?php endif; ?>

        <form id="form-customer" method="POST" action="">
            <label class="auth-label">Email Pelanggan</label>
            <div class="auth-input-group">
                <i class="bi bi-envelope"></i>
                <input type="email" name="email" class="auth-input" placeholder="contoh@mail.com" required>
            </div>

            <label class="auth-label">Password</label>
            <div class="auth-input-group">
                <i class="bi bi-lock"></i>
                <input type="password" name="password" id="passCust" class="auth-input" placeholder="Password Anda" required>
                <i class="bi bi-eye-slash" onclick="togglePass('passCust', this)" style="cursor: pointer;"></i>
            </div>

            

            <button type="submit" name="login_customer" class="btn-submit">Sign In Customer</button>
        </form>

        <form id="form-staff" method="POST" action="" class="d-none">
            <label class="auth-label">ID Karyawan</label>
            <div class="auth-input-group">
                <i class="bi bi-person-badge"></i>
                <input type="text" name="id_karyawan" class="auth-input" placeholder="Contoh: KRY001" required>
            </div>

            <label class="auth-label">Password</label>
            <div class="auth-input-group">
                <i class="bi bi-lock"></i>
                <input type="password" name="password" id="passStaff" class="auth-input" placeholder="Password Karyawan" required>
                <i class="bi bi-eye-slash" onclick="togglePass('passStaff', this)" style="cursor: pointer;"></i>
            </div>

            <button type="submit" name="login_staff" class="btn-submit">Sign In Karyawan</button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php">Sign Up</a>
        </div>
    </div>

    <script>
        function switchRole(role) {
            const btnCust = document.getElementById('btn-role-customer');
            const btnStaff = document.getElementById('btn-role-staff');
            const formCust = document.getElementById('form-customer');
            const formStaff = document.getElementById('form-staff');

            if (role === 'customer') {
                btnCust.classList.add('active');
                btnStaff.classList.remove('active');
                formCust.classList.remove('d-none');
                formStaff.classList.add('d-none');
            } else {
                btnStaff.classList.add('active');
                btnCust.classList.remove('active');
                formStaff.classList.remove('d-none');
                formCust.classList.add('d-none');
            }
        }

        function togglePass(inputId, iconElement) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                iconElement.classList.replace('bi-eye-slash', 'bi-eye');
            } else {
                input.type = 'password';
                iconElement.classList.replace('bi-eye', 'bi-eye-slash');
            }
        }
    </script>
</body>
</html>