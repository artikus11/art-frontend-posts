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

		$post_id = wp_insert_post( $event_data );

		$this->upload_thumbnail( $post_id );

		$this->set_term( $post_id, $event_data['tax_input'] );

		$this->success( 'Событие `' . $post_id . '` успешно создано' );

		wp_die();
	}


	public function upload_thumbnail( $post_id ) {

		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		add_filter(
			'upload_mimes',
			function ( $mimes ) {

				return [
					'jpg|jpeg|jpe' => 'image/jpeg',
					'png'          => 'image/png',
				];
			}
		);

		$attachment_id = media_handle_upload( 'event_thumbnail', $post_id );

		if ( is_wp_error( $attachment_id ) ) {
			$response_message = 'Ошибка загрузки файла `' . $_FILES['event_thumbnail']['name'] . '`: ' . $attachment_id->get_error_message();
			$this->error( $response_message );
		}

		set_post_thumbnail( $post_id, $attachment_id );

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

			if ( $size[0] > $max_size || $size[1] > $max_size ) {
				$image_message = 'Изображение не может быть больше ' . $max_size . 'рх в высоту или ширину';
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