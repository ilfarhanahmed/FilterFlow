<?php
namespace FilterFlow_Posts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Renderer {
	public static function sanitize_settings( array $settings ): array {
		$allowed_orderby        = array( 'date', 'title', 'menu_order', 'rand', 'comment_count', 'modified' );
		$allowed_order          = array( 'ASC', 'DESC' );
		$allowed_pagination     = array( 'numbers', 'load_more', 'none' );
		$allowed_excerpt_sources = array( 'smart', 'manual', 'wordpress' );
		$allowed_title_tags     = array( 'h2', 'h3', 'h4', 'h5', 'h6', 'div' );
		$allowed_date_sources   = array( 'published', 'modified' );
		$allowed_badge_positions = array( 'above-image', 'content', 'image-top-left', 'image-top-center', 'image-top-right', 'image-middle-left', 'image-center', 'image-middle-right', 'image-bottom-left', 'image-bottom-center', 'image-bottom-right' );

		$scalar = static function ( $value, string $default = '' ): string {
			return is_scalar( $value ) ? (string) $value : $default;
		};

		$categories = array();
		if ( isset( $settings['categories'] ) && is_array( $settings['categories'] ) ) {
			foreach ( $settings['categories'] as $category_id ) {
				if ( ! is_scalar( $category_id ) ) {
					continue;
				}

				$category_id = absint( $category_id );
				if ( $category_id ) {
					$categories[] = $category_id;
				}
			}
		}
		$categories = array_slice( array_values( array_unique( $categories ) ), 0, 200 );

		$orderby         = $scalar( $settings['orderby'] ?? 'date', 'date' );
		$order           = strtoupper( $scalar( $settings['order'] ?? 'DESC', 'DESC' ) );
		$pagination_type = $scalar( $settings['pagination_type'] ?? 'numbers', 'numbers' );
		$excerpt_source  = $scalar( $settings['excerpt_source'] ?? 'smart', 'smart' );
		$image_size      = sanitize_key( $scalar( $settings['image_size'] ?? 'large', 'large' ) );
		$title_tag       = strtolower( $scalar( $settings['card_title_tag'] ?? 'h3', 'h3' ) );
		$date_source     = strtolower( $scalar( $settings['date_source'] ?? 'published', 'published' ) );
		$date_format     = sanitize_text_field( $scalar( $settings['date_format'] ?? '', '' ) );
		$badge_position  = sanitize_key( $scalar( $settings['badge_position'] ?? 'content', 'content' ) );

		$allowed_image_sizes = array_merge( get_intermediate_image_sizes(), array( 'full' ) );
		$image_size          = in_array( $image_size, $allowed_image_sizes, true ) ? $image_size : 'large';

		return array(
			'categories'          => $categories,
			'posts_per_page'      => min( 24, max( 1, absint( $scalar( $settings['posts_per_page'] ?? 6, '6' ) ) ) ),
			'orderby'             => in_array( $orderby, $allowed_orderby, true ) ? $orderby : 'date',
			'order'               => in_array( $order, $allowed_order, true ) ? $order : 'DESC',
			'ignore_sticky'       => ! empty( $settings['ignore_sticky'] ),
			'exclude_current'     => ! empty( $settings['exclude_current'] ),
			'current_post_id'     => absint( $scalar( $settings['current_post_id'] ?? 0, '0' ) ),
			'show_image'          => ! empty( $settings['show_image'] ),
			'image_size'          => $image_size,
			'show_badge'          => ! empty( $settings['show_badge'] ),
			'link_badges'         => ! empty( $settings['link_badges'] ),
			'badge_limit'         => min( 10, max( 0, absint( $scalar( $settings['badge_limit'] ?? 0, '0' ) ) ) ),
			'badge_position'      => in_array( $badge_position, $allowed_badge_positions, true ) ? $badge_position : 'content',
			'card_title_tag'      => in_array( $title_tag, $allowed_title_tags, true ) ? $title_tag : 'h3',
			'show_excerpt'        => ! empty( $settings['show_excerpt'] ),
			'excerpt_source'      => in_array( $excerpt_source, $allowed_excerpt_sources, true ) ? $excerpt_source : 'smart',
			'excerpt_length'      => min( 80, max( 5, absint( $scalar( $settings['excerpt_length'] ?? 20, '20' ) ) ) ),
			'show_author'         => ! empty( $settings['show_author'] ),
			'author_prefix'       => sanitize_text_field( $scalar( $settings['author_prefix'] ?? __( 'By', 'filterflow-posts' ), __( 'By', 'filterflow-posts' ) ) ),
			'show_author_avatar'  => ! empty( $settings['show_author_avatar'] ),
			'author_avatar_size'  => min( 64, max( 16, absint( $scalar( $settings['author_avatar_size'] ?? 22, '22' ) ) ) ),
			'link_author'         => ! empty( $settings['link_author'] ),
			'show_date'           => ! empty( $settings['show_date'] ),
			'date_source'         => in_array( $date_source, $allowed_date_sources, true ) ? $date_source : 'published',
			'date_format'         => substr( $date_format, 0, 40 ),
			'show_reading_time'   => ! empty( $settings['show_reading_time'] ),
			'words_per_minute'    => min( 500, max( 100, absint( $scalar( $settings['words_per_minute'] ?? 220, '220' ) ) ) ),
			'show_comments'       => ! empty( $settings['show_comments'] ),
			'show_arrow'          => ! empty( $settings['show_arrow'] ),
			'pagination_type'     => in_array( $pagination_type, $allowed_pagination, true ) ? $pagination_type : 'numbers',
			'load_more_label'     => sanitize_text_field( $scalar( $settings['load_more_label'] ?? __( 'Load more', 'filterflow-posts' ), __( 'Load more', 'filterflow-posts' ) ) ),
			'no_posts_message'    => sanitize_text_field( $scalar( $settings['no_posts_message'] ?? __( 'No posts found.', 'filterflow-posts' ), __( 'No posts found.', 'filterflow-posts' ) ) ),
			'open_new_tab'        => ! empty( $settings['open_new_tab'] ),
		);
	}

	public static function get_query( array $settings, int $term_id = 0, int $page = 1 ): \WP_Query {
		$settings = self::sanitize_settings( $settings );

		if ( $term_id && ! empty( $settings['categories'] ) && ! in_array( $term_id, $settings['categories'], true ) ) {
			$term_id = 0;
		}

		$args = array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => $settings['posts_per_page'],
			'paged'               => max( 1, $page ),
			'orderby'             => $settings['orderby'],
			'order'               => $settings['order'],
			'ignore_sticky_posts' => $settings['ignore_sticky'],
			'no_found_rows'       => 'none' === $settings['pagination_type'],
		);

		if ( $term_id ) {
			$args['cat'] = $term_id;
		} elseif ( ! empty( $settings['categories'] ) ) {
			$args['category__in'] = $settings['categories'];
		}

		if ( $settings['exclude_current'] && $settings['current_post_id'] ) {
			$args['post__not_in'] = array( $settings['current_post_id'] );
		}

		/**
		 * Filter the query arguments used by FilterFlow.
		 *
		 * @param array $args     WP_Query arguments.
		 * @param array $settings Sanitized widget settings.
		 * @param int   $term_id  Active category term ID.
		 */
		$filtered_args = apply_filters( 'filterflow_posts/query_args', $args, $settings, $term_id );
		if ( is_array( $filtered_args ) ) {
			$args = $filtered_args;
		}

		return new \WP_Query( $args );
	}

	public static function render_posts( \WP_Query $query, array $settings ): string {
		$settings = self::sanitize_settings( $settings );

		ob_start();

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				self::render_card( get_the_ID(), $settings );
			}
		} else {
			echo '<div class="ffp-empty" role="status">' . esc_html( $settings['no_posts_message'] ) . '</div>';
		}

		wp_reset_postdata();
		return (string) ob_get_clean();
	}

	private static function render_card( int $post_id, array $settings ): void {
		$title       = get_the_title( $post_id );
		$permalink   = get_permalink( $post_id );
		$categories  = get_the_category( $post_id );
		$title_tag   = $settings['card_title_tag'];
		$author_id   = (int) get_post_field( 'post_author', $post_id );
		$author_name = $author_id ? (string) get_the_author_meta( 'display_name', $author_id ) : '';
		$has_meta    = $settings['show_author'] || $settings['show_date'] || $settings['show_reading_time'] || $settings['show_comments'];
		?>
		<?php
		$has_image        = $settings['show_image'] && has_post_thumbnail( $post_id );
		$badge_position   = $settings['badge_position'];
		$overlay_position = 0 === strpos( $badge_position, 'image-' );
		?>
		<article class="ffp-card ffp-card--badge-<?php echo esc_attr( $badge_position ); ?>">
			<?php if ( $settings['show_badge'] && 'above-image' === $badge_position && $has_image ) : ?>
				<?php self::render_badges( $categories, $settings, 'above-image' ); ?>
			<?php endif; ?>

			<?php if ( $has_image ) : ?>
				<div class="ffp-card__media">
					<a class="ffp-card__image" href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( $title ); ?>"<?php if ( $settings['open_new_tab'] ) : ?> target="_blank" rel="noopener noreferrer"<?php endif; ?>>
						<?php echo wp_kses_post( get_the_post_thumbnail( $post_id, $settings['image_size'], array( 'loading' => 'lazy', 'decoding' => 'async' ) ) ); ?>
					</a>
					<?php if ( $settings['show_badge'] && $overlay_position ) : ?>
						<?php self::render_badges( $categories, $settings, 'overlay' ); ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="ffp-card__body">
				<?php if ( $settings['show_badge'] && ( 'content' === $badge_position || ! $has_image ) ) : ?>
					<?php self::render_badges( $categories, $settings, 'content' ); ?>
				<?php endif; ?>

				<<?php echo esc_attr( $title_tag ); ?> class="ffp-card__title">
					<a href="<?php echo esc_url( $permalink ); ?>"<?php if ( $settings['open_new_tab'] ) : ?> target="_blank" rel="noopener noreferrer"<?php endif; ?>><?php echo esc_html( $title ); ?></a>
				</<?php echo esc_attr( $title_tag ); ?>>

				<?php if ( $settings['show_excerpt'] ) : ?>
					<?php $excerpt = self::get_card_excerpt( $post_id, $settings ); ?>
					<?php if ( '' !== $excerpt ) : ?>
						<div class="ffp-card__excerpt"><?php echo esc_html( $excerpt ); ?></div>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( $has_meta || $settings['show_arrow'] ) : ?>
					<div class="ffp-card__footer">
						<?php if ( $has_meta ) : ?>
							<div class="ffp-card__meta">
								<?php if ( $settings['show_author'] && '' !== $author_name ) : ?>
									<span class="ffp-card__meta-item ffp-card__author">
										<?php if ( $settings['show_author_avatar'] ) : ?>
											<?php echo wp_kses_post( get_avatar( $author_id, $settings['author_avatar_size'], '', $author_name, array( 'class' => 'ffp-card__author-avatar', 'loading' => 'lazy' ) ) ); ?>
										<?php else : ?>
											<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0"/></svg>
										<?php endif; ?>
										<span class="ffp-card__author-name">
											<?php if ( '' !== $settings['author_prefix'] ) : ?><span class="ffp-card__author-prefix"><?php echo esc_html( $settings['author_prefix'] ); ?></span> <?php endif; ?>
											<?php if ( $settings['link_author'] ) : ?>
												<a href="<?php echo esc_url( get_author_posts_url( $author_id ) ); ?>"><?php echo esc_html( $author_name ); ?></a>
											<?php else : ?>
												<?php echo esc_html( $author_name ); ?>
											<?php endif; ?>
										</span>
									</span>
								<?php endif; ?>

								<?php if ( $settings['show_date'] ) : ?>
									<span class="ffp-card__meta-item ffp-card__date">
										<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M7 2v3M17 2v3M3.5 9h17M5 4h14a2 2 0 0 1 2 2v14H3V6a2 2 0 0 1 2-2Z"/></svg>
										<?php echo esc_html( self::get_card_date( $post_id, $settings ) ); ?>
									</span>
								<?php endif; ?>

								<?php if ( $settings['show_reading_time'] ) : ?>
									<span class="ffp-card__meta-item ffp-card__reading-time"><?php echo esc_html( self::get_reading_time( $post_id, $settings['words_per_minute'] ) ); ?></span>
								<?php endif; ?>

								<?php if ( $settings['show_comments'] ) : ?>
									<span class="ffp-card__meta-item ffp-card__comments">
										<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 4h16v12H9l-5 4V4Z"/></svg>
										<?php echo esc_html( self::get_comment_count_label( $post_id ) ); ?>
									</span>
								<?php endif; ?>
							</div>
						<?php endif; ?>

						<?php if ( $settings['show_arrow'] ) : ?>
							<?php
							$arrow_label = sprintf(
								/* translators: %s: post title. */
								__( 'Read %s', 'filterflow-posts' ),
								$title
							);
							?>
							<a class="ffp-card__arrow" href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( $arrow_label ); ?>"<?php if ( $settings['open_new_tab'] ) : ?> target="_blank" rel="noopener noreferrer"<?php endif; ?>>
								<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
							</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</article>
		<?php
	}

	/**
	 * Build a clean card excerpt without repeating an Elementor Heading widget.
	 *
	 * Smart mode uses this order:
	 * 1. The post's manually entered WordPress excerpt.
	 * 2. Text Editor widgets from Elementor data (Heading widgets are ignored).
	 * 3. Post content with heading elements removed.
	 * 4. WordPress's generated excerpt as a final fallback.
	 */
	private static function render_badges( array $categories, array $settings, string $context = 'content' ): void {
		if ( empty( $categories ) ) { return; }
		$visible         = $settings['badge_limit'] > 0 ? array_slice( $categories, 0, $settings['badge_limit'] ) : $categories;
		$allowed_context = array( 'content', 'above-image', 'overlay' );
		$context         = in_array( $context, $allowed_context, true ) ? $context : 'content';
		$classes         = 'ffp-card__badges ffp-card__badges--' . $context;
		?>
		<div class="<?php echo esc_attr( $classes ); ?>" aria-label="<?php echo esc_attr__( 'Categories', 'filterflow-posts' ); ?>">
			<?php foreach ( $visible as $category ) : ?>
				<?php if ( $settings['link_badges'] ) : ?><a class="ffp-card__badge ffp-card__badge--term-<?php echo esc_attr( $category->term_id ); ?>" data-term="<?php echo esc_attr( $category->term_id ); ?>" href="<?php echo esc_url( get_category_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
				<?php else : ?><span class="ffp-card__badge ffp-card__badge--term-<?php echo esc_attr( $category->term_id ); ?>" data-term="<?php echo esc_attr( $category->term_id ); ?>"><?php echo esc_html( $category->name ); ?></span><?php endif; ?>
			<?php endforeach; ?>
		</div>
		<?php
	}


	private static function get_card_excerpt( int $post_id, array $settings ): string {
		$source         = $settings['excerpt_source'];
		$manual_excerpt = (string) get_post_field( 'post_excerpt', $post_id );
		$raw_excerpt    = '';

		if ( 'manual' === $source ) {
			$raw_excerpt = $manual_excerpt;
		} elseif ( 'wordpress' === $source ) {
			$raw_excerpt = (string) get_the_excerpt( $post_id );
		} else {
			$raw_excerpt = $manual_excerpt;

			if ( '' === trim( $raw_excerpt ) ) {
				$raw_excerpt = self::get_elementor_text_content( $post_id );
			}

			if ( '' === trim( $raw_excerpt ) ) {
				$raw_excerpt = self::get_post_content_without_headings( $post_id );
			}

			if ( '' === trim( $raw_excerpt ) ) {
				$raw_excerpt = (string) get_the_excerpt( $post_id );
			}
		}

		$excerpt = self::clean_text( $raw_excerpt );
		$excerpt = self::remove_leading_title( $excerpt, get_the_title( $post_id ) );

		if ( '' === $excerpt ) {
			return '';
		}

		return wp_trim_words( $excerpt, $settings['excerpt_length'], '…' );
	}

	/**
	 * Extract prose from Elementor Text Editor widgets while excluding headings.
	 */
	private static function get_elementor_text_content( int $post_id ): string {
		$elementor_data = get_post_meta( $post_id, '_elementor_data', true );

		if ( is_string( $elementor_data ) ) {
			$elementor_data = json_decode( $elementor_data, true );
		}

		if ( ! is_array( $elementor_data ) ) {
			return '';
		}

		$parts = array();
		self::collect_elementor_text_widgets( $elementor_data, $parts );

		return implode( ' ', $parts );
	}

	private static function collect_elementor_text_widgets( array $elements, array &$parts ): void {
		foreach ( $elements as $element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}

			$widget_type = isset( $element['widgetType'] ) && is_scalar( $element['widgetType'] )
				? (string) $element['widgetType']
				: '';
			$settings = isset( $element['settings'] ) && is_array( $element['settings'] )
				? $element['settings']
				: array();

			if ( 'text-editor' === $widget_type && isset( $settings['editor'] ) && is_scalar( $settings['editor'] ) ) {
				$text = self::clean_text( (string) $settings['editor'] );

				if ( '' !== $text ) {
					$parts[] = $text;
				}
			}

			if ( isset( $element['elements'] ) && is_array( $element['elements'] ) ) {
				self::collect_elementor_text_widgets( $element['elements'], $parts );
			}
		}
	}

	private static function get_post_content_without_headings( int $post_id ): string {
		$content = (string) get_post_field( 'post_content', $post_id );

		if ( '' === trim( $content ) ) {
			return '';
		}

		// Remove complete heading elements, including their text, before stripping HTML.
		$content = preg_replace( '#<h[1-6]\b[^>]*>.*?</h[1-6]>#is', ' ', $content );

		return is_string( $content ) ? $content : '';
	}

	private static function clean_text( string $text ): string {
		$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) ?: 'UTF-8' );
		$text = strip_shortcodes( $text );
		// Preserve word boundaries when Elementor content uses adjacent block-level elements.
		$text = preg_replace( '#<(?:br|/p|/div|/li|/ul|/ol|/blockquote|/section|/article|/h[1-6])\b[^>]*>#i', ' ', $text );
		$text = wp_strip_all_tags( (string) $text, true );
		// Remove any escaped or malformed heading fragments left by imported content.
		$text = preg_replace( '#</?h[1-6]\b[^>]*>#i', ' ', $text );
		$text = preg_replace( '/\s+/u', ' ', (string) $text );

		return trim( (string) $text );
	}

	private static function remove_leading_title( string $excerpt, string $title ): string {
		$clean_title = self::clean_text( $title );

		if ( '' === $excerpt || '' === $clean_title ) {
			return $excerpt;
		}

		$title_length = strlen( $clean_title );

		if ( 0 === strncasecmp( $excerpt, $clean_title, $title_length ) ) {
			$excerpt = substr( $excerpt, $title_length );
			$excerpt = ltrim( (string) $excerpt, " \t\n\r\0\x0B-–—:|.>" );
		}

		return trim( $excerpt );
	}

	private static function get_card_date( int $post_id, array $settings ): string {
		$format = $settings['date_format'];

		if ( 'modified' === $settings['date_source'] ) {
			return (string) get_the_modified_date( $format, $post_id );
		}

		return (string) get_the_date( $format, $post_id );
	}

	private static function get_comment_count_label( int $post_id ): string {
		$count = (int) get_comments_number( $post_id );

		return sprintf(
			/* translators: %s: number of comments. */
			_n( '%s comment', '%s comments', $count, 'filterflow-posts' ),
			number_format_i18n( $count )
		);
	}

	private static function get_reading_time( int $post_id, int $words_per_minute ): string {
		$content = get_post_field( 'post_content', $post_id );
		$words   = str_word_count( wp_strip_all_tags( strip_shortcodes( (string) $content ) ) );
		$minutes = max( 1, (int) ceil( $words / $words_per_minute ) );

		return sprintf(
			/* translators: %d: reading time in minutes. */
			_n( '%d min read', '%d min read', $minutes, 'filterflow-posts' ),
			$minutes
		);
	}

	public static function render_pagination( \WP_Query $query, array $settings, int $current_page = 1 ): string {
		$settings  = self::sanitize_settings( $settings );
		$max_pages = (int) $query->max_num_pages;

		if ( 'none' === $settings['pagination_type'] || $max_pages <= 1 ) {
			return '';
		}

		ob_start();

		if ( 'load_more' === $settings['pagination_type'] ) {
			if ( $current_page < $max_pages ) {
				printf(
					'<button type="button" class="ffp-load-more" data-page="%1$d">%2$s</button>',
					(int) ( $current_page + 1 ),
					esc_html( $settings['load_more_label'] )
				);
			}
		} else {
			echo '<nav class="ffp-pagination__numbers" aria-label="' . esc_attr__( 'Posts pagination', 'filterflow-posts' ) . '">';

			$start = max( 1, $current_page - 2 );
			$end   = min( $max_pages, $start + 4 );
			$start = max( 1, $end - 4 );

			if ( $current_page > 1 ) {
				printf( '<button type="button" class="ffp-page" data-page="%1$d" aria-label="%2$s">‹</button>', (int) ( $current_page - 1 ), esc_attr__( 'Previous page', 'filterflow-posts' ) );
			}

			for ( $page = $start; $page <= $end; $page++ ) {
				printf(
					'<button type="button" class="ffp-page%1$s" data-page="%2$d"%3$s>%2$d</button>',
					$page === $current_page ? ' is-active' : '',
					(int) $page,
					$page === $current_page ? ' aria-current="page"' : ''
				);
			}

			if ( $current_page < $max_pages ) {
				printf( '<button type="button" class="ffp-page" data-page="%1$d" aria-label="%2$s">›</button>', (int) ( $current_page + 1 ), esc_attr__( 'Next page', 'filterflow-posts' ) );
			}

			echo '</nav>';
		}

		return (string) ob_get_clean();
	}
}
