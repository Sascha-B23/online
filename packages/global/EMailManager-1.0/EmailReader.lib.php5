<?

/**
 * Connect to an email account and perform actions with the emails
 *
 * @author ngerwien
 */

require_once __dir__.'/EmailReadAction/IEmailReadAction.lib.php5';
require_once __dir__.'/EmailReadAction/EmailReadActionMove.lib.php5';
require_once __dir__.'/EmailReadAction/EmailReadActionDelete.lib.php5';
require_once __dir__.'/EmailReaderEmail.lib.php5';

class EmailReader {
    // imap server connection
    public $connection;

    // email login credentials
    private $server = 'yourserver.com';
    private $user   = 'email@yourserver.com';
    private $pass   = 'yourpassword';
    private $port   = 993;
	
	
    /**
	 * Expunge changed messages and close the connection
	 * 
	 * @return bool TRUE on success FALSE on failure
	 */
    public function Close()
	{
		imap_expunge($this->connection);
        return imap_close($this->connection);
    }
	
    
	/**
	 * Connect to an email server. Note: Close() should be called when the connections is not needed anymore.
	 * 
	 * @param string $server
	 * @param int $port
	 * @param string $user
	 * @param string $pass
	 * @param bool $useSSL
	 * @return bool TRUE on success FALSE on failure
	 */
    public function Connect($server, $port, $user, $pass, $mailboxName = "INBOX", $useSSL = true) {
		$this->server = $server;
		$this->port = $port;
		$this->user = $user;
		$this->pass = $pass;
        $this->connection = @imap_open('{'.$this->server.':'.$this->port.($useSSL ? '/ssl' : '').'}'.$mailboxName, $this->user, $this->pass);
		if($this->connection === FALSE)
		{
			//print_r(imap_errors());
			return FALSE;
		}
		
		return TRUE;
    }
	
	/**
	 * delete the message
	 * 
	 * @param int $msg_number
	 * @return bool
	 */
	public function Delete($msg_number, $doExpunge = TRUE)
	{
		imap_delete($this->connection, $msg_number);
		if($doExpunge)
		{
			imap_expunge($this->connection);
		}
		
		return TRUE;
	}
	
	/**
	 * move message to folder
	 * 
	 * @param int $msg_number
	 * @param string $folder
	 */
	public function Move($msg_number, $folder, $doExpunge = TRUE)
	{
		imap_mail_move($this->connection, $msg_number, $folder);
		if($doExpunge)
		{
			imap_expunge($this->connection);
		}
		
		return TRUE;
	}
	
	/**
	 * Returns an array of mailbox names on the connected server
	 * Note: Connect() has to be called before
	 * 
	 * @return array mailboxnames
	 */
	public function GetMailboxList()
	{
		return imap_list($this->connection, '{'.$this->server.':'.$this->port.($useSSL ? '/ssl' : '').'}', "*");
	}
	
    /**
	 * Pull messages from server and cache them
	 * 
	 * @param type $maxMsgCount Pulls only the last few messages in the inbox
	 * @return array Returns an array of Emails
	 */
    public function ReadInbox($maxMsgCount = 0) {		
        $inboxMsgCount = imap_num_msg($this->connection);
        $emails = array();
		
		//get the latest emails
		if($maxMsgCount <= 0)
		{
			$msgCount = 1; //message index '1' is the oldest message which will be read last
		}
		else
		{
			$msgCount = max(1, $inboxMsgCount - $maxMsgCount);
		}
		
		//read the newest messages first (the highest index is the newest message)
        for($i = $inboxMsgCount; $i >= $msgCount; $i--) {
			$email = new EmailReaderEmail();
			$email->msgNumber = $i;
			$email->header = imap_headerinfo($this->connection, $i);
			$email->body = imap_body($this->connection, $i);
			$email->structure = imap_fetchstructure($this->connection, $i);
			$email->numBodyParts = count($email->structure->parts);
			$email->bodyParts = array();
			for($j = 1; $j <= $email->numBodyParts; $j++)
			{
				$email->bodyParts[] = imap_fetchbody($this->connection, $i, $j);
			}
			
            $emails[] = $email;
        }
		
		return $emails;
    }
}
 
?>