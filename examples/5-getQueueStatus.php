<?php

    require './common.php';

    $list = $manager->getQueueStatus();

    print_r($list);