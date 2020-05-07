<?php

class AFCP_Shortcode {

	public function __construct() {

		add_shortcode( 'afcp_form', [ $this, 'shortcode_form' ] );
	}


	public function shortcode_form() {

		ob_start();
		?>
		Это форма добавления мероприятий
		<?php
		return ob_get_clean();
	}
}
