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
// Ajustes generales: nombre y descripción de la entrevista

        //Cabecera
        $mform->addElement('header', 'general', get_string('general', 'form'));

		// Nombre
        $mform->addElement('text', 'name', get_string('name'),'maxlength="100"');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

		// Descripción
        $mform->addElement('htmleditor', 'description', get_string('description'));
        $mform->setType('text', PARAM_RAW);
        $mform->addRule('description', null, 'required', null, 'client');
        //$mform->setHelpButton('description', array('writing', 'questions', 'richtext'), false, 'editorhelpbutton');

//-------------------------------------------------------------------------------
// Establece el límite temporal de la sesión de entrevistas y la duración de cada franja

        // Cabecera: Fecha
        $mform->addElement('header', 'date', get_string('date', 'interview'));

        // Fecha de comienzo (Día/Mes/Año/Hora/Minuto)
        $mform->addElement('date_time_selector', 'timeopen', get_string('timeopen', 'interview'));
        $mform->addHelpButton('timeopen', 'duration', 'interview');

        // Fecha de finalización (Día/Mes/Año/Hora/Minuto)
        $mform->addElement('date_time_selector', 'timeclose', get_string('timeclose', 'interview'));
        $mform->addHelpButton('timeclose', 'duration', 'interview');
        // Cabecera: Franjas horarias
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

        // Duración de cada franja
		$mform->addElement('text', 'timeslot', get_string('timeslot','interview'),'maxlength="3"');
        $mform->setType('timeslot', PARAM_INT);
        $mform->addRule('timeslot', null, 'required', null, 'client');
		$mform->addHelpButton('timeslot', 'duration', 'interview');

//-------------------------------------------------------------------------------
// Establece el lugar de la cita y el profesor al mando

        // Cabecera: detalles de la cita
        $mform->addElement('header', 'details', get_string('details', 'interview'));

        // Lugar de la cita
        $mform->addElement('text', 'location', get_string('location','interview'),'maxlength="50"');

        // Profesor al mando
        $mform->addElement('text', 'teacher', get_string('teacher','interview'),'maxlength="50"');

//-------------------------------------------------------------------------------
// Establece la visibilidad

        $mform->addElement('modvisible', 'visible', get_string('visible'));
		$this->standard_hidden_coursemodule_elements();
//-------------------------------------------------------------------------------
// Establece los botones de Guardar cambios y Cancelar

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

        // Para que esté todo correcto tiene que cumplirse:
        // 1. Tiempo inicio > Tiempo actual
        // 2. Tiempo cierre > Tiempo actual
        // 3. Tiempo cierre > Tiempo inicio
        // 4. Minutos por franja debe ser numérico
        // 5. Minutos por franja > 0 y distinto de vacío
        // 6. Tiempo de cierre - Tiempo de inicio >= Minutos por franja
        // 7. Duración_sesión = Duración_franja·N, siendo N entero

        // Almacena la hora de inicio de la sesión
        $a = date('H', $data['timeopen']);

        // Almacena los minutos de inicio de la sesión
        $b = date('i', $data['timeopen']);

        // Pasa todo a minutos
        $minstart = $a*60+$b;

        // Almacena la hora de finalización de la sesión
        $c = date('H', $data['timeclose']);

        // Almacena los minutos de finalización de la sesión
        $d = date('i', $data['timeclose']);

        // Pasa todo a minutos
        $minend = $c*60+$d;

        if ($data['timeopen']>=time() and $data['timeclose']>time() and $data['timeclose']>$data['timeopen'] and
            is_numeric($data['timeslot']) and $data['timeslot']>0 and
            ($minend - $minstart)>= $data['timeslot'] and
			is_int(($minend - $minstart)/$data['timeslot'])) {
            return true;

        // Establece un cuadro rodeando el tiempo de apertura e indicando que debe ser posterior a la fecha actual
        } elseif ($data['timeopen'] < time()) {
            return array('timeopen'=>get_string('timeopenfail', 'interview'));

        // Establece un cuadro rodeando el tiempo de cierre e indicando que debe ser posterior a la fecha actual
        } elseif ($data['timeclose'] <= time()) {
            return array('timeclose'=>get_string('timeclosefail', 'interview'));

        // Establece un cuadro rodeando el tiempo de cierre e indicando que debe ser posterior al tiempo de apertura
        } elseif ($data['timeclose']<= $data['timeopen']) {
            return array('timeclose'=>get_string('timefail', 'interview'));

        // Establece un cuadro rodeando la duración de la franja e indicando que debe proporcionarse un valor numérico
        } elseif (!is_numeric($data['timeslot'])) {
            return array('timeslot'=>get_string('numeric', 'interview'));

        // Establece un cuadro rodeando la duración de la franja e indicando que debe proporcionarse un valor positivo
        // y distinto de cero
        } elseif ($data['timeslot']<=0 ) {
            return array('timeslot'=>get_string('positive', 'interview'));

        // Establece un cuadro rodeando la duración de la franja e indicando que ésta debe ser menor que la
        // duración de la sesión
        } elseif ($minend - $minstart < $data['timeslot']) {
            return array('timeslot'=>get_string('timeslotfail', 'interview'));

        // Establece un cuadro rodeando la duración de la franja e indicando que debe proporcionarse un valor que encaje perfectamente
		// en la duración de la sesión
		} elseif (!is_int(($minend - $minstart)/$data['timeslot'])) {
			return array('timeslot'=>get_string('fit', 'interview'));
		}
    }
}

?>