<?php
require_once('../../config.php');
require_once("$CFG->dirroot/repository/morsle/lib.php");
require_once("$CFG->dirroot/google/lib.php");
$morsle = new repository_morsle();
//$morsle->get_token('drive');
$status = $morsle->cron();