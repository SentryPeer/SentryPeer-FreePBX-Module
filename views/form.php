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

<form action="" method="post" class="fpbx-submit" id="hwform" name="hwform" data-fpbx-delete="config.php?display=returnontransfer">
    <input type="hidden" name='action' value="save">

    <div class="element-container">
        <div class="row">
            <div class="form-group">
                <div class="col-md-3">
                    <label class="control-label" for="client-id"><?php echo _("SentryPeer Client ID") ?></label>
                    <i class="fa fa-question-circle fpbx-help-icon" data-for="client-id"></i>
                </div>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="alertinfo-client-id" name="alertinfo-client-id" value="<?php echo $settings['sentrypeer-client-id'];?>">
                </div>
                <div class="col-md-3">
                    <label class="control-label" for="client-secret"><?php echo _("SentryPeer Client Secret") ?></label>
                    <i class="fa fa-question-circle fpbx-help-icon" data-for="client-secret"></i>
                </div>
                <div class="col-md-9">
                    <input type="text" class="form-control" id="alertinfo-client-secret" name="alertinfo-client-secret" value="<?php echo $settings['sentrypeer-client-secret'];?>">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <span id="alertinfo-help" class="help-block fpbx-help-block"><?php echo _('You can get your Sentrypeer Client ID and Client Secret from your <a href="https://sentrypeer.com/dashboard" title="SentryPeer Dashboard">SentryPeer Dashboard</a>')?></span>
            </div>
        </div>
    </div>
</form>

