<?php
include '../database/config.php';

// Ambil data dari API Flask
$apiUrl = "http://localhost:5000/c45/akurasi";
$response = file_get_contents($apiUrl);
$data = json_decode($response, true);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pengujian C4.5</title>
  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="../assets/css/styles.min.css" />
</head>
<body>
  <!-- Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full">
    
    <!-- Sidebar -->
    <?php include 'partials/sidebar.php' ?>

    <!-- Main wrapper -->
    <div class="body-wrapper">
      
      <!-- Header -->
      <?php include 'partials/header.php' ?>

      <!-- Content -->
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <h4 class="fw-semibold mb-4">Hasil Pengujian Algoritma C4.5</h4>

          <!-- Akurasi dan skor -->
          <div class="row mb-4">
            <div class="col-md-4">
              <div class="card text-white bg-primary">
                <div class="card-body">
                  <h6 class="card-title">Akurasi</h6>
                  <h4><?= round($data['akurasi'] * 100, 2) ?>%</h4>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card text-white bg-success">
                <div class="card-body">
                  <h6 class="card-title">Precision</h6>
                  <h4><?= round($data['precision'] * 100, 2) ?>%</h4>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="card text-white bg-warning">
                <div class="card-body">
                  <h6 class="card-title">Recall</h6>
                  <h4><?= round($data['recall'] * 100, 2) ?>%</h4>
                </div>
              </div>
            </div>
          </div>

          <!-- F1-Score -->
          <div class="row mb-4">
            <div class="col-md-4">
              <div class="card text-white bg-info">
                <div class="card-body">
                  <h6 class="card-title">F1 Score</h6>
                  <h4><?= round($data['f1_score'] * 100, 2) ?>%</h4>
                </div>
              </div>
            </div>
          </div>

          <!-- Confusion Matrix -->
          <div class="card mb-4">
            <div class="card-header fw-semibold">Confusion Matrix</div>
            <div class="card-body">
              <table class="table table-bordered text-center">
                <thead class="table-light">
                  <tr>
                    <th></th>
                    <th>Pred: <?= $data['kelas_positif'] ?? 'Positif' ?></th>
                    <th>Pred: Negatif</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <th>Aktual: <?= $data['kelas_positif'] ?? 'Positif' ?></th>
                    <td><?= $data['confusion_matrix'][0][0] ?? '-' ?></td>
                    <td><?= $data['confusion_matrix'][0][1] ?? '-' ?></td>
                  </tr>
                  <tr>
                    <th>Aktual: Negatif</th>
                    <td><?= $data['confusion_matrix'][1][0] ?? '-' ?></td>
                    <td><?= $data['confusion_matrix'][1][1] ?? '-' ?></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Classification Report -->
          <!-- <div class="card">
            <div class="card-header fw-semibold">Laporan Klasifikasi Lengkap</div>
            <div class="card-body">
              <pre><?= $data['classification_report'] ?? 'Tidak tersedia' ?></pre>
            </div>
          </div> -->

        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <?php include 'partials/footer.php' ?>

  <!-- Script -->
  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
</body>
</html>
