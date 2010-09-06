<table width="675" border="0" align="center" cellpadding="8">
	<tr>
		<td colspan="2"><img src="<?php echo PA::$url .'/images/civic-commons-logo.jpg';?>" width="314" height="65" /></td>
	</tr>
	<tr>
		<td colspan="2">
			<table style="width:650px;">
				<tr>
					<td>
						<strong><font color="#89b7cd" face="Arial, Helvetica, sans-serif" size="5">
							<?php echo $subject;?>
						</font></strong>
					</td>  
				</tr>
			</table>
		</td>
	</tr>   
	<tr>
		<td valign="top" width="150"><img src="<?php echo PA::$url .'/images/people-blue.jpg';?>" width="150" height="268" /></td>
		<td width="525">
			<?php echo $message;?>
			<br />
			<br />
			Everyone at <?= PA::$site_name ?> respects your privacy. 
			Your information will never be shared with third parties unless specifically requested by you.
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<div style="width: auto; margin: 0px 0px 5px 0px; border-top:#FF9900 1px dashed"></div>
			<center>
				<font face="Arial, Helvetica, sans-serif" size="1" color="#4d748d">This email was sent by the Civic Commons &copy; 2010 Platform</font>
				<br />
				<br />
				<a href="http://creativecommons.org/"><img src="<?php echo PA::$url .'/images/cc.srr.primary.gif';?>" alt="Creative Commons" width="75" height="25" border="0" /></a>
				<br />
			</center>
		</td>
	</tr>
</table>
