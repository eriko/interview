<?php

/**
 * Este archivo php muestra una instancia
 * particular del m—dulo Entrevista personal
 * This php document shows an instance
 * specific to the model personal interview
 *  */

  // Se incluyen los ficheros especificados
  // It includes the specific files
    require_once("../../config.php");
    require_once("lib.php");

    // Variables obtenidas a travŽs de un POST o un GET
	// Vabiables obtained from a POST or a GET
    $id = optional_param('id', 0, PARAM_INT); // id de la instancia
											  // id of the instance
    $action = optional_param('action', '', PARAM_ALPHA); //acci—n a llevar a cabo
	                                                     //action performed

    // Comprobaciones de errores
	// Checking the errors

    // Dado el id de un m—dulo de un curso, encuentra su descripci—n
	// Given the id of the model of a course, find your description
    $cm = get_coursemodule_from_id('interview', $id);

    // Si no la encuentra, muestra un error
	// If it's not found, shows an error
  	if (!$cm) {
        error(get_string('cmidincorrect', 'interview'));
    }


    // Almacena la primera relaci—n que encuentra que cumple la restricci—n
	// Compile the first relationship found that satisfies the restriction
    $course = get_record('course', 'id', $cm->course);

    // Si no encuentra ninguna, muestra un error
	// If none are found, shows an error
    if (!$course) {
        error(get_string('cmisconfigured', 'interview'));
    }

  	// Chequea que el usuario actual est‡ dado de alta y tiene los permisos necesarios
	// Check that the actual user is released and has the necessary permissions
    require_course_login($course, false, $cm);

    //Almacena la primera relaci—n de la tabla interview que cumpla la restricci—n
	// Compile the primary relationship of the interview table that satisfies the restriction
    $interview = get_record('interview', 'id', $cm->instance);

    // Si falla devuelve error
	//if fails returns error
    if (!$interview) {
        error(get_string('cmincorrect','interview'));
    }

  	// Se utiliza para controlar la actividad reciente llevada a cabo por los usuarios
	// It's used to control the recent activity conducted by the user
    add_to_log($course->id, "interview", "view", "view.php?id=$cm->id", "$interview->id");

    // Almacena los nombres m‡s utilizados
	// Compile the most used names
    $strinterview  = get_string('modulename', 'interview');
    $strinterviews = get_string('modulenameplural', 'interview');
    $strchoose = get_string('choose', 'interview');
    $strdate = get_string('date', 'interview');
    $strstart = get_string('start', 'interview');
    $strend = get_string('end', 'interview');
    $strstudent = get_string('student', 'interview');
    $straction = get_string('action', 'interview');
    $strphoto = get_string('photo', 'interview');
    $stremail = get_string('email', 'interview');

  	// La impresi—n del nombre breve y la cabecera aparecen por defecto si no se incluye
  	// el siguiente c—digo aunque de esta forma es posible su modificaci—n
	//The impression of the short name and the heading appear defective if it doesn't include
	// the following code even if the form is possible to modify

  	// Muestra el nombre breve del curso proporcionando un link para ir a su p‡gina principal
	//Show the short name of the course proportionate to the link that goes to the primary page
    if ($course->category) {
        $navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
    } else {
        $navigation = '';
    }

    // Muestra la cabecera
	// Shows the header
  	print_header_simple(format_string($interview->name), '',
            			"<a href=\"index.php?id=$course->id\">$strinterviews</a> -> ".format_string($interview->name),
                        "", "", true, update_module_button($cm->id, $course->id, $strinterview), navmenu($course, $cm));

    // Coge el contexto de la instancia, si no lo hay lo crea

	// Take the context of the instance, if there isn't one create it
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    // Si falla devuelve error
	// If fails returns error
  	if (! $context) {
        print_error(get_string('badcontext','interview'));
    }

    echo '<br/>';

  	// Acciones posibles a efectuar
	// Actions possible to carry out
  	switch($action) {

    	// Acci—n: Elegir una franja temporal
		// Action: Elect a temporary string
        case 'mine':

            global $USER;

      		// Se recogen las variables necesarias
            // It picks up the necessary variables
            $id = required_param('id', PARAM_INT);
            $interviewid = required_param('interviewid', PARAM_INT);
            $slotid = required_param('slotid', PARAM_INT);

            // Comprobaciones de errores
			// checks the errors

            // Si no se ha elegido una franja temporal se informa de ello
			// If it hasn't selected a temporary string it informs you of that
            if (!$slotid) {
                notice(get_string('notselected', 'interview'), "view.php?id=$cm->id");
            }

      		// Almacena la franja temporal elegida
			// compiles the selected temporary string
      		$chosenslot = get_record('interview_slots', 'id', $slotid);

      		// Si falla devuelve error
			// If fails returns error
            if (!$chosenslot) {
                error(get_string('invalidslotid','interview'));
            }

            // Almacena todas las franjas temporales

			// Compiles all the temporary strings
            $slots = get_records('interview_slots', 'interviewid', $interviewid, 'id');

            // Para cada una de ellas
			// For each one
            foreach($slots as $slot) {

            	// Si el usuario actual ya ha elegido alguna, le informa
              	// de que s—lo puede asignarse una œnica franja
				// If the actual user has selected one, it informs you
				// that it can only assign a unique string
              	if ($slot->student == $USER->id) {
                	notice(get_string('oneslot','interview'),"view.php?id=$cm->id");
              	}
            }

            // Se guardan los nuevos datos
			// It saves the new data
            $chosenslot->id = $slotid;
            $chosenslot->student = $USER->id;
            $chosenslot->timemodified = time();

      		// Actualiza la franja. Si no es posible devuelve error

			// Actualizes the string. If it's not possible, returns error
            if (!update_record('interview_slots', $chosenslot)) {
            	error(get_string('notsaved', 'interview'));
            }

            // Se utiliza para controlar la actividad reciente llevada a cabo por los usuarios
			// It's used to control the recent activity carried out by the user
    		add_to_log($course->id, "interview", "choose", "view.php?id=$cm->id", "$interview->id");

        break;


      	// Acci—n: Asignarle a un alumno una franja temporal
		// Action: Assign an student a temporary string
    	case 'assign':

        	// Se recogen las variables necesarias
			// It picks up the necessary variables
          	$id = required_param('id', PARAM_INT);
          	$slotid = required_param('slotid', PARAM_INT);
          	$studentid = required_param('studentid', PARAM_INT);


          	// Comprobaciones de errores
			// Checks the errors


     		// Si no se ha elegido una franja temporal se informa de ello
			// If it hasn't selected a temporary string it will inform you
          	if (!$slotid) {
                notice(get_string('notselected', 'interview'), "view.php?id=$cm->id");
           	}

      		// Almacena la franja temporal elegida
    		// Compiles the selected temporary string
      		$slot = get_record('interview_slots', 'id', $slotid);

      		// Si falla devuelve error
			// If fails returns error
          	if (!$slot) {
                error(get_string('invalidslotid','interview'));
          	}

          	// Se guardan los nuevos datos
			// It saves the new data
			$slot->id = $slotid;
			$slot->student = $studentid;
			$slot->timemodified = time();

			// Actualiza la franja. Si no es posible devuelve error
			// Actualizes the string. If it's not possible, returns error
          	if (!update_record('interview_slots', $slot)) {
          		error(get_string('notassign', 'interview'));
          	}

          	// Se utiliza para controlar la actividad reciente llevada a cabo por los usuarios
			// It's used to control the recent activity performed by the user
    		add_to_log($course->id, "assignslot", "view", "view.php?id=$cm->id", "$interview->id");

        break;


        // Acci—n: Borrar franja
		// Action: Erases string
        case 'deleteslot':

      		// Recogen los par‡metros necesarios
			// Picks up the necessary parameters
      		$id = required_param('id', PARAM_INT);
      		$slotid = required_param('slotid', PARAM_INT);

        	// Se borra la franja seleccionada. Si todo va bien, redirecciona
        	// al usuario. Sino, devuelve error
			// It erases the seleted string. If all goes well, it addresses
			// to the user. If not, returns error
        	if (!delete_records('interview_slots', 'id', $slotid)) {
        		error (get_string('notdeleted','interview'));
            } else {
                redirect("view.php?id=$cm->id",get_string('deleting','interview'));
            }

        	// Controla la actividad reciente llevada a cabo por los usuarios
			//Controls the recent activity done by the users
        	add_to_log($course->id, "interview", "deleteslot", "view.php?id=$cm->id", $interview->id, $cm->id);

        break;


        // Acci—n: Liberar franja
		//Action: frees the string
        case 'freeslot' :

      		// Recogen los par‡metros necesarios
			// Picks up the necessary parameters
      		$id = required_param('id', PARAM_INT);
      		$slotid = required_param('slotid', PARAM_INT);
      		$studentid = required_param('studentid', PARAM_INT);

            // Se libera la franja seleccionada, eliminando
            // el usuario que almacena y dej‡ndola libre
            // para ser elegida por otro usuario
			//It frees the selected string, eliminating
			// the user that it compiles and leaves it free
			// to be selected by another user
            $studentid = 0;
            $slot->id = $slotid;
            $slot->student = $studentid;
            $slot->timemodified = time();

      		// Se actualiza la franja temporal. Si algo
      		// falla, devuelve error
			// It actualizes the temporary string. If something
			// fails, returns error
            if (!update_record('interview_slots', $slot)) {
                error (get_string('notupdated', 'interview'));
            } else {
                redirect("view.php?id=$cm->id",get_string('updating','interview'));
            }

            // Controla la actividad reciente llevada a cabo por los usuarios
			// Controls the recent activity done by the users
        	add_to_log($course->id, "interview", "freeslot", "view.php?id=$cm->id", $interview->id, $cm->id);

    	break;

    	// Acci—n: Anotar
		//Action: makes note
    	case 'takedown' :

      		// Recogen los par‡metros necesarios
			// Picks up the necessary parameters
      		$id = required_param('id', PARAM_INT);
      		$slotid = required_param('slotid', PARAM_INT);


      		// Se muestra un formulario donde el profesor podr‡
      		// a–adir anotaciones en la franja actual
			// It shows a form where the professor can
			// add notes in the actual string

      		echo '<form id="form" method="post" action="view.php" align="center">';
        		interview_take_notes($slotid, $id, $course);
          	echo '</form>';

    // Se utiliza para controlar la actividad reciente llevada a cabo por los usuarios
			// It's used to control the recent activity done by the user
    		add_to_log($course->id, "interview", "takenotes", "view.php?id=$cm->id", "$interview->id");

    	break;

    	// Acción: Guardar nota
    	case 'savenote':

      		// Recogen los parámetros necesarios
    	// Acci—n: Guardar nota
		// Action: Saves the note
    	case 'savenote':

      		// Recogen los par‡metros necesarios
			//Picks up the necessary parameters
      		$id = required_param('id', PARAM_INT);
      		$slotid = required_param('slotid', PARAM_INT);
      		$note = required_param('note', PARAM_TEXT);

      		// Almacena la franja horaria sobre la que se quiere anotar
      		$slot = get_record('interview_slots', 'id', $slotid);

      		// Se guardan los nuevos datos
			// Compiles the horary string over the one you want to record
      		$slot = get_record('interview_slots', 'id', $slotid);

      		// Se guardan los nuevos datos
			// It saves the new data

      		$slot->id = $slotid;
      		$slot->note = $note;
      		$slot->timemodified = time();

			// Se actualiza la franja. Si no es posible devuelve error
			// It actualizes the string. If not possible returns error
      		if (!update_record('interview_slots', $slot)) {
        		error(get_string('notupdated', 'interview'));
      		} else {
        		redirect("view.php?id=$cm->id",get_string('updating', 'interview'));
      		}

    	break;


    	// Acci—n: Modificar nota
		// Action: Modifies the note
    	case 'modify':

      		// Recogen los par‡metros necesarios
			// Picks up the necessary parameters
      		$id = required_param('id', PARAM_INT);
      		$slotid = required_param('slotid', PARAM_INT);
      		$note = required_param('note', PARAM_TEXT);


      		// Se muestra un formulario donde el profesor podr‡
      		// modificar las anotaciones hechas en la franja actual
			// It shows a form where the professor can
			// modify the notes done in the actual string
      		echo '<form id="form" method="post" action="view.php" align="center">';
        		interview_modify_notes($note, $slotid, $id, $course);
          	echo '</form>';

          	// Se utiliza para controlar la actividad reciente llevada a cabo por los usuarios
			// It's used to control the recent activity done by the user

    		add_to_log($course->id, "modifynotes", "view", "view.php?id=$cm->id", "$interview->id");

    	break;

    	// Acci—n: Cambiar de franja
		// Action: changes the string
    	case 'change' :

      		// Recogen los par‡metros necesarios
			// Picks up the necessary parameters
      		$id = required_param('id', PARAM_INT);
      		$slotid = required_param('slotid', PARAM_INT);

      		// Se elimina el usuario asignado a una franja temporal, para
      		// que pueda elegir otra franja de las que aœn est‡n libres
			// It eliminates the user assigned to a temporary string, so
			// it can pick another string that is free

      		$slot = get_record('interview_slots', 'id', $slotid);
      		$slot->id = $slotid;
      		$slot->student = 0;
      		$slot->timemodified = time();

      		// Se actualiza la franja temporal. Si algo
      		// falla, devuelve error
			// It actualized the temporary string. If something
			// fails, returns error
      		if (!update_record('interview_slots', $slot)) {
        		error(get_string('notupdated', 'interview'));
      		} else {
        		redirect("view.php?id=$cm->id",get_string('updating', 'interview'));
      		}

      		// Se utiliza para controlar la actividad reciente llevada a cabo por los usuarios
			// It's used to control the recent activity done by the users
    		add_to_log($course->id, "change", "view", "view.php?id=$cm->id", "$interview->id");

    	break;

    } // fin de las acciones
	  // End of the actions


    /************************************* PROFESOR ************************************/
    /***********************************************************************************/

    /************************************* PROFESSOR ***********************************/
    /***********************************************************************************/

    // Si el usuario actual es profesor
	// If the actual user is a professor
    if (isteacher($course->id,$USER->id)) {

        // Muestra el nombre de la instancia en formato cabecera
		// Shows the name of the instance in header format
        print_heading($interview->name, 'center');

        // Muestra la descripci—n de la Entrevista en un cuadro
		// Shows the description of the interview in a box
        if ($interview->description) {
            echo '<center>';
            print_simple_box(format_text($interview->description), 'center', '', '#eee');
            echo '</center>';
        }

        // Si se han establecido un lugar o un profesor se muestran en otro cuadro
		// If a place or professor has been established it's shown in another box
        if ($interview->location or $interview->teacher) {
        	echo '<center>';
          	print_simple_box_start('center', '', '#eee');
          	if (!empty($interview->location)) {
            	echo '<b>';
            	echo get_string('location', 'interview');
            	echo '</b>';
            	echo ': '.$interview->location;
          	}
      		if (!empty($interview->teacher)) {
            	echo '<br/>';
            	echo '<b>';
            	echo get_string('teacher', 'interview');
            	echo '</b>';
            	echo ': '.$interview->teacher;
          	}
          	print_simple_box_end();
          	echo '</center>';
        }


		/************** TABLA 1 **************/
		/*************************************/
		/************** TABLE 1 **************/
		/*************************************/

        // Almacena las franjas horarias ordenadas por id
		// Compiles the horary strings by id
        $slots = get_records('interview_slots', 'interviewid', $interview->id, 'id');

        // Define las cabeceras y alineaciones de la tabla Franjas Horarias
		// Defines the headings and aligns the table horary strings
        $table->head  = array ($strdate, $strstart, $strend, $strphoto, $strstudent,$straction);
        $table->align = array ('CENTER', 'CENTER', 'CENTER', 'CENTER', 'CENTER', 'CENTER');
        $table->width = array('','','','','','');


        // Para cada una de las franjas temporales
		// For each of the temporary strings
        foreach($slots as $slot) {

        	// Si no tiene un estudiante asignado y ya ha pasado su tiempo
			// la borra y pasa a la siguiente iteraci—n del foreach
			// If a student is not assigned and its time has passed
			// it erases and it passes to the second iteration of the foreach

            if ($slot->student == 0 and $slot->ending < time()) {
            	delete_records('interview_slots', 'id', $slot->id);
                continue;
            }


            // Define la forma en que se mostrar‡n la fecha de la sesi—n
            // y los tiempos de inicio y fin de cada franja
			// Defines the form that shows the date of the session
			// and the starting and ending times of each string
			$starttime = userdate($slot->start,  get_string('strftimetime'));
            $endtime = userdate($slot->ending,  get_string('strftimetime'));
            $startdate = userdate($slot->start,  get_string('strftimedateshort'));
            // Si la franja horaria ya ha sido seleccionada por un estudiante
			// If the horary string has been selecte by a student
            if ($slot->student) {

                // Almacena el usuario
				// Compiles the user
                $student = get_record('user', 'id', $slot->student);

                // Muestra la imagen del usuario
				// Shows the picture of the user
                $picture = print_user_picture($slot->student, $course->id, $student->picture, false, true);

                // Muestra el nombre completo del usuario en formato link
				// shows the full name of the user in a formatted link
                $name = "<a href=\"view.php?action=viewstudent&amp;id=$cm->id&amp;studentid=$student->id&amp;course=$interview->course&amp;order=DESC\">".fullname($student).'</a>';

            // Si la franja horaria aœn no ha sido seleccionada por ningœn estudiante
			// If the horary string has not been selected by a student
            } else {
                $picture = '';
                $name = '';
            }

            // Establece los links para las acciones
			// Establishes the links for the actions

            $actions = '<span style="font-size: x-small;">';
            // Acci—n de borrar
			// Action to erase
            $actions .= "[<a href=\"view.php?action=deleteslot&amp;id=$cm->id&amp;slotid=$slot->id\">".get_string('delete').'</a>]';

            // Si la franja temporal ya tiene asignado un estudiante
			// If the temporary string already is assigned to a student
            if ($slot->student != 0) {

            	// Acci—n de liberar
				// Action to free

              	$actions .= "[<a href=\"view.php?action=freeslot&amp;id=$cm->id&amp;slotid=$slot->id&amp;studentid=$slot->student\">".get_string('free', 'interview').'</a>]';
      		}

            // Si la franja temporal lleva asociada una nota
			// If the temporary string has a note associated with it
            if (!empty($slot->note)) {

                // Acciones de ver nota y modificar nota
				// Actions to see and modify the note
                $actions .= "[<a onclick=\"openwindow(".$slot->id.",".$cm->id.")\" >".get_string('viewnote', 'interview')."</a>]";
                $actions .= "[<a href=\"view.php?action=modify&amp;id=$cm->id&amp;slotid=$slot->id&amp;note=$slot->note\">".get_string('modify', 'interview').'</a>]';

            // Si la franja temporal no tiene ninguna nota
			// If the temporary string does not have a note
            } else {

                // Acci—n de anotar
				// Action to take note
                $actions .= "[<a href=\"view.php?action=takedown&amp;id=$cm->id&amp;slotid=$slot->id\">".get_string('takedown', 'interview').'</a>]';
            }

            $actions .= '</span>';

            // Inserta los datos en la tabla
			// Inserts data in the table
            $table->data[] = array ($startdate, $starttime, $endtime, $picture, $name, $actions);
        }

        // T’tulo de la tabla
		// Title of the table
        print_heading(get_string('slots' ,'interview'), 'center');

        // Muestra la tabla centrada en la pantalla
		// shows the table centered on the screen
        echo '<center>';
        print_table($table);
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


        /************** TABLA 2 **************/
        /*************************************/
		/************** TABLE 2 **************/
        /*************************************/


        // Almacena los estudiantes del curso
		// collects the students in the course
        $students = get_course_students($course->id, $sort="u.lastname", $dir="ASC");

        // Si no hay ninguno, lo notifica
		// If there are no students, will notify
        if (!$students) {
          	notify(get_string('noexistingstudents'));

        // Si los hay, crea una tabla que contiene a los usuarios
        // que aœn no han elegido una franja horaria
		// If there are students, creates a table with the users
		// that have not picked a horary string
        } else {

          	// Define las cabeceras y alineaciones en la tabla de estudiantes
			// Defines the headings and alignments in the table of students
            $mtable->head  = array ($strphoto, $strstudent, $stremail);//, $straction);
            $mtable->align = array ('CENTER', 'CENTER', 'CENTER');//, 'CENTER');
            $mtable->width = array('', '', '', '');

            // Inicio del link para enviar un mail a todos los
            // estudiantes que aœn no han elegido franja
			// Begins the link to send mail to all the
			// students that have not picked a string
            $mailto = '<a href="mailto:';

            // Para cada uno de los estudiantes
			// For each of the students
            foreach ($students as $student) {

                // Si no existe ninguna relaci—n que cumpla las restricciones
				// If a relationship that complies with the restrictions does not exist
                if (!record_exists('interview_slots', 'student', $student->id, 'interviewid', $interview->id)) {

                  	// Muestra la imagen del usuario
					// Shows the user image
                    $picture = print_user_picture($student->id, $course->id, $student->picture, false, true);

                    // Muestra su nombre completo en formato link
					// Shows the full name in link format
                    $name = "<a href=\"../../user/view.php?id=$student->id&amp;course=$interview->course\">".fullname($student)."</a>";

                    // Crea un link de mailto listo para usar
					// Creates a link to the mailto list for the user
                    $email = obfuscate_mailto($student->email);

                    // Incorpora el email del estudiante
					// Incorporates the email of the student
                    $mailto .= $student->email.', ';

                    // Almacena las franjas temporales ordenadas por id
                    //$slots = get_records('interview_slots', 'interviewid', $interview->id, 'id');
					// Compiles the temporary strings organized by id

					// Para cada una de ellas, si ya tiene asignado un alumno
					// pasa a la siguiente iteraci—n del foreach
					// For each one, if assigned to a student
					// passes to the next iteration of foreach
          			//foreach ($slots as $slot) {
            		//	if ($slot->student != 0) {
                	//		continue;
            		//	}
          			//}

					// Si las franjas temporales no est‡n vac’as
					// If the temporary strings are not empty
          			//if (!empty($slots)) {

          				// Crea un array
						// Creates an array
              		//	$choices = array();

              			// Para cada una de ellas, si aœn no tiene asignado un alumno,
              			// va rellenando el array con el horario de las franjas
						// For each one, if a student is not assigned,
						// fills in the array with the horary strings
					//	foreach($slots as $slot) {
              		//		if ($slot->student == 0) {
                  	//			$choices[$slot->id] = userdate($slot->start,  get_string('strftimetime')). ' - ' .userdate($slot->ending,  get_string('strftimetime'));
                	//		}
            		//	}

            			// Se crean los menœs iteraci—n con los horarios disponibles
						// It creates the iteration menus with the availiable times
					//	$actions = "<form name=\"form".$student->id."\">";
              		//	$actions .= choose_from_menu($choices, 'slotforstudent', '', 'choose', 'asignacion('.$cm->id.','.$student->id.',form'.$student->id.')', '0', true);
              		//	$actions .= "</form>";

					// Si las franjas temporales est‡n vac’as entonces
					// las acciones tambiŽn lo est‡n
					// If the temporary strings are empty
					// the actions are empty as well
          			//} else {
              		//	$actions = '';
          			//}

					// Inserta los datos en la tabla
					// Inserts the data in the table
          			$mtable->data[] = array($picture, $name, $email);//, $actions);
          		}
     		}
    	}
        // Cuenta los elementos del array
		// Counts the elements of the array
        $numm = count($mtable->data);


        // Si hay algœn dato en la tabla indica que hay algœn estudiante
        // que aœn no ha elegido una franja horaria
		// If there is data in the table that indicates that a studnet
		// has not picked a horary string
        if ($numm > 0) {

        	// Muestra el nœmero de estudiantes que aœn no han elegido
			// Shows the number of students that have not picked
			if ($numm == 1) {
            	print_heading(get_string('missingstudent', 'interview'), 'center');
			} elseif ($numm > 1) {
            	print_heading(get_string('missingstudents', 'interview', $numm), 'center');
			}

            // Crea los links para enviar invitaciones o recordatorios
			// Creates the links to send invitations or reminders
            $strinvitation = get_string('invitation', 'interview');
            $strreminder = get_string('reminder', 'interview');

            // Elimina el espacio en blanco y la coma al final de $mailto
			// Eliminates the blank space and the final coma of $mailto
            $mailto = rtrim($mailto, ', ');

            // Invitaci—n:
            // Especifica el asunto del email
			// Invitation:
			// Specifies the topic of the email
            $subject = $strinvitation.': '.$interview->name;

            // Especifica el cuerpo del mensaje
			// Specifies the body of the message
            $body = "$strinvitation: $interview->name\n\n".
                    get_string('invitationtext', 'interview').
                    "{$CFG->wwwroot}/mod/interview/view.php?id=$cm->id";

            // Establece el texto completo a enviar
			// Establishes the complete text to send
            echo '<center>'.get_string('composeemail', 'interview').
                    $mailto.'?subject='.htmlentities(rawurlencode($subject)).
                    '&amp;body='.htmlentities(rawurlencode($body)).
                    '"> '.$strinvitation.'</a> ';

            // Recordatorio:
            // Especifica el asunto del email
			// Reminder:
			// Establishes the topic of the email
            $subject = $strreminder.': '.$interview->name;

            // Especifica el cuerpo del mensaje
			// Establishes the body of the message
            $body = "$strreminder: $interview->name\n\n".
                    get_string('remindertext', 'interview').
                    "{$CFG->wwwroot}/mod/interview/view.php?id=$cm->id";

            // Establece el texto completo a enviar
			// Establishes the complete text to send
            echo $mailto.'?subject='.htmlentities(rawurlencode($subject)).
                    '&amp;body='.htmlentities(rawurlencode($body)).
                    '"> '.$strreminder.'</a></center><br />';

            // Muestra la tabla de estudiantes centrada en pantalla
			// Shows the table of students centered in the screen
            echo '<center>';
                print_table($mtable);
            echo '</center>';
        }


	/************************************* ESTUDIANTE ************************************/
    /*************************************************************************************/
	/************************************* STUDENT ************************************/
    /*************************************************************************************/

    // Si el usuario no es profesor sino estudiante
	// If the user is not a professor, the user is a student
    } elseif (isstudent($course->id,$USER->id)) {

        // Muestra el nombre de la instancia en formato cabecera
		// Shows the name of the instance formed in the header
        print_heading($interview->name, 'center');

        // Muestra la descripci—n en un cuadro
		// Shows the description in a square
        if ($interview->description) {
            echo '<center>';
            print_simple_box(format_text($interview->description), 'center', '', '#eee');
            echo '</center>';
        }

        // Si se han establecido un lugar o un profesor se muestran en otro cuadro
		// If a place or professor has been established, is shown in another square
        if ($interview->location or $interview->teacher) {
          	echo '<center>';
          	print_simple_box_start('center', '', '#eee');
            if (!empty($interview->location)) {
            	echo '<b>';
            	echo get_string('location', 'interview');
            	echo '</b>';
            	echo ': '.$interview->location;
          	}
      		if (!empty($interview->teacher)) {
            	echo '<br/>';
            	echo '<b>';
            	echo get_string('teacher', 'interview');
            	echo '</b>';
            	echo ': '.$interview->teacher;
          	}
          	echo '</center>';
          	print_simple_box_end();
        }

        // Almacena las franjas temporales ordenadas por id
		// Compiles the temporary strings ordered by id
        $slots = get_records('interview_slots', 'interviewid', $interview->id, 'id');

        // Define las cabeceras y alineaciones de la tabla Franjas Horarias
		// Defines the headers and the alignment on the table Horary Strings
        $table->head  = array ($strdate, $strstart, $strend, $strchoose);
        $table->align = array ('CENTER', 'CENTER', 'CENTER', 'CENTER');
        $table->width = array('','','','');

        // Para cada una de las franjas temporales
		// For each of the temporary strings
        foreach($slots as $slot) {

        	// Sino tiene un estudiante asignado y ha pasado su tiempo,
			// la borra y pasa a la siguiente iteraci—n del foreach
			// If a student is not assigned and their time has passed,
			// it's erased and goes on to the next iteration of foreach
            if ($slot->student == 0 and $slot->ending < time()) {
                delete_records('interview_slots', 'id', $slot->id);
                continue;
            }

      		// Si el usuario ya tiene asignada una franja temporal
			// If the user already has an assigned temporary string
            if ($slot->student == $USER->id) {

            	// Establece la forma en que se mostrar‡n los datos de su elecci—n
				// Establishes the form that shows the data of the choice
              	$starttime = userdate($slot->start,  get_string('strftimetime'));
	            $endtime = userdate($slot->ending,  get_string('strftimetime'));
	            $startdate = userdate($slot->start,  get_string('strftimedateshort'));

              	// Cuadro de texto donde mostrarle su elecci—n
				// square of text were the choice is shown
              	print_simple_box_start('center');
              	echo '<center>';
            	echo '<b>';
              	echo format_text(get_string('yourselection', 'interview'));
              	echo '</b>';
              	echo format_text(get_string('date', 'interview').': '.$startdate);
              	echo format_text(get_string('hour', 'interview').': '.$starttime. ' - '.$endtime);

              	// Aporta la opci—n de cambiar la franja seleccionada
				// Provides the option to change the selected string
              	echo "[<a href=\"view.php?action=change&amp;id=$cm->id&amp;slotid=$slot->id\">".get_string('change', 'interview').'</a>]';
              	echo '</center>';
              	print_simple_box_end();
            }

            // No muestra las franjas temporales que ya han sido
            // asignadas a otro alumno
			// Does not show the temporary strings that have been
			// assigned to another student
            if ($slot->student != 0) {
              	continue;
            }

            // Define la forma en que se mostrar‡n la fecha de la sesi—n
            // y los tiempos de inicio y fin de cada franja
			// defines the form that shows the date of the session
            $starttime = userdate($slot->start,  get_string('strftimetime'));
            $endtime = userdate($slot->ending,  get_string('strftimetime'));
            $startdate = userdate($interview->timeopen,  get_string('strftimedateshort'));

            // Establece el link para la acci—n
			// establishes the link for the action
			$actions = '<span style="font-size: x-small;">';
            // Acci—n de elegir una franja temporal
			// Action to pick a temporary string
            $actions .= "[<a href=\"view.php?action=mine&amp;id=$cm->id&amp;interviewid=$interview->id&amp;slotid=$slot->id\">".get_string('assign', 'interview').'</a>]';
            $actions .= '</span>';

            // Inserta los datos en la tabla
			// Inserts the data in the table
            $table->data[] = array ($startdate, $starttime, $endtime, $actions);
        }

    	// Si las franjas se han ido borrando solas porque ha caducado la entrevista
		// If the strings have been self erasing because the interview has expired
    	if (empty($slots) and $interview->timeclose) {
      		print_heading(get_string('expire', 'interview'), 'center');

    	// Sino, muestra la tabla centrada en pantalla
		// If not, shows the table centered on the screen
    	} else {

    		// T’tulo de la tabla
			// title of the table
        	print_heading(get_string('slots' ,'interview'), 'center');
        	echo '<center>';
        	print_table($table);
        	echo '</center>';
        	echo '<br /><br />';
    	}
	}


	// Muestra el pie de p‡gina
	// Shows the footer of the page
	print_footer($course);
?>

