<?php
	abstract class DBCore
	{
		protected $DB = null;

		private $Connected = false;
		
		public function __construct($Hostname, $Database, $Username, $Password)
		{
			try
			{
				$this->DB = new PDO("mysql:host={$Hostname};dbname={$Database}", $Username, $Password);

				$this->Connected = true;
			}
			catch(Exception $Exception)
			{
				error_log(sprintf("[%s] %s: %s", (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d h:i:s'), get_class($Exception), $Exception->getMessage()));
			}
		}

		public final function IsConnected()
		{ return $this->Connected; }
	}