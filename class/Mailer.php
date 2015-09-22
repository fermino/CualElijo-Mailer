<?php
	require_once dirname(__FILE__) . '/DBCore.php';
	require_once dirname(__FILE__) . '/SendGrid/sendgrid-php.php';

	class Mailer extends DBCore
	{
		private $SendGridAPIKey = null;

		public function __construct($Hostname, $Database, $Username, $Password, $SendGridAPIKey)
		{
			parent::__construct($Hostname, $Database, $Username, $Password);

			if(!isset($SendGridAPIKey))
				throw new Exception('SendGrid API Key cannot be empty');

			$this->SendGridAPIKey = $SendGridAPIKey;
		}
		public function Schedule($From, $To, $SendIn, $Subject, $Body)
		{
			try
			{
				if($this->IsConnected())
				{
					$From = filter_var($From, FILTER_VALIDATE_EMAIL);
					$To = filter_var($To, FILTER_VALIDATE_EMAIL);

					if($From !== false && strlen($From) <= 255 && $To !== false && strlen($To) <= 255 && !empty($Subject) && strlen($Subject) <= 255 && !empty($Body))
					{
						$SendAt = (new DateTime($SendIn, new DateTimeZone('UTC')))->format('Y-m-d H:i:s');

						$Query = $this->DB->prepare('INSERT INTO `emails`(`from`, `to`, `subject`, `body`, `created`, `send_at`, `sent`) VALUES (?, ?, ?, ?, NOW(), ?, false)');

						if($Query->execute(array($From, $To, $Subject, $Body, $SendAt)) && $Query->rowCount() === 1)
							return true;
					}
				}
			}
			catch(Exception $Exception)
			{
				error_log(sprintf("[%s] %s: %s", (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d h:i:s'), get_class($Exception), $Exception->getMessage()));
			}

			return false;
		}

		public function Cron()
		{
			try
			{
				if($this->IsConnected())
				{
					$Query = $this->DB->prepare('SELECT `id`, `from`, `to`, `subject`, `body` FROM `emails` WHERE `send_at` <= UTC_TIMESTAMP() AND `sent` = 0');

					if($Query->execute())
					{
						$Mails = $Query->fetchAll();

						if(!empty($Mails))
						{
							$SendGrid = new SendGrid($this->SendGridAPIKey, array('raise_exceptions' => false));

							$Sent = array();

							foreach($Mails as $Mail)
							{
								$SendGridEmail = new SendGrid\Email;

								$SendGridEmail
									->setFrom($Mail['from'])
									->addTo($Mail['to'])
									->setSubject($Mail['subject'])
									->setHtml($Mail['body']);

								$Response = $SendGrid->send($SendGridEmail);

								$Sent[$Mail['id']] = $Response->code == 200 ? true : $Response->body;

								$Value = $Response->code == 200 ? '1' : '-1';

								$Query = $this->DB->prepare('UPDATE `emails` SET `sent` = ? WHERE `id` = ?');

								$Query->execute(array($Value, $Mail['id']));
							}

							return $Sent;
						}

						return true;
					}
				}
			}
			catch(Exception $Exception)
			{
				error_log(sprintf("[%s] %s: %s", (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d h:i:s'), get_class($Exception), $Exception->getMessage()));
			}

			return false;
		}
	}