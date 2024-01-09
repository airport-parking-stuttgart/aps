<div class="rvx-import-step">
    <div class="rx-import-inner rx-import-step-first active">
        <div class="rvx-import-step__csv-body">
            <label class="rvx-import-step__csv-import-title"><?php _e( "Upload a file", "reviewx-pro" ); ?></label>
            <label class="rvx-import-step__subtitle">
                <?php
                printf( "%s %s %s %s %s",
                    __( "You can upload", "reviewx-pro" ),
                    "<strong>",
                    __( "CSV", "reviewx-pro" ),
                    "</strong>",
                    __( "files to import your product reviews.", "reviewx-pro" ) );
                ?>
            </label>
            <div class="rvx-import-field">
                <div class="rvx-import-field__wrapper">
                    <!-- actual upload which is hidden -->
                    <input type="file" name="reviewx_import_review_file" accept=".csv" id="rvx-import-file" hidden/>

                    <!-- our custom upload button -->
                    <label class="rvx-import-field__label" for="rvx-import-file"><?php _e( "Choose File", "reviewx-pro" ); ?></label>

                    <!-- name of file chosen -->
                    <span class="rvx-import-field__file-chosen" id="rvx-file-chosen"><?php _e( "No file chosen", "reviewx-pro" ); ?></span>
                </div>   
            </div>
            <div class="rvx-import-step__warning-wrapper">
                <p class="rvx-import-step__warning-msg"> Please choose a file* </p>
            </div>
        </div>
    </div>
</div>
