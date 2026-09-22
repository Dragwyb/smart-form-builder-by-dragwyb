<?php

namespace Dragwyb\Form_Builder\Includes\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Dragwyb\Form_Builder\Admin\Dragwyb_Pages\Dragwyb_Post;

class Helper {

	public static function directory_separator() {
		if ( defined( 'DIRECTORY_SEPARATOR' ) ) {
			return DIRECTORY_SEPARATOR;
		}

		$dir = ( strpos( DRAGWYB_FORM_BUILDER_PATH, '\\' ) !== false ) ? '\\' : '/';
		return $dir;
	}

	public static function namespace_into_dir_path( $namespace ) {
		$seperator = self::directory_separator();
		if ( ! strpos( $namespace, $seperator ) ) {
			$namespace = str_replace( '\\', $seperator, $namespace );
		}
		return $namespace;
	}

	public static function dir_path_into_namespace( $dir ) {
		$seperator = self::directory_separator();

		if ( strpos( $dir, $seperator ) ) {
			$dir = str_replace( $seperator, '\\', $dir );
		}

		return $dir;
	}

	final public static function is_preview_mode(): bool {
		$form_preview_id = isset( $_GET['preview_id'] ) ? sanitize_text_field( wp_unslash( $_GET['preview_id'] ) ) : '';
		$form_id         = isset( $_GET['p'] ) ? absint( $_GET['p'] ) : 0;
		$post_type       = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : '';

		if ( $form_preview_id && $form_id && $post_type === Dragwyb_Post::POST_TYPE && self::current_user_can_preview( $form_id ) && wp_verify_nonce( $form_preview_id, self::preview_private_key_name( $form_id ) ) && is_user_logged_in() ) {
			return true;
		}

		return false;
	}

	private static function current_user_can_preview( int $form_id ): bool {
		return current_user_can( 'manage_options' ) || current_user_can( 'edit_post', $form_id );
	}

	private static function preview_private_key_name( int $form_id ): string {
		$post_type = Dragwyb_Post::POST_TYPE;
		return $post_type . '-' . $form_id;
	}

	final public static function is_editor_preview_mode(): bool {
		if ( self::is_preview_mode() ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is done in self::is_preview_mode() which is called first.
			$iframe_mode = isset( $_GET['dragwyb_iframe_mode'] ) ? sanitize_key( wp_unslash( $_GET['dragwyb_iframe_mode'] ) ) : '';

			if ( 'true' === $iframe_mode ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Register shared Flatpickr script and style once.
	 */
	public static function register_flatpickr_assets(): void {
		if ( ! wp_script_is( 'dragwyb-flatpickr', 'registered' ) ) {
			wp_register_script(
				'dragwyb-flatpickr',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/lib/flatpickr/js/flatpickr.min.js' ),
				array(),
				DRAGWYB_FORM_BUILDER_VERSION,
				true
			);
		}

		if ( ! wp_style_is( 'dragwyb-flatpickr', 'registered' ) ) {
			wp_register_style(
				'dragwyb-flatpickr',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/lib/flatpickr/css/flatpickr.min.css' ),
				array(),
				DRAGWYB_FORM_BUILDER_VERSION,
				'all'
			);
		}
	}

	/**
	 * Detect if a supported translation plugin (Polylang or WPML) is active.
	 *
	 * Uses global variables and common functions.
	 *
	 * @return string 'polylang', 'wpml', or '' if neither is active.
	 */
	public static function get_active_translation_plugin(): string {
		// 1. Check Polylang via global variable $polylang or common function.
		if ( function_exists( 'pll_current_language' ) || isset( $GLOBALS['polylang'] ) ) {
			return 'polylang';
		}

		// 2. Check WPML via global variable $sitepress, version constant, or filter.
		if ( isset( $GLOBALS['sitepress'] ) || defined( 'ICL_SITEPRESS_VERSION' ) || has_filter( 'wpml_current_language' ) ) {
			return 'wpml';
		}

		return '';
	}

	/**
	 * Check if any translation plugin is active.
	 *
	 * @return bool
	 */
	public static function is_translation_plugin_active(): bool {
		return ! empty( self::get_active_translation_plugin() );
	}

	/**
	 * Get the current page language code/slug.
	 *
	 * @return string Language code (e.g., 'en', 'es', 'fr') or empty string.
	 */
	public static function get_current_language(): string {
		$plugin = self::get_active_translation_plugin();

		if ( 'polylang' === $plugin ) {
			if ( function_exists( 'pll_current_language' ) ) {
				$lang = pll_current_language( 'slug' );
				if ( ! empty( $lang ) ) {
					return (string) $lang;
				}
			}

			if ( isset( $GLOBALS['polylang']->curlang->slug ) ) {
				return (string) $GLOBALS['polylang']->curlang->slug;
			}
		} elseif ( 'wpml' === $plugin ) {
			$lang = apply_filters( 'wpml_current_language', null );
			if ( ! empty( $lang ) ) {
				return (string) $lang;
			}

			if ( isset( $GLOBALS['sitepress'] ) && method_exists( $GLOBALS['sitepress'], 'get_current_language' ) ) {
				return (string) $GLOBALS['sitepress']->get_current_language();
			}

			if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
				return (string) ICL_LANGUAGE_CODE;
			}
		}

		return '';
	}

	/**
	 * Get the language of a specific form post.
	 *
	 * @param int $form_id Form post ID.
	 * @return string Language slug/code or empty string.
	 */
	public static function get_form_language( int $form_id ): string {
		if ( empty( $form_id ) ) {
			return '';
		}

		$plugin = self::get_active_translation_plugin();

		if ( 'polylang' === $plugin ) {
			if ( function_exists( 'pll_get_post_language' ) ) {
				$lang = pll_get_post_language( $form_id, 'slug' );
				if ( ! empty( $lang ) ) {
					return (string) $lang;
				}
			}

			if ( isset( $GLOBALS['polylang']->model->post ) && method_exists( $GLOBALS['polylang']->model->post, 'get_language' ) ) {
				$lang_obj = $GLOBALS['polylang']->model->post->get_language( $form_id );
				if ( $lang_obj && ! empty( $lang_obj->slug ) ) {
					return (string) $lang_obj->slug;
				}
			}
			if ( function_exists( 'pll_default_language' ) ) {
				$default_lang = pll_default_language( 'slug' );
				if ( ! empty( $default_lang ) ) {
					return (string) $default_lang;
				}
			}
		} elseif ( 'wpml' === $plugin ) {
			$post_type = get_post_type( $form_id );
			if ( ! $post_type ) {
				$post_type = Dragwyb_Post::POST_TYPE;
			}

			$lang = apply_filters(
				'wpml_element_language_code',
				null,
				array(
					'element_id'   => $form_id,
					'element_type' => $post_type,
				)
			);

			if ( ! empty( $lang ) ) {
				return (string) $lang;
			}

			if ( isset( $GLOBALS['sitepress'] ) && method_exists( $GLOBALS['sitepress'], 'get_language_for_element' ) ) {
				$lang = $GLOBALS['sitepress']->get_language_for_element( $form_id, 'post_' . $post_type );
				if ( ! empty( $lang ) ) {
					return (string) $lang;
				}
			}

			$default_lang = apply_filters( 'wpml_default_language', null );
			if ( ! empty( $default_lang ) ) {
				return (string) $default_lang;
			}
		}

		return '';
	}

	/**
	 * Get configured active languages from Polylang or WPML.
	 *
	 * @return array Array of languages indexed by slug, each with keys 'slug', 'name', 'flag', 'flag_url'.
	 */
	public static function get_configured_languages(): array {
		$plugin    = self::get_active_translation_plugin();
		$languages = array();

		if ( 'polylang' === $plugin ) {
			$poly_langs = array();
			if ( function_exists( 'pll_languages_list' ) ) {
				$poly_langs = pll_languages_list( array( 'fields' => false ) );
				if ( empty( $poly_langs ) ) {
					$poly_langs = pll_languages_list();
				}
			}
			if ( empty( $poly_langs ) && isset( $GLOBALS['polylang']->model ) && method_exists( $GLOBALS['polylang']->model, 'get_languages_list' ) ) {
				$poly_langs = $GLOBALS['polylang']->model->get_languages_list();
			}
			if ( empty( $poly_langs ) && function_exists( 'PLL' ) && isset( PLL()->model ) ) {
				if ( method_exists( PLL()->model, 'get_languages_list' ) ) {
					$poly_langs = PLL()->model->get_languages_list();
				} elseif ( isset( PLL()->model->languages ) && method_exists( PLL()->model->languages, 'get_list' ) ) {
					$poly_langs = PLL()->model->languages->get_list();
				}
			}

			if ( is_array( $poly_langs ) ) {
				foreach ( $poly_langs as $item ) {
					$lang_obj = null;
					if ( is_object( $item ) ) {
						$lang_obj = $item;
					} elseif ( is_string( $item ) && ! empty( $item ) ) {
						if ( function_exists( 'PLL' ) && isset( PLL()->model ) && method_exists( PLL()->model, 'get_language' ) ) {
							$lang_obj = PLL()->model->get_language( $item );
						} elseif ( isset( $GLOBALS['polylang']->model ) && method_exists( $GLOBALS['polylang']->model, 'get_language' ) ) {
							$lang_obj = $GLOBALS['polylang']->model->get_language( $item );
						}
					}

					if ( is_object( $lang_obj ) ) {
						$slug     = (string) ( $lang_obj->slug ?? '' );
						$name     = (string) ( $lang_obj->name ?? $slug );
						$flag     = (string) ( $lang_obj->flag ?? '' );
						$flag_url = (string) ( $lang_obj->flag_url ?? '' );
					} else {
						$slug     = is_string( $item ) ? $item : '';
						$name     = strtoupper( $slug );
						$flag     = '';
						$flag_url = '';
					}

					if ( ! empty( $slug ) ) {
						$languages[ $slug ] = array(
							'slug'     => $slug,
							'name'     => $name,
							'flag'     => $flag,
							'flag_url' => $flag_url,
						);
					}
				}
			}
		} elseif ( 'wpml' === $plugin ) {
			$wpml_langs = apply_filters( 'wpml_active_languages', null, 'orderby=id&order=asc' );
			if ( is_array( $wpml_langs ) ) {
				foreach ( $wpml_langs as $lang_data ) {
					if ( is_array( $lang_data ) ) {
						$slug     = (string) ( $lang_data['code'] ?? $lang_data['language_code'] ?? '' );
						$name     = (string) ( $lang_data['native_name'] ?? $lang_data['translated_name'] ?? '' );
						$flag_url = (string) ( $lang_data['country_flag_url'] ?? '' );
						$flag     = ! empty( $flag_url ) ? sprintf( '<img src="%s" alt="%s" width="16" height="11" />', esc_url( $flag_url ), esc_attr( $name ) ) : '';
						if ( ! empty( $slug ) ) {
							$languages[ $slug ] = array(
								'slug'     => $slug,
								'name'     => $name,
								'flag'     => $flag,
								'flag_url' => $flag_url,
							);
						}
					}
				}
			}
		}

		return $languages;
	}

	/**
	 * Check if a translated form exists in the target language and return its ID.
	 *
	 * @param int    $form_id Form post ID.
	 * @param string $lang    Target language slug/code.
	 * @return int Translated form post ID or 0 if not found.
	 */
	public static function get_form_translation( int $form_id, string $lang ): int {
		if ( empty( $form_id ) || empty( $lang ) ) {
			return 0;
		}

		$plugin = self::get_active_translation_plugin();

		if ( 'polylang' === $plugin ) {
			if ( function_exists( 'pll_get_post' ) ) {
				$translated_id = pll_get_post( $form_id, $lang );
				if ( ! empty( $translated_id ) ) {
					return absint( $translated_id );
				}
			}

			if ( isset( $GLOBALS['polylang']->model->post ) && method_exists( $GLOBALS['polylang']->model->post, 'get_translation' ) ) {
				$translated_id = $GLOBALS['polylang']->model->post->get_translation( $form_id, $lang );
				if ( ! empty( $translated_id ) ) {
					return absint( $translated_id );
				}
			}
		} elseif ( 'wpml' === $plugin ) {
			$post_type = get_post_type( $form_id );
			if ( ! $post_type ) {
				$post_type = Dragwyb_Post::POST_TYPE;
			}

			// Pass false for 3rd param to avoid returning the original ID if translation does not exist.
			$translated_id = apply_filters( 'wpml_object_id', $form_id, $post_type, false, $lang );
			if ( ! empty( $translated_id ) ) {
				return absint( $translated_id );
			}

			if ( function_exists( 'icl_object_id' ) ) {
				$translated_id = icl_object_id( $form_id, $post_type, false, $lang );
				if ( ! empty( $translated_id ) ) {
					return absint( $translated_id );
				}
			}
		}

		return 0;
	}

	/**
	 * Common function to resolve the form to render based on current language.
	 *
	 * Checks if a translation plugin (Polylang or WPML) is active.
	 * If active, gets current page language and checks if current render form language is the same.
	 * If not the same, checks if the form has a translation in the current render language.
	 * If it exists, returns that translated form ID to render; otherwise returns the original form ID.
	 *
	 * @param int $form_id Form ID requested to render.
	 * @return int Form ID to render (translated or original).
	 */
	public static function get_translated_form_id( int $form_id ): int {
		if ( empty( $form_id ) ) {
			return $form_id;
		}

		// 1. Check if translation plugin (Polylang or WPML) is active.
		if ( ! self::is_translation_plugin_active() ) {
			return $form_id;
		}

		// 2. Get current page language.
		$current_lang = self::get_current_language();
		if ( empty( $current_lang ) ) {
			return $form_id;
		}

		// 3. Check if current render form language is same or not.
		$form_lang = self::get_form_language( $form_id );

		// If form language matches current language, render current form.
		if ( ! empty( $form_lang ) && strtolower( $form_lang ) === strtolower( $current_lang ) ) {
			return $form_id;
		}

		// 4. If not same, check if render form exists in current render language.
		$translated_id = self::get_form_translation( $form_id, $current_lang );

		// 5. If exists and is a valid form post, return that form ID to render.
		if ( ! empty( $translated_id ) && $translated_id !== $form_id ) {
			$translated_post = get_post( $translated_id );
			if ( $translated_post && 'trash' !== $translated_post->post_status ) {
				return $translated_id;
			}
		}

		return $form_id;
	}
}
