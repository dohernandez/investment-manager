<?php

namespace App\EventSubscriber;

use KevinPapst\AdminLTEBundle\Event\KnpMenuEvent;
use KevinPapst\AdminLTEBundle\Event\ThemeEvents;
use Knp\Menu\MenuItem;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Create a side menu base on the configuration defined in config/menu/menu.yaml
 *
 * Example:
 *
 * menu:
 *  OneMenuItem: # item menu name (required)
 *      label: '' item menu label (required)
 *      icon: '' item menu icon (optional)
 *      route: '' item menu route (optional)
 *      items: # sub items (optional)
 *
 * @package App\EventSubscriber
 */
class KnpMenuBuilderSubscriber implements EventSubscriberInterface
{
    private const CONFIG_MENU = 'menu.yaml';

    public static function getSubscribedEvents(): array
    {
        return [
            ThemeEvents::THEME_SIDEBAR_SETUP_KNP_MENU => ['onSetupMenu', 100],
        ];
    }

    public function onSetupMenu(KnpMenuEvent $event)
    {
        // Loading menu config
        $menuItems = Yaml::parseFile(\dirname(__DIR__) . '/../config/menu/' . self::CONFIG_MENU);

        $menu = $event->getMenu();

        $menu->addChild('MainNavigationMenuItem', [
            'label'        => 'MAIN NAVIGATION',
            'childOptions' => $event->getChildOptions()
        ])->setAttribute('class', 'header');

        $this->appendChild($event, $menu, $menuItems['menu']);
    }

    /**
     * Append the sub item (child) to its parent.
     *
     * @param KnpMenuEvent $event
     * @param MenuItem $parent
     * @param array $child
     *
     * @return MenuItem
     */
    private function appendChild(KnpMenuEvent $event, MenuItem $parent, array $child): MenuItem
    {
        foreach ($child as $key => $item) {
            /** @var MenuItem $menuItem */
            $menuItem = $parent->addChild($key, [
                    'label'        => isset($item['label']) ? $item['label'] : '',
                    'route'        => isset($item['route']) ? $item['route'] : '',
                    'childOptions' => $event->getChildOptions(),
                ]
            )->setLabelAttribute('icon', isset($item['icon']) ? $item['icon'] : '');

            if (isset($item['items']) && !empty($item['items'])) {
                $this->appendChild($event, $menuItem, $item['items']);
            }
        }

        return $parent;
    }
}
