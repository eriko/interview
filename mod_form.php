<?php

/**
 * Este archivo php establece la forma en la que se presenta el
 * formulario principal del módulo Entrevista Personal
 */


	// Se incluye el fichero especificado que se encarga de aportar métodos extra para darle forma al formulario
	require_once ($CFG->dirroot.'/course/moodleform_mod.php');

	class mod_interview_mod_form extends moodleform_mod {

    function definition() {
        global $CFG;
        $mform    =& $this->_form;

//-------------------------------------------------------------------------------
// General settings: name and interview description

        //Header
        $mform->addElement('header', 'general', get_string('general', 'form'));

		// Name
        $mform->addElement('text', 'name', get_string('name'),'maxlength="100"');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

		// Description
		$this->add_intro_editor(true, get_string('intro', 'interview'));
        //$mform->addElement('htmleditor', 'info', get_string('info'));
        //$mform->setType('text', PARAM_RAW);
        //$mform->addRule('info', null, 'required', null, 'client');
        //$mform->setHelpButton('description', array('writing', 'questions', 'richtext'), false, 'editorhelpbutton');

//-------------------------------------------------------------------------------
//Set the limit to the interview session and the length of each slot

        // Header date
        $mform->addElement('header', 'date', get_string('date', 'interview'));

        // Start Date (Day / Month / Year / Hour / Minute)
        $mform->addElement('date_time_selector', 'timeopen', get_string('timeopen', 'interview'),array("step"=>60,'optional'=>false));
        $mform->addHelpButton('timeopen', 'duration', 'interview');

        // End Date (Day / Month / Year / Hour / Minute)
        $mform->addElement('date_time_selector', 'timeclose', get_string('timeclose', 'interview'),array("step"=>60,'optional'=>false));
        $mform->addHelpButton('timeclose', 'duration', 'interview');
        // Header slots
        $mform->addElement('header', 'slots', get_string('slots', 'interview'));

        $options=array();
        $options[0]  = get_string('norestriction', 'interview');
        if ($CFG->interview_timeblock1enabled){
        $options[1]  = $CFG->interview_timeblock1name;
        }
        if ($CFG->interview_timeblock2enabled){
        $options[2]  = $CFG->interview_timeblock2name;
        }
        if ($CFG->interview_timeblock3enabled){
        $options[3]  = $CFG->interview_timeblock3name;
        }
        if ($CFG->interview_timeblock4enabled){
        $options[4]  = $CFG->interview_timeblock4name;
        }
        $mform->addElement('select', 'timeblock', get_string('timeblock', 'interview'), $options);

        // Length of each slot
		$mform->addElement('text', 'timeslot', get_string('timeslot','interview'),'maxlength="3"');
        $mform->setType('timeslot', PARAM_INT);
        $mform->addRule('timeslot', null, 'required', null, 'client');
		$mform->addHelpButton('timeslot', 'duration', 'interview');

//-------------------------------------------------------------------------------
// Set the date and place of the teacher in charge

        // Header: Citation Details
        $mform->addElement('header', 'details', get_string('details', 'interview'));

        // Meeting place
        $mform->addElement('text', 'location', get_string('location','interview'),'maxlength="50"');

        //Professor in charge
        $mform->addElement('text', 'teacher', get_string('teacher','interview'),'maxlength="50"');

//-------------------------------------------------------------------------------
// Set the visibility

        $mform->addElement('modvisible', 'visible', get_string('visible'));
		$this->standard_hidden_coursemodule_elements();
//-------------------------------------------------------------------------------
// Set the buttons Save and Cancel changes

        $this->add_action_buttons();
    }

        function definition_after_data() {
        parent::definition_after_data();
        global $COURSE;
        $mform    =& $this->_form;
        $coursemodule =& $mform->getElementValue('coursemodule');
        if ($coursemodule ){
            $mform->hardFreeze('timeopen');
            $mform->hardFreeze('timeclose');
            $mform->hardFreeze('timeslot');

        }
    }

    // Validación del formulario con comprobaciones de error al intentar enviar los datos
    function validation($data){

 // For this is all right must be met:
         // 1. Start Time> Current Time
         // 2. Closing Time> Current Time
         // 3. Closing Time> Time Start
         // 4. Minutes per band must be numeric
         // 5. Minutes per strip> 0 and different vacuum
         // 6. Closing time - start time> = Minutes per strip
         // 7. Session Length = Length Strip, where N is integer

		$days =  date('d', $data['timeclose']) - date('d', $data['timeopen']);

        // Get the start time of the session
        $a = date('H', $data['timeopen']);

        // Get the minutes of the start of the session
        $b = date('i', $data['timeopen']);

        // Spend all within minutes
        $minstart = $a*60+$b;

        // Get the time of closing of the session
        $c = date('H', $data['timeclose']);

        // Get the minutes of Logging
        $d = date('i', $data['timeclose']);

        // Spend all within minutes
        $minend = (($days*24)+$c)*60+$d;

        if ($data['timeopen']>=time() and $data['timeclose']>time() and $data['timeclose']>$data['timeopen'] and
            is_numeric($data['timeslot']) and $data['timeslot']>0 and
            ($minend - $minstart)>= $data['timeslot'] and
			is_int(($minend - $minstart)/$data['timeslot'])) {
            return true;

        // Establishing a time frame surrounding the opening and indicating that should be after the current date
        } elseif ($data['timeopen'] < time()) {
            return array('timeopen'=>get_string('timeopenfail', 'interview'));

        // Set a table around closing time and indicating that should be after the current date
        } elseif ($data['timeclose'] <= time()) {
            return array('timeclose'=>get_string('timeclosefail', 'interview'));

        // Establishing a time frame surrounding the closure and stating that time must be after the opening
        } elseif ($data['timeclose']<= $data['timeopen']) {
            return array('timeclose'=>get_string('timefail', 'interview'));

        // Set a frame around the slot duration and indicating that should provide a numerical value
        } elseif (!is_numeric($data['timeslot'])) {
            return array('timeslot'=>get_string('numeric', 'interview'));

        // Set a frame around the slot duration and indicating to be given a positive and non 0
        } elseif ($data['timeslot']<=0 ) {
            return array('timeslot'=>get_string('positive', 'interview'));

        // Set a frame around the slot duration stating that this should be less than the duration of the session
        } elseif ($minend - $minstart < $data['timeslot']) {
			$error = get_string('timeslotfail', 'interview') . $minend - $minstart. " minutes minend $minend - minstart $minstart over days $days";
            return array('timeslot'=> get_string('timeslotfail', 'interview'));

        // Set a frame around the slot duration and indicating to be given a value that fits snugly in the duration of
		// the session
		} elseif (!is_int(($minend - $minstart)/$data['timeslot'])) {
			return array('timeslot'=>get_string('fit', 'interview')."for ".$minend - $minstart." minutes");
		}
    }
}

?>