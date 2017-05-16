<?php
/**
 * Tools for Silex 2+ framework.
 *
 * @author Alexander Lokhman <alex.lokhman@gmail.com>
 *
 * @link https://github.com/lokhman/silex-tools
 *
 * Copyright (c) 2016 Alexander Lokhman <alex.lokhman@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Lokhman\Silex\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Request;

/**
 * Silex service provider for firewall rules.
 *
 * @author Alexander Lokhman <alex.lokhman@gmail.com>
 *
 * @link https://github.com/lokhman/silex-tools
 */
class FirewallServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app['firewall.options'] = [
            'allow' => [],
            'deny'  => [],
        ];

        $app['firewall.blocker'] = $app->protect(function (Request $request) {
            throw new Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException(
                sprintf('Your IP address %s is blocked with firewall rules.', $request->getClientIp()));
        });

        $app['firewall'] = function () use ($app) {
            $options = $app['firewall.options'];

            $parse = function ($ips) {
                if (is_string($ips)) {
                    $ips = explode(',', $ips);
                } elseif (!is_array($ips)) {
                    return [];
                }

                return array_filter(array_map('trim', $ips), function ($address) {
                    return filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
                        || filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
                });
            };

            return [
                $parse(isset($options['allow']) ? $options['allow'] : []),
                $parse(isset($options['deny']) ? $options['deny'] : []),
            ];
        };

        $app->before(function (Request $request) use ($app) {
            list($allow, $deny) = $app['firewall'];

            if (
                ($allow && !IpUtils::checkIp($request->getClientIp(), $allow))
                || ($deny && IpUtils::checkIp($request->getClientIp(), $deny))
            ) {
                return $app['firewall.blocker']($request);
            }
        }, Application::EARLY_EVENT);
    }
}
