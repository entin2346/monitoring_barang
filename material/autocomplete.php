<?php
session_start();

// Proteksi session agar aman
if(!isset($_SESSION['login'])){
    exit;
}

include "../config/koneksi.php";

// Mengambil data dari parameter URL (GET)
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$keyword_db = mysqli_real_escape_string($conn, $keyword);

// JIKA USER BELUM MENGETIK APA-APA, JANGAN KIRIMKAN LIST DATA APAPUN
if ($keyword_db === '') {
    exit;
}

// CARI DATA BERDASARKAN HURUF YANG DIKETIKKAN DI TABEL UTAMA MATERIAL_GUDANG
$query = mysqli_query($conn, "
    SELECT nama_material 
    FROM material_gudang 
    WHERE nama_material LIKE '%$keyword_db%' 
    AND nama_material <> ''
    GROUP BY nama_material 
    ORDER BY nama_material ASC 
    LIMIT 10
");

// Menghasilkan output berupa elemen HTML item list autocomplete
if (mysqli_num_rows($query) > 0) {
    while ($d = mysqli_fetch_assoc($query)) {
        $nama_material = $d['nama_material'];
        $nama_aman = urlencode($nama_material);
        echo "<div class='autocomplete-item' onclick='pilihMaterial(\"$nama_aman\")'>" . htmlspecialchars($nama_material, ENT_QUOTES, 'UTF-8') . "</div>";
    }
} else {
    // Menampilkan notice tipis jika pencarian memang tidak ada di database gudang
    echo "<div class='autocomplete-item text-muted' style='pointer-events: none; padding: 12px 16px;'>Tidak ada kecocokan material</div>";
}
?>