<?php

class AFCP_Ajax {

	public function __construct() {

		add_action( 'wp_ajax_created_event', [ $this, 'callback' ] );
		add_action( 'wp_ajax_nopriv_created_event', [ $this, 'callback' ] );
	}


	public function callback() {


		check_ajax_referer( 'afcp-ajax-nonce', 'nonce' );

		$this->validation();

		$this->validation_thumbnail();

		$event_data = [
			'post_type'    => 'event',
			'post_status'  => 'publish',
			'post_title'   => sanitize_text_field( $_POST['event_title'] ),
			'post_content' => wp_kses_post( $_POST['event_descriptions'] ),
			'meta_input'   => [
				'event_date'     => sanitize_text_field( $_POST['event_date'] ),
				'event_location' => sanitize_text_field( $_POST['event_location'] ),
			],
			'tax_input'    => [
				'topics'   => $_POST['event_topics'],
				'hashtags' => explode( ',', sanitize_text_field( $_POST['event_hashtags'] ) ),

			],
		];

		//error_log( print_r( $event_data, 1 ) );

		$post_id = wp_insert_post( $event_data );

		$this->set_term( $post_id, $event_data['tax_input'] );

		wp_die();
	}


	public function set_term( $post_id, $data ) {

		foreach ( $data as $key => $value ) {
			wp_set_object_terms( $post_id, $value, $key );
		}
	}


	public function validation() {

		$error = [];

		$required = [
			'event_title'    => 'Это обязательное поле. Укажите заголовок мероприятия',
			'event_topics'   => 'Это обязательное поле. Выберите нужную категорию',
			//'event_hashtags'     => 'Это обязательное поле. Укажите метку в виде хештега, в формате #вашаМетка',
			//'event_descriptions' => 'Это обязательное поле. Напишите о чем, это мероприятие',
			//'event_thumbnail'    => 'Это обязательное поле. Укажите миниатюру мероприятия',
			'event_date'     => 'Это обязательное поле. Укажите дату мероприятия',
			'event_location' => 'Это обязательное поле. Укажите меато проведения мероприятия',
		];

		foreach ( $required as $key => $item ) {

			if ( empty( $_POST[ $key ] ) || ! isset( $_POST[ $key ] ) ) {
				$error[ $key ] = $item;
			}
		}

		if ( $error ) {
			$this->error( $error );
		}
	}


	public function validation_thumbnail() {

		if ( ! empty( $_FILES ) ) {
			$size     = getimagesize( $_FILES['event_thumbnail']['tmp_name'] );
			$max_size = 800;
			$type     = $_FILES['event_thumbnail']['type'];

			if ( $size[0] > $max_size || $size[1] > $max_size ) {
				$image_message = 'Изображение не может быть больше ' . $max_size . 'рх в высоту или ширину';
				$this->remove_image( $image_message );
			}

			if ( 'image/jpeg' !== $type || 'image/png' !== $type ) {

				$image_message = 'Неверный формат файла. Жопускаются только файлы изображений в формате .jpg, .png';
				$this->remove_image( $image_message );
			}
		}

	}


	public function success( $message ) {

		wp_send_json_success(
			[
				'response' => 'SUCCESS',
				'message'  => $message,
			]
		);

	}


	public function error( $message ) {

		wp_send_json_error(
			[
				'response' => 'ERROR',
				'message'  => $message,
			]
		);

	}


	public function remove_image( $image_message ) {

		unlink( $_FILES['event_thumbnail']['tmp_name'] );

		$this->error( $image_message );;
	}
}