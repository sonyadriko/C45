<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

include '../database/config.php';
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

          <!-- Button untuk memulai pengujian -->
          <div class="row mb-4">
            <div class="col-12">
              <button id="btnPengujian" class="btn btn-primary btn-lg">
                <i class="ti ti-play me-2"></i>Mulai Pengujian C4.5
              </button>
              <div id="loadingSpinner" class="d-none mt-3">
                <div class="d-flex align-items-center">
                  <div class="spinner-border text-primary me-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                  </div>
                  <span class="text-muted">Sedang memproses pengujian...</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Container untuk hasil pengujian -->
          <div id="hasilPengujian" class="d-none">
            <!-- Akurasi dan skor -->
            <div class="row mb-4">
              <div class="col-md-4">
                <div class="card text-white bg-primary">
                  <div class="card-body">
                    <h6 class="card-title">Akurasi</h6>
                    <h4 id="akurasi">-</h4>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card text-white bg-success">
                  <div class="card-body">
                    <h6 class="card-title">Precision</h6>
                    <h4 id="precision">-</h4>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card text-white bg-warning">
                  <div class="card-body">
                    <h6 class="card-title">Recall</h6>
                    <h4 id="recall">-</h4>
                  </div>
                </div>
              </div>
            </div>

            <!-- F1-Score dan Error -->
            <div class="row mb-4">
              <div class="col-md-4">
                <div class="card text-white bg-info">
                  <div class="card-body">
                    <h6 class="card-title">F1 Score</h6>
                    <h4 id="f1Score">-</h4>
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="card text-white bg-danger">
                  <div class="card-body">
                    <h6 class="card-title">Error Rate</h6>
                    <h4 id="errorRate">-</h4>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tabel Testing Detail -->
            <div class="card mb-4">
              <div class="card-header fw-semibold">Detail Testing per Sampel</div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-bordered table-sm" id="tabelTesting">
                    <thead class="table-dark">
                      <tr>
                        <th>No</th>
                        <th>Garansi</th>
                        <th>Harga</th>
                        <th>Empati</th>
                        <th>Kepuasan<br>(Actual)</th>
                        <th>Hasil<br>(Predicted)</th>
                        <th>TP</th>
                        <th>TN</th>
                        <th>FP</th>
                        <th>FN</th>
                      </tr>
                    </thead>
                    <tbody id="testingTableBody">
                      <!-- Data akan dimuat via AJAX -->
                    </tbody>
                    <tfoot class="table-light">
                      <tr>
                        <th colspan="6">TOTAL</th>
                        <th id="totalTP">-</th>
                        <th id="totalTN">-</th>
                        <th id="totalFP">-</th>
                        <th id="totalFN">-</th>
                      </tr>
                      <tr>
                        <th colspan="6">Accuracy</th>
                        <th colspan="4" id="tableAccuracy">-</th>
                      </tr>
                      <tr>
                        <th colspan="6">Precision</th>
                        <th colspan="4" id="tablePrecision">-</th>
                      </tr>
                      <tr>
                        <th colspan="6">Recall</th>
                        <th colspan="4" id="tableRecall">-</th>
                      </tr>
                      <tr>
                        <th colspan="6">Error</th>
                        <th colspan="4" id="tableError">-</th>
                      </tr>
                    </tfoot>
                  </table>
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
                      <th id="kelasPositif">Pred: Positif</th>
                      <th>Pred: Negatif</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <th id="aktualPositif">Aktual: Positif</th>
                      <td id="tp">-</td>
                      <td id="fn">-</td>
                    </tr>
                    <tr>
                      <th>Aktual: Negatif</th>
                      <td id="fp">-</td>
                      <td id="tn">-</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <?php include 'partials/footer.php' ?>

  <!-- Script -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
  
  <script>
    $(document).ready(function() {
      $('#btnPengujian').click(function() {
        // Disable button dan tampilkan loading
        $(this).prop('disabled', true).html('<i class="ti ti-loader me-2"></i>Memproses...');
        $('#loadingSpinner').removeClass('d-none');
        $('#hasilPengujian').addClass('d-none');
        
        // Panggil API Flask baru untuk testing detail
        $.ajax({
          url: 'http://localhost:5000/c45/testing',
          method: 'GET',
          dataType: 'json',
          success: function(data) {
            const metrics = data.metrics;
            
            // Update nilai-nilai metrics
            $('#akurasi').text((metrics.accuracy * 100).toFixed(2) + '%');
            $('#precision').text((metrics.precision * 100).toFixed(2) + '%');
            $('#recall').text((metrics.recall * 100).toFixed(2) + '%');
            $('#f1Score').text((metrics.f1_score * 100).toFixed(2) + '%');
            $('#errorRate').text((metrics.error_rate * 100).toFixed(2) + '%');
            
            // Populate testing table
            const tbody = $('#testingTableBody');
            tbody.empty();
            
            data.testing_results.forEach(function(row, index) {
              const isCorrect = row.actual === row.predicted;
              const rowClass = isCorrect ? 'table-success' : 'table-danger';
              
              const tr = `
                <tr class="${rowClass}">
                  <td>${index + 1}</td>
                  <td>${row.garansi}</td>
                  <td>${row.harga}</td>
                  <td>${row.empati}</td>
                  <td>${row.actual}</td>
                  <td>${row.predicted}</td>
                  <td>${row.tp}</td>
                  <td>${row.tn}</td>
                  <td>${row.fp}</td>
                  <td>${row.fn}</td>
                </tr>
              `;
              tbody.append(tr);
            });
            
            // Update totals in table footer
            $('#totalTP').text(metrics.total_tp);
            $('#totalTN').text(metrics.total_tn);
            $('#totalFP').text(metrics.total_fp);
            $('#totalFN').text(metrics.total_fn);
            
            // Update metrics in table footer
            $('#tableAccuracy').text((metrics.accuracy * 100).toFixed(2) + '%');
            $('#tablePrecision').text((metrics.precision * 100).toFixed(2) + '%');
            $('#tableRecall').text((metrics.recall * 100).toFixed(2) + '%');
            $('#tableError').text((metrics.error_rate * 100).toFixed(2) + '%');
            
            // Update confusion matrix
            const cm = data.confusion_matrix;
            $('#kelasPositif').text('Pred: Puas');
            $('#aktualPositif').text('Aktual: Puas');
            $('#tp').text(cm[0][0]);
            $('#fn').text(cm[0][1]);
            $('#fp').text(cm[1][0]);
            $('#tn').text(cm[1][1]);
            
            // Tampilkan hasil
            $('#hasilPengujian').removeClass('d-none');
            
            // Sembunyikan loading dan reset button
            $('#loadingSpinner').addClass('d-none');
            $('#btnPengujian').prop('disabled', false).html('<i class="ti ti-play me-2"></i>Mulai Pengujian C4.5');
          },
          error: function(xhr, status, error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memproses pengujian. Silakan coba lagi.');
            
            // Reset button dan sembunyikan loading
            $('#loadingSpinner').addClass('d-none');
            $('#btnPengujian').prop('disabled', false).html('<i class="ti ti-play me-2"></i>Mulai Pengujian C4.5');
          }
        });
      });
    });
  </script>
</body>
</html>
