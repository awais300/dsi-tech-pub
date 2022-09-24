<?php

use DSI\TechPub\User\UserLogin;
?>
<p>Hello <?php echo $user->user_login; ?>,</p>

<p>Your access to the tech library is now granted. <a href="<?php echo get_site_url() . '/' . UserLogin::MY_ACCOUNT_PAGE; ?>"> Log in </a> and browse the library contents. You could also download tech publications if you are allowed to.</p>