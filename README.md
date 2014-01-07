yii-marionette
==============

Quickly integrate Backbone, Marionette, and RequireJS in your Yii application.

# Run your backbone app

1. Configure the Backbone component
2. Tell backbone which app to load
3. Pass initialization options to backbone
4. Call start()
5. You're done. All assets have been published and the necessary files included.

```php
Yii::app()->backbone->app = 'MyApp';
Yii::app()->backbone->options = array('key' => 'value');
Yii::app()->backbone->start();
```

# Requirements

* RequireJS is required for module loading.
* By default, this assumes there's a require.js file in your backbone app's root directory.
* Customize the path to require js using `Backbone::$requireJsUrl`
