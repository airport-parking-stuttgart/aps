<div class="rvx-import-inner">
    <div style="display: flex;">
        <div class="import-with-data">
            <?php
            $rvx_all_review_column = ReviewXPro_Import_Review::all_review_column();
            foreach ( $rvx_all_review_column as $key => $value ) :
                $required_value = ReviewXPro_Import_Review::is_required( $key );
                ?>
                <p class="product-map-select">
                    <input type="hidden" value="<?php echo esc_attr( $key ); ?>">
                    <?php echo esc_html( $value ); 
                        if( $required_value === 'required' ) :
                    ?>
                    <span class = "rx-import-required-fld"> * </span>
                    <?php endif; ?>
                </p>
            <?php endforeach; ?>
        </div>

        <div class="import-with-data">
            <?php
            $rvx_external_column = ReviewXPro_Import_Review::column_list();
            $column_keys = array_keys($rvx_all_review_column);
            for ( $i = 0; $i < count( $rvx_all_review_column ); $i++ ) :
                $required_value = ReviewXPro_Import_Review::is_required( $column_keys[$i] );
                ?>
                <select class="rv-review-field-select" name="rv_review_fields[]" <?php echo esc_attr($required_value); ?>>
                    <option value=""><?php _e( 'Select Review Field', 'reviewx-pro' ); ?></option>
                    <?php foreach ( $rvx_external_column as $key => $column ) : ?>
                        <option value="<?php echo $key; ?>"><?php echo $column; ?></option>
                    <?php endforeach; ?>
                </select>
                <?php
            endfor;
            ?>
        </div>
    </div>
</div>
