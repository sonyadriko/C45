<?php
include '../includes/auth_helper.php';
requireAdmin(); // Hanya admin yang bisa akses halaman ini

// Notifikasi sukses/gagal
$alert = '';
if (isset($_GET['sukses'])) {
  $alert = '<div class="alert alert-success alert-dismissible fade show" role="alert">Data berhasil disimpan.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
}
if (isset($_GET['gagal'])) {
  $alert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">Gagal menyimpan data!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Input Data Responden</title>
  <link rel="stylesheet" href="../assets/css/styles.min.css">
</head>
<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full">
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
          <?php if ($alert) echo $alert; ?>
          <h4 class="fw-semibold mb-4">Input Data Responden</h4>

          <div class="card mb-4">
            <div class="card-body">
              <h5 class="card-title">Upload File Excel</h5>
              <form action="proses_upload_excel.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                  <label class="form-label">Upload File Excel (.xlsx)</label>
                  <input type="file" name="file_excel" accept=".xlsx" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Upload & Simpan</button>
              </form>
            </div>
          </div>

          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Input Manual</h5>
              <form action="proses_input_manual.php" method="POST">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label>Email</label>
                    <input type="text" name="email" class="form-control" required>
                  </div>
                  <div class="col-md-6">
                    <label>Usia</label>
                    <input type="number" name="usia" class="form-control" required>
                  </div>
                </div>
                <div class="mt-3">
                  <?php
                  include '../database/config.php';
                  $q = mysqli_query($databaseConnection, "SELECT * FROM kriteria");
                  while ($k = mysqli_fetch_assoc($q)) {
                    echo "<div class='mb-3'>";
                    echo "<label>{$k['nama_kriteria']}</label>";
                    echo "<select name='kriteria[{$k['id_kriteria']}]' class='form-select' required>";
                    $nq = mysqli_query($databaseConnection, "SELECT * FROM nilai_kriteria WHERE id_kriteria = {$k['id_kriteria']}");
                    while ($n = mysqli_fetch_assoc($nq)) {
                      echo "<option value='{$n['id_nilai']}'>{$n['nilai']}</option>";
                    }
                    echo "</select></div>";
                  }
                  ?>
                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <?php include 'partials/footer.php' ?>

  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
</body>
</html>
