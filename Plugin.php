<?php namespace Fw\Editable;

use System\Classes\PluginBase;

/**
 * Editable Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name' => 'fw.editable::lang.plugin.name',
            'description' => 'fw.editable::lang.plugin.description',
            'author' => 'Maria Vilaró',
            'icon' => 'icon-leaf'
        ];
    }

    public function registerComponents()
    {
        return [
            'Fw\Editable\Components\Editable' => 'editable',
            'Fw\Editable\Components\NotEditable' => 'noteditable',
        ];
    }
}