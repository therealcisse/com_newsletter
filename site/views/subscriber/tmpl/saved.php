<?php

defined('_JEXEC') or die;

if($this->item) {
    JResponse::setHeader('Content-Type', 'application/json', true);
    $this->item->success = true;
    echo json_encode($this->item);
}
