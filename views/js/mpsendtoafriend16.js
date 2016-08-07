/**
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
 */

$(document).ready(function() {
	if (!!$.prototype.fancybox)
		$('#send_friend_button').fancybox({
			'hideOnContentClick': false
		});

	$('#send_friend_form_content .closefb').click(function(e) {
		$.fancybox.close();
		e.preventDefault();
	});

	$('#sendEmail').click(function(){
		var name = $('#friend_name').val();
		var email = $('#friend_email').val();
		if (name && email && !isNaN(id_product))
		{
			$.ajax({
				url: sendtoafriendAjax,
				type: "POST",
				headers: {"cache-control": "no-cache"},
				data: {
					action: 'sendToMyFriend', 
					secure_key: stf_secure_key,
					name: name,
					email: email, 
					id_product: id_product,
					'g-recaptcha-response': grecaptcha.getResponse()
				},
				dataType: "json",
				success: function(result) {
					$.fancybox.close();
					fancyMsgBox((result ? stf_msg_success : stf_msg_error), stf_msg_title);
				}
			});
		}
		else
			$('#send_friend_form_error').text(stf_msg_required);
	});
});