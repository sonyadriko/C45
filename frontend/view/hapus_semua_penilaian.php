<?php
include '../includes/auth_helper.php';
requireAdmin();

include '../database/config.php';

// Hapus data dari tabel penilaian terlebih dahulu (agar tidak melanggar foreign key)
mysqli_query($databaseConnection, "DELETE FROM penilaian");
// Hapus data dari tabel responden
mysqli_query($databaseConnection, "DELETE FROM responden");

// Redirect kembali ke halaman penilaian dengan pesan sukses
header('Location: penilaian.php?hapus=success');
exit; 