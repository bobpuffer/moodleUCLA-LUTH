<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function displaydir ($wdir, $files) {
    //  $wdir == / or /a or /a/b/c/d  etc

    @ini_set('memory_limit', '1024M');
    global $courseid, $DB, $OUTPUT;
    global $USER, $CFG, $COURSE;
    global $choose;
    global $deptstr, $userstr;
    require_once($CFG->dirroot . '/blocks/morsle/constants.php');

    $course = $COURSE;
    $user = $USER;


	// Get the sort parameter if there is one
    $sort = optional_param('sort', 1, PARAM_INT);
    $dirlist = array();
    $filelist = array();
    $dirhref = array();
    $filehref = array();
    $courseid = $course->id;
    $coursecontext = context_course::instance($COURSE->id);


    // separate all the files list into directories and files
    foreach ($files as $name=>$file) {
        if (is_folder($file)) {
            $dirlist[$name] = $file;
        } else {
            $filelist[$name] = $file;
        }
    }

    // setup variables and strings
    $strname = get_string("name", 'block_morsle');
    $strsize = get_string("size");
    $strmodified = get_string("modified");
    $straction = get_string("action");
    $strmakeafolder = get_string("morslemakecollection", 'block_morsle');
    $struploadafile = get_string("uploadafile");
    $strselectall = get_string("selectall");
    $strselectnone = get_string("deselectall");
    $strwithchosenfiles = get_string("withchosenfiles");
    $strmovetoanotherfolder = get_string("movetoanotherfolder");
    $strlinktocourse = get_string("linktocourse", 'block_morsle');
    $strmovefilestohere = get_string("movefilestohere");
    $strdeletefromcollection = get_string("deletefromcollection",'block_morsle');
    $strcreateziparchive = get_string("createziparchive");
    $strrename = get_string("rename");
    $stredit   = get_string("edit");
    $strunzip  = get_string("unzip");
    $strlist   = get_string("list");
    $strrestore= get_string("restore");
    $strchoose = get_string("choose");
    $strfolder = get_string("folder");
    $strfile   = get_string("file");
    $strdownload = get_string("strdownload", 'block_morsle');
    $struploadthisfile = get_string("uploadthisfile");
    $struploadandlinkthisfile = get_string("uploadandlinkthisfile", 'block_morsle');

    $filesize = 'Varies as to type of document';
    $strmaxsize = get_string("maxsize", "", $filesize);
    $strcancel = get_string("cancel");
    $strmodified = get_string("strmodified", 'block_morsle');

    //CLAMP #289 set color and background-color to transparent
	//Kevin Wiliarty 2011-03-08
    $padrename = get_string("rename");
    $padedit   = '<div style="color:transparent; background-color:transparent; display:inline">' . $stredit . '&nbsp;</div>';
    $padunzip  = '<div style="color:transparent; background-color:transparent; display:inline">' . $strunzip . '&nbsp;</div>';
    $padlist   = '<div style="color:transparent; background-color:transparent; display:inline">' . $strlist . '&nbsp;</div>';
    $padrestore= '<div style="color:transparent; background-color:transparent; display:inline">' . $strrestore . '&nbsp;</div>';
    $padchoose = '<div style="color:transparent; background-color:transparent; display:inline">' . $strchoose . '&nbsp;</div>';
    $padfolder = '<div style="color:transparent; background-color:transparent; display:inline">' . $strfolder . '&nbsp;</div>';
    $padfile   = '<div style="color:transparent; background-color:transparent; display:inline">' . $strfile . '&nbsp;</div>';
    $padlink   = '<div style="color:transparent; background-color:transparent; display:inline">' . $strlinktocourse . '&nbsp;</div>';

    $gdocsstr = 'Google-Docs-Storage-for-';

    // Set sort arguments so that clicking on a column that is already sorted reverses the sort order
    $sortvalues = array(1,2,3);
    foreach ($sortvalues as &$sortvalue) {
	    if ($sortvalue == $sort) {
            $sortvalue = -$sortvalue;
        }
    }

    $upload_max_filesize = get_max_upload_file_size($CFG->maxbytes);

    // beginning of with selected files portion
    echo "<table border=\"0\" cellspacing=\"2\" cellpadding=\"2\" style=\"min-width:900px;margin-left:auto;margin-right:auto\" class=\"files\">";
    if ($wdir !== '' && strpos($wdir, $gdocsstr) !== 1) {
        echo "<tr>";
        if (!empty($USER->fileop) and ($USER->fileop == "move") and ($USER->filesource <> $wdir)) {
            echo "<td colspan = \"3\" align=\"center\">";

            // move files to other folder form
            echo "<form action=\"morslefiles.php\" method=\"get\">";
            echo ' <input type="hidden" name="choose" value="'.$choose.'" />';
            echo " <input type=\"hidden\" name=\"courseid\" value=\"$courseid\" />";
            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />";
            echo " <input type=\"hidden\" name=\"action\" value=\"paste\" />";
            echo " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\" />";
            echo " <input align=\"center\" type=\"submit\" value=\"$strmovefilestohere\" />";
            echo "<span> --> <b>$wdir</b></span><br />";
            echo "</td>";
                    echo '<td>';
            echo "</form>";

            // cancel moving form
            echo "<form action=\"morslefiles.php\" method=\"get\" align=\"left\">";
            echo ' <input type="hidden" name="choose" value="'.$choose.'" />';
            echo " <input type=\"hidden\" name=\"courseid\" value=\"$courseid\" />";
            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />";
            echo " <input type=\"hidden\" name=\"action\" value=\"cancel\" />";
            echo " <input type=\"submit\" value=\"$strcancel\" style = \"color: red;margin-left:10px\" />";
            echo "</form>";
            echo "</td>";
        } else if (has_capability('moodle/course:update', $coursecontext) || strpos($wdir,'-write')) {
            echo '<td colspan = "4"></td>';
            echo '<td style="background-color:#ffddbb;padding-left:5px" colspan = "1" align="left">';

            // file upload form
            // TODO: what if we're in the user or departmental dir?
            echo "<form enctype=\"multipart/form-data\" method=\"post\" action=\"morslefiles.php\">";
            echo "<span> $struploadafile ($strmaxsize) --> <b>$wdir</b></span><br />";
            echo ' <input type="hidden" name="choose" value="'.$choose.'" />';
            echo " <input type=\"hidden\" name=\"courseid\" value=\"$courseid\" />";
            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />";
            echo " <input type=\"hidden\" name=\"action\" value=\"upload\" />";
            echo " <input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\" />";
            if (!isset($coursebytes)) { $coursebytes = 0; }
            if (!isset($modbytes)) { $modbytes = 0; }
            $maxbytes = get_max_upload_file_size($CFG->maxbytes, $coursebytes, $modbytes);
            $str = '<input type="hidden" name="MAX_FILE_SIZE" value="'. $maxbytes .'" />'."\n";
            $name = 'userfile';
            $str .= '<input type="file" size="50" name="'. $name .'" alt="'. $name .'" /><br />'."\n";

            echo $str;
            echo " <input type=\"submit\" name=\"save\" value=\"$struploadthisfile\" style = \"color: green;padding-left:5px\" />";
            echo " <input type=\"submit\" name=\"savelink\" value=\"$struploadandlinkthisfile\" style = \"color: blue;padding-left:5px\" />";
            echo "</form>";
                echo '</td>';
            echo '</tr>';
            echo '<tr>';
            echo "<td style = \"max-width:50px; white-space: nowrap\" colspan = \"2\" align=\"left\">";

            //dummy form - alignment only
/*
            echo "<form action=\"morslefiles.php\" method=\"get\">";
            echo "<fieldset class=\"invisiblefieldset\">";
            echo " <input type=\"button\" value=\"$strselectall\" onclick=\"checkall();\" style = \"color: green\" />";
            echo " <input type=\"button\" value=\"$strselectnone\" onclick=\"uncheckall();\" style = \"color: red\" />";
            echo "</fieldset>";
            echo "</form>";
 * 
 */
            echo "</td>";
            echo '<td align="center" colspan = "2">';

            // makedir form
            // TODO: program to allow this in user and departmental directory
            if (strpos($wdir,$deptstr) === false && strpos($wdir,$userstr) === false) { // not a user or departmental folder
                echo "<form action=\"morslefiles.php\" method=\"get\">";
                echo ' <input type="hidden" name="choose" value="'.$choose.'" />';
                echo " <input type=\"hidden\" name=\"courseid\" value=\"$courseid\" />";
                echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />";
                echo " <input type=\"hidden\" name=\"action\" value=\"makedir\" />";
                echo " <input type=\"submit\" value=\"$strmakeafolder\" />";
                echo "</form>";
            }
            echo '</td>';

                // cancel button div only if not in root morsle directory
            echo '<td style="background-color:#ffddbb;padding-left:5px" colspan="1">';
                    echo "<form action=\"morslefiles.php\" method=\"get\" align=\"left\">";
            echo ' <input type="hidden" name="choose" value="'.$choose.'" />';
            echo " <input type=\"hidden\" name=\"courseid\" value=\"$courseid\" />";
            echo " <input type=\"hidden\" name=\"wdir\" value=\"$wdir\" />";
            echo " <input type=\"hidden\" name=\"action\" value=\"cancel\" />";
            echo " <input type=\"submit\" value=\"$strcancel\" align=\"left\" style = \"color: red\" />";
            echo "</form>";
                echo '</td>';
            echo '</tr>';
        }
    }

    echo "<form action=\"morslefiles.php\" method=\"post\" id=\"dirform\">";
    echo "<div>";
    echo '<input type="hidden" name="choose" value="'.$choose.'" />';
    echo "<tr>";
    echo "<th class=\"header\" scope=\"col\" style = \"max-width : 40px\">";
    echo "<input type=\"hidden\" name=\"courseid\" value=\"$courseid\" />";
    echo '<input type="hidden" name="choose" value="'.$choose.'" />';
    echo "<input type=\"hidden\" name=\"wdir\" value=\"$wdir\" /> ";
    echo "<input type=\"hidden\" name=\"sesskey\" value=\"$USER->sesskey\" />";
    //    $options = array ("delete" => "$strdeletefromcollection");
            // only editing teachers can link items to course page
    if (has_capability('moodle/course:update', $coursecontext)) {
        $options['link'] = "$strlinktocourse";
    }
    if (!empty($filelist) || !empty($dirlist)) {

    //        echo html_writer::tag('label', "$strwithchosenfiles...", array('for'=>'formactionid'));
    //    	echo html_writer::select($options, "$strwithchosenfiles...", '', array(1 => "$strwithchosenfiles..."));
        echo '<div id="noscriptgo" style="display: inline;">';
        echo '<input type="submit" value="'.get_string('go').'" />';
        echo '<script type="text/javascript">'.
               "\n//<![CDATA[\n".
               'document.getElementById("noscriptgo").style.display = "none";'.
               "\n//]]>\n".'</script>';
        echo '</div>';

    }

    echo "</th>";
    echo "<th style=\"padding-left:120px\" class=\"header name\" scope=\"col\"><a href=\"" . qualified_me(). "&sort={$sortvalues[0]}\">$strname</a></th>";
    echo "<th class=\"header date\" scope=\"col\"><a href=\"" . qualified_me(). "&sort={$sortvalues[2]}\">$strmodified</a></th>";
    echo "<th class=\"header commands\" scope=\"col\">$straction</th>";
    echo "</tr>\n";

    // Sort parameter indicates column to sort by, and parity gives the direction
    switch ($sort) {
        case 1:
            $sortcmp = 'return strcasecmp($a[0],$b[0]);';
            break;
        case -1:
            $sortcmp = 'return strcasecmp($b[0],$a[0]);';
            break;
        case 2:
            $sortcmp = 'return ($a[1] - $b[1]);';
            break;
        case -2:
            $sortcmp = 'return ($b[1] - $a[1]);';
            break;
        case 3:
            $sortcmp = 'return ($a[2] - $b[2]);';
            break;
        case -3:
            $sortcmp = 'return ($b[2] - $a[2]);';
            break;
    }

    // Create a 2D array of directories and sort
    $dirdetails = array();
    foreach ($dirlist as $name=>$dir) {
        $dirdetails[$name] = new stdClass();
        $dirdetails[$name]->updated = docdate($dir);
        $dirdetails[$name]->link = $dir->alternateLink;
//        usort($dirdetails, create_function('$a,$b', $sortcmp));
    }

    // TODO: change to handle cross-listed courses
    // TODO: this needs to change if we eliminate morsle table
    if ($wdir === '') {
        $shortname = is_number(substr($course->shortname,0,5)) ? substr($course->shortname, 6) : $course->shortname;
        // SPLIT INTO DEPARTMENTAL CODES
        $dept = explode("-",$shortname);
        $deptpart = defined($dept[0]) ? CONSTANT($dept[0]) : null;
        $deptstr =  $deptpart . $deptstr;
        $deptaccount = strtolower($deptstr);
        // only show the user collection if we're in the base folder
        $dirdetails[$userstr] = new stdClass();
        $dirdetails[$userstr]->updated = date('Y-m-d');
        $dirdetails[$userstr]->link = 'https://drive.google.com';
    
        // always include departmental directory if exists
        // check to see if we even have a departmental account for this department but don't show the departmental collection if we're already in it indicated by $wdir
        if ($is_morsle_dept = $DB->get_record('morsle_active',array('shortname' => $deptaccount))
            && has_capability('moodle/course:update', $coursecontext)) {
            $dirdetails[$deptstr] = new stdClass();
            $dirdetails[$deptstr]->updated = date('Y-m-d');
        }

    }

    // Create a 2D array of files and sort
    $filedetails = array();
    $filetitles = array();
    foreach ($filelist as $name=>$file) {
        $filedetails[$name] = new stdClass();
        $filedetails[$name]->updated = docdate($file);
        $filedetails[$name]->link = $file->alternateLink;
//        $row = array($filename, $filedate);
//		array_push($filedetails, $row);
//		usort($filedetails, create_function('$a,$b', $sortcmp));
    }
    // TODO: fix this hack so we're back to being able to sort
//    ksort($filedetails); // sets the locked in sorting to name
    // need this in order to look up the link for the file based on doc title (key)
/*
    if (sizeof($filelist) > 0) {
            $filevalues = array_values($filelist);
            $filelist = array_combine($filetitles, $filevalues);
    }
*/
//    $count = 0;
//    $countdir = 0;
	$edittext = $padchoose .$padedit . $padunzip . $padlist . $padrestore;

    if ($wdir !== '') {
        echo "<tr class=\"folder\">";
        print_cell();
        print_cell('left', '<a href="morslefiles.php?courseid=' . $courseid . '&amp;wdir=' . $wdir . '&amp;choose=' . $choose . '&amp;name=' . $name . '"><img src="'.$OUTPUT->pix_url('f/folder').'" class="icon" alt="" />&nbsp;'. get_string('parentfolder') .'</a>', 'name');
//        print_cell('left', '<a  style="padding-left:0px" href="morslefiles.php?courseid='.$courseid.'&amp;wdir='.$wdir.'/&amp;choose='.$choose.'">&nbsp;'.get_string('parentfolder').'</a>', 'parent');
        echo "</tr>";
    }
    if (!empty($dirdetails)) {
        foreach ($dirdetails as $name => $dir) {
            echo "<tr class=\"folder\">";
            $filedate = $dir->updated;
            $filesafe = rawurlencode($name);
            $filename = $name;
            $fileurl = $dir->link;

//           	$countdir++;
            // TODO: fix the parent directory
            if ($name == '..') {
//                $fileurl = rawurlencode(dirname($wdir));
                print_cell();
                // alt attribute intentionally empty to prevent repetition in screen reader
				//CLAMP #289 change padding-left from 10 to 0px
				//Kevin Wiliarty 2011-03-08
                print_cell('left', '<a  style="padding-left:0px" href="morslefiles.php?courseid='.$courseid.'&amp;wdir='.$wdir.'/'.$fileurl.'&amp;choose='.$choose.'"><img src="'.$OUTPUT->pix_url('f/parent.gif').'" class="icon" alt="" />&nbsp;'.get_string('parentfolder').'</a>', 'name');
                print_cell();
                print_cell();
                print_cell();
/*
            } else if ($name === $userstr) { // if departmental account or user collection
            	// TODO: need to determine what $wdir is if we're coming in from one of the course subcollections
                // don't know where this fits in
		$branchdir = strpos($wdir,'read') !== false || strpos($wdir,'write') !== false  || $wdir === '' ? $filesafe : "$wdir/$filesafe";
                 print_cell();
                // alt attribute intentionally empty to prevent repetition in screen reader
				//CLAMP #289 change padding-left from 10 to 0px
				//Kevin Wiliarty 2011-03-08
                print_cell('left', '<a  style="padding-left:0px" href="morslefiles.php?courseid=' . $courseid . '&amp;wdir=' . $wdir . '&amp;choose=' . $choose .'&amp;name=' . $name . '"><img src="'.$OUTPUT->pix_url('f/folder').'" class="icon" alt="" />&nbsp;'. $name .'</a>', 'name');
//                print_cell('left', '<a  style="padding-left:0px" href="' . $fileurl . '" target="_blank"><img src="'. $OUTPUT->pix_url("f/folder") .'" class="icon" alt="" />&nbsp;'. $filename .'</a>');
                print_cell("right", $filedate, 'date');
//                print_cell();
                print_cell();
//              print_cell();
            } else if ($name === $deptstr){
            	// TODO: need to determine what $wdir is if we're coming in from one of the course subcollections
		$branchdir = strpos($wdir,'read') !== false || strpos($wdir,'write') !== false  || $wdir === '' ? $filesafe : "$wdir/$filesafe";
            	print_cell("center", "<input type=\"checkbox\" name=\"dir$countdir\" value=\"$filename\" />", 'checkbox');
                // alt attribute intentionally empty to prevent repetition in screen reader
				//CLAMP #289 change padding-left from 10 to 0px
				//Kevin Wiliarty 2011-03-08
                print_cell('left', '<a  style="padding-left:0px" href="morslefiles.php?courseid=' . $courseid . '&amp;wdir=' . $branchdir . '&amp;choose=' . $choose . '&amp;name=' . $name . '"><img src="'.$OUTPUT->pix_url('f/folder').'" class="icon" alt="" />&nbsp;'. $name .'</a>', 'name');
                print_cell("right", $filedate, 'date');
//                print_cell();
				if (has_capability('moodle/course:update', $coursecontext)) {
	                print_cell("left", "$edittext<a href=\"morslefiles.php?courseid=$courseid&amp;wdir=$branchdir&amp;file=$filename&amp;action=link&amp;type=dir&amp;choose=$choose\">$strlinktocourse</a>", 'commands');
				}
//              print_cell();
*/
            } else { // not a user or departmental folder
                print_cell();
//               	print_cell("center", "<input type=\"checkbox\" name=\"$name\" value=\"$filename\" />", 'checkbox');
//                print_cell("left", "<a href=\"morslefiles.php?courseid=$courseid&amp;wdir=$wdir/$filesafe&amp;choose=$choose\"><img src=\"$OUTPUT->pix_url('f/folder')\" class=\"icon\" alt=\"$strfolder\" />&nbsp;".$filename."</a>", 'name');
                $branchdir = "$wdir/$filesafe";
//                $branchdir = strpos($wdir,'read') !== false || strpos($wdir,'write') !== false  || $wdir === '' ? $filesafe : "$wdir/$filesafe";
                print_cell('left', '<a href="morslefiles.php?courseid=' . $courseid . '&amp;wdir=' . $branchdir . '&amp;choose=' . $choose . '&amp;name=' . $name . '"><img src="'.$OUTPUT->pix_url('f/folder').'" class="icon" alt="" />&nbsp;'. $filename .'</a>', 'name');
                print_cell("right", $filedate, 'date');
//                print_cell();
                if (has_capability('moodle/course:update', $coursecontext)) {
                    print_cell("left", "$edittext<a href=\"morslefiles.php?courseid=$courseid&amp;wdir=$branchdir&amp;file=$filename&amp;action=link&amp;type=dir&amp;choose=$choose\">$strlinktocourse</a>", 'commands');
//                    print_cell("left", "$edittext<a href=\"morslefiles.php?courseid=$courseid&amp;wdir=$wdir&amp;file=$filename&amp;action=link&amp;type=dir&amp;choose=$choose\">$strlinktocourse</a>", 'commands');
                }
            }

            echo "</tr>";
        }
    }

    $iconchoices = array('excel'=>'download/spreadsheets','powerpoint'=>'download/presentations','word'=>'download/documents',
    		'pdf'=>'application/pdf');
    if (!empty($filedetails)) {
        foreach ($filedetails as $name => $file) {

            if (isset($filelist[$name]->exportLinks)) {
                $links = array();
                $links = array_values($filelist[$name]->exportLinks);
                $exportlink = $links[0];
            } else {
                $exportlink = $filelist[$name]->alternateLink;
            }
            // positively identify the correct icon regardless of filename extension
            $icon = $filelist[$name]->iconLink;
            $filename = $name;
            $fileurl = $file->link;
            $embedlink = $filelist[$name]->embedLink;
            $embedsafe = rawurlencode($embedlink);
            $fileurlsafe = rawurlencode($fileurl);
            $filedate    = $file->updated;
            $fileid = $filelist[$name]->id;
            $selectfile = trim($fileurl, "/");

            echo "<tr class=\"morslefile\">";

            print_cell();
//            print_cell("center", "<input type=\"checkbox\" name=\"$name\" value=\"$filename\" />", 'checkbox');
			//CLAMP #289 change padding-left from 10 to 0px
			//Kevin Wiliarty 2011-03-08
            print_cell('left', '<a href="' . $fileurl . '" class="morslefile" target="_blank">
            		<img src="' . $icon . '" class="icon" alt="' . $strfile . '" /> ' . $filename . '</a>', 'name');

            print_cell("right", $filedate, 'date');
            if (has_capability('moodle/course:update', $coursecontext)) {
                if (strpos($wdir, $gdocsstr) === 1) {
                    print_cell("left", "$edittext <a href=\"morslefiles.php?courseid=$courseid&amp;wdir=$wdir/$fileid&amp;file=$filename&amp;name=$filename&amp;filelink=$fileurl&amp;action=link&amp;type=file&amp;choose=$choose\">$strlinktocourse</a>", 'commands');
                } else {
                    print_cell("left", "$edittext <a href=\"morslefiles.php?courseid=$courseid&amp;wdir=$wdir&amp;file=$filename&amp;action=link&amp;type=file&amp;choose=$choose\">$strlinktocourse</a>", 'commands');
                 }
                print_cell('left', '&nbsp&nbsp<a title="' . $name . '" href="embeddoc.php?courseid=$courseid&amp;embedlink=' . $embedsafe . '&amp;name=' . $filename . '"> Embed </a>','embed');
            }
//            print_cell('left', '&nbsp&nbsp<a title="' . strip_tags($strdownload) . ': ' . $name . '" href="' .$CFG->wwwroot
//                    . '/blocks/morsle/docs_export.php?exportlink=' . s($exportlink) . '&shortname=' . $course->shortname . '&title=' . $filename . '" target="_blank"> Download </a>','commands');
            print_cell();
            print_cell('left', '&nbsp&nbsp<a title="' . $name . '" href="' . s($exportlink) . '" target="_blank"> Download </a>','commands');
            //print_cell('left', '&nbsp&nbsp<a title="' . $name . '" href="embeddoc.php?"' . s($embedlink) . '" target="_blank"> Embed in a Page resource </a>','commands');

            echo "</tr>";
        }
    }
    echo "</div>";
    echo "</form>";
    echo "</table>";
}
