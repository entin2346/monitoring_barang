<?php

include "../config/koneksi.php";

$id = (int)$_GET['id'];

$data = mysqli_fetch_assoc(
mysqli_query($conn,"
SELECT *
FROM material_gudang
WHERE id='$id'
")
);

if(!empty($data['foto_material'])){

    @unlink(
        "upload/".$data['foto_material']
    );
}

mysqli_query($conn,"
DELETE FROM material_gudang
WHERE id='$id'
");

header("Location:index.php");