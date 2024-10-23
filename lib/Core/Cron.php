<?php

namespace Netdust\Core;


Class Cron{

    protected array $every = [];
    protected array $slug = [];

    public function __construct( array $interval, string $slug = '' ) {
        $this->every = $interval;
    }

    public function slug(){
        $reflect = new \ReflectionClass($this);
        $class = $reflect->getShortName();
        return 'wp_cron__'. strtolower($class);
    }

    public function schedule(){
        return 'schedule_'. $this->slug();
    }

    public function calculateInterval(){

        if(!(count(array_filter(array_keys($this->every), 'is_string')) > 0)){
            throw new \Exception("WP_Cron::\$interval must be an assoc array");
        }

        $interval = 0;
        $multipliers = array(
            'seconds' 	=> 1,
            'minutes' 	=> 60,
            'hours' 	=> 3600,
            'days' 		=> 86400,
            'weeks' 	=> 604800,
            'months' 	=> 2628000,
        );

        foreach($multipliers as $unit => $multiplier){
            if(isset($this->every[$unit]) && is_int($this->every[$unit])){
                $interval = $interval + ($this->every[$unit] * $multiplier);
            }
        }

        return $interval;
    }

    public function scheduleFilter($schedules){
        $interval = $this->calculateInterval();

        if(!in_array($this->schedule(), array_keys($schedules))){
            $schedules[$this->schedule()] = array(
                'interval' => $interval,
                'display'  => 'Every '. floor($interval / 60) .' minutes',
            );
        }

        return $schedules;
    }

    public function register(){

        $slug  = $this->slug();

        add_filter('cron_schedules', array($this, 'scheduleFilter'));

        if(!wp_next_scheduled($slug)){
            wp_schedule_event(time(), $this->schedule(), $slug);
        }

        if(method_exists($this, 'handle')){
            add_action($slug, array($this, 'handle'));
        }
    }
}