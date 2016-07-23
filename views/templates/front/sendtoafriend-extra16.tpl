{*
 * Copyright (C) Mijn Presta - All Rights Reserved
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 *
 * @author    Michael Dekker <prestashopaddons@mijnpresta.nl>
 * @copyright 2015 Mijn Presta
 * @license   proprietary
 * Intellectual Property of Mijn Presta
 *
*}

<li class="sendtofriend">
	<a id="send_friend_button" href="#send_friend_form">
		{l s='Send to a friend' mod='mpsendtoafriend'}
	</a>
	<div style="display: none;">
		<div id="send_friend_form">
			<h2  class="page-subheading">
				{l s='Send to a friend' mod='mpsendtoafriend'}
			</h2>
			<div class="row">
				<div class="product clearfix col-xs-12 col-sm-6">
					<img src="{$link->getImageLink($stf_product->link_rewrite, $stf_product_cover, 'home_default')|escape:'html':'UTF-8'}" height="{$homeSize.height}" width="{$homeSize.width}" alt="{$stf_product->name|escape:'html':'UTF-8'}" />
					<div class="product_desc">
						<p class="product_name">
							<strong>{$stf_product->name}</strong>
						</p>
						{$stf_product->description_short}
					</div>
				</div><!-- .product -->
				<div class="send_friend_form_content col-xs-12 col-sm-6" id="send_friend_form_content">
					<div id="send_friend_form_error"></div>
					<div id="send_friend_form_success"></div>
					<div class="form_container">
						<p class="intro_form">
							{l s='Recipient' mod='mpsendtoafriend'} :
						</p>
						<p class="text">
							<label for="friend_name">
								{l s='Name of your friend' mod='mpsendtoafriend'} <sup class="required">*</sup> :
							</label>
							<input id="friend_name" name="friend_name" type="text" value=""/>
						</p>
						<p class="text">
							<label for="friend_email">
								{l s='E-mail address of your friend' mod='mpsendtoafriend'} <sup class="required">*</sup> :
							</label>
							<input id="friend_email" name="friend_email" type="text" value=""/>
						</p>
						<p class="txt_required">
							<sup class="required">*</sup> {l s='Required fields' mod='mpsendtoafriend'}
						</p>
					</div>
					{if isset($sitekey) && $sitekey}
						<br />
						<div class="g-recaptcha" data-sitekey="{$sitekey|escape:'htmlall':'UTF-8'}" style="transform:scale(0.86);-webkit-transform:scale(0.86);transform-origin:0 0;-webkit-transform-origin:0 0;"></div>
					{/if}
					<p class="submit">
						<button id="sendEmail" class="btn button button-small" name="sendEmail" type="submit">
							<span>{l s='Send' mod='mpsendtoafriend'}</span>
						</button>&nbsp;
						{l s='or' mod='mpsendtoafriend'}&nbsp;
						<a class="closefb" href="#">
							{l s='Cancel' mod='mpsendtoafriend'}
						</a>
					</p>
				</div> <!-- .send_friend_form_content -->
			</div>
		</div>
	</div>
</li>
{addJsDef stf_secure_key=$stf_secure_key}
{addJsDefL name=stf_msg_success}{l s='Your e-mail has been sent successfully' mod='mpsendtoafriend' js=1}{/addJsDefL}
{addJsDefL name=stf_msg_error}{l s='Your e-mail could not be sent. Please check the e-mail address and try again.' mod='mpsendtoafriend' js=1}{/addJsDefL}
{addJsDefL name=stf_msg_title}{l s='Send to a friend' mod='mpsendtoafriend' js=1}{/addJsDefL}
{addJsDefL name=stf_msg_required}{l s='You did not fill required fields' mod='mpsendtoafriend' js=1}{/addJsDefL}