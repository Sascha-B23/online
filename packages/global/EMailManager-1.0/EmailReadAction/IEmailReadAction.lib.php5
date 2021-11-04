<?php

/**
 *
 * @author ngerwien
 */
interface IEmailReadAction {
	/**
	 * Executes an action on the given email with the given email reader
	 * 
	 * @param EmailReader $emailReader
	 * @param EmailReaderEmail $email
	 * @param var $params Generic parameters
	 */
	function Execute(EmailReader $emailReader, EmailReaderEmail $email, $params = null);
}