<?php

/**
 * Add a chat page into admin area for all users
 */
class FireChat_Chat
{
	/**
	 * Add hooks when class is loaded
	 */
	public function __construct()
	{
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'admin_footer', array( $this, 'show' ) );
	}

	/**
	 * Enqueue script for settings page
	 *
	 * @return void
	 */
	public function enqueue()
	{
		wp_enqueue_style( 'firechat', 'https://cdn.firebase.com/libs/firechat/2.0.1/firechat.min.css', '', '2.0.1' );
		wp_enqueue_style( 'chat', FIRECHAT_URL . 'css/admin/chat.css', '', '1.0.0' );

		wp_register_script( 'firebase', 'https://cdn.firebase.com/js/client/2.0.2/firebase.js', array( 'jquery' ), '2.0.2', true );
		wp_register_script( 'firechat', 'https://cdn.firebase.com/libs/firechat/2.0.1/firechat.min.js', array( 'firebase' ), '2.0.1', true );

		wp_enqueue_script( 'chat', FIRECHAT_URL . 'js/admin/chat.js', array( 'firechat' ), '1.0.0', true );

		$user     = wp_get_current_user();
		$option   = get_option( 'firechat', array( 'url' => '', 'secret' => '' ) );
		$tokenGen = new Services_FirebaseTokenGenerator( $option['secret'] );
		$token    = $tokenGen->createToken( array( 'uid' => 'user-' . $user->ID ), array( 'admin' => current_user_can( 'manage_options' ) ) );
		wp_localize_script( 'chat', 'Chat', array(
			'uid'         => $user->ID,
			'token'       => $token,
			'authError'   => __( 'Error authentication', 'firechat' ),
			'name'        => $user->user_login,
			'firebaseUrl' => $option['url']
		) );
	}

	/**
	 * Show settings page
	 *
	 * @return void
	 */
	public function show()
	{
		?>
		<div id="chat-wrapper">
			<a href="#" id="chat-header">
				<i class="dashicons dashicons-format-status"></i> <?php _e( 'Chat', 'firechat' ); ?>
			</a>
			<div id="firechat-wrapper"></div>
		</div>
		<?php
	}
}
