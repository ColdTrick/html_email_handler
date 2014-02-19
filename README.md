HTML Email Handler
==================

Send out full HTML mails to your users

Contents
--------
1. Features
2. Conflicts

1. Features
-----------
- Send out full HTML notifications to your users (also supported by webmail like GMail)
	- can be toggle in the admin settings
	- to customise it for your own theme overrule the view default/html_email_handler/notification/body.php
- Offers mail function for developers html_email_handler_send_email()
	- see /lib/functions.php for more information
- Offers CSS conversion to inline CSS (needed for webmail support) html_email_handler_css_inliner($html_text)
	- see lib/functions.php for more information

###1.1. Administrators, Developers & Designers
If you have the **[developers][developers_url]** plugin enabled you can easily design the layout of your HTML message, check the Theming sandbox. <br />
Otherwise you can go to [the test url][test_url] to design the layout.

2. Conflicts
------------

As this plugin offers some of the same functionality as other plugins their may be a conflict.
Please check if you have one (or more) of the following

- [phpmailer][phpmailer_url]
- [html_mail][html_mail_url]
- [mail_queue][mail_queue_url]

[developers_url]: /admin/plugins#developers
[test_url]: /html_email_handler/test
[phpmailer_url]: http://community.elgg.org/plugins/384769/1.0/phpmailer
[html_mail_url]: http://community.elgg.org/plugins/566028/v1.0/html-mails
[mail_queue_url]: http://community.elgg.org/plugins/616834/1.1/mail-queue