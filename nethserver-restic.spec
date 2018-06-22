%define restic_release 0.9.1

Summary: NethServer backup data using restic
Name: nethserver-restic
Version: 0.0.1
Release: 1%{?dist}
License: GPL
URL: %{url_prefix}/%{name} 
Source0: %{name}-%{version}.tar.gz
Source1: https://github.com/restic/restic/releases/download/v%{restic_release}/restic_%{restic_release}_linux_amd64.bz2
Source2: https://raw.githubusercontent.com/restic/restic/master/LICENSE

Requires: nethserver-backup-data
Requires: sshpass

BuildRequires: perl
BuildRequires: bzip2
BuildRequires: nethserver-devtools 

# Disable debuginfo creation
%define debug_package %{nil}


%description
NethServer backup data using restic

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
bunzip2 -c %{SOURCE1} > %{buildroot}/usr/bin/restic
mv %{SOURCE2} RESTIC-COPYING
echo %{restic_release} > RESTIC-RELEASE
%{genfilelist} --file /usr/bin/restic 'attr(0755,root,root)'  %{buildroot} > %{name}-%{version}-filelist
echo "%doc COPYING" >> %{name}-%{version}-filelist
echo "%doc RESTIC-COPYING" >> %{name}-%{version}-filelist
echo "%doc RESTIC-RELEASE" >> %{name}-%{version}-filelist


%post

%preun

%files -f %{name}-%{version}-filelist
%defattr(-,root,root)
%dir %{_nseventsdir}/%{name}-update

%changelog
