<!--

/**
 * Project: vCompare Nexus 
 * File: artifacts.php
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
<!DOCTYPE html>
<html lang="en">

<head>

  <?php include("components/header.php"); ?>
  
  <script src="assets/js/vCompare/artifacts.js"></script>

</head>

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
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Artifcts</li>
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
        <div class="col-lg-6 col-md-6 mb-md-0 mb-4">
          <div class="card">
            <div class="card-header pb-0">
              <div class="row">
                <div class="col-lg-6 col-7">
                  <h6>Systems / Integration Suit Tenant</h6>
                  
                </div>
               
              </div>
            </div>
                   
            <div class="card-body px-0 pb-2">
              <div class="table-responsive">
              
                <table class="table align-items-center mb-0" id="artifacts-table">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2"
                        style="width: 140px; word-wrap: break-word; white-space: normal;">
                        Tenant
                    </th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2"
                        style="width: 180px; word-wrap: break-word; white-space: normal;">
                        Package
                    </th>

                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Package</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2"></th>
                    </tr>
                  </thead>
                  <tbody>
                   
                   <!-- Filled via DB Load -->
                   
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4 col-md-6">
        
       <div class="card pb-2 mb-4">
                <div class="card-header pb-0 p-3">
                  <h6 class="mb-0">Filter</h6>
                </div>
                <div class="card-body p-3">
                  <ul class="list-group">
                    <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex align-items-center">
                      <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center align-self-start">
                        <i class="material-symbols-rounded opacity-10">source_environment</i>
                      </div>
                      
                      <div class="d-flex flex-column">
                        <h6 class="mb-1 text-dark text-sm">Owner</h6>
                        
                        <select style="min-width:140px" class="form-select-sm" aria-label="Default select owner" id="filterOwner">
                          <option value=""></option>
                         </select>
                      </div>
                    </div>

                    
                    </li>
                    <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                      <div class="d-flex align-items-center">
                        <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                          <i class="material-symbols-rounded opacity-10">host</i>
                        </div>
                        <div class="d-flex flex-column">
                          <h6 class="mb-1 text-dark text-sm">Tenant</h6>
                        
                        
                        <select style="min-width:140px" class="form-select-sm" aria-label="Default select owner" id="filterTenant">
                          <option value=""></option>
                        </select>
                        
                        
                        </div>
                      </div>
                     
                    </li>
                    <li class="list-group-item border-0 d-flex justify-content-between ps-0 border-radius-lg">
                      <div class="d-flex align-items-center">
                        <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                          <i class="material-symbols-rounded opacity-10">deployed_code</i>
                        </div>
                        
                        <div class="d-flex flex-column">
                          <h6 class="mb-1 text-dark text-sm">Package</h6>
                          
                          <div class="input-group input-group-dynamic mb-4">
                            <span class="input-group-text" id="basic-addon1"></span>
                            <input id="filterPackage" type="text" class="form-control" placeholder="Package Filter" aria-label="Filter" aria-describedby="basic-addon1">
                        </div>

                        </div>
                      </div>
                 
                    </li>
                  </ul>
                </div>
              </div>
        
        
          <div class="card d-none" id="packageDetail">
            <div class="card-header pb-0">
              <h6>Package</h6>
             </div>

						<div class="card-body p-3">

							<hr class="horizontal border-secondary mt-0 mb-2"
								style="border: 1px solid #6c757d; opacity: 1;">

							<ul class="list-group">
                                <li class="list-group-item border-0 ps-0 text-sm">
                                    <div class="row">
                                        <div class="col-3"><strong class="text-dark">Name:</strong></div>
                                        <div class="col-auto"><span id="packageName"></span></div>
                                    </div>
                                </li>
                                <li class="list-group-item border-0 ps-0 text-sm">
                                    <div class="row">
                                        <div class="col-3"><strong class="text-dark">ID:</strong></div>
                                        <div class="col-auto"><span id="packageId"></span></div>
                                    </div>
                                </li>
                                <li class="list-group-item border-0 ps-0 text-sm">
                                    <div class="row">
                                        <div class="col-3"><strong class="text-dark">No. Artifacts:</strong></div>
                                        <div class="col-auto"><span id="packageCount"></span></div>
                                    </div>
                                </li>
                            </ul>



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