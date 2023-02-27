<?php
/* SPDX-License-Identifier: AGPL-3.0  */
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
        out(_("Installing the SentryPeer Module"));
        $this->setConfig('sentrypeer-client-id', 'Please enter your SentryPeer Client ID from https://sentrypeer.com/settings');
        $this->setConfig('sentrypeer-client-secret', 'Please enter your SentryPeer Client Secret from https://sentrypeer.com/settings');
    }

    public function uninstall()
    {
        out(_("Uninstalling the SentryPeer Module"));
        $this->delConfig('sentrypeer-client-id');
        $this->delConfig('sentrypeer-client-secret');
        $this->delConfig('sentrypeer-access-token');
        $this->delConfig('sentrypeer-setup-complete');
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
        /*
         * Parts taken from https://git.freepbx.org/projects/FPBXCN/repos/outcnam/browse/Outcnam.class.php#101
         * as per suggestion from @lgaetz
         * https://community.freepbx.org/t/custom-module-development-starter-module-to-read-and-publishing-process/88563/17
         */
        $context = "macro-dialout-trunk";
        $dial_macro_exists = false;
        $exten = "s";
        $webroot = $this->FreePBX->Config->get('AMPWEBROOT');
        // the dial macro will only exist if there is at least one outroute defined with a trunk
        // just checking for the existence of routes and trunks is not sufficient
        $routes = $this->FreePBX->Core->getAllRoutes();
        foreach ($routes as $route) {
            if (!empty($this->FreePBX->Core->getRouteTrunksByID($route['route_id']))) {
                $dial_macro_exists = true;
            }
        }
        if ($dial_macro_exists) {
            // splice - https://wiki.freepbx.org/pages/viewpage.action?pageId=98701336
            $ext->splice($context, $exten, '', new \ext_set('ORIGINAL_NUM_FOR_SENTRYPEER', '${ARG2}'), "", 1);
            $ext->splice($context, $exten, "gocall", new \ext_noop('Checking ${ORIGINAL_NUM_FOR_SENTRYPEER} with SentryPeer'), "", 1);
            $ext->splice($context, $exten, "gocall", new \ext_agi('sentrypeer.php, ${ORIGINAL_NUM_FOR_SENTRYPEER}'), "", 2);
            $ext->splice($context, $exten, "gocall", new \ext_noop('SentryPeer Finished'), "", 3);

            // Add our own custom context so others can hook into it via extensions_custom.conf
            $ext->addInclude('macro-dialout-trunk', 'sentrypeer-context', 'For the SentryPeer service - https://sentypeer.com'); // Add the context to from-internal
            $mcontext = 'sentrypeer-context';
            $ext->add($mcontext, $exten, '', new \ext_noop('Number found on in the SentryPeer database. Hanging up the call.'));
            $ext->add($mcontext, $exten, '', new \ext_goto('1', 'h', 'macro-dialout-trunk'));
        }
    }

    public function doConfigPageInit($page)
    {
    }

    public function getActionBar($request)
    {
        $buttons = array();
        switch ($_GET['display']) {
            case 'sentrypeer':
                $buttons = array(
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
                break;
        }
        return $buttons;
    }

    public function showPage()
    {
        $setup_complete = $this->getConfig('sentrypeer-setup-complete');
        $access_token_issue = false;
        if (isset($_POST['action']) && $_POST['action'] == "save") {

            if (isset($_POST['client-id']) && isset($_POST['client-secret'])) {

                $this->setConfig('sentrypeer-client-id', $_POST['client-id']);
                $this->setConfig('sentrypeer-client-secret', $_POST['client-secret']);

                dbug("Saved client-id and client-secret.");
                unset($_POST);

                $got_access_token = $this->getAndSaveAccessToken();
                if ($got_access_token) {
                    $this->setConfig('sentrypeer-setup-complete', true);
                    $setup_complete = true;
                    needreload();
                } else {
                    $access_token_issue = true;
                    dbug("Failed to get access token.");
                }
            }
        }

        $subhead = _('Use SentryPeer® to help prevent VoIP cyberattacks, fraudulent VoIP phone calls (toll fraud) and improve cybersecurity by detecting early stage reconnaissance attempts.');
        $settings = array(
            'sentrypeer-client-id' => $this->getConfig('sentrypeer-client-id') ? $this->getConfig('sentrypeer-client-id') : 'not-found',
            'sentrypeer-client-secret' => $this->getConfig('sentrypeer-client-secret') ? $this->getConfig('sentrypeer-client-secret') : 'not-found',
        );
        $content = load_view(__DIR__ . '/views/form.php', array('settings' => $settings, 'setup_complete' => $setup_complete, 'access_token_issue' => $access_token_issue));
        show_view(__DIR__ . '/views/main.php', array('subhead' => $subhead, 'content' => $content));
    }

    public function getAndSaveAccessToken(): bool
    {
        $client_id = $this->getConfig('sentrypeer-client-id');
        $client_secret = $this->getConfig('sentrypeer-client-secret');

        $access_token_url = 'https://dev-vtqcrudk2kakzqos.uk.auth0.com/oauth/token';
        $timeout = 2;

        $ch = curl_init();

        $headers = ['Content-Type: application/json;charset=utf-8', 'Accept: application/json;charset=utf-8'];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERAGENT, 'SentryPeer-FreePBX-Module/1.0');
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'audience' => 'https://sentrypeer.com/api',
            'grant_type' => 'client_credentials'
        )));

        curl_setopt($ch, CURLOPT_URL, $access_token_url);

        $json = curl_exec($ch);
        $err = curl_error($ch);

        curl_close($ch);

        if ($err) {
            dbug("cURL Error #:" . $err);
            return false;
        } else {
            $res = json_decode($json, true);
            if (isset($res['access_token'])) {
                $this->setConfig('sentrypeer-access-token', $res['access_token']);
                dbug("Got SentryPeer API access token.");
                return true;
            } else {
                dbug("Error getting access token: " . $json);
                return false;
            }
        }
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
