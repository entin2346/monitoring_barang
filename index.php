<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome | I-CALM</title>

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
            font-family: 'Inter', sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: radial-gradient(circle at top right, #dbeafe 0%, var(--bg-base) 60%, #e0e7ff 100%);
            overflow: hidden;
            position: relative;
            color: var(--text-main);
            perspective: 800px;
        }

        /* CONTAINER UNTUK KANVAS PARTIKEL */
        #particleCanvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none; /* Agar tidak mengganggu interaksi mouse ke kartu */
        }

        /* ANMATED BACKGROUND BLOBS (BIAS CAHAYA LEMBUT) */
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

        /* --------------------------------------------------
           CARD SPLASH SETUP
        ----------------------------------------------------- */
        .card-container {
            position: relative;
            z-index: 2;
            transform-style: preserve-3d;
            padding: 20px;
        }

        .card-splash {
            position: relative;
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            backdrop-filter: blur(35px);
            -webkit-backdrop-filter: blur(35px);
            padding: 55px 45px;
            border-radius: 32px;
            text-align: center;
            width: 480px;
            overflow: hidden;
            
            box-shadow: 0 40px 80px -25px rgba(15, 23, 42, 0.2),
                        0 20px 40px -15px rgba(15, 23, 42, 0.1),
                        0 0 1px 1px rgba(255, 255, 255, 0.6) inset !important;
            
            transform-style: preserve-3d;
            transition: transform 0.15s ease-out, box-shadow 0.3s ease;
            animation: premiumEntrance 1.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
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

        /* 3D POP OUT ELEMENTS */
        .logo {
            width: 85px;
            height: auto;
            margin-bottom: 25px;
            filter: drop-shadow(0 15px 30px rgba(59, 130, 246, 0.25));
            transform: translateZ(80px);
            animation: logoPulse 3s ease-in-out infinite 1.5s;
        }

        .welcome {
            color: var(--primary-brand);
            letter-spacing: 5px;
            font-size: 0.8rem;
            font-weight: 800;
            margin-bottom: 12px;
            transform: translateZ(45px);
        }

        .title {
            color: var(--text-main);
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: -1.5px;
            line-height: 1;
            background: linear-gradient(135deg, #1e293b 20%, #1d4ed8 70%, #0369a1 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transform: translateZ(65px);
            animation: shineText 5s linear infinite 1.2s;
        }

        .subtitle {
            color: #334155; 
            font-size: 1.05rem;
            font-weight: 700;
            margin-bottom: 12px;
            line-height: 1.4;
            transform: translateZ(50px);
        }

        .desc {
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 40px;
            line-height: 1.6;
            padding: 0 15px;
            transform: translateZ(40px);
        }

        .loading {
            width: 260px;
            height: 5px;
            background: rgba(148, 163, 184, 0.2);
            border-radius: 20px;
            overflow: hidden;
            margin: 0 auto;
            position: relative;
            transform: translateZ(40px);
        }

        .bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #3b82f6, #60a5fa, #3b82f6);
            background-size: 200% auto;
            border-radius: 20px;
            box-shadow: 0 0 12px rgba(96, 165, 250, 0.6);
            animation: load 4.5s cubic-bezier(0.4, 0, 0.2, 1) forwards, barShine 1.5s linear infinite;
        }

        .footer {
            margin-top: 45px;
            color: #475569;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            transform: translateZ(35px);
        }

        @keyframes premiumEntrance {
            from { opacity: 0; transform: translateY(50px) rotateX(15deg); }
            to { opacity: 1; transform: translateY(0) rotateX(0deg); }
        }

        @keyframes logoPulse {
            0% { transform: translateZ(80px) scale(1); }
            50% { transform: translateZ(95px) scale(1.04); }
            100% { transform: translateZ(80px) scale(1); }
        }

        @keyframes shineText {
            to { background-position: 200% center; }
        }

        @keyframes load {
            0% { width: 0%; }
            100% { width: 100%; }
        }

        @keyframes barShine {
            to { background-position: 200% center; }
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
    <div class="card-splash" id="tiltCard">
        <div class="card-glare" id="cardGlare"></div>

        <img src="assets/logo_pln.png" class="logo" alt="Logo PLN">

        <div class="welcome">WELCOME TO</div>

        <div class="title"><i class="fa-solid fa-bolt text-warning me-1" style="-webkit-text-fill-color: initial;"></i>I-CALM</div>

        <div class="subtitle">Inventory Control & Logistics Monitoring</div>

        <div class="desc">
            Sistem Monitoring Distribusi Material, Gudang, dan Berita Acara
        </div>

        <div class="loading">
            <div class="bar"></div>
        </div>

        <div class="footer">
            <span>PLN UPT Makassar</span>
        </div>
    </div>
</div>

<script>
    // ----------------------------------------------------
    // ANIMASI PARTIKEL ENERGI ELEKTRON (CANVAS SCRIPT)
    // ----------------------------------------------------
    const canvas = document.getElementById('particleCanvas');
    const ctx = canvas.getContext('2d');

    let particlesArray = [];
    const numberOfParticles = 45; // Jumlah partikel melayang

    // Sesuaikan ukuran kanvas dengan jendela browser
    function setCanvasSize() {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    }
    setCanvasSize();
    window.addEventListener('resize', setCanvasSize);

    // Blueprint Objek Partikel
    class Particle {
        constructor() {
            this.x = Math.random() * canvas.width;
            this.y = Math.random() * canvas.height;
            this.size = Math.random() * 3 + 1; // Ukuran partikel kecil dan soft
            this.speedX = Math.random() * 0.4 - 0.2; // Bergerak sangat perlahan
            this.speedY = Math.random() * 0.4 - 0.2;
            this.opacity = Math.random() * 0.5 + 0.2;
        }
        // Menggambar partikel ke layar
        draw() {
            ctx.fillStyle = `rgba(59, 130, 246, ${this.opacity})`; // Warna biru PLN transparan
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
            ctx.fill();
        }
        // Logika pergerakan partikel melayang bebas
        update() {
            this.x += this.speedX;
            this.y += this.speedY;

            // Jika partikel keluar layar, posisikan kembali di sisi sebaliknya
            if (this.x > canvas.width) this.x = 0;
            if (this.x < 0) this.x = canvas.width;
            if (this.y > canvas.height) this.y = 0;
            if (this.y < 0) this.y = canvas.height;
        }
    }

    // Inisialisasi kumpulan partikel
    function initParticles() {
        particlesArray = [];
        for (let i = 0; i < numberOfParticles; i++) {
            particlesArray.push(new Particle());
        }
    }
    initParticles();

    // Loop Animasi Konstan
    function animateParticles() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        for (let i = 0; i < particlesArray.length; i++) {
            particlesArray[i].update();
            particlesArray[i].draw();
        }
        requestAnimationFrame(animateParticles);
    }
    animateParticles();


    // ----------------------------------------------------
    // INTERAKTIF 3D TILT CARD WITH SHADOW SHIFT
    // ----------------------------------------------------
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

    // Auto Redirect
    setTimeout(function(){
        window.location.href = "login/index.php";
    }, 4500);
</script>

</body>
</html>