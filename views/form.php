<!-- SPDX-License-Identifier: AGPL-3.0                      -->
<!-- Copyright (c) 2023 Gavin Henry <ghenry@sentrypeer.org> -->
<!--   _____            _              _____                -->
<!--  / ____|          | |            |  __ \               -->
<!-- | (___   ___ _ __ | |_ _ __ _   _| |__) |__  ___ _ __  -->
<!--  \___ \ / _ \ '_ \| __| '__| | | |  ___/ _ \/ _ \ '__| -->
<!--  ____) |  __/ | | | |_| |  | |_| | |  |  __/  __/ |    -->
<!-- |_____/ \___|_| |_|\__|_|   \__, |_|   \___|\___|_|    -->
<!--                              __/ |                     -->
<!--                             |___/                      -->

<form action="" method="post" class="fpbx-submit" autocomplete="off" name="editSentryPeer"
      data-fpbx-delete="config.php?display=returnontransfer">
    <input type="hidden" name="action" id="action" value="save">

    <?php if (empty($saved)): ?>
        <div class="alert alert-info" role="alert">
            <p>This module requires a free account from the <a href="https://sentrypeer.com" target="_blank"
                                                               title="SentryPeer website">SentryPeer website<i
                            class="fa fa-external-link"
                            aria-hidden="true"></i></a>.
                Once you have created an account, you can generate a Client ID and Client Secret in your <a
                        href="https://sentrypeer.com/settings" target="_blank"
                        title="SentryPeer Account Settings">Settings <i
                            class="fa fa-external-link"
                            aria-hidden="true"></i></a> and enter them below.</p>

        </div>
    <?php else: ?>
        <?php if ($got_access_token): ?>
            <div class="alert alert-success" role="alert">
                <p>You have successfully connected to SentryPeer.</p>
            </div>
        <?php else: ?>
            <div class="alert alert-danger" role="alert">
                <p>There was a problem connecting to SentryPeer. Please check your Client ID and Client Secret.</p>
            </div>
        <?php endif; ?>

    <?php endif; ?>
    <div class="element-container">
        <div class="row">
            <div class="form-group">
                <div class="col-md-3">
                    <label class="control-label" for="client-id"><?php echo _("SentryPeer Client ID") ?></label>
                    <i class="fa fa-question-circle fpbx-help-icon" data-for="client-id"></i>
                </div>
                <div class="col-md-9">
                    <input type="text" class="form-control confidential" id="client-id" name="client-id"
                           value="<?php echo $settings["sentrypeer-client-id"]; ?>">
                </div>
                <div class="col-md-3">
                    <label class="control-label"
                           for="client-secret"><?php echo _("SentryPeer Client Secret") ?></label>
                    <i class="fa fa-question-circle fpbx-help-icon" data-for="client-secret"></i>
                </div>
                <div class="col-md-9">
                    <input type="text" class="form-control confidential" id="client-secret"
                           name="client-secret"
                           value="<?php echo $settings["sentrypeer-client-secret"]; ?>">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                    <span id="client-id-help"
                          class="help-block fpbx-help-block"><?php echo _('You can get your Sentrypeer Client ID from your <a href="https://sentrypeer.com/dashboard" title="SentryPeer Settings" target="_blank">SentryPeer Settings <i class="fa fa-external-link" aria-hidden="true"></i></a>') ?></span>
            </div>
            <div class="col-md-12">
                    <span id="client-secret-help"
                          class="help-block fpbx-help-block"><?php echo _('You can get your Sentrypeer Client Secret from your <a href="https://sentrypeer.com/dashboard" title="SentryPeer Settings" target="_blank">SentryPeer Settings <i class="fa fa-external-link" aria-hidden="true"></i></a>') ?></span>
            </div>
        </div>
    </div>
</form>

