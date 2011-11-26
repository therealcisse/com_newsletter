<?php

defined('_JEXEC') or die;

header('content-type:application/json', true, 200);
echo json_encode(
    array(
        'results' => $this->categories,
        'success' => true,
        'Total' => count($this->categories)
    )
);