<?php

/**
 * This php document shows an instance
 * specific to the model personal interview
 *  */

  // It includes the specific files
require_once("../../config.php");
require_once("lib.php");

// Vabiables obtained from a POST or a GET
$id = optional_param('id', 0, PARAM_INT); // id de la instancia
// id of the instance
$action = optional_param('action', '', PARAM_ALPHA);

//action performed


// Checking the errors


// Given the id of the model of a course, find your description
$cm = get_coursemodule_from_id('interview', $id);


// If it's not found, shows an error
if (!$cm) {
	print_error(get_string('cmidincorrect', 'interview'));
}

// Compile the first relationship found that satisfies the restriction
//$course =$DB->get_record('course', 'id', $cm->course);
$course = $DB->get_record("course", array("id" => $cm->course));


// If none are found, shows an error
if (!$course) {
	print_error(get_string('cmisconfigured', 'interview'));
}


// Check that the actual user is released and has the necessary permissions
require_course_login($course, false, $cm);


// Compile the primary relationship of the interview table that satisfies the restriction
//$interview = $DB->get_record('interview', 'id', $cm->instance);
$interview = $DB->get_record('interview', array("id" => $cm->instance));

//if fails returns error
if (!$interview) {
	print_error(get_string('cmincorrect', 'interview'));
}

// It's used to control the recent activity conducted by the user
add_to_log($course->id, "interview", "view", "view.php?id=$cm->id", "$interview->id");

// Compile the most used names
$strinterview = get_string('modulename', 'interview');
$strinterviews = get_string('modulenameplural', 'interview');
$strchoose = get_string('choose', 'interview');
$strdate = get_string('date', 'interview');
$strstart = get_string('start', 'interview');
$strend = get_string('end', 'interview');
$strstudent = get_string('student', 'interview');
$straction = get_string('action', 'interview');
$strphoto = get_string('photo', 'interview');
$stremail = get_string('email', 'interview');

//The impression of the short name and the heading appear defective if it doesn't include
// the following code even if the form is possible to modify

//Show the short name of the course proportionate to the link that goes to the primary page
if ($course->category) {
	$navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
} else {
	$navigation = '';
}

// Muestra la cabecera
//$OUTPUT->header();
//$PAGE->set_button($OUTPUT->update_module_button($cm->id, $course->id, $strinterview));

//	(format_string($interview->name), '',
//"<a href=\"index.php?id=$course->id\">$strinterviews</a> -> ".format_string($interview->name),
//"", "", true, update_module_button($cm->id, $course->id, $strinterview), navmenu($course, $cm));
view_header($interview,$course,$cm);

// Take the context of the instance, if there isn't one create it
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

// If fails returns error
if (!$context) {
	print_error(get_string('badcontext', 'interview'));
}

echo '<br/>';

// Actions possible to carry out
switch ($action) {

	// Action: Elect a temporary string
	case 'mine':
		select($course);
		break;

	// Action: Assign an student a temporary string
	case 'assign':
		assign($course);
		break;

	// Action: Erases string
	case 'deleteslot':
		deleteslot($course,$cm);
		break;

	//Action: frees the string
	case 'freeslot' :
		freeslot($course);
		break;

}
// End of the actions


/************************************* PROFESSOR ***********************************/
/***********************************************************************************/

// If the actual user is a professor

$context = get_context_instance(CONTEXT_MODULE, $cm->id);

if (has_capability('mod/interview:edit',$context, $USER->id)) {
	echo '<H2>Is faculty</H2>';
}
elseif (has_capability('mod/interview:choose', $context, $USER->id)) {
	echo '<H2>Is student</H2>';
}

//should be has role faculty
if (has_capability('mod/interview:edit', get_context_instance(CONTEXT_COURSE, $course->id), $USER->id)) {

	// Shows the name of the instance in header format
	$pg_heading = ($interview->name);

	view_description($interview);


	/************** TABLA 1 **************/
	/*************************************/
	/************** TABLE 1 **************/
	/*************************************/

	$fac_slots_table = build_fac_slots_table($interview,$cm);
	// T’tulo de la tabla
	// Title of the table
	$tb1_heading = (get_string('slots', 'interview'));

	// shows the table centered on the screen
	echo '<center>';
	echo html_writer::table($fac_slots_table);
	echo '</center>';

	// Provides links to download the calculation pages
	echo "<br />\n";
	echo "<table class=\"downloadreport\" align=\"center\"><tr>\n";
	echo "<td>";
	$options = array();
	$options["id"] = "$cm->id";
	$options["download"] = "ods";
	$OUTPUT->single_button("report.php", get_string('downloadods'), 'post', $options);
	echo "</td><td>";
	$options["download"] = "xls";
	$OUTPUT->single_button("report.php", get_string('downloadexcel'), 'post', $options);
	echo "</td><td>";
	$options["download"] = "txt";
	$OUTPUT->single_button("report.php", get_string('downloadtext'), 'post', $options);
	echo "</td></tr></table>";
	echo '<br /><br />';


	/************** TABLE 2 **************/
	/*************************************/

	$stu_list_table = build_facstu_list_table($interview,$cm,$course);
	// collects the students in the course

	// Counts the elements of the array
	$numm = count($stu_list_table->data);


	// If there is data in the table that indicates that a studnet
	// has not picked a slot
	if ($numm > 0) {

		// Shows the table of students centered in the screen
		echo '<center>';
		echo html_writer::table($stu_list_table);
		echo '</center>';
	}

	/************************************* STUDENT ************************************/
	/*************************************************************************************/

	// If the user is not a professor, the user is a student
} elseif (has_capability('mod/interview:choose', get_context_instance(CONTEXT_COURSE, $course->id), $USER->id)) {

	// Shows the name of the instance formed in the header
	$heading = ($interview->name);

	// Muestra la descripci—n en un cuadro
	// Shows the description in a square
	if ($interview->description) {
		echo '<center>';
		print_simple_box(format_text($interview->description), 'center', '', '#eee');
		echo '</center>';
	}

	// If a place or professor has been established, is shown in another square
	if ($interview->location or $interview->teacher) {
		echo '<center>';
		print_simple_box_start('center', '', '#eee');
		if (!empty($interview->location)) {
			echo '<b>';
			echo get_string('location', 'interview');
			echo '</b>';
			echo ': ' . $interview->location;
		}
		if (!empty($interview->teacher)) {
			echo '<br/>';
			echo '<b>';
			echo get_string('teacher', 'interview');
			echo '</b>';
			echo ': ' . $interview->teacher;
		}
		echo '</center>';
		print_simple_box_end();
	}

	// Compiles the temporary strings ordered by id
	$slots = get_records('interview_slots', 'interviewid', $interview->id, 'id');

	// Defines the headers and the alignment on the table Horary Strings
	$table->head = array($strdate, $strstart, $strend, $strchoose);
	$table->align = array('CENTER', 'CENTER', 'CENTER', 'CENTER');
	$table->width = array('', '', '', '');

	// For each of the temporary strings
	foreach ($slots as $slot) {

		// If a student is not assigned and their time has passed,
		// it's erased and goes on to the next iteration of foreach
		if ($slot->student == 0 and $slot->ending < time()) {
			delete_records('interview_slots', 'id', $slot->id);
			continue;
		}

		// If the user already has an assigned temporary string
		if ($slot->student == $USER->id) {

			// Establishes the form that shows the data of the choice
			$starttime = userdate($slot->start, get_string('strftimetime'));
			$endtime = userdate($slot->ending, get_string('strftimetime'));
			$startdate = userdate($slot->start, get_string('strftimedateshort'));

			// square of text were the choice is shown
			print_simple_box_start('center');
			echo '<center>';
			echo '<b>';
			echo format_text(get_string('yourselection', 'interview'));
			echo '</b>';
			echo format_text(get_string('date', 'interview') . ': ' . $startdate);
			echo format_text(get_string('hour', 'interview') . ': ' . $starttime . ' - ' . $endtime);

			// Provides the option to change the selected string
			echo "[<a href=\"view.php?action=change&amp;id=$cm->id&amp;slotid=$slot->id\">" . get_string('change', 'interview') . '</a>]';
			echo '</center>';
			print_simple_box_end();
		}

		// Does not show the temporary strings that have been
		// assigned to another student
		if ($slot->student != 0) {
			continue;
		}

		// defines the form that shows the date of the session
		$starttime = userdate($slot->start, get_string('strftimetime'));
		$endtime = userdate($slot->ending, get_string('strftimetime'));
		$startdate = userdate($interview->timeopen, get_string('strftimedateshort'));

		// establishes the link for the action
		$actions = '<span style="font-size: x-small;">';

		// Action to pick a temporary string
		$actions .= "[<a href=\"view.php?action=mine&amp;id=$cm->id&amp;interviewid=$interview->id&amp;slotid=$slot->id\">" . get_string('assign', 'interview') . '</a>]';
		$actions .= '</span>';

		// Inserts the data in the table
		$table->data[] = array($startdate, $starttime, $endtime, $actions);
	}

	// If the strings have been self erasing because the interview has expired
	if (empty($slots) and $interview->timeclose) {
		$heading = (get_string('expire', 'interview'));

		// If not, shows the table centered on the screen
	} else {

		// title of the table
		$heading = (get_string('slots', 'interview'));
		echo '<center>';
		print_table($table);
		echo '</center>';
		echo '<br /><br />';
	}
}
// Shows the footer of the page
$OUTPUT->footer();
?>

