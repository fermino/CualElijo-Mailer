# CualElijo-Mailer

## Usage

Just configure config.php, then

To schedule an mail: 

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