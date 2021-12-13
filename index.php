<?php 

//All Controllers
$controllers=array("https://1111111:8443", "https://22222222:8443", "https://33333333:8443", "https://44444444:8443");


$controlleruser="username";
$controllerpassword="password";

$logins = array(
  0 => array(
      'username' => 'admin',
      'password' => 'admin',
      'password' => 'admin' //or standard
  ),
  1 => array(
      'username' => 'test',
      'password' => 'test',
      'type' => 'standard' //or standard
  ),
);

######################################## Do Not edit below this line ###################################
session_start();
require_once 'include/Client.php';
$page = base64_decode($_GET['page']);
$all_mac_list="";
//creates master list
if(isset($_POST['loaddata'])){
  $page=$_POST['loaddata'];
  foreach ($controllers as $value) { 
    $unifi_connection = new UniFi_API\Client($controlleruser, $controllerpassword, $value, $site_id, $controllerversion,false);
    $set_debug_mode   = $unifi_connection->set_debug($debug);
    $loginresults     = $unifi_connection->login(); 
    $site_list = $unifi_connection->list_sites();
    $unifi_connection->logout();   
    foreach ($site_list as $site) { 
        $site = json_decode(json_encode($site), true);
        $unifi_connection = new UniFi_API\Client($controlleruser, $controllerpassword, $value, $site['name'], $controllerversion,false);
        $set_debug_mode   = $unifi_connection->set_debug($debug);
        $loginresults     = $unifi_connection->login(); 
        $current =  $unifi_connection->list_wlanconf();
        $currents .= implode(",".PHP_EOL,json_decode(json_encode($current), true)[0]['mac_filter_list']).",";
        $list =  $unifi_connection-> list_users();
        $mac_list = json_decode(json_encode($list), true);
        
        foreach ($mac_list as $mac) { 
            $all_mac_list .= $mac['mac']."|".$mac['hostname']."|".$site['desc'].",";
        }
        $unifi_connection->logout(); 
    }   
  }
}
$controllerurl=base64_decode($_GET['controller']);
$site_id=base64_decode($_GET['site']);

$unifi_connection = new UniFi_API\Client($controlleruser, $controllerpassword, $controllerurl, $site_id, $controllerversion,false);
$set_debug_mode   = $unifi_connection->set_debug($debug);
$loginresults     = $unifi_connection->login(); 

$current2 =  $unifi_connection->list_wlanconf();
$current = implode(",".PHP_EOL,json_decode(json_encode($current2), true)[0]['mac_filter_list']);
$wlan_id = json_decode(json_encode($current2), true)[0]['_id'];
$site_list = $unifi_connection->list_sites();

if(isset($_POST['updateSingle'])){
    $_POST['updateSingle'] = preg_replace('/([\r\n\t])/','', $_POST['updateSingle']);
    $macs = explode(",",trim($_POST['updateSingle']));
    $mac_filter_policy="allow";
    $mac_filter_enabled=true;
 
    $unifi_connection = new UniFi_API\Client($controlleruser, $controllerpassword, $controllerurl, $siste_id, $controllerversion,false);
    $set_debug_mode   = $unifi_connection->set_debug($debug);
    $loginresults     = $unifi_connection->login(); 
    $site_list = $unifi_connection->list_sites();
    $unifi_connection->logout(); 

    foreach ($site_list as $site) {         
        $site = trim(json_decode(json_encode($site), true)['name']);
        if(in_array($site,$_POST['checkboxes'])){
            $unifi_connection = new UniFi_API\Client($controlleruser, $controllerpassword, $controllerurl, $site, $controllerversion,false);
            $set_debug_mode   = $unifi_connection->set_debug($debug);
            $loginresults     = $unifi_connection->login(); 
            $current2 =  $unifi_connection->list_wlanconf();
            $wlan_id = trim(json_decode(json_encode($current2), true)[0]['_id']);
            $unifi_connection->set_wlan_mac_filter($wlan_id, $mac_filter_policy, $mac_filter_enabled, $macs);        
        }
    } 
    header("location: index.php?controller=".base64_encode($controllerurl)."&site=".base64_encode($site_id)."&page=".base64_encode("page1"));
}

if(isset($_POST['username'],$_POST['password'])){
  $username = $_POST['username'];
  $password=$_POST['password'];
  if(array_search($username, array_column($logins, 'username')) !== false) {
    if(array_search($username, array_column($logins, 'username')) !== false) {
     $id = array_search("test2", array_column($logins, 'username'));
      $_SESSION['type']=$logins[$id]['type'];
      $_SESSION['userid']=$username;
      header("location: index.php");
    }else{
      $error="Incorrect Username/Password";
    }
  }else{
    $error="Incorrect Username/Password";
  }
}
if(isset($_POST['updateAll'])){
    $_POST['updateAll'] = preg_replace('/([\r\n\t])/','', $_POST['updateAll']);
    $macs = explode(",",trim($_POST['updateAll']));
    $mac_filter_policy="allow";
    $mac_filter_enabled=true;
    
    foreach ($controllers as $value) { 
        $unifi_connection = new UniFi_API\Client($controlleruser, $controllerpassword, $value, $site_id, $controllerversion,false);
        $set_debug_mode   = $unifi_connection->set_debug($debug);
        $loginresults     = $unifi_connection->login(); 
        $site_list = $unifi_connection->list_sites();
        $unifi_connection->logout();   
        foreach ($site_list as $site) { 
            $site = json_decode(json_encode($site), true)['name'];
            $unifi_connection = new UniFi_API\Client($controlleruser, $controllerpassword, $value, $site, $controllerversion,false);
            $set_debug_mode   = $unifi_connection->set_debug($debug);
            $loginresults     = $unifi_connection->login(); 
            $current2 =  $unifi_connection->list_wlanconf();
            $wlan_id = json_decode(json_encode($current2), true)[0]['_id'];
            $unifi_connection->set_wlan_mac_filter($wlan_id, $mac_filter_policy, $mac_filter_enabled, $macs);
        }   
    }
    header("location: index.php?controller=".base64_encode($controllerurl)."&site=".base64_encode($site_id)."&page=".base64_encode("page1"));
}
if($_POST['type']=="logout"){
  $_SESSION['userid']="";
  session_destroy();
  header("location: index.php");
}
if($_POST['type']=="disableMAC"){
  foreach ($controllers as $value) { 
    $unifi_connection = new UniFi_API\Client($controlleruser, $controllerpassword, $value, $site_id, $controllerversion,false);
    $set_debug_mode   = $unifi_connection->set_debug($debug);
    $loginresults     = $unifi_connection->login(); 
    $site_list = $unifi_connection->list_sites();
    $unifi_connection->logout();   
    foreach ($site_list as $site) { 
        $site = json_decode(json_encode($site), true);
        $unifi_connection = new UniFi_API\Client($controlleruser, $controllerpassword, $value, $site['name'], $controllerversion,false);
        $set_debug_mode   = $unifi_connection->set_debug($debug);
        $loginresults     = $unifi_connection->login(); 
        $current2 =  $unifi_connection->list_wlanconf();
        $wlan_id = json_decode(json_encode($current2), true)[0]['_id'];
        $unifi_connection->set_wlan_mac_filter($wlan_id,"allow", false, array("11:11:11:11:11:11")); 
    }   
  }
}
$array = explode(",",trim(rtrim($currents),","));
$currents = implode(",".PHP_EOL,array_unique($array));

function array_to_csv_download($array, $filename = "export.csv", $delimiter=";") {
  // open raw memory as file so no temp files needed, you might run out of memory though
  $f = fopen('php://memory', 'w'); 
  // loop over the input array
  foreach ($array as $line) { 
      // generate csv lines from the inner arrays
      fputcsv($f, $line, $delimiter); 
  }
  // reset the file pointer to the start of the file
  fseek($f, 0);
  // tell the browser it's going to be a csv file
  header('Content-Type: text/csv');
  // tell the browser we want to save it instead of displaying it
  header('Content-Disposition: attachment; filename="'.$filename.'";');
  // make php send the generated csv lines to the browser
  fpassthru($f);
}
if(isset($_POST['downloadCSV'])){
    array_to_csv_download(explode(",",$currents),"MACifi List.csv");
    die(); 
}
?>
<!doctype html>
<html lang="en" class="h-100">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <title>MACifi Updater</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" integrity="sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
		<link href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css" rel="stylesheet">
		<script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
		<script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
		<script src="https://cdn.datatables.net/buttons/2.0.1/js/dataTables.buttons.min.js"></script>
		<link href="https://cdn.datatables.net/buttons/2.0.1/css/buttons.bootstrap4.min.css" rel="stylesheet">
		<script src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.html5.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
   <style>
      .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
      }
      @media (min-width: 768px) {
        .bd-placeholder-img-lg {
          font-size: 3.5rem;
        }
      }
      .dt-button{
        border:none;
        background:#212529;
        color:#fff;
        border-radius:3px;
        padding:5px;
      }
      .dt-button :hover{
        color:#0d6efd;
      }
      .downArrow{
        position: fixed;
        bottom: 85%;
        left: 10%;
      }
      .bounce {
        -moz-animation: bounce 3s infinite;
        -webkit-animation: bounce 3s infinite;
        animation: bounce 3s infinite;
      }
      @-moz-keyframes bounce {
        0%, 20%, 50%, 80%, 100% {
          -moz-transform: translateY(0);
          transform: translateY(0);
        }
        40% {
          -moz-transform: translateY(-30px);
          transform: translateY(-30px);
        }
        60% {
          -moz-transform: translateY(-15px);
          transform: translateY(-15px);
        }
      }
      @-webkit-keyframes bounce {
        0%, 20%, 50%, 80%, 100% {
          -webkit-transform: translateY(0);
          transform: translateY(0);
        }
        40% {
          -webkit-transform: translateY(-30px);
          transform: translateY(-30px);
        }
        60% {
          -webkit-transform: translateY(-15px);
          transform: translateY(-15px);
        }
      }
      @keyframes bounce {
        0%, 20%, 50%, 80%, 100% {
          -moz-transform: translateY(0);
          -ms-transform: translateY(0);
          -webkit-transform: translateY(0);
          transform: translateY(0);
        }
        40% {
          -moz-transform: translateY(-30px);
          -ms-transform: translateY(-30px);
          -webkit-transform: translateY(-30px);
          transform: translateY(-30px);
        }
        60% {
          -moz-transform: translateY(-15px);
          -ms-transform: translateY(-15px);
          -webkit-transform: translateY(-15px);
          transform: translateY(-15px);
        }
      }
    </style>
  </head>
  <body class="d-flex flex-column h-100">   
    <header>
      <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
        <div class="container-fluid">
          <a class="navbar-brand" href="index.php">MACifi Updater</a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav me-auto mb-2 mb-md-0">       
              <li class="nav-item ">
                <a class="nav-link  <?php if($page=="home"){ echo "active"; } ?>" id="nav0" style="cursor:pointer" aria-current="page" onclick="$('.nav-link').removeClass('active');$('#nav0').addClass('active');$('.page').hide();$('#home').show();">Home</a>
              </li>
              <?php if($_SESSION['userid']!=""){ ?>
                <li class="nav-item">
                  <a class="nav-link <?php if($page=="page1"){ echo "active"; } ?>" id="nav1" style="cursor:pointer" aria-current="page" onclick="$('.nav-link').removeClass('active');$('#nav1').addClass('active');$('.page').hide();$('#page1').show();">Changes per Site</a>
                </li>
              
                <li class="nav-item">
                  <a class="nav-link <?php if($page=="page2"){ echo "active"; } ?>" id="nav2" aria-current="page" <?php if($_SESSION['type']=="admin"){ ?>onclick="$('.nav-link').removeClass('active');$('#nav2').addClass('active');$('.page').hide();$('#page2').show();" style="cursor:pointer" <?php }else{ echo " title='You must be an admin to view this page' style='cursor:not-allowed;color:#696969'"; } ?>>Bulk Changes</a>
                </li>
               
                <li class="nav-item">
                  <a class="nav-link <?php if($page=="page3"){ echo "active"; } ?>" id="nav3" style="cursor:pointer" aria-current="page" onclick="$('.nav-link').removeClass('active');$('#nav3').addClass('active');$('.page').hide();$('#page3').show();">Current Clients</a>
                </li>    
            </ul>
            <form style="display:inline" method="post">
              <input type="hidden" value="logout" name="type">
              <button style="float:right" title="Username: <?php echo $_SESSION['userid']; ?>" type="submit" class="btn btn-danger btn-sm" >Logout <i class="fas fa-sign-out-alt"></i></button>
            </form>
            <?php } ?>
          </div>
        </div>
      </nav>
    </header>
    <main style="margin-top:30px;background:#f3f3f3;min-height:100%" class="flex-shrink-0">   
        <div style="padding:50px" class="srow">
          <div id="home" class="page" >
          <center>
            <?php if($_SESSION['userid']!=""){ ?>
              <h5 class="downArrow bounce" style="margin-top:100px"> <i class="fas fa-arrow-up"></i><br>Select a page to get started</h5>
            <?php } ?>
              <?php if($_SESSION['userid']==""){ ?>
                <div style="margin-top:200px;width:400px">
                  <h3>Login</h3>
                  <p>You must first login to view and change content</p>
                  <p class="bounce" style="color:maroon"><?php echo $error; ?></p>
                  <hr>
                  <form method="post">
                    <div class="form-group">
                      <label style="float:left" for="usr">Username:</label>
                      <input autofocus value="<?php echo $_POST['username']; ?>" required name="username" type="text" class="form-control" id="usr">
                    </div>
                    <div style="margin-top:20px;" class="form-group">
                      <label style="float:left" for="pwd">Password:</label>
                      <input required name="password" type="password" class="form-control" id="pwd">
                    </div>
                    <div class="form-group">
                      <button style="margin-top:20px;float:right" type="submit" class="btn btn-secondary">Login</button>
                    </div>
                  </form>
               </div>

              <?php } ?>
          </center>
        </div>
        <?php if($_SESSION['userid']!=""){ ?>
        <form method="post" style="display:inline">
        <div id="page1" class="page row shadow p-3 mb-5 bg-white rounded" style="color:#333;;padding:20px;display:none">
          <h3>Changes per Site</h3> <hr> 
              <div class="col-xs-12 col-sm-12 col-md-2 col-lg-2" style="margin-top:50px;padding-bottom:20px;padding-top:0px;">
                  <div class="panel">
                      <div class="panel-heading">
                      <span class="panel-title"><h4>Controller List</h4></span>
                      </div>
                      <div class="panel-body pb5">              
                      <ul class="list-group">
                          <?php foreach ($controllers as $value) { 
                              if($controllerurl==$value){
                                  $style="background:#333;color:#fff";
                              }else{
                                  $style="";
                              } 
                          ?>
                              <li style="<?php echo $style; ?>" class="list-group-item">
                                  <a style="text-decoration:none;<?php echo $style; ?>" href="index.php?controller=<?php echo base64_encode($value); echo "&page=".base64_encode("page1"); ?>"><?php echo $value; ?></a>
                              </li>
                          <?php } ?>
                      </ul>
                      </div>
                  </div>
                  <div style="margin-top:50px" class="panel">
                      <div class="panel-heading">
                        <span class="panel-title">
                          <h4>Site List</h4>
                        </span>
                      </div>
                      <div class="panel-body pb5">              
                        <ul class="list-group">
                            <?php 
                                $count=0;
                                foreach ($site_list as $value) { 
                                $count++; 
                                $site = json_decode(json_encode($value), true);
                                if($site_id==$site['name']){
                                    $style="background:#333;color:#fff";
                                    
                                }else{
                                    $style="";
                                }         
                            ?>
                                <li style="<?php echo $style; ?>" class="list-group-item">
                                    <input <?php if($site_id==$site['name']){ echo " checked "; } ?>type="checkbox" value="<?php echo $site['name']; ?>" name="checkboxes[]">&nbsp;
                                    <a style="text-decoration:none;<?php echo $style; ?>" href="index.php?controller=<?php echo base64_encode($controllerurl);  echo "&page=".base64_encode("page1"); ?>&site=<?php echo base64_encode($site['name']); ?>"><?php echo ucwords($site['desc']); ?></a>
                                </li>
                            <?php } 
                            if($count==0){ echo '<li class="list-group-item">No sites</li>'; }
                            ?>
                        </ul>
                      </div>
                  </div>
              </div>  
              <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 mx-auto" style="padding-bottom:20px;padding-top:0px;">
              <?php if($controllerurl=="" or $site_id==""){ echo "<h6 class='bounce' style='margin-top:100px'><center>Please select a controller and a site to view whitelisted MAC addresses</center></h6>"; }else{ ?>
                  <h4 class="mt-5">Current Whitelisted MAC Addresses</h4>
                  <p>This will only update the selected sites on the selected controller</p>
                      <input type="hidden" name="wlanid" value="<?php echo $wlan_id; ?>">
                      <textarea name="updateSingle" style="height:300px" class="form-control"><?php Print_r($current); ?></textarea>
                      <button class="btn btn-primary btn-sm" type="submit" style="float:right;margin-top:20px;margin-left:5px" ><i class="fas fa-save"></i> Update Selected Site(s)</button>
                  <?php } ?>
              </div>
        </div>  
      </form>
      <?php if($_SESSION['type']=="admin"){ ?>
      <div id="page2" class="page row shadow p-3 mb-5 bg-white rounded" style="display:none">
          <div style="color:#333;;padding:20px"> 
            <h3>Bulk Changes
            <form style="display:inline" method="post">
              <input type="hidden" value="page2" name="loaddata">
              <button style="float:right" title="This may take a while to load" type="submit" class="btn btn-success btn-sm" >Reload Data</button>
            </form>
            </h3><hr>
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 mx-auto" style="padding:20px;">
                <h3 class="mt-5">Master MAC Whitelist </h3>
                <p>Each whitelists from each controller combined into one master. WIth this, you can update every controller at once.</p>
                <form method="post">
                    <textarea style="height:300px" name="updateAll" class="form-control"><?php echo rtrim(rtrim(trim($currents),","),","); ?></textarea>
                    <button class="btn btn-danger btn-sm" type="submit" style="float:right;margin-top:20px;margin-left:5px" ><i class="fas fa-save"></i> Update All Controllers</button>
                </form>  
                <form method="Post">
                    <input type="hidden" value="true" name="downloadCSV">
                    <button class="btn btn-secondary btn-sm" type="submit" style="float:right;margin-top:20px;margin-left:5px" ><i class="fas fa-file-download"></i> Download CSV</button>
                </form>
            </div>
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 mx-auto" style="padding:20px;">
                <h3 class="mt-5">Disable MAC Address Filtering </h3>
                <p>To improve the security of your Wi-Fi network, consider using MAC address filtering to prevent devices from authenticating with your APs. Otherwise, you can disable it on all Sites and Controllers.</p>

                <form method="post">
                    <input type="hidden" value="disableMAC" name="type">
                    <button class="btn btn-danger btn-sm" type="submit" style="float:right;margin-top:20px;margin-left:5px" ><i class="fas fa-stop"></i> Disable MAC Filtering</button>
                </form>
            </div>
        </div>
      </div>
      <?php } ?>
      <div id="page3" class="page row shadow p-3 mb-5 bg-white rounded" style="display:none">
          <div style="color:#333;;padding:20px"> 
            <h3>Current Online Clients
            <form style="display:inline" method="post">
              <input type="hidden" value="page3" name="loaddata">
              <button style="float:right"  type="submit" title="This may take a while to load" class="btn btn-success btn-sm" >Reload Data</button>
            </form>
            </h3>
            <hr>
            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9 mx-auto" style="padding:20px;">
                <h3 class="mt-5">MAC's From Currently Online Clients</h3>
                <p>Each online Clients MAC from each controller combined into one master. With this, you can get all the MAC Addresses at once.</p>
                <table class="table table-striped table-hover" id="myTable">
                  <thead>
                    <tr>
                        <th>Hostname</th>
                        <th>Site</th>
                        <th>MAC Address</th>    
                    </tr>
                  </thead>
                  <tbody>
                    <?php  
                    $count=0;
                    $macs = explode(",",rtrim($all_mac_list,","));
                    foreach ($macs as $value) { 
                      $value = explode("|",$value);
                      if($value[0]==""){
                        continue;
                      }           
                      $count++;
                      if($value[1]==""){
                        $value[1]="unknown";
                      }
                    ?>
                    <tr>
                        <td><?php echo $value[1]; ?></td>
                        <td><?php echo $value[2]; ?></td>
                        <td><?php echo $value[0]; ?></td>
                    </tr>
                    <?php } 
                      if($count==0){
                        echo " <tr><td colspan='3'>No Online Devices</td></tr>";
                      }
                    ?>
                  </tbody>
              </table>         
            </div>
        </div>
      </div>
      <?php } ?>
    </main>
    <footer class="footer mt-auto py-3 bg-light">
      <div class="container">
        <span class="text-muted">Created By Brandon Sanders 2021</span>
      </div>
    </footer>
</body>
<script>
$('#myTable').DataTable( {
	responsive: true,
	"lengthMenu": [[50, 100, 500, -1], [50, 100, 500, "All"]],
	colReorder: true,
	dom: 'Bfrtip',
	buttons: ['copy',
		{
			extend: 'excelHtml5',
			title: 'MACifi MAC List',
			text:'Export to Excel'
		},{
			extend: 'pdfHtml5',
			title: 'MACifi MAC List',
			text: 'Export to PDF'
		}
	]
} );	
</script>
</html>
<?php
    $unifi_connection->logout(); 
    if($_SESSION['userid']==""){ 
      echo "<script>$('.page').hide();$('#home').show();</script>";
    }else{
      if($page!=""){
          echo "<script>$('.page').hide();$('#".$page."').show();</script>";
      }
    }
 ?>