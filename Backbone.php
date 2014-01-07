<?php
/**
 * Backbone.php class file.
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 */

/**
 * Initialize Backbone.js / Marionette applications in Yii
 *
 * This implementation is designed to work with Backbone applications using Marionette.
 *
 * Set this as an application component and use as follows:
 * <code>
 * // In your component configuration
 * 'backbone' => array(
 *   'class' => 'path.to.Backbone',
 *   'appPath' => 'path.to.backbone.app.root.dir',
 *   'require' => array(
 *     // requireJs configuration
 *   ),
 * ),
 * </code>
 * <code>
 * // In your app.js file:
 * define([
 *   'jquery',
 *   'lodash',
 *   'Backbone',
 *   'Marionette',
 *   'options',
 * ], function ($, _, Backbone, Marionette, options) {
 *   var App = Backbone.Marionette.Application();
 *   App.addInitializer(options);
 *   App.start(options);
 * });
 * </code>
 * <code>
 * // In your controller
 * Yii::app()->backbone->app = 'MyApp';
 * Yii::app()->backbone->options = array( app data );
 * Yii::app()->backbone->start();
 * </code>
 */
class Backbone extends CApplicationComponent
{
  /**
   * Name of the requirejs file used to load the application
   * @var string
   */
  public $app;

  /**
   * JavaScript options registered as a global object on the page
   * This will be JSON-encoded using CJSON::encode().
   * @var array
   */
  public $options = array();

  /**
   * RequireJS configuration
   * @var array
   */
  public $require = array();

  /**
   * Name of the require module containing app options
   * This is published dynamically at runtime and added to the page
   * using data sourced from Backbone::$options.
   * @var string
   */
  public $optionsModuleName = 'options';

  /**
   * Alias to the directory containing your Backbone application
   * This is published in Backbone::init().
   * @var string
   */
  public $appPath = 'application.scripts.backbone';

  /**
   * Namespace prefix for the require configuration and app options
   * Options and require configuration are registered with CClientScript using the names:
   * - "{$namespace}:options"
   * - "{$namespace}:require"
   * @var string
   */
  public $namespace = 'Backbone:App';

  /**
   * Registers the Backbone app scripts
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
    if ($require) {
      $require = CJSON::encode($this->require);
      Yii::app()->clientScript->registerScript(
        $this->namespace.':require',
        "require.config($require);",
        CClientScript::POS_HEAD
      );
    }

    // Register app options in a module
    $options = CJSON::encode($this->options);
    $optionsModuleName = CJSON::encode($this->optionsModuleName);
    Yii::app()->clientScript->registerScript(
      $this->namespace.':options',
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

    // Require.js is responsible for loading dependencies and your
    // initialization script.
    Yii::app()->clientScript->registerScriptFile(
      $this->publishedUrl . '/require.js',
      CClientScript::POS_HEAD,
      array('data-main' => $this->publishedUrl . $main)
    );
  }
}

class BackboneException extends CException {}
