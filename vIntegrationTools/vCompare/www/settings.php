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
<!DOCTYPE html>
<html lang="en">

<head>
<?php include("components/header.php"); ?>
  
  
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="assets/js/vCompare/ajax.js"></script>
  <script src="assets/js/vCompare/settings.js"></script>
  <script src="assets/js/vCompare/core.js"></script>
  <link href="/assets/css/vCompare.css" rel="stylesheet" />

</head>

<script>
  console.log(typeof $); // Sollte "function" anzeigen
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
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Settings</li>
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
                <div style="min-height:145px">
                  <h6>Owner Management</h6>
                  <p class="text-sm mb-1">
                    <i class="fa fa-check text-info" aria-hidden="true"></i>
                    <span class="font-weight-bold">Owners</span> represent the entities responsible for your integration tenants, 
                    offering flexibility <span class="font-weight-bold">to match your organizational structure</span>. Whether it's a company, 
                    department, or specific site, you can use Owners to group and manage tenants effectively. 
                    
                    For consultants, Owners might represent clients; for internal teams, they could denote sites or departments. 
                    <span class="font-weight-bold">Customize this categorization to suit your needs and streamline tenant organization.</span>
                  </p>
                </div>
               
              </div>
            </div>
                   
            
            <div class="card-body px-0 pb-2">
              <div class="table-responsive">
                <table class="table align-items-center mb-0" id="v_Owner-table">
                  <thead id="v_Owner-head">
                    <tr>
	                  <th id="f-id" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-2"></th>
                      <th id="f-name" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Company</th>
                      <th id="f-info" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Info</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                    </tr>
                  </thead>
                  <tbody id="v_Owner-body">
                    <!-- 
                    <tr>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div>
                            <i class="material-symbols-rounded opacity-5 me-3">source_environment</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">Material XD Version</h6>
                          </div>
                        </div>
                      </td>
                      <td>
                        
                         <div class="d-flex px-2 py-1">
                          <div>
                            <i class="material-symbols-rounded opacity-5 me-2">cloud_circle</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">Material XD Version</h6>
                          </div>
                        </div>
                        
                        
                      </td>
                      <td class="align-middle text-center text-sm">
                        <span class="text-xs font-weight-bold"> $14,000 </span>
                      </td>
                     
                     
                    </tr>
                    -->
                  </tbody>
                  
                  <tfoot>
                      <tr>
                        <td colspan="4" class="text-end">
                            <i class="material-symbols-rounded vc-pointer" id="addOwner">add_box</i>
                         </td>
                      </tr>
                    </tfoot>
                  
                  
                </table>
                
                 </div>
                
                <div id="ownerForm" class="container-fluid d-none">
                
                <hr class="my-4 hr border-secondary border-top" />

                <form>
                <div class="input-group input-group-outline mb-4">
                  <label class="form-label">Owner</label>
                  <input type="text" id="ownerName" class="form-control" aria-label="Owner" aria-describedby="basic-addon1">
                </div>
               <div class="row g-2 mb-4 align-items-center">
              <div class="col">
                <div class="input-group input-group-outline">
	                <label class="form-label">Info</label>
                  <input type="text" id="ownerInfo" class="form-control" aria-label="Information">
                </div>
              </div>
              <div class="col-auto">
                <!-- Button Gruppe -->
                <div class="btn-group">
                  <button class="btn btn-icon btn-3 btn-outline-secondary" type="button" id="saveOwner">
                    <span class="btn-inner--icon"><i class="material-symbols-rounded">save</i></span>
                    <span class="btn-inner--text">Save</span>
                  </button>
                  <button class="btn btn-icon btn-3 btn-outline-danger" type="button" id="ownerCancel">
                    <span class="btn-inner--icon"><i class="material-symbols-rounded">close</i></span>
                    <span class="btn-inner--text vc-pointer">Cancel</span>
                  </button>
                </div>
              </div>
            </div>

                 
                </form>                
                
              </div>
            
            
            
            </div>
          </div>
        </div>
        
        
 <div class="col-lg-6 col-md-6 mb-md-0 mb-4">
          <div class="card">
            <div class="card-header pb-0">
              <div class="row">
                <div style="min-height:145px">
                  <h6>Tenant Management</h6>
                  <p class="text-sm mb-1">
                    <i class="fa fa-check text-info" aria-hidden="true"></i>
                    <span class="font-weight-bold"> Tenants are individual integration environments that serve as containers for your configurations</span>
                   , deployments, and data flows. Each tenant represents a distinct workspace, often linked to a specific Owner. Use tenants to separate environments like development, testing, or production, or to manage integrations for different customers, departments, or sites. This structure helps maintain clarity, organization, and control across your integration landscape.
                  </p>
                  </div>
               
              </div>
            </div>
            
                   <div class="card-body px-0 pb-2">
              <div class="table-responsive">
                <table class="table align-items-center mb-0" id="vcompare_tenant_v-table">
                  <thead id="vcompare_tenant_v-head">
                    <tr>
	                  <th id="f-id" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-2"></th>
                      <th id="f-owner" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Owner</th>
                      <th id="f-name" class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tenant</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Action</th>
                    </tr>
                  </thead>
                  <thead id="vcompare_tenant_v-body">
                  
                  <!-- 
                    <tr>
                     
                      <td>
                        
                         <div class="d-flex px-2 py-1">
                          <div>
                            <i class="material-symbols-rounded opacity-5 me-3">cloud_circle</i>
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">Material XD Version</h6>
                          </div>
                        </div>
                        
                        
                      </td>
                      <td class="align-middle text-center text-sm">
                        <span class="text-xs font-weight-bold"> $14,000 </span>
                      </td>
                      <td class="align-middle">
                        <div class="progress-wrapper w-75 mx-auto">
                          <div class="progress-info">
                            <div class="progress-percentage">
                              <span class="text-xs font-weight-bold">60%</span>
                            </div>
                          </div>
                          <div class="progress">
                            <div class="progress-bar bg-gradient-info w-60" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                        </div>
                      </td>
                    </tr>
                    -->
                  </tbody>
                  
                  <tfoot>
                      <tr>
                        <td colspan="4" class="text-end">
                            <i class="material-symbols-rounded vc-pointer" id="addTenant">add_box</i>
                         </td>
                      </tr>
                    </tfoot>
                 </table>
              </div>
              
                <div id="tenantForm" class="container-fluid d-none">
                
                <hr class="my-4 hr border-secondary border-top" />




                <form>

               <div class="row g-2 mb-4 align-items-center">
	              <div class="col">
                    <div class="input-group">
                      <span class="input-group-text me-4" id="tier-label">Owner</span>
                      <select id="tenantOwner" class="form-select" aria-label="Owner" aria-describedby="tier-label">
                        <option value=""></option>
                      </select>
                    </div>
                    
					</div>
					  <div class="col">
                    <div class="input-group input-group-outline">
                  <label class="form-label">Tenant</label>
                  <input type="text" id="tenantName" class="form-control" aria-label="Tenant" aria-describedby="basic-addon1">
                </div>
					</div>
					</div>

               <div class="row g-2 mb-4 align-items-center">
	              <div class="col">
                    
                   <div class="input-group">
                      <span class="input-group-text me-4" id="tier-label">System Tier</span>
                      <select id="tenantTier" class="form-select" aria-label="System Tier" aria-describedby="tier-label">
                        <option value="D">Development</option>
                        <option value="Q">Quality</option>
                        <option value="P">Production</option>
                      </select>
                    </div>


                    

					</div>
					
					  <div class="col">
                    <div class="input-group input-group-outline">
                  <label class="form-label">Host incl. Port</label>
                  <input type="text" id="tenantHost" class="form-control" aria-label="Host" aria-describedby="basic-addon1">
                </div>
					</div>
					</div>


				<div class="row g-2 mb-4 align-items-center">
	              <div class="col">
                    <div class="input-group input-group-outline">
                      <label class="form-label">Client ID</label>
                      <input type="text" id="tenantClient" class="form-control" aria-label="client" aria-describedby="basic-addon1">
                    </div>
					</div>
					
					  <div class="col">
                    <div class="input-group input-group-outline">
                  <label class="form-label">Secret</label>
                  <input type="text" id="tenantSecret" class="form-control" aria-label="Host" aria-describedby="basic-addon1">
                </div>
					</div>
					</div>


               <div class="row g-2 align-items-baseline">
              <div class="col">
                <div class="input-group input-group-outline">
	                <label class="form-label">Info</label>
                  <input type="text" id="tenantInfo" class="form-control" aria-label="Information">
                </div>
              </div>
              
             <div class="col d-flex justify-content-end">
               <div class="input-group input-group-outline">
	                <label class="form-label">TokenUrl</label>
                  <input type="text" id="tokenUrl" class="form-control" aria-label="TokenUrl">
                </div>
            </div>

            </div>

               <div class="row g-2 align-items-baseline pt-2">
              <div class="col">
              
              </div>
              
             <div class="col d-flex justify-content-end">
                <!-- Button Gruppe -->
                <div class="btn-group">
                    <button class="btn btn-icon btn-3 btn-outline-secondary" type="button" id="saveTenant">
                        <span class="btn-inner--icon"><i class="material-symbols-rounded">save</i></span>
                        <span class="btn-inner--text">Save</span>
                    </button>
                    <button class="btn btn-icon btn-3 btn-outline-danger" type="button" id="tenantCancel">
                        <span class="btn-inner--icon"><i class="material-symbols-rounded">close</i></span>
                        <span class="btn-inner--text vc-pointer">Cancel</span>
                    </button>
                </div>
            </div>

            </div>

                 
                </form>                
                
              </div>
              
              
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