<?php
include '../includes/auth_helper.php';
requireAdmin(); // Hanya admin yang bisa akses halaman ini
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>Kriteria</title>
  <?php 
    // Load required styles for this page
    $load_styles = ['datatables'];
    include 'partials/styles.php'; 
  ?>
</head>

<body>
  <!--  Body Wrapper -->
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
          <!--  Row 1 -->
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between mb-3">
                <h5 class="card-title fw-semibold">Data Kriteria</h5>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKriteria">Tambah Kriteria</button>
              </div>

              <div class="table-responsive">
                <table id="tabel-kriteria" class="table table-bordered table-striped">
                  <thead class="table-dark">
                    <tr>
                      <th>No</th>
                      <th>Nama Kriteria</th>
                      <th>Jumlah Nilai</th>
                      <th>Detail</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    include '../database/config.php';
                    $no = 1;
                    $query = mysqli_query($databaseConnection, "SELECT k.id_kriteria, k.nama_kriteria, COUNT(n.id_nilai) as jumlah_nilai 
                                                  FROM kriteria k 
                                                  LEFT JOIN nilai_kriteria n ON k.id_kriteria = n.id_kriteria 
                                                  GROUP BY k.id_kriteria");
                    while ($row = mysqli_fetch_assoc($query)) {
                        echo "<tr>";
                        echo "<td>{$no}</td>";
                        echo "<td>{$row['nama_kriteria']}</td>";
                        echo "<td>{$row['jumlah_nilai']}</td>";
                        echo "<td><a href='detail_kriteria.php?id={$row['id_kriteria']}' class='btn btn-sm btn-info'>Detail</a></td>";
                        echo "</tr>";
                        $no++;
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>




          <?php include 'partials/footer.php' ?>
          
        </div>
      </div>
    </div>
  </div>

<!-- Modal -->
<div class="modal fade" id="modalTambahKriteria" tabindex="-1" aria-labelledby="modalTambahKriteriaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="tambah_kriteria.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTambahKriteriaLabel">Tambah Kriteria</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label>Nama Kriteria</label>
          <input type="text" name="nama_kriteria" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Nilai-nilai Kriteria (pisahkan dengan koma)</label>
          <input type="text" name="nilai_kriteria" class="form-control" placeholder="Contoh: Sangat Mahal, Mahal, Murah" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>



<?php 
  // Load required scripts for this page
  $load_scripts = ['datatables'];
  include 'partials/scripts.php'; 
  ?>
  <script>
    $(document).ready(function () {
      $('#tabel-kriteria').DataTable();
    });
  </script>

</body>

</html>