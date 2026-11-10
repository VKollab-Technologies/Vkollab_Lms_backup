<?php

require('../../config.php');

require_login();

global $DB, $USER;

$courseid = optional_param('courseid', 0, PARAM_INT);
$returnurl = optional_param(
    'returnurl',
    '',
    PARAM_URL
);

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $courseid = optional_param(
    'courseid',
    0,
    PARAM_INT
);

$returnurl = optional_param(
    'returnurl',
    '',
    PARAM_URL
);

    $password = required_param('password', PARAM_RAW);

    $acknowledge = optional_param('acknowledge', 0, PARAM_INT);

    $user = authenticate_user_login(
        $USER->username,
        $password
    );

    if (!$user) {

        $message = '<p style="color:red;">
        Invalid password.
        </p>';

    } else if (!$acknowledge) {

        $message = '<p style="color:red;">
        Please accept the acknowledgement.
        </p>';

    } else {

        $exists = $DB->record_exists(
            'course_acknowledgements',
            [
                'userid' => $USER->id,
                'courseid' => $courseid
            ]
        );

        if (!$exists) {

            $record = new stdClass();

            $record->userid = $USER->id;
            $record->courseid = $courseid;
            $record->acknowledged = 1;
            $record->timecreated = time();

            $DB->insert_record(
                'course_acknowledgements',
                $record
            );
        }
         echo '<pre>';
         print_r($_REQUEST);
         echo '</pre>';
         die();
        if (!empty($returnurl)) {

    redirect(
    new moodle_url($returnurl),
    'Acknowledgement recorded successfully.',
    2
);

} else {

    redirect(
        new moodle_url(
            '/course/view.php',
            [
                'id' => $courseid
            ]
        ),
        'Acknowledgement recorded successfully.',
        2
    );
}

    }
}

echo '

<html>

<head>
<title>Course Acknowledgement</title>
</head>

<body style="
font-family:Arial;
margin:50px;">

<h2>Course Acknowledgement</h2>

'.$message.'

<form method="post">
<input
    type="hidden"
    name="returnurl"
    value="'.$returnurl.'">

<input
    type="hidden"
    name="courseid"
    value="'.$courseid.'">

    <p>

        <label>Username</label>

        <br>

        <input
            type="text"
            value="'.$USER->username.'"
            readonly>

    </p>

    <p>

        <label>Password</label>

        <br>

        <input
            type="password"
            name="password"
            required>

    </p>

    <p>

        <input
            type="checkbox"
            name="acknowledge"
            value="1"
            required>

        I confirm that I have completed and understood the course content.

    </p>

    <p>

        <input
            type="submit"
            value="Submit">

    </p>

</form>

</body>

</html>';
