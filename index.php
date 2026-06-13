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
            --bg-dark: #0f172a;           /* Slate 900 */
            --bg-card: rgba(30, 41, 59, 0.75); /* Dark Glassmorphism Slate 800 */
            --primary-brand: #38bdf8;     /* Sky Blue / Cyan Terang */
            --accent-blue: #2563eb;       /* Royal Blue PLN */
            --text-light: #f8fafc;        /* Putih Redup */
            --text-muted: #94a3b8;        /* Abu-abu Slate */
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
            /* Latar belakang gelap bergradasi radial, selaras dengan halaman login */
            background: radial-gradient(circle at top right, #1e3a8a 0%, #0f172a 70%);
            overflow: hidden;
            position: relative;
            color: var(--text-light);
        }

        /* ORNAMEN CAHAYA LATAR BELAKANG YANG BERGERAK HALUS */
        body::before {
            content: '';
            position: absolute;
            width: 700px;
            height: 700px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.15) 0%, rgba(0,0,0,0) 70%);
            top: -150px;
            left: -150px;
            z-index: 1;
            animation: floatAmbient 8s ease-in-out infinite alternate;
        }

        body::after {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.1) 0%, rgba(0,0,0,0) 70%);
            bottom: -100px;
            right: -100px;
            z-index: 1;
            animation: floatAmbient 10s ease-in-out infinite alternate-reverse;
        }

        /* CARD SPLASH: DARK GLASSMORPHISM PREMIUM */
        .card-splash {
            position: relative;
            z-index: 2;
            background: var(--bg-card);
            border: 1px solid rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            padding: 55px 45px;
            border-radius: 32px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
            width: 480px;
            /* Animasi masuk melambat yang sangat halus */
            animation: premiumFadeUp 1.2s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* ANIMASI LOGO BERDENYUT ELEGAN */
        .logo {
            width: 80px;
            height: auto;
            margin-bottom: 25px;
            filter: drop-shadow(0 4px 12px rgba(56, 189, 248, 0.2));
            animation: logoEntrance 1s cubic-bezier(0.34, 1.56, 0.64, 1) forwards, logoPulse 3s ease-in-out infinite 1s;
        }

        .welcome {
            color: var(--primary-brand);
            letter-spacing: 5px;
            font-size: 0.8rem;
            font-weight: 800;
            margin-bottom: 12px;
            opacity: 0;
            animation: fadeInText 0.8s ease-out 0.3s forwards;
        }

        /* TEKS UTAMA DENGAN GRADASI EMAS/CYAN GLOW */
        .title {
            color: var(--text-light);
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 10px;
            letter-spacing: -1.5px;
            line-height: 1;
            opacity: 0;
            background: linear-gradient(135deg, #f8fafc 30%, #38bdf8 70%, #f8fafc 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: fadeInText 0.8s ease-out 0.4s forwards, shineText 4s linear infinite 1.2s;
        }

        .subtitle {
            color: var(--text-light);
            font-size: 1.05rem;
            font-weight: 600;
            margin-bottom: 12px;
            line-height: 1.4;
            opacity: 0;
            animation: fadeInText 0.8s ease-out 0.5s forwards;
        }

        .desc {
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 40px;
            line-height: 1.6;
            padding: 0 15px;
            opacity: 0;
            animation: fadeInText 0.8s ease-out 0.6s forwards;
        }

        /* LOADING BAR MODEREN TEMA GELAP */
        .loading {
            width: 260px;
            height: 5px;
            background: rgba(15, 23, 42, 0.6);
            border-radius: 20px;
            overflow: hidden;
            margin: 0 auto;
            position: relative;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.3);
            opacity: 0;
            animation: fadeInText 0.5s ease-out 0.7s forwards;
        }

        .bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--accent-blue), var(--primary-brand), var(--accent-blue));
            background-size: 200% auto;
            border-radius: 20px;
            box-shadow: 0 0 12px rgba(56, 189, 248, 0.4);
            /* Loading smooth dengan percepatan di tengah (ease-in-out) */
            animation: load 2.5s cubic-bezier(0.65, 0, 0.35, 1) forwards, barShine 1.5s linear infinite;
        }

        .footer {
            margin-top: 40px;
            color: var(--text-muted);
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            opacity: 0;
            animation: fadeInText 0.8s ease-out 0.8s forwards;
        }

        /* ----------------------------------
           KUMPULAN ANIMASI PREMIUM (KEYFRAMES)
        ------------------------------------- */
        
        /* Kartu muncul dari bawah melambat halus */
        @keyframes premiumFadeUp {
            from {
                opacity: 0;
                transform: translateY(40px) scale(0.97);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Logo muncul memantul elegan */
        @keyframes logoEntrance {
            from {
                transform: scale(0.6);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Denyut lembut pada logo agar halaman tidak statis */
        @keyframes logoPulse {
            0% { transform: scale(1); filter: drop-shadow(0 4px 12px rgba(56, 189, 248, 0.2)); }
            50% { transform: scale(1.03); filter: drop-shadow(0 8px 20px rgba(56, 189, 248, 0.4)); }
            100% { transform: scale(1); filter: drop-shadow(0 4px 12px rgba(56, 189, 248, 0.2)); }
        }

        /* Transisi teks muncul seiring waktu berjalan */
        @keyframes fadeInText {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Kilatan gradasi pada teks utama */
        @keyframes shineText {
            to { background-position: 200% center; }
        }

        /* Progress pengisian loading bar */
        @keyframes load {
            0% { width: 0%; }
            100% { width: 100%; }
        }

        /* Efek cahaya bergerak di dalam loading bar */
        @keyframes barShine {
            to { background-position: 200% center; }
        }

        /* Pergerakan partikel cahaya samar di luar kartu */
        @keyframes floatAmbient {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, 20px) scale(1.05); }
        }
    </style>

    <script>
        // Pengalihan halaman otomatis setelah proses animasi loading selesai
        setTimeout(function(){
            window.location.href = "login/index.php";
        }, 2500);
    </script>
</head>
<body>

<div class="card-splash">

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
        <span class="text-white-50">PLN UPT Makassar</span>
    </div>

</div>

</body>
</html>