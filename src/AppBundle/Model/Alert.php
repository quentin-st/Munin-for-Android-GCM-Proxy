<?php

namespace AppBundle\Model;

class Alert
{
    private $group;
    private $host;
    private $category;
    private $plugin;
    /** @var Field[] */
    private $fields;

    public function __construct($group, $host, $category, $plugin)
    {
        $this->group = $group;
        $this->host = $host;
        $this->category = $category;
        $this->plugin = $plugin;
        $this->fields = [];
    }

    public function addField(Field $field)
    {
        $this->fields[] = $field;
    }

    public function toArray()
    {
        $array = [
            'group' => $this->group,
            'host' => $this->host,
            'category' => $this->category,
            'plugin' => $this->plugin,
            'fields' => []
        ];

        // Fields
        foreach ($this->fields as $field)
        {
            $array['fields'][] = $field->toArray();
        }

        return $array;
    }
}