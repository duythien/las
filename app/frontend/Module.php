<?php

namespace Las\Frontend;

/**
 * Frontend Module
 *
 * @package     las
 * @category    Module
 * @version     1.0
 */
class Module implements \Phalcon\Mvc\ModuleDefinitionInterface
{

    /**
     * Register a specific autoloader for the module
     *
     * @package     las
     * @version     1.0
     *
     * @param mixed $di dependency Injector
     *
     * @return void
     */
    public function registerAutoloaders($di = null)
    {
        $loader = new \Phalcon\Loader();

        $loader->registerNamespaces(array(
            'Las\Frontend\Controllers' => __DIR__ . '/controllers/',
        ));

        $loader->register();
    }

    /**
     * Register specific services for the module
     *
     * @package     las
     * @version     1.0
     *
     * @param object $di dependency Injector
     *
     * @return void
     */
    public function registerServices($di)
    {
        //Registering a dispatcher
        $di->set('dispatcher', function() {
            //Create/Get an EventManager
            $eventsManager = new \Phalcon\Events\Manager();
            //Attach a listener
            $eventsManager->attach("dispatch", function($event, $dispatcher, $exception) {
                //controller or action doesn't exist
                if ($event->getType() == 'beforeException') {
                    switch ($exception->getCode()) {
                        case \Phalcon\Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                        case \Phalcon\Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                            $dispatcher->forward(array(
                                'controller' => 'index',
                                'action' => 'notFound'
                            ));
                            return false;
                    }
                }
            });

            $dispatcher = new \Phalcon\Mvc\Dispatcher();
            //Set default namespace to frontend module
            $dispatcher->setDefaultNamespace("Las\Frontend\Controllers");
            //Bind the EventsManager to the dispatcher
            $dispatcher->setEventsManager($eventsManager);

            return $dispatcher;
        });

        //Registering the view component
        $di->set('view', function() use($di) {
            $view = new \Phalcon\Mvc\View();
            $view->setViewsDir(__DIR__ . '/views/');
            $view->registerEngines(\Las\Library\Tool::registerEngines($view, $di));
            return $view;
        });
    }

}
