<?php // $Id$

//  Manage all uploaded files in a course file area

//  All the Moodle-specific stuff is in this top section
//  Configuration and access control occurs here.
//  Must define:  USER, basedir, baseweb, html_header and html_footer
//  USER is a persistent variable using sessions

    global $COURSE, $USER, $DB, $OUTPUT;
    require('../../config.php');
    require_once($CFG->libdir . '/filelib.php');
    require_once($CFG->libdir . '/adminlib.php');
    require_once($CFG->dirroot.'/google/gauth.php');
    require_once($CFG->dirroot.'/google/lib.php');
    require_once($CFG->dirroot.'/mod/resource/lib.php');
    require_once($CFG->dirroot.'/blocks/morsle/morslelib.php');
    require_once($CFG->dirroot.'/blocks/morsle/morsle.php');
    require_once($CFG->dirroot.'/repository/morsle/morsle_class.php');
    ?>
<script type="text/javascript">
//<![CDATA[
    function mycheckall() {
      var el = document.getElementsByTagName('input');
      for(var i=0; i<el.length; i++) {
        if(el[i].type == 'checkbox') {
          el[i].checked = exby.checked? true:false;
        }
      }
    }
//]]>
</script>

<?php
    $courseid      = required_param('courseid', PARAM_INT);
    $file    = optional_param('file', '', PARAM_PATH);
    $action  = optional_param('action', '', PARAM_ACTION);
    $name    = optional_param('name', '', PARAM_FILE);
    $oldname = optional_param('oldname', '', PARAM_FILE);
    $choose  = optional_param('choose', '', PARAM_FILE); //in fact it is always 'formname.inputname'
    $userfile= optional_param('userfile','',PARAM_FILE);
    $save    = optional_param('save', 0, PARAM_BOOL);
    $savelink    = optional_param('savelink', 0, PARAM_BOOL);
    $filelink    = optional_param('filelink', 0, PARAM_URL);
    $text    = optional_param('text', '', PARAM_RAW);
    $confirm = optional_param('confirm', 0, PARAM_BOOL);
    $type    = optional_param('type', 'file', PARAM_ALPHA);
    $username = 'puffro01@luther.edu';
    $gdocsstr = 'Google-Docs-Storage-for-';
    $domain = 'luther.edu';
    //    $shortname =  required_param('shortname');
//    $COURSE->shortname = $shortname;
//    $COURSE->id = required_param('courseid');
    $parentfolderid =  optional_param('parentfolderid', '', PARAM_ALPHANUMEXT);

    $wdir    = optional_param('wdir', '', PARAM_PATH);

    $context = context_course::instance($courseid);
    if (! $course = $DB->get_record("course", array('id' => $courseid))) {
        error("That's an invalid course id");
    }
//    require_login($course);

    $returnurl = new moodle_url("$CFG->wwwroot/blocks/morsle/morslefiles.php", array('courseid' => $courseid, 'wdir' => $wdir, 'file' => $file, 'action' => $action, 'choose' => $choose));

    // check quickly if upload was clicked without a file chosen
    if (empty($_POST['file']) && $file == null && $action !== '') {
        redirect("$CFG->wwwroot/blocks/morsle/morslefiles.php?courseid=$courseid&wdir=$wdir&action=''");
    }
    
    $morslefilestr = get_string('morslefiles', "block_morsle");
    $PAGE->set_context($context);
    $PAGE->set_course($course);
    $PAGE->set_pagelayout('report');

    $PAGE->set_url("$CFG->wwwroot/blocks/morsle/morslefiles.php", array('courseid' => $courseid, 'wdir' => $wdir, 'file' => $file, 'action' => $action, 'choose' => $choose));

    $PAGE->set_title($course->shortname.': '. $morslefilestr);

    $PAGE->set_heading($course->fullname);
    $navitems = $PAGE->navbar->get_items();

    $courseowner = strtolower($COURSE->shortname . '@' . $domain); // constant for the course owner account
//    $morsle = new morsle($courseowner);
    if (strpos($wdir, $gdocsstr) === 1) {
        $username = substr($name,strlen($gdocsstr), 60);
//        $username = 'puffro01@luther.edu';
    } else {
        $username = $courseowner;
    }
    $morsle = new morsle_google_auth($username, 'drive');
    $morsle->domain = '@luther.edu';
    $morsle->useremail = $course->shortname . $morsle->domain;


    //    $owner = $courseowner; // defaults to course ownership, could be changed by morsle_get_files()

    $userstr = get_string('useraccountstring','block_morsle') . $USER->email;

    $deptstr = get_string('departmentaccountstring', 'block_morsle');

    $owner = $COURSE->shortname;

    // determine the folder id needed for all queries

    if (aminroot($wdir)) {
    	$collectionid = 'root';
    	$basecollectionid = null;
        $files = get_doc_feed($morsle, $collectionid);
        $collection = $wdir;
    } else {
        $collections = explode('/',$wdir);
        $basecollectionid = null;
    	foreach ($collections as $collection) { // cycle through the path so our ultimate collection is a subcollection of its parent
            if ($collection !== '') {
                $collectionid = get_collection($morsle, $collection, $basecollectionid);
                $basecollectionid = $collectionid; // just for cycling through the collections, not used again
            }
    	}
        $files = get_doc_feed($morsle, $collectionid);
    }


    $PAGE->navbar->ignore_active();
    if ($wdir === '') {
        $PAGE->navbar->add($morslefilestr, $returnurl);
    } else {
//    	$PAGE->navbar->add($wdir, $returnurl);
    	$PAGE->navbar->add($collection, $returnurl);
    }

    echo $OUTPUT->header();

    // get read-only folderid because we'll use this a lot and its easier than trying to keep getting it from Google
    $readfolderid = $DB->get_field('morsle_active', 'readfolderid', array('courseid' => $COURSE->id));

    $pagetitle = strip_tags($course->shortname.': '. $wdir);

//  End of configuration and access control

    switch ($action) {
    	case 'upload and link':
        case "upload":
//            html_header($course, $wdir);
//            require_once($CFG->dirroot.'/google/googleuploadlib.php');

            if (($save || $savelink) && confirm_sesskey()) { // either upload or upload and link
                    $today = date(DATE_RFC3339,time()-(60*10));
            	$course->maxbytes = 0;  // We are ignoring course limits
//                $um = new upload_manager('userfile',false,false,$course,false,0);
//                $res_med_link = get_feed_edit_link($owner);
//                if ($um->process_file_uploads($wdir)) {

                    // send the file contents up to google
                $uploadfile = $_FILES['userfile']['tmp_name'];
                $filetype = mimeinfo('type', strtolower($_FILES['userfile']['name']));
                $success = send_file_togoogle($morsle, $_FILES['userfile']['name'], $uploadfile, $filetype, $collectionid);
                notify(get_string('uploadedfile'));
//                }
                // um will take care of error reporting.

            }
            if ($save) { // only upload
                // get ready to display the list of resources again
                $files = morsle_get_files($morsle, $wdir, $collectionid);
                displaydir($wdir, $files);
                break;
            }
        case "link":
            if (strpos($wdir, $gdocsstr) === 1) {
                if (link_to_gdoc($name, $filelink)) {
                    $path = explode("/", $wdir);
                    $fileid = $path[2];
                    add_file_tocollection($morsle, $fileid, $readfolderid);
                    $wdir = '/' . $path[1];
                }
            } else {
                if ($savelink) {
                    $_POST['file1'] = $_FILES['userfile']['name'];
                }
                if (empty($_POST)) {
                    $_POST['file1'] = $file;
                }
                $files = morsle_get_files($morsle, $wdir, $collectionid);
                setfilelist($_POST, $wdir, $owner, $files, $type);
                if (!empty($USER->filelist)) {
                    foreach ($USER->filelist as $name=>$value) {
                        if (!link_to_gdoc($name, $value->link, $value->type)) {
                            print_error("gdocslinkerror","error");
                        } elseif (strpos($wdir, $deptstr) !== false || strpos($wdir, $userstr) !== false) {
                            // need to share resource with course user account
                            $acl_base_feed = 'https://docs.google.com/feeds/default/private/full/' . $value->id . '/acl';
                            assign_file_permissions($courseowner, 'writer', $owner, $acl_base_feed);
                            // need to place anything from departmental or instructors resources into the read-only collection so students can see them
                            add_file_tocollection($morsle, $readfolderid, $value->id);
                        }
                    }
                }
            }
            notify(get_string('linkedfile', 'block_morsle'));


            // get ready to display the list of resources again
            clearfilelist();
            $files = morsle_get_files($morsle, $wdir, $collectionid);
            displaydir($wdir, $files);
            break;
        case "makedir":
            if (($name != '') && confirm_sesskey()) {
            	$collections = explode("\r\n",$_POST['name']);
            	foreach ($collections as $name) {
                    if ($name !== '') {
                        createcollection($morsle, $name, $collectionid);
                    }
            	}

            	// go get folder contents from Google and display
//	            html_header($course, $wdir);
		    	$files = morsle_get_files($wdir, $collectionid, $owner);
	            displaydir($wdir, $files);
            } else {
            	// display the input form for the new collection name
                $strcreate = get_string("create");
                $strcancel = get_string("cancel");
                $strcreatefolder = get_string("createfolder", "block_morsle", $wdir);
//                html_header($course, $wdir, "form.name");
                echo "<p>$strcreatefolder:</p>";

                //TODO: replace with mform
                echo "<table><tr><td>";
                echo "<form action=\"morslefiles.php\" method=\"post\">";
                echo "<fieldset class=\"invisiblefieldset\">";
                echo ' <input type="hidden" name="choose" value="'.$choose.'" />';
                echo " <input type=\"hidden\" name=\"courseid\" value=\"$courseid\" />";
                echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />";
                echo " <input type=\"hidden\" name=\"action\" value=\"makedir\" />";
                echo " <textarea cols=60 rows=10 name=\"name\"></textarea>";
                echo " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\" />";
                echo " <input type=\"submit\" value=\"$strcreate\" />";
                echo "</fieldset>";
                echo "</form>";
                echo "</td><td>";
                echo "<form action=\"morslefiles.php\" method=\"get\">";
                echo "<div>";
                echo ' <input type="hidden" name="choose" value="'.$choose.'" />';
                echo " <input type=\"hidden\" name=\"courseid\" value=\"$courseid\" />";
                echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />";
                echo " <input type=\"hidden\" name=\"action\" value=\"cancel\" />";
                echo " <input type=\"submit\" value=\"$strcancel\" />";
                echo "</div>";
                echo "</form>";
                echo "</td></tr></table>";
            }
            break;
    	default:
            displaydir($wdir, $files);
            break;
}
echo $OUTPUT->footer();

function aminroot($wdir) {
    $gdocsstr = 'Google-Docs-Storage-for-';
    if ($wdir == '' || strpos($wdir, $gdocsstr)) {
        return true;
    } else {
        return false;
    }
}
?>