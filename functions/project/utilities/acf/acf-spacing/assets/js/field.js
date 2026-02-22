
/**
 * Included when FIELD_NAME fields are rendered for editing by publishers.
 */
( function( $ ) {
	function initialize_field( $field ) {
		/**
		 * $field is a jQuery object wrapping field elements in the editor.
		 */
		var top = '';
		var bottom = '';
		var res = '';
		var arrayValues = [
			"f--pt-15 f--pt-tablets-10",
			"f--pt-10 f--pt-tablets-8",
			"f--pt-5 f--pt-tablets-4",

			"f--pb-15 f--pb-tablets-10",
			"f--pb-10 f--pb-tablets-8",
			"f--pb-5 f--pb-tablets-4",

			"f--pt-15 f--pt-tablets-10 f--pb-15 f--pb-tablets-10",
			"f--pt-15 f--pt-tablets-10 f--pb-10 f--pb-tablets-8",
			"f--pt-15 f--pt-tablets-10 f--pb-5 f--pb-tablets-4",

			"f--pt-10 f--pt-tablets-8 f--pb-15 f--pb-tablets-10",
			"f--pt-10 f--pt-tablets-8 f--pb-10 f--pb-tablets-8",
			"f--pt-10 f--pt-tablets-8 f--pb-5 f--pb-tablets-4",

			"f--pt-5 f--pt-tablets-4 f--pb-15 f--pb-tablets-10",
			"f--pt-5 f--pt-tablets-4 f--pb-10 f--pb-tablets-8",
			"f--pt-5 f--pt-tablets-4 f--pb-5 f--pb-tablets-4",
		];
		var arrayNames = [
			"top-large",
			"top-medium",
			"top-small",

			"bottom-large",
			"bottom-medium",
			"bottom-small",

			"top-large-bottom-large",
			"top-large-bottom-medium",
			"top-large-bottom-small",
			
			"top-medium-bottom-large",
			"top-medium-bottom-medium",
			"top-medium-bottom-small",

			"top-small-bottom-large",
			"top-small-bottom-medium",
			"top-small-bottom-small",
		];

		var arrayVal = [
			"d-e",
			"d-f",
			"d-g",

			"b-h",
			"b-i",
			"b-j",

			"d-e-b-h",
			"d-e-b-i",
			"d-e-b-j",
			
			"d-f-b-h",
			"d-f-b-i",
			"d-f-b-j",

			"d-g-b-h",
			"d-g-b-i",
			"d-g-b-j",
		];
		var topSpace = $field.find(".js--top-space");
		var bottomSpace = $field.find(".js--bottom-space");
		var topTitle = $field.find(".js--top-title");
		var bottomTitle = $field.find(".js--bottom-title");
		$.each($($field.find(".js--padding").find("button")),function(){
			if($field.find("input").val()){
				$.each($($field.find(".js--padding").find("button")),function(){
					var fieldValue = $field.find("input").val();
					switch ($field.find("input").val()) {
						case "bottom-large":
						case "bottom-medium":
						case "bottom-small":
							if($(this).val() == "b"){
								$(this).addClass('active')
							}else{
								$(this).addClass('no-active')
							}
							if(fieldValue == 'bottom-large'){
								bottom = "f--pb-15 f--pb-tablets-10"
							}else if(fieldValue == 'bottom-small'){
								bottom = "f--pb-10 f--pb-tablets-8"
							}else if(fieldValue == 'bottom-small'){
								bottom = "f--pb-5 f--pb-tablets-4"
							}
							break;
						case "top-large":
						case "top-medium":
						case "top-small":
							if($(this).val() == "d"){
								$(this).addClass('active')
							}else{
								$(this).addClass('no-active')
							}
							if(fieldValue == 'top-large'){
								top = "f--pt-15 f--pt-tablets-10"
							}else if(fieldValue == 'top-small'){
								top = "f--pt-10 f--pt-tablets-8"
							}else if(fieldValue == 'top-small'){
								top = "f--pt-5 f--pt-tablets-4"
							}
							break;
						case "top-large-bottom-medium":
						case "top-large-bottom-large":
						case "top-large-bottom-small":
						case "top-medium-bottom-large":
						case "top-medium-bottom-medium":
						case "top-medium-bottom-small":
						case "top-small-bottom-large":
						case "top-small-bottom-medium":
						case "top-small-bottom-small":
							if($(this).val() == "a"){
								$(this).addClass('active')
							}else{
								$(this).addClass('no-active')
							}
							if(fieldValue == 'top-large-bottom-medium'){
								top = "f--pt-15 f--pt-tablets-10";
								bottom = "f--pb-15 f--pb-tablets-10";
							}else if(fieldValue == 'top-large-bottom-large'){
								top = "f--pt-15 f--pt-tablets-10";
								bottom = "f--pb-10 f--pb-tablets-8";
							}else if(fieldValue == 'top-large-bottom-small'){
								top = "f--pt-15 f--pt-tablets-10";
								bottom = "f--pb-5 f--pb-tablets-4";
							}else if(fieldValue == 'top-medium-bottom-large'){
								top = "f--pt-10 f--pt-tablets-8";
								bottom = "f--pb-15 f--pb-tablets-10";
							}else if(fieldValue == 'top-medium-bottom-medium'){
								top = "f--pt-10 f--pt-tablets-8";
								bottom = "f--pb-10 f--pb-tablets-8";
							}else if(fieldValue == 'top-medium-bottom-small'){
								top = "f--pt-10 f--pt-tablets-8";
								bottom = "f--pb-5 f--pb-tablets-4";
							}else if(fieldValue == 'top-small-bottom-large'){
								top = "f--pt-5 f--pt-tablets-4";
								bottom = "f--pb-15 f--pb-tablets-10";
							}else if(fieldValue == 'top-small-bottom-medium'){
								top = "f--pt-5 f--pt-tablets-4 ";
								bottom = "f--pb-10 f--pb-tablets-8";
							}else if(fieldValue == 'top-small-bottom-small'){
								top = "f--pt-5 f--pt-tablets-4";
								bottom = "f--pb-5 f--pb-tablets-4";
							}
							break;
						default:
							if($(this).val() == "-"){
								$(this).addClass('active')
							}else{
								$(this).addClass('no-active')
							}
							break;
					}
		
				});
				
			}
			$(this).on("click", (e) => {
				e.preventDefault();
				var buttonValue = $(this).val();
				
				$.each($($field.find(".js--padding").find("button")),function(){
					$(this).removeClass('active')
					$(this).addClass('no-active')
				});
				$(this).addClass('active');
				$(this).removeClass('no-active');


				if(buttonValue == 'a'){
					topSpace.css("display", "inline-flex");
					bottomSpace.css("display", "inline-flex");
					topTitle.css("display", "block");
					bottomTitle.css("display", "block");

					top = "f--pt-15 f--pt-tablets-10";
					bottom = "f--pb-15 f--pb-tablets-10";

				
					$.each($($field.find(".js--top-space").find("#e")),function(){
						$(this).addClass('active')
						$(this).removeClass('no-active')
					});
					$.each($($field.find(".js--top-space").find("#f")),function(){
						$(this).removeClass('active')
						$(this).addClass('no-active')
					});
					$.each($($field.find(".js--top-space").find("#g")),function(){
						$(this).addClass('active')
						$(this).addClass('no-active')
					});
					$.each($($field.find(".js--bottom-space").find("#h")),function(){
						$(this).addClass('active')
						$(this).removeClass('no-active')
					});
					$.each($($field.find(".js--bottom-space").find("#i")),function(){
						$(this).removeClass('active')
						$(this).addClass('no-active')
					});
					$.each($($field.find(".js--bottom-space").find("#j")),function(){
						$(this).removeClass('active')
						$(this).addClass('no-active')
					});			
				}else if(buttonValue == 'd'){
					topSpace.css("display", "inline-flex");
					bottomSpace.css("display", "none");
					topTitle.css("display", "block");
					bottomTitle.css("display", "none");

					top = "f--pt-15 f--pt-tablets-10";
					bottom = "";

					$.each($($field.find(".js--top-space").find("#e")),function(){
						$(this).addClass('active')
						$(this).removeClass('no-active')
					});
					$.each($($field.find(".js--top-space").find("#f")),function(){
						$(this).removeClass('active')
						$(this).addClass('no-active')
					});
					$.each($($field.find(".js--top-space").find("#g")),function(){
						$(this).removeClass('active')
						$(this).addClass('no-active')
					});

				}else if(buttonValue == 'b'){
					topSpace.css("display", "none");
					bottomSpace.css("display", "inline-flex");
					topTitle.css("display", "none");
					bottomTitle.css("display", "block");

					top = "";
					bottom =  "f--pb-15 f--pb-tablets-10";

					$.each($($field.find(".js--bottom-space").find("#h")),function(){
						$(this).addClass('active')
						$(this).removeClass('no-active')
					});
					$.each($($field.find(".js--bottom-space").find("#i")),function(){
						$(this).removeClass('active')
						$(this).addClass('no-active')
					});
					$.each($($field.find(".js--bottom-space").find("#j")),function(){
						$(this).removeClass('active')
						$(this).addClass('no-active')
					});
				}else{
					topSpace.css("display", "none");
					bottomSpace.css("display", "none");
					topTitle.css("display", "none");
					bottomTitle.css("display", "none");

					top = "";
					bottom = "";
				}
				$field.find("input").val(top + " " + bottom);
				
				if(top && bottom){
					res = top + " " + bottom;
				}else if(top){
					res = top ;
				}else if(bottom){
					res = bottom ;
				}else{
					res = '';
				}
				const found =  arrayValues.indexOf(res )
				if(res != ''){
					$field.find("input").val(arrayNames[found]);
				}else{
					$field.find("input").val("-");
				}

			});
		});

		$.each($($field.find(".js--top-space").find("button")),function(){
			if($field.find("input").val()){
				$.each($($field.find(".js--top-space").find("button")),function(){
					var valu = $field.find("input").val();
					var test =  arrayNames.indexOf(valu);
					if(test !== -1){
						if(arrayVal[test].includes($(this).val())){
							$(this).addClass('active')
							topSpace.css("display", "inline-flex");
							topTitle.css("display", "block");
						}else{
							$(this).addClass('no-active')
						}
					}else{
						$(this).addClass('no-active')
					}
				});
			}
			$(this).on("click", (e) => {
				e.preventDefault();
				var buttonValue = $(this).val();
				top = buttonValue;
				if(buttonValue == "e"){
					top = "f--pt-15 f--pt-tablets-10";
				}else if(buttonValue == "f"){
					top = "f--pt-10 f--pt-tablets-8";
				}else if(buttonValue == 'g'){
					top = "f--pt-5 f--pt-tablets-4";
				}
				if(top && bottom){
					res = top + " " + bottom;
				}else if(top){
					res = top ;
				}else if(bottom){
					res = bottom ;
				}
				const foundTop =  arrayValues.indexOf(res );
				$field.find("input").val(arrayNames[foundTop]);
				$.each($($field.find(".js--top-space").find("button")),function(){
					$(this).removeClass('active')
					$(this).addClass('no-active')
				});
				$(this).addClass('active');
				$(this).removeClass('no-active');

			});
		});

		$.each($($field.find(".js--bottom-space").find("button")),function(){
			if($field.find("input").val()){
				$.each($($field.find(".js--bottom-space").find("button")),function(){
					var valu = $field.find("input").val();
					var test =  arrayNames.indexOf(valu);
					if(test !== -1){
						if(arrayVal[test].includes($(this).val())){
							$(this).addClass('active')
							bottomSpace.css("display", "inline-flex");
							bottomTitle.css("display", "block");
						}else{
							$(this).addClass('no-active')
						}
					}else{
						$(this).addClass('no-active')
					}
				});
			}
			$(this).on("click", (e) => {
				e.preventDefault();
				var buttonValue = $(this).val();
				if(buttonValue == "h"){
					bottom = "f--pb-15 f--pb-tablets-10";
				}else if(buttonValue == "i"){
					bottom = "f--pb-10 f--pb-tablets-8";
				}else if(buttonValue == 'j'){
					bottom = "f--pb-5 f--pb-tablets-4";
				}
				if(top && bottom){
					res = top + " " + bottom;
				}else if(top){
					res = top ;
				}else if(bottom){
					res = bottom ;
				}
				const foundBottom =  arrayValues.indexOf(res);
				$field.find("input").val(arrayNames[foundBottom]);
				console.log(foundBottom);
				$.each($($field.find(".js--bottom-space").find("button")),function(){
					$(this).removeClass('active')
					$(this).addClass('no-active')
				});
				$(this).addClass('active');
				$(this).removeClass('no-active');

			});
		});

	}

	if( typeof acf.add_action !== 'undefined' ) {
		/**
		 * Run initialize_field when existing fields of this type load,
		 * or when new fields are appended via repeaters or similar.
		 */

		acf.add_action( 'ready_field/type=spacing', initialize_field );
		acf.add_action( 'append_field/type=spacing', initialize_field );
	}
} )( jQuery );
