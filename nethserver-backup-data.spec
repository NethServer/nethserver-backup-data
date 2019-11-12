%define tmbackup_commit da904fe66ce384ff3f844fdfc81b6a4d95410d9a

Summary: NethServer backup data and config files
Name: nethserver-backup-data
Version: 1.6.5
Release: 1%{?dist}
License: GPL
Source: %{name}-%{version}.tar.gz
Source1: https://raw.githubusercontent.com/laurent22/rsync-time-backup/%{tmbackup_commit}/rsync_tmbackup.sh
URL: %{url_prefix}/%{name}

BuildArch: noarch
BuildRequires: nethserver-devtools
Requires: cifs-utils, nfs-utils, duplicity, davfs2
Requires: nethserver-backup-config
Requires: sshpass
Requires: restic

%description
NethServer backup of config and data files

%prep
%setup

%build
%{makedocs}
perl createlinks

# relocate perl modules under default perl vendorlib directory:
mkdir -p root%{perl_vendorlib}
mkdir -p root/etc/backup-data
mkdir -p root/var/log/backup
mkdir -p root/var/spool/backup
mkdir -p root/etc/e-smith/events/post-restore-data
mkdir -p root/etc/e-smith/events/pre-backup-data
mkdir -p root/etc/e-smith/events/pre-restore-data
mv -v NethServer root%{perl_vendorlib}

%install
rm -rf %{buildroot}
(cd root ; find . -depth -print | cpio -dump %{buildroot})
mkdir -p %{buildroot}/usr/bin
mv %{SOURCE1} %{buildroot}/usr/bin/rsync_tmbackup
echo %{rsync_release} > RESTIC-RELEASE
%{genfilelist} --file /usr/bin/rsync_tmbackup 'attr(0755,root,root)'  %{buildroot} > %{name}-%{version}-%{release}-filelist

%files -f %{name}-%{version}-%{release}-filelist
%defattr(-,root,root)
%doc COPYING
%dir %{_nseventsdir}/%{name}-update
%dir %{_nseventsdir}/post-restore-data
%dir %{_nseventsdir}/pre-backup-data
%dir %{_nseventsdir}/pre-restore-data
%dir /var/log/backup
%dir /var/spool/backup
%dir /etc/backup-data
%dir /etc/backup-data.hooks
%config /etc/backup-data.d/custom.include
%config /etc/backup-data.d/custom.exclude


%changelog
* Tue Nov 12 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.6.5-1
- Duplicity backup-data-list missing files with spaces - Bug NethServer/dev#5898

* Thu Oct 31 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.6.4-1
- Restic backup over local usb may write to root filesystem - Bug NethServer/dev#5876

* Mon Oct 07 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.6.3-1
- Switch back to laurent22/rsync-time-backup - NethServer/nethserver-backup-data#48

* Thu Sep 05 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.6.2-1
- migrate. avoid empty backup record (#47)

* Wed Aug 28 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.6.1-1
- backup-data: rsync over sftp failure if non standard port - Bug NethServer/dev#5804

* Mon Aug 26 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.6.0-1
- Backup data restore Cockpit UI - NethServer/dev#5796
- Backup data list (rsync): list only from latest - Bug NethServer/dev#5799
- backup-data command: improved help message (#43)

* Fri Jun 14 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.5.4-1
- Incorrect rsync backup retention - NethServer/dev#5776

* Wed May 08 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.5.3-1
- Backup retention: 1 day - NethServer/dev#5748

* Mon Mar 04 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.5.2-1
- Restore data fails because of non-existent event - Bug NethServer/dev#5713
- Backup data stored in root filesystem - Bug NethServer/dev#5720

* Fri Feb 15 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.5.1-1
- Backup: duplicity doesn't cleanup old backups - Bug NethServer/dev#5710
- Dashboard: improve date visualization - Thanks to Federico Ballarini

* Wed Jan 30 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.5.0-1
- Remove single backup data - NethServer/dev#5691

* Tue Jan 08 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.5-1
- CIFS Backup fails after upgrade to 7.6 - NethServer/dev#5687

* Thu Dec 20 2018 Stephane de Labrusse <stephdl@de-labrusse.fr> - 1.4.4-1
- Backup: sender address not saved - Bug NethServer/dev#5677

* Tue Nov 27 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.3-1
- Pre-backup-data fails with disabled backup - Bug NethServer/dev#5655

* Mon Nov 05 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.2-1
- Email notification API - NethServer/dev#5614
- rsync: output file list only if invoked from tty

* Fri Sep 28 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.1-1
- Backup data: can't list content - Bug NethServer/dev#5591

* Tue Aug 28 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.0-1
- Backup-data: multiple schedule and backends - NethServer/dev#5538

* Wed Aug 08 2018 Davide Principi <davide.principi@nethesis.it> - 1.3.5-1
- TEXTINPUT_PASSWORD fields rendered as plain text - Bug NethServer/dev#5553

* Wed May 16 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.3.4-1
- Backup data: restore-file tries to restore non-exiting file - Bug NethServer/dev#5496

* Fri Apr 27 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.3.3-1
- Backup data: include log files - NethServer/dev#5470

* Wed Jul 12 2017 Davide Principi <davide.principi@nethesis.it> - 1.3.2-1
- Backup config history - NethServer/dev#5314

* Thu Apr 20 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.3.1-1
- Backup-data: remove .ssh directory from backup - Bug NethServer/dev#5269

* Wed Apr 12 2017 Davide Principi <davide.principi@nethesis.it> - 1.3.0-1
- Backup data: basic webDAV support for backups and storage stats - NethServer/dev#5235

* Tue Jan 03 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.2.3-1
- Backup-data not run after system crash - Bug NethServer/dev#5179

* Tue Sep 06 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.2.2-1
- Backup data: some files not included in backup - Bug NethServer/dev#5101

* Thu Sep 01 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.2.1-1
- Missing i18n labels - Bug NethServer/dev#5094

* Thu Jul 07 2016 Stefano Fancello <stefano.fancello@nethesis.it> - 1.2.0-1
- First NS7 release

* Tue Dec 01 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.1.8-1
- Backup data fails if a field contains \ character at the end  - Bug #3311 [NethServer]

* Tue Sep 29 2015 Davide Principi <davide.principi@nethesis.it> - 1.1.7-1
- Make Italian language pack optional - Enhancement #3265 [NethServer]

* Mon Sep 14 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.1.6-1
- Backup data fails if CIFS password contains pipe character - Bug #3249 [NethServer]

* Wed Jul 15 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.1.5-1
- Backup data: defer mount of destination - Enhancement #3222 [NethServer]
- Backup notification: add sender field - Enhancement #3219 [NethServer]

* Mon Jul 06 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.1.4-1
- Incremental backup fails after domain change - Bug #3174 [NethServer]
- Backup data: web interface for restore - Enhancement #2773 [NethServer]

* Thu Apr 23 2015 Davide Principi <davide.principi@nethesis.it> - 1.1.3-1
- Language packs support - Feature #3115 [NethServer]

* Wed Mar 18 2015 Davide Principi <davide.principi@nethesis.it> - 1.1.2-1
- backup-data fails just after installation - Bug #3004 [NethServer]

* Thu Mar 05 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.1.1-1
- backup-data retention policy multiple of 7 - Enhancement #3007 [NethServer]

* Tue Aug 05 2014 Davide Principi <davide.principi@nethesis.it> - 1.1.0-1.ns6
- Backup data: avoid multiple sessions - Enhancement #2828 [NethServer]

* Tue Jul 01 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.11-1.ns6
- Remove modifications for Enhancement #2773

* Mon Jun 30 2014 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.10-1.ns6
- Backup data: restore files from a certain time - Enhancement #2749
- Backup config: minimize creation of new backup - Enhancement #2699

* Wed Feb 26 2014 Davide Principi <davide.principi@nethesis.it> - 1.0.9-1.ns6
- Backup data: can't install package if backup is in progress - Bug #2667 [NethServer]
- Backup data: can't restore backup from old chians - Bug #2665 [NethServer]
- Send backup notification to root - Enhancement #2647 [NethServer]
- restore-data-duplicity action fails with whitespaces - Bug #2643 [NethServer]

* Wed Feb 05 2014 Davide Principi <davide.principi@nethesis.it> - 1.0.8-1.ns6
- NethCamp 2014 - Task #2618 [NethServer]
- Backup: force backup of configuration before starting backup-data - Enhancement #2118 [NethServer]
- Update all inline help documentation - Task #1780 [NethServer]
- Dashboard: new widgets - Enhancement #1671 [NethServer]

* Wed Oct 16 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.7-1.ns6
- Exclude e-smith db directory #2143
- Minor fix on web UI #2040

* Tue Aug 06 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.6-1.ns6
- Do not raise error on empty USBLabel if VFSType is not usb #2022

* Wed Jul 31 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.5-1.ns6
- Check if USBLabel is empty, check if USB disk is mounted on /mnt/backup #2022
- Force exclusions before inclusions
- Attach log on notification

* Fri Jul 12 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.4-1.ns6
- Backup: implement and document full restore #2043

* Tue Jun 25 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.3-1.ns6
* Add last-backup log extract to notification

* Mon Jun 17 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.2-1.ns6
- Avoid duplicate notifications. Refs #2023
- Implement simple retention policy. Refs #2024

* Tue Apr 30 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.1-1.ns6
- Rebuild for automatic package handling. #1870
- Add mail notification #1572
- Add web user interface
- Various bug fixes

* Mon Mar 18 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.0-1
- First release

* Wed Jan 30 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 0.9.0-1
- Fist release

