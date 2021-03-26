======================
nethserver-backup-data
======================

This module implements data backup for NethServer using different engines.

Available engines:

- duplicity: execute a full backup once a week, an incremental snapshot all other days of the week. Compression is enabled by default, encryption is not currently supported.
  It supports only storage backend which can be mounted on a local directory.  Used also for the single backup.
- restic: always execute incremental backups using file deduplication. Encryption is always enabled, compression is not implemented.
  It supports local and remote backends.
- rsync: Time Machine-style backup using rsync. Very fast and reliable, the destination contains just regular files which can be easily accessed.


The ``nethserver-backup-data`` package requires ``nethserver-backup-config``.

Configuration
=============

Backups can be scheduled in different hours to multiple storage backends.

Global properties
-----------------

This configuration is applied to all backups.
It uses the key ``backup-data`` inside ``configuration`` database.

Properties:

* ``IncludeLogs``: if enabled, add ``/var/log`` directory to backup, can be ``enabled`` or ``disabled``. Default is ``disabled``.


Local properties
----------------

This configuration is applied only to the selected backups.
Every backup record is saved inside the ``backups`` database. Each record can have 3 different types:

* ``duplicity``
* ``restic``
- ``rsync``

The key of the record is referred as the backup ``name``.


Common properties:

* ``status`` : enable or disable the automatic backup, can be ``enabled`` or ``disabled``. Default is ``enabled``. Regardless of this property, the backup is always executed if started manually
* ``BackupTime``: time of the scheduled backup. Must be in the cron-style syntax: Es. ``15 7 * * *``. Runs on 7:15.
* ``VFSType`` : set the backup medium, can be ``usb``, ``cifs``, ``nfs`` or ``webdav``.
* ``SMBShare``: contains the Samba share name
* ``SMBHost`` : host name of the SMB server
* ``SMBLogin`` : login user for the SMB server
* ``SMBPassword`` : password for the SMB server
* ``USBLabel`` : contains the filesystem label 
* ``NFSHost`` : host name of the NFS server
* ``NFShare`` : contains the NFS share name
* ``Notify``: if set to ``always``, always send a notification with backup status; if set to ``error``, send a notification only on error; if set to ``never``, never send a notification
* ``NotifyTo``: send the notification to given mail address, default is ``root@localhost``
* ``WebDAVUrl`` : contains the WebDAV URL address
* ``WebDAVLogin`` : login user for the WebDAV server
* ``WebDAVPassword`` : password for the WebDAV server
* ``CleanupOlderThan`` : time to retain backups, accept duplicity syntax (eg. 7D, 1M). Default is: never (keep all backups)

Supported VFSType:

* ``cifs`` : save the backup on a remote SMB server. Authentication is mandatory.
* ``nfs`` : save the backup on a remote NFS server. No authentication supported.
* ``usb`` : save the backup on a USB device. The device must have a writable filesystem with a custom label. 
  When the backup is started, the system will search for an USB device with the filesystem label saved in ``USBLabel``.
* ``webdav`` : save the backup on a WebDAV server. When using a secure connection make sure the target WebDAV server has a valid SSL certificate, otherwise the system will fail mounting the filesystem.


Backward compatibility
======================

To retain the backward compatibility with the old "single backup" feature, a backup named ``backup-data`` has the following special features:

- can have a ``NotifyFrom`` prop to specify the sender address of notification mail
- the backup can be modified from the old Server Manger and the status is reported inside the dashboard
- can be selectively restored using nethserver-restore-data package

Backup
======

The main command is ``/sbin/e-smith/backup-data -b <name>`` which starts the backup process. The backup is composed of three parts:

* *pre-backup-data* event: prepare the system (eg. dump of mysql tables)
* */etc/e-smith/events/actions/backup-data-<program>* action: execute the backup
  This actions must implement full/incremental logic and should also take care to mount and umount the destination
* *post-backup-data*: cleanup. Actions in this event can also implement retention policies


Logs and wrapper
----------------

Everything is logged to standard output and standard error.

If the backup is executed using ``backup-data-wrapper``,
a new log will be created inside ``/var/log/backup``.

After backup execution, the wrapper will also call
all executable scripts inside ``/etc/backup-data.hooks/`` directory.

Each script is invoked with the following parameters:

- backup name
- log file
- backup exit code


Default hooks
~~~~~~~~~~~~~

- ``backup-dashboard-status``: save the status of the backup ``/var/spool/backup/status-<backup_name>``
- ``backup-notify``: send backup notifications mails

Adding a backup
---------------

1. Create a backup record with all required options. Example: create a restic backup named ``t1`` using sFTP backend:

   ::

     db backups set t1 restic VFSType sftp SftpHost 192.168.1.123 SftpUser root SftpPort 22 SftpDirectory /mnt/t1 status enabled BackupTime 3:00 CleanupOlderThan 30D Notify error NotifyTo root@localhost

2. Enable the configuration:

  ::

     echo -e "Nethesis,1234" > /tmp/t1-password; signal-event nethserver-backup-data-save t1  /tmp/t1-password

Start a backup
--------------

Start the backup, by passing the name of the backup to ``backup-data`` command. Example:

  ::

    backup-data -b t1

Disk usage
==========

Each backup script creates statistics about disk utilization on the backup destination.
Statistics are available only for: cifs, nfs and usb.

Data are saved inside ``/var/spool/backup/disk_usage-<backup_name>``.

Indexing
========

In the *pre-backup-data* event the disk analyzer (Duc) make an indexing of filesystem, useful to create the graphical tree.

The name of the actions is ``/etc/e-smith/events/actions/nethserver-restore-data-duc-index`` and it compose the JSON file to create
the navigable graphic tree.

The indexing feature is limited to the backup named ``backup-data``.

Customization
=============

Global
------

Add custom include/exclude inside following files:

* ``/etc/backup-data.d/custom.include``
* ``/etc/backup-data.d/custom.exclude``

This configuration is applied to all backups.

Local
-----

Each backup can **override** the global list of include/exclude by creating two special files:

- ``/etc/backup-data/<name>.include``
- ``/etc/backup-data/<name>.exclude``

Where ``name`` is the name of the backup.

Retention policy
================

All backups can be deleted after a certain amount of time. Cleanup process takes place in post-backup-data event.
See ``CleanupOlderThan`` property.

Restore
=======

Restore from command line
-------------------------

The main command is ``/sbin/e-smith/restore-data -b <name>`` which starts the restore process:

* *pre-restore-data* event: used to prepare the system (Eg. mysql stop)
* *restore-data-<program>* action: search for a backup in the configuration database and restore it
* *post-restore-data* event: used to inform programs about new available data (eg. mysql restart)

To restore all data into the original location, use: ::

  restore-data -b <name>

To restore a file or directory, use: ::

  restore-file -b <name> <position> <path>

List backup contents
====================

The list of file inside each backup can be obtained using: ::

 /sbin/e-smith/backup-data-list -b <name>

Duplicity
=========

The default program used for backup is duplicity using the globbing file list feature. Encryption is disabled and duplicity cache is stored in ``/var/lib/nethserver/backup/duplicity/ directory``.
We plan to support all duplicity features in the near future.

See http://duplicity.nongnu.org/ for more information.

Extra options
-------------

Properties valid only for duplicity engine, see "Single backup" section for an explanation of each property:

* ``Type`` : can be ``full`` or ``incremental``. If ``full``, a full backup will be executed every time.
  If ``incremental``, a full backup will be executed once a week at ``FullDay``, all other backups will be incremental
* ``FullDay`` : number of day of the week when a full backup will be executed. Can be a number from 0 (Sunday) to 6 (Saturday). Defaults is ``0``.
* ``VolSize`` : size of chunks in MB, if supported by ``Program``. Default is 250

Storage backends
----------------

Supported ``VFSType`` :

* ``usb``
* ``cifs``
* ``nfs``
* ``webdav``

Listing backup sets
-------------------

To list current backup sets:

1. Mount the backup directory
2. Query duplicity status
3. Umount the backup directory

Just execute: ::

  /etc/e-smith/events/actions/mount-`config getprop backup-data VFSType`
  duplicity collection-status --no-encryption --archive-dir /var/lib/nethserver/backup/duplicity/ file:///mnt/backup/`config get SystemName`
  /etc/e-smith/events/actions/umount-`config getprop backup-data VFSType`

Restic
======

Implement backup engine using restic (https://restic.net/), it can be used as duplicity replacement for standard
backup or as multiple backup.

In restic, cleanup operations are composed by two commands: forget, to remove a snapshot, and prune, to actually remove the data
that was referenced by the deleted snapshot.
The prune operation is quite slow and should be executed at least once a week.

Extra options
-------------

* ``Prune``: execute the pruning on the specified time. Valid values are:

  * ``always``: run the prune every time at the end of backup
  * a number between ``0`` and ``6``: run the prune on the selected week day (0 is Sunday, 1 is Monday)

Storage backends
----------------

Supported ``VFSType`` :

* ``usb``
* ``cifs``
* ``nfs``
* ``webdav``
* ``s3``: Amazon S3 (or compatible server like Minio)
* ``sftp``: FTP over SSH
* ``b2``: BackBlaze B2
* ``rest``: Restic REST server


sftp
~~~~

SFTP

Connection to remote host uses a specific public key. A password is needed only once to copy the public key to the remote host.
SSH client configuration is added to ``/etc/ssh/sshd_config``.

Properties:

* ``SftpHost``: SSH host name or IP address
* ``SftpUser``: SSH user
* ``SftpPort``: SSH port
* ``SftpDirectory``: destination directory, must be writable by SSH user

Example: ::

  db backups set t1 restic status enabled BackupTime '15 7 * * *' CleanupOlderThan 30D Notify error NotifyTo root@localhost Prune 1 \
  VFSType sftp SftpHost 192.168.1.2 SftpUser root SftpPort 22 SftpDirectory /mnt/t1 
  echo -e "Nethesis,1234" > /tmp/t1-password; signal-event nethserver-backup-data-save t1  /tmp/t1-password

The temporary file containing the password will be deleted at the end of ``nethserver-backup-data-save`` event.

s3
~~

Amazon S3 (https://aws.amazon.com/s3/) compatible (like https://www.minio.io/).

Properties

* ``S3AccessKey``: user access key
* ``S3Bucket``: bucket (directory) name
* ``S3Host``: S3 host, use s3.amazonaws.com for Amazon
* ``S3SecretKey``: secret access key

Example: ::

  db backups set t1 restic VFSType s3 BackupTime '15 7 * * *' CleanupOlderThan never Notify error NotifyTo root@localhost status enabled Prune always\
  S3AccessKey XXXXXXXXXXXXXXXXXXXX S3Bucket restic-demo S3Host s3.amazonaws.com S3SecretKey xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx status enabled
  signal-event nethserver-backup-data-save t1


How to setup Amazon S3 access keys: https://restic.readthedocs.io/en/stable/080_examples.html


b2
~~

BackBlaze B2 (https://www.backblaze.com/b2/cloud-storage.html)

Properties:

* ``B2AccountId``: B2 account name
* ``B2AccountKey``: B2 account secret key
* ``B2Bucket``: B2 bucket (directory)

Example: ::
  
  db backups set t1 restic VFSType b2 BackupTime '15 7 * * *' CleanupOlderThan never Notify error NotifyTo root@localhost status enabled \
  B2AccountId B2AccountId xxxxxxxxxxxx B2AccountKey xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx B2Bucket restic-demo 
  signal-event nethserver-backup-data-save t1


Rest
~~~~

Restic REST server (https://github.com/restic/rest-server)

Properties:

* ``RestDirectory``: destination directory
* ``RestHost``: REST server host name or IP address
* ``RestPort``: REST server port (default for server is 8000)
* ``RestProtocol``: REST protocol, can be ``http`` or ``https``
* ``RestUser``: user for authentication (optional)
* ``RestPassword``: password for authentication (optional)


Example: ::

  db backups set t1 restic VFSType rest BackupTime '15 7 * * *' CleanupOlderThan never Notify error NotifyTo root@localhost status enabled \
  RestDirectory t1 RestHost 192.168.1.2 RestPassword mypass RestPort 8000 RestProtocol http RestUser myuser
  signal-event nethserver-backup-data-save t1

 
Database example: ::

 t2=restic
    BackupTime=1 7 * * *
    CleanupOlderThan=never
    Notify=error
    NotifyTo=root@localhost
    SMBHost=192.168.1.234
    SMBLogin=test
    SMBPassword=test
    SMBShare=test
    VFSType=cifs
    status=enabled
 t3=restic
    BackupTime=15 7 * * *
    CleanupOlderThan=never
    NFSHost=192.168.1.234
    NFSShare=/test
    Notify=error
    NotifyTo=root@localhost
    VFSType=nfs
    status=enabled

REST server
-----------

To manually install the REST server, download it from https://github.com/restic/rest-server/releases and save it 
under ``/usr/local/bin/rest-server``, example Linux 64bit: ::

  R=0.9.7; wget https://github.com/restic/rest-server/releases/download/v$R/rest-server-$R-linux-amd64.gz -O - | zcat > /usr/local/bin/rest-server
  chmod a+x /usr/local/bin/rest-server

Then configure it for NethServer: ::

  wget https://raw.githubusercontent.com/restic/rest-server/master/examples/systemd/rest-server.service -O - | sed 's/www\-data/apache/g' > /etc/systemd/system/rest-server.service
  systemctl daemon-reload
  systemctl start rest-server
  systemctl enable rest-server
  config set rest-server service TCPPort 8000 access green status enabled
  signal-event firewall-adjust


rsync
=====

Implement Time machine-style backup engine using ``rsync_tmbackup.sh`` (https://github.com/laurent22/rsync-time-backup),
based on rsync (https://rsync.samba.org/). It can be used as duplicity replacement for standard
backup or as multiple backup.

Retention policy
----------------

Backup sets are automatically deleted when the disk is getting full.

More info on expiration strategy: https://github.com/laurent22/rsync-time-backup#backup-expiration-logic

Storage backends
----------------

Supported ``VFSType`` :

* ``usb``
* ``cifs``
* ``nfs``
* ``webdav``
* ``sftp``: FTP over SSH


sftp
~~~~

SFTP

Connection to remote host uses a specific public key. A password is needed only once to copy the public key to the remote host.
SSH client configuration is added to ``/etc/ssh/sshd_config``.

Properties:

* ``SftpHost``: SSH host name or IP address
* ``SftpUser``: SSH user
* ``SftpPort``: SSH port
* ``SftpDirectory``: destination directory, must be writable by SSH user

Example: ::

  db backups set t1 rsync status enabled BackupTime '15 7 * * *' Notify error NotifyTo root@localhost \
  VFSType sftp SftpHost 192.168.1.2 SftpUser root SftpPort 22 SftpDirectory /mnt/t1 
  echo -e "Nethesis,1234" > /tmp/t1-password; signal-event nethserver-backup-data-save t1  /tmp/t1-password

The temporary file containing the password will be deleted at the end of ``nethserver-backup-data-save`` event.

 
Database example: ::

 t2=rsync
    BackupTime=1 7 * * *
    Notify=error
    NotifyTo=root@localhost
    SMBHost=192.168.1.234
    SMBLogin=test
    SMBPassword=test
    SMBShare=test
    VFSType=cifs
    status=enabled
 t3=rsync
    BackupTime=15 7 * * *
    NFSHost=192.168.1.234
    NFSShare=/test
    Notify=error
    NotifyTo=root@localhost
    VFSType=nfs
    status=enabled
