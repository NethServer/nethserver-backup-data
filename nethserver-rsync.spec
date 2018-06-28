Summary: NethServer backup data using rsync
Name: nethserver-rsync
Version: 0.0.1
Release: 1%{?dist}
License: GPL
URL: %{url_prefix}/%{name} 
Source0: %{name}-%{version}.tar.gz
Source1: https://raw.githubusercontent.com/laurent22/rsync-time-backup/master/rsync_tmbackup.sh
BuildArch: noarch

Requires: nethserver-backup-data

BuildRequires: perl
BuildRequires: nethserver-devtools 


%description
NethServer backup data using rsync

%prep
%setup

%build
perl createlinks

# relocate perl modules under default perl vendorlib directory:
mkdir -p root%{perl_vendorlib}
mv -v NethServer root%{perl_vendorlib}


%install
rm -rf %{buildroot}
(cd root; find . -depth -print | cpio -dump %{buildroot})
mkdir -p %{buildroot}/usr/bin
mv %{SOURCE1} %{buildroot}/usr/bin/rsync_tmbackup
echo %{rsync_release} > RESTIC-RELEASE
%{genfilelist} --file /usr/bin/rsync_tmbackup 'attr(0755,root,root)'  %{buildroot} > %{name}-%{version}-filelist
echo "%doc COPYING" >> %{name}-%{version}-filelist


%post

%preun

%files -f %{name}-%{version}-filelist
%defattr(-,root,root)
%dir %{_nseventsdir}/%{name}-update

%changelog
