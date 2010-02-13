<?php

/**
 * Este archivo php se encarga de mostrar las notas
 * creadas para las diferentes franjas horarias
 */

	// Se incluyen los ficheros especificados
    require_once("../../config.php");
    require_once("lib.php");

	// Recogen los parámetros necesarios
	$id = required_param(id, PARAM_INT);
	$slotid = required_param(slotid, PARAM_INT);

?>

<html>

<head>
		<title> <?php print_string('viewnote', 'interview') ?> </title>
</head>

<body>
		<?php

    	// Se construye la tabla que mostrará la nota
		echo '<table cellpadding="15" width="100%" height="100%">';
		echo '<tr><td align="center">';
		echo '<img src="note.ico">';
		echo '<font face="Comic Sans MS" size=5>';
		echo '<b>';
		print_string('note', 'interview');
		echo '</b>';
		echo '</font>';
		echo '</td></tr>';
		echo '<tr>';
		echo '<td valign="middle" align="center" width="100%" height="100%" style="border:3px #0099cc solid">';
		$slot = get_record('interview_slots', 'id', $slotid);
		echo '<font face="Arial" size=4>';
		echo $slot->note;
		echo '</font>';
		echo '</td>';
		echo '</tr>';
		echo '</table>';
		echo '</form>';

?>

</body>
</html>