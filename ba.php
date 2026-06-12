<?php

include "../config/koneksi.php";

if(isset($_POST['import'])){

    $file = $_FILES['file']['tmp_name'];

    $handle = fopen($file, "r");

    // Lewati baris judul
    fgetcsv($handle, 10000, ",");

    // Lewati header
    fgetcsv($handle, 10000, ",");

    while(($data = fgetcsv($handle, 10000, ",")) !== FALSE){

        if(empty($data[0])){
            continue;
        }

        $no_urut = mysqli_real_escape_string($conn,$data[0]);
        $jenis_berita_acara = mysqli_real_escape_string($conn,$data[1]);
        $tanggal = mysqli_real_escape_string($conn,$data[2]);
        $nama_barang = mysqli_real_escape_string($conn,$data[3]);
        $merk_jenis = mysqli_real_escape_string($conn,$data[4]);
        $jenis_barang = mysqli_real_escape_string($conn,$data[5]);
        $sumber_barang = mysqli_real_escape_string($conn,$data[6]);
        $satuan = mysqli_real_escape_string($conn,$data[7]);
        $jumlah = mysqli_real_escape_string($conn,$data[8]);
        $tujuan = mysqli_real_escape_string($conn,$data[9]);
        $kondisi_material = mysqli_real_escape_string($conn,$data[10]);
        $no_seri = mysqli_real_escape_string($conn,$data[11]);
        $asal_barang_vendor = mysqli_real_escape_string($conn,$data[12]);
        $berita_acara = mysqli_real_escape_string($conn,$data[13]);
        $dokumentasi_ba_kembali = mysqli_real_escape_string($conn,$data[14]);
        $keterangan = mysqli_real_escape_string($conn,$data[15]);
        $keterangan_tambahan = mysqli_real_escape_string($conn,$data[16]);
        $tug5 = mysqli_real_escape_string($conn,$data[17]);

        mysqli_query($conn,"
        INSERT INTO database_ba
        (
            no_urut,
            jenis_berita_acara,
            tanggal,
            nama_barang,
            merk_jenis,
            jenis_barang,
            sumber_barang,
            satuan,
            jumlah,
            tujuan,
            kondisi_material,
            no_seri,
            asal_barang_vendor,
            berita_acara,
            dokumentasi_ba_kembali,
            keterangan,
            keterangan_tambahan,
            tug5
        )
        VALUES
        (
            '$no_urut',
            '$jenis_berita_acara',
            '$tanggal',
            '$nama_barang',
            '$merk_jenis',
            '$jenis_barang',
            '$sumber_barang',
            '$satuan',
            '$jumlah',
            '$tujuan',
            '$kondisi_material',
            '$no_seri',
            '$asal_barang_vendor',
            '$berita_acara',
            '$dokumentasi_ba_kembali',
            '$keterangan',
            '$keterangan_tambahan',
            '$tug5'
        )
        ");
    }

    fclose($handle);

    echo "<h3>Import Database BA Berhasil</h3>";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Import Database BA</title>
</head>
<body>

<h2>Import Database BA</h2>

<form method="POST" enctype="multipart/form-data">

<input type="file" name="file" required>

<button type="submit" name="import">
Import CSV
</button>

</form>

</body>
</html>