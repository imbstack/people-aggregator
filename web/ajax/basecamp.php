<?php

$login_required = FALSE;
include_once("web/includes/page.php");
require_once "ext/BaseCamp/BaseCampClient.php";

function bc_err($msg, $focus='basecamp_url') {
    echo '<div class="bold font-size-14 font-red">'.$msg.'</div><script><!--
var x = $("'.$focus.'"); x.focus(); x.select();
// --></script>';
    exit;
}

function mk_input($first, $last, $email, $checked=TRUE) {
    global $form_id;
    $input = '<input name="included[bc_'.$form_id.']" type="checkbox" '.($checked ? 'checked="checked"' : '').'"/>'
        .'<input type="hidden" name="firstname[bc_'.$form_id.']" value="'.htmlspecialchars($first).'"/>'
        .'<input type="hidden" name="lastname[bc_'.$form_id.']" value="'.htmlspecialchars($last).'"/>'
        .'<input type="hidden" name="email[bc_'.$form_id.']" value="'.htmlspecialchars($email).'"/>';
    ++$form_id;
    return $input;
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') bc_err("This script only accepts POST requests.");

$bc_url = trim(@$_REQUEST['bc_url']);
$bc_login = trim(@$_REQUEST['bc_login']);
$bc_password = trim(@$_REQUEST['bc_password']);

if (strpos($bc_url, "http://") !== 0) $bc_url = "http://$bc_url";
$parsed_url = @parse_url($bc_url);
if (!$parsed_url) bc_err("Please enter a valid URL, e.g. mysite.grouphub.com", "basecamp_url");

$bc_url = $parsed_url['scheme']."://".$parsed_url['host'];

if (!$bc_login) bc_err("Please enter your Basecamp login name", "basecamp_login");
if (!$bc_password) bc_err("Please enter your Basecamp password", "basecamp_password");
    
$bc = new BaseCampClient($bc_url, $bc_login, $bc_password);

try {
    switch (@$_REQUEST['op']) {
    case 'projects':
        // show list of projects

        ?><div class="font-size-14 bold font-red">Select the project from which you want to import people:</div><ul><?

        $projects = $bc->list_projects();
        
        foreach ($projects as $project) {
            ?><li class="font-size-12"><a href="#basecamp_div" onclick="return basecamp({op: 'project_people', project: <?=$project->id?>, project_company: <?=$project->company->id?>})"><?=htmlspecialchars($project->name)?></a></li><?
        }
        echo "</ul>";
        break;
        
    case 'project_people':
        // a project has been selected.

        ?><div class="font-size-14 bold">Select the people you want to invite from the lists below:</div><?

        // first, find all the company ids we can.
        // - start with the one in the project info
        $project_id = (int)@$_REQUEST['project'];
        $company_ids = array((int)@$_REQUEST['project_company'] => @$_REQUEST['project_company_name']);

        // - then scrape the contact list and add those in too
        foreach ($bc->companies() as $company) {
            $cid = (int)$company['id'];
            if (!@$company_ids[$cid]) $company_ids[$cid] = $company['name'];
        }

        $form_id = 1000; // start at 1000 so we don't overlap with the entered names/emails

        // now fetch all people from each company that are on the given project
        foreach ($company_ids as $company_id => $company_name) {
            $existing_people = array();
            $new_people = array();
            echo '<div class="font-size-14" style="background-color: #f0f0f0; width: 100%">Company: '.htmlspecialchars($company_name).'</div>';
            foreach ($bc->people($company_id, $project_id) as $person) {
                $email = $person->{'email-address'};
                try {
                    $u = new User();
                    $u->load($email, "email");
                } catch (PAException $e) {
                    $u = NULL;
                    if ($e->code != USER_NOT_FOUND) throw $e;
                }
                $name = $person->{'first-name'}.' '.$person->{'last-name'};
//                echo "<li>person: $name $email";
                if ($u) {
                    $existing_people[] = $u;
                } else {
                    $new_people[] = $person;
                }
                echo "</li>";
            }
            echo <<<EOF
                <table width="100%" border="0">
                <tr><td width="50%" style="color: #909090">existing members</td><td style="color: #a0a0a0">unknown people</td></tr>
                <tr><td>
EOF;
            // show list of people who are already here
            foreach ($existing_people as $u) {
                echo '<div class="fleft">'.mk_input($u->first_name, $u->last_name, $u->email, ($u->user_id != $login_uid)).' '.uihelper_resize_mk_user_img($u, 50, 50)."<br/><a href=\"mailto:$u->email\">$u->first_name $u->last_name</a></div>";
            }
            echo <<<EOF
                </td><td>
EOF;
            // show list of new people
            foreach ($new_people as $p) {
                echo '<p>'.mk_input($p->{'first-name'}, $p->{'last-name'}, $p->{'email-address'}).' <a href="mailto:'.$p->{'email-address'}.'">'.$p->{'first-name'}.' '.$p->{'last-name'}."</a></p>";
            }
            echo <<<EOF
                </td></tr>
                </table>
EOF;
        }
        break;

    case 'foo':
        
        echo "<p>getting project list</p>"; flush();
        
        $projects = $bc->list_projects();
        
        foreach ($projects as $project) {
            echo "<h1>project</h1>";
            foreach ($project as $k=>$v) {
                echo "<li>$k -> $v</li>";
            }
            
            // now get detail on the people from each company who are involved in this project
            foreach ($companies as $company) {
                echo "<p>getting list of people from company ".$company['id']." who are working on this project</p>";
                flush();
                $people = $bc->people($company['id'], $project->id);
                
                foreach ($people as $person) {
                    echo "<h1>person</h1>";
                    foreach ($person as $k=>$v) {
                        echo "<li>$k -> $v</li>";
                    }
                }
            }
            break;
        }
    }
} catch (PAException $e) {
    if ($e->code == USER_INVALID_PASSWORD)
        bc_err("Invalid login.  Please check your username and password.", "basecamp_login");
    else
        bc_err("Error occurred: ($e->code) $e->message", "basecamp_url");
}

?>