<?php

/**
 * Este archivo php lista todas las instancias
 * del módulo Entrevista Personal en un curso particular
 */

  	// Se incluyen los ficheros especificados
    require_once("../../config.php");
    require_once("lib.php");

	// Se comprueba que el id del curso es el correcto
    $id = required_param('id', PARAM_INT);
	$course = get_record('course', 'id', $id);

	// Si no es correcto devuelve error
    if (!$course) {
        error(get_string('cidincorrect', 'interview'));
    }

	// Chequea que el usuario actual está dado de alta y tiene los permisos necesarios
    require_login($course->id);

	// Se utiliza para controlar la actividad reciente llevada a cabo por los usuarios
    add_to_log($course->id, "interview", "view all", "index.php?id=$course->id", "");

	// Se almacenan los nombres en singular y plural de la actividad
    $strinterviews = get_string('modulenameplural', 'interview');
    $strinterview  = get_string('modulename', 'interview');

    // Muestra el nombre breve del curso proporcionando un link para ir a su página principal
    if ($course->category) {
        $navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
    } else {
        $navigation = '';
    }

    // Muestra la cabecera
    print_header_simple("$strinterview", "", "$strinterviews", "", "", true, "", navmenu($course));

    // Almacena una matriz de todas las instancias de Entrevista Personal en un curso dado
	$interviews = get_all_instances_in_course('interview', $course);
    if (!$interviews) {
        notice(get_string('nointerviews','interview'),"../../course/view.php?id=$course->id");
        die;
    }

	// Se almacena la franja que seleccionó el usuario actual
    $slot = get_record('interview_slots', 'student', $USER->id);

    // Se guarda la franja horaria
  	if (!empty($USER->id) and $slot) {
    	$start = date('H:i',$slot->start);
    	$end = date('H:i', $slot->ending);
    }

	// Si el usuario es profesor del curso
	if (isteacher($course->id, $USER->id)) {

    	// Especifica la cabecera y alineación en que se mostrarán las distintas entrevistas
		// al hacer clic en el link de Entrevistas Personales de un curso
    	if ($course->format == 'weeks') { //Si el formato del curso es semanal
        	$table->head  = array (get_string('week'), $strinterview);
        	$table->align = array ('center', 'center');
    	} else if ($course->format == 'topics') { //Si el formato del curso es por temas
        	$table->head  = array (get_string('topic'), $strinterview);
        	$table->align = array ('center', 'center');
    	} else {
        	$table->head  = array ($strinterviews);
        	$table->align = array ('center');
    	}

      	// Inicializa las variables
		$currentsection = '';
		$printsection = '';

		// Para cada una de las instancias del módulo
    	foreach ($interviews as $interview) {

			// Si la sección es distinta de $currentsection
	        if ($interview->section !== $currentsection) {

	            // Y la sección no es cero
	            if ($interview->section) {

	                // Almacena la sección
	                $printsection = $interview->section;
	            }

	            // Si $currentsection no es vacía inserta una línea para marcar el cambio de sección
	            if ($currentsection !== '') {
	                $table->data[] = 'hr';
	            }

	            // Almacena la sección
	            $currentsection = $interview->section;

			}

    		// Muestra el texto apagado si se elije la opción Ocultar en el menú Visible
        	if (!$interview->visible) {
            	$link = "<a class=\"dimmed\" href=\"view.php?id=$interview->coursemodule\">$interview->name</a>";

        	// Mostrar normal si es Visible
        	} else {
            	$link = "<a href=\"view.php?id=$interview->coursemodule\">$interview->name</a>";
        	}

        	// Si el formato es semanal o por temas se añade a la tabla información sobre
        	// la sección y la entrevista a la que se refiere
        	if ($course->format == 'weeks' or $course->format == 'topics') {
            	$table->data[] = array ($printsection, $link);
        	} else {
            	$table->data[] = array ($link);
        	}
    	}

    // Si es estudiante del curso
	} elseif (isstudent($course->id, $USER->id)) {

		// Especifica la cabecera y alineación en que se mostrarán las distintas entrevistas
		// al hacer clic en el link de Entrevistas Personales de un curso
    	if ($course->format == 'weeks') { //Si el formato del curso es semanal
        	$table->head  = array (get_string('week'), $strinterview, get_string('chosenslot','interview'));
        	$table->align = array ('center', 'center', 'center');
    	} else if ($course->format == 'topics') { //Si el formato del curso es por temas
        	$table->head  = array (get_string('topic'), $strinterview, get_string('chosenslot', 'interview'));
        	$table->align = array ('center', 'center', 'center');
    	} else {
        	$table->head  = array ($strinterviews, get_string('chosenslot', 'interview'));
        	$table->align = array ('center', 'center');
    	}


	    // Inicializa las variables
		$currentsection = '';
		$printsection = '';


		// Para cada una de las instancias del módulo
    	foreach ($interviews as $interview) {

			// Si no está vacía la franja temporal la almacena
			if (!empty($slot)) {
				$aa = $start.' - '.$end;
			} else {
				$aa = '';
			}

			// Si la sección es distinta de $currentsection
	        if ($interview->section !== $currentsection) {

	            // Y la sección no es cero
	            if ($interview->section) {

	                // Almacena la sección
	                $printsection = $interview->section;
	            }

	            // Si $currentsection no es vacía inserta una línea para marcar el cambio de sección
	            if ($currentsection !== '') {
	                $table->data[] = 'hr';
	            }

	            // Almacena la sección
	            $currentsection = $interview->section;

			}

    		// Muestra el texto apagado si se elije la opción Ocultar en el menú Visible
        	if (!$interview->visible) {
            	$link = "<a class=\"dimmed\" href=\"view.php?id=$interview->coursemodule\">$interview->name</a>";

        	// Mostrar normal si es Visible
        	} else {
            	$link = "<a href=\"view.php?id=$interview->coursemodule\">$interview->name</a>";
        	}

        	// Si el formato es semanal o por temas se añade a la tabla información sobre
        	// la sección, la entrevista a la que se refiere y la franja seleccionada
        	if ($course->format == 'weeks' or $course->format == 'topics') {
            	$table->data[] = array ($printsection, $link, $aa);
        	} else {
            	$table->data[] = array ($link, $aa);
        	}
    	}
    }

    echo "<br />";

	// Muestra la tabla
    print_table($table);

	// Muestra el pie de página
    print_footer($course);

?>