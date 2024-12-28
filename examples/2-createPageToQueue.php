<?php

    use Coco\telegraph\dom\E;

    require './common.php';

    $contents = E::container([

        E::hr(),

        E::splitLine(),
        E::h3('《这是h3这是h3这是h3》'),

        E::splitLine(),
        E::h4('《这是h4这是h4这是h4》'),

        E::splitLine(),
        E::p([
            E::span('《p 中的 span标签》'),
            'p 中的普通字符',
            ' ',
            E::a('https://www.baidu.com', '普通a标签 baidu.com'),
            E::a1('https://www.baidu.com', '普通a1标签 baidu.com'),
        ]),

        E::splitLine(),
        E::p('Use Private Packagist if you want to share private code as a Composer package with colleagues or customers without publishing it for everyone on Packagist.org. Private Packagist allows you to manage your own private Composer repository with per-user authentication, team management and integration in version control systems.'),

        E::splitLine(),
        E::p('如今，科技的魔力为我们开启了一扇通往过去的窗户。通过AI的巧妙结合，那些静止的影像重新焕发生机，让我们得以重返往昔，与记忆中的自己重逢。'),

        E::splitLine(),

        E::a('https://www.baidu.com'),
        E::br(),

        E::a('https://www.google.com', 'baidu.com 111'),
        E::br(),

        E::a1('https://www.sina.com'),
        E::br(),

        E::a1('https://www.douyin.com', 'douyin.com 111'),
        E::br(),

        E::splitLine(),
        E::span('hello-span'),
        E::br(),

        E::splitLine(),
        E::em('这是 ememem'),
        E::br(),

        E::splitLine(),
        E::i('这是 iiiiiii'),
        E::br(),

        E::splitLine(),
        E::s('这是 sssssss'),
        E::br(),

        E::splitLine(),
        E::u('这是 uuuuuuu'),
        E::br(),

        E::splitLine(),
        E::strong('这是 strong strong'),
        E::br(),

        E::splitLine(),
        E::aside([
            E::p([
                E::span('hello-aside-1  '),
                E::a('https://www.baidu.com', 'baidu.com'),
            ]),
            E::p([
                E::span('hello-aside-2  '),
                E::a('https://www.baidu.com', 'baidu.com'),
            ]),
        ]),

        E::splitLine(),
        E::aside([
            'Use Private Packagist if you want to share private code as a Composer package with colleagues or customers without publishing it for everyone on Packagist.org. Private Packagist allows you to manage your own private Composer repository with per-user authentication, team management and integration in version control systems.',
        ]),

        E::splitLine(),
        E::list([
            'list hello111',
            'list hello222',
            'list hello333',
        ]),

        E::splitLine(),
        E::list([
            E::span('list span hello111'),
            E::span('list span hello222'),
            E::span('list span hello333'),
        ]),

        E::splitLine(),
        E::list([
            E::strong(E::a('https://www.baidu.com')),
            E::strong(E::a('https://www.baidu.com')),
            E::strong(E::a('https://www.baidu.com')),
        ]),

        E::splitLine(),
        E::list([
            E::strong([
                'list 1: ',
                E::a('https://www.baidu.com'),
            ]),
            E::strong([
                'list 2: ',
                E::a('https://www.baidu.com'),
            ]),
            E::strong([
                'list 3: ',
                E::a('https://www.baidu.com'),
            ]),
        ]),

        E::splitLine(),
        E::list([
            E::strong(E::a('https://www.baidu.com', 'list baidu111.com')),
            E::strong(E::a('https://www.baidu.com', 'list baidu222.com')),
            E::strong(E::a('https://www.baidu.com', 'list baidu333.com')),
        ], true),

        E::splitLine(),
        E::blockquoteList([
            E::strong(E::a('https://www.baidu.com', 'blockquoteList baidu111.com')),
            E::strong(E::a('https://www.baidu.com', 'blockquoteList baidu222.com')),
            E::strong(E::a('https://www.baidu.com', 'blockquoteList baidu333.com')),
        ]),

        E::splitLine(),
        E::blockquoteList([
            E::strong(E::a('https://www.baidu.com', 'blockquoteList format 1 baidu111.com')),
            E::strong(E::a('https://www.baidu.com', 'blockquoteList format 1 baidu222.com')),
            E::strong(E::a('https://www.baidu.com', 'blockquoteList format 1 baidu333.com')),
        ], '__ID__. '),

        E::splitLine(),
        E::blockquoteList([
            E::strong(E::a('https://www.baidu.com', 'blockquoteList format 2 baidu111.com')),
            E::strong(E::a('https://www.baidu.com', 'blockquoteList format 2 baidu222.com')),
            E::strong(E::a('https://www.baidu.com', 'blockquoteList format 2 baidu333.com')),
        ], '__ID__> '),

        E::splitLine(),
        E::blockquoteList([
            '这个纲领性文件是如何诞生的',
            '马龙成奥运会5A级打卡点热',
            '沈腾一家出游被网友偶遇',
            '夏日经济 乘“热”而上',
        ], '__ID__. '),

        E::splitLine(),
        E::blockquoteList([
            E::strong([
                'AdminLTE：',
                E::a('https://github.com/almasaeed2010/AdminLTE'),
            ]),
            E::strong([
                'vue-Element-Admin：',
                E::a('https://github.com/PanJiaChen/vue-element-admin'),
            ]),
            E::strong([
                'tabler：',
                E::a('https://github.com/tabler/tabler'),
            ]),
        ], '__ID__. '),

        E::splitLine(),

        E::AListWithCaption3([
            [
                "href" => 'https://baidu.com',
            ],
            [
                "href" => 'https://google.com',
            ],
            [
                "href" => 'https://sina.com',
            ],
        ], '', false, '|'),

        E::br(),
        E::splitLine(),

        E::AListWithCaption3([
            [
                "href"    => 'https://baidu.com',
                "caption" => "AListWithCaption3 baidu",
            ],
            [
                "href"    => 'https://google.com',
                "caption" => "AListWithCaption3 google",
            ],
            [
                "href"    => 'https://sina.com',
                "caption" => "AListWithCaption3 sina",
            ],
        ]),

        E::br(),
        E::splitLine(),

        E::AListWithCaption3([
            [
                "href"    => 'https://baidu.com',
                "caption" => "AListWithCaption3 baidu",
            ],
            [
                "href"    => 'https://google.com',
                "caption" => "AListWithCaption3 google",
            ],
            [
                "href"    => 'https://sina.com',
                "caption" => "AListWithCaption3 sina",
            ],
        ], '《__CAPTION__》', true, '|'),

        E::splitLine('-'),
        E::splitLine('='),
        E::splitLine('%'),
        E::splitLine('#'),
        E::splitLine('$'),
        E::splitLine('*'),
        E::splitLine('!'),

    ]);

    $manager->createPageToQueue('test', E::toJson($contents));

