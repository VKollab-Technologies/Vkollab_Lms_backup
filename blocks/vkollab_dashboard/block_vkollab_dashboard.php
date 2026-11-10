<?php

defined('MOODLE_INTERNAL') || die();

class block_vkollab_dashboard extends block_base {

    public function init() {
        $this->title = 'VKollab Dashboard';
    }

    public function applicable_formats() {
        return array('all' => true);
    }

    public function get_content() {
        global $DB, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();

        $isadmin = is_siteadmin();

        $roles = get_user_roles(context_system::instance(), $USER->id);

        $isreportingmanager = false;

        $ispracticemanager = false;
      
        $isceo = false;
        $iscontentadmin = false;

        foreach ($roles as $role) {

            if ($role->shortname == 'reportingmanager') {
                 $isreportingmanager = true;
            }

            if ($role->shortname == 'practicemanager') {
                $ispracticemanager = true;
            }

            if ($role->shortname == 'ceo'){
                $isceo = true;
            }
            if ($role->shortname == 'contentadmin') {
                $iscontentadmin = true;
            }
        }


       

        // ===================================
        // REPORTING MANAGER DASHBOARD
        // ===================================
        if ($isreportingmanager) {

            $managername = $USER->firstname;

$teammembers = $DB->count_records_sql("
    SELECT COUNT(*)
    FROM {user_info_data} d
    JOIN {user_info_field} f
        ON f.id = d.fieldid
    WHERE f.shortname = 'reporting_manager'
    AND d.data = ?
", array($managername));

$learnerids = $DB->get_fieldset_sql("
    SELECT u.id
    FROM {user} u
    JOIN {user_info_data} d
        ON d.userid = u.id
    JOIN {user_info_field} f
        ON f.id = d.fieldid
    WHERE f.shortname = 'reporting_manager'
    AND d.data = ?
", array($managername));

$completedtrainings = 0;
$pendingtrainings = 0;

foreach ($learnerids as $learnerid) {

    $assigned = $DB->count_records(
        'user_enrolments',
        array('userid' => $learnerid)
    );

    $completed = $DB->count_records_select(
        'course_completions',
        'userid = ? AND timecompleted IS NOT NULL',
        array($learnerid)
    );

    $completedtrainings += $completed;

    $pendingtrainings += max(
        0,
        $assigned - $completed
    );
}

$teamcompletion = 0;

if (($completedtrainings + $pendingtrainings) > 0) {

    $teamcompletion = round(
        ($completedtrainings /
        ($completedtrainings + $pendingtrainings)) * 100
    );
}
            $this->content->text = '

            <div style="
background:linear-gradient(90deg,#003B6F,#005A9C);
color:white;
padding:20px;
border-radius:12px;
margin-bottom:20px;
box-shadow:0 4px 12px rgba(0,0,0,.15);">

<h2 style="margin:0; color:#ffffff !important;">
Team Learning & Compliance Dashboard
</h2>

<p style="
margin-top:8px;
opacity:.9;
font-size:14px;">
Monitor team training completion, compliance status and progress.
</p>

</div>

            <div style="
                display:flex;
                flex-wrap:wrap;
                gap:15px;">

                <div style="
                    background:#ffffff;
padding:20px;
border-radius:12px;
width:220px;
text-align:center;
border-top:4px solid #005A9C;
box-shadow:0 4px 12px rgba(0,0,0,.08);
                    <h3 style="
margin:0;
font-size:14px;
font-weight:600;
color:#44546A;">
Team Members
</h3>
                   <h1 style="
margin-top:10px;
font-size:42px;
font-weight:700;
color:#003B6F;">
'.$teammembers.'
</h1>
                </div>

                <div style="
                    background:#ffffff;
padding:20px;
border-radius:12px;
width:220px;
text-align:center;
border-top:4px solid #005A9C;
box-shadow:0 4px 12px rgba(0,0,0,.08);
                   <h3 style="
margin:0;
font-size:14px;
font-weight:600;
color:#44546A;">
Completed
</h3>
                   <h1 style="
margin-top:10px;
font-size:42px;
font-weight:700;
color:#003B6F;">
'.$completedtrainings.'
</h1>
                </div>

                <div style="
                  background:#ffffff;
padding:20px;
border-radius:12px;
width:220px;
text-align:center;
border-top:4px solid #005A9C;
box-shadow:0 4px 12px rgba(0,0,0,.08);
                    <h3 style="
margin:0;
font-size:14px;
font-weight:600;
color:#44546A;">
Pending
</h3>
                    <h1 style="
margin-top:10px;
font-size:42px;
font-weight:700;
color:#003B6F;">
'.$pendingtrainings.'
</h1>
                </div>

                <div style="
                    background:#ffffff;
padding:20px;
border-radius:12px;
width:220px;
text-align:center;
border-top:4px solid #005A9C;
box-shadow:0 4px 12px rgba(0,0,0,.08);
                    <h3 style="
margin:0;
font-size:14px;
font-weight:600;
color:#44546A;">
Team Completion
</h3>
                   <h1 style="
margin-top:10px;
font-size:42px;
font-weight:700;
color:#003B6F;">
'.$teamcompletion.'%
</h1>
                </div>

            </div>';

        $this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
box-shadow:0 4px 12px rgba(0,0,0,.08);
margin-top:20px;">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
Team Progress
</h3>

<div style="
background:#E5E7EB;
height:18px;
border-radius:20px;">

<div style="
width:'.$teamcompletion.'%;
height:18px;
background:linear-gradient(
90deg,
#005A9C,
#0078D4
);
border-radius:20px;">
</div>

</div>

<div style="
display:flex;
justify-content:space-between;
margin-top:10px;
font-weight:600;
color:#44546A;">

<span>Team Completion</span>
<span>'.$teamcompletion.'%</span>

</div>

</div>';

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
box-shadow:0 4px 12px rgba(0,0,0,.08);
margin-top:20px;">

<h3 style="
color:#003B6F;">
Team Learning Summary
</h3>

<ul style="
line-height:2;
color:#44546A;">

<li>Team Members: '.$teammembers.'</li>

<li>Completed Training: '.$completedtrainings.'</li>

<li>Pending Training: '.$pendingtrainings.'</li>

<li>Completion Rate: '.$teamcompletion.'%</li>

</ul>

</div>';

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
box-shadow:0 4px 10px rgba(0,0,0,.08);
margin-top:20px;">

<h3 style="
color:#003B6F;">
Team Status Overview
</h3>

<p>
Completed Trainings : '.$completedtrainings.'
</p>

<p>
Pending Trainings : '.$pendingtrainings.'
</p>

</div>';
$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
 Team Learning Progress
</h3>

<table style="
width:100%;
border-collapse:collapse;">

<tr style="background:#F5F7FA;">
<th style="padding:10px;">Employee</th>
<th>Assigned</th>
<th>Completed</th>
<th>Pending</th>
<th>Completion %</th>
</tr>';
foreach ($learnerids as $learnerid) {

    $learner = $DB->get_record(
        'user',
        array('id' => $learnerid)
    );

    $assigned = $DB->count_records(
        'user_enrolments',
        array('userid' => $learnerid)
    );

    $completed = $DB->count_records_select(
        'course_completions',
        'userid = ? AND timecompleted IS NOT NULL',
        array($learnerid)
    );

    $pending = max(
        0,
        $assigned - $completed
    );

    $completionrate = 0;

    if ($assigned > 0) {
        $completionrate =
            round(($completed / $assigned) * 100);
    }

    $this->content->text .= '

    <tr>

        <td style="padding:10px;">
            '.$learner->firstname.'
        </td>

        <td>'.$assigned.'</td>

        <td>'.$completed.'</td>

        <td>'.$pending.'</td>

        <td>'.$completionrate.'%</td>

    </tr>';
}
$this->content->text .= '

</table>

</div>';

// ===================================
// OVERDUE TRAINING TRACKER
// ===================================

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
Overdue Training Tracker
</h3>

<table style="
width:100%;
border-collapse:collapse;">

<tr style="background:#F5F7FA;">
    <th style="padding:10px;">Employee</th>
    <th>Course</th>
    <th>Due Date</th>
    <th>Days Overdue</th>
</tr>';
foreach ($learnerids as $learnerid) {

    $learner = $DB->get_record(
        'user',
        array('id' => $learnerid)
    );

    $courses = $DB->get_records_sql("
        SELECT
            c.id,
            c.fullname,
            c.enddate
        FROM {course} c
        JOIN {enrol} e
            ON e.courseid = c.id
        JOIN {user_enrolments} ue
            ON ue.enrolid = e.id
        WHERE ue.userid = ?
        AND c.id > 1
    ", array($learnerid));

    foreach ($courses as $course) {

        $completed = $DB->record_exists(
            'course_completions',
            array(
                'userid' => $learnerid,
                'course' => $course->id
            )
        );

        if (
            !$completed &&
            $course->enddate > 0 &&
            $course->enddate < time()
        ) {

            $daysoverdue =
                floor((time() - $course->enddate) / 86400);

            $this->content->text .= '

            <tr>

                <td style="padding:10px;">
                    '.$learner->firstname.'
                </td>

                <td>
                    '.$course->fullname.'
                </td>

                <td>
                    '.date("d-M-Y", $course->enddate).'
                </td>

                <td style="
                    color:#D83B01;
                    font-weight:600;">
                    '.$daysoverdue.' Days
                </td>

            </tr>';
        }
    }
}
$this->content->text .= '

</table>

</div>';

// ===================================
// CERTIFICATES SUMMARY
// ===================================

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
Certificates Summary
</h3>

<table style="
width:100%;
border-collapse:collapse;">

<tr style="background:#F5F7FA;">
    <th style="padding:10px;">Employee</th>
    <th>Certificates Earned</th>
</tr>';

foreach ($learnerids as $learnerid) {

    $learner = $DB->get_record(
        'user',
        array('id' => $learnerid)
    );

    $certificatecount = $DB->count_records_select(
        'course_completions',
        'userid = ? AND timecompleted IS NOT NULL',
        array($learnerid)
    );

    $this->content->text .= '

    <tr>

        <td style="padding:10px;">
            '.$learner->firstname.'
        </td>

        <td>'.$certificatecount.'</td>

    </tr>';
}
$this->content->text .= '

</table>

</div>';
// ===================================
// ASSESSMENT ANALYTICS
// ===================================

$totalattempts = 0;
$totalscore = 0;
$highestscore = 0;
$lowestscore = null;
$passcount = 0;

$attempts = array();

foreach ($learnerids as $learnerid) {

    $userattempts = $DB->get_records_sql("
        SELECT
            qa.sumgrades,
            q.grade AS maxgrade
        FROM {quiz_attempts} qa
        JOIN {quiz} q
            ON q.id = qa.quiz
        WHERE qa.userid = ?
        AND qa.state = 'finished'
    ", array($learnerid));

    foreach ($userattempts as $attempt) {
        $attempts[] = $attempt;
    }
}

foreach ($attempts as $attempt) {

    if ($attempt->maxgrade > 0) {

        $score = round(
            ($attempt->sumgrades /
            $attempt->maxgrade) * 100
        );

        $totalscore += $score;
        $totalattempts++;

        if ($score > $highestscore) {
            $highestscore = $score;
        }

        if ($lowestscore === null || $score < $lowestscore) {
            $lowestscore = $score;
        }

        if ($score >= 50) {
            $passcount++;
        }
    }
}

if ($totalattempts > 0) {

    $averagescore = round(
        $totalscore / $totalattempts
    );

    $passrate = round(
        ($passcount / $totalattempts) * 100
    );

    $failrate = 100 - $passrate;

} else {

    $averagescore = 0;
    $highestscore = 0;
    $lowestscore = 0;
    $passrate = 0;
    $failrate = 0;
}

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
Assessment Analytics
</h3>

<div style="
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:12px;">

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #005A9C;
border-radius:8px;">
Average Score : '.$averagescore.'%
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #107C10;
border-radius:8px;">
Highest Score : '.$highestscore.'%
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #D83B01;
border-radius:8px;">
Lowest Score : '.$lowestscore.'%
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #0078D4;
border-radius:8px;">
Pass Rate : '.$passrate.'%
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #C50F1F;
border-radius:8px;">
Fail Rate : '.$failrate.'%
</div>

</div>

</div>';
$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
Team Learning Trend
</h3>

<table style="
width:100%;
border-collapse:collapse;">

<tr style="background:#F5F7FA;">
    <th style="padding:10px;">Month</th>
    <th>Completion Rate</th>
</tr>

<tr>
    <td style="padding:10px;">October 2026</td>
    <td>'.$teamcompletion.'%</td>
</tr>

</table>

</div>';

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
Reports
</h3>

<div style="
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:12px;">

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #005A9C;
border-radius:8px;">
Team Completion Report
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #107C10;
border-radius:8px;">
 Overdue Training Report
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #0078D4;
border-radius:8px;">
Assessment Report
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #D83B01;
border-radius:8px;">
Certificate Report
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #6A1B9A;
border-radius:8px;">
 Learner Progress Report
</div>

</div>

</div>';

            return $this->content;
        }

        // ===================================
// PRACTICE MANAGER DASHBOARD
// ===================================

if ($ispracticemanager) {

    $managername = $USER->firstname;

$totallearners = $DB->count_records_sql("
    SELECT COUNT(*)
    FROM {user_info_data} d
    JOIN {user_info_field} f
        ON f.id = d.fieldid
    WHERE f.shortname = 'practice_manager'
    AND d.data = ?
", array($managername));

    $totalcourses = $DB->count_records_select(
        'course',
        'id > 1'
    );

   $learnerids = $DB->get_fieldset_sql("
    SELECT u.id
    FROM {user} u
    JOIN {user_info_data} d
        ON d.userid = u.id
    JOIN {user_info_field} f
        ON f.id = d.fieldid
    WHERE f.shortname = 'practice_manager'
    AND d.data = ?
", array($managername));

$totalenrollments = 0;

if (!empty($learnerids)) {

    foreach ($learnerids as $learnerid) {

        $totalenrollments += $DB->count_records(
            'user_enrolments',
            array('userid' => $learnerid)
        );
    }
}

  $completed = 0;

if (!empty($learnerids)) {

    foreach ($learnerids as $learnerid) {

        $completed += $DB->count_records_select(
            'course_completions',
            'userid = ? AND timecompleted IS NOT NULL',
            array($learnerid)
        );
    }
}

    $completionpercentage = 0;

    if ($totalenrollments > 0) {
        $completionpercentage =
            round(($completed / $totalenrollments) * 100);
    }

    $this->content->text = '

    <div style="
    background:linear-gradient(
    90deg,
    #003B6F,
    #005A9C
    );
    color:white;
    padding:24px;
    border-radius:12px;
    margin-bottom:20px;
    box-shadow:0 4px 12px rgba(0,0,0,.15);">

    <h2 style="margin:0;
     color:#ffffff !important;">
    Practice Learning Dashboard
    </h2>

    <p style="
    margin-top:8px;
    color:#ffffff !important;
    opacity:0.9;">
    Monitor learning engagement and training progress.
    </p>

    </div>

    <div style="
    display:flex;
    flex-wrap:wrap;
    gap:20px;">

        <div style="
        background:#ffffff;
        padding:20px;
        border-radius:12px;
        width:220px;
        text-align:center;
        border-top:4px solid #005A9C;
        box-shadow:0 4px 12px rgba(0,0,0,.08);">
        <h3 style="margin:0;color:#44546A;">Learners</h3>
        <h1 style="
        color:#003B6F;
        font-size:42px;">'.$totallearners.'</h1>
        </div>

        <div style="
        background:#ffffff;
        padding:20px;
        border-radius:12px;
        width:220px;
        text-align:center;
        border-top:4px solid #005A9C;
        box-shadow:0 4px 12px rgba(0,0,0,.08);">
        <h3 style="margin:0;color:#44546A;">Courses</h3>
        <h1 style="
        color:#003B6F;
        font-size:42px;">'.$totalcourses.'</h1>
        </div>

        <div style="
        background:#ffffff;
        padding:20px;
        border-radius:12px;
        width:220px;
        text-align:center;
        border-top:4px solid #0078D4;
        box-shadow:0 4px 12px rgba(0,0,0,.08);">
        <h3 style="margin:0;color:#44546A;">Enrollments</h3>
        <h1 style="
        color:#003B6F;
        font-size:42px;">'.$totalenrollments.'</h1>
        </div>

        <div style="
        background:#ffffff;
        padding:20px;
        border-radius:12px;
        width:220px;
        text-align:center;
        border-top:4px solid #107C10;
        box-shadow:0 4px 12px rgba(0,0,0,.08);">
        <h3 style="margin:0;color:#44546A;">Completed</h3>
        <h1 style="
        color:#003B6F;
        font-size:42px;">'.$completed.'</h1>
        </div>

        <div style="
        background:#ffffff;
        padding:20px;
        border-radius:12px;
        width:220px;
        text-align:center;
        border-top:4px solid #D83B01;
        box-shadow:0 4px 12px rgba(0,0,0,.08);">
        <h3 style="margin:0;color:#44546A;">
        Completion %
        </h3>
        <h1 style="
        color:#003B6F;
        font-size:42px;">
        '.$completionpercentage.'%
        </h1>
        </div>

    </div>

    <div style="
    background:#ffffff;
    padding:20px;
    border-radius:12px;
    border:1px solid #D9E2EC;
    margin-top:20px;
    box-shadow:0 4px 12px rgba(0,0,0,.08);">

    <h3 style="
    color:#003B6F;">
     Practice Progress
    </h3>

    <div style="
    background:#E5E7EB;
    height:18px;
    border-radius:20px;">

    <div style="
    width:'.$completionpercentage.'%;
    height: 22px;
    background:linear-gradient(
    90deg,
    #005A9C,
    #0078D4
    );
    border-radius:30px;">
    </div>

    </div>

    <p style="
    margin-top:10px;
    font-weight:600;
    color:#44546A;">
    '.$completionpercentage.'% Completion Rate
    </p>

    </div>';

    $this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
 Practice Learning Summary
</h3>

<div style="
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:12px;">

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #005A9C;
border-radius:8px;">
Learners: '.$totallearners.'
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #005A9C;
border-radius:8px;">
Courses: '.$totalcourses.'
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #005A9C;
border-radius:8px;">
Enrollments: '.$totalenrollments.'
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #107C10;
border-radius:8px;">
Completion Rate: '.$completionpercentage.'%
</div>

</div>

</div>';
$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
Employee Learning Progress
</h3>

<table style="
width:100%;
border-collapse:collapse;">

<tr style="
background:#F5F7FA;">
<th style="padding:10px;">Employee</th>
<th>Assigned</th>
<th>Completed</th>
<th>Pending</th>
<th>Completion %</th>
</tr>';
foreach ($learnerids as $learnerid) {

    $learner = $DB->get_record(
        'user',
        array('id' => $learnerid)
    );

    $assigned = $DB->count_records(
        'user_enrolments',
        array('userid' => $learnerid)
    );

    $completedcount = $DB->count_records_select(
        'course_completions',
        'userid = ? AND timecompleted IS NOT NULL',
        array($learnerid)
    );

    $pendingcount = max(
        0,
        $assigned - $completedcount
    );

    $completionrate = 0;

    if ($assigned > 0) {
        $completionrate =
            round(($completedcount / $assigned) * 100);
    }
    $employeeperformance .= '

<div style="margin-bottom:20px;">

    <div style="
        display:flex;
        justify-content:space-between;
        margin-bottom:8px;
        color:#44546A;
        font-weight:600;">
        <span>'.$learner->firstname.'</span>
        <span>'.$completionrate.'%</span>
    </div>

    <div style="
        background:#E5E7EB;
        height:16px;
        border-radius:20px;">

        <div style="
            width:'.$completionrate.'%;
            height:16px;
            background:linear-gradient(
                90deg,
                #005A9C,
                #0078D4
            );
            border-radius:20px;">
        </div>

    </div>

</div>';

    $this->content->text .= '

    <tr>
        <td style="padding:10px;">
            '.$learner->firstname.'
        </td>

        <td>'.$assigned.'</td>

        <td>'.$completedcount.'</td>

        <td>'.$pendingcount.'</td>

        <td>'.$completionrate.'%</td>
    </tr>';
}
$this->content->text .= '

</table>

</div>';
$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
 Employee Performance
</h3>

'.$employeeperformance.'

</div>';

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">



<h3 style="
color:#003B6F;
margin-bottom:15px;">
Course Analytics
</h3>

<table style="
width:100%;
border-collapse:collapse;">

<tr style="background:#F5F7FA;">
    <th style="padding:10px;">Course</th>
    <th>Enrolled</th>
    <th>Completed</th>
    <th>Pending</th>
    <th>Completion %</th>
</tr>';
$courses = $DB->get_records_sql("
    SELECT DISTINCT c.id, c.fullname
    FROM {course} c
    WHERE c.id > 1
");

foreach ($courses as $course) {
$enrolled = 0;
$completed = 0;

foreach ($learnerids as $learnerid) {

    // Check learner enrolled in this course.
    $isenrolled = $DB->record_exists_sql("
        SELECT 1
        FROM {user_enrolments} ue
        JOIN {enrol} e
            ON e.id = ue.enrolid
        WHERE ue.userid = ?
        AND e.courseid = ?
    ", array($learnerid, $course->id));

    if ($isenrolled) {

        $enrolled++;

        $completion = $DB->record_exists(
            'course_completions',
            array(
                'userid' => $learnerid,
                'course' => $course->id
            )
        );

        if ($completion) {
            $completed++;
        }
    }
}

    $pending = max(0, $enrolled - $completed);

    $completionrate = 0;

    if ($enrolled > 0) {
        $completionrate =
            round(($completed / $enrolled) * 100);
    }

    $this->content->text .= '

    <tr>
        <td style="padding:10px;">
            '.$course->fullname.'
        </td>

        <td>'.$enrolled.'</td>

        <td>'.$completed.'</td>

        <td>'.$pending.'</td>

        <td>'.$completionrate.'%</td>

    </tr>';
}
$this->content->text .= '

</table>

</div>';

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
Certificates Summary
</h3>

<table style="
width:100%;
border-collapse:collapse;">

<tr style="background:#F5F7FA;">
    <th style="padding:10px;">Employee</th>
    <th>Certificates Earned</th>
</tr>';
foreach ($learnerids as $learnerid) {

    $learner = $DB->get_record(
        'user',
        array('id' => $learnerid)
    );

    // Temporary logic:
    // 1 certificate for each completed course.
    $certificatecount = $DB->count_records_select(
        'course_completions',
        'userid = ? AND timecompleted IS NOT NULL',
        array($learnerid)
    );

    $this->content->text .= '

    <tr>
        <td style="padding:10px;">
            '.$learner->firstname.'
        </td>

        <td>'.$certificatecount.'</td>
    </tr>';
}
$this->content->text .= '

</table>

</div>';

// ===================================
// OVERDUE TRAINING TRACKER
// ===================================

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
Overdue Training Tracker
</h3>

<table style="
width:100%;
border-collapse:collapse;">

<tr style="background:#F5F7FA;">
    <th style="padding:10px;">Employee</th>
    <th>Course</th>
    <th>Due Date</th>
    <th>Days Overdue</th>
</tr>';

foreach ($learnerids as $learnerid) {

    $learner = $DB->get_record(
        'user',
        array('id' => $learnerid)
    );

    $courses = $DB->get_records_sql("
        SELECT c.id,
               c.fullname,
               c.enddate
        FROM {course} c
        JOIN {enrol} e
            ON e.courseid = c.id
        JOIN {user_enrolments} ue
            ON ue.enrolid = e.id
        WHERE ue.userid = ?
        AND c.id > 1
    ", array($learnerid));

    foreach ($courses as $course) {

        $completed = $DB->record_exists(
            'course_completions',
            array(
                'userid' => $learnerid,
                'course' => $course->id
            )
        );

        if (!$completed &&
            $course->enddate > 0 &&
            $course->enddate < time()) {

            $daysoverdue =
                floor((time() - $course->enddate) / 86400);

            $this->content->text .= '

            <tr>

                <td style="padding:10px;">
                    '.$learner->firstname.'
                </td>

                <td>
                    '.$course->fullname.'
                </td>

                <td>
                    '.date("d-M-Y", $course->enddate).'
                </td>

                <td style="
                    color:#D83B01;
                    font-weight:600;">
                    '.$daysoverdue.' Days
                </td>

            </tr>';
        }
    }
}
$this->content->text .= '

</table>

</div>';
// ===================================
// ASSESSMENT ANALYTICS
// ===================================

// ===================================
// ASSESSMENT ANALYTICS
// ===================================

$totalattempts = 0;
$totalscore = 0;
$highestscore = 0;
$lowestscore = null;
$passcount = 0;

$attempts = array();

foreach ($learnerids as $learnerid) {

    $userattempts = $DB->get_records_sql("
        SELECT
            qa.sumgrades,
            q.grade AS maxgrade
        FROM {quiz_attempts} qa
        JOIN {quiz} q
            ON q.id = qa.quiz
        WHERE qa.userid = ?
        AND qa.state = 'finished'
    ", array($learnerid));

    foreach ($userattempts as $attempt) {
        $attempts[] = $attempt;
    }
}

foreach ($attempts as $attempt) {

    if ($attempt->maxgrade > 0) {

        $score = round(
            ($attempt->sumgrades /
            $attempt->maxgrade) * 100
        );

        $totalscore += $score;
        $totalattempts++;

        if ($score > $highestscore) {
            $highestscore = $score;
        }

        if ($lowestscore === null || $score < $lowestscore) {
            $lowestscore = $score;
        }

        if ($score >= 50) {
            $passcount++;
        }
    }
}

if ($totalattempts > 0) {

    $averagescore = round(
        $totalscore / $totalattempts
    );

    $passrate = round(
        ($passcount / $totalattempts) * 100
    );

    $failrate = 100 - $passrate;

} else {

    $averagescore = 0;
    $highestscore = 0;
    $lowestscore = 0;
    $passrate = 0;
    $failrate = 0;
}

if ($lowestscore === null) {
    $lowestscore = 0;
}

$this->content->text .= '



<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
Assessment Analytics
</h3>

<div style="
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:12px;">

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #005A9C;
border-radius:8px;">
Average Score : '.$averagescore.'%
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #107C10;
border-radius:8px;">
Highest Score : '.$highestscore.'%
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #D83B01;
border-radius:8px;">
Lowest Score : '.$lowestscore.'%
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #0078D4;
border-radius:8px;">
Pass Rate : '.$passrate.'%
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #C50F1F;
border-radius:8px;">
Fail Rate : '.$failrate.'%
</div>

</div>

</div>';

// ===================================
// LEARNING TREND
// ===================================

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
 Learning Trend
</h3>

<table style="
width:100%;
border-collapse:collapse;">

<tr style="background:#F5F7FA;">
    <th style="padding:10px;">Month</th>
    <th>Completion Rate</th>
</tr>

<tr>
    <td style="padding:10px;">October 2026</td>
    <td>'.$completionpercentage.'%</td>
</tr>

</table>

</div>';

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
My Courses
</h3>

<table style="
width:100%;
border-collapse:collapse;">

<tr style="background:#F5F7FA;">
<th style="padding:10px;">Course Name</th>
<th>Status</th>
</tr>';

$courses = $DB->get_records_sql("
    SELECT fullname, visible
    FROM {course}
    WHERE id > 1
");

foreach ($courses as $course) {

    $status = $course->visible ?
        'Published' :
        'Draft';

    $this->content->text .= '

    <tr>
        <td style="padding:10px;">
            '.$course->fullname.'
        </td>

        <td>'.$status.'</td>
    </tr>';
}
$this->content->text .= '

</table>

</div>';

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
Quick Actions
</h3>

<div style="
display:grid;
grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
gap:12px;">

<div style="background:#F8FAFC;padding:15px;border-radius:8px;">
 Create Course
</div>

<div style="background:#F8FAFC;padding:15px;border-radius:8px;">
Edit Course
</div>

<div style="background:#F8FAFC;padding:15px;border-radius:8px;">
 Create Quiz
</div>

<div style="background:#F8FAFC;padding:15px;border-radius:8px;">
 Upload Content
</div>

<div style="background:#F8FAFC;padding:15px;border-radius:8px;">
Manage Learning Paths
</div>

<div style="background:#F8FAFC;padding:15px;border-radius:8px;">
Submit For Approval
</div>

</div>

</div>';

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
Reports
</h3>

<div style="
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:12px;">

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #005A9C;
border-radius:8px;">
Employee Progress Report
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #107C10;
border-radius:8px;">
Course Completion Report
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #0078D4;
border-radius:8px;">
Assessment Report
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #D83B01;
border-radius:8px;">
Certificate Report
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #C50F1F;
border-radius:8px;">
Overdue Training Report
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #44546A;
border-radius:8px;">
Practice Analytics Report
</div>

</div>

</div>';

    return $this->content;
}

   

// ===================================
// CEO DASHBOARD
// ===================================

if ($isceo) {

    $employees = $DB->count_records(
        'user',
        array(
            'deleted' => 0,
            'suspended' => 0
        )
    );

    $courses = $DB->count_records_select(
        'course',
        'id > 1'
    );

    $enrollments = $DB->count_records(
        'user_enrolments'
    );

    $completedcourses = $DB->count_records_select(
        'course_completions',
        'timecompleted IS NOT NULL'
    );
    $activelearners = $DB->count_records_select(
    'user',
    'lastaccess > ?',
    array(time() - (30 * 24 * 60 * 60))
);

    $completionpercentage = 0;

    if ($enrollments > 0) {
        $completionpercentage =
            round(($completedcourses / $enrollments) * 100);
    }

    $this->content->text = '
 
    <div style="
background:linear-gradient(90deg,#003B6F,#005A9C);
color:white;
padding:24px;
border-radius:12px;
margin-bottom:25px;
box-shadow:0 4px 12px rgba(0,0,0,.15);">

<h2 style="
margin:0;
color:#ffffff !important;
font-size:30px;
font-weight:700;">
VKollab Executive Dashboard
</h2>

<p style="
margin-top:8px;
font-size:15px;
opacity:.9;">
Organization Learning, Compliance & Performance Analytics
</p>

</div>
   
  <div style="
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:20px;
margin-bottom:25px;">

    <div style="
    background:#FFFFFF;
    padding:20px;
    border-radius:12px;
    min-height:120px;
    text-align:center;
    border-left:5px solid #005A9C;
    box-shadow:0 4px 12px rgba(0,0,0,.08);">

        <h3 style="
        margin:0;
        font-size:14px;
        font-weight:600;
        color:#44546A;">
        Total Employees
        </h3>

        <h1 style="
        margin-top:10px;
        font-size:48px;
        font-weight:700;
        color:#003B6F;">
        '.$employees.'
        </h1>

    </div>

    <div style="
    background:#FFFFFF;
    padding:20px;
    border-radius:12px;
    min-height:120px;
    text-align:center;
    border-left:5px solid #005A9C;
    box-shadow:0 4px 12px rgba(0,0,0,.08);">

        <h3 style="
        margin:0;
        font-size:14px;
        font-weight:600;
        color:#44546A;">
        Total Courses
        </h3>

        <h1 style="
        margin-top:10px;
        font-size:48px;
        font-weight:700;
        color:#003B6F;">
        '.$courses.'
        </h1>

    </div>

    <div style="
    background:#FFFFFF;
    padding:20px;
    border-radius:12px;
    min-height:120px;
    text-align:center;
    border-left:5px solid #0078D4;
    box-shadow:0 4px 12px rgba(0,0,0,.08);">

        <h3 style="
        margin:0;
        font-size:14px;
        font-weight:600;
        color:#44546A;">
        Enrollments
        </h3>

        <h1 style="
        margin-top:10px;
        font-size:48px;
        font-weight:700;
        color:#003B6F;">
        '.$enrollments.'
        </h1>

    </div>

    <div style="
    background:#FFFFFF;
    padding:20px;
    border-radius:12px;
    min-height:120px;
    text-align:center;
    border-left:5px solid #107C10;
    box-shadow:0 4px 12px rgba(0,0,0,.08);">

        <h3 style="
        margin:0;
        font-size:14px;
        font-weight:600;
        color:#44546A;">
        Completed Courses
        </h3>

        <h1 style="
        margin-top:10px;
        font-size:48px;
        font-weight:700;
        color:#003B6F;">
        '.$completedcourses.'
        </h1>

    </div>

    <div style="
background:#FFFFFF;
padding:20px;
border-radius:12px;
min-height:120px;
text-align:center;
border-left:5px solid #107C10;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

    <h3 style="
    margin:0;
    font-size:14px;
    font-weight:600;
    color:#44546A;">
    Active Learners
    </h3>

    <h1 style="
    margin-top:10px;
    font-size:48px;
    font-weight:700;
    color:#003B6F;">
    '.$activelearners.'
    </h1>

</div>

    <div style="
    background:#FFFFFF;
    padding:20px;
    border-radius:12px;
    min-height:120px;
    text-align:center;
    border-left:5px solid #D83B01;
    box-shadow:0 4px 12px rgba(0,0,0,.08);">

        <h3 style="
        margin:0;
        font-size:14px;
        font-weight:600;
        color:#44546A;">
        Completion Rate
        </h3>

        <h1 style="
        margin-top:10px;
        font-size:48px;
        font-weight:700;
        color:#003B6F;">
        '.$completionpercentage.'%
        </h1>

    </div>

</div>';


// ===================================
// PRACTICE PERFORMANCE COMPARISON
// ===================================

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
Practice Performance Comparison
</h3>';

$practices = $DB->get_records_sql("
    SELECT DISTINCT d.data AS practice
    FROM {user_info_data} d
    JOIN {user_info_field} f
        ON f.id = d.fieldid
    WHERE f.shortname = 'practice'
    AND d.data <> ''
");

foreach ($practices as $practiceobj) {

    $practice = $practiceobj->practice;
    $assigned = 0;
    $completed = 0;

    $practiceusers = $DB->get_fieldset_sql("
        SELECT u.id
        FROM {user} u
        JOIN {user_info_data} d
            ON d.userid = u.id
        JOIN {user_info_field} f
            ON f.id = d.fieldid
        WHERE f.shortname = 'practice'
        AND d.data = ?
    ", array($practice));

    foreach ($practiceusers as $userid) {

        $assigned += $DB->count_records(
            'user_enrolments',
            array('userid' => $userid)
        );

        $completed += $DB->count_records_select(
            'course_completions',
            'userid = ? AND timecompleted IS NOT NULL',
            array($userid)
        );
    }

    $completion = 0;

    if ($assigned > 0) {
        $completion = round(
            ($completed / $assigned) * 100
        );
    }

    $width = max(50, $completion * 5);

    $this->content->text .= '

    <div style="margin-bottom:20px;">

        <div style="
        display:flex;
        justify-content:space-between;
        margin-bottom:8px;
        color:#44546A;
        font-weight:600;">

            <span>'.$practice.' Practice</span>
            <span>'.$completion.'%</span>

        </div>

        <div style="
        height:18px;
        width:'.$width.'px;
        background:linear-gradient(
            90deg,
            #005A9C,
            #0078D4
        );
        border-radius:25px;">
        </div>

    </div>';
}

$this->content->text .= '

</div>';
// ===================================
// ORGANIZATION LEARNING TREND
// ===================================

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
Organization Learning Trend
</h3>

<table style="
width:100%;
border-collapse:collapse;">

<tr style="background:#F5F7FA;">
    <th style="padding:10px;">Month</th>
    <th>Completion Rate</th>
</tr>

<tr>
    <td style="padding:10px;">October 2026</td>
    <td>'.$completionpercentage.'%</td>
</tr>

</table>

</div>';
$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
 Top Performing Practices
</h3>

<table style="
width:100%;
border-collapse:collapse;">

<tr style="background:#F5F7FA;">
    <th style="padding:10px;">Rank</th>
    <th>Practice</th>
    <th>Completion %</th>
</tr>';
$practiceperformance = array();

$practices = $DB->get_records_sql("
    SELECT DISTINCT d.data AS practice
    FROM {user_info_data} d
    JOIN {user_info_field} f
        ON f.id = d.fieldid
    WHERE f.shortname = 'practice'
    AND d.data <> ''
");

foreach ($practices as $practiceobj) {

    $practice = $practiceobj->practice;

    $assigned = 0;
    $completed = 0;

    $practiceusers = $DB->get_fieldset_sql("
        SELECT u.id
        FROM {user} u
        JOIN {user_info_data} d
            ON d.userid = u.id
        JOIN {user_info_field} f
            ON f.id = d.fieldid
        WHERE f.shortname = 'practice'
        AND d.data = ?
    ", array($practice));

    foreach ($practiceusers as $userid) {

        $assigned += $DB->count_records(
            'user_enrolments',
            array('userid' => $userid)
        );

        $completed += $DB->count_records_select(
            'course_completions',
            'userid = ? AND timecompleted IS NOT NULL',
            array($userid)
        );
    }

    $completion = 0;

    if ($assigned > 0) {
        $completion = round(
            ($completed / $assigned) * 100
        );
    }

    $practiceperformance[] = array(
        'practice' => $practice,
        'completion' => $completion
    );
}
usort($practiceperformance, function($a, $b) {
    return $b['completion'] <=> $a['completion'];
});

$rank = 1;

foreach ($practiceperformance as $practice) {

    $this->content->text .= '

    <tr>

        <td style="padding:10px;">
            '.$rank.'
        </td>

        <td>
            '.$practice['practice'].' Practice
        </td>

        <td>
            '.$practice['completion'].'%
        </td>

    </tr>';

    $rank++;
}
$this->content->text .= '

</table>

</div>';

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
 Executive Reports
</h3>

<div style="
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:12px;">

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #005A9C;
border-radius:8px;">
 Organization Learning Report
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #107C10;
border-radius:8px;">
 Practice Performance Report
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #0078D4;
border-radius:8px;">
 Assessment Report
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #D83B01;
border-radius:8px;">
 Certificate Report
</div>

</div>

</div>';
// ===================================
// PRACTICE-WISE ANALYTICS
// ===================================

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
 Practice-wise Analytics
</h3>

<table style="
width:100%;
border-collapse:collapse;">

<tr style="background:#F5F7FA;">
    <th style="padding:10px;">Practice</th>
    <th>Learners</th>
    <th>Completion %</th>
</tr>';
$practices = $DB->get_records_sql("
    SELECT DISTINCT d.data AS practice
    FROM {user_info_data} d
    JOIN {user_info_field} f
        ON f.id = d.fieldid
    WHERE f.shortname = 'practice'
    AND d.data <> ''
");

foreach ($practices as $practiceobj) {

    $practice = $practiceobj->practice;

    $learners = $DB->count_records_sql("
        SELECT COUNT(*)
        FROM {user_info_data} d
        JOIN {user_info_field} f
            ON f.id = d.fieldid
        WHERE f.shortname = 'practice'
        AND d.data = ?
    ", array($practice));

    $completed = 0;
    $assigned = 0;

    $practiceusers = $DB->get_fieldset_sql("
        SELECT u.id
        FROM {user} u
        JOIN {user_info_data} d
            ON d.userid = u.id
        JOIN {user_info_field} f
            ON f.id = d.fieldid
        WHERE f.shortname = 'practice'
        AND d.data = ?
    ", array($practice));

    foreach ($practiceusers as $userid) {

        $assigned += $DB->count_records(
            'user_enrolments',
            array('userid' => $userid)
        );

        $completed += $DB->count_records_select(
            'course_completions',
            'userid = ? AND timecompleted IS NOT NULL',
            array($userid)
        );
    }

    $completion = 0;

    if ($assigned > 0) {
        $completion =
            round(($completed / $assigned) * 100);
    }

    $this->content->text .= '

    <tr>

        <td style="padding:10px;">
            '.$practice.' Practice
        </td>

        <td>'.$learners.'</td>

        <td>'.$completion.'%</td>

    </tr>';
}
$this->content->text .= '

</table>

</div>';
// ===================================
// CERTIFICATION ANALYTICS
// ===================================

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
 Certification Analytics
</h3>

<table style="
width:100%;
border-collapse:collapse;">

<tr style="background:#F5F7FA;">
    <th style="padding:10px;">Practice</th>
    <th>Certificates Earned</th>
</tr>';
$practices = $DB->get_records_sql("
    SELECT DISTINCT d.data AS practice
    FROM {user_info_data} d
    JOIN {user_info_field} f
        ON f.id = d.fieldid
    WHERE f.shortname = 'practice'
    AND d.data <> ''
");

foreach ($practices as $practiceobj) {

    $practice = $practiceobj->practice;

    $certificatecount = 0;

    $practiceusers = $DB->get_fieldset_sql("
        SELECT u.id
        FROM {user} u
        JOIN {user_info_data} d
            ON d.userid = u.id
        JOIN {user_info_field} f
            ON f.id = d.fieldid
        WHERE f.shortname = 'practice'
        AND d.data = ?
    ", array($practice));

    foreach ($practiceusers as $userid) {

        $certificatecount += $DB->count_records_select(
            'course_completions',
            'userid = ? AND timecompleted IS NOT NULL',
            array($userid)
        );
    }

    $this->content->text .= '

    <tr>

        <td style="padding:10px;">
            '.$practice.' Practice
        </td>

        <td>'.$certificatecount.'</td>

    </tr>';
}
$this->content->text .= '

</table>

</div>';
$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#C50F1F;
margin-bottom:15px;">
 Low Performing Practices
</h3>

<table style="
width:100%;
border-collapse:collapse;">

<tr style="background:#F5F7FA;">
    <th style="padding:10px;">Practice</th>
    <th>Learners</th>
    <th>Completion %</th>
</tr>';
$practices = $DB->get_records_sql("
    SELECT DISTINCT d.data AS practice
    FROM {user_info_data} d
    JOIN {user_info_field} f
        ON f.id = d.fieldid
    WHERE f.shortname = 'practice'
    AND d.data <> ''
");

$practiceperformance = array();

foreach ($practices as $practiceobj) {

    $practice = $practiceobj->practice;

    $learners = 0;
    $assigned = 0;
    $completed = 0;

    $practiceusers = $DB->get_fieldset_sql("
        SELECT u.id
        FROM {user} u
        JOIN {user_info_data} d
            ON d.userid = u.id
        JOIN {user_info_field} f
            ON f.id = d.fieldid
        WHERE f.shortname = 'practice'
        AND d.data = ?
    ", array($practice));

    $learners = count($practiceusers);

    foreach ($practiceusers as $userid) {

        $assigned += $DB->count_records(
            'user_enrolments',
            array('userid' => $userid)
        );

        $completed += $DB->count_records_select(
            'course_completions',
            'userid = ? AND timecompleted IS NOT NULL',
            array($userid)
        );
    }

    $completion = 0;

    if ($assigned > 0) {
        $completion = round(
            ($completed / $assigned) * 100
        );
    }

    $practiceperformance[] = array(
        'practice' => $practice,
        'learners' => $learners,
        'completion' => $completion
    );
}
usort($practiceperformance, function($a, $b) {
    return $a['completion'] <=> $b['completion'];
});
foreach ($practiceperformance as $practice) {

    $this->content->text .= '

    <tr>

        <td style="padding:10px;">
            '.$practice['practice'].' Practice
        </td>

        <td>'.$practice['learners'].'</td>

        <td>'.$practice['completion'].'%</td>

    </tr>';
}
$this->content->text .= '

</table>

</div>';
// ===================================
// ORGANIZATION ASSESSMENT ANALYTICS
// ===================================

$totalattempts = 0;
$totalscore = 0;
$highestscore = 0;
$lowestscore = null;
$passcount = 0;

$sql = "
    SELECT
        qa.sumgrades,
        q.grade AS maxgrade
    FROM {quiz_attempts} qa
    JOIN {quiz} q
        ON q.id = qa.quiz
    WHERE qa.state = 'finished'
";

$attempts = $DB->get_records_sql($sql);

foreach ($attempts as $attempt) {

    if ($attempt->maxgrade > 0) {

        $score = round(
            ($attempt->sumgrades /
            $attempt->maxgrade) * 100
        );

        $totalscore += $score;
        $totalattempts++;

        if ($score > $highestscore) {
            $highestscore = $score;
        }

        if ($lowestscore === null ||
            $score < $lowestscore) {
            $lowestscore = $score;
        }

        if ($score >= 50) {
            $passcount++;
        }
    }
}

if ($totalattempts > 0) {

    $averagescore =
        round($totalscore / $totalattempts);

    $passrate =
        round(($passcount / $totalattempts) * 100);

    $failrate = 100 - $passrate;

} else {

    $averagescore = 0;
    $highestscore = 0;
    $lowestscore = 0;
    $passrate = 0;
    $failrate = 0;
}

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
 Assessment Analytics
</h3>

<div style="
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:12px;">

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #005A9C;
border-radius:8px;">
Average Score : '.$averagescore.'%
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #107C10;
border-radius:8px;">
Highest Score : '.$highestscore.'%
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #D83B01;
border-radius:8px;">
Lowest Score : '.$lowestscore.'%
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #0078D4;
border-radius:8px;">
Pass Rate : '.$passrate.'%
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #C50F1F;
border-radius:8px;">
Fail Rate : '.$failrate.'%
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #6A1B9A;
border-radius:8px;">
Assessments Completed : '.$totalattempts.'
</div>

</div>

</div>';

// ===================================
// ORGANIZATION OVERDUE TRAININGS
// ===================================

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#C50F1F;
margin-bottom:15px;">
 Organization Overdue Trainings
</h3>

<table style="
width:100%;
border-collapse:collapse;">

<tr style="background:#F5F7FA;">
    <th style="padding:10px;">Employee</th>
    <th>Practice</th>
    <th>Course</th>
    <th>Due Date</th>
    <th>Days Overdue</th>
</tr>';
$allusers = $DB->get_records_sql("
    SELECT DISTINCT u.id,
           u.firstname
    FROM {user} u
    JOIN {role_assignments} ra
        ON ra.userid = u.id
    JOIN {role} r
        ON r.id = ra.roleid
    WHERE r.shortname = 'student'
");

foreach ($allusers as $user) {

    $courses = $DB->get_records_sql("
        SELECT
            c.id,
            c.fullname,
            c.enddate
        FROM {course} c
        JOIN {enrol} e
            ON e.courseid = c.id
        JOIN {user_enrolments} ue
            ON ue.enrolid = e.id
        WHERE ue.userid = ?
        AND c.id > 1
    ", array($user->id));

    foreach ($courses as $course) {

        $completed = $DB->record_exists(
            'course_completions',
            array(
                'userid' => $user->id,
                'course' => $course->id
            )
        );

        if (
            !$completed &&
            $course->enddate > 0 &&
            $course->enddate < time()
        ) {

            $daysoverdue =
                floor((time() - $course->enddate) / 86400);

            $practice = $DB->get_field_sql("
                SELECT d.data
                FROM {user_info_data} d
                JOIN {user_info_field} f
                    ON f.id = d.fieldid
                WHERE d.userid = ?
                AND f.shortname = 'practice'
            ", array($user->id));

            $this->content->text .= '

            <tr>

                <td style="padding:10px;">
                    '.$user->firstname.'
                </td>

                <td>
                    '.$practice.'
                </td>

                <td>
                    '.$course->fullname.'
                </td>

                <td>
                    '.date("d-M-Y", $course->enddate).'
                </td>

                <td style="
                    color:#C50F1F;
                    font-weight:600;">
                    '.$daysoverdue.' Days
                </td>

            </tr>';
        }
    }
}
    $this->content->text .= '

</table>

</div>';
    
    return $this->content;
}
  // ===================================
        // organization system admin DASHBOARD
        // ===================================
      if ($iscontentadmin) {

    $totalcourses = $DB->count_records_select(
        'course',
        'id > 1'
    );

    $totalcourses = $DB->count_records_select(
    'course',
    'id > 1'
);

$draftcourses = $DB->count_records_sql("
    SELECT COUNT(*)
    FROM {course} c
    LEFT JOIN {customfield_data} cd
        ON cd.instanceid = c.id
    LEFT JOIN {customfield_field} cf
        ON cf.id = cd.fieldid
    WHERE c.id > 1
    AND (
        cd.value IS NULL
        OR cd.value = ''
    )
");

$pendingapproval = $DB->count_records_sql("
    SELECT COUNT(*)
    FROM {customfield_data} cd
    JOIN {customfield_field} cf
        ON cf.id = cd.fieldid
    WHERE cf.shortname = 'coursestatus'
    AND cd.value = '2'
");

$publishedcourses = $DB->count_records_sql("
    SELECT COUNT(*)
    FROM {customfield_data} cd
    JOIN {customfield_field} cf
        ON cf.id = cd.fieldid
    WHERE cf.shortname = 'coursestatus'
    AND cd.value = '3'
");

$archivedcourses = $DB->count_records_sql("
    SELECT COUNT(*)
    FROM {customfield_data} cd
    JOIN {customfield_field} cf
        ON cf.id = cd.fieldid
    WHERE cf.shortname = 'coursestatus'
    AND cd.value = '4'
");

$debugstatus = '';

foreach ($testcourses as $course) {
   
}
$totalassessments = $DB->count_records('quiz');


    $this->content->text = '
    

    <div style="
    background:linear-gradient(
    90deg,
    #003B6F,
    #005A9C
    );
    color:white;
    padding:24px;
    border-radius:12px;
    margin-bottom:20px;
    box-shadow:0 4px 12px rgba(0,0,0,.15);">

    <h2 style="margin:0;">
    VKollab Content Administration
    </h2>
    

    <p style="
    margin-top:8px;
    opacity:.9;">
    Course Lifecycle, Content Governance & Learning Assets
    </p>

    </div>

    <div style="
    display:grid;
    grid-template-columns:
    repeat(auto-fit,minmax(220px,1fr));
    gap:20px;">

        <div style="
        background:#FFFFFF;
        padding:20px;
        border-radius:12px;
        border-left:5px solid #005A9C;
        box-shadow:0 4px 12px rgba(0,0,0,.08);">
            <h3>Total Courses</h3>
            <h1>'.$totalcourses.'</h1>
        </div>

        <div style="
        background:#FFFFFF;
        padding:20px;
        border-radius:12px;
        border-left:5px solid #D83B01;
        box-shadow:0 4px 12px rgba(0,0,0,.08);">
            <h3>Draft Courses</h3>
            <h1>'.$draftcourses.'</h1>
        </div>

        <div style="
background:#FFFFFF;
padding:20px;
border-radius:12px;
border-left:5px solid #FF9800;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

    <h3>Pending Approval</h3>

    <h1>'.$pendingapproval.'</h1>

</div>
<div style="
background:#FFFFFF;
padding:20px;
border-radius:12px;
border-left:5px solid #6A1B9A;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

    <h3>Archived Courses</h3>

    <h1>'.$archivedcourses.'</h1>

</div>

        <div style="
        background:#FFFFFF;
        padding:20px;
        border-radius:12px;
        border-left:5px solid #107C10;
        box-shadow:0 4px 12px rgba(0,0,0,.08);">
            <h3>Published Courses</h3>
            <h1>'.$publishedcourses.'</h1>
        </div>

        <div style="
        background:#FFFFFF;
        padding:20px;
        border-radius:12px;
        border-left:5px solid #0078D4;
        box-shadow:0 4px 12px rgba(0,0,0,.08);">
            <h3>Assessments</h3>
            <h1>'.$totalassessments.'</h1>
        </div>

    </div>';
    $this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
Course Repository
</h3>

<table style="
width:100%;
border-collapse:collapse;">

<tr style="background:#F5F7FA;">
    <th style="padding:10px;">Course Name</th>
    <th>Status</th>
</tr>';
$courses = $DB->get_records_sql("
    SELECT
        c.id,
        c.fullname,
        cd.value
    FROM {course} c
    LEFT JOIN {customfield_data} cd
        ON cd.instanceid = c.id
    LEFT JOIN {customfield_field} cf
        ON cf.id = cd.fieldid
        AND cf.shortname = 'coursestatus'
    WHERE c.id > 1
");
foreach ($courses as $course) {

    $status = 'Draft';

    if ($course->value == '1') {
        $status = 'Draft';
    } else if ($course->value == '2') {
        $status = 'Pending Approval';
    } else if ($course->value == '3') {
        $status = 'Published';
    } else if ($course->value == '4') {
        $status = 'Archived';
    }

    $this->content->text .= '

    <tr>

        <td style="padding:10px;">
            '.$course->fullname.'
        </td>

        <td>'.$status.'</td>

    </tr>';
}
$this->content->text .= '

</table>

</div>';
$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
 Content By Category
</h3>

<table style="
width:100%;
border-collapse:collapse;">

<tr style="background:#F5F7FA;">
    <th style="padding:10px;">Practice</th>
    <th>Total Courses</th>
</tr>';
$categories = $DB->get_records_sql("
    SELECT id, name
    FROM {course_categories}
");

foreach ($categories as $category) {

    $coursecount = $DB->count_records(
        'course',
        array('category' => $category->id)
    );

    if ($coursecount > 0) {

        $this->content->text .= '

        <tr>

            <td style="padding:10px;">
                '.$category->name.'
            </td>

            <td>'.$coursecount.'</td>

        </tr>';
    }
}
$this->content->text .= '

</table>

</div>';
$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
Approval Queue
</h3>

<table style="
width:100%;
border-collapse:collapse;">

<tr style="background:#F5F7FA;">
    <th style="padding:10px;">Course Name</th>
    <th>Status</th>
</tr>';
$pendingcourses = $DB->get_records_sql("
    SELECT
        c.fullname,
        cd.value
    FROM {course} c
    JOIN {customfield_data} cd
        ON cd.instanceid = c.id
    JOIN {customfield_field} cf
        ON cf.id = cd.fieldid
    WHERE cf.shortname = 'coursestatus'
    AND cd.value = '2'
");
foreach ($pendingcourses as $course) {

    $this->content->text .= '

    <tr>

        <td style="padding:10px;">
            '.$course->fullname.'
        </td>

        <td>
             Pending Approval
        </td>

    </tr>';
}
$this->content->text .= '

</table>

</div>';
$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
Content Effectiveness Analytics
</h3>

<table style="
width:100%;
border-collapse:collapse;">

<tr style="background:#F5F7FA;">
    <th style="padding:10px;">Course</th>
    <th>Enrolled</th>
    <th>Completed</th>
    <th>Completion %</th>
</tr>';
$courses = $DB->get_records_sql("
    SELECT id, fullname
    FROM {course}
    WHERE id > 1
");

foreach ($courses as $course) {

    $enrolled = $DB->count_records_sql("
        SELECT COUNT(*)
        FROM {user_enrolments} ue
        JOIN {enrol} e
            ON e.id = ue.enrolid
        WHERE e.courseid = ?
    ", array($course->id));

    $completed = $DB->count_records_select(
        'course_completions',
        'course = ? AND timecompleted IS NOT NULL',
        array($course->id)
    );

    $completionrate = 0;

    if ($enrolled > 0) {
        $completionrate = round(
            ($completed / $enrolled) * 100
        );
    }

    $this->content->text .= '

    <tr>

        <td style="padding:10px;">
            '.$course->fullname.'
        </td>

        <td>'.$enrolled.'</td>

        <td>'.$completed.'</td>

        <td>'.$completionrate.'%</td>

    </tr>';
}
$this->content->text .= '

</table>

</div>';
    return $this->content;
}
        // ===================================
        // LEARNER DASHBOARD
        // ===================================
        if (!$isadmin) {

            $mycourses = $DB->count_records_sql("
                SELECT COUNT(DISTINCT c.id)
                FROM {course} c
                JOIN {enrol} e ON e.courseid = c.id
                JOIN {user_enrolments} ue ON ue.enrolid = e.id
                WHERE ue.userid = ?
            ", array($USER->id));

            $mycompleted = $DB->count_records_sql("
                SELECT COUNT(*)
                FROM {course_completions}
                WHERE userid = ?
                AND timecompleted IS NOT NULL
            ", array($USER->id));

            $mypending = max(0, $mycourses - $mycompleted);

            $completionpercentage = 0;

            if ($mycourses > 0) {
                $completionpercentage =
                    round(($mycompleted / $mycourses) * 100);
            }

            $this->content->text = '

            <div style="
background:linear-gradient(90deg,#003B6F,#005A9C);
color:white;
padding:20px;
border-radius:12px;
margin-bottom:20px;
box-shadow:0 4px 10px rgba(0,0,0,.12);">

<h2 style="margin:0; color:#ffffff !important;">
Welcome Back, '.$USER->firstname.'
</h2>

<p style="
margin-top:8px;
opacity:.9;
font-size:15px;">
Continue your learning journey.
Track course progress, certifications and achievements.
</p>

</div>

            <div style="
                display:flex;
                flex-wrap:wrap;
                gap:15px;">

                <div style="
                    background:#FFFFFF;
border-top:4px solid #005A9C;
padding:20px;
border-radius:12px;
width:220px;
text-align:center;
box-shadow:0 4px 10px rgba(0,0,0,.08);
                    <h3 style="
margin:0;
color:#44546A;
font-size:14px;">
My Courses
</h3>
                    <h1 style="
font-size:42px;
margin-top:10px;
color:#003B6F;">
'.$mycourses.'
</h1>
                </div>

                <div style="
                   background:#FFFFFF;
border-top:4px solid #005A9C;
padding:20px;
border-radius:12px;
width:220px;
text-align:center;
box-shadow:0 4px 10px rgba(0,0,0,.08);
                    <h3 style="
margin:0;
color:#44546A;
font-size:14px;">
Completed
</h3>
                    <h1 style="
font-size:42px;
margin-top:10px;
color:#003B6F;">
'.$mycompleted.'
</h1>
                </div>

                <div style="
                   background:#FFFFFF;
border-top:4px solid #005A9C;
padding:20px;
border-radius:12px;
width:220px;
text-align:center;
box-shadow:0 4px 10px rgba(0,0,0,.08);
                    <h3 style="
margin:0;
color:#44546A;
font-size:14px;">
Pending
</h3>
                   <h1 style="
font-size:42px;
margin-top:10px;
color:#003B6F;">
'.$mypending.'
</h1>
                </div>

                <div style="
                   background:#FFFFFF;
border-top:4px solid #005A9C;
padding:20px;
border-radius:12px;
width:220px;
text-align:center;
box-shadow:0 4px 10px rgba(0,0,0,.08);
                    <h3 style="
margin:0;
color:#44546A;
font-size:14px;">
Completion Rate
</h3>
                  <h1 style="
font-size:42px;
margin-top:10px;
color:#003B6F;">
'.$completionpercentage.'%
</h1>
                </div>

           </div>';

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
box-shadow:0 4px 10px rgba(0,0,0,.08);
margin-top:20px;">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
Learning Progress
</h3>

<div style="
background:#E5E7EB;
height:18px;
border-radius:20px;">

<div style="
width:'.$completionpercentage.'%;
height:18px;
background:linear-gradient(
90deg,
#005A9C,
#0078D4
);
border-radius:20px;">
</div>

</div>

<p style="
margin-top:10px;
font-weight:600;
color:#44546A;">
'.$completionpercentage.'% Complete
</p>

</div>';
$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;">

<h3 style="
color:#003B6F;">
Learning Summary
</h3>

<ul style="
color:#44546A;
line-height:2;">
<li>Courses Assigned: '.$mycourses.'</li>
<li>Courses Completed: '.$mycompleted.'</li>
<li>Courses Pending: '.$mypending.'</li>
<li>Completion Rate: '.$completionpercentage.'%</li>
</ul>

</div>';
$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 10px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
My Certificates
</h3>';
if ($mycompleted > 0) {

    $this->content->text .= '

    <div style="
    background:#F8FAFC;
    padding:15px;
    border-left:4px solid #107C10;
    border-radius:8px;
    margin-bottom:10px;">

    ✅ '.$mycompleted.' Certificate(s) Earned

    <br><br>

    Completed courses are eligible for certification.

    </div>';

} else {

    $this->content->text .= '

    <div style="
    background:#F8FAFC;
    padding:15px;
    border-left:4px solid #D83B01;
    border-radius:8px;">

    ⏳ No certificates earned yet.

    </div>';
}
$this->content->text .= '

</div>';
$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
 My Courses
</h3>

<table style="
width:100%;
border-collapse:collapse;">

<tr style="background:#F5F7FA;">
    <th style="padding:10px;">Course</th>
    <th>Status</th>
    <th>Progress</th>
</tr>';
$mycourseslist = $DB->get_records_sql("
    SELECT c.id,
           c.fullname
    FROM {course} c
    JOIN {enrol} e
        ON e.courseid = c.id
    JOIN {user_enrolments} ue
        ON ue.enrolid = e.id
    WHERE ue.userid = ?
    AND c.id > 1
", array($USER->id));
foreach ($mycourseslist as $course) {

    $completed = $DB->record_exists(
        'course_completions',
        array(
            'userid' => $USER->id,
            'course' => $course->id
        )
    );

    if ($completed) {

        $status = '✅ Completed';
        $progress = '100%';

    } else {

        $status = '⏳ In Progress';
        $progress = '0%';
    }

    $this->content->text .= '

    <tr>

        <td style="padding:10px;">
            '.$course->fullname.'
        </td>

        <td>'.$status.'</td>

        <td>'.$progress.'</td>

    </tr>';
}
$this->content->text .= '

</table>

</div>';

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
Upcoming & Overdue Training
</h3>

<table style="
width:100%;
border-collapse:collapse;">

<tr style="background:#F5F7FA;">
    <th style="padding:10px;">Course</th>
    <th>Due Date</th>
    <th>Status</th>
</tr>';
foreach ($mycourseslist as $course) {

    $duedate = '';

    if (!empty($course->enddate)) {

        $duedate =
            date('d-M-Y', $course->enddate);

        if ($course->enddate < time()) {

            $status =
                '<span style="color:#D83B01;font-weight:600;">
                Overdue
                </span>';

        } else {

            $remaining =
                ceil(
                    ($course->enddate - time())
                    / 86400
                );

            $status =
                $remaining.' Days Remaining';
        }

    } else {

        $duedate = 'Not Set';
        $status = '-';
    }

    $this->content->text .= '

    <tr>

        <td style="padding:10px;">
            '.$course->fullname.'
        </td>

        <td>'.$duedate.'</td>

        <td>'.$status.'</td>

    </tr>';
}
$this->content->text .= '

</table>

</div>';
// ===================================
// LEARNER ASSESSMENT ANALYTICS
// ===================================

// ===================================
// LEARNER ASSESSMENT ANALYTICS
// ===================================

$highestscore = 0;
$averagescore = 0;
$passrate = 0;
$failedassessments = 0;

$attempts = $DB->get_records_sql("
    SELECT
        qa.sumgrades,
        q.grade AS maxgrade
    FROM {quiz_attempts} qa
    JOIN {quiz} q
        ON q.id = qa.quiz
    WHERE qa.userid = ?
    AND qa.state = 'finished'
", array($USER->id));
foreach ($attempts as $attempt) {

   
}

$totalattempts = count($attempts);

if ($totalattempts > 0) {

    $totalscore = 0;

    foreach ($attempts as $attempt) {

        // Quiz in your LMS is currently out of 10.
    $score = round(
    ($attempt->sumgrades * 20)
);
        $totalscore += $score;

        if ($score > $highestscore) {
            $highestscore = $score;
        }
    }

    $averagescore = round($totalscore / $totalattempts);

    $passrate = 100;
    $failedassessments = 0;

} else {

    $highestscore = 0;
    $averagescore = 0;
    $passrate = 0;
    $failedassessments = 0;
}

$this->content->text .= '

<div style="
background:#ffffff;
padding:20px;
border-radius:12px;
border:1px solid #D9E2EC;
margin-top:20px;
box-shadow:0 4px 12px rgba(0,0,0,.08);">

<h3 style="
color:#003B6F;
margin-bottom:15px;">
 Assessment Analytics
</h3>

<div style="
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:12px;">

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #005A9C;
border-radius:8px;">
Highest Score : '.$highestscore.'%
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #107C10;
border-radius:8px;">
Average Score : '.$averagescore.'%
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #0078D4;
border-radius:8px;">
Pass Rate : '.$passrate.'%
</div>

<div style="
background:#F8FAFC;
padding:15px;
border-left:4px solid #D83B01;
border-radius:8px;">
Failed Assessments : '.$failedassessments.'
</div>

</div>

</div>';

            return $this->content;
        }

        // ===================================
        // ADMIN / CEO DASHBOARD
        // ===================================

        $employees = $DB->count_records('user',
            array('deleted' => 0, 'suspended' => 0));

        $courses = $DB->count_records_select(
            'course',
            'id > 1'
        );

        $enrollments = $DB->count_records(
            'user_enrolments'
        );

        $activeusers = $DB->count_records('user',
            array('deleted' => 0, 'suspended' => 0));

        $completedcourses = $DB->count_records_select(
            'course_completions',
            'timecompleted IS NOT NULL'
        );

        $pendingcourses = max(
            0,
            $enrollments - $completedcourses
        );

        $sql = "
            SELECT
                c.fullname,
                COUNT(ue.userid) AS enrollments
            FROM {course} c
            JOIN {enrol} e ON e.courseid = c.id
            JOIN {user_enrolments} ue ON ue.enrolid = e.id
            WHERE c.id > 1
            GROUP BY c.id, c.fullname
            ORDER BY enrollments DESC
        ";

        $courseenrollments = $DB->get_records_sql($sql);

        $this->content->text = '

       <div style="
background:linear-gradient(90deg,#003B6F,#005A9C);
color:white;
padding:24px;
border-radius:12px;
margin-bottom:20px;
box-shadow:0 4px 12px rgba(0,0,0,.15);">

    <h2 style="
margin:0;
font-size:32px;
font-weight:700;
color:#ffffff !important;">
VKollab LMS Administration
</h2>

<p style="
margin-top:10px;
font-size:15px;
opacity:.9;">
Learning Analytics • Compliance Tracking • System Monitoring
</p>

</div>

<div style="
display:flex;
flex-wrap:wrap;
gap:20px;
margin-bottom:25px;">

    <div style="
    background:#F8FAFC;
    padding:20px;
    border-radius:12px;
    width:180px;
    text-align:center;
    border-top:4px solid #005A9C;
    box-shadow:0 4px 12px rgba(0,0,0,.08);">

        <h3 style="
        margin:0;
        font-size:14px;
        color:#44546A;">
        Total Employees
        </h3>

        <h1 style="
        margin-top:10px;
        font-size:42px;
        color:#003B6F;">
        '.$employees.'
        </h1>

    </div>

    <div style="
    background:#ffffff;
    padding:20px;
    border-radius:12px;
    width:180px;
    text-align:center;
    border-top:4px solid #005A9C;
    box-shadow:0 4px 12px rgba(0,0,0,.08);">

        <h3 style="
        margin:0;
        font-size:14px;
        color:#44546A;">
        Total Courses
        </h3>

        <h1 style="
        margin-top:10px;
        font-size:42px;
        color:#003B6F;">
        '.$courses.'
        </h1>

    </div>

    <div style="
    background:#ffffff;
    padding:20px;
    border-radius:12px;
    width:180px;
    text-align:center;
    border-top:4px solid #0078D4;
    box-shadow:0 4px 12px rgba(0,0,0,.08);">

        <h3 style="
        margin:0;
        font-size:14px;
        color:#44546A;">
        Enrollments
        </h3>

        <h1 style="
        margin-top:10px;
        font-size:42px;
        color:#003B6F;">
        '.$enrollments.'
        </h1>

    </div>

    <div style="
    background:#ffffff;
    padding:20px;
    border-radius:12px;
    width:180px;
    text-align:center;
    border-top:4px solid #107C10;
    box-shadow:0 4px 12px rgba(0,0,0,.08);">

        <h3 style="
        margin:0;
        font-size:14px;
        color:#44546A;">
        Active Users
        </h3>

        <h1 style="
        margin-top:10px;
        font-size:42px;
        color:#003B6F;">
        '.$activeusers.'
        </h1>

    </div>

    <div style="
    background:#ffffff;
    padding:20px;
    border-radius:12px;
    width:180px;
    text-align:center;
    border-top:4px solid #D83B01;
    box-shadow:0 4px 12px rgba(0,0,0,.08);">

        <h3 style="
        margin:0;
        font-size:14px;
        color:#44546A;">
        Completed Courses
        </h3>

        <h1 style="
        margin-top:10px;
        font-size:42px;
        color:#003B6F;">
        '.$completedcourses.'
        </h1>

    </div>

</div>';
$this->content->text .= '

<div style="
background:#ffffff;
padding:25px;
border-radius:15px;
border:1px solid #D9E2EC;
box-shadow:0 4px 12px rgba(0,0,0,.08);
margin-top:25px;">

<h3 style="
margin:0 0 20px 0;
color:#003B6F;
font-size:22px;
font-weight:600;">
 Course Enrollment Analytics
</h3>';
  

       
        foreach ($courseenrollments as $course) {

    $width = max(200, ($course->enrollments * 250));

    $this->content->text .= '

    <div style="margin-bottom:20px;">

        <div style="
            display:flex;
            justify-content:space-between;
            margin-bottom:8px;
            color:#44546A;
            font-weight:600;">
            <span>'.$course->fullname.'</span>
            <span>'.$course->enrollments.'</span>
        </div>

        <div style="
            height:18px;
            width:'.$width.'px;
            background:linear-gradient(
                90deg,
                #005A9C,
                #0078D4
            );
            border-radius:25px;
            box-shadow:0 2px 4px rgba(0,0,0,.12);">
        </div>

    </div>';
}
  $this->content->text .= '

<hr style="
margin:25px 0;
border:none;
border-top:1px solid #E5E7EB;">

<h3 style="
color:#003B6F;
font-size:22px;
font-weight:600;">
Completion Status
</h3>

<p style="
font-weight:600;
color:#44546A;">
Completed Courses ('.$completedcourses.')
</p>

<div style="
height:20px;
width:'.max(100, $completedcourses * 100).'px;
background:linear-gradient(
90deg,
#107C10,
#16A34A
);
border-radius:25px;
margin-bottom:20px;">
</div>

<p style="
font-weight:600;
color:#44546A;">
Pending Courses ('.$pendingcourses.')
</p>

<div style="
height:20px;
width:'.max(100, $pendingcourses * 100).'px;
background:linear-gradient(
90deg,
#C50F1F,
#E74856
);
border-radius:25px;
margin-bottom:10px;">
</div>

</div>';

return $this->content;
    }
}
    