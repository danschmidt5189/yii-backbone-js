return array(
  'class' => 'path.to.Backbone',
  'appPath' => 'path.to.backboneJsAppDirectory',
  'require' => array(
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
  ),
);
