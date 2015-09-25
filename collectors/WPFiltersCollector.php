<?php

//use DebugBar;

class WPFiltersCollector extends DebugBar\DataCollector\DataCollector implements DebugBar\DataCollector\Renderable
{
    public function collect()
    {
        $messages = $this->getMessages();

        return array(
            'count' => count($messages),
            'messages' => $messages
        );
    }

    public function getMessages() {
        global $wp_filter;

        $messages[] = array();
        foreach ( $wp_filter as $filter_key => $filter_val ) {
            $output = '<strong>' . $filter_key . "</strong><br />\n";
            $output .= "<ul style=\"margin-left: 20px;\">\n";
            ksort( $filter_val );
            foreach ( $filter_val as $priority => $functions ) {
                $output .= '<li>';
                $output .= 'Priority: ' . $priority . "<br />\n";
                $output .= "<ul style=\"margin-left: 20px;\">\n";
                foreach ( $functions as $single_function ) {
                    if ( ( !is_string( $single_function['function'] ) && !is_object( $single_function['function'] ) ) && ( !is_array( $single_function['function'] ) || ( is_array( $single_function['function'] ) && ( !is_string( $single_function['function'][0] ) && !is_object( $single_function['function'][0] ) ) ) ) ) {
                        // Type 1 - not a callback
                        continue;
                    }
                    elseif ( is_object($single_function['function']) && ($single_function['function'] instanceof Closure) ) {
                        // Type 2 - closure
                        $output .= '<li>[<em>closure</em>]</li>';
                    }
                    elseif ( ( is_array( $single_function['function'] ) || ( is_object( $single_function['function'] ) ) && is_object($single_function['function'][0]) && ($single_function['function'][0] instanceof Closure) ) ) {
                        // Type 3 - closure within an array
                        $output .= '<li>[<em>closure</em>]</li>';
                    }
                    elseif ( is_string( $single_function['function'] ) && strpos( $single_function['function'], '::' ) === false ) {
                        // Type 4 - simple string function (includes lambda's)
                        $output .= '<li>' . sanitize_text_field( $single_function['function'] ) . '</li>';
                    }
                    elseif ( is_string( $single_function['function'] ) && strpos( $single_function['function'], '::' ) !== false ) {
                        // Type 5 - static class method calls - string
                        $output .= '<li>[<em>class</em>] ' . str_replace( '::', ' :: ', sanitize_text_field( $single_function['function'] ) ) . '</li>';
                    }
                    elseif ( is_array( $single_function['function'] ) && ( is_string( $single_function['function'][0] ) && is_string( $single_function['function'][1] ) ) ) {
                        // Type 6 - static class method calls - array
                        $output .= '<li>[<em>class</em>] ' . sanitize_text_field( $single_function['function'][0] ) . ' :: ' . sanitize_text_field( $single_function['function'][1] ) . '</li>';
                    }
                    elseif ( is_array( $single_function['function'] ) && ( is_object( $single_function['function'][0] ) && is_string( $single_function['function'][1] ) ) ) {
                        // Type 7 - object method calls
                        $output .= '<li>[<em>object</em>] ' . get_class( $single_function['function'][0] ) . ' -> ' . sanitize_text_field( $single_function['function'][1] ) . '</li>';
                    }
                    else {
                        // Type 8 - undetermined
                        $output .= '<li><pre>' . var_export( $single_function, true ) . '</pre></li>';
                    }
                }
                $output .= "</ul>\n";
                $output .= "</li>\n";
            }
            $output .= "</ul>\n";
            $messages[] = $output;
        }

        return $messages;
    }

    public function getName()
    {
        return 'filters';
    }

    public function getWidgets()
    {
        $name = $this->getName();
        return array(
            "$name" => array(
                'icon' => 'list-alt',
                'tooltip' => 'Filters',
                'widget' => 'PhpDebugBar.Widgets.ListWidget',
                'map' => "$name.messages",
                'default' => '[]',
                'position' => 'left'
            ),
            "$name:badge" => array(
                "map" => "$name.count",
                "default" => "0"
            )
        );
    }
}
