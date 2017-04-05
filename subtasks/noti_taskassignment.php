<?php

$num = isset($_GET["num"]) ? $_GET["num"] : $_GET["id"];

$tasks = new \phpCollab\Tasks\Tasks();

$subtaskNoti = $tasks->getSubTaskByIdIn($num);

$taskNoti = $tasks->getTaskById($subtaskNoti["subtas_task"]);

$tmpquery = "WHERE pro.id = '" . $taskNoti["tas_project"] . "'";
$projectNoti = new phpCollab\Request();
$projectNoti->openProjects($tmpquery);

$tmpquery = "WHERE noti.member IN($at)";
$listNotifications = new phpCollab\Request();
$listNotifications->openNotifications($tmpquery);
$comptListNotifications = count($listNotifications->not_id);

if ($listNotifications->not_taskassignment[0] == "0") {
    $mail = new phpCollab\Notification();

    $mail->getUserinfo($idSession, "from");

    $mail->partSubject = $strings["noti_taskassignment1"];
    $mail->partMessage = $strings["noti_taskassignment2"];

    if ($projectNoti->pro_org_id[0] == "1") {
        $projectNoti->pro_org_name[0] = $strings["none"];
    }

    $complValue = ($subtaskNoti["subtas_completion"] > 0) ? $subtaskNoti["subtas_completion"] . "0 %" : $subtaskNoti["subtas_completion"] . " %";
    $idStatus = $subtaskNoti["subtas_status"];
    $idPriority = $subtaskNoti["subtas_priority"];

    $body = $mail->partMessage . "\n\n" . $strings["subtask"] . " : " . $subtaskNoti["subtas_name"] . "\n" . $strings["start_date"] . " : " . $subtaskNoti["subtas_start_date"] . "\n" . $strings["due_date"] . " : " . $subtaskNoti["subtas_due_date"] . "\n" . $strings["completion"] . " : " . $complValue . "\n" . $strings["priority"] . " : $priority[$idPriority]\n" . $strings["status"] . " : $status[$idStatus]\n" . $strings["description"] . " : " . $subtaskNoti["subtas_description"] . "\n\n" . $strings["project"] . " : " . $projectNoti->pro_name[0] . " (" . $projectNoti->pro_id[0] . ")\n" . $strings["task"] . " : " . $taskNoti["tas_name"] . " (" . $taskNoti["tas_id"] . ")\n" . $strings["organization"] . " : " . $projectNoti->pro_org_name[0] . "\n\n" . $strings["noti_moreinfo"] . "\n";

    if ($subtaskNoti["subtas_mem_organization"] == "1") {
        $body .= "$root/general/login.php?url=subtasks/viewsubtask.php%3Fid=$num%26task=" . $taskNoti["tas_id"];
    } else if ($subtaskNoti["subtas_mem_organization"] != "1" && $projectNoti->pro_published[0] == "0" && $subtaskNoti["subtas_published"] == "0") {
        $body .= "$root/general/login.php?url=projects_site/home.php%3Fproject=" . $projectNoti->pro_id[0];
    }

    $body .= "\n\n" . $mail->footer;
    $subject = $mail->partSubject . " " . $subtaskNoti["subtas_name"];
    $mail->Subject = $subject;

    if ($subtaskNoti["subtas_priority"] == "4" || $subtaskNoti["subtas_priority"] == "5") {
        $mail->Priority = "1";
    } else {
        $mail->Priority = "3";
    }

    $mail->Body = $body;
    $mail->AddAddress($listNotifications->not_mem_email_work[0], $listNotifications->not_mem_name[0]);
    $mail->Send();
    $mail->ClearAddresses();
}
