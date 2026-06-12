<?php

include "../config/koneksi.php";

if(isset($_POST['import'])){

    $file = $_FILES['file']['tmp_name'];

    $handle = fopen($file, "r");

    // Lewati baris judul
    fgetcsv($handle, 1000, ",");

    // Lewati header kolom
    fgetcsv($handle, 1000, ",");

    while(($data = fgetcsv($handle, 10000, ",")) !== FALSE){

        $nama_material = mysqli_real_escape_string($conn,$data[1]);
        $satuan = mysqli_real_escape_string($conn,$data[2]);
        $jumlah = (int)$data[3];
        $no_rak = mysqli_real_escape_string($conn,$data[4]);
        $kondisi = mysqli_real_escape_string($conn,$data[5]);
        $lokasi = mysqli_real_escape_string($conn,$data[6]);
        $keterangan = mysqli_real_escape_string($conn,$data[7]);

        if(empty($nama_material)){
            continue;
        }

        mysqli_query($conn,"
        INSERT INTO material_gudang
        (
            nama_material,
            satuan,
            jumlah,
            no_rak,
            kondisi,
            lokasi_penyimpanan,
            keterangan
        )
        VALUES
        (
            '$nama_material',
            '$satuan',
            '$jumlah',
            '$no_rak',
            '$kondisi',
            '$lokasi',
            '$keterangan'
        )
        ");
    }

    fclose($handle);

    echo "Import berhasil!";
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Import Material</title>
</head>
<body>

<h2>Import Material Gudang</h2>

<form method="POST" enctype="multipart/form-data">

<input type="file" name="file" required>

<button type="submit" name="import">
Import CSV
</button>

</form>

</body>
</html>