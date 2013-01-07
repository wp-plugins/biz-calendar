<?php
/*
 Plugin Name: Biz Calendar
Plugin URI: http://residentbird.main.jp/bizplugin/
Description: 営業日・イベントカレンダーをウィジェットに表示するプラグインです。
Version: 1.3.0
Author:WordPress Biz Plugin
Author URI: http://residentbird.main.jp/bizplugin/
*/

new BizCalendarPlugin();

/**
 * プラグイン本体
 */
class BizCalendarPlugin{

	var $option_name = 'bizcalendar_options';
	var $adminUi;

	public function __construct(){
		$this->adminUi = new AdminUi($this->option_name);
		register_activation_hook(__FILE__, array(&$this,'on_activation'));	//プラグイン有効時の処理を設定
		register_deactivation_hook(__FILE__, array(&$this,'on_deactivation'));
		add_action( 'admin_init', array(&$this,'on_admin_init') );	//管理画面の初期化
		add_action( 'admin_menu', array(&$this, 'on_admin_menu'));			//管理画面にメニューを追加
		add_action( 'wp_print_styles', array(&$this,'on_print_styles'));				//cssの設定（管理画面以外)
		add_action( 'wp_print_scripts', array(&$this,'on_print_scripts'));				//javascriptの設定（管理画面以外)
		add_action( 'widgets_init', create_function( '', 'register_widget( "bizcalendarwidget" );' ) ); //ウィジェットの登録
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
			);
			update_option($this->option_name, $arr);
		}
	}

	function on_deactivation(){
		unregister_setting($this->option_name, $this->option_name );
		//delete_option($this->option_name);
		wp_deregister_style('biz-cal-style');
	}

	function on_print_scripts() {
		$path = WP_PLUGIN_DIR . '/biz-calendar/calendar.js';
		if(file_exists($path)){
			$url = plugins_url('calendar.js', __FILE__);
			wp_register_script('biz-cal-script', $url);
// 			wp_enqueue_script('biz-cal-script');
			wp_enqueue_script('biz-cal-script', $path, array('jquery'), false, true);
		}
	}

	function on_print_styles() {
		$path = WP_PLUGIN_DIR . '/biz-calendar/biz-cal.css';
		if(file_exists($path)){
			$url = plugins_url('biz-cal.css', __FILE__);
			wp_register_style('biz-cal-style', $url);
			wp_enqueue_style('biz-cal-style');
		}
	}

	function on_admin_init() {
		register_setting($this->option_name, $this->option_name);
		$this->adminUi->setUi();
	}

	public function on_admin_menu() {
		$page = add_options_page("BizCalendar設定", "BizCalendar設定", 'administrator', __FILE__, array(&$this, 'show_admin_page'));
	}

	public function show_admin_page() {
		$file = __FILE__;
		$option_name = $this->option_name;
		include_once('admin-view.php');
	}
}


class AdminUi {
	var $option_name;

	public function __construct( $option ){
		$this->option_name = $option;
	}

	public function setUi(){
		add_settings_section('fixed_holiday', '定休日', array(&$this,'text_fixed_holiday'), __FILE__);
		add_settings_field('id_holiday_title', '定休日の説明', array(&$this,'setting_holiday_title'), __FILE__, 'fixed_holiday');
		add_settings_field('id_chk_sun', '日曜日', array(&$this,'setting_chk_sun'), __FILE__, 'fixed_holiday');
		add_settings_field('id_chk_mon', '月曜日', array(&$this,'setting_chk_mon'), __FILE__, 'fixed_holiday');
		add_settings_field('id_chk_tue', '火曜日', array(&$this,'setting_chk_tue'), __FILE__, 'fixed_holiday');
		add_settings_field('id_chk_wed', '水曜日', array(&$this,'setting_chk_wed'), __FILE__, 'fixed_holiday');
		add_settings_field('id_chk_thu', '木曜日', array(&$this,'setting_chk_thu'), __FILE__, 'fixed_holiday');
		add_settings_field('id_chk_fri', '金曜日', array(&$this,'setting_chk_fri'), __FILE__, 'fixed_holiday');
		add_settings_field('id_chk_sat', '土曜日', array(&$this,'setting_chk_sat'), __FILE__, 'fixed_holiday');
		add_settings_field('id_chk_holiday', '祝日を定休日にする', array(&$this,'setting_chk_holiday'), __FILE__, 'fixed_holiday');

		add_settings_section('temp_holiday', '臨時休営業日', array(&$this,'text_temp_holiday'), __FILE__);
		add_settings_field('id_temp_holidays', '臨時休業日', array(&$this,'setting_temp_holidays'), __FILE__, 'temp_holiday');
		add_settings_field('id_temp_weekdays', '臨時営業日', array(&$this,'setting_temp_weekdays'), __FILE__, 'temp_holiday');

		add_settings_section('eventday', 'イベント', array(&$this,'text_eventday'), __FILE__);
		add_settings_field('id_eventday_title', 'イベントの説明', array(&$this,'setting_eventday_title'), __FILE__, 'eventday');
		add_settings_field('id_eventday_url', 'イベントのurl', array(&$this,'setting_eventday_url'), __FILE__, 'eventday');
		add_settings_field('id_eventdays', 'イベント日', array(&$this,'setting_eventdays'), __FILE__, 'eventday');
	}

	function  text_fixed_holiday() {
		echo '<p>定休日として設定する曜日をチェックします。<br>「祝日を定休日にする」にチェックすると、祝日が自動的に定休日になります。</p>';
	}

	function  text_temp_holiday() {
		echo '<p>臨時営業日・休業日を設定します。<br>YYYY-MM-DD (例 2001-01-01)の形式で登録します。複数登録する場合は改行してください。登録できる件数の上限はありません。</p>';
	}

	function  text_eventday() {
		echo '<p>イベントの説明、url、日にちを登録します。<br>イベント日は、YYYY-MM-DD (例 2001-01-01)の形式で登録します。複数登録する場合は改行してください。登録できる件数の上限はありません。</p>';
	}

	function setting_chk_sun() {
		$this->setting_chk( "sun" );
	}

	function setting_chk_mon() {
		$this->setting_chk( "mon" );
	}

	function setting_chk_tue() {
		$this->setting_chk( "tue" );
	}

	function setting_chk_wed() {
		$this->setting_chk( "wed" );
	}

	function setting_chk_thu() {
		$this->setting_chk( "thu" );
	}

	function setting_chk_fri() {
		$this->setting_chk( "fri" );
	}

	function setting_chk_sat() {
		$this->setting_chk( "sat" );
	}

	function setting_chk_holiday() {
		$this->setting_chk( "holiday" );
	}

	function setting_chk( $id ) {
		$options = get_option($this->option_name);
		$checked = (isset($options[$id]) && $options[$id]) ? $checked = ' checked="checked" ': "";
		$name = $this->option_name. "[$id]";

		echo "<input ".$checked." id='id_".$id."' name='".$name."' type='checkbox' />";
	}

	function setting_inputtext( $name, $size) {
		$options = get_option($this->option_name);
		$value = esc_html( $options[$name] );
		echo "<input id='{$name}' name='bizcalendar_options[{$name}]' size='{$size}' type='text' value='{$value}' />";
	}

	function setting_holiday_title() {
		$this->setting_inputtext("holiday_title", 40);
	}

	function setting_eventday_title() {
		$this->setting_inputtext("eventday_title", 40);
	}

	function setting_eventday_url() {
		$this->setting_inputtext("eventday_url", 60);
	}

	function setting_textarea( $name ) {
		$options = get_option($this->option_name);
		$value = esc_html( $options[ $name ] );
		echo "<textarea id='{$name}' name='bizcalendar_options[{$name}]' rows='7' cols='15'>{$value}</textarea>";
	}

	function setting_temp_holidays() {
		$this->setting_textarea("temp_holidays");
	}

	function setting_temp_weekdays() {
		$this->setting_textarea("temp_weekdays");
	}

	function setting_eventdays() {
		$this->setting_textarea("eventdays");
	}
}

class BizCalendarWidget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'BizCalendar', // Base ID
				'BizCalendar', // Name
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
		$options["holiday_title"] = esc_html($options["holiday_title"]);
		$options["eventday_title"] = esc_html($options["eventday_title"]);
		$holidays = null;
		if ( isset($options['holiday']) && $options["holiday"] == 'on'){
			$holidays = $this->getHolidays();
		}
		include_once('calendar-setting.js');
		echo "<div id='biz_calendar'></div>";
		echo $after_widget;
	}

	public function getHolidays(){
		$options = get_option( 'bizcalendar_options' );
		if ( $this->hasCache( $options) ){
			return $options["holiday_cache"];
		}

		$year = date('Y');
		//1-3月は前年の祝日を取得する
		$mon = date('n');
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
			return array();
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
		$options["holiday_cache_date"] = date( "Y/m", time());
		update_option('bizcalendar_options', $options);
		return $holidays;
	}

	private function hasCache($options){
		if( !isset($options["holiday_cache"]) || !isset($options["holiday_cache_date"])){
			return false;
		}
		if ( $options["holiday_cache_date"] != date( "Y/m", time()) ){
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