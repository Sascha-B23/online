<?
	$DOMAIN_NAME="DPA";	// Name der Domain für Include Files (z.B. domain.conf.hewal.php5)
	$libDir="/WWW/dienstplanauswertung/phplib/";
	$DONT_REDIRECT_SSL="true";
	$SESSION_DONT_START=true;
	require_once("../phplib/session.inc.php5");
	include("../phplib/helpdesk.lib.php5");
	$offline=true;
	$numNewTickets=0;
	$numReopenedTickets=0;
	$numTakenTickets=0;
	$helpDesk=new Helpdesk();
	$tickets=$helpDesk->GetTickets(null, "New", null, null  );
	if( isset($tickets) && isset($tickets->GetTicketsResult) && isset($tickets->GetTicketsResult->Result) ){
		$offline=false;
		if( isset($tickets->GetTicketsResult->Result->HDTicket) ){
			if( get_class($tickets->GetTicketsResult->Result->HDTicket)=="stdClass" )$numNewTickets++;
			elseif( is_array($tickets->GetTicketsResult->Result->HDTicket) )$numNewTickets+=count($tickets->GetTicketsResult->Result->HDTicket);
		}
	}
	$tickets=$helpDesk->GetTickets(null, "Reopened", null, null  );
	if( isset($tickets) && isset($tickets->GetTicketsResult) && isset($tickets->GetTicketsResult->Result) ){
		$offline=false;
		if( isset($tickets->GetTicketsResult->Result->HDTicket) ){
			if( get_class($tickets->GetTicketsResult->Result->HDTicket)=="stdClass" )$numReopenedTickets++;
			elseif( is_array($tickets->GetTicketsResult->Result->HDTicket) )$numReopenedTickets+=count($tickets->GetTicketsResult->Result->HDTicket);
		}
	}
	$tickets=$helpDesk->GetTickets(null, "Taken", null, null  );
	if( isset($tickets) && isset($tickets->GetTicketsResult) && isset($tickets->GetTicketsResult->Result) ){
		$offline=false;
		if( isset($tickets->GetTicketsResult->Result->HDTicket) ){
			if( get_class($tickets->GetTicketsResult->Result->HDTicket)=="stdClass" )$numTakenTickets++;
			elseif( is_array($tickets->GetTicketsResult->Result->HDTicket) )$numTakenTickets+=count($tickets->GetTicketsResult->Result->HDTicket);
		}
	}
	$tickets=$helpDesk->GetTickets(null, "Delegated", null, null  );
	if( isset($tickets) && isset($tickets->GetTicketsResult) && isset($tickets->GetTicketsResult->Result) ){
		$offline=false;
		if( isset($tickets->GetTicketsResult->Result->HDTicket) ){
			if( get_class($tickets->GetTicketsResult->Result->HDTicket)=="stdClass" )$numTakenTickets++;
			elseif( is_array($tickets->GetTicketsResult->Result->HDTicket) )$numTakenTickets+=count($tickets->GetTicketsResult->Result->HDTicket);
		}
	}	
?>
<table border="0" cellpadding="0" cellspacing="0" width="100%" height="100%">
	<tr>
		<td valign="top" align="left" style="font-size: 10px; color: #FFFFFF; font-family: Arial, Helvetica, sans-serif;">
		<?=date("d.m.Y H:i", time());?> Uhr<br>
		<? if($offline){ ?> 
			<font style="color:#ff4040;">Helpdesk ist offline</font>
		<? }else{?>
			<? if($numNewTickets>0){?><font style="color:#ff4040;"><?}?>
			<?=$numNewTickets;?> neu
			<? if($numNewTickets>0){?></font><?}?><br>
			<? if($numReopenedTickets>0){?><font style="color:#ff4040;"><?}?>
			<?=$numReopenedTickets;?> wiedergeöffnet
			<? if($numReopenedTickets>0){?></font><?}?><br>
			<? if($numTakenTickets>0){?><font style="color:#ff4040;"><?}?>
			<?=$numTakenTickets;?> zugewiesen
			<? if($numTakenTickets>0){?></font><?}?><br>
		<? }?>
		</td>
	</tr>
</table>