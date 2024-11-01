<?php

/*
 * Plugin Name: Vikinguard
 * Plugin URI: https://www.vikinguard.com
 * Description: it checks your site uptime and real user experience. This module provides all the infomation about your site\'s perfomance.
 * Author: Vikinguard. This is not just a software company.
 * Version: 5.0.1
 * Author URI: https://www.vikinguard.com
 */
function vikinguard_wpb_adding_heimdal_scripts()
{
    wp_register_script('heimdal', plugins_url('heimdal.js', __FILE__));
    wp_enqueue_script('heimdal');
}

function vikinguard_filter_customer($customer)
{
    if ($customer != null && $customer != "" && is_numeric($customer) && strlen($customer) == 32) {
        return true;
    }
    
    return false;
}

function vikinguard_esc_attr_e($a, $b)
{
    return esc_attr_e($a, $b);
}

function vikinguard_sanitize_customer($customer)
{
    return filter_var($customer, FILTER_SANITIZE_NUMBER_INT);
}

function vikinguard_filter_shop($shop)
{
    if ($shop != null && $shop != "" && is_numeric($shop) && strlen($shop) == 32) {
        return true;
    }
    
    return false;
}

function vikinguard_sanitize_shop($shop)
{
    return filter_var($shop, FILTER_SANITIZE_NUMBER_INT);
}

function vikinguard_filter_password($password)
{
    if ($password = ! null && $password != "" && strlen($password) > 5) {
        return true;
    }
    
    return false;
}

function vikinguard_sanitize_password($password)
{
    return filter_var($password, FILTER_UNSAFE_RAW);
}

function vikinguard_filter_action($action)
{
    if ($action == "reconfigured" || $action == "signup" || $action == "configuration" || $action == "multishop" || $action == "configured") {
        return true;
    }
    
    return false;
}

function vikinguard_sanitize_action($action)
{
    return sanitize_text_field($action);
}

// Make sure we don't expose any info if called directly
if (! function_exists('add_action')) {
    echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
    exit();
}

// For backwards compatibility, vikinguard_esc_attr_e was added in 2.8 and attribute_escape is from 2.8 marked as deprecated.
if (! function_exists('vikinguard_esc_attr_e')) {
    
    function vikinguard_vikinguard_esc_attr_e($text)
    {
        return attribute_escape($text);
    }
}

// The html code that goes in to the header
function add_Vikinguard_header()
{
    $customer = (string) get_option('HEIMDALAPM_CUSTOMER');
    $shop = (string) get_option('HEIMDALAPM_SHOP');
    
    if (! is_admin()) {
        ?>

<script type="text/javascript">
var heimdalparam={};

var shopid = "<?php echo esc_attr($shop); ?>";
var shopIndex = "10";
try{
	shopIndex = shopid.charAt(0);
	if (shopIndex === ""){
		shopIndex = "10";
	}else if (!(shopIndex >= '0' && shopIndex <= '9')) {
		shopIndex = "10";
	}
}catch(e){
	shopIndex = "10";	
}

var vikinguard_configCallBack = function(){
	BOOMR.init({
			beacon_url: "//eum.vikinguard.com"
	});
	BOOMR.addVar("customer","<?php echo esc_attr($customer); ?>");
	BOOMR.addVar("shop",shopid);
	BOOMR.addVar("version","WC5.0.1");
	vikinguard_info();
};

var vikinguard_info =function(){
	 for (key in heimdalparam){
    	BOOMR.addVar(key,heimdalparam[key]);
    
    }
};

var heimdaladdVar=function(key,value){
	heimdalparam[key]=value;
};

vikinguard_loadScript("//cdn.vikinguard.com/vikinguard-"+shopIndex+".js", vikinguard_configCallBack);

function vikinguard_loadScript(u, c){
    var h = document.getElementsByTagName('head')[0];
    var s = document.createElement('script');
    s.type = 'text/javascript';
    s.src = u;
    s.onreadystatechange = c;
    s.onload = c;
    h.appendChild(s);
   
}
</script>

<?php
    }
}

function print_Vikinguard_console()
{
    wp_enqueue_style("heimdalapm", plugins_url('heimdal.css', __FILE__));
    wp_enqueue_style("fill", plugins_url('heimdal.css', __FILE__));
    ?>

<div class="row">
	<div class="heimdal col-md-4">
		<img
			src="<?php echo esc_attr(plugins_url( 'heimdalfullbody.jpg', __FILE__ ));?>"
			alt=""></img>
	</div>
	<div class="steps col-md-8">
		<div class="row"><?php vikinguard_esc_attr_e('to access, clik on:' , 'Vikinguard');?>  https://vikinguard.com/heimdal/</div>
	</div>

	<!-- TODO: revisar que bloque es el bueno. este o el anterior 
				<div class="row"><?php vikinguard_esc_attr_e('to access, clik on:' , 'Vikinguard');?></div>
				<div class="row buttonheimdal">
					<h2><a href="https://vikinguard.com/heimdal/index.html?auto=true&email=<?php  echo esc_attr(get_option( 'HEIMDALAPM_EMAIL' ));?>&password=<?php  echo esc_attr(get_option( 'HEIMDALAPM_PASSWORD' ));?>&version=WC3.1.3" target="_blank">
 						Vikinguard Console</a>
					 </div></h2>
				</div>
				 -->


</div>
</div>





<?php
}

// Prints the admin menu where it is possible to add the tracking code
function print_Vikinguard_management()
{
    if (! current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to manage options for this blog.'));
    }

    wp_enqueue_style("heimdalapm", plugins_url('heimdal.css', __FILE__));

    // If we try to update the settings
    // TODO¿?
    $configurationEmail = get_option('HEIMDALAPM_EMAIL');
    $configurationPassword = get_option('HEIMDALAPM_PASSWORD');
    $customerid = (string) get_option('HEIMDALAPM_CUSTOMER');
    $shopid = (string) get_option('HEIMDALAPM_SHOP');

    // TODO
    $action = $_GET['action'];

    if (!vikinguard_filter_action($action)) {
        
        return mail_Vikinguard_Render();
    } else {
       
        $action = vikinguard_sanitize_action($action);

        if ($action == "reconfigured") {
            error_log("reconfigured", 0);

            return mail_Vikinguard_Render();
        }

        if ($action == "signup" && filter_var($_GET['heimdalapm_email'], FILTER_VALIDATE_EMAIL)) {

            update_option('HEIMDALAPM_EMAIL_TMP', sanitize_email($_GET['heimdalapm_email']));
            return signup_Vikinguard_Render();
        }
        if ($action == "configuration" && filter_var($_GET['heimdalapm_email'], FILTER_VALIDATE_EMAIL)) {

            update_option('HEIMDALAPM_EMAIL_TMP', sanitize_email($_GET['heimdalapm_email']));
            return configuration_Vikinguard_Render();
        }
        if ($action == "multishop" && filter_var($_GET['heimdalapm_email'], FILTER_VALIDATE_EMAIL) && vikinguard_filter_customer($_GET['heimdalapm_customer']) && vikinguard_filter_password($_GET['heimdalapm_password'])) {

            // update_option ( 'HEIMDALAPM_EMAIL_TMP', sanitize_email ( $_GET ['heimdalapm_email'] ) );
            update_option('HEIMDALAPM_EMAIL', sanitize_email($_GET['heimdalapm_email']));
            update_option('HEIMDALAPM_PASSWORD', vikinguard_sanitize_password($_GET['heimdalapm_password']));
            update_option('HEIMDALAPM_CUSTOMER', vikinguard_sanitize_customer($_GET['heimdalapm_customer']));
            return vikinguard_multishop_render();
        }

        if ($action == "configured" || filter_var($configurationEmail, FILTER_VALIDATE_EMAIL) && vikinguard_filter_customer($customerid) && vikinguard_filter_password($configurationPassword) && vikinguard_filter_shop($shopid)) {

            if ($action == "configured") {
                if (function_exists('wp_cache_clear_cache')) {

                    wp_cache_clear_cache();
                }

                if (filter_var($_GET['heimdalapm_email'], FILTER_VALIDATE_EMAIL) && vikinguard_filter_customer($_GET['heimdalapm_customer']) && vikinguard_filter_shop($_GET['heimdalapm_shop'])) {

                    // update_option ( 'HEIMDALAPM_EMAIL', sanitize_email ( $_GET ['heimdalapm_email'] ) );
                    // update_option ( 'HEIMDALAPM_PASSWORD', $_GET ['heimdalapm_password'] );
                    update_option('HEIMDALAPM_CUSTOMER', vikinguard_sanitize_customer($_GET['heimdalapm_customer']));
                    update_option('HEIMDALAPM_SHOP', vikinguard_sanitize_shop($_GET['heimdalapm_shop']));
                } else {

                    return mail_Vikinguard_Render();
                }
            }

            return configured_Vikinguard_Render();
        }
    }

    return mail_Vikinguard_Render();
    ?>
	
<?php
}

function vikinguard_multishop_render()
{
    // TODO
    $customer_info = stripcslashes($_GET['heimdalapm_customer_info']);
    $customer_info_decoded = json_decode($customer_info);
    $rights = $customer_info_decoded->rights;
    ?>
<div class="wrap">
	<img src="<?php echo esc_url(plugins_url( 'heimdal.png', __FILE__ )); ?>"
		alt="Heimdal logo" width="300px" />
	<h2>VIKINGUARD</h2>
	<hr />
	
		<?php
    if ($rights == "CUSTOMER_ADMIN" || $rights == "SHOP_ADMIN") {

        ?>
	<div id="register" class="form-signin">
		<span class="heimdal-inp-hed"><?php vikinguard_esc_attr_e('Mail', 'Vikinguard' );?></span>
		<span id="signupEmail"><?php echo esc_attr(get_option( 'HEIMDALAPM_EMAIL_TMP' ));?></span>
		<br> <input type="checkbox" id="signupTerms"
			data-error="<?php vikinguard_esc_attr_e('you must accept Vikinguard\'s terms', 'Vikinguard' );?>"
			required name="agree" class="heimdal-inp-hed" checked="checked"><?php vikinguard_esc_attr_e('I agree to the ', 'Vikinguard' );?> <a
			href="https://vikinguard.com/heimdal/EULA.html"> <?php vikinguard_esc_attr_e('Terms of Service.', 'Vikinguard' );?></a>
		</input>
		<div class="heimdal-form-pereira">
			<h3 class="form-signin-heading"><?php vikinguard_esc_attr_e('Select an existing web ...', 'Vikinguard' );?></h3>
			<select id="multishop_selector" name="shop" class="heimdal--input">
		        <?php

        foreach ($customer_info_decoded->shops as $element) {
            $desc = $element->shopName;
            $desc .= " (";
            $desc .= $element->shopURL;
            $desc .= ")";
            echo '<option value="' . esc_attr($element->shopId ). '">' . esc_attr($desc) . '</option>';
        }

        ?>
		        </select> <input type="submit" class="heimdal--button"
				value="<?php vikinguard_esc_attr_e('Use this web' , 'Vikinguard' );?>"
				onclick='shopSelected("<?php echo esc_attr(get_option ( 'HEIMDALAPM_CUSTOMER' ));?>","<?php vikinguard_esc_attr_e('You must accept the terms\n', 'Vikinguard' );?>");'>
			<br>
			<br>
			<br>
		</div>
		<?php
        if ($rights == "CUSTOMER_ADMIN") {

            ?>
		<div class="heimdal-form-pereira">
			<h3 class="form-signin-heading"><?php vikinguard_esc_attr_e('... or add a new one', 'Vikinguard' );?></h3>
			<ul>
				<li><span class="heimdal-inp-hed"
					title="<?php vikinguard_esc_attr_e('This is just a name to refer to your web.', 'Vikinguard' );?>"><?php vikinguard_esc_attr_e('Your New Web Name', 'Vikinguard' );?></span>
					<input type="text" id="addShopShopName" class="heimdal-inp"
					placeholder="<?php vikinguard_esc_attr_e('Web name', 'Vikinguard' );?>"
					required autofocus data-error="Customer" required name="customer"
					value="<?php echo esc_attr(bloginfo( 'name' )); ?>"> </input></li>
				<li><span class="heimdal-inp-hed"
					title="<?php vikinguard_esc_attr_e('Vikinguard is going to use this address to monitor the uptime of your web. Please, check the http and https is correct configured. Do not use private or localhost address, use your public ip or domain to allow Vikinguard to access to your web.', 'Vikinguard' );?>">
						<?php vikinguard_esc_attr_e('Your new web address', 'Vikinguard' );?></span>
					<input type="url" id="addShopUrl" class="heimdal-inp"
					placeholder="<?php vikinguard_esc_attr_e('Web URL', 'Vikinguard' );?>"
					required autofocus data-error="Customer" required name="customer"
					value="<?php echo esc_attr(bloginfo( 'url' )); ?>"> </input></li>
				<li><input id="enviar" class="heimdal--button"
					onclick='addShop("<?php echo esc_attr(get_option( 'HEIMDALAPM_EMAIL_TMP' ));?>","<?php echo esc_attr(get_option ( 'HEIMDALAPM_CUSTOMER' ));?>","<?php echo esc_attr(get_option ( 'HEIMDALAPM_PASSWORD' ));?>","<?php vikinguard_esc_attr_e('Web Name too short' , 'Vikinguard');?>\n","<?php vikinguard_esc_attr_e('Short url must start by http:// or https://', 'Vikinguard' );?>\n","<?php vikinguard_esc_attr_e('We have noticed that you configured Vikinguard to monitor a demo/test environment (localhost or 127.0.0.1). Please note that without real traffic and no public URL, you will not be able to monitor neither uptime neither real user experience and you will lose some important functionalities of our tool', 'Vikinguard' );?>","<?php vikinguard_esc_attr_e('You must accept the terms\n', 'Vikinguard' );?>","<?php vikinguard_esc_attr_e('Communication problem. Please try again later.', 'Vikinguard' );?>");'
					type="submit"
					value="<?php vikinguard_esc_attr_e('Add it!','Vikinguard' ) ?>"></input>

				</li>
				<br>
				<br>
			</ul>
		</div>
		
			
		<?php
        }
    }

    ?>
		<?php
    if ($rights == "NO_ADMIN") {

        ?>
					<div class="heimdal-form-pereira">

			<h3>
							<?php vikinguard_esc_attr_e('You do not have enough rights to configure this web.', 'Vikinguard' );?></span>
			</h3>
			<a onclick="reconfigured();"> <?php vikinguard_esc_attr_e('to reset the configuration' , 'Vikinguard');?></a>

		</div>
		<?php
    }
    ?>
				
	</div>


<?php
}

function mail_Vikinguard_Render()
{
    ?>

<div class="wrap">
		<img src="<?php echo esc_url(plugins_url( 'heimdal.png', __FILE__ )) ?>"
			alt="Heimdal logo" width="300px" />
		<h2>VIKINGUARD</h2>

		<hr />
		<div class="heimdal-form">
			<h3 class="form-signin-heading"><?php vikinguard_esc_attr_e('Please introduce your email to configure Vikinguard', 'Vikinguard'); ?></h3>

			<input type="email" id="checkEmail" class="heimdal--input"
				placeholder="<?php vikinguard_esc_attr_e('Mail address', 'Vikinguard'); ?>"
				required autofocus required name="mail" value=""
				title="<?php vikinguard_esc_attr_e('If you want to sign up, introduce your mail. If you are already registered, use your mail to sign in.', 'Vikinguard'); ?>"></input>
			<input
				onclick="sendMail('<?php vikinguard_esc_attr_e('Check your email' , 'Vikinguard');?>\n','<?php vikinguard_esc_attr_e('Communication problem. Please try again later.' , 'Vikinguard');?>')"
				id="enviar" type="submit" name="submit" class="heimdal--button"
				value="<?php vikinguard_esc_attr_e('Send it','Vikinguard' ) ?>"></input>
			<span class="heimdal-description"><?php vikinguard_esc_attr_e('Introduce your mail', 'Vikinguard'); ?></span>
		</div>

		<a href="https://vikinguard.com/support/" class="supportAdvise"><?php vikinguard_esc_attr_e('Do you have any problem? Please click here' , 'Vikinguard');?>.</a>

		<hr />
		<div class="row warning-note">
			<strong><?php vikinguard_esc_attr_e('We are not going to spam you' , 'Vikinguard');?>:</strong> <?php vikinguard_esc_attr_e('We are committed to keeping your e-mail address confidential. We do not sell, rent, or lease our subscription lists to third parties, and we will not provide your personal information to any third party individual, government agency, or company at any time unless compelled to do so by law.' , 'Vikinguard');?>
        				</div>


	</div>
<?php
}

function configuration_Vikinguard_Render()
{
    ?>
<div class="wrap">
		<img src="<?php echo esc_url(plugins_url( 'heimdal.png', __FILE__ )); ?>"
			alt="Heimdal logo" width="300px" />
		<h2>VIKINGUARD</h2>

		<hr />

		<h3 class="form-signin-heading"><?php vikinguard_esc_attr_e('Introduce your password to reconfigure the module.' , 'Vikinguard');?></h3>


		<div class="" id="sep">
			<ul>
				<li><span class="heimdal-inp-hed"><?php vikinguard_esc_attr_e('Mail' , 'Vikinguard');?></span>
					<input type="email" id="signinEmail" class="heimdal-inp"
					placeholder="<?php vikinguard_esc_attr_e('Mail address' , 'Vikinguard');?>"
					required autofocus
					data-error="<?php vikinguard_esc_attr_e('That email address is invalid' , 'Vikinguard');?>"
					required name="mail"
					value="<?php echo esc_attr(get_option( 'HEIMDALAPM_EMAIL_TMP' ));?>"> </input>
				</li>
				<li><span class="heimdal-inp-hed"><?php vikinguard_esc_attr_e('Password' , 'Vikinguard');?>
							</span> <input type="password" data-minlength="6"
					class="heimdal-inp" id="signinPassword"
					placeholder="<?php vikinguard_esc_attr_e('Password' , 'Vikinguard');?>"
					required name="password"
					data-error="<?php vikinguard_esc_attr_e('minimum 6 caracters' , 'Vikinguard');?>">
					</input></li>
				<li><span>					<?php vikinguard_esc_attr_e('Did you forget your password? Click' , 'Vikinguard');?> <a
						href="https://vikinguard.com/heimdal/index.html?action=forgot"
						target="_blank"><?php vikinguard_esc_attr_e(' here' , 'Vikinguard');?>.</a></span>
				</li>
				<li><input id="enviar"
					onclick='signupMail("<?php vikinguard_esc_attr_e('check your password' , 'Vikinguard');?>","<?php vikinguard_esc_attr_e('Communication problem. Please try again later' , 'Vikinguard');?>.")'
					class="heimdal--button" type="submit"
					value="<?php vikinguard_esc_attr_e('Sign in','Vikinguard' ) ?>"></input></li>
			</ul>
		</div>
	</div>
<?php
}

function configured_Vikinguard_Render()
{
    ?>

<div class="wrap">
		<img src="<?php echo esc_url(plugins_url( 'heimdal.png', __FILE__ )); ?>"
			alt="Heimdal logo" width="300px" />
		<h2>VIKINGUARD</h2>

		<hr />
		<div>
							<?php vikinguard_esc_attr_e('VIKINGUARD IS CONFIGURED' , 'Vikinguard');?> 
		</div>

		<a onclick="reconfigured();"> <?php vikinguard_esc_attr_e('to reset the configuration' , 'Vikinguard');?></a>
		<h2>
			<a
				href="https://vikinguard.com/heimdal/index.html?auto=true&email=<?php echo esc_attr(get_option( 'HEIMDALAPM_EMAIL' ));?>&password=<?php  echo esc_attr(get_option( 'HEIMDALAPM_PASSWORD'));?>&version=WC5.0.1"
				target="_blank"> Vikinguard Console</a>
	
	</div>
	</h2>
</div>
<?php
}

function signup_Vikinguard_Render()
{
    ?>
<div class="wrap">
	<img src="<?php echo esc_url(plugins_url( 'heimdal.png', __FILE__ )); ?>"
		alt="Heimdal logo" width="300px" />
	<h2>VIKINGUARD</h2>
	<hr />
	<div id="register" class="form-signin">
		<h3 class="form-signin-heading"><?php vikinguard_esc_attr_e('1) Select a password:', 'Vikinguard' );?></h3>
		<ul>
			<li><span class="heimdal-inp-hed"><?php vikinguard_esc_attr_e('Mail', 'Vikinguard' );?></span>
				<span id="signupEmail"><?php echo esc_attr(get_option( 'HEIMDALAPM_EMAIL_TMP' ));?></span>
			</li>
			<li><span class="heimdal-inp-hed"><?php vikinguard_esc_attr_e('Choose a Password', 'Vikinguard' );?></span>
				<input type="password" data-minlength="6" class="heimdal-inp"
				id="signupPassword"
				placeholder="<?php vikinguard_esc_attr_e('Password', 'Vikinguard' );?>"
				required name="password"
				data-error="<?php vikinguard_esc_attr_e('minimum 6 caracters', 'Vikinguard' );?>">
				</input></li>
			<li><span class="heimdal-inp-hed"><?php vikinguard_esc_attr_e('Confirm the Password', 'Vikinguard' );?></span>
				<input type="password" class="heimdal-inp" id="signupConfirm"
				data-match="#signupPassword"
				data-match-error="<?php vikinguard_esc_attr_e('Whoops, these don\'t match', 'Vikinguard' );?>"
				placeholder="<?php vikinguard_esc_attr_e('Confirm', 'Vikinguard' );?>"
				required name="confirm"></input></li>
		</ul>
		<h3 class="form-signin-heading"><?php vikinguard_esc_attr_e('2) Review/Modify:', 'Vikinguard' );?></h3>
		<ul>

			<li><span class="heimdal-inp-hed"
				title="<?php vikinguard_esc_attr_e('This is just a name to refer to your web.', 'Vikinguard' );?>"><?php vikinguard_esc_attr_e('Your web Name', 'Vikinguard' );?></span>
				<input type="text" id="signupCustomer" class="heimdal-inp"
				placeholder="<?php vikinguard_esc_attr_e('Customer name', 'Vikinguard' );?>"
				required autofocus data-error="Customer" required name="customer"
				value="<?php echo esc_attr(bloginfo( 'name' )); ?>"> </input></li>
			<li><span class="heimdal-inp-hed"
				title="<?php vikinguard_esc_attr_e('Vikinguard is going to use this address to monitor the uptime of your web. Please, check the http and https is correct configured. Do not use private or localhost address, use your public ip or domain to allow Vikinguard to access to your web.', 'Vikinguard' );?>">
					<?php vikinguard_esc_attr_e('Your Web Address', 'Vikinguard' );?></span>
				<input type="url" id="signupShop" class="heimdal-inp"
				placeholder="<?php vikinguard_esc_attr_e('Web URL', 'Vikinguard' );?>"
				required autofocus data-error="Customer" required name="customer"
				value="<?php echo esc_attr(bloginfo( 'url' )); ?>"> </input></li>
			<li><input type="checkbox" id="signupTerms"
				data-error="<?php vikinguard_esc_attr_e('you must accept Vikinguard\'s terms', 'Vikinguard' );?>"
				required name="agree" class="heimdal-inp-hed" checked="checked"><?php vikinguard_esc_attr_e('I agree to the ', 'Vikinguard' );?> <a
				href="https://vikinguard.com/heimdal/EULA.html"> <?php vikinguard_esc_attr_e('Terms of Service.', 'Vikinguard' );?></a>
				</input></li>
			<li><input id="enviar" class="heimdal--button"
				onclick='signup("<?php echo esc_attr(get_option( 'HEIMDALAPM_EMAIL_TMP' ));?>","<?php vikinguard_esc_attr_e('Customer Name too short' , 'Vikinguard');?>\n","<?php vikinguard_esc_attr_e('Short url must start by http:// or https://', 'Vikinguard' );?>\n","<?php vikinguard_esc_attr_e('Password too short', 'Vikinguard' );?>\n","<?php vikinguard_esc_attr_e('Whoops, these passwords do not match', 'Vikinguard' );?>\n","<?php vikinguard_esc_attr_e('Check your email configuration', 'Vikinguard' );?>\n","<?php vikinguard_esc_attr_e('You must accept the terms\n', 'Vikinguard' );?>","<?php vikinguard_esc_attr_e('We have noticed that you configured Vikinguard to monitor a demo/test environment (localhost or 127.0.0.1). Please note that without real traffic and no public URL, you will not be able to monitor neither uptime neither real user experience and you will lose some important functionalities of our tool', 'Vikinguard' );?>","<?php vikinguard_esc_attr_e('Communication problem. Please try again later.', 'Vikinguard' );?>","<?php echo esc_attr(get_option( 'HEIMDALAPM_EMAIL_TMP' ));?>");'
				type="submit"
				value="<?php vikinguard_esc_attr_e('Send it','Vikinguard' ) ?>"></input>
			</li>
		</ul>
	</div>

<?php
}

function add_Vikinguard_admin_page()
{
    if (function_exists('add_submenu_page')) {
        add_submenu_page('plugins.php', __('Vikinguard  Settings', 'Vikinguard'), __('Vikinguard  Settings'), 'manage_options', 'vikinguard-config', 'print_Vikinguard_management');
        add_menu_page(__('Vikinguard Console', 'Vikinguard'), __('Vikinguard Console'), 'manage_options', 'vikinguard-console', 'print_Vikinguard_console', null, 56.1);
    }
}

function add_Vikinguard_action_links($links)
{
    return array_merge(array(
        'settings' => '<a href="' . get_bloginfo('wpurl') . '/wp-admin/plugins.php?page=vikinguard-config">Settings</a>'
    ), $links);
}

add_action('wp_head', 'add_Vikinguard_header');

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    include_once ('includes/woocommerce-advance.php');
}

if (is_admin()) {
    load_plugin_textdomain('Vikinguard', false, dirname(plugin_basename(__FILE__)) . '/i18n');
    add_action('admin_enqueue_scripts', 'vikinguard_wpb_adding_heimdal_scripts');

    add_action('admin_menu', 'add_Vikinguard_admin_page');
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'add_Vikinguard_action_links');
}
?>
