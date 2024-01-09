<div class="rvx-import-page">
    <div class="rvx-import-page__header">
        <?php require_once REVIEWX_PRO_ADMIN_DIR_PATH . 'partials/external_review/admin-header.php'; ?>
    </div>

    <div class="rvx-import-page__devider-top"></div>
    <div class="rvx-import-page__body-wrapper">
        <div class="rvx-import-page__body-container"> 
            <div class="rvx-import-page__main-container">
                <div class="rvx-import-page__from-progress-container">
                    <div class="form-progress">
                        <progress class="rvx-form-progress-bar" min="0" max="100" value="0" step="33" aria-labelledby="form-progress-completion"></progress>
                        <div class="rvx-form-progress-indicator one active"></div>
                        <div class="rvx-form-progress-indicator two"></div>
                        <div class="rvx-form-progress-indicator three"></div>
                        <div class="rvx-form-progress-indicator four"></div>
                    </div>
                </div>
            
                <form method="post" id="rv-import-submit" rv-state="0">
                    <?php require_once REVIEWX_PRO_ADMIN_DIR_PATH . 'partials/external_review/import/import.php'; ?>
                    
                    <div class="rx-import-step">
                        <div class="rx-import-step-second"></div>
                    </div>

                    <div class="rx-import-step">
                        <div class="rx-import-step-third"></div>
                    </div>

                    <div class="rx-import-step">
                        <div class="rx-import-step-forth"></div>
                    </div>

                    <div class="reviewx-import-submit-btn-wrap">
                        <input type="submit" class="reviewx-import-submit-btn active" value="<?php echo esc_attr__( "Upload", "reviewx-pro" ); ?>">
                    </div>
                </div>

                <div class="rvx-import-page__devider-bottom-container ">
                    <div class="rvx-import-page__devider-bottom"></div>
                </div>

                <div class="rvx-import-page__button-container">
                    <div class="rvx-import-page__step-back">
                        <button class="rvx-step-button step-back" disabled><?php _e( "Back", "reviewx-pro" ); ?></button>
                    </div>
                    <div class="rvx-import-page__step-next">
                        <input type="submit" style="display:none;" class="reviewx-import-submit-btn active" value="<?php echo esc_attr__( "Upload", "reviewx-pro" ); ?>">
                        <button class="rvx-step-button step-next" disabled><?php _e( "Next", "reviewx-pro" ); ?></button>
                    </div>
                </div>
            </form>
            <div class="rx-loader-wrap">
                <div class="rx-import-loader"></div>
                <div class="rx-import-loader-massege">
                    <?php _e( "Review import in progress ...", "reviewx-pro" ); ?>
                </div>
            </div>
        </div>
    </div>
</div>
