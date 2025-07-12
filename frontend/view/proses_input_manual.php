<?php
include '../database/config.php';

$nama = $_POST['nama_responden'];
$usia = $_POST['usia'];
$kriteria = $_POST['kriteria'];

// Simpan responden
mysqli_query($databaseConnection, "INSERT INTO responden (nama_responden, usia) VALUES ('$nama', $usia)");
$id_responden = mysqli_insert_id($databaseConnection);

// Simpan penilaian
foreach ($kriteria as $id_kriteria => $id_nilai) {
  mysqli_query($databaseConnection, "INSERT INTO penilaian (id_responden, id_kriteria, id_nilai) VALUES ($id_responden, $id_kriteria, $id_nilai)");
}

header("Location: input_data.php?success=1");
exit;
