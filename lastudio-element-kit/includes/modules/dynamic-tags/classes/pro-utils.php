<?php
namespace LaStudioKitThemeBuilder\Modules\DynamicTags\Classes;

use LaStudioKitThemeBuilder\Modules\LoopBuilder\Providers\Taxonomy_Loop_Provider;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The `ElementorPro\Core\Utils` helpers used by the dynamic tags.
 */
class Pro_Utils {

	/**
	 * TODO: Use core method instead (after Pro minimum requirements is updated).
	 * PR URL: https://github.com/elementor/elementor/pull/24092
	 */
	public static function _unstable_get_super_global_value( $super_global, $key ) {
		if ( ! isset( $super_global[ $key ] ) ) {
			return null;
		}

		if ( $_FILES === $super_global ) {
			return isset( $super_global[ $key ]['name'] ) ?
				static::sanitize_file_name( $super_global[ $key ] ) :
				static::sanitize_multi_upload( $super_global[ $key ] );
		}

		return wp_kses_post_deep( wp_unslash( $super_global[ $key ] ) );
	}
	public static function get_page_title( $include_context = true ) {
		$title = '';

		if ( is_singular() ) {
			/* translators: %s: Search term. */
			$title = get_the_title();

			if ( $include_context ) {
				$post_type_obj = get_post_type_object( get_post_type() );
				$title = sprintf( '%s: %s', $post_type_obj->labels->singular_name, $title );
			}
		} elseif ( is_search() ) {
			/* translators: %s: Search term. */
			$title = sprintf( esc_html__( 'Search Results for: %s', 'lastudio-kit' ), get_search_query() );

			if ( get_query_var( 'paged' ) ) {
				/* translators: %s: Page number. */
				$title .= sprintf( esc_html__( '&nbsp;&ndash; Page %s', 'lastudio-kit' ), get_query_var( 'paged' ) );
			}
		} elseif ( is_category() ) {
			$title = single_cat_title( '', false );

			if ( $include_context ) {
				/* translators: Category archive title. %s: Category name. */
				$title = sprintf( esc_html__( 'Category: %s', 'lastudio-kit' ), $title );
			}
		} elseif ( is_tag() ) {
			$title = single_tag_title( '', false );
			if ( $include_context ) {
				/* translators: Tag archive title. %s: Tag name. */
				$title = sprintf( esc_html__( 'Tag: %s', 'lastudio-kit' ), $title );
			}
		} elseif ( is_author() ) {
			$title = '<span class="vcard">' . get_the_author() . '</span>';

			if ( $include_context ) {
				/* translators: Author archive title. %s: Author name. */
				$title = sprintf( esc_html__( 'Author: %s', 'lastudio-kit' ), $title );
			}
		} elseif ( is_year() ) {
			$title = get_the_date( _x( 'Y', 'yearly archives date format', 'lastudio-kit' ) );

			if ( $include_context ) {
				/* translators: Yearly archive title. %s: Year. */
				$title = sprintf( esc_html__( 'Year: %s', 'lastudio-kit' ), $title );
			}
		} elseif ( is_month() ) {
			$title = get_the_date( _x( 'F Y', 'monthly archives date format', 'lastudio-kit' ) );

			if ( $include_context ) {
				/* translators: Monthly archive title. %s: Month name and a year. */
				$title = sprintf( esc_html__( 'Month: %s', 'lastudio-kit' ), $title );
			}
		} elseif ( is_day() ) {
			$title = get_the_date( _x( 'F j, Y', 'daily archives date format', 'lastudio-kit' ) );

			if ( $include_context ) {
				/* translators: Daily archive title. %s: Date. */
				$title = sprintf( esc_html__( 'Day: %s', 'lastudio-kit' ), $title );
			}
		} elseif ( is_tax( 'post_format' ) ) {
			if ( is_tax( 'post_format', 'post-format-aside' ) ) {
				$title = _x( 'Asides', 'post format archive title', 'lastudio-kit' );
			} elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) {
				$title = _x( 'Galleries', 'post format archive title', 'lastudio-kit' );
			} elseif ( is_tax( 'post_format', 'post-format-image' ) ) {
				$title = _x( 'Images', 'post format archive title', 'lastudio-kit' );
			} elseif ( is_tax( 'post_format', 'post-format-video' ) ) {
				$title = _x( 'Videos', 'post format archive title', 'lastudio-kit' );
			} elseif ( is_tax( 'post_format', 'post-format-quote' ) ) {
				$title = _x( 'Quotes', 'post format archive title', 'lastudio-kit' );
			} elseif ( is_tax( 'post_format', 'post-format-link' ) ) {
				$title = _x( 'Links', 'post format archive title', 'lastudio-kit' );
			} elseif ( is_tax( 'post_format', 'post-format-status' ) ) {
				$title = _x( 'Statuses', 'post format archive title', 'lastudio-kit' );
			} elseif ( is_tax( 'post_format', 'post-format-audio' ) ) {
				$title = _x( 'Audio', 'post format archive title', 'lastudio-kit' );
			} elseif ( is_tax( 'post_format', 'post-format-chat' ) ) {
				$title = _x( 'Chats', 'post format archive title', 'lastudio-kit' );
			}
		} elseif ( is_post_type_archive() ) {
			$title = post_type_archive_title( '', false );

			if ( $include_context ) {
				/* translators: Post type archive title. %s: Post type name. */
				$title = sprintf( esc_html__( 'Archives: %s', 'lastudio-kit' ), $title );
			}
		} elseif ( is_tax() ) {
			$title = single_term_title( '', false );

			if ( $include_context ) {
				$tax = get_taxonomy( get_queried_object()->taxonomy );
				/* translators: Taxonomy term archive title. 1: Taxonomy singular name, 2: Current taxonomy term. */
				$title = sprintf( esc_html__( '%1$s: %2$s', 'lastudio-kit' ), $tax->labels->singular_name, $title );
			}
		} elseif ( is_archive() ) {
			$title = esc_html__( 'Archives', 'lastudio-kit' );
		} elseif ( is_404() ) {
			$title = esc_html__( 'Page Not Found', 'lastudio-kit' );
		} elseif ( is_home() ) {
			$title = wp_get_document_title();
		}

		/**
		 * Page title.
		 *
		 * Filters the title of the page.
		 *
		 * By default different pages have different titles depending of the page
		 * context (archive, singular, 404 etc.). This hook allows developers to
		 * alter those titles.
		 *
		 * @since 1.0.0
		 *
		 * @param string $title Page title to be displayed.
		 */
		$title = apply_filters( 'elementor/utils/get_the_archive_title', $title );

		return $title;
	}

	/**
	 * Used to overcome core bug when taxonomy is in more then one post type
	 *
	 * @see https://core.trac.wordpress.org/ticket/27918
	 *
	 * @global array $wp_taxonomies The registered taxonomies.
	 *
	 *
	 * @param array  $args
	 * @param string $output
	 * @param string $operator
	 *
	 * @return array
	 */
	public static function get_taxonomies( $args = [], $output = 'names', $operator = 'and' ) {
		global $wp_taxonomies;

		$field = ( 'names' === $output ) ? 'name' : false;

		// Handle 'object_type' separately.
		if ( isset( $args['object_type'] ) ) {
			$object_type = (array) $args['object_type'];
			unset( $args['object_type'] );
		}

		$taxonomies = wp_filter_object_list( $wp_taxonomies, $args, $operator );

		if ( isset( $object_type ) ) {
			foreach ( $taxonomies as $tax => $tax_data ) {
				if ( ! array_intersect( $object_type, $tax_data->object_type ) ) {
					unset( $taxonomies[ $tax ] );
				}
			}
		}

		if ( $field ) {
			$taxonomies = wp_list_pluck( $taxonomies, $field );
		}

		return $taxonomies;
	}
	public static function get_the_archive_url() {
		$url = '';

		if ( Taxonomy_Loop_Provider::is_loop_taxonomy_strict() ) {
			global $wp_query;
			$url = get_term_link( $wp_query->loop_term );
		} elseif ( is_category() || is_tag() || is_tax() ) {
			$url = get_term_link( get_queried_object() );
		} elseif ( is_author() ) {
			$url = get_author_posts_url( get_queried_object_id() );
		} elseif ( is_year() ) {
			$url = get_year_link( get_query_var( 'year' ) );
		} elseif ( is_month() ) {
			$url = get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) );
		} elseif ( is_day() ) {
			$url = get_day_link( get_query_var( 'year' ), get_query_var( 'monthnum' ), get_query_var( 'day' ) );
		} elseif ( is_post_type_archive() ) {
			$url = get_post_type_archive_link( get_post_type() );
		}

		if ( ! is_wp_error( $url ) ) {
			return $url;
		}

		return '';
	}
	public static function set_global_authordata() {
		global $authordata;
		if ( ! isset( $authordata->ID ) ) {
			$post = get_post();

			if ( ! $post ) {
				return;
			}

			$authordata = get_userdata( $post->post_author ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}
	}

	/**
	 * Remove words from a sentence.
	 *
	 * @param string  $text
	 * @param integer $length
	 *
	 * @return string
	 */
	public static function trim_words( $text, $length ) {
		if ( ! $length ) {
			return $text;
		}

		$whitespace_pattern = '/\s+/u';
		$words = preg_split( $whitespace_pattern, $text, -1, PREG_SPLIT_NO_EMPTY );

		if ( count( $words ) > $length ) {
			$words = array_slice( $words, 0, $length );
		}

		return implode( ' ', $words );
	}

	private static function sanitize_multi_upload( $fields ) {
		return array_map( function( $field ) {
			return array_map( [ __CLASS__, 'sanitize_file_name' ], $field );
		}, $fields );
	}

	private static function sanitize_file_name( $file ) {
		$file['name'] = sanitize_file_name( $file['name'] );

		return $file;
	}
}
