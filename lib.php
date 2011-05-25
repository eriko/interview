<?php

/**
 * This php document contains all the functions
 * necessary for the correct execution of the model
 */






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
 * Makes an existing instance with new data
 */

function interview_update_instance($interview) {
	global $DB;
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
	if (isset($interview->intro)) {
		$opt->intro = $interview->intro;
	}

	return $DB->update_record('interview', $opt);
}

/**
 * Given an identifier of the instance in this model,
 * this function will eliminate the permanent form if the instance
 * and whatever function depends on it.
 */

function interview_delete_instance($id) {
	global $DB;
	// Compile the primary relationship of the interview table that fulfills the restriction
	$interview = $DB->get_record('interview', array('id' => $id));

	// If there is no relationship in the table that has the id you're looking for return false
	if (!$interview) {
		return false;
	}

	// Initialize the variable that will contain the value to return
	$result = true;

	// Eliminate whatever relationship dependant on the previous, if in any moment
	// something fails, $result becomes false
	if (!$DB->delete_records('interview', array('id' => $interview->id))) {
		$result = false;
	}

	if (!$DB->delete_records('interview_slots', array('interviewid' => $interview->id))) {
		$result = false;
	}

	// Si todo se ha realizado correctamente devuelve verdadero
	return $result;
}

function select($course, $cm) {

	global $USER, $DB;

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
	$chosenslot = $DB->get_record('interview_slots', array('id' => $slotid));

	// If fails returns error
	if (!$chosenslot) {
		error(get_string('invalidslotid', 'interview'));
	}

	if (has_capability('mod/interview:choose', $context, $USER->id)){
	// Compiles all the temporary strings
	$slots = $DB->get_records('interview_slots', array('interviewid' => $interviewid));

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


	// Makes the slot. If it's not possible, returns error
	if (!$DB->update_record('interview_slots', $chosenslot)) {
		print_error(get_string('notsaved', 'interview'));
	}

	// It's used to control the recent activity carried out by the user
	add_to_log($course->id, "interview", "choose", "view.php?id=$cm->id", "$interviewid");
	}else{
		print_error(get_string('notallowed', 'interview'));
		// It's used to control the recent activity carried out by the user
		add_to_log($course->id, "interview", "choose", "view.php?id=$cm->id", "$interviewid");
	}
}

function assign($course, $cm) {
	global $DB;
	// It picks up the necessary variables
	$id = required_param('id', PARAM_INT);
	$slotid = required_param('slotid', PARAM_INT);
	$studentid = required_param('studentid', PARAM_INT);

	// Checks the errors

	// If it hasn't selected a temporary string it will inform you
	if (!$slotid) {
		notice(get_string('notselected', 'interview'), "view.php?id=$cm->id");
	}

	// Compiles the selected empty slot
	$slot = $DB->get_record('interview_slots', array('id' => $slotid));

	// If fails returns error
	if (!$slot) {
		print_error(get_string('invalidslotid', 'interview'));


		// It saves the new data
		$slot->id = $slotid;
		$slot->student = $studentid;
		$slot->timemodified = time();

		// Makes the string. If it's not possible, returns error
		if (!$DB->update_record('interview_slots', $slot)) {
			print_error(get_string('notassign', 'interview'));
		}

		// It's used to control the recent activity performed by the user
		add_to_log($course->id, "assignslot", "view", "view.php?id=$cm->id", "$interview->id");
	}
}

function hideslot($course, $cm, $interview) {
	global $DB;

	// Picks up the necessary parameters
	$id = required_param('id', PARAM_INT);
	$slotid = required_param('slotid', PARAM_INT);

	// Compiles the selected empty slot
	$slot = $DB->get_record('interview_slots', array('id' => $slotid));

	//free the slot if it has already been selcted
	if ($slot->student == null) {
		$stot->student = null;
	}
	//hide the slot
	$slot->available = false;
	$slot->timemodified = time();

	$DB->update_record('interview_slots', $slot);
	add_to_log($course->id, "interview", "hideslot", "view.php?id=$cm->id", $interview->id, $cm->id);


}

function unhideslot($course, $cm, $interview) {
	global $DB;

	// Picks up the necessary parameters
	$id = required_param('id', PARAM_INT);
	$slotid = required_param('slotid', PARAM_INT);

	// Compiles the selected empty slot
	$slot = $DB->get_record('interview_slots', array('id' => $slotid));

	//make it unhide
	$slot->available = true;
	$slot->timemodified = time();

	$DB->update_record('interview_slots', $slot);
	add_to_log($course->id, "interview", "hideslot", "view.php?id=$cm->id", $interview->id, $cm->id);


}

function release($course, $cm) {
	global $DB;
	// Picks up the necessary parameters
	$id = required_param('id', PARAM_INT);
	$slotid = required_param('slotid', PARAM_INT);
	$studentid = required_param('studentid', PARAM_INT);

	// Compiles the selected empty slot
	$slot = $DB->get_record('interview_slots', array('id' => $slotid));
	if ($slot->student == $studentid) {
		//It frees the selected string, eliminating
		// the user that it compiles and leaves it free
		// to be selected by another user
		$studentid = 0;
		$slot->id = $slotid;
		$slot->student = $studentid;
		$slot->timemodified = time();

		// It Makes the temporary string. If something
		// fails, returns error
		if (!$DB->update_record('interview_slots', $slot)) {
			error(get_string('notupdated', 'interview'));
		} else {
			redirect("view.php?id=$cm->id", get_string('updating', 'interview'));
		}

		// Controls the recent activity done by the users
		add_to_log($course->id, "interview", "release", "view.php?id=$cm->id", $interview->id, $cm->id);

	}
}

function freeslot($course, $cm) {
	global $DB;
	// Picks up the necessary parameters
	$slotid = required_param('slotid', PARAM_INT);

	//It frees the selected string, eliminating
	// the user that it compiles and leaves it free
	// to be selected by another user
	$slot->id = $slotid;
	$slot->student = 0;
	$slot->timemodified = time();

	// It Makes the temporary slot. If something
	// fails, returns error
	if (!$DB->update_record('interview_slots', $slot)) {
		error(get_string('notupdated', 'interview'));
	} else {
		redirect("view.php?id=$cm->id", get_string('updating', 'interview'));
	}

	// Controls the recent activity done by the users
	add_to_log($course->id, "interview", "freeslot", "view.php?id=$cm->id", $interview->id, $cm->id);
}



function  get_course_students($courseid, $sort, $dir) {
	global $DB;
	$context = get_context_instance(CONTEXT_COURSE, $courseid);

	$query = 'select u.id as id, firstname, lastname, picture, imagealt, email from mdl_role_assignments as a, mdl_user as u where contextid=' . $context->id . ' and roleid=5 and a.userid=u.id order by ' . $sort . ' ' . $dir . ';';

	$rs = $DB->get_recordset_sql($query);
	return $rs;

}



/**
 * Adds module specific settings to the settings block
 *
 * @param settings_navigation $settings The settings navigation object
 * @param navigation_node $interviewnode The node to add module settings to
 */
function interview_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $interviewnode) {
    global $USER, $PAGE, $CFG, $DB, $OUTPUT;

    $interviewobject = $DB->get_record("forum", array("id" => $PAGE->cm->instance));
    if (empty($PAGE->cm->context)) {
        $PAGE->cm->context = get_context_instance(CONTEXT_MODULE, $PAGE->cm->instance);
    }


    // for some actions you need to be enrolled, beiing admin is not enough sometimes here
    $enrolled = is_enrolled($PAGE->cm->context, $USER, '', false);
    $activeenrolled = is_enrolled($PAGE->cm->context, $USER, '', true);

    $canmanage  = has_capability('mod/interview:manage', $PAGE->cm->context);

}


?>