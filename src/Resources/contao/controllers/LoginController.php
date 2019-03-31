<?php

/**
 * Contao Open Source CMS
 * Copyright (c) 2005-2019 Leo Feyer
 * @package BUF (Beurteilen und Fördern)
 * @author Marko Cupic m.cupic@gmx.ch, 2014-2019
 * @link    https://github.com/markocupic/buf-bundle
 * @license MIT
 */

namespace Markocupic\BufBundle;

use Contao\StringUtil;
use Contao\System;
use Contao\Input;
use Contao\Environment;
use Contao\Frontend;

/**
 * Class LoginController
 * @package Markocupic\BufBundle
 */
class LoginController extends Frontend
{
    /**
     * @var $objMainController
     */
    protected $objMainController;

    /**
     * LoginController constructor.
     * @param $objMainController
     */
    public function __construct($objMainController)
    {
        $this->objMainController = $objMainController;
        $this->import('FrontendUser', 'User');
        return parent::__construct();
    }

    /**
     *
     */
    public function authenticate()
    {
        $container = System::getContainer();

        // Logout and redirect to the website root if the current page is protected
        if (Input::get('act') == 'logout')
        {
            if ($container->get('contao.security.token_checker')->hasFrontendUser())
            {
                $logoutPath = $container->get('security.logout_url_generator')->getLogoutPath();
                $this->redirect($logoutPath);
            }

            $this->redirect('');
        }
    }

    /**
     * @param $objTemplate
     * @return mixed
     */
    public function setTemplate($objTemplate)
    {
        $container = System::getContainer();

        /** @var RouterInterface $router */
        $router = $container->get('router');

        $objTemplate->hasError = $this->hasError;
        $objTemplate->username = $GLOBALS['TL_LANG']['MSC']['username'];
        $objTemplate->password = $GLOBALS['TL_LANG']['MSC']['password'][0];
        $objTemplate->action = $router->generate('contao_frontend_login');
        $objTemplate->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['login']);
        $objTemplate->value = specialchars(Input::post('username'));
        $objTemplate->autologin = ($this->autologin && $GLOBALS['TL_CONFIG']['autologin'] > 0);
        $objTemplate->autoLabel = $GLOBALS['TL_LANG']['MSC']['autologin'];

        return $objTemplate;
    }

}
