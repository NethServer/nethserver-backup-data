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
        $log = "/var/log/backup-data.log";
        $backup = array();
        if (file_exists($log)) {
            $lines = array_reverse(file($log));
            foreach ($lines as $line) {
                $tmp = explode(' - ', $line);
                if ($tmp[1] == "START") {
                    $backup['start'] = strtotime($tmp[0]);
                    break;
                }
            }
            $tmp = explode(' - ', $lines[0]);
            $backup['result'] = $tmp[1];;
            $backup['end'] = strtotime($tmp[0]);
        }
        $br = $this->getPlatform()->getDatabase('configuration')->getKey('backup-data');
        $backup['vfs'] = $br['VFSType'];
        $backup['status'] = $br['status'];
        $backup['type'] = $br['Type'];
        $backup['time'] = $br['BackupTime'];

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
