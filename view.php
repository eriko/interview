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
//$PAGE->set_title(format_string($interview->name));
//$PAGE->set_heading( "<a href=\"index.php?id=$course->id\">$strinterviews</a> -> ".format_string($interview->name));
//$PAGE->set_button($OUTPUT->update_module_button($cm->id, $course->id, $strinterview));

//	(format_string($interview->name), '',
//"<a href=\"index.php?id=$course->id\">$strinterviews</a> -> ".format_string($interview->name),
//"", "", true, update_module_button($cm->id, $course->id, $strinterview), navmenu($course, $cm));


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
		deletslot($course);
		break;

	//Action: frees the string
	case 'freeslot' :
		freeslot($course);
		break;

	//Action: makes note
	case 'takedown' :
		takenote($course);
		break;

	// Action: Saves the note
	case 'savenote':
		savenoet($course);
		break;

	// Action: Modifies the note
	case 'modify':
		modifynote($course);
		break;

	// Action: changes the string
	case 'change' :
		modifynote($course);
		break;

}
// End of the actions


/************************************* PROFESSOR ***********************************/
/***********************************************************************************/

// If the actual user is a professor

if (has_capability('mod/interview:edit', get_context_instance(CONTEXT_COURSE, $course->id), $USER->id)) {
	echo '<H2>Is faculty</H2>';
}
elseif (has_capability('mod/interview:choose', get_context_instance(CONTEXT_COURSE, $course->id), $USER->id)) {
	echo '<H2>Is student</H2>';
}

//should be has role faculty
if (has_capability('mod/interview:edit', get_context_instance(CONTEXT_COURSE, $course->id), $USER->id)) {

	// Shows the name of the instance in header format
	$pg_heading = ($interview->name, 'center');

	view_description();


	/************** TABLA 1 **************/
	/*************************************/
	/************** TABLE 1 **************/
	/*************************************/

	$fac_slots_table = build_fac_slots_table($interview);
	// T’tulo de la tabla
	// Title of the table
	$tb1_heading = (get_string('slots', 'interview'), 'center');

	// Muestra la tabla centrada en la pantalla
	// shows the table centered on the screen
	echo '<center>';
	print_table($slots_table);
	echo '</center>';

	// Proporciona links para descargarse las hojas de c‡lculo
	// Provides links to download the calculation pages
	echo "<br />\n";
	echo "<table class=\"downloadreport\" align=\"center\"><tr>\n";
	echo "<td>";
	$options = array();
	$options["id"] = "$cm->id";
	$options["download"] = "ods";
	print_single_button("report.php", $options, get_string('downloadods'));
	echo "</td><td>";
	$options["download"] = "xls";
	print_single_button("report.php", $options, get_string('downloadexcel'));
	echo "</td><td>";
	$options["download"] = "txt";
	print_single_button("report.php", $options, get_string('downloadtext'));
	echo "</td></tr></table>";
	echo '<br /><br />';


	/************** TABLE 2 **************/
	/*************************************/


	// collects the students in the course
	$students = get_course_students($course->id, $sort = "u.lastname", $dir = "ASC");


	// If there are no students, will notify
	if (!$students) {
		notify(get_string('noexistingstudents'));

		// If there are students, creates a table with the users
		// that have not picked a horary string
	} else {


		// Defines the headings and alignments in the table of students
		$mtable->head = array($strphoto, $strstudent, $stremail); //, $straction);
		$mtable->align = array('CENTER', 'CENTER', 'CENTER'); //, 'CENTER');
		$mtable->width = array('', '', '', '');


		// Begins the link to send mail to all the
		// students that have not picked a string
		$mailto = '<a href="mailto:';

		// Para cada uno de los estudiantes
		// For each of the students
		foreach ($students as $student) {


			// If a relationship that complies with the restrictions does not exist
			if (!record_exists('interview_slots', 'student', $student->id, 'interviewid', $interview->id)) {


				// Shows the user image
				$picture = print_user_picture($student->id, $course->id, $student->picture, false, true);


				// Shows the full name in link format
				$name = "<a href=\"../../user/view.php?id=$student->id&amp;course=$interview->course\">" . fullname($student) . "</a>";


				// Creates a link to the mailto list for the user
				$email = obfuscate_mailto($student->email);


				// Incorporates the email of the student
				$mailto .= $student->email . ', ';


				//$slots =$DB->get_records('interview_slots', 'interviewid', $interview->id, 'id');
				// Compiles the temporary strings organized by id


				// For each one, if assigned to a student
				// passes to the next iteration of foreach
				//foreach ($slots as $slot) {
				//	if ($slot->student != 0) {
				//		continue;
				//	}
				//}


				// If the temporary strings are not empty
				//if (!empty($slots)) {


				// Creates an array
				//	$choices = array();


				// For each one, if a student is not assigned,
				// fills in the array with the horary strings
				//	foreach($slots as $slot) {
				//		if ($slot->student == 0) {
				//			$choices[$slot->id] = userdate($slot->start,  get_string('strftimetime')). ' - ' .userdate($slot->ending,  get_string('strftimetime'));
				//		}
				//	}


				// It creates the iteration menus with the availiable times
				//	$actions = "<form name=\"form".$student->id."\">";
				//	$actions .= choose_from_menu($choices, 'slotforstudent', '', 'choose', 'asignacion('.$cm->id.','.$student->id.',form'.$student->id.')', '0', true);
				//	$actions .= "</form>";


				// las acciones tambiŽn lo est‡n
				// If the temporary strings are empty
				// the actions are empty as well
				//} else {
				//	$actions = '';
				//}


				// Inserts the data in the table
				$mtable->data[] = array($picture, $name, $email); //, $actions);
			}
		}
	}

	// Counts the elements of the array
	$numm = count($mtable->data);



	// If there is data in the table that indicates that a studnet
	// has not picked a horary string
	if ($numm > 0) {


		// Shows the number of students that have not picked
		if ($numm == 1) {
			$heading = (get_string('missingstudent', 'interview'), 'center');
		} elseif ($numm > 1) {
			$heading = (get_string('missingstudents', 'interview', $numm), 'center');
		}

		// Creates the links to send invitations or reminders
		$strinvitation = get_string('invitation', 'interview');
		$strreminder = get_string('reminder', 'interview');

		// Eliminates the blank space and the final coma of $mailto
		$mailto = rtrim($mailto, ', ');

		// Invitation:
		// Specifies the topic of the email
		$subject = $strinvitation . ': ' . $interview->name;

		// Specifies the body of the message
		$body = "$strinvitation: $interview->name\n\n" .
				get_string('invitationtext', 'interview') .
				"{$CFG->wwwroot}/mod/interview/view.php?id=$cm->id";

		// Establishes the complete text to send
		echo '<center>' . get_string('composeemail', 'interview') .
				$mailto . '?subject=' . htmlentities(rawurlencode($subject)) .
				'&amp;body=' . htmlentities(rawurlencode($body)) .
				'"> ' . $strinvitation . '</a> ';

		// Reminder:
		// Establishes the topic of the email
		$subject = $strreminder . ': ' . $interview->name;

		// Establishes the body of the message
		$body = "$strreminder: $interview->name\n\n" .
				get_string('remindertext', 'interview') .
				"{$CFG->wwwroot}/mod/interview/view.php?id=$cm->id";

		// Establishes the complete text to send
		echo $mailto . '?subject=' . htmlentities(rawurlencode($subject)) .
				'&amp;body=' . htmlentities(rawurlencode($body)) .
				'"> ' . $strreminder . '</a></center><br />';

		// Shows the table of students centered in the screen
		echo '<center>';
		print_table($mtable);
		echo '</center>';
	}

	/************************************* STUDENT ************************************/
	/*************************************************************************************/

	// If the user is not a professor, the user is a student
} elseif (has_capability('mod/interview:choose', get_context_instance(CONTEXT_COURSE, $course->id), $USER->id)) {

	// Shows the name of the instance formed in the header
	$heading = ($interview->name, 'center');

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
		$heading = (get_string('expire', 'interview'), 'center');

		// If not, shows the table centered on the screen
	} else {

		// title of the table
		$heading = (get_string('slots', 'interview'), 'center');
		echo '<center>';
		print_table($table);
		echo '</center>';
		echo '<br /><br />';
	}
}
// Shows the footer of the page
$OUTPUT->footer($course);
?>

