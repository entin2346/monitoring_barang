<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

$error_message = "";

if(isset($_POST['simpan'])){

    // Ambil data dan bersihkan dari SQL Injection / Spasi tak sengaja
    $jenis_berita_acara = isset($_POST['jenis_berita_acara']) ? strtoupper(trim(mysqli_real_escape_string($conn, $_POST['jenis_berita_acara']))) : "";
    $tanggal            = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $jenis_kategori     = mysqli_real_escape_string($conn, $_POST['jenis_kategori']); 
    $kategori_material  = mysqli_real_escape_string($conn, $_POST['kategori_material']);
    $nama_barang        = trim(mysqli_real_escape_string($conn, $_POST['nama_barang'])); 
    $merk_jenis         = mysqli_real_escape_string($conn, $_POST['merk_jenis']);
    $jenis_barang       = mysqli_real_escape_string($conn, $_POST['jenis_barang']);
    $sumber_barang      = mysqli_real_escape_string($conn, $_POST['sumber_barang']);
    $satuan             = mysqli_real_escape_string($conn, $_POST['satuan']);
    $jumlah             = (int)$_POST['jumlah'];
    $no_seri            = mysqli_real_escape_string($conn, $_POST['no_seri']);
    $asal_barang_vendor = mysqli_real_escape_string($conn, $_POST['asal_barang_vendor']);
    $tujuan             = mysqli_real_escape_string($conn, $_POST['tujuan']);
    $kondisi_material   = mysqli_real_escape_string($conn, $_POST['kondisi_material']);
    $keterangan         = mysqli_real_escape_string($conn, $_POST['keterangan']);

    // -----------------------------------------------------------------
    // PROSES VALIDASI & HITUNG ULANG TOTAL (TRANSACTION SAFETY)
    // -----------------------------------------------------------------
    
    // Mulai database transaction untuk menjamin keutuhan data
    $conn->begin_transaction();

    try {
        // VALIDASI 1: Pastikan Jenis BA dipilih dengan benar
        if (empty($jenis_berita_acara) || $jenis_berita_acara == "") {
            throw new Exception("Silakan pilih Jenis Berita Acara (BA) yang valid terlebih dahulu!");
        }

        // VALIDASI 2: Pastikan nama barang tidak kosong
        if (empty($nama_barang)) {
            throw new Exception("Nama Barang / Deskripsi Teknis wajib diisi!");
        }

        // Cek data awal di gudang dengan validasi case-insensitive dan trim space di database
        $cek_gudang = mysqli_query($conn, "SELECT jumlah FROM material_gudang WHERE LOWER(TRIM(nama_material)) = LOWER('$nama_barang')");
        $data_gudang = mysqli_fetch_assoc($cek_gudang);

        // NOTE: VALIDASI PEMBATASAN STOK DI GUDANG SEKARANG DIHAPUS/DINONAKTIFKAN
        // Anda sekarang bisa langsung menginput barang KELUAR kapan saja tanpa eror ditolak lagi.

        // STEP 1: Masukkan data log Berita Acara Baru ke database_ba
        $insert_ba = mysqli_query($conn, "
            INSERT INTO database_ba
            (jenis_berita_acara, tanggal, jenis_kategori, kategori_material, nama_barang, merk_jenis, jenis_barang, sumber_barang, satuan, jumlah, no_seri, asal_barang_vendor, tujuan, kondisi_material, keterangan)
            VALUES
            ('$jenis_berita_acara', '$tanggal', trim('$jenis_kategori'), '$kategori_material', '$nama_barang', '$merk_jenis', '$jenis_barang', '$sumber_barang', '$satuan', '$jumlah', '$no_seri', '$asal_barang_vendor', '$tujuan', '$kondisi_material', '$keterangan')
        ");

        if (!$insert_ba) {
            throw new Exception("Gagal menyimpan log Berita Acara: " . mysqli_error($conn));
        }

        // STEP 2: RE-CALCULATION ENGINE (Hitung ulang mutasi dari awal record secara bersih)
        // Jumlahkan semua data MASUK dan PENGEMBALIAN
        $query_in = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM database_ba WHERE LOWER(TRIM(nama_barang)) = LOWER('$nama_barang') AND (UPPER(TRIM(jenis_berita_acara)) = 'MASUK' OR UPPER(TRIM(jenis_berita_acara)) = 'PENGEMBALIAN')");
        $res_in = mysqli_fetch_assoc($query_in);
        $total_masuk = (int)($res_in['total'] ?? 0);

        // Jumlahkan semua data KELUAR, RETURN, dan PERBAIKAN
        $query_out = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM database_ba WHERE LOWER(TRIM(nama_barang)) = LOWER('$nama_barang') AND UPPER(TRIM(jenis_berita_acara)) IN ('KELUAR', 'RETURN', 'PERBAIKAN')");
        $res_out = mysqli_fetch_assoc($query_out);
        $total_keluar = (int)($res_out['total'] ?? 0);

        // Rumus stok mutlak (Bisa menghasilkan nilai minus jika barang langsung keluar)
        $stok_akhir_nyata = $total_masuk - $total_keluar;

        // STEP 3: Daftarkan baru atau Update data di material_gudang
        if ($data_gudang) {
            $update_gudang = mysqli_query($conn, "UPDATE material_gudang SET jumlah = '$stok_akhir_nyata' WHERE LOWER(TRIM(nama_material)) = LOWER('$nama_barang')");
            if (!$update_gudang) {
                throw new Exception("Gagal memperbarui kalkulasi stok gudang: " . mysqli_error($conn));
            }
        } else {
            // Jika langsung melakukan pengeluaran tanpa barang masuk sebelumnya, daftarkan otomatis ke master gudang
            $insert_gudang = mysqli_query($conn, "
                INSERT INTO material_gudang 
                (nama_material, jumlah, jenis_kategori, kondisi, keterangan) 
                VALUES 
                ('$nama_barang', '$stok_akhir_nyata', trim('$jenis_kategori'), '$kondisi_material', 'Otomatis dari Registrasi BA (Langsung Keluar)')
            ");
            if (!$insert_gudang) {
                throw new Exception("Gagal mendaftarkan item baru ke gudang: " . mysqli_error($conn));
            }
        }

        // Jika seluruh proses sukses tanpa exception, eksekusi commit data permanen
        $conn->commit();
        
        header("Location: index.php");
        exit;

    } catch (Exception $e) {
        // Gagalkan seluruh perubahan jika di tengah jalan ada kegagalan query/validasi
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}

// Ambil daftar barang untuk Autocomplete Dropdown
$daftar_material_gudang = mysqli_query($conn, "SELECT nama_material FROM material_gudang ORDER BY nama_material ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Data BA | I-CALM</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* DISESUAIKAN DENGAN TEMA WARNA CERAH (DETAIL.PHP) TANPA MENGUBAH STRUKTUR */
        :root {
            --bg-base: #f4f7fc;            
            --bg-card: #ffffff; 
            --primary-brand: #0284c7;       
            --text-main: #0f172a;           
            --text-muted: #64748b;          
            --border-glass: rgba(148, 163, 184, 0.15);
            --bg-sidebar: #d0e1f9; /* Variabel disamakan sepenuhnya dengan detail.php */
        }

        * {
            margin: 0; padding: 0; box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body { 
            background: var(--bg-base);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* SIDEBAR SINKRON 100% DENGAN DETAIL.PHP */
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

        .content { margin-left: 260px; background: transparent; }
        
        .navbar-custom { 
            background: rgba(255, 255, 255, 0.5); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            padding: 18px 32px; border-bottom: 1px solid var(--border-glass); position: sticky; top: 0; z-index: 999;
        }

        .main-body-wrapper { padding: 35px 32px; }

        /* Desain Card & Input Box Minimalis Tipis Berdasarkan Foto */
        .glass-form-card {
            background: var(--bg-card); border: 1px solid var(--border-glass);
            border-radius: 16px; padding: 35px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }

        .form-label { font-weight: 700; color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 8px; }
        
        .form-control, .form-select {
            background: #ffffff !important; border: 1px solid rgba(148, 163, 184, 0.4) !important;
            border-radius: 8px; padding: 10px 14px; font-size: 0.95rem; font-weight: 500; color: var(--text-main); transition: all 0.3s;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-brand) !important; box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.1) !important;
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
    
    <button class="dropdown-btn active">
        <span class="menu-content-wrapper">
            <i class="fa-solid fa-layer-group menu-icon"></i>
            <span>Monitoring</span>
        </span>
        <i class="fa-solid fa-chevron-down dropdown-chevron"></i>
    </button>
    <div class="dropdown-container" style="display: block;">
        <a href="../material/index.php">Material Gudang</a>
        <a href="../ba/index.php" class="active-menu">Database BA</a>
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
        <a href="../kategori/pemakaian.php">Pemakaian</a>
        <a href="../kategori/peminjaman.php">Peminjaman</a>
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
            <span class="navbar-brand mb-0 h1 d-flex align-items-center" style="font-weight: 800; font-size: 1.3rem;">
                <i class="fa-solid fa-folder-plus text-primary me-2"></i> KENDALI LOGISTIK 
                <span class="ms-2" style="font-weight: 400; font-size: 0.95rem; color: var(--text-muted);">/ Registrasi Berita Acara Baru</span>
            </span>
        </div>
    </nav>

    <div class="main-body-wrapper">
        <div class="glass-form-card">
            <h4 class="fw-extrabold mb-4" style="letter-spacing: -0.5px; font-weight: 800;"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Form Entry Data Berita Acara</h4>
            <hr class="mb-4 opacity-10">

            <?php if(!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert" style="border-radius: 12px;">
                    <strong><i class="fa-solid fa-triangle-exclamation me-2"></i>Transaksi Gagal/Dibatalkan!</strong><br>
                    <p class="mt-2 mb-2 bg-dark text-warning p-2 rounded small"><code><?= htmlspecialchars($error_message); ?></code></p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="row g-4">
                    
                    <div class="col-md-4">
                        <label class="form-label">Jenis Berita Acara (BA)</label>
                        <select name="jenis_berita_acara" class="form-select" required>
                            <option value="" disabled selected>-- Pilih Jenis BA --</option>
                            <option value="MASUK">MASUK</option>
                            <option value="KELUAR">KELUAR</option>
                            <option value="PENGEMBALIAN">PENGEMBALIAN</option>
                            <option value="RETURN">RETURN</option>
                            <option value="PERBAIKAN">PERBAIKAN</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tanggal Record</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Kategori Kelompok</label>
                        <select name="jenis_kategori" class="form-select" required>
                            <option value="STOCK">STOCK</option>
                            <option value="NON STOCK">NON STOCK</option>
                            <option value="NON PO">NON PO</option>
                            <option value="EX BONGKARAN">EX BONGKARAN</option>
                            <option value="PRE MEMORY">PRE MEMORY</option>
                            <option value="PEMINJAMAN">PEMINJAMAN</option>
                            <option value="PEMAKAIAN">PEMAKAIAN</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Kategori Material</label>
                        <select name="kategori_material" class="form-select">
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Material Gardu Induk">Material Gardu Induk</option>
                            <option value="Material Transmisi">Material Transmisi</option>
                            <option value="Material Proteksi">Material Proteksi</option>
                            <option value="Material Kabel">Material Kabel</option>
                            <option value="Material Trafo">Material Trafo</option>
                            <option value="Alat Kerja">Alat Kerja</option>
                            <option value="Alat Uji">Alat Uji</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label class="form-label">Nama Barang / Deskripsi Teknis</label>
                        <input type="text" name="nama_barang" list="data_barang_gudang" class="form-control" placeholder="Ketik atau pilih nama barang..." required autocomplete="off">
                        <datalist id="data_barang_gudang">
                            <?php while($row = mysqli_fetch_assoc($daftar_material_gudang)) : ?>
                                <option value="<?= htmlspecialchars($row['nama_material']); ?>"></option>
                            <?php endwhile; ?>
                        </datalist>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Merk / Spesifikasi</label>
                        <input type="text" name="merk_jenis" class="form-control" placeholder="Contoh: Schneider / Siemens">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Jenis Barang</label>
                        <input type="text" name="jenis_barang" class="form-control" placeholder="Contoh: Pasif / Mekanik">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Sumber Barang</label>
                        <input type="text" name="sumber_barang" class="form-control" placeholder="Contoh: Pengadaan Investasi">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Satuan</label>
                        <input type="text" name="satuan" class="form-control" placeholder="Contoh: Unit, Pcs, Meter">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Jumlah / Volume</label>
                        <input type="number" name="jumlah" class="form-control" placeholder="0" min="1" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Nomor Seri (S/N)</label>
                        <input type="text" name="no_seri" class="form-control" placeholder="Tulis nomor seri pabrikan jika ada">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Pemasok / Asal Material</label>
                        <input type="text" name="asal_barang_vendor" class="form-control" placeholder="Contoh: PT. PLN Tarakan">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tujuan Distribusi</label>
                        <input type="text" name="tujuan" class="form-control" placeholder="Contoh: ULTG Makassar">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Kondisi Fisik Material</label>
                        <select name="kondisi_material" class="form-select">
                            <option value="BAIK">BAIK</option>
                            <option value="RUSAK">RUSAK</option>
                            <option value="PERBAIKAN">PERBAIKAN</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Keterangan Tambahan</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Tuliskan detail info lapangan, lokasi penyimpanan sekunder, atau nomor nota dinas terkait..."></textarea>
                    </div>
                </div>

                <div class="mt-4 pt-2 d-flex gap-2">
                    <button type="submit" name="simpan" class="btn btn-primary fw-bold px-4 py-2" style="border-radius: 12px; background: linear-gradient(135deg, #0284c7, #2563eb); border: none; box-shadow: 0 4px 15px rgba(37, 99, 235, 0.25);">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Data BA
                    </button>
                    <a href="index.php" class="btn btn-light fw-bold px-4 py-2" style="border-radius: 12px; border: 1px solid rgba(148, 163, 184, 0.25); color: var(--text-muted);">
                        Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    /* JavaScript Toggle Menu diselaraskan penuh dengan detail.php */
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