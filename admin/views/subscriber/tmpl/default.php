<?php

defined('_JEXEC') or die;

if($this->item) {
    JResponse::setHeader('Content-Type', 'application/json', true);

    $ret = new stdClass;

    $ret->success = true;
    $ret->results = $this->item;

    echo json_encode($ret);
}
