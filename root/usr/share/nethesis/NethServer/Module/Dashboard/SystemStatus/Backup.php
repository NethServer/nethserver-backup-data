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

    private function readBackup()
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
        
        $orario = explode(" ",$backup['time']);
      
        function formatOre($h){
	        if(strlen($h)==1){return "0".$h;}else{return $h;}
        }
        
        function formatMin($m){
	         if(strlen($m)==1){return "0".$m;}else{return $m;}
        }
        
        function formatDay($d){
	        switch($d){
		        case 0: return "Sunday";
		        	break;
		        case 1: return "Monday";
		        	break;
		        case 2: return "Tuesday";
		        	break;
		        case 3: return "Wednesday";
		        	break;
		        case 4: return "Thursday";
		        	break;
		        case 5: return "Friday";
		        	break;
		        case 6: return "Saturday";
	        }
        }
        
        if($orario[1] == "*" && $orario[2] == "*" && $orario[3] == "*" && $orario[4] == "*"){
	        $backup['time'] = "Every hour at minute ".formatMin($orario[0]);
        }else if($orario[2] == "*" && $orario[3] == "*" && $orario[4] == "*"){
	        $backup['time'] = 'Every day at '.formatOre($orario[1]).":".formatMin($orario[0]);
        }else if($orario[2] == "*" && $orario[3] == "*"){
			$backup['time'] = "Every ".formatDay($orario[4])." at ".formatOre($orario[1]).":".formatMin($orario[0]);
        }else if($orario[3] == "*" && $orario[4] == "*"){
	        $backup['time'] = "Every month on ".$orario[2]." at ".formatOre($orario[1]).":".formatMin($orario[0]);
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
            $this->backup = $this->readBackup();
        }
        foreach ($this->backup as $k => $v) {
            $view['backup_' . $k] = $v;
        }
    }
}
