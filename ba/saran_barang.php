<?php
include "../config/koneksi.php";

// Ambil data keyword yang dikirim oleh AJAX POST
$keyword = isset($_POST['keyword']) ? trim($_POST['keyword']) : '';
$keyword = mysqli_real_escape_string($conn, $keyword);

$data = [];

// Proteksi: Jika kotak pencarian kosong, langsung stop (jangan jalankan query)
if ($keyword !== '') {
    
    /* PERUBAHAN UTAMA:
       Menggunakan '$keyword%' (TANPA tanda % di depan variabel).
       Ini memaksa SQL hanya mencari data yang diawali oleh karakter tersebut.
    */
    $query = mysqli_query($conn, "SELECT DISTINCT nama_barang FROM database_ba 
                                  WHERE LOWER(nama_barang) LIKE LOWER('$keyword%') 
                                  AND nama_barang IS NOT NULL 
                                  AND nama_barang <> '' 
                                  ORDER BY nama_barang ASC 
                                  LIMIT 10");

    if ($query) {
        while ($row = mysqli_fetch_assoc($query)) {
            $data[] = $row['nama_barang'];
        }
    }
}

// Kembalikan data dalam bentuk JSON murni ke JavaScript
header('Content-Type: application/json');
echo json_encode($data);
exit;