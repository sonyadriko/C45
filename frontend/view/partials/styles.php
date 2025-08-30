<?php
// Shared CSS imports for all pages
?>

<!-- Favicon -->
<link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />

<!-- Core CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@6.2.5/dist/simplebar.min.css">
<link rel="stylesheet" href="../assets/css/styles.min.css" />

<?php
// Additional CSS can be loaded conditionally
if (isset($load_styles) && is_array($load_styles)) {
    foreach ($load_styles as $style) {
        switch ($style) {
            case 'datatables':
                echo '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">' . "\n";
                break;
        }
    }
}
?>