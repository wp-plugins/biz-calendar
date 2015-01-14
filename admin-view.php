<div class="wrap">
	<div class="icon32" id="icon-options-general">
		<br>
	</div>
	<h2>Biz Calendar 設定</h2>
	<h3>祝日ファイル登録</h3>
	<form id="biz-holidays" action="upload_holidays">
		<p>祝日ファイルは<a href="http://residentbird.main.jp/bizplugin/store/biz-holiday/" target="_blank">こちら</a>で販売しています</p>
        <input id="holidays-file" name="holidays-file" type="file"></input>
        <input id="holidays-upload" type="submit" value="アップロード"></input>
        <span id="file-upload-result" style="padding-left:10px; font-weight:bold;"></span>
    </form>
	<form action="options.php" method="post">
		<?php settings_fields( $option_name ); ?>
		<?php do_settings_sections( $file ); ?>
		<p class="submit">
			<input name="Submit" type="submit" class="button-primary"
				value="<?php esc_attr_e('Save Changes'); ?>" />
		</p>
	</form>
</div>
