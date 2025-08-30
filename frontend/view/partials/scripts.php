<?php
// Shared JavaScript and CSS imports for all pages
?>

<!-- Core Libraries from CDN -->
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- SimpleBar for scrollbars -->
<script src="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.js"></script>
<!-- Iconify Icons -->
<script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>

<!-- Core Application Scripts -->
<script src="../assets/js/sidebarmenu.js"></script>
<script src="../assets/js/app.min.js"></script>

<?php
// Additional scripts can be loaded conditionally based on page needs
$page_scripts = [
    'datatables' => [
        'js' => [
            'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js',
            'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js'
        ],
        'css' => 'https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css'
    ],
    'apexcharts' => [
        'js' => 'https://cdn.jsdelivr.net/npm/apexcharts@3.44.0/dist/apexcharts.min.js'
    ],
    'dashboard' => [
        'js' => '../assets/js/dashboard.js'
    ]
];

// Check if specific scripts are requested
$requested_scripts = isset($load_scripts) ? $load_scripts : [];

foreach ($requested_scripts as $script_name) {
    if (isset($page_scripts[$script_name])) {
        $script = $page_scripts[$script_name];
        
        // Load CSS if available
        if (isset($script['css'])) {
            echo '<link rel="stylesheet" href="' . $script['css'] . '">' . "\n";
        }
        
        // Load JS
        if (isset($script['js'])) {
            if (is_array($script['js'])) {
                foreach ($script['js'] as $js_file) {
                    echo '<script src="' . $js_file . '"></script>' . "\n";
                }
            } else {
                echo '<script src="' . $script['js'] . '"></script>' . "\n";
            }
        }
    }
}
?>