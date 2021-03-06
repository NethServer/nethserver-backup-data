<?php
namespace NethServer\Module\Dashboard\SystemStatus;

/*
 * Copyright (C) 2013 Nethesis S.r.l.
 *
 * This script is part of NethServer.
 *
 * NethServer is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * NethServer is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Retrieve backup status
 *
 * @author Giacomo Sanchietti
 */
 
class Backup extends \Nethgui\Controller\AbstractController
{

    public $sortId = 30;
 
    private $backup = array();
	
    private  function formatDay($d,$view){
	$days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
	return $view->translate($days[$d]);
    }
	
    private function readBackup($view)
    {

        $backup = array();
        $status_file = "/var/spool/backup/status-backup-data";
        if (file_exists($status_file)) {
            $status = file_get_contents($status_file);
            $backup['end'] = filemtime($status_file);;
            $backup['result'] = $status == 0 ? 'SUCCESS' : 'ERROR';
        } else {
            $backup['result'] = "-";
            $backup['end'] = "";
        }
        $br = $this->getPlatform()->getDatabase('backups')->getKey('backup-data');
        $backup['vfs'] = $br['VFSType'] ? $br['VFSType'] : '-';
        $backup['status'] = $br['status'];
        $backup['time'] = $br['BackupTime'];
        
        $hour = explode(" ",$backup['time']);
              
        if($hour[1] == "*" && $hour[2] == "*" && $hour[3] == "*" && $hour[4] == "*"){
	        $backup['time'] = $view->translate('EveryHour')."&nbsp;".sprintf('%02d',$hour[0]);
        }else if($hour[2] == "*" && $hour[3] == "*" && $hour[4] == "*"){
 	        $backup['time'] = $view->translate('EveryDay')."&nbsp;".sprintf('%02d',$hour[1]).":".sprintf('%02d',$hour[0]);
        }else if($hour[2] == "*" && $hour[3] == "*"){
		$backup['time'] = $view->translate('EveryWeek')."&nbsp;".$this->formatDay($hour[4],$view)." at ".sprintf('%02d',$hour[1]).":".sprintf('%02d',$hour[0]);
        }else if($hour[3] == "*" && $hour[4] == "*"){
	        $backup['time'] = $view->translate('EveryMonth')."&nbsp;".$hour[2]." at ".sprintf('%02d',$hour[1]).":".sprintf('%02d',$hour[0]);
        }
        		 
        $disk_usage_file = "/var/spool/backup/disk_usage-backup-data";
        if (file_exists($disk_usage_file)) {
            $file = file_get_contents("$disk_usage_file");
            if ($du = json_decode($file, true)) {
                if ( is_numeric($du['size']) && $du['size'] >= 0 &&
                     is_numeric($du['used']) && $du['used'] >= 0 &&
                     is_numeric($du['avail']) && $du['avail'] >= 0 ) {
                        $backup = array_merge($backup, $du);
                }
            }
        }

        return $backup;
    }

    public function process()
    {
        $this->backup = $this->readBackup();
    }
 
    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        if (!$this->backup) {
            $this->backup = $this->readBackup($view);
        }
        foreach ($this->backup as $k => $v) {
            $view['backup_' . $k] = $v;
        }
    }	
       
}
