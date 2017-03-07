<?php

function formatSize($size)
{
    $units = array(' B', ' KB', ' MB', ' GB', ' TB', ' PB', ' EB', ' ZB', ' YB');
    $u = count($units)-1;
    for ($i = 0; ($size >= 1024 && $i < $u); $i ++ ) {
        $size /= 1024;
    }
    return round($size, 2) . $units[$i];
}


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
    if ( strlen($view['backup_size']) && strlen($view['backup_used']) && strlen($view['backup_avail']) ) {
        echo "<dt>".$T('usage_label')."</dt><dd>".formatSize($view['backup_used']*1024) ." / " . formatSize($view['backup_size']*1024)."</dd>";
        echo "<dt>".$T('avail_label')."</dt><dd>".formatSize($view['backup_avail']*1024)."</dd>";
        echo "<div id='backup_plot' value='{$view['backup_used']}' max='{$view['backup_size']}'><div id='backup_label' class='progress-label'></div></div>";
    } else {
	    echo "<dt>".$T('storage_stats')."</dt><dd>".$T('unknown')."</dd>";
    }
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
    .dashboard-item .ui-progressbar {
        position: relative;
    }

");

$view->includeJavascript("
(function ( $ ) {

    function refreshMasonry() {
        if ($(window).width() > 500) {
            $('#Dashboard_SystemStatus').masonry({
                itemSelector: '.dashboard-item',
                isAnimated: false,
            });
        }
    }
    function updateProgress(name, max, value, colorize) {
         p = value/max*100;
         $('#'+name+'_plot').progressbar( { value: p });
         $('#'+name+'_label').text(p.toPrecision(3)+'%');
         progressbarValue = $('#'+name+'_plot').find('.ui-progressbar-value');

         if (!colorize) {
             progressbarValue.css({ 'background': '#eee' });
             return;
         }
         color = 'green';
         if (p < 70) {
             color = 'green';
         } else if (p >=70 && p<=80) {
             color = 'yellow';
         } else if (p>80 && p<90) {
             color = 'orange';
         } else {
             color = 'red';
         }
         progressbarValue.css({ 'background': color });

    }


    $(document).ready(function() {
        updateProgress('backup', $('#backup_plot').attr('max'), $('#backup_plot').attr('value'), 1);
        setTimeout(refreshMasonry,100);
        $( '#Dashboard' ).bind( 'tabsshow', function(event, ui) {
            if (ui.panel.id == 'Dashboard_SystemStatus') {
                refreshMasonry();
            }
	});
    });
} ( jQuery ));
");
