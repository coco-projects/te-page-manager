<?php

    use Coco\tePageManager\Manager;

    require __DIR__ . '/../vendor/autoload.php';


    $manager = new Manager('p1');
    $manager->setTelegraphProxy('192.168.0.111:1080');

    $manager->setRedisConfig(db: 12);
    $manager->setMysqlConfig(db: 'ithinkphp_telegraph_test01');

    $manager->setEnableEchoLog(true);
    $manager->setEnableRedisLog(true);
    $manager->initServer();

    $manager->initAccountTable('te_page_account', function(\Coco\tePageManager\tables\Account $table) {
        $registry = $table->getTableRegistry();

        $table->setPkField('id');
        $table->setIsPkAutoInc(true);
    });

    $manager->initPagesTable('te_page_pages', function(\Coco\tePageManager\tables\Pages $table) {
        $registry = $table->getTableRegistry();

        $table->setPkField('id');
        $table->setIsPkAutoInc(false);
        $table->setPkValueCallable($registry::snowflakePKCallback());
    });

