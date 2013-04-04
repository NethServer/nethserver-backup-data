#
# Copyright (C) 2013 Nethesis S.r.l.
# http://www.nethesis.it - support@nethesis.it
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
# along with NethServer.  If not, see <http://www.gnu.org/licenses/>.
#

package NethServer::BackupData;

use strict;
use warnings;
use NethServer::Backup;

use vars qw($VERSION @ISA @EXPORT_OK);

use constant LOG_FILE => "/var/log/backup-data.log";
use constant NOTIFICATION_FILE => "/tmp/backup-data-notification";
use constant CONF_DIR => "/etc/backup-data.d/";


@ISA = qw(NethServer::Backup);

=head1 NAME

NethServer::BackupData - interface backup/restore of data (including configuration)

=head1 SYNOPSIS

    use NethServer::BackupData;
    my $backup = new NethServer::BackupData();


=head1 DESCRIPTION

This module provides an interface to the backup/restore of data (including configuration)

=cut

=head2 new

This is the class constructor which sets the log file.

=cut

sub new
{
    my $class = shift;
    my $notify = shift || 'error';
    my $notify_to = shift || 'root@localhost';
    my $self = {
        _log_file => LOG_FILE,
        _notify => $notify,
        _notify_to => $notify_to,
        _notification_file => NOTIFICATION_FILE,
    };
    $self = bless $self, $class;
    $self->{_log_lines} = $self->_count_log_lines();

    return $self;
}



=head1 AUTHOR

Nethsis srl <support@nethesis.it>

=cut

1;
