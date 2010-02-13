<?php  //$Id$

//1 blocks morning

$settings->add(new admin_setting_heading('interviewmethodheading1', get_string('interviewblock1', 'interview'),
                   get_string('interviewblockexplain', 'interview')));

$settings->add(new admin_setting_configcheckbox('interview_timeblock1enabled', get_string('enabled', 'interview'),
                   get_string('interviewtimeblockenabled', 'interview'), 1));

$settings->add(new admin_setting_configtext('interview_timeblock1name', get_string('timeblockname', 'interview'),
                   get_string('interviewtimeblockname', 'interview'), get_string('timeblock1namedefault', 'interview')));

$settings->add(new admin_setting_configtext('interview_timeblock1open', get_string('timeblockopen', 'interview'),
                   get_string('interviewtimeblockopen', 'interview'), 8, PARAM_INT));

$settings->add(new admin_setting_configtext('interview_timeblock1close', get_string('timeblockclose', 'interview'),
                   get_string('interviewtimeblockclose', 'interview'), 13, PARAM_INT));

//2 afternoonevening

$settings->add(new admin_setting_heading('interviewmethodheading2', get_string('interviewblock2', 'interview'),
                   get_string('interviewblockexplain', 'interview')));

$settings->add(new admin_setting_configcheckbox('interview_timeblock2enabled', get_string('enabled', 'interview'),
                   get_string('interviewtimeblockenabled', 'interview'), 1));

$settings->add(new admin_setting_configtext('interview_timeblock2name', get_string('timeblockname', 'interview'),
                   get_string('interviewtimeblockname', 'interview'), get_string('timeblock2namedefault', 'interview')));

$settings->add(new admin_setting_configtext('interview_timeblock2open', get_string('timeblockopen', 'interview'),
                   get_string('interviewtimeblockopen', 'interview'), 12, PARAM_INT));

$settings->add(new admin_setting_configtext('interview_timeblock2close', get_string('timeblockclose', 'interview'),
                   get_string('interviewtimeblockclose', 'interview'), 17, PARAM_INT));

//3 daytime

$settings->add(new admin_setting_heading('interviewmethodheading3', get_string('interviewblock3', 'interview'),
                   get_string('interviewblockexplain', 'interview')));

$settings->add(new admin_setting_configcheckbox('interview_timeblock3enabled', get_string('enabled', 'interview'),
                   get_string('interviewtimeblockenabled', 'interview'), 1));

$settings->add(new admin_setting_configtext('interview_timeblock3name', get_string('timeblockname', 'interview'),
                   get_string('interviewtimeblockname', 'interview'), get_string('timeblock3namedefault', 'interview')));

$settings->add(new admin_setting_configtext('interview_timeblock3open', get_string('timeblockopen', 'interview'),
                   get_string('interviewtimeblockopen', 'interview'), 8, PARAM_INT));

$settings->add(new admin_setting_configtext('interview_timeblock3close', get_string('timeblockclose', 'interview'),
                   get_string('interviewtimeblockclose', 'interview'), 17, PARAM_INT));

//4 evening

$settings->add(new admin_setting_heading('interviewmethodheading4', get_string('interviewblock4', 'interview'),
                   get_string('interviewblockexplain', 'interview')));

$settings->add(new admin_setting_configcheckbox('interview_timeblock4enabled', get_string('enabled', 'interview'),
                   get_string('interviewtimeblockenabled', 'interview'), 1));

$settings->add(new admin_setting_configtext('interview_timeblock4name', get_string('timeblockname', 'interview'),
                   get_string('interviewtimeblockname', 'interview'), get_string('timeblock4namedefault', 'interview')));

$settings->add(new admin_setting_configtext('interview_timeblock4open', get_string('timeblockopen', 'interview'),
                   get_string('interviewtimeblockopen', 'interview'), 16, PARAM_INT));

$settings->add(new admin_setting_configtext('interview_timeblock4close', get_string('timeblockclose', 'interview'),
                   get_string('interviewtimeblockclose', 'interview'), 22, PARAM_INT));


?>
