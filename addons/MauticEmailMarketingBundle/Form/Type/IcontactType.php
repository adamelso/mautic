<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic Contributors. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticAddon\MauticEmailMarketingBundle\Form\Type;

use Mautic\CoreBundle\Factory\MauticFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ConstantContactType
 *
 * @package Mautic\FormBundle\Form\Type
 */
class IcontactType extends AbstractType
{

    /**
     * @var MauticFactory
     */
    private $factory;

    public function __construct (MauticFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm (FormBuilderInterface $builder, array $options)
    {

        /** @var \Mautic\AddonBundle\Helper\IntegrationHelper $helper */
        $helper = $this->factory->getHelper('integration');

        /** @var \MauticAddon\MauticEmailMarketingBundle\Integration\IcontactIntegration $object */
        $object = $helper->getIntegrationObject('Icontact');

        if ($object->isAuthorized()) {
            $api = $object->getApiHelper();
            try {
                $lists = $api->getLists();

                $choices = array();
                if (!empty($lists['lists'])) {
                    foreach ($lists['lists'] as $list) {
                        $choices[$list['listId']] = $list['name'];
                    }

                    asort($choices);
                }
            } catch (\Exception $e) {
                $choices = array();
                $error   = $e->getMessage();
            }

            $builder->add('list', 'choice', array(
                'choices'  => $choices,
                'label'    => 'mautic.emailmarketing.list',
                'required' => false,
                'attr'     => array(
                    'tooltip'  => 'mautic.emailmarketing.list.tooltip'
                )
            ));

            if (!empty($error)) {
                $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($error) {
                    $form = $event->getForm();

                    if ($error) {
                        $form['list']->addError(new FormError($error));
                    }
                });
            }

            if (isset($options['form_area']) && $options['form_area'] == 'integration') {
                $leadFields = $this->factory->getModel('addon')->getLeadFields();

                $fields = $object->getFormLeadFields();

                list ($specialInstructions, $alertType) = $object->getFormNotes('leadfield_match');
                $builder->add('leadFields', 'integration_fields', array(
                    'label'                => 'mautic.integration.leadfield_matches',
                    'required'             => true,
                    'lead_fields'          => $leadFields,
                    'data'                 => isset($options['data']['leadFields']) ? $options['data']['leadFields'] : array(),
                    'integration_fields'   => $fields,
                    'special_instructions' => $specialInstructions,
                    'alert_type'           => $alertType
                ));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions (OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array('form_area'));
    }

    /**
     * @return string
     */
    public function getName ()
    {
        return "emailmarketing_icontact";
    }
}