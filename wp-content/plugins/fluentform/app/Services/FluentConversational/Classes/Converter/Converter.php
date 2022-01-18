<?php

namespace FluentForm\App\Services\FluentConversational\Classes\Converter;

use FluentForm\Framework\Helpers\ArrayHelper;
use FluentForm\App\Modules\Component\Component;
use FluentForm\App\Services\FormBuilder\Components\DateTime;
use FluentForm\App\Services\FluentConversational\Classes\Form;

class Converter
{
	public static function convert($form)
	{
		$form->fields = json_decode($form->form_fields, true);

		$fields = $form->fields['fields'];

		$form->submit_button = $form->fields['submitButton'];

		$questions = [];

		$imagePreloads = [];

		$allowedFields = static::fieldTypes();
		foreach ($fields as $field) {
			$question = [
				'id'                 => $field['uniqElKey'],
				'name'               => ArrayHelper::get($field, 'attributes.name'),
				'title'              => ArrayHelper::get($field, 'settings.label'),
				'type'               => ArrayHelper::get($allowedFields, $field['element']),
				'ff_input_type'      => $field['element'],
                'container_class'    => ArrayHelper::get($field, 'settings.container_class'),
                'placeholder'        => ArrayHelper::get($field, 'attributes.placeholder'),
				'required'           => ArrayHelper::get($field, 'settings.validation_rules.required.value'),
				'requiredMsg'        => ArrayHelper::get($field, 'settings.validation_rules.required.message'),
				'errorMessage'       => ArrayHelper::get($field, 'settings.validation_rules.required.message'),
				'validationRules'    => ArrayHelper::get($field, 'settings.validation_rules'),
				'answer'             => self::setDefaultValue(ArrayHelper::get($field, 'attributes.value'), $field, $form),
				'tagline'            => ArrayHelper::get($field, 'settings.help_message'),
				'style_pref'         => ArrayHelper::get($field, 'style_pref', [
					'layout'           => 'default',
					'media'            => '',
					'brightness'       => 0,
					'alt_text'         => '',
					'media_x_position' => 50,
					'media_y_position' => 50
				]),
				'conditional_logics' => self::parseConditionalLogic($field)
			];

			if (ArrayHelper::get($question, 'style_pref.layout') != 'default') {
				$media = ArrayHelper::get($question, 'style_pref.media');
				if ($media) {
					$imagePreloads[] = $media;
				}
			}

			if ($field['element'] === 'input_text') {
				$mask = ArrayHelper::get($field, 'settings.temp_mask');

				$mask = $mask === 'custom' ? ArrayHelper::get($field, 'attributes.data-mask') : $mask;

				if ($mask) {
					$question['mask'] = $mask;
				}
			} elseif ($field['element'] === 'welcome_screen') {
				$question['settings'] = ArrayHelper::get($field, 'settings', []);
				$question['subtitle'] = ArrayHelper::get($field, 'settings.description');
				$question['required'] = false;
//				$question['css'] = (new \FluentConversational\Form)->getSubmitBttnStyle($field);

			} elseif ($field['element'] === 'select') {
				$question['options'] = self::getAdvancedOptions($field);
				$question['placeholder'] = ArrayHelper::get($field, 'settings.placeholder', null);
				$question['searchable'] = ArrayHelper::get($field, 'settings.enable_select_2');
				$isMultiple = ArrayHelper::get($field, 'attributes.multiple', false);

				if ($isMultiple) {
					$question['multiple'] = true;
					$question['placeholder'] = ArrayHelper::get($field, 'attributes.placeholder', false);
					$question['max_selection'] = ArrayHelper::get($field, 'settings.max_selection');
					$question['max_selection'] = $question['max_selection'] ? intval($question['max_selection']) : 0;
				}
			} elseif ($field['element'] === 'select_country') {
				$options = array();
				$countries = getFluentFormCountryList();
				foreach ($countries as $key => $value) {
					$options[] = [
						'label' => $value,
						'value' => $key
					];
				}
				$question['options'] = $options;
				$question['placeholder'] = ArrayHelper::get($field, 'attributes.placeholder', null);
				$question['searchable'] = ArrayHelper::get($field, 'settings.enable_select_2');
			} elseif ($field['element'] === 'input_checkbox') {
				$question['options'] = self::getAdvancedOptions($field);;
				$question['multiple'] = true;
				$question = static::hasPictureMode($field, $question);
			} elseif ($field['element'] === 'input_radio') {
				$question['options'] = self::getAdvancedOptions($field);
				$question['nextStepOnAnswer'] = true;
				$question = static::hasPictureMode($field, $question);
			} elseif ($field['element'] === 'custom_html') {
				$question['content'] = ArrayHelper::get($field, 'settings.html_codes', '');
			} elseif ($field['element'] === 'section_break') {
				$question['content'] = ArrayHelper::get($field, 'settings.description', '');
				$question['contentAlign'] = ArrayHelper::get($field, 'settings.align', '');
			} elseif ($field['element'] === 'phone') {
				if (defined('FLUENTFORMPRO')) {
					$cssSource = FLUENTFORMPRO_DIR_URL . 'public/libs/intl-tel-input/css/intlTelInput.min.css';
					if (is_rtl()) {
						$cssSource = FLUENTFORMPRO_DIR_URL . 'public/libs/intl-tel-input/css/intlTelInput-rtl.min.css';
					}
					wp_enqueue_style('intlTelInput', $cssSource, [], '16.0.0');
					wp_enqueue_script('intlTelInputUtils', FLUENTFORMPRO_DIR_URL . 'public/libs/intl-tel-input/js/utils.js', [], '16.0.0', true);
					wp_enqueue_script('intlTelInput', FLUENTFORMPRO_DIR_URL . 'public/libs/intl-tel-input/js/intlTelInput.min.js', [], '16.0.0', true);
					$question['phone_settings'] = self::getPhoneFieldSettings($field, $form);
				}
			} elseif ($field['element'] === 'input_number') {
				$question['min'] = ArrayHelper::get($field, 'settings.validation_rules.min.value');
				$question['max'] = ArrayHelper::get($field, 'settings.validation_rules.max.value');
				$question['min'] = is_numeric($question['min']) ? $question['min'] : null;
				$question['max'] = is_numeric($question['max']) ? $question['max'] : null;
			} elseif (in_array($field['element'], ['terms_and_condition', 'gdpr_agreement'])) {
				$question['options'] = [
					[
						'label' => ArrayHelper::get($field, 'settings.tc_agree_text', 'I accept'),
						'value' => 'on',
					],
					[
						'label' => ArrayHelper::get($field, 'settings.tc_dis_agree_text', 'I accept'),
						'value' => 'off',
					]
				];

				$question['nextStepOnAnswer'] = true;
				$question['title'] = ArrayHelper::get($field, 'settings.tnc_html');
				if ($field['element'] === 'gdpr_agreement') {
					$question['required'] = true;
				}

			} elseif ($field['element'] === 'ratings') {
				$question['show_text'] = ArrayHelper::get($field, 'settings.show_text');
				$question['rateOptions'] = ArrayHelper::get($field, 'options', []);
				$question['nextStepOnAnswer'] = true;
			} elseif ($field['element'] === 'input_date') {
				$app = wpFluentForm();
				$dateField = new DateTime();

				wp_enqueue_style('flatpickr', $app->publicUrl('libs/flatpickr/flatpickr.min.css'));
				wp_enqueue_script('flatpickr', $app->publicUrl('libs/flatpickr/flatpickr.js'), [], false, true);

				$question['dateConfig'] = json_decode($dateField->getDateFormatConfigJSON($field['settings'], $form));
				$question['dateCustomConfig'] = json_decode($dateField->getCustomConfig($field['settings']));
			}
			if ($question['type']) {
				$questions[] = $question;
			}
			if ($field['element'] === 'custom_submit_button') {
				$form->submit_button = $field;
			}
		}

		$form->questions = $questions;

		$form->image_preloads = $imagePreloads;

		return $form;
	}

	public static function fieldTypes()
	{
		$fieldTypes = [
			'input_url'             => 'FlowFormUrlType',
			'input_date'            => 'FlowFormDateType',
			'input_text'            => 'FlowFormTextType',
			'ratings'               => 'FlowFormRateType',
			'input_email'           => 'FlowFormEmailType',
			'input_hidden'          => 'FlowFormHiddenType',
			'input_number'          => 'FlowFormNumberType',
			'select'                => 'FlowFormDropdownType',
			'select_country'        => 'FlowFormDropdownType',
			'textarea'              => 'FlowFormLongTextType',
			'input_password'        => 'FlowFormPasswordType',
			'custom_html'           => 'FlowFormSectionBreakType',
			'section_break'         => 'FlowFormSectionBreakType',
			'welcome_screen'        => 'FlowFormWelcomeScreenType',
			'input_checkbox'        => 'FlowFormMultipleChoiceType',
			'input_radio'           => 'FlowFormMultipleChoiceType',
			'terms_and_condition'   => 'FlowFormTermsAndConditionType',
			'gdpr_agreement'        => 'FlowFormTermsAndConditionType',
			'MultiplePictureChoice' => 'FlowFormMultiplePictureChoiceType',
		];

		if (defined('FLUENTFORMPRO')) {
			$fieldTypes['phone'] = 'FlowFormPhoneType';
		}

		return $fieldTypes;
	}

	public static function hasPictureMode($field, $question)
	{
		$pictureMode = ArrayHelper::get($field, 'settings.enable_image_input');

		if ($pictureMode) {
			$question['type'] = static::fieldTypes()['MultiplePictureChoice'];
		}

		return $question;
	}

	public static function hex2rgb($color, $opacity = 0.3)
	{
		if (!$color) {
			return;
		}
		$rgbValues = list($r, $g, $b) = array_map(
			function ($c) {
				return hexdec(str_pad($c, 2, $c));
			},
			str_split(ltrim($color, '#'), strlen($color) > 4 ? 2 : 1)
		);
		$rgbValues[3] = $opacity;
		$formattedValues = implode(',', $rgbValues);
		return "rgb({$formattedValues})";
	}

	public static function getPhoneFieldSettings($data, $form)
	{
		$geoLocate = ArrayHelper::get($data, 'settings.auto_select_country') == 'yes';
		$enabled = ArrayHelper::get($data, 'settings.int_tel_number') == 'with_extended_validation';

		$itlOptions = [
			'separateDialCode' => false,
			'nationalMode'     => true,
			'autoPlaceholder'  => 'aggressive',
			'formatOnDisplay'  => true
		];

		if ($geoLocate) {
			$itlOptions['initialCountry'] = 'auto';
		} else {
			$itlOptions['initialCountry'] = ArrayHelper::get($data, 'settings.default_country', '');
			$activeList = ArrayHelper::get($data, 'settings.phone_country_list.active_list');

			if ($activeList == 'priority_based') {
				$selectCountries = ArrayHelper::get($data, 'settings.phone_country_list.priority_based', []);
				$priorityCountries = self::getSelectedCountries($selectCountries);
				$itlOptions['preferredCountries'] = array_keys($priorityCountries);
			} else if ($activeList == 'visible_list') {
				$onlyCountries = ArrayHelper::get($data, 'settings.phone_country_list.visible_list', []);
				$itlOptions['onlyCountries'] = $onlyCountries;
			} else if ($activeList == 'hidden_list') {
				$countries = self::loadCountries($data);
				$itlOptions['onlyCountries'] = array_keys($countries);
			}
		}

		$itlOptions = apply_filters('fluentform_itl_options', $itlOptions, $data, $form);
		$itlOptions = json_encode($itlOptions);

		$settings = get_option('_fluentform_global_form_settings');
		$token = ArrayHelper::get($settings, 'misc.geo_provider_token');

		$url = 'https://ipinfo.io';
		if ($token) {
			$url = 'https://ipinfo.io/?token=' . $token;
		}
		$ipProviderUrl = apply_filters('fluentform_ip_provider', $url);

		return [
			'enabled'     => $enabled,
			'itlOptions'  => $itlOptions,
			'ipLookupUrl' => ($geoLocate && $ipProviderUrl) ? $ipProviderUrl : false,
		];
	}

	/**
	 * Load country list from file
	 * @param array $data
	 * @return array
	 */
	public static function loadCountries($data)
	{
		$data['options'] = array();
		$activeList = ArrayHelper::get($data, 'settings.phone_country_list.active_list');
		$countries = getFluentFormCountryList();
		$filteredCountries = [];
		if ($activeList == 'visible_list') {
			$selectCountries = ArrayHelper::get($data, 'settings.phone_country_list.' . $activeList, []);
			foreach ($selectCountries as $value) {
				$filteredCountries[$value] = $countries[$value];
			}
		} elseif ($activeList == 'hidden_list' || $activeList == 'priority_based') {
			$filteredCountries = $countries;
			$selectCountries = ArrayHelper::get($data, 'settings.phone_country_list.' . $activeList, []);
			foreach ($selectCountries as $value) {
				unset($filteredCountries[$value]);
			}
		} else {
			$filteredCountries = $countries;
		}

		return $filteredCountries;
	}

	public static function getSelectedCountries($keys = [])
	{
		$options = [];
		$countries = getFluentFormCountryList();
		foreach ($keys as $value) {
			$options[$value] = $countries[$value];
		}

		return $options;
	}

	public static function setDefaultValue($value, $field, $form)
	{
		if ($dynamicValue = ArrayHelper::get($field, 'settings.dynamic_default_value')) {
			$dynamicVal = (new Component(wpFluentForm()))->replaceEditorSmartCodes($dynamicValue, $form);

			$element = $field['element'];

			if ($dynamicVal && $element == 'input_checkbox') {
				$defaultValues = explode(',', $dynamicVal);
				return array_map('trim', $defaultValues);
			}

			if ($dynamicVal) {
				return $dynamicVal;
			}
		}

		if (!$value) {
			return $value;
		}
		if (is_string($value)) {
			return (new Component(wpFluentForm()))->replaceEditorSmartCodes($value, $form);
		}

		return $value;
	}

	private static function parseConditionalLogic($field)
	{
		$logics = ArrayHelper::get($field, 'settings.conditional_logics', []);

		if (!$logics || !$logics['status']) {
			return [];
		}

		$validConditions = [];
		foreach ($logics['conditions'] as $condition) {
			if (empty($condition['field']) || empty($condition['operator'])) {
				continue;
			}
			$validConditions[] = $condition;
		}

		if (!$validConditions) {
			return [];
		}

		$logics['conditions'] = $validConditions;

		return $logics;
	}

	private static function getAdvancedOptions($field)
	{
		$options = ArrayHelper::get($field, 'settings.advanced_options', []);

		if ($options && ArrayHelper::get($field, 'settings.randomize_options') == 'yes') {
			shuffle($options);
		}

		return $options;

	}
}
