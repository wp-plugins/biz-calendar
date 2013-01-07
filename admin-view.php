<div class="wrap">
	<div class="icon32" id="icon-options-general">
		<br>
	</div>
	<h2>Biz Calendar 設定</h2>
	<form action="options.php" method="post">
		<?php settings_fields( $option_name ); ?>
		<?php do_settings_sections( $file ); ?>
		<p class="submit">
			<input name="Submit" type="submit" class="button-primary"
				value="<?php esc_attr_e('Save Changes'); ?>" />
		</p>
	</form>
</div>
