<?php
$login_required = TRUE;
$use_theme = 'Beta'; //TODO : Remove this when new UI is completely implemented.
include_once("web/includes/page.php");
require_once "api/Message/Message.php";
require_once "api/Tag/Tag.php";
require_once "api/ContentCollection/ContentCollection.php";
include_once "api/ModuleSetting/ModuleSetting.php";
include_once "api/Theme/Template.php";
include_once "api/Links/Links.php";
include_once "api/Validation/Validation.php";

$error_array = array();
if($_POST) {
  filter_all_post($_POST);
  $error_array = @explode(",", $_POST['messages']);  
}

if(!empty($_POST['link_categories'])) {
    $tmp_array = explode(':', $_POST['link_categories']);
    $_POST['category_id'] = $tmp_array[0];
}

if(!empty($_POST['btn_apply_name'])) {
    if($_POST['form_action'] == "update") {
          if(!empty($_POST['category_name'])) { // updating a category name.
              try {
                  $Links = new Links();
                  //$tmp_array = explode(':', $_POST['link_categories']);
                  $_POST['category_id'] = $tmp_array[0];
                  $param_array = array('category_name'=>$_POST['category_name'], 'category_id'=>$tmp_array[0], 'changed'=>time(), 'user_id'=> $_SESSION['user']['id']);                  
                  $Links->set_params($param_array);
                  $Links->update_category ();
                  $error_array[] = "Category updated successfully";
              } catch (PAException $e) {
                  $error_array[] = $e->message;
              }
          
          } else {
              $error_array[] = "Please select a category";
          }
                  
    } else {    // adding new category.
        if(empty($_POST['category_name'])) {
            $error_array[] = "Please enter a category name";
            
        } else if(!Validation::validate_alpha_numeric($_POST['category_name'], 1)) {
            $error_array[] = "Please enter a valid category name";
            
        } else if(!empty($_POST['category_name'])) {
            try {
                $Links = new Links();
                $param_array = array('user_id'=>$_SESSION['user']['id'], 'category_name'=> $_POST['category_name'], 'created'=> time(), 'changed'=> time());
                $Links->set_params ($param_array);
                $Links->save_category ();
                $error_array[] = "Category added successfully";
            } catch (PAException $e) {
                  $error_array[] = $e->message;
            }  
        
        }
    
    }
    
} else if(!empty($_POST['btn_save_link'])) {
      if(empty($_POST['link_categories'])) {
          $error_array[] = "Please select a category";
      }
      
      if(empty($_POST['title'])) {
          $error_array[] = "Please enter a title for the link";
      }
      
      if(empty($_POST['url'])) {
           $error_array[] = "Please enter the URL for the link";
      } 
      $_POST['url'] = validate_url ($_POST['url']); 
       if( !Validation::isValidURL($_POST['url']) ) {
        $error_array[] = "Please enter a valid URL for the link";
      }              
     
      if(count($error_array) == 0) {
         //$tmp_array = explode(':', $_POST['link_categories']);
         //$_POST['category_id'] = $tmp_array[0];
            try {
                  if($_POST['form_action'] == "update") {
                      $id_array = $_POST['link_id'];
                      $temp = explode(':', $id_array[0]);
                      $link_id = $temp[1];
                      
                      $param_array = array('user_id'=> $_SESSION['user']['id'], 'category_id'=> $tmp_array[0], 'title'=> $_POST['title'], 'url'=> $_POST['url'], 'changed'=> time(), 'link_id'=> $link_id);
                      $Links = new Links();
                      $Links->set_params ($param_array);  
                      $Links->update_link ();  
                      unset($_POST['title']);
                      unset($_POST['url']);
                      $error_array[] = 'Link updated successfully';
                  
                  } else {
                      $param_array = array('user_id'=> $_SESSION['user']['id'], 'category_id'=> $tmp_array[0], 'title'=> $_POST['title'], 'url'=> $_POST['url'], 'changed'=> time(), 'created'=> time());
                      //p($param_array);
                      $Links = new Links();
                      $Links->set_params ($param_array);  
                      $Links->save_link ();
                      unset($_POST['title']);
                      unset($_POST['url']);
                      $error_array[] = 'Link added successfully';
                      
                  } 
              
                
              
            } catch (PAException $e) {
                $error_array[] = $e->message;
            }         
        
        
              
      }             
} else if(!empty($_POST['form_action']) &&  $_POST['form_action'] ==
         "delete_links" && count($_POST['link_id']) > 0) {
    $link_id_array = $_POST['link_id'];
    for($counter = 0; $counter < count($link_id_array); $counter++) {
        $temp_array = explode(':', $link_id_array[$counter]);
        $link_ids[] = $temp_array[1];
    }
    
    $link_id_string = substr($link_id_string, 0, strlen($link_id_string) - 2);
    $Links = new Links();
    $param_array = array('user_id'=> $_SESSION['user']['id'], 'changed'=> time(), 'link_id'=> $link_ids);  
    $Links->set_params($param_array);
    try {
        $Links->delete_link();
        $error_array[] = "Links deleted successfully";
    } catch (PAException $e) {
        $error_array[] = $e->message;
    }
    
} else if(!empty($_POST['form_action']) && $_POST['form_action'] ==
         "delete_category" && !empty($_POST['link_categories'])) {
    $param_array = array('user_id'=> $_SESSION['user']['id'], 'category_id'=> $_POST['category_id'], 'changed'=> time());
    
    $Links = new Links();
    $Links->set_params ($param_array);
    try {
        $Links->delete_category();
        $error_array[] = "Category deleted successfully";
    } catch (PAException $e) {
        $error_array[] = $e->message;
    }
} 


//error displaying


function setup_module($column, $moduleName, $obj) {
    global $content_type, $users,$uid,$group_ids,$user;

    switch ($column) {    
    case 'middle':           
          	$obj->orientation = CENTER;
           $obj->get_link_categories ();
           $obj->uid = $uid;
    break;
    }
    $obj->mode = PUB;
}

$page = new PageRenderer("setup_module", PAGE_LINKS_MANAGEMENT, sprintf(__("%s - My Links - %s"), $login_user->get_name(), $network_info->name), "container_three_column.tpl", "header.tpl", PUB, HOMEPAGE, $network_info);

$page->add_header_html('<script type="text/javascript" language="javascript" src="'.$current_theme_path.'/javascript/links.js"></script>');
$updated_category_id = null;
if (!empty($_POST['updated_category_id'])) {
  $updated_category_id = $_POST['updated_category_id'];
}
$page->add_header_html('<script type="text/javascript" language="javascript">var cat_id = "'.$updated_category_id.'";</script>');

$message = NULL;
if (count($error_array) > 0) {
  for($counter = 0; $counter < count($error_array); $counter++) {
      $message .= $error_array[$counter]."<br>";
  }
}

uihelper_error_msg($message);
uihelper_set_user_heading($page);

echo $page->render();
?>