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

// Take the context of the instance, if there isn't one create it
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

// If fails returns error
if (!$context) {
	print_error(get_string('badcontext', 'interview'));
}

echo '<br/>';

// Actions possible to carry out
switch ($action) {

	// Action: select a temporary slot
	case 'mine':
		select($course,$cm);
		break;

	// Action: Assign an student a time slot
	case 'assign':
		assign($course,$cm);
		break;

	// Action: release your time slot
	case 'release':
		release($course,$cm);
		break;

	// Action: Hides slot
	case 'hideslot':
		hideslot($course, $cm, $interview);
		break;

	// Action: unhides slot
	case 'unhideslot':
		unhideslot($course, $cm, $interview);
		break;

	//Action: frees the string
	case 'freeslot' :
		freeslot($course,$cm);
		break;

}
// End of the actions

//start laying out the page
// Muestra la cabecera
//$OUTPUT->header();
//$PAGE->set_button($OUTPUT->update_module_button($cm->id, $course->id, $strinterview));

//	(format_string($interview->name), '',
//"<a href=\"index.php?id=$course->id\">$strinterviews</a> -> ".format_string($interview->name),
//"", "", true, update_module_button($cm->id, $course->id, $strinterview), navmenu($course, $cm));

view_header($interview, $course, $cm);

/************************************* PROFESSOR ***********************************/
/***********************************************************************************/

// If the actual user is a professor

$context = get_context_instance(CONTEXT_MODULE, $cm->id);

if (has_capability('mod/interview:edit', $context, $USER->id)) {
	echo '<H2>Is faculty</H2>';
}
elseif (has_capability('mod/interview:choose', $context, $USER->id)) {
	echo '<H2>Is student</H2>';
}

//should be has role faculty
if (has_capability('mod/interview:edit', get_context_instance(CONTEXT_COURSE, $course->id), $USER->id)) {

	// Shows the name of the instance in header format
	$pg_heading = ($interview->name);

	view_intro($interview,$cm);


	/************** TABLA 1 **************/
	/*************************************/
	/************** TABLE 1 **************/
	/*************************************/

	$fac_slots_table = build_fac_slots_table($interview, $cm);
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
	echo $OUTPUT->single_button("report.php", get_string('downloadods'), 'post', $options);
	echo "</td><td>";
	$options["download"] = "xls";
	echo $OUTPUT->single_button("report.php", get_string('downloadexcel'), 'post', $options);
	echo "</td><td>";
	$options["download"] = "txt";
	echo $OUTPUT->single_button("report.php", get_string('downloadtext'), 'post', $options);
	echo "</td></tr></table>";
	echo '<br /><br />';


	/************** TABLE 2 **************/
	/*************************************/

	$stu_list_table = build_facstu_list_table($interview, $cm, $course);
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
	$stu_own_slot = build_stu_own_slots_table($interview, $cm);
	if ($stu_own_slot != null) {
		$OUTPUT->box_start('center');
		echo $stu_own_slot;
		$OUTPUT->box_end();
	}

 	//if ($stu_own_slot = null) {
	$stu_slots_table = build_stu_slots_table($interview, $cm);
	echo '<center>';
	echo html_writer::table($stu_slots_table);
	echo '</center>';
	//}
	
	// Shows the description in a square
	if ($interview->intro) {
		echo '<center>';
		$OUTPUT->box_start( 'center', '', '#eee');
		echo format_text($interview->intro);

		$OUTPUT->box_end();
		echo '</center>';
	}

	// If a place or professor has been established, is shown in another square
	if ($interview->location or $interview->teacher) {
		echo '<center>';
		$OUTPUT->box_start('center', '', '#eee');
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
		$OUTPUT->box_end();
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

