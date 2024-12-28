<?php

    use Coco\telegraph\dom\E;

    require './common.php';

    $doms = [
        E::splitLine(),
        E::h3(date('Y-m-d H:i:s')),
        E::hr(),
    ];

    $path = 'test-12-27-303';

    $manager->updatePageToQueue($path, 'test111', E::toJson(E::container($doms)));

