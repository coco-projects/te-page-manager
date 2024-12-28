<?php

    declare(strict_types = 1);

    namespace Coco\tePageManager;

    use Coco\queue\MissionManager;
    use Coco\queue\missionProcessors\GuzzleMissionProcessor;
    use Coco\queue\Queue;
    use Coco\queue\resultProcessor\CustomResultProcessor;
    use Coco\tableManager\TableRegistry;
    use Coco\tePageManager\missions\TelegraphMission;
    use Coco\tePageManager\tables\Account;
    use Coco\tePageManager\tables\Pages;
    use DI\Container;
    use Symfony\Component\Cache\Adapter\RedisAdapter;
    use Symfony\Component\Cache\Marshaller\DefaultMarshaller;
    use Symfony\Component\Cache\Marshaller\DeflateMarshaller;

    class Manager
    {
        protected ?Container $container = null;

        protected MissionManager $telegraphQueueMissionManager;

        public Queue $createAccountQueue;
        public Queue $createPageQueue;
        public Queue $updatePageQueue;

        protected bool $enableRedisLog = false;
        protected bool $enableEchoLog  = false;

        protected ?string $logNamespace;

        protected string $redisHost     = '127.0.0.1';
        protected string $redisPassword = '';
        protected int    $redisPort     = 6379;
        protected int    $redisDb       = 14;

        protected string $mysqlDb;
        protected string $mysqlHost     = '127.0.0.1';
        protected string $mysqlUsername = 'root';
        protected string $mysqlPassword = 'root';
        protected int    $mysqlPort     = 3306;

        protected ?string $telegraphPageShortName  = 'bob';
        protected ?string $telegraphPageAuthorName = 'tily';
        protected ?string $telegraphPageAuthorUrl  = '';
        protected ?string $telegraphProxy          = null;
        protected int     $telegraphTimeout        = 30;

        protected int $telegraphQueueDelayMs  = 0;
        protected int $telegraphQueueMaxTimes = 10;

        protected ?string $accountTableName = null;
        protected ?string $pagesTableName   = null;

        const CREATE_ACCOUNT_QUEUE = 'CREATE_ACCOUNT';
        const CREATE_PAGE_QUEUE    = 'CREATE_INDEX_PAGE';
        const UPDATE_PAGE_QUEUE    = 'UPDATE_INDEX_PAGE';

        public function __construct(protected string $redisNamespace, ?Container $container = null)
        {
            if (!is_null($container))
            {
                $this->container = $container;
            }
            else
            {
                $this->container = new Container();
            }
            $this->redisNamespace .= '-tg-pages';
            $this->logNamespace   = $this->redisNamespace . ':tg-log:';
        }

        public function queueMonitor(): void
        {
            $this->telegraphQueueMissionManager->getAllQueueInfoTable();
        }

        public function getQueueStatus(): array
        {
            return $this->telegraphQueueMissionManager->getAllQueueInfo();
        }

        public function restoreFailureMission(): void
        {
            $this->createAccountQueue->restoreErrorMission();
            $this->createAccountQueue->restoreTimesReachedMission();
            $this->createPageQueue->restoreErrorMission();
            $this->createPageQueue->restoreTimesReachedMission();
            $this->updatePageQueue->restoreErrorMission();
            $this->updatePageQueue->restoreTimesReachedMission();
        }

        public function initServer(): static
        {
            $this->initMissionManager();
            $this->initRedis();
            $this->initMysql();

            return $this;
        }


        public function setTelegraphProxy(?string $telegraphProxy): static
        {
            $this->telegraphProxy = $telegraphProxy;

            return $this;
        }

        public function setEnableEchoLog(bool $enableEchoLog): static
        {
            $this->enableEchoLog = $enableEchoLog;

            return $this;
        }

        public function setEnableRedisLog(bool $enableRedisLog): static
        {
            $this->enableRedisLog = $enableRedisLog;

            return $this;
        }

        protected function initMissionManager(): static
        {
            $this->telegraphQueueMissionManager = new MissionManager($this->container);
            $this->telegraphQueueMissionManager->setPrefix($this->redisNamespace);

            $logName = 'queue-manager';
            $this->telegraphQueueMissionManager->setStandardLogger($logName);
            if ($this->enableRedisLog)
            {
                $this->telegraphQueueMissionManager->addRedisHandler(redisHost: $this->redisHost, redisPort: $this->redisPort, password: $this->redisPassword, db: $this->redisDb, logName: $this->logNamespace . $logName, callback: $this->telegraphQueueMissionManager::getStandardFormatter());
            }

            if ($this->enableEchoLog)
            {
                $this->telegraphQueueMissionManager->addStdoutHandler($this->telegraphQueueMissionManager::getStandardFormatter());
            }

            return $this;
        }

        public function setRedisConfig(string $host = '127.0.0.1', string $password = '', int $port = 6379, int $db = 9): static
        {
            $this->redisHost     = $host;
            $this->redisPassword = $password;
            $this->redisPort     = $port;
            $this->redisDb       = $db;

            return $this;
        }

        public function setMysqlConfig($db, $host = '127.0.0.1', $username = 'root', $password = 'root', $port = 3306): static
        {
            $this->mysqlHost     = $host;
            $this->mysqlPassword = $password;
            $this->mysqlUsername = $username;
            $this->mysqlPort     = $port;
            $this->mysqlDb       = $db;

            return $this;
        }

        protected function initRedis(): static
        {
            $this->container->set('redisClient', function(Container $container) {
                return (new \Redis());
            });

            $this->telegraphQueueMissionManager->initRedisClient(function(MissionManager $missionManager) {
                /**
                 * @var \Redis $redis
                 */
                $redis = $missionManager->getContainer()->get('redisClient');
                $redis->connect($this->redisHost, $this->redisPort);
                $this->redisPassword && $redis->auth($this->redisPassword);
                $redis->select($this->redisDb);

                return $redis;
            });

            $this->initCache();

            $this->initQueue();

            return $this;
        }

        protected function getRedisClient(): \Redis
        {
            return $this->container->get('redisClient');
        }


        protected function initMysql(): static
        {
            $this->container->set('mysqlClient', function(Container $container) {

                $registry = TableRegistry::initMysqlClient($this->mysqlDb, $this->mysqlHost, $this->mysqlUsername, $this->mysqlPassword, $this->mysqlPort,);

                $logName = 'te-mysql';
                $registry->setStandardLogger($logName);

                if ($this->enableRedisLog)
                {
                    $registry->addRedisHandler(redisHost: $this->redisHost, redisPort: $this->redisPort, password: $this->redisPassword, db: $this->redisDb, logName: $this->logNamespace . $logName, callback: $this->telegraphQueueMissionManager::getStandardFormatter());
                }

                if ($this->enableEchoLog)
                {
                    $registry->addStdoutHandler($this->telegraphQueueMissionManager::getStandardFormatter());
                }

                return $registry;
            });

            return $this;
        }

        public function getMysqlClient(): TableRegistry
        {
            return $this->container->get('mysqlClient');
        }

        protected function initCache(): static
        {
            $this->container->set('cacheManager', function(Container $container) {
                $marshaller   = new DeflateMarshaller(new DefaultMarshaller());
                $cacheManager = new RedisAdapter($container->get('redisClient'), $this->redisNamespace . '-tg-cache', 0, $marshaller);

                return $cacheManager;
            });

            return $this;
        }

        public function getCacheManager(): RedisAdapter
        {
            return $this->container->get('cacheManager');
        }

        protected function initQueue(): static
        {
            $this->createAccountQueue = $this->telegraphQueueMissionManager->initQueue(static::CREATE_ACCOUNT_QUEUE);
            $this->createPageQueue    = $this->telegraphQueueMissionManager->initQueue(static::CREATE_PAGE_QUEUE);
            $this->updatePageQueue    = $this->telegraphQueueMissionManager->initQueue(static::UPDATE_PAGE_QUEUE);

            return $this;
        }


        public function getRandToken(): string
        {
            $tokens = $this->getCacheManager()->get('telegraph:account_tokens', function($item) {
                $item->expiresAfter(60);
                $tab = $this->getAccountTable();

                $tokens = $tab->tableIns()->column($tab->getAccessTokenField());

                return $tokens;
            });

            return $tokens[rand(0, count($tokens) - 1)];
        }

        public function initAccountTable(string $name, callable $callback): static
        {
            $this->accountTableName = $name;

            $this->getMysqlClient()->initTable($name, Account::class, $callback);

            return $this;
        }

        public function getAccountTable(): Account
        {
            return $this->getMysqlClient()->getTable($this->accountTableName);
        }

        public function initPagesTable(string $name, callable $callback): static
        {
            $this->pagesTableName = $name;

            $this->getMysqlClient()->initTable($name, Pages::class, $callback);

            return $this;
        }

        public function getPagesTable(): Pages
        {
            return $this->getMysqlClient()->getTable($this->pagesTableName);
        }


        public function createAccountToQueue($number): void
        {
            for ($i = 1; $i <= $number; $i++)
            {
                $mission = new TelegraphMission();
                $mission->setTimeout($this->telegraphTimeout);
                $mission->index = $i;

                if (!is_null($this->telegraphProxy))
                {
                    $mission->setProxy($this->telegraphProxy);
                }

                $mission->createAccount($this->telegraphPageShortName, $this->telegraphPageAuthorName, $this->telegraphPageAuthorUrl);

                $this->createAccountQueue->addNewMission($mission);
            }
        }

        public function listenCreateAccount(): void
        {
            $queue = $this->createAccountQueue;

            $queue->setContinuousRetry(true);
            $queue->setDelayMs($this->telegraphQueueDelayMs);
            $queue->setEnable(true);
            $queue->setMaxTimes($this->telegraphQueueMaxTimes);
            $queue->setIsRetryOnError(true);
            $queue->setMissionProcessor(new GuzzleMissionProcessor());

            $success = function(TelegraphMission $mission) {
                $response = $mission->getResult();
                $json     = $response->getBody()->getContents();
                $result   = json_decode($json, true);

                if ($result['ok'])
                {
                    $accountTab = $this->getAccountTable();

                    $data = [
                        $accountTab->getShortNameField()   => $result['result']['short_name'],
                        $accountTab->getAuthorUrlField()   => $result['result']['author_url'],
                        $accountTab->getAuthorNameField()  => $result['result']['author_name'],
                        $accountTab->getAuthUrlField()     => $result['result']['auth_url'],
                        $accountTab->getAccessTokenField() => $result['result']['access_token'],
                        $accountTab->getTimeField()        => time(),
                    ];

                    if (!$accountTab->isPkAutoInc())
                    {
                        $data[$accountTab->getPkField()] = $accountTab->calcPk();
                    }

                    $res = $accountTab->tableIns()->insert($data);

                    if ($res)
                    {
                        $this->telegraphQueueMissionManager->logInfo('创建成功: ' . $mission->index);
                    }
                    else
                    {
                        $this->telegraphQueueMissionManager->logError('写入错误: ' . $mission->index);
                    }

                }
                else
                {
                    $this->telegraphQueueMissionManager->logError($mission->index . ' -- ' . $result['error']);
                }
            };

            $catch = function(TelegraphMission $mission, \Exception $exception) {
                $this->telegraphQueueMissionManager->logError($exception->getMessage());
            };

            $queue->addResultProcessor(new CustomResultProcessor($success, $catch));

            $queue->listen();
        }

        public function createPageToQueue(string $title, string $jsonContents, int $type = 1): void
        {
            $token   = $this->getRandToken();
            $mission = new TelegraphMission();
            $mission->setTimeout($this->telegraphTimeout);
            $mission->token = $token;
            $mission->type  = $type;
            $mission->title = $title;

            if (!is_null($this->telegraphProxy))
            {
                $mission->setProxy($this->telegraphProxy);
            }

            $mission->setAccessToken($token);
            $mission->createPage($title, $jsonContents, true);

            $this->telegraphQueueMissionManager->logInfo('createPage: ' . $title);
            $this->createPageQueue->addNewMission($mission);
        }

        public function listenCreatePage(): void
        {
            $queue = $this->createPageQueue;

            $queue->setContinuousRetry(true);
            $queue->setDelayMs($this->telegraphQueueDelayMs);
            $queue->setEnable(true);
            $queue->setMaxTimes($this->telegraphQueueMaxTimes);
            $queue->setIsRetryOnError(true);
            $queue->setMissionProcessor(new GuzzleMissionProcessor());

            $success = function(TelegraphMission $mission) {
                $response = $mission->getResult();
                $json     = $response->getBody()->getContents();
                $result   = json_decode($json, true);

                if ($result['ok'])
                {
                    $pageTab = $this->getPagesTable();

                    $data = [
                        $pageTab->getPathField()        => $result['result']['path'],
                        $pageTab->getUrlField()         => $result['result']['url'],
                        $pageTab->getTitleField()       => $result['result']['title'],
                        $pageTab->getDescriptionField() => $result['result']['description'],
                        $pageTab->getContentField()     => json_encode($result['result']['content'], 256),
                        $pageTab->getViewsField()       => $result['result']['views'],
                        $pageTab->getCanEditField()     => (int)$result['result']['can_edit'],
                        $pageTab->getTokenField()       => $mission->token,
                        $pageTab->getTypeField()        => $mission->type,
                        $pageTab->getUpdateTimeField()  => time(),
                        $pageTab->getTimeField()        => time(),
                    ];

                    if (!$pageTab->isPkAutoInc())
                    {
                        $data[$pageTab->getPkField()] = $pageTab->calcPk();
                    }

                    $re = $pageTab->tableIns()->insert($data);

                    if ($re)
                    {
                        $this->telegraphQueueMissionManager->logInfo('ok-' . $mission->title);
                    }
                    else
                    {
                        $this->telegraphQueueMissionManager->logError($json);
                    }
                }
                else
                {
                    $this->telegraphQueueMissionManager->logError($result['error']);
                }

            };

            $catch = function(TelegraphMission $mission, \Exception $exception) {
                $this->telegraphQueueMissionManager->logError($exception->getMessage());
            };

            $queue->addResultProcessor(new CustomResultProcessor($success, $catch));

            $queue->listen();
        }

        public function getAllList(int $type = -1): \think\model\Collection|array|\think\Collection
        {
            $pageTab   = $this->getPagesTable();
            $wherePage = [];

            if ($type > 0)
            {
                $wherePage = [
                    [
                        $pageTab->getTypeField(),
                        '=',
                        $type,
                    ],
                ];
            }

            return $pageTab->tableIns()->where($wherePage)->select();
        }

        public function getInfoByPath(string $path)
        {
            $pageTab = $this->getPagesTable();

            $wherePage = [
                [
                    $pageTab->getPathField(),
                    '=',
                    $path,
                ],
            ];

            return $pageTab->tableIns()->where($wherePage)->findOrEmpty();
        }

        public function updatePageToQueue(string $path, string $title, string $jsonContents, int $type = 1): void
        {
            $pageTab = $this->getPagesTable();

            $info = $this->getInfoByPath($path);

            if ($info)
            {
                $info[$pageTab->getTypeField()] = $type;

                $mission = new TelegraphMission();
                $mission->setTimeout($this->telegraphTimeout);
                $mission->info = $info;

                if (!is_null($this->telegraphProxy))
                {
                    $mission->setProxy($this->telegraphProxy);
                }

                $mission->setAccessToken($info['token']);
                $mission->editPage($path, $title, $jsonContents, true);

                $this->telegraphQueueMissionManager->logInfo('updatePage: ' . $title);
                $this->updatePageQueue->addNewMission($mission);
            }
            else
            {
                $this->telegraphQueueMissionManager->logInfo('updatePage: 不存在的path: ' . $path);
            }
        }

        public function listenUpdatePage(): void
        {
            $queue = $this->updatePageQueue;

            $queue->setContinuousRetry(true);
            $queue->setDelayMs($this->telegraphQueueDelayMs);
            $queue->setEnable(true);
            $queue->setMaxTimes($this->telegraphQueueMaxTimes);
            $queue->setIsRetryOnError(true);
            $queue->setMissionProcessor(new GuzzleMissionProcessor());

            $success = function(TelegraphMission $mission) {
                $response = $mission->getResult();
                $json     = $response->getBody()->getContents();
                $result   = json_decode($json, true);

                $pageTab = $this->getPagesTable();

                if ($result['ok'])
                {
                    $this->telegraphQueueMissionManager->logInfo(implode([
                        '更新成功，',
                        '标题:' . $mission->info[$pageTab->getPathField()] . '，',
                        'url:' . $mission->info[$pageTab->getUrlField()],
                    ]));

                    $data = [
                        $pageTab->getPathField()        => $result['result']['path'],
                        $pageTab->getUrlField()         => $result['result']['url'],
                        $pageTab->getTitleField()       => $result['result']['title'],
                        $pageTab->getDescriptionField() => $result['result']['description'],
                        $pageTab->getContentField()     => json_encode($result['result']['content'], 256),
                        $pageTab->getViewsField()       => $result['result']['views'],
                        $pageTab->getCanEditField()     => (int)$result['result']['can_edit'],
                        $pageTab->getTypeField()        => $mission->info[$pageTab->getTypeField()],
                        $pageTab->getUpdateTimeField()  => time(),
                    ];

                    $wherePage = [
                        [
                            $pageTab->getPathField(),
                            '=',
                            $mission->info[$pageTab->getPathField()],
                        ],
                    ];

                    $re = $pageTab->tableIns()->where($wherePage)->update($data);

                    if ($re)
                    {
                        $this->telegraphQueueMissionManager->logInfo('ok-' . $mission->info[$pageTab->getTitleField()]);
                    }
                    else
                    {
                        $this->telegraphQueueMissionManager->logError($json);
                    }
                }
                else
                {
                    $this->telegraphQueueMissionManager->logError($result['error']);
                }
            };

            $catch = function(TelegraphMission $mission, \Exception $exception) {
                $this->telegraphQueueMissionManager->logError($exception->getMessage());
            };

            $queue->addResultProcessor(new CustomResultProcessor($success, $catch));

            $queue->listen();
        }


    }

