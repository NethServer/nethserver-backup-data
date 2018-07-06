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
        $br = $this->getPlatform()->getDatabase('configuration')->getKey('backup-data');
        $backup['vfs'] = $br['VFSType'] ? $br['VFSType'] : '-';
        $backup['status'] = $br['status'];
        $backup['time'] = $br['BackupTime'];

        $disk_usage_file = "/var/lib/nethserver/backup/disk_usage";
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
