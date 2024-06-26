<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace PrestaShopBundle\Controller\Admin;

use Context;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use LogicException;
use PrestaShop\PrestaShop\Core\Domain\Configuration\ShopConfigurationInterface;
use PrestaShop\PrestaShop\Core\Grid\GridInterface;
use PrestaShop\PrestaShop\Core\Help\Documentation;
use PrestaShop\PrestaShop\Core\Localization\Locale\Repository as LocaleRepository;
use PrestaShop\PrestaShop\Core\Localization\LocaleInterface;
use PrestaShop\PrestaShop\Core\Module\Exception\ModuleErrorInterface;
use PrestaShop\PrestaShop\Core\Security\Permission;
use PrestaShopBundle\Translation\TranslatorInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Service\ServiceProviderInterface;

/**
 * Extends The Symfony framework bundle controller to add common functions for PrestaShop needs.
 *
 * @deprecated since 9.0 to be removed in future versions (10+ at least, when it will not be used anymore),
 * should stop using it in favor of PrestaShopAdminController.
 */
class FrameworkBundleAdminController extends AbstractController
{
    /**
     * @deprecated since 9.0
     */
    public const PRESTASHOP_CORE_CONTROLLERS_TAG = 'prestashop.core.controllers';

    protected ?ServiceProviderInterface $controllerContainer = null;

    protected ?Container $globalContainer = null;

    /**
     * This method is completely hacky, we count on the fact that it is going to be used to inject the controller's dedicated
     * minified controller (thanks to the @required annotation, autowiring and AbstractController parent class), this allows us
     * to store the controller container in a dedicated field.
     *
     * On a second call, made by Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver, this setter is called with the
     * global container (mainly to cehck the current value actually), so we use the occasion to store the global container.
     *
     * The real container should be the controller one, but it doesn't contain all the public services we need that are in
     * the global container, so we keep a reference on both containers so that the get and has methods can try fallback on
     * both of them.
     *
     * This is quite ugly, but it prevents refactoring all the controllers (from both core and modules controllers), it is only
     * done on controllers that extend this class which should not be used anymore and be replaced by PrestaShopAdminController
     * controller by controller along with a refacto to do proper dependency injection.
     *
     * @param ContainerInterface $container
     *
     * @return ContainerInterface|null
     *
     * Note: this annotation is a MUST-HAVE, we have to keep it
     *
     * @required
     */
    public function setContainer(ContainerInterface $container): ?ContainerInterface
    {
        $return = parent::setContainer($container);
        if ($container instanceof ServiceProviderInterface) {
            $this->controllerContainer = $container;
        }
        if ($container instanceof Container) {
            $this->globalContainer = $container;
        }

        return $return;
    }

    /**
     * This method was removed in Symfony 6, for backward compatibility reasons this method is temporarily
     * maintained so the modules can keep using it a little longer. It will be removed in the next major though
     * along with this base controller class
     *
     * @deprecated since 9.0
     */
    protected function has(string $id): bool
    {
        trigger_deprecation('prestashop/prestashop', '9.0', 'Method "%s()" is deprecated, use method or constructor injection in your controller instead.', __METHOD__);

        if ($this->controllerContainer && $this->controllerContainer->has($id)) {
            return true;
        }
        if ($this->globalContainer && $this->globalContainer->has($id)) {
            return true;
        }

        return $this->container->has($id);
    }

    /**
     * This method was removed in Symfony 6, for backward compatibility reasons this method is temporarily
     * maintained so the modules can keep using it a little longer. It will be removed in the next major though
     * along with this base controller class
     *
     * @deprecated since 9.0
     */
    protected function get(string $id): object
    {
        trigger_deprecation('prestashop/prestashop', '9.0', 'Method "%s()" is deprecated, use method or constructor injection in your controller instead.', __METHOD__);

        return $this->doGet($id);
    }

    /**
     * This special get method tries to get a service either in the controller custom-made container (that contains
     * the most regular aliases to private services like twig, security, ...) and if it doesn't find it it tries to
     * get it from the global container (that contains all public services).
     *
     * This is completely going around the framework, we do this to allow this class to behave as it used to without
     * having to refactor the controller completely with proper dependncy injection, but it's a temporary solution that
     * will disappear with this fix.
     *
     * @param string $id
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function doGet(string $id): object
    {
        if ($this->controllerContainer && $this->controllerContainer->has($id)) {
            return $this->controllerContainer->get($id);
        }

        if ($this->globalContainer && $this->globalContainer->has($id)) {
            return $this->globalContainer->get($id);
        }

        return $this->container->get($id);
    }

    /**
     * This method was removed in Symfony 6, for backward compatibility reasons this method is temporarily
     * maintained so the modules can keep using it a little longer. It will be removed in the next major though
     * along with this base controller class
     *
     * @deprecated since 9.0
     */
    protected function getDoctrine(): ManagerRegistry
    {
        return $this->get('doctrine');
    }

    /**
     * @var string|null
     */
    protected $layoutTitle;

    /**
     * @return ShopConfigurationInterface
     */
    protected function getConfiguration(): ShopConfigurationInterface
    {
        return $this->get('prestashop.adapter.legacy.configuration');
    }

    /**
     * Returns form errors for JS implementation.
     *
     * Parse all errors mapped by id html field
     *
     * @param FormInterface $form
     *
     * @return array<array<string>> Errors
     *
     * @throws \Symfony\Component\Translation\Exception\InvalidArgumentException
     */
    public function getFormErrorsForJS(FormInterface $form)
    {
        $errors = [];

        if ($form->count() === 0) {
            return $errors;
        }

        foreach ($form->getErrors(true) as $error) {
            if ($error->getCause() && method_exists($error->getCause(), 'getPropertyPath')) {
                $formId = str_replace(
                    ['.', 'children[', ']', '_data'],
                    ['_', '', '', ''],
                    $error->getCause()->getPropertyPath()
                );
            } else {
                $formId = 'bubbling_errors';
            }

            if ($error->getMessagePluralization()) {
                $errors[$formId][] = $this->getTranslator()->trans(
                    $error->getMessageTemplate(),
                    array_merge(
                        $error->getMessageParameters(),
                        ['%count%' => $error->getMessagePluralization()]
                    ),
                    'validators'
                );
            } else {
                $errors[$formId][] = $this->getTranslator()->trans(
                    $error->getMessageTemplate(),
                    $error->getMessageParameters(),
                    'validators'
                );
            }
        }

        return $errors;
    }

    /**
     * Creates a HookEvent, sets its parameters, and dispatches it.
     *
     * Wrapper to: @see HookDispatcher::dispatchWithParameters()
     *
     * @param string $hookName The hook name
     * @param array $parameters The hook parameters
     */
    protected function dispatchHook($hookName, array $parameters)
    {
        $this->get('prestashop.core.hook.dispatcher')->dispatchWithParameters($hookName, $parameters);
    }

    /**
     * Creates a RenderingHookEvent, sets its parameters, and dispatches it. Returns the event with the response(s).
     *
     * Wrapper to: @see HookDispatcher::renderForParameters()
     *
     * @param string $hookName The hook name
     * @param array $parameters The hook parameters
     *
     * @return array The responses of hooks
     *
     * @throws Exception
     */
    protected function renderHook($hookName, array $parameters)
    {
        return $this->get('prestashop.core.hook.dispatcher')->renderForParameters($hookName, $parameters)->getContent();
    }

    /**
     * Generates a documentation link.
     *
     * @param string $section Legacy controller name
     * @param bool|string $title Help title
     *
     * @return string
     */
    protected function generateSidebarLink($section, $title = false)
    {
        $legacyContext = $this->get('prestashop.adapter.legacy.context');

        if (empty($title)) {
            $title = $this->trans('Help', 'Admin.Global');
        }

        $iso = (string) $legacyContext->getEmployeeLanguageIso();

        $url = $this->generateUrl('admin_common_sidebar', [
            'url' => $this->get(Documentation::class)->generateLink($section, $iso),
            'title' => $title,
        ]);

        // this line is allow to revert a new behaviour introduce in sf 5.4 which break the result we used to have
        return strtr($url, ['%2F' => '%252F']);
    }

    /**
     * Get the old but still useful context.
     *
     * @return Context
     */
    protected function getContext()
    {
        return $this->get('prestashop.adapter.legacy.context')->getContext();
    }

    /**
     * @return string
     *
     * //@todo: is there a better way using currency iso_code?
     */
    protected function getContextCurrencyIso(): string
    {
        return $this->getContext()->currency->iso_code;
    }

    /**
     * Get the locale based on the context
     *
     * @return LocaleInterface
     */
    protected function getContextLocale(): LocaleInterface
    {
        $locale = $this->getContext()->getCurrentLocale();
        if (null !== $locale) {
            return $locale;
        }

        /** @var LocaleRepository $localeRepository */
        $localeRepository = $this->get('prestashop.core.localization.locale.repository');
        $locale = $localeRepository->getLocale(
            $this->getContext()->language->getLocale()
        );

        return $locale;
    }

    /**
     * @param string $lang
     *
     * @return mixed
     */
    protected function langToLocale($lang)
    {
        return $this->get('prestashop.service.translation')->langToLocale($lang);
    }

    /**
     * @return bool
     */
    protected function isDemoModeEnabled()
    {
        return $this->getConfiguration()->get('_PS_MODE_DEMO_');
    }

    /**
     * @return string
     */
    protected function getDemoModeErrorMessage()
    {
        return $this->trans('This functionality has been disabled.', 'Admin.Notifications.Error');
    }

    /**
     * Checks if the attributes are granted against the current authentication token and optionally supplied object.
     *
     * @param string $controller name of the controller that token is tested against
     *
     * @return int
     *
     * @throws LogicException
     */
    protected function authorizationLevel($controller)
    {
        if ($this->isGranted(Permission::DELETE, $controller)) {
            return Permission::LEVEL_DELETE;
        }

        if ($this->isGranted(Permission::CREATE, $controller)) {
            return Permission::LEVEL_CREATE;
        }

        if ($this->isGranted(Permission::UPDATE, $controller)) {
            return Permission::LEVEL_UPDATE;
        }

        if ($this->isGranted(Permission::READ, $controller)) {
            return Permission::LEVEL_READ;
        }

        return 0;
    }

    /**
     * Get the translated chain from key.
     *
     * @param string $key the key to be translated
     * @param string $domain the domain to be selected
     * @param array $parameters Optional, pass parameters if needed (uncommon)
     *
     * @return string
     */
    protected function trans($key, $domain, array $parameters = [])
    {
        return $this->getTranslator()->trans($key, $parameters, $domain);
    }

    /**
     * Return errors as flash error messages.
     *
     * @param array $errorMessages
     *
     * @throws LogicException
     */
    protected function flashErrors(array $errorMessages)
    {
        foreach ($errorMessages as $error) {
            $message = is_array($error) ? $this->trans($error['key'], $error['domain'], $error['parameters']) : $error;
            $this->addFlash('error', $message);
        }
    }

    /**
     * Redirect employee to default page.
     *
     * @return RedirectResponse
     */
    protected function redirectToDefaultPage()
    {
        $legacyContext = $this->get('prestashop.adapter.legacy.context');
        $defaultTab = $legacyContext->getDefaultEmployeeTab();

        return $this->redirect($legacyContext->getAdminLink($defaultTab));
    }

    /**
     * Check if the connected user is granted to actions on a specific object.
     *
     * @param string $action
     * @param string $object
     * @param string $suffix
     *
     * @return bool
     *
     * @throws LogicException
     */
    protected function actionIsAllowed($action, $object = '', $suffix = '')
    {
        return (
            $action === 'delete' . $suffix && $this->isGranted(Permission::DELETE, $object)
        ) || (
            ($action === 'activate' . $suffix || $action === 'deactivate' . $suffix)
            && $this->isGranted(Permission::UPDATE, $object)
        ) || (
            ($action === 'duplicate' . $suffix)
            && ($this->isGranted(Permission::UPDATE, $object) || $this->isGranted(Permission::CREATE, $object))
        );
    }

    /**
     * Display a message about permissions failure according to an action.
     *
     * @param string $action
     * @param string $suffix
     *
     * @return string
     *
     * @throws Exception
     */
    protected function getForbiddenActionMessage($action, $suffix = '')
    {
        if ($action === 'delete' . $suffix) {
            return $this->trans('You do not have permission to delete this.', 'Admin.Notifications.Error');
        }

        if ($action === 'deactivate' . $suffix || $action === 'activate' . $suffix) {
            return $this->trans('You do not have permission to edit this.', 'Admin.Notifications.Error');
        }

        if ($action === 'duplicate' . $suffix) {
            return $this->trans('You do not have permission to add this.', 'Admin.Notifications.Error');
        }

        throw new Exception(sprintf('Invalid action (%s)', $action . $suffix));
    }

    /**
     * Get fallback error message when something unexpected happens.
     *
     * @param string $type
     * @param int $code
     * @param string $message
     *
     * @return string
     */
    protected function getFallbackErrorMessage($type, $code, $message = '')
    {
        $isDebug = $this->get('kernel')->isDebug();
        if ($isDebug && !empty($message)) {
            return $this->trans(
                'An unexpected error occurred. [%type% code %code%]: %message%',
                'Admin.Notifications.Error',
                [
                    '%type%' => $type,
                    '%code%' => $code,
                    '%message%' => $message,
                ]
            );
        }

        return $this->trans(
            'An unexpected error occurred. [%type% code %code%]',
            'Admin.Notifications.Error',
            [
                '%type%' => $type,
                '%code%' => $code,
            ]
        );
    }

    /**
     * Get Admin URI from PrestaShop 1.6 Back Office.
     *
     * @param string $controller the old Controller name
     * @param bool $withToken whether we add token or not
     * @param array $params url parameters
     *
     * @return string the page URI (with token)
     */
    protected function getAdminLink($controller, array $params, $withToken = true)
    {
        return $this->get('prestashop.adapter.legacy.context')->getAdminLink($controller, $withToken, $params);
    }

    /**
     * Present provided grid.
     *
     * @param GridInterface $grid
     *
     * @return array
     */
    protected function presentGrid(GridInterface $grid)
    {
        return $this->get('prestashop.core.grid.presenter.grid_presenter')->present($grid);
    }

    /**
     * Get commands bus to execute commands.
     *
     * @return \PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface
     */
    protected function getCommandBus()
    {
        return $this->get('prestashop.core.command_bus');
    }

    /**
     * Get query bus to execute queries.
     *
     * @return \PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface
     */
    protected function getQueryBus()
    {
        return $this->get('prestashop.core.query_bus');
    }

    /**
     * @param array $errors
     * @param int $httpStatusCode
     *
     * @return JsonResponse
     */
    protected function returnErrorJsonResponse(array $errors, $httpStatusCode)
    {
        $response = new JsonResponse();
        $response->setStatusCode($httpStatusCode);
        $response->setData($errors);

        return $response;
    }

    /**
     * @return int
     */
    protected function getContextLangId()
    {
        return $this->getContext()->language->id;
    }

    /**
     * @return int
     */
    protected function getContextShopId()
    {
        return $this->getContext()->shop->id;
    }

    /**
     * @param FormInterface $form
     */
    protected function addFlashFormErrors(FormInterface $form)
    {
        /** @var FormError $formError */
        foreach ($form->getErrors(true) as $formError) {
            $this->addFlash('error', $formError->getMessage());
        }
    }

    /**
     * Get error by exception from given messages
     *
     * @param Exception $e
     * @param array $messages
     *
     * @return string
     */
    protected function getErrorMessageForException(Exception $e, array $messages)
    {
        if ($e instanceof ModuleErrorInterface) {
            return $e->getMessage();
        }

        $exceptionType = $e::class;
        $exceptionCode = $e->getCode();

        if (isset($messages[$exceptionType])) {
            $message = $messages[$exceptionType];

            if (is_string($message)) {
                return $message;
            }

            if (is_array($message) && isset($message[$exceptionCode])) {
                return $message[$exceptionCode];
            }
        }

        return $this->getFallbackErrorMessage(
            $exceptionType,
            $exceptionCode,
            $e->getMessage()
        );
    }

    protected function getTranslator(): TranslatorInterface
    {
        return $this->get(TranslatorInterface::class);
    }
}
