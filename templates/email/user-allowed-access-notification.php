<?php

use DSI\TechPub\User\UserLogin;
?>
<p>Hello <?php echo $user->user_login; ?>,</p>

<p>Your access to the tech publication library is now granted. You can now <a href="<?php echo get_site_url() . '/' . UserLogin::MY_ACCOUNT_PAGE; ?>"> Log in </a> and can browse the library contents.</p>