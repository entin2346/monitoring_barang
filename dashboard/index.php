<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

/* ==========================================================================
   1. DATA GRAFIK MATERIAL (SOLUSI FIX: GROUP BY + MAX AGAR UNIK & BERVARIASI)
   ========================================================================== */
$grafik = mysqli_query($conn,"
    SELECT nama_material, MAX(jumlah) as jumlah_terbesar
    FROM material_gudang
    WHERE nama_material IS NOT NULL
    AND nama_material <> ''
    AND jumlah > 0
    GROUP BY nama_material
    ORDER BY jumlah_terbesar DESC
    LIMIT 10
");

$label = [];
$jumlah_material = [];

while($g = mysqli_fetch_assoc($grafik)){
    $label[] = $g['nama_material'];
    $jumlah_material[] = (int)$g['jumlah_terbesar']; 
}

/* ==========================================================================
   2. DATA STATISTIK DASHBOARD (Menggunakan nama variabel terpisah agar tidak bentrok)
   ========================================================================== */
$q_total_material = mysqli_query($conn, "SELECT COUNT(*) as total_item FROM material_gudang WHERE nama_material <> ''");
$res_material = mysqli_fetch_assoc($q_total_material);

$q_total_ba = mysqli_query($conn, "SELECT COUNT(*) as total_berkas FROM database_ba WHERE nama_barang <> ''");
$res_ba = mysqli_fetch_assoc($q_total_ba);

$q_total_stok = mysqli_query($conn, "SELECT SUM(jumlah) as total_fisik FROM material_gudang WHERE jumlah > 0");
$res_stok = mysqli_fetch_assoc($q_total_stok);

/* ==========================================================================
   3. GRAFIK DISTRIBUSI MATERIAL PER ULTG
   ========================================================================== */
$labelULTG = [
    'Jeneponto',
    'Maros',
    'Panakukang',
    'Parepare',
    'Sidrap',
    'Watampone'
];

$dataAlatUji = [];
$dataAlatKerja = [];
$dataMaterial = [];

$ultgList = [
    'JENEPONTO',
    'MAROS',
    'PANAKUKANG',
    'PARE',
    'SIDRAP',
    'WATAMPONE'
];

foreach($ultgList as $ultg){
    $q1 = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT COALESCE(SUM(jumlah),0) total
        FROM database_ba
        WHERE tujuan LIKE '%$ultg%'
        AND jenis_barang='ALAT UJI'
    "));
    $dataAlatUji[] = (int)$q1['total'];

    $q2 = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT COALESCE(SUM(jumlah),0) total
        FROM database_ba
        WHERE tujuan LIKE '%$ultg%'
        AND jenis_barang='ALAT KERJA'
    "));
    $dataAlatKerja[] = (int)$q2['total'];

    $q3 = mysqli_fetch_assoc(mysqli_query($conn,"
        SELECT COALESCE(SUM(jumlah),0) total
        FROM database_ba
        WHERE tujuan LIKE '%$ultg%'
        AND jenis_barang='MATERIAL'
    "));
    $dataMaterial[] = (int)$q3['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Dashboard Panel</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <script>
        Chart.register(ChartDataLabels);
    </script>
    
    <style>
        :root {
            --bg-body: #f4f7fc;
            --bg-card: #ffffff; 
            --primary: #0284c7;       
            --text-main: #0f172a;           
            --text-muted: #64748b;          
            --border-color: rgba(148, 163, 184, 0.12);
            --bg-sidebar: #d0e1f9; 
        }

        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--primary); }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body { 
            background: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* SIDEBAR STYLE SESUAI DENGAN DETAIL.PHP */
        .sidebar {
            position: fixed; left: 0; top: 0; width: 260px; height: 100%;
            background-color: var(--bg-sidebar);
            border-right: 1px solid rgba(2, 132, 199, 0.15);
            padding: 35px 20px; z-index: 1050; display: flex; flex-direction: column;
        }
        .sidebar h3 { 
            font-size: 1.25rem; font-weight: 800; color: #1e3a8a; 
            margin-bottom: 35px; padding-left: 6px; display: flex; align-items: center; gap: 10px;
        }
        
        .sidebar a, .dropdown-btn { 
            display: flex; align-items: center; justify-content: space-between; 
            color: #1e3a8a; text-decoration: none; padding: 11px 14px; 
            font-size: 0.9rem; font-weight: 700; border: none; background: transparent; 
            width: 100%; cursor: pointer; border-radius: 10px; margin-bottom: 5px; 
            transition: all 0.2s ease-in-out;
        }
        
        .sidebar a:hover, .dropdown-btn:hover { 
            color: #025a9c; 
            background: rgba(2, 132, 199, 0.12); 
            transform: translateX(4px);
        }
        
        .sidebar .menu-content-wrapper { display: flex; align-items: center; gap: 12px; }
        .sidebar a i, .dropdown-btn i.menu-icon { font-size: 1.05rem; width: 20px; text-align: center; color: #1e40af; }
        .sidebar a:hover i, .dropdown-btn:hover i.menu-icon { color: #025a9c; }
        
        .sidebar .active-menu {
            color: #ffffff !important; 
            background: #0284c7 !important; 
            font-weight: 700;
            box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25);
            border-radius: 10px;
            transform: translateX(4px);
        }
        .sidebar .active-menu i { color: #ffffff !important; }

        .dropdown-chevron { font-size: 0.75rem !important; transition: transform 0.2s ease; color: #1e40af !important; }
        .dropdown-btn.active .dropdown-chevron { transform: rotate(180deg); color: #ffffff !important; }
        .dropdown-btn.active { color: #ffffff !important; background: #0284c7 !important; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); }
        .dropdown-btn.active i.menu-icon { color: #ffffff !important; }
        
        .dropdown-container { display: none; padding-left: 12px; margin-bottom: 6px; margin-top: 4px; }
        .dropdown-container a { 
            padding: 9px 14px; font-size: 0.85rem; color: #1e40af; font-weight: 600; background: rgba(255, 255, 255, 0.3);
        }
        .dropdown-container a:hover { background: #ffffff; color: #0284c7; }

        .sidebar .logout-button { 
            margin-top: auto; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: 10px; 
        }
        .sidebar .logout-button i, .sidebar .logout-button span { color: #b91c1c !important; }
        .sidebar .logout-button:hover { background: #fee2e2; transform: none; }

        .content { margin-left: 260px; position: relative; }
        .navbar-custom { 
            background: #ffffff;
            padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999;
        }
        .navbar-custom .navbar-brand { color: var(--text-main); font-weight: 800; font-size: 1.3rem; }
        .main-body-wrapper { padding: 40px; }

        .glass-stat-card {
            background: var(--bg-card); border: 1px solid var(--border-color);
            border-radius: 16px; padding: 26px; position: relative;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .glass-stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04);
        }
        .stat-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.8px; color: var(--text-muted); font-weight: 700; margin-bottom: 8px; }
        .stat-number { font-size: 2rem; font-weight: 800; color: var(--text-main); margin: 0; }
        .card-blue { border-left: 5px solid var(--primary); }
        .card-green { border-left: 5px solid #10b981; }
        .card-orange { border-left: 5px solid #f59e0b; }

        .chart-card {
            background: var(--bg-card); border: 1px solid var(--border-color);
            border-radius: 16px; padding: 26px; height: 100%;
        }
        .chart-card h5 { font-size: 1rem; font-weight: 700; color: var(--text-main); margin-bottom: 22px; display: flex; align-items: center; gap: 8px; }

        .cyber-table-wrapper { 
            background: #ffffff !important; border: 1px solid var(--border-color); border-radius: 16px; overflow: hidden;
        }
        .table-cyber { width: 100%; border-collapse: separate; border-spacing: 0; margin: 0; }
        .table-cyber thead th { 
            background: #f8fafc !important; color: #334155 !important; 
            font-weight: 700; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.5px; padding: 16px 22px; 
            border-bottom: 1px solid var(--border-color);
        }
        .table-cyber tbody tr:not(:last-child) td { border-bottom: 1px solid var(--border-color); }
        .table-cyber tbody tr:hover td { background: #f8fafc; }
        .table-cyber tbody td { padding: 15px 22px; font-size: 0.88rem; vertical-align: middle; color: var(--text-main) !important; }

        .neon-badge-stock { 
            background: rgba(2, 132, 199, 0.06) !important; color: var(--primary) !important; 
            border: 1px solid rgba(2, 132, 199, 0.1) !important; border-radius: 8px; padding: 5px 12px; font-weight: 700; font-size: 0.8rem; display: inline-block;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><i class="fa-solid fa-bolt text-primary"></i> I-CALM Panel</h3>
    <a href="../dashboard/index.php" class="active-menu">
        <span class="menu-content-wrapper">
            <i class="fa-solid fa-chart-pie"></i>
            <span>Dashboard</span>
        </span>
    </a>
    
    <button class="dropdown-btn">
        <span class="menu-content-wrapper">
            <i class="fa-solid fa-layer-group menu-icon"></i>
            <span>Monitoring</span>
        </span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../material/index.php">Material Gudang</a>
        <a href="../ba/index.php">Database BA</a>
    </div>

    <button class="dropdown-btn">
        <span class="menu-content-wrapper">
            <i class="fa-solid fa-tags menu-icon"></i>
            <span>Kategori</span>
        </span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../kategori/stok.php">Stok</a>
        <a href="../kategori/non_stok.php">Non Stok</a>
        <a href="../kategori/non_po.php">Non PO</a>
        <a href="../kategori/ex_bongkaran.php">Ex Bongkaran</a>
        <a href="../kategori/pre_memory.php">Pre Memory</a>
        <a href="../kategori/peminjaman.php">Peminjaman</a>
        <a href="../kategori/pemakaian.php">Pemakaian</a>
    </div>
    
    <button class="dropdown-btn">
        <span class="menu-content-wrapper">
            <i class="fa-solid fa-file-import menu-icon"></i>
            <span>Import</span>
        </span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../import/material.php">Import Material</a>
        <a href="../import/ba.php">Import BA</a>
        <a href="../import/form_stok.php">Import Stok</a>
        <a href="../import/form_non_stok.php">Import Non Stok</a>
        <a href="../import/form_non_po.php">Import Non PO</a>
        <a href="../import/form_ex_bongkaran.php">Import Ex Bongkaran</a>
        <a href="../import/form_pre_memory.php">Import Pre Memory</a>
        <a href="../import/form_peminjaman.php">Import Peminjaman</a>
        <a href="../import/form_pemakaian.php">Import Pemakaian</a>
    </div>
    
    <button class="dropdown-btn">
        <span class="menu-content-wrapper">
            <i class="fa-solid fa-file-export menu-icon"></i>
            <span>Export</span>
        </span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../export/material_excel.php">Export Material</a>
        <a href="../export/ba_excel.php">Export BA</a>
    </div>
    
    <a href="../login/logout.php" class="logout-button">
        <span class="menu-content-wrapper">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Logout</span>
        </span>
    </a>
</div>

<div class="content">
    <nav class="navbar navbar-custom">
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center">
                <i class="fa-solid fa-boxes-stacked text-primary me-2"></i> KENDALI LOGISTIK 
                <span class="ms-2 fw-normal" style="font-size: 0.95rem; color: var(--text-muted);">/ Dashboard Panel</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="glass-stat-card card-blue">
                    <div class="stat-label">Total Jenis Material</div>
                    <div class="stat-number"><?= number_format($res_material['total_item'] ?? 0); ?> <span class="fw-normal text-muted" style="font-size: 1.1rem;">Item</span></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-stat-card card-orange">
                    <div class="stat-label">Total Berita Acara (BA)</div>
                    <div class="stat-number" style="color: #f59e0b;"><?= number_format($res_ba['total_berkas'] ?? 0); ?> <span class="fw-normal text-muted" style="font-size: 1.1rem;">Berkas</span></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-stat-card card-green">
                    <div class="stat-label">Total Kuantitas Stok</div>
                    <div class="stat-number" style="color: #10b981;"><?= number_format($res_stok['total_fisik'] ?? 0); ?> <span class="fw-normal text-muted" style="font-size: 1.1rem;">Unit</span></div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <div class="chart-card">
                    <h5><i class="fa-solid fa-chart-bar text-primary"></i> Top 10 Volume Material</h5>
                    <div style="position: relative; height:360px; width:100%">
                        <canvas id="chartMaterial"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-card">
                    <h5><i class="fa-solid fa-chart-bar text-success"></i> Distribusi Material per ULTG</h5>
                    <div style="position: relative; height:360px; width:100%">
                        <canvas id="chartMTU"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <div class="d-flex align-items-center mb-3">
                <h5 class="fw-bold m-0" style="font-size: 1rem; color: var(--text-main);"><i class="fa-solid fa-clock-rotate-left text-muted me-2"></i>10 Manifest Berita Acara Terbaru</h5>
            </div>
            <div class="cyber-table-wrapper table-responsive">
                <table class="table-cyber">
                    <thead>
                        <tr>
                            <th width="70" class="text-center">NO</th>
                            <th width="170">TANGGAL DATA</th>
                            <th>NAMA BARANG / MATERIAL</th>
                            <th width="145">VOLUME</th>
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
                                <td class="text-center fw-bold" style="color: var(--text-muted) !important; font-size:0.85rem;"><?= str_pad($no++, 2, '0', STR_PAD_LEFT); ?></td>
                                <td class="fw-semibold text-secondary">
                                    <?= (!empty($d['tanggal']) && $d['tanggal'] != '0000-00-00') ? date('d-m-Y', strtotime($d['tanggal'])) : '-'; ?>
                                </td>
                                <td class="fw-bold"><?= htmlspecialchars($d['nama_barang']); ?></td>
                                <td>
                                    <span class="neon-badge-stock"><?= number_format($d['jumlah']); ?></span>
                                </td>
                                <td class="text-muted fw-medium">
                                    <i class="fa-solid fa-map-pin text-danger opacity-70 me-2 small"></i><?= htmlspecialchars($d['tujuan']); ?>
                                </td>
                            </tr>
                            <?php 
                            } 
                        } else {
                            echo "<tr><td colspan='5' class='text-center py-5 text-muted fw-bold'><i class='fa-solid fa-box-open d-block fs-2 mb-2 opacity-45'></i>Tidak ada data log transaksi terbaru.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.querySelectorAll('.dropdown-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const container = this.nextElementSibling;
            this.classList.toggle('active');
            
            if (window.getComputedStyle(container).display === "block") {
                container.style.display = "none";
            } else {
                container.style.display = "block";
            }
        });
    });

    /* ==========================================
        CHART MATERIAL
    ========================================== */
    const ctx = document.getElementById('chartMaterial').getContext('2d');
    new Chart(ctx, { 
        type: 'bar',
        data: {
            labels: <?= json_encode($label); ?>,
            datasets: [{
                label: 'Stok Material',
                data: <?= json_encode($jumlah_material); ?>,
                backgroundColor: 'rgba(2, 132, 199, 0.2)', 
                borderColor: '#0284c7',
                borderWidth: 2,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 25,
                    bottom: 10
                }
            },
            plugins: { 
                legend: { display: false },
                datalabels: { 
                    display: true,
                    color: '#0284c7',
                    anchor: 'end',
                    align: 'top',
                    offset: 4,
                    font: { family: 'Plus Jakarta Sans', size: 9.5, weight: '700' },
                    formatter: function(value) { return value; }
                }
            },
            scales: { 
                y: { 
                    beginAtZero: true,
                    grace: '10%', 
                    grid: { color: '#f1f5f9' },
                    ticks: { color: '#94a3b8', font: { family: 'Plus Jakarta Sans', size: 10 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { 
                        color: '#334155', 
                        font: { family: 'Plus Jakarta Sans', size: 9.5, weight: 600 },
                        maxRotation: 45, 
                        minRotation: 45, 
                        padding: 8,      
                        callback: function(value) {
                            const label = this.getLabelForValue(value);
                            if (label.length > 18) {
                                return label.substring(0, 18) + '...';
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });

    /* ==========================================
        CHART MTU
    ========================================== */
    const canvasMTU = document.getElementById('chartMTU');
    if(canvasMTU){
        const ctxMTU = canvasMTU.getContext('2d');
        new Chart(ctxMTU,{
            type:'bar',
            data:{
                labels: <?= json_encode($labelULTG); ?>,
                datasets:[
                    {
                        label:'Alat Uji',
                        data: <?= json_encode($dataAlatUji); ?>,
                        backgroundColor: '#3b82f6',
                        borderRadius: 4,
                        barPercentage: 0.65,
                        categoryPercentage: 0.7
                    },
                    {
                        label:'Alat Kerja',
                        data: <?= json_encode($dataAlatKerja); ?>,
                        backgroundColor: '#10b981',
                        borderRadius: 4,
                        barPercentage: 0.65,
                        categoryPercentage: 0.7
                    },
                    {
                        label:'Material',
                        data: <?= json_encode($dataMaterial); ?>,
                        backgroundColor: '#f59e0b',
                        borderRadius: 4,
                        barPercentage: 0.65,
                        categoryPercentage: 0.7
                    }
                ]
            },
            options:{
                responsive:true,
                maintainAspectRatio:false,
                layout: {
                    padding: {
                        top: 20 
                    }
                },
                plugins:{
                    legend:{
                        display:true,
                        position:'top',
                        labels: { font: { family: 'Plus Jakarta Sans', weight: 600, size: 10.5 }, boxWidth: 8, usePointStyle: true }
                    },
                    datalabels:{
                        color:'#334155',
                        anchor:'end',
                        align:'top',
                        offset: 1,
                        formatter:function(value){ return value > 0 ? value : ''; },
                        font:{ family: 'Plus Jakarta Sans', weight:'700', size: 9.5 }
                    }
                },
                scales:{
                    y:{
                        beginAtZero:true,
                        grace: '10%', 
                        grid: { color: '#f1f5f9' },
                        ticks: { color: '#94a3b8', font: { family: 'Plus Jakarta Sans', size: 10 } }
                    },
                    x:{
                        grid: { display: false },
                        ticks: { color: '#334155', font: { family: 'Plus Jakarta Sans', weight: 600, size: 10 } }
                    }
                }
            }
        });
    }
</script>
</body>
</html>