<?php
/**
 *
 * Plugin Name: Amazon Widget
 * Plugin URI:  http://www.linsoftware.com/wordpress-plugin-tutorial-widget/
 * Description: Display Amazon Product in Widget
 * Author: Linnea Wilhelm
 * Author URI: http://www.linsoftware.com
 * Version: 1.0.0
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: lsw-amazon-widget
 * Tags: amazon, widget, amazon affiliate
 **/

class Lsw_Amazon  {

	/**
	 * @var string
	 */
	public $version = '1.0.0';


	public static $text_domain = 'lsw-amazon-widget';

	/**
	 * @var Lsw_Amazon The single instance of the class
	 */
	protected static $_instance = null;


	protected function __construct() {
		$this->init();
	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public static function add_custom_meta_box( $post_type, $post ) {
		$post_types = apply_filters('lsw_amazon_post_types', array('post', 'page'));
		add_meta_box( 'lsw_amazon_settings_box', __( 'Amazon Product Settings', self::$text_domain ), array('Lsw_Amazon','render_meta_box'),
		$post_types, 'normal', 'default' );
	}

	public function init() {
		add_action( 'add_meta_boxes', array('Lsw_Amazon', 'add_custom_meta_box'), 10, 2 );
		add_action( 'save_post',  array('Lsw_Amazon', 'save_meta_details') );
		add_action( 'widgets_init', function(){ register_widget( 'Lsw_Amazon_Widget' );});
	}

	public static function render_meta_box() {
		global $post;
		$lsw_amazon = get_post_meta($post->ID, 'lsw_amazon', true);
		$title = isset($lsw_amazon['title']) ? $lsw_amazon['title'] : '';
		$asin = isset($lsw_amazon['asin']) ? $lsw_amazon['asin'] : '';
		?>
		<label for="lsw_title">Title   </label><input class="widefat" type="text" name="lsw_title"
		                                           id="lsw_title" value="<?php echo esc_attr($title); ?>"> <br>
		<label for="lsw_asin">ASIN  </label><input class="widefat" type="text" name="lsw_asin"
		                                         id="lsw_asin" value="<?php echo esc_attr($asin); ?>">
		<?php
	}

	public static function save_meta_details( $post_id ) {
		if( isset($_POST['lsw_asin']) && isset($_POST['lsw_title'] )) {
			$lsw_amazon = array('asin'=>sanitize_text_field($_POST['lsw_asin']),
				'title'=>sanitize_text_field($_POST['lsw_title']));
			update_post_meta( $post_id, 'lsw_amazon', $lsw_amazon);
		}
	}

}


class LSW_Amazon_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'lsw_amazon_widget', // Base ID
			'Amazon in Widget', // Name
			array( 'description' => 'Display an Amazon Product' )
		);

	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		if(!is_single()) {
			return;
		}
		global $post;
		$lsw_amazon = get_post_meta($post->ID, 'lsw_amazon', true);
		if(isset($lsw_amazon['title']) && isset($lsw_amazon['asin'])) {
			$tag = isset( $instance['tag'] ) ? $instance['tag'] : '';
			echo $args['before_widget'];
			?>
			<a href="<?php echo $this->getAmazonUrl( $lsw_amazon['asin'], $tag ); ?>">
				<?php echo sanitize_text_field( $lsw_amazon['title'] ); ?>
			</a>
			<?php
			echo $args['after_widget'];
		}
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		// outputs the options form on admin
		$tag  = isset( $instance['tag'] ) ? $instance['tag'] : '';
		?>
		<label for="tag">Affiliate Tag:</label><input type="text"
		         name=" <?php echo $this->get_field_name( 'tag' ); ?>"
		id="<?php echo $this->get_field_id( 'tag' ); ?>" value="<?php echo esc_attr($tag); ?>"> <br>
	<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
		$instance             = $old_instance;
		$instance['tag']     = sanitize_text_field( $new_instance['tag'] );
		return $instance;
	}

	private function getAmazonUrl($asin, $affiliate_tag) {
		return "http://www.amazon.com/dp/" . $asin . "?tag=" . $affiliate_tag;
	}
}


Lsw_Amazon::instance();
