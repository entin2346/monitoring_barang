<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['login'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Session habis."
    ]);
    exit;
}

include "../config/koneksi.php";

if (!isset($_GET['id']) || !isset($_GET['file'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Parameter tidak lengkap."
    ]);
    exit;
}

$id = (int) $_GET['id'];
$file = urldecode($_GET['file']);

// Ambil data file lama
$stmt = $conn->prepare("SELECT file_ba FROM database_ba WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

if (!$result) {
    echo json_encode([
        "status" => "error",
        "message" => "Data tidak ditemukan."
    ]);
    exit;
}

$files = [];

if (!empty($result['file_ba'])) {

    $decode = json_decode($result['file_ba'], true);

    if (is_array($decode)) {
        $files = $decode;
    } else {
        $files = [$result['file_ba']];
    }
}

// Cari file
$key = array_search($file, $files);

if ($key === false) {
    echo json_encode([
        "status" => "error",
        "message" => "File tidak ditemukan."
    ]);
    exit;
}

// Hapus dari array
unset($files[$key]);
$files = array_values($files);

// Update database
$json = json_encode($files);

$stmt2 = $conn->prepare("UPDATE database_ba SET file_ba=? WHERE id=?");
$stmt2->bind_param("si", $json, $id);

if (!$stmt2->execute()) {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal update database."
    ]);
    exit;
}

// Hapus file fisik
$path = "../uploads/" . $file;

if (file_exists($path)) {
    unlink($path);
}

echo json_encode([
    "status" => "success"
]);