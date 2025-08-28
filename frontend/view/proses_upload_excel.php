<?php
require '../vendor/autoload.php'; // pastikan path sesuai

use PhpOffice\PhpSpreadsheet\IOFactory;

include '../database/config.php';

$file = $_FILES['file_excel']['tmp_name'];
$uploadSuccess = true;

try {
  $spreadsheet = IOFactory::load($file);
  $sheet = $spreadsheet->getActiveSheet();
  $data = $sheet->toArray();

  $header = $data[0];
  unset($data[0]);

  // mapping berdasarkan urutan kolom Excel
  $kriteria_order = [
      "Harga",
      "Keamanan", 
      "Lokasi",
      "Fasilitas",
      "Pelayanan",
      "Empati",
      "Kenyamanan",
      "Kelengkapan Barang",
      "Kualitas Produk",
      "Merk",
      "Diskon",
      "Daya Tanggap",
      "Promosi",
      "Respon",
      "Bukti Fisik",
      "Garansi",
      "Kecepatan Layanan",
      "Trend",
      "Kepuasan"
  ];
    

  foreach ($data as $row) {
      // Excel format: No, Email, Harga, Keamanan, ..., Trend, Kepuasan, Usia
      $timestamp = date('Y-m-d H:i:s'); // Timestamp sekarang
      $email = trim($row[1]); // Email dari kolom ke-2 (index 1)
      $usia = intval($row[21]); // Usia di kolom ke-22 (index 21)

      // Simpan ke tabel responden
      $res1 = mysqli_query($databaseConnection, 
          "INSERT INTO responden (email, usia, created_at) 
           VALUES ('$email', $usia, '$timestamp')");
      if (!$res1) $uploadSuccess = false;

      $id_responden = mysqli_insert_id($databaseConnection);

      // Proses setiap kriteria mulai dari kolom 2 (Harga) sampai 20 (Kepuasan)
      for ($i = 0; $i < count($kriteria_order); $i++) {
          $nama_kriteria = $kriteria_order[$i];
          $jawaban = trim($row[$i + 2]); // +2 karena kolom 0=No, kolom 1=Email
          
          // Ambil ID kriteria
          $qk = mysqli_query($databaseConnection, "SELECT id_kriteria FROM kriteria WHERE nama_kriteria = '$nama_kriteria' LIMIT 1");
          $dk = mysqli_fetch_assoc($qk);
          if ($dk) {
              $id_kriteria = $dk['id_kriteria'];

              // Ambil ID nilai_kriteria
              $qn = mysqli_query($databaseConnection, "SELECT id_nilai FROM nilai_kriteria WHERE nilai = '$jawaban' AND id_kriteria = $id_kriteria");
              $dn = mysqli_fetch_assoc($qn);
              if ($dn) {
                  $id_nilai = $dn['id_nilai'];
                  $res2 = mysqli_query($databaseConnection, "INSERT INTO penilaian (id_responden, id_kriteria, id_nilai) VALUES ($id_responden, $id_kriteria, $id_nilai)");
                  if (!$res2) $uploadSuccess = false;
              }
          }
      }
  }
} catch (Exception $e) {
  $uploadSuccess = false;
}

if ($uploadSuccess) {
  header("Location: input_data.php?sukses=1");
} else {
  header("Location: input_data.php?gagal=1");
}
exit;
