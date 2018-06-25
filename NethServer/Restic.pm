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
package NethServer::Restic;

use strict;

=head1 NAME

NethServer::Restic -- utility functions for restic.

=cut

=head1 DESCRIPTION

The library can be used to implement restic actions for backup data.

=cut

=head1 USAGE

Usage example:

  use NethServer::Restic;
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

Return a string representing default restic options.

=cut

sub initOptions {
   my $name = shift || return "";

   return " --cache-dir /var/lib/nethserver/backup/restic/$name --password-file /var/lib/nethserver/secrets/restic_$name";
}

=head2 prepareRepository

Return a string representing a trestic repository,
an empty string otherwise.

Set also required environment variables.

=cut

sub prepareRepository {
    my $systemname = shift;
    my $record = shift;
    my $name = $record->key;

    my $restic_repository = "";

    my $VFSType = $record->prop('VFSType') || return '';

    if (-x "/etc/e-smith/events/actions/mount-$VFSType") {
        my $mount;
        if ($name eq 'backup-data') {
           $mount = $record->prop('Mount') || '/mnt/backup';
        } else {
           $mount = "/mnt/backup-$name";
        }
        return $mount;
    }
    if ($VFSType eq 'sftp') {
        my $host = $record->prop('SftpHost');
        my $user = $record->prop('SftpUser');
        my $dir = $record->prop('SftpDirectory');
        $restic_repository = "sftp:$user\@$host:$dir";
    } elsif ($VFSType eq 's3') {
        my $host = $record->prop('S3Host');
        my $access = $record->prop('S3AccessKey');
        my $secret = $record->prop('S3SecretKey');
        my $bucket = $record->prop('S3Bucket');
        $ENV{AWS_ACCESS_KEY_ID} = $access;
        $ENV{AWS_SECRET_ACCESS_KEY} = $secret;
        $restic_repository = "s3:$host/$bucket";
    } elsif ($VFSType eq 'b2') {
        my $account = $record->prop('B2AccountId');
        my $key = $record->prop('B2AccountKey');
        my $bucket = $record->prop('B2Bucket');
        $ENV{B2_ACCOUNT_ID} = $account;
        $ENV{B2_ACCOUNT_KEY} = $key;
        $restic_repository = "b2:$bucket:/$systemname";
    } elsif ($VFSType eq 'rest') {
        my $host = $record->prop('RestHost');
        my $port = $record->prop('RestPort');
        my $user = $record->prop('RestUser') || '';
        my $password = $record->prop('RestPassword') || '';
        my $protocol = $record->prop('RestProtocol');
        my $dir = $record->prop('RestDirectory');
        my $auth = '';
        if ($user ne '' && $password ne '') {
           $auth = "$user:$password@";
        }
        $restic_repository = "rest:$protocol://$auth$host:$port/$dir"
    }

    return $restic_repository;
}


1;
