<?php

namespace FluentForm\App\Services\Integrations;

use FluentForm\Framework\Helpers\ArrayHelper;

abstract class IntegrationManager
{
    protected $app = null;
    protected $subscriber = null;
    protected $title = '';
    protected $description = '';
    protected $integrationKey = '';
    protected $optionKey = '';
    protected $settingsKey = '';
    protected $priority = 11;
    public $logo = '';
    public $hasGlobalMenu = true;
    public $category = 'crm';
    public $disableGlobalSettings = 'no';

    public function __construct($app, $title, $integrationKey, $optionKey, $settingsKey, $priority = 11)
    {
        $this->app = $app;
        $this->title = $title;
        $this->integrationKey = $integrationKey;
        $this->optionKey = $optionKey;
        $this->settingsKey = $settingsKey;
        $this->priority = $priority;
    }

    public function registerAdminHooks()
    {
        $isEnabled =  $this->isEnabled();

        add_filter('fluentform_global_addons', function ($addons) use ($isEnabled) {
            $addons[$this->integrationKey] = [
                'title' => $this->title,
                'category' => $this->category,
                'disable_global_settings' => $this->disableGlobalSettings,
                'description' => $this->description,
                'config_url' => ($this->disableGlobalSettings != 'yes') ? admin_url('admin.php?page=fluent_forms_settings#general-'.$this->integrationKey.'-settings') : '',
                'logo' => $this->logo,
                'enabled' => ($isEnabled) ? 'yes' : 'no'
            ];
            return $addons;
        }, $this->priority, 1);

        if(!$isEnabled) {
            return;
        }

        $this->registerNotificationHooks();

        // Global Settings Here

        if ($this->hasGlobalMenu) {
            add_filter('fluentform_global_settings_components', array($this, 'addGlobalMenu'));
            add_filter('fluentform_global_integration_settings_' . $this->integrationKey, array($this, 'getGlobalSettings'), $this->priority, 1);
            add_filter('fluentform_global_integration_fields_' . $this->integrationKey, array($this, 'getGlobalFields'), $this->priority, 1);
            add_action('fluentform_save_global_integration_settings_' . $this->integrationKey, array($this, 'saveGlobalSettings'), $this->priority, 1);
        }

        add_filter('fluentform_global_notification_types', array($this, 'addNotificationType'), $this->priority);

        add_filter('fluentform_get_available_form_integrations', array($this, 'pushIntegration'), $this->priority, 2);

        add_filter('fluentform_global_notification_feed_' . $this->settingsKey, array($this, 'setFeedAtributes'), 10, 2);

        add_filter('fluentform_get_integration_defaults_' . $this->integrationKey, array($this, 'getIntegrationDefaults'), 10, 2);
        add_filter('fluentform_get_integration_settings_fields_' . $this->integrationKey, array($this, 'getSettingsFields'), 10, 2);
        add_filter('fluentform_get_integration_merge_fields_' . $this->integrationKey, array($this, 'getMergeFields'), 10, 3);

        add_filter('fluentform_save_integration_settings_' . $this->integrationKey, array($this, 'setMetaKey'), 10, 2);
        add_filter('fluentform_get_integration_values_' . $this->integrationKey, array($this, 'prepareIntegrationFeed'), 10, 3);
    }

    public function registerNotificationHooks()
    {
        if ($this->isConfigured()) {
            add_filter('fluentform_global_notification_active_types', array($this, 'addActiveNotificationType'), $this->priority);
            add_action('fluentform_integration_notify_' . $this->settingsKey, array($this, 'notify'), $this->priority, 4);
        }
    }

    public function notify($feed, $formData, $entry, $form)
    {
        // Each integration have to implement this notify method
        return;
    }

    public function addGlobalMenu($setting)
    {
        $setting[$this->integrationKey] = [
            'hash'         => 'general-' . $this->integrationKey . '-settings',
            'component'    => 'general-integration-settings',
            'settings_key' => $this->integrationKey,
            'title'        => $this->title
        ];
        return $setting;
    }

    public function addNotificationType($types)
    {
        $types[] = $this->settingsKey;
        return $types;
    }

    public function addActiveNotificationType($types)
    {
        $types[$this->settingsKey] = $this->integrationKey;
        return $types;
    }

    public function getGlobalSettings($settings)
    {
        return $settings;
    }

    public function saveGlobalSettings($settings)
    {
        return $settings;
    }

    public function getGlobalFields($fields)
    {
        return $fields;
    }

    public function setMetaKey($data, $integrationId)
    {
        $data['meta_key'] = $this->settingsKey;
        return $data;
    }

    public function prepareIntegrationFeed($setting, $feed, $formId)
    {
        $defaults = $this->getIntegrationDefaults([], $formId);

        foreach ($setting as $settingKey => $settingValue) {
            if ($settingValue == 'true') {
                $setting[$settingKey] = true;
            } else if ($settingValue == 'false') {
                $setting[$settingKey] = false;
            } else if ($settingKey == 'conditionals') {
                if ($settingValue['status'] == 'true') {
                    $settingValue['status'] = true;
                } else if ($settingValue['status'] == 'false') {
                    $settingValue['status'] = false;
                }
                $setting['conditionals'] = $settingValue;
            }
        }

        if (!empty($setting['list_id'])) {
            $setting['list_id'] = (string)$setting['list_id'];
        }

        return wp_parse_args($setting, $defaults);
    }

    public abstract function getIntegrationDefaults($settings, $formId);

    public abstract function pushIntegration($integrations, $formId);

    public abstract function getSettingsFields($settings, $formId);

    public abstract function getMergeFields($list, $listId, $formId);

    public function setFeedAtributes($feed, $formId)
    {
        $feed['provider'] = $this->integrationKey;
        $feed['provider_logo'] = $this->logo;
        return $feed;
    }

    public function isConfigured()
    {
        $globalStatus = $this->getApiSettings();
        return $globalStatus && $globalStatus['status'];
    }

    public function isEnabled()
    {
        $globalModules = get_option('fluentform_global_modules_status');
        return $globalModules && isset($globalModules[$this->integrationKey]) && $globalModules[$this->integrationKey] == 'yes';
    }

    public function getApiSettings()
    {
        $settings = get_option($this->optionKey);
        if (!$settings || empty($settings['status'])) {
            $settings = [
                'apiKey' => '',
                'status' => false
            ];
        }
        return $settings;
    }

    protected function getSelectedTagIds($data, $inputData, $simpleKey = 'tag_ids', $routingId = 'tag_ids_selection_type', $routersKey = 'tag_routers')
    {
        $routing = ArrayHelper::get($data, $routingId, 'simple');
        if(!$routing || $routing == 'simple') {
            return ArrayHelper::get($data, $simpleKey, []);
        }

        $routers = ArrayHelper::get($data, $routersKey);
        if(empty($routers)) {
            return [];
        }

        return $this->evaluateRoutings($routers, $inputData);
    }

    protected function evaluateRoutings($routings, $inputData)
    {
        $validInputs = [];
        foreach ($routings as $routing) {
            $inputValue = ArrayHelper::get($routing, 'input_value');
            if(!$inputValue) {
                continue;
            }
            $condition = [
                'conditionals' => [
                    'status'     => true,
                    'is_test' => true,
                    'type'       => 'any',
                    'conditions' => [
                        $routing
                    ]
                ]
            ];

            if (\FluentForm\App\Services\ConditionAssesor::evaluate($condition, $inputData)) {
                $validInputs[] = $inputValue;
            }
        }

        return $validInputs;
    }
}