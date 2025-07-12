<?php
include '../database/config.php';

$id_nilai = $_GET['id'];
$id_kriteria = $_GET['kriteria'];

mysqli_query($databaseConnection, "DELETE FROM nilai_kriteria WHERE id_nilai = $id_nilai");

header("Location: detail_kriteria.php?id=$id_kriteria");
exit;
