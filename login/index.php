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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg,#0d6efd,#0a58ca);
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .login-card {
            width:420px;
            border:none;
            border-radius:20px;
            overflow:hidden;
        }
        .logo { width:90px; margin-bottom:15px; }
        .card-header-custom {
            text-align:center;
            padding-top:30px;
            padding-bottom:20px;
        }
        .card-body { padding:30px; }
        .btn-login { font-weight:bold; }
    </style>
</head>
<body>

<div class="card shadow-lg login-card">
    <div class="card-header card-header-custom bg-primary text-white">
        <img src="../assets/logo_pln.png" class="logo" alt="PLN">
        <h3>I-CALM</h3>
        <small>Inventory Control & Logistics Monitoring</small>
    </div>

    <div class="card-body">
        <?php if($error!=""){ ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-circle-exclamation"></i> <?= $error ?>
            </div>
        <?php } ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                    <input type="text" name="username" class="form-control" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" name="password" id="password" class="form-control" required>
                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" name="login" class="btn btn-primary w-100 btn-login">
                <i class="fa-solid fa-right-to-bracket"></i> Login
            </button>
        </form>

        <hr>
        <div class="text-center text-muted">
            Monitoring Distribusi Material & BA<br>
            PLN UPT Makassar
        </div>
    </div>
</div>

<script>
    function togglePassword(){
        var x = document.getElementById("password");
        if(x.type === "password"){
            x.type = "text";
        }else{
            x.type = "password";
        }
    }
</script>

</body>
</html>