<?php
include '../database/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Pohon Keputusan</title>
  <link rel="stylesheet" href="../assets/css/styles.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <script src="https://d3js.org/d3.v7.min.js"></script>
  <style>
    .node circle {
      fill: #6c5ce7;
      r: 6;
    }
    .node text {
      font-size: 12px;
      fill: #2d3436;
    }
    .link {
      fill: none;
      stroke: #636e72;
      stroke-width: 2px;
    }
  </style>
</head>
<body>
  

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
          <div class="container-fluid">
          <h3 class="mb-4">Pohon Keputusan</h3>

          <div class="card">
            <div class="card-body">
            
              <h3>Visualisasi Pohon Keputusan</h3>
              <svg id="tree" width="960" height="600"></svg>
              <div class="mt-4">
              <h5>Gambar Pohon Keputusan</h5>
              <div style="overflow-x: auto;">
                <img src="../../backend/tree_decision_c45.png" alt="Pohon Keputusan C4.5" style="max-width:100%; border:1px solid #ccc; background:#fff; padding:8px;">
              </div>
            </div>

            <script>
              const svg = d3.select("#tree"),
                    width = +svg.attr("width"),
                    height = +svg.attr("height");

              const g = svg.append("g").attr("transform", "translate(40,0)");

              const treeLayout = d3.tree().size([height - 100, width - 160]);
              const stratify = d3.stratify();

              d3.json("../../backend/tree_decision_c45.json").then(function(data) {
                const root = d3.hierarchy(data);
                treeLayout(root);

                g.selectAll(".link")
                  .data(root.links())
                  .join("path")
                  .attr("class", "link")
                  .attr("d", d3.linkHorizontal()
                      .x(d => d.y)
                      .y(d => d.x)
                  );

                const node = g.selectAll(".node")
                  .data(root.descendants())
                  .join("g")
                  .attr("class", "node")
                  .attr("transform", d => `translate(${d.y},${d.x})`);

                node.append("circle");

                node.append("text")
                  .attr("dy", 3)
                  .attr("x", d => d.children ? -8 : 8)
                  .style("text-anchor", d => d.children ? "end" : "start")
                  .text(d => d.data.name);
              });
            </script>

            </div>

            
            

          </div>
        </div>

          <?php include 'partials/footer.php' ?>
          
        </div>
      </div>
    </div>
  </div>

  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/sidebarmenu.js"></script>
  <script src="../assets/js/app.min.js"></script>
  <script src="../assets/libs/apexcharts/dist/apexcharts.min.js"></script>
  <script src="../assets/libs/simplebar/dist/simplebar.js"></script>
  <script src="../assets/js/dashboard.js"></script>
  <!-- solar icons -->
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
</body>
</html>
