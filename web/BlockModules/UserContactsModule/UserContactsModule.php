<?php
error_reporting(E_ALL);

require_once "api/User/UserContact.class.php";
require_once "web/includes/classes/xhtmlTagHelper.class.php";
require_once "web/includes/classes/CSVParser.class.php";
include_once( "api/ProfileIO/map/CSVDataMapper.class.php");
require_once "api/Messaging/MessageDispatcher.class.php";

class UserContactsModule extends Module {

  public $module_type = 'user';
  public $module_placement = 'middle';
  private $contacts;
  private $type;
  private $types = array('import', 'plaxo', 'mslive', 'linkedin', 'outlook');
  private $valid_cvs_mime_types = array('application/octet-stream', 'text/csv', 'text/plain', 'application/vnd.ms-excel');

  function __construct() {
    $this->title = __('Contacts');
    $this->outer_template =  "outer_public_center_edit_profile_module.tpl";
    $this->contacts = array();
  }

  function initializeModule($request_method, $request_data) {
      $this->type = (!empty($request_data['stype'])) ? $request_data['stype'] : 'import';
      $invite_message = (!empty($request_data['message'])) ? $request_data['message'] : CUSTOM_INVITATION_MESSAGE;

      switch($this->type) {
        case 'import':
          if(!empty($request_data['import_type'])) {
             switch($request_data['import_type']) {
               case 'plaxo':
               case 'mslive':
               case 'linkedin':
               case 'outlook':
                 $this->prepareImportContacts($request_data);
               break;

               default:
            }
          }
        break;

        case 'plaxo':
          $this->contacts = $this->getUserContacts((int)PA::$login_uid, 'plaxo');
        break;

        case 'mslive':
          $this->contacts = $this->getUserContacts((int)PA::$login_uid, 'mslive');

        break;

        case 'linkedin':
          $this->contacts = $this->getUserContacts((int)PA::$login_uid, 'linkedin');

        break;

        case 'outlook':
          $this->contacts = $this->getUserContacts((int)PA::$login_uid, 'outlook');

        break;

        default:
      }

      $this->set_inner_template("contacts_main.tpl");

      if(($request_method != 'POST') && ($request_method != 'AJAX')) {
        $this->inner_HTML = $this->generate_inner_html(array('type'  => 'contacts',
                                                             'stype' => $this->type,
                                                             'message' => $invite_message,
                                                             'active_tab' => 1 + array_search($this->type, $this->types),
                                                             'contacts' => $this->contacts
                            ));
     }
  }


  function handleRequest($request_method, $request_data) {
    if(!empty($request_data['action'])) {
      $action = $request_data['action'];
      $class_name = get_class($this);
      switch($request_method) {
        case 'POST':
          $method_name = 'handlePOST_'. $action;
          if(method_exists($this, $method_name)) {
             $this->{$method_name}($request_data);
          } else {
             throw new Exception("$class_name error: Unhandled POST action - \"$action\" in request." );
          }
        break;
        case 'GET':
          $method_name = 'handleGET_'. $action;
          if(method_exists($this, $method_name)) {
             $this->{$method_name}($request_data);
          } else {
             throw new Exception("$class_name error: Unhandled GET action - \"$action\" in request." );
          }
        break;
        case 'AJAX':
          $method_name = 'handleAJAX_'. $action;
          if(method_exists($this, $method_name)) {
             $this->{$method_name}($request_data);
          } else {
             throw new Exception("$class_name error: Unhandled AJAX action - \"$action\" in request." );
          }
        break;
      }
    }
  }

  private function handlePOST_import_contacts($request_data) {
//    echo "<pre>" . print_r($request_data, 1) . "</pre>";
//    echo "<pre>" . print_r($this->contacts, 1) . "</pre>";
    $msg = null;
    $nb_imported = 0;
    try {
      foreach($this->contacts as $contact) {
         $params = array("user_id"       => PA::$login_uid,
                         "contact_name"  => $contact['name'],
                         "contact_email" => $contact['email'],
                         "contact_extra" => serialize($contact['profile']),
                         "contact_type"  => $request_data['import_type']
                   );
         UserContact::insertUserContact($params);
         $nb_imported++;
//         echo "<pre>" . print_r($params, 1) . "</pre>";
      }
      $msg = $nb_imported . __(' contact(s) sucessfully imported');
    } catch(Exception $e) {
      $msg = $e->getMessage();
    }

    $redirect_url =  PA::$url.PA_ROUTE_USER_CONTACTS . "?type=contacts&stype=" . $request_data['import_type'] . "&msg=" . urlencode($msg);
    $this->controller->redirect($redirect_url);
/*
    $this->inner_HTML = $this->generate_inner_html(array('type'  => 'contacts',
                                                         'stype' => $this->type,
                                                         'active_tab' => 1 + array_search($this->type, $this->types)
                        ));
*/
  }

  private function handlePOST_importLinkedInCSV($request_data) {
//    echo "<pre>" . print_r($request_data, 1) . "</pre>";
//    echo "<pre>" . print_r($_FILES, 1) . "</pre>";
      $error = '';
      $html = '';

      if(!empty($_FILES['linkedin_csv']['name']) && is_uploaded_file($_FILES['linkedin_csv']['tmp_name'])) {
        $ext = strtolower(end(explode('.', $_FILES['linkedin_csv']['name'])));
        if(!in_array($_FILES['linkedin_csv']['type'], $this->valid_cvs_mime_types) || ($ext != 'csv')) {
//            $html = htmlspecialchars("<pre>" . print_r($_FILES, 1) . "</pre>");
          $error = __('Invalid file type. Please select a valid LinkedIn CSV file. ');
        } else {
          try {
            $content = file_get_contents($_FILES['linkedin_csv']['tmp_name']);
            $csv_parser = new CSVParser($content);
            $contacts = $csv_parser->getCSVContacts('CSVDataMapper', true);
            if(!$contacts) $error = $csv_parser->lastError;
            $this->deleteWithoutEmail($contacts, $csv_parser->mapped_contacts);
            $templ = PA::$blockmodule_path .'/'. get_class($this) . "/linkedin_list.tpl";
            $html_gen = & new Template($templ);
            $html_gen->set('contacts', $contacts);
            $html_gen->set('mapped_contacts', $csv_parser->mapped_contacts);
            $html = $html_gen->fetch();
          } catch (Exception $e) {
            $error = $e->getMessage();
          }
        }
      } else {
          $error = __('Please, select a valid LinkedIn CSV file.');
      }

      echo "{";
      echo                "error: '" . $error . "',\n";
      echo                "content: '" . base64_encode(htmlspecialchars($html)) . "'\n";
      echo "}";
      exit;
  }

  private function handlePOST_importOutlookCSV($request_data) {
//    echo "<pre>" . print_r($request_data, 1) . "</pre>";
//    echo "<pre>" . print_r($_FILES, 1) . "</pre>";
      $error = '';
      $html = '';

      if(!empty($_FILES['outlook_csv']['name']) && is_uploaded_file($_FILES['outlook_csv']['tmp_name'])) {
        $ext = strtolower(end(explode('.', $_FILES['outlook_csv']['name'])));
        if(!in_array($_FILES['outlook_csv']['type'], $this->valid_cvs_mime_types) || ($ext != 'csv')) {
//            $html = htmlspecialchars("<pre>" . print_r($_FILES, 1) . "</pre>");
          $error = __('Invalid file type. Please select a valid Outlook CSV file. ');
        } else {
          try {
            $content = file_get_contents($_FILES['outlook_csv']['tmp_name']);
            $csv_parser = new CSVParser($content);
            $contacts = $csv_parser->getCSVContacts('CSVDataMapper', true);
            if(!$contacts) $error = $csv_parser->lastError;
            $this->deleteWithoutEmail($contacts, $csv_parser->mapped_contacts);
            $templ = PA::$blockmodule_path .'/'. get_class($this) . "/outlook_list.tpl";
            $html_gen = & new Template($templ);
            $html_gen->set('contacts', $contacts);
            $html_gen->set('mapped_contacts', $csv_parser->mapped_contacts);
            $html = $html_gen->fetch();
          } catch (Exception $e) {
            $error = $e->getMessage();
          }
        }
      } else {
          $error = __('Please, select a valid Outlook CSV file.');
      }
/*
      echo "Error: $error <br />";
      echo "Contacts: <pre>" . print_r($contacts, 1) . "</pre>";
      echo "Mapped Contacts: <pre>" . print_r($csv_parser->mapped_contacts, 1) . "</pre>";

*/
      echo "{";
      echo                "error: '" . $error . "',\n";
      echo                "content: '" . base64_encode(htmlspecialchars($html)) . "'\n";
      echo "}";
      exit;

  }

  private function deleteWithoutEmail(&$contacts_list, &$mapped_contacts) {
     for($cnt = 0; $cnt < count($contacts_list); $cnt++) {
       if($contacts_list[$cnt]['email'] == "-no email address-") {
         unset($contacts_list[$cnt]);
         unset($mapped_contacts[$cnt]);
       }
     }
  }

  private function handlePOST_inviteSelected($request_data) {
    $message = trim($request_data['message']);
    if(empty($message)) {
      $msg = __("Invite message can't be empty.");
    } else {
      $msg = null;
      $message = nl2br($message);
      $selected_contacts = (!empty($request_data['invite_selected'])) ? $request_data['invite_selected'] : null;
      if($selected_contacts) {
        foreach($selected_contacts as $key => $cntct_id) {
          $contact = $this->contacts[$key];   // selected index = contacts index
          $inv = new Invitation();
          $inv->user_id =  PA::$login_uid;
          $inv->username = PA::$login_user->login_name;
          $user = PA::$login_user;

          // for invitation not for any group invitation collection id is -1
          $inv->inv_collection_id = -1;
          $inv->inv_status = INVITATION_PENDING;
          $auth_token = get_invitation_token(LONG_EXPIRES, $contact['email']);
          $token = '&amp;token='.$auth_token;
          $link_desc = wordwrap(PA::$url . "/register.php?InvID=$inv->inv_id", 120, "<br>", 1);
          $inv->register_url = "<a href=\"". PA::$url . "/register.php?InvID=$inv->inv_id\">$link_desc</a>";

          $acc_link_desc = wordwrap(PA::$url . "/login.php?action=accept&InvID=$inv->inv_id$token", 120, "<br>", 1);
          $inv->accept_url = "<a href=\"". PA::$url . "/login.php?action=accept&InvID=$inv->inv_id$token\">$acc_link_desc</a>";

          $inv->inv_user_id = NULL;
          $inv->inv_user_first_name = $contact['name'];
          $inv->inv_email = $contact['email'];
          $inv->inv_summary = "Invitation from $user->first_name $user->last_name to join ".PA::$site_name;
          if($message != CUSTOM_INVITATION_MESSAGE){
            $inv->inv_message = !empty($message) ? $message : null;
          }

          try {
            $inv->send();
          }
          catch (PAException $e) {
            $msg = "$e->message";
            $save_error = true;
          }

          if(isset($save_error) && ($save_error == true)) {
            $msg = "Sorry: you are unable to invite a friend. <br /> Reason: " . $msg;
          } else {
            // invitation has been sent, now send mail

/*    - Replaced with new PANotify code

            $invitee_image = uihelper_resize_mk_user_img($user->picture, 80, 80,'alt="'.$user->first_name.'" align="left" style="padding: 0px 12px 12px 0px;"');
            $network_icon_image = uihelper_resize_mk_img(PA::$network_info->inner_logo_image, 219, 35, DEFAULT_NETWORK_ICON,  'alt="'.PA::$network_info->name.'"');
            $invitee_url = url_for('user_blog', array('login'=>$user->login_name));
            $mail_type= "invite_pa";
            $array_of_data =
            array(
            'first_name'          => $user->first_name,
            'last_name'           => $user->last_name,
            'user_name'           => $user->login_name,
            'user_id'             => $user->user_id,
            'message'             => $inv->inv_message,
            'accept_url'          => $inv->accept_url,
            'register_url'        => $inv->register_url,
            'invited_user_name'   => $inv->inv_user_first_name,
            'invitee_image'       => $invitee_image,
            'network_name'        => PA::$network_info->name,
            'network_description' => PA::$network_info->description,
            'network_icon_image'  => $network_icon_image,
            'invitee_url'         => $invitee_url,
            'config_site_name'    => PA::$site_name
            );
            $check = pa_mail($inv->inv_email, $mail_type, $array_of_data, $user->email);
*/
            PAMail::send('invite_pa', $inv->inv_email, PA::$login_user, $inv);

            $msg .= "<br />" . $contact['name'] . ", " . $contact['email'];
          }
        } // end for : invitation to multiple email
        $msg  = "<br />Invitation message has been sent to: " . $msg;
      } else {
        $msg  = __("Please, select one or more contacts.");
      }
    }
    $redirect_url =  PA::$url . PA_ROUTE_USER_CONTACTS . "?type=contacts&stype=" . $this->type . "&msg=" . urlencode($msg);
    $this->controller->redirect($redirect_url);
  }

  private function handlePOST_deleteSelected($request_data) {
    $selected_contacts = (!empty($request_data['invite_selected'])) ? $request_data['invite_selected'] : null;
    if($selected_contacts) {
       $cnt = 0;
       foreach($selected_contacts as $key => $cntct_id) {
         try {
           UserContact::deleteUserContact($cntct_id);
           $cnt++;
         }
         catch(Exception $e) {
           $msg = $e->getMessage();
         }
       }
       $msg  = $cnt . __(" contact(s) sucessfully deleted.");
    } else {
       $msg  = __("Please, select one or more contacts.");
    }
    $redirect_url =  PA::$url . PA_ROUTE_USER_CONTACTS . "?type=contacts&stype=" . $this->type . "&msg=" . urlencode($msg);
    $this->controller->redirect($redirect_url);
  }

  private function handleAJAX_contactDetails($request_data) {
    $id = $request_data['contact_id'];
    $contact = UserContact::getUserContact( $id );
    if(!empty($contact) && is_object($contact)) {
      $html  = null;
      $name  =  $contact->get_contact_name();
      $email =  $contact->get_contact_email();
      $contact_extra = unserialize($contact->get_contact_extra());
      if(!empty($contact_extra['general'])) {
        if(!empty($contact_extra['general']['dob'])) {
           $bday_info = date_parse($contact_extra['general']['dob']);
           $bday = date("F dS", mktime(0, 0, 0, $bday_info['month'], $bday_info['day'], 0));
           $contact_extra['general']['dob'] = $bday;
        }
        $html .= "<h4 style=\"margin: 0px;\">" . __("General") . "</h4>";
        $li_contents = $this->buildHtmlList($contact_extra['general']);
        $html .= xhtmlTagHelper::ulistTag($li_contents, array("style" => "list-style-type: none; display: inline;"));
      }
      if(!empty($contact_extra['personal'])) {
        if(!empty($contact_extra['personal']['picture'])) {
           $img_url = $this->normalizeImgUrl($contact_extra['personal']['picture']);
           $img_url = PA::$url.'/resize_img.php?src='. $img_url .'&height=98&width=98';
           $contact_extra['personal']['picture'] = "<img src=\"$img_url\" alt=\"picture\" title=\"picture\" />";
        }
        $html .= "<h4 style=\"margin: 0px;\">" . __("Personal") . "</h4>";
        $li_contents = $this->buildHtmlList($contact_extra['personal']);
        $html .= xhtmlTagHelper::ulistTag($li_contents, array("style" => "list-style-type: none; display: inline;"));
      }
      if(!empty($contact_extra['professional'])) {
        $html .= "<h4 style=\"margin: 0px;\">" . __("Professional") . "</h4>";
        $li_contents = $this->buildHtmlList($contact_extra['professional']);
        $html .= xhtmlTagHelper::ulistTag($li_contents, array("style" => "list-style-type: none; display: inline;"));
      }
      if(!empty($contact_extra['extra'])) {
        if(!empty($contact_extra['extra']['business_photo'])) {
           $img_url = $this->normalizeImgUrl($contact_extra['extra']['business_photo']);
           $img_url = PA::$url.'/resize_img.php?src='. $img_url .'&height=98&width=98';
           $contact_extra['extra']['business_photo'] = "<img src=\"$img_url\" alt=\"picture\" title=\"picture\" />";
        }
        $html .= "<h4 style=\"margin: 0px;\">" . __("Other") . "</h4>";
        $li_contents = $this->buildHtmlList($contact_extra['extra']);
        $html .= xhtmlTagHelper::ulistTag($li_contents, array("style" => "list-style-type: none; display: inline;"));
      }
      echo $html;
    } else {
      echo __("No details.");
    }
    exit;
  }

  private function buildHtmlList($data) {
    $output = array();
    if(!is_array($data)) {
      $data = array($data);
    }
    foreach($data as $key => $value) {
      $key_upcs = ucfirst(strtr($key, array("_" => " ")));
      if(!preg_match("#</?[a-z][a-z0-9]*[^<>]*>#", $value)) {
        $value = wordwrap($value, 22, "<br />\n", true);
      }
      $html = "<ul style=\"padding-left: 12px;\">
                 <li style=\"width:84px; display: table-cell;\">$key_upcs:</li>
                 <li style=\"display: table-cell;\">$value</li>
               </ul>
              ";

      $output[] = $html;
    }
    return $output;
  }

  private function getUserContacts($user_id, $type) {
    $contacts_list = array();
    $contacts = UserContact::listUserContact( "user_id=$user_id AND contact_type='$type'" );
    foreach($contacts as $contact) {
      $name  =  $contact->get_contact_name();
      $email =  $contact->get_contact_email();
      $contact_extra = unserialize($contact->get_contact_extra());
      $contacts_list[] = array('cont_id'      => $contact->get_id(),
                               'name'         => $name,
                               'email'        => $email,
                               'picture'      => $this->getContactPicture($contact_extra),
                               'general'      => (!empty($contact_extra['general'])) ? $contact_extra['general'] : null,
                               'personal'     => (!empty($contact_extra['personal'])) ? $contact_extra['personal'] : null,
                               'professional' => (!empty($contact_extra['professional'])) ? $contact_extra['professional'] : null,
                               'extra'        => (!empty($contact_extra['extra'])) ? $contact_extra['extra'] : null
                               );
    }
    return $contacts_list;
  }

  private function getContactPicture($contact_extra) {
      if(!empty($contact_extra['personal']['picture'])) {
        $img_url = $this->normalizeImgUrl($contact_extra['personal']['picture']);
        $picture_url = PA::$url.'/resize_img.php?src='. $img_url .'&height=98&width=98';
      } else if(!empty($contact_extra['extra']['business_photo'])) { // Plaxo contacts can have BusinessPhoto data field
        $img_url = $this->normalizeImgUrl($contact_extra['extra']['business_photo']);
        $picture_url = PA::$url.'/resize_img.php?src='. $img_url .'&height=98&width=98';
      } else {
        $picture_url = PA::$url.'/resize_img.php?src=' . PA::$theme_url . '/images/default.png' . '&height=98&width=98';
      }
      return $picture_url;
  }

  private function normalizeImgUrl($url) {
    $outdata = null;
    if(false === strpos($url, "http://")) {
        $outdata = "http://" . $url;
    } else {
        $outdata = $url;
    }
    return $outdata;
  }

  private function prepareImportContacts($request_data) {
    $mapped_contacts = unserialize(base64_decode($request_data['contacts_encoded']));
    foreach($request_data['contact'] as $key => $value) {
      if(!empty($value['email'])) {
        $this->contacts[] = array('name' => $value['name'], 'email' => $value['email'], 'profile' => $mapped_contacts[$key]);
      }
    }
  }


  function set_inner_template($template_fname) {
    $this->inner_template = PA::$blockmodule_path .'/'. get_class($this) . "/$template_fname";
  }

  function render() {
    $content = parent::render();
    return $content;
  }

  function generate_inner_html($template_vars = array()) {

    $inner_html_gen = & new Template($this->inner_template);
    foreach($template_vars as $name => $value) {
      if(is_object($value)) {
        $inner_html_gen->set_object($name, $value);
      } else {
        $inner_html_gen->set($name, $value);
      }
    }
    $inner_html = $inner_html_gen->fetch();
    return $inner_html;
  }

}
