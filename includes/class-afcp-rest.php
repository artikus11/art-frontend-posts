<?php

class AFCP_Rest {

	public function __construct() {

		add_action( 'rest_api_init', [ $this, 'custom_routes' ] );

	}


	public function custom_routes() {

		register_rest_route(
			'afcp/v1',
			'/add',
			[
				'methods'  => [ 'POST' ],
				'callback' => [ $this, 'callback_rest' ],
			]
		);
	}


	public function callback_rest( WP_REST_Request $request ) {


		$thumbnail = $request->get_file_params( 'event_thumbnail' );

		$this->validation();

		$this->validation_thumbnail( $thumbnail );

		$event_data = [
			'post_type'    => 'event',
			'post_status'  => 'publish',
			'post_title'   => sanitize_text_field( $request->get_param( 'event_title' ) ),
			'post_content' => wp_kses_post( $request->get_param( 'event_descriptions' ) ),
			'meta_input'   => [
				'event_date'     => sanitize_text_field( $request->get_param( 'event_date' ) ),
				'event_location' => sanitize_text_field( $request->get_param( 'event_location' ) ),
			],
			'tax_input'    => [
				'topics'   => $request->get_param( 'event_topics' ),
				'hashtags' => explode( ',', sanitize_text_field( $request->get_param( 'event_hashtags' ) ) ),

			],
		];

		$post_id = wp_insert_post( $event_data );

		$this->upload_thumbnail( $post_id, $thumbnail );

		$this->set_term( $post_id, $event_data['tax_input'] );

		return [
			'message' => 'Событие `' . $post_id . '` успешно создано'
		];
	}


	public function upload_thumbnail( $post_id, $thumbnail ) {

		if ( empty( $thumbnail ) ) {
			return;
		}

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


	public function validation_thumbnail( $file ) {


		if ( ! empty( $file ) ) {
			$size     = getimagesize( $file['event_thumbnail']['tmp_name'] );
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