<?php
include("auth.php");

$userId=$_SESSION['id'];

$user_id=$_REQUEST['user_id'];

if($user_id){
	
	$userId=$user_id;
	$param_add="&user_id=$userId";
}

// Variables for pagination and search
$search = isset($_GET['search']) ? $_GET['search'] : '';
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1); // Ensure the page number is at least 1
$start = ($page - 1) * $perPage;

// Escape the search term to prevent SQL injection
$searchWildcard = "%" . $conn->real_escape_string($search) . "%";

// Count total records for pagination
$countSql = "SELECT COUNT(*) as total FROM commands WHERE (command_name LIKE '$searchWildcard') AND user_id='$userId' order by id desc";
$countResult = $conn->query($countSql);

if (!$countResult) {
    die("Error executing count query: " . $conn->error);
}

$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $perPage);

// Ensure the page does not exceed the total pages
$page = min($page, $totalPages);

// Fetch data
$sql = "SELECT * FROM commands WHERE (command_name LIKE '$searchWildcard') AND user_id='$userId' order by id desc LIMIT $start, $perPage";
$result = $conn->query($sql);

if (!$result) {
    die("Error executing data query: " . $conn->error);
}


$commands=array();
$select_prompt = "SELECT * FROM commands WHERE user_id='$userId'";
$result_select = $conn->query($select_prompt);
if ($result_select->num_rows > 0) {
	
	while($row_select = $result_select->fetch_assoc()){
		
		$push_array=array("name"=>$row_select['command_name'],"description"=>$row_select['command_text']);
	    array_push($commands,$push_array);
	}
	
}

?>
<!DOCTYPE html>
<html lang="en" dir="ltr" data-bs-theme="light" data-color-theme="Blue_Theme" data-layout="vertical">

<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<?php include("head.php")?>
<style>

.description-container {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 400px; /* Adjust as necessary */
  display: inline-block;
}

.p-2.bg-info-subtle.text-dark.mb-1.d-inline-block.rounded-1.fs-3, .p-2.text-bg-light.rounded-1.d-inline-block.text-dark.fs-3{
    width: 564px;
}


loader {
    width: 30px;
    height: 30px;
    border: 5px solid #2e2828;
    border-bottom-color: #ff000000;
    border-radius: 50%;
    display: inline-block;
    box-sizing: border-box;
    animation: rotation 1s linear infinite;
}

.loader_process {
    width: 25px;
    height: 25px;
    border: 5px solid #2e2828;
    border-bottom-color: #ff000000;
    border-radius: 50%;
    display: inline-block;
    box-sizing: border-box;
    animation: rotation 1s linear infinite;
}

    @keyframes rotation {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
    } 


 .file-upload-wrapper {
        display: flex;
        align-items: center;
        background-color: #f1f1f1;
        border-radius: 5px;
        padding: 5px 10px;
        cursor: pointer;
        position: relative;
    }
    .file-upload-wrapper:hover {
        background-color: #e1e1e1;
    }
    .file-upload-input {
        display: none;
    }
    .file-upload-icon {
        color: #cc0000;
        font-size: 24px;
        margin-right: 10px;
    }
    .file-upload-filename {
        font-size: 14px;
    }
    .file-remove-btn {
        display: none;
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: none;
        cursor: pointer;
        font-size: 16px;
        color: #666;
    }
    .file-remove-btn:hover {
        color: #333;
    }
    .progress-bar {
        width: 0%;
        height: 4px;
        background-color: #4CAF50;
        margin-top: 5px;
        border-radius: 2px;
    }
    .progress-container {
        width: 100%;
        background-color: #ddd;
        border-radius: 2px;
    }

.text-area-chat-message-send{
  width: 100%;
    border: 1px solid #e2e2e2;
    border-radius: 6px;
    padding: 10px;
}


.typing {
	white-space: pre-wrap;
	overflow: hidden; 
	border-right: .15em solid orange;
	animation: typing 3.5s steps(40, end), blink-caret .75s step-end infinite;
	
}

@keyframes typing {
	from { width: 0 }
	to { width: 100% }
}

@keyframes blink-caret {
	from, to { border-color: transparent }
	50% { border-color: orange; }
}


.file-gallery {
            height: 200px;
            overflow-y: auto;
            display: flex;
	flex-direction: column;
	gap: 10px;
	padding: 10px;
	border: 1px solid #ccc;
}
.file-search {
	margin-bottom: 10px;
}
.file-items {
	display: flex;
	flex-wrap: wrap;
	gap: 10px;
	overflow-y: auto;
}
.file-item {
	cursor: pointer;
	padding: 10px;
	border: 1px solid transparent; /* transparent border for consistent sizing */
	display: flex;
	align-items: center;
	gap: 5px;
}
.file-item.selected {
	border-color: #007bff; /* Bootstrap primary color for selection */
	background-color: #e7f1ff;
}
.file-item:hover {
	background-color: #f8f9fa;
}
.hidden {
	display: none;
}

.search-and-select {
    display: flex;
    align-items: center;
    gap: 10px; /* Adjust the spacing between the search input and the button */
    margin-bottom: 10px; /* Space before the list starts */
}
#suggestions {
    border: 1px solid #ccc;
    padding: 5px;
    display: none; /* Initially hidden */
    position: absolute; /* Positioned relative to the textarea */
    background-color: white;
    cursor: pointer;
    width: 530px; /* Same width as textarea */
    max-height: 200px;
    overflow-y: auto; /* Makes div scrollable */
    box-shadow: 0 4px 6px rgba(0,0,0,0.1); /* Subtle shadow for depth */
    border-radius: 4px; /* Rounded corners */
    margin-top: 5px; /* Slight space between textarea and suggestions */
    box-sizing: border-box;
	z-index:99999
}

#suggestions div {
    padding: 8px 10px;
    border-bottom: 1px solid #eee; /* Light line between items */
}
#suggestions div:last-child {
    border-bottom: none; /* Remove bottom border for the last item */
}
#suggestions div:hover {
    background-color: #f9f9f9; /* Light grey background on hover */
}
.bold {
    font-weight: bold; /* Make command name bold */
}	

.btn-outline-secondary{
    --bs-btn-color: #8d8d8d;
    --bs-btn-border-color: #989898;
    --bs-btn-hover-bg: #989898;
    --bs-btn-hover-border-color: #989898;
    --bs-btn-active-bg: #989898;
    --bs-btn-active-border-color: #989898;
    --bs-btn-disabled-color: #989898;
    --bs-btn-disabled-border-color: #989898;
}



</Style>

<style>
  .chat-box .chat-box-inner {
    height: calc(100vh - 280px);
    overflow-y: auto;
  }

  @media (min-width: 992px) { .chat-box .chat-box-inner { height: calc(100vh - 210px);} }
</style>

<body>
  <!-- Preloader -->
  <div class="preloader">
    <img src="<?=$logo;?>" alt="loader" class="lds-ripple img-fluid" />
  </div>
  <div id="main-wrapper">
    <!-- Sidebar Start -->
    <aside class="left-sidebar with-vertical">
      <div><!-- ---------------------------------- -->
<!-- Start Vertical Layout Sidebar -->
<!-- ---------------------------------- -->
<?php  include("sidebar.php"); ?>

<!-- ---------------------------------- -->
<!-- Start Vertical Layout Sidebar -->
<!-- ---------------------------------- --></div>
    </aside>
    <!--  Sidebar End -->
    <div class="page-wrapper">
      <?php include("header.php")?>
      <!--  Header End -->

      <aside class="left-sidebar with-horizontal">
        <!-- Sidebar scroll-->
<div>

  <?php include("sidebar.php")?>
  <!-- End Sidebar navigation -->
</div>
<!-- End Sidebar scroll-->
      </aside>

      


      <div class="body-wrapper">
      
	  <div class="container-fluid">
     
          <div class="card overflow-hidden chat-application">
            <div class="d-flex align-items-center justify-content-between gap-6 m-3 d-lg-none">
              <button class="btn btn-primary d-flex" type="button" data-bs-toggle="offcanvas" data-bs-target="#chat-sidebar" aria-controls="chat-sidebar">
                <i class="ti ti-menu-2 fs-5"></i>
              </button>
              <form class="position-relative w-100">
                <input type="text" class="form-control search-chat py-2 ps-5" id="text-srh" placeholder="Search Contactt">
                <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
              </form>
            </div>
            <div class="d-flex">
             
              <div class="w-100 w-xs-100 chat-container">
                <div class="chat-box-inner-part ">
                  <div class="chat-not-selected  d-none">
                    <div class="d-flex align-items-center justify-content-center  p-5">
                      <div class="text-center">
                        <span class="text-primary">
                          <i class="ti ti-message-dots fs-10"></i>
                        </span>
                        <h6 class="mt-2">Open chat from the list</h6>
                      </div>
                    </div>
                  </div> 
                  <div class="chatting-box d-block">
                   
                    <div class="d-flex parent-chat-box app-chat-right">
                      <div class="chat-box w-xs-100">
                        <div class="chat-box-inner p-9 simplebar-scrollable-y" data-simplebar="init"><div class="simplebar-wrapper" style="margin: -20px;"><div class="simplebar-height-auto-observer-wrapper"><div class="simplebar-height-auto-observer"></div></div><div class="simplebar-mask"><div class="simplebar-offset" style="right: 0px; bottom: 0px;"><div class="simplebar-content-wrapper" tabindex="0" role="region" aria-label="scrollable content"><div class="simplebar-content" style="padding: 20px;">
                          <div class="chat-list chat active-chat" data-user-id="1"> 
						  </div>
                          
                        </div></div></div></div>
						
						<div class="simplebar-placeholder">
						</div>
						</div>
						<div class="simplebar-track simplebar-horizontal" style="visibility: hidden;">
						<div class="simplebar-scrollbar" style="width: 0px; transform: translate3d(0px, 0px, 0px); display: none;"></div>
						</div>
						<div class="simplebar-track simplebar-vertical" style="visibility: visible;">
						<div class="simplebar-scrollbar" style="height: 25px; display: block; transform: translate3d(0px, 0px, 0px);"></div>
						</div>
						</div>
						
					<!--	<div class="progress-container" style="display:none;">
                               <div class="progress-bar"></div>
                        </div>-->
						
                        <div class="file-upload-wrapper" style="display:none;">
							<label for="file-upload" class="file-upload-label">
								<i class="fas fa-file-pdf file-upload-icon"></i>
								<span class="file-upload-filename">No file chosen</span>
							</label>
							<button class="file-remove-btn">✕</button>
						</div>
						
						<div class="file-gallery hidden">
							<div class="search-and-select">
								<input type="text" id="file_search" placeholder="Search files..."  class="form-control file-search">
								<button id="selectButton" class="btn btn-primary selectHighlightedFile ">Select</button>
								<button class="btn btn-danger remoce_files ">✕</button>
							</div>
							<div class="file-items">
							
						<?php	 
						
						$select_prompt = "SELECT * FROM files WHERE user_id='$userId'";
						$result_select = $conn->query($select_prompt);
						if ($result_select->num_rows > 0) {
							
							while($row_select = $result_select->fetch_assoc()){
						
		                ?>
								<div class="file-item" data-id="<?=$row_select['id']?>"><i class="fas fa-file-pdf"></i> <?=$row_select['file_name']?></div>
						<?php
						  }
						}
						?>
							</div>
						</div>


		
                        <div class="px-9 py-6 border-top chat-send-message-footer">
                              <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2 text-area-chat-message-send">
                                  <a class="file_open position-relative nav-icon-hover z-index-5" href="javascript:void(0)">
                                  <i class="ti ti-paperclip text-dark bg-hover-primary fs-7"></i>
								  </a>
								 <input type="file" id="file_upload" accept="application/pdf"  class="file-upload-input" style="display:none;"/>
								 <input type="hidden" class="file_id">
								 
                              <textarea type="text" id="left_prompt" class="form-control message-type-box text-muted border-0 p-0 ms-2"
                                placeholder="Type a Message" fdprocessedid="0p3op"></textarea>
								 <div id="suggestions"  class="suggestions-box" style="display: none; position: absolute; background: rgba(255, 255, 255, 0.9); border: 1px solid #ccc; padding: 5px; z-index: 10; width: 100%;"></div>
								
                            </div>
                            <ul class="list-unstyledn mb-0 d-flex align-items-center">
							<li class="ms-2">
							<span class="add_loader"></span>
                            </li> 
							<li class="send_message_prompt">
                                <a class="text-dark px-2 fs-7 bg-hover-primary nav-icon-hover position-relative z-index-5"
                                  href="javascript:void(0)"><i class="ti ti-arrow-up"></i></a>
                              </li> 
                            </ul>
                          </div>
                        </div>
						
                      </div>
                    </div>

                  </div>
                </div>
              </div>
              <div class="offcanvas offcanvas-start user-chat-box chat-offcanvas" tabindex="-1" id="chat-sidebar" aria-labelledby="offcanvasExampleLabel">
                <div class="offcanvas-header">
                  <h5 class="offcanvas-title" id="offcanvasExampleLabel">
                    Chats
                  </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="px-4 pt-9 pb-6">
                  <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="d-flex align-items-center">
                      <div class="position-relative">
                        <img src="http://18.211.246.17/admin/public/assets/images/profile/user-1.jpg" alt="user1" width="54" height="54" class="rounded-circle">
                        <span class="position-absolute bottom-0 end-0 p-1 badge rounded-pill bg-success">
                          <span class="visually-hidden">New alerts</span>
                        </span>
                      </div>
                      <div class="ms-3">
                        <h6 class="fw-semibold mb-2">Markarn Doe</h6>
                        <p class="mb-0 fs-2">Designer</p>
                      </div>
                    </div>
                    <div class="dropdown">
                      <a class="text-dark fs-6 nav-icon-hover" href="javascript:void(0)" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="ti ti-dots-vertical"></i>
                      </a>
                      <ul class="dropdown-menu">
                        <li>
                          <a class="dropdown-item d-flex align-items-center gap-2 border-bottom" href="javascript:void(0)"><span><i class="ti ti-settings fs-4"></i></span>Setting</a>
                        </li>
                        <li>
                          <a class="dropdown-item d-flex align-items-center gap-2" href="javascript:void(0)"><span><i class="ti ti-help fs-4"></i></span>Help
                            and feadback</a>
                        </li>
                        <li>
                          <a class="dropdown-item d-flex align-items-center gap-2" href="javascript:void(0)"><span><i class="ti ti-layout-board-split fs-4"></i></span>Enable split View mode</a>
                        </li>
                        <li>
                          <a class="dropdown-item d-flex align-items-center gap-2 border-bottom" href="javascript:void(0)"><span><i class="ti ti-table-shortcut fs-4"></i></span>Keyboard
                            shortcut</a>
                        </li>
                        <li>
                          <a class="dropdown-item d-flex align-items-center gap-2" href="javascript:void(0)"><span><i class="ti ti-login fs-4"></i></span>Sign
                            Out</a>
                        </li>
                      </ul>
                    </div>
                  </div>
                  <form class="position-relative mb-4">
                    <input type="text" class="form-control search-chat py-2 ps-5" id="text-srh" placeholder="Search Contact">
                    <i class="ti ti-search position-absolute top-50 start-0 translate-middle-y fs-6 text-dark ms-3"></i>
                  </form>
                 <!-- <div class="dropdown">
                    <a class="text-muted fw-semibold d-flex align-items-center" href="javascript:void(0)" role="button"
                      data-bs-toggle="dropdown" aria-expanded="false">
                      Recent Chats<i class="ti ti-chevron-down ms-1 fs-5"></i>
                    </a>
                    <ul class="dropdown-menu">
                      <li>
                        <a class="dropdown-item" href="javascript:void(0)">Sort by time</a>
                      </li>
                      <li>
                        <a class="dropdown-item border-bottom" href="javascript:void(0)">Sort by Unread</a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="javascript:void(0)">Hide favourites</a>
                      </li>
                    </ul>
                  </div>-->
                </div>
                <div class="app-chat">
                  <ul class="chat-users mh-n100 simplebar-scrollable-y" data-simplebar="init"><div class="simplebar-wrapper" style="margin: 0px;"><div class="simplebar-height-auto-observer-wrapper"><div class="simplebar-height-auto-observer"></div></div><div class="simplebar-mask"><div class="simplebar-offset" style="right: 0px; bottom: 0px;"><div class="simplebar-content-wrapper" tabindex="0" role="region" aria-label="scrollable content" style="height: auto; overflow: hidden scroll;"><div class="simplebar-content" style="padding: 0px;">
                   
                    
                    <li>
                      <a href="javascript:void(0)" class="px-4 py-3 bg-hover-light-black d-flex align-items-start justify-content-between chat-user" id="chat_user_2" data-user-id="2">
                        <div class="d-flex align-items-center">
                          <span class="position-relative">
                            <img src="http://18.211.246.17/admin/public/assets/images/profile/user-3.jpg" alt="user-2" width="48" height="48" class="rounded-circle">
                            <span class="position-absolute bottom-0 end-0 p-1 badge rounded-pill bg-danger">
                              <span class="visually-hidden">New alerts</span>
                            </span>
                          </span>
                          <div class="ms-3 d-inline-block w-75">
                            <h6 class="mb-1 fw-semibold chat-title" data-username="James Anderson">
                              Bianca Anderson
                            </h6>
                            <span class="fs-3 text-truncate text-dark fw-semibold d-block">Nice looking dress
                              you...</span>
                          </div>
                        </div>
                        <p class="fs-2 mb-0 text-muted">30 mins</p>
                      </a>
                    </li>
                    <li>
                      <a href="javascript:void(0)" class="px-4 py-3 bg-hover-light-black d-flex align-items-start justify-content-between chat-user" id="chat_user_3" data-user-id="3">
                        <div class="d-flex align-items-center">
                          <span class="position-relative">
                            <img src="http://18.211.246.17/admin/public/assets/images/profile/user-4.jpg" alt="user-8" width="48" height="48" class="rounded-circle">
                            <span class="position-absolute bottom-0 end-0 p-1 badge rounded-pill bg-warning">
                              <span class="visually-hidden">New alerts</span>
                            </span>
                          </span>
                          <div class="ms-3 d-inline-block w-75">
                            <h6 class="mb-1 fw-semibold chat-title" data-username="James Anderson">
                              Andrew Johnson
                            </h6>
                            <span class="fs-3 text-truncate text-body-color d-block">Sent a photo</span>
                          </div>
                        </div>
                        <p class="fs-2 mb-0 text-muted">2 hrs</p>
                      </a>
                    </li>
                    <li>
                      <a href="javascript:void(0)" class="px-4 py-3 bg-hover-light-black d-flex align-items-start justify-content-between chat-user" id="chat_user_4" data-user-id="4">
                        <div class="d-flex align-items-center">
                          <span class="position-relative">
                            <img src="http://18.211.246.17/admin/public/assets/images/profile/user-5.jpg" alt="user-4" width="48" height="48" class="rounded-circle">
                            <span class="position-absolute bottom-0 end-0 p-1 badge rounded-pill bg-success">
                              <span class="visually-hidden">New alerts</span>
                            </span>
                          </span>
                          <div class="ms-3 d-inline-block w-75">
                            <h6 class="mb-1 fw-semibold chat-title" data-username="James Anderson">
                              Mark Strokes
                            </h6>
                            <span class="fs-3 text-truncate text-body-color d-block">Lorem ispusm text sud...</span>
                          </div>
                        </div>
                        <p class="fs-2 mb-0 text-muted">5 days</p>
                      </a>
                    </li>
                    <li>
                      <a href="javascript:void(0)" class="px-4 py-3 bg-hover-light-black d-flex align-items-start justify-content-between chat-user" id="chat_user_5" data-user-id="5">
                        <div class="d-flex align-items-center">
                          <span class="position-relative">
                            <img src="http://18.211.246.17/admin/public/assets/images/profile/user-6.jpg" alt="user1" width="48" height="48" class="rounded-circle">
                            <span class="position-absolute bottom-0 end-0 p-1 badge rounded-pill bg-success">
                              <span class="visually-hidden">New alerts</span>
                            </span>
                          </span>
                          <div class="ms-3 d-inline-block w-75">
                            <h6 class="mb-1 fw-semibold chat-title" data-username="James Anderson">
                              Mark, Stoinus &amp; Rishvi..
                            </h6>
                            <span class="fs-3 text-truncate text-dark fw-semibold d-block">Lorem ispusm text ...</span>
                          </div>
                        </div>
                        <p class="fs-2 mb-0 text-muted">5 days</p>
                      </a>
                    </li>
                    <li>
                      <a href="javascript:void(0)" class="px-4 py-3 bg-hover-light-black d-flex align-items-start justify-content-between chat-user" id="chat_user_2" data-user-id="2">
                        <div class="d-flex align-items-center">
                          <span class="position-relative">
                            <img src="http://18.211.246.17/admin/public/assets/images/profile/user-7.jpg" alt="user-2" width="48" height="48" class="rounded-circle">
                            <span class="position-absolute bottom-0 end-0 p-1 badge rounded-pill bg-danger">
                              <span class="visually-hidden">New alerts</span>
                            </span>
                          </span>
                          <div class="ms-3 d-inline-block w-75">
                            <h6 class="mb-1 fw-semibold chat-title" data-username="James Anderson">
                              Bianca Anderson
                            </h6>
                            <span class="fs-3 text-truncate text-dark fw-semibold d-block">Nice looking dress
                              you...</span>
                          </div>
                        </div>
                        <p class="fs-2 mb-0 text-muted">30 mins</p>
                      </a>
                    </li>
                    <li>
                      <a href="javascript:void(0)" class="px-4 py-3 bg-hover-light-black d-flex align-items-start justify-content-between chat-user" id="chat_user_3" data-user-id="3">
                        <div class="d-flex align-items-center">
                          <span class="position-relative">
                            <img src="http://18.211.246.17/admin/public/assets/images/profile/user-8.jpg" alt="user-8" width="48" height="48" class="rounded-circle">
                            <span class="position-absolute bottom-0 end-0 p-1 badge rounded-pill bg-warning">
                              <span class="visually-hidden">New alerts</span>
                            </span>
                          </span>
                          <div class="ms-3 d-inline-block w-75">
                            <h6 class="mb-1 fw-semibold chat-title" data-username="James Anderson">
                              Andrew Johnson
                            </h6>
                            <span class="fs-3 text-truncate text-body-color d-block">Sent a photo</span>
                          </div>
                        </div>
                        <p class="fs-2 mb-0 text-muted">2 hrs</p>
                      </a>
                    </li>
                  </div></div></div></div><div class="simplebar-placeholder" style="width: 330px; height: 480px;"></div></div><div class="simplebar-track simplebar-horizontal" style="visibility: hidden;"><div class="simplebar-scrollbar" style="width: 0px; display: none;"></div></div><div class="simplebar-track simplebar-vertical" style="visibility: visible;"><div class="simplebar-scrollbar" style="height: 25px; display: block; transform: translate3d(0px, 0px, 0px);"></div></div></ul>
				  
				   
				   
                </div>
              </div>
            </div>
          </div>
        </div>
	  
	  
      <script>
  function handleColorTheme(e) {
    $("html").attr("data-color-theme", e);
    $(e).prop("checked", !0);
  }
</script>
<!-- <button class="btn btn-info p-3 rounded-circle d-flex align-items-center justify-content-center customizer-btn"
  type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
  <iconify-icon icon="solar:settings-linear" class="fs-7"></iconify-icon>
</button> -->

<div class="offcanvas customizer offcanvas-end" tabindex="-1" id="offcanvasExample"
  aria-labelledby="offcanvasExampleLabel">
  <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
    <h4 class="offcanvas-title fw-semibold" id="offcanvasExampleLabel">
      Settings
    </h4>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body h-n80" data-simplebar>
    <h6 class="fw-semibold fs-4 mb-2">Theme</h6>

    <div class="d-flex flex-row gap-3 customizer-box" role="group">
      <input type="radio" class="btn-check light-layout" name="theme-layout" id="light-layout" autocomplete="off" />
      <label class="btn p-9 btn-outline-primary" for="light-layout">
        <iconify-icon icon="solar:sun-2-bold" class="icon fs-7 me-2"></iconify-icon>Light</label>

      <input type="radio" class="btn-check dark-layout" name="theme-layout" id="dark-layout" autocomplete="off" />
      <label class="btn p-9 btn-outline-primary" for="dark-layout"><iconify-icon icon="solar:moon-linear"
          class="icon fs-7 me-2"></iconify-icon>Dark</label>
    </div>

    <h6 class="mt-5 fw-semibold fs-4 mb-2">Theme Direction</h6>
    <div class="d-flex flex-row gap-3 customizer-box" role="group">
      <input type="radio" class="btn-check" name="direction-l" id="ltr-layout" autocomplete="off" />
      <label class="btn p-9 btn-outline-primary" for="ltr-layout">
        <iconify-icon icon="solar:align-left-linear" class="icon fs-7 me-2"></iconify-icon>LTR</label>

      <input type="radio" class="btn-check" name="direction-l" id="rtl-layout" autocomplete="off" />
      <label class="btn p-9 btn-outline-primary" for="rtl-layout"><iconify-icon icon="solar:align-right-linear"
          class="icon fs-7 me-2"></iconify-icon>RTL</label>
    </div>

    <h6 class="mt-5 fw-semibold fs-4 mb-2">Theme Colors</h6>

    <div class="d-flex flex-row flex-wrap gap-3 customizer-box color-pallete" role="group">
      <input type="radio" class="btn-check" name="color-theme-layout" id="Blue_Theme" autocomplete="off" />
      <label class="btn p-9 btn-outline-primary d-flex align-items-center justify-content-center"
        onclick="handleColorTheme('Blue_Theme')" for="Blue_Theme" data-bs-toggle="tooltip" data-bs-placement="top"
        data-bs-title="BLUE_THEME">
        <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-1">
          <iconify-icon icon="tabler:check" class="text-white d-flex icon fs-5"></iconify-icon>
        </div>
      </label>

      <input type="radio" class="btn-check" name="color-theme-layout" id="Aqua_Theme" autocomplete="off" />
      <label class="btn p-9 btn-outline-primary d-flex align-items-center justify-content-center"
        onclick="handleColorTheme('Aqua_Theme')" for="Aqua_Theme" data-bs-toggle="tooltip" data-bs-placement="top"
        data-bs-title="AQUA_THEME">
        <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-2">
          <iconify-icon icon="tabler:check" class="text-white d-flex icon fs-5"></iconify-icon>
        </div>
      </label>

      <input type="radio" class="btn-check" name="color-theme-layout" id="Purple_Theme" autocomplete="off" />
      <label class="btn p-9 btn-outline-primary d-flex align-items-center justify-content-center"
        onclick="handleColorTheme('Purple_Theme')" for="Purple_Theme" data-bs-toggle="tooltip" data-bs-placement="top"
        data-bs-title="PURPLE_THEME">
        <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-3">
          <iconify-icon icon="tabler:check" class="text-white d-flex icon fs-5"></iconify-icon>
        </div>
      </label>

      <input type="radio" class="btn-check" name="color-theme-layout" id="green-theme-layout" autocomplete="off" />
      <label class="btn p-9 btn-outline-primary d-flex align-items-center justify-content-center"
        onclick="handleColorTheme('Green_Theme')" for="green-theme-layout" data-bs-toggle="tooltip"
        data-bs-placement="top" data-bs-title="GREEN_THEME">
        <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-4">
          <iconify-icon icon="tabler:check" class="text-white d-flex icon fs-5"></iconify-icon>
        </div>
      </label>

      <input type="radio" class="btn-check" name="color-theme-layout" id="cyan-theme-layout" autocomplete="off" />
      <label class="btn p-9 btn-outline-primary d-flex align-items-center justify-content-center"
        onclick="handleColorTheme('Cyan_Theme')" for="cyan-theme-layout" data-bs-toggle="tooltip"
        data-bs-placement="top" data-bs-title="CYAN_THEME">
        <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-5">
          <iconify-icon icon="tabler:check" class="text-white d-flex icon fs-5"></iconify-icon>
        </div>
      </label>

      <input type="radio" class="btn-check" name="color-theme-layout" id="orange-theme-layout" autocomplete="off" />
      <label class="btn p-9 btn-outline-primary d-flex align-items-center justify-content-center"
        onclick="handleColorTheme('Orange_Theme')" for="orange-theme-layout" data-bs-toggle="tooltip"
        data-bs-placement="top" data-bs-title="ORANGE_THEME">
        <div class="color-box rounded-circle d-flex align-items-center justify-content-center skin-6">
          <iconify-icon icon="tabler:check" class="text-white d-flex icon fs-5"></iconify-icon>
        </div>
      </label>
    </div>

    <h6 class="mt-5 fw-semibold fs-4 mb-2">Layout Type</h6>
    <div class="d-flex flex-row gap-3 customizer-box" role="group">
      <div>
        <input type="radio" class="btn-check" name="page-layout" id="vertical-layout" autocomplete="off" />
        <label class="btn p-9 btn-outline-primary" for="vertical-layout"><iconify-icon
            icon="solar:sidebar-minimalistic-linear" class="icon fs-7 me-2"></iconify-icon>Vertical</label>
      </div>
      <div>
        <input type="radio" class="btn-check" name="page-layout" id="horizontal-layout" autocomplete="off" />
        <label class="btn p-9 btn-outline-primary" for="horizontal-layout"><iconify-icon
            icon="solar:airbuds-case-minimalistic-linear" class="icon fs-7 me-2"></iconify-icon>Horizontal</label>
      </div>
    </div>

    <h6 class="mt-5 fw-semibold fs-4 mb-2">Container Option</h6>

    <div class="d-flex flex-row gap-3 customizer-box" role="group">
      <input type="radio" class="btn-check" name="layout" id="boxed-layout" autocomplete="off" />
      <label class="btn p-9 btn-outline-primary" for="boxed-layout"><iconify-icon
          icon="solar:align-horizonta-spacing-linear" class="icon fs-7 me-2"></iconify-icon>Boxed</label>

      <input type="radio" class="btn-check" name="layout" id="full-layout" autocomplete="off" />
      <label class="btn p-9 btn-outline-primary" for="full-layout"><iconify-icon
          icon="solar:align-vertical-spacing-linear" class="icon fs-7 me-2"></iconify-icon>Full</label>
    </div>

    <h6 class="fw-semibold fs-4 mb-2 mt-5">Sidebar Type</h6>
    <div class="d-flex flex-row gap-3 customizer-box" role="group">
      <a href="javascript:void(0)" class="fullsidebar">
        <input type="radio" class="btn-check" name="sidebar-type" id="full-sidebar" autocomplete="off" />
        <label class="btn p-9 btn-outline-primary" for="full-sidebar"><iconify-icon icon="solar:mirror-left-linear"
            class="icon fs-7 me-2"></iconify-icon>Full</label>
      </a>
      <div>
        <input type="radio" class="btn-check " name="sidebar-type" id="mini-sidebar" autocomplete="off" />
        <label class="btn p-9 btn-outline-primary" for="mini-sidebar"><iconify-icon icon="solar:mirror-right-linear"
            class="icon fs-7 me-2"></iconify-icon>Collapse</label>
      </div>
    </div>

    <h6 class="mt-5 fw-semibold fs-4 mb-2">Card With</h6>

    <div class="d-flex flex-row gap-3 customizer-box" role="group">
      <input type="radio" class="btn-check" name="card-layout" id="card-with-border" autocomplete="off" />
      <label class="btn p-9 btn-outline-primary" for="card-with-border"><iconify-icon
          icon="solar:quit-full-screen-square-linear" class="icon fs-7 me-2"></iconify-icon>Border</label>

      <input type="radio" class="btn-check" name="card-layout" id="card-without-border" autocomplete="off" />
      <label class="btn p-9 btn-outline-primary" for="card-without-border"><iconify-icon
          icon="solar:minimize-square-2-linear" class="icon fs-7 me-2"></iconify-icon>Shadow</label>
    </div>
  </div>
</div>
    </div>

    

  </div>
  <div class="dark-transparent sidebartoggler"></div>
<?php include("footer.php")?>
</html>
<script src="https://cdn.jsdelivr.net/npm/showdown@1.9.1/dist/showdown.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const commands = <?=json_encode($commands);?>;
    const input = document.getElementById("left_prompt");
    const suggestions = document.getElementById("suggestions");

    function updateSuggestions(value) {
        const cursorPosition = input.selectionStart;
        const lastSegment = value.slice(0, cursorPosition).split(/\s+/).pop();

        if (lastSegment.startsWith("/")) {
            const filteredCommands = lastSegment.length === 1 ? 
                commands : // Show all commands if only "/" is typed
                commands.filter(cmd => cmd.name.includes(lastSegment.slice(1))); // Otherwise, filter by input after "/"
                
            suggestions.innerHTML = ''; // Clear existing suggestions
            if (filteredCommands.length > 0) {
                suggestions.style.display = "block";
                filteredCommands.forEach(function(command) {
                    const div = document.createElement("div");
                    div.innerHTML = `<span class="bold">${command.name}</span> - ${command.description}`;
                    div.onclick = function() {
                        replaceCommandWithDescription(lastSegment, command.description);
                    };
                    suggestions.appendChild(div);
                });
            } else {
                suggestions.style.display = "none";
            }

            // Adjust the position of the suggestions box
            positionSuggestions(cursorPosition);
        } else {
            suggestions.style.display = "none";
        }
    }

    function replaceCommandWithDescription(lastSegment, description) {
        const text = input.value;
        const upToCursor = text.substring(0, input.selectionStart);
        const startOfCommand = upToCursor.lastIndexOf(lastSegment);
        const beforeCommand = upToCursor.substring(0, startOfCommand);
        const afterCommand = text.substring(startOfCommand + lastSegment.length);
        input.value = beforeCommand + description + afterCommand;
        suggestions.style.display = "none";
        input.focus();
        const newPos = beforeCommand.length + description.length;
        input.setSelectionRange(newPos, newPos);
    }

   
    function positionSuggestions(cursorPosition) {
    const lineHeight = parseInt(window.getComputedStyle(input).lineHeight); // Get the line height of the textarea
    const rows = input.value.substring(0, cursorPosition).split('\n'); // Get all the rows before the cursor
    const currentLine = rows.length - 1; // Current line number (0-based)

    // Get the scroll position of the textarea
    const scrollTop = input.scrollTop;

    const textareaHeight = input.offsetHeight; // Get the total height of the textarea

    // Get the number of lines that are visible in the textarea
    const visibleLinesCount = Math.floor(textareaHeight / lineHeight);

    // Calculate the current line relative to the visible part
    let visibleLine = currentLine - Math.floor(scrollTop / lineHeight);

    // If the visible line goes beyond the available lines (e.g., in case of large scroll), clip it
    if (visibleLine < 0) {
        visibleLine = 0;
    } else if (visibleLine >= visibleLinesCount) {
        visibleLine = visibleLinesCount - 1;
    }

    // Calculate the top offset based on the visible row within the textarea's scrollable area
    let topOffset = visibleLine * lineHeight;

    let total_lines_in_textarea = Math.floor(textareaHeight / lineHeight)-1;
    // console.log("Total rows:", total_lines_in_textarea);

    let not_morethan_top_offset=total_lines_in_textarea*lineHeight;

    // not_morethan_top_offset in this case is 315

    // If top offset exceeds a certain limit (e.g., 315px), clip it
    if (topOffset >= not_morethan_top_offset) {
        topOffset = not_morethan_top_offset;
    }

    // Set the position of the suggestions box considering the scroll offset
    suggestions.style.top = `${topOffset + lineHeight + 10}px`; // Position below the current line
    suggestions.style.left = `${input.offsetLeft}px`; // Align with the textarea's left edge
    suggestions.style.width = `${input.offsetWidth}px`; // Match the width of the textarea

    // // Optionally, log the results for debugging purposes
    // console.log({
    //     lineHeight,
    //     cursorPosition,
    //     currentLine,
    //     scrollTop,
    //     visibleLine,
    //     topOffset
    // });
}

    input.addEventListener("input", function() {
        updateSuggestions(input.value);
    });

    input.addEventListener("blur", function() {
        // Hide suggestions when the textarea loses focus
        setTimeout(() => {
            suggestions.style.display = "none";
        }, 200);
    });
});
</script>

<script>

$(document).ready(function() {
	
	
	 $(document).on("keyup","#file_search",function(event) { 
	 
        const searchInput = document.getElementById('file_search').value.toLowerCase(); // Get the search input and convert to lowercase
        const fileItems = document.querySelectorAll('.file-item'); // Get all file-item elements

        fileItems.forEach(item => {
            const fileName = item.textContent.toLowerCase(); // Get the text content of the file-item and convert to lowercase
            if (fileName.includes(searchInput)) {
                item.style.display = ''; // Show the item if it matches
            } else {
                item.style.display = 'none'; // Hide the item if it doesn't match
            }
        });
    });
	
	
	function removeFile() {
		    $(".file_id").val(''); 
            $('.file-upload-input').val(''); 
			$('.file-upload-filename').text('No file chosen'); 
			$('.file-upload-icon').attr('class', 'fas fa-file-pdf file-upload-icon'); 
			$('.file-remove-btn').hide(); 
			$('.file-upload-wrapper').hide(); 
			$('.progress-container').hide(); 
			$('.progress-bar').css('width', '0%');
    }

   // $(document).on("click","#btn-add",function(event) {
		
	 $('#addContactModalTitle').submit(function(event) {	
	 
		event.preventDefault();
		
		var formData = $('#addContactModalTitle').serialize();

				$.ajax({
					url: 'ajax_command.php?action=add&user_id=<?=$userId?>',
					type: 'POST',
					data: formData,
					success: function(response) {
						
						alert(response);
						location.reload();

					}
				});
		
	});
	
    $(document).on("click",".file_open",function(event) { 
	     removeFile();
		 $('.file-item').removeClass('selected');
         $('.file-gallery').show();
		 window.scrollTo(0, document.body.scrollHeight);
    });
	
	$(document).on("click",".file-item",function(event) { 
	
			$('.file-item').removeClass('selected');
			$(this).addClass('selected');

    });
    
	 
	$(document).on("click",".selectHighlightedFile",function(event) { 
				const selectedFile = document.querySelector('.file-item.selected');
				if (selectedFile) {
					//alert("Selected file: " + selectedFile.textContent.trim());
					$('.file-upload-filename').text(selectedFile.textContent.trim());
					$('.file-gallery').hide();
					$('.file-upload-wrapper').show();
					$('.file-remove-btn').show();
					$("#file_upload").attr("disabled","disabled");
					var file_id=$('.file-item.selected').attr("data-id");
					$(".file_id").val(file_id);
					
				} else {
							alert("No file selected.");
				}
	});

	
	$(document).on("click",".copyToClipboard",function(event) {
		    var elementId=$(this).attr("data-id");
			var text = $("#"+elementId).text();
			console.log(text);
			navigator.clipboard.writeText(text).then(function() {
				alert('Text copied to clipboard');
			}).catch(function(err) {
				console.error('Failed to copy text: ', err);
			});
	});


	function getCurrentTime24Hrs() {
			let now = new Date();
			let hours = now.getHours();  
			let minutes = now.getMinutes(); 
			let formattedHours = hours.toString().padStart(2, '0');
			let formattedMinutes = minutes.toString().padStart(2, '0');

			return formattedHours + ':' + formattedMinutes;
    }   
    
	
   function typeText(text, element, speed = 20) {
    let i = 0;
    let isTag = false;
    let textContent = '';
    let typingTimeout; // Store timeout reference
    let stopTyping = false; // Flag to stop typing

    function typeWriter() {
        if (stopTyping) return; // Stop if the flag is set

        if (i < text.length) {
            if (text[i] === '<') isTag = true;
            if (text[i] === '>') {
                isTag = false;
                textContent += text[i++];
                $(element).html(textContent);
                typingTimeout = setTimeout(typeWriter, speed);
                return;
            }

            textContent += text[i++];
            if (!isTag) {
                $(element).html(textContent);
                typingTimeout = setTimeout(typeWriter, speed);
            } else {
                typingTimeout = setTimeout(typeWriter, 0);
            }
        }
    }

    // Attach a stop function to the element
    element.stopTyping = () => {
        stopTyping = true;
        clearTimeout(typingTimeout); // Clear any pending timeouts
    };

    typeWriter();
}

$(document).on("click", ".send_message_prompt", function(event) {
    var converter = new showdown.Converter({
        simplifiedAutoLink: true,
        strikethrough: true,
        tables: true,
        tasklists: true,
        simpleLineBreaks: true,
        openLinksInNewWindow: true
    });

    var id = "<?=uniqid().'-'.uniqid()?>";
    var send_content = $(".message-type-box").val();
    var file_id = $(".file_id").val();

    if (send_content) {
        $('.file-remove-btn').click();
        var time = getCurrentTime24Hrs();
        var send_content_ = send_content.replace(/\n/g, '<br>');

        if (file_id) {
            send_content_ = '<div><i class="fas fa-file-pdf"></i> Sample referral document.pdf </div><br>' + send_content_;
        } else {
            send_content_ = send_content_;
        }

        var message_from = `<div class="hstack gap-3 align-items-start mb-7 justify-content-end"><div class="text-end"><h6 class="fs-2 text-muted">${time}</h6><div class="p-2 bg-info-subtle text-dark mb-1 d-inline-block rounded-1 fs-3">${send_content_}</div></div></div>`;
        $(".chat-list").append(message_from);
        $(".add_loader").addClass("loader_process");
        $(".message-type-box").val('');
        $(this).attr("disabled", "disabled");

        $.ajax({
            url: 'send_message_chatgpt.php',
            type: 'POST',
            data: { id: id, send_content: send_content, file_id: file_id },
            success: function(response) {
                if (response != "404") {
                    var obj = JSON.parse(response);
                    var gpt_reply = converter.makeHtml(obj.response);
                    var time = getCurrentTime24Hrs();
                    var uniqueId = "typingText-" + new Date().getTime();

                    var message_to = `
                        <div class="hstack gap-3 align-items-start mb-7 justify-content-start">
                            <h6 class="fs-2 text-muted">${time}</h6>
                            <div class="p-2 text-bg-light rounded-1 d-inline-block text-dark fs-3" id="${uniqueId}"></div> 
                            <button class="btn copyToClipboard" data-id="${uniqueId}"><i class="fas fa-copy"></i></button>
                            <button class="btn stopTypingButton" data-id="${uniqueId}"><i class="fas fa-stop"></i></button>
                        </div>`;
                    $(".chat-list").append(message_to);

                    const element = $("#" + uniqueId)[0];
                    typeText(gpt_reply, element);

                    $(".right_prompt").val(gpt_reply);
                    $(".prompt-send").removeAttr("disabled");
                    $(".add_loader").removeClass("loader_process");
                } else {
                    alert("ChatGPT error");
                }
            }
        });
    } else {
        alert("Message should not be empty");
    }
});

// Stop writing functionality
$(document).on("click", ".stopTypingButton", function() {
    var uniqueId = $(this).data("id");
    var element = $("#" + uniqueId)[0];
    if (element && typeof element.stopTyping === "function") {
        element.stopTyping(); // Call the stop function attached to the element
    }
});


});		
</script>

<script>
$(document).ready(function () {
	
	$(document).on("click",".remoce_files",function(event) {
		
		$('.file-gallery').hide();
	});
	
	$(document).on("click",".file-remove-btn",function(event) {
		
            $('.file-upload-input').val(''); 
			$(".file_id").val(''); 
			$('.file-upload-filename').text('No file chosen'); 
			$('.file-upload-icon').attr('class', 'fas fa-file-pdf file-upload-icon'); 
			$('.file-remove-btn').hide(); 
			$('.file-upload-wrapper').hide(); 
			$('.progress-container').hide(); 
			$('.progress-bar').css('width', '0%');
    });
	
	
    $('#file_upload').change(function (e) {
        var file = e.target.files[0];
        if (file) {
            var formData = new FormData();
            formData.append('file', file);

            var fileName = file.name;
            $('.file-upload-filename').text(fileName);
            $('.file-remove-btn').show();
            
			 $('.file-upload-wrapper').show(); // Hide the remove button
             $('.progress-container').show(); // Hide the remove button
	
            $.ajax({
                url: 'upload.php', // Server script to process the upload
                type: 'POST',
                xhr: function () {
                    var myXhr = $.ajaxSettings.xhr();
                    if (myXhr.upload) {
                        // Update progress bar
                        myXhr.upload.addEventListener('progress', function (e) {
                            if (e.lengthComputable) {
                                var percentComplete = (e.loaded / e.total) * 100;
                                $('.progress-bar').css('width', percentComplete + '%');
                            }
                        }, false);
                    }
                    return myXhr;
                },
                success: function (data) {
                    alert('File uploaded successfully');
                },
                error: function () {
                    alert('Error uploading file');
                },
                data: formData,
                cache: false,
                contentType: false,
                processData: false
            });
        }
    });
});


</script>
