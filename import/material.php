<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

$jumlah_import = 0;
$sukses_import = false;
$gagal_import  = false; 
$sukses_hapus  = false;

// 1. AKSI UNTUK MENGHAPUS SEMUA DATA (RESET DATABASE)
if(isset($_POST['kosongkan_data'])){
    if(mysqli_query($conn, "TRUNCATE TABLE material_gudang")){
        $sukses_hapus = true;
    }
}

// 2. AKSI IMPORT DATA MATERIAL
if(isset($_POST['import'])){
    if($_FILES['file']['error'] == 0){
        $file = $_FILES['file']['tmp_name'];
        $handle = fopen($file, "r");

        if ($handle !== FALSE) {
            // Prepared statements database
            $cek_stmt = $conn->prepare("SELECT id FROM material_gudang WHERE LOWER(nama_material) = LOWER(?) AND LOWER(lokasi_penyimpanan) = LOWER(?) LIMIT 1");
            $stmt = $conn->prepare("INSERT INTO material_gudang 
                (nama_material, satuan, jumlah, no_rak, kondisi, lokasi_penyimpanan, keterangan) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");

            while(($data = fgetcsv($handle, 0, ",")) !== FALSE){
                // Skip jika baris kosong total
                if (empty($data) || !isset($data[0]) || trim($data[0]) === '') {
                    continue;
                }

                // Bersihkan spasi kosong di setiap ujung data kolom
                foreach ($data as $key => $val) {
                    $data[$key] = trim($val);
                }

                // Lewati baris nama kolom / header tabel agar tidak masuk database
                if (strtolower($data[0]) === 'storage location description' || strtolower($data[0]) === 'no') {
                    continue;
                }

                // =========================================================================
                // PENENTUAN JALUR DATA BERDASARKAN HASIL COPIAN FILE CSV ASLI ANDA
                // =========================================================================
                if (isset($data[8]) && ($data[8] === 'Fast moving' || $data[8] === 'Slow moving' || $data[8] === 'Jenis Pergerakan')) {
                    // JALUR 1: FILE material_stok(1).csv 
                    $nama_material = isset($data[2]) ? $data[2] : '';
                    $satuan        = isset($data[3]) ? $data[3] : '-';
                    $raw_jumlah    = isset($data[4]) ? $data[4] : '0';
                    $no_rak        = '-';
                    $kondisi       = 'BAIK';
                    $lokasi        = isset($data[0]) ? $data[0] : 'GUDANG UPT';
                    $keterangan    = isset($data[5]) ? 'Kategori: ' . $data[5] : '';
                } 
                else {
                    // JALUR 2: FILE material_nonstok.csv
                    $nama_material = isset($data[1]) ? $data[1] : '';
                    $satuan        = isset($data[2]) ? $data[2] : '-';
                    $raw_jumlah    = isset($data[3]) ? $data[3] : '0';
                    $no_rak        = isset($data[4]) ? $data[4] : '-';
                    $kondisi       = isset($data[5]) ? $data[5] : '-';
                    $lokasi        = isset($data[6]) ? $data[6] : '-';
                    $keterangan    = isset($data[7]) ? $data[7] : '';
                }

                if (empty($nama_material)) {
                    continue;
                }

                // Bersihkan sisa tanda petik dua pembungkus string bawaan CSV
                $nama_material = trim($nama_material, '"');

                // Ubah format angka pecahan desimal Excel (misal: 4.0) menjadi Integer bulat (4)
                $jumlah = (int)round((float)$raw_jumlah);

                // Saringan anti-duplikat berdasarkan Nama Material & Lokasi
                // $cek_stmt->bind_param("ss", $nama_material, $lokasi);
                $cek_stmt->execute();
                $cek_stmt->store_result();
                if($cek_stmt->num_rows > 0){
                    continue; // Lewati kalau data sudah pernah masuk
                }

                // Eksekusi Simpan murni ke MySQL
                $stmt->bind_param("ssissss", $nama_material, $satuan, $jumlah, $no_rak, $kondisi, $lokasi, $keterangan);
                if($stmt->execute()){
                    $jumlah_import++;
                }
            }

            fclose($handle);
            $cek_stmt->close();
            $stmt->close();
            $sukses_import = true;
        } else {
            $gagal_import = true;
        }
    } else {
        $gagal_import = true;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I-CALM | Manajemen Material Gudang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --bg-base: #f4f7fc; 
            --bg-card: #ffffff; 
            --primary-brand: #0284c7; 
            --text-main: #0f172a; 
            --text-muted: #64748b; 
            --border-glass: rgba(148, 163, 184, 0.15); 
            --bg-sidebar: #d0e1f9; 
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: var(--bg-base); color: var(--text-main); min-height: 100vh; }
        
        .sidebar { position: fixed; left: 0; top: 0; width: 260px; height: 100%; background-color: var(--bg-sidebar); border-right: 1px solid rgba(2, 132, 199, 0.15); padding: 35px 20px; display: flex; flex-direction: column; }
        .sidebar h3 { font-size: 1.25rem; font-weight: 800; color: #1e3a8a; margin-bottom: 35px; display: flex; align-items: center; gap: 10px; }
        .sidebar a, .dropdown-btn { display: flex; align-items: center; justify-content: space-between; color: #1e3a8a; text-decoration: none; padding: 11px 14px; font-size: 0.9rem; font-weight: 700; border: none; background: transparent; width: 100%; cursor: pointer; border-radius: 10px; margin-bottom: 5px; text-align: left; }
        .sidebar a:hover, .dropdown-btn:hover { background: rgba(255, 255, 255, 0.5); }
        .sidebar .active-menu { color: #ffffff !important; background: #0284c7 !important; box-shadow: 0 4px 14px rgba(2, 132, 199, 0.25); }
        
        .dropdown-container { display: none; padding-left: 12px; margin-bottom: 6px; }
        .dropdown-container a { padding: 9px 14px; font-size: 0.85rem; background: rgba(255, 255, 255, 0.3); text-decoration: none; display: block; color: #1e3a8a; }
        
        .content { margin-left: 260px; padding: 40px; }
        .cyber-import-container { background: var(--bg-card); border: 1px solid var(--border-glass); border-radius: 24px; padding: 40px; box-shadow: 0 25px 50px -12px rgba(148, 163, 184, 0.2); }
        .upload-drag-zone { border: 2px dashed rgba(2, 132, 199, 0.25); background: rgba(255, 255, 255, 0.4); border-radius: 20px; padding: 40px 30px; text-align: center; }
        .file-input-cyber { background: #ffffff !important; border: 1px solid rgba(148, 163, 184, 0.3) !important; border-radius: 12px; padding: 12px; max-width: 450px; margin: 24px auto 0 auto; }
        .btn-action-submit { background: linear-gradient(135deg, #0284c7, #0369a1); border: none; color: #fff; font-weight: 700; padding: 14px 32px; border-radius: 12px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3><i class="fa-solid fa-bolt text-primary"></i> I-CALM Panel</h3>
    <a href="../dashboard/index.php"><span><i class="fa-solid fa-chart-pie me-2"></i>Dashboard</span></a>
    
    <button class="dropdown-btn"><span><i class="fa-solid fa-layer-group me-2"></i>Monitoring</span><i class="fa-solid fa-chevron-down"></i></button>
    <div class="dropdown-container">
        <a href="../material/index.php">Material Gudang</a>
        <a href="../ba/index.php">Database BA</a>
    </div>

    <button class="dropdown-btn active"><span><i class="fa-solid fa-file-import me-2"></i>Import</span><i class="fa-solid fa-chevron-down"></i></button>
    <div class="dropdown-container" style="display: block;">
        <a href="material.php" class="active-menu">Import Material</a>
        <a href="ba.php">Import BA</a>
    </div>

    <a href="../login/logout.php" class="mt-auto btn btn-light text-danger fw-bold">Logout</a>
</div>

<div class="content">
    <div class="alert alert-warning d-flex justify-content-between align-items-center mb-4 p-3" style="border-radius:16px;">
        <div>
            <h6 class="fw-bold text-dark mb-1"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>Pembersihan Data Sebelum Upload</h6>
            <p class="text-muted small mb-0">Disarankan mengosongkan database terlebih dahulu jika ingin memperbarui seluruh isinya agar tidak duplikat.</p>
        </div>
        <form method="POST" id="formReset">
            <button type="submit" name="kosongkan_data" class="btn btn-danger fw-bold px-4" style="border-radius:10px;">
                <i class="fa-solid fa-trash-can me-2"></i>Kosongkan Semua Data Lama
            </button>
        </form>
    </div>

    <div class="cyber-import-container">
        <div class="text-center mb-4">
            <h4 class="fw-bold text-dark mb-1"><i class="fa-solid fa-file-csv text-primary me-2"></i>Smart Import Data Material (.CSV)</h4>
            <p class="text-muted small">Sistem mendeteksi struktur kolom secara otomatis. Mendukung file <b>Material Stok</b> & <b>Non-Stok</b>.</p>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <div class="upload-drag-zone mb-4">
                <i class="fa-solid fa-square-poll-horizontal fa-3x text-primary mb-3 opacity-75"></i>
                <h5 class="fw-bold text-dark mb-1">Pilih File CSV Anda</h5>
                <input type="file" name="file" class="form-control file-input-cyber" accept=".csv" required>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="../material/index.php" class="btn btn-outline-secondary px-4 py-2.5" style="border-radius:12px;">Kembali</a>
                <button type="submit" name="import" class="btn btn-action-submit">Proses & Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.querySelectorAll('.dropdown-btn').forEach(button => {
        button.addEventListener('click', function() {
            const container = this.nextElementSibling;
            container.style.display = container.style.display === "block" ? "none" : "block";
        });
    });

    document.getElementById('formReset').addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'Bersihkan Database?',
            text: "Seluruh riwayat data material di tabel saat ini akan dihapus total!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Kosongkan!',
            cancelButtonText: 'Batal'
        }).then((result) => { if (result.isConfirmed) { this.submit(); } });
    });

    <?php if($sukses_hapus): ?>
        Swal.fire({ title: 'Dibersihkan!', text: 'Database saat ini sudah bersih total dan siap menerima data baru.', icon: 'success', confirmButtonColor: '#0284c7' });
    <?php endif; ?>

    <?php if($sukses_import): ?>
        Swal.fire({
            title: 'Import Sukses Sempurna!',
            text: 'Sebanyak <?= $jumlah_import; ?> data material berhasil disimpan!',
            icon: 'success',
            confirmButtonColor: '#0284c7'
        }).then(() => { window.location = '../material/index.php'; });
    <?php endif; ?>

    <?php if($gagal_import): ?>
        Swal.fire({ title: 'Gagal Import!', text: 'Periksa kembali format file CSV Anda atau koneksi database.', icon: 'error', confirmButtonColor: '#d33' });
    <?php endif; ?>
</script>
</body>
</html>