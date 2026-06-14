<?php
session_start();

if(!isset($_SESSION['login'])){
    header("Location: ../login/index.php");
    exit;
}

include "../config/koneksi.php";

if(isset($_POST['simpan'])){

    $jenis_berita_acara = mysqli_real_escape_string($conn,$_POST['jenis_berita_acara']);
    $tanggal = mysqli_real_escape_string($conn,$_POST['tanggal']);
    $nama_barang = mysqli_real_escape_string($conn,$_POST['nama_barang']);
    $merk_jenis = mysqli_real_escape_string($conn,$_POST['merk_jenis']);
    $jenis_barang = mysqli_real_escape_string($conn,$_POST['jenis_barang']);
    $sumber_barang = mysqli_real_escape_string($conn,$_POST['sumber_barang']);
    $satuan = mysqli_real_escape_string($conn,$_POST['satuan']);
    $jumlah = (int)$_POST['jumlah'];
    $no_seri = mysqli_real_escape_string($conn,$_POST['no_seri']);
    $asal_barang_vendor = mysqli_real_escape_string($conn,$_POST['asal_barang_vendor']);
    $kategori_material = mysqli_real_escape_string($conn,$_POST['kategori_material']);
    $tujuan = mysqli_real_escape_string($conn,$_POST['tujuan']);
    $kondisi_material = mysqli_real_escape_string($conn,$_POST['kondisi_material']);
    $keterangan = mysqli_real_escape_string($conn,$_POST['keterangan']);

    mysqli_query($conn,"
    INSERT INTO database_ba
    (
        jenis_berita_acara,
        tanggal,
        nama_barang,
        merk_jenis,
        jenis_barang,
        sumber_barang,
        satuan,
        jumlah,
        no_seri,
        asal_barang_vendor,
        kategori_material,
        tujuan,
        kondisi_material,
        keterangan
    )
    VALUES
    (
        '$jenis_berita_acara',
        '$tanggal',
        '$nama_barang',
        '$merk_jenis',
        '$jenis_barang',
        '$sumber_barang',
        '$satuan',
        '$jumlah',
        '$no_seri',
        '$asal_barang_vendor',
        '$kategori_material',
        '$tujuan',
        '$kondisi_material',
        '$keterangan'
    )
    ");

    header("Location:index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data BA | I-CALM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4 mb-5">

    <div class="alert alert-primary shadow-sm mb-4">
        <h3 class="mb-1">📄 Tambah Data Berita Acara</h3>
        <small>I-CALM (Integrated Control and Logistics Monitoring)</small>
    </div>

    <div class="card shadow">
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Jenis BA</label>
                        <select name="jenis_berita_acara" class="form-control" required>
                            <option value="MASUK">MASUK</option>
                            <option value="KELUAR">KELUAR</option>
                            <option value="PENGEMBALIAN">PENGEMBALIAN</option>
                            <option value="RETURN">RETURN</option>
                            <option value="PERBAIKAN">PERBAIKAN</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Nama Barang</label>
                        <input type="text" name="nama_barang" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Merk / Jenis</label>
                        <input type="text" name="merk_jenis" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Jenis Barang</label>
                        <input type="text" name="jenis_barang" class="form-control">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Sumber Barang</label>
                        <input type="text" name="sumber_barang" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Satuan</label>
                        <input type="text" name="satuan" class="form-control">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label>Jumlah</label>
                        <input type="number" name="jumlah" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Nomor Seri</label>
                        <input type="text" name="no_seri" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Pemasok / Asal Material</label>
                        <input type="text" name="asal_barang_vendor" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Kategori Material</label>
                        <select name="kategori_material" class="form-control">
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Material Gardu">Material Gardu</option>
                            <option value="Material Proteksi">Material Proteksi</option>
                            <option value="Material Kabel">Material Kabel</option>
                            <option value="Material Trafo">Material Trafo</option>
                            <option value="Alat Kerja">Alat Kerja</option>
                            <option value="Alat Uji">Alat Uji</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Tujuan</label>
                        <input type="text" name="tujuan" class="form-control" placeholder="Contoh: ULTG Makassar">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label>Kondisi Material</label>
                        <select name="kondisi_material" class="form-control">
                            <option value="BAIK">BAIK</option>
                            <option value="RUSAK">RUSAK</option>
                            <option value="PERBAIKAN">PERBAIKAN</option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label>Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3"></textarea>
                    </div>
                </div>

                <button type="submit" name="simpan" class="btn btn-success">Simpan</button>
                <a href="index.php" class="btn btn-secondary">Kembali</a>
            </form>
        </div>
    </div>

</div>

</body>
</html>