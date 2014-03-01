<?php
/*
 Plugin Name: Biz Calendar
Plugin URI: http://residentbird.main.jp/bizplugin/
Description: 営業日・イベントカレンダーをウィジェットに表示するプラグインです。
Version: 1.6.0
Author:WordPress Biz Plugin
Author URI: http://residentbird.main.jp/bizplugin/
*/

include_once ( dirname(__FILE__) . "/admin-ui.php" );
new BizCalendarPlugin();

class BC
{
	const VERSION = "1.6.0";
	const SHORTCODE = "showpostlist";
	const OPTIONS = "bizcalendar_options";

	public static function get_option(){
		return get_option(self::OPTIONS);
	}

	public static function update_option( $options ){
		if ( empty($options)){
			return;
		}
		update_option(self::OPTIONS, $options);
	}

	public static function enqueue_css_js(){
		wp_enqueue_style('biz-cal-style', plugins_url('biz-cal.css', __FILE__ ), array(), self::VERSION);
		wp_enqueue_script('biz-cal-script', plugins_url('calendar.js', __FILE__ ), array('jquery'), self::VERSION );
	}

	public static function localize_js(){
		wp_localize_script( 'biz-cal-script', 'bizcalOptions', self::get_option() );
		wp_localize_script( 'biz-cal-script', 'bizcalplugindir', plugin_dir_url( __FILE__ ) );
	}
}

/**
 * プラグイン本体
 */
class BizCalendarPlugin{

	var $option_name = 'bizcalendar_options';
	var $adminUi;

	public function __construct(){
		register_activation_hook(__FILE__, array(&$this,'on_activation'));
		add_action( 'admin_init', array(&$this,'on_admin_init') );
		add_action( 'admin_menu', array(&$this, 'on_admin_menu'));
		add_action( 'wp_enqueue_scripts', array(&$this,'on_enqueue_scripts'));
		add_action( 'widgets_init', create_function( '', 'register_widget( "bizcalendarwidget" );' ) );
	}

	function on_activation() {
		$tmp = get_option($this->option_name);
		if(!is_array($tmp)) {
			$arr = array(
					"holiday_title" => "定休日",
					"eventday_title" => "イベント開催日",
					"sun" => "on",
					"mon" => "",
					"tue" => "",
					"wed" => "",
					"thu" => "",
					"fri" => "",
					"sat" => "on",
					"holiday" => "on",
					"temp_holidays" =>"2013-01-02\n2013-01-03\n",
					"temp_weekdays" =>"",
					"eventdays" =>"",
					"event_url" =>"",
					"holiday_cache" =>"",
					"holiday_cache_date" =>"",
					"month_limit" =>"制限なし",
					"nextmonthlimit" =>"12",
					"prevmonthlimit" =>"12",
			);
			update_option($this->option_name, $arr);
		}
	}

	function on_enqueue_scripts() {
		if ( is_admin() ) {
			return;
		}
		BC::enqueue_css_js();
		BC::localize_js();
	}

	function on_admin_init() {
		$this->adminUi = new AdminUi( __FILE__ );
	}

	public function on_admin_menu() {
		$page = add_options_page("Biz Calendar設定", "Biz Calendar設定", 'administrator', __FILE__, array(&$this, 'show_admin_page'));
	}

	public function show_admin_page() {
		$file = __FILE__;
		$option_name = $this->option_name;
		include_once( dirname(__FILE__) . '/admin-view.php');
	}
}


class BizCalendarWidget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'BizCalendar', // Base ID
				'Biz Calendar', // Name
				array( 'description' => __( '営業日・イベントカレンダー', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( ! empty( $title ) ){
			echo $before_title . $title . $after_title;
		}
		$options = get_option( 'bizcalendar_options' );
		if ( isset($options['holiday']) && $options["holiday"] == 'on'){
			$options = $this->getHolidays($options);
		}
		echo "<div id='biz_calendar'></div>";
		echo $after_widget;
	}

	public function getHolidays( $options ){
		if ( $this->hasCache( $options) ){
			return $options;
		}

		$year = date_i18n('Y');
		//1-3月は前年の祝日を取得する
		$mon = date_i18n('n');
		if ( $mon < 4){
			$year -= 1;
		}

		$url = sprintf(
				'http://www.google.com/calendar/feeds/%s/public/full-noattendees?start-min=%s&start-max=%s&max-results=%d&alt=json' ,
				'outid3el0qkcrsuf89fltf7a4qbacgt9@import.calendar.google.com' , // 'japanese@holiday.calendar.google.com' ,
				$year.'-04-01' ,  // 取得開始日
				($year + 1).'-03-31' ,  // 取得終了日
				50              // 最大取得数
		);

		$results = file_get_contents($url);
		if ( !isset($results) ){
			return $options;
		}
		$results = json_decode($results, true);
		$holidays = array();
		foreach ($results['feed']['entry'] as $val ) {
			$date  = $val['gd$when'][0]['startTime'];
			$title = $val['title']['$t'];
			$holidays[$date] = $title;
		}
		ksort($holidays);

		//キャッシュを更新する
		$options["holiday_cache"] = $holidays;
		$options["holiday_cache_date"] = date_i18n( "Y/m");
		update_option('bizcalendar_options', $options);
		return $options;
	}

	private function hasCache($options){
		if( !isset($options["holiday_cache"]) || !isset($options["holiday_cache_date"])){
			return false;
		}
		if ( $options["holiday_cache_date"] != date_i18n( "Y/m") ){
			return false;
		}
		return true;
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'text_domain' );
		}
		?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?>
	</label> <input class="widefat"
		id="<?php echo $this->get_field_id( 'title' ); ?>"
		name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
		value="<?php echo esc_attr( $title ); ?>" />
</p>
<?php
	}
}

?>