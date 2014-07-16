<?php
/**
 * @package     Mautic
 * @copyright   2014 Mautic, NP. All rights reserved.
 * @author      Mautic
 * @link        http://mautic.com
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\DashboardBundle\EventListener;


use Mautic\CoreBundle\CoreEvents;
use Mautic\CoreBundle\Event as MauticEvent;
use Mautic\CoreBundle\EventListener\CommonSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class DashboardSubscriber
 *
 * @package Mautic\DashboardBundle\EventListener
 */
class DashboardSubscriber extends CommonSubscriber
{
    /**
     * @return array
     */
    static public function getSubscribedEvents()
    {
        return array(
            CoreEvents::BUILD_MENU  => array('onBuildMenu', 0),
            CoreEvents::BUILD_ROUTE => array('onBuildRoute', 0)
        );
    }

    /**
     * @param MenuEvent $event
     */
    public function onBuildMenu(MauticEvent\MenuEvent $event)
    {
        $path = __DIR__ . "/../Resources/config/menu.php";
        $items = include $path;
        $event->addMenuItems($items);
    }

    /**
     * @param RouteEvent $event
     */
    public function onBuildRoute(MauticEvent\RouteEvent $event)
    {
        $path = __DIR__ . "/../Resources/config/routing.php";
        $event->addRoutes($path);
    }

}