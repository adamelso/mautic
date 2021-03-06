<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticAddon\MauticEmailMarketingBundle\Integration;

/**
 * Class IcontactIntegration
 */
class IcontactIntegration extends EmailAbstractIntegration
{

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getName ()
    {
        return 'Icontact';
    }

    /**
     * @return string
     */
    public function getDisplayName ()
    {
        return 'iContact';
    }

    public function getAuthenticationType()
    {
        return 'rest';
    }

    /**
     * Get a list of keys required to make an API call.  Examples are key, clientId, clientSecret
     *
     * @return array
     */
    public function getRequiredKeyFields ()
    {
        return array(
            'API-AppId'      => 'mautic.icontact.keyfield.appid',
            'API-Username'   => 'mautic.icontact.keyfield.username',
            'API-Password'   => 'mautic.icontact.keyfield.password'
        );
    }

    /**
     * @return array
     */
    public function getSecretKeys()
    {
        return array(
            'API-Password'
        );
    }

    public function getApiUrl()
    {
        return 'https://app.icontact.com/icp/a';
    }

    /**
     * Get account ID and client folder ID
     *
     * @param array $settings
     * @param array $parameters
     */
    public function authCallback($settings = array(), $parameters = array())
    {
        $url = $this->getApiUrl();

        $response = $this->makeRequest($url, $parameters);

        $keys = array();
        if (!empty($response['accounts'])) {
            $keys['accountId'] = $response['accounts'][0]['accountId'];

            $url .= '/' . $keys['accountId'] . '/c';
            $response = $this->makeRequest($url, $parameters);

            if (!empty($response['clientfolders'])) {
                $keys['clientFolderId'] = $response['clientfolders'][0]['clientFolderId'];

               $this->extractAuthKeys($keys, 'clientFolderId');
            }
        }
    }

    /**
     * @param        $url
     * @param array  $parameters
     * @param string $method
     * @param array  $settings
     *
     * @return mixed|string
     */
    public function makeRequest($url, $parameters = array(), $method = 'GET', $settings = array())
    {
        $settings['headers'] = array(
            'Except:',
            'Accept: application/json',
            'Content-Type: application/json',
            'Api-Version: 2.2',
            'Api-AppId: ' . $this->keys['API-AppId'],
            'Api-Username: ' . $this->keys['API-Username'],
            'API-Password: ' . $this->keys['API-Password']
        );

        return parent::makeRequest($url, $parameters, $method, $settings);
    }

    /**
     * @return bool
     */
    public function isAuthorized()
    {
        $keys = $this->getRequiredKeyFields();
        foreach ($keys as $k => $l) {
            if (empty($this->keys[$k])) {
                return false;
            }
        }

        if (empty($this->keys['accountId']) || empty($this->keys['clientFolderId'])) {
            return false;
        }

        return true;
    }

    /**
     * Returns settings for the integration form
     *
     * @return array
     */
    public function getFormSettings()
    {
        return array(
            'requires_callback'      => false,
            'requires_authorization' => true
        );
    }

    /**
     * @return array
     */
    public function getAvailableLeadFields ($settings = array())
    {
        static $leadFields = array();

        if (empty($leadFields)) {
            $fields = array(
                'email',
                'prefix',
                'firstName',
                'lastName',
                'suffix',
                'street',
                'street2',
                'city',
                'state',
                'postalCode',
                'phone',
                'fax',
                'business'
            );

            $leadFields = array();
            foreach ($fields as $f) {
                $leadFields[$f] = array(
                    'label' => 'mautic.icontact.field.' . $f,
                    'type'  => 'string',
                    'required' => ($f == 'email' ) ? true : false
                );
            }

           $customfields = $this->getApiHelper()->getCustomFields();

            if (!empty($customfields['customfields'])) {
                foreach ($customfields['customfields'] as $field) {
                    $leadFields['cf_' . $field['customFieldId']] = array(
                        'label'    => $field['publicName'],
                        'type'     => 'string',
                        'required' => false
                    );
                }
            }
        }

        return $leadFields;
    }


    /**
     * @param $lead
     */
    public function pushLead($lead, $config = array())
    {
        $config = $this->mergeConfigToFeatureSettings($config);

        $mappedData = $this->populateLeadData($lead, $config);

        if (empty($mappedData)) {
            return false;
        } elseif (empty($mappedData['email'])) {
            return false;
        } elseif (!isset($config['list_settings'])) {
            return false;
        }

        try {
            if ($this->isAuthorized()) {
                $customfields = array();
                foreach ($mappedData as $k => &$v) {
                    if (strpos($k, 'cf_') === 0) {
                        $customfields[str_replace('cf_', '', $k)] = (string) $v;
                        unset($mappedData[$k]);
                    } else {
                        $v = (string) $v;
                    }
                }

                $listId = $config['list_settings']['list'];

                if (!empty($customfields)) {
                    $mappedData += $customfields;
                }

                $this->getApiHelper()->subscribeLead($listId, $mappedData);

                return true;
            }
        } catch (\Exception $e) {
            die(var_dump($e->getMessage()));
            $this->logIntegrationError($e);
        }

        return false;
    }
}
