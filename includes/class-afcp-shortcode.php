<?php

class AFCP_Shortcode {

	public function __construct() {

		add_shortcode( 'afcp_form', [ $this, 'shortcode_form' ] );
	}


	public function shortcode_form() {

		wp_enqueue_script( 'afcp-script' );
		//wp_enqueue_script( 'afcp-script-ajax' );
		wp_enqueue_script( 'afcp-script-rest' );
		wp_enqueue_script( 'jquery-form' );
		wp_enqueue_style( 'afcp-styles' );
		wp_enqueue_script( 'afcp-select2-script' );
		wp_enqueue_style( 'afcp-select2-style' );

		ob_start();
		?>
		<form action="POST" id="event-form" class="event-form">

			<?php
			foreach ( $this->fields() as $key => $value ):
				$this->fields_form( $key, $value );
			endforeach;
			?>

			<button
				type="submit"
				class="button submit-event"
				name="send_event">
				Добавить мероприятие
			</button>

		</form>
		<?php
		return ob_get_clean();
	}


	public function fields() {

		return apply_filters(
			'afcp_fields',
			[
				'event_title'        => [
					'type'        => 'text',
					'label'       => 'Заголовок мероприятия',
					'required'    => true,
					'description' => 'Это обязательное поле. Укажите заголовок мероприятия',
				],
				'event_topics'       => [
					'type'        => 'multiselect',
					'label'       => 'Категория мероприятия',
					'input_class' => [ 'js-multiselect' ],
					'options'     => $this->get_field_terms(),
					'required'    => true,
					'description' => 'Выберите нужную категорию',
				],
				'event_hashtags'     => [
					'type'        => 'text',
					'label'       => 'Метки мероприятия',
					'description' => 'Укажите метку в виде хештега, в формате #вашаМетка',
				],
				'event_descriptions' => [
					'type'              => 'wysiwyg_editor',
					'label'             => 'Описание мероприятия',
					'description'       => 'Напишите о чем, это мероприятие',
					'custom_attributes' => [
						'wpautop'          => 1,
						'media_buttons'    => 0,
						'textarea_rows'    => 3,
						'tabindex'         => 0,
						'editor_css'       => '<style></style>',
						'editor_class'     => 'form-field',
						'teeny'            => 1,
						'dfw'              => 0,
						'tinymce'          => 1,
						'quicktags'        => 0,
						'drag_drop_upload' => 0,
					],
					'value'             => '',
				],
				'event_thumbnail'    => [
					'type'  => 'file',
					'label' => 'Миниатюра мероприятия',
				],
				'event_date'         => [
					'type'     => 'datepicker',
					'label'    => 'Дата мероприятия',
					'required' => true,
				],
				'event_location'     => [
					'type'     => 'text',
					'label'    => 'Место мероприятия',
					'required' => true,
				],

			]
		);

	}


	public function fields_form( $key, $args, $value = null ) {

		$defaults = array(
			'type'              => 'text',
			'label'             => '',
			'description'       => '',
			'placeholder'       => '',
			'maxlength'         => false,
			'required'          => false,
			'autocomplete'      => false,
			'id'                => $key,
			'class'             => array(),
			'label_class'       => array(),
			'input_class'       => array(),
			'return'            => false,
			'options'           => array(),
			'custom_attributes' => array(),
			'validate'          => array(),
			'default'           => '',
			'autofocus'         => '',
			'priority'          => '',
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( 'afp_form_field_args', $args, $key, $value );

		if ( $args['required'] ) {
			$args['class'][] = 'validate-required';
			$required        = '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'afp' ) . '">*</abbr>';
		} else {
			$required = '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'afp' ) . ')</span>';
		}

		if ( is_string( $args['label_class'] ) ) {
			$args['label_class'] = array( $args['label_class'] );
		}

		if ( is_null( $value ) ) {
			$value = $args['default'];
		}

		// Custom attribute handling.
		$custom_attributes         = array();
		$args['custom_attributes'] = array_filter( (array) $args['custom_attributes'], 'strlen' );

		if ( $args['maxlength'] ) {
			$args['custom_attributes']['maxlength'] = absint( $args['maxlength'] );
		}

		if ( ! empty( $args['autocomplete'] ) ) {
			$args['custom_attributes']['autocomplete'] = $args['autocomplete'];
		}

		if ( true === $args['autofocus'] ) {
			$args['custom_attributes']['autofocus'] = 'autofocus';
		}

		if ( $args['description'] ) {
			$args['custom_attributes']['aria-describedby'] = $args['id'] . '-description';
		}

		if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
			foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
				$custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
			}
		}

		if ( ! empty( $args['validate'] ) ) {
			foreach ( $args['validate'] as $validate ) {
				$args['class'][] = 'validate-' . $validate;
			}
		}

		$field           = '';
		$label_id        = $args['id'];
		$sort            = $args['priority'] ? $args['priority'] : '';
		$field_container = '<p class="form-row %1$s" id="%2$s" data-priority="' . esc_attr( $sort ) . '">%3$s</p>';

		switch ( $args['type'] ) {
			case 'textarea':
				$field .= '<textarea name="' . esc_attr( $key ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" id="' . esc_attr( $args['id'] ) .
				          '" placeholder="' . esc_attr( $args['placeholder'] ) . '" ' . ( empty( $args['custom_attributes']['rows'] ) ? ' rows="2"' : '' ) .
				          ( empty( $args['custom_attributes']['cols'] ) ? ' cols="5"' : '' ) . implode( ' ', $custom_attributes ) . '>' . esc_textarea( $value ) . '</textarea>';

				break;
			case 'checkbox':
				$field = '<label class="checkbox ' . implode( ' ', $args['label_class'] ) . '" ' . implode( ' ', $custom_attributes ) . '>
						<input type="' . esc_attr( $args['type'] ) . '" class="input-checkbox ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) .
				         '" id="' . esc_attr( $args['id'] ) . '" value="1" ' . checked( $value, 1, false ) . ' /> ' . $args['label'] . $required . '</label>';

				break;
			case 'text':
			case 'password':
			case 'datetime':
			case 'datetime-local':
			case 'date':
			case 'month':
			case 'time':
			case 'week':
			case 'number':
			case 'email':
			case 'url':
			case 'file':
			case 'tel':
				$field .= '<input type="' . esc_attr( $args['type'] ) . '" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) .
				          '" id="' . esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' .
				          implode( ' ', $custom_attributes ) . ' />';

				break;
			case 'datepicker':
				wp_enqueue_script( 'jquery-ui-datepicker' );
				wp_enqueue_style( 'jqueryui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css', false, null );

				$field .= '<input type="text" class="datepicker ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" name="' . esc_attr( $key ) . '" id="' .
				          esc_attr( $args['id'] ) . '" placeholder="' . esc_attr( $args['placeholder'] ) . '"  value="' . esc_attr( $value ) . '" ' .
				          implode( ' ', $custom_attributes ) . ' />';

				break;
			case 'select':
				$field   = '';
				$options = '';

				if ( ! empty( $args['options'] ) ) {
					foreach ( $args['options'] as $option_key => $option_text ) {
						if ( '' === $option_key ) {
							// If we have a blank option, select2 needs a placeholder.
							if ( empty( $args['placeholder'] ) ) {
								$args['placeholder'] = $option_text ? $option_text : __( 'Choose an option', 'afp' );
							}
							$custom_attributes[] = 'data-allow_clear="true"';
						}
						$options .= '<option value="' . esc_attr( $option_key ) . '" ' . selected( $value, $option_key, false ) . '>' . esc_attr( $option_text ) . '</option>';
					}

					$field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="select ' . esc_attr( implode( ' ', $args['input_class'] ) ) .
					          '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ) . '">
							' . $options . '
						</select>';
				}

				break;
			case 'multiselect':
				$field   = '';
				$options = '';

				if ( ! empty( $args['options'] ) ) {
					foreach ( $args['options'] as $option_key => $option_text ) {
						if ( '' === $option_key ) {
							if ( empty( $args['placeholder'] ) ) {
								$args['placeholder'] = $option_text ? $option_text : __( 'Choose an option', 'afp' );
							}
							$custom_attributes[] = 'data-allow_clear="true"';
						}
						$options .= '<option value="' . esc_attr( $option_key ) . '" ' . selected( $value, $option_key, false ) . '>' . esc_attr( $option_text ) . '</option>';
					}

					$field .= '<select multiple name="' . esc_attr( $key ) . '[]" id="' . esc_attr( $args['id'] ) . '" class="multiselect ' .
					          esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' .
					          esc_attr( $args['placeholder'] ) . '">
							' . $options . '
						</select>';
				}

				break;
			case 'radio':
				$label_id .= '_' . current( array_keys( $args['options'] ) );

				if ( ! empty( $args['options'] ) ) {
					foreach ( $args['options'] as $option_key => $option_text ) {
						$field .= '<input type="radio" class="input-radio ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $option_key ) .
						          '" name="' . esc_attr( $key ) . '" ' . implode( ' ', $custom_attributes ) . ' id="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) .
						          '"' . checked( $value, $option_key, false ) . ' />';
						$field .= '<label for="' . esc_attr( $args['id'] ) . '_' . esc_attr( $option_key ) . '" class="radio ' . implode( ' ', $args['label_class'] ) . '">' .
						          $option_text . '</label>';
					}
				}

				break;
			case 'wysiwyg_editor' :
				wp_localize_script(
					'afcp-script',
					'field_editor',
					[
						'key' => esc_attr( $key ),
					]
				);

				ob_start();
				wp_editor(
					esc_textarea( $value ),
					esc_attr( $key ),
					[
						'wpautop'          => $args['custom_attributes']['wpautop'],
						'media_buttons'    => $args['custom_attributes']['media_buttons'],
						'textarea_name'    => $key,
						'textarea_rows'    => $args['custom_attributes']['textarea_rows'],
						'tabindex'         => $args['custom_attributes']['tabindex'],
						'editor_css'       => $args['custom_attributes']['editor_css'],
						'editor_class'     => $args['custom_attributes']['editor_class'],
						'teeny'            => $args['custom_attributes']['teeny'],
						'dfw'              => $args['custom_attributes']['dfw'],
						'tinymce'          => $args['custom_attributes']['tinymce'],
						'quicktags'        => $args['custom_attributes']['quicktags'],
						'drag_drop_upload' => $args['custom_attributes']['drag_drop_upload'],
					]
				);
				$editor = ob_get_clean();

				?>
				<div
					class="<?php echo esc_attr( implode( ' ', $args['class'] ) ); ?>"
					id="<?php echo esc_attr( $args['id'] ) . '_field'; ?>"
					style="margin: 0 0 20px;">
					<?php if ( ! empty( $args['label'] ) ) : ?>
						<label>
							<?php echo $args['label']; ?>
							<?php if ( ! empty( $args['required'] ) ) : ?>
								<abbr class="required" title="Обязательное">*</abbr>
							<?php endif; ?>
						</label>
					<?php endif; ?>
					<?php echo $editor; ?>

				</div>

				<?php
				break;
		}

		if ( ! empty( $field ) ) {
			$field_html = '';

			if ( $args['label'] && 'checkbox' !== $args['type'] ) {
				$field_html .= '<label for="' . esc_attr( $label_id ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . $args['label'] . $required .
				               '</label>';
			}

			$field_html .= '<span class="afp-input-wrapper">' . $field;

			if ( $args['description'] ) {
				$field_html .= '<span class="description" id="' . esc_attr( $args['id'] ) . '-description" aria-hidden="true">' . wp_kses_post( $args['description'] ) . '</span>';
			}

			$field_html .= '</span>';

			$container_class = esc_attr( implode( ' ', $args['class'] ) );
			$container_id    = esc_attr( $args['id'] ) . '_field';
			$field           = sprintf( $field_container, $container_class, $container_id, $field_html );
		}

		if ( ! $args['return'] ) {
			echo $field; // WPCS: XSS ok.
		}

		return $field;
	}


	public function get_field_terms() {

		$terms = get_terms(
			[
				'taxonomy'   => [ 'topics' ],
				'orderby'    => 'id',
				'order'      => 'ASC',
				'hide_empty' => false,
			]
		);

		$field_terms = [];

		foreach ( $terms as $term ) {
			$field_terms[ $term->slug ] = $term->name;
		}

		return $field_terms;
	}
}
