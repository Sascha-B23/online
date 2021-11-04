<?php

/**
 * Description of EmailReadActionDelete
 *
 * @author ngerwien
 */
class EmailReadActionDelete implements IEmailReadAction {
	/**
	 * Deletes the given email
	 * 
	 * @param EmailReader $emailReader
	 * @param EmailReaderEmail $email
	 * @param var $params specifies if the email should be expunged immediately or wait until $emailManager->Close()
	 */
	public function Execute(EmailReader $emailReader, EmailReaderEmail $email, $params = null)
	{
		if($params === null)
		{
			$params = FALSE;
		}
		$emailReader->Delete($email->msgNumber, $params);
	}
}