<?php
session_start();
include "../config/koneksi.php";

$error = "";
$success = "";

if(isset($_POST['register'])){
    $nama_lengkap = mysqli_real_escape_string($conn, trim($_POST['nama_lengkap']));
    $username     = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password     = $_POST['password'];
    $konfirmasi   = $_POST['konfirmasi_password'];

    if($password !== $konfirmasi){
        $error = "Konfirmasi password tidak cocok!";
    } else {
        $password_encrypted = md5($password);
        
        // Cek apakah username sudah ada
        $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
        if(mysqli_num_rows($cek_user) > 0){
            $error = "Username sudah terdaftar, gunakan yang lain!";
        } else {
            // Simpan ke database
            $query = mysqli_query($conn, "INSERT INTO users (nama_lengkap, username, password) VALUES ('$nama_lengkap', '$username', '$password_encrypted')");
            if($query){
                $success = "Registrasi Berhasil! Silakan login.";
            } else {
                $error = "Gagal mendaftarkan akun, coba lagi.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I-CALM | Registrasi Akun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        :root {
            --bg-dark: #0f172a;
            --bg-card: #1e293b;
            --primary-brand: #38bdf8;
            --accent-blue: #2563eb;
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at top right, #1e3a8a 0%, #0f172a 70%);
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
            color: var(--text-light);
        }

        .register-card {
            width: 480px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            background: var(--bg-card);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
        }

        .card-header-custom {
            text-align: center;
            padding: 40px 40px 10px 40px;
        }

        .app-title {
            color: var(--text-light);
            font-weight: 800;
            font-size: 1.7rem;
            letter-spacing: -0.5px;
        }

        .card-body { padding: 20px 40px 40px 40px; }
        .form-group-custom { margin-bottom: 20px; }
        
        .form-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 6px;
            display: block;
        }

        .input-group-custom {
            border: 1.5px solid rgba(255, 255, 255, 0.1);
            background: rgba(15, 23, 42, 0.5);
            border-radius: 14px;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
        }

        .input-group-custom:focus-within {
            border-color: var(--primary-brand);
            box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.15);
            background: rgba(15, 23, 42, 0.8);
        }

        .input-group-text-custom {
            background: transparent;
            border: none;
            color: var(--text-muted);
            padding: 14px 12px 14px 18px;
        }

        .form-control-custom {
            border: none !important;
            background: transparent !important;
            padding: 14px 16px 14px 4px;
            font-size: 0.95rem;
            color: var(--text-light);
            width: 100%;
        }

        .form-control-custom:focus { box-shadow: none !important; outline: none !important; }
        .form-control-custom::placeholder { color: #64748b; }

        .btn-register {
            background: linear-gradient(135deg, var(--accent-blue), #1d4ed8);
            border: none;
            color: white;
            padding: 14px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.25s ease;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-register:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.5);
            background: linear-gradient(135deg, #3b82f6, var(--accent-blue));
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            border-radius: 14px;
            font-size: 0.85rem;
            padding: 12px;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.2);
            color: #86efac;
            border-radius: 14px;
            font-size: 0.85rem;
            padding: 12px;
        }
    </style>
</head>
<body>

<div class="card register-card">
    <div class="card-header-custom">
        <div class="app-title"><i class="fa-solid fa-user-plus text-info me-2"></i>Daftar Akun Baru</div>
        <p class="small text-muted mt-1">Sistem Kontrol Logistik I-CALM</p>
    </div>

    <div class="card-body">
        <?php if($error != ""){ ?>
            <div class="alert alert-error mb-3"><i class="fa-solid fa-triangle-exclamation me-2"></i><?= $error ?></div>
        <?php } ?>
        
        <?php if($success != ""){ ?>
            <div class="alert alert-success mb-3"><i class="fa-solid fa-circle-check me-2"></i><?= $success ?></div>
        <?php } ?>

        <form method="POST">
            <div class="form-group-custom">
                <label class="form-label">Nama Lengkap</label>
                <div class="input-group-custom">
                    <span class="input-group-text input-group-text-custom"><i class="fa-solid fa-id-card"></i></span>
                    <input type="text" name="nama_lengkap" class="form-control-custom" placeholder="Nama Lengkap Anda" required autocomplete="off">
                </div>
            </div>

            <div class="form-group-custom">
                <label class="form-label">Username</label>
                <div class="input-group-custom">
                    <span class="input-group-text input-group-text-custom"><i class="fa-solid fa-user"></i></span>
                    <input type="text" name="username" class="form-control-custom" placeholder="Buat username unik" required autocomplete="off">
                </div>
            </div>

            <div class="form-group-custom">
                <label class="form-label">Password</label>
                <div class="input-group-custom">
                    <span class="input-group-text input-group-text-custom"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" name="password" class="form-control-custom" placeholder="Minimal 6 karakter" required>
                </div>
            </div>

            <div class="form-group-custom">
                <label class="form-label">Konfirmasi Password</label>
                <div class="input-group-custom">
                    <span class="input-group-text input-group-text-custom"><i class="fa-solid fa-lock-open"></i></span>
                    <input type="password" name="konfirmasi_password" class="form-control-custom" placeholder="Ulangi password" required>
                </div>
            </div>

            <button type="submit" name="register" class="btn btn-register w-100 mb-3">Buat Akun Sistem</button>

            <div class="text-center mt-2" style="font-size: 0.9rem; color: var(--text-muted);">
                Sudah punya akses? <a href="index.php" class="fw-bold text-decoration-none" style="color: var(--primary-brand);">Login Disini</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>