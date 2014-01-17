<?php

echo "<div class='dashboard-item'>";
echo $view->header()->setAttribute('template',$T('backup_title'));
if (!$view['backup_status']['vfs']) {
    echo "<p>".$T('backup_not_configured')."</p>";
} else {
    echo "<dl>";
    echo "<dt>".$T('status_label')."</dt><dd>"; echo $T($view['backup_status']); echo "</dd>";
    echo "<dt>".$T('time_label')."</dt><dd>"; echo $T($view['backup_time']); echo "</dd>";
    echo "<dt>".$T('vfs_label')."</dt><dd>"; echo strtoupper($T($view['backup_vfs'])); echo "</dd>";
    echo "<dt>".$T('type_label')."</dt><dd>"; echo $T($view['backup_type']); echo "</dd>";
    echo "<h3 class='backup'>".$T('last_backup')."</h3>"; 
    echo "<dt>".$T('result_label')."</dt><dd class='backup-".strtolower($view['backup_result'])."'>"; echo $T($view['backup_result']); echo "</dd>";
    echo "<dt>".$T('start_label')."</dt><dd>"; echo date("Y-m-d H:i",$view['backup_start']); echo "</dd>";
    echo "<dt>".$T('end_label')."</dt><dd>"; echo date("Y-m-d H:i",$view['backup_end']); echo "</dd>";
    echo "</dl>";
}
echo "</div>";

$view->includeCss("
    .dashboard-item h3.backup {
        text-align: center;
        font-weight: bold;
        font-size: 1.2em;
        margin: 5px;
    }
    .dashboard-item .backup-success {
        padding: 3px;
        color: green;
        font-weight: bold;
    }
    .dashboard-item .backup-error {
        padding: 3px;
        color: red;
        font-weight: bold;
    }

");
