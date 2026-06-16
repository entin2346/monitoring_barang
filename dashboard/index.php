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
    <title>I-CALM Dashboard | Ocean Premium Live</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        :root {
            --bg-base: #e6eef8;            
            --bg-body: #f8fafc;
            --bg-card: rgba(255, 255, 255, 0.7); 
            --primary-brand: #0284c7;       
            --accent-blue: #2563eb;         
            --accent-purple: #7c3aed;
            --text-main: #0f172a;           
            --text-muted: #475569;          
            --border-glass: rgba(255, 255, 255, 0.8);
            --border-light: rgba(148, 163, 184, 0.12);
        }

        /* CUSTOM SCROLLBAR */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg-base); }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 20px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--primary-brand); }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body { 
            background: radial-gradient(circle at top right, #dbeafe 0%, var(--bg-base) 50%, #eff6ff 100%);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ANIMASI ENTRANCE */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        /* ========================================================
           SIDEBAR PREMIUM
        ========================================================= */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100%;
            background: linear-gradient(135deg, rgba(11, 27, 60, 0.98) 0%, rgba(7, 43, 102, 0.96) 60%, rgba(2, 110, 168, 0.95) 100%);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 28px;
            z-index: 1000;
            box-shadow: 10px 0 40px rgba(7, 43, 102, 0.15); 
        }
        
        .sidebar h3 { 
            font-size: 1.35rem; font-weight: 800; padding: 0 24px; margin-bottom: 35px; letter-spacing: -0.5px; color: #ffffff;
            display: flex; align-items: center; gap: 10px;
        }
        .sidebar h3 i { color: #38bdf8 !important; text-shadow: 0 0 15px rgba(56, 189, 248, 0.7); }
        
        .sidebar a, .dropdown-btn { 
            display: flex; align-items: center; justify-content: space-between; color: rgba(255, 255, 255, 0.65); 
            text-decoration: none; padding: 13px 24px; font-size: 0.9rem; font-weight: 600; border: none; background: none; width: 100%; transition: all 0.25s; cursor: pointer;
        }
        .sidebar a:hover, .dropdown-btn:hover { background: rgba(255, 255, 255, 0.06); color: #ffffff; }

        .sidebar .active-menu {
            color: #ffffff !important; 
            background: linear-gradient(90deg, rgba(56, 189, 248, 0.15) 0%, rgba(56, 189, 248, 0.02) 100%) !important; 
            border-left: 4px solid #38bdf8; padding-left: 20px;
        }
        .sidebar .active-menu i { color: #38bdf8 !important; }
        .sidebar a i, .dropdown-btn i { font-size: 1.05rem; width: 22px; text-align: center; color: rgba(255, 255, 255, 0.5); margin-right: 10px;}
        .sidebar a:hover i, .dropdown-btn:hover i { color: #ffffff; }
        
        .sidebar .menu-text { flex-grow: 1; }
        .dropdown-chevron { font-size: 0.75rem !important; transition: transform 0.2s ease; color: rgba(255, 255, 255, 0.4) !important; }
        .dropdown-btn.active .dropdown-chevron { transform: rotate(180deg); color: #38bdf8 !important; }

        .dropdown-container { display: none; background: rgba(0, 0, 0, 0.12); padding: 4px 0; }
        .dropdown-container a { padding: 10px 24px 10px 56px; font-size: 0.85rem; font-weight: 500; color: rgba(255, 255, 255, 0.55); }
        .dropdown-container a:hover { color: #38bdf8; background: transparent; }

        .sidebar .logout-button {
            margin-top: 40px; background: rgba(239, 68, 68, 0.08); border-radius: 12px; width: calc(100% - 32px); margin-left: 16px; padding: 12px 16px;
        }
        .sidebar .logout-button:hover { background: rgba(239, 68, 68, 0.2) !important; }
        .sidebar .logout-button i, .sidebar .logout-button .menu-text { color: #fca5a5 !important; }

        /* ========================================================
           CONTENT AREA COMPONENTS
        ========================================================= */
        .content { margin-left: 260px; }
        
        .navbar-custom { 
            background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            padding: 18px 32px; border-bottom: 1px solid var(--border-glass); position: sticky; top: 0; z-index: 999;
        }
        .navbar-custom .navbar-brand { color: var(--text-main); font-weight: 800; font-size: 1.3rem; letter-spacing: -0.5px;}
        .navbar-custom .navbar-text { color: var(--text-muted) !important; font-size: 0.85rem; font-weight: 600; }

        .main-body { padding: 35px 32px; min-height: calc(100vh - 78px);}

        /* STAT CARDS */
        .card-stat {
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border-radius: 24px; padding: 26px 24px; position: relative; overflow: hidden;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 10px 30px -10px rgba(148, 163, 184, 0.08), 0 1px 1px rgba(255,255,255,0.9) inset;
        }
        .card-stat .stat-title { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); font-weight: 700; margin-bottom: 8px; }
        .card-stat .stat-value { font-size: 2.2rem; font-weight: 800; color: var(--text-main); margin: 0; letter-spacing: -1px; }
        
        .card-stat .stat-icon-box {
            position: absolute; right: 24px; top: 50%; transform: translateY(-50%);
            width: 56px; height: 56px; border-radius: 16px;
            display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
            transition: all 0.3s ease;
        }

        .card-blue { border-left: 5px solid var(--primary-brand); }
        .card-blue .stat-icon-box { background: rgba(2, 132, 199, 0.08); color: var(--primary-brand); }
        .card-blue:hover { transform: translateY(-5px); box-shadow: 0 20px 35px -5px rgba(2, 132, 199, 0.12), 0 0 0 1px rgba(2, 132, 199, 0.2); }
        
        .card-cyan { border-left: 5px solid var(--accent-blue); }
        .card-cyan .stat-icon-box { background: rgba(37, 99, 213, 0.08); color: var(--accent-blue); }
        .card-cyan:hover { transform: translateY(-5px); box-shadow: 0 20px 35px -5px rgba(37, 99, 213, 0.12), 0 0 0 1px rgba(37, 99, 213, 0.2); }
        
        .card-dark { border-left: 5px solid var(--accent-purple); }
        .card-dark .stat-icon-box { background: rgba(124, 58, 237, 0.08); color: var(--accent-purple); }
        .card-dark:hover { transform: translateY(-5px); box-shadow: 0 20px 35px -5px rgba(124, 58, 237, 0.12), 0 0 0 1px rgba(124, 58, 237, 0.2); }

        /* LIVE INDICATOR */
        .pulse-indicator {
            display: inline-block; width: 8px; height: 8px; background-color: #10b981; border-radius: 50%; margin-right: 8px;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); animation: pulse 1.6s infinite; vertical-align: middle;
        }
        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        /* GLASS TABLE */
        .table-container-icalm {
            background: rgba(255, 255, 255, 0.6) !important; backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-glass); border-radius: 24px; overflow: hidden; box-shadow: 0 15px 35px rgba(148,163,184,0.05);
        }
        .table-icalm-custom { width: 100%; border-collapse: collapse; margin: 0; background: transparent !important; }
        .table-icalm-custom thead th {
            background: rgba(241, 245, 249, 0.7) !important; color: var(--text-muted) !important;
            font-weight: 700; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 0.8px; padding: 16px 20px;
            border-bottom: 1px solid var(--border-light); text-align: left;
        }
        .table-icalm-custom tbody tr { background: transparent !important; border-bottom: 1px solid var(--border-light); transition: all 0.2s; }
        .table-icalm-custom tbody tr:hover { background: rgba(255, 255, 255, 0.85) !important; }
        .table-icalm-custom tbody td { padding: 15px 20px; font-size: 0.88rem; vertical-align: middle; color: var(--text-main) !important; font-weight: 500; }

        .badge-jumlah-custom {
            background: rgba(2, 132, 199, 0.08) !important; color: var(--primary-brand) !important;
            border: 1px solid rgba(2, 132, 199, 0.15) !important; border-radius: 8px; padding: 4px 12px; font-weight: 700; font-size: 0.78rem; display: inline-block;
        }

        /* CHART CARDS */
        .chart-card {
            background: var(--bg-card); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-glass); border-radius: 24px; padding: 25px; height: 100%;
            box-shadow: 0 15px 35px -10px rgba(148, 163, 184, 0.08), 0 1px 1px rgba(255,255,255,0.9) inset;
            transition: transform 0.3s ease;
        }
        .chart-card h5 { font-size: 1.05rem; font-weight: 700; color: var(--text-main); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><i class="fa-solid fa-bolt"></i>I-CALM Panel</h3>

    <a href="../dashboard/index.php" class="active-menu">
        <span><i class="fa-solid fa-chart-pie"></i><span class="menu-text">Dashboard</span></span>
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

    <a href="../login/logout.php" class="logout-button">
        <span><i class="fa-solid fa-right-from-bracket"></i><span class="menu-text">Logout</span></span>
    </a>
</div>

<div class="content">
    <nav class="navbar navbar-custom">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-bolt text-primary me-2"></i>I-CALM</span>
            <span class="navbar-text d-none d-sm-block">
                Inventory Control & Logistics Monitoring
            </span>
        </div>
    </nav>

    <div class="main-body animate-fade-in">
        <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h3 class="fw-bold tracking-tight m-0" style="font-size: 1.65rem; font-weight: 800; color: var(--text-main);">Dashboard Monitoring</h3>
                <p class="text-muted m-0 mt-1 small">Sistem integrasi & kendali cerdas distribusi logistik aktif.</p>
            </div>
            <div class="px-3 py-2 rounded-4 border border-white" style="background: rgba(255,255,255,0.4); backdrop-filter: blur(10px); box-shadow: 0 4px 15px rgba(0,0,0,0.01);">
                <span class="pulse-indicator"></span>
                <span class="small fw-semibold text-muted">Operator: <span class="text-dark fw-bold"><?= htmlspecialchars($_SESSION['nama'] ?? 'User'); ?></span></span>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card-stat card-blue">
                    <div class="stat-title">Total Jenis Material</div>
                    <h2 class="stat-value"><?= number_format($total_material['total'] ?? 0); ?></h2>
                    <div class="stat-icon-box"><i class="fa-solid fa-box-open"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-stat card-cyan">
                    <div class="stat-title">Total Berita Acara (BA)</div>
                    <h2 class="stat-value"><?= number_format($total_ba['total'] ?? 0); ?></h2>
                    <div class="stat-icon-box"><i class="fa-solid fa-file-invoice"></i></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card-stat card-dark">
                    <div class="stat-title">Total Kuantitas Stok</div>
                    <h2 class="stat-value"><?= number_format($total_stok['total'] ?? 0); ?></h2>
                    <div class="stat-icon-box"><i class="fa-solid fa-cubes"></i></div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <div class="chart-card">
                    <h5><i class="fa-solid fa-chart-line text-primary"></i> Top 10 Volume Material</h5>
                    <div style="position: relative; height:340px; width:100%">
                        <canvas id="chartMaterial"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-card">
                    <h5><i class="fa-solid fa-chart-area text-purple" style="color:var(--accent-purple);"></i> Peremajaan MTU Berjalan</h5>
                    <div style="position: relative; height:340px; width:100%">
                        <canvas id="chartMTU"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <div class="d-flex align-items-center mb-3">
                <h5 class="fw-bold m-0" style="font-size: 1.1rem; color: var(--text-main);"><i class="fa-solid fa-clock-rotate-left text-secondary me-2"></i>10 Manifest Berita Acara Terbaru</h5>
            </div>
            <div class="table-container-icalm table-responsive">
                <table class="table-icalm-custom">
                    <thead>
                        <tr>
                            <th width="60">NO</th>
                            <th width="160">TANGGAL DATA</th>
                            <th>NAMA BARANG / MATERIAL</th>
                            <th width="140">VOLUME</th>
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
                                <td style="color: var(--text-muted) !important; font-weight: bold;"><?= $no++; ?></td>
                                <td style="color: var(--accent-blue) !important; font-weight: 600;">
                                    <i class="fa-regular fa-calendar me-2 opacity-70"></i><?= (!empty($d['tanggal']) && $d['tanggal'] != '0000-00-00') ? date('d-m-Y', strtotime($d['tanggal'])) : '-'; ?>
                                </td>
                                <td style="color: var(--text-main) !important; font-weight: 700;"><?= htmlspecialchars($d['nama_barang']); ?></td>
                                <td>
                                    <span class="badge-jumlah-custom"><?= number_format($d['jumlah']); ?></span>
                                </td>
                                <td style="color: var(--text-muted) !important;">
                                    <span style="color: var(--primary-brand) !important;"><i class="fa-solid fa-location-dot me-2 opacity-75"></i></span><?= htmlspecialchars($d['tujuan']); ?>
                                </td>
                            </tr>
                            <?php 
                            } 
                        } else {
                            echo "<tr><td colspan='5' class='text-center py-5' style='color: var(--text-muted) !important;'>Tidak ada data log transaksi terbaru.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Handler Dropdown Sidebar
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
    /* ==========================================
       CHART MATERIAL (PREMIUM SMOOTH AREA SPLINE)
    ========================================== */
    const ctx = document.getElementById('chartMaterial').getContext('2d');
    
    const fillGradient = ctx.createLinearGradient(0, 0, 0, 300);
    fillGradient.addColorStop(0, 'rgba(37, 99, 235, 0.25)');   
    fillGradient.addColorStop(1, 'rgba(2, 132, 199, 0.0)');   

    new Chart(ctx, { 
        type: 'bar',


        data: {
            labels: <?= json_encode($label); ?>,
            datasets: [{
                label: 'Stok Material',
                data: <?= json_encode($jumlah_material); ?>,
                borderColor: '#2563eb',
                borderWidth: 3,
                pointBackgroundColor: '#2563eb',
                pointHoverRadius: 7,
                tension: 0.4, // Membuat garis melengkung dinamis (Spline)
                fill: true,
                backgroundColor: fillGradient
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { 
                y: { 
                    beginAtZero: true,
                    grid: { color: 'rgba(148, 163, 184, 0.08)' },
                    ticks: { color: '#64748b', font: { family: 'Plus Jakarta Sans', weight: 500 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#0f172a', font: { family: 'Plus Jakarta Sans', size: 10, weight: 600 } }
                }
            }
        }
    });

    /* ==========================================
       CHART MTU (PREMIUM GRADIENT BAR)
    ========================================== */
    const canvasMTU = document.getElementById('chartMTU');

if (canvasMTU) {

    const ctxMTU = canvasMTU.getContext('2d');

    const gradientMTU = ctxMTU.createLinearGradient(0,0,0,400);
    gradientMTU.addColorStop(0,'#38bdf8');
    gradientMTU.addColorStop(1,'#2563eb');

    new Chart(ctxMTU,{
    type:'bar',
    data:{
        labels: <?= json_encode($labelMTU); ?>,
        datasets:[{
            label:'Jumlah MTU',
            data: <?= json_encode($dataMTU); ?>,
            backgroundColor: gradientMTU,
            borderRadius:8,
            barThickness:35
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false,
        plugins:{
            legend:{
                display:false
            }
        },
        scales:{
            y:{
                beginAtZero:true
            }
        }
    }
});

}
</script>

</body>
</html>