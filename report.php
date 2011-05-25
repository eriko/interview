<?php

/**
 * Este archivo php se encarga de llevar a cabo la descarga de los
 * resultados que se van obteniendo.
 */
	global $DB;
    // Se incluyen los ficheros especificados
    require_once("../../config.php");
    require_once("lib.php");

    // Variables obtenidas a través de un POST o un GET
    $id       = required_param('id', PARAM_INT);   // id de la instancia
    $download = optional_param('download', '', PARAM_ALPHA); // string con el tipo de descarga que se quiere efectuar

	// Comprobaciones de errores

	// Dado un id de un módulo de un curso, encuentra su descripción
	$cm = get_coursemodule_from_id('interview', $id);

	// Si no la encuentra, muestra un error
    if (!$cm) {
        error(get_string('cmidincorrect', 'interview'));
    }

	// Almacena la primera relación que cumpla la restricción
	$course = $DB->get_record('course', array('id' => $cm->course));

	// Si no encuentra ninguna, muestra un error
    if (!$course) {
        error(get_string('cmisconfigured', 'interview'));
    }

	// Chequea que el usuario actual está dado de alta y tiene los permisos necesarios
    require_login($course->id, false, $cm);

    // Coge el contexto de la instancia, si no lo hay lo crea
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    // Almacena la instancia
    $interview = $DB->get_record('interview', array('id'=> $cm->instance));

	// Si falla, devuelve error
    if (!$interview) {
        error(get_string('cmincorrect','interview'));
    }

	// Se almacenan los nombres más habituales de la actividad
    $strinterview = get_string('modulename', 'interview'); // nombre en singular
    $strresponses = get_string('responses', 'interview'); // resultados

	// Se utiliza para controlar la actividad reciente llevada a cabo por los usuarios
    add_to_log($course->id, "interview", "report", "report.php?id=$cm->id", "$interview->id",$cm->id);

	// Almacena los usuarios del contexto actual con varios de sus datos personales
	// y ordenándolos por su apellido de forma ascendente
    $users = get_users_by_capability($context, 'mod/interview:choose', 'u.id, u.firstname, u.lastname', 'u.lastname ASC');

	// Si no hay usuarios lo indica en formato cabecera
    if (!$users) {
        print_heading(get_string('nousersyet'), 'center');
    }

	// Si se va a efectuar una descarga de los resultados en formado ODS
    if ($download == "ods") {

        // Se incluye el fichero especificado
        require_once("$CFG->libdir/odslib.class.php");

    	// Calcula el nombre del fichero
        $filename = clean_filename("$course->shortname ".strip_tags(format_string($strinterview,true))).'.ods';
    	// Crea el cuaderno
        $workbook = new MoodleODSWorkbook("-");
    	// Envía las cabeceras HTTP
        $workbook->send($filename);
    	// Crea la primera hoja de trabajo
        $myxls =& $workbook->add_worksheet($strresponses);

    	// Imprime los nombres de todos los campos
        $myxls->write_string(0,0,get_string('lastname'));
        $myxls->write_string(0,1,get_string('firstname'));
        $myxls->write_string(0,2,get_string('idnumber'));
        $myxls->write_string(0,3,get_string('group'));
        $myxls->write_string(0,4,get_string('schedule','interview'));


    	// Genera los datos para el cuerpo de la hoja de cálculo
        $i=0;
        $row=1; // fila 1

        // Si hay usuarios que cumplan la capacidad
        if ($users) {

            // Para cada uno de ellos
            foreach ($users as $user) {

				// Almacena la franja temporal asociada a él
            	$slot = $DB->get_record('interview_slots', array('interviewid'=> $interview->id,'student'=> $user->id));


                    // Si no es vacío su estudiante asignado
                    if (!empty($slot->student)) {

						// Establece la forma de mostrar la duración de la franja
                        $start = date('H:i',$slot->start);
						$end = date('H:i', $slot->ending);

						// Escribe los campos
                        $myxls->write_string($row,0,$user->lastname);
                        $myxls->write_string($row,1,$user->firstname);
                        $myxls->write_string($row,2,$user->id);

	                    // Almacena la opción elegida y la muestra
	                    $useroption = $start.'-'.$end;
	                    if (isset($useroption)) {
	                        $myxls->write_string($row,3,format_string($useroption,true));
	                    }
                        $row++; // pasa a la siguiente fila
                    }
                    $pos=4;
            }

    		// Cierra el cuaderno
            $workbook->close();

            exit;
        }
    }


	// Si se va a efectuar una descarga de los resultados en formato EXCEL
    if ($download == "xls") {

        // Se incluye el fichero especificado
        require_once("$CFG->libdir/excellib.class.php");

    	// Calcula el nombre del fichero
        $filename = clean_filename("$course->shortname ".strip_tags(format_string($strinterview,true))).'.xls';
   		// Crea el cuaderno
        $workbook = new MoodleExcelWorkbook("-");
    	// Envía las cabeceras HTTP
        $workbook->send($filename);
    	// Crea la primera hoja de trabajo
        $myxls =& $workbook->add_worksheet($strresponses);

    	// Imprime los nombres de todos los campos
        $myxls->write_string(0,0,get_string('lastname'));
        $myxls->write_string(0,1,get_string('firstname'));
        $myxls->write_string(0,2,get_string('idnumber'));
        $myxls->write_string(0,3,get_string('slot','interview'));


    	// Genera los datos para el cuerpo de la hoja de cálculo
        $i=0;
        $row=1; // fila 1

        // Si hay usuarios que cumplan la capacidad
        if ($users) {

            // Para cada uno de ellos
            foreach ($users as $user) {

				// Almacena la franja temporal asociada a él
        		$slot = $DB->get_record('interview_slots',array( 'interviewid'=> $interview->id,'student'=> $user->id));

                // Si no es vacío su estudiante asignado
                if (!empty($slot->student) ){

					// Establece la forma de mostrar la duración de la franja
                	$start = date('H:i',$slot->start);
					$end = date('H:i', $slot->ending);

					// Escribe los campos
                    $myxls->write_string($row,0,$user->lastname);
                    $myxls->write_string($row,1,$user->firstname);
                    $myxls->write_string($row,2,$user->id);

                    // Almacena la opción elegida y la muestra
                    $useroption = $start.'-'.$end;
                    if (isset($useroption)) {
                        $myxls->write_string($row,3,format_string($useroption,true));
                    }
                    $row++; // pasa a la siguiente fila
                }
                $pos=4;
            }

		// Cierra el cuaderno
        $workbook->close();

        exit;
        }
    }

	// Si se va a efectuar una descarga de los resultados en formato TXT
    if ($download == "txt") {

    	// Calcula el nombre del fichero
        $filename = clean_filename("$course->shortname ".strip_tags(format_string($strinterview,true))).'.txt';

		// Cabeceras
        header("Content-Type: application/download\n");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate,post-check=0,pre-check=0");
        header("Pragma: public");

		// Imprime los nombres de todos los campos
        echo get_string('lastname')."\t".get_string('firstname')."\t";
        echo get_string('idnumber')."\t".get_string('slot','interview')."\n";

        // Genera los datos para el cuerpo de la hoja de cálculo
        $i=0;
        $row=1; // fila 1

        // Si hay usuarios que cumplan la capacidad
        if ($users) {

            // Para cada uno de ellos
            foreach ($users as $user) {

				// Almacena la franja temporal asociada a él
                $slot = $DB->get_record('interview_slots',array( 'interviewid'=> $interview->id,'student'=> $user->id));

                // Si no es vacío su estudiante asignado
            	if (!empty($slot->student)) {

					// Establece la forma de mostrar la duración de la franja
            	    $start = date('H:i',$slot->start);
					$end = date('H:i', $slot->ending);

					// Escribe los campos
                    echo $user->lastname;
                    echo "\t".$user->firstname;
                    echo "\t".$user->id."\t";
                    echo $start.' - '.$end."\n";
                }
                $row++; // pasa a la siguiente fila
            }
            exit;
        }
    }
?>