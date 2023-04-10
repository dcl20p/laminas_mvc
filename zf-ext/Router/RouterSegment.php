<?php
/**
 * @link      http://github.com/laminas/laminas-router for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zf\Ext\Router;

use Laminas\I18n\Translator\TranslatorInterface as Translator;
use Laminas\Router\Exception;
use Laminas\Stdlib\RequestInterface as Request;
use Laminas\Router\Http\Segment;
use Laminas\Router\Http\RouteMatch;
/**
 * Segment route.
 */
class RouterSegment extends Segment
{
    /**
     * match(): defined by RouteInterface interface.
     *
     * @see    \Laminas\Router\RouteInterface::match()
     * @param  Request     $request
     * @param  string|null $pathOffset
     * @param  array       $options
     * @return RouteMatch|null
     * @throws Exception\RuntimeException
     */
    public function match(Request $request, $pathOffset = null, array $options = [])
    {
        if (!method_exists($request, 'getUri')) {
            return;
        }

        $uri = $request->getUri();
        $path = $uri->getPath();

        if ('/' !== BASE_URL || '/' !== $path) {
            $pattern = '/^(' . preg_quote(BASE_URL, '/') . '\/){1}/';
            $path = rtrim(preg_replace($pattern, '/', $path), '/');

            preg_replace_callback($pattern, function($match) use(&$count) {
                $count++;
            }, $path, -1, $count);
            $path = rtrim(preg_replace($pattern, '/', $path, -1, $count), '/');
            if ($count === 0 && ('' === $path || BASE_URL === $path)) {
                $path = '/';
            }
            $request->setUri($path);
            if (method_exists($request, 'setBaseUrl')) {
                $request->setBaseUrl(BASE_URL);
            }
            if (method_exists($request, 'setRequestUri')) {
                $request->setRequestUri($path);
            }
        }

        if ($pathOffset !== null) {
            if ($pathOffset >= 0 && strlen($path) >= $pathOffset && !empty($this->route)) {
                if (strpos($path, $this->route, $pathOffset) === $pathOffset) {
                    return new RouteMatch($this->defaults, strlen($this->route));
                }
            }

            return null;
        }

        $regex = $this->regex;

        if ($this->translationKeys) {
            if (!isset($options['translator']) || !$options['translator'] instanceof Translator) {
                throw new Exception\RuntimeException('No translator provided');
            }

            $translator = $options['translator'];
            $textDomain = $options['text_domain'] ?? 'default';
            $locale = $options['locale'] ?? null;

            foreach ($this->translationKeys as $key) {
                $regex = str_replace('#' . $key . '#', $translator->translate($key, $textDomain, $locale), $regex);
            }
        }

        if ($pathOffset !== null) {
            $result = preg_match('(\G' . $regex . ')', $path, $matches, null, $pathOffset);
        } else {
            $result = preg_match('(^' . $regex . '$)', $path, $matches);
        }

        if (!$result) {
            return null;
        }

        $matchedLength = strlen($matches[0]);
        $params = [];

        foreach ($this->paramMap as $index => $name) {
            if (isset($matches[$index]) && $matches[$index] !== '') {
                $params[$name] = $this->decode($matches[$index]);
            }
        }

        return new RouteMatch(array_merge($this->defaults, $params), $matchedLength);
    }

}
