<?php
/**
 * Backbone.php class file.
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

/**
 * // ========================================================= //
 * // DO NOT PUT SENSITIVE DATA IN YOUR BACKBONE APP DIRECTORY! //
 * // ========================================================= //
 */

/**
 * Initialize Backbone.js / Marionette applications in Yii
 *
 * Running your Backbone app:
 * <code>
 * <?php
 * Yii::app()->backbone->app = 'MyApp';
 * Yii::app()->backbone->options = $initOptions;
 * Yii::app()->backbone->start();
 * ?>
 * </code>
 */
class Backbone extends CApplicationComponent
{
  /**
   * Backbone app filename
   *
   * This value is set as the 'data-main' attribute of the requireJs script
   * and bootstraps your application. The '/' prefix and '.js' suffix will
   * be added automatically, so the following are equivalent:
   * - MyApp.js
   * - /MyApp.js
   * - MyApp
   * - /MyApp
   *
   * Your app.js file should look something like this:
   * <code>
   * require([
   *   "jquery",
   *   "underscore",
   *   "Backbone",
   *   "Marionette",
   *   "options",
   * ], function ($, _, Backbone, Marionette, options) {
   *   var MyApp = new Backbone.Marionette.Application();
   *   MyApp.addInitializer();
   *   MyApp.start(options);
   * });
   * </code>
   * @var string
   */
  public $app;

  /**
   * Marionette app options
   *
   * This will be JSON-encoded using CJSON::encode() and defined as a require module
   * named Backbone::$optionsModuleName. This means you can require the options in
   * your application file's require() statement.
   *
   * @var array
   */
  public $options = array();

  /**
   * Alias to the directory containing your Backbone application
   *
   * This entire directory is published in Backbone::init().
   *
   * @var string
   */
  public $appPath = 'application.scripts.backbone';

  /**
   * RequireJS configuration
   *
   * If set, this value will be JSON-encoded and passed to require.config().
   *
   * @var array
   */
  public $require = array(
    'paths' => array(
      'jquery' => 'http://cdnjs.cloudflare.com/ajax/libs/jquery/2.0.3/jquery',
      'underscore' => 'http://cdnjs.cloudflare.com/ajax/libs/lodash.js/2.4.1/lodash.min',
      'backbone' => 'http://cdnjs.cloudflare.com/ajax/libs/backbone.js/1.1.0/backbone-min',
      'marionette' => '//cdnjs.cloudflare.com/ajax/libs/backbone.marionette/1.4.1-bundled/backbone.marionette.min',
    ),
    'shim' => array(
      'backbone' => array(
        'deps' => array('jquery', 'underscore'),
        'exports' => 'Backbone',
      ),
      'marionette'  => array(
        'deps'  => array('jquery', 'underscore', 'backbone'),
        'exports' => 'Marionette',
      ),
    ),
  );

  /**
   * require.js url
   *
   * If not set, this assumes your file lives at "{Backbone::$appPath}/require.js".
   * As with all files in the Backbone::$appPath directory, this file is published in Backbone::init().
   *
   * @var string
   */
  public $requireJsUrl = '//cdnjs.cloudflare.com/ajax/libs/require.js/2.1.9/require.min.js';

  /**
   * App options module name
   *
   * @var string
   */
  public $optionsModuleName = 'options';

  /**
   * scriptPrefix prefix for the require configuration and app options
   * Options and require configuration are registered with CClientScript using the names:
   * - "{$scriptPrefix}:options"
   * - "{$scriptPrefix}:require"
   * @var string
   */
  public $scriptPrefix = 'Backbone:App';

  /**
   * Registers the Backbone app scripts
   *
   * @see Backbone::$require, the requirejs configuration
   * @see Backbone::$options, app options
   * @see Backbone::$optionsModuleName, name of the require module containing options
   */
  public function start()
  {
    if (!$this->app) {
      throw new BackboneException('Backbone::$app should be a filename in '.$this->appPath);
    }

    // Configure require.js
    if ($this->require) {
      $require = CJSON::encode($this->require);
      Yii::app()->clientScript->registerScript(
        "{$this->scriptPrefix}:require",
        "require.config($require);",
        CClientScript::POS_HEAD
      );
    }

    // Register app options in a module
    $options = CJSON::encode($this->options);
    $optionsModuleName = CJSON::encode($this->optionsModuleName);
    Yii::app()->clientScript->registerScript(
      "{$this->scriptPrefix}:options",
      "define($optionsModuleName,[],function(){return $options;});",
      CClientScript::POS_HEAD
    );

    // Register the app using require and data-main
    $this->registerApp($this->app);
  }

  /**
   * Published assets in the app directory
   */
  public function init()
  {
    $this->publishAppDirectory();
  }

  /**
   * Publishes all assets in the Backbone app directory
   */
  public function publishAppDirectory()
  {
    $path = Yii::getPathOfAlias($this->appPath);
    Yii::app()->assetManager->publish($path);
  }

  /**
   * Returns the url
   * @return string url of the published assets directory
   */
  public function getPublishedUrl()
  {
    $path = Yii::getPathOfAlias($this->appPath);
    return Yii::app()->assetManager->getPublishedUrl($path);
  }

  /**
   * Uses requirejs to register the app
   * @param string $app name of the app file. This is loaded async by require js to startup the app.
   */
  public function registerApp($main)
  {
    // Normalize the 'main.js' filename
    if (substr($main, 0, -3) != '.js')  { $main .= '.js'; }
    if (substr($main, 0, 1) != '/')     { $main = '/' . $main; }

    // Where to look for RequireJs?
    if ($this->requireJsUrl) {
      $requireJsUrl = $this->requireJsUrl;
    } else {
      $requireJsUrl = $this->publishedUrl . '/require.js';
    }

    // Require.js is responsible for loading dependencies and your
    // initialization script.
    Yii::app()->clientScript->registerScriptFile(
      $requireJsUrl,
      CClientScript::POS_HEAD,
      array('data-main' => $this->publishedUrl . $main)
    );
  }
}

class BackboneException extends CException {}
