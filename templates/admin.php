<?php
	$options = get_option('kontxt_include_location', ['type' => null, 'text' => null]);
	$type = $options['type'];
	$text = $options['text'];
?>

<?php settings_errors(); ?>

<div id="admin_root">
	<h1>Kontxt Settings</h1>

	<ol>
		<li>Create an account at <a href="https://kontxt.io">Kontxt</a> to manage features on your websites.</li>
		<li>E-mail <span class="emphasis">info@kontxt.io</span> to connect your account with your website.</li>
		<li>
			<div>
				<p>Select where to include Kontxt:</p>

				<form id="FORM" method="POST" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
					<input type="hidden" name="action" value="kontxt_settings_form">
					<?php wp_nonce_field( 'kontxt_settings_update', 'kontxt_form' ); ?>

					<div>
					  <input type="radio" id="NONE" name="LOCATIONS" value="NONE"
					  	<?php echo $type === 'NONE' ? 'checked' : null; ?>>

					  <label for="NONE">None</label>
					</div>

					<div>
					  <input type="radio" id="POSTS" name="LOCATIONS" value="POSTS"
					  	<?php echo $type === 'POSTS' ? 'checked' : null; ?>>

					  <label for="POSTS">Posts</label>
					</div>

					<div>
					  <input type="radio" id="PAGES" name="LOCATIONS" value="PAGES"
					  	<?php echo $type === 'PAGES' ? 'checked' : null; ?>>

					  <label for="PAGES">Pages</label>
					</div>

					<div>
					  <input type="radio" id="POSTS_AND_PAGES" name="LOCATIONS" value="POSTS_AND_PAGES"
					  	<?php echo $type === 'POSTS_AND_PAGES' ? 'checked' : null; ?>>

					  <label for="POSTS_AND_PAGES">Posts & Pages</label>
					</div>

					<div>
					  <input type="radio" id="CONTAINS" name="LOCATIONS" value="CONTAINS"
					  	<?php echo $type === 'CONTAINS' ? 'checked' : null; ?>>

					  <label for="CONTAINS">All URLs that <span class="bold">contain</span> the following <span class="bold">text</span></label>
					  <input type="text" id="CONTAINS_INPUT" name="TEXT_INPUT[]" placeholder="Example: /articles/" value="<?php echo $type === 'CONTAINS' ? $text : '' ?>"/>
					  <span id="HIDDEN_CONTAINS"></span>
					</div>

					<div>
					  <input type="radio" id="REGEX" name="LOCATIONS" value="REGEX"
					  	<?php echo $type === 'REGEX' ? 'checked' : null; ?>>

					  <label for="REGEX">All URLs that <span class="bold">match</span> the following <span class="bold">regex</span></label>
					  <input type="text" id="REGEX_INPUT" name="TEXT_INPUT[]" placeholder="Example: /\/(blogs|articles)\//i" value="<?php echo $type === 'REGEX' ? $text : '' ?>"/> <span id="REGEX_MESSAGE" class="valid">* Invalid Regex.</span>
					  <span id="HIDDEN_REGEX"></span>
					</div>

					<?php submit_button(); ?>

				</form>
			</div>
		</li>
	</ol>

	<div>
		<span style="color:red;">*</span> E-mail <span class="emphasis">info@kontxt.io</span> if you have questions or need help.
	</div>
</div>

<style>
	#admin_root, p {
		font-size: 15px;
	}

	#FORM {
		height: 300px;
		display: flex;
		flex-direction: column;
		justify-content: space-between;
	}

	#REGEX_INPUT, #CONTAINS_INPUT {
		min-width: 240px;
		margin-left: 5px;
	}

	#HIDDEN_REGEX, #HIDDEN_CONTAINS, .hide {
		visibility: hidden;
	}

	.bold {
		font-weight: bold;
	}

	.valid {
		display: none;
	}

	.invalid {
		color: red;
	}

	.emphasis {
		font-style: italic;
		font-weight: bold;
	}

	li {
		margin: 15px 0px;
	}
</style>

<script>
	const REGEX_INPUT = 'REGEX_INPUT';
	const REGEX = 'REGEX';
	const CONTAINS_INPUT = 'CONTAINS_INPUT';
	const CONTAINS = 'CONTAINS';
	const LOCATIONS = 'LOCATIONS';
	const HIDDEN_REGEX = 'HIDDEN_REGEX';
	const TEXT_INPUT = 'TEXT_INPUT';
	const REGEX_MESSAGE = 'REGEX_MESSAGE';

	const el_id_to_padding_left = {
		[REGEX_INPUT]: null,
		[CONTAINS_INPUT]: null,
	};

	function check_valid_regex(event){
		const regex_input_el = document.getElementById(REGEX_INPUT);
		const regex_val = regex_input_el.value;

		const split_index = regex_val.lastIndexOf('/');
		const flags = regex_val.substring(split_index + 1);
		const regex_pattern = regex_val.substring(0, split_index);

		let is_valid_regex = null;

		try{
			new RegExp(regex_pattern, flags);
			is_valid_regex = true;
		}catch(e){
			is_valid_regex = false;
		}

		const regex_message_el = document.getElementById(REGEX_MESSAGE);

		if(is_valid_regex){
			regex_message_el.className = 'valid';
		}else{
			regex_message_el.className = 'invalid';
		}
	}

	const regex_input_el = document.getElementById(REGEX_INPUT);
	regex_input_el.addEventListener('input', check_valid_regex);

	function add_input_click_check_radio_and_resize_hidden_input(radio_id, input_id){
		const input_el = document.getElementById(input_id);
		input_el.addEventListener('click', function(event){
			const radio_el = document.getElementById(radio_id);
			radio_el.checked = true;
		});

		input_el.addEventListener('input', function(event){
			const input_el = document.getElementById(input_id);



			if(!el_id_to_padding_left[input_id]){
				const style = window.getComputedStyle(input_el);
				el_id_to_padding_left[input_id] = parseInt(style.getPropertyValue('padding-left'));
			}

			const hidden_el = document.getElementById(`HIDDEN_${radio_id}`);
			hidden_el.innerText = input_el.value; //event.target.value

			const curr_el_width = input_el.offsetWidth;
			const curr_hidden_el_width = hidden_el.offsetWidth + 4*el_id_to_padding_left[input_id];

			if(curr_hidden_el_width > curr_el_width){
				input_el.style.width = `${curr_hidden_el_width}px`;
			}else{
				if(curr_hidden_el_width < curr_el_width){
					input_el.style.width = `${curr_hidden_el_width}px`;
				}
			}
		});
	}


	add_input_click_check_radio_and_resize_hidden_input(REGEX, REGEX_INPUT);
	add_input_click_check_radio_and_resize_hidden_input(CONTAINS, CONTAINS_INPUT);


	function click_clear_inactive_inputs(event){
		const id = event.currentTarget.getAttribute('id');

		const contains_input_el = document.getElementById(CONTAINS_INPUT);
		const regex_input_el = document.getElementById(REGEX_INPUT);

		const is_contains = id === CONTAINS || id === CONTAINS_INPUT;
		const is_regex = id === REGEX || id === REGEX_INPUT;

		if(!is_regex){
			regex_input_el.value = '';
		}


		if(!is_contains){
			contains_input_el.value = '';
		}
	}

	const radio_els = [...document.querySelectorAll(`input[name=${LOCATIONS}]`)];
	const text_els = [...document.querySelectorAll(`input[name=${TEXT_INPUT}]`)];
	const els = [...radio_els, ...text_els];

	els.forEach(val => {
		val.addEventListener('click', click_clear_inactive_inputs);
	})
</script>