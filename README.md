# CualElijo-Mailer

## Usage

Create a MySQL database and import dbdump.sql, configure config.php with your DB and SendGrid access data, and then

To schedule an mail (tomorrow [1 day], you can change it :P): 

```php
require_once 'class/Mailer.php';
require_once 'config.php';

$Mailer = new Mailer($Hostname, $Database, $Username, $Password, $SendGridAPIKey);

$Mailer->Schedule('from@example.com', 'my-customer@gmail.com', '1 day', 'The subject', 'The HTML body');
```

To send scheduled mails: 

```
php cron.php
```