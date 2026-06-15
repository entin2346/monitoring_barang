<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

/* =========================
   GRAFIK MATERIAL
========================= */
$grafik = mysqli_query($conn,"
    SELECT nama_material, jumlah
    FROM material_gudang
    WHERE nama_material IS NOT NULL
    AND nama_material <> ''
    AND jumlah > 0
    ORDER BY jumlah DESC
    LIMIT 10
");

$label = [];
$jumlah_material = [];

while($g = mysqli_fetch_assoc($grafik)){
    $label[] = $g['nama_material'];
    $jumlah_material[] = (int)$g['jumlah'];
}

/* =========================
   STATISTIK DASHBOARD
========================= */
$total_material = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) as total FROM material_gudang WHERE nama_material <> ''")
);

$total_ba = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) as total FROM database_ba WHERE nama_barang <> ''")
);

$total_stok = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT SUM(jumlah) as total FROM material_gudang")
);
/* =========================
   GRAFIK MTU
========================= */
$grafikMTU = mysqli_query($conn,"
    SELECT ultg, SUM(mtu_terganti) AS total
    FROM peremajaan_mtu
    GROUP BY ultg
    ORDER BY ultg ASC
");

if($grafikMTU === false){
    die("Query MTU Error: ".mysqli_error($conn));
}

$labelMTU = [];
$dataMTU = [];

while($m = mysqli_fetch_assoc($grafikMTU)){
    $labelMTU[] = 'ULTG '.$m['ultg'];
    $dataMTU[] = (int)$m['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I-CALM Dashboard | Premium Dark</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --bg-dark: #090d16;           
            --bg-body: #0f172a;           
            --bg-card: #1e293b;           
            --primary-brand: #38bdf8;     
            --accent-blue: #2563eb;       
            --accent-purple: #a855f7;     
            --text-light: #f8fafc;        
            --text-muted: #94a3b8;        
            --border-color: rgba(255, 255, 255, 0.05);
        }

        /* CUSTOM SCROLLBAR */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: var(--bg-dark); }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 20px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--primary-brand); }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body { 
            background: var(--bg-dark);
            color: var(--text-light);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* SIDEBAR PREMIUM */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100%;
            background: #111827;
            border-right: 1px solid var(--border-color);
            padding-top: 28px;
            z-index: 1000;
            box-shadow: 10px 0 30px rgba(0,0,0,0.2);
        }
        
        .sidebar h3 { 
            font-size: 1.4rem; 
            font-weight: 800; 
            padding: 0 24px; 
            margin-bottom: 35px; 
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #fff 30%, var(--primary-brand));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .sidebar a, .dropdown-btn { 
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: var(--text-muted); 
            text-decoration: none; 
            padding: 14px 24px; 
            font-size: 0.95rem;
            font-weight: 600;
            border: none;
            background: none;
            width: 100%;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }
        
        .sidebar a:hover, .dropdown-btn:hover { 
            background: rgba(255, 255, 255, 0.02); 
            color: var(--text-light);
        }

        .sidebar .active-menu {
            color: var(--primary-brand) !important; 
            background: rgba(56, 189, 248, 0.04) !important; 
            border-left: 4px solid var(--primary-brand); 
            padding-left: 20px;
            text-shadow: 0 0 10px rgba(56, 189, 248, 0.3);
        }

        .sidebar a i, .dropdown-btn i {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
            transition: transform 0.2s;
        }

        .sidebar a:hover i { transform: scale(1.1); }
        .sidebar .menu-text { flex-grow: 1; }
        .dropdown-chevron { font-size: 0.8rem !important; transition: transform 0.2s ease; }
        .dropdown-btn.active .dropdown-chevron { transform: rotate(180deg); color: var(--primary-brand); }

        .dropdown-container {
            display: none;
            background: rgba(0, 0, 0, 0.2); 
            padding: 4px 0;
        }
        
        .dropdown-container a { padding: 11px 24px 11px 56px; font-size: 0.85rem; font-weight: 500; }

        /* CONTENT AREA & GLASS NAVBAR */
        .content { margin-left: 260px; }
        
        .navbar-custom { 
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 18px 32px; 
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 999;
        }
        .navbar-custom .navbar-brand { color: var(--text-light); font-weight: 800; font-size: 1.4rem; letter-spacing: -0.5px;}
        .navbar-custom .navbar-text { color: var(--text-muted) !important; font-size: 0.9rem; font-weight: 500; }

        .main-body { padding: 40px 32px; background: linear-gradient(180deg, #0f172a 0%, #090d16 100%); min-height: calc(100vh - 78px);}

        /* GLOWING CARD STATISTIK */
        .card-stat {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 28px 24px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-stat .stat-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: var(--text-muted);
            font-weight: 700;
            margin-bottom: 12px;
        }
        
        .card-stat .stat-value {
            font-size: 2.3rem;
            font-weight: 800;
            color: var(--text-light);
            margin: 0;
            letter-spacing: -1px;
        }
        
        .card-stat .stat-icon {
            position: absolute;
            right: 24px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 2.8rem;
            transition: all 0.3s ease;
        }

        .card-blue { border-left: 5px solid var(--primary-brand); }
        .card-blue .stat-icon { background: linear-gradient(135deg, #38bdf8, #0369a1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .card-blue:hover { border-color: var(--primary-brand); box-shadow: 0 10px 30px rgba(56, 189, 248, 0.15); transform: translateY(-4px); }
        
        .card-cyan { border-left: 5px solid var(--accent-blue); }
        .card-cyan .stat-icon { background: linear-gradient(135deg, #60a5fa, #1d4ed8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .card-cyan:hover { border-color: var(--accent-blue); box-shadow: 0 10px 30px rgba(37, 99, 235, 0.15); transform: translateY(-4px); }
        
        .card-dark { border-left: 5px solid var(--accent-purple); }
        .card-dark .stat-icon { background: linear-gradient(135deg, #c084fc, #6b21a8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .card-dark:hover { border-color: var(--accent-purple); box-shadow: 0 10px 30px rgba(168, 85, 247, 0.15); transform: translateY(-4px); }

        /* LIVE STATUS PULSE */
        .pulse-indicator {
            display: inline-block;
            width: 8px; height: 8px;
            background-color: #10b981;
            border-radius: 50%;
            margin-right: 8px;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse 1.6s infinite;
            vertical-align: middle;
        }

        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        /* ISOLASI KUSTOM TOTAL UNTUK TABEL DASHBOARD 
           (Tanpa Menggunakan Class .table Bootstrap Agar Tidak Ditimpa Menjadi Putih)
        */
        .table-container-icalm {
            background: #111827 !important; /* Latar belakang gelap solid */
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            overflow: hidden;
            width: 100%;
        }

        .table-icalm-custom {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            padding: 0;
            background: #111827 !important;
        }

        .table-icalm-custom thead th {
            background: #1f2937 !important; /* Warna header abu-abu gelap */
            color: #9ca3af !important; /* Warna teks header */
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.8px;
            padding: 16px 20px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            text-align: left;
        }

        .table-icalm-custom tbody tr {
            background: #111827 !important; /* Memaksa latar belakang baris tetap gelap gulita */
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: background 0.2s ease;
        }

        .table-icalm-custom tbody tr:hover {
            background: #1f2937 !important; /* Efek hover saat kursor di atas baris */
        }

        .table-icalm-custom tbody td {
            padding: 16px 20px;
            font-size: 0.9rem;
            vertical-align: middle;
            color: #ffffff !important; /* MEMAKSA TEKS ISIAN MENJADI PUTIH BERKILAU */
        }

        /* Kustom Khas Untuk Badge Volume Kuantitas */
        .badge-jumlah-custom {
            background: rgba(56, 189, 248, 0.15) !important;
            color: #38bdf8 !important;
            border: 1px solid rgba(56, 189, 248, 0.3) !important;
            border-radius: 6px;
            padding: 4px 10px;
            font-weight: 700;
            font-size: 0.8rem;
            display: inline-block;
        }

        /* Perbaikan khusus sub-title teks redup di bawah "Dashboard Monitoring" agar tidak gelap */
        .sub-judul-terang {
            color: #94a3b8 !important; /* Warna abu cerah terang */
            font-size: 0.9rem;
        }

        .chart-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        .chart-card h5 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-light);
            margin-bottom: 25px;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><i class="fa-solid fa-bolt text-warning me-2"></i>I-CALM Panel</h3>

    <a href="../dashboard/index.php" class="active-menu">
        <span><i class="fa-solid fa-chart-pie me-2"></i><span class="menu-text">Dashboard</span></span>
    </a>

    <button class="dropdown-btn">
        <span><i class="fa-solid fa-layer-group"></i><span class="menu-text">Monitoring</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../material/index.php">Material Gudang</a>
        <a href="../ba/index.php">Database BA</a>
    </div>

    <button class="dropdown-btn">
        <span><i class="fa-solid fa-file-import"></i><span class="menu-text">Import</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../import/material.php">Import Material</a>
        <a href="../import/ba.php">Import BA</a>
    </div>

    <button class="dropdown-btn">
        <span><i class="fa-solid fa-file-export"></i><span class="menu-text">Export</span></span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../export/material_excel.php">Export Material</a>
        <a href="../export/ba_excel.php">Export BA</a>
    </div>

    <a href="../login/logout.php" class="mt-4">
        <span><i class="fa-solid fa-right-from-bracket text-danger"></i><span class="menu-text text-danger">Logout</span></span>
    </a>
</div>

<div class="content">
    <nav class="navbar navbar-custom">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-bolt text-warning me-2"></i>I-CALM</span>
            <span class="navbar-text d-none d-sm-block">
                Inventory Control & Logistics Monitoring
            </span>
        </div>
    </nav>

    <div class="main-body">
        <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h3 class="fw-bold tracking-tight m-0" style="font-size: 1.7rem; font-weight: 800; color: var(--text-light);">Dashboard Monitoring</h3>
                <p class="sub-judul-terang m-0 mt-1">Sistem kendali aset distribusi logistik aktif.</p>
            </div>
            <div class="px-3 py-2 rounded-3 border border-secondary border-opacity-10" style="background: #151f32;">
                <span class="pulse-indicator"></span>
                <span class="small fw-semibold text-muted">Sistem Aktif: <span class="text-white"><?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?></span></span>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card-stat card-blue">
                    <div class="stat-title">Total Jenis Material</div>
                    <h2 class="stat-value"><?= number_format($total_material['total'] ?? 0); ?></h2>
                    <i class="fa-solid fa-box-open stat-icon"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-stat card-cyan">
                    <div class="stat-title">Total Berita Acara (BA)</div>
                    <h2 class="stat-value"><?= number_format($total_ba['total'] ?? 0); ?></h2>
                    <i class="fa-solid fa-file-invoice stat-icon"></i>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-stat card-dark">
                    <div class="stat-title">Total Kuantitas Stok</div>
                    <h2 class="stat-value"><?= number_format($total_stok['total'] ?? 0); ?></h2>
                    <i class="fa-solid fa-cubes stat-icon"></i>
                </div>
            </div>
        </div>

        <div class="mb-5">
            <div class="d-flex align-items-center mb-3">
                <h5 class="fw-bold m-0" style="font-size: 1.2rem; color: var(--text-light);">📌 10 Berita Acara Terbaru</h5>
            </div>
            <div class="table-container-icalm table-responsive">
                <table class="table-icalm-custom">
                    <thead>
                        <tr>
                            <th width="70">NO</th>
                            <th width="180">TANGGAL DATA</th>
                            <th>NAMA BARANG / MATERIAL</th>
                            <th width="160">VOLUME</th>
                            <th>TUJUAN LOGISTIK</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $ba_terbaru = mysqli_query($conn,"
                            SELECT * FROM database_ba 
                            WHERE nama_barang IS NOT NULL AND nama_barang <> '' 
                            ORDER BY tanggal DESC, id DESC LIMIT 10
                        ");
                        $no = 1;
                        if(mysqli_num_rows($ba_terbaru) > 0) {
                            while($d = mysqli_fetch_assoc($ba_terbaru)){
                            ?>
                            <tr>
                                <td style="color: #9ca3af !important; font-weight: bold;"><?= $no++; ?></td>
                                <td style="color: #38bdf8 !important; font-weight: 500;">
                                    <i class="fa-regular fa-calendar-check me-2"></i><?= (!empty($d['tanggal']) && $d['tanggal'] != '0000-00-00') ? date('d-m-Y', strtotime($d['tanggal'])) : '-'; ?>
                                </td>
                                <td style="color: #ffffff !important; font-weight: 700;"><?= htmlspecialchars($d['nama_barang']); ?></td>
                                <td>
                                    <span class="badge-jumlah-custom"><?= number_format($d['jumlah']); ?></span>
                                </td>
                                <td style="color: #e5e7eb !important;">
                                    <span style="color: #22d3ee !important;"><i class="fa-solid fa-location-arrow me-2"></i></span><?= htmlspecialchars($d['tujuan']); ?>
                                </td>
                            </tr>
                            <?php 
                            } 
                        } else {
                            echo "<tr><td colspan='5' class='text-center py-5 style='color: #9ca3af !important;'>Tidak ada data log transaksi terbaru.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
<div class="chart-card mb-4">
    <h5>📊 Top 10 Volume Material Tertinggi</h5>

    <div style="position: relative; height:400px; width:100%">
        <canvas id="chartMaterial"></canvas>
    </div>
</div>
        <div class="chart-card mt-4">
    <h5>📊 Grafik Peremajaan MTU 2022-2023</h5>

    <div style="position: relative; height:380px; width:100%">
        <canvas id="chartMTU"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    var dropdown = document.getElementsByClassName("dropdown-btn");
    for (var i = 0; i < dropdown.length; i++) {
        dropdown[i].addEventListener("click", function() {
            this.classList.toggle("active");
            var dropdownContent = this.nextElementSibling;
            if (dropdownContent.style.display === "block") {
                dropdownContent.style.display = "none";
            } else {
                dropdownContent.style.display = "block";
            }
        });
    }
</script>

<script>
    const ctx = document.getElementById('chartMaterial').getContext('2d');
    
    const brandGradient = ctx.createLinearGradient(0, 0, 0, 350);
    brandGradient.addColorStop(0, '#38bdf8');   
    brandGradient.addColorStop(1, '#2563eb');   

    new Chart(ctx, {
        
        type: 'bar',
        data: {
            labels: <?= json_encode($label); ?>,
            datasets: [{
                label: 'Jumlah Kuantitas',
                data: <?= json_encode($jumlah_material); ?>,
                backgroundColor: brandGradient, 
                hoverBackgroundColor: '#ffffff',
                borderRadius: 8, 
                borderWidth: 0,
                barThickness: 20 
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: { 
                y: { 
                    beginAtZero: true,
                    grid: { color: 'rgba(255, 255, 255, 0.04)' },
                    ticks: { color: '#94a3b8', font: { family: 'Inter', weight: 500 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#cbd5e1', font: { family: 'Inter', size: 11, weight: 600 } }
                }
            }
        }
    });
</script>
<script>
const ctxMTU = document.getElementById('chartMTU').getContext('2d');

const gradientMTU = ctxMTU.createLinearGradient(0,0,0,400);
gradientMTU.addColorStop(0,'#38bdf8');
gradientMTU.addColorStop(1,'#2563eb');

new Chart(ctxMTU,{
    type:'bar',
    data:{
        labels: <?= json_encode($labelMTU); ?>,
        datasets:[{
            data: <?= json_encode($dataMTU); ?>,
            backgroundColor:gradientMTU,
            borderRadius:8,
            barThickness:35
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false,
        plugins:{
            legend:{display:false}
        },
        scales:{
            y:{
                beginAtZero:true,
                ticks:{ color:'#94a3b8' },
                grid:{ color:'rgba(255,255,255,0.05)' }
            },
            x:{
                ticks:{ color:'#cbd5e1' },
                grid:{ display:false }
            }
        }
    }
});
</script>
</body>
</html>