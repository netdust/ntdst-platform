<?php

namespace Netdust\View\UI;



class UI
{
    /** A reference to the singleton instance of the UIInterface
     *
     * @var UIInterface|null
     */
    protected static $UI;

    /**
     * Returns the singleton instance of the UIInterface the application
     *
     * @return UIInterface The singleton instance of the UIInterface
     */
    public static function ui()
    {
        if (!isset(static::$UI)) {
            static::setUI(new UIHelper());
        }

        return static::$UI;
    }

    /**
     * Sets the router instance the Application should use as a router
     *
     * @param UIInterface $UI A reference to the Router instance the Application
     *
     * @return void The method does not return any value.
     */
    public static function setUI(UIInterface $UI)
    {
        static::$UI = $UI;
    }


    /**
     * field builder.
     *
     * @param mixed  $ui         The current value of the field.
     * @param array  $params
     */
    public static function make(string $ui, array $params = [] ): SettingsField
    {
        return static::$UI->make($ui, $params);
    }
}