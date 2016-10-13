<?php
/**
 * @copyright: DotKernel
 * @library: dotkernel/dot-user
 * @author: n3vrax
 * Date: 7/16/2016
 * Time: 1:04 AM
 */

namespace Dot\User\Factory\Form;

use Dot\User\EventManagerAwareFactoryTrait;
use Dot\User\Form\ForgotPasswordForm;
use Dot\User\Form\InputFilter\ForgotPasswordInputFilter;
use Dot\User\Options\UserOptions;
use Interop\Container\ContainerInterface;

/**
 * Class ForgotPasswordFormFactory
 * @package Dot\User\Factory\Form
 */
class ForgotPasswordFormFactory
{
    use EventManagerAwareFactoryTrait;

    /**
     * @param ContainerInterface $container
     * @return ForgotPasswordForm
     */
    public function __invoke(ContainerInterface $container)
    {
        $filter = new ForgotPasswordInputFilter($container->get(UserOptions::class));
        $filter->setEventManager($this->getEventManager($container));
        $filter->init();

        $form = new ForgotPasswordForm();
        $form->setInputFilter($filter);
        $form->setEventManager($this->getEventManager($container));
        $form->init();

        return $form;
    }
}