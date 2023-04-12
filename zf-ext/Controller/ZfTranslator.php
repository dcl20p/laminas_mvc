<?php
/**
 * Provides translation services using the MvcTranslator service.
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zf\Ext\Controller;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Psr\Container\ContainerInterface;
use Laminas\I18n\Translator\TranslatorInterface;

class ZfTranslator extends AbstractPlugin
{
    const SERVICE_ALIAS = 'mvcTranslate';

    private TranslatorInterface $translator;

    public function __construct(ContainerInterface $container)
    {
        $this->translator = $container->get(TranslatorInterface::class);
    }

    public function __invoke(string $message = ''): string
    {
        if (empty($message)) {
            return '';
        }

        return $this->translator->translate($message);
    }
}

