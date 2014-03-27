<?php

namespace Jeroen\Bundle\BlogBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {
    	$user = $this->container->get('security.context')->getToken()->getUser();
        $logged_in = is_object($user) ? TRUE : FALSE;

        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');

        $menu->addChild('Home', array('route' => 'jeroen_blog_homepage'));

        if($logged_in) {
            $menu->addChild('Log out', array('route' => 'fos_user_security_logout'));
        }
        else {
            $menu->addChild('Log in', array('route' => 'fos_user_security_login'));
        }

        return $menu;
    }

    public function blogTabs(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav nav-pills');

        $menu->addChild('View', array(
            'route' => 'jeroen_blog_view',
            'routeParameters' => array('id' => $options['id']),
        )); 
        $menu->addChild('Edit', array(
            'route' => 'jeroen_blog_edit',
            'routeParameters' => array('id' => $options['id']),
        ));
        $menu->addChild('Delete', array(
            'route' => 'jeroen_blog_delete',
            'routeParameters' => array('id' => $options['id']),
        ));

        return $menu;
    }
}