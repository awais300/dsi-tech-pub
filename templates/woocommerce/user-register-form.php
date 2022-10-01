<?php

use DSI\TechPub\User\UserLogin;
?>
<?php if (isset($_GET[UserLogin::REGISTER_STATUS_KEY]) && $_GET[UserLogin::REGISTER_STATUS_KEY] == 'pending') : ?>
	<div class="notify">
		<p>Thank you for submitting your request. Your request has been received and will be processed as soon as possible. You will receive an email notification once your request has been approved.</p>
	</div>
<?php endif; ?>