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

  // mapping manual header ke id_kriteria
  $map_kriteria = [
      "Bagaimana pendapat Anda mengenai harga pada toko ini?" => "Harga",
      "Seberapa aman Anda merasa selama berada di toko ini, termasuk perlindungan terhadap barang pribadi, keamanan di area parkir, keberadaan CCTV, serta situasi lingkungan sekitar selama berbelanja?" => "Keamanan",
      "Bagaimana pendapat Anda mengenai lokasi toko ini, apakah strategis dan mudah dijangkau?" => "Lokasi",
      "Bagaimana pendapat Anda tentang fasilitas yang tersedia di toko ini?" => "Fasilitas",
      "Bagaimana pendapat Anda tentang kualitas pelayanan di toko ini?" => "Pelayanan",
      "Bagaimana pendapat Anda tentang empati dan kepedulian karyawan toko terhadap kebutuhan Anda saat berbelanja" => "Empati",
      "Seberapa nyaman Anda pada saat berbelanja pada toko ini?" => "Kenyamanan",
      "Bagaimana pendapat Anda tentang kelengkapan barang pada toko ini?" => "Kelengkapan Barang",
      "Bagaimana pendapat Anda tentang kualitas produk yang dijual di toko ini?" => "Kualitas Produk",
      "Bagaimana pendapat Anda tentang merk produk yang dijual di toko ini?" => "Merk",
      "Bagaimana pendapat Anda tentang diskon yang ditawarkan oleh toko ini?" => "Diskon",
      "Bagaimana pendapat Anda tentang daya tanggap karyawan dalam melayani dan membantu pelanggan?" => "Daya Tanggap",
      "Bagaimana pendapat Anda tentang promosi yang dilakukan oleh toko ini?" => "Promosi",
      "Seberapa cepat karyawan merespon pertanyaan atau keluhan Anda saat berbelanja di toko ini?" => "Respon",
      "Bagaimana pendapat Anda tentang aspek fisik toko ini, seperti kebersihan, desain interior, dan kerapihan produk?" => "Bukti Fisik",
      "Apakah toko ini menyediakan garansi atau layanan penukaran produk?" => "Garansi",
      "Bagaimana pendapat Anda tentang kecepatan layanan yang diberikan oleh karyawan di toko ini?" => "Kecepatan Layanan",
      "Bagaimana pendapat Anda tentang kesesuaian produk yang dijual di toko ini dengan tren fashion saat ini?" => "Trend",
      "Apakah Anda puas berbelanja pada toko ini?" => "Kepuasan"
  ];
    

  foreach ($data as $row) {
      $timestamp = date('Y-m-d H:i:s', strtotime($row[0])); // Kolom Timestamp
      $email = trim($row[1]); // Email Address
      $usia = intval($row[count($row) - 1]); // Usia (kolom terakhir)

      // Simpan ke tabel responden
      $res1 = mysqli_query($databaseConnection, 
          "INSERT INTO responden (email, usia, created_at) 
           VALUES ('$email', $usia, '$timestamp')");
      if (!$res1) $uploadSuccess = false;

      $id_responden = mysqli_insert_id($databaseConnection);

      $index = 2; // Mulai dari kolom ke-3 (Harga)
      
      foreach ($map_kriteria as $kolom => $nama_kriteria) {
          // Ambil ID kriteria
          $qk = mysqli_query($databaseConnection, "SELECT id_kriteria FROM kriteria WHERE nama_kriteria = '$nama_kriteria' LIMIT 1");
          $dk = mysqli_fetch_assoc($qk);
          $id_kriteria = $dk['id_kriteria'];

          // Ambil ID nilai_kriteria
          $jawaban = trim($row[$index]);
          $qn = mysqli_query($databaseConnection, "SELECT id_nilai FROM nilai_kriteria WHERE nilai = '$jawaban' AND id_kriteria = $id_kriteria");
          $dn = mysqli_fetch_assoc($qn);
          if ($dn) {
              $id_nilai = $dn['id_nilai'];
              $res2 = mysqli_query($databaseConnection, "INSERT INTO penilaian (id_responden, id_kriteria, id_nilai) VALUES ($id_responden, $id_kriteria, $id_nilai)");
              if (!$res2) $uploadSuccess = false;
          }
          $index++;
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
