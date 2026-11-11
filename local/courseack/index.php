<?php

require('../../config.php');

require_login();

global $DB, $USER;

$courseid = optional_param('courseid', 0, PARAM_INT);
$returnurl = optional_param(
    'returnurl',
    '',
    PARAM_RAW
);

$returnurl = urldecode($returnurl);
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
    PARAM_RAW
);

$returnurl = urldecode($returnurl);

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
        
        if (!empty($returnurl)) {
echo '
<script>
window.parent.location.reload();
</script>
';
exit;

} else {

    echo '
<script>
window.parent.location.reload();
</script>
';
exit;
}

    }
}
$course = get_course($courseid);
$fullname = fullname($USER);
$currenttime = date('d-M-Y h:i A');
echo '

<style>
body{
    margin:0;
    padding:10px;
    font-family:Segoe UI, Arial, sans-serif;
    background:transparent;
}

.ack-wrapper{
    display:flex;
    justify-content:center;
    align-items:flex-start;
    min-height:650px;
    padding-top:20px;
}

.ack-card{
    width:700px;
    max-width:95%;
    background:#ffffff;
    border-radius:14px;
    padding:30px;
    box-shadow:0 15px 40px rgba(0,0,0,.15);
}
.ack-header{
    text-align:center;
    margin-bottom:25px;
}
.ack-header h2{
    color:#0f4c81;
    margin:0;
}

.ack-header p{
    color:#666;
    margin-top:10px;
}

.user-box{
    background:#f5f7fa;
    padding:12px;
    border-radius:8px;
    margin-bottom:20px;
}

.form-group{
    margin-bottom:20px;
}

.form-group label{
    display:block;
    margin-bottom:8px;
    font-weight:600;
}

.form-control{
    width:100%;
    padding:12px;
    border:1px solid #d0d7de;
    border-radius:6px;
}

.acknowledge-box{
    background:#f0f8ff;
    border-left:5px solid #0f4c81;
    padding:15px;
    border-radius:6px;
    margin-bottom:20px;
}

.submit-btn{
    background:#0f4c81;
    color:white;
    border:none;
    padding:12px 30px;
    border-radius:6px;
    cursor:pointer;
    font-size:15px;
}

.submit-btn:hover{
    background:#09355b;
}

</style>

<div class="ack-wrapper">
<div class="ack-card">

<div class="ack-header">

<h2>Course Acknowledgement</h2>

<p>Please review the information below and provide your electronic acknowledgement before proceeding with the assessment. This acknowledgement will be recorded for compliance and audit purposes.</p>

</div>

'.$message.'

<form method="post">

<input type="hidden" name="returnurl" value="'.$returnurl.'">

<input type="hidden" name="courseid" value="'.$courseid.'">

<div class="user-box">

<div class="user-box">

<div style="margin-bottom:8px;">
<strong>Course:</strong>
'.$course->fullname.'
</div>

<div style="margin-bottom:8px;">
<strong>Learner Name:</strong>
'.$fullname.'
</div>

<div style="margin-bottom:8px;">
<strong>Username:</strong>
'.$USER->username.'
</div>

<div>
<strong>Date & Time:</strong>
'.$currenttime.'
</div>

</div>

</div>

<div class="form-group">

<label>Password</label>

<input
class="form-control"
type="password"
name="password"
placeholder="Enter your password"
required>

</div>

<div class="acknowledge-box">

<div style="
background:#fff3cd;
color:#856404;
padding:10px;
border-radius:5px;
font-weight:600;
margin-bottom:12px;">

Electronic Signature Required

</div>

<input
type="checkbox"
name="acknowledge"
value="1"
required>

I acknowledge that I have completed all required learning content in this course and understand the information presented. I understand that this acknowledgement serves as my electronic signature and will be recorded for training compliance and audit purposes.

</div>

<div style="text-align:center;">

<button
class="submit-btn"
type="submit">

Submit Acknowledgement

</button>

</div>

</form>

</div>
</div>
<script>

history.pushState(null, null, location.href);

window.onpopstate = function () {
    history.go(1);
};

</script>

';