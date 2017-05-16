# silex-firewall

[![StyleCI](https://styleci.io/repos/91457398/shield?branch=master)](https://styleci.io/repos/91457398)

Firewall rules service provider for [**Silex 2.0+**](http://silex.sensiolabs.org/) micro-framework.

> This project is a part of [`silex-tools`](https://github.com/lokhman/silex-tools) library.

## <a name="installation"></a>Installation
You can install `silex-firewall` with [Composer](http://getcomposer.org):

    composer require lokhman/silex-firewall

## <a name="documentation"></a>Documentation
Register `FirewallServiceProvider` with firewall *allow* or/and *deny* settings:

    use Lokhman\Silex\Provider\FirewallServiceProvider;

    $app->register(new FirewallServiceProvider(), [
        'firewall.options' => [
            'allow' => ['127.0.0.1', '::1'],  // as array
            'deny'  => '127.0.0.1, ::1',      // as string
        ],
    ]);

    // override default blocking function
    $app['firewall.blocker'] = $app->protect(function (Request $request) {
        return JsonResponse(['deny' => $request->getClientIp()]);
    });

## <a name="license"></a>License
Library is available under the MIT license. The included LICENSE file describes this in detail.
