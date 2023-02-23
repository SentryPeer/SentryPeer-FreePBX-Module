<?php
/* SPDX-License-Identifier: GPL-2.0-only OR GPL-3.0-only  */
/* Copyright (c) 2023 Gavin Henry <ghenry@sentrypeer.org> */
/*
   _____            _              _____
  / ____|          | |            |  __ \
 | (___   ___ _ __ | |_ _ __ _   _| |__) |__  ___ _ __
  \___ \ / _ \ '_ \| __| '__| | | |  ___/ _ \/ _ \ '__|
  ____) |  __/ | | | |_| |  | |_| | |  |  __/  __/ |
 |_____/ \___|_| |_|\__|_|   \__, |_|   \___|\___|_|
                              __/ |
                             |___/
*/

namespace FreePBX\modules;

class Sentrypeer extends \FreePBX_Helpers implements \BMO
{
    public function __construct($freepbx = null)
    {
        if ($freepbx == null) {
            throw new Exception("Not given a FreePBX Object");
        }
        $this->FreePBX = $freepbx;
        $this->db = $freepbx->Database;
    }

    public function install()
    {
        $this->setConfig('sentrypeer-client-id', 'sentrypeer-client-id');
        $this->setConfig('sentrypeer-client-secret', 'sentrypeer-client-secret');
    }

    public function uninstall()
    {
        $this->delConfig('sentrypeer-client-id');
        $this->delConfig('sentrypeer-client-secret');
    }

    public function backup()
    {
    }

    public function restore($backup)
    {
    }

    /**
     * Set Priority for Dialplan Hooking
     * Core sits at a priority of 600
     * @method myDialplanHooks
     * @return string        Priority
     */
    public static function myDialplanHooks()
    {
        return 900;
    }

    /**
     * Hook into Dialplan (extensions_additional.conf)
     * @method doDialplanHook
     * @param class $ext The Extensions Class https://wiki.freepbx.org/pages/viewpage.action?pageId=98701336
     * @param string $engine Always Asterisk, Legacy
     * @param string $priority Priority
     */
    public function doDialplanHook(&$ext, $engine, $priority)
    {
        $ext->addInclude('from-internal-additional', 'sentrypeer-context'); // Add the context to from from-internal
        $mcontext = 'sentrypeer-context';
        $ext->add($mcontext, '_X!', '1', new \ext_noop('Checking ${EXTEN} with SentryPeer'));
        $ext->add($mcontext, '_X!', 'n', new \ext_agi('sentrypeer.php, ${EXTEN}'));
    }

    public function doConfigPageInit($page)
    {
    }

    public function ucpConfigPage($mode, $user, $action)
    {
    }

    public function ucpAddUser($id, $display, $ucpStatus, $data)
    {
    }

    public function ucpUpdateUser($id, $display, $ucpStatus, $data)
    {
    }

    public function ucpDelUser($id, $display, $ucpStatus, $data)
    {
    }

    public function ucpAddGroup($id, $display, $data)
    {
    }

    public function ucpUpdateGroup($id, $display, $data)
    {
    }

    public function ucpDelGroup($id, $display, $data)
    {
    }

    public function getActionBar($request)
    {
        $buttons = array();
        switch ($_GET['display']) {
            case 'sentrypeer':
                $buttons = array(
                    'delete' => array(
                        'name' => 'delete',
                        'id' => 'delete',
                        'value' => _('Delete')
                    ),
                    'reset' => array(
                        'name' => 'reset',
                        'id' => 'reset',
                        'value' => _('Reset')
                    ),
                    'submit' => array(
                        'name' => 'submit',
                        'id' => 'submit',
                        'value' => _('Submit')
                    )
                );
                if (empty($_GET['extdisplay'])) {
                    unset($buttons['delete']);
                }
                break;
        }
        return $buttons;
    }

    public function showPage()
    {

        $subhead = _('Use SentryPeer® to help prevent VoIP cyberattacks, fraudulent VoIP phone calls (toll fraud) and improve cybersecurity by detecting early stage reconnaissance attempts.');
        $settings = array(
            'sentrypeer-client-id' => $this->getConfig('sentrypeer-client-id') ? $this->getConfig('sentrypeer-client-id') : 'not-found',
            'sentrypeer-client-secret' => $this->getConfig('sentrypeer-client-secret') ? $this->getConfig('sentrypeer-client-secret') : 'not-found',
        );
        $content = load_view(__DIR__ . '/views/form.php', array('settings' => $settings));
        show_view(__DIR__ . '/views/main.php', array('subhead' => $subhead, 'content' => $content));
    }

    public function ajaxRequest($req, &$setting)
    {
        switch ($req) {
            case 'getJSON':
                return true;
                break;
            default:
                return false;
                break;
        }
    }

    public function ajaxHandler()
    {
        switch ($_REQUEST['command']) {
            case 'getJSON':
                switch ($_REQUEST['jdata']) {
                    case 'grid':
                        $ret = array();
                        /*code here to generate array*/
                        return $ret;
                        break;

                    default:
                        return false;
                        break;
                }
                break;

            default:
                return false;
                break;
        }
    }

    public function getRightNav($request)
    {
    }
}
