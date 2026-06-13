<?php

include "../config/koneksi.php";

$jumlah_import = 0;

if(isset($_POST['import'])){

    if($_FILES['file']['error'] == 0){

        $file = $_FILES['file']['tmp_name'];

        $handle = fopen($file, "r");

        // Lewati judul
        fgetcsv($handle, 10000, ",");

        // Lewati header
        fgetcsv($handle, 10000, ",");

        while(($data = fgetcsv($handle, 10000, ",")) !== FALSE){

            // Lewati baris kosong
            if(count(array_filter($data)) == 0){
                continue;
            }

            $nama_material = trim($data[1] ?? '');

            if($nama_material == ''){
                continue;
            }

            $nama_material = mysqli_real_escape_string($conn,$nama_material);
            $satuan        = mysqli_real_escape_string($conn,trim($data[2] ?? ''));
            $jumlah        = (int)($data[3] ?? 0);
            $no_rak        = mysqli_real_escape_string($conn,trim($data[4] ?? ''));
            $kondisi       = mysqli_real_escape_string($conn,trim($data[5] ?? ''));
            $lokasi        = mysqli_real_escape_string($conn,trim($data[6] ?? ''));
            $keterangan    = mysqli_real_escape_string($conn,trim($data[7] ?? ''));

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

            $jumlah_import++;
        }

        fclose($handle);

        echo "
        <script>
        alert('Import berhasil. Total data: $jumlah_import');
        window.location='material.php';
        </script>
        ";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Import Material Gudang</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<style>

body{
    background:#f4f6f9;
}

.import-card{
    max-width:850px;
    margin:auto;
    border:none;
    border-radius:20px;
    overflow:hidden;
}

.header-import{
    background:linear-gradient(135deg,#ffc107,#ff9800);
    color:#000;
    text-align:center;
    padding:30px;
}

.upload-box{
    border:2px dashed #ffc107;
    border-radius:15px;
    padding:40px;
    text-align:center;
    background:#fffdf5;
}

.upload-box:hover{
    background:#fff8e1;
}

</style>

</head>

<body>

<div class="container py-5">

<div class="card shadow-lg import-card">

<div class="header-import">

<h2>
<i class="fa-solid fa-boxes-stacked"></i>
Import Material Gudang
</h2>

<p class="mb-0">
Monitoring Material UPT Makassar
</p>

</div>

<div class="card-body p-4">

<?php

$total = mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM material_gudang
")
);

?>

<div class="row mb-4">

<div class="col-md-6">

<div class="card bg-primary text-white">

<div class="card-body text-center">

<h6>Total Material Saat Ini</h6>

<h2>
<?= number_format($total['total']); ?>
</h2>

</div>

</div>

</div>

<div class="col-md-6">

<div class="card bg-success text-white">

<div class="card-body text-center">

<h6>Status Gudang</h6>

<h2>AKTIF</h2>

</div>

</div>

</div>

</div>

<div class="alert alert-warning">

<b>Petunjuk Import :</b>

<ul class="mb-0 mt-2">
<li>File harus format CSV</li>
<li>Kolom tidak boleh diubah</li>
<li>Import hanya data material gudang</li>
<li>Data akan langsung masuk ke database</li>
</ul>

</div>

<form method="POST" enctype="multipart/form-data">

<div class="upload-box mb-4">

<i class="fa-solid fa-cloud-arrow-up fa-4x text-warning mb-3"></i>

<h5>Pilih File Material CSV</h5>

<p class="text-muted">
Upload daftar material gudang
</p>

<input
type="file"
name="file"
class="form-control"
accept=".csv"
required>

</div>

<div class="text-center">

<button
type="submit"
name="import"
class="btn btn-warning btn-lg">

<i class="fa-solid fa-upload"></i>
Import Material

</button>

<a
href="../dashboard/index.php"
class="btn btn-secondary btn-lg">

<i class="fa-solid fa-house"></i>
Dashboard

</a>

</div>

</form>

</div>

</div>

</div>

</body>
</html>