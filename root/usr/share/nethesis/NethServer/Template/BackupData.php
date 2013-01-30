<?php

echo $view->header()->setAttribute('template', $T('BackupData_Title'));
$general = $view->panel()
    ->setAttribute('title', $T('BackupData_General_Title'))
    ->insert($view->checkbox('status','enabled')->setAttribute('uncheckedValue', 'disabled'))
    ->insert($view->textInput('BackupTime'))

    ->insert($view->fieldset()->setAttribute('template',$T('BackupDataType_label'))
        ->insert($view->fieldsetSwitch('Type', 'full'))
        ->insert($view->fieldsetSwitch('Type', 'incremental', $view::FIELDSETSWITCH_EXPANDABLE)
            ->insert($view->selector('FullDay', $view::SELECTOR_DROPDOWN))
            ->insert($view->checkbox('ForceFull','enabled')->setAttribute('uncheckedValue', 'disabled'))
        )
     )

;
$destination = $view->panel()
    ->setAttribute('title', $T('BackupData_Destination_Title'))
    ->insert($view->fieldsetSwitch('VFSType', 'usb',$view::FIELDSETSWITCH_EXPANDABLE)
        ->insert($view->textInput('USBLabel')->setAttribute('template',$T('BackupDataType_device')))
    )
    ->insert($view->fieldsetSwitch('VFSType', 'cifs',$view::FIELDSETSWITCH_EXPANDABLE)
        ->insert($view->textInput('SMBHost'))
        ->insert($view->textInput('SMBShare'))
        ->insert($view->textInput('SMBLogin'))
        ->insert($view->textInput('SMBPassword'))
    )
    ->insert($view->fieldsetSwitch('VFSType', 'nfs',$view::FIELDSETSWITCH_EXPANDABLE)
        ->insert($view->textInput('NFSHost'))
        ->insert($view->textInput('NFSShare'))
    )
;

$tabs = $view->tabs()
    ->insert($general)
    ->insert($destination)
;

echo $tabs;

echo $view->buttonList($view::BUTTON_SUBMIT | $view::BUTTON_HELP);

