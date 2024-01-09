<div class="rx-import-inner">
	<label class="rx-product-list-label-title"><?php _e( "Mapping", "reviewx-pro" ); ?></label>
	<label class="rx-product-list-label">
		<?php printf("%s %s %s",
			__("Select a column from the file to group your reviews.", "reviewx-pro"),
			"</br>",
			__("It is recommended to group by product title.", "reviewx-pro"));
		?>
	</label>
	<select id="rvx-foreign-product-id" name="rv_external_product_identifire" required>
		<option value=""><?php esc_html_e( "Select", "reviewx-pro" ); ?></option>
		<?php
		$col_items = ReviewXPro_Import_Review::column_list();
		if ( is_array( $col_items ) && count($col_items) != 0 ) :
			foreach ($col_items as $key => $col) :
				?>
				<option value="<?php echo esc_html( $key ); ?>">
					<?php echo esc_html( $col ); ?>
				</option>
			<?php
			endforeach;
		endif;
		?>
	</select>
</div>
