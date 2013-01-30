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
 * Configure Hylafax and modem
 *
 * @author Giacomo Sanchietti <giacomo.sanchietti@nethesis.it>
 * @since 1.0
 */
class BackupData extends \Nethgui\Controller\AbstractController
{

    /**
     *
     *
     * @var Array list of valid VFSType
     */
    private $vfstypes = array('usb','cifs','nfs');

    protected function initializeAttributes(\Nethgui\Module\ModuleAttributesInterface $base)
    {
        return \Nethgui\Module\SimpleModuleAttributesProvider::extendModuleAttributes($base, 'Configuration', 50);
    }

    public function initialize()
    {
        parent::initialize();
        $this->declareParameter('status', Validate::SERVICESTATUS, array('configuration', 'backup-data', ''));
        $this->declareParameter('BackupTime', $this->createValidator()->regexp('^(([0-1]?[0-9])|([2][0-3])):([0-5]?[0-9])$'), array('configuration', 'backup-data', 'BackupTime'));
        $this->declareParameter('Type', $this->createValidator()->memberOf(array('full','incremental')), array('configuration', 'backup-data', 'Type'));
        $this->declareParameter('ForceFull',Validate::SERVICESTATUS, array('configuration', 'backup-data', 'FaxName'));
        $this->declareParameter('FullDay', $this->createValidator()->integer()->greatThan(-1)->lessThan(7), array('configuration', 'backup-data', 'FullDay'));
        
        $this->declareParameter('VFSType', $this->createValidator()->memberOf($this->vfstypes), array('configuration', 'backup-data', 'VFSType'));
        
        $this->declareParameter('SMBShare', Validate::ANYTHING, array('configuration', 'backup-data', 'SMBShare'));
        $this->declareParameter('SMBHost', Validate::ANYTHING, array('configuration', 'backup-data', 'SMBHost'));
        $this->declareParameter('SMBLogin', Validate::ANYTHING, array('configuration', 'backup-data', 'SMBLogin'));
        $this->declareParameter('SMBPassword', Validate::ANYTHING, array('configuration', 'backup-data', 'SMBPassword'));

        $this->declareParameter('NFSShare', Validate::ANYTHING, array('configuration', 'backup-data', 'NFSShare'));
        $this->declareParameter('NFSHost', Validate::ANYTHING, array('configuration', 'backup-data', 'NFSHost'));
        
        $this->declareParameter('USBLabel', Validate::ANYTHING, array('configuration', 'backup-data', 'USBLabel'));

    }

    protected function onParametersSaved($changes)
    {
        $this->getPlatform()->signalEvent('nethserver-backup-data-save@post-process');
    }

    public function prepareView(\Nethgui\View\ViewInterface $view)
    {
        parent::prepareView($view);
        $view['TypeDatasource'] = array_map(function($fmt) use ($view) {
            return array($fmt, $view->translate($fmt . '_label'));
        }, array('full', 'incremental'));

        $view['FullDayDatasource'] = array_map(function($fmt) use ($view) {
            return array($fmt, $view->translate($fmt . '_label'));
        }, array(0, 1, 2, 3, 4, 5, 6));

        $view['VFSTypeDatasource'] = array_map(function($fmt) use ($view) {
            return array($fmt, $view->translate($fmt . '_label'));
        }, $this->vfstypes);

    }


}
