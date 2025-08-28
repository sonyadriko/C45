<?php
// session_start();
// Get user role from session
$userRole = isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : 'user';
?>
<!-- Import Iconify untuk menampilkan icon -->
<script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

<aside class="left-sidebar">
      <!-- Sidebar scroll-->
      <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
          <a href="./index.php" class="text-nowrap logo-img" style="font-size: 2rem; font-weight: bold; color: #2A3547; text-decoration: none;">
            C45
          </a>
          <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
            <iconify-icon icon="solar:close-circle-bold" width="24" height="24"></iconify-icon>
          </div>
        </div>
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
          <ul id="sidebarnav">
            <li class="nav-small-cap">
              <iconify-icon icon="solar:home-2-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">Menu</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="./index.php" aria-expanded="false">
              <iconify-icon icon="mdi:view-dashboard" width="24" height="24"></iconify-icon>
                <span class="hide-menu">Dashboard</span>
              </a>
            </li>
            
            <?php if ($userRole === 'admin'): ?>
            <li class="sidebar-item">
              <a class="sidebar-link" href="./kriteria.php" aria-expanded="false">
                <iconify-icon icon="solar:layers-bold" width="24" height="24"></iconify-icon>
                <span class="hide-menu">Kriteria</span>
              </a>
            </li>
            <?php endif; ?>
            
            <?php if ($userRole === 'admin'): ?>
            <li class="sidebar-item">
              <a class="sidebar-link" href="./penilaian.php" aria-expanded="false">
                <iconify-icon icon="solar:clipboard-check-bold" width="24" height="24"></iconify-icon>
                <span class="hide-menu">Penilaian</span>
              </a>
            </li>
            <?php endif; ?>
            <!--<li class="sidebar-item">
              <a class="sidebar-link" href="./pohon_keputusan.php" aria-expanded="false">
              <iconify-icon icon="mdi:tree-outline" width="24" height="24"></iconify-icon>
                <span class="hide-menu">Pohon Keputusan</span>
              </a>
            </li> -->
            <?php if ($userRole === 'admin'): ?>
            <li class="sidebar-item">
              <a class="sidebar-link" href="./tabel_perhitungan.php" aria-expanded="false">
                <iconify-icon icon="solar:calculator-bold" width="24" height="24"></iconify-icon>
                <span class="hide-menu">Tabel Perhitungan</span>
              </a>
            </li>
            <?php endif; ?>
            <li class="sidebar-item">
              <a class="sidebar-link" href="./pengujian.php" aria-expanded="false">
                <iconify-icon icon="solar:monitor-bold" width="24" height="24"></iconify-icon>
                <span class="hide-menu">Pengujian</span>
              </a>
            </li>
            
            <!-- Menu Admin Only -->
            <?php if ($userRole === 'admin'): ?>
            <li class="nav-small-cap">
              <iconify-icon icon="solar:settings-bold" class="nav-small-cap-icon fs-4"></iconify-icon>
              <span class="hide-menu">Admin</span>
            </li>
            <li class="sidebar-item">
              <a class="sidebar-link" href="./input_data.php" aria-expanded="false">
                <iconify-icon icon="solar:upload-bold" width="24" height="24"></iconify-icon>
                <span class="hide-menu">Input Data</span>
              </a>
            </li>
            <?php endif; ?>
          </ul>
          
        </nav>
        <!-- End Sidebar navigation -->
      </div>
      <!-- End Sidebar scroll-->
    </aside>