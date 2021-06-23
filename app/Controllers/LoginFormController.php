<?php

namespace CTOSLS\App\Controllers;


class LoginFormController
{
  /**
   * LoginForm constructor.
   */
  public function __construct()
  {
    // Run the class if is in wp-login page
    if (strpos($GLOBALS['_SERVER']['REQUEST_URI'], 'wp-login') !== false) {
      include_once ABSPATH . 'wp-admin/includes/plugin.php';

      if (! \is_plugin_active('user-registration/user-registration.php')) {
        add_action('login_form', [$this, 'authenticator']);
        add_action('authenticate', [$this, 'authenticate'], 30, 3);
        add_action('login_head', [$this, 'styles']);
        add_filter('login_headerurl', [$this, 'url']);
        add_filter('login_headertext', [$this, 'title']);
        add_action('login_head', [$this, 'remove_shake'], 20);
        add_filter('login_redirect', [$this, 'redirect'], 10, 3);
        add_filter('widget_text', 'do_shortcode');
        add_filter('admin_body_class', [$this, 'dashboard']);
      }
    }
  }

  /**
   * Creates and gets unique ID for login user.
   *
   * This will prevent a race condition if multiple people try to login at the same time.
   *
   * @access private
   * @param  string $session_token optional If set, it will get uniqid from transients. If
   *     not set, it will generate one.
   * @return bool|string
   */
  private function uniqid($session_token)
  {
    // edit these if you wish
    if (! isset($session_token)) {
      $session_token = '';
    }

    $key_length   = 12;
    $uniqid_length = 64;
    $transient_expires = 10 * 60 * 60;

    if (! $session_token) {
      // generate new uniqid. This should be unique for all users who request wp-login form.
      $key = bin2hex(openssl_random_pseudo_bytes($key_length));
      $uniqid = bin2hex(openssl_random_pseudo_bytes($uniqid_length));

      $transient_name = 'auth_uniqid_' . $key;
      $transient_value = $key . $uniqid;

      set_transient($transient_name, $transient_value, $transient_expires);

      return $transient_value;
    } else {
      // need to get the uniqid
      $transient_name = 'auth_uniqid_' . substr($session_token, 0, ($key_length * 2));	// bin2hex doubles the key length
      $transient_value = get_transient($transient_name);

      if ($transient_value == $session_token) {
        return true;
      } else {
        // transient is either wrong or expired. Either way, let's clean it up.
        delete_transient($transient_name);
        return false;
      }
    }
  }

  /**
   * Adds one or more classes to the body tag in the dashboard.
   *
   * @link https://wordpress.stackexchange.com/a/154951/17187
   * @param  String $classes Current body classes.
   * @return String          Altered body classes.
   */
  public function dashboard($classes)
  {
    $loginScreen = get_option( 'aios_custom_login_screen' );
    $loginScreen = ! empty($loginScreen) ? $loginScreen : 'default';

    $tdp_class  = '';
    if ($loginScreen == 'thedesignpeople') {
      $tdp_class = "$classes tdp-dashboard";
    }

    return $tdp_class;
  }

  /**
   * Custom Fields on WP Login.
   *
   * @since 3.1.5
   *
   * @access protected
   */
  public function authenticator()
  {
    if (is_plugin_active('user-registration/user-registration.php')) {
      return;
    }

    // set uniqid
    $uniqid = $this->uniqid('');

    $loginScreen = get_option( 'aios_custom_login_screen' );
    $loginScreen = ! empty($loginScreen) ? $loginScreen : 'default';

    echo '<div id="imhuman-container">
				<p>Security</p>
				<div class="imcontainer">
					<label for="imhuman"><input type="checkbox" name="imhuman" id="imhuman" value="imnotarobot"> I\'m not a robot</label>
					<input type="hidden" name="session_token" id="session_token" value="' . $uniqid . '"></div>
				</div>';

    if($this->aios_original_login()){
      echo '<div id="rm-rb"><div class="clear"></div></div>';
	  echo '<div id="powered-by">Powered by <a href="#" target="_blank">Code Trajectory</a></div>';
    }
  }

  /**
   * Authenticate on login
   *
   * @param $user
   * @param $username
   * @param $password
   * @return mixed
   * @since 3.1.5
   *
   * @access protected
   */
  public function authenticate($user, $username, $password)
  {
    if (isset($_POST['imhuman'])) {
      if ($_POST['imhuman'] == 'imnotarobot') {
        if ($this->uniqid($_POST['session_token'])) {
          return $user;
        } else {
          return new \WP_Error( 'denied', 'Session expired, Create New <a href="' . admin_url() . '">Session Create</a>.' );
        }
      }
    }

    return false;
  }

  /**
   * Add custom style for wp-login.
   *
   * @since 3.1.5
   *
   * @access protected
   */
  public function styles()
  {
    
	echo '<script>
		document.addEventListener("DOMContentLoaded",function(){
			function insertAfter(referenceNode, newNode) {
				referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
			}

			var $nav 			= document.getElementById("nav");
			var $backtoblog 	= document.getElementById("backtoblog");
			var $loginform 		= document.getElementById("loginform");
			var $userpass 		= document.getElementById("user_pass");
			var $poweredby 		= document.getElementById("powered-by");
			var $imhuman 		= document.getElementById("imhuman-container");
			var $rmrb 			= document.getElementById("rm-rb");
			var $imhuman 		= document.getElementById("imhuman-container");
			var $forgetmenot 	= document.getElementsByClassName("forgetmenot");
			var $submit 		= document.getElementsByClassName("submit");
			var $login_error 	= document.createElement( "div" );

			$login_error.id = "login_error";
			$login_error.className = "shake_error";
			$login_error.innerHTML = "We need to make sure you\'re not a robot.";

			if( $nav != null ) {
				$nav.querySelector("a").innerHTML = "Forgot Password?";
				$backtoblog.querySelector("a").innerHTML = "Back to ' . get_site_url() . '";
				insertAfter( $userpass, $nav );
			}

			$loginform.append( $submit[0] );
			$loginform.append( $rmrb );
			if( $backtoblog != null ) {
				$loginform.append( $backtoblog );
			}
			$loginform.append( $poweredby );

			$submit.value = "Login";

			$rmrb.prepend( $forgetmenot[0] );

			document.getElementById("wp-submit").addEventListener( "click", function( e ) {
				if( document.getElementById("imhuman").checked == false ) {
					e.preventDefault();
					if( document.getElementById("login_error") == null ) {
						$loginform.className="imhuman-error"
						document.getElementById("login").insertBefore( $login_error, $loginform );
					}
				}
			} );
		});
	</script>
	<style>
	body{
		background: rgb(2,0,36);
		background: linear-gradient(90deg, rgba(2,0,36,1) 0%, rgba(138,6,164,1) 0%, rgba(9,9,121,1) 100%, rgba(0,212,255,1) 100%);
		display:flex;
		align-items: center;
		justify-content: center;
	}
	#login {
		width: 100%;
		display: flex;
		background: #fff;
		max-width: 1140px;
		padding: 50px 0;
		position: relative;
	}

		.login h1 {
			width: 40%;
			border-right: 1px solid #d8d2d2;
			display: flex;
			align-items: center;
		}

			.login h1 a {
				display: block;
				width: 100%;
				background-size: 100%;
				height: 100%;
				margin: 0 17%;
				background-position: center;
			}

		.login form{
			width: 60%;
			box-shadow: 0 0 0;
			background: transparent;
			border-color: transparent;
			padding-left: 6%;
			flex-grow: 0;
			padding-right: 4%;
		}
			.login #nav {
				margin: 10px 0;
				padding: 0;
			}
			
		#imhuman-container{

		}
			#imhuman-container p{
				display:none;
			}
			#powered-by {
				text-align: center;
				padding: 5px;
			}
			#backtoblog {
				margin: 60px 0 0;
				text-align: center;
			}
			#login form p.submit input {
				background: #007eff;
				width: 100%;
				max-width: 200px;
				text-transform: uppercase;
			}
			.login #login_error {
				width: 64%;
				right: 0;
				position: absolute;
				padding: 20px 0;
				text-align: center;
				border: none;
				top: 0;
				color: red;
			}

			@media screen and (max-width: 1199px) {
				#login{
					max-width: 900px;
			}
			@media screen and (max-width: 992px) {
			
			}
			  
			@media screen and (max-width: 600px) {
				#login {
					max-width: 300px;
					display: block;
				}
				.login h1 {
					width: 100%;
				}

				.login #login_error{
					position: static;
					width: 100%;
					text-align: center;
				}
					.login h1 a{
						height: 84px;
						margin: 0;
					}
			}
	</style>';

  }

  /**
   * Add custom style for wp-login.
   *
   * @since 3.1.5
   *
   * @access protected
   * @return string
   */
  public function url()
  {
    return get_bloginfo('url');
  }

  /**
   * Add custom style for wp-login.
   *
   * @since 3.1.5
   *
   * @access protected
   * @return string
   */
  public function title()
  {
    return get_bloginfo('name');
  }



  /**
   * Remove shake on wrong login.
   *
   * @since 3.1.5
   *
   * @access protected
   */
  public function remove_shake()
  {
    remove_action('login_head', 'wp_shake_js', 12);
  }

  /**
   * Redirect users to homepage if admin.
   *
   * @param $redirect_to
   * @param $request
   * @param $user
   * @return string
   * @since 3.1.5
   *
   * @access protected
   */
  public function redirect($redirect_to, $request, $user)
  {
    global $user;

    if (isset($user->roles) && is_array($user->roles)) {
      if(in_array("administrator", $user->roles)) {
        return $redirect_to;
      } else {
        return home_url();
      }
    } else {
      return $redirect_to;
    }
  }


  /**
   * Original Login Condition
   *
   * @since 3.1.5
   *
   * @access protected
   * @return string
   */
  public function aios_original_login()
  {
    return get_option('aios_custom_login_screen') !== 'original';
  }
}

new LoginFormController();
