<?php
session_start();

// Pastikan user sudah login
if(!isset($_SESSION['login'])){
    exit;
}

include "../config/koneksi.php";

// Ambil keyword dari JavaScript fetch
$keyword = $_GET['keyword'] ?? '';
$keyword = mysqli_real_escape_string($conn, $keyword);

if ($keyword !== '') {
    // Sesuai request Anda: Menggunakan '$keyword%' agar HANYA mencari yang BERAWALAN huruf tersebut
    $query = mysqli_query($conn, "
        SELECT DISTINCT nama_material 
        FROM material_gudang 
        WHERE nama_material LIKE '$keyword%' 
        AND nama_material <> ''
        ORDER BY nama_material ASC 
        LIMIT 10
    ");

    if (mysqli_num_rows($query) > 0) {
        while ($row = mysqli_fetch_assoc($query)) {
            $nama = $row['nama_material'];
            // Output berupa elemen HTML yang akan ditangkap oleh JavaScript
            // Menambahkan addslashes agar nama material yang punya tanda petik tidak merusak JavaScript
            echo "<div class='autocomplete-item' onclick=\"pilihMaterial('" . urlencode($nama) . "')\">" . htmlspecialchars($nama) . "</div>";
        }
    } else {
        // Jika tidak ada data yang berawalan huruf tersebut
        echo "<div class='autocomplete-item' style='color:#94a3b8; cursor:default;'>Material tidak ditemukan...</div>";
    }
}
?>