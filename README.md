/config/system.php:

```php
return array(
	'debug'	=> false,
	'host'	=> 'fencing-zp.tk',

	'timezone'	=> 'Europe/Kiev',

	'contacts/phone'	=> '',
	'contacts/skype'	=> '',
	'contacts/email'	=> '',
	'copyrights'	=> 'ЗГДЮЛФ 2013, Все права защищены',


	'l10n@default'	=> 'ru_RU',

	'email/transport'	=> 'file',
	'email@from'	=> 'Site <site@site.com>',
	'email@to'	=> 'manager <site@site.com>',
);
```


/config/db.php:

```php
return array(
	'db@host'	=> 'localhost',
	'db@name'	=> 'fencing',
	'db@user'	=> 'root',
	'db@pass'	=> '',
	'db@charset'	=> 'utf8',
);
```