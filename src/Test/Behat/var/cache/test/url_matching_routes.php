<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/orm/contacts' => [[['_route' => 'zenstruck_foundry_tests_fixture_app_createcontact__invoke', '_controller' => 'Zenstruck\\Foundry\\Tests\\Fixture\\App\\Controller\\CreateContact'], null, ['POST' => 0], null, false, false, null]],
        '/hello-world' => [[['_route' => 'zenstruck_foundry_tests_fixture_app_createcontact_index', '_controller' => 'Zenstruck\\Foundry\\Tests\\Fixture\\App\\Controller\\CreateContact::index'], null, null, null, false, false, null]],
        '/' => [[['_route' => 'zenstruck_foundry_tests_fixture_app_helloworld_index', '_controller' => 'Zenstruck\\Foundry\\Tests\\Fixture\\App\\Controller\\HelloWorldController::index'], null, null, null, false, false, null]],
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/orm/(?'
                    .'|create/([^/]++)(*:30)'
                    .'|d(?'
                        .'|elete/([^/]++)(*:55)'
                        .'|b/delete/([^/]++)(*:79)'
                    .')'
                    .'|update/([^/]++)(?:/([^/]++))?(*:116)'
                .')'
                .'|/mongo/(?'
                    .'|create/([^/]++)(*:150)'
                    .'|d(?'
                        .'|elete/([^/]++)(*:176)'
                        .'|b/delete/([^/]++)(*:201)'
                    .')'
                    .'|update/([^/]++)(?:/([^/]++))?(*:239)'
                .')'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        30 => [[['_route' => 'zenstruck_foundry_tests_fixture_app_creategenericmodel_ormcreate', '_controller' => 'Zenstruck\\Foundry\\Tests\\Fixture\\App\\Controller\\CreateGenericModel::ormCreate'], ['value'], null, null, false, true, null]],
        55 => [[['_route' => 'zenstruck_foundry_tests_fixture_app_deletegenericmodel_ormdelete', '_controller' => 'Zenstruck\\Foundry\\Tests\\Fixture\\App\\Controller\\DeleteGenericModel::ormDelete'], ['id'], null, null, false, true, null]],
        79 => [[['_route' => 'zenstruck_foundry_tests_fixture_app_deletegenericmodel_ormdbdelete', '_controller' => 'Zenstruck\\Foundry\\Tests\\Fixture\\App\\Controller\\DeleteGenericModel::ormDbDelete'], ['id'], null, null, false, true, null]],
        116 => [[['_route' => 'zenstruck_foundry_tests_fixture_app_updategenericmodel_ormupdate', 'newValue' => 'foo', '_controller' => 'Zenstruck\\Foundry\\Tests\\Fixture\\App\\Controller\\UpdateGenericModel::ormUpdate'], ['id', 'newValue'], null, null, false, true, null]],
        150 => [[['_route' => 'zenstruck_foundry_tests_fixture_app_creategenericmodel_mongocreate', '_controller' => 'Zenstruck\\Foundry\\Tests\\Fixture\\App\\Controller\\CreateGenericModel::mongoCreate'], ['value'], null, null, false, true, null]],
        176 => [[['_route' => 'zenstruck_foundry_tests_fixture_app_deletegenericmodel_mongodelete', '_controller' => 'Zenstruck\\Foundry\\Tests\\Fixture\\App\\Controller\\DeleteGenericModel::mongoDelete'], ['id'], null, null, false, true, null]],
        201 => [[['_route' => 'zenstruck_foundry_tests_fixture_app_deletegenericmodel_mongodbdelete', '_controller' => 'Zenstruck\\Foundry\\Tests\\Fixture\\App\\Controller\\DeleteGenericModel::mongoDbDelete'], ['id'], null, null, false, true, null]],
        239 => [
            [['_route' => 'zenstruck_foundry_tests_fixture_app_updategenericmodel_mongoupdate', 'newValue' => 'foo', '_controller' => 'Zenstruck\\Foundry\\Tests\\Fixture\\App\\Controller\\UpdateGenericModel::mongoUpdate'], ['id', 'newValue'], null, null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
