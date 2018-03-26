<?php
/**
 * File for the QMNQuizManager class
 *
 * @package QSM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class generates the contents of the quiz shortcode
 *
 * @since 4.0.0
 */
class QMNQuizManager {

	/**
	 * Main Construct Function
	 *
	 * Call functions within class
	 *
	 * @since 4.0.0
	 * @uses QMNQuizManager::add_hooks() Adds actions to hooks and filters
	 * @return void
	 */
	public function __construct() {
		$this->add_hooks();
	}

	/**
	 * Add Hooks
	 *
	 * Adds functions to relavent hooks and filters
	 *
	 * @since 4.0.0
	 * @return void
	 */
	public function add_hooks() {
		add_shortcode( 'mlw_quizmaster', array( $this, 'display_shortcode' ) );
		add_shortcode( 'qsm', array( $this, 'display_shortcode' ) );
		add_action( 'wp_ajax_qmn_process_quiz', array( $this, 'ajax_submit_results' ) );
		add_action( 'wp_ajax_nopriv_qmn_process_quiz', array( $this, 'ajax_submit_results' ) );
	}

	/**
	 * Generates Content For Quiz Shortcode
	 *
	 * Generates the content for the [mlw_quizmaster] shortcode
	 *
	 * @since 4.0.0
	 * @param array $atts The attributes passed from the shortcode.
	 * @uses QMNQuizManager:load_questions() Loads questions
	 * @uses QMNQuizManager:create_answer_array() Prepares answers
	 * @uses QMNQuizManager:display_quiz() Generates and prepares quiz page
	 * @uses QMNQuizManager:display_results() Generates and prepares results page
	 * @return string The content for the shortcode
	 */
	public function display_shortcode( $atts ) {
		extract(shortcode_atts(array(
			'quiz'            => 0,
			'question_amount' => 0,
		), $atts));

		ob_start();

		global $wpdb;
		global $mlwQuizMasterNext;
		global $qmn_allowed_visit;
		global $qmn_json_data;
		$qmn_json_data = array();
		$qmn_allowed_visit = true;
		$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz );
		$question_amount = intval( $question_amount );

		// Legacy variable.
		global $mlw_qmn_quiz;
		$mlw_qmn_quiz = $quiz;

		$return_display = '';
		$qmn_quiz_options = $mlwQuizMasterNext->quiz_settings->get_quiz_options();

		// If quiz options isn't found, stop function.
		if ( is_null( $qmn_quiz_options ) || empty( $qmn_quiz_options->quiz_name ) ) {
			return __( 'It appears that this quiz is not set up correctly', 'quiz-master-next' );
		}

		// Loads Quiz Template.
		// The quiz_stye is misspelled because it has always been misspelled and fixing it would break many sites :(.
		if ( 'default' == $qmn_quiz_options->theme_selected ) {
			$return_display .= '<style type="text/css">' . $qmn_quiz_options->quiz_stye . '</style>';
			wp_enqueue_style( 'qmn_quiz_style', plugins_url( '../../css/qmn_quiz.css', __FILE__ ) );
		} else {
			$registered_template = $mlwQuizMasterNext->pluginHelper->get_quiz_templates( $qmn_quiz_options->theme_selected );
			// Check direct file first, then check templates folder in plugin, then check templates file in theme.
			// If all fails, then load custom styling instead.
			if ( $registered_template && file_exists( $registered_template['path'] ) ) {
				wp_enqueue_style( 'qmn_quiz_template', $registered_template['path'], array(), $mlwQuizMasterNext->version );
			} elseif ( $registered_template && file_exists( plugin_dir_path( __FILE__ ) . '../../templates/' . $registered_template['path'] ) ) {
				wp_enqueue_style( 'qmn_quiz_template', plugins_url( '../../templates/' . $registered_template['path'], __FILE__ ), array(), $mlwQuizMasterNext->version );
			} elseif ( $registered_template && file_exists( get_stylesheet_directory_uri() . '/templates/' . $registered_template['path'] ) ) {
				wp_enqueue_style( 'qmn_quiz_template', get_stylesheet_directory_uri() . '/templates/' . $registered_template['path'], array(), $mlwQuizMasterNext->version );
			} else {
				echo "<style type='text/css'>{$qmn_quiz_options->quiz_stye}</style>";
			}
		}

		// Start to prepare variable array for filters.
		$qmn_array_for_variables = array(
			'quiz_id'     => $qmn_quiz_options->quiz_id,
			'quiz_name'   => $qmn_quiz_options->quiz_name,
			'quiz_system' => $qmn_quiz_options->system,
		);

		if ( $_SERVER['REMOTE_ADDR'] ) {
			$qmn_array_for_variables['user_ip'] = $_SERVER['REMOTE_ADDR'];
		} else {
			$qmn_array_for_variables['user_ip'] = 'Unknown';
		}

		$return_display .= "<script>
			if (window.qmn_quiz_data === undefined) {
				window.qmn_quiz_data = new Object();
			}
		</script>";
		$qmn_json_data = array(
			'quiz_id'           => $qmn_array_for_variables['quiz_id'],
			'quiz_name'         => $qmn_array_for_variables['quiz_name'],
			'disable_answer'    => $qmn_quiz_options->disable_answer_onselect,
			'ajax_show_correct' => $qmn_quiz_options->ajax_show_correct,
		);

		$return_display = apply_filters( 'qmn_begin_shortcode', $return_display, $qmn_quiz_options, $qmn_array_for_variables );

		// Check if we should be showing quiz or results page.
		if ( $qmn_allowed_visit && ! isset( $_POST["complete_quiz"] ) && ! empty( $qmn_quiz_options->quiz_name ) ) {
			$return_display .= $this->display_quiz( $qmn_quiz_options, $qmn_array_for_variables, $question_amount );
		} elseif ( isset( $_POST["complete_quiz"] ) && 'confirmation' == $_POST["complete_quiz"] && $_POST["qmn_quiz_id"] == $qmn_array_for_variables["quiz_id"] ) {
			$qmn_quiz_questions = $this->load_questions( $quiz, $qmn_quiz_options, false );
			$qmn_quiz_answers = $this->create_answer_array( $qmn_quiz_questions );
			$return_display .= $this->display_results( $qmn_quiz_options, $qmn_quiz_questions, $qmn_quiz_answers, $qmn_array_for_variables );
		}

		$qmn_filtered_json = apply_filters( 'qmn_json_data', $qmn_json_data, $qmn_quiz_options, $qmn_array_for_variables );

		$return_display .= '<script>
			window.qmn_quiz_data["' . $qmn_json_data["quiz_id"] . '"] = ' . json_encode( $qmn_json_data ) . '
		</script>';

		$return_display .= ob_get_clean();
		$return_display = apply_filters( 'qmn_end_shortcode', $return_display, $qmn_quiz_options, $qmn_array_for_variables );
		return $return_display;
	}

	/**
	 * Loads Questions
	 *
	 * Retrieves the questions from the database
	 *
	 * @since 4.0.0
	 * @param int   $quiz_id The id for the quiz.
	 * @param array $quiz_options The database row for the quiz.
	 * @param bool  $is_quiz_page If the page being loaded is the quiz page or not.
	 * @param int   $question_amount The amount of questions entered using the shortcode attribute.
	 * @return array The questions for the quiz
	 * @deprecated 5.2.0 Use new class: QSM_Questions instead
	 */
	public function load_questions( $quiz_id, $quiz_options, $is_quiz_page, $question_amount = 0 ) {

		// Prepare variables.
		global $wpdb;
		global $mlwQuizMasterNext;
		$questions = array();
		$order_by_sql = 'ORDER BY question_order ASC';
		$limit_sql = '';

		// Checks if the questions should be randomized.
		if ( 1 == $quiz_options->randomness_order || 2 == $quiz_options->randomness_order ) {
			$order_by_sql = 'ORDER BY rand()';
		}

		// Check if we should load all questions or only a selcted amount.
		if ( $is_quiz_page && ( 0 != $quiz_options->question_from_total || 0 !== $question_amount ) ) {
			if ( 0 !== $question_amount ) {
				$limit_sql = " LIMIT $question_amount";
			} else {
				$limit_sql = ' LIMIT ' . intval( $quiz_options->question_from_total );
			}
		}

		// If using newer pages system from 5.2.
		$pages = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'pages', array() );
		// Get all question IDs needed.
		$total_pages = count( $pages );
		if ( $total_pages > 0 ) {
			for ( $i = 0; $i < $total_pages; $i++ ) {
				foreach ( $pages[ $i ] as $question ) {
					$question_ids[] = intval( $question );
				}
			}
			$question_sql = implode( ', ', $question_ids );
			$questions = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "mlw_questions WHERE question_id IN ($question_sql) " . $order_by_sql . $limit_sql );
		} else {
			$questions = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "mlw_questions WHERE quiz_id=%d AND deleted=0 " . $order_by_sql . $limit_sql, $quiz_id ) );
		}

		// Returns an array of all the loaded questions.
		return $questions;
	}

	/**
	 * Prepares Answers
	 *
	 * Prepares or creates the answer array for the quiz
	 *
	 * @since 4.0.0
	 * @param array $questions The questions for the quiz.
	 * @param bool  $is_ajax Pass true if this is an ajax call.
	 * @return array The answers for the quiz
	 * @deprecated 5.2.0 Use new class: QSM_Questions instead
	 */
	public function create_answer_array( $questions, $is_ajax = false ) {

		// Load and prepare answer arrays.
		$mlw_qmn_answer_arrays = array();
		$question_list = array();
		foreach ( $questions as $mlw_question_info ) {
			$question_list[ $mlw_question_info->question_id ] = get_object_vars( $mlw_question_info );
			if ( is_serialized( $mlw_question_info->answer_array ) && is_array( @unserialize( $mlw_question_info->answer_array ) ) ) {
				$mlw_qmn_answer_array_each = @unserialize( $mlw_question_info->answer_array );
				$mlw_qmn_answer_arrays[ $mlw_question_info->question_id ] = $mlw_qmn_answer_array_each;
				$question_list[ $mlw_question_info->question_id ]["answers"] = $mlw_qmn_answer_array_each;
			} else {
				$mlw_answer_array_correct = array(0, 0, 0, 0, 0, 0);
				$mlw_answer_array_correct[$mlw_question_info->correct_answer-1] = 1;
				$mlw_qmn_answer_arrays[$mlw_question_info->question_id] = array(
					array($mlw_question_info->answer_one, $mlw_question_info->answer_one_points, $mlw_answer_array_correct[0]),
					array($mlw_question_info->answer_two, $mlw_question_info->answer_two_points, $mlw_answer_array_correct[1]),
					array($mlw_question_info->answer_three, $mlw_question_info->answer_three_points, $mlw_answer_array_correct[2]),
					array($mlw_question_info->answer_four, $mlw_question_info->answer_four_points, $mlw_answer_array_correct[3]),
					array($mlw_question_info->answer_five, $mlw_question_info->answer_five_points, $mlw_answer_array_correct[4]),
					array($mlw_question_info->answer_six, $mlw_question_info->answer_six_points, $mlw_answer_array_correct[5]));
					$question_list[$mlw_question_info->question_id]["answers"] = $mlw_qmn_answer_arrays[$mlw_question_info->question_id];
			}
		}
		if ( ! $is_ajax ) {
			global $qmn_json_data;
			$qmn_json_data["question_list"] = $question_list;
		}
		return $mlw_qmn_answer_arrays;
	}

	/**
	 * Generates Content Quiz Page
	 *
	 * Generates the content for the quiz page part of the shortcode
	 *
	 * @since 4.0.0
	 * @param array $options The database row of the quiz.
	 * @param array $quiz_data The array of results for the quiz.
	 * @param int $question_amount The number of questions to load for quiz.
	 * @uses QMNQuizManager:display_begin_section() Creates display for beginning section
	 * @uses QMNQuizManager:display_questions() Creates display for questions
	 * @uses QMNQuizManager:display_comment_section() Creates display for comment section
	 * @uses QMNQuizManager:display_end_section() Creates display for end section
	 * @return string The content for the quiz page section
	 */
	public function display_quiz( $options, $quiz_data, $question_amount ) {

		global $qmn_allowed_visit;
		global $mlwQuizMasterNext;
		$quiz_display = '';
		$quiz_display = apply_filters( 'qmn_begin_quiz', $quiz_display, $options, $quiz_data );
		if ( ! $qmn_allowed_visit ) {
			return $quiz_display;
		}
		wp_enqueue_script( 'json2' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-tooltip' );
		wp_enqueue_style( 'jquery-redmond-theme', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/redmond/jquery-ui.css' );

		global $qmn_json_data;
		$qmn_json_data['error_messages'] = array(
			'email' => $options->email_error_text,
			'number' => $options->number_error_text,
			'incorrect' => $options->incorrect_error_text,
			'empty' => $options->empty_error_text,
		);

		wp_enqueue_script( 'qmn_quiz', plugins_url( '../../js/qmn_quiz.js', __FILE__ ), array( 'wp-util', 'underscore', 'jquery', 'jquery-ui-tooltip' ), $mlwQuizMasterNext->version );
		wp_localize_script( 'qmn_quiz', 'qmn_ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) ); // setting ajaxurl
		wp_enqueue_script( 'math_jax', '//cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.2/MathJax.js?config=TeX-MML-AM_CHTML' );

		global $qmn_total_questions;
		$qmn_total_questions = 0;
		global $mlw_qmn_section_count;
		$mlw_qmn_section_count = 0;

		$quiz_display .= "<div class='qmn_quiz_container mlw_qmn_quiz'>";
		$quiz_display .= "<form name='quizForm{$quiz_data['quiz_id']}' id='quizForm{$quiz_data['quiz_id']}' action='' method='post' class='qsm-quiz-form qmn_quiz_form mlw_quiz_form' novalidate >";
		$quiz_display .= "<div name='mlw_error_message' id='mlw_error_message' class='qsm-error-message qmn_error_message_section'></div>";
		$quiz_display .= "<span id='mlw_top_of_quiz'></span>";
		$quiz_display = apply_filters( 'qmn_begin_quiz_form', $quiz_display, $options, $quiz_data );

		// If deprecated pagination setting is not used, use new system...
		$pages = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'pages', array() );
		if ( 0 == $options->randomness_order && 0 == $options->question_from_total && 0 == $options->pagination && 0 !== count( $pages ) ) {
			$quiz_display .= $this->display_pages( $options, $quiz_data );
		} else {
			// ... else, use older system.
			$questions = $this->load_questions( $quiz_data['quiz_id'], $options, true, $question_amount );
			$answers = $this->create_answer_array( $questions );
			$quiz_display .= $this->display_begin_section( $options, $quiz_data );
			$quiz_display = apply_filters( 'qmn_begin_quiz_questions', $quiz_display, $options, $quiz_data );
			$quiz_display .= $this->display_questions( $options, $questions, $answers );
			$quiz_display = apply_filters( 'qmn_before_comment_section', $quiz_display, $options, $quiz_data );
			$quiz_display .= $this->display_comment_section( $options, $quiz_data );
			$quiz_display = apply_filters( 'qmn_after_comment_section', $quiz_display, $options, $quiz_data );
			$quiz_display .= $this->display_end_section( $options, $quiz_data );
		}

		$quiz_display .= "<div name='mlw_error_message_bottom' id='mlw_error_message_bottom' class='qmn_error_message_section'></div>";
		$quiz_display .= "<input type='hidden' name='total_questions' id='total_questions' value='$qmn_total_questions'/>";
		$quiz_display .= "<input type='hidden' name='timer' id='timer' value='0'/>";
		$quiz_display .= "<input type='hidden' class='qmn_quiz_id' name='qmn_quiz_id' id='qmn_quiz_id' value='{$quiz_data['quiz_id']}'/>";
		$quiz_display .= "<input type='hidden' name='complete_quiz' value='confirmation' />";
		$quiz_display = apply_filters( 'qmn_end_quiz_form', $quiz_display, $options, $quiz_data );
		$quiz_display .= '</form>';
		$quiz_display .= '</div>';

		$quiz_display = apply_filters( 'qmn_end_quiz', $quiz_display, $options, $quiz_data );
		return $quiz_display;
	}

	/**
	 * Creates the pages of content for the quiz/survey
	 *
	 * @since 5.2.0
	 * @param array $options The settings for the quiz.
	 * @param array $quiz_data The array of quiz data.
	 * @return string The HTML for the pages
	 */
	public function display_pages( $options, $quiz_data ) {
		global $mlwQuizMasterNext;
		global $qmn_json_data;
		ob_start();
		$pages = $mlwQuizMasterNext->pluginHelper->get_quiz_setting( 'pages', array() );
		$questions = QSM_Questions::load_questions_by_pages( $options->quiz_id );
		$question_list = '';
		if ( count( $pages ) > 1 && ( ! empty( $options->message_before ) || 0 == $options->contact_info_location ) ) {
			$qmn_json_data['first_page'] = true;
			$message_before = wpautop( htmlspecialchars_decode( $options->message_before, ENT_QUOTES ) );
			$message_before = apply_filters( 'mlw_qmn_template_variable_quiz_page', $message_before, $quiz_data );
			?>
			<section class="qsm-page">
				<div class="quiz_section quiz_begin">
					<div class='qsm-before-message mlw_qmn_message_before'><?php echo $message_before; ?></div>
					<?php
					if ( 0 == $options->contact_info_location ) {
						echo QSM_Contact_Manager::display_fields( $options );
					}
					?>
				</div>
			</section>
			<?php
		}

		// If there is only one page.
		if ( 1 == count( $pages ) ) {
			?>
			<section class="qsm-page">
				<?php
				if ( ! empty( $options->message_before ) || 0 == $options->contact_info_location ) {
					$qmn_json_data['first_page'] = false;
					$message_before = wpautop( htmlspecialchars_decode( $options->message_before, ENT_QUOTES ) );
					$message_before = apply_filters( 'mlw_qmn_template_variable_quiz_page', $message_before, $quiz_data );
					?>
					<div class="quiz_section quiz_begin">
						<div class='qsm-before-message mlw_qmn_message_before'><?php echo $message_before; ?></div>
						<?php
						if ( 0 == $options->contact_info_location ) {
							echo QSM_Contact_Manager::display_fields( $options );
						}
						?>
					</div>
					<?php
				}
				foreach ( $pages[0] as $question_id ) {
					$question_list .= $question_id . 'Q';
					$question = $questions[ $question_id ];
					?>
					<div class='quiz_section question-section-id-<?php echo esc_attr( $question_id ); ?>'>
						<?php 
						echo $mlwQuizMasterNext->pluginHelper->display_question( $question['question_type_new'], $question_id, $options );
						if ( 0 == $question['comments'] ) {
							echo "<input type='text' class='qsm-question-comment qsm-question-comment-small mlw_qmn_question_comment' x-webkit-speech id='mlwComment$question_id' name='mlwComment$question_id' value='" . esc_attr( htmlspecialchars_decode( $options->comment_field_text, ENT_QUOTES ) ) . "' onclick='qmnClearField(this)'/>";
						}
						if ( 2 == $question['comments'] ) {
							echo "<textarea class='qsm-question-comment qsm-question-comment-large mlw_qmn_question_comment' id='mlwComment$question_id' name='mlwComment$question_id' onclick='qmnClearField(this)'>" . htmlspecialchars_decode( $options->comment_field_text, ENT_QUOTES ) . "</textarea>";
						}
						// Checks if a hint is entered.
						if ( ! empty( $question['hints'] ) ) {
							echo '<div title="' . esc_attr( htmlspecialchars_decode( $question['hints'], ENT_QUOTES ) ) . '" class="qsm-hint qsm_hint mlw_qmn_hint_link">' . $options->hint_text . '</div>';
						}
						?>
					</div>
					<?php
				}
				if ( 0 == $options->comment_section ) {
					$message_comments = wpautop( htmlspecialchars_decode( $options->message_comment, ENT_QUOTES ) );
					$message_comments = apply_filters( 'mlw_qmn_template_variable_quiz_page', $message_comments, $quiz_data );
					?>
					<div class="quiz_section quiz_begin">
						<label for='mlwQuizComments' class='qsm-comments-label mlw_qmn_comment_section_text'><?php echo esc_html( $message_comments ); ?></label>
						<textarea id='mlwQuizComments' name='mlwQuizComments' class='qsm-comments qmn_comment_section'></textarea>
					</div>
					<?php
				}
				if ( ! empty( $options->message_end_template ) || 1 == $options->contact_info_location ) {
					$message_after = wpautop( htmlspecialchars_decode( $options->message_end_template, ENT_QUOTES ) );
					$message_after = apply_filters( 'mlw_qmn_template_variable_quiz_page', $message_after, $quiz_data );
					?>
					<div class="quiz_section">
						<div class='qsm-after-message mlw_qmn_message_end'><?php echo $message_after; ?></div>
						<?php
						if ( 1 == $options->contact_info_location ) {
							echo QSM_Contact_Manager::display_fields( $options );
						}
						?>
					</div>
					<?php
				}
				?>
			</section>
			<?php
		} else {
			foreach ( $pages as $page ) {
				?>
				<section class="qsm-page">
					<?php
					foreach ( $page as $question_id ) {
						$question_list .= $question_id . 'Q';
						$question = $questions[ $question_id ];
						?>
						<div class='quiz_section question-section-id-<?php echo esc_attr( $question_id ); ?>'>
							<?php 
							echo $mlwQuizMasterNext->pluginHelper->display_question( $question['question_type_new'], $question_id, $options );
							if ( 0 == $question['comments'] ) {
								echo "<input type='text' class='qsm-question-comment qsm-question-comment-small mlw_qmn_question_comment' x-webkit-speech id='mlwComment$question_id' name='mlwComment$question_id' value='" . esc_attr( htmlspecialchars_decode( $options->comment_field_text, ENT_QUOTES ) ) . "' onclick='qmnClearField(this)'/>";
							}
							if ( 2 == $question['comments'] ) {
								echo "<textarea class='qsm-question-comment qsm-question-comment-large mlw_qmn_question_comment' id='mlwComment$question_id' name='mlwComment$question_id' onclick='qmnClearField(this)'>" . htmlspecialchars_decode( $options->comment_field_text, ENT_QUOTES ) . "</textarea>";
							}
							// Checks if a hint is entered.
							if ( ! empty( $question['hints'] ) ) {
								echo '<div title="' . esc_attr( htmlspecialchars_decode( $question['hints'], ENT_QUOTES ) ) . '" class="qsm-hint qsm_hint mlw_qmn_hint_link">' . $options->hint_text . '</div>';
							}
							?>
						</div>
						<?php
					}
					?>
				</section>
				<?php
			}
		}

		if ( count( $pages ) > 1 && 0 == $options->comment_section ) {
			$message_comments = wpautop( htmlspecialchars_decode( $options->message_comment, ENT_QUOTES ) );
			$message_comments = apply_filters( 'mlw_qmn_template_variable_quiz_page', $message_comments, $quiz_data );
			?>
			<section class="qsm-page">
				<div class="quiz_section quiz_begin">
					<label for='mlwQuizComments' class='qsm-comments-label mlw_qmn_comment_section_text'><?php echo $message_comments; ?></label>
					<textarea id='mlwQuizComments' name='mlwQuizComments' class='qsm-comments qmn_comment_section'></textarea>
				</div>
			</section>
			<?php
		}
		if ( count( $pages ) > 1 && ( ! empty( $options->message_end_template ) || 1 == $options->contact_info_location ) ) {
			$message_after = wpautop( htmlspecialchars_decode( $options->message_end_template, ENT_QUOTES ) );
			$message_after = apply_filters( 'mlw_qmn_template_variable_quiz_page', $message_after, $quiz_data );
			?>
			<section class="qsm-page">
				<div class="quiz_section">
					<div class='qsm-after-message mlw_qmn_message_end'><?php echo $message_after; ?></div>
					<?php
					if ( 1 == $options->contact_info_location ) {
						echo QSM_Contact_Manager::display_fields( $options );
					}
					?>
				</div>
				<?php
				// Legacy code.
				do_action( 'mlw_qmn_end_quiz_section' );
				?>
			</section>
			<?php
		}
		?>
		<!-- View for pagination -->
		<script type="text/template" id="tmpl-qsm-pagination">
			<div class="qsm-pagination qmn_pagination border margin-bottom">
				<a class="qsm-btn qsm-previous qmn_btn mlw_qmn_quiz_link mlw_previous" href="#"><?php echo esc_html( $options->previous_button_text ); ?></a>
				<span class="qmn_page_message"></span>
				<div class="qmn_page_counter_message"></div>
				<a class="qsm-btn qsm-next qmn_btn mlw_qmn_quiz_link mlw_next" href="#"><?php echo esc_html( $options->next_button_text ); ?></a>
				<input type='submit' class='qsm-btn qsm-submit-btn qmn_btn' value='<?php echo esc_attr( htmlspecialchars_decode( $options->submit_button_text, ENT_QUOTES ) ); ?>' />
			</div>
		</script>
		<input type='hidden' name='qmn_question_list' value='<?php echo esc_attr( $question_list ); ?>' />
		<?php
		return ob_get_clean();
	}

	/**
	 * Creates Display For Beginning Section
	 *
	 * Generates the content for the beginning section of the quiz page
	 *
	 * @since 4.0.0
	 * @param array $qmn_quiz_options The database row of the quiz.
	 * @param array $qmn_array_for_variables The array of results for the quiz.
	 * @return string The content for the beginning section
	 * @deprecated 5.2.0 Use new page system instead
	 */
	public function display_begin_section( $qmn_quiz_options, $qmn_array_for_variables ) {
		$section_display = '';
		global $qmn_json_data;
		if ( ! empty( $qmn_quiz_options->message_before ) || $qmn_quiz_options->contact_info_location == 0) {
			$qmn_json_data["first_page"] = true;
			global $mlw_qmn_section_count;
			$mlw_qmn_section_count +=1;
			$section_display .= "<div class='quiz_section  quiz_begin slide$mlw_qmn_section_count'>";

			$message_before = wpautop(htmlspecialchars_decode($qmn_quiz_options->message_before, ENT_QUOTES));
			$message_before = apply_filters( 'mlw_qmn_template_variable_quiz_page', $message_before, $qmn_array_for_variables);

			$section_display .= "<div class='mlw_qmn_message_before'>$message_before</div>";
			if ( 0 == $qmn_quiz_options->contact_info_location ) {
				$section_display .= QSM_Contact_Manager::display_fields( $qmn_quiz_options );
			}
			$section_display .= "</div>";
		} else {
			$qmn_json_data["first_page"] = false;
		}
		return $section_display;
	}

	/**
	 * Creates Display For Questions
	 *
	 * Generates the content for the questions part of the quiz page
	 *
	 * @since 4.0.0
	 * @param array $qmn_quiz_options The database row of the quiz.
	 * @param array $qmn_quiz_questions The questions of the quiz.
	 * @param array $qmn_quiz_answers The answers of the quiz.
	 * @uses QMNPluginHelper:display_question() Displays a question
	 * @return string The content for the questions section
	 * @deprecated 5.2.0 Use new page system instead
	 */
	public function display_questions( $qmn_quiz_options, $qmn_quiz_questions, $qmn_quiz_answers ) {
		$question_display = '';
		global $mlwQuizMasterNext;
		global $qmn_total_questions;
		global $mlw_qmn_section_count;
		$question_id_list = '';
		foreach ( $qmn_quiz_questions as $mlw_question ) {
			$question_id_list .= $mlw_question->question_id."Q";
			$mlw_qmn_section_count = $mlw_qmn_section_count + 1;
			$question_display .= "<div class='quiz_section question-section-id-{$mlw_question->question_id} slide{$mlw_qmn_section_count}'>";

			$question_display .= $mlwQuizMasterNext->pluginHelper->display_question( $mlw_question->question_type_new, $mlw_question->question_id, $qmn_quiz_options );

			if ( 0 == $mlw_question->comments ) {
				$question_display .= "<input type='text' class='mlw_qmn_question_comment' x-webkit-speech id='mlwComment".$mlw_question->question_id."' name='mlwComment".$mlw_question->question_id."' value='".esc_attr(htmlspecialchars_decode($qmn_quiz_options->comment_field_text, ENT_QUOTES))."' onclick='qmnClearField(this)'/>";
				$question_display .= "<br />";
			}
			if ( 2 == $mlw_question->comments ) {
				$question_display .= "<textarea cols='70' rows='5' class='mlw_qmn_question_comment' id='mlwComment".$mlw_question->question_id."' name='mlwComment".$mlw_question->question_id."' onclick='qmnClearField(this)'>".htmlspecialchars_decode($qmn_quiz_options->comment_field_text, ENT_QUOTES)."</textarea>";
				$question_display .= "<br />";
			}

			// Checks if a hint is entered.
			if ( ! empty( $mlw_question->hints ) ) {
				$question_display .= "<div title=\"" . esc_attr( htmlspecialchars_decode( $mlw_question->hints, ENT_QUOTES ) ) . "\" class='qsm_hint mlw_qmn_hint_link'>{$qmn_quiz_options->hint_text}</div>";
				$question_display .= "<br /><br />";
			}
			$question_display .= "</div>";
		}
		$question_display .= "<input type='hidden' name='qmn_question_list' value='$question_id_list' />";
		return $question_display;
	}

	/**
	 * Creates Display For Comment Section
	 *
	 * Generates the content for the comment section part of the quiz page
	 *
	 * @since 4.0.0
	 * @param array $qmn_quiz_options The database row of the quiz.
	 * @param array $qmn_array_for_variables The array of results for the quiz.
	 * @return string The content for the comment section
	 * @deprecated 5.2.0 Use new page system instead
	 */
	public function display_comment_section( $qmn_quiz_options, $qmn_array_for_variables ) {
		global $mlw_qmn_section_count;
		$comment_display = '';
		if ( 0 == $qmn_quiz_options->comment_section ) {
			$mlw_qmn_section_count = $mlw_qmn_section_count + 1;
			$comment_display .= "<div class='quiz_section slide".$mlw_qmn_section_count."'>";
			$message_comments = wpautop(htmlspecialchars_decode($qmn_quiz_options->message_comment, ENT_QUOTES));
			$message_comments = apply_filters( 'mlw_qmn_template_variable_quiz_page', $message_comments, $qmn_array_for_variables);
			$comment_display .= "<label for='mlwQuizComments' class='mlw_qmn_comment_section_text'>$message_comments</label><br />";
			$comment_display .= "<textarea cols='60' rows='10' id='mlwQuizComments' name='mlwQuizComments' class='qmn_comment_section'></textarea>";
			$comment_display .= "</div>";
		}
		return $comment_display;
	}

	/**
	 * Creates Display For End Section Of Quiz Page
	 *
	 * Generates the content for the end section of the quiz page
	 *
	 * @since 4.0.0
	 * @param array $qmn_quiz_options The database row of the quiz.
	 * @param array $qmn_array_for_variables The array of results for the quiz.
	 * @return string The content for the end section
	 * @deprecated 5.2.0 Use new page system instead
	 */
	public function display_end_section( $qmn_quiz_options, $qmn_array_for_variables ) {
		global $mlw_qmn_section_count;
		$section_display = '';
		$section_display .= "<br />";
		$mlw_qmn_section_count = $mlw_qmn_section_count + 1;
		$section_display .= "<div class='quiz_section slide$mlw_qmn_section_count quiz_end'>";
		if (! empty( $qmn_quiz_options->message_end_template ) ) {
			$message_end = wpautop( htmlspecialchars_decode( $qmn_quiz_options->message_end_template, ENT_QUOTES));
			$message_end = apply_filters( 'mlw_qmn_template_variable_quiz_page', $message_end, $qmn_array_for_variables);
			$section_display .= "<span class='mlw_qmn_message_end'>$message_end</span>";
			$section_display .= "<br /><br />";
		}
		if ( 1 == $qmn_quiz_options->contact_info_location ) {
			$section_display .= QSM_Contact_Manager::display_fields( $qmn_quiz_options );
		}

		//Legacy Code
		ob_start();
		do_action( 'mlw_qmn_end_quiz_section' );
		$section_display .= ob_get_contents();
		ob_end_clean();

		$section_display .= "<input type='submit' class='qsm-btn qsm-submit-btn qmn_btn' value='".esc_attr(htmlspecialchars_decode($qmn_quiz_options->submit_button_text, ENT_QUOTES))."' />";
		$section_display .= "</div>";

		return $section_display;
	}

	/**
	 * Generates Content Results Page
	 *
	 * Generates the content for the results page part of the shortcode
	 *
	 * @since 4.0.0
	 * @param array $qmn_quiz_options The database row of the quiz.
	 * @param array $qmn_quiz_questions The questions of the quiz.
	 * @param array $qmn_quiz_answers The answers of the quiz.
	 * @param array $qmn_array_for_variables The array of results for the quiz.
	 * @uses QMNQuizManager:submit_results() Perform The Quiz/Survey Submission
	 * @return string The content for the results page section
	 */
	public function display_results( $qmn_quiz_options, $qmn_quiz_questions, $qmn_quiz_answers, $qmn_array_for_variables ) {
		$result = $this->submit_results( $qmn_quiz_options, $qmn_quiz_questions, $qmn_quiz_answers, $qmn_array_for_variables );
		$results_array = $result;
		return $results_array['display'];
	}

	/**
	 * Calls the results page from ajax
	 *
	 * @since 4.6.0
	 * @uses QMNQuizManager:submit_results() Perform The Quiz/Survey Submission
	 * @return string The content for the results page section
	 */
	public function ajax_submit_results() {
		global $qmn_allowed_visit;
		global $mlwQuizMasterNext;
		parse_str( $_POST["quizData"], $_POST );
		$qmn_allowed_visit = true;
		$quiz = intval( $_POST["qmn_quiz_id"] );
		$mlwQuizMasterNext->pluginHelper->prepare_quiz( $quiz );
		$qmn_quiz_options = $mlwQuizMasterNext->quiz_settings->get_quiz_options();
		$qmn_quiz_questions = $this->load_questions( $quiz, $qmn_quiz_options, false );
		$qmn_quiz_answers = $this->create_answer_array( $qmn_quiz_questions, true );
		$qmn_array_for_variables = array(
			'quiz_id' => $qmn_quiz_options->quiz_id,
			'quiz_name' => $qmn_quiz_options->quiz_name,
			'quiz_system' => $qmn_quiz_options->system,
		);
		echo json_encode( $this->submit_results( $qmn_quiz_options, $qmn_quiz_questions, $qmn_quiz_answers, $qmn_array_for_variables) );
		die();
	}

	/**
	 * Perform The Quiz/Survey Submission
	 *
	 * Prepares and save the results, prepares and send emails, prepare results page
	 *
	 * @since 4.6.0
	 * @param array $qmn_quiz_options The database row of the quiz.
	 * @param array $qmn_quiz_questions The questions of the quiz.
	 * @param array $qmn_quiz_answers The answers of the quiz.
	 * @param array $qmn_array_for_variables The array of results for the quiz.
	 * @uses QMNQuizManager:check_answers() Creates display for beginning section
	 * @uses QMNQuizManager:check_comment_section() Creates display for questions
	 * @uses QMNQuizManager:display_results_text() Creates display for end section
	 * @uses QMNQuizManager:display_social() Creates display for comment section
	 * @uses QMNQuizManager:send_user_email() Creates display for end section
	 * @uses QMNQuizManager:send_admin_email() Creates display for end section
	 * @return string The content for the results page section
	 */
	public function submit_results( $qmn_quiz_options, $qmn_quiz_questions, $qmn_quiz_answers, $qmn_array_for_variables ) {
		global $qmn_allowed_visit;
		$result_display = '';

		if ( $_SERVER["REMOTE_ADDR"] ) {
			$qmn_array_for_variables['user_ip'] = $_SERVER["REMOTE_ADDR"];
		} else {
			$qmn_array_for_variables['user_ip'] = "Unknown";
		}

		$result_display = apply_filters( 'qmn_begin_results', $result_display, $qmn_quiz_options, $qmn_array_for_variables );
		if ( ! $qmn_allowed_visit ) {
			return $result_display;
		}

		// Gather contact information
		$qmn_array_for_variables['user_name'] = 'None';
		$qmn_array_for_variables['user_business'] = 'None';
		$qmn_array_for_variables['user_email'] = 'None';
		$qmn_array_for_variables['user_phone'] = 'None';
		$contact_responses = QSM_Contact_Manager::process_fields( $qmn_quiz_options );
		foreach ( $contact_responses as $field ) {
			if ( isset( $field['use'] ) ) {
				if ( 'name' === $field['use'] ) {
					$qmn_array_for_variables['user_name'] = $field["value"];
				}
				if ( 'comp' === $field['use'] ) {
					$qmn_array_for_variables['user_business'] = $field["value"];
				}
				if ( 'email' === $field['use'] ) {
					$qmn_array_for_variables['user_email'] = $field["value"];
				}
				if ( 'phone' === $field['use'] ) {
					$qmn_array_for_variables['user_phone'] = $field["value"];
				}
			}
		}

		$mlw_qmn_timer = isset($_POST["timer"]) ? intval( $_POST["timer"] ) : 0;
		$qmn_array_for_variables['user_id'] = get_current_user_id();
		$qmn_array_for_variables['timer'] = $mlw_qmn_timer;
		$qmn_array_for_variables['time_taken'] = current_time( 'h:i:s A m/d/Y' );
		$qmn_array_for_variables['contact'] = $contact_responses;

		if ( !isset( $_POST["mlw_code_captcha"] ) || ( isset( $_POST["mlw_code_captcha"] ) && $_POST["mlw_user_captcha"] == $_POST["mlw_code_captcha"] ) ) {

			$qmn_array_for_variables = array_merge($qmn_array_for_variables,$this->check_answers($qmn_quiz_questions, $qmn_quiz_answers, $qmn_quiz_options, $qmn_array_for_variables));
			$result_display = apply_filters('qmn_after_check_answers', $result_display, $qmn_quiz_options, $qmn_array_for_variables);
			$qmn_array_for_variables['comments'] = $this->check_comment_section($qmn_quiz_options, $qmn_array_for_variables);
			$result_display = apply_filters('qmn_after_check_comments', $result_display, $qmn_quiz_options, $qmn_array_for_variables);
			$result_display .= $this->display_results_text($qmn_quiz_options, $qmn_array_for_variables);
			$result_display = apply_filters('qmn_after_results_text', $result_display, $qmn_quiz_options, $qmn_array_for_variables);
			$result_display .= $this->display_social($qmn_quiz_options, $qmn_array_for_variables);
			$result_display = apply_filters('qmn_after_social_media', $result_display, $qmn_quiz_options, $qmn_array_for_variables);

			// Creates our results array.
			$results_array = array(
				intval( $qmn_array_for_variables['timer'] ),
				$qmn_array_for_variables['question_answers_array'],
				htmlspecialchars( stripslashes( $qmn_array_for_variables['comments'] ), ENT_QUOTES ),
				'contact' => $contact_responses,
			);
			$results_array = apply_filters( 'qsm_results_array', $results_array, $qmn_array_for_variables );
			$serialized_results = serialize( $results_array );

			global $wpdb;
			$table_name = $wpdb->prefix . "mlw_results";
			$results_insert = $wpdb->insert(
				$table_name,
				array(
					'quiz_id'       => $qmn_array_for_variables['quiz_id'],
					'quiz_name'     => $qmn_array_for_variables['quiz_name'],
					'quiz_system'   => $qmn_array_for_variables['quiz_system'],
					'point_score'   => $qmn_array_for_variables['total_points'],
					'correct_score' => $qmn_array_for_variables['total_score'],
					'correct'       => $qmn_array_for_variables['total_correct'],
					'total'         => $qmn_array_for_variables['total_questions'],
					'name' => $qmn_array_for_variables['user_name'],
					'business' => $qmn_array_for_variables['user_business'],
					'email' => $qmn_array_for_variables['user_email'],
					'phone' => $qmn_array_for_variables['user_phone'],
					'user' => $qmn_array_for_variables['user_id'],
					'user_ip' => $qmn_array_for_variables['user_ip'],
					'time_taken' => $qmn_array_for_variables['time_taken'],
					'time_taken_real' => date( "Y-m-d H:i:s", strtotime( $qmn_array_for_variables['time_taken'] ) ),
					'quiz_results' => $serialized_results,
					'deleted' => 0
				),
				array(
					'%d',
					'%s',
					'%d',
					'%d',
					'%d',
					'%d',
					'%d',
					'%s',
					'%s',
					'%s',
					'%s',
					'%d',
					'%s',
					'%s',
					'%s',
					'%s',
					'%d'
				)
			);


			$this->send_user_email($qmn_quiz_options, $qmn_array_for_variables);
			$result_display = apply_filters('qmn_after_send_user_email', $result_display, $qmn_quiz_options, $qmn_array_for_variables);
			$this->send_admin_email($qmn_quiz_options, $qmn_array_for_variables);
			$result_display = apply_filters('qmn_after_send_admin_email', $result_display, $qmn_quiz_options, $qmn_array_for_variables);
			$result_display = apply_filters('qmn_end_results', $result_display, $qmn_quiz_options, $qmn_array_for_variables);
			//Legacy Code
			do_action('mlw_qmn_load_results_page', $wpdb->insert_id, $qmn_quiz_options->quiz_settings);
		}
		else
		{
			$result_display .= "Thank you.";
		}

		//Check to see if we need to set up a redirect
		$redirect = false;
		$redirect_url = '';
		if (is_serialized($qmn_quiz_options->message_after) && is_array(@unserialize($qmn_quiz_options->message_after))) {
			$mlw_message_after_array = @unserialize($qmn_quiz_options->message_after);

			//Cycle through landing pages
			foreach($mlw_message_after_array as $mlw_each)
			{
				//Check to see if not default
				if ($mlw_each[0] != 0 || $mlw_each[1] != 0)
				{
					//Check to see if points fall in correct range
					if ($qmn_quiz_options->system == 1 && $qmn_array_for_variables['total_points'] >= $mlw_each[0] && $qmn_array_for_variables['total_points'] <= $mlw_each[1])
					{
						if (esc_url($mlw_each["redirect_url"]) != '')
						{
							$redirect = true;
							$redirect_url = esc_url( $mlw_each["redirect_url"] );
						}
						break;
					}
					//Check to see if score fall in correct range
					if ($qmn_quiz_options->system == 0 && $qmn_array_for_variables['total_score'] >= $mlw_each[0] && $qmn_array_for_variables['total_score'] <= $mlw_each[1])
					{
						if (esc_url($mlw_each["redirect_url"]) != '')
						{
							$redirect = true;
							$redirect_url = esc_url( $mlw_each["redirect_url"] );
						}
						break;
					}
				}
				else
				{
					if (esc_url($mlw_each["redirect_url"]) != '')
					{
						$redirect = true;
						$redirect_url = esc_url( $mlw_each["redirect_url"] );
					}
					break;
				}
			}
		}

		//Prepare data to be sent back to front-end
		$return_array = array(
			'display' => $result_display,
			'redirect' => $redirect,
			'redirect_url' => $redirect_url
		);

		return $return_array;
	}

	/**
	  * Scores User Answers
	  *
	  * Calculates the users scores for the quiz
	  *
	  * @since 4.0.0
		* @param array $qmn_quiz_options The database row of the quiz
		* @param array $qmn_quiz_questions The questions of the quiz
		* @param array $qmn_quiz_answers The answers of the quiz
		* @param array $qmn_array_for_variables The array of results for the quiz
		* @uses QMNPluginHelper:display_review() Scores the question
		* @return array The results of the user's score
	  */
	public function check_answers($qmn_quiz_questions, $qmn_quiz_answers, $qmn_quiz_options, $qmn_array_for_variables)
	{
		$mlw_points = 0;
		$mlw_correct = 0;
		$mlw_total_score = 0;
		$mlw_question_answers = "";
		global $mlwQuizMasterNext;
		isset($_POST["total_questions"]) ? $mlw_total_questions = intval($_POST["total_questions"]) : $mlw_total_questions = 0;
		isset($_POST["qmn_question_list"]) ? $question_list = explode('Q',$_POST["qmn_question_list"]) : $question_list = array();
		$mlw_user_text = "";
		$mlw_correct_text = "";
		$qmn_correct = "incorrect";
		$qmn_answer_points = 0;
		$mlw_qmn_answer_array = array();
		foreach($qmn_quiz_questions as $mlw_question)
		{
			foreach($question_list as $question_id)
			{
				if ($mlw_question->question_id == $question_id)
				{
					$mlw_user_text = "";
					$mlw_correct_text = "";
					$qmn_correct = "incorrect";
					$qmn_answer_points = 0;

					$results_array = $mlwQuizMasterNext->pluginHelper->display_review($mlw_question->question_type_new, $mlw_question->question_id);
					if (!isset($results_array["null_review"]))
					{
						$mlw_points += $results_array["points"];
						$qmn_answer_points += $results_array["points"];
						if ($results_array["correct"] == "correct")
						{
							$mlw_correct += 1;
							$qmn_correct = "correct";
						}
						$mlw_user_text = $results_array["user_text"];
						$mlw_correct_text = $results_array["correct_text"];

						if ( isset( $_POST["mlwComment".$mlw_question->question_id] ) ) {
							$mlw_qm_question_comment = htmlspecialchars( stripslashes( $_POST["mlwComment".$mlw_question->question_id] ), ENT_QUOTES );
						} else {
							$mlw_qm_question_comment = "";
						}
						$question_text = $mlw_question->question_name;
						if (isset($results_array["question_text"]))
						{
							$question_text = $results_array["question_text"];
						}
						$mlw_qmn_answer_array[] = apply_filters('qmn_answer_array', array($question_text, htmlspecialchars($mlw_user_text, ENT_QUOTES), htmlspecialchars($mlw_correct_text, ENT_QUOTES), $mlw_qm_question_comment, "correct" => $qmn_correct, "id" => $mlw_question->question_id, "points" => $qmn_answer_points, "category" => $mlw_question->category), $qmn_quiz_options, $qmn_array_for_variables);
					}
					break;
				}
			}
		}

		//Calculate Total Percent Score And Average Points Only If Total Questions Doesn't Equal Zero To Avoid Division By Zero Error
		if ($mlw_total_questions != 0)
		{
			$mlw_total_score = round((($mlw_correct/$mlw_total_questions)*100), 2);
			$mlw_average_points = round(($mlw_points/$mlw_total_questions), 2);
		}
		else
		{
			$mlw_total_score = 0;
			$mlw_average_points = 0;
		}

		return array(
			'total_points' => $mlw_points,
			'total_score' => $mlw_total_score,
			'total_correct' => $mlw_correct,
			'total_questions' => $mlw_total_questions,
			'question_answers_display' => $mlw_question_answers,
			'question_answers_array' => $mlw_qmn_answer_array,
		);
	}

	/**
	  * Retrieves User's Comments
	  *
	  * Checks to see if the user left a comment and returns the comment
	  *
	  * @since 4.0.0
		* @param array $qmn_quiz_options The database row of the quiz
		* @param array $qmn_array_for_variables The array of results for the quiz
		* @return string The user's comments
	  */
	public function check_comment_section($qmn_quiz_options, $qmn_array_for_variables)
	{
		$qmn_quiz_comments = "";
		if ( isset( $_POST["mlwQuizComments"] ) ) {
			$qmn_quiz_comments = esc_textarea( stripslashes( $_POST["mlwQuizComments"] ) );
		}
		return apply_filters( 'qmn_returned_comments', $qmn_quiz_comments, $qmn_quiz_options, $qmn_array_for_variables );
	}


	/**
	  * Displays Results Text
	  *
	  * Prepares and display text for the results page
	  *
	  * @since 4.0.0
		* @param array $qmn_quiz_options The database row of the quiz
		* @param array $qmn_array_for_variables The array of results for the quiz
		* @return string The contents for the results text
	  */
	public function display_results_text($qmn_quiz_options, $qmn_array_for_variables)
	{
		$results_text_display = '';
		if (is_serialized($qmn_quiz_options->message_after) && is_array(@unserialize($qmn_quiz_options->message_after)))
		{
			$mlw_message_after_array = @unserialize($qmn_quiz_options->message_after);
			//Cycle through landing pages
			foreach($mlw_message_after_array as $mlw_each)
			{
				//Check to see if default
				if ($mlw_each[0] == 0 && $mlw_each[1] == 0)
				{
					$mlw_message_after = htmlspecialchars_decode($mlw_each[2], ENT_QUOTES);
					$mlw_message_after = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_message_after, $qmn_array_for_variables);
					$mlw_message_after = str_replace( "\n" , "<br>", $mlw_message_after);
					$results_text_display .= $mlw_message_after;
					break;
				}
				else
				{
					//Check to see if points fall in correct range
					if ($qmn_quiz_options->system == 1 && $qmn_array_for_variables['total_points'] >= $mlw_each[0] && $qmn_array_for_variables['total_points'] <= $mlw_each[1])
					{
						$mlw_message_after = htmlspecialchars_decode($mlw_each[2], ENT_QUOTES);
						$mlw_message_after = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_message_after, $qmn_array_for_variables);
						$mlw_message_after = str_replace( "\n" , "<br>", $mlw_message_after);
						$results_text_display .= $mlw_message_after;
						break;
					}
					//Check to see if score fall in correct range
					if ($qmn_quiz_options->system == 0 && $qmn_array_for_variables['total_score'] >= $mlw_each[0] && $qmn_array_for_variables['total_score'] <= $mlw_each[1])
					{
						$mlw_message_after = htmlspecialchars_decode($mlw_each[2], ENT_QUOTES);
						$mlw_message_after = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_message_after, $qmn_array_for_variables);
						$mlw_message_after = str_replace( "\n" , "<br>", $mlw_message_after);
						$results_text_display .= $mlw_message_after;
						break;
					}
				}
			}
		}
		else
		{
			//Prepare the after quiz message
			$mlw_message_after = htmlspecialchars_decode($qmn_quiz_options->message_after, ENT_QUOTES);
			$mlw_message_after = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_message_after, $qmn_array_for_variables);
			$mlw_message_after = str_replace( "\n" , "<br>", $mlw_message_after);
			$results_text_display .= $mlw_message_after;
		}
		return do_shortcode( $results_text_display );
	}

	/**
	  * Display Social Media Buttons
	  *
	  * Prepares and displays social media buttons for sharing results
	  *
	  * @since 4.0.0
		* @param array $qmn_quiz_options The database row of the quiz
		* @param array $qmn_array_for_variables The array of results for the quiz
		* @return string The content of the social media button section
	  */
	public function display_social($qmn_quiz_options, $qmn_array_for_variables)
	{
		$social_display = '';
		if ($qmn_quiz_options->social_media == 1)
		{
			$settings = (array) get_option( 'qmn-settings' );
			$facebook_app_id = '483815031724529';
			if (isset($settings['facebook_app_id']))
			{
				$facebook_app_id = esc_js( $settings['facebook_app_id'] );
			}

			//Load Social Media Text
			$qmn_social_media_text = "";
			if ( is_serialized( $qmn_quiz_options->social_media_text ) && is_array( @unserialize( $qmn_quiz_options->social_media_text ) ) ) {
				$qmn_social_media_text = @unserialize($qmn_quiz_options->social_media_text);
			} else {
				$qmn_social_media_text = array(
		        		'twitter' => $qmn_quiz_options->social_media_text,
		        		'facebook' => $qmn_quiz_options->social_media_text
		        	);
			}
			$qmn_social_media_text["twitter"] = apply_filters( 'mlw_qmn_template_variable_results_page', $qmn_social_media_text["twitter"], $qmn_array_for_variables);
			$qmn_social_media_text["facebook"] = apply_filters( 'mlw_qmn_template_variable_results_page', $qmn_social_media_text["facebook"], $qmn_array_for_variables);
			$social_display .= "<br />
			<a class=\"mlw_qmn_quiz_link\" onclick=\"qmnSocialShare('facebook', '".esc_js($qmn_social_media_text["facebook"])."', '".esc_js($qmn_quiz_options->quiz_name)."', '$facebook_app_id');\">Facebook</a>
			<a class=\"mlw_qmn_quiz_link\" onclick=\"qmnSocialShare('twitter', '".esc_js($qmn_social_media_text["twitter"])."', '".esc_js($qmn_quiz_options->quiz_name)."');\">Twitter</a>
			<br />";
		}
		return apply_filters('qmn_returned_social_buttons', $social_display, $qmn_quiz_options, $qmn_array_for_variables);
	}

	/**
	  * Send User Email
	  *
	  * Prepares the email to the user and then sends the email
	  *
	  * @since 4.0.0
		* @param array $qmn_quiz_options The database row of the quiz
		* @param array $qmn_array_for_variables The array of results for the quiz
		*/
	public function send_user_email($qmn_quiz_options, $qmn_array_for_variables)
	{
		add_filter( 'wp_mail_content_type', 'mlw_qmn_set_html_content_type' );
		$mlw_message = "";

		//Check if this quiz has user emails turned on
		if ( $qmn_quiz_options->send_user_email == "0" ) {

			//Make sure that the user filled in the email field
			if ( $qmn_array_for_variables['user_email'] != "" ) {

				//Prepare from email and name
				$from_email_array = maybe_unserialize( $qmn_quiz_options->email_from_text );
				if ( ! isset( $from_email_array["from_email"] ) ) {
					$from_email_array = array(
						'from_name' => $qmn_quiz_options->email_from_text,
						'from_email' => $qmn_quiz_options->admin_email,
						'reply_to' => 1
					);
				}

				if ( ! is_email( $from_email_array["from_email"] ) ) {
					if ( is_email( $qmn_quiz_options->admin_email ) ) {
						$from_email_array["from_email"] = $qmn_quiz_options->admin_email;
					} else {
						$from_email_array["from_email"] = get_option( 'admin_email ', 'test@example.com' );
					}
				}

				//Prepare email attachments
				$attachments = array();
				$attachments = apply_filters( 'qsm_user_email_attachments', $attachments, $qmn_array_for_variables );

				if ( is_serialized( $qmn_quiz_options->user_email_template ) && is_array( @unserialize( $qmn_quiz_options->user_email_template ) ) ) {

					$mlw_user_email_array = @unserialize( $qmn_quiz_options->user_email_template );

					//Cycle through emails
					foreach( $mlw_user_email_array as $mlw_each ) {

						//Generate Email Subject
						if ( !isset( $mlw_each[3] ) ) {
							$mlw_each[3] = "Quiz Results For %QUIZ_NAME";
						}
						$mlw_each[3] = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_each[3], $qmn_array_for_variables );

						//Check to see if default
						if ( $mlw_each[0] == 0 && $mlw_each[1] == 0 ) {
							$mlw_message = htmlspecialchars_decode( $mlw_each[2], ENT_QUOTES );
							$mlw_message = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables );
							$mlw_message = str_replace( "\n" , "<br>", $mlw_message );
							$mlw_message = str_replace( "<br/>" , "<br>", $mlw_message );
							$mlw_message = str_replace( "<br />" , "<br>", $mlw_message );
							$mlw_headers = 'From: '.$from_email_array["from_name"].' <'.$from_email_array["from_email"].'>' . "\r\n";
							wp_mail( $qmn_array_for_variables['user_email'], $mlw_each[3], $mlw_message, $mlw_headers, $attachments );
							break;
						} else {

							//Check to see if this quiz uses points and check if the points earned falls in the point range for this email
							if ( $qmn_quiz_options->system == 1 && $qmn_array_for_variables['total_points'] >= $mlw_each[0] && $qmn_array_for_variables['total_points'] <= $mlw_each[1] ) {
								$mlw_message = htmlspecialchars_decode( $mlw_each[2], ENT_QUOTES );
								$mlw_message = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables );
								$mlw_message = str_replace( "\n" , "<br>", $mlw_message );
								$mlw_message = str_replace( "<br/>" , "<br>", $mlw_message );
								$mlw_message = str_replace( "<br />" , "<br>", $mlw_message );
								$mlw_headers = 'From: '.$from_email_array["from_name"].' <'.$from_email_array["from_email"].'>' . "\r\n";
								wp_mail( $qmn_array_for_variables['user_email'], $mlw_each[3], $mlw_message, $mlw_headers, $attachments );
								break;
							}

							//Check to see if score fall in correct range
							if ( $qmn_quiz_options->system == 0 && $qmn_array_for_variables['total_score'] >= $mlw_each[0] && $qmn_array_for_variables['total_score'] <= $mlw_each[1] ) {
								$mlw_message = htmlspecialchars_decode( $mlw_each[2], ENT_QUOTES );
								$mlw_message = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables );
								$mlw_message = str_replace( "\n" , "<br>", $mlw_message );
								$mlw_message = str_replace( "<br/>" , "<br>", $mlw_message );
								$mlw_message = str_replace( "<br />" , "<br>", $mlw_message );
								$mlw_headers = 'From: '.$from_email_array["from_name"].' <'.$from_email_array["from_email"].'>' . "\r\n";
								wp_mail( $qmn_array_for_variables['user_email'], $mlw_each[3], $mlw_message, $mlw_headers, $attachments );
								break;
							}
						}
					}
				} else {

					//Uses older email system still which was before different emails were created.
					$mlw_message = htmlspecialchars_decode( $qmn_quiz_options->user_email_template, ENT_QUOTES );
					$mlw_message = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables );
					$mlw_message = str_replace( "\n" , "<br>", $mlw_message );
					$mlw_message = str_replace( "<br/>" , "<br>", $mlw_message );
					$mlw_message = str_replace( "<br />" , "<br>", $mlw_message );
					$mlw_headers = 'From: '.$from_email_array["from_name"].' <'.$from_email_array["from_email"].'>' . "\r\n";
					wp_mail( $qmn_array_for_variables['user_email'], "Quiz Results For ".$qmn_quiz_options->quiz_name, $mlw_message, $mlw_headers, $attachments );
				}
			}
		}
		remove_filter( 'wp_mail_content_type', 'mlw_qmn_set_html_content_type' );
	}

	/**
	  * Send Admin Email
	  *
	  * Prepares the email to the admin and then sends the email
	  *
	  * @since 4.0.0
		* @param array $qmn_quiz_options The database row of the quiz
		* @param arrar $qmn_array_for_variables The array of results for the quiz
		*/
	public function send_admin_email($qmn_quiz_options, $qmn_array_for_variables)
	{
		//Switch email type to HTML
		add_filter( 'wp_mail_content_type', 'mlw_qmn_set_html_content_type' );

		$mlw_message = "";
		if ( $qmn_quiz_options->send_admin_email == "0" ) {
			if ( $qmn_quiz_options->admin_email != "" ) {
				$from_email_array = maybe_unserialize( $qmn_quiz_options->email_from_text );
				if ( ! isset( $from_email_array["from_email"] ) ) {
					$from_email_array = array(
						'from_name' => $qmn_quiz_options->email_from_text,
						'from_email' => $qmn_quiz_options->admin_email,
						'reply_to' => 1
					);
				}

				if ( ! is_email( $from_email_array["from_email"] ) ) {
					if ( is_email( $qmn_quiz_options->admin_email ) ) {
						$from_email_array["from_email"] = $qmn_quiz_options->admin_email;
					} else {
						$from_email_array["from_email"] = get_option( 'admin_email ', 'test@example.com' );
					}
				}

				$mlw_message = "";
				$mlw_subject = "";
				if (is_serialized($qmn_quiz_options->admin_email_template) && is_array(@unserialize($qmn_quiz_options->admin_email_template)))
				{
					$mlw_admin_email_array = @unserialize($qmn_quiz_options->admin_email_template);

					//Cycle through landing pages
					foreach($mlw_admin_email_array as $mlw_each)
					{

						//Generate Email Subject
						if (!isset($mlw_each["subject"]))
						{
							$mlw_each["subject"] = "Quiz Results For %QUIZ_NAME";
						}
						$mlw_each["subject"] = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_each["subject"], $qmn_array_for_variables);

						//Check to see if default
						if ($mlw_each["begin_score"] == 0 && $mlw_each["end_score"] == 0)
						{
							$mlw_message = htmlspecialchars_decode($mlw_each["message"], ENT_QUOTES);
							$mlw_message = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables);
							$mlw_message = str_replace( "\n" , "<br>", $mlw_message);
							$mlw_message = str_replace( "<br/>" , "<br>", $mlw_message);
							$mlw_message = str_replace( "<br />" , "<br>", $mlw_message);
							$mlw_subject = $mlw_each["subject"];
							break;
						}
						else
						{
							//Check to see if points fall in correct range
							if ($qmn_quiz_options->system == 1 && $qmn_array_for_variables['total_points'] >= $mlw_each["begin_score"] && $qmn_array_for_variables['total_points'] <= $mlw_each["end_score"])
							{
								$mlw_message = htmlspecialchars_decode($mlw_each["message"], ENT_QUOTES);
								$mlw_message = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables);
								$mlw_message = str_replace( "\n" , "<br>", $mlw_message);
								$mlw_message = str_replace( "<br/>" , "<br>", $mlw_message);
								$mlw_message = str_replace( "<br />" , "<br>", $mlw_message);
								$mlw_subject = $mlw_each["subject"];
								break;
							}

							//Check to see if score fall in correct range
							if ($qmn_quiz_options->system == 0 && $qmn_array_for_variables['total_score'] >= $mlw_each["begin_score"] && $qmn_array_for_variables['total_score'] <= $mlw_each["end_score"])
							{
								$mlw_message = htmlspecialchars_decode($mlw_each["message"], ENT_QUOTES);
								$mlw_message = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables);
								$mlw_message = str_replace( "\n" , "<br>", $mlw_message);
								$mlw_message = str_replace( "<br/>" , "<br>", $mlw_message);
								$mlw_message = str_replace( "<br />" , "<br>", $mlw_message);
								$mlw_subject = $mlw_each["subject"];
								break;
							}
						}
					}
				}
				else
				{
					$mlw_message = htmlspecialchars_decode($qmn_quiz_options->admin_email_template, ENT_QUOTES);
					$mlw_message = apply_filters( 'mlw_qmn_template_variable_results_page', $mlw_message, $qmn_array_for_variables);
					$mlw_message = str_replace( "\n" , "<br>", $mlw_message);
					$mlw_message = str_replace( "<br/>" , "<br>", $mlw_message);
					$mlw_message = str_replace( "<br />" , "<br>", $mlw_message);
					$mlw_subject = "Quiz Results For ".$qmn_quiz_options->quiz_name;
				}
			}
			if ( get_option('mlw_advert_shows') == 'true' ) {$mlw_message .= "<br>This email was generated by the Quiz And Survey Master script by Frank Corso";}
			$headers = array(
				'From: '.$from_email_array["from_name"].' <'.$from_email_array["from_email"].'>'
			);
			if ( $from_email_array["reply_to"] == 0 ) {
				$headers[] = 'Reply-To: '.$qmn_array_for_variables["user_name"]." <".$qmn_array_for_variables["user_email"].">";
			}
			$admin_emails = explode( ",", $qmn_quiz_options->admin_email );
			foreach( $admin_emails as $admin_email ) {
				if ( is_email( $admin_email ) ) {
					wp_mail( $admin_email, $mlw_subject, $mlw_message, $headers );
				}
			}
		}

		//Remove HTML type for emails
		remove_filter( 'wp_mail_content_type', 'mlw_qmn_set_html_content_type' );
	}
}
$qmnQuizManager = new QMNQuizManager();

add_filter('qmn_begin_shortcode', 'qmn_require_login_check', 10, 3);
function qmn_require_login_check($display, $qmn_quiz_options, $qmn_array_for_variables)
{
	global $qmn_allowed_visit;
	if ( $qmn_quiz_options->require_log_in == 1 && !is_user_logged_in() )
	{
		$qmn_allowed_visit = false;
		$mlw_message = wpautop(htmlspecialchars_decode($qmn_quiz_options->require_log_in_text, ENT_QUOTES));
		$mlw_message = apply_filters( 'mlw_qmn_template_variable_quiz_page', $mlw_message, $qmn_array_for_variables);
		$mlw_message = str_replace( "\n" , "<br>", $mlw_message);
		$display .= $mlw_message;
		$display .= wp_login_form( array('echo' => false) );
	}
	return $display;
}

add_filter( 'qmn_begin_shortcode', 'qsm_scheduled_timeframe_check', 10, 3 );
function qsm_scheduled_timeframe_check( $display, $options, $variable_data ) {
	global $qmn_allowed_visit;

	// Checks if the start and end dates have data
	if ( ! empty( $options->scheduled_time_start ) && ! empty( $options->scheduled_time_end ) ) {
		$start = strtotime( $options->scheduled_time_start );
		$end = strtotime( $options->scheduled_time_end ) + 86399;

		// Checks if the current timestamp is outside of scheduled timeframe
		if ( current_time( 'timestamp' ) < $start || current_time( 'timestamp' ) > $end ) {
			$qmn_allowed_visit = false;
			$message = wpautop( htmlspecialchars_decode( $options->scheduled_timeframe_text, ENT_QUOTES ) );
			$message = apply_filters( 'mlw_qmn_template_variable_quiz_page', $message, $variable_data );
			$display .= str_replace( "\n" , "<br>", $message );
		}
	}
	return $display;
}

add_filter('qmn_begin_shortcode', 'qmn_total_user_tries_check', 10, 3);

/**
 * Checks if user has already reach the user limit of the quiz
 *
 * @since 5.0.0
 * @param string $display The HTML displayed for the quiz
 * @param array $qmn_quiz_options The settings for the quiz
 * @param array $qmn_array_for_variables The array of data by the quiz
 * @return string The altered HTML display for the quiz
 */
function qmn_total_user_tries_check( $display, $qmn_quiz_options, $qmn_array_for_variables ) {

	global $qmn_allowed_visit;
	if ( $qmn_quiz_options->total_user_tries != 0 ) {

		// Prepares the variables
		global $wpdb;
		$mlw_qmn_user_try_count = 0;

		// Checks if the user is logged in. If so, check by user id. If not, check by IP.
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$mlw_qmn_user_try_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM ".$wpdb->prefix."mlw_results WHERE user=%d AND deleted='0' AND quiz_id=%d", $current_user->ID, $qmn_array_for_variables['quiz_id'] ) );
		} else {
			$mlw_qmn_user_try_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM ".$wpdb->prefix."mlw_results WHERE user_ip='%s' AND deleted='0' AND quiz_id=%d", $qmn_array_for_variables['user_ip'], $qmn_array_for_variables['quiz_id'] ) );
		}

		// If user has already reached the limit for this quiz
		if ( $mlw_qmn_user_try_count >= $qmn_quiz_options->total_user_tries ) {

			// Stops the quiz and prepares entered text
			$qmn_allowed_visit = false;
			$mlw_message = wpautop( htmlspecialchars_decode( $qmn_quiz_options->total_user_tries_text, ENT_QUOTES ) );
			$mlw_message = apply_filters( 'mlw_qmn_template_variable_quiz_page', $mlw_message, $qmn_array_for_variables );
			$display .= $mlw_message;
		}
	}
	return $display;
}

add_filter('qmn_begin_quiz', 'qmn_total_tries_check', 10, 3);
function qmn_total_tries_check($display, $qmn_quiz_options, $qmn_array_for_variables)
{
	global $qmn_allowed_visit;
	if ( $qmn_quiz_options->limit_total_entries != 0 )
	{
		global $wpdb;
		$mlw_qmn_entries_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(quiz_id) FROM ".$wpdb->prefix."mlw_results WHERE deleted='0' AND quiz_id=%d", $qmn_array_for_variables['quiz_id'] ) );
		if ($mlw_qmn_entries_count >= $qmn_quiz_options->limit_total_entries)
		{
			$mlw_message = wpautop(htmlspecialchars_decode($qmn_quiz_options->limit_total_entries_text, ENT_QUOTES));
			$mlw_message = apply_filters( 'mlw_qmn_template_variable_quiz_page', $mlw_message, $qmn_array_for_variables);
			$display .= $mlw_message;
			$qmn_allowed_visit = false;
		}
	}
	return $display;
}

add_filter('qmn_begin_quiz', 'qmn_pagination_check', 10, 3);
function qmn_pagination_check( $display, $qmn_quiz_options, $qmn_array_for_variables ) {
	if ( $qmn_quiz_options->pagination != 0 ) {
		global $wpdb;
		global $qmn_json_data;
		$total_questions = 0;
		if ( $qmn_quiz_options->question_from_total != 0 ) {
			$total_questions = $qmn_quiz_options->question_from_total;
		} else {
			$questions = QSM_Questions::load_questions_by_pages( $qmn_quiz_options->quiz_id );
			$total_questions = count( $questions );
		}
		$display .= "<style>.quiz_section { display: none; }</style>";

		$qmn_json_data["pagination"] = array(
			'amount' => $qmn_quiz_options->pagination,
			'section_comments' => $qmn_quiz_options->comment_section,
			'total_questions' => $total_questions,
			'previous_text' => $qmn_quiz_options->previous_button_text,
			'next_text' => $qmn_quiz_options->next_button_text
		);
	}
	return $display;
}

add_filter( 'qmn_begin_quiz_form', 'qmn_timer_check', 15, 3 );
function qmn_timer_check( $display, $qmn_quiz_options, $qmn_array_for_variables ) {
	global $qmn_allowed_visit;
	global $qmn_json_data;
	if ( $qmn_allowed_visit && $qmn_quiz_options->timer_limit != 0 ) {
		$qmn_json_data["timer_limit"] = $qmn_quiz_options->timer_limit;
		$display .= '<div style="display:none;" id="mlw_qmn_timer" class="mlw_qmn_timer"></div>';
	}
	return $display;
}

add_filter('qmn_begin_quiz', 'qmn_update_views', 10, 3);
function qmn_update_views($display, $qmn_quiz_options, $qmn_array_for_variables)
{
	global $wpdb;
	$mlw_views = $qmn_quiz_options->quiz_views;
	$mlw_views += 1;
	$results = $wpdb->update(
		$wpdb->prefix . "mlw_quizzes",
		array(
			'quiz_views' => $mlw_views
		),
		array( 'quiz_id' => $qmn_array_for_variables["quiz_id"] ),
		array(
			'%d'
		),
		array( '%d' )
	);
	return $display;
}

add_filter('qmn_begin_results', 'qmn_update_taken', 10, 3);
function qmn_update_taken($display, $qmn_quiz_options, $qmn_array_for_variables)
{
	global $wpdb;
	$mlw_taken = $qmn_quiz_options->quiz_taken;
	$mlw_taken += 1;
	$results = $wpdb->update(
		$wpdb->prefix . "mlw_quizzes",
		array(
			'quiz_taken' => $mlw_taken
		),
		array( 'quiz_id' => $qmn_array_for_variables["quiz_id"] ),
		array(
			'%d'
		),
		array( '%d' )
	);
	return $display;
}

/*
This function helps set the email type to HTML
*/
function mlw_qmn_set_html_content_type() {

	return 'text/html';
}
?>
