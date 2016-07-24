{*
* 2016 Mijn Presta
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.md.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@mijnpresta.nl so we can send you a copy immediately.
*
*  @author    Michael Dekker <info@mijnpresta.nl>
*  @copyright 2016 Mijn Presta
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{if isset($sitekey) && $sitekey}
	<script type="text/javascript">
		var SendtoafriendCaptchaCallback = function() {
			$('.g-recaptcha-sendtoafriend').each(function(index, element) {
				grecaptcha.render(element, {
					'sitekey': '{$sitekey|escape:'javascript':'UTF-8'}'
				});
			});
		};
	</script>
	<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?onload=SendtoafriendCaptchaCallback&render=explicit" async defer></script>
{/if}