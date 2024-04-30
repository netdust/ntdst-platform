<?php

namespace Netdust\View\UI;


class UIHelper implements UIInterface {



    /**
     * field builder.
     *
     * @param string $type           The type of the field.
     * @param mixed  $value         The current value of the field.
     * @param array  $params        [
     *     Parameters specific to this field. All arguments will be passed into the template, so if you are creating a
     *     custom template for this field, you can pass any additional fields, and they will be passed along to the
     *     template.
     *
     *     In addition to these params, each field type can also pass any attribute that is supported by HTML according to
     *     Moz standards. For more information about field-specific attributes, check out the moz documentation.
     *
     *     @var string $name          Required. The name to use for this field in HTML.
     *     @var string $setting_key   The name to use when to use when saving, or looking this item up in the database.
     *                                Defaults to field name
     *     @var string $id            The field's html ID value. Defaults to the field name.
     *     @var string $description   A description to use when displaying this field. Defaults to no description.
     *     @var string $label         The label to use with this field. Defaults to no label.
     *     @var string $wrapper_class The wrapper class for this specific field. Defaults to no class.
     * ]
     * @param bool $echo
     */
    public function make( string $type, $value, array $params = [],  $echo = false ) {

        $output= null;
        $capitalizedClassName = 'Netdust\Utils\UI\SettingsFields\\' .ucfirst($type);
        if (class_exists( $capitalizedClassName)) {
            $output = ( new $capitalizedClassName($value, $params) );
        }


        if ( $echo && ($output instanceof  SettingsField) ) {
            echo $output->place(  );
        }else{
            return $output;
        }
    }


}