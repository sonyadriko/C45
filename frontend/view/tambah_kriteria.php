<?php
include '../database/config.php';

$nama = $_POST['nama_kriteria'];
$nilai_string = $_POST['nilai_kriteria']; // contoh: "Mahal, Murah, Sedang"
$nilai_array = array_map('trim', explode(',', $nilai_string));

// 1. Insert kriteria
mysqli_query($databaseConnection, "INSERT INTO kriteria (nama_kriteria) VALUES ('$nama')");
$id_kriteria = mysqli_insert_id($databaseConnection);

// 2. Insert nilai-nilai kriteria
foreach ($nilai_array as $nilai) {
    mysqli_query($databaseConnection, "INSERT INTO nilai_kriteria (id_kriteria, nilai) VALUES ($id_kriteria, '$nilai')");
}

header("Location: kriteria.php");
exit;
