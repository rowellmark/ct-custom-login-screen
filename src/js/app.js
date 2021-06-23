( function($) {
	$( document ).ready( function() {

		var $document 		= $( document ),
			$window 		= $( window ),
			$viewport 		= $( 'html, body' ),
			$html 			= $( 'html' ),
			$body 			= $( 'body' );
		/**
		 * Construct.
		 */
		function __construct() {
			aios_color_picker();
			permastructure();
		}

		/**
		 * Set color picker.
		 */
		function aios_color_picker() {
			var $inputPicker = $( '.aios-color-picker' );

			$inputPicker.each( function() {
				$( this ).wpColorPicker();
			} );
		}

		/**
		 * Slug for Testimonials - On keypress remove replace special character to -
		 */
		function permastructure() {
			var $inputPermastructure = $( '.testimonials-permastructure' );

			$inputPermastructure.on( 'keyup', function() {
				var $this 	= $( this ),
					$val 	= slugify( $this.val() );

				$this.val( $val );
			} );

			/// enable permastructure
			$checkBox = $('input[name="aios_testimonials_settings[enable_permalinks]"]');

			// on page load
			if ( $checkBox.is(':checked') ){
				$inputPermastructure.removeAttr('disabled');
			}else{
				$inputPermastructure.prop('disabled', true);
			}
			// on click
			$checkBox.on('click', function () {
				if ( $(this).is(':checked') ){
					$inputPermastructure.removeAttr('disabled');
				}else{
					$inputPermastructure.prop('disabled', true);
				}
			});
			
		}
			function slugify(string) {
				const a = 'àáäâãèéëêìíïîòóöôùúüûñçßÿœæŕśńṕẃǵǹḿǘẍźḧ·/_,:;';
				const b = 'aaaaaeeeeiiiioooouuuuncsyoarsnpwgnmuxzh------';
				const p = new RegExp(a.split('').join('|'), 'g');

				return string.toString().toLowerCase()
					.replace(/\s+/g, '-') /** Replace spaces with **/
					.replace(p, c => b.charAt(a.indexOf(c))) /** Replace special characters **/
					.replace(/&/g, '-and-') /** Replace & with ‘and’ **/
					.replace(/[^\w\-]+/g, '-') /** Remove all non-word characters **/
					.replace(/\-\-+/g, '-') /** Replace multiple — with single - **/
					.replace(/^-+/, ''); /** Trim — from start of text .replace(/-+$/, '') Trim — from end of text **/
			}

		/**
		 * Instantiate
		 */
		__construct();

	} );
} )( jQuery );