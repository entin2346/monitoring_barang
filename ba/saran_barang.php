<?php
session_start();

// Validasi session agar aman dari akses luar sistem
if(!isset($_SESSION['login'])){
    header('HTTP/1.0 403 Forbidden');
    echo json_encode(["error" => "Akses ditolak"]);
    exit;
}

// Hubungkan ke database
include "../config/koneksi.php";

// Set header response agar dibaca sebagai JSON oleh JavaScript browser
header('Content-Type: application/json; charset=utf-8');

// Menangkap parameter 'keyword' dari objek FormData (POST)
$keyword = $_POST['keyword'] ?? '';

// Bersihkan spasi liar di ujung teks
$keyword_clean = trim($keyword);

// Inisialisasi array penampung hasil akhir
$response_data = [];

if ($keyword_clean !== '') {
    // PERBAIKAN: Menggunakan % di awal dan akhir agar pencarian fleksibel (Sama dengan ba/index.php)
    $param_cari = '%' . $keyword_clean . '%';

    // PERBAIKAN: Menggunakan Prepared Statements untuk keamanan maksimal dan menggunakan DISTINCT
    $stmt = $conn->prepare("SELECT DISTINCT nama_barang 
                            FROM database_ba 
                            WHERE nama_barang IS NOT NULL 
                              AND nama_barang <> '' 
                              AND nama_barang LIKE ? 
                            ORDER BY nama_barang ASC 
                            LIMIT 10");
    
    if ($stmt) {
        $stmt->bind_param("s", $param_cari);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $response_data[] = $row['nama_barang'];
        }
        
        $stmt->close();
    }
}

// Mengembalikan data dalam bentuk format JSON murni Array linear: ["Nama Barang A", "Nama Barang B"]
echo json_encode($response_data);