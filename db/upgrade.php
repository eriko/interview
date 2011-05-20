<?php  //$Id$

// This file keeps track of upgrades to 
// the forum module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

function xmldb_interview_upgrade($oldversion=0) {

    global $CFG, $THEME, $DB;
	$dbman = $DB->get_manager();
    $result = true;

/// And upgrade begins here. For each one, you'll need one 
/// block of code similar to the next one. Please, delete 
/// this comment lines once this file start handling proper
/// upgrade code.

/// if ($result && $oldversion < YYYYMMDD00) { //New version in version.php
///     $result = result of "/lib/ddllib.php" function calls
/// }

    if ($oldversion < 2011051901) {

        // Define field available to be added to interview_slots
        $table = new xmldb_table('interview_slots');
        $field = new xmldb_field('available', XMLDB_TYPE_INTEGER, '1', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, '1', 'timemodified');

        // Conditionally launch add field available
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // interview savepoint reached
        upgrade_mod_savepoint(true, 2011051901, 'interview');
    }

	 if ($oldversion < 2011052000) {

        // Rename field intro on table interview to NEWNAMEGOESHERE
        $table = new xmldb_table('interview');
        $field = new xmldb_field('description', XMLDB_TYPE_TEXT, 'small', null, XMLDB_NOTNULL, null, null, 'name');

        // Launch rename field intro
        $dbman->rename_field($table, $field, 'intro');

		// Define field introformat to be added to interview
		$field = new xmldb_field('introformat', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'intro');

        // Conditionally launch add field introformat
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // interview savepoint reached
        upgrade_mod_savepoint(true, 2011052000, 'interview');
    }

    return $result;
}

?>
