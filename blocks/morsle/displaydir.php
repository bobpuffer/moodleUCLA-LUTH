<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function displaydir ($wdir, $files) {
	//  $wdir == / or /a or /a/b/c/d  etc

//    global $basedir;
    global $courseid, $DB, $OUTPUT;
    global $USER, $CFG, $COURSE;
    global $choose;
    global $deptstr, $userstr;

    $course = $COURSE;
    $user = $USER;

	require_once($CFG->dirroot . '/blocks/morsle/constants.php');

	// Get the sort parameter if there is one
    $sort = optional_param('sort', 1, PARAM_INT);
    $dirlist = array();
    $filelist = array();
    $dirhref = array();
    $filehref = array();
    $courseid = $course->id;
    $coursecontext = context_course::instance($COURSE->id);

    // always include departmental directory if exists
    // TODO: change to handle cross-listed courses
	$shortname = is_number(substr($course->shortname,0,5)) ? substr($course->shortname, 6) : $course->shortname;
	// SPLIT INTO DEPARTMENTAL CODES
	$dept = explode("-",$shortname);
	$deptpart = defined($dept[0]) ? CONSTANT($dept[0]) : null;
	$deptstr =  $deptpart . $deptstr;
	$deptaccount = strtolower($deptstr);
	// check to see if we even have a departmental account for this department but don't show the departmental collection if we're already in it indicated by $wdir
	// TODO: this needs to change if we eliminate morsle table
	if ($wdir === '/'
//	if (strpos($wdir,$deptstr) === false
//			&& strpos($wdir,$shortname) === false
//			&& strpos($wdir, $userstr) === false
			&& $is_morsle_dept = $DB->get_record('morsle_active',array('shortname' => $deptaccount))
			&& has_capability('moodle/course:update', $coursecontext)) {
		$dirlist['dept'] = new stdClass();
		$dirlist['dept']->title  = $deptstr;
		$dirlist['dept']->updated = date('Y-m-d');
	}

	// only show the user collection if we're in the base folder
	if ($wdir === '/') {
//	if (strpos($wdir, $userstr) === false
//			&& strpos($wdir,$shortname) === false
//			&& strpos($wdir, $deptstr) === false) {
		$dirlist['dir'] = new stdClass();
		$dirlist['dir']->title  = $userstr; // include link to instructor's google docs
		$dirlist['dir']->updated = date('Y-m-d');
	}

	// separate all the files list into directories and files
	foreach ($files as $file) {
	    if (is_folder($file)) {
            $dirlist[] = $file;
	    } else {
            $filelist[] = $file;
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

    $padedit = $padunzip = $padlist = $padrestore = $padchoose = $padfolder = $padfile = $padlink = '';
    $attsArr = array($padedit=>$stredit, $padunzip=>$strunzip, $padlist=>$strlist, $padrestore=>$strrestore, $padchoose=>$strchoose, $padfolder=>$strfolder, $padfile=>$strfile, $padlink=>$strlinktocourse);
               foreach ($attsArr as $key => $value) {
                    $key = html_writer::div($value . '&nbsp', '', array('style'=>'color:transparent; background-color:transparent; display:inline;'));
                }
/*
    $padedit = html_writer::div($stredit . '&nbsp','', array('style'=>'color:transparent; background-color:transparent; display:inline'));
    $padunzip = html_writer::div($strunzip . '&nbsp','', array('style'=>'color:transparent; background-color:transparent; display:inline'));
    $padlist = html_writer::div($strlist . '&nbsp','', array('style'=>'color:transparent; background-color:transparent; display:inline'));
    $padrestore = html_writer::div($strrestore . '&nbsp','', array('style'=>'color:transparent; background-color:transparent; display:inline'));
    $padchoose = html_writer::div($strchoose . '&nbsp','', array('style'=>'color: transparent; background-color:transparent; display:inline'));
    $padfolder = html_writer::div($strfolder . '&nbsp','', array('style'=>'color:transparent; background-color:transparent; display:inline;'));
    $padfile = html_writer::div($strfile . '&nbsp','', array('style'=>'color:transparent; background-color; transparent; display:inline;'));
    $padlink = html_writer::div($strlinktocourse . '&nbsp','', array('style'=>'color:transparent; background-color:transparent; display:inline;'));
 */ 
    // Set sort arguments so that clicking on a column that is already sorted reverses the sort order
    $sortvalues = array(1,2,3);
    foreach ($sortvalues as &$sortvalue) {
	    if ($sortvalue == $sort) {
            $sortvalue = -$sortvalue;
        }
    }

    $upload_max_filesize = get_max_upload_file_size($CFG->maxbytes);

    // beginning of with selected files portion
    echo html_writer::start_tag('table', array('border'=>'0','cellspacing'=>'2','cellpadding'=>'2','style'=>'min-width: 900px; margin-left:auto; margin-right:auto','class'=>'files'));
    echo html_writer::start_tag('tr');

    //html_writer::table($table);
    if (!empty($USER->fileop) and ($USER->fileop == "move") and ($USER->filesource <> $wdir)) {
        echo html_writer::start_tag('td', array('colspan'=>'3','align'=>'center'));
        // move files to other folder form
        echo html_writer::start_tag('form', array('action'=>'morslefiles.php','method'=>'get'));
        echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'choose','value'=>$choose));
        echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'courseid','value'=>$courseid));
        echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'wdir','value'=>$wdir));
        echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'action','value'=>'paste'));
        echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'sesskey','value'=>$USER->sesskey));
        echo html_writer::tag('input', '', array('align'=>'center','type'=>'submit','value'=>$strmovefilestohere));
        //echo "<span> --> <b>$wdir</b></span><br />";
        echo html_writer::start_span() . '-->' . html_writer::tag('b', $wdir) . html_writer::end_span() . html_writer::end_tag('br');
        echo html_writer::end_tag('td');
        echo html_writer::start_tag('td');
        echo html_writer::end_tag('form');

        // cancel moving form
        echo html_writer::start_tag('form', array('action'=>'morslefiles.php','method'=>'get','align'=>'left'));
        echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'choose','value'=>$choose));
        echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'courseid','value'=>$courseid));
        echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'wdir','value'=>$wdir));
        echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'action','value'=>'cancel'));
        echo html_writer::tag('input', '', array('type'=>'submit','value'=>$strcancel,'style'=>'color:red; margin-left:10px'));
        echo html_writer::end_tag('form');
        echo html_writer::end_tag('td');
    } else {
		if (has_capability('moodle/course:update', $coursecontext) || strpos($wdir,'-write')) {
		echo html_writer::start_tag('tr', array('style'=>'background-color: #ffddbb;'));
                echo html_writer::start_tag('td', array('colspan'=>'3','align'=>'left','style'=>'background-color:#ffddbb; padding-left:5px;'));

                
	        // file upload form
	        // TODO: what if we're in the user or departmental dir?
                echo html_writer::start_tag('form', array('enctype'=>'multipart/form-data','method'=>'post','action'=>'morslefiles.php'));
                echo html_writer::start_span() . '&nbsp' . $struploadafile .'&nbsp('.$strmaxsize.')&nbsp'. html_writer::tag('b', $wdir) . html_writer::end_span() . html_writer::tag('br','');
                echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'choose','value'=>$choose));
                echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'courseid','value'=>$courseid));
                echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'wdir','value'=>$wdir));
                echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'action','value'=>'upload'));
                echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'sesskey','value'=>$USER->sesskey));
	        if (!isset($coursebytes)) { 
		    $coursebytes = 0; 
		}
	        if (!isset($modbytes)) { 
		    $modbytes = 0; 
		}
	        $maxbytes = get_max_upload_file_size($CFG->maxbytes, $coursebytes, $modbytes);
                $str = html_writer::tag('input', '', array('type'=>'hidden','name'=>'MAX_FILE_SIZE','value'=>$maxbytes)) . "\n";
	        $name = 'userfile';
                $str .= html_writer::tag('input', '', array('type'=>'file','size'=>'50','name'=>$name,'alt'=>$name, 'style'=>'margin-left: 5px;')) . html_writer::end_tag('br') . "\n";

	        echo $str;
                echo html_writer::tag('input', '', array('type'=>'submit','name'=>'save','value'=>$struploadthisfile,'style'=>'color:green; padding-left:5px;'));
                echo html_writer::tag('input', '', array('type'=>'submit','name'=>'savelink','value'=>$struploadandlinkthisfile,'style'=>'color:blue; padding-left:5px;'));
                echo html_writer::end_tag('form');
                echo html_writer::end_tag('td');
		echo html_writer::end_tag('tr');
		
		// cancel button div only if not in root morsle directory
		echo html_writer::start_tag('tr');
		echo html_writer::tag('td','',array('colspan'=>'2','style'=>'background-color:#ffddbb;'));
		echo html_writer::start_tag('td', array('style'=>'background-color:#ffddbb; padding-left:5px;','colspan'=>'1','align'=>'right'));
                echo html_writer::start_tag('form', array('action'=>'morslefiles.php','method'=>'get','align'=>'left'));
                echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'choose','value'=>$choose));
                echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'courseid','value'=>$courseid));
                echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'wdir','value'=>$wdir));
                echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'action','value'=>'cancel'));
                echo html_writer::tag('input', '', array('type'=>'submit','value'=>$strcancel,'align'=>'left','style'=>'color:red;'));
                echo html_writer::end_tag('form');
                echo html_writer::end_tag('td');
		echo html_writer::end_tag('tr');
                echo html_writer::end_tag('tr');
                echo html_writer::start_tag('tr');
		echo html_writer::start_tag('tr') . html_writer::tag('td', '<br>',array('colspace'=>'4')) . html_writer::end_tag('tr');
                echo html_writer::start_tag('td', array('style'=>'max-width:50px; white-space:nowrap;','colspan'=>'2','align'=>'left'));
                
	        //dummy form - alignment only
                echo html_writer::start_tag('form', array('action'=>'morslefiles.php','method'=>'get'));
            	echo html_writer::start_tag('fieldset', array('class'=>'invisiblefieldset'));
            	echo html_writer::tag('input', '', array('type'=>'button','value'=>$strselectall,'onclick'=>'checkall();','style'=>'color:green;'));
            	echo html_writer::tag('input', '', array('type'=>'button','value'=>$strselectnone,'onclick'=>'checknone();','style'=>'color:red;'));
            	echo html_writer::end_tag('fieldset');
            	echo html_writer::end_tag('form');
                echo html_writer::end_tag('td');

                echo html_writer::start_tag('td', array('align'=>'center','colspan'=>'2'));

	        // makedir form
			// TODO: program to allow this in user and departmental directory
            	if (strpos($wdir,$deptstr) === false && strpos($wdir,$userstr) === false) { // not a user or departmental folder
              	    echo html_writer::start_tag('form', array('action'=>'morslefiles.php','method'=>'get'));
                    echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'choose','value'=>$choose));
                    echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'courseid','value'=>$courseid));
                    echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'wdir','value'=>$wdir));
                    echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'action','value'=>'makedir'));
                    echo html_writer::tag('input', '', array('type'=>'submit','value'=>$strmakeafolder));
                    echo html_writer::end_tag('form');
            	}
                echo html_writer::end_tag('td');
                echo html_writer::end_tag('tr');
	    }
	}
        echo html_writer::start_tag('form', array('action'=>'morslefiles.php','method'=>'post','id'=>'dirform'));
        echo html_writer::start_div();
        echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'choose','value'=>$choose));
        echo html_writer::start_tag('tr');
        echo html_writer::start_tag('th', array('class'=>'header','scope'=>'col','style'=>'max-width:40px;'));
        echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'courseid','value'=>$courseid));
        echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'choose','value'=>$choose));
        echo html_writer::tag('input', '', array('type'=>'hidden','name'=>'wdir','value'=>$wdir));
        echo html_writer::tag('input','', array('type'=>'hidden','name'=>'sesskey','value'=>$USER->sesskey));
//      $options = array ("delete" => "$strdeletefromcollection");
	// only editing teachers can link items to course page
	if (has_capability('moodle/course:update', $coursecontext)) {
            $options['link'] = "$strlinktocourse";
	}
        if (!empty($filelist) || !empty($dirlist)) {

//        echo html_writer::tag('label', "$strwithchosenfiles...", array('for'=>'formactionid'));
//    	  echo html_writer::select($options, "$strwithchosenfiles...", '', array(1 => "$strwithchosenfiles..."));
           
            echo html_writer::start_div('', array('id'=>'noscriptgo','style'=>'display:inline;'));
            echo html_writer::tag('input','', array('type'=>'submit', 'value'=>get_string('go')));
            echo html_writer::script('document.getElementById("noscriptgo").style.display="none"');
            echo html_writer::end_div();

        }

    echo html_writer::end_tag('th');
    echo html_writer::start_tag('th', array('style'=>'padding-right:120px;','class'=>'header name', 'scope'=>'col')) . html_writer::link(qualified_me(), $strname, array('&sort'=>'{'.$sortvalues[0].'}')) . html_writer::end_tag('th');
    echo html_writer::start_tag('th', array('class'=>'header date','scope'=>'col')) . html_writer::link(qualified_me(), $strmodified, array('&sort'=>'{'.$sortvalues[2].'}')) . html_writer::end_tag('th');;
    echo html_writer::tag('th', $straction, array('class'=>'header commands','scope'=>'col'));
    echo html_writer::end_tag('tr') ."\n";

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
    foreach ($dirlist as $dir) {
        $filename = $dir->title;
        $filedate = docdate($dir);
        $row = array($filename, $filedate);
		array_push($dirdetails, $row);
 		usort($dirdetails, create_function('$a,$b', $sortcmp));
 	}

	// Create a 2D array of files and sort
    $filedetails = array();
    $filetitles = array();
    foreach ($filelist as $key=>$file) {
        $filename = s($file->title);
        $filedate = $file->modifiedDate;
	$filetitles[] = $filename;
	$filedetails[$filename] = array($filename, $filedate);
//        $row = array($filename, $filedate);
//		array_push($filedetails, $row);
//		usort($filedetails, create_function('$a,$b', $sortcmp));
	}
	// TODO: fix this hack so we're back to being able to sort
	ksort($filedetails); // sets the locked in sorting to name

	// need this in order to look up the link for the file based on doc title (key)
	if (sizeof($filelist) > 0) {
		$filevalues = array_values($filelist);
		$filelist = array_combine($filetitles, $filevalues);
	}
	$count = 0;
    $countdir = 0;
	$edittext = $padchoose .$padedit . $padunzip . $padlist . $padrestore;

    if (!empty($dirdetails)) {
        foreach ($dirdetails as $dir) {
            echo html_writer::start_tag('tr', array('class'=>'folder'));

           	$countdir++;
            $filedate = $dir[1];
            $filesafe = rawurlencode($dir[0]);
            // TODO: fix the parent directory
            if ($dir[0] == '..') {
//                $fileurl = rawurlencode(dirname($wdir));
                print_cell();
                // alt attribute intentionally empty to prevent repetition in screen reader
				//CLAMP #289 change padding-left from 10 to 0px
				//Kevin Wiliarty 2011-03-08
                print_cell('left', '<a  style="padding-left:0px" href="morslefiles.php?courseid='.$courseid.'&amp;wdir='.$wdir.'/'.$fileurl.'&amp;choose='.$choose.'"><img src="'.$OUTPUT->pix_url('f/parent.gif').'" class="icon" alt="" />&nbsp;'.get_string('parentfolder').'</a>', 'name');
                print_cell();
                print_cell();
                print_cell();
            } elseif(strpos($dir[0],$deptstr) === false && strpos($dir[0],$userstr) === false) { // not a user or departmental folder
                $filename = $dir[0];
		        foreach ($file->link as $link) {
		            if ($link['rel'] == 'alternate') {
		                $fileurl = $link['href'];
		                break;
		            }
		        }
               	print_cell("center", "<input type=\"checkbox\" name=\"dir$countdir\" value=\"$filename\" />", 'checkbox');
//                print_cell("left", "<a href=\"morslefiles.php?courseid=$courseid&amp;wdir=$wdir/$filesafe&amp;choose=$choose\"><img src=\"$OUTPUT->pix_url('f/folder')\" class=\"icon\" alt=\"$strfolder\" />&nbsp;".$filename."</a>", 'name');
                print_cell('left', '<a href="morslefiles.php?courseid='.$courseid.'&amp;wdir=' . $wdir . '/' . $filesafe .'&amp;choose='.$choose.'"><img src="'.$OUTPUT->pix_url('f/folder').'" class="icon" alt="" />&nbsp;'. $filename .'</a>', 'name');
                print_cell("right", $filedate, 'date');
//                print_cell();
				if (has_capability('moodle/course:update', $coursecontext)) {
                	print_cell("left", "$edittext<a href=\"morslefiles.php?courseid=$courseid&amp;wdir=$wdir&amp;file=$filename&amp;action=link&amp;type=dir&amp;choose=$choose\">$strlinktocourse</a>", 'commands');
				}
            } else { // if departmental account or user collection
            	// TODO: need to determine what $wdir is if we're coming in from one of the course subcollections
//                $fileurl = rawurlencode(dirname($wdir));
				$branchdir = strpos($wdir,'read') !== false || strpos($wdir,'write') !== false  || $wdir === '' ? $filesafe : "$wdir/$filesafe";
            	print_cell("center", "<input type=\"checkbox\" name=\"dir$countdir\" value=\"$filename\" />", 'checkbox');
                // alt attribute intentionally empty to prevent repetition in screen reader
				//CLAMP #289 change padding-left from 10 to 0px
				//Kevin Wiliarty 2011-03-08
                print_cell('left', '<a  style="padding-left:0px" href="morslefiles.php?courseid='.$courseid.'&amp;wdir=' . $branchdir .'&amp;choose='.$choose.'"><img src="'.$OUTPUT->pix_url('f/folder').'" class="icon" alt="" />&nbsp;'. $dir[0] .'</a>', 'name');
                print_cell("right", $filedate, 'date');
//                print_cell();
				if (has_capability('moodle/course:update', $coursecontext)) {
	                print_cell("left", "$edittext<a href=\"morslefiles.php?courseid=$courseid&amp;wdir=$branchdir&amp;file=$filename&amp;action=link&amp;type=dir&amp;choose=$choose\">$strlinktocourse</a>", 'commands');
				}
//              print_cell();
            }

            echo html_writer::end_tag('tr');
        }
    }

    $iconchoices = array('excel'=>'download/spreadsheets','powerpoint'=>'download/presentations','word'=>'download/documents', 'pdf'=>'application/pdf');
    if (!empty($filedetails)) {
        foreach ($filedetails as $filekey => $file) {

			// positively identify the correct icon regardless of filename extension
        	$exportlink = $filelist[$filekey]->content['src'];
        	$icon = mimeinfo("icon", $filekey);
			if ($icon == 'unknown') {
				foreach ($iconchoices as $key=>$value) {
					if (strpos($exportlink,$value)) {
						$icon = $key;
						break;
					}
				}
			}

            $count++;
            $filename = $filekey;
            $fileurl = get_href_noentry($filelist[$filekey], 'alternate');
            $fileurlsafe = rawurlencode($fileurl);
            $filedate    = substr(str_replace('Z','',str_replace('T',' ',$filelist[$filekey]->updated)),0,19);
//          $filedate    = date(strtotime($filelist[$filekey]->updated), 'm-d-Y H:M:S');

            $selectfile = trim($fileurl, "/");

            echo html_writer::start_tag('td', array('class'=>'file'));

            print_cell("center", "<input type=\"checkbox\" name=\"file$count\" value=\"$filename\" />", 'checkbox');
	    //CLAMP #289 change padding-left from 10 to 0px
	    //Kevin Wiliarty 2011-03-08
            echo html_writer::start_tag('td', array('align'=>'left','style'=>'white-space:nowrap; padding-left:0px;','class'=>'name'));
            
            $echovar = '<a href="' . $fileurl . '" target="_blank">
            		//<img src="' . $OUTPUT->pix_url("f/$icon") . '" class="icon" alt="' . $strfile . '" />&nbsp;' . htmlspecialchars($filename) . '</a>';
            echo $echovar;
//html_writer::link(qualified_me(), $strname, array('&sort'=>'{'.$sortvalues[0].'}'))
            //$echovar = html_writer::tag('a', $fileurl, array('target'=>'_blank')) . html_writer::img($OUTPUT->pix_url("f/$icon"), $strfile, array('class'=>'icon')) . '&nbsp;'.htmlspecialchars($filename) . html_writer::end_tag('a');
            //echo $echovar;
            echo html_writer::end_tag('td');

            print_cell("right", $filedate, 'date');
		if (has_capability('moodle/course:update', $coursecontext)) {
	              print_cell("left", "$edittext <a href=\"morslefiles.php?courseid=$courseid&amp;wdir=$wdir&amp;file=$filename&amp;action=link&amp;type=file&amp;choose=$choose\">$strlinktocourse</a>", 'commands');
            }
            print_cell('left', '&nbsp&nbsp<a title="' . strip_tags($strdownload) . ': ' . $filekey . '" href="' .$CFG->wwwroot
                    . '/blocks/morsle/docs_export.php?exportlink=' . s($exportlink) . '&shortname=' . $course->shortname . '&title=' . $filename . '" target="_blank"> Download </a>','commands');
//            print_cell();

           echo html_writer::end_tag('tr');
        }
    }
    echo html_writer::end_div();
    echo html_writer::end_tag('form');
    echo html_writer::end_tag('table');
}


