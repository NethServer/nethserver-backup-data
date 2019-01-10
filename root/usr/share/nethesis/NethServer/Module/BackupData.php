<?php
namespace NethServer\Module;

/*
 * Copyright (C) 2012 Nethesis S.r.l.
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

use Nethgui\System\PlatformInterface as Validate;


/**
 * Configure Backup Data
 *
 * @author Giacomo Sanchietti <giacomo.sanchietti@nethesis.it>
 * @since 1.0
 */
class BackupData extends \Nethgui\Controller\AbstractController
{

    /**
     * @var Array list of valid VFSType
     */
    private $vfstypes = array('usb','cifs','nfs','webdav');

    /**
     * @var Array list of valid notification values
     */
    private $notifytypes = array('error','always','never');
    
    /**
     * @var Array list of valid cleanup times
     */
    private $cleanuptypes = array('never','7D','14D','28D','56D','168D','364D');


    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        return \Nethgui\Module\SimpleModuleAttributesProvider::extendModuleAttributes($base, 'Configuration', 50);
    }

    public function initialize()
    {
        parent::initialize();
        $fromValidator = $this->createValidator()->orValidator(
             $this->createValidator(\Nethgui\System\PlatformInterface::EMPTYSTRING),
             $this->createValidator(Validate::EMAIL)
        );
        $this->declareParameter('status', Validate::SERVICESTATUS, array('backups', 'backup-data', 'status'));
        $this->declareParameter('BackupTime', $this->createValidator()->regexp('/^(([0-1]?[0-9])|([2][0-3])):([0-5]?[0-9])$/'), array('backups', 'backup-data', 'BackupTime'));
        $this->declareParameter('Type', $this->createValidator()->memberOf(array('full','incremental')), array('backups', 'backup-data', 'Type'));
        $this->declareParameter('FullDay', $this->createValidator()->integer()->greatThan(-1)->lessThan(7), array('backups', 'backup-data', 'FullDay'));

        $this->declareParameter('notifyToType', $this->createValidator()->memberOf(array('admin','custom')), array());
        $this->declareParameter('notifyToCustom', Validate::EMAIL, array());
        $this->declareParameter('notifyTo', FALSE, array('backups', 'backup-data', 'NotifyTo')); # not accessibile from UI, position is IMPORTANT
        $this->declareParameter('notify', $this->createValidator()->memberOf($this->notifytypes), array('backups', 'backup-data', 'Notify'));
        $this->declareParameter('notifyFrom', $fromValidator, array('backups', 'backup-data', 'NotifyFrom'));

        $this->declareParameter('VFSType', $this->createValidator()->memberOf($this->vfstypes), array('backups', 'backup-data', 'VFSType'));
        
        $this->declareParameter('SMBShare', Validate::NOTEMPTY, array('backups', 'backup-data', 'SMBShare'));
        $this->declareParameter('SMBHost', Validate::HOSTADDRESS, array('backups', 'backup-data', 'SMBHost'));
        $this->declareParameter('SMBLogin', Validate::ANYTHING, array('backups', 'backup-data', 'SMBLogin'));
        $this->declareParameter('SMBPassword', Validate::ANYTHING, array('backups', 'backup-data', 'SMBPassword'));

        $this->declareParameter('NFSShare', Validate::NOTEMPTY, array('backups', 'backup-data', 'NFSShare'));
        $this->declareParameter('NFSHost', Validate::HOSTADDRESS, array('backups', 'backup-data', 'NFSHost'));
        
        $this->declareParameter('USBLabel', Validate::NOTEMPTY, array('backups', 'backup-data', 'USBLabel'));

        $this->declareParameter('WebDAVUrl', Validate::NOTEMPTY, array('backups', 'backup-data', 'WebDAVUrl'));
        $this->declareParameter('WebDAVLogin', Validate::ANYTHING, array('backups', 'backup-data', 'WebDAVLogin'));
        $this->declareParameter('WebDAVPassword', Validate::ANYTHING, array('backups', 'backup-data', 'WebDAVPassword'));

        $this->declareParameter('CleanupOlderThan', $this->createValidator()->memberOf($this->cleanuptypes), array('backups', 'backup-data', 'CleanupOlderThan'));
        $this->declareParameter('IncludeLogs', Validate::SERVICESTATUS, array('configuration', 'backup-data', 'IncludeLogs'));

    }

    protected function onParametersSaved($changes)
    {
        $this->getPlatform()->signalEvent('nethserver-backup-data-save');
    }

    private function getModel($item) {
        $label = '';
        if (isset($item['VENDOR']) && $item['VENDOR']) {
             $label .= " - ".trim($item['VENDOR']);
        }
        if (isset($item['MODEL']) && $item['MODEL']) {
             $label .= " - ".trim($item['MODEL']);
        }
        return $label;
    }

    private function listFilesystems(\Nethgui\View\ViewInterface $view)
    {
        $ret = array();
        $ret[] = array(""," --- ");
        $items = array();
        $devices = $this->getPlatform()->exec('/usr/bin/lsblk -o NAME,LABEL,MOUNTPOINT,TYPE,VENDOR,MODEL,PKNAME,SIZE -P')->getOutputArray();
        foreach ($devices as $device) {
            $r = array();
            preg_match_all("/([^,= ]+)=\"([^,=]*)\"/", $device, $r);
            $tmp = array_combine($r[1], $r[2]);
            $items[$tmp['NAME']] = $tmp;
        }
        foreach ($items as $key => $item) {
            if ($item['TYPE'] != 'rom' && $item['TYPE'] != 'lvm' && $item['LABEL']) {
                $fslabel = trim($item['LABEL']);
                $label = $fslabel;
                if (isset($item['MOUNTPOINT']) && $item['MOUNTPOINT']) {
                     $label .= ' ('.$item['MOUNTPOINT'].')';
                }
                $model = $this->getModel($item);
                if (!$model) {
                    $model = $this->getModel($items[$item['PKNAME']]);
                }
                $size = '';
                if (isset($item['SIZE']) && $item['SIZE']) {
                    $size = " - ".$item['SIZE'];
                }
                $ret[] = array($fslabel,$label.$model.$size);
            }
        }

        return $ret;
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['TypeDatasource'] = array_map(function($fmt) use ($view) {
            return array($fmt, $view->translate($fmt . '_label'));
        }, array('full', 'incremental'));

        $view['FullDayDatasource'] = array_map(function($fmt) use ($view) {
            return array($fmt, $view->translate($fmt . '_label'));
        }, array("1", "2", "3", "4", "5", "6", "0"));

        $view['VFSTypeDatasource'] = array_map(function($fmt) use ($view) {
            return array($fmt, $view->translate($fmt . '_label'));
        }, $this->vfstypes);

        $view['USBLabelDatasource'] = $this->listFilesystems($view);
        
        $view['CleanupOlderThanDatasource'] = array_map(function($fmt) use ($view) {
            return array($fmt, $view->translate($fmt . '_label'));
        }, $this->cleanuptypes);

        $view['SenderAddress'] = $this->getPlatform()->getDatabase('configuration')->getProp('root','SenderAddress');
    }

    public function validate(\Nethgui\Controller\ValidationReportInterface $report)
    {
        if ($this->getRequest()->isMutation()) {
            $validator = $this->createValidator()->memberOf($this->vfstypes);
            if ($this->parameters['status'] == 'enabled' && !$validator->evaluate($this->parameters['VFSType'])) {
                 $report->addValidationError($this, 'VFSType', $validator);
            }
            switch ($this->parameters{'VFSType'}) {
                case 'cifs':
                    if (strpos($this->parameters['SMBPassword'],'|') !== false) {
                        $report->addValidationErrorMessage($this, 'SMBPassword', 'invalid_pipe_char');
                    }
                    if ($this->parameters['SMBShare'] && substr($this->parameters['SMBShare'], -1) == '\\') {
                        $report->addValidationErrorMessage($this, 'SMBShare', 'invalid_last_char');
                    }
                    break;
                case 'webdav':
                    if (strpos($this->parameters['WebDAVPassword'],'|') !== false) {
                        $report->addValidationErrorMessage($this, 'WebDAVPassword', 'invalid_pipe_char');
                    }
                    if ( ! filter_var($this->parameters['WebDAVUrl'], FILTER_VALIDATE_URL) ) {
                        $report->addValidationErrorMessage($this, 'WebDAVUrl', 'invalid_url');
                    }
                    break;
                default:
                    // do nothing
            }
        }
        parent::validate($report);
    }


    public function readNotifyToCustom()
    {
        if ($this->parameters["notifyToType"] === 'custom') {
             return $this->parameters["notifyTo"];
        } else {
             return "";
        }
    }

    public function writeNotifyToCustom($value)
    {
        if ($this->parameters["notifyToType"] === 'custom') {
             $this->parameters["notifyTo"] = $value;
        } else {
             $this->parameters["notifyTo"] = "root@localhost";
        }
        return true;
    }


    public function readNotifyFrom()
    {
        $sender = $this->getPlatform()->getDatabase('configuration')->getProp('root','SenderAddress');
        if ($sender) {
            return $sender;
        } else {
            return $this->getPlatform()->getDatabase('backups')->getProp('backup-data','NotifyFrom');
        }
    }

    public function writeNotifyFrom($v)
    {
        $sender = $this->getPlatform()->getDatabase('configuration')->getProp('root','SenderAddress');
        if (!$sender) {
            return array($v);
        }
    }

    public function readNotifyToType()
    {
        $current = $this->getPlatform()->getDatabase('backups')->getProp('backup-data','NotifyTo');
        if($current == "root@localhost") {
            return "admin";
        } else {
            return "custom";
        }
    }

    public function writeNotifyToType($value)
    {
        if ($this->parameters["notifyToType"] === 'admin') {
             $this->parameters["notifyTo"] = 'root@localhost';
             return 'admin';
        } else {
             return "";
        }
    }

    public function readBackupTime()
    {
        $tmp = explode(" ", $this->getPlatform()->getDatabase('backups')->getProp('backup-data','BackupTime'));
        return sprintf('%d:%02d', $tmp[1], $tmp[0]);
    }

    public function writeBackupTime($value)
    {
        $tmp = explode(":",$value);
        return array(sprintf("%d %d * * *", $tmp[1], $tmp[0]));
    }
}
