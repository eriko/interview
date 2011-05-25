<?php
/**
 * Created by JetBrains PhpStorm.
 * User: eriko
 * Date: 5/25/11
 * Time: 10:16 AM
 * To change this template use File | Settings | File Templates.
 */
 
class mod_interview_renderer extends plugin_renderer_base {


function build_stu_slots_table($interview, $cm) {
	global $DB, $USER;

	// Compiles the temporary strings ordered by id
	$slots = $DB->get_records('interview_slots', array("interviewid" => $interview->id), " start ASC");

	$strdate = get_string('date', 'interview');
	$strstart = get_string('start', 'interview');
	$strend = get_string('end', 'interview');
	$strchoose = get_string('choose', 'interview');
	$straction = get_string('action', 'interview');
	$strphoto = get_string('photo', 'interview');

	// Defines the headers and the alignment on the table Horary Strings
	$stu_slots_table = new html_table();
	$stu_slots_table->head = array($strdate, $strstart, $strend, $strchoose);
	$stu_slots_table->headspan = (get_string('slots', 'interview'));
	$stu_slots_table->align = array('CENTER', 'CENTER', 'CENTER', 'CENTER');
	$stu_slots_table->data = array();


	// For each of the temporary strings
	foreach ($slots as $slot) {

		// If a student is not assigned and their time has passed,
		// it's erased and goes on to the next iteration of foreach
		if ($slot->student == 0 and $slot->ending < time()) {
			$DB->delete_records('interview_slots', array('id' => $slot->id));
			continue;
		}

		// Does not show the temporary strings that have been
		// assigned to another student
		if ($slot->student != 0) {
			continue;
		}
		$row = array();
		// defines the form that shows the date of the session
		$row["starttime"] = userdate($slot->start, get_string('strftimetime'));
		$row["endtime"] = userdate($slot->ending, get_string('strftimetime'));
		$row["startdate"] = userdate($interview->timeopen, get_string('strftimedateshort'));

		// establishes the link for the action
		$actions = '<span style="font-size: x-small;">';

		// Action to pick a temporary string
		$actions .= "[<a href=\"view.php?action=mine&amp;id=$cm->id&amp;interviewid=$interview->id&amp;slotid=$slot->id\">" . get_string('assign', 'interview') . '</a>]';
		$actions .= '</span>';
		$row["actions"] = $actions;
		// Inserts the data in the table
		$stu_slots_table->data[] = $row;
	}
	return $stu_slots_table;
}

	function build_facstu_list_table($interview, $cm, $course) {
	global $DB, $OUTPUT;
	$strstudent = get_string('student', 'interview');
	$strphoto = get_string('photo', 'interview');
	$stremail = get_string('email', 'interview');

	// collects the students in the course
	$students = get_course_students($course->id, $sort = "u.lastname", $dir = "ASC");


	// If there are no students, will notify
	if (!$students) {
		$OUTPUT->notify(get_string('noexistingstudents'));

		// If there are students, creates a table with the users
		// that have not picked a horary string
	} else {


		// Defines the headings and alignments in the table of students
		$stu_list_table = new html_table();
		$stu_list_table->head = array($strphoto, $strstudent, $stremail);
		$stu_list_table->align = array('CENTER', 'CENTER', 'CENTER');
		$stu_list_table->data = array();


		// Begins the link to send mail to all the
		// students that have not picked a string
		$mailto = '<a href="mailto:';

		// Para cada uno de los estudiantes
		// For each of the students
		foreach ($students as $student) {
			$row = array();

			// If a relationship that complies with the restrictions does not exist
			if (!$DB->record_exists('interview_slots', array('student' => $student->id, 'interviewid' => $interview->id))) {


				// Shows the user image
				$picture = $OUTPUT->user_picture($student);


				$row["picture"] = $picture;
				// Shows the full name in link format
				$name = "<a href=\"../../user/view.php?id=$student->id&amp;course=$interview->course\">" . fullname($student) . "</a>";

				$row["name"] = $name;
				// Creates a link to the mailto list for the user
				$email = obfuscate_mailto($student->email);

				$row["email"] = $email;
				// Inserts the data in the table
				$stu_list_table->data[] = array($picture, $name, $email); //, $actions);
			}
		}
	}
	return $stu_list_table;

}

	function build_fac_slots_table($interview, $cm) {
		// Compiles the slot strings by id
		global $DB, $OUTPUT;
		$conditions = array("interviewid" => $interview->id);
		$slots = $DB->get_records('interview_slots', $conditions, " start ASC");
		$strdate = get_string('date', 'interview');
		$strstart = get_string('start', 'interview');
		$strend = get_string('end', 'interview');
		$strstudent = get_string('student', 'interview');
		$straction = get_string('action', 'interview');
		$strphoto = get_string('photo', 'interview');

		// Defines the headings and aligns the table horary strings
		$fac_slots_table = new html_table();
		$fac_slots_table->head = array($strdate, $strstart, $strend, $strphoto, $strstudent, $straction);
		$fac_slots_table->headspan = (get_string('slots', 'interview'));
		$fac_slots_table->align = array('CENTER', 'CENTER', 'CENTER', 'CENTER', 'CENTER', 'CENTER');
		$fac_slots_table->data = array();

		// For each of the temporary strings
		foreach ($slots as $slot) {

			// If a student is not assigned and its time has passed
			// it erases and it passes to the second iteration of the foreach

			if ($slot->student == 0 and $slot->ending < time()) {
				$DB->delete_records('interview_slots', array('id'=>$slot->id));
				continue;
			}
			$row = array();
			// Defines the form that shows the date of the session
			// and the starting and ending times of each string
			$row["date"] = userdate($slot->start, get_string('strftimedateshort'));
			$row["start"] = userdate($slot->start, get_string('strftimetime'));
			$row["ending"] = userdate($slot->ending, get_string('strftimetime'));
			// If the horary string has been selecte by a student
			if ($slot->student) {
				// Compiles the user
				$student = $DB->get_record('user', array('id' => $slot->student));
				// Shows the picture of the user
				$picture = $OUTPUT->user_picture($student);
				// shows the full name of the user in a formatted link
				$name = "<a href=\"view.php?action=viewstudent&amp;id=$cm->id&amp;studentid=$student->id&amp;course=$interview->course&amp;order=DESC\">" . fullname($student) . '</a>';

				// If the horary string has not been selected by a student
			} else {
				$picture = '';
				$name = '';
			}
			$row["picture"] = $picture;
			$row["name"] = $name;

			// Establishes the links for the actions
			$actions = '<span style="font-size: x-small;">';
			// Action to erase
			if ($slot->available == true && $slot->student == 0) {
				$actions .= "[<a href=\"view.php?action=hideslot&amp;id=$cm->id&amp;slotid=$slot->id\">" . get_string('hide', 'interview') . '</a>]';
			}
			if ($slot->available == false) {
				$actions .= "[<a href=\"view.php?action=unhideslot&amp;id=$cm->id&amp;slotid=$slot->id\">" . get_string('unhide', 'interview') . '</a>]';
			}
			// If the temporary string already is assigned to a student
			if ($slot->student != 0) {

				// Action to free

				$actions .= "[<a href=\"view.php?action=freeslot&amp;id=$cm->id&amp;slotid=$slot->id&amp;studentid=$slot->student\">" . get_string('free', 'interview') . '</a>]';
			}


			$actions .= '</span>';
			$row["actions"] = $actions;

			// Inserts data in the table
			$fac_slots_table->data[] = $row;
		}
		return $fac_slots_table;
	}


	function build_stu_own_slots_table($interview, $cm) {
	global $DB, $USER;
	// Compiles the temporary strings ordered by id
	$slots = $DB->get_records('interview_slots', array("interviewid" => $interview->id, "student" => $USER->id), " start ASC");

	$thier_slot = null;
	// For each of the temporary strings
	foreach ($slots as $slot) {

		// If the user already has an assigned temporary string
		if ($slot->student == $USER->id) {

			$thier_slot = "";
			// Establishes the form that shows the data of the choice
			$starttime = userdate($slot->start, get_string('strftimetime'));
			$endtime = userdate($slot->ending, get_string('strftimetime'));
			$startdate = userdate($slot->start, get_string('strftimedateshort'));

			// square of text were the choice is shown
			$thier_slot = "";
			$thier_slot .= '<center>';
			$thier_slot .= '<b>';
			$thier_slot .= format_text(get_string('yourselection', 'interview'));
			$thier_slot .= '</b>';
			$thier_slot .= format_text(get_string('date', 'interview') . ': ' . $startdate);
			$thier_slot .= format_text(get_string('hour', 'interview') . ': ' . $starttime . ' - ' . $endtime);

			// Provides the option to change the selected string
			$thier_slot .= "[<a href=\"view.php?action=release&amp;id=$cm->id&amp;slotid=$slot->id&amp;studentid=$slot->student\">" . get_string('change', 'interview') . '</a>]';
			$thier_slot .= '</center>';
		}
	}

	return $thier_slot;

}
	
}