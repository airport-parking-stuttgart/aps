<div class="rvx-import-inner">
    <div class="rvx-data-mapping-tab">
        <div class="rvx-data-mapping-heading"><?php _e( "Mapping", "reviewx-pro" ); ?></div>
        <div class="rvx-data-mapping-title"><?php _e( "Specify system attributes and map them to your files headers.", "reviewx-pro" ); ?></div>
    </div>
    <div style="display: flex">
        <div class="import-with-data">
            <label class="product-map-select-title"><?php _e( "Headers from uploaded file", "reviewx-pro" ); ?></label>
            <?php
            if ( count( $review_group_product ) > 0 && count( $external_product_list ) > 0 ) :
                foreach ( $external_product_list as $external_product ) :
                    ?>
                    <select name="rv_external_product[]" class="rx-product-map-select">
                        <option value="none"><?php _e( 'Select Column Items', 'reviewx-pro' ); ?></option>
                        <?php foreach ( $external_product_list as $item ) : ?>
                            <option value="<?php echo $item; ?>"><?php echo $item; ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php
                endforeach;
            endif; 
            ?>
        </div>
    
        <div class="import-with-data">
            <label class="product-map-select-title"><?php _e( "System Attributes", "reviewx-pro" ); ?></label>
            <?php
            if ( count( $review_group_product ) > 0 && count( $wc_product_list ) > 0 ) :
                foreach ( $external_product_list as $external_product ) :
                    ?>
                    <select name="rv_wc_product[]" class="rx-product-map-select">
                        <option value="none"><?php _e( 'Select Existing Product', 'reviewx-pro' ); ?></option>
                        <?php foreach ( $wc_product_list as $key => $product ) : ?>
                            <option value="<?php echo $key; ?>"><?php echo $product; ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php
                endforeach;
            endif; 
            ?>
        </div>
    </div>
</div>
