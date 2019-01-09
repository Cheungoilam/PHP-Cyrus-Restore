%global systemd (0%{?fedora} >= 18) || (0%{?rhel} >= 7)
%global bigname PHP-Cyrus-Restore

Summary: A management tool for delayed deleted folders and delayed expunged mails.
Name: CyrusRestore
Version: 0.1.1
Release: 1%{?dist}
Group: Applications/Communications
License: Apache-2.0
URL: https://falon.github.io/%{bigname}/
Source0: https://github.com/falon/%{bigname}/archive/master.zip
BuildArch:      noarch

# Required for all versions
Requires: httpd >= 2.4.6
Requires: mod_ssl >= 2.4.6
Requires: php >= 7.1
Requires: php-imap >= 7.1
Requires: php-ldap >= 7.1
Requires: FalonCommon >= 0.1.2

%description
%{bigname}
A frontend to manage the delayed deleted folders
and the delayed expunged mails.

%clean
rm -rf %{buildroot}/

%prep
%autosetup -n %{bigname}-master


%install

rm -rf rpm

# Web HTTPD conf

install -D -m0444 %{bigname}.conf-default %{buildroot}%{_sysconfdir}/httpd/conf.d/%{bigname}.conf
sed -i 's|\/var\/www\/html\/include|%{_datadir}/include|' %{buildroot}%{_sysconfdir}/httpd/conf.d/%{bigname}.conf
sed -i 's|\/var\/www\/html\/%{bigname}|%{_datadir}/%{bigname}|' %{buildroot}%{_sysconfdir}/httpd/conf.d/%{bigname}.conf
rm %{bigname}.conf-default

# Cyrus Restore application files
mkdir -p %{buildroot}%{_datadir}/%{bigname}
cp -a * %{buildroot}%{_datadir}/%{bigname}/
mv %{buildroot}%{_datadir}/%{bigname}/config.php_default %{buildroot}%{_datadir}/%{bigname}/config.php
## Remove unnecessary files
rm -rf %{buildroot}%{_datadir}/%{bigname}/_config.yml %{buildroot}%{_datadir}/.gitignore %{buildroot}%{_datadir}/rpm

##File list
find %{buildroot}%{_datadir}/%{bigname} -mindepth 1 -type f | grep -v LICENSE$ | grep -v \.md$ | grep -v config\.php$ | grep -v \.git | grep -v '\_default$' | sed -e "s@$RPM_BUILD_ROOT@@" > FILELIST

%post
case "$1" in
  1)
        echo -en "\n\n\e[33mRemember to setup the ssh connection with each IMAP server.\nPlease, modify the file\n\t%{_sysconfdir}/httpd/conf.d/%{bigname}.conf\nand the file\n\t%{_datadir}/%{bigname}/config.php\nat your need. Enjoy!\e[39m\n\n"
  ;;
esac


%files -f FILELIST
%license %{_datadir}/%{bigname}/LICENSE
%doc %{_datadir}/%{bigname}/README.md
%config(noreplace) %{_datadir}/%{bigname}/config.php
%config(noreplace) %{_sysconfdir}/httpd/conf.d/%{bigname}.conf

%changelog
* Wed Jan 09 2019 Marco Favero <marco.favero@csi.it> 0.1.1-1
- Added facility to map the [@] key in [Tab] key in form navigation

* Thu Jan 03 2019 Marco Favero <marco.favero@csi.it> 0.1.0-1
- Initial build version
