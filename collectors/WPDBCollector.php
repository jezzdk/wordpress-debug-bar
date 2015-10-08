<?php

//use DebugBar;

class WPDBCollector extends DebugBar\DataCollector\DataCollector implements DebugBar\DataCollector\Renderable
{
    public function collect()
    {
        $messages = $this->getMessages();

        return array(
            'count' => count($messages),
            'messages' => $messages
        );
    }

    public function getMessages()
    {
        global $wpdb;
        $messages = array();
        $time = 0;

        foreach ( $wpdb->queries as $query ) {
            $messages[] = '<p style="white-space: nowrap; overflow: scroll"><span style="font-weight: bold; margin-right: 20px;">'
                          . '[Time: <span style="display: inline-block; width: 70px; text-align: right" title="' . $query[1] . '">'
                          . $this->convertToMs( $query[1] )
                          . ' ms</span>]:</span><span>'
                          . $query[0] . '</span></p>';
            $time += $query[1];
        }

        array_unshift( $messages, 'Total queries: ' . sizeof( $wpdb->queries ) . ', total query time: ' . $this->convertToMs( $time ) . ' ms' );

        return $messages;
    }

    public function getName()
    {
        return 'queries';
    }

    public function getWidgets()
    {
        $name = $this->getName();
        return array(
            "$name" => array(
                'icon' => 'list-alt',
                'tooltip' => 'Queries',
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

    protected function convertToMs( $seconds )
    {
        return round( $seconds * 1000, 1);
    }
}
