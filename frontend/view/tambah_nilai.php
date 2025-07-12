<?php
include '../database/config.php';

$id_kriteria = $_POST['id_kriteria'];
$nilai = trim($_POST['nilai']);

if ($nilai != '') {
    mysqli_query($databaseConnection, "INSERT INTO nilai_kriteria (id_kriteria, nilai) VALUES ($id_kriteria, '$nilai')");
}

header("Location: detail_kriteria.php?id=$id_kriteria");
exit;
