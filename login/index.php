<?php
session_start();
include "../config/koneksi.php";

$error = "";

if(isset($_POST['login'])){

    $username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password = md5($_POST['password']);

    $cek = mysqli_query($conn,"
        SELECT *
        FROM users
        WHERE username='$username'
        AND password='$password'
    ");

    if(mysqli_num_rows($cek) > 0){

        $data = mysqli_fetch_assoc($cek);

        $_SESSION['login'] = true;
        $_SESSION['nama'] = $data['nama_lengkap'];

        header("Location: ../dashboard/index.php");
        exit;

    }else{
        $error = "Username atau Password Salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I-CALM | Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        :root {
            --bg-dark: #0f172a;           /* Slate 900 */
            --bg-card: #1e293b;           /* Slate 800 */
            --primary-brand: #38bdf8;     /* Sky Blue / Cyan Terang */
            --accent-blue: #2563eb;       /* Royal Blue PLN */
            --text-light: #f8fafc;        /* Putih Redup */
            --text-muted: #94a3b8;        /* Abu-abu Slate */
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

        .login-card {
            width: 450px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            overflow: hidden;
            background: var(--bg-card);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
        }

        .card-header-custom {
            text-align: center;
            padding: 45px 40px 10px 40px;
            background: transparent;
            border-bottom: none;
        }

        .logo {
            width: 70px;
            height: auto;
            margin-bottom: 18px;
            filter: drop-shadow(0 4px 12px rgba(56, 189, 248, 0.2));
        }

        .app-title {
            color: var(--text-light);
            font-weight: 800;
            font-size: 1.85rem;
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }

        .app-subtitle {
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 500;
        }

        .card-body {
            padding: 25px 40px 45px 40px;
        }

        .form-group-custom {
            margin-bottom: 22px;
        }

        .form-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 8px;
            display: block;
        }

        .input-group-custom {
            border: 1.5px solid rgba(255, 255, 255, 0.1);
            background: rgba(15, 23, 42, 0.5);
            border-radius: 14px;
            transition: all 0.25s ease;
            overflow: hidden;
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

        .input-group-custom:focus-within .input-group-text-custom {
            color: var(--primary-brand);
        }

        .form-control-custom {
            border: none !important;
            background: transparent !important;
            padding: 14px 16px 14px 4px;
            font-size: 0.95rem;
            color: var(--text-light);
            font-weight: 500;
            width: 100%;
        }

        .form-control-custom::placeholder {
            color: #64748b;
        }

        .form-control-custom:focus {
            box-shadow: none !important;
            outline: none !important;
        }

        .btn-eye-custom {
            border: none !important;
            background: transparent !important;
            color: var(--text-muted);
            padding-right: 18px;
        }
        
        .btn-eye-custom:hover {
            color: var(--text-light);
        }

        .btn-login {
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

        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.5);
            background: linear-gradient(135deg, #3b82f6, var(--accent-blue));
        }

        .alert-custom {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            border-radius: 14px;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 14px 16px;
            margin-bottom: 24px;
        }

        .divider {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            margin: 30px 0 20px 0;
        }

        .footer-text {
            font-size: 0.8rem;
            color: var(--text-muted);
            line-height: 1.6;
        }
    </style>
</head>
<body>

<div class="card login-card">
    <div class="card-header-custom">
        <img src="../assets/logo_pln.png" class="logo" alt="Logo PLN">
        <div class="app-title"><i class="fa-solid fa-bolt text-warning me-2"></i>I-CALM</div>
        <div class="app-subtitle">Inventory Control & Logistics Monitoring</div>
    </div>

    <div class="card-body">
        <?php if($error != ""){ ?>
            <div class="alert alert-custom d-flex align-items-center" role="alert">
                <i class="fa-solid fa-circle-exclamation me-2 fs-5"></i>
                <div><?= $error ?></div>
            </div>
        <?php } ?>

        <form method="POST">
            <div class="form-group-custom">
                <label class="form-label">Username</label>
                <div class="input-group-custom">
                    <span class="input-group-text input-group-text-custom">
                        <i class="fa-solid fa-user"></i>
                    </span>
                    <input type="text" name="username" class="form-control-custom" placeholder="Masukkan username" autocomplete="off" required>
                </div>
            </div>

            <div class="form-group-custom">
                <label class="form-label">Password</label>
                <div class="input-group-custom">
                    <span class="input-group-text input-group-text-custom">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" name="password" id="password" class="form-control-custom" placeholder="Masukkan password" required>
                    <button type="button" class="btn btn-eye-custom" onclick="togglePassword()">
                        <i class="fa-solid fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" name="login" class="btn btn-login w-100 mb-3">
                <i class="fa-solid fa-right-to-bracket me-2"></i> Masuk Ke Dashboard
            </button>

            <div class="text-center mt-3" style="font-size: 0.9rem; color: var(--text-muted);">
                Belum memiliki akun? 
                <a href="register.php" class="fw-bold text-decoration-none" style="color: var(--primary-brand);">Daftar Sekarang</a>
            </div>
        </form>

        <div class="divider"></div>

        <div class="text-center footer-text">
            <i class="fa-solid fa-shield-halved me-1" style="color: var(--primary-brand);"></i> Monitoring Distribusi Material & BA
            <br>
            <span class="fw-bold text-white-50">PLN UPT Makassar</span>
        </div>
    </div>
</div>

<script>
function togglePassword(){
    var x = document.getElementById("password");
    var icon = document.getElementById("eyeIcon");
    if(x.type === "password"){
        x.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        x.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>