<?php
use Your\Module\HelloCommand;

return [
    'console' => [
        'value' => [
            'commands' => [
                HelloCommand::class, // simple (via class name)
                'hello' => ['className' => HelloCommand::class], // classic
                ['constructor' => function () { return new HelloCommand(); }] // via constructor
            ],
        ],
    ],
];
