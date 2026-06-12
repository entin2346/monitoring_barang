<?php
session_start();
include "../config/koneksi.php";

if(isset($_POST['login'])){

    $username = mysqli_real_escape_string($conn,$_POST['username']);
    $password = md5($_POST['password']);

    $cek = mysqli_query($conn,"
        SELECT * FROM users
        WHERE username='$username'
        AND password='$password'
    ");

    if(mysqli_num_rows($cek)>0){

        $data = mysqli_fetch_assoc($cek);

        $_SESSION['login']=true;
        $_SESSION['nama']=$data['nama_lengkap'];

        header("Location: ../dashboard/index.php");
        exit;

    }else{

        echo "<script>alert('Username atau Password Salah');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-4">

<div class="card">

<div class="card-header">
<h4>Login Monitoring Barang</h4>
</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">
<label>Username</label>
<input type="text" name="username" class="form-control" required>
</div>

<div class="mb-3">
<label>Password</label>
<input type="password" name="password" class="form-control" required>
</div>

<button type="submit"
name="login"
class="btn btn-primary w-100">
Login
</button>

</form>

</div>

</div>

</div>

</div>

</div>

</body>
</html>