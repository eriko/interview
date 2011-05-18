<?php

/**
 * This php document contains all the functions
 * necessary for the correct execution of the model
 */

/**************************
 *  Standard functions
 **************************/

/**
 * Give an object containing all the necessary data,
 * (defined by the form in mod.html) this function
 * creates a new instance and returns the identifier
 * of the saying of the new instance.
 */
class interview_base {

	/**
	 * Constructor for the base assignment class
	 *
	 * Constructor for the base assignment class.
	 * If cmid is set create the cm, course, assignment objects.
	 * If the assignment is hidden and the user is not a teacher then
	 * this prints a page header and notice.
	 *
	 * @global object
	 * @global object
	 * @param int $cmid the current course module id - not set for new assignments
	 * @param object $assignment usually null, but if we have it we pass it to save db access
	 * @param object $cm usually null, but if we have it we pass it to save db access
	 * @param object $course usually null, but if we have it we pass it to save db access
	 */
	function interview_base($cmid = 'staticonly', $assignment = NULL, $cm = NULL, $course = NULL) {
		global $COURSE, $DB;

		if ($cmid == 'staticonly') {
			//use static functions only!
			return;
		}

		global $CFG;

		if ($cm) {
			$this->cm = $cm;
		} else if (!$this->cm = get_coursemodule_from_id('interview', $cmid)) {
			print_error('invalidcoursemodule');
		}

		$this->context = get_context_instance(CONTEXT_MODULE, $this->cm->id);

		if ($course) {
			$this->course = $course;
		} else if ($this->cm->course == $COURSE->id) {
			$this->course = $COURSE;
		} else if (!$this->course = $DB->get_record('course', array('id' => $this->cm->course))) {
			print_error('invalidid', 'interview');
		}

		if ($assignment) {
			$this->assignment = $assignment;
		} else if (!$this->assignment = $DB->get_record('interview', array('id' => $this->cm->instance))) {
			print_error('invalidid', 'interview');
		}

		$this->assignment->cmidnumber = $this->cm->idnumber; // compatibility with modedit interview obj
		$this->assignment->courseid = $this->course->id; // compatibility with modedit interview obj

		$this->strinterview = get_string('modulename', 'interview');
		$this->strinterviews = get_string('modulenameplural', 'interview');
		$this->strlastmodified = get_string('lastmodified');
		$this->pagetitle = strip_tags($this->course->shortname . ': ' . $this->strassignment . ': ' . format_string($this->assignment->name, true));

		// visibility handled by require_login() with $cm parameter
		// get current group only when really needed

		/// Set up things for a HTML editor if it's needed
		$this->defaultformat = editors_get_preferred_format();
	}


}

/****************************
 ** Specific functions  **
 ****************************/

/**
 * This function is in charge of showing a form
 * where the user can introduce the entry
 * pertinent to the chosen strip(***might mean string, not sure about this one*** will notate with "***")
 */


/**
 * Display the interview, used by view.php
 *
 * This in turn calls the methods producing individual parts of the page
 */


function view_description($interview) {
	global $OUTPUT;
	// Shows the description of the interview in a box
	if ($interview->description) {
		echo '<center>';
		$OUTPUT->box(format_text($interview->description), 'center', '', '#eee');
		echo '</center>';
	}

	// If a place or professor has been established it's shown in another box
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
		$OUTPUT->box_end();
		echo '</center>';
	}
}

function view_header($interview, $course, $cm) {
	global $CFG, $PAGE, $OUTPUT;
	$PAGE->set_url('/mod/interview/view.php', array('id' => $cm->id));
	$strinterviews = get_string('modulenameplural', 'interview');
	$PAGE->set_title(format_string($interview->name));
	$PAGE->set_heading("<a href=\"index.php?id=$course->id\">$strinterviews</a> -> " . format_string($interview->name));

	echo $OUTPUT->header();

	groups_print_activity_menu($cm, $CFG->wwwroot . '/mod/inteview/view.php?id=' . $cm->id);

	echo '<div class="clearer"></div>';
}

function interview_add_instance($interview) {
	global $CFG, $DB;
	$interview->timemodified = time();

	// Insert a relationship in the interview table and return the identifier
	// If inserted correctly, create a relationship corresponding in the table
	// interview_slots
	if ($interview->id = $DB->insert_record("interview", $interview)) {
		for ($i = $interview->timeopen; $i < $interview->timeclose; $i += $interview->timeslot * 60) {
			$slot = new stdClass();
			$slot->interviewid = $interview->id;
			$slot->start = $i;
			$slot->ending = $i + $interview->timeslot * 60;
			$slot->timemodified = time();
			if ($interview->timeblock > 0) {
				$timeopen = ($CFG->{"interview_timeblock" . $interview->timeblock . "open"}) * 100;
				$timeclose = ($CFG->{"interview_timeblock" . $interview->timeblock . "close"}) * 100;
				$start = intval(date('Gi', $slot->start));
				$end = intval(date('Gi', $slot->ending));

				if ($start >= $timeopen && $start < $timeclose && $end <= $timeclose && $end > $timeopen) {
					$DB->insert_record("interview_slots", $slot);
				}
			}
			else
			{
				$DB->insert_record("interview_slots", $slot);
			}
		}
	}
	//return the id of the interview
	return $interview->id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the formula in mod.html) this function
 * actualizes an existing instance with new data
 */

function interview_update_instance($interview) {

	// Create a new object
	$opt = new object();
	$opt->timemodified = time();
	$opt->id = $interview->instance;

	// If a location has been established, assign it
	if (isset($interview->location)) {
		$opt->location = $interview->location;
	}

	// If a professor has been established, assign it
	if (isset($interview->teacher)) {
		$opt->teacher = $interview->teacher;
	}

	// If a name has been established, assign it
	if (isset($interview->name)) {
		$opt->name = $interview->name;
	}

	// If a description has been established, assign it
	if (isset($interview->description)) {
		$opt->description = $interview->description;
	}

	return update_record('interview', $opt);
}

/**
 * Given an identifier of the instance in this model,
 * this function will eliminate the permanent form if the instance
 * and whatever function depends on it.
 */

function interview_delete_instance($id) {

	// Compile the primary relationship of the interview table that fulfills the restriction
	$interview = get_record('interview', 'id', $id);

	// If there is no relationship in the table that has the id you're looking for return false
	if (!$interview) {
		return false;
	}

	// Initialize the variable that will contain the value to return
	$result = true;

	// Eliminate whatever relationship dependant on the previous, if in any moment
	// something fails, $result becomes false
	if (!delete_records('interview', 'id', $interview->id)) {
		$result = false;
	}

	if (!delete_records('interview_slots', 'interviewid', $interview->id)) {
		$result = false;
	}

	// Si todo se ha realizado correctamente devuelve verdadero
	return $result;
}

function select($course) {

	global $USER;

	// It picks up the necessary variables
	$id = required_param('id', PARAM_INT);
	$interviewid = required_param('interviewid', PARAM_INT);
	$slotid = required_param('slotid', PARAM_INT);

	// checks the errors

	// If it hasn't selected a temporary string it informs you of that
	if (!$slotid) {
		notice(get_string('notselected', 'interview'), "view.php?id=$cm->id");
	}

	// compiles the selected temporary string
	$chosenslot = $DB->get_record('interview_slots', 'id', $slotid);

	// If fails returns error
	if (!$chosenslot) {
		error(get_string('invalidslotid', 'interview'));
	}


	// Compiles all the temporary strings
	$slots = get_records('interview_slots', 'interviewid', $interviewid, 'id');

	// For each one
	foreach ($slots as $slot) {

		// If the actual user has selected one, it informs you
		// that it can only assign a unique string
		if ($slot->student == $USER->id) {
			notice(get_string('oneslot', 'interview'), "view.php?id=$cm->id");
		}
	}

	// It saves the new data
	$chosenslot->id = $slotid;
	$chosenslot->student = $USER->id;
	$chosenslot->timemodified = time();


	// Actualizes the string. If it's not possible, returns error
	if (!update_record('interview_slots', $chosenslot)) {
		print_error(get_string('notsaved', 'interview'));
	}

	// It's used to control the recent activity carried out by the user
	add_to_log($course->id, "interview", "choose", "view.php?id=$cm->id", "$interview->id");
}

function assign($course) {
	// It picks up the necessary variables
	$id = required_param('id', PARAM_INT);
	$slotid = required_param('slotid', PARAM_INT);
	$studentid = required_param('studentid', PARAM_INT);

	// Checks the errors

	// If it hasn't selected a temporary string it will inform you
	if (!$slotid) {
		notice(get_string('notselected', 'interview'), "view.php?id=$cm->id");
	}

	// Compiles the selected temporary string
	$slot = $DB->get_record('interview_slots', 'id', $slotid);

	// If fails returns error
	if (!$slot) {
		print_error(get_string('invalidslotid', 'interview'));


		// It saves the new data
		$slot->id = $slotid;
		$slot->student = $studentid;
		$slot->timemodified = time();

		// Actualizes the string. If it's not possible, returns error
		if (!update_record('interview_slots', $slot)) {
			print_error(get_string('notassign', 'interview'));
		}

		// It's used to control the recent activity performed by the user
		add_to_log($course->id, "assignslot", "view", "view.php?id=$cm->id", "$interview->id");
	}
}

function deleteslot($course,$cm) {
	global $DB;

	// Picks up the necessary parameters
	$id = required_param('id', PARAM_INT);
	$slotid = required_param('slotid', PARAM_INT);

	// It erases the seleted string. If all goes well, it addresses
	// to the user. If not, returns error
	if (!$DB->delete_records('interview_slots', array('id' => $slotid))) {
		error(get_string('notdeleted', 'interview'));
	} else {
		redirect("view.php?id=$cm->id", get_string('deleting', 'interview'));
	}

	//Controls the recent activity done by the users
	add_to_log($course->id, "interview", "deleteslot", "view.php?id=$cm->id", $interview->id, $cm->id);
}

function freeslot($course) {

	// Picks up the necessary parameters
	$id = required_param('id', PARAM_INT);
	$slotid = required_param('slotid', PARAM_INT);
	$studentid = required_param('studentid', PARAM_INT);

	//It frees the selected string, eliminating
	// the user that it compiles and leaves it free
	// to be selected by another user
	$studentid = 0;
	$slot->id = $slotid;
	$slot->student = $studentid;
	$slot->timemodified = time();

	// It actualizes the temporary string. If something
	// fails, returns error
	if (!update_record('interview_slots', $slot)) {
		error(get_string('notupdated', 'interview'));
	} else {
		redirect("view.php?id=$cm->id", get_string('updating', 'interview'));
	}

	// Controls the recent activity done by the users
	add_to_log($course->id, "interview", "freeslot", "view.php?id=$cm->id", $interview->id, $cm->id);
}

function build_fac_slots_table($interview, $cm) { // Compiles the slot strings by id
	global $DB;
	$conditions = array("interviewid" => $interview->id);
	$slots = $DB->get_records('interview_slots', $conditions , " start ASC");
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
			$DB->delete_records('interview_slots', 'id', $slot->id);
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
			$student = $DB->get_record('user', 'id', $slot->student);
			// Shows the picture of the user
			$picture = $OUTPUT->user_picture(student);
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
		$actions .= "[<a href=\"view.php?action=deleteslot&amp;id=$cm->id&amp;slotid=$slot->id\">" . get_string('delete') . '</a>]';

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

function  get_course_students($courseid, $sort, $dir) {
	global $DB;
	$context = get_context_instance(CONTEXT_COURSE, $courseid);

	$query = 'select u.id as id, firstname, lastname, picture, imagealt, email from mdl_role_assignments as a, mdl_user as u where contextid=' . $context->id . ' and roleid=5 and a.userid=u.id order by ' . $sort . ' ' . $dir . ';';

	$rs = $DB->get_recordset_sql($query);
	return $rs;

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

?>