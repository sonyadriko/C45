<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Input Data Responden</title>
  <link rel="stylesheet" href="../assets/css/styles.min.css">
</head>
<body>
<div class="container py-5">
  <h3 class="mb-4">Input Data Responden</h3>

  <div class="card mb-4">
    <div class="card-body">
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
            <label>Nama Responden</label>
            <input type="text" name="nama_responden" class="form-control" required>
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
</body>
</html>
