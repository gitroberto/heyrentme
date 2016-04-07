<?php
namespace AppBundle\ViewModel\Admin;

class EventNode {

    public $lastDate = null;
    public $name = null;
    public $desc = null;
    public $events = array();

    public function addEvent(LogEvent $event) {
        array_push($this->events, $event);
        if ($this->lastDate === null || $event->date->format('YmdHis') > $this->lastDate->format('YmdHis'))
            $this->lastDate = $event->date;
    }
    
    static function cmp($a, $b) {
        return strcmp($a->lastDate->format('YmdHis'), $b->lastDate->format('YmdHis'));
    }
}
