<?php

/**
 * Definición de las posibilidades/capacidades de actuación sobre el módulo Entrevista Personal.
 *
 * Las capacidades son cargadas en la base de datos cuando el módulo es
 * instalado o actualizado. Siempre que esta definición sea actualizada,
 * la versión del módulo debe ser aumentada.
 *
 * El sistema tiene cuatro posibles valores para la posibilidad de actuación:
 * CAP_ALLOW, CAP_PREVENT, CAP_PROHIBIT e inherit (no determinada).
 *
 * Convención de nombrado:
 *
 * Es importante que los nombres de las capacidades sean únicos. La convención de nombrado
 * para capacidades que sean específicas para módulos y bloques es la siguiente:
 *   [mod/block]/<component_name>:<capabilityname>
 *
 * component_name debe ser el mismo que el nombre del directorio del módulo o bloque.
 *
 * Lo esencial de las capacidades de Moodle es definido de esta forma:
 *    moodle/<capabilityclass>:<capabilityname>
 *
 * Ejemplos: mod/forum:viewpost
 *           block/recent_activity:view
 *           moodle/site:deleteuser
 *
 * La variable para el array de definición de capacidades sigue el formato
 *   $<componenttype>_<component_name>_capabilities
 *
 * Para las capacidades centrales, la variable es $moodle_capabilities.
 */


$capabilities = array(

    // Elección
	//Select slot
    'mod/interview:choose' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
        	'student' => CAP_ALLOW
        )
    ),

    // Cambio de franja
	// Change selected slot
    'mod/interview:change' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
        	'student' => CAP_ALLOW
        )
    ),

	// Borrar franjas temporales
	// Delete slot
    'mod/interview:edit' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
    ),

	// Borrar franjas temporales
	// Delete slot
    'mod/interview:deleteslots' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
    ),

    // Liberar franjas temporales
	// Release a slot select by a student
    'mod/interview:freeslots' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
    ),

    // Asignar franjas temporales
	// Assign a slot to a student
    'mod/interview:assignslot' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
    ),

	// Tomar notas
	//take note on a student
    'mod/interview:takenotes' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
    ),

    // Modificar notas
	//modify the notes on a student
    'mod/interview:modifynotes' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'admin' => CAP_ALLOW
        )
    )
);

?>