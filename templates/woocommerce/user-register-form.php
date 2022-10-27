<?php

use DSI\TechPub\User\UserLogin;
?>
<?php if (isset($_GET[UserLogin::REGISTER_STATUS_KEY]) && $_GET[UserLogin::REGISTER_STATUS_KEY] == 'pending') : ?>
	<div class="notify">
		<p>We will review your registration request. Once approved, you will receive a notification email. Thank you.</p>
	</div>
<?php endif; ?>