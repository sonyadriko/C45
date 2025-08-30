<?php
include '../includes/auth_helper.php';
requireLogin();
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard</title>
  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
  
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
          <!-- Error Message -->
          <?php if (isset($_GET['error']) && $_GET['error'] === 'unauthorized'): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Akses Ditolak!</strong> Anda tidak memiliki izin untuk mengakses halaman tersebut.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          <?php endif; ?>

          <!-- Welcome Message -->
          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <h4 class="card-title">Selamat Datang, <?= htmlspecialchars($_SESSION['user']['name'] ?? 'User') ?>!</h4>
                  <p class="card-text">
                    Anda login sebagai: <strong><?= ucwords(str_replace('_', ' ', $_SESSION['user']['role'] ?? 'user')) ?></strong>
                  </p>
                  <p class="card-text">
                    Sistem C4.5 Decision Tree untuk analisis kepuasan pelanggan.
                  </p>
                </div>
              </div>
            </div>
          </div>

          
        </div>
        
      </div>
      <?php include 'partials/footer.php' ?>

    </div>
  </div>
  <?php 
  // Load required scripts for this page
  $load_scripts = ['datatables'];
  include 'partials/scripts.php'; 
  ?>
</body>

</html>