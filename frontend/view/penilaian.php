<?php
include '../includes/auth_helper.php';
requireAdmin(); // Hanya admin yang bisa akses halaman ini

include '../database/config.php';
$successMessage = '';
if (isset($_GET['upload']) && $_GET['upload'] === 'success') {
    $successMessage = "✅ Data berhasil diupload!";
}
if (isset($_GET['hapus']) && $_GET['hapus'] === 'success') {
    $successMessage = "✅ Semua data berhasil dihapus!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Data Penilaian</title>
  <?php 
  // Load required styles for this page
  $load_styles = ['datatables'];
  include 'partials/styles.php'; 
  ?>
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
<?php if ($successMessage): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= $successMessage ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>
      <h3 class="mb-4">Data Penilaian Responden</h3>
      <div class="card">

    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <button id="btnProses" class="btn btn-success" style="width: auto; min-width: 200px; max-width: 100%;">Proses Analisis C4.5</button>
        <button id="btnHapusSemua" class="btn btn-danger" style="width: auto; min-width: 200px; max-width: 100%;">Hapus Semua Data</button>
      </div>
      <div class="table-responsive mt-4">
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

    </div>

    <script>
    document.getElementById('btnProses').addEventListener('click', () => {
      // Tampilkan loading state
      const btn = document.getElementById('btnProses');
      const originalText = btn.textContent;
      btn.textContent = 'Memproses...';
      btn.disabled = true;
      
      // fetch('http://127.0.0.1:5000/c45/run')
      fetch('http://127.0.0.1:5000/c45/manual')
        .then(res => {
          if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
          }
          return res.json();
        })
        .then(data => {
          // Tampilkan hasil dalam alert atau buat elemen baru
          alert('Analisis C4.5 berhasil diproses!\n\nHasil tersimpan di:\n- Gambar: ' + data.image_saved + '\n- JSON: ' + data.json_saved + '\n\nPesan: ' + data.message);
          
          // Redirect ke halaman pohon keputusan untuk melihat hasil
          window.location.href = 'tabel_perhitungan.php';
        })
        .catch(err => {
          console.error('Error details:', err);
          alert("Gagal memproses analisis: " + err.message);
        })
        .finally(() => {
          // Kembalikan button ke state semula
          btn.textContent = originalText;
          btn.disabled = false;
        });
    });

    document.getElementById('btnHapusSemua').addEventListener('click', function() {
      if (confirm('Yakin ingin menghapus SEMUA data responden dan penilaian? Tindakan ini tidak dapat dibatalkan!')) {
        window.location.href = 'hapus_semua_penilaian.php';
      }
    });
    </script>
  </div>
</div>
      </div>
    </div>
  </div>



  <?php 
  // Load required scripts for this page
  $load_scripts = ['datatables'];
  include 'partials/scripts.php'; 
  ?>
<script>
  $(document).ready(function () {
    $('#tabel-penilaian').DataTable({
      scrollX: true
    });
  });
</script>
</body>
</html>
