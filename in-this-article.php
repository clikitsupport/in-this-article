<?php
/**
 * Plugin Name: In This Article
 * Version: 1.1.4
 * Description: Plugin that fetches all h2 and h3 tags from the post content
 * Author: ClikIT
 * Author URI:https://clikitnow.com/
 * Tested up to: 6.6
 * Requires PHP: 7.4
 * Text Domain: in-this-article
 * License: GPLv2 or later
 *
 * @package in-this-article
 */

if ( ! function_exists( 'itah_scripts' ) ) {
	/**
	 * Plugin scripts and styles.
	 */
	function itah_scripts() {
		wp_register_style( 'itah-styles', plugin_dir_url( __FILE__ ) . '/assets/css/itah-style.css', '', 1.0 );
		wp_register_script( 'itah-script', plugin_dir_url( __FILE__ ) . '/assets/js/itah-script.js', '', 1.0, true );
	}
}
add_action( 'wp_enqueue_scripts', 'itah_scripts' );

if ( ! function_exists( 'itah_admin_scripts' ) ) {
	/**
	 * Plugin admin scripts and styles.
	 */
	function itah_admin_scripts() {
		wp_enqueue_script( 'itah-admin-script', plugin_dir_url( __FILE__ ) . '/assets/admin/js/itah-admin-script.js', '', time(), true );
		wp_enqueue_style( 'itah-admin-style', plugin_dir_url( __FILE__ ) . '/assets/admin/css/itah-admin-style.css', '', time() );
	}
}
add_action( 'admin_enqueue_scripts', 'itah_admin_scripts' );

if ( ! function_exists( 'itah_admin_page' ) ) {
	/**
	 * Admin page of the plugin.
	 */
	function itah_admin_page() {
		add_menu_page(
			'In this Article',
			'In this Article',
			'manage_options',
			'in-this-article',
			'itah_in_this_article_callback',
			'dashicons-admin-tools',
			100,
		);
	}
}
add_action( 'admin_menu', 'itah_admin_page' );

if ( ! function_exists( 'itah_in_this_article_callback' ) ) {
	/**
	 * Callback for the admin page
	 */
	function itah_in_this_article_callback() {
		$shortcode = '[itah_in_this_article]';
		?>
		<div class="itah_guide">
			<div class="itah_heading">
				<h2>How to Use?</h2>
			</div>
			<div class="itah_description">
				<p>
					This plugin provides a shortcode that dynamically generates a structured table of contents (TOC) for your posts or pages. The TOC is built by fetching and organizing all h2 and h3 heading tags in the order they appear in your content. h3 headings are nested under their respective h2 headings, creating a clear hierarchical structure.
					Each item in the TOC is clickable, enabling smooth scrolling to the corresponding section in your post or page. This feature enhances navigation and improves the user experience by making it easier for readers to jump to specific content. 
					Simply add the shortcode to your post or page, and the plugin will automatically generate the clickable table of contents based on your headings.
				</p>
			</div>
		</div>
		<div class="wrap">
			<input type="text" id="itah-shortcode" value="<?php echo esc_attr( $shortcode ); ?>" readonly>
			<button class="button button-primary" id="itah_copy_btn">Copy Shortcode</button>			
		</div>
		<?php
	}
}

if ( ! function_exists( 'itah_in_this_article' ) ) {
	/**
	 * Shortcode to list the h2 and h3.
	 */
	function itah_in_this_article() {
		ob_start();
		wp_enqueue_style( 'itah-styles' );
		wp_enqueue_script( 'itah-script' );
		$dom = new DOMDocument();
		global $post;
		$headings = itah_get_headings_witah_hierarchy( $post->ID );
		if ( empty( $headings ) ) {
			return;
		}
		?>
		<div class="ita_in_this_article_wrapper">
			<h4>In this article</h4>
			<ul>
				<?php
				foreach ( $headings as $heading ) {
					$text        = trim( $heading['text'] );
					$id          = strtolower( preg_replace( '/[^a-zA-Z0-9]+/', '-', $text ) );
					$existing_id = $dom->getElementById( $id );
					if ( $existing_id ) {
						$id .= '-' . uniqid();
					}
					$tag = '';
					?>
					<li>
						<a href="#<?php echo esc_attr( $id ); ?>">
							<?php echo esc_html( $text ); ?>
						</a>
						<?php
						if ( ! empty( $heading['subheadings'] ) ) {
							?>
							<ul class="sub">
								<?php
								foreach ( $heading['subheadings'] as $subheading ) {
									$text        = trim( $subheading['text'] );
									$id          = strtolower( preg_replace( '/[^a-zA-Z0-9]+/', '-', $text ) );
									$existing_id = $dom->getElementById( $id );
									if ( $existing_id ) {
										$id .= '-' . uniqid();
									}
									?>
									<li>
										<a href="#<?php echo esc_attr( $id ); ?>">
											<?php echo esc_html( $subheading['text'] ); ?>
										</a>
										</li>
									<?php
								}
								?>
								
							</ul>
							<?php
						}
						?>
					</li>
					<?php
				}
				?>
			</ul>
		</div>
		<?php
		return ob_get_clean();
	}
}
add_shortcode( 'itah_in_this_article', 'itah_in_this_article' );

if ( ! function_exists( 'itah_add_ids_to_headings' ) ) {
	/**
	 * Add unique ID to all H2 and H3.
	 *
	 * @param string $content contents of the post.
	 */
	function itah_add_ids_to_headings( $content ) {
		if ( ! empty( $content ) ) {
			$dom = new DOMDocument();
			libxml_use_internal_errors( true );
			$dom->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ) );
			libxml_clear_errors();
			$xpath    = new DOMXPath( $dom );
			$headings = $xpath->query( '//h2 | //h3' );
			if ( $headings ) {
				foreach ( $headings as $heading ) {
					$text        = trim( $heading->nodeValue );
					$id          = strtolower( preg_replace( '/[^a-zA-Z0-9]+/', '-', $text ) );
					$existing_id = $dom->getElementById( $id );
					if ( $existing_id ) {
						$id .= '-' . uniqid();
					}
					$heading->setAttribute( 'id', $id );
				}
			}
			return $dom->saveHTML();
		} else {
			return $content;
		}
	}
}
add_filter( 'the_content', 'itah_add_ids_to_headings' );

if ( ! function_exists( 'itah_get_headings_witah_hierarchy' ) ) {
	/**
	 * Get all h2 and h3 in parent and sub hierarchy.
	 *
	 * @param int $post_id ID of the post.
	 */
	function itah_get_headings_witah_hierarchy( $post_id ) {
		$post    = get_post( $post_id );
		$content = $post->post_content;
		$dom     = new DOMDocument();
		libxml_use_internal_errors( true );
		$dom->loadHTML( mb_convert_encoding( $content, 'HTML-ENTITIES', 'UTF-8' ) );
		libxml_clear_errors();
		$xpath      = new DOMXPath( $dom );
		$headings   = array();
		$current_h2 = array();

		foreach ( $xpath->query( '//h2 | //h3' ) as $heading ) {
			$tag  = $heading->nodeName;
			$text = trim( $heading->nodeValue );

			if ( 'h2' === $tag ) {
				$current_h2 = array(
					'tag'       => 'h2',
					'text'      => $text,
					$subheading => array(),
				);
				$headings[] = $current_h2;
			} elseif ( 'h3' === $tag && $current_h2 ) {

				$current_h2['subheadings'][] = array(
					'tag'  => 'h3',
					'text' => $text,
				);

				$headings[ array_key_last( $headings ) ] = $current_h2;
			}
		}
		return $headings;
	}
}
