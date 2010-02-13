<?php

/**
 * Este archivo php contiene todas las funciones
 * necesarias para la correcta ejecución del módulo
 * This php document contains all the functions
 * necessary for the correct execution of the model
 */

/**************************
** 	FUNCIONES ESTÁNDAR  **
 *  Standard functions
**************************/

/**
 * Dado un objeto conteniendo todos los datos necesarios,
 * (definidos por el formulario en mod.html) esta función
 * creará una nueva instancia y retornará el identificador
 * de dicha nueva instancia.

 * Give an object containing all the necessary data,
 * (defined by the form in mod.html) this function
 * creates a new instance and returns the identifier
 * of the saying of the new instance.
 */

function interview_add_instance($interview) {
    global $CFG;
    $interview->timemodified = time();

    // Inserta una relación en la tabla interview y devuelve su identificador
    
    // Si se inserta correctamente, crea la relación correspondiente en la tabla
    // interview_slots
    // Insert a relationship in the interview table and return the identifier
    // If inserted correctly, create a relationship corresponding in the table
    // interview_slots
    if ($interview->id = insert_record("interview", $interview)) {
        for($i = $interview->timeopen; $i < $interview->timeclose; $i += $interview->timeslot*60) {
        	$slot = new object();
        	$slot->interviewid = $interview->id;
        	$slot->start = $i;
        	$slot->ending = $i + $interview->timeslot*60;
        	$slot->timemodified = time();
            if ($interview -> timeblock > 0)
            {
                $timeopen = ($CFG -> {"interview_timeblock".$interview -> timeblock."open"})*100  ;
                $timeclose = ($CFG -> {"interview_timeblock".$interview -> timeblock."close"})*100 ;
                $start = intval(date('Gi' ,$slot ->start));
                $end = intval(date('Gi' ,$slot ->ending));

                if ($start >= $timeopen && $start < $timeclose && $end <= $timeclose && $end > $timeopen )
                {
                    insert_record("interview_slots", $slot);
                }
            }
            else
            {
                insert_record("interview_slots", $slot);
            }
        }
    }
    // Retorna el id de la Entrevista
    //return the id of the interview
    return $interview->id;
}

/**
 * Dado un objeto conteniendo todos los datos necesarios,
 * (definidos por el formulario en mod.html) esta función
 * actualizará una instancia existente con nuevos datos.
 * Given an object containing all the necessary data,
 * (defined by the formula in mod.html) this function
 * actualizes an existing instance with new data
 */

function interview_update_instance($interview) {

	// Crea un nuevo objeto
    // Create a new object
	$opt = new object();
	$opt->timemodified = time();
	$opt->id = $interview->instance;

	// Si se ha establecido un lugar, asignárselo
    // If a location has been established, assign it
    if (isset($interview->location)) {
		$opt->location = $interview->location;
	}

	// Si se ha establecido un profesor, asignárselo
    // If a professor has been established, assign it
	if (isset($interview->teacher)) {
		$opt->teacher = $interview->teacher;
	}

	// Si se ha establecido un nombre, asignárselo
    // If a name has been established, assign it
	if (isset($interview->name)) {
		$opt->name = $interview->name;
	}

	// Si se ha establecido una descripción, asignársela
    // If a description has been established, assign it
	if (isset($interview->description)) {
		$opt->description = $interview->description;
	}

    return update_record('interview', $opt);
}

/**
 * Dado un identificador de una instancia de este módulo,
 * esta función eliminará de forma permanente la instancia
 * y cualquier dato dependiente de ella.
 * Given an identifier of the instance in this model,
 * this function will eliminate the permanent form if the instance
 * and whatever function depends on it.
 */

function interview_delete_instance($id) {

    // Almacena la primera relación de la tabla interview que cumpla la restricción
    // Compile the primary relationship of the interview table that fulfills the restriction
    $interview = get_record('interview', 'id', $id);

    // Si no hay ninguna relación en la tabla que tenga el id buscado retorna falso
    // If there is no relationship in the table that has the id you're looking for return false
    if (!$interview) {
        return false;
    }

    // Inicializa la variable que va a contener el valor a retornar
    // Initialize the variable that will contain the value to return
    $result = true;

    // Elimina cualquier relación dependiente de la anterior, si en algún momento
    // algo falla se hace falso $result
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
** 	FUNCIONES ESPECÍFICAS  **
** Specific functions  **
****************************/

/**
 * Esta función se encarga de mostrar un formulario
 * donde el usuario puede introducir la anotación
 * pertinente sobre la franja elegida
 * This function is in charge of showing a form
 * where the user can introduce the entry
 * pertinent to the chosen strip(***might mean string, not sure about this one*** will notate with "***")
 */

function interview_take_notes($slotid, $id, $course) {

    // Almacena el contexto de la instancia como un objeto y si no lo hay, lo crea
    // Compile the context of the instance like an object and if there isn't one, create it
    $context = get_context_instance(CONTEXT_MODULE, $id);

	// Almacena la franja temporal de la que se quiere tomar notas
    // Compile the temporary strip*** that you want to take notes from
	$slot = get_record('interview','id', $slotid);

	// Si tiene la capacidad de hacer anotaciones
    // If you have the capability to make notations
    if (has_capability('mod/interview:takenotes', $context)) {

		// Si aún no tiene establecida ninguna anotación
        // If you don't have an established notation
		if (empty($slot->notes)) {

			// Se crea el formulario
            // Create the form
			echo '<center>';
    		echo "<table cellpadding=\"20\" cellspacing=\"20\" class=\"boxaligncenter\"><tr>";
            echo "<td align=\"center\" valign=\"top\">";
        	print_heading(get_string('addnote', 'interview'), 'center');
            print_textarea($usehtmleditor, 5, 60, 200, 100, 'note');
			echo "<br/>";
			echo '<input type="hidden" name="action" value="savenote" />';
			echo '<input type="hidden" name="id" value="'.$id.'" />';
			echo '<input type="hidden" name="slotid" value="'.$slotid.'" />';
                        echo '<input type="submit" value="';
                        echo get_string('accept', 'interview');
                        echo '"/>';
	        echo "</td>";
        	echo "</tr>";
        	echo "</table>";
        	echo '</center>';

		// Si la franja ya tiene una nota muestra error
		// indicando que no se puede sobreescribir
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
 * Esta función se encarga de mostrar un formulario
 * donde el usuario puede modificar una anotación
 * previamente hecha sobre una franja horaria
 * This function is in charge of showing the form
 * where the user can modify the notation
 * previously made about the horary strip***
 */

function interview_modify_notes($note, $slotid, $id, $course) {

    // Almacena el contexto de la instancia como un objeto y si no lo hay, lo crea
    // Compile the context of the instance like an object and if there isn't one, create it
    $context = get_context_instance(CONTEXT_MODULE, $id);

	// Almacena la franja temporal de la que se quiere tomar notas
    // Compile the temporary strip*** that you want to take notes from
	$slot = get_record('interview','id', $slotid);

	// Si tiene la capacidad de modificar anotaciones
    // If it has the capability to modify the notations
    if (has_capability('mod/interview:modifynotes', $context)) {

		// Se asegura de que exista previamente la nota
        // It assures that the note previously exists
		if (!empty($note)) {

			// Se crea el formulario
            // Create the form
			echo '<center>';
    		echo "<table cellpadding=\"20\" cellspacing=\"20\" class=\"boxaligncenter\"><tr>";
            echo "<td align=\"center\" valign=\"top\">";
        	print_heading(get_string('modifynote', 'interview'), 'center');
            print_textarea($usehtmleditor, 5, 60, 200, 100, 'note', $note);
			echo "<br/>";
			echo '<input type="hidden" name="action" value="savenote" />';
			echo '<input type="hidden" name="id" value="'.$id.'" />';
			echo '<input type="hidden" name="slotid" value="'.$slotid.'" />';
			  echo '<input type="submit" value="';
                          echo get_string('accept', 'interview');
                          echo '"/>';	        
                echo "</td>";
        	echo "</tr>";
        	echo "</table>";
        	echo '</center>';

       	// Si la nota que se quiere modificar no existe,
       	// muestra error
        // If the note that you want to modify doesn't exist
        // shows an error
	   	} else {
			error(get_string('notnote', 'interview'));
	   	}
   }

    // Muestra el pie de página
    // Shows the footer
    print_footer($course);
    exit;
}

?>