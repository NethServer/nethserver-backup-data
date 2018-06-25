=================
nethserver-restic
=================

Supported backends:

- CIFS
- NFS
- USB
- sFTP
- Amazon S3 or compatibile
- BackBlaze B2B
- Restic REST server


TODO
====

- Better handling of restore and backup logs


Add a backup
============

sFTP example: ::

  db backups set t1 restic VFSType sftp SftpHost 192.168.1.2 SftpUser root SftpPassword Nethesis,1234 SftpPort 22 SftpDirectory /mnt/t1 status enabled BackupTime 3:00 CleanupOlderThan 30D Notify error NotifyFrom '' NotifyTo root@localhost
  echo -e "Nethesis,1234" > /tmp/t1-password; signal-event nethserver-backup-data-save t1  /tmp/t1-password

Execute: ::

  backup-data t1


Database
========

Example: ::

 t2=restic
    BackupTime=1:00
    CleanupOlderThan=never
    Notify=error
    NotifyFrom=
    NotifyTo=root@localhost
    SMBHost=192.168.1.234
    SMBLogin=test
    SMBPassword=test
    SMBShare=test
    VFSType=cifs
    status=enabled
 t3=restic
    BackupTime=1:00
    CleanupOlderThan=never
    NFSHost=192.168.1.234
    NFSShare=/test
    Notify=error
    NotifyFrom=
    NotifyTo=root@localhost
    VFSType=nfs
    status=enabled
 t4=restic
    BackupTime=2:00
    CleanupOlderThan=10D
    Notify=error
    NotifyFrom=
    NotifyTo=root@localhost
    USBLabel=backup
    VFSType=usb
    status=enabled
 t5=restic
    BackupTime=3:00
    CleanupOlderThan=10D
    Notify=error
    NotifyFrom=
    NotifyTo=root@localhost
    SftpDirectory=/tmp/t5/
    SftpHost=192.168.1.234
    SftpPort=22
    SftpUser=root
    VFSType=sftp
    status=enabled
 t6=restic
    BackupTime=4:00
    CleanupOlderThan=10D
    Notify=error
    NotifyFrom=
    NotifyTo=root@localhost
    S3AccessKey=XXXXXXXXXXXXXXXXXXXX
    S3Bucket=restic-demo
    S3Host=s3.amazonaws.com
    S3SecretKey=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
    VFSType=s3
    status=enabled
 t7=restic
    B2AccountId=xxxxxxxxxxxx
    B2AccountKey=xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
    B2Bucket=restic-demo
    BackupTime=5:00
    CleanupOlderThan=11D
    Notify=error
    NotifyFrom=
    NotifyTo=root@localhost
    VFSType=b2
    status=enabled
 t8=restic
    BackupTime=6:00
    CleanupOlderThan=10D
    Notify=error
    NotifyFrom=
    NotifyTo=root@localhost
    RestDirectory=t8
    RestHost=localhost
    RestPassword=test
    RestPort=8000
    RestProtocol=http
    RestUser=test
    VFSType=rest
    status=enabled


