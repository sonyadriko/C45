<?php
include '../database/config.php';
$successMessage = '';
if (isset($_GET['upload']) && $_GET['upload'] === 'success') {
    $successMessage = "✅ Data berhasil diupload!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Data Penilaian</title>
  <link rel="stylesheet" href="../assets/css/styles.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>
<body>

<div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
   >
    <!-- Sidebar Start -->
    <?php include 'partials/sidebar.php' ?>
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      <?php include 'partials/header.php' ?>
      <!--  Header End -->
      <div class="body-wrapper-inner">
      <div class="container-fluid">
  <h3 class="mb-4">Data Penilaian Responden</h3>
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table id="tabel-penilaian" class="table table-bordered table-striped">
          <thead class="table-dark">
            <tr>
              <th>No</th>
              <th>Email</th>
              <th>Usia</th>
              <?php
              // Ambil semua kriteria untuk header kolom
              $kriteriaList = [];
              $qKriteria = mysqli_query($databaseConnection, "SELECT * FROM kriteria ORDER BY id_kriteria");
              while ($kr = mysqli_fetch_assoc($qKriteria)) {
                $kriteriaList[$kr['id_kriteria']] = $kr['nama_kriteria'];
                echo "<th>{$kr['nama_kriteria']}</th>";
              }
              ?>
            </tr>
          </thead>
          <tbody>
            <?php
            $qResponden = mysqli_query($databaseConnection, "SELECT * FROM responden");
            $no = 1;
            while ($r = mysqli_fetch_assoc($qResponden)) {
              echo "<tr>";
              echo "<td>{$no}</td>";
              echo "<td>{$r['email']}</td>";
              echo "<td>{$r['usia']}</td>";

              foreach ($kriteriaList as $id_kriteria => $nama_kriteria) {
                // Ambil nilai kriteria untuk responden ini
                $qNilai = mysqli_query($databaseConnection, "
                  SELECT nk.nilai 
                  FROM penilaian p 
                  JOIN nilai_kriteria nk ON p.id_nilai = nk.id_nilai
                  WHERE p.id_responden = {$r['id_responden']} AND p.id_kriteria = {$id_kriteria}
                  LIMIT 1
                ");
                $nilai = mysqli_fetch_assoc($qNilai);
                echo "<td>" . ($nilai ? $nilai['nilai'] : '-') . "</td>";
              }

              echo "</tr>";
              $no++;
            }
            ?>
          </tbody>
        </table>
      </div>
      <button id="btnProses" class="btn btn-success" style="width: auto; min-width: 200px; max-width: 100%;">Proses Analisis C4.5</button>

    </div>

    <script>
    document.getElementById('btnProses').addEventListener('click', () => {
      fetch('http://localhost:5000/c45/run')
        .then(res => res.json())
        .then(data => {
          document.getElementById('hasilC45').textContent = data.tree;
        })
        .catch(err => alert("Gagal hitung: " + err));
    });
    </script>
  </div>
</div>
      </div>
    </div>
  </div>



  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
  <script src="../assets/libs/apexcharts/dist/apexcharts.min.js"></script>
  <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
  <script src="../assets/js/dashboard.js"></script>
  <!-- solar icons -->
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
  $(document).ready(function () {
    $('#tabel-penilaian').DataTable({
      scrollX: true
    });
  });
</script>
</body>
</html>
