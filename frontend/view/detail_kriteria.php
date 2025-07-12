<?php
include '../database/config.php';

if (!isset($_GET['id'])) {
  echo "ID Kriteria tidak ditemukan.";
  exit;
}

$id_kriteria = $_GET['id'];

// Ambil nama kriteria
$q_kriteria = mysqli_query($databaseConnection, "SELECT * FROM kriteria WHERE id_kriteria = $id_kriteria");
$kriteria = mysqli_fetch_assoc($q_kriteria);

// Ambil nilai-nilainya
$q_nilai = mysqli_query($databaseConnection, "SELECT * FROM nilai_kriteria WHERE id_kriteria = $id_kriteria");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Detail Kriteria</title>
  <link rel="stylesheet" href="../assets/css/styles.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>
<body>
<div class="container py-5">
  <h3 class="mb-4">Detail Kriteria: <strong><?= $kriteria['nama_kriteria'] ?></strong></h3>

  <a href="kriteria.php" class="btn btn-secondary mb-3">← Kembali</a>

  <!-- Form Tambah Nilai -->
  <form action="tambah_nilai.php" method="POST" class="row g-3 mb-4">
    <input type="hidden" name="id_kriteria" value="<?= $id_kriteria ?>">
    <div class="col-md-8">
      <input type="text" name="nilai" class="form-control" placeholder="Masukkan Nilai Baru (contoh: Sangat Murah)" required>
    </div>
    <div class="col-md-4">
      <button type="submit" class="btn btn-primary">Tambah Nilai</button>
    </div>
  </form>

  <div class="card">
    <div class="card-body">
      <table id="tabel-nilai" class="table table-bordered table-striped">
        <thead class="table-dark">
          <tr>
            <th>No</th>
            <th>Nilai</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = 1;
          while ($row = mysqli_fetch_assoc($q_nilai)) {
            echo "<tr>";
            echo "<td>{$no}</td>";
            echo "<td>{$row['nilai']}</td>";
            echo "<td><a href='hapus_nilai.php?id={$row['id_nilai']}&kriteria={$id_kriteria}' class='btn btn-sm btn-danger' onclick=\"return confirm('Yakin hapus nilai ini?')\">Hapus</a></td>";
            echo "</tr>";
            $no++;
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="../assets/libs/jquery/dist/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
  $(document).ready(function () {
    $('#tabel-nilai').DataTable();
  });
</script>
</body>
</html>
