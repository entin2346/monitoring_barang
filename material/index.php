<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

// 1. Ambil kata kunci dari URL (Metode GET)
$cari = $_GET['cari'] ?? '';

// 2. SOLUSI ENTER: Ubah kembali tanda '+' dari browser menjadi spasi normal
$cari = str_replace('+', ' ', $cari);

// 3. Amankan data dari SQL Injection & bersihkan spasi liar di ujung kata
$cari = mysqli_real_escape_string($conn, $cari);
$cari_clean = trim($cari); 

$limit = 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page < 1){ $page = 1; }
$offset = ($page - 1) * $limit;

// 4. SOLUSI FILTER AWALAN: Menggunakan '$cari_clean%' tanpa persen di depan
if ($cari_clean !== '') {
    $whereCari = "(nama_material LIKE '$cari_clean%')";
} else {
    // Jika tidak sedang mencari, tampilkan semua data
    $whereCari = "1=1"; 
}

/* TOTAL DATA BERDASARKAN FILTER */
$total_query = mysqli_query($conn,"
    SELECT COUNT(*) AS total
    FROM material_gudang
    WHERE nama_material <> ''
    AND $whereCari
");

$total_data = mysqli_fetch_assoc($total_query)['total'];
$total_halaman = ceil($total_data / $limit);

/* TOTAL STOK GLOBAL */
$total_stok = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT SUM(jumlah) AS total FROM material_gudang")
);

/* QUERY DATA MATERIAL GUDANG */
$query = mysqli_query($conn,"
    SELECT *
    FROM material_gudang
    WHERE nama_material <> ''
    AND $whereCari
    ORDER BY
        CASE
            WHEN no_rak='A1' THEN 1
            WHEN no_rak='A2' THEN 2
            WHEN no_rak='A3' THEN 3
            WHEN no_rak='B1' THEN 4
            WHEN no_rak='B2' THEN 5
            WHEN no_rak='B3' THEN 6
            WHEN no_rak='B4' THEN 7
            WHEN no_rak='C1' THEN 8
            WHEN no_rak='C2' THEN 9
            WHEN no_rak='C3' THEN 10
            WHEN no_rak='D1' THEN 11
            WHEN no_rak='D2' THEN 12
            WHEN no_rak='D3' THEN 13
            WHEN no_rak='E1' THEN 14
            WHEN no_rak='E2' THEN 15
            WHEN no_rak='E3' THEN 16
            WHEN no_rak='F1' THEN 17
            WHEN no_rak='F2' THEN 18
            WHEN no_rak='G1' THEN 19
            WHEN no_rak='G2' THEN 20
            WHEN no_rak='G3' THEN 21
            WHEN no_rak='H1' THEN 22
            WHEN no_rak='H2' THEN 23
            WHEN no_rak='H3' THEN 24
            WHEN no_rak='I1' THEN 25
            WHEN no_rak='I2' THEN 26
            WHEN no_rak='J1' THEN 27
            WHEN no_rak='J2' THEN 28
            WHEN no_rak='K1' THEN 29
            WHEN no_rak='K2' THEN 30
            WHEN no_rak='K3' THEN 31
            WHEN no_rak='M1' THEN 32
            WHEN no_rak='M2' THEN 33
            WHEN no_rak='M3' THEN 34
            WHEN no_rak='PETI' THEN 35
            WHEN no_rak='RAK ISOLATOR' THEN 36
            ELSE 999
        END ASC,
        nama_material ASC
    LIMIT $offset,$limit
");

if(!$query){
    die(mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Material Gudang Premium</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-base: #e2e8f0;            
            --bg-body: #f8fafc;
            --bg-card: rgba(255, 255, 255, 0.55); 
            --primary-brand: #0284c7;       
            --accent-blue: #3b82f6;         
            --accent-purple: #8b5cf6;
            --text-main: #1e293b;            
            --text-muted: #64748b;          
            --border-glass: rgba(255, 255, 255, 0.7);
            --border-light: rgba(148, 163, 184, 0.15);
        }

        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: var(--bg-base); }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 20px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--primary-brand); }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { 
            background: radial-gradient(circle at top right, #dbeafe 0%, var(--bg-base) 60%, #e0e7ff 100%);
            color: var(--text-main); min-height: 100vh; overflow-x: hidden;
        }

        .sidebar {
            position: fixed; left: 0; top: 0; width: 260px; height: 100%;
            background: linear-gradient(135deg, rgba(15, 32, 67, 0.95) 0%, rgba(9, 53, 122, 0.9) 50%, rgba(2, 132, 199, 0.85) 100%);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.1); padding-top: 28px; z-index: 1000;
            box-shadow: 5px 0 30px rgba(9, 53, 122, 0.15); 
        }
        
        .sidebar h3 { font-size: 1.4rem; font-weight: 800; padding: 0 24px; margin-bottom: 35px; color: #ffffff; display: flex; align-items: center;}
        .sidebar h3 i { color: #38bdf8 !important; text-shadow: 0 0 12px rgba(56, 189, 248, 0.6); }
        
        .sidebar a, .dropdown-btn { 
            display: flex; align-items: center; justify-content: space-between; color: rgba(255, 255, 255, 0.7); 
            text-decoration: none; padding: 14px 24px; font-size: 0.95rem; font-weight: 600; border: none; background: none; width: 100%; cursor: pointer;
        }
        .sidebar a:hover, .dropdown-btn:hover { background: rgba(255, 255, 255, 0.08); color: #ffffff; }
        .sidebar .active-menu {
            color: #ffffff !important; 
            background: linear-gradient(90deg, rgba(56, 189, 248, 0.2) 0%, rgba(56, 189, 248, 0.03) 100%) !important; 
            border-left: 4px solid #38bdf8; padding-left: 20px;
        }
        .sidebar .active-menu i { color: #38bdf8 !important; }
        .sidebar a i, .dropdown-btn i { margin-right: 12px; font-size: 1.1rem; width: 20px; text-align: center; color: rgba(255, 255, 255, 0.6); }
        
        .dropdown-container { display: none; background: rgba(0, 0, 0, 0.15); padding: 4px 0; }
        .dropdown-container a { padding: 11px 24px 11px 56px; font-size: 0.85rem; font-weight: 500; color: rgba(255, 255, 255, 0.6); }
        .dropdown-container a:hover { color: #38bdf8; background: transparent; }

        .sidebar .logout-button { margin-top: 30px; background: rgba(239, 68, 68, 0.1); border-radius: 12px; width: calc(100% - 32px); margin-left: 16px; padding: 12px 16px; }
        .sidebar .logout-button:hover { background: rgba(239, 68, 68, 0.25) !important; }
        .sidebar .logout-button i, .sidebar .logout-button .menu-text { color: #fca5a5 !important; }

        .content { margin-left: 260px; }
        .navbar-custom { 
            background: rgba(255, 255, 255, 0.45); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
            padding: 18px 32px; border-bottom: 1px solid var(--border-glass); position: sticky; top: 0; z-index: 999;
        }
        .navbar-custom .navbar-brand { color: var(--text-main); font-weight: 800; font-size: 1.4rem; }

        .main-body-wrapper { padding: 40px 32px; min-height: calc(100vh - 78px);}

        .glass-stat-card {
            background: var(--bg-card); border: 1px solid var(--border-glass); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border-radius: 24px; padding: 28px 24px; box-shadow: 0 10px 30px -10px rgba(148, 163, 184, 0.12);
        }
        .stat-label { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1.2px; color: var(--text-muted); font-weight: 700; margin-bottom: 12px; }
        .stat-number { font-size: 2.3rem; font-weight: 800; color: var(--text-main); margin: 0; }

        .cyber-search-box {
            background: rgba(255, 255, 255, 0.4); border: 1px solid var(--border-glass); border-radius: 24px; padding: 24px; position: relative;
        }
        .input-cyber-group { background: rgba(255, 255, 255, 0.8); border: 1px solid var(--border-light); border-radius: 14px; overflow: hidden; }
        .input-cyber-group input { background: transparent !important; border: none !important; color: var(--text-main) !important; padding: 14px 20px; }

        .autocomplete-box {
            position: absolute; left: 0; right: 0; top: 100%; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 12px;
            margin-top: 6px; z-index: 99999 !important; max-height: 260px; overflow-y: auto; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); padding: 5px 0;
        }
        .autocomplete-item { padding: 12px 16px; cursor: pointer; border-bottom: 1px solid #f1f5f9; color: var(--text-main); font-size: 0.95rem; text-align: left;}
        .autocomplete-item:hover { background: #f1f5f9; color: var(--primary-brand); }

        .cyber-table-wrapper { background: rgba(255, 255, 255, 0.5) !important; backdrop-filter: blur(20px); border: 1px solid var(--border-glass); border-radius: 24px; overflow: hidden; }
        .table-cyber { width: 100%; border-collapse: collapse; margin: 0; }
        .table-cyber thead th { background: rgba(241, 245, 249, 0.8) !important; color: var(--text-muted) !important; font-weight: 700; font-size: 0.75rem; padding: 18px 24px; }
        .table-cyber tbody tr { border-bottom: 1px solid var(--border-light); }
        .table-cyber tbody tr:hover { background: rgba(255, 255, 255, 0.8) !important; }
        .table-cyber tbody td { padding: 16px 24px; font-size: 0.9rem; color: var(--text-main) !important; font-weight: 500; }

        .neon-badge-stock { background: rgba(2, 132, 199, 0.08) !important; color: var(--primary-brand) !important; border: 1px solid rgba(2, 132, 199, 0.15) !important; padding: 4px 12px; border-radius: 8px; font-weight: 700; }
        .badge-status-baik { background: rgba(16, 185, 129, 0.1) !important; color: #10b981 !important; border: 1px solid rgba(16, 185, 129, 0.2) !important; padding: 6px 12px; border-radius: 8px; font-weight: 700; }
        .badge-status-other { background: rgba(245, 158, 11, 0.1) !important; color: #d97706 !important; border: 1px solid rgba(245, 158, 11, 0.2) !important; padding: 6px 12px; border-radius: 8px; font-weight: 700; }

        .btn-action-group { background: rgba(241, 245, 249, 0.9); border: 1px solid var(--border-light); border-radius: 10px; display: inline-flex; }
        .btn-action-item { background: transparent; border: none; color: var(--text-muted); padding: 8px 14px; text-decoration: none; }
        .btn-action-item.btn-view:hover { color: var(--primary-brand); }
        .btn-action-item.btn-edit:hover { color: #d97706; }
        .btn-action-item.btn-delete:hover { color: #ef4444; }

        .pagination .page-link { background-color: rgba(255, 255, 255, 0.6) !important; border: 1px solid var(--border-glass) !important; color: var(--text-muted) !important; padding: 10px 18px; border-radius: 10px; margin: 0 3px; }
        .pagination .page-item.active .page-link { background: linear-gradient(135deg, var(--primary-brand), var(--accent-blue)) !important; color: #ffffff !important; box-shadow: 0 4px 15px rgba(2, 132, 199, 0.25); }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><i class="fa-solid fa-bolt me-2"></i>I-CALM Panel</h3>
    <a href="../dashboard/index.php"><span><i class="fa-solid fa-chart-pie me-2"></i>Dashboard</span></a>
    <button class="dropdown-btn active">
        <span><i class="fa-solid fa-layer-group"></i>Monitoring</span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container" style="display: block;">
        <a href="../material/index.php" class="active-menu">Material Gudang</a>
        <a href="../ba/index.php">Database BA</a>
    </div>
    <button class="dropdown-btn">
        <span><i class="fa-solid fa-file-import"></i>Import</span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container">
        <a href="../import/material.php">Import Material</a>
        <a href="../import/ba.php">Import BA</a>
    </div>
    <a href="../login/logout.php" class="logout-button"><span><i class="fa-solid fa-right-from-bracket"></i>Logout</span></a>
</div>

<div class="content">
    <nav class="navbar navbar-custom">
        <div class="container-fluid px-0">
            <span class="navbar-brand mb-0 h1 d-flex align-items-center">
                <i class="fa-solid fa-boxes-stacked text-primary me-2"></i> KENDALI LOGISTIK 
                <span class="ms-2" style="font-weight: 400; font-size: 0.95rem; color: var(--text-muted);">/ Material Gudang</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="glass-stat-card">
                    <div class="stat-label">Total Klasifikasi Material Filtered</div>
                    <div class="stat-number"><?= number_format($total_data); ?> <span style="font-size: 1.1rem; font-weight: 500; color: var(--text-muted);">Item</span></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-stat-card">
                    <div class="stat-label">Volume Akumulasi Stok Global</div>
                    <div class="stat-number" style="color: #10b981;"><?= number_format($total_stok['total'] ?? 0); ?> <span style="font-size: 1.1rem; font-weight: 500; color: var(--text-muted);">Unit</span></div>
                </div>
            </div>
        </div>

        <div class="cyber-search-box mb-4">
            <form id="formCari" method="GET">
                <div class="row g-3">
                    <div class="col-md-10" style="position: relative;">
                        <div class="input-group input-cyber-group">
                            <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <input 
                                type="text" 
                                name="cari" 
                                id="cari"
                                class="form-control" 
                                autocomplete="off"
                                placeholder="Cari nama material..." 
                                value="<?= htmlspecialchars($cari); ?>"
                            >
                        </div>
                        <div id="hasil_autocomplete" class="autocomplete-box" style="display:none;"></div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 fw-bold py-3" style="border-radius: 14px; background: linear-gradient(135deg, #0284c7, #2563eb); border: none;"><i class="fa-solid fa-sliders me-1"></i> Saring Data</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="mb-3 d-flex justify-content-between align-items-center">
            <a href="tambah.php" class="btn btn-primary btn-sm fw-bold px-4 py-2" style="border-radius: 12px; background: var(--primary-brand); border:none;"><i class="fa-solid fa-plus-circle me-1"></i> Registrasi Material Baru</a>
            <?php if(!empty($cari_clean)) { ?>
                <span class="small text-muted">Hasil filter awalan: <strong class="text-primary">"<?= htmlspecialchars($cari_clean) ?>"</strong></span>
            <?php } ?>
        </div>

        <div class="cyber-table-wrapper table-responsive mb-4">
            <table class="table-cyber">
                <thead>
                    <tr>
                        <th width="70" class="text-center">ID</th>
                        <th>NAMA KELOMPOK MATERIAL GUDANG</th>
                        <th width="100">SATUAN</th>
                        <th width="160">JUMLAH STOK</th>
                        <th width="140">NOMOR RAK</th>
                        <th width="140">STATUS KONDISI</th>
                        <th>LOKASI PENYIMPANAN</th>
                        <th width="180" class="text-center">MANAJEMEN OPSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = $offset + 1;
                    if(mysqli_num_rows($query) > 0){
                        while($d = mysqli_fetch_assoc($query)){
                    ?>
                    <tr>
                        <td class="text-center fw-bold" style="color: var(--text-muted) !important;"><?= $no++; ?></td>
                        <td style="font-weight: 700; color: var(--text-main) !important; font-size:0.95rem;"><?= htmlspecialchars($d['nama_material']); ?></td>
                        <td><span class="small px-2 py-1 rounded fw-semibold" style="background: rgba(0,0,0,0.03); border: 1px solid var(--border-light); color: var(--text-muted);"><?= htmlspecialchars($d['satuan']); ?></span></td>
                        <td><span class="neon-badge-stock"><?= number_format($d['jumlah']); ?></span></td>
                        <td style="font-weight: 600; color: var(--text-main);"><i class="fa-solid fa-layer-group text-muted me-2 small"></i><?= htmlspecialchars($d['no_rak']); ?></td>
                        <td>
                            <?php
                            if(strtoupper($d['kondisi']) == 'BAIK'){
                                echo "<span class='badge-status-baik'><i class='fa-solid fa-circle-check me-1 small'></i> BAIK</span>";
                            }else{
                                echo "<span class='badge-status-other'><i class='fa-solid fa-triangle-exclamation me-1 small'></i> ".htmlspecialchars(strtoupper($d['kondisi']))."</span>";
                            }
                            ?>
                        </td>
                        <td><i class="fa-solid fa-map-pin text-danger opacity-70 me-2 small"></i><?= htmlspecialchars($d['lokasi_penyimpanan']); ?></td>
                        <td class="text-center">
                            <div class="btn-action-group">
                                <a href="detail.php?id=<?= $d['id']; ?>" class="btn-action-item btn-view"><i class="fa-solid fa-expand"></i></a>
                                <a href="edit.php?id=<?= $d['id']; ?>" class="btn-action-item btn-edit"><i class="fa-solid fa-user-pen"></i></a>
                                <a href="hapus.php?id=<?= $d['id']; ?>" class="btn-action-item btn-delete" onclick="return confirm('Hapus permanen?')"><i class="fa-solid fa-trash-can"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php
                        }
                    }else{
                    ?>
                    <tr>
                        <td colspan="8" class="text-center py-5" style="color: var(--text-muted) !important;">
                            <i class="fa-solid fa-satellite-dish d-block fs-1 mb-3 text-muted opacity-40"></i> Material tidak ditemukan.
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php if($total_halaman > 1) { ?>
        <nav class="pb-5">
            <ul class="pagination justify-content-center">
                <?php if($page > 1){ ?>
                <li class="page-item"><a class="page-link" href="?cari=<?= urlencode($cari_clean); ?>&page=<?= $page-1; ?>"><i class="fa-solid fa-chevron-left"></i></a></li>
                <?php } ?>
                <?php
                for($i=1; $i<=$total_halaman; $i++){
                    if($i == 1 || $i == $total_halaman || ($i >= $page-2 && $i <= $page+2)){
                ?>
                    <li class="page-item <?= ($i==$page)?'active':''; ?>">
                        <a class="page-link" href="?cari=<?= urlencode($cari_clean); ?>&page=<?= $i; ?>"><?= $i; ?></a>
                    </li>
                <?php
                    }
                }
                ?>
                <?php if($page < $total_halaman){ ?>
                <li class="page-item"><a class="page-link" href="?cari=<?= urlencode($cari_clean); ?>&page=<?= $page+1; ?>"><i class="fa-solid fa-chevron-right"></i></a></li>
                <?php } ?>
            </ul>
        </nav>
        <?php } ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    /* DROPDOWN SIDEBAR */
    var dropdown = document.getElementsByClassName("dropdown-btn");
    for (var i = 0; i < dropdown.length; i++) {
        dropdown[i].addEventListener("click", function() {
            this.classList.toggle("active");
            var dropdownContent = this.nextElementSibling;
            dropdownContent.style.display = (dropdownContent.style.display === "block") ? "none" : "block";
        });
    }

    /* AUTOCOMPLETE MECHANISM */
    const inputCari = document.getElementById("cari");
    const hasil = document.getElementById("hasil_autocomplete");

    inputCari.addEventListener("input", function(){
        let keyword = this.value.trim();
        if(keyword.length < 1){
            hasil.style.display = "none";
            return;
        }

        fetch("autocomplete.php?keyword=" + encodeURIComponent(keyword))
        .then(res => res.text())
        .then(data => {
            if(data.trim() !== ""){
                hasil.innerHTML = data;
                hasil.style.display = "block"; 
            }else{
                hasil.style.display = "none";
            }
        })
        .catch(err => console.error(err));
    });

    function pilihMaterial(namaEncoded){
        let nama = decodeURIComponent(namaEncoded);
        inputCari.value = nama;
        hasil.style.display = "none";
        
        // Kirim form otomatis setelah pilihan diklik
        document.getElementById("formCari").submit();
    }

    document.addEventListener("click", function(e){
        if(!hasil.contains(e.target) && e.target !== inputCari){
            hasil.style.display = "none";
        }
    });
</script>
</body>
</html>