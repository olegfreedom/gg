<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
            'application' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '[:controller[/:action[/:get_http_request_string]]][/]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'get_http_request_string' => '[a-zA-Z0-9][a-zA-Z0-9_\/\-\\\.]*',
                            ),
                            'defaults' => array(
                                'action' => 'index',
                                '__NAMESPACE__' => 'Application\Controller'
                            )
                        )
                    )
                )
            ),
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'application/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'application/layout/header' => __DIR__ . '/../view/layout/header.phtml',
            'application/layout/footer' => __DIR__ . '/../view/layout/footer.phtml',
            'application/layout/page-body' => __DIR__ . '/../view/layout/page-body.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
