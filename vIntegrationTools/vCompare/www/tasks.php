<!--

/**
 * Project: vCompare Nexus 
 * File: index.php
 * Author: Stefan Zimara <stefan.zimara@valcoba.com>
 * Created: 2025-19-01
 * Updated: 2025-19-01
 *
 * Description:
 * This is an enhancment of vCompare to analyze data in you browser
 *
 * Documentation:
 * For detailed documentation, refer to:
 * https://github.com/stefanzimara/vIntegrationTools
 *
 * History:
 * - 2025-01-01: Initial version created by Stefan Zimara
 * - 2025-01-10: DB Export integrated
 * - 2025-01-19: Initial Version of vCompare Nexus
 * 
 * Usage:
 * https://github.com/stefanzimara/vIntegrationTools/wiki
 *    
 * License:
 * This project is licensed under the AGPL License - see the LICENSE file for details.
 * 
 * Layout:
 * Layout is based on Material Dashboard 3 - v3.2.0
 * Product Page: https://www.creative-tim.com/product/material-dashboard
**/

-->
<head>
<?php include("components/header.php"); ?>
  
  
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="assets/js/vCompare/ajax.js"></script>
  <script src="assets/js/vCompare/tasks.js"></script>
  <script src="assets/js/vCompare/core.js"></script>
  <link href="/assets/css/vCompare.css" rel="stylesheet" />

</head>

<script>
  //console.log(typeof $); // Sollte "function" anzeigen
</script>



<body class="g-sidenav-show  bg-gray-100">

 	<?php 
 	
 	  include("components/menu.php");
 	
 	?>
 	
 	
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-3 shadow-none border-radius-xl" id="navbarBlur" data-scroll="true">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="javascript:;">Pages</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Task executor</li>
          </ol>
        </nav>
        
        
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
          <div class="ms-md-auto pe-md-3 d-flex align-items-center">
          </div>
          <ul class="navbar-nav d-flex align-items-center  justify-content-end">
          
            <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                <div class="sidenav-toggler-inner">
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                </div>
              </a>
            </li>
            <li class="nav-item px-3 d-flex align-items-center">
              <a href="settings.php" class="nav-link text-body p-0">
                <i class="material-symbols-rounded fixed-plugin-button-nav">settings</i>
              </a>
            </li>
            
          
            
          </ul>
        </div>
      </div>
    </nav>
    <!-- End Navbar -->
    
    <div class="container-fluid py-2">
    
      <div class="row mb-4">
        <div class="col mb-md-0 mb-4">
        
        	<div class="card h-100">
            <div class="card-header pb-0">
              <h6>Task Executor</h6>
              <p class="text-sm">
                <i class="fa fa-arrow-up text-success" aria-hidden="true"></i>
            
            
                <p>
       On this page, you can start the data ingestion process and monitor its 
       progress in real time. Use this tool to efficiently manage and track 
       your background tasks.

    </p>
   
            
            
            
              </p>
            </div>
            </div>
        
          
        </div>
        
        
        
                <div class="col mb-md-0 mb-4">
        
        	<div class="card h-100">
            <div class="card-header pb-0">
              <h6>Read Data</h6>
              <p class="text-sm">
                <i class="fa fa-arrow-up text-success" aria-hidden="true"></i>
            
            
               <div class="d-flex justify-content-center">
  <button id="startButton" type="button" class="btn btn-secondary btn-lg">Start Import</button>
</div>

   
            
            
            
              </p>
            </div>
            </div>
        
          
        </div>
      </div>
      

      
   
     
     <!-- Footer -->
     <?php include("components/footer.php");?>
     
    </div>
  </main>
 
  <!--   Core JS Files   -->
  <script src="/assets/js/core/popper.min.js"></script>
  <script src="/assets/js/core/bootstrap.min.js"></script>
  <script src="/assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="/assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="/assets/js/plugins/chartjs.min.js"></script>

  
  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Material Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="/assets/js/material-dashboard.min.js?v=3.2.0"></script>
</body>

</html>