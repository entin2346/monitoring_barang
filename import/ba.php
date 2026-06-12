<?php
include "../config/koneksi.php";

if(isset($_POST['import'])){

    $file = $_FILES['file']['tmp_name'];
    $handle = fopen($file, "r");

    // Lewati baris judul dan header
    fgetcsv($handle, 10000, ",");
    fgetcsv($handle, 10000, ",");

    while(($data = fgetcsv($handle, 10000, ",")) !== FALSE){

        // Menggunakan array_filter untuk memastikan baris tidak kosong sepenuhnya
        if(count(array_filter($data)) == 0){
            continue;
        }

        // Menggunakan fungsi mysqli_real_escape_string untuk keamanan
        $no_urut = mysqli_real_escape_string($conn, $data[0] ?? '');
        $jenis_berita_acara = mysqli_real_escape_string($conn, $data[1] ?? '');
        $tanggal = mysqli_real_escape_string($conn, $data[2] ?? '');
        $nama_barang = mysqli_real_escape_string($conn, $data[3] ?? '');
        $merk_jenis = mysqli_real_escape_string($conn, $data[4] ?? '');
        $jenis_barang = mysqli_real_escape_string($conn, $data[5] ?? '');
        $sumber_barang = mysqli_real_escape_string($conn, $data[6] ?? '');
        $satuan = mysqli_real_escape_string($conn, $data[7] ?? '');
        $jumlah = mysqli_real_escape_string($conn, $data[8] ?? '');
        $tujuan = mysqli_real_escape_string($conn, $data[9] ?? '');
        $kondisi_material = mysqli_real_escape_string($conn, $data[10] ?? '');
        $no_seri = mysqli_real_escape_string($conn, $data[11] ?? '');
        $asal_barang_vendor = mysqli_real_escape_string($conn, $data[12] ?? '');
        $berita_acara = mysqli_real_escape_string($conn, $data[13] ?? '');
        $dokumentasi_ba_kembali = mysqli_real_escape_string($conn, $data[14] ?? '');
        $keterangan = mysqli_real_escape_string($conn, $data[15] ?? '');
        $keterangan_tambahan = mysqli_real_escape_string($conn, $data[16] ?? '');
        $tug5 = mysqli_real_escape_string($conn, $data[17] ?? '');

        mysqli_query($conn, "INSERT INTO database_ba (
            no_urut, jenis_berita_acara, tanggal, nama_barang, merk_jenis, 
            jenis_barang, sumber_barang, satuan, jumlah, tujuan, 
            kondisi_material, no_seri, asal_barang_vendor, berita_acara, 
            dokumentasi_ba_kembali, keterangan, keterangan_tambahan, tug5
        ) VALUES (
            '$no_urut', '$jenis_berita_acara', '$tanggal', '$nama_barang', '$merk_jenis', 
            '$jenis_barang', '$sumber_barang', '$satuan', '$jumlah', '$tujuan', 
            '$kondisi_material', '$no_seri', '$asal_barang_vendor', '$berita_acara', 
            '$dokumentasi_ba_kembali', '$keterangan', '$keterangan_tambahan', '$tug5'
        )");
    }

    fclose($handle);
    echo "<script>alert('Import Database BA Berhasil'); window.location='ba.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Import Database BA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">

<h2>Import Database BA</h2>
<form method="POST" enctype="multipart/form-data" class="mt-3">
    <div class="mb-3">
        <input type="file" name="file" class="form-control" style="width: 300px;" required>
    </div>
    <button type="submit" name="import" class="btn btn-primary">Import CSV</button>
</form>

</body>
</html>