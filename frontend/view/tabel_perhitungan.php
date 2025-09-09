<?php
include '../includes/auth_helper.php';
// requireAdmin(); // Hanya admin yang bisa akses halaman ini
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tabel Perhitungan C4.5</title>
  <?php 
  // Load required styles for this page
  $load_styles = ['datatables'];
  include 'partials/styles.php'; 
  ?>
  <style>
    .table-calculation {
      font-size: 0.9rem;
    }
    .table-calculation th {
      background-color: #495057;
      color: white;
      text-align: center;
      vertical-align: middle;
    }
    .table-calculation td {
      text-align: center;
      vertical-align: middle;
    }
    .entropy-value {
      font-weight: bold;
      color: #0d6efd;
    }
    .gain-value {
      font-weight: bold;
      color: #198754;
    }
    .loading-spinner {
      display: none;
      text-align: center;
      padding: 2rem;
    }
  </style>
</head>
<body>

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
<?php if(isAdmin()): ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h3>Tabel Perhitungan C4.5</h3>
          <div>
            <button id="btnMuat" class="btn btn-primary">Muat Data Perhitungan</button>
            <button id="btnPohon" class="btn btn-success">Lihat Pohon Keputusan</button>
          </div>
        </div>
        <?php else: ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h3>Pohon Keputusan C4.5</h3>
        </div>
        <?php endif; ?>

        <div class="card">
          <div class="card-body">
            <?php if(isAdmin()): ?>
            <div class="loading-spinner" id="loadingSpinner">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2">Memuat perhitungan...</p>
            </div>

            <div id="datasetInfo" class="alert alert-info" style="display: none;">
              <h5>Informasi Dataset:</h5>
              <p id="datasetDetails"></p>
            </div>

            <div class="table-responsive" id="tableContainer" style="display: none;">
              <table class="table table-bordered table-calculation" id="tabelPerhitungan">
                <thead>
                  <tr>
                    <th>Node</th>
                    <th>Kriteria</th>
                    <th>Value</th>
                    <th>Jumlah Kasus</th>
                    <th>Puas</th>
                    <th>Tidak Puas</th>
                    <th>Entropy</th>
                    <th>Information Gain</th>
                  </tr>
                </thead>
                <tbody id="tabelBody">
                  <!-- Data akan dimuat via JavaScript -->
                </tbody>
              </table>
            </div>

            <div id="errorMessage" class="alert alert-danger" style="display: none;">
              <p id="errorText"></p>
            </div>
            
            <div id="pohonContainer" class="mt-4" style="display: none;">
              <h5>Pohon Keputusan C4.5</h5>
              <div class="text-center">
                <img id="pohonImage" src="" alt="Pohon Keputusan" class="img-fluid" style="max-width: 100%; height: auto;">
              </div>
            </div>
            <?php else: ?>
            <div class="loading-spinner" id="loadingSpinnerKepala">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2">Memuat pohon keputusan...</p>
            </div>
            
            <div id="pohonContainerKepala" class="mt-4">
              <div class="text-center">
                <img id="pohonImageKepala" src="" alt="Pohon Keputusan" class="img-fluid" style="max-width: 100%; height: auto;">
              </div>
            </div>
            
            <div id="errorMessageKepala" class="alert alert-danger" style="display: none;">
              <p id="errorTextKepala"></p>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php 
  // Load required scripts for this page
  // $load_scripts = ['datatables'];
  include 'partials/scripts.php'; 
  ?>
  
<script>
<?php if(isAdmin()): ?>
document.getElementById('btnMuat').addEventListener('click', function() {
  const loadingSpinner = document.getElementById('loadingSpinner');
  const tableContainer = document.getElementById('tableContainer');
  const errorMessage = document.getElementById('errorMessage');
  const datasetInfo = document.getElementById('datasetInfo');
  const btn = this;

  // Show loading
  loadingSpinner.style.display = 'block';
  tableContainer.style.display = 'none';
  errorMessage.style.display = 'none';
  datasetInfo.style.display = 'none';
  btn.disabled = true;

  fetch('http://127.0.0.1:5000/c45/tabel')
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        throw new Error(data.error);
      }

      // Show dataset info
      document.getElementById('datasetDetails').innerHTML = `
        <strong>Total Sampel:</strong> ${data.dataset_info.total_samples} | 
        <strong>Puas:</strong> ${data.dataset_info.puas_count} | 
        <strong>Tidak Puas:</strong> ${data.dataset_info.tidak_puas_count} | 
        <strong>Entropy Awal:</strong> ${data.dataset_info.entropy || 'N/A'}
      `;
      datasetInfo.style.display = 'block';

      // Clear existing table data
      const tbody = document.getElementById('tabelBody');
      tbody.innerHTML = '';

      // Populate table dengan styling berbeda
      data.table_data.forEach(row => {
        const tr = document.createElement('tr');
        
        // Add special styling based on row type
        if (row.is_separator) {
          tr.className = 'table-warning';
          tr.style.fontWeight = 'bold';
          tr.style.textAlign = 'center';
        } else if (row.is_header) {
          tr.className = 'table-info';
          tr.style.fontWeight = 'bold';
        } else if (row.is_subnode_header) {
          tr.className = 'table-success';
          tr.style.fontWeight = 'bold';
        }
        
        tr.innerHTML = `
          <td>${row.node}</td>
          <td>${row.kriteria}</td>
          <td>${row.value}</td>
          <td>${row.jumlah_kasus}</td>
          <td>${row.puas}</td>
          <td>${row.tidak_puas}</td>
          <td class="entropy-value">${row.entropy}</td>
          <td class="gain-value">${row.information_gain}</td>
        `;
        tbody.appendChild(tr);
      });

      // Show table
      tableContainer.style.display = 'block';

      // Initialize DataTable after jQuery is loaded
      if (typeof $ !== 'undefined' && $.fn.DataTable) {
        if ($.fn.DataTable.isDataTable('#tabelPerhitungan')) {
          $('#tabelPerhitungan').DataTable().destroy();
        }
        $('#tabelPerhitungan').DataTable({
          scrollX: true,
          pageLength: 25,
          order: [[0, 'asc']]
        });
      }
    })
    .catch(error => {
      document.getElementById('errorText').textContent = 'Error: ' + error.message;
      errorMessage.style.display = 'block';
      console.error('Error:', error);
    })
    .finally(() => {
      loadingSpinner.style.display = 'none';
      btn.disabled = false;
    });
});

// Pohon Keputusan button
document.getElementById('btnPohon').addEventListener('click', function() {
  const pohonContainer = document.getElementById('pohonContainer');
  const pohonImage = document.getElementById('pohonImage');
  const btn = this;
  
  btn.disabled = true;
  btn.textContent = 'Loading...';
  
  fetch('http://127.0.0.1:5000/c45/run')
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        throw new Error(data.error);
      }
      
      // Tampilkan gambar pohon
      const timestamp = new Date().getTime();
      pohonImage.src = `http://127.0.0.1:5000/c45/tree-image?t=${timestamp}`;
      pohonContainer.style.display = 'block';
      
      // Scroll ke pohon
      pohonContainer.scrollIntoView({ behavior: 'smooth' });
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error: ' + error.message);
    })
    .finally(() => {
      btn.disabled = false;
      btn.textContent = 'Lihat Pohon Keputusan';
    });
});
<?php else: ?>
// Auto load pohon keputusan untuk kepala toko
document.addEventListener('DOMContentLoaded', function() {
  const loadingSpinner = document.getElementById('loadingSpinnerKepala');
  const pohonContainer = document.getElementById('pohonContainerKepala');
  const pohonImage = document.getElementById('pohonImageKepala');
  const errorMessage = document.getElementById('errorMessageKepala');
  
  // Show loading
  loadingSpinner.style.display = 'block';
  
  fetch('http://127.0.0.1:5000/c45/run')
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        throw new Error(data.error);
      }
      
      // Tampilkan gambar pohon
      const timestamp = new Date().getTime();
      pohonImage.src = `http://127.0.0.1:5000/c45/tree-image?t=${timestamp}`;
      pohonContainer.style.display = 'block';
    })
    .catch(error => {
      document.getElementById('errorTextKepala').textContent = 'Error: ' + error.message;
      errorMessage.style.display = 'block';
      console.error('Error:', error);
    })
    .finally(() => {
      loadingSpinner.style.display = 'none';
    });
});
<?php endif; ?>
</script>

</body>
</html>