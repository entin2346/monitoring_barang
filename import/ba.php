<?php
include "../config/koneksi.php";

if(isset($_POST['import'])){

    if($_FILES['file']['error'] == 0){

        $file = $_FILES['file']['tmp_name'];
        $handle = fopen($file, "r");

        // Lewati 2 baris awal
        fgetcsv($handle, 10000, ",");
        fgetcsv($handle, 10000, ",");

        while(($data = fgetcsv($handle, 10000, ",")) !== FALSE){

            // Lewati baris kosong
            if(count(array_filter($data)) == 0){
                continue;
            }

            // Lewati jika nama barang kosong
            if(trim($data[3] ?? '') == ''){
                continue;
            }

            $no_urut            = mysqli_real_escape_string($conn, trim($data[0] ?? ''));
            $jenis_berita_acara = mysqli_real_escape_string($conn, trim($data[1] ?? ''));

            // KONVERSI TANGGAL
            $tanggal_csv = trim($data[2] ?? '');
            $tanggal = NULL;

            if(!empty($tanggal_csv)){
                $date = DateTime::createFromFormat('d-M-y', $tanggal_csv);

                if($date){
                    $tanggal = $date->format('Y-m-d');
                }else{
                    $tanggal = 'NULL';
                }
            }else{
                $tanggal = 'NULL';
            }

            $nama_barang            = mysqli_real_escape_string($conn, trim($data[3] ?? ''));
            $merk_jenis             = mysqli_real_escape_string($conn, trim($data[4] ?? ''));
            $jenis_barang           = mysqli_real_escape_string($conn, trim($data[5] ?? ''));
            $sumber_barang          = mysqli_real_escape_string($conn, trim($data[6] ?? ''));
            $satuan                 = mysqli_real_escape_string($conn, trim($data[7] ?? ''));
            $jumlah                 = (int)($data[8] ?? 0);
            $tujuan                 = mysqli_real_escape_string($conn, trim($data[9] ?? ''));
            $kondisi_material       = mysqli_real_escape_string($conn, trim($data[10] ?? ''));
            $no_seri                = mysqli_real_escape_string($conn, trim($data[11] ?? ''));
            $asal_barang_vendor     = mysqli_real_escape_string($conn, trim($data[12] ?? ''));
            $berita_acara           = mysqli_real_escape_string($conn, trim($data[13] ?? ''));
            $dokumentasi_ba_kembali = mysqli_real_escape_string($conn, trim($data[14] ?? ''));
            $keterangan             = mysqli_real_escape_string($conn, trim($data[15] ?? ''));
            $keterangan_tambahan    = mysqli_real_escape_string($conn, trim($data[16] ?? ''));
            $tug5                   = mysqli_real_escape_string($conn, trim($data[17] ?? ''));

            $sql = "
            INSERT INTO database_ba (
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
            ) VALUES (
                '$no_urut',
                '$jenis_berita_acara',
                ".($tanggal == 'NULL' ? "NULL" : "'$tanggal'").",
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
            )";

            mysqli_query($conn, $sql);
        }

        fclose($handle);

        echo "
        <script>
            alert('Import Database BA Berhasil');
            window.location='ba.php';
        </script>
        ";

    } else {

        echo "
        <script>
            alert('Pilih file CSV terlebih dahulu!');
        </script>
        ";
    }
}
?>

<!DOCTYPE html>
<html lang='id'>
<head>
    <meta charset='UTF-8'>
    <title>Import Database BA</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
</head>
<body class='p-4'>

<div class='container'>

    <h2>Import Database BA</h2>

    <form method='POST' enctype='multipart/form-data' class='mt-4'>

        <div class='mb-3'>
            <label class='form-label'>Pilih File CSV</label>
            <input type='file'
                   name='file'
                   class='form-control'
                   accept='.csv'
                   required>
        </div>

        <button type='submit'
                name='import'
                class='btn btn-primary'>
            Import CSV
        </button>

        <a href='../dashboard/index.php'
           class='btn btn-secondary'>
            Kembali Dashboard
        </a>

    </form>

</div>

</body>
</html>