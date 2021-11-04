<?
	if (is_a($db, 'DBManagerTracer'))
	{
		$db->PrintDebugOverviewInfo();
	}
?>
</div>
		</div>
		<script type="text/javascript">
			var myHeight;
			function setContentHeight(){
				if( typeof( window.innerWidth ) == 'number' ) {
					//Non-IE
					myHeight = window.innerHeight - 83;
					document.getElementById("div_body_main_content").style.height = myHeight+"px";
					return false;
				} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
					//IE 6+ in 'standards compliant mode'
					myHeight = document.documentElement.clientHeight - 89;
					document.getElementById("div_body_main_content").style.height = myHeight+"px";
					return false;
				} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
					//IE 4 and IE 8 in 'compatibility mode' compatible
					myHeight = document.body.clientHeight - 89;
					document.getElementById("div_body_main_content").style.height = myHeight+"px";
					return false;
				}
			}
		</script>
	</body>
</html>