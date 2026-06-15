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
        
        $cek_user = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
        if(mysqli_num_rows($cek_user) > 0){
            $error = "Username sudah terdaftar, gunakan yang lain!";
        } else {
            $query = mysqli_query($conn, "INSERT INTO users (nama_lengkap, username, password) VALUES ('$nama_lengkap', '$username', '$password_encrypted')");
            if($query){
                $success = "Registrasi Berhasil! Silakan <a href='index.php' class='fw-bold text-white'>Login</a>.";
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
            --bg-base: #e2e8f0; --bg-card: rgba(255, 255, 255, 0.45); --primary-brand: #0284c7; 
            --accent-blue: #3b82f6; --text-main: #1e293b; --text-muted: #64748b; --border-glass: rgba(255, 255, 255, 0.7);
        }
        body { font-family: 'Inter', sans-serif; background: radial-gradient(circle at top right, #dbeafe 0%, var(--bg-base) 60%, #e0e7ff 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 30px; position: relative; color: var(--text-main); }
        #particleCanvas { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; pointer-events: none; }
        .card { width: 450px; background: var(--bg-card) !important; border: 1px solid var(--border-glass) !important; backdrop-filter: blur(35px); border-radius: 32px; box-shadow: 0 20px 40px rgba(15, 23, 42, 0.1); z-index: 2; }
        .card-header { text-align: center; padding: 40px 40px 10px 40px; border-bottom: none; background: transparent; }
        .card-body { padding: 20px 40px 40px 40px; }
        .form-label { font-size: 0.75rem; font-weight: 800; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px; }
        .input-group { border: 1.5px solid rgba(148, 163, 184, 0.25); background: rgba(255, 255, 255, 0.4); border-radius: 14px; }
        .form-control { border: none !important; background: transparent !important; padding: 12px 16px; font-weight: 600; }
        .btn-register { background: linear-gradient(135deg, var(--accent-blue), #1d4ed8); border: none; color: white; padding: 12px; border-radius: 14px; font-weight: 700; }
        .alert { border-radius: 14px; font-size: 0.85rem; font-weight: 600; }
    </style>
</head>
<body>

<canvas id="particleCanvas"></canvas>

<div class="card">
    <div class="card-header">
        <h4 class="fw-bold">Daftar Akun Baru</h4>
        <div class="small text-muted">Sistem I-CALM UPT Makassar</div>
    </div>
    <div class="card-body">
        <?php if($error != ""){ ?> <div class="alert alert-danger"><?= $error ?></div> <?php } ?>
        <?php if($success != ""){ ?> <div class="alert alert-success"><?= $success ?></div> <?php } ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Nama Lengkap</label>
                <div class="input-group"><input type="text" name="nama_lengkap" class="form-control" required></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group"><input type="text" name="username" class="form-control" required></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group"><input type="password" name="password" id="p1" class="form-control" required></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Konfirmasi Password</label>
                <div class="input-group"><input type="password" name="konfirmasi_password" id="p2" class="form-control" required></div>
            </div>
            <button type="submit" name="register" class="btn btn-register w-100">Daftar Sekarang</button>
            <div class="text-center mt-3 small"><a href="index.php" class="text-decoration-none" style="color: var(--primary-brand);">Kembali ke Login</a></div>
        </form>
    </div>
</div>

<script>
    // Partikel Background tetap ada agar tampilan tetap hidup dan senada dengan login
    const canvas = document.getElementById('particleCanvas');
    const ctx = canvas.getContext('2d');
    let particles = [];
    function resize() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
    window.addEventListener('resize', resize); resize();
    class Particle {
        constructor() { this.x = Math.random()*canvas.width; this.y = Math.random()*canvas.height; this.speedX = Math.random()*0.5-0.25; this.speedY = Math.random()*0.5-0.25; }
        update() { this.x += this.speedX; this.y += this.speedY; if(this.x<0 || this.x>canvas.width) this.speedX *= -1; if(this.y<0 || this.y>canvas.height) this.speedY *= -1; }
        draw() { ctx.fillStyle = 'rgba(59, 130, 246, 0.3)'; ctx.beginPath(); ctx.arc(this.x, this.y, 2, 0, Math.PI*2); ctx.fill(); }
    }
    for(let i=0; i<40; i++) particles.push(new Particle());
    function animate() { ctx.clearRect(0,0,canvas.width, canvas.height); particles.forEach(p => { p.update(); p.draw(); }); requestAnimationFrame(animate); }
    animate();
</script>
</body>
</html>