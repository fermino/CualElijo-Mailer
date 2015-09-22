<?php
	require_once dirname(__FILE__) . '/class/Mailer.php';
	require_once dirname(__FILE__) . '/class/Std.php';

	require_once dirname(__FILE__) . '/config.php';

	if(!empty($Hostname) && !empty($Database) && !empty($Username) && !empty($Password) && !empty($SendGridAPIKey))
	{
		$Mailer = new Mailer($Hostname, $Database, $Username, $Password, $SendGridAPIKey);

		if($Mailer->IsConnected())
		{
			$Sent = $Mailer->Cron();

			if(is_array($Sent))
			{
				$SentCount = 0;
				$TotalCount = count($Sent);

				$IDs = array_keys($Sent);

				foreach($IDs as $ID)
					if(!is_array($Sent[$ID]) && $Sent[$ID])
						$SentCount++;

				Std::Out("{$SentCount} / {$TotalCount} sent mails");

				foreach($IDs as $ID)
				{
					if(is_array($Sent[$ID]))
					{
						Std::Out("Error at mail {$ID}: ");

						print_r($Sent[$ID]);
					}
				}
			}
			elseif($Sent === true)
				Std::Out('Nothing to send :P');
			else
				Std::Out('Check your error logs!');
		}
		else
			Std::Out("Can't connect to mysql. Check your error logs and config.php");
	}
	else
		Std::Out('Configuration variables at config.php cannot be empty');