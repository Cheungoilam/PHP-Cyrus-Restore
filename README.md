# PHP-Cyrus-Restore
A frontend to manage the delayed deleted folders and the delayed expunged mails.
This is a very draft, essential documentation. More details will be written...

## Requisite
You could have many Cyrus-IMAPD servers, each account is LDAP profiled with an LDIF like:

```
dn: uid=name@example.com,o=example.com,c=en
mailAlternateAddress: altname@example.com
objectClass: top
objectClass: person
objectClass: organizationalPerson
uid: name@example.com
mail: name@example.com
mailHost: imap.example.com
```

The domain root is

```
dn: o=example.com,c=en
objectClass: top
objectClass: organization
o: example.com
```

You can have many virtual domains. Each domain have a tree with
`dn: o=<domain>,c=en`.
`imap.example.com` is the Cyrus-IMAPD server where the mailbox `name@example.com` stays.

The attribute `o` is not mandatory. It is defined in the `$attr_ldap_localdom` configuration parameter.

## Install
Clone the repository in a web server.
Clone the *falon-common* repository in your Document Root. This must be install the `include` folder under the Document Root.

Rename config.php_default in config.php and change it at your own.

Otherwise, if you have a RH EL7 like system, install through RPM file. Then configure LDAP and IMAP in config.php.

## How it works
Delayed deleted folders are recovered to their initial path. The `$trashFolder` is omitted from the recovered path. This is useful if the MUA moves the folder to the Trash or similar folder before to delete it. If many folders with the same name are delayed deleted, then the delete date is suffixed to the folder recovered name.

Delayed expunged messages are recovered simply calling the `unexpunge` command on the `mailhost` server. Make sure you can make ssh root connection to those servers. You can setup a certificate authentication.
