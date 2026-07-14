<?php
session_start();

// 1. Validasi Login
if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

// 2. Batasi Akses: Hanya Administrator (Admin) yang boleh mengedit user
$session_role = $_SESSION['role'] ?? $_SESSION['level'] ?? $_SESSION['hak_akses'] ?? $_SESSION['status'] ?? '';
if (strcasecmp($session_role, 'Admin') !== 0) {
    echo "<script>
            alert('Akses ditolak! Akun Anda (" . htmlspecialchars($session_role ?: 'Tanpa Role') . ") tidak memiliki hak akses Administrator untuk mengedit pengguna.');
            window.location.href = '../dashboard/index.php';
          </script>";
    exit;
}

// Hubungkan ke database
include "../config/koneksi.php";

// 3. Tangkap ID user yang akan diedit
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id_user = $_GET['id'];

// Ambil data user lama dari database menggunakan Prepared Statement
$query = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
mysqli_stmt_bind_param($query, "i", $id_user);
mysqli_stmt_execute($query);
$result = mysqli_stmt_get_result($query);
$d = mysqli_fetch_assoc($result);

// Jika user tidak ditemukan
if (!$d) {
    echo "<script>
            alert('Data pengguna tidak ditemukan!');
            window.location.href = 'index.php';
          </script>";
    exit;
}

// Deteksi nama kolom dinamis di database Anda agar tidak terjadi "Undefined index"
$nama_db = $d['nama'] ?? $d['nama_lengkap'] ?? $d['nama_user'] ?? '';
$role_db = $d['role'] ?? $d['level'] ?? $d['hak_akses'] ?? 'User';

// Menentukan nama kolom role asli yang ada di tabel database Anda
$nama_kolom_role = 'role'; // Default
if (array_key_exists('level', $d)) {
    $nama_kolom_role = 'level';
} elseif (array_key_exists('hak_akses', $d)) {
    $nama_kolom_role = 'hak_akses';
}

// Menentukan nama kolom nama asli yang ada di tabel database Anda
$nama_kolom_nama = 'nama'; // Default
if (array_key_exists('nama_lengkap', $d)) {
    $nama_kolom_nama = 'nama_lengkap';
} elseif (array_key_exists('nama_user', $d)) {
    $nama_kolom_nama = 'nama_user';
}

// 4. Proses Update Data setelah Form dikirim (POST)
if (isset($_POST['update'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $role_baru = $_POST['role'];
    $password_baru = $_POST['password'];

    // Cek apakah kolom password baru diisi oleh Admin
    if (!empty($password_baru)) {
        // PERBAIKAN: Menggunakan MD5 agar sinkron dengan sistem login dan register Anda
        $password_hash = md5($password_baru); 
        
        // Update data dengan password baru
        $query_update = "UPDATE users SET 
                            $nama_kolom_nama = '$nama', 
                            username = '$username', 
                            password = '$password_hash', 
                            $nama_kolom_role = '$role_baru' 
                         WHERE id = '$id_user'";
    } else {
        // PERBAIKAN: Jika password baru kosong, JANGAN perbarui kolom password di database!
        $query_update = "UPDATE users SET 
                            $nama_kolom_nama = '$nama', 
                            username = '$username', 
                            $nama_kolom_role = '$role_baru' 
                         WHERE id = '$id_user'";
    }

    // Eksekusi query update
    if (mysqli_query($conn, $query_update)) {
        echo "<script>
                alert('Data pengguna berhasil diperbarui!');
                window.location.href = 'index.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal memperbarui data: " . mysqli_error($conn) . "');
              </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>I-CALM | Edit User</title>
    
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
        .sidebar .logout-button:hover { background: #fee2e2; }

        .content { margin-left: 260px; position: relative; }
        .navbar-custom { 
            background: #ffffff;
            padding: 20px 40px; border-bottom: 1px solid var(--border-color); position: sticky; top: 0; z-index: 999;
        }
        .navbar-custom .navbar-brand { color: var(--text-main); font-weight: 800; font-size: 1.3rem; }
        .main-body-wrapper { padding: 40px; }

        /* FORM CONTAINER */
        .form-container-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 30px;
            max-width: 650px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.02);
        }

        .section-sub-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary);
            border-bottom: 2px solid var(--primary);
            padding-bottom: 10px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label {
            font-weight: 700;
            font-size: 0.85rem;
            color: #334155;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            padding: 10px 14px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }

        .btn-simpan {
            background-color: var(--primary) !important;
            border-color: var(--primary) !important;
            color: #ffffff;
            font-weight: 700;
            font-size: 0.9rem;
            padding: 10px 20px;
            border-radius: 8px;
        }

        .btn-batal {
            background-color: #f1f5f9 !important;
            border-color: #e2e8f0 !important;
            color: #475569 !important;
            font-weight: 700;
            font-size: 0.9rem;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-batal:hover {
            background-color: #e2e8f0 !important;
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
        <a href="../import/material.php">Import Material</a>
        <a href="../import/ba.php">Import BA</a>
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
                <span class="ms-2 fw-normal" style="font-size: 0.95rem; color: var(--text-muted);">/ Edit Pengguna</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="form-container-card">
            <span class="section-sub-title">
                <i class="fa-solid fa-user-pen"></i> Form Edit Data Pengguna
            </span>

            <form action="" method="POST">
                <!-- Nama Pengguna -->
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Pengguna</label>
                    <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($nama_db); ?>" required>
                </div>

                <!-- Username -->
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($d['username'] ?? ''); ?>" required>
                </div>

                <!-- Password Baru (Opsional) -->
                <div class="mb-3">
                    <label for="password" class="form-label">Password Baru (Dikosongkan jika tidak ingin diubah)</label>
                    <!-- Input placeholder diubah agar memudahkan admin -->
                    <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan password baru untuk mengganti password lama">
                </div>

                <!-- Hak Akses / Role (Dinamis Timbal Balik) -->
                <div class="mb-4">
                    <label for="role" class="form-label">Hak Akses / Role</label>
                    <select class="form-select" id="role" name="role" required>
                        <option value="User" <?= (strcasecmp($role_db, 'User') === 0) ? 'selected' : ''; ?>>User</option>
                        <option value="Admin" <?= (strcasecmp($role_db, 'Admin') === 0) ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>

                <!-- Tombol Aksi -->
                <div class="d-flex gap-2 justify-content-end">
                    <a href="index.php" class="btn btn-batal"><i class="fa-solid fa-xmark me-2"></i>Batal</a>
                    <button type="submit" name="update" class="btn btn-simpan"><i class="fa-solid fa-floppy-disk me-2"></i>Simpan Perubahan</button>
                </div>
            </form>
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