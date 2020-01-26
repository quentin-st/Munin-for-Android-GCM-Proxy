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

    public function addField(Field $field): void
    {
        $this->fields[] = $field;
    }

    public function toArray(): array
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

    /**
     * Group & host must not be null for this alert to be valid
     * @return bool
     */
    public function isValid()
    {
        return $this->group != null
            && $this->host != null;
    }
}