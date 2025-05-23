# Elgentos_VarnishExtended

This module aims to add some extra features to the Varnish capabilities in Magento.

## Configurable tracking parameters

The [core Magento VCL](https://github.com/magento/magento2/blob/2.4-develop/app/code/Magento/PageCache/etc/varnish6.vcl) contains hard-coded marketing tracking parameters. Almost nobody changes them, but adding tracking parameters often used on your site highly increases hit rate.

This extension adds a field under Stores > Config > System > Full Page Cache > Varnish Configuration > Tracking Parameters in the backend to customize your own parameters.

**IMPORTANT NOTE**: this is not applied automatically! You need to use the optimized VCL:

```
bin/magento varnish:vcl:generate --export-version=6 ---input-file=vendor/elgentos/magento2-varnish-extended/etc/varnish6.vcl -output-file=/data/web/varnish6.vcl
varnishadm vcl.load new-custom-vcl /data/web/varnish6.vcl
varnishadm vcl.use new-custom-vcl
```

## Marketing Parameters
### Info message when removing marketing parameters
Marketing parameters in the URL have a negative effect on the hit rate of the (Varnish) cache. You can list the marketing parameters that Varnish should remove to improve the hit rate.

Don't worry, removing these parameters will not break Magento, because these URL parameters are only processed in your browser, not on the server.

### Warning message when a marketing parameter matches our regular expression

**Warning:** you are trying to remove a marketing parameter from the URL that is also a *filterable product attribute* in Magento. Are you sure you want to remove it? Removing this parameter may prevent Magento from filtering on that attribute.

## Checking the Varnish hit rate

If you use RUMVision and when on Hypernode or Maxcluster, you can view the historical Varnish hit rate in RUMvision.

Otherwise you can do it manually:

```
wget --quiet https://raw.githubusercontent.com/olivierHa/check_varnish/master/check_varnish.py
chmod +x check_varnish.py
./check_varnish.py -f MAIN.cache_hit,MAIN.cache_miss -r
```

A good hit-rate for a B2C store is around 80-90%. For B2B, this would be lower, depending on how closed-off your catalog is.

## Auto-apply custom VCL

You can place it in your Git repo in `app/etc/varnish6.vcl` and automate applying it through [Deployer](https://deployer.org/) on each deploy with the following Deployer task.

```
desc('Auto-apply VCL when custom VCL exists');
task('vcl:auto-apply', function () {
    if (test('[ -f {{release_path}}/app/etc/varnish6.vcl ]')) {
        $timestamp = date('YmdHis');
        run('{{bin/php}} {{release_path}}/bin/magento varnish:vcl:generate --export-version=6 --input-file={{release_path}}/app/etc/varnish6.vcl --output-file=/data/web/varnish6.vcl');
        run('varnishadm vcl.load vcl' . $timestamp . ' /data/web/varnish6.vcl');
        run('varnishadm vcl.use vcl' . $timestamp);
    }
})->select('stage=production');
```

Contrary to popular belief, loading & activating ('using') a new VCL does not purge the cache objects already in Varnish. However, the new VCL might change how future requests are processed, which could result in cached items being evicted sooner or fetched differently.

## Compatibility

Needs at least Magento 2.4.7.
