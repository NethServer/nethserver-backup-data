#
# Copyright (C) 2018 Nethesis S.r.l.
# http://www.nethesis.it - nethserver@nethesis.it
#
# This script is part of NethServer.
#
# NethServer is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License,
# or any later version.
#
# NethServer is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with NethServer.  If not, see COPYING.
#
package NethServer::Rsync;

use strict;

=head1 NAME

NethServer::Rsync -- utility functions for rsync backup.

=cut

=head1 DESCRIPTION

The library can be used to implement rsync actions for backup data.

=cut

=head1 USAGE

Usage example:

  use NethServer::Rsync;
  use esmith::ConfigDB;

  my $db = esmith::ConfigDB->open_ro('backups');
  my $backup = $db->get('test');
  my $repo = NethServer::Restic::prepareRepository(
     'my.system.domain',
     $backup
  );

  print $repo;

=cut

=head1 FUNCTIONS

=head2 initOptions

Return a string representing default rsync options.

=cut

sub initOptions {
   my $include = shift || die("No file specified for rsync backup");
   my $exclude = shift || '';
   my $flags = `rsync_tmbackup --rsync-get-flags`;
   chomp($flags);

   if (! -t STDOUT) {
       # hide list of changed files
       $flags =~ s/--itemize-changes//;
   }

   $flags .= " --files-from=$include ";
   if ($exclude ne '') {
       $flags .= " --exclude-from=$exclude ";
   }

   return "--rsync-set-flags \"$flags\"";
}

=head2 initRestoreOptions

Return a string representing default rsync options for restore.

=cut

sub initRestoreOptions {
   my $include = shift || "";
   my $flags = `rsync_tmbackup --rsync-get-flags`;
   chomp($flags);
   if ($include ne '') {
       $flags .= " --files-from=$include ";
   }
   return $flags;
}


=head2 prepareRepository

Return a string representing a rsync repository,
an empty string otherwise.

Set also required environment variables.

=cut

sub prepareRepository {
    my $systemname = shift;
    my $record = shift;
    my $name = $record->key;

    my $rsync_repository = "";

    my $VFSType = $record->prop('VFSType') || return '';

    if (-x "/etc/e-smith/events/actions/mount-$VFSType") {
        my $mount = "/mnt/backup-$name";

        my $ret = system("/etc/e-smith/events/actions/mount-$VFSType fake-event $name");
        if ($ret > 0) {
            return "";
        }
        $mount = "$mount/$systemname";

        system("mkdir -p -- \"$mount\"; touch \"$mount/backup.marker\"");
        
        return $mount;
    }
    if ($VFSType eq 'sftp') {
        my $host = $record->prop('SftpHost');
        my $user = $record->prop('SftpUser');
        my $dir = $record->prop('SftpDirectory');
        $rsync_repository = "$user\@$host:$dir";
        system("ssh $user\@$host 'mkdir -p -- \"$dir\"; touch \"$dir/backup.marker\"'"); 
    }

    return $rsync_repository;
}


1;
