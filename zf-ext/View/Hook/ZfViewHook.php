<?php 
namespace Zf\Ext\View\Hook;

use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerInterface;
use Laminas\View\Helper\AbstractHelper;

class ZfViewHook extends AbstractHelper
{
    protected static $_events;

    /**
     * Set event manager
     *
     * @param EventManagerInterface $events
     * @return void
     */
    public function setEventManager(EventManagerInterface $events)
    {
        $events->setIdentifiers([
            __CLASS__, get_class($this)
        ]);
        self::$_events = $events;
    }

    /**
     * GEt event manager
     */
    public function getEventManager()
    {
        if (!self::$_events) {
            $this->setEventManager(new EventManager());
        }
        return self::$_events;
    }

    public function __invoke() {
        return $this;
    }

    /**
     * @param string $evtName
     * @param callable $evt
     * @return void
     */
    public function attachEvt(string $evtName, callable $evt)
    {
        $this->getEventManager()->attach($evtName, $evt);
    }

    /**
     * @param string $evtName
     * @param array|object $params
     * @return void
     */
    public function triggerEvt(string $evtName, array|object $params)
    {
        $this->getEventManager()->trigger($evtName, null, $params);
    }
}
?>