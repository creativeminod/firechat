<?php
/*
Plugin Name: Firechat for WordPress
Plugin URI: http://www.deluxeblogtips.com
Description: Real-time chat for WordPress
Version: 1.0.0
Author: Rilwis
Author URI: http://www.deluxeblogtips.com
*/

if ( is_admin() )
{
	define( 'FIRECHAT_DIR', plugin_dir_path( __FILE__ ) );
	define( 'FIRECHAT_URL', plugin_dir_url( __FILE__ ) );

	// Settings page
	if ( ! class_exists( 'FITSSP' ) )
	{
		require_once FIRECHAT_DIR . 'lib/fitwp-simple-settings-page.php';
	}
	require_once FIRECHAT_DIR . 'inc/admin/settings.php';

	// Chat page
	if ( ! class_exists( 'JWT' ) )
	{
		require_once FIRECHAT_DIR . 'lib/JWT.php';
	}
	if ( ! class_exists( 'Services_FirebaseTokenGenerator' ) )
	{
		require_once FIRECHAT_DIR . 'lib/FirebaseToken.php';
	}
	require_once FIRECHAT_DIR . 'inc/admin/chat.php';
	new FireChat_Chat;
}
