<?php 

    

    $menu["default"][0]["icon"] = "table_view";
    $menu["default"][0]["text"] = "Artifacts";
    $menu["default"][0]["page"] = "artifacts.php";

    $menu["default"][1]["icon"] = "dashboard";
    $menu["default"][1]["text"] = "Compare";
    $menu["default"][1]["page"] = "index.php";
    
    $menu["admin"][0]["icon"] = "settings";
    $menu["admin"][0]["text"] = "Settings";
    $menu["admin"][0]["page"] = "settings.php";
    
    $menu["admin"][1]["icon"] = "update";
    $menu["admin"][1]["text"] = "Task Executor";
    $menu["admin"][1]["page"] = "tasks.php";
    
    //$menu["admin"][2]["icon"] = "note_stack";
    //$menu["admin"][2]["text"] = "Logs";
    //$menu["admin"][2]["page"] = "logs.php";
    
    
    $currentPage = basename($_SERVER['REQUEST_URI']);
    
   
?>

 <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-radius-lg fixed-start ms-2  bg-white my-2" id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-dark opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand px-4 py-3 m-0" href="/">
        <img src="/assets/img/vcompare-icon.png" class="navbar-brand-img" width="26" height="26" alt="main_logo">
        <span class="ms-1 text-sm text-dark">vCompare Nexus</span>
      </a>
    </div>
    <hr class="horizontal dark mt-0 mb-2">
    <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
      <ul class="navbar-nav">
      
      	<?php foreach($menu["default"] as $entry) { 
      	
      	$isActive = ($entry["page"] === $currentPage) ? 'active bg-gradient-dark text-white' : 'nav-link text-dark';?>
      	
        <li class="nav-item">
          <a class="nav-link <?php echo $isActive; ?>" href="/<?php echo $entry["page"]; ?>">
            <i class="material-symbols-rounded opacity-5"><?php echo $entry["icon"]; ?></i>
            <span class="nav-link-text ms-1"><?php echo $entry["text"]; ?></span>
          </a>
        </li>
        <?php } ?>
        
        
        <li class="nav-item mt-3">
          <h6 class="ps-4 ms-2 text-uppercase text-xs text-dark font-weight-bolder opacity-5">Administration</h6>
        </li>
       
             	<?php foreach($menu["admin"] as $entry) { 
             	    $isActive = ($entry["page"] === $currentPage) ? 'active bg-gradient-dark text-white' : 'nav-link text-dark';?>
      
        <li class="nav-item">
          <a class="nav-link <?php echo $isActive; ?>" href="/<?php echo $entry["page"]; ?>">
            <i class="material-symbols-rounded opacity-5"><?php echo $entry["icon"]; ?></i>
            <span class="nav-link-text ms-1"><?php echo $entry["text"]; ?></span>
          </a>
        </li>
        
        <?php } ?>
        
      </ul>
    </div>
    <div class="sidenav-footer position-absolute w-100 bottom-0 ">
      <div class="mx-3">
        <a class="btn btn-outline-dark mt-4 w-100" target="_blank" href="https://github.com/stefanzimara/vIntegrationTools/wiki" type="button">Documentation</a>
      </div>
    </div>
  </aside>
  
  
