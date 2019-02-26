<?php
/**
 * @see https://github.com/dotkernel/dot-user/ for the canonical source repository
 * @copyright Copyright (c) 2017 Apidemia (https://www.apidemia.com)
 * @license https://github.com/dotkernel/dot-user/blob/master/LICENSE.md MIT License
 */

namespace Dot\User;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\EntityListenerResolver;
use Dot\Mapper\Factory\DbMapperFactory;
use Dot\User\Authentication\AuthenticationListener;
use Dot\User\Authentication\InjectLoginForm;
use Dot\User\Controller\UserController;
use Dot\User\Entity\ConfirmTokenEntity;
use Dot\User\Entity\RememberTokenEntity;
use Dot\User\Entity\ResetTokenEntity;
use Dot\User\Entity\RoleEntity;
use Dot\User\Entity\UserEntity;
use Dot\User\Factory\AuthenticationListenerFactory;
use Dot\User\Factory\AutoLoginFactory;
use Dot\User\Factory\BcryptFactory;
use Dot\User\Factory\FormFactory;
use Dot\User\Factory\InjectLoginFormFactory;
use Dot\User\Factory\PasswordCheckFactory;
use Dot\User\Factory\TokenServiceFactory;
use Dot\User\Factory\UserControllerFactory;
use Dot\User\Factory\UserDbMapperFactory;
use Dot\User\Factory\UserFieldsetFactory;
use Dot\User\Factory\UserOptionsFactory;
use Dot\User\Factory\UserServiceFactory;
use Dot\User\Form\AccountForm;
use Dot\User\Form\ChangePasswordForm;
use Dot\User\Form\ForgotPasswordForm;
use Dot\User\Form\LoginForm;
use Dot\User\Form\RegisterForm;
use Dot\User\Form\ResetPasswordForm;
use Dot\User\Form\UserFieldset;
use Dot\User\Mapper\RoleDbMapper;
use Dot\User\Mapper\TokenDbMapper;
use Dot\User\Mapper\UserDbMapper;
use Dot\User\Middleware\AutoLogin;
use Dot\User\Options\UserOptions;
use Dot\User\Service\PasswordCheck;
use Dot\User\Service\TokenService;
use Dot\User\Service\TokenServiceInterface;
use Dot\User\Service\UserService;
use Dot\User\Service\UserServiceInterface;
use Ramsey\Uuid\Doctrine\UuidBinaryOrderedTimeType;
use Ramsey\Uuid\Doctrine\UuidBinaryType;
use Ramsey\Uuid\Doctrine\UuidType;
use Zend\Crypt\Password\PasswordInterface;

/**
 * Class ConfigProvider
 * @package Dot\User
 */
class ConfigProvider
{
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependenciesConfig(),
            'doctrine' => $this->getDoctrineConfig(),
            'templates' => [
                'paths' => [
                    'dot-user-form' => [realpath(__DIR__ . '/../templates/dot-user-form')],
                ],
            ],

            'dot_authentication' => [
                'web' => [
                    'event_listeners' => [
                        [
                            'type' => InjectLoginForm::class,
                            'priority' => 600
                        ],
                        [
                            'type' => AuthenticationListener::class,
                            'priority' => 500
                        ],
                    ],
                ]
            ],

            'dot_user' => [
                'login_options' => [],
                'messages_options' => [
                    'messages' => []
                ],
                'password_recovery_options' => [],
                'register_options' => [],
                'template_options' => [],
            ],
            

            'dot_mapper' => [
                'mapper_manager' => [
                    'factories' => [
                        RoleDbMapper::class => DbMapperFactory::class,
                        UserDbMapper::class => UserDbMapperFactory::class,
                        TokenDbMapper::class => DbMapperFactory::class,
                    ],
                    'aliases' => [
                        RoleEntity::class => RoleDbMapper::class,
                        UserEntity::class => UserDbMapper::class,

                        ConfirmTokenEntity::class => TokenDbMapper::class,
                        RememberTokenEntity::class => TokenDbMapper::class,
                        ResetTokenEntity::class => TokenDbMapper::class,
                    ]
                ],
            ],

            'dot_form' => [
                'form_manager' => [
                    'factories' => [
                        UserFieldset::class => UserFieldsetFactory::class,
                        RegisterForm::class => FormFactory::class,
                        AccountForm::class => FormFactory::class,
                        ChangePasswordForm::class => FormFactory::class,
                        ForgotPasswordForm::class => FormFactory::class,
                        LoginForm::class => FormFactory::class,
                        ResetPasswordForm::class => FormFactory::class,
                    ],
                    'aliases' => [
                        'UserFieldset' => UserFieldset::class,
                        'Register' => RegisterForm::class,
                        'Account' => AccountForm::class,
                        'ChangePassword' => ChangePasswordForm::class,
                        'ForgotPassword' => ForgotPasswordForm::class,
                        'Login' => LoginForm::class,
                        'ResetPassword' => ResetPasswordForm::class,
                    ]
                ]
            ]
        ];
    }

    public function getDoctrineConfig()
    {
        return [
            'connection' => [
                'orm_default' => [
                    'doctrine_mapping_types' => [
                        UuidBinaryType::NAME => 'binary',
                        UuidBinaryOrderedTimeType::NAME => 'binary',
                    ]
                ],
            ],
            'configuration' => [
                'orm_default' => [
                    'entity_listener_resolver' => EntityListenerResolver::class,
                    'numeric_functions' => [
                        'RAND' => Rand::class,
                    ]
                ]
            ],
            'driver' => [
                'orm_default' => [
                    'drivers' => [
                        'Dot\\User\\Entity' => 'UserEntities',
                    ]
                ],
                'ContactEntities' => [
                    'class' => AnnotationDriver::class,
                    'cache' => 'array',
                    'paths' => __DIR__ . '/Contact/Entity',
                ],
                'ProjectEntities' => [
                    'class' => AnnotationDriver::class,
                    'cache' => 'array',
                    'paths' => __DIR__ . '/Project/Entity',
                ],
                'UserEntities' => [
                    'class' => AnnotationDriver::class,
                    'cache' => 'array',
                    'paths' => __DIR__ . '/User/Entity',
                ],
                'CompanyEntities' => [
                    'class' => AnnotationDriver::class,
                    'cache' => 'array',
                    'paths' => __DIR__ . '/Company/Entity',
                ],
                'QuestionEntities' => [
                    'class' => AnnotationDriver::class,
                    'cache' => 'array',
                    'paths' => __DIR__ . '/Question/Entity',
                ]
            ],
            'types' => [
                UuidType::NAME => UuidType::class,
                UuidBinaryType::NAME => UuidBinaryType::class,
                UuidBinaryOrderedTimeType::NAME => UuidBinaryOrderedTimeType::class,
            ]
        ];
    }

    public function getDependenciesConfig()
    {
        return [
            'factories' => [
                PasswordInterface::class => BcryptFactory::class,
                PasswordCheck::class => PasswordCheckFactory::class,

                UserOptions::class => UserOptionsFactory::class,
                UserController::class => UserControllerFactory::class,
                UserService::class => UserServiceFactory::class,
                TokenService::class => TokenServiceFactory::class,

                AutoLogin::class => AutoLoginFactory::class,
                InjectLoginForm::class => InjectLoginFormFactory::class,
                AuthenticationListener::class => AuthenticationListenerFactory::class,
            ],
            'aliases' => [
                UserServiceInterface::class => UserServiceDoctrine::class,
                'UserService' => UserServiceInterface::class,
                TokenServiceInterface::class => TokenService::class,
                'TokenService' => TokenServiceInterface::class,
            ]
        ];
    }
}
