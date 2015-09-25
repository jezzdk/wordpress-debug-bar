<?php

//use DebugBar;

class WPActionsCollector extends DebugBar\DataCollector\DataCollector implements DebugBar\DataCollector\Renderable
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
        global $wp_actions;
        return array_keys( $wp_actions );
    }

    public function getName()
    {
        return 'actions';
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
