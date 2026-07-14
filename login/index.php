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
        $_SESSION['nama']  = $data['nama_lengkap'];
        // PERBAIKAN: Menyimpan data role ke dalam Session saat login sukses
        $_SESSION['role']  = $data['role'] ?? 'User';

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
            --bg-base: #e2e8f0;            
            --bg-card: rgba(255, 255, 255, 0.45); 
            --primary-brand: #0284c7;       
            --accent-blue: #3b82f6;         
            --text-main: #1e293b;           
            --text-muted: #64748b;          
            --border-glass: rgba(255, 255, 255, 0.7); 
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at top right, #dbeafe 0%, var(--bg-base) 60%, #e0e7ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
            overflow-x: hidden;
            position: relative;
            color: var(--text-main);
            perspective: 800px;
        }

        #particleCanvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        .bg-glow-container {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 1;
        }

        .glow-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.6;
        }

        .glow-orb-1 {
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.4) 0%, rgba(56, 189, 248, 0) 70%);
            top: -120px;
            left: -120px;
            animation: floatOrb1 20s infinite alternate ease-in-out;
        }

        .glow-orb-2 {
            width: 650px;
            height: 650px;
            background: radial-gradient(circle, rgba(165, 180, 252, 0.5) 0%, rgba(165, 180, 252, 0) 70%);
            bottom: -150px;
            right: -150px;
            animation: floatOrb2 25s infinite alternate ease-in-out;
        }

        @keyframes floatOrb1 {
            0% { transform: translate(0, 0) scale(1); border-radius: 42% 58% 70% 30% / 45% 45% 55% 55%; }
            50% { transform: translate(100px, 60px) scale(1.1); border-radius: 70% 30% 52% 48% / 60% 40% 60% 40%; }
            100% { transform: translate(20px, 120px) scale(0.95); border-radius: 42% 58% 70% 30% / 45% 45% 55% 55%; }
        }

        @keyframes floatOrb2 {
            0% { transform: translate(0, 0) scale(1); border-radius: 70% 30% 52% 48% / 60% 40% 60% 40%; }
            50% { transform: translate(-80px, -100px) scale(0.9); border-radius: 42% 58% 70% 30% / 45% 45% 55% 55%; }
            100% { transform: translate(-20px, -30px) scale(1.05); border-radius: 70% 30% 52% 48% / 60% 40% 60% 40%; }
        }

        .card-container {
            position: relative;
            z-index: 2;
            transform-style: preserve-3d;
            padding: 20px;
        }

        .login-card {
            position: relative;
            width: 450px;
            background: var(--bg-card) !important;
            border: 1px solid var(--border-glass) !important;
            backdrop-filter: blur(35px);
            -webkit-backdrop-filter: blur(35px);
            border-radius: 32px;
            overflow: hidden;
            box-shadow: 0 40px 80px -25px rgba(15, 23, 42, 0.2),
                        0 20px 40px -15px rgba(15, 23, 42, 0.1),
                        0 0 1px 1px rgba(255, 255, 255, 0.6) inset !important;
            transform-style: preserve-3d;
            transition: transform 0.15s ease-out, box-shadow 0.3s ease;
            animation: cardEntrance 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .card-glare {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.4) 0%, rgba(255, 255, 255, 0) 60%);
            opacity: 0;
            pointer-events: none;
            z-index: 5;
            mix-blend-mode: overlay;
            transition: opacity 0.3s ease;
        }

        .card-header-custom, .card-body {
            transform-style: preserve-3d;
        }

        .logo, .app-title, .app-subtitle, .form-group-custom, .btn-login, .divider, .footer-text, .register-link {
            transform: translateZ(40px);
        }

        .logo {
            width: 75px;
            height: auto;
            margin-bottom: 18px;
            filter: drop-shadow(0 15px 25px rgba(59, 130, 246, 0.2));
            transform: translateZ(70px);
        }

        .card-header-custom {
            text-align: center;
            padding: 45px 40px 10px 40px;
            background: transparent;
            border-bottom: none;
        }

        .app-title {
            color: var(--text-main);
            font-weight: 800;
            font-size: 2.2rem;
            letter-spacing: -1px;
            margin-bottom: 4px;
            background: linear-gradient(135deg, #1e293b 20%, #1d4ed8 70%, #0369a1 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transform: translateZ(55px);
        }

        .app-subtitle {
            color: #334155;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .card-body {
            padding: 25px 40px 45px 40px;
        }

        .form-group-custom {
            margin-bottom: 22px;
        }

        .form-label {
            font-size: 0.75rem;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 8px;
            display: block;
        }

        .input-group-custom {
            border: 1.5px solid rgba(148, 163, 184, 0.25);
            background: rgba(255, 255, 255, 0.4);
            border-radius: 14px;
            transition: all 0.25s ease;
            overflow: hidden;
            display: flex;
            align-items: center;
            backdrop-filter: blur(5px);
        }

        .input-group-custom:focus-within {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
            background: rgba(255, 255, 255, 0.8);
        }

        .input-group-text-custom {
            background: transparent;
            border: none;
            color: var(--text-muted);
            padding: 14px 12px 14px 18px;
        }

        .input-group-custom:focus-within .input-group-text-custom {
            color: var(--accent-blue);
        }

        .form-control-custom {
            border: none !important;
            background: transparent !important;
            padding: 14px 16px 14px 4px;
            font-size: 0.95rem;
            color: var(--text-main);
            font-weight: 600;
            width: 100%;
        }

        .form-control-custom::placeholder {
            color: #94a3b8;
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
            color: var(--text-main);
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
            box-shadow: 0 8px 16px rgba(59, 130, 246, 0.25);
            transform: translateZ(50px);
        }

        .btn-login:hover {
            transform: translateZ(55px) translateY(-2px);
            box-shadow: 0 12px 24px rgba(59, 130, 246, 0.4);
            background: linear-gradient(135deg, #60a5fa, var(--accent-blue));
        }

        .alert-custom {
            background-color: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #dc2626;
            border-radius: 14px;
            font-size: 0.85rem;
            font-weight: 700;
            padding: 14px 16px;
            margin-bottom: 24px;
            transform: translateZ(40px);
        }

        .divider {
            border-top: 1px solid rgba(148, 163, 184, 0.2);
            margin: 30px 0 20px 0;
        }

        .footer-text {
            font-size: 0.8rem;
            color: #475569;
            line-height: 1.6;
            font-weight: 600;
        }

        @keyframes cardEntrance {
            from { opacity: 0; transform: translateY(40px) rotateX(10deg); }
            to { opacity: 1; transform: translateY(0) rotateX(0deg); }
        }
    </style>
</head>
<body>

<canvas id="particleCanvas"></canvas>

<div class="bg-glow-container">
    <div class="glow-orb glow-orb-1"></div>
    <div class="glow-orb glow-orb-2"></div>
</div>

<div class="card-container" id="cardWrapper">
    <div class="card login-card" id="tiltCard">
        <div class="card-glare" id="cardGlare"></div>

        <div class="card-header-custom">
            <img src="../assets/logo_pln.png" class="logo" alt="Logo PLN">
            <div class="app-title"><i class="fa-solid fa-bolt text-warning me-2" style="-webkit-text-fill-color: initial;"></i>I-CALM</div>
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

                <div class="text-center mt-3 register-link" style="font-size: 0.9rem; color: var(--text-muted); font-weight: 600;">
                    Belum memiliki akun? 
                    <a href="register.php" class="fw-bold text-decoration-none" style="color: var(--primary-brand);">Daftar Sekarang</a>
                </div>
            </form>

            <div class="divider"></div>

            <div class="text-center footer-text">
                <i class="fa-solid fa-shield-halved me-1" style="color: var(--primary-brand);"></i> Monitoring Distribusi Material & BA
                <br>
                <span class="text-muted" style="opacity: 0.85;">PLN UPT Makassar</span>
            </div>
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

const canvas = document.getElementById('particleCanvas');
const ctx = canvas.getContext('2d');
let particlesArray = [];
const numberOfParticles = 45;

function setCanvasSize() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
}
setCanvasSize();
window.addEventListener('resize', setCanvasSize);

class Particle {
    constructor() {
        this.x = Math.random() * canvas.width;
        this.y = Math.random() * canvas.height;
        this.size = Math.random() * 3 + 1;
        this.speedX = Math.random() * 0.4 - 0.2;
        this.speedY = Math.random() * 0.4 - 0.2;
        this.opacity = Math.random() * 0.5 + 0.2;
    }
    draw() {
        ctx.fillStyle = `rgba(59, 130, 246, ${this.opacity})`;
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        ctx.fill();
    }
    update() {
        this.x += this.speedX;
        this.y += this.speedY;
        if (this.x > canvas.width) this.x = 0;
        if (this.x < 0) this.x = canvas.width;
        if (this.y > canvas.height) this.y = 0;
        if (this.y < 0) this.y = canvas.height;
    }
}

function initParticles() {
    particlesArray = [];
    for (let i = 0; i < numberOfParticles; i++) {
        particlesArray.push(new Particle());
    }
}
initParticles();

function animateParticles() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    for (let i = 0; i < particlesArray.length; i++) {
        particlesArray[i].update();
        particlesArray[i].draw();
    }
    requestAnimationFrame(animateParticles);
}
animateParticles();

const wrapper = document.getElementById('cardWrapper');
const card = document.getElementById('tiltCard');
const glare = document.getElementById('cardGlare');

wrapper.addEventListener('mousemove', (e) => {
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left; 
    const y = e.clientY - rect.top; 
    
    const cardWidth = rect.width;
    const cardHeight = rect.height;
    
    const rotateX = ((cardHeight / 2 - y) / (cardHeight / 2)) * 18;
    const rotateY = ((x - cardWidth / 2) / (cardWidth / 2)) * 18;
    
    card.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
    
    glare.style.opacity = '1';
    glare.style.background = `radial-gradient(circle at ${x}px ${y}px, rgba(255, 255, 255, 0.5) 0%, rgba(255, 255, 255, 0) 60%)`;
    
    card.style.boxShadow = `${-rotateY * 2}px ${rotateX * 2}px 80px -25px rgba(15, 23, 42, 0.25), 0 20px 40px -15px rgba(15, 23, 42, 0.1)`;
});

wrapper.addEventListener('mouseleave', () => {
    card.style.transition = "transform 0.5s ease-out, box-shadow 0.5s ease-out";
    card.style.transform = `rotateX(0deg) rotateY(0deg)`;
    card.style.boxShadow = "0 40px 80px -25px rgba(15, 23, 42, 0.2), 0 20px 40px -15px rgba(15, 23, 42, 0.1)";
    glare.style.opacity = '0';
});

wrapper.addEventListener('mouseenter', () => {
    card.style.transition = "none";
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>