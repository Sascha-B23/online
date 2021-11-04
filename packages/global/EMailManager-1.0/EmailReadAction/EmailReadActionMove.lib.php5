<?php

/**
 * Description of EmailReadActionMove
 *
 * @author ngerwien
 */
class EmailReadActionMove implements IEmailReadAction {
	/**
	 * Moves the given email to the given folder
	 * 
	 * @param EmailReader $emailReader
	 * @param EmailReaderEmail $email
	 * @param var $params the destination folder
	 */
	public function Execute(EmailReader $emailReader, EmailReaderEmail $email, $params = null)
	{
		if($params === null)
		{
			$params = FALSE;
		}
		$emailReader->Move($email->msgNumber, $params);
	}
}