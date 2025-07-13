<?php
include '../database/config.php';

$email = $_POST['email'];
$usia = $_POST['usia'];
$kriteria = $_POST['kriteria'];

// Simpan responden
$res1 = mysqli_query($databaseConnection, "INSERT INTO responden (email, usia) VALUES ('$email', $usia)");
$id_responden = mysqli_insert_id($databaseConnection);

$allSuccess = $res1;
// Simpan penilaian
foreach ($kriteria as $id_kriteria => $id_nilai) {
  $res2 = mysqli_query($databaseConnection, "INSERT INTO penilaian (id_responden, id_kriteria, id_nilai) VALUES ($id_responden, $id_kriteria, $id_nilai)");
  if (!$res2) $allSuccess = false;
}

if ($allSuccess) {
  header("Location: input_data.php?sukses=1");
} else {
  header("Location: input_data.php?gagal=1");
}
exit;
