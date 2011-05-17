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

	/****************************
	 ** Specific functions  **
	 ****************************/

	/**
	 * This function is in charge of showing a form
	 * where the user can introduce the entry
	 * pertinent to the chosen strip(***might mean string, not sure about this one*** will notate with "***")
	 */

	function interview_take_notes($slotid, $id, $course) {

		// Compile the context of the instance like an object and if there isn't one, create it
		$context = get_context_instance(CONTEXT_MODULE, $id);

		// Compile the temporary strip*** that you want to take notes from
		$slot = get_record('interview', 'id', $slotid);

		// If you have the capability to make notations
		if (has_capability('mod/interview:takenotes', $context)) {

			// If you don't have an established notation
			if (empty($slot->notes)) {

				// Create the form
				echo '<center>';
				echo "<table cellpadding=\"20\" cellspacing=\"20\" class=\"boxaligncenter\"><tr>";
				echo "<td align=\"center\" valign=\"top\">";
				print_heading(get_string('addnote', 'interview'), 'center');
				print_textarea($usehtmleditor, 5, 60, 200, 100, 'note');
				echo "<br/>";
				echo '<input type="hidden" name="action" value="savenote" />';
				echo '<input type="hidden" name="id" value="' . $id . '" />';
				echo '<input type="hidden" name="slotid" value="' . $slotid . '" />';
				echo '<input type="submit" value="';
				echo get_string('accept', 'interview');
				echo '"/>';
				echo "</td>";
				echo "</tr>";
				echo "</table>";
				echo '</center>';

				//If the strip*** already has a note showing an error
				// indicating that it can't be overwritten
			} else {
				error(get_string('notealready', 'interview'));
			}
		}

		// Muestra el pie de página
		//Show the footer
		print_footer($course);
		exit;
	}

	/**
	 *
	 * This function is in charge of showing the form
	 * where the user can modify the notation
	 * previously made about the horary strip***
	 */

	function interview_modify_notes($note, $slotid, $id, $course) {

		// Compile the context of the instance like an object and if there isn't one, create it
		$context = get_context_instance(CONTEXT_MODULE, $id);

		// Compile the temporary strip*** that you want to take notes from
		$slot = get_record('interview', 'id', $slotid);

		// If it has the capability to modify the notations
		if (has_capability('mod/interview:modifynotes', $context)) {

			// It assures that the note previously exists
			if (!empty($note)) {

				// Create the form
				echo '<center>';
				echo "<table cellpadding=\"20\" cellspacing=\"20\" class=\"boxaligncenter\"><tr>";
				echo "<td align=\"center\" valign=\"top\">";
				print_heading(get_string('modifynote', 'interview'), 'center');
				print_textarea($usehtmleditor, 5, 60, 200, 100, 'note', $note);
				echo "<br/>";
				echo '<input type="hidden" name="action" value="savenote" />';
				echo '<input type="hidden" name="id" value="' . $id . '" />';
				echo '<input type="hidden" name="slotid" value="' . $slotid . '" />';
				echo '<input type="submit" value="';
				echo get_string('accept', 'interview');
				echo '"/>';
				echo "</td>";
				echo "</tr>";
				echo "</table>";
				echo '</center>';

				// If the note that you want to modify doesn't exist
				// shows an error
			} else {
				error(get_string('notnote', 'interview'));
			}
		}

		// Shows the footer
		print_footer($course);
		exit;
	}

	/**
	 * Display the interview, used by view.php
	 *
	 * This in turn calls the methods producing individual parts of the page
	 */
	function view() {

		$context = get_context_instance(CONTEXT_MODULE, $this->cm->id);
		require_capability('mod/assignment:view', $context);

		add_to_log($this->course->id, "assignment", "view", "view.php?id={$this->cm->id}",
			$this->assignment->id, $this->cm->id);

		$this->view_pg_header();


		$this->view_tb1 header();
		$this->view_table1();

		$this->view_tb2_header();
		$this->view_table2();


        $this->view_footer();
    }

	function view_description() {
		// Shows the description of the interview in a box
		if ($interview->description) {
			echo '<center>';
			print_simple_box(format_text($interview->description), 'center', '', '#eee');
			echo '</center>';
		}

		// If a place or professor has been established it's shown in another box
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
			print_simple_box_end();
			echo '</center>';
		}
	}

	function view_header($subpage = '') {
		global $CFG, $PAGE, $OUTPUT;

		if ($subpage) {
			$PAGE->navbar->add($subpage);
		}

		$PAGE->set_title($this->pagetitle);
		$PAGE->set_heading($this->course->fullname);

		echo $OUTPUT->header();

		groups_print_activity_menu($this->cm, $CFG->wwwroot . '/mod/inteview/view.php?id=' . $this->cm->id);

		echo '<div class="reportlink">' . $this->submittedlink() . '</div>';
		echo '<div class="clearer"></div>';
	}
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

function deleteslot($course) {

}
	// Picks up the necessary parameters
	$id = required_param('id', PARAM_INT);
	$slotid = required_param('slotid', PARAM_INT);

	// It erases the seleted string. If all goes well, it addresses
	// to the user. If not, returns error
	if (!delete_records('interview_slots', 'id', $slotid)) {
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

function takenote($course) {

	// Picks up the necessary parameters
	$id = required_param('id', PARAM_INT);
	$slotid = required_param('slotid', PARAM_INT);


	// It shows a form where the professor can
	// add notes in the actual string

	echo '<form id="form" method="post" action="view.php" align="center">';
	interview_take_notes($slotid, $id, $course);
	echo '</form>';

	// It's used to control the recent activity done by the user
	add_to_log($course->id, "interview", "takenotes", "view.php?id=$cm->id", "$interview->id");

}

function savenote($course) {
	//Picks up the necessary parameters
	$id = required_param('id', PARAM_INT);
	$slotid = required_param('slotid', PARAM_INT);
	$note = required_param('note', PARAM_TEXT);

	// Almacena la franja horaria sobre la que se quiere anotar
	$slot = $DB->get_record('interview_slots', 'id', $slotid);

	// Compiles the horary string over the one you want to record
	$slot = get_record('interview_slots', 'id', $slotid);

	// Se guardan los nuevos datos
	// It saves the new data

	$slot->id = $slotid;
	$slot->note = $note;
	$slot->timemodified = time();

	// It actualizes the string. If not possible returns error
	if (!update_record('interview_slots', $slot)) {
		print_error(get_string('notupdated', 'interview'));
	} else {
		redirect("view.php?id=$cm->id", get_string('updating', 'interview'));
	}
}

function modifynote($course) {

	// Picks up the necessary parameters
	$id = required_param('id', PARAM_INT);
	$slotid = required_param('slotid', PARAM_INT);
	$note = required_param('note', PARAM_TEXT);


	// It shows a form where the professor can
	// modify the notes done in the actual string
	echo '<form id="form" method="post" action="view.php" align="center">';
	interview_modify_notes($note, $slotid, $id, $course);
	echo '</form>';

	// It's used to control the recent activity done by the user

	add_to_log($course->id, "modifynotes", "view", "view.php?id=$cm->id", "$interview->id");
}

function modifynote($course) {

	// Picks up the necessary parameters
	$id = required_param('id', PARAM_INT);
	$slotid = required_param('slotid', PARAM_INT);

	// It eliminates the user assigned to a temporary string, so
	// it can pick another string that is free

	$slot = $DB->get_record('interview_slots', 'id', $slotid);
	$slot->id = $slotid;
	$slot->student = 0;
	$slot->timemodified = time();

	// It actualized the temporary string. If something
	// fails, returns error
	if (!update_record('interview_slots', $slot)) {
		print_error(get_string('notupdated', 'interview'));
	} else {
		redirect("view.php?id=$cm->id", get_string('updating', 'interview'));
	}

	// It's used to control the recent activity done by the users
	add_to_log($course->id, "change", "view", "view.php?id=$cm->id", "$interview->id");
}

function build_fac_slot_table($interview) { // Compiles the horary strings by id
	$slots = $DB->get_records('interview_slots', 'interviewid', $interview->id, 'id');
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
	
	// Defines the headings and aligns the table horary strings
	$table1->head = array($strdate, $strstart, $strend, $strphoto, $strstudent, $straction);
	$table1->align = array('CENTER', 'CENTER', 'CENTER', 'CENTER', 'CENTER', 'CENTER');
	$table1->width = array('', '', '', '', '', '');


	// For each of the temporary strings
	foreach ($slots as $slot) {

		// If a student is not assigned and its time has passed
		// it erases and it passes to the second iteration of the foreach

		if ($slot->student == 0 and $slot->ending < time()) {
			delete_records('interview_slots', 'id', $slot->id);
			continue;
		}

		// Defines the form that shows the date of the session
		// and the starting and ending times of each string
		$starttime = userdate($slot->start, get_string('strftimetime'));
		$endtime = userdate($slot->ending, get_string('strftimetime'));
		$startdate = userdate($slot->start, get_string('strftimedateshort'));
		// If the horary string has been selecte by a student
		if ($slot->student) {
			// Compiles the user
			$student = $DB->get_record('user', 'id', $slot->student);
			// Shows the picture of the user
			$picture = print_user_picture($slot->student, $course->id, $student->picture, false, true);
			// shows the full name of the user in a formatted link
			$name = "<a href=\"view.php?action=viewstudent&amp;id=$cm->id&amp;studentid=$student->id&amp;course=$interview->course&amp;order=DESC\">" . fullname($student) . '</a>';

			// If the horary string has not been selected by a student
		} else {
			$picture = '';
			$name = '';
		}

		// Establishes the links for the actions
		$actions = '<span style="font-size: x-small;">';
		// Action to erase
		$actions .= "[<a href=\"view.php?action=deleteslot&amp;id=$cm->id&amp;slotid=$slot->id\">" . get_string('delete') . '</a>]';

		// If the temporary string already is assigned to a student
		if ($slot->student != 0) {

			// Action to free

			$actions .= "[<a href=\"view.php?action=freeslot&amp;id=$cm->id&amp;slotid=$slot->id&amp;studentid=$slot->student\">" . get_string('free', 'interview') . '</a>]';
		}

		// If the temporary string has a note associated with it
		if (!empty($slot->note)) {

			// Actions to see and modify the note
			$actions .= "[<a onclick=\"openwindow(" . $slot->id . "," . $cm->id . ")\" >" . get_string('viewnote', 'interview') . "</a>]";
			$actions .= "[<a href=\"view.php?action=modify&amp;id=$cm->id&amp;slotid=$slot->id&amp;note=$slot->note\">" . get_string('modify', 'interview') . '</a>]';

			// If the temporary string does not have a note
		} else {

			// Action to take note
			$actions .= "[<a href=\"view.php?action=takedown&amp;id=$cm->id&amp;slotid=$slot->id\">" . get_string('takedown', 'interview') . '</a>]';
		}

		$actions .= '</span>';

		// Inserts data in the table
		$table1->data[] = array($startdate, $starttime, $endtime, $picture, $name, $actions);
	}
	return $table1;
}

?>