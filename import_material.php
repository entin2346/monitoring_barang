<?php
include "../config/koneksi.php";

if(isset($_POST["import"])){

    $file = $_FILES['file']['tmp_name'];

    $handle = fopen($file, "r");

    fgetcsv($handle, 1000, ",");

    while(($data = fgetcsv($handle, 1000, ",")) !== FALSE){

        $nama_material = mysqli_real_escape_string($conn,$data[0]);
        $satuan = mysqli_real_escape_string($conn,$data[1]);
        $jumlah = (int)$data[2];

        mysqli_query($conn,"
        INSERT INTO material_gudang
        (nama_material,satuan,jumlah)
        VALUES
        ('$nama_material','$satuan','$jumlah')
        ");
    }

    fclose($handle);

    echo "Import berhasil";
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file" required>
    <button type="submit" name="import">
        Import CSV
    </button>
</form>