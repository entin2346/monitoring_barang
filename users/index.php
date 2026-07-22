<?php
session_start();

// 1. Validasi Login
if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

// Hubungkan ke database
include "../config/koneksi.php";

// 2. Proteksi Hak Akses: Hanya Administrator (Admin) yang boleh mengelola Manajemen User
// Memeriksa berbagai kemungkinan penamaan session role login Anda
$session_role = $_SESSION['role'] ?? $_SESSION['level'] ?? $_SESSION['hak_akses'] ?? $_SESSION['status'] ?? '';
if (strcasecmp($session_role, 'Admin') !== 0) {
    echo "<script>
            alert('Akses ditolak! Akun Anda (" . htmlspecialchars($session_role ?: 'Tanpa Role') . ") tidak memiliki hak akses Administrator.');
            window.location.href = '../dashboard/index.php';
          </script>";
    exit;
}

/* ==========================================================================
   AMBIL DATA PENGGUNA/USER DARI DATABASE
   ========================================================================== */
$query_users = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Manajemen User</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
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

        /* SIDEBAR STYLE */
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

        /* KARTU HEADER MANAJEMEN USER */
        .user-header-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.02);
        }
        .user-header-title {
            font-size: 1.45rem;
            font-weight: 800;
            color: #1e3a8a;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .user-header-desc {
            font-size: 0.88rem;
            color: var(--text-muted);
            margin: 0;
        }

        /* CONTAINER UTAMA TABEL */
        .user-container-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.02);
        }

        .section-sub-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--primary);
            border-bottom: 2px solid var(--primary);
            padding-bottom: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* CYBER TABLE STYLE */
        .cyber-table-wrapper { 
            background: #ffffff !important; border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden;
        }
        .table-cyber { width: 100%; border-collapse: separate; border-spacing: 0; margin: 0; }
        .table-cyber thead th { 
            background: #f8fafc !important; color: #475569 !important; 
            font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 16px 22px; 
            border-bottom: 1px solid var(--border-color);
        }
        .table-cyber tbody tr:not(:last-child) td { border-bottom: 1px solid var(--border-color); }
        .table-cyber tbody tr:hover td { background: #f8fafc; }
        .table-cyber tbody td { padding: 15px 22px; font-size: 0.88rem; vertical-align: middle; color: var(--text-main) !important; }

        /* BADGE ROLE */
        .badge-role-user {
            background-color: #e0f2fe !important;
            color: #0284c7 !important;
            border: 1px solid #bae6fd;
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 0.8rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .badge-role-admin {
            background-color: #ffe4e6 !important;
            color: #e11d48 !important;
            border: 1px solid #fecdd3;
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 0.8rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* TOMBOL AKSI */
        .btn-action-edit {
            background-color: #ffb703 !important;
            border-color: #ffb703 !important;
            color: #ffffff !important;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 6px 16px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: opacity 0.2s;
        }
        .btn-action-hapus {
            background-color: #ef4444 !important;
            border-color: #ef4444 !important;
            color: #ffffff !important;
            font-size: 0.78rem;
            font-weight: 700;
            padding: 6px 16px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: opacity 0.2s;
        }
        .btn-action-edit:hover, .btn-action-hapus:hover {
            opacity: 0.9;
            color: #ffffff;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><i class="fa-solid fa-bolt text-primary"></i> I-CALM Panel</h3>
    <a href="../dashboard/index.php">
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
        <a href="../kategori/stok/stok.php">Stok</a>
        <a href="../kategori/non_stok/non_stok.php">Non Stok</a>
        <a href="../kategori/non_po/non_po.php">Non PO</a>
        <a href="../kategori/ex_bongkaran/ex_bongkaran.php">Ex Bongkaran</a>
        <a href="../kategori/pre_memory/pre_memory.php">Pre Memory</a>
        <a href="../kategori/peminjaman/peminjaman.php">Peminjaman</a>
        <a href="../kategori/pemakaian/pemakaian.php">Pemakaian</a>
    </div>
    
    <button class="dropdown-btn">
        <span class="menu-content-wrapper">
            <i class="fa-solid fa-file-import menu-icon"></i>
            <span>Import</span>
        </span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
          <div class="dropdown-container">
        <a href="/monitoring_barang/import/material.php">Import Material</a>
        <a href="/monitoring_barang/import/ba.php">Import BA</a>
        <a href="/monitoring_barang/import/form_stok.php">Import Stok</a>
        <a href="/monitoring_barang/import/form_non_stok.php">Import Non Stok</a>
        <a href="/monitoring_barang/import/form_non_po.php">Import Non PO</a>
        <a href="/monitoring_barang/import/form_ex_bongkaran.php">Import Ex Bongkaran</a>
        <a href="/monitoring_barang/import/form_pre_memory.php">Import Pre Memory</a>
        <a href="/monitoring_barang/import/form_peminjaman.php">Import Peminjaman</a>
        <a href="/monitoring_barang/import/form_pemakaian.php">Import Pemakaian</a>
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

    <!-- MENU AKTIF MANAJEMEN USER -->
    <a href="index.php" class="active-menu">
        <span class="menu-content-wrapper">
            <i class="fa-solid fa-user-gear"></i>
            <span>Manajemen User</span>
        </span>
    </a>
    
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
                <i class="fa-solid fa-user-gear text-primary me-2"></i> MANAJEMEN USER
                <span class="ms-2 fw-normal" style="font-size: 0.95rem; color: var(--text-muted);">/ Pengaturan Hak Akses</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <!-- 1. Header Manajemen User -->
        <div class="user-header-card">
            <div class="user-header-title">
                <i class="fa-solid fa-user-shield"></i> Manajemen User
            </div>
            <p class="user-header-desc">
                Kelola hak akses pengelola sistem, tambah akun baru, atau perbarui data pengguna.
            </p>
        </div>

        <!-- 2. Tabel Daftar Pengguna -->
        <div class="user-container-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <span class="section-sub-title m-0">
                    <i class="fa-solid fa-thumbtack text-danger"></i> Daftar Pengguna Sistem
                </span>
                <!-- Tombol Tambah User baru -->
                <a href="tambah.php" class="btn btn-primary btn-sm fw-bold px-3 py-2" style="border-radius: 8px;">
                    <i class="fa-solid fa-user-plus me-1"></i> Tambah User
                </a>
            </div>

            <div class="cyber-table-wrapper table-responsive">
                <table class="table-cyber">
                    <thead>
                        <tr>
                            <th width="80" class="text-center">No</th>
                            <th>Nama Pengguna</th>
                            <th>Username</th>
                            <th width="200">Role / Hak Akses</th>
                            <th width="200" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        if(mysqli_num_rows($query_users) > 0) {
                            while($d = mysqli_fetch_assoc($query_users)){
                                // Solusi Fallback: Memeriksa variasi nama kolom database agar tidak terjadi Warning
                                $nama_user = $d['nama'] ?? $d['nama_lengkap'] ?? $d['nama_user'] ?? 'Tanpa Nama';
                                $role_user = $d['role'] ?? $d['level'] ?? $d['hak_akses'] ?? 'User';

                                // Menentukan class badge role (misal: 'Admin' atau 'User')
                                $role_badge = (strcasecmp($role_user, 'Admin') === 0) ? 
                                    '<span class="badge-role-admin"><i class="fa-solid fa-user-tie"></i> Admin</span>' : 
                                    '<span class="badge-role-user"><i class="fa-solid fa-user"></i> User</span>';
                            ?>
                            <tr>
                                <td class="text-center fw-bold" style="color: var(--text-muted) !important; font-size:0.85rem;">
                                    <?= $no++; ?>
                                </td>
                                <td class="fw-bold"><?= htmlspecialchars($nama_user); ?></td>
                                <td class="text-secondary fw-semibold"><?= htmlspecialchars($d['username'] ?? 'Tidak ada'); ?></td>
                                <td><?= $role_badge; ?></td>
                                <td class="text-center">
                                    <!-- Link Edit -->
                                    <a href="edit.php?id=<?= $d['id'] ?? ''; ?>" class="btn-action-edit me-1">
                                        <i class="fa-solid fa-pen"></i> Edit
                                    </a>
                                    <!-- Link Hapus -->
                                    <a href="hapus.php?id=<?= $d['id'] ?? ''; ?>" class="btn-action-hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                            <?php 
                            } 
                        } else {
                            echo "<tr><td colspan='5' class='text-center py-5 text-muted fw-bold'><i class='fa-solid fa-users-slash d-block fs-2 mb-2 opacity-45'></i>Belum ada data user terdaftar.</td></tr>";
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
    // Handle Dropdown Sidebar Klik
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
</script>
</body>
</html>