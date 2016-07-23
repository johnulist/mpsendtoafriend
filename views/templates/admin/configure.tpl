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
*}
{if $smarty.const._PS_VERSION_|@addcslashes:'\'' < '1.6'}
	<fieldset id="main-panel">
		<legend>
			{l s='Send to a Friend -- with captcha' mod='mpsendtoafriend'}
		</legend>
		<p>
			{l s='This modules enables the reCAPTCHA by Google on the send to a friend form and protects the form from spambots.' mod='mpsendtoafriend'}<br />
		</p>
		<strong>{l s='Quick start' mod='mpsendtoafriend'}</strong>
		<ol style="list-style: decimal; margin-left: 20px">
			<li><a href="https://www.google.com/recaptcha/intro/index.html" target="_blank">{l s='Register your domain with reCAPTCHA and find your secret and site keys.' mod='mpsendtoafriend'}</a></li>
			<li>{l s='Enter your keys in the fields below and enable the captcha.' mod='mpsendtoafriend'}</li>
			<li>{l s='You are good to go! Should you find any problems, check out the GitHub repository: ' mod='mpsendtoafriend'} <a href="https://github.com/firstred/mpsendtoafriend" target="_blank">https://github.com/firstred/mpsendtoafriend</a></li>
		</ol>
	</fieldset>
	<br />
	<fieldset id="main-panel">
		<legend>
			{l s='Full version' mod='mpsendtoafriend'}
		</legend>
		<p>
			{l s='You can secure the following forms with the full version:' mod='mpsendtoafriend'}
		</p>
		<ol style="list-style: decimal; margin-left: 20px">
			<li>Login</li>
			<li>Register</li>
			<li>Password forgotten</li>
			<li>Contact</li>
			<li>Back Office login</li>
		</ol>
		<strong>
			{l s='The module is available on Addons:' mod='mpsendtoafriend'} <a href="http://addons.prestashop.com/en/19154-the-new-recaptcha.html" target="_blank">http://addons.prestashop.com/en/19154-the-new-recaptcha.html</a>
		</strong>
	</fieldset>
	<br />
{else}
	<div class="panel" id="main-panel">
		<h3><i class="icon icon-rocket"></i> {l s='Send to a Friend -- with captcha' mod='mpsendtoafriend'}</h3>
		<p>
			{l s='This modules enables the reCAPTCHA by Google on the send to a friend form and protects the form from spambots.' mod='mpsendtoafriend'}<br />
		</p>
		<strong>{l s='Quick start' mod='mpsendtoafriend'}</strong>
		<ol>
			<li><a href="https://www.google.com/recaptcha/intro/index.html" target="_blank">{l s='Register your domain with reCAPTCHA and find your secret and site keys.' mod='mpsendtoafriend'}</a></li>
			<li>{l s='Enter your keys in the fields below and enable the captcha.' mod='mpsendtoafriend'}</li>
			<li>{l s='You are good to go! Should you find any problems, check out the GitHub repository: ' mod='mpsendtoafriend'} <a href="https://github.com/firstred/mpsendtoafriend" target="_blank">https://github.com/firstred/mpsendtoafriend</a></li>
		</ol>
	</div>
	<div class="panel" id="main-panel">
		<h3><i class="icon icon-rocket"></i> {l s='Full version' mod='mpsendtoafriend'}</h3>
		<p>
			{l s='You can secure the following forms with the full version:' mod='mpsendtoafriend'}
		</p>
		<ol style="list-style: decimal; margin-left: 20px">
			<li>Login</li>
			<li>Register</li>
			<li>Password forgotten</li>
			<li>Contact</li>
			<li>Back Office login</li>
		</ol>
		<strong>
			{l s='The module is available on Addons:' mod='mpsendtoafriend'} <a href="http://addons.prestashop.com/en/19154-the-new-recaptcha.html" target="_blank">http://addons.prestashop.com/en/19154-the-new-recaptcha.html</a>
		</strong>
	</div>
{/if}