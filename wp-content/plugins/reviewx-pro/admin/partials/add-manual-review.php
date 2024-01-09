<div class="rx-add-manual-review-wrapper">
	<div class="manual_review_body">
		<div class="manual_review-campaigns manual_review-view-wrapper manual_review_view">
			<div class="manual_review_header">
				<div class="manual_review_header_title">
					<h3>
						<?php echo __('Add New Review', 'reviewx-pro'); ?>
					</h3>
				</div>
			</div>
		</div>
	</div>
	<div class="manual_review_body">
		<div class="manual_review_pad_30">
			<form class="rx-form rx-form--label-top" name="attachmentForm" id="attachmentForm">
				<div class="rx-row">
					<div class="rx-col">
						<div class="rx-form-item">
							<label class="rx-form-item__label"><?php echo __('Select Product/CPT*', 'reviewx-pro'); ?></label>
							<div class="rx-form-item__content">
								<div class="rx-input">
									<select class="rx-input__inner" name="manual_review_product" id="manual_review_product">
										<option value=""><?php echo __('Select Product/CPT', 'reviewx-pro'); ?></option>
										<?php
										
										if (!is_null(self::reviewx_products_posts())) {
											foreach (self::reviewx_products_posts() as $product) :
												if (is_object($product)) {
													$id = $product->ID;
													$prod_name = $product->post_title;
												} else {
													$id = $product['ID'];
													$prod_name = $product['post_title'];
												}
										?>
												<option value="<?php echo $id; ?>"><?php echo $prod_name; ?></option>
											<?php endforeach;
										} else {
											global $wpdb;
											$data 	= [];
											if( get_option( '_rx_wc_active_check' ) == 1 ) {
											$table 	= $wpdb->prefix . 'posts';
											$sql 	= "SELECT ID, `post_title` FROM $table WHERE `post_type` = 'product' AND `post_status`= 'publish' ORDER BY post_title";
										
												$data 	= $wpdb->get_results($sql, ARRAY_A);
											}
											foreach ($data as $product) :
												if (is_object($product)) {
													$id = $product->ID;
													$prod_name = $product->post_title;
												} else {
													$id = $product['ID'];
													$prod_name = $product['post_title'];
												}
											?>
												<option value="<?php echo $id; ?>"><?php echo $prod_name; ?></option>
										<?php endforeach;
										}	?>

									</select>
								</div>
								<div class="rx-input-error"></div>
							</div>
						</div>
						<div class="rx-form-item">
							<label class="rx-form-item__label"><?php echo __('Select User*', 'reviewx-pro'); ?></label>
							<div class="rx-form-item__content">
								<div class="rx-input">
									<select class="rx-input__inner" name="manual_review_user" id="manual_review_user">
										<option value=""><?php echo __('Select User', 'reviewx-pro'); ?></option>
										<option value="anonymously"><?php echo __('Anonymously', 'reviewx-pro'); ?></option>
										<option value="custom"><?php echo __('Custom User', 'reviewx-pro'); ?></option>
										<?php
										foreach (self::reviewx_users() as $user) :
										?>
											<option value="<?php echo $user->ID; ?>"><?php echo $user->display_name; ?></option>
										<?php endforeach; ?>
									</select>
								</div>
								<div class="rx-input-error"></div>
							</div>
						</div>
						<div class="rx-form-item manual-review-author">
							<label class="rx-form-item__label"><?php echo __('Author Name*', 'reviewx-pro'); ?></label>
							<div class="rx-form-item__content">
								<div class="rx-input">
									<input type="text" name="manual_review_author" id="manual_review_author" class="rx-input__inner" placeholder="<?php echo __('Author Name', 'reviewx-pro'); ?>">
								</div>
								<div class="rx-input-name-error"></div>
							</div>
						</div>
						<div class="rx-form-item manual-review-author">
							<label class="rx-form-item__label"><?php echo __('Author Email*', 'reviewx-pro'); ?></label>
							<div class="rx-form-item__content">
								<div class="rx-input">
									<input type="email" name="manual_review_author_email" id="manual_review_author_email" class="rx-input__inner" placeholder="<?php echo __('Author Email', 'reviewx-pro'); ?>">
								</div>
								<div class="rx-input-email-error"></div>
							</div>
						</div>
						<div class="rx-form-item">
							<div class="product-review-tab">
								<div class="add_your_review">
									<div class="reviewx-rating">
										<table class="rx-criteria-table reviewx-rating">
											<tbody id="rx-manual-review-criteria">
												<?php
												echo apply_filters('rx_load_product_rating_type', \ReviewX\Controllers\Admin\Core\ReviewxMetaBox::get_option_settings());
												?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
						<div class="rx-form-item">
							<label class="rx-form-item__label"><?php echo __('Review Title', 'reviewx-pro'); ?></label>
							<div class="rx-form-item__content">
								<div class="rx-input">
									<input type="text" name="manual_review_title" id="manual_review_title" class="rx-input__inner" placeholder="<?php echo __('Review Title', 'reviewx-pro'); ?>">
								</div>
							</div>
						</div>
						<div class="rx-form-item">
							<label class="rx-form-item__label"><?php echo __('Review*', 'reviewx-pro'); ?></label>
							<div class="rx-form-item__content">
								<div class="rx-input">
									<?php
									$quicktags_settings = array('buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,close');
									wp_editor(
										'',
										'manual_review_text',
										array(
											'media_buttons' => false,
											'tinymce'       => true,
											'quicktags' 	=> true,
											'quicktags'     => $quicktags_settings,
											'textarea_rows' => 20,
											'editor_class'	=> 'rx-input__inner',
											'textarea_name' => 'manual_review_text'
										)
									);
									?>
								</div>
								<div class="rx-input-error"></div>
							</div>
						</div>
						<div class="rx-form-item">
							<label class="rx-form-item__label"></label>
							<div class="rx-form-item__content">
								<div class="rx-input">
									<div class="rx-images rx-flex-grid-100" id="rx-images">
										<p class="rx-comment-form-attachment">
											<label class="rx_upload_file rx-form-btn">
												<input id="manual_review_photo" name="manual_review_photo" type="button" data-multiple="true">
												<img src="<?php echo esc_url(assets('storefront/images/image.svg')); ?>" class="img-fluid">
												<span><?php echo __('Upload images', 'reviewx-pro'); ?></span>
											</label>
										</p>
									</div>
								</div>
							</div>
						</div>

						<div class="rx-form-item">
							<label class="rx-form-item__label"></label>
							<div class="rx-form-item__content">
								<div class="rx-input">
									<div class="rx-flex-grid-100">
										<div class="rx-form-video-element">
											<div class="rx-video-field">
												<select id="manual_review_video_source" name="manual_review_video_source" class="rx-input__inner">
													<option value="self"><?php echo __('Upload File', 'reviewx-pro'); ?></option>
													<option value="external"><?php echo __('External Link', 'reviewx-pro'); ?></option>
												</select>
												<span class="rx-selection-arrow"><b></b></span>
											</div>
											<div class="rx-video-field">
												<a class="rx-popup-video" id="rx-show-video-preview" href="">
													<img src="<?php echo esc_url(assets("storefront/images/video-icon.png")); ?>" alt="<?php echo esc_attr__('ReviewX', 'reviewx-pro'); ?>">
												</a>
												<div class="manual_review_self_video" id="manual_review_self_video">
													<label class="rx_upload_video">
														<input name="manual_review_video" id="manual_review_video" value="Upload Video" type="button">
														<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 66 37" style="enable-background:new 0 0 66 37;" xml:space="preserve">
															<path class="st0" d="M63.8,1.9l-16.5,9.9V5.2C47.3,2.3,45,0,42.1,0H5.2C2.3,0,0,2.3,0,5.2v26.7C0,34.7,2.3,37,5.2,37h36.9 c2.9,0,5.2-2.3,5.2-5.2v-6.2l16.5,9.5c0.9,0.6,2-0.1,2-1.1V3.1C65.8,2,64.7,1.4,63.8,1.9z" />
														</svg>
														<span><?php echo __('Upload a video', 'reviewx-pro'); ?></span>
													</label>
												</div>
												<div class="manual_review_external_video_url" id="manual_review_external_video_url">
													<input type="text" name="manual_review_set_video_url" id="manual_review_set_video_url" class="rx-input__inner" placeholder="<?php esc_attr__('Video URL', 'reviewx-pro'); ?>" title="<?php esc_attr__('E.g.: ', 'reviewx-pro') . esc_url('https://www.youtube.com/watch?v=HhBUmxEOfpc'); ?>">
												</div>
												<input type="hidden" name="manual_review_video_url" id="manual_review_video_url">
											</div>
										</div>
										<div class="manual_review_note_video" id="manual_review_note_video">
											<?php echo __('E.g.: ', 'reviewx-pro') . 'https://www.youtube.com/watch?v=HhBUmxEOfpc'; ?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="rx-form-item">
							<label class="rx-form-item__label">
								<?php echo __('Recommendation', 'reviewx-pro'); ?>
							</label>
							<div class="rx-form-item__content">
								<div class="rx-input">
									<div class="reviewx_recommended_list">
										<div class="reviewx_radio">
											<input id="recommend" name="manual_review_recommend_status" value="1" type="radio" checked="checked">
											<label for="recommend" class="radio-label happy_face">
												<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 80 80" style="enable-background:new 0 0 80 80;" xml:space="preserve">
													<style type="text/css">
														.happy_st0 {
															fill: #D0D6DC;
														}

														.happy_st1 {
															fill: #6D6D6D;
														}
													</style>
													<g>
														<radialGradient id="SVGID_1_" cx="40" cy="40" r="40" gradientUnits="userSpaceOnUse">
															<stop offset="0" style="stop-color:#62E2FF" />
															<stop offset="0.9581" style="stop-color:#3593FF" />
														</radialGradient>
														<path class="happy_st0 rx_happy" d="M40,0C18,0,0,18,0,40c0,22,18,40,40,40s40-18,40-40C80,18,62,0,40,0z M54,24c3.2,0,6,2.8,6,6c0,3.2-2.8,6-6,6
													c-3.2,0-6-2.8-6-6C48,26.8,50.8,24,54,24z M26,24c3.2,0,6,2.8,6,6c0,3.2-2.8,6-6,6c-3.2,0-6-2.8-6-6C20,26.8,22.8,24,26,24z M40,64
													c-10.4,0-19.2-6.8-22.4-16h44.8C59.2,57.2,50.4,64,40,64z" />
														<path class="happy_st1" d="M54,36c3.2,0,6-2.8,6-6c0-3.2-2.8-6-6-6c-3.2,0-6,2.8-6,6C48,33.2,50.8,36,54,36z" />
														<path class="happy_st1" d="M26,36c3.2,0,6-2.8,6-6c0-3.2-2.8-6-6-6c-3.2,0-6,2.8-6,6C20,33.2,22.8,36,26,36z" />
														<path class="happy_st1" d="M40,64c10.4,0,19.2-6.8,22.4-16H17.6C20.8,57.2,29.6,64,40,64z" />
													</g>
												</svg>
											</label>
										</div>
										<div class="reviewx_radio">
											<input id="neutral" name="manual_review_recommend_status" value="0" type="radio">
											<label for="neutral" class="radio-label neutral_face">
												<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 80 80" style="enable-background:new 0 0 80 80;" xml:space="preserve">
													<style type="text/css">
														.st0 {
															fill: #6D6D6D;
														}

														.st1 {
															fill: #D1D7DD;
														}
													</style>
													<g>
														<path class="st0" d="M54,36c3.2,0,6-2.8,6-6c0-3.2-2.8-6-6-6c-3.2,0-6,2.8-6,6C48,33.2,50.8,36,54,36z" />
														<path class="st0" d="M26,36c3.2,0,6-2.8,6-6c0-3.2-2.8-6-6-6c-3.2,0-6,2.8-6,6C20,33.2,22.8,36,26,36z" />
														<path class="st1" d="M40,0C18,0,0,18,0,40c0,22,18,40,40,40s40-18,40-40C80,18,62,0,40,0z M54,24c3.2,0,6,2.8,6,6c0,3.2-2.8,6-6,6
													c-3.2,0-6-2.8-6-6C48,26.8,50.8,24,54,24z M26,24c3.2,0,6,2.8,6,6c0,3.2-2.8,6-6,6c-3.2,0-6-2.8-6-6C20,26.8,22.8,24,26,24z" />
														<path class="st0" d="M58.4,57.3H21.6c-0.5,0-0.9-0.4-0.9-0.9v-7.1c0-0.5,0.4-0.9,0.9-0.9h36.8c0.5,0,0.9,0.4,0.9,0.9v7.1
													C59.3,56.9,58.9,57.3,58.4,57.3z" />
													</g>
												</svg>
											</label>
										</div>
										<div class="reviewx_radio">
											<input id="not_recommend" name="manual_review_recommend_status" value="0" type="radio">
											<label for="not_recommend" class="radio-label sad_face">
												<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 80 80" style="enable-background:new 0 0 80 80;" xml:space="preserve">
													<style type="text/css">
														.st0 {
															fill: #6D6D6D;
														}

														.st1 {
															fill: #D1D7DD;
														}
													</style>
													<g>
														<path class="st0" d="M54,36c3.2,0,6-2.8,6-6c0-3.2-2.8-6-6-6c-3.2,0-6,2.8-6,6C48,33.2,50.8,36,54,36z" />
														<path class="st0" d="M26,36c3.2,0,6-2.8,6-6c0-3.2-2.8-6-6-6c-3.2,0-6,2.8-6,6C20,33.2,22.8,36,26,36z" />
														<path class="st1" d="M40,0C18,0,0,18,0,40c0,22,18,40,40,40s40-18,40-40C80,18,62,0,40,0z M54,24c3.2,0,6,2.8,6,6c0,3.2-2.8,6-6,6
													c-3.2,0-6-2.8-6-6C48,26.8,50.8,24,54,24z M26,24c3.2,0,6,2.8,6,6c0,3.2-2.8,6-6,6c-3.2,0-6-2.8-6-6C20,26.8,22.8,24,26,24z" />
														<path class="st0" d="M40,42.8c-9.5,0-17.5,6.2-20.4,14.6h40.8C57.5,49,49.5,42.8,40,42.8z" />
													</g>
												</svg>
											</label>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="rx-form-item">
							<label class="rx-form-item__label">
								<?php echo __('Verified Review', 'reviewx-pro'); ?>
							</label>
							<div class="rx-form-item__content">
								<div class="rx-input">
									<div class="rx-control-wrapper">
										<label class="switch">
											<input class="rx-meta-field" type="checkbox" id="manual_review_verified" name="manual_review_verified" value="1">
											<span class="slider round"></span>
										</label>
									</div>
								</div>
							</div>
						</div>
						<div class="rx-form-item">
							<label class="rx-form-item__label">
								<?php echo __('Custom Date', 'reviewx-pro'); ?>
							</label>
							<div class="rx-form-item__content">
								<div class="rx-input">
									<div class="rx-control-wrapper">
										<label class="switch">
											<input class="rx-meta-field" type="checkbox" id="manual_review_custom_date" name="manual_review_custom_date" value="1">
											<span class="slider round"></span>
										</label>
									</div>
								</div>
							</div>
						</div>
						<div class="rx-form-item manual_review_date_area">
							<label class="rx-form-item__label"></label>
							<div class="rx-form-item__content">
								<div class="rx-input">
									<input type="datetime-local" name="manual_review_date" id="manual_review_date" class="rx-input__inner manual_review_date">
								</div>
							</div>
						</div>

						<div class="rx-form-item save_manual_review_area">
							<label class="rx-form-item__label"></label>
							<div class="rx-form-item__content">
								<div class="rx-input">
									<button class="quick-builder-submit-btn" name="save_manual_review" id="save_manual_review" type="button">
										<?php echo __('Save', 'reviewx-pro'); ?>
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>