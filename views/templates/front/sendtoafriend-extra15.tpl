{*
 * 2016 Mijn Presta
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mijnpresta.nl so we can send you a copy immediately.
 *
 *  @author    Michael Dekker <info@mijnpresta.nl>
 *  @copyright 2016 Mijn Presta
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *
*}

<script type="text/javascript">
	{literal}
	$('document').ready(function(){
		$('#send_friend_button').fancybox({
			'hideOnContentClick': false
		});

		$('#sendEmail').click(function(){

			var name = $('#friend_name').val();
			var email = $('#friend_email').val();
			var id_product = $('#id_product_comment_send').val();
			if (name && email && !isNaN(id_product))
			{
				$.ajax({
					{/literal}url: "{$module_dir}mpsendtoafriend_ajax.php",{literal}
					type: "POST",
					headers: {"cache-control": "no-cache"},
					data: {
						action: 'sendToMyFriend',
						secure_key: '{/literal}{$stf_secure_key}{literal}',
						name: name,
						email: email,
						id_product: id_product,
						'g-recaptcha-response': grecaptcha.getResponse()
					},
					dataType: "json",
					success: function(result) {
						$.fancybox.close();
						var msg = result ? "{/literal}{l s='Your e-mail has been sent successfully' mod='mpsendtoafriend'}{literal}" : "{/literal}{l s='Your e-mail could not be sent. Please check the name, e-mail address and captcha and try again.' mod='mpsendtoafriend'}{literal}";
						var title = "{/literal}{l s='Send to a friend' mod='mpsendtoafriend'}{literal}";
						fancyMsgBox(msg, title);
					}
				});
			}
			else
				$('#send_friend_form_error').text("{/literal}{l s='You did not fill required fields' mod='mpsendtoafriend' js=1}{literal}");
		});
	});
	{/literal}
</script>
<li class="sendtofriend">
	<a id="send_friend_button" href="#send_friend_form">{l s='Send to a friend' mod='mpsendtoafriend'}</a>
</li>

<div style="display: none;">
	<div id="send_friend_form">
		<h2 class="title">{l s='Send to a friend' mod='mpsendtoafriend'}</h2>
		<div class="product clearfix">
			<img src="{$link->getImageLink($stf_product->link_rewrite, $stf_product_cover, 'home_default')|escape:'html'}" height="{$homeSize.height}" width="{$homeSize.width}" alt="{$stf_product->name|escape:html:'UTF-8'}" />
			<div class="product_desc">
				<p class="product_name"><strong>{$stf_product->name}</strong></p>
				{$stf_product->description_short}
			</div>
		</div>

		<div class="send_friend_form_content" id="send_friend_form_content">
			<div id="send_friend_form_error"></div>
			<div id="send_friend_form_success"></div>
			<div class="form_container">
				<p class="intro_form">{l s='Recipient' mod='mpsendtoafriend'} :</p>
				<p class="text">
					<label for="friend_name">{l s='Name of your friend' mod='mpsendtoafriend'} <sup class="required">*</sup> :</label>
					<input id="friend_name" name="friend_name" type="text" value=""/>
				</p>
				<p class="text">
					<label for="friend_email">{l s='E-mail address of your friend' mod='mpsendtoafriend'} <sup class="required">*</sup> :</label>
					<input id="friend_email" name="friend_email" type="text" value=""/>
				</p>
				<p class="txt_required"><sup class="required">*</sup> {l s='Required fields' mod='mpsendtoafriend'}</p>
			</div>
			{if isset($sitekey) && $sitekey}
				<br />
				<div class="g-recaptcha" data-sitekey="{$sitekey|escape:'htmlall':'UTF-8'}" style="transform:scale(0.86);-webkit-transform:scale(0.86);transform-origin:0 0;-webkit-transform-origin:0 0;"></div>
			{/if}
			<p class="submit">
				<input id="id_product_comment_send" name="id_product" type="hidden" value="{$stf_product->id}" />
				<a href="#" onclick="$.fancybox.close();">{l s='Cancel' mod='mpsendtoafriend'}</a>&nbsp;{l s='or' mod='mpsendtoafriend'}&nbsp;
				<input id="sendEmail" class="button" name="sendEmail" type="submit" value="{l s='Send' mod='mpsendtoafriend'}" />
			</p>
		</div>
	</div>
</div>